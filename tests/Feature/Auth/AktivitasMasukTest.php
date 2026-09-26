<?php

namespace Tests\Feature\Auth;

use App\AktivitasMasuk;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
}
