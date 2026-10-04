<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\AnalisisBibliometrik;
use App\ClinikScopusPemesanan;
use App\KategoriLayanan;
use App\PendaftaranScopusCamp;
use App\PendaftaranScopusKafe;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Layar Pendaftar Layanan: penyatuan lima tabel yang bentuknya berbeda.
 *
 * Yang dijaga di sini terutama hal-hal yang TIDAK menimbulkan galat saat
 * rusak: `UNION ALL` yang mencocokkan kolom menurut posisi sehingga nilainya
 * tertukar diam-diam, status yang jatuh ke keadaan yang salah, nomor telepon
 * bertanda hubung yang membuat pencarian mengembalikan nol hasil, dan layar
 * orang dalam yang terbuka untuk pelanggan.
 */
class LayarPendaftaranLayananTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Nama angkatan disimpan di ingatan selama satu permintaan. Di dalam
        // uji, "satu permintaan" berumur satu PROSES — jadi baris yang dibuat
        // uji ini tidak akan terlihat kalau petanya sudah terisi uji sebelumnya.
        Pendaftaran::lupakan();
    }

    private function akun(string $peran): User
    {
        $u = User::create([
            'full_name' => 'Uji ' . $peran,
            'username' => 'uji_pdl_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        // email_verified_at tidak ada di $fillable, jadi harus lewat
        // forceFill — tanpa itu middleware terverifikasi mengalihkan semuanya.
        $u->forceFill([
            'status' => 'active',
            'email_verified_at' => now(),
            'peran' => $peran,
        ])->save();

        return $u->refresh();
    }

    // -------------------------------------------------------------- hak akses

    #[Test]
    public function pelanggan_tidak_boleh_membuka_layar_ini(): void
    {
        /*
         * Grup rute account/ hanya bermiddleware auth + terverifikasi — tidak
         * ada middleware peran sama sekali di sana. Jadi penjagaannya HARUS di
         * pengendalinya, dan uji ini yang membuktikannya ada: isinya nama,
         * email, nomor telepon, dan nominal pembayaran ratusan orang.
         */
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        $this->actingAs($pelanggan)
            ->get(route('account.pendaftaran-layanan.index'))
            ->assertRedirect(route('account.dashboard.index'));
    }

    #[Test]
    public function pelanggan_tidak_boleh_mengunduh_berkasnya(): void
    {
        // Diperiksa terpisah dari layarnya: menutup halaman tanpa menutup
        // unduhannya meninggalkan seluruh datanya tetap bisa diambil.
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        foreach (['pdf', 'excel'] as $jalur) {
            $this->actingAs($pelanggan)
                ->get(route('account.pendaftaran-layanan.' . $jalur))
                ->assertRedirect(route('account.dashboard.index'));
        }
    }

    #[Test]
    public function karyawan_dan_administrator_boleh_membukanya(): void
    {
        foreach ([User::PERAN_KARYAWAN, User::PERAN_ADMINISTRATOR] as $peran) {
            $orang = $this->akun($peran);

            $this->actingAs($orang)
                ->get(route('account.pendaftaran-layanan.index'))
                ->assertOk();

            // Sesi permintaan pertama masih memegang pengguna sebelumnya;
            // tanpa ini permintaan berikutnya tertolak ke /login tanpa pesan.
            $this->flushSession();
        }
    }

    // ------------------------------------------------- penyatuan lima tabel

    #[Test]
    public function kelima_layanan_muncul_di_kueri_gabungan(): void
    {
        /*
         * Jumlah baris gabungan harus SAMA dengan jumlah kelima tabelnya.
         *
         * Ini yang menangkap `UNION` yang tertulis tanpa `ALL` — ia membuang
         * baris kembar, dan dua pendaftaran yang isinya kebetulan sama persis
         * akan hilang satu tanpa galat apa pun.
         */
        $terpisah = 0;

        foreach ([
            'scopus_camp_pendaftaran',
            'analisis_bibliometrik',
            'webinar_eksklusif_pendaftaran',
            'pendaftaran_scopus_kafe',
            'clinikscopus_pemesanan',
        ] as $tabel) {
            $terpisah += DB::table($tabel)->count();
        }

        $this->assertSame($terpisah, Pendaftaran::kueri()->count());

        // Dan tiap kunci layanan yang keluar dari kueri memang ada di
        // katalognya — kunci yang tidak ada di katalog akan tampil di layar
        // sebagai nama mentah tanpa ikon, tanpa warna, dan tanpa tautan.
        $kunci = Pendaftaran::kueri()->distinct()->pluck('layanan')->sort()->values()->all();

        $this->assertCount(5, $kunci, 'Kelima layanan harus punya barisnya di basis data uji.');

        foreach ($kunci as $k) {
            $this->assertArrayHasKey($k, Pendaftaran::katalog(),
                "Kunci layanan '{$k}' keluar dari kueri tetapi tidak ada di katalog.");
        }
    }

    #[Test]
    public function kolomnya_tidak_tertukar_antar_cabang_union(): void
    {
        /*
         * Penjaga untuk jebakan yang paling tidak bersuara di fitur ini:
         * PEMETAAN kolom per layanan. Lima tabel menamai hal yang sama dengan
         * lima nama berbeda — nama pemesan ada di `nama` di tiga tabel dan di
         * `nama_pemesan` di Clinik Scopus, yang juga punya `afiliasi_pemesan`
         * di sebelahnya. Satu pemetaan yang salah tunjuk tidak menimbulkan
         * galat apa pun; afiliasi orangnya cuma akan tampil di kolom nama, dan
         * yang menemukannya adalah panitia yang membaca layarnya.
         *
         * Jadi satu baris ditanam di tiap tabel dengan nilai yang khas, lalu
         * dibaca kembali lewat kueri gabungannya. Terbukti menangkap: menunjuk
         * `nama_orang` Clinik ke `afiliasi_pemesan` membuat uji ini merah.
         *
         * Urutan daftar KOLOM-nya sendiri TIDAK perlu dijaga di sini — sudah
         * dicoba ditukar dan hasilnya identik, sebab alias tiap kolom dirakit
         * dari butir daftar yang sama.
         */
        $tanda = Str::random(10);
        $angkatan = $this->angkatan();
        $orangLain = $this->akun(User::PERAN_KARYAWAN);

        $dibuat = [
            'scopus_camp' => PendaftaranScopusCamp::create([
                'id_transaksi' => 'NOMOR-' . $tanda,
                'kategori_id' => $angkatan->id,
                'nama' => 'Nama ' . $tanda,
                'email' => 'surel-' . $tanda . '@contoh.test',
                'telp' => '0811-1111-1111',
                'affiliasi' => 'Afiliasi ' . $tanda,
                'jumlah_pendaftar' => '4',
                'total_pembayaran' => '1234567',
                'kode_unik' => '777',
                'status' => 'diproses',
            ]),
            'bibliometrik' => AnalisisBibliometrik::create([
                'id_transaksi' => 'NOMOR-B-' . $tanda,
                'kategori_id' => $angkatan->id,
                'nama' => 'Nama B ' . $tanda,
                'email' => 'surel-b-' . $tanda . '@contoh.test',
                'telp' => '0822-2222-2222',
                'jumlah_pendaftar' => '1',
                'total_pembayaran' => '555000',
                'status' => 'Pendaftaran Diterima',
            ]),
            'scopus_kafe' => PendaftaranScopusKafe::create([
                'id_pemesanan' => 'K-' . substr($tanda, 0, 6),
                'nama' => 'Nama K ' . $tanda,
                'email' => 'surel-k-' . $tanda . '@contoh.test',
                'telp' => '0833-3333-3333',
                'sesi' => 'Sesi uji ' . $tanda,
                'total_keseluruhan_pembayaran' => '250000',
                'kode_unik_pembayaran' => '321',
                'status' => 'menunggu verifikasi',
            ]),
            'clinik_scopus' => ClinikScopusPemesanan::create([
                // clinikscopus_id NOT NULL tanpa nilai bawaan, jadi barisnya
                // harus menunjuk sesi yang benar ada.
                // Tiga kolom NOT NULL tanpa nilai bawaan: sesinya, trainernya,
                // dan pelanggannya. Barisnya tidak bisa dibuat tanpa ketiganya.
                'clinikscopus_id' => $this->sesiClinik(),
                'trainer_id' => $orangLain->id,
                'customer_id' => $orangLain->id,
                'id_transaksi' => 'NOMOR-C-' . $tanda,
                'kode_booking' => 'BOOK-' . $tanda,
                'nama_pemesan' => 'Nama C ' . $tanda,
                'email_pemesan' => 'surel-c-' . $tanda . '@contoh.test',
                'telp_pemesan' => '0844-4444-4444',
                'afiliasi_pemesan' => 'Afiliasi C ' . $tanda,
                'sesi' => 'Sesi 2',
                'jam_sesi' => '10.00 - 11.00 WIB',
                'total_pembayaran' => 99000,
                'kode_unik' => 123,
                'status' => 'completed',
            ]),
        ];

        $harapan = [
            'scopus_camp' => ['NOMOR-' . $tanda, 'Nama ' . $tanda, 'surel-' . $tanda . '@contoh.test', '0811-1111-1111', 4, 1234567],
            'bibliometrik' => ['NOMOR-B-' . $tanda, 'Nama B ' . $tanda, 'surel-b-' . $tanda . '@contoh.test', '0822-2222-2222', 1, 555000],
            'scopus_kafe' => ['K-' . substr($tanda, 0, 6), 'Nama K ' . $tanda, 'surel-k-' . $tanda . '@contoh.test', '0833-3333-3333', 1, 250000],
            'clinik_scopus' => ['NOMOR-C-' . $tanda, 'Nama C ' . $tanda, 'surel-c-' . $tanda . '@contoh.test', '0844-4444-4444', 1, 99000],
        ];

        foreach ($harapan as $layanan => [$nomor, $nama, $email, $telp, $jumlah, $total]) {
            $baris = Pendaftaran::kueri()
                ->where('layanan', $layanan)
                ->where('id', $dibuat[$layanan]->id)
                ->first();

            $this->assertNotNull($baris, "Baris {$layanan} tidak ditemukan di kueri gabungan.");
            $this->assertSame($nomor, $baris->nomor, "Kolom nomor {$layanan} tertukar.");
            $this->assertSame($nama, $baris->nama_orang, "Kolom nama {$layanan} tertukar.");
            $this->assertSame($email, $baris->email, "Kolom email {$layanan} tertukar.");
            $this->assertSame($telp, $baris->telp, "Kolom telp {$layanan} tertukar.");
            $this->assertSame($jumlah, (int) $baris->jumlah, "Kolom jumlah {$layanan} tertukar.");
            $this->assertSame($total, (int) $baris->total, "Kolom total {$layanan} tertukar.");
        }

        // Scopus Kafe dan Clinik Scopus tidak punya kolom jumlah pendaftar;
        // keduanya HARUS jadi 1, bukan NULL — kalau NULL, penjumlahan "berapa
        // orang" melewatkan seluruh baris kedua layanan itu.
        $this->assertSame(1, (int) Pendaftaran::kueri()
            ->where('id', $dibuat['scopus_kafe']->id)->value('jumlah'));
    }

    #[Test]
    public function sesi_terbaca_dari_angkatan_maupun_dari_teks_sesinya(): void
    {
        /*
         * Dua bentuk yang berbeda sama sekali, dan keduanya harus terbaca:
         * tiga layanan menunjuk baris kategori_layanan, dua layanan menyimpan
         * nama sesinya sebagai teks di barisnya sendiri. Kolom sesi yang cuma
         * menengok kategori_layanan akan kosong untuk sembilan baris Scopus
         * Kafe dan dua baris Clinik Scopus.
         */
        $angkatan = $this->angkatan();
        $tanda = Str::random(8);

        $camp = PendaftaranScopusCamp::create([
            'id_transaksi' => 'S-' . $tanda,
            'kategori_id' => $angkatan->id,
            'nama' => 'Peserta ' . $tanda,
            'email' => $tanda . '@contoh.test',
            'telp' => '0811-0000-0001',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '10000',
            'status' => 'diproses',
        ]);

        $kafe = PendaftaranScopusKafe::create([
            'id_pemesanan' => 'KF-' . substr($tanda, 0, 5),
            'nama' => 'Pemesan ' . $tanda,
            'email' => 'kf' . $tanda . '@contoh.test',
            'telp' => '0811-0000-0002',
            'sesi' => 'Ngopi bareng ' . $tanda,
            'total_keseluruhan_pembayaran' => '20000',
            'status' => 'menunggu verifikasi',
        ]);

        Pendaftaran::lupakan();

        $barisCamp = Pendaftaran::kueri()->where('id', $camp->id)->first();
        $barisKafe = Pendaftaran::kueri()->where('id', $kafe->id)->first();

        $this->assertSame($angkatan->nama, Pendaftaran::sesiBaris($barisCamp));
        $this->assertSame('Ngopi bareng ' . $tanda, Pendaftaran::sesiBaris($barisKafe));
    }

    // ----------------------------------------------------- pemetaan keadaan

    #[Test]
    public function setiap_status_yang_benar_benar_tersimpan_punya_keadaan(): void
    {
        /*
         * Uji ini membaca nilai status yang BENAR-BENAR ada di basis data,
         * bukan daftar yang ditulis di kode. Itu bedanya: kolomnya varchar
         * bebas di kelima tabel, jadi borang mana pun bisa menuliskan nilai
         * baru kapan saja tanpa migrasi — dan nilai yang belum terdaftar akan
         * jatuh ke keadaan 'lain' tanpa ada yang tahu.
         *
         * Kalau uji ini merah, yang benar BUKAN melonggarkan ujinya melainkan
         * menambahkan nilai barunya ke KEADAAN.
         */
        $status = Pendaftaran::kueri()->distinct()->pluck('status');

        $this->assertNotEmpty($status, 'Basis data uji tidak punya satu pun pendaftaran.');

        $tanpaKeadaan = $status
            ->filter(fn ($s) => Pendaftaran::keadaanDari($s) === 'lain')
            ->values()
            ->all();

        $this->assertSame([], $tanpaKeadaan, 'Nilai status ini belum punya keadaan: '
            . implode(', ', array_map(fn ($s) => "'{$s}'", $tanpaKeadaan)));
    }

    #[Test]
    public function keadaan_dicocokkan_tanpa_peduli_huruf_besar_dan_spasi(): void
    {
        // Tabel yang sama memuat 'Pendaftaran Diterima' sementara borang lain
        // bisa menulisnya dengan huruf kecil atau spasi ganda; ketiganya satu
        // keadaan yang sama persis.
        $this->assertSame('lunas', Pendaftaran::keadaanDari('Pendaftaran Diterima'));
        $this->assertSame('lunas', Pendaftaran::keadaanDari('pendaftaran  diterima'));
        $this->assertSame('lunas', Pendaftaran::keadaanDari('  PENDAFTARAN DITERIMA  '));
        $this->assertSame('menunggu', Pendaftaran::keadaanDari('pending'));
        $this->assertSame('tidak_jadi', Pendaftaran::keadaanDari('expired'));
        $this->assertSame('lain', Pendaftaran::keadaanDari('status yang belum pernah ada'));
        $this->assertSame('lain', Pendaftaran::keadaanDari(null));
    }

    #[Test]
    public function status_yang_belum_dikenali_tetap_punya_barisnya_sendiri(): void
    {
        /*
         * Aturan "status baru wajib punya barisnya sendiri".
         *
         * Baris berstatus asing TIDAK boleh hilang dari semua saringan — kalau
         * ia tidak bisa ditemukan lewat satu pun saringan, tidak ada satu
         * tombol pun yang bisa menyentuhnya, dan uangnya menggantung tanpa
         * ada yang tahu.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);

        $aneh = PendaftaranScopusCamp::create([
            'id_transaksi' => 'ANEH-' . $tanda,
            'kategori_id' => $this->angkatan()->id,
            'nama' => 'Status Asing ' . $tanda,
            'email' => $tanda . '@contoh.test',
            'telp' => '0811-0000-0003',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '30000',
            'status' => 'menunggu tanda tangan rektor',
        ]);

        $this->assertSame('lain', Pendaftaran::keadaanDari($aneh->status));

        // Ada di saringan 'lain' ...
        $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['keadaan' => 'lain', 'cari' => $tanda]))
            ->assertOk()
            ->assertSee('ANEH-' . $tanda);

        $this->flushSession();

        // ... dan TIDAK ikut muncul di keadaan mana pun yang lain.
        foreach (array_keys(Pendaftaran::KEADAAN) as $keadaan) {
            $this->actingAs($orang)
                ->get(route('account.pendaftaran-layanan.index', ['keadaan' => $keadaan, 'cari' => $tanda]))
                ->assertOk()
                ->assertDontSee('ANEH-' . $tanda);

            $this->flushSession();
        }
    }

    // ------------------------------------------------------------- saringan

    #[Test]
    public function saringan_layanan_hanya_menyisakan_layanan_itu(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        foreach (array_keys(Pendaftaran::katalog()) as $layanan) {
            $diharapkan = Pendaftaran::kueri()->where('layanan', $layanan)->count();

            $halaman = $this->actingAs($orang)
                ->get(route('account.pendaftaran-layanan.index', ['layanan' => $layanan]));

            $halaman->assertOk();

            // Diperiksa dari angka ringkasannya, bukan dari baris yang tampil:
            // halaman pertama cuma memuat dua puluh baris.
            $halaman->assertSee(number_format($diharapkan, 0, ',', '.'), false);

            $this->flushSession();
        }
    }

    #[Test]
    public function saringan_keadaan_hanya_mengembalikan_status_milik_keadaan_itu(): void
    {
        foreach (Pendaftaran::KEADAAN as $nama => $k) {
            $status = Pendaftaran::kueri()
                ->whereIn('status', $k['nilai'])
                ->distinct()
                ->pluck('status');

            foreach ($status as $s) {
                $this->assertSame($nama, Pendaftaran::keadaanDari($s),
                    "Status '{$s}' tersaring sebagai '{$nama}' padahal keadaannya bukan itu.");
            }
        }
    }

    #[Test]
    public function nomor_telepon_bertanda_hubung_tetap_ketemu_tanpa_tanda_hubung(): void
    {
        /*
         * Penjaga untuk kegagalan yang paling sering dialami panitia: nomor
         * tersimpan bertanda hubung ('0822-2090-6000') sementara yang disalin
         * orang dari WhatsApp tidak ('082220906000'). Tanpa pembersihan di
         * KEDUA sisi, pencariannya mengembalikan nol hasil — dan orangnya
         * menyimpulkan pendaftarannya tidak ada.
         *
         * Diuji dengan empat bentuk penulisan nomor yang sama, termasuk bentuk
         * kode negara, karena keempatnya memang beredar.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);
        $nomorAcak = '08' . mt_rand(10, 99) . '-' . mt_rand(1000, 9999) . '-' . mt_rand(1000, 9999);
        $angka = preg_replace('/\D+/', '', $nomorAcak);

        PendaftaranScopusCamp::create([
            'id_transaksi' => 'TELP-' . $tanda,
            'kategori_id' => $this->angkatan()->id,
            'nama' => 'Pemilik Nomor ' . $tanda,
            'email' => $tanda . '@contoh.test',
            'telp' => $nomorAcak,
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '40000',
            'status' => 'diproses',
        ]);

        $bentuk = [
            $nomorAcak,                          // apa adanya, bertanda hubung
            $angka,                              // tanpa tanda baca
            '+62' . substr($angka, 1),           // kode negara bertanda tambah
            '62' . substr($angka, 1),            // kode negara tanpa tanda tambah
        ];

        foreach ($bentuk as $dicari) {
            $this->actingAs($orang)
                ->get(route('account.pendaftaran-layanan.index', ['cari' => $dicari]))
                ->assertOk()
                ->assertSee('TELP-' . $tanda);

            $this->flushSession();
        }
    }

    #[Test]
    public function saringan_bukti_memisahkan_yang_sudah_dan_belum_mengunggah(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $tanda = Str::random(8);
        $angkatan = $this->angkatan();

        $dengan = PendaftaranScopusCamp::create([
            'id_transaksi' => 'ADA-' . $tanda,
            'kategori_id' => $angkatan->id,
            'nama' => 'Berbukti ' . $tanda,
            'email' => 'a' . $tanda . '@contoh.test',
            'telp' => '0811-0000-0004',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '50000',
            'gambar' => 'ScopusCamp/berkas-yang-tidak-ada-' . $tanda . '.jpg',
            'status' => 'diproses',
        ]);

        $tanpa = PendaftaranScopusCamp::create([
            'id_transaksi' => 'NIHIL-' . $tanda,
            'kategori_id' => $angkatan->id,
            'nama' => 'Tanpa bukti ' . $tanda,
            'email' => 'b' . $tanda . '@contoh.test',
            'telp' => '0811-0000-0005',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '60000',
            'status' => 'diproses',
        ]);

        $halaman = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['bukti' => 'ada', 'cari' => $tanda]));
        $halaman->assertOk()->assertSee('ADA-' . $tanda)->assertDontSee('NIHIL-' . $tanda);

        $this->flushSession();

        $halaman = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['bukti' => 'belum', 'cari' => $tanda]));
        $halaman->assertOk()->assertSee('NIHIL-' . $tanda)->assertDontSee('ADA-' . $tanda);
    }

    #[Test]
    public function bukti_yang_berkasnya_hilang_dibedakan_dari_yang_belum_diunggah(): void
    {
        /*
         * Terukur: 72 dari 183 nilai bukti di basis data menunjuk berkas yang
         * sudah tidak ada di cakram. Menyebutnya "ada" membuat panitia menekan
         * tautan yang pasti gagal lalu mengira layarnya yang rusak; menyebutnya
         * "belum ada" menyembunyikan bahwa orangnya SUDAH mengunggah dan
         * buktinyalah yang lenyap.
         */
        $angkatan = $this->angkatan();
        $tanda = Str::random(8);

        $hilang = PendaftaranScopusCamp::create([
            'id_transaksi' => 'HILANG-' . $tanda,
            'kategori_id' => $angkatan->id,
            'nama' => 'Bukti hilang ' . $tanda,
            'email' => $tanda . '@contoh.test',
            'telp' => '0811-0000-0006',
            'jumlah_pendaftar' => '1',
            'total_pembayaran' => '70000',
            'gambar' => 'ScopusCamp/pasti-tidak-ada-' . $tanda . '.jpg',
            'status' => 'diproses',
        ]);

        $baris = Pendaftaran::kueri()->where('id', $hilang->id)->first();
        $bukti = Pendaftaran::buktiBaris($baris);

        $this->assertTrue($bukti['nilai'], 'Kolomnya terisi, jadi nilai harus true.');
        $this->assertFalse($bukti['ada'], 'Berkasnya tidak ada di cakram, jadi ada harus false.');

        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        /*
         * Diperiksa dari KELAS PENANDANYA dan dari tulisannya sekaligus.
         *
         * Kelasnya yang menjamin keping itu memang keadaan "berkas hilang" dan
         * bukan salah satu dari dua keadaan bukti lainnya; tulisannya yang
         * menjamin panitia benar-benar bisa membacanya. Memeriksa tulisan saja
         * membuat ujinya merah tiap kali kalimatnya dirapikan — sudah terjadi
         * saat kepingnya diubah dari lencana jadi teks berikon.
         */
        $halaman = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['cari' => $tanda]));

        $halaman->assertOk();
        $halaman->assertSee('pdl-bukti-hilang', false);
        $halaman->assertSee('berkas hilang');
    }

    // ------------------------------------------------------------- urutan

    #[Test]
    public function penomoran_halaman_tidak_menampilkan_baris_yang_sama_dua_kali(): void
    {
        /*
         * Diurutkan menurut status, kolom yang nilainya berulang ratusan kali
         * — 91 baris Bibliometrik berstatus sama persis. Tanpa pengurut kedua
         * yang pasti unik, basis data boleh menyusunnya berbeda tiap
         * permintaan, dan baris yang sama bisa muncul di dua halaman sekaligus
         * sementara baris lain tidak muncul sama sekali.
         *
         * Dihitung dari id-nya, bukan dari tampilannya: yang dijaga keutuhan
         * penomoran halamannya.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);
        $semua = Pendaftaran::kueri()->count();
        $perHalaman = 20;
        $halaman = (int) ceil($semua / $perHalaman);

        $this->assertGreaterThan(1, $halaman, 'Perlu lebih dari satu halaman untuk menguji ini.');

        $terlihat = [];

        for ($h = 1; $h <= $halaman; $h++) {
            $id = Pendaftaran::kueri()
                ->orderByRaw('status asc')
                ->orderBy('p.id')
                ->forPage($h, $perHalaman)
                ->pluck('id')
                ->all();

            $terlihat = array_merge($terlihat, $id);
        }

        $this->assertSame($semua, count($terlihat));
        $this->assertSame($semua, count(array_unique($terlihat)),
            'Ada baris yang muncul di lebih dari satu halaman.');

        /*
         * Dan pengendalinya memang memakai pengurut kedua itu.
         *
         * Diperiksa dari sumbernya, bukan dari keluaran halamannya, dan itu
         * disengaja: gejala cacatnya adalah urutan yang BOLEH berbeda antar
         * permintaan, bukan yang pasti berbeda. MySQL kebetulan sering
         * mengembalikan urutan yang sama untuk kueri yang sama, jadi uji lewat
         * HTTP bisa hijau berkali-kali di atas kode yang rusak — penjaga yang
         * hijau karena kebetulan lebih buruk daripada tidak ada penjaga.
         *
         * Yang diperiksa pernyataan yang PERSIS dimaksud, bukan kemiripan
         * tekstual: `->orderBy('p.id')` harus muncul dua kali — sekali untuk
         * daftarnya, sekali untuk kedua unduhannya yang memakai urutan sama.
         */
        $sumber = file_get_contents(app_path('Http/Controllers/account/PendaftaranLayananController.php'));

        $this->assertSame(2, substr_count($sumber, "->orderBy('p.id')"),
            'Pengendalinya harus memakai pengurut kedua yang unik di daftar dan di ekspor, '
            . 'kalau tidak baris yang sama bisa muncul di dua halaman.');
    }

    // ------------------------------------------------------------- unduhan

    #[Test]
    public function unduhan_pdf_terbit_sebagai_pdf(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $jawab = $this->actingAs($orang)->get(route('account.pendaftaran-layanan.pdf'));

        $jawab->assertOk();
        $jawab->assertHeader('Content-Type', 'application/pdf');

        // Diperiksa dari isinya, bukan cuma dari kepalanya: Dompdf yang gagal
        // merender tetap bisa mengembalikan 200 berisi untaian kosong.
        $this->assertStringStartsWith('%PDF-', $jawab->getContent());
    }

    #[Test]
    public function unduhan_excel_terbit_sebagai_berkas(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $jawab = $this->actingAs($orang)->get(route('account.pendaftaran-layanan.excel'));

        $jawab->assertOk();
        $this->assertStringContainsString('attachment', (string) $jawab->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.xlsx', (string) $jawab->headers->get('Content-Disposition'));
    }

    #[Test]
    public function unduhan_membawa_saringan_yang_sedang_dipakai(): void
    {
        /*
         * Berkas berisi sebagian baris tidak boleh terbaca seperti daftar yang
         * lengkap — dan berkas unduhan justru yang paling sering diteruskan ke
         * orang lain, terlepas dari layar tempat ia diunduh. Jadi nama
         * saringannya WAJIB tercetak di dalamnya.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $jawab = $this->actingAs($orang)->get(route('account.pendaftaran-layanan.pdf', [
            'layanan' => 'scopus_camp',
            'keadaan' => 'lunas',
        ]));

        $jawab->assertOk();

        // Isi PDF-nya terkompresi, jadi yang bisa diperiksa dari sini
        // keberhasilannya; isi teksnya diperiksa lewat templatnya di bawah.
        $this->assertStringStartsWith('%PDF-', $jawab->getContent());

        $html = view('account.pendaftaran_layanan.ekspor-pdf', [
            'baris' => collect(),
            'saringan' => ['Layanan' => 'Scopus Camp', 'Keadaan' => 'Lunas'],
            'katalog' => Pendaftaran::katalog(),
        ])->render();

        $this->assertStringContainsString('Layanan: Scopus Camp', $html);
        $this->assertStringContainsString('Keadaan: Lunas', $html);
        $this->assertStringNotContainsString('Tanpa saringan', $html);
    }

    // ---------------------------------------------------------------- menu

    #[Test]
    public function entri_menunya_ada_di_layar_lain(): void
    {
        /*
         * Diperiksa dari HALAMAN LAIN, bukan dari halamannya sendiri: halaman
         * itu memuat alamatnya sendiri di formulir dan tautan saringannya,
         * jadi ujinya akan tetap hijau walau entri menunya dibuang.
         *
         * Tanpa entri menu, layar ini hanya bisa dibuka dengan mengetik
         * alamatnya — dan praktis tidak bisa ditemukan siapa pun.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($orang)
            ->get(route('account.galeri.index'))
            ->assertOk()
            ->assertSee(route('account.pendaftaran-layanan.index'), false)
            ->assertSee('Pendaftar Layanan');
    }

    // ------------------------------------------------------------ pembantu

    /**
     * Satu sesi Clinik Scopus yang benar ada, untuk ditunjuk baris uji.
     *
     * Kolom clinikscopus_id NOT NULL dan tanpa nilai bawaan, jadi baris uji
     * tidak bisa dibuat tanpa menunjuk sesi yang sungguhan.
     */
    private function sesiClinik(): string
    {
        $ada = DB::table('clinikscopus')->value('id');

        $this->assertNotNull($ada, 'Basis data uji tidak punya satu pun sesi Clinik Scopus.');

        return (string) $ada;
    }

    /**
     * Tawaran alumni dibaca dari varian angkatan yang terpilih, jadi hanya
     * benar kalau pilihan angkatannya sudah diisi lebih dulu.
     *
     * Yang diperiksa urutan PEMANGGILANNYA di segarkan(), bukan letak
     * markahnya: tawarannya kini tinggal di fungsi tawarkanAlumni() sendiri,
     * yang juga dipanggil saat angkatannya berganti.
     *
     * Terbalik, lookup-nya memakai varian kosong dan tawarannya tidak pernah
     * muncul — tanpa galat apa pun di peramban. Terukur: tarif menyetel 15%,
     * kotak centangnya tetap tersembunyi. Penjaga ini membaca urutan di
     * sumbernya karena bug-nya hidup di JS, bukan di jawaban peladen.
     */
    public function test_tawaran_alumni_dihitung_sesudah_angkatan_diisi(): void
    {
        $sumber = file_get_contents(
            resource_path('views/account/pendaftaran_layanan/baru.blade.php')
        );

        $segarkan = strpos($sumber, 'var segarkan = function');
        $this->assertNotFalse($segarkan, 'Fungsi segarkan() tidak ditemukan.');

        $isiAngkatan = strpos($sumber, 'isiAngkatan(pilih.nilai)', $segarkan);
        $tawaran = strpos($sumber, 'tawarkanAlumni(pilih)', $segarkan);

        $this->assertNotFalse($isiAngkatan, 'segarkan() tidak mengisi pilihan angkatan.');
        $this->assertNotFalse($tawaran, 'segarkan() tidak menawarkan pilihan alumni.');

        $this->assertLessThan(
            $tawaran,
            $isiAngkatan,
            'Tawaran alumni dihitung sebelum pilihan angkatan diisi, '
                . 'jadi variannya masih kosong dan potongannya tidak pernah ditemukan.'
        );
    }

    /**
     * Borang pendaftaran tidak membatasi lebarnya sendiri.
     *
     * Patokan rumahnya `.mis-badan > .section { max-width: 1600px }` di
     * mis-ui.css, dan SEMUA layar memakainya. Borang ini sempat dibatasi
     * 1.000px: hasilnya satu-satunya layar yang tidak penuh — terbaca seperti
     * layar yang belum jadi, bukan seperti layar yang rapi.
     *
     * Yang menahan isian merentang terlalu jauh bukan lebar halamannya
     * melainkan lebar ISIANNYA (.bar-penuh/.bar-lebar dibatasi 520px) dan
     * kolom ringkasan di kanan yang memakai sisa lebarnya.
     */
    #[Test]
    public function borang_pendaftaran_tidak_memangkas_lebarnya_sendiri(): void
    {
        $sumber = file_get_contents(
            resource_path('views/account/pendaftaran_layanan/baru.blade.php')
        );

        // Hanya bagian gayanya; kalimat penjelas di komentar Blade boleh
        // menyebut angkanya.
        preg_match('/<style>(.*?)<\/style>/s', $sumber, $m);
        $this->assertNotEmpty($m, 'Blok gaya borangnya tidak ditemukan.');

        $gaya = preg_replace('#/\*.*?\*/#s', '', $m[1]);

        /*
         * Yang dicari: aturan apa pun yang menyebut .bar-wadah DAN memasang
         * max-width di badannya. Memeriksa per blok, bukan per berkas — kelas
         * lain di borang ini memang boleh dan perlu punya max-width.
         */
        preg_match_all('/([^{}]*\.bar-wadah[^{}]*)\{([^}]*)\}/', (string) $gaya, $blok, PREG_SET_ORDER);

        foreach ($blok as $b) {
            $this->assertStringNotContainsString(
                'max-width',
                $b[2],
                'Borang pendaftaran memangkas lebarnya sendiri lewat "' . trim($b[1])
                    . '". Patokan rumahnya 1600px di mis-ui.css, dan layar yang '
                    . 'berhenti lebih awal jadi satu-satunya yang tidak penuh.'
            );
        }
    }

    /**
     * Berganti angkatan tidak boleh merakit ulang pilihannya.
     *
     * isiAngkatan() mengosongkan menunya lalu mengisinya lagi, dan merakit
     * ulang berarti pilihannya kembali ke angkatan PERTAMA. Terukur di
     * peramban: memilih "Angkatan ke-202 · 30 Okt 2026" langsung melompat
     * kembali ke ke-198.
     *
     * Yang membuatnya berbahaya, harga kelima angkatan itu sama persis — jadi
     * tidak ada satu pun angka di layar yang berubah, dan pendaftarnya masuk
     * angkatan serta tanggal yang salah tanpa tanda apa pun.
     */
    #[Test]
    public function berganti_angkatan_tidak_merakit_ulang_pilihannya(): void
    {
        $sumber = file_get_contents(
            resource_path('views/account/pendaftaran_layanan/baru.blade.php')
        );

        $awal = strpos($sumber, "e.target === menuAngkatan");
        $this->assertNotFalse($awal, 'Cabang penangan ganti angkatan tidak ditemukan.');

        // Sampai cabang berikutnya; cabang lain memang boleh memanggil segarkan().
        $akhir = strpos($sumber, '} else {', $awal);
        $this->assertNotFalse($akhir, 'Ujung cabangnya tidak ditemukan.');

        $cabang = substr($sumber, $awal, $akhir - $awal);
        $cabang = preg_replace('#/\*.*?\*/|//[^\n]*#s', '', $cabang);

        $this->assertStringNotContainsString(
            'segarkan()',
            (string) $cabang,
            'Cabang ganti angkatan memanggil segarkan(), yang merakit ulang '
                . 'pilihannya — pilihan panitia melompat kembali ke angkatan pertama.'
        );
    }

    /**
     * Pilihan angkatan membawa nomor dan tanggalnya.
     *
     * Terukur di basis data: lima angkatan Scopus Camp yang akan datang
     * bernama "Scopus Camp Yogyakarta" SEMUA. Tanpa nomor dan tanggal, kelima
     * pilihannya identik huruf per huruf dan panitia memilih secara
     * untung-untungan — padahal tanggal pelaksanaannya berbeda-beda.
     */
    #[Test]
    public function pilihan_angkatan_membawa_nomor_dan_tanggalnya(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Scopus Camp Uji Nomor ' . Str::random(5),
            'nama_ke' => '202',
            'mulai' => '2026-12-30',
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'status' => 'active',
        ]);

        $jawab = $this->actingAs($orang)->get(route('account.pendaftaran-layanan.baru'));

        $jawab->assertOk();

        /*
         * Muatannya @json, jadi yang diperiksa isi muatannya — bukan tulisan
         * di layar, yang dirakit JS dari muatan ini.
         */
        $jawab->assertSee('"nomor":"202"', false);

        // Nama bulan Indonesia, bukan "Dec": APP_LOCALE=en, jadi ini hanya
        // keluar kalau tanggalnya memang dilewatkan Carbon ->locale('id').
        $jawab->assertSee('"tanggal":"30 Des 2026"', false);

        $angkatan->forceFill(['status' => 'nonactive'])->save();
    }

    /**
     * Borang menyisakan ruang untuk bilah menu bawah ponsel.
     *
     * Bilah itu melayang (`position: fixed`, 20px dari dasar, tinggi 75px) dan
     * halamannya tidak punya bantalan bawah sama sekali — jadi isian terakhir
     * berakhir DI BAWAHNYA dan tidak bisa disentuh. Terukur di 390x844 pada
     * tata letak ponsel.
     *
     * Dijaga dari sumbernya sebab kegagalannya tidak terlihat dari layar lebar
     * mana pun: di sana bilahnya tidak ada, jadi seluruh pengukuran meja
     * melewatkannya begitu saja.
     */
    #[Test]
    public function borang_menyisakan_ruang_untuk_bilah_menu_ponsel(): void
    {
        $sumber = file_get_contents(
            resource_path('views/account/pendaftaran_layanan/baru.blade.php')
        );

        preg_match('/<style>(.*?)<\/style>/s', $sumber, $m);
        $this->assertNotEmpty($m, 'Blok gaya borangnya tidak ditemukan.');

        $gaya = preg_replace('#/\*.*?\*/#s', '', $m[1]);

        preg_match('/body\.is-mobile\s+\.mis-badan\s*\{([^}]*)\}/', (string) $gaya, $blok);

        $this->assertNotEmpty(
            $blok,
            'Borang tidak menyisakan ruang untuk bilah menu bawah ponsel; '
                . 'isian terakhirnya akan berakhir di bawahnya.'
        );

        preg_match('/padding-bottom\s*:\s*(\d+)px/', $blok[1], $angka);

        $this->assertNotEmpty($angka, 'Ruangnya harus berupa padding-bottom.');

        // Bilahnya 75px ditambah 20px jarak dari dasar; kurang dari itu tetap
        // menutupi.
        $this->assertGreaterThanOrEqual(
            95,
            (int) $angka[1],
            'Ruangnya kurang dari tinggi bilah menu (75px) plus jaraknya (20px).'
        );
    }

    /**
     * Ringkasan biaya TIDAK menempel di layar sempit.
     *
     * Terukur di 390x844: kartunya setinggi 247px — hampir sepertiga layar —
     * dan menempel di dasar berarti sepertiga layar itu tertutup selamanya.
     * Ia bahkan menimpa bilah menu bawah, sehingga tombol Simpan tertutup
     * separuh.
     */
    #[Test]
    public function ringkasan_biaya_tidak_menempel_di_layar_sempit(): void
    {
        $sumber = file_get_contents(
            resource_path('views/account/pendaftaran_layanan/baru.blade.php')
        );

        preg_match('/<style>(.*?)<\/style>/s', $sumber, $m);
        $gaya = preg_replace('#/\*.*?\*/#s', '', $m[1]);

        /*
         * Aturan menempelnya harus dipagari batas BAWAH, bukan hanya batas
         * atas: tanpa min-width ia berlaku sampai layar tersempit.
         */
        preg_match_all(
            '/@media\s*\(min-width:\s*(\d+)px\)[^{]*\{\s*\.bar-samping\s*\{([^}]*position:\s*sticky[^}]*)\}/s',
            (string) $gaya,
            $cocok,
            PREG_SET_ORDER
        );

        $this->assertNotEmpty(
            $cocok,
            'Aturan "menempel" pada .bar-samping harus dipagari @media (min-width: …); '
                . 'tanpa itu ia berlaku juga di ponsel dan menutupi sepertiga layar.'
        );

        foreach ($cocok as $c) {
            $this->assertGreaterThanOrEqual(
                768,
                (int) $c[1],
                'Batas bawahnya minimal 768px; di bawah itu layarnya terlalu sempit untuk ditutupi.'
            );
        }
    }

    /**
     * Borang bercabang sejak pertanyaan PERTAMA.
     *
     * Dulu "pesanan lembaga?" ada di langkah terakhir, padahal jawabannya
     * menentukan isi langkah-langkah sebelumnya — jumlah orang, nama peserta
     * rombongan, potongan alumni, afiliasi, dan seluruh identitas lembaga.
     * Admin mengisi semuanya dulu, baru diberi tahu sebagian tadi tidak
     * relevan.
     */
    #[Test]
    public function borang_menanyakan_untuk_siapa_di_langkah_pertama(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $jawab = $this->actingAs($orang)->get(route('account.pendaftaran-layanan.baru'));

        $jawab->assertOk();
        $jawab->assertSee('Untuk siapa?');

        // Kartunya tentang SIAPA YANG MEMBAYAR, bukan berapa orangnya:
        // rombongan bisa dibayar sendiri maupun oleh lembaganya.
        $jawab->assertSee('Perorangan');
        $jawab->assertSee('Lembaga / instansi');

        $isi = $jawab->getContent();

        // Pertanyaan itu harus datang SEBELUM pilihan layanannya.
        $this->assertLessThan(
            strpos($isi, 'Layanan apa?'),
            strpos($isi, 'Untuk siapa?'),
            '"Untuk siapa" harus ditanya lebih dulu; jawabannya menentukan sisanya.'
        );

        // Lima langkah, bukan enam: blok lembaganya dilebur ke langkah
        // "siapa yang mendaftar", bukan jadi kartu tersendiri.
        preg_match_all('/class="bar-nomor"[^>]*>(\d+)</', $isi, $nomor);
        $this->assertSame(['1', '2', '3', '4', '5'], $nomor[1]);
    }

    /**
     * Isian yang jarang dipakai dilipat, dan dilipat dalam keadaan TERTUTUP.
     *
     * Terukur: 29 nama isian di borang ini. Isian yang jarang diisi tetapi
     * selalu terlihat menambah beban baca di setiap pendaftaran, bukan hanya
     * di yang membutuhkannya.
     */
    #[Test]
    public function isian_yang_jarang_dipakai_tertutup_saat_borang_dibuka(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.baru'))
            ->assertOk()
            ->getContent();

        foreach ([
            'bar-blok-lembaga' => 'blok lembaga',
            'bar-bungkus-catatan' => 'catatan panitia',
            'bar-bungkus-peserta' => 'nama peserta rombongan',
        ] as $id => $sebutan) {
            $this->assertMatchesRegularExpression(
                '/id="' . $id . '"[^>]*\shidden/',
                $isi,
                'Isian ' . $sebutan . ' harus tertutup saat borang dibuka.'
            );
        }

        // Sakelarnya sendiri harus ada — kalau tidak, isiannya tertutup
        // selamanya dan tidak bisa dipakai siapa pun.
        foreach (['bar-pakai-potongan', 'bar-pakai-catatan', 'bar-perlu-faktur', 'bar-pic-beda'] as $sakelar) {
            $this->assertStringContainsString('id="' . $sakelar . '"', $isi);
        }
    }

    /**
     * Saringan yang jarang dipakai dilipat — tetapi tidak pernah jadi
     * saringan siluman.
     *
     * Delapan kendali sekaligus adalah delapan hal yang harus dibaca sebelum
     * satu nama ditemukan, dan yang memakai layar ini bukan orang yang
     * terbiasa dengan borang. Lima di antaranya dilipat sehingga yang tersisa
     * cuma Cari, Layanan, dan Keadaan.
     *
     * Yang dijaga justru kebalikannya: saringan terlipat yang SEDANG
     * TERPASANG harus terbuka sendiri. Kalau tidak, daftarnya terlihat kurang
     * isinya tanpa ada satu pun tanda yang menjelaskan kenapa — dan itu
     * berakhir dengan panitia menyimpulkan datanya hilang.
     */
    #[Test]
    public function saringan_jarang_pakai_dilipat_tetapi_terbuka_saat_terpasang(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $tertutup = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index'))
            ->assertOk()->getContent();

        $this->assertStringContainsString('id="pdl-lain-tuas"', $tertutup);
        $this->assertMatchesRegularExpression('/id="pdl-lain"[^>]*\shidden/', $tertutup);

        // Ketiga kendali yang tersisa tetap di muka; melipat semuanya berarti
        // mencari satu nama menuntut membuka lipatan lebih dulu.
        foreach (['pdl-cari', 'pdl-layanan', 'pdl-keadaan'] as $tetap) {
            $this->assertStringContainsString('id="' . $tetap . '"', $tertutup);
        }

        $terbuka = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['bukti' => 'belum']))
            ->assertOk()->getContent();

        $this->assertDoesNotMatchRegularExpression('/id="pdl-lain"[^>]*\shidden/', $terbuka);

        /*
         * Jumlahnya disebut di tuasnya. Dua saringan terpasang harus terbaca
         * "2", bukan sekadar "ada yang aktif" — panitia yang melihat hasil
         * sedikit perlu tahu berapa banyak yang harus dilepas.
         */
        $dua = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.index', ['bukti' => 'belum', 'bayar' => 'transfer']))
            ->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/id="pdl-lain-tuas".*?mis-pil mis-pil-ungu">\s*2\s*</s',
            $dua
        );
    }

    /**
     * Sesi Scopus Kafe dipilih dari daftar, bukan diketik sendiri.
     *
     * Scopus Kafe berjalan pada jam tetap dan jamnya berbeda menurut varian:
     * offline dua sesi, online hanya sesi pagi. Kotak teks bebas membuat satu
     * sesi yang sama tersimpan dalam beberapa ejaan — terukur 9 baris
     * tersimpan dengan "sesi 1", "sesi 2", dan "sesi 3" — sehingga daftar
     * hadir per sesi tidak bisa dikelompokkan sama sekali.
     *
     * Yang dijaga di sini bukan rupa kartunya, melainkan bahwa jamnya sampai
     * ke halaman: dirakit di peramban dari daftar yang dikirim peladen, jadi
     * daftar yang tidak terkirim berarti kotak sesi yang kosong selamanya.
     */
    #[Test]
    public function sesi_scopus_kafe_dipilih_dari_daftar_bukan_diketik(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.baru'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="bar-sesi-kartu"', $isi);

        // Kotak teks bebasnya memang DIBUANG, bukan sekadar disembunyikan:
        // isian tersembunyi tetap terkirim, dan nilainya akan menimpa pilihan
        // kartunya.
        $this->assertStringNotContainsString('id="bar-sesi"', $isi);

        /*
         * Jam kedua sesinya ada di halaman. Dicocokkan ke sumbernya, bukan
         * ditulis ulang di sini — kalau tidak, ujinya tetap hijau justru saat
         * jamnya diubah di satu tempat dan tidak di tempat lain.
         */
        foreach (Pendaftaran::sesiSemua()['scopus_kafe'] as $varian => $daftar) {
            foreach ($daftar as $sesi) {
                $this->assertStringContainsString($sesi['mulai'], $isi);
                $this->assertStringContainsString($sesi['selesai'], $isi);
            }
        }

        // Online memang hanya punya satu sesi; offline dua. Kalau ini
        // terbalik, pendaftar online dijanjikan jam yang tidak dibuka.
        $this->assertCount(1, Pendaftaran::sesiPilihan('scopus_kafe', 'online'));
        $this->assertCount(2, Pendaftaran::sesiPilihan('scopus_kafe', 'offline'));
    }

    /**
     * Daftar peserta rombongan bisa diambil dari berkas, bukan hanya diketik.
     *
     * Lembaga mengirim daftar pesertanya sebagai lampiran Excel. Tanpa jalur
     * berkas, rombongan 30 orang berarti 30 baris yang disalin satu per satu
     * — pekerjaan yang paling mungkin dilewati panitia, dan begitu dilewati,
     * 29 nomor pesertanya tidak pernah tercatat.
     *
     * Yang dijaga: tombolnya ada, ia menunjuk titik masuk yang benar, dan
     * kalimat ajakannya menyebut BERKAS APA. "Unggah berkas" saja membuat
     * panitia menebak-nebak dan akhirnya tidak memakainya sama sekali.
     */
    #[Test]
    public function daftar_peserta_bisa_diambil_dari_berkas_excel(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.baru'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="bar-peserta-berkas"', $isi);
        $this->assertStringContainsString('Ambil dari Excel atau CSV', $isi);
        $this->assertStringContainsString(route('account.pendaftaran-layanan.baca-peserta'), $isi);

        /*
         * Nomornya diminta, dan contohnya ditunjukkan di tempat ia diketik.
         * Label "Nama peserta lain" tanpa contoh membuat panitia mengetik
         * nama saja — persis keadaan yang hendak diperbaiki.
         */
        $this->assertStringContainsString('Nama &amp; nomor peserta lain', $isi);
        $this->assertMatchesRegularExpression('/placeholder="[^"]*081234567890/', $isi);

        /*
         * Peringatan kelebihan nama ada, dan tertutup saat borang dibuka.
         *
         * Peladen memang memotong daftarnya sampai sebanyak kursi yang
         * dibayar — tetapi memotong diam-diam berarti nama yang hilang baru
         * ketahuan di hari acara, dan sejak daftarnya bisa datang dari berkas
         * lembaga berisi puluhan baris, itu berhenti jadi kemungkinan yang
         * jauh.
         */
        $this->assertMatchesRegularExpression('/id="bar-peserta-lebih"[^>]*\shidden/', $isi);
    }

    /**
     * Menu "gabung ke pesanan" tidak ditawarkan kalau belum ada pesanannya.
     *
     * Admin yang baru memilih jalur lembaga disuguhi menu berisi SATU pilihan
     * bertuliskan "Bukan pesanan lembaga" — menyangkal pilihan yang baru saja
     * ia buat, dan tidak ada yang bisa dipilih di sana.
     */
    #[Test]
    public function menu_gabung_pesanan_disembunyikan_kalau_belum_ada_pesanannya(): void
    {
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        // Semua pesanan yang terbuka ditutup dulu, sebatas transaksi uji ini.
        \App\PemesananLembaga::query()->update(['status' => \App\PemesananLembaga::SELESAI]);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.baru'))
            ->assertOk()
            ->getContent();

        /*
         * Diperiksa dari pembungkus TEPAT SEBELUM labelnya, bukan dengan pola
         * tag utuh: Blade menyisipkan spasi di sekitar atribut bersyarat, jadi
         * pola yang mengeja tagnya persis akan meleset tanpa ada yang salah
         * pada markahnya.
         */
        $sebelum = substr($isi, 0, strpos($isi, 'for="bar-pemesanan"'));
        $pembungkus = substr($sebelum, strrpos($sebelum, '<div'));

        $this->assertStringContainsString(
            'hidden',
            $pembungkus,
            'Menunya harus tertutup saat tidak ada pesanan yang bisa dipilih.'
        );

        // Dan kalimatnya tidak lagi menyangkal pilihan yang baru dibuat.
        $this->assertStringNotContainsString('Bukan pesanan lembaga', $isi);
    }

    #[Test]
    public function nomor_surat_pesanan_disebut_dalam_bahasa_yang_dipahami(): void
    {
        /*
         * "PO" singkatan Inggris dari purchase order, dan penggunanya bukan
         * orang pengadaan — ditanya "Nomor PO", mereka harus menebak apa yang
         * diminta. Disebut "surat pesanan", yang memang istilah dipakai
         * lembaga saat menerbitkannya.
         */
        $orang = $this->akun(User::PERAN_ADMINISTRATOR);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.baru'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Nomor surat pesanan', $isi);
        $this->assertStringNotContainsString('Nomor PO', $isi);
    }

    /** Satu angkatan untuk ditunjuk baris uji; dipakai ulang kalau sudah ada. */
    private function angkatan(): KategoriLayanan
    {
        $ada = KategoriLayanan::where('layanan', 'scopus_camp')->first();

        if ($ada !== null) {
            return $ada;
        }

        return KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Angkatan Uji ' . Str::random(6),
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50',
            'sisa_kuota' => '50',
            'status' => 'active',
        ]);
    }
}
