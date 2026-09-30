<?php

namespace Tests\Feature\Layanan;

use App\ClinikScopusBiayaPersesi;
use App\KategoriLayanan;
use App\Layanan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Katalog layanan, sesudah pindah dari kode ke basis data.
 *
 * Yang dijaga di sini terutama satu hal: `kode` adalah kunci yang dipegang
 * tabel lain. Tarif dan angkatan menyimpan nilai itu di kolom `layanan`, dan
 * tidak ada kunci asing yang menjaganya — jadi kalau kodenya berubah atau
 * layanannya dihapus, keduanya menggantung TANPA galat apa pun.
 */
class KatalogLayananTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Layanan::lupakanKatalog();
    }

    private function akun(string $peran): User
    {
        $u = User::create([
            'full_name' => 'Uji ' . $peran,
            'username' => 'uji_lyn_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $u->forceFill(['status' => 'active', 'email_verified_at' => now(), 'peran' => $peran])->save();

        return $u->refresh();
    }

    private function isian(array $lain = []): array
    {
        return array_merge([
            'nama' => 'Sharing Session Eksklusif',
            'satuan' => 'per peserta',
            'ikon' => 'fa-comments',
            'warna' => 'mis-biru',
            'aktif' => 1,
        ], $lain);
    }

    // ------------------------------------------------------------- menambah

    #[Test]
    public function layanan_baru_langsung_muncul_di_katalog(): void
    {
        /*
         * Inti pemindahan ini: sebelumnya menambah layanan berarti mengubah
         * kode lalu deploy. Sekarang cukup satu borang, dan seluruh layar yang
         * membaca katalog ikut tanpa disentuh.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)
            ->post(route('account.layanan.store'), $this->isian())
            ->assertRedirect(route('account.Clinik-Scopus-Biaya-Persesi.index'));

        Layanan::lupakanKatalog();
        $katalog = ClinikScopusBiayaPersesi::layanan();

        $this->assertArrayHasKey('sharing_session_eksklusif', $katalog);
        $this->assertSame('Sharing Session Eksklusif', $katalog['sharing_session_eksklusif']['nama']);
        $this->assertSame('per peserta', $katalog['sharing_session_eksklusif']['satuan']);
    }

    #[Test]
    public function tarif_bisa_langsung_disetel_untuk_layanan_yang_baru_ditambah(): void
    {
        // Tanpa ini penambahannya setengah jalan: layanannya ada, tapi
        // validator tarif menolaknya karena daftarnya masih yang lama.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->post(route('account.layanan.store'), $this->isian());
        Layanan::lupakanKatalog();

        $this->actingAs($admin)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'layanan' => 'sharing_session_eksklusif',
            'biaya_persesi' => '250.000',
        ]);

        $tarif = ClinikScopusBiayaPersesi::berlaku('sharing_session_eksklusif');

        $this->assertNotNull($tarif, 'Tarif layanan baru harus bisa disetel.');
        $this->assertSame(250000, (int) $tarif->biaya_persesi);
        $this->assertSame('Sharing Session Eksklusif', $tarif->nama_layanan);
        $this->assertSame('per peserta', $tarif->satuan);
    }

    #[Test]
    public function angkatan_bisa_dibuat_untuk_layanan_yang_baru_ditambah(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->post(route('account.layanan.store'), $this->isian());
        Layanan::lupakanKatalog();

        $this->actingAs($admin)->post(route('account.kategori-layanan.store'), [
            'layanan' => 'sharing_session_eksklusif',
            'nama' => 'Sharing Session #1',
            'mulai' => '2026-11-20',
            'status' => 'draft',
        ]);

        $angkatan = KategoriLayanan::where('nama', 'Sharing Session #1')->first();

        $this->assertNotNull($angkatan);
        $this->assertSame('Sharing Session Eksklusif', $angkatan->nama_layanan);
    }

    #[Test]
    public function kode_dibuat_dari_namanya_dan_tidak_bentrok(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->post(route('account.layanan.store'), $this->isian());
        $this->actingAs($admin)->post(route('account.layanan.store'),
            $this->isian(['nama' => 'Sharing Session Eksklusif!']));

        $kode = Layanan::whereIn('nama', ['Sharing Session Eksklusif', 'Sharing Session Eksklusif!'])
            ->pluck('kode')->sort()->values()->all();

        $this->assertSame(['sharing_session_eksklusif', 'sharing_session_eksklusif_2'], $kode);
    }

    #[Test]
    public function nama_yang_sudah_ada_ditolak(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)
            ->post(route('account.layanan.store'), $this->isian(['nama' => 'Scopus Camp']))
            ->assertSessionHasErrors('nama');
    }

    #[Test]
    public function ikon_di_luar_daftar_ditolak(): void
    {
        /*
         * Penjaga penting. Proyek ini memakai Font Awesome 5; nama FA6 tidak
         * merender apa pun TANPA galat. IkonAdaGlifnyaTest memindai berkas
         * tampilan, tetapi nama yang datang dari basis data lolos dari
         * pemindaian itu — jadi penjaganya harus di validator.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)
            ->post(route('account.layanan.store'), $this->isian(['ikon' => 'fa-pen-to-square']))
            ->assertSessionHasErrors('ikon');

        $this->assertSame(0, Layanan::where('nama', 'Sharing Session Eksklusif')->count());
    }

    #[Test]
    public function seluruh_ikon_pilihan_benar_benar_ada_di_font_awesome_5(): void
    {
        $css = file_get_contents(public_path('assets/modules/fontawesome/css/all.css'));
        $hilang = [];

        foreach (array_keys(Layanan::IKON) as $ikon) {
            if (! str_contains($css, '.' . $ikon . ':before')) {
                $hilang[] = $ikon;
            }
        }

        $this->assertSame([], $hilang, 'Ikon pilihan tanpa glif: ' . implode(', ', $hilang));
    }

    // --------------------------------------------------------------- varian

    #[Test]
    public function varian_diketik_satu_per_baris_dan_dapat_kodenya_sendiri(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->post(route('account.layanan.store'),
            $this->isian(['varian' => "Tatap Muka\nDaring"]));

        $this->assertSame(
            ['tatap_muka' => 'Tatap Muka', 'daring' => 'Daring'],
            Layanan::where('kode', 'sharing_session_eksklusif')->first()->varian_peta
        );
    }

    #[Test]
    public function kode_varian_bertahan_walau_layanannya_disunting_lagi(): void
    {
        /*
         * Kode varian tersimpan di tarif dan angkatan. Kalau ia dibuat ulang
         * tiap kali layanannya disunting, tarif yang menunjuk kode lama jadi
         * tidak terbaca — dan borang pemesanannya menampilkan harga kosong.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->post(route('account.layanan.store'),
            $this->isian(['varian' => "Tatap Muka\nDaring"]));

        $layanan = Layanan::where('kode', 'sharing_session_eksklusif')->first();

        $this->actingAs($admin)->post(route('account.layanan.update', $layanan),
            $this->isian(['nama' => 'Sharing Session Premium', 'varian' => "Tatap Muka\nDaring"]));

        $layanan->refresh();

        $this->assertSame('Sharing Session Premium', $layanan->nama);
        $this->assertSame('sharing_session_eksklusif', $layanan->kode, 'Kodenya tidak boleh ikut nama.');
        $this->assertSame(['tatap_muka' => 'Tatap Muka', 'daring' => 'Daring'], $layanan->varian_peta);
    }

    #[Test]
    public function varian_yang_sudah_dipakai_tidak_bisa_dibuang(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $layanan = Layanan::where('kode', 'scopus_camp')->first();

        $this->actingAs($admin)
            ->post(route('account.layanan.update', $layanan), [
                'nama' => 'Scopus Camp', 'satuan' => 'per peserta',
                'ikon' => 'fa-campground', 'warna' => 'mis-hijau',
                'varian' => 'Pulau Jawa',   // luar_jawa dibuang
            ])
            ->assertSessionHasErrors('varian');

        $this->assertArrayHasKey('luar_jawa', $layanan->refresh()->varian_peta);
    }

    // ------------------------------------------------------------ menghapus

    #[Test]
    public function layanan_yang_sudah_punya_tarif_tidak_bisa_dihapus(): void
    {
        /*
         * Tarif dan angkatan menyimpan KODE layanan, bukan kunci asing, jadi
         * basis data tidak akan menolak apa pun. Yang terjadi kalau dibiarkan:
         * keduanya menggantung tanpa nama, diam-diam.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $layanan = Layanan::where('kode', 'scopus_camp')->first();

        $jawab = $this->actingAs($admin)
            ->deleteJson(route('account.layanan.destroy', $layanan));

        $jawab->assertStatus(409);
        $this->assertStringContainsString('tarif', $jawab->json('message'));
        $this->assertNotNull(Layanan::find($layanan->getKey()));
    }

    #[Test]
    public function layanan_yang_belum_dipakai_boleh_dihapus(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->post(route('account.layanan.store'), $this->isian());
        $layanan = Layanan::where('kode', 'sharing_session_eksklusif')->first();

        $this->actingAs($admin)
            ->deleteJson(route('account.layanan.destroy', $layanan))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNull(Layanan::find($layanan->getKey()));
    }

    #[Test]
    public function menonaktifkan_menyembunyikan_tanpa_menghapus_datanya(): void
    {
        // Jalan keluar untuk layanan yang sudah tidak dijual tapi datanya harus
        // tetap utuh — dan itulah yang disarankan pesan penolakan hapusnya.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $layanan = Layanan::where('kode', 'scopus_kafe')->first();
        $sebelum = ClinikScopusBiayaPersesi::untuk('scopus_kafe')->count();

        $this->actingAs($admin)->post(route('account.layanan.update', $layanan), [
            'nama' => 'Scopus Kafe', 'satuan' => 'per pertemuan',
            'ikon' => 'fa-coffee', 'warna' => 'mis-jingga',
        ])->assertSessionHasNoErrors();

        Layanan::lupakanKatalog();

        $this->assertFalse($layanan->refresh()->aktif);
        $this->assertArrayNotHasKey('scopus_kafe', ClinikScopusBiayaPersesi::layanan());
        $this->assertSame($sebelum, ClinikScopusBiayaPersesi::untuk('scopus_kafe')->count());
    }

    // ------------------------------------------------------------------ hak

    #[Test]
    public function karyawan_tidak_boleh_menambah_mengubah_atau_menghapus(): void
    {
        $karyawan = $this->akun(User::PERAN_KARYAWAN);
        $sebelum = Layanan::count();
        $layanan = Layanan::where('kode', 'scopus_kafe')->first();

        $this->actingAs($karyawan)->post(route('account.layanan.store'), $this->isian());
        $this->actingAs($karyawan)->post(route('account.layanan.update', $layanan),
            $this->isian(['nama' => 'Diubah karyawan']));
        $this->actingAs($karyawan)
            ->deleteJson(route('account.layanan.destroy', $layanan))
            ->assertStatus(403);

        $this->assertSame($sebelum, Layanan::count());
        $this->assertSame('Scopus Kafe', $layanan->refresh()->nama);
    }

    #[Test]
    public function karyawan_tidak_disuguhi_tombol_tambah_layanan(): void
    {
        $karyawan = $this->akun(User::PERAN_KARYAWAN);

        $isi = $this->actingAs($karyawan)
            ->get(route('account.Clinik-Scopus-Biaya-Persesi.index'))->getContent();

        $this->assertStringNotContainsString('data-layanan-baru', $isi);
        $this->assertStringNotContainsString('data-layanan=', $isi);
    }
}
