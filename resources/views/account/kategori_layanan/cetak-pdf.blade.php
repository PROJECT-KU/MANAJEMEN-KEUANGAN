@php
    /*
     * Logo sebagai data URI; Dompdf tidak mengambil berkas luar kecuali
     * isRemoteEnabled dinyalakan. Kepala, garis indigo, kaki, dan nomor
     * halamannya disalin dari ekspor Data Pelanggan dan Daftar Harga supaya
     * seluruh berkas yang keluar dari MIS terlihat serupa.
     */
    $berkasLogo = public_path('assets/img/logo-email.png');
    $logo = is_file($berkasLogo)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($berkasLogo))
        : null;

    $aktif = $angkatan->where('status', 'active')->count();
    $pendaftar = $angkatan->sum(fn ($a) => $a->jumlah_pendaftar);
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Angkatan Layanan — MIS Rumah Scopus</title>
    <style>
        /* Dompdf hanya mengenal sebagian kecil CSS: tidak ada flexbox, tidak
           ada grid, tidak ada custom property. Tata letaknya memakai tabel dan
           lebar persen. Satuan pt, bukan px: dompdf memampatkan px dengan 0,75
           sehingga angka px meleset seperempat. */
        @page {
            margin: 96px 34px 64px;
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
            padding: 6px 7px;
            border: 1px solid #e8edf3;
            vertical-align: top;
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

        .harga {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
        }

        /* Lencana: span berlatar, sebab dompdf tidak mengenal border-radius
           pada elemen sebaris yang diberi padding besar. */
        .lencana {
            padding: 1px 5px;
            background: #eef2ff;
            color: #4338ca;
            font-size: 7.5px;
        }

        /* Daftar fasilitas sebagai baris terpisah, bukan <ul>: dompdf memberi
           bulatan dan indentasi yang tidak bisa dikendalikan. */
        .fasilitas {
            margin: 0;
            line-height: 1.5;
        }

        .kosong {
            padding: 18px;
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
                    <p class="judul">Angkatan Layanan</p>
                    <p class="anak-judul">Angkatan seluruh layanan jasa Rumah Scopus.</p>
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
                <td width="72%">
                    Jumlah pendaftar dihitung dari pendaftaran Scopus Camp dan Analisis
                    Bibliometrik. Layanan yang pendaftarannya belum tercatat di sistem ini
                    tertulis nol.
                </td>
                <td width="28%"></td>
            </tr>
        </table>
    </div>

    <table class="keterangan-berkas">
        <tr>
            <td width="34%">
                <span class="label">Saringan</span><br>
                <span class="nilai">{{ $saringan }}</span>
            </td>
            <td width="33%">
                <span class="label">Jumlah angkatan</span><br>
                <span class="nilai">{{ $angkatan->count() }} baris &middot; {{ $aktif }} aktif</span>
            </td>
            <td width="33%">
                <span class="label">Total pendaftar</span><br>
                <span class="nilai">{{ $pendaftar }} orang</span>
            </td>
        </tr>
    </table>

    @if ($angkatan->isEmpty())
        <p class="kosong">Tidak ada angkatan yang cocok dengan saringan ini.</p>
    @else
        <table class="daftar">
            <thead>
                <tr>
                    <th width="22%">Angkatan</th>
                    <th width="14%">Layanan</th>
                    <th width="14%">Tanggal</th>
                    <th width="11%">Kuota</th>
                    <th width="13%">Biaya</th>
                    <th width="10%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($angkatan as $i => $a)
                    <tr class="{{ $i % 2 ? 'selang' : '' }}">
                        <td>
                            <span class="tebal">{{ $a->nama }}</span>
                            @if ($a->nama_ke)
                                <br><span class="samar">Angkatan ke-{{ $a->nama_ke }}</span>
                            @endif
                            @if ($a->lokasi)
                                <br><span class="samar">{{ $a->lokasi }}</span>
                            @endif
                        </td>

                        <td>
                            {{ $a->nama_layanan }}
                            @if ($a->nama_varian)
                                <br><span class="lencana">{{ $a->nama_varian }}</span>
                            @endif
                        </td>

                        <td class="samar">
                            {{ \App\Support\RentangTanggal::tulis(
                                $a->mulai ? \Carbon\Carbon::parse($a->mulai) : null,
                                $a->selesai ? \Carbon\Carbon::parse($a->selesai) : null
                            ) ?: '—' }}
                        </td>

                        <td class="nowrap">
                            <span class="tebal">{{ $a->sisa_kuota ?? '—' }}</span>
                            <span class="samar">dari {{ $a->total_kuota ?? '—' }}</span>
                            <br><span class="samar">{{ $a->jumlah_pendaftar }} pendaftar</span>
                        </td>

                        <td class="nowrap">
                            @if ((int) $a->biaya > 0)
                                <span class="tebal">Rp {{ number_format((int) $a->biaya, 0, ',', '.') }}</span>
                                @if ((int) $a->total_biaya > 0 && (int) $a->total_biaya !== (int) $a->biaya)
                                    <br><span class="samar">
                                        promo Rp {{ number_format((int) $a->total_biaya, 0, ',', '.') }}
                                    </span>
                                @endif
                            @else
                                <span class="samar">—</span>
                            @endif
                        </td>

                        <td>
                            @php($rupa = ['active' => 'Aktif', 'non active' => 'Nonaktif', 'draft' => 'Draf'])
                            {{ $rupa[$a->status] ?? $a->status }}
                            @if ($a->sudah_lewat)
                                <br><span class="samar">tanggalnya lewat</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Nomor halaman digambar Dompdf sendiri; Blade tidak bisa menghitungnya
         karena pemenggalan halamannya baru diketahui saat render.

         Disalin apa adanya dari ekspor Data Pelanggan — termasuk jebakan
         pengukurannya di bawah. --}}
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
                $pdf->get_width() - 26 - $lebar, $pdf->get_height() - 26,
                $teks, $huruf, 6, [0.58, 0.64, 0.72]
            );
        }
    </script>
</body>

</html>
