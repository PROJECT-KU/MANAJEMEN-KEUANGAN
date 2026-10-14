<?php

namespace Tests\Feature;

use App\Exports\PelangganExport;
use App\Exports\PendaftaranLayananExport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Tests\TestCase;

/**
 * Berkas unduhan Data Pelanggan berupa SATU CETAKAN dengan Pendaftar Layanan.
 *
 * Dua berkas yang keluar dari sistem yang sama tidak boleh terbaca seperti
 * datang dari dua tempat. Sebelum ini kepala berkas Pelanggan cuma tiga baris
 * teks polos berukuran 13px sementara Pendaftar Layanan punya pita berjudul
 * terpusat, kepala kolom nila, dan baris berselang-seling.
 *
 * Dijaga dengan MEMBANDINGKAN KEDUA BERKAS yang benar-benar dihasilkan, bukan
 * dengan mencocokkan ke angka yang ditulis di sini. Dicocokkan ke angka tetap,
 * memperbaiki rupa acuannya nanti membuat penjaga ini hijau sementara kedua
 * berkasnya justru kembali berbeda.
 *
 * Tanpa basis data: barisnya dirakit di sini.
 */
class EksporPelangganSeragamTest extends TestCase
{
    /** @return array{0: Worksheet, 1: string} lembar dan huruf lajur terakhirnya */
    private function lembar($ekspor, int $jumlahKolom): array
    {
        $jalur = tempnam(sys_get_temp_dir(), 'uji-seragam') . '.xlsx';
        file_put_contents($jalur, Excel::raw($ekspor, \Maatwebsite\Excel\Excel::XLSX));

        try {
            // Gaya dan lebar baru terhitung saat DITULIS, bukan saat dirakit;
            // dibaca dari berkas inilah angkanya nyata.
            return [
                IOFactory::load($jalur)->getActiveSheet(),
                Coordinate::stringFromColumnIndex($jumlahKolom),
            ];
        } finally {
            @unlink($jalur);
        }
    }

    /** @return array{0: Worksheet, 1: string} */
    private function pelanggan(): array
    {
        $orang = collect([
            (object) [
                'id' => 1, 'full_name' => 'Pelanggan Satu', 'username' => 'satu',
                'email' => 'satu@contoh.test', 'email_verified_at' => now(),
                'telp' => '081100000001', 'status' => 'active', 'created_at' => now(),
            ],
            (object) [
                'id' => 2, 'full_name' => 'Pelanggan Dua', 'username' => 'dua',
                'email' => 'dua@contoh.test', 'email_verified_at' => null,
                /*
                 * SENGAJA tanpa nol di depannya.
                 *
                 * Nomor berawalan nol sudah diperlakukan untaian oleh penilai
                 * bawaan PhpSpreadsheet, jadi contoh seperti itu tidak pernah
                 * menguji apa pun — terukur: melepas penulisan tegasnya tetap
                 * membuat penjaga ini hijau. Yang benar-benar dibaca sebagai
                 * bilangan justru nomor tanpa nol depan.
                 */
                'telp' => '81100000002', 'status' => 'nonaktif', 'created_at' => now(),
            ],
        ]);

        $ekspor = new PelangganExport($orang, [1 => ['jumlah' => 2, 'terakhir' => now()]], []);

        return $this->lembar($ekspor, count($ekspor->headings()));
    }

    /** @return array{0: Worksheet, 1: string} */
    private function acuan(): array
    {
        /*
         * DUA baris, sama banyak dengan contoh pelanggannya.
         *
         * Baris berselang-seling baru terbentuk mulai baris genap pertama;
         * dengan satu baris saja, lembar acuannya tidak punya baris 6 untuk
         * dibandingkan dan penjaganya merah karena contohnya terlalu pendek,
         * bukan karena rupanya berbeda.
         */
        $satu = [
            'layanan' => 'scopus_camp', 'nomor' => 'SC-0001', 'nama_orang' => 'Acuan',
            'email' => 'acuan@contoh.test', 'telp' => '081100000003', 'affiliasi' => '-',
            'sesi' => null, 'angkatan_id' => null, 'jumlah' => 1, 'total' => 100000,
            'kode_unik' => 500, 'kode_diskon' => null, 'nominal_diskon' => null,
            'bukti' => null, 'catatan' => null, 'cara_bayar' => 'transfer',
            'grup' => null, 'status' => 'diproses', 'waktu' => now(), 'promo' => null,
        ];

        $baris = collect([
            (object) $satu,
            (object) array_merge($satu, ['nomor' => 'SC-0002', 'nama_orang' => 'Acuan Dua']),
        ]);

        $ekspor = new PendaftaranLayananExport($baris, [], []);

        return $this->lembar($ekspor, count($ekspor->headings()));
    }

    // ------------------------------------------------------------ pita kepala

    /** @test */
    public function pita_kepala_berkasnya_serupa_dengan_pendaftar_layanan(): void
    {
        [$p] = $this->pelanggan();
        [$a] = $this->acuan();

        foreach (['A1', 'A2', 'A3'] as $sel) {
            $this->assertSame(
                $a->getStyle($sel)->getFill()->getStartColor()->getRGB(),
                $p->getStyle($sel)->getFill()->getStartColor()->getRGB(),
                'Latar ' . $sel . ' berbeda dari ekspor Pendaftar Layanan.'
            );

            $this->assertSame(
                $a->getStyle($sel)->getFont()->getSize(),
                $p->getStyle($sel)->getFont()->getSize(),
                'Ukuran huruf ' . $sel . ' berbeda dari ekspor Pendaftar Layanan.'
            );

            $this->assertSame(
                $a->getStyle($sel)->getFont()->getColor()->getRGB(),
                $p->getStyle($sel)->getFont()->getColor()->getRGB(),
                'Warna huruf ' . $sel . ' berbeda dari ekspor Pendaftar Layanan.'
            );

            $this->assertSame(
                $a->getStyle($sel)->getAlignment()->getHorizontal(),
                $p->getStyle($sel)->getAlignment()->getHorizontal(),
                'Perataan ' . $sel . ' berbeda dari ekspor Pendaftar Layanan.'
            );
        }

        $this->assertSame(
            $a->getStyle('A3')->getBorders()->getBottom()->getBorderStyle(),
            $p->getStyle('A3')->getBorders()->getBottom()->getBorderStyle(),
            'Garis penutup pita berbeda; tanpa itu ketiga baris keterangan mengambang di atas tabel.'
        );

        foreach ([1, 2, 3, 4] as $n) {
            $this->assertSame(
                $a->getRowDimension($n)->getRowHeight(),
                $p->getRowDimension($n)->getRowHeight(),
                'Tinggi baris ' . $n . ' berbeda dari ekspor Pendaftar Layanan.'
            );
        }
    }

    /** @test */
    public function ketiga_baris_kepala_digabung_selebar_tabelnya(): void
    {
        /*
         * Tanpa digabung, ShouldAutoSize mengukur lajur A dari kalimat
         * terpanjang di A1-A3 — lajur A jadi selebar puluhan huruf untuk
         * menampung nomor urut yang cuma butuh tiga, dan seluruh kolom lain
         * terdorong jauh ke kanan.
         *
         * Lebarnya dihitung dari jumlah kepala kolom, bukan ditulis 'J':
         * ditulis tetap, menambah satu kolom membuat pitanya berhenti tepat
         * sebelum kolom terakhir tanpa galat apa pun.
         */
        [$lembar, $akhir] = $this->pelanggan();

        $gabungan = array_values($lembar->getMergeCells());

        foreach (['1', '2', '3'] as $n) {
            $this->assertContains('A' . $n . ':' . $akhir . $n, $gabungan,
                'Baris kepala ke-' . $n . ' tidak digabung selebar tabelnya.');
        }
    }

    // ----------------------------------------------------------- kepala kolom

    /** @test */
    public function kepala_kolom_dan_baris_isinya_serupa_dengan_pendaftar_layanan(): void
    {
        [$p] = $this->pelanggan();
        [$a] = $this->acuan();

        $this->assertSame(
            $a->getStyle('A4')->getFill()->getStartColor()->getRGB(),
            $p->getStyle('A4')->getFill()->getStartColor()->getRGB(),
            'Latar kepala kolom berbeda dari ekspor Pendaftar Layanan.'
        );

        $this->assertSame(
            $a->getStyle('A4')->getFont()->getColor()->getRGB(),
            $p->getStyle('A4')->getFont()->getColor()->getRGB(),
            'Warna huruf kepala kolom berbeda dari ekspor Pendaftar Layanan.'
        );

        $this->assertTrue($p->getStyle('A4')->getFont()->getBold());

        /*
         * Baris berselang-seling. Lembar berisi ratusan baris dibaca dengan
         * jari di layar, dan mata kehilangan barisnya tanpa pembeda apa pun.
         * Baris 6 yang genap; baris 5 dibiarkan putih.
         */
        $this->assertSame(
            $a->getStyle('A6')->getFill()->getStartColor()->getRGB(),
            $p->getStyle('A6')->getFill()->getStartColor()->getRGB(),
            'Baris berselang-selingnya berbeda dari ekspor Pendaftar Layanan.'
        );
    }

    /** @test */
    public function kepala_kolom_tetap_terlihat_saat_digulung(): void
    {
        [$lembar] = $this->pelanggan();

        $this->assertSame('A5', $lembar->getFreezePane());
    }

    // -------------------------------------------------------- nomor telepon

    /** @test */
    public function nomor_telepon_untaian_tegas_tanpa_kutip_di_depannya(): void
    {
        /*
         * Dua perkara sekaligus. Dibiarkan jadi bilangan, "081100000001"
         * kehilangan nol depannya dan tidak bisa dipakai menghubungi siapa
         * pun. Dijaga dengan kutip tunggal di depannya — cara lama berkas ini
         * — kutipnya ikut tersalin saat nomornya di-copy.
         */
        [$lembar] = $this->pelanggan();

        foreach (['F5' => '081100000001', 'F6' => '81100000002'] as $sel => $harapan) {
            $isi = $lembar->getCell($sel);

            $this->assertSame(DataType::TYPE_STRING, $isi->getDataType(),
                'Nomor di ' . $sel . ' tidak tersimpan sebagai untaian; nol depannya akan hilang.');
            $this->assertSame($harapan, (string) $isi->getValue());
            $this->assertStringNotContainsString("'", (string) $isi->getValue(),
                'Kutip tunggalnya ikut tersimpan dan akan tersalin saat nomornya di-copy.');
            $this->assertSame('@', $lembar->getStyle($sel)->getNumberFormat()->getFormatCode());
        }
    }

    /** @test */
    public function keterangan_berkas_menyebut_isinya_dan_saringannya(): void
    {
        /*
         * Lembar kerja berisi 29 baris tersaring terbaca persis seperti
         * daftar yang lengkap — dan lembar kerja justru berkas yang paling
         * sering diteruskan ke orang lain.
         */
        [$lembar] = $this->pelanggan();

        $this->assertStringContainsString('Data Pelanggan', (string) $lembar->getCell('A1')->getValue());
        $this->assertStringContainsString('2 pelanggan', (string) $lembar->getCell('A2')->getValue());
        $this->assertStringContainsString('1 akun aktif', (string) $lembar->getCell('A2')->getValue());
        $this->assertStringContainsString('1 pernah memesan', (string) $lembar->getCell('A2')->getValue());
        $this->assertStringContainsString('tanpa saringan', (string) $lembar->getCell('A3')->getValue());
    }
}
