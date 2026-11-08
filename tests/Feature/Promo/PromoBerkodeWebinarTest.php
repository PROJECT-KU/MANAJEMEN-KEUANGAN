<?php

namespace Tests\Feature\Promo;

use App\KategoriLayanan;
use App\PromoLayanan;
use App\Support\PotonganKode;
use App\Support\PromoAngkatan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Promo BERKODE di Webinar Eksklusif: hidup di peladen, mati di layar.
 *
 * ---------------------------------------------------------------------------
 * Apa yang dilaporkan
 * ---------------------------------------------------------------------------
 *
 * "Kenapa di Webinar Eksklusif promonya tidak terbaca?" — promo "Promo
 * Eksklusif" (potongan Rp 51.000, kode EKSKLUSIF, syarat pernah lunas di
 * Webinar Eksklusif) sudah dipasang dan angkatannya dicentang, tetapi halaman
 * landing tetap memajang Rp 150.000.
 *
 * Harga publik yang tidak berubah itu BENAR dan memang diminta pemiliknya
 * sendiri: promo berkode tidak boleh mencoret harga umum. Yang salah TIGA hal
 * lain, dan ketiganya membuat promonya tidak bisa dipakai siapa pun.
 *
 * ---------------------------------------------------------------------------
 * Ketiganya
 * ---------------------------------------------------------------------------
 *
 *   1. kotak isian kodenya tidak pernah digambar — pagarnya bertanya
 *      `$sesi->kode_diskon !== ''`, kode gaya lama yang menempel di
 *      angkatan, sementara kode promo hidup di baris promonya;
 *   2. pratinjau kodenya tidak mengirim NOMOR, padahal syarat alumni
 *      butuh nomor. Peladen memotong Rp 51.000 saat borangnya dikirim,
 *      sementara layar menjawab "Kode itu tidak cocok";
 *   3. pratinjau di layar ADMIN menjanjikan "Rp 150.000 jadi Rp 99.000"
 *      tanpa satu kata pun soal kode maupun syaratnya — itu yang membuat
 *      pemiliknya mengira harga publiknya akan ikut turun.
 */
class PromoBerkodeWebinarTest extends TestCase
{
    use DatabaseTransactions;

    private function sesi(array $ganti = []): KategoriLayanan
    {
        return KategoriLayanan::create(array_merge([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'UJI Webinar Kode ' . Str::random(6),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(10)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30',
            'platform' => 'Zoom',
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'biaya' => 150000, 'status' => 'active',
        ], $ganti));
    }

    private function promoBerkode(KategoriLayanan $sesi, array $ganti = []): PromoLayanan
    {
        $p = PromoLayanan::create(array_merge([
            'nama' => 'UJI Promo Kode ' . Str::random(5),
            'kode' => 'UJIKODE' . Str::upper(Str::random(4)),
            'tipe' => 'rupiah',
            'nominal' => 51000,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addMonth()->toDateString(),
            'aktif' => true,
        ], $ganti));

        $p->angkatan()->attach($sesi->getKey());
        PromoAngkatan::lupakan();

        return $p;
    }

    /** Nomor yang SUDAH pernah lunas di Webinar Eksklusif. */
    private function alumni(KategoriLayanan $sesi): string
    {
        $nomor = '0813' . random_int(10000000, 99999999);

        WebinarEksklusifPendaftaran::create([
            'token' => Str::random(30),
            'id_transaksi' => \App\Support\PendaftaranSemuaLayanan::nomorBaru('webinar_eksklusif'),
            'kategori_id' => $sesi->getKey(),
            'nama' => 'Alumni Webinar',
            'email' => Str::lower(Str::random(10)) . '@contoh.test',
            'telp' => \App\Support\NomorTelepon::rapikan($nomor),
            'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '150000',
            'status' => 'Pendaftaran Diterima',
        ]);

        return $nomor;
    }

    // ----------------------------------------- 1. kotak isiannya digambar

    #[Test]
    public function kotak_kode_digambar_untuk_angkatan_yang_disasar_promo_berkode(): void
    {
        /*
         * Pagarnya dulu hanya mengenali kode milik ANGKATAN. Promo berkode
         * karena itu tidak pernah punya tempat untuk diketik — terukur pada
         * "testing webinar eksklusif 3", yang `kode_diskon`-nya NULL.
         */
        $sesi = $this->sesi();

        $this->get(route('public.webinareksklusif.daftar', $sesi->getKey()))
            ->assertOk()
            ->assertDontSee('id="ses-kode"', false);

        $this->promoBerkode($sesi);

        $this->get(route('public.webinareksklusif.daftar', $sesi->getKey()))
            ->assertOk()
            ->assertSee('id="ses-kode"', false);
    }

    #[Test]
    public function kotak_kode_tetap_digambar_untuk_kode_milik_angkatan(): void
    {
        // Jalur lama tidak boleh ikut hilang saat pagarnya diperlebar.
        $sesi = $this->sesi(['kode_diskon' => 'HEMAT30', 'nominal_diskon' => '30000']);

        $this->get(route('public.webinareksklusif.daftar', $sesi->getKey()))
            ->assertOk()
            ->assertSee('id="ses-kode"', false);
    }

    #[Test]
    public function promo_tanpa_kode_tidak_memunculkan_kotak_isian(): void
    {
        /*
         * Promo OTOMATIS memotong harga sendiri; tidak ada yang perlu
         * diketik. Kotak yang muncul di sana membuat orang mencari kode yang
         * tidak pernah ada.
         */
        $sesi = $this->sesi();
        $this->promoBerkode($sesi, ['kode' => null]);

        $this->get(route('public.webinareksklusif.daftar', $sesi->getKey()))
            ->assertOk()
            ->assertDontSee('id="ses-kode"', false);
    }

    // ------------------------------------------- 2. pratinjau membawa nomor

    #[Test]
    public function pratinjau_dan_tagihan_sepakat_untuk_promo_bersyarat_alumni(): void
    {
        /*
         * INTI laporannya. Peladen memotong Rp 51.000, layar menjawab "tidak
         * cocok" — sebab JavaScript-nya tidak mengirim nomor, padahal
         * cekDiskon() memang sudah membacanya sejak awal.
         */
        $sesi = $this->sesi();
        $promo = $this->promoBerkode($sesi, ['syarat_layanan' => ['webinar_eksklusif']]);
        $nomor = $this->alumni($sesi);

        PromoAngkatan::lupakan();

        // Tanpa nomor: memang belum berhak — dan itu benar.
        $this->postJson(route('public.webinareksklusif.cekdiskon', $sesi->getKey()), [
            'kode' => $promo->kode, 'jumlah' => 1,
        ])->assertOk()->assertJsonPath('cocok', false);

        // Dengan nomor alumni: potongannya keluar.
        $this->postJson(route('public.webinareksklusif.cekdiskon', $sesi->getKey()), [
            'kode' => $promo->kode, 'jumlah' => 1, 'telp' => $nomor,
        ])->assertOk()
            ->assertJsonPath('cocok', true)
            ->assertJsonPath('potongan', 51000);

        // Dan jalur yang MENAGIH sepakat dengan pratinjaunya.
        $hasil = PotonganKode::hitung($sesi, $promo->kode, 150000, $nomor);

        $this->assertSame(51000, $hasil['potongan']);
    }

    #[Test]
    public function borangnya_benar_benar_mengirim_nomor_ke_pratinjau(): void
    {
        /*
         * Perilaku JavaScript; PHPUnit tidak menjalankannya. Yang dipin
         * markah yang dikirim ke peramban: nomornya ikut di badan permintaan,
         * ikut di kunci dedupe, dan perubahan nomor memicu pemeriksaan ulang.
         *
         * Ketiganya hilang saat cacatnya ada, dan ketiganya perlu: tanpa
         * kunci dedupe, mengisi nomor SESUDAH kode tidak memicu apa pun —
         * dan itu urutan yang wajar, sebab kotak kodenya ada di bawah nomor.
         */
        $sesi = $this->sesi();
        $this->promoBerkode($sesi);

        $isi = (string) $this->get(route('public.webinareksklusif.daftar', $sesi->getKey()))
            ->assertOk()->getContent();

        $this->assertStringContainsString('telp: nomor', $isi);
        $this->assertStringContainsString("'|' + nomor", $isi);
        $this->assertStringContainsString("telp.addEventListener('change', periksa)", $isi);
    }

    // --------------------------------------- 3. pratinjau admin menyebut syarat

    #[Test]
    public function pratinjau_admin_menyebut_kode_dan_syaratnya(): void
    {
        /*
         * Kotak hijau di layar penyusun promo berbunyi "harga Rp 150.000
         * jadi Rp 99.000 per peserta, hemat Rp 51.000" — tanpa satu kata pun
         * soal kode maupun syarat alumni. Itu yang membuat pemiliknya
         * membuka halaman umum, melihat Rp 150.000, dan mengira promonya
         * tidak terbaca.
         */
        $isi = (string) file_get_contents(
            base_path('resources/views/account/promo_layanan/form.blade.php')
        );

        $this->assertStringContainsString('Harga di halaman umum TIDAK berubah', $isi);
        $this->assertStringContainsString('hanya kalau pendaftar mengetik kode', $isi);
        $this->assertStringContainsString('hanya untuk yang pernah lunas di layanan yang dicentang', $isi);

        /*
         * Kelasnya BUKAN .prf-syarat — nama itu sudah dipakai daftar centang
         * layanan dan bergaya `display: flex`. Dipakai ulang, kalimatnya
         * pecah jadi kotak-kotak melayang tanpa satu galat pun.
         */
        $this->assertStringContainsString("'prf-pratinjau-syarat'", $isi);
        $this->assertStringContainsString('.prf-pratinjau-syarat {', $isi);
    }

    // ------------------------------------- harga publiknya TIDAK ikut turun

    #[Test]
    public function harga_publik_tetap_penuh_untuk_promo_berkode(): void
    {
        /*
         * Ini yang memang DIMINTA pemiliknya dan tidak boleh berubah: promo
         * berkode tidak mencoret harga umum. Diuji bersama yang lain supaya
         * perbaikan di atas tidak diam-diam menariknya ikut turun.
         */
        $sesi = $this->sesi();
        $this->promoBerkode($sesi, ['syarat_layanan' => ['webinar_eksklusif']]);

        PromoAngkatan::lupakan();

        $jawab = $this->getJson('/api/webinar-eksklusif/daftar')->assertOk();

        $baris = collect($jawab->json('sesi') ?? [])
            ->firstWhere('id', (string) $sesi->getKey());

        if ($baris === null) {
            $this->markTestSkipped('Sesi uji tidak masuk daftar API (ada sesi lain yang lebih dekat).');
        }

        $this->assertSame(150000, (int) $baris['harga']);
        $this->assertNull($baris['harga_promo']);
    }
}
