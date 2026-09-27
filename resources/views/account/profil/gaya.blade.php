{{-- Gaya halaman profil.

     Kelasnya berawalan .prf- supaya tidak bertabrakan dengan CSS Bootstrap
     milik layout admin, persis seperti .dsb- di dasbor. Peubah warna, radius,
     dan bayangannya sengaja disalin dari dasbor supaya dua halaman ini terasa
     satu keluarga.

     Tiga kelas lama — .form-control-modern, .btn-modern, dan .btn-gradient —
     tetap dipertahankan (dengan rupa baru) karena dipakai juga oleh komponen
     Livewire di tab PIN dan Keamanan. --}}
@push('gaya')
<style>
    .prf {
        --prf-tinta: #0f172a;
        --prf-tinta-2: #475569;
        --prf-tinta-3: #64748b;
        --prf-tinta-4: #94a3b8;
        --prf-garis: #e2e8f0;
        --prf-kartu: rgba(255, 255, 255, .92);
        --prf-radius: 22px;
        --prf-bayang: 0 18px 38px rgba(15, 23, 42, .06);
        --prf-ungu: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        --prf-hijau: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        --prf-merah: linear-gradient(135deg, #f43f5e 0%, #fb7185 100%);
        --prf-biru: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
        --prf-kuning: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
        --prf-jingga: linear-gradient(135deg, #f97316 0%, #fb923c 100%);

        font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
        color: var(--prf-tinta-2);
        display: grid;
        gap: 22px;
    }

    .prf *,
    .prf *::before,
    .prf *::after {
        box-sizing: border-box;
    }

    /* ------------------------------------------------------------- kepala */

    .prf-kepala {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        background:
            radial-gradient(520px 200px at 0% 0%, rgba(99, 102, 241, .07), transparent 70%),
            var(--prf-kartu);
        border: 1px solid rgba(255, 255, 255, .8);
        border-radius: var(--prf-radius);
        box-shadow: var(--prf-bayang);
        padding: 18px 22px;
    }

    .prf-kepala-avatar {
        width: 50px;
        height: 50px;
        flex: 0 0 50px;
        border-radius: 17px;
        overflow: hidden;
        background: var(--prf-ungu);
        box-shadow: 0 12px 22px -14px #6366f1;
    }

    .prf-kepala-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .prf-kepala-teks {
        flex: 1 1 260px;
        min-width: 0;
    }

    .prf-judul {
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

    .prf-sub {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        margin: 4px 0 0;
        font-size: .82rem;
        color: var(--prf-tinta-3);
    }

    .prf-sub i {
        font-size: 12px;
        color: #a5b4fc;
        margin-right: 5px;
    }

    .prf-pemisah {
        color: #cbd5e1;
    }

    .prf-kepala-aksi {
        display: flex;
        align-items: center;
        gap: 9px;
        flex-wrap: wrap;
        margin-left: auto;
    }

    /* -------------------------------------------------------- tata letak */

    /*
     * Kolom kiri diberi lebar tetap supaya kartu identitas tidak melar di
     * layar lebar, sedangkan kolom kanan memakai minmax(0, 1fr) agar isinya
     * yang panjang (tabel riwayat masuk) tidak mendorong kisi melebar.
     */
    .prf-tata {
        display: grid;
        grid-template-columns: minmax(0, 340px) minmax(0, 1fr);
        gap: 22px;
        align-items: start;
    }

    .prf-sisi {
        display: grid;
        gap: 22px;
        position: sticky;
        top: 92px;
    }

    .prf-utama {
        min-width: 0;
    }

    /* --------------------------------------------------------- kartu umum */

    .prf-kartu {
        background: var(--prf-kartu);
        border: 1px solid rgba(255, 255, 255, .8);
        border-radius: var(--prf-radius);
        box-shadow: var(--prf-bayang);
        padding: 22px 24px;
    }

    .prf-kartu-kepala {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }

    .prf-kartu-judul {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: var(--prf-tinta);
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .prf-kartu-sub {
        margin: 4px 0 0;
        font-size: .8rem;
        color: var(--prf-tinta-3);
    }

    /* ----------------------------------------------------- medali & warna */

    /* Lencana ikon: ikonnya betul-betul di tengah lewat grid + place-items,
       bukan padding kira-kira, jadi tidak pernah miring di ukuran mana pun. */
    .prf-medali {
        display: grid;
        place-items: center;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        border-radius: 15px;
        background: var(--warna, var(--prf-ungu));
        color: #fff;
        font-size: 1.02rem;
        box-shadow: 0 12px 22px -12px rgba(15, 23, 42, .6);
    }

    .prf-medali.kecil {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        border-radius: 13px;
        font-size: .88rem;
    }

    .prf-ungu { --warna: var(--prf-ungu); }
    .prf-hijau { --warna: var(--prf-hijau); }
    .prf-merah { --warna: var(--prf-merah); }
    .prf-biru { --warna: var(--prf-biru); }
    .prf-kuning { --warna: var(--prf-kuning); }
    .prf-jingga { --warna: var(--prf-jingga); }

    .prf-ikon-ungu { color: #6366f1; }
    .prf-ikon-hijau { color: #10b981; }
    .prf-ikon-merah { color: #f43f5e; }
    .prf-ikon-biru { color: #0ea5e9; }
    .prf-ikon-kuning { color: #f59e0b; }
    .prf-ikon-jingga { color: #f97316; }

    .prf-pil {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 11px;
        border-radius: 10px;
        font-size: .7rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .prf-pil i {
        font-size: 10px;
    }

    .prf-pil-hijau { color: #047857; background: #d1fae5; }
    .prf-pil-abu { color: #64748b; background: #f1f5f9; }
    .prf-pil-biru { color: #0369a1; background: #e0f2fe; }
    .prf-pil-kuning { color: #92400e; background: #fef3c7; }
    .prf-pil-merah { color: #be123c; background: #ffe4e6; }
    .prf-pil-ungu { color: #4338ca; background: #e0e7ff; }

    /* ------------------------------------------------------ kartu identitas */

    .prf-identitas {
        text-align: center;
        display: grid;
        justify-items: center;
        gap: 0;
    }

    .prf-foto-bingkai {
        position: relative;
        width: 124px;
        height: 124px;
        display: grid;
        place-items: center;
        border-radius: 38px;
        background: var(--prf-ungu);
        padding: 4px;
        box-shadow: 0 18px 32px -18px #6366f1;
    }

    .prf-foto {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 34px;
        border: 4px solid #fff;
        background: #fff;
        display: block;
    }

    /* Lencana terverifikasi menempel di sudut foto. */
    .prf-foto-lencana {
        position: absolute;
        right: -4px;
        bottom: -4px;
        display: grid;
        place-items: center;
        width: 34px;
        height: 34px;
        border-radius: 12px;
        border: 3px solid #fff;
        color: #fff;
        font-size: .72rem;
        background: var(--warna, var(--prf-hijau));
    }

    .prf-nama {
        margin: 16px 0 0;
        font-size: 1.08rem;
        font-weight: 800;
        color: var(--prf-tinta);
        letter-spacing: -.02em;
        line-height: 1.3;
    }

    .prf-nama-pengguna {
        margin: 3px 0 12px;
        font-size: .8rem;
        color: var(--prf-tinta-4);
        font-weight: 600;
    }

    .prf-pil-baris {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 7px;
        margin-bottom: 18px;
    }

    /* Tiga angka ringkas di bawah nama. Kisi 3 kolom tetap supaya lebarnya
       sama rata dan tidak ada sisa ruang di kanan. */
    .prf-mini-kisi {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 9px;
        width: 100%;
        margin-bottom: 18px;
    }

    .prf-mini {
        padding: 11px 6px;
        border-radius: 15px;
        background: #f8fafc;
        border: 1px solid var(--prf-garis);
    }

    .prf-mini-ikon {
        font-size: .82rem;
        margin-bottom: 5px;
        display: block;
    }

    .prf-mini-angka {
        margin: 0;
        font-size: .82rem;
        font-weight: 800;
        color: var(--prf-tinta);
        line-height: 1.25;
    }

    .prf-mini-label {
        margin: 2px 0 0;
        font-size: .62rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--prf-tinta-4);
    }

    /* ------------------------------------------------------- unggah foto */

    .prf-unggah-bungkus {
        width: 100%;
        display: grid;
        gap: 10px;
        padding-top: 18px;
        border-top: 1px dashed var(--prf-garis);
    }

    /* Input berkas bawaan disembunyikan tapi tetap ada di urutan Tab:
       labelnya yang terlihat, dan ia tetap mengaktifkan input lewat for=. */
    .prf-berkas {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .prf-unggah {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 12px 14px;
        border-radius: 16px;
        border: 1.5px dashed #c7d2fe;
        background: #f8faff;
        cursor: pointer;
        text-align: left;
        margin: 0;
        transition: border-color .25s ease, background .25s ease;
    }

    .prf-unggah:hover,
    .prf-berkas:focus-visible + .prf-unggah {
        border-color: #6366f1;
        background: #eef2ff;
    }

    .prf-unggah-teks {
        min-width: 0;
        flex: 1 1 auto;
    }

    .prf-unggah-nama {
        margin: 0;
        font-size: .78rem;
        font-weight: 700;
        color: var(--prf-tinta);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .prf-bantuan {
        margin: 0;
        font-size: .7rem;
        color: var(--prf-tinta-4);
        font-weight: 600;
    }

    /* ---------------------------------------------------- daftar kontak */

    .prf-daftar {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 10px;
    }

    .prf-baris {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 12px 14px;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid var(--prf-garis);
        transition: border-color .25s ease, background .25s ease;
    }

    .prf-baris:hover {
        border-color: #c7d2fe;
        background: #fff;
    }

    .prf-baris-teks {
        min-width: 0;
        flex: 1 1 auto;
    }

    .prf-label {
        margin: 0 0 3px;
        font-size: .66rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--prf-tinta-4);
    }

    .prf-nilai {
        margin: 0;
        font-size: .85rem;
        font-weight: 700;
        color: var(--prf-tinta);
        overflow-wrap: anywhere;
        line-height: 1.35;
    }

    /* Tombol pensil di ujung baris. */
    .prf-ubah {
        display: grid;
        place-items: center;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        border: 1px solid var(--prf-garis);
        border-radius: 12px;
        background: #fff;
        color: #6366f1;
        font-size: .72rem;
        cursor: pointer;
        transition: all .25s ease;
    }

    .prf-ubah:hover {
        background: #6366f1;
        border-color: #6366f1;
        color: #fff;
        transform: translateY(-2px);
    }

    /* ------------------------------------------------------------- tab */

    .prf-tab-kartu {
        background: var(--prf-kartu);
        border: 1px solid rgba(255, 255, 255, .8);
        border-radius: var(--prf-radius);
        box-shadow: var(--prf-bayang);
        overflow: hidden;
    }

    .prf-tab-kepala {
        padding: 20px 22px 0;
    }

    .prf-tab {
        display: flex;
        gap: 6px;
        list-style: none;
        margin: 0 0 20px;
        padding: 6px;
        background: #f1f5f9;
        border: 1px solid var(--prf-garis);
        border-radius: 18px;
    }

    .prf-tab > li {
        flex: 1 1 0;
        min-width: 0;
    }

    /*
     * Bootstrap memberi .nav-link warna & padding sendiri, dan .nav-pills
     * .active memakai warna primer bawaan tema. Keduanya ditimpa di sini
     * supaya pil aktifnya memakai gradien yang sama dengan tombol dasbor.
     */
    .prf-tab .nav-link {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 11px 10px;
        border-radius: 14px;
        font-size: .82rem;
        font-weight: 700;
        color: var(--prf-tinta-3);
        background: transparent;
        white-space: nowrap;
        transition: all .25s ease;
    }

    .prf-tab .nav-link i {
        font-size: 13px;
    }

    .prf-tab .nav-link:hover {
        color: #4f46e5;
        background: #fff;
    }

    .prf-tab .nav-link.active {
        background: var(--prf-ungu);
        color: #fff;
        box-shadow: 0 10px 20px -12px #6366f1;
    }

    /* Ikon berwarna ikut jadi putih saat pilnya aktif. */
    .prf-tab .nav-link.active i {
        color: #fff !important;
    }

    .prf-tab-isi {
        padding: 0 22px 22px;
    }

    /* --------------------------------------------------------- bagian isian */

    .prf-bagian + .prf-bagian,
    .prf-bagian-lanjut {
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px dashed var(--prf-garis);
    }

    .prf-bagian-kepala {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .prf-bagian-judul {
        margin: 0;
        font-size: .92rem;
        font-weight: 800;
        color: var(--prf-tinta);
        letter-spacing: -.01em;
    }

    .prf-bagian-sub {
        margin: 2px 0 0;
        font-size: .76rem;
        color: var(--prf-tinta-3);
    }

    /*
     * auto-fit + minmax membuat isian menata dirinya sendiri: tiga kolom di
     * layar lebar, dua di tablet, satu di ponsel — tanpa kelas col-md-* dan
     * tanpa titik putus yang harus dijaga satu per satu.
     */
    .prf-kisi-isian {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 16px;
    }

    .prf-kisi-isian.dua {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }

    .prf-isian-penuh {
        grid-column: 1 / -1;
    }

    .prf-isian {
        display: grid;
        gap: 7px;
        min-width: 0;
        /* Tanpa ini, isian di kolom tanpa teks bantuan ikut meregang setinggi
           kolom sebelahnya yang punya teks bantuan. */
        align-content: start;
    }

    .prf-label-isian {
        margin: 0;
        font-size: .7rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--prf-tinta-3);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .prf-label-isian i {
        font-size: 10px;
    }

    .prf-isian-bantuan {
        margin: 0;
        font-size: .72rem;
        color: var(--prf-tinta-4);
    }

    /* Rupa dasar semua isian — juga dipakai komponen Livewire PIN & Keamanan. */
    .form-control-modern {
        width: 100%;
        height: auto;
        padding: 12px 16px;
        border: 1.5px solid var(--prf-garis);
        border-radius: 14px;
        background: #f8fafc;
        font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
        font-size: .88rem;
        font-weight: 600;
        color: #0f172a;
        transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
    }

    .form-control-modern::placeholder {
        color: #cbd5e1;
        font-weight: 600;
    }

    .form-control-modern:focus {
        border-color: #6366f1;
        background: #fff;
        outline: none;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, .12);
    }

    .form-control-modern.is-invalid {
        border-color: #f43f5e;
        background: #fff1f2;
    }

    select.form-control-modern {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2394a3b8'%3E%3Cpath d='M4.5 6.5 8 10l3.5-3.5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 16px;
        padding-right: 38px;
    }

    input[type="date"].form-control-modern {
        appearance: none;
        -webkit-appearance: none;
        display: block;
        width: 100%;
        min-height: 46px;
    }

    /* Isian terkunci: rupanya sengaja dibedakan supaya pengguna tahu itu
       bukan kolom yang sedang gagal, melainkan memang tidak bisa diubah. */
    .prf-terkunci {
        position: relative;
    }

    .prf-terkunci .form-control-modern {
        background: #f1f5f9;
        border-color: #e2e8f0;
        border-style: dashed;
        color: #64748b;
        cursor: not-allowed;
    }

    /* Nilai yang hanya dibaca (status, level, jenis akun) ditampilkan sebagai
       kartu kecil, bukan <select disabled>: isinya tetap terbaca jelas dan
       tidak menyamar jadi isian yang bisa disunting. */
    .prf-statis {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 14px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid var(--prf-garis);
        min-height: 46px;
    }

    .prf-statis-teks {
        margin: 0;
        font-size: .85rem;
        font-weight: 700;
        color: var(--prf-tinta);
        overflow-wrap: anywhere;
    }

    /* ---------------------------------------------------------- tombol */

    .prf-tombol,
    .btn-modern {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 18px;
        border: 0;
        border-radius: 14px;
        font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
        font-size: .85rem;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: transform .25s ease, box-shadow .25s ease, background .25s ease;
    }

    .prf-tombol-ungu,
    .btn-gradient {
        background: var(--prf-ungu, linear-gradient(135deg, #6366f1 0%, #a855f7 100%));
        color: #fff !important;
        box-shadow: 0 10px 20px -12px #6366f1;
    }

    .prf-tombol-ungu:hover:not(:disabled),
    .btn-gradient:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 16px 26px -14px #6366f1;
        color: #fff !important;
    }

    .prf-tombol-biru {
        background: var(--prf-biru);
        color: #fff !important;
        box-shadow: 0 10px 20px -12px #0ea5e9;
    }

    .prf-tombol-biru:hover {
        transform: translateY(-2px);
        color: #fff !important;
    }

    .prf-tombol:disabled,
    .btn-modern:disabled {
        background: #f1f5f9 !important;
        color: #94a3b8 !important;
        box-shadow: none;
        cursor: not-allowed;
        transform: none;
    }

    .prf-penuh {
        width: 100%;
    }

    /* Baris tombol simpan menempel di dasar kartu saat isian digulir. */
    .prf-aksi {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px dashed var(--prf-garis);
    }

    .prf-aksi-catatan {
        margin: 0;
        font-size: .74rem;
        color: var(--prf-tinta-4);
        flex: 1 1 180px;
    }

    /* ---------------------------------------------------------- kabar */

    .prf-kabar {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 14px 16px;
        border-radius: 16px;
        margin-bottom: 20px;
        border: 1px solid #fde68a;
        background: #fffbeb;
    }

    .prf-kabar-teks {
        margin: 0;
        font-size: .82rem;
        font-weight: 600;
        color: #78350f;
        flex: 1 1 auto;
    }

    /* ----------------------------------------------------------- modal */

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
        padding: 28px;
        border-radius: 24px;
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
        color: #94a3b8;
        cursor: pointer;
        transition: all .2s ease;
    }

    .custom-popup-close:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .prf-modal-kepala {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-right: 34px;
    }

    .prf-modal-judul {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: var(--prf-tinta);
    }

    .prf-modal-sub {
        margin: 2px 0 0;
        font-size: .76rem;
        color: var(--prf-tinta-3);
    }

    /* Isian kode verifikasi: satu baris enam angka berjarak lebar. */
    .prf-kode {
        text-align: center;
        font-size: 1.4rem !important;
        font-weight: 800 !important;
        letter-spacing: .5em;
        text-indent: .5em;
        padding: 14px !important;
    }

    /* ------------------------------------------------------ kata sandi */

    .prf-sandi {
        position: relative;
    }

    .prf-sandi .form-control-modern {
        padding-right: 44px;
    }

    .password-toggle-inside {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #94a3b8;
        transition: color .2s ease;
        z-index: 3;
        font-size: 13px;
        padding: 5px;
    }

    .password-toggle-inside:hover {
        color: #6366f1;
    }

    /* Tab PIN & Keamanan memakai kelas Bootstrap dari komponen Livewire;
       ini merapikan jaraknya supaya sejajar dengan tab lain. */
    .prf-tab-isi .tab-pane > div:first-child {
        margin-top: 0;
    }

    .prf-tab-isi .alert {
        border-radius: 16px;
        border: 1px solid var(--prf-garis);
    }

    .prf-tab-isi .table {
        font-size: .82rem;
    }

    /* ------------------------------------------------ fokus papan ketik */

    .prf a:focus-visible,
    .prf button:focus-visible,
    .prf [tabindex]:focus-visible,
    .prf input:focus-visible,
    .prf select:focus-visible {
        outline: 3px solid rgba(99, 102, 241, .5);
        outline-offset: 3px;
        border-radius: 14px;
    }

    /* --------------------------------------------------------- responsif */

    /* Tablet: kartu identitas berhenti menempel supaya tidak memakan tinggi
       layar, dan dua kolom berubah jadi satu. */
    @media (max-width: 1100px) {
        .prf-tata {
            grid-template-columns: minmax(0, 1fr);
        }

        .prf-sisi {
            position: static;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .prf {
            gap: 16px;
        }

        .prf-tata,
        .prf-sisi {
            gap: 16px;
        }

        .prf-kartu {
            padding: 18px;
            border-radius: 18px;
        }

        .prf-tab-kepala {
            padding: 16px 16px 0;
        }

        .prf-tab-isi {
            padding: 0 16px 18px;
        }

        /* Empat tab tidak muat berjajar di ponsel; jadikan satu baris yang
           bisa digeser, bukan empat baris bertumpuk setinggi 226px. */
        .prf-tab {
            flex-wrap: nowrap;
            overflow-x: auto;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x proximity;
            border-radius: 16px;
        }

        .prf-tab::-webkit-scrollbar {
            display: none;
        }

        .prf-tab > li {
            flex: 0 0 auto;
            scroll-snap-align: start;
        }

        .prf-tab .nav-link {
            padding: 10px 14px;
            font-size: .78rem;
        }

        .prf-kepala {
            padding: 16px;
            border-radius: 18px;
        }

        .prf-kepala-aksi {
            margin-left: 0;
            width: 100%;
        }

        .prf-kisi-isian,
        .prf-kisi-isian.dua {
            grid-template-columns: minmax(0, 1fr);
        }

        .prf-aksi .prf-tombol {
            width: 100%;
        }
    }

    @media (max-width: 400px) {
        .prf-mini-kisi {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        /* Kotak ketiga mengisi sisa baris supaya tidak ada ruang menganga. */
        .prf-mini-kisi > .prf-mini:last-child {
            grid-column: 1 / -1;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .prf * {
            transition: none !important;
        }
    }
</style>
@endpush
