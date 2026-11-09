<?php

namespace Tests\Feature\ScopusKafe;

use App\ClinikScopusBiayaPersesi;
use App\PendaftaranScopusKafe;
use App\Services\Doku;
use App\Support\PendaftaranSemuaLayanan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Borang Scopus Kafe: isi otomatis, dua jalurnya.
 *
 * Yang dijanjikan pemiliknya: "jika sudah login ATAU nomornya pernah buat
 * pemesanan maka data langsung terisi otomatis". Dua jalan, dan keduanya
 * gagal DENGAN DIAM kalau rusak — borangnya tetap 200, isiannya cuma kosong,
 * dan dari layar "belum pernah memesan" tidak bisa dibedakan dari "jalurnya
 * rusak". Itu sebabnya dijaga di sini, bukan diandalkan terlihat.
 *
 * ---------------------------------------------------------------------------
 * Yang terukur rusak 9 Nov 2026, dan sebabnya tidak satu pun melempar galat
 * ---------------------------------------------------------------------------
 *
 *   1. JALUR SUDAH LOGIN TIDAK PERNAH MENGISI NOMOR. Borangnya membaca
 *      `$akun->phone`, dan kolom itu tidak ada di tabel users — namanya
 *      `telp`. Terukur NULL untuk seluruh 145 akun yang punya nomor. Blade
 *      memulangkan null untuk properti yang tidak ada, jadi `?? ''` selalu
 *      menang: tidak ada galat, cuma isian pertama yang selalu kosong. Dan
 *      isian pertama itu satu-satunya yang mencari sisanya.
 *
 *   2. NAMA AKUN MENANG ATAS NAMA PESANAN. Nama di akun sering sekadar
 *      "admin" atau "staff"; nama di pesanan ditulis dalam bentuk yang
 *      memang diminta borang ini.
 *
 *   3. TEMPEL DARI PAPAN KLIP TIDAK MEMICU APA PUN. Hanya 'input' yang
 *      didengarkan, dan sebagian peramban tidak melepasnya untuk tempel
 *      maupun untuk isian otomatisnya sendiri. Terukur: nol permintaan.
 *
 *   4. 429 SAMA SEKALI TIDAK DIKATAKAN. Jatahnya 4 per menit per IP, dan
 *      mengetik satu nomor dengan ragu melepas enam permintaan. Yang ditolak
 *      menyisakan borang kosong, pita kabar kosong, konsol tanpa galat.
 *
 * Yang 3 dan 4 hidup di JavaScript, jadi yang dijaga di sini keberadaan
 * aturannya di dalam skrip yang terkirim — bukan perilakunya, yang diukur
 * lewat peramban sungguhan. Uji ini tidak bisa menjalankan JavaScript;
 * yang bisa ia lakukan adalah menolak penghapusan aturan itu tanpa sengaja.
 */
class IsiOtomatisBorangTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Tanpa gerbang siap, borangnya memantulkan ke halaman daftar dan
        // tidak digambar sama sekali — jadi tidak ada yang bisa diperiksa.
        config()->set('services.doku.client_id', 'BRN-UJI-KAFE');
        config()->set('services.doku.secret_key', 'SK-UJI-KAFE');

        $this->sesiAktif();
    }

    /** Satu sesi aktif, supaya borangnya digambar. */
    private function sesiAktif(): void
    {
        ClinikScopusBiayaPersesi::create([
            'layanan' => 'scopus_kafe',
            'varian' => 'offline',
            'biaya_persesi' => '1.000.000',
            'status' => ClinikScopusBiayaPersesi::AKTIF,
            'sesi' => [['nama' => 'Sesi Uji ' . Str::upper(Str::random(4)), 'mulai' => '08:00', 'selesai' => '13:00']],
        ]);

        PendaftaranSemuaLayanan::lupakanSesi();
        ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();
    }

    private function borang(): string
    {
        $r = $this->get(route('public.scopuskafe.formpendaftaran'));
        $r->assertOk();

        return (string) $r->getContent();
    }

    /** Nilai atribut `value` dari satu isian, atau null kalau tidak ada. */
    private function nilai(string $isi, string $id): ?string
    {
        if (! preg_match('/<input[^>]*\bid="' . preg_quote($id, '/') . '"[^>]*>/s', $isi, $tag)) {
            return null;
        }

        return preg_match('/\bvalue="([^"]*)"/', $tag[0], $m) ? html_entity_decode($m[1]) : null;
    }

    /** Isi mentah berkas skrip borangnya. */
    private function skrip(): string
    {
        return (string) file_get_contents(
            resource_path('views/public/scopus_kafe/partials/skrip-borang.blade.php')
        );
    }

    /**
     * Badan satu fungsi di dalam skrip borangnya.
     *
     * Dipotong dari kurung kurawal pembuka sampai pasangannya, dihitung
     * bersarang — bukan sampai '}' pertama, yang akan berhenti di tengah
     * blok if mana pun di dalamnya.
     */
    private function fungsiSkrip(string $nama): string
    {
        $skrip = $this->skrip();
        $awal = strpos($skrip, 'function ' . $nama . '(');

        $this->assertNotFalse($awal, "Fungsi {$nama}() tidak ada lagi di skrip borang.");

        $buka = strpos($skrip, '{', $awal);
        $dalam = 0;

        for ($i = $buka; $i < strlen($skrip); $i++) {
            if ($skrip[$i] === '{') {
                $dalam++;
            } elseif ($skrip[$i] === '}') {
                $dalam--;

                if ($dalam === 0) {
                    return substr($skrip, $buka, $i - $buka + 1);
                }
            }
        }

        $this->fail("Kurung fungsi {$nama}() tidak tertutup.");
    }

    private function akun(array $ganti = []): User
    {
        $u = User::create(array_merge([
            'full_name' => 'Akun Uji ' . Str::upper(Str::random(4)),
            'username' => 'kafe_' . Str::random(8),
            'email' => 'akun' . Str::random(8) . '@contoh.test',
            'password' => bcrypt('RahasiaUji2026'),
            'level' => 'user',
            'telp' => '0811' . random_int(1000000, 9999999),
        ], $ganti));

        $u->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $u;
    }

    private function pesananKafe(string $telp, string $nama, string $email): PendaftaranScopusKafe
    {
        return PendaftaranScopusKafe::create([
            'nama' => $nama,
            'email' => $email,
            'telp' => $telp,
            'tanggal_pemesanan' => now()->addWeek()->toDateString(),
            'varian' => 'offline',
            'status_pembayaran' => 'pending',
            'total_keseluruhan_pembayaran' => 1000000,
        ]);
    }

    // ----------------------------------------------------------- sudah login

    #[Test]
    public function yang_sudah_login_borangnya_terisi_termasuk_nomornya(): void
    {
        $akun = $this->akun(['full_name' => 'Dr. Akun Login', 'telp' => '081299887766']);

        $isi = $this->actingAs($akun)->borang();

        $this->assertSame('Dr. Akun Login', $this->nilai($isi, 'kfbNama'));
        $this->assertSame($akun->email, $this->nilai($isi, 'kfbEmail'));

        /*
         * NOMORNYA juga, dan ini yang dulu rusak.
         *
         * Dua yang lain terisi dari kolom yang namanya memang benar, jadi
         * memeriksa nama dan email saja akan HIJAU di atas cacatnya. Isian
         * nomor ini satu-satunya yang mencari sisanya saat tamu mengetiknya,
         * dan ia dibaca dari kolom yang tidak ada.
         */
        $this->assertSame('081299887766', $this->nilai($isi, 'kfbTelp'),
            'Isian nomor WhatsApp harus terisi dari users.telp — bukan dari '
            . '`phone`, kolom yang tidak ada di tabel itu.');
    }

    #[Test]
    public function pesanan_terakhir_menang_atas_nama_di_akun(): void
    {
        $akun = $this->akun(['full_name' => 'admin', 'telp' => '081277665544']);

        $this->pesananKafe('081277665544', 'Prof. Dr. Nama Dari Pesanan', 'pesanan@contoh.test');

        $isi = $this->actingAs($akun)->borang();

        $this->assertSame('Prof. Dr. Nama Dari Pesanan', $this->nilai($isi, 'kfbNama'),
            'Nama di akun sering sekadar "admin"; yang dipakai borang ini harus '
            . 'nama dari pesanan terakhir.');
        $this->assertSame('pesanan@contoh.test', $this->nilai($isi, 'kfbEmail'));
    }

    #[Test]
    public function yang_sudah_login_tidak_ikut_menghabiskan_jatah_pencarian(): void
    {
        $akun = $this->akun();

        $isi = $this->actingAs($akun)->borang();

        /*
         * Borangnya sudah terisi dari peladen, jadi skrip pencarian lewat
         * nomor TIDAK dikirimkan kepadanya sama sekali. Jalur itu dibatasi 4
         * per menit per IP dan 25 per hari per IP — jatah yang disediakan
         * untuk tamu, dan tidak ada alasan yang sudah login ikut memakannya
         * demi jawaban yang sudah dipegang peladen.
         */
        $this->assertStringNotContainsString('cari-pendaftar', $isi,
            'Yang sudah login tidak boleh dikirimi jalur pencarian nomor: '
            . 'borangnya sudah terisi, dan jalur itu dibatasi lajunya.');
    }

    #[Test]
    public function pita_kabar_mengatakan_isinya_dari_mana(): void
    {
        $akun = $this->akun();

        $isi = $this->actingAs($akun)->borang();

        /*
         * Borang yang terisi sendiri tanpa mengatakan apa pun terbaca seperti
         * data orang lain yang tertinggal di peramban — dan yang membacanya
         * begitu akan menghapusnya lalu mengetik ulang, yaitu tepat yang
         * fitur ini dibuat untuk dihindari.
         */
        $this->assertMatchesRegularExpression(
            '/id="kfbKabarIsi"(?![^>]*\bhidden\b)/s',
            $isi,
            'Pita kabar harus TERLIHAT saat borangnya terisi dari peladen.'
        );
        $this->assertStringContainsString('kami isikan dari data Anda', $isi);
    }

    #[Test]
    public function borang_tamu_tidak_memajang_pita_kosong(): void
    {
        $isi = $this->borang();

        $this->assertSame('', (string) $this->nilai($isi, 'kfbNama'));

        /*
         * Pitanya ada di markah tetapi HARUS `hidden`: kotak hijau kosong
         * tepat di bawah isian pertama bukan kabar, melainkan cacat rupa.
         */
        $this->assertMatchesRegularExpression(
            '/id="kfbKabarIsi"[^>]*\s+hidden/s',
            $isi,
            'Pita kabar harus tersembunyi selama belum ada yang dikatakan.'
        );
    }

    #[Test]
    public function atribut_hidden_benar_benar_menyembunyikan_pitanya(): void
    {
        /*
         * `[hidden]` bekerja lewat `display: none` di lembar gaya BAWAAN
         * PERAMBAN, dan lembar gaya bawaan selalu kalah terhadap lembar gaya
         * halaman — bobot pemilihnya tidak ikut menentukan. Tanpa penimpa
         * eksplisit, `display: flex` menang dan pitanya tampil KOSONG
         * sepanjang waktu.
         *
         * Diperiksa di sini karena kegagalannya tidak terbaca dari markah:
         * atribut `hidden`-nya tetap ada, terpasang rapi, dan tidak bekerja.
         */
        $gaya = (string) file_get_contents(
            resource_path('views/public/scopus_kafe/partials/gaya-borang.blade.php')
        );

        $this->assertStringContainsString('.kfb-kabar-isi[hidden]', $gaya,
            'Gaya .kfb-kabar-isi memakai display: flex, jadi atribut hidden '
            . 'harus ditimpa eksplisit atau pitanya tampil kosong selamanya.');
    }

    // ----------------------------------------------------- tamu, lewat nomor

    #[Test]
    public function tamu_dikirimi_jalur_pencarian_beserta_batas_dari_peladen(): void
    {
        $isi = $this->borang();

        $this->assertStringContainsString('cari-pendaftar', $isi);

        /*
         * Batas panjang nomornya DITERUSKAN peladen, tidak ditulis ulang di
         * skripnya. Dua angka terpisah akan berselisih perlahan, dan
         * selisihnya berupa pencarian yang tidak pernah terkirim — atau yang
         * terkirim lalu selalu dijawab kosong.
         */
        $this->assertStringContainsString(
            'NOMOR_MIN = ' . \App\Support\NomorTelepon::MINIMAL,
            $isi
        );
        $this->assertStringContainsString(
            'NOMOR_MAKS = ' . \App\Support\NomorTelepon::MAKSIMAL,
            $isi
        );
    }

    #[Test]
    public function nomor_yang_pernah_memesan_dikenali_pencarinya(): void
    {
        /*
         * Jalur tamu berujung pada alamat yang sama dengan Scopus Camp, dan
         * yang menjawabnya kelas pencari bersama. Diperiksa lewat alamat itu,
         * bukan lewat kelasnya langsung: yang pernah rusak bukan pencariannya
         * melainkan sambungannya.
         */
        $this->pesananKafe('0813-5566-7788', 'Pemesan Kafe Lama', 'lama@contoh.test');

        $this->postJson(route('public.scopuscamp.caripendaftar'), ['telp' => '081355667788'])
            ->assertOk()
            ->assertJson([
                'ditemukan' => true,
                'nama' => 'Pemesan Kafe Lama',
                'email' => 'lama@contoh.test',
            ]);
    }

    #[Test]
    public function nomor_yang_belum_pernah_memesan_dijawab_sama_dengan_nomor_ngawur(): void
    {
        /*
         * Jawaban yang SAMA untuk keduanya. Membedakannya memberi tahu yang
         * mencoba nomor satu per satu mana nomor yang benar-benar ada di
         * sini — dan alamat ini menukar nomor jadi nama dan email.
         */
        $belum = $this->postJson(route('public.scopuscamp.caripendaftar'),
            ['telp' => '0899' . random_int(1000000, 9999999)]);
        $ngawur = $this->postJson(route('public.scopuscamp.caripendaftar'), ['telp' => '12']);

        $belum->assertOk()->assertExactJson(['ditemukan' => false]);
        $ngawur->assertOk()->assertExactJson(['ditemukan' => false]);
    }

    // --------------------------------------- aturan yang hidup di JavaScript

    #[Test]
    public function skrip_mendengarkan_tempel_dan_pindah_isian_bukan_hanya_ketikan(): void
    {
        $isi = $this->borang();

        /*
         * Terukur tanpa keduanya: menempel nomor yang ada di basis data lalu
         * berpindah isian mengirim NOL permintaan, dan borangnya tetap kosong
         * tanpa sepatah kata. Orang yang menyalin nomornya dari WhatsApp
         * adalah jalur paling wajar di layanan ini.
         */
        foreach (["'change'", "'blur'"] as $peristiwa) {
            $this->assertStringContainsString(
                "telp.addEventListener({$peristiwa}",
                $isi,
                "Tanpa pendengar {$peristiwa}, tempel dari papan klip dan isian "
                . 'otomatis peramban tidak memicu pencarian sama sekali.'
            );
        }
    }

    #[Test]
    public function skrip_mengatakan_saat_pencariannya_sedang_dibatasi(): void
    {
        $isi = $this->borang();

        /*
         * 429 yang didiamkan adalah bentuk yang dilaporkan sebagai "kenapa
         * belum otomatis": borang kosong, pita kosong, konsol tanpa galat.
         * Dari layar, "belum pernah memesan" dan "sedang dibatasi" tidak bisa
         * dibedakan.
         */
        $this->assertStringContainsString('429', $isi,
            'Skripnya harus memeriksa 429 — bukan menelannya lewat `r.ok`.');
        $this->assertStringContainsString('Pengisian otomatis sedang dibatasi', $isi,
            'Penolakan pembatas laju harus dikatakan, bukan didiamkan.');

        /*
         * Kalimatnya SENGAJA tidak menyebut apakah nomornya terdaftar: 429
         * terbit untuk nomor yang ada maupun yang tidak, dan menyebutkannya
         * mengubah pita itu jadi alat memeriksa keanggotaan.
         *
         * Diperiksa pada KALIMAT PITANYA, bukan pada seluruh halaman: kata
         * "terdaftar" memang ada di tata letak publik, pada tautan masuk
         * akun, dan uji yang menyapu seluruh respons akan merah karena hal
         * yang tidak ada hubungannya.
         */
        /*
         * KOMENTARNYA DIBUANG DULU.
         *
         * Komentar di dalam fungsi itu menjelaskan justru mengapa kata-kata
         * ini tidak boleh dipakai, jadi ia memuat semuanya — dan uji yang
         * membaca badan fungsi apa adanya akan merah karena penjelasan
         * aturannya sendiri. Pola yang sama sudah pernah membuat uji di
         * proyek ini merah lewat komentar CSS yang ikut terkirim ke peramban.
         */
        $kalimat = (string) preg_replace(
            ['#/\*.*?\*/#s', '#//[^\n]*#'],
            '',
            $this->fungsiSkrip('tampilkanTertahan')
        );

        foreach (['terdaftar', 'pernah memesan', 'ketemu'] as $kata) {
            $this->assertStringNotContainsString($kata, $kalimat,
                'Kabar "sedang dibatasi" tidak boleh menyebut keadaan nomornya: '
                . '429 terbit untuk nomor yang ada di basis data maupun yang '
                . 'tidak, dan menyebutkannya menjadikan pita ini alat memeriksa '
                . 'keanggotaan.');
        }
    }

    #[Test]
    public function skrip_tidak_mencari_nomor_yang_sama_dua_kali(): void
    {
        $isi = $this->borang();

        /*
         * Tanpa penjaga ini, 'blur' yang dilepas tiap kali orangnya kembali
         * memeriksa isiannya jadi satu permintaan baru — dan jatahnya 4 per
         * menit per IP.
         */
        $this->assertStringContainsString('nomorTerakhir', $isi);
        $this->assertStringContainsString('nomor === nomorTerakhir', $isi);
    }

    #[Test]
    public function jeda_ketikan_cukup_panjang_untuk_tidak_mencari_nomor_separuh(): void
    {
        $isi = $this->borang();

        /*
         * Terukur dengan 600 ms: satu orang mengetik satu nomor dengan ragu
         * dan melepas ENAM permintaan, yang keempat dan seterusnya dijawab
         * 429 — termasuk yang membawa nomor lengkapnya. Dengan 1.500 ms:
         * SATU permintaan, dan borangnya terisi.
         *
         * Yang dijaga angkanya CUKUP BESAR, bukan persis 1500: 1.800 atau
         * 2.000 sama benar, dan uji yang memaku satu angka cuma akan merah
         * karena orang menyetelnya lebih aman.
         *
         * Menaikkan batas panjang nomor BUKAN pengganti aturan ini: dari 347
         * nomor yang tersimpan, 16 (4,6%) panjangnya cuma 9-10 angka, jadi
         * batas yang cukup tinggi untuk menahan nomor separuh juga membuang
         * satu dari dua puluh dua orang sungguhan.
         */
        $this->assertSame(1, preg_match('/setTimeout\(cari,\s*(\d+)\)/', $isi, $m),
            'Pencarian lewat ketikan harus tetap ditunda — tanpa penundaan, '
            . 'tiap ketukan papan ketik jadi satu permintaan.');

        $this->assertGreaterThanOrEqual(1200, (int) $m[1],
            'Jeda di bawah 1.200 ms masih terjadi DI TENGAH mengetik nomor, '
            . 'dan nomor separuh yang ikut dicari menghabiskan jatah 4 per '
            . 'menit sebelum nomor lengkapnya sampai.');
    }

    #[Test]
    public function skrip_menormalkan_nomor_seperti_peladen(): void
    {
        $isi = $this->borang();

        /*
         * Peladen mengukur panjangnya SESUDAH 0 di depan diganti 62, jadi
         * "087880000914" dihitung 13 angka di sana. Skrip yang mengukur
         * bentuk mentahnya menghitung 12, dan batas yang katanya sama tetap
         * berselisih satu angka — selisih yang berupa satu permintaan
         * terkirim lalu ditolak.
         */
        $this->assertStringContainsString("'62' + angka.slice(1)", $isi,
            'Skripnya harus menormalkan nomor seperti NomorTelepon::rapikan() '
            . 'sebelum mengukur panjangnya.');
    }

    #[Test]
    public function yang_diisikan_skrip_bisa_dikosongkan_lagi(): void
    {
        $isi = $this->borang();

        /*
         * Isi otomatis yang tidak bisa dibatalkan adalah isi otomatis yang
         * memaksa. Yang dikosongkan HANYA yang diisikan skrip — mengosongkan
         * semuanya membuang nama yang mungkin sudah diketik orangnya sendiri,
         * dan tombol yang menghapus pekerjaan orang tidak akan ditekan
         * siapa pun lagi.
         */
        $this->assertStringContainsString('Kosongkan', $isi);
        $this->assertStringContainsString('diisiSkrip.forEach', $isi);
    }

    #[Test]
    public function skrip_tidak_menimpa_isian_yang_sudah_diketik(): void
    {
        $isi = $this->borang();

        // Orangnya mungkin sengaja memesankan untuk orang lain.
        $this->assertStringContainsString("isian.value.trim() !== ''", $isi);
    }

    #[Test]
    public function pita_kabar_tidak_dirakit_lewat_innerhtml(): void
    {
        $skrip = $this->skrip();

        /*
         * Pitanya memuat NAMA dan EMAIL dari basis data, dan keduanya diketik
         * orang ke borang umum — jadi keduanya bisa memuat markah. Dirakit
         * lewat createElement dan textContent, bukan innerHTML.
         *
         * Yang dijaga PITA INI, bukan seluruh berkas: kabar tanggal di blok
         * lain memang memakai innerHTML, dan isinya kalimat tetap yang
         * ditulis di berkas ini sendiri — tidak ada data orang di sana.
         */
        $this->assertStringNotContainsString('kabarIsi.innerHTML', $skrip,
            'Pita kabar memuat nama dan email dari basis data; merakitnya '
            . 'lewat innerHTML menjadikannya jalan masuk markah.');
        $this->assertStringContainsString('teks.textContent = kalimat', $skrip);
    }
}
