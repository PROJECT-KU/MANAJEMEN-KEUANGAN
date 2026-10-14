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

    /*
     * Dihitung dari baris yang BENAR-BENAR tercetak, bukan dari seluruh tabel.
     *
     * Angka ringkasan yang menghitung seluruh tabel sementara daftarnya
     * tersaring akan bercerita tentang kumpulan yang berbeda dari yang ada di
     * bawahnya — dan itu jenis selisih yang membuat orang berhenti
     * mempercayai angkanya.
     */
    $perluDicek = $angkatan->filter(fn ($a) => ! empty($a->perlu_dicek))->count();
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
         * 794pt, bukan 730pt: A4 MENDATAR 842pt dikurangi bantalan 32px
         * (24pt) di kedua sisi. Dengan 730pt garisnya berhenti 64pt sebelum
         * tepi kanan isi.
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
         * tercetak, hijau untuk angkatan yang sedang berjalan, kuning HANYA
         * saat memang ada yang perlu dicek. Selalu kuning, warnanya berhenti
         * berarti apa-apa dan "Perlu dicek 0" ikut terbaca seperti peringatan.
         *
         * Pemilihnya SELENGKAP pemilih dasarnya. Ditulis ".kartu-pokok" saja,
         * bobotnya (0,1,0) kalah dari ".keterangan-berkas td.kartu" (0,2,1)
         * di atas — dan yang tercetak keempat kartu abu-abu seragam tanpa
         * galat apa pun.
         */
        .keterangan-berkas td.kartu-pokok { border-left-color: #6366f1; background: #eef2ff; }
        .keterangan-berkas td.kartu-hidup { border-left-color: #10b981; background: #ecfdf5; }
        .keterangan-berkas td.kartu-awas  { border-left-color: #f59e0b; background: #fffbeb; }

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

        /* Merah tua, bukan merah terang: dompdf mencetak merah terang jadi
           abu kehitaman pada pencetak hitam-putih, dan keterangannya hilang. */
        .perlu {
            color: #9f1239;
            font-size: 7.5px;
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
                {{-- Kedua lajur tepi SAMA LEBAR, dan judulnya terpusat di
                     lajur tengah. Dengan lajur logo 150px dan lajur kanan
                     tanpa lebar tetap, judulnya terpusat pada SISA lebar —
                     bukan pada lebar halaman. --}}
                @if ($logo)
                    <td width="190"><img src="{{ $logo }}" alt="MIS Rumah Scopus" height="38"></td>
                @endif
                <td align="center">
                    <p class="judul">Angkatan Layanan</p>
                    <p class="anak-judul">Angkatan seluruh layanan jasa Rumah Scopus.</p>
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
        <tr valign="top">
            <td width="15%" class="kartu kartu-pokok">
                <span class="label">Angkatan tercetak</span><br>
                <span class="nilai">{{ number_format($angkatan->count(), 0, ',', '.') }}</span>
            </td>
            <td class="sela"></td>
            <td width="12%" class="kartu kartu-hidup">
                <span class="label">Sedang aktif</span><br>
                <span class="nilai">{{ number_format($aktif, 0, ',', '.') }}</span>
            </td>
            <td class="sela"></td>
            <td width="13%" class="kartu">
                <span class="label">Total pendaftar</span><br>
                <span class="nilai">{{ number_format($pendaftar, 0, ',', '.') }}</span>
            </td>
            <td class="sela"></td>
            {{-- Kuning HANYA saat memang ada yang perlu dicek; selalu kuning,
                 warnanya berhenti berarti apa-apa. --}}
            <td width="13%" class="kartu {{ $perluDicek > 0 ? 'kartu-awas' : '' }}">
                <span class="label">Perlu dicek</span><br>
                <span class="nilai">{{ number_format($perluDicek, 0, ',', '.') }}</span>
            </td>
            <td class="sela"></td>
            <td class="kartu">
                <span class="label">Saringan</span><br>
                <span class="nilai nilai-teks">{{ $saringan }}</span>
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

                            {{-- Ditaruh di bawah namanya, BUKAN sebagai kolom
                                 ketujuh: enam kolomnya sudah dipatok persen dan
                                 menambah satu lagi memaksa semuanya menyempit.
                                 Di sini ia juga terbaca sebagai keterangan baris
                                 itu, bukan sebagai angka yang harus dibandingkan
                                 antarbaris. --}}
                            @if ($a->perlu_dicek)
                                <br><span class="perlu">Perlu dicek: {{ implode('; ', $a->perlu_dicek) }}</span>
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
