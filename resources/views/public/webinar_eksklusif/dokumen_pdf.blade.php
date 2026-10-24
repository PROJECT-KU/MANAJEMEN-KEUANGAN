{{--
  Faktur dan kwitansi Webinar Eksklusif.

  SELURUH tata letaknya kini di pendaftaran/dokumen_pdf.blade.php, dipakai
  bersama semua layanan. Berkas ini hanya menyiapkan angkanya.

  Dokumen INILAH acuan yang diminta pemiliknya untuk semua layanan 24 Okt
  2026 — susunannya diangkat apa adanya ke kerangka itu, lalu dokumen ini
  ikut memakainya. Diangkat, bukan disalin: dua salinan cetakan keuangan
  yang sama akan berbeda perlahan, dan yang berbeda pada dokumen keuangan
  adalah angka.

  Yang dikirim pemanggilnya: $jenis, $pendaftaran, $sesi, $terbilang.
--}}
@php
    $lunas = $jenis === 'kwitansi';

    $total = (int) $pendaftaran->total_pembayaran;
    $potongan = (int) ($pendaftaran->nominal_diskon ?? 0);
    $unik = (int) ($pendaftaran->kode_unik ?? 0);
    $jumlahOrang = max(1, (int) $pendaftaran->jumlah_pendaftar);

    $pokok = $total + $potongan - $unik;

    /*
     * Harga satuannya hanya dipakai kalau pokoknya habis dibagi jumlah
     * orangnya. Kalau tidak, satu baris utuh lebih jujur daripada harga
     * satuan yang dibulatkan dan jumlahnya tidak cocok.
     */
    $perOrang = $pokok % $jumlahOrang === 0;

    /*
     * Keterangan barisnya menyebut ANGKATAN dan TANGGAL KEGIATANNYA.
     *
     * Nama sesi saja tidak cukup pada dokumen yang disimpan: judul yang sama
     * dipakai lagi di angkatan berikutnya, dan yang membuka faktur lamanya
     * berbulan-bulan kemudian tidak punya cara tahu angkatan mana yang ia
     * bayar. Bendahara yang mencocokkannya pun tidak.
     */
    $angkatan = trim((string) ($sesi?->nama_ke ?? ''));

    $tanggalSesi = $sesi?->mulai
        ? \App\Support\RentangTanggal::tulis(
            \Carbon\Carbon::parse($sesi->mulai),
            $sesi->selesai ? \Carbon\Carbon::parse($sesi->selesai) : null
        )
        : null;

    /*
     * Rinciannya DICOCOKKAN dengan yang dikirim ke DOKU (lihat
     * Doku::buatTagihan): judul sesi kali jumlah peserta, lalu potongan
     * sebagai baris negatif, lalu kode unik. Berbeda susunannya, pembayar
     * memegang dua kertas yang menjumlahkan angka yang sama dengan cara yang
     * berbeda.
     */
    $baris = [[
        'nama' => ($sesi?->nama ?: 'Webinar Eksklusif') . ($angkatan !== '' ? ' #' . $angkatan : ''),
        'nota' => trim(($tanggalSesi ?: '') . ($sesi?->jam ? ' · ' . $sesi->jam : ''), ' ·'),
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

    if ($unik > 0) {
        // Kode uniknya SELALU disebut: yang membayar harus mengirim angka
        // yang persis, dan selisih tiga digit membuat pembayarannya tidak
        // cocok dengan tagihan mana pun.
        $baris[] = ['nama' => 'Kode unik pembayaran', 'qty' => 1, 'harga' => $unik];
    }

    $tanggalAcuan = $lunas ? $pendaftaran->bayar_pada : $pendaftaran->created_at;
@endphp

@include('pendaftaran.dokumen_pdf', [
    'lunas' => $lunas,
    'nomor' => str_replace('WE-', $lunas ? 'KW-' : 'INV-', (string) $pendaftaran->id_transaksi),
    'nomorPendaftaran' => $pendaftaran->id_transaksi,
    'tanggalDokumen' => $lunas
        ? ($pendaftaran->bayar_pada?->locale('id')->translatedFormat('j F Y, H:i') . ' WIB')
        : ($pendaftaran->created_at?->locale('id')->translatedFormat('j F Y') ?: ''),
    'tanggalCap' => $tanggalAcuan?->locale('id')->translatedFormat('j F Y') ?: '',
    'tenggat' => $pendaftaran->kedaluwarsa_pada
        ? $pendaftaran->kedaluwarsa_pada->locale('id')->translatedFormat('j F Y, H:i') . ' WIB'
        : null,
    'caraBayar' => $pendaftaran->bayar_kanal,
    'pelanggan' => [
        'nama' => $pendaftaran->nama,
        'email' => $pendaftaran->email,
        'telp' => $pendaftaran->telp,
    ],
    'baris' => $baris,
    'total' => $total,
    'dibayar' => $lunas ? $total : 0,
    'terbilang' => $terbilang,
])
