<?php

namespace Tests\Feature\WebinarEksklusif;

use App\KategoriLayanan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Nomor pendaftaran tidak pernah dipakai ulang, dan pembayaran tidak pernah
 * diwariskan.
 *
 * Terjadi 17 Okt 2026, dan rantainya panjang: satu pendaftaran yang SUDAH
 * DIBAYAR dihapus pukul 21:49:10, lalu orang yang sama mendaftar lagi 47
 * detik kemudian. Nomor barunya dihitung dari tabel hidup saja, jadi nomor
 * yang baru saja dibebaskan itu dipakai ulang — dan nomor itulah yang jadi
 * invoice_number di gerbang pembayaran.
 *
 * Akibatnya:
 *   1. gerbang menolak tagihan barunya ("nomor sudah terpakai");
 *   2. saat statusnya ditanyakan, gerbang menjawab SUCCESS — untuk
 *      pembayaran milik pendaftaran yang sudah dihapus;
 *   3. pendaftaran baru tertandai LUNAS tanpa ada uang masuk, dan
 *      pendaftarnya menerima surat "SUDAH DIBAYAR".
 *
 * Dijaga DUA lapis. Yang menjaga uang tidak boleh bergantung pada satu
 * lapis saja.
 */
class NomorTidakDiwariskanTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-0000');
        config()->set('services.doku.secret_key', 'SK-UJI-0000');

        Mail::fake();
    }

    private function sesi(): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Warisan ' . Str::random(5),
            'nama_ke' => (string) random_int(7000, 7999),
            'mulai' => now()->addDays(14)->toDateString(),
            'selesai' => now()->addDays(14)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '19',
            'biaya' => '129000', 'status' => 'active',
        ]);
    }

    // ------------------------------------------- lapis 1: nomornya

    #[Test]
    public function nomor_yang_sudah_dihapus_tidak_dipakai_ulang(): void
    {
        $awalan = 'WE-' . now()->format('Ymd') . '-';

        /*
         * Nomor yang sangat tinggi: tabel hidup berisi nomor-nomor hari ini
         * juga, dan yang diuji adalah apakah catatan penghapusan IKUT
         * dihitung — bukan sekadar "nomornya naik".
         */
        DB::table('pendaftaran_dihapus')->insert([
            // id dan pendaftaran_id WAJIB terisi; tabelnya tidak memberi
            // nilai bawaan untuk keduanya.
            'id' => (string) Str::uuid(),
            'pendaftaran_id' => (string) Str::uuid(),
            'layanan' => 'webinar_eksklusif',
            'nomor' => $awalan . '9001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame($awalan . '9002', WebinarEksklusifPendaftaran::nomorBaru(),
            'Nomor yang pernah dihapus dipakai ulang; pendaftaran baru akan '
            . 'mewarisi pembayaran milik pendahulunya di gerbang.');
    }

    // --------------------------------- lapis 2: umur pembayarannya

    private function pendaftaranBaru(): WebinarEksklusifPendaftaran
    {
        return WebinarEksklusifPendaftaran::create([
            'id_transaksi' => 'WE-WARIS-' . Str::random(6),
            'kategori_id' => $this->sesi()->getKey(),
            'nama' => 'Pendaftar Baru',
            'email' => 'baru' . Str::random(5) . '@contoh.test',
            'telp' => '6281234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '129934',
            'cara_bayar' => 'doku',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHour(),
        ]);
    }

    #[Test]
    public function pembayaran_yang_lebih_tua_tidak_diwariskan(): void
    {
        $p = $this->pendaftaranBaru();

        // Pembayarannya terjadi EMPAT JAM sebelum pendaftaran ini dibuat —
        // persis bentuk kejadian 17 Okt 2026.
        Http::fake(['*/orders/v1/status/*' => Http::response([
            'transaction' => [
                'status' => 'SUCCESS',
                'date' => $p->created_at->copy()->subHours(4)->toIso8601ZuluString(),
            ],
        ], 200)]);

        $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))
            ->assertOk()->assertJson(['lunas' => false]);

        $this->assertSame('pending', $p->refresh()->status,
            'Pendaftaran baru tertandai lunas oleh pembayaran milik orang lain.');

        Mail::assertNothingSent();
    }

    #[Test]
    public function pembayaran_sesudah_pendaftaran_tetap_diterima(): void
    {
        /*
         * Penjaga arah sebaliknya, dan ia penting: penjagaan yang terlalu
         * ketat akan menolak pembayaran yang sah, dan gejalanya persis sama
         * dengan "callback tidak datang".
         */
        $p = $this->pendaftaranBaru();

        Http::fake(['*/orders/v1/status/*' => Http::response([
            'transaction' => [
                'status' => 'SUCCESS',
                'date' => $p->created_at->copy()->addMinutes(3)->toIso8601ZuluString(),
            ],
        ], 200)]);

        $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))
            ->assertOk()->assertJson(['lunas' => true]);

        $this->assertSame('paid', $p->refresh()->status);
    }

    #[Test]
    public function selisih_jam_kecil_tidak_menolak_pembayaran_sah(): void
    {
        /*
         * Jam peladen kita dan jam gerbang tidak pernah sama persis.
         * Pembayaran yang tercatat beberapa menit "sebelum" pendaftarannya
         * hampir pasti selisih jam, bukan warisan — yang diwariskan terpaut
         * jam, bukan menit.
         */
        $p = $this->pendaftaranBaru();

        Http::fake(['*/orders/v1/status/*' => Http::response([
            'transaction' => [
                'status' => 'SUCCESS',
                'date' => $p->created_at->copy()->subMinutes(3)->toIso8601ZuluString(),
            ],
        ], 200)]);

        $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))
            ->assertOk()->assertJson(['lunas' => true]);
    }
}
