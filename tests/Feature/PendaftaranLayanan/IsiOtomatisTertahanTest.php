<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\CategoriesOnlineTraining;
use App\CategoriesScopusCamp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pengisian otomatis yang DITOLAK harus kelihatan, bukan diam.
 *
 * ---------------------------------------------------------------------------
 * Apa yang terjadi
 * ---------------------------------------------------------------------------
 *
 * Alamat /cari-pendaftar dibatasi tiga lapis sekaligus — 4 per menit per IP,
 * 25 per hari per IP, dan 8 PER HARI PER NOMOR. Yang terakhir itu yang paling
 * mudah menggigit orang sungguhan: tiap kali borangnya dimuat ulang, nomor
 * yang sama ditanyakan sekali lagi, dan penghitungnya tidak peduli bahwa
 * jawabannya sama persis dengan yang tadi.
 *
 * Terukur 2 Nov 2026 lewat Chrome DevTools pada borang yang sungguhan:
 *
 *     POST /Class-Online/cari-pendaftar  ->  429 Too Many Requests
 *     penghitung per-hari-nomor          ->  8 dari 8, berlaku sampai besok
 *     penghitung per-hari-IP             ->  23 dari 25
 *
 * Dan DI LAYAR tidak ada apa pun. Tiga isian tetap kosong, tanpa pita, tanpa
 * pesan, tanpa galat di konsol — sebab skripnya memeriksa `r.ok ? r.json() :
 * null` lalu berhenti. Pemiliknya menyimpulkan fiturnya rusak dan
 * menanyakannya untuk Class Online MAUPUN Class Offline sekaligus; keduanya
 * memang sama-sama diam, dan keduanya memang sedang ditolak.
 *
 * ---------------------------------------------------------------------------
 * Kenapa dijaga dari sumbernya
 * ---------------------------------------------------------------------------
 *
 * Yang rusak perilaku JavaScript, dan PHPUnit tidak menjalankannya. Yang bisa
 * dijangkau dari sini markah yang dikirim ke peramban — jadi itu yang dipin:
 * cabang 429-nya ada, perakit pitanya ada, dan nomor yang gagal dibuka lagi
 * untuk dicoba ulang. Ketiganya yang hilang saat cacatnya ada.
 *
 * Perilakunya sendiri dibuktikan lewat Chrome DevTools, bukan di sini.
 */
class IsiOtomatisTertahanTest extends TestCase
{
    use DatabaseTransactions;

    /** @return array<string, string> alamat borang tiap layanan bergerbang */
    private function borang(): array
    {
        $camp = CategoriesScopusCamp::create([
            'layanan' => 'scopus_camp',
            'token' => Str::random(30),
            'nama' => 'UJI Tertahan Camp ' . Str::random(6),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(12)->toDateString(),
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'biaya' => 5500000, 'status' => 'active',
        ]);

        $online = CategoriesOnlineTraining::create([
            'token' => Str::random(30),
            'nama' => 'UJI Tertahan CO ' . Str::random(6),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(40)->toDateString(),
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'biaya' => 3500000, 'status' => 'active',
        ]);

        return [
            'Class Offline' => route('public.scopuscamp.formpendaftaran', $camp->getKey()),
            'Class Online' => route('public.onlinetraining.formpendaftaran', $online->getKey()),
        ];
    }

    #[Test]
    public function kedua_borang_menampilkan_kabar_saat_pencariannya_ditolak(): void
    {
        config()->set('services.doku.client_id', 'BRN-UJI-TAHAN');
        config()->set('services.doku.secret_key', 'SK-UJI-TAHAN');

        foreach ($this->borang() as $layanan => $alamat) {
            $isi = (string) $this->get($alamat)->assertOk()->getContent();

            // Cabang penolakannya ADA. Tanpa ini 429 jatuh ke `! r.ok` dan
            // berakhir sebagai null tanpa sepatah kata pun di layar.
            $this->assertStringContainsString('r.status === 429', $isi, $layanan);

            // Perakit pitanya ada, beserta kalimat yang dibaca orangnya.
            $this->assertStringContainsString('tampilkanTertahan', $isi, $layanan);
            $this->assertStringContainsString('Pengisian otomatis sedang dibatasi', $isi, $layanan);

            // Warnanya BERBEDA dari pita "sudah kami isikan": dua kabar yang
            // berlawanan arti tidak boleh terlihat sama.
            $this->assertStringContainsString('daf-kabar-tertahan', $isi, $layanan);
        }
    }

    #[Test]
    public function nomor_yang_gagal_dibuka_lagi_untuk_dicoba_ulang(): void
    {
        /*
         * `nomorTerakhir` disetel SEBELUM fetch, supaya nomor yang sama tidak
         * ditanyakan dua kali. Kalau permintaannya GAGAL dan penandanya
         * dibiarkan terisi, halaman itu tidak akan pernah mencoba lagi —
         * bahkan sesudah batas per menitnya pulih sendiri semenit kemudian.
         * Orangnya harus memuat ulang halamannya untuk mendapat satu
         * kesempatan lagi, dan memuat ulang itu justru yang menghabiskan
         * jatah per-harinya.
         */
        config()->set('services.doku.client_id', 'BRN-UJI-TAHAN');
        config()->set('services.doku.secret_key', 'SK-UJI-TAHAN');

        foreach ($this->borang() as $layanan => $alamat) {
            $isi = (string) $this->get($alamat)->assertOk()->getContent();

            /*
             * Dicari DI DALAM cabang 429-nya, bukan di mana saja dalam
             * berkas. Versi pertama uji ini cuma menghitung berapa kali
             * "nomorTerakhir = '';" muncul, dan terbukti HOLLOW: cacatnya
             * dikembalikan lewat mutasi — seluruh cabang 429 dibuang — dan
             * uji ini tetap hijau, sebab dua kemunculan lain (nomor terlalu
             * pendek, dan .catch jaringan) sudah cukup memenuhi hitungannya.
             */
            $awal = mb_strpos($isi, 'r.status === 429');

            $this->assertNotFalse($awal, $layanan . ': cabang penolakannya tidak ada.');

            $cabang = mb_substr($isi, $awal, 400);

            $this->assertStringContainsString("nomorTerakhir = '';", $cabang,
                $layanan . ': penanda nomor tidak dibuka lagi sesudah ditolak.');
            $this->assertStringContainsString('tampilkanTertahan()', $cabang,
                $layanan . ': penolakannya tidak memunculkan kabar apa pun.');
        }
    }

    #[Test]
    public function kalimatnya_tidak_membocorkan_apakah_nomornya_terdaftar(): void
    {
        /*
         * 429 terbit untuk nomor yang terdaftar MAUPUN yang tidak. Kalimat
         * yang menyebut "data Anda tidak ditemukan" atau sebaliknya akan
         * mengubah pita ini jadi alat memeriksa keanggotaan — persis yang
         * ditutup jawaban seragam di cekKodeDiskon() dan tautanSaya().
         */
        config()->set('services.doku.client_id', 'BRN-UJI-TAHAN');
        config()->set('services.doku.secret_key', 'SK-UJI-TAHAN');

        foreach ($this->borang() as $layanan => $alamat) {
            $isi = (string) $this->get($alamat)->assertOk()->getContent();

            // Dicari lewat perakit pitanya, supaya uji ini ikut merah kalau
            // perakitnya dibuang sementara kalimatnya tertinggal sebagai
            // teks mati di suatu tempat.
            $this->assertStringContainsString('function tampilkanTertahan', $isi, $layanan);

            $awal = mb_strpos($isi, 'Pengisian otomatis sedang dibatasi');
            $this->assertNotFalse($awal, $layanan);

            $kalimat = mb_strtolower(mb_substr($isi, $awal, 400));

            foreach (['tidak ditemukan', 'belum terdaftar', 'sudah terdaftar'] as $bocor) {
                $this->assertStringNotContainsString($bocor, $kalimat, $layanan . ' / ' . $bocor);
            }
        }
    }
}
