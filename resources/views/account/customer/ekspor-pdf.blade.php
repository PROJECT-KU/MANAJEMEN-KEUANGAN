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
            margin: 2px 0 0;
            font-size: 9px;
            color: #64748b;
        }

        .garis-kepala {
            position: fixed;
            top: -14px;
            left: 0;
            width: 730pt;
            border-top: 2px solid #6366f1;
        }

        .kaki {
            position: fixed;
            bottom: -44px;
            left: 0;
            width: 730pt;
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
            font-size: 8.5px;
        }

        .label {
            color: #94a3b8;
            font-size: 7px;
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
                @if ($logo)
                    <td width="150"><img src="{{ $logo }}" alt="MIS Rumah Scopus" height="38"></td>
                @endif
                <td>
                    <p class="judul">Data Pelanggan</p>
                    <p class="anak-judul">Orang luar yang memakai layanan jasa Rumah Scopus.</p>
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
        <table width="100%">
            <tr>
                <td width="70%">
                    Jumlah pesanan dihitung dari tiga layanan yang mencatat pemesannya
                    (Clinik Scopus, Analisis Bibliometrik, Scopus Kafe).
                </td>
                <td width="30%"></td>
            </tr>
        </table>
    </div>

    <table class="keterangan-berkas">
        <tr>
            <td width="25%">
                <span class="label">Jumlah pelanggan</span><br>
                <span class="nilai">{{ number_format($pelanggan->count()) }}</span>
            </td>
            <td width="25%">
                <span class="label">Akun aktif</span><br>
                <span class="nilai">{{ number_format($aktif) }}</span>
            </td>
            <td width="25%">
                <span class="label">Email terverifikasi</span><br>
                <span class="nilai">{{ number_format($terverifikasi) }}</span>
            </td>
            <td width="25%">
                <span class="label">Saringan</span><br>
                <span class="nilai">
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
