<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\Exports\DaftarHadirExport;
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
 * Daftar hadir: satu baris per ORANG, bukan per pendaftaran.
 *
 * Ekspor yang sudah ada membawa satu baris per pendaftaran, jadi rombongan
 * lima orang muncul sebagai satu nama. Terukur: 109 pendaftaran rombongan
 * mewakili 296 orang, sementara berkasnya cuma memuat 109 nama — padahal
 * layar Peserta sendiri menyebut daftar itu "yang dipakai menerbitkan
 * sertifikat dan memasukkan orang ke grup".
 */
class DaftarHadirTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function satu_baris_per_orang_bukan_per_pendaftaran(): void
    {
        [$orang, $b] = $this->rombongan(4, ['Peserta Dua']);

        $baris = $this->isi($b);

        $this->assertCount(4, $baris,
            'Dibayar untuk 4 orang, tetapi berkasnya tidak memuat 4 baris.');

        $this->assertSame(['Pendaftar', 'Peserta', 'Peserta', 'Peserta'],
            array_column($baris, 5));
    }

    /**
     * Kursi yang namanya belum diisi TETAP dibariskan.
     *
     * Dibuang, berkasnya terbaca seperti daftar yang sudah lengkap — dan
     * yang memegangnya di meja registrasi tidak tahu ada orang yang namanya
     * belum pernah ditanyakan.
     */
    #[Test]
    public function kursi_tanpa_nama_tetap_dibariskan(): void
    {
        [$orang, $b] = $this->rombongan(3, ['Peserta Dua']);

        $nama = array_column($this->isi($b), 4);

        $this->assertContains('(belum diisi)', $nama,
            'Kursi yang namanya belum diisi hilang dari daftar hadirnya.');
        $this->assertSame(1, count(array_keys($nama, '(belum diisi)')));
    }

    #[Test]
    public function nomor_telepon_tidak_jadi_bilangan(): void
    {
        [$orang, $b] = $this->rombongan(2, ['Peserta Dua']);

        foreach (array_column($this->isi($b), 6) as $telp) {
            if ($telp === '') {
                continue;
            }

            $this->assertStringStartsWith("'", $telp,
                'Nomornya tidak diawali kutip, jadi spreadsheet membuang nol depannya '
                . 'dan nomor itu tidak bisa dipakai menghubungi siapa pun.');
        }
    }

    /**
     * Pesertanya dibaca dalam SATU kueri, bukan satu per pendaftaran.
     * Ekspor memang pekerjaan yang menyentuh seluruh tabel sekaligus; per
     * baris, 290 pendaftaran berarti 290 kueri tambahan.
     */
    #[Test]
    public function pesertanya_dibaca_sekali_saja(): void
    {
        [$orang, $b] = $this->rombongan(3, ['Dua', 'Tiga']);

        \Illuminate\Support\Facades\DB::enableQueryLog();
        \Illuminate\Support\Facades\DB::flushQueryLog();

        $this->isi($b);

        $kueri = collect(\Illuminate\Support\Facades\DB::getQueryLog())
            ->filter(fn ($q) => str_contains($q['query'], 'pendaftaran_peserta'))
            ->count();

        $this->assertLessThanOrEqual(1, $kueri,
            'Peserta dibaca lebih dari sekali; ekspor 290 pendaftaran akan '
            . 'menjalankan ratusan kueri tambahan.');
    }

    #[Test]
    public function unduhannya_lembar_kerja_dan_tertutup_untuk_pelanggan(): void
    {
        [$orang, $b] = $this->rombongan(2, []);

        $jawab = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.hadir', ['cari' => $b->id_transaksi]));

        $jawab->assertOk();
        $this->assertStringContainsString('spreadsheet',
            strtolower((string) $jawab->headers->get('content-type')));

        $this->flushSession();

        $pelanggan = User::create([
            'full_name' => 'Pelanggan Uji', 'username' => 'hdr_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'), 'level' => 'user',
        ]);
        $pelanggan->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_PELANGGAN])->save();

        /*
         * Diperiksa TERPISAH dari layarnya: menutup halaman tanpa menutup
         * unduhannya meninggalkan nama dan nomor seluruh peserta tetap bisa
         * diambil siapa pun yang tahu alamatnya.
         */
        $this->actingAs($pelanggan)
            ->get(route('account.pendaftaran-layanan.hadir'))
            ->assertRedirect(route('account.dashboard.index'));
    }

    // ------------------------------------------------------------- pembantu

    /** @return list<array<int, mixed>> */
    private function isi(PendaftaranScopusCamp $b): array
    {
        Pendaftaran::lupakan();

        $baris = Pendaftaran::kueri()
            ->where('layanan', 'scopus_camp')->where('id', $b->getKey())->get();

        return (new DaftarHadirExport($baris, Pendaftaran::katalog()))->array();
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp} */
    private function rombongan(int $jumlah, array $pesertaLain): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $t = Str::random(8);

        $u = User::create([
            'full_name' => 'Panitia Uji', 'username' => 'hdr_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Uji ' . $t,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ]);

        $b = PendaftaranScopusCamp::create([
            'id_transaksi' => 'HDR-' . $t, 'kategori_id' => $angkatan->id,
            'nama' => 'Pendaftar Utama', 'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0001', 'jumlah_pendaftar' => (string) $jumlah,
            'total_pembayaran' => '5500000', 'status' => 'diproses',
        ]);

        foreach ($pesertaLain as $i => $nama) {
            PendaftaranPeserta::create([
                'layanan' => 'scopus_camp', 'pendaftaran_id' => (string) $b->getKey(),
                'urutan' => $i + 1, 'nama' => $nama, 'telp' => '08123456789' . $i,
            ]);
        }

        return [$u, $b];
    }
}
