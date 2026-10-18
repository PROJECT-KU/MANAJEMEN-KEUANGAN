{{--
  FAKTUR — tagihan yang BELUM dibayar.

  Sepasang dengan kwitansi_pdf, dan bedanya bukan hanya kata: kwitansi
  menyatakan uangnya SUDAH diterima, faktur menyatakan berapa yang HARUS
  dibayar dan sampai kapan. Tertukar, yang satu jadi bukti palsu dan yang
  lain jadi tagihan untuk uang yang sudah masuk.

  Batasan Dompdf-nya sama persis seperti kwitansi: tanpa flex dan grid,
  lebar kolom persen berjumlah tepat 100, logo lewat jalur berkas, dan
  Helvetica — bukan DejaVu — supaya fontnya tidak disisipkan utuh ke tiap
  berkas. Alasan lengkap tiap butirnya ada di kwitansi_pdf.
--}}
@php
    $logo = public_path('assets/img/logo-rsc-email.png');

    $nomor = str_replace('WE-', 'INV-', (string) $pendaftaran->id_transaksi);

    $total = (int) $pendaftaran->total_pembayaran;
    $potongan = (int) ($pendaftaran->nominal_diskon ?? 0);
    $unik = (int) ($pendaftaran->kode_unik ?? 0);
    $pokok = $total + $potongan - $unik;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Faktur {{ $nomor }}</title>
    <style>
        @page { margin: 28px 34px; }

        body {
            margin: 0;
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

        /* Kotak tagihannya KUNING, bukan abu-abu seperti kwitansi: yang ini
           menuntut tindakan, yang itu menutup urusan. */
        .jumlah {
            margin: 16px 0 0;
            padding: 12px 14px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 6px;
        }
        .jumlah td { padding: 0; }
        .jumlah-label { width: 40%; color: #92400e; }
        .jumlah-nilai { width: 60%; text-align: right; font-size: 19px; font-weight: bold; color: #92400e; }

        .terbilang {
            margin: 8px 0 0;
            padding: 9px 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-style: italic;
            color: #475569;
        }

        .cap {
            display: inline-block;
            padding: 7px 16px;
            border: 2px solid #b45309;
            border-radius: 6px;
            color: #b45309;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .langkah { margin: 16px 0 0; }
        .langkah-judul {
            margin: 0 0 6px;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #64748b;
        }
        .langkah td { padding: 3px 0; vertical-align: top; }
        .langkah-no { width: 4%; font-weight: bold; color: #0f2b5b; }
        .langkah-isi { width: 96%; color: #334155; }

        .kaki { margin-top: 20px; color: #64748b; font-size: 10px; line-height: 1.6; }
        .kaki-kiri { width: 62%; }
        .kaki-kanan { width: 38%; text-align: center; }
        .kaki td { vertical-align: top; padding: 0; }
    </style>
</head>
<body>

<table class="kop">
    <tr>
        <td class="kop-logo"><img src="{{ $logo }}" alt="Rumah Scopus Foundation" width="180"></td>
        <td class="kop-kanan">
            <p class="judul">FAKTUR</p>
            <p class="nomor">No. {{ $nomor }}</p>
        </td>
    </tr>
</table>

<div class="garis"></div>

<table class="baris">
    <tr>
        <td class="label">Ditagihkan kepada</td>
        <td class="isi">{{ $pendaftaran->nama }}</td>
    </tr>
    <tr>
        <td class="label">Untuk</td>
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

{{-- Rinciannya digambar hanya kalau ada yang perlu dirinci; alasannya
     sama persis seperti di kwitansi. --}}
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
            {{-- Kode uniknya DISEBUT di faktur, tidak hanya di kwitansi:
                 yang membayar lewat transfer harus mengirim angka yang
                 PERSIS, dan selisih tiga digit membuat pembayarannya tidak
                 cocok dengan tagihan mana pun. --}}
            <tr>
                <td class="label">Kode unik pembayaran</td>
                <td class="isi">+ Rp {{ number_format($unik, 0, ',', '.') }}</td>
            </tr>
        @endif
    </table>
@endif

<table class="jumlah">
    <tr>
        <td class="jumlah-label">Jumlah yang harus dibayar</td>
        <td class="jumlah-nilai">Rp {{ number_format($total, 0, ',', '.') }}</td>
    </tr>
</table>

<div class="terbilang">Terbilang: {{ $terbilang }}</div>

<table class="baris" style="margin-top:14px;">
    <tr>
        <td class="label">Batas pembayaran</td>
        {{-- Batasnya dicetak TEBAL dan lengkap sampai menit: faktur ini
             sebagian dicetak lalu dibaca lagi berjam-jam kemudian, dan
             "hari ini" tidak berarti apa-apa di kertas. --}}
        <td class="isi">
            {{ $pendaftaran->kedaluwarsa_pada
                ? $pendaftaran->kedaluwarsa_pada->locale('id')->translatedFormat('j F Y, H:i') . ' WIB'
                : '-' }}
        </td>
    </tr>
    <tr>
        <td class="label">Status</td>
        <td class="isi">Belum dibayar</td>
    </tr>
</table>

<div class="langkah">
    <p class="langkah-judul">CARA MEMBAYAR</p>
    <table>
        <tr>
            <td class="langkah-no">1.</td>
            <td class="langkah-isi">Buka halaman status pendaftaran Anda lewat tautan di email ini.</td>
        </tr>
        <tr>
            <td class="langkah-no">2.</td>
            <td class="langkah-isi">Tekan <b>Lanjutkan pembayaran</b>, lalu pilih caranya &mdash; transfer bank
                (virtual account), QRIS, e-wallet, atau kartu.</td>
        </tr>
        <tr>
            <td class="langkah-no">3.</td>
            <td class="langkah-isi">Begitu pembayarannya masuk, status Anda berubah sendiri dan kwitansinya
                kami kirim lewat email. Tidak perlu mengirim bukti apa pun.</td>
        </tr>
    </table>
</div>

<table class="kaki">
    <tr>
        <td class="kaki-kiri">
            <strong style="color:#475569;">Rumah Scopus Foundation</strong><br>
            Bangunsari, Bangun Kerto, Turi, Sleman, Daerah Istimewa Yogyakarta 55551<br>
            info@rumahscopusfoundation.com<br><br>
            Faktur ini diterbitkan otomatis dan sah tanpa tanda tangan basah.
        </td>
        <td class="kaki-kanan">
            <span class="cap">BELUM DIBAYAR</span>
        </td>
    </tr>
</table>

</body>
</html>
