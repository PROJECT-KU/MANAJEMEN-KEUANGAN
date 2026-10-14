@php
    /*
     * Logo disisipkan sebagai data URI, bukan ditautkan: Dompdf hanya mau
     * mengambil berkas dari luar kalau isRemoteEnabled dinyalakan, dan alamat
     * yang dirangkai asset() menunjuk APP_URL.
     *
     * Kepala, garis indigo, dan kaki disalin apa adanya dari ekspor Data
     * Pelanggan supaya seluruh berkas yang keluar dari MIS terlihat serupa.
     */
    $berkasLogo = public_path('assets/img/logo-email.png');
    $logo = is_file($berkasLogo)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($berkasLogo))
        : null;

    $berppn = collect($baris)->filter(fn ($b) => $b['tarif']->ppn_persen > 0)->count();
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Daftar Harga Layanan — MIS Rumah Scopus</title>
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
            width: 525pt;
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
         * 547pt, bukan 525pt: A4 TEGAK 595pt dikurangi bantalan 32px (24pt)
         * di kedua sisi. Dengan 525pt garisnya berhenti 22pt sebelum tepi
         * kanan isi, dan kepalanya terlihat kurang panjang.
         */
        .garis-kepala {
            position: fixed;
            top: -14px;
            left: 0;
            width: 547pt;
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
            width: 547pt;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
            color: #94a3b8;
        }

        /*
         * Ringkasan jadi kartu TERPISAH, bukan satu baris sel bersambung.
         *
         * Bersambung, ketiga angka berbeda terbaca seperti satu baris tabel
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
         * tercetak, kuning HANYA saat memang ada tarif yang kena PPN — selalu
         * kuning, warnanya berhenti berarti apa-apa dan "Tidak ada" ikut
         * terbaca seperti peringatan.
         *
         * Pemilihnya SELENGKAP pemilih dasarnya. Ditulis ".kartu-pokok" saja,
         * bobotnya (0,1,0) kalah dari ".keterangan-berkas td.kartu" (0,2,1)
         * di atas — dan yang tercetak ketiga kartu abu-abu seragam tanpa
         * galat apa pun.
         */
        .keterangan-berkas td.kartu-pokok { border-left-color: #6366f1; background: #eef2ff; }
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

        /* Tanggal berlakunya kalimat, bukan angka: pada 11px ia memakan dua
           baris lalu menaikkan tinggi seluruh pita. */
        .nilai-teks { font-size: 8px; font-weight: normal; color: #334155; }

        /*
         * Potongan ditulis hijau, bukan abu seperti satuan di atasnya.
         *
         * Ia satu-satunya angka di lembar ini yang MENGURANGI yang harus
         * dibayar; disamakan dengan keterangan satuan, ia terbaca sebagai
         * catatan kaki dan terlewat oleh pembacanya.
         */
        .potongan {
            font-size: 7.5px;
            font-weight: bold;
            color: #15803d;
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
                {{-- Kedua lajur tepi SAMA LEBAR, dan judulnya terpusat di
                     lajur tengah.

                     Dengan lajur logo 150px dan lajur kanan tanpa lebar
                     tetap, judulnya terpusat pada SISA lebar — bukan pada
                     lebar halaman — sehingga titik tengahnya jatuh sekitar
                     sepertiga lebar kertas. 150px cukup memuat "MIS Rumah
                     Scopus Foundation" pada 8,5px tebal di kertas tegak. --}}
                @if ($logo)
                    <td width="150"><img src="{{ $logo }}" alt="MIS Rumah Scopus" height="38"></td>
                @endif
                <td align="center">
                    <p class="judul">Daftar Harga Layanan</p>
                    <p class="anak-judul">Harga dan fasilitas layanan jasa Rumah Scopus.</p>
                </td>
                <td width="150" align="right">
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
                    Harga dapat berubah sewaktu-waktu. Angkatan yang sudah berjalan memakai
                    harga yang berlaku saat pendaftarannya dibuka, bukan harga di lembar ini.
                </td>
                <td width="28%"></td>
            </tr>
        </table>
    </div>

    <table class="keterangan-berkas">
        <tr valign="top">
            <td width="22%" class="kartu kartu-pokok">
                <span class="label">Tarif berlaku</span><br>
                <span class="nilai">{{ count($baris) }}</span>
            </td>
            <td class="sela"></td>
            <td width="24%" class="kartu {{ $berppn > 0 ? 'kartu-awas' : '' }}">
                <span class="label">Dikenakan PPN</span><br>
                <span class="nilai">
                    {{ $berppn > 0 ? $berppn . ' dari ' . count($baris) : 'Tidak ada' }}
                </span>
            </td>
            <td class="sela"></td>
            <td class="kartu">
                {{-- DULU "Berlaku per". Diganti saat kolom "Berlaku sejak"
                     masuk ke tabelnya: dua tulisan yang hampir sama persis
                     di satu halaman, padahal artinya berbeda — yang ini
                     tanggal cetaknya, yang di tabel tanggal mulai berlakunya
                     tiap tarif. --}}
                <span class="label">Harga per tanggal</span><br>
                <span class="nilai nilai-teks">{{ now()->locale('id')->translatedFormat('d F Y') }}</span>
            </td>
        </tr>
    </table>

    @if (count($baris) === 0)
        <p class="kosong">Belum ada layanan yang tarifnya disetel.</p>
    @else
        <table class="daftar">
            <thead>
                <tr>
                    {{-- Persennya WAJIB berjumlah 100; Dompdf membagi sisanya
                         sekehendaknya kalau kurang. Lebar kolom "Berlaku
                         sejak" diambil dari Layanan dan Fasilitas sekaligus,
                         supaya daftar fasilitasnya tidak menyempit sendirian
                         — kolom itu yang paling banyak isinya. --}}
                    <th width="24%">Layanan</th>
                    <th width="18%">Harga</th>
                    <th width="12%">Berlaku sejak</th>
                    <th width="46%">Fasilitas yang didapat</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($baris as $i => $b)
                    @php($t = $b['tarif'])
                    <tr class="{{ $i % 2 ? 'selang' : '' }}">
                        <td>
                            <span class="tebal">{{ $b['nama'] }}</span>
                            @if ($b['namaVarian'])
                                <br><span class="lencana">{{ $b['namaVarian'] }}</span>
                            @endif
                        </td>

                        <td class="nowrap">
                            <span class="harga">{{ $t->tarif_terbaca }}</span><br>
                            <span class="samar">{{ $b['satuan'] }}</span>
                            @if ($t->ppn_persen > 0)
                                <br><span class="samar">
                                    + PPN {{ $t->ppn_persen }}% &rarr;
                                    <span class="tebal">Rp {{ number_format($t->total_dibayar, 0, ',', '.') }}</span>
                                </span>
                            @endif

                            {{-- Potongan yang BERLAKU untuk pembacanya.

                                 Lembar ini yang dibagikan ke calon peserta,
                                 dan potongannya justru hal yang paling ingin
                                 mereka ketahui — tetapi sebelum ini hanya ada
                                 di lembar kerja yang dipakai ke dalam.
                                 Terukur: Scopus Camp Jawa memberi potongan
                                 alumni 10% yang tidak pernah muncul di kertas
                                 yang sampai ke orangnya.

                                 Yang TIDAK ikut dan sengaja: siapa yang
                                 menyetel tarifnya. Itu urusan ke dalam, dan
                                 panduan sudah mencatat bahwa kertas ini
                                 dibagikan ke luar. --}}
                            @if ($t->diskon_alumni_persen > 0 || $t->diskon_rombongan_persen > 0)
                                <br><span class="potongan">
                                    @if ($t->diskon_alumni_persen > 0)
                                        Alumni &minus;{{ (int) $t->diskon_alumni_persen }}%
                                    @endif
                                    @if ($t->diskon_alumni_persen > 0 && $t->diskon_rombongan_persen > 0)
                                        <br>
                                    @endif
                                    @if ($t->diskon_rombongan_persen > 0)
                                        {{ (int) $t->diskon_rombongan_min }}+ orang
                                        &minus;{{ (int) $t->diskon_rombongan_persen }}%
                                    @endif
                                </span>
                            @endif
                        </td>

                        {{-- Tanggal mulai berlakunya tarif ini.

                             Aman dicetak di kertas yang dibagikan ke luar
                             KARENA daftar ini hanya memuat tarif yang sudah
                             berlaku: barisnya datang dari semuaYangBerlaku(),
                             yang menyaring status AKTIF. Tarif yang baru
                             dijadwalkan masih berstatus TERJADWAL dan tidak
                             pernah sampai ke sini — justru itu kebocoran yang
                             ditakutkan dulu, yaitu rencana kenaikan harga
                             terbaca orang luar sebelum waktunya.

                             Tarif lama banyak yang tanggalnya belum pernah
                             diisi (terukur: 6 dari 8 saat kolom ini dibuat),
                             jadi tanda pisahnya bukan kelainan. --}}
                        <td class="samar nowrap">
                            {{ $t->berlaku_mulai?->format('d/m/Y') ?? '—' }}
                        </td>

                        <td>
                            @if ($t->daftar_fasilitas)
                                {{-- SELURUHNYA, tidak dipotong. Daftar harga yang
                                     menyembunyikan sebagian isinya lebih berbahaya
                                     daripada tidak ada. --}}
                                <p class="fasilitas">
                                    @foreach ($t->daftar_fasilitas as $n => $f)
                                        {{ $n + 1 }}. {{ $f }}@if (! $loop->last)<br>@endif
                                    @endforeach
                                </p>
                            @else
                                <span class="samar">&mdash;</span>
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
