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
    public function daftar_harga_cetak_memuat_seluruh_fasilitas(): void
    {
        /*
         * Kartu memotong fasilitas di empat butir untuk layar. Kalau
         * pemotongan itu terjadi di MARKAH, daftar harga yang dicetak diam-diam
         * tidak lengkap — Scopus Camp Pulau Jawa tercetak 4 dari 7 tanpa satu
         * pun tanda ada yang dipotong. Jadi semuanya dirender, dan yang
         * kelebihan disembunyikan lewat kelas.
         */
        $admin = $this->akun();

        $tarif = T::berlaku('scopus_camp', 'jawa');
        $this->assertGreaterThan(4, count($tarif->daftar_fasilitas),
            'Uji ini perlu layanan dengan lebih dari empat fasilitas.');

        $isi = $this->actingAs($admin)
            ->get(route('account.Clinik-Scopus-Biaya-Persesi.index'))->getContent();

        foreach ($tarif->daftar_fasilitas as $f) {
            $this->assertStringContainsString(e($f), $isi,
                'Fasilitas "' . $f . '" harus ada di markah, walau tersembunyi di layar.');
        }

        $this->assertStringContainsString('tar-lebih', $isi,
            'Yang kelebihan disembunyikan lewat kelas, bukan dibuang dari markah.');
    }

    #[Test]
    public function cetakan_menyembunyikan_yang_tidak_pantas_dibagikan(): void
    {
        // Rencana kenaikan, layanan tanpa tarif, tombol, dan saringan tidak
        // punya tempat di kertas yang diberikan ke calon peserta.
        $berkas = resource_path('views/account/clinik_scopus_biaya_persesi/index.blade.php');
        $isi = file_get_contents($berkas);

        $awal = strpos($isi, '@media print');
        $this->assertNotFalse($awal, 'Gaya cetaknya harus ada.');

        $blok = substr($isi, $awal, 1400);

        foreach (['.tar-atur', '.tar-jadwal', '.tar-kartu.kosong', '.tar-saring', '.tar-kaki'] as $sel) {
            $this->assertStringContainsString($sel, $blok, $sel . ' harus disembunyikan saat dicetak.');
        }

        $this->assertStringContainsString('.tar-kepala-cetak', $blok,
            'Kepala cetak berisi judul dan tanggal harus ditampilkan.');
    }

    #[Test]
    public function daftar_harga_cetak_menyebut_tanggalnya(): void
    {
        // Daftar harga tanpa tanggal tidak bisa dipercaya siapa pun yang
        // menerimanya seminggu kemudian.
        $admin = $this->akun();

        $this->actingAs($admin)
            ->get(route('account.Clinik-Scopus-Biaya-Persesi.index'))
            ->assertSee('Berlaku per ' . now()->locale('id')->translatedFormat('d F Y'), false);
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
