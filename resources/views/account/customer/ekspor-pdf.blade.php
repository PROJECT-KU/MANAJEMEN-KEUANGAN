@php
    /*
     * Logo disisipkan sebagai data URI, bukan ditautkan: Dompdf hanya mau
     * mengambil berkas dari luar kalau isRemoteEnabled dinyalakan, dan alamat
     * yang dirangkai asset() menunjuk APP_URL.
     */
    $berkasLogo = public_path('assets/img/logo-email.png');
    $logo = is_file($berkasLogo)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($berkasLogo))
        : null;

    $aktif = $pelanggan->where('status', 'active')->count();
    $terverifikasi = $pelanggan->filter(fn ($p) => $p->email_verified_at)->count();
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Data Pelanggan — MIS Rumah Scopus</title>
    <style>
        /* Dompdf hanya mengenal sebagian kecil CSS: tidak ada flexbox, tidak
           ada grid, tidak ada custom property. Tata letaknya memakai tabel dan
           lebar persen. Satuan pt, bukan px: dompdf memampatkan px dengan 0,75
           sehingga angka px meleset seperempat. */
        @page {
            margin: 96px 32px 64px;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #334155;
        }

        /* position: fixed berarti kepala dan kaki terulang di TIAP halaman. */
        .kepala {
            position: fixed;
            top: -76px;
            left: 0;
            width: 730pt;
            height: 58px;
        }

        .kepala td {
            vertical-align: middle;
        }

        .judul {
            margin: 0;
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
        }

        .anak-judul {
            margin: 3px 0 0;
            font-size: 8.5px;
            color: #64748b;
        }

        /* Baris kanan kepala: label kecil di atas nilainya, bukan dua baris
           sejajar. Dua baris sejajar membuat "MIS Rumah Scopus Foundation"
           dan waktu unduhnya terbaca sebagai satu kalimat. */
        .meta-label {
            font-size: 6.5px;
            letter-spacing: 1px;
            /* #a5b4fc nyaris tak terbaca di atas putih saat dicetak; #818cf8
               tetap beraksen tanpa memudar. */
            color: #818cf8;
        }

        .meta-nilai { font-size: 8.5px; color: #475569; font-weight: bold; }

        /*
         * Dua garis beda tebal, bukan satu.
         *
         * Satu garis penuh terbaca seperti pembatas seadanya; potongan tebal
         * berwarna merek di ujung kiri membuat kepalanya terbaca sebagai kop.
         * Keduanya fixed supaya ikut terulang di tiap halaman.
         *
         * 794pt, bukan 730pt: A4 mendatar 842pt dikurangi bantalan 32px
         * (24pt) di kedua sisi. Dengan 730pt garisnya berhenti 64pt sebelum
         * tepi kanan isi, dan kepalanya terlihat seperti kurang panjang.
         */
        .garis-kepala {
            position: fixed;
            top: -14px;
            left: 0;
            width: 794pt;
            border-top: 1px solid #e2e8f0;
        }

        .garis-kepala-tebal {
            position: fixed;
            top: -16px;
            left: 0;
            width: 84pt;
            border-top: 3px solid #6366f1;
        }

        .kaki {
            position: fixed;
            bottom: -44px;
            left: 0;
            width: 794pt;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
            color: #94a3b8;
        }

        /*
         * Ringkasan jadi kartu TERPISAH, bukan satu baris sel bersambung.
         *
         * Bersambung, keempat angka berbeda terbaca seperti satu baris tabel
         * dan tidak ada yang menonjol. Jaraknya dibuat dengan sel pemisah
         * berlebar tetap: Dompdf tidak mengenal gap maupun border-spacing
         * yang hanya mendatar.
         */
        .keterangan-berkas {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 13px;
        }

        .keterangan-berkas td.kartu {
            padding: 6px 9px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #cbd5e1;
            font-size: 8px;
        }

        /*
         * Warnanya membawa arti, bukan hiasan: nila untuk jumlah yang
         * tercetak, hijau untuk akun yang hidup.
         *
         * Pemilihnya SELENGKAP pemilih dasarnya. Ditulis ".kartu-pokok" saja,
         * bobotnya (0,1,0) kalah dari ".keterangan-berkas td.kartu" (0,2,1) di
         * atas — dan yang tercetak keempat kartu abu-abu seragam tanpa galat
         * apa pun.
         */
        .keterangan-berkas td.kartu-pokok { border-left-color: #6366f1; background: #eef2ff; }
        .keterangan-berkas td.kartu-hidup { border-left-color: #10b981; background: #ecfdf5; }

        .keterangan-berkas td.sela { padding: 0; border: 0; width: 7px; }

        .label {
            color: #94a3b8;
            font-size: 6.5px;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .nilai {
            color: #0f172a;
            font-weight: bold;
            font-size: 11px;
        }

        /* Saringan tetap berukuran teks: isinya kalimat, bukan angka, dan
           pada 11px ia memakan dua baris lalu menaikkan tinggi seluruh pita. */
        .nilai-teks { font-size: 8px; font-weight: normal; color: #334155; }

        table.daftar {
            width: 100%;
            border-collapse: collapse;
        }

        table.daftar thead th {
            padding: 6px 7px;
            background: #6366f1;
            color: #ffffff;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: .04em;
            text-align: left;
            border: 1px solid #6366f1;
        }

        table.daftar tbody td {
            padding: 5px 7px;
            border: 1px solid #e8edf3;
            vertical-align: middle;
        }

        table.daftar tbody tr.selang td {
            background: #fafbfd;
        }

        .tebal {
            font-weight: bold;
            color: #0f172a;
        }

        .samar {
            color: #64748b;
        }

        .nowrap {
            white-space: nowrap;
        }

        /* Lencana: span berlatar, sebab dompdf tidak mengenal border-radius
           pada elemen sebaris yang diberi padding besar. */
        .lencana {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 7.5px;
            font-weight: bold;
            white-space: nowrap;
        }

        .l-hijau { background: #d1fae5; color: #047857; }
        .l-abu   { background: #eef2f7; color: #475569; }
        .l-biru  { background: #dbeafe; color: #1d4ed8; }
        .l-kuning{ background: #fef3c7; color: #92400e; }

        .kosong {
            padding: 28px 10px;
            text-align: center;
            color: #94a3b8;
            border: 1px solid #e8edf3;
        }
    </style>
</head>

<body>
    <div class="kepala">
        <table width="100%">
            <tr>
                {{-- Kedua lajur tepi SAMA LEBAR, dan judulnya terpusat di
                     lajur tengah.

                     Dengan lajur logo 150px dan lajur kanan tanpa lebar
                     tetap, judulnya terpusat pada SISA lebar — bukan pada
                     lebar halaman — sehingga titik tengahnya jatuh sekitar
                     sepertiga lebar kertas. 190px cukup memuat "MIS Rumah
                     Scopus Foundation" pada 8,5px tebal tanpa terpatah. --}}
                @if ($logo)
                    <td width="190"><img src="{{ $logo }}" alt="MIS Rumah Scopus" height="38"></td>
                @endif
                <td align="center">
                    <p class="judul">Data Pelanggan</p>
                    <p class="anak-judul">Orang luar yang memakai layanan jasa Rumah Scopus.</p>
                </td>
                <td width="190" align="right">
                    <span class="meta-label">DITERBITKAN OLEH</span><br>
                    <span class="meta-nilai">MIS Rumah Scopus Foundation</span><br>
                    <span class="anak-judul">Diunduh {{ now()->format('d/m/Y H:i') }} WIB</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="garis-kepala"></div>
    <div class="garis-kepala-tebal"></div>

    <div class="kaki">
        <table width="100%">
            <tr>
                <td width="70%">
                    Jumlah pesanan dihitung dari lima layanan (Clinik Scopus, Analisis
                    Bibliometrik, Scopus Kafe, Scopus Camp, Webinar Eksklusif), dicocokkan
                    lewat rujukan akun bila ada, lalu nomor telepon, lalu email, lalu nama.
                    Online Training tidak tercatat di sistem ini.
                </td>
                <td width="30%"></td>
            </tr>
        </table>
    </div>

    <table class="keterangan-berkas">
        <tr valign="top">
            <td width="16%" class="kartu kartu-pokok">
                <span class="label">Pelanggan tercetak</span><br>
                <span class="nilai">{{ number_format($pelanggan->count(), 0, ',', '.') }}</span>
            </td>
            <td class="sela"></td>
            <td width="14%" class="kartu kartu-hidup">
                <span class="label">Akun aktif</span><br>
                <span class="nilai">{{ number_format($aktif, 0, ',', '.') }}</span>
            </td>
            <td class="sela"></td>
            <td width="16%" class="kartu">
                <span class="label">Email terverifikasi</span><br>
                <span class="nilai">{{ number_format($terverifikasi, 0, ',', '.') }}</span>
            </td>
            <td class="sela"></td>
            <td class="kartu">
                <span class="label">Saringan</span><br>
                <span class="nilai nilai-teks">
                    {{-- Saringan yang sedang dipakai ikut dicetak: tanpa itu,
                         berkas berisi 12 baris tidak bisa dibedakan dari daftar
                         yang memang cuma punya 12 pelanggan. --}}
                    @if (empty($saringan))
                        Tanpa saringan
                    @else
                        @foreach ($saringan as $nama => $nilai)
                            {{ $nama }}: {{ $nilai }}@if (! $loop->last) &middot; @endif
                        @endforeach
                    @endif
                </span>
            </td>
        </tr>
    </table>

    @if ($pelanggan->isEmpty())
        <p class="kosong">Tidak ada pelanggan yang cocok dengan saringan ini.</p>
    @else
        <table class="daftar">
            <thead>
                <tr>
                    <th width="4%">No.</th>
                    <th width="20%">Nama</th>
                    <th width="14%">Username</th>
                    <th width="24%">Email</th>
                    <th width="12%">Telepon</th>
                    <th width="10%">Status</th>
                    <th width="8%">Pesanan</th>
                    <th width="8%">Bergabung</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pelanggan as $i => $orang)
                    <tr class="{{ $i % 2 ? 'selang' : '' }}">
                        <td class="samar">{{ $i + 1 }}</td>
                        <td class="tebal">{{ $orang->full_name ?: $orang->username }}</td>
                        <td class="samar">{{ $orang->username }}</td>
                        <td>{{ $orang->email }}</td>
                        <td class="samar nowrap">{{ $orang->telp ?: '—' }}</td>
                        <td class="nowrap">
                            @if ($orang->status === 'active')
                                <span class="lencana l-hijau">Aktif</span>
                            @else
                                <span class="lencana l-abu">Nonaktif</span>
                            @endif
                            @if (! $orang->email_verified_at)
                                <span class="lencana l-kuning">Belum verifikasi</span>
                            @endif
                        </td>
                        <td>
                            @php ($n = $pesanan[$orang->id]['jumlah'] ?? 0)
                            @if ($n > 0)
                                <span class="lencana l-biru">{{ $n }}&times;</span>
                            @else
                                <span class="samar">—</span>
                            @endif
                        </td>
                        <td class="samar nowrap">
                            {{ optional($orang->created_at)->format('d/m/Y') ?: '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Nomor halaman digambar Dompdf sendiri; Blade tidak bisa menghitungnya
         karena pemenggalan halamannya baru diketahui saat render. --}}
    <script type="text/php">
        if (isset($pdf)) {
            $teks = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            $huruf = $fontMetrics->getFont("DejaVu Sans");

            /*
             * Penandanya diganti angka contoh sebelum diukur: {PAGE_NUM} baru
             * disulih SESUDAH ini, jadi mengukur $teks apa adanya berarti
             * mengukur panjang penandanya (36 huruf) dan tulisannya terlempar
             * jauh ke kiri.
             */
            $jumlah = (string) (method_exists($pdf, "get_page_count") ? $pdf->get_page_count() : 1);
            $contoh = str_replace(
                ["{PAGE_NUM}", "{PAGE_COUNT}"],
                [str_repeat("0", strlen($jumlah)), $jumlah],
                $teks
            );
            $lebar = $fontMetrics->getTextWidth($contoh, $huruf, 6);

            $pdf->page_text(
                $pdf->get_width() - 24 - $lebar, $pdf->get_height() - 26,
                $teks, $huruf, 6, [0.58, 0.64, 0.72]
            );
        }
    </script>
</body>

</html>
