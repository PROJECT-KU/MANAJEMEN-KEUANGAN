@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Rincian Pendaftaran | MIS Rumah Scopus
@stop

@push('gaya')
    <style>
        /*
         * Dua kolom: kartu identitas di kiri (tetap), kartu bertab di kanan
         * (melar) — kerangka yang sama dengan layar Profil dan Data Pelanggan.
         *
         * align-items: start, BUKAN stretch. Dengan stretch, kartu identitas
         * yang isinya pendek ikut setinggi kolom tab dan sisanya jadi petak
         * putih DI DALAM kartunya; di layar Data Pelanggan pernah terukur
         * 341px kosong. Kartu yang berhenti di ujung isinya jauh lebih enak
         * dilihat daripada kartu yang dipaksa rata bawah.
         */
        .rin-kisi {
            display: grid;
            grid-template-columns: minmax(min(100%, 290px), 330px) minmax(0, 1fr);
            gap: var(--mis-jarak);
            align-items: start;
        }

        @media (max-width: 991.98px) {
            .rin-kisi {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        /* ------------------------------------------- kartu identitas */

        .rin-identitas {
            text-align: center;
        }

        /*
         * Medali besar: MODIFIER di atas .mis-medali, bukan kelas baru.
         *
         * Dengan begitu ia ikut mewarisi seluruh perbaikan yang sudah ada di
         * sana — latar dari `var(--warna)` yang disetel kelas warnanya,
         * `place-items: center` yang memusatkan glif tanpa bantalan
         * kira-kira, `font-size: inherit` pada glifnya yang mengalahkan
         * aturan global `.fas { font-size: 20px }`, dan `margin: 0 !important`
         * yang membatalkan margin 4px yang style.css pasang pada ikon di
         * dalam <a>. Menulis kelas sendiri berarti mengulang keempatnya, dan
         * melewatkan satu saja membuat ikonnya meleset dari titik tengah.
         */
        .mis-medali.rin-besar {
            width: 62px;
            height: 62px;
            flex: 0 0 62px;
            margin: 0 auto 13px;
            border-radius: 20px;
            font-size: 1.6rem;
        }

        .rin-nama {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.3;
            color: var(--mis-tinta);
            overflow-wrap: anywhere;
        }

        .rin-nomor {
            margin: 4px 0 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .76rem;
            color: var(--mis-tinta-3);
            overflow-wrap: anywhere;
        }

        .rin-pil-baris {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 6px;
            margin-top: 11px;
        }

        /* Baris keterangan: medali mini + label + nilai. Kisi tiga kolom
           dengan medali merentang dua baris, jadi ia sejajar dengan BLOK
           teksnya — pola yang sama dipakai halaman Profil. */
        .rin-baris {
            display: grid;
            grid-template-columns: 25px minmax(0, 1fr);
            align-items: center;
            gap: 2px 10px;
            padding: 11px 0;
            text-align: left;
            border-top: 1px dashed var(--mis-garis);
        }

        .rin-baris:first-of-type {
            margin-top: 15px;
        }

        .rin-baris .mis-medali {
            grid-row: span 2;
        }

        .rin-baris-label {
            margin: 0;
            font-size: .64rem;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: var(--mis-tinta-4);
        }

        .rin-baris-nilai {
            margin: 0;
            font-size: .84rem;
            color: var(--mis-tinta);
            overflow-wrap: anywhere;
        }

        .rin-baris-nilai a {
            color: var(--mis-tinta);
            text-decoration: none;
        }

        .rin-baris-nilai a:hover {
            color: var(--mis-ungu-tinta, #6366f1);
            text-decoration: underline;
        }

        .rin-samar {
            color: var(--mis-tinta-4);
        }

        .rin-bukti-gambar {
            display: block;
            width: 100%;
            max-height: 190px;
            object-fit: contain;
            margin-top: 9px;
            border: 1px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #f8fafc;
        }

        /*
         * Tab di halaman ini menyesuaikan lebarnya dengan tulisannya.
         *
         * Bawaan .mis-tab memberi tiap tab `flex: 1 1 0` — lebar sama rata.
         * Dengan lima tab di kolom kanan, bagian tiap tab tinggal seperlima,
         * dan tulisan tab terpanjang tidak muat di sana. Karena
         * `.mis-tab .nav-link` ber-`white-space: nowrap`, ia TIDAK membungkus
         * melainkan meluber keluar kotaknya sendiri: terukur 11px di 1280px
         * dan 10px di 1024px, menumpuk ke tab sebelahnya, dan tidak ada galat
         * apa pun yang menunjukkannya.
         *
         * `flex: 0 1 auto` membuat tiap tab selebar isinya dan barisnya
         * membungkus kalau memang tidak cukup — bukan terklip. Pemilihnya
         * lewat id supaya layar lain tetap memakai bawaannya.
         *
         * Di bawah 768px aturan bersamanya mengubah strip jadi penggulung
         * satu baris berpenanda tepi; timpaan ini tidak dipakai di sana.
         */
        @media (min-width: 768px) {
            #rin-tab > li {
                flex: 0 1 auto;
            }
        }

        /* ------------------------------------------------ isi tab */

        /*
         * Tiga angka ringkas memakai ubin BERSAMA .mis-ubin.
         *
         * Dijaga RupaBersamaTest: perangkat ubin ringkasan lahir di layar Data
         * Pelanggan, dan menyalinnya dengan awalan layar sendiri berarti tiap
         * perbaikan rupa dikerjakan dua kali. Versi pertama bagian ini memang
         * begitu, dan penjaganya menangkapnya.
         *
         * Yang TIDAK dipakai pembungkus .mis-ringkas-nya: di bawah 1100px ia
         * berubah jadi penggulung mendatar, dan guliran mendatar di dalam tab
         * tanpa penanda tepi adalah persis yang dilarang panduan. Kisi di sini
         * membungkus ke bawah.
         *
         * minmax(min(100%, 155px), 1fr): lebar tetap pada argumen pertama
         * adalah lantai yang tidak bisa ditembus, dan di 320px ruang yang
         * tersedia cuma sekitar 258px.
         */
        .rin-angka {
            display: grid;
            /* auto-fit DIBATASI tiga kolom lewat lebar maksimum jalurnya:
               tanpa itu, di 1440px kisinya membuka jalur keempat yang kosong
               dan ketiga ubinnya menyusut tanpa alasan. */
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 155px), 1fr));
            gap: 11px;
            margin-bottom: var(--mis-jarak);
        }

        /* Dipatok tiga kolom sejak 576px, bukan 992px: di 820px kolom kanan
           sudah selebar ~758px, dan auto-fit di sana membuka jalur KEEMPAT
           yang kosong sehingga ketiga ubinnya menyusut tanpa alasan. */
        @media (min-width: 576px) {
            .rin-angka {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        /* Kisi isian: tiga kolom di layar lebar, dua di tablet, satu di
           ponsel — tanpa titik putus yang harus dijaga satu per satu. */
        .rin-isian-kisi {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 215px), 1fr));
            gap: 14px;
            align-content: start;
        }

        .rin-isian-penuh {
            grid-column: 1 / -1;
        }

        .rin-bagian + .rin-bagian {
            margin-top: 19px;
            padding-top: 17px;
            border-top: 1px dashed var(--mis-garis);
        }

        .rin-bagian-judul {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 0 0 13px;
        }

        .rin-bagian-judul span.teks {
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: var(--mis-tinta-3);
        }

        .rin-kaki {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 19px;
            padding-top: 16px;
            border-top: 1px solid var(--mis-garis);
        }

        .rin-status-baris {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 11px;
        }

        .rin-status-baris .mis-isian {
            flex: 1 1 210px;
            min-width: 0;
        }

        /* Kotak pemberitahuan. Kuning untuk yang perlu diketahui, merah untuk
           yang tidak bisa diurungkan — warnanya mengikuti keluarga yang sama
           dengan lencana status, bukan warna sendiri. */
        .rin-nota {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 13px 0 0;
            padding: 11px 14px;
            border: 1px solid #fde68a;
            border-radius: var(--mis-radius-kecil);
            background: #fffbeb;
            font-size: .79rem;
            line-height: 1.5;
            color: #92400e;
        }

        .rin-nota i {
            flex: 0 0 auto;
            margin-top: 2px;
            font-size: inherit;
        }

        .rin-nota-bahaya {
            border-color: #fecdd3;
            background: #fff1f2;
            color: #9f1239;
        }

        .rin-nota-biru {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        /* Daftar peserta rombongan. Bernomor, sebab urutannya berarti: itu
           yang dipakai menerbitkan sertifikat. */
        .rin-peserta {
            display: grid;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
            counter-reset: peserta;
        }

        .rin-peserta li {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 13px;
            border: 1px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #fff;
            font-size: .85rem;
            color: var(--mis-tinta);
        }

        .rin-peserta li::before {
            counter-increment: peserta;
            content: counter(peserta);
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            width: 26px;
            height: 26px;
            border-radius: 9px;
            background: #f1f5f9;
            font-size: .72rem;
            font-weight: 800;
            color: var(--mis-tinta-3);
        }

        .rin-peserta-nama {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .rin-peserta-surel {
            display: block;
            font-size: .74rem;
            color: var(--mis-tinta-4);
        }

        @media (max-width: 575.98px) {
            .mis-medali.rin-besar {
                width: 54px;
                height: 54px;
                flex: 0 0 54px;
                font-size: 1.4rem;
            }

            .rin-kaki .mis-tombol {
                flex: 1 1 100%;
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
@php
    use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
    use App\Support\PesananPelanggan;

    $keadaan = Pendaftaran::keadaanDari($pendaftaran->status);
    $rupa = Pendaftaran::KEADAAN[$keadaan]
        ?? ['label' => 'Belum dikenali', 'warna' => 'abu', 'ikon' => 'fa-circle-notch'];

    $bukti = $baris ? Pendaftaran::buktiBaris($baris) : ['url' => null, 'ada' => false, 'nilai' => false];
    $sesiBaris = $baris ? Pendaftaran::sesiBaris($baris) : null;
    $waktuBaris = $baris ? Pendaftaran::waktuBaris($baris) : null;

    $nomor = $pendaftaran->id_transaksi ?: ($pendaftaran->id_pemesanan ?: $pendaftaran->getKey());
    $namaOrang = $pendaftaran->nama ?: ($pendaftaran->nama_pemesan ?: 'Tanpa nama');
    $emailOrang = $pendaftaran->email ?: ($pendaftaran->email_pemesan ?: '');
    $telpOrang = $pendaftaran->telp ?: ($pendaftaran->telp_pemesan ?: '');
    $afiliasiOrang = $pendaftaran->affiliasi ?: ($pendaftaran->afiliasi_pemesan ?: '');
    $wa = PesananPelanggan::nomorWa($telpOrang);

    $jumlahOrang = max(1, (int) $pendaftaran->jumlah_pendaftar);
    $totalBayar = (int) ($pendaftaran->total_pembayaran ?: $pendaftaran->total_keseluruhan_pembayaran);
    $kodeUnik = (int) ($pendaftaran->kode_unik ?: $pendaftaran->kode_unik_pembayaran);
    $diskon = (int) ($pendaftaran->nominal_diskon ?: $pendaftaran->diskon);

    $berangkatan = Pendaftaran::berangkatan($layanan);

    /*
     * Label dan pengelompokan medan — urusan tampilan, jadi di sini.
     *
     * Medan yang BOLEH disunting datang dari $medan, yaitu daftar putih di
     * UbahDataPendaftaran. Medan tanpa label di sini tidak pernah tergambar,
     * jadi menambah medan di sana menuntut menambah labelnya juga — itu
     * disengaja, supaya tidak ada isian tanpa nama yang terbaca orang.
     */
    $label = [
        'nama' => 'Nama lengkap', 'nama_pemesan' => 'Nama pemesan',
        'email' => 'Email', 'email_pemesan' => 'Email',
        'telp' => 'Nomor WhatsApp', 'telp_pemesan' => 'Nomor WhatsApp',
        'affiliasi' => 'Afiliasi / instansi', 'afiliasi_pemesan' => 'Afiliasi / instansi',
        'note' => 'Catatan panitia',
        'kendala' => 'Kendala', 'desc_kendala' => 'Keterangan kendala',

        'kategori_id' => 'Angkatan', 'jumlah_pendaftar' => 'Jumlah orang',
        'ppn' => 'PPN', 'kode_unik' => 'Kode unik',
        'kode_diskon' => 'Kode diskon', 'nominal_diskon' => 'Nominal potongan',
        'total_pembayaran' => 'Total bayar',
        'total_keseluruhan_pembayaran' => 'Total keseluruhan',

        'tanggal_pemesanan' => 'Tanggal pemesanan',
        'tanggal_reschedule' => 'Tanggal jadwal ulang',
        'group_wa' => 'Tautan grup WhatsApp',
        'sesi' => 'Sesi', 'jam_sesi' => 'Jam sesi',
        'waktu_mulai' => 'Mulai', 'waktu_selesai' => 'Selesai', 'lokasi' => 'Lokasi',
        'biaya' => 'Biaya', 'kode_unik_pembayaran' => 'Kode unik',
        'subtotal_pembayaran' => 'Subtotal',
        'sesi_kedua' => 'Sesi', 'waktu_mulai_kedua' => 'Mulai',
        'waktu_selesai_kedua' => 'Selesai', 'lokasi_kedua' => 'Lokasi',
        'biaya_kedua' => 'Biaya', 'kode_unik_pembayaran_kedua' => 'Kode unik',
        'subtotal_pembayaran_kedua' => 'Subtotal',
        'sesi_ketiga' => 'Sesi', 'waktu_mulai_ketiga' => 'Mulai',
        'waktu_selesai_ketiga' => 'Selesai', 'lokasi_ketiga' => 'Lokasi',
        'biaya_ketiga' => 'Biaya', 'kode_unik_pembayaran_ketiga' => 'Kode unik',
        'subtotal_pembayaran_ketiga' => 'Subtotal',
    ];

    $medanPenuh = ['group_wa', 'note', 'desc_kendala', 'lokasi', 'lokasi_kedua', 'lokasi_ketiga'];
    $medanUang = [
        'ppn', 'kode_unik', 'nominal_diskon', 'total_pembayaran',
        'biaya', 'kode_unik_pembayaran', 'subtotal_pembayaran',
        'biaya_kedua', 'kode_unik_pembayaran_kedua', 'subtotal_pembayaran_kedua',
        'biaya_ketiga', 'kode_unik_pembayaran_ketiga', 'subtotal_pembayaran_ketiga',
        'total_keseluruhan_pembayaran',
    ];

    /*
     * Isi tiap tab, disusun sebagai bagian berjudul.
     *
     * Dipisah jadi tab, bukan ditumpuk jadi satu borang panjang: Scopus Kafe
     * punya 26 isian, dan menyuguhkan semuanya sekaligus menuntut menggulung
     * jauh hanya untuk menemukan satu nominal. Satu tab satu borang, jadi
     * menyimpan satu kelompok tidak menuntut mengirim kelompok lainnya.
     */
    $isiTab = [
        'diri' => [
            ['Identitas pendaftar', 'fa-user', 'mis-ungu',
                ['nama', 'nama_pemesan', 'email', 'email_pemesan', 'telp', 'telp_pemesan', 'affiliasi', 'afiliasi_pemesan']],
            ['Kendala yang disampaikan', 'fa-comment-dots', 'mis-biru', ['kendala', 'desc_kendala']],
            ['Catatan panitia', 'fa-sticky-note', 'mis-kuning', ['note']],
        ],
        'bayar' => [
            ['Angkatan & jumlah orang', 'fa-layer-group', 'mis-ungu', ['kategori_id', 'jumlah_pendaftar']],
            ['Nominal', 'fa-money-bill-wave', 'mis-hijau',
                ['ppn', 'kode_unik', 'kode_diskon', 'nominal_diskon', 'total_pembayaran', 'total_keseluruhan_pembayaran']],
        ],
        'sesi' => [
            ['Sesi pertama', 'fa-calendar-check', 'mis-biru',
                ['tanggal_pemesanan', 'sesi', 'jam_sesi', 'waktu_mulai', 'waktu_selesai', 'lokasi', 'biaya', 'kode_unik_pembayaran', 'subtotal_pembayaran']],
            ['Sesi kedua', 'fa-calendar-plus', 'mis-jingga',
                ['sesi_kedua', 'waktu_mulai_kedua', 'waktu_selesai_kedua', 'lokasi_kedua', 'biaya_kedua', 'kode_unik_pembayaran_kedua', 'subtotal_pembayaran_kedua']],
            ['Sesi ketiga', 'fa-calendar-plus', 'mis-kuning',
                ['sesi_ketiga', 'waktu_mulai_ketiga', 'waktu_selesai_ketiga', 'lokasi_ketiga', 'biaya_ketiga', 'kode_unik_pembayaran_ketiga', 'subtotal_pembayaran_ketiga']],
            ['Penjadwalan ulang & grup', 'fa-redo', 'mis-merah', ['tanggal_reschedule', 'group_wa']],
        ],
    ];

    /** Bagian yang benar-benar punya isi untuk layanan ini. */
    $bagianTab = function (string $tab) use ($isiTab, $medan, $label) {
        $hasil = [];

        foreach ($isiTab[$tab] ?? [] as [$judul, $ikon, $warna, $daftar]) {
            $ada = [];

            foreach ($daftar as $kolom) {
                if (isset($medan[$kolom], $label[$kolom])) {
                    $ada[] = $kolom;
                }
            }

            if ($ada !== []) {
                $hasil[] = [$judul, $ikon, $warna, $ada];
            }
        }

        return $hasil;
    };

    $bagianDiri = $bagianTab('diri');
    $bagianBayar = $bagianTab('bayar');
    $bagianSesi = $bagianTab('sesi');

    $adaPeserta = $layanan === 'webinar_eksklusif' && $jumlahOrang > 1;

    /* Deret tab, hanya yang memang punya isi. */
    $tab = [['ringkasan', 'Ringkasan', 'fa-clipboard-check']];

    if ($bagianDiri !== []) {
        $tab[] = ['diri', 'Identitas', 'fa-user-edit'];
    }

    if ($bagianBayar !== []) {
        $tab[] = ['bayar', 'Pembayaran', 'fa-wallet'];
    }

    if ($bagianSesi !== []) {
        $tab[] = ['sesi', 'Jadwal', 'fa-calendar-alt'];
    }

    if ($adaPeserta) {
        $tab[] = ['peserta', 'Peserta', 'fa-users'];
    }

    /*
     * Tab Hapus SELALU ada, juga bagi yang tidak boleh menghapus.
     *
     * Layar tidak boleh menyuguhkan sesuatu yang kirimannya akan ditolak —
     * tetapi ia juga harus menulis ALASANNYA di tempat tombol itu seharusnya
     * berada. Tabnya dibuang sama sekali, karyawan yang mencari cara
     * membatalkan pendaftaran tidak menemukan apa pun dan tidak tahu harus
     * meminta ke siapa.
     */
    $tab[] = ['hapus', 'Hapus', 'fa-trash-alt'];

    $kunciTab = array_column($tab, 0);
    $tabSekarang = in_array($tabAktif, $kunciTab, true) ? $tabAktif : 'ringkasan';

    /* Status mana yang MENGIRIM EMAIL ke pendaftarnya. */
    $statusBersurat = [];

    foreach (array_keys($pilihanStatus) as $nilaiStatus) {
        if (Pendaftaran::suratUntuk($layanan, $nilaiStatus) !== null) {
            $statusBersurat[] = $pilihanStatus[$nilaiStatus];
        }
    }
@endphp
<div class="main-content mis-badan">
    <section class="section">

        {{-- ------------------------------------------------ kepala --}}
        <div class="mis-kepala">
            <span class="mis-medali {{ $info['warna'] }}" aria-hidden="true">
                <i class="fas {{ $info['ikon'] }}"></i>
            </span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">{{ $namaOrang }}</h1>
                <p class="mis-sub">{{ $info['nama'] }} &middot; {{ $nomor }}</p>
            </div>
            <div class="mis-kepala-aksi">
                <a class="mis-tombol mis-tombol-halus"
                    href="{{ route('account.pendaftaran-layanan.index', ['layanan' => $layanan]) }}">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali ke daftar
                </a>
            </div>
        </div>

        @include('account.pendaftaran_layanan.partials.pesan')

        <div class="rin-kisi">

            {{-- --------------------------------- kartu identitas (kiri) --}}
            <section class="mis-kartu rin-identitas">
                <span class="mis-medali rin-besar {{ $info['warna'] }}" aria-hidden="true">
                    <i class="fas {{ $info['ikon'] }}"></i>
                </span>

                <h2 class="rin-nama">{{ $namaOrang }}</h2>
                <p class="rin-nomor">{{ $nomor }}</p>

                <div class="rin-pil-baris">
                    <span class="mis-pil mis-pil-{{ $rupa['warna'] }}">
                        <i class="fas {{ $rupa['ikon'] }}" aria-hidden="true"></i> {{ $rupa['label'] }}
                    </span>
                    @if ($jumlahOrang > 1)
                        <span class="mis-pil mis-pil-ungu">
                            <i class="fas fa-users" aria-hidden="true"></i> {{ $jumlahOrang }} orang
                        </span>
                    @endif
                </div>

                {{-- Tiap baris keterangan diberi medali mini BERWARNA sesuai
                     kelompoknya, dan warnanya konsisten di seluruh MIS: biru
                     untuk email, hijau untuk WhatsApp, ungu untuk afiliasi,
                     jingga untuk sesi, kuning untuk waktu. Ikonnya dipusatkan
                     oleh place-items, bukan oleh bantalan kira-kira. --}}
                <div class="rin-baris">
                    <span class="mis-medali mini mis-biru" aria-hidden="true"><i class="fas fa-envelope"></i></span>
                    <p class="rin-baris-label">Email</p>
                    <p class="rin-baris-nilai">
                        @if ($emailOrang)
                            <a href="mailto:{{ $emailOrang }}" title="Kirim email ke {{ $emailOrang }}">{{ $emailOrang }}</a>
                        @else
                            <span class="rin-samar">belum diisi</span>
                        @endif
                    </p>
                </div>

                <div class="rin-baris">
                    <span class="mis-medali mini mis-hijau" aria-hidden="true"><i class="fab fa-whatsapp"></i></span>
                    <p class="rin-baris-label">WhatsApp</p>
                    <p class="rin-baris-nilai">
                        @if ($wa)
                            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener"
                                title="Hubungi lewat WhatsApp">{{ $telpOrang }}</a>
                        @elseif ($telpOrang)
                            {{-- Ditandai, bukan dijadikan tautan yang menuntun ke
                                 halaman galat WhatsApp: nomor di bawah sembilan
                                 angka bukan nomor telepon melainkan sisa isian. --}}
                            <span title="Nomor ini tidak bisa dipakai menghubungi lewat WhatsApp.">
                                <i class="fas fa-phone-slash mis-ikon-kuning" aria-hidden="true"></i> {{ $telpOrang }}
                            </span>
                        @else
                            <span class="rin-samar">belum diisi</span>
                        @endif
                    </p>
                </div>

                <div class="rin-baris">
                    <span class="mis-medali mini mis-ungu" aria-hidden="true"><i class="fas fa-building"></i></span>
                    <p class="rin-baris-label">Afiliasi</p>
                    <p class="rin-baris-nilai">
                        {{ $afiliasiOrang ?: '' }}
                        @if (! $afiliasiOrang)
                            <span class="rin-samar">belum diisi</span>
                        @endif
                    </p>
                </div>

                <div class="rin-baris">
                    <span class="mis-medali mini mis-jingga" aria-hidden="true"><i class="fas fa-calendar-alt"></i></span>
                    <p class="rin-baris-label">Sesi / angkatan</p>
                    <p class="rin-baris-nilai">
                        {{ $sesiBaris ?: '' }}
                        @if (! $sesiBaris)
                            <span class="rin-samar">tidak tercatat</span>
                        @endif
                    </p>
                </div>

                <div class="rin-baris">
                    <span class="mis-medali mini mis-kuning" aria-hidden="true"><i class="fas fa-clock"></i></span>
                    <p class="rin-baris-label">Mendaftar</p>
                    <p class="rin-baris-nilai">
                        {{ $waktuBaris ? $waktuBaris->translatedFormat('d F Y, H:i') . ' WIB' : '' }}
                        @if (! $waktuBaris)
                            <span class="rin-samar">tidak tercatat</span>
                        @endif
                    </p>
                </div>

                <div class="rin-baris">
                    <span class="mis-medali mini {{ $bukti['ada'] ? 'mis-hijau' : 'mis-merah' }}" aria-hidden="true">
                        <i class="fas fa-receipt"></i>
                    </span>
                    <p class="rin-baris-label">Bukti bayar</p>
                    <p class="rin-baris-nilai">
                        @if (! $bukti['nilai'])
                            <span class="rin-samar">
                                @if ($layanan === 'webinar_eksklusif')
                                    tidak memakai unggahan bukti
                                @else
                                    belum diunggah
                                @endif
                            </span>
                        @elseif ($bukti['ada'])
                            <a href="{{ $bukti['url'] }}" target="_blank" rel="noopener">
                                Buka ukuran penuh <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                            </a>
                        @else
                            {{-- Dibedakan dari "belum diunggah": terukur 72 dari 183
                                 nilai bukti menunjuk berkas yang sudah tidak ada di
                                 cakram, dan keduanya menuntut tindakan berbeda. --}}
                            <span style="color: #92400e;">berkasnya sudah tidak ada di server</span>
                        @endif
                    </p>
                </div>

                @if ($bukti['ada'])
                    <img class="rin-bukti-gambar" src="{{ $bukti['url'] }}"
                        alt="Bukti bayar {{ $namaOrang }}" loading="lazy">
                @endif
            </section>

            {{-- -------------------------------------- kartu bertab (kanan) --}}
            <div class="mis-kartu mis-tab-kartu">
                <div class="mis-tab-kepala">
                    <h2 class="mis-kartu-judul">Kelola pendaftaran</h2>
                    <p class="mis-kartu-sub">
                        Pindahkan status, betulkan data, atau pindahkan angkatannya.
                    </p>
                </div>

                {{-- role=tablist + aria-controls: isinya ditukar tanpa memuat
                     ulang halaman, jadi tanpa penanda ini pembaca layar tidak
                     mengumumkan apa pun saat tabnya berganti. --}}
                <ul class="mis-tab nav nav-pills" id="rin-tab" role="tablist">
                    @foreach ($tab as [$kunci, $judul, $ikon])
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $tabSekarang === $kunci ? 'active' : '' }}"
                                id="rin-tab-{{ $kunci }}" data-toggle="pill" href="#rin-panel-{{ $kunci }}"
                                role="tab" aria-controls="rin-panel-{{ $kunci }}"
                                aria-selected="{{ $tabSekarang === $kunci ? 'true' : 'false' }}">
                                <i class="fas {{ $ikon }}" aria-hidden="true"></i> {{ $judul }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="mis-tab-isi">

                    {{-- ======================================= ringkasan --}}
                    <div class="tab-pane fade {{ $tabSekarang === 'ringkasan' ? 'show active' : '' }}"
                        id="rin-panel-ringkasan" role="tabpanel" aria-labelledby="rin-tab-ringkasan" tabindex="0">

                        {{-- Tiga angka yang paling sering ditanyakan panitia saat
                             mencocokkan transfer, berdampingan supaya terbaca
                             sekali lihat. --}}
                        <div class="rin-angka">
                            <div class="mis-ubin">
                                <span class="mis-medali kecil mis-hijau" aria-hidden="true"><i class="fas fa-money-bill-wave"></i></span>
                                <div style="min-width: 0;">
                                    <p class="mis-ubin-angka">Rp {{ number_format($totalBayar, 0, ',', '.') }}</p>
                                    <p class="mis-ubin-label">Total bayar</p>
                                </div>
                            </div>

                            <div class="mis-ubin">
                                <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-hashtag"></i></span>
                                <div style="min-width: 0;">
                                    <p class="mis-ubin-angka">
                                        {{ $kodeUnik > 0 ? number_format($kodeUnik, 0, ',', '.') : '—' }}
                                    </p>
                                    <p class="mis-ubin-label">Kode unik</p>
                                </div>
                            </div>

                            <div class="mis-ubin">
                                <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-users"></i></span>
                                <div style="min-width: 0;">
                                    <p class="mis-ubin-angka">{{ $jumlahOrang }}</p>
                                    <p class="mis-ubin-label">Jumlah orang</p>
                                </div>
                            </div>
                        </div>

                        @if ($kodeUnik > 0)
                            <p class="rin-nota rin-nota-biru">
                                <i class="fas fa-info-circle" aria-hidden="true"></i>
                                <span>
                                    Cocokkan mutasi rekening dengan nominal
                                    <strong>Rp {{ number_format($totalBayar, 0, ',', '.') }}</strong> —
                                    angka {{ number_format($kodeUnik, 0, ',', '.') }} di ujungnya
                                    memang kode unik pendaftaran ini, bukan kelebihan bayar.
                                </span>
                            </p>
                        @endif

                        @if ($diskon > 0)
                            <p class="rin-nota rin-nota-biru">
                                <i class="fas fa-tag" aria-hidden="true"></i>
                                <span>
                                    Dapat potongan <strong>Rp {{ number_format($diskon, 0, ',', '.') }}</strong>@if ($pendaftaran->kode_diskon) dengan kode <strong>{{ $pendaftaran->kode_diskon }}</strong>@endif.
                                </span>
                            </p>
                        @endif

                        {{-- Borang status di tab Ringkasan, bukan tab sendiri:
                             inilah tindakan yang paling sering dikerjakan, dan
                             menaruhnya di balik satu tab lagi menambah satu
                             ketukan untuk pekerjaan sehari-hari. --}}
                        <div class="rin-bagian">
                            <p class="rin-bagian-judul">
                                <span class="mis-medali kecil mis-{{ $rupa['warna'] === 'abu' ? 'ungu' : $rupa['warna'] }}" aria-hidden="true">
                                    <i class="fas fa-exchange-alt"></i>
                                </span>
                                <span class="teks">Status pembayaran</span>
                                <span class="mis-pil mis-pil-{{ $rupa['warna'] }}" style="margin-left: auto;">
                                    <i class="fas {{ $rupa['ikon'] }}" aria-hidden="true"></i> {{ $rupa['label'] }}
                                </span>
                            </p>

                            <form method="POST"
                                action="{{ route('account.pendaftaran-layanan.status', [$layanan, $pendaftaran->getKey()]) }}">
                                @csrf
                                <div class="rin-status-baris">
                                    <div class="mis-isian">
                                        {{-- Label menyebut status ASLINYA, bukan keadaan
                                             ringkasnya. Versi sebelumnya berbunyi
                                             "Status sekarang — Lunas" sementara pilihan di
                                             bawahnya berbunyi "Pendaftaran diterima": dua
                                             kosakata untuk satu hal, dan orang yang membaca
                                             sekilas mengira keduanya berbeda. --}}
                                        <label class="mis-label" for="rin-status">Pindahkan status</label>
                                        <select class="form-control-modern" id="rin-status" name="status" required>
                                            @foreach ($pilihanStatus as $nilaiStatus => $tulisan)
                                                <option value="{{ $nilaiStatus }}" @selected($pendaftaran->status === $nilaiStatus)>{{ $tulisan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="mis-tombol mis-tombol-ungu">
                                        <i class="fas fa-check" aria-hidden="true"></i> Simpan status
                                    </button>
                                </div>

                                @if ($statusBersurat !== [])
                                    @php
                                        // Dirakit di PHP, bukan di dalam {{ }}: kurung
                                        // buka di dalam untaian ikut terbaca pemindai
                                        // direktif Blade.
                                        $kalimatSurat = implode(' atau ', $statusBersurat);
                                    @endphp
                                    {{-- Disebut DI MUKA, bukan sesudah terkirim: email
                                         tidak bisa ditarik kembali. --}}
                                    <p class="rin-nota">
                                        <i class="fas fa-envelope" aria-hidden="true"></i>
                                        <span>
                                            Memilih <strong>{{ $kalimatSurat }}</strong> akan mengirim
                                            email pemberitahuan ke pendaftarnya.
                                        </span>
                                    </p>
                                @endif

                                @if ($layanan === 'webinar_eksklusif')
                                    <p class="rin-nota">
                                        <i class="fas fa-chair" aria-hidden="true"></i>
                                        <span>
                                            Khusus layanan ini, <strong>Kedaluwarsa</strong> dan
                                            <strong>Dibatalkan</strong> mengembalikan kursinya ke kuota
                                            angkatan; mengembalikannya ke status aktif mengambil
                                            kursinya lagi.
                                        </span>
                                    </p>
                                @endif
                            </form>
                        </div>

                        @if ($pendaftaran->note)
                            <div class="rin-bagian">
                                <p class="rin-bagian-judul">
                                    <span class="mis-medali kecil mis-kuning" aria-hidden="true"><i class="fas fa-history"></i></span>
                                    <span class="teks">Jejak &amp; catatan</span>
                                </p>
                                <p class="rin-baris-nilai">{{ $pendaftaran->note }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- ============================== borang per kelompok --}}
                    @foreach ([['diri', $bagianDiri], ['bayar', $bagianBayar], ['sesi', $bagianSesi]] as [$kunciTabIsi, $bagian])
                        @if ($bagian !== [])
                            <div class="tab-pane fade {{ $tabSekarang === $kunciTabIsi ? 'show active' : '' }}"
                                id="rin-panel-{{ $kunciTabIsi }}" role="tabpanel"
                                aria-labelledby="rin-tab-{{ $kunciTabIsi }}" tabindex="0">

                                {{-- Satu borang per tab: menyimpan satu kelompok
                                     tidak menuntut mengirim kelompok lainnya, dan
                                     validatornya memakai 'sometimes' supaya medan
                                     di tab lain tidak dianggap dikosongkan. --}}
                                <form method="POST"
                                    action="{{ route('account.pendaftaran-layanan.ubah', [$layanan, $pendaftaran->getKey()]) }}">
                                    @csrf
                                    @method('PUT')

                                    @foreach ($bagian as [$judulBagian, $ikonBagian, $warnaBagian, $daftarMedan])
                                        <div class="rin-bagian">
                                            <p class="rin-bagian-judul">
                                                <span class="mis-medali kecil {{ $warnaBagian }}" aria-hidden="true">
                                                    <i class="fas {{ $ikonBagian }}"></i>
                                                </span>
                                                <span class="teks">{{ $judulBagian }}</span>
                                            </p>

                                            <div class="rin-isian-kisi">
                                                @foreach ($daftarMedan as $kolom)
                                                    @include('account.pendaftaran_layanan.partials.isian', [
                                                        'kolom' => $kolom,
                                                        'jenis' => $medan[$kolom],
                                                        'tulisan' => $label[$kolom],
                                                        'nilai' => old($kolom, $pendaftaran->{$kolom}),
                                                        'penuh' => in_array($kolom, $medanPenuh, true),
                                                        'uang' => in_array($kolom, $medanUang, true),
                                                        'angkatan' => $angkatan,
                                                    ])
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="rin-kaki">
                                        <button type="submit" class="mis-tombol mis-tombol-ungu">
                                            <i class="fas fa-save" aria-hidden="true"></i> Simpan perubahan
                                        </button>
                                        <a class="mis-tombol mis-tombol-halus"
                                            href="{{ route('account.pendaftaran-layanan.rincian', [$layanan, $pendaftaran->getKey()]) }}">
                                            Batalkan
                                        </a>
                                    </div>
                                </form>
                            </div>
                        @endif
                    @endforeach

                    {{-- ======================================== peserta --}}
                    @if ($adaPeserta)
                        <div class="tab-pane fade {{ $tabSekarang === 'peserta' ? 'show active' : '' }}"
                            id="rin-panel-peserta" role="tabpanel" aria-labelledby="rin-tab-peserta" tabindex="0">

                            @php
                                $semuaPeserta = $pendaftaran->semuaPeserta();
                                $terdaftar = count($semuaPeserta);
                            @endphp

                            <p class="mis-kartu-sub" style="margin-bottom: 13px;">
                                Nama di daftar ini yang dipakai menerbitkan sertifikat dan
                                memasukkan orang ke grup.
                            </p>

                            {{-- Dibawa dari layar pendaftar webinar yang dibuang:
                                 tanpa daftar ini, nama peserta kedua dan seterusnya
                                 tidak bisa dilihat di mana pun lagi. --}}
                            <ol class="rin-peserta">
                                @foreach ($semuaPeserta as $orangKe)
                                    <li>
                                        <span class="rin-peserta-nama">
                                            {{ $orangKe['nama'] ?: 'Tanpa nama' }}
                                            @if (! empty($orangKe['email']))
                                                <span class="rin-peserta-surel">{{ $orangKe['email'] }}</span>
                                            @endif
                                        </span>
                                        @if ($orangKe['utama'])
                                            <span class="mis-pil mis-pil-ungu" style="margin-left: auto;">pendaftar</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>

                            @if ($terdaftar !== $jumlahOrang)
                                {{-- Selisihnya disebut, bukan dibiarkan: pendaftaran
                                     yang dibayar untuk lima orang tetapi hanya memuat
                                     tiga nama berarti dua sertifikat tidak bisa
                                     diterbitkan, dan itu baru ketahuan di hari acara. --}}
                                <p class="rin-nota">
                                    <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                                    <span>
                                        Dibayar untuk <strong>{{ $jumlahOrang }}</strong> orang, tetapi baru
                                        <strong>{{ $terdaftar }}</strong> nama yang tercatat. Tanyakan
                                        sisanya ke pendaftarnya.
                                    </span>
                                </p>
                            @endif
                        </div>
                    @endif

                    {{-- ========================================== hapus --}}
                    <div class="tab-pane fade {{ $tabSekarang === 'hapus' ? 'show active' : '' }}"
                        id="rin-panel-hapus" role="tabpanel" aria-labelledby="rin-tab-hapus" tabindex="0">

                        @if ($bolehMenghapus)
                            <p class="rin-nota rin-nota-bahaya">
                                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                                <span>
                                    Barisnya hilang permanen beserta berkas bukti bayarnya@if ($berangkatan), dan kursinya dikembalikan ke kuota angkatan@endif@if ($layanan === 'clinik_scopus'), beserta testimoni yang menempel padanya@endif.
                                    Tidak bisa diurungkan, dan belum ada tong sampah.
                                </span>
                            </p>

                            <p class="rin-nota rin-nota-biru">
                                <i class="fas fa-undo" aria-hidden="true"></i>
                                <span>
                                    Kalau yang Anda maksud <strong>membatalkan</strong>, pindahkan
                                    statusnya di tab Ringkasan — datanya tetap bisa dilihat dan
                                    kursinya tetap terurus.
                                </span>
                            </p>

                            <form method="POST" id="rin-borang-hapus"
                                action="{{ route('account.pendaftaran-layanan.hapus', [$layanan, $pendaftaran->getKey()]) }}">
                                @csrf
                                @method('DELETE')
                                <div class="rin-kaki">
                                    <button type="button" class="mis-tombol mis-tombol-hapus" id="rin-tombol-hapus"
                                        data-nama="{{ $namaOrang }}" data-nomor="{{ $nomor }}">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i> Hapus pendaftaran ini
                                    </button>
                                </div>
                            </form>
                        @else
                            {{-- Tombolnya tidak disuguhkan, DAN alasannya ditulis di
                                 tempat tombol itu seharusnya berada — beserta jalan
                                 keluar yang memang bisa ia pakai sendiri. --}}
                            <p class="rin-nota">
                                <i class="fas fa-lock" aria-hidden="true"></i>
                                <span>
                                    Hanya administrator yang boleh menghapus pendaftaran.
                                    Penghapusannya tidak bisa diurungkan dan belum ada tong
                                    sampah, jadi haknya dipersempit.
                                </span>
                            </p>

                            <p class="rin-nota rin-nota-biru">
                                <i class="fas fa-undo" aria-hidden="true"></i>
                                <span>
                                    Kalau yang Anda maksud <strong>membatalkan</strong>, itu bisa
                                    Anda lakukan sendiri: pindahkan statusnya di tab
                                    <strong>Ringkasan</strong>.
                                </span>
                            </p>
                        @endif
                    </div>

                </div>{{-- mis-tab-isi --}}
            </div>
        </div>

    </section>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        'use strict';

        /*
         * Strip tab di ponsel: satu baris yang bisa digeser, DISERTAI penanda
         * tepi memudar. Tanpa tanda bahwa masih ada isi di samping, tab
         * terakhir praktis tidak ada.
         *
         * Polanya disalin dari layar Data Pelanggan, termasuk satu hal yang
         * mudah salah: ambang "sudah di paling kiri" dihitung dari BANTALAN
         * strip, bukan nol — tiap tab ber-scroll-snap-align: start, jadi
         * posisi diam paling kiri sama dengan bantalannya. Dibandingkan
         * dengan nol, penanda kiri menyala sejak halaman dibuka dan
         * menjanjikan isi yang sebenarnya tidak ada.
         */
        var strip = document.getElementById('rin-tab');

        if (!strip) {
            return;
        }

        var segarkan = function () {
            var bantalan = parseFloat(getComputedStyle(strip).paddingLeft) || 0;

            // 1px toleransi: lebar hasil hitungan peramban kerap berkoma.
            strip.classList.toggle('mis-tab-geser-kiri', strip.scrollLeft > bantalan + 1);
            strip.classList.toggle(
                'mis-tab-geser-kanan',
                strip.scrollLeft + strip.clientWidth < strip.scrollWidth - 1
            );
        };

        strip.addEventListener('scroll', segarkan, { passive: true });
        window.addEventListener('resize', segarkan);
        segarkan();

        var terlihatPenuh = function (el) {
            var a = strip.getBoundingClientRect();
            var b = el.getBoundingClientRect();

            return b.left >= a.left - 1 && b.right <= a.right + 1;
        };

        /*
         * Tab yang terbuka digeser ke dalam pandangan — penting saat halaman
         * dibuka ulang pada tab yang memuat galat validasinya: tanpa ini
         * tandanya aktif tetapi tabnya sendiri di luar layar.
         *
         * HANYA kalau memang belum terlihat utuh: scrollIntoView tanpa syarat
         * menggeser stripnya sebesar bantalannya sendiri, dan penanda kiri
         * menyala tanpa ada isi di sana.
         */
        var aktif = strip.querySelector('.nav-link.active');

        if (aktif && !terlihatPenuh(aktif)) {
            aktif.scrollIntoView({ block: 'nearest', inline: 'nearest' });
        }

        strip.addEventListener('click', function (e) {
            var tab = e.target.closest('.nav-link');

            if (!tab) {
                return;
            }

            setTimeout(function () {
                if (!terlihatPenuh(tab)) {
                    tab.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' });
                }

                segarkan();
            }, 60);
        });
    })();

    /*
     * Konfirmasi hapus lewat misKonfirmasi(), bukan Swal.fire langsung —
     * pembungkus bersama itu yang menjaga rupanya seragam di semua layar.
     *
     * Nama orangnya lewat `sorot`, BUKAN dirangkai ke `pesan`: ia disisipkan
     * sebagai teks, jadi tanda < di dalam nama tidak pernah tertafsir markah.
     *
     * Dialognya bukan pengaman. Peladennya tetap memeriksa peran penghapusnya,
     * jadi melewatinya lewat konsol tidak membuat apa pun bisa terhapus.
     */
    document.addEventListener('DOMContentLoaded', function () {
        var tombol = document.getElementById('rin-tombol-hapus');

        if (!tombol) {
            return;
        }

        tombol.addEventListener('click', function () {
            window.misKonfirmasi({
                judul: 'Hapus pendaftaran ini?',
                pesan: 'Barisnya hilang permanen beserta bukti bayarnya. Tidak bisa diurungkan.',
                sorot: tombol.dataset.nomor + ' — ' + tombol.dataset.nama,
                tombol: 'Ya, hapus',
                jenis: 'bahaya',
            }).then(function (setuju) {
                if (setuju) {
                    document.getElementById('rin-borang-hapus').submit();
                }
            });
        });
    });
</script>
@endpush
