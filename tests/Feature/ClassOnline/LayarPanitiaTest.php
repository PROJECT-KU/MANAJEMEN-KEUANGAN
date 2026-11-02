<?php

namespace Tests\Feature\ClassOnline;

use App\CategoriesOnlineTraining;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Layar panitia untuk angkatan Class Online.
 *
 * Tanpa layar ini layanan itu tidak bisa dipakai siapa pun: halaman umumnya
 * memang berdiri, tetapi angkatannya cuma bisa dibuat lewat tinker. Yang
 * dijaga di sini SAMBUNGANNYA — rute, kelas model berglobal scope, dan
 * cetakan Blade-nya — bukan logika penyuntingannya, yang memang disalin utuh
 * dari layar Scopus Camp.
 */
class LayarPanitiaTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        $u = User::create([
            'full_name' => 'Uji CO ' . Str::random(4),
            'username' => 'uji_co_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $u->forceFill([
            'status' => 'active',
            'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR,
        ])->save();

        return $u->refresh();
    }

    #[Test]
    public function daftar_tambah_dan_sunting_angkatan_terbuka(): void
    {
        $admin = $this->admin();

        $a = CategoriesOnlineTraining::create([
            'token' => Str::random(30),
            'nama' => 'UJI Panitia CO ' . Str::random(6),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(40)->toDateString(),
            'lokasi' => 'Zoom',
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'biaya' => 3500000, 'status' => 'active',
        ]);

        // DIBESARKAN, sama seperti layar Scopus Camp: cetakannya memakai
        // strtoupper() pada nama kategorinya.
        $this->actingAs($admin)
            ->get(route('account.kategorionlinetraining.index'))
            ->assertOk()
            ->assertSee(mb_strtoupper($a->nama) . ' #' . $a->nama_ke);

        $this->actingAs($admin)
            ->get(route('account.kategorionlinetraining.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('account.kategorionlinetraining.edit', $a->getKey()))
            ->assertOk()
            ->assertSee($a->nama);
    }

    #[Test]
    public function angkatan_yang_dibuat_panitia_berlayanan_online_training(): void
    {
        /*
         * Tabel `kategori_layanan` dipakai BERSAMA keenam layanan, dan
         * kolom `layanan` yang memisahkannya. Dibuat lewat kelas induk yang
         * tidak menyetelnya, barisnya tersimpan TANPA layanan — tidak akan
         * pernah muncul lagi di layar mana pun, dan tidak ada galat yang
         * memberi tahu. Terukur saat layanan ini dirakit: kuerinya justru
         * memulangkan angkatan SEMUA layanan, 380+ baris.
         */
        $a = CategoriesOnlineTraining::create([
            'token' => Str::random(30),
            'nama' => 'UJI Layanan CO ' . Str::random(6),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(40)->toDateString(),
            'total_kuota' => 5, 'sisa_kuota' => 5,
            'biaya' => 3500000, 'status' => 'active',
        ]);

        $this->assertSame('online_training', (string) $a->refresh()->layanan);

        // Dan kuerinya TIDAK memulangkan angkatan layanan lain.
        $lain = CategoriesOnlineTraining::pluck('layanan')->unique()->values()->all();

        $this->assertSame(['online_training'], $lain);
    }

    #[Test]
    public function layar_pendaftar_bersama_mengenali_layanan_ini(): void
    {
        $admin = $this->admin();

        // Layar Pendaftar Layanan membaca keenam layanan lewat katalog; kalau
        // butirnya tidak lengkap, ia 500 atau memulangkan daftar kosong tanpa
        // sebab yang kelihatan.
        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.index', ['layanan' => 'online_training']))
            ->assertOk()
            ->assertSee('Class Online');
    }
}
