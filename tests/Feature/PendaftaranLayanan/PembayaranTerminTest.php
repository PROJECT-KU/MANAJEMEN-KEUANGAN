<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\PembayaranPendaftaran;
use App\PemesananLembaga;
use App\PendaftaranScopusCamp;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pembayaran bertermin — DP, cicilan, pelunasan.
 *
 * Sebelum ini uang hanya punya dua keadaan: "Menunggu bayar" atau "Lunas".
 * Tidak ada di antaranya. Akibatnya lembaga yang sudah mentransfer DP 30%
 * tercatat SAMA PERSIS dengan yang belum bayar sepeser pun, dan satu-satunya
 * jejak DP-nya adalah catatan panitia — teks bebas yang tidak dijumlahkan di
 * mana pun.
 */
class PembayaranTerminTest extends TestCase
{
    use DatabaseTransactions;

    private function akun(string $peran = User::PERAN_ADMINISTRATOR): User
    {
        $u = User::create([
            'full_name' => 'Uji Termin',
            'username' => 'uji_termin_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('rahasia-panjang-sekali'),
            'level' => 'user',
        ]);

        $u->forceFill(['status' => 'active', 'email_verified_at' => now(), 'peran' => $peran])->save();

        return $u;
    }

    private function angkatan(int $kuota = 40, int $sisa = 40)
    {
        return \App\KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Uji Termin ' . Str::random(5),
            'nomor_angkatan' => random_int(9000, 9999),
            'tanggal_mulai' => now()->addMonth()->toDateString(),
            'tanggal_selesai' => now()->addMonth()->addDays(3)->toDateString(),
            'total_kuota' => $kuota,
            'sisa_kuota' => $sisa,
            'biaya' => 5000000,
            'status' => 'active',
        ]);
    }

    /** Mendaftarkan satu baris; $tambahan menentukan rombongan/lembaganya. */
    private function daftarkan(User $orang, string $nama, array $tambahan = [])
    {
        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), array_merge([
            'layanan' => 'scopus_camp',
            'kategori_id' => $this->angkatan()->id,
            'nama' => $nama,
            'email' => Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-' . random_int(1000, 9999),
        ], $tambahan))->assertRedirect();

        return PendaftaranScopusCamp::where('nama', $nama)->firstOrFail();
    }

    #[Test]
    public function dp_lembaga_tercatat_dan_sisa_tagihannya_dihitung(): void
    {
        $orang = $this->akun();

        $a = $this->daftarkan($orang, 'Peserta Lembaga Satu', [
            'jenis' => 'lembaga',
            'lembaga_nama' => 'Universitas Uji Termin',
            'jumlah' => 10,
        ]);

        $lembaga = PemesananLembaga::untukPendaftaran('scopus_camp', (string) $a->id);
        $this->assertNotNull($lembaga, 'Pendaftarannya harus terikat pesanan lembaga.');

        $tagihan = $lembaga->tagihan();
        $this->assertGreaterThan(0, $tagihan);

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.pembayaran', ['scopus_camp', $a->id]),
            ['nominal' => '10.000.000', 'tanggal' => now()->toDateString(), 'cara_bayar' => 'transfer']
        )->assertRedirect()->assertSessionHasNoErrors();

        $ringkas = $lembaga->ringkasBayar();

        $this->assertSame(10000000, $ringkas['terbayar']);
        $this->assertSame($tagihan - 10000000, $ringkas['sisa']);
        $this->assertFalse($ringkas['lunas']);

        /*
         * Terminnya menempel ke PESANAN, bukan ke baris pendaftarannya. Kalau
         * menempel ke barisnya, pendaftaran kedua dari lembaga yang sama akan
         * punya sisa tagihannya sendiri — dan tidak satu pun angka itu
         * menjawab "pesanan ini kurang berapa".
         */
        $this->assertSame(
            1,
            PembayaranPendaftaran::milik(PembayaranPendaftaran::LEMBAGA, (string) $lembaga->id)->count()
        );
        $this->assertSame(
            0,
            PembayaranPendaftaran::milik(PembayaranPendaftaran::PENDAFTARAN, (string) $a->id)->count()
        );
    }

    #[Test]
    public function termin_berikutnya_menutup_tagihan_dan_disebut_pelunasan(): void
    {
        $orang = $this->akun();

        $a = $this->daftarkan($orang, 'Peserta Lunas Bertahap', [
            'jenis' => 'lembaga',
            'lembaga_nama' => 'Institut Uji Pelunasan',
            'jumlah' => 4,
        ]);

        $lembaga = PemesananLembaga::untukPendaftaran('scopus_camp', (string) $a->id);
        $tagihan = $lembaga->tagihan();

        foreach ([5000000, $tagihan - 5000000] as $nominal) {
            $this->actingAs($orang)->post(
                route('account.pendaftaran-layanan.pembayaran', ['scopus_camp', $a->id]),
                ['nominal' => (string) $nominal, 'tanggal' => now()->toDateString(), 'cara_bayar' => 'transfer']
            )->assertSessionHasNoErrors();
        }

        $ringkas = $lembaga->ringkasBayar();

        $this->assertTrue($ringkas['lunas']);
        $this->assertSame(0, $ringkas['sisa']);

        $semua = $lembaga->pembayaran();
        $this->assertSame([1, 2], $semua->pluck('urutan')->all());

        /*
         * Sebutannya ikut keadaan, bukan nomor urutnya saja. Termin terakhir
         * yang masih menyisakan tagihan BUKAN pelunasan, dan menyebutnya
         * begitu membuat kwitansinya berbohong di tangan orang keuangan
         * lembaga.
         */
        $this->assertSame('DP', $semua[0]->sebutan(true, 2));
        $this->assertSame('Pelunasan', $semua[1]->sebutan(true, 2));
        $this->assertSame('Termin 2', $semua[1]->sebutan(false, 2));
    }

    #[Test]
    public function rombongan_yang_dibayar_perorangan_juga_bisa_dp(): void
    {
        /*
         * Si A mengajak enam temannya dan membayar sendiri. Itu SATU baris
         * pendaftaran berisi tujuh kursi — bukan pesanan lembaga — jadi
         * terminnya menempel ke barisnya sendiri.
         */
        $orang = $this->akun();

        $a = $this->daftarkan($orang, 'Pemesan Rombongan DP', ['jumlah' => 7]);

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.pembayaran', ['scopus_camp', $a->id]),
            ['nominal' => '5.000.000', 'tanggal' => now()->toDateString(), 'cara_bayar' => 'tunai']
        )->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(
            1,
            PembayaranPendaftaran::milik(PembayaranPendaftaran::PENDAFTARAN, (string) $a->id)->count()
        );

        // Dan terminnya ikut terhapus bersama pendaftarannya: tidak ada kunci
        // asing yang membersihkannya sendiri.
        $this->actingAs($orang)
            ->delete(route('account.pendaftaran-layanan.hapus', ['scopus_camp', $a->id]))
            ->assertRedirect();

        $this->assertSame(
            0,
            PembayaranPendaftaran::milik(PembayaranPendaftaran::PENDAFTARAN, (string) $a->id)->count()
        );
    }

    #[Test]
    public function pendaftar_satu_orang_tidak_dibukakan_pembayaran_bertermin(): void
    {
        /*
         * Membuka cicilan untuk satu kursi berarti kursi yang ditahan
         * berbulan-bulan oleh uang yang tidak seberapa. Ditolak dengan
         * kalimat yang menyebut JALAN LAINNYA, bukan sekadar "tidak bisa".
         */
        $orang = $this->akun();

        $a = $this->daftarkan($orang, 'Pendaftar Sendirian');

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.pembayaran', ['scopus_camp', $a->id]),
            ['nominal' => '1.000.000', 'tanggal' => now()->toDateString()]
        )->assertRedirect()->assertSessionHas('info');

        $this->assertSame(0, PembayaranPendaftaran::count());

        // Dan tabnya pun tidak muncul di layar rinciannya.
        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $a->id]))
            ->assertOk()->getContent();

        $this->assertStringNotContainsString('id="rin-panel-termin"', $isi);
    }

    #[Test]
    public function tanggal_di_masa_depan_ditolak(): void
    {
        /*
         * Mencatat uang yang BELUM masuk berarti sisa tagihan yang salah
         * sampai hari itu tiba — dan panitia berhenti menagih karena angkanya
         * terlihat sudah beres.
         */
        $orang = $this->akun();

        $a = $this->daftarkan($orang, 'Peserta Tanggal Depan', ['jumlah' => 3]);

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.pembayaran', ['scopus_camp', $a->id]),
            ['nominal' => '1.000.000', 'tanggal' => now()->addWeek()->toDateString()]
        )->assertSessionHasErrors('tanggal');

        $this->assertSame(0, PembayaranPendaftaran::count());
    }

    #[Test]
    public function layar_rincian_dan_faktur_menyebut_sisa_tagihannya(): void
    {
        $orang = $this->akun();

        $a = $this->daftarkan($orang, 'Peserta Lihat Sisa', [
            'jenis' => 'lembaga',
            'lembaga_nama' => 'Politeknik Uji Layar',
            'jumlah' => 6,
        ]);

        $lembaga = PemesananLembaga::untukPendaftaran('scopus_camp', (string) $a->id);

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.pembayaran', ['scopus_camp', $a->id]),
            ['nominal' => '7.000.000', 'tanggal' => now()->toDateString(), 'cara_bayar' => 'transfer']
        )->assertSessionHasNoErrors();

        $sisa = number_format($lembaga->ringkasBayar()['sisa'], 0, ',', '.');

        $rincian = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $a->id]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('id="rin-panel-termin"', $rincian);
        $this->assertStringContainsString('Rp ' . $sisa, $rincian);

        /*
         * Fakturnya pun. Faktur yang menyebut total tagihan saja sementara
         * lembaganya sudah membayar DP terbaca seperti tagihan yang belum
         * disentuh — dan itu yang dibawa ke rapat anggaran.
         */
        $faktur = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.faktur', $lembaga->id))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Sisa tagihan', $faktur);
        $this->assertStringContainsString('bertermin', $faktur);

        /*
         * Dan kalimat "pembayarannya per pendaftaran" HILANG begitu ada
         * termin: satu transfer DP tidak mungkin cocok dengan kode unik siapa
         * pun, jadi dua kalimat itu saling membatalkan.
         */
        $this->assertStringNotContainsString('per pendaftaran', $faktur);
    }

    #[Test]
    public function kwitansi_memuat_nominal_sisa_dan_penerimanya(): void
    {
        $orang = $this->akun();

        $a = $this->daftarkan($orang, 'Peserta Kwitansi', [
            'jenis' => 'lembaga',
            'lembaga_nama' => 'Sekolah Uji Kwitansi',
            'jumlah' => 5,
        ]);

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.pembayaran', ['scopus_camp', $a->id]),
            [
                'nominal' => '9.000.000',
                'tanggal' => now()->toDateString(),
                'cara_bayar' => 'transfer',
                'bukti' => UploadedFile::fake()->image('bukti.jpg', 50, 50),
            ]
        )->assertSessionHasNoErrors();

        $bayar = PembayaranPendaftaran::firstOrFail();

        // Buktinya benar-benar tersimpan, dan namanya dirakit sistem — nama
        // berkas dari ponsel kerap berapostrof, dan firewall hostingnya
        // menolak alamat berapostrof dengan 403 sebelum PHP sempat jalan.
        $this->assertNotNull($bayar->bukti);
        $this->assertStringStartsWith('termin-', $bayar->bukti);

        $berkas = public_path(
            \App\Http\Controllers\account\PendaftaranLayananController::FOLDER_BUKTI_TERMIN
            . '/' . $bayar->bukti
        );

        $this->assertFileExists($berkas);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.kwitansi', $bayar->getKey()))
            ->assertOk()->getContent();

        $this->assertStringContainsString('KWITANSI', $isi);
        $this->assertStringContainsString('Rp 9.000.000', $isi);
        $this->assertStringContainsString('Sekolah Uji Kwitansi', $isi);
        $this->assertStringContainsString('Sisa tagihan', $isi);

        /*
         * DatabaseTransactions TIDAK melindungi cakram — berkas yang ditulis
         * uji ini tetap tertinggal di public/ sesudah transaksinya digulung.
         */
        @unlink($berkas);
        $this->assertFileDoesNotExist($berkas);
    }

    #[Test]
    public function panitia_diberi_tahu_di_mana_dp_dicatat(): void
    {
        /*
         * Tab "Termin & DP" sudah ada, tetapi tidak ada yang tahu ia ada
         * sampai mencarinya — dan yang dicari panitia justru tombol DP di
         * borang pembuatan, tempat ia memang tidak ada.
         *
         * Dua tempat dijaga di sini: kalimat yang muncul tepat saat halaman
         * rinciannya terbuka, dan nota di borangnya sendiri.
         */
        $orang = $this->akun();

        $rombongan = $this->daftarkan($orang, 'Pemesan Diberi Tahu', ['jumlah' => 4]);

        $this->assertStringContainsString(
            'Termin & DP',
            (string) session('sukses'),
            'Pesanan rombongan harus disebutkan di mana DP-nya dicatat.'
        );

        // Dan pendaftar satu kursi TIDAK diberi kalimat itu: ia memang tidak
        // dibukakan pembayaran bertermin, jadi menunjuk tab yang tidak muncul
        // hanya membuat panitia mencarinya sia-sia.
        $this->daftarkan($orang, 'Pendaftar Sendiri Saja');

        $this->assertStringNotContainsString('Termin & DP', (string) session('sukses'));

        /*
         * Notanya ada di borang pembuatan, dan TERTUTUP saat borang dibuka —
         * yang membukanya jumlah orang atau pilihan lembaganya.
         */
        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.baru'))
            ->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/id="bar-nota-dp"[^>]*\shidden/', $isi);
        $this->assertStringContainsString('DP atau cicilan dicatat setelah ini', $isi);

        // Notanya menunjuk tab yang namanya memang dipakai di layar rincian.
        $rincian = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $rombongan->id]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Termin &amp; DP', $rincian);
    }

    #[Test]
    public function hanya_orang_dalam_yang_boleh_mencatat_dan_menghapus(): void
    {
        $orang = $this->akun();
        $a = $this->daftarkan($orang, 'Peserta Penjaga Akses', ['jumlah' => 3]);

        /*
         * Tamu: ditolak sebelum apa pun tersimpan.
         *
         * Harus KELUAR dulu. actingAs() menyetel pengguna pada penjaganya dan
         * itu bertahan untuk SELURUH uji, bukan satu permintaan — tanpa baris
         * ini, "permintaan tamu" di bawah masih membawa akun admin yang
         * dipakai mendaftarkan di atas, dan penjaganya terlihat bobol padahal
         * tidak pernah diuji.
         */
        \Illuminate\Support\Facades\Auth::logout();

        $this->post(route('account.pendaftaran-layanan.pembayaran', ['scopus_camp', $a->id]), [
            'nominal' => '1.000.000', 'tanggal' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertSame(0, PembayaranPendaftaran::count());

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.pembayaran', ['scopus_camp', $a->id]),
            ['nominal' => '1.000.000', 'tanggal' => now()->toDateString()]
        )->assertSessionHasNoErrors();

        $bayar = PembayaranPendaftaran::firstOrFail();

        \Illuminate\Support\Facades\Auth::logout();

        $this->delete(route('account.pendaftaran-layanan.pembayaran.hapus', $bayar->getKey()))
            ->assertRedirect();

        $this->assertSame(1, PembayaranPendaftaran::count(), 'Tamu tidak boleh menghapusnya.');

        $this->actingAs($orang)
            ->delete(route('account.pendaftaran-layanan.pembayaran.hapus', $bayar->getKey()))
            ->assertRedirect();

        $this->assertSame(0, PembayaranPendaftaran::count());
    }

    #[Test]
    public function panel_termin_tidak_menyisakan_blok_mengambang(): void
    {
        /*
         * Sebelum ini kalimat pengantar, kotak angka tagihan, dan notanya
         * mengambang bertiga di atas latar badan tab sementara borang di
         * bawahnya sudah berkartu — irama yang sama pecahnya dengan tab
         * Ringkasan dulu.
         *
         * Yang dijaga: SELURUH anak langsung panel Termin berupa kartu.
         */
        $orang = $this->akun();

        // Tab Termin & DP hanya muncul untuk pesanan lembaga, jadi pendaftarnya
        // harus rombongan — bukan peserta perorangan.
        $camp = $this->daftarkan($orang, 'Peserta Uji Kartu ' . Str::random(5), [
            'jenis' => 'lembaga',
            'lembaga_nama' => 'Universitas Uji Kartu',
            'jumlah' => 10,
        ]);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->id]))
            ->assertOk()
            ->getContent();

        $dom = new \DOMDocument();
        $sebelumnya = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $isi);
        libxml_clear_errors();
        libxml_use_internal_errors($sebelumnya);

        $panel = (new \DOMXPath($dom))->query('//*[@id="rin-panel-termin"]')->item(0);

        $this->assertNotNull($panel, 'Panel Termin & DP tidak ketemu.');

        $lepas = [];

        foreach ($panel->childNodes as $simpul) {
            if ($simpul->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            if (! str_contains($simpul->getAttribute('class'), 'rin-bagian')) {
                $lepas[] = $simpul->nodeName . '.' . $simpul->getAttribute('class');
            }
        }

        $this->assertSame([], $lepas,
            'Ada isi tab Termin & DP yang tidak dibungkus kartu: ' . implode(' | ', $lepas));
    }

    #[Test]
    public function unggah_bukti_termin_memakai_pola_halaman_profil(): void
    {
        /*
         * Kotak berkas bawaan peramban ("Choose file / No file chosen") tidak
         * bisa diberi gaya, berbahasa Inggris, dan rupanya berbeda di tiap
         * peramban — di antara isian lain yang seragam ia terbaca seperti
         * unsur asing.
         *
         * Polanya disalin dari halaman Profil: isian aslinya disembunyikan
         * 1x1 TRANSPARAN (bukan display:none, supaya tetap bisa menerima
         * fokus papan ketik) dan labelnya yang jadi sasaran ketukan.
         */
        $orang = $this->akun();

        // Tab Termin & DP hanya muncul untuk pesanan lembaga, jadi pendaftarnya
        // harus rombongan — bukan peserta perorangan.
        $camp = $this->daftarkan($orang, 'Peserta Uji Kartu ' . Str::random(5), [
            'jenis' => 'lembaga',
            'lembaga_nama' => 'Universitas Uji Kartu',
            'jumlah' => 10,
        ]);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->id]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('class="rin-berkas', $isi,
            'Isian berkas belum memakai kelas yang menyembunyikannya.');
        $this->assertStringContainsString('class="rin-unggah"', $isi,
            'Label pengunggah bergaya Profil tidak ada.');
        $this->assertStringContainsString('data-mis-berkas=', $isi,
            'Nama berkas yang dipilih tidak akan pernah ditulis di labelnya.');

        // Dan isian aslinya TIDAK lagi memakai kelas kotak biasa, yang akan
        // membuat kotak bawaannya tetap terlihat di samping pengunggahnya.
        $this->assertSame(0, preg_match('/<input type="file"[^>]*class="form-control-modern/', $isi),
            'Isian berkas masih memakai kelas kotak biasa; kotak bawaannya akan tetap terlihat.');
    }
}
