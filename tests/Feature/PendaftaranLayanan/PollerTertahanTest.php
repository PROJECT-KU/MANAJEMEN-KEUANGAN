<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\CategoriesOnlineTraining;
use App\PendaftaranOnlineTraining;
use App\Support\PendaftaranSemuaLayanan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Penanya keadaan pembayaran: jatahnya per PENDAFTARAN, dan penolakannya
 * tidak diam.
 *
 * ---------------------------------------------------------------------------
 * Dua cacat yang saling menyembunyikan
 * ---------------------------------------------------------------------------
 *
 * Sampai 3 Nov 2026 pembatas `camp-keadaan` dihitung PER ALAMAT IP: 30 per
 * menit, dibagi bersama oleh Scopus Camp, Scopus Kafe, dan Class Online.
 * Pollernya sendiri 12 per menit per tab. Jadi tiga orang BERBEDA di
 * belakang satu alamat — jaringan kampus ber-NAT, yaitu persis sasaran
 * layanan ini — sudah 36 per menit, dan yang ketiga ditolak 429 karena
 * kelakuan dua orang yang tidak ia kenal.
 *
 * Cacat kedua membuat yang pertama tak terlihat: pollernya memeriksa
 * `jawab.ok ? jawab.json() : null` lalu berhenti. Halamannya karena itu
 * berkata "menunggu pembayaran" SELAMANYA walau uangnya sudah masuk, dan
 * orangnya menghubungi panitia menanyakan uang yang sebenarnya sudah
 * tercatat.
 */
class PollerTertahanTest extends TestCase
{
    use DatabaseTransactions;

    private function pendaftaran(): PendaftaranOnlineTraining
    {
        $a = CategoriesOnlineTraining::create([
            'token' => Str::random(30),
            'nama' => 'UJI Poller ' . Str::random(6),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(40)->toDateString(),
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'biaya' => 3500000, 'status' => 'active',
        ]);

        return PendaftaranOnlineTraining::create([
            'token' => Str::random(30),
            'id_transaksi' => PendaftaranSemuaLayanan::nomorBaru('online_training'),
            'kategori_id' => $a->getKey(),
            'email' => Str::lower(Str::random(10)) . '@contoh.test',
            'nama' => 'Penunggu Pembayaran',
            'telp' => '0813' . random_int(10000000, 99999999),
            'affiliasi' => 'Universitas Uji',
            'ppn' => '0', 'kode_unik' => '123',
            'jumlah_pendaftar' => 1, 'total_pembayaran' => '1875123',
            'status' => PendaftaranSemuaLayanan::statusAwal('online_training'),
            'cara_bayar' => 'doku',
            'kedaluwarsa_pada' => now()->addHour(),
        ]);
    }

    #[Test]
    public function jatahnya_tidak_lagi_dibagi_antar_pendaftaran(): void
    {
        /*
         * Inti perbaikannya. Dua pendaftaran yang BERBEDA — dua orang
         * berbeda — dari alamat IP yang sama tidak boleh saling
         * menghabiskan jatah.
         *
         * Terukur di peladen sungguhan sesudah perbaikan: 40 permintaan
         * pertama untuk satu pendaftaran menjawab 200, yang ke-41 menjawab
         * 429, dan pendaftaran LAIN dari alamat yang sama tetap 200.
         */
        $a = $this->pendaftaran();
        $b = $this->pendaftaran();

        $habis = false;

        for ($i = 0; $i < 60; $i++) {
            if ($this->getJson(route('public.onlinetraining.keadaan', $a->getKey()))->status() === 429) {
                $habis = true;
                break;
            }
        }

        $this->assertTrue($habis, 'Jatah per pendaftaran tidak pernah habis — pembatasnya tidak bekerja.');

        // Dan yang KEDUA tetap dilayani.
        $this->getJson(route('public.onlinetraining.keadaan', $b->getKey()))->assertOk();
    }

    #[Test]
    public function pollernya_mengabarkan_saat_ditolak_bukan_diam(): void
    {
        /*
         * Perilaku JavaScript, dan PHPUnit tidak menjalankannya. Yang bisa
         * dijangkau markah yang dikirim ke peramban, jadi itu yang dipin:
         * cabang 429-nya, penghentian penanyaan, dan perakit pitanya.
         *
         * Perilakunya sendiri dibuktikan lewat Chrome DevTools — jatah satu
         * pendaftaran dihabiskan lebih dulu, lalu halamannya dibuka:
         * pollernya menembak SEKALI, menerima 429, berhenti, dan pitanya
         * muncul beserta tombol "Muat ulang".
         */
        $p = $this->pendaftaran();

        $isi = (string) $this->get(route('public.onlinetraining.status', $p->getKey()))
            ->assertOk()->getContent();

        $awal = mb_strpos($isi, 'jawab.status === 429');

        $this->assertNotFalse($awal, 'Poller tidak mengenali penolakan 429.');

        $cabang = mb_substr($isi, $awal, 300);

        $this->assertStringContainsString('clearInterval(jam)', $cabang,
            'Poller terus menembak walau sudah ditolak.');
        $this->assertStringContainsString('kabarkanTertahan()', $cabang,
            'Penolakannya tidak memunculkan kabar apa pun.');

        // Pitanya memberi JALAN KELUAR, bukan cuma keluhan.
        $this->assertStringContainsString('function kabarkanTertahan', $isi);
        $this->assertStringContainsString('Muat ulang', $isi);
    }

    #[Test]
    public function ketiga_layanan_memakai_poller_yang_sama_sudah_diperbaiki(): void
    {
        /*
         * Pembatasnya dipakai bersama tiga layanan, jadi penolakannya bisa
         * muncul di ketiganya. Satu yang tertinggal akan diam persis seperti
         * sebelum perbaikan — dan yang tertinggal selalu yang tidak diuji.
         */
        foreach ([
            'resources/views/public/scopus_camp/status.blade.php',
            'resources/views/public/online_training/status.blade.php',
            'resources/views/public/scopus_kafe/partials/poller.blade.php',
        ] as $berkas) {
            $isi = (string) file_get_contents(base_path($berkas));

            $this->assertStringContainsString('jawab.status === 429', $isi, $berkas);
            $this->assertStringContainsString('function kabarkanTertahan', $isi, $berkas);
        }
    }
}
