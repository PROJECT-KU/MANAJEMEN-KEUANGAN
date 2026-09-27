{{-- Gaya dasbor. Semua kelas berawalan .dsb- supaya tidak bertabrakan dengan
     CSS Bootstrap milik layout admin. --}}
<style>
    .dsb {
        --dsb-tinta: #0f172a;
        --dsb-tinta-2: #475569;
        --dsb-tinta-3: #64748b;
        --dsb-garis: #e2e8f0;
        --dsb-kartu: rgba(255, 255, 255, .92);
        /* Disamakan dengan .mis-* dan dasbor lemon: radius lebih kecil,
           bayangan tipis + tepi 1px. Yang lama membuat tiap kartu tampak
           mengambang dan kebesaran. */
        --dsb-radius: 18px;
        --dsb-bayang: 0 6px 16px -12px rgba(15, 23, 42, .55);
        --dsb-ungu: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        --dsb-hijau: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        --dsb-merah: linear-gradient(135deg, #f43f5e 0%, #fb7185 100%);
        --dsb-biru: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
        --dsb-kuning: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
        --dsb-jingga: linear-gradient(135deg, #f97316 0%, #fb923c 100%);

        font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
        color: var(--dsb-tinta-2);
        display: grid;
        gap: var(--mis-jarak, 14px);
    }

    .dsb *,
    .dsb *::before,
    .dsb *::after {
        box-sizing: border-box;
    }

    /* ------------------------------------------------------------- kepala */

    /* Ucapan di bilah atas disembunyikan khusus di dasbor: kepala kartu ini
       sudah menyapa, jadi keduanya bersamaan terasa mengulang. */
    body.dasbor-terbuka .main-navbar #greeting {
        /* !important memang perlu: elemennya memakai utilitas .d-flex milik
           Bootstrap yang sudah ber-!important. */
        display: none !important;
    }

    /* Sisi kiri bilah atas diisi nama halaman supaya tidak menganggur. */
    body.dasbor-terbuka .main-navbar .form-inline::before {
        content: "Dashboard";
        margin-left: 4px;
        font-size: .85rem;
        font-weight: 700;
        letter-spacing: -.01em;
        color: #64748b;
    }

    .dsb-kepala {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        background:
            radial-gradient(520px 200px at 0% 0%, rgba(99, 102, 241, .07), transparent 70%),
            var(--dsb-kartu);
        border: 1px solid var(--dsb-garis);
        border-radius: var(--dsb-radius);
        box-shadow: var(--dsb-bayang);
        padding: 14px 18px;
    }

    .dsb-kepala-avatar {
        display: grid;
        place-items: center;
        width: 50px;
        height: 50px;
        flex: 0 0 50px;
        border-radius: 17px;
        background: var(--dsb-ungu);
        color: #fff;
        font-size: 1.25rem;
        font-weight: 800;
        box-shadow: 0 12px 22px -14px #6366f1;
    }

    .dsb-kepala-teks {
        flex: 1 1 260px;
        min-width: 0;
    }

    .dsb-judul {
        margin: 0;
        font-size: clamp(1.15rem, 1.9vw, 1.5rem);
        font-weight: 800;
        letter-spacing: -.03em;
        line-height: 1.25;
        background: linear-gradient(to right, #1e293b 0%, #6366f1 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .dsb-sub {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        margin: 4px 0 0;
        font-size: .82rem;
        color: var(--dsb-tinta-3);
    }

    .dsb-tanggal {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        color: var(--dsb-tinta-3);
        white-space: nowrap;
    }

    .dsb-tanggal i {
        font-size: 12px;
        color: #a5b4fc;
    }

    .dsb-pemisah {
        color: #cbd5e1;
    }

    .dsb-kepala-aksi {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
    }

    .dsb-segar {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: .76rem;
        font-weight: 600;
        color: #94a3b8;
        white-space: nowrap;
    }

    .dsb-tombol-segar {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 15px;
        border: 1px solid var(--dsb-garis);
        border-radius: 13px;
        background: #fff;
        font-family: inherit;
        font-size: .8rem;
        font-weight: 700;
        color: #6366f1;
        cursor: pointer;
        transition: all .25s ease;
        white-space: nowrap;
    }

    .dsb-tombol-segar:hover:not(:disabled) {
        border-color: #a5b4fc;
        box-shadow: 0 8px 18px -12px #6366f1;
    }

    .dsb-tombol-segar:disabled {
        opacity: .7;
        cursor: progress;
    }

    .dsb-berputar {
        animation: dsb-putar .8s linear infinite;
    }

    @keyframes dsb-putar {
        to { transform: rotate(360deg); }
    }

    /* Tabel alternatif grafik: hanya untuk pembaca layar, tidak boleh
       terlihat. Tanpa aturan ini, isinya muncul sebagai daftar angka
       panjang di bawah judul grafik. */
    .dsb-khusus-pembaca {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    /* -------------------------------------------------------------- kabar */

    .dsb-kabar {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        border-radius: var(--dsb-radius);
        font-size: .88rem;
        line-height: 1.5;
    }

    .dsb-kabar-kuning {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #78350f;
    }

    .dsb-kabar-ikon {
        display: grid;
        place-items: center;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 14px;
        background: var(--dsb-kuning);
        color: #fff;
        font-size: 1.05rem;
    }

    .dsb-kabar-tombol {
        margin-left: auto;
        flex: 0 0 auto;
        padding: 9px 16px;
        border-radius: 12px;
        background: #78350f;
        color: #fff !important;
        font-size: .8rem;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    /* -------------------------------------------------------------- kartu */

    .dsb-kartu {
        background: var(--dsb-kartu);
        border: 1px solid var(--dsb-garis);
        border-radius: var(--dsb-radius);
        box-shadow: var(--dsb-bayang);
        padding: clamp(16px, 2.2vw, 22px);
    }

    .dsb-kartu-kepala {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }

    .dsb-kartu-judul {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: var(--dsb-tinta);
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .dsb-kartu-sub {
        margin: 4px 0 0;
        font-size: .8rem;
        color: var(--dsb-tinta-3);
    }

    .dsb-ikon-biru { color: #0ea5e9; }
    .dsb-ikon-ungu { color: #6366f1; }
    .dsb-ikon-hijau { color: #10b981; }
    .dsb-ikon-merah { color: #f43f5e; }
    .dsb-ikon-jingga { color: #f97316; }

    .dsb-tautan {
        font-size: .8rem;
        font-weight: 700;
        color: #6366f1;
        text-decoration: none;
        white-space: nowrap;
    }

    .dsb-tautan:hover {
        color: #4f46e5;
        text-decoration: underline;
    }

    /* ---------------------------------------------------------- kartu KPI */

    .dsb-kpi {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 18px;
    }

    .dsb-kpi-kartu {
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        transition: transform .25s ease, box-shadow .25s ease;
    }

    .dsb-kpi-kartu:hover {
        transform: translateY(-3px);
        box-shadow: 0 22px 44px rgba(15, 23, 42, .1);
        text-decoration: none;
        color: inherit;
    }

    .dsb-kpi-kartu .dsb-panah {
        margin-left: auto;
    }

    .dsb-kpi-kartu::after {
        content: "";
        position: absolute;
        inset: 0 0 auto auto;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        transform: translate(38%, -38%);
        opacity: .09;
        background: var(--warna);
    }

    .dsb-ungu { --warna: var(--dsb-ungu); }
    .dsb-hijau { --warna: var(--dsb-hijau); }
    .dsb-merah { --warna: var(--dsb-merah); }
    .dsb-biru { --warna: var(--dsb-biru); }
    .dsb-kuning { --warna: var(--dsb-kuning); }

    .dsb-medali {
        display: grid;
        place-items: center;
        width: 52px;
        height: 52px;
        flex: 0 0 52px;
        border-radius: 17px;
        background: var(--warna, var(--dsb-ungu));
        color: #fff;
        font-size: 1.15rem;
        box-shadow: 0 12px 22px -12px rgba(15, 23, 42, .6);
    }

    .dsb-kpi-isi {
        min-width: 0;
    }

    .dsb-label {
        margin: 0 0 4px;
        line-height: 1.35;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #94a3b8;
    }

    .dsb-angka {
        margin: 0;
        /* angka rupiah bisa panjang; ukurannya menyesuaikan lebar kartu
           supaya "Rp" tidak pernah terpisah dari angkanya */
        font-size: clamp(1.02rem, 1.45vw, 1.3rem);
        font-weight: 800;
        color: var(--dsb-tinta);
        letter-spacing: -.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dsb-catatan {
        margin: 6px 0 0;
        font-size: .76rem;
        color: var(--dsb-tinta-3);
        line-height: 1.5;
    }

    .dsb-delta {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 7px;
        border-radius: 8px;
        font-weight: 800;
        font-size: .72rem;
    }

    .dsb-delta.naik {
        color: #047857;
        background: #d1fae5;
    }

    .dsb-delta.turun {
        color: #be123c;
        background: #ffe4e6;
    }

    /* -------------------------------------------------------------- kisi */

    /* Dulu isi dasbor dibagi "lajur utama" dan "lajur samping" dengan lebar
       tetap. Begitu kartu kas dihapus, lajur utama tinggal satu kartu pendek
       sementara lajur samping berisi empat kartu -- menyisakan lubang kosong
       setinggi 772px di layar 1440. Kini semuanya satu kisi: kartu mengisi
       kolom yang tersedia, dan kartu yang memang butuh lebar (grafik gaji,
       karyawan, artikel, akses cepat) merentang penuh. */
    /*
     * Memakai flex-wrap, bukan grid.
     *
     * Dengan grid, kartu terakhir pada sebuah baris tetap selebar satu kolom
     * sehingga sisa barisnya menganggur -- itulah lubang kosong di sebelah
     * kartu Clinik Scopus dan Keamanan Akun. Pada flex-wrap setiap kartu
     * boleh melar (flex-grow), jadi baris apa pun selalu terisi penuh berapa
     * pun jumlah kartunya.
     */
    .dsb-kisi {
        display: flex;
        flex-wrap: wrap;
        gap: var(--mis-jarak, 14px);
        align-items: stretch;
    }

    .dsb-kisi > section {
        display: flex;
        flex-direction: column;
        flex: 1 1 330px;
        min-width: 0;
        max-width: 100%;
    }

    /* Kartu yang selalu merentang penuh. Pemilihnya harus sama kuatnya
       dengan `.dsb-kisi > section` di atas, kalau tidak flex-basis 330px
       yang menang dan grafik gaji ikut menyempit. */
    .dsb-kisi > section.dsb-lebar {
        flex: 1 1 100%;
    }

    /* isi kosong ikut memenuhi kartu yang teregang, supaya tidak ada
       ruang menganggur di bawahnya */
    .dsb-kisi > section > .dsb-kosong {
        flex: 1;
        align-content: center;
    }

    .dsb-kepala-kanan {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    /* ------------------------------------------------------ perlu tindakan */

    .dsb-tindakan-kisi {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 12px;
    }

    .dsb-tindakan-item {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 13px 15px;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid var(--dsb-garis);
        text-decoration: none;
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }

    .dsb-tindakan-item:hover {
        transform: translateY(-2px);
        border-color: #c7d2fe;
        box-shadow: 0 12px 24px -18px rgba(15, 23, 42, .8);
        text-decoration: none;
    }

    .dsb-tindakan-teks {
        display: flex;
        flex-direction: column;
        min-width: 0;
        flex: 1 1 auto;
    }

    .dsb-tindakan-teks strong {
        font-size: .87rem;
        font-weight: 700;
        color: var(--dsb-tinta);
    }

    .dsb-tindakan-teks small {
        font-size: .74rem;
        color: var(--dsb-tinta-3);
    }

    .dsb-medali.kecil {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        border-radius: 14px;
        font-size: .95rem;
    }

    .dsb-panah {
        color: #cbd5e1;
        font-size: .8rem;
        flex: 0 0 auto;
    }

    /* ------------------------------------------------------ grafik batang */

    /* grafik = garis bantu di belakang + batang di depan */
    .dsb-grafik {
        position: relative;
        margin-top: auto;
    }

    .dsb-garis-bantu {
        position: absolute;
        inset: 22px 0 28px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        pointer-events: none;
    }

    .dsb-garis-bantu span {
        position: relative;
        display: block;
        border-top: 1px dashed var(--dsb-garis);
    }

    .dsb-garis-bantu span i {
        position: absolute;
        right: 0;
        top: -8px;
        padding-left: 6px;
        background: var(--dsb-kartu);
        font-style: normal;
        font-size: .58rem;
        font-weight: 700;
        color: #b8c2cf;
    }

    /* tombol geser tahun pada kepala kartu */
    .dsb-geser {
        display: inline-flex;
        gap: 4px;
        margin-left: 4px;
    }

    .dsb-geser button {
        width: 26px;
        height: 26px;
        display: grid;
        place-items: center;
        border: 1px solid var(--dsb-garis);
        border-radius: 8px;
        background: #fff;
        color: var(--dsb-tinta-3);
        font-size: .62rem;
        cursor: pointer;
        transition: all .2s ease;
    }

    .dsb-geser button:hover:not(:disabled) {
        border-color: #c7d2fe;
        color: #6366f1;
    }

    .dsb-geser button:disabled {
        opacity: .4;
        cursor: not-allowed;
    }

    .dsb-batang {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        align-items: end;
        gap: 8px;
        height: 190px;
        padding-top: 22px;
    }

    .dsb-batang-kolom {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        height: 100%;
        min-width: 0;
    }

    .dsb-batang-isi {
        position: relative;
        border-radius: 8px 8px 4px 4px;
        background: #e2e8f0;
        transition: filter .25s ease, transform .25s ease;
    }

    .dsb-batang-isi.ada {
        background: var(--dsb-biru);
    }

    .dsb-batang-kolom:hover .dsb-batang-isi.ada {
        filter: brightness(1.08);
        transform: translateY(-3px);
    }

    .dsb-batang-tip {
        position: absolute;
        bottom: calc(100% + 7px);
        left: 50%;
        transform: translateX(-50%);
        padding: 4px 9px;
        border-radius: 8px;
        background: #0f172a;
        color: #fff;
        font-size: .68rem;
        font-weight: 700;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s ease;
    }

    .dsb-batang-kolom:hover .dsb-batang-tip {
        opacity: 1;
    }

    /* nilai di atas batang: selalu terlihat, termasuk di layar sentuh */
    .dsb-batang-nilai {
        display: block;
        text-align: center;
        font-size: .58rem;
        font-weight: 800;
        color: #64748b;
        margin-bottom: 4px;
        white-space: nowrap;
    }

    .dsb-batang-label {
        margin-top: 9px;
        text-align: center;
        font-size: .64rem;
        font-weight: 700;
        color: #94a3b8;
    }

    /* --------------------------------------------------------- kategori */

    .dsb-daftar-kategori {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 15px;
    }

    .dsb-kategori-atas {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 7px;
        font-size: .83rem;
    }

    .dsb-kategori-nama {
        font-weight: 700;
        color: var(--dsb-tinta);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dsb-kategori-nilai {
        font-weight: 700;
        color: var(--dsb-tinta-2);
        white-space: nowrap;
    }

    .dsb-bar {
        height: 9px;
        border-radius: 99px;
        background: #f1f5f9;
        overflow: hidden;
    }

    .dsb-bar-isi {
        display: block;
        height: 100%;
        border-radius: 99px;
    }

    .dsb-bar-isi.warna-0 { background: var(--dsb-ungu); }
    .dsb-bar-isi.warna-1 { background: var(--dsb-biru); }
    .dsb-bar-isi.warna-2 { background: var(--dsb-jingga); }
    .dsb-bar-isi.warna-3 { background: var(--dsb-hijau); }
    .dsb-bar-isi.warna-4 { background: var(--dsb-merah); }

    /* ------------------------------------------------------------- mini */

    .dsb-mini {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .dsb-mini-kartu {
        padding: 14px;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid var(--dsb-garis);
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .dsb-mini-kartu::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: var(--warna);
    }

    .dsb-mini-angka {
        display: block;
        font-size: 1.32rem;
        font-weight: 800;
        color: var(--dsb-tinta);
    }

    .dsb-mini-label {
        font-size: .72rem;
        font-weight: 600;
        color: var(--dsb-tinta-3);
    }

    /* ------------------------------------------------------------- orang */

    .dsb-daftar-orang,
    .dsb-daftar-tugas {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 12px;
    }

    .dsb-daftar-orang li,
    .dsb-daftar-tugas li {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 13px;
        border-radius: 15px;
        background: #f8fafc;
        border: 1px solid var(--dsb-garis);
    }

    .dsb-avatar {
        display: grid;
        place-items: center;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        border-radius: 13px;
        background: var(--dsb-ungu);
        color: #fff;
        font-weight: 800;
    }

    .dsb-avatar.besar {
        width: 62px;
        height: 62px;
        flex: 0 0 62px;
        border-radius: 20px;
        font-size: 1.5rem;
        margin: 0 auto 14px;
    }

    .dsb-orang-teks,
    .dsb-tugas-teks {
        display: flex;
        flex-direction: column;
        min-width: 0;
        flex: 1 1 auto;
    }

    .dsb-orang-teks strong,
    .dsb-tugas-teks strong {
        font-size: .87rem;
        font-weight: 700;
        color: var(--dsb-tinta);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dsb-orang-teks small,
    .dsb-tugas-teks small {
        font-size: .74rem;
        color: var(--dsb-tinta-3);
    }

    .dsb-pil {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .dsb-pil-hijau { color: #047857; background: #d1fae5; }
    .dsb-pil-abu { color: #64748b; background: #f1f5f9; }
    .dsb-pil-biru { color: #0369a1; background: #e0f2fe; }
    .dsb-pil-kuning { color: #92400e; background: #fef3c7; }
    .dsb-pil-merah { color: #be123c; background: #ffe4e6; }

    .dsb-titik {
        width: 10px;
        height: 10px;
        flex: 0 0 10px;
        border-radius: 50%;
    }

    .dsb-titik.biru { background: #0ea5e9; }
    .dsb-titik.merah { background: #f43f5e; }
    .dsb-titik.kuning { background: #f59e0b; }
    .dsb-titik.hijau { background: #10b981; }

    .dsb-lewat {
        color: #be123c;
        font-weight: 800;
    }

    /* ---------------------------------------------------------- presensi */

    .dsb-presensi-keadaan {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 14px 15px;
        border-radius: 16px;
        margin-bottom: 15px;
    }

    .dsb-presensi-keadaan strong {
        display: block;
        font-size: .9rem;
        color: var(--dsb-tinta);
    }

    .dsb-presensi-keadaan small {
        font-size: .75rem;
        color: var(--dsb-tinta-3);
    }

    .dsb-presensi-ikon {
        display: grid;
        place-items: center;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 15px;
        color: #fff;
        font-size: 1.05rem;
    }

    .dsb-presensi-keadaan.belum { background: #fff1f2; border: 1px solid #fecdd3; }
    .dsb-presensi-keadaan.belum .dsb-presensi-ikon { background: var(--dsb-merah); }
    .dsb-presensi-keadaan.jalan { background: #ecfdf5; border: 1px solid #a7f3d0; }
    .dsb-presensi-keadaan.jalan .dsb-presensi-ikon { background: var(--dsb-hijau); }
    .dsb-presensi-keadaan.selesai { background: #eff6ff; border: 1px solid #bfdbfe; }
    .dsb-presensi-keadaan.selesai .dsb-presensi-ikon { background: var(--dsb-biru); }

    .dsb-presensi-tombol {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .dsb-tombol {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 42px;
        padding: 0 16px;
        border: 0;
        border-radius: 12px;
        font-family: inherit;
        font-size: .85rem;
        font-weight: 700;
        color: #fff !important;
        text-decoration: none;
        cursor: pointer;
        transition: transform .25s ease, box-shadow .25s ease;
    }

    .dsb-tombol:hover:not(:disabled) {
        transform: translateY(-2px);
        color: #fff;
        text-decoration: none;
    }

    .dsb-tombol-hijau { background: var(--dsb-hijau); box-shadow: 0 10px 20px -12px #10b981; }
    .dsb-tombol-jingga { background: var(--dsb-jingga); box-shadow: 0 10px 20px -12px #f97316; }
    .dsb-tombol-ungu { background: var(--dsb-ungu); box-shadow: 0 10px 20px -12px #6366f1; }

    .dsb-tombol-mati {
        background: #f1f5f9;
        color: #94a3b8 !important;
        cursor: not-allowed;
    }

    .dsb-statistik-kecil {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-top: 16px;
        padding-top: 15px;
        border-top: 1px dashed var(--dsb-garis);
        text-align: center;
    }

    /* varian di bagian atas kartu: garisnya di bawah, bukan di atas */
    .dsb-statistik-kecil.atas {
        margin: 0 0 16px;
        padding: 0 0 15px;
        border-top: 0;
        border-bottom: 1px dashed var(--dsb-garis);
    }

    .dsb-statistik-kecil span {
        display: block;
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--dsb-tinta);
    }

    .dsb-statistik-kecil small {
        font-size: .7rem;
        color: var(--dsb-tinta-3);
    }

    .dsb-lipat {
        display: none;
        align-items: center;
        gap: 6px;
        padding: 6px 11px;
        border: 1px solid var(--dsb-garis);
        border-radius: 10px;
        background: #fff;
        font-family: inherit;
        font-size: .74rem;
        font-weight: 700;
        color: #6366f1;
        cursor: pointer;
    }

    .dsb-lipat i {
        font-size: .6rem;
        transition: transform .25s ease;
    }

    @media (max-width: 767.98px) {
        .dsb-lipat {
            display: inline-flex;
        }
    }

    [x-cloak] {
        display: none !important;
    }

    /* ------------------------------------------------- pita kehadiran */

    .dsb-pita {
        display: flex;
        gap: 4px;
        margin-top: 14px;
    }

    .dsb-pita-hari {
        flex: 1 1 auto;
        height: 22px;
        border-radius: 6px;
        background: #f1f5f9;
        border: 1px solid transparent;
    }

    .dsb-pita-hari.hadir { background: var(--dsb-hijau); }
    .dsb-pita-hari.izin { background: var(--dsb-kuning); }
    .dsb-pita-hari.libur { background: #eef2f7; }
    .dsb-pita-hari.kosong { background: #fee2e2; }
    .dsb-pita-hari.hari-ini { background: #fff; border-color: #a5b4fc; border-style: dashed; }

    .dsb-pita-teks {
        margin: 7px 0 0;
        font-size: .68rem;
        font-weight: 600;
        color: var(--dsb-tinta-3);
        text-align: center;
    }

    /* -------------------------------------------------------------- cuti */

    .dsb-cincin {
        width: 132px;
        height: 132px;
        margin: 4px auto 14px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: conic-gradient(#10b981 calc(var(--isi) * 1%), #e2e8f0 0);
    }

    .dsb-cincin-tengah {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: #fff;
        display: grid;
        place-items: center;
        align-content: center;
    }

    .dsb-cincin-tengah strong {
        font-size: 1.7rem;
        font-weight: 800;
        color: var(--dsb-tinta);
        line-height: 1;
    }

    .dsb-cincin-tengah small {
        font-size: .72rem;
        color: var(--dsb-tinta-3);
    }

    .dsb-cincin-teks {
        margin: 0;
        text-align: center;
        font-size: .8rem;
        color: var(--dsb-tinta-3);
        line-height: 1.6;
    }

    /* ----------------------------------------------------------- artikel */

    .dsb-artikel {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .dsb-artikel-kartu {
        display: block;
        text-decoration: none;
        border-radius: 18px;
        transition: transform .25s ease;
    }

    .dsb-artikel-kartu:hover {
        text-decoration: none;
        transform: translateY(-3px);
    }

    .dsb-artikel-kartu:hover h4 {
        color: #4f46e5;
    }

    .dsb-artikel-kartu h4 {
        margin: 11px 0 5px;
        font-size: .86rem;
        font-weight: 700;
        line-height: 1.45;
        color: var(--dsb-tinta);
    }

    .dsb-artikel-kartu small {
        font-size: .72rem;
        color: #94a3b8;
    }

    .dsb-artikel-gambar {
        position: relative;
        aspect-ratio: 16 / 10;
        border-radius: 16px;
        overflow: hidden;
        /* kerangka berkilau selama gambar belum termuat, supaya kotak abu-abu
           tidak terlihat seperti gambar rusak */
        background: linear-gradient(100deg, #f1f5f9 20%, #e9eef5 50%, #f1f5f9 80%);
        background-size: 220% 100%;
        animation: dsb-kilau 1.4s linear infinite;
    }

    .dsb-artikel-gambar.termuat {
        background: #f1f5f9;
        animation: none;
    }

    .dsb-artikel-gambar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .dsb-artikel-kosong {
        display: grid;
        place-items: center;
        height: 100%;
        color: #cbd5e1;
        font-size: 1.6rem;
    }

    .dsb-artikel-kategori {
        position: absolute;
        left: 10px;
        bottom: 10px;
        padding: 4px 10px;
        border-radius: 9px;
        background: rgba(15, 23, 42, .78);
        color: #fff;
        font-size: .66rem;
        font-weight: 700;
        backdrop-filter: blur(4px);
    }

    /* ------------------------------------------------------ profil ringkas */

    .dsb-profil-ringkas {
        text-align: center;
    }

    .dsb-profil-ringkas .dsb-kartu-judul {
        justify-content: center;
    }

    .dsb-profil-ringkas .dsb-kartu-sub {
        margin-bottom: 16px;
        word-break: break-word;
    }

    /* ------------------------------------------------- menu akses cepat */

    .dsb-pintasan .menu-item,
    .dsb-pintasan #carousel,
    .dsb-pintasan .card-icon {
        font-family: inherit;
    }

    /* markah menu lama memakai jarak & bayangannya sendiri; diselaraskan
       dengan kartu pembungkusnya */
    .dsb-pintasan .menu-item {
        border-radius: 16px;
        padding: 14px 8px;
        transition: background .25s ease, transform .25s ease;
    }

    .dsb-pintasan .menu-item:hover {
        background: #f8fafc;
        transform: translateY(-2px);
    }

    .dsb-pintasan .menu-label {
        font-size: .74rem;
        font-weight: 700;
        color: var(--dsb-tinta-2);
    }

    .dsb-pintasan .icon-circle {
        border-radius: 15px;
        box-shadow: 0 10px 18px -14px rgba(15, 23, 42, .9);
    }

    /* kartu pembungkus sudah punya bayangan sendiri; buang bayangan ganda
       dan jarak berlebih dari markah menu lama */
    .dsb-pintasan .quick-menu-container,
    .dsb-pintasan .menu-grid {
        box-shadow: none !important;
        background: transparent !important;
        padding: 0 !important;
        margin: 0 !important;
        border: 0 !important;
    }

    /* ------------------------------------------ keadaan sedang menyegarkan */

    .dsb-menyegarkan .dsb-kartu {
        position: relative;
        overflow: hidden;
    }

    .dsb-menyegarkan .dsb-kartu::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(100deg, rgba(255, 255, 255, 0) 20%, rgba(255, 255, 255, .75) 50%, rgba(255, 255, 255, 0) 80%);
        background-size: 220% 100%;
        animation: dsb-kilau 1.1s linear infinite;
        pointer-events: none;
    }

    @keyframes dsb-kilau {
        from { background-position: 180% 0; }
        to { background-position: -80% 0; }
    }

    /* ------------------------------------------------------------ kosong */

    .dsb-kosong {
        display: grid;
        place-items: center;
        gap: 9px;
        padding: 26px 16px;
        border: 1px dashed var(--dsb-garis);
        border-radius: 16px;
        background: #f8fafc;
        text-align: center;
    }

    .dsb-kosong i {
        font-size: 1.5rem;
        color: #cbd5e1;
    }

    .dsb-kosong p {
        margin: 0;
        font-size: .8rem;
        color: var(--dsb-tinta-3);
        line-height: 1.55;
    }

    /* ============================== TABLET ============================== */

    @media (max-width: 1199.98px) {

        /* Empat kartu tidak boleh jadi tiga tambah satu: sisa barisnya
           menganggur. Di bawah 1200px dijadikan dua lajur rapi. */
        .dsb-kpi {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

    }

    /* ============================== PONSEL ============================== */

    @media (max-width: 767.98px) {
        .dsb {
            gap: 16px;
        }

        .dsb-kepala {
            padding: 18px 18px 20px;
            align-items: stretch;
        }

        .dsb-kepala-aksi {
            width: 100%;
        }

        .dsb-pemilih {
            width: 100%;
            justify-content: space-between;
        }

        .dsb-pemilih button {
            flex: 1;
            padding: 9px 6px;
            font-size: .76rem;
        }

        .dsb-kartu {
            padding: 18px 16px;
        }

        /* Dua lajur, bukan satu: empat kartu bertumpuk menghabiskan satu
           layar penuh sebelum isi dasbor terlihat. */
        .dsb-kpi {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .dsb-kpi-kartu {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .dsb-kpi-kartu .dsb-medali {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            border-radius: 14px;
            font-size: .95rem;
        }

        .dsb-kpi-kartu .dsb-panah {
            display: none;
        }

        .dsb-angka {
            font-size: 1.02rem;
        }

        .dsb-catatan {
            font-size: .7rem;
        }

        .dsb-kisi {
            gap: 16px;
            grid-template-columns: 1fr;
        }

        .dsb-tindakan-kisi {
            grid-template-columns: 1fr;
        }

        .dsb-batang {
            height: 150px;
            gap: 5px;
        }

        /* nilai di atas batang: selalu terlihat, termasuk di layar sentuh */
    .dsb-batang-nilai {
        display: block;
        text-align: center;
        font-size: .58rem;
        font-weight: 800;
        color: #64748b;
        margin-bottom: 4px;
        white-space: nowrap;
    }

    .dsb-batang-label {
            font-size: .56rem;
        }

        .dsb-artikel {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .dsb-kabar {
            flex-wrap: wrap;
        }

        .dsb-kabar-tombol {
            margin-left: 0;
            width: 100%;
            text-align: center;
        }

        .dsb-statistik-kecil span {
            font-size: 1rem;
        }
    }

    @media (max-width: 400px) {
        .dsb-artikel {
            grid-template-columns: 1fr;
        }
    }

    /* ------------------------------------------------ fokus papan ketik */

    .dsb a:focus-visible,
    .dsb button:focus-visible {
        outline: 3px solid rgba(99, 102, 241, .5);
        outline-offset: 3px;
        border-radius: 14px;
    }

    @media (prefers-reduced-motion: reduce) {
        .dsb * {
            transition: none !important;
        }
    }
</style>
