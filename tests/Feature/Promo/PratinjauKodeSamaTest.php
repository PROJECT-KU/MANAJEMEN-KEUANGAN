<?php

namespace Tests\Feature\Promo;

use App\CategoriesScopusCamp;
use App\PromoLayanan;
use App\Support\PotonganKode;
use App\Support\PromoAngkatan;
use App\WebinarEksklusifPendaftaran;
use App\KategoriLayanan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tombol "Cek" dan tagihannya HARUS menjawab angka yang sama.
 *
 * Terukur 2 Nov 2026 di angkatan ke-202. Alamat pratinjau hanya mengenali
 * kode milik ANGKATAN, sementara store() sudah mengenali kode promo juga.
 * Jadi "EKSKLUSIF" dijawab "Kode diskonnya tidak berlaku untuk angkatan
 * ini" — padahal kalau borangnya tetap dikirim, peladen MEMOTONG 550.000.
 *
 * Arahnya kebetulan aman (layar menjanjikan lebih sedikit daripada yang
 * ditagih), tetapi akibatnya tetap nyata: yang membacanya menyerah sebelum
 * sempat tahu potongannya ada. Dan satu perubahan kecil bisa membalik
 * arahnya jadi layar menjanjikan lebih banyak daripada yang ditagih.
 */
class PratinjauKodeSamaTest extends TestCase
{
    use DatabaseTransactions;

    private const KODE = 'UJIPRATINJAU';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-PRA');
        config()->set('services.doku.secret_key', 'SK-UJI-PRA');
        Mail::fake();
        Http::fake(['*/checkout/v1/payment' => Http::response([
            'response' => [
                'payment' => ['url' => 'https://staging.doku.com/checkout-link-v2/P'],
                'order' => ['invoice_number' => 'CAMP-PRA'],
            ],
        ], 200)]);

        PromoAngkatan::lupakan();
    }

    protected function tearDown(): void
    {
        PromoAngkatan::lupakan();
        parent::tearDown();
    }

    private function angkatan(): CategoriesScopusCamp
    {
        return CategoriesScopusCamp::create([
            'layanan' => 'scopus_camp', 'varian' => 'jawa',
            'token' => Str::random(30),
            'nama' => 'UJI Camp Pratinjau ' . Str::random(6),
            'nama_ke' => (string) random_int(3000, 3999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(12)->toDateString(),
            'lokasi' => 'Yogyakarta',
            'group_wa' => 'https://chat.whatsapp.com/ujipra',
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'biaya' => 5500000, 'status' => 'active',
        ]);
    }

    private function promo(CategoriesScopusCamp $a, array $syarat = []): PromoLayanan
    {
        $p = PromoLayanan::create([
            'nama' => 'UJI Promo Pratinjau ' . Str::random(5),
            'kode' => self::KODE,
            'syarat_layanan' => $syarat ?: null,
            'tipe' => 'rupiah', 'nominal' => 550000,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addMonth()->toDateString(),
            'aktif' => true,
        ]);

        $p->angkatan()->attach($a->getKey());
        PromoAngkatan::lupakan();

        return $p;
    }

    /** Berapa yang dijanjikan tombol "Cek" di layar. */
    private function pratinjau(CategoriesScopusCamp $a, string $telp): int
    {
        $jawab = $this->getJson(route('public.scopuscamp.cekkodediskon', $a->getKey())
            . '?kode_diskon=' . self::KODE . '&telp=' . urlencode($telp) . '&jumlah=1')
            ->assertOk()->json();

        return (int) ($jawab['casted_nominal_diskon'] ?? 0);
    }

    /** Berapa yang BENAR-BENAR dipotong peladen saat menagih. */
    private function ditagih(CategoriesScopusCamp $a, string $telp): int
    {
        return PotonganKode::hitung(
            $a, self::KODE, (int) $a->harga_setelah_promo, $telp
        )['potongan'];
    }

    #[Test]
    public function promo_tanpa_syarat_dijanjikan_sama_dengan_yang_ditagih(): void
    {
        $a = $this->angkatan();
        $this->promo($a);

        $telp = '081200000055';

        $this->assertSame(550000, $this->ditagih($a, $telp));
        $this->assertSame($this->ditagih($a, $telp), $this->pratinjau($a, $telp),
            'Tombol "Cek" menjanjikan angka yang berbeda dari yang ditagih.');
    }

    #[Test]
    public function promo_bersyarat_sepakat_untuk_alumni_maupun_bukan(): void
    {
        /*
         * Dua-duanya diperiksa. Sepakat hanya di salah satu sisi berarti
         * layar dan tagihan tetap bisa berselisih bagi separuh orang.
         */
        $a = $this->angkatan();
        $this->promo($a, ['webinar_eksklusif']);

        $alumni = '0812' . random_int(10000000, 99999999);
        $bukan = '0813' . random_int(10000000, 99999999);

        $sesi = KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif', 'token' => Str::random(30),
            'nama' => 'UJI Webinar Pra ' . Str::random(5),
            'nama_ke' => (string) random_int(4000, 4999),
            'mulai' => now()->subDays(30)->toDateString(),
            'selesai' => now()->subDays(30)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30',
            'platform' => 'Zoom', 'pemateri' => 'Pemateri Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '20',
            'biaya' => '129000', 'status' => 'active',
        ]);

        WebinarEksklusifPendaftaran::create([
            'kategori_id' => $sesi->getKey(),
            'id_transaksi' => 'WE-PRA-' . Str::random(6),
            'nama' => 'Alumni Pratinjau', 'email' => 'pra' . Str::random(5) . '@contoh.test',
            'telp' => $alumni, 'jumlah_pendaftar' => 1,
            'total_pembayaran' => '129000', 'status' => 'paid',
        ]);

        $this->assertSame(550000, $this->ditagih($a, $alumni), 'Alumni tidak dapat potongan.');
        $this->assertSame(0, $this->ditagih($a, $bukan), 'Yang bukan alumni ikut dapat potongan.');

        foreach ([$alumni => 'alumni', $bukan => 'bukan alumni'] as $telp => $ket) {
            $this->assertSame($this->ditagih($a, $telp), $this->pratinjau($a, $telp),
                'Tombol "Cek" dan tagihannya berselisih untuk ' . $ket . '.');
        }
    }
}
