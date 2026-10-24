{{--
  Faktur dan kwitansi Scopus Kafe.

  SELURUH tata letaknya di pendaftaran/dokumen_pdf.blade.php, dipakai bersama
  semua layanan. Berkas ini hanya menyiapkan angkanya.

  Scopus Kafe BELUM PERNAH punya lampiran sama sekali — suratnya dikirim
  tanpa satu pun dokumen, jadi yang harus menyerahkan tagihannya ke bendahara
  kantor tidak punya apa pun untuk diserahkan. Ditambahkan 24 Okt 2026 atas
  permintaan pemiliknya.

  Bentuk datanya berbeda dari layanan lain: SAMPAI TIGA SESI tersimpan dalam
  satu baris, masing-masing dengan kolom biaya dan kode uniknya sendiri. Jadi
  tiap sesi jadi barisnya sendiri di rinciannya — dijumlahkan jadi satu baris,
  yang membukanya tidak punya cara tahu sesi mana yang ia bayar.

  Yang dikirim pemanggilnya: $jenis, $pendaftaran, $terbilang.
--}}
@php
    $lunas = $jenis === 'kwitansi';

    $total = (int) $pendaftaran->total_keseluruhan_pembayaran;

    /*
     * Ketiga sesi dibaca dari kolomnya masing-masing. Yang namanya kosong
     * dilewati: pemesanan satu sesi mengisi kolom pertama saja, dan baris
     * kosong di faktur terbaca sebagai data yang hilang.
     */
    $sesiKe = [
        ['sesi', 'waktu_mulai', 'waktu_selesai', 'lokasi', 'biaya', 'kode_unik_pembayaran'],
        ['sesi_kedua', 'waktu_mulai_kedua', 'waktu_selesai_kedua', 'lokasi_kedua',
            'biaya_kedua', 'kode_unik_pembayaran_kedua'],
        ['sesi_ketiga', 'waktu_mulai_ketiga', 'waktu_selesai_ketiga', 'lokasi_ketiga',
            'biaya_ketiga', 'kode_unik_pembayaran_ketiga'],
    ];

    $baris = [];
    $unik = 0;

    foreach ($sesiKe as $ke => [$kNama, $kMulai, $kSelesai, $kLokasi, $kBiaya, $kUnik]) {
        $nama = trim((string) $pendaftaran->{$kNama});

        if ($nama === '') {
            continue;
        }

        $jam = trim(implode(' - ', array_filter([
            trim((string) $pendaftaran->{$kMulai}),
            trim((string) $pendaftaran->{$kSelesai}),
        ])));

        $lokasi = trim((string) $pendaftaran->{$kLokasi});

        $baris[] = [
            'nama' => 'Sesi ' . ($ke + 1) . ' — ' . $nama,
            'nota' => trim($jam . ($lokasi !== '' ? ' · ' . $lokasi : ''), ' ·'),
            'qty' => 1,
            'harga' => (int) $pendaftaran->{$kBiaya},
        ];

        $unik += (int) $pendaftaran->{$kUnik};
    }

    /*
     * Kode uniknya dijumlahkan dari ketiga sesi lalu disebut SEKALI.
     *
     * Disebut per sesi, rinciannya punya tiga baris recehan yang tidak ada
     * gunanya dibaca terpisah — yang perlu diketahui pembayarnya cuma berapa
     * total angka ganjil yang harus ia kirim.
     */
    if ($unik > 0) {
        $baris[] = ['nama' => 'Kode unik pembayaran', 'qty' => 1, 'harga' => $unik];
    }

    // Pemesanan tanpa satu pun sesi bernama tidak mungkin ada, tetapi faktur
    // tanpa satu pun baris akan tergambar sebagai tabel kosong; disebut apa
    // adanya daripada dibiarkan bolong.
    if ($baris === []) {
        $baris[] = ['nama' => 'Scopus Kafe', 'qty' => 1, 'harga' => $total];
    }
@endphp

@include('pendaftaran.dokumen_pdf', [
    'lunas' => $lunas,
    'nomor' => str_replace('KAFE-', $lunas ? 'KW-' : 'INV-', (string) $pendaftaran->id_pemesanan),
    'nomorPendaftaran' => $pendaftaran->id_pemesanan,
    'tanggalDokumen' => $pendaftaran->created_at?->locale('id')->translatedFormat('j F Y') ?: '',
    'tanggalCap' => $pendaftaran->created_at?->locale('id')->translatedFormat('j F Y') ?: '',

    // Scopus Kafe belum memakai gerbang pembayaran, jadi ia tidak punya
    // tenggat maupun kanal. Dikirim null, bukan dibiarkan: kerangkanya
    // mencetak "-", sedangkan variabel yang tidak dikirim melempar.
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
