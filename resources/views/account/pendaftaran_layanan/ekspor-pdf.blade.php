@php
    use App\Support\PendaftaranSemuaLayanan as Pendaftaran;

    /*
     * Logo disisipkan sebagai data URI, bukan ditautkan: Dompdf hanya mau
     * mengambil berkas dari luar kalau isRemoteEnabled dinyalakan — dan di
     * pengendalinya ia sengaja dimatikan, supaya berkas PDF tidak pernah bisa
     * dipakai menarik alamat luar.
     */
    $berkasLogo = public_path('assets/img/logo-email.png');
    $logo = is_file($berkasLogo)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($berkasLogo))
        : null;

    /*
     * Angka ringkasnya dihitung dari baris yang BENAR-BENAR tercetak, bukan
     * dari seluruh tabel. Berkas yang memuat 29 baris tersaring sambil
     * mencetak jumlah 187 akan terbaca sebagai daftar yang lengkap, dan
     * berkas unduhan justru yang paling sering diteruskan ke orang lain.
     */
    $jumlahOrang = 0;
    $uangLunas = 0;
    $belumBayar = 0;

    foreach ($baris as $satu) {
        $jumlahOrang += (int) $satu->jumlah;
        $keadaanSatu = Pendaftaran::keadaanDari($satu->status);

        if ($keadaanSatu === 'lunas') {
            $uangLunas += (int) $satu->total;
        }

        if ($keadaanSatu === 'menunggu') {
            $belumBayar++;
        }
    }

    /* Warna lencana PDF dipetakan dari warna layar, supaya kertas dan layar
       tidak bercerita berbeda tentang baris yang sama. */
    $kelasLencana = [
        'hijau' => 'l-hijau',
        'kuning' => 'l-kuning',
        'merah' => 'l-merah',
        'biru' => 'l-biru',
        'ungu' => 'l-ungu',
        'abu' => 'l-abu',
    ];
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Pendaftar Layanan — MIS Rumah Scopus</title>
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
            font-size: 8.5px;
            color: #334155;
        }

        /* position: fixed berarti kepala dan kaki terulang di TIAP halaman.
           Lebarnya disebut dalam pt sebab elemen fixed tidak mewarisi lebar
           induknya; 794pt adalah lebar A4 mendatar dikurangi kedua marginnya. */
        .kepala {
            position: fixed;
            top: -76px;
            left: 0;
            width: 794pt;
            height: 58px;
        }

        .kepala td {
            vertical-align: middle;
        }

        .judul {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: .6px;
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
            /* #a5b4fc dicoba lebih dulu dan nyaris tak terbaca di atas
               putih saat dicetak; #818cf8 tetap beraksen tanpa memudar. */
            color: #818cf8;
        }

        .meta-nilai { font-size: 8.5px; color: #475569; font-weight: bold; }

        /*
         * Dua garis beda tebal, bukan satu.
         *
         * Satu garis penuh terbaca seperti pembatas seadanya; potongan tebal
         * berwarna merek di ujung kiri membuat kepalanya terbaca sebagai kop.
         * Keduanya fixed supaya ikut terulang di tiap halaman.
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
            font-size: 7.5px;
            color: #94a3b8;
        }

        /*
         * Ringkasan jadi kartu TERPISAH, bukan satu baris sel bersambung.
         *
         * Bersambung, kelima angka berbeda terbaca seperti satu baris tabel
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
         * tercetak, kuning hanya saat memang ada yang belum bayar, hijau
         * untuk uang yang sudah masuk.
         *
         * Pemilihnya SELENGKAP pemilih dasarnya. Ditulis ".kartu-pokok"
         * saja, bobotnya (0,1,0) kalah dari ".keterangan-berkas td.kartu"
         * (0,2,1) di atas — dan yang tercetak kelima kartu abu-abu seragam
         * tanpa galat apa pun. Terjadi persis begitu saat dicoba, dan sempat
         * disangka batasan Dompdf pada border-collapse.
         */
        .keterangan-berkas td.kartu-pokok { border-left-color: #6366f1; background: #eef2ff; }
        .keterangan-berkas td.kartu-awas  { border-left-color: #f59e0b; background: #fffbeb; }
        .keterangan-berkas td.kartu-uang  { border-left-color: #10b981; background: #ecfdf5; }

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
        .nilai-teks { color: #0f172a; font-weight: bold; font-size: 8px; }

        table.daftar {
            width: 100%;
            border-collapse: collapse;
        }

        table.daftar thead th {
            padding: 7px 6px;
            background: #4f46e5;
            color: #ffffff;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: .09em;
            text-align: left;
            border: 1px solid #4f46e5;
        }

        table.daftar tbody td {
            padding: 6px;
            border: 1px solid #e8edf3;
            vertical-align: top;
        }

        table.daftar tbody tr.selang td {
            background: #fafbfd;
        }

        /* Lajur nomor diberi latar sendiri supaya ujung kiri tabel punya
           jangkar — tanpa itu barisnya terbaca melayang di halaman lebar. */
        table.daftar tbody td.no {
            background: #f1f5f9;
            text-align: center;
            font-weight: bold;
            color: #64748b;
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

        .kanan {
            text-align: right;
        }

        .tengah {
            text-align: center;
        }

        /* Lencana: span berlatar, sebab dompdf tidak mengenal border-radius
           pada elemen sebaris yang diberi padding besar. */
        .lencana {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 8px;
            font-size: 7px;
            font-weight: bold;
            white-space: nowrap;
        }

        .l-hijau  { background: #d1fae5; color: #047857; }
        .l-abu    { background: #eef2f7; color: #475569; }
        .l-biru   { background: #dbeafe; color: #1d4ed8; }
        .l-kuning { background: #fef3c7; color: #92400e; }
        .l-merah  { background: #ffe4e6; color: #be123c; }
        .l-ungu   { background: #ede9fe; color: #6d28d9; }

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

                     Semula lajur logo 150px sementara lajur kanan tanpa lebar
                     tetap, jadi judulnya terpusat pada SISA lebar — bukan pada
                     lebar halaman. Terlihat jelas di hasil cetaknya: titik
                     tengah judulnya jatuh sekitar sepertiga lebar kertas,
                     bukan separuhnya.

                     190px cukup memuat "MIS Rumah Scopus Foundation" pada
                     8,5px tebal tanpa terpatah. --}}
                @if ($logo)
                    <td width="190"><img src="{{ $logo }}" alt="MIS Rumah Scopus" height="38"></td>
                @endif
                <td align="center">
                    <p class="judul">Pendaftar Layanan</p>
                    <p class="anak-judul">Semua yang mendaftar layanan jasa Rumah Scopus.</p>
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
                <td width="75%">
                    Dirangkum dari lima layanan: Scopus Camp, Analisis Bibliometrik, Webinar
                    Eksklusif, Scopus Kafe, dan Clinik Scopus. Online Training tidak tercatat
                    di sistem ini. Kolom bukti bayar menyebut &ldquo;berkas hilang&rdquo; bila
                    kolomnya terisi tetapi berkasnya tidak ada lagi di server.
                </td>
                <td width="25%"></td>
            </tr>
        </table>
    </div>

    <table class="keterangan-berkas">
        <tr valign="top">
            <td width="14%" class="kartu kartu-pokok">
                <span class="label">Pendaftaran tercetak</span><br>
                <span class="nilai">{{ number_format($baris->count(), 0, ',', '.') }}</span>
            </td>
            <td class="sela"></td>
            <td width="11%" class="kartu">
                <span class="label">Jumlah orang</span><br>
                <span class="nilai">{{ number_format($jumlahOrang, 0, ',', '.') }}</span>
            </td>
            <td class="sela"></td>
            {{-- Kuning HANYA saat memang ada yang belum bayar. Selalu kuning,
                 warnanya berhenti berarti apa-apa dan "Belum bayar 0" ikut
                 terbaca seperti peringatan. --}}
            <td width="11%" class="kartu {{ $belumBayar > 0 ? 'kartu-awas' : '' }}">
                <span class="label">Belum bayar</span><br>
                <span class="nilai">{{ number_format($belumBayar, 0, ',', '.') }}</span>
            </td>
            <td class="sela"></td>
            <td width="19%" class="kartu kartu-uang">
                <span class="label">Uang masuk dari yang lunas</span><br>
                <span class="nilai">Rp {{ number_format($uangLunas, 0, ',', '.') }}</span>
            </td>
            <td class="sela"></td>
            <td class="kartu">
                <span class="label">Saringan</span><br>
                <span class="nilai-teks">
                    {{-- Saringan yang sedang dipakai ikut dicetak: tanpa itu,
                         berkas berisi 12 baris tidak bisa dibedakan dari daftar
                         yang memang cuma punya 12 pendaftar. --}}
                    @if (empty($saringan))
                        Tanpa saringan &mdash; seluruh pendaftar
                    @else
                        @foreach ($saringan as $nama => $nilai)
                            {{ $nama }}: {{ $nilai }}@if (! $loop->last) &middot; @endif
                        @endforeach
                    @endif
                </span>
            </td>
        </tr>
    </table>

    @if ($baris->isEmpty())
        <p class="kosong">Tidak ada pendaftar yang cocok dengan saringan ini.</p>
    @else
        <table class="daftar">
            <thead>
                <tr>
                    {{-- Kepala kolom DISEJAJARKAN dengan isinya: nomor dan
                         jumlah orang di tengah, nominal di kanan. Semuanya
                         rata kiri, kepala "Total bayar" berdiri jauh dari
                         angka yang diwakilinya. --}}
                    {{-- "Kode diskon" duduk sesudah "Total bayar", persis
                         urutan lembar kerjanya: kolom yang sama tidak boleh
                         berpindah tempat antara dua berkas yang diunduh dari
                         dua tombol bersebelahan.

                         Persennya WAJIB berjumlah 100; kurang sedikit saja,
                         Dompdf membagikan sisanya sekehendaknya dan lebar
                         yang disetel di sini berhenti berarti. --}}
                    <th width="3%" class="tengah">No.</th>
                    <th width="11%">Layanan</th>
                    <th width="9%">Nomor</th>
                    <th width="15%">Pendaftar</th>
                    <th width="12%">Kontak</th>
                    <th width="13%">Sesi</th>
                    <th width="4%" class="tengah">Orang</th>
                    <th width="10%" class="kanan">Total bayar</th>
                    <th width="10%">Kode diskon</th>
                    <th width="13%">Keadaan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($baris as $i => $b)
                    @php
                        $layananBaris = $katalog[$b->layanan]['nama'] ?? $b->layanan;
                        $keadaanBaris = Pendaftaran::keadaanDari($b->status);
                        $rupa = Pendaftaran::KEADAAN[$keadaanBaris]
                            ?? ['label' => 'Belum dikenali', 'warna' => 'abu'];
                        $buktiBaris = Pendaftaran::buktiBaris($b);
                        $waktuBaris = Pendaftaran::waktuBaris($b);
                        $kodeUnik = (int) $b->kode_unik;
                    @endphp
                    <tr class="{{ $i % 2 ? 'selang' : '' }}">
                        <td class="no">{{ $i + 1 }}</td>
                        <td>{{ $layananBaris }}</td>
                        <td class="samar">{{ $b->nomor ?: '—' }}</td>
                        <td>
                            <span class="tebal">{{ $b->nama_orang ?: 'Tanpa nama' }}</span>
                            @if ($b->affiliasi)
                                <br><span class="samar">{{ $b->affiliasi }}</span>
                            @endif
                        </td>
                        <td class="samar">
                            {{ $b->telp ?: '—' }}
                            @if ($b->email)
                                <br>{{ $b->email }}
                            @endif
                        </td>
                        {{-- Nomor angkatannya ikut: berkas ini yang dipakai
                             merekap, dan rekap yang menyebut "Scopus Camp
                             Yogyakarta" tanpa nomornya menggabungkan dua ratus
                             angkatan jadi satu baris. --}}
                        <td class="samar">{{ Pendaftaran::sesiUntukBerkas($b) ?? '—' }}</td>
                        <td class="tengah">{{ (int) $b->jumlah }}</td>
                        <td class="kanan nowrap">
                            <span class="tebal">Rp {{ number_format((int) $b->total, 0, ',', '.') }}</span>
                            @if ($kodeUnik > 0)
                                {{-- Kode uniknya ikut tercetak: inilah yang
                                     dicocokkan panitia dengan mutasi rekening,
                                     dan daftar cetak ini dipakai persis untuk
                                     itu. --}}
                                <br><span class="samar">kode {{ number_format($kodeUnik, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        {{-- Kode diskon yang dipakai pendaftaran ini.

                             Satu kolom untuk DUA hal yang terlihat sama di
                             mata pembacanya: kode yang diketik pendaftar
                             (SalamQ1, MEI DAY) dan potongan yang berlaku
                             sendiri — ALUMNI, ROMBONGAN, dan Promo Layanan.
                             Yang terakhir ditulis dengan huruf biasa supaya
                             tidak terbaca seperti kode yang bisa diketik
                             orang; kode sungguhan dibiarkan apa adanya sebab
                             besar-kecil hurufnya berarti.

                             Nominalnya ikut, karena daftar inilah yang
                             dipakai merekap dan "ALUMNI" tanpa angka tidak
                             menjawab berapa yang dipotong. --}}
                        <td class="samar">
                            @php
                                $kodeBaris = trim((string) ($b->kode_diskon ?? ''));
                                $potonganBaris = (int) ($b->nominal_diskon ?? 0);
                                $tulisKode = match (true) {
                                    $kodeBaris === '' => null,
                                    $kodeBaris === 'ALUMNI' => 'Alumni',
                                    $kodeBaris === 'ROMBONGAN' => 'Rombongan',
                                    $kodeBaris === 'PROMO' => 'Promo',
                                    str_starts_with($kodeBaris, 'PROMO: ') => 'Promo: ' . mb_substr($kodeBaris, 7),
                                    default => $kodeBaris,
                                };
                            @endphp

                            @if ($tulisKode === null)
                                —
                            @else
                                <span class="tebal">{{ $tulisKode }}</span>
                                @if ($potonganBaris > 0)
                                    <br>&minus;&nbsp;Rp {{ number_format($potonganBaris, 0, ',', '.') }}
                                @endif
                            @endif
                        </td>

                        <td>
                            <span class="lencana {{ $kelasLencana[$rupa['warna']] ?? 'l-abu' }}">{{ $rupa['label'] }}</span>
                            @if (! $buktiBaris['nilai'])
                                <br><span class="samar">bukti belum ada</span>
                            @elseif (! $buktiBaris['ada'])
                                <br><span class="samar">berkas bukti hilang</span>
                            @endif
                            @if ($waktuBaris)
                                <br><span class="samar nowrap">{{ $waktuBaris->format('d/m/Y') }}</span>
                            @endif
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
             * mengukur panjang penandanya dan tulisannya terlempar jauh ke kiri.
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
