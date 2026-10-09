<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\Actions\Pendaftaran\KirimSuratStatus;
use App\KategoriLayanan;
use App\Mail\PerubahanStatusPendaftaranMail;
use App\PendaftaranJejak;
use App\PendaftaranScopusCamp;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Riwayat surat, dan tombol kirim ulang.
 *
 * Dua kekurangan yang saling bergantung. Kegagalan surat sebelumnya hanya
 * muncul di log peladen — yang tidak dibaca panitia dan, dengan LOG_LEVEL
 * yang ketat, kadang tidak tertulis sama sekali. Dan surat yang tidak sampai
 * hanya bisa diulang dengan MEMINDAHKAN STATUSNYA BOLAK-BALIK, yang
 * meninggalkan dua jejak palsu pada riwayat yang justru dipakai menelusuri
 * perselisihan.
 */
class RiwayatSuratDanKirimUlangTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function surat_yang_terkirim_dicatat_beserta_alamatnya(): void
    {
        Mail::fake();
        [$orang, $baris] = $this->pendaftaran();

        $hasil = (new KirimSuratStatus)->jalankan('scopus_camp', $baris, $orang->full_name);

        $this->assertTrue($hasil['terkirim'], $hasil['pesan']);

        $jejak = PendaftaranJejak::milik('scopus_camp', (string) $baris->getKey())
            ->where('aksi', 'surat')->firstOrFail();

        // Status yang DISURATKAN ikut tercatat: "ada surat" saja tidak
        // memberitahu surat mana yang dikirim.
        $this->assertSame('diproses', $jejak->dari);
        $this->assertSame($baris->email, $jejak->ke);
        $this->assertStringContainsString('terkirim ke ' . $baris->email, $jejak->kalimat);
        $this->assertStringContainsString('oleh ' . $orang->full_name, $jejak->kalimat);
    }

    #[Test]
    public function alamat_kosong_dicatat_sebagai_gagal_beserta_sebabnya(): void
    {
        /*
         * Sebab kegagalan yang paling sering dan paling mudah dibetulkan
         * panitia — tetapi hanya kalau ia TAHU, dan sebelum ini ia tidak
         * pernah tahu.
         */
        Mail::fake();
        [$orang, $baris] = $this->pendaftaran();
        $baris->forceFill(['email' => ''])->save();

        $hasil = (new KirimSuratStatus)->jalankan('scopus_camp', $baris->fresh(), $orang->full_name);

        $this->assertFalse($hasil['terkirim']);
        $this->assertStringContainsString('belum punya alamat email', $hasil['pesan']);

        Mail::assertNothingSent();

        $jejak = PendaftaranJejak::milik('scopus_camp', (string) $baris->getKey())
            ->where('aksi', 'surat-gagal')->firstOrFail();

        $this->assertStringContainsString('GAGAL', $jejak->kalimat);
        $this->assertStringContainsString('alamat emailnya kosong', $jejak->kalimat,
            'Jejaknya tidak menyebut SEBABNYA; panitia akan mengulang hal yang sama.');
    }

    #[Test]
    public function peladen_surat_yang_rewel_dicatat_juga(): void
    {
        [$orang, $baris] = $this->pendaftaran();

        // Peladen surat yang melempar — kegagalan yang paling tidak terlihat.
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('Connection refused'));

        $hasil = (new KirimSuratStatus)->jalankan('scopus_camp', $baris, $orang->full_name);

        $this->assertFalse($hasil['terkirim']);

        $jejak = PendaftaranJejak::milik('scopus_camp', (string) $baris->getKey())
            ->where('aksi', 'surat-gagal')->firstOrFail();

        $this->assertStringContainsString('Connection refused', (string) $jejak->ke);
    }

    #[Test]
    public function kirim_ulang_tidak_menyentuh_statusnya(): void
    {
        /*
         * Inti tombolnya. Sebelum ini satu-satunya cara mengirim ulang adalah
         * memindahkan status bolak-balik — dan itu meninggalkan dua jejak
         * status palsu, plus menggeser kuota untuk layanan yang kuotanya ikut
         * berpindah.
         */
        Mail::fake();
        [$orang, $baris] = $this->pendaftaran();

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.kirim-ulang', ['scopus_camp', $baris->getKey()])
        )->assertRedirect()->assertSessionHas('sukses');

        $this->assertSame('diproses', $baris->fresh()->status, 'Statusnya ikut berubah.');

        $this->assertSame(0,
            PendaftaranJejak::milik('scopus_camp', (string) $baris->getKey())
                ->where('aksi', 'status')->count(),
            'Kirim ulang meninggalkan jejak perpindahan status yang tidak pernah terjadi.');

        Mail::assertSent(PerubahanStatusPendaftaranMail::class, 1);
    }

    #[Test]
    public function tombolnya_tergambar_dan_borangnya_terpisah(): void
    {
        [$orang, $baris] = $this->pendaftaran();

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $baris->getKey()]))
            ->assertOk()->getContent();

        $ada = preg_match('/<form[^>]*class="rin-kirim-ulang"[^>]*>(?<dalam>.{0,700})/s', $isi, $borang);

        $this->assertSame(1, $ada, 'Tombol kirim ulang tidak tergambar.');

        $this->assertStringContainsString(
            route('account.pendaftaran-layanan.kirim-ulang', ['scopus_camp', $baris->getKey()]),
            $borang[0]
        );

        /*
         * Borangnya TERPISAH dari borang status di atasnya: borang bersarang
         * bukan markah yang sah, dan tombol ini tidak boleh ikut mengirim
         * nilai status apa pun — yang dikerjakannya justru tidak menyentuh
         * status.
         */
        $this->assertStringNotContainsString('name="status"', $borang['dalam']);
    }

    #[Test]
    public function pelanggan_tidak_boleh_mengirim_ulang(): void
    {
        Mail::fake();
        [, $baris] = $this->pendaftaran();

        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        $this->actingAs($pelanggan)->post(
            route('account.pendaftaran-layanan.kirim-ulang', ['scopus_camp', $baris->getKey()])
        )->assertRedirect(route('account.dashboard.index'));

        Mail::assertNothingSent();
    }

    #[Test]
    public function pengirimnya_satu_tempat_untuk_semua_pemanggil(): void
    {
        /*
         * Tiga pemanggil: perpindahan status, tombol kirim ulang, dan
         * perintah pengingat terjadwal. Ketiganya harus mengirim surat yang
         * sama persis dan mencatatnya dengan cara yang sama.
         */
        $status = file_get_contents(base_path('app/Actions/Pendaftaran/UbahStatusPendaftaran.php'));

        $this->assertStringContainsString('KirimSuratStatus', $status,
            'Perpindahan status tidak lagi memakai pengirim bersama.');

        $this->assertStringNotContainsString('private function rakitSurat', $status,
            'Perakit surat kembali disalin ke UbahStatusPendaftaran; dua salinan '
            . 'pasti berbeda perlahan.');
    }

    // ------------------------------------------------------------- pembantu

    private function akun(string $peran): User
    {
        $u = User::create([
            'full_name' => 'Rina Panitia', 'username' => 'srt_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'), 'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(), 'peran' => $peran])->save();

        return $u->refresh();
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp} */
    private function pendaftaran(): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $t = Str::random(8);

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Uji ' . $t,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ]);

        $baris = PendaftaranScopusCamp::create([
            'id_transaksi' => 'T-' . $t, 'kategori_id' => $angkatan->id,
            'nama' => 'Peserta ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0001', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => '5500000', 'status' => 'diproses',
        ]);

        return [$this->akun(User::PERAN_ADMINISTRATOR), $baris];
    }
}
