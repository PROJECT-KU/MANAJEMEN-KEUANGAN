{{-- Bahasa rupa blog, dipakai BERSAMA oleh /blog dan halaman kategori.

     Dipisah ke sini begitu halaman kedua memakainya. Disalin, keduanya akan
     berbeda perlahan — dan bedanya baru ketahuan saat seseorang menekan
     keripik kategori di /blog lalu mendarat di halaman yang warnanya lain.

     Dimuat lewat @include di dalam @section('konten'), BUKAN ditaruh
     menggantung di luar section seperti berkas kategori yang lama: yang
     menggantung tetap tercetak, tetapi mendarat SEBELUM <!doctype html>.
     Peramban memaafkannya; yang membaca sumbernya tidak. --}}
<style>
    /* Palet dan huruf DISALIN dari beranda, bukan dikarang ulang.

       Nilainya sengaja ditulis lagi di sini alih-alih menumpang .bnd:
       aturan beranda hidup di dalam home.blade.php dan tidak ikut terkirim
       ke halaman ini. Menumpangnya berarti halaman blog diam-diam
       kehilangan seluruh warnanya pada hari beranda dirapikan. */
    .blg {
        --kertas: #fdf5e8;  --kertas-2: #f8ead4;
        --tinta: #16233f;   --tinta-2: #5c6b85;
        --jingga: #e76d20;  --jingga-muda: #fb8c3d; --jingga-tua: #ad4d12;
        --jingga-kuat: #b85213;
        --stabilo: #ffd84d;
        --rumput: #0f766e;  --ungu: #5b3fd6; --langit: #1668c9;

        background: var(--kertas);
        color: var(--tinta);
        font-family: 'Figtree', system-ui, sans-serif;
        overflow-x: clip;
    }
    .blg h1, .blg h2, .blg h3 {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 800; color: var(--tinta); margin: 0;
        letter-spacing: -.01em;
    }
    .blg p { margin: 0; color: var(--tinta-2); line-height: 1.68; }
    .blg a { text-decoration: none; }

    /* ========================================================= kepala */
    /* 70px, BUKAN 900px. Yang lama memakai #hero milik templat yang
       memaksa tinggi tetap 900px; terukur 513px di antaranya putih kosong
       yang harus digulung orang sebelum melihat satu judul pun. */
    /* 124px di atas, SAMA dengan hero beranda.

       Kepala situs berposisi fixed setinggi 82px dan tidak memesan ruang
       untuk dirinya sendiri. Dengan 70px, stikernya mulai di y=70 — dua
       belas piksel teratasnya tertutup kepala situs, dan yang terlihat cuma
       potongan bawah lencana yang seperti terpotong salah. */
    .blg-kepala {
        padding: 124px 0 54px;
        background: linear-gradient(180deg, var(--kertas-2) 0%, var(--kertas) 100%);
        text-align: center;
    }
    .blg-stiker {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 7px 17px; margin-bottom: 18px;
        background: var(--tinta); color: var(--kertas);
        border-radius: 999px;
        font-family: 'Figtree', sans-serif; font-weight: 800; font-size: .84rem;
    }
    .blg-stiker i { color: var(--stabilo); }

    .blg .blg-judul {
        font-size: clamp(1.9rem, 4.2vw, 3rem);
        line-height: 1.12; max-width: 17ch; margin: 0 auto;
    }

    /* Stabilonya digambar di BELAKANG kata lewat ::after, bukan sebagai
       latar kotak: coretan yang miring sedikit dan tidak rata terbaca
       seperti ditarik tangan, dan itu yang membedakannya dari highlight
       bawaan editor. */
    .blg-stabilo { position: relative; display: inline-block; z-index: 0; }
    .blg-stabilo::after {
        content: ''; position: absolute; z-index: -1;
        left: -5px; right: -5px; bottom: .06em; height: .42em;
        background: var(--stabilo); border-radius: 3px;
        transform: rotate(-1.1deg) skewX(-9deg);
    }

    .blg p.blg-sub {
        max-width: 58ch; margin: 17px auto 0;
        font-size: 1.04rem;
    }

    .blg-keripik {
        display: flex; flex-wrap: wrap; justify-content: center; gap: 10px;
        margin-top: 28px;
    }
    .blg-keripik-butir {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 8px 15px;
        background: var(--kertas);
        border: 2px solid var(--tinta); border-radius: 999px;
        box-shadow: 3px 3px 0 var(--c);
        color: var(--tinta); font-weight: 700; font-size: .9rem;
        transition: transform .17s ease, box-shadow .17s ease;
    }
    .blg-keripik-butir:hover {
        color: var(--tinta);
        transform: translate(-2px, -2px); box-shadow: 6px 6px 0 var(--c);
    }
    .blg-keripik-butir span {
        min-width: 21px; padding: 1px 6px; border-radius: 999px;
        background: var(--c); color: #fff;
        font-size: .74rem; font-weight: 800; text-align: center;
    }

    /* ====================================================== pencarian */
    .blg-cari {
        display: flex; align-items: center; gap: 10px;
        max-width: 560px; margin: 24px auto 0;
        padding: 7px 7px 7px 17px;
        background: var(--kertas);
        border: 2px solid var(--tinta); border-radius: 999px;
        box-shadow: 4px 4px 0 var(--jingga-muda);
    }
    .blg-cari:focus-within { box-shadow: 6px 6px 0 var(--jingga); }
    .blg-cari > i { flex: 0 0 auto; color: var(--jingga); }

    .blg-cari input {
        flex: 1 1 auto; min-width: 0;
        padding: 9px 0; border: none; background: transparent;
        color: var(--tinta); font-size: .96rem; font-weight: 600;
    }
    .blg-cari input::placeholder { color: var(--tinta-2); font-weight: 500; }
    /* Tanpa ini peramban menggambar cincin biru bawaannya di dalam pil —
       satu-satunya biru di halaman, dan posisinya meleset dari tepi pil. */
    .blg-cari input:focus { outline: none; }
    /* Silang bawaan Safari/Chrome dibuang: kita punya tombol hapus sendiri
       yang juga membersihkan alamatnya, bukan cuma isian kotaknya. */
    .blg-cari input::-webkit-search-cancel-button { display: none; }

    .blg-cari-hapus {
        flex: 0 0 auto;
        display: inline-flex; align-items: center; justify-content: center;
        width: 30px; height: 30px; border-radius: 50%;
        background: var(--kertas-2); color: var(--tinta-2); font-size: .8rem;
    }
    .blg-cari-hapus:hover { background: var(--tinta); color: var(--kertas); }

    .blg-cari button {
        flex: 0 0 auto;
        padding: 10px 21px;
        background: var(--jingga-kuat); color: #fff;
        border: 2px solid var(--tinta); border-radius: 999px;
        font-weight: 800; font-size: .92rem;
    }
    .blg-cari button:hover { background: var(--tinta); }

    .blg-bagian { padding: 58px 0; }

    .blg .blg-judul-kecil {
        font-size: 1.62rem; margin-bottom: 28px;
    }

    /* ======================================================== sorotan */
    /* Dua kolom TAK SAMA — gambar lebih lebar daripada teksnya. Kisi
       simetris membuat tulisan terbaru terbaca sebagai kartu biasa yang
       kebetulan lebih besar, bukan sebagai sorotan. */
    .blg-sorot {
        display: grid; grid-template-columns: 1.15fr 1fr;
        background: var(--kertas);
        border: 2px solid var(--tinta); border-radius: 24px;
        box-shadow: 9px 9px 0 var(--jingga);
        overflow: hidden; color: var(--tinta);
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .blg-sorot:hover {
        color: var(--tinta);
        transform: translate(-3px, -3px); box-shadow: 13px 13px 0 var(--jingga);
    }
    .blg-sorot-gambar { display: block; overflow: hidden; min-height: 320px; }
    .blg-sorot-gambar img {
        width: 100%; height: 100%; object-fit: cover; display: block;
        transition: scale .45s ease;
    }
    .blg-sorot:hover .blg-sorot-gambar img { scale: 1.05; }

    .blg-sorot-isi {
        display: flex; flex-direction: column; justify-content: center;
        gap: 13px; padding: 36px 38px;
        border-left: 2px solid var(--tinta);
    }
    .blg-tanda {
        align-self: flex-start;
        padding: 3px 11px; border-radius: 999px;
        background: var(--stabilo); color: var(--tinta);
        font-size: .74rem; font-weight: 800;
        letter-spacing: .08em; text-transform: uppercase;
    }
    .blg .blg-sorot-isi h2 { font-size: 1.72rem; line-height: 1.24; }
    .blg-cuplik { color: var(--tinta-2); font-size: .98rem; line-height: 1.65; }

    .blg-meta {
        display: flex; flex-wrap: wrap; gap: 5px 17px;
        color: var(--tinta-2); font-size: .85rem; font-weight: 600;
    }
    .blg-meta i { margin-right: 5px; color: var(--jingga); }

    .blg-ajak {
        display: inline-flex; align-items: center; gap: 9px; margin-top: 5px;
        color: var(--jingga-tua); font-weight: 800;
        transition: gap .2s ease;
    }
    .blg-sorot:hover .blg-ajak { gap: 15px; }

    /* ========================================================== kisi */
    .blg-kisi { display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px; }

    .blg-kartu {
        display: flex; flex-direction: column;
        background: var(--kertas);
        border: 2px solid var(--tinta); border-radius: 20px;
        box-shadow: 6px 6px 0 var(--jingga-muda);
        color: var(--tinta); overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .blg-kartu:hover {
        color: var(--tinta);
        transform: translate(-3px, -3px); box-shadow: 10px 10px 0 var(--jingga-muda);
    }
    .blg-kartu-gambar {
        position: relative; display: block;
        aspect-ratio: 16 / 10; overflow: hidden;
    }
    .blg-kartu-gambar img {
        width: 100%; height: 100%; object-fit: cover; display: block;
        transition: scale .4s ease;
    }
    .blg-kartu:hover .blg-kartu-gambar img { scale: 1.07; }
    .blg-kartu-tag {
        position: absolute; left: 12px; bottom: 12px;
        padding: 4px 11px; border-radius: 999px;
        background: var(--tinta); color: var(--kertas);
        font-size: .73rem; font-weight: 800;
    }

    .blg-kartu-isi {
        display: flex; flex-direction: column; flex: 1 1 auto; gap: 9px;
        padding: 18px 20px 20px;
        border-top: 2px solid var(--tinta);
    }
    .blg .blg-kartu-isi h3 { font-size: 1.06rem; line-height: 1.35; }

    .blg-kartu-kaki {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        margin-top: auto; padding-top: 15px;
        border-top: 2px dashed rgba(22, 35, 63, .14);
    }
    .blg-penulis {
        display: inline-flex; align-items: center; gap: 8px;
        color: var(--tinta-2); font-size: .84rem; font-weight: 700;
    }
    .blg-penulis img {
        width: 28px; height: 28px; border-radius: 50%; object-fit: cover;
        border: 2px solid var(--tinta);
    }
    .blg-ajak-kecil {
        display: inline-flex; align-items: center; gap: 7px;
        color: var(--jingga-tua); font-weight: 800; font-size: .88rem;
        transition: gap .2s ease;
    }
    .blg-kartu:hover .blg-ajak-kecil { gap: 12px; }

    /* ======================================================== ajakan */
    .blg-ajakan {
        padding: 48px 38px; text-align: center;
        background: linear-gradient(135deg, var(--jingga) 0%, var(--jingga-muda) 100%);
        border: 2px solid var(--tinta); border-radius: 26px;
        box-shadow: 10px 10px 0 var(--tinta);
    }
    .blg .blg-ajakan h2 { color: #fff; font-size: clamp(1.5rem, 3vw, 2.1rem); }
    .blg .blg-ajakan p {
        max-width: 52ch; margin: 13px auto 0; color: rgba(255, 255, 255, .93);
    }
    .blg .blg-umpan {
        margin-top: 22px; color: rgba(255, 255, 255, .9);
        font-size: .92rem; font-weight: 600;
    }
    .blg-umpan a {
        display: inline-flex; align-items: center; gap: 7px; margin-left: 6px;
        color: #fff; font-weight: 800; text-decoration: underline;
        text-underline-offset: 3px;
    }
    .blg-umpan a:hover { color: var(--stabilo); }

    .blg-aksi {
        display: flex; flex-wrap: wrap; justify-content: center; gap: 13px;
        margin-top: 26px;
    }
    .blg-tombol {
        display: inline-flex; align-items: center; gap: 9px;
        padding: 13px 24px;
        border: 2px solid var(--tinta); border-radius: 999px;
        font-weight: 800; font-size: .97rem;
        transition: transform .17s ease, box-shadow .17s ease;
    }
    .blg-tombol:hover { transform: translate(-2px, -2px); }
    .blg-tombol-utama {
        background: var(--kertas); color: var(--jingga-tua);
        box-shadow: 4px 4px 0 var(--tinta);
    }
    .blg-tombol-utama:hover { color: var(--jingga-tua); box-shadow: 7px 7px 0 var(--tinta); }
    .blg-tombol-kedua {
        background: var(--tinta); color: #fff;
        box-shadow: 4px 4px 0 rgba(22, 35, 63, .35);
    }
    .blg-tombol-kedua:hover { color: #fff; box-shadow: 7px 7px 0 rgba(22, 35, 63, .35); }

    /* =============================================== halaman kategori */
    /* Pembungkusnya yang jadi baris sendiri, BUKAN tautannya.

       Tanpa ini keduanya inline dan "Semua tulisan" berdempet sebaris dengan
       stiker "Kategori". Yang dibuat blok pembungkusnya, bukan <a>-nya:
       tautan selebar layar berarti sasaran klik tak terlihat yang membentang
       sampai tepi halaman. */
    .blg-balik-baris { margin-bottom: 14px; }
    .blg-balik {
        display: inline-flex; align-items: center; gap: 8px;
        color: var(--tinta-2); font-weight: 700; font-size: .9rem;
        transition: gap .2s ease, color .2s ease;
    }
    .blg-balik:hover { color: var(--jingga-tua); gap: 13px; }

    /* Keripik yang sedang dibuka dibalik warnanya — isi penuh, bukan garis
       tepi saja. Pembeda yang cuma menebalkan garis tidak terlihat di antara
       lima keripik yang semuanya bergaris tebal. */
    .blg-keripik-butir.blg-keripik-aktif {
        background: var(--c); color: #fff;
        box-shadow: 3px 3px 0 var(--tinta);
    }
    .blg-keripik-butir.blg-keripik-aktif:hover { color: #fff; }
    .blg-keripik-aktif span { background: rgba(255, 255, 255, .25); color: #fff; }

    .blg-kosong {
        max-width: 46ch; margin: 0 auto; padding: 52px 30px; text-align: center;
        background: var(--kertas);
        border: 2px dashed rgba(22, 35, 63, .28); border-radius: 22px;
    }
    .blg-kosong i { font-size: 2.3rem; color: var(--jingga); }
    .blg .blg-kosong h2 { margin: 14px 0 9px; font-size: 1.35rem; }
    .blg-kosong .blg-tombol { margin-top: 22px; }

    /* Paginasi bawaan Bootstrap 4 — biru #007bff dan bersudut tajam, warna
       yang tidak ada di mana pun lagi di halaman ini. */
    .blg-halaman { display: flex; justify-content: center; margin-top: 38px; }
    .blg-halaman .pagination { gap: 8px; margin: 0; flex-wrap: wrap; }
    .blg-halaman .page-item .page-link {
        min-width: 42px; padding: 9px 14px;
        background: var(--kertas); color: var(--tinta);
        border: 2px solid var(--tinta); border-radius: 13px;
        box-shadow: 3px 3px 0 rgba(22, 35, 63, .16);
        font-weight: 800; text-align: center;
        transition: transform .17s ease, box-shadow .17s ease;
    }
    .blg-halaman .page-item .page-link:hover {
        background: var(--kertas-2); color: var(--tinta);
        transform: translate(-2px, -2px); box-shadow: 5px 5px 0 rgba(22, 35, 63, .16);
    }
    .blg-halaman .page-item.active .page-link {
        background: var(--jingga-kuat); color: #fff;
        border-color: var(--tinta); box-shadow: 3px 3px 0 var(--tinta);
    }
    .blg-halaman .page-item.disabled .page-link {
        background: var(--kertas-2); color: var(--tinta-2);
        border-color: rgba(22, 35, 63, .25); box-shadow: none;
    }

    @media (max-width: 991.98px) {
        .blg-kisi { grid-template-columns: repeat(2, 1fr); }
        .blg-sorot { grid-template-columns: 1fr; }
        .blg-sorot-gambar { min-height: 0; aspect-ratio: 16 / 9; }
        /* Garisnya pindah ke ATAS begitu kolomnya menumpuk; dibiarkan di
           kiri, ia jadi garis tegak menggantung di sisi teks. */
        .blg-sorot-isi { border-left: none; border-top: 2px solid var(--tinta); padding: 26px 24px; }
    }
    @media (max-width: 767.98px) {
        .blg-kisi { grid-template-columns: 1fr; }
        .blg-kepala { padding: 104px 0 40px; }
        .blg-bagian { padding: 42px 0; }
        .blg-ajakan { padding: 34px 22px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .blg *,
        .blg *::before,
        .blg *::after { transition: none !important; }
    }
</style>
