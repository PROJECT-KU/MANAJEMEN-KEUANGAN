@extends('public.layout.header')

@section('title')
Beranda | Rumah Scopus
@stop

@push('kepala')
{{-- Fraunces untuk judul, Figtree untuk teks.

     BUKAN Poppins, dan itu disengaja. Poppins adalah huruf bawaan hampir
     setiap templat gratis; halaman yang memakainya langsung terbaca sebagai
     "templat", bukan sebagai milik seseorang. Fraunces punya sumbu SOFT dan
     WONK yang membuat lengkungnya sedikit tidak rata — persis yang membuat
     judul terasa digambar tangan, bukan dicetak mesin.

     Sumbu optical size (opsz) dipakai di judul besar: pada ukuran besar
     Fraunces menipiskan kaitnya sendiri, dan tanpa itu judulnya terlihat
     gemuk. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600..900&family=Figtree:wght@400;500;700;800&display=swap">
@endpush

@section('konten')
{{--
    Beranda Rumah Scopus — rombakan kedua, 18 Okt 2026.

    Rombakan pertama rapi tetapi terbaca seperti halaman buatan mesin: kartu
    putih seragam bersudut 18px, kisi simetris sempurna, Poppins, dan aksen
    jingga di atas putih. Itu bentuk yang sama persis dengan ribuan halaman
    lain, dan pemiliknya menyebutnya apa adanya.

    Yang dikejar sekarang: HALAMAN YANG TERLIHAT DIBUAT ORANG.

      - Warnanya kertas dan tinta, bukan putih dan jingga. Latar krem hangat
        bertekstur, tulisan biru tinta pekat, stabilo kuning sebagai penanda
        — dunia rupa penerbitan ilmiah, bukan dunia rupa SaaS.

      - Bentuknya tidak seragam. Kartu alasan memakai kisi bento dengan satu
        kartu besar; kartu layanan berbentuk KARCIS dengan takik di tepinya;
        yang dipelajari ditulis di kertas tempel yang miring-miring.

      - Ada yang bergerak terus, bukan cuma muncul sekali saat digulung:
        pita berjalan, kata yang berganti sendiri di judul, gelembung yang
        mengapung, dan cap yang berputar pelan.

      - Bayangannya PEJAL, bukan kabur. Bayangan kabur adalah tanda paling
        khas antarmuka cetakan; bayangan pejal terbaca seperti sablon.

    Bahasanya sengaja TIDAK formal, atas permintaan pemiliknya: memakai
    "kamu", kalimat pendek, kata sehari-hari. Kalau nanti dirasa terlalu
    santai untuk dosen senior, yang perlu diganti cuma kata sapaannya.
--}}

<style>
    /* ============================================================ dasar */
    .bnd {
        /* --- kertas & tinta ---------------------------------------- */
        --kertas: #fbf5e9;
        --kertas-2: #f5ecd9;
        --tinta: #16233f;
        --tinta-2: #5c6b85;

        /* Stabilo. Satu-satunya warna yang boleh menimpa tulisan, dan
           hanya untuk menandai — persis seperti stabilo sungguhan. */
        --stabilo: #ffd84d;

        /* Empat warna penanda. Dipilih yang tidak satu pun bertetangga di
           roda warna, supaya enam kartu berjajar tidak terbaca sebagai
           satu gradasi. */
        --bata: #d9442b;
        --rumput: #0f766e;
        --ungu: #5b3fd6;
        --langit: #1668c9;

        --garis: #e3d9c4;

        position: relative;
        background: var(--kertas);
        color: var(--tinta);
        font-family: 'Figtree', system-ui, sans-serif;
        overflow-x: clip;
    }

    /* Butiran kertas, dibuat dari feTurbulence sebagai SVG sebaris —
       beberapa ratus bait, tajam di resolusi berapa pun, dan tidak perlu
       satu pun berkas gambar yang harus ikut terdeploy.

       Tanpa ini latar krem rata terbaca sebagai "warna latar", bukan
       sebagai kertas. */
    .bnd::before {
        content: ""; position: absolute; inset: 0; z-index: 0;
        pointer-events: none; opacity: .5;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='3'/%3E%3C/filter%3E%3Crect width='180' height='180' filter='url(%23n)' opacity='.055'/%3E%3C/svg%3E");
    }

    .bnd > * { position: relative; z-index: 1; }

    .bnd h1, .bnd h2, .bnd h3 {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 800; color: var(--tinta); margin: 0;
        font-variation-settings: 'SOFT' 20, 'WONK' 1;
        letter-spacing: -.02em;
        text-wrap: balance;
    }

    .bnd p { margin: 0; color: var(--tinta-2); line-height: 1.65; text-wrap: pretty; }

    .bnd-bagian { padding: 86px 0; }

    /* Kepala situsnya `fixed-top` setinggi 80px. Tanpa ini, menekan "Lihat
       kelasnya" menggulung halaman sampai tepi atas bagiannya — tepat di
       belakang kepala situs — dan stiker beserta judulnya hilang di balik
       menu. Terlihat persis begitu saat dipotret pada posisi gulir itu. */
    #layanan { scroll-margin-top: 96px; }

    /* ------------------------------------------------- stabilo & stiker */
    /* Sapuan stabilo di belakang satu frasa. Miring sedikit dan tepinya
       tidak rata — stabilo sungguhan tidak pernah berhenti tepat di ujung
       huruf. Digambar DI BELAKANG tulisan lewat z-index negatif supaya
       tulisannya tetap pekat dan terbaca. */
    .bnd-stabilo { position: relative; display: inline-block; }
    .bnd-stabilo::after {
        content: ""; position: absolute; z-index: -1;
        left: -.18em; right: -.24em; bottom: .04em; height: .52em;
        background: var(--stabilo);
        border-radius: 3px 10px 6px 12px;
        transform-origin: left center;
        /* Disapukan dari kiri ke kanan, seperti orang memegang stabilo. */
        animation: bnd-sapu .62s cubic-bezier(.2, .8, .3, 1) .35s both;
    }
    @keyframes bnd-sapu {
        from { transform: rotate(-1.2deg) skewX(-9deg) scaleX(0); }
        to   { transform: rotate(-1.2deg) skewX(-9deg) scaleX(1); }
    }

    /* Stiker miring, dipakai di tiap kepala bagian. Kemiringannya kecil
       dan tetap: stiker yang ditempel tangan tidak pernah sejajar, tetapi
       juga tidak berpindah-pindah tiap halaman dimuat. */
    .bnd-stiker {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 8px 15px; border-radius: 999px;
        background: var(--tinta); color: var(--kertas);
        font-size: .78rem; font-weight: 800; letter-spacing: .04em;
        transform: rotate(-2deg);
        box-shadow: 3px 3px 0 rgba(22, 35, 63, .18);
    }
    .bnd-stiker i { color: var(--stabilo); }

    /* --------------------------------------------------------- tombol */
    .bnd-tombol {
        display: inline-flex; align-items: center; gap: 10px;
        padding: 15px 28px; border: 2px solid var(--tinta); border-radius: 999px;
        font-family: 'Figtree', sans-serif; font-weight: 800; font-size: 1rem;
        text-decoration: none; cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .bnd-tombol-utama {
        background: var(--bata); color: #fff;
        box-shadow: 5px 5px 0 var(--tinta);
    }
    .bnd-tombol-utama:hover {
        color: #fff; transform: translate(-2px, -2px);
        box-shadow: 8px 8px 0 var(--tinta);
    }
    .bnd-tombol-kedua {
        background: var(--kertas); color: var(--tinta);
        box-shadow: 5px 5px 0 rgba(22, 35, 63, .22);
    }
    .bnd-tombol-kedua:hover {
        color: var(--tinta); transform: translate(-2px, -2px);
        box-shadow: 8px 8px 0 rgba(22, 35, 63, .22);
    }

    /* ============================================================= hero */
    /* Bawahnya 70 jadi 44: pita berjalan di bawahnya adalah PENUTUP hero,
       bukan bagian yang berdiri sendiri. Dengan 70px di atas dan 86px dari
       bagian berikutnya, pitanya terbaca mengapung di antara dua ruang
       kosong. */
    .bnd-hero { padding: 124px 0 44px; }

    /* 1.08fr : 0.92fr, bukan 1 : 1. Kisi yang persis seimbang membuat kedua
       sisinya saling bersaing; sedikit tidak seimbang memberi tahu mata
       sisi mana yang dibaca lebih dulu. */
    /* Lajur fotonya dipatok LEBARNYA, bukan pecahan.

       Dulu .92fr, jadi bingkainya ikut melebar bersama layar: terukur
       607x751 di layar 1440, sementara kolom teks di sebelahnya cuma 478
       tinggi. Fotonya 273px lebih tinggi daripada tulisan yang seharusnya
       dibaca lebih dulu, dan yang terbaca "ini halaman foto", bukan "ini
       halaman tawaran".

       Dipatok di JALURNYA, bukan lewat max-width pada kotak fotonya: pernah
       dicoba begitu dengan margin-left:auto, dan itu membatalkan peregangan
       item grid-nya — seluruh isi kotak itu berposisi mutlak, jadi lebarnya
       mengerut ke isi yang tersisa, yaitu 7px. */
    .bnd-hero-kisi {
        display: grid; grid-template-columns: minmax(0, 1fr) 440px;
        gap: 56px; align-items: center;
    }

    .bnd-hero h1 {
        margin-top: 20px;
        font-size: 3.4rem; line-height: 1.06;
        font-variation-settings: 'opsz' 120, 'SOFT' 20, 'WONK' 1;
    }

    /* Kata yang berganti sendiri. Ketiganya ditumpuk di petak kisi yang
       SAMA, jadi lebar kotaknya mengikuti kata terpanjang dan baris di
       bawahnya tidak melompat tiap kali katanya berganti. */
    .bnd-putar { display: inline-grid; vertical-align: baseline; text-align: left; }
    .bnd-putar > span {
        grid-area: 1 / 1;
        color: var(--bata); opacity: 0;
        animation: bnd-ganti 10.5s infinite;
    }
    .bnd-putar > span:nth-child(2) { animation-delay: 3.5s; }
    .bnd-putar > span:nth-child(3) { animation-delay: 7s; }
    @keyframes bnd-ganti {
        0%        { opacity: 0; translate: 0 14px; }
        4%, 30%   { opacity: 1; translate: 0 0; }
        34%, 100% { opacity: 0; translate: 0 -14px; }
    }

    .bnd-hero p { margin-top: 20px; font-size: 1.12rem; max-width: 46ch; }

    .bnd-aksi { display: flex; flex-wrap: wrap; gap: 16px; margin-top: 32px; }

    .bnd-centang { display: flex; flex-wrap: wrap; gap: 8px 22px; margin-top: 30px; }
    .bnd-centang span {
        display: inline-flex; align-items: center; gap: 8px;
        font-size: .92rem; font-weight: 600; color: var(--tinta);
    }
    .bnd-centang i { color: var(--rumput); }

    /* ------------------------------------------------------ foto hero */
    /* Lebarnya DIBATASI, dan batasnya dipasang di sini — bukan di
       bingkainya — supaya selotip dan gelembung angka yang berpatokan ke
       kotak ini ikut bergeser bersamanya.

       Tanpa batas ini, bingkainya memakai seluruh lebar lajurnya: terukur
       607x751 di layar 1440, sementara kolom teks di sebelahnya cuma 478
       tinggi. Fotonya 273px lebih tinggi daripada tulisan yang seharusnya
       dibaca lebih dulu, dan yang terbaca "ini halaman foto", bukan
       "ini halaman tawaran". */
    .bnd-foto { position: relative; }

    /* Bingkai LENGKUNG, bukan persegi membulat. Persegi membulat adalah
       bentuk bawaan setiap kartu di internet; lengkung di atas terbaca
       seperti jendela, dan langsung memberi tahu bahwa ada orang yang
       memilihkan bentuknya. */
    /* Bentuk RUMAH: atapnya melengkung, dindingnya lurus. Fotonya mengisi
       bentuk ini sepenuhnya lewat object-fit: cover — bukan menyesuaikan
       bentuknya ke foto, sebab tujuh belas fotonya punya enam perbandingan
       sisi yang berbeda dan bingkainya tidak boleh ikut berubah tiap kali
       slidernya berpindah. */
    .bnd-foto-bingkai {
        position: relative; overflow: hidden;
        aspect-ratio: 4 / 5;
        border: 3px solid var(--tinta);
        border-radius: 200px 200px 22px 22px;
        box-shadow: 10px 10px 0 var(--stabilo);
        transform: rotate(-1.4deg);
        background: var(--kertas-2);
    }

    .bnd-slide {
        position: absolute; inset: 0;
        opacity: 0; visibility: hidden;
        transition: opacity .7s ease, visibility .7s;
    }
    .bnd-slide-aktif { opacity: 1; visibility: visible; }
    .bnd-slide img { width: 100%; height: 100%; object-fit: cover; display: block; }

    /* Lapisan tinta tipis di bagian bawah foto. Pengatur slidernya duduk di
       atas foto yang isinya tidak bisa ditebak — tanpa lapisan ini, tombol
       dan angkanya hilang begitu kebetulan jatuh di atas bagian yang
       terang. */
    .bnd-geser-kaki {
        position: absolute; left: 0; right: 0; bottom: 0; z-index: 2;
        display: flex; align-items: center; gap: 12px;
        padding: 42px 18px 16px;
        background: linear-gradient(to top, rgba(22, 35, 63, .78), transparent);
    }

    .bnd-geser-tombol {
        display: inline-flex; align-items: center; justify-content: center;
        width: 36px; height: 36px; flex: 0 0 auto;
        border: 2px solid var(--kertas); border-radius: 50%;
        background: rgba(22, 35, 63, .55); color: var(--kertas);
        font-size: .95rem; cursor: pointer; padding: 0;
        transition: background .18s ease, color .18s ease;
    }
    .bnd-geser-tombol:hover { background: var(--stabilo); color: var(--tinta); border-color: var(--stabilo); }

    .bnd-geser-hitung {
        margin-left: auto;
        font-family: 'Fraunces', serif; font-weight: 700; font-size: .92rem;
        color: var(--kertas); font-variant-numeric: tabular-nums;
    }

    /* Garis kemajuan tipis di tepi paling bawah bingkai: memberi tahu
       slidernya memang berjalan sendiri, tanpa menambah satu pun kata. */
    .bnd-geser-bar {
        position: absolute; left: 0; right: 0; bottom: 0; z-index: 3;
        height: 4px; background: rgba(251, 245, 233, .22);
    }
    .bnd-geser-bar > i {
        display: block; height: 100%; width: 0;
        background: var(--stabilo);
    }
    .bnd-geser-bar-jalan > i { animation: bnd-maju var(--lama, 5s) linear forwards; }
    @keyframes bnd-maju { from { width: 0; } to { width: 100%; } }

    /* Selotip di dua pojok atas — tanda paling cepat dibaca bahwa sesuatu
       ditempel tangan. */
    .bnd-selotip {
        position: absolute; z-index: 3;
        width: 86px; height: 26px;
        background: rgba(255, 216, 77, .72);
        border-left: 1px dashed rgba(22, 35, 63, .18);
        border-right: 1px dashed rgba(22, 35, 63, .18);
    }
    .bnd-selotip-kiri { top: 10px; left: -14px; transform: rotate(-32deg); }
    .bnd-selotip-kanan { top: 10px; right: -14px; transform: rotate(32deg); }

    /* Dua gelembung angka mengapung di tepi foto: membawa bukti ke layar
       pertama, tanpa menunggu orangnya menggulung sampai pita angka. */
    .bnd-gelembung {
        position: absolute; z-index: 4;
        display: flex; align-items: center; gap: 11px;
        padding: 12px 16px; border-radius: 16px;
        background: var(--kertas); border: 2px solid var(--tinta);
        box-shadow: 4px 4px 0 rgba(22, 35, 63, .2);
        animation: bnd-apung 5.4s ease-in-out infinite;
    }
    .bnd-gelembung strong {
        display: block; line-height: 1;
        font-family: 'Fraunces', serif; font-size: 1.3rem;
    }
    .bnd-gelembung small { font-size: .74rem; color: var(--tinta-2); font-weight: 600; }
    .bnd-gelembung i { font-size: 1.1rem; }

    .bnd-gelembung-1 { left: -32px; top: 20%; rotate: -3deg; }
    .bnd-gelembung-2 { right: -24px; bottom: 15%; rotate: 2.5deg; animation-delay: 1.8s; }

    @keyframes bnd-apung {
        0%, 100% { translate: 0 0; }
        50%      { translate: 0 -13px; }
    }

    /* ========================================================== rute */
    /* Menggantikan pita berjalan yang dibuang 18 Okt 2026.

       Pitanya cuma hiasan: delapan kata kunci yang berlari, tidak menjawab
       satu pun pertanyaan. Padahal pertanyaan pengunjung sesudah membaca
       hero selalu sama — "terus gimana caranya?" — dan halaman ini tidak
       pernah menjawabnya di bagian mana pun.

       Bentuknya JALUR, bukan empat kartu. Empat kartu setara membuat mata
       membacanya sebagai pilihan yang bisa diambil salah satu; rel
       putus-putus dengan empat perhentian bernomor terbaca sebagai urutan
       yang harus dilewati semua — dan urutan itulah seluruh isinya.

       Tetap di pita tinta: ia memisahkan hero dari bagian berikutnya, peran
       yang dulu dipegang pita berjalan. */
    .bnd-rute {
        background: var(--tinta);
        padding: 46px 0 42px;
    }

    .bnd-rute-deret {
        position: relative;
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px;
    }

    /* Rel putus-putus, digambar di lapisan paling belakang. Tepi kiri dan
       kanannya disisakan supaya relnya tidak menjulur keluar dari
       perhentian pertama dan terakhir. */
    .bnd-rute-deret::before {
        content: ""; position: absolute; z-index: 0;
        top: 26px; left: 12%; right: 12%;
        border-top: 2px dashed rgba(251, 245, 233, .3);
    }

    .bnd-rute-henti {
        position: relative; z-index: 1; text-align: center;
    }

    .bnd-rute-nomor {
        display: inline-flex; align-items: center; justify-content: center;
        width: 54px; height: 54px; border-radius: 50%; margin-bottom: 16px;
        background: var(--tinta);
        border: 2px solid rgba(251, 245, 233, .34);
        color: var(--stabilo);
        font-family: 'Fraunces', serif; font-weight: 800; font-size: 1.3rem;
        line-height: 1;
        transition: background .22s ease, color .22s ease, border-color .22s ease, scale .22s ease;
    }
    /* Latarnya PEKAT, bukan tembus pandang: ia harus menutupi rel yang
       lewat persis di belakangnya. */
    .bnd-rute-henti:hover .bnd-rute-nomor {
        background: var(--stabilo); color: var(--tinta);
        border-color: var(--stabilo); scale: 1.08;
    }

    .bnd-rute-henti h3 { color: var(--kertas); font-size: 1.08rem; margin-bottom: 7px; }
    .bnd-rute-henti p {
        color: rgba(251, 245, 233, .7); font-size: .9rem;
        max-width: 30ch; margin: 0 auto;
    }

    /* ==================================================== kepala bagian */
    .bnd-kepala { margin-bottom: 44px; max-width: 62ch; }
    .bnd-kepala-tengah { margin-left: auto; margin-right: auto; text-align: center; }

    .bnd-judul {
        margin-top: 16px;
        font-size: 2.6rem; line-height: 1.12;
        font-variation-settings: 'opsz' 72, 'SOFT' 20, 'WONK' 1;
    }

    /* `.bnd p.bnd-sub`, bukan `.bnd-sub`.

       Aturan `.bnd p { margin: 0 }` di atas berbobot (0,1,1); selektor kelas
       tunggal hanya (0,1,0) dan KALAH — margin-top-nya tidak akan pernah
       berlaku. Pernah terjadi persis begitu pada rombakan pertama: terukur
       0px di keempat kepala bagian, dan gejalanya hanya terlihat di ponsel
       tempat judul dua baris menempel pada kalimat penjelasnya. */
    .bnd p.bnd-sub { margin-top: 15px; font-size: 1.06rem; }

    /* ========================================================== bento */
    /* Empat kartu, EMPAT UKURAN BERBEDA. Kisi yang keempat kotaknya sama
       persis adalah tanda paling kentara bahwa isinya tidak pernah
       ditimbang — padahal "sudah kebukti ratusan kali" jelas lebih berat
       daripada tiga lainnya, dan kartunya harus menunjukkan itu. */
    .bnd-bento {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;
    }
    .bnd-bento > :nth-child(1) { grid-column: span 2; }
    .bnd-bento > :nth-child(4) { grid-column: span 2; }

    .bnd-kartu {
        position: relative; overflow: hidden;
        padding: 28px 26px;
        background: var(--kertas-2);
        border: 2px solid var(--tinta); border-radius: 20px;
        box-shadow: 5px 5px 0 rgba(22, 35, 63, .14);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .bnd-kartu:hover {
        transform: translate(-3px, -3px);
        box-shadow: 9px 9px 0 var(--c);
    }

    .bnd-kartu-ikon {
        display: inline-flex; align-items: center; justify-content: center;
        width: 54px; height: 54px; border-radius: 16px; margin-bottom: 18px;
        background: var(--c); color: #fff; font-size: 1.4rem;
        border: 2px solid var(--tinta);
        transition: rotate .22s ease, scale .22s ease;
    }
    .bnd-kartu:hover .bnd-kartu-ikon { rotate: -8deg; scale: 1.08; }

    .bnd-kartu h3 { font-size: 1.22rem; margin-bottom: 9px; }
    .bnd-kartu p { font-size: .97rem; }

    /* Kartu besar: angkanya yang bicara, bukan ikonnya. */
    .bnd-kartu-besar { display: flex; align-items: center; gap: 28px; }
    .bnd-angka-raksasa {
        flex: 0 0 auto;
        font-family: 'Fraunces', serif; font-weight: 900;
        font-size: 4.4rem; line-height: .92; color: var(--bata);
        font-variation-settings: 'opsz' 120, 'WONK' 1;
    }

    /* ======================================================== layanan */
    .bnd-kisi-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px; }

    /* Bentuk KARCIS: takik setengah lingkaran di kedua tepi, tepat di garis
       putus-putus yang memisahkan gambar dari keterangannya. Takiknya dua
       lingkaran sewarna latar yang menimpa tepi kartu — cara yang sama
       dipakai karcis sungguhan. */
    .bnd-karcis {
        position: relative; display: flex; flex-direction: column;
        background: var(--kertas-2);
        border: 2px solid var(--tinta); border-radius: 18px;
        box-shadow: 6px 6px 0 rgba(22, 35, 63, .16);
        transition: transform .22s ease, box-shadow .22s ease;
    }
    .bnd-karcis:hover {
        transform: translate(-3px, -4px);
        box-shadow: 11px 11px 0 var(--c);
    }

    .bnd-karcis-gambar { position: relative; overflow: hidden; border-radius: 16px 16px 0 0; }
    .bnd-karcis-gambar img {
        width: 100%; height: 190px; object-fit: cover; display: block;
        transition: scale .45s ease;
    }
    .bnd-karcis:hover .bnd-karcis-gambar img { scale: 1.07; }

    .bnd-karcis-ubin {
        display: flex; align-items: center; justify-content: center;
        height: 190px; border-radius: 16px 16px 0 0;
        background: var(--c); color: #fff; font-size: 3.6rem;
    }

    .bnd-karcis-tag {
        position: absolute; left: 14px; top: 14px; z-index: 2;
        padding: 6px 13px; border-radius: 999px;
        background: var(--kertas); border: 2px solid var(--tinta);
        font-size: .74rem; font-weight: 800; color: var(--tinta);
        transform: rotate(-2.5deg);
    }

    /* Garis sobek berikut takiknya. */
    .bnd-karcis-sobek {
        position: relative; height: 0;
        border-top: 2px dashed rgba(22, 35, 63, .3);
    }
    .bnd-karcis-sobek::before,
    .bnd-karcis-sobek::after {
        content: ""; position: absolute; top: -13px;
        width: 24px; height: 24px; border-radius: 50%;
        background: var(--kertas-2);
        border: 2px solid var(--tinta);
    }
    .bnd-karcis-sobek::before { left: -14px; clip-path: inset(0 0 0 48%); }
    .bnd-karcis-sobek::after { right: -14px; clip-path: inset(0 48% 0 0); }

    .bnd-karcis-isi {
        display: flex; flex-direction: column; flex: 1 1 auto;
        padding: 24px 24px 24px;
    }
    .bnd-karcis-isi h3 { font-size: 1.2rem; margin-bottom: 10px; }
    .bnd-karcis-isi p { font-size: .96rem; }

    /* Tombolnya didorong ke dasar kartu supaya semua tombol sebaris,
       berapa pun panjang keterangannya. */
    .bnd-karcis-tombol {
        display: inline-flex; align-items: center; gap: 9px;
        margin-top: auto; padding-top: 20px;
        color: var(--c); font-weight: 800; font-size: .97rem; text-decoration: none;
        transition: gap .2s ease;
    }
    .bnd-karcis:hover .bnd-karcis-tombol { gap: 15px; }

    /* ========================================================== angka */
    .bnd-angka {
        position: relative; overflow: hidden;
        padding: 82px 0;
        background: var(--tinta);
    }
    .bnd-angka .bnd-judul, .bnd-angka h3 { color: var(--kertas); }
    .bnd-angka p { color: rgba(251, 245, 233, .72); }

    .bnd-kisi-angka { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

    .bnd-nilai {
        padding: 30px 26px; border-radius: 20px;
        background: rgba(251, 245, 233, .07);
        border: 2px dashed rgba(251, 245, 233, .3);
        transition: background .22s ease, transform .22s ease;
    }
    .bnd-nilai:hover { background: rgba(251, 245, 233, .13); transform: translateY(-4px); }
    .bnd-nilai-angka {
        margin-top: 12px;
        font-family: 'Fraunces', serif; font-weight: 900;
        font-size: 3.1rem; line-height: 1; color: var(--stabilo);
        font-variation-settings: 'opsz' 120, 'WONK' 1;
        font-variant-numeric: tabular-nums;
    }
    .bnd-nilai p { margin-top: 12px; font-size: .95rem; }

    /* Cap bundar berputar pelan di sisi pita. Tulisannya dibengkokkan
       mengikuti lingkaran lewat <textPath> — bukan gambar, jadi tajam di
       layar mana pun dan beratnya beberapa ratus bait. */
    .bnd-cap {
        position: absolute; right: 3%; top: 50%; translate: 0 -50%;
        width: 160px; height: 160px; opacity: .92;
        animation: bnd-putar 26s linear infinite;
    }
    /* 11px dan .16em, bukan 13px dan .2em.

       "RUMAH SCOPUS FOUNDATION" jauh lebih panjang daripada "RUMAH SCOPUS ·
       JOGJA" yang dulu di sini. Pada ukuran lama, dua putarannya terukur
       536px sementara keliling lingkarannya cuma 452px — 84px terakhir
       tergulung menimpa awalnya sendiri. Pada ukuran ini 431px, menyisakan
       21px sebagai jeda di tempat kedua putarannya bertemu. */
    /* 11,4px dan .16em — angkanya ganjil, dan itu memang dicari.

       "RUMAH SCOPUS FOUNDATION" jauh lebih panjang daripada "RUMAH SCOPUS ·
       JOGJA" yang dulu di sini. Pada ukuran awal 13px/.2em, dua putarannya
       terukur 536px di keliling 452px — 84px terakhir tergulung menimpa
       awalnya sendiri.

       Dikecilkan ke 11px menghilangkan tumpang tindihnya tetapi menyisakan
       431px: 21px kosong di satu titik, dan lubang selebar itu di antara
       huruf yang jaraknya cuma 2px terbaca sebagai cacat, bukan sebagai
       jeda.

       Pada ukuran ini 447px, menyisakan 5px — selebar satu spasi di huruf
       ini. Sambungan antara "·" terakhir dan "R" pertama jadi tidak bisa
       dibedakan dari jarak kata di dalamnya. */
    .bnd-cap text { fill: var(--stabilo); font-size: 11.4px; font-weight: 800; letter-spacing: .16em; }
    @keyframes bnd-putar { to { rotate: 360deg; } }

    /* ==================================================== kertas tempel */
    .bnd-tempel-kisi { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px 24px; }

    /* Kertas tempel. Miringnya BERBEDA-BEDA, diberikan satu per satu di
       markahnya — bukan satu sudut untuk semua, dan bukan diacak
       JavaScript. Sudut yang sama di enam kertas terbaca sebagai cetakan;
       sudut yang diacak tiap muat membuat halamannya tidak pernah sama dua
       kali, dan itu mengganggu orang yang kembali. */
    .bnd-tempel {
        position: relative;
        padding: 30px 22px 24px;
        background: var(--c);
        box-shadow: 4px 5px 0 rgba(22, 35, 63, .18);
        transition: rotate .2s ease, translate .2s ease, box-shadow .2s ease;
    }
    /* Diluruskan saat disentuh: yang sedang dibaca berdiri tegak, sisanya
       tetap miring. */
    .bnd-tempel:hover { rotate: 0deg; translate: 0 -5px; box-shadow: 7px 9px 0 rgba(22, 35, 63, .26); }

    .bnd-tempel::before {
        content: ""; position: absolute; left: 50%; top: -11px;
        width: 74px; height: 22px; translate: -50% 0; rotate: -2deg;
        background: rgba(251, 245, 233, .8);
        border-left: 1px dashed rgba(22, 35, 63, .16);
        border-right: 1px dashed rgba(22, 35, 63, .16);
    }
    .bnd-tempel i { font-size: 1.5rem; color: var(--tinta); }
    .bnd-tempel h3 { margin-top: 12px; font-size: 1.06rem; }
    .bnd-tempel p { margin-top: 7px; font-size: .9rem; color: rgba(22, 35, 63, .72); }

    /* ====================================================== testimoni */
    /* Tiga rekaman berjajar, lalu kutipannya di bawah.

       Ketiganya TIDAK berperbandingan sisi sama: dua tegak 540x960 dan satu
       rebah 960x540. Yang dipatok TINGGINYA, bukan lebarnya — dengan lebar
       yang dipatok, rekaman rebahnya harus dipotong jadi tegak dan yang
       terbuang justru sisi kiri-kanan, tempat orang yang sedang berbicara
       biasanya berada.

       Jadi tiap polaroid memeluk rekamannya sendiri: yang tegak jadi sempit
       dan tinggi, yang rebah jadi lebar dan pendek. Tingginya berbeda, dan
       itu memang bentuk foto yang dipasang di dinding. */
    /* EMPAT kolom: tiga rekaman dan kutipannya, sebaris.

       Kutipannya dulu berdiri sendiri di bawah barisan — selebar 677px di
       tengah ruang 1.296px, jadi ada 310px kosong di kiri dan kanannya, dan
       satu pita kosong lagi di antara keduanya. Dinaikkan jadi anggota
       barisan yang sama, ruang itu terpakai dan tingginya ikut sama dengan
       polaroid di sebelahnya. */
    .bnd-testi-kisi {
        display: grid; grid-template-columns: repeat(4, 1fr);
        gap: 24px; align-items: stretch; justify-items: center;
    }

    /* Keempatnya MEREGANG ke tinggi baris yang sama.

       Menyetel ukuran huruf kutipannya sampai pas tinggi polaroid pernah
       dicoba, dan itu rapuh: tiap lebar layar memberi tinggi kutipan yang
       berbeda, jadi angkanya pas di 1440 lalu meleset lagi di 1200.

       Dengan meregang, selisihnya jatuh sebagai bidang putih di BAWAH
       keterangan polaroid — dan itu kebetulan persis tepi bawah polaroid
       sungguhan, yang memang lebih tebal daripada ketiga sisi lainnya. */
    .bnd-testi-kisi > .bnd-polaroid { align-self: stretch; }

    /* Bingkai polaroid: pinggiran bawah lebih tebal, seperti foto cetak. */
    .bnd-polaroid {
        /* margin: 0 — polaroidnya <figure>, dan Bootstrap memberi figure
           margin-bottom: 1rem. Dengan peregangan baris, 16px itu terpotong
           dari tingginya: terukur polaroid 498,453px di baris setinggi 514,
           dan selisih 15,5px-nya persis satu rem. Gejalanya terbaca seperti
           "peregangannya tidak jalan", padahal jalan. */
        margin: 0;
        width: fit-content; max-width: 100%;
        padding: 13px 13px 16px;
        background: var(--kertas);
        border: 2px solid var(--tinta);
    }
    /* Kemiringan dan warna bayangannya berbeda-beda, ditulis satu per satu:
       tiga polaroid bersudut sama terbaca sebagai cetakan, bukan sebagai
       foto yang ditempel orang. */
    .bnd-polaroid:nth-child(1) { transform: rotate(-1.8deg); box-shadow: 8px 8px 0 var(--ungu); }
    .bnd-polaroid:nth-child(2) { transform: rotate(1.2deg); box-shadow: 8px 8px 0 var(--rumput); }
    .bnd-polaroid:nth-child(3) { transform: rotate(-.9deg); box-shadow: 8px 8px 0 var(--bata); }

    /* Kotak tayang berukuran SAMA untuk ketiganya, dan rekamannya
       memenuhinya dari dalam.

       Dua rekaman memang tegak 540x960. Yang ketiga tersimpan 960x540 —
       tetapi isinya sebenarnya rekaman TEGAK yang diberi bantalan kiri-kanan
       berisi potongan lain yang tidak ada hubungannya. Memotong sisinya
       bukan mengorbankan apa pun; justru membuang bantalan itu.

       Sebelumnya tiap polaroid memeluk rekamannya sendiri, dan yang rebah
       jadi 290px lebih pendek daripada tetangganya — ruang kosong selebar
       satu kolom menganga di tengah barisan. */
    .bnd-tayang {
        position: relative; overflow: hidden;
        height: 420px; aspect-ratio: var(--ar, 9 / 16);
        border: 2px solid var(--tinta); background: var(--tinta);
    }
    /* Rekamannya diperbesar sampai TINGGINYA penuh, lalu dipusatkan;
       kelebihan lebarnya terpotong rata kiri-kanan. max-width: none wajib —
       tanpa itu aturan bawaan templat menahannya di 100% dan yang rebah
       justru menyusut jadi pita tipis di tengah kotak. */
    .bnd-tayang video {
        position: absolute; top: 50%; left: 50%; translate: -50% -50%;
        height: 100%; width: auto; max-width: none; display: block;
    }

    .bnd-polaroid-kaki {
        margin-top: 12px; text-align: center;
        font-family: 'Fraunces', serif; font-weight: 700; font-size: .88rem;
    }

    /* Kutipannya jadi kartu keempat. Isinya dipusatkan tegak supaya tidak
       menggantung di atas saat kalimatnya lebih pendek daripada rekaman di
       sebelahnya. */
    .bnd-testi-kutip {
        width: 100%; margin: 0;
        display: flex; flex-direction: column; justify-content: center;
        padding: 22px 20px;
    }
    /* Ukurannya disetel supaya kartunya TIDAK melebihi tinggi polaroid di
       sebelahnya. Pada .98rem/1,65 terukur 533px — 47px lebih tinggi, dan
       selisih itu membuat ketiga polaroid menggantung dengan ruang kosong
       di bawahnya, persis yang hendak dihilangkan. */
    .bnd-testi-kutip p { font-size: .9rem; line-height: 1.58; }
    .bnd-testi-kutip .bnd-balon-kutip { font-size: 2.6rem; margin-bottom: 4px; }
    .bnd-testi-kutip .bnd-balon-kaki { margin-top: 14px; padding-top: 13px; }
    .bnd-testi-kutip .bnd-balon-kaki i { width: 38px; height: 38px; font-size: .9rem; }
    .bnd-testi-kutip .bnd-balon-kaki strong { font-size: .92rem; }
    .bnd-testi-kutip .bnd-balon-kaki small { font-size: .8rem; }

    /* Gelembung percakapan dengan ekor di kiri bawah. */
    .bnd-balon {
        position: relative;
        padding: 34px 32px;
        background: var(--kertas);
        border: 2px solid var(--tinta); border-radius: 22px;
        box-shadow: 6px 6px 0 rgba(22, 35, 63, .16);
    }
    .bnd-balon::after {
        content: ""; position: absolute; left: 46px; bottom: -16px;
        width: 28px; height: 28px;
        background: var(--kertas);
        border-right: 2px solid var(--tinta); border-bottom: 2px solid var(--tinta);
        rotate: 56deg;
    }
    .bnd-balon p { font-size: 1.1rem; line-height: 1.72; color: var(--tinta); }
    .bnd-balon p + p { margin-top: 14px; }
    .bnd-balon-kutip {
        display: block; margin-bottom: 8px;
        font-family: 'Fraunces', serif; font-size: 3.4rem; line-height: .55;
        color: var(--stabilo);
    }
    .bnd-balon-kaki {
        display: flex; align-items: center; gap: 13px; margin-top: 26px;
        padding-top: 20px; border-top: 2px dashed var(--garis);
    }
    .bnd-balon-kaki i {
        display: inline-flex; align-items: center; justify-content: center;
        width: 46px; height: 46px; border-radius: 50%; flex: 0 0 auto;
        background: var(--rumput); color: #fff; border: 2px solid var(--tinta);
    }
    .bnd-balon-kaki strong { display: block; font-size: 1rem; }
    .bnd-balon-kaki small { color: var(--tinta-2); font-size: .88rem; }

    /* ========================================================= ajakan */
    .bnd-ajakan {
        padding: 62px 48px; text-align: center;
        background: var(--stabilo);
        border: 3px solid var(--tinta); border-radius: 26px;
        box-shadow: 10px 10px 0 var(--tinta);
    }
    .bnd-ajakan h2 { font-size: 2.5rem; line-height: 1.12; }
    .bnd-ajakan p {
        margin: 16px auto 0; max-width: 52ch;
        color: rgba(22, 35, 63, .78); font-size: 1.08rem;
    }
    .bnd-ajakan .bnd-aksi { justify-content: center; }
    .bnd-ajakan .bnd-tombol-kedua { background: var(--kertas); }

    /* ==================================================== layar kecil */
    @media (max-width: 991.98px) {
        .bnd-hero-kisi,
        .bnd-testi-kisi { grid-template-columns: minmax(0, 1fr); gap: 44px; }
        /* Di layar sempit fotonya turun ke bawah teks dan dipusatkan;
           tidak ada lagi lajur kanan yang harus ditempelinya. Di sini
           max-width aman: kisinya sudah satu lajur, jadi tidak ada
           peregangan item grid yang bisa dibatalkan. */
        .bnd-foto { order: 2; max-width: 400px; margin-inline: auto; width: 100%; }
        .bnd-hero h1 { font-size: 2.7rem; }
        .bnd-judul { font-size: 2.1rem; }

        .bnd-bento { grid-template-columns: repeat(2, 1fr); }
        .bnd-bento > :nth-child(1),
        .bnd-bento > :nth-child(4) { grid-column: span 2; }

        .bnd-kisi-3,
        .bnd-tempel-kisi,
        .bnd-rute-deret { grid-template-columns: repeat(2, 1fr); }
        .bnd-rute-deret { gap: 32px 18px; }
        .bnd-rute-deret::before { display: none; }
        .bnd-cap { display: none; }
        .bnd-testi-kisi { grid-template-columns: repeat(2, 1fr); }
        .bnd-tayang { height: 360px; }
        /* Di dua kolom kutipannya jatuh di baris kedua bersama satu rekaman;
           tidak ada tinggi yang perlu disamakan lagi. */
        .bnd-testi-kutip { align-self: start; }
    }

    @media (max-width: 767.98px) {
        .bnd-bagian { padding: 58px 0; }
        .bnd-hero { padding: 104px 0 52px; }
        .bnd-hero h1 { font-size: 2.1rem; }
        .bnd-hero p { font-size: 1.02rem; }
        .bnd-judul { font-size: 1.8rem; }
        .bnd-kepala { margin-bottom: 30px; }

        .bnd-bento,
        .bnd-kisi-3,
        .bnd-kisi-angka,
        .bnd-tempel-kisi,
        .bnd-rute-deret { grid-template-columns: 1fr; }
        .bnd-bento > :nth-child(1),
        .bnd-bento > :nth-child(4) { grid-column: span 1; }

        .bnd-kartu-besar { flex-direction: column; align-items: flex-start; gap: 14px; }
        .bnd-angka-raksasa { font-size: 3.4rem; }

        /* Gelembung angkanya DISEMBUNYIKAN di ponsel: pada lebar 390px
           keduanya menimpa wajah orang di fotonya, dan yang menolong di
           layar lebar justru merusak di layar sempit. */
        .bnd-gelembung { display: none; }
        .bnd-foto-bingkai { border-radius: 140px 140px 18px 18px; }

        .bnd-aksi > .bnd-tombol { flex: 1 1 100%; justify-content: center; }
        .bnd-ajakan { padding: 38px 22px; box-shadow: 6px 6px 0 var(--tinta); }
        .bnd-ajakan h2 { font-size: 1.68rem; }
        .bnd-testi-kisi { grid-template-columns: 1fr; gap: 34px; }
        .bnd-tayang { height: 420px; }
        .bnd-nilai-angka { font-size: 2.6rem; }
        /* Relnya DISEMBUNYIKAN saat perhentiannya menumpuk tegak: garis
           mendatar di antara kotak yang tersusun ke bawah menunjuk arah
           yang salah. */
        .bnd-rute-deret::before { display: none; }
    }

    /* ================================================== gerak minimal */
    /* Tiap selektor beranimasi DISEBUT TERANG di sini, bukan hanya tersapu
       `.bnd *`: penjaga GerakMinimalPublikTest mencocokkan teks selektornya
       apa adanya, dan sapuan bintang membuatnya buta terhadap animasi
       berikutnya yang ditulis orang lain.

       Yang berjalan terus — pita, kata berputar, gelembung mengapung, cap
       berputar — dimatikan sepenuhnya. Beranda adalah halaman pertama yang
       dibuka orang; kalau layarnya membuatnya pusing, ia belum punya satu
       pun alasan untuk bertahan. */
    @media (prefers-reduced-motion: reduce) {
        .bnd-gelembung { animation: none !important; }
        .bnd-geser-bar-jalan > i { animation: none !important; }

        /* Slidernya tidak berpindah sendiri — lihat alasannya di skripnya.
           Tombol maju-mundurnya tetap jalan, jadi fotonya tetap bisa
           dilihat semua. */
        .bnd-slide { transition: none !important; }
        .bnd-cap { animation: none !important; }
        .bnd-stabilo::after { animation: none !important; transform: rotate(-1.2deg) skewX(-9deg); }

        /* Kata berputar dimatikan berarti ketiganya tertumpuk dan tidak ada
           yang terbaca. Yang pertama dibiarkan terlihat. */
        .bnd-putar > span { animation: none !important; opacity: 0; }
        .bnd-putar > span:first-child { opacity: 1; }

        .bnd *,
        .bnd *::before,
        .bnd *::after { transition: none !important; }
    }
</style>

<div class="bnd">

    {{-- ================================================== hero ========= --}}
    <section class="bnd-hero">
        <div class="container">
            <div class="bnd-hero-kisi">
                <div data-aos="fade-up">
                    <span class="bnd-stiker">
                        <i class="bi bi-stars"></i> Ratusan naskah sudah tembus
                    </span>

                    <h1>
                        Naskahmu nggak berhenti di
                        <span class="bnd-putar">
                            <span>laci.</span>
                            <span>draft.</span>
                            <span>angan.</span>
                        </span>
                        <br>
                        Kita bantu sampai <span class="bnd-stabilo">terbit</span>.
                    </h1>

                    <p>
                        Nggak perlu jago nulis dari awal. Bawa saja idemu — nanti kita
                        bedah bareng sampai jadi naskah yang siap dikirim ke jurnal
                        internasional.
                    </p>

                    <div class="bnd-aksi">
                        <a href="#layanan" class="bnd-tombol bnd-tombol-utama">
                            Lihat kelasnya <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="https://www.youtube.com/@rumahscopus" target="_blank" rel="noopener"
                            class="bnd-tombol bnd-tombol-kedua">
                            <i class="bi bi-play-fill"></i> Cerita alumni
                        </a>
                    </div>

                    <div class="bnd-centang">
                        <span><i class="bi bi-check2-circle"></i> Trainernya publish sendiri</span>
                        <span><i class="bi bi-check2-circle"></i> Latihan pakai naskahmu</span>
                        <span><i class="bi bi-check2-circle"></i> Ditemani sampai terbit</span>
                    </div>
                </div>

                <div class="bnd-foto" data-aos="zoom-out" data-aos-delay="150">
                    <span class="bnd-selotip bnd-selotip-kiri"></span>
                    <span class="bnd-selotip bnd-selotip-kanan"></span>

                    {{-- Tujuh belas foto kegiatan sungguhan, menggantikan satu
                         gambar hiasan. Aslinya HEIC dan JPG 1,5–4,6 MB dari
                         kamera ponsel; diubah ke WebP lebar 1.200px.

                         ORIENTASINYA sempat hilang. HEIC dari iPhone menyimpan
                         arah putarnya di EXIF, bukan di piksel — dan `sips -s
                         format jpeg` tidak memanggangnya ke hasil, jadi sebelas
                         dari tujuh belas foto keluar miring 90 derajat tanpa
                         satu pun galat. Diubah lewat `qlmanage -t` yang
                         menghormati EXIF-nya. --}}
                    <div class="bnd-foto-bingkai" data-bnd-geser>
                        @php($fotoHero = [
                            'Peserta merayakan kelulusan kelas di Rumah Scopus',
                            'Peserta berfoto bersama di Rumah Scopus',
                            'Suasana kelas Scopus Camp di Rumah Scopus',
                            'Peserta Scopus Camp Banjarmasin berfoto bersama',
                            'Peserta menerima buku dari Rumah Scopus',
                            'Trainer mendampingi peserta menulis naskah',
                            'Peserta mengerjakan naskah bersama trainer',
                            'Sesi pendampingan penulisan di Rumah Scopus',
                            'Peserta berdiskusi di ruang kelas Rumah Scopus',
                            'Trainer menjelaskan materi kepada peserta',
                            'Peserta bekerja dengan laptop di kelas',
                            'Peserta menyimak paparan di layar',
                            'Suasana kelas daring bersama trainer',
                            'Peserta menerima penghargaan dari Rumah Scopus',
                            'Peserta berfoto bersama seusai kelas',
                            'Trainer memaparkan materi di depan kelas',
                            'Dua peserta mengerjakan naskah bersama',
                        ])

                        @foreach ($fotoHero as $i => $keterangan)
                            {{-- Hanya foto PERTAMA yang dimuat seketika. Sisanya
                                 menunggu giliran lewat data-src, dan slidernya
                                 memuat satu di depan saat berpindah.

                                 Bukan loading="lazy": slide yang tidak aktif
                                 tetap berada di dalam layar, cuma tembus pandang
                                 — peramban menganggapnya terlihat dan mengunduh
                                 ketujuh belasnya sekaligus, 3,3 MB di layar
                                 pertama. --}}
                            <div class="bnd-slide @if ($i === 0) bnd-slide-aktif @endif">
                                <img
                                    @if ($i === 0)
                                        src="{{ asset('assets/img/public/header/header-01.webp') }}"
                                        fetchpriority="high"
                                    @else
                                        data-src="{{ asset('assets/img/public/header/header-' . sprintf('%02d', $i + 1) . '.webp') }}"
                                    @endif
                                    alt="{{ $keterangan }}">
                            </div>
                        @endforeach

                        <div class="bnd-geser-kaki">
                            <button type="button" class="bnd-geser-tombol" data-bnd-mundur
                                aria-label="Foto sebelumnya">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button type="button" class="bnd-geser-tombol" data-bnd-maju
                                aria-label="Foto berikutnya">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                            <span class="bnd-geser-hitung" data-bnd-hitungfoto>01 / {{ count($fotoHero) }}</span>
                        </div>

                        <span class="bnd-geser-bar" data-bnd-bar aria-hidden="true"><i></i></span>
                    </div>

                    <span class="bnd-gelembung bnd-gelembung-1">
                        <i class="bi bi-mortarboard-fill" style="color: var(--bata)"></i>
                        <span><strong>5.000+</strong><small>alumni</small></span>
                    </span>
                    <span class="bnd-gelembung bnd-gelembung-2">
                        <i class="bi bi-journal-check" style="color: var(--rumput)"></i>
                        <span><strong>1.851</strong><small>artikel terbit</small></span>
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- ====================================================== rute ===== --}}
    <section class="bnd-rute">
        <div class="container">
            <div class="bnd-rute-deret">
                {{-- Perhentian keempat sengaja menyebut "terbit", bukan
                     "submit": submit cuma separuh jalan, dan menghentikan
                     ceritanya di situ persis keraguan yang bikin orang ragu
                     mendaftar. --}}
                @foreach ([
                    ['Ngobrol dulu', 'Cerita kondisi naskahmu ke admin. Gratis, dan nggak ada kewajiban lanjut.'],
                    ['Pilih kelasnya', 'Kami bantu carikan yang paling pas sama waktu dan targetmu.'],
                    ['Bedah bareng', 'Naskahmu sendiri yang dikerjakan — bukan contoh kasus punya orang.'],
                    ['Sampai terbit', 'Ditemani terus lewat revisi reviewer, sampai artikelmu benar-benar terbit.'],
                ] as $i => [$judul, $ket])
                    <div class="bnd-rute-henti" data-aos="fade-up" data-aos-delay="{{ $i * 110 }}">
                        <span class="bnd-rute-nomor">{{ $i + 1 }}</span>
                        <h3>{{ $judul }}</h3>
                        <p>{{ $ket }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================================================== alasan ======= --}}
    <section class="bnd-bagian">
        <div class="container">
            <div class="bnd-kepala" data-aos="fade-up">
                <span class="bnd-stiker"><i class="bi bi-heart-fill"></i> Kenapa balik lagi</span>
                <h2 class="bnd-judul">Yang bikin orang betah sampai naskahnya terbit</h2>
                <p class="bnd-sub">
                    Bukan karena kelasnya seru saja — tapi karena nggak ditinggal
                    pas bagian yang paling susah.
                </p>
            </div>

            <div class="bnd-bento">
                {{-- Kartu pertama dan keempat melebar dua kolom; alasannya ada
                     di aturan .bnd-bento. --}}
                <div class="bnd-kartu" style="--c: var(--bata)" data-aos="fade-up">
                    <span class="bnd-kartu-ikon"><i class="bi bi-person-check-fill"></i></span>
                    <h3>Trainernya nggak cuma ngajar</h3>
                    <p>
                        Mereka nulis dan publish sendiri di jurnal bereputasi. Jadi yang
                        dibagikan pengalaman waktu naskahnya ditolak reviewer, bukan isi
                        slide.
                    </p>
                </div>

                <div class="bnd-kartu" style="--c: var(--rumput)" data-aos="fade-up" data-aos-delay="100">
                    <span class="bnd-kartu-ikon"><i class="bi bi-pencil-fill"></i></span>
                    <h3>Naskahmu sendiri</h3>
                    <p>Bukan contoh kasus punya orang. Pulang dari kelas, naskahmu sudah jalan.</p>
                </div>

                <div class="bnd-kartu" style="--c: var(--ungu)" data-aos="fade-up" data-aos-delay="200">
                    <span class="bnd-kartu-ikon"><i class="bi bi-chat-heart-fill"></i></span>
                    <h3>Nggak ditinggal</h3>
                    <p>
                        Kelas selesai bukan berarti selesai. Pendampingan jalan terus
                        sampai artikelmu benar-benar terbit.
                    </p>
                </div>

                <div class="bnd-kartu bnd-kartu-besar" style="--c: var(--langit)"
                    data-aos="fade-up" data-aos-delay="300">
                    <span class="bnd-angka-raksasa">1.851</span>
                    <span>
                        <h3>Sudah kebukti, bukan janji</h3>
                        <p>
                            Segitu jumlah artikel peserta yang sudah terbit dan terindeks
                            Scopus. Angkanya nambah terus tiap bulan.
                        </p>
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================= layanan ======= --}}
    <section class="bnd-bagian" id="layanan" style="background: var(--kertas-2);">
        <div class="container">
            <div class="bnd-kepala bnd-kepala-tengah" data-aos="fade-up">
                <span class="bnd-stiker"><i class="bi bi-ticket-perforated-fill"></i> Pilih kelasmu</span>
                <h2 class="bnd-judul">Mau yang mana dulu?</h2>
                <p class="bnd-sub">
                    Enam cara belajar. Ada yang tiga hari penuh, ada yang cukup lima
                    puluh menit — tinggal cocokkan sama waktumu.
                </p>
            </div>

            {{-- CLINIK SCOPUS DULU MENUNJUK /Analisis-Bibliometrik.

                 Bukan salah ketik yang kelihatan: tombolnya tetap berbunyi
                 normal dan tetap membuka halaman yang sah, jadi yang tahu ia
                 salah cuma orang yang menekannya lalu memesan layanan lain —
                 dan orang itu tidak mengadu, ia pergi. Dijaga BerandaTest. --}}
            <div class="bnd-kisi-3">
                @foreach ([
                    ['var(--bata)', 'HomeScopusCamp.png', 'Offline · 3 hari', 'Scopus Camp', 'Tiga hari ketemu langsung di Jogja, naskahmu dibedah sampai kelar. Habis itu masih ditemani setahun penuh.', url('/Scopus-Camp')],
                    ['var(--langit)', 'HomeOnlineTraining.png', 'Online · 8 sesi', 'Online Training', 'Delapan kali ketemu daring bareng trainer. Cocok buat yang susah ninggalin kampus.', 'https://rumahscopus.com/courses/online-class/'],
                    ['var(--ungu)', 'HomeScopusKafe.png', 'Privat · 5 jam', 'Scopus Kafe', 'Lima jam berdua sama trainer. Buat naskah yang sudah jalan tapi mentok di satu bagian.', url('/Scopus-Kafe')],
                    ['var(--rumput)', 'HomeBibliometrik.png', 'Online · 8 sesi', 'Analisis Bibliometrik', 'Belajar baca peta riset: mana yang sudah ramai, mana celah yang belum digarap orang.', url('/Analisis-Bibliometrik')],
                    ['#b45309', 'HomeScopusClinic.png', 'Chat · 50 menit', 'Clinik Scopus', 'Lagi mentok satu pertanyaan saja? Ngobrol lewat chat lima puluh menit, langsung dapat jalan keluarnya.', url('/Clinik-Scopus')],
                    ['#be185d', null, 'Daring · kuota terbatas', 'Webinar Eksklusif', 'Sesi daring bertema khusus, dibuka berkala. Kuotanya sedikit, jadi jangan kelamaan mikir.', url('/Webinar-Eksklusif')],
                ] as $i => [$warna, $gambar, $tag, $judul, $ket, $tautan])
                    <div class="bnd-karcis" style="--c: {{ $warna }}"
                        data-aos="fade-up" data-aos-delay="{{ ($i % 3) * 110 }}">
                        <div class="bnd-karcis-gambar">
                            @if ($gambar)
                                <img src="{{ asset('assets/img/public/' . $gambar) }}" alt="{{ $judul }}">
                            @else
                                {{-- Belum ada fotonya. Ubin berwarna lebih jujur
                                     daripada meminjam foto layanan lain. --}}
                                <div class="bnd-karcis-ubin"><i class="bi bi-broadcast"></i></div>
                            @endif
                            <span class="bnd-karcis-tag">{{ $tag }}</span>
                        </div>

                        <div class="bnd-karcis-sobek"></div>

                        <div class="bnd-karcis-isi">
                            <h3>{{ $judul }}</h3>
                            <p>{{ $ket }}</p>
                            <a href="{{ $tautan }}" class="bnd-karcis-tombol">
                                Intip detailnya <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- =================================================== angka ======= --}}
    <section class="bnd-angka">
        {{-- Cap bundar berputar. Tulisannya dibengkokkan mengikuti lingkaran
             lewat <textPath> — bukan gambar, jadi tetap tajam dan beratnya
             beberapa ratus bait. --}}
        <svg class="bnd-cap" viewBox="0 0 200 200" aria-hidden="true">
            <defs>
                <path id="bnd-lingkar"
                    d="M 100,100 m -72,0 a 72,72 0 1,1 144,0 a 72,72 0 1,1 -144,0" />
            </defs>
            <text>
                <textPath href="#bnd-lingkar">
                    RUMAH SCOPUS FOUNDATION · RUMAH SCOPUS FOUNDATION ·
                </textPath>
            </text>
        </svg>

        <div class="container">
            <div class="bnd-kepala" data-aos="fade-up">
                <span class="bnd-stiker"
                    style="background: var(--stabilo); color: var(--tinta); box-shadow: 3px 3px 0 rgba(251, 245, 233, .28);">
                    <i class="bi bi-bar-chart-fill" style="color: var(--bata)"></i> Angkanya
                </span>
                <h2 class="bnd-judul">Nggak perlu kami lebih-lebihkan</h2>
                <p class="bnd-sub">
                    Ini hitungan dari peserta yang benar-benar menyelesaikan
                    pendampingannya bareng kami.
                </p>
            </div>

            {{-- Penghitungnya DITULIS SENDIRI di bawah, bukan purecounter.

                 Bukan karena purecounter buruk — ia memang sudah dimuat
                 layout ini — melainkan karena pemisah ribuannya koma dan
                 tidak bisa diganti. "5,000" salah dalam Bahasa Indonesia,
                 dan angka yang salah tulis pada bagian yang isinya memang
                 cuma angka adalah hal pertama yang dilihat orang.

                 Yang diperbaiki dari versi lama tetap dipertahankan: ia baru
                 berhitung saat bagiannya TERLIHAT. Versi lama berjalan pada
                 DOMContentLoaded — ribuan langkah untuk angka yang ada
                 3.000px di bawah layar pertama, dan sudah selesai jauh
                 sebelum ada yang menggulung ke sana. --}}
            <div class="bnd-kisi-angka">
                @foreach ([
                    ['bi-mortarboard-fill', 5000, 'orang sudah ikut kelas kami'],
                    ['bi-send-check-fill', 2315, 'naskah sudah dikirim ke jurnal internasional'],
                    ['bi-journal-check', 1851, 'artikel sudah terbit dan terindeks Scopus'],
                ] as $i => [$ikon, $nilai, $ket])
                    <div class="bnd-nilai" data-aos="fade-up" data-aos-delay="{{ $i * 110 }}">
                        <i class="bi {{ $ikon }}" style="color: var(--stabilo); font-size: 1.5rem;"></i>

                        {{-- Angkanya DITULIS UTUH di markahnya, bukan nol.
                             Yang JavaScript-nya mati — atau yang memakai
                             pembaca layar — tetap membaca angka yang benar,
                             bukan tiga buah nol. --}}
                        <div class="bnd-nilai-angka" data-bnd-hitung="{{ $nilai }}">
                            {{ number_format($nilai, 0, ',', '.') }}
                        </div>

                        <p>{{ $ket }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================ kertas tempel ======= --}}
    <section class="bnd-bagian">
        <div class="container">
            <div class="bnd-kepala bnd-kepala-tengah" data-aos="fade-up">
                <span class="bnd-stiker"><i class="bi bi-lightbulb-fill"></i> Isi kelasnya</span>
                <h2 class="bnd-judul">Yang bakal kamu kuasai</h2>
                <p class="bnd-sub">
                    Enam hal yang kepakai terus, dari halaman pertama sampai artikelmu
                    nongol di indeks.
                </p>
            </div>

            <div class="bnd-tempel-kisi">
                @foreach ([
                    ['#ffd84d', '-2.2', 'bi-file-earmark-text-fill', 'Nulis yang enak dibaca', 'Susunan kalimat yang bikin reviewer betah, bukan bikin ngantuk.'],
                    ['#a7f3d0', '1.8', 'bi-robot', 'Pakai AI dengan benar', 'Boleh dibantu AI — asal tahu batas yang masih aman buat jurnal.'],
                    ['#bfdbfe', '-1.4', 'bi-diagram-3-fill', 'Bongkar anatomi paper', 'Tiap bagian paper punya tugasnya. Kalau tahu, nulisnya jauh lebih cepat.'],
                    ['#fecdd3', '2.4', 'bi-link-45deg', 'Cari referensi kelas satu', 'Bukan asal banyak, tapi yang bikin argumenmu susah dibantah.'],
                    ['#e9d5ff', '-2', 'bi-chat-square-text-fill', 'Dibimbing terus', 'Mentok di tengah jalan? Ada yang bisa ditanya, nggak jalan sendirian.'],
                    ['#fde68a', '1.2', 'bi-patch-check-fill', 'Sampai terindeks', 'Submit itu baru separuh jalan. Kita temani sampai benar-benar terbit.'],
                ] as $i => [$warna, $miring, $ikon, $judul, $ket])
                    <div class="bnd-tempel"
                        style="--c: {{ $warna }}; rotate: {{ $miring }}deg;"
                        data-aos="fade-up" data-aos-delay="{{ $i * 70 }}">
                        <i class="bi {{ $ikon }}"></i>
                        <h3>{{ $judul }}</h3>
                        <p>{{ $ket }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================================================ testimoni ====== --}}
    <section class="bnd-bagian" style="background: var(--kertas-2);">
        <div class="container">
            <div class="bnd-kepala bnd-kepala-tengah" data-aos="fade-up">
                <span class="bnd-stiker"><i class="bi bi-chat-quote-fill"></i> Kata mereka</span>
                <h2 class="bnd-judul">Yang sudah duluan tembus</h2>
            </div>

            {{-- Ketiga rekaman ini SAMA PERSIS dengan yang dipakai halaman
                 iklan Webinar Eksklusif, dan sengaja diambil dari tempat yang
                 sama: storage/video di MIS. Halaman iklan itu repo terpisah
                 yang memuatnya lewat alamat ini juga — satu berkas, dua
                 halaman, jadi mengganti rekamannya cukup sekali. --}}
            <div class="bnd-testi-kisi">
                {{-- --ar hanya diberikan pada yang tersimpan rebah.

                     23/36 bukan 9/16: isi tegaknya menempati bagian tengah
                     selebar 345 dari 960 piksel sumbernya, jadi kotak
                     seketat 9/16 akan ikut memotong bahu orangnya. Angka ini
                     memotong persis sampai tepi bantalannya. --}}
                @foreach ([
                    ['testimoni', 'testimoni-sampul', null],
                    ['testimoni2-web', 'testimoni2-sampul', '23 / 36'],
                    ['testimoni3-web', 'testimoni3-sampul', null],
                ] as $i => [$berkas, $sampul, $rasio])
                    <figure class="bnd-polaroid" data-aos="fade-up" data-aos-delay="{{ $i * 110 }}">
                        {{-- preload="none", BUKAN "metadata".

                             Ketiganya berjumlah 94 MB. Dengan "metadata" saja
                             peramban tetap membuka sambungan ke tiap berkas
                             untuk membaca kepalanya — tiga permintaan ke berkas
                             puluhan megabita, di halaman depan, untuk rekaman
                             yang belum tentu ditonton.

                             Sampulnya yang menggantikan bingkai pertama, dan
                             itu sebabnya tiap rekaman punya berkas -sampul
                             sendiri. --}}
                        <div class="bnd-tayang" @if ($rasio) style="--ar: {{ $rasio }}" @endif>
                            <video controls playsinline preload="none"
                                poster="{{ asset('storage/video/' . $sampul . '.webp') }}"
                                aria-label="Testimoni peserta Rumah Scopus">
                                <source src="{{ asset('storage/video/' . $berkas . '.mp4') }}" type="video/mp4">
                                Peramban kamu belum bisa memutar video ini.
                            </video>
                        </div>
                        <figcaption class="bnd-polaroid-kaki">Peserta Rumah Scopus</figcaption>
                    </figure>
                @endforeach

                {{-- Kutipannya DI DALAM kisi, bukan sesudahnya: ia kartu
                     keempat, bukan penutup di bawah barisan. --}}
                <div class="bnd-balon bnd-testi-kutip" data-aos="fade-up" data-aos-delay="330">
                <span class="bnd-balon-kutip">&ldquo;</span>
                <p>
                    Awalnya pesimis banget. Rasanya Scopus itu buat orang lain,
                    bukan buat saya. Tiga hari di Jogja ternyata cukup untuk
                    mengubah cara saya memandang naskah sendiri.
                </p>
                <p>Sekarang sudah submit — dan ternyata memang sebisa itu.</p>

                <div class="bnd-balon-kaki">
                    <i class="bi bi-people-fill"></i>
                    <span>
                        <strong>Peserta Scopus Camp</strong>
                            <small>Pusat Rumah Scopus, Yogyakarta</small>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =================================================== ajakan ====== --}}
    <section class="bnd-bagian">
        <div class="container">
            <div class="bnd-ajakan" data-aos="zoom-in">
                <h2>Sudah siap naskahmu terbit?</h2>
                {{-- JANGAN menawarkan "ngobrol lima puluh menit" di sini.

                     Kalimat itu dulu berdiri di tempat ini, dan itu keliru:
                     sesi lima puluh menit adalah Clinik Scopus, layanan
                     BERBAYAR. Menawarkannya sebagai jalan masuk yang santai
                     membuat orang mengira konsultasinya gratis — dan yang
                     menanggung kekecewaannya admin yang harus menjelaskan
                     bahwa itu produk, bukan sambutan.

                     Yang memang gratis cuma bertanya ke admin. Itu yang
                     ditawarkan di sini. --}}
                <p>
                    Nggak harus langsung ambil kelas. Tanya-tanya dulu ke admin
                    kami — gratis, dan nggak ada kewajiban apa pun. Nanti kita
                    bantu carikan yang paling pas buat naskahmu.
                </p>
                <div class="bnd-aksi">
                    <a href="#layanan" class="bnd-tombol bnd-tombol-utama">
                        Pilih kelas <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="{{ url('/contact') }}" class="bnd-tombol bnd-tombol-kedua">
                        <i class="bi bi-chat-dots-fill"></i> Tanya dulu
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>

<script>
    /*
     * Slider foto hero.
     *
     * Ditulis sendiri, bukan memakai Swiper yang sudah dimuat layout ini:
     * yang dibutuhkan cuma mengganti kelas aktif dan memuat gambar
     * berikutnya. Swiper menambahkan pengukuran tata letak, sentuhan geser,
     * dan pengamat ukuran — yang semuanya harus ikut dimatikan lagi untuk
     * slider yang tidak bisa digeser tangan.
     */
    (function () {
        var bingkai = document.querySelector('[data-bnd-geser]');

        if (! bingkai) { return; }

        var slide = bingkai.querySelectorAll('.bnd-slide');
        var hitung = bingkai.querySelector('[data-bnd-hitungfoto]');
        var bar = bingkai.querySelector('[data-bnd-bar]');
        var kini = 0;
        var jeda = null;
        var LAMA = 5000;

        if (slide.length < 2) { return; }

        var diam = window.matchMedia('(prefers-reduced-motion: reduce)');

        /*
         * Gambar dimuat SATU DI DEPAN, bukan semuanya sekaligus.
         *
         * Ketujuh belas foto berjumlah 3,3 MB. Slide yang tidak aktif tetap
         * berada di dalam layar — cuma tembus pandang — jadi loading="lazy"
         * tidak menahannya sama sekali; peramban akan mengunduh semuanya di
         * layar pertama.
         */
        function muat(ke) {
            var el = slide[ke];

            if (! el) { return; }

            var gambar = el.querySelector('img[data-src]');

            if (! gambar) { return; }

            gambar.src = gambar.getAttribute('data-src');
            gambar.removeAttribute('data-src');
        }

        function dua(n) { return n < 10 ? '0' + n : String(n); }

        function tampilkan(ke) {
            ke = (ke + slide.length) % slide.length;

            slide[kini].classList.remove('bnd-slide-aktif');
            slide[ke].classList.add('bnd-slide-aktif');
            kini = ke;

            if (hitung) { hitung.textContent = dua(kini + 1) + ' / ' + dua(slide.length); }

            muat(kini + 1 < slide.length ? kini + 1 : 0);
            ulangBar();
        }

        /* Garis kemajuannya disetel ulang dengan MEMBUANG lalu memasang
           kembali kelasnya, dan memaksa penataan ulang di antaranya.
           Tanpa itu peramban menganggap kelasnya tidak pernah berubah dan
           animasinya tidak mengulang dari nol. */
        function ulangBar() {
            if (! bar || jeda === null) { return; }

            bar.classList.remove('bnd-geser-bar-jalan');
            void bar.offsetWidth;
            bar.style.setProperty('--lama', (LAMA / 1000) + 's');
            bar.classList.add('bnd-geser-bar-jalan');
        }

        function mulai() {
            if (jeda !== null) { return; }

            jeda = setInterval(function () { tampilkan(kini + 1); }, LAMA);
            ulangBar();
        }

        function berhenti() {
            if (jeda === null) { return; }

            clearInterval(jeda);
            jeda = null;

            if (bar) { bar.classList.remove('bnd-geser-bar-jalan'); }
        }

        bingkai.querySelector('[data-bnd-maju]').addEventListener('click', function () {
            tampilkan(kini + 1);
        });
        bingkai.querySelector('[data-bnd-mundur]').addEventListener('click', function () {
            tampilkan(kini - 1);
        });

        /* Berhenti saat ditunjuk atau saat tombolnya dapat fokus papan
           ketik: foto yang berganti sendiri tepat ketika orangnya hendak
           menekan tombol adalah cara tercepat membuat sliderny menjengkelkan. */
        bingkai.addEventListener('mouseenter', berhenti);
        bingkai.addEventListener('mouseleave', function () { if (! diam.matches) { mulai(); } });
        bingkai.addEventListener('focusin', berhenti);

        /* Berhenti juga saat tabnya tidak terlihat. Tanpa ini, slidernya
           tetap berjalan di latar dan orangnya kembali ke foto acak —
           sekaligus memuat ketujuh belasnya tanpa pernah ditonton. */
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) { berhenti(); } else if (! diam.matches) { mulai(); }
        });

        muat(1);

        /* Yang meminta gerak minimal tidak dijalankan sendiri. Tombol maju
           dan mundurnya tetap bekerja, jadi ia tetap bisa melihat semuanya
           — hanya atas kehendaknya sendiri. */
        if (! diam.matches) { mulai(); }
    })();
</script>

<script>
    /*
     * Angka yang berhitung naik saat bagiannya masuk layar.
     *
     * Ditulis di sini, bukan memakai purecounter yang sudah dimuat layout:
     * pemisah ribuan purecounter koma dan tidak bisa diganti, sedangkan
     * Bahasa Indonesia memakai titik. Lima belas baris lebih murah daripada
     * menampilkan "5,000" di bagian yang isinya memang cuma angka.
     *
     * IntersectionObserver, bukan penyimak gulir: yang terakhir memanggil
     * fungsinya puluhan kali per detik selama halamannya digulung, dan
     * beranda ini panjang.
     */
    (function () {
        var angka = document.querySelectorAll('[data-bnd-hitung]');

        if (! angka.length || ! ('IntersectionObserver' in window)) {
            return;   // angkanya sudah tertulis utuh di markahnya
        }

        /* Yang meminta gerak minimal tidak dihitung-naikkan sama sekali —
           angka yang berkedip-kedip termasuk gerak yang dihindarinya. */
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        var tulis = function (el, nilai) {
            el.textContent = nilai.toLocaleString('id-ID');
        };

        var jalan = function (el) {
            var tujuan = parseInt(el.getAttribute('data-bnd-hitung'), 10);

            if (! isFinite(tujuan)) { return; }

            var mulai = null;
            var lama = 1600;

            var langkah = function (kini) {
                if (mulai === null) { mulai = kini; }

                var maju = Math.min((kini - mulai) / lama, 1);

                // Melambat di ujung: angka yang berhenti mendadak terbaca
                // seperti halaman yang tersendat.
                tulis(el, Math.round(tujuan * (1 - Math.pow(1 - maju, 3))));

                if (maju < 1) { requestAnimationFrame(langkah); }
            };

            requestAnimationFrame(langkah);
        };

        var pengamat = new IntersectionObserver(function (masuk) {
            masuk.forEach(function (satu) {
                if (! satu.isIntersecting) { return; }

                // Berhenti mengamati SEBELUM menghitung: tanpa itu, menggulung
                // naik-turun melewati bagian ini menjalankan hitungannya lagi
                // dari nol, dan angkanya terlihat seperti sedang berubah.
                pengamat.unobserve(satu.target);
                jalan(satu.target);
            });
        }, { threshold: .4 });

        angka.forEach(function (el) {
            tulis(el, 0);
            pengamat.observe(el);
        });
    })();
</script>
@stop
