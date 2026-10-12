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

    /**
     * Angka ringkasan BERHENTI menghitung yang sudah dikembalikan.
     *
     * Terukur sebelum diperbaiki: sesudah satu pendaftaran dipulihkan,
     * jumlah dan uangnya tidak bergerak sama sekali — uangnya terhitung dua
     * kali, hidup di pembukuan sekaligus tercatat sebagai hilang.
     */
    #[Test]
    public function ringkasannya_berhenti_menghitung_yang_sudah_kembali(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();

        /*
         * Terminnya WAJIB ada. Tanpa pembayaran, uang_terhapus-nya nol dan
         * tuntutan uang di bawah membandingkan nol dengan nol — hijau tanpa
         * menguji apa pun.
         */
        PembayaranPendaftaran::create([
            'jenis' => PembayaranPendaftaran::PENDAFTARAN, 'induk_id' => $id,
            'urutan' => 1, 'nominal' => 400000, 'tanggal' => now()->toDateString(),
        ]);

        $this->actingAs($admin);
        $this->hapus($b);

        /*
         * Dituntut pada ANGKA YANG SAMPAI KE LAYAR, bukan pada scope
         * modelnya. Versi pertama penjaga ini memanggil
         * PendaftaranDihapus::masihTerhapus() sendiri, jadi ia tetap hijau
         * walau pengendalinya kembali menghitung seluruh tabel — dan di
         * pengendalilah cacatnya berada.
         */
        $sebelum = $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus'))
            ->assertOk();

        $jumlahSebelum = $sebelum->viewData('jumlahSemua');
        $uangSebelum = $sebelum->viewData('uangSemua');

        (new PulihkanPendaftaran)->jalankan((string) $this->arsip($id)->getKey());

        $sesudah = $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus'))
            ->assertOk();

        $this->assertSame($jumlahSebelum - 1, $sesudah->viewData('jumlahSemua'),
            'Yang sudah dikembalikan masih terhitung sebagai terhapus di layarnya.');

        $this->assertSame($uangSebelum - 400000, $sesudah->viewData('uangSemua'),
            'Uangnya masih terhitung hilang padahal sudah kembali ke pembukuan.');

        $this->assertSame(1, PendaftaranDihapus::sudahDipulihkan()
            ->where('pendaftaran_id', $id)->count());
    }

    /** Saringan keadaan memisahkan yang masih hilang dari yang sudah kembali. */
    #[Test]
    public function saringan_keadaan_memisahkan_keduanya(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();
        $nomor = $b->id_transaksi;

        $this->actingAs($admin);
        $this->hapus($b);
        (new PulihkanPendaftaran)->jalankan((string) $this->arsip($id)->getKey());

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus',
                ['cari' => $nomor, 'keadaan' => 'pulih']))
            ->assertOk()->assertSee(strtoupper($nomor));

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus',
                ['cari' => $nomor, 'keadaan' => 'terhapus']))
            ->assertOk()->assertDontSee(strtoupper($nomor));
    }

    /**
     * Memulihkan ke angkatan yang sudah penuh DISEBUTKAN.
     *
     * Sisa kuotanya dijepit di nol, jadi layar angkatan menampilkan "sisa 0"
     * yang terlihat pas — dan tidak ada tempat lain yang memberi tahu bahwa
     * kursinya sebenarnya kelebihan.
     */
    #[Test]
    public function kelebihan_kursi_disebutkan_di_pesannya(): void
    {
        [$admin, $b] = $this->dengan(['jumlah_pendaftar' => '3'], ['sisa_kuota' => '47']);
        $id = (string) $b->getKey();

        $this->actingAs($admin);
        $this->hapus($b);

        // Angkatannya diisi penuh selagi pendaftarannya terhapus.
        KategoriLayanan::where('id', $b->kategori_id)->update(['sisa_kuota' => '1']);

        $hasil = (new PulihkanPendaftaran)->jalankan((string) $this->arsip($id)->getKey());

        $this->assertTrue($hasil['berhasil'], $hasil['pesan']);
        $this->assertStringContainsString('kelebihan 2 kursi', $hasil['pesan']);
        $this->assertSame(0, (int) KategoriLayanan::find($b->kategori_id)->sisa_kuota);
    }

    /** Cukup kuota: tidak ada peringatan yang mengganggu. */
    #[Test]
    public function kuota_cukup_tidak_memperingatkan_apa_pun(): void
    {
        [$admin, $b] = $this->dengan(['jumlah_pendaftar' => '2'], ['sisa_kuota' => '48']);
        $id = (string) $b->getKey();

        $this->actingAs($admin);
        $this->hapus($b);

        $hasil = (new PulihkanPendaftaran)->jalankan((string) $this->arsip($id)->getKey());

        $this->assertStringNotContainsString('kelebihan', $hasil['pesan']);
    }

    /**
     * Sesudah menghapus, layar daftar menawarkan "Kembalikan" di tempat.
     *
     * Tanpa itu, mengurungkan satu salah klik berarti membuka catatan
     * penghapusan lalu mencarinya di antara baris lain — dan yang baru saja
     * salah klik tidak selalu tahu layar itu ada.
     */
    #[Test]
    public function sesudah_menghapus_daftarnya_menawarkan_urungkan(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();

        $this->actingAs($admin)
            ->delete(route('account.pendaftaran-layanan.hapus', ['scopus_camp', $id]))
            ->assertRedirect(route('account.pendaftaran-layanan.index'))
            ->assertSessionHas('urungkanHapus');

        $arsipId = (string) $this->arsip($id)->getKey();

        $this->actingAs($admin)
            ->withSession(['urungkanHapus' => ['arsip' => $arsipId, 'pesan' => 'dihapus']])
            ->get(route('account.pendaftaran-layanan.index'))
            ->assertOk()
            ->assertSee(route('account.pendaftaran-layanan.pulihkan', $arsipId), false);
    }

    /** Catatannya bisa dibuang permanen, berikut bukti di parkirannya. */
    #[Test]
    public function catatannya_bisa_dibuang_permanen(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();

        $folder = Pendaftaran::sumber('scopus_camp')['bukti_folder'];
        $nama = 'uji-buang-' . Str::random(8) . '.jpg';
        $jalur = public_path($folder . '/' . $nama);

        if (! is_dir(dirname($jalur))) {
            mkdir(dirname($jalur), 0755, true);
        }

        file_put_contents($jalur, 'bukti-uji');
        $b->forceFill(['gambar' => $nama])->save();

        $this->actingAs($admin);
        $this->hapus($b);

        $arsip = $this->arsip($id);
        $diparkir = public_path($folder . '/' . HapusPendaftaran::FOLDER_PARKIR
            . '/' . $arsip->bukti_parkir);

        try {
            $this->assertFileExists($diparkir);

            $this->actingAs($admin)
                ->delete(route('account.pendaftaran-layanan.buang-catatan', $arsip->getKey()))
                ->assertRedirect()
                ->assertSessionHas('sukses');

            $this->assertNull(PendaftaranDihapus::find($arsip->getKey()),
                'Catatannya masih ada.');
            $this->assertFileDoesNotExist($diparkir,
                'Bukti di parkirannya tidak ikut dibuang; ia jadi yatim selamanya.');
        } finally {
            @unlink($jalur);
            @unlink($diparkir);
        }
    }

    /** Membuang catatan hanya untuk administrator. */
    #[Test]
    public function buang_catatan_hanya_untuk_administrator(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();

        $this->actingAs($admin);
        $this->hapus($b);

        $arsipId = (string) $this->arsip($id)->getKey();

        $t = Str::random(8);
        $pelanggan = User::create([
            'full_name' => 'Pelanggan Uji', 'username' => 'bcg_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $pelanggan->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_PELANGGAN])->save();

        $this->flushSession();

        $this->actingAs($pelanggan)
            ->delete(route('account.pendaftaran-layanan.buang-catatan', $arsipId))
            ->assertRedirect(route('account.dashboard.index'));

        $this->assertNotNull(PendaftaranDihapus::find($arsipId));
    }

    /**
     * Saringan catatan penghapusan MENYARING HIDUP, sama dengan layar
     * Pendaftar Layanan.
     *
     * Dulu borang biasa: memilih "Sudah dikembalikan" mengubah isi menunya
     * tetapi daftarnya diam sampai tombol Cari ditekan — sementara di layar
     * sebelah memilih saringan langsung menyaring. Dua layar bersebelahan
     * yang berperilaku berbeda membuat yang memakainya menyangka
     * saringannya rusak.
     *
     * Yang dijaga KONTRAK MARKAHNYA, bukan perilakunya: penyaringnya
     * JavaScript, dan uji PHP tidak menjalankannya. Satu id yang salah ketik
     * membuat skripnya diam total tanpa galat apa pun.
     */
    #[Test]
    public function saringannya_memenuhi_kontrak_penyaring_hidup(): void
    {
        [$admin, $b] = $this->dengan();

        $isi = $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus'))
            ->assertOk()->getContent();

        $this->assertSame(1, preg_match('/data-mis-saring="([a-z-]+)"/', $isi, $cocok),
            'Borangnya tidak memakai penyaring hidup bersama.');

        /*
         * Wadah hasilnya harus BENAR-BENAR ADA dengan id itu. Tidak ada,
         * mis-ui.js berhenti di baris pertamanya (`if (!hasil) return`) dan
         * seluruh penyaringnya mati diam-diam — termasuk kotak carinya.
         */
        $this->assertStringContainsString('id="' . $cocok[1] . '"', $isi,
            'Wadah hasil yang disebut data-mis-saring tidak ada di halamannya.');

        $this->assertSame(1, preg_match('/data-mis-saring-juga="([a-z-]+)"/', $isi, $juga));

        $this->assertStringContainsString('id="' . $juga[1] . '"', $isi,
            'Ringkasan yang disebut data-mis-saring-juga tidak ada; angkanya '
            . 'akan tertinggal pada nilai sebelum disaring.');

        // Kotak cari dan tombol cadangannya ikut dituntut: tanpa data-mis-cari
        // penyaringnya hidup tetapi tidak pernah menyahut ketikan.
        $this->assertStringContainsString('data-mis-cari', $isi);
        $this->assertStringContainsString('data-mis-terapkan', $isi);
    }

    /**
     * Jumlah lajur ubin mengikuti JUMLAH ubinnya.
     *
     * Dipatok dua sementara ubin ketiga muncul saat ada yang dikembalikan,
     * ubin itu jatuh sendirian ke baris kedua dan menyisakan lubang selebar
     * separuh layar.
     */
    #[Test]
    public function lajur_ubinnya_mengikuti_jumlahnya(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();

        $this->actingAs($admin);
        $this->hapus($b);

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus'))
            ->assertOk()
            ->assertSee('mis-ringkas-2', false);

        (new PulihkanPendaftaran)->jalankan((string) $this->arsip($id)->getKey());

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus'))
            ->assertOk()
            ->assertSee('mis-ringkas-3', false);
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
