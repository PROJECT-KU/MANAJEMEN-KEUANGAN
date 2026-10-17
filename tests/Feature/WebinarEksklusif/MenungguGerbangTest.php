<?php

namespace Tests\Feature\WebinarEksklusif;

use App\KategoriLayanan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Yang membayar lewat gerbang pembayaran MENUNGGU, bukan disuruh bayar lagi.
 *
 * Pemberitahuan DOKU berjalan dari peladen ke peladen, dan hampir selalu
 * kalah cepat dari peramban yang cuma berpindah halaman. Akibatnya orang
 * mendarat di halaman status tepat sesudah membayar dan membaca "belum
 * dibayar" — lalu sebagian membayar untuk kedua kalinya.
 *
 * Halaman statusnya kini menunggu dan bertanya sendiri ke peladen sampai
 * kabarnya datang, lalu berubah jadi lunas atau gagal. Alurnya sudah
 * dibuktikan utuh di peramban sungguhan 17 Okt 2026: panel menunggu tampil,
 * status diubah jadi lunas, halamannya berubah sendiri tanpa disentuh.
 */
class MenungguGerbangTest extends TestCase
{
    use DatabaseTransactions;

    private function sesi(): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Tunggu ' . Str::random(5),
            'nama_ke' => (string) random_int(7000, 7999),
            'mulai' => now()->addDays(14)->toDateString(),
            'selesai' => now()->addDays(14)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Pemateri Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '20',
            'biaya' => '129000', 'status' => 'active',
        ]);
    }

    private function pendaftaran(array $ubah = []): WebinarEksklusifPendaftaran
    {
        return WebinarEksklusifPendaftaran::create(array_merge([
            'id_transaksi' => 'WE-UJI-' . Str::random(6),
            'kategori_id' => $this->sesi()->getKey(),
            'nama' => 'Pendaftar Uji',
            'email' => 'uji' . Str::random(5) . '@contoh.test',
            'telp' => '6281234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '130289',
            'kode_unik' => '1289',
            'cara_bayar' => 'doku',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHour(),
        ], $ubah));
    }

    // ------------------------------------------------- alamat keadaan

    #[Test]
    public function keadaan_memulangkan_belum_selesai_selama_masih_menunggu(): void
    {
        $p = $this->pendaftaran();

        $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))
            ->assertOk()
            ->assertJson(['ada' => true, 'lunas' => false, 'selesai' => false]);
    }

    #[Test]
    public function keadaan_memulangkan_selesai_begitu_lunas(): void
    {
        $p = $this->pendaftaran(['status' => 'paid']);

        $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))
            ->assertOk()
            ->assertJson(['lunas' => true, 'selesai' => true]);
    }

    #[Test]
    public function keadaan_memulangkan_selesai_saat_kedaluwarsa(): void
    {
        /*
         * "selesai" berarti halamannya berhenti bertanya. Kedaluwarsa juga
         * selesai — tanpa itu halaman yang ditinggal terbuka akan bertanya
         * selamanya, dan tiap pertanyaan satu proses peladen.
         */
        $p = $this->pendaftaran(['status' => 'expired']);

        $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))
            ->assertOk()
            ->assertJson(['habis' => true, 'selesai' => true]);
    }

    #[Test]
    public function keadaan_tidak_membocorkan_apa_pun(): void
    {
        /*
         * Alamat ini tanpa sesi: siapa pun yang memegang tautannya bisa
         * memanggilnya. Jadi ia tidak boleh memulangkan apa pun yang belum
         * terlihat di halaman itu sendiri — nama, email, maupun nominal.
         */
        $p = $this->pendaftaran(['nama' => 'Budi Rahasia', 'email' => 'rahasia@contoh.test']);

        $isi = $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))
            ->assertOk()->json();

        $this->assertSame(['ada', 'lunas', 'batal', 'habis', 'selesai'], array_keys($isi));
    }

    #[Test]
    public function keadaan_pendaftaran_yang_tidak_ada_menjawab_404(): void
    {
        $this->getJson(route('public.webinareksklusif.keadaan', (string) Str::uuid()))
            ->assertNotFound()
            ->assertJson(['ada' => false]);
    }

    // ------------------------------------------------- panel menunggu

    #[Test]
    public function panel_menunggu_tampil_untuk_yang_bayar_lewat_gerbang(): void
    {
        $p = $this->pendaftaran();

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertSee('Menunggu konfirmasi pembayaran')
            ->assertSee(route('public.webinareksklusif.keadaan', $p->getKey()));
    }

    #[Test]
    public function panel_menunggu_tidak_tampil_untuk_transfer_manual(): void
    {
        /*
         * Yang transfer manual TIDAK sedang menunggu apa pun dari gerbang
         * pembayaran — ia justru masih harus mengirim uang dan mengunggah
         * bukti. Panel menunggu di sana akan membuatnya duduk diam menanti
         * kabar yang tidak akan pernah datang.
         */
        $p = $this->pendaftaran(['cara_bayar' => 'transfer']);

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertDontSee('Menunggu konfirmasi pembayaran');
    }

    #[Test]
    public function panel_menunggu_hilang_begitu_lunas(): void
    {
        $p = $this->pendaftaran(['status' => 'paid']);

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertSee('sudah lunas')
            ->assertDontSee('Menunggu konfirmasi pembayaran');
    }

    #[Test]
    public function panel_menunggu_hilang_saat_kedaluwarsa(): void
    {
        $p = $this->pendaftaran(['status' => 'expired']);

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertDontSee('Menunggu konfirmasi pembayaran');
    }
}
