<?php

namespace Tests\Feature\ClinikScopus;

use App\ClinikScopusBiayaPersesi;
use App\Layanan;
use App\PendaftaranScopusKafe;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Sesi Scopus Kafe beserta jamnya, disetel di layar Tarif Layanan.
 *
 * Jamnya dulu ditulis di kode, jadi mengubahnya menuntut deploy — padahal
 * yang tahu jamnya berubah adalah panitia, bukan yang memegang kodenya.
 *
 * Yang dijaga di sini terutama hal-hal yang TIDAK terlihat saat rusak: tarif
 * baru yang diam-diam menghapus setelan, teks yang tersimpan sebagai kosong
 * tanpa kabar, dan jam bawaan yang hilang begitu kolomnya ada.
 */
class SesiTarifTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Layanan::lupakanKatalog();
        Pendaftaran::lupakanSesi();
        ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();
    }

    private function admin(): User
    {
        $u = User::create([
            'full_name' => 'Uji Sesi Tarif',
            'username' => 'uji_sesi_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('rahasia-panjang-sekali'),
            'level' => 'user',
        ]);

        $u->forceFill([
            'status' => 'active',
            'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR,
        ])->save();

        return $u;
    }

    /** Menyetel tarif Scopus Kafe offline beserta sesinya. */
    private function setel(User $admin, ?string $sesi, array $tambahan = [])
    {
        return $this->actingAs($admin)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), array_merge([
            'layanan' => 'scopus_kafe',
            'varian' => 'offline',
            'biaya_persesi' => '1.100.000',
            'sesi' => $sesi,
        ], $tambahan));
    }

    #[Test]
    public function jam_yang_disetel_di_tarif_menimpa_jam_bawaan(): void
    {
        $admin = $this->admin();

        $this->setel($admin, "Sesi Pagi, 07.30 - 11.30\nSesi Siang, 12.30 - 16.30")
            ->assertSessionHasNoErrors();

        Pendaftaran::lupakanSesi();
        $daftar = Pendaftaran::sesiPilihan('scopus_kafe', 'offline');

        $this->assertSame(
            [['nilai' => 'sesi pagi', 'nama' => 'Sesi Pagi', 'mulai' => '07:30', 'selesai' => '11:30'],
                ['nilai' => 'sesi siang', 'nama' => 'Sesi Siang', 'mulai' => '12:30', 'selesai' => '16:30']],
            $daftar
        );

        /*
         * Dan jam barunya benar-benar sampai ke baris pendaftarannya. Daftar
         * yang berubah di satu tempat saja berarti panitia memilih "Sesi
         * Pagi" lalu barisnya tetap tersimpan berjam lama.
         */
        $this->actingAs($admin)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_kafe',
            'nama' => 'Peserta Jam Baru',
            'email' => 'jambaru' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0070',
            'total' => '1.100.000',
            'varian' => 'offline',
            'sesi' => 'sesi siang',
        ])->assertRedirect();

        $b = PendaftaranScopusKafe::where('nama', 'Peserta Jam Baru')->first();

        $this->assertNotNull($b);
        $this->assertSame('sesi siang', $b->sesi);
        $this->assertSame('12:30:00', (string) $b->waktu_mulai);
        $this->assertSame('16:30:00', (string) $b->waktu_selesai);

        // Varian LAIN tidak ikut berubah: jam online dan offline memang
        // berbeda, dan menyetel satu tidak boleh menyeret yang lain.
        $this->assertSame(
            [['nilai' => 'sesi 1', 'nama' => 'Sesi 1', 'mulai' => '08:00', 'selesai' => '13:00']],
            Pendaftaran::sesiPilihan('scopus_kafe', 'online')
        );
    }

    #[Test]
    public function tarif_tanpa_setelan_sesi_tetap_punya_jam_bawaan(): void
    {
        /*
         * Kosong BUKAN berarti tidak ada sesi. Tanpa jaring ini, tarif baru
         * yang dibuat saat harganya naik akan menghapus jadwal sesi tanpa ada
         * yang meminta — dan borang pendaftarannya berhenti menawarkan sesi
         * sama sekali.
         */
        $admin = $this->admin();

        $this->setel($admin, null)->assertSessionHasNoErrors();

        Pendaftaran::lupakanSesi();

        $this->assertSame(
            Pendaftaran::SESI['scopus_kafe']['offline'],
            Pendaftaran::sesiPilihan('scopus_kafe', 'offline')
        );
    }

    #[Test]
    public function sesi_yang_tidak_terbaca_ditolak_bukan_disimpan_kosong(): void
    {
        /*
         * Yang dijaga bukan penolakannya, melainkan bahwa ia BERSUARA.
         * Disimpan diam-diam sebagai NULL, panitia menyangka jadwalnya sudah
         * berubah padahal yang berlaku masih yang lama — dan itu baru
         * ketahuan saat pesertanya datang di jam yang salah.
         */
        $admin = $this->admin();

        $this->setel($admin, "Sesi Pagi\nSesi Siang")
            ->assertSessionHasErrors('sesi');

        Pendaftaran::lupakanSesi();

        $this->assertSame(
            Pendaftaran::SESI['scopus_kafe']['offline'],
            Pendaftaran::sesiPilihan('scopus_kafe', 'offline'),
            'Yang berlaku harus tetap jam sebelumnya, bukan kosong.'
        );
    }

    #[Test]
    public function tarif_baru_tidak_menghapus_diskon_rombongan(): void
    {
        /*
         * Cacat lama, ketemu saat menambahkan sesi ke kedua cabang simpan:
         * kedua potongan rombongan hanya ikut di cabang "perbaiki", jadi
         * MENAIKKAN HARGA — yang selalu lewat cabang tarif baru — menghapus
         * diskon rombongan yang sudah disetel, diam-diam. Borangnya memang
         * mengirim angkanya; yang hilang di perjalanan.
         */
        $admin = $this->admin();

        $this->setel($admin, null, [
            'biaya_persesi' => '1.300.000',
            'diskon_rombongan_min' => 10,
            'diskon_rombongan_persen' => 15,
        ])->assertSessionHasNoErrors();

        $tarif = ClinikScopusBiayaPersesi::query()
            ->where('layanan', 'scopus_kafe')
            ->where('varian', 'offline')
            ->where('status', ClinikScopusBiayaPersesi::AKTIF)
            ->first();

        $this->assertNotNull($tarif);
        $this->assertSame(10, (int) $tarif->diskon_rombongan_min);
        $this->assertSame(15, (int) $tarif->diskon_rombongan_persen);
    }

    #[Test]
    public function kotak_sesi_hanya_ditawarkan_untuk_layanan_yang_punya_tempatnya(): void
    {
        /*
         * Syaratnya kolom di tabel pendaftarannya, bukan "sudah punya sesi":
         * layanan yang sesinya belum disetel justru yang paling butuh
         * kotaknya muncul. Dan layanan yang tidak punya kolomnya tidak boleh
         * ditawari sama sekali — sesi yang tersimpan di sana tidak akan
         * pernah terbaca oleh apa pun.
         */
        $this->assertSame(['scopus_kafe'], Pendaftaran::layananBersesi());

        $admin = $this->admin();

        $isi = $this->actingAs($admin)
            ->get(route('account.Clinik-Scopus-Biaya-Persesi.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="tar-f-sesi"', $isi);

        // Tertutup saat layar dibuka; yang membukanya pilihan layanannya.
        $this->assertMatchesRegularExpression('/id="tar-f-bungkus-sesi"[^>]*\shidden/', $isi);

        // Penandanya benar untuk Scopus Kafe dan salah untuk yang lain —
        // kalau terbalik, kotaknya muncul di tempat yang tidak bisa
        // menyimpannya.
        $this->assertStringContainsString('&quot;bersesi&quot;:true', $isi);
        $this->assertStringContainsString('&quot;bersesi&quot;:false', $isi);

        /*
         * Kotaknya dibuka berisi jam yang SEDANG berlaku, bukan kosong.
         * Tarif yang belum menyetel sesinya tetap punya jam — yang bawaan —
         * dan kotak kosong di sebelah kartu yang menyebut dua sesi terbaca
         * seperti data yang gagal dimuat, lalu diketik ulang dari nol.
         */
        $bawaan = ClinikScopusBiayaPersesi::sesiSebagaiTeks(
            Pendaftaran::sesiPilihan('scopus_kafe', 'offline')
        );

        $this->assertSame("Sesi 1, 08:00 - 13:00\nSesi 2, 13:00 - 18:00", $bawaan);
        $this->assertStringContainsString(str_replace("\n", '\\n', e($bawaan)), $isi);

        // Dan jam itu juga terbaca di kartunya, tanpa membuka dialog apa pun
        // — "sesi 2 jam berapa" ditanyakan belasan kali sehari.
        $this->assertStringContainsString('13.00 –', $isi);
    }
}
