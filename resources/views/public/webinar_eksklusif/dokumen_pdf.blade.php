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

    /*
     * Nomor panitianya dibaca dari config, BUKAN diketik ulang di sini.
     *
     * Ia berubah saat orangnya berganti, dan nomor yang diketik ulang di
     * tiap tempat akan tertinggal di sebagian tempat — pada dokumen yang
     * sudah dicetak dan dipegang orang, tertinggalnya permanen.
     *
     * Disimpan format internasional tanpa plus (6288983567819) karena
     * wa.me memintanya begitu; di sini dikembalikan ke bentuk yang dibaca
     * orang Indonesia.
     */
    $wa = preg_replace('/^62/', '0', (string) config('panitia.whatsapp'));
    $waTampil = trim(chunk_split($wa, 4, '-'), '-');
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

        .kop td { vertical-align: middle; padding: 0; }
        /* 46 + 54 = 100. Logonya 215px, jadi jatah kolomnya harus cukup;
           terlalu sempit, logonya meluber dan Dompdf memotongnya tanpa satu
           pun galat. */
        .kop-kiri { width: 46%; }
        .kop-kanan { width: 54%; text-align: right; }

        .judul { margin: 0; font-size: 30px; font-weight: bold; color: #0f2b5b; letter-spacing: 2px; }
        .nomor { margin: 4px 0 0; font-size: 11px; color: #64748b; }

        .garis-kop {
            margin-top: 12px;
            border-top: 3px solid #b45309;
            height: 1px; font-size: 0; line-height: 0;
        }
        .garis-lunas { border-top-color: #15803d; }

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

        /* 52 + 48 = 100. */
        .bawah-rinci { margin-top: 16px; }
        .bawah-rinci > tr > td { vertical-align: top; padding: 0; }
        .bawah-kiri { width: 52%; padding-right: 18px !important; }
        .bawah-kanan { width: 48%; }

        .terbilang-judul {
            margin: 0 0 3px;
            font-size: 9px; font-weight: bold; letter-spacing: 1px; color: #94a3b8;
        }
        .terbilang-isi { margin: 0; font-style: italic; color: #475569; }

        /*
         * border-spacing: 0 WAJIB di sini.
         *
         * border-collapse: separate dipakai supaya border-radius kotaknya
         * tergambar — pada collapse, Dompdf mengabaikan radiusnya. Tetapi
         * separate juga memberi JARAK antar sel, dan garis oranye di atas
         * baris "Sisa tagihan" ikut terputus di jarak itu: terlihat di
         * potret sebagai dua potong garis dengan celah di tengahnya.
         */
        .ringkas {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            border-collapse: separate;
            border-spacing: 0;
        }
        .ringkas td { padding: 8px 14px; border: 0; }
        .ringkas-label { width: 52%; text-align: left; color: #475569; }
        .ringkas-nilai { width: 48%; text-align: right; font-weight: bold; color: #0f2b5b; }

        .ringkas-garis {
            padding: 0 14px !important;
            height: 2px; font-size: 0; line-height: 0;
        }
        .ringkas-garis:after {
            content: '';
            display: block;
            border-top: 2px solid #b45309;
        }

        .sisa td {
            padding-top: 9px !important;
            color: #b45309;
            font-size: 14px;
            font-weight: bold;
        }

        .ringkas-lunas .ringkas-garis:after { border-top-color: #15803d; }
        .ringkas-lunas .sisa td { color: #15803d; }

        /* 62 + 38 = 100. */
        .kaki-blok { margin-top: 30px; }
        .kaki-blok td { vertical-align: bottom; padding: 0; }
        .kaki-kiri { width: 62%; }
        .kaki-kanan { width: 38%; text-align: right; }

        .kaki { margin: 0; color: #94a3b8; font-size: 9.5px; line-height: 1.6; }

        /* 46 + 54 = 100. */
        .pita {
            margin-top: 26px;
            background: #f8fafc;
            border-top: 2px solid #0f2b5b;
            border-collapse: separate;
        }
        .pita td { padding: 12px 14px; vertical-align: top; border: 0; }
        .pita-kiri { width: 46%; }
        .pita-kanan { width: 54%; text-align: right; }

        .pita-nama {
            margin: 0 0 3px;
            font-size: 10px; font-weight: bold; letter-spacing: .8px; color: #0f2b5b;
        }
        .pita-isi { margin: 0; font-size: 9.5px; line-height: 1.65; color: #64748b; }
        .pita-isi strong { color: #0f2b5b; }
        .pita-sosial { margin: 5px 0 0; font-size: 9.5px; color: #64748b; }
        .pita-sosial strong { color: #0f2b5b; }

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
            <img src="{{ $logo }}" alt="Rumah Scopus Foundation" width="215">
        </td>
        <td class="kop-kanan">
            <p class="judul">{{ $judul }}</p>
            <p class="nomor">No. {{ $nomor }}</p>
        </td>
    </tr>
</table>

{{-- Garis tebal berwarna keadaan, bukan garis abu tipis: ia menutup kopnya
     sekaligus menyebut keadaan dokumennya sebelum satu angka pun dibaca. --}}
<div class="garis-kop {{ $lunas ? 'garis-lunas' : '' }}"></div>

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

{{-- Terbilang di KIRI, ringkasan angka di KANAN, sejajar.

     Ditumpuk, halamannya jadi satu kolom panjang yang seluruhnya rata kiri
     dan ruang di sebelah kanan tabel terbuang. Bersebelahan, keduanya
     mengisi lebar yang sama dan mata langsung menemukan angka akhirnya. --}}
<table class="bawah-rinci">
    <tr>
        <td class="bawah-kiri">
            <p class="terbilang-judul">TERBILANG</p>
            <p class="terbilang-isi">{{ $terbilang }}</p>
        </td>

        <td class="bawah-kanan">
            <table class="ringkas {{ $lunas ? 'ringkas-lunas' : '' }}">
                <tr>
                    <td class="ringkas-label">Total</td>
                    <td class="ringkas-nilai">{{ $rp($total) }}</td>
                </tr>
                <tr>
                    <td class="ringkas-label">Sudah dibayar</td>
                    <td class="ringkas-nilai">{{ $rp($dibayar) }}</td>
                </tr>
                {{-- Namanya SAMA di kedua dokumen: barisnya memang berarti
                     hal yang sama, yaitu berapa yang masih harus dibayar.
                     Diberi nama "Lunas" di kwitansi, angkanya jadi nol tanpa
                     keterangan — dan nol apa tidak terjawab oleh barisnya
                     sendiri. Yang membedakan warnanya. --}}
                {{-- Garisnya BARIS TERSENDIRI selebar penuh, bukan
                     border-top pada kedua selnya.

                     Dompdf menggambar border tiap sel sebagai kotak
                     terpisah; dua sel bersebelahan menghasilkan dua potong
                     garis dengan celah di sambungannya — terlihat di potret
                     sebagai garis yang putus di tengah. border-spacing: 0
                     TIDAK menutupnya; dicoba, celahnya tetap ada.

                     Satu sel ber-colspan tidak punya sambungan. --}}
                <tr>
                    <td colspan="2" class="ringkas-garis"></td>
                </tr>
                <tr class="sisa">
                    <td class="ringkas-label">Sisa tagihan</td>
                    <td class="ringkas-nilai">{{ $rp($sisa) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

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

{{-- PITA KAKI.

     Dokumen ini berpindah tangan: disimpan, dicetak, diteruskan ke
     bendahara kantor, kadang dibaca berbulan-bulan kemudian oleh orang
     yang tidak pernah membuka emailnya. Tanpa pita ini, satu-satunya cara
     menghubungi penerbitnya adalah mencari emailnya kembali.

     Ikonnya TEKS, bukan gambar. Tiga berkas ikon yang ada di aplikasi ini
     berukuran 14-25 KB; empat di antaranya akan melipatgandakan berkas yang
     sekarang 20 KB. Lagi pula teks di dalam PDF bisa disalin, sementara
     ikon tidak — dan yang hendak menghubungi perlu menyalin, bukan
     memandang. TikTok pun memang tidak punya ikonnya di sini. --}}
<table class="pita">
    <tr>
        <td class="pita-kiri">
            <p class="pita-nama">RUMAH SCOPUS FOUNDATION</p>
            <p class="pita-isi">
                Bangunsari, Bangun Kerto, Turi, Sleman<br>
                Daerah Istimewa Yogyakarta 55551
            </p>
        </td>

        <td class="pita-kanan">
            <p class="pita-isi">
                <strong>rumahscopusfoundation.com</strong><br>
                info@rumahscopusfoundation.com<br>
                WhatsApp {{ $waTampil }}
            </p>
            <p class="pita-sosial">
                Instagram &amp; TikTok <strong>@rumah_scopus</strong>
                &nbsp;&middot;&nbsp; YouTube <strong>@rumahscopus</strong>
            </p>
        </td>
    </tr>
</table>

</body>
</html>
