@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Tarif Layanan | MIS
@stop

{{-- Pemberitahuan lewat toast bersama, bukan kotak alert Bootstrap. --}}
@include('partials.toast-flash')

@push('gaya')
<style>
    /*
     * Bahasa rupanya mengikuti docs/panduan-ui-mis.md — ubin ikon .mis-medali,
     * lencana .mis-pil, tombol .mis-tombol, kartu .mis-bagian.
     */

    .tar-kepala {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        column-gap: 12px; row-gap: 2px; align-items: center;
        margin-bottom: 12px;
    }

    .tar-kepala > .mis-medali { grid-row: 1 / span 2; align-self: center; }
    .tar-kepala-judul { grid-column: 2; grid-row: 1; margin: 0; line-height: 1.25; font-size: .92rem; font-weight: 800; color: var(--mis-tinta); }
    .tar-kepala-sub { grid-column: 2; grid-row: 2; margin: 0; line-height: 1.45; font-size: .75rem; color: var(--mis-tinta-3); }

    /* ------------------------------------------------- kisi kartu layanan */

    .tar-kisi {
        display: grid;
        /*
         * min(100%, 290px), bukan 290px telanjang: minmax dengan lebar tetap
         * TIDAK pernah menyusut di bawah angka itu, jadi di layar 320px
         * (ruang terpakai ~258px) kolomnya meluber keluar induknya. Terukur
         * pada kisi jadwal sebelum diperbaiki.
         */
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 290px), 1fr));
        gap: var(--mis-jarak);
        margin-bottom: var(--mis-jarak);
        /*
         * stretch (bawaan), bukan start. Borangnya sekarang terbuka di
         * dialog, jadi tidak ada lagi kartu yang tiba-tiba jadi dua kali
         * lebih tinggi dan menarik seisi barisnya. Yang tersisa cuma selisih
         * jumlah fasilitas, dan itu justru lebih rapi kalau dasarnya rata:
         * tombolnya sejajar di seluruh baris.
         */
    }

    .tar-kartu {
        display: flex;
        flex-direction: column;
        padding: 18px;
        border: 1px solid var(--mis-garis);
        border-radius: var(--mis-radius);
        background: #fff;
        box-shadow: var(--mis-bayang);
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .tar-kartu:hover { border-color: #c7d2fe; box-shadow: 0 10px 24px -18px rgba(99, 102, 241, .9); }

    /* Kartu ajakan menambah layanan. Bertepi putus-putus dan tanpa bayangan
       supaya jelas ia bukan data, melainkan tindakan. */
    .tar-tambah {
        display: grid; place-content: center; justify-items: center;
        gap: 9px; min-height: 180px; padding: 24px;
        border: 2px dashed var(--mis-garis); border-radius: var(--mis-radius);
        background: transparent;
        color: var(--mis-tinta-3); text-align: center;
        cursor: pointer;
        transition: all .2s ease;
    }

    .tar-tambah:hover { border-color: #a5b4fc; background: #f5f3ff; color: #4f46e5; }
    .tar-tambah-judul { display: block; line-height: 1.3; font-size: .86rem; font-weight: 800; }
    .tar-tambah-ket { display: block; line-height: 1.45; font-size: .74rem; max-width: 24ch; }
    .tar-tambah .mis-medali { margin-bottom: 2px; }

    /* Yang belum punya tarif ditandai, bukan disembunyikan: layanan yang
       terlewat harus kelihatan justru karena terlewat. */
    .tar-kartu.kosong { border-color: #fde68a; background: linear-gradient(180deg, #fffbeb 0%, #fff 60%); }
    .tar-kartu.kosong:hover { border-color: #fcd34d; }

    .tar-kartu-kepala {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        column-gap: 11px; row-gap: 4px; align-items: center;
    }

    .tar-kartu-kepala > .mis-medali { grid-row: 1 / span 2; align-self: center; }
    .tar-atur { grid-column: 3; grid-row: 1 / span 2; align-self: center; }

    .tar-nama {
        grid-column: 2; grid-row: 1; margin: 0;
        line-height: 1.25; font-size: .88rem; font-weight: 800; color: var(--mis-tinta);
        overflow-wrap: anywhere;
    }

    .tar-lencana { grid-column: 2; grid-row: 2; display: flex; flex-wrap: wrap; gap: 5px; }

    /* ----------------------------------------------------------- harga */

    .tar-harga {
        display: flex; flex-wrap: wrap; align-items: baseline; gap: 7px;
        margin: 15px 0 0;
    }

    .tar-angka {
        line-height: 1.05;
        font-size: 1.45rem; font-weight: 800; color: var(--mis-tinta);
        overflow-wrap: anywhere;
    }

    .tar-kartu.kosong .tar-angka { font-size: 1.15rem; color: #b45309; }

    .tar-satuan { line-height: 1.4; font-size: .74rem; color: var(--mis-tinta-3); }

    /* Dibayar pelanggan hanya muncul kalau PPN-nya memang dikenakan; baris
       "PPN 0%" tidak memberi tahu apa pun. */
    .tar-total {
        display: flex; align-items: baseline; justify-content: space-between; gap: 10px;
        margin-top: 9px; padding: 7px 10px;
        border-radius: 9px; background: #ecfdf5;
        font-size: .74rem; color: #047857;
    }

    .tar-total strong { font-size: .84rem; font-weight: 800; }

    /* ------------------------------------------------------- fasilitas */

    .tar-fasilitas {
        margin: 13px 0 0; padding: 0; list-style: none;
        display: grid; gap: 5px;
    }

    .tar-fasilitas li {
        display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 7px;
        line-height: 1.4; font-size: .76rem; color: var(--mis-tinta-2);
    }

    .tar-fasilitas .fas { font-size: 10px !important; color: #10b981; margin-top: 3px; }
    .tar-fasilitas .tar-lebih { display: none; }


    /* Daftar dipotong supaya kartunya tetap sepadan; selengkapnya ada di
       dialog. Empat butir cukup untuk tahu ini layanan yang mana. */
    .tar-sisa {
        margin: 6px 0 0; line-height: 1.4;
        font-size: .73rem; font-weight: 700; color: #6d28d9;
    }

    .tar-kosong-teks { margin: 13px 0 0; line-height: 1.45; font-size: .76rem; color: var(--mis-tinta-4); }

    /* ------------------------------------------------------------ kaki */

    /* margin-top: auto mendorong tombolnya ke dasar kartu, jadi seluruh baris
       tombolnya sejajar walau jumlah fasilitasnya berbeda-beda. */
    .tar-kaki { margin-top: auto; padding-top: 15px; }

    .tar-kaki .mis-tombol { width: 100%; }

    .tar-tanda-cetakan {
        display: flex; align-items: center; gap: 6px;
        margin: 0 0 9px;
        line-height: 1.4; font-size: .72rem; color: var(--mis-tinta-4);
    }

    .tar-tanda-cetakan .fas { font-size: 11px !important; }

    /* --------------------------------------------------------- dialog */

    /*
     * Borangnya di dialog, bukan terlipat di dalam kartunya.
     *
     * Sebelumnya tujuh kartu memuat tujuh borang; membuka satu menarik seisi
     * barisnya jadi dua kali lebih tinggi — terukur 294px jadi 590px — dan
     * kartu di sebelahnya menyisakan petak putih hampir 300px. Di dialog,
     * kisinya tidak bergerak sama sekali, dan borangnya dapat ruang yang cukup
     * untuk tarif, fasilitas, kegiatan, kontak, dan cetakan sekaligus.
     */
    .tar-dialog {
        width: min(620px, calc(100vw - 32px));
        max-height: calc(100vh - 48px);
        padding: 0;
        border: none; border-radius: 20px;
        background: #fff;
        box-shadow: 0 32px 64px -24px rgba(15, 23, 42, .35);
        overflow: hidden;
    }

    .tar-dialog::backdrop { background: rgba(15, 23, 42, .45); backdrop-filter: blur(2px); }

    .tar-dialog-borang { display: flex; flex-direction: column; max-height: calc(100vh - 48px); }

    .tar-dialog-kepala {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 12px; align-items: center;
        padding: 16px 18px;
        border-bottom: 1px solid var(--mis-garis);
    }

    .tar-dialog-judul { margin: 0; line-height: 1.25; font-size: .95rem; font-weight: 800; color: var(--mis-tinta); }
    .tar-dialog-sub { margin: 2px 0 0; line-height: 1.4; font-size: .74rem; color: var(--mis-tinta-3); }

    .tar-tutup {
        display: grid; place-items: center;
        width: 34px; height: 34px;
        border: 1px solid var(--mis-garis); border-radius: 10px;
        background: #fff; color: var(--mis-tinta-3);
        cursor: pointer;
    }

    .tar-tutup:hover { border-color: #fecaca; background: #fef2f2; color: #e11d48; }
    .tar-tutup .fas { font-size: 14px !important; }

    .tar-dialog-isi {
        display: grid; gap: 13px;
        padding: 18px;
        overflow-y: auto;
        /* Wadah gulirnya menyatakan sendiri bahwa guliran berhenti di sini.
           Di mis-ui.css aturan ini sengaja TIDAK dipasang ke seluruh keturunan
           dialog — textarea yang isinya muat akan ikut menahan guliran. */
        overscroll-behavior: contain;
    }

    .tar-dialog-kaki {
        display: flex; flex-wrap: wrap; gap: 9px;
        padding: 14px 18px;
        border-top: 1px solid var(--mis-garis);
        background: #f8fafc;
    }

    .tar-dua { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr); gap: 11px; }

    /* Awalan "Rp" dan akhiran "%" menempel di dalam kotaknya: keduanya satuan,
       bukan keterangan yang perlu labelnya sendiri. */
    .tar-isian { position: relative; }
    .tar-isian .form-control-modern { padding-left: 40px; }
    .tar-isian.persen .form-control-modern { padding-left: 14px; padding-right: 36px; }

    .tar-tanda {
        position: absolute; bottom: 0;
        display: grid; place-items: center;
        width: 30px; height: 42px;
        font-size: .82rem; font-weight: 700; color: var(--mis-tinta-4);
        pointer-events: none;
    }

    .tar-tanda.kiri { left: 8px; }
    .tar-tanda.kanan { right: 6px; }

    .tar-area {
        /* width 100% WAJIB: lebar bawaan textarea datang dari atribut cols
           (20 aksara), bukan dari induknya. */
        width: 100%;
        min-height: 92px; padding: 10px 13px;
        border: 1px solid var(--mis-garis); border-radius: 11px;
        background: #fff;
        line-height: 1.5; font-size: .82rem; color: var(--mis-tinta);
        resize: vertical;
    }

    .tar-area:focus { outline: none; border-color: #a5b4fc; box-shadow: 0 0 0 3px rgba(99, 102, 241, .5); }

    .tar-pendek { min-height: 62px; }
    .tar-panjang { min-height: 210px; font-family: ui-monospace, "SF Mono", Menlo, monospace; font-size: .75rem; }

    .tar-cetakan > summary {
        display: flex; align-items: center; gap: 8px;
        padding: 9px 12px;
        border: 1px dashed var(--mis-garis); border-radius: 11px;
        background: #f8fafc;
        font-size: .78rem; font-weight: 700; color: var(--mis-tinta-2);
        cursor: pointer; list-style: none;
    }

    .tar-cetakan > summary::-webkit-details-marker { display: none; }
    .tar-cetakan[open] > summary { margin-bottom: 10px; }

    .tar-penanda {
        margin: 8px 0 0; padding: 10px 12px; list-style: none;
        display: grid; gap: 5px;
        border-radius: 11px; background: #f8fafc;
    }

    .tar-penanda li {
        display: grid;
        grid-template-columns: minmax(0, auto) minmax(0, 1fr);
        gap: 8px; align-items: baseline;
        line-height: 1.45; font-size: .72rem; color: var(--mis-tinta-3);
    }

    .tar-penanda code {
        padding: 1px 6px; border-radius: 6px;
        background: #ede9fe; color: #6d28d9;
        font-size: .72rem; white-space: nowrap;
    }

    .tar-wajib { color: #e11d48; font-weight: 800; }
    .tar-opsional { font-weight: 600; color: var(--mis-tinta-4); text-transform: none; }

    .lyn-pratinjau {
        display: flex; align-items: center; gap: 10px;
        margin-top: 9px; padding: 9px 11px;
        border-radius: 11px; background: #f8fafc;
        font-size: .75rem; color: var(--mis-tinta-3);
    }

    .lyn-aktif {
        display: grid; grid-template-columns: auto minmax(0, 1fr);
        gap: 10px; align-items: start;
        margin: 0; padding: 11px 13px;
        border: 1px solid var(--mis-garis); border-radius: 12px;
        line-height: 1.5; font-size: .79rem; font-weight: 700; color: var(--mis-tinta-2);
        cursor: pointer;
    }

    .lyn-aktif input { margin-top: 3px; }
    .lyn-aktif-ket { display: block; margin-top: 3px; font-size: .73rem; font-weight: 400; color: var(--mis-tinta-3); }

    /* Saringan kartu */
    .tar-saring {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px; align-items: center;
        margin-bottom: 12px;
    }

    /* Pembungkus isian: inilah acuan posisi ikon dan tombol kosongkan. */
    .tar-saring-kotak { position: relative; display: block; }

    .tar-saring .form-control-modern { width: 100%; padding-left: 38px; padding-right: 40px; }

    /* Silang bawaan peramban dimatikan; kalau tidak ada DUA tombol kosongkan
       berdampingan di Chrome, dan yang bawaan tidak ikut memicu penyaringan. */
    .tar-saring input[type="search"]::-webkit-search-cancel-button { display: none; }

    .tar-saring-ikon {
        position: absolute; left: 0; top: 0; bottom: 0;
        display: grid; place-items: center;
        width: 38px;
        color: var(--mis-tinta-4); pointer-events: none;
    }

    .tar-saring-ikon .fas { font-size: 13px !important; }

    .tar-saring-bersih {
        position: absolute; right: 5px; top: 50%;
        transform: translateY(-50%);
        display: grid; place-items: center;
        width: 30px; height: 30px;
        border: none; border-radius: 8px;
        background: transparent; color: var(--mis-tinta-4);
        cursor: pointer;
    }

    .tar-saring-bersih:hover { background: #fef2f2; color: #e11d48; }
    .tar-saring-bersih .fas { font-size: 12px !important; }
    .tar-saring-hasil { font-size: .75rem; color: var(--mis-tinta-3); white-space: nowrap; }

    /* Ringkasan yang bisa ditekan tetap serupa lencana, hanya dapat penunjuk. */
    .tar-pil-tombol { border: none; cursor: pointer; font: inherit; }
    .tar-pil-tombol:hover { filter: brightness(.96); }

    /* Kartu yang disorot sesudah lompat dari ringkasan. */
    .tar-kartu.disorot {
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, .35);
    }

    .tar-kosong-kotak {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 9px; align-items: start;
        margin-top: 13px; padding: 11px 12px;
        border: 1px dashed var(--mis-garis); border-radius: 11px;
        line-height: 1.45; font-size: .75rem; color: var(--mis-tinta-4);
    }

    .tar-kosong-kotak .fas { font-size: 12px !important; margin-top: 2px; }

    .tar-jadwal {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 7px; align-items: center;
        margin: 10px 0 0; padding: 7px 10px;
        border-radius: 9px; background: #fffbeb;
        line-height: 1.4; font-size: .74rem; color: #b45309;
        text-decoration: none;
    }

    .tar-jadwal:hover { background: #fef3c7; color: #92400e; text-decoration: none; }
    .tar-jadwal .fas { font-size: 11px !important; }
    .tar-jadwal strong { font-weight: 800; }

    /* Bagian kenaikan terjadwal */
    .tar-jadwal-kisi {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr));
        gap: 10px;
    }

    .tar-jadwal-butir {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px; align-items: center;
        padding: 11px 13px;
        border: 1px solid #fde68a; border-radius: 12px;
        background: #fffbeb;
    }

    .tar-jadwal-nama { display: block; line-height: 1.3; font-size: .78rem; font-weight: 700; color: #92400e; }
    .tar-jadwal-nilai { display: block; line-height: 1.35; font-size: .86rem; font-weight: 800; color: var(--mis-tinta); }
    .tar-jadwal-oleh { display: block; margin-top: 2px; line-height: 1.4; font-size: .71rem; color: var(--mis-tinta-4); }

    .tar-kosong-semua { grid-column: 1 / -1; }

    /* Tawaran nilai lazim di sebelah labelnya. */
    .tar-tawar {
        margin-left: 6px; padding: 1px 7px;
        border: 1px solid #c7d2fe; border-radius: 999px;
        background: #eef2ff;
        font-size: .66rem; font-weight: 700; color: #4f46e5;
        text-transform: none; letter-spacing: 0;
        cursor: pointer;
    }

    .tar-tawar:hover { background: #e0e7ff; }

    /* Layanan nonaktif */
    .tar-nonaktif-kisi {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr));
        gap: 10px;
    }

    .tar-nonaktif-butir {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 10px; align-items: center;
        padding: 10px 12px;
        border: 1px solid var(--mis-garis); border-radius: 12px;
        background: #f8fafc;
    }

    .tar-nonaktif-nama { display: block; line-height: 1.3; font-size: .82rem; font-weight: 700; color: var(--mis-tinta-2); }
    .tar-nonaktif-ket { display: block; line-height: 1.4; font-size: .72rem; color: var(--mis-tinta-4); }

    .tar-saring-riwayat {
        display: grid; grid-template-columns: minmax(0, 220px); gap: 4px;
        margin-bottom: 12px;
    }

    .tar-salin {
        display: flex; align-items: center; gap: 8px;
        width: 100%; padding: 9px 12px;
        border: 1px dashed #c7d2fe; border-radius: 11px;
        background: #f5f3ff;
        font-size: .78rem; font-weight: 700; color: #4f46e5;
        cursor: pointer; text-align: left;
    }

    .tar-salin:hover { background: #ede9fe; }

    .tar-pratinjau {
        display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
        padding: 9px 11px; border-radius: 10px; background: #f5f3ff;
        font-size: .76rem; color: var(--mis-tinta-3);
    }

    /* ----------------------------------------------------------- riwayat */

    .tar-riwayat-nilai { margin: 0; line-height: 1.25; font-size: .86rem; font-weight: 700; color: var(--mis-tinta); }
    .tar-riwayat-ket { margin: 1px 0 0; line-height: 1.4; font-size: .73rem; color: var(--mis-tinta-3); }
    .tar-aksi { display: flex; align-items: center; justify-content: flex-end; gap: 6px; }

    /* --------------------------------------------------------- responsif */

    /*
     * Penunjuk kasar (jari) butuh sasaran lebih besar; 34px terlalu kecil dan
     * letaknya di pojok kartu, tempat ibu jari paling sering meleset.
     *
     * Lebar ponsel ikut disebut, bukan mengandalkan pointer: coarse saja —
     * ciri itu tidak selalu dilaporkan peramban (emulasi DevTools pun tidak
     * menyetelnya), jadi aturan yang cuma bergantung padanya bisa tidak
     * pernah aktif tanpa ada yang tahu.
     */
    @media (pointer: coarse), (max-width: 767.98px) {
        .tar-atur { width: 44px; height: 44px; }
    }

    /*
     * Tidak ada lagi gaya @media print di sini. Daftar harganya diunduh
     * sebagai PDF lewat rute tersendiri, memakai cetakan yang sama dengan
     * ekspor Data Pelanggan — lengkap dengan logo, kepala berulang, dan kaki.
     *
     * Dua jalur cetak dengan hasil berbeda adalah persis masalah "dua pintu,
     * dua aturan" yang berkali-kali ditutup di layar ini.
     */

    @media (max-width: 767.98px) {
        .tar-kisi { grid-template-columns: minmax(0, 1fr); }
        .tar-saring { grid-template-columns: minmax(0, 1fr); }

        /* Lencana ringkasan selebar kartunya dan rata tengah. Rata kiri, ia
           menggantung sendirian di bawah tombol cetak yang selebar penuh. */
        .mis-kepala-aksi > .mis-pil,
        .mis-kepala-aksi > .tar-pil-tombol {
            width: 100%;
            justify-content: center;
        }

        .tar-saring-hasil { text-align: center; }
        .tar-saring-riwayat { grid-template-columns: minmax(0, 1fr); }
        .tar-dua { grid-template-columns: minmax(0, 1fr); }
        .tar-angka { font-size: 1.35rem; }

        .tar-dialog {
            width: 100vw; max-width: 100vw; max-height: 100vh;
            margin: 0; border-radius: 0;
        }

        .tar-dialog-borang { max-height: 100vh; }
        .tar-dialog-kaki > .mis-tombol { flex: 1 1 auto; }

        /* Tiap baris riwayat jadi kartu tersendiri, seperti daftar pelanggan. */
        .mis-tabel-kartu.tar-tabel tbody { display: flex; flex-direction: column; gap: 8px; }
        .mis-tabel-kartu.tar-tabel tbody tr { border: 1px solid var(--mis-garis); border-radius: 13px; background: #f8fafc; }

        /* Tujuh keterangan berlabel per kartu terukur 242px — terlalu tinggi
           untuk daftar yang dibaca sambil menggulung. Dua yang paling jarang
           ditindaklanjuti disembunyikan; keduanya tetap ada di layar lebar. */
        .mis-tabel-kartu.tar-tabel td[data-judul="PPN"],
        .mis-tabel-kartu.tar-tabel td[data-judul="Dipakai"] { display: none; }
    }
</style>
@endpush

@section('content')
<div class="main-content mis-badan">
    <section class="section">

        {{-- ------------------------------------------------ kepala --}}
        <div class="mis-kepala">
            <span class="mis-medali mis-hijau" aria-hidden="true"><i class="fas fa-money-bill-wave"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Tarif layanan</h1>
                <p class="mis-sub">Harga, fasilitas, dan cetakan deskripsi seluruh layanan jasa — disetel sekali di sini.</p>
            </div>
            <div class="mis-kepala-aksi">
                @if ($totalKartu > 0)
                    <a href="{{ route('account.Clinik-Scopus-Biaya-Persesi.cetak') }}"
                        class="mis-tombol mis-tombol-halus tar-cetak">
                        <i class="fas fa-file-pdf"></i> Unduh daftar harga
                    </a>
                @endif

                {{-- Bisa ditekan kalau memang ada yang terlewat: memberi tahu ada
                     yang kurang tanpa mengantar ke sana cuma setengah pekerjaan. --}}
                @if ($totalKartu === 0)
                    {{-- Nol bukan kabar baik. "Semua 0 tarif sudah disetel" dengan
                         centang hijau adalah kalimat gembira untuk keadaan yang justru
                         paling perlu diperhatikan. --}}
                    <span class="mis-pil mis-pil-abu">
                        <i class="fas fa-exclamation-circle"></i>
                        Belum ada layanan aktif
                    </span>
                @elseif ($adaTarif === $totalKartu)
                    <span class="mis-pil mis-pil-hijau">
                        <i class="fas fa-check-circle"></i>
                        Semua {{ $totalKartu }} tarif sudah disetel
                    </span>
                @else
                    <button type="button" class="mis-pil mis-pil-kuning tar-pil-tombol" data-lompat-kosong>
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ $totalKartu - $adaTarif }} tarif belum disetel — lihat
                    </button>
                @endif
            </div>
        </div>

        {{-- Saringan cepat. Katalognya kini boleh tumbuh sendiri, jadi kisinya
             tidak lagi dijamin muat sekali lihat. Menyaring di peramban, bukan
             memuat ulang: daftarnya kecil dan jawabannya harus seketika. --}}
        <div class="tar-saring">
            {{-- Ikon dan tombol kosongkan diposisikan terhadap KOTAK ISIANNYA,
                 bukan terhadap seluruh baris saringan. Terhadap barisnya, di
                 ponsel barisnya menumpuk jadi dua (isian + keterangan hasil)
                 sehingga ikonnya ikut turun ke tengah keduanya. --}}
            <div class="tar-saring-kotak">
                <span class="tar-saring-ikon" aria-hidden="true"><i class="fas fa-search"></i></span>
                <input type="search" id="tar-cari" class="form-control-modern"
                    placeholder="Cari layanan — nama atau varian" aria-label="Cari layanan">
                <button type="button" class="tar-saring-bersih" id="tar-cari-bersih"
                    hidden aria-label="Kosongkan pencarian" title="Kosongkan pencarian">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <span class="tar-saring-hasil" id="tar-cari-hasil" aria-live="polite"></span>
        </div>

        {{-- --------------------------------------- kartu tiap layanan --}}
        <div class="tar-kisi">
            @foreach ($kartu as $k)
                @php($t = $k['tarif'])
                @php($fasilitas = $t ? $t->daftar_fasilitas : [])
                @php($jadwal = $terjadwal->first(fn ($j) => $j->layanan === $k['layanan'] && $j->varian === $k['varian']))
                <div class="tar-kartu {{ $t ? '' : 'kosong' }}"
                    data-cari="{{ Str::lower($k['nama'] . ' ' . $k['namaVarian']) }}">
                    <div class="tar-kartu-kepala">
                        <span class="mis-medali {{ $k['warna'] }}" aria-hidden="true">
                            <i class="fas {{ $k['ikon'] }}"></i>
                        </span>
                        <h2 class="tar-nama">{{ $k['nama'] }}</h2>
                        @if ($bolehUbah && $k['pengatur'])
                            <button type="button" class="mis-tombol mis-tombol-garis tar-atur"
                                title="Ubah layanan {{ $k['nama'] }}"
                                data-layanan="{{ json_encode([
                                    'alamat' => route('account.layanan.update', $k['pengatur']),
                                    'hapus' => route('account.layanan.destroy', $k['pengatur']),
                                    'nama' => $k['pengatur']->nama,
                                    'satuan' => $k['pengatur']->satuan,
                                    'ikon' => $k['pengatur']->ikon,
                                    'warna' => $k['pengatur']->warna,
                                    'aktif' => $k['pengatur']->aktif,
                                    'urutan' => $k['pengatur']->urutan,
                                    'varian' => implode("\n", array_values($k['pengatur']->varian_peta)),
                                    'terpakai' => $k['pengatur']->jumlah_tarif + $k['pengatur']->jumlah_angkatan,
                                ]) }}">
                                <i class="fas fa-cog"></i>
                            </button>
                        @endif

                        <div class="tar-lencana">
                            @if ($k['namaVarian'])
                                <span class="mis-pil mis-pil-ungu">
                                    <i class="fas fa-map-marker-alt"></i> {{ $k['namaVarian'] }}
                                </span>
                            @endif
                            @if ($t && $t->ppn_persen > 0)
                                <span class="mis-pil mis-pil-kuning">
                                    <i class="fas fa-percent"></i> PPN {{ $t->ppn_persen }}%
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="tar-harga">
                        <span class="tar-angka">{{ $t ? $t->tarif_terbaca : 'Belum disetel' }}</span>
                        <span class="tar-satuan">{{ $k['satuan'] }}</span>
                    </div>

                    @if ($t && $t->ppn_persen > 0)
                        <div class="tar-total">
                            <span>Dibayar pelanggan</span>
                            <strong>Rp {{ number_format($t->total_dibayar, 0, ',', '.') }}</strong>
                        </div>
                    @endif

                    @if ($fasilitas)
                        {{-- SELURUH fasilitas dirender; yang lebih dari empat
                             disembunyikan di layar lewat kelas, bukan dipotong dari
                             markahnya. Dipotong dari markah, ia ikut hilang bagi
                             pembaca layar dan penyalinan teks — bukan cuma bagi mata.
                             Daftar harga PDF mengambil datanya sendiri, jadi ia tidak
                             terpengaruh pemotongan ini. --}}
                        <ul class="tar-fasilitas">
                            @foreach ($fasilitas as $i => $f)
                                <li class="{{ $i >= 4 ? 'tar-lebih' : '' }}">
                                    <i class="fas fa-check" aria-hidden="true"></i>
                                    <span>{{ $f }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @if (count($fasilitas) > 4)
                            <p class="tar-sisa">+{{ count($fasilitas) - 4 }} fasilitas lainnya</p>
                        @endif
                    @else
                        {{-- Kotak bertepi putus-putus, bukan satu baris teks yang
                             menggantung: kartu yang fasilitasnya kosong terukur
                             menyisakan 56px petak putih, dan ruang itu lebih
                             berguna sebagai ajakan daripada sebagai lubang. --}}
                        <div class="tar-kosong-kotak">
                            <i class="fas fa-list-ul" aria-hidden="true"></i>
                            <span>
                                {{ $t
                                    ? 'Fasilitasnya belum diisi — pengumuman angkatan tidak akan menyebut apa pun.'
                                    : 'Tarifnya belum pernah disetel — borang angkatan menampilkan harga kosong.' }}
                            </span>
                        </div>
                    @endif

                    @if ($jadwal)
                        {{-- Bisa ditekan: di sinilah orang pertama kali melihat
                             jadwalnya, jadi di sinilah ia mencari cara mengubahnya. --}}
                        <a href="#tar-jadwal-bagian" class="tar-jadwal">
                            <i class="fas fa-clock" aria-hidden="true"></i>
                            <span>
                                Naik jadi <strong>{{ $jadwal->tarif_terbaca }}</strong> pada
                                {{ $jadwal->berlaku_mulai->locale('id')->translatedFormat('d F Y') }}
                            </span>
                            <i class="fas fa-angle-right" aria-hidden="true"></i>
                        </a>
                    @endif

                    <div class="tar-kaki">
                        @if ($t && $t->penginput_id)
                            <p class="tar-tanda-cetakan">
                                <i class="fas fa-user-edit mis-ikon-biru" aria-hidden="true"></i>
                                Disetel {{ optional($t->penginput)->full_name ?: 'pengguna yang sudah dihapus' }}
                            </p>
                        @endif

                        @if ($t)
                            <p class="tar-tanda-cetakan">
                                @if ($t->ada_cetakan)
                                    <i class="fas fa-check-circle mis-ikon-hijau" aria-hidden="true"></i>
                                    Cetakan deskripsi siap dipakai
                                @else
                                    <i class="fas fa-exclamation-circle mis-ikon-kuning" aria-hidden="true"></i>
                                    Cetakan deskripsi belum diisi
                                @endif
                            </p>
                        @endif

                        @if ($bolehUbah)
                            <button type="button" class="mis-tombol {{ $t ? 'mis-tombol-halus' : 'mis-tombol-ungu' }}"
                                data-setel="{{ json_encode([
                                    'layanan' => $k['layanan'],
                                    'varian' => $k['varian'],
                                    'nama' => $k['nama'] . ($k['namaVarian'] ? ' — ' . $k['namaVarian'] : ''),
                                    'satuan' => $k['satuan'],
                                    'id' => $t?->getKey(),
                                    'biaya' => $t ? (int) $t->biaya_persesi : null,
                                    'ppn' => $t && $t->ppn !== null ? $t->ppn_persen : null,
                                    'fasilitas' => implode("\n", $fasilitas),
                                    'kegiatan' => $t ? implode("\n", $t->daftar_kegiatan) : '',
                                    'kontak' => $t?->kontak ?? '',
                                    'cetakan' => $t?->template_deskripsi ?? '',
                                    // Varian lain dari layanan yang sama, untuk disalin.
                                    'saudara' => collect($kartu)
                                        ->filter(fn ($x) => $x['layanan'] === $k['layanan'] && $x['varian'] !== $k['varian'] && $x['tarif'])
                                        ->map(fn ($x) => [
                                            'nama' => $x['namaVarian'],
                                            'biaya' => (int) $x['tarif']->biaya_persesi,
                                            'ppn' => $x['tarif']->ppn !== null ? $x['tarif']->ppn_persen : null,
                                            'fasilitas' => implode("\n", $x['tarif']->daftar_fasilitas),
                                            'kegiatan' => implode("\n", $x['tarif']->daftar_kegiatan),
                                            'kontak' => $x['tarif']->kontak ?? '',
                                            'cetakan' => $x['tarif']->template_deskripsi ?? '',
                                        ])->values()->all(),
                                ]) }}">
                                <i class="fas {{ $t ? 'fa-edit' : 'fa-plus' }}"></i>
                                {{ $t ? 'Ubah tarif & fasilitas' : 'Setel tarif' }}
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach

            @if ($totalKartu === 0)
                <div class="mis-kosong tar-kosong-semua">
                    <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-money-bill-wave"></i></span>
                    <p class="mis-kosong-judul">Belum ada layanan yang dijual</p>
                    <p class="mis-kosong-teks">
                        {{ $nonaktif->isNotEmpty()
                            ? 'Semua layanan sedang dinonaktifkan. Aktifkan lagi di bawah, atau tambah yang baru.'
                            : 'Tambah layanan pertama, lalu setel tarifnya.' }}
                    </p>
                </div>
            @endif

            @if ($bolehUbah)
                <button type="button" class="tar-tambah" data-layanan-baru>
                    <span class="mis-medali mis-ungu" aria-hidden="true"><i class="fas fa-plus"></i></span>
                    <span class="tar-tambah-judul">Tambah layanan</span>
                    <span class="tar-tambah-ket">
                        Jenis jasa baru — misalnya sharing session eksklusif — beserta tarifnya.
                    </span>
                </button>
            @endif
        </div>

        {{-- ---------------------------------- tarif terjadwal --}}
        @if ($terjadwal->isNotEmpty())
            {{-- WAJIB punya bagiannya sendiri. Tabel riwayat hanya memuat baris
                 berstatus nonaktif, jadi tanpa ini tarif terjadwal tidak punya
                 baris di mana pun — kartunya mengumumkan kenaikan yang tidak
                 bisa dibatalkan oleh siapa pun. --}}
            <div class="mis-bagian tar-jadwal-bagian" id="tar-jadwal-bagian">
                <div class="tar-kepala">
                    <span class="mis-medali kecil mis-kuning" aria-hidden="true"><i class="fas fa-clock"></i></span>
                    <h2 class="tar-kepala-judul">Kenaikan yang sudah dijadwalkan</h2>
                    <p class="tar-kepala-sub">
                        Harga yang berlaku sekarang tidak berubah sampai tanggalnya tiba.
                        Dibatalkan di sini kalau rencananya berubah.
                    </p>
                </div>

                <div class="tar-jadwal-kisi">
                    @foreach ($terjadwal as $j)
                        <div class="tar-jadwal-butir">
                            <span class="tar-jadwal-teks">
                                <span class="tar-jadwal-nama">
                                    {{ $j->nama_layanan }}@if ($j->nama_varian) &middot; {{ $j->nama_varian }}@endif
                                </span>
                                <span class="tar-jadwal-nilai">
                                    {{ $j->tarif_terbaca }}
                                    mulai {{ $j->berlaku_mulai->locale('id')->translatedFormat('d F Y') }}
                                </span>
                                <span class="tar-jadwal-oleh">
                                    Dijadwalkan
                                    {{ $j->penginput_id ? (optional($j->penginput)->full_name ?: 'akun terhapus') : 'tanpa jejak' }}
                                    pada {{ optional($j->created_at)->locale('id')->translatedFormat('d M Y') }}
                                </span>
                            </span>

                            @if ($bolehUbah)
                                <button type="button" class="mis-tombol mis-tombol-hapus"
                                    data-hapus="{{ route('account.Clinik-Scopus-Biaya-Persesi.destroy', $j) }}"
                                    data-nilai="{{ $j->nama_layanan }} {{ $j->tarif_terbaca }}">
                                    <i class="fas fa-times"></i> Batalkan
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ------------------------------------ layanan nonaktif --}}
        @if ($nonaktif->isNotEmpty())
            {{-- WAJIB ditampilkan. Katalog hanya membaca yang aktif, jadi tanpa
                 bagian ini layanan yang dinonaktifkan lenyap dari layar dan tidak
                 ada cara mengaktifkannya lagi — padahal pesan penolakan hapus
                 justru menyarankan menonaktifkan. --}}
            <div class="mis-bagian tar-nonaktif">
                <div class="tar-kepala">
                    <span class="mis-medali kecil mis-abu" aria-hidden="true"><i class="fas fa-eye-slash"></i></span>
                    <h2 class="tar-kepala-judul">Layanan yang tidak dijual lagi</h2>
                    <p class="tar-kepala-sub">
                        Tersembunyi dari daftar tarif dan borang angkatan. Tarif serta
                        angkatannya tetap utuh, dan bisa diaktifkan lagi kapan saja.
                    </p>
                </div>

                <div class="tar-nonaktif-kisi">
                    @foreach ($nonaktif as $l)
                        <div class="tar-nonaktif-butir">
                            <span class="mis-medali kecil mis-abu" aria-hidden="true">
                                <i class="fas {{ $l->ikon }}"></i>
                            </span>
                            <span class="tar-nonaktif-teks">
                                <span class="tar-nonaktif-nama">{{ $l->nama }}</span>
                                <span class="tar-nonaktif-ket">
                                    {{ $l->jumlah_tarif }} tarif &middot; {{ $l->jumlah_angkatan }} angkatan
                                </span>
                            </span>

                            @if ($bolehUbah)
                                <button type="button" class="mis-tombol mis-tombol-halus"
                                    data-layanan="{{ json_encode([
                                        'alamat' => route('account.layanan.update', $l),
                                        'hapus' => route('account.layanan.destroy', $l),
                                        'nama' => $l->nama,
                                        'satuan' => $l->satuan,
                                        'ikon' => $l->ikon,
                                        'warna' => $l->warna,
                                        'aktif' => $l->aktif,
                                        'urutan' => $l->urutan,
                                        'varian' => implode("\n", array_values($l->varian_peta)),
                                        'terpakai' => $l->jumlah_tarif + $l->jumlah_angkatan,
                                    ]) }}">
                                    <i class="fas fa-redo"></i> Aktifkan lagi
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @unless ($bolehUbah)
            <p class="mis-bantuan" style="margin-bottom: var(--mis-jarak);">
                <i class="fas fa-lock mis-ikon-kuning"></i>
                Hanya administrator yang boleh mengubah tarif.
            </p>
        @endunless

        {{-- ----------------------------------------------- riwayat --}}
        <div class="mis-bagian">
            <div class="tar-kepala">
                <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-history"></i></span>
                <h2 class="tar-kepala-judul">Tarif sebelumnya</h2>
                <p class="tar-kepala-sub">
                    {{ $totalRiwayat }} tarif lama tersimpan — angkatan yang sudah berjalan
                    memakai harga yang berlaku saat itu.
                    @if ($saringRiwayat)
                        Sedang disaring; {{ $riwayat->total() }} di antaranya cocok.
                    @endif
                </p>
            </div>

            <form method="GET" class="tar-saring-riwayat">
                <label class="mis-label" for="tar-riwayat-layanan">Saring layanan</label>
                <select class="form-control-modern" id="tar-riwayat-layanan" name="riwayat"
                    onchange="this.form.submit()">
                    <option value="">Semua layanan</option>
                    @foreach (\App\Layanan::katalog() as $kode => $tentang)
                        <option value="{{ $kode }}" @selected($saringRiwayat === $kode)>{{ $tentang['nama'] }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="mis-tombol mis-tombol-halus">Saring</button></noscript>
            </form>

            @if ($riwayat->isEmpty())
                <div class="mis-kosong">
                    <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-history"></i></span>
                    <p class="mis-kosong-judul">Belum ada tarif lama</p>
                    <p class="mis-kosong-teks">Tarif yang diganti akan tersimpan di sini, lengkap dengan tanggalnya.</p>
                </div>
            @else
                <div class="mis-tabel-bungkus">
                    <table class="mis-tabel mis-tabel-kartu tar-tabel">
                        <thead>
                            <tr>
                                <th>Layanan</th>
                                <th>Tarif</th>
                                <th>PPN</th>
                                <th>Dipakai</th>
                                <th>Disetel</th>
                                <th>Oleh</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($riwayat as $item)
                                @php($tentang = \App\Layanan::katalog()[$item->layanan] ?? null)
                                <tr>
                                    <td class="mis-td-utama">
                                        <span class="mis-sel-utama">
                                            <span class="mis-medali kecil mis-abu" aria-hidden="true">
                                                <i class="fas {{ $tentang['ikon'] ?? 'fa-tag' }}"></i>
                                            </span>
                                            <span class="mis-sel-teks">
                                                <p class="tar-riwayat-nilai">{{ $item->nama_layanan }}</p>
                                                <p class="tar-riwayat-ket">{{ $item->nama_varian ?: $item->satuan }}</p>
                                            </span>
                                        </span>
                                    </td>

                                    <td data-judul="Tarif">
                                        <span class="tar-riwayat-nilai">{{ $item->tarif_terbaca }}</span>
                                    </td>

                                    <td data-judul="PPN">
                                        @if ($item->ppn_persen > 0)
                                            <span class="mis-pil mis-pil-kuning">
                                                <i class="fas fa-percent"></i> {{ $item->ppn_persen }}%
                                            </span>
                                        @else
                                            <span class="tar-riwayat-ket">Tanpa PPN</span>
                                        @endif
                                    </td>

                                    {{-- Hanya sesi Clinik Scopus yang menyimpan
                                         biaya_persesi_id. Angkatan layanan lain menyalin
                                         angkanya, tidak menunjuk barisnya — jadi untuk
                                         mereka jumlahnya BUKAN nol, melainkan tidak
                                         diketahui. Menulis "Belum dipakai" di sana adalah
                                         angka yang berbohong. --}}
                                    <td data-judul="Dipakai">
                                        @if (! $item->pemakaian_terhitung)
                                            <span class="tar-riwayat-ket"
                                                title="Angkatan menyalin harganya, tidak menunjuk baris tarif ini">
                                                tidak tertaut
                                            </span>
                                        @elseif ($item->clinik_scopus_count > 0)
                                            <span class="mis-pil mis-pil-biru" title="Jadi acuan harga sesi sebanyak ini">
                                                <i class="fas fa-link"></i> {{ $item->clinik_scopus_count }} sesi
                                            </span>
                                        @else
                                            <span class="tar-riwayat-ket">Belum dipakai</span>
                                        @endif
                                    </td>

                                    <td data-judul="Disetel">
                                        <span class="tar-riwayat-ket">
                                            {{ optional($item->updated_at)->locale('id')->translatedFormat('d M Y') ?: '—' }}
                                        </span>
                                    </td>

                                    <td data-judul="Oleh">
                                        <span class="tar-riwayat-ket">
                                            {{ $item->penginput_id
                                                ? (optional($item->penginput)->full_name ?: 'akun terhapus')
                                                : 'tidak tercatat' }}
                                        </span>
                                    </td>

                                    <td data-judul="Aksi">
                                        <div class="tar-aksi">
                                            @if ($bolehUbah)
                                                <button type="button" class="mis-tombol mis-tombol-garis"
                                                    title="Berlakukan lagi tarif ini"
                                                    data-berlaku="{{ route('account.Clinik-Scopus-Biaya-Persesi.berlakukan', $item) }}"
                                                    data-nilai="{{ $item->nama_layanan }} {{ $item->tarif_terbaca }}">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                                <button type="button" class="mis-tombol mis-tombol-bahaya"
                                                    title="Hapus dari riwayat"
                                                    data-hapus="{{ route('account.Clinik-Scopus-Biaya-Persesi.destroy', $item) }}"
                                                    data-nilai="{{ $item->nama_layanan }} {{ $item->tarif_terbaca }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @else
                                                <span class="tar-riwayat-ket">—</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $riwayat->links('vendor.pagination.bootstrap-4') }}
            @endif
        </div>

    </section>
</div>

@if ($bolehUbah)
    {{-- SATU borang untuk semua layanan, diisi saat dibuka. Tujuh borang di
         tujuh kartu berarti tujuh salinan markah yang sama di setiap muat
         halaman, dan tiap kartu jadi dua kali lebih tinggi saat dibuka. --}}
    <dialog class="tar-dialog" id="tar-dialog">
        <form method="POST" action="{{ route('account.Clinik-Scopus-Biaya-Persesi.simpan') }}"
            class="tar-dialog-borang" id="tar-borang">
            @csrf
            <input type="hidden" name="layanan" id="tar-f-layanan">
            <input type="hidden" name="varian" id="tar-f-varian">
            {{-- Diisi skrip saat angkanya TIDAK berubah: membetulkan salah ketik
                 tidak boleh meninggalkan jejak seolah harganya pernah naik. --}}
            <input type="hidden" name="perbaiki" id="tar-f-perbaiki" value="">

            <div class="tar-dialog-kepala">
                <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-coins"></i></span>
                <div>
                    <h2 class="tar-dialog-judul" id="tar-f-judul">Setel tarif</h2>
                    <p class="tar-dialog-sub" id="tar-f-sub"></p>
                </div>
                <button type="button" class="tar-tutup" id="tar-batal" aria-label="Tutup">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="tar-dialog-isi">
                {{-- Muncul hanya kalau layanannya punya varian lain yang sudah
                     terisi. Jawa dan luar Jawa isinya ~90% sama — kegiatan,
                     kontak, dan cetakannya identik — dan mengetiknya dua kali
                     adalah pekerjaan yang diciptakan sendiri. --}}
                <button type="button" class="tar-salin" id="tar-f-salin" hidden>
                    <i class="fas fa-copy mis-ikon-ungu" aria-hidden="true"></i>
                    <span id="tar-f-salin-teks">Salin dari varian lain</span>
                </button>

                <div class="tar-dua">
                    <div class="mis-isian tar-isian">
                        <label class="mis-label" for="tar-f-biaya">
                            Tarif <span class="tar-wajib" aria-hidden="true">*</span>
                        </label>
                        <span class="tar-tanda kiri" aria-hidden="true">Rp</span>
                        <input type="text" class="form-control-modern" id="tar-f-biaya" name="biaya_persesi"
                            inputmode="numeric" autocomplete="off" required placeholder="0">
                    </div>

                    <div class="mis-isian tar-isian persen">
                        <label class="mis-label" for="tar-f-ppn">
                            PPN
                            @if ($ppnLazim)
                                {{-- Ditawarkan, bukan disetel diam-diam: yang lupa mengisi
                                     PPN baru ketahuan saat ada yang menghitung tagihan. --}}
                                <button type="button" class="tar-tawar" data-ppn-lazim="{{ $ppnLazim }}">
                                    pakai {{ $ppnLazim }}%
                                </button>
                            @endif
                        </label>
                        <span class="tar-tanda kanan" aria-hidden="true">%</span>
                        <input type="number" class="form-control-modern" id="tar-f-ppn" name="ppn"
                            min="0" max="100" inputmode="numeric" placeholder="0">
                    </div>
                </div>

                <div class="tar-pratinjau" aria-live="polite">
                    <i class="fas fa-calculator mis-ikon-ungu" aria-hidden="true"></i>
                    <span id="tar-f-pratinjau">Pelanggan membayar <strong>—</strong></span>
                </div>

                <div class="mis-isian">
                    <label class="mis-label" for="tar-f-mulai">
                        Mulai berlaku <span class="tar-opsional">opsional</span>
                    </label>
                    <input type="date" class="form-control-modern" id="tar-f-mulai" name="berlaku_mulai">
                    <p class="mis-bantuan" id="tar-f-mulai-ket">
                        Kosongkan untuk berlaku sekarang juga. Diisi tanggal yang akan datang,
                        tarifnya <strong>menunggu</strong> — harga yang sekarang tidak berubah
                        sampai tanggal itu tiba.
                    </p>
                </div>

                <div class="mis-isian">
                    <label class="mis-label" for="tar-f-fasilitas">Fasilitas yang didapat</label>
                    <textarea class="tar-area" id="tar-f-fasilitas" name="fasilitas" rows="5"
                        placeholder="Tempel teks pengumuman di sini, atau ketik satu fasilitas per baris"></textarea>
                    <p class="mis-bantuan">
                        Boleh <strong>ditempel utuh</strong> dari teks pengumuman — yang diambil hanya
                        baris di bawah judul &ldquo;Fasilitas&rdquo;, nomornya dibuang sendiri.
                    </p>
                </div>

                <div class="mis-isian">
                    <label class="mis-label" for="tar-f-kegiatan">Kegiatan utama</label>
                    <textarea class="tar-area" id="tar-f-kegiatan" name="kegiatan" rows="4"
                        placeholder="Boleh ditempel utuh, atau satu kegiatan per baris"></textarea>
                    <p class="mis-bantuan">
                        Sama seperti fasilitas — yang diambil baris di bawah judul &ldquo;Kegiatan&rdquo;.
                    </p>
                </div>

                <div class="mis-isian">
                    <label class="mis-label" for="tar-f-kontak">Kontak panitia</label>
                    <textarea class="tar-area tar-pendek" id="tar-f-kontak" name="kontak" rows="2"
                        placeholder="&#128222; Kumala: 0889-8356-7819"></textarea>
                    <p class="mis-bantuan">Diubah di sini sekali, seluruh angkatan berikutnya ikut.</p>
                </div>

                <details class="tar-cetakan">
                    <summary>
                        <i class="fas fa-file-alt mis-ikon-biru" aria-hidden="true"></i>
                        Cetakan deskripsi angkatan
                        <span class="mis-pil mis-pil-abu" id="tar-f-tanda">belum diisi</span>
                    </summary>

                    <textarea class="tar-area tar-panjang" id="tar-f-cetakan" name="template_deskripsi"
                        rows="10" spellcheck="false"
                        placeholder="Teks pengumuman yang dipakai ulang tiap angkatan"></textarea>

                    <p class="mis-bantuan">
                        Ditulis sekali, dipakai semua angkatan. Bagian yang berganti tiap angkatan
                        cukup ditulis sebagai penanda di bawah ini — sistem yang mengisinya.
                    </p>

                    <ul class="tar-penanda">
                        @foreach (\App\Support\PerakitDeskripsi::PENANDA as $kode => $arti)
                            <li><code>{{ $kode }}</code> <span>{{ $arti }}</span></li>
                        @endforeach
                    </ul>
                </details>
            </div>

            <div class="tar-dialog-kaki">
                <button type="submit" class="mis-tombol mis-tombol-ungu" id="tar-f-simpan">
                    <i class="fas fa-save"></i> Berlakukan
                </button>
                <button type="button" class="mis-tombol mis-tombol-halus" data-tutup>Batal</button>
            </div>
        </form>
    </dialog>

    {{-- ---------------------------------------- dialog layanan --}}
    <dialog class="tar-dialog" id="lyn-dialog">
        <form method="POST" action="{{ route('account.layanan.store') }}"
            class="tar-dialog-borang" id="lyn-borang">
            @csrf

            <div class="tar-dialog-kepala">
                <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
                <div>
                    <h2 class="tar-dialog-judul" id="lyn-judul">Layanan baru</h2>
                    <p class="tar-dialog-sub" id="lyn-sub">Setelah disimpan, tarifnya disetel di kartu yang muncul.</p>
                </div>
                <button type="button" class="tar-tutup" data-tutup aria-label="Tutup">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="tar-dialog-isi">
                <div class="mis-isian">
                    <label class="mis-label" for="lyn-nama">
                        Nama layanan <span class="tar-wajib" aria-hidden="true">*</span>
                    </label>
                    <input type="text" class="form-control-modern" id="lyn-nama" name="nama" required
                        maxlength="120" placeholder="mis. Sharing Session Eksklusif">
                    <p class="mis-bantuan" id="lyn-kode-ket">
                        Kode internalnya dibuat otomatis dari nama ini dan tidak berubah lagi —
                        tarif serta angkatan menunjuk kode itu.
                    </p>
                </div>

                <div class="tar-dua">
                    <div class="mis-isian">
                        <label class="mis-label" for="lyn-satuan">
                            Satuan <span class="tar-wajib" aria-hidden="true">*</span>
                        </label>
                        <input type="text" class="form-control-modern" id="lyn-satuan" name="satuan" required
                            maxlength="60" placeholder="per peserta">
                        <p class="mis-bantuan">Tertulis di bawah harga: &ldquo;Rp 250.000 per peserta&rdquo;.</p>
                    </div>

                    <div class="mis-isian">
                        <label class="mis-label" for="lyn-warna">Warna ubin</label>
                        <select class="form-control-modern" id="lyn-warna" name="warna">
                            @foreach ($daftarWarna as $kode => $nama)
                                <option value="{{ $kode }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mis-isian">
                    <label class="mis-label" for="lyn-urutan">Urutan tampil</label>
                    <input type="number" class="form-control-modern" id="lyn-urutan" name="urutan"
                        min="0" max="9999" step="10" placeholder="10">
                    <p class="mis-bantuan">
                        Makin kecil makin depan. Kolomnya sudah ada sejak awal tetapi tidak
                        pernah punya tuasnya, jadi layanan baru selalu menempel di ujung.
                    </p>
                </div>

                <div class="mis-isian">
                    <label class="mis-label" for="lyn-ikon">Ikon</label>
                    <select class="form-control-modern" id="lyn-ikon" name="ikon">
                        @foreach ($daftarIkon as $kode => $arti)
                            <option value="{{ $kode }}">{{ $arti }}</option>
                        @endforeach
                    </select>

                    {{-- Ikonnya dipilih dari daftar tertutup, bukan diketik: proyek ini
                         memakai Font Awesome 5, dan nama FA6 tidak merender apa pun tanpa
                         galat sama sekali. --}}
                    <div class="lyn-pratinjau">
                        <span class="mis-medali" id="lyn-ubin" aria-hidden="true"><i class="fas fa-tag"></i></span>
                        <span>Begini tampilannya di kartu.</span>
                    </div>
                </div>

                <div class="mis-isian">
                    <label class="mis-label" for="lyn-varian">Varian <span class="tar-opsional">opsional</span></label>
                    <textarea class="tar-area tar-pendek" id="lyn-varian" name="varian" rows="3"
                        placeholder="Kosongkan kalau harganya satu saja&#10;Pulau Jawa&#10;Luar Pulau Jawa"></textarea>
                    <p class="mis-bantuan">
                        Satu varian per baris. Dipakai kalau <strong>harga atau fasilitasnya
                        berbeda</strong> di dalam satu layanan — tiap varian dapat kartu tarifnya
                        sendiri. Varian yang sudah dipakai tidak bisa dibuang.
                    </p>
                </div>

                <label class="lyn-aktif" for="lyn-aktif">
                    <input type="checkbox" id="lyn-aktif" name="aktif" value="1" checked>
                    <span>
                        Masih dijual
                        <span class="lyn-aktif-ket">
                            Dilepas centangnya, layanan ini hilang dari daftar tanpa menghapus
                            tarif maupun angkatan yang sudah ada.
                        </span>
                    </span>
                </label>
            </div>

            <div class="tar-dialog-kaki">
                <button type="submit" class="mis-tombol mis-tombol-ungu" id="lyn-simpan">
                    <i class="fas fa-save"></i> Simpan layanan
                </button>
                <button type="button" class="mis-tombol mis-tombol-halus" data-tutup>Batal</button>
                <button type="button" class="mis-tombol mis-tombol-hapus" id="lyn-hapus" hidden>
                    <i class="fas fa-trash-alt"></i> Hapus
                </button>
            </div>
        </form>
    </dialog>
@endif
@endsection

@push('scripts')
<script>
    /*
     * Saringan kartu. Dikerjakan di peramban: daftarnya kecil dan jawabannya
     * harus seketika, sementara memuat ulang halaman untuk menyaring tujuh
     * kartu terasa jauh lebih lambat daripada mengetiknya.
     */
    (function () {
        const cari = document.getElementById('tar-cari');
        if (!cari) return;

        const hasil = document.getElementById('tar-cari-hasil');
        const bersih = document.getElementById('tar-cari-bersih');
        const kartu = [...document.querySelectorAll('.tar-kartu')];
        const tambah = document.querySelector('.tar-tambah');

        function saring() {
            const kata = cari.value.trim().toLowerCase();
            let tampil = 0;

            kartu.forEach(function (k) {
                const cocok = kata === '' || (k.dataset.cari || '').includes(kata);
                k.hidden = ! cocok;
                if (cocok) tampil++;
            });

            // Kartu "Tambah layanan" ikut sembunyi saat menyaring: ia bukan
            // hasil pencarian, dan berdiri sendiri di tengah daftar kosong
            // terbaca seolah itulah yang ditemukan.
            if (tambah) tambah.hidden = kata !== '';

            hasil.textContent = kata === ''
                ? ''
                : (tampil === 0 ? 'Tidak ada yang cocok' : tampil + ' dari ' + kartu.length + ' layanan');

            // Tombol kosongkan hanya ada saat memang ada yang bisa dikosongkan.
            bersih.hidden = cari.value === '';
        }

        cari.addEventListener('input', saring);

        bersih.addEventListener('click', function () {
            cari.value = '';
            saring();
            cari.focus();
        });

        // Esc mengosongkan juga, kebiasaan yang sudah dipunyai orang dari
        // kotak pencarian mana pun.
        cari.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && cari.value !== '') {
                e.preventDefault();
                cari.value = '';
                saring();
            }
        });

        saring();
    })();

    /*
     * Ringkasan "N tarif belum disetel" mengantar ke kartunya, bukan cuma
     * memberi tahu. Disorot sebentar supaya jelas yang mana.
     */
    (function () {
        const pil = document.querySelector('[data-lompat-kosong]');
        if (!pil) return;

        pil.addEventListener('click', function () {
            const kosong = [...document.querySelectorAll('.tar-kartu.kosong')].filter((k) => !k.hidden);
            if (kosong.length === 0) return;

            kosong[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            kosong.forEach(function (k) {
                k.classList.add('disorot');
                setTimeout(function () { k.classList.remove('disorot'); }, 2600);
            });
        });
    })();

    @if ($bolehUbah)
    /*
     * Dialog tarif: satu borang yang diisi ulang tiap kali dibuka.
     */
    (function () {
        const dialog = document.getElementById('tar-dialog');
        if (!dialog) return;

        const el = (id) => document.getElementById(id);
        const rupiah = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');
        const angka = (v) => parseInt(String(v).replace(/\D+/g, ''), 10) || 0;

        const biaya = el('tar-f-biaya');
        const ppn = el('tar-f-ppn');
        const perbaiki = el('tar-f-perbaiki');
        const simpan = el('tar-f-simpan');
        const pratinjau = el('tar-f-pratinjau');

        let acuan = null;   // tarif yang berlaku saat dialog dibuka
        let idLama = null;
        let saudara = [];   // varian lain dari layanan yang sama

        function segarkan() {
            const dasar = angka(biaya.value);
            const persen = Math.min(100, Math.max(0, parseInt(ppn.value, 10) || 0));
            const pajak = Math.round(dasar * persen / 100);

            if (dasar < 1) {
                pratinjau.innerHTML = 'Pelanggan membayar <strong>—</strong>';
            } else if (persen > 0) {
                pratinjau.innerHTML = 'Pelanggan membayar <strong>' + rupiah(dasar + pajak) + '</strong> — '
                    + rupiah(dasar) + ' + PPN ' + persen + '% (' + rupiah(pajak) + ')';
            } else {
                pratinjau.innerHTML = 'Pelanggan membayar <strong>' + rupiah(dasar) + '</strong> — tanpa PPN';
            }

            /*
             * Tulisan tombolnya mengikuti apa yang sebenarnya akan terjadi.
             * Tarif yang sama persis tetapi PPN atau fasilitasnya berbeda tetap
             * dihitung perbaikan, bukan kenaikan harga.
             */
            const bertanggal = el('tar-f-mulai').value !== '';
            const sama = acuan !== null && dasar === acuan && ! bertanggal;

            perbaiki.value = sama ? (idLama || '') : '';
            simpan.innerHTML = bertanggal
                ? '<i class="fas fa-clock"></i> Jadwalkan'
                : (sama ? '<i class="fas fa-save"></i> Perbaiki' : '<i class="fas fa-save"></i> Berlakukan');
        }

        biaya.addEventListener('input', function () {
            const n = angka(biaya.value);
            biaya.value = n > 0 ? n.toLocaleString('id-ID') : '';
            segarkan();
        });

        ppn.addEventListener('input', segarkan);

        document.addEventListener('click', function (e) {
            const tawar = e.target.closest('[data-ppn-lazim]');
            if (!tawar) return;

            ppn.value = tawar.dataset.ppnLazim;
            ppn.dispatchEvent(new Event('input', { bubbles: true }));
            ppn.focus();
        });

        document.addEventListener('click', function (e) {
            const pemicu = e.target.closest('[data-setel]');
            if (!pemicu) return;

            const d = JSON.parse(pemicu.dataset.setel);

            el('tar-f-layanan').value = d.layanan;
            el('tar-f-varian').value = d.varian || '';
            el('tar-f-judul').textContent = d.nama;
            el('tar-f-sub').textContent = d.biaya === null
                ? 'Belum punya tarif — ' + d.satuan
                : 'Berlaku sekarang ' + rupiah(d.biaya) + ' ' + d.satuan;

            biaya.value = d.biaya === null ? '' : Number(d.biaya).toLocaleString('id-ID');
            ppn.value = d.ppn === null ? '' : d.ppn;
            el('tar-f-fasilitas').value = d.fasilitas;
            el('tar-f-kegiatan').value = d.kegiatan;
            el('tar-f-kontak').value = d.kontak;
            el('tar-f-cetakan').value = d.cetakan;

            const tanda = el('tar-f-tanda');
            tanda.textContent = d.cetakan.trim() !== '' ? 'sudah ada' : 'belum diisi';
            tanda.className = 'mis-pil ' + (d.cetakan.trim() !== '' ? 'mis-pil-hijau' : 'mis-pil-abu');

            acuan = d.biaya;
            idLama = d.id;
            saudara = d.saudara || [];

            // Tanggal mulai selalu dikosongkan saat dialog dibuka: menyetel
            // tarif baru jauh lebih sering daripada menjadwalkannya, dan
            // tanggal yang tertinggal dari pembukaan sebelumnya akan membuat
            // tarif menunggu tanpa diminta.
            el('tar-f-mulai').value = '';

            const salin = el('tar-f-salin');
            salin.hidden = saudara.length === 0;

            if (saudara.length > 0) {
                el('tar-f-salin-teks').textContent = 'Salin isi dari ' + saudara[0].nama;
            }

            segarkan();
            dialog.showModal();
            biaya.focus();
        });

        /*
         * Menyalin dari varian lain. Harganya SENGAJA tidak ikut: yang sama
         * antar varian itu fasilitas, kegiatan, kontak, dan cetakannya —
         * harganya justru alasan variannya ada.
         */
        el('tar-f-salin').addEventListener('click', function () {
            if (saudara.length === 0) return;

            const d = saudara[0];

            window.misKonfirmasi({
                judul: 'Salin isi dari varian lain?',
                pesan: 'Fasilitas, kegiatan, kontak, dan cetakan deskripsi diambil dari %s. '
                    + 'Tarif dan PPN tidak ikut — itu justru yang membedakan variannya.',
                sorot: d.nama,
                tombol: 'Ya, salin',
                jenis: 'tanya',
                glif: 'fa-copy',
            }).then(function (ya) {
                if (!ya) return;

                el('tar-f-fasilitas').value = d.fasilitas;
                el('tar-f-kegiatan').value = d.kegiatan;
                el('tar-f-kontak').value = d.kontak;
                el('tar-f-cetakan').value = d.cetakan;

                const tanda = el('tar-f-tanda');
                tanda.textContent = d.cetakan.trim() !== '' ? 'sudah ada' : 'belum diisi';
                tanda.className = 'mis-pil ' + (d.cetakan.trim() !== '' ? 'mis-pil-hijau' : 'mis-pil-abu');

                window.misToast('berhasil', 'Isi disalin dari ' + d.nama + '. Periksa dulu sebelum disimpan.');
            });
        });

        // Tombolnya berubah tulisan saat tanggal diisi: menekan "Berlakukan"
        // padahal yang terjadi menjadwalkan adalah kejutan yang bisa dihindari.
        el('tar-f-mulai').addEventListener('input', segarkan);

        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-tutup]') || e.target.closest('#tar-batal')) dialog.close();
        });

        // Menekan latar gelapnya ikut menutup, seperti yang orang harapkan.
        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) dialog.close();
        });
    })();

    /*
     * Dialog layanan: menambah jenis jasa baru, dan mengubah yang sudah ada.
     *
     * Satu borang untuk dua keadaan, dibedakan alamat kirimnya. Dua borang
     * terpisah berarti dua salinan daftar ikon dan warna yang sama.
     */
    (function () {
        const dialog = document.getElementById('lyn-dialog');
        if (!dialog) return;

        const el = (id) => document.getElementById(id);
        const borang = el('lyn-borang');
        const ikon = el('lyn-ikon');
        const warna = el('lyn-warna');
        const ubin = el('lyn-ubin');
        const hapus = el('lyn-hapus');
        const ALAMAT_BARU = borang.getAttribute('action');

        let alamatHapus = null;

        function segarkanUbin() {
            ubin.className = 'mis-medali ' + warna.value;
            ubin.innerHTML = '<i class="fas ' + ikon.value + '"></i>';
        }

        ikon.addEventListener('change', segarkanUbin);
        warna.addEventListener('change', segarkanUbin);

        function buka(d) {
            const baru = d === null;

            borang.setAttribute('action', baru ? ALAMAT_BARU : d.alamat);
            el('lyn-judul').textContent = baru ? 'Layanan baru' : 'Ubah ' + d.nama;
            el('lyn-sub').textContent = baru
                ? 'Setelah disimpan, tarifnya disetel di kartu yang muncul.'
                : (d.terpakai > 0
                    ? 'Dipakai ' + d.terpakai + ' tarif & angkatan — kodenya tidak bisa diubah.'
                    : 'Belum dipakai data mana pun.');

            el('lyn-nama').value = baru ? '' : d.nama;
            el('lyn-satuan').value = baru ? '' : d.satuan;
            el('lyn-varian').value = baru ? '' : d.varian;
            el('lyn-aktif').checked = baru ? true : d.aktif;
            ikon.value = baru ? 'fa-star' : d.ikon;
            warna.value = baru ? 'mis-ungu' : d.warna;
            el('lyn-urutan').value = baru ? '' : (d.urutan ?? '');

            // Nama yang sudah dipakai tetap boleh diubah — yang dikunci
            // kodenya, dan kode tidak ikut nama setelah dibuat.
            el('lyn-kode-ket').hidden = ! baru;

            // Menghapus hanya ditawarkan kalau memang belum dipakai apa pun.
            const bolehHapus = ! baru && d.terpakai === 0;
            hapus.hidden = ! bolehHapus;
            alamatHapus = bolehHapus ? d.hapus : null;

            segarkanUbin();
            dialog.showModal();
            el('lyn-nama').focus();
        }

        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-layanan-baru]')) { buka(null); return; }

            const atur = e.target.closest('[data-layanan]');
            if (atur) buka(JSON.parse(atur.dataset.layanan));
        });

        hapus.addEventListener('click', function () {
            if (!alamatHapus) return;

            window.misKonfirmasi({
                judul: 'Hapus layanan ini?',
                pesan: '%s hilang dari daftar layanan. Hanya bisa kalau belum dipakai tarif maupun angkatan.',
                sorot: el('lyn-nama').value,
                tombol: 'Ya, hapus',
                jenis: 'bahaya',
            }).then(function (ya) {
                if (!ya) return;

                fetch(alamatHapus, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                })
                    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                    .then(function (j) {
                        window.misToast(j.ok && j.d.success ? 'berhasil' : 'gagal', j.d.message);
                        if (j.ok && j.d.success) setTimeout(function () { window.location.reload(); }, 900);
                    })
                    .catch(function () { window.misToast('gagal', 'Tidak bisa menghubungi peladen.'); });
            });
        });

        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-tutup]') && dialog.open) dialog.close();
        });

        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) dialog.close();
        });
    })();

    @endif

    /*
     * Memberlakukan tarif lama dan menghapus baris riwayat.
     *
     * Satu penangan untuk seluruh tabel: alamatnya datang dari route() di
     * markah, bukan dirangkai di JavaScript — perubahan rute tidak diam-diam
     * merusak tombolnya.
     */
    (function () {
        const kirim = function (alamat, metode) {
            return fetch(alamat, {
                method: metode,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                .then(function (j) {
                    window.misToast(j.ok && j.d.success ? 'berhasil' : 'gagal',
                        j.d.message || 'Tindakannya gagal.');
                    return j.ok && j.d.success;
                })
                .catch(function () {
                    window.misToast('gagal', 'Tidak bisa menghubungi peladen.');
                    return false;
                });
        };

        document.addEventListener('click', function (e) {
            const pulih = e.target.closest('[data-berlaku]');

            if (pulih) {
                window.misKonfirmasi({
                    judul: 'Berlakukan lagi tarif ini?',
                    pesan: '%s akan dipakai untuk angkatan berikutnya. Tarif yang sekarang berhenti berlaku.',
                    sorot: pulih.dataset.nilai,
                    tombol: 'Ya, berlakukan',
                    jenis: 'tanya',
                    glif: 'fa-undo',
                }).then(function (ya) {
                    if (ya) kirim(pulih.dataset.berlaku, 'POST').then(function (baik) {
                        if (baik) setTimeout(function () { window.location.reload(); }, 900);
                    });
                });

                return;
            }

            const hapus = e.target.closest('[data-hapus]');
            if (!hapus) return;

            window.misKonfirmasi({
                judul: 'Hapus tarif ini dari riwayat?',
                pesan: 'Baris %s dihapus permanen. Tarif yang masih jadi acuan harga pesanan tidak bisa dihapus.',
                sorot: hapus.dataset.nilai,
                tombol: 'Ya, hapus',
                jenis: 'bahaya',
            }).then(function (ya) {
                if (ya) kirim(hapus.dataset.hapus, 'DELETE').then(function (baik) {
                    if (baik) hapus.closest('tr').remove();
                });
            });
        });
    })();
</script>
@endpush
