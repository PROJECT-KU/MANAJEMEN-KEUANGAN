<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\KategoriLayanan;
use App\PendaftaranPeserta;
use App\PendaftaranScopusCamp;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Daftar peserta yang panjang tidak memanjangkan kartunya tanpa batas.
 *
 * Rombongan di layanan ini bisa belasan orang — terukur satu pendaftaran
 * berisi 15 — dan tiap orang satu kartu. Lima belas di antaranya mendorong
 * borang pengisiannya keluar dari pandangan, padahal borang itu yang justru
 * dibuka panitia di tab ini. Terukur: panel 1.313px, dan 764px sesudah
 * ditahan.
 */
class DaftarPesertaPanjangDitahanTest extends TestCase
{
    use DatabaseTransactions;

    private const BATAS = 6;

    #[Test]
    public function peserta_di_luar_batas_ditahan_dan_jumlahnya_disebut(): void
    {
        [$orang, $baris] = $this->rombongan(15);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $baris->getKey()]))
            ->assertOk()->getContent();

        $blok = $this->blok($isi);

        $this->assertSame(15, substr_count($blok, '<li'), 'prasyarat: kelima belasnya tergambar');

        $this->assertSame(15 - self::BATAS, substr_count($blok, 'rin-peserta-lama'),
            'Jumlah peserta yang ditahan tidak sesuai batasnya.');

        // Angkanya DISEBUT: "Tampilkan lainnya" tidak memberi tahu apakah yang
        // tersembunyi dua atau dua ratus.
        $this->assertStringContainsString('Tampilkan 9 peserta lainnya', $isi);
    }

    #[Test]
    public function yang_ditahan_adalah_yang_BELAKANG(): void
    {
        /*
         * Nomor urut daftar ini yang dipakai menerbitkan sertifikat, jadi ia
         * dibaca dari atas — dan pendaftarnya sendiri selalu nomor satu.
         * Menahan yang depan berarti panitia membuka tab ini dan tidak
         * menemukan orang yang ia cari di tempat yang seharusnya.
         */
        [$orang, $baris] = $this->rombongan(10);

        $blok = $this->blok($this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $baris->getKey()]))
            ->assertOk()->getContent());

        preg_match_all('/<li([^>]*)>(.*?)<\/li>/s', $blok, $cocok, PREG_SET_ORDER);

        $this->assertCount(10, $cocok);

        // Pendaftarnya (butir pertama) tidak pernah ditahan.
        $this->assertStringNotContainsString('rin-peserta-lama', $cocok[0][1]);
        $this->assertStringContainsString('pendaftar', $cocok[0][2]);

        // Tepat di batasnya: butir ke-6 (indeks 5) tampak, ke-7 ditahan.
        $this->assertStringNotContainsString('rin-peserta-lama', $cocok[5][1]);
        $this->assertStringContainsString('rin-peserta-lama', $cocok[6][1]);
        $this->assertStringContainsString('rin-peserta-lama', $cocok[9][1]);
    }

    #[Test]
    public function rombongan_kecil_tidak_diberi_tombol(): void
    {
        [$orang, $baris] = $this->rombongan(self::BATAS);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $baris->getKey()]))
            ->assertOk()->getContent();

        $this->assertStringNotContainsString('rin-peserta-lama', $this->blok($isi));

        $this->assertSame(0, preg_match('/<button[^>]*data-mis-ringkas="\.rin-peserta"/', $isi),
            'Tombol pembuka tergambar padahal tidak ada yang ditahan.');
    }

    #[Test]
    public function aturan_sembunyinya_cukup_berbobot(): void
    {
        /*
         * `.rin-peserta li` menyetel `display: flex`. Kelas tunggal berbobot
         * (0,1,0) dan KALAH terhadapnya — barisnya tetap tergambar tanpa ada
         * galat apa pun yang menunjukkannya. Pelajaran yang sama sudah dibayar
         * di daftar jejak perubahan.
         */
        $gaya = file_get_contents(
            resource_path('views/account/pendaftaran_layanan/rincian.blade.php')
        );

        $this->assertMatchesRegularExpression(
            '/\.rin-peserta\s+li\.rin-peserta-lama\s*\{[^}]*display:\s*none/s', $gaya);

        $this->assertMatchesRegularExpression(
            '/\.rin-peserta\.rin-peserta-penuh\s+li\.rin-peserta-lama\s*\{[^}]*display:\s*flex/s', $gaya);
    }

    #[Test]
    public function kedua_daftar_memakai_skrip_yang_sama(): void
    {
        /*
         * Jejak perubahan dan daftar peserta punya gejala yang sama persis,
         * dan versi pertama tuasnya ditulis khusus untuk jejak. Dua salinan
         * perilaku yang sama pasti berbeda perlahan — jadi skripnya umum,
         * dan tiap daftar menyebut sendiri pemilih serta kelas penuhnya.
         */
        $sumber = file_get_contents(
            resource_path('views/account/pendaftaran_layanan/rincian.blade.php')
        );

        $this->assertSame(1, substr_count($sumber, "querySelectorAll('[data-mis-ringkas]')"),
            'Tuas daftar ringkas tidak lagi satu skrip untuk semua daftar.');

        foreach (['.rin-jejak', '.rin-peserta'] as $pemilih) {
            $this->assertStringContainsString('data-mis-ringkas="' . $pemilih . '"', $sumber,
                "Daftar {$pemilih} tidak memakai tuas bersama itu.");
        }
    }

    // ------------------------------------------------------------- pembantu

    private function blok(string $isi): string
    {
        $ada = preg_match('/<ol class="rin-peserta">(?<dalam>.*?)<\/ol>/s', $isi, $cocok);
        $this->assertSame(1, $ada, 'Daftar peserta tidak tergambar.');

        return $cocok['dalam'];
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp} */
    private function rombongan(int $jumlah): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $u = User::create([
            'full_name' => 'Rina Panitia', 'username' => 'psrt_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'), 'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        $t = Str::random(8);

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Uji ' . $t,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ]);

        $baris = PendaftaranScopusCamp::create([
            'id_transaksi' => 'T-' . $t, 'kategori_id' => $angkatan->id,
            'nama' => 'Pemesan ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0001', 'jumlah_pendaftar' => (string) $jumlah,
            'total_pembayaran' => '5500000', 'status' => 'diproses',
        ]);

        // Pemesannya sendiri sudah terhitung satu, jadi sisanya jumlah - 1.
        foreach (range(1, $jumlah - 1) as $ke) {
            PendaftaranPeserta::create([
                'layanan' => 'scopus_camp', 'pendaftaran_id' => (string) $baris->getKey(),
                'urutan' => $ke, 'nama' => 'Peserta ke-' . ($ke + 1),
                'telp' => '62811000' . str_pad((string) $ke, 4, '0', STR_PAD_LEFT),
            ]);
        }

        return [$u, $baris];
    }
}
