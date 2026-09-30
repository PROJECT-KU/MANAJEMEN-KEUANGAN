<?php

namespace App\Exports;

use App\Support\PesananPelanggan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Daftar pelanggan sebagai lembar kerja.
 *
 * Berdampingan dengan ekspor PDF, tidak menggantikannya: PDF untuk dibaca dan
 * dilampirkan, lembar kerja untuk diolah — disaring, diurutkan, dan disalin ke
 * tempat lain. Daftar pelanggan hampir selalu berakhir di spreadsheet.
 *
 * FromArray, bukan FromCollection: nilainya perlu dirakit dulu (nomor telepon
 * dijaga tetap untaian, jumlah pesanan datang dari sumber lain), dan
 * FromCollection akan menumpahkan seluruh kolom tabel users apa adanya —
 * termasuk kata sandi tersandi dan token ingat-saya.
 */
class PelangganExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    public function __construct(
        private Collection $pelanggan,
        private array $pesanan,
    ) {}

    public function title(): string
    {
        return 'Data Pelanggan';
    }

    public function headings(): array
    {
        return [
            'No.',
            'Nama lengkap',
            'Username',
            'Email',
            'Email terverifikasi',
            'Telepon',
            'Status akun',
            'Jumlah pesanan',
            'Pesanan terakhir',
            'Bergabung',
        ];
    }

    public function array(): array
    {
        $baris = [];

        foreach ($this->pelanggan->values() as $i => $orang) {
            // Bawaannya array, bukan null: sebagian besar pelanggan memang
            // belum pernah memesan, dan $p['terakhir'] pada null menimbulkan
            // peringatan yang di Laravel jadi galat dan menggagalkan seluruh
            // unduhan.
            $p = $this->pesanan[$orang->id] ?? ['jumlah' => 0, 'terakhir' => null];

            $baris[] = [
                $i + 1,
                $orang->full_name ?: $orang->username,
                $orang->username,
                $orang->email,
                $orang->email_verified_at ? 'Sudah' : 'Belum',
                /*
                 * Diawali kutip tunggal supaya tetap untaian.
                 *
                 * Tanpa itu spreadsheet membaca "081234567890" sebagai bilangan,
                 * membuang nol di depannya, lalu menampilkannya sebagai 8,12346E+10
                 * pada nomor yang cukup panjang. Nomor telepon yang kehilangan nol
                 * depannya tidak bisa dipakai menelepon siapa pun.
                 */
                $orang->telp ? "'" . $orang->telp : '',
                $orang->status === 'active' ? 'Aktif' : 'Nonaktif',
                $p['jumlah'],
                $p['terakhir']?->format('d/m/Y') ?? '',
                optional($orang->created_at)->format('d/m/Y') ?? '',
            ];
        }

        return $baris;
    }

    public function registerEvents(): array
    {
        return [
            // Baris kepala ditebalkan dan dibekukan supaya masih terlihat saat
            // digulung — daftar seratus baris tidak bisa dibaca tanpa itu.
            AfterSheet::class => function (AfterSheet $acara) {
                $lembar = $acara->sheet->getDelegate();
                $lembar->getStyle('A1:J1')->getFont()->setBold(true);
                $lembar->freezePane('A2');
            },
        ];
    }
}
