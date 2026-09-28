@php
    /*
     * Logo disisipkan sebagai data URI, bukan ditautkan.
     *
     * Dompdf hanya mau mengambil berkas dari luar kalau isRemoteEnabled
     * dinyalakan, dan alamat yang dirangkai asset() menunjuk APP_URL yang di
     * sini masih http://localhost. Logo PDF gaji yang sudah ada memakai $src
     * yang tidak pernah didefinisikan di mana pun — persis kegagalan yang
     * dihindari di sini.
     */
    $berkasLogo = public_path('assets/img/logo-email.png');
    $logo = is_file($berkasLogo)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($berkasLogo))
        : null;

    $gagal = $baris->where('berhasil', false)->count();
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Riwayat Keamanan — {{ $pengguna->username }}</title>
    <style>
        /* Dompdf hanya mengenal sebagian kecil CSS: tidak ada flexbox, tidak
           ada grid, tidak ada custom property. Tata letaknya karena itu
           memakai tabel dan lebar persen. */
        @page {
            margin: 96px 32px 64px;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5px;
            color: #334155;
        }

        /* Kepala dan kaki position: fixed berarti ia terulang di TIAP halaman,
           jadi lembar kedua dan seterusnya tetap berlogo dan berpenomoran. */
        .kepala {
            position: fixed;
            top: -76px;
            left: 0;
            right: 0;
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
            margin: 2px 0 0;
            font-size: 9.5px;
            color: #64748b;
        }

        .garis-kepala {
            position: fixed;
            top: -14px;
            left: 0;
            right: 0;
            border-top: 2px solid #6366f1;
        }

        .kaki {
            position: fixed;
            bottom: -44px;
            left: 0;
            /* Lebar pasti, bukan left+right: dompdf tidak menurunkan lebar
               kotak position: fixed dari kedua tepinya, sehingga lebar persen
               di dalamnya tidak punya acuan dan kalimatnya melebar penuh
               sampai tertimpa nomor halaman.
               Satuannya pt, bukan px: dompdf memampatkan px dengan 0,75
               (96 dpi -> 72 dpi), jadi 531px hanya jadi 398pt dan garis
               kakinya berhenti di tengah halaman.
               547 = lebar A4 595pt dikurangi dua kali margin 24pt (yaitu
               32px pada @page, yang juga ikut dimampatkan). */
            width: 547pt;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
            color: #94a3b8;
        }

        .keterangan-berkas {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .keterangan-berkas td {
            padding: 5px 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }

        .label {
            color: #94a3b8;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .nilai {
            color: #0f172a;
            font-weight: bold;
        }

        table.daftar {
            width: 100%;
            border-collapse: collapse;
        }

        table.daftar thead th {
            padding: 7px 8px;
            background: #6366f1;
            color: #ffffff;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: .04em;
            text-align: left;
            border: 1px solid #6366f1;
        }

        table.daftar tbody td {
            padding: 6px 8px;
            border: 1px solid #e8edf3;
            vertical-align: middle;
        }

        table.daftar tbody tr.selang td {
            background: #fafbfd;
        }

        .waktu {
            white-space: nowrap;
            font-weight: bold;
            color: #0f172a;
        }

        .samar {
            color: #64748b;
        }

        /* Lencana hasil: span berlatar, sebab dompdf tidak mengenal
           border-radius pada elemen sebaris yang diberi padding besar. */
        .lencana {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 8px;
            font-size: 8.5px;
            font-weight: bold;
            white-space: nowrap;
        }

        .lencana-berhasil {
            background: #d1fae5;
            color: #047857;
        }

        .lencana-gagal {
            background: #ffe4e6;
            color: #be123c;
        }

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
                @if ($logo)
                    <td width="150"><img src="{{ $logo }}" alt="MIS Rumah Scopus" height="38"></td>
                @endif
                <td>
                    <p class="judul">Riwayat Keamanan Akun</p>
                    <p class="anak-judul">{{ $pengguna->full_name ?: $pengguna->username }}</p>
                </td>
                <td align="right" class="anak-judul">
                    MIS Rumah Scopus Foundation<br>
                    Diunduh {{ now()->format('d/m/Y H:i') }} WIB
                </td>
            </tr>
        </table>
    </div>

    <div class="garis-kepala"></div>

    <div class="kaki">
        {{-- Nomor halamannya TIDAK ditulis di sini. Blade tidak bisa
             menghitungnya: pemenggalan halaman baru diketahui saat render,
             jadi yang menggambarnya skrip Dompdf di bawah. Sebelumnya kedua
             cara dipakai sekaligus, sehingga muncul kata "Halaman" kosong
             berdampingan dengan "1 dari 1". --}}
        {{-- Dibatasi 70%: sisa kanannya disediakan untuk nomor halaman yang
             digambar skrip. Tanpa batas ini kalimatnya melebar penuh dan
             tertimpa nomornya. --}}
        <table width="100%">
            <tr>
                <td width="70%">
                    Berkas ini memuat riwayat keamanan akun <strong>{{ $pengguna->username }}</strong>.
                    Ada baris yang bukan Anda? Segera ganti kata sandi dan hubungi admin.
                </td>
                <td width="30%"></td>
            </tr>
        </table>
    </div>

    <table class="keterangan-berkas">
        <tr>
            <td width="34%">
                <span class="label">Akun</span><br>
                <span class="nilai">{{ $pengguna->username }}</span>
            </td>
            <td width="34%">
                <span class="label">Alamat email</span><br>
                <span class="nilai">{{ $pengguna->email }}</span>
            </td>
            <td width="16%">
                <span class="label">Jumlah catatan</span><br>
                <span class="nilai">{{ $baris->count() }}</span>
            </td>
            <td width="16%">
                <span class="label">Percobaan gagal</span><br>
                <span class="nilai">{{ $gagal }}</span>
            </td>
        </tr>
    </table>

    @if ($baris->isEmpty())
        <p class="kosong">Belum ada catatan keamanan pada akun ini.</p>
    @else
        <table class="daftar">
            <thead>
                <tr>
                    <th width="17%">Waktu</th>
                    <th width="12%">Hasil</th>
                    <th width="29%">Keterangan</th>
                    <th width="16%">Alamat IP</th>
                    <th width="26%">Perangkat</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($baris as $i => $a)
                    <tr class="{{ $i % 2 ? 'selang' : '' }}">
                        <td class="waktu">{{ optional($a->created_at)->format('d/m/Y H:i:s') }}</td>
                        <td>
                            <span class="lencana {{ $a->berhasil ? 'lencana-berhasil' : 'lencana-gagal' }}">
                                {{ $a->berhasil ? 'Berhasil' : 'Gagal' }}
                            </span>
                        </td>
                        <td>{{ $a->alasan ?: '—' }}</td>
                        <td class="samar">{{ $a->ip ?: '—' }}</td>
                        {{-- Nama yang bisa dibaca orang, sama seperti di layar. --}}
                        <td class="samar">{{ \App\Support\NamaPerangkat::ringkas($a->peramban) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Nomor halaman digambar Dompdf sendiri; ia tidak bisa dihitung dari
         Blade karena pemenggalan halamannya baru diketahui saat render. --}}
    <script type="text/php">
        if (isset($pdf)) {
            $teks = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            $huruf = $fontMetrics->getFont("DejaVu Sans");

            /*
             * Lebarnya TIDAK boleh diukur dari $teks apa adanya: penggantian
             * {PAGE_NUM} dan {PAGE_COUNT} baru terjadi setelah ini, sehingga
             * yang terukur adalah panjang penandanya (36 huruf) dan tulisannya
             * terlempar ~110pt ke kiri. Jadi penandanya diganti dulu dengan
             * angka contoh sepanjang jumlah halaman sebenarnya — angka DejaVu
             * Sans berlebar seragam, jadi 0 sama lebarnya dengan 9.
             */
            $jumlah = (string) (method_exists($pdf, "get_page_count") ? $pdf->get_page_count() : 1);
            $contoh = str_replace(
                ["{PAGE_NUM}", "{PAGE_COUNT}"],
                [str_repeat("0", strlen($jumlah)), $jumlah],
                $teks
            );
            $lebar = $fontMetrics->getTextWidth($contoh, $huruf, 6);

            /*
             * Dirata-kanan ke tepi cetak dan sebaris dengan kalimat kaki di
             * sebelah kiri. 24 = margin kanan sesungguhnya (32px pada @page
             * yang dimampatkan dompdf jadi 24pt); 26 diukur dari hasil render
             * supaya alasnya sebaris dengan kalimat kaki, bukan 5pt di
             * bawahnya. Ukuran 6pt = 8px pada .kaki sesudah pemampatan yang
             * sama; dengan 8pt nomornya tampak lebih besar daripada kalimat
             * di sebelah kirinya.
             */
            $pdf->page_text(
                $pdf->get_width() - 24 - $lebar, $pdf->get_height() - 26,
                $teks, $huruf, 6, [0.58, 0.64, 0.72]
            );
        }
    </script>
</body>

</html>
