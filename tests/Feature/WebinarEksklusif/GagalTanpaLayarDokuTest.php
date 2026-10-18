<?php

namespace Tests\Feature\WebinarEksklusif;

use App\KategoriLayanan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\MenyiapkanGerbangBayar;
use Tests\TestCase;

/**
 * Yang halaman statusnya terbuka menerima kabar gagalnya DARI KITA.
 *
 * Tagihan DOKU yang habis masa berlakunya menahan orangnya di domain DOKU
 * lewat TIGA tekanan sebelum ia sampai ke kabar yang sebenarnya milik kita:
 *
 *   1. kotak "Sorry, Your order has expired" di halaman DOKU;
 *   2. halaman time-out DOKU;
 *   3. tombol "GO TO MERCHANT" — baru yang ini mengantarnya kembali ke sini.
 *
 * Dua layar pertama digambar di domain DOKU dan TIDAK bisa kita ganti:
 * Checkout mereka tidak punya setelan alamat-alih saat kedaluwarsa, yang ada
 * hanya pemberitahuan peladen-ke-peladen.
 *
 * SEJARAHNYA, supaya tidak diputuskan dua kali. 18 Okt 2026 borangnya sempat
 * diubah agar mendarat di halaman status lebih dulu, supaya halaman kita yang
 * tetap terbuka dan ketiga layar itu tidak pernah terlihat. Dikembalikan pada
 * hari yang sama atas keputusan pemiliknya: ongkosnya satu tekanan tambahan
 * yang ditanggung SEMUA pendaftar — termasuk mayoritas yang memang membayar —
 * demi jalur yang hanya dilewati yang membatalkan. Keputusannya diterima, dan
 * akibatnya diterima bersamanya.
 *
 * Yang TETAP berlaku, dan itulah isi berkas ini: untuk siapa pun yang halaman
 * status ini sedang terbuka — yang datang dari tautan di emailnya, dan yang
 * tab bayarnya sudah ditutup — kabar gagalnya tetap milik kita sendiri. Tiga
 * hal yang menopangnya:
 *
 *   - tombol bayar di halaman ini membuka DOKU di TAB LAIN, jadi halaman ini
 *     tidak ikut terbuang;
 *   - saat waktunya habis, halaman ini berbicara lebih dulu;
 *   - nolnya ditanyakan dulu ke peladen sebelum berbicara.
 */
class GagalTanpaLayarDokuTest extends TestCase
{
    use DatabaseTransactions;
    use MenyiapkanGerbangBayar;

    private const TAMPILAN = 'resources/views/public/webinar_eksklusif/status.blade.php';

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanGerbangBayar();
        Mail::fake();
    }

    private function sesi(): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Gagal ' . Str::random(5),
            'nama_ke' => (string) random_int(5000, 5999),
            'mulai' => now()->addDays(12)->toDateString(),
            'selesai' => now()->addDays(12)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '20',
            'biaya' => '129000', 'status' => 'active',
        ]);
    }

    /** @param array<string, mixed> $ubah */
    private function pendaftaran(array $ubah = []): WebinarEksklusifPendaftaran
    {
        return WebinarEksklusifPendaftaran::create(array_merge([
            'id_transaksi' => 'WE-GAGAL-' . Str::random(5),
            'kategori_id' => $this->sesi()->getKey(),
            'nama' => 'Pendaftar Gagal',
            'email' => 'gagal' . Str::random(5) . '@contoh.test',
            'telp' => '6281234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '129333',
            'cara_bayar' => 'doku',
            'bayar_url' => 'https://bayar.uji.test/gagal',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHour(),
        ], $ubah));
    }

    /**
     * Isi satu blok <script> dari tampilannya, TANPA komentar.
     *
     * Komentarnya dibuang lebih dulu, dan itu bukan kerapian: penjelasan di
     * berkas itu menyebut nama-nama yang justru hendak dilarang — "GO TO
     * MERCHANT", location.reload(), isConfirmed. Tanpa dibuang, penjaga di
     * bawah merah karena kalimat yang menerangkannya, atau hijau karena
     * kalimat yang cuma menyebutnya.
     */
    private function skrip(string $penanda): string
    {
        $sumber = (string) file_get_contents(base_path(self::TAMPILAN));

        // Komentar Blade dulu: isinya bisa memuat "<script>" dan memotong
        // pencarian blok di bawah pada tempat yang salah.
        $sumber = (string) preg_replace('/\{\{--.*?--\}\}/s', '', $sumber);

        $mulai = strpos($sumber, $penanda);

        $this->assertNotFalse(
            $mulai,
            'Penanda "' . $penanda . '" tidak ada lagi di ' . self::TAMPILAN
            . '; penjaganya kehilangan sasaran dan harus diperbarui, bukan dibiarkan.'
        );

        $awal = strrpos(substr($sumber, 0, $mulai), '<script>');
        $akhir = strpos($sumber, '</script>', $mulai);

        $this->assertNotFalse($awal);
        $this->assertNotFalse($akhir);

        $blok = substr($sumber, $awal, $akhir - $awal);

        return (string) preg_replace(['#/\*.*?\*/#s', '#//[^\n]*#'], '', $blok);
    }

    #[Test]
    public function borangnya_mengantar_langsung_ke_halaman_bayar(): void
    {
        /*
         * Keputusan pemiliknya, 18 Okt 2026: menekan "Daftar dan bayar
         * sekarang" harus membuka halaman pembayaran, bukan halaman antara.
         *
         * Dijaga supaya tidak berubah diam-diam. Pernah diubah sekali, dan
         * perubahan itu menambah satu tekanan bagi SEMUA pendaftar — termasuk
         * mayoritas yang memang membayar — tanpa seorang pun melihatnya
         * sampai dicoba dengan tangan.
         */
        $sesi = $this->sesi();

        $jawab = $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id,
            'nama' => 'Pendaftar Gagal',
            'email' => 'gagal' . Str::random(5) . '@contoh.test',
            'telp' => '0812-3456-' . random_int(1000, 9999),
            'jumlah_pendaftar' => 1, 'peserta' => [], 'setuju' => '1',
        ]);

        $jawab->assertRedirect('https://bayar.uji.test/x');

        $p = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->firstOrFail();

        $this->assertSame(
            'https://bayar.uji.test/x',
            $p->bayar_url,
            'Alamat halaman bayarnya dibuang sesudah dipakai mengalihkan. Yang '
            . 'menutup tab itu tidak punya jalan kembali, dan kursinya hangus '
            . 'saat waktunya habis.'
        );
    }

    #[Test]
    public function tombol_bayarnya_membuka_tab_lain(): void
    {
        /*
         * Yang sampai ke tombol ini bukan orang yang baru mengisi borang —
         * borangnya mengalihkan langsung ke DOKU. Yang sampai ke sini datang
         * dari tautan di emailnya, atau tab bayarnya sudah ditutup.
         *
         * Bagi mereka halaman ini SATU-SATUNYA yang terbuka. Tanpa
         * target="_blank" tombolnya menimpa halaman ini dengan halaman DOKU,
         * dan pemberitahuan kedaluwarsa di bawah kehilangan tempat berdiri.
         */
        Http::fake(['*/orders/v1/status/*' => Http::response([], 404)]);

        $p = $this->pendaftaran();

        $isi = (string) $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->getContent();

        $cocok = preg_match(
            '/<a\b[^>]*' . preg_quote((string) $p->bayar_url, '/') . '[^>]*>/',
            $isi,
            $tangkap
        );

        $this->assertSame(1, $cocok, 'Tombol menuju halaman bayarnya tidak tergambar.');

        $this->assertStringContainsString(
            'target="_blank"',
            $tangkap[0],
            'Tombol bayarnya menimpa halaman ini. Begitu waktunya habis, yang '
            . 'terbuka di layar orangnya halaman DOKU — bukan halaman kita — dan '
            . 'ia harus menekan tiga kali untuk sampai ke kabar yang benar.'
        );

        $this->assertStringContainsString(
            'rel="noopener"',
            $tangkap[0],
            'Tanpa rel="noopener", halaman gerbangnya memegang window.opener dan '
            . 'bisa mengalihkan halaman yang justru sedang kita jaga tetap terbuka.'
        );
    }

    #[Test]
    public function hitung_mundur_yang_habis_tidak_memuat_ulang_diam_diam(): void
    {
        /*
         * Dulu nolnya memuat ulang halaman sesudah 1,5 detik tanpa sepatah
         * kata. Orangnya — yang mungkin sedang menatap tab pembayaran di
         * sebelah — kembali ke tab ini dan menemukan isinya sudah berganti
         * jadi kartu merah, tanpa pernah diberi tahu kapan dan kenapa.
         */
        $blok = $this->skrip('var batas = new Date(');

        $this->assertStringContainsString(
            'staPeriksaAkhir(',
            $blok,
            'Hitung mundurnya tidak lagi menyerahkan nolnya ke pemeriksa akhir; '
            . 'kabar kedaluwarsanya tidak akan diberitahukan kepada siapa pun.'
        );

        $this->assertStringNotContainsString(
            'reload()',
            $blok,
            'Nolnya kembali memuat ulang halaman sendiri, diam-diam. Pemberitahuannya '
            . 'dilewati, dan orangnya membaca sendiri dari layar yang sudah berganti.'
        );
    }

    #[Test]
    public function nolnya_ditanyakan_dulu_ke_peladen(): void
    {
        /*
         * Angka nol itu dihitung dari jam PERAMBAN. Yang jamnya maju lima
         * menit akan mencapai nol lebih dulu daripada peladen — dan kalau
         * pemberitahuannya langsung muncul lalu halamannya dimuat ulang,
         * peladen memulangkan keadaan menunggu lagi, nolnya tercapai lagi,
         * dan orangnya terkurung dalam pemberitahuan berulang selama lima
         * menit penuh.
         */
        $blok = $this->skrip('window.staPeriksaAkhir = function');
        $badan = substr($blok, (int) strpos($blok, 'window.staPeriksaAkhir = function'));

        $this->assertStringContainsString(
            'fetch(',
            $badan,
            'Pemeriksa akhirnya tidak lagi bertanya ke peladen. Jam peramban yang '
            . 'maju akan memicu pemberitahuan berulang sampai jam peladennya menyusul.'
        );
    }

    #[Test]
    public function penanya_membedakan_kabar_baik_dari_kabar_buruk(): void
    {
        /*
         * Keduanya "selesai", dan dulu keduanya diperlakukan sama: dimuat
         * ulang begitu saja. Untuk yang lunas itu benar — halaman lunasnya
         * sendiri yang merayakan. Untuk yang batal, isi layarnya berganti
         * jadi kartu merah tanpa sepatah kata.
         */
        $blok = $this->skrip('var BATAS');

        $this->assertStringContainsString(
            'd.lunas',
            $blok,
            'Penanyanya tidak lagi membedakan lunas dari batal; yang kursinya '
            . 'dilepas tidak diberi tahu sama sekali.'
        );

        $this->assertStringContainsString(
            'staKabarGagal(',
            $blok,
            'Kabar buruk dari peladen tidak lagi memunculkan pemberitahuan kita.'
        );
    }

    #[Test]
    public function pemberitahuannya_ditutup_dengan_cara_apa_pun_tetap_menyegarkan(): void
    {
        /*
         * Yang menekan Esc atau mengetuk di luar kotaknya TIDAK boleh
         * tertinggal di halaman berangka 00:00:00 yang isinya sudah tidak
         * benar lagi. Karena itu pemuatan ulangnya bergantung pada
         * PENUTUPANNYA, bukan pada tombolnya ditekan.
         */
        $blok = $this->skrip('window.staKabarGagal = function');

        $this->assertStringContainsString('window.location.reload();', $blok);

        $this->assertStringNotContainsString(
            'isConfirmed',
            $blok,
            'Pemuatan ulangnya digantungkan pada tombolnya ditekan. Yang menutup '
            . 'kotaknya dengan Esc tertinggal di halaman yang sudah tidak benar.'
        );
    }

    #[Test]
    public function yang_kedaluwarsa_mendapat_halaman_gagal_kita_sendiri(): void
    {
        /*
         * Ujung jalurnya. Pemberitahuan di atas mengantar ke SINI, dan
         * halaman ini harus menyatakan keadaannya tanpa perlu ditafsirkan.
         */
        Http::fake(['*/orders/v1/status/*' => Http::response([], 404)]);

        $p = $this->pendaftaran([
            'status' => 'expired',
            'kedaluwarsa_pada' => now()->subMinutes(5),
        ]);

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertSee('Batas waktu pembayarannya sudah lewat')
            ->assertSee('Kursinya sudah dilepas kembali')
            ->assertSee('Lihat sesi yang dibuka')
            ->assertDontSee('Bayar sekarang');
    }
}
