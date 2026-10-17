<?php

namespace Tests\Feature\WebinarEksklusif;

use App\KategoriLayanan;
use App\User;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\MenyiapkanGerbangBayar;
use Tests\TestCase;

/**
 * Pendaftaran yang SUDAH DIHAPUS dianggap tidak pernah ada.
 *
 * Orang yang pendaftarannya dihapus panitia harus bisa mendaftar lagi
 * dengan data yang sama persis — tanpa dicegat "data ini sudah punya
 * pendaftaran di sesi ini". Dicegat, ia tidak punya jalan apa pun untuk
 * masuk lagi kecuali memakai nama atau email lain.
 *
 * Dan pendaftaran barunya harus benar-benar BARU: nomor sendiri, tagihan
 * sendiri. Nomor yang dipakai ulang membuatnya mewarisi pembayaran milik
 * pendaftaran yang dihapus — lihat NomorTidakDiwariskanTest.
 */
class DihapusBolehDaftarLagiTest extends TestCase
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
            'nama' => 'Sesi Hapus ' . Str::random(5),
            'nama_ke' => (string) random_int(7000, 7999),
            'mulai' => now()->addDays(14)->toDateString(),
            'selesai' => now()->addDays(14)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '20',
            'biaya' => '129000', 'status' => 'active',
        ]);
    }

    private function admin(): User
    {
        $t = Str::random(8);

        $orang = User::create([
            'full_name' => 'Uji Hapus ' . $t, 'username' => 'hps_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $orang->forceFill([
            'status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR,
        ])->save();

        return $orang;
    }

    /** Data pendaftar yang SAMA PERSIS, dipakai dua kali. */
    private function borang(KategoriLayanan $sesi): array
    {
        return [
            'kategori_id' => $sesi->id,
            'nama' => 'Berto Juni Krisnanto',
            'email' => 'berto.hapus@contoh.test',
            'telp' => '0812-3456-7890',
            'jumlah_pendaftar' => 1, 'peserta' => [], 'setuju' => '1',
        ];
    }

    #[Test]
    public function sesudah_dihapus_data_yang_sama_boleh_mendaftar_lagi(): void
    {
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi))->assertRedirect();

        $pertama = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->firstOrFail();

        // Dihapus panitia lewat layarnya sendiri, bukan dihapus langsung di
        // basis data: yang diuji jalur yang benar-benar dipakai orang.
        $this->actingAs($this->admin())
            ->delete(route('account.pendaftaran-layanan.hapus', [
                'layanan' => 'webinar_eksklusif', 'id' => $pertama->getKey(),
            ]));

        $this->assertNull(WebinarEksklusifPendaftaran::find($pertama->getKey()),
            'prasyarat: pendaftarannya benar-benar terhapus');

        // Mendaftar lagi dengan data yang SAMA PERSIS.
        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi))->assertRedirect();

        $kedua = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->get();

        $this->assertCount(1, $kedua,
            'Data yang sama dicegat padahal pendaftaran lamanya sudah dihapus; '
            . 'orangnya tidak punya jalan masuk lagi.');

        $this->assertNotSame($pertama->id_transaksi, $kedua->first()->id_transaksi,
            'Nomornya dipakai ulang; pendaftaran baru akan mewarisi pembayaran '
            . 'milik pendaftaran yang dihapus.');
    }

    #[Test]
    public function kursinya_kembali_sesudah_dihapus(): void
    {
        /*
         * Kalau kursinya tidak kembali, menghapus satu pendaftaran
         * menghanguskan satu kursi selamanya — dan angkatannya penuh lebih
         * cepat daripada yang sebenarnya.
         */
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi))->assertRedirect();

        $this->assertSame(19, (int) $sesi->refresh()->sisa_kuota, 'prasyarat: kursinya terpotong');

        $p = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->firstOrFail();

        $this->actingAs($this->admin())
            ->delete(route('account.pendaftaran-layanan.hapus', [
                'layanan' => 'webinar_eksklusif', 'id' => $p->getKey(),
            ]));

        $this->assertSame(20, (int) $sesi->refresh()->sisa_kuota,
            'Kursinya tidak kembali sesudah pendaftarannya dihapus.');
    }
}
