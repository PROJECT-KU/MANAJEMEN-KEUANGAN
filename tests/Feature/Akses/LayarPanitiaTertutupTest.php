<?php

namespace Tests\Feature\Akses;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Layar panitia tidak terbuka untuk pelanggan.
 *
 * ---------------------------------------------------------------------------
 * Kenapa uji ini ada
 * ---------------------------------------------------------------------------
 *
 * Grup rute `account/` hanya ber-middleware `web, auth, terverifikasi` — tidak
 * ada middleware peran sama sekali, dan tidak ada aliasnya di Kernel. Jadi
 * penjagaannya diserahkan ke tiap pengendali, satu per satu, dan yang lupa
 * memasangnya terbuka untuk siapa pun yang punya akun. Peran bawaan kolomnya
 * `'user'`: tiap pelanggan yang mendaftar sudah memenuhi `auth` +
 * `terverifikasi`.
 *
 * Terukur 3 Nov 2026 dengan satu akun berperan `user`, SEMBILAN layar panitia
 * menjawab HTTP 200 untuknya — dan di layar Kategori, DELETE-nya benar-benar
 * menghapus angkatan.
 *
 * ---------------------------------------------------------------------------
 * Kenapa diuji dengan DUA akun, bukan satu
 * ---------------------------------------------------------------------------
 *
 * Uji fitur yang membuat penggunanya dengan `User::create([...])` saja akan
 * HIJAU walau layarnya tidak terjaga: perannya bawaan `user`, dan layar yang
 * tidak menjaga apa pun memang menerimanya. Jadi tiap layar di sini dicoba
 * dua kali — sekali sebagai administrator yang HARUS masuk, sekali sebagai
 * pelanggan yang HARUS ditolak. Yang pertama menjaga supaya penjagaannya
 * tidak sekaligus mengunci orang yang berhak.
 */
class LayarPanitiaTertutupTest extends TestCase
{
    use DatabaseTransactions;

    /** Layar yang hanya boleh dibuka administrator. */
    private const LAYAR = [
        'account.Artikel.index',
        'account.Kategori-Artikel.index',
        'account.refrensi-paper.index',
        'account.camp.index',
        'account.meme.index',
        'account.paperisasi.index',
        'account.maintenance.index',
        'account.peserta.list',
        'karir.index',
        'account.kategorionlinetraining.index',
        'account.kategoriscopuscamp.index',
        'account.kategorianalisisbibliometrik.index',
    ];

    private function akun(string $peran): User
    {
        $u = User::create([
            'full_name' => 'Uji Akses ' . Str::random(4),
            'username' => 'uji_ak_' . Str::random(8),
            'email' => Str::lower(Str::random(10)) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        // email_verified_at tidak ada di $fillable — tanpa forceFill,
        // middleware `terverifikasi` mengalihkan semuanya dan ujinya merah
        // karena alasan yang salah.
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(), 'peran' => $peran])->save();

        return $u->refresh();
    }

    #[Test]
    public function pelanggan_ditolak_di_semua_layar_panitia(): void
    {
        $pelanggan = $this->akun('user');

        $this->assertFalse($pelanggan->adalahAdministrator());
        $this->assertFalse($pelanggan->adalahOrangDalam());

        foreach (self::LAYAR as $nama) {
            if (! Route::has($nama)) {
                continue;
            }

            $this->actingAs($pelanggan)->get(route($nama))->assertForbidden($nama);
        }
    }

    #[Test]
    public function administrator_tetap_bisa_masuk_ke_semuanya(): void
    {
        /*
         * Setengah lain dari penjagaan, dan yang paling mudah terlupa:
         * pagar yang terlalu rapat sama buruknya dengan yang tidak ada.
         * Tanpa uji ini, salah ketik nama peran akan mengunci seluruh
         * panitia dari layarnya sendiri dan tidak ada yang tahu sampai ada
         * yang mengeluh.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        foreach (self::LAYAR as $nama) {
            if (! Route::has($nama)) {
                continue;
            }

            $this->actingAs($admin)->get(route($nama))->assertOk($nama);
        }
    }

    #[Test]
    public function menghapus_pun_ditolak_bukan_hanya_melihat(): void
    {
        /*
         * Yang paling berbahaya bukan layar yang terbaca melainkan tombol
         * yang bisa ditekan. Terukur sebelum penjagaannya dipasang: DELETE
         * ke layar Kategori menjawab 200 dan angkatannya BENAR-BENAR HILANG.
         */
        $pelanggan = $this->akun('user');

        $a = \App\CategoriesOnlineTraining::create([
            'token' => Str::random(30),
            'nama' => 'UJI Akses Hapus ' . Str::random(6),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(40)->toDateString(),
            'total_kuota' => 5, 'sisa_kuota' => 5,
            'biaya' => 3500000, 'status' => 'active',
        ]);

        $this->actingAs($pelanggan)
            ->delete(route('account.kategorionlinetraining.destroy', $a->getKey()))
            ->assertForbidden();

        $this->assertTrue(\App\CategoriesOnlineTraining::whereKey($a->getKey())->exists());
    }

    // ------------------------------------------------- yang TIDAK boleh ikut

    #[Test]
    public function halaman_maintenance_publik_tetap_terbuka_tanpa_masuk(): void
    {
        /*
         * MaintenanceController melayani SATU rute publik — /page-maintenance,
         * tanpa auth sama sekali. Ikut dijaga administrator, situsnya sendiri
         * tidak bisa dibuka justru saat sedang dimatikan: pengunjung
         * mendapat 403, bukan halaman "sedang perbaikan".
         *
         * Karena itu method page() ada di daftar kecuali penjaganya.
         */
        $this->get('/page-maintenance')->assertOk();
    }

    #[Test]
    public function lembar_kerja_peserta_tidak_ikut_terkunci(): void
    {
        /*
         * Empat aksi PesertaController bukan layar panitia melainkan lembar
         * kerja pesertanya sendiri — borangnya, penyimpanannya, penandaan
         * selesai, dan testimoni bertoken. Ikut dijaga administrator,
         * peserta terkunci di luar lembar yang dibuat untuknya.
         *
         * Yang diperiksa: jawabannya BUKAN 403. Kode persisnya sengaja tidak
         * dipatok — rute ini kadang 200 dan kadang mengalihkan, tergantung
         * keadaan sesi, dan keduanya sudah begitu sebelum penjagaan ini
         * dipasang. Memaku salah satunya membuat uji ini merah karena hal
         * yang bukan urusannya.
         */
        $pelanggan = $this->akun('user');

        $jawab = $this->actingAs($pelanggan)->get(route('account.peserta.form'));

        $this->assertNotSame(403, $jawab->status(),
            'Penjaga administrator ikut mengunci lembar kerja pesertanya sendiri.');
    }
}
