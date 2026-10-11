<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\KategoriLayanan;
use App\PendaftaranScopusCamp;
use App\Support\KabarStatusPendaftaran;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\Support\PesanWaPendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tombol "Follow up" per status, lewat WhatsApp.
 *
 * Panitia mengabari pendaftar lewat dua saluran — surat dan WhatsApp — dan
 * keduanya harus mengatakan hal yang sama. Kalimat keadaannya karena itu
 * diambil dari KabarStatusPendaftaran, sumber yang dipakai suratnya; ditulis
 * ulang, keduanya akan berbeda bunyi perlahan dan pendaftar yang membaca
 * keduanya tidak tahu mana yang berlaku.
 */
class TindakLanjutWaTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function tombolnya_ada_di_kartu_status(): void
    {
        [$orang, $b] = $this->dengan();

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->getKey()]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Follow up: Menunggu bayar', $isi,
            'Tombol follow up tidak menyebut status yang sedang berlaku.');

        /*
         * Tombolnya BORANG, bukan tautan langsung: peladen mencatat jejaknya
         * dulu baru meneruskan ke percakapannya. Dijaga keduanya — borangnya
         * ada, dan ujungnya memang api.whatsapp.com.
         */
        $this->assertMatchesRegularExpression(
            '#<form[^>]*class="rin-follow-up"[^>]*action="[^"]*/follow-up"#s',
            $isi,
            'Tombol follow up bukan borang, jadi penekanannya tidak tercatat.'
        );

        $this->actingAs($orang)
            ->post(route('account.pendaftaran-layanan.follow-up', ['scopus_camp', $b->getKey()]))
            ->assertRedirectContains('https://api.whatsapp.com/send?phone=');
    }

    /**
     * Kalimatnya datang dari sumber yang sama dengan suratnya. Diuji pada
     * status yang kalimatnya khas, bukan yang umum — kalau pesannya ternyata
     * dikarang sendiri, kata-kata ini tidak akan muncul.
     */
    #[Test]
    public function kalimatnya_sama_dengan_suratnya(): void
    {
        foreach (['diproses', 'Pendaftaran Diterima', 'Pendaftaran Reschedule',
            'Pendaftaran Refund', 'Pendaftaran Ditolak'] as $status) {
            $pesan = PesanWaPendaftaran::tindakLanjut($this->baris($status));

            $this->assertStringContainsString(
                KabarStatusPendaftaran::untuk($status)['pembuka'], $pesan,
                "Pesan WhatsApp status {$status} tidak memakai kalimat yang sama dengan suratnya."
            );
        }
    }

    /**
     * TIDAK BOLEH ada kata-kata khas surat.
     *
     * Percobaan pertama memakai ulang penutup milik suratnya lalu
     * menambalnya dengan str_replace. Dua kalimat lolos: "lewat email yang
     * sama" dan "Rinciannya ada di bawah ini" — yang pertama menyuruh orang
     * membalas email yang tidak sedang ia buka, yang kedua menjanjikan tabel
     * yang tidak ada di WhatsApp.
     */
    #[Test]
    public function tidak_ada_kalimat_khas_surat(): void
    {
        foreach (KabarStatusPendaftaran::semuaStatus() as $status) {
            $pesan = PesanWaPendaftaran::tindakLanjut($this->baris($status), 'Angkatan Uji');

            foreach (['balas email', 'email ini', 'email yang sama', 'surat ini'] as $khasSurat) {
                $this->assertStringNotContainsString($khasSurat, $pesan,
                    "Pesan WhatsApp status {$status} menyuruh pendaftar membuka email.");
            }
        }
    }

    /**
     * Status "Dijadwalkan ulang" berjanji rinciannya tercantum di bawah, dan
     * janji itu hanya ditepati kalau sesinya memang ikut terkirim.
     */
    #[Test]
    public function sesinya_ikut_saat_dijadwalkan_ulang(): void
    {
        $pesan = PesanWaPendaftaran::tindakLanjut(
            $this->baris('Pendaftaran Reschedule'), 'Angkatan Yogyakarta ke-12'
        );

        $this->assertStringContainsString('Angkatan Yogyakarta ke-12', $pesan,
            'Jadwal barunya tidak disebut, padahal kalimatnya menjanjikan rinciannya.');
    }

    /** Tiap keadaan punya kalimat penutupnya; tidak ada pesan yang menggantung. */
    #[Test]
    public function tiap_keadaan_punya_penutupnya(): void
    {
        foreach (array_keys(Pendaftaran::KEADAAN) as $keadaan) {
            $nilai = Pendaftaran::KEADAAN[$keadaan]['nilai'][0] ?? null;

            if ($nilai === null) {
                continue;
            }

            $pesan = PesanWaPendaftaran::tindakLanjut($this->baris($nilai));
            $akhir = trim((string) strrchr($pesan, "\n"));

            /*
             * Yang dituntut: baris terakhirnya KALIMAT AJAKAN, bukan baris
             * kosong atau potongan rincian. Menuntut diakhiri titik saja
             * terlalu lemah — potongan rincian pun berakhir titik.
             */
            $this->assertNotSame('', $akhir,
                "Pesan untuk keadaan {$keadaan} berakhir dengan baris kosong.");

            $this->assertMatchesRegularExpression('/(balas pesan ini|tautan pembayaran|simpan pesan ini|ingin ditanyakan|mengabari melalui nomor ini|kepada panitia)/', $akhir,
                "Pesan untuk keadaan {$keadaan} tidak ditutup kalimat ajakan: {$akhir}");
        }
    }

    /**
     * Status yang belum punya kalimat tidak menghasilkan pesan setengah
     * jadi — panitia masih bisa mengetik sisanya sendiri, jauh lebih baik
     * daripada tombol yang mati.
     */
    #[Test]
    public function status_tanpa_kalimat_tetap_menghasilkan_pesan(): void
    {
        $pesan = PesanWaPendaftaran::tindakLanjut($this->baris('status-karangan'));

        $this->assertStringContainsString('Yth. Bapak/Ibu Budi Santoso,', $pesan);
        $this->assertStringContainsString('CAMP-0007', $pesan,
            'Nomor pendaftarannya hilang, jadi pesannya tidak menunjuk pendaftaran mana pun.');
    }

    #[Test]
    public function tanpa_nomor_yang_bisa_dihubungi_tombolnya_tidak_ada(): void
    {
        [$orang, $b] = $this->dengan(['telp' => '0812']);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->getKey()]))
            ->assertOk()->getContent();

        /*
         * Dicari TAGNYA, bukan nama kelasnya di mana saja.
         *
         * Versi pertama mencari untaian 'rin-tombol-wa' di seluruh halaman
         * dan merah pada markah yang justru benar: blok <style> halaman ini
         * sendiri memuat nama kelas itu. Pemindai yang menjaring gayanya
         * sendiri tidak menjaga apa pun.
         */
        $this->assertSame(0, preg_match('/<button[^>]*class="[^"]*rin-tombol-wa/', $isi),
            'Tombolnya tergambar untuk nomor yang tidak bisa dihubungi; panitia '
            . 'akan mengira pesannya terkirim.');
    }

    /**
     * Kedua tombol SEJAJAR dalam satu baris, dengan satu keterangan di
     * bawah keduanya.
     *
     * Keduanya pekerjaan yang sama — mengabari pendaftar tentang keadaannya
     * sekarang, cuma lewat saluran berbeda. Bertumpuk, keduanya tampak
     * tidak sejenis dan tiap tombol menuntut keterangan sendiri yang
     * mengulang kalimat yang sama.
     */
    #[Test]
    public function kedua_tombolnya_sebaris(): void
    {
        [$orang, $b] = $this->dengan();

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->getKey()]))
            ->assertOk()->getContent();

        /*
         * Dicari ATRIBUT class-nya, bukan nama kelasnya di mana saja.
         *
         * Kemunculan pertama 'rin-lanjutan-tombol' di halaman ini ada di
         * blok <style>-nya sendiri — potongan yang diambil dari situ berisi
         * CSS, bukan markah, dan penjaganya merah pada susunan yang justru
         * benar. Jebakan yang sudah beberapa kali terjadi di berkas ini.
         */
        $awal = strpos($isi, 'class="rin-lanjutan-tombol"');

        $this->assertNotFalse($awal, 'Baris tombol tindak lanjut tidak tergambar.');

        $baris = substr($isi, $awal, strpos($isi, '</div>', $awal) - $awal);

        $this->assertStringContainsString('Kirim ulang suratnya', $baris,
            'Tombol kirim ulang tidak berada di baris yang sama.');
        $this->assertStringContainsString('Follow up:', $baris,
            'Tombol follow up tidak berada di baris yang sama.');
    }

    /**
     * Borangnya display: contents.
     *
     * Sebagai blok, <form> jadi anak flex tersendiri dan tombolnya
     * terbungkus satu lapis lagi — jaraknya dobel dan tingginya tidak
     * sejajar dengan tautan di sebelahnya.
     */
    #[Test]
    public function borang_kirim_ulang_tidak_memakan_slot_flex(): void
    {
        $this->assertMatchesRegularExpression(
            '/\.rin-kirim-ulang,\s*\.rin-follow-up\s*\{[^}]*display:\s*contents/s',
            file_get_contents(resource_path('views/account/pendaftaran_layanan/rincian.blade.php')),
            'Borang kirim ulang kembali jadi blok, jadi kedua tombolnya tidak sejajar.'
        );
    }

    /**
     * Keterangannya mengikuti tombol yang MEMANG ada.
     *
     * Satu kalimat tetap yang menyebut dua tombol akan menerangkan tombol
     * yang tidak tergambar — surat tidak selalu ada untuk tiap status, dan
     * nomor WhatsApp tidak selalu bisa dihubungi.
     */
    #[Test]
    public function keterangannya_tidak_menyebut_tombol_yang_tidak_ada(): void
    {
        [$orang, $b] = $this->dengan(['telp' => '0812']);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $b->getKey()]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Mengirim lagi surat untuk status yang sekarang', $isi,
            'Keterangan untuk tombol tunggalnya hilang.');

        $this->assertStringNotContainsString('Keduanya memakai status', $isi,
            'Keterangannya menyebut dua tombol padahal yang tergambar cuma satu.');
    }

    /**
     * Menekan Follow up MENINGGALKAN JEJAK.
     *
     * Sebelum ini tombolnya tautan biasa: WhatsApp terbuka dan selesai.
     * Percobaan kirim email tercatat rapi, jadi riwayatnya timpang — yang
     * lewat surat terlihat, yang lewat WhatsApp tidak — dan pertanyaan
     * "sudah difollow-up belum?" tidak bisa dijawab dari layar mana pun.
     */
    #[Test]
    public function menekannya_meninggalkan_jejak(): void
    {
        [$orang, $b] = $this->dengan();

        $this->actingAs($orang)
            ->post(route('account.pendaftaran-layanan.follow-up', ['scopus_camp', $b->getKey()]))
            ->assertRedirectContains('api.whatsapp.com');

        $jejak = \App\PendaftaranJejak::milik('scopus_camp', (string) $b->getKey())
            ->where('aksi', 'follow-up')->first();

        $this->assertNotNull($jejak, 'Follow up tidak meninggalkan jejak apa pun.');
        $this->assertSame('diproses', $jejak->dari,
            'Status yang difollow-up tidak tercatat, jadi tidak ketahuan pesan mana yang dibuka.');
        $this->assertNotEmpty($jejak->oleh_nama, 'Siapa yang menghubungi tidak tercatat.');
    }

    /**
     * Kalimatnya berbunyi "DIBUKA", bukan "terkirim".
     *
     * Yang diketahui peladen hanyalah panitia menekan tombolnya;
     * pengirimannya terjadi di aplikasi WhatsApp, di luar jangkauan sistem
     * ini. Menuliskannya "terkirim" akan jadi catatan yang berbohong — dan
     * jejak inilah yang dipakai menelusuri perselisihan.
     */
    #[Test]
    public function jejaknya_tidak_mengaku_terkirim(): void
    {
        [$orang, $b] = $this->dengan();

        $this->actingAs($orang)
            ->post(route('account.pendaftaran-layanan.follow-up', ['scopus_camp', $b->getKey()]));

        $kalimat = \App\PendaftaranJejak::milik('scopus_camp', (string) $b->getKey())
            ->where('aksi', 'follow-up')->first()->kalimat;

        $this->assertStringContainsString('dibuka', $kalimat);
        $this->assertStringNotContainsString('terkirim', $kalimat,
            'Jejaknya mengaku pesannya terkirim, padahal yang diketahui cuma tombolnya ditekan.');
    }

    #[Test]
    public function nomor_yang_tidak_terpakai_tidak_dicatat(): void
    {
        [$orang, $b] = $this->dengan(['telp' => '0812']);

        $this->actingAs($orang)
            ->post(route('account.pendaftaran-layanan.follow-up', ['scopus_camp', $b->getKey()]))
            ->assertSessionHas('error');

        $this->assertSame(0, \App\PendaftaranJejak::milik('scopus_camp', (string) $b->getKey())
            ->where('aksi', 'follow-up')->count(),
            'Jejak follow up tertulis padahal percakapannya tidak pernah bisa dibuka.');
    }

    /**
     * Pengirimnya "admin Rumah Scopus", bukan panitia satu layanan.
     *
     * Satu orang memegang kelima layanan, dan memperkenalkan diri sebagai
     * panitia satu layanan membuat percakapan berikutnya — yang sering soal
     * pendaftaran lain milik orang yang sama — terbaca seperti datang dari
     * orang yang berbeda.
     */
    #[Test]
    public function memperkenalkan_diri_sebagai_admin_rumah_scopus(): void
    {
        $pesan = PesanWaPendaftaran::tindakLanjut($this->baris('diproses'));

        $this->assertStringContainsString('Perkenalkan, saya admin Rumah Scopus.', $pesan);
        $this->assertStringNotContainsString('panitia Scopus Camp', $pesan,
            'Masih memperkenalkan diri sebagai panitia satu layanan.');
    }

    /**
     * Rincian lengkap: layanan, angkatan KE-BERAPA, tanggal mulai, jumlah
     * orang, nomor, dan nominalnya.
     *
     * "Angkatan Penuh YMO7ZY" tidak menunjuk apa pun bagi yang menerimanya —
     * nama itu dipakai panitia, sedangkan yang diingat pendaftar nomor
     * angkatannya.
     */
    #[Test]
    public function rinciannya_menyebut_angkatan_dan_jumlah_orangnya(): void
    {
        [$orang, $b] = $this->dengan(['jumlah_pendaftar' => '3']);

        Pendaftaran::lupakan();
        $union = Pendaftaran::kueri()
            ->where('layanan', 'scopus_camp')->where('id', $b->getKey())->first();

        $pesan = PesanWaPendaftaran::tindakLanjut($union, Pendaftaran::sesiBaris($union));

        $this->assertStringContainsString('Layanan: *Scopus Camp*', $pesan);
        $this->assertStringContainsString('Angkatan ke-77: Angkatan Uji', $pesan,
            'Angkatan ke-berapa tidak disebut; namanya saja tidak menunjuk apa pun '
            . 'bagi yang menerimanya.');
        $this->assertStringContainsString('Mulai: ', $pesan, 'Tanggal mulainya tidak disebut.');
        $this->assertStringContainsString('Jumlah peserta: *3 orang*', $pesan,
            'Jumlah orang yang didaftarkan tidak disebut.');
        // Nomornya dicetak huruf besar supaya mudah dibacakan lewat telepon;
        // dibandingkan apa adanya, penjaganya merah pada pesan yang benar.
        $this->assertStringContainsString(strtoupper($b->id_transaksi), $pesan);
    }

    /** Pendaftaran satu orang tidak perlu diberi tahu bahwa ia satu orang. */
    #[Test]
    public function satu_orang_tidak_disebut_jumlahnya(): void
    {
        [$orang, $b] = $this->dengan(['jumlah_pendaftar' => '1']);

        Pendaftaran::lupakan();
        $union = Pendaftaran::kueri()
            ->where('layanan', 'scopus_camp')->where('id', $b->getKey())->first();

        $this->assertStringNotContainsString('Jumlah peserta',
            PesanWaPendaftaran::tindakLanjut($union));
    }

    /**
     * Yang buktinya SUDAH diunggah tidak ditagih lagi.
     *
     * Keadaannya memang masih 'menunggu' — statusnya belum lunas — tetapi
     * uangnya sudah dikirim dan yang ditunggu panitia, bukan dia. Terukur
     * saat mencobanya: pesan berbunyi "bukti Anda sedang diperiksa" lalu
     * menutup dengan "kalau sudah transfer, kirimkan buktinya" — dua
     * kalimat yang saling membantah dalam satu pesan.
     */
    #[Test]
    public function yang_buktinya_sudah_masuk_tidak_ditagih_lagi(): void
    {
        [$orang, $b] = $this->dengan(['gambar' => 'bukti/scopus_camp/ada.webp']);

        Pendaftaran::lupakan();
        $union = Pendaftaran::kueri()
            ->where('layanan', 'scopus_camp')->where('id', $b->getKey())->first();

        $pesan = PesanWaPendaftaran::tindakLanjut($union);

        /*
         * Dipatok pada kalimat penagihan yang BERLAKU SEKARANG. Patokan
         * lamanya menunjuk "kirimkan bukti transfernya" — kalimat yang
         * sudah dibuang seluruhnya — jadi ia lulus tanpa menguji apa pun.
         */
        $this->assertStringNotContainsString('tautan pembayaran', $pesan,
            'Orang yang sudah membayar masih ditagih.');
        $this->assertStringNotContainsString('Nominal yang ditunggu', $pesan,
            'Nominalnya masih disebut sebagai tagihan.');
        $this->assertStringContainsString('tidak perlu mengirimkan apa pun lagi', $pesan);
    }

    /** Yang belum mengunggah bukti TETAP ditagih — penjaga di atas tidak boleh
     *  mematikan tagihan untuk semua orang. */
    #[Test]
    public function yang_belum_mengunggah_bukti_tetap_ditagih(): void
    {
        [$orang, $b] = $this->dengan();

        Pendaftaran::lupakan();
        $union = Pendaftaran::kueri()
            ->where('layanan', 'scopus_camp')->where('id', $b->getKey())->first();

        $this->assertStringContainsString('tautan pembayaran',
            PesanWaPendaftaran::tindakLanjut($union));
    }

    /**
     * Lewat pembayaran daring tidak ada bukti yang perlu dikirim.
     *
     * Gatewaynya yang mengabari sistem. Menyuruh orang mengirim tangkapan
     * layar ke WhatsApp membuat ia mengira pembayarannya belum sah sampai
     * ia melakukannya — dan panitia menerima kiriman yang tidak pernah
     * dipakai siapa pun.
     *
     * Hari ini ketiga ratus barisnya masih 'transfer', jadi kekeliruan ini
     * belum menggigit — tetapi ia akan mengenai SELURUH pendaftar di hari
     * pembayaran daring dinyalakan, dan hari itu tidak ada ujinya kalau
     * aturannya cuma anggapan.
     */
    #[Test]
    public function pembayaran_daring_tidak_disuruh_mengirim_bukti(): void
    {
        $pesan = PesanWaPendaftaran::tindakLanjut($this->baris('diproses', ['cara_bayar' => 'doku']));

        $this->assertStringContainsString('tautan pembayaran yang kami kirimkan', $pesan);
        $this->assertStringContainsString('tidak perlu mengirimkan bukti pembayaran', $pesan);
    }

    /**
     * NAMA GATEWAYNYA tidak pernah disebut ke pendaftar.
     *
     * Itu urusan internal: yang perlu ia tahu cuma bahwa pembayarannya
     * diselesaikan lewat tautan yang dikirim sistem. Disebut namanya, pesan
     * ini berbohong pada hari gatewaynya diganti — dan yang menerimanya
     * akan mencari halaman yang sudah tidak dipakai lagi.
     */
    #[Test]
    public function nama_gatewaynya_tidak_disebut(): void
    {
        foreach (['doku', 'transfer', 'tunai', ''] as $cara) {
            $pesan = PesanWaPendaftaran::tindakLanjut(
                $this->baris('diproses', ['cara_bayar' => $cara])
            );

            $this->assertStringNotContainsStringIgnoringCase('doku', $pesan,
                "Nama gatewaynya bocor ke pesan untuk cara bayar '{$cara}'.");
        }
    }

    /** Bayar di tempat: tidak ada yang perlu ditransfer sama sekali. */
    #[Test]
    public function bayar_di_tempat_tidak_disuruh_transfer(): void
    {
        $pesan = PesanWaPendaftaran::tindakLanjut($this->baris('diproses', ['cara_bayar' => 'tunai']));

        $this->assertStringNotContainsString('transfer', strtolower($pesan),
            'Pembayar tunai disuruh transfer.');
        $this->assertStringContainsString('diserahkan langsung kepada panitia', $pesan);
    }

    /**
     * Baris bercara bayar 'transfer' pun TIDAK lagi diminta bukti manual.
     *
     * Pembayarannya seluruhnya lewat gerbang pembayaran. Ketiga ratus tujuh
     * baris yang ada hari ini masih bernilai 'transfer' — nilai bawaan dari
     * zaman rekening — jadi justru nilai itulah yang diterima hampir semua
     * orang. Dibiarkan menagih bukti transfer, perubahan ini tidak mengenai
     * siapa pun.
     */
    #[Test]
    public function baris_transfer_lama_tidak_diminta_bukti_manual(): void
    {
        $pesan = PesanWaPendaftaran::tindakLanjut($this->baris('diproses', ['cara_bayar' => 'transfer']));

        $this->assertStringContainsString('tautan pembayaran yang kami kirimkan', $pesan);
        $this->assertStringNotContainsString('bukti transfer', $pesan);
    }

    /**
     * TIDAK ADA satu pun pesan yang menyuruh transfer manual, apa pun
     * statusnya dan apa pun cara bayarnya.
     *
     * Disapu seluruh kombinasinya, bukan satu contoh: kalimat penagihannya
     * satu, tetapi kalimat keadaan dan peringatannya bercabang — dan cabang
     * yang terlewat hanya terlihat pada status yang jarang dipakai panitia.
     */
    #[Test]
    public function tidak_ada_pesan_yang_menyuruh_transfer_manual(): void
    {
        /*
         * "rekening" dan "bukti transfer" adalah bahasa pencocokan mutasi
         * dengan tangan. "angka terakhir" peringatan kode uniknya: lewat
         * gerbang pembayaran nominalnya terpatok di tautannya, tidak ada
         * yang bisa dibulatkan.
         */
        $manual = ['bukti transfer', 'rekening', 'angka terakhir', 'kode unik', 'ditransfer'];

        $tercela = [];

        foreach (KabarStatusPendaftaran::semuaStatus() as $status) {
            foreach (['transfer', 'doku', 'tunai', ''] as $caraBayar) {
                $pesan = PesanWaPendaftaran::tindakLanjut(
                    $this->baris($status, ['cara_bayar' => $caraBayar])
                );

                foreach ($manual as $kata) {
                    if (str_contains($pesan, $kata)) {
                        $tercela[] = $status . ' / ' . ($caraBayar ?: '(kosong)') . ': "' . $kata . '"';
                    }
                }
            }
        }

        $this->assertSame([], $tercela,
            "Pesan berikut masih berbahasa transfer manual:\n- " . implode("\n- ", $tercela) . "\n");
    }

    // ------------------------------------------------------------- pembantu

    /**
     * Ragam bahasanya RESMI di seluruh lima belas statusnya.
     *
     * Yang menerima pesan ini sebagian dosen dan peneliti, dan isinya
     * menagih uang. Ditulis santai, tagihannya terbaca seperti pesan asal —
     * bukan pemberitahuan lembaga yang perlu ditindaklanjuti.
     *
     * Disapu seluruh statusnya, bukan satu contoh: kalimat keadaannya datang
     * dari katalog yang dipakai bersama suratnya, dan satu kalimat santai
     * yang terselip di sana ikut terkirim lewat WhatsApp tanpa ada yang
     * melihatnya. Terukur sebelum ini: sapaan dan penutupnya sudah resmi,
     * tetapi kalimat tengahnya masih "sudah masuk dan sekarang menunggu".
     */
    #[Test]
    public function ragam_bahasanya_resmi_di_semua_status(): void
    {
        /*
         * Penanda santai, masing-masing punya pasangan resminya:
         * "Halo" -> "Yth. Bapak/Ibu", "Kalau" -> "Apabila",
         * ", jadi" -> "sehingga", "bisa" -> "dapat".
         */
        $santai = ['Halo', 'Kalau ', ', jadi ', 'nggak', 'Oke', '!'];

        foreach (KabarStatusPendaftaran::semuaStatus() as $status) {
            $pesan = PesanWaPendaftaran::tindakLanjut($this->baris($status));

            $this->assertStringStartsWith('Yth. Bapak/Ibu', $pesan,
                "Pesan status {$status} tidak dibuka sapaan resmi.");

            foreach ($santai as $kata) {
                $this->assertStringNotContainsString($kata, $pesan,
                    "Pesan status {$status} memuat ragam santai \"{$kata}\".");
            }

            /*
             * Emoji dibuang seluruhnya. Dulu penutupnya ditutup emoji, dan
             * itu satu-satunya alasan tombol ini dulu memakai api.whatsapp.com
             * — sekarang alasannya tinggal tanda baca, tetapi emojinya tidak
             * boleh kembali lewat kalimat baru yang ditambahkan nanti.
             */
            $this->assertSame(0,
                preg_match('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $pesan),
                "Pesan status {$status} masih memuat emoji.");
        }
    }

    /**
     * Nama yang diisi seluruhnya huruf kecil dibesarkan awal katanya.
     *
     * Borang pendaftarannya tidak memaksa huruf besar. Hasilnya sapaan
     * "Yth. Bapak/Ibu joko," — justru di kalimat yang paling resmi dari
     * seluruh pesan ini.
     */
    #[Test]
    public function nama_huruf_kecil_dibesarkan_di_sapaannya(): void
    {
        $pesan = PesanWaPendaftaran::tindakLanjut(
            $this->baris('diproses', ['nama_orang' => 'joko susilo'])
        );

        $this->assertStringStartsWith('Yth. Bapak/Ibu Joko Susilo,', $pesan);
    }

    /**
     * Nama yang SUDAH memuat huruf besar dibiarkan utuh.
     *
     * Di dalamnya ada singkatan dan gelar — "M. Rizki", "Prof. H." — yang
     * rusak kalau dibesarkan-kecilkan ulang. Penjaga ini yang membatasi
     * perbaikan di atas supaya tidak menyapu semua nama.
     */
    #[Test]
    public function nama_bergelar_tidak_diubah_hurufnya(): void
    {
        $pesan = PesanWaPendaftaran::tindakLanjut(
            $this->baris('diproses', ['nama_orang' => 'M. Rizki Al-Hakim, S.Si'])
        );

        $this->assertStringStartsWith('Yth. Bapak/Ibu M. Rizki Al-Hakim, S.Si,', $pesan);
    }

    /**
     * Peserta yang sudah lunas diberi tautan grupnya.
     *
     * Sebelum ini panitia menempelkannya dengan tangan sesudah menekan
     * Follow up — dan tautan grup yang salah tempel mengirim peserta satu
     * angkatan ke grup angkatan lain.
     */
    #[Test]
    public function peserta_lunas_diberi_tautan_grupnya(): void
    {
        $pesan = PesanWaPendaftaran::tindakLanjut($this->baris('Pendaftaran Diterima', [
            'grup' => 'https://chat.whatsapp.com/AbCdEf123456',
        ]));

        $this->assertStringContainsString('https://chat.whatsapp.com/AbCdEf123456', $pesan);

        /*
         * Alamatnya BERDIRI SENDIRI di barisnya, tanpa titik di ujungnya.
         * WhatsApp menebak batas tautan dari spasi, jadi tanda baca yang
         * menempel ikut termakan ke dalam alamat — yang ditekan pendaftar
         * lalu alamat yang tidak ada.
         */
        $this->assertContains('https://chat.whatsapp.com/AbCdEf123456',
            explode("\n", $pesan),
            'Tautannya tidak berdiri sendiri di satu baris; tanda baca di '
            . 'sebelahnya akan ikut termakan ke dalam alamat.');
    }

    /** Yang dijadwalkan ulang sudah membayar — cuma pindah tanggal. */
    #[Test]
    public function yang_dijadwalkan_ulang_tetap_diberi_grupnya(): void
    {
        $pesan = PesanWaPendaftaran::tindakLanjut($this->baris('Pendaftaran Reschedule', [
            'grup' => 'https://chat.whatsapp.com/AbCdEf123456',
        ]));

        $this->assertStringContainsString('https://chat.whatsapp.com/AbCdEf123456', $pesan);
    }

    /**
     * Yang BELUM membayar, yang batal, dan yang dananya dikembalikan tidak
     * diberi tautannya.
     *
     * Grupnya akan terisi orang yang kursinya belum ada, dan mengeluarkan
     * mereka kembali satu per satu pekerjaan panitia yang tidak perlu ada.
     */
    #[Test]
    public function yang_belum_jadi_peserta_tidak_diberi_grupnya(): void
    {
        foreach (['diproses', 'Pendaftaran Dibatalkan', 'Pendaftaran Refund',
            'Pendaftaran Ditolak', 'expired'] as $status) {
            $pesan = PesanWaPendaftaran::tindakLanjut($this->baris($status, [
                'grup' => 'https://chat.whatsapp.com/AbCdEf123456',
            ]));

            $this->assertStringNotContainsString('chat.whatsapp.com', $pesan,
                "Status {$status} diberi tautan grup padahal belum/bukan peserta.");
        }
    }

    /**
     * Isian yang BUKAN tautan tidak pernah tercetak.
     *
     * Kolomnya varchar bebas, dan terukur di basis data ia lebih sering
     * berisi catatan panitia daripada alamat: 153 dari 228 isian berbunyi
     * "menyusul" atau "link nyusul". Dicetak apa adanya, yang terkirim
     * adalah "Silakan bergabung ke grup peserta melalui tautan berikut:
     * menyusul".
     */
    #[Test]
    public function isian_yang_bukan_tautan_tidak_pernah_tercetak(): void
    {
        $bukanTautan = [
            'menyusul', 'Menyusul', 'link nyusul', '-', 'belum ada',
            'https://drive.google.com/file/d/abc',
            'wa.me/628123456789',
            'chat.whatsapp.com/AbCdEf123456',
            'http://chat.whatsapp.com/AbCdEf123456',
        ];

        foreach ($bukanTautan as $isi) {
            $pesan = PesanWaPendaftaran::tindakLanjut(
                $this->baris('Pendaftaran Diterima', ['grup' => $isi])
            );

            $this->assertStringNotContainsString('Silakan bergabung', $pesan,
                "Isian \"{$isi}\" diperlakukan sebagai tautan grup.");

            /*
             * Isian pendek dilewati di tuntutan kedua. '-' satu huruf memang
             * wajar muncul di pesan yang benar — "Angkatan ke-6" punya satu —
             * jadi menuntutnya tidak ada berarti merah pada pesan yang tidak
             * bersalah. Tuntutan pertama di atas sudah menutup keduanya:
             * tanpa baris ajakannya, tidak ada tautan yang tercetak.
             */
            if (mb_strlen($isi) >= 5) {
                $this->assertStringNotContainsString($isi, $pesan,
                    "Isian \"{$isi}\" ikut tercetak di pesannya.");
            }
        }
    }

    private function baris(string $status, array $ubah = []): object
    {
        return (object) array_merge([
            'nama_orang' => 'Budi Santoso', 'nomor' => 'camp-0007', 'total' => 5500750,
            'kode_unik' => 750, 'status' => $status, 'layanan' => 'scopus_camp',
        ], $ubah);
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp} */
    private function dengan(array $ubah = []): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $t = Str::random(8);

        $u = User::create([
            'full_name' => 'Panitia Uji', 'username' => 'tdl_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Uji ' . $t,
            // nama_ke WAJIB diisi: dari sinilah "angkatan ke-berapa" dibaca,
            // dan angkatan sungguhan selalu punya nomornya.
            'nama_ke' => '77',
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ]);

        $b = PendaftaranScopusCamp::create(array_merge([
            'id_transaksi' => 'T-' . $t, 'kategori_id' => $angkatan->id,
            'nama' => 'Peserta ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0001', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => '5500000', 'status' => 'diproses',
        ], $ubah));

        return [$u, $b];
    }
}
