<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\KategoriLayanan;
use App\PendaftaranJejak;
use App\PendaftaranScopusCamp;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Dua hal di baris tabel: penanda yang menggantung, dan jalan pintas lunas.
 *
 * Ubin ringkasan sudah memberi tahu BERAPA yang menunggu terlalu lama —
 * yang belum terjawab cuma yang mana, dan sebelum ini satu-satunya cara
 * mengetahuinya adalah menyaring lalu kehilangan pandangan atas sisanya.
 *
 * Menandai lunas, tindakan yang paling sering dikerjakan, butuh dua klik
 * dan satu muat halaman: buka rincian, lalu cari satu dari enam tombol
 * status yang rupanya sama persis.
 */
class BarisButuhPerhatianTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function yang_menunggu_terlalu_lama_ditandai(): void
    {
        [$orang, $b] = $this->dengan(['waktu' => now()->subDays(Pendaftaran::HARI_MENGGANTUNG + 5)]);

        $baris = $this->baris($orang, $b);

        $this->assertStringContainsString('pdl-baris-lama', $baris,
            'Barisnya tidak ditandai; ubin menyebut berapa, tetapi yang mana tetap '
            . 'harus dicari satu per satu.');
        $this->assertStringContainsString('menunggu 12 hari', $baris,
            'Lamanya tidak disebut — padahal itu yang menentukan siapa dihubungi dulu.');
    }

    #[Test]
    public function yang_baru_mendaftar_tidak_ditandai(): void
    {
        [$orang, $b] = $this->dengan(['waktu' => now()->subDay()]);

        $this->assertStringNotContainsString('pdl-baris-lama', $this->baris($orang, $b));
    }

    /**
     * Pembayar tunai menyerahkan uangnya saat datang, jadi transfernya tidak
     * sedang ditunggu. Menandainya membuat daftar tagihan berisi orang yang
     * tidak perlu ditagih, dan daftar seperti itu berhenti dibaca.
     */
    #[Test]
    public function pembayar_tunai_tidak_ditandai_walau_lama(): void
    {
        [$orang, $b] = $this->dengan([
            'waktu' => now()->subDays(40),
            'cara_bayar' => 'tunai',
        ]);

        $this->assertStringNotContainsString('pdl-baris-lama', $this->baris($orang, $b));
    }

    #[Test]
    public function yang_sudah_lunas_tidak_ditandai(): void
    {
        [$orang, $b] = $this->dengan([
            'waktu' => now()->subDays(40),
            'status' => Pendaftaran::statusLunas('scopus_camp'),
        ]);

        $this->assertStringNotContainsString('pdl-baris-lama', $this->baris($orang, $b));
    }

    /**
     * Penanda di baris, ubin ringkasan, dan saringan HARUS memakai satu
     * aturan. Tiga salinan akan berselisih tanpa suara: ubin berbunyi 12
     * sementara yang bertanda cuma 9.
     */
    #[Test]
    public function aturannya_satu_untuk_ubin_saringan_dan_penanda(): void
    {
        $pengendali = file_get_contents(
            app_path('Http/Controllers/account/PendaftaranLayananController.php')
        );

        $this->assertSame(0, preg_match('/private const HARI_MENGGANTUNG/', $pengendali),
            'Pengendalinya memegang salinan ambangnya sendiri.');

        $this->assertStringContainsString('Pendaftaran::batasMenggantung()', $pengendali,
            'Kueri ubin dan saringannya tidak lagi memakai ambang bersama.');

        $this->assertStringContainsString('Pendaftaran::menggantung($b)',
            file_get_contents(resource_path('views/account/pendaftaran_layanan/baris.blade.php')),
            'Barisnya menghitung sendiri siapa yang menggantung.');
    }

    // ------------------------------------------------------ tandai lunas

    #[Test]
    public function tombol_tandai_lunas_ada_di_barisnya(): void
    {
        [$orang, $b] = $this->dengan();

        $baris = $this->baris($orang, $b);

        $this->assertStringContainsString('Tandai lunas', $baris);
        $this->assertStringContainsString('data-pdl-lunas', $baris,
            'Tombolnya tidak punya penanda konfirmasi; ia akan mengirim borangnya '
            . 'tanpa bertanya apa-apa.');
        $this->assertStringContainsString('value="Pendaftaran Diterima"', $baris,
            'Status lunasnya tidak diambil dari katalog layanan itu.');
    }

    #[Test]
    public function yang_sudah_lunas_tidak_punya_tombolnya(): void
    {
        [$orang, $b] = $this->dengan(['status' => Pendaftaran::statusLunas('scopus_camp')]);

        $this->assertStringNotContainsString('Tandai lunas', $this->baris($orang, $b));
    }

    #[Test]
    public function menekannya_memindahkan_status_lewat_jalur_yang_sama(): void
    {
        [$orang, $b] = $this->dengan();

        $this->actingAs($orang)
            ->post(route('account.pendaftaran-layanan.status', ['scopus_camp', $b->getKey()]),
                ['status' => Pendaftaran::statusLunas('scopus_camp')])
            ->assertRedirect();

        $this->assertSame('Pendaftaran Diterima', $b->fresh()->status);

        /*
         * Lewat rute yang sudah ada, jadi jejaknya tetap tertulis. Jalur
         * kedua yang menyimpan statusnya sendiri akan melewatkan jejak,
         * surat, dan kuota sekaligus — dan tidak ada yang menunjukkannya
         * sampai ada yang mencari riwayat yang tidak pernah ditulis.
         */
        $this->assertSame(1, PendaftaranJejak::milik('scopus_camp', (string) $b->getKey())
            ->where('aksi', 'status')->count());
    }

    // ------------------------------------------------------------- pembantu

    private function baris(User $orang, PendaftaranScopusCamp $b): string
    {
        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['cari' => $b->id_transaksi]))
            ->assertOk()->getContent();

        /*
         * Dipotong ke BARISNYA saja. Daftarnya memuat pendaftaran sungguhan
         * dari basis data nyata, dan salah satunya bisa saja ikut
         * menggantung — penjaga yang menjaring seluruh halaman akan hijau
         * karena baris orang lain.
         */
        /*
         * Dicari MULAI DARI <tbody>, bukan dari awal halaman.
         *
         * Nomor pendaftarannya juga tergambar di kotak cari di kepala
         * halaman (value="T-…"), sebab daftarnya memang dibuka dengan
         * menyaringnya. Dicari dari awal, yang ketemu kotak itu — lalu
         * potongannya diambil dari <tr> terakhir SEBELUM kotak cari, yaitu
         * markah yang tidak ada hubungannya dengan barisnya sama sekali.
         */
        $tubuh = strpos($isi, '<tbody');

        $this->assertNotFalse($tubuh, 'Tabelnya tidak tergambar.');

        $awal = strpos($isi, $b->id_transaksi, $tubuh);

        $this->assertNotFalse($awal, 'Barisnya tidak muncul di daftar.');

        $mulai = strrpos(substr($isi, 0, $awal), '<tr ');
        $akhir = strpos($isi, '</tr>', $awal);

        /*
         * Potongannya diperiksa, bukan dipercaya.
         *
         * Nomor pendaftarannya juga muncul di tautan halaman di BAWAH tabel
         * (pagination membawa ?cari=...). Kalau barisnya sendiri ternyata
         * tidak tergambar, yang ketemu tautan itu — dan karena tidak ada
         * </tr> sesudahnya, potongannya jadi untaian KOSONG. Penegasan
         * apa pun atas untaian kosong lalu gagal dengan pesan yang
         * menuding markah barisnya, padahal yang salah pemotong ini.
         */
        $this->assertNotFalse($mulai, 'Tidak ada <tr> sebelum nomor pendaftarannya.');
        $this->assertNotFalse($akhir, 'Nomornya ketemu di luar tabel, bukan di barisnya.');

        $potong = substr($isi, $mulai, $akhir - $mulai);

        $this->assertNotSame('', $potong, 'Potongan barisnya kosong.');

        return $potong;
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp} */
    private function dengan(array $ubah = []): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $t = Str::random(8);
        $waktu = $ubah['waktu'] ?? now()->subDay();
        unset($ubah['waktu']);

        $u = User::create([
            'full_name' => 'Panitia Uji', 'username' => 'brs_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Uji ' . $t,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ]);

        $b = PendaftaranScopusCamp::create(array_merge([
            'id_transaksi' => 'T-' . $t, 'kategori_id' => $angkatan->id,
            'nama' => 'Peserta ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0001', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => '5500000', 'status' => 'diproses',
        ], $ubah));

        $b->forceFill(['created_at' => $waktu])->save();

        return [$u, $b->refresh()];
    }
}
