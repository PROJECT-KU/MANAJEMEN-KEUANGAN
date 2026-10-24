{{--
  Rupa kotak dialog SweetAlert2 untuk seluruh halaman umum.

  Diminta pemiliknya 24 Okt 2026: dialognya masih berupa bawaan SweetAlert2 —
  huruf sistem, kotak putih bersudut kecil, tombol batal abu-abu, dan ikon
  biru keabuan. Di tengah halaman yang sudah memakai kertas krem, Fraunges,
  dan jingga, kotak itu terbaca seperti datang dari aplikasi lain.

  SATU BERKAS, disertakan tata letak umum — bukan disalin ke sepuluh halaman
  yang memakai Swal. Sepuluh salinan akan menyimpang, dan yang pertama
  menyimpang selalu halaman yang jarang dibuka.

  Yang diubah HANYA huruf, warna, sudut, dan ukuran tombol. Tidak ada
  display, position, atau lebar yang disentuh: SweetAlert2 menghitung
  posisinya sendiri, dan mengubahnya berarti memperbaiki rupa satu dialog
  sambil merusak letak sembilan lainnya.
--}}
<style>
    /*
     * Dua nama huruf yang sama dengan halaman Scopus Camp. Keduanya sudah
     * dimuat halaman yang memakainya; di halaman yang tidak memuatnya, nama
     * ini jatuh ke cadangan di belakangnya tanpa galat.
     */
    .swal2-popup {
        border-radius: 22px;
        padding: 30px 26px 24px;
        font-family: 'Figtree', system-ui, -apple-system, sans-serif;
        border: 1px solid #e7dcc6;
        box-shadow: 0 24px 60px rgba(22, 35, 63, .22);
    }

    .swal2-title {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 800;
        font-size: 1.5rem;
        line-height: 1.25;
        color: #16233f;
        padding: 0 8px;
    }

    .swal2-html-container {
        font-size: 1rem;
        line-height: 1.65;
        color: #5c6b85;
        margin-top: 10px;
    }

    /* ----------------------------------------------------------- ikon */
    /*
     * Ikonnya diwarnai ulang, bukan diganti gambar: SweetAlert2 menggambar
     * ikonnya dari border dan pseudo-element, jadi menukar gambarnya berarti
     * menulis ulang seluruh bentuknya.
     */
    .swal2-icon { border-width: 3px; }

    .swal2-icon.swal2-question {
        border-color: #f3c99a;
        color: #ad4d12;
    }
    .swal2-icon.swal2-warning {
        border-color: #f3c99a;
        color: #ad4d12;
    }
    .swal2-icon.swal2-success {
        border-color: #a7e3d8;
    }
    .swal2-icon.swal2-success [class^='swal2-success-line'] { background-color: #0f766e; }
    .swal2-icon.swal2-success .swal2-success-ring { border-color: rgba(15, 118, 110, .3); }

    .swal2-icon.swal2-error {
        border-color: #e3b7b1;
        color: #a3281c;
    }
    .swal2-icon.swal2-error [class^='swal2-x-mark-line'] { background-color: #a3281c; }

    /* --------------------------------------------------------- tombol */
    /*
     * 52px, dan itu bukan selera: patokan sasaran sentuh 44px, sementara
     * sasaran layanan ini orang berusia lanjut. Tombol bawaan SweetAlert2
     * setinggi 40px.
     */
    .swal2-actions { gap: 10px; margin-top: 22px; }

    .swal2-styled {
        min-height: 52px;
        padding: 0 24px;
        border-radius: 14px;
        font-family: 'Figtree', system-ui, sans-serif;
        font-size: 1rem;
        font-weight: 800;
        box-shadow: none;
    }

    /*
     * !important DIPERLUKAN di sini, dan hanya di sini.
     *
     * SweetAlert2 menyetel warna latar tombolnya lewat atribut style
     * SEBARIS — dari confirmButtonColor yang diberikan pemanggil — dan gaya
     * sebaris selalu menang atas berkas gaya mana pun. Tanpa !important,
     * tombolnya tetap berwarna apa pun yang kebetulan ditulis di sepuluh
     * pemanggil yang berbeda, dan seragamnya tidak pernah tercapai.
     */
    .swal2-styled.swal2-confirm {
        background: linear-gradient(135deg, #fb8c3d, #ad4d12) !important;
        color: #fff !important;
        box-shadow: 0 8px 20px rgba(231, 109, 32, .32) !important;
    }
    .swal2-styled.swal2-confirm:hover { filter: brightness(1.06); }

    /* Tombol batal BUKAN abu-abu: abu-abu terbaca sebagai tombol mati, dan
       yang menekannya justru orang yang mau kembali memeriksa isiannya. */
    .swal2-styled.swal2-cancel {
        background: #fff !important;
        color: #16233f !important;
        border: 2px solid #e7dcc6 !important;
    }
    .swal2-styled.swal2-cancel:hover {
        border-color: #e76d20 !important;
        color: #ad4d12 !important;
    }

    /* Cincin fokus jingga, bukan biru bawaan. */
    .swal2-styled:focus { box-shadow: 0 0 0 4px rgba(231, 109, 32, .25) !important; }

    /* ------------------------------------------------- kotak menunggu */
    /*
     * Dipakai dialog "sedang diproses", menggantikan Swal.showLoading().
     *
     * Pemutar bawaannya cincin kecil berputar — ia menyampaikan "sedang
     * sibuk" tetapi tidak menyampaikan BERAPA LAMA, dan di tengah kotak
     * seluas ini ia terbaca seperti halaman yang menggantung. Yang
     * menunggunya orang yang baru saja menekan tombol bayar lima juta
     * rupiah, dan di situ keraguan paling mahal.
     *
     * Rel dengan isian yang meluncur: geraknya terus-menerus dan arahnya
     * jelas, jadi terbaca "sedang berjalan", bukan "sedang macet". Tidak
     * berpura-pura tahu persennya — isiannya memang tidak pernah berhenti.
     */
    .dlg-tunggu { margin-top: 4px; }

    .dlg-rel {
        position: relative;
        height: 8px;
        border-radius: 999px;
        background: #f2e7d2;
        overflow: hidden;
    }
    .dlg-rel::after {
        content: '';
        position: absolute;
        top: 0; bottom: 0; left: 0;
        width: 42%;
        border-radius: 999px;
        background: linear-gradient(90deg, #fb8c3d, #ad4d12);
        animation: dlg-luncur 1.25s cubic-bezier(.45, 0, .55, 1) infinite;
    }
    @keyframes dlg-luncur {
        /* -45% sampai 103%: isiannya keluar penuh di kedua ujung, jadi tidak
           ada jeda saat ia berbalik — jeda terbaca sebagai berhenti. */
        from { left: -45%; }
        to   { left: 103%; }
    }

    .dlg-langkah {
        margin-top: 14px;
        font-size: .9rem;
        line-height: 1.6;
        color: #5c6b85;
    }
    .dlg-langkah b { color: #16233f; }

    @media (prefers-reduced-motion: reduce) {
        /*
         * Isiannya DIAM di tengah, bukan dihilangkan: rel kosong terbaca
         * sebagai bar yang gagal dimuat, dan yang melihatnya justru orang
         * yang paling tidak menyangka halamannya sedang beranimasi.
         */
        .dlg-rel::after { animation: none; left: 29%; }
    }

    /* --------------------------------------------------------- lainnya */
    .swal2-container { backdrop-filter: blur(2px); }
    .swal2-timer-progress-bar { background: #e76d20; }
    .swal2-loader { border-color: #e76d20 transparent #e76d20 transparent; }

    @media (max-width: 575.98px) {
        .swal2-popup { padding: 26px 18px 20px; border-radius: 18px; }
        .swal2-title { font-size: 1.3rem; }
        /* Bertumpuk di layar sempit: dua tombol setinggi 52px berdampingan
           menyisakan masing-masing selebar 120px, dan kalimat "Ya, lanjut
           bayar" pecah jadi dua baris di dalamnya. */
        .swal2-actions { flex-direction: column-reverse; width: 100%; }
        .swal2-actions .swal2-styled { width: 100%; margin: 0; }
    }
</style>
