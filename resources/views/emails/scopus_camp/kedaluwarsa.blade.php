{{--
  Kabar kursi yang dilepas karena tenggat bayarnya terlewat.

  Isinya saja; rupanya dirakit kerangka bersama di emails/pendaftaran, supaya
  surat ini tidak bisa berbeda rupa dari surat bukti pendaftarannya.

  NADANYA TIDAK MENUDUH. Sebagian yang menerima surat ini SUDAH membayar —
  mutasinya belum tercocokkan, atau pembayarannya masuk beberapa menit
  sesudah tenggatnya. Surat yang menyatakan "Anda tidak membayar" kepada
  orang yang baru saja mengirim lima juta rupiah adalah cara tercepat
  kehilangan kepercayaannya.
--}}
@php
    use App\Support\PendaftaranSemuaLayanan as Pendaftaran;

    $namaKelas = trim($angkatan->nama . ' ' . ($angkatan->nama_ke ? '#' . $angkatan->nama_ke : ''));

    $rincian = array_filter([
        'Nomor pendaftaran' => strtoupper((string) $pendaftaran->id_transaksi),
        'Kelas' => $namaKelas,
        'Nama' => $pendaftaran->nama,
        'Nominal tagihan' => (int) $pendaftaran->total_pembayaran > 0
            ? 'Rp ' . number_format((int) $pendaftaran->total_pembayaran, 0, ',', '.')
            : null,
        'Batas pembayaran' => $pendaftaran->kedaluwarsa_pada
            ? $pendaftaran->kedaluwarsa_pada->locale('id')->translatedFormat('j F Y, H:i') . ' WIB'
            : null,
    ], fn ($v) => $v !== null && $v !== '');

    /*
     * Tautan daftar ulang hanya kalau angkatannya MASIH terbuka. Mengajak
     * mendaftar lagi ke angkatan yang sudah berjalan atau sudah penuh
     * membuat orangnya menekan tautan yang memantulkannya kembali.
     */
    $masihBuka = $angkatan->status === 'active'
        && $angkatan->mulai !== null
        && \Carbon\Carbon::parse($angkatan->mulai)->startOfDay()->gt(now()->startOfDay())
        && ($angkatan->total_kuota === null || (int) $angkatan->sisa_kuota > 0);

    $tautan = $masihBuka ? [[
        'label' => 'Kursinya masih bisa diambil lagi',
        'teks' => 'Daftar ulang sekarang',
        'alamat' => route('public.scopuscamp.formpendaftaran', $angkatan->getKey()),
    ]] : [];
@endphp
@include('emails.pendaftaran.kerangka', [
    'judul' => 'Kursi Anda sudah kami lepas',
    'lencana' => ['teks' => 'Tenggat terlewat', 'latar' => '#fdecea', 'tinta' => '#8e2318'],
    'sapaan' => $pendaftaran->nama ?: 'Bapak/Ibu',
    'pembuka' => 'Pembayaran untuk pendaftaran di bawah ini belum masuk sampai batas '
        . 'waktunya, jadi kursinya kami lepas untuk pendaftar lain.',
    'rincian' => $rincian,
    'sorot' => [
        'judul' => 'Merasa sudah membayar?',
        'isi' => 'Balasan ke alamat ini tidak dibaca siapa pun. Hubungi admin resmi kami '
            . 'dengan menyebut nomor pendaftaran di atas — pembayarannya tidak hilang, '
            . 'hanya konfirmasinya yang tertunda.',
    ],
    'penutup' => $masihBuka
        ? ['Masih ingin ikut? Kursinya bisa diambil lagi selama kuotanya belum penuh.']
        : ['Angkatan ini sudah tidak menerima pendaftaran baru. Jadwal berikutnya '
            . 'diumumkan di halaman Scopus Camp.'],
    'tautan' => $tautan,
])
