<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\CategoriesOnlineTraining;
use App\CategoriesScopusCamp;
use App\Http\Controllers\Publict\PemberitahuanDokuController;
use App\PendaftaranOnlineTraining;
use App\PendaftaranScopusCamp;
use App\Support\PendaftaranSemuaLayanan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SATU alamat pemberitahuan DOKU untuk semua layanan.
 *
 * ---------------------------------------------------------------------------
 * Kenapa ini ada
 * ---------------------------------------------------------------------------
 *
 * Tiap layanan bergerbang punya alamat pemberitahuannya sendiri. Itu rapi di
 * kode dan SALAH di dasbor DOKU: dasbornya menyediakan satu kolom
 * "Notification URL" per KANAL pembayaran — Virtual Account satu, QRIS satu —
 * bukan satu per layanan. Keempat layanan kita memakai akun dan kanal yang
 * sama, jadi keempat alamat itu tidak mungkin didaftarkan bersamaan.
 *
 * Yang terpasang hanya satu, dan tiga layanan sisanya TIDAK PERNAH diberi
 * tahu. Gejalanya tidak terlihat sebagai galat: pembayarannya masih tersusul
 * saat halaman status dibuka, jadi yang membuka halamannya akhirnya lunas
 * juga — tetapi yang menutup tabnya sesudah membayar menunggu tanpa batas,
 * dan panitia melihat pendaftaran yang terlihat belum bayar padahal uangnya
 * sudah masuk.
 *
 * Ditemukan 8 Nov 2026 saat hendak mendaftarkan keempat alamat itu di dasbor
 * produksi.
 */
class PemberitahuanSatuAlamatTest extends TestCase
{
    use DatabaseTransactions;

    private const KLIEN = 'BRN-UJI-SATU';
    private const RAHASIA = 'SK-UJI-SATU';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', self::KLIEN);
        config()->set('services.doku.secret_key', self::RAHASIA);

        Mail::fake();
    }

    // ----------------------------------------------------------- pemilahan

    #[Test]
    public function tiap_awalan_dipilah_ke_layanan_pemiliknya(): void
    {
        foreach ([
            'CAMP-20261108-0001' => 'scopus_camp',
            'CO-20261108-0001' => 'online_training',
            'WE-20261108-0001' => 'webinar_eksklusif',
            'KAFE-20261108-0001' => 'scopus_kafe',

            // Nomor percobaan kedua: awalannya tidak berubah.
            'CAMP-20261108-0001-R2' => 'scopus_camp',
            'CO-20261108-0001-R3' => 'online_training',
        ] as $nomor => $harap) {
            $this->assertSame($harap,
                PemberitahuanDokuController::layananDariNomor($nomor), $nomor);
        }
    }

    #[Test]
    public function nomor_yang_tidak_dikenali_tidak_dipilah_ke_mana_pun(): void
    {
        /*
         * Lebih baik tidak dipilah daripada dipilah ke layanan yang salah:
         * yang salah akan mencatat pembayaran pada pendaftaran orang lain.
         */
        foreach (['', 'TANPA-AWALAN', 'XYZ-0001', '123456', 'CAMP20261108'] as $nomor) {
            $this->assertNull(
                PemberitahuanDokuController::layananDariNomor($nomor),
                var_export($nomor, true)
            );
        }
    }

    #[Test]
    public function awalan_terpanjang_didahulukan(): void
    {
        /*
         * Penjagaan untuk layanan berikutnya. Kalau kelak ada awalan yang
         * merupakan awalan dari yang lain — "CO" versus "CONTOH" — urutan
         * pemeriksaan menentukan, dan yang salah mencatat uang di
         * pendaftaran orang lain. Diurut dari yang terpanjang.
         */
        $this->assertSame('scopus_kafe',
            PemberitahuanDokuController::layananDariNomor('KAFE-20261108-0001'));

        // 'CAMP' tidak boleh tertangkap oleh awalan lain yang lebih pendek.
        $this->assertSame('scopus_camp',
            PemberitahuanDokuController::layananDariNomor('CAMP-20261108-0001'));
    }

    // -------------------------------------------------------- jalur penuh

    /** @param array<string, mixed> $isi */
    private function kirim(array $isi, bool $benar = true, string $jalur = '/pemberitahuan/doku')
    {
        $requestId = (string) Str::uuid();
        $waktu = gmdate('Y-m-d\TH:i:s\Z');
        $json = json_encode($isi, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $bahan = implode("\n", [
            'Client-Id:' . self::KLIEN,
            'Request-Id:' . $requestId,
            'Request-Timestamp:' . $waktu,
            'Request-Target:' . $jalur,
            'Digest:' . base64_encode(hash('sha256', $json, true)),
        ]);

        $tanda = 'HMACSHA256=' . base64_encode(
            hash_hmac('sha256', $bahan, $benar ? self::RAHASIA : 'SALAH', true)
        );

        return $this->call('POST', $jalur, [], [], [], [
            'HTTP_Client-Id' => self::KLIEN,
            'HTTP_Request-Id' => $requestId,
            'HTTP_Request-Timestamp' => $waktu,
            'HTTP_Signature' => $tanda,
            'CONTENT_TYPE' => 'application/json',
        ], $json);
    }

    private function campMenunggu(): PendaftaranScopusCamp
    {
        $a = CategoriesScopusCamp::create([
            'layanan' => 'scopus_camp', 'token' => Str::random(30),
            'nama' => 'UJI Satu Alamat ' . Str::random(6),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(12)->toDateString(),
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'biaya' => 5500000, 'status' => 'active',
        ]);

        return PendaftaranScopusCamp::create([
            'token' => Str::random(30),
            'id_transaksi' => PendaftaranSemuaLayanan::nomorBaru('scopus_camp'),
            'kategori_id' => $a->getKey(),
            'email' => Str::lower(Str::random(10)) . '@contoh.test',
            'nama' => 'Pembayar Camp',
            'telp' => '0813' . random_int(10000000, 99999999),
            'affiliasi' => 'Universitas Uji',
            'ppn' => '0', 'kode_unik' => '123',
            'jumlah_pendaftar' => 1, 'total_pembayaran' => '5500123',
            'status' => PendaftaranSemuaLayanan::statusAwal('scopus_camp'),
            'cara_bayar' => 'doku',
            'kedaluwarsa_pada' => now()->addHour(),
        ]);
    }

    private function onlineMenunggu(): PendaftaranOnlineTraining
    {
        $a = CategoriesOnlineTraining::create([
            'token' => Str::random(30),
            'nama' => 'UJI Satu Alamat CO ' . Str::random(6),
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
            'nama' => 'Pembayar Online',
            'telp' => '0813' . random_int(10000000, 99999999),
            'affiliasi' => 'Universitas Uji',
            'ppn' => '0', 'kode_unik' => '123',
            'jumlah_pendaftar' => 1, 'total_pembayaran' => '1875123',
            'status' => PendaftaranSemuaLayanan::statusAwal('online_training'),
            'cara_bayar' => 'doku',
            'kedaluwarsa_pada' => now()->addHour(),
        ]);
    }

    /** @return array<string, mixed> */
    private function kiriman(string $nomor): array
    {
        return [
            'order' => ['invoice_number' => $nomor],
            'transaction' => ['status' => 'SUCCESS', 'date' => now()->toIso8601String()],
            'service' => ['id' => 'VIRTUAL_ACCOUNT'],
            'acquirer' => ['id' => 'MANDIRI'],
        ];
    }

    #[Test]
    public function satu_alamat_melunaskan_pendaftaran_camp(): void
    {
        $p = $this->campMenunggu();

        $this->kirim($this->kiriman($p->id_transaksi))->assertOk();

        $this->assertSame('Pendaftaran Diterima', (string) $p->refresh()->status);
        $this->assertNotNull($p->bayar_pada);
    }

    #[Test]
    public function satu_alamat_melunaskan_pendaftaran_class_online(): void
    {
        /*
         * Inti perbaikannya: SATU alamat melayani layanan yang BERBEDA.
         * Sebelum ini, alamat yang terdaftar di dasbor cuma bisa satu, dan
         * layanan lain tidak pernah dikabari.
         */
        $p = $this->onlineMenunggu();

        $this->kirim($this->kiriman($p->id_transaksi))->assertOk();

        $this->assertSame('Pendaftaran Diterima', (string) $p->refresh()->status);
        $this->assertNotNull($p->bayar_pada);
    }

    #[Test]
    public function tanda_tangan_salah_tetap_ditolak_di_alamat_bersama(): void
    {
        /*
         * Alamat ini dikecualikan dari CSRF, jadi tanda tangannya
         * satu-satunya penjaga — dan ia diperiksa oleh pengendali layanan
         * yang dituju, memakai jalur permintaan yang SEBENARNYA. Kalau
         * penerusannya mengubah jalur, tanda tangan yang SAH akan ditolak
         * dan tidak ada satu pun pembayaran yang tercatat.
         */
        $p = $this->campMenunggu();

        $this->kirim($this->kiriman($p->id_transaksi), false)->assertStatus(401);

        $this->assertNotSame('Pendaftaran Diterima', (string) $p->refresh()->status);
        $this->assertNull($p->bayar_pada);
    }

    #[Test]
    public function nomor_asing_dijawab_dua_ratus_bukan_galat(): void
    {
        // Bukan-200 membuat DOKU mengulang pemberitahuan yang sama
        // berhari-hari.
        $this->kirim($this->kiriman('XYZ-TIDAK-DIKENAL'))->assertOk();
    }

    #[Test]
    public function alamat_lama_per_layanan_tetap_hidup(): void
    {
        /*
         * Yang terlanjur terdaftar di dasbor tidak boleh mati hanya karena
         * ada jalan baru.
         */
        $p = $this->campMenunggu();

        $this->kirim($this->kiriman($p->id_transaksi), true, '/Scopus-Camp/pemberitahuan/doku')
            ->assertOk();

        $this->assertSame('Pendaftaran Diterima', (string) $p->refresh()->status);
    }

    #[Test]
    public function setiap_layanan_bergerbang_punya_pengendalinya(): void
    {
        /*
         * Layanan bergerbang yang tidak terdaftar di peta pengendali akan
         * DIAM: nomornya tidak terpilah, pemberitahuannya dijawab 200, dan
         * pembayarannya tidak pernah tercatat lewat jalur ini.
         */
        $peta = (new \ReflectionClass(PemberitahuanDokuController::class))
            ->getConstant('PENGENDALI');

        foreach (PendaftaranSemuaLayanan::semuaSumber() as $kunci => $sumber) {
            if (empty($sumber['pakai_gerbang'])) {
                continue;
            }

            $this->assertArrayHasKey($kunci, $peta,
                "Layanan bergerbang '{$kunci}' tidak punya pengendali di alamat pemberitahuan bersama.");

            $this->assertNotEmpty($sumber['nomor_awalan'] ?? '',
                "Layanan bergerbang '{$kunci}' tanpa awalan nomor — tidak bisa dipilah.");
        }
    }
}
