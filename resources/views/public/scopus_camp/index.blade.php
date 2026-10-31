@extends('public.layout.header')

@section('title', 'Scopus Camp — kelas tatap muka | Rumah Scopus')

@section('uraian', 'Tiga hari menulis artikel ilmiah sampai terkirim ke jurnal terindeks Scopus, ditemani pendamping yang sudah terbiasa menembusnya. Jadwal, biaya, dan sisa kursi tiap angkatan.')

@push('kepala')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600..900&family=Figtree:wght@400;500;700;800&display=swap">
@endpush

@section('konten')
{{--
    Scopus Camp — dirombak 22 Okt 2026 mengikuti beranda.

    Yang diganti dan kenapa:

      - PALETNYA. Gradien merah-jingga #ff3131 -> #ff914d di atas putih:
        palet lama yang sudah ditinggalkan beranda dan blog justru karena
        pemiliknya menyebutnya terlalu mirip situs lain.

      - BLOK GAYANYA menggantung di luar @section, sama seperti berkas
        kategori blog yang sudah dibetulkan: tetap tercetak, tetapi mendarat
        sebelum <!doctype html>.

      - SUSUNANNYA. Seluruh angkatan dicetak sebagai kartu poster di satu
        halaman — terukur 294 kartu setinggi 22.711px. Dan posternya SAMA
        PERSIS di tiap kartu, jadi halamannya jadi dinding berisi satu gambar
        yang diulang ratusan kali. Sekarang posternya dipajang sekali di
        kolom kiri bersama daftar fasilitas, dan angkatannya jadi baris
        berisi tanggal, lokasi, harga, dan sisa kursi.

      - TANGGALNYA memakai date('d M'), yang memulangkan "Oct" di halaman
        berbahasa Indonesia.
--}}
<style>
    /* Palet dan huruf DISALIN dari beranda.

       Ditulis lagi di sini, bukan menumpang .bnd: aturan beranda hidup di
       dalam home.blade.php dan tidak ikut terkirim ke halaman ini.
       Menumpangnya berarti halaman ini diam-diam kehilangan seluruh
       warnanya pada hari beranda dirapikan. */
    .kmp {
        --kertas: #fdf5e8;  --kertas-2: #f8ead4;
        --tinta: #16233f;   --tinta-2: #5c6b85;
        --jingga: #e76d20;  --jingga-muda: #fb8c3d; --jingga-tua: #ad4d12;
        --jingga-kuat: #b85213;
        --rumput: #0f766e;  --ungu: #5b3fd6;
        --garis: #e7dcc6;

        background: var(--kertas);
        color: var(--tinta);
        font-family: 'Figtree', system-ui, sans-serif;
        overflow-x: clip;
    }
    .kmp h1, .kmp h2, .kmp h3 {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 800; color: var(--tinta); margin: 0; letter-spacing: -.01em;
    }
    .kmp p { margin: 0; color: var(--tinta-2); line-height: 1.68; }
    .kmp a { text-decoration: none; }

    /* ========================================================= kepala */
    /* 124px di atas, sama dengan hero beranda: kepala situs berposisi fixed
       setinggi 82px dan tidak memesan ruang untuk dirinya sendiri. */
    /* 104px di atas: kepala situs berposisi fixed setinggi 82px dan tidak
       memesan ruang untuk dirinya sendiri, jadi yang benar-benar perlu cuma
       82px ditambah sedikit napas. Sisanya dulu jadi jurang kosong di layar
       pertama — terukur 120px antara akhir paragraf hero dan benda
       berikutnya, pada halaman yang justru diminta tanpa ruang kosong. */
    .kmp-kepala {
        padding: 104px 0 34px;
        background: linear-gradient(180deg, var(--kertas-2) 0%, var(--kertas) 100%);
        text-align: center;
    }
    /* Pil kertas bergaris tipis, BUKAN blok navy pekat.

       Bentuk lamanya satu-satunya benda gelap di layar pertama, jadi mata
       jatuh ke sana lebih dulu daripada ke judulnya. Disamakan dengan
       Scopus Kafe: latar kertas, garis tipis, huruf jingga tua. */
    .kmp-stiker {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 7px 16px; margin-bottom: 20px;
        background: var(--kertas-2); border: 1px solid var(--garis);
        border-radius: 999px; font-weight: 800; font-size: .85rem;
        color: var(--jingga-tua);
    }

    .kmp .kmp-judul { font-size: clamp(1.9rem, 4.2vw, 3rem); line-height: 1.12; max-width: 20ch; margin: 0 auto; }

    /* Stabilo digambar di BELAKANG hurufnya lewat gradien, warna persik.

       Kuning #ffd84d sebelumnya warna paling terang di seluruh halaman dan
       menarik mata lebih kuat daripada tombol mana pun. Disamakan dengan
       Scopus Kafe. padding-bottom pada judulnya supaya garis stabilo baris
       terakhir tidak bersentuhan dengan paragraf di bawahnya. */
    .kmp-stabilo { background: linear-gradient(180deg, transparent 62%, #fbd9b4 62%); padding: 0 .1em; }
    .kmp .kmp-judul { padding-bottom: .12em; }

    .kmp p.kmp-sub { max-width: 56ch; margin: 17px auto 0; font-size: 1.04rem; }

    /* ========================================================== isi */
    .kmp-bagian { padding: 30px 0 54px; }

    /* SATU LAJUR PENUH, bukan dua kolom tak sama.

       Bentuk sebelumnya menaruh keterangan di lajur kiri selebar 330px dan
       daftar angkatan di kanan. Terukur di 1440px: isi lajur kirinya
       berhenti sekitar 910px sementara kisinya setinggi 1.468px — jadi
       sekitar 550px lajur kiri bawah kosong melompong, dan panjang keduanya
       ditentukan data yang berubah tiap pekan sehingga jurang itu tidak bisa
       dihilangkan dengan menyetel tinggi.

       Satu lajur penuh menghapusnya dengan sendirinya, DAN memberi ruang
       untuk kartu angkatan tiga kolom — yang memotong tinggi daftarnya jadi
       sepertiga. Halamannya turun dari 2,72 layar. */
    .kmp-kisi { display: block; }

    /* TIDAK ada position: sticky di panel ini, dan itu hasil pengukuran,
       bukan selera.

       Panelnya sempat dibuat menempel dengan top: 104px. Terukur di
       peramban: di gulir 0 ia turun TEPAT 104px dari baris pertama — celah
       yang terlihat jelas — lalu saat digulung ia TIDAK menempel sama
       sekali, tepi atasnya terus naik sampai -933px. Jadi aturan itu nol
       guna dan berbiaya: satu-satunya akibatnya celah di layar pertama.

       Dicoba empat bentuk (sticky di item kisi, di kartu dalam, dengan
       bungkus blok biasa, dan dengan bungkus setinggi penuh); yang
       menghilangkan celahnya juga menghilangkan kemenempelannya. Sticky di
       blok biasa pada halaman yang SAMA bergeser 0px, jadi pemicunya
       konteks kisi, bukan gaya halaman ini. */

    /* Dua kartu penutup sejajar di layar lebar.

       1,5fr : 1fr, bukan separuh-separuh. Kartu kiri memuat tujuh butir
       fasilitas dan kanan cuma satu isian; dibagi rata, yang kiri patah jadi
       banyak baris sementara yang kanan setengahnya kosong — persis ruang
       kosong yang mau dihilangkan.

       `align-items: stretch` (bawaan) sengaja dibiarkan: tingginya jadi sama,
       dan dua kartu bersebelahan yang tingginya berbeda terbaca seperti
       salah satunya belum selesai dimuat. */
    .kmp-bawah { display: grid; gap: 16px; margin-top: 34px; }
    /* Lebar kartu kanan DIPATOK, bukan dibagi menurut rasio.

       Dibagi 1,5fr : 1fr, lebarnya ikut lebar layar — dan terukur di 1180px
       kartu kanan cuma 368px, yang setelah tombol 165px menyisakan kotak
       email 156px: tidak cukup untuk melihat satu alamat utuh sambil
       mengetiknya. Ambangnya sempat dinaikkan dari 1000px ke 1180px dan
       angkanya TIDAK membaik, sebab yang salah rasionya, bukan ambangnya.

       Dipatok 420–500px, kotaknya ±289px di layar mana pun yang cukup
       lebar. Di bawah 1200px keduanya menumpuk dan kotaknya kembali lebar. */
    @media (min-width: 1200px) {
        .kmp-bawah { grid-template-columns: minmax(0, 1fr) 420px; }
    }
    .kmp-bawah > * { min-width: 0; }
    .kmp-panel-kartu { height: 100%; }
    .kmp-panel-kartu {
        padding: 24px 26px;
        background: #fff;
        border: 1px solid var(--garis); border-radius: 22px;
        box-shadow: 0 8px 22px rgba(22, 35, 63, .06);

        /* `translate`, BUKAN `transform` — alasannya sama dengan kartu
           angkatan: keduanya kini dimunculkan AOS, yang memasang transform
           sendiri pada unsur yang sama dan mengalahkan aturan :hover. */
        transition: translate .22s cubic-bezier(.2, .7, .3, 1),
                    box-shadow .22s ease, border-color .18s ease;
    }
    .kmp-panel-kartu:hover {
        translate: 0 -3px;
        border-color: #efd7b4;
        box-shadow: 0 16px 32px rgba(22, 35, 63, .10);
    }

    /* TIDAK ADA efek pointer pada butir fasilitas, dan itu disengaja —
       lihat keterangan denyutnya di atas. Butir yang bergeser sempat ada di
       sini lalu dicabut atas permintaan pemiliknya. */

    .kmp .kmp-panel-kartu h2 { font-size: 1.12rem; margin-bottom: 12px; }
    .kmp-paket + .kmp-paket {
        margin-top: 18px; padding-top: 18px;
        border-top: 2px dashed rgba(22, 35, 63, .18);
    }
    .kmp-paket-tanda {
        display: inline-block; margin-left: 6px; padding: 2px 10px;
        border-radius: 999px; background: var(--jingga); color: #fff;
        font-family: 'Figtree', sans-serif; font-size: .72rem; font-weight: 800;
        vertical-align: middle;
    }
    .kmp-beda {
        margin-top: 20px; padding-top: 18px;
        border-top: 2px dashed rgba(22, 35, 63, .2);
    }
    .kmp-beda-butir + .kmp-beda-butir { margin-top: 14px; }
    .kmp-beda-label {
        display: inline-block; margin-bottom: 8px; padding: 3px 11px;
        border-radius: 999px; background: var(--jingga); color: #fff;
        font-family: 'Figtree', sans-serif; font-size: .78rem; font-weight: 800;
    }
    /* Pemilihnya `.kmp-beda .kmp-fasilitas-tambah i` — (0,3,0), bukan
       (0,2,0).

       Ditulis `.kmp-fasilitas-tambah i` saja, bobotnya SAMA dengan
       `.kmp-fasilitas i` yang kebetulan berdiri belakangan di berkas ini,
       jadi yang menang aturan hijau itu. Terukur: kedua ikon memulangkan
       rgb(15,118,110), dan tanda "+" tidak bisa dibedakan dari tanda
       centang — padahal justru selisih itu yang jadi inti bagian ini. */
    .kmp-beda .kmp-fasilitas-tambah i { color: var(--jingga); }
    .kmp-beda-nihil { color: var(--tinta-2); font-size: .9rem; margin: 0; }

    .kmp-varian {
        padding: 1px 9px; border-radius: 999px;
        background: color-mix(in srgb, var(--ungu) 14%, transparent);
        color: var(--ungu); font-size: .76rem; font-weight: 800;
    }
    /* Berkisi, bukan satu lajur panjang.

       Lajurnya dulu 330px, jadi tujuh butir bertumpuk memang satu-satunya
       bentuk yang muat. Melintang di lajur penuh 1.296px, satu lajur
       meninggalkan tiga perempat kartu kosong di sebelah kanannya — persis
       ruang kosong yang mau dihilangkan. */
    /* MENGALIR, bukan berkolom sama lebar.

       Bentuk sebelumnya `repeat(auto-fit, minmax(270px, 1fr))` — empat lajur
       selebar ±300px. Butirnya panjangnya berbeda-beda, jadi tiap lajur
       menyisakan ruang kosong sampai ujung lajurnya: "Penginapan di tempat
       acara" memakai separuh lajurnya, sementara "Pendampingan lanjutan
       sampai jurnal memberi jawaban" di sebelahnya justru terpaksa patah
       jadi dua baris. Kosong dan sesak bersebelahan, pada kisi yang sama.

       Mengalir, tiap butir selebar isinya sendiri dan yang berikutnya
       langsung menempel. Jaraknya tetap diatur `gap`, jadi rapat bukan
       berarti berdempetan. */
    /* BERLAJUR LAGI, tetapi lajurnya selebar butir terpanjang.

       Mengalir bebas memang rapat, tetapi awal tiap butir jadi berbeda di
       tiap baris — terukur 99px, 360px, lalu 99px, 517px, lalu 99px, 442px.
       Tujuh butir yang awalnya berloncatan begitu terbaca seperti daftar
       yang belum disusun.

       Lebar lajurnya 390px, dan angka itu DIUKUR bukan dikira: butir
       terpanjang ("Pendampingan lanjutan sampai jurnal memberi jawaban")
       membutuhkan 364px untuk teksnya ditambah 26px untuk ikon dan jaraknya.
       Dipatok 340px seperti percobaan sebelumnya, butir itu patah jadi dua
       baris sementara enam lainnya satu baris — dan satu butir yang
       menjulang di antara enam yang rata justru lebih berantakan daripada
       lajur yang berloncatan.

       auto-fit membuatnya menyesuaikan sendiri: di kartu selebar 726px ia
       jadi SATU lajur (dua lajur butuh 808px), dan di kartu yang lebih lebar
       ia jadi dua. Berapa pun lebarnya, tidak ada butir yang patah. */
    .kmp-fasilitas {
        list-style: none; padding: 0; margin: 0;
        display: grid; gap: 8px 20px;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 390px), 1fr));
    }

    /* Ikon dan teks SEJAJAR pada baris pertama.

       Sebelumnya ikonnya digeser `margin-top: 4px` — angka yang dulu
       dikira-kira. Terukur: ketujuh butir meleset 4px ke bawah dari titik
       tengah baris pertama teksnya, serempak. Angka tetap memang tidak bisa
       benar; ia harus mengikuti tinggi baris.

       Kotak ikonnya dibuat setinggi SATU BARIS teks (1.5em, sama dengan
       line-height-nya) lalu isinya dipusatkan di dalam kotak itu. Dengan
       begitu ia ikut benar saat butirnya satu baris maupun dua. */
    .kmp-fasilitas li {
        display: flex; gap: 9px; align-items: flex-start; padding: 2px 0;
        color: var(--tinta); font-size: .92rem; line-height: 1.5;
    }
    .kmp-fasilitas i {
        flex: 0 0 auto; color: var(--rumput);
        display: inline-flex; align-items: center; justify-content: center;
        height: 1.5em;
    }

    /* Tanda centangnya BERDENYUT, teksnya tidak.

       Diminta pemiliknya 31 Okt 2026: geseran teks saat pointer lewat
       dicabut — di daftar tujuh butir, teks yang bergerak membuat matanya
       kehilangan baris yang sedang dibaca.

       Denyutnya 1 -> 1,14 -> 1 dalam 2,6 detik, dan tiap butir dimulai
       0,16 detik lebih lambat daripada sebelumnya. Serempak, tujuh tanda
       yang mengembang bersamaan terbaca seperti halaman yang berkedip;
       bergiliran, ia terbaca sebagai riak yang mengalir sekali lalu lewat. */
    @keyframes kmpDenyut {
        0%, 100% { transform: scale(1); }
        50%      { transform: scale(1.14); }
    }
    .kmp-fasilitas i { animation: kmpDenyut 2.6s ease-in-out infinite; }
    .kmp-fasilitas li:nth-child(2) i { animation-delay: .16s; }
    .kmp-fasilitas li:nth-child(3) i { animation-delay: .32s; }
    .kmp-fasilitas li:nth-child(4) i { animation-delay: .48s; }
    .kmp-fasilitas li:nth-child(5) i { animation-delay: .64s; }
    .kmp-fasilitas li:nth-child(6) i { animation-delay: .80s; }
    .kmp-fasilitas li:nth-child(7) i { animation-delay: .96s; }
    .kmp-fasilitas li:nth-child(8) i { animation-delay: 1.12s; }

    /* ====================================================== angkatan */
    /* Kartu angkatan BERKISI, bukan satu tumpukan baris selebar layar.

       Dua belas baris setinggi 98px adalah 1.341px yang harus digulir orang
       untuk melihat dua belas tanggal — padahal yang dibandingkan cuma
       tanggal, kota, dan harga. Tiga kolom memuat yang sama dalam empat
       baris. */
    .kmp-deret {
        display: grid; gap: 14px;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 330px), 1fr));
        align-items: start;
    }

    /* Penomoran halaman MELINTANG seluruh kisi; tanpa ini ia jadi satu sel
       selebar sepertiga dan nomornya membungkus jadi tiga baris. */
    .kmp-halaman { grid-column: 1 / -1; }

    /* Bayangannya navy tembus pandang, BUKAN jingga pekat.

       Dua belas baris berbayang jingga penuh membuat warna ajakan utama
       halaman ini — tombol dan harga — tidak lagi menonjol dari apa pun:
       jingga ada di mana-mana, jadi ia berhenti berarti "ini yang diklik".
       Angka bayangannya disamakan dengan kartu Scopus Kafe. */
    .kmp-baris {
        display: grid; grid-template-columns: 58px minmax(0, 1fr);
        grid-template-areas: 'tgl isi' 'kaki kaki';
        gap: 12px 14px; align-items: start;
        padding: 16px 18px;
        background: #fff;
        border: 2px solid var(--tinta); border-radius: 18px;
        box-shadow: 6px 6px 0 rgba(22, 35, 63, .12);
        color: var(--tinta);
        transition: translate .22s cubic-bezier(.2, .7, .3, 1),
                    box-shadow .22s cubic-bezier(.2, .7, .3, 1),
                    border-color .18s ease;
    }
    /* ANGKATNYA LEWAT `translate`, BUKAN `transform`.

       Ditulis sebagai transform, kartunya TIDAK terangkat sama sekali —
       terukur lewat DevTools: computed transform-nya tetap "none" saat
       :hover dipaksa, sementara warna garis dan bayangannya berubah dengan
       benar. Sebabnya AOS: aturannya
       `[data-aos^="fade"][data-aos^="fade"].aos-animate` berbobot (0,3,0)
       dan memasang `transform: translate3d(0,0,0)` pada kartu yang sama,
       mengalahkan `.kmp-baris:hover` yang (0,2,0).

       Menaikkan bobot pemilihnya bisa saja, tetapi berarti menang tipis atas
       pustaka yang boleh berubah kapan pun ia diperbarui. `translate`
       properti TERSENDIRI — ia tidak bertabrakan dengan transform milik AOS,
       melainkan bertumpuk dengannya.

       Empat piksel, tidak lebih. Kartu yang melompat besar membuat yang di
       sebelahnya terbaca ikut bergeser — mata menangkap gerak, bukan jarak. */
    .kmp-baris:hover {
        color: var(--tinta);
        translate: 0 -4px;
        border-color: var(--jingga);
        box-shadow: 6px 11px 0 rgba(231, 109, 32, .18);
    }

    /* Ditekan, kartunya turun kembali: tanpa keadaan ini tombol terasa
       menggantung — sudah diklik tetapi tidak membalas apa pun sampai
       halaman berikutnya datang. */
    .kmp-baris:active {
        translate: 0 -1px;
        box-shadow: 4px 5px 0 rgba(231, 109, 32, .22);
    }

    /* Tanda tanggalnya ikut hidup sedikit. Diputar -2 derajat, bukan
       diperbesar saja: pembesaran murni pada kotak berisi angka terbaca
       seperti salah render, sementara miring sedikit terbaca sebagai stiker
       yang terangkat. */
    .kmp-baris .kmp-tgl {
        transition: transform .22s cubic-bezier(.2, .7, .3, 1),
                    box-shadow .22s ease;
    }
    .kmp-baris:hover .kmp-tgl {
        transform: scale(1.06) rotate(-2.5deg);
        box-shadow: 0 6px 14px rgba(231, 109, 32, .32);
    }

    /* Nama angkatannya berganti warna, bukan bergaris bawah: garis bawah
       pada judul tebal berserif menambah satu garis lagi ke kartu yang
       sudah penuh garis. */
    .kmp-nama { transition: color .18s ease; }
    .kmp-baris:hover .kmp-nama { color: var(--jingga-tua); }
    /* Yang kuotanya habis tidak boleh terlihat sama menariknya dengan yang
       masih bisa didaftari. */
    .kmp-baris.kmp-penuh {
        background: var(--kertas-2); border-color: var(--garis);
        box-shadow: none; opacity: .75;
    }
    /* Yang kursinya habis TIDAK ikut hidup sama sekali.

       Kartu yang terangkat, bergaris jingga, dan panahnya bergeser adalah
       janji "ini bisa diklik". Pada angkatan yang sudah penuh janji itu
       dilanggar sedetik kemudian, dan yang mengkliknya merasa dipermainkan
       oleh halaman yang kami sendiri yang membuatnya bergerak. */
    .kmp-baris.kmp-penuh:hover,
    .kmp-baris.kmp-penuh:active {
        translate: none; box-shadow: none; border-color: var(--garis);
    }
    .kmp-baris.kmp-penuh:hover .kmp-tgl { transform: none; box-shadow: none; }
    .kmp-baris.kmp-penuh:hover .kmp-nama { color: var(--tinta); }

    .kmp-tgl {
        grid-area: tgl;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: 8px 4px; border-radius: 13px;
        background: var(--jingga); color: #fff; line-height: 1;
    }
    .kmp-penuh .kmp-tgl { background: var(--tinta-2); }
    .kmp-tgl strong { font-family: 'Fraunces', serif; font-weight: 900; font-size: 1.42rem; }
    .kmp-tgl span {
        margin-top: 4px; font-size: .72rem; font-weight: 800;
        letter-spacing: .1em; text-transform: uppercase;
    }

    .kmp-isi { grid-area: isi; display: flex; flex-direction: column; gap: 5px; min-width: 0; }
    .kmp-nama { font-family: 'Fraunces', serif; font-weight: 800; font-size: 1.1rem; line-height: 1.3; }
    .kmp-rinci {
        display: flex; flex-wrap: wrap; gap: 4px 16px;
        color: var(--tinta-2); font-size: .85rem; font-weight: 600;
    }
    .kmp-rinci i { margin-right: 4px; color: var(--jingga); }

    /* Harga dan ajakan turun ke BARIS SENDIRI, melintang di kaki kartu.

       Di lajur selebar 400px, tiga lajur sebaris membuat nama angkatan
       tinggal dua pertiga kata — "Scopus Camp Yogyak…". Di kaki kartu
       keduanya punya lebar penuh, dan matanya membaca satu arah: tanggal,
       nama, lalu harga. */
    .kmp-kanan {
        grid-area: kaki;
        display: flex; flex-wrap: wrap; align-items: center; gap: 6px 10px;
        padding-top: 11px; border-top: 1px dashed var(--garis);
        white-space: nowrap;
    }
    .kmp-ajak { margin-left: auto; }
    .kmp-coret { color: var(--tinta-2); font-size: .82rem; font-weight: 600; }
    .kmp-harga { font-family: 'Fraunces', serif; font-weight: 800; font-size: 1.12rem; }
    /* Warnanya ditulis utuh, bukan var(--stabilo): token itu dibuang
       bersama kuning #ffd84d, dan nilai yang tidak ada membuat lencana ini
       kehilangan latarnya sama sekali tanpa satu galat pun. */
    .kmp-hemat {
        padding: 1px 9px; border-radius: 999px;
        background: #fbd9b4; color: var(--jingga-tua);
        font-size: .72rem; font-weight: 800;
    }
    /* ================================================ kabar kilat & tautan */
    .kmp-kabar {
        display: flex; gap: 12px; align-items: flex-start;
        padding: 14px 18px; margin: 0 0 20px; border-radius: 14px;
        font-size: 15px; line-height: 1.6; border: 1px solid;
    }
    .kmp-kabar i { flex: 0 0 auto; margin-top: 3px; font-size: 18px; }
    .kmp-kabar.sukses { background: #eafaf6; border-color: #a7e3d6; color: #0b5a52; }
    .kmp-kabar.galat  { background: #fdecea; border-color: #f0bdb6; color: #8e2318; }

    /* ======================== borang "kehilangan tautan"

       Di lajur keterangan, tepat di bawah kartu "Yang kamu dapat".

       Riwayat letaknya: mula-mula di kaki halaman — terukur 2,4 layar ke
       bawah di laptop dan 3,9 layar di ponsel — lalu sempat jadi pita
       selebar layar di bawah hero (0,5 layar), dan kini di sini (1,1 layar).
       Setengah layar lebih jauh daripada pita, dan itu harga yang dibayar
       dengan sadar: lajur ini memang lajur keterangan, jadi borangnya duduk
       bersama hal-hal yang dibaca orang SEBELUM memilih angkatan, dan daftar
       angkatannya tidak lagi terdorong ke bawah.

       MENUMPUK KE BAWAH, bukan sebaris: lajurnya 330 px. Dipaksa sebaris,
       kotak email tinggal ~120 px — terlalu sempit untuk melihat alamat yang
       sedang diketik, dan alamat yang salah ketik berarti suratnya terbang ke
       tempat lain tanpa ada yang tahu. */
    /* Satu baris mendatar di layar lebar, bukan kartu tegak.

       Duduk di atas daftar angkatan, kartu tegak setinggi tiga baris dengan
       tombol selebar 1.296px mendorong kartu angkatan pertama turun — dan
       yang dicari kebanyakan orang di halaman ini angkatannya, bukan borang
       ini. Mendatar ia setinggi satu isian saja. */
    .kmp-lacak {
        margin: 0; padding: 16px 18px; border-radius: 18px;
        background: #fff; border: 1px solid var(--garis);
        box-shadow: 0 6px 20px rgba(22, 35, 63, .05);
        display: grid; gap: 12px; align-items: center;
        transition: translate .22s cubic-bezier(.2, .7, .3, 1),
                    box-shadow .22s ease, border-color .18s ease;
    }

    /* :focus-within ikut, bukan cuma :hover.

       Yang sampai ke borang ini lewat papan ketik — Tab dari penomoran
       halaman — tidak pernah menyentuhnya dengan pointer. Tanpa
       :focus-within, kartunya diam sementara kursor sudah berkedip di
       dalamnya, dan tidak ada yang menandai bahwa isiannya sudah aktif. */
    .kmp-lacak:hover,
    .kmp-lacak:focus-within {
        translate: 0 -3px;
        border-color: var(--jingga);
        box-shadow: 0 16px 32px rgba(231, 109, 32, .14);
    }

    .kmp-lacak-ikon { transition: transform .25s cubic-bezier(.2, .7, .3, 1); }
    .kmp-lacak:hover .kmp-lacak-ikon,
    .kmp-lacak:focus-within .kmp-lacak-ikon { transform: rotate(-8deg) scale(1.08); }
    @media (min-width: 860px) {
        .kmp-lacak { grid-template-columns: minmax(0, 1fr) minmax(360px, 460px); gap: 24px; }
        .kmp-lacak .kmp-lacak-isian {
            margin-top: 0; grid-template-columns: minmax(0, 1fr) auto;
        }
    }

    /* Begitu kartunya duduk di lajur kanan yang sempit, bentuk mendatarnya
       DIBATALKAN: keterangan kembali ke atas dan isiannya di bawah. Aturan
       mendatar di atas memakai lebar LAYAR, bukan lebar kartunya — dibiarkan,
       kotak email di lajur 430px terjepit jadi beberapa puluh piksel. */
    @media (min-width: 1200px) {
        .kmp-bawah .kmp-lacak {
            grid-template-columns: 1fr; gap: 14px; align-content: center;
        }
        .kmp-bawah .kmp-lacak .kmp-lacak-isian {
            grid-template-columns: minmax(0, 1fr) auto;
        }
    }

    .kmp-lacak-kata {
        display: flex; align-items: flex-start; gap: 12px;
        font-size: 14px; line-height: 1.55; color: #5c6b85;
    }
    .kmp-lacak-kata strong {
        display: block; color: #16233f; font-size: 15.5px; margin-bottom: 2px;
    }

    .kmp-lacak-ikon {
        flex: 0 0 auto;
        display: grid; place-items: center; width: 40px; height: 40px;
        border-radius: 12px; font-size: 18px;
        background: linear-gradient(135deg, #fde8d3, #fbd9b4); color: #ad4d12;
    }

    .kmp-lacak-isian { display: grid; gap: 9px; margin-top: 14px; }

    .kmp-lacak-isian input {
        width: 100%; min-width: 0; padding: 12px 14px; font-size: 15px;
        border: 1px solid #ddd0b8; border-radius: 12px; background: #fdfcf9;
        color: #16233f;
    }
    .kmp-lacak-isian input::placeholder { color: #9aa6ba; }
    .kmp-lacak-isian input:focus {
        outline: 2px solid #e76d20; outline-offset: 1px; background: #fff;
    }

    .kmp-lacak-isian button {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; padding: 12px 16px; font-size: 15px; font-weight: 700;
        border: 0; border-radius: 12px; cursor: pointer;
        background: #16233f; color: #fff;
        transition: background .18s ease, translate .18s ease, box-shadow .18s ease;
    }
    .kmp-lacak-isian button:hover {
        background: #23365c;
        translate: 0 -2px;
        box-shadow: 0 8px 18px rgba(22, 35, 63, .24);
    }
    .kmp-lacak-isian button:active { translate: 0 0; box-shadow: none; }

    /* Di layar sempit lajurnya melebar penuh, jadi kotak dan tombolnya muat
       sebaris — dan sebaris di situ lebih baik: ia memendekkan satu blok lagi
       di halaman yang sudah panjang. */
    @media (min-width: 576px) and (max-width: 992px) {
        .kmp-lacak-isian { grid-template-columns: minmax(0, 1fr) auto; }
        .kmp-lacak-isian button { width: auto; white-space: nowrap; }
    }

    .kmp-sisa {
        padding: 1px 9px; border-radius: 999px;
        background: var(--jingga-kuat); color: #fff;
        font-size: .72rem; font-weight: 800;
    }
    .kmp-tutup {
        padding: 2px 11px; border-radius: 999px;
        background: var(--tinta-2); color: #fff;
        font-size: .74rem; font-weight: 800;
    }
    .kmp-ajak {
        display: inline-flex; align-items: center; gap: 7px; margin-top: 3px;
        color: var(--jingga-tua); font-weight: 800; font-size: .9rem;
        transition: gap .2s ease;
    }
    .kmp-ajak i { transition: transform .22s cubic-bezier(.2, .7, .3, 1); }
    .kmp-baris:hover .kmp-ajak { gap: 11px; }
    .kmp-baris:hover .kmp-ajak i { transform: translateX(3px); }

    .kmp-halaman { display: flex; justify-content: center; margin-top: 34px; }
    /* justify-content: center DI DAFTARNYA, bukan cuma di pembungkusnya.

       Pembungkusnya sudah `display:flex; justify-content:center`, dan itu
       memusatkan BLOK penomorannya sebagai satu kesatuan — cukup selama
       nomornya muat satu baris. Begitu membungkus, tiap barisnya tetap
       dimulai dari tepi kiri daftarnya.

       Terukur di 390px: 11 tombol pecah jadi dua baris, dan baris keduanya
       melenceng 118px ke kiri dari titik tengah layar. Di 360px melencengnya
       81px. Di 768px nomornya masih muat satu baris, jadi cacatnya tidak
       pernah terlihat dari layar lebar sama sekali. */
    .kmp-halaman .pagination {
        gap: 6px; margin: 0; flex-wrap: wrap; justify-content: center;
    }

    /* Tombolnya bulat, bergaris tipis, tanpa bayangan keras.

       Bentuk lamanya kotak bergaris 2px bertumpuk bayangan — empat belas
       benda sekeras kartu angkatan, berjejer di bawahnya. Penomoran halaman
       bukan isi halaman; ia tidak boleh seberat yang dinomorinya. */
    .kmp-halaman .page-item .page-link {
        min-width: 38px; padding: 7px 12px;
        background: #fff; color: var(--tinta);
        border: 1px solid var(--garis); border-radius: 999px;
        box-shadow: none;
        font-weight: 700; font-size: .92rem; text-align: center;
        transition: border-color .15s ease, color .15s ease;
    }
    .kmp-halaman .page-item .page-link:hover {
        border-color: var(--jingga); color: var(--jingga-tua);
    }
    .kmp-halaman .page-item.active .page-link {
        background: var(--jingga); border-color: var(--jingga);
        color: #fff; box-shadow: none;
    }
    .kmp-halaman .page-item.disabled .page-link {
        background: transparent; color: #b6aa94;
        border-color: var(--garis); box-shadow: none;
    }

    .kmp-kosong {
        max-width: 48ch; margin: 0 auto; padding: 48px 30px; text-align: center;
        background: var(--kertas-2);
        border: 2px dashed rgba(22, 35, 63, .28); border-radius: 22px;
    }
    .kmp-kosong i { font-size: 2.2rem; color: var(--jingga); }
    .kmp .kmp-kosong h2 { margin: 14px 0 9px; font-size: 1.3rem; }

    .kmp-tombol {
        display: inline-flex; align-items: center; gap: 9px;
        padding: 12px 22px; margin-top: 20px;
        background: var(--jingga-kuat); color: #fff;
        border: 2px solid var(--tinta); border-radius: 999px;
        box-shadow: 4px 4px 0 var(--tinta); font-weight: 800;
    }
    .kmp-tombol:hover { color: #fff; background: var(--tinta); }

    @media (max-width: 992px) {
        .kmp-kisi { grid-template-columns: 1fr; gap: 30px; }
    }
    @media (max-width: 600px) {
        .kmp-kepala { padding: 104px 0 40px; }
        .kmp-baris { grid-template-columns: 58px minmax(0, 1fr); gap: 14px; padding: 15px 16px; }
        .kmp-kanan {
            grid-column: 1 / -1;
            flex-direction: row; align-items: center; justify-content: flex-start;
            flex-wrap: wrap; gap: 6px 12px;
            padding-top: 11px; border-top: 2px dashed rgba(22, 35, 63, .14);
        }
        .kmp-ajak { margin-top: 0; margin-left: auto; }
    }

    /* Yang minta gerak dikurangi tidak cukup dimatikan TRANSISINYA saja.

       Aturan lamanya hanya `transition: none`, jadi transform-nya tetap
       berlaku — kartunya tetap melompat 4px dan tanda tanggalnya tetap
       miring, cuma seketika alih-alih mulus. Bagi yang memasang setelan itu
       karena vertigo atau mabuk gerak, perpindahan mendadak justru lebih
       buruk daripada yang mulus. Animasi dan transform ikut dimatikan;
       warna dan garis tetap berubah, sebab keduanya tidak bergerak. */
    @media (prefers-reduced-motion: reduce) {
        .kmp *, .kmp *::before, .kmp *::after {
            transition: none !important;
            animation: none !important;
        }
        .kmp .kmp-baris:hover,
        .kmp .kmp-baris:active,
        .kmp .kmp-panel-kartu:hover,
        .kmp .kmp-lacak:hover,
        .kmp .kmp-lacak:focus-within,
        .kmp .kmp-lacak-isian button:hover { translate: none !important; }
        .kmp .kmp-baris:hover .kmp-tgl,
        .kmp .kmp-baris:hover .kmp-ajak i,
        .kmp .kmp-fasilitas i,
        .kmp .kmp-lacak:hover .kmp-lacak-ikon,
        .kmp .kmp-lacak:focus-within .kmp-lacak-ikon { transform: none !important; }
    }
</style>

<div class="kmp">

    {{-- ====================================== kabar kilat & jalan kembali

         Pesan galat sebelumnya TIDAK DIGAMBAR DI MANA PUN pada jalur layanan
         ini. Terukur 25 Okt 2026: membuka borang angkatan yang sudah ditutup
         memantulkan orangnya ke halaman ini dengan ->with('error'), dan
         halaman ini cuma punya penangan 'success' — yang pun mengabaikan isi
         pesannya dan selalu berbunyi "Data Berhasil Terkirim! Mohon tunggu
         verifikasi 1x24 jam", kalimat yang sudah tidak benar sejak
         pembayarannya lewat gerbang. --}}
    {{-- DIGAMBAR HANYA KALAU ADA ISINYA.

         Pembungkusnya berpadding-atas 96 px untuk melewati kepala halaman
         yang melayang. Digambar selalu, padding itu jadi 96 px ruang kosong
         di atas hero pada SETIAP kunjungan — padahal hero-nya sendiri sudah
         punya ruang atasnya. --}}
    @if (session('error') || session('success') || $errors->any())
    <div class="container" style="padding-top: 96px;">
        @if (session('error'))
            <div class="kmp-kabar galat" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if (session('success'))
            <div class="kmp-kabar sukses" role="status">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="kmp-kabar galat" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif
    </div>
    @endif


    {{-- ==================================================== kepala ===== --}}
    <section class="kmp-kepala">
        <div class="container">
            <span class="kmp-stiker"><i class="bi bi-geo-alt-fill"></i> Kelas tatap muka · 3 hari</span>

            <h1 class="kmp-judul">
                Datang bawa data, pulang naskahmu sudah <span class="kmp-stabilo">terkirim</span>
            </h1>

            <p class="kmp-sub">
                Tiga hari menulis bareng pendamping yang sudah terbiasa menembus
                jurnal terindeks Scopus. Bukan duduk mendengarkan ceramah —
                naskahmu sendiri yang dikerjakan.
                @if ($categories->total() > 0)
                    Ada {{ $categories->total() }} angkatan yang sedang dibuka.
                @endif
            </p>
        </div>
    </section>

    {{-- ================================================== angkatan ===== --}}
    <section class="kmp-bagian">
        <div class="container">
            @if ($categories->isEmpty())
                <div class="kmp-kosong">
                    <i class="bi bi-calendar-x"></i>
                    <h2>Belum ada angkatan yang dibuka</h2>
                    <p>
                        Jadwal berikutnya biasanya diumumkan beberapa minggu sebelum
                        acaranya. Kabari admin kalau mau diberi tahu duluan.
                    </p>
                    <a href="{{ route('blog.contact.kontak') }}" class="kmp-tombol">
                        <i class="bi bi-chat-dots-fill"></i> Hubungi admin
                    </a>
                </div>
            @else
                <div class="kmp-kisi">


                    @php
                        /*
                         * Penanda varian hanya muncul kalau halaman ini memang
                         * memuat lebih dari satu. Dengan satu varian saja,
                         * lencana "Pulau Jawa" di tiap baris cuma pengulangan
                         * yang tidak membedakan apa pun.
                         */
                        $banyakVarian = $categories->getCollection()
                            ->pluck('varian')->filter()->unique()->count() > 1;
                    @endphp

                    <div class="kmp-deret">
                        @foreach ($categories as $i => $item)
                            @php
                                /*
                                 * Kuota NULL berarti TIDAK DIBATASI, bukan nol.
                                 *
                                 * Syarat lamanya `sisa_kuota === null || <= 0`,
                                 * jadi angkatan tanpa batas kuota dipajang
                                 * "Pendaftaran Ditutup". Belum pernah terasa
                                 * karena kebetulan semua angkatan yang ada
                                 * mengisi kuotanya.
                                 */
                                $penuh = $item->total_kuota !== null && (int) $item->sisa_kuota < 1;

                                $hargaAsal = (int) $item->harga_sebelum_promo;
                                $hargaBayar = (int) $item->harga_setelah_promo;
                                $adaDiskon = $hargaAsal > $hargaBayar && $hargaBayar > 0;
                                $hematPersen = $adaDiskon ? (int) round((($hargaAsal - $hargaBayar) / $hargaAsal) * 100) : 0;

                                $mulai = \Carbon\Carbon::parse($item->mulai)->locale('id');
                                $selesai = $item->selesai ? \Carbon\Carbon::parse($item->selesai) : null;
                            @endphp

                            @if ($penuh)
                                <div class="kmp-baris kmp-penuh">
                            @else
                                <a href="{{ route('public.scopuscamp.Selengkapnya', ['id' => $item->id]) }}"
                                    class="kmp-baris" data-aos="fade-up"
                                    {{-- Jedanya berpola TIGA, mengikuti tiga lajur kisinya.

                                         Dulu `% 4` — sisa dari bentuk lama yang
                                         satu lajur. Di kisi tiga kolom polanya
                                         tidak lagi jatuh sebaris: kartu pertama
                                         baris kedua kebagian jeda 240ms
                                         sementara tetangganya 0ms, jadi
                                         munculnya terbaca acak, bukan mengalir
                                         dari kiri ke kanan. --}}
                                    data-aos-delay="{{ ($i % 3) * 80 }}">
                            @endif

                                <span class="kmp-tgl">
                                    <strong>{{ $mulai->translatedFormat('j') }}</strong>
                                    <span>{{ $mulai->translatedFormat('M') }}</span>
                                </span>

                                <span class="kmp-isi">
                                    <span class="kmp-nama">
                                        {{ $item->nama }}@if ($item->nama_ke) <span style="color: var(--tinta-2);">#{{ $item->nama_ke }}</span>@endif
                                    </span>
                                    <span class="kmp-rinci">
                                        <span><i class="bi bi-calendar3"></i>
                                            {{ \App\Support\RentangTanggal::tulis($mulai, $selesai, true) }}</span>
                                        @if ($item->lokasi)
                                            <span><i class="bi bi-geo-alt"></i> {{ $item->lokasi }}</span>
                                        @endif
                                        @if ($banyakVarian && $item->varian)
                                            <span class="kmp-varian">
                                                {{ $namaVarian[$item->varian] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $item->varian)) }}
                                            </span>
                                        @endif
                                        @if ($item->total_kuota !== null && ! $penuh)
                                            <span><i class="bi bi-people"></i> kuota {{ $item->total_kuota }}</span>
                                        @endif
                                    </span>
                                </span>

                                <span class="kmp-kanan">
                                    @if ($penuh)
                                        <span class="kmp-tutup"><i class="bi bi-lock-fill"></i> Kursinya habis</span>
                                    @else
                                        {{-- Harga nol DIAM, tidak jadi "Rp 0".

                                             Terukur ada angkatan yang biayanya
                                             memang 0 di basis data; "Rp 0" di
                                             samping angkatan lain yang 5,5 juta
                                             terbaca sebagai sistem yang rusak,
                                             bukan sebagai kelas gratis. --}}
                                        @if ($hargaBayar > 0)
                                            @if ($adaDiskon)
                                                <s class="kmp-coret">Rp {{ number_format($hargaAsal, 0, ',', '.') }}</s>
                                            @endif
                                            <span class="kmp-harga">Rp {{ number_format($hargaBayar, 0, ',', '.') }}</span>

                                            @if ($hematPersen > 0)
                                                <span class="kmp-hemat">hemat {{ $hematPersen }}%</span>
                                            @endif
                                        @endif

                                        {{-- Sisa kursi disebut hanya kalau tinggal
                                             sedikit. "20 kursi" tidak mendesak siapa
                                             pun; menyebutnya saat masih longgar
                                             membuat angka itu kehilangan arti waktu
                                             benar-benar sempit. --}}
                                        @if ($item->total_kuota !== null && (int) $item->sisa_kuota <= 5)
                                            <span class="kmp-sisa">sisa {{ (int) $item->sisa_kuota }} kursi</span>
                                        @endif

                                        <span class="kmp-ajak">Lihat & daftar <i class="bi bi-arrow-right"></i></span>
                                    @endif
                                </span>

                            @if ($penuh)
                                </div>
                            @else
                                </a>
                            @endif
                        @endforeach

                        @if ($categories->hasPages())
                            <div class="kmp-halaman">{{ $categories->links() }}</div>
                        @endif
                    </div>

                    {{-- Poster dan fasilitasnya SEKALI di sini, bukan di tiap
                         kartu: flyer-nya sama persis untuk semua angkatan. --}}
                    {{-- Dua kartu penutup SEJAJAR di layar lebar.

                         Ditumpuk, keduanya memakan dua blok penuh padahal
                         isinya sama-sama pendek — kartu lacak cuma setinggi
                         satu isian, dan separuh lebarnya kosong. Sejajar,
                         keduanya memakai lebar yang sama dan halaman ikut
                         memendek. Diminta pemiliknya 31 Okt 2026. --}}
                    <div class="kmp-bawah">
                        <div class="kmp-panel" data-aos="fade-up">
                            <div class="kmp-panel-kartu">
                                {{-- POSTER TIDAK DIPAJANG di sini, dan itu keputusan.

                                     Versi pertama rombakan ini memasangnya sekali di
                                     panel, diambil dari angkatan pertama. Begitu
                                     halaman ini memuat Jawa bersama luar Jawa, satu
                                     flyer berdiri di atas dua paket yang isinya
                                     berbeda — dan flyer itu mewakili salah satunya
                                     saja.

                                     Sempat dicoba menampilkannya hanya kalau seluruh
                                     angkatan memakai gambar yang sama; terukur dengan
                                     data sebenarnya ia nyaris tidak pernah lolos,
                                     sebab sebagian angkatan memang tidak berflyer.
                                     Aturan yang hampir selalu salah lebih baik
                                     dibuang daripada dipelihara.

                                     Yang dibutuhkan orang di halaman daftar adalah
                                     APA YANG DIDAPAT, dan itu ada di bawah ini.
                                     Flyer tiap angkatan tetap tampil di halaman
                                     detailnya masing-masing. --}}

                                {{-- Yang SAMA ditulis sekali, selisihnya disebut
                                     terpisah.

                                     Bentuk sebelumnya memajang dua daftar utuh
                                     berdampingan — dan terukur, keduanya mengulang
                                     5 dari 7 butir yang sama. Untuk tahu bedanya,
                                     pembaca harus membandingkan dua daftar baris
                                     per baris. Sasaran halaman ini orang berusia
                                     lanjut, dan yang mereka cari cuma satu hal:
                                     apa bedanya.

                                     Sebelum itu pun pernah lebih buruk: SATU daftar
                                     untuk semua varian, diambil dari angkatan
                                     pertama — jadi peserta luar Jawa dijanjikan
                                     penginapan dan kolam renang yang tidak akan ia
                                     dapatkan, padahal membayar satu juta lebih
                                     mahal. --}}
                                @if ($pisahkan)
                                    <h2>Yang kamu dapat</h2>
                                    <ul class="kmp-fasilitas">
                                        @foreach ($fasilitasUmum as $butir)
                                            <li><i class="bi bi-check-circle-fill"></i> <span>{{ $butir }}</span></li>
                                        @endforeach
                                    </ul>

                                    <div class="kmp-beda">
                                        <h2>Bedanya</h2>

                                        @foreach ($paket as $p)
                                            <div class="kmp-beda-butir">
                                                <span class="kmp-beda-label">{{ $p['label'] ?: 'Paket lain' }}</span>

                                                @if (! empty($p['khusus']))
                                                    <ul class="kmp-fasilitas kmp-fasilitas-tambah">
                                                        @foreach ($p['khusus'] as $butir)
                                                            <li><i class="bi bi-plus-circle-fill"></i> <span>{{ $butir }}</span></li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    {{-- Disebut, bukan didiamkan: paket yang tidak
                                                         muncul di daftar "bedanya" membuat
                                                         pembacanya menebak apakah ia terlewat. --}}
                                                    <p class="kmp-beda-nihil">Yang di atas saja, tanpa tambahan.</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    @foreach ($paket as $p)
                                        <div class="kmp-paket">
                                            <h2>
                                                Yang kamu dapat
                                                @if ($paket->count() > 1 && $p['label'])
                                                    <span class="kmp-paket-tanda">{{ $p['label'] }}</span>
                                                @endif
                                            </h2>
                                            <ul class="kmp-fasilitas">
                                                @foreach ($p['fasilitas'] as $butir)
                                                    <li><i class="bi bi-check-circle-fill"></i> <span>{{ $butir }}</span></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                        </div>

                        {{-- ================== jalan kembali bagi yang kehilangan tautannya

                             PALING BAWAH, sesudah kartu "Yang kamu dapat".
                             Keputusan pemiliknya 31 Okt 2026, alasannya tegas:
                             yang membuka halaman ini harus langsung tertuju ke
                             jadwalnya, bukan ke borang yang cuma dibutuhkan
                             sebagian kecil orang.

                             Riwayatnya panjang dan tiap langkahnya diukur. Mula-
                             mula di kaki halaman: 2,4 layar ke bawah di laptop
                             dan 3,9 layar di ponsel, di balik dua belas baris
                             angkatan setinggi 1.341px dan penomoran 29 nomor.
                             Lalu dipindah ke lajur keterangan kiri (1,1 layar),
                             lalu ke atas daftar saat lajur itu dibubarkan.

                             Kembali ke bawah SEKARANG tidak mengulang kesalahan
                             yang sama, dan itu bukan pembelaan melainkan
                             pengukuran: halamannya sudah dipendekkan dari 2,72
                             layar jadi 2,2 — daftar angkatannya kini berkisi
                             tiga kolom setinggi 731px, bukan tumpukan 1.341px.
                             Jarak ke borang ini tinggal sekitar 1,4 layar,
                             lebih dekat daripada saat ia duduk di lajur kiri.

                             Alamat halaman status memuat UUID yang tidak bisa
                             ditebak, jadi yang menutup tabnya dan tidak
                             menerima suratnya kehilangan pendaftarannya untuk
                             selamanya, termasuk tombol bayarnya. Tautannya
                             dikirim ke EMAIL, tidak ke layar: ditampilkan,
                             siapa pun yang menebak alamat email orang lain
                             bisa membuka pendaftarannya — di halaman itu ada
                             nama, nomor telepon, afiliasi, dan daftar
                             pesertanya. --}}
                        <form method="POST" action="{{ route('public.scopuscamp.tautansaya') }}"
                            class="kmp-lacak" data-aos="fade-up" data-aos-delay="80">
                            @csrf

                            <div class="kmp-lacak-kata">
                                <span class="kmp-lacak-ikon"><i class="bi bi-ticket-detailed"></i></span>
                                <span>
                                    <strong>Sudah pernah mendaftar?</strong>
                                    Masukkan email Anda, tautan pendaftarannya kami kirim ke sana.
                                </span>
                            </div>

                            <div class="kmp-lacak-isian">
                                <label for="kmpLacakEmail" class="visually-hidden">Alamat email</label>
                                <input type="email" id="kmpLacakEmail" name="email" required
                                    autocomplete="email" placeholder="nama@email.com"
                                    value="{{ old('email') }}">
                                <button type="submit">
                                    <i class="bi bi-envelope"></i> Kirim tautannya
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            @endif
        </div>
    </section>

</div>

{{-- Pemberitahuan 'success' DULU digambar SweetAlert dengan teks tetap
     "Data Berhasil Terkirim! Mohon tunggu verifikasi 1x24 jam" — isi pesannya
     diabaikan seluruhnya.

     Kalimat itu sudah tidak benar sejak pembayarannya lewat gerbang: tidak ada
     yang diverifikasi 1x24 jam, dan yang baru saja meminta tautan
     pendaftarannya diberi tahu bahwa datanya terkirim. Diganti pita di kepala
     halaman yang mencetak pesannya sendiri. --}}
@stop
