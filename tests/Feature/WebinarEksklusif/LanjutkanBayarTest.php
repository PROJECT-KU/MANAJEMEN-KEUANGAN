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
 * Yang belum membayar punya JALAN KEMBALI ke halaman bayar.
 *
 * Alamat halaman bayar lahir sekali saat tagihannya dibuat, lalu dulu hanya
 * dipakai untuk mengalihkan dan dibuang. Akibatnya siapa pun yang menutup
 * tab itu — atau membuka tautan di emailnya nanti — mendarat di halaman
 * status yang menghitung mundur TANPA satu pun tombol untuk membayar.
 * Kursinya dilepas saat waktunya habis, dan ia tidak pernah punya jalan
 * menyelesaikannya.
 *
 * Tombol di email berbunyi "Lihat cara pembayaran" dan menuju halaman ini,
 * jadi halaman inilah yang harus memuat caranya.
 */
class LanjutkanBayarTest extends TestCase
{
    use DatabaseTransactions;
    use MenyiapkanGerbangBayar;

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
            'nama' => 'Sesi Lanjut ' . Str::random(5),
            'nama_ke' => (string) random_int(7000, 7999),
            'mulai' => now()->addDays(14)->toDateString(),
            'selesai' => now()->addDays(14)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '20',
            'biaya' => '129000', 'status' => 'active',
        ]);
    }

    #[Test]
    public function alamat_halaman_bayar_tersimpan_saat_tagihannya_dibuat(): void
    {
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id,
            'nama' => 'Pendaftar Lanjut',
            'email' => 'lanjut' . Str::random(5) . '@contoh.test',
            'telp' => '0812-3456-' . random_int(1000, 9999),
            'jumlah_pendaftar' => 1, 'peserta' => [], 'setuju' => '1',
        ])->assertRedirect();

        $p = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->firstOrFail();

        $this->assertSame('https://bayar.uji.test/x', $p->bayar_url,
            'Alamat halaman bayarnya dibuang; yang menutup tabnya tidak punya '
            . 'jalan kembali dan kursinya hangus saat waktunya habis.');
    }

    /** @param array<string, mixed> $ubah */
    private function pendaftaran(array $ubah = []): WebinarEksklusifPendaftaran
    {
        return WebinarEksklusifPendaftaran::create(array_merge([
            'id_transaksi' => 'WE-LANJUT-' . Str::random(5),
            'kategori_id' => $this->sesi()->getKey(),
            'nama' => 'Pendaftar Lanjut',
            'email' => 'lanjut' . Str::random(5) . '@contoh.test',
            'telp' => '6281234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '129915',
            'cara_bayar' => 'doku',
            'bayar_url' => 'https://bayar.uji.test/lanjut',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHour(),
        ], $ubah));
    }

    #[Test]
    public function halaman_status_memberi_tombol_melanjutkan_pembayaran(): void
    {
        Http::fake(['*/orders/v1/status/*' => Http::response([], 404)]);

        $p = $this->pendaftaran();

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertSee('Bayar sekarang')
            ->assertSee('https://bayar.uji.test/lanjut', false);
    }

    #[Test]
    public function yang_sudah_lunas_tidak_ditawari_membayar_lagi(): void
    {
        /*
         * Tombol membayar di halaman orang yang sudah lunas adalah undangan
         * membayar dua kali.
         */
        Http::fake();

        $p = $this->pendaftaran(['status' => 'paid']);

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertDontSee('Bayar sekarang');
    }

    #[Test]
    public function tanpa_alamat_tersimpan_tombolnya_tidak_dipaksakan(): void
    {
        /*
         * Pendaftaran lama tidak punya alamatnya, dan tagihan yang gagal
         * dibuat juga tidak. Tombol kosong yang menuju ke mana-mana lebih
         * buruk daripada tidak ada tombol — yang ini tetap melihat panel
         * menunggu beserta kalimat "hubungi panitia" sesudah sepuluh menit.
         */
        Http::fake(['*/orders/v1/status/*' => Http::response([], 404)]);

        $p = $this->pendaftaran(['bayar_url' => null]);

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertDontSee('Bayar sekarang')
            ->assertSee('Menunggu konfirmasi pembayaran');
    }
}
