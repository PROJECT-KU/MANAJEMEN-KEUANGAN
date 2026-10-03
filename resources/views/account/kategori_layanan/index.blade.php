@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Angkatan Layanan | MIS
@stop

@include('partials.toast-flash')

@push('gaya')
<style>
    /* Bahasa rupanya mengikuti docs/panduan-ui-mis.md — sama dengan Tarif
       layanan dan Data Pelanggan. */

    .ang-kepala {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        column-gap: 12px; row-gap: 2px; align-items: center;
        margin-bottom: 12px;
    }

    .ang-kepala > .mis-medali { grid-row: 1 / span 2; align-self: center; }
    .ang-kepala-judul { grid-column: 2; grid-row: 1; margin: 0; line-height: 1.25; font-size: .92rem; font-weight: 800; color: var(--mis-tinta); }
    .ang-kepala-sub { grid-column: 2; grid-row: 2; margin: 0; line-height: 1.45; font-size: .75rem; color: var(--mis-tinta-3); }

    /* --------------------------------------------------------------- saringan */

    /* ------------------------------------------------------------------ tabel */

    .ang-nama { margin: 0; line-height: 1.3; font-size: .86rem; font-weight: 700; color: var(--mis-tinta); overflow-wrap: anywhere; }
    .ang-ket { margin: 1px 0 0; line-height: 1.4; font-size: .73rem; color: var(--mis-tinta-3); }
    .ang-aksi { display: flex; align-items: center; justify-content: flex-end; gap: 6px; }

    /* Borang gandakan tidak boleh memakai ruang barisnya sendiri. */
    .ang-gandakan { display: inline-flex; margin: 0; }

    /*
     * Lebar kolom dipatok persen.
     *
     * Dibiarkan otomatis, peramban membagi rata menurut isi terpanjang:
     * terukur kolom Angkatan cuma 219px dari 1124px sementara Kuota dapat
     * 158px dan Aksi 154px untuk isi yang jauh lebih pendek. Akibatnya nama
     * "Scopus Camp Yogyakarta" patah dua baris DAN keterangannya patah dua
     * baris lagi — barisnya jadi 99px, dan tingginya berbeda-beda antar baris
     * tergantung panjang namanya.
     */
    .ang-k-nama { width: 34%; }
    .ang-k-layanan { width: 11%; }
    .ang-k-tanggal { width: 13%; }
    .ang-k-kuota { width: 11%; }
    .ang-k-biaya { width: 12%; }
    .ang-k-status { width: 9%; }
    .ang-k-aksi { width: 10%; }

    .ang-sampul {
        width: 38px; height: 38px;
        object-fit: cover;
        border-radius: 11px;
        border: 1px solid var(--mis-garis);
        flex: 0 0 auto;
    }

    .ang-nama a { color: inherit; }
    .ang-nama a:hover { color: #4f46e5; text-decoration: none; }

    /* Kolom centang: ukuran dan warnanya sama dengan Data Customer, layar
       yang sudah lebih dulu punya aksi massal. Dibuat sendiri, kotak centang
       jadi tiga ukuran berbeda di tiga layar yang tugasnya sama. */
    .ang-centang-sel {
        width: 34px;
        padding-right: 0 !important;
    }

    /* Ukuran dan daerah tekannya datang dari .mis-centang di mis-ui.css —
       dipakai bersama Data Pelanggan supaya tidak jadi dua ukuran lagi. */

    .ang-massal {
        display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
        padding: 11px 13px; margin-bottom: 12px;
        border: 1px solid #c7d2fe; border-radius: 13px;
        background: #eef2ff;
    }

    .ang-massal-jumlah { font-size: .8rem; color: #4338ca; }
    .ang-massal-jumlah strong { font-size: .92rem; font-weight: 800; }
    .ang-massal-tombol { display: flex; flex-wrap: wrap; gap: 8px; margin-left: auto; }

    .ang-tautan {
        display: inline-block;
        /* "lihat 1 pendaftar" tidak boleh patah: dua barisnya membuat baris
           tabelnya 18px lebih tinggi daripada baris tetangganya, dan kolom
           kuotanya jadi terlihat berantakan. */
        white-space: nowrap;
        margin-top: 3px;
        line-height: 1.4;
        font-size: .72rem;
        font-weight: 700;
        color: #4f46e5;
    }

    .ang-tautan:hover { color: #4338ca; }

    .ang-kuota { display: inline-flex; align-items: baseline; gap: 4px; font-size: .8rem; color: var(--mis-tinta-2); }

    /* Satu nilai bertingkat: angkanya di atas, keterangannya di bawah. */
    .ang-nilai { display: block; }
    .ang-nilai .ang-nama { display: block; white-space: nowrap; }
    .ang-nilai .ang-ket { display: block; }
    .ang-kuota strong { font-size: .92rem; font-weight: 800; color: var(--mis-tinta); }

    /*
     * Dua kolom DAN tombolnya ikut di baris kedua, bukan sendirian di baris
     * ketiga: empat kendali dalam dua kolom sempat memakan 183px di 820–1000px,
     * dengan menu urut sendirian di baris kedua dan tombol Saring sendirian di
     * baris ketiga.
     */
    @media (max-width: 1199.98px) {
    }

    /*
     * Dua kolom disembunyikan mulai 992px ke bawah — bukan cuma di mode kartu.
     *
     * Tabel ini delapan kolom, terbanyak di antara layar daftar mana pun di
     * MIS, dan mode kartu bersama baru menyala di 768px.
     *
     * Terukur: tabelnya menuntut 921px sementara wadahnya cuma 854px di layar
     * 1200px dan 896px di 992px — keduanya harus digeser ke samping. Di 820px
     * kolom Angkatan tinggal 85px dan "Scopus Camp Yogyakarta" patah di tengah
     * kata jadi "Yogyakar-ta". Data Pelanggan tidak pernah begitu di lebar
     * mana pun, jadi ini memang khas tabel ini, bukan batas bersama.
     *
     * Ambangnya 1366px. Dicari dengan mengukur, bukan ditebak: delapan kolom
     * baru berhenti tergeser ke samping mulai 1280px, tetapi di sana barisnya
     * masih 151px karena semua yang tersisa patah-patah. Di 1240px dengan enam
     * kolom barisnya 99px. Jadi yang dipakai bukan lebar terkecil yang "muat",
     * melainkan yang pertama terbaca: 1366px, lebar laptop yang paling umum.
     *
     * Yang dibuang dua yang paling jarang ditindaklanjuti, dan keduanya sama
     * dengan yang sudah disembunyikan di ponsel — layanan sudah tertulis di
     * nama dan tersaring pil di atas, harga ada di halaman rincian. Keduanya
     * tetap utuh di layar lebar dan di berkas unduhan.
     */
    @media (max-width: 1365.98px) {
        .ang-tabel .ang-k-layanan,
        .ang-tabel .ang-k-biaya,
        .ang-tabel td[data-judul="Layanan"],
        .ang-tabel td[data-judul="Biaya"],
        /*
         * Dua pemilih terakhir bukan pengulangan. Di mode kartu, aturan
         * bersama `.mis-tabel.mis-tabel-kartu tbody td` berbobot (0,3,2) —
         * lebih berat daripada `.ang-tabel td[data-judul=...]` yang (0,2,1) —
         * jadi display:grid-nya menang dan kedua baris itu muncul kembali di
         * kartu. Terlihat di potret 390px, tidak terlihat oleh hitungan kolom
         * karena yang dihitung <th>, bukan <td>.
         */
        .mis-tabel.mis-tabel-kartu.ang-tabel tbody td[data-judul="Layanan"],
        .mis-tabel.mis-tabel-kartu.ang-tabel tbody td[data-judul="Biaya"] { display: none; }
    }

    /*
     * Pemberitahuan "Urungkan" sesudah menghapus.
     *
     * Ditempel di bawah layar, bukan di atas: bilah pilih-massal sudah
     * menempati tepi atas, dan dua pita yang saling menimpa membuat keduanya
     * tidak terbaca.
     *
     * DI LUAR media query mana pun. Ditaruh di dalam, ia pernah menyisip ke
     * tengah daftar pemilih "sembunyikan kolom Layanan dan Biaya" dan
     * memutusnya — kedua kolom itu muncul kembali di 768px dan tabelnya harus
     * digeser ke samping, sementara pemberitahuannya sendiri tidak bergaya
     * sama sekali di layar lebar.
     */
    .ang-urung {
        position: fixed;
        left: 50%;
        bottom: 18px;
        transform: translateX(-50%);
        z-index: 1080;
        display: flex;
        align-items: center;
        gap: 14px;
        max-width: calc(100vw - 32px);
        padding: 12px 16px;
        border-radius: 14px;
        background: #1e293b;
        color: #f8fafc;
        font-size: .88rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .28);
    }

    .ang-urung form { margin: 0; }

    .ang-urung-tombol {
        border: 0;
        border-radius: 10px;
        padding: 8px 14px;
        background: #fbbf24;
        color: #1f2937;
        font-weight: 700;
        font-size: .85rem;
        /* 36px penuh: ini tombol yang ditekan orang yang sedang panik. */
        min-height: 36px;
        cursor: pointer;
    }

    .ang-urung-tombol:hover { background: #f59e0b; }

    @media (max-width: 767.98px) {
        /*
         * Direntangkan selebar layar, bukan dibiarkan selebar isinya.
         *
         * Dibiarkan menyusut, terukur lebarnya cuma 214px dan nama angkatan
         * patah jadi empat baris — kotaknya 130px tinggi dan menutupi dua
         * kartu di bawahnya. Tombolnya tetap di kanan.
         */
        .ang-urung {
            left: 16px;
            right: 16px;
            transform: none;
            max-width: none;
            justify-content: space-between;
            gap: 10px;
        }

        .ang-urung > span {
            flex: 1 1 0;
            min-width: 0;
            font-size: .82rem;
        }

        .ang-urung-tombol { padding: 8px 12px; }
    }

    @media (max-width: 767.98px) {

        /* Tiap baris jadi kartu, seperti daftar pelanggan: garis 1px terlalu
           sepi untuk memisahkan enam keterangan berlabel. */
        .mis-tabel-kartu.ang-tabel tbody { display: flex; flex-direction: column; gap: 8px; }
        .mis-tabel-kartu.ang-tabel tbody tr { border: 1px solid var(--mis-garis); border-radius: 13px; background: #f8fafc; }

        /* Kotak centang dipindah ke sudut kanan-atas kartu, sama seperti Data
           Customer. Dibiarkan jadi baris berlabel "Pilih", ia menambah satu
           baris lagi ke kartu yang sudah 253px tingginya — dan label itu tidak
           memberi tahu apa pun yang kotaknya belum katakan. */
        .mis-tabel.mis-tabel-kartu.ang-tabel tbody tr { position: relative; }

        .mis-tabel.mis-tabel-kartu.ang-tabel tbody td.ang-centang-sel {
            position: absolute;
            top: 10px;
            right: 12px;
            width: auto;
            padding: 0 !important;
            justify-content: flex-end;
        }

        .mis-tabel.mis-tabel-kartu.ang-tabel tbody td.ang-centang-sel::before { display: none; }

        /* Kartu yang terpilih diberi warna: di antara kartu-kartu berjarak,
           kotak 16px di sudut terlalu kecil untuk menunjukkan mana yang ikut. */
        .mis-tabel-kartu.ang-tabel tbody tr:has(.ang-centang:checked) {
            border-color: #c7d2fe;
            background: #f5f6ff;
        }

        /* Nama angkatan diberi ruang kanan supaya nama panjang tidak menyelinap
           ke bawah kotak centangnya. */
        .mis-tabel.mis-tabel-kartu.ang-tabel tbody td.mis-td-utama { padding-right: 28px; }
    }
</style>
@endpush

@section('content')
@php
    /*
     * Tautan ke pendaftar satu angkatan, di layar Pendaftar Layanan.
     *
     * Dulu menunjuk layar pendaftar per layanan dan mengirim `kategori` —
     * yang TIDAK PERNAH dibaca layar itu. Jadi menekan tautannya menampilkan
     * seluruh pendaftar layanan tersebut, bukan angkatan yang ditekan, dan
     * tidak ada yang tahu saringannya tidak bekerja. Layar terpadu memang
     * membaca `angkatan`.
     *
     * Dan kini berlaku untuk KETIGA layanan berangkatan, bukan dua: Webinar
     * Eksklusif dulu tidak punya tautan sama sekali di sini.
     */
    $tautanPendaftar = function ($a) {
        if (! \App\Support\PendaftaranSemuaLayanan::berangkatan((string) $a->layanan)) {
            return null;
        }

        return route('account.pendaftaran-layanan.index', [
            'layanan' => $a->layanan,
            'angkatan' => $a->id,
        ]);
    };
@endphp
<div class="main-content mis-badan">
    <section class="section">

        <div class="mis-kepala">
            <span class="mis-medali mis-ungu" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Angkatan layanan</h1>
                <p class="mis-sub">Semua angkatan jasa dalam satu daftar — harga dan deskripsinya ikut tarif induk.</p>
            </div>
            <div class="mis-kepala-aksi mis-kepala-aksi-pasangan">
                {{-- Layar kategori yang digantikan layar ini SUDAH punya unduhan
                     PDF dan Excel; menyatukannya tanpa keduanya berarti
                     diam-diam mencabut kemampuan yang sudah dipakai orang.
                     Saringan yang sedang aktif ikut terbawa. --}}
                <a href="{{ route('account.kategori-layanan.excel', request()->query()) }}"
                    class="mis-tombol mis-tombol-halus">
                    <i class="fas fa-file-excel"></i> Unduh Excel
                </a>
                <a href="{{ route('account.kategori-layanan.cetak', request()->query()) }}"
                    class="mis-tombol mis-tombol-halus">
                    <i class="fas fa-file-pdf"></i> Unduh PDF
                </a>
                @if ($bolehUbah)
                    <a href="{{ route('account.kategori-layanan.create', ['layanan' => $layanan ?: 'scopus_camp']) }}"
                        class="mis-tombol mis-tombol-ungu">
                        <i class="fas fa-plus"></i> Angkatan baru
                    </a>
                @endif
            </div>
        </div>

        {{-- ---------------------------------------------- ringkasan --}}
        {{-- Ubin yang sama dengan Data Pelanggan, dan kelasnya memang kelas
             yang sama (.mis-ubin di mis-ui.css) — bukan salinan.

             Sebelumnya ini sebaris tulisan abu-abu "60 angkatan · 6 aktif ·
             24 draf · 30 nonaktif". Angkanya ada, tetapi tidak menarik mata
             dan tidak bisa ditekan, padahal angka yang menarik perhatian
             hampir selalu memancing pertanyaan "yang mana saja?".

             Tiap ubin membawa layanan dan kata kunci yang sedang dipakai,
             jadi menekannya mempersempit — bukan mengulang dari nol. --}}
        @php
            $bawaUbin = array_filter([
                'layanan' => $layanan,
                'periode' => $periode,
                'cari' => $cari !== '' ? $cari : null,
            ]);
            $ubin = [
                [null, 'Seluruh angkatan', $perStatus->sum(), 'mis-ungu', 'fa-layer-group'],
                ['active', 'Sedang aktif', $perStatus['active'] ?? 0, 'mis-hijau', 'fa-check-circle'],
                ['draft', 'Masih draf', $perStatus['draft'] ?? 0, 'mis-kuning', 'fa-pen'],
                ['non active', 'Nonaktif', $perStatus['non active'] ?? 0, 'mis-abu', 'fa-pause-circle'],
            ];
        @endphp
        @php
            /*
             * Blok @php, BUKAN @php(...) sebaris.
             *
             * Penanda sebaris itu ditutup Blade di kurung PERTAMA yang
             * ditemuinya, jadi array_sum($jumlahPerlu) membuat kurungnya
             * timpang: sisanya jadi kode PHP mentah, kompilasinya berhenti di
             * situ, dan halamannya galat 500 dengan menyebut variabel lain
             * yang sama sekali tidak bersalah.
             */
            $perluPertama = array_key_first(array_filter($jumlahPerlu));
        @endphp
        {{-- Di ponsel ubinnya jadi barisan yang digeser; pembungkus ini yang
             memegang petunjuknya, sebab barisan itu sendiri yang menggeser. --}}
        <div class="mis-ringkas-geser" data-mis-geser>
        <div class="mis-ringkas {{ $totalPerlu > 0 ? 'mis-ringkas-5' : '' }}" aria-label="Ringkasan angkatan">
            @foreach ($ubin as [$nilaiStatus, $label, $angka, $warna, $glif])
                <a class="mis-ubin {{ $status === $nilaiStatus ? 'terpilih' : '' }}"
                    href="{{ route('account.kategori-layanan.index',
                        $nilaiStatus === null ? $bawaUbin : array_merge($bawaUbin, ['status' => $nilaiStatus])) }}"
                    title="{{ $nilaiStatus === null ? 'Tampilkan semua status' : 'Saring: hanya yang ' . strtolower($label) }}">
                    <span class="mis-medali kecil {{ $warna }}" aria-hidden="true"><i class="fas {{ $glif }}"></i></span>
                    <div>
                        <p class="mis-ubin-angka">{{ number_format($angka) }}</p>
                        <p class="mis-ubin-label">{{ $label }}</p>
                    </div>
                </a>
            @endforeach

            {{-- Ubin kelima hanya muncul kalau memang ada yang perlu dicek.
                 Ubin bertuliskan 0 menempati ruang tanpa memberi tahu apa pun,
                 dan di layar sempit ia mendesak empat ubin lain. --}}
            @if ($totalPerlu > 0)
                <a class="mis-ubin {{ $perlu ? 'terpilih' : '' }}"
                    href="{{ route('account.kategori-layanan.index',
                        array_merge($bawaUbin, ['perlu' => $perluPertama])) }}"
                    title="Saring: angkatan yang perlu ditindaklanjuti">
                    {{-- Merah, bukan kuning: "Masih draf" tepat di sebelahnya sudah
                         kuning, dan dua ubin sewarna untuk dua arti berbeda membuat
                         keduanya harus dibaca dulu sebelum bisa dibedakan. --}}
                    <span class="mis-medali kecil mis-merah" aria-hidden="true"><i class="fas fa-exclamation-triangle"></i></span>
                    <div>
                        <p class="mis-ubin-angka">{{ number_format($totalPerlu) }}</p>
                        <p class="mis-ubin-label">Perlu dicek</p>
                    </div>
                </a>
            @endif
        </div>
            <p class="mis-ringkas-petunjuk" aria-hidden="true">
                <i class="fas fa-arrows-alt-h"></i> Geser untuk lihat semua
            </p>
        </div>

        {{-- ---------------------------------------------- penyaring --}}
        {{-- <details> membungkus penyaringnya, sama seperti Data Pelanggan: di
             ponsel empat kendali yang selalu terbuka memakan satu layar penuh
             sebelum baris pertama data kelihatan. Di layar lebar ia dipaksa
             terbuka oleh mis-ui.js dan ringkasannya disembunyikan, jadi tampak
             seperti baris penyaring biasa. --}}
        <details class="mis-lipat" data-mis-lipat>
            <summary>
                <i class="fas fa-sliders-h mis-ikon-ungu" aria-hidden="true"></i>
                Cari &amp; saring
                @if ($adaSaringan)
                    <span class="mis-pil mis-pil-ungu">aktif</span>
                @endif
            </summary>

        <div class="mis-saring-kartu">
            <form method="GET" action="{{ route('account.kategori-layanan.index') }}" class="mis-saring"
                data-mis-saring="ang-hasil">
                {{-- Menu pengurut menulis ke dua isian ini, bukan mengirim
                     namanya sendiri: peladen hanya mengenal 'urut' dan 'arah'. --}}
                <input type="hidden" name="urut" id="ang-urut-kolom" value="{{ $urut }}">
                <input type="hidden" name="arah" id="ang-urut-arah" value="{{ $arah }}">

                <div class="mis-isian mis-saring-cari">
                    <label class="mis-label" for="ang-cari">Cari</label>
                    <input type="search" class="form-control-modern" id="ang-cari" name="cari"
                        value="{{ $cari }}" placeholder="Nama, nomor angkatan, atau lokasi"
                        autocomplete="off" aria-controls="ang-hasil" data-mis-cari>
                        {{-- Tautan, bukan tombol: tanpa JavaScript ia tetap membuka
                             daftar tanpa kata kunci. Dengan JavaScript, mis-ui.js
                             menahan tautannya dan mengosongkan di tempat.

                             Selalu ada di markah, disembunyikan lewat `hidden`:
                             dengan saringan hidup, kotaknya bisa jadi kosong atau
                             terisi tanpa halamannya dimuat ulang, jadi yang
                             dirender bersyarat tidak akan pernah muncul. --}}
                    <a href="{{ route('account.kategori-layanan.index', array_filter([
                        'layanan' => $layanan, 'status' => $status,
                        'perlu' => $perlu, 'periode' => $periode,
                    ])) }}" class="mis-saring-hapus" aria-label="Kosongkan pencarian"
                        title="Kosongkan pencarian" data-mis-kosongkan
                        @if ($cari === '') hidden @endif>
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </a>
                    <span class="mis-saring-sibuk" aria-hidden="true"></span>
                </div>

                {{-- Dulu sederet pil di atas halaman. Dijadikan menu supaya
                     susunan layarnya sama dengan Data Pelanggan — kepala, ubin,
                     kartu saringan, tabel — dan supaya satu pita penuh di atas
                     daftar tidak lagi memakan ruang sebelum baris pertama
                     terlihat. Jumlahnya ikut dibawa ke dalam labelnya. --}}
                <div class="mis-isian mis-saring-pilih">
                    <label class="mis-label" for="ang-layanan">Layanan</label>
                    <select class="form-control-modern" id="ang-layanan" name="layanan">
                        <option value="">Semua layanan ({{ $jumlah->sum() }})</option>
                        @foreach ($katalog as $kunci => $tentang)
                            <option value="{{ $kunci }}" @selected($layanan === $kunci)>
                                {{ $tentang['nama'] }} ({{ $jumlah[$kunci] ?? 0 }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mis-isian mis-saring-pilih">
                    <label class="mis-label" for="ang-status">Status</label>
                    <select class="form-control-modern" id="ang-status" name="status">
                        <option value="">Semua status</option>
                        @foreach (['active' => 'Aktif', 'non active' => 'Nonaktif', 'draft' => 'Draf'] as $k => $l)
                            <option value="{{ $k }}" @selected($status === $k)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Pilihan jadi, bukan dua kotak tanggal: yang ditanyakan selalu
                     "yang mana bulan depan", dan pemilih tanggal adalah kendali
                     yang paling sering salah dipakai di layar-layar ini. --}}
                <div class="mis-isian mis-saring-pilih">
                    <label class="mis-label" for="ang-periode">Periode</label>
                    <select class="form-control-modern" id="ang-periode" name="periode">
                        <option value="">Kapan saja</option>
                        @foreach (\App\KategoriLayanan::PERIODE as $kunci => $label)
                            <option value="{{ $kunci }}" @selected($periode === $kunci)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mis-isian mis-saring-pilih">
                    <label class="mis-label" for="ang-perlu">Perlu dicek</label>
                    <select class="form-control-modern" id="ang-perlu" name="perlu">
                        <option value="">Semua angkatan</option>
                        @foreach (\App\KategoriLayanan::PERLU as $kunci => $label)
                            <option value="{{ $kunci }}" @selected($perlu === $kunci)>
                                {{ $label }} ({{ $jumlahPerlu[$kunci] ?? 0 }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Di layar lebar kepala kolomnya yang mengurutkan; menu ini
                     penggantinya di ponsel, tempat <thead> disembunyikan. --}}
                <div class="mis-isian mis-saring-pilih mis-urut-ponsel">
                    <label class="mis-label" for="ang-urut">Urutkan</label>
                    <select class="form-control-modern" id="ang-urut" name="urutgabung" data-mis-urut-ponsel>
                        @php
                            // Larik bersarang, bukan kunci "kolom|arah" yang dibelah
                            // di dalam @foreach: @php(...) sebaris tidak menangani
                            // pembongkaran larik, dan halamannya galat 500 tanpa
                            // menyebut sebabnya.
                            $pilihanUrut = [
                                ['mulai', 'turun', 'Terbaru dulu'],
                                ['mulai', 'naik', 'Terlama dulu'],
                                ['nama', 'naik', 'Nama A–Z'],
                                ['nama', 'turun', 'Nama Z–A'],
                                ['sisa_kuota', 'naik', 'Sisa kuota paling sedikit'],
                                ['sisa_kuota', 'turun', 'Sisa kuota paling banyak'],
                                ['status', 'naik', 'Status'],
                                ['biaya', 'turun', 'Biaya termahal'],
                                ['biaya', 'naik', 'Biaya termurah'],
                                ['layanan', 'naik', 'Layanan A–Z'],
                            ];
                        @endphp
                        @foreach ($pilihanUrut as $pilihan)
                            <option value="{{ $pilihan[0] }}|{{ $pilihan[1] }}"
                                @selected($urut === $pilihan[0] && $arah === $pilihan[1])>{{ $pilihan[2] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombolnya tetap ada di markah dan baru disembunyikan oleh
                     mis-ui.js. Tanpa JavaScript — peramban lama, berkasnya gagal
                     termuat, jaringan putus di tengah — penyaringnya masih bisa
                     dipakai seperti formulir biasa. --}}
                <button type="submit" class="mis-tombol mis-tombol-ungu" data-mis-terapkan>
                    <i class="fas fa-search"></i> Terapkan
                </button>

                @if ($adaSaringan)
                    <a href="{{ route('account.kategori-layanan.index') }}"
                        class="mis-tombol mis-tombol-halus" title="Hapus semua saringan">
                        <i class="fas fa-times"></i> Reset
                    </a>
                @endif
            </form>
        </div>
        </details>

        @if ($bolehUbah)
            {{-- Bilah muncul HANYA saat ada yang dipilih. Bilah kosong yang
                 bertuliskan "0 dipilih" menempati ruang tanpa memberi tahu apa
                 pun, dan di ponsel ia menutupi barisnya.

                 Letaknya DI LUAR #ang-hasil: isi wadah itu ditukar tiap kali
                 mengetik, dan bilah yang ikut tertukar akan kehilangan
                 hitungannya di tengah pemilihan. --}}
            <div class="ang-massal" id="ang-massal" hidden>
                <span class="ang-massal-jumlah"><strong id="ang-massal-n">0</strong> dipilih</span>

                <div class="ang-massal-tombol">
                    {{-- Mencentang satu per satu 34 baris yang perlu dicek berarti
                         empat halaman; tombol ini mencentang seluruh baris yang
                         SEDANG tampil, dan pilihannya bertahan saat berpindah
                         halaman. --}}
                    <button type="button" class="mis-tombol mis-tombol-halus" id="ang-massal-semua">
                        <i class="fas fa-check-double mis-ikon-ungu"></i> Pilih semua
                    </button>
                    <button type="button" class="mis-tombol mis-tombol-halus" data-massal="active">
                        <i class="fas fa-check-circle"></i> Aktifkan
                    </button>
                    <button type="button" class="mis-tombol mis-tombol-halus" data-massal="non active">
                        <i class="fas fa-times-circle"></i> Nonaktifkan
                    </button>
                    <button type="button" class="mis-tombol mis-tombol-halus" data-massal="draft">
                        <i class="fas fa-pen"></i> Jadikan draf
                    </button>
                    <button type="button" class="mis-tombol mis-tombol-hapus" id="ang-massal-hapus">
                        <i class="fas fa-trash-alt"></i> Hapus
                    </button>
                    <button type="button" class="mis-tombol mis-tombol-halus" id="ang-massal-batal">
                        Batal
                    </button>
                </div>
            </div>
        @endif

        {{-- role=status + aria-live: isi bagian ini ditukar diam-diam tiap
             ketikan. Tanpa penanda ini, pembaca layar tidak mengumumkan apa pun
             dan orangnya tidak tahu daftarnya sudah berubah. --}}
        <div class="mis-hasil" id="ang-hasil" role="status" aria-live="polite" aria-atomic="false">
            @if ($angkatan->isEmpty())
                {{-- Judulnya ikut berubah, bukan cuma subjudulnya: "Belum ada
                     angkatan" pada daftar yang sedang disaring adalah kalimat
                     yang tidak benar — angkatannya ada, cuma tidak cocok. --}}
                @if ($adaSaringan)
                    <div class="mis-kosong mis-kosong-cari">
                        <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-search"></i></span>
                        <p class="mis-kosong-judul">Tidak ada yang cocok</p>
                        <p class="mis-kosong-teks">
                            @if ($cari !== '')
                                Tidak ada angkatan bernama &ldquo;<strong>{{ $cari }}</strong>&rdquo;.
                            @endif
                            Coba kata kunci lain, atau hapus saringannya.
                        </p>
                        <a href="{{ route('account.kategori-layanan.index') }}"
                            class="mis-tombol mis-tombol-halus mis-kosong-aksi">
                            <i class="fas fa-times"></i> Hapus saringan
                        </a>
                    </div>
                @else
                    <div class="mis-kosong">
                        <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
                        <p class="mis-kosong-judul">Belum ada angkatan</p>
                        <p class="mis-kosong-teks">Buat angkatan pertama lewat tombol di atas.</p>
                    </div>
                @endif
            @else
                <div class="mis-tabel-bungkus">
                    <table class="mis-tabel mis-tabel-kartu ang-tabel">
                        <thead>
                            {{-- aria-sort menyebut kolom mana yang sedang mengurutkan;
                                 judul tautannya berbunyi "Urutkan menurut ..." dan itu
                                 menjelaskan apa yang terjadi KALAU ditekan, bukan
                                 keadaan sekarang. --}}
                            @php
                                $ariaUrut = fn ($k) => $urut === $k
                                    ? ($arahKode === 'asc' ? 'ascending' : 'descending')
                                    : 'none';

                                // Saringan yang ikut dibawa tiap tautan pengurut; tanpa
                                // ini, mengurutkan membuang layanan dan kata kunci yang
                                // sedang dipakai dan daftarnya melompat kembali ke awal.
                                //
                                // SEMUA saringan, bukan tiga. 'perlu' sempat tertinggal:
                                // menyaring "Draf kadaluwarsa" lalu menekan kepala kolom
                                // mengembalikan seluruh 60 baris, dan yang terbaca orang
                                // cuma "urutannya berubah".
                                $bawaUrut = array_filter([
                                    'layanan' => $layanan,
                                    'status' => $status,
                                    'perlu' => $perlu,
                                    'periode' => $periode,
                                    'cari' => $cari !== '' ? $cari : null,
                                ]);
                            @endphp
                            <tr>
                                @if ($bolehUbah)
                                    <th class="ang-centang-sel">
                                        <label class="mis-centang-bungkus">
                                            <input type="checkbox" id="ang-pilih-semua" class="mis-centang"
                                                aria-label="Pilih semua angkatan di halaman ini">
                                        </label>
                                    </th>
                                @endif
                                {{-- aria-sort memberi tahu pembaca layar kolom mana yang
                                     sedang mengurutkan dan ke arah mana; tanpa itu
                                     panah di layar tidak berarti apa-apa bagi mereka. --}}
                                <th class="ang-k-nama" aria-sort="{{ $ariaUrut('nama') }}">
                                    @include('partials.urut-kolom', ['rute' => 'account.kategori-layanan.index',
                                        'bawa' => $bawaUrut, 'arah' => $arahKode, 'kolom' => 'nama', 'label' => 'Angkatan'])
                                </th>
                                <th class="ang-k-layanan" aria-sort="{{ $ariaUrut('layanan') }}">
                                    @include('partials.urut-kolom', ['rute' => 'account.kategori-layanan.index',
                                        'bawa' => $bawaUrut, 'arah' => $arahKode, 'kolom' => 'layanan', 'label' => 'Layanan'])
                                </th>
                                <th class="ang-k-tanggal" aria-sort="{{ $ariaUrut('mulai') }}">
                                    @include('partials.urut-kolom', ['rute' => 'account.kategori-layanan.index',
                                        'bawa' => $bawaUrut, 'arah' => $arahKode, 'kolom' => 'mulai', 'label' => 'Tanggal'])
                                </th>
                                <th class="ang-k-kuota" aria-sort="{{ $ariaUrut('sisa_kuota') }}">
                                    @include('partials.urut-kolom', ['rute' => 'account.kategori-layanan.index',
                                        'bawa' => $bawaUrut, 'arah' => $arahKode, 'kolom' => 'sisa_kuota', 'label' => 'Kuota'])
                                </th>
                                <th class="ang-k-biaya" aria-sort="{{ $ariaUrut('biaya') }}">
                                    @include('partials.urut-kolom', ['rute' => 'account.kategori-layanan.index',
                                        'bawa' => $bawaUrut, 'arah' => $arahKode, 'kolom' => 'biaya', 'label' => 'Biaya'])
                                </th>
                                <th class="ang-k-status" aria-sort="{{ $ariaUrut('status') }}">
                                    @include('partials.urut-kolom', ['rute' => 'account.kategori-layanan.index',
                                        'bawa' => $bawaUrut, 'arah' => $arahKode, 'kolom' => 'status', 'label' => 'Status'])
                                </th>
                                <th class="ang-k-aksi text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($angkatan as $a)
                                @php($tentang = $katalog[$a->layanan] ?? null)
                                <tr>
                                    @if ($bolehUbah)
                                        <td class="ang-centang-sel">
                                            {{-- Kelasnya ang-centang, BUKAN ang-pilih: nama itu
                                                 sudah dipakai pil layanan di atas, dan
                                                 querySelectorAll('.ang-pilih') ikut menjaring
                                                 keenam pil sehingga centang-semua tak pernah
                                                 bisa penuh. --}}
                                            <label class="mis-centang-bungkus">
                                                <input type="checkbox" class="ang-centang mis-centang"
                                                    value="{{ $a->id }}"
                                                    aria-label="Pilih {{ $a->nama }}">
                                            </label>
                                        </td>
                                    @endif
                                    <td class="mis-td-utama">
                                        <span class="mis-sel-utama">
                                            {{-- Sampulnya ditampilkan kecil: angkatan tanpa sampul
                                                 langsung terlihat di antara yang punya, dan itulah
                                                 yang tampil di halaman publik. --}}
                                            @if ($a->alamat_sampul)
                                                <img src="{{ $a->alamat_sampul }}" alt="" class="ang-sampul" loading="lazy">
                                            @else
                                                <span class="mis-medali kecil {{ $tentang['warna'] ?? 'mis-abu' }}" aria-hidden="true">
                                                    <i class="fas {{ $tentang['ikon'] ?? 'fa-layer-group' }}"></i>
                                                </span>
                                            @endif
                                            <span class="mis-sel-teks">
                                                <p class="ang-nama">
                                                    <a href="{{ route('account.kategori-layanan.detail', $a) }}">{{ $a->nama }}</a>
                                                </p>
                                                <p class="ang-ket">
                                                    {{ $a->nama_ke ? 'Angkatan ke-' . $a->nama_ke : 'Tanpa nomor' }}
                                                    @if ($a->lokasi) &middot; {{ $a->lokasi }} @endif
                                                </p>
                                            </span>
                                        </span>
                                    </td>

                                    <td data-judul="Layanan">
                                        <span class="ang-ket">{{ $a->nama_layanan }}</span>
                                        @if ($a->nama_varian)
                                            <span class="mis-pil mis-pil-ungu">{{ $a->nama_varian }}</span>
                                        @endif
                                    </td>

                                    <td data-judul="Tanggal">
                                        <span class="ang-ket">
                                            {{-- Bentuk pendek: kolomnya sempit, dan nama bulan
                                                 lengkap membuat tanggal terpanjang patah dua
                                                 baris. Halaman rincian tetap yang lengkap. --}}
                                            {{ \App\Support\RentangTanggal::tulis(
                                                $a->mulai ? \Carbon\Carbon::parse($a->mulai) : null,
                                                $a->selesai ? \Carbon\Carbon::parse($a->selesai) : null,
                                                true
                                            ) ?: '—' }}
                                        </span>
                                    </td>

                                    <td data-judul="Kuota">
                                        <span class="ang-kuota">
                                            <strong>{{ $a->sisa_kuota ?? '—' }}</strong>
                                            <span>dari {{ $a->total_kuota ?? '—' }}</span>
                                        </span>

                                        @if ($a->kuota_habis)
                                            {{-- Kuota habis harus kelihatan tanpa membandingkan
                                                 dua angka sendiri. --}}
                                            <span class="mis-pil mis-pil-merah">
                                                <i class="fas fa-user-friends"></i> Penuh
                                            </span>
                                        @endif

                                        {{-- "peserta", bukan "pendaftar": yang dihitung ORANG,
                                             dan satu pendaftaran boleh membawa rombongan.
                                             Jumlah barisnya ikut disebut hanya kalau memang
                                             berbeda, supaya yang membuka daftarnya tidak
                                             bingung menemukan 5 baris untuk 25 orang. --}}
                                        @if ($a->jumlah_pendaftar > 0 && $tautanPendaftar($a))
                                            {{-- "14 dari 25" lalu berhenti di situ menyisakan
                                                 pertanyaan "siapa"; jawabannya satu klik. --}}
                                            <a href="{{ $tautanPendaftar($a) }}" class="ang-tautan">
                                                lihat {{ $a->sebutPeserta() }}
                                            </a>
                                        @elseif ($a->jumlah_pendaftar > 0)
                                            <span class="ang-ket">{{ $a->sebutPeserta() }}</span>
                                        @elseif ($a->belumPunyaPendaftaran())
                                            {{-- Angkatan Scopus Cafe / Clinik Scopus: kuotanya
                                                 TIDAK akan pernah berkurang sendiri karena
                                                 layanannya belum punya tabel pendaftaran. Tanpa
                                                 kalimat ini, "20 dari 20" terbaca seperti belum
                                                 ada yang daftar. --}}
                                            <span class="ang-ket" title="Layanan ini belum punya layar pendaftaran, jadi sisa kuotanya diisi tangan">
                                                pendaftaran belum tercatat
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Harga dan promonya dibungkus jadi SATU nilai. Di modus
                                         kartu, selnya jadi flex (label | nilai), jadi dua unsur
                                         terpisah berubah jadi dua item berdampingan dan angka
                                         yang panjang terpaksa patah jadi "Rp" lalu "4.500.000". --}}
                                    <td data-judul="Biaya">
                                        <span class="ang-nilai">
                                            <span class="ang-nama">
                                                {{ (int) $a->biaya > 0 ? 'Rp ' . number_format((int) $a->biaya, 0, ',', '.') : '—' }}
                                            </span>
                                            @if ((int) $a->total_biaya > 0 && (int) $a->total_biaya !== (int) $a->biaya)
                                                <span class="ang-ket">
                                                    promo Rp {{ number_format((int) $a->total_biaya, 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </span>
                                    </td>

                                    <td data-judul="Status">
                                        {{-- Berikon seperti lencana status di Data Pelanggan:
                                             warna saja tidak terbaca orang yang buta warna,
                                             dan hijau/kuning adalah pasangan yang paling
                                             sering tertukar. --}}
                                        @php($rupa = [
                                            'active' => ['mis-pil-hijau', 'Aktif', 'fa-check'],
                                            'non active' => ['mis-pil-abu', 'Nonaktif', 'fa-pause'],
                                            'draft' => ['mis-pil-kuning', 'Draf', 'fa-pen'],
                                        ])
                                        @php($s = $rupa[$a->status] ?? ['mis-pil-abu', $a->status, 'fa-question'])
                                        <span class="mis-pil {{ $s[0] }}"><i class="fas {{ $s[2] }}"></i> {{ $s[1] }}</span>

                                        @if ($a->sudah_lewat)
                                            {{-- Tidak ada apa pun yang menutup angkatan otomatis,
                                                 jadi ia bisa terpajang "Aktif" berbulan-bulan
                                                 sesudah acaranya selesai. --}}
                                            @if ($bolehUbah)
                                                {{-- Memberi tahu tanpa menawarkan jalan keluar cuma
                                                     setengah pekerjaan: menekannya menonaktifkan
                                                     angkatan itu. --}}
                                                <button type="button" class="mis-pil mis-pil-kuning tar-pil-tombol"
                                                    data-nonaktifkan="{{ $a->id }}" data-nama="{{ $a->nama }}"
                                                    title="Masih aktif padahal tanggalnya sudah lewat — tekan untuk menonaktifkan">
                                                    <i class="fas fa-exclamation-triangle"></i> Lewat
                                                </button>
                                            @else
                                                <span class="mis-pil mis-pil-kuning"
                                                    title="Masih aktif padahal tanggalnya sudah lewat">
                                                    <i class="fas fa-exclamation-triangle"></i> Lewat
                                                </span>
                                            @endif
                                        @endif

                                        {{-- Ketiga keadaan "perlu dicek" jadi SATU lencana,
                                             bukan tiga.

                                             Tiga lencana terpisah membuat baris Bibliometrik
                                             memuat empat lencana sekaligus — terukur menuntut
                                             504px di kartu selebar 360px, jadi tabelnya harus
                                             digeser ke samping, dan di layar lebar barisnya
                                             membengkak dari 71px jadi 118px.

                                             Yang perlu diketahui sekilas cuma "baris ini
                                             perlu dicek"; perinciannya menyusul di tooltip
                                             dan di halaman rincian. --}}
                                        {{-- Alasannya dihitung di model, BUKAN di blok
                                             @php di sini: blok seperti itu akan dipasangkan
                                             Blade dengan @php(...) sebaris yang sudah ada di
                                             atas, dan semua di antaranya ikut tertelan. --}}
                                        @if ($a->perlu_dicek_lain)
                                            <span class="mis-pil mis-pil-merah"
                                                title="Perlu dicek: {{ implode('; ', $a->perlu_dicek_lain) }}">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ count($a->perlu_dicek_lain) > 1
                                                    ? 'Perlu dicek ' . count($a->perlu_dicek_lain) . ' hal'
                                                    : 'Perlu dicek' }}
                                            </span>
                                        @endif
                                    </td>

                                    <td data-judul="Aksi">
                                        <div class="ang-aksi">
                                            @if ($bolehUbah)
                                                <a href="{{ route('account.kategori-layanan.edit', $a) }}"
                                                    class="mis-tombol mis-tombol-garis" title="Lihat &amp; ubah">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                {{-- Menggandakan: 41 angkatan Yogyakarta isinya
                                                     nyaris sama persis, dan semuanya diketik ulang. --}}
                                                <form method="POST" class="ang-gandakan"
                                                    action="{{ route('account.kategori-layanan.gandakan', $a) }}">
                                                    @csrf
                                                    <button type="submit" class="mis-tombol mis-tombol-garis"
                                                        title="Gandakan jadi rancangan baru">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </form>

                                                <button type="button" class="mis-tombol mis-tombol-bahaya"
                                                    title="Hapus angkatan"
                                                    data-hapus="{{ route('account.kategori-layanan.destroy', $a) }}"
                                                    data-nama="{{ $a->nama }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @else
                                                <span class="ang-ket">—</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $angkatan->links('vendor.pagination.bootstrap-4') }}
            @endif
        </div>

    </section>
</div>
@endsection

@push('scripts')
<script>
    /*
     * Pilihan massal.
     *
     * Penangannya dipasang di wadah #ang-hasil, bukan di tiap kotak centang:
     * isi wadah itu ditukar tiap kali mengetik atau berpindah halaman, dan
     * pemasangan langsung akan hilang begitu isinya diganti pertama kali.
     *
     * Pilihannya disimpan di Set, BUKAN dibaca dari kotak yang sedang tampil.
     * Dengan pilihan yang hanya hidup di kotaknya, mencentang sepuluh angkatan
     * lalu tanpa sengaja mengetik satu huruf di kotak cari menghapusnya tanpa
     * sepatah kata. Disimpan terpisah, pilihan bertahan menyeberangi pencarian
     * dan halaman — sama persis dengan Data Pelanggan.
     */
    (function () {
        const hasil = document.getElementById('ang-hasil');
        const bilah = document.getElementById('ang-massal');
        if (!hasil || !bilah) return;

        const angka = document.getElementById('ang-massal-n');
        const dipilih = new Set();

        function kotakTampil() {
            return [...hasil.querySelectorAll('.ang-centang')];
        }

        function segarkan() {
            const n = dipilih.size;
            angka.textContent = n;
            bilah.hidden = n === 0;

            const kotak = kotakTampil();
            const semua = hasil.querySelector('#ang-pilih-semua');

            if (semua) {
                const tercentang = kotak.filter(function (k) { return dipilih.has(k.value); }).length;
                semua.checked = kotak.length > 0 && tercentang === kotak.length;
                // Sebagian terpilih ditandai setengah, bukan kosong: kosong
                // terbaca seolah tidak ada yang dipilih sama sekali.
                semua.indeterminate = tercentang > 0 && tercentang < kotak.length;
            }
        }

        // Kotak yang baru digambar disamakan dengan pilihan yang tersimpan;
        // tanpa ini, menyaring lalu kembali membuat centangnya hilang di layar
        // padahal pilihannya masih tercatat.
        function pulihkan() {
            kotakTampil().forEach(function (k) { k.checked = dipilih.has(k.value); });
            segarkan();
        }

        hasil.addEventListener('change', function (e) {
            if (e.target.id === 'ang-pilih-semua') {
                kotakTampil().forEach(function (k) {
                    k.checked = e.target.checked;
                    if (e.target.checked) dipilih.add(k.value); else dipilih.delete(k.value);
                });
                segarkan();
                return;
            }

            if (!e.target.classList.contains('ang-centang')) return;

            if (e.target.checked) dipilih.add(e.target.value); else dipilih.delete(e.target.value);
            segarkan();
        });

        document.getElementById('ang-massal-semua').addEventListener('click', function () {
            kotakTampil().forEach(function (k) { dipilih.add(k.value); });
            pulihkan();
        });

        document.getElementById('ang-massal-batal').addEventListener('click', function () {
            dipilih.clear();
            pulihkan();
        });

        /*
         * Menghapus massal lewat jalurnya sendiri, dan konfirmasinya menyebut
         * angkanya. Angkatan yang sudah punya pendaftar dilewati peladen, bukan
         * membatalkan seluruh tindakan — jawabannya menyebut berapa yang
         * dilewati.
         */
        document.getElementById('ang-massal-hapus').addEventListener('click', function () {
            if (dipilih.size === 0) return;

            window.misKonfirmasi({
                judul: 'Hapus angkatan terpilih?',
                pesan: '%s dihapus dari daftar. Yang sudah punya peserta akan dilewati. '
                    + 'Masing-masing masih bisa dikembalikan lewat halaman rinciannya.',
                sorot: dipilih.size + ' angkatan',
                tombol: 'Ya, hapus',
                jenis: 'bahaya',
            }).then(function (ya) {
                if (!ya) return;

                const isi = new FormData();
                [...dipilih].forEach(function (x) { isi.append('id[]', x); });

                fetch(@json(route('account.kategori-layanan.massal-hapus')), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: isi,
                })
                    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                    .then(function (j) {
                        window.misToast(j.ok && j.d.success ? 'berhasil' : 'gagal',
                            j.d.message || 'Penghapusannya gagal.');
                        if (j.ok && j.d.success) setTimeout(function () { window.location.reload(); }, 1200);
                    })
                    .catch(function () { window.misToast('gagal', 'Tidak bisa menghubungi peladen.'); });
            });
        });

        // Daftar baru selesai dimuat: centangnya dipasang ulang.
        document.addEventListener('mis:saring-selesai', pulihkan);

        function ubahStatus(id, status, kalimat, sorot) {
            window.misKonfirmasi({
                judul: 'Ubah status angkatan?',
                pesan: kalimat,
                sorot: sorot,
                tombol: 'Ya, ubah',
                jenis: 'tanya',
                glif: 'fa-layer-group',
            }).then(function (ya) {
                if (!ya) return;

                const isi = new FormData();
                id.forEach(function (x) { isi.append('id[]', x); });
                isi.append('status', status);

                fetch(@json(route('account.kategori-layanan.massal')), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: isi,
                })
                    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                    .then(function (j) {
                        window.misToast(j.ok && j.d.success ? 'berhasil' : 'gagal',
                            j.d.message || 'Tindakannya gagal.');

                        /*
                         * Dimuat ulang supaya lencana statusnya ikut berubah —
                         * tetapi diberi jeda dulu. Dimuat ulang seketika, toast
                         * "10 angkatan diubah jadi Nonaktif." ikut terbuang
                         * sebelum terbaca, dan orangnya tidak tahu berapa yang
                         * kena.
                         */
                        if (j.ok && j.d.success) setTimeout(function () { window.location.reload(); }, 900);
                    })
                    .catch(function () { window.misToast('gagal', 'Tidak bisa menghubungi peladen.'); });
            });
        }

        bilah.querySelectorAll('[data-massal]').forEach(function (b) {
            b.addEventListener('click', function () {
                if (dipilih.size === 0) return;

                ubahStatus([...dipilih], b.dataset.massal,
                    '%s akan diubah statusnya. Angkatan yang sudah punya pendaftar tetap utuh; '
                        + 'yang berubah cuma tampil atau tidaknya di halaman publik.',
                    dipilih.size + ' angkatan');
            });
        });

        // Lencana "Lewat" memakai jalur yang sama dengan satu id. Dipasang di
        // wadahnya juga, sebab lencananya ikut tertukar saat menyaring.
        hasil.addEventListener('click', function (e) {
            const pil = e.target.closest('[data-nonaktifkan]');
            if (!pil) return;

            // %s disulih nama angkatannya, jadi kalimatnya harus dimulai dengan
            // nama itu. "Tanggal %s sudah lewat" terbaca "Tanggal SCOPUS CAMP
            // YOGYAKARTA sudah lewat" — bukan kalimat Indonesia.
            ubahStatus([pil.dataset.nonaktifkan], 'non active',
                '%s sudah selesai tanggalnya tetapi statusnya masih Aktif. Nonaktifkan sekarang?',
                pil.dataset.nama);
        });

        segarkan();
    })();

    /**
     * Pemberitahuan berisi tombol "Urungkan".
     *
     * Dibuat di sini, bukan lewat window.misToast: pemberitahuan bersama itu
     * hanya menerima teks, dan menambahinya tombol akan mengubah perilaku
     * sepuluh layar lain yang memakainya.
     */
    function tawarkanUrung(alamat, nama) {
        const kotak = document.createElement('div');
        kotak.className = 'ang-urung';
        kotak.setAttribute('role', 'status');

        const teks = document.createElement('span');
        teks.textContent = (nama || 'Angkatan') + ' dihapus.';

        const borang = document.createElement('form');
        borang.method = 'POST';
        borang.action = alamat;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const tombol = document.createElement('button');
        tombol.type = 'submit';
        tombol.className = 'ang-urung-tombol';
        tombol.textContent = 'Urungkan';

        borang.appendChild(csrf);
        borang.appendChild(tombol);
        kotak.appendChild(teks);
        kotak.appendChild(borang);
        document.body.appendChild(kotak);

        // Lima belas detik: cukup untuk sadar dan menekan, tidak cukup lama
        // untuk menutupi daftar yang sedang dipakai.
        setTimeout(function () { kotak.remove(); }, 15000);
    }

    document.addEventListener('click', function (e) {
        const tombol = e.target.closest('[data-hapus]');
        if (!tombol) return;

        window.misKonfirmasi({
            judul: 'Hapus angkatan ini?',
            pesan: '%s dihapus dari daftar. Angkatan yang sudah punya peserta tidak bisa dihapus.',
            sorot: tombol.dataset.nama,
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
                    if (!(j.ok && j.d.success)) {
                        window.misToast('gagal', j.d.message);
                        return;
                    }

                    tombol.closest('tr').remove();

                    /* Jalan kembali ditawarkan di tempat, bukan disembunyikan di
                       halaman lain: yang menghapus angkatan yang salah baru sadar
                       beberapa detik kemudian, dan saat itu ia masih menatap layar
                       yang sama. */
                    if (j.d.pulihkan) {
                        tawarkanUrung(j.d.pulihkan, tombol.dataset.nama);
                    } else {
                        window.misToast('berhasil', j.d.message);
                    }
                })
                .catch(function () { window.misToast('gagal', 'Tidak bisa menghubungi peladen.'); });
        });
    });
</script>
@endpush
