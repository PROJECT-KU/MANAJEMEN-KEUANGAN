<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\KategoriLayanan;
use App\PendaftaranPeserta;
use App\PendaftaranScopusCamp;
use App\Support\DaftarPresensi;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Lembar presensi peserta: satu baris per ORANG, satu ANGKATAN, bentuk PDF.
 *
 * Dulu berupa lembar kerja Excel atas seluruh hasil saringan. Yang terunduh
 * karena itu 496 orang dari 300 pendaftaran di belasan angkatan sekaligus —
 * terukur pada berkas yang benar-benar dihasilkan layar ini — padahal
 * presensi dipakai di meja registrasi SATU acara.
 */
class PresensiPesertaTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function satu_baris_per_orang_bukan_per_pendaftaran(): void
    {
        [$orang, $b] = $this->rombongan(4, ['Peserta Dua']);

        $isi = $this->isi($b);

        $this->assertCount(4, $isi,
            'Dibayar untuk 4 orang, tetapi lembarnya tidak memuat 4 baris.');

        $this->assertSame(['Pendaftar', 'Peserta', 'Peserta', 'Peserta'],
            array_column($isi, 'peran'));
    }

    /**
     * Kursi yang namanya belum diisi TETAP dibariskan.
     *
     * Dibuang, lembarnya terbaca seperti daftar yang sudah lengkap — dan
     * yang memegangnya di meja registrasi tidak tahu ada orang yang namanya
     * belum pernah ditanyakan.
     */
    #[Test]
    public function kursi_tanpa_nama_tetap_dibariskan(): void
    {
        [$orang, $b] = $this->rombongan(3, ['Peserta Dua']);

        $isi = $this->isi($b);
        $nama = array_column($isi, 'nama');

        $this->assertContains('(belum diisi)', $nama,
            'Kursi yang namanya belum diisi hilang dari lembar presensinya.');
        $this->assertSame(1, count(array_keys($nama, '(belum diisi)')));

        // Ditandai, bukan cuma bertuliskan begitu: lembarnya mencetaknya
        // samar supaya terlihat beda dari nama yang sungguhan.
        $this->assertSame([true, true, false], array_column($isi, 'terisi'));
    }

    /**
     * Afiliasi rombongan MENURUN dari pendaftarnya.
     *
     * Tabel peserta tidak punya kolomnya, dan rombongan memang datang dari
     * satu lembaga yang sama. Dikosongkan, separuh lembar presensi jadi
     * kolom kosong tanpa sebab yang terlihat.
     */
    #[Test]
    public function afiliasi_rombongan_menurun_dari_pendaftarnya(): void
    {
        [$orang, $b] = $this->rombongan(3, ['Peserta Dua'], ['affiliasi' => 'Universitas Uji']);

        $this->assertSame(['Universitas Uji', 'Universitas Uji', 'Universitas Uji'],
            array_column($this->isi($b), 'afiliasi'));
    }

    /**
     * Nomornya bentuk LOKAL, bukan +62.
     *
     * Lembar ini dibaca panitia di meja registrasi sambil menelepon yang
     * belum datang, dan nomor berawalan +62 tidak bisa dibandingkan sekilas
     * dengan yang tertulis di kartu peserta.
     */
    #[Test]
    public function nomornya_bentuk_lokal(): void
    {
        [$orang, $b] = $this->rombongan(2, ['Peserta Dua']);

        foreach (array_column($this->isi($b), 'telp') as $telp) {
            if ($telp === '') {
                continue;
            }

            $this->assertStringStartsWith('0', $telp,
                'Nomornya bukan bentuk lokal: ' . $telp);
        }
    }

    /**
     * Pesertanya dibaca dalam SATU kueri, bukan satu per pendaftaran.
     * Lembar satu angkatan memang menyentuh seluruh pendaftarannya
     * sekaligus; per baris, 60 pendaftaran berarti 60 kueri tambahan.
     */
    #[Test]
    public function pesertanya_dibaca_sekali_saja(): void
    {
        [$orang, $b] = $this->rombongan(3, ['Dua', 'Tiga']);

        DB::enableQueryLog();
        DB::flushQueryLog();

        $this->isi($b);

        $kueri = collect(DB::getQueryLog())
            ->filter(fn ($q) => str_contains($q['query'], 'pendaftaran_peserta'))
            ->count();

        $this->assertLessThanOrEqual(1, $kueri,
            'Peserta dibaca lebih dari sekali.');
    }

    /**
     * TANPA angkatan, unduhannya DITOLAK — bukan sekadar tombolnya mati.
     *
     * Tombol mati cuma menutup satu jalan; alamatnya masih bisa diketik,
     * dan yang keluar lembar presensi gabungan belasan angkatan yang persis
     * jadi sebab fitur ini ditulis ulang.
     */
    #[Test]
    public function tanpa_angkatan_unduhannya_ditolak(): void
    {
        [$orang, $b] = $this->rombongan(2, []);

        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.presensi'))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    #[Test]
    public function dengan_angkatan_yang_keluar_pdf(): void
    {
        [$orang, $b] = $this->rombongan(2, ['Peserta Dua']);

        $jawab = $this->actingAs($orang)->get(route(
            'account.pendaftaran-layanan.presensi', ['angkatan' => $b->kategori_id]
        ));

        $jawab->assertOk();

        $this->assertSame('application/pdf', $jawab->headers->get('content-type'));

        /*
         * Nama berkasnya menyebut ANGKATANNYA. Panitia mengunduh lembar ini
         * satu per angkatan menjelang acara, dan lima berkas bernama
         * "presensi-2026-10-11" di satu folder unduhan tidak bisa dibedakan
         * tanpa membukanya satu per satu.
         */
        $this->assertStringContainsString('presensi-angkatan-uji',
            strtolower((string) $jawab->headers->get('content-disposition')));

        // Benar-benar PDF, bukan HTML yang kepalanya diakui PDF.
        $this->assertStringStartsWith('%PDF-', $jawab->getContent());
    }

    /**
     * Lembarnya berlogo YAYASAN dan logonya benar-benar tertanam.
     *
     * Dompdf dijalankan dengan isRemoteEnabled = false, jadi alamat http apa
     * pun tidak akan diambil dan logonya hilang tanpa pesan. Satu-satunya
     * cara yang pasti menyisipkannya sebagai data URI.
     */
    #[Test]
    public function lembarnya_berlogo_yayasan_yang_tertanam(): void
    {
        $isi = (string) file_get_contents(
            resource_path('views/account/pendaftaran_layanan/presensi-pdf.blade.php')
        );

        $this->assertStringContainsString("public_path('assets/img/LogoRSC.png')", $isi,
            'Lembar presensi tidak memakai logo yayasan.');
        $this->assertStringContainsString('base64_encode', $isi,
            'Logonya ditautkan, bukan ditanam — Dompdf tanpa isRemoteEnabled '
            . 'tidak akan mengambilnya dan logonya hilang tanpa pesan.');
        $this->assertStringNotContainsString("asset('assets/img", $isi,
            'Logonya dirakit asset(); alamat http tidak diambil Dompdf.');
    }

    /** Tertutup untuk pelanggan, diperiksa TERPISAH dari layarnya. */
    #[Test]
    public function tertutup_untuk_pelanggan(): void
    {
        $t = Str::random(8);

        $pelanggan = User::create([
            'full_name' => 'Pelanggan Uji', 'username' => 'prs_' . $t,
            'email' => $t . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'), 'level' => 'user',
        ]);
        $pelanggan->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_PELANGGAN])->save();

        /*
         * Menutup halaman tanpa menutup unduhannya meninggalkan nama dan
         * nomor seluruh peserta tetap bisa diambil siapa pun yang tahu
         * alamatnya.
         */
        $this->actingAs($pelanggan)
            ->get(route('account.pendaftaran-layanan.presensi'))
            ->assertRedirect(route('account.dashboard.index'));
    }

    /**
     * Tombolnya MATI sampai angkatannya dipilih.
     *
     * Tanpa saringan itu yang terunduh 496 orang dari belasan angkatan
     * sekaligus, dan panitia harus memilahnya sendiri di luar sistem
     * sebelum bisa dicetak.
     */
    #[Test]
    public function tombolnya_mati_sebelum_angkatan_dipilih(): void
    {
        [$orang, $b] = $this->rombongan(2, []);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index'))
            ->assertOk()->getContent();

        $this->assertStringContainsString('mis-tombol-mati', $isi,
            'Tombol presensi tidak dimatikan padahal angkatannya belum dipilih.');

        /*
         * TIDAK ADA tautannya sama sekali, bukan tautan yang dipudarkan.
         * Atribut disabled tidak berlaku pada <a> — peramban tetap
         * membukanya, dan yang terlihat cuma tombol pudar yang ternyata
         * bisa ditekan.
         */
        $this->assertSame(0, preg_match(
            '/<a[^>]+href="[^"]*unduh-presensi/', $isi),
            'Tautan presensinya masih ada walau tombolnya terlihat mati.');
    }

    /** Dipilih angkatannya, tombolnya hidup dan membawa angkatan itu. */
    #[Test]
    public function tombolnya_hidup_sesudah_angkatan_dipilih(): void
    {
        [$orang, $b] = $this->rombongan(2, []);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['angkatan' => $b->kategori_id]))
            ->assertOk()->getContent();

        $this->assertSame(1, preg_match(
            '/<a[^>]+href="[^"]*unduh-presensi[^"]*"/', $isi, $cocok),
            'Tombol presensinya tidak jadi tautan walau angkatannya sudah dipilih.');

        /*
         * Angkatannya IKUT di tautannya. Tertinggal, unduhannya ditolak
         * pengendali — tombol hidup yang menghasilkan pesan galat.
         */
        $this->assertStringContainsString('angkatan=' . $b->kategori_id,
            html_entity_decode($cocok[0]),
            'Angkatan yang sedang disaring tidak ikut di tautan presensinya.');

        $this->assertStringNotContainsString('mis-tombol-mati', $isi);
    }

    // ------------------------------------------------------------- pembantu

    /** @return list<array<string, mixed>> */
    private function isi(PendaftaranScopusCamp $b): array
    {
        Pendaftaran::lupakan();

        return DaftarPresensi::orang(
            Pendaftaran::kueri()
                ->where('layanan', 'scopus_camp')->where('id', $b->getKey())->get()
        );
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp} */
    private function rombongan(int $jumlah, array $pesertaLain, array $ubah = []): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $t = Str::random(8);

        $u = User::create([
            'full_name' => 'Panitia Uji', 'username' => 'prs_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Uji ' . $t,
            'nama_ke' => '77',
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ]);

        $b = PendaftaranScopusCamp::create(array_merge([
            'id_transaksi' => 'PRS-' . $t, 'kategori_id' => $angkatan->id,
            'nama' => 'Pendaftar Utama', 'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0001', 'jumlah_pendaftar' => (string) $jumlah,
            'total_pembayaran' => '5500000', 'status' => 'diproses',
        ], $ubah));

        foreach ($pesertaLain as $i => $nama) {
            PendaftaranPeserta::create([
                'layanan' => 'scopus_camp', 'pendaftaran_id' => (string) $b->getKey(),
                'urutan' => $i + 1, 'nama' => $nama, 'telp' => '08123456789' . $i,
            ]);
        }

        return [$u, $b];
    }
}
