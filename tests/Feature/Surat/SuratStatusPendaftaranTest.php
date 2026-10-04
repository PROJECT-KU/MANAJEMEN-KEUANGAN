<?php

namespace Tests\Feature\Surat;

use App\AnalisisBibliometrik;
use App\CategoriesAnalisisBibliometrik;
use App\CategoriesScopusCamp;
use App\PendaftaranScopusCamp;
use App\PendaftaranScopusKafe;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Surat pemberitahuan status pendaftaran.
 *
 * Kelima templatnya dulu dibangun seperti HALAMAN WEB: memuat Bootstrap,
 * FontAwesome, dan style.css lewat <link>, lalu menata isinya dengan
 * `display: flex`. Ketiganya dibuang mentah-mentah oleh Gmail, Outlook, dan
 * hampir semua klien email — yang sampai ke penerima tinggal teks tanpa gaya,
 * rata kiri, tanpa logo.
 *
 * Yang paling merugikan bukan rupanya: rincian dulu disusun DUA KOLOM ber-flex,
 * label di kiri dan nilai di kanan. Tanpa dukungan flex keduanya jatuh
 * bertumpuk, sehingga daftar label terbaca terpisah dari daftar nilainya —
 * "Kode Transaksi / Tanggal Mulai / Total" lalu "NMMCG / 12 Jan / Rp 5.500.000"
 * tanpa ada yang memasangkannya.
 *
 * Uji ini merakit tiap suratnya sungguhan dan memeriksa yang TIDAK BISA
 * dilihat dari membaca templatnya satu per satu.
 */
class SuratStatusPendaftaranTest extends TestCase
{
    use DatabaseTransactions;

    /** @return array<string, \Illuminate\Mail\Mailable> */
    private function semuaSurat(): array
    {
        $camp = new PendaftaranScopusCamp([
            'id_transaksi' => 'UJI-CAMP-1', 'nama' => 'Budi Santoso',
            'total_pembayaran' => 5500000,
        ]);

        $angkatanCamp = new CategoriesScopusCamp([
            'nama' => 'Scopus Camp Yogyakarta', 'nama_ke' => '199', 'lokasi' => 'Yogyakarta',
            'mulai' => '2026-11-01', 'selesai' => '2026-11-03',
            'group_wa' => 'https://chat.whatsapp.com/contoh',
        ]);

        $bib = new AnalisisBibliometrik([
            'id_transaksi' => 'UJI-BIB-1', 'nama' => 'Siti Rahma',
            'total_pembayaran' => 1250000, 'group_wa' => 'https://chat.whatsapp.com/contoh2',
        ]);

        $angkatanBib = new CategoriesAnalisisBibliometrik([
            'nama' => 'Bibliometrik', 'nama_ke' => '12',
            'mulai' => '2026-12-01', 'selesai' => '2026-12-05',
        ]);

        $kafe = new PendaftaranScopusKafe([
            'id_pemesanan' => 'UJI-KAFE-1', 'nama' => 'Agus Nugroho', 'telp' => '081234567890',
            'tanggal_pemesanan' => '2026-10-20', 'total_keseluruhan_pembayaran' => 350000,
            'status' => 'Pembayaran Diterima',
        ]);

        return [
            'Camp diterima' => new \App\Mail\ScopusCampUpdateDiterimaMail($camp, $angkatanCamp, 'MIS'),
            'Camp dijadwalkan ulang' => new \App\Mail\ScopusCampUpdateResheduleMail($camp, $angkatanCamp, 'MIS'),
            'Bibliometrik diterima' => new \App\Mail\AnalisisBibliometrikUpdateDiterimaMail($bib, $angkatanBib, 'MIS'),
            'Bibliometrik dijadwalkan ulang' => new \App\Mail\AnalisisBibliometrikUpdateResheduleMail($bib, $angkatanBib, 'MIS'),
            'Scopus Kafe' => new \App\Mail\UpdatePublicPendaftaranScopusKafeMail($kafe, $kafe, 'MIS', true),
        ];
    }

    #[Test]
    public function setiap_surat_status_bisa_dirakit_dan_membawa_logo(): void
    {
        foreach ($this->semuaSurat() as $nama => $surat) {
            $isi = $surat->render();

            /*
             * Yang dituntut: ADA <img> berlogo dengan src terisi — bukan
             * bentuk src-nya.
             *
             * Percobaan pertama menuntut `src="cid:..."` dan merah, padahal
             * logonya ada: saat surat hanya DIRENDER (tidak dikirim),
             * $message->embed() menyemat gambarnya sebagai data: URI; alamat
             * cid: baru muncul saat benar-benar dikirim. Menuntut bentuknya
             * berarti menguji jalur pengiriman, bukan isi suratnya.
             */
            $this->assertSame(1, preg_match(
                '/<img[^>]+alt="Rumah Scopus Foundation"[^>]*>/', $isi, $logo
            ), $nama . ': logo Rumah Scopus tidak ada di suratnya.');

            $this->assertMatchesRegularExpression('/src="(?!")[^"]+"/', $logo[0],
                $nama . ': logonya ada tetapi sumber gambarnya kosong.');

            // Nama penerimanya disapa.
            $this->assertStringContainsString('Halo', $isi, $nama . ': tidak ada sapaan.');
        }
    }

    #[Test]
    public function surat_status_tidak_bersandar_pada_gaya_yang_dibuang_klien_email(): void
    {
        foreach ($this->semuaSurat() as $nama => $surat) {
            $isi = $surat->render();

            $this->assertDoesNotMatchRegularExpression('/<link[^>]+rel=["\']stylesheet/i', $isi,
                $nama . ': masih memuat stylesheet lewat <link>, dan klien email membuangnya — '
                    . 'suratnya sampai tanpa gaya sama sekali.');

            $this->assertStringNotContainsString('display: flex', $isi,
                $nama . ': masih menata isinya dengan flexbox, yang tidak didukung Outlook; '
                    . 'kolom label dan nilainya akan jatuh bertumpuk.');

            $this->assertStringNotContainsString('<script', $isi,
                $nama . ': memuat skrip, yang selalu dibuang klien email.');

            // Isinya dirakit dengan tabel — satu-satunya tata letak yang
            // benar-benar didukung di semua klien.
            $this->assertStringContainsString('<table', $isi, $nama . ': tidak memakai tabel.');
        }
    }

    #[Test]
    public function rincian_surat_berpasangan_label_dan_nilainya(): void
    {
        /*
         * Label dan nilainya WAJIB berada di satu baris tabel.
         *
         * Inilah yang rusak paling diam-diam di templat lama: keduanya ada,
         * ejaannya benar, tetapi terpisah jadi dua blok sehingga penerima
         * harus menghitung sendiri baris keberapa yang berpasangan dengan
         * baris keberapa.
         */
        $isi = (new \App\Mail\ScopusCampUpdateDiterimaMail(
            new PendaftaranScopusCamp([
                'id_transaksi' => 'UJI-CAMP-1', 'nama' => 'Budi Santoso', 'total_pembayaran' => 5500000,
            ]),
            new CategoriesScopusCamp([
                'nama' => 'Scopus Camp Yogyakarta', 'nama_ke' => '199', 'lokasi' => 'Yogyakarta',
                'mulai' => '2026-11-01', 'selesai' => '2026-11-03', 'group_wa' => '',
            ]),
            'MIS'
        ))->render();

        $rapat = preg_replace('/\s+/', ' ', $isi) ?? '';

        foreach ([
            'Kode transaksi' => 'UJI-CAMP-1',
            'Total pembayaran' => 'Rp 5.500.000',
            'Lokasi' => 'Yogyakarta',
        ] as $label => $nilai) {
            $this->assertSame(1, preg_match(
                '#<tr>.*?' . preg_quote($label, '#') . '.*?' . preg_quote($nilai, '#') . '.*?</tr>#',
                $rapat
            ), 'Label "' . $label . '" tidak sebaris dengan nilainya di dalam tabel rincian.');
        }
    }
}
