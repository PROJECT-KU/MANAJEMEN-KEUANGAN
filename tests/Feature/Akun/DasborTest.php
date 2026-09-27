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

    /** Kategori pemasukan seadanya; tabel debit menuntut kunci asing yang sah. */
    private function kategoriDebit(): int
    {
        $ada = DB::table('categories_debit')->value('id');

        if ($ada) {
            return (int) $ada;
        }

        return (int) DB::table('categories_debit')->insertGetId([
            'name' => 'Uji Dasbor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** Satu baris pemasukan; kolom wajib diisi seadanya. */
    private function catatDebit(User $pengguna, int $nominal, $tanggal): void
    {
        DB::table('debit')->insert([
            'user_id' => $pengguna->getKey(),
            'category_id' => $this->kategoriDebit(),
            'description' => 'uji dasbor',
            'nominal' => $nominal,
            'debit_date' => $tanggal->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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

    public function test_rentang_bisa_diganti(): void
    {
        $this->actingAs($this->buatPengguna('manager'));

        Livewire::test(Dasbor::class)
            ->assertSet('rentang', 'bulan')
            ->call('gantiRentang', 'tahun')
            ->assertSet('rentang', 'tahun')
            ->call('gantiRentang', 'semua')
            ->assertSet('rentang', 'semua')
            // nilai asing tidak diterima
            ->call('gantiRentang', 'sembarang')
            ->assertSet('rentang', 'bulan');
    }

    public function test_kas_bulan_lalu_dihitung_lintas_tahun(): void
    {
        // Kode lama memakai whereYear(tahun ini) + whereMonth(bulan lalu),
        // sehingga pembanding pada bulan Januari selalu kosong.
        $this->travelTo(now()->setDate(now()->year, 1, 15));

        $pengguna = $this->buatPengguna('manager');
        $this->actingAs($pengguna);

        $this->catatDebit($pengguna, 500000, now()->subMonthNoOverflow()->startOfMonth()->addDays(3));
        $this->catatDebit($pengguna, 750000, now());

        $kas = Livewire::test(Dasbor::class)->instance()->kas();

        $this->assertSame(750000.0, $kas['masuk']);
        // 750rb dibanding 500rb bulan lalu = +50%
        $this->assertSame(50.0, $kas['masuk_selisih']);

        $this->travelBack();
    }

    public function test_pemasukan_tahun_ini_tidak_terbatas_bulan_mei(): void
    {
        // Cabang non-pengelola pada kode lama menimpa pemasukan tahun ini
        // dengan kueri yang dipatok bulan Mei.
        $pengguna = $this->buatPengguna('karyawan');
        $this->actingAs($pengguna);

        foreach ([1, 5, 9] as $bulan) {
            $this->catatDebit($pengguna, 100000, now()->setDate(now()->year, $bulan, 10));
        }

        $uji = Livewire::test(Dasbor::class)->call('gantiRentang', 'tahun');

        $this->assertSame(300000.0, $uji->instance()->kas()['masuk']);
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
