<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\Actions\Pendaftaran\HapusPendaftaran;
use App\KategoriLayanan;
use App\PembayaranPendaftaran;
use App\PendaftaranDihapus;
use App\PendaftaranJejak;
use App\PendaftaranPengembalian;
use App\PendaftaranScopusCamp;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Menghapus pendaftaran tidak lagi menelan uangnya tanpa bekas.
 *
 * Sebelum ini penghapusan membuang baris pembayaran atas nama pendaftaran
 * itu, dan angkanya keluar dari pembukuan tanpa menyisakan apa pun. Layar
 * rinciannya mencatat dengan teliti tiap medan yang disunting, tiap
 * percobaan kirim surat, dan tiap rupiah yang dikembalikan — lalu satu klik
 * menghapus seluruhnya, dan tidak ada satu layar pun yang bisa menjawab
 * "ke mana perginya".
 */
class CatatanPenghapusanTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function uang_yang_ikut_terhapus_tercatat_angkanya(): void
    {
        [$admin, $b] = $this->dengan();

        PembayaranPendaftaran::create([
            'jenis' => PembayaranPendaftaran::PENDAFTARAN,
            'induk_id' => (string) $b->getKey(),
            'urutan' => 1, 'nominal' => 2500000, 'tanggal' => now()->toDateString(),
        ]);
        PembayaranPendaftaran::create([
            'jenis' => PembayaranPendaftaran::PENDAFTARAN,
            'induk_id' => (string) $b->getKey(),
            'urutan' => 2, 'nominal' => 1000000, 'tanggal' => now()->toDateString(),
        ]);

        $this->actingAs($admin)->hapus($b);

        $arsip = $this->arsip($b);

        $this->assertSame(3500000, $arsip->uang_terhapus,
            'Uang yang ikut terhapus tidak tercatat; angkanya keluar dari pembukuan tanpa bekas.');
        $this->assertSame(2, $arsip->jumlah_pembayaran);
        $this->assertSame($admin->full_name, $arsip->oleh_nama,
            'Siapa yang menghapus tidak tercatat — padahal itu yang ditanya kalau ada selisih uang.');
    }

    /**
     * `total` cuma TAGIHAN, dan tagihan yang belum dibayar sepeser pun tidak
     * meninggalkan lubang di pembukuan. Dua angka yang berbeda, dan
     * menyamakannya membuat setiap penghapusan tampak merugikan.
     */
    #[Test]
    public function tagihan_tanpa_pembayaran_bukan_uang_yang_hilang(): void
    {
        [$admin, $b] = $this->dengan();

        $this->actingAs($admin)->hapus($b);

        $arsip = $this->arsip($b);

        $this->assertSame(0, $arsip->uang_terhapus);
        $this->assertSame(5500000, $arsip->total, 'Tagihannya tetap dicatat sebagai keterangan.');
    }

    /**
     * INI yang paling mudah terlewat, dan sempat terlewat.
     *
     * Terukur di basis data ini: NOL baris termin untuk pendaftaran,
     * sementara 172 pendaftaran berstatus lunas. Versi pertama catatan
     * penghapusan menjumlahkan baris termin — jadi untuk SETIAP pendaftaran
     * yang ada sekarang angkanya nol, dan peringatan "Yang ikut hilang"
     * tidak akan pernah muncul sekali pun.
     *
     * Padahal menghapusnya betul-betul mengurangi ringkasan "uang masuk" di
     * layar daftar, yang menghitung dari tagihan baris berstatus lunas.
     */
    #[Test]
    public function lunas_tanpa_termin_tetap_terhitung_uangnya(): void
    {
        [$admin, $b] = $this->dengan();

        $b->forceFill(['status' => Pendaftaran::statusLunas('scopus_camp')])->save();

        $this->assertSame(0, PembayaranPendaftaran::milik(
            PembayaranPendaftaran::PENDAFTARAN, (string) $b->getKey()
        )->count(), 'prasyarat: memang tidak ada termin yang dicatat');

        $this->actingAs($admin)->hapus($b->refresh());

        $this->assertSame(5500000, $this->arsip($b)->uang_terhapus,
            'Pendaftaran lunas tanpa termin tercatat hilang TANPA uang, padahal '
            . 'ringkasan uang masuk ikut berkurang sebesar tagihannya.');
    }

    #[Test]
    public function layarnya_memperingatkan_juga_untuk_lunas_tanpa_termin(): void
    {
        [$admin, $b] = $this->dengan();

        $b->forceFill(['status' => Pendaftaran::statusLunas('scopus_camp')])->save();

        $panel = $this->panelHapus($this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->getKey()]))
            ->assertOk()->getContent());

        $this->assertStringContainsString('Yang ikut hilang', $panel,
            'Peringatannya tidak muncul untuk pendaftaran lunas tanpa termin — '
            . 'yaitu SELURUH pendaftaran lunas yang ada sekarang.');
        $this->assertStringContainsString('Rp 5.500.000', $panel);
    }

    #[Test]
    public function jejaknya_ikut_dipotret_supaya_masih_terbaca(): void
    {
        [$admin, $b] = $this->dengan();

        PendaftaranJejak::create([
            'layanan' => 'scopus_camp', 'pendaftaran_id' => (string) $b->getKey(),
            'aksi' => 'status', 'dari' => 'diproses', 'ke' => 'Pendaftaran Diterima',
            'oleh_nama' => 'Panitia Lama',
        ]);

        $this->actingAs($admin)->hapus($b);

        $arsip = $this->arsip($b);

        $this->assertSame(1, $arsip->jumlah_jejak);
        $this->assertStringContainsString('Panitia Lama', json_encode($arsip->potret),
            'Riwayatnya tidak ikut dipotret; begitu pendaftarannya hilang, '
            . 'tidak ada lagi halaman yang bisa membukanya.');
    }

    /**
     * Catatan pengembalian dananya ikut dihapus — induknya ditunjuk pasangan
     * (layanan, pendaftaran_id) tanpa kunci asing, jadi basis datanya tidak
     * akan membersihkannya sendiri. Angkanya wajib sudah masuk potret.
     */
    #[Test]
    public function pengembalian_dana_ikut_dipotret_lalu_dibuang(): void
    {
        [$admin, $b] = $this->dengan();

        PendaftaranPengembalian::create([
            'layanan' => 'scopus_camp', 'pendaftaran_id' => (string) $b->getKey(),
            'nominal' => 750000, 'tanggal' => now()->toDateString(),
        ]);

        $this->actingAs($admin)->hapus($b);

        $this->assertSame(0, PendaftaranPengembalian::milik('scopus_camp', (string) $b->getKey())->count(),
            'Catatan pengembaliannya tertinggal sebagai baris yatim.');

        $this->assertStringContainsString('750000', json_encode($this->arsip($b)->potret),
            'Pengembalian dananya hilang tanpa masuk potret.');
    }

    /**
     * Arsip yang menyebut pendaftaran yang sebenarnya masih ada lebih
     * menyesatkan daripada tidak ada arsip sama sekali.
     */
    #[Test]
    public function penghapusan_yang_batal_tidak_meninggalkan_arsip(): void
    {
        [$admin, $b] = $this->dengan();

        $sebelum = PendaftaranDihapus::count();

        $this->actingAs($admin);
        $hasil = (new HapusPendaftaran)->jalankan('scopus_camp', 'bukan-id-yang-ada');

        $this->assertFalse($hasil['berhasil']);
        $this->assertSame($sebelum, PendaftaranDihapus::count());
        $this->assertNotNull($b->fresh(), 'prasyarat: yang asli tidak ikut terhapus');
    }

    #[Test]
    public function layarnya_menyebut_uang_yang_akan_hilang_sebelum_tombolnya(): void
    {
        [$admin, $b] = $this->dengan();

        PembayaranPendaftaran::create([
            'jenis' => PembayaranPendaftaran::PENDAFTARAN,
            'induk_id' => (string) $b->getKey(),
            'urutan' => 1, 'nominal' => 1250000, 'tanggal' => now()->toDateString(),
        ]);

        $isi = $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->getKey()]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Rp 1.250.000', $isi,
            'Nominal yang akan hilang tidak disebut; yang menekan tombol merah '
            . 'tidak pernah tahu berapa yang ia hapus.');

        $panel = $this->panelHapus($isi);

        $this->assertStringContainsString('Yang ikut hilang', $panel);
        $this->assertStringContainsString('Rp 1.250.000', $panel,
            'Angkanya ada di halaman tapi tidak di tab Hapus — tempat ia dibaca.');
    }

    #[Test]
    public function layarnya_tidak_mengarang_angka_untuk_yang_belum_bayar(): void
    {
        [$admin, $b] = $this->dengan();

        $panel = $this->panelHapus($this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->getKey()]))
            ->assertOk()->getContent());

        $this->assertStringNotContainsString('Yang ikut hilang', $panel,
            'Daftar kehilangan muncul padahal tidak ada apa-apa yang hilang.');
    }

    #[Test]
    public function catatannya_hanya_untuk_administrator(): void
    {
        [$admin, $b] = $this->dengan();

        $this->actingAs($admin)->get(route('account.pendaftaran-layanan.terhapus'))->assertOk();

        /*
         * Sesi permintaan pertama masih memegang pengguna sebelumnya; tanpa
         * ini permintaan berikutnya tertolak ke /login tanpa pesan — dan
         * penjaga perannya jadi hijau karena alasan yang keliru.
         */
        $this->flushSession();

        $biasa = User::create([
            'full_name' => 'Karyawan Uji', 'username' => 'kar_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'), 'level' => 'user',
        ]);
        $biasa->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_KARYAWAN])->save();

        $this->actingAs($biasa)
            ->get(route('account.pendaftaran-layanan.terhapus'))
            ->assertRedirect(route('account.pendaftaran-layanan.index'));
    }

    #[Test]
    public function catatannya_menampilkan_yang_sudah_dihapus(): void
    {
        [$admin, $b] = $this->dengan();

        PembayaranPendaftaran::create([
            'jenis' => PembayaranPendaftaran::PENDAFTARAN,
            'induk_id' => (string) $b->getKey(),
            'urutan' => 1, 'nominal' => 400000, 'tanggal' => now()->toDateString(),
        ]);

        $nomor = $b->id_transaksi;
        $this->actingAs($admin)->hapus($b);

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus', ['cari' => $nomor]))
            ->assertOk()
            /*
             * Judulnya ikut dituntut. Berkas ini sempat memakai
             * @section('main') — nama bagian dari proyek lain — dan tata
             * letaknya hanya mengenal 'content': halamannya terbit 200 dengan
             * kerangka lengkap dan ISI KOSONG, tanpa galat apa pun.
             */
            ->assertSee('Catatan penghapusan')
            ->assertSee(strtoupper($nomor))
            ->assertSee('Rp 400.000')
            // Disebut apa adanya: orang yang membuka layar bernama "catatan
            // penghapusan" wajar mengira ini tong sampah.
            ->assertSee('tidak bisa dikembalikan', false);
    }

    /**
     * Arsipnya bisa diunduh untuk direkap.
     *
     * Yang dibutuhkan saat menutup buku bukan membaca dua puluh baris per
     * halaman melainkan MENJUMLAHKAN: berapa uang yang keluar dari pembukuan
     * bulan ini, dan oleh siapa.
     */
    #[Test]
    public function catatannya_bisa_diunduh(): void
    {
        [$admin, $b] = $this->dengan();

        $this->actingAs($admin)->hapus($b);

        $jawab = $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus.excel'));

        $jawab->assertOk();

        $this->assertStringContainsString('spreadsheet',
            strtolower((string) $jawab->headers->get('content-type')),
            'Unduhannya bukan lembar kerja.');
    }

    #[Test]
    public function unduhannya_juga_hanya_untuk_administrator(): void
    {
        [$admin] = $this->dengan();

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus.excel'))->assertOk();

        $this->flushSession();

        $biasa = User::create([
            'full_name' => 'Karyawan Uji', 'username' => 'kry_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'), 'level' => 'user',
        ]);
        $biasa->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_KARYAWAN])->save();

        /*
         * Diperiksa TERPISAH dari layarnya: menutup halaman tanpa menutup
         * unduhannya meninggalkan seluruh datanya tetap bisa diambil — dan
         * isi berkas ini email serta nominal orang yang barisnya sudah tidak
         * ada.
         */
        $this->actingAs($biasa)
            ->get(route('account.pendaftaran-layanan.terhapus.excel'))
            ->assertRedirect(route('account.pendaftaran-layanan.index'));
    }

    // ------------------------------------------------------------- pembantu

    private function hapus(PendaftaranScopusCamp $b): void
    {
        $hasil = (new HapusPendaftaran)->jalankan('scopus_camp', (string) $b->getKey());

        $this->assertTrue($hasil['berhasil'], $hasil['pesan']);
    }

    private function arsip(PendaftaranScopusCamp $b): PendaftaranDihapus
    {
        $arsip = PendaftaranDihapus::where('pendaftaran_id', (string) $b->getKey())->first();

        $this->assertNotNull($arsip, 'Penghapusannya tidak meninggalkan catatan apa pun.');

        return $arsip;
    }

    /**
     * Isi panel tab Hapus saja.
     *
     * Dipotong, bukan dicari di seluruh halaman: nominal yang sama juga
     * tergambar di tab Ringkasan dan tab Pembayaran, jadi penjaga yang
     * menjaring seluruh halaman tetap hijau walaupun tab Hapus kosong
     * melompong.
     */
    private function panelHapus(string $isi): string
    {
        $awal = strpos($isi, 'id="rin-panel-hapus"');

        $this->assertNotFalse($awal, 'Panel tab Hapus tidak tergambar.');

        $akhir = strpos($isi, '</div>', strpos($isi, 'rin-borang-hapus') ?: $awal);

        return substr($isi, $awal, max(0, ($akhir ?: strlen($isi)) - $awal));
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp} */
    private function dengan(): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $t = Str::random(8);

        $admin = User::create([
            'full_name' => 'Admin Uji ' . $t, 'username' => 'adm_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $admin->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Uji ' . $t,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ]);

        $b = PendaftaranScopusCamp::create([
            'id_transaksi' => 'T-' . $t, 'kategori_id' => $angkatan->id,
            'nama' => 'Peserta ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0001', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => '5500000', 'status' => 'diproses',
        ]);

        return [$admin, $b];
    }
}
