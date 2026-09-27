<?php

namespace Tests\Feature\Akun;

use App\Livewire\Akun\Dasbor;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DasborTest extends TestCase
{
    use DatabaseTransactions;

    private function buatPengguna(string $level = 'karyawan'): User
    {
        $pengguna = User::create([
            'full_name' => 'Uji Dasbor',
            'username' => 'uji_dasbor_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => $level,
            'company' => 'Rumah Scopus',
        ]);

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    public static function peran(): array
    {
        return [
            'manager' => ['manager'],
            'ceo' => ['ceo'],
            'staff' => ['staff'],
            'karyawan' => ['karyawan'],
            'trainer' => ['trainer'],
            'user' => ['user'],
            'admin' => ['admin'],
        ];
    }

    #[DataProvider('peran')]
    public function test_dasbor_terbuka_untuk_tiap_peran(string $level): void
    {
        $pengguna = $this->buatPengguna($level);

        $this->actingAs($pengguna)
            ->get('/account/dashboard')
            ->assertOk()
            ->assertSeeLivewire(Dasbor::class);
    }

    public function test_dasbor_tidak_lagi_menampilkan_kas(): void
    {
        // Fitur Uang Masuk & Uang Keluar dihapus 27 September 2026.
        $this->actingAs($this->buatPengguna('manager'));

        Livewire::test(Dasbor::class)
            ->assertDontSee('Uang masuk')
            ->assertDontSee('Uang keluar')
            ->assertDontSee('Pengeluaran terbesar');
    }

    public function test_halaman_uang_masuk_dan_keluar_sudah_tiada(): void
    {
        $this->actingAs($this->buatPengguna('manager'))
            ->get('/account/debit')
            ->assertNotFound();

        $this->actingAs($this->buatPengguna('manager'))
            ->get('/account/credit')
            ->assertNotFound();
    }

    public function test_segarkan_memperbarui_penanda_waktu(): void
    {
        $this->actingAs($this->buatPengguna('karyawan'));

        $uji = Livewire::test(Dasbor::class);
        $semula = $uji->get('dimuatPada');

        $this->travel(2)->hours();

        $uji->call('segarkan');

        $this->assertNotSame($semula, $uji->get('dimuatPada'));

        $this->travelBack();
    }

    public function test_artikel_draf_tidak_ikut_tampil(): void
    {
        $this->actingAs($this->buatPengguna('karyawan'));

        $judul = 'Draf Rahasia ' . uniqid();

        DB::table('artikel')->insert([
            'user_id' => auth()->id(),
            'categories_artikel_id' => DB::table('categories_artikel')->value('id'),
            'token' => \Illuminate\Support\Str::random(20),
            'judul' => $judul,
            'kata_kunci' => 'uji',
            'isi' => 'uji',
            'dilihat' => 0,
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::test(Dasbor::class)->assertDontSee($judul);
    }

    public function test_manajer_tanpa_perusahaan_tidak_melihat_akun_orang_lain(): void
    {
        // where('company', null) diterjemahkan Laravel jadi "IS NULL", jadi
        // tanpa penjaga, manajer berperusahaan kosong melihat semua akun yang
        // perusahaannya juga kosong.
        $manajer = $this->buatPengguna('manager');
        $manajer->forceFill(['company' => null])->save();

        $lain = $this->buatPengguna('karyawan');
        $lain->forceFill(['company' => null, 'full_name' => 'Akun Tanpa Perusahaan'])->save();

        $this->actingAs($manajer->refresh());

        $tim = Livewire::test(Dasbor::class)->instance()->tim();

        $this->assertSame(0, $tim['total']);
        $this->assertTrue($tim['tanpa_perusahaan']);
        $this->assertNotContains('Akun Tanpa Perusahaan', $tim['terbaru']->pluck('full_name')->all());
    }

    public function test_karyawan_terbaru_hanya_dari_perusahaan_sendiri(): void
    {
        $manajer = $this->buatPengguna('manager');

        $lain = $this->buatPengguna('karyawan');
        $lain->forceFill(['company' => 'Perusahaan Lain', 'full_name' => 'Orang Perusahaan Lain'])->save();

        $this->actingAs($manajer);

        $nama = Livewire::test(Dasbor::class)->instance()->tim()['terbaru']->pluck('full_name');

        $this->assertNotContains('Orang Perusahaan Lain', $nama->all());
    }
}
