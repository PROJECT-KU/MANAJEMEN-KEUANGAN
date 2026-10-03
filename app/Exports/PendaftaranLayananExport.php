<?php

namespace App\Exports;

use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Pendaftar seluruh layanan sebagai lembar kerja.
 *
 * Berdampingan dengan ekspor PDF, tidak menggantikannya: PDF untuk dibaca dan
 * dilampirkan, lembar kerja untuk diolah — disaring, dijumlahkan, dan dipakai
 * menyusun daftar hadir atau sertifikat.
 *
 * FromArray, bukan FromCollection: nilainya perlu dirakit dulu — status mentah
 * diterjemahkan, nomor telepon dijaga tetap untaian, dan nama layanan dibaca
 * dari katalog.
 */
class PendaftaranLayananExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    public function __construct(
        private Collection $baris,
        private array $katalog,
        private array $saringan = [],
    ) {}

    public function title(): string
    {
        return 'Pendaftar Layanan';
    }

    public function headings(): array
    {
        return [
            'No.',
            'Layanan',
            'Nomor pendaftaran',
            'Nama',
            'Email',
            'Telepon',
            'Afiliasi',
            'Sesi / angkatan',
            'Angkatan ke-',
            'Jumlah orang',
            'Total bayar',
            'Kode unik',
            'Kode diskon',
            'Keadaan',
            'Status asli',
            'Bukti bayar',
            'Tanggal daftar',
        ];
    }

    public function array(): array
    {
        $hasil = [];

        foreach ($this->baris->values() as $i => $b) {
            $keadaan = Pendaftaran::keadaanDari($b->status);
            $bukti = Pendaftaran::buktiBaris($b);

            $hasil[] = [
                $i + 1,
                $this->katalog[$b->layanan]['nama'] ?? $b->layanan,
                $b->nomor,
                $b->nama_orang,
                $b->email,
                /*
                 * Diawali kutip tunggal supaya tetap untaian.
                 *
                 * Tanpa itu spreadsheet membaca '082220906000' sebagai
                 * bilangan, membuang nol depannya, lalu menampilkannya sebagai
                 * 8,22221E+10 pada nomor yang cukup panjang. Nomor telepon yang
                 * kehilangan nol depannya tidak bisa dipakai menghubungi
                 * siapa pun.
                 */
                $b->telp ? "'" . $b->telp : '',
                $b->affiliasi ?? '',
                Pendaftaran::sesiBaris($b) ?? '',
                /*
                 * Nomor angkatan jadi KOLOM TERSENDIRI, bukan disambung ke
                 * nama sesinya.
                 *
                 * Lembar kerja ini diunduh untuk direkap — disaring,
                 * dikelompokkan, dan dijumlahkan. Nomor yang menempel di
                 * dalam untaian nama tidak bisa dipakai mengelompokkan;
                 * sebagai kolom sendiri ia bisa. Dan tetap untaian, bukan
                 * bilangan: nomornya penanda, bukan angka yang dijumlahkan.
                 */
                Pendaftaran::nomorAngkatanBaris($b) ?? '',
                (int) $b->jumlah,
                // Angka, bukan untaian berformat: lembar kerja memang diunduh
                // supaya kolom ini bisa dijumlahkan sendiri oleh penerimanya.
                (int) $b->total,
                $b->kode_unik !== null && $b->kode_unik !== '' ? (int) $b->kode_unik : '',
                $b->kode_diskon ?? '',
                $keadaan === 'lain'
                    ? 'Belum dikenali'
                    : Pendaftaran::KEADAAN[$keadaan]['label'],
                // Status mentah ikut dibawa. Terjemahannya menyatukan sembilan
                // nilai jadi empat keadaan, jadi tanpa kolom ini selisih antar
                // layanan — 'expired' vs 'Pendaftaran Dibatalkan' — hilang dari
                // berkasnya dan tidak bisa ditelusuri lagi.
                $b->status,
                $this->sebutanBukti($bukti),
                Pendaftaran::waktuBaris($b)?->format('d/m/Y H:i') ?? '',
            ];
        }

        return $hasil;
    }

    /**
     * Keadaan bukti bayar dalam satu kata yang jujur.
     *
     * Tiga keadaan, bukan dua: 72 dari 183 nilai menunjuk berkas yang sudah
     * tidak ada di cakram, dan menuliskannya "Ada" membuat penerima berkas
     * ini mengira buktinya masih bisa dibuka.
     */
    private function sebutanBukti(array $bukti): string
    {
        if (! $bukti['nilai']) {
            return 'Belum diunggah';
        }

        return $bukti['ada'] ? 'Ada' : 'Berkasnya hilang';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $acara) {
                $lembar = $acara->sheet->getDelegate();

                /*
                 * Keterangan berkas disisipkan DI ATAS kepala kolom.
                 *
                 * Tanpa ini, lembar kerja berisi 29 baris tersaring terbaca
                 * persis seperti daftar pendaftar yang lengkap — dan lembar
                 * kerja justru berkas yang paling sering diteruskan ke orang
                 * lain, terlepas dari layar tempat ia diunduh.
                 */
                $lembar->insertNewRowBefore(1, 3);

                $orang = 0;
                $uang = 0;

                foreach ($this->baris as $b) {
                    $orang += (int) $b->jumlah;

                    if (Pendaftaran::keadaanDari($b->status) === 'lunas') {
                        $uang += (int) $b->total;
                    }
                }

                $lembar->setCellValue('A1', 'Pendaftar Layanan — MIS Rumah Scopus');
                $lembar->setCellValue('A2', 'Diunduh ' . now()->format('d/m/Y H:i') . ' WIB'
                    . ' · ' . number_format($this->baris->count(), 0, ',', '.') . ' pendaftaran'
                    . ' · ' . number_format($orang, 0, ',', '.') . ' orang'
                    . ' · lunas Rp ' . number_format($uang, 0, ',', '.'));
                $lembar->setCellValue('A3', 'Saringan: ' . ($this->saringan === []
                    ? 'tanpa saringan (seluruh pendaftar)'
                    : implode(' · ', array_map(
                        fn ($nama, $nilai) => $nama . ': ' . $nilai,
                        array_keys($this->saringan),
                        $this->saringan
                    ))));

                $lembar->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $lembar->getStyle('A2:A3')->getFont()->setSize(10);
                $lembar->getStyle('A4:Q4')->getFont()->setBold(true);

                // Dibekukan di bawah kepala kolom supaya ia tetap terlihat saat
                // digulung — 187 baris tidak bisa dibaca tanpa itu.
                $lembar->freezePane('A5');
            },
        ];
    }
}
