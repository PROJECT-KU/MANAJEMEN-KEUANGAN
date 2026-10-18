@extends('public.layout.header')

@section('title')
Beranda | Rumah Scopus
@stop

@section('konten')
{{--
    Halaman depan Rumah Scopus.

    Ditulis ulang 18 Okt 2026. Yang sebelumnya ada di sini bukan desain yang
    buruk, melainkan desain yang TIDAK PERNAH DIPUTUSKAN: tiap bagian dirakit
    sendiri-sendiri dengan gaya ditulis langsung di atribut style=, sehingga
    satu halaman memuat tiga ukuran judul, dua perlakuan garis bawah, dan dua
    pita jingga penuh yang berulang. Mata membacanya sebagai "halaman ini
    disusun beberapa orang pada waktu yang berbeda".

    Tiga hal yang diperbaiki, dan ketiganya bisa diperiksa:

      1. SEMUA paragraf dulu rata kiri-kanan (text-align: justify). Pada kolom
         kartu selebar 260px, itu membuat sungai putih melebar di tengah
         kalimat — terukur pada kartu layanan, satu baris berisi tiga kata
         dengan jarak antar kata hampir dua kali normal. Sekarang rata kiri
         dengan text-wrap: pretty.

      2. Perataan judulnya BERGANTIAN, tidak lagi semuanya rata tengah.
         Bagian yang punya tautan lanjutan rata kiri dengan tautannya di
         kanan; bagian yang berdiri sendiri rata tengah. Yang berubah bukan
         selera melainkan kegunaannya: judul rata tengah dengan tautan di
         kanan memaksa mata melompat dua kali untuk satu baris.

      3. Warnanya tidak lagi satu jingga untuk segalanya. Tiap kartu membawa
         warnanya sendiri lewat --c, dan tintanya dirakit dari warna itu
         dengan color-mix — jadi menambah kartu tidak perlu menambah satu set
         aturan CSS baru.

    Gayanya ditulis dalam satu blok <style> di berkas ini, bukan di
    assets/artikel/css/style.css: berkas itu milik templat artikel dan dipakai
    SELURUH halaman publik, jadi menambahkan aturan beranda di sana akan
    menyentuh halaman yang tidak sedang dikerjakan.
--}}

<style>
    /* ------------------------------------------------------------ dasar */
    .bnd {
        /* Warna merek Rumah Scopus. Dibiarkan sebagai dua titik gradien,
           bukan satu warna rata: seluruh identitas yang sudah ada —
           tombol, pita, logo — memakai peralihan merah ke jingga ini. */
        --api-1: #ff3131;
        --api-2: #ff914d;

        /* Biru tua. Dipakai untuk pita angka dan tulisan judul. Tanpa warna
           kedua yang pekat, halaman ini cuma jingga di atas putih dari atas
           sampai bawah, dan bagian yang penting kehilangan cara untuk
           menonjol. */
        --navy: #0f2b5b;

        --tinta: #1c1f26;
        --tinta-2: #6b7280;
        --garis: #e9edf2;
        --latar: #f8fafc;

        font-family: 'Poppins', 'Open Sans', sans-serif;
        color: var(--tinta);
    }

    /* Judul dan paragraf halaman ini TIDAK memakai aturan bawaan templat
       artikel: di sana h1 dan h2 punya ukuran dan margin sendiri yang
       dirancang untuk halaman tulisan, bukan untuk halaman depan. */
    .bnd h1, .bnd h2, .bnd h3 {
        font-family: 'Poppins', sans-serif;
        color: var(--tinta);
        margin: 0;
        /* Judul dua baris dibagi rata, bukan satu baris penuh dengan satu
           kata menggantung sendirian di bawahnya. */
        text-wrap: balance;
    }

    .bnd p {
        margin: 0;
        color: var(--tinta-2);
        line-height: 1.65;
        /* pretty, BUKAN justify. Lihat alasannya di kepala berkas. */
        text-wrap: pretty;
    }

    .bnd-bagian { padding: 76px 0; }
    .bnd-bagian-rapat { padding: 56px 0; }

    /* ------------------------------------------------- kepala tiap bagian */
    /* Satu bentuk untuk semua bagian: label kecil, judul besar, satu kalimat
       penjelas. Sebelumnya tiap bagian punya polanya sendiri — ada yang
       berjudul h1 dengan garis bawah gradien, ada yang h1 polos. */
    .bnd-kepala { margin-bottom: 38px; }

    .bnd-kepala-tengah { text-align: center; }
    .bnd-kepala-tengah .bnd-sub { margin-left: auto; margin-right: auto; }

    /* Rata kiri DENGAN tautan di kanan, sebaris. Dipakai bagian yang punya
       kelanjutan; tautannya harus duduk sejajar judulnya, bukan melayang di
       bawah tengah. */
    .bnd-kepala-sisi {
        display: flex; align-items: flex-end; justify-content: space-between;
        gap: 24px; flex-wrap: wrap;
    }

    .bnd-label {
        display: inline-flex; align-items: center; gap: 8px;
        margin-bottom: 8px;
        font-size: .74rem; font-weight: 700; letter-spacing: .16em;
        text-transform: uppercase; color: var(--api-1);
    }
    .bnd-label i { font-size: .8rem; line-height: 1; }

    .bnd-judul {
        font-size: 2.45rem; font-weight: 800; line-height: 1.08;
        letter-spacing: -.03em;
    }

    /* `.bnd p.bnd-sub`, bukan `.bnd-sub`.

       Aturan `.bnd p { margin: 0 }` di atas berbobot (0,1,1) — satu kelas
       satu elemen — sedangkan `.bnd-sub` hanya (0,1,0). Selektor kelas
       tunggal KALAH, dan margin-top-nya tidak pernah berlaku: terukur
       jaraknya 0px di keempat kepala bagian, pada 390px maupun 1440px.
       Gejalanya cuma terlihat di ponsel, tempat judul dua baris dan
       kalimat penjelasnya saling menempel sampai terbaca sebagai satu blok.

       Yang ditambah elemennya, bukan !important: menang lewat !important
       berarti aturan berikutnya harus ikut memakainya juga. */
    .bnd p.bnd-sub {
        margin-top: 14px; max-width: 58ch;
        font-size: 1.02rem;
    }

    .bnd-tautan {
        display: inline-flex; align-items: center; gap: 8px;
        color: var(--api-1); font-weight: 700; font-size: .95rem;
        text-decoration: none; white-space: nowrap;
        transition: gap .18s ease, color .18s ease;
    }
    .bnd-tautan:hover { color: #d9531a; gap: 13px; }

    /* ------------------------------------------------------------- hero */
    .bnd-hero {
        position: relative; overflow: hidden;
        /* 118px, bukan 76: kepala situsnya fixed-top setinggi 80px, dan
           angka ini TIDAK boleh turun di bawah itu — judulnya akan
           tertutup. */
        padding: 118px 0 84px;
        background:
            radial-gradient(circle at 88% 8%, rgba(255, 145, 77, .20), transparent 46%),
            radial-gradient(circle at 4% 72%, rgba(15, 43, 91, .10), transparent 44%),
            linear-gradient(180deg, #fff 0%, var(--latar) 100%);
    }

    .bnd-hero-kisi {
        display: grid; grid-template-columns: 1.05fr .95fr;
        gap: 54px; align-items: center;
    }

    .bnd-hero h1 {
        font-size: 3.1rem; font-weight: 800; line-height: 1.07;
        letter-spacing: -.035em;
    }

    /* Satu kata disorot, bukan seluruh judul diwarnai. Judul berwarna penuh
       di atas latar terang kehilangan kepekatannya; satu kata berwarna
       justru menarik mata ke kata yang memang menjual. */
    .bnd-sorot {
        background: linear-gradient(90deg, var(--api-1), var(--api-2));
        -webkit-background-clip: text; background-clip: text;
        -webkit-text-fill-color: transparent; color: transparent;
    }

    .bnd-hero p { margin-top: 18px; font-size: 1.06rem; max-width: 52ch; }

    .bnd-aksi { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 30px; }

    .bnd-tombol {
        display: inline-flex; align-items: center; gap: 10px;
        padding: 14px 26px; border-radius: 999px;
        font-weight: 700; font-size: .98rem; text-decoration: none;
        border: 1px solid transparent;
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    }
    .bnd-tombol-utama {
        background: linear-gradient(90deg, var(--api-1), var(--api-2));
        color: #fff;
        box-shadow: 0 12px 26px rgba(255, 73, 49, .32);
    }
    .bnd-tombol-utama:hover {
        color: #fff; transform: translateY(-2px);
        box-shadow: 0 16px 32px rgba(255, 73, 49, .42);
    }
    .bnd-tombol-kedua {
        background: #fff; color: var(--navy); border-color: var(--garis);
        box-shadow: 0 6px 16px rgba(15, 43, 91, .08);
    }
    .bnd-tombol-kedua:hover {
        color: var(--navy); transform: translateY(-2px); border-color: #cfd8e3;
    }

    /* Tiga janji singkat di bawah tombol. Menjawab keraguan yang muncul
       justru SESUDAH orang tertarik — "berapa lama", "siapa yang mengajar" —
       tanpa menunggu ia menggulung ke bagian berikutnya. */
    .bnd-janji {
        display: flex; flex-wrap: wrap; gap: 10px 22px; margin-top: 28px;
        padding-top: 22px; border-top: 1px dashed #e3e8ef;
    }
    .bnd-janji span {
        display: inline-flex; align-items: center; gap: 8px;
        font-size: .88rem; font-weight: 600; color: var(--tinta-2);
    }
    .bnd-janji i { color: #16a34a; font-size: .95rem; }

    .bnd-hero-gambar { position: relative; }
    .bnd-hero-gambar img {
        position: relative; z-index: 1;
        width: 100%; height: auto; display: block;
        border-radius: 22px;
    }

    /* Bulatan jingga lembut di belakang fotonya. Tanpa ini fotonya
       melayang sendirian di ruang putih sisi kanan. */
    .bnd-hero-gambar::before {
        content: ""; position: absolute; z-index: 0;
        inset: -8% -6% -10% -4%;
        border-radius: 42% 58% 54% 46% / 52% 44% 56% 48%;
        background: linear-gradient(135deg, rgba(255, 145, 77, .28), rgba(255, 49, 49, .14));
        filter: blur(2px);
        animation: bnd-ombak 14s ease-in-out infinite;
    }

    @keyframes bnd-ombak {
        0%, 100% { border-radius: 42% 58% 54% 46% / 52% 44% 56% 48%; transform: translateY(0); }
        50%      { border-radius: 56% 44% 46% 54% / 44% 56% 44% 56%; transform: translateY(-10px); }
    }

    /* ---------------------------------------------------- kartu alasan */
    .bnd-kisi-4 {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;
    }

    /* Satu bentuk kartu dipakai bagian "kenapa memilih" dan "yang dipelajari".
       Warnanya datang dari --c yang dipasang per kartu, jadi menambah kartu
       tidak menambah satu set aturan baru. */
    .bnd-kartu {
        position: relative; overflow: hidden;
        background: #fff; border: 1px solid var(--garis); border-radius: 18px;
        padding: 26px 22px;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }
    .bnd-kartu:hover {
        transform: translateY(-4px);
        border-color: color-mix(in srgb, var(--c) 34%, #fff);
        box-shadow: 0 16px 32px color-mix(in srgb, var(--c) 20%, transparent);
    }

    /* Sapuan warna samar di pojok kanan atas, membesar saat disentuh.
       Perlakuan yang sama di semua kartu halaman ini supaya terbaca sebagai
       satu bahasa, bukan beberapa percobaan. */
    .bnd-kartu::before {
        content: ""; position: absolute; top: -40px; right: -40px;
        width: 108px; height: 108px; border-radius: 50%;
        background: color-mix(in srgb, var(--c) 13%, transparent);
        transition: transform .3s ease;
    }
    .bnd-kartu:hover::before { transform: scale(1.4); }

    .bnd-ikon {
        position: relative; z-index: 1;
        display: inline-flex; align-items: center; justify-content: center;
        width: 52px; height: 52px; border-radius: 15px; margin-bottom: 16px;
        background: color-mix(in srgb, var(--c) 12%, #fff);
        color: var(--c); font-size: 1.35rem;
        transition: background .22s ease, color .22s ease, transform .22s ease;
    }
    .bnd-kartu:hover .bnd-ikon {
        background: var(--c); color: #fff; transform: rotate(-6deg) scale(1.06);
    }

    .bnd-kartu h3 {
        position: relative; z-index: 1;
        font-size: 1.08rem; font-weight: 700; margin-bottom: 7px;
    }
    .bnd-kartu p { position: relative; z-index: 1; font-size: .92rem; }

    /* --------------------------------------------------- kartu layanan */
    .bnd-kisi-3 {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;
    }

    .bnd-layanan {
        display: flex; flex-direction: column; overflow: hidden;
        background: #fff; border: 1px solid var(--garis); border-radius: 20px;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }
    .bnd-layanan:hover {
        transform: translateY(-5px);
        border-color: color-mix(in srgb, var(--c) 32%, #fff);
        box-shadow: 0 20px 40px color-mix(in srgb, var(--c) 22%, transparent);
    }

    .bnd-layanan-atas { position: relative; overflow: hidden; }
    .bnd-layanan-atas img {
        width: 100%; height: 188px; object-fit: cover; display: block;
        transition: transform .4s ease;
    }
    .bnd-layanan:hover .bnd-layanan-atas img { transform: scale(1.06); }

    /* Lencana cara belajarnya duduk DI ATAS gambar, bukan di bawah judul:
       di situ ia terbaca bersamaan dengan gambarnya, dan tidak menambah satu
       baris lagi pada tumpukan teks yang sudah padat. */
    .bnd-lencana {
        position: absolute; left: 14px; top: 14px; z-index: 1;
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 12px; border-radius: 999px;
        background: rgba(255, 255, 255, .94);
        color: var(--c); font-size: .74rem; font-weight: 700;
        letter-spacing: .02em;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .14);
    }

    /* Ubin bergradien untuk layanan yang belum punya foto. Lebih jujur
       daripada memakai foto layanan lain, dan tetap seukuran gambar yang
       lain supaya barisannya tidak pincang. */
    .bnd-layanan-ubin {
        display: flex; align-items: center; justify-content: center;
        height: 188px;
        background: linear-gradient(135deg, color-mix(in srgb, var(--c) 92%, #000 0%), color-mix(in srgb, var(--c) 55%, #fff));
        color: #fff; font-size: 3.4rem;
    }

    .bnd-layanan-isi {
        display: flex; flex-direction: column; flex: 1 1 auto;
        padding: 20px 22px 22px;
    }
    .bnd-layanan-isi h3 { font-size: 1.14rem; font-weight: 700; margin-bottom: 9px; }
    .bnd-layanan-isi p { font-size: .92rem; }

    /* Tombolnya didorong ke dasar kartu supaya SEMUA tombol sebaris,
       berapa pun panjang keterangannya. Sebelumnya keterangan yang panjang
       menurunkan tombolnya sendiri dan barisan tombol jadi bergerigi. */
    .bnd-layanan-tombol {
        display: inline-flex; align-items: center; justify-content: center; gap: 9px;
        margin-top: auto; padding-top: 18px;
    }
    .bnd-layanan-tombol > a {
        display: inline-flex; align-items: center; justify-content: center; gap: 9px;
        width: 100%; padding: 12px 18px; border-radius: 12px;
        background: color-mix(in srgb, var(--c) 10%, #fff);
        color: var(--c); font-weight: 700; font-size: .93rem; text-decoration: none;
        transition: background .2s ease, color .2s ease, gap .2s ease;
    }
    .bnd-layanan:hover .bnd-layanan-tombol > a {
        background: var(--c); color: #fff; gap: 13px;
    }

    /* ------------------------------------------------------ pita angka */
    /* Biru tua, BUKAN jingga. Halaman ini sudah punya dua tempat jingga
       pekat (tombol hero dan kepala tiap bagian); pita ketiga berwarna sama
       membuat ketiganya saling meredam. Biru tua juga memberi jeda gelap di
       tengah halaman yang seluruhnya terang. */
    .bnd-angka {
        position: relative; overflow: hidden;
        padding: 64px 0;
        background:
            radial-gradient(circle at 12% 18%, rgba(255, 145, 77, .22), transparent 42%),
            radial-gradient(circle at 88% 82%, rgba(255, 49, 49, .18), transparent 46%),
            linear-gradient(135deg, #0f2b5b 0%, #16386f 55%, #0b2149 100%);
    }
    .bnd-angka .bnd-judul, .bnd-angka .bnd-sub { color: #fff; }
    .bnd-angka .bnd-sub { color: rgba(255, 255, 255, .76); }
    .bnd-angka .bnd-label { color: var(--api-2); }

    .bnd-kisi-angka {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px;
    }

    .bnd-nilai {
        position: relative; overflow: hidden;
        padding: 28px 24px; border-radius: 18px;
        background: rgba(255, 255, 255, .07);
        border: 1px solid rgba(255, 255, 255, .16);
        transition: background .22s ease, transform .22s ease;
    }
    .bnd-nilai:hover { background: rgba(255, 255, 255, .12); transform: translateY(-4px); }

    .bnd-nilai-kepala { display: flex; align-items: center; gap: 14px; }
    .bnd-nilai-ikon {
        display: inline-flex; align-items: center; justify-content: center;
        width: 48px; height: 48px; border-radius: 14px; flex: 0 0 auto;
        background: linear-gradient(135deg, var(--api-1), var(--api-2));
        color: #fff; font-size: 1.25rem;
    }
    .bnd-nilai-angka {
        font-size: 2.7rem; font-weight: 800; line-height: 1; color: #fff;
        letter-spacing: -.03em;
        /* Angka berjalan tidak boleh menggeser tata letaknya tiap kali
           lebar digitnya berubah. */
        font-variant-numeric: tabular-nums;
    }
    .bnd-nilai p { margin-top: 14px; color: rgba(255, 255, 255, .78); font-size: .9rem; }

    /* ------------------------------------------------- yang akan dipelajari */
    .bnd-pelajari-kisi {
        display: grid; grid-template-columns: 1.1fr .9fr; gap: 46px; align-items: center;
    }
    .bnd-kisi-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }

    .bnd-pelajari-gambar img {
        width: 100%; height: auto; display: block; border-radius: 20px;
    }

    /* Keenamnya hanya berisi ikon dan satu baris nama — tidak ada keterangan
       yang menyusul. Dengan bentuk kartu penuh, terukur 150px tingginya
       untuk satu baris teks: dua pertiga kartunya ruang kosong, dan enam
       kartu seperti itu memanjangkan kolomnya jauh melewati gambar di
       sebelahnya.

       Dibuat mendatar: ikon di kiri, nama di kanan, sebaris. Yang berubah
       cuma susunannya — warna, sapuan pojok, dan angkatannya saat disentuh
       tetap sama persis dengan kartu di bagian lain. */
    .bnd-kartu-ringkas {
        display: flex; align-items: center; gap: 14px;
        padding: 16px 18px;
    }
    .bnd-kartu-ringkas .bnd-ikon {
        width: 44px; height: 44px; margin-bottom: 0; flex: 0 0 auto;
        border-radius: 13px; font-size: 1.15rem;
    }
    .bnd-kartu-ringkas h3 { margin-bottom: 0; font-size: 1rem; }

    /* ---------------------------------------------------------- testimoni */
    .bnd-testi-kisi {
        display: grid; grid-template-columns: .95fr 1.05fr; gap: 42px; align-items: center;
    }

    .bnd-testi-video {
        position: relative; border-radius: 20px; overflow: hidden;
        background: linear-gradient(135deg, var(--api-1), var(--api-2));
        box-shadow: 0 22px 44px rgba(255, 73, 49, .26);
    }
    .bnd-testi-video video {
        width: 100%; height: 520px; object-fit: cover; display: block;
    }

    /* Kutipan, bukan paragraf biasa: isinya memang perkataan tentang
       peserta, dan tanda kutip raksasa memberi tahu itu sebelum satu kata
       pun dibaca. Sebelumnya teks ini dibungkus <h3> rata kiri-kanan —
       judul yang bukan judul, dengan sungai putih di tengahnya. */
    .bnd-kutip { position: relative; padding-left: 4px; }
    .bnd-kutip::before {
        content: "\201C";
        position: absolute; left: -10px; top: -46px;
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 7rem; line-height: 1; color: color-mix(in srgb, var(--api-2) 32%, #fff);
        pointer-events: none;
    }
    .bnd-kutip p {
        position: relative; z-index: 1;
        font-size: 1.08rem; line-height: 1.75; color: #374151;
    }
    .bnd-kutip p + p { margin-top: 14px; }

    .bnd-kutip-kaki {
        display: flex; align-items: center; gap: 13px; margin-top: 24px;
        padding-top: 20px; border-top: 1px solid var(--garis);
    }
    .bnd-kutip-kaki i {
        display: inline-flex; align-items: center; justify-content: center;
        width: 44px; height: 44px; border-radius: 50%; flex: 0 0 auto;
        background: linear-gradient(135deg, var(--api-1), var(--api-2));
        color: #fff; font-size: 1.05rem;
    }
    .bnd-kutip-kaki strong { display: block; font-size: .98rem; color: var(--tinta); }
    .bnd-kutip-kaki small { color: var(--tinta-2); font-size: .86rem; }

    /* ------------------------------------------------------- ajakan akhir */
    .bnd-ajakan {
        position: relative; overflow: hidden;
        padding: 52px 46px; border-radius: 26px;
        background:
            radial-gradient(circle at 92% 12%, rgba(255, 255, 255, .22), transparent 42%),
            linear-gradient(120deg, var(--api-1), var(--api-2));
        box-shadow: 0 24px 48px rgba(255, 73, 49, .3);
        text-align: center;
    }
    .bnd-ajakan h2 {
        color: #fff; font-size: 2.1rem; font-weight: 800; letter-spacing: -.03em;
        line-height: 1.12;
    }
    .bnd-ajakan p {
        color: rgba(255, 255, 255, .92); margin: 14px auto 0; max-width: 56ch;
    }
    .bnd-ajakan .bnd-aksi { justify-content: center; }
    .bnd-ajakan .bnd-tombol-utama {
        background: #fff; color: var(--api-1); box-shadow: 0 12px 26px rgba(15, 23, 42, .2);
    }
    .bnd-ajakan .bnd-tombol-utama:hover { color: var(--api-1); }
    .bnd-ajakan .bnd-tombol-kedua {
        background: rgba(255, 255, 255, .14); color: #fff;
        border-color: rgba(255, 255, 255, .5);
    }
    .bnd-ajakan .bnd-tombol-kedua:hover { color: #fff; background: rgba(255, 255, 255, .24); }

    /* ---------------------------------------------------------- layar kecil */
    @media (max-width: 991.98px) {
        .bnd-hero-kisi,
        .bnd-pelajari-kisi,
        .bnd-testi-kisi { grid-template-columns: 1fr; gap: 36px; }

        /* Gambar hero turun KE BAWAH teksnya di layar sempit. Di atas, ia
           mendorong judul dan tombolnya keluar dari layar pertama — dan
           yang harus terbaca lebih dulu kalimatnya, bukan fotonya. */
        .bnd-hero-gambar { order: 2; }

        .bnd-kisi-4 { grid-template-columns: repeat(2, 1fr); }
        .bnd-kisi-3 { grid-template-columns: repeat(2, 1fr); }
        .bnd-hero h1 { font-size: 2.5rem; }
        .bnd-judul { font-size: 2.05rem; }
        .bnd-testi-video video { height: 420px; }
    }

    @media (max-width: 767.98px) {
        .bnd-bagian { padding: 54px 0; }
        .bnd-hero { padding: 104px 0 62px; }
        .bnd-hero h1 { font-size: 2.05rem; }
        .bnd-hero p { font-size: 1rem; }
        .bnd-judul { font-size: 1.72rem; }
        .bnd-kepala { margin-bottom: 28px; }

        .bnd-kisi-4,
        .bnd-kisi-3,
        .bnd-kisi-2,
        .bnd-kisi-angka { grid-template-columns: 1fr; }

        .bnd-aksi > .bnd-tombol { flex: 1 1 100%; justify-content: center; }
        .bnd-ajakan { padding: 38px 22px; border-radius: 20px; }
        .bnd-ajakan h2 { font-size: 1.58rem; }
        .bnd-testi-video video { height: 340px; }
        .bnd-nilai-angka { font-size: 2.3rem; }
        .bnd-kutip::before { font-size: 5rem; top: -32px; }
    }

    /* Jalan keluar untuk SEMUA gerak di halaman ini. Ditulis paling bawah
       supaya tidak ada aturan sesudahnya yang menghidupkannya lagi.

       Orang yang menyalakan "kurangi gerak" di sistemnya sebagian
       menyalakannya karena gerak di layar membuatnya pusing — dan beranda
       adalah halaman pertama yang ia buka, sebelum ia tahu situs ini apa.

       Selektor beranimasinya DISEBUT TERANG di baris pertama, bukan hanya
       tersapu oleh `.bnd *`. Penjaga GerakMinimalPublikTest mencocokkan
       teks selektornya apa adanya; sapuan bintang memang mematikannya di
       peramban, tetapi membuat penjaga itu buta — dan animasi berikutnya
       yang ditulis orang lain akan lolos tanpa ada yang memberi tahu. */
    @media (prefers-reduced-motion: reduce) {
        .bnd-hero-gambar::before {
            animation: none !important;
        }

        .bnd *,
        .bnd *::before,
        .bnd *::after {
            animation: none !important;
            transition: none !important;
        }
    }
</style>

<div class="bnd">

    {{-- ================================================== hero ========= --}}
    <section class="bnd-hero">
        <div class="container">
            <div class="bnd-hero-kisi">
                <div data-aos="fade-up">
                    <span class="bnd-label">
                        <i class="bi bi-mortarboard-fill"></i> Pelatihan jurnal bereputasi
                    </span>

                    {{-- "Terindeks Scopus" disorot, bukan seluruh judulnya:
                         itu kata yang dicari orang saat mendarat di sini. --}}
                    <h1>
                        Dari naskah pertama sampai
                        <span class="bnd-sorot">terindeks Scopus</span>
                    </h1>

                    <p>
                        Rumah Scopus mendampingi penulis Indonesia menembus jurnal
                        internasional bereputasi — dari menyusun naskah, memilih jurnal,
                        sampai artikelnya benar-benar terbit.
                    </p>

                    <div class="bnd-aksi">
                        <a href="#layanan" class="bnd-tombol bnd-tombol-utama">
                            Lihat layanan kami <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="https://www.youtube.com/@rumahscopus" target="_blank" rel="noopener"
                            class="bnd-tombol bnd-tombol-kedua">
                            <i class="bi bi-play-circle-fill"></i> Tonton cerita peserta
                        </a>
                    </div>

                    <div class="bnd-janji">
                        <span><i class="bi bi-check-circle-fill"></i> Trainer berpengalaman</span>
                        <span><i class="bi bi-check-circle-fill"></i> Praktik langsung</span>
                        <span><i class="bi bi-check-circle-fill"></i> Didampingi sampai terbit</span>
                    </div>
                </div>

                <div class="bnd-hero-gambar" data-aos="zoom-out" data-aos-delay="150">
                    <img src="{{ asset('assets/img/header1.png') }}"
                        alt="Peserta pelatihan Rumah Scopus">
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================ kenapa memilih ===== --}}
    <section class="bnd-bagian">
        <div class="container">
            {{-- Rata tengah: bagian ini tidak punya kelanjutan ke mana pun,
                 jadi tidak ada yang perlu duduk di sisi kanannya. --}}
            <div class="bnd-kepala bnd-kepala-tengah" data-aos="fade-up">
                <span class="bnd-label"><i class="bi bi-patch-check-fill"></i> Kenapa Rumah Scopus</span>
                <h2 class="bnd-judul">Bukan sekadar kelas menulis</h2>
                <p class="bnd-sub">
                    Empat hal yang membuat peserta bertahan sampai artikelnya terbit,
                    bukan berhenti di tengah jalan.
                </p>
            </div>

            {{-- Warnanya dipasang per kartu lewat --c. Keempatnya sengaja
                 berbeda supaya barisan ini tidak terbaca sebagai satu blok
                 jingga yang sama dari ujung ke ujung. --}}
            <div class="bnd-kisi-4">
                @foreach ([
                    ['#f26522', 'bi-person-badge-fill', 'Trainer profesional', 'Didampingi trainer yang sudah menerbitkan artikelnya sendiri di jurnal bereputasi.'],
                    ['#2563eb', 'bi-people-fill', 'Pendampingan penuh', 'Tidak berhenti di kelas — pendampingan berlanjut sampai artikel Anda terindeks.'],
                    ['#7c3aed', 'bi-pencil-square', 'Learning by doing', 'Naskah Anda sendiri yang dikerjakan di kelas, bukan contoh kasus milik orang lain.'],
                    ['#16a34a', 'bi-award-fill', 'Terbukti berulang', 'Ratusan artikel peserta sudah terindeks Scopus, bukan janji di atas kertas.'],
                ] as $i => [$warna, $ikon, $judul, $ket])
                    <div class="bnd-kartu" style="--c: {{ $warna }}"
                        data-aos="fade-up" data-aos-delay="{{ $i * 90 }}">
                        <span class="bnd-ikon"><i class="bi {{ $ikon }}"></i></span>
                        <h3>{{ $judul }}</h3>
                        <p>{{ $ket }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================================================ layanan ======== --}}
    <section class="bnd-bagian" id="layanan" style="background: var(--latar);">
        <div class="container">
            {{-- Rata kiri dengan tautan di kanan: bagian ini PUNYA
                 kelanjutan, dan tautannya harus sejajar judulnya. --}}
            <div class="bnd-kepala bnd-kepala-sisi" data-aos="fade-up">
                <div>
                    <span class="bnd-label"><i class="bi bi-grid-fill"></i> Layanan kami</span>
                    <h2 class="bnd-judul">Pilih jalur yang paling cocok</h2>
                    <p class="bnd-sub">
                        Enam cara belajar dengan tempo dan cara pendampingan yang berbeda —
                        dari kelas tiga hari penuh sampai konsultasi lima puluh menit.
                    </p>
                </div>
                <a href="{{ url('/contact') }}" class="bnd-tautan">
                    Bingung memilih? Tanya kami <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            {{-- CLINIK SCOPUS DULU MENUNJUK KE /Analisis-Bibliometrik.

                 Bukan salah ketik yang kelihatan: tombolnya tetap berbunyi
                 "Pesan Sekarang" dan tetap membuka halaman yang sah, jadi
                 satu-satunya yang tahu ia salah adalah orang yang menekannya
                 lalu mendapati dirinya memesan layanan lain. Dibetulkan ke
                 /Clinik-Scopus. --}}
            <div class="bnd-kisi-3">
                @foreach ([
                    ['#f26522', 'HomeScopusCamp.png', 'Offline · 3 hari', 'Scopus Camp', 'Pendampingan tatap muka selama tiga hari penuh, lalu pendampingan lanjutan selama satu tahun.', url('/Scopus-Camp')],
                    ['#2563eb', 'HomeOnlineTraining.png', 'Online · 8 pertemuan', 'Online Training', 'Delapan kali pertemuan daring bersama trainer, dengan pendampingan lanjutan selama tiga bulan.', 'https://rumahscopus.com/courses/online-class/'],
                    ['#7c3aed', 'HomeScopusKafe.png', 'Privat · 5 jam', 'Scopus Kafe', 'Sesi privat lima jam, satu lawan satu, untuk naskah yang sudah berjalan dan butuh dibereskan.', url('/Scopus-Kafe')],
                    ['#0891b2', 'HomeBibliometrik.png', 'Online · 8 pertemuan', 'Analisis Bibliometrik', 'Belajar memetakan arah riset dan menemukan celah yang belum digarap orang lain.', url('/Analisis-Bibliometrik')],
                    ['#16a34a', 'HomeScopusClinic.png', 'Chat · 50 menit', 'Clinik Scopus', 'Konsultasi singkat lewat chat untuk satu pertanyaan yang sedang menghambat naskah Anda.', url('/Clinik-Scopus')],
                    ['#db2777', null, 'Daring · sesi terbatas', 'Webinar Eksklusif', 'Sesi daring bertema khusus dengan kuota terbatas, dibuka berkala sepanjang tahun.', url('/Webinar-Eksklusif')],
                ] as $i => [$warna, $gambar, $lencana, $judul, $ket, $tautan])
                    <div class="bnd-layanan" style="--c: {{ $warna }}"
                        data-aos="fade-up" data-aos-delay="{{ ($i % 3) * 110 }}">
                        <div class="bnd-layanan-atas">
                            @if ($gambar)
                                <img src="{{ asset('assets/img/public/' . $gambar) }}" alt="{{ $judul }}">
                            @else
                                {{-- Belum punya fotonya. Ubin bergradien lebih
                                     jujur daripada meminjam foto layanan lain. --}}
                                <div class="bnd-layanan-ubin"><i class="bi bi-broadcast"></i></div>
                            @endif
                            <span class="bnd-lencana">{{ $lencana }}</span>
                        </div>

                        <div class="bnd-layanan-isi">
                            <h3>{{ $judul }}</h3>
                            <p>{{ $ket }}</p>
                            <span class="bnd-layanan-tombol">
                                <a href="{{ $tautan }}">
                                    Lihat detailnya <i class="bi bi-arrow-right"></i>
                                </a>
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================================================== angka ======== --}}
    <section class="bnd-angka">
        <div class="container">
            <div class="bnd-kepala bnd-kepala-tengah" data-aos="fade-up">
                <span class="bnd-label"><i class="bi bi-graph-up-arrow"></i> Sudah dipercaya</span>
                <h2 class="bnd-judul">Ribuan penulis sudah lebih dulu</h2>
                <p class="bnd-sub">
                    Angka ini dikumpulkan dari peserta yang benar-benar menyelesaikan
                    pendampingannya bersama kami.
                </p>
            </div>

            {{-- data-purecounter: angkanya baru berjalan saat bagian ini
                 TERLIHAT, bukan saat halamannya dimuat.

                 Versi sebelumnya menjalankan ketiganya lewat
                 requestAnimationFrame pada DOMContentLoaded — yaitu ribuan
                 langkah untuk angka yang berada 3.000px di bawah layar
                 pertama, dan sudah selesai berhitung jauh sebelum ada yang
                 menggulung ke sana. Yang tersisa untuk dilihat orangnya cuma
                 angka diam. --}}
            <div class="bnd-kisi-angka">
                @foreach ([
                    ['bi-mortarboard-fill', 5000, 'alumni sudah mengikuti pelatihan di Rumah Scopus'],
                    ['bi-send-check-fill', 2315, 'naskah sudah disubmit ke jurnal internasional'],
                    ['bi-journal-check', 1851, 'artikel sudah terbit dan terindeks Scopus'],
                ] as $i => [$ikon, $nilai, $ket])
                    <div class="bnd-nilai" data-aos="fade-up" data-aos-delay="{{ $i * 110 }}">
                        <div class="bnd-nilai-kepala">
                            <span class="bnd-nilai-ikon"><i class="bi {{ $ikon }}"></i></span>
                            <span class="bnd-nilai-angka purecounter"
                                data-purecounter-start="0"
                                data-purecounter-end="{{ $nilai }}"
                                data-purecounter-duration="2">0</span>
                        </div>
                        <p>{{ $ket }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================== yang dipelajari === --}}
    <section class="bnd-bagian">
        <div class="container">
            <div class="bnd-pelajari-kisi">
                <div>
                    {{-- Rata kiri, menempel pada daftarnya sendiri. Rata
                         tengah di kolom yang bersebelahan dengan gambar
                         membuat judulnya tidak jelas milik sisi yang mana. --}}
                    <div class="bnd-kepala" data-aos="fade-up">
                        <span class="bnd-label"><i class="bi bi-lightbulb-fill"></i> Isi pelatihan</span>
                        <h2 class="bnd-judul">Apa yang akan Anda pelajari</h2>
                        <p class="bnd-sub">
                            Enam keterampilan yang dipakai berulang kali sepanjang
                            perjalanan satu naskah.
                        </p>
                    </div>

                    <div class="bnd-kisi-2">
                        @foreach ([
                            ['#2563eb', 'bi-file-earmark-richtext-fill', 'Penulisan digital'],
                            ['#7c3aed', 'bi-cpu-fill', 'Memanfaatkan AI'],
                            ['#0891b2', 'bi-diagram-3-fill', 'Anatomi paper'],
                            ['#16a34a', 'bi-link-45deg', 'Referensi terbaik'],
                            ['#d97706', 'bi-chat-square-text-fill', 'Pendampingan'],
                            ['#db2777', 'bi-patch-check-fill', 'Terindeks Scopus'],
                        ] as $i => [$warna, $ikon, $judul])
                            <div class="bnd-kartu bnd-kartu-ringkas" style="--c: {{ $warna }}"
                                data-aos="fade-up" data-aos-delay="{{ $i * 70 }}">
                                <span class="bnd-ikon"><i class="bi {{ $ikon }}"></i></span>
                                <h3>{{ $judul }}</h3>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bnd-pelajari-gambar" data-aos="fade-left" data-aos-delay="150">
                    <img src="{{ asset('assets/artikel/img/Tiny.jpg') }}"
                        alt="Ilustrasi proses menulis dan menelaah artikel ilmiah">
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================== testimoni ======== --}}
    <section class="bnd-bagian" style="background: var(--latar);">
        <div class="container">
            <div class="bnd-kepala bnd-kepala-tengah" data-aos="fade-up">
                <span class="bnd-label"><i class="bi bi-chat-quote-fill"></i> Testimoni</span>
                <h2 class="bnd-judul">Kata mereka yang sudah tembus</h2>
            </div>

            <div class="bnd-testi-kisi">
                <div class="bnd-testi-video" data-aos="fade-right">
                    {{-- preload="metadata": tanpa ini peramban mengunduh
                         seluruh berkas videonya sebelum ada yang menekan
                         putar — beban yang ditanggung setiap pengunjung
                         beranda, termasuk yang tidak pernah menggulung
                         sampai ke sini. Dengan metadata saja, peramban tetap
                         menggambar bingkai pertamanya sebagai sampul.

                         TANPA poster=. Sempat diisi cover.png, dan gambar itu
                         ternyata iklan layanan lain ("Manuskrip Berbasis
                         Analisis Bibliometrik, 500k") — sampul yang
                         menjanjikan isi yang berbeda dari videonya. --}}
                    <video controls preload="metadata">
                        <source src="{{ asset('assets/img/public/testimoni.MOV') }}" type="video/mp4">
                        Peramban Anda tidak bisa memutar video ini.
                    </video>
                </div>

                <div class="bnd-kutip" data-aos="fade-left" data-aos-delay="120">
                    <p>
                        Testimoni dari peserta yang berhasil submit ke jurnal
                        internasional bereputasi di Scopus. Para peserta merasa
                        bahagia setelah mengikuti Scopus Camp di pusat Rumah Scopus
                        di Yogyakarta selama tiga hari.
                    </p>
                    <p>
                        Dari yang awalnya pesimis dan takut untuk tembus ke Scopus,
                        sekarang mereka sudah membuktikan sendiri bahwa tembus Scopus
                        itu mudah.
                    </p>

                    <div class="bnd-kutip-kaki">
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

    {{-- ================================================ ajakan ========= --}}
    <section class="bnd-bagian-rapat">
        <div class="container">
            <div class="bnd-ajakan" data-aos="zoom-in">
                <h2>Naskah Anda layak terbit</h2>
                <p>
                    Mulai dari satu sesi konsultasi, atau langsung ikut kelas penuh.
                    Kami bantu dari langkah pertama.
                </p>
                <div class="bnd-aksi">
                    <a href="#layanan" class="bnd-tombol bnd-tombol-utama">
                        Pilih layanan <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="{{ url('/contact') }}" class="bnd-tombol bnd-tombol-kedua">
                        <i class="bi bi-chat-dots-fill"></i> Tanya dulu
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@stop
