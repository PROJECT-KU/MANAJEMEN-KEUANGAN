<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\KategoriLayanan;
use App\PendaftaranJejak;
use App\PendaftaranScopusCamp;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Jejak yang panjang tidak memanjangkan kartunya tanpa batas.
 *
 * Sejak tiap medan yang disunting menulis jejaknya sendiri, daftarnya tumbuh
 * jauh lebih cepat: satu tekan Simpan di tab Data pendaftaran bisa
 * meninggalkan empat baris sekaligus. Kartunya lalu memanjang ke bawah dan
 * mendorong "Catatan panitia" jauh dari pandangan.
 */
class JejakPanjangDitahanTest extends TestCase
{
    use DatabaseTransactions;

    private const BATAS = 6;

    #[Test]
    public function jejak_lama_ditahan_dan_jumlahnya_disebut(): void
    {
        [$orang, $baris] = $this->dengan(20);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $baris->getKey()]))
            ->assertOk()->getContent();

        $blok = $this->blokJejak($isi);

        $this->assertSame(20, substr_count($blok, '<li'), 'prasyarat: kedua puluhnya tergambar');

        $ditahan = substr_count($blok, 'rin-jejak-lama');

        $this->assertSame(20 - self::BATAS, $ditahan,
            'Jumlah jejak yang ditahan tidak sesuai batasnya.');

        // Jumlahnya DISEBUT dengan angka: "Tampilkan lainnya" tidak memberi
        // tahu apakah yang tersembunyi dua atau dua ratus.
        $this->assertStringContainsString('Tampilkan 14 jejak lebih lama', $isi,
            'Tombolnya tidak menyebut berapa jejak yang ditahan.');
    }

    #[Test]
    public function yang_tertahan_adalah_yang_TERTUA(): void
    {
        /*
         * Urutannya tertua di atas, terbaru di bawah — jejak dibaca sebagai
         * cerita dari awal. Kalau yang ditahan justru yang terbaru, panitia
         * yang baru saja menekan Simpan tidak melihat hasil tindakannya
         * sendiri, dan akan mengira perubahannya tidak tercatat.
         */
        [$orang, $baris] = $this->dengan(10);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $baris->getKey()]))
            ->assertOk()->getContent();

        $blok = $this->blokJejak($isi);

        // Butir ke-1 (tertua) ditahan; butir terakhir (terbaru) tidak.
        $butir = [];
        preg_match_all('/<li([^>]*)>(.*?)<\/li>/s', $blok, $cocok, PREG_SET_ORDER);

        foreach ($cocok as $c) {
            $butir[] = ['ditahan' => str_contains($c[1], 'rin-jejak-lama'), 'isi' => $c[2]];
        }

        $this->assertCount(10, $butir);

        $this->assertTrue($butir[0]['ditahan'], 'Jejak TERTUA tidak ditahan.');
        $this->assertStringContainsString('jejak ke-1 ', $butir[0]['isi']);

        $this->assertFalse($butir[9]['ditahan'], 'Jejak TERBARU ikut ditahan.');
        $this->assertStringContainsString('jejak ke-10 ', $butir[9]['isi']);

        // Tepat di batasnya: butir ke-4 (indeks 3) masih ditahan, ke-5 tidak.
        $this->assertTrue($butir[3]['ditahan']);
        $this->assertFalse($butir[4]['ditahan']);
    }

    #[Test]
    public function jejak_pendek_tidak_diberi_tombol_sama_sekali(): void
    {
        [$orang, $baris] = $this->dengan(self::BATAS);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $baris->getKey()]))
            ->assertOk()->getContent();

        $this->assertStringNotContainsString('rin-jejak-lama', $this->blokJejak($isi),
            'Jejak sebanyak batasnya ikut ditahan; tombolnya jadi membuka yang tidak ada.');

        /*
         * Dicari TOMBOLNYA, bukan untaian penandanya di mana saja.
         *
         * Percobaan pertama mencari 'data-mis-jejak-lagi' di seluruh halaman
         * dan langsung merah pada markah yang justru benar: skrip pembukanya
         * sendiri memuat pemilih `[data-mis-jejak-lagi]`. Pemindai yang
         * menjaring kodenya sendiri tidak menjaga apa pun.
         */
        $this->assertSame(0, preg_match('/<button[^>]*data-mis-jejak-lagi/', $isi),
            'Tombol pembuka tergambar padahal tidak ada yang ditahan.');
    }

    #[Test]
    public function yang_ditahan_tetap_ada_di_markahnya(): void
    {
        /*
         * Ditahan CSS, bukan dibuang dari markahnya. Jejak audit yang hanya
         * bisa dibuka lewat permintaan kedua ke peladen berarti satu jalur
         * lagi yang bisa gagal — dan kalau gagal, riwayatnya tidak bisa dibaca
         * sama sekali.
         */
        [$orang, $baris] = $this->dengan(15);

        $blok = $this->blokJejak($this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $baris->getKey()]))
            ->assertOk()->getContent());

        $this->assertStringContainsString('jejak ke-1 ', $blok,
            'Jejak tertua hilang dari markahnya; ia tidak bisa dibuka lagi tanpa memuat ulang.');
    }

    #[Test]
    public function aturan_sembunyinya_cukup_berbobot(): void
    {
        /*
         * `.rin-jejak li` menyetel `display: grid`. Kelas tunggal
         * `.rin-jejak-lama` berbobot (0,1,0) dan KALAH terhadapnya — barisnya
         * tetap tergambar tanpa ada galat apa pun yang menunjukkannya.
         * Pemilihnya wajib menyebut keduanya.
         */
        $gaya = file_get_contents(
            resource_path('views/account/pendaftaran_layanan/rincian.blade.php')
        );

        $this->assertMatchesRegularExpression(
            '/\.rin-jejak\s+li\.rin-jejak-lama\s*\{[^}]*display:\s*none/s', $gaya,
            'Aturan penyembunyi jejak lama tidak lagi menyebut .rin-jejak li, '
            . 'jadi bobotnya kalah dan jejaknya tetap tergambar.'
        );

        $this->assertMatchesRegularExpression(
            '/\.rin-jejak\.rin-jejak-penuh\s+li\.rin-jejak-lama\s*\{[^}]*display:\s*grid/s', $gaya,
            'Aturan pembukanya hilang; tombolnya tidak akan menampilkan apa pun.'
        );
    }

    // ------------------------------------------------------------- pembantu

    private function blokJejak(string $isi): string
    {
        $ada = preg_match('/<ul class="rin-jejak">(?<dalam>.*?)<\/ul>/s', $isi, $cocok);

        $this->assertSame(1, $ada, 'Daftar jejak tidak tergambar.');

        return $cocok['dalam'];
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp} */
    private function dengan(int $jumlah): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $u = User::create([
            'full_name' => 'Rina Panitia', 'username' => 'jejak_' . Str::random(8),
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
            'nama' => 'Peserta ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0001', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => '5500000', 'status' => 'diproses',
        ]);

        for ($ke = 1; $ke <= $jumlah; $ke++) {
            PendaftaranJejak::create([
                'layanan' => 'scopus_camp',
                'pendaftaran_id' => (string) $baris->getKey(),
                'aksi' => 'ubah',
                'medan' => 'note',
                'dari' => 'jejak ke-' . $ke . ' lama',
                'ke' => 'jejak ke-' . $ke . ' baru',
                'oleh_nama' => 'Rina Panitia',
                // Waktunya dibedakan: urutannya created_at lalu id, dan baris
                // yang waktunya sama persis akan terurut dari id acaknya.
                'created_at' => now()->subMinutes($jumlah - $ke),
            ]);
        }

        return [$u, $baris];
    }
}
