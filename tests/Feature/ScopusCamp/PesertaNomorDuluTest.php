<?php

namespace Tests\Feature\ScopusCamp;

use App\CategoriesScopusCamp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Di kartu "Peserta lainnya", NOMOR didahulukan daripada nama.
 *
 * Keputusan pemiliknya 2 Nov 2026, dan alasannya sama dengan kartu
 * pendaftar utama di atasnya: nomor itulah yang MENCARI namanya dari
 * pesanan sebelumnya. Dengan nama di depan, orangnya sudah terlanjur
 * mengetik hal yang justru bisa kami isikan sendiri — dan isian yang
 * terisi sendiri tepat saat kursornya ada di sana justru mengagetkan.
 *
 * Urutannya hidup di DUA tempat: markah Blade yang merender ulang kiriman
 * yang ditolak validasi (old()), dan perakit JavaScript yang membangun
 * barisnya saat jumlah peserta diubah. Dibalik di satu tempat saja,
 * urutannya berbeda antara halaman yang baru dibuka dan halaman yang
 * kembali dari validasi gagal — dan tidak ada galat yang menyebutnya.
 */
class PesertaNomorDuluTest extends TestCase
{
    use DatabaseTransactions;

    private const BORANG = 'resources/views/public/scopus_camp/form_pendaftaran.blade.php';

    protected function setUp(): void
    {
        parent::setUp();

        // Borangnya tidak digambar sama sekali tanpa gerbang pembayaran.
        config()->set('services.doku.client_id', 'BRN-UJI-URUT');
        config()->set('services.doku.secret_key', 'SK-UJI-URUT');
    }

    private function angkatan(): CategoriesScopusCamp
    {
        return CategoriesScopusCamp::create([
            'layanan' => 'scopus_camp',
            'token' => Str::random(30),
            'nama' => 'UJI Camp Urut ' . Str::random(6),
            'nama_ke' => (string) random_int(9000, 9999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(12)->toDateString(),
            'lokasi' => 'Yogyakarta',
            'group_wa' => 'https://chat.whatsapp.com/ujiurut',
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'biaya' => 5500000, 'status' => 'active',
        ]);
    }

    #[Test]
    public function markah_lamanya_menaruh_nomor_sebelum_nama(): void
    {
        /*
         * Dipicu lewat kiriman yang DITOLAK validasi, sebab hanya itu yang
         * membuat Blade merender baris peserta dari old(). Halaman yang
         * baru dibuka merakitnya lewat JavaScript, dan itu dijaga uji di
         * bawah.
         */
        $a = $this->angkatan();

        $this->from(route('public.scopuscamp.formpendaftaran', $a->getKey()))
            ->post(route('public.scopuscamp.store'), [
                'kategori_id' => $a->getKey(),
                'nama' => '', // sengaja kosong supaya validasinya menolak
                'email' => 'urut@contoh.test',
                'telp' => '081234567890',
                'affiliasi' => 'Universitas Uji',
                'jumlah_pendaftar' => 2,
                'peserta' => [['nama' => 'Peserta Dua', 'telp' => '081200000002']],
                'setuju' => '1',
            ])
            ->assertRedirect();

        // Halaman borangnya dibuka LAGI; old() baru terbaca di permintaan
        // berikutnya, bukan pada jawaban pengalihannya sendiri.
        $isi = $this->get(route('public.scopuscamp.formpendaftaran', $a->getKey()))
            ->assertOk()->getContent();

        $letakTelp = strpos($isi, 'peserta[0][telp]');
        $letakNama = strpos($isi, 'peserta[0][nama]');

        $this->assertNotFalse($letakTelp, 'Baris peserta tidak dirender ulang dari old().');
        $this->assertNotFalse($letakNama, 'Isian nama peserta tidak dirender ulang dari old().');

        $this->assertLessThan($letakNama, $letakTelp,
            'Nama peserta digambar sebelum nomornya; orangnya diminta mengetik '
            . 'nama yang justru bisa diisikan dari nomornya.');
    }

    #[Test]
    public function perakit_javascriptnya_juga_menaruh_nomor_sebelum_nama(): void
    {
        /*
         * Dipatok di sumbernya, bukan lewat peramban: PHPUnit tidak
         * menjalankan JavaScript. Yang diperiksa urutan dua baris yang
         * merakit medannya — kalau tertukar, barisnya tergambar dengan nama
         * di depan pada halaman yang baru dibuka, sementara markah Blade di
         * atas tetap benar.
         */
        $sumber = file_get_contents(base_path(self::BORANG));

        $letakTelp = strpos($sumber, "medanPeserta(i, 'telp'");
        $letakNama = strpos($sumber, "medanPeserta(i, 'nama'");

        $this->assertNotFalse($letakTelp, 'Perakit medan nomor peserta tidak ada lagi.');
        $this->assertNotFalse($letakNama, 'Perakit medan nama peserta tidak ada lagi.');

        $this->assertLessThan($letakNama, $letakTelp,
            'Perakit JavaScript masih menaruh nama sebelum nomor, jadi halaman '
            . 'yang baru dibuka berbeda urutannya dari halaman yang kembali '
            . 'dari validasi gagal.');
    }

    #[Test]
    public function isian_peserta_punya_penanda_yang_dipakai_pencarinya(): void
    {
        /*
         * Pencarian nama peserta memasang SATU pendengar di induknya lalu
         * mengenali isiannya lewat kelas `daf-peserta-telp` dan
         * `daf-peserta-nama`. Kelas itu hilang, pendengarnya diam tanpa
         * satu galat pun — nomor diketik, tidak ada yang terjadi.
         */
        $sumber = file_get_contents(base_path(self::BORANG));

        foreach (['daf-peserta-telp', 'daf-peserta-nama'] as $kelas) {
            $this->assertStringContainsString($kelas, $sumber,
                'Penanda ' . $kelas . ' hilang; pencari nama peserta tidak '
                . 'mengenali isiannya lagi dan diam tanpa galat.');
        }

        $this->assertStringContainsString("classList.add('daf-peserta-' + kunci)", $sumber,
            'Baris yang dirakit JavaScript tidak lagi diberi penanda itu, jadi '
            . 'pencariannya cuma bekerja pada baris hasil validasi gagal.');
    }
}
