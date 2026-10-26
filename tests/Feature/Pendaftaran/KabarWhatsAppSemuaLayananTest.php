<?php

namespace Tests\Feature\Pendaftaran;

use App\Actions\Pendaftaran\KabariWhatsApp;
use App\AnalisisBibliometrik;
use App\CategoriesAnalisisBibliometrik;
use App\CategoriesScopusCamp;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\PendaftaranScopusCamp;
use App\PendaftaranScopusKafe;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pemberitahuan WhatsApp untuk SEMUA layanan, bukan cuma Webinar Eksklusif.
 *
 * Sampai 24 Okt 2026 hanya satu dari lima layanan yang mengirimkannya. Empat
 * sisanya diam sama sekali: yang mendaftar Scopus Camp, Analisis
 * Bibliometrik, atau Scopus Kafe hanya dikabari lewat email — dan yang tidak
 * membuka emailnya kehilangan nomor pendaftarannya, satu-satunya hal yang
 * disebut panitia saat pembayarannya ditelusuri.
 *
 * Yang dijaga di sini bukan adanya pesan, melainkan hal-hal yang kalau rusak
 * TIDAK mengeluarkan galat apa pun:
 *
 *   - satu layanan terlepas dari rakitannya dan diam lagi tanpa ada yang
 *     menyadarinya;
 *   - peringatan penipuan yang BERJANJI "pembayaran hanya lewat tautan
 *     resmi" terkirim ke layanan yang justru meminta transfer manual —
 *     janji yang dilanggar sistemnya sendiri, dan yang mengajari orang
 *     mengabaikan peringatan kita yang berikutnya;
 *   - pesan "selesaikan pembayarannya" ke orang yang bukti transfernya SUDAH
 *     terunggah, yang lalu mentransfer dua kali;
 *   - pesan "kursi Anda dilepas karena belum bayar" ke orang yang
 *     dibatalkan panitia karena alasan lain.
 */
class KabarWhatsAppSemuaLayananTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        config()->set('services.fonnte.token', 'TOKEN-UJI');
        config()->set('panitia.whatsapp', '6288983567819');

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
    }

    // ----------------------------------------------------------- data ujinya

    private function angkatanCamp(): CategoriesScopusCamp
    {
        return CategoriesScopusCamp::create([
            'layanan' => 'scopus_camp',
            'token' => Str::random(30),
            'nama' => 'UJI Camp WA ' . Str::random(5),
            'nama_ke' => '8123',
            'mulai' => now()->addDays(20)->toDateString(),
            'selesai' => now()->addDays(22)->toDateString(),
            'lokasi' => 'Yogyakarta',
            'total_kuota' => 20, 'sisa_kuota' => 19,
            'biaya' => 5500000, 'status' => 'active',
        ]);
    }

    private function angkatanBibliometrik(): CategoriesAnalisisBibliometrik
    {
        return CategoriesAnalisisBibliometrik::create([
            'layanan' => 'bibliometrik',
            'token' => Str::random(30),
            'nama' => 'UJI Bib WA ' . Str::random(5),
            'nama_ke' => '7123',
            'mulai' => now()->addDays(20)->toDateString(),
            'selesai' => now()->addDays(20)->toDateString(),
            'total_kuota' => 20, 'sisa_kuota' => 19,
            'biaya' => 1500000, 'status' => 'active',
        ]);
    }

    /**
     * Satu pendaftaran per layanan, dengan nomor dan nominal yang BERBEDA.
     *
     * Berbeda supaya uji yang memeriksa isi pesannya tidak bisa lulus karena
     * kebetulan membaca pesan layanan lain: nomornya sendiri yang jadi
     * penanda layanan mana yang benar-benar mengirim.
     *
     * @return array{0: string, 1: object, 2: ?object}
     */
    private function barisnya(string $layanan): array
    {
        if ($layanan === 'scopus_camp') {
            $a = $this->angkatanCamp();

            return ['scopus_camp', PendaftaranScopusCamp::create([
                'token' => Str::random(30),
                'id_transaksi' => 'CAMP-WA-' . Str::upper(Str::random(6)),
                'kategori_id' => $a->getKey(),
                'nama' => 'Pendaftar Camp',
                'email' => 'camp' . Str::random(5) . '@contoh.test',
                'telp' => '081234567890',
                'jumlah_pendaftar' => 1,
                'total_pembayaran' => '5500000',
                'cara_bayar' => 'doku',
                'status' => 'diproses',
                'kedaluwarsa_pada' => now()->addHour(),
            ]), $a];
        }

        if ($layanan === 'bibliometrik') {
            $a = $this->angkatanBibliometrik();

            return ['bibliometrik', AnalisisBibliometrik::create([
                'token' => Str::random(30),
                'id_transaksi' => 'BIB-WA-' . Str::upper(Str::random(6)),
                'kategori_id' => $a->getKey(),
                'nama' => 'Pendaftar Bib',
                'email' => 'bib' . Str::random(5) . '@contoh.test',
                'telp' => '081234567891',
                'jumlah_pendaftar' => 1,
                'total_pembayaran' => '1500321',
                'cara_bayar' => 'transfer',
                'status' => 'diproses',
            ]), $a];
        }

        if ($layanan === 'scopus_kafe') {
            return ['scopus_kafe', PendaftaranScopusKafe::create([
                'id_pemesanan' => 'KAFE-WA-' . Str::upper(Str::random(6)),
                'nama' => 'Pendaftar Kafe',
                'email' => 'kafe' . Str::random(5) . '@contoh.test',
                'telp' => '081234567892',
                'sesi' => 'Sesi Uji',
                'total_keseluruhan_pembayaran' => '250432',
                'cara_bayar' => 'transfer',
                'status' => 'menunggu verifikasi',
            ]), null];
        }

        $a = \App\KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'UJI Webinar WA ' . Str::random(5),
            'nama_ke' => '9123',
            'mulai' => now()->addDays(16)->toDateString(),
            'selesai' => now()->addDays(16)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '19',
            'biaya' => '129000', 'status' => 'active',
        ]);

        return ['webinar_eksklusif', \App\WebinarEksklusifPendaftaran::create([
            'id_transaksi' => 'WE-WA-' . Str::upper(Str::random(6)),
            'kategori_id' => $a->getKey(),
            'nama' => 'Pendaftar Webinar',
            'email' => 'we' . Str::random(5) . '@contoh.test',
            'telp' => '081234567893',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '129543',
            'cara_bayar' => 'doku',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHour(),
        ]), $a];
    }

    /** Isi pesan terakhir yang dikirim ke Fonnte, atau null kalau tidak ada. */
    private function pesanTerakhir(): ?string
    {
        $pesan = null;

        foreach (Http::recorded() as [$permintaan, $jawaban]) {
            if (str_contains($permintaan->url(), 'fonnte.com')) {
                $pesan = (string) $permintaan['message'];
            }
        }

        return $pesan;
    }

    /** @return array<int, string> */
    private const SEMUA = ['webinar_eksklusif', 'scopus_camp', 'bibliometrik', 'scopus_kafe'];

    /*
     * Scopus Kafe pindah ke BERGERBANG 26 Okt 2026; Analisis Bibliometrik
     * satu-satunya yang masih bertransfer manual.
     */
    private const BERGERBANG = ['webinar_eksklusif', 'scopus_camp', 'scopus_kafe'];

    private const MANUAL = ['bibliometrik'];

    /*
     * Yang punya TEMPAT TERBATAS untuk dilepas kembali — dan itu bukan
     * hal yang sama dengan bergerbang. Scopus Kafe bergerbang tetapi tidak
     * berkuota: yang dipesan waktu pendampingan, bukan kursi dari jatah yang
     * terbatas, jadi tagihan yang mati di sana tidak melepaskan apa pun.
     */
    private const BERKUOTA = ['webinar_eksklusif', 'scopus_camp'];

    private const TANPA_KUOTA = ['bibliometrik', 'scopus_kafe'];

    // ------------------------------------------------------------- ujiannya

    #[Test]
    public function keempat_layanan_mengirim_pesan_berisi_nomor_dan_nominalnya(): void
    {
        /*
         * PENJAGA TERPENTING di berkas ini: satu layanan terlepas dari
         * rakitannya dan diam lagi.
         *
         * Diamnya tidak mengeluarkan galat apa pun — tidak ada yang gagal,
         * tidak ada yang tercatat — dan baru ketahuan dari pendaftar yang
         * mengadu bahwa ia tidak pernah dikabari.
         *
         * Nomor DAN nominalnya, bukan salah satu: pesan tanpa nomor tidak
         * bisa ditelusuri panitia, dan pesan tanpa nominal membuat orang
         * mentransfer harga yang ia ingat sendiri — tanpa kode unik, yang
         * justru satu-satunya cara pembayarannya dicocokkan.
         */
        foreach (self::SEMUA as $layanan) {
            [$l, $p, $angkatan] = $this->barisnya($layanan);

            Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);

            $terkirim = app(KabariWhatsApp::class)->pendaftaranMasuk($l, $p, $angkatan);

            $this->assertTrue($terkirim, $layanan . ' tidak mengirim WhatsApp sama sekali.');

            $pesan = (string) $this->pesanTerakhir();
            $nomor = (string) ($p->id_transaksi ?? $p->id_pemesanan);

            $this->assertStringContainsString($nomor, $pesan,
                $layanan . ' mengirim pesan tanpa nomor pendaftarannya.');

            $this->assertStringContainsString(
                number_format((int) ($p->total_pembayaran ?? $p->total_keseluruhan_pembayaran), 0, ',', '.'),
                $pesan,
                $layanan . ' mengirim pesan tanpa nominal yang harus dibayar.');
        }
    }

    #[Test]
    public function layanan_bertransfer_manual_tidak_berjanji_hanya_lewat_tautan_resmi(): void
    {
        /*
         * Kaki pesan Webinar Eksklusif berbunyi "pembayaran hanya lewat
         * tautan resmi". Itu BOLEH ditulis setegas itu di sana: layanan itu
         * hanya menerima DOKU, jadi siapa pun yang meminta transfer di luar
         * tautannya memang penipu.
         *
         * Analisis Bibliometrik dan Scopus Kafe masih bertransfer manual ke
         * rekening yayasan. Kalimat yang SAMA di sana jadi janji yang
         * dilanggar sistemnya sendiri — orangnya baru saja diminta
         * mentransfer oleh borang kami — dan peringatan penipuan yang
         * terbukti salah mengajari orang mengabaikan peringatan kita yang
         * berikutnya, termasuk yang benar.
         *
         * Baris ini yang akan merah lebih dulu kalau kakinya disamaratakan.
         */
        foreach (self::MANUAL as $layanan) {
            [$l, $p, $angkatan] = $this->barisnya($layanan);

            Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
            app(KabariWhatsApp::class)->pendaftaranMasuk($l, $p, $angkatan);

            $pesan = (string) $this->pesanTerakhir();

            $this->assertStringNotContainsString('hanya lewat tautan resmi', $pesan,
                $layanan . ' berjanji pembayaran hanya lewat tautan resmi, padahal '
                . 'borangnya sendiri meminta transfer manual.');

            // Yang ditegaskan TUJUANNYA, bukan caranya — dan itu benar di
            // kelima layanan.
            $this->assertStringContainsString('atas nama yayasan', $pesan,
                $layanan . ' tidak menyebut ke rekening mana pembayaran yang sah masuk.');
        }
    }

    #[Test]
    public function layanan_bergerbang_tetap_menegaskan_tautan_resminya(): void
    {
        /*
         * Pasangan terbalik dari uji di atas, dan perlu keduanya: dibuat
         * seragam ke arah yang lebih lembut, kedua layanan bergerbang
         * kehilangan kalimat yang paling tegas — dan di situlah penipuan
         * paling sering terjadi, sebab di situ ada uang yang menunggu
         * dibayar dalam hitungan menit.
         */
        foreach (self::BERGERBANG as $layanan) {
            [$l, $p, $angkatan] = $this->barisnya($layanan);

            Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
            app(KabariWhatsApp::class)->pendaftaranMasuk($l, $p, $angkatan);

            $this->assertStringContainsString('hanya lewat tautan resmi',
                (string) $this->pesanTerakhir(),
                $layanan . ' tidak lagi menegaskan bahwa pembayarannya hanya lewat tautan resmi.');
        }
    }

    #[Test]
    public function yang_buktinya_sudah_terunggah_tidak_disuruh_membayar_lagi(): void
    {
        /*
         * Scopus Kafe MEWAJIBKAN unggahan bukti transfer di borang
         * pendaftarannya: orangnya sudah mengirim uang DAN buktinya sebelum
         * pesan ini berangkat.
         *
         * Disuruh menyelesaikan pembayaran, ia mengira unggahannya gagal
         * lalu mentransfer untuk kedua kalinya. Uangnya masuk dua kali, dan
         * pengembaliannya dikerjakan dengan tangan.
         *
         * Ikut dijaga: desakan "mohon segera diselesaikan" juga tidak boleh
         * ada. Ia membantah kalimat di atasnya sendiri yang baru saja
         * berkata tidak ada yang perlu dikerjakan.
         */
        foreach (self::MANUAL as $layanan) {
            [$l, $p, $angkatan] = $this->barisnya($layanan);

            $p->forceFill(['gambar' => 'uji/bukti-palsu.jpg'])->save();

            Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
            app(KabariWhatsApp::class)->pendaftaranMasuk($l, $p->refresh(), $angkatan);

            $pesan = (string) $this->pesanTerakhir();

            $this->assertStringNotContainsString('Silakan selesaikan pembayaran', $pesan,
                $layanan . ' menyuruh membayar lagi, padahal bukti transfernya sudah terunggah.');

            $this->assertStringContainsString('sedang kami periksa', $pesan,
                $layanan . ' tidak memberi tahu bahwa buktinya sedang diperiksa, jadi '
                . 'orangnya tidak tahu apa yang ditunggunya.');

            $this->assertStringNotContainsString('Mohon segera diselesaikan', $pesan,
                $layanan . ' mendesak orang yang sudah membayar, di pesan yang sama yang '
                . 'baru saja berkata tidak ada yang perlu ia kerjakan.');
        }
    }

    #[Test]
    public function yang_belum_mengunggah_bukti_diberi_tahu_ke_mana_mentransfer(): void
    {
        /*
         * Pasangan terbalik dari uji di atas, dan perlu keduanya.
         *
         * Analisis Bibliometrik memakai borang yang sama tetapi unggahan
         * buktinya TIDAK wajib — dua pendaftaran di satu layanan bisa berbeda
         * keadaan. Yang belum mengunggah harus tahu ke mana uangnya masuk;
         * dikirimi "buktinya sedang kami periksa", ia menunggu kabar atas
         * pembayaran yang belum pernah ia kerjakan, dan kursinya berlalu.
         *
         * Yang disebut HALAMAN PENDAFTARANNYA, bukan nomor rekening yang
         * diketik di perakit pesan: satu angka salah di situ mengirim uang
         * orang ke rekening orang lain tanpa ada yang tahu sampai ia menagih.
         */
        [$l, $p, $angkatan] = $this->barisnya('bibliometrik');

        $this->assertSame('', trim((string) $p->gambar),
            'Data ujinya sudah memuat bukti, jadi uji ini tidak menguji apa pun.');

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
        app(KabariWhatsApp::class)->pendaftaranMasuk($l, $p, $angkatan);

        $pesan = (string) $this->pesanTerakhir();

        $this->assertStringContainsString('rekening atas nama yayasan', $pesan,
            'Yang belum mengunggah bukti tidak diberi tahu ke mana uangnya masuk.');

        $this->assertStringNotContainsString('sedang kami periksa', $pesan,
            'Yang belum membayar dikabari buktinya sedang diperiksa, jadi ia menunggu '
            . 'kabar atas pembayaran yang belum pernah ia kerjakan.');

        $this->assertStringNotContainsString('melalui tautan berikut', $pesan,
            'Layanan bertransfer manual diberi tautan pembayaran yang tidak ada.');
    }

    #[Test]
    public function layanan_tanpa_halaman_status_tidak_memuat_tautan_status(): void
    {
        /*
         * Hanya Webinar Eksklusif dan Scopus Camp punya halaman status
         * ber-UUID. Yang tidak punya TIDAK diberi tautan — tautan yang
         * mengantar ke halaman yang salah lebih buruk daripada tidak ada
         * tautan, sebab yang menekannya mengira ia sudah memeriksa.
         */
        foreach (self::MANUAL as $layanan) {
            [$l, $p, $angkatan] = $this->barisnya($layanan);

            Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
            app(KabariWhatsApp::class)->pendaftaranMasuk($l, $p, $angkatan);

            $this->assertStringNotContainsString('/Status/', (string) $this->pesanTerakhir(),
                $layanan . ' memuat tautan halaman status, padahal layanan ini tidak punya.');
        }
    }

    #[Test]
    public function kursi_dilepas_tidak_pernah_dikirim_ke_layanan_tanpa_kuota(): void
    {
        /*
         * "Pembayaran belum masuk sampai batas waktunya, jadi kursinya kami
         * lepas" adalah pernyataan tentang GERBANG PEMBAYARAN: ada tenggat,
         * dan tenggatnya lewat.
         *
         * Dua layanan ini tidak punya tenggat sama sekali. Pesan itu di sana
         * menuduh orang tidak membayar untuk sesuatu yang tidak pernah kami
         * batasi waktunya — dan sebagian dari mereka buktinya sudah ada di
         * tangan kami.
         */
        foreach (self::TANPA_KUOTA as $layanan) {
            [$l, $p, $angkatan] = $this->barisnya($layanan);

            Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);

            $this->assertFalse(app(KabariWhatsApp::class)->kursiDilepas($l, $p, $angkatan),
                $layanan . ' mengirim pesan kursi dilepas, padahal tidak punya tempat '
                . 'terbatas yang bisa dilepas.');

            $this->assertFalse(app(KabariWhatsApp::class)->gagalBayar($l, $p, $angkatan),
                $layanan . ' mengirim pesan gagal bayar yang menyebut tempatnya dilepas, '
                . 'padahal tidak ada kuota yang dikembalikan.');

            Http::assertNothingSent();
        }
    }

    #[Test]
    public function clinik_scopus_dilewati_tanpa_mengirim_apa_pun(): void
    {
        /*
         * Clinik Scopus belum punya satu surat pun. Dikabari lewat WhatsApp
         * sementara emailnya tidak pernah datang, pemesannya menerima pesan
         * tentang kejadian yang tidak ada jejaknya di mana pun — dan nomor
         * yang ia sebut saat bertanya tidak akan cocok dengan apa pun yang
         * dipegang panitia.
         */
        [, $p, ] = $this->barisnya('scopus_kafe');

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);

        $this->assertFalse(app(KabariWhatsApp::class)->pendaftaranMasuk('clinik_scopus', $p),
            'Clinik Scopus dikabari lewat WhatsApp, padahal suratnya belum ada.');

        $this->assertFalse(KabariWhatsApp::melayani('clinik_scopus'));

        Http::assertNothingSent();
    }

    #[Test]
    public function status_lunas_tiap_layanan_mengabari_dengan_kosakatanya_sendiri(): void
    {
        /*
         * Kosakata statusnya BERBEDA di tiap tabel — 'paid', 'Pendaftaran
         * Diterima', 'pembayaran diterima' — dan itu tidak bisa dirapikan
         * dari sini tanpa memindahkan ratusan baris yang sudah ada.
         *
         * Disamaratakan ke satu kata, sebagian layanan tidak akan pernah
         * mengabari pelunasannya: statusnya memang tidak pernah berbunyi
         * seperti itu, dan tidak ada yang gagal — pesannya cuma tidak
         * berangkat.
         */
        $lunas = [
            'webinar_eksklusif' => 'paid',
            'scopus_camp' => 'Pendaftaran Diterima',
            'bibliometrik' => 'Pendaftaran Diterima',
            'scopus_kafe' => 'pembayaran diterima',
        ];

        foreach ($lunas as $layanan => $status) {
            [$l, $p, ] = $this->barisnya($layanan);
            $p->forceFill(['status' => $status])->save();

            Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);

            $this->assertTrue(app(KabariWhatsApp::class)->untukStatus($l, $p->refresh()),
                $layanan . ' tidak mengabari pelunasannya pada status "' . $status . '".');

            $this->assertStringContainsString('🎉 *Pembayaran Anda sudah kami terima!*',
                (string) $this->pesanTerakhir(),
                $layanan . ' mengirim pesan yang bukan pemberitahuan pelunasan.');
        }
    }

    #[Test]
    public function pembatalan_oleh_panitia_tidak_menuduh_orangnya_tidak_membayar(): void
    {
        /*
         * Di Scopus Camp, 'Pendaftaran Dibatalkan' adalah KEPUTUSAN PANITIA,
         * dan alasannya bisa apa saja: pesertanya mengundurkan diri, kelasnya
         * tidak jalan, berkasnya tidak lengkap, dananya dikembalikan.
         *
         * Pesannya karena itu TIDAK BOLEH menyebut sebab apa pun — dan
         * khususnya tidak boleh menyebut sebab yang paling menyinggung:
         * bahwa orangnya tidak membayar. Sebagian dari mereka sudah, dan
         * panitia yang menerima balasan marahnya tidak tahu dari mana
         * tuduhan itu datang.
         *
         * Pesan yang menyebut tenggat terlewat hanya dikirim perintah
         * scopus-camp:kedaluwarsakan, satu-satunya tempat yang sebabnya benar
         * menurut saringannya sendiri.
         */
        [$l, $p, ] = $this->barisnya('scopus_camp');
        $p->forceFill(['status' => 'Pendaftaran Dibatalkan'])->save();

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);

        $this->assertTrue(app(KabariWhatsApp::class)->untukStatus($l, $p->refresh()),
            'Pembatalan oleh panitia tidak dikabari sama sekali.');

        $pesan = (string) $this->pesanTerakhir();

        foreach (['belum masuk sampai batas', 'batas waktu pembayarannya telah terlewat',
            'tidak selesai', 'belum bayar'] as $tuduhan) {
            $this->assertStringNotContainsStringIgnoringCase($tuduhan, $pesan,
                'Pembatalan oleh panitia menuduh orangnya tidak membayar: "' . $tuduhan . '"');
        }

        $this->assertStringContainsString('terdapat kekeliruan', $pesan,
            'Pembatalan tidak memberi jalan bagi yang merasa keliru dibatalkan.');
    }

    #[Test]
    public function nama_kelasnya_dieja_sama_dengan_faktur_dan_suratnya(): void
    {
        /*
         * "Nama #nomor" — ejaan yang sama dengan halaman pendaftaran, surat
         * bukti, dan fakturnya. Disebut berbeda di sini, orang yang
         * mencocokkan pesan WhatsApp-nya dengan fakturnya mengira keduanya
         * dua pendaftaran yang berbeda, lalu bertanya mana yang berlaku.
         */
        [$l, $p, $a] = $this->barisnya('scopus_camp');

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
        app(KabariWhatsApp::class)->pendaftaranMasuk($l, $p, $a);

        $this->assertStringContainsString($a->nama . ' #' . $a->nama_ke,
            (string) $this->pesanTerakhir(),
            'Nama kelasnya dieja berbeda dari faktur dan suratnya.');
    }

    #[Test]
    public function angkatan_yang_tidak_diserahkan_dicari_sendiri(): void
    {
        /*
         * Ditambahkan karena pemanggilnya SEMPAT menyerahkan
         * $pendaftaran->kategori — relasi yang tidak ada di model itu.
         * Eloquent memulangkan null tanpa galat, dan pesannya berbunyi
         * "pendaftaran Anda untuk *Scopus Camp*" alih-alih menyebut kelas
         * dan tanggalnya.
         *
         * Tidak ada yang gagal, tidak ada yang tercatat; yang menerimanya
         * cuma tidak tahu kelas mana yang ia bayar — dan di layanan ini satu
         * orang bisa mendaftar dua kelas.
         */
        [$l, $p, $a] = $this->barisnya('scopus_camp');

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);

        // Angkatannya SENGAJA tidak diserahkan.
        app(KabariWhatsApp::class)->pendaftaranMasuk($l, $p);

        $this->assertStringContainsString($a->nama, (string) $this->pesanTerakhir(),
            'Angkatan yang tidak diserahkan tidak dicari sendiri; pesannya menyebut '
            . 'nama layanan alih-alih nama kelasnya.');
    }

    #[Test]
    public function keempat_layanan_memperingatkan_penipuan_dan_menyebut_nomor_resmi(): void
    {
        /*
         * Orang yang baru saja mendaftar adalah sasaran empuk: ia memang
         * sedang menunggu dihubungi soal pembayaran, jadi siapa pun yang
         * menyapa mengaku Rumah Scopus akan dipercaya.
         *
         * Keempat layanan, bukan sebagiannya — yang ditipu belum tentu
         * mendaftar lewat layanan yang kebetulan memuat peringatannya.
         */
        foreach (self::SEMUA as $layanan) {
            [$l, $p, $a] = $this->barisnya($layanan);

            Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
            app(KabariWhatsApp::class)->pendaftaranMasuk($l, $p, $a);

            $pesan = (string) $this->pesanTerakhir();

            $this->assertStringContainsString('Hati-hati penipuan', $pesan,
                $layanan . ' tidak memperingatkan penipuan.');

            $this->assertStringContainsString('0889-8356-7819', $pesan,
                $layanan . ' tidak menyebut nomor admin resmi yang bisa dihubungi.');

            $this->assertStringContainsString('jangan dibalas', $pesan,
                $layanan . ' tidak melarang membalas, padahal pengirimnya nomor sistem.');
        }
    }

    #[Test]
    public function tautan_daftar_ulang_tidak_melempar_untuk_layanan_tanpa_angkatan(): void
    {
        /*
         * Rute borang tiga layanan menuntut parameter angkatan; Scopus Kafe
         * tidak menerimanya sama sekali. Diserahkan salah, route() MELEMPAR
         * — dan lemparannya terjadi justru di satu-satunya baris yang berisi
         * jalan keluar bagi orang yang pembayarannya gagal.
         *
         * Diuji lewat layanan bergerbang tanpa angkatan: itu bentuk yang
         * paling mungkin terjadi, yaitu saat angkatannya sudah terhapus.
         */
        [$l, $p, ] = $this->barisnya('scopus_camp');
        $p->forceFill(['kategori_id' => null])->save();

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);

        $this->assertTrue(app(KabariWhatsApp::class)->kursiDilepas($l, $p->refresh()),
            'Pesan kursi dilepas gagal dirakit saat angkatannya tidak ada.');

        $this->assertStringContainsString('/Scopus-Camp', (string) $this->pesanTerakhir(),
            'Pesannya tidak memuat jalan untuk mendaftar lagi.');
    }

    #[Test]
    public function gagal_kirim_tidak_melempar_ke_pemanggilnya(): void
    {
        /*
         * Dipanggil dari jalur pendaftaran dan dari pemberitahuan gerbang
         * pembayaran. Lemparan di sini membatalkan pendaftaran yang barisnya
         * sudah tersimpan, atau membuat kami menjawab bukan-200 ke DOKU —
         * yang lalu mengulang pemberitahuan pembayaran yang sudah tercatat,
         * dan pengulangannya mengirim surat kedua ke orang yang sama.
         */
        foreach (self::SEMUA as $layanan) {
            [$l, $p, $a] = $this->barisnya($layanan);

            Http::fake(['api.fonnte.com/*' => fn () => throw new \RuntimeException('jaringan mati')]);

            $this->assertFalse(app(KabariWhatsApp::class)->pendaftaranMasuk($l, $p, $a),
                $layanan . ': kegagalan jaringan terbaca sebagai terkirim.');
        }
    }

    #[Test]
    public function tiap_jalur_pendaftaran_benar_benar_memanggil_perakitnya(): void
    {
        /*
         * Pin kontrak atas SUMBERNYA, dan sengaja.
         *
         * Ketiga borang ini menyimpan barisnya lalu mengirim surat; yang
         * terlepas kalau panggilan WhatsApp-nya hilang bukan sesuatu yang
         * bisa diukur tanpa menembus borangnya sendiri — unggahan bukti,
         * kuota, kode diskon, dan tiga sesi Scopus Kafe sekaligus.
         *
         * Yang dijaga: tidak ada satu pun dari empat jalur pendaftaran yang
         * boleh diam. Hilangnya satu panggilan tidak mengeluarkan galat apa
         * pun — layanan itu cuma berhenti mengabari, dan baru ketahuan dari
         * pendaftar yang mengadu.
         */
        $jalur = [
            'app/Http/Controllers/Publict/PublicScopusCampController.php' => "pendaftaranMasuk('scopus_camp'",
            'app/Http/Controllers/Publict/PublicAnalisisBibliometrikController.php' => "pendaftaranMasuk('bibliometrik'",
            'app/Http/Controllers/Publict/PublicScopusKafeController.php' => "pendaftaranMasuk('scopus_kafe'",
            'app/Http/Controllers/Publict/PublicWebinarEksklusifController.php' => "pendaftaranMasuk('webinar_eksklusif'",
        ];

        foreach ($jalur as $berkas => $panggilan) {
            $this->assertStringContainsString(
                $panggilan,
                (string) file_get_contents(base_path($berkas)),
                $berkas . ' tidak lagi mengabari pendaftarnya lewat WhatsApp.'
            );
        }

        // Pelunasan lewat gerbang DAN lewat layar panitia, dua jalur yang
        // berbeda sama sekali — dan keduanya harus mengabari.
        $this->assertStringContainsString("->lunas('scopus_camp'",
            (string) file_get_contents(base_path('app/Http/Controllers/Publict/PublicScopusCampController.php')),
            'Pelunasan Scopus Camp lewat DOKU tidak mengabari pendaftarnya.');

        $this->assertStringContainsString('->untukStatus($layanan',
            (string) file_get_contents(base_path('app/Actions/Pendaftaran/UbahStatusPendaftaran.php')),
            'Perpindahan status dari layar panitia tidak mengabari pendaftarnya.');
    }

    #[Test]
    public function sapuan_scopus_camp_dibatasi_supaya_nomornya_tidak_diblokir(): void
    {
        /*
         * Yang membuat nomor WhatsApp diblokir bukan banyaknya pesan dalam
         * sehari melainkan LEDAKAN: puluhan pesan beruntun dalam hitungan
         * detik, ke nomor berbeda, berbunyi hampir sama.
         *
         * Perintah ini satu-satunya tempat di Scopus Camp yang bisa meledak —
         * ia memindahkan puluhan pendaftaran sekaligus. Diblokir, yang hilang
         * bukan cuma pesan kedaluwarsa: SELURUH pemberitahuan kelima layanan
         * lewat nomor yang sama, termasuk tagihan yang umurnya 60 menit.
         */
        config()->set('services.fonnte.batas_sapuan', 2);

        // Jedanya dimatikan DI UJI saja; dibiarkan 25 detik, rangkaian uji
        // ini sendiri tertidur hampir satu menit.
        config()->set('services.fonnte.jeda_sapuan_detik', 0);

        $angkatan = $this->angkatanCamp();

        for ($i = 0; $i < 5; $i++) {
            PendaftaranScopusCamp::create([
                'token' => Str::random(30),
                'id_transaksi' => 'CAMP-SAPU-' . Str::upper(Str::random(8)),
                'kategori_id' => $angkatan->getKey(),
                'nama' => 'Pendaftar Sapu ' . $i,
                'email' => 'sapu' . Str::random(5) . '@contoh.test',
                'telp' => '081234567890',
                'jumlah_pendaftar' => 1,
                'total_pembayaran' => '5500000',
                'cara_bayar' => 'doku',
                'status' => 'diproses',
                'kedaluwarsa_pada' => now()->subHours($i + 1),
            ]);
        }

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);

        $this->artisan('scopus-camp:kedaluwarsakan')->assertSuccessful();

        $terkirim = 0;

        foreach (Http::recorded() as [$permintaan, $jawaban]) {
            if (str_contains($permintaan->url(), 'fonnte.com')) {
                $terkirim++;
            }
        }

        $this->assertSame(2, $terkirim,
            'Sapuan Scopus Camp tidak dibatasi; satu kali jalan mengirim seluruh tumpukan.');
    }

    #[Test]
    public function yang_tertinggal_batas_sapuan_kursinya_belum_dilepas(): void
    {
        /*
         * Pasangan wajib dari uji di atas, dan yang paling mudah salah.
         *
         * Batasnya HARUS menggigit pada baris yang diproses, bukan pada pesan
         * yang dikirim. Dipasang pada pesannya, tiga orang yang tertinggal
         * tetap kehilangan kursinya tanpa pernah dikabari — dan barisnya
         * sudah keluar dari saringan perintah ini, jadi tidak ada jalankan
         * berikutnya yang akan melihatnya lagi.
         *
         * Yang diukur: tiga sisanya masih berstatus awal, jadi jalankan
         * berikutnya sepuluh menit kemudian mengambilnya.
         */
        config()->set('services.fonnte.batas_sapuan', 2);
        config()->set('services.fonnte.jeda_sapuan_detik', 0);

        $angkatan = $this->angkatanCamp();
        $kunci = [];

        for ($i = 0; $i < 5; $i++) {
            $kunci[] = PendaftaranScopusCamp::create([
                'token' => Str::random(30),
                'id_transaksi' => 'CAMP-SISA-' . Str::upper(Str::random(8)),
                'kategori_id' => $angkatan->getKey(),
                'nama' => 'Pendaftar Sisa ' . $i,
                'email' => 'sisa' . Str::random(5) . '@contoh.test',
                'telp' => '081234567890',
                'jumlah_pendaftar' => 1,
                'total_pembayaran' => '5500000',
                'cara_bayar' => 'doku',
                'status' => 'diproses',
                'kedaluwarsa_pada' => now()->subHours($i + 1),
            ])->getKey();
        }

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);

        $this->artisan('scopus-camp:kedaluwarsakan')->assertSuccessful();

        $masihAwal = PendaftaranScopusCamp::whereIn('id', $kunci)
            ->where('status', 'diproses')
            ->count();

        $this->assertSame(3, $masihAwal,
            'Batasnya dipasang pada pesannya, bukan pada barisnya: kursi orang yang '
            . 'tertinggal sudah dilepas tanpa ia pernah dikabari, dan barisnya tidak '
            . 'akan dilihat jalankan berikutnya.');
    }

    #[Test]
    public function pesan_otomatis_dan_draf_panitia_dirakit_dari_satu_naskah(): void
    {
        /*
         * INTI permintaan pemiliknya, dan satu-satunya uji di berkas ini yang
         * membandingkan dua saluran.
         *
         * Satu pendaftar menerima DUA pesan tentang satu hal: yang otomatis
         * begitu borangnya tersimpan, lalu yang diketik panitia saat
         * menindaklanjuti. Dirakit dari dua naskah, keduanya akan berbeda
         * perlahan — dan yang paling mudah berselisih justru NOMINALNYA,
         * satu-satunya angka di pesan itu yang tidak boleh diragukan.
         *
         * Yang dibandingkan bagian-bagian yang memang harus sama, bukan
         * untaiannya utuh: draf panitia memuat satu kalimat perkenalan yang
         * sengaja tidak ada di pesan mesin — tidak ada orang yang sedang
         * menulisnya, dan kakinya sendiri sudah berkata pesan itu otomatis.
         */
        [$l, $p, $angkatan] = $this->barisnya('scopus_camp');

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
        app(KabariWhatsApp::class)->pendaftaranMasuk($l, $p, $angkatan);

        $otomatis = (string) $this->pesanTerakhir();

        Pendaftaran::lupakan();
        $union = Pendaftaran::kueri()
            ->where('layanan', 'scopus_camp')->where('id', $p->getKey())->first();

        $this->assertNotNull($union, 'Barisnya tidak terbaca dari kueri gabungan.');

        $draf = \App\Support\PesanWaPendaftaran::tindakLanjut($union, null);

        $sama = [
            '🎉 *Selamat, pendaftaran Anda sudah berhasil!*',
            '📩 Halo Bapak/Ibu ' . $p->nama,
            '✅ *Langkah selanjutnya:*',
            'Total pembayaran: *Rp ' . number_format((int) $p->total_pembayaran, 0, ',', '.') . '*',
            route('public.scopuscamp.status', $p->getKey()),
            'Salam Q1 ✨',
        ];

        foreach ($sama as $bagian) {
            $this->assertStringContainsString($bagian, $otomatis,
                'Pesan otomatisnya kehilangan bagian: ' . $bagian);

            $this->assertStringContainsString($bagian, $draf,
                'Draf panitianya kehilangan bagian: ' . $bagian);
        }
    }

    #[Test]
    public function tidak_ada_nomor_rekening_di_pesan_layanan_bergerbang(): void
    {
        /*
         * Naskah aslinya menyuruh mentransfer ke Bank BRI dengan nomor
         * rekening yang diketik di dalamnya. Pemiliknya meminta bagian itu
         * diganti tautan pembayaran.
         *
         * Dijaga karena kembalinya tidak mengeluarkan galat apa pun, dan
         * akibatnya paling mahal dari seluruh berkas ini: dua layanan ini
         * TIDAK punya rekening yang dicocokkan dengan tangan, jadi uang yang
         * masuk ke nomor yang tertulis di pesan tidak akan pernah tercatat
         * sebagai pembayaran pendaftaran itu — dan yang mengirimnya merasa
         * sudah membayar.
         */
        foreach (self::BERGERBANG as $layanan) {
            [$l, $p, $angkatan] = $this->barisnya($layanan);

            Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
            app(KabariWhatsApp::class)->pendaftaranMasuk($l, $p, $angkatan);

            $pesan = (string) $this->pesanTerakhir();

            foreach (['No. Rek', 'Bank BRI', 'A.n:', 'rekening berikut'] as $kata) {
                $this->assertStringNotContainsString($kata, $pesan,
                    $layanan . ' menyuruh transfer ke rekening, padahal pembayarannya '
                    . 'lewat gerbang: "' . $kata . '"');
            }

            // Deretan angka panjang — bentuk nomor rekening — tidak boleh ada
            // sama sekali. Nominalnya bertitik, nomor pendaftarannya bertanda
            // hubung, jadi tidak ada yang sah berbentuk seperti ini.
            $this->assertSame(0, preg_match('/\b\d{9,}\b/', $pesan),
                $layanan . ' memuat deretan angka panjang yang terbaca sebagai nomor rekening.');
        }
    }

    #[Test]
    public function potongan_harga_menyebut_harga_normalnya_dari_data(): void
    {
        /*
         * Naskah aslinya menyebut "harga normal Rp 1.875.000" dan "20
         * pendaftar pertama" sebagai angka tetap. Keduanya TIDAK diketik di
         * perakitnya:
         *
         *   - harga normalnya dihitung dari total + potongannya. Harga tiap
         *     angkatan berbeda, dan satu angka yang diketik akan salah untuk
         *     semua angkatan selain satu;
         *
         *   - batas pendaftarnya dibaca dari promo_layanan.kuota, dan terukur
         *     24 Okt 2026 keempat promo yang ada BERNILAI NULL. Ditulis tetap
         *     "20", pesannya mengancam dengan batas yang tidak pernah
         *     ditegakkan sistemnya sendiri — lalu yang terlambat tetap
         *     mendapat harga diskon, dan ancaman kita berikutnya tidak lagi
         *     dipercaya.
         */
        [$l, $p, $angkatan] = $this->barisnya('scopus_camp');

        $p->forceFill([
            'kode_diskon' => 'HEMAT876',
            'nominal_diskon' => 876000,
            'total_pembayaran' => '4624000',
        ])->save();

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
        app(KabariWhatsApp::class)->pendaftaranMasuk($l, $p->refresh(), $angkatan);

        $pesan = (string) $this->pesanTerakhir();

        $this->assertStringContainsString('dengan diskon sudah berhasil', $pesan,
            'Pendaftaran berdiskon tidak disebut berdiskon di judulnya.');

        $this->assertStringContainsString('📌 *Catatan penting:*', $pesan,
            'Catatan soal potongannya tidak ada.');

        $this->assertStringContainsString('HEMAT876', $pesan,
            'Kode potongan yang dipakai tidak disebut, padahal itu yang ditanyakan '
            . 'orangnya saat harganya berbeda dari yang ia lihat di halaman.');

        // 4.624.000 + 876.000 = 5.500.000
        $this->assertStringContainsString('harga normal *Rp 5.500.000*', $pesan,
            'Harga normalnya tidak dihitung dari total + potongannya.');

        $this->assertStringNotContainsString('pendaftar pertama', $pesan,
            'Pesannya mengancam dengan batas jumlah pendaftar, padahal tidak ada '
            . 'satu promo pun yang kuotanya disetel.');
    }

    #[Test]
    public function tanpa_potongan_tidak_ada_catatan_penting(): void
    {
        /*
         * Pasangan terbalik: yang membayar harga penuh tidak diberi catatan
         * soal potongan yang tidak pernah ia dapat. Kalimat "harganya kembali
         * ke harga normal" di situ membingungkan — harga yang ia lihat SUDAH
         * harga normalnya.
         */
        [$l, $p, $angkatan] = $this->barisnya('scopus_camp');

        $this->assertSame(0, (int) $p->nominal_diskon,
            'Data ujinya sudah berdiskon, jadi uji ini tidak menguji apa pun.');

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
        app(KabariWhatsApp::class)->pendaftaranMasuk($l, $p, $angkatan);

        $pesan = (string) $this->pesanTerakhir();

        $this->assertStringNotContainsString('Catatan penting', $pesan,
            'Yang membayar harga penuh diberi catatan soal potongan yang tidak ada.');

        $this->assertStringNotContainsString('harga normal', $pesan,
            'Yang membayar harga penuh diancam kembali ke harga normal.');

        $this->assertStringContainsString('🎉 *Selamat, pendaftaran Anda sudah berhasil!*', $pesan,
            'Judulnya menyebut diskon untuk pendaftaran tanpa potongan.');
    }

    #[Test]
    public function nama_layanan_tidak_disebut_dua_kali(): void
    {
        /*
         * Terukur 24 Okt 2026: 46 dari 375 angkatan Scopus Camp, 10 dari 10
         * angkatan Analisis Bibliometrik, dan 1 dari 2 sesi Webinar Eksklusif
         * memberi nama angkatannya dengan menyertakan nama layanannya sendiri
         * — "Scopus Camp Padang", "Analisis Bibliometrik Batch 12".
         *
         * Tanpa penyaringan, kalimat pembukanya berbunyi "pendaftaran Anda
         * untuk *Scopus Camp — Scopus Camp Padang #4*". Di Analisis
         * Bibliometrik itu terjadi pada SELURUH pendaftarannya, bukan
         * sebagian.
         */
        [$l, $p, $angkatan] = $this->barisnya('scopus_camp');

        $angkatan->forceFill(['nama' => 'Scopus Camp Padang'])->save();

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
        app(KabariWhatsApp::class)->pendaftaranMasuk($l, $p, $angkatan->refresh());

        $pesan = (string) $this->pesanTerakhir();

        $this->assertStringContainsString('*Scopus Camp Padang #', $pesan,
            'Nama kelasnya tidak disebut.');

        $this->assertStringNotContainsString('Scopus Camp — Scopus Camp', $pesan,
            'Nama layanannya disebut dua kali dalam satu kalimat.');
    }

    /**
     * Keempat kabar statusnya, dirakit lalu diambil isi pesannya.
     *
     * @return array<string, string>
     */
    private function semuaKabar(string $layanan): array
    {
        [$l, $p, $angkatan] = $this->barisnya($layanan);
        $wa = app(KabariWhatsApp::class);

        $pesan = [];

        foreach (['pendaftaranMasuk', 'lunas', 'gagalBayar', 'kursiDilepas'] as $cara) {
            Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);

            if (! $wa->{$cara}($l, $p, $angkatan)) {
                continue;
            }

            $pesan[$cara] = (string) $this->pesanTerakhir();
        }

        return $pesan;
    }

    #[Test]
    public function tidak_ada_pesan_otomatis_yang_menyuruh_membalas(): void
    {
        /*
         * Kakinya berkata "mohon jangan dibalas", sebab nomor pengirimnya
         * nomor SISTEM. Pesan yang di baris lain menyuruh membalas membantah
         * kakinya sendiri — dan yang membalas lalu menunggu jawaban yang
         * tidak akan pernah datang, sementara masalahnya (uangnya terpotong,
         * tempatnya hilang) tetap tidak tertangani.
         *
         * DIBANDINGKAN TANPA MEMBEDAKAN HURUF, dan itu hasil koreksi:
         * penjagaan lamanya melarang "Balas pesan ini" berhuruf besar saja,
         * sementara tiga penutup milik draf panitia berbunyi "mohon *b*alas
         * pesan ini" — lolos seluruhnya. Penutup itu kini dipakai bersama
         * kedua saluran, jadi kekeliruannya bisa sampai ke pesan otomatis.
         */
        foreach (self::BERGERBANG as $layanan) {
            foreach ($this->semuaKabar($layanan) as $cara => $pesan) {
                $this->assertStringNotContainsStringIgnoringCase('balas pesan ini', $pesan,
                    $layanan . '/' . $cara . ' menyuruh membalas, padahal kakinya melarang.');
            }
        }
    }

    #[Test]
    public function nama_gerbangnya_tidak_pernah_bocor_ke_pendaftar(): void
    {
        /*
         * Nama gerbangnya urusan internal: yang perlu diketahui pendaftar
         * cuma bahwa pembayarannya diselesaikan lewat tautan kami. Disebut
         * namanya, pesan ini berbohong pada hari gerbangnya diganti — dan
         * yang menerimanya mencari halaman yang sudah tidak dipakai.
         *
         * Bocornya tidak lewat kalimat yang diketik melainkan lewat KOLOM:
         * nilai mentah `cara_bayar` memang berbunyi "doku", dan labelnya di
         * katalog berbunyi "Pembayaran daring (DOKU)". Dicetak sebagai "Cara
         * bayar", keduanya sampai ke pendaftar.
         */
        foreach (self::BERGERBANG as $layanan) {
            [$l, $p, $angkatan] = $this->barisnya($layanan);

            // Kanal yang benar DIKOSONGKAN — justru keadaan yang membuat
            // perakitnya jatuh ke nilai mentah kolomnya.
            $p->forceFill(['bayar_kanal' => null, 'cara_bayar' => 'doku'])->save();

            Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
            app(KabariWhatsApp::class)->lunas($l, $p->refresh(), $angkatan);

            $this->assertStringNotContainsStringIgnoringCase('doku',
                (string) $this->pesanTerakhir(),
                $layanan . ': nama gerbangnya bocor ke pesan pelunasan.');
        }
    }

    #[Test]
    public function keempat_kabarnya_berbentuk_sama(): void
    {
        /*
         * Satu pendaftar menerima beberapa pesan ini sepanjang satu
         * pendaftaran: tagihan, lalu pelunasan, atau tagihan lalu kabar
         * tempatnya dilepas. Bentuk yang berganti-ganti membuat ia
         * mencurigai salah satunya palsu — dan kecurigaan itu beralasan,
         * sebab memang ada pihak yang mengatasnamakan lembaga ini.
         *
         * Sebelum ini keempatnya berbeda sungguhan: yang tagihan berjudul
         * "🎉 *Selamat…*" bersapaan "📩 Halo Bapak/Ibu", sementara tiga
         * sisanya berjudul "*Pembayaran diterima* — Rumah Scopus Foundation"
         * bersapaan "Halo <nama>,".
         */
        /*
         * DIPATOK KE YANG BERKUOTA, bukan ke yang bergerbang.
         *
         * Scopus Kafe bergerbang tetapi tidak berkuota, jadi dua dari empat
         * kabarnya — gagal bayar dan tempat dilepas — memang sengaja tidak
         * pernah dikirim. Diuji sebagai empat, uji ini akan menuntut pesan
         * yang justru tidak boleh ada.
         */
        foreach (self::BERKUOTA as $layanan) {
            $semua = $this->semuaKabar($layanan);

            $this->assertCount(4, $semua,
                $layanan . ': ada kabar yang tidak terkirim sama sekali.');

            foreach ($semua as $cara => $pesan) {
                $ikut = $layanan . '/' . $cara;

                $this->assertStringContainsString('📩 Halo Bapak/Ibu ', $pesan,
                    $ikut . ' tidak memakai sapaan yang sama dengan pesan lainnya.');

                $this->assertStringEndsWith('rekening pribadi, dan pembayaran hanya lewat '
                    . 'tautan resmi di pesan maupun email ini.', $pesan,
                    $ikut . ' tidak ditutup peringatan penipuannya.');

                $this->assertStringContainsString('Salam Q1 ✨', $pesan,
                    $ikut . ' kehilangan tanda tangan lembaganya.');

                $this->assertStringContainsString('jangan dibalas', $pesan,
                    $ikut . ' tidak melarang membalas, padahal pengirimnya nomor sistem.');

                $this->assertStringContainsString('0889-8356-7819', $pesan,
                    $ikut . ' tidak menyebut nomor admin resmi yang bisa dihubungi.');
            }
        }
    }

    #[Test]
    public function kabar_buruk_tidak_ditutup_harapan_publikasi(): void
    {
        /*
         * "Semoga ini jadi langkah awal menuju publikasi" kepada orang yang
         * baru saja ditolak atau dananya dikembalikan terbaca sebagai
         * sindiran — ia justru tidak ikut.
         *
         * Tanda tangannya tetap ada di keduanya: pesan tanpa "Salam Q1"
         * terbaca seperti datang dari orang lain, justru di pesan yang paling
         * mungkin dicurigai penipuan.
         */
        $buruk = ['Pendaftaran Ditolak', 'Pendaftaran Refund', 'Pendaftaran Dibatalkan'];

        foreach ($buruk as $status) {
            [$l, $p, ] = $this->barisnya('scopus_camp');
            $p->forceFill(['status' => $status])->save();

            Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);

            $this->assertTrue(app(KabariWhatsApp::class)->untukStatus($l, $p->refresh()),
                'Status "' . $status . '" tidak dikabari lewat WhatsApp sama sekali.');

            $pesan = (string) $this->pesanTerakhir();

            $this->assertStringNotContainsString('langkah awal menuju publikasi', $pesan,
                'Status "' . $status . '" ditutup harapan publikasi, padahal orangnya '
                . 'justru tidak ikut.');

            $this->assertStringContainsString('Salam Q1 ✨', $pesan,
                'Status "' . $status . '" kehilangan tanda tangan lembaganya.');
        }
    }

    #[Test]
    public function penolakan_tidak_memasang_nominal_yang_terbaca_sebagai_tagihan(): void
    {
        /*
         * Sebagian yang ditolak belum pernah membayar apa pun. Angka yang
         * berdiri sendiri di pesan penolakan terbaca sebagai tagihan —
         * terutama oleh mereka — dan yang mentransfernya mengirim uang untuk
         * pendaftaran yang sudah tidak ada.
         */
        [$l, $p, ] = $this->barisnya('scopus_camp');
        $p->forceFill(['status' => 'Pendaftaran Ditolak'])->save();

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
        app(KabariWhatsApp::class)->untukStatus($l, $p->refresh());

        $pesan = (string) $this->pesanTerakhir();

        $this->assertStringNotContainsString(
            number_format((int) $p->total_pembayaran, 0, ',', '.'), $pesan,
            'Pesan penolakan memasang nominalnya, yang terbaca sebagai tagihan.');
    }

    #[Test]
    public function grup_peserta_hanya_untuk_yang_sudah_jadi_peserta(): void
    {
        /*
         * Grupnya akan terisi orang yang tempatnya belum ada, dan
         * mengeluarkan mereka kembali satu per satu pekerjaan panitia yang
         * tidak perlu ada.
         *
         * Isian yang BUKAN tautan juga tidak boleh tercetak: kolomnya varchar
         * bebas, dan terukur di basis data ia lebih sering berisi catatan
         * panitia daripada alamat — "menyusul", "link nyusul". Dicetak apa
         * adanya, yang terkirim adalah "bergabung ke grup melalui tautan
         * berikut: menyusul".
         */
        [$l, $p, $angkatan] = $this->barisnya('scopus_camp');
        $angkatan->forceFill(['group_wa' => 'https://chat.whatsapp.com/UjiGrup123'])->save();

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
        app(KabariWhatsApp::class)->lunas($l, $p, $angkatan->refresh());

        $this->assertStringContainsString('https://chat.whatsapp.com/UjiGrup123',
            (string) $this->pesanTerakhir(),
            'Yang sudah lunas tidak diberi tautan grupnya.');

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
        app(KabariWhatsApp::class)->kursiDilepas($l, $p, $angkatan);

        $this->assertStringNotContainsString('chat.whatsapp.com',
            (string) $this->pesanTerakhir(),
            'Yang tempatnya dilepas tetap diberi tautan grup peserta.');

        $angkatan->forceFill(['group_wa' => 'menyusul'])->save();

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
        app(KabariWhatsApp::class)->lunas($l, $p, $angkatan->refresh());

        $this->assertStringNotContainsString('tautan berikut:',
            (string) $this->pesanTerakhir(),
            'Isian yang bukan tautan tercetak sebagai tautan grup.');
    }
}
