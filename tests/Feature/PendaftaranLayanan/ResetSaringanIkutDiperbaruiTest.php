<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\KategoriLayanan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Penanda saringan ikut diperbarui saat menyaring TANPA memuat ulang.
 *
 * Tombol Reset dirender peladen, sementara saringannya dipasang lewat
 * penyaring hidup: ia menukar daftar hasil dan wilayah yang disebut
 * data-mis-saring-juga, lalu mengganti alamatnya lewat history.replaceState.
 * Borangnya sendiri tidak pernah ikut ditukar.
 *
 * Akibatnya: halaman dibuka tanpa saringan (Reset tidak dirender), lalu
 * panitia memilih layanan, keadaan, dan angkatan — alamatnya berubah,
 * daftarnya menyusut, dan Reset TIDAK PERNAH muncul. Satu-satunya jalan
 * membersihkan saringan jadi menghapus alamatnya dengan tangan.
 */
class ResetSaringanIkutDiperbaruiTest extends TestCase
{
    use DatabaseTransactions;

    private function sebagaiPanitia(): User
    {
        $u = User::create([
            'full_name' => 'Rina Panitia', 'username' => 'reset_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'), 'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        return $u;
    }

    private function halaman(array $saringan = []): string
    {
        return $this->actingAs($this->sebagaiPanitia())
            ->get(route('account.pendaftaran-layanan.index', $saringan))
            ->assertOk()->getContent();
    }

    #[Test]
    public function pembungkusnya_ada_walau_belum_ada_saringan(): void
    {
        /*
         * Inti perbaikannya. Penyaring hidup memperbarui wilayah lewat
         * innerHTML, jadi wilayahnya HARUS sudah ada sejak halaman dibuka —
         * unsur yang baru lahir saat saringan terpasang tidak akan pernah
         * ketemu untuk ditukar.
         */
        $isi = $this->halaman();

        foreach (['pdl-reset', 'pdl-aktif', 'pdl-lain-jumlah'] as $id) {
            $this->assertStringContainsString('id="' . $id . '"', $isi,
                "Pembungkus {$id} tidak tergambar saat belum ada saringan, jadi "
                . 'penyaring hidup tidak punya apa pun untuk diperbarui.');
        }

        // Dan memang KOSONG: tidak ada Reset saat tidak ada yang perlu direset.
        $this->assertSame('', $this->isiPembungkus($isi, 'pdl-reset'));
        $this->assertSame('', $this->isiPembungkus($isi, 'pdl-aktif'));
    }

    #[Test]
    public function reset_tergambar_begitu_ada_saringan(): void
    {
        $angkatan = KategoriLayanan::where('layanan', 'scopus_camp')->value('id');

        $isi = $this->halaman([
            'layanan' => 'scopus_camp', 'keadaan' => 'jadwal', 'angkatan' => $angkatan,
        ]);

        $dalam = $this->isiPembungkus($isi, 'pdl-reset');

        $this->assertStringContainsString('Reset', $dalam);

        // Tautannya ke alamat TANPA saringan — itu yang membuatnya mereset.
        $this->assertStringContainsString(
            'href="' . route('account.pendaftaran-layanan.index') . '"', $dalam,
            'Tombol Reset tidak menunjuk alamat tanpa saringan.'
        );

        $this->assertStringContainsString('aktif', $this->isiPembungkus($isi, 'pdl-aktif'));
        $this->assertStringContainsString('1', $this->isiPembungkus($isi, 'pdl-lain-jumlah'),
            'Angka saringan lipat tidak tergambar; saringan tersembunyi jadi tak terhitung.');
    }

    #[Test]
    public function ketiganya_didaftarkan_ke_penyaring_hidup(): void
    {
        /*
         * Tanpa ini pembungkusnya memang ada, tetapi tidak pernah ditukar —
         * dan gejalanya persis sama dengan sebelum diperbaiki.
         */
        $isi = $this->halaman();

        $ada = preg_match('/data-mis-saring-juga="(?<isi>[^"]*)"/', $isi, $cocok);

        $this->assertSame(1, $ada, 'Daftar wilayah yang ikut diperbarui hilang.');

        $daftar = array_map('trim', explode(',', $cocok['isi']));

        foreach (['pdl-ringkas', 'pdl-aksi', 'pdl-reset', 'pdl-aktif', 'pdl-lain-jumlah'] as $id) {
            $this->assertContains($id, $daftar,
                "Wilayah {$id} tidak ikut diperbarui saat menyaring tanpa memuat ulang.");
        }
    }

    #[Test]
    public function pembungkus_kosong_tidak_menyisakan_petak(): void
    {
        /*
         * `display: contents` membuat pembungkusnya tidak jadi item lentur
         * sendiri. Tanpa itu, deret saringan menyisakan satu petak kosong di
         * tempat Reset akan muncul — persis keluhan yang memulai pekerjaan
         * ini, cuma pindah tempat.
         */
        $gaya = (string) preg_replace('#/\*.*?\*/#s',
            '', file_get_contents(resource_path('views/account/pendaftaran_layanan/index.blade.php')));

        $ada = preg_match('/\.pdl-reset\s*\{(?<isi>[^}]*)\}/', $gaya, $cocok);

        $this->assertSame(1, $ada, 'Aturan pembungkus Reset hilang.');
        $this->assertMatchesRegularExpression('/display:\s*contents/', $cocok['isi']);
    }

    /** Isi satu pembungkus, tanpa spasi di ujungnya. */
    private function isiPembungkus(string $isi, string $id): string
    {
        $ada = preg_match('/<span id="' . preg_quote($id, '/') . '"[^>]*>(?<dalam>.*?)<\/span>\s*$/ms',
            $isi, $cocok);

        if ($ada !== 1) {
            // Pembungkus bersarang: diambil sampai penutup yang seimbang.
            $awal = strpos($isi, '<span id="' . $id . '"');
            $this->assertNotFalse($awal, "Pembungkus {$id} tidak ketemu.");

            $potong = substr($isi, $awal);
            $buka = 0;
            $panjang = 0;

            foreach (preg_split('/(<span\b|<\/span>)/i', $potong, -1,
                PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE) as [$bagian, $posisi]) {
                if (stripos($bagian, '<span') === 0) {
                    $buka++;
                } elseif (stripos($bagian, '</span>') === 0) {
                    $buka--;

                    if ($buka === 0) {
                        $panjang = $posisi;
                        break;
                    }
                }
            }

            $dalam = substr($potong, 0, $panjang);
            $dalam = substr($dalam, strpos($dalam, '>') + 1);

            return trim($dalam);
        }

        return trim($cocok['dalam']);
    }
}
