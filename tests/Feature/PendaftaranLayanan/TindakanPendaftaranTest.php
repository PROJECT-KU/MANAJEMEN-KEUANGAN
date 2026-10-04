<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\AnalisisBibliometrik;
use App\ClinikScopusPemesanan;
use App\ClinikScopusTestimoni;
use App\KategoriLayanan;
use App\Mail\AnalisisBibliometrikUpdateDiterimaMail;
use App\Mail\ScopusCampUpdateDiterimaMail;
use App\Mail\ScopusCampUpdateResheduleMail;
use App\Mail\UpdatePublicPendaftaranScopusKafeMail;
use App\PendaftaranScopusCamp;
use App\PendaftaranScopusKafe;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tindakan di layar Pendaftar Layanan: rincian, ubah data, ubah status, hapus.
 *
 * Seluruhnya dipindahkan dari lima layar pendaftaran per layanan yang dibuang.
 * Yang dijaga di sini terutama hal-hal yang TIDAK terlihat saat hilang:
 * email pemberitahuan yang berhenti terkirim, kuota yang tidak dikembalikan,
 * berkas bukti yang tertinggal di cakram, dan testimoni yang jadi yatim.
 */
class TindakanPendaftaranTest extends TestCase
{
    use DatabaseTransactions;

    /** @var array<int, string> berkas yang dibuat uji ini di cakram */
    private array $berkasUji = [];

    protected function setUp(): void
    {
        parent::setUp();

        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();
    }

    protected function tearDown(): void
    {
        /*
         * DatabaseTransactions TIDAK melindungi cakram.
         *
         * Uji di bawah membuat berkas bukti palsu untuk membuktikan
         * penghapusannya membuang berkasnya; kalau ujinya gagal di tengah,
         * berkas itu tertinggal di public/. Dibuang di sini, bukan di akhir
         * ujinya.
         */
        foreach ($this->berkasUji as $berkas) {
            if (is_file($berkas)) {
                @unlink($berkas);
            }
        }

        parent::tearDown();
    }

    /*
     * ------------------------------------------------------------------
     * Cara bayar
     * ------------------------------------------------------------------
     *
     * Sebelum ini satu-satunya cara bayar yang bisa dicatat adalah transfer,
     * jadi pendaftar yang DIBANTU panitia dan menyerahkan uangnya di tempat
     * tercatat "menunggu bayar" tanpa bukti — tidak bisa dibedakan dari yang
     * memang belum membayar.
     */

    #[Test]
    public function tunai_yang_uangnya_sudah_diterima_langsung_lunas(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Pembayar Tunai',
            'email' => 'tunai' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0010',
            'cara_bayar' => 'tunai',
            'uang_diterima' => '1',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Pembayar Tunai')->first();

        $this->assertNotNull($b);
        $this->assertSame('tunai', $b->cara_bayar);

        /*
         * Nilai status lunasnya BERBEDA tiap layanan ('Pendaftaran Diterima',
         * 'paid', 'pembayaran diterima'), jadi yang diperiksa adalah keadaan
         * ringkasnya — bukan untaian mentah yang hanya benar untuk satu
         * layanan.
         */
        $this->assertSame('lunas', Pendaftaran::keadaanDari($b->status));
        $this->assertStringContainsString('uang sudah diterima', (string) $b->note);
    }

    #[Test]
    public function tunai_yang_belum_dibayar_tetap_menunggu(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        // Centangnya TIDAK dikirim: orangnya baru akan membayar saat datang.
        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Bayar Nanti Di Tempat',
            'email' => 'nanti' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0011',
            'cara_bayar' => 'tunai',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Bayar Nanti Di Tempat')->first();

        $this->assertNotNull($b);
        $this->assertSame('tunai', $b->cara_bayar);
        $this->assertSame('menunggu', Pendaftaran::keadaanDari($b->status));
        $this->assertStringNotContainsString('uang sudah diterima', (string) $b->note);
    }

    #[Test]
    public function transfer_tidak_bisa_dilunaskan_dari_borang(): void
    {
        /*
         * Centang "uang diterima" dikirim bersama transfer. Diabaikan dengan
         * sengaja: transfer masih perlu dicocokkan dengan mutasi rekening, dan
         * melunaskannya dari borang berarti melunaskan sebelum ada yang
         * memeriksa buktinya.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Transfer Dipaksa Lunas',
            'email' => 'paksa' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0012',
            'cara_bayar' => 'transfer',
            'uang_diterima' => '1',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Transfer Dipaksa Lunas')->first();

        $this->assertNotNull($b);
        $this->assertSame('transfer', $b->cara_bayar);
        $this->assertSame('menunggu', Pendaftaran::keadaanDari($b->status));
    }

    #[Test]
    public function doku_tidak_bisa_dipilih_panitia(): void
    {
        /*
         * 'doku' ditulis jalur pendaftaran umum saat tagihannya dibuat.
         * Diterima dari borang ini, hasilnya baris bertanda sudah dibayar
         * daring padahal tidak ada tagihan yang pernah dibuat — dan tidak ada
         * yang bisa mencocokkannya ke mana pun.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Coba DOKU',
            'email' => 'doku' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0013',
            'cara_bayar' => 'doku',
        ])->assertSessionHasErrors('cara_bayar');

        $this->assertNull(PendaftaranScopusCamp::where('nama', 'Coba DOKU')->first());
    }

    #[Test]
    public function cara_bayar_ngawur_jatuh_ke_transfer_bukan_tersimpan_apa_adanya(): void
    {
        /*
         * Lapisan tindakannya tidak boleh bergantung pada validasi
         * pengendalinya: ia juga dipanggil dari tempat lain, dan nilai di luar
         * katalog berarti baris yang tidak punya label di layar mana pun.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $hasil = (new \App\Actions\Pendaftaran\BuatPendaftaran)->jalankan('scopus_camp', [
            'kategori_id' => $angkatan->id,
            'nama' => 'Cara Bayar Ngawur',
            'email' => 'ngawur' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0014',
            'cara_bayar' => 'gopay',
            'uang_diterima' => '1',
        ], $orang->full_name);

        $this->assertTrue($hasil['berhasil']);
        $this->assertSame('transfer', $hasil['model']->cara_bayar);
        // Jatuh ke transfer berarti centang "uang diterima" ikut tidak berlaku.
        $this->assertSame('menunggu', Pendaftaran::keadaanDari($hasil['model']->status));
    }

    #[Test]
    public function pembayar_tunai_tidak_ikut_tertandai_menggantung(): void
    {
        /*
         * Penanda menggantung mencari pendaftar yang sudah lama menunggu tanpa
         * kabar supaya DITAGIH. Pembayar tunai tidak sedang ditunggu
         * transfernya, jadi memasukkannya berarti daftar tagihan yang isinya
         * orang yang tidak perlu ditagih — dan daftar seperti itu berhenti
         * dibaca.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $lama = now()->subDays(30);

        $tunai = PendaftaranScopusCamp::create([
            'id_transaksi' => 'UJI-TUNAI-' . Str::random(6),
            'kategori_id' => $angkatan->id,
            'nama' => 'Menunggu Tapi Tunai',
            'email' => 'lama1' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0015',
            'total_pembayaran' => '1000000',
            'status' => 'diproses',
            'cara_bayar' => 'tunai',
        ]);
        $tunai->forceFill(['created_at' => $lama, 'updated_at' => $lama])->save();

        $transfer = PendaftaranScopusCamp::create([
            'id_transaksi' => 'UJI-TRF-' . Str::random(6),
            'kategori_id' => $angkatan->id,
            'nama' => 'Menunggu Lewat Transfer',
            'email' => 'lama2' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0016',
            'total_pembayaran' => '1000000',
            'status' => 'diproses',
            'cara_bayar' => 'transfer',
        ]);
        $transfer->forceFill(['created_at' => $lama, 'updated_at' => $lama])->save();

        $jawab = $this->actingAs($orang)->get(
            route('account.pendaftaran-layanan.index', ['lama' => '1', 'layanan' => 'scopus_camp'])
        );

        $jawab->assertOk();
        $jawab->assertSee('Menunggu Lewat Transfer');
        $jawab->assertDontSee('Menunggu Tapi Tunai');
    }

    #[Test]
    public function saringan_cara_bayar_memisahkan_keduanya(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        foreach ([['Disaring Tunai', 'tunai'], ['Disaring Transfer', 'transfer']] as [$nama, $cara]) {
            PendaftaranScopusCamp::create([
                'id_transaksi' => 'UJI-SAR-' . Str::random(6),
                'kategori_id' => $angkatan->id,
                'nama' => $nama,
                'email' => 'saring' . Str::random(6) . '@contoh.test',
                'telp' => '0816-0000-0017',
                'total_pembayaran' => '1000000',
                'status' => 'diproses',
                'cara_bayar' => $cara,
            ]);
        }

        $jawab = $this->actingAs($orang)->get(
            route('account.pendaftaran-layanan.index', ['bayar' => 'tunai', 'layanan' => 'scopus_camp'])
        );

        $jawab->assertOk();
        $jawab->assertSee('Disaring Tunai');
        $jawab->assertDontSee('Disaring Transfer');
    }

    #[Test]
    public function baris_lama_tanpa_cara_bayar_disebut_transfer(): void
    {
        /*
         * Baris dari sebelum kolomnya ada. Disebut transfer, bukan "belum
         * dikenali": sebelum kolom itu ada, transfer satu-satunya jalur yang
         * disediakan borangnya — jadi itu bukan terkaan.
         */
        $this->assertSame('transfer', Pendaftaran::caraBayar(null)['kunci']);
        $this->assertSame('transfer', Pendaftaran::caraBayar('')['kunci']);

        // Nilai tak dikenal tetap dapat label, dan warnanya netral — bukan
        // hijau, yang akan terbaca seperti sudah dibayar.
        $this->assertSame('mis-abu', Pendaftaran::caraBayar('gopay')['warna']);
    }

    #[Test]
    public function status_lunas_ada_untuk_kelima_layanan(): void
    {
        /*
         * Kelimanya memakai kosakata status yang berbeda. Satu saja yang tidak
         * terpetakan, panitia yang menerima uang tunai di sana mencatatnya dan
         * barisnya diam-diam tetap "menunggu bayar".
         */
        foreach (array_keys(Pendaftaran::katalog()) as $layanan) {
            $this->assertNotNull(
                Pendaftaran::statusLunas($layanan),
                "Layanan {$layanan} tidak punya nilai status lunas yang terpetakan."
            );
        }
    }

    /*
     * ------------------------------------------------------------------
     * Varian untuk layanan tanpa angkatan
     * ------------------------------------------------------------------
     *
     * Empat layanan mewarisi variannya dari angkatan yang dipilih. Scopus
     * Kafe tidak berangkatan — terukur nol baris angkatan — jadi sebelum ini
     * varian Online/Offline yang disetel di layar Layanan tidak muncul di
     * borang sama sekali, dan tidak punya tempat untuk disimpan.
     */

    #[Test]
    public function scopus_kafe_menyimpan_varian_yang_dipilih(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $this->berivarian('scopus_kafe', ['online' => 'Online', 'offline' => 'Offline']);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_kafe',
            'nama' => 'Peserta Kafe Daring',
            'email' => 'kafe' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0020',
            'total' => '900.000',
            'varian' => 'online',
            // Sesinya ikut dikirim sebab varian yang punya daftar sesi kini
            // menuntutnya; yang diperiksa uji ini tetap variannya.
            'sesi' => 'sesi 1',
        ])->assertRedirect();

        $b = PendaftaranScopusKafe::where('nama', 'Peserta Kafe Daring')->first();

        $this->assertNotNull($b);
        $this->assertSame('online', $b->varian);
        $this->assertSame(900000 + (int) $b->kode_unik_pembayaran, (int) $b->total_keseluruhan_pembayaran);
    }

    #[Test]
    public function varian_di_luar_daftar_layanannya_disimpan_kosong(): void
    {
        /*
         * Daftar variannya hidup di kolom JSON `layanan.varian` yang disunting
         * orang lewat aplikasi — bukan daftar tertutup di kode. Nilai karangan
         * yang diterima apa adanya berarti baris yang tarifnya tidak akan
         * pernah ketemu, dan tidak ada yang tahu sebabnya.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $this->berivarian('scopus_kafe', ['online' => 'Online', 'offline' => 'Offline']);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_kafe',
            'nama' => 'Peserta Varian Karangan',
            'email' => 'karang' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0021',
            'total' => '500.000',
            'varian' => 'hibrida',
        ])->assertRedirect();

        $b = PendaftaranScopusKafe::where('nama', 'Peserta Varian Karangan')->first();

        $this->assertNotNull($b);
        $this->assertNull($b->varian);
    }

    #[Test]
    public function layanan_berangkatan_tidak_menyimpan_varian_dari_borang(): void
    {
        /*
         * Variannya datang dari ANGKATAN, bukan dari kiriman borang. Tabelnya
         * pun tidak punya kolomnya, jadi nilai yang terbawa harus diabaikan —
         * bukan membuat kirimannya gagal.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Varian Terbawa',
            'email' => 'bawa' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0022',
            'varian' => 'offline',
        ])->assertRedirect();

        $this->assertNotNull(
            PendaftaranScopusCamp::where('nama', 'Peserta Varian Terbawa')->first()
        );
    }

    /** Menyetel daftar varian satu layanan, sebatas transaksi uji ini. */
    private function berivarian(string $kode, array $varian): void
    {
        $isi = [];

        foreach ($varian as $k => $nama) {
            $isi[] = ['kode' => $k, 'nama' => $nama];
        }

        \App\Layanan::where('kode', $kode)->update(['varian' => json_encode($isi)]);
        \App\Layanan::lupakanKatalog();
    }

    /*
     * ------------------------------------------------------------------
     * Pendaftar ganda, bukti, kabar, dan simpan-lagi
     * ------------------------------------------------------------------
     */

    #[Test]
    public function orang_yang_sudah_terdaftar_ditahan_dengan_keterangan(): void
    {
        /*
         * Sebelumnya tidak ada pemeriksaan sama sekali: orang yang sama bisa
         * didaftarkan dua kali ke angkatan yang sama tanpa peringatan, dan
         * kuotanya ikut berkurang dua kali.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $email = 'ganda' . Str::random(6) . '@contoh.test';

        $kirim = fn () => $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Kembar',
            'email' => $email,
            'telp' => '0816-0000-0030',
        ]);

        $kirim()->assertRedirect();
        $sisa = (int) $angkatan->refresh()->sisa_kuota;

        $kirim()->assertSessionHasErrors('email');

        $this->assertSame(
            1,
            PendaftaranScopusCamp::where('email', $email)->count(),
            'Kirim kedua tidak boleh membuat baris kedua.'
        );
        $this->assertSame(
            $sisa,
            (int) $angkatan->refresh()->sisa_kuota,
            'Kuota tidak boleh berkurang untuk kiriman yang ditahan.'
        );
    }

    #[Test]
    public function nomor_yang_sama_dengan_pemisah_berbeda_tetap_terdeteksi(): void
    {
        /*
         * "0816-0000-0031" dan "+62 816 0000 0031" orang yang sama. Dibanding
         * sebagai untaian mentah keduanya tidak pernah cocok, jadi
         * pemeriksaannya mengadu ANGKA SAJA.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Nomor Sama',
            'email' => 'satu' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0031',
        ])->assertRedirect();

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Nomor Sama Lagi',
            'email' => 'dua' . Str::random(6) . '@contoh.test',
            'telp' => '0816 0000 0031',
        ])->assertSessionHasErrors('email');

        $this->assertNull(
            PendaftaranScopusCamp::where('nama', 'Peserta Nomor Sama Lagi')->first()
        );
    }

    #[Test]
    public function panitia_bisa_melanjutkan_kalau_memang_orang_berbeda(): void
    {
        /*
         * Bukan larangan keras: dua orang berbeda bisa berbagi satu nomor
         * WhatsApp keluarga, dan panitia yang tahu itu harus tetap bisa
         * melanjutkan.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Kakak',
            'email' => 'kakak' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0032',
        ])->assertRedirect();

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Adik',
            'email' => 'adik' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0032',
            'abaikan_ganda' => '1',
        ])->assertRedirect();

        $this->assertNotNull(PendaftaranScopusCamp::where('nama', 'Adik')->first());
    }

    #[Test]
    public function angkatan_berbeda_bukan_pendaftar_ganda(): void
    {
        // Orang yang sama memang boleh ikut angkatan Oktober dan November.
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $satu = $this->angkatan('scopus_camp', 20, 20);
        $dua = KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Angkatan Kedua ' . Str::random(5),
            'mulai' => now()->addMonths(2)->toDateString(),
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'status' => 'active',
        ]);
        $email = 'duakali' . Str::random(6) . '@contoh.test';

        foreach ([$satu, $dua] as $a) {
            $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
                'layanan' => 'scopus_camp',
                'kategori_id' => $a->id,
                'nama' => 'Peserta Dua Angkatan',
                'email' => $email,
                'telp' => '0816-0000-0033',
            ])->assertRedirect();
        }

        $this->assertSame(2, PendaftaranScopusCamp::where('email', $email)->count());
    }

    #[Test]
    public function simpan_dan_tambah_lagi_kembali_ke_borang_dengan_angkatannya(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $jawab = $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Rombongan Satu',
            'email' => 'rombong' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0034',
            'lagi' => '1',
        ]);

        $jawab->assertRedirect(route('account.pendaftaran-layanan.baru', [
            'layanan' => 'scopus_camp',
            'kategori' => $angkatan->id,
        ]));

        $this->assertNotNull(PendaftaranScopusCamp::where('nama', 'Rombongan Satu')->first());
    }

    #[Test]
    public function bukti_bayar_bisa_diunggah_saat_mendaftarkan(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $jawab = $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Pembawa Struk',
            'email' => 'struk' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0035',
            'bukti' => \Illuminate\Http\UploadedFile::fake()->image('struk.jpg', 40, 40),
        ]);

        $jawab->assertRedirect();
        $b = PendaftaranScopusCamp::where('nama', 'Pembawa Struk')->first();

        $this->assertNotNull($b);
        $this->assertNotEmpty($b->gambar);

        /*
         * Berkasnya ditulis ke cakram SUNGGUHAN; DatabaseTransactions tidak
         * mengembalikan berkas. Dibuang di sini, dan keberadaannya dibuktikan
         * dulu supaya ujinya tidak lulus hanya karena tidak ada yang ditulis.
         */
        $jalur = public_path('ScopusCamp/' . $b->gambar);

        $this->assertFileExists($jalur);
        @unlink($jalur);
        $this->assertFileDoesNotExist($jalur);
    }

    #[Test]
    public function pendaftar_dikabari_hanya_kalau_dicentang(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Tanpa Kabar',
            'email' => 'sepi' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0036',
        ])->assertRedirect();

        \Illuminate\Support\Facades\Mail::assertNothingSent();

        $alamat = 'kabar' . Str::random(6) . '@contoh.test';

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Dapat Kabar',
            'email' => $alamat,
            'telp' => '0816-0000-0037',
            'kabari' => '1',
        ])->assertRedirect();

        \Illuminate\Support\Facades\Mail::assertSent(
            \App\Mail\PendaftaranDicatatMail::class,
            fn ($m) => $m->hasTo($alamat)
        );
    }

    #[Test]
    public function sesi_scopus_kafe_tersimpan_beserta_jamnya(): void
    {
        /*
         * Pernyataan lama uji ini mengunci kotak teks BEBAS — ia menyimpan
         * "Sesi 1 — Menyusun pendahuluan" dan menganggapnya benar. Aturannya
         * memang diganti, bukan ujinya yang kebetulan rewel: Scopus Kafe
         * berjalan pada jam tetap, dan teks bebas membuat satu sesi yang sama
         * tersimpan "Sesi 1", "sesi1", atau "pagi" sehingga daftar hadir per
         * sesi tidak bisa dikelompokkan sama sekali.
         *
         * Jamnya ikut diperiksa. Kolom `waktu_mulai`/`waktu_selesai` sudah
         * ada dan selalu diisi jalur pendaftaran umum; dibiarkan kosong,
         * pendaftaran buatan panitia terlihat belum berjadwal di layar yang
         * membaca keduanya.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_kafe',
            'nama' => 'Peserta Bersesi',
            'email' => 'sesi' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0038',
            'total' => '750.000',
            'varian' => 'offline',
            'sesi' => 'sesi 2',
        ])->assertRedirect();

        $b = PendaftaranScopusKafe::where('nama', 'Peserta Bersesi')->first();

        $this->assertNotNull($b);
        $this->assertSame('sesi 2', $b->sesi);
        $this->assertSame('13:00:00', (string) $b->waktu_mulai);
        $this->assertSame('18:00:00', (string) $b->waktu_selesai);
    }

    #[Test]
    public function sesi_yang_tidak_ada_di_variannya_ditolak(): void
    {
        /*
         * Online hanya punya sesi pagi. "Sesi 2" di sana adalah sesi yang
         * memang tidak pernah ada — dan diterima diam-diam, pendaftarnya
         * dijanjikan jam yang tidak akan dibuka.
         *
         * Ditolak, bukan dibuang diam-diam: pendaftaran yang tersimpan TANPA
         * sesi padahal panitia merasa sudah memilihnya adalah kesalahan yang
         * tidak terlihat sampai hari pelaksanaan.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_kafe',
            'nama' => 'Sesi Karangan',
            'email' => 'karangan' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0039',
            'total' => '750.000',
            'varian' => 'online',
            'sesi' => 'sesi 2',
        ])->assertSessionHasErrors('sesi');

        $this->assertNull(PendaftaranScopusKafe::where('nama', 'Sesi Karangan')->first());

        // Dan sesi yang memang ada di variannya tetap lolos — supaya
        // penolakannya tidak terbaca sebagai "semua ditolak".
        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_kafe',
            'nama' => 'Sesi Benar',
            'email' => 'benar' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0040',
            'total' => '750.000',
            'varian' => 'online',
            'sesi' => 'sesi 1',
        ])->assertRedirect();

        $b = PendaftaranScopusKafe::where('nama', 'Sesi Benar')->first();

        $this->assertNotNull($b);
        $this->assertSame('08:00:00', (string) $b->waktu_mulai);
    }

    #[Test]
    public function total_yang_diminta_sudah_memuat_kode_uniknya(): void
    {
        /*
         * Kode uniknya harus MASUK ke total, bukan disimpan di sebelahnya.
         *
         * Begitulah jalur pendaftaran umum menyimpannya — terukur di basis
         * data: total 4.500.072 dengan kode 72, 4.275.028 dengan kode 28.
         * Jalur panitia dulu menyimpan 4.950.000 dengan kode 50, jadi nominal
         * yang diminta tidak pernah memuat penandanya dan tidak ada transfer
         * yang bisa dicocokkan dengannya. Suratnya bahkan menulis "angka 50 di
         * ujungnya" pada angka yang berakhir 000.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $angkatan->forceFill(['biaya' => '1000000', 'total_biaya' => '1000000'])->save();

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Kode Ikut',
            'email' => 'kodeikut' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0040',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Peserta Kode Ikut')->first();

        $this->assertNotNull($b);

        $kode = (int) $b->kode_unik;
        $total = (int) $b->total_pembayaran;

        $this->assertGreaterThan(0, $kode, 'Scopus Camp memakai kode unik.');
        $this->assertSame(1000000 + $kode, $total);

        /*
         * Selisih nominal dengan harga pokoknya HARUS persis kode uniknya —
         * itulah yang dicocokkan panitia dengan mutasi rekening, dan itulah
         * yang dulu selalu nol.
         *
         * Bukan "tiga angka terakhir": sejak rentangnya diseragamkan ke
         * 500-1500, kode 1.188 tidak muat di tiga angka, dan pernyataan
         * lamanya merah untuk kode yang justru sah.
         */
        $this->assertSame($kode, $total - 1000000, 'Selisihnya harus kode uniknya sendiri.');
        $this->assertGreaterThanOrEqual(500, $kode);
        $this->assertLessThanOrEqual(1500, $kode);
    }

    #[Test]
    public function dua_pendaftar_seangkatan_tidak_pernah_bernominal_sama(): void
    {
        /*
         * Nominal kembar membuat dua transfer tidak bisa dibedakan milik
         * siapa. Pemeriksaannya mengadu kolom `total` apa adanya — sebab
         * total itu SUDAH memuat kodenya; menambahkannya sekali lagi
         * menghitung kodenya dua kali dan bentrokan yang sebenarnya tidak
         * pernah terlihat.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $angkatan->forceFill(['biaya' => '1000000', 'total_biaya' => '1000000'])->save();

        $nominal = [];

        for ($i = 1; $i <= 6; $i++) {
            $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
                'layanan' => 'scopus_camp',
                'kategori_id' => $angkatan->id,
                'nama' => 'Kembar Nominal ' . $i,
                'email' => 'kembar' . $i . Str::random(6) . '@contoh.test',
                'telp' => '0816-0000-01' . $i,
                'abaikan_ganda' => '1',
            ])->assertRedirect();

            $nominal[] = (int) PendaftaranScopusCamp::where('nama', 'Kembar Nominal ' . $i)
                ->value('total_pembayaran');
        }

        $this->assertCount(6, array_unique($nominal), 'Nominalnya tidak boleh ada yang kembar.');
    }

    #[Test]
    public function nama_peserta_rombongan_tersimpan_dan_tampil(): void
    {
        /*
         * Kelima tabel pendaftaran hanya punya SATU nama, sementara jumlahnya
         * bisa lebih dari satu — terukur 6 baris sudah berjumlah lebih dari
         * satu, dan nama peserta selain pemesannya tidak ada di mana pun.
         * Daftar hadir rombongan jadi tidak bisa dibuat dari sistem.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Pemesan Rombongan',
            'email' => 'rombongan' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0050',
            'jumlah' => 3,
            // Ditempel dari WhatsApp, lengkap dengan penomorannya.
            'peserta' => "1. Budi Santoso\n2) Siti Rahma\n\n   ",
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Pemesan Rombongan')->first();
        $this->assertNotNull($b);

        $peserta = \App\PendaftaranPeserta::milik('scopus_camp', (string) $b->id)
            ->terurut()->pluck('nama')->all();

        // Penomorannya dibuang, baris kosongnya diabaikan, urutannya tetap.
        $this->assertSame(['Budi Santoso', 'Siti Rahma'], $peserta);

        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->id]))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Siti Rahma')
            ->assertSee('Pemesan Rombongan');
    }

    #[Test]
    public function nomor_peserta_rombongan_ikut_tersimpan_dan_dinormalkan(): void
    {
        /*
         * Namanya saja tidak cukup. Tanpa nomor tiap orang, rombongan tujuh
         * orang hanya punya SATU nomor yang bisa dihubungi — nomor pemesannya
         * — sehingga undangan grup, pengingat jadwal, dan tautan sertifikat
         * enam orang lain bergantung pada ia mau meneruskan.
         *
         * Keempat bentuk yang benar-benar beredar diuji sekaligus: koma, tab
         * (hasil tempelan langsung dari Excel), nomor menempel di ujung baris,
         * dan nama tanpa nomor sama sekali. Satu bentuk saja yang diuji
         * membuat tiga bentuk lain bisa rusak tanpa ketahuan.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Pemesan Bernomor',
            'email' => 'bernomor' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0052',
            'jumlah' => 5,
            'peserta' => "1. Budi Santoso, 081234567890\n"
                . "Siti Rahma\t0895-4217-3544\n"
                . "Agus Nugroho 6281122334455\n"
                . 'Rina Tanpa Nomor',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Pemesan Bernomor')->first();
        $this->assertNotNull($b);

        $peserta = \App\PendaftaranPeserta::milik('scopus_camp', (string) $b->id)
            ->terurut()->get(['nama', 'telp'])
            ->map(fn ($p) => [$p->nama, $p->telp])->all();

        // Nomornya disimpan dalam SATU bentuk, 62xxx, sama seperti nomor
        // pendaftar utama di kelima tabel — kalau tidak, dua nomor yang sama
        // orangnya tersimpan dua bentuk dan pencarian hanya menemukan satu.
        $this->assertSame([
            ['Budi Santoso', '6281234567890'],
            ['Siti Rahma', '6289542173544'],
            ['Agus Nugroho', '6281122334455'],
            ['Rina Tanpa Nomor', null],
        ], $peserta);

        // Di layar rincian nomornya jadi tautan WhatsApp: daftar ini dibuka
        // justru saat panitia hendak menghubungi orangnya satu per satu.
        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->id]))
            ->assertOk()
            ->assertSee('wa.me/6281234567890')
            ->assertSee('081234567890');
    }

    #[Test]
    public function baris_judul_kolom_tidak_jadi_peserta(): void
    {
        /*
         * Menempelkan tabel Excel ke kotak teksnya ikut membawa baris
         * judulnya. Tanpa penjaga ini, "Nama" tersimpan sebagai peserta
         * pertama — dan nomor urut sertifikat SEMUA orang di rombongan itu
         * meleset satu.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Pemesan Bertabel',
            'email' => 'tabel' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0053',
            'jumlah' => 3,
            'peserta' => "Nama\tNo HP\nBudi Santoso\t081234567890\nSiti Rahma\t085700011122",
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Pemesan Bertabel')->first();

        $this->assertSame(
            ['Budi Santoso', 'Siti Rahma'],
            \App\PendaftaranPeserta::milik('scopus_camp', (string) $b->id)->terurut()->pluck('nama')->all()
        );
    }

    #[Test]
    public function berkas_excel_peserta_dibaca_jadi_nama_dan_nomor(): void
    {
        /*
         * Lembaga mengirim daftar pesertanya sebagai lampiran, bukan diketik
         * di badan pesan. Tanpa jalur ini, rombongan 30 orang berarti 30
         * baris yang disalin satu per satu — pekerjaan yang paling mungkin
         * dilewati panitia, dan begitu dilewati, 29 nomor pesertanya hilang.
         *
         * Kolomnya dikenali dari ISINYA, bukan judulnya: berkas lembaga tidak
         * pernah berjudul sama ("No HP", "No. WA", "Telepon", "Kontak"), dan
         * mencocokkan judul berarti berkas di luar daftar itu kehilangan
         * seluruh nomornya diam-diam.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $isi = "No,Nama Peserta,Kontak\n"
            . "1,Budi Santoso,081234567890\n"
            . "2,Siti Rahma,0895-4217-3544\n"
            . "3,Rina Tanpa Nomor,\n";

        $jawab = $this->actingAs($orang)->post(route('account.pendaftaran-layanan.baca-peserta'), [
            'berkas' => UploadedFile::fake()->createWithContent('peserta.csv', $isi),
        ]);

        $jawab->assertOk()
            ->assertJson([
                'ok' => true,
                'jumlah' => 3,
                // Berapa yang BERNOMOR disebut terpisah: daftar yang terbaca
                // namanya saja terlihat berhasil, padahal justru nomornya yang
                // hendak dikumpulkan.
                'bernomor' => 2,
            ]);

        /*
         * Ditulis balik dalam bentuk 08xx, bukan 62xx yang disimpan. Panitia
         * yang melihat nomor yang baru saja ia kirim berubah bentuk akan
         * mengira berkasnya salah terbaca lalu membetulkannya kembali.
         */
        $this->assertSame(
            "Budi Santoso, 081234567890\nSiti Rahma, 089542173544\nRina Tanpa Nomor",
            $jawab->json('teks'),
            'Kolom "No" tidak boleh jadi nama, dan baris judulnya tidak boleh jadi orang.'
        );
    }

    #[Test]
    public function berkas_peserta_yang_tidak_bisa_dibaca_ditolak_dengan_kalimat_biasa(): void
    {
        /*
         * Penggunanya bukan orang yang paham jenis berkas. Ditolak tanpa
         * kalimat yang menyebutkan HARUS APA, ia akan mencoba berkas yang sama
         * berulang kali — jadi yang dijaga di sini bukan penolakannya,
         * melainkan kalimatnya.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.baca-peserta'), [
            'berkas' => UploadedFile::fake()->create('daftar.pdf', 8),
        ])->assertStatus(422)->assertJson([
            'ok' => false,
            'pesan' => 'Berkasnya harus Excel (.xlsx atau .xls) atau CSV.',
        ]);

        // Berkas yang jenisnya benar tetapi tidak memuat satu nama pun juga
        // harus bersuara: diam membuat panitia mengira daftarnya sudah masuk.
        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.baca-peserta'), [
            'berkas' => UploadedFile::fake()->createWithContent('kosong.csv', "Nama,No HP\n"),
        ])->assertStatus(422)->assertJson([
            'ok' => false,
            'pesan' => 'Tidak ada nama yang terbaca di berkas itu.',
        ]);
    }

    #[Test]
    public function berkas_peserta_hanya_boleh_dibaca_orang_dalam(): void
    {
        // Titik masuk yang menerima unggahan tanpa penjaga adalah tempat orang
        // luar menaruh berkas di peladen ini.
        $this->post(route('account.pendaftaran-layanan.baca-peserta'), [
            'berkas' => UploadedFile::fake()->createWithContent('peserta.csv', "Budi,081234567890\n"),
        ])->assertRedirect();
    }

    #[Test]
    public function nama_peserta_dibatasi_jumlah_yang_dibayar(): void
    {
        /*
         * Nama ke-empat pada rombongan berbayar tiga adalah orang yang
         * kursinya tidak pernah dibeli — mencatatnya berarti daftar hadir
         * yang lebih panjang daripada kursi yang terjual.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Pemesan Berlebih',
            'email' => 'lebih' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0051',
            'jumlah' => 2,
            'peserta' => "Peserta Dua\nPeserta Tiga\nPeserta Empat",
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Pemesan Berlebih')->first();

        $this->assertSame(
            ['Peserta Dua'],
            \App\PendaftaranPeserta::milik('scopus_camp', (string) $b->id)->terurut()->pluck('nama')->all(),
            'Dua orang dibayar: pemesannya sendiri plus satu nama.'
        );
    }

    #[Test]
    public function peserta_rombongan_ikut_terhapus_bersama_pendaftarannya(): void
    {
        /*
         * Pesertanya menunjuk pendaftarannya lewat pasangan (layanan,
         * pendaftaran_id) tanpa kunci asing, jadi basis datanya tidak akan
         * membersihkannya sendiri — barisnya akan tinggal sebagai yatim.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Pemesan Akan Dihapus',
            'email' => 'hapus' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0052',
            'jumlah' => 2,
            'peserta' => 'Ikut Terhapus',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Pemesan Akan Dihapus')->first();
        $id = (string) $b->id;

        $this->assertSame(1, \App\PendaftaranPeserta::milik('scopus_camp', $id)->count());

        $this->actingAs($orang)
            ->delete(route('account.pendaftaran-layanan.hapus', ['scopus_camp', $id]))
            ->assertRedirect();

        $this->assertSame(
            0,
            \App\PendaftaranPeserta::milik('scopus_camp', $id)->count(),
            'Pesertanya tidak boleh tertinggal sebagai baris yatim.'
        );
    }

    #[Test]
    public function slip_memuat_nomor_nominal_dan_peserta_rombongannya(): void
    {
        /*
         * Pendaftar yang datang langsung dan membayar tunai sebelumnya pulang
         * tanpa pegangan apa pun: nomor dan kode uniknya hanya ada di layar
         * panitia dan di email — dan sebagian dari mereka tidak punya email.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Pemegang Slip',
            'telp' => '0816-0000-0060',
            'jumlah' => 2,
            'peserta' => 'Teman Sebelah',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Pemegang Slip')->first();
        $this->assertNotNull($b);

        $jawab = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.slip', ['scopus_camp', $b->id]));

        $jawab->assertOk();
        $jawab->assertSee($b->id_transaksi);
        $jawab->assertSee('Pemegang Slip');
        $jawab->assertSee('Teman Sebelah');
        $jawab->assertSee(number_format((int) $b->total_pembayaran, 0, ',', '.'));

        // Nomor pendaftarannya dikirim tanpa email sama sekali — sejak email
        // tidak lagi wajib, inilah satu-satunya pegangan orangnya.
        $this->assertSame('', (string) $b->email);
    }

    #[Test]
    public function slip_hanya_untuk_orang_dalam(): void
    {
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);
        $baris = PendaftaranScopusCamp::first();

        $this->assertNotNull($baris, 'Butuh satu baris untuk diuji.');

        $this->actingAs($pelanggan)
            ->get(route('account.pendaftaran-layanan.slip', ['scopus_camp', $baris->id]))
            ->assertRedirect();
    }

    #[Test]
    public function borang_tetap_terbuka_sesudah_satu_pendaftaran_dibuat(): void
    {
        /*
         * Dulu menjaga daftar "baru saja Anda masukkan" di kolom samping, yang
         * kuerinya tidak ikut mengambil kolom `id` dan membuat SELURUH halaman
         * borang mati dengan "Undefined property: stdClass::$id". Daftar itu
         * sudah dibuang atas permintaan pemilik produk, 4 Okt 2026.
         *
         * Ujinya tetap ada dalam bentuk yang lebih kecil, dan bukan karena
         * sayang membuangnya: cacat seperti itu hanya muncul SESUDAH ada satu
         * pendaftaran, jadi seluruh uji lain — yang membuka borang dalam
         * keadaan kosong — melewatkannya begitu saja.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->get(route('account.pendaftaran-layanan.baru'))->assertOk();

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Pendaftar Sebelum Borang Dibuka',
            'telp' => '0816-0000-0070',
            'lagi' => '1',
        ])->assertRedirect();

        $jawab = $this->actingAs($orang)->get(route('account.pendaftaran-layanan.baru', [
            'layanan' => 'scopus_camp',
            'kategori' => $angkatan->id,
        ]));

        $jawab->assertOk();

        // Layanan dan angkatannya tetap terbawa sepulang "simpan & tambah
        // lagi"; itulah yang membuat mendaftarkan rombongan tidak menuntut
        // memilih ulang keduanya tiap orang.
        $jawab->assertSee('Scopus Camp');

        $this->assertNotNull(
            PendaftaranScopusCamp::where('nama', 'Pendaftar Sebelum Borang Dibuka')->first()
        );
    }

    /*
     * ------------------------------------------------------------------
     * Pesanan lembaga
     * ------------------------------------------------------------------
     *
     * Kuota tiap angkatan 20 kursi sedangkan lembaga rutin memesan lebih —
     * rombongan terbesar yang pernah ada 37 orang. Pesanan sebesar itu
     * terpaksa dipecah ke beberapa angkatan, dan tanpa pengikat ini hasil
     * pecahannya tidak saling tahu bahwa mereka satu pesanan.
     */

    #[Test]
    public function pesanan_lembaga_mengikat_pendaftaran_lintas_angkatan(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $satu = $this->angkatan('scopus_camp', 20, 20);
        $dua = KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Angkatan Lembaga ' . Str::random(5),
            'mulai' => now()->addMonths(2)->toDateString(),
            'total_kuota' => '20', 'sisa_kuota' => '20', 'status' => 'active',
        ]);

        // Pesanan pertama membuat lembaganya sekalian.
        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $satu->id,
            'nama' => 'PIC Lembaga',
            'telp' => '0816-0000-0080',
            'jumlah' => 20,
            'jenis' => 'lembaga',
            'lembaga_nama' => 'Universitas Uji ' . Str::random(4),
            'lembaga_npwp' => '01.234.567.8-901.000',
            'lembaga_po' => 'PO/2026/0099',
        ])->assertRedirect();

        $b1 = PendaftaranScopusCamp::where('nama', 'PIC Lembaga')->first();
        $lembaga = \App\PemesananLembaga::untukPendaftaran('scopus_camp', (string) $b1->id);

        $this->assertNotNull($lembaga, 'Pendaftarannya harus terikat ke pesanan yang baru dibuat.');
        $this->assertStringStartsWith('PL-', $lembaga->kode);
        $this->assertSame('01.234.567.8-901.000', $lembaga->npwp);

        // Sisanya ke angkatan LAIN, diikat ke pesanan yang sama.
        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $dua->id,
            'nama' => 'PIC Lembaga',
            'telp' => '0816-0000-0080',
            'jumlah' => 10,
            'pemesanan_id' => $lembaga->id,
        ])->assertRedirect();

        $this->assertSame(
            2,
            $lembaga->baris()->count(),
            'Kedua pendaftarannya harus terikat ke satu pesanan.'
        );

        $this->assertSame(30, $lembaga->pendaftaran()->sum(fn ($b) => (int) $b->jumlah));
    }

    #[Test]
    public function faktur_menjumlahkan_seluruh_pendaftaran_pesanannya(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $angkatan->forceFill(['biaya' => '1000000', 'total_biaya' => '1000000'])->save();

        $lembaga = \App\PemesananLembaga::create([
            'nama_lembaga' => 'Lembaga Faktur',
            'pic_nama' => 'Bendahara',
            'dibuat_oleh' => 'uji',
        ]);

        foreach ([2, 3] as $ke => $jml) {
            $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
                'layanan' => 'scopus_camp',
                'kategori_id' => $angkatan->id,
                'nama' => 'Peserta Faktur ' . $ke,
                'telp' => '0816-0000-009' . $ke,
                'jumlah' => $jml,
                'pemesanan_id' => $lembaga->id,
                'abaikan_ganda' => '1',
            ])->assertRedirect();
        }

        $jumlah = $lembaga->pendaftaran()->sum(fn ($b) => (int) $b->total);

        $jawab = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.faktur', $lembaga->id));

        $jawab->assertOk();
        $jawab->assertSee($lembaga->kode);
        $jawab->assertSee('Lembaga Faktur');
        $jawab->assertSee(number_format($jumlah, 0, ',', '.'));
        // Lima kursi: 2 + 3.
        $jawab->assertSee('>5<', false);
    }

    #[Test]
    public function diskon_rombongan_berlaku_mulai_ambangnya(): void
    {
        /*
         * Sebelum ini satu-satunya cara memberi harga rombongan adalah
         * potongan khusus berupa rupiah — panitia menghitung sendiri diskon
         * 30 orangnya lalu mengetik hasilnya.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 50, 50);
        $angkatan->forceFill(['biaya' => '1000000', 'total_biaya' => '1000000', 'varian' => null])->save();

        $this->tarif('scopus_camp', null, ['diskon_rombongan_min' => 10, 'diskon_rombongan_persen' => 20]);

        // Sembilan orang: BELUM mencapai ambangnya.
        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp', 'kategori_id' => $angkatan->id,
            'nama' => 'Rombongan Kurang', 'telp' => '0816-0000-0100', 'jumlah' => 9,
        ])->assertRedirect();

        $kurang = PendaftaranScopusCamp::where('nama', 'Rombongan Kurang')->first();
        $this->assertSame(9000000 + (int) $kurang->kode_unik, (int) $kurang->total_pembayaran);

        // Sepuluh orang: tepat di ambangnya.
        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp', 'kategori_id' => $angkatan->id,
            'nama' => 'Rombongan Cukup', 'telp' => '0816-0000-0101', 'jumlah' => 10,
        ])->assertRedirect();

        $cukup = PendaftaranScopusCamp::where('nama', 'Rombongan Cukup')->first();

        // 10 x 1.000.000 dipotong 20% = 8.000.000.
        $this->assertSame(8000000 + (int) $cukup->kode_unik, (int) $cukup->total_pembayaran);
        $this->assertSame(2000000, (int) $cukup->nominal_diskon);
        $this->assertSame('ROMBONGAN', $cukup->kode_diskon);
    }

    #[Test]
    public function yang_berlaku_potongan_yang_paling_menguntungkan(): void
    {
        /*
         * Hanya SATU potongan yang berlaku, dan yang dipakai yang paling
         * besar — dua potongan yang ditumpuk membuat harga akhirnya tidak
         * bisa dijelaskan dari salah satunya.
         *
         * Dulu alumni selalu menang. Aturan itu merugikan sejak potongan
         * alumni dihitung dari SATU kursi: di sini alumni 50% dari satu kursi
         * = 500.000, sedangkan rombongan 20% dari sepuluh kursi = 2.000.000.
         * Pendaftarnya akan diberi yang kecil, dan tidak ada yang menyadari
         * sebab keduanya sama-sama "potongan yang dihitung sistem".
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 50, 50);
        $angkatan->forceFill(['biaya' => '1000000', 'total_biaya' => '1000000', 'varian' => null])->save();

        $this->tarif('scopus_camp', null, [
            'diskon_alumni_persen' => 50,
            'diskon_rombongan_min' => 10,
            'diskon_rombongan_persen' => 20,
        ]);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp', 'kategori_id' => $angkatan->id,
            'nama' => 'Alumni Rombongan', 'telp' => '0816-0000-0102',
            'jumlah' => 10, 'alumni' => '1',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Alumni Rombongan')->first();

        // Rombongan 20% x 10 kursi = 2.000.000 mengalahkan alumni 50% x 1
        // kursi = 500.000.
        $this->assertSame('ROMBONGAN', $b->kode_diskon);
        $this->assertSame(2000000, (int) $b->nominal_diskon);
    }

    #[Test]
    public function pesanan_lembaga_boleh_melebihi_kuota_angkatannya(): void
    {
        /*
         * Kuota 20 kursi sedangkan lembaga rutin memesan lebih — dan memaksa
         * pesanan 30 orang dipecah ke dua angkatan berarti memecah satu
         * rombongan yang seharusnya berangkat bersama, hanya karena angkanya
         * tidak bulat. Keputusan pemilik produk, 4 Okt 2026.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'PIC Lembaga Besar',
            'telp' => '0816-0000-0110',
            'jumlah' => 30,
            'jenis' => 'lembaga',
            'lembaga_nama' => 'Universitas Besar ' . Str::random(4),
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'PIC Lembaga Besar')->first();

        $this->assertNotNull($b, 'Pesanan lembaga 30 orang di kuota 20 harus tersimpan.');
        $this->assertSame(30, (int) $b->jumlah_pendaftar);

        /*
         * Sisa kuotanya MINUS, bukan dijepit ke nol: dijepit, angkatan
         * berkuota 20 yang diisi 30 terbaca "sisa 0" — sama persis dengan yang
         * diisi tepat 20, dan kelebihannya hilang dari layar.
         */
        $this->assertSame(
            -10,
            (int) $angkatan->refresh()->sisa_kuota,
            'Kelebihannya harus terlihat sebagai angka minus.'
        );

        // Jejaknya menyebut kelebihannya; enam bulan kemudian tidak ada yang
        // ingat kenapa angkatan itu berisi 30 orang.
        $this->assertStringContainsString('MELEBIHI kuota angkatan sebanyak 10 kursi', (string) $b->note);
    }

    #[Test]
    public function pendaftar_perorangan_tetap_ditolak_kalau_kursinya_kurang(): void
    {
        /*
         * Kuota itulah yang menjaga kelasnya tidak kebanjiran orang yang
         * mendaftar sendiri-sendiri; kelonggarannya HANYA untuk pesanan
         * lembaga.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Perorangan Rakus',
            'telp' => '0816-0000-0111',
            'jumlah' => 30,
        ])->assertRedirect();

        $this->assertNull(
            PendaftaranScopusCamp::where('nama', 'Perorangan Rakus')->first(),
            'Tanpa pesanan lembaga, kiriman yang melebihi kuota tetap ditolak.'
        );

        $this->assertSame(20, (int) $angkatan->refresh()->sisa_kuota, 'Kuotanya tidak boleh bergeser.');
    }

    #[Test]
    public function angkatan_yang_sudah_kelebihan_tetap_bisa_ditambah_lembaga(): void
    {
        /*
         * Pesanan lembaga sering datang bertahap. Angkatan yang sudah minus
         * harus tetap menerima tambahan dari pesanan yang sama — kalau tidak,
         * pembatasnya pindah dari kuota ke "sudah terlanjur lewat sekali".
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $lembaga = \App\PemesananLembaga::create([
            'nama_lembaga' => 'Lembaga Bertahap',
            'pic_nama' => 'PIC',
            'dibuat_oleh' => 'uji',
        ]);

        foreach ([25, 5] as $ke => $jml) {
            $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
                'layanan' => 'scopus_camp',
                'kategori_id' => $angkatan->id,
                'nama' => 'Tahap ' . $ke,
                'telp' => '0816-0000-012' . $ke,
                'jumlah' => $jml,
                'pemesanan_id' => $lembaga->id,
                'abaikan_ganda' => '1',
            ])->assertRedirect();
        }

        $this->assertSame(-10, (int) $angkatan->refresh()->sisa_kuota, '20 - 25 - 5 = -10.');
        $this->assertSame(2, $lembaga->baris()->count());
    }

    #[Test]
    public function jalur_lembaga_tanpa_nama_ditolak_dengan_keterangan(): void
    {
        /*
         * Tanpa jenisnya, peladen tidak bisa membedakan pesanan lembaga yang
         * namanya lupa diisi dari pendaftar perorangan biasa — dan kirimannya
         * akan tersimpan sebagai pendaftaran perorangan diam-diam, tanpa
         * pesanan lembaga yang sebenarnya dimaksudkan.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Lupa Nama Lembaga',
            'telp' => '0816-0000-0130',
            'jenis' => 'lembaga',
        ])->assertSessionHasErrors('lembaga_nama');

        $this->assertNull(PendaftaranScopusCamp::where('nama', 'Lupa Nama Lembaga')->first());
    }

    #[Test]
    public function jalur_perorangan_tidak_membuat_pesanan_walau_namanya_terbawa(): void
    {
        /*
         * Isian tersembunyi tetap terkirim. Admin yang sempat memilih jalur
         * lembaga lalu kembali ke perorangan membawa serta nama lembaganya —
         * dan tanpa syarat jenisnya, pesanan yang tidak pernah dimaksudkan
         * ikut terbuat.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $sebelum = \App\PemesananLembaga::count();

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Perorangan Saja',
            'telp' => '0816-0000-0131',
            'jenis' => 'perorangan',
            'lembaga_nama' => 'Nama Yang Tertinggal',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Perorangan Saja')->first();

        $this->assertNotNull($b, 'Pendaftarannya tetap tersimpan.');
        $this->assertSame($sebelum, \App\PemesananLembaga::count(), 'Tidak boleh ada pesanan baru.');
        $this->assertNull(\App\PemesananLembaga::untukPendaftaran('scopus_camp', (string) $b->id));
    }

    #[Test]
    public function jalur_perorangan_tidak_menuntut_isian_lembaga(): void
    {
        /*
         * Inti "minim isian": pendaftar perorangan cukup layanan, angkatan,
         * nama, dan nomornya. Sisanya tidak pernah diminta.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Empat Isian Saja',
            'telp' => '0816-0000-0132',
            'jenis' => 'perorangan',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Empat Isian Saja')->first();

        $this->assertNotNull($b);
        $this->assertSame(1, (int) $b->jumlah_pendaftar, 'Satu orang berarti satu kursi.');
        $this->assertSame('', (string) $b->email);
    }

    /**
     * Rentang kode unik SAMA untuk kelima layanan.
     *
     * Dulu masing-masing punya rentangnya sendiri — 1–99, 1–999, 1000–1500,
     * 500–1500 — sehingga nominal transfer yang penanda uniknya bergantung
     * pada layanan mana yang didaftar, dan panitia harus mengingat aturan
     * berbeda untuk tiap layanan saat mencocokkan mutasi rekening.
     */
    #[Test]
    public function rentang_kode_unik_seragam_di_semua_layanan(): void
    {
        foreach (array_keys(Pendaftaran::katalog()) as $layanan) {
            $this->assertSame(
                [500, 1500],
                Pendaftaran::rentangKodeUnik($layanan),
                'Layanan ' . $layanan . ' memakai rentang kode unik yang berbeda.'
            );
        }

        // Layanan yang belum terdaftar pun jatuh ke rentang yang sama, bukan
        // ke angka cadangan tersendiri.
        $this->assertSame([500, 1500], Pendaftaran::rentangKodeUnik('belum_ada'));
    }

    /**
     * Tidak ada angka rentang yang ditulis tangan di luar satu tetapan itu.
     *
     * Terukur sebelum ini: katalog Clinik Scopus menyebut 1000–1500
     * sementara halaman pendaftarannya membuat 500–1500 — dua angka untuk
     * satu hal, dan tidak ada yang tahu mana yang berlaku sampai nominalnya
     * diadu dengan mutasi rekening.
     */
    #[Test]
    public function tidak_ada_rentang_kode_unik_yang_ditulis_tangan(): void
    {
        $akar = dirname(__DIR__, 3);
        $melenceng = [];

        foreach ([
            '/app/Http/Controllers/Publict',
            '/resources/views/public',
        ] as $folder) {
            $jalan = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($akar . $folder));

            foreach ($jalan as $b) {
                if (! $b->isFile() || ! preg_match('/\.(php|blade\.php)$/', $b->getFilename())) {
                    continue;
                }

                $isi = (string) file_get_contents($b->getPathname());

                /*
                 * Yang dicari: pembuat kode unik yang menyebut angkanya
                 * sendiri. Pola Math.random() dengan dua angka, atau
                 * random_int dengan dua angka, di dalam fungsi yang namanya
                 * menyebut kode unik.
                 */
                if (! preg_match('/function\s+generate\w*(?:Unique|Kode)\w*\s*\([^)]*\)\s*\{(.{0,300}?)\}/is', $isi, $m)) {
                    continue;
                }

                if (preg_match('/\d{2,}/', $m[1])) {
                    $melenceng[] = str_replace($akar . '/', '', $b->getPathname());
                }
            }
        }

        $this->assertSame(
            [],
            $melenceng,
            "Berkas ini menulis sendiri rentang kode uniknya:\n- " . implode("\n- ", $melenceng)
                . "\nBacalah dari PendaftaranSemuaLayanan::KODE_UNIK.\n"
        );
    }

    #[Test]
    public function rombongan_perorangan_dengan_satu_alumni_memotong_satu_kursi(): void
    {
        /*
         * Si A mengajak enam temannya dan MEMBAYAR SENDIRI — bukan lembaga.
         * A alumni, teman-temannya tidak.
         *
         * Dua hal yang dulu salah di keadaan ini. Pertama jalurnya tidak ada:
         * isian jumlah orang hanya muncul di jalur lembaga. Kedua, potongan
         * alumninya dihitung dari SELURUH rombongan — pada Scopus Camp
         * Rp 5.500.000 itu Rp 3.850.000, padahal seharusnya Rp 550.000.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $angkatan->forceFill([
            'varian' => null, 'biaya' => '5500000', 'total_biaya' => '5500000',
        ])->save();

        // Alumni 10%, dan TANPA diskon rombongan supaya yang diuji murni
        // perhitungan alumninya.
        $this->tarif('scopus_camp', null, ['diskon_alumni_persen' => 10]);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'jenis' => 'perorangan',
            'nama' => 'Si A Alumni',
            'telp' => '0816-0000-0140',
            'jumlah' => 7,
            'alumni' => '1',
            'peserta' => "Teman Satu\nTeman Dua\nTeman Tiga\nTeman Empat\nTeman Lima\nTeman Enam",
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Si A Alumni')->first();

        $this->assertNotNull($b, 'Rombongan yang dibayar perorangan harus bisa disimpan.');
        $this->assertSame(7, (int) $b->jumlah_pendaftar);

        // 10% dari SATU kursi, bukan dari tujuh.
        $this->assertSame(550000, (int) $b->nominal_diskon);
        $this->assertSame('ALUMNI', $b->kode_diskon);

        // 7 x 5.500.000 = 38.500.000, dipotong 550.000.
        $this->assertSame(37950000 + (int) $b->kode_unik, (int) $b->total_pembayaran);

        // Keenam temannya ikut tercatat untuk daftar hadir.
        $this->assertSame(
            6,
            \App\PendaftaranPeserta::milik('scopus_camp', (string) $b->id)->count()
        );

        // Tidak ada pesanan lembaga yang terbuat; yang membayar perorangan.
        $this->assertNull(\App\PemesananLembaga::untukPendaftaran('scopus_camp', (string) $b->id));
    }

    #[Test]
    public function alumni_sendirian_tetap_memotong_kursinya_sendiri(): void
    {
        /*
         * Penjaga supaya perbaikan di atas tidak diam-diam mengubah keadaan
         * yang paling lazim: alumni yang mendaftar sendirian. Satu kursi dan
         * seluruh subtotal kebetulan sama besar di sini, dan itu memang
         * harapannya.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $angkatan->forceFill([
            'varian' => null, 'biaya' => '5500000', 'total_biaya' => '5500000',
        ])->save();

        $this->tarif('scopus_camp', null, ['diskon_alumni_persen' => 10]);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'jenis' => 'perorangan',
            'nama' => 'Alumni Sendirian',
            'telp' => '0816-0000-0141',
            'alumni' => '1',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Alumni Sendirian')->first();

        $this->assertSame(550000, (int) $b->nominal_diskon);
        $this->assertSame(4950000 + (int) $b->kode_unik, (int) $b->total_pembayaran);
    }

    // ------------------------------------------------------------- pembantu

    private function akun(string $peran): User
    {
        $u = User::create([
            'full_name' => 'Uji ' . $peran,
            'username' => 'uji_tp_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $u->forceFill([
            'status' => 'active',
            'email_verified_at' => now(),
            'peran' => $peran,
        ])->save();

        return $u->refresh();
    }

    /**
     * Satu tarif AKTIF untuk layanan/varian tertentu, dengan potongan
     * alumninya.
     *
     * Tarif aktif yang sudah ada untuk kombinasi itu dinonaktifkan dulu:
     * pencariannya mengambil yang pertama ketemu, jadi dua baris aktif
     * membuat ujinya bergantung pada urutan yang tidak dijamin.
     */
    /**
     * @param  int|array<string, int|null>|null  $persenAlumni  angka = potongan
     *   alumni saja; larik = kolom potongan apa adanya (alumni, rombongan).
     */
    private function tarif(string $layanan, ?string $varian, $persenAlumni): \App\ClinikScopusBiayaPersesi
    {
        \App\ClinikScopusBiayaPersesi::where('layanan', $layanan)
            ->where('status', \App\ClinikScopusBiayaPersesi::AKTIF)
            ->when($varian === null, fn ($q) => $q->whereNull('varian'), fn ($q) => $q->where('varian', $varian))
            ->update(['status' => \App\ClinikScopusBiayaPersesi::NONAKTIF]);

        return \App\ClinikScopusBiayaPersesi::create([
            'layanan' => $layanan,
            'varian' => $varian,
            'biaya_persesi' => '1000000',
            'status' => \App\ClinikScopusBiayaPersesi::AKTIF,
        ] + (is_array($persenAlumni)
            ? $persenAlumni
            : ['diskon_alumni_persen' => $persenAlumni]));
    }

    /** Satu angkatan baru milik layanan tertentu, dengan kuota yang diketahui. */
    private function angkatan(string $layanan, int $total = 50, ?int $sisa = null): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => $layanan,
            'nama' => 'Angkatan Uji ' . Str::random(6),
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => (string) $total,
            'sisa_kuota' => (string) ($sisa ?? $total),
            'status' => 'active',
        ]);
    }

    private function sesiClinik(): string
    {
        $id = DB::table('clinikscopus')->value('id');
        $this->assertNotNull($id, 'Basis data uji tidak punya sesi Clinik Scopus.');

        return (string) $id;
    }

    /** Satu baris contoh per layanan, beserta angkatannya kalau ada. */
    private function buat(string $layanan, array $tambahan = []): array
    {
        $tanda = Str::random(8);

        if (in_array($layanan, ['scopus_camp', 'bibliometrik'], true)) {
            $angkatan = $this->angkatan($layanan);
            $kelas = $layanan === 'scopus_camp' ? PendaftaranScopusCamp::class : AnalisisBibliometrik::class;

            return [$kelas::create(array_merge([
                'id_transaksi' => 'T-' . $tanda,
                'kategori_id' => $angkatan->id,
                'nama' => 'Peserta ' . $tanda,
                'email' => $tanda . '@contoh.test',
                'telp' => '0811-0000-0001',
                'affiliasi' => 'Instansi ' . $tanda,
                'jumlah_pendaftar' => '2',
                'total_pembayaran' => '1000000',
                'status' => 'diproses',
            ], $tambahan)), $angkatan];
        }

        if ($layanan === 'webinar_eksklusif') {
            $angkatan = $this->angkatan($layanan);

            return [WebinarEksklusifPendaftaran::create(array_merge([
                'id_transaksi' => 'WE-' . $tanda,
                'kategori_id' => $angkatan->id,
                'nama' => 'Peserta ' . $tanda,
                'email' => $tanda . '@contoh.test',
                'telp' => '62811000002',
                'jumlah_pendaftar' => 3,
                'total_pembayaran' => '400000',
                'status' => 'pending',
            ], $tambahan)), $angkatan];
        }

        if ($layanan === 'scopus_kafe') {
            return [PendaftaranScopusKafe::create(array_merge([
                'id_pemesanan' => 'K-' . substr($tanda, 0, 6),
                'nama' => 'Pemesan ' . $tanda,
                'email' => $tanda . '@contoh.test',
                'telp' => '0811-0000-0003',
                'sesi' => 'sesi 1',
                'total_keseluruhan_pembayaran' => '250000',
                'status' => 'menunggu verifikasi',
            ], $tambahan)), null];
        }

        $orang = $this->akun(User::PERAN_KARYAWAN);

        return [ClinikScopusPemesanan::create(array_merge([
            'clinikscopus_id' => $this->sesiClinik(),
            'trainer_id' => $orang->id,
            'customer_id' => $orang->id,
            'id_transaksi' => 'C-' . $tanda,
            'kode_booking' => 'BOOK-' . $tanda,
            'nama_pemesan' => 'Pemesan ' . $tanda,
            'email_pemesan' => $tanda . '@contoh.test',
            'telp_pemesan' => '0811-0000-0004',
            'sesi' => 'Sesi 1',
            'jam_sesi' => '09.00 - 10.00 WIB',
            'total_pembayaran' => 99000,
            'status' => 'pending',
        ], $tambahan)), null];
    }

    // -------------------------------------------------------------- rincian

    #[Test]
    public function rincian_terbuka_untuk_kelima_layanan(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        foreach (array_keys(Pendaftaran::katalog()) as $layanan) {
            [$baris] = $this->buat($layanan);

            $halaman = $this->actingAs($orang)
                ->get(route('account.pendaftaran-layanan.rincian', [$layanan, $baris->getKey()]));

            $halaman->assertOk();
            $halaman->assertSee('Rincian Pendaftaran');
            // Nama orangnya memang tampil, jadi halamannya benar-benar
            // membaca barisnya dan bukan cuma kerangkanya.
            $halaman->assertSee($baris->nama ?? $baris->nama_pemesan);

            $this->flushSession();
        }
    }

    #[Test]
    public function layanan_yang_tidak_dikenali_dan_id_asing_jadi_404(): void
    {
        /*
         * {layanan} datang dari alamat halaman dan menentukan MODEL mana yang
         * dipanggil. Tanpa pencocokan ke katalog tertutup, nilai mentah dari
         * alamat bisa menunjuk kelas apa pun.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['layanan-karangan', 'abc']))
            ->assertNotFound();

        $this->flushSession();

        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', (string) Str::uuid()]))
            ->assertNotFound();
    }

    #[Test]
    public function pelanggan_tidak_boleh_menyentuh_satu_pun_tindakannya(): void
    {
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);
        [$baris, $angkatan] = $this->buat('scopus_camp');
        $kunci = [$baris->getKey()];
        $tujuan = route('account.dashboard.index');

        $this->actingAs($pelanggan)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', ...$kunci]))
            ->assertRedirect($tujuan);
        $this->flushSession();

        $this->actingAs($pelanggan)
            ->put(route('account.pendaftaran-layanan.ubah', ['scopus_camp', ...$kunci]), [
                'nama' => 'Diubah Paksa', 'email' => 'x@contoh.test', 'telp' => '0811',
                'kategori_id' => $angkatan->id, 'jumlah_pendaftar' => 1,
            ])
            ->assertRedirect($tujuan);
        $this->flushSession();

        $this->actingAs($pelanggan)
            ->post(route('account.pendaftaran-layanan.status', ['scopus_camp', ...$kunci]), [
                'status' => 'Pendaftaran Diterima',
            ])
            ->assertRedirect($tujuan);
        $this->flushSession();

        $this->actingAs($pelanggan)
            ->delete(route('account.pendaftaran-layanan.hapus', ['scopus_camp', ...$kunci]))
            ->assertRedirect($tujuan);

        // Dan datanya memang tidak tersentuh.
        $baris->refresh();
        $this->assertSame('diproses', $baris->status);
        $this->assertStringStartsWith('Peserta ', $baris->nama);
    }

    // ---------------------------------------------------------- ubah status

    #[Test]
    public function status_diterima_mengirim_email_ke_pendaftarnya(): void
    {
        /*
         * Inilah yang paling mudah hilang saat menyatukan layar: mengubah
         * status di layar lama MENGIRIM EMAIL. Dihilangkan, pelanggan berhenti
         * diberi tahu dan tidak ada galat apa pun yang memberitahukannya.
         */
        Mail::fake();

        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp] = $this->buat('scopus_camp');

        $this->actingAs($orang)
            ->post(route('account.pendaftaran-layanan.status', ['scopus_camp', $camp->getKey()]), [
                'status' => 'Pendaftaran Diterima',
            ])
            ->assertRedirect();

        Mail::assertSent(ScopusCampUpdateDiterimaMail::class, 1);
        $this->assertSame('Pendaftaran Diterima', $camp->refresh()->status);
    }

    #[Test]
    public function tiap_layanan_mengirim_surat_yang_memang_miliknya(): void
    {
        Mail::fake();

        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $harapan = [
            ['scopus_camp', 'Pendaftaran Reschedule', ScopusCampUpdateResheduleMail::class],
            ['bibliometrik', 'Pendaftaran Diterima', AnalisisBibliometrikUpdateDiterimaMail::class],
            ['scopus_kafe', 'pembayaran diterima', UpdatePublicPendaftaranScopusKafeMail::class],
        ];

        foreach ($harapan as [$layanan, $status, $surat]) {
            [$baris] = $this->buat($layanan);

            $this->actingAs($orang)
                ->post(route('account.pendaftaran-layanan.status', [$layanan, $baris->getKey()]), [
                    'status' => $status,
                ])
                ->assertRedirect();

            Mail::assertSent($surat, 1);

            $this->flushSession();
        }
    }

    #[Test]
    public function webinar_dan_clinik_tidak_mengirim_surat_apa_pun(): void
    {
        // Bukan kelalaian: pemberitahuan lunas webinar sudah dikirim jalur
        // pendaftarannya sendiri, dan Clinik Scopus memang tidak pernah
        // mengirim surat dari layar panitia.
        Mail::fake();

        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        foreach ([['webinar_eksklusif', 'paid'], ['clinik_scopus', 'completed']] as [$layanan, $status]) {
            [$baris] = $this->buat($layanan);

            $this->actingAs($orang)
                ->post(route('account.pendaftaran-layanan.status', [$layanan, $baris->getKey()]), [
                    'status' => $status,
                ])
                ->assertRedirect();

            $this->assertSame($status, $baris->refresh()->status);
            $this->flushSession();
        }

        Mail::assertNothingSent();
    }

    #[Test]
    public function status_yang_tidak_berlaku_untuk_layanannya_ditolak(): void
    {
        // Kosakata statusnya berbeda di tiap layanan; 'pembayaran diterima'
        // milik Scopus Kafe tidak berarti apa pun di Scopus Camp, dan
        // menyimpannya membuat barisnya jatuh ke keadaan yang salah.
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp] = $this->buat('scopus_camp');

        $this->actingAs($orang)
            ->post(route('account.pendaftaran-layanan.status', ['scopus_camp', $camp->getKey()]), [
                'status' => 'pembayaran diterima',
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame('diproses', $camp->refresh()->status);
    }

    #[Test]
    public function kursi_webinar_dikembalikan_lalu_diambil_lagi(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$webinar, $angkatan] = $this->buat('webinar_eksklusif');

        // Kursinya sudah dipotong saat mendaftar; di uji ini barisnya dibuat
        // langsung, jadi sisanya disetel dulu seperti sesudah pendaftaran.
        $angkatan->forceFill(['sisa_kuota' => (string) (50 - 3)])->save();

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.status', ['webinar_eksklusif', $webinar->getKey()]),
            ['status' => 'cancel']
        )->assertRedirect();

        $this->assertSame(50, (int) $angkatan->refresh()->sisa_kuota,
            'Dibatalkan, ketiga kursinya harus kembali.');

        $this->flushSession();

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.status', ['webinar_eksklusif', $webinar->getKey()]),
            ['status' => 'paid']
        )->assertRedirect();

        $this->assertSame(47, (int) $angkatan->refresh()->sisa_kuota,
            'Diaktifkan kembali, ketiga kursinya harus diambil lagi.');
    }

    #[Test]
    public function status_scopus_camp_tidak_menggeser_kuota(): void
    {
        /*
         * Disengaja, dan dijaga supaya tidak "diperbaiki" tanpa sadar:
         * keempat layanan selain Webinar memang tidak pernah memindahkan
         * kuota saat statusnya berubah. Menyeragamkannya akan menggeser angka
         * sisa_kuota pada 48 angkatan yang sudah ada — perubahan yang tidak
         * diminta dan tidak bisa dibedakan dari kekeliruan nanti. Kuotanya
         * tetap berpindah saat barisnya DIHAPUS.
         */
        Mail::fake();

        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp, $angkatan] = $this->buat('scopus_camp');
        $sisaAwal = (int) $angkatan->sisa_kuota;

        $this->actingAs($orang)->post(
            route('account.pendaftaran-layanan.status', ['scopus_camp', $camp->getKey()]),
            ['status' => 'Pendaftaran Dibatalkan']
        )->assertRedirect();

        $this->assertSame($sisaAwal, (int) $angkatan->refresh()->sisa_kuota);
    }

    // ------------------------------------------------------------ ubah data

    #[Test]
    public function data_diri_bisa_dibetulkan(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp, $angkatan] = $this->buat('scopus_camp');

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.ubah', ['scopus_camp', $camp->getKey()]),
            [
                'nama' => 'Nama Yang Sudah Dibetulkan',
                'email' => 'betul@contoh.test',
                'telp' => '0812-3456-7890',
                'affiliasi' => 'Universitas Contoh',
                'kategori_id' => $angkatan->id,
                'jumlah_pendaftar' => 2,
                'note' => 'Dibetulkan lewat uji.',
            ]
        )->assertRedirect(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]));

        $camp->refresh();
        $this->assertSame('Nama Yang Sudah Dibetulkan', $camp->nama);
        $this->assertSame('betul@contoh.test', $camp->email);
        $this->assertSame('Universitas Contoh', $camp->affiliasi);
    }

    #[Test]
    public function status_tidak_ikut_berubah_saat_data_disunting(): void
    {
        // Memindahkan status mengirim email dan menggeser kuota; keduanya
        // tidak boleh terjadi hanya karena seseorang membetulkan ejaan nama.
        Mail::fake();

        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp, $angkatan] = $this->buat('scopus_camp');

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.ubah', ['scopus_camp', $camp->getKey()]),
            [
                'nama' => 'Ejaan Dibetulkan',
                'email' => $camp->email,
                'telp' => $camp->telp,
                'kategori_id' => $angkatan->id,
                'jumlah_pendaftar' => 2,
                // Dikirim sengaja, dan HARUS diabaikan.
                'status' => 'Pendaftaran Diterima',
            ]
        )->assertRedirect();

        $this->assertSame('diproses', $camp->refresh()->status);
        Mail::assertNothingSent();
    }

    #[Test]
    public function email_yang_bentuknya_tidak_sah_ditolak(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp, $angkatan] = $this->buat('scopus_camp');
        $emailAsli = $camp->email;

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.ubah', ['scopus_camp', $camp->getKey()]),
            [
                'nama' => 'Tetap', 'email' => 'bukan-email', 'telp' => '0811',
                'kategori_id' => $angkatan->id, 'jumlah_pendaftar' => 1,
            ]
        )->assertSessionHasErrors('email');

        $this->assertSame($emailAsli, $camp->refresh()->email);
    }

    #[Test]
    public function pindah_angkatan_mengembalikan_kuota_lama_dan_memotong_yang_baru(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp, $lama] = $this->buat('scopus_camp');
        $baru = $this->angkatan('scopus_camp', 30, 30);

        // Seperti sesudah pendaftaran: dua kursi sudah terpakai di angkatan lama.
        $lama->forceFill(['sisa_kuota' => (string) (50 - 2)])->save();

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.ubah', ['scopus_camp', $camp->getKey()]),
            [
                'nama' => $camp->nama, 'email' => $camp->email, 'telp' => $camp->telp,
                'kategori_id' => $baru->id, 'jumlah_pendaftar' => 2,
            ]
        )->assertRedirect();

        $this->assertSame($baru->id, $camp->refresh()->kategori_id);
        $this->assertSame(50, (int) $lama->refresh()->sisa_kuota, 'Kursi di angkatan lama harus kembali.');
        $this->assertSame(28, (int) $baru->refresh()->sisa_kuota, 'Kursi di angkatan baru harus terpotong.');
    }

    #[Test]
    public function jumlah_yang_melebihi_sisa_kuota_ditolak(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp, $angkatan] = $this->buat('scopus_camp');

        // Sisa tiga kursi, dan dua di antaranya sudah dipakai baris ini.
        $angkatan->forceFill(['total_kuota' => '50', 'sisa_kuota' => '3'])->save();

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.ubah', ['scopus_camp', $camp->getKey()]),
            [
                'nama' => $camp->nama, 'email' => $camp->email, 'telp' => $camp->telp,
                'kategori_id' => $angkatan->id, 'jumlah_pendaftar' => 9,
            ]
        );

        $this->assertSame(2, (int) $camp->refresh()->jumlah_pendaftar,
            'Jumlahnya tidak boleh tersimpan kalau melebihi sisa kuota.');
        $this->assertSame(3, (int) $angkatan->refresh()->sisa_kuota,
            'Kuotanya tidak boleh bergeser saat kirimannya ditolak.');
    }

    #[Test]
    public function jumlah_sama_dengan_sisa_plus_miliknya_sendiri_tetap_diterima(): void
    {
        /*
         * Jebakan yang mudah salah: sisa kuota angkatan BARU harus dihitung
         * dengan menambahkan kembali jumlah pendaftar lama bila angkatannya
         * tidak berpindah. Tanpa itu, menyunting pendaftaran berisi 2 orang
         * tanpa mengubah jumlahnya pun akan ditolak, sebab 2 dianggap
         * tambahan baru di atas sisa yang sudah dikurangi 2.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp, $angkatan] = $this->buat('scopus_camp');

        $angkatan->forceFill(['total_kuota' => '50', 'sisa_kuota' => '1'])->save();

        $this->actingAs($orang)->put(
            route('account.pendaftaran-layanan.ubah', ['scopus_camp', $camp->getKey()]),
            [
                'nama' => $camp->nama, 'email' => $camp->email, 'telp' => $camp->telp,
                // 1 sisa + 2 miliknya sendiri = 3 kursi yang boleh dipakai.
                'kategori_id' => $angkatan->id, 'jumlah_pendaftar' => 3,
            ]
        )->assertRedirect();

        $this->assertSame(3, (int) $camp->refresh()->jumlah_pendaftar);
        $this->assertSame(0, (int) $angkatan->refresh()->sisa_kuota);
    }

    #[Test]
    public function nominal_diterima_dengan_pemisah_ribuan_apa_pun(): void
    {
        /*
         * Pengendali lama membuang TITIK untuk Scopus Camp dan KOMA untuk
         * Scopus Kafe. Jadi "4.275.028" yang diketik di layar Kafe dulu
         * tersimpan sebagai nol — tanpa galat, dan total bayarnya hilang.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp, $angkatan] = $this->buat('scopus_camp');

        foreach (['4.275.028', '4,275,028', '4 275 028', 'Rp 4.275.028'] as $ditulis) {
            $this->actingAs($orang)->put(
                route('account.pendaftaran-layanan.ubah', ['scopus_camp', $camp->getKey()]),
                [
                    'nama' => $camp->nama, 'email' => $camp->email, 'telp' => $camp->telp,
                    'kategori_id' => $angkatan->id, 'jumlah_pendaftar' => 2,
                    'total_pembayaran' => $ditulis,
                ]
            )->assertRedirect();

            $this->assertSame(4275028, (int) $camp->refresh()->total_pembayaran,
                'Nominal "' . $ditulis . '" tidak terbaca benar.');

            $this->flushSession();
        }
    }

    // ---------------------------------------------------------------- hapus

    #[Test]
    public function hanya_administrator_yang_boleh_menghapus(): void
    {
        /*
         * Aturan terketat di antara kelima layar lama, disengaja dipakai
         * untuk semuanya: penghapusannya tidak bisa diurungkan dan belum ada
         * tong sampah. Tiga layar lama tidak menuntut apa pun.
         */
        $karyawan = $this->akun(User::PERAN_KARYAWAN);
        [$camp] = $this->buat('scopus_camp');

        $this->actingAs($karyawan)
            ->delete(route('account.pendaftaran-layanan.hapus', ['scopus_camp', $camp->getKey()]))
            ->assertRedirect();

        $this->assertNotNull(PendaftaranScopusCamp::find($camp->getKey()),
            'Karyawan tidak boleh berhasil menghapus.');

        $this->flushSession();

        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)
            ->delete(route('account.pendaftaran-layanan.hapus', ['scopus_camp', $camp->getKey()]))
            ->assertRedirect(route('account.pendaftaran-layanan.index'));

        $this->assertNull(PendaftaranScopusCamp::find($camp->getKey()));
    }

    #[Test]
    public function hapus_mengembalikan_kuota_dan_membuang_berkas_buktinya(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        // Berkas bukti palsu, bernama khas supaya tidak mungkin bertabrakan
        // dengan bukti sungguhan milik pendaftar.
        $nama = 'uji-hapus-' . Str::random(10) . '.jpg';
        $berkas = public_path('ScopusCamp/' . $nama);
        $this->berkasUji[] = $berkas;

        @mkdir(dirname($berkas), 0775, true);
        file_put_contents($berkas, 'bukan gambar sungguhan');
        $this->assertFileExists($berkas);

        [$camp, $angkatan] = $this->buat('scopus_camp', ['gambar' => 'ScopusCamp/' . $nama]);
        $angkatan->forceFill(['sisa_kuota' => (string) (50 - 2)])->save();

        $this->actingAs($admin)
            ->delete(route('account.pendaftaran-layanan.hapus', ['scopus_camp', $camp->getKey()]))
            ->assertRedirect();

        $this->assertNull(PendaftaranScopusCamp::find($camp->getKey()));
        $this->assertSame(50, (int) $angkatan->refresh()->sisa_kuota, 'Kuotanya harus kembali.');
        $this->assertFileDoesNotExist($berkas, 'Berkas buktinya harus ikut terbuang.');
    }

    #[Test]
    public function hapus_pemesanan_clinik_ikut_membuang_testimoninya(): void
    {
        /*
         * Testimoni menunjuk pemesanannya TANPA kunci asing, jadi basis
         * datanya tidak akan menolak maupun membersihkannya sendiri — ia akan
         * tinggal sebagai baris yatim, dan halaman publik galat saat merujuk
         * pemesanan yang sudah tidak ada.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        [$clinik] = $this->buat('clinik_scopus');

        // Empat kolom NOT NULL tanpa nilai bawaan; barisnya tidak bisa
        // dibuat tanpa keempatnya.
        $testimoni = ClinikScopusTestimoni::create([
            'clinikscopus_id' => $clinik->clinikscopus_id,
            'clinikscopus_pemesanan_id' => $clinik->getKey(),
            'trainer_id' => $clinik->trainer_id,
            'customer_id' => $clinik->customer_id,
            'rating' => 5,
            'deskripsi' => 'Testimoni uji ' . Str::random(6),
        ]);

        $this->assertNotNull(ClinikScopusTestimoni::find($testimoni->getKey()));

        $this->actingAs($admin)
            ->delete(route('account.pendaftaran-layanan.hapus', ['clinik_scopus', $clinik->getKey()]))
            ->assertRedirect();

        $this->assertNull(ClinikScopusPemesanan::find($clinik->getKey()));
        $this->assertNull(ClinikScopusTestimoni::find($testimoni->getKey()),
            'Testimoninya harus ikut terhapus, bukan tinggal yatim.');
    }

    // ----------------------------------------------------------- angkatan

    #[Test]
    public function nama_angkatan_tidak_mengulang_nama_layanannya(): void
    {
        /*
         * Terukur: 56 dari 59 angkatan namanya memuat nama layanannya
         * sendiri, jadi tiap baris daftar menulis hal yang sama dua kali —
         * kolom Layanan berbunyi "Scopus Camp" dan kolom Sesi "Scopus Camp
         * Jakarta". Yang ingin dibaca cuma satu kata.
         */
        $this->assertSame('Jakarta', Pendaftaran::namaRingkas('scopus_camp', 'Scopus Camp Jakarta'));
        $this->assertSame('Batch 2', Pendaftaran::namaRingkas('webinar_eksklusif', 'Webinar Eksklusif - Batch 2'));

        // Kalau sesudah dipangkas tidak tersisa apa-apa, nama penuhnya
        // dikembalikan: sel kosong lebih buruk daripada sel yang mengulang.
        $this->assertSame('Analisis Bibliometrik',
            Pendaftaran::namaRingkas('bibliometrik', 'Analisis Bibliometrik'));

        // Dipangkas dari DEPAN saja; kata yang berada di tengah bukan awalan
        // yang mubazir.
        $this->assertSame('Kelas Scopus Camp lanjutan',
            Pendaftaran::namaRingkas('scopus_camp', 'Kelas Scopus Camp lanjutan'));
    }

    #[Test]
    public function daftar_memajang_nama_ringkas_dan_menyimpan_yang_penuh(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Scopus Camp Kota ' . $tanda,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'status' => 'active',
        ]);

        PendaftaranScopusCamp::create([
            'id_transaksi' => 'RINGKAS-' . $tanda,
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Ringkas ' . $tanda,
            'email' => $tanda . '@contoh.test',
            'telp' => '0811-0000-0021',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '10000',
            'status' => 'diproses',
        ]);

        Pendaftaran::lupakan();

        $halaman = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['cari' => $tanda]));

        $halaman->assertOk();
        // Yang terbaca di kolomnya nama ringkasnya...
        $halaman->assertSee('Kota ' . $tanda);
        // ...dan nama penuhnya tetap ada, di atribut title.
        $halaman->assertSee('title="Scopus Camp Kota ' . $tanda . '"', false);
    }

    #[Test]
    public function angkatan_yang_kursinya_habis_ditandai_penuh(): void
    {
        /*
         * Tanpa penanda ini, daftar pendaftar tidak memberi tahu apakah
         * angkatannya masih bisa menerima orang — dan itu baru ketahuan saat
         * pendaftaran berikutnya ditolak.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Scopus Camp Penuh ' . $tanda,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '10',
            'sisa_kuota' => '0',
            'status' => 'active',
        ]);

        PendaftaranScopusCamp::create([
            'id_transaksi' => 'PENUH-' . $tanda,
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Penuh ' . $tanda,
            'email' => 'p' . $tanda . '@contoh.test',
            'telp' => '0811-0000-0022',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '10000',
            'status' => 'diproses',
        ]);

        Pendaftaran::lupakan();

        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['cari' => $tanda]))
            ->assertOk()
            ->assertSee('pdl-penuh', false)
            ->assertSee('penuh');
    }

    #[Test]
    public function nomor_angkatan_tampil_di_daftar_dan_ikut_kedua_berkas(): void
    {
        /*
         * "Batch ke berapa" — yang dipakai admin merekap.
         *
         * Nama tempat saja TIDAK menunjuk satu angkatan: terukur, Scopus Camp
         * Yogyakarta sudah angkatan ke-202 sementara Jakarta baru ke-9. Rekap
         * yang menyebut "Scopus Camp Yogyakarta" tanpa nomornya menggabungkan
         * dua ratus angkatan jadi satu baris.
         *
         * Diperiksa di KETIGA tempat sekaligus — layar, PDF, dan lembar
         * kerja — sebab yang diunduh untuk direkap justru dua yang terakhir,
         * dan nomor yang hanya ada di layar tidak menolong siapa pun.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Scopus Camp Kota ' . $tanda,
            'nama_ke' => '202',
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'status' => 'active',
        ]);

        $p = PendaftaranScopusCamp::create([
            'id_transaksi' => 'NOMOR-' . $tanda,
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Nomor ' . $tanda,
            'email' => $tanda . '@contoh.test',
            'telp' => '0811-0000-0031',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '10000',
            'status' => 'diproses',
        ]);

        Pendaftaran::lupakan();

        /*
         * 1. Di layar daftar — DENGAN KATA, bukan "#202".
         *
         * Pernyataan lama uji ini menuntut keduanya muncul: "#202" sebagai
         * teks kepingnya, "Angkatan ke-202" sebagai title-nya. Tanda pagarnya
         * sengaja dibuang: artinya hanya terbaca kalau kursornya ditahan di
         * atas kepingnya, dan itu tidak dilakukan orang yang sedang mencari
         * satu nama.
         */
        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['cari' => $tanda]))
            ->assertOk()
            ->assertSee('Angkatan ke-202')
            ->getContent();

        $this->assertStringNotContainsString(
            '>#202<',
            $isi,
            'Nomor angkatan tidak boleh lagi muncul sebagai tanda pagar saja.'
        );


        $baris = Pendaftaran::kueri()->where('id', $p->getKey())->first();

        // 2. Di PDF — lewat sebutan yang dipakai templatnya.
        $this->assertSame(
            'Scopus Camp Kota ' . $tanda . ' — angkatan ke-202',
            Pendaftaran::sesiUntukBerkas($baris)
        );

        // 3. Di lembar kerja, sebagai KOLOM TERSENDIRI supaya bisa dipakai
        //    mengelompokkan — nomor yang menempel di dalam untaian nama tidak
        //    bisa.
        $ekspor = new \App\Exports\PendaftaranLayananExport(
            collect([$baris]), Pendaftaran::katalog(), []
        );

        $kepala = $ekspor->headings();
        $isi = $ekspor->array()[0];

        $kolom = array_search('Angkatan ke-', $kepala, true);

        $this->assertNotFalse($kolom, 'Lembar kerja harus punya kolom nomor angkatan.');
        $this->assertSame('202', $isi[$kolom]);
        $this->assertSame(count($kepala), count($isi), 'Jumlah kolom isi dan kepalanya harus sama.');
    }

    #[Test]
    public function pencarian_menemukan_lewat_nama_dan_nomor_angkatan(): void
    {
        /*
         * Orang mencari lewat apa yang mereka ingat, dan untuk merekap yang
         * diingat biasanya "Yogyakarta" atau "202" — bukan nomor pendaftaran
         * seseorang. Sebelum ini keduanya mengembalikan nol hasil.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Scopus Camp Palu' . $tanda,
            'nama_ke' => '777',
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'status' => 'active',
        ]);

        PendaftaranScopusCamp::create([
            'id_transaksi' => 'CARIANGKATAN-' . $tanda,
            'kategori_id' => $angkatan->id,
            // Namanya sengaja TIDAK memuat kata yang dicari, supaya yang
            // terbukti memang pencocokan lewat angkatannya.
            'nama' => 'Orang Biasa Saja',
            'email' => $tanda . '@contoh.test',
            'telp' => '0811-0000-0033',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '10000',
            'status' => 'diproses',
        ]);

        Pendaftaran::lupakan();

        foreach (['Palu' . $tanda, '777'] as $kataKunci) {
            $this->actingAs($orang)
                ->get(route('account.pendaftaran-layanan.index', ['cari' => $kataKunci]))
                ->assertOk()
                ->assertSee('CARIANGKATAN-' . $tanda);

            $this->flushSession();
        }
    }

    #[Test]
    public function nomor_angkatan_dicocokkan_persis_bukan_sebagian(): void
    {
        /*
         * Dengan LIKE, mencari "7" akan menarik angkatan ke-7, ke-70, ke-77,
         * dan ke-777 sekaligus — dan rekap yang mengira dirinya satu angkatan
         * sebenarnya memuat empat.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        /*
         * Tandanya HURUF SAJA, bukan Str::random().
         *
         * Str::random() memakai huruf DAN angka, jadi tandanya bisa memuat
         * "7" — dan tanda itu ikut ke id_transaksi serta emailnya, sehingga
         * pencarian "7" menemukan barisnya sendiri dan ujinya merah tanpa ada
         * yang salah pada kodenya. Terukur 13,2% dari 2.000 percobaan, yang
         * cocok dengan "merah sekali, lalu hijau beberapa putaran".
         */
        $tanda = collect(range('A', 'Z'))->shuffle()->take(8)->implode('');

        $a777 = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Scopus Camp Tujuh' . $tanda,
            'nama_ke' => '777', 'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '20', 'sisa_kuota' => '20', 'status' => 'active',
        ]);

        PendaftaranScopusCamp::create([
            'id_transaksi' => 'TIGATUJUH-' . $tanda,
            'kategori_id' => $a777->id,
            'nama' => 'Peserta Tujuh Ratus', 'email' => 't' . $tanda . '@contoh.test',
            'telp' => '0811-0000-0034', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => '10000', 'status' => 'diproses',
        ]);

        Pendaftaran::lupakan();

        // '7' TIDAK boleh menarik angkatan ke-777.
        $halaman = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['cari' => '7', 'layanan' => 'scopus_camp']));

        $halaman->assertOk();
        $halaman->assertDontSee('TIGATUJUH-' . $tanda);
    }

    #[Test]
    public function angkatan_tanpa_nomor_tidak_menampilkan_apa_apa(): void
    {
        // Kolom nama_ke boleh kosong; yang tidak boleh adalah layar atau
        // berkas yang menulis "angkatan ke-" lalu berhenti.
        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Scopus Camp Tanpa Nomor ' . Str::random(5),
            'nama_ke' => null,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'status' => 'active',
        ]);

        $p = PendaftaranScopusCamp::create([
            'id_transaksi' => 'TANPA-' . Str::random(6),
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Tanpa Nomor',
            'email' => Str::random(6) . '@contoh.test',
            'telp' => '0811-0000-0032',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '10000',
            'status' => 'diproses',
        ]);

        Pendaftaran::lupakan();

        $baris = Pendaftaran::kueri()->where('id', $p->getKey())->first();

        $this->assertNull(Pendaftaran::nomorAngkatanBaris($baris));
        $this->assertStringNotContainsString('angkatan ke-', (string) Pendaftaran::sesiUntukBerkas($baris));
        $this->assertSame($angkatan->nama, Pendaftaran::sesiUntukBerkas($baris));
    }

    #[Test]
    public function menu_angkatan_hanya_memuat_yang_punya_pendaftar(): void
    {
        /*
         * Menawarkan angkatan kosong berarti menyediakan pilihan yang pasti
         * mengembalikan nol baris, dan orang yang menekannya menyimpulkan
         * saringannya rusak. Terukur 39 dari 59 angkatan yang punya pendaftar.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);

        $kosong = KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Scopus Camp Sepi ' . $tanda,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'status' => 'active',
        ]);

        Pendaftaran::lupakan();

        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index'))
            ->assertOk()
            // Angkatan tanpa satu pun pendaftar tidak ditawarkan.
            ->assertDontSee('value="' . $kosong->id . '"', false);
    }

    #[Test]
    public function menu_angkatan_benar_benar_menyaring(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);

        $satu = $this->angkatan('scopus_camp');
        $dua = $this->angkatan('scopus_camp');

        foreach ([['DIANGKATAN', $satu], ['DILUAR', $dua]] as [$nama, $a]) {
            PendaftaranScopusCamp::create([
                'id_transaksi' => $nama . '-' . $tanda,
                'kategori_id' => $a->id,
                'nama' => $nama . ' ' . $tanda,
                'email' => strtolower($nama) . $tanda . '@contoh.test',
                'telp' => '0811-0000-0023',
                'jumlah_pendaftar' => '1',
                'total_pembayaran' => '10000',
                'status' => 'diproses',
            ]);
        }

        Pendaftaran::lupakan();

        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['cari' => $tanda, 'angkatan' => $satu->id]))
            ->assertOk()
            ->assertSee('DIANGKATAN-' . $tanda)
            ->assertDontSee('DILUAR-' . $tanda);
    }

    // ----------------------------------------- mendaftarkan dari panitia

    #[Test]
    public function borang_pendaftaran_hanya_untuk_orang_dalam(): void
    {
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);
        $tujuan = route('account.dashboard.index');

        $this->actingAs($pelanggan)
            ->get(route('account.pendaftaran-layanan.baru'))
            ->assertRedirect($tujuan);

        $this->flushSession();

        $this->actingAs($pelanggan)
            ->post(route('account.pendaftaran-layanan.simpan'), [
                'layanan' => 'scopus_camp', 'nama' => 'Paksa Masuk',
                'email' => 'paksa@contoh.test', 'telp' => '0811',
            ])
            ->assertRedirect($tujuan);
    }

    #[Test]
    public function panitia_bisa_mendaftarkan_orang_dan_kuotanya_berkurang(): void
    {
        /*
         * Sebelum ini TIDAK ADA jalurnya: yang mendaftar lewat WhatsApp atau
         * datang langsung tidak bisa dimasukkan, sehingga daftar pendaftar
         * tidak pernah lengkap dan kuota angkatan tidak mencerminkan kursi
         * yang sebenarnya terpakai.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $angkatan->forceFill(['biaya' => '1000000', 'total_biaya' => '900000'])->save();

        $jawab = $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Didaftarkan Panitia',
            'email' => 'panitia' . Str::random(6) . '@contoh.test',
            'telp' => '0812-3456-7890',
            'affiliasi' => 'Instansi Uji',
            'jumlah' => 3,
        ]);

        $baris = PendaftaranScopusCamp::where('nama', 'Didaftarkan Panitia')->first();

        $this->assertNotNull($baris, 'Barisnya harus tersimpan.');
        // Diantar ke halaman rinciannya: nomor dan kode uniknya baru dibuat
        // sistem, dan itu yang perlu dikirim panitia ke orangnya.
        $jawab->assertRedirect(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $baris->getKey()]));

        $this->assertSame('diproses', $baris->status, 'Status awalnya milik layanan itu.');
        $this->assertSame(2700000 + (int) $baris->kode_unik, (int) $baris->total_pembayaran,
            '900.000 x 3 orang, plus kode uniknya.');
        $this->assertNotEmpty($baris->id_transaksi, 'Nomornya dibuat sistem.');
        $this->assertGreaterThan(0, (int) $baris->kode_unik, 'Kode uniknya dibuat sistem.');
        $this->assertStringContainsString('Didaftarkan panitia', (string) $baris->note);
        $this->assertStringContainsString('Uji administrator', (string) $baris->note);

        $this->assertSame(17, (int) $angkatan->refresh()->sisa_kuota, 'Tiga kursinya terpakai.');
    }

    #[Test]
    public function kursi_yang_tidak_cukup_ditolak_dan_kuotanya_tidak_bergeser(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 2);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Melebihi Kursi',
            'email' => 'lebih' . Str::random(6) . '@contoh.test',
            'telp' => '0812-0000-0000',
            'jumlah' => 5,
        ]);

        $this->assertNull(PendaftaranScopusCamp::where('nama', 'Melebihi Kursi')->first());
        $this->assertSame(2, (int) $angkatan->refresh()->sisa_kuota, 'Kuotanya tidak boleh bergeser.');
    }

    #[Test]
    public function angkatan_milik_layanan_lain_ditolak(): void
    {
        /*
         * Angkatan Bibliometrik dipasang ke pendaftaran Scopus Camp akan
         * membuat kuota KEDUA layanan salah tanpa ada yang menolak — dan
         * barisnya muncul di saringan layanan yang keliru.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $milikBiblio = $this->angkatan('bibliometrik', 20, 20);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $milikBiblio->id,
            'nama' => 'Angkatan Silang',
            'email' => 'silang' . Str::random(6) . '@contoh.test',
            'telp' => '0812-0000-0001',
            'jumlah' => 1,
        ]);

        $this->assertNull(PendaftaranScopusCamp::where('nama', 'Angkatan Silang')->first());
        $this->assertSame(20, (int) $milikBiblio->refresh()->sisa_kuota);
    }

    #[Test]
    public function potongan_khusus_mengurangi_total_dan_tercatat(): void
    {
        /*
         * Potongan KHUSUS, di atas potongan bawaan angkatannya.
         *
         * Angkatan sudah punya diskonnya sendiri dan itu sudah terhitung di
         * `total_biaya`; yang ini untuk hal yang tidak bisa diketahui
         * angkatan — peserta yang disponsori, harga mitra, atau kesepakatan
         * di tempat.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $angkatan->forceFill(['biaya' => '1000000', 'total_biaya' => '900000'])->save();

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Disponsori',
            'email' => 'sponsor' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0001',
            'jumlah' => 2,
            // Ditulis berpemisah, seperti yang diketik orang.
            'potongan' => '300.000',
            'kode_potongan' => 'SPONSOR',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Peserta Disponsori')->first();

        $this->assertNotNull($b);
        // 900.000 x 2 = 1.800.000, dipotong 300.000.
        $this->assertSame(1500000 + (int) $b->kode_unik, (int) $b->total_pembayaran);
        $this->assertSame(300000, (int) $b->nominal_diskon);
        $this->assertSame('SPONSOR', $b->kode_diskon);
        // Potongan yang tidak bisa dijelaskan enam bulan kemudian sama saja
        // dengan selisih uang yang tidak ada keterangannya.
        // Kalimat jejaknya netral — "potongan", bukan "potongan khusus" —
        // sebab potongannya kini bisa dua jenis, dan jenisnya sudah disebut
        // oleh kodenya di dalam kurung.
        $this->assertStringContainsString('potongan Rp 300.000 (SPONSOR)', (string) $b->note);
    }

    #[Test]
    public function alumni_mendapat_potongan_dari_tarif_layanan(): void
    {
        /*
         * Potongan alumni disetel SEKALI di Tarif Layanan, lalu diambil
         * borang pendaftaran — bukan diketik ulang tiap kali, yang berarti
         * angkanya bisa berbeda-beda antar petugas tanpa ada yang tahu mana
         * yang benar.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $angkatan->forceFill([
            'varian' => null, 'biaya' => '1000000', 'total_biaya' => '1000000',
        ])->save();

        $this->tarif('scopus_camp', null, 10);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta Alumni',
            'email' => 'alumni' . Str::random(6) . '@contoh.test',
            'telp' => '0817-0000-0001',
            'jumlah' => 2,
            'alumni' => '1',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Peserta Alumni')->first();

        $this->assertNotNull($b);

        /*
         * Potongannya 10% dari SATU KURSI, bukan dari dua.
         *
         * Pernyataan lamanya 200.000 — 10% dari seluruh subtotal — dan itu
         * memang yang dulu dihitung kodenya. Status alumni milik satu orang,
         * bukan milik teman yang ia ajak; dihitung dari subtotal, satu alumni
         * yang mengajak enam teman memotong 10% dari tujuh kursi sekaligus.
         */
        $this->assertSame(100000, (int) $b->nominal_diskon);
        $this->assertSame(1900000 + (int) $b->kode_unik, (int) $b->total_pembayaran);
        $this->assertSame('ALUMNI', $b->kode_diskon);
    }

    #[Test]
    public function potongan_alumni_tidak_bisa_digabung_dengan_potongan_khusus(): void
    {
        /*
         * Diminta pemilik, dan memang begitu mestinya: dua potongan yang
         * ditumpuk membuat harga akhirnya tidak bisa dijelaskan dari salah
         * satunya. Yang alumni menang — ia datang dari aturan yang disetel
         * sekali, sementara potongan khusus diketik per pendaftaran.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $angkatan->forceFill(['varian' => null, 'biaya' => '1000000', 'total_biaya' => '1000000'])->save();

        $this->tarif('scopus_camp', null, 10);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Alumni Dan Khusus',
            'email' => 'dua' . Str::random(6) . '@contoh.test',
            'telp' => '0817-0000-0002',
            'jumlah' => 1,
            'alumni' => '1',
            // Dikirim juga, dan HARUS diabaikan — bukan ditambahkan.
            'potongan' => '900000',
            'kode_potongan' => 'SPONSOR',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Alumni Dan Khusus')->first();

        $this->assertNotNull($b);
        $this->assertSame(100000, (int) $b->nominal_diskon, 'Hanya potongan alumni yang berlaku.');
        $this->assertSame(900000 + (int) $b->kode_unik, (int) $b->total_pembayaran);
        $this->assertSame('ALUMNI', $b->kode_diskon);
    }

    #[Test]
    public function potongan_alumni_mengikuti_varian_angkatannya(): void
    {
        /*
         * Satu layanan bisa punya beberapa tarif dengan varian berbeda —
         * Scopus Camp punya jawa Rp 5,5jt dan luar_jawa Rp 6,5jt — dan
         * potongan alumninya disetel per tarif. Memakai tarif mana pun yang
         * kebetulan ketemu duluan berarti memberi potongan milik varian lain.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->tarif('scopus_camp', 'jawa', 10);
        $this->tarif('scopus_camp', 'luar_jawa', 25);

        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $angkatan->forceFill([
            'varian' => 'luar_jawa', 'biaya' => '1000000', 'total_biaya' => '1000000',
        ])->save();

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Alumni Luar Jawa',
            'email' => 'luar' . Str::random(6) . '@contoh.test',
            'telp' => '0817-0000-0003',
            'jumlah' => 1,
            'alumni' => '1',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Alumni Luar Jawa')->first();

        $this->assertNotNull($b);
        $this->assertSame(250000, (int) $b->nominal_diskon,
            'Yang berlaku 25% milik luar_jawa, bukan 10% milik jawa.');
    }

    #[Test]
    public function alumni_tanpa_tarif_yang_menyetelnya_tidak_memotong_apa_pun(): void
    {
        /*
         * Kiriman tetap diperiksa peladen walau borangnya tidak menawarkan
         * pilihan alumni untuk layanan seperti itu — yang menentukan peladen,
         * bukan markah yang bisa diubah dari peramban.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $angkatan->forceFill(['varian' => null, 'biaya' => '1000000', 'total_biaya' => '1000000'])->save();

        // Tarifnya ada, tetapi potongan alumninya tidak disetel.
        $this->tarif('scopus_camp', null, null);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Alumni Tanpa Aturan',
            'email' => 'tanpa' . Str::random(6) . '@contoh.test',
            'telp' => '0817-0000-0004',
            'jumlah' => 1,
            'alumni' => '1',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Alumni Tanpa Aturan')->first();

        $this->assertNotNull($b);
        $this->assertSame(1000000 + (int) $b->kode_unik, (int) $b->total_pembayaran,
            'Tidak ada potongan sama sekali.');
    }

    #[Test]
    public function potongan_tidak_boleh_melebihi_tagihannya(): void
    {
        // Total negatif tidak berarti apa-apa sebagai nominal transfer, dan
        // kode uniknya akan dicari atas angka yang mustahil dicocokkan.
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $angkatan->forceFill(['biaya' => '500000', 'total_biaya' => '500000'])->save();

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_camp',
            'kategori_id' => $angkatan->id,
            'nama' => 'Potongan Kebablasan',
            'email' => 'lebih' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0002',
            'jumlah' => 1,
            'potongan' => '9999999',
        ])->assertRedirect();

        $b = PendaftaranScopusCamp::where('nama', 'Potongan Kebablasan')->first();

        $this->assertNotNull($b);
        $this->assertSame((int) $b->kode_unik, (int) $b->total_pembayaran,
            'Potongannya memakan seluruh tagihan; yang tersisa hanya kode uniknya.');
        $this->assertSame(500000, (int) $b->nominal_diskon, 'Potongannya dibatasi tagihannya.');
    }

    #[Test]
    public function layanan_tanpa_kolom_diskon_tidak_menyimpan_potongan(): void
    {
        /*
         * Scopus Kafe tidak punya kolom nominal_diskon — dan di sana
         * nominalnya memang diketik langsung, jadi potongannya sudah termasuk
         * di dalamnya. Menuliskannya ke sana akan melempar "Unknown column"
         * dan menggagalkan seluruh pendaftarannya.
         */
        $this->assertFalse(Pendaftaran::katalogBisaDibuat()['scopus_kafe']['bisa_potongan']);

        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_kafe',
            'nama' => 'Kafe Dengan Potongan',
            'email' => 'kafe' . Str::random(6) . '@contoh.test',
            'telp' => '0816-0000-0003',
            'total' => '200000',
            'potongan' => '50000',
            'kode_potongan' => 'ABAIKAN',
        ])->assertRedirect();

        $b = PendaftaranScopusKafe::where('nama', 'Kafe Dengan Potongan')->first();

        $this->assertNotNull($b, 'Pendaftarannya tetap tersimpan, tidak gagal.');
        // Potongannya tetap mengurangi totalnya — yang tidak bisa cuma
        // mencatatnya di kolom tersendiri.
        $this->assertSame(150000 + (int) $b->kode_unik_pembayaran, (int) $b->total_keseluruhan_pembayaran);
    }

    #[Test]
    public function layanan_tanpa_angkatan_memakai_nominal_yang_diketik(): void
    {
        // Scopus Kafe tidak berangkatan — tarifnya per sesi dan berbeda-beda,
        // jadi nominalnya memang harus diketik.
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'scopus_kafe',
            'nama' => 'Pemesan Kafe Uji',
            'email' => 'kafe' . Str::random(6) . '@contoh.test',
            'telp' => '0813-0000-0000',
            // Ditulis berpemisah: pengendali lama membuang titik di satu
            // layanan dan koma di layanan lain, jadi "250.000" pernah
            // tersimpan sebagai nol.
            'total' => '250.000',
        ])->assertRedirect();

        $baris = PendaftaranScopusKafe::where('nama', 'Pemesan Kafe Uji')->first();

        $this->assertNotNull($baris);
        $this->assertSame(250000 + (int) $baris->kode_unik_pembayaran,
            (int) $baris->total_keseluruhan_pembayaran);
        $this->assertSame('menunggu verifikasi', $baris->status);
        $this->assertNotEmpty($baris->id_pemesanan);
    }

    #[Test]
    public function clinik_scopus_tidak_bisa_didaftarkan_dari_layar_ini(): void
    {
        /*
         * Disengaja, dan dijaga supaya tidak "dilengkapi" tanpa sadar:
         * pemesanan Clinik Scopus mengikat sesi, trainer, dan akun pelanggan —
         * ketiganya kolom NOT NULL yang menunjuk baris lain. Borang yang
         * menebaknya akan membuat pemesanan yang menunjuk sesi atau trainer
         * yang salah.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->assertArrayNotHasKey('clinik_scopus', Pendaftaran::katalogBisaDibuat());

        $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
            'layanan' => 'clinik_scopus',
            'nama' => 'Clinik Paksa',
            'email' => 'clinik' . Str::random(6) . '@contoh.test',
            'telp' => '0814-0000-0000',
            'total' => '100000',
        ])->assertSessionHasErrors('layanan');

        $this->assertNull(ClinikScopusPemesanan::where('nama_pemesan', 'Clinik Paksa')->first());
    }

    #[Test]
    public function nomor_pendaftaran_menyebut_layanan_dan_tanggalnya(): void
    {
        /*
         * Pernyataan lama uji ini mengunci nomor LIMA AKSARA ACAK untuk tiga
         * layanan — "NMMCG", "GNKMO", "HUQEZ". Aturannya memang diganti,
         * bukan ujinya yang kebetulan rewel: nomor begitu tidak menyebutkan
         * layanannya, tanggalnya, maupun urutannya, sehingga panitia yang
         * menerima "nomor saya NMMCG" harus mencarinya dulu untuk tahu itu
         * Scopus Camp atau Bibliometrik. Di telepon, lima aksara acak juga
         * jauh lebih mudah salah didengar daripada tanggal plus nomor urut.
         *
         * Awalannya KATA, bukan singkatan dua huruf: "SC" dan "CS" untuk
         * Scopus Camp dan Clinik Scopus hanya berbeda urutan hurufnya.
         */
        $hari = now()->format('Ymd');

        foreach ([
            'scopus_camp' => 'CAMP',
            'bibliometrik' => 'BIB',
            'webinar_eksklusif' => 'WE',
            'scopus_kafe' => 'KAFE',
            'clinik_scopus' => 'KLINIK',
        ] as $layanan => $awalan) {
            $this->assertMatchesRegularExpression(
                '/^' . $awalan . '-' . $hari . '-\d{4}$/',
                Pendaftaran::nomorBaru($layanan),
                'Nomor ' . $layanan . ' harus menyebut layanan dan tanggalnya.'
            );
        }

        // Tidak ada dua layanan yang berbagi awalan: nomor yang awalannya
        // sama membuat seluruh gunanya hilang.
        $semua = array_map(
            fn ($l) => explode('-', Pendaftaran::nomorBaru($l))[0],
            array_keys(Pendaftaran::katalog())
        );

        $this->assertSame(count($semua), count(array_unique($semua)));
    }

    #[Test]
    public function nomor_berurut_dalam_satu_hari_dan_tidak_pernah_kembar(): void
    {
        /*
         * Nomor urutnya dihitung dari yang TERBESAR hari itu, bukan dari
         * jumlah barisnya: pendaftaran yang dihapus akan membuat hitungan
         * baris memberi nomor yang sudah pernah dipakai — dan dua orang
         * menyebut nomor yang sama saat menghubungi panitia.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 20, 20);
        $nomor = [];

        for ($ke = 1; $ke <= 3; $ke++) {
            $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
                'layanan' => 'scopus_camp',
                'kategori_id' => $angkatan->id,
                'nama' => 'Berurut ' . $ke,
                'email' => 'urut' . Str::random(6) . '@contoh.test',
                'telp' => '0816-0000-01' . $ke . '0',
            ])->assertRedirect();

            $nomor[] = PendaftaranScopusCamp::where('nama', 'Berurut ' . $ke)->value('id_transaksi');
        }

        $this->assertSame($nomor, array_unique($nomor), 'Tidak boleh ada nomor kembar.');

        $urut = array_map(fn ($n) => (int) substr((string) $n, -4), $nomor);

        $this->assertSame([$urut[0], $urut[0] + 1, $urut[0] + 2], $urut);

        // Nomor LAMA tetap apa adanya. Yang sudah beredar ada di email
        // pendaftar dan percakapan WhatsApp; menulis ulangnya membuat nomor
        // yang dipegang orang tidak cocok lagi dengan yang ada di sistem.
        $lama = PendaftaranScopusCamp::where('id_transaksi', 'not like', 'CAMP-%')
            ->whereNotNull('id_transaksi')->count();

        $this->assertGreaterThan(0, $lama, 'Baris lama harus masih memakai nomor lamanya.');
    }

    #[Test]
    public function kode_unik_membuat_totalnya_belum_terpakai(): void
    {
        /*
         * Gunanya mencocokkan mutasi rekening: dua orang yang membayar nominal
         * yang SAMA PERSIS tidak bisa dibedakan. Jadi yang harus unik bukan
         * kodenya melainkan hasil penjumlahannya.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $angkatan = $this->angkatan('scopus_camp', 50, 50);
        $angkatan->forceFill(['biaya' => '500000', 'total_biaya' => '500000'])->save();

        $totalnya = [];

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($orang)->post(route('account.pendaftaran-layanan.simpan'), [
                'layanan' => 'scopus_camp',
                'kategori_id' => $angkatan->id,
                'nama' => 'Kode Unik ' . $i,
                'email' => 'kode' . $i . Str::random(5) . '@contoh.test',
                'telp' => '0815-0000-000' . $i,
                'jumlah' => 1,
            ])->assertRedirect();

            $b = PendaftaranScopusCamp::where('nama', 'Kode Unik ' . $i)->first();
            $this->assertNotNull($b);
            $totalnya[] = (int) $b->total_pembayaran + (int) $b->kode_unik;

            $this->flushSession();
        }

        $this->assertSame(count($totalnya), count(array_unique($totalnya)),
            'Dua pendaftaran menghasilkan nominal transfer yang sama persis.');
    }

    // ------------------------------------------------ saringan tanggal

    #[Test]
    public function saringan_tanggal_memasukkan_hari_batasnya_sendiri(): void
    {
        /*
         * Perkara batas yang paling mudah salah: kolom waktunya bertimestamp,
         * jadi `<= '2026-10-03'` berarti `<= 2026-10-03 00:00:00` dan
         * MEMBUANG seluruh pendaftaran yang terjadi pada hari itu. Orangnya
         * menyaring "sampai hari ini" lalu mendapati pendaftaran hari ini
         * hilang — tanpa galat apa pun.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);
        $angkatan = $this->angkatan('scopus_camp');

        $sore = PendaftaranScopusCamp::create([
            'id_transaksi' => 'SORE-' . $tanda,
            'kategori_id' => $angkatan->id,
            'nama' => 'Daftar Sore ' . $tanda,
            'email' => $tanda . '@contoh.test',
            'telp' => '0811-0000-0011',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '10000',
            'status' => 'diproses',
        ]);

        // Jam 17.30 pada hari yang jadi batas akhirnya.
        $hari = now()->subDays(3)->startOfDay();
        $sore->forceFill(['created_at' => $hari->copy()->setTime(17, 30)])->save();

        $halaman = $this->actingAs($orang)->get(route('account.pendaftaran-layanan.index', [
            'cari' => $tanda,
            'dari' => $hari->toDateString(),
            'sampai' => $hari->toDateString(),
        ]));

        $halaman->assertOk();
        $halaman->assertSee('SORE-' . $tanda);
    }

    #[Test]
    public function saringan_tanggal_membuang_yang_di_luar_rentang(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);
        $angkatan = $this->angkatan('scopus_camp');

        $dibuat = [];

        foreach ([['LAMA', 40], ['TENGAH', 10], ['BARU', 1]] as [$nama, $hariLalu]) {
            $b = PendaftaranScopusCamp::create([
                'id_transaksi' => $nama . '-' . $tanda,
                'kategori_id' => $angkatan->id,
                'nama' => $nama . ' ' . $tanda,
                'email' => strtolower($nama) . $tanda . '@contoh.test',
                'telp' => '0811-0000-0012',
                'jumlah_pendaftar' => '1',
                'total_pembayaran' => '10000',
                'status' => 'diproses',
            ]);

            $b->forceFill(['created_at' => now()->subDays($hariLalu)])->save();
            $dibuat[$nama] = $b;
        }

        $halaman = $this->actingAs($orang)->get(route('account.pendaftaran-layanan.index', [
            'cari' => $tanda,
            'dari' => now()->subDays(20)->toDateString(),
            'sampai' => now()->subDays(5)->toDateString(),
        ]));

        $halaman->assertOk();
        $halaman->assertSee('TENGAH-' . $tanda);
        $halaman->assertDontSee('LAMA-' . $tanda);
        $halaman->assertDontSee('BARU-' . $tanda);
    }

    #[Test]
    public function tanggal_yang_tidak_masuk_akal_diabaikan_bukan_mengosongkan_daftar(): void
    {
        /*
         * "2026-13-45" diterima SQL sebagai untaian dan kuerinya mengembalikan
         * nol baris TANPA galat — jadi orangnya menyimpulkan datanya yang
         * tidak ada. Diabaikan, daftarnya tetap utuh.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        [$camp] = $this->buat('scopus_camp');

        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', [
                'cari' => $camp->id_transaksi,
                'dari' => '2026-13-45',
                'sampai' => 'bukan tanggal',
            ]))
            ->assertOk()
            ->assertSee($camp->id_transaksi);
    }

    #[Test]
    public function berkas_unduhan_menyebut_rentang_tanggalnya(): void
    {
        // Berkas berisi pendaftaran satu bulan yang tidak menyebut bulannya
        // terbaca persis seperti daftar yang lengkap.
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.pdf', [
                'dari' => '2026-09-01', 'sampai' => '2026-09-30',
            ]))
            ->assertOk();

        $html = view('account.pendaftaran_layanan.ekspor-pdf', [
            'baris' => collect(),
            'saringan' => ['Tanggal daftar' => '01 Sep 2026 sampai 30 Sep 2026'],
            'katalog' => Pendaftaran::katalog(),
        ])->render();

        $this->assertStringContainsString('01 Sep 2026 sampai 30 Sep 2026', $html);
    }

    // --------------------------------------------- menunggu terlalu lama

    #[Test]
    public function saringan_menggantung_hanya_menyisakan_yang_menunggu_lama(): void
    {
        /*
         * Empat dari lima layanan TIDAK punya kedaluwarsa sama sekali, jadi
         * pendaftaran yang transfernya tidak pernah datang menunggu selamanya
         * tanpa ada yang menengok. Terukur di basis data: 5 menunggu lebih
         * dari 7 hari, 2 di antaranya lebih dari 90 hari.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);
        $angkatan = $this->angkatan('scopus_camp');

        $buat = function (string $nama, int $hariLalu, string $status) use ($tanda, $angkatan) {
            $b = PendaftaranScopusCamp::create([
                'id_transaksi' => $nama . '-' . $tanda,
                'kategori_id' => $angkatan->id,
                'nama' => $nama . ' ' . $tanda,
                'email' => strtolower($nama) . $tanda . '@contoh.test',
                'telp' => '0811-0000-0013',
                'jumlah_pendaftar' => '1',
                'total_pembayaran' => '10000',
                'status' => $status,
            ]);

            $b->forceFill(['created_at' => now()->subDays($hariLalu)])->save();

            return $b;
        };

        $buat('LAMAMENUNGGU', 30, 'diproses');
        $buat('BARUMENUNGGU', 2, 'diproses');
        // Sudah lunas: lamanya tidak jadi soal, ia tidak menggantung.
        $buat('LAMALUNAS', 30, 'Pendaftaran Diterima');

        $halaman = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['cari' => $tanda, 'lama' => '1']));

        $halaman->assertOk();
        $halaman->assertSee('LAMAMENUNGGU-' . $tanda);
        $halaman->assertDontSee('BARUMENUNGGU-' . $tanda);
        $halaman->assertDontSee('LAMALUNAS-' . $tanda);
    }

    // ------------------------------------------------ jejak kelima layanan

    #[Test]
    public function kelima_layanan_mencatat_jejak_perubahan_statusnya(): void
    {
        /*
         * Jejaknya kini di TABELNYA SENDIRI, bukan di kolom catatan panitia.
         *
         * Dulu ia ditempelkan ke `note` — kolom yang sama yang dipakai
         * panitia menulis catatannya. Dua hal rusak karenanya: catatan
         * panitia bisa terhapus saat disunting, dan dua layanan yang tidak
         * punya kolom itu tidak pernah terekam sama sekali.
         *
         * Diperiksa untuk KELIMA layanan sekaligus: layanan berikutnya yang
         * datang harus ketahuan di sini, bukan saat ada selisih uang yang
         * tidak bisa ditelusuri.
         */
        Mail::fake();

        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $sasaran = [
            'scopus_camp' => 'Pendaftaran Dibatalkan',
            'bibliometrik' => 'Pendaftaran Dibatalkan',
            'webinar_eksklusif' => 'cancel',
            'scopus_kafe' => 'pembayaran ditolak',
            'clinik_scopus' => 'canceled',
        ];

        foreach ($sasaran as $layanan => $status) {
            [$b] = $this->buat($layanan);
            $statusLama = $b->status;

            $this->actingAs($orang)->post(
                route('account.pendaftaran-layanan.status', [$layanan, $b->getKey()]),
                ['status' => $status]
            )->assertRedirect();

            $jejak = \App\PendaftaranJejak::milik($layanan, (string) $b->getKey())
                ->terurut()->get();

            $this->assertCount(1, $jejak, "Jejak perubahan {$layanan} tidak tercatat.");
            $this->assertSame($statusLama, $jejak[0]->dari, "Status asal {$layanan} tidak tercatat.");
            $this->assertSame($status, $jejak[0]->ke, "Status tujuan {$layanan} tidak tercatat.");
            $this->assertNotEmpty($jejak[0]->oleh_nama, "Jejak {$layanan} tidak menyebut siapa yang mengubah.");

            /*
             * Dan catatan panitianya TIDAK tersentuh. Inilah inti
             * pemisahannya: jejak sistem tidak boleh menumpang di kolom yang
             * disunting orang.
             */
            $kolom = Pendaftaran::kolomCatatan($layanan);

            if ($kolom !== null) {
                $this->assertStringNotContainsString('→', (string) $b->refresh()->{$kolom},
                    "Jejak {$layanan} masih ikut ditulis ke catatan panitia.");
            }
            /*
             * Namanya diperiksa di JEJAKNYA (lihat oleh_nama di atas), bukan
             * di kolom catatan. Assertion lama yang menuntutnya ada di `note`
             * justru menuntut hal yang sekarang dilarang.
             */

            $this->flushSession();
        }
    }

    // ------------------------------------------- layar yang dipertahankan

    #[Test]
    public function pelanggan_hanya_melihat_pesanan_clinik_miliknya(): void
    {
        /*
         * Riwayat Pemesanan Clinik Scopus DIPERTAHANKAN saat empat layar
         * pendaftaran per layanan dibuang: ia satu-satunya tempat pelanggan
         * bisa melihat pesanannya sendiri.
         *
         * Dan penyaringnya dikencangkan. Syarat lamanya `jenis ===
         * 'perorangan'`, sehingga pelanggan berjenis lain — atau kosong —
         * jatuh ke luar semua cabang dan melihat pesanan SELURUH orang.
         * Uji ini memakai pelanggan berjenis 'perusahaan', yaitu keadaan
         * yang dulu membuka celahnya.
         */
        // Kolom `jenis` NOT NULL berbawaan 'perorangan', jadi celahnya hanya
        // terbuka untuk akun yang jenisnya disetel ke nilai lain dengan
        // sengaja — 'perusahaan' adalah nilai yang sudah dikenal borangnya.
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);
        $pelanggan->forceFill(['jenis' => 'perusahaan'])->save();
        $pelanggan->refresh();

        $this->assertSame('perusahaan', $pelanggan->jenis,
            'Ujinya harus memakai pelanggan yang BUKAN perorangan.');

        [$punyaOrangLain] = $this->buat('clinik_scopus');

        $milikDia = ClinikScopusPemesanan::create([
            'clinikscopus_id' => $punyaOrangLain->clinikscopus_id,
            'trainer_id' => $punyaOrangLain->trainer_id,
            'customer_id' => $pelanggan->id,
            'id_transaksi' => 'MILIKDIA-' . Str::random(6),
            'kode_booking' => 'BOOK-' . Str::random(6),
            'nama_pemesan' => 'Punya Dia Sendiri',
            'email_pemesan' => 'dia@contoh.test',
            'telp_pemesan' => '0811-0000-0009',
            'sesi' => 'Sesi 1',
            'total_pembayaran' => 50000,
            'status' => 'pending',
        ]);

        $halaman = $this->actingAs($pelanggan)
            ->get(route('account.Clinik-Scopus-Riwayat-Pemesanan.index'));

        $halaman->assertOk();

        /*
         * Diperiksa dari KODE BOOKING-nya, bukan dari nama pemesannya.
         *
         * Layar itu menampilkan nama dari relasi `customer` — yaitu
         * `full_name` akunnya — bukan kolom `nama_pemesan` di barisnya. Jadi
         * memeriksa nama pemesan akan gagal walau penyaringnya benar, dan
         * ujinya menuduh kode yang tidak bersalah.
         */
        $halaman->assertSee($milikDia->kode_booking);
        $halaman->assertDontSee($punyaOrangLain->kode_booking);
    }

    #[Test]
    public function tombol_hapus_tidak_disuguhkan_ke_yang_tidak_boleh(): void
    {
        /*
         * Layar tidak boleh menyuguhkan sesuatu yang kirimannya akan ditolak,
         * dan alasannya ditulis di tempat tombol itu seharusnya berada.
         * Diperiksa dari MARKAHNYA, bukan dari tulisannya: komentar Blade
         * tidak terkirim, tetapi kalimat di dalam kartunya bisa saja memuat
         * kata yang sama.
         */
        [$camp] = $this->buat('scopus_camp');
        $alamat = route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]);

        $karyawan = $this->actingAs($this->akun(User::PERAN_KARYAWAN))->get($alamat);
        $karyawan->assertOk();
        $karyawan->assertDontSee('id="rin-tombol-hapus"', false);
        $karyawan->assertSee('Hanya administrator yang boleh menghapus');

        $this->flushSession();

        $admin = $this->actingAs($this->akun(User::PERAN_ADMINISTRATOR))->get($alamat);
        $admin->assertOk();
        $admin->assertSee('id="rin-tombol-hapus"', false);
    }

    #[Test]
    public function semua_status_ditawarkan_sebagai_tombol_sekali_tekan(): void
    {
        /*
         * Menu tarik diganti deret tombol, dan penggantian seperti itu mudah
         * diam-diam MENGURANGI pilihan: menu tarik memuat semuanya dengan satu
         * perulangan, sedangkan tombol yang dirakit tangan gampang tertinggal
         * satu. Scopus Camp punya enam status, Scopus Kafe tiga, Clinik empat
         * — tidak ada yang sama.
         *
         * Jadi yang dituntut: SETIAP status yang boleh dipasang panitia punya
         * tombolnya sendiri, berikut nilai yang benar di data-nilai.
         */
        [$camp] = $this->buat('scopus_camp');

        $halaman = $this->actingAs($this->akun(User::PERAN_ADMINISTRATOR))
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]));

        $halaman->assertOk();

        $pilihan = Pendaftaran::pilihanStatus('scopus_camp');

        $this->assertNotEmpty($pilihan, 'Prasyarat ujinya hilang: layanan ini tidak punya pilihan status.');

        foreach ($pilihan as $nilai => $tulisan) {
            $halaman->assertSee('data-nilai="' . e($nilai) . '"', false);
            $halaman->assertSee('data-tulisan="' . e($tulisan) . '"', false);
        }

        // Nilainya tetap dikirim lewat medan bernama 'status', sama seperti
        // sebelumnya — pengendalinya tidak ikut diubah.
        $halaman->assertSee('name="status"', false);
        $halaman->assertSee('id="rin-borang-status"', false);
    }

    #[Test]
    public function status_yang_berlaku_ditandai_dan_tidak_bisa_ditekan_lagi(): void
    {
        /*
         * Dimatikan, bukan disembunyikan: deret yang berubah panjang tiap kali
         * statusnya pindah membuat orang kehilangan patokan letak. Dan
         * menekan status yang sedang berlaku berarti mengirim perubahan yang
         * tidak mengubah apa pun — pada status bersurat, itu mengirim email
         * ulang ke pendaftarnya.
         */
        [$camp] = $this->buat('scopus_camp');

        $halaman = $this->actingAs($this->akun(User::PERAN_ADMINISTRATOR))
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]))
            ->assertOk();

        $isi = $halaman->getContent();

        $this->assertSame(1, preg_match_all('/class="rin-status-tombol[^"]*\bsekarang\b[^"]*"/', $isi),
            'Harus ada TEPAT satu tombol yang ditandai sebagai status sekarang.');

        // Tombol yang ditandai itu harus yang statusnya memang terpasang, dan
        // harus benar-benar dimatikan.
        $this->assertSame(1, preg_match(
            '/<button[^>]*\bsekarang\b[^>]*>/s', $isi, $cocok
        ));

        $this->assertStringContainsString('disabled', $cocok[0],
            'Tombol status yang sedang berlaku harus dimatikan.');

        $this->assertStringContainsString('data-nilai="' . e($camp->status) . '"', $cocok[0],
            'Yang ditandai "sekarang" bukan status yang sedang terpasang.');
    }

    #[Test]
    public function status_yang_mengirim_email_ditandai_di_tombolnya_sendiri(): void
    {
        /*
         * Penandanya di TOMBOLNYA, bukan hanya di catatan bawah.
         *
         * Catatan itu menyebut nama status dalam kalimat; orang yang sedang
         * mengarahkan kursor ke sebuah tombol tidak sedang membacanya. Dan
         * email yang sudah terkirim tidak bisa ditarik kembali, jadi
         * peringatannya harus berada di tempat tangannya.
         *
         * data-surat juga yang dibaca skrip penegasannya untuk memilih
         * kalimat mana yang ditampilkan, jadi ia bukan hiasan.
         */
        [$camp] = $this->buat('scopus_camp');

        $isi = $this->actingAs($this->akun(User::PERAN_ADMINISTRATOR))
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]))
            ->assertOk()
            ->getContent();

        $adaYangBersurat = false;

        foreach (Pendaftaran::pilihanStatus('scopus_camp') as $nilai => $tulisan) {
            $bersurat = Pendaftaran::suratUntuk('scopus_camp', $nilai) !== null;

            if (! $bersurat) {
                continue;
            }

            $adaYangBersurat = true;

            $this->assertSame(1, preg_match(
                '/<button[^>]*data-nilai="' . preg_quote(e($nilai), '/') . '"[^>]*>/s', $isi, $cocok
            ), 'Tombol untuk status "' . $tulisan . '" tidak ketemu.');

            $this->assertStringContainsString('data-surat="1"', $cocok[0],
                'Status "' . $tulisan . '" mengirim email tetapi tombolnya tidak menandainya, '
                    . 'jadi penegasannya tidak akan menyebut email itu.');

            /*
             * Penandanya harus TERBACA, bukan sekadar ada di markah.
             *
             * Versi pertama cuma glif amplop 11px berwarna abu dengan
             * keterangan di atribut title — dan title hanya muncul kalau
             * kursor ditahan di atasnya. Panitia yang hendak memindahkan
             * status tidak sedang menunggui tooltip; ia menekan. Emailnya
             * tidak bisa ditarik kembali, jadi tandanya harus terbaca
             * SEBELUM ditekan.
             */
            $this->assertSame(1, preg_match(
                '#<button[^>]*data-nilai="' . preg_quote(e($nilai), '#') . '"[^>]*>(?<dalam>.*?)</button>#s',
                $isi, $isiTombol
            ));

            $this->assertStringContainsString('kirim email', strtolower($isiTombol['dalam']),
                'Tombol status "' . $tulisan . '" mengirim email tetapi tidak mengatakannya '
                    . 'dengan kata — penandanya tidak terbaca sebelum ditekan.');
        }

        $this->assertTrue($adaYangBersurat,
            'Prasyarat ujinya hilang: tidak ada satu pun status Scopus Camp yang mengirim email.');
    }

    #[Test]
    public function tiap_tab_punya_warna_ikonnya_sendiri_seperti_halaman_profil(): void
    {
        /*
         * Mengikuti model tab halaman Profil, yang tiap ikonnya berwarna
         * sendiri — terukur di sana: ungu, jingga, biru, hijau.
         *
         * Tanpa warna, ketujuh ikon tab di layar ini sewarna semua dan
         * praktis cuma hiasan: yang membedakan satu tab dari tab lain hanya
         * tulisannya, dan itu menuntut dibaca. Warna terbaca lebih dulu
         * daripada huruf.
         *
         * Dijaga dari MARKAHNYA, bukan dari hasil rupanya: kelas warnanya
         * yang menentukan, dan kelas yang tertinggal pada satu tab tidak
         * menimbulkan galat apa pun.
         */
        [$camp] = $this->buat('scopus_camp');

        $isi = $this->actingAs($this->akun(User::PERAN_ADMINISTRATOR))
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, preg_match('#<ul[^>]*id="rin-tab"(?<isi>.*?)</ul>#s', $isi, $strip),
            'Deret tab tidak ketemu.');

        preg_match_all('/<i class="fas ([^"]*)"/', $strip['isi'], $ikon);

        $this->assertGreaterThanOrEqual(3, count($ikon[1]),
            'Prasyarat ujinya hilang: tab yang terbentuk terlalu sedikit.');

        $warna = [];

        foreach ($ikon[1] as $kelas) {
            $this->assertSame(1, preg_match('/\bmis-ikon-[a-z]+\b/', $kelas, $cocok),
                'Ada ikon tab tanpa kelas warna: "' . $kelas . '"');

            $warna[] = $cocok[0];
        }

        // Bukan sekadar berwarna — harus BERBEDA-BEDA. Tujuh ikon yang
        // semuanya ungu sama tidak menolongnya dengan yang semuanya kelabu.
        $this->assertGreaterThanOrEqual(4, count(array_unique($warna)),
            'Warna ikon tabnya kurang beragam: ' . implode(', ', $warna));

        // Tab penghapus WAJIB merah, menyamai rupa bahayanya.
        $this->assertSame(1, preg_match('/<a[^>]*mis-tab-bahaya[^>]*>.*?<i class="fas ([^"]*)"/s',
            $strip['isi'], $hapus));

        $this->assertStringContainsString('mis-ikon-merah', $hapus[1],
            'Ikon tab Hapus harus merah, sewarna dengan rupa bahayanya.');
    }

    #[Test]
    public function tulisan_tab_dibungkus_unsur_sendiri_bukan_teks_telanjang(): void
    {
        /*
         * Di dalam flex, teks telanjang jadi "anonymous flex item" yang
         * min-width-nya auto dan TIDAK bisa disetel oleh pemilih CSS mana pun
         * — karena ia bukan unsur. Akibatnya ia menolak menyusut maupun
         * membungkus, lalu meluber keluar tabnya tanpa galat, tanpa
         * penggulung, dan tanpa satu tanda pun bahwa ada tulisan yang tidak
         * terbaca.
         *
         * Terukur sebelum dibungkus: huruf meluber 12px di 1024px dan 1280px,
         * 6px di 768px. Sesudah dibungkus <span>: nol di ketujuh lebar yang
         * diukur.
         *
         * Dijaga karena pembungkus ini TAMPAK tidak berguna — ia tidak
         * mengubah rupa apa pun saat ruangnya cukup, jadi ia persis jenis
         * markah yang dibuang orang berikutnya saat merapikan.
         */
        [$camp] = $this->buat('scopus_camp');

        $isi = $this->actingAs($this->akun(User::PERAN_ADMINISTRATOR))
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, preg_match('#<ul[^>]*id="rin-tab"(?<isi>.*?)</ul>#s', $isi, $strip));

        preg_match_all('#<a[^>]*class="nav-link[^"]*"[^>]*>(?<dalam>.*?)</a>#s', $strip['isi'], $tautan);

        $this->assertNotEmpty($tautan['dalam'], 'Tidak ada tautan tab yang ketemu.');

        foreach ($tautan['dalam'] as $dalam) {
            $this->assertStringContainsString('rin-tab-teks', $dalam,
                'Ada tulisan tab yang tidak dibungkus unsur sendiri, jadi ia tidak bisa '
                    . 'dibuat menyusut dan akan meluber keluar tabnya.');

            // Tidak boleh ada sisa teks di luar pembungkusnya: satu kata yang
            // tertinggal di luar sudah cukup untuk meluber.
            $sisa = trim(preg_replace('#<[^>]*>#', ' ', preg_replace(
                '#<span class="rin-tab-teks">.*?</span>#s', ' ', $dalam
            ) ?? '') ?? '');

            $this->assertSame('', $sisa,
                'Masih ada tulisan tab di luar pembungkusnya: "' . $sisa . '"');
        }
    }

    #[Test]
    public function kartu_identitas_tidak_menawarkan_tindakan_yang_sama_dua_kali(): void
    {
        /*
         * Kartu kiri sempat memuat deret tombol cepat (WhatsApp, Email, Salin
         * nomor) DI ATAS daftar keterangannya — sementara baris Email dan
         * WhatsApp di bawahnya juga sudah berupa tautan ke tempat yang sama.
         *
         * Dua tempat untuk satu hal membuat orang menduga keduanya berbeda,
         * dan pada layar yang dipakai orang awam itu mahal: ia akan mencoba
         * keduanya untuk memastikan. Terukur, kartunya 854px — 124px LEBIH
         * TINGGI daripada kartu di sebelahnya, sebagian karena pengulangan
         * itu. Sesudah tindakannya dipindahkan ke dalam barisnya
         * masing-masing: 730px, sama persis dengan kartu kanan.
         *
         * Dijaga dengan menghitung TAUTANNYA, bukan tulisannya: tombol yang
         * ditambahkan lagi dengan label berbeda tetap menunjuk alamat yang
         * sama.
         */
        [$camp] = $this->buat('scopus_camp');

        $camp->forceFill([
            'email' => 'uji.kartu@contoh.test',
            'telp' => '081234567890',
        ])->save();

        $isi = $this->actingAs($this->akun(User::PERAN_ADMINISTRATOR))
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, preg_match('#<section[^>]*rin-identitas[^>]*>(?<isi>.*?)</section>#s', $isi, $kartu),
            'Kartu identitas tidak ketemu.');

        $dalam = $kartu['isi'];

        $this->assertSame(1, preg_match_all('#href="https://wa\.me/#', $dalam),
            'Tautan WhatsApp muncul lebih dari sekali di kartu identitas.');

        $this->assertSame(1, preg_match_all('#href="mailto:#', $dalam),
            'Tautan email muncul lebih dari sekali di kartu identitas.');

        $this->assertSame(1, preg_match_all('#data-rin-salin=#', $dalam),
            'Tombol salin nomor muncul lebih dari sekali di kartu identitas.');
    }

    #[Test]
    public function label_dan_nilai_di_kartu_identitas_dibungkus_bersama(): void
    {
        /*
         * Barisnya berkisi TIGA lajur: medali, isi, tindakan.
         *
         * Label dan nilainya WAJIB berada dalam satu pembungkus. Tanpa itu,
         * penempatan otomatis kisi menaruh keduanya BERJAJAR di lajur 2 dan 3
         * — bukan bertumpuk. Terukur saat pembungkusnya belum ada: pada baris
         * Afiliasi, labelnya menempati lajur 215px dan nilainya lajur 24px di
         * sebelahnya.
         *
         * Jebakannya: dengan DUA lajur hal itu tidak terjadi, sebab isian
         * kedua jatuh sendiri ke baris berikutnya. Menambah lajur ketiga
         * diam-diam mengubah tata letaknya tanpa galat apa pun.
         */
        [$camp] = $this->buat('scopus_camp');

        $isi = $this->actingAs($this->akun(User::PERAN_ADMINISTRATOR))
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]))
            ->assertOk()
            ->getContent();

        preg_match_all('#<div class="rin-baris">(?<dalam>.*?)</div>\s*\n\s*(?=<div class="rin-baris">|</section>|<img)#s',
            $isi, $baris);

        $this->assertNotEmpty($baris['dalam'], 'Baris keterangan tidak ketemu.');

        foreach ($baris['dalam'] as $b) {
            $this->assertStringContainsString('rin-baris-isi', $b,
                'Ada baris yang label dan nilainya tidak dibungkus bersama, jadi keduanya akan '
                    . 'berjajar di lajur terpisah, bukan bertumpuk.');
        }
    }

    #[Test]
    public function teks_kartu_identitas_tidak_mewarisi_tinggi_baris_mutlak(): void
    {
        /*
         * style.css memasang `p, ul:not(.list-unstyled), ol { line-height:
         * 28px }` — nilai MUTLAK, bukan rasio, untuk tiap paragraf di seluruh
         * aplikasi.
         *
         * Pada label berhuruf 10,2px itu berarti kotaknya 28px: sekitar 9px
         * ruang kosong menggantung di bawah hurufnya, dan 7px lagi di atas
         * nilainya. Terukur, jarak antar KOTAKNYA 0 sementara jarak yang
         * TERLIHAT antara "Email" dan alamatnya ±16px — jadi mengatur `gap`
         * atau `margin` tidak pernah menolong, sebab ruang matinya ada DI
         * DALAM kotak masing-masing. Itu yang membuat keluhannya terbaca
         * sebagai "jaraknya terlalu lebar" padahal tidak ada jarak yang
         * disetel.
         *
         * Sesudah tinggi barisnya disebut sendiri: kotak label 28 -> 14px,
         * nilai 28 -> 19px, ruang mati 16 -> 5px, tinggi barisnya 77 -> 57px.
         *
         * Dijaga karena aturannya tampak berlebihan bagi yang tidak tahu
         * warisan itu ada — persis jenis baris yang dibuang orang berikutnya
         * saat merapikan.
         */
        $gaya = preg_replace('#/\*.*?\*/#s', ' ', file_get_contents(
            resource_path('views/account/pendaftaran_layanan/rincian.blade.php')
        )) ?? '';

        foreach (['.rin-baris-label', '.rin-baris-nilai'] as $kelas) {
            $this->assertSame(1, preg_match(
                '/' . preg_quote($kelas, '/') . '\s*\{[^}]*line-height:\s*[\d.]+\s*;/',
                $gaya
            ), $kelas . ' tidak menyebut line-height sendiri, jadi ia mewarisi 28px MUTLAK dari '
                . 'style.css dan menyisakan ruang mati di dalam kotaknya.');
        }

        // Rasio, BUKAN piksel: menyebutnya dalam px mengulangi kesalahan yang
        // sama pada ukuran huruf yang berbeda.
        $this->assertSame(0, preg_match(
            '/\.rin-baris-(label|nilai)\s*\{[^}]*line-height:\s*\d+px/',
            $gaya
        ), 'Tinggi barisnya disebut dalam piksel; pakai rasio supaya ia ikut ukuran hurufnya.');
    }

    #[Test]
    public function jarak_baris_pertama_tidak_bersandar_pada_first_of_type(): void
    {
        /*
         * `:first-of-type` menghitung tipe TAG, bukan kelas.
         *
         * Jarak lencana keadaan ke garis pembatas pertama dulu ditulis
         * `.rin-baris:first-of-type { margin-top: 15px }`. Aturan itu TIDAK
         * PERNAH cocok: <div> pertama di kartu ini `.rin-pil-baris`, jadi
         * tidak ada satu pun `.rin-baris` yang berstatus "div pertama".
         * Terukur akibatnya: jarak lencana ke garisnya 0 — garisnya menempel
         * persis di bawah lencananya.
         *
         * Diamnya sempurna. CSS yang pemilihnya tidak cocok tidak
         * memberitahu siapa pun, dan aturannya TERLIHAT benar saat dibaca.
         *
         * Yang dijaga: pemilihnya menyebut tetangganya langsung, jadi ia
         * tidak bergantung pada tag apa saja yang kebetulan ada di atasnya.
         */
        $gaya = preg_replace('#/\*.*?\*/#s', ' ', file_get_contents(
            resource_path('views/account/pendaftaran_layanan/rincian.blade.php')
        )) ?? '';

        $this->assertSame(0, preg_match('/\.rin-baris:first-of-type/', $gaya),
            '.rin-baris:first-of-type dipakai lagi. Pemilih itu tidak pernah cocok karena '
                . '<div> pertama di kartunya adalah .rin-pil-baris, jadi jaraknya diam-diam hilang.');

        $this->assertSame(1, preg_match('/\.rin-pil-baris\s*\+\s*\.rin-baris\s*\{[^}]*margin-top:/', $gaya),
            'Jarak lencana ke baris pertama tidak disetel lewat pemilih tetangga, '
                . 'jadi baris pertamanya akan menempel pada lencana di atasnya.');

        /*
         * Urutan markahnya ikut dijaga: pemilih tetangga di atas hanya
         * bekerja kalau .rin-baris memang datang TEPAT sesudah
         * .rin-pil-baris. Menyelipkan apa pun di antaranya mematikannya
         * tanpa galat.
         */
        [$camp] = $this->buat('scopus_camp');

        $isi = $this->actingAs($this->akun(User::PERAN_ADMINISTRATOR))
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]))
            ->assertOk()
            ->getContent();

        // Dicari lewat class="...", BUKAN nama kelasnya saja: nama itu muncul
        // lebih dulu di blok gaya halaman, dan potongan yang dimulai dari sana
        // tidak pernah memuat markahnya sama sekali.
        $mulai = strpos($isi, 'class="rin-pil-baris"');

        $this->assertNotFalse($mulai, 'Baris lencana keadaan tidak ketemu di markahnya.');

        /*
         * Diperiksa PERSIS pada celah antara penutup lencana dan baris
         * pertamanya, bukan dengan mencari pola "</div> lalu .rin-baris" di
         * sekitarnya. Percobaan pertama memakai pola itu dan LULUS walau
         * sebuah <hr> sudah diselipkan di tengah: polanya cocok pada
         * pasangan lain beberapa ratus huruf di bawahnya, yaitu penutup
         * baris pertama dan pembuka baris kedua.
         *
         * Lencananya tidak memuat <div> bersarang, jadi </div> pertama
         * sesudahnya memang penutupnya.
         */
        $tutup = strpos($isi, '</div>', $mulai);
        $barisAwal = strpos($isi, '<div class="rin-baris">', $mulai);

        $this->assertNotFalse($tutup);
        $this->assertNotFalse($barisAwal);

        $celah = substr($isi, $tutup + strlen('</div>'), $barisAwal - $tutup - strlen('</div>'));

        $this->assertSame('', trim($celah),
            'Ada unsur yang menyelip antara .rin-pil-baris dan baris pertamanya, jadi pemilih '
                . 'tetangganya tidak lagi cocok dan jaraknya hilang diam-diam. Yang menyelip: '
                . trim($celah));
    }

    #[Test]
    public function tiap_ubin_ringkasan_membawa_keterangannya_sendiri(): void
    {
        /*
         * Ketiga ubin angka di tab Ringkasan nyaris kosong: terukur lebarnya
         * 253px sementara isinya cuma 99px pada "Kode unik" dan 120px pada
         * "Jumlah orang" — lebih dari separuh ubin jadi petak putih.
         *
         * Yang mengisinya bukan hiasan, melainkan jawaban atas pertanyaan
         * yang memang menyusul angkanya: dibayar lewat apa, "22" itu apa, dan
         * 15 orangnya sudah tercatat namanya atau belum.
         *
         * Yang ketiga paling berharga: "baru 1 dari 15 nama" menyebut selisih
         * yang kalau tidak disebut baru ketahuan di hari acara, saat 14
         * sertifikat tidak bisa diterbitkan.
         */
        [$camp] = $this->buat('scopus_camp');

        $camp->forceFill(['jumlah_pendaftar' => 15])->save();

        $isi = $this->actingAs($this->akun(User::PERAN_ADMINISTRATOR))
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]))
            ->assertOk()
            ->getContent();

        // Tiap ubin wajib punya barisnya; dihitung dari jumlah ubinnya sendiri
        // supaya uji ini tetap benar kalau nanti ubinnya ditambah.
        $jumlahUbin = preg_match_all('#class="mis-ubin"#', $isi);
        $jumlahKet = preg_match_all('#class="rin-angka-ket#', $isi);

        $this->assertGreaterThanOrEqual(3, $jumlahUbin, 'Prasyarat ujinya hilang: ubinnya kurang dari tiga.');

        $this->assertSame($jumlahUbin, $jumlahKet,
            'Ada ubin ringkasan tanpa baris keterangan, jadi lebih dari separuh lebarnya '
                . 'kembali jadi petak kosong.');

        // Selisih nama yang belum tercatat WAJIB disebut, bukan didiamkan.
        $this->assertStringContainsString('dari 15 nama', $isi,
            'Selisih antara jumlah orang yang dibayar dan nama yang tercatat tidak disebut.');
    }

    #[Test]
    public function tab_ringkasan_hanya_berisi_kartu_setara(): void
    {
        /*
         * Iramanya pernah pecah: terukur, tiga blok teratas panel ini
         * mengambang tanpa kartu (deret ubin dan dua nota) sementara dua di
         * bawahnya berkartu — radius 0/14/14/15/15 dan celah 16/13/14/14.
         *
         * Tiap bagiannya sendiri sudah benar, dan justru itu yang membuatnya
         * sulit ditunjuk: yang salah bukan isinya melainkan tidak adanya
         * satuan yang seragam. Mata yang membaca sekilas tidak menemukan di
         * mana satu hal berakhir dan hal berikutnya mulai.
         *
         * Yang dijaga: SELURUH anak langsung panel Ringkasan berupa
         * .rin-bagian. Nota atau ubin yang ditambahkan lagi di luar kartu
         * akan mengulang pecahnya irama itu.
         */
        [$camp] = $this->buat('scopus_camp');

        // Catatannya diisi supaya kartu KETIGA ikut dirender: tanpa itu
        // panelnya cuma dua kartu, dan uji ini lolos tanpa pernah melihat
        // bagian yang paling mudah tertinggal saat markahnya disusun ulang.
        $camp->forceFill(['note' => 'Catatan uji tata letak.'])->save();

        $isi = $this->actingAs($this->akun(User::PERAN_ADMINISTRATOR))
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]))
            ->assertOk()
            ->getContent();

        /*
         * Diperiksa lewat DOM, BUKAN lekukan markahnya.
         *
         * Percobaan pertama mengenali anak panel dari jumlah spasi di awal
         * barisnya, dan itu salah: kartu "Jejak & catatan" ada di dalam @if
         * sehingga lekukannya 28, bukan 24 — jadi ia tidak pernah terhitung
         * dan ujinya merah pada markah yang sebenarnya benar. Lekukan sumber
         * tidak pernah jadi pernyataan tentang struktur.
         */
        $dom = new \DOMDocument();
        $sebelumnya = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $isi);
        libxml_clear_errors();
        libxml_use_internal_errors($sebelumnya);

        $panel = (new \DOMXPath($dom))->query('//*[@id="rin-panel-ringkasan"]')->item(0);

        $this->assertNotNull($panel, 'Panel Ringkasan tidak ketemu.');

        $kelasAnak = [];

        foreach ($panel->childNodes as $simpul) {
            if ($simpul->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $kelasAnak[] = $simpul->getAttribute('class');
        }

        $this->assertNotEmpty($kelasAnak, 'Tidak ada anak panel yang terbaca.');

        $bukanKartu = array_values(array_filter(
            $kelasAnak,
            fn ($k) => ! str_contains($k, 'rin-bagian')
        ));

        $this->assertSame([], $bukanKartu,
            'Ada isi tab Ringkasan yang tidak dibungkus kartu, jadi iramanya pecah: '
                . implode(' | ', $bukanKartu));

        $this->assertGreaterThanOrEqual(3, count($kelasAnak),
            'Prasyarat ujinya hilang: panel Ringkasan harusnya memuat tiga kartu.');
    }

    #[Test]
    public function tiap_isian_borang_berikon_berwarna(): void
    {
        /*
         * Kartu di tab Ringkasan penuh ikon berwarna, sementara borang di tab
         * lain polos sama sekali — dan kartunya sendiri IDENTIK: terukur
         * bantalan 15px 16px, radius 15px, bayangan, medali 30x30, dan
         * tipografi judul sama persis di keduanya. Yang membuatnya terasa
         * berbeda hanya isinya.
         *
         * Warnanya menyamai baris di kartu identitas sebelah kiri — email
         * biru, WhatsApp hijau, afiliasi ungu. Satu hal yang sama tidak boleh
         * berganti warna hanya karena dilihat di tab yang berbeda.
         *
         * Dijaga per-isian, bukan sekadar "ada ikon di panel": medan yang
         * ditambahkan nanti akan jatuh ke ikon netral, dan yang dituntut di
         * sini setiap isian PUNYA ikon, bukan sebagian.
         */
        [$camp] = $this->buat('scopus_camp');

        $isi = $this->actingAs($this->akun(User::PERAN_ADMINISTRATOR))
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]))
            ->assertOk()
            ->getContent();

        preg_match_all('#<label class="mis-label rin-label(?<kelas>[^"]*)"[^>]*>(?<dalam>.*?)</label>#s',
            $isi, $label, PREG_SET_ORDER);

        $this->assertGreaterThanOrEqual(4, count($label),
            'Prasyarat ujinya hilang: isian borangnya terlalu sedikit.');

        $tanpaIkon = [];

        foreach ($label as $l) {
            // Label yang sengaja disembunyikan tidak perlu ikon: ia memang
            // tidak dilihat siapa pun.
            if (str_contains($l['kelas'], 'sr-only')) {
                continue;
            }

            if (! preg_match('/mis-medali[^"]*mis-(hijau|biru|ungu|merah|kuning|jingga|abu)/', $l['dalam'])) {
                $tanpaIkon[] = trim(strip_tags($l['dalam']));
            }
        }

        $this->assertSame([], $tanpaIkon,
            'Ada isian borang tanpa ikon berwarna, jadi tabnya terbaca lebih polos daripada '
                . 'tab Ringkasan: ' . implode(', ', $tanpaIkon));
    }

    #[Test]
    public function label_yang_mengulang_judul_bagiannya_disembunyikan(): void
    {
        /*
         * Bagian "Catatan panitia" berisi satu isian yang labelnya berbunyi
         * sama persis dengan judul kartunya, berikut ikon kuning yang sama,
         * bertumpuk langsung. Satu hal disebut dua kali membuat orang mencari
         * bedanya — dan tidak ada.
         *
         * Disembunyikan dari MATA saja, bukan dibuang: isian tanpa label sama
         * sekali tidak bisa dikenali pembaca layar.
         */
        [$camp] = $this->buat('scopus_camp');

        $isi = $this->actingAs($this->akun(User::PERAN_ADMINISTRATOR))
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, preg_match(
            '#<label class="mis-label rin-label sr-only"[^>]*for="rin-note"[^>]*>(?<dalam>.*?)</label>#s',
            $isi, $cocok
        ), 'Label "Catatan panitia" tidak disembunyikan, jadi judulnya tercetak dua kali.');

        $this->assertStringContainsString('Catatan panitia', $cocok['dalam'],
            'Labelnya disembunyikan tetapi tulisannya ikut hilang; pembaca layar jadi '
                . 'menemui isian tanpa nama.');
    }

    #[Test]
    public function jarak_kartu_tidak_bersandar_pada_anak_pertama(): void
    {
        /*
         * Jarak antar kartu dulu dipasang di SETIAP kartu lalu dibatalkan
         * dengan `.rin-bagian:first-child`. Aturan itu tidak pernah mengenai
         * kartu pertama DI DALAM BORANG: anak pertama <form> adalah dua
         * <input type=hidden> yang dipasang direktif token CSRF dan direktif
         * metode, jadi kartunya bukan anak pertama.
         *
         * Margin 14px itu lolos, lalu RUNTUH menembus <form> yang tidak
         * berbantalan dan mendorong seluruh isinya. Terukur: jarak badan tab
         * ke kartu pertama 31px di tab Identitas melawan 17px di tab
         * Ringkasan — beda 14px, persis satu margin.
         *
         * Diamnya sempurna: tidak ada galat, dan aturannya TERLIHAT benar
         * saat dibaca. Jebakan yang sama dengan `:first-of-type` di kartu
         * identitas kiri — pemilih berdasar posisi dikalahkan saudara yang
         * tidak terlihat.
         */
        $gaya = preg_replace('#/\*.*?\*/#s', ' ', file_get_contents(
            resource_path('views/account/pendaftaran_layanan/rincian.blade.php')
        )) ?? '';

        $this->assertSame(0, preg_match('/\.rin-bagian:first-child/', $gaya),
            '.rin-bagian:first-child dipakai lagi. Ia tidak mengenai kartu pertama di dalam '
                . 'borang, sebab isian tersembunyi CSRF mendahuluinya — jaraknya jadi 14px '
                . 'lebih besar daripada tab lain.');

        $this->assertSame(1, preg_match('/\.rin-bagian\s*~\s*\.rin-bagian\s*\{[^}]*margin-top:/', $gaya),
            'Jarak antar kartu harus lewat pemilih saudara umum (~), supaya ia tetap benar '
                . 'walau ada isian tersembunyi atau nota menyelip.');
    }

    #[Test]
    public function komentar_di_gaya_tidak_menyebut_direktif_blade(): void
    {
        /*
         * Nama direktif Blade yang ditulis di dalam komentar CSS TETAP
         * dikompilasi: komentar `/* ... *\/` di dalam <style> bukan komentar
         * bagi Blade, melainkan teks biasa.
         *
         * Terjadi 4 Okt 2026 di berkas ini: sebuah komentar menyebut dua
         * direktif borang sebagai lambangnya, dan halamannya 500 dengan
         * "Undefined constant method_field". Galatnya menunjuk nomor baris
         * hasil kompilasi, bukan komentarnya.
         *
         * Yang aman hanya komentar Blade, sebab ia memang dibuang sebelum
         * kompilasi. Di komentar CSS, sebut direktifnya DENGAN KATA.
         */
        $isi = file_get_contents(resource_path('views/account/pendaftaran_layanan/rincian.blade.php'));

        $this->assertSame(1, preg_match('/<style>(?<gaya>.*?)<\/style>/s', $isi, $cocok),
            'Blok gaya tidak ketemu.');

        preg_match_all('#/\*(?<isi>.*?)\*/#s', $cocok['gaya'], $komentar);

        $tersangka = [];

        foreach ($komentar['isi'] as $k) {
            if (preg_match_all('/@[a-z]+/', $k, $d)) {
                $tersangka = array_merge($tersangka, $d[0]);
            }
        }

        $this->assertSame([], array_values(array_unique($tersangka)),
            'Ada nama direktif Blade di dalam komentar CSS; Blade tetap mengompilasinya dan '
                . 'halamannya bisa 500. Sebut dengan kata: ' . implode(', ', array_unique($tersangka)));
    }

    #[Test]
    public function nominal_berpemisah_tetap_tersimpan_sebagai_angka(): void
    {
        /*
         * Kotak nominal kini menampilkan "Rp" di dalamnya dan memberi pemisah
         * ribuan sambil diketik. Keduanya alat BACA — yang tersimpan harus
         * tetap angkanya saja.
         *
         * Ini pasangan wajib dari perubahan tampilannya: kalau peladen
         * berhenti membuang pemisahnya, "82.500.022" akan tersimpan jadi 82
         * dan selisihnya tidak akan ketahuan sampai ada yang mencocokkan
         * mutasi rekening.
         */
        [$camp] = $this->buat('scopus_camp');

        $this->actingAs($this->akun(User::PERAN_ADMINISTRATOR))->put(
            route('account.pendaftaran-layanan.ubah', ['scopus_camp', $camp->getKey()]),
            ['total_pembayaran' => 'Rp 82.500.022', 'ppn' => '1.000', 'nominal_diskon' => '450.000']
        )->assertRedirect();

        $segar = $camp->fresh();

        $this->assertSame(82500022, (int) $segar->total_pembayaran,
            'Pemisah ribuan ikut tersimpan; nominalnya jadi salah.');
        $this->assertSame(1000, (int) $segar->ppn);
        $this->assertSame(450000, (int) $segar->nominal_diskon);
    }

    #[Test]
    public function kotak_nominal_membawa_awalan_rupiah_di_dalamnya(): void
    {
        /*
         * Kepala berkas partial isian sudah MENJANJIKAN "Rp menempel di dalam
         * kotaknya" sejak lama, tetapi yang ada hanya kalimat bantuan "Dalam
         * rupiah" di bawah kotak — dan kalimat di bawah kotak tidak terbaca
         * saat mata sedang berada di dalam kotaknya.
         *
         * Dijaga per-kotak: medan nominal yang ditambahkan nanti harus ikut
         * membawanya, bukan sebagian saja.
         */
        [$camp] = $this->buat('scopus_camp');

        $isi = $this->actingAs($this->akun(User::PERAN_ADMINISTRATOR))
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $camp->getKey()]))
            ->assertOk()
            ->getContent();

        /*
         * Dicocokkan sebagai JENDELA sesudah pembungkusnya, bukan sampai
         * penutupnya.
         *
         * Dua percobaan sebelumnya meleset dan keduanya memulangkan nol kotak
         * pada markah yang justru benar: yang pertama menuntut <p> bantuan
         * sesudahnya — kalimat itu sudah dibuang; yang kedua menuntut dua
         * </span> berurutan — padahal ada <input> di antaranya.
         */
        $jumlah = preg_match_all('#<span class="rin-uang-kotak">(?<dalam>.{0,400})#s', $isi, $kotak, PREG_SET_ORDER);

        $this->assertGreaterThanOrEqual(4, $jumlah,
            'Kotak nominal berawalan Rp terlalu sedikit; ada medan uang yang terlewat.');

        foreach ($kotak as $k) {
            $this->assertStringContainsString('rin-uang-awalan', $k['dalam'],
                'Ada kotak nominal tanpa awalan Rp di dalamnya.');
            $this->assertStringContainsString('data-mis-rupiah', $k['dalam'],
                'Ada kotak nominal yang tidak ikut diberi pemisah ribuan.');
        }

        // Kalimat bantuan lama yang kini keliru tidak boleh kembali: titiknya
        // justru ditampilkan sekarang.
        $this->assertStringNotContainsString('Dalam rupiah, tanpa titik.', $isi,
            'Kalimat bantuan lama bertentangan dengan tampilannya sekarang.');
    }
}
