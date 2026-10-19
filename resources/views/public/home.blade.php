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
        /* Kertasnya dihangatkan sedikit ke arah jingga (#fbf5e9 jadi
           #fdf5e8): latar krem netral membuat jingga di atasnya terbaca
           sebagai tempelan, bukan sebagai warna rumahnya sendiri. */
        --kertas: #fdf5e8;
        --kertas-2: #f8ead4;
        --tinta: #16233f;
        --tinta-2: #5c6b85;

        /* JINGGA LOGO — warna utama halaman ini.

           Bukan dikira-kira: diambil dari berkas logonya sendiri. Piksel
           paling menonjol pada LogoRSC.png yang terpasang di kepala situs
           adalah #e76d20, dan Icon-RS.png memulangkan #e86d20. Dua berkas
           berbeda, satu warna yang sama. */
        --jingga: #e76d20;
        --jingga-muda: #fb8c3d;

        /* Jingga yang lebih dalam, KHUSUS tombol berisi penuh.

           Tombol tulisannya kecil dan harus menonjol; latar jingga logo
           memaksa tulisannya bertinta (putih cuma berasio 3,18 di sana),
           dan tinta di atas jingga — walau lulus di angka 4,91 — terbaca
           kurang menonjol karena keduanya sama-sama gelap sedang.

           Pada #b85213 putih berasio 4,94: lulus dengan ruang lega, dan
           warnanya masih jelas jingga. Permukaan LEBAR tetap memakai jingga
           logo; yang butuh warna lebih dalam cuma tulisan kecil. */
        --jingga-kuat: #b85213;
        /* #ad4d12, bukan #c25613: dipakai sebagai TULISAN di atas kertas,
           dan pada #c25613 rasionya cuma 4,19 — di bawah ambang 4,5. */
        --jingga-tua: #ad4d12;

        /* Stabilo. Satu-satunya warna yang boleh menimpa tulisan, dan
           hanya untuk menandai — persis seperti stabilo sungguhan.
           Dipertahankan justru supaya jingganya tidak sendirian: halaman
           satu warna terbaca monoton, sekalipun warnanya benar. */
        --stabilo: #ffd84d;

        /* Warna penyerta, dipakai LEBIH JARANG daripada jingga. Dipilih
           yang tidak satu pun bertetangga di roda warna, supaya enam kartu
           berjajar tidak terbaca sebagai satu gradasi. */
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
    #layanan,
    #testimoni { scroll-margin-top: 96px; }

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
    /* Ikon stikernya jingga, bukan stabilo. Stiker muncul di tiap kepala
       bagian, jadi satu aturan ini sendirian menaruh jingga di enam tempat
       yang tersebar merata sepanjang halaman. */
    .bnd-stiker i { color: var(--jingga-muda); }

    /* --------------------------------------------------------- tombol */
    .bnd-tombol {
        display: inline-flex; align-items: center; gap: 10px;
        padding: 15px 28px; border: 2px solid var(--tinta); border-radius: 999px;
        font-family: 'Figtree', sans-serif; font-weight: 800; font-size: 1rem;
        text-decoration: none; cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease;
    }
    /* Putih di atas jingga yang LEBIH DALAM.

       Tiga pilihan, ketiganya terukur: putih di atas jingga logo #e76d20
       berasio 3,18 (gagal); tinta di atas jingga logo 4,91 (lulus, tetapi
       dua warna gelap sedang yang berdampingan terbaca kurang menonjol);
       putih di atas #b85213 berasio 4,94 — lulus DAN menonjol.

       Jadi yang digelapkan cuma latar TOMBOLNYA, bukan jingga halamannya.
       Permukaan lebar — pita ajakan, sorotan, penanda — tetap memakai
       jingga logo apa adanya. */
    .bnd-tombol-utama {
        background: var(--jingga-kuat); color: #fff;
        box-shadow: 5px 5px 0 var(--tinta);
    }
    .bnd-tombol-utama:hover {
        /* Latarnya tidak dimudakan saat disentuh: jingga yang lebih muda
           menjatuhkan rasio putihnya kembali di bawah ambang. Yang bergerak
           bayangannya. */
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
    /* Semburat jingga samar di belakang hero. Latar krem rata membuat
       tombol jingga di atasnya terbaca sebagai benda asing; dengan
       semburat ini jingganya terbaca sebagai warna ruangannya sendiri. */
    .bnd-hero {
        position: relative;
        padding: 124px 0 44px;
        background:
            radial-gradient(circle at 86% 6%, rgba(231, 109, 32, .17), transparent 48%),
            radial-gradient(circle at 2% 70%, rgba(255, 216, 77, .2), transparent 42%);
    }

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
        color: var(--jingga); opacity: 0;
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
        box-shadow: 10px 10px 0 var(--jingga-muda);
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
        color: var(--jingga-muda);
        font-family: 'Fraunces', serif; font-weight: 800; font-size: 1.3rem;
        line-height: 1;
        transition: background .22s ease, color .22s ease, border-color .22s ease, scale .22s ease;
    }
    /* Latarnya PEKAT, bukan tembus pandang: ia harus menutupi rel yang
       lewat persis di belakangnya. */
    .bnd-rute-henti:hover .bnd-rute-nomor {
        background: var(--jingga); color: #fff;
        border-color: var(--jingga); scale: 1.08;
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
        font-size: 4.4rem; line-height: .92; color: var(--jingga);
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

    /* Pemilihnya `.bnd-karcis-isi p.bnd-karcis-harga`, bukan
       `.bnd-karcis-harga` saja.

       Sebarisnya di atas — `.bnd-karcis-isi p` — berbobot (0,1,1); kelas
       tunggal cuma (0,1,0) dan KALAH, jadi ukuran hurufnya tetap .96rem dan
       seluruh jarak di bawah ini tidak berlaku. Tidak ada galat; angkanya
       cuma terlihat seperti kalimat biasa. Jebakan yang sama pernah membuat
       .bnd-sub tidak berjarak sama sekali. */
    .bnd-karcis-isi p.bnd-karcis-harga {
        display: flex; align-items: baseline; gap: 9px;
        margin-top: 15px; padding-top: 14px;
        border-top: 2px dashed rgba(231, 109, 32, .45);
        font-size: .88rem;
    }
    .bnd-karcis-harga span { color: var(--tinta-2); font-weight: 600; }
    .bnd-karcis-harga strong {
        font-family: 'Fraunces', serif; font-weight: 800; font-size: 1.24rem;
        color: var(--c);
    }

    /* Tombolnya didorong ke dasar kartu supaya semua tombol sebaris,
       berapa pun panjang keterangannya. */
    .bnd-karcis-tombol {
        display: inline-flex; align-items: center; gap: 9px;
        margin-top: auto; padding-top: 20px;
        color: var(--c); font-weight: 800; font-size: .97rem; text-decoration: none;
        transition: gap .2s ease;
    }
    .bnd-karcis:hover .bnd-karcis-tombol { gap: 15px; }

    /* ========================================================= jadwal */
    /* Dua kolom TAK SAMA, dan itu intinya: kolom kiri tetap 320px, daftarnya
       mengambil sisanya. Bagian lain halaman ini semuanya kisi simetris. */
    .bnd-jadwal-kisi {
        display: grid; grid-template-columns: 320px minmax(0, 1fr); gap: 52px;
        align-items: start;
    }

    /* Kepalanya ikut menggulung sampai daftarnya habis. Daftar empat baris
       lebih tinggi daripada kepalanya, dan tanpa ini kolom kiri jadi ruang
       kosong memanjang — persis keluhan "bagian tengah kosong" yang sudah
       pernah muncul di bagian testimoni. */
    @media (min-width: 993px) {
        .bnd-jadwal-kepala { position: sticky; top: 104px; }
    }
    .bnd-jadwal-kepala .bnd-tombol { margin-top: 24px; }

    .bnd-jadwal-deret { display: flex; flex-direction: column; gap: 16px; }

    .bnd-jadwal-baris {
        display: grid; grid-template-columns: 74px minmax(0, 1fr) auto;
        gap: 22px; align-items: center;
        padding: 17px 21px;
        background: var(--kertas);
        border: 2px solid var(--tinta); border-radius: 18px;
        box-shadow: 5px 5px 0 var(--c);
        color: var(--tinta); text-decoration: none;
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .bnd-jadwal-baris:hover {
        transform: translate(-3px, -3px);
        box-shadow: 9px 9px 0 var(--c);
        color: var(--tinta);
    }

    .bnd-jadwal-tgl {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: 9px 4px; border-radius: 13px;
        background: var(--c); color: #fff; line-height: 1;
    }
    .bnd-jadwal-tgl strong {
        font-family: 'Fraunces', serif; font-weight: 900; font-size: 1.68rem;
    }
    .bnd-jadwal-tgl span {
        margin-top: 4px; font-size: .72rem; font-weight: 800;
        letter-spacing: .1em; text-transform: uppercase;
    }

    .bnd-jadwal-isi { display: flex; flex-direction: column; gap: 5px; min-width: 0; }

    .bnd-jadwal-tanda {
        align-self: flex-start;
        padding: 2px 9px; border-radius: 999px;
        background: color-mix(in srgb, var(--c) 14%, transparent);
        color: var(--c);
        font-size: .71rem; font-weight: 800;
        letter-spacing: .07em; text-transform: uppercase;
    }
    .bnd-jadwal-nama {
        font-family: 'Fraunces', serif; font-weight: 800; font-size: 1.1rem;
        line-height: 1.3;
    }
    .bnd-jadwal-rinci {
        display: flex; flex-wrap: wrap; gap: 4px 16px;
        color: var(--tinta-2); font-size: .85rem; font-weight: 600;
    }
    .bnd-jadwal-rinci i { margin-right: 4px; color: var(--c); }

    .bnd-jadwal-kanan {
        display: flex; flex-direction: column; align-items: flex-end; gap: 3px;
        text-align: right; white-space: nowrap;
    }
    .bnd-jadwal-coret { color: var(--tinta-2); font-size: .8rem; font-weight: 600; }
    .bnd-jadwal-harga {
        font-family: 'Fraunces', serif; font-weight: 800; font-size: 1.08rem;
    }
    .bnd-jadwal-sisa {
        padding: 1px 9px; border-radius: 999px;
        background: var(--stabilo); color: var(--tinta);
        font-size: .72rem; font-weight: 800;
    }
    .bnd-jadwal-ajak {
        display: inline-flex; align-items: center; gap: 7px;
        margin-top: 3px; color: var(--c); font-weight: 800; font-size: .88rem;
        transition: gap .2s ease;
    }
    .bnd-jadwal-baris:hover .bnd-jadwal-ajak { gap: 13px; }

    @media (max-width: 992px) {
        .bnd-jadwal-kisi { grid-template-columns: 1fr; gap: 32px; }
    }
    @media (max-width: 600px) {
        .bnd-jadwal-baris {
            grid-template-columns: 58px minmax(0, 1fr);
            gap: 15px; padding: 15px 16px;
        }
        /* Kolom kanan turun ke bawah teksnya dan ikut lebar penuh, bukan
           menumpuk di kolom tanggal. */
        .bnd-jadwal-kanan {
            grid-column: 1 / -1;
            flex-direction: row; align-items: center; justify-content: flex-start;
            flex-wrap: wrap; gap: 6px 12px;
            padding-top: 11px; border-top: 2px dashed rgba(22, 35, 63, .14);
        }
        .bnd-jadwal-ajak { margin-top: 0; margin-left: auto; }
    }

    /* =========================================================== alat */
    .bnd-alat { background: var(--kertas); }

    .bnd-alat-kartu {
        display: flex; flex-direction: column;
        padding: 28px 26px 26px;
        background: var(--kertas-2);
        border: 2px solid var(--tinta); border-radius: 20px;
        box-shadow: 6px 6px 0 var(--c);
        color: var(--tinta); text-decoration: none;
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .bnd-alat-kartu:hover {
        transform: translate(-3px, -3px);
        box-shadow: 10px 10px 0 var(--c);
        color: var(--tinta);
    }
    .bnd-alat-ikon {
        display: inline-flex; align-items: center; justify-content: center;
        width: 52px; height: 52px; margin-bottom: 17px;
        border-radius: 15px; background: var(--c); color: #fff; font-size: 1.4rem;
    }
    .bnd-alat-kartu h3 {
        font-family: 'Fraunces', serif; font-weight: 800; font-size: 1.15rem;
        margin-bottom: 9px;
    }
    .bnd-alat-kartu p { font-size: .94rem; }
    .bnd-alat-ajak {
        display: inline-flex; align-items: center; gap: 8px;
        margin-top: auto; padding-top: 19px;
        color: var(--c); font-weight: 800; font-size: .95rem;
        transition: gap .2s ease;
    }
    .bnd-alat-kartu:hover .bnd-alat-ajak { gap: 14px; }

    /* Alatnya tinggal DUA sesudah Cek Plagiasi dibuang, dan .bnd-kisi-3
       tetap memesan tiga kolom — terukur 441px kosong menganga di kanan,
       membuat bagiannya terbaca seperti ada kartu yang gagal dimuat.

       DI DALAM @media (min-width: 992px), bukan berdiri sendiri. Timpaan
       telanjang `.bnd-alat .bnd-kisi-3` berbobot (0,2,0) dan akan MENANG
       melawan `.bnd-kisi-3` (0,1,0) di dalam media ponsel — dua kolom
       selebar 420px bertahan sampai layar 390px dan meluber keluar. */
    @media (min-width: 992px) {
        .bnd-alat .bnd-kisi-3 {
            grid-template-columns: repeat(2, minmax(0, 420px));
            justify-content: center;
        }
    }

    /* ======================================================== bacaan */
    .bnd-baca {
        display: flex; flex-direction: column;
        background: var(--kertas);
        border: 2px solid var(--tinta); border-radius: 20px;
        box-shadow: 6px 6px 0 var(--jingga-muda);
        color: var(--tinta); text-decoration: none; overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .bnd-baca:hover {
        transform: translate(-3px, -3px);
        box-shadow: 10px 10px 0 var(--jingga-muda);
        color: var(--tinta);
    }
    .bnd-baca-gambar { display: block; aspect-ratio: 16 / 10; overflow: hidden; }
    .bnd-baca-gambar img {
        width: 100%; height: 100%; object-fit: cover; display: block;
        transition: scale .4s ease;
    }
    .bnd-baca:hover .bnd-baca-gambar img { scale: 1.07; }

    .bnd-baca-isi {
        display: flex; flex-direction: column; flex: 1 1 auto;
        padding: 20px 22px 22px;
        border-top: 2px solid var(--tinta);
    }
    .bnd-baca-tanggal {
        color: var(--tinta-2); font-size: .79rem; font-weight: 700;
        letter-spacing: .05em; text-transform: uppercase;
    }
    .bnd-baca-isi h3 {
        margin: 7px 0 0;
        font-family: 'Fraunces', serif; font-weight: 800; font-size: 1.08rem;
        line-height: 1.35;
    }
    .bnd-baca-ajak {
        display: inline-flex; align-items: center; gap: 8px;
        margin-top: auto; padding-top: 17px;
        color: var(--jingga-tua); font-weight: 800; font-size: .92rem;
        transition: gap .2s ease;
    }
    .bnd-baca:hover .bnd-baca-ajak { gap: 14px; }

    /* =========================================================== faq */
    /* Dua kolom supaya enam pertanyaan tidak jadi menara setinggi layar. */
    .bnd-faq-deret {
        display: grid; grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px 20px; align-items: start;
    }

    .bnd-faq-butir {
        background: var(--kertas);
        border: 2px solid var(--tinta); border-radius: 17px;
        box-shadow: 4px 4px 0 rgba(22, 35, 63, .13);
        overflow: hidden;
    }
    .bnd-faq-butir summary {
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
        padding: 17px 20px;
        font-family: 'Fraunces', serif; font-weight: 800; font-size: 1.01rem;
        cursor: pointer; list-style: none;
    }
    /* Segitiga bawaan dibuang di kedua mesin peramban; penandanya ikon kita
       sendiri yang berputar jadi silang saat terbuka. */
    .bnd-faq-butir summary::-webkit-details-marker { display: none; }
    .bnd-faq-butir summary::marker { content: ''; }

    .bnd-faq-butir summary i {
        flex: 0 0 auto;
        display: inline-flex; align-items: center; justify-content: center;
        width: 28px; height: 28px; border-radius: 9px;
        background: var(--jingga); color: #fff; font-size: .82rem;
        transition: rotate .22s ease;
    }
    .bnd-faq-butir[open] summary i { rotate: 45deg; }
    .bnd-faq-butir[open] { box-shadow: 4px 4px 0 var(--jingga-muda); }

    .bnd-faq-butir p {
        margin: 0; padding: 0 20px 19px;
        font-size: .95rem;
    }

    @media (max-width: 860px) {
        .bnd-faq-deret { grid-template-columns: 1fr; }
    }

    /* ================================================= ajakan ponsel */
    .bnd-pita-hp { display: none; }

    @media (max-width: 767px) {
        .bnd-pita-hp {
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 99998;
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
            background: var(--kertas);
            border-top: 2px solid var(--tinta);
            box-shadow: 0 -4px 18px rgba(22, 35, 63, .12);
        }
        .bnd-pita-utama {
            flex: 1 1 auto;
            display: inline-flex; align-items: center; justify-content: center; gap: 9px;
            padding: 13px 18px;
            background: var(--jingga-kuat); color: #fff;
            border: 2px solid var(--tinta); border-radius: 999px;
            font-weight: 800; font-size: .97rem; text-decoration: none;
        }
        .bnd-pita-utama:hover, .bnd-pita-utama:focus { color: #fff; }
        .bnd-pita-wa {
            flex: 0 0 auto;
            display: inline-flex; align-items: center; justify-content: center;
            width: 48px; height: 48px;
            background: #16a34a; color: #fff;
            border: 2px solid var(--tinta); border-radius: 15px;
            font-size: 1.3rem; text-decoration: none;
        }
        .bnd-pita-wa:hover, .bnd-pita-wa:focus { color: #fff; }

        /* Halaman diberi ruang ekstra supaya pita ini tidak menutupi baris
           terakhir kaki halaman. */
        body { padding-bottom: 76px; }

        /* Tombol mengambang milik tata letak dinaikkan ke atas pita.
           Tombol WhatsApp mengambangnya DISEMBUNYIKAN — pita ini sudah
           memuatnya, dan dua tombol WhatsApp di satu layar membuat orang
           mengira keduanya mengarah ke tempat berbeda. */
        .kaki-wa { display: none !important; }
        .back-to-top { bottom: 86px !important; }
    }

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
    /* TIGA kolom, dan kutipannya menumpuk di bawah rekaman TENGAH.

       Dua rekaman tegak 540x960 jadi tinggi; yang tengah tersimpan rebah
       960x540 jadi jauh lebih pendek — dan ruang kosong di bawahnya itulah
       yang dulu menganga. Kutipannya dinaikkan ke situ: lubangnya terisi,
       dan ketiga lajurnya berakhir di garis yang sama.

       Sempat dicoba memotong rekaman rebahnya jadi tegak supaya ketiganya
       setinggi sama. Itu menghilangkan lubangnya, tetapi memperkecil orang
       yang sedang berbicara dan membuang bagian gambarnya — padahal
       lubangnya bisa diisi oleh sesuatu yang memang perlu tempat. */
    .bnd-testi-kisi {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 28px; align-items: stretch; justify-items: center;
    }

    /* Lajur tengah: rekaman di atas, kutipan mengisi sisanya sampai dasar. */
    .bnd-testi-tengah {
        display: flex; flex-direction: column; gap: 22px;
        width: 100%; height: 100%;
    }
    .bnd-testi-tengah > .bnd-polaroid { align-self: center; }

    /* Polaroid di lajur kiri dan kanan MEREGANG ke tinggi baris. Selisihnya
       jatuh sebagai bidang putih di bawah keterangannya — dan itu kebetulan
       persis tepi bawah polaroid sungguhan, yang memang lebih tebal daripada
       ketiga sisi lainnya. */
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
    /* Kemiringan dan warna bayangannya berbeda-beda: tiga polaroid bersudut
       sama terbaca sebagai cetakan, bukan sebagai foto yang ditempel orang.

       Diberikan lewat VARIABEL di markahnya, bukan :nth-child. Rekaman
       tengah dibungkus bersama kutipannya, jadi ia anak PERTAMA dari
       pembungkus itu — dengan :nth-child ia diam-diam memakai warna milik
       polaroid pertama, dan ketiganya jadi dua ungu satu bata. */
    .bnd-polaroid {
        transform: rotate(var(--putar, 0deg));
        box-shadow: 8px 8px 0 var(--bayang, var(--ungu));
    }

    /* Tiap rekaman memakai perbandingan sisinya SENDIRI, tidak dipotong.
       Yang tegak jadi sempit dan tinggi, yang rebah jadi lebar dan pendek —
       dan ruang kosong di bawah yang rebah diisi kutipannya.

       620px, bukan 420: tinggi barisnya ditentukan lajur TENGAH (rekaman
       pendek + kutipan = 705px terukur). Pada 420px, kedua lajur tepi
       meregang ke 705 dan menyisakan 220px bidang putih kosong di bawah
       keterangannya — ruang kosong yang cuma berpindah tempat, bukan
       hilang. */
    .bnd-polaroid video {
        display: block; max-height: 620px; width: auto; max-width: 100%;
        border: 2px solid var(--tinta); background: var(--tinta);
    }

    .bnd-polaroid-kaki {
        margin-top: 12px; text-align: center;
        font-family: 'Fraunces', serif; font-weight: 700; font-size: .88rem;
    }

    /* Kutipannya mengisi sisa lajur tengah sampai dasar. flex: 1 — bukan
       tinggi yang dipatok: tinggi sisanya berbeda di tiap lebar layar, dan
       angka yang pas di 1440 akan meleset di 1200. */
    .bnd-testi-kutip {
        flex: 1 1 auto; width: 100%; margin: 0;
        display: flex; flex-direction: column; justify-content: center;
        padding: 22px 22px;
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
    /* Blok terbesar di halaman, dan sekarang JINGGA — ini yang membuat
       jingganya terbaca dominan, bukan sekadar sering muncul.

       Dulu stabilo. Kuning sebesar ini menarik mata lebih kuat daripada
       seluruh jingga di atasnya digabung, jadi warna merek yang sebenarnya
       justru kalah oleh penanda. */
    .bnd-ajakan {
        position: relative; overflow: hidden;
        padding: 62px 48px; text-align: center;
        background:
            radial-gradient(circle at 88% 10%, rgba(255, 216, 77, .34), transparent 46%),
            linear-gradient(118deg, var(--jingga) 0%, var(--jingga-muda) 100%);
        border: 3px solid var(--tinta); border-radius: 26px;
        box-shadow: 10px 10px 0 var(--tinta);
    }
    /* Tinta, bukan putih — alasannya sama seperti pada tombol utama, dan
       di sini taruhannya lebih besar: paragraf ini yang paling panjang di
       halaman dan dibaca orang yang sedang memutuskan. */
    .bnd-ajakan h2 { font-size: 2.5rem; line-height: 1.12; color: var(--tinta); }
    .bnd-ajakan p {
        margin: 16px auto 0; max-width: 52ch;
        color: rgba(22, 35, 63, .88); font-size: 1.08rem;
    }
    .bnd-ajakan .bnd-aksi { justify-content: center; }
    /* Tombol utamanya DIBALIK jadi kertas di atas jingga: tombol jingga di
       atas latar jingga hilang bentuknya. */
    .bnd-ajakan .bnd-tombol-utama { background: var(--kertas); color: var(--jingga-tua); }
    .bnd-ajakan .bnd-tombol-utama:hover { color: var(--jingga-tua); }
    .bnd-ajakan .bnd-tombol-kedua {
        background: transparent; color: var(--tinta);
        border-color: var(--tinta);
        box-shadow: 5px 5px 0 rgba(22, 35, 63, .3);
    }
    .bnd-ajakan .bnd-tombol-kedua:hover {
        color: var(--tinta); background: var(--kertas);
        box-shadow: 8px 8px 0 rgba(22, 35, 63, .3);
    }

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
        /* SATU lajur, bukan dua.

           Isinya tiga rekaman ditambah satu kutipan yang menumpuk pada
           rekaman tengah — jumlah yang tidak terbagi rata ke dua lajur.
           Terukur pada 991px: polaroidnya 686 / 234 / 406, tiga tinggi
           berbeda dalam satu kisi yang seharusnya rapi. */
        .bnd-testi-kisi { grid-template-columns: minmax(0, 1fr); gap: 34px; }
        .bnd-polaroid video { max-height: 420px; }
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
        .bnd-polaroid video { max-height: 320px; }
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
                        {{-- Menuju bagian testimoni di halaman ini, BUKAN ke
                             YouTube. Tombol di hero yang membuka tab lain
                             mengirim orang keluar sebelum ia sempat melihat
                             satu pun layanan — dan rekamannya memang sudah ada
                             di halaman ini, beberapa gulir di bawah.

                             Tautan ke kanal YouTube-nya tidak hilang: ia ada
                             di kaki situs bersama ikon sosial lainnya. --}}
                        <a href="#testimoni" class="bnd-tombol bnd-tombol-kedua">
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
                        <i class="bi bi-mortarboard-fill" style="color: var(--jingga)"></i>
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

    {{-- ================================================= jadwal ======= --}}
    {{-- Bagian ini TIDAK ADA sebelumnya, dan itu lubang terbesar beranda.

         Pengendalinya dulu memulangkan view tanpa satu pun data, sementara
         233 angkatan menunggu dibuka di basis data. Orang yang datang ingin
         tahu dua hal — KAPAN dan BERAPA — lalu harus menebaknya dengan
         membuka enam halaman layanan satu-satu.

         Tata letaknya SENGAJA asimetris: kolom kiri tetap, daftar di kanan.
         Enam bagian lain halaman ini berirama sama persis — pita selebar
         layar, stiker, judul tengah, lalu kisi simetris — dan sesudah bagian
         ketiga mata sudah bisa menebaknya. Satu bagian yang bentuknya lain
         memutus hafalan itu.

         Kosong berarti HILANG, bukan "belum ada jadwal": pita kosong yang
         mengumumkan ketiadaan lebih buruk daripada tidak bertanya. --}}
    @if ($sesi->isNotEmpty())
        <section class="bnd-bagian bnd-jadwal" id="jadwal">
            <div class="container">
                <div class="bnd-jadwal-kisi">
                    <div class="bnd-jadwal-kepala" data-aos="fade-right">
                        <span class="bnd-stiker"><i class="bi bi-calendar-event-fill"></i> Jadwal terdekat</span>
                        <h2 class="bnd-judul">Yang paling cepat jalan</h2>
                        <p class="bnd-sub">
                            @if ($jumlah_sesi > count($sesi))
                                Ada {{ $jumlah_sesi }} kelas yang sedang dibuka. Ini
                                {{ count($sesi) }} yang paling dekat —
                            @else
                                Ini kelas yang sedang dibuka —
                            @endif
                            tanggalnya sudah pasti, kursinya yang nggak.
                        </p>

                        <a href="#layanan" class="bnd-tombol bnd-tombol-kedua">
                            Lihat semua kelas <i class="bi bi-arrow-down"></i>
                        </a>
                    </div>

                    <div class="bnd-jadwal-deret">
                        @foreach ($sesi as $i => $k)
                            @php($warna = [
                                'scopus_camp' => 'var(--jingga)',
                                'webinar_eksklusif' => '#be185d',
                                'bibliometrik' => 'var(--rumput)',
                                'scopus_kafe' => 'var(--ungu)',
                                'clinik_scopus' => '#b45309',
                            ][$k['layanan']] ?? 'var(--langit)')

                            <a href="{{ $k['tautan'] }}" class="bnd-jadwal-baris"
                                style="--c: {{ $warna }}"
                                data-aos="fade-up" data-aos-delay="{{ $i * 90 }}">

                                <span class="bnd-jadwal-tgl">
                                    <strong>{{ $k['tgl'] }}</strong>
                                    <span>{{ $k['bulan'] }}</span>
                                </span>

                                <span class="bnd-jadwal-isi">
                                    <span class="bnd-jadwal-tanda">{{ $k['layanan_nama'] }}</span>
                                    <span class="bnd-jadwal-nama">{{ $k['judul'] }}</span>

                                    <span class="bnd-jadwal-rinci">
                                        <span><i class="bi bi-calendar3"></i> {{ $k['hari'] }}, {{ $k['tanggal'] }}</span>

                                        @if ($k['jam'])
                                            <span><i class="bi bi-clock"></i> {{ $k['jam'] }}</span>
                                        @endif

                                        @if ($k['tempat'])
                                            <span>
                                                <i class="bi {{ $k['daring'] ? 'bi-camera-video' : 'bi-geo-alt' }}"></i>
                                                {{ $k['tempat'] }}
                                            </span>
                                        @endif

                                        {{-- Pematerinya muncul di sini, dan itu disengaja: kolom
                                             pemateri + jabatan + fotonya sudah lama terisi admin
                                             dan tidak pernah sampai ke beranda. Orang membeli
                                             orang, bukan nama kelas. --}}
                                        @if ($k['pemateri'])
                                            <span><i class="bi bi-person-badge"></i> {{ $k['pemateri'] }}</span>
                                        @endif
                                    </span>
                                </span>

                                <span class="bnd-jadwal-kanan">
                                    {{-- Harga nol DIAM, tidak jadi "Rp 0". --}}
                                    @if ($k['harga'] > 0)
                                        @if ($k['harga_penuh'])
                                            <s class="bnd-jadwal-coret">Rp {{ number_format($k['harga_penuh'], 0, ',', '.') }}</s>
                                        @endif
                                        <span class="bnd-jadwal-harga">Rp {{ number_format($k['harga'], 0, ',', '.') }}</span>
                                    @endif

                                    {{-- Sisa kursi disebut hanya kalau tinggal sedikit.
                                         "20 kursi" tidak mendesak siapa pun; "sisa 3 kursi"
                                         iya — dan menyebutnya saat masih longgar membuat
                                         angka itu kehilangan arti waktu benar-benar sempit. --}}
                                    @if ($k['sisa'] !== null && $k['sisa'] <= 5)
                                        <span class="bnd-jadwal-sisa">sisa {{ $k['sisa'] }} kursi</span>
                                    @endif

                                    <span class="bnd-jadwal-ajak">Lihat <i class="bi bi-arrow-right"></i></span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

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
                <div class="bnd-kartu" style="--c: var(--jingga)" data-aos="fade-up">
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
            {{-- HARGANYA dari $harga, bukan ditulis di sini.

                 Sebelum ini beranda tidak menyebut satu angka pun — kata
                 "Rp" nol kali di seluruh halaman — padahal tarif tiap
                 layanan sudah diurus admin di satu tabel. Harga yang
                 disembunyikan membuat orang menutup halaman, bukan
                 menanyakannya; dan yang betul-betul mau tahu harus membuka
                 enam halaman layanan satu-satu.

                 Kuncinya kode layanan yang SAMA dengan katalog Layanan dan
                 kolom `layanan` di kategori_layanan. Yang tarif berlakunya
                 belum diisi — Online Training — kartunya diam saja, bukan
                 memajang "mulai dari Rp 0". --}}
            <div class="bnd-kisi-3">
                @foreach ([
                    ['scopus_camp', 'var(--jingga)', 'HomeScopusCamp.png', 'Offline · 3 hari', 'Scopus Camp', 'Tiga hari ketemu langsung di Jogja, naskahmu dibedah sampai kelar. Habis itu masih ditemani setahun penuh.', url('/Scopus-Camp')],
                    ['online_training', 'var(--langit)', 'HomeOnlineTraining.png', 'Online · 8 sesi', 'Online Training', 'Delapan kali ketemu daring bareng trainer. Cocok buat yang susah ninggalin kampus.', 'https://rumahscopus.com/courses/online-class/'],
                    ['scopus_kafe', 'var(--ungu)', 'HomeScopusKafe.png', 'Privat · 5 jam', 'Scopus Kafe', 'Lima jam berdua sama trainer. Buat naskah yang sudah jalan tapi mentok di satu bagian.', url('/Scopus-Kafe')],
                    ['bibliometrik', 'var(--rumput)', 'HomeBibliometrik.png', 'Online · 8 sesi', 'Analisis Bibliometrik', 'Belajar baca peta riset: mana yang sudah ramai, mana celah yang belum digarap orang.', url('/Analisis-Bibliometrik')],
                    ['clinik_scopus', '#b45309', 'HomeScopusClinic.png', 'Chat · 50 menit', 'Clinik Scopus', 'Lagi mentok satu pertanyaan saja? Ngobrol lewat chat lima puluh menit, langsung dapat jalan keluarnya.', url('/Clinik-Scopus')],
                    ['webinar_eksklusif', '#be185d', null, 'Daring · kuota terbatas', 'Webinar Eksklusif', 'Sesi daring bertema khusus, dibuka berkala. Kuotanya sedikit, jadi jangan kelamaan mikir.', url('/Webinar-Eksklusif')],
                ] as $i => [$kode, $warna, $gambar, $tag, $judul, $ket, $tautan])
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

                            @if (($harga[$kode] ?? 0) > 0)
                                <p class="bnd-karcis-harga">
                                    <span>mulai dari</span>
                                    <strong>Rp {{ number_format($harga[$kode], 0, ',', '.') }}</strong>
                                </p>
                            @endif

                            <a href="{{ $tautan }}" class="bnd-karcis-tombol">
                                Intip detailnya <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ==================================================== alat ====== --}}
    {{-- Kedua halaman ini SUDAH ADA dan sama-sama memulangkan 200, tapi tidak
         pernah disebut beranda sama sekali.

         Dipisah dari kartu layanan, bukan dijejalkan ke sana, karena memang
         bukan barang yang sama: yang di atas dijual, yang di sini dipakai
         tanpa bayar dan tanpa daftar. Mencampurnya membuat orang mengira
         salah satunya jebakan.

         Cek Plagiasi dulu jadi kartu ketiga di sini dan DIBUANG 19 Okt 2026.
         Bukan sekadar tidak terpakai: tombol kirimnya memanggil
         FileUpload::create(), dan kelas FileUpload tidak pernah ada di
         proyek ini — tidak di App, tidak di App\Models, tidak di mana pun.
         Setiap naskah yang dikirim orang berakhir di galat 500 sesudah
         berkasnya telanjur tersimpan. --}}
    <section class="bnd-bagian bnd-alat">
        <div class="container">
            <div class="bnd-kepala bnd-kepala-tengah" data-aos="fade-up">
                <span class="bnd-stiker" style="background: var(--rumput); color: #fff;">
                    <i class="bi bi-tools"></i> Gratis
                </span>
                <h2 class="bnd-judul">Dipakai dulu, nggak usah bayar</h2>
                <p class="bnd-sub">
                    Dua alat yang boleh dipakai siapa saja — nggak perlu daftar,
                    nggak perlu login.
                </p>
            </div>

            <div class="bnd-kisi-3">
                @foreach ([
                    ['bi-search', 'var(--rumput)', 'Refrensi Paper',
                     $jumlah_referensi > 0
                        ? 'Telusuri ' . number_format($jumlah_referensi, 0, ',', '.') . ' paper yang sudah kami kumpulkan. Buat cari pembanding atau bahan sitasi.'
                        : 'Telusuri kumpulan paper kami buat cari pembanding atau bahan sitasi.',
                     url('/Refrensi-Paper'), 'Cari paper'],
                    ['bi-upc-scan', 'var(--ungu)', 'Cek ID Paper',
                     'Sudah pernah ikut kelas kami? Lacak nomor paper-mu di sini, tanpa perlu tanya admin dulu.',
                     url('/paperisasi/public/data'), 'Lacak ID'],
                ] as $i => [$ikon, $warna, $judul, $ket, $tautan, $ajak])
                    <a href="{{ $tautan }}" class="bnd-alat-kartu" style="--c: {{ $warna }}"
                        data-aos="fade-up" data-aos-delay="{{ $i * 110 }}">
                        <span class="bnd-alat-ikon"><i class="bi {{ $ikon }}"></i></span>
                        <h3>{{ $judul }}</h3>
                        <p>{{ $ket }}</p>
                        <span class="bnd-alat-ajak">{{ $ajak }} <i class="bi bi-arrow-right"></i></span>
                    </a>
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
                    <i class="bi bi-bar-chart-fill" style="color: var(--jingga)"></i> Angkanya
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
            {{-- Angkanya pindah ke config/beranda.php.

                 Bukan ke basis data, dan itu disengaja: yang tercatat di
                 sistem ini cuma 136 pendaftaran Scopus Camp: aplikasinya baru,
                 yayasannya tidak. Menghitungnya dari tabel akan mengubah
                 "5.000 orang" jadi "153 orang" — bukan membuatnya jujur,
                 melainkan menghapus kerja sepuluh tahun yang memang terjadi
                 tapi tidak lewat sistem ini. Alasan lengkapnya ada di
                 config/beranda.php. --}}
            <div class="bnd-kisi-angka">
                @foreach (config('beranda.angka') as $i => $angka)
                    @php($ikon = $angka['ikon'])
                    @php($nilai = (int) $angka['nilai'])
                    @php($ket = $angka['ket'])
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
    <section class="bnd-bagian" id="testimoni" style="background: var(--kertas-2);">
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
            {{-- Ketiga rekaman ini SAMA PERSIS dengan yang dipakai halaman
                 iklan Webinar Eksklusif, dan sengaja diambil dari tempat yang
                 sama: storage/video di MIS. Halaman iklan itu repo terpisah
                 yang memuatnya lewat alamat ini juga — satu berkas, dua
                 halaman, jadi mengganti rekamannya cukup sekali. --}}
            @php($rekaman = [
                ['testimoni', 'testimoni-sampul', '-1.8', 'var(--ungu)'],
                ['testimoni2-web', 'testimoni2-sampul', '1.2', 'var(--rumput)'],
                ['testimoni3-web', 'testimoni3-sampul', '-.9', 'var(--jingga)'],
            ])

            <div class="bnd-testi-kisi">
                @foreach ($rekaman as $i => [$berkas, $sampul, $putar, $bayang])
                    {{-- Rekaman TENGAH dibungkus bersama kutipannya: ia yang
                         tersimpan rebah, jadi paling pendek, dan ruang kosong
                         di bawahnyalah yang diisi kutipan itu. --}}
                    @if ($i === 1)
                        <div class="bnd-testi-tengah" data-aos="fade-up" data-aos-delay="110">
                    @endif

                    <figure class="bnd-polaroid" style="--putar: {{ $putar }}deg; --bayang: {{ $bayang }}"
                        @if ($i !== 1) data-aos="fade-up" data-aos-delay="{{ $i * 220 }}" @endif>
                        {{-- preload="none", BUKAN "metadata".

                             Ketiganya berjumlah 94 MB. Dengan "metadata" saja
                             peramban tetap membuka sambungan ke tiap berkas
                             untuk membaca kepalanya — tiga permintaan ke berkas
                             puluhan megabita, di halaman depan, untuk rekaman
                             yang belum tentu ditonton.

                             Sampulnya yang menggantikan bingkai pertama, dan
                             itu sebabnya tiap rekaman punya berkas -sampul
                             sendiri. --}}
                        {{-- controlsList="nodownload": membuang butir "Download"
                             dari menu tiga titik bawaan Chrome.

                             INI TIDAK MENCEGAH UNDUHAN, dan jangan sampai
                             dikira begitu. Alamat berkasnya tetap ada di
                             sumber halaman, dan siapa pun yang membuka
                             DevTools — atau sekadar menempelkan alamatnya di
                             bilah alamat — tetap mendapatkannya utuh.

                             Yang dicabut cuma jalan yang paling mudah: satu
                             tekanan di menu yang kebetulan ada di depan mata.
                             Mencegah sungguhan butuh alamat bertanda tangan
                             yang kedaluwarsa, potongan HLS beralas token,
                             atau DRM — dan ketiganya pun tidak mutlak.

                             Tidak didukung Firefox maupun Safari; di sana
                             menu unduhnya memang tidak ada di tempat yang
                             sama, tetapi "Simpan video sebagai…" lewat klik
                             kanan tetap bisa. --}}
                        <video controls playsinline preload="none"
                            controlsList="nodownload"
                            poster="{{ asset('storage/video/' . $sampul . '.webp') }}"
                            aria-label="Testimoni peserta Rumah Scopus">
                            <source src="{{ asset('storage/video/' . $berkas . '.mp4') }}" type="video/mp4">
                            Peramban kamu belum bisa memutar video ini.
                        </video>
                        <figcaption class="bnd-polaroid-kaki">Peserta Rumah Scopus</figcaption>
                    </figure>

                    @if ($i === 1)
                            <div class="bnd-balon bnd-testi-kutip">
                                <span class="bnd-balon-kutip">&ldquo;</span>
                                <p>
                                    Awalnya pesimis banget. Rasanya Scopus itu buat orang
                                    lain, bukan buat saya. Tiga hari di Jogja ternyata cukup
                                    untuk mengubah cara saya memandang naskah sendiri.
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
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================================================== artikel ===== --}}
    {{-- Blognya hidup — 12 tulisan terbit — dan beranda tidak menautkannya
         satu kali pun, bahkan tidak ke /blog. Orang yang belum siap membeli
         tidak punya apa pun untuk dibaca sebelum pergi. --}}
    @if ($artikel->isNotEmpty())
        <section class="bnd-bagian" style="background: var(--kertas-2);">
            <div class="container">
                <div class="bnd-kepala bnd-kepala-tengah" data-aos="fade-up">
                    <span class="bnd-stiker"><i class="bi bi-journal-text"></i> Bacaan</span>
                    <h2 class="bnd-judul">Belum siap daftar? Baca dulu</h2>
                    <p class="bnd-sub">
                        Tulisan-tulisan soal nulis artikel, cari jurnal, dan hal-hal
                        yang biasanya bikin naskah mandek.
                    </p>
                </div>

                <div class="bnd-kisi-3">
                    @foreach ($artikel as $i => $a)
                        <a href="{{ route('blog.topic.blog-single', ['id' => $a->id]) }}"
                            class="bnd-baca" data-aos="fade-up" data-aos-delay="{{ $i * 110 }}">
                            <span class="bnd-baca-gambar">
                                {{-- loading="lazy" aman di sini: kartunya di bawah layar
                                     pertama dan TIDAK berada di dalam apa pun yang
                                     opacity-0 — penundaannya betul-betul jalan. --}}
                                <img src="{{ asset('images/' . $a->gambar_depan) }}"
                                    alt="{{ $a->judul }}" loading="lazy" decoding="async">
                            </span>
                            <span class="bnd-baca-isi">
                                <span class="bnd-baca-tanggal">
                                    {{ \Carbon\Carbon::parse($a->created_at)->locale('id')->translatedFormat('j M Y') }}
                                </span>
                                <h3>{{ $a->judul }}</h3>
                                <span class="bnd-baca-ajak">Baca <i class="bi bi-arrow-right"></i></span>
                            </span>
                        </a>
                    @endforeach
                </div>

                <div class="bnd-aksi" style="justify-content: center; margin-top: 38px;">
                    <a href="{{ url('/blog') }}" class="bnd-tombol bnd-tombol-kedua">
                        Semua tulisan <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- ====================================================== faq ====== --}}
    {{-- Dipakai <details>, BUKAN akordeon ber-JavaScript.

         Buka-tutupnya jadi urusan peramban: tetap jalan kalau skripnya gagal
         dimuat, bisa dicari lewat Ctrl+F karena isinya tetap di DOM, dan
         pembaca layar sudah tahu membacanya tanpa atribut aria tambahan.

         Jawabannya diambil dari daftar fasilitas yang BENAR-BENAR tersimpan
         di tarif tiap layanan, bukan dikarang: sertifikat, rekaman, dan
         pendampingan lanjutan semuanya ada di sana. --}}
    <section class="bnd-bagian bnd-faq" id="faq">
        <div class="container">
            <div class="bnd-kepala bnd-kepala-tengah" data-aos="fade-up">
                <span class="bnd-stiker" style="background: var(--ungu); color: #fff;">
                    <i class="bi bi-patch-question-fill"></i> Sering ditanya
                </span>
                <h2 class="bnd-judul">Yang biasanya ditanyain duluan</h2>
            </div>

            <div class="bnd-faq-deret">
                @foreach ([
                    ['Naskah saya belum jadi. Boleh ikut?',
                     'Boleh. Yang penting datanya sudah ada — menulisnya justru yang dikerjakan bareng di kelas. Kalau datanya juga belum, cerita dulu saja ke admin, nanti dicarikan kelas yang paling masuk akal buat kondisimu. Nanyanya gratis.'],
                    ['Dapat sertifikat, nggak?',
                     'Dapat. Scopus Camp dapat sertifikat plus perlengkapan peserta, webinar dapat e-sertifikat resmi atas nama peserta, dan kelas bibliometrik dapat sertifikat plus rekaman kelasnya.'],
                    ['Selesai kelas terus ditinggal gitu aja?',
                     'Nggak. Di Scopus Camp pendampingannya jalan terus sampai jurnalnya memberi jawaban — termasuk pas kamu harus benerin revisi dari reviewer.'],
                    ['Harus datang ke Jogja?',
                     'Nggak semua. Scopus Camp memang ketemu langsung, tapi webinar, Analisis Bibliometrik, dan Scopus Kafe punya kelas daring. Tempat atau platform-nya ditulis di tiap jadwal di atas.'],
                    ['Kuotanya keburu habis gimana?',
                     'Jadwalnya dibuka berkala, jadi hampir selalu ada angkatan berikutnya. Biar nggak kelewatan, kabari admin kelas mana yang kamu incar — nanti dikabari waktu dibuka.'],
                    ['Bayarnya gimana?',
                     'Pilih kelasnya, isi formulir pendaftaran, lalu bayar lewat jalur yang muncul di layar. Bukti pendaftaranmu dikirim ke email yang kamu isi, jadi pastikan alamatnya benar.'],
                ] as $i => [$tanya, $jawab])
                    <details class="bnd-faq-butir" data-aos="fade-up" data-aos-delay="{{ ($i % 3) * 90 }}">
                        <summary>
                            <span>{{ $tanya }}</span>
                            <i class="bi bi-plus-lg"></i>
                        </summary>
                        <p>{{ $jawab }}</p>
                    </details>
                @endforeach
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

    {{-- =============================================== ajakan ponsel ==

         Sesudah layar pertama, tombol daftarnya 6.000px jauhnya — terukur
         tinggi halaman ini 6.314px. Di ponsel itu berarti orang yang sudah
         yakin di tengah halaman harus menggulung balik ke atas atau terus ke
         bawah untuk menemukan jalan masuknya.

         Hanya di beranda, dan hanya di ponsel: di layar lebar menunya selalu
         kelihatan, jadi pita ini cuma akan menutupi isi halaman tanpa
         menambah apa pun. --}}
    <div class="bnd-pita-hp">
        <a href="#layanan" class="bnd-pita-utama">
            Lihat kelas <i class="bi bi-arrow-right"></i>
        </a>
        <a href="{{ \App\Support\TautanWa::kirim(config('panitia.whatsapp'), 'Halo, saya mau tanya soal kelas Rumah Scopus.') }}"
            class="bnd-pita-wa" target="_blank" rel="noopener"
            aria-label="Tanya admin lewat WhatsApp">
            <i class="bi bi-whatsapp"></i>
        </a>
    </div>

</div>

<script>
    /*
     * Klik kanan di atas rekaman testimoni tidak memunculkan menu peramban.
     *
     * Pasangan controlsList="nodownload": yang itu membuang butir Download
     * dari menu tiga titik, yang ini membuang "Simpan video sebagai…" dari
     * menu klik kanan. Keduanya menutup jalan yang paling mudah SAJA —
     * alamat berkasnya tetap ada di sumber halaman, dan ini bukan
     * pengamanan. Lihat keterangan di markahnya.
     *
     * Hanya pada rekaman itu, bukan seluruh halaman: mematikan klik kanan
     * di mana-mana merampas salin-tempel, buka-di-tab-baru, dan periksa
     * ejaan dari orang yang tidak sedang mengunduh apa pun.
     */
    (function () {
        document.querySelectorAll('.bnd-polaroid video').forEach(function (v) {
            v.addEventListener('contextmenu', function (e) { e.preventDefault(); });
        });
    })();
</script>

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
