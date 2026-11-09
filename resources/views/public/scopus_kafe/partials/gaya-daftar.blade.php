{{--
  Gaya halaman daftar Scopus Kafe.

  Bahasa rupanya sama dengan Scopus Camp — kertas, tinta, jingga, Fraunces +
  Figtree — tetapi SUSUNANNYA sengaja berbeda, dan itu keputusan bukan
  kelalaian.

  Scopus Camp memajang puluhan angkatan: ia butuh lajur kiri untuk keterangan
  dan lajur kanan yang panjang untuk daftarnya. Scopus Kafe cuma punya dua
  sesi tetap. Susunan yang sama di sini meninggalkan separuh layar kosong —
  terukur pada bentuk pertamanya: judul jatuh ke empat baris di lajur sempit
  sementara setengah kanan tidak dipakai apa pun.

  Jadi yang dipakai di sini TERPUSAT: judul di tengah, dua kartu sesi
  berdampingan, empat ubin "yang kamu dapat" sejajar, dan satu pita jalan
  kembali di kaki.

  Gradien merah-jingga #ff3131 → #ff914d dari bentuk lamanya dibuang: palet
  yang sudah ditinggalkan beranda dan blog justru karena pemiliknya
  menyebutnya terlalu mirip situs lain.
--}}
<style>
    .kfd {
        --kertas: #fdf5e8;  --kertas-2: #f8ead4;
        --tinta: #16233f;   --tinta-2: #5c6b85;
        --jingga: #e76d20;  --jingga-muda: #fb8c3d; --jingga-tua: #ad4d12;
        --rumput: #0f766e;
        --garis: #e7dcc6;

        background: var(--kertas); color: var(--tinta);
        font-family: 'Figtree', system-ui, sans-serif; overflow-x: clip;
    }
    .kfd h1, .kfd h2, .kfd h3 {
        font-family: 'Fraunces', Georgia, serif; font-weight: 800;
        color: var(--tinta); margin: 0; letter-spacing: -.01em;
    }
    .kfd p { margin: 0; color: var(--tinta-2); line-height: 1.7; }

    .kfd-kabar {
        display: flex; gap: 12px; align-items: flex-start;
        padding: 14px 18px; margin: 0 0 20px; border-radius: 14px;
        font-size: 15px; line-height: 1.6; border: 1px solid;
    }
    .kfd-kabar i { flex: 0 0 auto; margin-top: 3px; font-size: 18px; }
    .kfd-kabar.sukses { background: #eafaf6; border-color: #a7e3d6; color: #0b5a52; }
    .kfd-kabar.galat  { background: #fdecea; border-color: #f0bdb6; color: #8e2318; }

    /* ========================================================== kepala */
    /* 104px di atas, sama dengan Scopus Camp: kepala situs berposisi fixed
       setinggi 82px dan tidak memesan ruang untuk dirinya sendiri, jadi
       yang benar-benar perlu 82px ditambah sedikit napas. Sisanya jurang
       kosong di layar pertama. */
        /* Jarak bawah 34px -> 26px. Kepala ini tidak lagi diakhiri pita
       "kapan" yang perlu bernapas; yang menyusulnya langsung kartu sesi,
       sama seperti Scopus Camp. */
    .kfd-kepala { padding: 104px 0 26px; text-align: center; }

    .kfd-stiker {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 7px 16px; border-radius: 999px; margin-bottom: 20px;
        background: var(--kertas-2); border: 1px solid var(--garis);
        font-size: .85rem; font-weight: 800; color: var(--jingga-tua);
    }

    /* max-width + margin auto, BUKAN hanya text-align: judul yang dipusatkan
       tanpa dibatasi lebarnya tetap merentang selebar layar dan barisnya jadi
       terlalu panjang untuk dibaca sekali tatap. */
    /* BOBOTNYA .kfd .kfd-judul, bukan .kfd-judul saja.

       Aturan reset di atas — `.kfd h1, .kfd h2, .kfd h3 { margin: 0 }` —
       berbobot (0,1,1) karena memuat kelas DAN unsur. `.kfd-judul` cuma
       (0,1,0), jadi `margin: 0 auto`-nya KALAH dan judulnya tidak pernah
       terpusat.

       Terukur: judulnya duduk 345 px di kiri titik tengah layar, dengan
       text-align: center yang memang berlaku — jadi barisnya terpusat satu
       sama lain tetapi seluruh bloknya melenceng. Cacat yang tidak kelihatan
       dari kodenya, cuma dari potretnya. */
    .kfd .kfd-judul {
        font-size: clamp(1.9rem, 4.6vw, 3rem); line-height: 1.18;

        /* 18ch -> 22ch. Judul halaman ini LEBIH PANJANG daripada milik
           Scopus Camp ("Datang dengan naskahmu, pulang dengan jalan
           keluarnya" versus "Datang bawa data, pulang naskahmu sudah
           terkirim"), jadi lebar yang sama membuatnya patah TIGA baris
           sementara Camp dua — terukur 60px lebih tinggi untuk kalimat
           yang sama pentingnya.

           Dibatasi ch, bukan piksel: satuannya ikut ukuran huruf, jadi
           patahannya tetap dua baris saat clamp() mengecilkan judulnya di
           layar sempit. */
        max-width: 22ch; margin: 0 auto;
    }

    /* Stabilo digambar di BELAKANG hurufnya lewat gradien.

       padding-bottom pada judulnya: tanpa itu garis stabilo baris terakhir
       bersentuhan dengan paragraf di bawahnya — terukur pada bentuk
       pertamanya. */
    .kfd-stabilo { background: linear-gradient(180deg, transparent 62%, #fbd9b4 62%); padding: 0 .1em; }
    .kfd .kfd-judul { padding-bottom: .12em; }

    .kfd .kfd-sub { font-size: 1.05rem; max-width: 62ch; margin: 18px auto 0; }

    /* Hari dan tanggal terdekat DILIPAT ke kalimat ini (lihat markahnya),
       jadi keduanya perlu menonjol tanpa jadi kotak sendiri. */
    .kfd .kfd-sub strong { color: var(--tinta); font-weight: 800; }

    /* Gaya pita "kapan" (.kfd-kapan*) DIBUANG bersama markahnya 9 Nov
       2026 — keterangannya kini dilipat ke kalimat pembuka. Begitu juga
       .kfd-renggang dan .kfd-bagian-kata, yang ikut hilang bersama judul
       bagian "Pilih bentuk dan sesinya".

       Dibuang, bukan ditinggalkan: gaya yatim adalah gaya yang kelak
       disalin orang ke tempat yang salah, dan nama seperti "renggang"
       terdengar berlaku umum padahal ia dibuat untuk satu judul tertentu. */

    /* ========================================================= bagian */
    .kfd-bagian { padding: 8px 0 54px; text-align: center; }
    .kfd-bagian-judul { font-size: clamp(1.4rem, 3vw, 1.9rem); }

    /* ==================================================== blok varian */
    /* Satu blok per bentuk layanan. Tanpa pemisah ini dua kelompok sesi
       yang harganya berbeda terbaca sebagai satu deret kartu yang sama. */
    /* padding: 0 DINYATAKAN, bukan diandaikan.

       Blok ini sebuah <section>, dan templat situs memberi `section`
       padding 60px atas-bawah. Terukur: tiap blok varian 360px padahal
       isinya cuma 240px — 120px sisanya padding warisan itu, dan dengan
       dua varian ia menyumbang 240px ke panjang halaman tanpa menampilkan
       apa pun. Tidak kelihatan dari gaya berkas ini sama sekali; yang
       menunjukkannya cuma computed style. */
        /* 30px -> 22px: dua varian berarti jarak ini dibayar dua kali, dan
       kartunya sendiri sudah berjarak. */
    .kfd-varian { margin-top: 22px; padding: 0; }
    .kfd .kfd-varian-kepala { max-width: 860px; margin: 0 auto; }
    .kfd .kfd-varian-nama {
        display: inline-flex; align-items: center; gap: 10px;
        font-size: 1.15rem; margin: 0;
    }
    .kfd .kfd-varian-nama i { color: var(--jingga); }
        /* SEBARIS dengan nama variannya, bukan paragraf di bawahnya. Pemisah
       titik di depannya hanya muncul kalau keduanya memang sebaris —
       di layar sempit ia turun dan pemisahnya disembunyikan. */
    .kfd .kfd-varian-ket {
        margin: 0; font-size: .95rem; color: var(--tinta-2); font-weight: 500;
    }
    @media (min-width: 620px) {
        .kfd .kfd-varian-ket::before {
            content: "·"; margin: 0 10px; color: var(--garis); font-weight: 700;
        }
    }
    @media (max-width: 619.98px) {
        .kfd .kfd-varian-nama { display: block; }
        .kfd .kfd-varian-ket { display: block; margin-top: 5px; }
    }
    .kfd .kfd-varian-ket strong { color: var(--tinta); }

    /* ====================================================== kartu sesi */
    .kfd-varian .kfd-sesi { margin-top: 18px; }
    .kfd-sesi {
        display: grid; gap: 20px; margin: 30px auto 0; max-width: 860px;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr));
    }

    .kfd-kartu {
        /* MENDATAR DAN PADAT, bukan lajur tegak terpusat.

           Terukur: bentuk tegaknya setinggi 336px per kartu, dan dengan dua
           varian halaman ini jadi 3,48 layar — sementara kartu angkatan
           Scopus Camp yang isinya sejenis cuma 160px. Yang memakan tinggi
           bukan isinya melainkan susunannya: jam, nama, dua ciri, dan harga
           masing-masing menempati barisnya sendiri di tengah.

           Mendatar, jam duduk di samping nama dan cirinya, lalu harganya di
           kaki kartu. Isi yang sama, tinggi sepertiganya. */
        display: grid;
        grid-template-columns: 62px minmax(0, 1fr);
        grid-template-areas: 'jam nama' 'jam ciri' 'kaki kaki';
        gap: 4px 14px; align-items: start;
        padding: 16px 18px; text-align: left;
        background: #fff; border: 2px solid var(--tinta); border-radius: 20px;
        box-shadow: 6px 6px 0 rgba(22, 35, 63, .12);
    }

    /* TIDAK ADA efek pointer pada kartunya, dan itu disengaja.

       Kartu sesi di sini BUKAN tautan — yang bisa diklik cuma tombol "Pesan
       sekarang" di bawahnya. Kartu yang terangkat saat disentuh pointer
       adalah janji "ini bisa diklik" yang dilanggar sedetik kemudian. Di
       Scopus Camp kartunya memang tautan, jadi di sana janji itu ditepati. */

    .kfd-jam { grid-area: jam; }
    .kfd-nama { grid-area: nama; }
    .kfd-ciri { grid-area: ciri; }
    .kfd-harga { grid-area: kaki; }

    .kfd-jam {
        display: grid; place-items: center; gap: 1px;
        width: 62px; padding: 8px 4px; border-radius: 14px;
        background: linear-gradient(135deg, var(--jingga-muda), var(--jingga-tua));
        color: #fff;
    }
    .kfd-jam-angka { font-family: 'Fraunces', Georgia, serif; font-size: 1.05rem; font-weight: 800; }
    .kfd-jam-label { font-size: .6rem; letter-spacing: .08em; text-transform: uppercase; opacity: .92; }

    .kfd-nama { font-size: 1.1rem; }

    .kfd-ciri { list-style: none; padding: 0; margin: 0; display: grid; gap: 3px; }
    /* Rata kiri, bukan terpusat: kartunya tidak lagi lajur tegak di tengah,
       dan teks terpusat di sebelah kanan kotak jam terbaca seperti melayang
       lepas dari namanya. */
    .kfd-ciri li {
        display: flex; align-items: center; gap: 8px;
        font-size: .88rem; color: var(--tinta-2); line-height: 1.5;
    }
    .kfd-ciri i { color: var(--jingga); }
    .kfd-ciri-ket { opacity: .75; }

    /* Harga dan satuannya SEBARIS di kaki kartu: ditumpuk, keduanya
       menambah satu baris lagi ke kartu yang justru sedang dipendekkan. */
    .kfd-harga {
        margin-top: 8px; padding-top: 10px; width: 100%;
        display: flex; align-items: baseline; gap: 8px;
        border-top: 1px dashed var(--garis);
    }
    .kfd-harga-angka {
        font-family: 'Fraunces', Georgia, serif;
        font-size: 1.2rem; font-weight: 800; color: var(--rumput);
    }
    .kfd-harga-ket { margin-left: auto; font-size: .8rem; color: var(--tinta-2); }

    /* ========================================================== ajakan */
        /* 34px -> 26px. Scopus Camp tidak punya tombol melayang seperti ini
       sama sekali — tindakannya ada di kaki tiap kartu — jadi yang bisa
       dilakukan di sini cuma tidak memberinya ruang berlebih. */
    .kfd-ajak { margin-top: 26px; }
    .kfd-tombol {
        display: inline-flex; align-items: center; justify-content: center; gap: 10px;
        padding: 15px 34px;
        background: var(--jingga); color: #fff;
        border: 2px solid var(--tinta); border-radius: 999px;
        box-shadow: 5px 5px 0 var(--tinta);
        font-family: 'Fraunces', Georgia, serif; font-size: 1.05rem; font-weight: 800;
        transition: transform .15s ease;
    }
    .kfd-tombol:hover {
        color: #fff; background: var(--tinta);
        translate: 0 -2px; box-shadow: 0 14px 30px rgba(22, 35, 63, .22);
    }
    .kfd-tombol:active { translate: 0 0; box-shadow: 0 6px 14px rgba(22, 35, 63, .2); }
    .kfd-tombol.mati { background: var(--tinta-2); box-shadow: none; cursor: not-allowed; }
    .kfd .kfd-ajak-kata { margin-top: 12px; font-size: .92rem; max-width: 46ch; margin-left: auto; margin-right: auto; }

    /* =========================================================== ubin */
    /* Jumlah lajurnya DIPATOK per lebar layar, bukan lewat auto-fit.

       `repeat(auto-fit, minmax(min(100%, 220px), 1fr))` benar selama blok
       ini anak langsung .container. Begitu ia jadi SEL KISI di .kfd-bawah,
       `100%` tidak lagi mengacu ke lebar yang sama: terukur di layar 390px,
       ubinnya 581px di dalam induk 366px — dua ubin selebar 281px, dan yang
       kanan terpotong di luar layar.

       Terpotongnya TIDAK terlihat dan TIDAK menggulir ke samping, sebab
       `.kfd { overflow-x: clip }` memotongnya diam-diam. Cacat yang cuma
       kelihatan dari angka, bukan dari tampilannya. */
    /* ============================================ yang kamu dapat */
    /* SATU KARTU berisi daftar centang — bentuk yang sama dengan panel
       fasilitas Scopus Camp, yang halaman ini diminta mengikutinya.

       Ubin berikon yang berdiri di sini sebelumnya terukur 422px (plus
       80px judul bagiannya) untuk empat kalimat pendek; bentuk Camp
       menyampaikan tujuh butir setara dalam 211px. Kelas .kfd-ubin*
       dibuang bersamanya — tidak ada lagi yang memakainya, dan gaya yatim
       adalah gaya yang kelak disalin orang ke tempat yang salah. */
    .kfd-panel-kartu {
        height: 100%; padding: 24px 26px; text-align: left;
        background: #fff; border: 1px solid var(--garis); border-radius: 22px;
        box-shadow: 0 8px 22px rgba(22, 35, 63, .06);

        /* `translate`, bukan `transform`: AOS memasang transform-nya
           sendiri pada unsur yang sama dan menang bobot, sehingga
           geseran hover tidak pernah terlihat. Sama persis dengan yang
           sudah terukur di Scopus Camp. */
        transition: translate .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .kfd-panel-kartu:hover {
        translate: 0 -3px;
        border-color: #efd7b4;
        box-shadow: 0 16px 32px rgba(22, 35, 63, .10);
    }
    .kfd .kfd-panel-kartu h2 { font-size: 1.12rem; margin-bottom: 12px; }

    .kfd-fasilitas {
        list-style: none; padding: 0; margin: 0;
        display: grid; gap: 8px 20px;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 330px), 1fr));
    }
    .kfd-fasilitas li {
        display: flex; gap: 9px; align-items: flex-start; padding: 2px 0;
        color: var(--tinta); font-size: .92rem; line-height: 1.5;
    }

    /* `height: 1.5em` menyamakan tinggi ikon dengan tinggi baris teksnya,
       jadi centangnya sejajar dengan huruf pertama — bukan dengan puncak
       kotak ikonnya. Pada butir yang patah dua baris, bedanya terlihat. */
    .kfd-fasilitas i {
        flex: 0 0 auto; color: var(--rumput);
        display: inline-flex; align-items: center; justify-content: center;
        height: 1.5em;
    }

    /* ================================================== jalan kembali */
    /* Dua blok penutup sejajar.

       Lebar kartu lacak DIPATOK 420-500px, bukan dibagi rasio: dibagi
       rasio, lebarnya ikut lebar layar dan kotak emailnya menyempit sampai
       tidak muat satu alamat utuh — angka itu sudah terukur saat Scopus
       Camp disusun begini. Di bawah 1200px keduanya menumpuk. */
    .kfd-bawah { display: grid; gap: 16px; }
    @media (min-width: 1200px) {
        .kfd-bawah { grid-template-columns: minmax(0, 1fr) minmax(420px, 500px); }
        .kfd-bawah .kfd-lacak { margin-top: 0; align-content: center; }
    }
    .kfd-bawah > * { min-width: 0; }

    /* RATA KIRI, bukan terpusat — bentuk yang sama dengan Scopus Camp.

       Terpusat, ikon 48px berdiri sendiri di satu baris, judul di baris
       kedua, keterangan di ketiga, lalu isiannya: lima tingkat untuk satu
       kotak email. Camp menyusunnya mendatar — ikon di kiri teks — dan
       kartunya lebih pendek tanpa kehilangan satu kata pun.

       `justify-items` dilepas, bukan diganti `start`: nilai bawaannya
       `stretch`, dan itu yang membuat kotak isiannya memenuhi lebar
       kartu seperti di Camp. */
    .kfd-lacak {
        display: grid; gap: 12px; align-items: center;
        margin: 72px auto 0; max-width: 560px; padding: 20px 22px;
        background: #fff; border: 1px solid #efe3cd; border-radius: 22px;
        box-shadow: 0 10px 28px rgba(22, 35, 63, .06);
        text-align: left;
    }

    /* Ikon dan kata SEBARIS. Di lajur kanan yang sempit (<420px isinya)
       keduanya kembali bertumpuk, sebab memaksanya mendatar di situ
       menyisakan beberapa puluh piksel untuk kalimatnya. */
    @media (min-width: 420px) {
        .kfd-lacak-sapa { display: flex; gap: 14px; align-items: flex-start; }
    }
    .kfd-lacak-ikon {
        display: grid; place-items: center; width: 48px; height: 48px;
        border-radius: 15px; font-size: 1.25rem;
        background: linear-gradient(135deg, #fde8d3, #fbd9b4); color: var(--jingga-tua);
    }
    .kfd-lacak-kata strong { display: block; font-size: 1.08rem; color: var(--tinta); margin-bottom: 4px; }
    .kfd-lacak-kata span { font-size: .93rem; color: var(--tinta-2); line-height: 1.6; }

    /* Kotak dan tombolnya TIDAK pernah terpisah baris: kotak isian yang
       tombolnya terlempar ke baris lain terbaca seperti dua hal yang tidak
       berhubungan. */
    .kfd-lacak-isian { display: flex; gap: 10px; width: 100%; flex-wrap: wrap; }
    .kfd-lacak-isian input {
        flex: 1 1 200px; min-width: 0; padding: 12px 15px; font-size: 15px;
        border: 1px solid #ddd0b8; border-radius: 12px; background: #fdfcf9; color: var(--tinta);
    }
    .kfd-lacak-isian input:focus { outline: 2px solid var(--jingga); outline-offset: 1px; background: #fff; }
    .kfd-lacak-isian button {
        flex: 0 0 auto; display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 20px; font-size: 15px; font-weight: 700; white-space: nowrap;
        border: 0; border-radius: 12px; cursor: pointer; background: var(--tinta); color: #fff;
    }
    .kfd-lacak-isian button:hover {
        background: #23365c; translate: 0 -2px; box-shadow: 0 8px 18px rgba(22, 35, 63, .24);
    }
    .kfd-lacak-isian button:active { translate: 0 0; box-shadow: none; }

    .kfd-kosong {
        padding: 56px 24px; margin-top: 24px;
        background: #fff; border: 2px dashed var(--garis); border-radius: 22px;
    }
    .kfd-kosong i { font-size: 2.4rem; color: var(--tinta-2); }
    .kfd-kosong h2 { margin: 14px 0 9px; font-size: 1.3rem; }
    .kfd .kfd-kosong p { max-width: 44ch; margin: 0 auto; }

    @media (max-width: 600px) {
        .kfd-kepala { padding: 104px 0 32px; }
        .kfd-lacak { padding: 22px 18px; }
    }

    /* Yang minta gerak dikurangi tidak cukup dimatikan TRANSISINYA saja:
       tanpa baris transform/translate di bawah, tombolnya tetap melompat —
       cuma seketika alih-alih mulus, dan bagi yang memasang setelan itu
       karena vertigo perpindahan mendadak justru lebih buruk. */
    @media (prefers-reduced-motion: reduce) {
        .kfd *, .kfd *::before, .kfd *::after {
            transition: none !important;
            animation: none !important;
        }
        .kfd .kfd-tombol:hover,
        .kfd .kfd-tombol:active,
        .kfd .kfd-lacak-isian button:hover,
        .kfd .kfd-lacak-isian button:active {
            translate: none !important; transform: none !important;
        }
    }
</style>
