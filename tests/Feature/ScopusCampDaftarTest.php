<?php

namespace Tests\Feature;

use App\CategoriesScopusCamp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Halaman detail angkatan dan borang pendaftarannya.
 *
 * Dirombak 22 Okt 2026 untuk pembaca berusia lanjut: sedikit isian, huruf
 * besar, tombol daftar yang tidak perlu dicari. Yang dijaga di sini bukan
 * seleranya, melainkan hal-hal yang kalau rusak tidak mengeluarkan galat.
 */
class ScopusCampDaftarTest extends TestCase
{
    use DatabaseTransactions;

    private const BORANG = 'resources/views/public/scopus_camp/form_pendaftaran.blade.php';

    private function angkatan(array $ganti = []): CategoriesScopusCamp
    {
        return CategoriesScopusCamp::create(array_merge([
            'layanan' => 'scopus_camp',
            'nama' => 'UJI Camp ' . Str::random(6),
            'nama_ke' => random_int(9000, 9999),
            'mulai' => now()->addDays(4)->toDateString(),
            'selesai' => now()->addDays(6)->toDateString(),
            'lokasi' => 'Yogyakarta',
            'total_kuota' => 20,
            'sisa_kuota' => 20,
            'biaya' => 5500000,
            'status' => 'active',
        ], $ganti));
    }

    private function detail(CategoriesScopusCamp $a): string
    {
        return (string) $this->get(route('public.scopuscamp.Selengkapnya', ['id' => $a->id]))
            ->assertOk()->getContent();
    }

    #[Test]
    public function tanggal_kosong_tidak_pernah_jadi_januari_1970(): void
    {
        /*
         * `selesai` boleh kosong, dan terukur memang ada yang kosong.
         *
         * Versi lama menulis date('d M Y', strtotime($data->selesai)).
         * strtotime(null) memulangkan false, date() membacanya sebagai 0, dan
         * yang tercetak "01 Jan 1970" — benar-benar tampil di bilah kanan
         * halaman ini, di sebelah angkatan yang sedang ditawarkan.
         */
        $tanpaSelesai = $this->angkatan([
            'selesai' => null,
            'mulai' => now()->addDays(2)->toDateString(),
        ]);

        $dibuka = $this->angkatan();

        foreach ([$this->detail($dibuka), $this->detail($tanpaSelesai)] as $isi) {
            $this->assertStringNotContainsString('1970', $isi,
                'Halaman ini mencetak tanggal 1970 — tanda strtotime(null) yang '
                . 'dibaca sebagai awal zaman Unix.');
        }
    }

    #[Test]
    public function tidak_menunjuk_gambar_cadangan_yang_tidak_ada(): void
    {
        /*
         * Dua gambar cadangan dirujuk tampilan lama dan TIDAK ADA berkasnya:
         * ScopusCamp/no-image.jpg di halaman detail dan
         * assets/img/meme/no-image.jpg di borangnya. Terukur enam gambar
         * rusak di satu halaman detail.
         *
         * Di borang yang meminta bukti transfer, gambar rusak terbaca sebagai
         * unggahan yang gagal — tepat pada langkah yang paling bikin ragu.
         */
        $a = $this->angkatan();

        $halaman = [
            'detail' => $this->detail($a),
            'borang' => (string) $this->get(route('public.scopuscamp.formpendaftaran', ['id' => $a->id]))
                ->assertOk()->getContent(),
        ];

        foreach ($halaman as $nama => $isi) {
            foreach (['ScopusCamp/no-image.jpg', 'meme/no-image.jpg'] as $hilang) {
                $this->assertStringNotContainsString('src="' . url($hilang) . '"', $isi,
                    'Halaman ' . $nama . ' menunjuk gambar cadangan ' . $hilang
                    . ' yang tidak ada berkasnya.');
            }
        }
    }

    #[Test]
    public function bilah_kanan_hanya_menawarkan_angkatan_yang_masih_dibuka(): void
    {
        /*
         * Daftarnya dulu `orderBy('created_at','desc')->take(6)` tanpa
         * saringan apa pun: angkatan yang kuotanya habis, yang tanggalnya
         * sudah lewat, dan angkatan yang sedang dibuka sendiri semuanya ikut
         * ditawarkan — diurut menurut kapan barisnya diketik admin, bukan
         * kapan acaranya.
         */
        $ini = $this->angkatan();

        $penuh = $this->angkatan(['sisa_kuota' => 0]);
        $lewat = $this->angkatan(['mulai' => now()->subDay()->toDateString()]);
        $mati = $this->angkatan(['status' => 'non active']);
        $layak = $this->angkatan(['mulai' => now()->addDay()->toDateString()]);

        $isi = $this->detail($ini);

        /*
         * Jendelanya dipotong dari MARKAH bilahnya, bukan dari kemunculan
         * pertama kata "cmp-lain" — yang pertama itu aturan CSS di blok
         * <style> dekat puncak halaman, jadi jendelanya memuat seluruh
         * halaman termasuk judulnya sendiri, dan ujinya merah untuk alasan
         * yang salah.
         */
        $awal = strpos($isi, '<div class="cmp-lain">');

        $this->assertNotFalse($awal, 'Bilah "jadwal lain" tidak tergambar.');

        $bilah = substr($isi, $awal);

        $this->assertStringContainsString($layak->nama, $bilah,
            'Angkatan yang masih dibuka tidak ditawarkan di bilah kanan.');

        foreach ([[$penuh, 'kuotanya habis'], [$lewat, 'tanggalnya sudah lewat'],
                  [$mati, 'statusnya tidak aktif'], [$ini, 'sedang dibuka halamannya sendiri']] as [$a, $sebab]) {
            $this->assertStringNotContainsString($a->nama, $bilah,
                'Angkatan yang ' . $sebab . ' ikut ditawarkan di bilah kanan.');
        }
    }

    #[Test]
    public function borangnya_tidak_lagi_penuh_isian_yang_tak_bisa_diisi(): void
    {
        /*
         * Kartu "Detail Batch" dulu berisi TUJUH <input readonly> — Batch,
         * Lokasi, Sisa Kuota, Mulai, Selesai, Biaya, Kode Unik — ditambah
         * Jumlah Pendaftar yang juga readonly. Semuanya tampak seperti isian
         * yang menunggu diisi, padahal tidak satu pun bisa diubah, dan
         * peladen mengabaikan lima di antaranya.
         *
         * Bagi pembaca berusia lanjut itu tujuh kotak yang harus ditafsirkan
         * dulu sebelum sampai ke empat isian yang benar-benar perlu diisi.
         */
        $a = $this->angkatan();

        $isi = (string) $this->get(route('public.scopuscamp.formpendaftaran', ['id' => $a->id]))
            ->assertOk()->getContent();

        preg_match_all('/<input\b[^>]*>/i', $isi, $cocok);

        $readonly = array_filter($cocok[0], fn ($t) => stripos($t, 'readonly') !== false
            && stripos($t, 'type="hidden"') === false);

        $this->assertSame([], array_values($readonly),
            "Borang ini masih memuat isian readonly yang tampak bisa diisi:\n- "
            . implode("\n- ", $readonly));
    }

    #[Test]
    public function nilai_yang_dibaca_javascript_tetap_terkirim(): void
    {
        /*
         * Isian readonly yang dibuang BUKAN sekadar hiasan bagi skripnya:
         * hitungTotalPembayaran() membaca #biaya, menulis #kodeUnik, dan
         * membaca #jumlahPendaftar. Dibuang begitu saja, totalnya berhenti
         * terhitung — dan halamannya tetap 200, tetap rapi, cuma totalnya
         * diam di nol.
         */
        $a = $this->angkatan();

        $isi = (string) $this->get(route('public.scopuscamp.formpendaftaran', ['id' => $a->id]))
            ->assertOk()->getContent();

        foreach (['biaya', 'kodeUnik', 'jumlahPendaftar'] as $id) {
            $this->assertMatchesRegularExpression(
                '/<input\b[^>]*id="' . $id . '"[^>]*>/i', $isi,
                'Isian #' . $id . ' hilang dari borang; perhitungan totalnya putus.');
        }

        // Dan angkanya harus benar-benar terkirim ke peladen.
        foreach (['name="biaya"', 'name="kode_unik"', 'name="jumlah_pendaftar"'] as $nama) {
            $this->assertStringContainsString($nama, $isi,
                'Isian ' . $nama . ' tidak lagi dikirim; peladen menghitung dari nilai kosong.');
        }
    }

    #[Test]
    public function hanya_ada_satu_ajakan_daftar_yang_terlihat(): void
    {
        /*
         * Rombakan pertama memasang tombol "Daftar sekarang" DUA KALI — atas
         * dan bawah — supaya tidak perlu dicari dengan menggulung. Pemiliknya
         * menyebutnya apa adanya: tombolnya ada dua.
         *
         * Bagi pembaca yang jadi sasaran halaman ini, dua tombol serupa
         * menimbulkan pertanyaan yang tidak perlu — yang mana yang benar, dan
         * apakah yang tadi sudah tertekan.
         *
         * Jangkauannya tetap dijaga lewat pita di dasar layar, tetapi pita
         * itu HARUS bersyarat: ia muncul justru ketika tombol di atas sudah
         * lewat dari layar. Tanpa syarat itu, dua jalan masuk kembali
         * terlihat bersamaan — dan halamannya tetap 200, jadi tidak ada yang
         * memberi tahu.
         */
        $a = $this->angkatan();
        $isi = $this->detail($a);

        /* Dihitung dari atribut class=, bukan dari kemunculan katanya:
           nama kelas yang sama juga muncul di blok <style> dan di pemilih
           JavaScript, jadi substr_count memulangkan tiga untuk satu tombol. */
        $diIsi = preg_match_all('/class="[^"]*\bcmp-tombol-atas\b[^"]*"/', $isi);
        $this->assertSame(1, $diIsi,
            'Ada ' . $diIsi . ' tombol daftar di badan halaman; seharusnya satu.');

        $semua = preg_match_all('/class="[^"]*\bcmp-tombol\b[^"]*"/', $isi);
        $this->assertSame(2, $semua,
            'Ada ' . $semua . ' tombol daftar di halaman ini; yang sah cuma dua — '
            . 'satu di badan halaman dan satu di pita, dan keduanya tidak pernah '
            . 'terlihat bersamaan.');

        /*
         * Yang dipin: pengamatnya benar-benar DIPASANG pada tombol itu.
         *
         * Versi pertama penjaga ini cuma memeriksa kata "IntersectionObserver"
         * muncul di halaman — dan terbukti kosong: pengamatnya saya lumpuhkan
         * sampai pitanya tampil terus, kata itu tetap ada, dan ujinya tetap
         * hijau.
         *
         * Ini tetap pin kontrak, bukan bukti perilaku: PHPUnit tidak
         * menjalankan JavaScript. Akibat sebenarnya — tidak pernah dua ajakan
         * terlihat bersamaan — diukur di peramban pada gulir 0, 400, 900, dan
         * 1800; semuanya memulangkan tepat satu.
         */
        $this->assertStringContainsString('.observe(tombolAtas)', $isi,
            'Pengamat pitanya tidak dipasang pada tombol daftar; pita akan tampil '
            . 'bersamaan dengan tombol di badan halaman.');

        $this->assertStringContainsString('cmp-pita-tampil', $isi,
            'Kelas penanda pita hilang; pitanya tidak punya keadaan tersembunyi.');
    }

    #[Test]
    public function nomor_rekening_tidak_bisa_disembunyikan_tak_sengaja(): void
    {
        /*
         * Nomor rekeningnya dulu duduk di dalam akordeon. Ia memang terbuka
         * secara bawaan, tetapi tombolnya tetap bisa ditekan — dan sekali
         * tertekan, nomornya menghilang. Di halaman yang satu-satunya
         * tujuannya mentransfer uang, tidak ada alasan menaruh nomor rekening
         * di balik tombol yang bisa salah tekan.
         */
        $a = $this->angkatan();

        $isi = (string) $this->get(route('public.scopuscamp.formpendaftaran', ['id' => $a->id]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('216401000467563', $isi,
            'Nomor rekening hilang dari borang.');

        $this->assertStringNotContainsString('data-bs-toggle="collapse"', $isi,
            'Ada bagian borang yang bisa dilipat; nomor rekening tidak boleh '
            . 'berada di balik tombol yang bisa salah tekan.');
    }
}
