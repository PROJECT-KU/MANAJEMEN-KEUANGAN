<?php

namespace Tests\Feature\WebinarEksklusif;

use App\KategoriLayanan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Alat menirukan pembayaran, untuk pengujian lokal saja.
 *
 * Pemberitahuan DOKU adalah peladen DOKU menelepon peladen kita, dan
 * `localhost` tidak bisa dihubungi dari luar. Terukur 17 Okt 2026: NOL
 * permintaan pernah masuk ke alamat pemberitahuan sepanjang hari, padahal
 * pembayaran di sandbox berkali-kali sukses. Tanpa alat ini, seluruh layar
 * sesudah pembayaran tidak pernah bisa dilihat di mesin pengembang.
 *
 * Yang dijaga di sini dua hal: ia mengubah kolom yang SAMA dengan penerima
 * pemberitahuan sungguhan, dan ia TIDAK BISA jalan di produksi.
 */
class TirukanBayarTest extends TestCase
{
    use DatabaseTransactions;

    private function pendaftaran(array $ubah = []): WebinarEksklusifPendaftaran
    {
        $sesi = KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Tiru ' . Str::random(5),
            'nama_ke' => (string) random_int(7000, 7999),
            'mulai' => now()->addDays(14)->toDateString(),
            'selesai' => now()->addDays(14)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '20',
            'biaya' => '129000', 'status' => 'active',
        ]);

        return WebinarEksklusifPendaftaran::create(array_merge([
            'id_transaksi' => 'WE-TIRU-' . Str::random(6),
            'kategori_id' => $sesi->getKey(),
            'nama' => 'Pendaftar Uji',
            'email' => 'uji' . Str::random(5) . '@contoh.test',
            'telp' => '6281234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '129997',
            'cara_bayar' => 'doku',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHour(),
        ], $ubah));
    }

    #[Test]
    public function menandai_lunas_persis_seperti_pemberitahuan_sungguhan(): void
    {
        $p = $this->pendaftaran();

        $this->artisan('webinar-eksklusif:tirukan-bayar', ['nomor' => $p->id_transaksi])
            ->assertSuccessful();

        $p->refresh();

        $this->assertSame('paid', $p->status);
        $this->assertSame('success', $p->bayar_status);
        $this->assertNotNull($p->bayar_pada,
            'bayar_pada tidak terisi; penerima pemberitahuan sungguhan mengisinya, '
            . 'jadi yang diuji di lokal bukan lagi keadaan yang sebenarnya.');
    }

    #[Test]
    public function bisa_menirukan_pembayaran_gagal(): void
    {
        $p = $this->pendaftaran();

        $this->artisan('webinar-eksklusif:tirukan-bayar', [
            'nomor' => $p->id_transaksi, '--gagal' => true,
        ])->assertSuccessful();

        $this->assertSame('expired', $p->refresh()->status);
    }

    #[Test]
    public function nomor_yang_tidak_ada_ditolak(): void
    {
        $this->artisan('webinar-eksklusif:tirukan-bayar', ['nomor' => 'WE-TIDAK-ADA'])
            ->assertFailed();
    }

    #[Test]
    public function yang_sudah_lunas_tidak_diproses_dua_kali(): void
    {
        $waktu = now()->subHour();
        $p = $this->pendaftaran(['status' => 'paid', 'bayar_pada' => $waktu]);

        $this->artisan('webinar-eksklusif:tirukan-bayar', ['nomor' => $p->id_transaksi])
            ->assertSuccessful();

        $this->assertSame(
            $waktu->format('Y-m-d H:i:s'),
            $p->refresh()->bayar_pada->format('Y-m-d H:i:s'),
            'Waktu bayarnya ditimpa; pendaftaran yang sudah lunas tidak boleh disentuh lagi.'
        );
    }

    #[Test]
    public function menolak_jalan_di_produksi(): void
    {
        /*
         * INI penjaga yang paling penting.
         *
         * Menandai lunas tanpa uang masuk adalah hal yang paling tidak boleh
         * bisa dilakukan sembarangan di produksi. Perintah ini memang hanya
         * bisa dipanggil dari baris perintah, tetapi siapa pun yang punya
         * akses SSH ke peladen punya baris perintah.
         */
        $p = $this->pendaftaran();

        app()->detectEnvironment(fn () => 'production');

        $this->artisan('webinar-eksklusif:tirukan-bayar', ['nomor' => $p->id_transaksi])
            ->assertFailed();

        $this->assertSame('pending', $p->refresh()->status,
            'Statusnya ikut berubah padahal perintahnya ditolak.');
    }
}
