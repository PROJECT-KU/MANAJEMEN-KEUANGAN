<?php

namespace Tests\Feature;

use App\Exports\PendaftaranLayananExport;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * Rupa berkas "Unduh Excel" di layar Pendaftar Layanan.
 *
 * Dijaga atas BERKAS YANG BENAR-BENAR DIHASILKAN, bukan atas sumbernya.
 * Semua cacat yang pernah ada di ekspor ini diam di tingkat sumber: gaya
 * yang menunjuk lajur keliru, rentang penyaring rusak, dan pembatas lebar
 * yang syaratnya tidak pernah benar. Ketiganya tidak menimbulkan galat
 * apa pun — yang berubah hanya rupa berkas yang sudah terunduh.
 *
 * Tanpa basis data: barisnya dirakit di sini, dan tidak satu pun kolom
 * yang dipakai memicu kueri.
 */
class EksporPendaftaranRapiTest extends TestCase
{
    /** Lajur terakhir tabelnya, dihitung dari kepala kolom yang sebenarnya. */
    private function akhirLajur(PendaftaranLayananExport $ekspor): string
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
            count($ekspor->headings())
        );
    }

    private function baris(array $ganti = []): object
    {
        return (object) array_merge([
            'layanan' => 'scopus_camp',
            'nomor' => 'SC-0001',
            'nama_orang' => 'Nama Panjang Sekali Beserta Gelar Akademiknya, S.Pd., M.Pd.',
            'email' => 'nama.panjang.sekali.beserta.gelarnya@universitas.ac.id',
            'telp' => '081100000001',
            'affiliasi' => 'Universitas Dengan Nama Yang Juga Panjang Sekali',
            'sesi' => 'Sesi pagi',
            'angkatan_id' => null,
            'jumlah' => 2,
            'total' => 1500000,
            'kode_unik' => 777,
            'kode_diskon' => '',
            'status' => 'Pembayaran Berhasil',
            'bukti' => '',
            'waktu' => '2026-10-01 09:00:00',
        ], $ganti);
    }

    /** Lembar kerjanya, dibangkitkan lalu dibaca kembali dari cakram. */
    private function lembar(Collection $baris, array $saringan = []): array
    {
        $ekspor = new PendaftaranLayananExport($baris, Pendaftaran::katalog(), $saringan);

        $jalur = tempnam(sys_get_temp_dir(), 'uji-ekspor') . '.xlsx';
        file_put_contents($jalur, Excel::raw($ekspor, \Maatwebsite\Excel\Excel::XLSX));

        try {
            $lembar = IOFactory::load($jalur)->getActiveSheet();

            // Lebar autosize baru terhitung saat DITULIS, bukan saat dirakit;
            // dibaca dari berkas inilah angkanya nyata.
            return [$lembar, $this->akhirLajur($ekspor)];
        } finally {
            @unlink($jalur);
        }
    }

    /**
     * Nomor telepon TIDAK berkutip tunggal di depannya.
     *
     * Kutip itu trik mengetik di Excel, bukan nilai. Diserahkan lewat
     * PhpSpreadsheet ia tertulis apa adanya: yang tampil di sel
     * "'0812-2017-7011", dan kutipnya ikut tersalin saat nomornya di-copy
     * untuk menghubungi orangnya.
     */
    public function test_nomor_telepon_tersimpan_sebagai_untaian_tanpa_kutip(): void
    {
        [$lembar] = $this->lembar(collect([$this->baris()]));

        $sel = $lembar->getCell('F5');

        $this->assertSame('081100000001', $sel->getValue(),
            'Nomor teleponnya berubah di berkasnya — nol depannya hilang atau '
            . 'kutip tunggalnya ikut tersimpan.');
        $this->assertSame(DataType::TYPE_STRING, $sel->getDataType(),
            'Teleponnya tersimpan bukan sebagai untaian; Excel akan membuang '
            . 'nol depannya sendiri.');
        $this->assertSame('@', $lembar->getStyle('F5')->getNumberFormat()->getFormatCode());
    }

    /**
     * Rentang penyaringnya berbentuk sah.
     *
     * Pernah terakit "A4:Q4:Q9" karena disambung dari rentang kepala yang
     * sudah lengkap. PhpSpreadsheet menerimanya tanpa mengeluh dan berkasnya
     * tersimpan dengan baik — penyaringnya saja yang tidak pernah muncul
     * di Excel.
     */
    public function test_penyaring_bawaan_rentangnya_sah(): void
    {
        $baris = collect([$this->baris(), $this->baris(['nomor' => 'SC-0002'])]);

        [$lembar, $akhir] = $this->lembar($baris);

        $this->assertSame('A4:' . $akhir . '6', $lembar->getAutoFilter()->getRange(),
            'Rentang penyaringnya tidak menutup tepat kepala kolom sampai baris '
            . 'terakhir.');
    }

    /**
     * Lajur teks panjang DIPATOK, dan lajur "No." sempit.
     *
     * Pembatasnya pernah dipasang bersyarat — "patok kalau lebih lebar dari
     * 32" — dan diam seluruhnya: saat AfterSheet berjalan autosize belum
     * dihitung, getWidth() mengembalikan -1, jadi syaratnya tidak pernah
     * benar. Terukur pada berkas yang dihasilkan: lajur Nama tetap 51,8.
     */
    public function test_lajur_panjang_dipatok_lebarnya(): void
    {
        [$lembar] = $this->lembar(collect([$this->baris()]));

        $patokan = ['A' => 6, 'D' => 32, 'E' => 26, 'G' => 30, 'H' => 26];

        foreach ($patokan as $lajur => $lebar) {
            $this->assertSame((float) $lebar,
                (float) $lembar->getColumnDimension($lajur)->getWidth(),
                'Lebar lajur ' . $lajur . ' tidak dipatok; satu nama bergelar '
                . 'panjang mendorong seluruh kolom sesudahnya keluar layar.');
        }
    }

    /** Kepala kolomnya berwarna, dibekukan, dan keterangannya digabung. */
    public function test_kepala_kolom_bergaya_dan_dibekukan(): void
    {
        [$lembar, $akhir] = $this->lembar(collect([$this->baris()]), ['Angkatan' => 'Scopus Camp 199']);

        $gaya = $lembar->getStyle('A4');

        $this->assertTrue($gaya->getFont()->getBold());
        $this->assertSame('FFFFFFFF', $gaya->getFont()->getColor()->getARGB());
        $this->assertSame('FF4F46E5', $gaya->getFill()->getStartColor()->getARGB(),
            'Kepala kolomnya tidak berlatar warna — kepala dan isinya terbaca sama.');

        $this->assertSame('A5', $lembar->getFreezePane(),
            'Lembarnya tidak dibekukan di bawah kepala kolom; ratusan baris '
            . 'tidak bisa dibaca tanpa itu.');

        // Ketiga baris keterangan digabung selebar tabel. Tanpa itu autosize
        // mengukur lajur A dari kalimat terpanjang di A1–A3.
        $gabungan = array_values($lembar->getMergeCells());

        foreach (['A1:' . $akhir . '1', 'A2:' . $akhir . '2', 'A3:' . $akhir . '3'] as $rentang) {
            $this->assertContains($rentang, $gabungan);
        }
    }

    /**
     * Kepala berkasnya DIPUSATKAN di atas tabel, sebagai satu pita.
     *
     * Gabungannya selebar A–Q. Rata kiri, judulnya menempel di pojok
     * sementara sisa pitanya membentang kosong — terbaca seperti sel yang
     * kelupaan diisi, bukan judul berkas. Terlihat begitu di Excel yang
     * sebenarnya sebelum ini dipasang.
     */
    public function test_kepala_berkas_terpusat_dan_berpita(): void
    {
        [$lembar, $akhir] = $this->lembar(collect([$this->baris()]));

        foreach (['A1', 'A2', 'A3'] as $sel) {
            $gaya = $lembar->getStyle($sel);

            $this->assertSame(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                $gaya->getAlignment()->getHorizontal(),
                $sel . ' tidak terpusat; judulnya menempel di pojok kiri pita '
                . 'yang membentang selebar tabel.'
            );

            $this->assertSame('FFEEF2FF', $gaya->getFill()->getStartColor()->getARGB(),
                $sel . ' tidak berlatar pita, jadi kepala berkasnya tidak '
                . 'terbaca sebagai satu blok.');
        }

        // Judulnya harus lebih besar daripada kedua baris keterangannya;
        // seukuran, ketiganya terbaca sederajat.
        $this->assertGreaterThan(
            $lembar->getStyle('A2')->getFont()->getSize(),
            $lembar->getStyle('A1')->getFont()->getSize()
        );

        /*
         * Latar pita dan latar lembar sama-sama terang. Tanpa garis
         * penutupnya batas bawah pita tidak kelihatan sama sekali dan
         * ketiga baris keterangan mengambang di atas tabel.
         */
        $this->assertSame(
            \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
            $lembar->getStyle($akhir . '3')->getBorders()->getBottom()->getBorderStyle(),
            'Pita kepalanya tidak ditutup garis sampai lajur terakhir.'
        );
    }

    /**
     * Keterangan berkasnya menyebutkan saringan yang sedang berlaku.
     *
     * Lembar kerja justru berkas yang paling sering diteruskan ke orang
     * lain. Tanpa barisnya, 29 baris tersaring terbaca persis seperti
     * daftar pendaftar yang lengkap.
     */
    public function test_saringan_tercatat_di_kepala_berkas(): void
    {
        [$lembar] = $this->lembar(
            collect([$this->baris()]),
            ['Angkatan' => 'Scopus Camp 199', 'Keadaan' => 'Lunas']
        );

        $this->assertStringContainsString('MIS Rumah Scopus', (string) $lembar->getCell('A1')->getValue());
        $this->assertStringContainsString('1 pendaftaran', (string) $lembar->getCell('A2')->getValue());

        $saringan = (string) $lembar->getCell('A3')->getValue();

        $this->assertStringContainsString('Angkatan: Scopus Camp 199', $saringan);
        $this->assertStringContainsString('Keadaan: Lunas', $saringan);

        [$tanpa] = $this->lembar(collect([$this->baris()]));

        $this->assertStringContainsString('tanpa saringan',
            (string) $tanpa->getCell('A3')->getValue(),
            'Berkas tanpa saringan tidak menyatakan dirinya lengkap, jadi '
            . 'pembacanya tidak bisa membedakannya dari berkas tersaring.');
    }

    /** Nominalnya angka berformat uang, bukan untaian. */
    public function test_total_bayar_tetap_angka_berformat(): void
    {
        [$lembar] = $this->lembar(collect([$this->baris()]));

        $this->assertSame(1500000, $lembar->getCell('K5')->getValue());
        $this->assertSame('#,##0', $lembar->getStyle('K5')->getNumberFormat()->getFormatCode());
    }
}
