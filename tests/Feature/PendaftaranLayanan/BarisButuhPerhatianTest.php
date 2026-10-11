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

    /**
     * Bukti yang sudah lama menunggu DIPERIKSA ditandai juga.
     *
     * Beda dari penanda "menggantung": yang itu menunggu orang lain
     * membayar, yang ini menunggu panitia membuka satu gambar — dan selama
     * itu uangnya mungkin sudah masuk tanpa tercatat. Terukur di basis
     * data: enam bukti menunggu diperiksa selama 177, 127, 74, 28, 17, dan
     * 6 hari.
     */
    #[Test]
    public function bukti_yang_lama_menunggu_diperiksa_ditandai(): void
    {
        [$orang, $b] = $this->dengan([
            'gambar' => 'bukti/scopus_camp/ada.webp',
            'waktu' => now()->subDays(Pendaftaran::HARI_PERIKSA + 9),
        ]);

        $baris = $this->baris($orang, $b);

        $this->assertStringContainsString('pdl-baris-periksa', $baris,
            'Bukti yang menunggu diperiksa berbulan-bulan tampil sama dengan yang '
            . 'baru diunggah tadi pagi.');
        $this->assertStringContainsString('belum diperiksa 12 hari', $baris,
            'Lamanya tidak disebut — padahal itu yang membedakan antrean biasa '
            . 'dari yang terlupakan.');
    }

    #[Test]
    public function bukti_yang_baru_diunggah_tidak_ditandai(): void
    {
        [$orang, $b] = $this->dengan([
            'gambar' => 'bukti/scopus_camp/ada.webp',
            'waktu' => now()->subDay(),
        ]);

        $this->assertStringNotContainsString('pdl-baris-periksa', $this->baris($orang, $b));
    }

    /** Tanpa bukti, tidak ada yang perlu diperiksa — itu urusan penanda lain. */
    #[Test]
    public function tanpa_bukti_tidak_ditandai_perlu_diperiksa(): void
    {
        [$orang, $b] = $this->dengan(['waktu' => now()->subDays(40)]);

        $this->assertStringNotContainsString('pdl-baris-periksa', $this->baris($orang, $b));
    }

    #[Test]
    public function yang_sudah_lunas_tidak_ditandai_perlu_diperiksa(): void
    {
        [$orang, $b] = $this->dengan([
            'gambar' => 'bukti/scopus_camp/ada.webp',
            'waktu' => now()->subDays(40),
            'status' => Pendaftaran::statusLunas('scopus_camp'),
        ]);

        $this->assertStringNotContainsString('pdl-baris-periksa', $this->baris($orang, $b));
    }

    /**
     * Aturan umurnya SATU untuk ubin dan penanda baris. Dua salinan akan
     * berselisih tanpa suara.
     */
    #[Test]
    public function aturan_periksanya_satu(): void
    {
        $this->assertStringContainsString('Pendaftaran::periksaTertunda($b)',
            file_get_contents(resource_path('views/account/pendaftaran_layanan/baris.blade.php')),
            'Barisnya menghitung sendiri siapa yang terlambat diperiksa.');

        $this->assertStringContainsString("\$ringkasan['periksa_tertua']",
            file_get_contents(resource_path('views/account/pendaftaran_layanan/index.blade.php')),
            'Umur bukti tertua tidak disebut di ubinnya.');
    }

    // --------------------------------------------------------- follow up

    /**
     * Di barisnya ada FOLLOW UP, bukan "Tandai lunas".
     *
     * Menandai lunas dari daftar dibuang karena pembayaran akan ditangani
     * payment gateway: statusnya berpindah sendiri, dan tombol yang
     * menandainya dengan tangan berhenti jadi pekerjaan harian — sementara
     * satu salah tekan di sana memindahkan status SEKALIGUS mengirim surat
     * ke pendaftarnya. Yang memang dikerjakan tiap hari justru menghubungi
     * orangnya.
     */
    #[Test]
    public function barisnya_menawarkan_follow_up(): void
    {
        [$orang, $b] = $this->dengan();

        $baris = $this->baris($orang, $b);

        $this->assertStringContainsString('Follow up', $baris);
        $this->assertStringContainsString('/follow-up"', $baris,
            'Tombolnya tidak menuju rute yang mencatat jejaknya.');

        $this->assertStringNotContainsString('Tandai lunas', $baris,
            'Tombol tandai lunas masih ada di daftar; satu salah tekan di sana '
            . 'memindahkan status sekaligus mengirim surat.');
    }

    /**
     * Follow up ada untuk SEMUA keadaan, bukan cuma yang menunggu bayar.
     *
     * Pesannya memang menyesuaikan statusnya — yang sudah lunas diberi
     * ucapan terima kasih, yang direfund diberi keterangan dananya — jadi
     * membatasinya ke satu keadaan membuang gunanya.
     */
    #[Test]
    public function follow_up_ada_juga_untuk_yang_sudah_lunas(): void
    {
        [$orang, $b] = $this->dengan(['status' => Pendaftaran::statusLunas('scopus_camp')]);

        $this->assertStringContainsString('Follow up', $this->baris($orang, $b));
    }

    /**
     * Tanpa nomor yang bisa dihubungi, tombolnya tidak digambar: tombol
     * yang membuka percakapan dengan nomor yang tidak ada membuat panitia
     * mengira pesannya terkirim.
     */
    #[Test]
    public function tanpa_nomor_tidak_ada_tombol_follow_up(): void
    {
        [$orang, $b] = $this->dengan(['telp' => '0812']);

        $this->assertStringNotContainsString('Follow up', $this->baris($orang, $b));
    }

    /** Menekannya meninggalkan jejak, sama seperti dari layar rincian. */
    #[Test]
    public function menekannya_meninggalkan_jejak(): void
    {
        [$orang, $b] = $this->dengan();

        $this->actingAs($orang)
            ->post(route('account.pendaftaran-layanan.follow-up', ['scopus_camp', $b->getKey()]))
            ->assertRedirectContains('api.whatsapp.com');

        $this->assertSame(1, PendaftaranJejak::milik('scopus_camp', (string) $b->getKey())
            ->where('aksi', 'follow-up')->count(),
            'Follow up dari daftar tidak tercatat, jadi riwayatnya timpang dengan '
            . 'yang ditekan dari layar rincian.');
    }

    /**
     * Kedua tombol aksi SEJAJAR, tidak pernah bertumpuk.
     *
     * Dengan flex-wrap: wrap, susunannya bergantung pada sisa lebar kolom
     * Aksi — dan lebar itu ditentukan isi baris LAIN di halaman yang sama.
     * Terukur: kolomnya 233px di halaman 1 dan 186px di halaman 6 (nama
     * sesinya lebih panjang di sana), sementara kedua tombolnya menuntut
     * 255px. Akibatnya tombol yang sama tampil bersebelahan di satu halaman
     * dan bertumpuk di halaman berikutnya, dan tangan yang sudah hafal
     * letaknya meleset tiap ganti halaman.
     */
    #[Test]
    public function kedua_tombol_aksinya_tidak_pernah_bertumpuk(): void
    {
        $gaya = file_get_contents(
            resource_path('views/account/pendaftaran_layanan/index.blade.php')
        );

        $this->assertMatchesRegularExpression(
            '/\.pdl-aksi-baris\s*\{[^}]*flex-wrap:\s*nowrap/s', $gaya,
            'Tombol aksinya boleh membungkus lagi, jadi letaknya berubah-ubah '
            . 'antar halaman.'
        );

        $this->assertSame(0, preg_match('/\.pdl-aksi-baris\s*\{[^}]*flex-wrap:\s*wrap/s', $gaya),
            'flex-wrap: wrap kembali — itu persis yang membuat letaknya tidak tetap.');

        /*
         * Dan selnya tidak boleh menyusut di bawah lebar kedua tombolnya:
         * tanpa white-space: nowrap, tabel memberi kolom ini lebar
         * seadanya dan tombolnya terpotong, bukan sejajar.
         */
        $this->assertMatchesRegularExpression(
            '/td\[data-judul="Aksi"\]\s*\{[^}]*white-space:\s*nowrap/s', $gaya,
            'Kolom Aksi boleh menyusut lagi di bawah lebar tombolnya.'
        );
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
