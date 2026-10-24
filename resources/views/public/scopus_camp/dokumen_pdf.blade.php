{{--
  Faktur dan kwitansi Scopus Camp.

  SELURUH tata letaknya di pendaftaran/dokumen_pdf.blade.php, dipakai bersama
  semua layanan. Berkas ini hanya menyiapkan angkanya.

  Sebelum 24 Okt 2026 ia punya cetakannya sendiri: logo di tengah, tabel
  tanpa kolom Qty maupun Harga satuan, tanpa cap, tanpa pita kaki, dan tanpa
  baris "Sisa tagihan" — jadi dokumen untuk dua layanan yang sama penerbitnya
  terbaca seperti berasal dari dua kantor. Diseragamkan atas permintaan
  pemiliknya.

  Cacat yang ikut hilang bersama cetakan lamanya:

    - date('j F Y', strtotime($angkatan->selesai)) mencetak "1 January 1970"
      untuk angkatan yang tanggal selesainya belum ditentukan;
    - tanggal pembayarannya berbahasa Inggris, sebab APP_LOCALE=en;
    - number_format(null) pada kode unik, PPN, dan diskon.

  Kontraknya SERAGAM dengan cetakan layanan lain — $jenis, $pendaftaran,
  $angkatan, $terbilang — supaya App\Support\LampiranDokumen bisa merakit
  lampirannya tanpa satu cabang per layanan. Namanya pun dokumen_pdf, bukan
  invoice: berkas ini mencetak faktur DAN kwitansi.
--}}
@php
    /*
     * Jenisnya dari pemanggil, BUKAN disimpulkan dari statusnya.
     *
     * Kwitansi hanya boleh terbit kalau uangnya memang sudah diterima, dan
     * yang tahu itu pemanggilnya — surat penerimaan pembayaran. Disimpulkan
     * di sini, satu perubahan status oleh panitia bisa menerbitkan kwitansi
     * untuk uang yang belum masuk.
     */
    $lunas = $jenis === 'kwitansi';

    $total = (int) $pendaftaran->total_pembayaran;
    $potongan = (int) ($pendaftaran->nominal_diskon ?? 0);
    $unik = (int) ($pendaftaran->kode_unik ?? 0);
    $ppn = (int) ($pendaftaran->ppn ?? 0);
    $jumlahOrang = max(1, (int) $pendaftaran->jumlah_pendaftar);

    // Pokoknya = total dikurangi yang ditambahkan dan ditambah yang dipotong,
    // supaya rinciannya menjumlah tepat ke totalnya lagi.
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
        'nama' => ($angkatan->nama ?: 'Scopus Camp') . ($nomorAngkatan !== '' ? ' #' . $nomorAngkatan : ''),
        'nota' => trim(($tanggalKelas ?: '') . ($angkatan->lokasi ? ' · ' . $angkatan->lokasi : ''), ' ·'),
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
        // Kode uniknya SELALU disebut: yang membayar harus mengirim angka
        // yang persis, dan selisih tiga digit membuat pembayarannya tidak
        // cocok dengan tagihan mana pun.
        $baris[] = ['nama' => 'Kode unik pembayaran', 'qty' => 1, 'harga' => $unik];
    }

    $tanggalAcuan = $lunas
        ? ($pendaftaran->bayar_pada ?: $pendaftaran->created_at)
        : $pendaftaran->created_at;
@endphp

@include('pendaftaran.dokumen_pdf', [
    'lunas' => $lunas,
    'nomor' => str_replace('CAMP-', $lunas ? 'KW-' : 'INV-', (string) $pendaftaran->id_transaksi),
    'nomorPendaftaran' => $pendaftaran->id_transaksi,
    'tanggalDokumen' => $lunas
        ? ($tanggalAcuan?->locale('id')->translatedFormat('j F Y, H:i') . ' WIB')
        : ($pendaftaran->created_at?->locale('id')->translatedFormat('j F Y') ?: ''),
    'tanggalCap' => $tanggalAcuan?->locale('id')->translatedFormat('j F Y') ?: '',
    'tenggat' => $pendaftaran->kedaluwarsa_pada
        ? $pendaftaran->kedaluwarsa_pada->locale('id')->translatedFormat('j F Y, H:i') . ' WIB'
        : null,
    'caraBayar' => $pendaftaran->bayar_kanal,
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
