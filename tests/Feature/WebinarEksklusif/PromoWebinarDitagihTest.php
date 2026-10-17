<?php

namespace Tests\Feature\WebinarEksklusif;

use App\KategoriLayanan;
use App\PromoLayanan;
use App\Support\PromoAngkatan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Webinar Eksklusif menagih harga promo, bukan harga penuh.
 *
 * Tiga layar menyebut angka untuk satu sesi — layar MIS, halaman landing di
 * subdomain, dan borang pendaftaran ini — dan ketiganya harus menyebut angka
 * yang sama.
 *
 * 15 Okt 2026 borang inilah satu-satunya yang tertinggal. Admin memasang
 * Promo Layanan Rp 21.000; MIS dan halaman landing turun jadi Rp 129.000,
 * borang ini tetap menagih Rp 150.000 karena membaca kolom `biaya`. Tidak
 * ada galat apa pun — pendaftar hanya membayar Rp 21.000 lebih mahal per
 * kursi daripada yang dijanjikan kepadanya.
 *
 * Perkara yang SAMA dengan Scopus Camp dan Analisis Bibliometrik seminggu
 * sebelumnya; kedua layanan itu sudah dipindahkan ke harga_setelah_promo,
 * Webinar Eksklusif terlewat.
 */
class PromoWebinarDitagihTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        PromoAngkatan::lupakan();
    }

    private function sesi(array $lain = []): KategoriLayanan
    {
        return KategoriLayanan::create(array_merge([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Promo ' . Str::random(5),
            'nama_ke' => (string) random_int(7000, 7999),
            'mulai' => now()->addDays(14)->toDateString(),
            'selesai' => now()->addDays(14)->toDateString(),
            'jam_mulai' => '09:30',
            'jam_selesai' => '11:30',
            'platform' => 'Zoom',
            'pemateri' => 'Pemateri Uji',
            'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'biaya' => '150000',
            'status' => 'active',
        ], $lain));
    }

    private function promo(KategoriLayanan $sesi, array $ubah = []): PromoLayanan
    {
        $promo = PromoLayanan::create(array_merge([
            'nama' => 'Promo Uji ' . Str::random(4),
            'tipe' => 'nominal',
            'nominal' => '21000',
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDays(20)->toDateString(),
            'aktif' => true,
        ], $ubah));

        $promo->angkatan()->attach($sesi->getKey());

        PromoAngkatan::lupakan();

        return $promo;
    }

    private function daftarkan(KategoriLayanan $sesi, int $jumlah = 1): WebinarEksklusifPendaftaran
    {
        $sebelumnya = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)
            ->pluck('id')->all();

        $peserta = [];

        for ($i = 2; $i <= $jumlah; $i++) {
            $peserta[] = ['nama' => 'Peserta ' . $i];
        }

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id,
            'nama' => 'Budi Santoso',
            'email' => 'budi' . Str::random(5) . '@contoh.test',
            'telp' => '0812-3456-' . random_int(1000, 9999),
            'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => $jumlah,
            'peserta' => $peserta,
            'setuju' => '1',
        ])->assertRedirect();

        /*
         * Diambil dari SELISIH kumpulan id, bukan latest('id').
         *
         * Kunci utamanya UUID, jadi latest('id') mengurutkan menurut abjad
         * UUID — bukan urutan pembuatan — dan memulangkan baris sembarang
         * begitu ada lebih dari satu pendaftaran di sesi yang sama. Di sini
         * baru satu, jadi selama ini benar karena kebetulan.
         */
        $baru = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)
            ->whereNotIn('id', $sebelumnya)
            ->get();

        $this->assertCount(1, $baru, 'Pendaftarannya tidak bertambah tepat satu.');

        return $baru->first();
    }

    #[Test]
    public function yang_ditagih_harga_promo_bukan_harga_penuh(): void
    {
        $sesi = $this->sesi();
        $this->promo($sesi);

        $p = $this->daftarkan($sesi);

        /*
         * Kode unik ikut DITAMBAHKAN ke tagihan untuk mencocokkan transfer,
         * jadi yang diperiksa dasarnya: total dikurangi kode uniknya.
         */
        $dasar = (int) $p->total_pembayaran - (int) $p->kode_unik;

        $this->assertSame(129000, $dasar, sprintf(
            'Satu kursi ditagih Rp %s; harga promonya Rp 129.000. Pendaftar '
            . 'membayar lebih mahal daripada yang dijanjikan di layar.',
            number_format($dasar, 0, ',', '.')));
    }

    #[Test]
    public function promonya_dikalikan_jumlah_kursi(): void
    {
        $sesi = $this->sesi();
        $this->promo($sesi);

        $p = $this->daftarkan($sesi, 3);

        $dasar = (int) $p->total_pembayaran - (int) $p->kode_unik;

        $this->assertSame(129000 * 3, $dasar,
            'Potongannya tidak dikalikan jumlah kursi; pesanan tiga kursi '
            . 'hanya dapat satu kali potongan.');
    }

    #[Test]
    public function tali_ke_promonya_ikut_tersimpan(): void
    {
        $sesi = $this->sesi();
        $promo = $this->promo($sesi);

        $p = $this->daftarkan($sesi);

        $this->assertSame($promo->getKey(), $p->promo_id,
            'Pendaftarannya tidak menyimpan promo yang dipakainya; layar '
            . 'dampak promo akan menghitungnya nol padahal dipakai.');
    }

    #[Test]
    public function borangnya_memajang_harga_promo(): void
    {
        $sesi = $this->sesi();
        $this->promo($sesi);

        $this->get(route('public.webinareksklusif.daftar', $sesi->id))
            ->assertOk()
            ->assertSee('Rp 129.000')
            ->assertDontSee('Rp 150.000');
    }

    #[Test]
    public function daftar_sesi_di_halaman_umum_memajang_harga_promo(): void
    {
        /*
         * Kartu di halaman daftar dan angka di borangnya harus sama. Beda
         * sedikit saja, pengunjung melihat harganya "berubah sendiri" saat
         * mengklik — dan tidak ada apa pun di layar yang menjelaskan kenapa.
         */
        $sesi = $this->sesi();
        $this->promo($sesi);

        $this->get(route('public.webinareksklusif.index'))
            ->assertOk()
            ->assertSee('Rp 129.000')
            ->assertSee('Rp 150.000');   // dicoret, bukan hilang
    }

    #[Test]
    public function tanpa_promo_harganya_tetap_penuh(): void
    {
        /*
         * Penjaga arah sebaliknya: perbaikan ini tidak boleh diam-diam
         * memotong harga sesi yang memang tidak punya promo.
         */
        $sesi = $this->sesi();

        $p = $this->daftarkan($sesi);

        $dasar = (int) $p->total_pembayaran - (int) $p->kode_unik;

        $this->assertSame(150000, $dasar);
        $this->assertNull($p->promo_id);
    }
}
