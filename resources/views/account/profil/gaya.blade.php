{{-- Gaya khusus halaman profil.

     Yang umum — kartu, lencana ikon, pil, kisi isian, tombol, isian, toast —
     datang dari .mis-* di public/assets/css/mis-ui.css, sama seperti layar
     lain. Di sini hanya yang memang milik halaman ini: kartu identitas,
     pemilih foto, strip tab, dan jendela kecilnya.

     @push('gaya') wajib: <style> di badan berkas terbit SEBELUM CSS
     Bootstrap, sehingga aturan berbobot sama selalu kalah. --}}
@push('gaya')
<style>
    .prof {
        display: grid;
        gap: var(--mis-jarak);
    }

    .prof *,
    .prof *::before,
    .prof *::after {
        box-sizing: border-box;
    }

    /*
     * Jarak antar kartu hanya dari gap kolomnya; margin bawaan .mis-kartu
     * dimatikan di sini. Tanpa ini gap 16px + margin 16px jadi 32px, dan
     * halamannya terasa renggang padahal tokennya sudah dirapatkan.
     */
    .prof > .mis-kepala,
    .prof-sisi > .mis-kartu,
    .prof-utama > .mis-kartu {
        margin-bottom: 0;
    }

    .prof-titik {
        color: #cbd5e1;
    }

    .prof-kepala-foto {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 14px;
        overflow: hidden;
        background: var(--mis-ungu);
        box-shadow: 0 12px 22px -14px #6366f1;
    }

    .prof-kepala-foto img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* -------------------------------------------------------- tata letak */

    /*
     * Kolom kiri lebarnya tetap supaya kartu identitas tidak melar di layar
     * lebar; kolom kanan minmax(0, 1fr) supaya tabel riwayat masuk yang
     * panjang tidak mendorong kisinya melebar.
     */
    .prof-tata {
        display: grid;
        grid-template-columns: minmax(0, 320px) minmax(0, 1fr);
        gap: var(--mis-jarak);
        /* stretch, bukan start: kalau satu kolom lebih pendek, sisanya jadi
           bagian dalam kartu — bukan petak kosong di sebelah kartu lain. */
        align-items: stretch;
    }

    .prof-sisi {
        display: grid;
        grid-auto-rows: max-content;
        gap: var(--mis-jarak);
        align-content: start;
        position: sticky;
        top: 88px;
    }

    .prof-utama {
        min-width: 0;
        display: flex;
    }

    .prof-utama > .mis-kartu {
        width: 100%;
        display: flex;
        flex-direction: column;
    }

    /* Isi tab memanjang mengisi tinggi kartunya. */
    .prof-tab-isi {
        flex: 1 1 auto;
    }

    .prof-penuh {
        width: 100%;
    }

    /* ---------------------------------------------------- kartu identitas */

    .prof-identitas {
        text-align: center;
        display: grid;
        justify-items: center;
    }

    .prof-foto-bingkai {
        position: relative;
        width: 92px;
        height: 92px;
        display: grid;
        place-items: center;
        border-radius: 28px;
        background: var(--mis-ungu);
        padding: 4px;
        box-shadow: 0 18px 32px -18px #6366f1;
    }

    .prof-foto {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 24px;
        border: 3px solid #fff;
        background: #fff;
        display: block;
    }

    /*
     * Lencana terverifikasi mengikuti centang WhatsApp: lingkaran penuh
     * berwarna hijau khas WhatsApp dengan centang putih di tengah, bukan
     * kotak membulat bergradien.
     */
    .prof-foto-lencana {
        position: absolute;
        right: -2px;
        bottom: -2px;
        display: grid;
        place-items: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: 2.5px solid #fff;
        color: #fff;
        font-size: .62rem;
        background: #25d366;
        box-shadow: 0 2px 6px rgba(15, 23, 42, .18);
    }

    /* Belum terverifikasi tetap kuning supaya bedanya langsung terlihat. */
    .prof-foto-lencana.belum {
        background: #f59e0b;
    }

    .prof-foto-lencana i {
        line-height: 1;
    }

    .prof-nama {
        margin: 12px 0 0;
        font-size: 1.08rem;
        font-weight: 800;
        color: var(--mis-tinta);
        letter-spacing: -.02em;
        line-height: 1.3;
    }

    .prof-username {
        margin: 2px 0 10px;
        font-size: .8rem;
        color: var(--mis-tinta-4);
        font-weight: 600;
    }

    .prof-pil-baris {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 7px;
        margin-bottom: 14px;
    }

    /* Tiga angka ringkas; kolomnya tetap supaya lebarnya rata. */
    .prof-mini-kisi {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 9px;
        width: 100%;
    }

    .prof-mini {
        display: grid;
        justify-items: center;
        gap: 5px;
        padding: 8px 6px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid var(--mis-garis);
    }

    .prof-mini-angka {
        margin: 0;
        font-size: .8rem;
        font-weight: 800;
        color: var(--mis-tinta);
        line-height: 1.25;
    }

    .prof-mini-label {
        margin: 0;
        font-size: .62rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--mis-tinta-4);
    }

    /* ----------------------------------------------------- pemilih foto */

    .prof-unggah-bungkus {
        width: 100%;
        display: grid;
        gap: 10px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px dashed var(--mis-garis);
    }

    /* Input berkas bawaan disembunyikan, tetapi tetap bisa dicapai Tab:
       labelnya yang terlihat dan ia mengaktifkan input lewat for=. */
    .prof-berkas {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .prof-unggah {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 10px 12px;
        border-radius: 13px;
        border: 1.5px dashed #c7d2fe;
        background: #f8faff;
        cursor: pointer;
        text-align: left;
        margin: 0;
        transition: border-color .25s ease, background .25s ease;
    }

    .prof-unggah:hover,
    .prof-berkas:focus-visible + .prof-unggah {
        border-color: #6366f1;
        background: #eef2ff;
    }

    .prof-unggah-teks {
        min-width: 0;
        flex: 1 1 auto;
    }

    .prof-unggah-nama {
        display: block;
        font-size: .78rem;
        font-weight: 700;
        color: var(--mis-tinta);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ------------------------------------------------ kelengkapan profil */

    .prof-lengkap-atas {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 12px;
    }

    .prof-lengkap-teks {
        min-width: 0;
    }

    /*
     * Cincin kemajuan dari conic-gradient: satu elemen, tanpa SVG dan tanpa
     * skrip. --nilai diisi dari PHP sebagai angka 0-100.
     */
    .prof-cincin {
        display: grid;
        place-items: center;
        width: 58px;
        height: 58px;
        flex: 0 0 58px;
        border-radius: 50%;
        background: conic-gradient(#6366f1 calc(var(--nilai, 0) * 1%), #e2e8f0 0);
    }

    .prof-cincin-isi {
        display: grid;
        place-items: center;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: #fff;
    }

    .prof-cincin-isi strong {
        font-size: .92rem;
        font-weight: 800;
        color: var(--mis-tinta);
        line-height: 1;
    }

    .prof-cincin-isi small {
        font-size: .6rem;
        font-weight: 700;
        color: var(--mis-tinta-4);
    }

    .prof-lengkap-daftar {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 6px;
    }

    /* Tiap butir sebuah tombol: menekannya membuka tab yang tepat dan
       menaruh kursor di kotaknya. */
    .prof-lengkap-butir {
        display: flex;
        align-items: center;
        gap: 14px;
        width: 100%;
        padding: 8px 10px;
        border: 1px solid var(--mis-garis);
        border-radius: 12px;
        background: #f8fafc;
        text-align: left;
        cursor: pointer;
        transition: transform .2s ease, border-color .2s ease, background .2s ease;
    }

    .prof-lengkap-butir:hover {
        border-color: #c7d2fe;
        background: #fff;
        transform: translateX(2px);
    }

    .prof-lengkap-butir-teks {
        min-width: 0;
        flex: 1 1 auto;
        display: grid;
    }

    .prof-lengkap-butir-judul {
        font-size: .82rem;
        font-weight: 700;
        color: var(--mis-tinta);
        line-height: 1.3;
    }

    .prof-lengkap-panah {
        font-size: 11px;
        color: #cbd5e1;
        flex: 0 0 auto;
    }

    .prof-lengkap-tuntas {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        padding: 10px 12px;
        border-radius: 13px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        font-size: .8rem;
        font-weight: 600;
        color: #047857;
    }

    /* Blok email di dalam kartu identitas, dipisah garis putus-putus. */
    .prof-email-blok {
        width: 100%;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px dashed var(--mis-garis);
        text-align: left;
    }

    .prof-email-label {
        margin-bottom: 8px;
    }

    /* ------------------------------------------------------ baris email */

    /*
     * Rata atas, bukan rata tengah.
     *
     * Isinya dua baris (alamat email lalu keterangan terverifikasi), jadi
     * ubin ikon yang rata tengah membuat alamat emailnya duduk 11px lebih
     * tinggi daripada ikonnya. Dengan rata atas, ikon, alamat, dan tombol
     * ubah semuanya sejajar di baris pertama.
     */
    .prof-baris {
        display: grid;
        gap: 5px;
        padding: 10px 12px;
        border-radius: 13px;
        background: #f8fafc;
        border: 1px solid var(--mis-garis);
    }

    /* Ikon, alamat, dan tombol ubah: satu baris, rata tengah satu sama lain. */
    .prof-baris-atas {
        display: flex;
        align-items: center;
        gap: 13px;
        min-width: 0;
    }

    .prof-baris-atas .prof-nilai {
        flex: 1 1 auto;
        min-width: 0;
    }

    /* Keterangan menjorok selebar ubin + jaraknya, jadi ia segaris dengan
       alamat email di atasnya, bukan dengan ikonnya. */
    .prof-baris-ket {
        /* selebar ubin (25px) + jarak (13px) */
        padding-left: 38px;
    }

    /* Tombol ubah di baris ini lebih kecil daripada tombol ikon di tabel,
       dan flex: 0 0 auto supaya tidak terjepit jadi 31x34 seperti sebelumnya. */
    .prof-baris .mis-tombol-garis {
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        border-radius: 9px;
        font-size: .68rem;
    }

    .prof-baris-teks {
        min-width: 0;
        flex: 1 1 auto;
    }

    .prof-nilai {
        margin: 0;
        font-size: .85rem;
        font-weight: 700;
        color: var(--mis-tinta);
        overflow-wrap: anywhere;
        line-height: 1.35;
    }

    .prof-verif {
        margin-top: 12px;
        display: grid;
        gap: 6px;
    }

    /* -------------------------------------------------------------- tab */

    .prof-tab-kartu {
        padding: 0;
        overflow: hidden;
    }

    .prof-tab-kepala {
        padding: 16px 18px 0;
    }

    .prof-tab {
        display: flex;
        gap: 6px;
        list-style: none;
        margin: 0 0 16px;
        padding: 5px;
        background: #f1f5f9;
        border: 1px solid var(--mis-garis);
        border-radius: 14px;
    }

    .prof-tab > li {
        flex: 1 1 0;
        min-width: 0;
    }

    /* Bootstrap memberi .nav-link warna & padding sendiri; ditimpa di sini
       supaya pil aktifnya memakai gradien yang sama dengan tombol dasbor. */
    .prof-tab .nav-link {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 9px 10px;
        border-radius: 11px;
        font-size: .8rem;
        font-weight: 700;
        color: var(--mis-tinta-3);
        background: transparent;
        white-space: nowrap;
        transition: all .25s ease;
    }

    .prof-tab .nav-link i {
        font-size: 13px;
    }

    .prof-tab .nav-link:hover {
        color: #4f46e5;
        background: #fff;
    }

    .prof-tab .nav-link.active {
        background: var(--mis-ungu);
        color: #fff;
        box-shadow: 0 10px 20px -12px #6366f1;
    }

    .prof-tab .nav-link.active i {
        color: #fff !important;
    }

    .prof-tab-isi {
        padding: 0 18px 18px;
    }

    /* Kisi kedua "Nama & kontak" hanya berisi dua isian; auto-fit meniadakan
       jalur yang tidak terpakai sehingga keduanya mengisi penuh barisnya. */
    .prof-kisi-dua {
        margin-top: 14px;
    }

    /* --------------------------------------------------- bagian isian */

    .prof-bagian + .prof-bagian {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px dashed var(--mis-garis);
    }

    .prof-bagian-kepala {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 14px;
    }

    .prof-bagian-judul {
        margin: 0;
        font-size: .92rem;
        font-weight: 800;
        color: var(--mis-tinta);
        letter-spacing: -.01em;
    }

    .prof-bagian-sub {
        margin: 2px 0 0;
        font-size: .76rem;
        color: var(--mis-tinta-3);
    }

    /* Nilai yang ditetapkan admin: kartu kecil, bukan <select disabled>
       yang menyamar jadi isian padahal tidak pernah ikut tersimpan. */
    /*
     * Kartu "Ditetapkan oleh admin": empat kartu di kisi tiga kolom
     * menyisakan dua sel kosong. Dengan lebar minimum 300px ia jatuh ke dua
     * kolom, jadi empat kartu mengisi 2x2 penuh.
     */
    .prof-kisi-admin {
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }

    /* Kalau jumlahnya ganjil (akun 'user' tidak punya kartu Perusahaan),
       kartu terakhir melebar mengisi sisa barisnya. */
    .prof-kisi-admin > :last-child:nth-child(2n + 1) {
        grid-column: span 2;
    }

    @media (max-width: 767.98px) {
        .prof-kisi-admin > :last-child:nth-child(2n + 1) {
            grid-column: auto;
        }
    }

    /* Kartu ini hanya menampilkan label + satu nilai, jadi tingginya cukup
       mengikuti isinya; padding 10/12 dengan ubin 38px membuatnya 80px,
       setinggi kotak isian yang bisa disunting di atasnya. */
    .prof-statis {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 10px;
        border-radius: 11px;
        background: #f8fafc;
        border: 1px solid var(--mis-garis);
    }

    .prof-statis-label {
        margin: 0 0 1px;
        line-height: 1.2;
        font-size: .62rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--mis-tinta-4);
    }

    .prof-statis-nilai {
        margin: 0;
        line-height: 1.3;
        font-size: .82rem;
        font-weight: 700;
        color: var(--mis-tinta);
        overflow-wrap: anywhere;
    }

    .prof-aksi {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px dashed var(--mis-garis);
    }

    .prof-aksi-catatan {
        margin: 0;
        font-size: .74rem;
        color: var(--mis-tinta-4);
        flex: 1 1 180px;
    }

    /*
     * Baris tombol turun ke dasar kartu.
     *
     * Di layar lebar kolom kanan jadi lebih pendek daripada kolom kiri, dan
     * sisanya muncul sebagai petak putih di bawah tombol. Dengan margin-top
     * auto, baris tombolnya menempel ke dasar kartu seperti kaki — ruang itu
     * jadi bagian dari tata letak, bukan lubang.
     */
    .prof-tab-isi .tab-content {
        height: 100%;
    }

    .prof-tab-isi .tab-pane.active {
        display: flex;
        flex-direction: column;
        min-height: 100%;
    }

    .prof-tab-isi .tab-pane.active > form {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
    }

    .prof-tab-isi .prof-aksi {
        margin-top: auto;
    }

    /* Bintang merah hanya untuk isian yang validatornya memang wajib. */
    .prof-wajib {
        color: #e11d48;
        font-weight: 800;
    }

    /* Pesan galat per isian. Sebelumnya halaman ini tidak punya satu pun,
       sehingga validasi yang menolak terasa seperti tombol yang rusak. */
    .prof-salah {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        margin: 0;
        font-size: .74rem;
        font-weight: 600;
        color: #e11d48;
    }

    .prof-salah i {
        margin-top: 2px;
        font-size: 10px;
    }

    .prof-kabar {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 14px;
        border-radius: 13px;
        margin-bottom: 16px;
        border: 1px solid #fde68a;
        background: #fffbeb;
    }

    .prof-kabar-teks {
        margin: 0;
        font-size: .82rem;
        font-weight: 600;
        color: #78350f;
        flex: 1 1 auto;
    }

    /* ------------------------------------------------------------ modal */

    .custom-popup {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(15, 23, 42, .55);
        backdrop-filter: blur(6px);
        padding: 16px;
        overflow-y: auto;
    }

    .custom-popup-content {
        position: relative;
        max-width: 440px;
        width: 100%;
        margin: 8vh auto;
        background: #fff;
        padding: 22px;
        border-radius: 18px;
        box-shadow: 0 30px 60px -20px rgba(15, 23, 42, .4);
        font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
    }

    .custom-popup-close {
        position: absolute;
        right: 16px;
        top: 12px;
        display: grid;
        place-items: center;
        width: 32px;
        height: 32px;
        border-radius: 11px;
        font-size: 20px;
        line-height: 1;
        color: var(--mis-tinta-4);
        cursor: pointer;
        transition: all .2s ease;
    }

    .custom-popup-close:hover {
        background: #f1f5f9;
        color: var(--mis-tinta);
    }

    .prof-modal-kepala {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 20px;
        padding-right: 34px;
    }

    .prof-modal-judul {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: var(--mis-tinta);
    }

    .prof-modal-sub {
        margin: 2px 0 0;
        font-size: .76rem;
        color: var(--mis-tinta-3);
    }

    .prof-rapat {
        margin-bottom: 16px;
    }

    /* Enam angka berjarak lebar supaya mudah dicocokkan dengan email. */
    .prof-kode {
        text-align: center;
        font-size: 1.25rem !important;
        font-weight: 800 !important;
        letter-spacing: .45em;
        text-indent: .45em;
        padding: 11px !important;
    }

    /* -------------------------------------------------------- kata sandi */

    .prof-sandi {
        position: relative;
    }

    .prof-sandi .form-control-modern {
        padding-right: 40px;
    }

    .password-toggle-inside {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: var(--mis-tinta-4);
        transition: color .2s ease;
        z-index: 3;
        font-size: 13px;
        padding: 5px;
    }

    .password-toggle-inside:hover {
        color: #6366f1;
    }

    /* Tab PIN & Keamanan memakai kelas Bootstrap dari komponen Livewire. */
    .prof-tab-isi .table {
        font-size: .82rem;
    }

    /* --------------------------------------------------------- responsif */

    /* Tablet: kartu identitas berhenti menempel supaya tidak memakan tinggi
       layar, dan dua kolom jadi satu. */
    @media (max-width: 1100px) {
        .prof-tata {
            grid-template-columns: minmax(0, 1fr);
        }

        .prof-sisi {
            position: static;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .prof,
        .prof-tata,
        .prof-sisi {
            gap: 16px;
        }

        .prof-tab-kepala {
            padding: 16px 16px 0;
        }

        .prof-tab-isi {
            padding: 0 16px 18px;
        }

        /* Empat tab tidak muat berjajar; jadikan satu baris yang bisa
           digeser, bukan empat baris bertumpuk. */
        .prof-tab {
            flex-wrap: nowrap;
            overflow-x: auto;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x proximity;
            border-radius: 16px;
        }

        .prof-tab::-webkit-scrollbar {
            display: none;
        }

        .prof-tab > li {
            flex: 0 0 auto;
            scroll-snap-align: start;
        }

        .prof-tab .nav-link {
            padding: 10px 14px;
            font-size: .78rem;
        }

        .prof-aksi .mis-tombol {
            width: 100%;
        }
    }

    @media (max-width: 400px) {
        .prof-mini-kisi {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        /* Kotak ketiga mengisi sisa baris supaya tidak ada ruang menganga. */
        .prof-mini-kisi > .prof-mini:last-child {
            grid-column: 1 / -1;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .prof * {
            transition: none !important;
        }
    }
</style>
@endpush
