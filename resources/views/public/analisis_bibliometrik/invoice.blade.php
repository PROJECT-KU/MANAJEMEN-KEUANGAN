{{--
  Faktur Analisis Bibliometrik.

  SELURUH tata letaknya di pendaftaran/dokumen_pdf.blade.php, dipakai bersama
  semua layanan. Berkas ini hanya menyiapkan angkanya — lihat alasan
  penyeragamannya di kerangka itu.

  Cetakan lamanya bahkan membungkus isinya dengan <form action="{{ route(
  'account.gaji.store') }}"> — borang milik layar GAJI, di dalam faktur
  pendaftaran. Ia tidak pernah bisa dikirim dari dalam PDF, jadi tidak ada
  akibatnya; tetapi rute yang salah di dalam dokumen keuangan adalah hal yang
  menyesatkan siapa pun yang membacanya nanti.

  Yang dikirim AnalisisBibliometrikMail: $analisisbibliometrik,
  $categoriesanalisisbibliometrik, $terbilang.
--}}
@php
    $pendaftaran = $analisisbibliometrik;
    $angkatan = $categoriesanalisisbibliometrik;

    $lunas = in_array((string) $pendaftaran->status,
        \App\Support\PendaftaranSemuaLayanan::KEADAAN['lunas']['nilai'], true);

    $total = (int) $pendaftaran->total_pembayaran;
    $potongan = (int) ($pendaftaran->nominal_diskon ?? 0);
    $unik = (int) ($pendaftaran->kode_unik ?? 0);
    $ppn = (int) ($pendaftaran->ppn ?? 0);
    $jumlahOrang = max(1, (int) $pendaftaran->jumlah_pendaftar);

    $pokok = $total + $potongan - $unik - $ppn;
    $perOrang = $pokok % $jumlahOrang === 0;

    $nomorAngkatan = trim((string) ($angkatan->nama_ke ?? ''));

    $tanggalKelas = $angkatan->mulai
        ? \App\Support\RentangTanggal::tulis(
            \Carbon\Carbon::parse($angkatan->mulai),
            $angkatan->selesai ? \Carbon\Carbon::parse($angkatan->selesai) : null
        )
        : null;

    $baris = [[
        'nama' => ($angkatan->nama ?: 'Analisis Bibliometrik')
            . ($nomorAngkatan !== '' ? ' #' . $nomorAngkatan : ''),
        'nota' => $tanggalKelas ?: '',
        'qty' => $perOrang ? $jumlahOrang : 1,
        'harga' => $perOrang ? intdiv($pokok, $jumlahOrang) : $pokok,
    ]];

    if ($potongan > 0) {
        $baris[] = [
            'nama' => 'Potongan' . ($pendaftaran->kode_diskon ? ' (' . $pendaftaran->kode_diskon . ')' : ''),
            'qty' => 1,
            'harga' => -1 * $potongan,
        ];
    }

    if ($ppn > 0) {
        $baris[] = ['nama' => 'PPN', 'qty' => 1, 'harga' => $ppn];
    }

    if ($unik > 0) {
        $baris[] = ['nama' => 'Kode unik pembayaran', 'qty' => 1, 'harga' => $unik];
    }
@endphp

@include('pendaftaran.dokumen_pdf', [
    'lunas' => $lunas,
    'nomor' => str_replace('BIB-', $lunas ? 'KW-' : 'INV-', (string) $pendaftaran->id_transaksi),
    'nomorPendaftaran' => $pendaftaran->id_transaksi,
    'tanggalDokumen' => $pendaftaran->created_at?->locale('id')->translatedFormat('j F Y') ?: '',
    'tanggalCap' => $pendaftaran->created_at?->locale('id')->translatedFormat('j F Y') ?: '',

    /*
     * Bibliometrik TIDAK punya kolom kedaluwarsa_pada maupun bayar_kanal —
     * layanannya belum memakai gerbang pembayaran. Dikirim null, bukan
     * dibiarkan: kerangkanya mencetak "-" untuk null, sedangkan variabel yang
     * tidak dikirim sama sekali akan melempar "Undefined variable".
     */
    'tenggat' => null,
    'caraBayar' => null,

    'pelanggan' => [
        'nama' => $pendaftaran->nama,
        'email' => $pendaftaran->email,
        'telp' => $pendaftaran->telp
            ? \App\Support\NomorTelepon::tampil($pendaftaran->telp)
            : null,
    ],
    'baris' => $baris,
    'total' => $total,
    'dibayar' => $lunas ? $total : 0,
    'terbilang' => $terbilang,
])
