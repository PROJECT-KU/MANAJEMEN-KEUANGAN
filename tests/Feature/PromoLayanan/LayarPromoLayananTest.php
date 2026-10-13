<?php

namespace Tests\Feature\PromoLayanan;

use App\KategoriLayanan;
use App\PendaftaranScopusCamp;
use App\PromoLayanan;
use App\Support\PromoAngkatan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Layar promo layanan: daftar, borang, saklar, dan hapusnya.
 *
 * Menggantikan layar promo Clinik Scopus yang hanya melayani satu layanan.
 * Yang dijaga di sini termasuk hal yang dulu tidak dijaga sama sekali: hak
 * akses (grup rute account/ TIDAK memasang middleware peran, jadi pengendali
 * yang lupa memeriksanya terbuka untuk pelanggan).
 */
class LayarPromoLayananTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        PromoAngkatan::lupakan();
    }

    // ------------------------------------------------------------ hak akses

    #[Test]
    public function pelanggan_tidak_bisa_membuka_layar_promo(): void
    {
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        foreach ([
            route('account.promo-layanan.index'),
            route('account.promo-layanan.create'),
        ] as $alamat) {
            $this->actingAs($pelanggan)->get($alamat)
                ->assertRedirect(route('account.dashboard.index'));
        }
    }

    #[Test]
    public function pelanggan_tidak_bisa_menyimpan_promo(): void
    {
        /*
         * Diperiksa terpisah dari layarnya. Menyembunyikan tombolnya saja
         * menyisakan alamat POST yang masih bisa dipanggil langsung, dan
         * itulah lubang yang sebenarnya.
         */
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        $this->actingAs($pelanggan)->post(route('account.promo-layanan.store'), [
            'nama' => 'Promo Selundupan', 'tipe' => 'persen', 'persen' => 90,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
        ])->assertRedirect(route('account.dashboard.index'));

        $this->assertSame(0, PromoLayanan::where('nama', 'Promo Selundupan')->count());
    }

    #[Test]
    public function orang_dalam_bisa_membuka_layarnya(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->get(route('account.promo-layanan.index'))
            ->assertOk()->assertSee('Promo Layanan');

        $this->actingAs($admin)->get(route('account.promo-layanan.create'))
            ->assertOk()->assertSee('Angkatan mana yang dapat promo?');
    }

    // --------------------------------------------------------------- daftar

    #[Test]
    public function daftarnya_menampilkan_promo_beserta_potongan_dan_periodenya(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        PromoLayanan::create([
            'nama' => 'Promo Terbaca ' . Str::random(4), 'tipe' => 'persen', 'persen' => 35,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDays(5)->toDateString(), 'aktif' => true,
        ]);

        $this->actingAs($admin)->get(route('account.promo-layanan.index'))
            ->assertOk()
            ->assertSee('35%')
            ->assertSee('Sedang berjalan');
    }

    #[Test]
    public function promo_tanpa_angkatan_ditandai_belum_dipilih(): void
    {
        /*
         * Promo tanpa angkatan tidak memotong apa pun. Ditulis "0" saja, ia
         * terbaca seperti angka biasa dan tidak ada yang memperbaikinya —
         * terukur: ketiga promo yang dipindah dari Clinik Scopus berada tepat
         * dalam keadaan itu.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        PromoLayanan::create([
            'nama' => 'Promo Melayang ' . Str::random(4), 'tipe' => 'rupiah', 'nominal' => 50_000,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $this->actingAs($admin)->get(route('account.promo-layanan.index'))
            ->assertOk()->assertSee('belum dipilih');
    }

    #[Test]
    public function saringan_keadaan_benar_benar_mempersempit_daftarnya(): void
    {
        /*
         * Keadaannya tidak ada di kolom mana pun, jadi saringannya merangkai
         * ulang syaratnya di SQL. Dikerjakan di PHP sesudah data diambil,
         * penomoran halamannya langsung salah: halaman pertama berisi 10 baris
         * lalu tersaring jadi 3, dan jumlah totalnya ikut berbohong.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $t = Str::random(5);

        PromoLayanan::create([
            'nama' => 'Jalan ' . $t, 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDay()->toDateString(), 'aktif' => true,
        ]);

        PromoLayanan::create([
            'nama' => 'Nanti ' . $t, 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->addDays(5)->toDateString(),
            'selesai' => now()->addDays(9)->toDateString(), 'aktif' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('account.promo-layanan.index', ['cari' => $t, 'keadaan' => 'berjalan']))
            ->assertOk()
            ->assertSee('Jalan ' . $t)
            ->assertDontSee('Nanti ' . $t);

        $this->actingAs($admin)
            ->get(route('account.promo-layanan.index', ['cari' => $t, 'keadaan' => 'belum-mulai']))
            ->assertOk()
            ->assertSee('Nanti ' . $t)
            ->assertDontSee('Jalan ' . $t);
    }

    #[Test]
    public function saringan_layanan_memakai_angkatan_yang_dicentang(): void
    {
        /*
         * Promo tidak punya kolom layanan — ia menempel pada angkatan, dan
         * angkatanlah yang punya layanan. Jadi saringannya harus menembus
         * tabel sambungan; disaring dari kolom yang tidak ada, menunya akan
         * selalu memulangkan nol tanpa galat.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $t = Str::random(5);

        $camp = $this->angkatan('scopus_camp');
        $biblio = $this->angkatan('bibliometrik');

        $a = PromoLayanan::create([
            'nama' => 'Camp ' . $t, 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);
        $a->angkatan()->attach($camp->getKey());

        $b = PromoLayanan::create([
            'nama' => 'Biblio ' . $t, 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);
        $b->angkatan()->attach($biblio->getKey());

        $this->actingAs($admin)
            ->get(route('account.promo-layanan.index', ['cari' => $t, 'layanan' => 'bibliometrik']))
            ->assertOk()
            ->assertSee('Biblio ' . $t)
            ->assertDontSee('Camp ' . $t);
    }

    // -------------------------------------------------------------- simpanan

    #[Test]
    public function menyimpan_promo_sekaligus_mencatat_angkatan_yang_dicentang(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $satu = $this->angkatan('scopus_camp');
        $dua = $this->angkatan('bibliometrik');

        $nama = 'Promo Tersimpan ' . Str::random(5);

        $this->actingAs($admin)->post(route('account.promo-layanan.store'), [
            'nama' => $nama, 'tipe' => 'persen', 'persen' => 25,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDays(10)->toDateString(),
            'aktif' => '1',
            'angkatan' => [$satu->getKey(), $dua->getKey()],
        ])->assertRedirect(route('account.promo-layanan.index'));

        $promo = PromoLayanan::where('nama', $nama)->firstOrFail();

        $this->assertSame(25, $promo->persen);
        $this->assertNull($promo->nominal);
        $this->assertTrue($promo->aktif);
        $this->assertSame(2, $promo->angkatan()->count());
        $this->assertSame($admin->id, (int) $promo->penginput_id);
    }

    #[Test]
    public function id_angkatan_kembar_di_kiriman_tidak_menggandakan_barisnya(): void
    {
        /*
         * Kiriman yang memuat id yang sama dua kali akan ditolak kunci unik
         * pivotnya dengan galat yang menyebut nama constraint, bukan apa yang
         * salah. Dibuang di pengendali supaya pesannya tidak pernah sampai ke
         * admin.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $satu = $this->angkatan('scopus_camp');

        $nama = 'Promo Kembar ' . Str::random(5);

        $this->actingAs($admin)->post(route('account.promo-layanan.store'), [
            'nama' => $nama, 'tipe' => 'rupiah', 'nominal' => 50_000,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'angkatan' => [$satu->getKey(), $satu->getKey(), $satu->getKey()],
        ])->assertRedirect(route('account.promo-layanan.index'));

        $promo = PromoLayanan::where('nama', $nama)->firstOrFail();

        $this->assertSame(1, $promo->angkatan()->count());
    }

    #[Test]
    public function mengubah_promo_melepaskan_centang_yang_dibatalkan(): void
    {
        /*
         * sync(), bukan attach(). Dengan attach saja, angkatan yang tadi
         * dicentang lalu dibatalkan tetap kena promo dan tidak ada cara apa
         * pun mencabutnya dari borang.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $tetap = $this->angkatan('scopus_camp');
        $dilepas = $this->angkatan('scopus_camp');

        $promo = PromoLayanan::create([
            'nama' => 'Promo Disunting ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);
        $promo->angkatan()->attach([$tetap->getKey(), $dilepas->getKey()]);

        $this->actingAs($admin)->put(route('account.promo-layanan.update', $promo->id), [
            'nama' => $promo->nama, 'tipe' => 'persen', 'persen' => 15,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => '1',
            'angkatan' => [$tetap->getKey()],
        ])->assertRedirect(route('account.promo-layanan.index'));

        $sisa = $promo->fresh()->angkatan->pluck('id')->all();

        $this->assertSame([(string) $tetap->getKey()], $sisa);
        $this->assertSame(15, $promo->fresh()->persen);
    }

    #[Test]
    public function berganti_tipe_mengosongkan_angka_tipe_yang_lama(): void
    {
        /*
         * Promo yang diubah dari rupiah ke persen akan menyimpan keduanya
         * kalau angka lamanya dibiarkan, dan kartu ringkasannya jadi menyebut
         * dua potongan berbeda untuk satu promo.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $promo = PromoLayanan::create([
            'nama' => 'Promo Bertukar ' . Str::random(4), 'tipe' => 'rupiah', 'nominal' => 80_000,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $this->actingAs($admin)->put(route('account.promo-layanan.update', $promo->id), [
            'nama' => $promo->nama, 'tipe' => 'persen', 'persen' => 20,
            'batas_potongan' => 300_000,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => '1',
        ])->assertRedirect(route('account.promo-layanan.index'));

        $segar = $promo->fresh();

        $this->assertSame('persen', $segar->tipe);
        $this->assertSame(20, $segar->persen);
        $this->assertNull($segar->nominal);
        $this->assertSame(300_000, $segar->batas_potongan);
    }

    #[Test]
    public function batas_potongan_dibuang_saat_tipenya_rupiah(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $nama = 'Promo Batas ' . Str::random(5);

        $this->actingAs($admin)->post(route('account.promo-layanan.store'), [
            'nama' => $nama, 'tipe' => 'rupiah', 'nominal' => 90_000,
            'batas_potongan' => 10_000,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
        ])->assertRedirect(route('account.promo-layanan.index'));

        $this->assertNull(PromoLayanan::where('nama', $nama)->firstOrFail()->batas_potongan);
    }

    #[Test]
    public function kotak_aktif_yang_dilepas_benar_benar_mematikan_promonya(): void
    {
        /*
         * Kotak centang yang tidak dicentang TIDAK terkirim sama sekali. Tanpa
         * medan tersembunyi bernilai 0 di depannya, "matikan promo" pada
         * borang sunting tidak pernah tersimpan — dan promonya tetap memotong
         * harga walau admin sudah mematikannya.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $promo = PromoLayanan::create([
            'nama' => 'Promo Dimatikan ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $this->actingAs($admin)->put(route('account.promo-layanan.update', $promo->id), [
            'nama' => $promo->nama, 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => '0',
        ])->assertRedirect(route('account.promo-layanan.index'));

        $this->assertFalse($promo->fresh()->aktif);
    }

    // ------------------------------------------------- satuan & format angka

    #[Test]
    public function nominal_bertitik_ribuan_tersimpan_sebagai_angka(): void
    {
        /*
         * Kotak rupiahnya memformat dirinya sambil diketik, jadi yang sampai
         * ke peladen "75.000". Tanpa pembersihan sebelum validasi, aturan
         * `integer` menolaknya dan admin melihat "potongan rupiah harus
         * berupa angka" pada kotak yang jelas-jelas berisi angka.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $nama = 'Promo Bertitik ' . Str::random(5);

        $this->actingAs($admin)->post(route('account.promo-layanan.store'), [
            'nama' => $nama, 'tipe' => 'rupiah', 'nominal' => '75.000',
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->assertSame(75_000, PromoLayanan::where('nama', $nama)->firstOrFail()->nominal);
    }

    #[Test]
    public function batas_potongan_bertitik_ikut_dibersihkan(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $nama = 'Promo Batas Bertitik ' . Str::random(5);

        $this->actingAs($admin)->post(route('account.promo-layanan.store'), [
            'nama' => $nama, 'tipe' => 'persen', 'persen' => 40,
            'batas_potongan' => 'Rp 1.000.000',
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->assertSame(1_000_000, PromoLayanan::where('nama', $nama)->firstOrFail()->batas_potongan);
    }

    #[Test]
    public function kotak_rupiah_yang_dikosongkan_tetap_null_bukan_nol(): void
    {
        /*
         * Untaian kosong yang jadi 0 akan ditolak `min:1` — galat pada medan
         * yang memang boleh dikosongkan, dan admin tidak punya cara keluar
         * selain menebak.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $nama = 'Promo Tanpa Batas ' . Str::random(5);

        $this->actingAs($admin)->post(route('account.promo-layanan.store'), [
            'nama' => $nama, 'tipe' => 'persen', 'persen' => 15,
            'batas_potongan' => '',
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->assertNull(PromoLayanan::where('nama', $nama)->firstOrFail()->batas_potongan);
    }

    #[Test]
    public function kotak_rupiah_bukan_type_number_dan_bersatuan_rp(): void
    {
        /*
         * type=number menolak memuat "75.000": peramban mengosongkan kotaknya
         * diam-diam begitu titiknya diketik, dan yang terlihat cuma angka yang
         * hilang. Dijaga dari markahnya karena gejalanya tidak meninggalkan
         * jejak apa pun di peladen.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $isi = $this->actingAs($admin)
            ->get(route('account.promo-layanan.create'))->assertOk()->getContent();

        foreach (['prf-nominal', 'prf-batas'] as $id) {
            preg_match('/<input[^>]*id="' . $id . '"[^>]*>/', $isi, $cocok);

            $this->assertNotEmpty($cocok, 'Kotak ' . $id . ' tidak ketemu di markahnya.');
            $this->assertStringContainsString('type="text"', $cocok[0],
                'Kotak ' . $id . ' kembali jadi type=number; titik ribuannya akan dibuang peramban.');
            $this->assertStringContainsString('inputmode="numeric"', $cocok[0],
                'Tanpa inputmode=numeric, ponsel memunculkan papan tik huruf untuk kotak uang.');
            $this->assertStringContainsString('data-prf-rupiah', $cocok[0],
                'Tanpa penanda itu kotaknya tidak ikut diformat sambil diketik.');
        }

        // Satuannya menempel di dalam kotaknya, bukan jadi label sendiri.
        $this->assertSame(2, substr_count($isi, '<span class="prf-tanda kiri" aria-hidden="true">Rp</span>'));
        $this->assertStringContainsString('<span class="prf-tanda kanan" aria-hidden="true">%</span>', $isi);
    }

    #[Test]
    public function borang_memakai_dua_lajur_di_layar_lebar(): void
    {
        /*
         * Dibatasi 880px, kartunya berhenti di tengah dan sisa kanannya
         * menganga — terukur ~380px kosong pada layar 1960px.
         *
         * Dua hal dijaga sekaligus, dan keduanya pernah salah:
         *
         * 1. Tidak ada lagi batas lebar pada .prf-tumpuk.
         * 2. Langkah 1-3 dibungkus SATU unsur kisi, bukan tiga unsur yang
         *    direntang grid-row: span 3. Unsur yang membentang beberapa baris
         *    menarik baris-baris itu setinggi dirinya, jadi ketiga kartu di
         *    kiri terpisah jarak yang tidak diminta siapa pun — terukur 68px
         *    dan 72px pada jarak baku 16px.
         */
        $berkas = file_get_contents(
            base_path('resources/views/account/promo_layanan/form.blade.php')
        );

        preg_match('/\.prf-tumpuk\s*\{([^}]*)\}/', $berkas, $dasar);

        $this->assertNotEmpty($dasar, 'Aturan .prf-tumpuk tidak ketemu.');
        $this->assertStringNotContainsString('max-width', $dasar[1],
            'Batas lebar kembali dipasang; sisi kanan layar lebar akan menganga lagi.');

        $this->assertMatchesRegularExpression(
            '/@media \(min-width: 992px\)\s*\{\s*\.prf-tumpuk\s*\{[^}]*grid-template-columns:\s*minmax\(0, 1fr\) minmax\(0, 1fr\)/',
            $berkas,
            'Dua lajurnya hilang; borangnya kembali menurun satu lajur di layar lebar.'
        );

        $this->assertStringNotContainsString('grid-row: 1 / span 3', $berkas,
            'Langkah 4 kembali direntang beberapa baris; jarak antar kartu di kiri akan melar sendiri.');

        // Pembungkus lajur kirinya memang ada, dan memuat TIGA kartu.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $isi = $this->actingAs($admin)
            ->get(route('account.promo-layanan.create'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($isi, 'class="prf-lajur"'));

        /*
         * Batasnya nomor langkah 4, bukan komentar Blade penutupnya: komentar
         * Blade dibuang saat dirender, jadi mencarinya di halaman selalu gagal
         * — penjaga yang merah untuk alasan yang salah. Bukan pula JUDUL
         * langkah 4: judulnya berada DI DALAM blok .prf-langkah miliknya, jadi
         * potongannya ikut membawa satu .prf-langkah yang bukan haknya.
         */
        preg_match('/class="prf-lajur">(.*?)class="prf-nomor" aria-hidden="true">4</s', $isi, $kiri);

        $this->assertNotEmpty($kiri, 'Pembungkus lajur kiri tidak ketemu di markahnya.');
        /*
         * Yang dihitung NOMOR langkahnya, bukan blok .prf-langkah-nya.
         * Penanda batas di atas berada DI DALAM blok .prf-langkah milik
         * langkah 4, jadi potongannya selalu ikut membawa satu blok yang
         * bukan haknya — terukur 4, bukan 3.
         */
        $this->assertSame(3, substr_count($kiri[1], 'class="prf-nomor"'),
            'Lajur kiri tidak lagi memuat tepat tiga langkah.');
    }

    #[Test]
    public function tanda_satuan_ditambat_ke_pembungkus_kotaknya(): void
    {
        /*
         * Ditambat ke .mis-isian, tandanya meleset: tiap kotak di borang ini
         * punya teks bantuan di bawahnya, jadi dasar .mis-isian bukan dasar
         * kotaknya. Terukur sebelum dibetulkan: tanda 409-451 sementara
         * kotaknya 402-442 — meleset 7px ke bawah pada KETIGA medan.
         *
         * Yang dijaga bentuk penambatannya, bukan angkanya: `top: 20px` yang
         * kebetulan pas hari ini akan meleset lagi begitu ukuran labelnya
         * berubah, dan tidak ada galat yang memberi tahu.
         */
        $berkas = file_get_contents(
            base_path('resources/views/account/promo_layanan/form.blade.php')
        );

        $this->assertMatchesRegularExpression(
            '/\.prf-kotak\s*\{[^}]*position:\s*relative/',
            $berkas,
            'Pembungkus kotaknya tidak lagi relative; tanda Rp/% akan menambat ke .mis-isian dan meleset.'
        );

        preg_match('/\.prf-tanda\s*\{([^}]*)\}/', $berkas, $aturan);

        $this->assertNotEmpty($aturan, 'Aturan .prf-tanda tidak ketemu.');
        $this->assertStringContainsString('top: 0', $aturan[1]);
        $this->assertStringContainsString('bottom: 0', $aturan[1]);
        $this->assertStringNotContainsString('height:', $aturan[1],
            'Tinggi tetap membuat tandanya meleset begitu tinggi kotaknya berubah; biarkan inset yang menentukan.');
        $this->assertStringContainsString('pointer-events: none', $aturan[1],
            'Tanpa ini, menekan tepat di atas "Rp" tidak memfokuskan kotaknya.');

        // Ketiga kotaknya memang dibungkus, bukan cuma gayanya yang ada.
        $this->assertSame(3, substr_count($berkas, '<span class="prf-kotak">'));
    }

    #[Test]
    public function nilai_tersimpan_dipajang_bertitik_saat_disunting(): void
    {
        /*
         * Tersimpan 75000 dan dipajang "75000", kotaknya terbaca berbeda dari
         * saat diketik — dan admin yang menyimpan ulang tanpa menyentuhnya
         * melihat angkanya "berubah" sendiri.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $promo = PromoLayanan::create([
            'nama' => 'Promo Terpajang ' . Str::random(4), 'tipe' => 'rupiah', 'nominal' => 125_000,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $this->actingAs($admin)->get(route('account.promo-layanan.edit', $promo->id))
            ->assertOk()
            ->assertSee('value="125.000"', false);
    }

    // ------------------------------------------------------------- validasi

    #[Test]
    public function tanggal_selesai_sebelum_mulai_ditolak(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->post(route('account.promo-layanan.store'), [
            'nama' => 'Promo Terbalik', 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->addDays(5)->toDateString(),
            'selesai' => now()->toDateString(),
        ])->assertSessionHasErrors('selesai');

        $this->assertSame(0, PromoLayanan::where('nama', 'Promo Terbalik')->count());
    }

    #[Test]
    public function promo_sehari_diterima(): void
    {
        /*
         * after_or_equal, bukan after: promo sehari — mulai dan selesai di
         * tanggal yang sama — masuk akal dan sudah pernah diminta.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $nama = 'Promo Sehari ' . Str::random(5);

        $this->actingAs($admin)->post(route('account.promo-layanan.store'), [
            'nama' => $nama, 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->assertSame('berjalan', PromoLayanan::where('nama', $nama)->firstOrFail()->keadaan);
    }

    #[Test]
    public function nilai_potongan_diperiksa_menurut_tipenya(): void
    {
        /*
         * Diwajibkan dua-duanya, admin yang memilih "potong persen" tetap
         * ditolak karena kotak rupiah kosong — padahal kotak itu memang
         * disembunyikan saat tipenya persen.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->post(route('account.promo-layanan.store'), [
            'nama' => 'Promo Tanpa Persen', 'tipe' => 'persen',
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
        ])->assertSessionHasErrors('persen');

        $this->actingAs($admin)->post(route('account.promo-layanan.store'), [
            'nama' => 'Promo Tanpa Rupiah', 'tipe' => 'rupiah',
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
        ])->assertSessionHasErrors('nominal');
    }

    #[Test]
    public function persen_di_atas_seratus_ditolak(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->post(route('account.promo-layanan.store'), [
            'nama' => 'Promo Gratis Total', 'tipe' => 'persen', 'persen' => 150,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
        ])->assertSessionHasErrors('persen');
    }

    #[Test]
    public function angkatan_yang_tidak_ada_ditolak(): void
    {
        /*
         * Nilainya datang dari kiriman borang, dan id sembarang akan sampai ke
         * pivotnya apa adanya — menghasilkan baris yang menunjuk angkatan yang
         * tidak ada, yang di layar terbaca sebagai baris kosong tanpa nama.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->post(route('account.promo-layanan.store'), [
            'nama' => 'Promo Hantu', 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'angkatan' => [(string) Str::uuid()],
        ])->assertSessionHasErrors('angkatan.0');
    }

    // ------------------------------------------------------------- tindakan

    #[Test]
    public function saklar_membalik_keadaan_promonya(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $promo = PromoLayanan::create([
            'nama' => 'Promo Saklar ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $this->actingAs($admin)->post(route('account.promo-layanan.saklar', $promo->id));
        $this->assertFalse($promo->fresh()->aktif);

        $this->actingAs($admin)->post(route('account.promo-layanan.saklar', $promo->id));
        $this->assertTrue($promo->fresh()->aktif);
    }

    #[Test]
    public function pelanggan_tidak_bisa_menarik_saklarnya(): void
    {
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        $promo = PromoLayanan::create([
            'nama' => 'Promo Terkunci ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $this->actingAs($pelanggan)
            ->post(route('account.promo-layanan.saklar', $promo->id))
            ->assertRedirect(route('account.dashboard.index'));

        $this->assertTrue($promo->fresh()->aktif);
    }

    #[Test]
    public function menghapus_promo_ikut_membuang_sambungan_angkatannya(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp');

        $promo = PromoLayanan::create([
            'nama' => 'Promo Dihapus ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);
        $promo->angkatan()->attach($angkatan->getKey());

        $id = (string) $promo->getKey();

        $this->actingAs($admin)->delete(route('account.promo-layanan.hapus', $id))
            ->assertRedirect(route('account.promo-layanan.index'));

        $this->assertNull(PromoLayanan::find($id));
        $this->assertSame(0, DB::table('promo_layanan_angkatan')->where('promo_id', $id)->count());
    }

    #[Test]
    public function menghapus_promo_tidak_mengubah_harga_yang_sudah_ditagih(): void
    {
        /*
         * Nominalnya sudah tersimpan di baris pendaftarannya, bukan dihitung
         * ulang dari promonya. Kalau tidak, menghapus promo lama akan
         * mengubah pembukuan pendaftaran yang sudah lunas berbulan-bulan
         * sebelumnya.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp');

        $promo = PromoLayanan::create([
            'nama' => 'Promo Lampau ' . Str::random(4), 'tipe' => 'persen', 'persen' => 20,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDay()->toDateString(), 'aktif' => true,
        ]);
        $promo->angkatan()->attach($angkatan->getKey());
        PromoAngkatan::lupakan();

        $hasil = (new \App\Actions\Pendaftaran\BuatPendaftaran)->jalankan('scopus_camp', [
            'kategori_id' => $angkatan->getKey(),
            'nama' => 'Pendaftar Lampau', 'email' => Str::random(6) . '@contoh.test',
            'telp' => '0811-0000-0003', 'jumlah' => '1', 'cara_bayar' => 'transfer',
        ], $admin->full_name);

        $this->assertTrue($hasil['berhasil']);
        $sebelum = (int) $hasil['model']->total_pembayaran;
        $potongSebelum = (int) $hasil['model']->nominal_diskon;

        $this->actingAs($admin)->delete(route('account.promo-layanan.hapus', $promo->id));

        $segar = PendaftaranScopusCamp::findOrFail($hasil['model']->getKey());

        $this->assertSame($sebelum, (int) $segar->total_pembayaran);
        $this->assertSame($potongSebelum, (int) $segar->nominal_diskon);
    }

    #[Test]
    public function menghapus_angkatan_ikut_membuang_sambungan_promonya(): void
    {
        /*
         * Dibiarkan menggantung, penghitung "berlaku untuk N angkatan"
         * menyebut angkatan yang sudah tidak ada dan borangnya menampilkan
         * baris kosong tanpa nama.
         */
        $angkatan = $this->angkatan('scopus_camp');

        $promo = PromoLayanan::create([
            'nama' => 'Promo Yatim ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);
        $promo->angkatan()->attach($angkatan->getKey());

        $angkatan->delete();

        $this->assertSame(0, DB::table('promo_layanan_angkatan')
            ->where('promo_id', $promo->getKey())->count());
        $this->assertNotNull(PromoLayanan::find($promo->getKey()));
    }

    // ------------------------------- promo terlihat dari layar angkatannya

    #[Test]
    public function layar_angkatan_menyebut_promo_yang_sedang_berjalan(): void
    {
        /*
         * Promo yang tidak kelihatan dari layar angkatan praktis tidak ada.
         *
         * Di situlah admin memeriksa harga sebelum mengumumkan angkatan, dan
         * sebelum ini satu-satunya cara mengetahui ada promo adalah membuka
         * menu Promo Layanan lalu mengingat angkatan mana saja yang
         * tercentang.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp');

        $promo = PromoLayanan::create([
            'nama' => 'Promo Terlihat ' . Str::random(4), 'tipe' => 'persen', 'persen' => 25,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDays(5)->toDateString(), 'aktif' => true,
        ]);
        $promo->angkatan()->attach($angkatan->getKey());
        PromoAngkatan::lupakan();

        // Biaya 2.000.000 - 25% = 1.500.000.
        $this->actingAs($admin)
            ->get(route('account.kategori-layanan.index', ['cari' => $angkatan->nama]))
            ->assertOk()
            ->assertSee('promo Rp 1.500.000');
    }

    #[Test]
    public function rincian_angkatan_menunjuk_ke_promonya(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp');

        $promo = PromoLayanan::create([
            'nama' => 'Promo Tertaut ' . Str::random(4), 'tipe' => 'rupiah', 'nominal' => 250_000,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDays(5)->toDateString(), 'aktif' => true,
        ]);
        $promo->angkatan()->attach($angkatan->getKey());
        PromoAngkatan::lupakan();

        $this->actingAs($admin)
            ->get(route('account.kategori-layanan.detail', $angkatan->getKey()))
            ->assertOk()
            ->assertSee($promo->nama)
            ->assertSee(route('account.promo-layanan.edit', $promo->id))
            ->assertSee('jadi Rp 1.750.000');
    }

    #[Test]
    public function promo_yang_sudah_lewat_tidak_disebut_di_layar_angkatan(): void
    {
        /*
         * Arah sebaliknya ikut dijaga. Diuji hanya "promo tampil", syarat
         * periodenya bisa hilang dari kuerinya tanpa satu uji pun merah — dan
         * layar angkatan akan menyebut harga promo yang sudah tidak berlaku.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp');

        $promo = PromoLayanan::create([
            'nama' => 'Promo Kadaluarsa ' . Str::random(4), 'tipe' => 'persen', 'persen' => 25,
            'mulai' => now()->subDays(30)->toDateString(),
            'selesai' => now()->subDays(2)->toDateString(), 'aktif' => true,
        ]);
        $promo->angkatan()->attach($angkatan->getKey());
        PromoAngkatan::lupakan();

        $this->actingAs($admin)
            ->get(route('account.kategori-layanan.index', ['cari' => $angkatan->nama]))
            ->assertOk()
            ->assertDontSee('promo Rp 1.500.000');
    }

    #[Test]
    public function promo_untuk_sepuluh_angkatan_dibaca_dalam_satu_kueri(): void
    {
        /*
         * Dibaca per baris lewat accessor-nya, layar dengan 10 angkatan
         * menjalankan 10 kueri yang bentuknya sama persis — dan jumlahnya
         * tumbuh begitu daftar per halamannya dinaikkan.
         *
         * Yang dihitung kuerinya SENDIRI, bukan waktunya: ukuran waktu di
         * basis data lokal terlalu berderau untuk membedakan 1 kueri dari 10.
         */
        $angkatan = [];

        for ($i = 0; $i < 10; $i++) {
            $angkatan[] = $this->angkatan('scopus_camp');
        }

        $promo = PromoLayanan::create([
            'nama' => 'Promo Serentak ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDay()->toDateString(), 'aktif' => true,
        ]);
        $promo->angkatan()->attach(collect($angkatan)->map->getKey()->all());

        PromoAngkatan::lupakan();

        $jumlahKueri = 0;
        DB::listen(function () use (&$jumlahKueri) { $jumlahKueri++; });

        PromoAngkatan::muat($angkatan);

        // Dua: satu untuk promonya, satu untuk angkatan yang dimuat bersamanya.
        $this->assertLessThanOrEqual(2, $jumlahKueri,
            'Promo dimuat per baris, bukan berkelompok: ' . $jumlahKueri . ' kueri untuk 10 angkatan.');

        // Dan hasilnya benar, bukan cuma hemat.
        foreach ($angkatan as $a) {
            $this->assertSame($promo->getKey(), PromoAngkatan::untuk($a)?->getKey());
        }
    }

    // ------------------------------------------------------------- perkakas

    private function akun(string $peran): User
    {
        $t = Str::random(8);

        $orang = User::create([
            'full_name' => 'Uji Promo ' . $t, 'username' => 'lpr_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $orang->forceFill([
            'status' => 'active', 'email_verified_at' => now(), 'peran' => $peran,
        ])->save();

        return $orang;
    }

    private function angkatan(string $layanan): KategoriLayanan
    {
        KategoriLayanan::lupakanPendaftar();

        return KategoriLayanan::create([
            'layanan' => $layanan, 'nama' => 'Angkatan Promo ' . Str::random(6),
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '30', 'sisa_kuota' => '30', 'status' => 'active',
            'biaya' => '2000000',
        ]);
    }
}
