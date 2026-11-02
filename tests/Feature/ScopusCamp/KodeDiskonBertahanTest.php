<?php

namespace Tests\Feature\ScopusCamp;

use App\CategoriesScopusCamp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kode diskon tidak boleh hilang saat borangnya dipantulkan validasi.
 *
 * Terukur 2 Nov 2026: kedua isiannya — kotak yang terlihat dan medan
 * tersembunyi yang benar-benar terkirim — tidak memakai old(). Jadi
 * pendaftar yang lupa mengisi nama mendapat halaman yang sama TANPA kode
 * diskonnya, dan kalau ia tidak menyadarinya ia membayar penuh.
 *
 * Tidak ada galat apa pun yang memberi tahu; yang hilang justru potongannya
 * sendiri, dan satu-satunya yang dirugikan orang yang sudah berhak atasnya.
 */
class KodeDiskonBertahanTest extends TestCase
{
    use DatabaseTransactions;

    private const BORANG = 'resources/views/public/scopus_camp/form_pendaftaran.blade.php';

    protected function setUp(): void
    {
        parent::setUp();

        // Borangnya tidak digambar sama sekali tanpa gerbang pembayaran.
        config()->set('services.doku.client_id', 'BRN-UJI-KODE');
        config()->set('services.doku.secret_key', 'SK-UJI-KODE');
    }

    private function angkatan(): CategoriesScopusCamp
    {
        return CategoriesScopusCamp::create([
            'layanan' => 'scopus_camp', 'varian' => 'jawa', 'token' => Str::random(30),
            'nama' => 'UJI Camp Kode ' . Str::random(6),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(12)->toDateString(),
            'lokasi' => 'Yogyakarta', 'group_wa' => 'https://chat.whatsapp.com/ujikode',
            'total_kuota' => 20, 'sisa_kuota' => 20, 'biaya' => 5500000, 'status' => 'active',
        ]);
    }

    #[Test]
    public function kodenya_kembali_sesudah_validasi_menolak(): void
    {
        $a = $this->angkatan();

        $this->from(route('public.scopuscamp.formpendaftaran', $a->getKey()))
            ->post(route('public.scopuscamp.store'), [
                'kategori_id' => $a->getKey(),
                'nama' => '', // sengaja kosong supaya validasinya menolak
                'email' => 'kode@contoh.test',
                'telp' => '081234567890',
                'affiliasi' => 'Universitas Uji',
                'jumlah_pendaftar' => 1,
                'setuju' => '1',
                'kode_diskon' => 'KODEKUUJI',
            ])
            ->assertRedirect();

        $isi = $this->get(route('public.scopuscamp.formpendaftaran', $a->getKey()))
            ->assertOk()->getContent();

        $this->assertStringContainsString('value="KODEKUUJI"', $isi,
            'Kode diskonnya hilang saat borangnya dipantulkan; pendaftar yang '
            . 'tidak menyadarinya akan membayar penuh.');

        // DUA isian membawanya: yang terlihat supaya orangnya tahu kodenya
        // masih terpasang, dan yang tersembunyi sebab itu yang terkirim.
        $this->assertSame(2, substr_count($isi, 'value="KODEKUUJI"'),
            'Hanya salah satu isiannya yang dikembalikan. Yang terlihat saja '
            . 'berarti kodenya tampak terpasang tetapi tidak ikut terkirim; '
            . 'yang tersembunyi saja berarti orangnya tidak tahu kodenya ada.');
    }

    #[Test]
    public function borangnya_memeriksa_ulang_kode_yang_dikembalikan(): void
    {
        /*
         * Mengembalikan kotaknya saja tidak cukup: potongannya hidup di
         * medan tersembunyi dan di total yang dihitung skrip, dan keduanya
         * kosong di halaman yang baru dimuat. Orangnya akan melihat kodenya
         * terpasang sementara totalnya memajang harga penuh — dan tidak ada
         * yang memberi tahu mana yang benar.
         *
         * Dipatok di sumbernya: PHPUnit tidak menjalankan JavaScript.
         */
        $sumber = file_get_contents(base_path(self::BORANG));

        $this->assertStringContainsString('function periksaKodeDiskon(', $sumber,
            'Pemeriksaan kode tidak lagi berupa fungsi bernama, jadi ia tidak '
            . 'bisa dipanggil ulang saat halaman dimuat maupun saat jumlah '
            . 'peserta berubah.');

        $this->assertMatchesRegularExpression(
            '/kodeDiskon\'\)\.value\.trim\(\) !== \'\'[\s\S]{0,120}periksaKodeDiskon\(true\)/',
            $sumber,
            'Kode yang dikembalikan old() tidak diperiksa ulang saat halaman '
            . 'dimuat; kodenya terlihat terpasang tetapi potongannya tidak.');
    }

    #[Test]
    public function potongannya_dihitung_ulang_saat_jumlah_peserta_berubah(): void
    {
        /*
         * Promo PERSEN menghitung potongannya dari subtotal. Terukur dengan
         * promo 10%: potongan Rp 550.000 pada satu peserta menjadi
         * Rp 1.650.000 pada tiga peserta — dan tanpa pemeriksaan ulang,
         * layar tertinggal di angka lama sementara peladen menagih angka
         * yang baru. Selisih 1,1 juta antara yang dipajang dan yang ditagih.
         */
        $sumber = file_get_contents(base_path(self::BORANG));

        $this->assertStringContainsString('periksaUlangDiskon();', $sumber,
            'Jumlah peserta berubah tanpa memicu perhitungan ulang potongan.');

        $this->assertStringContainsString("'&jumlah=' + encodeURIComponent(", $sumber,
            'Jumlah peserta tidak ikut dikirim ke pemeriksa kode, jadi '
            . 'potongan promo persen dihitung dari satu kursi saja.');
    }
}
