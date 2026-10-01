<?php

namespace Tests\Feature\Kategori;

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
 * Tambahan layar Angkatan Layanan hasil audit 30 Sep 2026.
 *
 * Dua di antaranya menutup hal yang hilang saat dua layar kategori disatukan,
 * dan satu menutup lubang yang bisa merusak angka kuota.
 */
class AngkatanLanjutanTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Layanan::lupakanKatalog();
        KategoriLayanan::lupakanPendaftar();
    }

    private function akun(string $peran = User::PERAN_ADMINISTRATOR): User
    {
        $u = User::create([
            'full_name' => 'Uji ' . Str::random(4),
            'username' => 'uji_ang2_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $u->forceFill(['status' => 'active', 'email_verified_at' => now(), 'peran' => $peran])->save();

        return $u->refresh();
    }

    private function angkatan(array $lain = []): KategoriLayanan
    {
        return KategoriLayanan::create(array_merge([
            'layanan' => 'scopus_camp', 'varian' => 'jawa',
            'token' => Str::random(30), 'nama' => 'Angkatan Uji', 'nama_ke' => '7',
            'mulai' => now()->addMonth(), 'selesai' => now()->addMonth()->addDays(2),
            'lokasi' => 'Yogyakarta', 'total_kuota' => '20', 'sisa_kuota' => '20',
            'biaya' => '5500000', 'total_biaya' => '5500000',
            'desc' => 'Isi pengumuman', 'status' => 'active',
        ], $lain));
    }

    // ------------------------------------------------------------- kuota

    #[Test]
    public function kuota_tidak_bisa_disetel_di_bawah_jumlah_pendaftar(): void
    {
        /*
         * Dibiarkan, angka sisanya jadi tidak berarti dan angkatan yang
         * sebenarnya penuh terlihat masih longgar — atau sebaliknya.
         */
        $admin = $this->akun();

        $terpakai = DB::table('scopus_camp_pendaftaran')
            ->select('kategori_id', DB::raw('count(*) n'))
            ->whereNotNull('kategori_id')->groupBy('kategori_id')
            ->orderByDesc('n')->first();

        if (! $terpakai || $terpakai->n < 2) {
            $this->markTestSkipped('Perlu angkatan dengan minimal dua pendaftar.');
        }

        $a = KategoriLayanan::find($terpakai->kategori_id);

        $this->actingAs($admin)
            ->post(route('account.kategori-layanan.update', $a), [
                'layanan' => $a->layanan, 'varian' => $a->varian,
                'nama' => $a->nama, 'mulai' => '2026-11-06', 'status' => 'draft',
                'total_kuota' => $terpakai->n - 1,
            ])
            ->assertSessionHasErrors('total_kuota');

        $this->assertSame((string) $a->total_kuota, (string) $a->refresh()->total_kuota);
    }

    #[Test]
    public function sisa_kuota_tidak_boleh_melebihi_totalnya(): void
    {
        // Kalau tidak, angkatan bisa menerima lebih banyak orang daripada yang
        // disediakan.
        $admin = $this->akun();
        $a = $this->angkatan();

        $this->actingAs($admin)
            ->post(route('account.kategori-layanan.update', $a), [
                'layanan' => 'scopus_camp', 'varian' => 'jawa',
                'nama' => $a->nama, 'mulai' => '2026-11-06', 'status' => 'draft',
                'total_kuota' => 20, 'sisa_kuota' => 25,
            ])
            ->assertSessionHasErrors('sisa_kuota');
    }

    // -------------------------------------------------------- penggandaan

    #[Test]
    public function menggandakan_menyalin_isinya_tetapi_bukan_tanggal_dan_statusnya(): void
    {
        /*
         * 41 angkatan Yogyakarta isinya nyaris sama persis dan semuanya
         * diketik ulang. Yang TIDAK ikut: tanggal (acaranya belum ditentukan)
         * dan statusnya — salinannya selalu draf, supaya tidak ada angkatan
         * yang terbit hanya karena tombol tertekan.
         */
        $admin = $this->akun();
        $asal = $this->angkatan(['nama_ke' => '190']);

        $this->actingAs($admin)
            ->post(route('account.kategori-layanan.gandakan', $asal))
            ->assertRedirect();

        $salinan = KategoriLayanan::where('layanan', $asal->layanan)
            ->whereKeyNot($asal->getKey())
            ->latest('created_at')->first();

        $this->assertNotNull($salinan, 'Salinannya harus ada.');

        /*
         * Nomornya harus BEBAS, bukan sekadar naik satu.
         *
         * Menaikkan asal satu membuat tombol ini sendiri yang memproduksi
         * kembar: menggandakan ke-187 menghasilkan 188, dan 188 sudah dipakai
         * dua angkatan Yogyakarta. Yang dijaga di sini cuma satu hal — nomor
         * hasilnya belum dipakai angkatan lain mana pun di lokasi yang sama.
         */
        $this->assertGreaterThan(190, (int) $salinan->nama_ke,
            'Nomornya harus lebih besar daripada yang digandakan.');

        $this->assertSame(1, KategoriLayanan::where('layanan', $asal->layanan)
            ->whereRaw("coalesce(lokasi, '') = ?", [(string) $asal->lokasi])
            ->where('nama_ke', $salinan->nama_ke)->count(),
            'Nomor hasil penggandaan tidak boleh kembar dengan yang sudah ada.');
        $this->assertSame($asal->nama, $salinan->nama);
        $this->assertSame($asal->lokasi, $salinan->lokasi);
        $this->assertSame($asal->desc, $salinan->desc);
        $this->assertSame((string) $asal->total_kuota, (string) $salinan->total_kuota);

        $this->assertSame('draft', $salinan->status, 'Salinannya harus draf, bukan langsung aktif.');
        $this->assertNull($salinan->selesai, 'Tanggal selesainya harus dikosongkan.');
        $this->assertSame((string) $asal->total_kuota, (string) $salinan->sisa_kuota,
            'Salinannya belum punya pendaftar, jadi sisanya penuh lagi.');
        $this->assertNotSame($asal->token, $salinan->token, 'Tokennya harus baru.');
    }

    #[Test]
    public function karyawan_boleh_menggandakan_sama_seperti_membuat(): void
    {
        /*
         * Di layar ini karyawan memang boleh membuat dan mengubah angkatan —
         * beda dengan Tarif layanan yang administrator saja. Menggandakan cuma
         * cara cepat membuat, jadi haknya harus sama; membatasinya lebih ketat
         * daripada "tambah angkatan" justru tidak konsisten.
         */
        $karyawan = $this->akun(User::PERAN_KARYAWAN);
        $a = $this->angkatan(['nama_ke' => '300']);

        $this->actingAs($karyawan)
            ->post(route('account.kategori-layanan.gandakan', $a))
            ->assertRedirect();

        $this->assertNotNull(KategoriLayanan::where('nama_ke', '301')->first());
    }

    // ------------------------------------------------------------ ekspor

    #[Test]
    public function daftar_angkatan_bisa_diunduh_pdf_dan_excel(): void
    {
        /*
         * Layar kategori yang digantikan layar ini SUDAH punya keduanya;
         * menyatukannya tanpa itu berarti diam-diam mencabut kemampuan yang
         * sudah dipakai orang.
         */
        $admin = $this->akun();

        $pdf = $this->actingAs($admin)->get(route('account.kategori-layanan.cetak'));
        $pdf->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $excel = $this->actingAs($admin)->get(route('account.kategori-layanan.excel'));
        $excel->assertOk();
        $this->assertStringContainsString('spreadsheetml', $excel->headers->get('Content-Type'));
    }

    #[Test]
    public function unduhan_mengikuti_saringan_yang_sedang_dipakai(): void
    {
        // Yang diunduh orang hampir selalu yang sedang dilihatnya.
        $admin = $this->akun();

        $pdf = $this->actingAs($admin)
            ->get(route('account.kategori-layanan.cetak', ['layanan' => 'bibliometrik']));

        $pdf->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    #[Test]
    public function karyawan_boleh_mengunduh(): void
    {
        // Yang dibatasi mengubah angkatan, bukan membacanya.
        $karyawan = $this->akun(User::PERAN_KARYAWAN);

        $this->actingAs($karyawan)
            ->get(route('account.kategori-layanan.cetak'))
            ->assertOk();
    }

    // ------------------------------------------------------- pengurutan

    #[Test]
    public function daftar_bisa_diurutkan_dan_kolom_karangan_ditolak(): void
    {
        /*
         * Nilainya datang dari alamat, jadi nama kolom sembarang akan sampai ke
         * orderBy apa adanya kalau tidak dibatasi daftar tertutup.
         */
        $admin = $this->akun();

        $urut = $this->actingAs($admin)
            ->get(route('account.kategori-layanan.index', ['urut' => 'nama', 'arah' => 'naik']));

        $urut->assertOk();
        $this->assertSame('nama', $urut->viewData('urut'));

        $karangan = $this->actingAs($admin)
            ->get(route('account.kategori-layanan.index', ['urut' => 'password']));

        $karangan->assertOk();
        $this->assertSame('mulai', $karangan->viewData('urut'), 'Kolom karangan harus jatuh ke bawaan.');
    }

    // ------------------------------------------------------ keadaan kosong

    #[Test]
    public function keadaan_kosong_tersaring_tidak_bilang_belum_ada(): void
    {
        // Angkatannya ADA, cuma tidak cocok saringannya.
        $admin = $this->akun();

        $halaman = $this->actingAs($admin)
            ->get(route('account.kategori-layanan.index', ['cari' => 'zzzqqqxxx-tidak-mungkin']));

        $halaman->assertOk();
        $halaman->assertSee('Tidak ada yang cocok', false);
        $halaman->assertDontSee('Belum ada angkatan', false);
        $halaman->assertSee('mis-kosong-cari', false);
        $halaman->assertSee('Hapus saringan', false);
    }

    // ------------------------------------------------------------ penanda

    #[Test]
    public function kuota_habis_dan_tanggal_lewat_ditandai(): void
    {
        $penuh = $this->angkatan(['sisa_kuota' => '0']);
        $lewat = $this->angkatan([
            'mulai' => now()->subMonth(), 'selesai' => now()->subMonth()->addDays(2),
            'status' => 'active',
        ]);

        $this->assertTrue($penuh->kuota_habis);
        $this->assertTrue($lewat->sudah_lewat);

        /*
         * Dicari namanya, bukan membuka halaman pertama: daftarnya diurutkan
         * tanggal mulai terbaru dan berhalaman sepuluh, jadi angkatan yang
         * tanggalnya sudah lewat justru tidak ada di sana.
         */
        $admin = $this->akun();

        $isi = $this->actingAs($admin)
            ->get(route('account.kategori-layanan.index', ['cari' => 'Angkatan Uji']))
            ->getContent();

        $this->assertStringContainsString('Penuh', $isi);
        $this->assertStringContainsString('Lewat', $isi);
    }

    #[Test]
    public function peserta_dihitung_per_orang_bukan_per_baris(): void
    {
        /*
         * Yang dijumlahkan `jumlah_pendaftar`, BUKAN jumlah barisnya.
         *
         * Satu baris pendaftaran boleh berisi rombongan: dari 80 baris Scopus
         * Camp isinya 105 orang. Dihitung per baris, layar ini menulis 5
         * peserta untuk angkatan ke-175 sementara halaman publik — yang sejak
         * dulu memakai sum() — menulis 25 untuk angkatan yang sama, dan
         * penjaga kuota mengizinkan kuota disetel di bawah jumlah orang yang
         * sudah terdaftar.
         */
        KategoriLayanan::lupakanPendaftar();
        $hitung = KategoriLayanan::hitungPendaftar();

        $orang = 0;
        $baris = 0;

        foreach (KategoriLayanan::TABEL_PENDAFTARAN as $tabel) {
            $orang += (int) DB::table($tabel)->whereNotNull('kategori_id')
                ->selectRaw('sum(greatest(coalesce(jumlah_pendaftar, 1), 1)) as n')->value('n');
            $baris += DB::table($tabel)->whereNotNull('kategori_id')->count();
        }

        $this->assertSame($orang, array_sum($hitung));
        $this->assertSame($baris, array_sum(KategoriLayanan::hitungPendaftaran()));
    }

    #[Test]
    public function menggandakan_tidak_boleh_memakai_nomor_yang_sudah_terpakai(): void
    {
        // Dua angkatan bernomor berurutan: menggandakan yang pertama harus
        // MELEWATI yang kedua, bukan menabraknya.
        $satu = $this->angkatan(['nama_ke' => '910', 'lokasi' => 'Kota Uji']);
        $dua = $this->angkatan(['nama_ke' => '911', 'lokasi' => 'Kota Uji']);

        $this->actingAs($this->akun())
            ->post(route('account.kategori-layanan.gandakan', $satu))
            ->assertRedirect();

        /*
         * Dipilih dengan menyingkirkan dua yang sudah dikenal, BUKAN dengan
         * latest('created_at'): ketiganya dibuat pada detik yang sama, dan
         * urutannya jadi tidak menentukan — ujinya merah berselang-seling
         * sambil menunjuk kode yang benar.
         */
        $salinan = KategoriLayanan::where('lokasi', 'Kota Uji')
            ->whereKeyNot($satu->getKey())->whereKeyNot($dua->getKey())->first();

        $this->assertSame('912', (string) $salinan->nama_ke);
    }
}
