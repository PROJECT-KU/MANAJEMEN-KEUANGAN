<?php

namespace Tests\Feature\Pelanggan;

use App\Support\PesananPelanggan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Penautan pesanan ke akun pelanggan.
 *
 * Tiga dari empat tabel layanan tidak menyimpan rujukan apa pun ke akun
 * pemesannya — hanya nama, email, dan nomor telepon yang diketik sendiri.
 * Jadi penautannya menebak, dan yang diuji di sini adalah bahwa tebakannya
 * menahan diri pada saat yang tepat: lebih baik sebuah pesanan tidak muncul
 * daripada muncul di halaman orang yang salah.
 */
class PenautanPesananTest extends TestCase
{
    use DatabaseTransactions;

    private function pelanggan(string $nama, string $email, string $telp): User
    {
        $orang = User::create([
            'full_name' => $nama,
            'username' => 'uji_' . Str::random(10),
            'email' => $email,
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $orang->forceFill([
            'status' => 'active',
            'peran' => User::PERAN_PELANGGAN,
            'telp' => $telp,
        ])->save();

        return $orang->refresh();
    }

    /**
     * Satu baris pesanan di Analisis Bibliometrik.
     *
     * Tabel itu dipilih karena satu-satunya kolom wajibnya adalah id, jadi
     * contohnya tidak perlu menumpang baris milik orang lain.
     */
    private function pesanan(string $nama, string $email, string $telp, string $status = 'Pendaftaran Diterima'): string
    {
        $id = (string) Str::uuid();

        DB::table('analisis_bibliometrik')->insert([
            'id' => $id,
            'id_transaksi' => 'UJI-' . Str::upper(Str::random(6)),
            'nama' => $nama,
            'email' => $email,
            'telp' => $telp,
            'total_pembayaran' => '150000',
            'status' => $status,
            'created_at' => now(),
        ]);

        return $id;
    }

    // ------------------------------------------------------- pembakuan nomor

    public static function bentukNomor(): array
    {
        return [
            'bertanda hubung' => ['0858-4276-1933', '085842761933'],
            'polos' => ['085842761933', '085842761933'],
            'kode negara +62' => ['+6285842761933', '085842761933'],
            'kode negara 62' => ['6285842761933', '085842761933'],
            'berspasi' => ['0858 4276 1933', '085842761933'],
            'berkurung' => ['(0858) 4276-1933', '085842761933'],
            'tanpa nol depan' => ['85842761933', '085842761933'],
        ];
    }

    #[Test]
    #[DataProvider('bentukNomor')]
    public function nomor_yang_sama_dibakukan_jadi_satu(string $tulisan, string $baku): void
    {
        // Tanpa pembakuan, satu nomor yang ditulis tujuh cara jadi tujuh orang
        // berbeda — dan di data yang ada sebagian besar nomor bertanda hubung.
        $this->assertSame($baku, PesananPelanggan::nomorBaku($tulisan));
    }

    #[Test]
    public function sisa_isian_tidak_dianggap_nomor(): void
    {
        /*
         * Yang berbahaya bukan nilai acak melainkan nilai kosong dan pendek:
         * kalau "-" dianggap nomor yang sah, SEMUA baris berisian "-" jadi
         * satu orang, dan pesanan puluhan orang menumpuk di satu halaman.
         */
        foreach (['', '   ', '-', '0', '12345', 'tidak ada', '+62'] as $sampah) {
            $this->assertSame('', PesananPelanggan::nomorBaku($sampah), "[{$sampah}] seharusnya ditolak.");
        }
    }

    #[Test]
    public function nama_terlalu_pendek_tidak_dipakai_mencocokkan(): void
    {
        $this->assertSame('', PesananPelanggan::namaBaku('a'));
        $this->assertSame('', PesananPelanggan::namaBaku('ab'));
        $this->assertSame('budi santoso', PesananPelanggan::namaBaku('  Budi   SANTOSO '));
    }

    // ------------------------------------------------------------ penautannya

    #[Test]
    public function pesanan_tertaut_lewat_nomor_telepon_walau_emailnya_beda(): void
    {
        /*
         * Inti perubahannya. Orang mengganti alamat emailnya; nomor
         * teleponnya jauh lebih jarang berubah. Terukur pada data yang ada,
         * nomor telepon menautkan 29 baris sementara email hanya 3.
         */
        $orang = $this->pelanggan('Budi Santoso', 'budi.baru@contoh.test', '0812-3456-7890');
        $this->pesanan('Budi Santoso', 'budi.lama@contoh.test', '081234567890');

        $this->assertCount(1, PesananPelanggan::untuk($orang));
    }

    #[Test]
    public function nomor_yang_dipakai_dua_akun_dilewati_dan_jatuh_ke_email(): void
    {
        // Persis keadaan yang ada di data: satu orang punya dua akun dengan
        // nomor yang sama tetapi alamat email berbeda.
        $satu = $this->pelanggan('Ani Lestari', 'ani.kampus@contoh.test', '0899-1111-2222');
        $dua = $this->pelanggan('Ani Lestari', 'ani.pribadi@contoh.test', '0899-1111-2222');

        $this->pesanan('Ani Lestari', 'ani.pribadi@contoh.test', '089911112222');

        $this->assertCount(0, PesananPelanggan::untuk($satu), 'Akun tanpa email yang cocok seharusnya tidak kebagian.');
        $this->assertCount(1, PesananPelanggan::untuk($dua), 'Cadangan email seharusnya menautkannya ke sini.');
    }

    #[Test]
    public function kalau_nomor_dan_email_sama_sama_mendua_pesanannya_tidak_diberikan_ke_siapa_pun(): void
    {
        /*
         * Keputusan yang paling penting di berkas ini: menahan diri.
         *
         * Menaruh pesanan pada salah satu dari dua kemungkinan berarti
         * separuh kemungkinannya menampilkan pesanan orang lain di halaman
         * seseorang — dan itu lebih buruk daripada tidak menampilkan apa pun,
         * sebab yang membaca tidak punya cara tahu bahwa itu bukan miliknya.
         */
        $satu = $this->pelanggan('Rudi Hartono', 'rudi.a@contoh.test', '0899-3333-4444');
        $dua = $this->pelanggan('Rudi Hartono', 'rudi.b@contoh.test', '0899-3333-4444');

        // Nomornya mendua, emailnya tidak menunjuk keduanya, namanya mendua.
        $this->pesanan('Rudi Hartono', 'rudi.entah@contoh.test', '089933334444');

        $this->assertCount(0, PesananPelanggan::untuk($satu));
        $this->assertCount(0, PesananPelanggan::untuk($dua));
    }

    #[Test]
    public function nama_dipakai_kalau_nomor_dan_email_tidak_menolong(): void
    {
        $orang = $this->pelanggan('Sulastri Wijayanti', 'sulastri@contoh.test', '0821-5555-6666');

        // Nomor dan email di baris pesanannya berbeda; tinggal namanya.
        $this->pesanan('sulastri   WIJAYANTI', 'lain@contoh.test', '0899-0000-1111');

        $this->assertCount(1, PesananPelanggan::untuk($orang));
    }

    #[Test]
    public function pesanan_tanpa_pengenal_yang_cocok_tidak_tertaut_ke_siapa_pun(): void
    {
        // Kebanyakan orang yang memesan layanan memang tidak pernah mendaftar
        // akun; terukur, 151 dari 183 baris tidak cocok dengan akun mana pun.
        $orang = $this->pelanggan('Joko Prasetyo', 'joko@contoh.test', '0813-7777-8888');
        $this->pesanan('Orang Lain Sama Sekali', 'entah@contoh.test', '0855-9999-0000');

        $this->assertCount(0, PesananPelanggan::untuk($orang));
    }

    #[Test]
    public function satu_pesanan_hanya_dihitung_sekali_walau_cocok_lewat_beberapa_kunci(): void
    {
        // Nomor, email, DAN nama ketiganya cocok. Dengan penautan yang
        // memeriksa tiap kunci secara terpisah, baris ini akan terhitung tiga
        // kali dan pelanggannya tampak memesan tiga kali.
        $orang = $this->pelanggan('Dewi Anggraini', 'dewi@contoh.test', '0817-1234-5678');
        $this->pesanan('Dewi Anggraini', 'dewi@contoh.test', '0817-1234-5678');

        $this->assertCount(1, PesananPelanggan::untuk($orang));
        $this->assertSame(1, PesananPelanggan::ringkas(collect([$orang]))[$orang->id]['jumlah']);
    }

    #[Test]
    public function daftar_dan_halaman_rincian_memberi_jawaban_yang_sama(): void
    {
        /*
         * Keduanya dipakai di layar yang berbeda — ringkas() untuk angka di
         * daftar, untuk() untuk baris di halaman rincian. Kalau aturannya
         * berbeda sedikit saja, daftar bilang "2x" sementara halamannya
         * memuat tiga baris, dan tidak ada yang tahu mana yang benar.
         */
        $orang = $this->pelanggan('Hendra Gunawan', 'hendra@contoh.test', '0818-2222-3333');
        $this->pesanan('Hendra Gunawan', 'hendra@contoh.test', '08182222333311');
        $this->pesanan('Hendra G', 'lain@contoh.test', '0818-2222-3333');

        $ringkas = PesananPelanggan::ringkas(collect([$orang]))[$orang->id]['jumlah'] ?? 0;

        $this->assertSame(PesananPelanggan::untuk($orang)->count(), $ringkas);
    }

    // -------------------------------------------------------------- sumbernya

    #[Test]
    public function kelima_tabel_layanan_dan_kolomnya_benar_benar_ada(): void
    {
        /*
         * Nama tabel dan kolom ditulis sebagai untaian, jadi salah ketik tidak
         * ketahuan sampai halamannya dibuka dan galat. Uji ini membacanya
         * langsung dari skema.
         */
        $sumber = (new \ReflectionClass(PesananPelanggan::class))->getConstant('SUMBER');

        /*
         * Lima, bukan empat. Webinar Eksklusif ditambahkan 3 Okt 2026: ia
         * lahir sesudah daftar ini dibuat untuk empat layanan, dan akibatnya
         * pendaftaran webinar seseorang tidak pernah muncul di halaman
         * pelanggannya sementara jumlah pesanannya terhitung kurang.
         *
         * Angkanya disebut persis supaya layanan berikutnya yang terlewat
         * ketahuan di sini, bukan di halaman pelanggan yang kehilangan baris.
         */
        $this->assertCount(5, $sumber,
            'Kelima layanan yang punya tabel pendaftaran harus ikut: Clinik Scopus, '
            . 'Analisis Bibliometrik, Scopus Kafe, Scopus Camp, Webinar Eksklusif.');

        foreach ($sumber as $s) {
            $kolom = array_map(
                fn ($k) => mb_strtolower($k),
                DB::getSchemaBuilder()->getColumnListing($s['tabel'])
            );

            $this->assertNotEmpty($kolom, "Tabel {$s['tabel']} tidak ada.");

            foreach (['nomor', 'nilai', 'telp', 'email', 'nama', 'customer_id'] as $peran) {
                if ($s[$peran] === null) {
                    continue;
                }

                $this->assertContains(
                    mb_strtolower($s[$peran]),
                    $kolom,
                    "Kolom {$s['tabel']}.{$s[$peran]} (sebagai '{$peran}') tidak ada."
                );
            }
        }
    }

    #[Test]
    public function ikon_layanan_ada_di_font_awesome_yang_dibundel(): void
    {
        /*
         * Yang dibundel Font Awesome 5, sementara nama ikon mudah disalin dari
         * contoh versi 6. Ikon yang tidak ada tidak menimbulkan galat apa pun
         * — ia sekadar kosong, dan baru ketahuan kalau ada yang memperhatikan.
         * Begitulah 'fa-mug-hot' milik Scopus Kafe luput selama ini.
         */
        $berkas = public_path('assets/modules/fontawesome/css/all.css');

        if (! is_file($berkas)) {
            $this->markTestSkipped('Berkas Font Awesome tidak ada di lingkungan ini.');
        }

        $css = file_get_contents($berkas);
        $sumber = (new \ReflectionClass(PesananPelanggan::class))->getConstant('SUMBER');

        foreach ($sumber as $s) {
            $this->assertStringContainsString(
                '.' . $s['ikon'] . ':before',
                $css,
                "Ikon {$s['ikon']} ({$s['layanan']}) tidak ada di Font Awesome yang dibundel."
            );
        }
    }
}
