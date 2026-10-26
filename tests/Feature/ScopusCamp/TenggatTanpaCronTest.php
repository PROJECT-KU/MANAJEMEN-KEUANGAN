<?php

namespace Tests\Feature\ScopusCamp;

use App\CategoriesScopusCamp;
use App\Mail\ScopusCampKedaluwarsaMail;
use App\PendaftaranScopusCamp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tenggat bayar ditegakkan TANPA menunggu cron.
 *
 * Sampai 26 Okt 2026 satu-satunya yang melepas kursi Scopus Camp adalah
 * perintah terjadwal — dan cron hPanel di peladen ini belum terpasang. Jadi
 * tenggat 60 menit yang dipajang halaman status tidak pernah ditegakkan
 * siapa pun: kursinya tertahan selamanya, angkatan yang terlihat penuh
 * sebenarnya kosong, dan pendaftarnya melihat tombol bayar yang menunjuk
 * tagihan mati.
 *
 * Yang dijaga di sini: jalur web ikut menegakkannya, dan kejadian itu
 * dikabari lewat DUA saluran — bukan WhatsApp saja, yang perangkatnya
 * sedang putus.
 */
class TenggatTanpaCronTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Cache::flush();

        config()->set('services.doku.client_id', 'BRN-UJI-TENGGAT');
        config()->set('services.doku.secret_key', 'SK-UJI-TENGGAT');

        // Gerbangnya tidak ditanyai sungguhan; jawabannya 'belum dibayar'.
        Http::fake(['*' => Http::response(['transaction' => ['status' => 'PENDING']], 200)]);
    }

    private function angkatan(array $ganti = []): CategoriesScopusCamp
    {
        return CategoriesScopusCamp::create(array_merge([
            'layanan' => 'scopus_camp', 'token' => Str::random(30),
            'nama' => 'UJI Tenggat ' . Str::random(5),
            'nama_ke' => (string) random_int(4000, 4999),
            'mulai' => now()->addDays(20)->toDateString(),
            'selesai' => now()->addDays(22)->toDateString(),
            'total_kuota' => 20, 'sisa_kuota' => 18,
            'biaya' => 5500000, 'status' => 'active',
        ], $ganti));
    }

    private function lewatTenggat(CategoriesScopusCamp $a, array $ganti = []): PendaftaranScopusCamp
    {
        return PendaftaranScopusCamp::create(array_merge([
            'token' => Str::random(30),
            'id_transaksi' => 'CAMP-TGT-' . Str::upper(Str::random(6)),
            'kategori_id' => $a->getKey(),
            'nama' => 'Pendaftar Tenggat',
            'email' => 'tgt' . Str::random(5) . '@contoh.test',
            'telp' => '081234567890', 'jumlah_pendaftar' => 2,
            'total_pembayaran' => '11000000', 'cara_bayar' => 'doku',
            'status' => 'diproses', 'bayar_status' => 'menunggu',
            'bayar_url' => 'https://staging.doku.com/checkout-link-v2/MATI',
            'kedaluwarsa_pada' => now()->subHours(2),
        ], $ganti));
    }

    #[Test]
    public function membuka_halaman_statusnya_sendiri_melepas_kursinya(): void
    {
        /*
         * Yang paling sering terjadi: orangnya kembali ke halaman statusnya
         * berjam-jam kemudian. Tanpa ini ia melihat "Tinggal satu langkah
         * lagi" beserta tombol bayar yang mengantar ke halaman galat DOKU —
         * dan menyalahkan dirinya sendiri.
         */
        $a = $this->angkatan();
        $p = $this->lewatTenggat($a);

        $isi = $this->get(route('public.scopuscamp.status', $p->getKey()))
            ->assertOk()->getContent();

        $this->assertSame('Pendaftaran Dibatalkan', $p->refresh()->status,
            'Tenggatnya tidak ditegakkan; kursinya tertahan menunggu cron yang '
            . 'belum terpasang.');

        $this->assertStringContainsString('Kursinya sudah kami lepas', $isi,
            'Halaman yang sedang ia buka tidak menampilkan keadaan sebenarnya.');

        $this->assertStringNotContainsString('MATI', $isi,
            'Tombol bayarnya masih menunjuk tagihan yang sudah mati.');
    }

    #[Test]
    public function kursinya_benar_benar_kembali_ke_kuota(): void
    {
        /*
         * Inti seluruh perkaranya: kursi yang tidak kembali membuat angkatan
         * terlihat penuh padahal kosong, dan pendaftar berikutnya ditolak
         * untuk tempat yang sebenarnya ada.
         *
         * DIHITUNG ULANG, bukan ditambahkan: penambahan yang terjadi dua kali
         * membuka kursi yang tidak ada.
         */
        $a = $this->angkatan(['total_kuota' => 20, 'sisa_kuota' => 18]);
        $p = $this->lewatTenggat($a);

        $this->get(route('public.scopuscamp.status', $p->getKey()))->assertOk();

        $this->assertSame(20, (int) $a->refresh()->sisa_kuota,
            'Kursinya tidak kembali ke kuota angkatannya.');
    }

    #[Test]
    public function dua_kali_dibuka_tidak_melepas_kursi_dua_kali(): void
    {
        /*
         * Halaman ini ditanyai pollernya sendiri tiap beberapa detik, dan
         * perintah terjadwal bisa menyapu bersamaan. Dilepas dua kali,
         * kuotanya membuka kursi yang sebenarnya tidak ada — dan pendaftar
         * yang mengisinya datang ke tempat yang sudah penuh.
         */
        $a = $this->angkatan(['total_kuota' => 20, 'sisa_kuota' => 18]);
        $p = $this->lewatTenggat($a);

        for ($i = 0; $i < 3; $i++) {
            $this->get(route('public.scopuscamp.status', $p->getKey()))->assertOk();
        }

        $this->assertSame(20, (int) $a->refresh()->sisa_kuota,
            'Kursinya dilepas lebih dari sekali; kuotanya membuka tempat yang tidak ada.');
    }

    #[Test]
    public function halaman_angkatan_ikut_menegakkan_tenggatnya(): void
    {
        /*
         * Tenggatnya ditegakkan juga oleh halaman yang dibuka CALON pendaftar
         * — bukan cuma oleh pemilik pendaftaran yang kedaluwarsa. Itu yang
         * membuat kursinya kembali walau pemiliknya sendiri tidak pernah
         * kembali ke halaman statusnya.
         *
         * DIPATOK KE HALAMAN ANGKATANNYA, bukan halaman daftar. Halaman
         * daftar memajang dua belas angkatan terdekat dari 343 yang ada di
         * basis data ini, jadi angkatan uji yang mulainya 20 hari lagi belum
         * tentu masuk — ujinya akan merah atau hijau tergantung data yang
         * kebetulan ada. Halaman angkatannya selalu memuat angkatan itu.
         *
         * Halaman daftar memakai rakitan yang sama, dengan daftar kunci
         * angkatan yang sedang dipajangnya.
         */
        $a = $this->angkatan();
        $p = $this->lewatTenggat($a);

        $this->get(route('public.scopuscamp.Selengkapnya', $a->getKey()))->assertOk();

        $this->assertSame('Pendaftaran Dibatalkan', $p->refresh()->status,
            'Halaman angkatan tidak menegakkan tenggat pendaftaran di dalamnya.');

        $this->assertSame(20, (int) $a->refresh()->sisa_kuota,
            'Sisa kursi yang dipajang halaman itu masih angka lama.');
    }

    #[Test]
    public function yang_belum_lewat_tenggatnya_tidak_disentuh(): void
    {
        /*
         * Sisi sebaliknya, dan ia yang paling mahal kalau salah: melepas
         * kursi orang yang masih punya waktu berarti ia kehilangan tempatnya
         * di tengah proses membayar — kadang sesudah uangnya keluar.
         */
        $a = $this->angkatan();
        $p = $this->lewatTenggat($a, ['kedaluwarsa_pada' => now()->addHour()]);

        $this->get(route('public.scopuscamp.status', $p->getKey()))->assertOk();
        $this->get(route('public.scopuscamp.index'))->assertOk();

        $this->assertSame('diproses', $p->refresh()->status,
            'Kursi yang tenggatnya belum lewat ikut dilepas.');
    }

    #[Test]
    public function yang_sudah_bayar_tidak_pernah_dilepas(): void
    {
        /*
         * Pembayaran yang masuk beberapa detik sebelum tenggatnya — atau yang
         * pemberitahuannya terlambat — tidak boleh kehilangan kursinya.
         * Saringannya karena itu menuntut `bayar_pada` kosong DAN statusnya
         * masih status awal.
         */
        $a = $this->angkatan();
        $p = $this->lewatTenggat($a, ['bayar_pada' => now()->subHour()]);

        $this->get(route('public.scopuscamp.status', $p->getKey()))->assertOk();

        $this->assertSame('diproses', $p->refresh()->status,
            'Pendaftaran yang pembayarannya sudah tercatat ikut dilepas.');
    }

    #[Test]
    public function kursi_yang_dilepas_dikabari_lewat_surat_juga(): void
    {
        /*
         * Sampai 26 Okt 2026 kejadian ini hanya dikabari lewat WhatsApp — dan
         * perangkat Fonnte-nya sedang putus, jadi tidak dikabari ke mana pun.
         * Orangnya baru tahu kalau ia kebetulan membuka halaman statusnya
         * lagi, kadang berhari-hari kemudian, dan yang pertama ia kerjakan
         * adalah menghubungi panitia.
         *
         * Dua saluran: yang satu gagal tidak boleh membuat orangnya tidak
         * tahu sama sekali.
         */
        config()->set('services.fonnte.jeda_sapuan_detik', 0);

        $a = $this->angkatan();
        $p = $this->lewatTenggat($a);

        $this->artisan('scopus-camp:kedaluwarsakan')->assertSuccessful();

        $this->assertSame('Pendaftaran Dibatalkan', $p->refresh()->status);

        Mail::assertSent(ScopusCampKedaluwarsaMail::class, function ($surat) use ($p) {
            $isi = $surat->render();

            $this->assertStringContainsString($p->id_transaksi, $isi,
                'Suratnya tidak menyebut pendaftaran mana yang dimaksud.');

            /*
             * NADANYA TIDAK MENUDUH. Sebagian yang menerimanya SUDAH membayar
             * — mutasinya belum tercocokkan, atau pembayarannya masuk
             * beberapa menit sesudah tenggatnya. Surat yang menyatakan "Anda
             * tidak membayar" kepada orang yang baru mengirim lima juta
             * rupiah adalah cara tercepat kehilangan kepercayaannya.
             */
            $this->assertStringContainsString('Merasa sudah membayar', $isi,
                'Suratnya tidak memberi jalan bagi yang merasa sudah membayar.');

            return true;
        });
    }

    #[Test]
    public function surat_tidak_mengajak_daftar_ke_angkatan_yang_sudah_tutup(): void
    {
        /*
         * Mengajak mendaftar lagi ke angkatan yang sudah berjalan membuat
         * orangnya menekan tautan yang memantulkannya kembali tanpa
         * penjelasan — kabar buruk yang disusul jalan buntu.
         */
        config()->set('services.fonnte.jeda_sapuan_detik', 0);

        $a = $this->angkatan(['status' => 'non active']);
        $p = $this->lewatTenggat($a);

        $this->artisan('scopus-camp:kedaluwarsakan')->assertSuccessful();

        Mail::assertSent(ScopusCampKedaluwarsaMail::class, function ($surat) {
            $this->assertStringNotContainsString('Daftar ulang sekarang', $surat->render(),
                'Suratnya mengajak mendaftar ke angkatan yang sudah tidak menerima '
                . 'pendaftaran baru.');

            return true;
        });
    }

    #[Test]
    public function pencarian_nomor_dibatasi_per_hari_bukan_hanya_per_menit(): void
    {
        /*
         * Alamat isi-otomatis memulangkan NAMA, EMAIL, dan AFILIASI untuk
         * satu nomor telepon, tanpa login. Batas per menit saja tidak
         * menahan penyapuan LAMBAT: 10 per menit berarti 14.400 nomor sehari
         * dari satu alamat IP — cukup untuk memperkaya daftar nomor bocoran
         * jadi berkas identitas, dari basis data kita.
         *
         * Fiturnya tidak dicabut; harganya yang diubah.
         */
        $kena = false;

        for ($i = 0; $i < 40; $i++) {
            // Nomor BERBEDA tiap kali: yang diuji batas per IP, bukan batas
            // per nomor.
            $telp = '0812' . str_pad((string) (10000000 + $i), 8, '0', STR_PAD_LEFT);

            $jawab = $this->postJson(route('public.scopuscamp.caripendaftar'), ['telp' => $telp]);

            if ($jawab->status() === 429) {
                $kena = true;
                break;
            }

            // Jeda buatan supaya batas per MENIT tidak yang menggigit duluan.
            $this->travel(20)->seconds();
        }

        $this->assertTrue($kena,
            'Pencarian nomor tidak dibatasi per hari; penyapuan lambat lolos '
            . 'seluruhnya dan satu IP bisa memanen ribuan identitas sehari.');
    }
}
