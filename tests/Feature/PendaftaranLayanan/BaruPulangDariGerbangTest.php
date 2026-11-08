<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\CategoriesOnlineTraining;
use App\PendaftaranOnlineTraining;
use App\Support\PendaftaranSemuaLayanan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Halaman status tidak boleh bilang "belum bayar" saat ia belum tahu.
 *
 * ---------------------------------------------------------------------------
 * Apa yang dilaporkan
 * ---------------------------------------------------------------------------
 *
 * 8 Nov 2026: pembayaran sudah SUKSES di simulator DOKU, tetapi halaman status
 * masih berbunyi "Tinggal satu langkah lagi — Pendaftaran Anda sudah tersimpan
 * dan kursinya ditahan. Yang tersisa hanya menyelesaikan pembayaran." Kalimat
 * pemiliknya: "user mengira belum membayar dan akan membayar 2x".
 *
 * ---------------------------------------------------------------------------
 * Kenapa itu bisa terjadi, dan kenapa ia TIDAK akan hilang sendiri
 * ---------------------------------------------------------------------------
 *
 * Ada jeda yang memang tidak bisa dihapus: gerbang mengalihkan pendaftarnya
 * kembali ke sini SEBELUM pembayarannya selesai diteruskan bank. Halaman ini
 * lalu menanyai gerbang sendiri (susulkanDariGerbang) dan pollernya mengulang
 * tiap lima detik — tetapi pada tarikan PERTAMA jawabannya memang sering belum
 * ada.
 *
 * Yang salah bukan jedanya melainkan APA YANG DIKATAKAN selama jeda itu.
 * Halaman ini cuma punya dua jawaban — lunas, atau belum bayar — jadi "belum
 * tahu" terpaksa dibacakan sebagai "belum bayar". Itu pernyataan, bukan
 * pertanyaan, dan di bawahnya berdiri tombol bergradien "Bayar sekarang" yang
 * tagihannya MASIH HIDUP. Pembayaran kedua yang mengikutinya sah dan uangnya
 * benar-benar masuk.
 */
class BaruPulangDariGerbangTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-PULANG');
        config()->set('services.doku.secret_key', 'SK-UJI-PULANG');

        Mail::fake();

        // Gerbang menjawab "belum lunas" — persis keadaan yang jadi masalah.
        Http::fake(['*' => Http::response(['transaction' => ['status' => 'PENDING']], 200)]);
    }

    private function menunggu(): PendaftaranOnlineTraining
    {
        $a = CategoriesOnlineTraining::create([
            'token' => Str::random(30),
            'nama' => 'UJI Pulang Gerbang ' . Str::random(6),
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
            'nama' => 'Baru Pulang Bayar',
            'telp' => '0813' . random_int(10000000, 99999999),
            'affiliasi' => 'Universitas Uji',
            'ppn' => '0', 'kode_unik' => '123',
            'jumlah_pendaftar' => 1, 'total_pembayaran' => '1875123',
            'status' => PendaftaranSemuaLayanan::statusAwal('online_training'),
            'cara_bayar' => 'doku', 'bayar_status' => 'menunggu',
            'bayar_nomor' => 'UJI-PULANG-' . Str::upper(Str::random(5)),
            'bayar_url' => 'https://staging.doku.com/checkout-link-v2/UJI',
            'kedaluwarsa_pada' => now()->addHour(),
        ]);
    }

    private function isi(PendaftaranOnlineTraining $p, bool $dariGerbang): string
    {
        $alamat = route('public.onlinetraining.status', $p->getKey())
            . ($dariGerbang ? '?dari=gerbang' : '');

        return (string) $this->get($alamat)->assertOk()->getContent();
    }

    #[Test]
    public function yang_baru_pulang_tidak_diberi_tahu_bahwa_ia_belum_bayar(): void
    {
        $p = $this->menunggu();

        $isi = $this->isi($p, true);

        $this->assertStringContainsString('Kami sedang memastikan pembayaran Anda', $isi);
        $this->assertStringContainsString('Jangan membayar lagi', $isi);

        /*
         * Kalimat lamanya TIDAK boleh ikut tergambar. Dua judul di satu
         * halaman — satu berkata "sedang dipastikan", satu berkata "yang
         * tersisa hanya menyelesaikan pembayaran" — lebih membingungkan
         * daripada cacat aslinya.
         */
        $this->assertStringNotContainsString('Tinggal satu langkah lagi', $isi);
        $this->assertStringNotContainsString('Yang tersisa hanya menyelesaikan pembayaran', $isi);
    }

    #[Test]
    public function tombol_bayarnya_tidak_lagi_jadi_ajakan(): void
    {
        /*
         * Tombol inilah yang mengubah kesalahpahaman jadi uang: tagihannya
         * masih hidup, jadi tautannya benar-benar mengantar ke halaman DOKU
         * yang sama dan pembayaran keduanya SAH.
         *
         * Tidak dibuang — yang memang belum sempat membayar tetap
         * membutuhkannya — tetapi bobot dan kalimatnya berubah: dari ajakan
         * jadi jalan keluar yang menyebut syaratnya sendiri.
         */
        $p = $this->menunggu();

        $isi = $this->isi($p, true);

        $this->assertStringNotContainsString('> Bayar sekarang', $isi);
        $this->assertStringContainsString('Belum sempat membayar?', $isi);
        $this->assertStringContainsString('abaikan tombol itu', $isi);

        // Tautannya tetap ADA: menyembunyikannya membuat yang benar-benar
        // belum membayar menghubungi panitia.
        $this->assertStringContainsString((string) $p->bayar_url, $isi);
    }

    #[Test]
    public function tanpa_penanda_halamannya_tidak_berubah(): void
    {
        /*
         * Yang membuka tautan dari emailnya berhari-hari kemudian memang
         * belum membayar, dan kepadanya "Bayar sekarang" justru yang benar.
         * Keadaan ketiga ini HANYA untuk yang baru saja pulang dari gerbang.
         */
        $p = $this->menunggu();

        $isi = $this->isi($p, false);

        $this->assertStringContainsString('Tinggal satu langkah lagi', $isi);
        $this->assertStringContainsString('Bayar sekarang', $isi);
        $this->assertStringNotContainsString('Kami sedang memastikan pembayaran Anda', $isi);
    }

    #[Test]
    public function yang_sudah_lunas_tetap_dibacakan_lunas(): void
    {
        /*
         * Penandanya cuma petunjuk tampilan. Begitu pembayarannya tercatat,
         * ia tidak boleh menahan kabar baiknya — walau alamatnya masih
         * membawa `?dari=gerbang` karena orangnya belum memuat ulang.
         */
        $p = $this->menunggu();
        $p->forceFill([
            'status' => 'Pendaftaran Diterima',
            'bayar_status' => 'success',
            'bayar_pada' => now(),
        ])->save();

        $isi = $this->isi($p->refresh(), true);

        $this->assertStringNotContainsString('Kami sedang memastikan pembayaran Anda', $isi);
        $this->assertStringNotContainsString('Jangan membayar lagi', $isi);
    }

    #[Test]
    public function alamat_kembali_ke_gerbang_membawa_penandanya(): void
    {
        /*
         * Tanpa penanda di alamat kembalinya, keadaan ketiga di atas tidak
         * akan pernah terpicu oleh alur yang sebenarnya — hanya oleh orang
         * yang mengetiknya sendiri.
         */
        foreach ([
            'app/Http/Controllers/Publict/PublicOnlineTrainingController.php',
            'app/Http/Controllers/Publict/PublicScopusCampController.php',
            'app/Http/Controllers/Publict/PublicScopusKafeController.php',
        ] as $berkas) {
            $this->assertStringContainsString("'dari' => 'gerbang'",
                (string) file_get_contents(base_path($berkas)), $berkas);
        }
    }

    #[Test]
    public function ketiga_layanan_bergerbang_ikut_diperbaiki(): void
    {
        // Satu yang tertinggal akan mengulangi cacatnya persis, dan yang
        // tertinggal selalu yang tidak diuji.
        foreach ([
            'resources/views/public/online_training/status.blade.php',
            'resources/views/public/scopus_camp/status.blade.php',
            'resources/views/public/scopus_kafe/status.blade.php',
        ] as $berkas) {
            $isi = (string) file_get_contents(base_path($berkas));

            $this->assertStringContainsString('$baruDariGerbang', $isi, $berkas);
            $this->assertStringContainsString('Kami sedang memastikan pembayaran Anda', $isi, $berkas);
            $this->assertStringContainsString('Jangan membayar lagi', $isi, $berkas);
        }
    }
}
