<?php

namespace Tests\Feature\ClinikScopus;

use App\ClinikScopusBiayaPersesi as T;
use App\KategoriLayanan;
use App\Layanan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Audit ronde kedua layar tarif, 30 Sep 2026.
 *
 * Tiga temuannya cacat yang dibuat pada ronde pertama — termasuk satu yang
 * mengulang kesalahan yang baru saja diperbaiki: menyembunyikan sesuatu tanpa
 * jalan kembali.
 */
class TarifAuditDuaTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Layanan::lupakanKatalog();

        // Uji di berkas ini ditulis dengan andaian Scopus Kafe BELUM
        // bervarian — lihat tanpaVarian() di Tests\TestCase.
        $this->tanpaVarian('scopus_kafe');
        // Penanda "jadwal sudah diperiksa" berumur satu permintaan di produksi,
        // tetapi satu PROSES di uji — tanpa dibuang, uji berikutnya tidak
        // pernah menaikkan tarif terjadwalnya.
        \App\ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();
    }

    private function akun(string $peran = User::PERAN_ADMINISTRATOR): User
    {
        $u = User::create([
            'full_name' => 'Uji ' . Str::random(4),
            'username' => 'uji_a2_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $u->forceFill(['status' => 'active', 'email_verified_at' => now(), 'peran' => $peran])->save();

        return $u->refresh();
    }

    // -------------------------------------------- tarif yatim karena varian

    #[Test]
    public function menambah_varian_memindahkan_tarif_lama_bukan_meninggalkannya(): void
    {
        /*
         * Temuan paling berbahaya ronde kedua, karena akibatnya DIAM.
         *
         * Tarif Scopus Kafe menyimpan varian NULL. Begitu layanannya diberi
         * varian, pencari tarif tidak menemukannya lagi: harganya lenyap dari
         * borang angkatan, kartunya berubah jadi "Belum disetel", sementara
         * barisnya tetap berstatus aktif selamanya — hantu yang hidup lagi
         * kalau variannya dibuang. Tidak ada galat apa pun.
         */
        $admin = $this->akun();
        $lama = T::berlaku('scopus_kafe');
        $layanan = Layanan::where('kode', 'scopus_kafe')->first();

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_kafe', 'token' => Str::random(30),
            'nama' => 'Kafe Uji', 'mulai' => now(), 'status' => 'draft',
        ]);

        $this->actingAs($admin)->post(route('account.layanan.update', $layanan), [
            'nama' => 'Scopus Kafe', 'satuan' => 'per pertemuan',
            'ikon' => 'fa-coffee', 'warna' => 'mis-jingga', 'aktif' => 1,
            'varian' => "Tatap Muka\nDaring",
        ])->assertSessionHasNoErrors();

        Layanan::lupakanKatalog();

        $this->assertSame('tatap_muka', $lama->refresh()->varian,
            'Tarif lamanya harus pindah ke varian pertama, bukan ditinggalkan.');

        $this->assertSame('tatap_muka', $angkatan->refresh()->varian,
            'Angkatannya harus ikut pindah; kalau tidak ia menunjuk varian yang tarifnya tidak ada.');

        $this->assertNotNull(T::berlaku('scopus_kafe', 'tatap_muka'),
            'Harganya harus tetap ketemu sesudah variannya ditambah.');

        $this->assertSame(0, T::untuk('scopus_kafe')->whereNull('varian')->count(),
            'Tidak boleh ada tarif tanpa varian tersisa — itulah hantunya.');
    }

    #[Test]
    public function pemindahan_varian_tidak_menyentuh_layanan_lain(): void
    {
        $admin = $this->akun();
        $clinik = T::berlaku('clinik_scopus');
        $layanan = Layanan::where('kode', 'scopus_kafe')->first();

        $this->actingAs($admin)->post(route('account.layanan.update', $layanan), [
            'nama' => 'Scopus Kafe', 'satuan' => 'per pertemuan',
            'ikon' => 'fa-coffee', 'warna' => 'mis-jingga', 'aktif' => 1,
            'varian' => 'Tatap Muka',
        ]);

        $this->assertNull($clinik->refresh()->varian, 'Clinik Scopus tidak punya varian dan tidak boleh diberi.');
    }

    // ------------------------------------------------- membatalkan jadwal

    #[Test]
    public function tarif_terjadwal_punya_barisnya_sendiri_di_layar(): void
    {
        /*
         * Tabel riwayat hanya memuat baris berstatus nonaktif, jadi tanpa
         * bagian tersendiri tarif terjadwal tidak punya baris di mana pun —
         * kartunya mengumumkan kenaikan yang tidak bisa dibatalkan siapa pun.
         */
        $admin = $this->akun();

        T::create([
            'layanan' => 'scopus_kafe', 'biaya_persesi' => 1500000,
            'berlaku_mulai' => now()->addMonth(), 'status' => T::TERJADWAL,
        ]);

        $halaman = $this->actingAs($admin)->get(route('account.Clinik-Scopus-Biaya-Persesi.index'));

        $halaman->assertOk();
        $halaman->assertSee('Kenaikan yang sudah dijadwalkan', false);
        $halaman->assertSee('Batalkan', false);
        $this->assertCount(1, $halaman->viewData('terjadwal'));
    }

    #[Test]
    public function membatalkan_jadwal_tidak_mengubah_harga_yang_berlaku(): void
    {
        $admin = $this->akun();
        $sekarang = T::berlaku('scopus_kafe');

        $jadwal = T::create([
            'layanan' => 'scopus_kafe', 'biaya_persesi' => 1500000,
            'berlaku_mulai' => now()->addMonth(), 'status' => T::TERJADWAL,
        ]);

        $jawab = $this->actingAs($admin)
            ->deleteJson(route('account.Clinik-Scopus-Biaya-Persesi.destroy', $jadwal));

        $jawab->assertOk()->assertJson(['success' => true]);
        $this->assertStringContainsString('dibatalkan', $jawab->json('message'));
        $this->assertNull(T::find($jadwal->getKey()));
        $this->assertTrue(T::berlaku('scopus_kafe')->is($sekarang));
    }

    // ---------------------------------------------------- jejak perbaikan

    #[Test]
    public function memperbaiki_tarif_mencatat_yang_terakhir_mengubahnya(): void
    {
        /*
         * Jejak yang mencatat pembuat baris menunjuk orang keliru: memperbaiki
         * justru cara tarif paling sering berubah, dan "disetel oleh A" pada
         * angka yang ditulis B adalah jejak yang menyesatkan.
         */
        $pertama = $this->akun();
        $kedua = $this->akun();

        $this->actingAs($pertama)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'layanan' => 'scopus_kafe', 'biaya_persesi' => '1.000.000',
        ]);

        $tarif = T::berlaku('scopus_kafe');
        $this->assertSame($pertama->id, (int) $tarif->penginput_id);

        /*
         * Sesi permintaan sebelumnya masih memegang pengguna pertama, jadi
         * actingAs() saja tidak cukup — permintaan keduanya akan tertolak ke
         * /login tanpa galat maupun pesan, dan ujinya gagal seolah kodenya yang
         * salah. Ini jebakan uji, bukan cacat aplikasi.
         */
        $this->flushSession();

        $this->actingAs($kedua)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'layanan' => 'scopus_kafe', 'biaya_persesi' => '1.000.000',
            'ppn' => 11, 'perbaiki' => $tarif->getKey(),
        ]);

        $this->assertSame($kedua->id, (int) $tarif->refresh()->penginput_id);
        $this->assertSame(11, $tarif->ppn_persen, 'Perbaikannya memang harus berlaku.');
    }

    // ------------------------------------------------- keadaan serba kosong

    #[Test]
    public function saat_semua_layanan_nonaktif_layarnya_tidak_berpura_pura_baik(): void
    {
        /*
         * Dulu berbunyi "Semua 0 tarif sudah disetel" lengkap dengan centang
         * hijau — kalimat gembira untuk keadaan yang justru paling perlu
         * diperhatikan.
         */
        $admin = $this->akun();
        Layanan::query()->update(['aktif' => false]);
        Layanan::lupakanKatalog();

        $halaman = $this->actingAs($admin)->get(route('account.Clinik-Scopus-Biaya-Persesi.index'));

        $halaman->assertOk();
        $halaman->assertSee('Belum ada layanan aktif', false);
        $halaman->assertSee('Belum ada layanan yang dijual', false);
        $halaman->assertDontSee('Semua 0 tarif sudah disetel', false);
        $this->assertSame(0, $halaman->viewData('totalKartu'));
    }

    // ------------------------------------------------------------- cetak

    #[Test]
    public function daftar_harga_pdf_serupa_dengan_ekspor_data_pelanggan(): void
    {
        /*
         * Cetaknya dulu window.print() dengan gaya @media print, dan hasilnya
         * tidak serupa dengan berkas lain yang keluar dari MIS: tanpa logo,
         * tanpa kepala berulang, tanpa kaki. Sekarang memakai cetakan yang
         * sama dengan ekspor Data Pelanggan.
         */
        $admin = $this->akun();

        $jawab = $this->actingAs($admin)->get(route('account.Clinik-Scopus-Biaya-Persesi.cetak'));

        $jawab->assertOk();
        $jawab->assertHeader('Content-Type', 'application/pdf');
        // Respons biasa, bukan streamed: dompdf->output() dikirim lewat response().
        $this->assertStringStartsWith('%PDF', $jawab->getContent());

        $nama = $jawab->headers->get('Content-Disposition');
        $this->assertStringContainsString('daftar-harga-layanan-', $nama);
        $this->assertStringContainsString('.pdf', $nama);
    }

    #[Test]
    public function cetakan_pdf_memuat_seluruh_fasilitas_tanpa_dipotong(): void
    {
        /*
         * Kartu di layar memotong daftar di empat butir. Cetakannya mengambil
         * datanya sendiri, jadi tidak boleh ikut terpotong — daftar harga yang
         * menyembunyikan sebagian isinya lebih berbahaya daripada tidak ada.
         */
        $tarif = T::berlaku('scopus_camp', 'jawa');
        $this->assertGreaterThan(4, count($tarif->daftar_fasilitas),
            'Uji ini perlu layanan dengan lebih dari empat fasilitas.');

        $html = view('account.clinik_scopus_biaya_persesi.cetak-pdf', [
            'baris' => [[
                'nama' => 'Scopus Camp',
                'namaVarian' => 'Pulau Jawa',
                'satuan' => 'per peserta',
                'tarif' => $tarif,
            ]],
        ])->render();

        foreach ($tarif->daftar_fasilitas as $f) {
            $this->assertStringContainsString(e($f), $html);
        }

        $this->assertStringContainsString('Daftar Harga Layanan', $html);
        $this->assertStringContainsString('MIS Rumah Scopus Foundation', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html, 'Logonya harus disisipkan.');
    }

    #[Test]
    public function cetakan_pdf_hanya_memuat_layanan_yang_tarifnya_sudah_disetel(): void
    {
        // "Belum disetel" itu peringatan untuk admin, bukan keterangan untuk
        // calon peserta yang menerima lembarnya.
        $admin = $this->akun();

        $halaman = $this->actingAs($admin)->get(route('account.Clinik-Scopus-Biaya-Persesi.index'));
        $adaYangKosong = $halaman->viewData('totalKartu') > $halaman->viewData('adaTarif');

        $this->assertTrue($adaYangKosong, 'Uji ini perlu setidaknya satu layanan tanpa tarif.');

        $berlaku = T::semuaYangBerlaku();
        $baris = [];

        foreach (\App\Layanan::katalog() as $kunci => $tentang) {
            foreach (($tentang['varian'] ?: [null => null]) as $kv => $nv) {
                if ($t = ($berlaku[$kunci . '|' . ($kv ?: '')] ?? null)) {
                    $baris[] = ['nama' => $tentang['nama'], 'namaVarian' => $nv,
                        'satuan' => $tentang['satuan'], 'tarif' => $t];
                }
            }
        }

        $html = view('account.clinik_scopus_biaya_persesi.cetak-pdf', ['baris' => $baris])->render();

        $this->assertStringNotContainsString('Belum disetel', $html);
        $this->assertSame($halaman->viewData('adaTarif'), count($baris));
    }

    #[Test]
    public function karyawan_boleh_mengunduh_daftar_harga(): void
    {
        // Daftar harga dibagikan ke luar; yang boleh melihat tarif boleh
        // mengunduhnya. Yang dibatasi mengubahnya, bukan membacanya.
        $karyawan = $this->akun(User::PERAN_KARYAWAN);

        $this->actingAs($karyawan)
            ->get(route('account.Clinik-Scopus-Biaya-Persesi.cetak'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    // ------------------------------------------------------------ pencarian

    #[Test]
    public function saringan_berbentuk_sama_dengan_data_pelanggan(): void
    {
        /*
         * Bentuknya disamakan dengan .pel-saring-kartu: kartu putih berlabel,
         * tombol hapus ketikan di dalam kotaknya, tombol Reset, dan keadaan
         * kosong "Tidak ada yang cocok" dengan kalimat yang sama.
         *
         * Diperiksa dari markahnya, bukan dari gayanya: ukurannya sudah
         * dibandingkan langsung di peramban, tetapi bagian-bagiannya yang
         * gampang hilang saat layarnya disunting lagi.
         */
        $admin = $this->akun();

        $isi = $this->actingAs($admin)
            ->get(route('account.Clinik-Scopus-Biaya-Persesi.index'))->getContent();

        $this->assertStringContainsString('tar-saring-kartu', $isi, 'Kotak cari harus di dalam kartu saringan.');
        $this->assertStringContainsString('id="tar-reset"', $isi, 'Tombol Reset harus ada.');
        $this->assertStringContainsString('id="tar-kosong-cari"', $isi, 'Keadaan kosong pencarian harus ada.');

        // Kalimatnya persis sama dengan Data Pelanggan.
        $this->assertStringContainsString('Tidak ada yang cocok', $isi);
        $this->assertStringContainsString('Coba kata kunci lain, atau hapus saringannya.', $isi);

        /*
         * Keseragamannya dijaga dengan membandingkan KEDUA layar, bukan
         * menyalin kalimatnya ke uji: disalin, mengubah salah satu layar saja
         * tetap lolos dan keduanya diam-diam berbeda lagi.
         */
        // DENGAN kata kunci yang tidak mungkin ada: tanpa saringan, keadaan
        // kosongnya memang tidak dirender sama sekali.
        $pelanggan = $this->actingAs($admin)
            ->get(route('account.customer.index', ['cari' => 'zzzqqqxxx-tidak-mungkin-ada']))
            ->getContent();

        foreach (['Tidak ada yang cocok', 'Coba kata kunci lain, atau hapus saringannya.',
                  'mis-kosong-cari', 'Hapus saringan'] as $bagian) {
            $this->assertStringContainsString($bagian, $pelanggan,
                'Data Pelanggan harus memakai bentuk yang sama: ' . $bagian);
        }

        // Labelnya ada; kotak cari tanpa label hanya bisa ditebak dari
        // placeholder, yang hilang begitu diketik.
        $this->assertMatchesRegularExpression('/<label[^>]*for="tar-cari"/', $isi);
    }

    #[Test]
    public function reset_menunjuk_layar_yang_sama_tanpa_saringan(): void
    {
        // Alamatnya tetap ada sebagai cadangan kalau skripnya tidak termuat;
        // dengan skrip, pengosongannya dikerjakan di tempat.
        $admin = $this->akun();

        $this->actingAs($admin)
            ->get(route('account.Clinik-Scopus-Biaya-Persesi.index'))
            ->assertSee('href="' . route('account.Clinik-Scopus-Biaya-Persesi.index') . '"', false);
    }

    // ------------------------------------------------- audit ronde keempat

    #[Test]
    public function pencarian_menjangkau_fasilitas_bukan_cuma_nama(): void
    {
        /*
         * Orang mencari layanan lewat apa yang didapat peserta. Mengetik
         * "penginapan" dulu tidak menemukan apa pun padahal itu fasilitas
         * Scopus Camp Pulau Jawa.
         */
        $admin = $this->akun();

        $isi = $this->actingAs($admin)
            ->get(route('account.Clinik-Scopus-Biaya-Persesi.index'))->getContent();

        preg_match_all('/data-cari="([^"]*)"/', $isi, $m);
        $gabungan = strtolower(implode(' | ', $m[1]));

        $this->assertStringContainsString('penginapan', $gabungan,
            'Fasilitas harus ikut dicari.');
        $this->assertStringContainsString('per peserta', $gabungan,
            'Satuan harus ikut dicari.');
    }

    #[Test]
    public function keadaan_kosong_setingkat_kisi_selalu_dibungkus_kartu(): void
    {
        /*
         * Dibuat sesudah menemukan dua keadaan kosong beda bentuk di layar yang
         * sama: yang satu di dalam kartu, yang lain mengambang di latar.
         *
         * Yang diperiksa PERSIS keadaan yang menggantikan kisi — bukan semua
         * `.mis-kosong` di berkas. Percobaan pertama memindai seluruhnya dengan
         * jendela 220 aksara ke belakang, dan ia menandai dua keadaan kosong
         * riwayat yang sebenarnya SUDAH berada di dalam kartu bagiannya; jendela
         * sepanjang apa pun cuma memindah batas salahnya.
         */
        $isi = file_get_contents(
            resource_path('views/account/clinik_scopus_biaya_persesi/index.blade.php')
        );

        $this->assertMatchesRegularExpression(
            '/<div class="mis-bagian tar-kosong-semua"/',
            $isi,
            'Keadaan kosong "belum ada layanan" harus dibungkus kartu.'
        );

        $this->assertMatchesRegularExpression(
            '/<div class="mis-bagian[^"]*" id="tar-kosong-cari"/',
            $isi,
            'Keadaan kosong pencarian harus dibungkus kartu.'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/<div class="mis-kosong[^"]*tar-kosong-semua"/',
            $isi,
            'Tidak boleh ada keadaan kosong setingkat kisi yang tanpa kartu.'
        );
    }

    #[Test]
    public function riwayat_kosong_karena_saringan_tidak_bilang_belum_ada(): void
    {
        // Riwayatnya ADA, cuma bukan untuk layanan yang sedang disaring.
        $admin = $this->akun();

        T::query()->where('status', T::NONAKTIF)->update(['layanan' => 'scopus_camp']);

        $halaman = $this->actingAs($admin)->get(route('account.Clinik-Scopus-Biaya-Persesi.index', [
            'riwayat' => 'clinik_scopus',
        ]));

        $halaman->assertOk();
        $halaman->assertSee('Tidak ada yang cocok', false);
        $halaman->assertDontSee('Belum ada tarif lama', false);
    }

    #[Test]
    public function dialog_punya_nama_untuk_pembaca_layar(): void
    {
        // Tanpa aria-labelledby, pembaca layar mengumumkan "dialog" saja —
        // bukan "Scopus Camp — Pulau Jawa".
        $admin = $this->akun();

        $isi = $this->actingAs($admin)
            ->get(route('account.Clinik-Scopus-Biaya-Persesi.index'))->getContent();

        foreach ([['tar-dialog', 'tar-f-judul'], ['lyn-dialog', 'lyn-judul']] as [$dialog, $judul]) {
            $this->assertMatchesRegularExpression(
                '/id="' . $dialog . '"[^>]*aria-labelledby="' . $judul . '"/s',
                $isi,
                $dialog . ' harus dinamai oleh ' . $judul
            );
        }
    }

    #[Test]
    public function daftar_harga_pdf_menomori_halamannya(): void
    {
        /*
         * Ekspor Data Pelanggan menomori halamannya; daftar harga sempat tidak,
         * jadi "seragam" yang diklaim sebelumnya belum penuh — dan daftar yang
         * menyeberang beberapa halaman tidak bisa dicocokkan urutannya.
         */
        $blade = file_get_contents(
            resource_path('views/account/clinik_scopus_biaya_persesi/cetak-pdf.blade.php')
        );

        $this->assertStringContainsString('{PAGE_NUM}', $blade);
        $this->assertStringContainsString('{PAGE_COUNT}', $blade);
        $this->assertStringContainsString('page_text', $blade);

        // Jebakannya: penanda diukur pakai angka contoh, bukan apa adanya.
        $this->assertStringContainsString('str_repeat("0"', $blade,
            'Lebarnya harus diukur dengan angka contoh, bukan dengan penandanya.');
    }

    #[Test]
    public function kepala_menyebut_kapan_harga_terakhir_disentuh(): void
    {
        // Pertanyaan pertama saat seseorang curiga harganya berubah.
        $admin = $this->akun();

        $halaman = $this->actingAs($admin)->get(route('account.Clinik-Scopus-Biaya-Persesi.index'));

        $halaman->assertOk();
        $halaman->assertSee('Terakhir diubah', false);
        $this->assertNotNull($halaman->viewData('terakhir'));
    }

    // ----------------------------------------------------- tanggal & perbaiki

    #[Test]
    public function tanggal_bersama_perbaiki_ditolak_bukan_dibuang_diam_diam(): void
    {
        /*
         * "Perbaiki" membetulkan yang sedang berlaku; tanggal menjadwalkan yang
         * akan berlaku. Cabang perbaikan tidak memakai tanggalnya sama sekali,
         * jadi dibiarkan, tanggal yang dikirim hilang tanpa kabar apa pun.
         */
        $admin = $this->akun();
        $tarif = T::berlaku('scopus_kafe');

        $this->actingAs($admin)
            ->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
                'layanan' => 'scopus_kafe',
                'biaya_persesi' => number_format((int) $tarif->biaya_persesi, 0, ',', '.'),
                'perbaiki' => $tarif->getKey(),
                'berlaku_mulai' => now()->addMonth()->toDateString(),
            ])
            ->assertSessionHasErrors('berlaku_mulai');

        $this->assertSame(0, T::where('status', T::TERJADWAL)->count());
    }

    // ---------------------------------------------------------- keterangan

    #[Test]
    public function jumlah_seluruh_riwayat_disebut_bukan_hanya_halaman_ini(): void
    {
        $admin = $this->akun();
        $total = T::where('status', T::NONAKTIF)->count();

        $halaman = $this->actingAs($admin)->get(route('account.Clinik-Scopus-Biaya-Persesi.index'));

        $this->assertSame($total, $halaman->viewData('totalRiwayat'));
        $halaman->assertSee($total . ' tarif lama tersimpan', false);
    }

    #[Test]
    public function ppn_yang_lazim_ditawarkan_dari_layanan_lain(): void
    {
        // Ditawarkan, bukan disetel diam-diam: yang lupa mengisi PPN baru
        // ketahuan saat ada yang menghitung tagihan.
        $admin = $this->akun();

        DB::table('clinikscopus_biaya_persesi')
            ->where('layanan', 'scopus_kafe')->update(['ppn' => 11]);

        $halaman = $this->actingAs($admin)->get(route('account.Clinik-Scopus-Biaya-Persesi.index'));

        $this->assertSame(11, $halaman->viewData('ppnLazim'));
        $halaman->assertSee('pakai 11%', false);
    }
}
