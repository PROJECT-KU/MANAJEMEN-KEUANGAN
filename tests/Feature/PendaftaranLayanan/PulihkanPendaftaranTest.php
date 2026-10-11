<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\Actions\Pendaftaran\HapusPendaftaran;
use App\Actions\Pendaftaran\PulihkanPendaftaran;
use App\KategoriLayanan;
use App\PembayaranPendaftaran;
use App\PendaftaranDihapus;
use App\PendaftaranJejak;
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
 * Pendaftaran yang dihapus bisa dikembalikan dari catatan penghapusan.
 *
 * Sebelum ini penghapusan permanen: layarnya sendiri menegaskan "tidak bisa
 * diurungkan, dan belum ada tong sampah". Satu salah klik pada pendaftaran
 * lunas berarti nama, peserta rombongan, termin, dan bukti bayarnya hilang
 * sekaligus — dan yang hilang baru ketahuan saat orangnya menanyakan
 * kursinya.
 *
 * Yang diuji di sini bukan "tombolnya ada" melainkan APA YANG KEMBALI.
 */
class PulihkanPendaftaranTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function barisnya_kembali_dengan_kunci_yang_sama(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();

        $this->actingAs($admin);
        $this->hapus($b);

        $this->assertNull(PendaftaranScopusCamp::find($id),
            'Barisnya masih ada; penghapusannya tidak jalan.');

        $hasil = (new PulihkanPendaftaran)->jalankan((string) $this->arsip($id)->getKey());

        $this->assertTrue($hasil['berhasil'], $hasil['pesan']);

        $kembali = PendaftaranScopusCamp::find($id);

        $this->assertNotNull($kembali, 'Barisnya tidak kembali.');

        /*
         * Kunci yang SAMA, bukan baris baru. Diterbitkan kunci baru, seluruh
         * rujukan yang menyimpan id lamanya — peserta, termin, jejak —
         * menunjuk baris yang tidak ada lagi.
         */
        $this->assertSame($id, (string) $kembali->getKey());
        $this->assertSame($b->id_transaksi, $kembali->id_transaksi);
        $this->assertSame('diproses', $kembali->status);
    }

    /** Peserta rombongan dan terminnya ikut kembali. */
    #[Test]
    public function peserta_dan_terminnya_ikut_kembali(): void
    {
        [$admin, $b] = $this->dengan(['jumlah_pendaftar' => '3']);
        $id = (string) $b->getKey();

        PendaftaranPeserta::create([
            'layanan' => 'scopus_camp', 'pendaftaran_id' => $id,
            'urutan' => 1, 'nama' => 'Peserta Dua', 'telp' => '08123456789',
        ]);

        PembayaranPendaftaran::create([
            'jenis' => PembayaranPendaftaran::PENDAFTARAN, 'induk_id' => $id,
            'urutan' => 1, 'nominal' => 400000, 'tanggal' => now()->toDateString(),
        ]);

        $this->actingAs($admin);
        $this->hapus($b);

        $this->assertSame(0, PendaftaranPeserta::milik('scopus_camp', $id)->count());

        (new PulihkanPendaftaran)->jalankan((string) $this->arsip($id)->getKey());

        $this->assertSame(1, PendaftaranPeserta::milik('scopus_camp', $id)->count(),
            'Nama peserta rombongannya tidak ikut kembali.');

        $this->assertSame(400000, (int) PembayaranPendaftaran::milik(
            PembayaranPendaftaran::PENDAFTARAN, $id
        )->sum('nominal'), 'Terminnya tidak ikut kembali, jadi uangnya hilang dari pembukuan.');
    }

    /**
     * Kuota angkatannya diambil LAGI.
     *
     * Penghapusan mengembalikan kursinya ke kuota. Pemulihan yang tidak
     * mengambilnya lagi membuat angkatan tampak longgar padahal kursinya
     * sudah terpakai — dan pendaftar berikutnya diterima melebihi kuota.
     */
    #[Test]
    public function kuota_angkatannya_diambil_lagi(): void
    {
        /*
         * Sisa kuotanya DIBUAT lebih kecil dari totalnya — 48 dari 50, meniru
         * angkatan yang dua kursinya memang sudah terpakai baris ini.
         *
         * Dibiarkan 50 dari 50, pengembalian kuota saat menghapus kena batas
         * totalnya dan angkanya tidak bergerak sama sekali; yang teruji
         * batas itu, bukan pemulihannya.
         */
        [$admin, $b] = $this->dengan(['jumlah_pendaftar' => '2'], ['sisa_kuota' => '48']);
        $id = (string) $b->getKey();
        $angkatan = KategoriLayanan::find($b->kategori_id);
        $sebelum = (int) $angkatan->sisa_kuota;

        $this->actingAs($admin);
        $this->hapus($b);

        $this->assertSame($sebelum + 2, (int) $angkatan->fresh()->sisa_kuota,
            'Penghapusannya tidak mengembalikan kuota; uji ini jadi tidak berarti.');

        (new PulihkanPendaftaran)->jalankan((string) $this->arsip($id)->getKey());

        $this->assertSame($sebelum, (int) $angkatan->fresh()->sisa_kuota,
            'Kursinya tidak diambil lagi, jadi angkatannya tampak longgar.');
    }

    /** Catatannya TIDAK hilang saat dipulihkan, cuma ditandai. */
    #[Test]
    public function catatannya_ditandai_bukan_dihapus(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();

        $this->actingAs($admin);
        $this->hapus($b);

        $arsip = $this->arsip($id);
        (new PulihkanPendaftaran)->jalankan((string) $arsip->getKey());

        $arsip->refresh();

        $this->assertNotNull($arsip->dipulihkan_at,
            'Catatannya tidak ditandai, jadi ia terlihat seperti masih terhapus.');
        $this->assertStringContainsString('Admin Uji', (string) $arsip->dipulihkan_oleh);
    }

    /** Dua kali pulih ditolak; yang kedua tidak menggandakan apa pun. */
    #[Test]
    public function pulih_dua_kali_ditolak(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();

        $this->actingAs($admin);
        $this->hapus($b);

        $arsipId = (string) $this->arsip($id)->getKey();

        $this->assertTrue((new PulihkanPendaftaran)->jalankan($arsipId)['berhasil']);

        $kedua = (new PulihkanPendaftaran)->jalankan($arsipId);

        $this->assertFalse($kedua['berhasil']);
        $this->assertStringContainsString('sudah dikembalikan', $kedua['pesan']);

        $this->assertSame(1, PendaftaranScopusCamp::whereKey($id)->count());
    }

    /**
     * Barisnya sudah dibuat ulang dengan tangan? Pemulihannya DITOLAK.
     *
     * Menimpanya diam-diam menghapus pekerjaan orang lain tanpa ada yang
     * tahu — dan yang menekan Kembalikan mengira ia cuma mengurungkan
     * penghapusan.
     */
    #[Test]
    public function tidak_menimpa_baris_yang_sudah_ada_lagi(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();
        $nomor = $b->id_transaksi;

        $this->actingAs($admin);
        $this->hapus($b);

        /*
         * forceFill, BUKAN create(['id' => ...]): 'id' bukan medan yang boleh
         * diisi massal, jadi create() membuangnya diam-diam dan barisnya
         * lahir berkunci baru — tabrakan yang hendak diuji tidak pernah
         * terjadi, dan ujinya lulus karena alasan yang keliru.
         */
        (new PendaftaranScopusCamp)->forceFill([
            'id' => $id, 'id_transaksi' => $nomor, 'kategori_id' => $b->kategori_id,
            'nama' => 'Diketik Ulang', 'email' => 'ulang@contoh.test',
            'telp' => '0811-0000-0002', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => '5500000', 'status' => 'diproses',
        ])->save();

        $hasil = (new PulihkanPendaftaran)->jalankan((string) $this->arsip($id)->getKey());

        $this->assertFalse($hasil['berhasil']);
        $this->assertStringContainsString('sudah ada lagi', $hasil['pesan']);
        $this->assertSame('Diketik Ulang', PendaftaranScopusCamp::find($id)->nama,
            'Baris yang diketik ulang tertimpa potret lama.');
    }

    /** Jejaknya mencatat siapa yang mengembalikan. */
    #[Test]
    public function pemulihannya_meninggalkan_jejak(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();

        $this->actingAs($admin);
        $this->hapus($b);
        (new PulihkanPendaftaran)->jalankan((string) $this->arsip($id)->getKey());

        $jejak = PendaftaranJejak::milik('scopus_camp', $id)->where('aksi', 'pulih')->first();

        $this->assertNotNull($jejak, 'Pemulihannya tidak meninggalkan jejak apa pun.');
        $this->assertStringContainsString('Admin Uji', (string) $jejak->oleh_nama);
    }

    /** Hanya administrator yang boleh mengembalikan. */
    #[Test]
    public function hanya_administrator_yang_boleh_mengembalikan(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();

        $this->actingAs($admin);
        $this->hapus($b);

        $arsipId = (string) $this->arsip($id)->getKey();

        $t = Str::random(8);
        $pelanggan = User::create([
            'full_name' => 'Pelanggan Uji', 'username' => 'plg_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $pelanggan->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_PELANGGAN])->save();

        $this->flushSession();

        $this->actingAs($pelanggan)
            ->post(route('account.pendaftaran-layanan.pulihkan', $arsipId))
            ->assertRedirect(route('account.dashboard.index'));

        $this->assertNull(PendaftaranScopusCamp::find($id),
            'Pelanggan berhasil mengembalikan pendaftaran.');
    }

    /** Lewat layarnya: ditekan, lalu dibawa ke rincian pendaftarannya. */
    #[Test]
    public function lewat_layarnya_dibawa_ke_rinciannya(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();

        $this->actingAs($admin);
        $this->hapus($b);

        $this->actingAs($admin)
            ->post(route('account.pendaftaran-layanan.pulihkan', $this->arsip($id)->getKey()))
            ->assertRedirect(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $id]))
            ->assertSessionHas('sukses');

        $this->assertNotNull(PendaftaranScopusCamp::find($id));
    }

    /**
     * Bukti bayarnya ikut kembali ke cakram.
     *
     * Penghapusan dulu MEMBUANG berkasnya dengan unlink(), jadi pemulihan
     * apa pun akan mengembalikan pendaftaran lunas tanpa satu-satunya hal
     * yang membuktikan uangnya pernah masuk. Sekarang berkasnya diparkir di
     * subfolder 'terhapus/' dan dipindahkan kembali saat dipulihkan.
     *
     * Berkasnya sungguhan ditulis ke public/, bukan Storage::fake(): jalur
     * yang dipakai kode ini public_path(), dan cakram palsu tidak
     * menyentuhnya sama sekali — ujinya akan hijau tanpa menguji apa pun.
     */
    #[Test]
    public function bukti_bayarnya_ikut_kembali(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();

        $folder = Pendaftaran::sumber('scopus_camp')['bukti_folder'];
        $nama = 'uji-pulih-' . Str::random(8) . '.jpg';
        $jalur = public_path($folder . '/' . $nama);

        if (! is_dir(dirname($jalur))) {
            mkdir(dirname($jalur), 0755, true);
        }

        file_put_contents($jalur, 'bukti-uji');
        $b->forceFill(['gambar' => $nama])->save();

        $this->actingAs($admin);

        try {
            $this->hapus($b);

            $this->assertFileDoesNotExist($jalur,
                'Berkasnya masih di tempat asal; ia tidak diparkir.');

            $arsip = $this->arsip($id);

            $this->assertNotNull($arsip->bukti_parkir,
                'Jalur parkirannya tidak dicatat, jadi tidak ada yang tahu '
                . 'berkas mana yang harus dikembalikan.');

            $hasil = (new PulihkanPendaftaran)->jalankan((string) $arsip->getKey());

            $this->assertTrue($hasil['berhasil'], $hasil['pesan']);
            $this->assertFileExists($jalur, 'Bukti bayarnya tidak kembali ke tempatnya.');
            $this->assertSame('bukti-uji', file_get_contents($jalur));

            // Parkirannya dikosongkan supaya pemulihan kedua tidak mengira
            // masih ada berkas yang menunggu.
            $this->assertNull($arsip->fresh()->bukti_parkir);
        } finally {
            /*
             * Dibersihkan SENDIRI, dan hanya berkas yang dibuat uji ini.
             * DatabaseTransactions tidak melindungi cakram — baris bisa
             * di-rollback, berkas tidak.
             */
            @unlink($jalur);
            @unlink(public_path($folder . '/' . HapusPendaftaran::FOLDER_PARKIR
                . '/' . $id . '__' . $nama));
        }
    }

    /**
     * Penghapusan LAMA yang berkasnya sudah terlanjur dibuang tetap bisa
     * dipulihkan — barisnya kembali, buktinya tidak, dan pesannya bilang.
     *
     * Diam soal ini lebih buruk daripada gagal: yang memulihkan menyangka
     * semuanya utuh lalu menandai pendaftarannya lunas berdasarkan bukti
     * yang sudah tidak ada.
     */
    #[Test]
    public function tanpa_berkas_parkiran_tetap_pulih_dan_pesannya_jujur(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();

        $this->actingAs($admin);
        $this->hapus($b);

        $arsip = $this->arsip($id);
        $arsip->forceFill(['bukti_parkir' => null])->save();

        $hasil = (new PulihkanPendaftaran)->jalankan((string) $arsip->getKey());

        $this->assertTrue($hasil['berhasil'], $hasil['pesan']);
        $this->assertStringContainsString('Bukti bayarnya tidak ikut kembali', $hasil['pesan']);
        $this->assertNotNull(PendaftaranScopusCamp::find($id));
    }

    /**
     * Layar catatan penghapusan tetap terbuka SESUDAH ada yang dipulihkan.
     *
     * Terjadi sungguhan saat membangun ini: `dipulihkan_at` tidak dicor jadi
     * tanggal, jadi nilainya untaian — dan layarnya memanggil
     * translatedFormat() padanya. Halamannya 500 begitu ada SATU catatan
     * yang dikembalikan, sementara yang belum dipulihkan (null) lolos tanpa
     * gejala apa pun. Sepuluh uji pemulihan yang sudah ada tidak
     * menangkapnya karena tak satu pun membuka layarnya.
     */
    #[Test]
    public function layar_catatannya_tetap_terbuka_sesudah_dipulihkan(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();
        $nomor = $b->id_transaksi;

        $this->actingAs($admin);
        $this->hapus($b);

        $arsipId = (string) $this->arsip($id)->getKey();

        (new PulihkanPendaftaran)->jalankan($arsipId);

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus', ['cari' => $nomor]))
            ->assertOk()
            ->assertSee(strtoupper($nomor))
            ->assertSee('dikembalikan')
            /*
             * Dipatok pada ALAMAT BORANGNYA, bukan nama kelas tombolnya.
             * Nama kelas muncul juga di blok <style> dan <script> halaman
             * yang sama, jadi "tidak memuat .thp-tombol-pulih" mustahil
             * lulus — dan penjaganya akan merah selamanya tanpa ada yang
             * salah pada halamannya.
             *
             * Tombolnya HARUS hilang untuk yang sudah dipulihkan: masih ada,
             * menekannya cuma menghasilkan pesan galat.
             */
            ->assertDontSee(route('account.pendaftaran-layanan.pulihkan', $arsipId), false);
    }

    /** Yang BELUM dipulihkan tetap punya tombolnya. */
    #[Test]
    public function yang_belum_dipulihkan_masih_punya_tombolnya(): void
    {
        [$admin, $b] = $this->dengan();

        $this->actingAs($admin);
        $this->hapus($b);

        $arsipId = (string) $this->arsip((string) $b->getKey())->getKey();

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus', ['cari' => $b->id_transaksi]))
            ->assertOk()
            ->assertSee(route('account.pendaftaran-layanan.pulihkan', $arsipId), false);
    }

    // ------------------------------------------------------------- pembantu

    private function hapus(PendaftaranScopusCamp $b): void
    {
        $hasil = (new HapusPendaftaran)->jalankan('scopus_camp', (string) $b->getKey());

        $this->assertTrue($hasil['berhasil'], $hasil['pesan']);
    }

    private function arsip(string $id): PendaftaranDihapus
    {
        $arsip = PendaftaranDihapus::where('pendaftaran_id', $id)->first();

        $this->assertNotNull($arsip, 'Penghapusannya tidak meninggalkan catatan apa pun.');

        return $arsip;
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp} */
    private function dengan(array $ubah = [], array $ubahAngkatan = []): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $t = Str::random(8);

        $admin = User::create([
            'full_name' => 'Admin Uji ' . $t, 'username' => 'plh_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $admin->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        $angkatan = KategoriLayanan::create(array_merge([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Uji ' . $t,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ], $ubahAngkatan));

        $b = PendaftaranScopusCamp::create(array_merge([
            'id_transaksi' => 'T-' . $t, 'kategori_id' => $angkatan->id,
            'nama' => 'Peserta ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0001', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => '5500000', 'status' => 'diproses',
        ], $ubah));

        return [$admin, $b];
    }
}
