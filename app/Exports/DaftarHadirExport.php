<?php

namespace App\Exports;

use App\PendaftaranPeserta;
use App\Support\DaftarPeserta;
use App\Support\NomorTelepon;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Daftar hadir: satu baris per ORANG, bukan per pendaftaran.
 *
 * Ekspor yang sudah ada membawa satu baris per pendaftaran, jadi rombongan
 * lima orang muncul sebagai satu nama. Terukur: 109 pendaftaran rombongan
 * mewakili 296 orang, sementara berkasnya cuma memuat 109 nama.
 *
 * Padahal layar Peserta sendiri menyebut daftar itu "yang dipakai
 * menerbitkan sertifikat dan memasukkan orang ke grup" — dan tidak ada satu
 * pun jalan mengeluarkannya. Akibatnya terlihat di data: dari 296 orang,
 * baru 9 nama peserta yang pernah dicatat. Panitia tidak punya alasan
 * mengisi daftar yang tidak bisa dipakai.
 *
 * Yang belum punya nama TETAP dibariskan, bertuliskan "(belum diisi)".
 * Dibuang, berkasnya terbaca seperti daftar yang sudah lengkap — dan yang
 * memegangnya di meja registrasi tidak tahu ada kursi yang namanya hilang.
 */
class DaftarHadirExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    /** @var array<string, Collection>|null peserta per pendaftaran, dibaca sekali */
    private ?array $peserta = null;

    public function __construct(
        private Collection $baris,
        private array $katalog,
        private array $saringan = [],
    ) {}

    public function title(): string
    {
        return 'Daftar Hadir';
    }

    public function headings(): array
    {
        return [
            'No.',
            'Layanan',
            'Sesi / angkatan',
            'Nomor pendaftaran',
            'Nama',
            'Peran',
            'Telepon',
            'Email',
            'Keadaan pendaftaran',
        ];
    }

    public function array(): array
    {
        $this->bacaPeserta();

        $hasil = [];
        $no = 0;

        foreach ($this->baris as $b) {
            $keadaan = Pendaftaran::keadaanDari($b->status);
            $label = $keadaan === 'lain'
                ? 'Belum dikenali'
                : Pendaftaran::KEADAAN[$keadaan]['label'];

            $umum = [
                $this->katalog[$b->layanan]['nama'] ?? $b->layanan,
                Pendaftaran::sesiBaris($b) ?? '',
                $b->nomor,
            ];

            // Pendaftarnya sendiri selalu baris pertama rombongannya.
            $hasil[] = array_merge([++$no], $umum, [
                $b->nama_orang,
                'Pendaftar',
                $this->telp($b->telp),
                $b->email ?? '',
                $label,
            ]);

            $lain = $this->peserta[$b->layanan . '|' . $b->id] ?? collect();

            foreach ($lain as $orang) {
                $hasil[] = array_merge([++$no], $umum, [
                    $orang->nama ?: '(tanpa nama)',
                    'Peserta',
                    $this->telp($orang->telp),
                    $orang->email ?? '',
                    $label,
                ]);
            }

            /*
             * Kursi yang dibayar tetapi namanya belum diisi tetap dibariskan.
             * Tanpa baris ini, berkasnya terbaca seperti daftar yang sudah
             * lengkap, dan yang memegangnya di meja registrasi tidak tahu
             * ada orang yang namanya belum pernah ditanyakan.
             */
            $kurang = max(0, (int) $b->jumlah - 1 - $lain->count());

            for ($i = 0; $i < $kurang; $i++) {
                $hasil[] = array_merge([++$no], $umum, [
                    '(belum diisi)', 'Peserta', '', '', $label,
                ]);
            }
        }

        return $hasil;
    }

    /**
     * Peserta seluruh baris dibaca dalam SATU kueri.
     *
     * Dibaca per pendaftaran di dalam gelung, berkas berisi 290 pendaftaran
     * berarti 290 kueri tambahan — dan ekspor memang pekerjaan yang
     * menyentuh seluruh tabel sekaligus.
     */
    private function bacaPeserta(): void
    {
        if ($this->peserta !== null) {
            return;
        }

        $kunci = $this->baris->map(fn ($b) => (string) $b->id)->all();

        $this->peserta = PendaftaranPeserta::query()
            ->whereIn('pendaftaran_id', $kunci)
            ->orderBy('urutan')
            ->get()
            ->groupBy(fn ($p) => $p->layanan . '|' . $p->pendaftaran_id)
            ->all();
    }

    /**
     * Diawali kutip tunggal supaya tetap untaian.
     *
     * Tanpa itu spreadsheet membaca '082220906000' sebagai bilangan,
     * membuang nol depannya, lalu menampilkannya sebagai 8,22221E+10. Nomor
     * yang kehilangan nol depannya tidak bisa dipakai menghubungi siapa pun.
     */
    private function telp(?string $nomor): string
    {
        $rapi = trim((string) $nomor);

        if ($rapi === '') {
            return '';
        }

        return "'" . DaftarPeserta::bentukLokal(NomorTelepon::rapikan($rapi));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $acara) {
                $lembar = $acara->sheet->getDelegate();

                $lembar->insertNewRowBefore(1, 3);

                $orang = 0;
                $belum = 0;

                foreach ($this->array() as $baris) {
                    $orang++;

                    if ($baris[4] === '(belum diisi)') {
                        $belum++;
                    }
                }

                $lembar->setCellValue('A1', 'Daftar Hadir — MIS Rumah Scopus');
                $lembar->setCellValue('A2', 'Diunduh ' . now()->format('d/m/Y H:i') . ' WIB'
                    . ' · ' . number_format($orang, 0, ',', '.') . ' orang'
                    . ' dari ' . number_format($this->baris->count(), 0, ',', '.') . ' pendaftaran'
                    . ($belum > 0
                        ? ' · ' . number_format($belum, 0, ',', '.') . ' nama belum diisi'
                        : ''));
                $lembar->setCellValue('A3', 'Saringan: ' . ($this->saringan === []
                    ? 'tanpa saringan (seluruh pendaftar)'
                    : implode(' · ', array_map(
                        fn ($nama, $nilai) => $nama . ': ' . $nilai,
                        array_keys($this->saringan),
                        $this->saringan
                    ))));

                $lembar->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $lembar->getStyle('A2:A3')->getFont()->setSize(10);
                $lembar->getStyle('A4:I4')->getFont()->setBold(true);

                $lembar->freezePane('A5');
            },
        ];
    }
}
