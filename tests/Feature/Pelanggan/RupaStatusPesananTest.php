<?php

namespace Tests\Feature\Pelanggan;

use App\Support\PesananPelanggan;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Warna lencana status di riwayat pesanan pelanggan.
 *
 * Kolom status ketiga tabel layanan bertipe varchar bebas, bukan enum, jadi
 * tidak ada apa pun di basis data yang menjaga nilainya. Uji ini yang
 * menjaganya: kalau salah satu layanan mengganti kosakata statusnya dan
 * pemetaannya tidak ikut diperbarui, lencananya diam-diam kembali jadi
 * abu-abu — tidak ada galat, tidak ada yang rusak, hanya keterangan yang
 * hilang. Uji terakhir di bawah itulah yang menangkapnya.
 */
class RupaStatusPesananTest extends TestCase
{
    /**
     * Status yang memang bisa muncul, diambil dari borang tiap layanan.
     *
     * Bukan dari isi tabel: data yang ada sekarang cuma memuat 5 dari 13
     * nilai, jadi menguji yang ada saja akan meloloskan delapan sisanya.
     */
    public static function statusSah(): array
    {
        return [
            // Clinik Scopus
            'pending' => ['pending', 'kuning'],
            'paid' => ['paid', 'hijau'],
            'completed' => ['completed', 'hijau'],
            'canceled' => ['canceled', 'merah'],

            // Analisis Bibliometrik
            'diproses' => ['diproses', 'biru'],
            'Pendaftaran Diterima' => ['Pendaftaran Diterima', 'hijau'],
            'Pendaftaran Ditolak' => ['Pendaftaran Ditolak', 'merah'],
            'Pendaftaran Dibatalkan' => ['Pendaftaran Dibatalkan', 'merah'],
            'Pendaftaran Refund' => ['Pendaftaran Refund', 'ungu'],
            'Pendaftaran Reschedule' => ['Pendaftaran Reschedule', 'biru'],

            // Scopus Kafe
            'menunggu verifikasi' => ['menunggu verifikasi', 'kuning'],
            'pembayaran diterima' => ['pembayaran diterima', 'hijau'],
            'pembayaran ditolak' => ['pembayaran ditolak', 'merah'],
        ];
    }

    #[Test]
    #[DataProvider('statusSah')]
    public function tiap_status_punya_warna_bukan_abu(string $status, string $warna): void
    {
        $rupa = PesananPelanggan::rupaStatus($status);

        $this->assertSame($warna, $rupa['warna'], "Status '{$status}' seharusnya berwarna {$warna}.");

        // Kelas pilnya harus benar-benar ada di lembar gaya; salah tulis nama
        // warna menghasilkan lencana tanpa latar, bukan galat.
        $this->assertStringContainsString(
            '.mis-pil-' . $rupa['warna'] . ' {',
            file_get_contents(public_path('assets/css/mis-ui.css')),
            "Kelas .mis-pil-{$rupa['warna']} belum ada di mis-ui.css."
        );
    }

    #[Test]
    #[DataProvider('statusSah')]
    public function labelnya_berbahasa_indonesia(string $status): void
    {
        $label = PesananPelanggan::rupaStatus($status)['label'];

        // Empat status Clinik Scopus berbahasa Inggris; layar ini dipakai
        // orang dalam yang belum tentu paham "canceled".
        foreach (['pending', 'paid', 'completed', 'canceled', 'refund', 'reschedule'] as $inggris) {
            $this->assertStringNotContainsStringIgnoringCase(
                $inggris,
                $label,
                "Label '{$label}' masih berbahasa Inggris."
            );
        }

        $this->assertNotSame('', trim($label));
    }

    #[Test]
    public function yang_menggagalkan_diperiksa_sebelum_yang_meluluskan(): void
    {
        /*
         * "pembayaran ditolak" memuat kata "bayar" DAN kata "tolak". Kalau
         * penebaknya memeriksa "bayar" lebih dulu, pesanan yang ditolak tampil
         * hijau — kekeliruan yang paling mahal di layar ini, sebab ia membuat
         * pesanan gagal terbaca seperti pesanan beres.
         */
        $this->assertSame('merah', PesananPelanggan::rupaStatus('pembayaran gagal')['warna']);
        $this->assertSame('merah', PesananPelanggan::rupaStatus('verifikasi dibatalkan')['warna']);
        $this->assertSame('merah', PesananPelanggan::rupaStatus('sudah bayar tapi dibatalkan')['warna']);
    }

    #[Test]
    public function yang_tidak_dikenali_jadi_abu_abu_bukan_hijau(): void
    {
        // Lencana hijau pada keadaan yang tidak dipahami lebih menyesatkan
        // daripada lencana netral.
        foreach (['', '   ', 'zxcv', 'Tahap 4'] as $aneh) {
            $this->assertSame('abu', PesananPelanggan::rupaStatus($aneh)['warna']);
        }

        $this->assertSame('Tanpa status', PesananPelanggan::rupaStatus('')['label']);
    }

    #[Test]
    public function beda_besar_kecil_huruf_dan_spasi_ganda_tidak_bikin_keadaan_baru(): void
    {
        $acuan = PesananPelanggan::rupaStatus('Pendaftaran Diterima');

        foreach (['pendaftaran diterima', 'PENDAFTARAN DITERIMA', '  Pendaftaran   Diterima  '] as $tulisan) {
            $this->assertSame($acuan, PesananPelanggan::rupaStatus($tulisan), "Bentuk '{$tulisan}' seharusnya sama.");
        }
    }

    #[Test]
    public function status_yang_benar_benar_ada_di_basis_data_semuanya_terpetakan(): void
    {
        /*
         * Penjaga sesungguhnya. Uji di atas memakai daftar yang ditulis
         * tangan; yang ini membaca nilai yang BENAR-BENAR tersimpan, sehingga
         * kosakata baru yang muncul tanpa sepengetahuan siapa pun ikut
         * tertangkap.
         */
        $tabel = [
            'clinikscopus_pemesanan',
            'analisis_bibliometrik',
            'pendaftaran_scopus_kafe',
        ];

        $abu = [];

        foreach ($tabel as $t) {
            foreach (DB::table($t)->distinct()->pluck('status') as $status) {
                if ($status === null || trim((string) $status) === '') {
                    continue;
                }

                if (PesananPelanggan::rupaStatus((string) $status)['warna'] === 'abu') {
                    $abu[] = $t . ': ' . $status;
                }
            }
        }

        $this->assertSame(
            [],
            $abu,
            "Status berikut tersimpan di basis data tetapi belum punya warna:\n- " . implode("\n- ", $abu)
        );
    }
}
