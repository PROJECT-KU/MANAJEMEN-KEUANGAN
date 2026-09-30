<?php

namespace Tests\Feature\ClinikScopus;

use App\ClinikScopusBiayaPersesi;
use App\Layanan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tambahan layar tarif hasil audit 30 Sep 2026.
 *
 * Empat di antaranya menutup cacat yang saya buat sendiri: layanan nonaktif
 * yang tidak bisa diaktifkan lagi, "berlakukan" yang menerima nol padahal
 * borangnya menolak, kolom pemakaian yang berbohong, dan urutan tampil yang
 * kolomnya ada tetapi tuasnya tidak.
 */
class TarifLanjutanTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Layanan::lupakanKatalog();
        // Penanda "jadwal sudah diperiksa" berumur satu permintaan di produksi,
        // tetapi satu PROSES di uji — tanpa dibuang, uji berikutnya tidak
        // pernah menaikkan tarif terjadwalnya.
        \App\ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();
    }

    private function akun(string $peran): User
    {
        $u = User::create([
            'full_name' => 'Uji ' . $peran,
            'username' => 'uji_lanj_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $u->forceFill(['status' => 'active', 'email_verified_at' => now(), 'peran' => $peran])->save();

        return $u->refresh();
    }

    // ------------------------------------------------- layanan nonaktif

    #[Test]
    public function layanan_nonaktif_tetap_terlihat_supaya_bisa_diaktifkan_lagi(): void
    {
        /*
         * Cacat yang paling berbahaya dari audit itu. Katalog hanya membaca
         * yang aktif dan kartu dibangun dari katalog, jadi layanan yang
         * dinonaktifkan lenyap sama sekali — padahal pesan penolakan hapus
         * justru menyarankan menonaktifkan. Jalan buntu yang dibuat oleh
         * nasihat kita sendiri.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $layanan = Layanan::where('kode', 'scopus_kafe')->first();
        $layanan->forceFill(['aktif' => false])->save();
        Layanan::lupakanKatalog();

        $halaman = $this->actingAs($admin)->get(route('account.Clinik-Scopus-Biaya-Persesi.index'));

        $halaman->assertOk();
        $halaman->assertSee('Layanan yang tidak dijual lagi', false);
        $halaman->assertSee('Scopus Kafe', false);
        $halaman->assertSee('Aktifkan lagi', false);

        $this->assertTrue(
            $halaman->viewData('nonaktif')->contains(fn ($l) => $l->kode === 'scopus_kafe'),
            'Layanan nonaktif harus dikirim ke layarnya.'
        );
    }

    #[Test]
    public function layanan_nonaktif_bisa_diaktifkan_lagi_lewat_borang_yang_sama(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $layanan = Layanan::where('kode', 'scopus_kafe')->first();
        $layanan->forceFill(['aktif' => false])->save();

        $this->actingAs($admin)->post(route('account.layanan.update', $layanan), [
            'nama' => 'Scopus Kafe', 'satuan' => 'per pertemuan',
            'ikon' => 'fa-coffee', 'warna' => 'mis-jingga', 'aktif' => 1,
        ])->assertSessionHasNoErrors();

        Layanan::lupakanKatalog();

        $this->assertTrue($layanan->refresh()->aktif);
        $this->assertArrayHasKey('scopus_kafe', Layanan::katalog());
    }

    // ------------------------------------------------------ tarif nol

    #[Test]
    public function tarif_nol_di_riwayat_tidak_bisa_diberlakukan(): void
    {
        /*
         * Dua pintu ke satu tempat harus punya aturan sama. Borang penyetelan
         * menolak nol mentah-mentah, tetapi "berlakukan lagi" dulu tidak
         * memeriksa apa pun — satu klik pada baris Rp 0 menjadikannya tarif
         * yang berlaku.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $nol = ClinikScopusBiayaPersesi::create([
            'layanan' => 'scopus_kafe', 'biaya_persesi' => 0,
            'status' => ClinikScopusBiayaPersesi::NONAKTIF,
        ]);

        $jawab = $this->actingAs($admin)
            ->postJson(route('account.Clinik-Scopus-Biaya-Persesi.berlakukan', $nol));

        $jawab->assertStatus(409);
        $this->assertStringContainsString('nol', $jawab->json('message'));
        $this->assertSame(ClinikScopusBiayaPersesi::NONAKTIF, $nol->refresh()->status);
    }

    // ------------------------------------------------------ jejak penyetel

    #[Test]
    public function penyetel_tarif_dicatat_otomatis(): void
    {
        // Dicatat sistem, bukan diminta dari borang: isian yang bisa dilewati
        // bukan jejak.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'layanan' => 'scopus_kafe',
            'biaya_persesi' => '1.250.000',
        ]);

        $tarif = ClinikScopusBiayaPersesi::berlaku('scopus_kafe');

        $this->assertSame($admin->id, (int) $tarif->penginput_id);
        $this->assertSame($admin->full_name, $tarif->penginput->full_name);
    }

    // -------------------------------------------------------- penjadwalan

    #[Test]
    public function tarif_bertanggal_menunggu_dan_tidak_mengganggu_harga_sekarang(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $sekarang = ClinikScopusBiayaPersesi::berlaku('scopus_kafe');

        $this->actingAs($admin)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'layanan' => 'scopus_kafe',
            'biaya_persesi' => '1.500.000',
            'berlaku_mulai' => now()->addMonth()->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->assertTrue(
            ClinikScopusBiayaPersesi::berlaku('scopus_kafe')->is($sekarang),
            'Harga yang berlaku tidak boleh berubah sebelum tanggalnya.'
        );

        $this->assertSame(1, ClinikScopusBiayaPersesi::untuk('scopus_kafe')
            ->where('status', ClinikScopusBiayaPersesi::TERJADWAL)->count());
    }

    #[Test]
    public function tarif_terjadwal_naik_sendiri_begitu_tanggalnya_tiba(): void
    {
        /*
         * Kenaikannya dikerjakan saat tarifnya DIBACA, bukan oleh penjadwal:
         * penjadwal yang tidak jalan membuat harga tertinggal tanpa ada yang
         * tahu, dan itu kegagalan yang paling mahal di sini.
         */
        $nanti = ClinikScopusBiayaPersesi::create([
            'layanan' => 'scopus_kafe', 'biaya_persesi' => 1500000,
            'berlaku_mulai' => now()->subDay(),
            'status' => ClinikScopusBiayaPersesi::TERJADWAL,
        ]);

        $berlaku = ClinikScopusBiayaPersesi::berlaku('scopus_kafe');

        $this->assertTrue($berlaku->is($nanti));
        $this->assertSame(ClinikScopusBiayaPersesi::AKTIF, $nanti->refresh()->status);
    }

    #[Test]
    public function tarif_terjadwal_tidak_ikut_dimatikan_oleh_penyetelan_lain(): void
    {
        // Menyetel harga hari ini tidak boleh membatalkan kenaikan yang sudah
        // dijadwalkan bulan depan.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $jadwal = ClinikScopusBiayaPersesi::create([
            'layanan' => 'scopus_kafe', 'biaya_persesi' => 1500000,
            'berlaku_mulai' => now()->addMonth(),
            'status' => ClinikScopusBiayaPersesi::TERJADWAL,
        ]);

        $this->actingAs($admin)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'layanan' => 'scopus_kafe', 'biaya_persesi' => '1.100.000',
        ]);

        $this->assertSame(ClinikScopusBiayaPersesi::TERJADWAL, $jadwal->refresh()->status);
    }

    #[Test]
    public function tanggal_mulai_di_masa_lalu_ditolak(): void
    {
        // Tanggal yang sudah lewat bukan jadwal; itu cuma cara berbelit untuk
        // memberlakukannya sekarang, dan hasilnya membingungkan di riwayat.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)
            ->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
                'layanan' => 'scopus_kafe', 'biaya_persesi' => '1.100.000',
                'berlaku_mulai' => now()->subWeek()->toDateString(),
            ])
            ->assertSessionHasErrors('berlaku_mulai');
    }

    // ------------------------------------------------ pemakaian yang jujur

    #[Test]
    public function pemakaian_hanya_dihitung_untuk_layanan_yang_memang_tertaut(): void
    {
        /*
         * Hanya sesi Clinik Scopus yang menyimpan biaya_persesi_id. Angkatan
         * layanan lain menyalin angkanya, tidak menunjuk barisnya — jadi untuk
         * mereka jumlahnya BUKAN nol, melainkan tidak diketahui. Menuliskannya
         * "Belum dipakai" adalah angka yang berbohong.
         */
        $clinik = ClinikScopusBiayaPersesi::create([
            'layanan' => 'clinik_scopus', 'biaya_persesi' => 125000,
            'status' => ClinikScopusBiayaPersesi::NONAKTIF,
        ]);

        $camp = ClinikScopusBiayaPersesi::create([
            'layanan' => 'scopus_camp', 'varian' => 'jawa', 'biaya_persesi' => 4500000,
            'status' => ClinikScopusBiayaPersesi::NONAKTIF,
        ]);

        $this->assertTrue($clinik->pemakaian_terhitung);
        $this->assertFalse($camp->pemakaian_terhitung);
    }

    #[Test]
    public function riwayat_bisa_disaring_per_layanan(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        ClinikScopusBiayaPersesi::create([
            'layanan' => 'scopus_kafe', 'biaya_persesi' => 900000,
            'status' => ClinikScopusBiayaPersesi::NONAKTIF,
        ]);

        $halaman = $this->actingAs($admin)->get(route('account.Clinik-Scopus-Biaya-Persesi.index', [
            'riwayat' => 'scopus_kafe',
        ]));

        $halaman->assertOk();

        foreach ($halaman->viewData('riwayat') as $baris) {
            $this->assertSame('scopus_kafe', $baris->layanan, 'Saringan riwayat kebobolan.');
        }
    }

    // ------------------------------------------------------ urutan tampil

    #[Test]
    public function urutan_tampil_bisa_diubah_dan_mengatur_susunan_kartu(): void
    {
        // Kolomnya ada sejak awal tetapi tidak pernah punya tuasnya, jadi
        // layanan baru selalu menempel di ujung.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $kafe = Layanan::where('kode', 'scopus_kafe')->first();

        $this->actingAs($admin)->post(route('account.layanan.update', $kafe), [
            'nama' => 'Scopus Kafe', 'satuan' => 'per pertemuan',
            'ikon' => 'fa-coffee', 'warna' => 'mis-jingga', 'aktif' => 1, 'urutan' => 1,
        ])->assertSessionHasNoErrors();

        Layanan::lupakanKatalog();

        $this->assertSame(1, $kafe->refresh()->urutan);
        $this->assertSame('scopus_kafe', array_key_first(Layanan::katalog()));
    }

    #[Test]
    public function urutan_yang_tidak_dikirim_tidak_menimpa_yang_sudah_ada(): void
    {
        // Dinolkan, layanan itu melompat ke urutan paling depan tanpa diminta.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $kafe = Layanan::where('kode', 'scopus_kafe')->first();
        $semula = $kafe->urutan;

        $this->actingAs($admin)->post(route('account.layanan.update', $kafe), [
            'nama' => 'Scopus Kafe', 'satuan' => 'per pertemuan',
            'ikon' => 'fa-coffee', 'warna' => 'mis-jingga', 'aktif' => 1,
        ]);

        $this->assertSame($semula, $kafe->refresh()->urutan);
    }
}
