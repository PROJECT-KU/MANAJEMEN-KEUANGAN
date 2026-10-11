<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\Actions\Pendaftaran\HapusPendaftaran;
use App\KategoriLayanan;
use App\PendaftaranDihapus;
use App\PendaftaranScopusCamp;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Bukti bayar yang menunggu di parkiran penghapusan tidak boleh menumpuk
 * selamanya.
 *
 * Sejak penghapusan bisa dikembalikan, buktinya dipindahkan ke subfolder
 * 'terhapus/' bukan dibuang. Tanpa pemangkas, folder yang DILAYANI WEB itu
 * menyimpan bukti transfer pendaftaran yang dihapus bertahun-tahun lalu.
 * Diperiksa langsung di peladen lokal: berkas di sana terjawab HTTP 200.
 *
 * Berkasnya sungguhan ditulis ke public/, bukan Storage::fake(): jalur yang
 * dipakai kodenya public_path(), dan cakram palsu tidak menyentuhnya sama
 * sekali — ujinya akan hijau tanpa menguji apa pun.
 */
class PangkasParkiranTest extends TestCase
{
    use DatabaseTransactions;

    /** @var list<string> dibersihkan sendiri; cakram tidak ikut di-rollback */
    private array $berkasUji = [];

    protected function tearDown(): void
    {
        foreach ($this->berkasUji as $berkas) {
            @unlink($berkas);
        }

        parent::tearDown();
    }

    #[Test]
    public function parkiran_yang_lewat_masa_simpan_dibuang(): void
    {
        [$arsip, $diparkir] = $this->terhapusDenganBukti();

        // Dituakan melewati batasnya.
        PendaftaranDihapus::where('id', $arsip->getKey())
            ->update(['created_at' => now()->subDays(120)]);

        $this->artisan('pendaftaran:pangkas-parkiran', ['--hari' => 90])
            ->assertSuccessful();

        $this->assertFileDoesNotExist($diparkir, 'Bukti lama masih menunggu di parkiran.');

        /*
         * Catatannya TETAP tinggal; yang dibuang cuma berkasnya. Arsipnya
         * yang menjawab "ke mana perginya pendaftaran itu", dan pertanyaan
         * itu tidak kedaluwarsa bersama berkas buktinya.
         */
        $this->assertNotNull(PendaftaranDihapus::find($arsip->getKey()));
        $this->assertNull($arsip->fresh()->bukti_parkir);
    }

    #[Test]
    public function parkiran_yang_masih_baru_dibiarkan(): void
    {
        [$arsip, $diparkir] = $this->terhapusDenganBukti();

        $this->artisan('pendaftaran:pangkas-parkiran', ['--hari' => 90])
            ->assertSuccessful();

        $this->assertFileExists($diparkir,
            'Bukti yang baru dihapus ikut terbuang; pemulihannya jadi tidak utuh.');
        $this->assertNotNull($arsip->fresh()->bukti_parkir);
    }

    /**
     * Berkas YATIM dibuang: ada di parkiran tetapi tidak ditunjuk catatan
     * mana pun. Terukur dua berkas menumpuk begitu hanya dari uji yang
     * dijalankan berulang.
     */
    #[Test]
    public function berkas_yatim_di_parkiran_dibuang(): void
    {
        $folder = Pendaftaran::sumber('scopus_camp')['bukti_folder'];
        $parkiran = public_path($folder . '/' . HapusPendaftaran::FOLDER_PARKIR);

        if (! is_dir($parkiran)) {
            mkdir($parkiran, 0755, true);
        }

        $yatim = $parkiran . '/uji-yatim-' . Str::random(8) . '.jpg';
        $this->berkasUji[] = $yatim;

        file_put_contents($yatim, 'yatim');

        $this->artisan('pendaftaran:pangkas-parkiran')->assertSuccessful();

        $this->assertFileDoesNotExist($yatim, 'Berkas yatim dibiarkan menumpuk.');
    }

    /** --kering hanya melapor, tidak menyentuh berkas apa pun. */
    #[Test]
    public function pilihan_kering_tidak_membuang_apa_pun(): void
    {
        [$arsip, $diparkir] = $this->terhapusDenganBukti();

        PendaftaranDihapus::where('id', $arsip->getKey())
            ->update(['created_at' => now()->subDays(120)]);

        $this->artisan('pendaftaran:pangkas-parkiran', ['--hari' => 90, '--kering' => true])
            ->assertSuccessful();

        $this->assertFileExists($diparkir, '--kering tetap membuang berkasnya.');
        $this->assertNotNull($arsip->fresh()->bukti_parkir);
    }

    /** Masa simpan di bawah satu hari ditolak. */
    #[Test]
    public function masa_simpan_nol_ditolak(): void
    {
        $this->artisan('pendaftaran:pangkas-parkiran', ['--hari' => 0])->assertFailed();
    }

    // ------------------------------------------------------------- pembantu

    /** @return array{0: PendaftaranDihapus, 1: string} */
    private function terhapusDenganBukti(): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $t = Str::random(8);

        $admin = User::create([
            'full_name' => 'Admin Uji ' . $t, 'username' => 'pkp_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $admin->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        $this->actingAs($admin);

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Uji ' . $t,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '48', 'status' => 'active',
        ]);

        $folder = Pendaftaran::sumber('scopus_camp')['bukti_folder'];
        $nama = 'uji-parkir-' . $t . '.jpg';
        $jalur = public_path($folder . '/' . $nama);
        $this->berkasUji[] = $jalur;

        if (! is_dir(dirname($jalur))) {
            mkdir(dirname($jalur), 0755, true);
        }

        file_put_contents($jalur, 'bukti-uji');

        $b = PendaftaranScopusCamp::create([
            'id_transaksi' => 'T-' . $t, 'kategori_id' => $angkatan->id,
            'nama' => 'Peserta ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0001', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => '5500000', 'status' => 'diproses',
            'gambar' => $nama,
        ]);

        $hasil = (new HapusPendaftaran)->jalankan('scopus_camp', (string) $b->getKey());
        $this->assertTrue($hasil['berhasil'], $hasil['pesan']);

        $arsip = PendaftaranDihapus::where('pendaftaran_id', (string) $b->getKey())->first();
        $this->assertNotNull($arsip);
        $this->assertNotNull($arsip->bukti_parkir);

        $diparkir = public_path($folder . '/' . HapusPendaftaran::FOLDER_PARKIR
            . '/' . $arsip->bukti_parkir);
        $this->berkasUji[] = $diparkir;

        return [$arsip, $diparkir];
    }
}
