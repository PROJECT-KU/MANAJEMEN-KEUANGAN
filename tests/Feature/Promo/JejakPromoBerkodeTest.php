<?php

namespace Tests\Feature\Promo;

use App\CategoriesScopusCamp;
use App\PendaftaranScopusCamp;
use App\PromoLayanan;
use App\Support\DampakPromo;
use App\Support\PromoAngkatan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Promo yang dipakai LEWAT KODE harus meninggalkan jejak.
 *
 * Terukur 2 Nov 2026: promo berkuota 2 dipakai sekali lewat kode, lalu
 * `terpakai` tetap 0 dan `kuotaHabis()` tetap false. Sebabnya `promo_id`
 * hanya diisi dari promo OTOMATIS, sementara yang berkode memotong harga
 * tanpa dicatat sama sekali.
 *
 * Akibatnya dua, dan keduanya diam: kuota promo berkode tidak membatasi
 * apa pun — promo "khusus 50 orang pertama" bisa dipakai lima ratus orang —
 * dan layar Dampak Promo melaporkan promo itu tidak pernah dipakai siapa
 * pun, jadi keputusan promo berikutnya dibuat dari angka yang salah.
 */
class JejakPromoBerkodeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-JEJAK');
        config()->set('services.doku.secret_key', 'SK-UJI-JEJAK');
        Mail::fake();
        Http::fake(['*/checkout/v1/payment' => Http::response([
            'response' => [
                'payment' => ['url' => 'https://staging.doku.com/checkout-link-v2/J'],
                'order' => ['invoice_number' => 'CAMP-J'],
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
            'layanan' => 'scopus_camp', 'varian' => 'jawa', 'token' => Str::random(30),
            'nama' => 'UJI Camp Jejak ' . Str::random(6),
            'nama_ke' => (string) random_int(7000, 7999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(12)->toDateString(),
            'lokasi' => 'Yogyakarta', 'group_wa' => 'https://chat.whatsapp.com/ujijejak',
            'total_kuota' => 20, 'sisa_kuota' => 20, 'biaya' => 5500000, 'status' => 'active',
        ]);
    }

    private function promo(CategoriesScopusCamp $a, array $ganti = []): PromoLayanan
    {
        $p = PromoLayanan::create(array_merge([
            'nama' => 'UJI Promo Jejak ' . Str::random(5),
            'kode' => 'JEJAKUJI',
            'tipe' => 'rupiah', 'nominal' => 500000,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addMonth()->toDateString(),
            'aktif' => true,
        ], $ganti));

        $p->angkatan()->attach($a->getKey());
        PromoAngkatan::lupakan();

        return $p;
    }

    private function daftar(CategoriesScopusCamp $a, ?string $kode): ?PendaftaranScopusCamp
    {
        /*
         * Baris barunya dicari lewat KUNCI yang sudah ada sebelumnya, bukan
         * latest('created_at').
         *
         * Dua pendaftaran yang dibuat dalam detik yang sama punya
         * `created_at` yang SAMA PERSIS, dan urutan di antaranya tidak
         * ditentukan — terukur di uji ini: pendaftar kedua memulangkan baris
         * PERTAMA, jadi potongannya terbaca 500.000 dan ujinya menuduh
         * penjagaan kuota yang sebenarnya sudah bekerja.
         */
        $sebelum = PendaftaranScopusCamp::where('kategori_id', $a->getKey())
            ->pluck('id')->all();

        $this->post(route('public.scopuscamp.store'), [
            'kategori_id' => $a->getKey(), 'nama' => 'Pendaftar Jejak',
            'email' => 'jejak' . Str::random(6) . '@contoh.test',
            'telp' => '0812' . random_int(10000000, 99999999),
            'affiliasi' => 'Universitas Uji', 'jumlah_pendaftar' => 1, 'setuju' => '1',
            'kode_diskon' => $kode,
        ]);

        /*
         * Angka dampak promo disimpan di ingatan selama satu permintaan —
         * sengaja, sebab layar rinciannya menanyakannya berkali-kali. Di
         * dalam uji, permintaannya tidak pernah berakhir, jadi pendaftaran
         * yang baru dibuat tidak akan terhitung sampai ingatannya dibuang.
         */
        DampakPromo::lupakan();
        PromoAngkatan::lupakan();

        return PendaftaranScopusCamp::where('kategori_id', $a->getKey())
            ->whereNotIn('id', $sebelum)
            ->first();
    }

    #[Test]
    public function promo_berkode_tercatat_di_pendaftarannya(): void
    {
        $a = $this->angkatan();
        $promo = $this->promo($a);

        $p = $this->daftar($a, 'JEJAKUJI');

        $this->assertSame(500000, (int) $p->nominal_diskon, 'Potongannya tidak masuk.');

        $this->assertSame($promo->getKey(), $p->promo_id,
            'Promo yang dipakai lewat kode tidak tercatat di pendaftarannya; '
            . 'kuotanya tidak akan pernah berkurang dan layar Dampak Promo '
            . 'melaporkannya tidak pernah dipakai siapa pun.');
    }

    #[Test]
    public function kuotanya_benar_benar_berkurang_lalu_habis(): void
    {
        /*
         * Yang dijaga AKIBATNYA, bukan cuma kolomnya: promo berkuota 1
         * harus menolak pemakai kedua. Tanpa itu, "khusus 50 orang pertama"
         * bisa dipakai lima ratus orang.
         */
        $a = $this->angkatan();
        $promo = $this->promo($a, ['kuota' => 1]);

        $this->daftar($a, 'JEJAKUJI');

        $this->assertSame(1, $promo->fresh()->terpakai, 'Pemakaiannya tidak terhitung.');
        $this->assertTrue($promo->fresh()->kuotaHabis(), 'Kuotanya tidak pernah habis.');

        $kedua = $this->daftar($a, 'JEJAKUJI');

        $this->assertSame(0, (int) $kedua->nominal_diskon,
            'Pendaftar kedua masih dapat potongan padahal kuotanya tinggal satu.');
    }

    #[Test]
    public function promo_otomatis_tetap_tercatat_seperti_sebelumnya(): void
    {
        /*
         * Yang lama tidak boleh ikut rusak saat yang berkode ditambahkan.
         */
        $a = $this->angkatan();
        $promo = $this->promo($a, ['kode' => null]);

        $p = $this->daftar($a, null);

        $this->assertSame($promo->getKey(), $p->promo_id,
            'Promo otomatis berhenti tercatat.');
    }
}
