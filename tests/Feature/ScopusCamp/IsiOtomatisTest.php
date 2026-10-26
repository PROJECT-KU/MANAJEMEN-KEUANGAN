<?php

namespace Tests\Feature\ScopusCamp;

use App\CategoriesScopusCamp;
use App\PendaftaranScopusCamp;
use App\Support\IsiOtomatisDariNomor;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Borang Scopus Camp terisi sendiri dari nomor WhatsApp.
 *
 * Diminta pemiliknya 23 Okt 2026: yang pernah memesan cukup mengetik
 * nomornya, dan nama, email, serta asal instansinya terisi dari pesanan
 * terakhirnya. Sasaran layanan ini orang berusia lanjut, dan empat isian
 * yang harus diketik di ponsel adalah tempat paling banyak orang berhenti.
 *
 * Yang dijaga di sini bukan kemudahannya, melainkan hal-hal yang kalau rusak
 * TIDAK mengeluarkan galat apa pun:
 *
 *   - borang yang terisi nama ORANG LAIN;
 *   - isian yang sudah diketik orangnya ditimpa diam-diam;
 *   - alamat yang menukar nomor jadi nama dan email, tanpa batas laju,
 *     jadi bisa dipakai memanen keduanya;
 *   - nomor telepon tercatat UTUH di log peladen.
 */
class IsiOtomatisTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        /*
         * Borang Scopus Camp hanya digambar kalau gerbang pembayarannya
         * siap — sejak jalur unggah bukti dicabut 26 Okt 2026, pendaftaran
         * tanpa gerbang pasti ditolak, jadi borangnya pun tidak digambar.
         * Tanpa kunci ini, uji yang membuka borangnya menerima 302.
         */
        config()->set('services.doku.client_id', 'BRN-UJI-ISI');
        config()->set('services.doku.secret_key', 'SK-UJI-ISI');
    }

    private const BORANG = 'resources/views/public/scopus_camp/form_pendaftaran.blade.php';

    private function angkatan(): CategoriesScopusCamp
    {
        return CategoriesScopusCamp::create([
            'layanan' => 'scopus_camp',
            'token' => Str::random(30),
            'nama' => 'UJI Camp Isi ' . Str::random(6),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(12)->toDateString(),
            'lokasi' => 'Yogyakarta',
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'biaya' => 5500000, 'status' => 'active',
        ]);
    }

    /** Nomor yang pasti belum dipakai siapa pun di basis data ini. */
    private function nomorBaru(): string
    {
        return '08' . random_int(1000000000, 1999999999);
    }

    private function pesananScopusCamp(string $telp, array $ubah = []): PendaftaranScopusCamp
    {
        return PendaftaranScopusCamp::create(array_merge([
            'token' => Str::random(30),
            'id_transaksi' => 'CAMP-ISI-' . Str::random(8),
            'kategori_id' => $this->angkatan()->getKey(),
            'nama' => 'Budi Santoso, M.Pd.',
            'email' => 'budi' . Str::random(5) . '@contoh.test',
            'telp' => $telp,
            'affiliasi' => 'Universitas Gadjah Mada',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '5500777',
            'status' => 'diproses',
        ], $ubah));
    }

    private function tanya(string $telp)
    {
        return $this->postJson(route('public.scopuscamp.caripendaftar'), ['telp' => $telp]);
    }

    // ------------------------------------------------------- ketemu

    #[Test]
    public function nomor_yang_pernah_memesan_mengisi_nama_email_dan_instansi(): void
    {
        $telp = $this->nomorBaru();
        $p = $this->pesananScopusCamp($telp);

        $this->tanya($telp)
            ->assertOk()
            ->assertJson([
                'ditemukan' => true,
                'nama' => $p->nama,
                'email' => $p->email,
                'affiliasi' => $p->affiliasi,
            ]);
    }

    #[Test]
    public function bentuk_nomor_yang_berbeda_tetap_ketemu(): void
    {
        /*
         * Kolom telp diisi bertahun-tahun oleh layar yang berbeda: ada yang
         * "6281...", ada "0812...", ada "+62 812-...". Mencari satu bentuk
         * saja membuat sebagian orang tidak pernah ketemu — dan diamnya
         * terbaca seperti ia memang belum pernah memesan.
         */
        $angka = '0812' . random_int(10000000, 19999999);

        $this->pesananScopusCamp($angka);

        $enam = '62' . substr($angka, 1);

        foreach ([$angka, $enam, '+' . $enam, '0812-' . substr($angka, 4)] as $bentuk) {
            $this->tanya($bentuk)->assertOk()->assertJson(['ditemukan' => true]);
        }
    }

    #[Test]
    public function pesanan_dari_layanan_lain_juga_ketemu(): void
    {
        /*
         * Yang pernah ikut Scopus Kafe lalu mendaftar Scopus Camp adalah
         * ORANG YANG SAMA, dan datanya sudah ada. Mencari hanya di tabel
         * layanan yang sedang dibuka membuat sebagian besar pelanggan lama
         * tidak pernah ketemu.
         */
        $telp = $this->nomorBaru();

        DB::table('pendaftaran_scopus_kafe')->insert([
            'id' => (string) Str::uuid(),
            'nama' => 'Siti Aminah, S.Si.',
            'email' => 'siti' . Str::random(5) . '@contoh.test',
            'telp' => $telp,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $this->tanya($telp)
            ->assertOk()
            ->assertJson(['ditemukan' => true, 'nama' => 'Siti Aminah, S.Si.']);
    }

    #[Test]
    public function pesanan_terbaru_yang_dipakai(): void
    {
        /*
         * Orang pindah instansi dan berganti alamat surat. Pesanan lama
         * bukan keterangan yang ingin ia pakai hari ini — dan kalau yang
         * lama menang, ia harus menghapus isian yang terisi sendiri sebelum
         * bisa mengetik yang benar.
         */
        $telp = $this->nomorBaru();

        /*
         * Yang LAMA sengaja ditaruh di tabel yang DIDAHULUKAN daftar
         * sumbernya (Scopus Camp), dan yang BARU di tabel yang disebut
         * belakangan (Scopus Kafe).
         *
         * Dibalik, penjaga ini kosong: "yang pertama ketemu" kebetulan sama
         * dengan "yang terbaru", jadi pencarian yang berhenti di tabel
         * pertama pun tetap hijau. Sudah dibuktikan — mutasinya lolos
         * sebelum urutan ini dibalik.
         */
        $this->pesananScopusCamp($telp, [
            'nama' => 'Nama Lama',
            'created_at' => now()->subYears(3),
        ]);

        DB::table('pendaftaran_scopus_kafe')->insert([
            'id' => (string) Str::uuid(),
            'nama' => 'Nama Baru, M.Si.',
            'email' => 'baru' . Str::random(5) . '@contoh.test',
            'telp' => $telp,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->tanya($telp)
            ->assertOk()
            ->assertJson(['ditemukan' => true, 'nama' => 'Nama Baru, M.Si.']);
    }

    // ---------------------------------------------------- tidak ketemu

    #[Test]
    public function nomor_yang_belum_pernah_memesan_dijawab_tidak_ketemu(): void
    {
        $this->tanya($this->nomorBaru())
            ->assertOk()
            ->assertExactJson(['ditemukan' => false]);
    }

    #[Test]
    public function nomor_yang_tidak_masuk_akal_dijawab_sama_dengan_yang_tidak_ketemu(): void
    {
        /*
         * Jawaban yang BERBEDA untuk nomor yang tidak masuk akal dan nomor
         * yang memang belum pernah memesan memberi tahu pemanen mana nomor
         * yang benar-benar ada di sini.
         */
        $this->tanya('123')->assertOk()->assertExactJson(['ditemukan' => false]);
    }

    #[Test]
    public function nomor_yang_dipakai_dua_akun_tidak_ditebak(): void
    {
        /*
         * Nomor bersama tidak punya cara dipilih yang benar dari sini.
         * Didiamkan, bukan ditebak: borang yang terisi nama ORANG LAIN jauh
         * lebih buruk daripada borang yang tetap kosong — dan yang mengisinya
         * tidak punya alasan memeriksa isian yang "sudah benar".
         */
        $telp = $this->nomorBaru();

        foreach (['Akun Satu', 'Akun Dua'] as $i => $nama) {
            User::create([
                'uuid' => (string) Str::uuid(),
                'full_name' => $nama,
                'email' => 'akun' . $i . Str::random(6) . '@contoh.test',
                'username' => 'uji' . Str::random(8),
                'password' => bcrypt('rahasia-uji'),
                'telp' => $telp,
                'level' => 'customer',
            ]);
        }

        $this->tanya($telp)->assertOk()->assertExactJson(['ditemukan' => false]);
    }

    // -------------------------------------------------------- penjagaan

    #[Test]
    public function alamatnya_dibatasi_jumlah_permintaannya(): void
    {
        /*
         * Alamat ini MENUKAR NOMOR JADI NAMA DAN EMAIL, dan siapa pun bisa
         * memanggilnya. Tanpa batas laju ia bisa dipakai memanen keduanya
         * dengan mencoba nomor satu per satu, dan tidak ada satu pun layar
         * yang menyatakan sedang terjadi.
         */
        $rute = collect(app('router')->getRoutes())
            ->first(fn ($r) => $r->getName() === 'public.scopuscamp.caripendaftar');

        $this->assertNotNull($rute, 'Rutenya tidak ada.');

        $this->assertNotEmpty(
            array_filter($rute->gatherMiddleware(), fn ($m) => str_starts_with((string) $m, 'throttle:')),
            'Alamat pencari data pendaftar TIDAK dibatasi jumlah permintaannya.'
        );
    }

    #[Test]
    public function jatahnya_tidak_memakan_jatah_borang_pendaftaran(): void
    {
        /*
         * `throttle:10,1` tanpa nama TIDAK memberi rute ini jatahnya sendiri.
         *
         * Kuncinya dirakit Laravel dari DOMAIN dan ALAMAT IP saja — alamat
         * rutenya tidak ikut — jadi semua rute ber-throttle polos berbagi
         * satu penghitung per pengunjung. Akibatnya sepuluh kali memakai isi
         * otomatis menghabiskan jatah rute pendaftaran yang batasnya enam,
         * dan orang berikutnya di jaringan yang sama — satu kantor, satu
         * kampus, satu sambungan bersama — ditolak 429 saat menekan
         * "Daftar".
         *
         * Tertangkap sebagai uji pembatas laju Webinar Eksklusif yang merah
         * HANYA di rangkaian penuh dan hijau saat dijalankan sendiri. Dijaga
         * di sini supaya penyebabnya tidak perlu dicari dua kali.
         */
        $rute = collect(app('router')->getRoutes())
            ->first(fn ($r) => $r->getName() === 'public.scopuscamp.caripendaftar');

        $batas = array_values(array_filter(
            $rute->gatherMiddleware(),
            fn ($m) => str_starts_with((string) $m, 'throttle:')
        ));

        $this->assertCount(1, $batas, 'Rutenya tidak punya pembatas laju.');

        $nama = substr($batas[0], strlen('throttle:'));

        $this->assertFalse(
            (bool) preg_match('/^\d+,\d+$/', $nama),
            'Pembatasnya ditulis sebagai angka polos (' . $nama . '), jadi '
            . 'jatahnya BERBAGI dengan seluruh rute ber-throttle lain — '
            . 'termasuk borang pendaftaran. Pakai pembatas bernama.'
        );

        $this->assertNotNull(\Illuminate\Support\Facades\RateLimiter::limiter($nama),
            'Pembatas bernama "' . $nama . '" tidak terdaftar, jadi batasnya '
            . 'tidak berlaku sama sekali.');
    }

    #[Test]
    public function jawabannya_tidak_membawa_keterangan_lain(): void
    {
        /*
         * Tiap medan tambahan di jawaban ini jadi medan baru yang bisa
         * dipanen. Borangnya cuma butuh tiga; nomor pesanan, riwayat, dan
         * nominal tidak boleh ikut keluar.
         */
        $telp = $this->nomorBaru();
        $this->pesananScopusCamp($telp);

        $isi = $this->tanya($telp)->assertOk()->json();

        $this->assertSame(['ditemukan', 'nama', 'email', 'affiliasi'], array_keys($isi),
            'Jawabannya membawa medan di luar yang dibutuhkan borang.');
    }

    #[Test]
    public function nomornya_tidak_pernah_dicatat_utuh(): void
    {
        /*
         * Log peladen dibaca lebih banyak orang daripada basis datanya, dan
         * disalin ke tempat lain saat ada gangguan. Nomor utuh di sana
         * adalah daftar nomor pelanggan yang bocor tanpa ada yang menyadari.
         */
        $telp = '0813' . random_int(10000000, 19999999);
        $this->pesananScopusCamp($telp);

        Log::spy();

        $this->tanya($telp)->assertOk();

        Log::shouldHaveReceived('info')->withArgs(function ($pesan, $isi = []) use ($telp) {
            $angka = preg_replace('/\D+/', '', $telp);

            $this->assertStringNotContainsString(substr($angka, 1), (string) ($isi['nomor'] ?? ''),
                'Nomor telepon tercatat utuh di log.');

            $this->assertStringContainsString('***', (string) ($isi['nomor'] ?? ''),
                'Nomornya tidak ditandai tersamar.');

            return true;
        });
    }

    // ------------------------------------------------------------ borang

    #[Test]
    public function yang_sudah_masuk_akun_borangnya_sudah_terisi(): void
    {
        /*
         * Datanya sudah ada di sesi. Menunggu ia mengetik nomornya sendiri
         * dulu berarti meminta keterangan yang kita pegang.
         */
        $akun = User::create([
            'uuid' => (string) Str::uuid(),
            'full_name' => 'Rina Kusuma, S.T.',
            'email' => 'rina' . Str::random(6) . '@contoh.test',
            'username' => 'rina' . Str::random(8),
            'password' => bcrypt('rahasia-uji'),
            'telp' => $this->nomorBaru(),
            // 25 aksara, karena kolom `company` di tabel users memang
            // varchar(25) — nama instansi yang lebih panjang dipotong
            // basis data tanpa satu pun peringatan.
            'company' => 'Universitas Gadjah Mada',
            'level' => 'customer',
        ]);

        $isi = (string) $this->actingAs($akun)
            ->get(route('public.scopuscamp.formpendaftaran', ['id' => $this->angkatan()->getKey()]))
            ->assertOk()->getContent();

        foreach ([$akun->full_name, $akun->email, $akun->company] as $nilai) {
            $this->assertStringContainsString(e($nilai), $isi,
                'Borangnya tidak terisi untuk orang yang sudah masuk akun.');
        }
    }

    #[Test]
    public function nomor_whatsapp_jadi_isian_pertama(): void
    {
        /*
         * Nomor yang duduk di kotak TERAKHIR berarti orangnya sudah terlanjur
         * mengetik ketiga isian lain sebelum pengisian otomatisnya sempat
         * menolong — dan isian yang sudah terisi tidak pernah ditimpa. Jadi
         * fiturnya ada, jalan, dan tidak menolong siapa pun.
         */
        $isi = (string) $this->get(route('public.scopuscamp.formpendaftaran',
            ['id' => $this->angkatan()->getKey()]))->assertOk()->getContent();

        $urutan = [];

        foreach (['telp', 'nama', 'email', 'affiliasi'] as $medan) {
            $pos = strpos($isi, 'id="' . $medan . '"');
            $this->assertNotFalse($pos, 'Isian "' . $medan . '" tidak ada di borang.');
            $urutan[$medan] = $pos;
        }

        $this->assertSame('telp', array_key_first($urutan + []),
            'Urutan medan tidak terbaca.');

        foreach (['nama', 'email', 'affiliasi'] as $sesudah) {
            $this->assertLessThan($urutan[$sesudah], $urutan['telp'],
                'Nomor WhatsApp digambar SESUDAH isian "' . $sesudah . '".');
        }
    }

    #[Test]
    public function isian_yang_sudah_diketik_tidak_pernah_ditimpa(): void
    {
        /*
         * Orangnya mungkin sengaja memakai nama atau email yang berbeda kali
         * ini — misalnya mendaftarkan rekan kerja dari nomornya sendiri.
         * Menimpanya berarti ia mengirim pendaftaran atas nama orang lain
         * tanpa sadar.
         *
         * Dijaga di sumbernya: penjaga berbasis permintaan HTTP tidak
         * menjalankan JavaScript sama sekali.
         */
        $sumber = file_get_contents(base_path(self::BORANG));

        $this->assertStringContainsString("if (! e || ! data[k] || e.value.trim() !== '') {", $sumber,
            'Penjaga "jangan timpa isian yang sudah diisi" hilang dari skrip '
            . 'isi otomatis.');
    }

    #[Test]
    public function selalu_bisa_dibatalkan_dan_hanya_mengosongkan_yang_diisikan(): void
    {
        /*
         * Mengosongkan SEMUANYA akan membuang nama yang mungkin sudah
         * diketik orangnya sendiri sebelum nomornya diisi — tepat pada orang
         * yang menekan "Kosongkan" karena merasa isinya salah.
         */
        $sumber = file_get_contents(base_path(self::BORANG));

        $this->assertMatchesRegularExpression(
            '/function batalkan\(\)\s*\{.*?diisiSkrip\.forEach/s', $sumber,
            'Tombol "Kosongkan" tidak lagi bekerja dari daftar isian yang '
            . 'benar-benar diisikan skrip.');
    }

    #[Test]
    public function semua_layanan_ikut_ditengok(): void
    {
        /*
         * Daftar sumbernya ditulis sebagai data di satu tempat. Layanan yang
         * terlewat tidak mengeluarkan galat apa pun — pelanggannya cuma
         * tidak pernah ketemu, dan itu tidak bisa dibedakan dari "memang
         * belum pernah memesan".
         */
        $kelas = new \ReflectionClass(IsiOtomatisDariNomor::class);
        $sumber = $kelas->getConstant('SUMBER');

        $tabel = array_column($sumber, 'tabel');

        foreach ([
            'scopus_camp_pendaftaran',
            'webinar_eksklusif_pendaftaran',
            'analisis_bibliometrik',
            'clinikscopus_pemesanan',
            'pendaftaran_scopus_kafe',
        ] as $wajib) {
            $this->assertContains($wajib, $tabel,
                'Tabel ' . $wajib . ' tidak ikut ditengok; pelanggan lama '
                . 'layanan itu tidak akan pernah ketemu.');
        }
    }
}
