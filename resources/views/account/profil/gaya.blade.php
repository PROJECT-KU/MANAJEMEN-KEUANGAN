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

    /* ---------------------------------------------------- lencana status */

    .prof-lencana-deret {
        gap: 8px;
    }

    /*
     * Lencana, bukan sekadar pil datar: latar bergradien tipis, tepi setipis
     * rambut dengan warna yang sama, dan bayangan pendek supaya ia terasa
     * timbul sedikit dari kartunya.
     */
    .prof-lencana {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 5px 13px 5px 10px;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 800;
        letter-spacing: .01em;
        white-space: nowrap;
        color: var(--tinta-lencana);
        background: linear-gradient(135deg,
            color-mix(in srgb, var(--warna-lencana) 16%, #fff),
            color-mix(in srgb, var(--warna-lencana) 7%, #fff));
        border: 1px solid color-mix(in srgb, var(--warna-lencana) 28%, #fff);
        box-shadow: 0 2px 6px -3px color-mix(in srgb, var(--warna-lencana) 55%, transparent);
    }

    .prof-lencana-hijau { --warna-lencana: #10b981; --tinta-lencana: #047857; }
    .prof-lencana-biru { --warna-lencana: #0ea5e9; --tinta-lencana: #0369a1; }
    .prof-lencana-kuning { --warna-lencana: #f59e0b; --tinta-lencana: #92400e; }
    .prof-lencana-merah { --warna-lencana: #f43f5e; --tinta-lencana: #be123c; }

    .prof-lencana-titik {
        position: relative;
        flex: 0 0 8px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--warna-lencana);
    }

    /* Denyutnya sebuah cincin yang melebar lalu memudar — titik intinya
       tetap utuh, jadi lencananya tidak ikut berkedip. */
    .prof-lencana-titik.berdenyut::after {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: var(--warna-lencana);
        animation: prof-denyut 2s cubic-bezier(.22, .61, .36, 1) infinite;
    }

    @keyframes prof-denyut {
        0% { transform: scale(1); opacity: .6; }
        70% { transform: scale(2.6); opacity: 0; }
        100% { transform: scale(2.6); opacity: 0; }
    }

    /* Denyut dimatikan untuk yang memilih gerak minimal; lencananya tetap
       terbaca karena warnanya yang membedakan, bukan geraknya. */
    @media (prefers-reduced-motion: reduce) {
        .prof-lencana-titik.berdenyut::after {
            animation: none;
        }
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
        /*
         * start, bukan stretch.
         *
         * Dengan stretch, kartu kanan ikut setinggi kolom kiri. Di tab yang
         * isinya pendek (Kata sandi, PIN, Keamanan) sisanya jadi petak putih
         * ratusan piksel DI DALAM kartu — lebih mengganggu daripada kartu
         * yang memang berhenti di ujung isinya.
         */
        align-items: start;
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
     * Lencana terverifikasi: tepi bergerigi seperti tanda terverifikasi di
     * Instagram, digambar sebagai path SVG 12 tonjolan. Lingkaran CSS tidak
     * bisa bergelombang seperti ini.
     *
     * Warnanya tetap hijau, bukan biru seperti Instagram, supaya sewarna
     * dengan centang "Sudah diverifikasi" tepat di bawahnya.
     */
    .prof-foto-lencana {
        position: absolute;
        right: -5px;
        bottom: -5px;
        display: block;
        width: 30px;
        height: 30px;
        line-height: 0;
        filter: drop-shadow(0 2px 4px rgba(15, 23, 42, .22));
    }

    .prof-foto-lencana svg {
        display: block;
        width: 100%;
        height: 100%;
        overflow: visible;
    }

    /* Tepi putih dibuat dari garis tebal pada bentuk yang sama, jadi ia
       mengikuti gerigi persis tanpa perlu path kedua. */
    .prof-lencana-tepi {
        fill: none;
        stroke: #fff;
        stroke-width: 11;
        stroke-linejoin: round;
    }

    .prof-lencana-isi {
        fill: #16a34a;
    }

    .prof-foto-lencana.belum .prof-lencana-isi {
        fill: #f59e0b;
    }

    .prof-lencana-centang {
        fill: none;
        stroke: #fff;
        stroke-width: 11;
        stroke-linecap: round;
        stroke-linejoin: round;
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
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        column-gap: 14px;
        align-items: center;
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

    .prof-unggah > .mis-medali {
        grid-column: 1;
        grid-row: 1 / span 2;
        align-self: center;
    }

    .prof-unggah-nama {
        grid-column: 2;
        grid-row: 1;
    }

    .prof-unggah > .mis-bantuan {
        grid-column: 2;
        grid-row: 2;
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
    /* Ubin sejajar dengan judul butirnya, bukan dirata-tengahkan terhadap
       judul + keterangan — sama seperti kepala bagian dan baris email. */
    .prof-lengkap-butir {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        column-gap: 14px;
        align-items: center;
        width: 100%;
        padding: 8px 10px;
        border: 1px solid var(--mis-garis);
        border-radius: 12px;
        background: #f8fafc;
        text-align: left;
        cursor: pointer;
        transition: transform .2s ease, border-color .2s ease, background .2s ease;
    }

    /* Aturan yang sama dengan kepala bagian: ubin merentang dua baris lalu
       dirata-tengahkan, jadi ia sejajar dengan blok teksnya. */
    .prof-lengkap-butir > .mis-medali {
        grid-column: 1;
        grid-row: 1 / span 2;
        align-self: center;
    }

    .prof-lengkap-panah {
        grid-column: 3;
        grid-row: 1 / 3;
    }

    .prof-lengkap-butir:hover {
        border-color: #c7d2fe;
        background: #fff;
        transform: translateX(2px);
    }

    /* Judul di baris 1, keterangan di baris 2 — keduanya anak langsung kisi
       supaya ubin bisa disejajarkan dengan baris pertamanya. */
    .prof-lengkap-butir-judul {
        grid-column: 2;
        grid-row: 1;
    }

    .prof-lengkap-butir > .mis-bantuan {
        grid-column: 2;
        grid-row: 2;
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
     * Satu kisi tiga kolom. Ubin dan tombol merentang dua baris lalu
     * dirata-tengahkan, jadi keduanya sejajar dengan BLOK teksnya — aturan
     * yang sama dengan kepala bagian. Keterangan menjorok dengan sendirinya
     * karena ia berada di kolom kedua, bukan lewat padding yang harus
     * dihitung ulang tiap kali ubinnya berubah ukuran.
     */
    .prof-baris {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        column-gap: 11px;
        row-gap: 1px;
        align-items: center;
        padding: 9px 11px;
        border-radius: 13px;
        background: #f8fafc;
        border: 1px solid var(--mis-garis);
    }

    .prof-baris > .mis-medali,
    .prof-baris > .mis-tombol-garis {
        grid-row: 1 / span 2;
        align-self: center;
    }

    .prof-baris > .prof-nilai {
        grid-column: 2;
        grid-row: 1;
    }

    .prof-baris-ket {
        grid-column: 2;
        grid-row: 2;
    }

    /* Tombol ubah di baris ini lebih kecil daripada tombol ikon di tabel,
       dan flex: 0 0 auto supaya tidak terjepit jadi 31x34 seperti sebelumnya. */
    .prof-baris .mis-tombol-garis {
        grid-column: 3;
        width: 28px;
        height: 28px;
        border-radius: 9px;
        /* 0,72rem ~ 11,5px pada tombol 28px: pensilnya jelas terbaca tetapi
           masih menyisakan ruang di sekelilingnya. */
        font-size: .72rem;
        color: #6366f1;
    }

    .prof-baris .mis-tombol-garis:hover {
        background: #6366f1;
        border-color: #6366f1;
        color: #fff;
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

    /* Kepala kartu: sama seperti kepala bagian, hanya judulnya sedikit
       lebih besar karena ia menaungi seluruh kartu, bukan satu bagian. */
    .prof-kepala-kartu > .prof-bagian-judul {
        font-size: 1rem;
    }

    /* --------------------------------------------------- bagian isian */

    .prof-bagian + .prof-bagian {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px dashed var(--mis-garis);
    }

    /*
     * Ubin ikon sejajar dengan JUDULNYA, bukan dirata-tengahkan terhadap
     * judul + keterangan. Dengan flex + align-items: center, ubin 30px yang
     * diadu dengan blok dua baris membuat judulnya duduk 15px di atas pusat
     * ikon — persis cacat yang sudah diperbaiki di baris email.
     *
     * Kisi dua kolom: ubin menempati baris judul saja, keterangan turun ke
     * baris kedua kolom kanan sehingga otomatis menjorok sejajar judul.
     */
    .prof-bagian-kepala {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        column-gap: 12px;
        /*
         * row-gap 0, bukan 2px.
         *
         * Ubin 30px lebih tinggi daripada judul 18px, jadi baris judul sudah
         * ikut setinggi ubin dan menyisakan 6px di bawah judulnya. Menambah
         * row-gap di atas itu membuat keterangan terdorong 8px — terasa
         * renggang padahal angkanya kecil.
         */
        row-gap: 2px;
        align-items: center;
        margin-bottom: 10px;
    }

    /*
     * Ubin merentang dua baris lalu dirata-tengahkan, jadi ia sejajar dengan
     * BLOK teksnya (judul + keterangan), bukan dengan judulnya saja.
     *
     * Sempat dicoba sebaliknya — ubin hanya di baris judul — supaya judulnya
     * setinggi ikon. Hasilnya ikon terlihat menggantung di bagian atas
     * teksnya, karena keterangan di bawah membuat bloknya lebih tinggi
     * daripada ubin.
     */
    .prof-bagian-kepala > .mis-medali {
        grid-column: 1;
        grid-row: 1 / span 2;
        align-self: center;
    }

    .prof-bagian-judul {
        grid-column: 2;
        grid-row: 1;
    }

    .prof-bagian-sub {
        grid-column: 2;
        grid-row: 2;
    }

    /* Kepala dengan satu lencana di kanan, mis. "3 gagal dalam 30 hari".
       Lencananya membentang dua baris supaya ia rata tengah terhadap
       seluruh blok judul, bukan terhadap baris judulnya saja. */
    .prof-bagian-kepala.punya-aksi {
        grid-template-columns: auto minmax(0, 1fr) auto;
    }

    .prof-bagian-lencana {
        grid-column: 3;
        grid-row: 1 / span 2;
        align-self: center;
    }

    .prof-bagian-judul {
        margin: 0;
        line-height: 1.25;
        font-size: .92rem;
        font-weight: 800;
        color: var(--mis-tinta);
        letter-spacing: -.01em;
    }

    .prof-bagian-sub {
        margin: 0;
        line-height: 1.35;
        font-size: .75rem;
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
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        font-size: .74rem;
        color: var(--mis-tinta-4);
        flex: 1 1 180px;
    }

    .prof-aksi-catatan i,
    .prof-salah i {
        font-size: inherit;
        line-height: 1;
        flex: 0 0 auto;
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
    }

    .prof-tab-isi .tab-pane.active > form {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
    }

    /* Tidak lagi didorong ke dasar kartu: kartunya sekarang berhenti di
       ujung isinya, jadi baris tombol memang sudah di bawah. */
    .prof-tab-isi .prof-aksi {
        margin-top: 18px;
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

    .prof-salah {
        align-items: center;
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
        background: rgba(15, 23, 42, .5);
        backdrop-filter: blur(5px);
        padding: 16px;
        overflow-y: auto;
    }

    /*
     * Lebarnya dipatok 380px: isinya cuma dua isian, dan kotak selebar
     * 440px membuat tiap baris terlihat menganga.
     */
    .custom-popup-content {
        position: relative;
        max-width: 380px;
        width: 100%;
        margin: 10vh auto;
        background: #fff;
        padding: 18px;
        border-radius: 18px;
        border: 1px solid rgba(255, 255, 255, .7);
        box-shadow: 0 24px 48px -18px rgba(15, 23, 42, .45);
        font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
        /* Muncul dengan naik sedikit dan membesar tipis, bukan menyembul
           begitu saja. */
        animation: prof-jendela-masuk .22s cubic-bezier(.22, .61, .36, 1);
    }

    @keyframes prof-jendela-masuk {
        from { opacity: 0; transform: translateY(10px) scale(.97); }
        to { opacity: 1; transform: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .custom-popup-content {
            animation: none;
        }
    }

    /* Tombol tutup memakai rupa tombol ikon sistem, bukan tanda silang polos. */
    .custom-popup-close {
        position: absolute;
        right: 14px;
        top: 14px;
        display: grid;
        place-items: center;
        width: 28px;
        height: 28px;
        border: 1px solid var(--mis-garis);
        border-radius: 9px;
        background: #fff;
        font-size: .72rem;
        line-height: 1;
        color: var(--mis-tinta-3);
        cursor: pointer;
        transition: all .2s ease;
    }

    /* Tanpa ini tanda silangnya 20px oleh aturan global layout, dan nyaris
       memenuhi tombol 28px-nya. */
    .custom-popup-close i {
        font-size: inherit;
        line-height: 1;
    }

    .custom-popup-close:hover {
        background: #fff1f2;
        border-color: #fecdd3;
        color: #e11d48;
    }

    /*
     * Kepala jendela memakai pola yang sama dengan kepala bagian: ubin ikon
     * merentang dua baris lalu dirata-tengahkan, jadi sejajar dengan blok
     * judul + keterangan. Garis putus-putus memisahkannya dari isian.
     */
    .prof-modal-kepala {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        column-gap: 12px;
        row-gap: 1px;
        align-items: center;
        margin-bottom: 14px;
        padding: 0 34px 14px 0;
        border-bottom: 1px dashed var(--mis-garis);
    }

    .prof-modal-kepala > .mis-medali {
        grid-column: 1;
        grid-row: 1 / span 2;
        align-self: center;
    }

    .prof-modal-judul {
        grid-column: 2;
        grid-row: 1;
        margin: 0;
        font-size: .95rem;
        font-weight: 800;
        line-height: 1.25;
        color: var(--mis-tinta);
    }

    .prof-modal-sub {
        grid-column: 2;
        grid-row: 2;
        margin: 0;
        font-size: .75rem;
        line-height: 1.35;
        color: var(--mis-tinta-3);
    }

    .prof-rapat {
        margin-bottom: 12px;
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

    /* ------------------------------------------------- syarat kata sandi */

    .prof-syarat {
        list-style: none;
        margin: 12px 0 0;
        padding: 10px 12px;
        display: grid;
        gap: 6px;
        background: #f8fafc;
        border: 1px solid var(--mis-garis);
        border-radius: 13px;
    }

    .prof-syarat li {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: .76rem;
        font-weight: 600;
        line-height: 1.35;
        color: var(--mis-tinta-3);
        transition: color .2s ease;
    }

    /* Penanda bulat di kiri: kosong selagi belum terpenuhi, berisi centang
       begitu terpenuhi. Bentuknya tetap sama supaya barisnya tidak bergeser. */
    .prof-syarat li::before {
        content: "";
        flex: 0 0 15px;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        border: 1.5px solid var(--mis-garis);
        background: #fff;
        transition: all .2s ease;
    }

    .prof-syarat li.oke {
        color: #047857;
    }

    .prof-syarat li.oke::before {
        border-color: #10b981;
        background: #10b981 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 6L9 17l-5-5'/%3E%3C/svg%3E") center/9px no-repeat;
    }

    /* Catatan di samping isian, bukan di bawahnya: kisi dua kolom jadi
       terisi penuh dan tabnya tidak menyisakan petak kosong di kanan. */
    .prof-catatan-samping {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin: 0;
        align-self: center;
        padding: 10px 12px;
        border-radius: 13px;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        font-size: .76rem;
        line-height: 1.45;
        color: #0369a1;
    }

    .prof-catatan-samping i {
        font-size: inherit;
        line-height: 1.45;
        flex: 0 0 auto;
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
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        display: grid;
        place-items: center;
        width: 24px;
        height: 24px;
        border-radius: 7px;
        cursor: pointer;
        color: var(--mis-tinta-4);
        transition: all .2s ease;
        z-index: 3;
        /* !important memang perlu: aturan global layout memakai selektor
           .fas yang bobotnya sama, dan ia terbit belakangan. */
        font-size: .8rem !important;
        line-height: 1;
    }

    .password-toggle-inside:hover {
        background: #eef2ff;
        color: #6366f1;
    }

    /* ------------------------------------------------ tombol teks sekunder */

    /*
     * .mis-tombol-garis dan .mis-tombol-bahaya di mis-ui.css sengaja 34x34
     * untuk tombol berisi ikon saja. Dua kelas di bawah ini memakai kerangka
     * .mis-tombol (tinggi, sudut, jarak ikon) tapi berwarna lembut, untuk
     * tombol sekunder yang berteks: "Lupakan perangkat", "Nonaktifkan PIN".
     */
    .prof-tombol-halus {
        background: #fff;
        border: 1px solid var(--mis-garis);
        color: var(--mis-tinta-2);
    }

    .prof-tombol-halus:hover:not(:disabled) {
        border-color: #c7d2fe;
        background: #eef2ff;
        color: #4f46e5;
        transform: translateY(-2px);
    }

    /* Merahnya terlihat tanpa harus disentuh dulu — tombol yang mematikan
       sesuatu tidak boleh menyamar jadi tombol biasa. */
    .prof-tombol-bahaya-teks {
        background: #fff1f2;
        border: 1px solid #fecdd3;
        color: #e11d48;
    }

    .prof-tombol-bahaya-teks:hover:not(:disabled) {
        background: #e11d48;
        border-color: #e11d48;
        color: #fff;
        transform: translateY(-2px);
    }

    /* ---------------------------------------------------- tab PIN masuk */

    /*
     * Dua kotak keterangan di atas formulir: keadaan PIN dan keadaan
     * perangkat. Keduanya sebaris supaya tidak memakan tinggi — yang penting
     * bagi pengguna cuma "sudah aktif atau belum".
     */
    .pin-keadaan {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 13px;
        margin-bottom: 14px;
        border-radius: 13px;
        background: #f8fafc;
        border: 1px solid var(--mis-garis);
    }

    .pin-keadaan.nyala {
        background: linear-gradient(135deg, #f0fdfa, #f5f3ff);
        border-color: #c7d2fe;
    }

    .pin-keadaan-teks {
        flex: 1 1 auto;
        min-width: 0;
    }

    .pin-keadaan-judul {
        margin: 0 0 1px;
        line-height: 1.25;
        font-size: .86rem;
        font-weight: 800;
        color: var(--mis-tinta);
    }

    .pin-keadaan-sub {
        margin: 0;
        line-height: 1.35;
        font-size: .74rem;
        color: var(--mis-tinta-3);
    }

    .pin-perangkat {
        padding: 12px 13px;
        margin-bottom: 14px;
        border-radius: 13px;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
    }

    .pin-perangkat.siap {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #ecfdf5;
        border-color: #a7f3d0;
    }

    .pin-perangkat-teks {
        flex: 1 1 auto;
        min-width: 0;
    }

    .pin-perangkat.siap .prof-tombol-halus {
        height: 38px;
        padding: 0 14px;
        font-size: .8rem;
        flex: 0 0 auto;
    }

    .pin-perangkat .prof-bagian-kepala {
        margin-bottom: 10px;
    }

    /* Satu kotak isian + satu tombol yang berdampingan. Dipakai di tab PIN
       ("Daftarkan") dan tab Keamanan ("Keluarkan"). */
    .prof-baris-aksi {
        display: flex;
        gap: 10px;
    }

    /*
     * Tinggi keduanya disamakan di 46px.
     *
     * .pin-isian aslinya 50px dan .mis-tombol 42px, jadi kotak dan tombol
     * yang berdampingan tidak rata atas-bawah. 46px di tengah keduanya:
     * kotaknya masih lebih lega daripada isian biasa, tombolnya tidak
     * terlihat kekecilan.
     */
    .prof-baris-aksi > .form-control-modern {
        flex: 1 1 auto;
        min-width: 0;
        height: 46px;
    }

    .prof-baris-aksi .mis-tombol {
        flex: 0 0 auto;
        height: 46px;
    }

    /*
     * Kotak angka PIN: sedikit lebih tinggi daripada isian biasa dan angkanya
     * direnggangkan, senada dengan kotak kode di halaman masuk. Bedanya
     * disengaja — inilah yang akan diketik orang tiap kali masuk.
     */
    .pin-isian {
        height: 50px;
        text-align: center;
        font-size: 1.2rem;
        font-weight: 800;
        letter-spacing: .45em;
        /* letter-spacing menambah jarak SESUDAH huruf terakhir juga, jadi
           deretnya condong ke kiri; text-indent menggesernya balik. */
        text-indent: .45em;
        color: var(--mis-tinta);
    }

    .pin-isian::placeholder {
        font-size: .95rem;
        letter-spacing: .3em;
        font-weight: 600;
    }

    .pin-lihat {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 12px 0 0;
        font-size: .76rem;
        font-weight: 600;
        color: var(--mis-tinta-3);
        cursor: pointer;
        user-select: none;
    }

    .pin-lihat input {
        width: 15px;
        height: 15px;
        margin: 0;
        accent-color: #6366f1;
        cursor: pointer;
    }

    .pin-tip {
        list-style: none;
        margin: 12px 0 0;
        padding: 11px 12px;
        display: grid;
        gap: 9px;
        background: #f8fafc;
        border: 1px solid var(--mis-garis);
        border-radius: 13px;
    }

    .pin-tip li {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: .76rem;
        font-weight: 600;
        line-height: 1.4;
        color: var(--mis-tinta-3);
    }

    /* ---------------------------------------------------- tab keamanan */

    .kmn-daftar {
        display: grid;
        gap: 8px;
    }

    .kmn-perangkat {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 9px 11px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid var(--mis-garis);
    }

    /* Perangkat yang sedang dipakai diberi warna, bukan hanya label: itu
       baris yang TIDAK boleh diakhiri, jadi harus terbaca sekilas. */
    .kmn-perangkat.ini {
        background: #ecfdf5;
        border-color: #a7f3d0;
    }

    .kmn-perangkat-teks {
        flex: 1 1 auto;
        min-width: 0;
    }

    .kmn-perangkat-nama {
        margin: 0 0 1px;
        line-height: 1.25;
        font-size: .82rem;
        font-weight: 700;
        color: var(--mis-tinta);
        overflow-wrap: anywhere;
    }

    .kmn-perangkat-ket {
        margin: 0;
        line-height: 1.3;
        font-size: .72rem;
        color: var(--mis-tinta-4);
    }

    .kmn-perangkat .mis-pil,
    .kmn-perangkat .mis-tombol {
        flex: 0 0 auto;
    }

    /* Tombol di dalam baris daftar: lebih pendek daripada tombol formulir
       supaya barisnya tidak ikut tinggi. */
    .kmn-tombol-kecil {
        height: 32px;
        padding: 0 12px;
        font-size: .76rem;
    }

    /* Tabel riwayat duduk di dalam kartu profil yang sudah punya bayangan,
       jadi bungkusnya cukup bergaris — bayangan di atas bayangan membuatnya
       tampak mengambang. */
    .kmn-tabel {
        box-shadow: none;
        border-radius: var(--mis-radius-kecil);
    }

    .kmn-tabel .mis-tabel thead th,
    .kmn-tabel .mis-tabel tbody td {
        padding: 10px 14px;
    }

    .kmn-waktu {
        font-size: .8rem;
        font-weight: 700;
        color: var(--mis-tinta);
        white-space: nowrap;
    }

    /* Lencana di atas, alasannya di bawah. Di mode kartu (ponsel) sel ini
       rata kanan, jadi keduanya ikut rata kanan tanpa aturan tambahan. */
    .kmn-hasil {
        display: grid;
        gap: 3px;
        justify-items: start;
    }

    .kmn-samar {
        font-size: .78rem;
        color: var(--mis-tinta-4);
        overflow-wrap: anywhere;
    }

    .kmn-catatan {
        align-items: flex-start;
        margin-top: 12px;
        line-height: 1.45;
    }

    .kmn-catatan i {
        margin-top: 2px;
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

        /* Lencana keadaan turun ke baris sendiri, rata dengan teksnya —
           kalau dipaksa tetap di kanan, judulnya terpatah dua baris. */
        .pin-keadaan {
            flex-wrap: wrap;
        }

        .pin-keadaan > .prof-lencana {
            order: 3;
            margin-left: 48px;
        }

        /* Tombol dan isian melebar penuh: di ponsel tidak ada ruang untuk
           dua hal berdampingan tanpa salah satunya jadi terlalu sempit. */
        .pin-perangkat.siap {
            flex-wrap: wrap;
        }

        .pin-perangkat.siap .prof-tombol-halus {
            width: 100%;
            margin-left: 0;
        }

        .prof-baris-aksi {
            flex-direction: column;
        }

        .prof-baris-aksi .mis-tombol {
            width: 100%;
        }

        /* Lencana "n gagal dalam 30 hari" turun ke bawah keterangannya;
           dipaksa tetap di kanan, judulnya terpatah tiap kata. */
        .prof-bagian-kepala.punya-aksi {
            grid-template-columns: auto minmax(0, 1fr);
        }

        .prof-bagian-lencana {
            grid-column: 2;
            grid-row: 3;
            justify-self: start;
            margin-top: 6px;
        }

        /* Tombol "Akhiri" pindah ke baris sendiri: di 390px, nama perangkat
           dan tombol berdampingan menyisakan dua kata per baris. */
        .kmn-perangkat {
            flex-wrap: wrap;
        }

        .kmn-perangkat > .mis-pil,
        .kmn-perangkat > .mis-tombol {
            margin-left: 36px;
        }

        /* Di mode kartu, sel nilai berada di sisi kanan; lencana dan
           alasannya ikut rata kanan supaya tepinya lurus dengan nilai
           baris lain. */
        .kmn-hasil {
            justify-items: end;
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
