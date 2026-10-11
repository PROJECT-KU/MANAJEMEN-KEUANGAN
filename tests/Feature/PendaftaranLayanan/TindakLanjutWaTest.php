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

        /*
         * Tombolnya BORANG, bukan tautan langsung: peladen mencatat jejaknya
         * dulu baru meneruskan ke percakapannya. Dijaga keduanya — borangnya
         * ada, dan ujungnya memang api.whatsapp.com.
         */
        $this->assertMatchesRegularExpression(
            '#<form[^>]*class="rin-follow-up"[^>]*action="[^"]*/follow-up"#s',
            $isi,
            'Tombol follow up bukan borang, jadi penekanannya tidak tercatat.'
        );

        $this->actingAs($orang)
            ->post(route('account.pendaftaran-layanan.follow-up', ['scopus_camp', $b->getKey()]))
            ->assertRedirectContains('https://api.whatsapp.com/send?phone=');
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
        $this->assertSame(0, preg_match('/<button[^>]*class="[^"]*rin-tombol-wa/', $isi),
            'Tombolnya tergambar untuk nomor yang tidak bisa dihubungi; panitia '
            . 'akan mengira pesannya terkirim.');
    }

    /**
     * Kedua tombol SEJAJAR dalam satu baris, dengan satu keterangan di
     * bawah keduanya.
     *
     * Keduanya pekerjaan yang sama — mengabari pendaftar tentang keadaannya
     * sekarang, cuma lewat saluran berbeda. Bertumpuk, keduanya tampak
     * tidak sejenis dan tiap tombol menuntut keterangan sendiri yang
     * mengulang kalimat yang sama.
     */
    #[Test]
    public function kedua_tombolnya_sebaris(): void
    {
        [$orang, $b] = $this->dengan();

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->getKey()]))
            ->assertOk()->getContent();

        /*
         * Dicari ATRIBUT class-nya, bukan nama kelasnya di mana saja.
         *
         * Kemunculan pertama 'rin-lanjutan-tombol' di halaman ini ada di
         * blok <style>-nya sendiri — potongan yang diambil dari situ berisi
         * CSS, bukan markah, dan penjaganya merah pada susunan yang justru
         * benar. Jebakan yang sudah beberapa kali terjadi di berkas ini.
         */
        $awal = strpos($isi, 'class="rin-lanjutan-tombol"');

        $this->assertNotFalse($awal, 'Baris tombol tindak lanjut tidak tergambar.');

        $baris = substr($isi, $awal, strpos($isi, '</div>', $awal) - $awal);

        $this->assertStringContainsString('Kirim ulang suratnya', $baris,
            'Tombol kirim ulang tidak berada di baris yang sama.');
        $this->assertStringContainsString('Follow up:', $baris,
            'Tombol follow up tidak berada di baris yang sama.');
    }

    /**
     * Borangnya display: contents.
     *
     * Sebagai blok, <form> jadi anak flex tersendiri dan tombolnya
     * terbungkus satu lapis lagi — jaraknya dobel dan tingginya tidak
     * sejajar dengan tautan di sebelahnya.
     */
    #[Test]
    public function borang_kirim_ulang_tidak_memakan_slot_flex(): void
    {
        $this->assertMatchesRegularExpression(
            '/\.rin-kirim-ulang,\s*\.rin-follow-up\s*\{[^}]*display:\s*contents/s',
            file_get_contents(resource_path('views/account/pendaftaran_layanan/rincian.blade.php')),
            'Borang kirim ulang kembali jadi blok, jadi kedua tombolnya tidak sejajar.'
        );
    }

    /**
     * Keterangannya mengikuti tombol yang MEMANG ada.
     *
     * Satu kalimat tetap yang menyebut dua tombol akan menerangkan tombol
     * yang tidak tergambar — surat tidak selalu ada untuk tiap status, dan
     * nomor WhatsApp tidak selalu bisa dihubungi.
     */
    #[Test]
    public function keterangannya_tidak_menyebut_tombol_yang_tidak_ada(): void
    {
        [$orang, $b] = $this->dengan(['telp' => '0812']);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->getKey()]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Mengirim lagi surat untuk status yang sekarang', $isi,
            'Keterangan untuk tombol tunggalnya hilang.');

        $this->assertStringNotContainsString('Keduanya memakai status', $isi,
            'Keterangannya menyebut dua tombol padahal yang tergambar cuma satu.');
    }

    /**
     * Menekan Follow up MENINGGALKAN JEJAK.
     *
     * Sebelum ini tombolnya tautan biasa: WhatsApp terbuka dan selesai.
     * Percobaan kirim email tercatat rapi, jadi riwayatnya timpang — yang
     * lewat surat terlihat, yang lewat WhatsApp tidak — dan pertanyaan
     * "sudah difollow-up belum?" tidak bisa dijawab dari layar mana pun.
     */
    #[Test]
    public function menekannya_meninggalkan_jejak(): void
    {
        [$orang, $b] = $this->dengan();

        $this->actingAs($orang)
            ->post(route('account.pendaftaran-layanan.follow-up', ['scopus_camp', $b->getKey()]))
            ->assertRedirectContains('api.whatsapp.com');

        $jejak = \App\PendaftaranJejak::milik('scopus_camp', (string) $b->getKey())
            ->where('aksi', 'follow-up')->first();

        $this->assertNotNull($jejak, 'Follow up tidak meninggalkan jejak apa pun.');
        $this->assertSame('diproses', $jejak->dari,
            'Status yang difollow-up tidak tercatat, jadi tidak ketahuan pesan mana yang dibuka.');
        $this->assertNotEmpty($jejak->oleh_nama, 'Siapa yang menghubungi tidak tercatat.');
    }

    /**
     * Kalimatnya berbunyi "DIBUKA", bukan "terkirim".
     *
     * Yang diketahui peladen hanyalah panitia menekan tombolnya;
     * pengirimannya terjadi di aplikasi WhatsApp, di luar jangkauan sistem
     * ini. Menuliskannya "terkirim" akan jadi catatan yang berbohong — dan
     * jejak inilah yang dipakai menelusuri perselisihan.
     */
    #[Test]
    public function jejaknya_tidak_mengaku_terkirim(): void
    {
        [$orang, $b] = $this->dengan();

        $this->actingAs($orang)
            ->post(route('account.pendaftaran-layanan.follow-up', ['scopus_camp', $b->getKey()]));

        $kalimat = \App\PendaftaranJejak::milik('scopus_camp', (string) $b->getKey())
            ->where('aksi', 'follow-up')->first()->kalimat;

        $this->assertStringContainsString('dibuka', $kalimat);
        $this->assertStringNotContainsString('terkirim', $kalimat,
            'Jejaknya mengaku pesannya terkirim, padahal yang diketahui cuma tombolnya ditekan.');
    }

    #[Test]
    public function nomor_yang_tidak_terpakai_tidak_dicatat(): void
    {
        [$orang, $b] = $this->dengan(['telp' => '0812']);

        $this->actingAs($orang)
            ->post(route('account.pendaftaran-layanan.follow-up', ['scopus_camp', $b->getKey()]))
            ->assertSessionHas('error');

        $this->assertSame(0, \App\PendaftaranJejak::milik('scopus_camp', (string) $b->getKey())
            ->where('aksi', 'follow-up')->count(),
            'Jejak follow up tertulis padahal percakapannya tidak pernah bisa dibuka.');
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
