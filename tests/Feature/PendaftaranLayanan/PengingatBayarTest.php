<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\Actions\Pendaftaran\KirimSuratPengingat;
use App\KategoriLayanan;
use App\Mail\PengingatPembayaranMail;
use App\PendaftaranJejak;
use App\PendaftaranScopusCamp;
use App\PendaftaranScopusKafe;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pengingat pembayaran untuk kelima layanan.
 *
 * Sebelum ini satu-satunya pengingat yang ada milik Webinar Eksklusif, dan
 * isinya pun soal "sesinya besok", bukan pembayaran. Empat layanan lain
 * tidak punya apa pun: pendaftar yang lupa transfer berdiam di "menunggu
 * bayar" sampai ada panitia yang kebetulan menyapanya satu per satu.
 *
 * Yang dijaga di sini bukan cuma "suratnya terkirim", melainkan kepada SIAPA
 * ia tidak boleh terkirim. Surat tagihan yang salah sasaran lebih merugikan
 * daripada tidak ada surat sama sekali.
 */
class PengingatBayarTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();
        Mail::fake();
    }

    #[Test]
    public function yang_belum_bayar_diingatkan_dan_tercatat(): void
    {
        $b = $this->camp();

        $this->jalankan();

        $this->assertDisurati($b);

        $jejak = $this->jejak($b);

        $this->assertCount(1, $jejak, 'Pengingatnya tidak tercatat di jejak.');
        $this->assertSame(KirimSuratPengingat::AKSI, $jejak[0]->aksi);
        $this->assertSame($b->email, $jejak[0]->ke, 'Jejaknya tidak menyebut ke mana suratnya pergi.');
        $this->assertStringContainsString('Pengingat pembayaran terkirim', $jejak[0]->kalimat);
    }

    /**
     * Yang paling mudah salah: Scopus Kafe berdiam di "menunggu verifikasi"
     * justru SESUDAH membayar. Mengiriminya "pembayaran Anda belum kami
     * terima" membuat orang yang sudah transfer mengira uangnya hilang.
     */
    #[Test]
    public function yang_sudah_mengunggah_bukti_tidak_ditagih(): void
    {
        $b = $this->kafe(['gambar' => 'bukti/pendaftaran_scopus_kafe/ada.webp']);

        $this->jalankan('scopus_kafe');

        $this->assertTidakDisurati($b,
            'Yang sudah mengunggah bukti ikut ditagih.');
    }

    #[Test]
    public function kafe_tanpa_bukti_tetap_diingatkan(): void
    {
        $b = $this->kafe(['gambar' => null]);

        $this->jalankan('scopus_kafe');

        $this->assertCount(1, $this->jejak($b, 'scopus_kafe'),
            'Prasyarat aturan bukti: yang memang belum mengunggah harus tetap diingatkan, '
            . 'kalau tidak, penjaga di atas hijau hanya karena tidak ada yang pernah dikirim.');
    }

    #[Test]
    public function yang_baru_mendaftar_belum_ditagih(): void
    {
        $b = $this->camp([], now()->subDay());

        $this->jalankan();

        $this->assertTidakDisurati($b, 'Yang mendaftar kemarin sudah ditagih.');
    }

    #[Test]
    public function yang_sudah_lunas_tidak_ditagih(): void
    {
        $b = $this->camp(['status' => 'Pendaftaran Diterima']);

        $this->jalankan();

        $this->assertTidakDisurati($b, 'Yang sudah lunas ikut ditagih.');
    }

    /**
     * Angkatannya sudah berjalan: menagih pembayaran untuk kegiatan yang
     * sudah lewat tidak ada gunanya, dan bagi penerimanya terbaca seperti
     * sistem yang tidak tahu apa-apa.
     */
    #[Test]
    public function angkatan_yang_sudah_lewat_tidak_ditagih(): void
    {
        $b = $this->camp([], null, now()->subWeek());

        $this->jalankan();

        $this->assertTidakDisurati($b,
            'Pendaftar angkatan yang sudah berjalan masih ditagih.');
    }

    #[Test]
    public function tidak_ditagih_dua_kali_dalam_sepekan(): void
    {
        $b = $this->camp();

        $this->jalankan();
        $this->jalankan();

        $this->assertCount(1, $this->jejak($b),
            'Perintahnya jalan tiap hari; tanpa jarak, pendaftar yang sama disurati '
            . 'tiap hari sampai ia membayar.');
    }

    #[Test]
    public function berhenti_sesudah_tiga_kali(): void
    {
        $b = $this->camp();

        // Tiga pengingat yang sudah lewat masa jedanya.
        foreach ([30, 20, 10] as $hari) {
            PendaftaranJejak::create([
                'layanan' => 'scopus_camp', 'pendaftaran_id' => (string) $b->getKey(),
                'aksi' => KirimSuratPengingat::AKSI, 'dari' => 'diproses', 'ke' => $b->email,
                'created_at' => now()->subDays($hari),
            ]);
        }

        $this->jalankan();

        $this->assertTidakDisurati($b, 'Pengingat keempat tetap terkirim.');
        $this->assertCount(3, $this->jejak($b), 'Yang keempat tetap tercatat.');
    }

    /**
     * Kegagalan pun dihitung sebagai "sudah dicoba". Kalau tidak, pendaftaran
     * yang alamat emailnya kosong dicoba lagi tiap hari dan menumpuk satu
     * baris jejak gagal per hari, selamanya.
     */
    #[Test]
    public function alamat_kosong_tercatat_dan_tidak_dicoba_tiap_hari(): void
    {
        $b = $this->camp(['email' => '']);

        $this->jalankan();
        $this->jalankan();

        $jejak = $this->jejak($b);

        $this->assertCount(1, $jejak);
        $this->assertSame(KirimSuratPengingat::AKSI_GAGAL, $jejak[0]->aksi);
        $this->assertStringContainsString('alamat emailnya kosong', $jejak[0]->kalimat);
    }

    /**
     * Suratnya dirakit kerangka bersama — logo yayasan, judul di tengah,
     * rincian dua lajur — bukan teks rata kiri tanpa gaya.
     */
    #[Test]
    public function suratnya_memakai_kerangka_yang_sama(): void
    {
        $b = $this->camp();

        $isi = (new PengingatPembayaranMail('scopus_camp', $b, 5))->render();

        $this->assertStringContainsString('logo-rsc-email.png', $isi,
            'Logo Rumah Scopus hilang dari suratnya.');
        $this->assertStringContainsString('align="center"', $isi,
            'Suratnya kembali rata kiri; kerangka bersamanya tidak terpakai.');
        $this->assertStringContainsString(strtoupper($b->id_transaksi), $isi,
            'Nomor pendaftarannya tidak disebut, padahal itu yang ditanyakan ke panitia.');
    }

    /**
     * Nominalnya disebut UTUH sampai digit terakhir. Dibulatkan, transfernya
     * tidak bisa dicocokkan dan pendaftarnya tetap tercatat belum bayar
     * meski uangnya sudah masuk.
     */
    #[Test]
    public function nominal_persisnya_disorot(): void
    {
        $b = $this->camp(['total_pembayaran' => '5500750', 'kode_unik' => '750']);

        $isi = (new PengingatPembayaranMail('scopus_camp', $b, 5))->render();

        $this->assertStringContainsString('Rp 5.500.750', $isi);
        $this->assertStringContainsString('jangan dibulatkan', $isi);
    }

    // ------------------------------------------------------------- pembantu

    /**
     * Surat dihitung PER BARIS, bukan se-aplikasi.
     *
     * `Mail::assertSent($kelas, 1)` dan `assertNothingSent()` menghitung
     * seluruh surat yang terkirim, termasuk untuk pendaftaran sungguhan yang
     * sudah ada di basis data — uji ini jalan dengan DatabaseTransactions di
     * atas data nyata, bukan di atas tabel kosong. Terbukti saat membuktikan
     * penjaganya merah: mematikan satu saringan membuat LIMA uji merah,
     * tiga di antaranya cuma karena kebetulan ada baris nyata yang ikut
     * tersurati. Penjaga yang bisa merah karena data orang lain tidak
     * menunjuk apa pun.
     */
    private function assertDisurati($baris, string $pesan = ''): void
    {
        Mail::assertSent(PengingatPembayaranMail::class,
            fn ($m) => (string) $m->pendaftaran->getKey() === (string) $baris->getKey());

        $this->assertTrue(true, $pesan);
    }

    private function assertTidakDisurati($baris, string $pesan): void
    {
        Mail::assertNotSent(PengingatPembayaranMail::class,
            fn ($m) => (string) $m->pendaftaran->getKey() === (string) $baris->getKey());

        $this->assertTrue(true, $pesan);
    }

    private function jalankan(string $layanan = 'scopus_camp'): void
    {
        $this->artisan('pendaftaran:ingatkan-bayar', ['--layanan' => $layanan])
            ->assertExitCode(0);
    }

    /** @return \Illuminate\Support\Collection<int, PendaftaranJejak> */
    private function jejak($baris, string $layanan = 'scopus_camp')
    {
        return PendaftaranJejak::milik($layanan, (string) $baris->getKey())
            ->whereIn('aksi', [KirimSuratPengingat::AKSI, KirimSuratPengingat::AKSI_GAGAL])
            ->orderBy('created_at')->get();
    }

    private function camp(array $ubah = [], $didaftarkan = null, $mulai = null): PendaftaranScopusCamp
    {
        $t = Str::random(8);

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Uji ' . $t,
            'mulai' => ($mulai ?: now()->addMonth())->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ]);

        $baris = PendaftaranScopusCamp::create(array_merge([
            'id_transaksi' => 'T-' . $t, 'kategori_id' => $angkatan->id,
            'nama' => 'Peserta ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0001', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => '5500000', 'status' => 'diproses',
        ], $ubah));

        // created_at dipaksa SESUDAH create: hook waktunya menimpa apa pun
        // yang diserahkan lewat create().
        $baris->forceFill(['created_at' => $didaftarkan ?: now()->subDays(10)])->save();

        return $baris->refresh();
    }

    private function kafe(array $ubah = []): PendaftaranScopusKafe
    {
        $t = Str::random(8);

        $baris = PendaftaranScopusKafe::create(array_merge([
            'id_pemesanan' => 'K-' . $t,
            'nama' => 'Pemesan ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0002',
            'total_keseluruhan_pembayaran' => '350000',
            'status' => 'menunggu verifikasi',
        ], $ubah));

        $baris->forceFill(['created_at' => now()->subDays(10)])->save();

        return $baris->refresh();
    }
}
