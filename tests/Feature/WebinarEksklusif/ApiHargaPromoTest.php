<?php

namespace Tests\Feature\WebinarEksklusif;

use App\KategoriLayanan;
use App\PromoLayanan;
use App\Support\PromoAngkatan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Harga yang dipajang halaman landing SAMA dengan yang ditagih borangnya.
 *
 * Halaman landing Webinar Eksklusif berdiri di subdomainnya sendiri dan
 * mengambil seluruh isinya dari endpoint ini. Ia tidak punya akses apa pun ke
 * basis data, jadi apa pun yang tidak dikirim di sini tidak akan pernah
 * sampai ke sana — tanpa galat, tanpa halaman kosong, cuma angka lama yang
 * terus terpampang.
 *
 * Itu yang terjadi 15 Okt 2026. Admin memasang Promo Layanan Rp 21.000 untuk
 * sesi Express Writing; layar MIS dan borang pendaftarannya ikut turun jadi
 * Rp 129.000, tetapi endpoint ini membaca `total_biaya` — kolom diskon gaya
 * lama yang tidak pernah disentuh fitur promo — sehingga `harga_promo`
 * memulangkan null dan halaman landing tetap memajang Rp 150.000. Halaman itu
 * justru yang dituju iklan berbayar.
 */
class ApiHargaPromoTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        PromoAngkatan::lupakan();
    }

    private function sesi(array $ubah = []): KategoriLayanan
    {
        /*
         * Kuncinya DISETEL LANGSUNG kalau pemanggilnya menyerahkannya.
         *
         * `id` tidak ada di $fillable — create() akan membuangnya diam-diam —
         * jadi ia dipasang lewat forceFill sebelum disimpan. Dipakai uji yang
         * harus menang pemutus seri `orderBy('id')`.
         */
        $kunci = $ubah['id'] ?? null;
        unset($ubah['id']);

        if ($kunci !== null) {
            $sesi = new KategoriLayanan;
            $sesi->forceFill(array_merge($this->bawaanSesi(), $ubah, ['id' => $kunci]))->save();

            return $sesi;
        }

        return KategoriLayanan::create(array_merge($this->bawaanSesi(), $ubah));
    }

    /** @return array<string, mixed> */
    private function bawaanSesi(): array
    {
        return [
            'layanan' => 'webinar_eksklusif',
            'nama' => 'Sesi Uji ' . Str::random(6),
            'nama_ke' => '9',
            'mulai' => now()->addDays(10)->toDateString(),
            'status' => 'active',
            'biaya' => '150000',
        ];
    }

    private function promo(KategoriLayanan $sesi, array $ubah = []): PromoLayanan
    {
        $promo = PromoLayanan::create(array_merge([
            'nama' => 'Promo Uji ' . Str::random(4),
            'tipe' => 'nominal',
            'nominal' => '21000',
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDays(7)->toDateString(),
            'aktif' => true,
        ], $ubah));

        $promo->angkatan()->attach($sesi->getKey());

        PromoAngkatan::lupakan();

        return $promo;
    }

    /** Sesi mana yang terbit; endpoint hanya memulangkan satu. */
    private function kartu(KategoriLayanan $sesi): ?array
    {
        $isi = $this->getJson('/api/webinar-eksklusif')->json();

        foreach (array_merge([$isi['sesi'] ?? null], $isi['berikutnya'] ?? []) as $kartu) {
            if (($kartu['id'] ?? null) === $sesi->getKey()) {
                return $kartu;
            }
        }

        return null;
    }

    /** @test */
    public function promo_layanan_ikut_terbit_sebagai_harga_promo(): void
    {
        $sesi = $this->sesi();
        $this->promo($sesi);

        $kartu = $this->kartu($sesi);

        $this->assertNotNull($kartu, 'Sesi ujinya tidak terbit sama sekali di endpoint.');

        $this->assertSame(150000, $kartu['harga'],
            'Harga coretnya berubah; yang boleh berubah hanya harga promonya.');

        $this->assertSame(129000, $kartu['harga_promo'],
            'Promo Layanan tidak sampai ke halaman landing. Halaman itu tidak '
            . 'punya jalan lain ke basis data, jadi ia akan terus memajang '
            . 'harga penuh tanpa galat apa pun.');

        $this->assertSame('Rp 129.000', $kartu['harga_promo_tulis']);
    }

    /** @test */
    public function yang_dipajang_sama_dengan_yang_akan_ditagih(): void
    {
        /*
         * Inti perkaranya, dan sebabnya diuji terhadap accessor yang sama
         * yang dipakai HargaUmum saat menagih: dua rumus terpisah untuk satu
         * angka adalah cara cacat ini lahir pertama kali.
         */
        $sesi = $this->sesi(['biaya' => '999000']);
        $this->promo($sesi, ['tipe' => 'persen', 'persen' => '10', 'nominal' => null]);

        $kartu = $this->kartu($sesi);

        $this->assertNotNull($kartu);
        $this->assertSame(
            (int) $sesi->fresh()->harga_setelah_promo,
            $kartu['harga_promo'],
            'Angka yang dipajang berbeda dari angka yang akan ditagih.');
    }

    /** @test */
    public function diskon_gaya_lama_tetap_terbit_seperti_sebelumnya(): void
    {
        /*
         * total_biaya adalah diskon per angkatan gaya lama. Ia sudah terbit
         * sejak dulu, dan perbaikan promo tidak boleh mematikannya — di
         * produksi masih ada 18 angkatan yang memakainya.
         */
        $sesi = $this->sesi(['biaya' => '150000', 'total_biaya' => '120000']);

        $kartu = $this->kartu($sesi);

        $this->assertNotNull($kartu);
        $this->assertSame(150000, $kartu['harga']);
        $this->assertSame(120000, $kartu['harga_promo']);
    }

    /** @test */
    public function naskah_deskripsinya_ikut_menyebut_harga_promo(): void
    {
        /*
         * Halaman landing memajang naskah deskripsi apa adanya di bawah
         * kotak harganya. Kalau naskah itu masih menyebut harga penuh, satu
         * halaman menyebut dua harga berbeda untuk satu sesi — dan yang
         * membacanya tidak punya cara tahu mana yang berlaku.
         */
        /*
         * Tanggalnya sengaja PALING DEKAT: hanya sesi pertama yang dikirim
         * lengkap beserta naskahnya; sesi lain cuma diringkas.
         *
         * TANGGAL SAJA TIDAK CUKUP, dan ini perbaikan kedua atas uji yang
         * sama. Patokan sebelumnya memakai hari ini dengan alasan "sesi nyata
         * yang bertabrakan mulainya besok" — dan esoknya tiba. Terukur 25 Okt
         * 2026: sesi nyata "testing webinar eksklusif" mulai hari itu juga,
         * jadi tanggalnya seri lagi dan ujinya merah separuh waktu. Patokan
         * apa pun yang berupa tanggal akan mengalami hal yang sama begitu
         * ada sesi nyata di tanggal itu.
         *
         * Yang dipatok sekarang PEMUTUS SERINYA. Kuerinya mengurutkan
         * `mulai` lalu `id` — lihat ApiWebinarEksklusifController — jadi
         * kunci yang dimulai nol berurut paling depan di antara UUID mana
         * pun. Dengan begitu sesi uji ini menang tanpa bergantung pada data
         * yang kebetulan ada.
         */
        $sesi = $this->sesi([
            'id' => '00000000-0000-4000-8000-' . bin2hex(random_bytes(6)),
            'mulai' => now()->toDateString(),
            'desc' => "Yang didapat peserta\n\n\u{1F4B5} Rp 150.000 per peserta",
        ]);
        $this->promo($sesi);

        $isi = $this->getJson('/api/webinar-eksklusif')->json();

        $this->assertSame($sesi->getKey(), $isi['sesi']['id'] ?? null,
            'Sesi ujinya bukan yang terbit lengkap; naskahnya tidak ikut diperiksa.');

        $this->assertStringContainsString('Rp 129.000', $isi['sesi']['deskripsi'] ?? '',
            'Naskah yang dikirim ke halaman landing masih menyebut harga penuh.');

        $this->assertStringContainsString('harga normal Rp 150.000', $isi['sesi']['deskripsi'] ?? '',
            'Harga normalnya hilang; yang membaca tidak tahu ia sedang dapat potongan.');
    }

    /** @test */
    public function tanpa_promo_apa_pun_tidak_ada_harga_coret(): void
    {
        $sesi = $this->sesi();

        $kartu = $this->kartu($sesi);

        $this->assertNotNull($kartu);
        $this->assertSame(150000, $kartu['harga']);
        $this->assertNull($kartu['harga_promo'],
            'Sesi tanpa promo memulangkan harga promo; halaman landing akan '
            . 'mencoret harganya sendiri tanpa ada potongan apa pun.');
    }
}
