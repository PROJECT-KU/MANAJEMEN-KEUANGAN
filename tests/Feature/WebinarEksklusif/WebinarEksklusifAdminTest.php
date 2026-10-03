<?php

namespace Tests\Feature\WebinarEksklusif;

use App\ClinikScopusBiayaPersesi;
use App\KategoriLayanan;
use App\Layanan;
use App\User;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Layar panitia untuk pendaftaran Webinar Eksklusif.
 *
 * SEBELUM layar ini ada, satu-satunya yang menulis status 'paid' adalah
 * pemberitahuan balik DOKU. Selama kredensial DOKU belum diisi, semua
 * pembayaran jatuh ke transfer manual — dan tidak ada satu pun jalur yang
 * bisa menandainya lunas. Terukur di basis data lokal: 3 dari 3 pendaftaran
 * berstatus expired, nol pernah paid.
 */
class WebinarEksklusifAdminTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Layanan::lupakanKatalog();
        KategoriLayanan::lupakanPendaftar();
        ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();
    }

    private function panitia(): User
    {
        $panitia = User::create([
            'full_name' => 'Panitia Uji',
            'username' => 'panitia-' . Str::random(6),
            'email' => 'panitia-' . Str::random(5) . '@contoh.test',
            'password' => bcrypt('rahasia-uji'),
        ]);

        /*
         * email_verified_at TIDAK ada di $fillable, jadi mengirimkannya lewat
         * create() membuatnya hilang diam-diam — dan middleware
         * 'terverifikasi' mengalihkan tiap permintaannya. Disetel terpisah.
         */
        $panitia->forceFill(['email_verified_at' => now()])->save();

        return $panitia;
    }

    private function sesi(array $lain = []): KategoriLayanan
    {
        return KategoriLayanan::create(array_merge([
            'layanan' => 'webinar_eksklusif', 'token' => Str::random(30),
            'nama' => 'Sesi Admin ' . Str::random(4), 'nama_ke' => (string) random_int(6000, 6999),
            'mulai' => now()->addDays(12)->toDateString(), 'selesai' => now()->addDays(12)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Pemateri', 'total_kuota' => '20', 'sisa_kuota' => '18',
            'biaya' => '129000', 'status' => 'active',
        ], $lain));
    }

    private function pendaftaran(KategoriLayanan $sesi, array $lain = []): WebinarEksklusifPendaftaran
    {
        return WebinarEksklusifPendaftaran::create(array_merge([
            'kategori_id' => $sesi->id, 'nama' => 'Pendaftar', 'email' => 'p-' . Str::random(5) . '@contoh.test',
            'telp' => '628123456789', 'jumlah_pendaftar' => 2, 'total_pembayaran' => '258000',
            'cara_bayar' => 'transfer', 'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHours(20),
        ], $lain));
    }

    #[Test]
    public function layarnya_tertutup_untuk_tamu(): void
    {
        $this->get(route('account.webinarpendaftar.index'))->assertRedirect();
    }

    #[Test]
    public function panitia_melihat_daftar_pendaftaran(): void
    {
        $sesi = $this->sesi();
        $p = $this->pendaftaran($sesi, ['nama' => 'Siti Pendaftar']);

        $this->actingAs($this->panitia())
            ->get(route('account.webinarpendaftar.index'))
            ->assertOk()
            ->assertSee('Siti Pendaftar', false)
            ->assertSee($p->id_transaksi, false);
    }

    /**
     * INI yang menutup lubang terbesarnya: transfer manual akhirnya punya
     * jalur untuk ditandai lunas.
     */
    #[Test]
    public function panitia_bisa_menandai_lunas(): void
    {
        $sesi = $this->sesi();
        $p = $this->pendaftaran($sesi);

        $this->actingAs($this->panitia())
            ->post(route('account.webinarpendaftar.lunasi', $p->getKey()))
            ->assertRedirect();

        $segar = $p->fresh();

        $this->assertSame('paid', $segar->status);
        $this->assertNotNull($segar->bayar_pada);
        $this->assertStringContainsString('Panitia Uji', (string) $segar->note,
            'siapa yang menandai harus tercatat — kalau ada selisih uang, yang ditanya orangnya');
    }

    /**
     * Kuota TIDAK dipotong lagi saat dilunasi: sudah dipotong saat orangnya
     * menekan "Daftar". Memotongnya sekali lagi menghilangkan kursi yang
     * sebenarnya masih ada.
     */
    #[Test]
    public function melunasi_tidak_memotong_kuota_dua_kali(): void
    {
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '18']);
        $p = $this->pendaftaran($sesi);

        $this->actingAs($this->panitia())
            ->post(route('account.webinarpendaftar.lunasi', $p->getKey()));

        $this->assertSame('18', (string) $sesi->fresh()->sisa_kuota);
    }

    /**
     * Panitia sering baru sempat memeriksa bukti SESUDAH batas waktunya
     * lewat. Tanpa ini, orang yang sudah membayar justru kehilangan
     * tempatnya.
     */
    #[Test]
    public function melunasi_yang_sudah_kedaluwarsa_mengambil_kursinya_kembali(): void
    {
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $p = $this->pendaftaran($sesi, [
            'status' => 'expired',
            'kedaluwarsa_pada' => now()->subHours(3),
        ]);

        $this->actingAs($this->panitia())
            ->post(route('account.webinarpendaftar.lunasi', $p->getKey()));

        $this->assertSame('paid', $p->fresh()->status);
        $this->assertSame('18', (string) $sesi->fresh()->sisa_kuota,
            'kursinya diambil kembali, 20 - 2 peserta');
    }

    #[Test]
    public function melunasi_dua_kali_tidak_mengubah_apa_pun(): void
    {
        $sesi = $this->sesi(['sisa_kuota' => '18']);
        $p = $this->pendaftaran($sesi);

        $panitia = $this->panitia();

        $this->actingAs($panitia)->post(route('account.webinarpendaftar.lunasi', $p->getKey()));
        $this->actingAs($panitia)->post(route('account.webinarpendaftar.lunasi', $p->getKey()));

        $this->assertSame('18', (string) $sesi->fresh()->sisa_kuota);
        $this->assertSame(1, substr_count((string) $p->fresh()->note, 'Dilunasi manual'));
    }

    #[Test]
    public function membatalkan_mengembalikan_kursinya(): void
    {
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '18']);
        $p = $this->pendaftaran($sesi);

        $this->actingAs($this->panitia())
            ->post(route('account.webinarpendaftar.batalkan', $p->getKey()))
            ->assertRedirect();

        $this->assertSame('cancel', $p->fresh()->status);
        $this->assertSame('20', (string) $sesi->fresh()->sisa_kuota);
    }

    #[Test]
    public function membatalkan_dua_kali_tidak_mengembalikan_kursi_dua_kali(): void
    {
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '18']);
        $p = $this->pendaftaran($sesi);

        $panitia = $this->panitia();

        $this->actingAs($panitia)->post(route('account.webinarpendaftar.batalkan', $p->getKey()));
        $this->actingAs($panitia)->post(route('account.webinarpendaftar.batalkan', $p->getKey()));

        $this->assertSame('20', (string) $sesi->fresh()->sisa_kuota,
            'tidak boleh melebihi yang dikembalikan sekali');
    }

    #[Test]
    public function pencarian_menemukan_dari_nomor_pendaftaran(): void
    {
        $sesi = $this->sesi();
        $p = $this->pendaftaran($sesi, ['nama' => 'Khusus Dicari']);
        $this->pendaftaran($sesi, ['nama' => 'Tidak Dicari']);

        $this->actingAs($this->panitia())
            ->get(route('account.webinarpendaftar.index', ['cari' => $p->id_transaksi]))
            ->assertOk()
            ->assertSee('Khusus Dicari', false)
            ->assertDontSee('Tidak Dicari', false);
    }

    /** Nama peserta rombongan tampil — itu yang dipakai menerbitkan sertifikat. */
    #[Test]
    public function nama_peserta_rombongan_tampil_di_layar_panitia(): void
    {
        $sesi = $this->sesi();
        $p = $this->pendaftaran($sesi, ['jumlah_pendaftar' => 3]);

        $p->pesertaLain()->create(['urutan' => 0, 'nama' => 'Anggota Dua']);
        $p->pesertaLain()->create(['urutan' => 1, 'nama' => 'Anggota Tiga']);

        $this->actingAs($this->panitia())
            ->get(route('account.webinarpendaftar.index'))
            ->assertSee('Anggota Dua', false)
            ->assertSee('Anggota Tiga', false);
    }
}
