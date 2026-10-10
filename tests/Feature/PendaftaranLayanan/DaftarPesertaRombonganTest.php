<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\KategoriLayanan;
use App\PendaftaranPeserta;
use App\PendaftaranScopusCamp;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Panitia mengisikan nama peserta rombongan dari layar rinciannya.
 *
 * Borang TAMBAH sudah bisa sejak awal; layar rinciannya tidak — jadi
 * rombongan yang terlanjur tersimpan tanpa daftar nama tidak bisa dilengkapi
 * dari mana pun. Terukur di produksi 8 Okt 2026: enam pendaftaran rombongan,
 * NOL nama peserta tercatat, sementara layarnya memperingatkan "tanyakan
 * sisanya ke pendaftarnya".
 */
class DaftarPesertaRombonganTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();
    }

    #[Test]
    public function panitia_bisa_mengisikan_nama_dan_nomor_pesertanya(): void
    {
        [$orang, $baris] = $this->rombongan(4);

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => "Budi Santoso, 081234567890\nSiti Rahma, 085700011122\nAgus Pratama"]
        )->assertRedirect()->assertSessionHasNoErrors();

        $peserta = PendaftaranPeserta::milik('scopus_camp', (string) $baris->getKey())
            ->terurut()->get();

        $this->assertCount(3, $peserta);
        $this->assertSame(['Budi Santoso', 'Siti Rahma', 'Agus Pratama'], $peserta->pluck('nama')->all());
        $this->assertSame([1, 2, 3], $peserta->pluck('urutan')->map(fn ($u) => (int) $u)->all());

        // Nomornya dinormalkan sama seperti nomor pendaftar utama; kalau tidak,
        // satu orang tersimpan dalam dua bentuk dan pencarian hanya menemukan satu.
        $this->assertSame('6281234567890', $peserta[0]->telp);

        // Kosong jadi null, bukan untaian kosong: dua penanda "tidak ada nomor"
        // di satu kolom membuat tiap pembacanya harus memeriksa keduanya.
        $this->assertNull($peserta[2]->telp);
    }

    /**
     * Kelebihan nama DITOLAK, bukan dipotong diam-diam.
     *
     * Versi sebelumnya memotongnya lewat array_slice dan layarnya tetap
     * menjawab "Daftar pesertanya lengkap: 2 dari 2 orang" — terukur: tiga
     * nama diketik, SATU tersimpan, dan sistemnya mengucapkan selamat. Dua
     * nama hilang tanpa satu pun tanda, dan panitia baru tahu saat
     * menerbitkan sertifikat.
     *
     * Nama ke-empat pada rombongan berbayar tiga adalah orang yang kursinya
     * tidak pernah dibeli; yang harus terjadi bukan membuangnya diam-diam,
     * melainkan bertanya.
     */
    #[Test]
    public function kelebihan_nama_ditolak_bukan_dipotong(): void
    {
        [$orang, $baris] = $this->rombongan(3);

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => "Satu\nDua\nTiga\nEmpat\nLima"]
        )->assertSessionHasErrors('peserta');

        $this->assertSame([],
            PendaftaranPeserta::milik('scopus_camp', (string) $baris->getKey())
                ->terurut()->pluck('nama')->all(),
            'Sebagian namanya tetap tersimpan padahal kirimannya ditolak.');
    }

    /** Pas sebanyak kursinya tetap diterima — batasnya bukan satu kurangnya. */
    #[Test]
    public function pas_sebanyak_kursinya_diterima(): void
    {
        [$orang, $baris] = $this->rombongan(3);

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => "Satu\nDua"]
        )->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(['Satu', 'Dua'],
            PendaftaranPeserta::milik('scopus_camp', (string) $baris->getKey())
                ->terurut()->pluck('nama')->all());
    }

    /**
     * Penolakannya TIDAK BOLEH menghapus daftar yang sudah benar.
     *
     * Panitia yang menempelkan daftar terlalu panjang lalu ditolak akan
     * mengira daftarnya hilang — dan kalau benar-benar hilang, ia harus
     * mengetik ulang semuanya dari awal.
     */
    #[Test]
    public function penolakannya_tidak_menghapus_daftar_lama(): void
    {
        [$orang, $baris] = $this->rombongan(3);

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => "Satu\nDua"]
        )->assertRedirect();

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => "Satu\nDua\nTiga\nEmpat"]
        )->assertSessionHasErrors('peserta');

        $this->assertSame(['Satu', 'Dua'],
            PendaftaranPeserta::milik('scopus_camp', (string) $baris->getKey())
                ->terurut()->pluck('nama')->all(),
            'Daftar yang sudah benar ikut terhapus oleh kiriman yang ditolak.');
    }

    /**
     * Pesan salahnya menyebut jalan keluarnya. Kelebihan nama hampir selalu
     * berarti salah satu dari dua hal — ada nama yang tidak seharusnya di
     * situ, atau rombongannya memang bertambah — dan panitia yang tidak
     * diberi tahu yang kedua akan menghapus nama orang yang sudah membayar.
     */
    #[Test]
    public function pesan_salahnya_menyebut_jalan_keluarnya(): void
    {
        [$orang, $baris] = $this->rombongan(2);

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => "Satu\nDua\nTiga"]
        );

        $pesan = session('errors')->first('peserta');

        $this->assertStringContainsString('paling banyak 1 nama', $pesan);
        $this->assertStringContainsString('mengisi 3 nama', $pesan,
            'Berapa yang diisi tidak disebut, jadi panitia menebak apa yang salah.');
        $this->assertStringContainsString('Jumlah orang', $pesan,
            'Jalan keluarnya tidak disebut; yang membacanya akan menghapus nama '
            . 'orang yang sebenarnya sudah membayar.');
    }

    /**
     * Daftarnya penuh: tempat mengetiknya HILANG.
     *
     * Borang yang tetap terbuka saat kursinya habis terbaca sebagai "masih
     * boleh menambah" — dan yang mengetik nama kesekian baru tahu saat
     * kirimannya ditolak. Yang paling jelas menyampaikan "sudah lengkap"
     * adalah hilangnya tempat mengetik.
     */
    #[Test]
    public function borangnya_dilipat_saat_daftarnya_sudah_penuh(): void
    {
        [$orang, $baris] = $this->rombongan(2);

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => 'Satu']
        )->assertRedirect();

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $baris->getKey()]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Daftarnya sudah lengkap', $isi,
            'Tidak ada yang memberi tahu bahwa kursinya sudah habis.');

        $this->assertMatchesRegularExpression(
            '/<form[^>]*id="rin-peserta-borang"[^>]*\shidden/s', $isi,
            'Kotak isian dan pemilih berkasnya masih menganga padahal tidak ada '
            . 'kursi tersisa.'
        );
    }

    /**
     * DILIPAT, bukan dibuang. Kotak itu satu-satunya cara membetulkan nama
     * yang salah ketik — simpanPeserta mengganti seluruh daftar, tidak ada
     * penyunting per baris. Dibuang, satu huruf salah di nama berarti
     * sertifikat yang salah cetak dan tidak ada jalan membetulkannya.
     */
    #[Test]
    public function daftar_penuh_masih_bisa_dibetulkan(): void
    {
        [$orang, $baris] = $this->rombongan(2);

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => 'Sattu']
        )->assertRedirect();

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $baris->getKey()]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('data-rin-buka="rin-peserta-borang"', $isi,
            'Tidak ada jalan membuka borangnya lagi; salah ketik jadi permanen.');

        // Dan pembetulannya memang tersimpan.
        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => 'Satu']
        )->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(['Satu'],
            PendaftaranPeserta::milik('scopus_camp', (string) $baris->getKey())
                ->terurut()->pluck('nama')->all());
    }

    /**
     * Kiriman yang ditolak membuka borangnya kembali.
     *
     * Tanpa ini, panitia yang mengisi terlalu banyak akan dibalikkan ke
     * layar yang borangnya TERTUTUP — pesan salahnya ada di dalam lipatan,
     * dan yang dilihatnya cuma panel hijau "sudah lengkap" yang seolah
     * mengatakan tidak terjadi apa-apa.
     */
    #[Test]
    public function kiriman_yang_ditolak_membuka_borangnya_lagi(): void
    {
        [$orang, $baris] = $this->rombongan(2);

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => 'Satu']
        )->assertRedirect();

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => "Satu\nDua"]
        )->assertSessionHasErrors('peserta');

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $baris->getKey()]))
            ->assertOk()->getContent();

        $this->assertSame(0, preg_match('/<form[^>]*id="rin-peserta-borang"[^>]*\shidden/s', $isi),
            'Borangnya tetap terlipat sesudah kirimannya ditolak, jadi pesan '
            . 'salahnya tersembunyi di dalam lipatan.');
    }

    #[Test]
    public function menyimpan_ulang_mengganti_daftarnya_bukan_menumpuk(): void
    {
        [$orang, $baris] = $this->rombongan(5);

        $kirim = fn (string $teks) => $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => $teks]
        )->assertRedirect();

        $kirim("Budi\nSiti");
        $kirim("Budi\nSiti\nAgus");

        $peserta = PendaftaranPeserta::milik('scopus_camp', (string) $baris->getKey())->terurut()->get();

        $this->assertCount(3, $peserta, 'Daftarnya menumpuk; nama ganda saat dibetulkan.');
        $this->assertSame(['Budi', 'Siti', 'Agus'], $peserta->pluck('nama')->all());
    }

    #[Test]
    public function daftar_boleh_dikosongkan(): void
    {
        [$orang, $baris] = $this->rombongan(4);

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => "Budi\nSiti"]
        )->assertRedirect();

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => '']
        )->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(0,
            PendaftaranPeserta::milik('scopus_camp', (string) $baris->getKey())->count(),
            'Daftar yang dikosongkan tidak ikut terhapus.');
    }

    #[Test]
    public function email_peserta_tidak_hilang_saat_daftarnya_disimpan_ulang(): void
    {
        /*
         * Kotak teksnya hanya membawa nama dan nomor; kolom `email` tidak
         * punya tempat di dalamnya. Hari ini belum ada kode yang mengisinya,
         * jadi belum ada yang hilang — tetapi kolomnya ada dan suatu hari akan
         * terisi, dan saat itu menyimpan ulang daftar nama akan menghapus
         * email seluruh rombongan tanpa satu pun pesan.
         */
        [$orang, $baris] = $this->rombongan(4);

        PendaftaranPeserta::create([
            'layanan' => 'scopus_camp', 'pendaftaran_id' => (string) $baris->getKey(),
            'urutan' => 1, 'nama' => 'Budi', 'email' => 'budi@contoh.test', 'telp' => '6281234567890',
        ]);

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => "Budi, 081234567890\nSiti"]
        )->assertRedirect();

        $peserta = PendaftaranPeserta::milik('scopus_camp', (string) $baris->getKey())->terurut()->get();

        $this->assertSame('budi@contoh.test', $peserta[0]->email,
            'Email peserta hilang saat daftarnya disimpan ulang.');

        // Nama yang BERGANTI di urutan itu tidak boleh mewarisi email orang lain.
        $this->assertNull($peserta[1]->email);
    }

    #[Test]
    public function email_tidak_diwariskan_ke_orang_yang_berbeda(): void
    {
        [$orang, $baris] = $this->rombongan(4);

        PendaftaranPeserta::create([
            'layanan' => 'scopus_camp', 'pendaftaran_id' => (string) $baris->getKey(),
            'urutan' => 1, 'nama' => 'Budi', 'email' => 'budi@contoh.test',
        ]);

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => 'Dewi Lestari']
        )->assertRedirect();

        $satu = PendaftaranPeserta::milik('scopus_camp', (string) $baris->getKey())->firstOrFail();

        $this->assertSame('Dewi Lestari', $satu->nama);
        $this->assertNull($satu->email,
            'Email Budi ikut menempel ke Dewi hanya karena urutannya sama.');
    }

    #[Test]
    public function perubahan_daftarnya_meninggalkan_jejak(): void
    {
        [$orang, $baris] = $this->rombongan(5);

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => "Budi\nSiti"]
        )->assertRedirect();

        $jejak = \App\PendaftaranJejak::milik('scopus_camp', (string) $baris->getKey())
            ->where('aksi', 'peserta')->firstOrFail();

        $this->assertStringContainsString('0 → 2 nama', $jejak->kalimat);
        $this->assertStringContainsString('oleh ' . $orang->full_name, $jejak->kalimat);
    }

    #[Test]
    public function pelanggan_tidak_boleh_mengubah_daftar_peserta(): void
    {
        [$orang, $baris] = $this->rombongan(4);

        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        $this->actingAs($pelanggan)->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => 'Orang Asing']
        )->assertRedirect(route('account.dashboard.index'));

        $this->assertSame(0,
            PendaftaranPeserta::milik('scopus_camp', (string) $baris->getKey())->count());

        \Illuminate\Support\Facades\Auth::logout();

        $this->put(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            ['peserta' => 'Orang Asing']
        )->assertRedirect();

        $this->assertSame(0,
            PendaftaranPeserta::milik('scopus_camp', (string) $baris->getKey())->count());
    }

    #[Test]
    public function borangnya_tergambar_di_tab_peserta(): void
    {
        [$orang, $baris] = $this->rombongan(4);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $baris->getKey()]))
            ->assertOk()->getContent();

        /*
         * Diperiksa TAG PEMBUKA borangnya, bukan sekadar alamat rutenya muncul
         * di suatu tempat.
         *
         * Percobaan pertama cuma mencari alamat rute dan name="peserta", dan
         * tetap hijau sesudah <form> sengaja diganti jadi <div>: alamatnya ada
         * di baris berikutnya, jadi ia tetap tercetak. Yang tinggal cuma kotak
         * teks yang tidak bisa dikirim ke mana pun — persis keadaan yang
         * hendak diperbaiki.
         */
        $ada = preg_match('/<form class="rin-peserta-borang"[^>]*>(?<dalam>.{0,900})/s', $isi, $borang);

        $this->assertSame(1, $ada,
            'Borang daftar peserta tidak tergambar; panitia tidak punya jalan mengisinya.');

        $this->assertStringContainsString('method="POST"', $borang[0]);
        $this->assertStringContainsString(
            route('account.pendaftaran-layanan.peserta', ['scopus_camp', $baris->getKey()]),
            $borang[0],
            'Borangnya tidak menuju penyimpan daftar peserta.'
        );

        // PUT, bukan POST murni: rutenya PUT, dan tanpa penyelundupnya
        // kiriman borang akan ditolak 405 tanpa penjelasan apa pun.
        $this->assertStringContainsString('name="_method" value="PUT"', $borang['dalam']);

        $this->assertStringContainsString('name="peserta"', $isi);

        // Jalur Excel/CSV ikut ada, menunjuk ke pembaca yang sama dengan borang Tambah.
        $this->assertStringContainsString(
            route('account.pendaftaran-layanan.baca-peserta'), $isi,
            'Jalur Excel/CSV hilang dari borang peserta.'
        );

        /*
         * Kalimat lama tidak boleh kembali. "Tanyakan sisanya ke pendaftarnya"
         * menyuruh menunggu orang lain, di layar orang yang sekarang bisa
         * mengerjakannya sendiri.
         */
        $this->assertStringNotContainsString('Tanyakan sisanya ke pendaftarnya', $isi);
    }

    // ------------------------------------------------------------- pembantu

    private function akun(string $peran): User
    {
        $u = User::create([
            'full_name' => 'Rina Panitia', 'username' => 'peserta_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'), 'level' => 'user',
        ]);

        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => $peran])->save();

        return $u->refresh();
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp} */
    private function rombongan(int $jumlah): array
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $t = Str::random(8);

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Uji ' . $t,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ]);

        $baris = PendaftaranScopusCamp::create([
            'id_transaksi' => 'T-' . $t, 'kategori_id' => $angkatan->id,
            'nama' => 'Pemesan ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0001', 'jumlah_pendaftar' => (string) $jumlah,
            'total_pembayaran' => '5500000', 'status' => 'diproses',
        ]);

        return [$orang, $baris];
    }
}
