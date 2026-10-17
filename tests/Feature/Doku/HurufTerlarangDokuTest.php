<?php

namespace Tests\Feature\Doku;

use App\Services\Doku;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Teks yang dikirim ke DOKU dibersihkan dari huruf yang ditolaknya.
 *
 * DOKU hanya menerima a-z A-Z 0-9 dan . - / + , = _ : ' @ % ( ) beserta
 * spasi. SATU huruf di luar itu membuat seluruh tagihan ditolak dengan
 * "Invalid character", dan yang mendaftar cuma melihat "pembayaran daring
 * sedang bermasalah" lalu diarahkan ke transfer manual — tanpa petunjuk apa
 * pun tentang sebabnya.
 *
 * Bukan kemungkinan yang jauh: judul sesi dan nama pendaftar diketik bebas,
 * dan naskah di sistem ini penuh emoji (📘 🗓 🎯) serta tanda pisah panjang.
 * Terjadi 17 Okt 2026 — nama item sempat diberi "—" dan "…", dan pembayaran
 * daring mati karenanya.
 */
class HurufTerlarangDokuTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-0000');
        config()->set('services.doku.secret_key', 'SK-UJI-0000');

        Http::fake(['*' => Http::response(
            ['response' => ['payment' => ['url' => 'https://bayar.test/x']]], 200
        )]);
    }

    /** @param array<string, mixed> $ubah */
    private function kirim(array $ubah = []): array
    {
        app(Doku::class)->buatTagihan(array_merge([
            'nomor' => 'UJI-0001',
            'jumlah' => 129000,
            'harga_satuan' => 129000,
            'jumlah_peserta' => 1,
            'judul' => 'Webinar Uji',
            'nama' => 'Pendaftar Uji',
            'email' => 'uji@contoh.test',
            'telp' => '6281234567890',
            'kembali' => 'https://contoh.test/status',
        ], $ubah));

        $kiriman = null;

        Http::recorded(function ($permintaan) use (&$kiriman) {
            $kiriman = json_decode($permintaan->body(), true);
        });

        $this->assertNotNull($kiriman, 'Tidak ada tagihan yang dikirim.');

        return $kiriman;
    }

    /** Huruf yang DITOLAK DOKU, dicari di seluruh teks yang dikirim. */
    private function yangTerlarang(string $teks): array
    {
        preg_match_all('/[^a-zA-Z0-9 .\-\/+,=_:\x27@%()]/u', $teks, $m);

        return array_values(array_unique($m[0]));
    }

    #[Test]
    public function emoji_di_judul_sesi_tidak_ikut_terkirim(): void
    {
        /*
         * Naskah di sistem ini memang begini bentuknya — admin menyalin judul
         * beserta emojinya dari pengumuman WhatsApp.
         */
        $isi = $this->kirim(['judul' => '📘 Webinar Scopus 🎯 Angkatan 2']);

        $nama = $isi['order']['line_items'][0]['name'];

        $this->assertSame([], $this->yangTerlarang($nama),
            'Masih ada huruf yang ditolak DOKU di nama item: "' . $nama . '".');

        $this->assertSame('Webinar Scopus Angkatan 2', $nama,
            'Emojinya dibuang, tetapi spasi rangkap bekasnya tertinggal.');
    }

    #[Test]
    public function tanda_pisah_panjang_dan_elipsis_diganti_padanannya(): void
    {
        /*
         * DIGANTI, bukan dibuang: "Webinar  Angkatan 2" kehilangan artinya,
         * sementara "Webinar - Angkatan 2" tetap terbaca seperti aslinya.
         */
        $isi = $this->kirim(['judul' => 'Webinar — Angkatan 2 …']);

        $this->assertSame('Webinar - Angkatan 2 ...', $isi['order']['line_items'][0]['name']);
    }

    #[Test]
    public function nama_pendaftar_ikut_dibersihkan(): void
    {
        $isi = $this->kirim(['nama' => 'Budi “Si Rajin” Santoso 🎓']);

        $nama = $isi['customer']['name'];

        $this->assertSame([], $this->yangTerlarang($nama),
            'Nama pendaftar masih memuat huruf yang ditolak: "' . $nama . '".');
    }

    #[Test]
    public function label_potongan_ikut_dibersihkan(): void
    {
        $isi = $this->kirim([
            'jumlah' => 109000,
            'potongan' => 20000,
            'label_potongan' => 'Potongan — ALUMNI ✨',
        ]);

        $label = $isi['order']['line_items'][1]['name'];

        $this->assertSame([], $this->yangTerlarang($label),
            'Label potongannya masih memuat huruf yang ditolak: "' . $label . '".');
    }

    #[Test]
    public function judul_yang_sudah_bersih_tidak_diubah_sama_sekali(): void
    {
        /*
         * Penjaga arah sebaliknya: pembersihnya tidak boleh diam-diam
         * mengubah judul yang memang sudah sah. Judul inilah yang dipakai
         * sekarang di produksi.
         */
        $judul = 'Scopus Detective: Trik Cepat Membongkar Kedok Jurnal Palsu dan Predator';

        $isi = $this->kirim(['judul' => $judul]);

        $this->assertSame($judul, $isi['order']['line_items'][0]['name']);
    }
}
