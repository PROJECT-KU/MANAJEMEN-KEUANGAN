@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Data Pelanggan | MIS
@stop

{{-- Pemberitahuan lewat toast bersama, bukan kotak alert Bootstrap. --}}
@include('partials.toast-flash')

@push('gaya')
<style>
    /*
     * Layar ini memakai bahasa rupa yang sama dengan halaman profil: kartu
     * .mis-bagian, ubin ikon .mis-medali, lencana .mis-pil, tombol .mis-tombol.
     * Yang ditulis di sini hanya yang khas layar ini.
     *
     * Versi sebelumnya menaruh 265 baris <style> beserta puluhan style sebaris
     * di dalam markahnya, dengan nama kelas sendiri (hero-glass, customer-card)
     * yang tidak dipakai layar lain. Akibatnya dua layar yang sama-sama daftar
     * data tampil berbeda, dan setiap perbaikan rupa harus dikerjakan dua kali.
     */

    /* ------------------------------------------------------- ringkasan */

    .pel-ringkas {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .pel-ubin {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border: 1px solid #e7ecf5;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }

    /* Ubin sekaligus tautan: warna dan garis bawah tautan dibuang, dan
       keadaan terpilihnya ditandai tepi beraksen. */
    a.pel-ubin {
        color: inherit;
        text-decoration: none;
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    a.pel-ubin:hover,
    a.pel-ubin:focus-visible {
        border-color: #c7d2fe;
        box-shadow: 0 6px 16px -10px rgba(79, 70, 229, .7);
        transform: translateY(-1px);
    }

    a.pel-ubin.terpilih {
        border-color: #6366f1;
        box-shadow: 0 0 0 1px #6366f1 inset;
    }

    /* Pengurut khusus ponsel; di layar lebar kepala kolomnya yang bekerja. */
    .pel-urut-ponsel {
        display: none;
    }

    /* Kolom centang: selebar kotaknya saja. */
    .pel-centang-sel {
        width: 34px;
        padding-right: 0 !important;
    }

    .pel-centang-sel input {
        width: 16px;
        height: 16px;
        accent-color: #4f46e5;
        cursor: pointer;
    }

    /*
     * Baris aksi massal muncul hanya saat ada yang dipilih.
     *
     * Melekat di bawah layar, bukan di atas daftar: yang mencentang baris
     * biasanya sedang menggulung ke bawah, dan tombol yang tertinggal di atas
     * menuntut ia menggulung balik hanya untuk menekannya.
     */
    .pel-massal {
        position: sticky;
        /*
         * 57px = tinggi kaki halaman (45px) + jarak 12px.
         *
         * Kaki halaman ber-position: fixed dengan z-index 1000, jadi pada
         * bottom: 12px baris ini terbenam di belakangnya: terukur, barisnya
         * menempati 822-888px sementara kakinya mulai di 855px, dan
         * elementFromPoint di titik tengah baris justru menunjuk kaki itu.
         * z-index-nya pun harus di atas 1000, bukan 5.
         */
        bottom: 57px;
        z-index: 1001;
        display: none;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 12px;
        padding: 11px 14px;
        border: 1px solid #c7d2fe;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 16px 36px -18px rgba(15, 23, 42, .45);
    }

    .pel-massal.tampil {
        display: flex;
    }

    .pel-massal-jumlah {
        margin: 0;
        margin-right: auto;
        font-size: .82rem;
        font-weight: 700;
        color: var(--mis-tinta);
    }

    /* Lencana yang bisa ditekan: tetap rupa lencana, tetapi jelas bisa dituju. */
    .pel-pil-tautan {
        text-decoration: none;
    }

    .pel-pil-tautan:hover,
    .pel-pil-tautan:focus-visible {
        filter: brightness(.94);
        text-decoration: none;
    }

    /* Alamat email yang tidak lengkap: ditandai, tidak disembunyikan. */
    .pel-email-rusak {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #92400e;
        text-decoration: underline dotted #f59e0b;
        text-underline-offset: 3px;
    }

    .pel-ubin-angka {
        margin: 0;
        line-height: 1.1;
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--mis-tinta);
    }

    .pel-ubin-label {
        margin: 2px 0 0;
        line-height: 1.4;
        font-size: .74rem;
        font-weight: 600;
        color: var(--mis-tinta-3);
    }

    /* ------------------------------------------------------- penyaring */

    /*
     * Penyaring berkartu, bukan mengambang di latar halaman.
     *
     * Ubin ringkasan di atasnya dan tabel di bawahnya sama-sama berkartu
     * putih; di antara keduanya, tiga kendali tanpa latar terbaca seperti
     * tercecer di luar susunan, bukan seperti satu perangkat yang utuh.
     */
    .pel-saring-kartu {
        padding: 14px 16px;
        margin-bottom: 14px;
        border: 1px solid #e7ecf5;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }

    .pel-saring {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 10px;
    }

    /*
     * Ketiga kendali berbagi baris dengan perbandingan tetap 2:1:1.
     *
     * Dua keadaan yang sama-sama salah pernah terjadi di sini. Tanpa batas
     * apa pun, kotak cari melahap seluruh sisa baris — sekitar 60% lebar
     * layar — sementara dua menu di sebelahnya tinggal 170px. Dengan batas
     * 420px, kebalikannya: terukur 347px kosong di 1470px dan 787px di
     * 1920px, jadi separuh kartunya melompong.
     *
     * Yang keliru bukan lebar mutlaknya melainkan perbandingannya. Dipatok
     * 2:1:1, ketiganya selalu memenuhi barisnya pada lebar berapa pun, dan
     * kotak cari — satu-satunya yang menerima tulisan bebas — tetap yang
     * terlebar tanpa jadi enam kali lipat menu di sebelahnya.
     */
    .pel-saring-cari {
        position: relative;
        flex: 2 1 260px;
        min-width: 0;
    }

    /* Tombol hapus di dalam kotak cari. */
    .pel-hapus {
        position: absolute;
        right: 10px;
        bottom: 9px;
        display: grid;
        place-items: center;
        width: 24px;
        height: 24px;
        padding: 0;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: var(--mis-tinta-4);
        cursor: pointer;
        transition: background .18s ease, color .18s ease;
    }

    .pel-hapus:hover {
        background: #eef2ff;
        color: #4f46e5;
    }

    .pel-hapus > .fas {
        /* Aturan global layout mengunci .fas ke 20px dengan bobot yang sama,
           jadi di sini perlu lebih kuat. */
        font-size: .78rem !important;
    }

    /* Isian diberi ruang di kanan supaya ketikan panjang tidak menyelinap
       ke bawah tombol hapus. */
    .pel-saring-cari .form-control-modern {
        padding-right: 40px;
    }

    /* Cincin berputar kecil di dalam kotak cari. Menggantikan tempat tombol
       hapus selagi permintaannya berjalan, bukan berdampingan dengannya —
       dua tanda di sudut yang sama hanya membingungkan. */
    .pel-sibuk {
        position: absolute;
        right: 15px;
        bottom: 13px;
        width: 15px;
        height: 15px;
        border: 2px solid #e2e8f0;
        border-top-color: #6366f1;
        border-radius: 50%;
        opacity: 0;
        pointer-events: none;
        transition: opacity .15s ease;
    }

    .pel-saring.sibuk .pel-sibuk {
        opacity: 1;
        animation: pel-putar .7s linear infinite;
    }

    .pel-saring.sibuk .pel-hapus {
        visibility: hidden;
    }

    @keyframes pel-putar {
        to { transform: rotate(360deg); }
    }

    /* Hasil diredupkan selagi diganti, supaya jelas angkanya sedang berubah
       — tanpa menghilangkannya, yang membuat halaman berkedip dan melompat. */
    #pel-hasil {
        transition: opacity .15s ease;
    }

    #pel-hasil.sibuk {
        opacity: .45;
    }

    @media (prefers-reduced-motion: reduce) {
        .pel-saring.sibuk .pel-sibuk {
            animation: none;
        }

        #pel-hasil {
            transition: none;
        }
    }

    .pel-saring-pilih {
        flex: 1 1 170px;
        min-width: 0;
    }

    /* Ringkasan pelipat hanya berguna di ponsel; di layar lebar penyaringnya
       memang selalu terbuka, jadi ringkasannya tidak perlu ada. */
    .pel-lipat > summary {
        display: none;
    }

    /* ---------------------------------------------------------- daftar */

    /* Sel nama: foto + nama + username dalam satu sel supaya barisnya tidak
       melebar oleh kolom yang isinya cuma satu kata. */
    .pel-orang {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 0;
    }

    /* Kepala kolom yang bisa diurutkan. Ikon panahnya samar sampai kolomnya
       dipakai, supaya tiga panah sekaligus tidak ramai di kepala tabel. */
    .pel-urut {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: inherit;
        text-decoration: none;
    }

    .pel-urut:hover {
        color: #4f46e5;
    }

    .pel-urut > .fas {
        font-size: .7rem !important;
        opacity: .35;
    }

    .pel-urut.aktif {
        color: #4f46e5;
    }

    .pel-urut.aktif > .fas {
        opacity: 1;
    }

    .pel-nama {
        margin: 0;
        line-height: 1.25;
        font-size: .84rem;
        font-weight: 700;
        color: var(--mis-tinta);
        overflow-wrap: anywhere;
    }

    .pel-akun {
        margin: 1px 0 0;
        line-height: 1.4;
        font-size: .72rem;
        color: var(--mis-tinta-3);
        overflow-wrap: anywhere;
    }

    .pel-kontak {
        display: flex;
        flex-direction: column;
        gap: 2px;
        font-size: .76rem;
        color: var(--mis-tinta-2);
    }

    .pel-kontak span,
    .pel-kontak a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        overflow-wrap: anywhere;
    }

    /* Tautannya tidak digarisbawahi dan tidak berwarna tautan: di dalam tabel,
       dua puluh empat tautan biru bergaris membuat kolomnya berisik. Warnanya
       baru muncul saat disentuh, jadi tetap ketahuan bisa ditekan. */
    .pel-kontak a {
        color: inherit;
        text-decoration: none;
    }

    .pel-kontak a:hover,
    .pel-kontak a:focus-visible {
        color: #4f46e5;
        text-decoration: underline;
    }

    /* Ikon sebaris ikut ukuran teksnya; aturan global layout mengunci .fas
       ke 20px dengan bobot yang sama, jadi di sini perlu lebih spesifik. */
    .pel-kontak span > .fas,
    .pel-kontak a > .fas,
    .pel-kontak a > .fab {
        width: 13px;
        font-size: .72rem !important;
        text-align: center;
    }

    .pel-aksi {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }

    /* ------------------------------------------------------- responsif */

    @media (max-width: 1100px) {
        .pel-ringkas {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    /*
     * Pita 768px: tabelnya masih tabel, tetapi tinggal sepuluh piksel lagi.
     *
     * Mode kartu menyala di bawah 768px (meski komentarnya di mis-ui.css
     * menyebut "ponsel"), jadi tepat di 768px — lebar iPad tegak — tabelnya
     * utuh dengan tujuh kolom. Terukur ia meluber 10px sesudah kolom centang
     * ditambahkan, dan 10px itu cukup membuat daftarnya harus digeser ke
     * samping. Bantalan selnya dirapatkan di pita ini; 6px per sisi dikali
     * tujuh kolom jauh lebih dari cukup.
     */
    @media (max-width: 991.98px) {
        .mis-tabel th,
        .mis-tabel td {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .pel-centang-sel {
            width: 28px;
            padding-left: 10px !important;
        }
    }

    @media (max-width: 767.98px) {
        /* Baru di sini pengurutnya berguna: di atas 768px kepala kolomnya
           masih kelihatan dan sudah melakukan tugas yang sama. */
        .pel-urut-ponsel {
            display: block;
        }

        /*
         * Di mode kartu, kotak centangnya duduk di sudut kanan atas kartu.
         * Sebagai baris berlabel sendiri ia menambah satu baris di tiap kartu
         * untuk sesuatu yang tidak perlu dibaca; di sudut, ia tetap terjangkau
         * jempol dan tidak mengganggu susunan kartunya.
         */
        .mis-tabel.mis-tabel-kartu tbody tr {
            position: relative;
        }

        .mis-tabel.mis-tabel-kartu tbody td.pel-centang-sel {
            position: absolute;
            top: 10px;
            right: 12px;
            width: auto;
            padding: 0 !important;
            justify-content: flex-end;
        }

        .mis-tabel.mis-tabel-kartu tbody td.pel-centang-sel::before {
            display: none;
        }

        /* Sel nama diberi ruang di kanan supaya nama panjang tidak menyelinap
           ke bawah kotak centangnya. */
        .mis-tabel.mis-tabel-kartu tbody td.mis-td-utama {
            padding-right: 28px;
        }

        /*
         * Baris aksi massal diangkat di atas bilah navigasi mengambang milik
         * cabang ponsel; pada posisi bawaannya ia tertutup penuh.
         */
        .pel-massal {
            bottom: 96px;
        }

        /*
         * Keempat tombolnya disusun dua-dua, bukan dibiarkan membungkus
         * sendiri.
         *
         * Lebar keempatnya berbeda-beda ("Batal" 110px, "Nonaktifkan" 160px),
         * jadi pembungkusan alaminya menghasilkan tiga tombol di baris pertama
         * dan dua di baris kedua — tepinya bergerigi dan tidak ada yang lurus.
         * Dalam kisi dua kolom, keempatnya sama lebar dan barisnya rata.
         */
        .pel-massal {
            display: grid;
            /* minmax(0, 1fr), bukan 1fr: 1fr tidak pernah menyusut di bawah
               isi terlebarnya, jadi kolom berisi "Nonaktifkan" merebut 144px
               sementara kolom "Batal" tinggal 119px — terukur di 320px. */
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .pel-massal.tampil {
            display: grid;
        }

        .pel-massal-jumlah {
            grid-column: 1 / -1;
            margin-right: 0;
        }

        .pel-massal .mis-tombol {
            justify-content: center;
        }
    }

    @media (max-width: 767.98px) {
        .pel-ringkas {
            gap: 10px;
        }

        .pel-ubin {
            padding: 12px 13px;
            border-radius: 14px;
        }

        .pel-ubin-angka {
            font-size: 1.15rem;
        }

        /*
         * Penyaring dilipat di ponsel.
         *
         * Tiga kendali yang selalu terbuka memakan satu layar penuh sebelum
         * baris pertama data kelihatan, padahal yang dicari orang justru
         * datanya. <details>/<summary> dipakai supaya tidak perlu JavaScript
         * dan tetap bisa dibuka pembaca layar.
         */
        .pel-lipat > summary {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 13px;
            border: 1px solid var(--mis-garis);
            border-radius: 12px;
            background: #fff;
            font-size: .8rem;
            font-weight: 700;
            color: var(--mis-tinta-2);
            cursor: pointer;
            list-style: none;
        }

        .pel-lipat > summary::-webkit-details-marker {
            display: none;
        }

        .pel-lipat[open] > summary {
            margin-bottom: 10px;
        }

        .pel-saring {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }

        .pel-saring-cari,
        .pel-saring-pilih {
            flex: 1 1 auto;
            /*
             * align-self: stretch menimpa .mis-isian, yang memasang
             * align-self: start.
             *
             * Dalam susunan menurun, sifat itu membuat tiap kendali selebar
             * isinya sendiri: terukur 220/144/172/183px pada layar 390px,
             * jadi keempatnya bertepi ragged dan 109px di kanan menganga.
             * Bukan akibat perubahan lebar di layar lebar — diperiksa pada
             * keadaan sebelumnya, angkanya sama persis.
             */
            align-self: stretch;
        }

        .pel-saring .mis-tombol {
            width: 100%;
        }
    }

    /* Tombol tidak ikut berbagi perbandingan: ia bukan penyaring, dan melar
       selebar sepertiga baris hanya membuatnya tampak seperti kendali ketiga. */
    .pel-saring > .mis-tombol {
        flex: 0 0 auto;
        align-self: flex-end;
    }

    @media (max-width: 575.98px) {
        /* Di mode kartu, sel nilai berada di kanan; deretan tombol ikut rata
           kanan supaya tepinya lurus dengan nilai baris lainnya. */
        .pel-aksi {
            justify-content: flex-end;
        }

        .pel-kontak {
            align-items: flex-end;
        }
    }
</style>
@endpush

@section('content')
<div class="main-content mis-badan">
    <section class="section">

        {{-- ------------------------------------------------ kepala --}}
        <div class="mis-kepala">
            <span class="mis-medali mis-ungu" aria-hidden="true"><i class="fas fa-users"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Data Pelanggan</h1>
                <p class="mis-sub">Orang luar yang memakai layanan jasa Rumah Scopus.</p>
            </div>
            <div class="mis-kepala-aksi">
                {{-- Ekspor membawa saringan yang sedang dipakai, bukan seluruh
                     tabel: yang diunduh orang hampir selalu yang dilihatnya. --}}
                {{-- Dua bentuk unduhan, bukan satu: PDF untuk dibaca dan
                     dilampirkan, lembar kerja untuk diolah. Daftar pelanggan
                     hampir selalu berakhir di spreadsheet. --}}
                <a class="mis-tombol mis-tombol-halus"
                    href="{{ route('account.customer.ekspor.excel', request()->only('cari', 'status', 'verifikasi')) }}">
                    <i class="fas fa-file-excel mis-ikon-hijau"></i> Unduh Excel
                </a>
                <a class="mis-tombol mis-tombol-halus"
                    href="{{ route('account.customer.ekspor', request()->only('cari', 'status', 'verifikasi')) }}">
                    <i class="fas fa-file-pdf mis-ikon-merah"></i> Unduh PDF
                </a>
            </div>
        </div>

        {{-- ---------------------------------------------- ringkasan --}}
        {{-- Empat angka yang paling sering ditanyakan, dihitung dari seluruh
             pelanggan — bukan dari halaman yang sedang tampil. --}}
        {{-- Tiap ubin sekaligus pintasan saringan.
             Angka yang menarik perhatian hampir selalu memancing pertanyaan
             "yang mana saja?", dan sebelum ini tidak ada cara menjawabnya:
             angkanya terlihat tetapi daftarnya tidak bisa dipersempit jadi
             orang-orang itu. Berupa TAUTAN, bukan tombol berskrip, jadi
             alamatnya bisa disalin dan tetap bekerja tanpa JavaScript. --}}
        <div class="pel-ringkas">
            <a class="pel-ubin {{ ! $status && ! $verifikasi && ! $punyaPesanan && ! $baru ? 'terpilih' : '' }}"
                href="{{ route('account.customer.index', request()->only('cari')) }}"
                title="Tampilkan semua pelanggan">
                <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-users"></i></span>
                <div>
                    <p class="pel-ubin-angka">{{ number_format($ringkasan['total']) }}</p>
                    <p class="pel-ubin-label">Seluruh pelanggan</p>
                </div>
            </a>
            <a class="pel-ubin {{ $status === 'aktif' ? 'terpilih' : '' }}"
                href="{{ route('account.customer.index', array_merge(request()->only('cari'), ['status' => 'aktif'])) }}"
                title="Saring: hanya akun aktif">
                <span class="mis-medali kecil mis-hijau" aria-hidden="true"><i class="fas fa-user-check"></i></span>
                <div>
                    <p class="pel-ubin-angka">{{ number_format($ringkasan['aktif']) }}</p>
                    <p class="pel-ubin-label">Akun aktif</p>
                </div>
            </a>
            {{-- Dulu "Email terverifikasi", dan itu angka mati: terukur 65 aktif
                 dan 65 terverifikasi, NOL yang berbeda ke salah satu arah,
                 sebab verifyEmail() menyetel keduanya sekaligus. Jumlah yang
                 pernah memesan memang berbeda (29 dari 102). --}}
            <a class="pel-ubin {{ $punyaPesanan ? 'terpilih' : '' }}"
                href="{{ route('account.customer.index', array_merge(request()->only('cari'), ['pesanan' => 'ada'])) }}"
                title="Saring: hanya yang pernah memesan">
                <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-receipt"></i></span>
                <div>
                    <p class="pel-ubin-angka">{{ number_format($ringkasan['memesan']) }}</p>
                    <p class="pel-ubin-label">Pernah memesan</p>
                </div>
            </a>
            <a class="pel-ubin {{ $baru ? 'terpilih' : '' }}"
                href="{{ route('account.customer.index', array_merge(request()->only('cari'), ['baru' => '30'])) }}"
                title="Saring: bergabung 30 hari terakhir">
                <span class="mis-medali kecil mis-jingga" aria-hidden="true"><i class="fas fa-user-plus"></i></span>
                <div>
                    <p class="pel-ubin-angka">{{ number_format($ringkasan['baru']) }}</p>
                    <p class="pel-ubin-label">Bergabung 30 hari terakhir</p>
                </div>
            </a>
        </div>

        {{-- ---------------------------------------------- penyaring --}}
        {{-- <details> membungkus penyaringnya, bukan berdiri sendiri: di
             ponsel tiga kendali yang selalu terbuka memakan satu layar penuh
             sebelum baris pertama data kelihatan. Di layar lebar ia dipaksa
             terbuka oleh skrip di bawah dan ringkasannya disembunyikan, jadi
             tampak seperti baris penyaring biasa. --}}
        <details class="pel-lipat" id="pel-penyaring">
            <summary>
                <i class="fas fa-sliders-h mis-ikon-ungu" aria-hidden="true"></i>
                Cari &amp; saring
                @if ($adaSaringan)
                    <span class="mis-pil mis-pil-ungu">aktif</span>
                @endif
            </summary>

        <div class="pel-saring-kartu">
        <form method="GET" action="{{ route('account.customer.index') }}" class="pel-saring" id="pel-borang">
            {{-- Urutan ikut terbawa saat menyaring; tanpa ini, menekan Terapkan
                 diam-diam mengembalikan urutannya ke bawaan. --}}
            <input type="hidden" name="urut" value="{{ $urut }}">
            <input type="hidden" name="arah" value="{{ $arah === 'asc' ? 'naik' : 'turun' }}">
            <div class="mis-isian pel-saring-cari">
                <label class="mis-label" for="pel-cari">Cari</label>
                <input type="search" class="form-control-modern" id="pel-cari" name="cari"
                    value="{{ $cari }}" placeholder="Nama, username, email, atau telepon"
                    autocomplete="off" aria-controls="pel-hasil">
                {{-- Tombol hapus ketikan. Diberi type=button supaya tidak
                     ikut mengirim formulir, dan disembunyikan saat kotaknya
                     kosong — tanda silang di kotak kosong tidak ada gunanya
                     dan hanya menambah satu hal untuk dipahami. --}}
                <button type="button" class="pel-hapus" id="pel-hapus"
                    aria-label="Hapus kata kunci pencarian" title="Hapus kata kunci"
                    @if ($cari === '') hidden @endif>
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
                <span class="pel-sibuk" id="pel-sibuk" aria-hidden="true"></span>
            </div>

            <div class="mis-isian pel-saring-pilih">
                <label class="mis-label" for="pel-status">Status akun</label>
                <select class="form-control-modern" id="pel-status" name="status">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected($status === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option>
                </select>
            </div>

            <div class="mis-isian pel-saring-pilih">
                <label class="mis-label" for="pel-verifikasi">Email</label>
                <select class="form-control-modern" id="pel-verifikasi" name="verifikasi">
                    <option value="">Semua email</option>
                    <option value="sudah" @selected($verifikasi === 'sudah')>Sudah diverifikasi</option>
                    <option value="belum" @selected($verifikasi === 'belum')>Belum diverifikasi</option>
                </select>
            </div>

            {{--
              Pengurut KHUSUS ponsel.

              Kepala kolom yang bisa diurutkan ada di dalam <thead>, dan di mode
              kartu <thead> disembunyikan untuk pembaca layar saja — terukur di
              390px ia berukuran 1x1 dan terklip. Akibatnya keempat kolom yang
              bisa diurutkan tidak bisa dijangkau sama sekali dari ponsel.
              Menu ini menggantikannya di sana, dan disembunyikan di layar lebar
              karena kepala kolomnya sudah melakukan tugas yang sama.
            --}}
            <div class="mis-isian pel-saring-pilih pel-urut-ponsel">
                <label class="mis-label" for="pel-urut-pilih">Urutkan</label>
                <select class="form-control-modern" id="pel-urut-pilih" name="urutgabung">
                    @php
                        // Larik bersarang, bukan kunci "kolom|arah" yang dibelah
                        // di dalam @foreach: @php(...) sebaris tidak menangani
                        // pembongkaran larik, dan halamannya galat 500 tanpa
                        // menyebut sebabnya.
                        $pilihanUrut = [
                            ['bergabung', 'turun', 'Terbaru bergabung'],
                            ['bergabung', 'naik', 'Terlama bergabung'],
                            ['nama', 'naik', 'Nama A–Z'],
                            ['nama', 'turun', 'Nama Z–A'],
                            ['pesanan', 'turun', 'Pesanan terbanyak'],
                            ['status', 'naik', 'Status akun'],
                        ];
                        $arahSekarang = $arah === 'asc' ? 'naik' : 'turun';
                    @endphp
                    @foreach ($pilihanUrut as $pilihan)
                        <option value="{{ $pilihan[0] }}|{{ $pilihan[1] }}"
                            @selected($urut === $pilihan[0] && $arahSekarang === $pilihan[1])>{{ $pilihan[2] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tombolnya tetap ada di markah dan baru disembunyikan oleh
                 skrip di bawah. Tanpa JavaScript — peramban lama, skrip gagal
                 termuat, jaringan putus di tengah — penyaringnya masih bisa
                 dipakai seperti formulir biasa. --}}
            <button type="submit" class="mis-tombol mis-tombol-ungu" id="pel-terapkan">
                <i class="fas fa-search"></i> Terapkan
            </button>

            @if ($adaSaringan)
                <a href="{{ route('account.customer.index') }}" class="mis-tombol mis-tombol-halus" title="Hapus semua saringan">
                    <i class="fas fa-times"></i> Reset
                </a>
            @endif
        </form>
        </div>
        </details>

        {{-- --------------------------------------------------- daftar --}}
        {{-- Dibungkus dan diberi id: hanya bagian inilah yang ditukar saat
             mengetik, jadi kepala halaman, ringkasan, dan kotak pencariannya
             tidak ikut digambar ulang — dan fokus ketikan tidak hilang. --}}
        {{-- role=status + aria-live: isi tabel ini ditukar diam-diam tiap
             ketikan. Tanpa penanda ini, pembaca layar tidak mengumumkan apa
             pun — orang yang tidak melihat layarnya mengetik lalu tidak tahu
             daftarnya sudah berubah, apalagi jadi berapa baris. Kalimat
             ringkasnya ada di .pel-jumlah di bawah. --}}
        <div id="pel-hasil" role="status" aria-live="polite" aria-atomic="false">
        @if ($pelanggan->isEmpty())
            <div class="mis-bagian">
                <div class="mis-kosong">
                    <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-users"></i></span>
                    <p class="mis-kosong-judul">
                        {{ $adaSaringan ? 'Tidak ada yang cocok' : 'Belum ada pelanggan' }}
                    </p>
                    <p class="mis-kosong-teks">
                        {{ $adaSaringan
                            ? 'Coba ganti kata kuncinya, atau hapus saringannya.'
                            : 'Pelanggan muncul di sini setelah mendaftar di layanan.' }}
                    </p>
                </div>
            </div>
        @else
            {{-- mis-tabel-kartu: di bawah 576px tabelnya berubah jadi tumpukan
                 kartu, jadi tidak perlu digeser ke samping di ponsel. --}}
            <div class="mis-tabel-bungkus">
                <table class="mis-tabel mis-tabel-kartu">
                    <thead>
                        <tr>
                            {{-- Kepala kolom yang bisa diurutkan berupa TAUTAN,
                                 bukan tombol berskrip: ia tetap bekerja tanpa
                                 JavaScript, bisa dibuka di tab baru, dan
                                 urutannya ikut tersimpan di alamat halaman. --}}
                            {{-- aria-sort menyatakan KEADAAN kolomnya, bukan
                                 aksinya. Ikon panahnya aria-hidden dan judul
                                 tautannya berbunyi "Urutkan menurut ..." — itu
                                 menjelaskan apa yang terjadi kalau ditekan,
                                 bukan bahwa kolom ini sedang diurutkan. --}}
                            @php
                                $ariaUrut = fn ($k) => $urut === $k
                                    ? ($arah === 'asc' ? 'ascending' : 'descending')
                                    : 'none';
                            @endphp
                            @if (auth()->user()->adalahAdministrator())
                                <th class="pel-centang-sel">
                                    <input type="checkbox" id="pel-centang-semua"
                                        aria-label="Pilih semua pelanggan di halaman ini">
                                </th>
                            @endif
                            <th aria-sort="{{ $ariaUrut('nama') }}">@include('account.customer.partials.urut', ['kolom' => 'nama', 'label' => 'Pelanggan'])</th>
                            <th>Kontak</th>
                            <th aria-sort="{{ $ariaUrut('status') }}">@include('account.customer.partials.urut', ['kolom' => 'status', 'label' => 'Status'])</th>
                            <th aria-sort="{{ $ariaUrut('pesanan') }}">@include('account.customer.partials.urut', ['kolom' => 'pesanan', 'label' => 'Pesanan'])</th>
                            <th aria-sort="{{ $ariaUrut('bergabung') }}">@include('account.customer.partials.urut', ['kolom' => 'bergabung', 'label' => 'Bergabung'])</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pelanggan as $orang)
                            <tr>
                                @if (auth()->user()->adalahAdministrator())
                                    {{-- BUKAN mis-td-samar: kelas itu berarti
                                         display:none di mode kartu — ia memang
                                         dibuat untuk kolom nomor urut. Dipakai di
                                         sini, seluruh aksi massal jadi mustahil di
                                         ponsel: terukur 12 kotak centang ada di
                                         markah dan nol terlihat. --}}
                                    <td class="pel-centang-sel">
                                        <input type="checkbox" class="pel-centang" value="{{ $orang->uuid }}"
                                            aria-label="Pilih {{ $orang->full_name ?: $orang->username }}">
                                    </td>
                                @endif
                                <td class="mis-td-utama">
                                    <div class="pel-orang">
                                        {{-- Lencana hanya untuk yang SUDAH terverifikasi.
                                             Yang belum sudah disebut apa adanya oleh
                                             lencana status di kolom sebelah, jadi
                                             menandainya dua kali di satu baris cuma
                                             menambah ramai tanpa menambah keterangan. --}}
                                        @include('partials.avatar', [
                                            'orang' => $orang,
                                            'ukuran' => 38,
                                            'lencana' => (bool) $orang->email_verified_at,
                                        ])
                                        <div style="min-width: 0;">
                                            <p class="pel-nama">{{ $orang->full_name ?: $orang->username }}</p>
                                            <p class="pel-akun">&#64;{{ $orang->username }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td data-judul="Kontak">
                                    {{-- Tautan, bukan teks. Kolom ini judulnya "Kontak" dan
                                         gunanya menghubungi orangnya; sebagai teks, nomornya
                                         harus disalin dulu ke aplikasi lain. wa.me menuntut
                                         nomor berformat internasional tanpa tanda baca, dan
                                         itulah yang dikerjakan nomorWa(). --}}
                                    <div class="pel-kontak">
                                        @if ($orang->emailTampakSah())
                                            <a href="mailto:{{ $orang->email }}" title="Kirim email ke {{ $orang->email }}">
                                                <i class="fas fa-envelope mis-ikon-biru"></i> {{ $orang->email }}
                                            </a>
                                        @else
                                            {{-- Ditandai, bukan diperbaiki. Lima alamat di data yang
                                                 ada terpotong tepat di 30 huruf — pola khas batas
                                                 kolom lama saat impor — dan surat ke sana tidak
                                                 akan pernah sampai. Menebak bentuk benarnya justru
                                                 berisiko mengirim ke orang lain. --}}
                                            <span class="pel-email-rusak"
                                                title="Alamat ini tidak lengkap, jadi email ke sini tidak akan sampai.">
                                                <i class="fas fa-exclamation-triangle mis-ikon-kuning"></i> {{ $orang->email }}
                                            </span>
                                        @endif
                                        @php ($wa = \App\Support\PesananPelanggan::nomorWa($orang->telp))
                                        @if ($wa)
                                            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener"
                                                title="Hubungi lewat WhatsApp">
                                                <i class="fab fa-whatsapp mis-ikon-hijau"></i> {{ $orang->telp }}
                                            </a>
                                        @else
                                            <span><i class="fas fa-phone mis-ikon-hijau"></i> Nomor belum diisi</span>
                                        @endif
                                    </div>
                                </td>

                                <td data-judul="Status">
                                    {{--
                                      SATU lencana, bukan dua.

                                      Dulu tiap baris memuat lencana status akun dan lencana
                                      verifikasi email berdampingan. Keduanya praktis selalu
                                      mengatakan hal yang sama — terukur pada data yang ada:
                                      65 aktif, 65 terverifikasi, NOL yang berbeda. Bukan
                                      kebetulan: verifyEmail() menyetel email_verified_at dan
                                      status = active sekaligus, jadi keduanya terkunci.

                                      Yang tersisa cuma satu keadaan yang benar-benar bisa
                                      berbeda — aktif tetapi emailnya belum terverifikasi —
                                      dan itulah yang disebutkan kalau terjadi.
                                    --}}
                                    @if ($orang->status !== 'active')
                                        <span class="mis-pil mis-pil-abu"><i class="fas fa-pause"></i> Nonaktif</span>
                                    @elseif ($orang->email_verified_at)
                                        <span class="mis-pil mis-pil-hijau"><i class="fas fa-check"></i> Aktif</span>
                                    @else
                                        <span class="mis-pil mis-pil-kuning"><i class="fas fa-clock"></i> Aktif &middot; email belum terverifikasi</span>
                                    @endif
                                </td>

                                <td data-judul="Pesanan">
                                    @php ($p = $pesanan[$orang->id] ?? null)
                                    @if ($p)
                                        {{-- Menaut langsung ke tab Pesanan orang itu. Lencana
                                             "2x" memang memancing untuk ditekan, dan sebelum
                                             ini tidak melakukan apa-apa: orang harus membuka
                                             rinciannya lalu mencari sendiri tabnya. --}}
                                        <a class="mis-pil mis-pil-biru pel-pil-tautan"
                                            href="{{ route('account.customer.edit', $orang) }}#pel-panel-pesanan"
                                            title="Lihat {{ $p['jumlah'] }} pesanannya &middot; terakhir {{ $p['terakhir']?->locale('id')->translatedFormat('d M Y') }}">
                                            <i class="fas fa-receipt"></i> {{ $p['jumlah'] }}&times;
                                        </a>
                                    @else
                                        <span class="pel-akun">Belum ada</span>
                                    @endif
                                </td>

                                <td data-judul="Bergabung">
                                    <span class="pel-akun" style="font-size: .78rem;">
                                        {{ optional($orang->created_at)->locale('id')->translatedFormat('d M Y') ?: '-' }}
                                    </span>
                                </td>

                                <td data-judul="Aksi">
                                    <div class="pel-aksi">
                                        <a href="{{ route('account.customer.edit', $orang) }}"
                                            class="mis-tombol mis-tombol-garis" title="Lihat &amp; ubah">
                                            <i class="fas fa-user-edit"></i>
                                        </a>
                                        @if (auth()->user()->adalahAdministrator())
                                            <button type="button" class="mis-tombol mis-tombol-bahaya"
                                                title="Hapus pelanggan"
                                                data-hapus="{{ route('account.customer.destroy', $orang) }}"
                                                data-nama="{{ $orang->full_name ?: $orang->username }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $pelanggan->links('vendor.pagination.bootstrap-4') }}
        @endif
        </div>

        @if (auth()->user()->adalahAdministrator())
            {{-- Hanya mengaktifkan dan menonaktifkan, TIDAK menghapus:
                 penghapusan massal berarti satu salah klik menghilangkan
                 puluhan akun untuk selamanya, dan di sini tidak ada tong
                 sampah yang bisa mengembalikannya. --}}
            <div class="pel-massal" id="pel-massal" role="region" aria-label="Aksi untuk pelanggan terpilih">
                <p class="pel-massal-jumlah" id="pel-massal-jumlah">0 dipilih</p>
                {{-- Kotak "pilih semua" ada di kepala tabel, dan kepala tabel
                     disembunyikan di mode kartu. Tombol ini penggantinya di
                     ponsel, dan di layar lebar ia tetap berguna sebagai jalan
                     kedua. --}}
                <button type="button" class="mis-tombol mis-tombol-halus" id="pel-massal-semua">
                    <i class="fas fa-check-double mis-ikon-ungu"></i> Pilih semua
                </button>
                <button type="button" class="mis-tombol mis-tombol-halus" data-massal="aktifkan">
                    <i class="fas fa-user-check mis-ikon-hijau"></i> Aktifkan
                </button>
                <button type="button" class="mis-tombol mis-tombol-halus" data-massal="nonaktifkan">
                    <i class="fas fa-user-slash mis-ikon-kuning"></i> Nonaktifkan
                </button>
                <button type="button" class="mis-tombol mis-tombol-halus" id="pel-massal-batal">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        @endif

    </section>
</div>
@endsection

@push('scripts')
<script>
    /*
     * Mencari sambil mengetik, tanpa menekan tombol apa pun.
     *
     * Yang ditukar HANYA #pel-hasil, bukan seluruh halaman: kalau halamannya
     * dimuat ulang tiap ketikan, fokus keluar dari kotak cari dan huruf
     * berikutnya hilang.
     *
     * Tiga hal yang membuat ini tidak sekadar "panggil fetch tiap ketikan":
     *
     *   1. Jeda 300 ms. Tanpa itu, mengetik "budi" mengirim empat permintaan
     *      dan tiga di antaranya sia-sia.
     *   2. Permintaan lama dibatalkan. Tanpa itu, jawaban untuk "bud" bisa
     *      tiba SESUDAH jawaban untuk "budi" dan menimpanya — daftarnya lalu
     *      tidak cocok dengan apa yang tertulis di kotak cari.
     *   3. Alamat halaman ikut diperbarui. Tanpa itu, menyegarkan halaman
     *      atau menyalin tautannya mengembalikan daftar tanpa saringan.
     */
    (function () {
        const borang = document.getElementById('pel-borang');
        const hasil = document.getElementById('pel-hasil');
        const cari = document.getElementById('pel-cari');
        const terapkan = document.getElementById('pel-terapkan');
        if (!borang || !hasil || !cari) return;

        // Baru disembunyikan di sini: kalau skrip ini tidak jalan, tombolnya
        // tetap ada dan penyaringnya masih bisa dipakai.
        if (terapkan) terapkan.hidden = true;

        let jeda = null;
        let batal = null;

        const muat = function (alamat, doronganRiwayat) {
            if (batal) batal.abort();
            batal = new AbortController();

            borang.classList.add('sibuk');
            hasil.classList.add('sibuk');

            fetch(alamat, {
                signal: batal.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (r) {
                    if (!r.ok) throw new Error('status ' + r.status);
                    return r.text();
                })
                .then(function (teks) {
                    // Diurai sebagai dokumen, bukan disisipkan mentah: yang
                    // dibutuhkan cuma satu bagiannya, dan mengurai lebih dulu
                    // berarti skrip di dalamnya tidak ikut dijalankan.
                    const doc = new DOMParser().parseFromString(teks, 'text/html');
                    const baru = doc.getElementById('pel-hasil');
                    if (baru) hasil.innerHTML = baru.innerHTML;

                    if (doronganRiwayat) {
                        history.replaceState(null, '', alamat);
                    }

                    /*
                     * Isian tersembunyi urut/arah disamakan dengan alamat yang
                     * baru dimuat.
                     *
                     * Tanpa ini, menekan kepala kolom memang mengubah urutan —
                     * tetapi formulirnya masih memegang urutan lama, sehingga
                     * huruf berikutnya yang diketik diam-diam mengembalikan
                     * urutannya ke keadaan sebelum ditekan.
                     */
                    const par = new URL(alamat, location.origin).searchParams;
                    borang.querySelectorAll('input[type=hidden]').forEach(function (i) {
                        if (par.has(i.name)) i.value = par.get(i.name);
                    });
                })
                .catch(function (e) {
                    // Pembatalan bukan kegagalan: ia memang disengaja saat
                    // huruf berikutnya diketik.
                    if (e.name === 'AbortError') return;
                    window.misToast('gagal', 'Gagal memuat daftar. Coba lagi.');
                })
                .finally(function () {
                    borang.classList.remove('sibuk');
                    hasil.classList.remove('sibuk');
                });
        };

        const alamatSekarang = function () {
            const data = new FormData(borang);
            const p = new URLSearchParams();

            for (const [k, v] of data.entries()) {
                if (String(v).trim() !== '') p.set(k, v);
            }

            const q = p.toString();
            return borang.action + (q ? '?' + q : '');
        };

        const jadwalkan = function (tundaan) {
            clearTimeout(jeda);
            jeda = setTimeout(function () { muat(alamatSekarang(), true); }, tundaan);
        };

        const hapus = document.getElementById('pel-hapus');

        const setelHapus = function () {
            if (hapus) hapus.hidden = cari.value === '';
        };

        cari.addEventListener('input', function () {
            setelHapus();
            jadwalkan(300);
        });

        if (hapus) {
            hapus.addEventListener('click', function () {
                cari.value = '';
                setelHapus();
                // Fokus dikembalikan ke kotaknya: yang menghapus kata kunci
                // hampir selalu mau mengetik kata kunci lain.
                cari.focus();
                jadwalkan(0);
            });
        }

        // Tombol silang bawaan <input type=search> di sebagian peramban
        // mengosongkan isian tanpa memicu 'input', jadi 'search' ikut didengar.
        cari.addEventListener('search', function () {
            setelHapus();
            jadwalkan(0);
        });

        /*
         * Menu pengurut khusus ponsel menulis ke isian tersembunyi urut/arah,
         * bukan mengirim namanya sendiri: peladen hanya mengenal dua nama itu,
         * dan namanya sengaja 'urutgabung' supaya tidak ikut terkirim.
         */
        const pilihUrut = document.getElementById('pel-urut-pilih');

        if (pilihUrut) {
            pilihUrut.addEventListener('change', function () {
                const bagian = pilihUrut.value.split('|');
                const setel = function (nama, nilai) {
                    const i = borang.querySelector('input[type=hidden][name="' + nama + '"]');
                    if (i) i.value = nilai;
                };
                setel('urut', bagian[0]);
                setel('arah', bagian[1]);
                jadwalkan(0);
            });
        }

        // Menu pilihan tidak perlu ditunda: satu klik sudah keputusan penuh.
        borang.querySelectorAll('select').forEach(function (s) {
            if (s.id === 'pel-urut-pilih') return;
            s.addEventListener('change', function () { jadwalkan(0); });
        });

        // Enter tidak boleh memuat ulang halaman; hasilnya sudah tampil.
        borang.addEventListener('submit', function (e) {
            e.preventDefault();
            jadwalkan(0);
        });

        /*
         * Penomoran halaman dan kepala kolom pengurut ikut ditangani di sini.
         * Keduanya berada DI DALAM bagian yang ditukar, jadi penangan harus
         * dipasang di wadahnya — pemasangan langsung akan hilang begitu isinya
         * diganti pertama kali.
         */
        hasil.addEventListener('click', function (e) {
            const tautan = e.target.closest('.pagination a, .pel-urut');
            if (!tautan || e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;

            e.preventDefault();
            muat(tautan.href, true);
            hasil.scrollIntoView({ block: 'start', behavior: 'smooth' });
        });
    })();

    /*
     * Penyaring terbuka sendiri mulai 768px dan terlipat di bawah itu.
     *
     * Dikerjakan skrip, bukan CSS: isi <details> yang tertutup disembunyikan
     * oleh gaya bawaan peramban, dan menimpanya dari CSS tidak bisa diandalkan
     * antar peramban.
     */
    (function () {
        const lipat = document.getElementById('pel-penyaring');
        if (!lipat) return;
        const lebar = window.matchMedia('(min-width: 768px)');

        /*
         * Di ponsel penyaringnya terlipat — dan di dalamnya ada menu Urutkan,
         * satu-satunya cara mengurutkan di sana. Terukur: sebelum dibuka,
         * elementFromPoint di titik tengah menu itu tidak menunjuk apa pun.
         * Jadi begitu ada saringan atau urutan yang bukan bawaan, penyaringnya
         * dibuka sendiri: yang sedang berlaku harus terlihat, bukan tersembunyi
         * di balik satu ketukan lagi.
         */
        const sedangDipakai = @json($adaSaringan) || @json($urut !== 'bergabung' || $arah !== 'desc');

        const setel = function () {
            if (lebar.matches || sedangDipakai) lipat.setAttribute('open', '');
        };

        setel();
        lebar.addEventListener('change', setel);
    })();

    /*
     * Pilih banyak, lalu aktifkan/nonaktifkan sekaligus.
     *
     * Penangannya dipasang di wadah #pel-hasil, bukan di tiap kotak centang:
     * isi wadah itu ditukar tiap kali mengetik atau berpindah halaman, dan
     * pemasangan langsung akan hilang begitu isinya diganti pertama kali.
     */
    (function () {
        const hasil = document.getElementById('pel-hasil');
        const bar = document.getElementById('pel-massal');
        if (!hasil || !bar) return;

        const jumlahTeks = document.getElementById('pel-massal-jumlah');

        /*
         * Pilihannya disimpan di sini, BUKAN dibaca dari kotak yang sedang
         * tampil.
         *
         * Isi #pel-hasil ditukar tiap kali mengetik atau berpindah halaman.
         * Dengan pilihan yang hanya hidup di kotaknya, mencentang dua belas
         * orang lalu tanpa sengaja mengetik satu huruf di kotak cari
         * menghapusnya tanpa sepatah kata — terukur: "2 dipilih" jadi
         * "0 dipilih" dan baris aksinya hilang begitu saja.
         *
         * Disimpan terpisah, pilihan bertahan menyeberangi pencarian dan
         * halaman, jadi orang bisa menyaring, memilih, menyaring lagi, memilih
         * lagi, baru bertindak.
         */
        const dipilih = new Set();

        const segarkan = function () {
            const n = dipilih.size;
            bar.classList.toggle('tampil', n > 0);
            if (jumlahTeks) jumlahTeks.textContent = n + ' dipilih';

            const semua = hasil.querySelector('#pel-centang-semua');
            const kotak = [...hasil.querySelectorAll('.pel-centang')];

            if (semua) {
                const tampilTerpilih = kotak.filter(function (c) { return dipilih.has(c.value); }).length;
                semua.checked = kotak.length > 0 && tampilTerpilih === kotak.length;
                // Sebagian terpilih ditandai setengah, bukan kosong: kosong
                // membuatnya tampak seolah tidak ada yang dipilih sama sekali.
                semua.indeterminate = tampilTerpilih > 0 && tampilTerpilih < kotak.length;
            }
        };

        // Kotak yang baru digambar disamakan lagi dengan pilihan yang tersimpan.
        const selaraskan = function () {
            hasil.querySelectorAll('.pel-centang').forEach(function (c) {
                c.checked = dipilih.has(c.value);
            });
            segarkan();
        };

        hasil.addEventListener('change', function (e) {
            if (e.target.id === 'pel-centang-semua') {
                hasil.querySelectorAll('.pel-centang').forEach(function (c) {
                    c.checked = e.target.checked;
                    if (e.target.checked) dipilih.add(c.value); else dipilih.delete(c.value);
                });
            } else if (e.target.classList.contains('pel-centang')) {
                if (e.target.checked) dipilih.add(e.target.value); else dipilih.delete(e.target.value);
            } else {
                return;
            }

            segarkan();
        });

        new MutationObserver(selaraskan).observe(hasil, { childList: true, subtree: true });

        document.getElementById('pel-massal-semua').addEventListener('click', function () {
            hasil.querySelectorAll('.pel-centang').forEach(function (c) {
                c.checked = true;
                dipilih.add(c.value);
            });
            segarkan();
        });

        document.getElementById('pel-massal-batal').addEventListener('click', function () {
            dipilih.clear();
            hasil.querySelectorAll('.pel-centang, #pel-centang-semua').forEach(function (c) {
                c.checked = false;
                c.indeterminate = false;
            });
            segarkan();
        });

        bar.querySelectorAll('[data-massal]').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                const uuid = [...dipilih];
                if (uuid.length === 0) return;

                const aksi = tombol.dataset.massal;
                const aktif = aksi === 'aktifkan';

                window.misKonfirmasi({
                    judul: aktif ? 'Aktifkan pelanggan terpilih?' : 'Nonaktifkan pelanggan terpilih?',
                    pesan: aktif
                        ? '%s akan bisa masuk kembali ke akunnya.'
                        : '%s tidak akan bisa masuk lagi sampai diaktifkan kembali. Datanya tetap utuh.',
                    sorot: uuid.length + ' pelanggan',
                    tombol: aktif ? 'Ya, aktifkan' : 'Ya, nonaktifkan',
                    jenis: 'tanya',
                    glif: aktif ? 'fa-user-check' : 'fa-user-slash',
                }).then(function (ya) {
                    if (!ya) return;

                    const data = new FormData();
                    data.append('aksi', aksi);
                    uuid.forEach(function (u) { data.append('uuid[]', u); });

                    fetch(@json(route('account.customer.massal')), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: data,
                    })
                        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                        .then(function (j) {
                            if (!j.ok || !j.d.success) {
                                window.misToast('gagal', j.d.message || 'Gagal mengubah pelanggan terpilih.');
                                return;
                            }
                            window.misToast('berhasil', j.d.message);
                            // Dimuat ulang supaya lencana statusnya ikut berubah.
                            window.location.reload();
                        })
                        .catch(function () {
                            window.misToast('gagal', 'Tidak bisa menghubungi peladen.');
                        });
                });
            });
        });
    })();

    /*
     * Penghapusan: satu penangan untuk seluruh tabel, bukan onclick di tiap
     * baris. Dengan begitu tidak ada nama fungsi global yang harus dijaga, dan
     * alamatnya datang dari route() di markah — bukan dirangkai dari id di
     * JavaScript, yang membuat perubahan rute diam-diam merusak tombolnya.
     */
    document.addEventListener('click', function (e) {
        const tombol = e.target.closest('[data-hapus]');
        if (!tombol) return;

        /* Lewat pembungkus bersama, bukan Swal.fire sendiri: nama pelanggannya
           disisipkan sebagai teks (ia datang dari isian orang), tombolnya
           memakai kelas berteks yang benar, dan Batal yang jadi tumpuan fokus. */
        window.misKonfirmasi({
            judul: 'Hapus pelanggan ini?',
            pesan: 'Data %s dan seluruh riwayat masuknya ikut terhapus. Tindakan ini tidak bisa dibatalkan.',
            sorot: tombol.dataset.nama || 'pelanggan ini',
            tombol: 'Ya, hapus',
            jenis: 'bahaya',
        }).then(function (ya) {
            if (!ya) return;

            fetch(tombol.dataset.hapus, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                .then(function (j) {
                    if (!j.ok || !j.d.success) {
                        window.misToast('gagal', j.d.message || 'Gagal menghapus pelanggan.');
                        return;
                    }
                    window.misToast('berhasil', j.d.message);
                    tombol.closest('tr').remove();
                })
                .catch(function () {
                    window.misToast('gagal', 'Tidak bisa menghubungi peladen.');
                });
        });
    });
</script>
@endpush
