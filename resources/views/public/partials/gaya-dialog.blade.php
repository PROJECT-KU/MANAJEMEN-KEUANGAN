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
