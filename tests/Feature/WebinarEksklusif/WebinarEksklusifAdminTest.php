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
        /*
         * Perannya disetel ORANG DALAM. Dengan peran bawaan 'user', ujinya
         * tetap hijau padahal layarnya tidak terjaga sama sekali — persis
         * yang sempat terjadi sebelum penjagaan ditambahkan.
         */
        $panitia->forceFill([
            'email_verified_at' => now(),
            'peran' => 'administrator',
        ])->save();

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
        $this->get(route('account.pendaftaran-layanan.index', ['layanan' => 'webinar_eksklusif']))->assertRedirect();
    }

    #[Test]
    public function panitia_melihat_daftar_pendaftaran(): void
    {
        $sesi = $this->sesi();
        $p = $this->pendaftaran($sesi, ['nama' => 'Siti Pendaftar']);

        $this->actingAs($this->panitia())
            ->get(route('account.pendaftaran-layanan.index', ['layanan' => 'webinar_eksklusif']))
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
            ->post(route('account.pendaftaran-layanan.status', ['webinar_eksklusif', $p->getKey()]), ['status' => 'paid'])
            ->assertRedirect();

        $segar = $p->fresh();

        $this->assertSame('paid', $segar->status);
        $this->assertNotNull($segar->bayar_pada);
        /*
         * Jejaknya di tabelnya sendiri, BUKAN di kolom catatan: jejak sistem
         * tidak boleh menumpang di kolom yang disunting panitia.
         */
        $jejak = \App\PendaftaranJejak::milik('webinar_eksklusif', (string) $p->getKey())
            ->terurut()->get();

        $this->assertCount(1, $jejak, 'perpindahan statusnya harus tercatat');
        $this->assertSame('pending', $jejak[0]->dari);
        $this->assertSame('paid', $jejak[0]->ke);
        $this->assertSame('Panitia Uji', $jejak[0]->oleh_nama,
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
            ->post(route('account.pendaftaran-layanan.status', ['webinar_eksklusif', $p->getKey()]), ['status' => 'paid']);

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
            ->post(route('account.pendaftaran-layanan.status', ['webinar_eksklusif', $p->getKey()]), ['status' => 'paid']);

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

        $this->actingAs($panitia)->post(route('account.pendaftaran-layanan.status', ['webinar_eksklusif', $p->getKey()]), ['status' => 'paid']);
        $this->actingAs($panitia)->post(route('account.pendaftaran-layanan.status', ['webinar_eksklusif', $p->getKey()]), ['status' => 'paid']);

        $this->assertSame('18', (string) $sesi->fresh()->sisa_kuota);
        // Perpindahan kedua ditolak sebab statusnya sudah sama, jadi jejaknya
        // tetap SATU baris — bukan dua yang isinya sama.
        $this->assertSame(1, \App\PendaftaranJejak::milik('webinar_eksklusif', (string) $p->getKey())->count());
    }

    #[Test]
    public function membatalkan_mengembalikan_kursinya(): void
    {
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '18']);
        $p = $this->pendaftaran($sesi);

        $this->actingAs($this->panitia())
            ->post(route('account.pendaftaran-layanan.status', ['webinar_eksklusif', $p->getKey()]), ['status' => 'cancel'])
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

        $this->actingAs($panitia)->post(route('account.pendaftaran-layanan.status', ['webinar_eksklusif', $p->getKey()]), ['status' => 'cancel']);
        $this->actingAs($panitia)->post(route('account.pendaftaran-layanan.status', ['webinar_eksklusif', $p->getKey()]), ['status' => 'cancel']);

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
            ->get(route('account.pendaftaran-layanan.index', ['layanan' => 'webinar_eksklusif', 'cari' => $p->id_transaksi]))
            ->assertOk()
            ->assertSee('Khusus Dicari', false)
            ->assertDontSee('Tidak Dicari', false);
    }

    /**
     * Nama peserta rombongan tampil — itu yang dipakai menerbitkan sertifikat.
     *
     * Pindah dari daftar ke halaman RINCIAN sejak layar pendaftar webinar
     * dibuang: daftar terpadu memuat lima layanan, dan menumpuk nama peserta
     * di dalam selnya membuat barisnya setinggi rombongan terbesar. Yang
     * dijaga di sini namanya masih bisa dilihat di suatu tempat — kalau tidak,
     * sertifikat peserta kedua dan seterusnya tidak bisa diterbitkan.
     */
    #[Test]
    public function nama_peserta_rombongan_tampil_di_halaman_rincian(): void
    {
        $sesi = $this->sesi();
        $p = $this->pendaftaran($sesi, ['jumlah_pendaftar' => 3]);

        $p->pesertaLain()->create(['urutan' => 0, 'nama' => 'Anggota Dua']);
        $p->pesertaLain()->create(['urutan' => 1, 'nama' => 'Anggota Tiga']);

        $this->actingAs($this->panitia())
            ->get(route('account.pendaftaran-layanan.rincian', ['webinar_eksklusif', $p->getKey()]))
            ->assertOk()
            ->assertSee('Anggota Dua', false)
            ->assertSee('Anggota Tiga', false);
    }

    /**
     * Jumlah nama yang kurang dari yang dibayar DISEBUT.
     *
     * Pendaftaran yang dibayar untuk tiga orang tetapi hanya memuat dua nama
     * berarti satu sertifikat tidak bisa diterbitkan — dan tanpa kalimat itu
     * hal ini baru ketahuan di hari acara.
     */
    #[Test]
    public function selisih_jumlah_peserta_disebut_di_rincian(): void
    {
        $sesi = $this->sesi();
        $p = $this->pendaftaran($sesi, ['jumlah_pendaftar' => 3]);
        $p->pesertaLain()->create(['urutan' => 0, 'nama' => 'Anggota Dua']);

        $this->actingAs($this->panitia())
            ->get(route('account.pendaftaran-layanan.rincian', ['webinar_eksklusif', $p->getKey()]))
            ->assertOk()
            ->assertSee('nama yang tercatat', false);
    }

    /**
     * Layarnya harus BISA DITEMUKAN dari menu.
     *
     * Tanpa entri menu, ia hanya bisa dibuka dengan mengetik alamatnya — dan
     * satu-satunya cara menandai transfer manual jadi lunas praktis tidak
     * bisa ditemukan siapa pun. Kerusakan yang tidak menampakkan diri:
     * layarnya ada, ujinya hijau, tetapi tak seorang pun memakainya.
     */
    #[Test]
    public function layarnya_ada_di_menu_samping(): void
    {
        /*
         * Diperiksa dari HALAMAN LAIN, bukan dari layarnya sendiri: layar
         * pendaftar memuat alamatnya sendiri di borang saringannya, jadi
         * memeriksanya di sana tetap hijau walau entri menunya dicabut —
         * sempat terjadi pada uji ini sebelum diperbaiki.
         *
         * Entri "Pendaftar Webinar" sendiri DIBUANG 3 Okt 2026: layar
         * Pendaftar Layanan memuat kelima layanan sekaligus, jadi satu entri
         * sudah mencakupnya. Yang dijaga di sini pendaftar webinar masih bisa
         * DITEMUKAN dari menu — lewat entri terpadunya.
         */
        $this->actingAs($this->panitia())
            ->get(route('account.galeri.index'))
            ->assertOk()
            ->assertSee(route('account.pendaftaran-layanan.index'), false)
            ->assertSee('Pendaftar Layanan', false)
            ->assertDontSee('Pendaftar Webinar', false);
    }

    /**
     * Pelanggan biasa TIDAK boleh membuka layar ini, apalagi menandai
     * pembayaran orang lain lunas.
     *
     * Grup rute account/ hanya bermiddleware auth + terverifikasi, jadi tanpa
     * penjagaan di pengendalinya layar ini terbuka untuk siapa pun yang punya
     * akun — terukur 200 sebelum diperbaiki.
     */
    #[Test]
    public function pelanggan_biasa_ditolak(): void
    {
        $sesi = $this->sesi();
        $p = $this->pendaftaran($sesi);

        $pelanggan = User::create([
            'full_name' => 'Pelanggan Biasa',
            'username' => 'pel-' . Str::random(6),
            'email' => 'pel-' . Str::random(5) . '@contoh.test',
            'password' => bcrypt('rahasia-uji'),
            'peran' => 'user',
        ]);
        $pelanggan->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($pelanggan)
            ->get(route('account.pendaftaran-layanan.index', ['layanan' => 'webinar_eksklusif']))
            ->assertRedirect();

        $this->actingAs($pelanggan)
            ->post(route('account.pendaftaran-layanan.status', ['webinar_eksklusif', $p->getKey()]), ['status' => 'paid'])
            ->assertRedirect();

        $this->assertSame('pending', $p->fresh()->status,
            'pelanggan tidak boleh bisa menandai pembayaran lunas');

        $this->actingAs($pelanggan)
            ->post(route('account.pendaftaran-layanan.status', ['webinar_eksklusif', $p->getKey()]), ['status' => 'cancel']);

        $this->assertSame('pending', $p->fresh()->status,
            'pelanggan tidak boleh bisa membatalkan pendaftaran orang lain');
    }
}
