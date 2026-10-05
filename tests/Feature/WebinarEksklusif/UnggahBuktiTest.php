<?php

namespace Tests\Feature\WebinarEksklusif;

use App\ClinikScopusBiayaPersesi;
use App\KategoriLayanan;
use App\Layanan;
use App\Services\Gambar;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Peserta mengunggah bukti transfernya sendiri dari halaman status.
 *
 * Ada selama gerbang pembayaran DOKU belum terverifikasi. Sebelumnya
 * satu-satunya jalan adalah WhatsApp panitia, dan bukti yang menumpuk di satu
 * nomor pribadi tidak pernah sampai ke baris pendaftarannya.
 *
 * Yang dijaga di sini tiga hal yang mudah sekali rusak diam-diam:
 *
 * 1. Yang TERSIMPAN selalu WebP — bukan JPG yang cuma berganti nama.
 * 2. Berkas ASLINYA tidak tertinggal di mana pun.
 * 3. Penjagaannya ada di PELADEN, bukan cuma borang yang tidak digambar.
 */
class UnggahBuktiTest extends TestCase
{
    use DatabaseTransactions;

    private const FOLDER = 'bukti/webinar_eksklusif';

    /**
     * @var array<int, string> berkas yang SUDAH ADA sebelum uji ini jalan
     *
     * DatabaseTransactions mengembalikan barisnya, tetapi TIDAK menyentuh
     * cakram sama sekali. Tanpa potret ini, tiap jalan uji meninggalkan WebP
     * baru di storage pengembang — dan pembersihan tanpa pagar pernah
     * menghapus berkas sungguhan milik orang.
     */
    private array $milikOrang = [];

    /**
     * @var array<int, string> id pendaftaran yang SUDAH ADA sebelum uji jalan
     */
    private array $sebelumUji = [];

    /**
     * @var array<int, string> jalur yang DIDAFTARKAN uji ini saat mengunggah
     */
    private array $sampah = [];

    protected function setUp(): void
    {
        parent::setUp();
        Layanan::lupakanKatalog();
        KategoriLayanan::lupakanPendaftar();
        ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();

        $this->milikOrang = Storage::disk(Gambar::CAKRAM)->files(self::FOLDER);
        $this->sebelumUji = WebinarEksklusifPendaftaran::pluck('id')->all();
    }

    protected function tearDown(): void
    {
        $this->bersihkanBerkasUji();

        parent::tearDown();
    }

    /**
     * Membuang HANYA berkas yang lahir dari uji ini.
     *
     * Versi pertama menyapu apa pun di folder bukti yang tidak ada di potret
     * awal. Itu menghapus bukti transfer SUNGGUHAN milik pendaftar: ia
     * mengunggah pada detik yang sama uji ini berjalan, berkasnya belum ada
     * saat potret diambil, jadi ia dianggap sampah uji. Barisnya tertinggal
     * menunjuk berkas yang tidak ada, dan halaman statusnya diam-diam kembali
     * menawarkan "unggah bukti" seolah ia belum pernah mengirim apa pun.
     *
     * GaleriTest di berkas sebelah sudah pernah kena persis begitu dan sudah
     * memakai jalan yang benar: hanya menghapus jalur yang DIDAFTARKAN uji.
     * Pola itu tidak ikut terbawa ke sini.
     *
     * Sekarang yang dihapus hanya berkas yang ditunjuk pendaftaran yang
     * DIBUAT uji ini — barisnya tidak ada sebelum uji jalan. Pendaftaran
     * orang sungguhan sudah ada sebelumnya, jadi berkas barunya tidak pernah
     * masuk daftar hapus, berapa pun sempitnya jarak waktunya.
     *
     * Potret berkas awal tetap dipertahankan sebagai pagar kedua.
     */
    private function bersihkanBerkasUji(): void
    {
        $lama = $this->sebelumUji === [] ? ['-'] : $this->sebelumUji;

        $buatanUji = WebinarEksklusifPendaftaran::whereNotIn('id', $lama)
            ->whereNotNull('gambar')
            ->where('gambar', '!=', '')
            ->pluck('gambar')
            ->all();

        foreach (array_unique(array_merge($this->sampah, $buatanUji)) as $jalur) {
            if (in_array($jalur, $this->milikOrang, true)) {
                continue;
            }

            Storage::disk(Gambar::CAKRAM)->delete($jalur);
        }
    }

    private function sesi(array $lain = []): KategoriLayanan
    {
        return KategoriLayanan::create(array_merge([
            'layanan' => 'webinar_eksklusif', 'token' => Str::random(30),
            'nama' => 'Sesi Bukti ' . Str::random(4), 'nama_ke' => (string) random_int(7000, 7999),
            'mulai' => now()->addDays(12)->toDateString(), 'selesai' => now()->addDays(12)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Pemateri', 'total_kuota' => '20', 'sisa_kuota' => '18',
            'biaya' => '129000', 'status' => 'active',
        ], $lain));
    }

    #[Test]
    public function layar_bukti_masuk_ikut_selebar_layar_dan_berlajur(): void
    {
        /*
         * Layar ini dulu ikut dipaksa menyempit, karena aturannya "yang tidak
         * sedang harus membayar berarti satu lajur". Padahal isinya ADA di
         * kedua sisi: rincian di kiri, lalu panel "kursi Anda ditahan", tombol
         * lihat bukti, lipatan ganti bukti, dan tombol kirim ulang di kanan.
         *
         * Terukur sesudah dilepas: dua lajur 661 dan 586px di layar 1.470px,
         * kartunya 1.382x700. Yang menyempit tinggal lunas, batal, dan
         * kedaluwarsa — ketiganya memang tidak punya apa-apa di lajur kanan.
         */
        $p = $this->pendaftaran();

        $this->kirim($p, UploadedFile::fake()->image('bukti.jpg', 600, 400))->assertRedirect();

        $isi = $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->getContent();

        preg_match('/<div class="container (sta-wadah[^"]*)"/', $isi, $wadah);
        preg_match('/<div class="(sta-kisi[^"]*)"/', $isi, $kisi);

        $this->assertStringNotContainsString('sta-wadah-ramping', $wadah[1] ?? '',
            'Layar bukti-masuk masih disempitkan padahal isinya dua lajur.');

        $this->assertStringNotContainsString('sta-kisi-tunggal', $kisi[1] ?? '',
            'Isinya masih ditumpuk satu lajur.');

        $this->assertSame(2, substr_count($isi, '<div class="sta-lajur '),
            'Lajurnya bukan dua.');

        $kiri = substr($isi, (int) strpos($isi, 'sta-lajur-kiri'),
            (int) strpos($isi, 'sta-lajur-kanan') - (int) strpos($isi, 'sta-lajur-kiri'));

        $this->assertStringContainsString('class="sta-rincian"', $kiri,
            'Rinciannya tidak di lajur kiri.');

        $this->assertStringNotContainsString('class="sta-selesai"', $kiri,
            'Panel "kursi ditahan" ikut ke lajur kiri; lajur kanannya jadi kosong.');
    }

    #[Test]
    public function centang_bergerak_sekali_lalu_diam(): void
    {
        /*
         * Beda maksud dengan jam pasirnya, jadi beda pula gerakannya.
         *
         * Jam pasir berdetak TERUS karena waktunya memang masih berjalan.
         * Centang ini menandai satu kejadian yang sudah selesai: bergerak
         * sekali lalu diam. Diberi "infinite", yang sudah beres terbaca
         * seperti masih dikerjakan — dan gerakan berulang yang tidak perlu
         * itu juga yang paling mengganggu di layar yang orangnya cuma
         * menunggu.
         *
         * "both" WAJIB. Tanpa itu ubinnya kembali ke keadaan sebelum animasi
         * di antara halaman termuat dan detik pertama gerakan, dan yang
         * terlihat centang berkedip dulu sebelum melenting.
         */
        $p = $this->pendaftaran();

        $this->kirim($p, UploadedFile::fake()->image('bukti.jpg', 600, 400))->assertRedirect();

        $isi = $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->getContent();

        preg_match('/<span class="(sta-ubin[^"]*)"/', $isi, $ubin);

        $this->assertStringContainsString('sta-ubin-selesai', $ubin[1] ?? '',
            'Centangnya tidak ditandai; ia akan muncul begitu saja tanpa gerakan.');

        $aturan = preg_replace('#/\*.*?\*/#s', '', $isi);

        $ada = preg_match('/\.sta-ubin-selesai\s*\{(?<isi>[^}]*)\}/', (string) $aturan, $cocok);

        $this->assertSame(1, $ada, 'Aturan gerak centangnya hilang.');

        $this->assertStringContainsString('both', $cocok['isi'],
            'Gerakannya tidak dikunci "both"; centangnya akan berkedip dulu sebelum melenting.');

        $this->assertStringNotContainsString('infinite', $cocok['isi'],
            'Centangnya bergerak berulang; yang sudah beres jadi terbaca masih dikerjakan.');

        $this->assertMatchesRegularExpression('/\.sta-ubin-selesai::after\s*\{/', (string) $aturan,
            'Cincin gelombangnya hilang.');

        $this->assertMatchesRegularExpression(
            '/@media \(prefers-reduced-motion: reduce\) \{[^}]*sta-ubin-selesai/s',
            (string) $aturan,
            'Gerak centangnya tidak bisa dimatikan lewat setelan peramban.'
        );
    }

    #[Test]
    public function pembersihan_tidak_menyentuh_bukti_yang_diunggah_orang_saat_uji_berjalan(): void
    {
        /*
         * Ini menirukan kejadian sungguhan, bukan kemungkinan di atas kertas.
         *
         * Seorang pendaftar mengunggah bukti transfernya pada 17:48:52. Uji
         * di berkas ini berjalan beberapa detik kemudian, memotret isi folder
         * — potretnya sudah memuat berkas itu atau belum, tergantung detik —
         * lalu menyapu semua yang tidak ada di potret. Berkasnya hilang.
         * Barisnya tertinggal menunjuk berkas yang tidak ada, dan halaman
         * statusnya kembali menawarkan "unggah bukti" kepada orang yang baru
         * saja mengirimkannya. Tidak ada satu pun galat yang terbit.
         *
         * Yang membedakan keduanya bukan WAKTU, melainkan ASAL BARISNYA:
         * pendaftaran orang sungguhan sudah ada sebelum uji dimulai.
         */
        $orang = $this->pendaftaran();
        $this->sebelumUji[] = $orang->getKey();

        $jalurOrang = self::FOLDER . '/orang-' . Str::random(8) . '.webp';
        Storage::disk(Gambar::CAKRAM)->put($jalurOrang, 'bukan gambar sungguhan');
        $orang->forceFill(['gambar' => $jalurOrang])->save();

        $buatanUji = $this->pendaftaran();
        $jalurUji = self::FOLDER . '/uji-' . Str::random(8) . '.webp';
        Storage::disk(Gambar::CAKRAM)->put($jalurUji, 'bukan gambar sungguhan');
        $buatanUji->forceFill(['gambar' => $jalurUji])->save();

        $this->bersihkanBerkasUji();

        $this->assertTrue(Storage::disk(Gambar::CAKRAM)->exists($jalurOrang),
            'Bukti yang diunggah orang saat uji berjalan ikut terhapus; buktinya hilang tanpa galat apa pun.');

        $this->assertFalse(Storage::disk(Gambar::CAKRAM)->exists($jalurUji),
            'Berkas buatan uji tidak dibersihkan; folder bukti akan terus menumpuk.');

        Storage::disk(Gambar::CAKRAM)->delete($jalurOrang);
    }

    private function pendaftaran(array $lain = []): WebinarEksklusifPendaftaran
    {
        return WebinarEksklusifPendaftaran::create(array_merge([
            'kategori_id' => $this->sesi()->id,
            'nama' => 'Pendaftar Bukti', 'email' => 'b-' . Str::random(5) . '@contoh.test',
            'telp' => '628123456789', 'jumlah_pendaftar' => 1, 'total_pembayaran' => '130478',
            'cara_bayar' => 'transfer', 'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHours(20),
        ], $lain));
    }

    private function kirim(WebinarEksklusifPendaftaran $p, UploadedFile $berkas)
    {
        $jawab = $this->post(
            route('public.webinareksklusif.bukti', $p->getKey()),
            ['bukti' => $berkas]
        );

        /*
         * Jalurnya DIDAFTARKAN di sini, bukan disimpulkan belakangan dari isi
         * folder.
         *
         * RALAT atas alasan yang tertulis di commit yang memasangnya. Di sana
         * tertulis lapis ini perlu karena "satu WebP tetap tertinggal tiap
         * jalan berurutan". Itu KELIRU: WebP yang terlihat tertinggal itu
         * bukan sampah uji, melainkan bukti transfer yang BARU SAJA diunggah
         * pendaftar — dan ia lalu dihapus dengan tangan karena dikira sampah.
         * Ujinya sendiri tidak pernah bocor.
         *
         * Lapis ini tetap dipertahankan, dengan alasan yang benar: mendaftar
         * saat membuat tidak pernah bisa salah mengenali milik siapa, sedang
         * menyimpulkan dari isi folder selalu bisa.
         */
        $baru = (string) $p->fresh()?->gambar;

        if ($baru !== '') {
            $this->sampah[] = $baru;
        }

        return $jawab;
    }

    // ------------------------------------------------------- jalan bahagia

    #[Test]
    public function jpg_yang_diunggah_tersimpan_sebagai_webp(): void
    {
        $p = $this->pendaftaran();

        $this->kirim($p, UploadedFile::fake()->image('bukti-transfer.jpg', 1200, 900))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $jalur = (string) $p->fresh()->gambar;

        $this->assertStringStartsWith(self::FOLDER . '/', $jalur,
            'Buktinya harus mendarat di folder layanan ini, bukan di akar cakram.');
        $this->assertStringEndsWith('.webp', $jalur,
            'Yang tersimpan harus WebP, bukan berkas asli yang cuma berganti nama.');

        $this->assertTrue(Storage::disk(Gambar::CAKRAM)->exists($jalur));

        /*
         * Diperiksa dari ISI berkasnya, bukan dari akhirannya.
         *
         * Akhiran .webp pada berkas yang isinya masih JPEG akan lolos uji yang
         * hanya membaca namanya, dan rusaknya baru ketahuan saat panitia
         * membuka berkasnya.
         */
        $isi = Storage::disk(Gambar::CAKRAM)->get($jalur);

        $this->assertSame('WEBP', substr($isi, 8, 4),
            'Isi berkasnya ternyata bukan WebP.');
        $this->assertNotFalse(getimagesizefromstring($isi));
    }

    #[Test]
    public function png_juga_diterima(): void
    {
        $p = $this->pendaftaran();

        $this->kirim($p, UploadedFile::fake()->image('bukti.png', 800, 600))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $this->assertStringEndsWith('.webp', (string) $p->fresh()->gambar);
    }

    #[Test]
    public function berkas_asli_tidak_ikut_tertinggal_di_cakram(): void
    {
        /*
         * Janji yang disampaikan kepada peserta di halamannya: "berkas aslinya
         * tidak kami simpan". Yang dijaga di sini janji itu, bukan sekadar
         * nilai kolomnya.
         */
        $p = $this->pendaftaran();

        $this->kirim($p, UploadedFile::fake()->image('rahasia-nama-asli.jpg', 900, 600))
            ->assertRedirect();

        /*
         * Sampah bikinan sistem operasi dikecualikan. macOS menaruh .DS_Store
         * di folder mana pun yang pernah dibuka Finder, dan uji ini merah
         * karenanya — padahal yang dijaga: berkas UNGGAHAN yang bukan WebP
         * tidak boleh tertinggal. Berkas itu bukan unggahan siapa pun.
         */
        $semua = array_values(array_filter(
            Storage::disk(Gambar::CAKRAM)->allFiles(self::FOLDER),
            fn ($b) => ! str_starts_with(basename($b), '.')
        ));

        foreach ($semua as $berkas) {
            $this->assertStringEndsWith('.webp', $berkas,
                'Ada berkas bukan-WebP tertinggal di folder bukti: ' . $berkas);
        }

        $this->assertEmpty(
            array_filter($semua, fn ($b) => str_contains($b, 'rahasia-nama-asli')),
            'Nama berkas asli peserta ikut tersimpan; namanya sendiri bisa memuat data pribadi.'
        );
    }

    #[Test]
    public function bukti_yang_diganti_membuang_berkas_lamanya(): void
    {
        /*
         * Tanpa ini tiap unggahan ulang meninggalkan satu WebP yatim yang
         * tidak ditunjuk baris mana pun dan tidak akan pernah terhapus.
         */
        $p = $this->pendaftaran();

        $this->kirim($p, UploadedFile::fake()->image('pertama.jpg', 600, 400))->assertRedirect();
        $lama = (string) $p->fresh()->gambar;

        $this->kirim($p, UploadedFile::fake()->image('kedua.jpg', 600, 400))->assertRedirect();
        $baru = (string) $p->fresh()->gambar;

        $this->assertNotSame($lama, $baru);
        $this->assertFalse(Storage::disk(Gambar::CAKRAM)->exists($lama),
            'Bukti lama masih tertinggal di cakram sesudah diganti.');
        $this->assertTrue(Storage::disk(Gambar::CAKRAM)->exists($baru));
    }

    // ------------------------------------------------------------ penolakan

    #[Test]
    public function berkas_bukan_gambar_ditolak(): void
    {
        $p = $this->pendaftaran();

        $this->kirim($p, UploadedFile::fake()->create('tagihan.pdf', 120, 'application/pdf'))
            ->assertSessionHasErrors('bukti');

        $this->assertSame('', (string) $p->fresh()->gambar);
    }

    #[Test]
    public function berkas_kelewat_besar_ditolak(): void
    {
        $p = $this->pendaftaran();

        // 9 MB; batasnya 8 MB.
        $this->kirim($p, UploadedFile::fake()->create('besar.jpg', 9 * 1024, 'image/jpeg'))
            ->assertSessionHasErrors('bukti');

        $this->assertSame('', (string) $p->fresh()->gambar);
    }

    #[Test]
    public function heic_lolos_pemeriksaan_borang(): void
    {
        /*
         * HEIC adalah format BAWAAN kamera iPhone, dan peserta yang memotret
         * buktinya di situ tidak tahu berkasnya bukan JPG.
         *
         * Yang dijaga: akhiran .heic TIDAK ditolak borangnya. Pemeriksaan
         * lewat jenis MIME akan menolaknya di sebagian peladen — finfo di
         * sana memulangkan application/octet-stream untuk HEIC.
         *
         * Berhasil-tidaknya konversi tergantung alat yang ada di peladen, dan
         * itu diuji terpisah. Di sini yang penting galatnya BUKAN galat
         * borang.
         */
        $p = $this->pendaftaran();

        $jawab = $this->kirim($p, UploadedFile::fake()->create('IMG_4821.heic', 300, 'image/heic'));

        $jawab->assertSessionDoesntHaveErrors('bukti');
    }

    #[Test]
    public function heic_yang_tidak_terbaca_dijawab_kalimat_yang_bisa_dikerjakan(): void
    {
        /*
         * Peladen tanpa Imagick/libheif tidak bisa membongkar HEIC sama
         * sekali. Yang dilihat peserta harus menyebut jalan keluar yang bisa
         * ia kerjakan sendiri — bukan "terjadi kesalahan".
         */
        $p = $this->pendaftaran();

        // Isinya bukan HEIC sungguhan, jadi tidak ada alat mana pun yang bisa
        // membacanya: itu persis keadaan yang diuji.
        $this->kirim($p, UploadedFile::fake()->create('IMG_4822.heic', 300, 'image/heic'))
            ->assertRedirect()
            ->assertSessionHas('error', fn ($pesan) => str_contains($pesan, 'JPG'));

        $this->assertSame('', (string) $p->fresh()->gambar);
    }

    // ------------------------------------------------- penjagaan di peladen

    #[Test]
    public function pendaftaran_yang_sudah_lunas_menolak_bukti(): void
    {
        /*
         * Borangnya memang tidak digambar untuk yang sudah lunas — tetapi
         * alamatnya tetap bisa dikirimi permintaan oleh siapa pun yang pernah
         * membuka halamannya.
         */
        $p = $this->pendaftaran(['status' => 'paid', 'bayar_status' => 'lunas']);

        $this->kirim($p, UploadedFile::fake()->image('bukti.jpg', 600, 400))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('', (string) $p->fresh()->gambar);
    }

    #[Test]
    public function pendaftaran_yang_dibatalkan_menolak_bukti(): void
    {
        $p = $this->pendaftaran(['status' => 'cancel']);

        $this->kirim($p, UploadedFile::fake()->image('bukti.jpg', 600, 400))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('', (string) $p->fresh()->gambar);
    }

    #[Test]
    public function pendaftaran_kedaluwarsa_menolak_bukti(): void
    {
        $p = $this->pendaftaran([
            'status' => 'expired',
            'kedaluwarsa_pada' => now()->subHour(),
        ]);

        $this->kirim($p, UploadedFile::fake()->image('bukti.jpg', 600, 400))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('', (string) $p->fresh()->gambar);
    }

    // ---------------------------------------------------------- tampilannya

    #[Test]
    public function halaman_status_menawarkan_unggahan_dan_menyebut_formatnya(): void
    {
        $p = $this->pendaftaran();

        $isi = $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            route('public.webinareksklusif.bukti', $p->getKey()), $isi,
            'Borangnya tidak menunjuk ke alamat unggahan.');

        $this->assertStringContainsString('enctype="multipart/form-data"', $isi,
            'Tanpa enctype, berkasnya tidak pernah ikut terkirim dan galatnya tidak menyebut sebabnya.');

        $this->assertStringContainsString('.heic', $isi,
            'Daftar format yang diterima harus menyebut HEIC; itu bawaan kamera iPhone.');

        // Isian berkasnya disembunyikan 1x1 transparan, BUKAN display:none —
        // yang display:none dilewati papan ketik sama sekali.
        $this->assertStringContainsString('class="sta-berkas"', $isi);
    }

    #[Test]
    public function sesudah_bukti_masuk_halamannya_berhenti_menawarkan_unggahan(): void
    {
        /*
         * Dulu sesudah mengunggah, peserta kembali ke layar yang SAMA PERSIS:
         * nomor rekening, langkah "transfer dulu", hitung mundur, dan borang
         * unggah yang masih menganga. Yang awam membacanya sebagai "belum
         * berhasil", lalu mengunggah lagi. Dan lagi.
         *
         * Yang dijaga: sesudah buktinya masuk, TIDAK ADA satu pun ajakan
         * mengulang yang tergambar.
         */
        $p = $this->pendaftaran();

        $this->kirim($p, UploadedFile::fake()->image('bukti.jpg', 600, 400))->assertRedirect();

        $isi = $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->getContent();

        // Yang HARUS ada: kabar selesai dan buktinya sendiri.
        $this->assertStringContainsString('Bukti pembayaran Anda sudah masuk', $isi);
        $this->assertStringContainsString('Anda tidak perlu mengirim apa pun lagi', $isi);
        $this->assertStringContainsString('storage/' . $p->fresh()->gambar, $isi);

        // Yang HARUS hilang. Nomor rekening dan ajakan transfer di layar orang
        // yang baru saja membayar adalah yang membuatnya mengira gagal.
        $this->assertStringNotContainsString('2164 0100 0467 563', $isi,
            'Nomor rekening masih tergambar untuk orang yang buktinya sudah masuk.');
        $this->assertStringNotContainsString('Cara membayar', $isi,
            'Langkah "cara membayar" masih tergambar sesudah buktinya masuk.');
        $this->assertStringNotContainsString('Sudah transfer? Unggah buktinya di sini', $isi,
            'Borang unggahnya masih ditawarkan seperti belum pernah dikirim.');
        /*
         * Dicari UNSUR-nya, bukan kata mentahnya: "data-mis-mundur" juga muncul
         * sebagai pemilih di dalam skrip hitung mundurnya sendiri, dan
         * percobaan pertama merah pada markah yang justru benar.
         */
        $this->assertSame(0, substr_count($isi, 'data-mis-mundur="'),
            'Hitung mundurnya masih berjalan, padahal kursinya justru sedang ditahan.');

        /*
         * Penggantian bukti tetap MUNGKIN — ada yang memotret layar yang
         * salah — tetapi terlipat, bukan terbuka sejak awal.
         */
        $this->assertStringContainsString('Salah kirim? Ganti buktinya', $isi);
        $this->assertStringNotContainsString('<details class="sta-ganti" open>', $isi,
            'Borang penggantinya terbuka sejak awal; yang tidak perlu mengganti akan ikut menekannya.');
    }

    #[Test]
    public function unggahan_jadi_jalur_utama_dan_whatsapp_jadi_bantuan(): void
    {
        /*
         * Dulu tombol WhatsApp hijau SELEBAR KARTU berdiri lebih dulu, dan
         * borang unggahnya di bawahnya. Yang paling besar dan paling atas yang
         * ditekan orang — jadi buktinya tetap mengalir ke nomor pribadi
         * panitia dan tidak pernah menempel ke barisnya.
         */
        $p = $this->pendaftaran();

        $isi = $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->getContent();

        $borang = strpos($isi, 'sta-unggah-borang');
        $wa = strpos($isi, 'wa.me/');

        $this->assertNotFalse($borang, 'Borang unggahnya tidak tergambar.');
        $this->assertNotFalse($wa, 'Jalan keluar lewat WhatsApp hilang sama sekali.');

        $this->assertLessThan($wa, $borang,
            'WhatsApp masih berdiri sebelum borang unggahnya.');

        // Tombol selebar kartu itu sudah turun jadi satu baris bantuan.
        $this->assertStringNotContainsString('class="sta-wa"', $isi,
            'Tombol WhatsApp selebar kartu masih jadi ajakan utama.');
        $this->assertStringContainsString('class="sta-bantuan"', $isi);

        // Langkah keduanya menunjuk ke borang di halaman ini, bukan ke WhatsApp.
        $this->assertStringContainsString('Unggah bukti transfernya di halaman ini', $isi);
    }

    #[Test]
    public function kursi_yang_buktinya_sudah_masuk_tidak_ikut_dilepas(): void
    {
        /*
         * Kalau layarnya berkata "kursi Anda ditahan sampai pemeriksaan
         * selesai", itu harus BENAR.
         *
         * Peserta yang mengunggah bukti di jam ke-23 sudah membayar; yang
         * tersisa cuma panitia mencocokkannya, dan itu pekerjaan jam kerja.
         * Dilepas juga, uangnya sudah pindah tetapi kursinya hilang.
         */
        $sesi = $this->sesi();

        $berbukti = $this->pendaftaran([
            'kategori_id' => $sesi->id,
            'kedaluwarsa_pada' => now()->subMinutes(5),
            'gambar' => 'bukti/webinar_eksklusif/contoh.webp',
        ]);

        $kosong = $this->pendaftaran([
            'kategori_id' => $sesi->id,
            'kedaluwarsa_pada' => now()->subMinutes(5),
        ]);

        WebinarEksklusifPendaftaran::lepaskanYangKedaluwarsa($sesi->id);

        $this->assertSame('pending', $berbukti->fresh()->status,
            'Kursi yang buktinya sudah masuk ikut dilepas; uangnya pindah tetapi tempatnya hilang.');
        $this->assertSame('expired', $kosong->fresh()->status,
            'prasyarat: yang tanpa bukti memang harus dilepas');

        // Dan halamannya tidak boleh berkata "batas waktunya lewat".
        $this->assertFalse($berbukti->fresh()->sudah_kedaluwarsa);
    }

    #[Test]
    public function panitia_melihat_bukti_yang_diunggah_peserta(): void
    {
        /*
         * Bukti yang tidak bisa dilihat panitia sama saja dengan tidak ada.
         *
         * Dulu buktiBaris() merakit jalurnya sendiri sebagai public/<folder>/
         * dan layanan ini TIDAK punya folder lama — jadi apa pun yang
         * diunggah peserta selalu terbaca "berkasnya tidak ada".
         */
        $p = $this->pendaftaran();

        $this->kirim($p, UploadedFile::fake()->image('bukti.jpg', 600, 400))->assertRedirect();

        $baris = (object) [
            'layanan' => 'webinar_eksklusif',
            'bukti' => $p->fresh()->gambar,
        ];

        $bukti = \App\Support\PendaftaranSemuaLayanan::buktiBaris($baris);

        $this->assertTrue($bukti['nilai']);
        $this->assertTrue($bukti['ada'], 'Panitia akan melihat "berkasnya tidak ada".');
        $this->assertNotNull($bukti['url']);
    }

    #[Test]
    public function heic_sungguhan_diubah_jadi_webp(): void
    {
        /*
         * Jalur HEIC dari ujung ke ujung, dengan berkas HEIC SUNGGUHAN.
         *
         * Dilewati kalau mesin ini tidak bisa membuatnya — pembuatannya
         * memakai `sips` yang hanya ada di macOS. Dilewati pun ada gunanya:
         * ia menandai bahwa jalur ini TIDAK terbukti di mesin itu, bukan
         * berpura-pura hijau.
         */
        $heic = $this->berkasHeic();

        if ($heic === null) {
            $this->markTestSkipped('Mesin ini tidak bisa membuat berkas HEIC contoh (butuh sips).');
        }

        $p = $this->pendaftaran();

        $this->kirim($p, new UploadedFile($heic, 'IMG_5120.heic', 'image/heic', null, true))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        @unlink($heic);

        $jalur = (string) $p->fresh()->gambar;

        $this->assertStringEndsWith('.webp', $jalur);

        $isi = Storage::disk(Gambar::CAKRAM)->get($jalur);

        $this->assertSame('WEBP', substr($isi, 8, 4),
            'HEIC-nya tidak benar-benar berubah jadi WebP.');
        $this->assertNotFalse(getimagesizefromstring($isi));
    }

    /** Berkas HEIC contoh; null kalau mesin ini tidak bisa membuatnya. */
    private function berkasHeic(): ?string
    {
        $sumber = sys_get_temp_dir() . '/heic-sumber-' . Str::random(6) . '.jpg';
        $tujuan = sys_get_temp_dir() . '/heic-bukti-' . Str::random(6) . '.heic';

        $im = imagecreatetruecolor(600, 400);
        imagefill($im, 0, 0, imagecolorallocate($im, 30, 90, 200));
        imagejpeg($im, $sumber);
        imagedestroy($im);

        @exec('command -v sips 2>/dev/null', $ada, $kode);

        if ($kode === 0) {
            @exec(sprintf('sips -s format heic %s --out %s 2>/dev/null',
                escapeshellarg($sumber), escapeshellarg($tujuan)));
        }

        @unlink($sumber);

        return is_file($tujuan) ? $tujuan : null;
    }
}
