<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\Actions\Pendaftaran\HapusPendaftaran;
use App\KategoriLayanan;
use App\PendaftaranDihapus;
use App\PendaftaranScopusCamp;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Catatan penghapusan: alasan, saringan, pengurut, dan tindakan borongan.
 *
 * Arsipnya sudah menjawab KE MANA perginya satu pendaftaran. Yang kurang
 * ada enam, dan semuanya terukur sebelum dikerjakan: alasannya tidak pernah
 * tercatat, saringannya dua lawan sembilan di layar sebelah, tidak ada
 * pengurut sama sekali, penghapusan berulang tidak ditandai, tidak ada
 * tindakan borongan, dan parkiran buktinya tidak punya jendela.
 */
class ArsipLengkapTest extends TestCase
{
    use DatabaseTransactions;

    // ------------------------------------------------------------- alasan

    #[Test]
    public function alasannya_tersimpan_dan_tampil_di_catatannya(): void
    {
        [$admin, $b] = $this->dengan();

        $this->actingAs($admin)
            ->delete(route('account.pendaftaran-layanan.hapus', ['scopus_camp', $b->getKey()]), [
                'alasan' => 'Pendaftaran ganda, yang asli T-LAIN',
            ])
            ->assertRedirect();

        $arsip = $this->arsip($b);

        $this->assertSame('Pendaftaran ganda, yang asli T-LAIN', $arsip->alasan);

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus', ['cari' => $b->id_transaksi]))
            ->assertOk()
            ->assertSee('Pendaftaran ganda, yang asli T-LAIN');
    }

    /**
     * Alasannya TIDAK diwajibkan.
     *
     * Dipaksa, yang terisi bukan alasan jujur melainkan satu huruf asal
     * supaya tombolnya mau jalan — dan kolom berisi "x" lebih buruk daripada
     * kolom kosong, sebab yang membacanya mengira itu alasan sungguhan.
     */
    #[Test]
    public function tanpa_alasan_penghapusannya_tetap_jalan(): void
    {
        [$admin, $b] = $this->dengan();

        $this->actingAs($admin)
            ->delete(route('account.pendaftaran-layanan.hapus', ['scopus_camp', $b->getKey()]))
            ->assertRedirect();

        $this->assertNull($this->arsip($b)->alasan);
        $this->assertNull(PendaftaranScopusCamp::find($b->getKey()));
    }

    /**
     * Alasan yang kepanjangan DIPOTONG, bukan membatalkan penghapusannya.
     *
     * Kolomnya varchar(255); diserahkan apa adanya, MySQL menolak seluruh
     * penyimpanan arsip dan penghapusannya batal karena panjang kalimat.
     */
    #[Test]
    public function alasan_kepanjangan_dipotong_bukan_membatalkan(): void
    {
        [$admin, $b] = $this->dengan();

        $this->actingAs($admin)
            ->delete(route('account.pendaftaran-layanan.hapus', ['scopus_camp', $b->getKey()]), [
                'alasan' => str_repeat('a', 400),
            ])
            ->assertRedirect();

        $this->assertNull(PendaftaranScopusCamp::find($b->getKey()),
            'Penghapusannya batal gara-gara panjang alasannya.');
        $this->assertSame(255, mb_strlen($this->arsip($b)->alasan));
    }

    // ----------------------------------------------------------- saringan

    /**
     * Kalimat kosongnya JUJUR saat yang menyaring bukan kotak cari.
     *
     * Terukur sebelum dibetulkan: menyaring Keadaan tanpa mengetik apa pun
     * menampilkan "Belum ada pendaftaran yang dihapus — setiap penghapusan
     * akan tercatat di sini", padahal ada tujuh.
     */
    #[Test]
    public function kalimat_kosongnya_tidak_menyangkal_datanya(): void
    {
        [$admin, $b] = $this->dengan();

        $this->actingAs($admin);
        $this->hapus($b);

        // Saringan layanan yang pasti tidak kena: barisnya scopus_camp.
        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus', ['layanan' => 'clinik_scopus']))
            ->assertOk()
            ->assertSee('Tidak ada yang cocok')
            ->assertDontSee('Belum ada pendaftaran yang dihapus');
    }

    #[Test]
    public function saringan_layanan_dan_oleh_bekerja(): void
    {
        [$admin, $b] = $this->dengan();
        $nomor = $b->id_transaksi;

        $this->actingAs($admin);
        $this->hapus($b);

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus', ['layanan' => 'scopus_camp']))
            ->assertOk()->assertSee(strtoupper($nomor));

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus', ['layanan' => 'bibliometrik']))
            ->assertOk()->assertDontSee(strtoupper($nomor));

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus', ['oleh' => $admin->full_name]))
            ->assertOk()->assertSee(strtoupper($nomor));

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus', ['oleh' => 'Orang Lain']))
            ->assertOk()->assertDontSee(strtoupper($nomor));
    }

    #[Test]
    public function rentang_tanggal_menyaring_yang_di_luarnya(): void
    {
        [$admin, $b] = $this->dengan();
        $nomor = $b->id_transaksi;

        $this->actingAs($admin);
        $this->hapus($b);

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus', [
                'dari' => now()->subDay()->toDateString(),
                'sampai' => now()->addDay()->toDateString(),
            ]))->assertOk()->assertSee(strtoupper($nomor));

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus', [
                'dari' => now()->addWeek()->toDateString(),
            ]))->assertOk()->assertDontSee(strtoupper($nomor));
    }

    /** Pengurutnya daftar putih; nama kolom karangan tidak sampai ke SQL. */
    #[Test]
    public function pengurut_karangan_jatuh_ke_bawaan(): void
    {
        [$admin, $b] = $this->dengan();

        $this->actingAs($admin);
        $this->hapus($b);

        $jawab = $this->actingAs($admin)->get(route('account.pendaftaran-layanan.terhapus', [
            'urut' => 'uang_terhapus) --', 'arah' => 'naik',
        ]))->assertOk();

        $this->assertSame('waktu', $jawab->viewData('urutArsip'));
    }

    #[Test]
    public function pengurut_uang_mengubah_urutannya(): void
    {
        [$admin, $b] = $this->dengan();

        $this->actingAs($admin);
        $this->hapus($b);

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus', ['urut' => 'uang', 'arah' => 'naik']))
            ->assertOk()
            ->assertViewHas('urutArsip', 'uang')
            ->assertViewHas('arahArsip', 'asc');
    }

    // ------------------------------------------------------ berulang & UI

    /**
     * Pendaftaran yang dihapus lebih dari sekali DITANDAI.
     *
     * Tanpa penanda, kedua catatannya tampil persis sama dan terbaca seperti
     * dua pendaftaran berbeda yang kebetulan senama.
     */
    #[Test]
    public function penghapusan_berulang_ditandai(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();

        $this->actingAs($admin);
        $this->hapus($b);

        (new \App\Actions\Pendaftaran\PulihkanPendaftaran)
            ->jalankan((string) $this->arsip($b)->getKey());

        $this->hapus(PendaftaranScopusCamp::find($id));

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus', ['cari' => $b->id_transaksi]))
            ->assertOk()
            ->assertSee('dihapus 2', false);
    }

    /** Jendela parkiran menyebut berapa bukti yang masih menunggu. */
    #[Test]
    public function jendela_parkiran_menyebut_jumlahnya(): void
    {
        [$admin, $b] = $this->dengan();

        $folder = Pendaftaran::sumber('scopus_camp')['bukti_folder'];
        $nama = 'uji-jendela-' . Str::random(8) . '.jpg';
        $jalur = public_path($folder . '/' . $nama);

        if (! is_dir(dirname($jalur))) {
            mkdir(dirname($jalur), 0755, true);
        }

        file_put_contents($jalur, 'bukti');
        $b->forceFill(['gambar' => $nama])->save();

        $this->actingAs($admin);
        $this->hapus($b);

        $diparkir = public_path($folder . '/' . HapusPendaftaran::FOLDER_PARKIR
            . '/' . $this->arsip($b)->bukti_parkir);

        try {
            $this->actingAs($admin)
                ->get(route('account.pendaftaran-layanan.terhapus'))
                ->assertOk()
                ->assertSee('bukti bayar')
                ->assertSee('pendaftaran:pangkas-parkiran');
        } finally {
            @unlink($jalur);
            @unlink($diparkir);
        }
    }

    // ----------------------------------------------------------- borongan

    /**
     * Borongan TANPA saringan ditolak.
     *
     * "Buang semua" yang mengosongkan seluruh catatan penghapusan hanya satu
     * salah klik jauhnya, dan tidak ada yang bisa mengurungkannya.
     */
    #[Test]
    public function borongan_tanpa_saringan_ditolak(): void
    {
        [$admin, $b] = $this->dengan();

        $this->actingAs($admin);
        $this->hapus($b);

        $sebelum = PendaftaranDihapus::count();

        $this->actingAs($admin)
            ->delete(route('account.pendaftaran-layanan.buang-borongan'))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame($sebelum, PendaftaranDihapus::count(),
            'Catatan terbuang walau saringannya kosong.');
    }

    /** Dengan saringan: hanya yang cocok yang terbuang. */
    #[Test]
    public function borongan_membuang_yang_cocok_saja(): void
    {
        [$admin, $b] = $this->dengan();
        $nomor = $b->id_transaksi;

        $this->actingAs($admin);
        $this->hapus($b);

        $lain = PendaftaranDihapus::create([
            'layanan' => 'bibliometrik', 'pendaftaran_id' => (string) Str::uuid(),
            'nomor' => 'LAIN-' . Str::random(6), 'nama' => 'Jangan Terbuang',
            'email' => 'x@contoh.test', 'status' => 'diproses', 'total' => 0,
            'uang_terhapus' => 0, 'jumlah_pembayaran' => 0, 'jumlah_jejak' => 0,
            'potret' => [], 'oleh_nama' => 'Panitia Uji',
        ]);

        $this->actingAs($admin)
            ->delete(route('account.pendaftaran-layanan.buang-borongan', ['cari' => $nomor]))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $this->assertSame(0, PendaftaranDihapus::where('nomor', $nomor)->count());
        $this->assertNotNull($lain->fresh(), 'Catatan di luar saringan ikut terbuang.');
    }

    #[Test]
    public function borongan_hanya_untuk_administrator(): void
    {
        [$admin, $b] = $this->dengan();
        $nomor = $b->id_transaksi;

        $this->actingAs($admin);
        $this->hapus($b);

        $t = Str::random(8);
        $pelanggan = User::create([
            'full_name' => 'Pelanggan Uji', 'username' => 'brg_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $pelanggan->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_PELANGGAN])->save();

        $this->flushSession();

        $this->actingAs($pelanggan)
            ->delete(route('account.pendaftaran-layanan.buang-borongan', ['cari' => $nomor]))
            ->assertRedirect(route('account.dashboard.index'));

        $this->assertSame(1, PendaftaranDihapus::where('nomor', $nomor)->count());
    }

    /**
     * Keping keterangan BOLEH MEMBUNGKUS di layar sempit.
     *
     * .mis-lencana bersama menyetel white-space: nowrap — benar untuk
     * lencana pendek seperti "Lunas", tetapi keping "dikembalikan 12 Okt
     * 2026, 07:19 oleh tesss" selebar 302px tidak muat di 390px dan
     * menembus tepi kartunya.
     *
     * Diukur di peramban lewat iframe berlebar 390px, bukan ditaksir:
     *
     *     tanpa aturan ini  gulir badan 414px
     *     tanpa aturan ini  gulir badan 414px
     *     dengan aturan     gulir badan 390px
     *
     * Pemilihnya menyebut SEMUA anak, bukan `span` saja. Versi pertama
     * penjaga ini mematok `.thp-ket > span` — dan aturan produk yang
     * dijaganya pun begitu. Keping "dikembalikan …" lalu ditulis sebagai
     * <a> agar bisa ditekan, keluar dari aturan itu tanpa satu tanda pun,
     * dan penjaganya tetap hijau: terukur ulang di 390px, lebar gulir
     * badan 411px melawan layar 390px dengan penjaga ini menyala.
     */
    #[Test]
    public function keping_boleh_membungkus_di_layar_sempit(): void
    {
        $isi = file_get_contents(resource_path(
            'views/account/pendaftaran_layanan/terhapus.blade.php'
        ));

        $this->assertMatchesRegularExpression(
            '/@media[^{]*max-width[^{]*\{(?:[^{}]|\{[^{}]*\})*\.thp-ket > \*\s*\{[^}]*white-space:\s*normal/s',
            $isi,
            'Keping keterangan tidak diizinkan membungkus di layar sempit; '
            . 'keping "dikembalikan … oleh …" akan menembus tepi kartunya.'
        );
    }

    /**
     * Alasannya IKUT DIUNDUH.
     *
     * Ia medan yang menjawab KENAPA, bukan cuma ke mana — dan berkas
     * unduhan justru yang paling sering diteruskan ke orang yang tidak
     * pernah membuka layarnya. Ditinggalkan, satu-satunya jawabannya cuma
     * ada di layar.
     */
    #[Test]
    public function alasannya_ikut_di_unduhan(): void
    {
        $isi = file_get_contents(app_path('Exports/PendaftaranDihapusExport.php'));

        $this->assertStringContainsString("'Alasan',", $isi,
            'Kolom Alasan tidak ada di kepala unduhan.');
        $this->assertStringContainsString('alasan_rapi', $isi,
            'Kepalanya ada tetapi barisnya tidak pernah diisi.');
    }

    /**
     * Jumlah kolom kepala SAMA dengan jumlah sel tiap barisnya.
     *
     * Satu kolom yang ditambahkan ke kepala tanpa pasangannya di baris
     * menggeser SELURUH kolom sesudahnya satu langkah — tanpa galat, tanpa
     * berkas rusak. Yang menerimanya membaca "Tagihan" di bawah judul
     * "Alasan" dan tidak punya alasan untuk curiga.
     */
    #[Test]
    public function kolom_unduhan_sejajar_dengan_kepalanya(): void
    {
        [$admin, $b] = $this->dengan();

        $this->actingAs($admin);
        $this->hapus($b);

        $ekspor = new \App\Exports\PendaftaranDihapusExport(
            PendaftaranDihapus::query()->terbaru()->get()
        );

        $isi = $ekspor->array();

        $this->assertNotEmpty($isi, 'Unduhannya tidak memuat satu baris pun.');

        foreach ($isi as $i => $baris) {
            $this->assertCount(count($ekspor->headings()), $baris,
                'Baris ke-' . ($i + 1) . ' tidak sejajar dengan kepala kolomnya; '
                . 'seluruh kolom sesudah yang hilang akan bergeser.');
        }
    }

    /**
     * Catatan yang sudah dipulihkan BISA DITEKAN menuju pendaftarannya.
     *
     * Dulu kepingnya berhenti di "dikembalikan <waktu>" — dan untuk
     * membukanya orang harus menyalin nomornya lalu mencarinya di layar
     * daftar. Barisnya sudah ada lagi; tautannya cuma belum pernah
     * dipasang.
     */
    #[Test]
    public function catatan_yang_pulih_menautkan_pendaftarannya(): void
    {
        [$admin, $b] = $this->dengan();
        $id = (string) $b->getKey();

        $this->actingAs($admin);
        $this->hapus($b);

        (new \App\Actions\Pendaftaran\PulihkanPendaftaran)
            ->jalankan((string) $this->arsip($b)->getKey());

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus', ['cari' => $b->id_transaksi]))
            ->assertOk()
            ->assertSee(
                route('account.pendaftaran-layanan.rincian', ['scopus_camp', $id]),
                false
            );
    }

    /**
     * Keempat angka ringkasannya datang dari SATU kueri.
     *
     * Dulu empat kueri agregat terpisah, dan satu di antaranya terdaftar
     * dua kali sebagai kunci larik yang sama — PHP diam, yang kedua menang,
     * yang pertama tetap dijalankan lalu dibuang. Terukur sembilan kueri ke
     * satu tabel tiap kali layarnya dibuka.
     */
    #[Test]
    public function ringkasannya_tidak_memborong_kueri(): void
    {
        [$admin, $b] = $this->dengan();

        $this->actingAs($admin);
        $this->hapus($b);

        \Illuminate\Support\Facades\DB::enableQueryLog();
        \Illuminate\Support\Facades\DB::flushQueryLog();

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.terhapus'))->assertOk();

        $jumlah = collect(\Illuminate\Support\Facades\DB::getQueryLog())
            ->filter(fn ($q) => str_contains($q['query'], 'pendaftaran_dihapus'))
            ->count();

        $this->assertLessThanOrEqual(6, $jumlah,
            'Layar catatan penghapusan menembak ' . $jumlah . ' kueri ke satu tabel.');
    }

    // ------------------------------------------------------------- pembantu

    private function hapus(PendaftaranScopusCamp $b): void
    {
        $hasil = (new HapusPendaftaran)->jalankan('scopus_camp', (string) $b->getKey());

        $this->assertTrue($hasil['berhasil'], $hasil['pesan']);
    }

    private function arsip(PendaftaranScopusCamp $b): PendaftaranDihapus
    {
        $arsip = PendaftaranDihapus::where('pendaftaran_id', (string) $b->getKey())
            ->orderByDesc('created_at')->first();

        $this->assertNotNull($arsip);

        return $arsip;
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp} */
    private function dengan(): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $t = Str::random(8);

        $admin = User::create([
            'full_name' => 'Admin Arsip ' . $t, 'username' => 'ars_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $admin->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Uji ' . $t,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '48', 'status' => 'active',
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
