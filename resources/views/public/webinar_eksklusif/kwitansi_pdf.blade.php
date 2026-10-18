{{--
  KWITANSI pelunasan — dilampirkan ke surat "pembayaran diterima".

  Dirakit Dompdf, dan itu mengikat bentuknya:

    - TIDAK ADA flex maupun grid. Dompdf tidak mengenal keduanya; yang
      memakainya tergambar menumpuk di pojok kiri atas tanpa satu pun galat.
      Susunannya tabel, sama seperti surat.

    - Lebar kolomnya persen dan jumlahnya HARUS tepat 100. Kurang atau
      lebih, Dompdf membagikan sisanya sendiri menurut aturan yang tidak
      terlihat dari sini, dan kolomnya melenceng tanpa sebab yang jelas.

    - Logonya JALUR BERKAS, bukan alamat http. Alamat jarak jauh menuntut
      isRemoteEnabled dan satu perjalanan jaringan tiap kwitansi — satu
      kemungkinan gagal yang tidak perlu, pada berkas yang justru dibuat
      saat uang orangnya baru saja masuk.

  Logonya yayasan, bukan MIS: ini dokumen yang disimpan orangnya sebagai
  bukti bayar, dan mungkin ditunjukkan ke bendahara kantornya.
--}}
@php
    $logo = public_path('assets/img/logo-rsc-email.png');

    // Nomornya DITURUNKAN dari nomor pendaftaran, bukan nomor baru.
    // Satu pendaftaran satu pelunasan, jadi nomor terpisah cuma menambah
    // satu hal yang bisa tidak cocok saat panitia mencocokkan keduanya.
    $nomor = str_replace('WE-', 'KW-', (string) $pendaftaran->id_transaksi);

    $total = (int) $pendaftaran->total_pembayaran;
    $potongan = (int) ($pendaftaran->nominal_diskon ?? 0);
    $unik = (int) ($pendaftaran->kode_unik ?? 0);
    $pokok = $total + $potongan - $unik;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kwitansi {{ $nomor }}</title>
    <style>
        @page { margin: 28px 34px; }

        body {
            margin: 0;
            /* Helvetica, BUKAN DejaVu Sans.

               DejaVu DISISIPKAN utuh ke tiap berkas; Helvetica salah satu
               font bawaan PDF yang tidak perlu disisipkan sama sekali.
               Terukur pada kwitansi yang sama: 1.289.545 bita turun jadi
               angka di bawah seratus ribu — lampiran yang sebelumnya lebih
               berat daripada seluruh suratnya.

               Bisa ditukar karena isinya ASCII semua. Begitu ada satu
               aksara di luar itu, Helvetica menggambarnya sebagai kotak
               kosong tanpa satu pun galat, dan DejaVu harus kembali. */
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.55;
        }

        table { width: 100%; border-collapse: collapse; }

        .kop td { vertical-align: middle; padding: 0 0 14px; }
        .kop-logo { width: 46%; }
        .kop-kanan { width: 54%; text-align: right; }

        .judul { margin: 0; font-size: 22px; font-weight: bold; color: #0f2b5b; letter-spacing: 2px; }
        .nomor { margin: 2px 0 0; font-size: 11px; color: #64748b; }

        .garis { border-top: 2px solid #0f2b5b; height: 1px; font-size: 0; line-height: 0; }

        .label { color: #64748b; }
        .isi { font-weight: bold; color: #0f2b5b; }

        .baris td { padding: 7px 0; border-bottom: 1px solid #eef2f7; }
        .baris .label { width: 34%; }
        .baris .isi { width: 66%; text-align: right; }

        .jumlah {
            margin: 16px 0 0;
            padding: 12px 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .jumlah td { padding: 0; }
        .jumlah-label { width: 40%; color: #64748b; }
        .jumlah-nilai { width: 60%; text-align: right; font-size: 19px; font-weight: bold; color: #0f2b5b; }

        .terbilang {
            margin: 8px 0 0;
            padding: 9px 14px;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 6px;
            font-style: italic;
            color: #9a3412;
        }

        .cap {
            display: inline-block;
            padding: 7px 20px;
            border: 2px solid #15803d;
            border-radius: 6px;
            color: #15803d;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 3px;
        }

        .kaki { margin-top: 22px; color: #64748b; font-size: 10px; line-height: 1.6; }
        .kaki-kiri { width: 60%; }
        .kaki-kanan { width: 40%; text-align: center; }
        .kaki td { vertical-align: top; padding: 0; }
    </style>
</head>
<body>

<table class="kop">
    <tr>
        <td class="kop-logo"><img src="{{ $logo }}" alt="Rumah Scopus Foundation" width="180"></td>
        <td class="kop-kanan">
            <p class="judul">KWITANSI</p>
            <p class="nomor">No. {{ $nomor }}</p>
        </td>
    </tr>
</table>

<div class="garis"></div>

<table class="baris">
    <tr>
        <td class="label">Telah diterima dari</td>
        <td class="isi">{{ $pendaftaran->nama }}</td>
    </tr>
    <tr>
        <td class="label">Untuk pembayaran</td>
        <td class="isi">{{ $sesi?->nama ?: 'Webinar Eksklusif' }}</td>
    </tr>
    @if ($sesi?->mulai)
        <tr>
            <td class="label">Tanggal sesi</td>
            <td class="isi">
                {{ \Carbon\Carbon::parse($sesi->mulai)->locale('id')->translatedFormat('j F Y') }}
            </td>
        </tr>
    @endif
    <tr>
        <td class="label">Jumlah peserta</td>
        <td class="isi">{{ (int) $pendaftaran->jumlah_pendaftar }} orang</td>
    </tr>
    <tr>
        <td class="label">Nomor pendaftaran</td>
        <td class="isi">{{ $pendaftaran->id_transaksi }}</td>
    </tr>
</table>

{{-- Rinciannya digambar HANYA kalau ada yang perlu dirinci.

     Tanpa potongan maupun kode unik, ketiga barisnya berbunyi sama:
     "Biaya 130.000 / Total 130.000". Baris yang mengulang baris di
     bawahnya membuat orang mencari bedanya, dan tidak menemukannya. --}}
@if ($potongan > 0 || $unik > 0)
    <table class="baris" style="margin-top:4px;">
        <tr>
            <td class="label">Biaya pendaftaran</td>
            <td class="isi">Rp {{ number_format($pokok, 0, ',', '.') }}</td>
        </tr>
        @if ($potongan > 0)
            <tr>
                <td class="label">Potongan{{ $pendaftaran->kode_diskon ? ' (' . $pendaftaran->kode_diskon . ')' : '' }}</td>
                <td class="isi">- Rp {{ number_format($potongan, 0, ',', '.') }}</td>
            </tr>
        @endif
        @if ($unik > 0)
            <tr>
                <td class="label">Kode unik pembayaran</td>
                <td class="isi">+ Rp {{ number_format($unik, 0, ',', '.') }}</td>
            </tr>
        @endif
    </table>
@endif

<table class="jumlah">
    <tr>
        <td class="jumlah-label">Jumlah diterima</td>
        <td class="jumlah-nilai">Rp {{ number_format($total, 0, ',', '.') }}</td>
    </tr>
</table>

<div class="terbilang">Terbilang: {{ $terbilang }}</div>

<table class="baris" style="margin-top:14px;">
    <tr>
        <td class="label">Tanggal pembayaran</td>
        <td class="isi">
            {{ $pendaftaran->bayar_pada
                ? $pendaftaran->bayar_pada->locale('id')->translatedFormat('j F Y, H:i') . ' WIB'
                : '-' }}
        </td>
    </tr>
    <tr>
        <td class="label">Cara pembayaran</td>
        {{-- Kanalnya kosong untuk yang dilunaskan panitia dari layar MIS —
             memang tidak lewat gerbang mana pun. Disebut apa adanya, bukan
             dikosongkan: yang membaca kwitansinya perlu tahu bedanya. --}}
        <td class="isi">{{ $pendaftaran->bayar_kanal ?: 'Dikonfirmasi panitia' }}</td>
    </tr>
</table>

<table class="kaki">
    <tr>
        <td class="kaki-kiri">
            <strong style="color:#475569;">Rumah Scopus Foundation</strong><br>
            Bangunsari, Bangun Kerto, Turi, Sleman, Daerah Istimewa Yogyakarta 55551<br>
            info@rumahscopusfoundation.com<br><br>
            Kwitansi ini diterbitkan otomatis dan sah tanpa tanda tangan basah.
        </td>
        <td class="kaki-kanan">
            <span class="cap">LUNAS</span>
        </td>
    </tr>
</table>

</body>
</html>
