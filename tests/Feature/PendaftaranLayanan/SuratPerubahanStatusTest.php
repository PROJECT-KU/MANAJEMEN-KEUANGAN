<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\Actions\Pendaftaran\UbahStatusPendaftaran;
use App\KategoriLayanan;
use App\Mail\PerubahanStatusPendaftaranMail;
use App\Support\KabarStatusPendaftaran;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tiap perubahan status memberi tahu pendaftarnya.
 *
 * Sebelum ini 5 dari 23 status yang mengirim surat. Delapan belas sisanya
 * diam — termasuk SELURUH status Webinar Eksklusif dan Clinik Scopus.
 * Pendaftar yang ditolak, dibatalkan, atau kursinya dilepas karena batas
 * waktu menunggu kabar yang tidak akan pernah datang.
 */
class SuratPerubahanStatusTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();
    }

    #[Test]
    public function tiap_status_tiap_layanan_punya_suratnya(): void
    {
        $tanpa = [];

        foreach (array_keys(Pendaftaran::katalog()) as $layanan) {
            foreach (array_keys(Pendaftaran::pilihanStatus($layanan)) as $status) {
                if (Pendaftaran::suratUntuk($layanan, $status) === null) {
                    $tanpa[] = $layanan . ' / ' . $status;
                }
            }
        }

        $this->assertSame([], $tanpa,
            "Status berikut tidak memberi tahu pendaftarnya sama sekali:\n- "
            . implode("\n- ", $tanpa) . "\n");
    }

    #[Test]
    public function tiap_status_punya_kalimatnya_sendiri(): void
    {
        /*
         * Dua daftar yang harus seiring: status yang dipakai layanan, dan
         * kalimat suratnya. Status baru yang ditambahkan tanpa kalimatnya
         * akan MELEMPAR saat suratnya dirakit — di belakang try/catch, jadi
         * yang terlihat panitia cuma "suratnya tidak terkirim" tanpa sebab.
         */
        $tanpa = [];

        foreach (array_keys(Pendaftaran::katalog()) as $layanan) {
            foreach (array_keys(Pendaftaran::pilihanStatus($layanan)) as $status) {
                if (! KabarStatusPendaftaran::ada($status)) {
                    $tanpa[] = $layanan . ' / ' . $status;
                }
            }
        }

        $this->assertSame([], $tanpa,
            "Status berikut belum punya kalimat di KabarStatusPendaftaran:\n- "
            . implode("\n- ", $tanpa) . "\n");

        // Sebaliknya juga: kalimat yang tidak dipakai status mana pun hanya
        // menumpuk dan menyesatkan yang membacanya.
        $dipakai = [];

        foreach (array_keys(Pendaftaran::katalog()) as $layanan) {
            $dipakai = array_merge($dipakai, array_keys(Pendaftaran::pilihanStatus($layanan)));
        }

        $menganggur = array_diff(KabarStatusPendaftaran::semuaStatus(), $dipakai);

        $this->assertSame([], array_values($menganggur),
            'Kalimat ini tidak dipakai status mana pun: ' . implode(', ', $menganggur));
    }

    /**
     * Ragam bahasa suratnya RESMI, pembuka sampai penutupnya.
     *
     * Dijaga SEPASANG, bukan pembukanya saja. Terukur saat memformalkannya:
     * pembukanya sudah "telah kami terima dan saat ini menunggu pembayaran"
     * sementara penutup surat yang sama masih "Kalau sudah transfer,
     * kirimkan buktinya ke panitia supaya bisa segera kami cek" — satu
     * surat bernada dua, dan yang paling janggal justru terbaca di surat
     * yang menagih uang.
     *
     * Kalimat pembukanya dipakai bersama pesan WhatsApp (lihat
     * PesanWaPendaftaran), jadi satu kalimat santai di sini ikut terkirim
     * lewat dua saluran sekaligus.
     */
    #[Test]
    public function ragam_bahasa_suratnya_resmi(): void
    {
        /*
         * Penanda santai, masing-masing punya pasangan resminya yang sudah
         * dipakai: "Kalau" -> "Apabila", ", jadi" -> "sehingga",
         * "bisa" -> "dapat", "kami cek" -> "kami periksa".
         *
         * Halaman status publiknya TIDAK ikut dijaga penjaga ini. Suaranya
         * sengaja lebih awam — yang membacanya pendaftar di tengah
         * membayar, bukan penerima surat resmi.
         */
        $santai = ['Halo', 'Kalau ', ', jadi ', 'kami cek', 'balas saja', 'nggak', '!'];

        $tercela = [];

        foreach (KabarStatusPendaftaran::semuaStatus() as $status) {
            $kabar = KabarStatusPendaftaran::untuk($status);
            $teks = $kabar['pembuka'] . ' ' . implode(' ', $kabar['penutup']);

            foreach ($santai as $kata) {
                if (str_contains($teks, $kata)) {
                    $tercela[] = $status . ': "' . $kata . '"';
                }
            }
        }

        $this->assertSame([], $tercela,
            "Kalimat surat berikut masih bernada santai:\n- " . implode("\n- ", $tercela) . "\n");
    }

    #[Test]
    public function kedua_puluh_tiga_suratnya_benar_benar_terbit(): void
    {
        /*
         * Dirakit sungguhan, bukan sekadar diperiksa kelasnya ada. Templat
         * yang memanggil kolom yang tidak dimiliki satu layanan baru ketahuan
         * saat dirender — dan saat itu terjadi di produksi, yang terlihat cuma
         * satu baris di log.
         */
        $terbit = 0;

        foreach ($this->contohSemuaLayanan() as $layanan => $baris) {
            foreach (array_keys(Pendaftaran::pilihanStatus($layanan)) as $status) {
                $salin = clone $baris;
                $salin->status = $status;

                $html = (new PerubahanStatusPendaftaranMail($layanan, $salin))->render();

                $kabar = KabarStatusPendaftaran::untuk($status);

                $this->assertStringContainsString($kabar['judul'], $html,
                    "Surat {$layanan}/{$status} tidak memuat judulnya.");
                $this->assertStringContainsString($kabar['pembuka'], $html,
                    "Surat {$layanan}/{$status} tidak memuat kalimat pembukanya.");
                $this->assertStringContainsString(
                    Pendaftaran::katalog()[$layanan]['nama'], $html,
                    "Surat {$layanan}/{$status} tidak menyebut layanannya."
                );

                $terbit++;
            }
        }

        $this->assertSame(23, $terbit, 'Jumlah statusnya berubah; daftar kalimatnya perlu ditinjau.');
    }

    /**
     * Tidak ada surat yang menyuruh pendaftar MEMBALAS.
     *
     * Kotak masuk pengirimnya tidak dijaga siapa pun. Balasan pendaftar
     * mengendap di sana tanpa ada yang membacanya, dan yang mengirimnya
     * mengira pertanyaannya sudah sampai — lalu menunggu jawaban yang tidak
     * akan pernah datang. Itu lebih buruk daripada tidak diberi jalan sama
     * sekali.
     *
     * Dulu sembilan kalimat penutup berbunyi "mohon balas email ini".
     */
    #[Test]
    public function surat_tidak_menyuruh_membalas_emailnya(): void
    {
        $tercela = [];

        foreach (KabarStatusPendaftaran::semuaStatus() as $status) {
            $kabar = KabarStatusPendaftaran::untuk($status);
            $teks = $kabar['pembuka'] . ' ' . implode(' ', $kabar['penutup']);

            /*
             * "membalas" sengaja TIDAK dilarang: kalimat "mohon tidak
             * membalas email ini" justru yang benar, dan melarang katanya
             * akan merah pada kalimat yang tidak bersalah.
             */
            foreach (['balas email', 'balas surat', 'balasan email'] as $kata) {
                if (str_contains(mb_strtolower($teks), $kata)) {
                    $tercela[] = $status . ': "' . $kata . '"';
                }
            }
        }

        $this->assertSame([], $tercela,
            "Kalimat surat berikut menyuruh membalas email yang tidak dibaca "
            . "siapa pun:\n- " . implode("\n- ", $tercela) . "\n");
    }

    /**
     * Gantinya memang ADA di suratnya, bukan cuma kalimatnya yang dibuang.
     *
     * Menghapus "balas email ini" tanpa memberi jalan lain meninggalkan
     * pendaftar tanpa cara menghubungi siapa pun — lebih buruk daripada
     * sebelumnya.
     *
     * Nomornya dibandingkan dengan config('panitia.whatsapp'), bukan dengan
     * angka yang ditulis ulang di sini: ditulis ulang, penjaganya tetap
     * hijau pada hari nomornya diganti dan tombolnya menunjuk orang yang
     * sudah tidak memegangnya.
     */
    #[Test]
    public function surat_memberi_jalan_menghubungi_admin(): void
    {
        $nomor = (string) config('panitia.whatsapp');

        $this->assertNotSame('', $nomor, 'Nomor WhatsApp panitia tidak tersetel.');

        foreach ($this->contohSemuaLayanan() as $layanan => $baris) {
            $html = (new PerubahanStatusPendaftaranMail($layanan, $baris))->render();

            $this->assertStringContainsString(
                'https://api.whatsapp.com/send?phone=' . $nomor, $html,
                "Surat {$layanan} tidak punya tombol menghubungi admin."
            );

            $this->assertStringContainsString('tidak dibalas', $html,
                "Surat {$layanan} tidak memberi tahu bahwa emailnya tidak dibalas.");

            /*
             * wa.me DITOLAK. Ia mengacaukan tanda baca dan emoji pada teks
             * yang sudah terisi — sebab yang sama tombol WhatsApp di layar
             * panitia memakai api.whatsapp.com.
             */
            $this->assertStringNotContainsString('wa.me/', $html,
                "Surat {$layanan} memakai wa.me, bukan api.whatsapp.com.");
        }
    }

    #[Test]
    public function suratnya_berlogo_yayasan_bukan_logo_alat(): void
    {
        /*
         * logo-email.png adalah logo MIS — "Management Integration System by
         * Rumah Scopus" — logo ALAT ADMINISTRASI INTERNAL. Surat ini dibaca
         * pendaftar, bukan orang dalam; mengirimi calon peserta logo alat
         * internal menyampaikan merek yang salah.
         */
        [$layanan, $baris] = [array_key_first($this->contohSemuaLayanan()), null];
        $contoh = $this->contohSemuaLayanan();
        $baris = $contoh[$layanan];
        $baris->status = Pendaftaran::statusAwal($layanan);

        $html = (new PerubahanStatusPendaftaranMail($layanan, $baris))->render();

        $this->assertStringContainsString('logo-rsc-email.png', $html,
            'Surat status tidak memakai logo yayasan.');
        $this->assertStringNotContainsString('logo-email.png"', $html,
            'Surat status memakai logo alat administrasi internal.');
        $this->assertStringNotContainsString('LogoRSC', $html,
            'Surat status memakai berkas logo lama yang sudah digantikan.');
    }

    #[Test]
    public function suratnya_tidak_rata_kiri_semua(): void
    {
        /*
         * Keluhan yang memicu pekerjaan ini. Yang dijaga UNSUR mana yang di
         * tengah, bukan sekadar ada kata "center" di suatu tempat: logo,
         * judul, lencana, dan kakinya. Isi paragraf tetap rata kiri — teks
         * panjang yang ditengahkan justru lebih sulit dibaca.
         */
        $contoh = $this->contohSemuaLayanan();
        $layanan = 'webinar_eksklusif';
        $baris = $contoh[$layanan];
        $baris->status = 'expired';

        $html = (new PerubahanStatusPendaftaranMail($layanan, $baris))->render();

        $this->assertGreaterThanOrEqual(4, substr_count($html, 'align="center"'),
            'Unsur yang seharusnya di tengah (logo, judul, lencana, kaki) berkurang.');

        // Logonya di dalam sel yang ditengahkan.
        $this->assertMatchesRegularExpression(
            '/<td align="center"[^>]*>\s*<img[^>]*logo-rsc-email/s', $html,
            'Logonya tidak lagi berada di sel yang ditengahkan.'
        );

        // Nilai di tabel rincian rata KANAN, labelnya kiri — supaya angkanya
        // sejajar dan bisa dibandingkan sekilas.
        $this->assertStringContainsString('<td align="right"', $html,
            'Nilai di tabel rincian tidak lagi rata kanan.');

        // Dan bukan dengan flexbox: Gmail dan Outlook membuangnya mentah-
        // mentah, dan itu persis sebab emailnya dulu terbaca rata kiri polos.
        $this->assertStringNotContainsString('display:flex', $html);
        $this->assertStringNotContainsString('display: flex', $html);
    }

    #[Test]
    public function status_yang_dulu_diam_sekarang_mengirim_surat(): void
    {
        Mail::fake();

        $orang = $this->akun();
        $contoh = $this->contohSemuaLayanan();

        // Dua layanan yang SEBELUMNYA tidak punya satu pun surat status.
        foreach (['webinar_eksklusif' => 'cancel', 'clinik_scopus' => 'canceled'] as $layanan => $status) {
            $baris = $contoh[$layanan];

            $hasil = (new UbahStatusPendaftaran)->jalankan(
                $layanan, (string) $baris->getKey(), $status, $orang->full_name
            );

            $this->assertTrue($hasil['berhasil'], $hasil['pesan']);
            $this->assertTrue($hasil['surat'],
                "Perubahan status {$layanan} tidak mengirim surat apa pun.");
        }

        Mail::assertSent(PerubahanStatusPendaftaranMail::class, 2);
    }

    #[Test]
    public function surat_khusus_yang_sudah_ada_tetap_didahulukan(): void
    {
        /*
         * Surat "diterima" Scopus Camp memuat tautan grup WhatsApp, lokasi,
         * serta tanggal mulai dan selesai angkatan — isinya lebih kaya
         * daripada surat umum. Surat umum yang menang atasnya berarti
         * pendaftar yang diterima kehilangan tautan grupnya.
         */
        $this->assertSame(
            \App\Mail\ScopusCampUpdateDiterimaMail::class,
            Pendaftaran::suratUntuk('scopus_camp', 'Pendaftaran Diterima')
        );

        $this->assertSame(
            PerubahanStatusPendaftaranMail::class,
            Pendaftaran::suratUntuk('scopus_camp', 'Pendaftaran Ditolak')
        );
    }

    // ------------------------------------------------------------- pembantu

    private function akun(): User
    {
        $u = User::create([
            'full_name' => 'Rina Panitia', 'username' => 'surat_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'), 'level' => 'user',
        ]);

        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        return $u->refresh();
    }

    /** Satu baris BARU per layanan; tidak menyentuh data yang sudah ada. */
    private function contohSemuaLayanan(): array
    {
        $buatAngkatan = fn (string $l) => KategoriLayanan::create([
            'layanan' => $l, 'nama' => 'Angkatan Uji ' . Str::random(6),
            'mulai' => now()->addMonth()->toDateString(),
            'selesai' => now()->addMonth()->addDays(3)->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ]);

        $t = fn () => Str::random(8);

        $umum = fn ($x) => [
            'nama' => 'Peserta ' . $x, 'email' => $x . '@contoh.test',
            'telp' => '0811-0000-0001', 'affiliasi' => 'Instansi ' . $x,
        ];

        $a = $t();
        $b = $t();
        $c = $t();
        $d = $t();
        $e = $t();

        return [
            'scopus_camp' => \App\PendaftaranScopusCamp::create($umum($a) + [
                'id_transaksi' => 'T-' . $a, 'kategori_id' => $buatAngkatan('scopus_camp')->id,
                'jumlah_pendaftar' => '1', 'total_pembayaran' => '5500000', 'status' => 'diproses']),

            'bibliometrik' => \App\AnalisisBibliometrik::create($umum($b) + [
                'id_transaksi' => 'B-' . $b, 'kategori_id' => $buatAngkatan('bibliometrik')->id,
                'jumlah_pendaftar' => '1', 'total_pembayaran' => '700000', 'status' => 'diproses']),

            'webinar_eksklusif' => \App\WebinarEksklusifPendaftaran::create($umum($c) + [
                'id_transaksi' => 'WE-' . $c, 'kategori_id' => $buatAngkatan('webinar_eksklusif')->id,
                'jumlah_pendaftar' => '1', 'total_pembayaran' => '129500', 'status' => 'pending']),

            'scopus_kafe' => \App\PendaftaranScopusKafe::create($umum($d) + [
                'id_pemesanan' => 'K-' . $d, 'tanggal_pemesanan' => now()->toDateString(),
                'total_pembayaran' => '1000000', 'status' => 'menunggu verifikasi']),

            'clinik_scopus' => \App\ClinikScopusPemesanan::create([
                'id_transaksi' => 'C-' . $e, 'nama_pemesan' => 'Peserta ' . $e,
                'email_pemesan' => $e . '@contoh.test', 'telp_pemesan' => '0811-0000-0001',
                'afiliasi_pemesan' => 'Instansi ' . $e,
                'clinikscopus_id' => DB::table('clinikscopus')->value('id'),
                'sesi' => DB::table('clinikscopus')->value('id'),
                'trainer_id' => \App\ClinikScopusPemesanan::value('trainer_id') ?: 1,
                'customer_id' => \App\ClinikScopusPemesanan::value('customer_id') ?: 1,
                'total_pembayaran' => '500000', 'status' => 'pending']),
        ];
    }
}
