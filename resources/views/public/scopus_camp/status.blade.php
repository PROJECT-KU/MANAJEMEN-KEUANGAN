@extends('public.layout.header')

@section('title', 'Status pendaftaran ' . $pendaftaran->id_transaksi . ' | Rumah Scopus')

@push('kepala')
{{-- Jangan diindeks: isinya keterangan satu orang.

     Ditulis sebagai meta di tumpukan 'kepala', BUKAN @section('robot'):
     tata letak publik ini tidak punya @yield untuk itu, jadi bagiannya akan
     diam-diam tidak digambar sama sekali dan halaman ini terindeks. --}}
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600..900&family=Figtree:wght@400;500;700;800&display=swap">
@endpush

@section('konten')
{{--
    Keadaan satu pendaftaran Scopus Camp.

    Halaman ini yang menampung orang dari TIGA arah: sesudah membayar di
    DOKU, lewat tautan di surat buktinya, dan saat tagihannya gagal dibuat.
    Jadi ia harus masuk akal dibaca dalam ketiga keadaan itu, termasuk yang
    paling buruk — tagihan tidak terbuat dan orangnya tidak tahu harus apa.

    Yang ditanyakan berkala BUKAN seluruh halaman melainkan satu alamat yang
    cuma memulangkan keadaannya. Memuat ulang seluruh halaman tiap beberapa
    detik di hosting ini pernah berakhir 503 — kuota prosesnya habis oleh
    poller, bukan oleh pengunjung.
--}}
@php
    $lunas = in_array((string) $pendaftaran->status,
        \App\Support\PendaftaranSemuaLayanan::KEADAAN['lunas']['nilai'], true);

    $batal = in_array((string) $pendaftaran->status,
        ['Pendaftaran Dibatalkan', 'Pendaftaran Ditolak', 'Pendaftaran Refund'], true);

    /*
     * Tombol bayarnya hanya digambar kalau tagihannya MEMANG masih bisa
     * dibayar: alamatnya ada, belum lunas, belum dibatalkan, dan tenggatnya
     * belum lewat. Tanpa pemeriksaan tenggat, yang membukanya besok menekan
     * tombol yang mengantarnya ke halaman DOKU bertuliskan "order has
     * expired" — dan menyalahkan dirinya sendiri.
     */
    $masihBisaBayar = $pendaftaran->bayar_url
        && ! $lunas
        && ! $batal
        && ($pendaftaran->kedaluwarsa_pada === null || $pendaftaran->kedaluwarsa_pada->isFuture());

    $wa = \App\Support\TautanWa::kirim(
        config('panitia.whatsapp'),
        'Halo, saya mau bertanya soal pendaftaran Scopus Camp nomor '
            . $pendaftaran->id_transaksi . '.'
    );

    /*
     * JADWAL DAN LOKASI acaranya, yang sebelumnya tidak disebut sama sekali
     * di halaman ini.
     *
     * Orangnya baru saja membayar lima juta untuk sebuah acara, dan halaman
     * yang memberitahunya "sudah lunas" tidak menyebutkan kapan maupun di
     * mana acaranya. Keterangan itu memang ada di suratnya — tetapi halaman
     * ini yang ia buka lagi saat mencarinya.
     */
    $fMulai = $kategori && $kategori->mulai ? \Carbon\Carbon::parse($kategori->mulai)->locale('id') : null;
    $fSelesai = $kategori && $kategori->selesai ? \Carbon\Carbon::parse($kategori->selesai) : null;

    // Peserta rombongannya; kosong untuk pendaftaran satu orang.
    $pesertaLain = $pendaftaran->pesertaLain;

    /*
     * Tautan grup peserta HANYA untuk yang sudah lunas.
     *
     * Grup itu isinya peserta yang sudah membayar. Memajangnya pada
     * pendaftaran yang belum lunas berarti siapa pun yang memegang tautan
     * halaman ini bisa masuk tanpa membayar.
     */
    $grup = $lunas && $kategori && trim((string) $kategori->group_wa) !== ''
        ? trim((string) $kategori->group_wa)
        : null;
@endphp
<style>
    .sts {
        --kertas: #fdf5e8;  --kertas-2: #f8ead4;
        --tinta: #16233f;   --tinta-2: #5c6b85;
        --jingga: #e76d20;  --jingga-muda: #fb8c3d; --jingga-tua: #ad4d12;
        --rumput: #0f766e;  --rumput-muda: #14a394;
        --garis: #e7dcc6;   --merah: #a3281c;

        background: var(--kertas); color: var(--tinta);
        font-family: 'Figtree', system-ui, sans-serif;
        font-size: 18px; padding: 108px 0 72px; min-height: 86vh;
        overflow-x: clip;
    }
    .sts h1, .sts h2 {
        font-family: 'Fraunces', Georgia, serif; font-weight: 800;
        color: var(--tinta); margin: 0; letter-spacing: -.01em;
    }
    .sts p { margin: 0; color: var(--tinta-2); line-height: 1.7; }
    .sts a { text-decoration: none; }

    .sts-bungkus { max-width: 660px; margin: 0 auto; }

    /* ====================================================== kepala keadaan */
    /* Pita warna di puncak kartunya: keadaannya terbaca sebelum satu huruf
       pun dibaca, dan itu yang dicari orang saat membuka halaman ini. */
    .sts-kotak {
        position: relative; overflow: hidden;
        background: #fff; border: 1px solid var(--garis); border-radius: 26px;
        box-shadow: 0 16px 40px rgba(22, 35, 63, .08);
    }
    .sts-kotak::before {
        content: ''; position: absolute; inset: 0 0 auto; height: 6px;
    }
    .sts-kotak.lunas::before  { background: linear-gradient(90deg, var(--rumput), var(--rumput-muda)); }
    .sts-kotak.tunggu::before { background: linear-gradient(90deg, var(--jingga-tua), var(--jingga-muda)); }
    .sts-kotak.batal::before  { background: linear-gradient(90deg, var(--merah), #d2695c); }

    .sts-kepala { padding: 40px 34px 30px; text-align: center; }

    /* Meterai bundar, bukan pil kecil.

       Keadaan "sudah lunas" adalah akhir yang baik dari rangkaian langkah
       yang panjang, dan lencana setinggi 32px tidak menyampaikan apa pun
       tentang itu. Di layar ponsel ia bahkan nyaris tidak terlihat. */
    .sts-meterai {
        display: inline-flex; align-items: center; justify-content: center;
        width: 92px; height: 92px; margin-bottom: 20px; border-radius: 50%;
        font-size: 2.4rem;
    }
    .sts-meterai.lunas  { background: #dcf3ee; color: var(--rumput); }

    /* ============================================ centang yang digambar */
    .sts-centang { width: 56px; height: 56px; overflow: visible; }
    .sts-centang-lingkar, .sts-centang-garis {
        fill: none; stroke: var(--rumput);
        stroke-linecap: round; stroke-linejoin: round;
    }
    .sts-centang-lingkar { stroke-width: 2.5; opacity: .35; }
    .sts-centang-garis { stroke-width: 4.5; }

    /*
     * Panjang garisnya DIHITUNG, bukan ditebak.
     *
     * Lingkaran r=23 -> keliling 2*pi*23 = 144,5. Centangnya dua ruas:
     * (14,5;26,5)->(22,5;34,5) sepanjang sqrt(8^2+8^2) = 11,3 dan
     * (22,5;34,5)->(37,5;18,5) sepanjang sqrt(15^2+16^2) = 21,9 — total 33,2.
     * Dibulatkan ke atas supaya ujungnya tidak tersisa sepotong: nilai yang
     * kurang panjang menyisakan garis yang tidak pernah selesai tergambar,
     * dan itu terbaca sebagai centang yang patah.
     */
    .sts-centang-lingkar { stroke-dasharray: 145; stroke-dashoffset: 145; }
    .sts-centang-garis   { stroke-dasharray: 34;  stroke-dashoffset: 34; }

    /*
     * BERULANG TERUS, bukan sekali saat halaman dibuka.
     *
     * Versi pertama memakai `forwards`: animasinya jalan satu kali lalu
     * berhenti di keadaan akhir, dan sesudah itu halaman diam. Diminta
     * pemiliknya supaya terus berjalan.
     *
     * Satu putaran 4 detik, dan pembagiannya disengaja: menggambar 0,9 detik,
     * lalu DIAM 2,5 detik, baru surut 0,6 detik. Tanpa jeda diam yang panjang
     * ia terbaca sebagai kedipan gelisah, bukan sebagai tanda yang
     * dibubuhkan berulang — dan pada halaman yang mengabarkan "sudah lunas",
     * gelisah adalah kesan yang paling tidak diinginkan.
     *
     * Persennya dihitung dari 4 detik itu: 14% = 0,56 dtk, 22% = 0,88 dtk,
     * 85% = 3,4 dtk.
     */
    .sts-meterai.lunas .sts-centang-lingkar {
        animation: sts-gambar-lingkar 4s ease-in-out infinite;
    }
    .sts-meterai.lunas .sts-centang-garis {
        /* Menunggu lingkarannya hampir tertutup dulu: dua garis yang tumbuh
           bersamaan terbaca sebagai gerak acak, bukan sebagai satu tanda
           yang sedang dibubuhkan. */
        animation: sts-gambar-garis 4s ease-in-out infinite;
    }

    @keyframes sts-gambar-lingkar {
        0%        { stroke-dashoffset: 145; }
        14%, 85%  { stroke-dashoffset: 0; }
        100%      { stroke-dashoffset: 145; }
    }
    @keyframes sts-gambar-garis {
        0%, 11%   { stroke-dashoffset: 34; }
        22%, 85%  { stroke-dashoffset: 0; }
        100%      { stroke-dashoffset: 34; }
    }

    /* Meterainya ikut muncul dengan sedikit lonjakan — tanpa itu
       lingkarannya tergambar di dalam kotak yang sudah diam, dan geraknya
       terasa tertempel. */
    .sts-meterai.lunas {
        /* Lonjakan masuknya tetap SEKALI: kotaknya sendiri tidak perlu ikut
           berdenyut — yang berulang isinya, dan kotak yang ikut membesar
           tiap empat detik membuat tulisan di bawahnya ikut bergeser. */
        animation: sts-muncul .45s cubic-bezier(.2, 1.3, .4, 1) both;
    }
    @keyframes sts-muncul {
        from { transform: scale(.6); opacity: 0; }
        to   { transform: scale(1);  opacity: 1; }
    }

    /* Gelombang ke luar, SEIRAMA dengan putaran centangnya — 4 detik yang
       sama, dan berangkat tepat saat centangnya selesai (22%). Dua irama
       yang berbeda panjang akan berpapasan di tempat yang berganti-ganti
       tiap putaran, dan itu terbaca sebagai dua animasi yang kebetulan
       bertabrakan. */
    .sts-meterai.lunas::after {
        content: ''; position: absolute; inset: 0; border-radius: 50%;
        border: 2px solid var(--rumput);
        animation: sts-gelombang 4s ease-out infinite;
    }
    @keyframes sts-gelombang {
        0%, 22%  { transform: scale(1);    opacity: .5; }
        45%      { transform: scale(1.45); opacity: 0; }
        100%     { transform: scale(1.45); opacity: 0; }
    }
    /* position agar ::after di atas punya acuan. */
    .sts-meterai { position: relative; }
    .sts-meterai.tunggu { background: #fff4d6; color: #8a6200; }
    .sts-meterai.batal  { background: #fbe4e0; color: var(--merah); }

    /* Lingkaran luarnya berdenyut HANYA selagi menunggu: gerakan pada
       halaman yang sudah selesai cuma menarik mata ke tempat yang tidak
       perlu dilihat lagi. */
    .sts-meterai.tunggu { box-shadow: 0 0 0 0 rgba(231, 109, 32, .45); animation: sts-denyut 2.2s infinite; }
    @keyframes sts-denyut {
        70%  { box-shadow: 0 0 0 18px rgba(231, 109, 32, 0); }
        100% { box-shadow: 0 0 0 0 rgba(231, 109, 32, 0); }
    }
    @media (prefers-reduced-motion: reduce) {
        /*
         * Dimatikan untuk yang memintanya di setelan sistemnya — TETAPI
         * keadaan akhirnya yang dipasang, bukan sekadar animasinya dicabut.
         *
         * Garis centangnya berangkat dari stroke-dashoffset penuh, jadi
         * "animation: none" saja membuatnya TIDAK PERNAH TERGAMBAR: yang
         * tampil lingkaran kosong tanpa centang, persis pada orang yang
         * paling tidak akan menyangka halamannya sedang beranimasi.
         */
        .sts-meterai.tunggu,
        .sts-meterai.lunas { animation: none; }
        .sts-meterai.lunas::after { animation: none; opacity: 0; }
        .sts-centang-lingkar, .sts-centang-garis { stroke-dashoffset: 0; animation: none; }
    }

    .sts-judul { font-size: clamp(1.55rem, 3.2vw, 2.1rem); line-height: 1.18; }
    .sts-kata { margin-top: 11px; font-size: 1.04rem; }

    /* ====================================================== kwitansi */
    /* Bentuk karcis, dipisahkan garis gigi.

       Isinya memang kwitansi: nomor, kelas, nama, dan nominal yang sudah
       dibayar. Bentuk yang mengakuinya membuat halaman ini terasa seperti
       bukti yang bisa disimpan, bukan sekadar pemberitahuan. */
    .sts-karcis {
        position: relative; margin: 0 24px 26px; padding: 26px 24px 24px;
        background: var(--kertas); border-radius: 18px;
    }
    .sts-karcis::before, .sts-karcis::after {
        content: ''; position: absolute; top: -13px;
        width: 26px; height: 26px; border-radius: 50%; background: #fff;
    }
    .sts-karcis::before { left: -13px; }
    .sts-karcis::after  { right: -13px; }

    /* ------------------------------------------- nomor pendaftaran */
    .sts-nomor-blok {
        text-align: center; padding-bottom: 20px; margin-bottom: 4px;
        border-bottom: 2px dashed var(--garis);
    }
    .sts-nomor-label {
        display: block; margin-bottom: 6px;
        font-size: .72rem; font-weight: 800; letter-spacing: .09em;
        text-transform: uppercase; color: var(--tinta-2);
    }
    .sts-nomor {
        display: block;
        font-family: 'Fraunces', Georgia, serif;
        font-size: clamp(1.25rem, 4.2vw, 1.55rem); font-weight: 900;
        color: var(--tinta); letter-spacing: .04em;
        /* Angka berjajar: nomor yang dibandingkan huruf demi huruf dengan
           yang di email harus sejajar lebarnya, dan Fraunces memakai angka
           gaya lama yang tingginya tidak seragam. */
        font-variant-numeric: lining-nums tabular-nums;
        /* Boleh dipenggal di layar sempit; nomornya satu kata panjang dan
           tanpa ini ia mendorong kwitansinya melebihi lebar layar. */
        overflow-wrap: anywhere;
    }

    /* ------------------------------------------- baris label & nilai */
    .sts-baris-daftar { margin: 0; }
    .sts-baris {
        display: flex; align-items: baseline; justify-content: space-between;
        gap: 18px; padding: 12px 0;
        border-bottom: 1px dashed var(--garis);
    }
    .sts-baris:last-child { border-bottom: 0; }
    .sts-baris dt {
        flex: 0 0 auto;
        font-size: .74rem; font-weight: 800; letter-spacing: .07em;
        text-transform: uppercase; color: var(--tinta-2);
    }
    .sts-baris dd {
        /* Rata KANAN, dan di situ inti rapinya: nilainya berjajar di satu
           tepi, jadi bisa dibaca menurun tanpa membaca labelnya sama sekali. */
        flex: 1 1 auto; min-width: 0; margin: 0; text-align: right;
        font-weight: 700; font-size: 1.02rem; color: var(--tinta);
        line-height: 1.45;
    }

    /* Nominal yang sudah dibayar dipisahkan garis dan dibesarkan: itu angka
       yang paling dicari saat kwitansi ini dibuka lagi. */
    .sts-total {
        display: flex; align-items: baseline; justify-content: space-between;
        gap: 14px; margin-top: 20px; padding-top: 18px;
        border-top: 2px dashed var(--garis);
    }
    .sts-total span {
        font-size: .74rem; font-weight: 800; letter-spacing: .07em;
        text-transform: uppercase; color: var(--tinta-2);
    }
    .sts-total b {
        font-family: 'Fraunces', Georgia, serif; font-size: 1.5rem; font-weight: 900;
        color: var(--jingga-tua); line-height: 1.1;
        font-variant-numeric: lining-nums tabular-nums;
    }
    .sts-kotak.lunas .sts-total b { color: var(--rumput); }

    /* ====================================================== peserta */
    .sts-bagian { margin: 0 24px 26px; }
    .sts-bagian-judul {
        display: flex; align-items: center; gap: 9px; margin-bottom: 14px;
        font-family: 'Fraunces', Georgia, serif; font-weight: 800; font-size: 1.1rem;
    }
    .sts-bagian-judul i { color: var(--jingga); }

    .sts-peserta { list-style: none; margin: 0; padding: 0; }
    .sts-peserta li {
        display: grid; grid-template-columns: 30px minmax(0, 1fr); gap: 12px;
        align-items: baseline; padding: 10px 0;
        border-bottom: 1px dashed var(--garis);
    }
    .sts-peserta li:last-child { border-bottom: 0; }
    .sts-peserta-nomor {
        display: flex; align-items: center; justify-content: center;
        width: 30px; height: 30px; border-radius: 50%;
        background: var(--kertas-2); color: var(--tinta);
        font-size: .84rem; font-weight: 800;
    }
    .sts-peserta-nama { font-weight: 700; color: var(--tinta); }
    .sts-peserta-sub { display: block; font-size: .88rem; color: var(--tinta-2); font-weight: 500; }
    .sts-peserta-utama {
        margin-left: 7px; padding: 2px 9px; border-radius: 999px;
        background: var(--kertas-2); color: var(--tinta-2);
        font-size: .72rem; font-weight: 800; white-space: nowrap;
    }

    /* ====================================================== langkah */
    .sts-langkah { list-style: none; margin: 0; padding: 0; counter-reset: langkah; }
    .sts-langkah li {
        display: grid; grid-template-columns: 28px minmax(0, 1fr); gap: 12px;
        align-items: start; padding: 9px 0;
        font-size: .97rem; line-height: 1.6; color: var(--tinta-2);
    }
    .sts-langkah li::before {
        counter-increment: langkah; content: counter(langkah);
        display: flex; align-items: center; justify-content: center;
        width: 28px; height: 28px; border-radius: 50%;
        background: var(--kertas-2); color: var(--tinta);
        font-size: .82rem; font-weight: 800;
    }
    .sts-langkah strong { color: var(--tinta); }

    /* ====================================================== tombol */
    .sts-aksi { padding: 0 24px 30px; }

    .sts-tombol {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        width: 100%; min-height: 60px; padding: 0 22px;
        border: 0; border-radius: 16px; cursor: pointer;
        background: linear-gradient(135deg, var(--jingga-muda), var(--jingga-tua));
        color: #fff; font-family: 'Fraunces', Georgia, serif;
        font-size: 1.1rem; font-weight: 800;
        box-shadow: 0 10px 24px rgba(231, 109, 32, .3);
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .sts-tombol:hover { transform: translateY(-2px); color: #fff; }
    .sts-tombol.hijau {
        background: linear-gradient(135deg, var(--rumput-muda), var(--rumput));
        box-shadow: 0 10px 24px rgba(15, 118, 110, .3);
    }

    .sts-tombol-polos {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        width: 100%; min-height: 54px; margin-top: 12px; padding: 0 20px;
        border: 2px solid var(--garis); border-radius: 15px;
        background: #fff; color: var(--tinta); font-weight: 800; font-size: 1rem;
    }
    .sts-tombol-polos:hover { border-color: var(--jingga); color: var(--jingga-tua); }

    .sts-catatan {
        display: flex; align-items: flex-start; gap: 10px; margin-top: 16px;
        font-size: .92rem; line-height: 1.6; color: var(--tinta-2);
    }
    .sts-catatan i { color: var(--jingga); margin-top: 3px; }

    /* Tautan kembali DI LUAR kartunya: ia jalan keluar, bukan salah satu
       tindakan pada pendaftaran ini. Disamakan weight-nya dengan tautan
       "kembali" di halaman borang. */
    .sts-balik {
        display: inline-flex; align-items: center; gap: 8px;
        margin-top: 22px; padding: 11px 0;
        color: var(--tinta-2); font-weight: 700; font-size: .95rem;
        transition: gap .2s ease, color .2s ease;
    }
    .sts-balik:hover { color: var(--jingga-tua); gap: 13px; }

    @media (max-width: 575.98px) {
        .sts { font-size: 17px; padding-top: 96px; }
        .sts-kotak { border-radius: 20px; }
        .sts-kepala { padding: 32px 20px 24px; }
        .sts-meterai { width: 76px; height: 76px; font-size: 2rem; }
        .sts-karcis, .sts-bagian { margin-left: 16px; margin-right: 16px; }
        .sts-aksi { padding-left: 16px; padding-right: 16px; }
        /*
         * Di bawah 576px label dan nilainya BERTUMPUK, bukan berhadapan.
         *
         * Nilai terpanjangnya "30 Oktober – 1 November 2026"; berhadapan
         * dengan labelnya di layar selebar 360px ia tersisa 160px dan pecah
         * jadi tiga baris di sebelah label satu baris — persis bentuk yang
         * hendak dihindari. Bertumpuk, tiap keterangan tetap utuh satu baris.
         */
        .sts-baris { display: block; }
        .sts-baris dt { display: block; margin-bottom: 3px; }
        .sts-baris dd { text-align: left; }
    }
</style>

<section class="sts" data-keadaan="{{ route('public.scopuscamp.keadaan', $pendaftaran->id) }}"
    data-selesai="{{ $lunas || $batal ? '1' : '0' }}">
    <div class="container">
        <div class="sts-bungkus">
            <div class="sts-kotak {{ $lunas ? 'lunas' : ($batal ? 'batal' : 'tunggu') }}">

                <div class="sts-kepala">
                    @if ($lunas)
                        {{-- CENTANGNYA SVG, bukan ikon huruf.

                             Ikon Font Awesome adalah glif — ia hanya bisa
                             dibesarkan dan diputar. Yang menyenangkan dilihat
                             di layar "sudah lunas" adalah centang yang
                             DIGAMBAR: garisnya muncul dari pangkal ke ujung,
                             dan itu menuntut stroke-dasharray pada path
                             sungguhan.

                             aria-hidden: artinya sudah disampaikan judul di
                             bawahnya, dan pembaca layar tidak perlu mendengar
                             "gambar" dua kali. --}}
                        <span class="sts-meterai lunas">
                            <svg class="sts-centang" viewBox="0 0 52 52" aria-hidden="true" focusable="false">
                                <circle class="sts-centang-lingkar" cx="26" cy="26" r="23" />
                                <path class="sts-centang-garis" d="M14.5 26.5 L22.5 34.5 L37.5 18.5" />
                            </svg>
                        </span>
                        <h1 class="sts-judul">Pendaftaran Anda sudah lunas</h1>
                        <p class="sts-kata">
                            Terima kasih — kursi Anda sudah pasti. Keterangan lengkapnya
                            juga kami kirim ke <strong>{{ $pendaftaran->email }}</strong>.
                        </p>
                    @elseif ($batal)
                        <span class="sts-meterai batal"><i class="fas fa-times"></i></span>
                        <h1 class="sts-judul">Pendaftaran ini sudah tidak berjalan</h1>
                        <p class="sts-kata">
                            Statusnya sekarang <strong>{{ $pendaftaran->status }}</strong>.
                            Kalau menurut Anda ini keliru, hubungi panitia dengan menyebut
                            nomor pendaftaran di bawah.
                        </p>
                    @else
                        <span class="sts-meterai tunggu"><i class="fas fa-hourglass-half"></i></span>
                        <h1 class="sts-judul">Tinggal satu langkah lagi</h1>
                        <p class="sts-kata">
                            Pendaftaran Anda sudah tersimpan dan kursinya ditahan.
                            Yang tersisa hanya menyelesaikan pembayaran.
                        </p>
                    @endif
                </div>

                <div class="sts-karcis">

                    {{-- NOMOR PENDAFTARAN berdiri sendiri di puncak kwitansi.

                         Ia bukan salah satu keterangan di antara keterangan
                         lain: ia satu-satunya yang harus DISEBUT ULANG orangnya
                         saat menghubungi panitia. Di dalam deret label-nilai
                         ia tenggelam di antara tanggal dan lokasi. --}}
                    <div class="sts-nomor-blok">
                        <span class="sts-nomor-label">Nomor pendaftaran</span>
                        <span class="sts-nomor">{{ $pendaftaran->id_transaksi }}</span>
                    </div>

                    {{-- Label KIRI, nilai KANAN — bentuk kwitansi, bukan kisi
                         rata kiri.

                         Deret label-di-atas-nilai rata kiri membuat mata harus
                         melompat dua kali untuk tiap keterangan: turun ke
                         nilainya, lalu balik ke atas untuk label berikutnya.
                         Dengan label dan nilai berhadapan di satu baris,
                         nilainya berjajar rapi di satu tepi dan bisa dibaca
                         menurun tanpa membaca labelnya sama sekali. --}}
                    <dl class="sts-baris-daftar">
                        <div class="sts-baris">
                            <dt>Kelas</dt>
                            <dd>
                                @if ($kategori)
                                    {{ $kategori->nama }}@if ($kategori->nama_ke) #{{ $kategori->nama_ke }}@endif
                                @else
                                    {{-- Angkatannya sudah dihapus panitia; barisnya tetap
                                         ada, jadi halaman ini tidak boleh 500 karenanya. --}}
                                    Keterangan kelasnya tidak tersedia lagi
                                @endif
                            </dd>
                        </div>

                        @if ($fMulai)
                            <div class="sts-baris">
                                <dt>Tanggal</dt>
                                <dd>{{ \App\Support\RentangTanggal::tulis($fMulai, $fSelesai) }}</dd>
                            </div>
                        @endif

                        @if ($kategori && $kategori->lokasi)
                            <div class="sts-baris">
                                <dt>Lokasi</dt>
                                <dd>{{ $kategori->lokasi }}</dd>
                            </div>
                        @endif

                        <div class="sts-baris">
                            <dt>Atas nama</dt>
                            <dd>{{ $pendaftaran->nama }}</dd>
                        </div>

                        <div class="sts-baris">
                            <dt>Jumlah peserta</dt>
                            <dd>{{ (int) $pendaftaran->jumlah_pendaftar }} orang</dd>
                        </div>

                        @if ($pendaftaran->bayar_kanal)
                            <div class="sts-baris">
                                <dt>Dibayar lewat</dt>
                                <dd>{{ $pendaftaran->bayar_kanal }}</dd>
                            </div>
                        @endif
                    </dl>

                    <div class="sts-total">
                        <span>{{ $lunas ? 'Sudah dibayar' : 'Total bayar' }}</span>
                        <b>Rp {{ number_format((int) $pendaftaran->total_pembayaran, 0, ',', '.') }}</b>
                    </div>
                </div>

                {{-- DAFTAR PESERTA, dan halaman ini belum pernah memuatnya.

                     Ini satu-satunya tempat pendaftar bisa MEMERIKSA siapa saja
                     yang ia daftarkan. Tanpa itu, salah ketik nama baru ketahuan
                     di sertifikat — saat sudah terlambat diperbaiki. --}}
                @if ($pesertaLain->isNotEmpty())
                    <div class="sts-bagian">
                        <h2 class="sts-bagian-judul"><i class="fas fa-users"></i> Peserta yang didaftarkan</h2>

                        <ul class="sts-peserta">
                            <li>
                                <span class="sts-peserta-nomor">1</span>
                                <span>
                                    <span class="sts-peserta-nama">{{ $pendaftaran->nama }}</span>
                                    <span class="sts-peserta-utama">pendaftar</span>
                                    <span class="sts-peserta-sub">{{ $pendaftaran->email }}</span>
                                </span>
                            </li>
                            @foreach ($pesertaLain as $i => $orang)
                                <li>
                                    <span class="sts-peserta-nomor">{{ $i + 2 }}</span>
                                    <span>
                                        <span class="sts-peserta-nama">{{ $orang->nama }}</span>
                                        @if ($orang->telp)
                                            <span class="sts-peserta-sub">{{ \App\Support\NomorTelepon::tampil($orang->telp) }}</span>
                                        @else
                                            <span class="sts-peserta-sub">Nomornya belum diisi</span>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>

                        @if ($wa !== '')
                            <p class="sts-catatan" style="margin-top: 14px;">
                                <i class="fas fa-pen"></i>
                                <span>Ada nama atau nomor yang keliru? Beri tahu panitia lewat
                                    WhatsApp, sebut nomor pendaftarannya.</span>
                            </p>
                        @endif
                    </div>
                @endif

                {{-- LANGKAH BERIKUTNYA.

                     Yang sudah membayar tidak tahu harus menunggu apa, dan
                     yang belum membayar tidak tahu apa yang terjadi sesudahnya.
                     Keduanya berakhir di WhatsApp panitia menanyakan hal yang
                     sama berulang-ulang. --}}
                @unless ($batal)
                    <div class="sts-bagian">
                        {{-- fa-clipboard-list, dan ikon ini bukan pilihan bebas.

                             Yang terpasang Font Awesome Free 5.5.0. Nama gaya
                             FA6 seperti fa-list-check TIDAK membuat halaman
                             galat — ikonnya sekadar tidak tergambar, lebarnya
                             nol, dan judulnya jadi menempel ke tepi. Terjadi
                             persis begitu di halaman ini sebelum dibetulkan. --}}
                        <h2 class="sts-bagian-judul"><i class="fas fa-clipboard-list"></i> Setelah ini</h2>

                        <ol class="sts-langkah">
                            @if ($lunas)
                                <li><span><strong>Simpan nomor pendaftaran</strong> di atas —
                                    itu yang disebut saat menghubungi panitia.</span></li>
                                <li><span>Masuk ke <strong>grup peserta</strong> lewat tombol di
                                    bawah. Pengumuman teknis dibagikan di sana.</span></li>
                                <li><span>Datang sesuai tanggal dan lokasi di kwitansi ini.
                                    Panitia mengabari lagi menjelang hari-H.</span></li>
                            @else
                                <li><span><strong>Selesaikan pembayaran</strong> lewat tombol di
                                    bawah — QRIS atau Virtual Account.</span></li>
                                <li><span>Halaman ini <strong>berubah sendiri</strong> begitu
                                    pembayaran Anda masuk. Tidak perlu mengirim bukti transfer.</span></li>
                                <li><span>Tautan grup peserta muncul di sini, dan juga dikirim
                                    ke email Anda.</span></li>
                            @endif
                        </ol>
                    </div>
                @endunless

                <div class="sts-aksi">
                    @if ($masihBisaBayar)
                        <a href="{{ $pendaftaran->bayar_url }}" class="sts-tombol">
                            <i class="fas fa-credit-card"></i> Bayar sekarang
                        </a>
                        <div class="sts-catatan">
                            <i class="fas fa-info-circle"></i>
                            <span>Begitu pembayaran Anda masuk, halaman ini berubah sendiri —
                                tidak perlu mengirim bukti transfer.</span>
                        </div>
                    @elseif (! $lunas && ! $batal)
                        <div class="sts-catatan" style="margin-top: 0;">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Halaman pembayarannya sudah tidak berlaku. Hubungi panitia
                                lewat WhatsApp dengan menyebut nomor pendaftaran di atas,
                                dan tagihannya akan dibuatkan lagi.</span>
                        </div>
                    @endif

                    @if ($grup)
                        <a href="{{ $grup }}" target="_blank" rel="noopener"
                            class="sts-tombol hijau">
                            <i class="fab fa-whatsapp"></i> Masuk grup peserta
                        </a>
                    @endif

                    @if ($wa !== '')
                        <a href="{{ $wa }}" target="_blank" rel="noopener" class="sts-tombol-polos">
                            <i class="fab fa-whatsapp"></i> Tanya panitia
                        </a>
                    @endif
                </div>

            </div>

            <a href="{{ route('public.scopuscamp.index') }}" class="sts-balik">
                <i class="fas fa-arrow-left"></i> Kembali ke daftar Scopus Camp
            </a>
        </div>
    </div>
</section>

<script>
    (function () {
        var wadah = document.querySelector('.sts');

        // Sudah selesai: tidak ada yang perlu ditunggu, jadi tidak ada yang
        // perlu ditanyakan.
        if (! wadah || wadah.dataset.selesai === '1') {
            return;
        }

        var alamat = wadah.dataset.keadaan;
        var sisa = 40;   // 40 x 5 detik = sekitar 3 menit, lalu berhenti sendiri

        /*
         * Berhenti sendiri, dan berhenti saat tabnya tidak terlihat.
         *
         * Poller yang jalan selamanya di tiap tab yang lupa ditutup adalah
         * sebab 503 di hosting ini: kuota prosesnya habis oleh permintaan
         * yang tidak ada yang membaca jawabannya.
         */
        var jam = setInterval(function () {
            if (document.hidden) {
                return;
            }

            if (--sisa <= 0) {
                clearInterval(jam);
                return;
            }

            fetch(alamat, { headers: { 'Accept': 'application/json' } })
                .then(function (jawab) { return jawab.ok ? jawab.json() : null; })
                .then(function (data) {
                    if (data && data.selesai) {
                        clearInterval(jam);
                        window.location.reload();
                    }
                })
                .catch(function () { /* jaringan putus sesaat; dicoba lagi nanti */ });
        }, 5000);
    })();
</script>
@stop
