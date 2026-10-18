{{--
  SATU cetakan untuk faktur DAN kwitansi.

  Keduanya dokumen yang sama dengan dua keadaan: faktur menyatakan berapa
  yang HARUS dibayar, kwitansi menyatakan bahwa jumlah itu SUDAH diterima.
  Ditulis sebagai dua berkas, keduanya berbeda perlahan — dan yang berbeda
  pada dokumen keuangan adalah angka.

  Tata letaknya meniru faktur DOKU: judul di kiri, logo di kanan, tiga
  kolom keterangan, lalu tabel rincian dengan Total / Sudah dibayar / Sisa
  tagihan. Pesertanya sudah menerima bentuk itu dari gerbangnya sendiri,
  jadi yang ini terbaca sebagai dokumen yang sama, bukan sebagai kertas
  kedua yang harus dicocokkan.

  Batasan Dompdf: tanpa flex dan grid, lebar kolom persen berjumlah TEPAT
  100 (Dompdf membagikan sisanya sendiri kalau tidak), logo lewat jalur
  berkas, dan Helvetica — bukan DejaVu — supaya fontnya tidak disisipkan
  utuh ke tiap berkas. Terukur: DejaVu 1.289.545 bita melawan 20.965.
--}}
@php
    $lunas = $jenis === 'kwitansi';

    $judul = $lunas ? 'KWITANSI' : 'FAKTUR';
    $nomor = str_replace('WE-', $lunas ? 'KW-' : 'INV-', (string) $pendaftaran->id_transaksi);

    $logo = public_path('assets/img/logo-rsc-email.png');

    $total = (int) $pendaftaran->total_pembayaran;
    $potongan = (int) ($pendaftaran->nominal_diskon ?? 0);
    $unik = (int) ($pendaftaran->kode_unik ?? 0);
    $jumlahOrang = max(1, (int) $pendaftaran->jumlah_pendaftar);

    $pokok = $total + $potongan - $unik;

    /*
     * Rinciannya DICOCOKKAN dengan yang dikirim ke DOKU (lihat
     * Doku::buatTagihan): judul sesi kali jumlah peserta, lalu potongan
     * sebagai baris negatif, lalu kode unik. Berbeda susunannya, peserta
     * memegang dua kertas yang menjumlahkan angka yang sama dengan cara
     * yang berbeda — dan yang mencocokkannya ke bendahara harus menjelaskan
     * selisih yang sebenarnya tidak ada.
     *
     * Harga satuannya hanya dipakai kalau pokoknya habis dibagi jumlah
     * orangnya. Kalau tidak, satu baris utuh lebih jujur daripada harga
     * satuan yang dibulatkan dan jumlahnya tidak cocok.
     */
    $perOrang = $jumlahOrang > 0 && $pokok % $jumlahOrang === 0;

    $baris = [[
        'nama' => $sesi?->nama ?: 'Webinar Eksklusif',
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

    $dibayar = $lunas ? $total : 0;
    $sisa = $total - $dibayar;

    $rp = fn (int $n) => ($n < 0 ? '-' : '') . number_format(abs($n), 0, ',', '.');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $judul }} {{ $nomor }}</title>
    <style>
        @page { margin: 30px 36px; }

        body {
            margin: 0;
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10.5px;
            color: #1e293b;
            line-height: 1.5;
        }

        table { width: 100%; border-collapse: collapse; }

        .kop td { vertical-align: top; padding: 0; }
        /* 58 + 42 = 100. Logonya dibesarkan 150 -> 215px, jadi jatah
           kolomnya ikut dilebarkan; dibiarkan 30%, logonya meluber keluar
           kolom dan Dompdf memotongnya tanpa satu pun galat. */
        .kop-kiri { width: 58%; }
        .kop-kanan { width: 42%; text-align: right; }

        .judul { margin: 0; font-size: 26px; font-weight: bold; color: #0f2b5b; letter-spacing: 1px; }
        .nomor { margin: 3px 0 0; font-size: 11px; color: #64748b; }

        .garis { border-top: 1px solid #e2e8f0; height: 1px; font-size: 0; line-height: 0; }

        /* Tiga kolom keterangan, dipisah garis tegak seperti faktur DOKU. */
        .ket { margin: 14px 0; }
        .ket td { vertical-align: top; padding: 12px 14px; }
        .ket-1 { width: 34%; padding-left: 0 !important; }
        .ket-2 { width: 33%; border-left: 1px solid #e2e8f0; }
        .ket-3 { width: 33%; border-left: 1px solid #e2e8f0; }

        .ket-label { margin: 0; font-weight: bold; color: #0f2b5b; }
        .ket-isi { margin: 0 0 10px; color: #475569; }
        .ket-isi:last-child { margin-bottom: 0; }
        .ket-kecil { color: #94a3b8; }

        .sapa { margin: 4px 0 10px; }

        .keadaan { margin: 0 0 10px; font-size: 14px; font-weight: bold; }
        .keadaan-belum { color: #b45309; }
        .keadaan-lunas { color: #15803d; }

        /* 52 + 10 + 19 + 19 = 100. Jumlahnya HARUS tepat; kurang atau lebih,
           Dompdf membagikan sisanya menurut aturan yang tidak terlihat. */
        .rinci { margin-top: 12px; }
        .rinci th {
            padding: 9px 10px;
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
            color: #475569;
            text-align: left;
        }
        .rinci td { padding: 10px; border-bottom: 1px solid #eef2f7; vertical-align: top; }
        .k-ket { width: 52%; }
        .k-qty { width: 10%; text-align: center; }
        .k-harga { width: 19%; text-align: right; }
        .k-jumlah { width: 19%; text-align: right; }

        .ringkas { margin-top: 2px; }
        .ringkas td { padding: 7px 10px; }
        .ringkas-kosong { width: 62%; }
        .ringkas-label { width: 19%; text-align: right; font-weight: bold; color: #0f2b5b; }
        .ringkas-nilai { width: 19%; text-align: right; font-weight: bold; color: #0f2b5b; }

        .sisa td { border-top: 2px solid #b45309; color: #b45309; font-size: 13px; }
        .sisa-lunas td { border-top: 2px solid #15803d; color: #15803d; }

        /* 62 + 38 = 100. */
        .kaki-blok { margin-top: 30px; }
        .kaki-blok td { vertical-align: bottom; padding: 0; }
        .kaki-kiri { width: 62%; }
        .kaki-kanan { width: 38%; text-align: right; }

        .kaki { margin: 0; color: #94a3b8; font-size: 9.5px; line-height: 1.6; }

        /* Bingkai luar capnya; bingkai dalamnya digambar oleh <td>-nya. */
        .cap {
            width: 170px;
            margin-left: auto;
            border: 2px solid #b45309;
            border-radius: 10px;
            border-collapse: separate;
        }

        .cap td {
            padding: 11px 10px 9px;
            border: 1px solid #b45309;
            border-radius: 6px;
            text-align: center;
        }

        .cap-logo { display: block; margin: 0 auto 6px; }

        .cap-kata {
            margin: 0;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1.5px;
            color: #b45309;
        }

        .cap-tanggal { margin: 2px 0 0; font-size: 8.5px; color: #b45309; }

        .cap-lunas { border-color: #15803d; }
        .cap-lunas td { border-color: #15803d; }
        .cap-lunas .cap-kata,
        .cap-lunas .cap-tanggal { color: #15803d; }
    </style>
</head>
<body>

<table class="kop">
    <tr>
        <td class="kop-kiri">
            <p class="judul">{{ $judul }}</p>
            <p class="nomor">No. {{ $nomor }}</p>
        </td>
        <td class="kop-kanan">
            <img src="{{ $logo }}" alt="Rumah Scopus Foundation" width="215">
        </td>
    </tr>
</table>

<div class="garis" style="margin-top:14px;"></div>

<table class="ket">
    <tr>
        <td class="ket-1">
            <p class="ket-label">{{ $lunas ? 'Tanggal pembayaran' : 'Tanggal faktur' }}</p>
            <p class="ket-isi">
                @if ($lunas)
                    {{ $pendaftaran->bayar_pada
                        ? $pendaftaran->bayar_pada->locale('id')->translatedFormat('j F Y, H:i') . ' WIB'
                        : '-' }}
                @else
                    {{ $pendaftaran->created_at?->locale('id')->translatedFormat('j F Y') ?: '-' }}
                @endif
            </p>

            @unless ($lunas)
                {{-- Batasnya lengkap SAMPAI MENIT: dokumen ini sebagian
                     dicetak lalu dibaca lagi berjam-jam kemudian, dan "hari
                     ini" tidak berarti apa-apa di kertas. --}}
                <p class="ket-label">Batas pembayaran</p>
                <p class="ket-isi">
                    {{ $pendaftaran->kedaluwarsa_pada
                        ? $pendaftaran->kedaluwarsa_pada->locale('id')->translatedFormat('j F Y, H:i') . ' WIB'
                        : '-' }}
                </p>
            @else
                <p class="ket-label">Cara pembayaran</p>
                <p class="ket-isi">{{ $pendaftaran->bayar_kanal ?: 'Dikonfirmasi panitia' }}</p>
            @endunless
        </td>

        <td class="ket-2">
            <p class="ket-label">{{ $lunas ? 'Diterima dari' : 'Ditagihkan kepada' }}</p>
            <p class="ket-isi">
                <strong>{{ $pendaftaran->nama }}</strong><br>
                <span class="ket-kecil">{{ $pendaftaran->email }}</span>
                @if ($pendaftaran->telp)
                    <br><span class="ket-kecil">{{ $pendaftaran->telp }}</span>
                @endif
            </p>
        </td>

        <td class="ket-3">
            <p class="ket-label">Dari</p>
            <p class="ket-isi">
                <strong>Rumah Scopus Foundation</strong><br>
                <span class="ket-kecil">Sleman, Daerah Istimewa Yogyakarta</span><br>
                <span class="ket-kecil">info@rumahscopusfoundation.com</span>
            </p>
        </td>
    </tr>
</table>

<div class="garis"></div>

<p class="sapa" style="margin-top:14px;">Halo {{ $pendaftaran->nama }},</p>

<p class="keadaan {{ $lunas ? 'keadaan-lunas' : 'keadaan-belum' }}">
    {{ $lunas ? 'Pembayaran Diterima' : 'Belum Dibayar' }}
</p>

<p style="margin:0;color:#475569;">
    {{ $lunas
        ? 'Terima kasih, pembayaran Anda sudah kami terima. Berikut rincian transaksinya:'
        : 'Terima kasih sudah mendaftar. Berikut rincian tagihan Anda:' }}
</p>

<table class="rinci">
    <tr>
        <th class="k-ket">Keterangan</th>
        <th class="k-qty" style="text-align:center;">Qty</th>
        <th class="k-harga" style="text-align:right;">Harga (Rp)</th>
        <th class="k-jumlah" style="text-align:right;">Jumlah (Rp)</th>
    </tr>
    @foreach ($baris as $b)
        <tr>
            <td class="k-ket">{{ $b['nama'] }}</td>
            <td class="k-qty">{{ $b['qty'] }}</td>
            <td class="k-harga">{{ $rp($b['harga']) }}</td>
            <td class="k-jumlah">{{ $rp($b['harga'] * $b['qty']) }}</td>
        </tr>
    @endforeach
</table>

<table class="ringkas">
    <tr>
        <td class="ringkas-kosong"></td>
        <td class="ringkas-label">Total</td>
        <td class="ringkas-nilai">{{ $rp($total) }}</td>
    </tr>
    <tr>
        <td class="ringkas-kosong"></td>
        <td class="ringkas-label">Sudah dibayar</td>
        <td class="ringkas-nilai">{{ $rp($dibayar) }}</td>
    </tr>
    <tr class="sisa {{ $lunas ? 'sisa-lunas' : '' }}">
        <td class="ringkas-kosong" style="border-top:0;"></td>
        {{-- Namanya SAMA di kedua dokumen: barisnya memang berarti hal
             yang sama, yaitu berapa yang masih harus dibayar. Diberi nama
             "Lunas" di kwitansi, angkanya jadi nol tanpa keterangan — dan
             nol apa tidak terjawab oleh barisnya sendiri. Yang membedakan
             warnanya. --}}
        <td class="ringkas-label" style="color:inherit;">Sisa tagihan</td>
        <td class="ringkas-nilai" style="color:inherit;">{{ $rp($sisa) }}</td>
    </tr>
</table>

<p style="margin:14px 0 0;font-style:italic;color:#64748b;">Terbilang: {{ $terbilang }}</p>

{{-- Kaki dua kolom: keterangan di kiri, CAP di kanan.

     Langkah "cara membayar" dibuang dari sini. Dokumen ini dibaca bersama
     emailnya, dan emailnya sudah memuat tombol menuju halaman bayar —
     langkah yang sama ditulis dua kali membuat yang satu tertinggal saat
     yang lain diperbarui, dan yang tertinggal pada dokumen keuangan selalu
     yang dicetak. --}}
<table class="kaki-blok">
    <tr>
        <td class="kaki-kiri">
            <p class="kaki">
                Nomor pendaftaran: {{ $pendaftaran->id_transaksi }}<br>
                {{ $lunas ? 'Kwitansi' : 'Faktur' }} ini diterbitkan otomatis oleh sistem
                Rumah Scopus Foundation dan sah tanpa tanda tangan basah.
            </p>
        </td>

        <td class="kaki-kanan">
            {{-- CAP.

                 Bingkai ganda, bukan satu garis: satu garis terbaca sebagai
                 kotak biasa, dua garis terbaca sebagai stempel. Warnanya
                 mengikuti keadaan dokumennya, jadi capnya tidak pernah
                 menyatakan hal yang berbeda dari angkanya.

                 TIDAK diputar miring. Dompdf tidak mengenal transform, dan
                 yang memakainya tergambar lurus tanpa satu pun galat — jadi
                 kemiringannya hanya akan ada di kepala penulisnya. --}}
            <table class="cap {{ $lunas ? 'cap-lunas' : 'cap-belum' }}">
                <tr>
                    <td>
                        <img src="{{ $logo }}" alt="" width="118" class="cap-logo">
                        <p class="cap-kata">{{ $lunas ? 'LUNAS' : 'BELUM DIBAYAR' }}</p>
                        <p class="cap-tanggal">
                            {{ ($lunas ? $pendaftaran->bayar_pada : $pendaftaran->created_at)
                                ?->locale('id')->translatedFormat('j F Y') ?: '' }}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
