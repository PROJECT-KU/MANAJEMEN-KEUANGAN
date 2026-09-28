<?php

namespace Tests\Feature\Akun;

use App\AktivitasMasuk;
use App\Livewire\Akun\KeamananAkun;
use App\Mail\PemberitahuanKeluarPerangkatMail;
use App\PerangkatPin;
use App\Support\IngatanMasuk;
use App\Support\PenandaPerangkat;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class KeamananAkunTest extends TestCase
{
    use DatabaseTransactions;

    private const SANDI = 'RahasiaUji2026';

    private function buatPengguna(): User
    {
        $pengguna = User::create([
            'full_name' => 'Uji Keamanan',
            'username' => 'uji_keamanan_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make(self::SANDI),
            'level' => 'karyawan',
        ]);

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    /**
     * Kue yang membuat peramban uji dianggap perangkat PIN terdaftar: kue
     * penanda DAN barisnya di peladen. Kue saja tidak cukup sejak izin PIN
     * diperiksa di peladen — itu memang maksudnya.
     */
    private function kuePerangkatTerdaftar(User $pengguna): array
    {
        $penanda = str_repeat('b', 32);

        PerangkatPin::firstOrCreate(
            ['user_id' => $pengguna->getKey(), 'penanda' => $penanda],
            ['peramban' => 'Peramban uji', 'ip' => '127.0.0.1', 'terakhir_dipakai_pada' => now()]
        );

        return [
            IngatanMasuk::NAMA => json_encode([
                'identitas' => $pengguna->username,
                'mode' => 'pin',
                'ingat' => false,
                'uid' => $pengguna->getKey(),
            ]),
            PenandaPerangkat::NAMA => $penanda,
        ];
    }

    /** Perangkat lain dibuatkan barisnya langsung; ia tidak punya kue di sini. */
    private function perangkatLain(User $pengguna, string $peramban = 'Peramban HP'): PerangkatPin
    {
        return PerangkatPin::create([
            'user_id' => $pengguna->getKey(),
            'penanda' => str_repeat('c', 32),
            'peramban' => $peramban,
            'ip' => '114.10.22.9',
            'terakhir_dipakai_pada' => now()->subHour(),
        ]);
    }

    /**
     * Inilah yang dulu tidak mungkin: mencabut izin PIN sebuah perangkat dari
     * perangkat lain. Sebelum ada catatan di peladen, HP yang hilang hanya
     * bisa dilumpuhkan dengan mematikan PIN untuk semua perangkat sekaligus.
     */
    public function test_izin_perangkat_lain_bisa_dicabut_dari_sini(): void
    {
        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');
        $this->actingAs($pengguna);

        $lain = $this->perangkatLain($pengguna);

        Testable::create(KeamananAkun::class, [], [], $this->kuePerangkatTerdaftar($pengguna))
            ->call('cabutIzinPin', $lain->id)
            ->assertDispatched('toast', jenis: 'berhasil');

        $this->assertNull(PerangkatPin::find($lain->id));

        // PIN akunnya sendiri tidak ikut mati — yang dicabut izin perangkatnya.
        $this->assertTrue($pengguna->refresh()->pinAktif());
    }

    public function test_perangkat_yang_sedang_dipakai_tidak_dicabut_lewat_jalan_itu(): void
    {
        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');
        $this->actingAs($pengguna);

        $kue = $this->kuePerangkatTerdaftar($pengguna);
        $ini = PerangkatPin::where('user_id', $pengguna->getKey())->first();

        Testable::create(KeamananAkun::class, [], [], $kue)
            ->call('cabutIzinPin', $ini->id)
            ->assertDispatched('toast', jenis: 'gagal');

        $this->assertNotNull(PerangkatPin::find($ini->id));
    }

    /** Nomor baris milik orang lain tidak boleh bisa dicabut dari sini. */
    public function test_perangkat_milik_akun_lain_tidak_bisa_dicabut(): void
    {
        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');

        $orangLain = $this->buatPengguna();
        $milikOrangLain = $this->perangkatLain($orangLain);

        $this->actingAs($pengguna);

        Testable::create(KeamananAkun::class, [], [], $this->kuePerangkatTerdaftar($pengguna))
            ->call('cabutIzinPin', $milikOrangLain->id)
            ->assertDispatched('toast', jenis: 'gagal');

        $this->assertNotNull(PerangkatPin::find($milikOrangLain->id));
    }

    public function test_daftar_perangkat_menaruh_yang_sedang_dipakai_di_atas(): void
    {
        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');
        $this->actingAs($pengguna);

        // Perangkat lain ini terakhir dipakai lebih BARU daripada yang sedang
        // dibuka, jadi urutan menurut waktu saja akan menaruhnya di atas.
        $lain = $this->perangkatLain($pengguna);
        $lain->forceFill(['terakhir_dipakai_pada' => now()->addHour()])->save();

        $kue = $this->kuePerangkatTerdaftar($pengguna);

        $daftar = Testable::create(KeamananAkun::class, [], [], $kue)->instance()->daftarPerangkatPin();

        $this->assertTrue($daftar->first()->ini, 'Perangkat yang sedang dipakai harus di baris pertama.');
        $this->assertCount(2, $daftar);
    }

    /**
     * Sesi tiruan milik pengguna, dengan keaktifan yang bisa diatur.
     *
     * daftarSesi() hanya membaca tabel sessions kalau driver sesinya memang
     * 'database'; di lingkungan uji driver-nya array, jadi tanpa baris ini
     * daftarnya selalu kosong dan ujinya lulus tanpa menguji apa pun.
     */
    private function buatSesi(User $pengguna, string $peramban, int $menitLalu): string
    {
        config(['session.driver' => 'database']);

        $id = 'uji' . uniqid();

        DB::table('sessions')->insert([
            'id' => $id,
            'user_id' => $pengguna->getKey(),
            'ip_address' => '114.10.22.9',
            'user_agent' => $peramban,
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->subMinutes($menitLalu)->getTimestamp(),
        ]);

        return $id;
    }

    /**
     * Daftar perangkat dilipat setelah tiga baris. Tanpa itu, akun yang
     * dipakai di banyak perangkat menampilkan sampai 20 baris berjajar dan
     * mengubur riwayat keamanan di bawahnya.
     */
    public function test_daftar_perangkat_dilipat_sesudah_tiga(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        for ($i = 0; $i < 6; $i++) {
            $this->buatSesi($pengguna, 'Peramban uji ke-' . $i, $i + 1);
        }

        $uji = Livewire::test(KeamananAkun::class);

        $this->assertCount(3, $uji->viewData('sesiTampil'));
        $this->assertSame(3, $uji->viewData('sisaPerangkat'));

        // $toggle tidak bisa dipanggil lewat ->call() di harness Livewire;
        // yang diuji tetap cabang tampilan yang sama.
        $uji->set('semuaPerangkat', true);

        $this->assertCount(6, $uji->viewData('sesiTampil'));
    }

    /**
     * Perangkat yang sedang dipakai selalu di urutan pertama — justru baris
     * itulah yang TIDAK boleh diakhiri, jadi ia tidak boleh tersembunyi di
     * balik lipatan. Urutan menurut keaktifan saja tidak menjaminnya.
     */
    public function test_perangkat_yang_sedang_dipakai_selalu_di_atas(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        // Sesi ini paling lama tidak aktif, tetapi inilah yang sedang dipakai.
        $this->buatSesi($pengguna, 'Peramban saya', 90);
        DB::table('sessions')->where('user_agent', 'Peramban saya')
            ->update(['id' => session()->getId()]);

        for ($i = 0; $i < 4; $i++) {
            $this->buatSesi($pengguna, 'Peramban lain ke-' . $i, $i);
        }

        $tampil = Livewire::test(KeamananAkun::class)->viewData('sesiTampil');

        $this->assertTrue($tampil->first()->ini, 'Perangkat ini harus jadi baris pertama.');
    }

    /**
     * Saringan "hanya yang gagal". Halaman ini gunanya menjawab "ada yang
     * bukan saya?", dan baris gagal itulah yang paling mungkin menjawabnya —
     * tetapi di akun yang sering dipakai ia tenggelam di antara baris wajar.
     */
    public function test_riwayat_bisa_disaring_hanya_yang_gagal(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        AktivitasMasuk::catat($pengguna, $pengguna->username, true, 'masuk biasa');
        AktivitasMasuk::catat($pengguna, $pengguna->username, false, 'kata sandi salah');

        $uji = Livewire::test(KeamananAkun::class);

        $this->assertCount(2, $uji->viewData('riwayat'));

        $uji->set('hanyaGagal', true);

        $disaring = $uji->viewData('riwayat');

        $this->assertCount(1, $disaring);
        $this->assertSame('kata sandi salah', $disaring->first()->alasan);

        // Jumlah totalnya ikut menyesuaikan, kalau tidak penanda "terpotong"
        // akan berbohong.
        $this->assertSame(1, $uji->viewData('totalRiwayat'));
    }

    /**
     * Mengakhiri sesi di perangkat lain memang alat pengamanan, tetapi ia
     * juga bisa dipakai orang yang sudah terlanjur masuk untuk mengusir
     * pemilik aslinya. Sampai sekarang itu satu-satunya tindakan besar di
     * halaman ini yang tidak meninggalkan kabar apa pun.
     */
    public function test_keluar_dari_perangkat_lain_mengabari_pemilik_akun(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        RateLimiter::clear('keluarkan-perangkat|' . $pengguna->getKey());

        Livewire::test(KeamananAkun::class)
            ->set('kataSandi', self::SANDI)
            ->call('keluarkanPerangkatLain')
            ->assertHasNoErrors();

        Mail::assertSent(
            PemberitahuanKeluarPerangkatMail::class,
            fn ($surat) => $surat->hasTo($pengguna->email)
        );
    }

    public function test_kata_sandi_salah_tidak_mengabari_siapa_pun(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        RateLimiter::clear('keluarkan-perangkat|' . $pengguna->getKey());

        Livewire::test(KeamananAkun::class)
            ->set('kataSandi', 'SandiKeliru2026')
            ->call('keluarkanPerangkatLain')
            ->assertHasErrors('kataSandi');

        Mail::assertNothingSent();
    }

    public function test_pengguna_melihat_riwayat_masuknya_sendiri(): void
    {
        $pengguna = $this->buatPengguna();
        $lain = $this->buatPengguna();

        AktivitasMasuk::create([
            'user_id' => $pengguna->getKey(),
            'identitas' => $pengguna->username,
            'berhasil' => false,
            'alasan' => 'kata sandi salah',
            'ip' => '203.0.113.7',
        ]);

        AktivitasMasuk::create([
            'user_id' => $lain->getKey(),
            'identitas' => $lain->username,
            'berhasil' => false,
            'alasan' => 'kata sandi salah',
            'ip' => '198.51.100.9',
        ]);

        $this->actingAs($pengguna);

        Livewire::test(KeamananAkun::class)
            ->assertSee('203.0.113.7')
            ->assertDontSee('198.51.100.9');
    }

    public function test_bisa_keluar_dari_perangkat_lain_dengan_kata_sandi_benar(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        Livewire::test(KeamananAkun::class)
            ->set('kataSandi', self::SANDI)
            ->call('keluarkanPerangkatLain')
            ->assertHasNoErrors()
            // Pemberitahuannya lewat toast bersama, bukan kotak .alert yang
            // ikut tergambar ulang setiap komponen menyegarkan dirinya.
            ->assertDispatched('toast', jenis: 'berhasil');

        $this->assertDatabaseHas('aktivitas_masuk', [
            'user_id' => $pengguna->getKey(),
            'alasan' => 'keluar dari perangkat lain',
        ]);

        // Sesi ini harus tetap hidup.
        $this->assertAuthenticatedAs($pengguna);
    }

    public function test_kata_sandi_salah_ditolak(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        RateLimiter::clear('keluarkan-perangkat|' . $pengguna->getKey());

        Livewire::test(KeamananAkun::class)
            ->set('kataSandi', 'SandiKeliru2026')
            ->call('keluarkanPerangkatLain')
            ->assertHasErrors('kataSandi');

        $this->assertDatabaseMissing('aktivitas_masuk', [
            'user_id' => $pengguna->getKey(),
            'alasan' => 'keluar dari perangkat lain',
        ]);
    }

    public function test_tab_keamanan_tampil_di_halaman_profil(): void
    {
        $pengguna = $this->buatPengguna();

        $this->actingAs($pengguna)
            ->get(route('account.profil.show', $pengguna->uuid))
            ->assertOk()
            ->assertSee('Keamanan')
            ->assertSeeLivewire(KeamananAkun::class);
    }
}
