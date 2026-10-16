<?php

namespace Tests\Feature;

use App\ClinikScopusBiayaPersesi;
use App\Exports\AngkatanLayananExport;
use App\Exports\PelangganExport;
use App\Exports\PendaftaranLayananExport;
use App\Exports\TarifLayananExport;
use App\KategoriLayanan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Tests\TestCase;

/**
 * SELURUH lembar kerja yang keluar dari MIS memakai satu cetakan.
 *
 * Berkas yang keluar dari sistem yang sama tidak boleh terbaca seperti datang
 * dari tempat berbeda. Sebelum ini kepala berkas Pelanggan cuma tiga baris
 * teks polos 13px dan Tarif Layanan tidak punya lembar kerja sama sekali,
 * sementara Pendaftar Layanan sudah punya pita berjudul terpusat, kepala kolom
 * nila, dan baris berselang-seling.
 *
 * Dijaga dengan MEMBANDINGKAN BERKAS yang benar-benar dihasilkan terhadap
 * berkas acuannya, bukan dengan mencocokkan ke angka yang ditulis di sini.
 * Dicocokkan ke angka tetap, memperbaiki rupa acuannya nanti membuat penjaga
 * ini hijau sementara berkas-berkasnya justru kembali berbeda.
 *
 * Ekspor BARU wajib ditambahkan ke daftar di semuaEkspor(); daftar inilah yang
 * membuat "satu cetakan" berarti sesuatu.
 *
 * Tanpa basis data: barisnya dirakit di sini.
 */
class EksporSatuCetakanTest extends TestCase
{
    /*
     * Ekspor Angkatan Layanan memakai model sungguhan — accessor-nya
     * (nama_layanan, jumlah_pendaftar, perlu_dicek) membaca katalog layanan
     * dan tabel pendaftaran. Jadi uji ini tidak lagi bebas basis data, dan
     * DatabaseTransactions WAJIB: tanpa itu angkatan contohnya tertinggal di
     * basis data sungguhan dan ikut terhitung di layar mana pun.
     */
    use DatabaseTransactions;

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

    /**
     * Semua lembar kerja yang harus serupa acuannya.
     *
     * @return array<string, callable(): array{0: Worksheet, 1: string}>
     */
    private function semuaEkspor(): array
    {
        return [
            'Data Pelanggan' => fn () => $this->pelanggan(),
            'Tarif Layanan' => fn () => $this->tarif(),
            'Angkatan Layanan' => fn () => $this->angkatan(),
        ];
    }

    /** @return array{0: Worksheet, 1: string} */
    private function angkatan(): array
    {
        KategoriLayanan::lupakanPendaftar();

        // DUA baris, sama banyak dengan contoh lain: baris berselang-seling
        // baru terbentuk mulai baris genap pertama.
        $baris = collect([1, 2])->map(fn ($n) => KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Angkatan Cetakan ' . \Illuminate\Support\Str::random(6),
            'nama_ke' => (string) $n,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '20', 'sisa_kuota' => '20', 'status' => 'active',
            'biaya' => '2000000',
        ]));

        $ekspor = new AngkatanLayananExport($baris, 'Seluruh angkatan, tanpa saringan.');

        return $this->lembar($ekspor, count($ekspor->headings()));
    }

    /** @return array{0: Worksheet, 1: string} */
    private function tarif(): array
    {
        $tarif = new ClinikScopusBiayaPersesi([
            'layanan' => 'clinik_scopus', 'biaya_persesi' => '125000', 'ppn' => '11',
            'diskon_alumni_persen' => '10', 'diskon_rombongan_min' => '5',
            'diskon_rombongan_persen' => '15',
            'fasilitas' => ['Materi', 'Sertifikat'],
        ]);

        $baris = [
            ['nama' => 'Clinik Scopus', 'namaVarian' => null, 'satuan' => 'per sesi', 'tarif' => $tarif],
            ['nama' => 'Scopus Camp', 'namaVarian' => 'Jawa', 'satuan' => 'per peserta', 'tarif' => $tarif],
        ];

        $ekspor = new TarifLayananExport($baris, []);

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
        [$a] = $this->acuan();

        foreach ($this->semuaEkspor() as $nama => $buat) {
            [$p] = $buat();
            $this->bandingkanPita($p, $a, $nama);
        }
    }

    private function bandingkanPita(Worksheet $p, Worksheet $a, string $nama): void
    {
        foreach (['A1', 'A2', 'A3'] as $sel) {
            $this->assertSame(
                $a->getStyle($sel)->getFill()->getStartColor()->getRGB(),
                $p->getStyle($sel)->getFill()->getStartColor()->getRGB(),
                $nama . ': latar ' . $sel . ' berbeda dari ekspor Pendaftar Layanan.'
            );

            $this->assertSame(
                $a->getStyle($sel)->getFont()->getSize(),
                $p->getStyle($sel)->getFont()->getSize(),
                $nama . ': ukuran huruf ' . $sel . ' berbeda dari ekspor Pendaftar Layanan.'
            );

            $this->assertSame(
                $a->getStyle($sel)->getFont()->getColor()->getRGB(),
                $p->getStyle($sel)->getFont()->getColor()->getRGB(),
                $nama . ': warna huruf ' . $sel . ' berbeda dari ekspor Pendaftar Layanan.'
            );

            $this->assertSame(
                $a->getStyle($sel)->getAlignment()->getHorizontal(),
                $p->getStyle($sel)->getAlignment()->getHorizontal(),
                $nama . ': perataan ' . $sel . ' berbeda dari ekspor Pendaftar Layanan.'
            );
        }

        $this->assertSame(
            $a->getStyle('A3')->getBorders()->getBottom()->getBorderStyle(),
            $p->getStyle('A3')->getBorders()->getBottom()->getBorderStyle(),
            $nama . ': garis penutup pita berbeda; tanpa itu ketiga baris keterangan mengambang di atas tabel.'
        );

        foreach ([1, 2, 3, 4] as $n) {
            $this->assertSame(
                $a->getRowDimension($n)->getRowHeight(),
                $p->getRowDimension($n)->getRowHeight(),
                $nama . ': tinggi baris ' . $n . ' berbeda dari ekspor Pendaftar Layanan.'
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
        foreach ($this->semuaEkspor() as $nama => $buat) {
            [$lembar, $akhir] = $buat();

            $gabungan = array_values($lembar->getMergeCells());

            foreach (['1', '2', '3'] as $n) {
                $this->assertContains('A' . $n . ':' . $akhir . $n, $gabungan,
                    $nama . ': baris kepala ke-' . $n . ' tidak digabung selebar tabelnya.');
            }
        }
    }

    // ----------------------------------------------------------- kepala kolom

    /** @test */
    public function kepala_kolom_dan_baris_isinya_serupa_dengan_pendaftar_layanan(): void
    {
        [$a] = $this->acuan();

        foreach ($this->semuaEkspor() as $nama => $buat) {
            [$p] = $buat();
            $this->bandingkanTabel($p, $a, $nama);
        }
    }

    private function bandingkanTabel(Worksheet $p, Worksheet $a, string $nama): void
    {
        $this->assertSame(
            $a->getStyle('A4')->getFill()->getStartColor()->getRGB(),
            $p->getStyle('A4')->getFill()->getStartColor()->getRGB(),
            $nama . ': latar kepala kolom berbeda dari ekspor Pendaftar Layanan.'
        );

        $this->assertSame(
            $a->getStyle('A4')->getFont()->getColor()->getRGB(),
            $p->getStyle('A4')->getFont()->getColor()->getRGB(),
            $nama . ': warna huruf kepala kolom berbeda dari ekspor Pendaftar Layanan.'
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
            $nama . ': baris berselang-selingnya berbeda dari ekspor Pendaftar Layanan.'
        );
    }

    /** @test */
    public function kepala_kolom_tetap_terlihat_saat_digulung(): void
    {
        foreach ($this->semuaEkspor() as $nama => $buat) {
            [$lembar] = $buat();

            $this->assertSame('A5', $lembar->getFreezePane(),
                $nama . ': kepala kolomnya tidak dibekukan; daftar panjang tidak bisa dibaca saat digulung.');
        }
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

    /**
     * @test
     *
     * Lembar kerja dan berkas cetak Data Pelanggan menjawab pertanyaan yang
     * sama.
     *
     * Keduanya diunduh dari satu tombol yang bersebelahan, jadi orang
     * menganggapnya dua rupa dari daftar yang sama. Sewaktu kolom "Pesanan
     * terakhir" baru ada di lembar kerjanya, yang mencetak PDF-nya tidak
     * punya cara tahu kolom itu ada — tidak ada yang hilang, tidak ada yang
     * salah, kolomnya saja yang tidak pernah ikut.
     */
    public function kolom_lembar_kerja_ikut_tercetak_di_berkas_pdfnya(): void
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
                'telp' => '81100000002', 'status' => 'nonaktif', 'created_at' => now(),
            ],
        ]);

        $terakhir = now()->subDays(9);

        $cetak = view('account.customer.ekspor-pdf', [
            'pelanggan' => $orang,
            'pesanan' => [1 => ['jumlah' => 2, 'terakhir' => $terakhir]],
            'saringan' => [],
        ])->render();

        // Gaya ikut terkirim ke Dompdf, dan komentar di dalamnya memakai
        // kata yang sama dengan kepala kolomnya; dibuang dulu supaya yang
        // diperiksa benar-benar yang tercetak.
        $tercetak = preg_replace('/<style\b.*?<\/style>/s', '', $cetak);

        $kepalaLembar = (new PelangganExport($orang, [], []))->headings();

        foreach ($kepalaLembar as $kepala) {
            // Dua kolom ini memang disatukan di berkas cetaknya: "Status"
            // memuat lencana verifikasinya, dan "Pesanan" memuat jumlahnya.
            // Yang dijaga di sini isinya ikut tercetak, bukan judulnya sama
            // persis.
            if (in_array($kepala, ['Email terverifikasi', 'Jumlah pesanan', 'Nama lengkap', 'Status akun'], true)) {
                continue;
            }

            $this->assertStringContainsString($kepala, $tercetak,
                'Kolom "' . $kepala . '" ada di lembar kerjanya tetapi tidak di berkas cetaknya.');
        }

        $this->assertStringContainsString($terakhir->format('d/m/Y'), $tercetak,
            'Tanggal pesanan terakhirnya tidak sampai ke berkas cetak; kolomnya '
            . 'ada tetapi isinya tidak pernah diambil.');

        // Pelanggan yang belum pernah memesan: tanda pisah, bukan sel kosong.
        // Dihitung di dalam <tbody> saja — judul halamannya juga memakai
        // tanda pisah, dan menghitungnya ikut membuat angka ini tidak berarti.
        preg_match('/<tbody>(.*?)<\/tbody>/s', $tercetak, $isi);

        $this->assertSame(2, substr_count($isi[1], '&mdash;') + substr_count($isi[1], '—'),
            'Pelanggan Dua belum pernah memesan; sel "Pesanan" dan "Pesanan '
            . 'terakhir"-nya harus bertanda pisah, bukan dikosongkan.');
    }

    /**
     * @test
     *
     * Kode diskon ikut tercetak di berkas Pendaftar Layanan.
     *
     * Satu kolom untuk DUA hal yang di mata pembacanya sama saja: kode yang
     * diketik pendaftar, dan potongan yang berlaku sendiri (alumni,
     * rombongan, Promo Layanan). Lembar kerjanya sudah punya kolom itu sejak
     * lama; berkas cetaknya belum, sehingga rekap yang dicetak tidak pernah
     * bisa menjawab kenapa satu baris membayar lebih murah daripada yang lain
     * di angkatan yang sama.
     */
    public function kode_diskon_ikut_tercetak_di_berkas_pendaftar(): void
    {
        $baris = collect([
            $this->barisPendaftar(['kode_diskon' => 'SalamQ1', 'nominal_diskon' => '225000']),
            $this->barisPendaftar(['kode_diskon' => 'ALUMNI', 'nominal_diskon' => '550000']),
            $this->barisPendaftar(['kode_diskon' => 'PROMO: Promo Spesial', 'nominal_diskon' => '21000']),
            $this->barisPendaftar(['kode_diskon' => null, 'nominal_diskon' => null]),
        ]);

        $cetak = view('account.pendaftaran_layanan.ekspor-pdf', [
            'baris' => $baris,
            // Larik "nama => nilai", bukan untaian: berkas ini merangkai
            // saringannya sendiri, berbeda dari ekspor Angkatan Layanan yang
            // menerima kalimat siap cetak.
            'saringan' => [],
            'katalog' => \App\Support\PendaftaranSemuaLayanan::katalog(),
        ])->render();

        $tercetak = (string) preg_replace('/<style\b.*?<\/style>/s', '', $cetak);

        $this->assertStringContainsString('Kode diskon', $tercetak,
            'Kepala kolom "Kode diskon" tidak ada di berkas cetaknya, padahal '
            . 'lembar kerjanya punya.');

        /*
         * Kode yang DIKETIK orang dicetak apa adanya: besar-kecil hurufnya
         * berarti, dan panitia mencocokkannya dengan kode yang disebar.
         */
        $this->assertStringContainsString('SalamQ1', $tercetak);

        /*
         * Potongan yang berlaku SENDIRI ditulis dengan huruf biasa — kalau
         * dicetak "ALUMNI", ia terbaca seperti kode yang bisa diketik orang,
         * dan panitia akan dicari-cari kode yang tidak pernah ada.
         */
        $this->assertStringContainsString('Alumni', $tercetak);
        $this->assertStringNotContainsString('ALUMNI', $tercetak);
        $this->assertStringContainsString('Promo: Promo Spesial', $tercetak);

        // Nominalnya ikut: "Alumni" tanpa angka tidak menjawab berapa yang
        // dipotong, dan berkas inilah yang dipakai merekap.
        $this->assertStringContainsString('550.000', $tercetak);

        // Urutannya mengikuti lembar kerjanya.
        preg_match('/<thead>(.*?)<\/thead>/s', $tercetak, $kepala);
        preg_match_all('/<th\b[^>]*>(.*?)<\/th>/s', $kepala[1], $judul);

        $urutan = array_values(array_filter(
            array_map(fn ($t) => trim(strip_tags($t)), $judul[1]),
            fn ($t) => $t !== ''
        ));

        $this->assertSame(['Total bayar', 'Kode diskon', 'Keadaan'],
            array_slice($urutan, -3),
            'Urutan tiga kolom terakhirnya tidak sama dengan lembar kerjanya.');

        $lembar = (new PendaftaranLayananExport(collect(), []))->headings();

        $this->assertGreaterThan(
            array_search('Total bayar', $lembar, true),
            array_search('Kode diskon', $lembar, true),
            'Di lembar kerjanya "Kode diskon" tidak lagi sesudah "Total bayar"; '
            . 'urutan di berkas cetak harus ikut diperbaiki.');
    }

    /** Satu baris pendaftar secukupnya untuk dirender berkas cetaknya. */
    private function barisPendaftar(array $ubah = []): object
    {
        return (object) array_merge([
            'layanan' => 'scopus_camp',
            'nomor' => 'UJI' . \Illuminate\Support\Str::random(4),
            'nama_orang' => 'Pendaftar Uji',
            'affiliasi' => 'Universitas Uji',
            'telp' => '081100000001',
            'email' => 'uji@contoh.test',
            'jumlah' => 1,
            'total' => '5500000',
            'kode_unik' => '86',
            'kode_diskon' => null,
            'nominal_diskon' => null,
            'status' => 'paid',
            'bukti' => null,
            'waktu' => now()->toDateTimeString(),
            'kategori_id' => null,
            'sesi' => 'Angkatan Uji',
        ], $ubah);
    }

    /**
     * @test
     *
     * Sama untuk Angkatan Layanan — kolom yang sama, urutan yang sama.
     *
     * Berbeda dari Data Pelanggan, berkas cetak angkatan memang sengaja
     * meninggalkan beberapa kolom lembar kerjanya; yang dijaga di sini kolom
     * yang DIPILIH ikut benar-benar sampai ke kertas, dan urutannya tidak
     * bertukar terhadap lembar kerjanya.
     */
    public function kode_promo_angkatan_ikut_tercetak_di_berkas_pdfnya(): void
    {
        KategoriLayanan::lupakanPendaftar();

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp',
            'nama' => 'Angkatan Cetakan ' . \Illuminate\Support\Str::random(6),
            'nama_ke' => '9',
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'status' => 'active',
            'biaya' => '5500000',
            'kode_diskon' => 'UJICETAK7',
        ]);

        KategoriLayanan::hitungPendaftar();

        $cetak = view('account.kategori_layanan.cetak-pdf', [
            'angkatan' => collect([$angkatan]),
            // Untaian, bukan larik: ringkasanSaringan() memulangkan kalimat
            // siap cetak, dan tampilannya mencetaknya apa adanya.
            'saringan' => 'Seluruh angkatan, tanpa saringan.',
        ])->render();

        // Gaya dibuang dulu: komentar CSS ikut terkirim ke Dompdf dan bisa
        // memuat kata yang sama dengan kepala kolomnya.
        $tercetak = (string) preg_replace('/<style\b.*?<\/style>/s', '', $cetak);

        $this->assertStringContainsString('Kode promo', $tercetak,
            'Kepala kolom "Kode promo" tidak ada di berkas cetaknya, padahal '
            . 'lembar kerjanya punya.');

        $this->assertStringContainsString('UJICETAK7', $tercetak,
            'Kode promonya tidak sampai ke kertas; kolomnya ada tetapi isinya '
            . 'tidak pernah diambil.');

        /*
         * Urutannya mengikuti lembar kerja: Biaya, Kode promo, Status.
         * Kolom yang sama berpindah tempat antara dua berkas yang diunduh
         * dari dua tombol bersebelahan adalah cacat tersendiri — tidak ada
         * yang salah isinya, cuma tidak bisa dibandingkan berdampingan.
         */
        preg_match('/<thead>(.*?)<\/thead>/s', $tercetak, $kepala);

        preg_match_all('/<th\b[^>]*>(.*?)<\/th>/s', $kepala[1], $judul);

        $urutan = array_map(fn ($t) => trim(strip_tags($t)), $judul[1]);

        $this->assertSame(['Biaya', 'Kode promo', 'Status'],
            array_values(array_slice($urutan, -3)),
            'Urutan tiga kolom terakhirnya tidak sama dengan lembar kerjanya.');

        $lembar = (new AngkatanLayananExport(collect([$angkatan]), ''))->headings();

        $this->assertGreaterThan(
            array_search('Biaya', $lembar, true),
            array_search('Kode promo', $lembar, true),
            'Di lembar kerjanya "Kode promo" tidak lagi sesudah "Biaya"; '
            . 'urutan di berkas cetak harus ikut diperbaiki.');
    }
}
