{{--
  Gaya halaman status Scopus Kafe.

  DISALIN dari halaman status Scopus Camp, dan disalin dengan sadar: satu
  orang bisa memakai dua layanan, dan halaman hasil yang berbeda bentuk
  membuatnya mengira salah satunya bukan milik Rumah Scopus.

  Disalin, bukan dipakai bersama lewat satu berkas: kedua halaman memang
  boleh berbeda isi — Scopus Camp punya daftar peserta rombongan dan tautan
  grup, Scopus Kafe punya tiga sesi dan jamnya — dan satu berkas gaya yang
  melayani keduanya akan penuh pengecualian. Yang dijaga kesamaan BAHASA
  RUPANYA (kertas, tinta, jingga, Fraunces + Figtree), bukan kesamaan baris.
--}}
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

    /* Pembayaran gagal BUKAN merah seperti pembatalan: kursinya masih
       ditahan dan tagihannya bisa dibuat ulang. Merah menyampaikan "sudah
       habis", dan itu bukan yang terjadi. */
    .sts-kotak.gagal::before  { background: linear-gradient(90deg, #b45309, #f59e0b); }

    /* ============================================== kabar kilat (flash)

       DI ATAS kotak statusnya, bukan di dalamnya: isinya kabar tentang
       TINDAKAN yang baru saja dikerjakan ("suratnya dikirim ulang",
       "tagihannya tidak bisa dibuat"), bukan tentang keadaan pendaftarannya.
       Di dalam kotak, keduanya terbaca sebagai satu hal. */
    .sts-kabar {
        display: flex; gap: 12px; align-items: flex-start;
        padding: 14px 18px; margin: 0 0 18px; border-radius: 16px;
        font-size: 15px; line-height: 1.6; border: 1px solid;
    }
    .sts-kabar i { flex: 0 0 auto; margin-top: 3px; font-size: 18px; }
    .sts-kabar.sukses { background: #eafaf6; border-color: #a7e3d6; color: #0b5a52; }
    .sts-kabar.galat  { background: #fdecea; border-color: #f0bdb6; color: #8e2318; }

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
    .sts-meterai.gagal  { background: #fef3c7; color: #b45309; }

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
    /* JARAKNYA DI WADAHNYA, bukan di tiap tombol.

       Sebelumnya hanya tombol sekunder yang punya margin-top, sementara
       .sts-tombol (yang bergradien, termasuk .hijau) tidak punya sama sekali.
       Selama hanya satu tombol bergradien yang tampil, itu tidak terlihat —
       dan memang begitu dulu: tombol bayar hanya muncul saat belum lunas,
       tombol grup hanya saat sudah lunas.

       Begitu "Unduh kwitansi" ditambahkan di antara keduanya, jaraknya jadi
       NOL dan kedua tombol itu menempel. Terukur lewat getBoundingClientRect:
       0 px antara "Unduh kwitansi (PDF)" dan "Masuk grup peserta".

       Dengan gap di wadahnya, tiap gabungan tombol — dan gabungan baru mana
       pun nanti — berjarak sama tanpa perlu ada yang mengingat memasang
       margin di kelas yang benar. */
    .sts-aksi {
        display: flex; flex-direction: column; gap: 12px;
        padding: 0 24px 30px;
    }

    /* Pembungkus <form> ikut jadi anak flex. Tanpa ini ia menyusut mengikuti
       isinya di sebagian peramban, dan tombolnya jadi lebih sempit daripada
       tautan di atas-bawahnya. */
    .sts-aksi > form { display: block; width: 100%; margin: 0; }

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

    /* ================================== tindakan pendukung, satu baris

       auto-fit dengan minmax: ketiganya berbagi satu baris selama muat, dan
       turun sendiri jadi dua lajur lalu satu lajur begitu kartunya menyempit.
       Tanpa titik putus yang ditulis tangan — lebar kartunya sendiri yang
       menentukan, bukan lebar layar, dan keduanya tidak selalu sama. */
    .sts-aksi-kecil {
        display: grid; gap: 10px;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    }

    /* <form> pembungkus tombol kirim ulang ikut jadi sel kisi. Tanpa ini ia
       menyusut mengikuti isinya dan tombolnya lebih sempit daripada
       tetangganya di baris yang sama. */
    .sts-aksi-kecil > form { display: block; width: 100%; margin: 0; }

    .sts-tombol-kecil {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; min-height: 46px; padding: 8px 12px;
        border: 2px solid var(--garis); border-radius: 13px;
        background: #fff; color: var(--tinta);
        font-weight: 800; font-size: .92rem; line-height: 1.25;
        text-align: center; cursor: pointer;
        transition: border-color .18s ease, color .18s ease;
    }
    .sts-tombol-kecil:hover { border-color: var(--jingga); color: var(--jingga-tua); }
    .sts-tombol-kecil i { font-size: 1rem; flex: 0 0 auto; }

    /* Tulisannya MEMBUNGKUS, tidak dipotong.

       Versi pertama memotongnya dengan ellipsis supaya satu label panjang
       tidak melebarkan selnya. Terukur di lebar 390 px — lebar ponsel yang
       paling umum — "Kirim ulang ke email" keluar terpotong: label yang
       terpotong adalah label yang berbohong, dan yang membacanya tidak tahu
       tombol itu mengirim apa ke mana.

       Melebarkan selnya tidak jadi soal: lajurnya sudah 1fr yang sama rata,
       dan min-width 0 yang menahan isinya meluber. Sel yang tingginya
       bertambah diikuti dua tetangganya oleh kisi, jadi barisnya tetap rata. */
    .sts-tombol-kecil span { min-width: 0; }

    /* Margin NEGATIF, dan itu disengaja.

       Catatan ini menjelaskan tombol DI ATASNYA, jadi ia harus lebih dekat
       ke sana daripada ke tombol berikutnya — kedekatan itu yang
       memberitahu mata keduanya satu kelompok.

       Terukur sesudah gap wadahnya dipasang: 16 px di atas catatan dan 12 px
       di bawahnya, yaitu kebalikannya — catatannya terbaca sebagai milik
       tombol yang justru tidak ia jelaskan. Dengan -4 px jaraknya jadi 8 px
       di atas dan 12 px di bawah. */
    .sts-catatan {
        display: flex; align-items: flex-start; gap: 10px; margin-top: -4px;
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
