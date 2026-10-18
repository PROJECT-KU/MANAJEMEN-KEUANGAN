<?php

namespace Tests\Feature\WebinarEksklusif;

use App\KategoriLayanan;
use App\Mail\WebinarEksklusifPendaftaranMail;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Yang belum membayar menerima FAKTURNYA; yang sudah membayar tidak.
 *
 * Sepasang dengan kwitansi, dan bedanya bukan hanya kata: kwitansi
 * menyatakan uangnya SUDAH diterima, faktur menyatakan berapa yang HARUS
 * dibayar dan sampai kapan. Tertukar, yang satu jadi bukti palsu dan yang
 * lain jadi tagihan untuk uang yang sudah masuk.
 */
class FakturBelumBayarTest extends TestCase
{
    use DatabaseTransactions;

    private function sesi(): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Faktur ' . Str::random(5),
            'nama_ke' => (string) random_int(1000, 1999),
            'mulai' => now()->addDays(13)->toDateString(),
            'selesai' => now()->addDays(13)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '19',
            'biaya' => '129000', 'status' => 'active',
        ]);
    }

    /** @param array<string, mixed> $ubah */
    private function pendaftaran(array $ubah = []): WebinarEksklusifPendaftaran
    {
        return WebinarEksklusifPendaftaran::create(array_merge([
            'id_transaksi' => 'WE-FAKTUR-' . Str::random(5),
            'kategori_id' => $this->sesi()->getKey(),
            'nama' => 'Pendaftar Faktur',
            'email' => 'faktur' . Str::random(5) . '@contoh.test',
            'telp' => '6281234567890',
            'jumlah_pendaftar' => 1,
            'kode_unik' => '1082',
            'total_pembayaran' => '130082',
            'cara_bayar' => 'doku',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHour(),
        ], $ubah));
    }

    private function surat(WebinarEksklusifPendaftaran $p): WebinarEksklusifPendaftaranMail
    {
        $s = new WebinarEksklusifPendaftaranMail($p, KategoriLayanan::find($p->kategori_id));
        $s->build();

        return $s;
    }

    #[Test]
    public function yang_belum_bayar_menerima_faktur(): void
    {
        $p = $this->pendaftaran();

        $lampiran = $this->surat($p)->rawAttachments;

        $this->assertCount(1, $lampiran, 'Suratnya terkirim tanpa faktur.');

        $this->assertStringStartsWith('%PDF', $lampiran[0]['data'],
            'Lampirannya bukan PDF yang sah.');

        $this->assertSame(
            str_replace('WE-', 'INV-', (string) $p->id_transaksi) . '.pdf',
            $lampiran[0]['name'],
            'Nama berkasnya tidak menurunkan nomor pendaftarannya; dua faktur '
            . 'di folder unduhan yang sama akan bertabrakan.'
        );
    }

    #[Test]
    public function yang_sudah_bayar_tidak_ditagih_lagi(): void
    {
        /*
         * Surat ini dikirim ULANG lewat tombol "kirim ulang bukti
         * pendaftaran", dan tombol itu tetap ada sesudah pembayarannya
         * masuk. Dilampirkan tanpa syarat, yang sudah membayar menerima
         * tagihan untuk uang yang sudah ia bayar — dan sebagian akan
         * membayarnya lagi.
         */
        $p = $this->pendaftaran([
            'status' => 'paid',
            'bayar_pada' => now(),
        ]);

        $this->assertCount(0, $this->surat($p)->rawAttachments,
            'Yang sudah lunas menerima faktur; itu tagihan untuk uang yang '
            . 'sudah masuk.');
    }

    #[Test]
    public function fakturnya_menyebut_jumlah_batas_waktu_dan_status(): void
    {
        $p = $this->pendaftaran();

        // Isinya diperiksa dari TAMPILANNYA: teks di dalam PDF tersimpan
        // tersandi dan tidak bisa dicari apa adanya.
        $html = view('public.webinar_eksklusif.dokumen_pdf', [
            'jenis' => 'faktur',
            'pendaftaran' => $p,
            'sesi' => KategoriLayanan::find($p->kategori_id),
            'terbilang' => 'seratus tiga puluh ribu delapan puluh dua rupiah',
        ])->render();

        $this->assertStringContainsString('FAKTUR', $html);
        $this->assertStringContainsString('130.082', $html);
        $this->assertStringContainsString('Belum Dibayar', $html);
        $this->assertStringContainsString('Batas pembayaran', $html);

        /*
         * SISA TAGIHANNYA sama dengan totalnya, dan "Sudah dibayar" nol.
         *
         * Ini yang membedakan faktur dari kwitansi — bukan judulnya.
         * Judulnya bisa benar sementara angkanya menyatakan uangnya sudah
         * masuk, dan dokumen seperti itu adalah bukti palsu.
         */
        $this->assertStringContainsString('Sisa tagihan', $html);
        $this->assertMatchesRegularExpression(
            '/Sudah dibayar<\/td>\s*<td[^>]*>\s*0\s*</s',
            $html,
            'Faktur menyatakan sebagian sudah dibayar padahal belum.'
        );

        // Kode uniknya WAJIB disebut: yang membayar harus mengirim angka
        // yang persis, dan selisih tiga digit membuat pembayarannya tidak
        // cocok dengan tagihan mana pun.
        $this->assertStringContainsString('1.082', $html);

        $this->assertStringNotContainsString('KWITANSI', $html,
            'Faktur memakai kata kwitansi; itu menyatakan uangnya sudah '
            . 'diterima padahal belum.');
    }

    #[Test]
    public function suratnya_menyebut_lampirannya(): void
    {
        /*
         * Lampiran yang tidak disebut tidak dibuka — terutama oleh yang
         * harus menyerahkannya ke bendahara kantornya lebih dulu.
         */
        $p = $this->pendaftaran();

        $html = $this->surat($p)->render();

        $this->assertStringContainsString('Fakturnya terlampir', $html);
    }

    #[Test]
    public function fakturnya_tidak_menyisipkan_font(): void
    {
        /*
         * Dompdf menyisipkan font yang bukan bawaan PDF, utuh, ke tiap
         * berkas. Terukur pada kwitansi: DejaVu Sans membuatnya 1.289.545
         * bita melawan 20.965 dengan Helvetica.
         */
        $p = $this->pendaftaran();

        $ukuran = strlen($this->surat($p)->rawAttachments[0]['data']);

        $this->assertLessThan(200_000, $ukuran,
            'Fakturnya ' . number_format($ukuran) . ' bita — hampir pasti ada '
            . 'font yang disisipkan utuh ke dalamnya.');
    }
}
