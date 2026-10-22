<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Blog: /blog dan halaman kategorinya.
 *
 * Dirombak 19 Okt 2026 mengikuti rupa beranda. Yang dijaga di sini bukan
 * seleranya — rupa boleh berubah kapan saja — melainkan empat hal yang
 * kalau rusak tidak mengeluarkan satu pun galat:
 *
 *   1. tulisan yang BELUM terbit ikut terkirim ke peramban;
 *   2. tulisan yang sudah terbit TIDAK muncul di daftarnya;
 *   3. keripik kategori mengantar ke halaman yang kosong;
 *   4. halaman kategori berganti rupa dari /blog, sehingga perpindahannya
 *      terbaca seperti keluar dari situsnya.
 */
class BlogTest extends TestCase
{
    use DatabaseTransactions;

    private const GAYA = 'resources/views/public/article/partials/gaya.blade.php';

    /** Satu tulisan buatan uji; judulnya acak supaya tidak bentrok data nyata. */
    private function tulisan(array $ganti = []): object
    {
        $kategori = DB::table('categories_artikel')->value('id');
        $penulis = DB::table('users')->value('id');

        $this->assertNotNull($kategori, 'Tidak ada kategori artikel untuk diuji.');

        $data = array_merge([
            'user_id' => $penulis,
            'categories_artikel_id' => $kategori,
            'token' => Str::random(16),
            'judul' => 'UJI Blog ' . Str::random(8),
            'kata_kunci' => 'uji',
            'gambar_depan' => 'tidak-ada.png',
            'gambar_cover' => 'tidak-ada.png',
            'isi' => '<p>' . str_repeat('kata ', 60) . '</p>',
            'dilihat' => 0,
            'status' => 'publish',
            'created_at' => now(),
            'updated_at' => now(),
        ], $ganti);

        $id = DB::table('artikel')->insertGetId($data);

        return (object) ($data + ['id' => $id]);
    }

    #[Test]
    public function blognya_terbuka(): void
    {
        $this->get('/blog')->assertOk();
    }

    #[Test]
    public function tulisan_yang_belum_terbit_tidak_pernah_sampai_ke_peramban(): void
    {
        /*
         * Kuerinya dulu mengambil SELURUH baris artikel apa pun statusnya;
         * yang menyaring 'publish' cuma tampilannya, di dalam @php per
         * kategori.
         *
         * Artinya draf yang belum selesai tetap terkirim utuh ke peramban —
         * tersembunyi dari mata, tetapi ada di sumber halaman BESERTA
         * ISINYA. Siapa pun yang menekan "lihat sumber" membacanya, dan
         * penulisnya tidak akan pernah tahu.
         */
        $draf = $this->tulisan(['status' => 'draft', 'judul' => 'RAHASIA Draf ' . Str::random(6)]);

        $isi = (string) $this->get('/blog')->assertOk()->getContent();

        $this->assertStringNotContainsString($draf->judul, $isi,
            'Tulisan berstatus draft ikut terkirim ke peramban. Isinya terbaca '
            . 'lewat "lihat sumber" meski tidak tergambar di layar.');
    }

    #[Test]
    public function daftarnya_tidak_diam_diam_memotong_tulisan(): void
    {
        /*
         * Tampilannya dulu mengelompokkan per kategori lalu take(3). Kategori
         * Journal punya lima tulisan, jadi DUA di antaranya tidak pernah
         * muncul di /blog sama sekali — terukur 10 kartu untuk 12 tulisan
         * terbit.
         *
         * Hilangnya tidak bergejala: halamannya tetap penuh dan rapi, dan
         * yang menulisnya mengira tulisannya sudah tayang.
         *
         * Daftarnya SEKARANG berhalaman, jadi yang dijaga bukan lagi "semua
         * muncul sekaligus" melainkan dua hal yang lebih tepat: jumlah yang
         * DIAKUI halamannya sama dengan jumlah tulisan terbit, dan halaman
         * pertamanya benar-benar terisi penuh. Potongan diam-diam melanggar
         * salah satunya.
         */
        collect(range(1, 4))->each(fn () => $this->tulisan());

        $terbit = \App\Artikel::where('status', 'publish')->count();

        $isi = (string) $this->get('/blog')->assertOk()->getContent();
        $teks = (string) preg_replace('/\s+/', ' ', strip_tags($isi));

        $this->assertStringContainsString($terbit . ' tulisan', $teks,
            'Halaman tidak mengakui seluruh ' . $terbit . ' tulisan terbit yang ada.');

        // Satu disorot, sisanya berkartu — jadi kartunya satu kurang dari
        // isi halamannya.
        $perHalaman = min($terbit, 9);
        $kartu = substr_count($isi, 'class="blg-kartu"');

        $this->assertSame($perHalaman - 1, $kartu,
            'Halaman pertama memajang ' . $kartu . ' kartu, seharusnya '
            . ($perHalaman - 1) . ' (satu lagi jadi sorotan).');
    }

    #[Test]
    public function keripik_kategori_tidak_pernah_mengantar_ke_halaman_kosong(): void
    {
        /*
         * Kategori boleh dibuat admin sebelum tulisannya ada. Menawarkannya
         * di /blog berarti mengantar orang ke daftar kosong — dan halaman
         * kosong terbaca sebagai situs yang rusak, bukan sebagai kategori
         * yang memang belum diisi.
         */
        $kosong = DB::table('categories_artikel')->insertGetId([
            'user_id' => DB::table('users')->value('id'),
            'token' => Str::random(16),
            'kategori' => 'UJI Kategori Kosong ' . Str::random(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $isi = (string) $this->get('/blog')->assertOk()->getContent();

        $this->assertStringNotContainsString(
            route('blog.topic.kategori', ['categories_artikel_id' => $kosong]), $isi,
            'Keripik kategori yang belum punya tulisan terbit tetap ditawarkan di '
            . '/blog. Yang menekannya mendarat di halaman tanpa isi.');
    }

    #[Test]
    public function halaman_kategori_memakai_rupa_yang_sama_dengan_blog(): void
    {
        /*
         * Keripik di /blog mengantar ke sini, jadi halaman ini yang pertama
         * dibuka orang sesudah melihat rupa barunya. Dibiarkan memakai
         * gradien merah lama, perpindahannya terbaca seperti keluar dari
         * situsnya.
         *
         * Dijaga dari KEDUA halaman memuat partial gaya yang sama, bukan dari
         * warna tertentu: warnanya boleh berganti, yang tidak boleh cuma
         * salah satunya yang berganti.
         */
        $kategori = DB::table('artikel')
            ->where('status', 'publish')
            ->whereNotNull('categories_artikel_id')
            ->value('categories_artikel_id');

        $this->assertNotNull($kategori, 'Tidak ada kategori berisi untuk diuji.');

        $blog = (string) $this->get('/blog')->assertOk()->getContent();
        $halamanKategori = (string) $this->get(
            route('blog.topic.kategori', ['categories_artikel_id' => $kategori])
        )->assertOk()->getContent();

        foreach ([['blog' => $blog, 'nama' => '/blog'], ['blog' => $halamanKategori, 'nama' => 'halaman kategori']] as $h) {
            $this->assertStringContainsString('class="blg"', $h['blog'],
                $h['nama'] . ' tidak lagi memakai pembungkus .blg milik rupa blog.');

            $this->assertStringContainsString('Fraunces', $h['blog'],
                $h['nama'] . ' tidak lagi memuat huruf judulnya; ia akan memakai huruf '
                . 'bawaan templat sementara halaman blog yang satunya memakai Fraunces.');

            $this->assertStringContainsString('--kertas: #fdf5e8', $h['blog'],
                $h['nama'] . ' kehilangan palet blognya.');
        }
    }

    #[Test]
    public function tiap_tulisan_punya_judul_halamannya_sendiri(): void
    {
        /*
         * <title> setiap halaman artikel dulu berbunyi "Artikel | Rumah
         * Scopus" — sama persis untuk kedua belas tulisan. Judul tulisannya
         * sendiri tidak pernah masuk ke sana.
         *
         * Dua akibatnya, dan keduanya diam: tautan yang dibagikan ke
         * WhatsApp muncul sebagai "Artikel | Rumah Scopus" tanpa gambar dan
         * tanpa kalimat, dan di mesin pencari kedua belas tulisan saling
         * bertumpuk sebagai hasil yang kelihatan identik. Halamannya tetap
         * 200, isinya tetap benar, dan tidak ada yang melapor.
         */
        $dua = \App\Artikel::where('status', 'publish')->take(2)->get();

        if ($dua->count() < 2) {
            $this->markTestSkipped('Butuh dua tulisan terbit untuk membandingkan judulnya.');
        }

        $judul = [];

        foreach ($dua as $tulisan) {
            $isi = (string) $this->get(route('blog.topic.blog-single', ['id' => $tulisan->id]))
                ->assertOk()->getContent();

            preg_match('#<title>(.*?)</title>#s', $isi, $tangkap);
            $judul[] = trim($tangkap[1] ?? '');

            $this->assertStringContainsString($tulisan->judul, $tangkap[1] ?? '',
                'Judul halaman tidak memuat judul tulisannya: "' . ($tangkap[1] ?? '') . '"');
        }

        $this->assertNotSame($judul[0], $judul[1],
            'Dua tulisan berbeda memakai judul halaman yang sama persis: "' . $judul[0] . '"');
    }

    #[Test]
    public function tiap_halaman_blog_punya_uraian_dan_pratinjau_bagikan(): void
    {
        /*
         * meta description dan keywords dulu content="" di SELURUH situs,
         * dan tidak ada satu pun tag og:. Tautan yang dibagikan muncul
         * sebagai sepotong alamat mentah: tanpa judul, tanpa gambar, tanpa
         * kalimat — tidak memberi alasan apa pun untuk ditekan.
         */
        $tulisan = \App\Artikel::where('status', 'publish')->first();

        $jalur = ['/blog'];

        if ($tulisan) {
            $jalur[] = route('blog.topic.blog-single', ['id' => $tulisan->id]);
        }

        foreach ($jalur as $alamat) {
            $isi = (string) $this->get($alamat)->assertOk()->getContent();

            foreach ([
                'meta name="description"' => '#<meta name="description" content="([^"]*)"#',
                'meta property="og:title"' => '#<meta property="og:title" content="([^"]*)"#',
                'meta property="og:image"' => '#<meta property="og:image" content="([^"]*)"#',
            ] as $nama => $pola) {
                preg_match($pola, $isi, $tangkap);

                $this->assertNotSame('', trim($tangkap[1] ?? ''),
                    $alamat . ' mengirim ' . $nama . ' yang KOSONG. Tautannya dibagikan '
                    . 'tanpa pratinjau apa pun.');
            }
        }
    }

    #[Test]
    public function pencarian_menemukan_kata_di_badan_tulisan(): void
    {
        /*
         * Sebelum ini /blog tidak punya pencarian sama sekali, dan satu-
         * satunya yang ada — kotak di bilah samping halaman artikel —
         * ber-action="" dengan <input> TANPA atribut name: ditekan, ia
         * memuat ulang halaman yang sama tanpa membawa apa pun. Tidak pernah
         * bisa mencari sejak dipasang, dan yang memakainya menyimpulkan
         * tulisan yang dicarinya memang tidak ada.
         */
        $kata = 'Zebrafosfat' . Str::random(5);

        $tulisan = $this->tulisan([
            'judul' => 'UJI Cari ' . Str::random(6),
            'isi' => '<p>Kalimat pembuka. ' . $kata . ' ada di badan tulisan, bukan di judulnya.</p>',
        ]);

        $isi = (string) $this->get('/blog?cari=' . urlencode($kata))->assertOk()->getContent();

        $this->assertStringContainsString($tulisan->judul, $isi,
            'Pencarian tidak menemukan kata yang ada di badan tulisan. Judul saja '
            . 'tidak cukup: orang mencari "reviewer" atau "sitasi", kata yang hampir '
            . 'selalu ada di isi dan hampir tidak pernah di judul.');
    }

    #[Test]
    public function tanda_persen_tidak_memulangkan_seluruh_tulisan(): void
    {
        /*
         * % dan _ adalah jokernya LIKE. Tanpa di-escape, mengetik satu tanda
         * persen memulangkan SELURUH tulisan — dan yang mengetiknya
         * menyimpulkan pencariannya rusak, bukan bahwa ia baru saja menulis
         * joker.
         */
        $semua = \App\Artikel::where('status', 'publish')->count();

        $isi = (string) $this->get('/blog?cari=%25')->assertOk()->getContent();

        preg_match('/(\d+)\s+hasil untuk/', strip_tags($isi), $tangkap);

        $this->assertNotNull($tangkap[1] ?? null, 'Halaman hasil tidak menyebut jumlahnya.');

        $this->assertLessThan($semua, (int) $tangkap[1],
            'Mencari "%" memulangkan seluruh ' . $semua . ' tulisan; jokernya tidak di-escape.');
    }

    #[Test]
    public function penghitung_baca_benar_benar_naik(): void
    {
        /*
         * Kolom `dilihat` berisi angka sungguhan — 2.783, 2.088, 1.728 —
         * tetapi tidak ada satu baris kode pun yang menaikkannya; ia cuma
         * terdaftar di $fillable. Angkanya beku di nilai yang ditinggal
         * sistem lama, dan tidak ada yang tahu sebab tidak ada yang
         * memajangnya.
         */
        $tulisan = $this->tulisan(['dilihat' => 7]);

        $this->get(route('blog.topic.blog-single', ['id' => $tulisan->id]))->assertOk();

        $this->assertSame(8, (int) DB::table('artikel')->where('id', $tulisan->id)->value('dilihat'),
            'Membuka tulisan tidak menaikkan penghitung bacanya.');
    }

    #[Test]
    public function umpan_rss_sah_dan_hanya_memuat_yang_terbit(): void
    {
        $draf = $this->tulisan(['status' => 'draft', 'judul' => 'RAHASIA RSS ' . Str::random(6)]);
        $terbit = $this->tulisan(['judul' => 'UJI RSS ' . Str::random(6)]);

        $jawaban = $this->get(route('blog.rss'))->assertOk();

        $jawaban->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');

        $xml = (string) $jawaban->getContent();

        /*
         * Diurai sungguhan, bukan dicocokkan teks: satu tanda & yang tidak
         * di-escape di dalam tulisan sudah cukup membuat seluruh umpan
         * ditolak pembacanya — dan pencocokan teks tetap hijau.
         */
        $sebelumnya = libxml_use_internal_errors(true);
        $akar = simplexml_load_string($xml);
        libxml_use_internal_errors($sebelumnya);

        $this->assertNotFalse($akar, 'Umpan RSS bukan XML yang sah; pembaca umpan menolaknya.');

        $this->assertStringContainsString($terbit->judul, $xml, 'Tulisan terbit tidak masuk umpan.');
        $this->assertStringNotContainsString($draf->judul, $xml, 'Draf ikut masuk umpan RSS.');
    }

    #[Test]
    public function tampilan_blog_tidak_memakai_strftime(): void
    {
        /*
         * strftime() sudah usang sejak PHP 8.1 dan DIBUANG di PHP 9 —
         * peladen ini sudah menjalankan 8.4. Ia juga mengabaikan locale
         * Carbon, jadi keluarannya "Friday, 11 July 2025" di halaman yang
         * seluruh kalimatnya Bahasa Indonesia.
         *
         * Dijaga untuk tampilan blog saja. Terukur 19 Okt 2026: fungsi itu
         * masih dipakai di sekitar 47 berkas lain — surat, tagihan, dan
         * layar sisi akun — dan itu pekerjaan tersendiri.
         */
        $berkas = [
            'resources/views/public/article/public.blade.php',
            'resources/views/public/article/publickategori.blade.php',
            'resources/views/public/article/blogsingle.blade.php',
            'resources/views/public/article/rss.blade.php',
            'resources/views/public/article/partials/gaya.blade.php',
        ];

        $kena = [];

        foreach ($berkas as $b) {
            $isi = (string) preg_replace(
                ['#/\*.*?\*/#s', '#\{\{--.*?--\}\}#s'], '',
                (string) file_get_contents(base_path($b))
            );

            if (preg_match('/(?<![\w:>$-])strftime\s*\(/', $isi)) {
                $kena[] = $b;
            }
        }

        $this->assertSame([], $kena,
            "Tampilan blog ini memakai strftime(), yang dibuang di PHP 9:\n- "
            . implode("\n- ", $kena));
    }

    #[Test]
    public function layar_pertama_blog_tidak_tertutup_kepala_situs(): void
    {
        /*
         * Kepala situs berposisi fixed setinggi 82px dan TIDAK memesan ruang
         * untuk dirinya sendiri — beranda mengatasinya dengan padding-top
         * 124px di hero-nya.
         *
         * Blog sempat memakai 70px, dan terukur dua belas piksel teratas
         * stikernya tertutup: yang terlihat cuma potongan bawah lencana yang
         * seperti tergunting salah. Tidak ada galat, dan di layar lebar
         * sekilas tampak disengaja.
         */
        $sumber = (string) preg_replace(
            ['#/\*.*?\*/#s', '#\{\{--.*?--\}\}#s'], '',
            (string) file_get_contents(base_path(self::GAYA))
        );

        $cocok = preg_match('/\.blg-kepala \{[^}]*padding:\s*(\d+)px/s', $sumber, $tangkap);

        $this->assertSame(1, $cocok, 'Aturan padding .blg-kepala tidak ditemukan.');

        $this->assertGreaterThanOrEqual(100, (int) $tangkap[1],
            'Jarak atas layar pertama blog tinggal ' . $tangkap[1] . 'px. Kepala situs '
            . 'setinggi 82px mengambang di atasnya dan akan menutupi isi teratasnya.');
    }
}
