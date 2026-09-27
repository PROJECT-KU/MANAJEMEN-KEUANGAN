<?php

namespace Tests\Feature\Auth;

use App\AktivitasMasuk;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AktivitasMasukTest extends TestCase
{
    use DatabaseTransactions;

    public function test_catatan_lama_dipangkas(): void
    {
        $lama = AktivitasMasuk::create([
            'identitas' => 'uji-lama', 'berhasil' => false, 'ip' => '127.0.0.1',
        ]);
        $lama->forceFill(['created_at' => now()->subDays(120)])->save();

        $baru = AktivitasMasuk::create([
            'identitas' => 'uji-baru', 'berhasil' => true, 'ip' => '127.0.0.1',
        ]);

        $this->artisan('aktivitas:pangkas', ['--hari' => 90])->assertSuccessful();

        $this->assertNull(AktivitasMasuk::find($lama->id), 'Catatan di luar masa simpan harus dihapus.');
        $this->assertNotNull(AktivitasMasuk::find($baru->id), 'Catatan baru harus tetap ada.');
    }

    public function test_masa_simpan_tidak_boleh_nol(): void
    {
        $this->artisan('aktivitas:pangkas', ['--hari' => 0])->assertFailed();
    }

    private function pengawas(): User
    {
        $pengguna = User::create([
            'full_name' => 'Pengawas Uji',
            'username' => 'pengawas' . substr(uniqid(), -6),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'manager',
            'company' => 'rumahscopus',
        ]);

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna;
    }

    public function test_keluar_ikut_tercatat(): void
    {
        $pengguna = $this->pengawas();

        $this->actingAs($pengguna)->post('/logout')->assertRedirect();

        $catatan = AktivitasMasuk::where('user_id', $pengguna->id)->latest('id')->first();

        $this->assertNotNull($catatan);
        $this->assertSame('keluar', $catatan->alasan);
    }

    public function test_saringan_tanggal_dipakai(): void
    {
        $pengguna = $this->pengawas();

        $lama = AktivitasMasuk::create(['identitas' => 'jauh-hari', 'berhasil' => true, 'ip' => '10.0.0.1']);
        $lama->forceFill(['created_at' => now()->subDays(10)])->save();
        AktivitasMasuk::create(['identitas' => 'hari-ini', 'berhasil' => true, 'ip' => '10.0.0.2']);

        $this->actingAs($pengguna)
            ->get(route('account.aktivitas-masuk.index', ['dari' => now()->subDays(2)->toDateString()]))
            ->assertOk()
            ->assertSee('hari-ini')
            ->assertDontSee('jauh-hari');
    }

    public function test_ekspor_csv_hanya_untuk_peran_pengawas(): void
    {
        $pengawas = $this->pengawas();
        AktivitasMasuk::create(['identitas' => 'ada-di-csv', 'berhasil' => false, 'ip' => '10.0.0.3']);

        $res = $this->actingAs($pengawas)->get(route('account.aktivitas-masuk.ekspor'));
        $res->assertOk();
        $this->assertStringContainsString('text/csv', $res->headers->get('Content-Type'));
        $this->assertStringContainsString('ada-di-csv', $res->streamedContent());

        $biasa = $this->pengawas();
        $biasa->forceFill(['level' => 'karyawan'])->save();

        // Sesi dibersihkan: AuthenticateSession mengikat sesi pada hash kata
        // sandi, jadi berganti pengguna di tengah tes akan dianggap sesi basi.
        $this->flushSession();
        $this->app['auth']->forgetGuards();

        $this->actingAs($biasa)
            ->get(route('account.aktivitas-masuk.ekspor'))
            ->assertRedirect(route('account.dashboard.index'));
    }
}
