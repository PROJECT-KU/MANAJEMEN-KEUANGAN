<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\KategoriLayanan;
use App\PendaftaranScopusCamp;
use App\Support\KabarStatusPendaftaran;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\Support\PesanWaPendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tombol "Follow up" per status, lewat WhatsApp.
 *
 * Panitia mengabari pendaftar lewat dua saluran — surat dan WhatsApp — dan
 * keduanya harus mengatakan hal yang sama. Kalimat keadaannya karena itu
 * diambil dari KabarStatusPendaftaran, sumber yang dipakai suratnya; ditulis
 * ulang, keduanya akan berbeda bunyi perlahan dan pendaftar yang membaca
 * keduanya tidak tahu mana yang berlaku.
 */
class TindakLanjutWaTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function tombolnya_ada_di_kartu_status(): void
    {
        [$orang, $b] = $this->dengan();

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->getKey()]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Follow up: Menunggu bayar', $isi,
            'Tombol follow up tidak menyebut status yang sedang berlaku.');

        $this->assertMatchesRegularExpression(
            '#<a[^>]*class="[^"]*rin-tombol-wa[^"]*"[^>]*href="https://api\.whatsapp\.com/send\?phone=#s',
            $isi,
            'Tombolnya tidak memakai endpoint api.whatsapp.com.'
        );
    }

    /**
     * Kalimatnya datang dari sumber yang sama dengan suratnya. Diuji pada
     * status yang kalimatnya khas, bukan yang umum — kalau pesannya ternyata
     * dikarang sendiri, kata-kata ini tidak akan muncul.
     */
    #[Test]
    public function kalimatnya_sama_dengan_suratnya(): void
    {
        foreach (['diproses', 'Pendaftaran Diterima', 'Pendaftaran Reschedule',
            'Pendaftaran Refund', 'Pendaftaran Ditolak'] as $status) {
            $pesan = PesanWaPendaftaran::tindakLanjut($this->baris($status));

            $this->assertStringContainsString(
                KabarStatusPendaftaran::untuk($status)['pembuka'], $pesan,
                "Pesan WhatsApp status {$status} tidak memakai kalimat yang sama dengan suratnya."
            );
        }
    }

    /**
     * TIDAK BOLEH ada kata-kata khas surat.
     *
     * Percobaan pertama memakai ulang penutup milik suratnya lalu
     * menambalnya dengan str_replace. Dua kalimat lolos: "lewat email yang
     * sama" dan "Rinciannya ada di bawah ini" — yang pertama menyuruh orang
     * membalas email yang tidak sedang ia buka, yang kedua menjanjikan tabel
     * yang tidak ada di WhatsApp.
     */
    #[Test]
    public function tidak_ada_kalimat_khas_surat(): void
    {
        foreach (KabarStatusPendaftaran::semuaStatus() as $status) {
            $pesan = PesanWaPendaftaran::tindakLanjut($this->baris($status), 'Angkatan Uji');

            foreach (['balas email', 'email ini', 'email yang sama', 'surat ini'] as $khasSurat) {
                $this->assertStringNotContainsString($khasSurat, $pesan,
                    "Pesan WhatsApp status {$status} menyuruh pendaftar membuka email.");
            }
        }
    }

    /**
     * Status "Dijadwalkan ulang" berjanji "Rinciannya ada di bawah ini", dan
     * janji itu hanya ditepati kalau sesinya memang ikut terkirim.
     */
    #[Test]
    public function sesinya_ikut_saat_dijadwalkan_ulang(): void
    {
        $pesan = PesanWaPendaftaran::tindakLanjut(
            $this->baris('Pendaftaran Reschedule'), 'Angkatan Yogyakarta ke-12'
        );

        $this->assertStringContainsString('Angkatan Yogyakarta ke-12', $pesan,
            'Jadwal barunya tidak disebut, padahal kalimatnya menjanjikan rinciannya.');
    }

    /** Tiap keadaan punya kalimat penutupnya; tidak ada pesan yang menggantung. */
    #[Test]
    public function tiap_keadaan_punya_penutupnya(): void
    {
        foreach (array_keys(Pendaftaran::KEADAAN) as $keadaan) {
            $nilai = Pendaftaran::KEADAAN[$keadaan]['nilai'][0] ?? null;

            if ($nilai === null) {
                continue;
            }

            $pesan = PesanWaPendaftaran::tindakLanjut($this->baris($nilai));
            $akhir = trim((string) strrchr($pesan, "\n"));

            /*
             * Yang dituntut: baris terakhirnya KALIMAT AJAKAN, bukan baris
             * kosong atau potongan rincian. Versi pertama menuntut diakhiri
             * titik, dan itu merah pada kalimat yang justru benar —
             * penutupnya memang ditutup emoji, bukan titik.
             */
            $this->assertNotSame('', $akhir,
                "Pesan untuk keadaan {$keadaan} berakhir dengan baris kosong.");

            $this->assertMatchesRegularExpression('/(balas pesan ini|kirimkan bukti|Simpan pesan ini|ditanyakan)/', $akhir,
                "Pesan untuk keadaan {$keadaan} tidak ditutup kalimat ajakan: {$akhir}");
        }
    }

    /**
     * Status yang belum punya kalimat tidak menghasilkan pesan setengah
     * jadi — panitia masih bisa mengetik sisanya sendiri, jauh lebih baik
     * daripada tombol yang mati.
     */
    #[Test]
    public function status_tanpa_kalimat_tetap_menghasilkan_pesan(): void
    {
        $pesan = PesanWaPendaftaran::tindakLanjut($this->baris('status-karangan'));

        $this->assertStringContainsString('Halo Budi Santoso', $pesan);
        $this->assertStringContainsString('CAMP-0007', $pesan,
            'Nomor pendaftarannya hilang, jadi pesannya tidak menunjuk pendaftaran mana pun.');
    }

    #[Test]
    public function tanpa_nomor_yang_bisa_dihubungi_tombolnya_tidak_ada(): void
    {
        [$orang, $b] = $this->dengan(['telp' => '0812']);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->getKey()]))
            ->assertOk()->getContent();

        /*
         * Dicari TAGNYA, bukan nama kelasnya di mana saja.
         *
         * Versi pertama mencari untaian 'rin-tombol-wa' di seluruh halaman
         * dan merah pada markah yang justru benar: blok <style> halaman ini
         * sendiri memuat nama kelas itu. Pemindai yang menjaring gayanya
         * sendiri tidak menjaga apa pun.
         */
        $this->assertSame(0, preg_match('/<a[^>]*class="[^"]*rin-tombol-wa/', $isi),
            'Tombolnya tergambar untuk nomor yang tidak bisa dihubungi; panitia '
            . 'akan mengira pesannya terkirim.');
    }

    // ------------------------------------------------------------- pembantu

    private function baris(string $status): object
    {
        return (object) [
            'nama_orang' => 'Budi Santoso', 'nomor' => 'camp-0007', 'total' => 5500750,
            'kode_unik' => 750, 'status' => $status, 'layanan' => 'scopus_camp',
        ];
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp} */
    private function dengan(array $ubah = []): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $t = Str::random(8);

        $u = User::create([
            'full_name' => 'Panitia Uji', 'username' => 'tdl_' . $t,
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

        return [$u, $b];
    }
}
