<?php

namespace Tests\Feature\Akun;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Hak akses ditentukan PERAN, bukan JABATAN.
 *
 * Dulu keduanya ditumpuk di kolom `level`, sehingga menamai seseorang
 * "manager" otomatis memberinya hak pengelola. Sekarang jabatan hanya
 * penanda posisi; menyandangnya tidak menambah hak apa pun.
 */
class PeranBukanJabatanTest extends TestCase
{
    use DatabaseTransactions;

    /* Jabatan default '' , bukan null: kolom level masih NOT NULL di basis
       data, jadi kosong hanya bisa diwakili untai kosong. */
    private function buat(string $peran, string $jabatan = ''): User
    {
        $pengguna = User::create([
            'full_name' => 'Uji Peran',
            'username' => 'uji_peran_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'peran' => $peran,
            'level' => $jabatan,
        ]);

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    #[Test]
    public function hanya_tiga_peran_yang_dikenal(): void
    {
        $this->assertSame(
            ['administrator', 'karyawan', 'user'],
            User::SEMUA_PERAN
        );
    }

    #[Test]
    public function jabatan_manager_tidak_memberi_hak_pengelola(): void
    {
        // Inti seluruh perubahan ini: nama jabatan tidak boleh membuka pintu.
        $pengguna = $this->buat('karyawan', 'manager');

        $this->assertFalse($pengguna->adalahAdministrator());
        $this->assertTrue($pengguna->adalahKaryawan());

        // Ditolaknya berupa pengalihan ke dasbor dengan pesan, bukan 403 —
        // itu cara halaman ini menolak sejak awal, dan yang diuji di sini
        // adalah SIAPA yang ditolak, bukan bentuk penolakannya.
        $this->actingAs($pengguna)
            ->get(route('account.aktivitas-masuk.index'))
            ->assertRedirect(route('account.dashboard.index'))
            ->assertSessionHas('error');
    }

    #[Test]
    public function administrator_boleh_walau_jabatannya_kosong(): void
    {
        // Kebalikannya: peran yang membuka pintu, bukan ada-tidaknya jabatan.
        $pengguna = $this->buat('administrator', '');

        $this->actingAs($pengguna)
            ->get(route('account.aktivitas-masuk.index'))
            ->assertOk();
    }

    #[Test]
    public function pelanggan_bukan_orang_dalam(): void
    {
        $this->assertFalse($this->buat('user')->adalahOrangDalam());
        $this->assertTrue($this->buat('karyawan')->adalahOrangDalam());
        $this->assertTrue($this->buat('administrator')->adalahOrangDalam());
    }

    #[Test]
    public function punya_peran_menerima_beberapa_sekaligus(): void
    {
        $pengguna = $this->buat('karyawan');

        $this->assertTrue($pengguna->punyaPeran('administrator', 'karyawan'));
        $this->assertFalse($pengguna->punyaPeran('administrator', 'user'));
    }

    #[Test]
    public function peran_baru_tidak_boleh_di_luar_tiga_itu(): void
    {
        // Lapisan terakhir: walau pengelola yang mengirim, nilai asing ditolak.
        $pengelola = $this->buat('administrator');
        $anggota = $this->buat('karyawan');

        $this->actingAs($pengelola)
            ->post(route('account.pengguna.update', $anggota), ['peran' => 'karyawan'])
            ->assertRedirect();

        $this->assertSame('karyawan', $anggota->refresh()->peran);
    }
}
