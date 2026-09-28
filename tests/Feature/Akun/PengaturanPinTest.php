<?php

namespace Tests\Feature\Akun;

use App\Livewire\Akun\PengaturanPin;
use App\Mail\PemberitahuanPinMail;
use App\PerangkatPin;
use App\Support\IngatanMasuk;
use App\Support\PenandaPerangkat;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Livewire\Features\SupportTesting\Testable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PengaturanPinTest extends TestCase
{
    use DatabaseTransactions;

    private const SANDI = 'RahasiaUji2026';

    /**
     * Kue yang membuat peramban uji dianggap perangkat PIN terdaftar.
     *
     * Sejak izin PIN diperiksa di peladen, kue ingatan saja tidak cukup:
     * barisnya di perangkat_pin harus ada juga. Itu memang maksudnya —
     * perangkat yang izinnya dicabut tidak boleh lagi mengaku terdaftar.
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

    private function buatPengguna(array $ubah = []): User
    {
        $pengguna = User::create(array_merge([
            'full_name' => 'Uji Atur PIN',
            'username' => 'uji_atur_pin_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make(self::SANDI),
            'level' => 'karyawan',
        ], $ubah));

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    public function test_tab_pin_tampil_di_halaman_profil(): void
    {
        $pengguna = $this->buatPengguna();

        $this->actingAs($pengguna)
            ->get(route('account.profil.show', $pengguna->uuid))
            ->assertOk()
            ->assertSee('PIN masuk')
            ->assertSeeLivewire(PengaturanPin::class)
            // Livewire harus ikut memuat asetnya di layout admin, kalau tidak
            // tombol di dalam tab tidak akan berfungsi.
            ->assertSee('livewire.js', false);
    }

    public function test_pin_bisa_diaktifkan_dengan_kata_sandi_yang_benar(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', '482913')
            ->set('pinKonfirmasi', '482913')
            ->call('simpan')
            ->assertHasNoErrors();

        $pengguna->refresh();

        $this->assertTrue($pengguna->pinAktif());
        $this->assertTrue($pengguna->pinCocok('482913'));
        $this->assertNotNull($pengguna->pin_diubah_pada);

        Mail::assertSent(PemberitahuanPinMail::class, fn ($surat) => $surat->aksi === 'diaktifkan');
    }

    public function test_pin_disimpan_teracak_bukan_apa_adanya(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', '482913')
            ->set('pinKonfirmasi', '482913')
            ->call('simpan');

        $tersimpan = (string) $pengguna->refresh()->pin;

        $this->assertNotSame('482913', $tersimpan);
        $this->assertStringNotContainsString('482913', $tersimpan);
        $this->assertTrue(Hash::check('482913', $tersimpan));
    }

    public function test_mengaktifkan_pin_membuat_perangkat_ini_siap_pakai_pin(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', '482913')
            ->set('pinKonfirmasi', '482913')
            ->call('simpan');

        $kue = Cookie::queued(IngatanMasuk::NAMA);

        $this->assertNotNull($kue, 'Perangkat harus mengingat akun supaya PIN bisa dipakai tanpa username.');

        $isi = json_decode((string) $kue->getValue(), true);

        $this->assertSame('pin', $isi['mode']);
        $this->assertSame($pengguna->username, $isi['identitas']);
        $this->assertSame($pengguna->getKey(), $isi['uid']);
        // Mengaktifkan PIN tidak ikut menyalakan pengisian otomatis isian.
        $this->assertFalse($isi['ingat']);
    }

    public function test_kata_sandi_salah_menolak_perubahan_pin(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        RateLimiter::clear('atur-pin|' . $pengguna->getKey());

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', 'SandiKeliru2026')
            ->set('pin', '482913')
            ->set('pinKonfirmasi', '482913')
            ->call('simpan')
            ->assertHasErrors('kataSandi');

        $this->assertFalse($pengguna->refresh()->pinAktif());
    }

    #[DataProvider('pinLemah')]
    public function test_pin_yang_mudah_ditebak_ditolak(string $pin): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', $pin)
            ->set('pinKonfirmasi', $pin)
            ->call('simpan')
            ->assertHasErrors('pin');

        $this->assertFalse($pengguna->refresh()->pinAktif());
    }

    public static function pinLemah(): array
    {
        return [
            'angka sama' => ['111111'],
            'berurutan naik' => ['123456'],
            'berurutan turun' => ['654321'],
            'pola berulang' => ['121212'],
            'tiga angka berulang' => ['123123'],
            'terlalu umum' => ['159753'],
        ];
    }

    public function test_pin_tidak_boleh_sama_dengan_tanggal_lahir(): void
    {
        $pengguna = $this->buatPengguna(['tanggal_lahir' => '1998-08-17']);
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', '170898')
            ->set('pinKonfirmasi', '170898')
            ->call('simpan')
            ->assertHasErrors('pin');
    }

    public function test_ulangan_pin_harus_sama(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', '482913')
            ->set('pinKonfirmasi', '482914')
            ->call('simpan')
            ->assertHasErrors('pinKonfirmasi');

        $this->assertFalse($pengguna->refresh()->pinAktif());
    }

    public function test_pin_hanya_boleh_angka_sepanjang_enam(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', '48291')
            ->set('pinKonfirmasi', '48291')
            ->call('simpan')
            ->assertHasErrors(['pin' => 'digits']);
    }

    public function test_pin_bisa_diubah(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');
        $this->actingAs($pengguna);

        // Kue perangkat dikirimkan supaya peramban ini dianggap sudah memakai
        // PIN: sejak pengaman dipasang, mengganti PIN hanya boleh dari
        // perangkat yang terdaftar — lihat
        // test_perangkat_asing_tidak_bisa_mengganti_pin_dengan_angka_lain.
        Testable::create(PengaturanPin::class, [], [], $this->kuePerangkatTerdaftar($pengguna))
            ->set('kataSandi', self::SANDI)
            ->set('pin', '739154')
            ->set('pinKonfirmasi', '739154')
            ->call('simpan')
            ->assertHasNoErrors();

        $pengguna->refresh();

        $this->assertTrue($pengguna->pinCocok('739154'));
        $this->assertFalse($pengguna->pinCocok('482913'));

        Mail::assertSent(PemberitahuanPinMail::class, fn ($surat) => $surat->aksi === 'diubah');
    }

    public function test_pin_bisa_dinonaktifkan(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->call('nonaktifkan')
            ->assertHasNoErrors()
            // Pemberitahuannya lewat toast bersama, bukan kotak .alert.
            ->assertDispatched('toast', jenis: 'berhasil');

        $pengguna->refresh();

        $this->assertFalse($pengguna->pinAktif());
        $this->assertNull($pengguna->pin);

        Mail::assertSent(PemberitahuanPinMail::class, fn ($surat) => $surat->aksi === 'dinonaktifkan');
    }

    public function test_nonaktifkan_butuh_kata_sandi_yang_benar(): void
    {
        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');
        $this->actingAs($pengguna);

        RateLimiter::clear('atur-pin|' . $pengguna->getKey());

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', 'SandiKeliru2026')
            ->call('nonaktifkan')
            ->assertHasErrors('kataSandi');

        $this->assertTrue($pengguna->refresh()->pinAktif());
    }

    public function test_kata_sandi_salah_berulang_ditunda(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        RateLimiter::clear('atur-pin|' . $pengguna->getKey());

        $uji = Livewire::test(PengaturanPin::class);

        for ($i = 0; $i < 5; $i++) {
            $uji->set('kataSandi', 'SandiKeliru2026')
                ->set('pin', '482913')
                ->set('pinKonfirmasi', '482913')
                ->call('simpan')
                ->assertHasErrors('kataSandi');
        }

        $uji->set('kataSandi', self::SANDI)
            ->set('pin', '482913')
            ->set('pinKonfirmasi', '482913')
            ->call('simpan')
            ->assertHasErrors('kataSandi');

        $this->assertFalse($pengguna->refresh()->pinAktif());
    }

    public function test_pin_dari_komputer_bisa_didaftarkan_di_perangkat_lain(): void
    {
        // Ini jawaban untuk "PIN dibuat di komputer, mau dipakai di HP":
        // masuk sekali dengan kata sandi di perangkat baru, lalu masukkan PIN
        // yang sudah ada di sini.
        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');
        $this->actingAs($pengguna);

        $uji = Livewire::test(PengaturanPin::class);

        $this->assertFalse($uji->instance()->perangkatSiap(), 'Perangkat baru belum boleh langsung memakai PIN.');

        $uji->set('pinPerangkat', '482913')
            ->call('aktifkanDiPerangkat')
            ->assertHasNoErrors();

        $kue = Cookie::queued(IngatanMasuk::NAMA);

        $this->assertNotNull($kue);

        $isi = json_decode((string) $kue->getValue(), true);

        $this->assertSame('pin', $isi['mode']);
        $this->assertSame($pengguna->getKey(), $isi['uid']);
    }

    /**
     * Inti pengamannya: satu akun hanya punya satu PIN. Mengetik angka baru
     * di HP bukan "mendaftarkan HP", melainkan menimpa PIN yang dipakai
     * laptop juga — dan pemilik laptop tidak diberi tahu sampai ia terkunci.
     */
    public function test_perangkat_asing_tidak_bisa_mengganti_pin_dengan_angka_lain(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('070698');
        $this->actingAs($pengguna);

        RateLimiter::clear('atur-pin|' . $pengguna->getKey());
        RateLimiter::clear('pin-perangkat|' . $pengguna->getKey());

        $uji = Livewire::test(PengaturanPin::class);

        $this->assertFalse($uji->instance()->perangkatSiap(), 'Perangkat uji harus dianggap belum terdaftar.');
        $this->assertFalse($uji->instance()->bolehGantiPin());

        $uji->set('kataSandi', self::SANDI)
            ->set('pin', '980607')
            ->set('pinKonfirmasi', '980607')
            ->call('simpan')
            ->assertHasErrors('pin')
            ->assertDispatched('toast', jenis: 'gagal');

        // PIN lama harus utuh: inilah yang dulu diam-diam tertimpa.
        $this->assertTrue($pengguna->refresh()->pinCocok('070698'));
        $this->assertFalse($pengguna->pinCocok('980607'));

        // Perangkat asing juga tidak boleh ikut terdaftar dari percobaan gagal.
        $this->assertNull(Cookie::queued(IngatanMasuk::NAMA));

        Mail::assertNothingSent();
    }

    /**
     * Kalau angkanya memang PIN yang sekarang, yang diminta orang ini
     * sebenarnya "pakai PIN saya di perangkat ini" — dikerjakan, tanpa
     * menyentuh PIN-nya.
     */
    public function test_perangkat_asing_yang_mengetik_pin_benar_malah_didaftarkan(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('070698');
        $diubahSemula = $pengguna->refresh()->pin;
        $this->actingAs($pengguna);

        RateLimiter::clear('atur-pin|' . $pengguna->getKey());
        RateLimiter::clear('pin-perangkat|' . $pengguna->getKey());

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', '070698')
            ->set('pinKonfirmasi', '070698')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertDispatched('toast', jenis: 'berhasil');

        $kue = Cookie::queued(IngatanMasuk::NAMA);

        $this->assertNotNull($kue, 'Perangkat seharusnya ikut terdaftar.');
        $this->assertSame('pin', json_decode((string) $kue->getValue(), true)['mode']);

        // PIN tidak diganti, jadi nilainya tidak ikut diacak ulang.
        $this->assertSame($diubahSemula, $pengguna->refresh()->pin);

        // Tidak ada yang berubah, jadi tidak ada kabar perubahan PIN.
        Mail::assertNothingSent();
    }

    /** Dari perangkat yang sudah memakai PIN, menggantinya tetap boleh. */
    public function test_perangkat_terdaftar_tetap_bisa_mengganti_pin(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('070698');
        $this->actingAs($pengguna);

        RateLimiter::clear('atur-pin|' . $pengguna->getKey());

        $uji = Testable::create(PengaturanPin::class, [], [], $this->kuePerangkatTerdaftar($pengguna));

        $this->assertTrue($uji->instance()->bolehGantiPin());

        $uji->set('kataSandi', self::SANDI)
            ->set('pin', '980607')
            ->set('pinKonfirmasi', '980607')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertTrue($pengguna->refresh()->pinCocok('980607'));
    }

    /** Akun yang memang belum punya PIN tidak ikut terkunci pengaman ini. */
    public function test_akun_tanpa_pin_tetap_bisa_membuat_pin_di_perangkat_mana_pun(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        RateLimiter::clear('atur-pin|' . $pengguna->getKey());

        $uji = Livewire::test(PengaturanPin::class);

        $this->assertTrue($uji->instance()->bolehGantiPin());

        $uji->set('kataSandi', self::SANDI)
            ->set('pin', '980607')
            ->set('pinKonfirmasi', '980607')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertTrue($pengguna->refresh()->pinCocok('980607'));
    }

    /**
     * Formulir ganti PIN memakai kantong jatah yang sama dengan kotak
     * pendaftaran perangkat, jadi ia tidak bisa dipakai menebak PIN setelah
     * jatah di kotak itu habis.
     */
    public function test_tebakan_lewat_formulir_ikut_menghabiskan_jatah_pendaftaran(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('070698');
        $this->actingAs($pengguna);

        RateLimiter::clear('atur-pin|' . $pengguna->getKey());
        RateLimiter::clear('pin-perangkat|' . $pengguna->getKey());

        $uji = Livewire::test(PengaturanPin::class);

        for ($i = 0; $i < 5; $i++) {
            $uji->set('kataSandi', self::SANDI)
                ->set('pin', '111333')
                ->set('pinKonfirmasi', '111333')
                ->call('simpan')
                ->assertHasErrors('pin');
        }

        // Jatahnya habis, jadi kotak pendaftaran ikut tertutup — walaupun
        // sekarang PIN-nya diketik dengan benar.
        $uji->set('pinPerangkat', '070698')
            ->call('aktifkanDiPerangkat')
            ->assertHasErrors('pinPerangkat');

        $this->assertNull(Cookie::queued(IngatanMasuk::NAMA));
    }

    public function test_daftar_perangkat_menolak_pin_yang_salah(): void
    {
        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');
        $this->actingAs($pengguna);

        RateLimiter::clear('pin-perangkat|' . $pengguna->getKey());

        Livewire::test(PengaturanPin::class)
            ->set('pinPerangkat', '111333')
            ->call('aktifkanDiPerangkat')
            ->assertHasErrors('pinPerangkat');

        $this->assertNull(Cookie::queued(IngatanMasuk::NAMA));
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

        Testable::create(PengaturanPin::class, [], [], $this->kuePerangkatTerdaftar($pengguna))
            ->call('lupakanPerangkatLain', $lain->id)
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

        Testable::create(PengaturanPin::class, [], [], $kue)
            ->call('lupakanPerangkatLain', $ini->id)
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

        Testable::create(PengaturanPin::class, [], [], $this->kuePerangkatTerdaftar($pengguna))
            ->call('lupakanPerangkatLain', $milikOrangLain->id)
            ->assertDispatched('toast', jenis: 'gagal');

        $this->assertNotNull(PerangkatPin::find($milikOrangLain->id));
    }

    /**
     * PIN mati berarti tidak ada perangkat yang boleh memakainya. Kalau
     * catatannya tertinggal, perangkat itu langsung terdaftar kembali begitu
     * PIN diaktifkan lagi nanti.
     */
    public function test_mematikan_pin_mencabut_izin_semua_perangkat(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');
        $this->actingAs($pengguna);

        $this->perangkatLain($pengguna);
        $kue = $this->kuePerangkatTerdaftar($pengguna);

        $this->assertSame(2, PerangkatPin::where('user_id', $pengguna->getKey())->count());

        Testable::create(PengaturanPin::class, [], [], $kue)
            ->set('kataSandi', self::SANDI)
            ->call('nonaktifkan')
            ->assertHasNoErrors();

        $this->assertSame(0, PerangkatPin::where('user_id', $pengguna->getKey())->count());
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

        $daftar = Testable::create(PengaturanPin::class, [], [], $kue)->instance()->daftarPerangkatPin();

        $this->assertTrue($daftar->first()->ini, 'Perangkat yang sedang dipakai harus di baris pertama.');
        $this->assertCount(2, $daftar);
    }

    public function test_perangkat_bisa_dilupakan_tanpa_mematikan_pin(): void
    {
        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');
        $this->actingAs($pengguna);

        // Kue dikirimkan seperti yang dilakukan peramban pada kunjungan
        // berikutnya; harness Livewire tidak mengembalikan kue yang baru
        // diantre ke permintaan setelahnya.
        Testable::create(PengaturanPin::class, [], [], [
            IngatanMasuk::NAMA => json_encode([
                'identitas' => $pengguna->username,
                'mode' => 'pin',
                'ingat' => false,
                'uid' => $pengguna->getKey(),
            ]),
        ])->call('lupakanPerangkat');

        $kue = Cookie::queued(IngatanMasuk::NAMA);

        $this->assertNotNull($kue);
        $this->assertNull($kue->getValue(), 'Ingatan perangkat harus dihapus.');
        $this->assertTrue($pengguna->refresh()->pinAktif(), 'PIN akun harus tetap aktif.');
    }
}
