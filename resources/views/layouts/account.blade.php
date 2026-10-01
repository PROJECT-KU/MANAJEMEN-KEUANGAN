<!DOCTYPE html>
<html lang="en">

@php
use Jenssegers\Agent\Agent;
$agent = new Agent();
@endphp

<head>
    <!-- cdn sweet alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- end -->
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>@yield('title')</title>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Favicon-->
    {{-- Sama dengan halaman masuk: tab peramban butuh gambar persegi, jadi
         yang dipakai potongan logo, bukan logo utuh. --}}
    <link rel="icon" href="{{ asset('assets/img/mis-favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/mis-favicon.png') }}">
    <!-- General CSS Files -->
    <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap-daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}">

    <!-- CSS Libraries -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    {{-- Huruf yang sama dengan dasbor & halaman auth --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="{{ asset('assets/modules/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/modules/moment.min.js') }}"></script>
    <script src="{{ asset('assets/modules/cleave-js/dist/cleave.min.js') }}"></script>
    <script src="{{ asset('assets/js/highcharts.js') }}"></script>
    <!-- zoom image -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

    <!-- end -->

    {{-- Lapis penyeragam tampilan: dimuat terakhir supaya menimpa Stisla. --}}
    <link rel="stylesheet" href="{{ asset('assets/css/mis-ui.css') }}?v=79">

    <style>
        .fas,
        .far,
        .fab,
        .fal {
            font-size: 20px;
        }

        .form-group label {
            font-weight: bold;
        }
    </style>
    <!--================== SERVICE WORKER ==================-->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then((reg) => {
                        console.log('Service Worker registered.', reg);
                    })
                    .catch((err) => {
                        console.error('Service Worker registration failed:', err);
                    });
            });
        }
    </script>
    <!--================== END ==================-->
    <style>
        .navbar {
            position: fixed;
            top: 15px;
            /* Memberikan jarak dari atas agar terlihat melayang (Floating) */
            left: 270px;
            /* Jarak dari sidebar + sedikit gap agar rapi */
            right: 20px;
            /* Jarak dari kanan */
            z-index: 1050;
            height: 65px;

            /* Glassmorphism Effect */
            background: rgba(255, 255, 255, 0.7) !important;
            backdrop-filter: blur(15px) saturate(180%);
            -webkit-backdrop-filter: blur(15px) saturate(180%);

            /* Border Tipis seperti Apple UI */
            border: 1px solid rgba(255, 255, 255, 0.4) !important;
            border-radius: 18px;
            /* Sudut membulat modern */

            /* Shadow yang sangat halus (Soft Shadow) */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04) !important;

            display: flex;
            align-items: center;
            padding: 0 20px;
            transition: all 0.3s ease;
        }

        /* Efek saat scroll (opsional jika ingin berubah warna) */
        .navbar-active {
            background: rgba(255, 255, 255, 0.9) !important;
            top: 0;
            left: 250px;
            right: 0;
            border-radius: 0;
        }

        /* Styling Teks & Ikon agar Kontras dengan Background Kaca */
        .navbar .nav-link,
        .navbar #greeting,
        .navbar .nav-link-user {
            color: #1e293b !important;
            /* Warna slate gelap yang elegan */
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.3px;
        }

        /* Styling Avatar di Navbar */
        .user-img-nav {
            border-radius: 12px !important;
            /* Kotak membulat lebih modern daripada lingkaran sempurna */
            border: 2px solid #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        /* Responsif untuk Mobile */
        @media (max-width: 1024px) {
            .navbar {
                top: 0;
                left: 0;
                right: 0;
                margin: 0;
                border-radius: 0;
                background: #fff !important;
                /* Full white di mobile agar clean */
            }
        }
    </style>
    <style>
        /* Logo MIS berbentuk lebar; di sidebar yang menyempit hanya bagian
           ikon grafiknya yang ditampilkan supaya tetap terbaca. */
        /* Logo MIS lebih tinggi daripada logo lama, jadi bidang merek sidebar
           diberi ruang agar tidak terpotong. */
        .sidebar-brand:not(.sidebar-brand-sm) {
            height: auto;
            min-height: 76px;
            padding: 12px 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-brand:not(.sidebar-brand-sm) img {
            max-height: 58px;
            width: auto;
        }

        .merek-ikon-kecil {
            display: block;
            width: 52px;
            height: auto;
            margin: 0 auto;
        }
    </style>

    {{-- Gaya khusus halaman. Harus di sini, bukan di badan berkas: tanpa ini
         lembar gaya halaman terbit SEBELUM CSS Bootstrap, sehingga aturan
         dengan bobot sama (misal .prf-tab lawan .nav) selalu kalah. --}}
    @stack('gaya')
</head>
@php
/*
 * $tenggatDate dan $isTenggatExpired dibuang: keduanya dihitung enam kali di
 * berkas ini dan tidak pernah dibaca satu kali pun. Kolom users.tenggat yang
 * jadi sumbernya juga kosong di seluruh 146 baris dan ikut dihapus.
 *
 * Komentarnya gaya PHP, bukan {{-- --}}: di dalam @php Blade tidak mengolah
 * komentar miliknya sendiri, sehingga tandanya lolos apa adanya ke PHP dan
 * menjadikan berkas ini tidak sah.
 */
$isStatusnonactive = Auth::check() && Auth::user()->status === 'nonactive';
@endphp

    <body style="background-color: #F5F5F5;" class="{{ $agent->isMobile() ? 'is-mobile' : '' }}">
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <!--==================UNTUK DIVACE MOBILE==================-->
            @if ($agent->isMobile())
            <nav class="navbar navbar-expand-lg main-navbar shadow-sm">
                <form class="form-inline mr-auto d-flex align-items-center">
                    {{-- Tombol menu: tanpa ini sidebar di ponsel tidak bisa dibuka
                         sama sekali, sehingga halaman lain tak terjangkau. --}}
                    <a href="#" data-toggle="sidebar" class="mis-burger" aria-label="Buka menu">
                        <i class="fas fa-bars"></i>
                    </a>
                    <p id="greeting" class="text-dark font-weight-bold mb-0 ml-2 mt-3" style="font-size:13px;"></p>
                </form>

                <!-- Dropdown Profil -->
                <ul class="navbar-nav navbar-right mr-2">
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user d-flex align-items-center text-dark">
                            @if (Auth::user()->gambar == null)
                            <img alt="image" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail rounded-circle" style="width: 50px; height:50px; margin: 5px 10px;">
                            @else
                            <img alt="image" src="{{ \App\Support\FotoProfil::url(Auth::user()->gambar) }}" class="img-thumbnail rounded-circle" style="width: 50px; height:50px; margin: 5px 10px;">
                            @endif
                            <div class="d-sm-none d-lg-inline-block">Hi, {{ Auth::user()->full_name }}</div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="dropdown-title">Logged in as <strong>{{ Auth::user()->username }}</strong>
                                <hr>
                            </div>
                            <a href="{{ route('account.profil.show', ['uuid' => Auth::user()->uuid]) }}" class="dropdown-item has-icon">
                                <i class="far fa-user"></i> PROFIL SAYA
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('logout') }}" onclick="event.preventDefault();
                document.getElementById('logout-form').submit();" class="dropdown-item has-icon text-danger">
                                <i class="fas fa-sign-out-alt"></i> KELUAR
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                </ul>
            </nav>

            @yield('content')

            @extends('layouts.version')


            <!--================== UNTUK DIVACE SELAIN MOBILE ==================-->
            @else


            <nav class="navbar navbar-expand-lg main-navbar">
                <form class="form-inline mr-auto d-flex align-items-center" style="height: 100%;">
                    {{-- Di tablet sidebar juga tersembunyi; tombol ini yang membukanya. --}}
                    <a href="#" data-toggle="sidebar" class="mis-burger" aria-label="Buka menu">
                        <i class="fas fa-bars"></i>
                    </a>
                    <p id="greeting" class="text-white font-weight-bold mb-0 ml-2 d-flex align-items-center mt-3"></p>
                </form>

                {{-- Pintasan pemberitahuan. Angkanya dihitung sekali per
                     permintaan oleh AppServiceProvider, jadi bagian ini tidak
                     menambah satu pun kueri. --}}
                <ul class="navbar-nav mis-notif">
                    @if (Auth::user()->adalahOrangDalam())
                        <li>
                            <a href="{{ route('account.todolist.index') }}" class="mis-notif-tombol" title="Tugas ditugaskan">
                                <i class="fas fa-tasks"></i>
                                @if (($totalAssignTask ?? 0) > 0)
                                    <span class="mis-notif-angka">{{ $totalAssignTask > 99 ? '99+' : $totalAssignTask }}</span>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('account.PerjalananDinas.index') }}" class="mis-notif-tombol" title="Perjalanan dinas">
                                <i class="fas fa-plane-departure"></i>
                                @if (($countAjukan ?? 0) > 0)
                                    <span class="mis-notif-angka">{{ $countAjukan > 99 ? '99+' : $countAjukan }}</span>
                                @endif
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="{{ route('account.Clinik-Scopus-Riwayat-Pemesanan.index') }}" class="mis-notif-tombol" title="Pemesanan Clinik Scopus">
                            <i class="fas fa-file-invoice-dollar"></i>
                            @if (($countScopusPending ?? 0) > 0)
                                <span class="mis-notif-angka">{{ $countScopusPending > 99 ? '99+' : $countScopusPending }}</span>
                            @endif
                        </a>
                    </li>
                </ul>

                <!-- Dropdown Profil -->
                @if (Auth::check())
                <ul class="navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user d-flex align-items-center">
                            @if (Auth::user()->gambar == null)
                            <img alt="image" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail rounded-circle" style="width: 50px; height:50px; margin: 5px 10px;">
                            @else
                            <img alt="image" src="{{ \App\Support\FotoProfil::url(Auth::user()->gambar) }}" class="img-thumbnail rounded-circle" style="width: 50px; height:50px; margin: 5px 10px;">
                            @endif
                            <div class="d-sm-none d-lg-inline-block">Hi, {{ Auth::user()->full_name }}</div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="dropdown-title">Logged in as <strong>{{ Auth::user()->username }}</strong>
                                <hr>
                            </div>
                            <a href="{{ route('account.profil.show', ['uuid' => Auth::user()->uuid]) }}" class="dropdown-item has-icon">
                                <i class="far fa-user"></i> PROFIL SAYA
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('logout') }}" onclick="event.preventDefault();
                document.getElementById('logout-form').submit();" class="dropdown-item has-icon text-danger">
                                <i class="fas fa-sign-out-alt"></i> KELUAR
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                    @endif
                </ul>
            </nav>

            <div class="main-sidebar sidebar-style-2" id="SidebarPwa" style="position: fixed;">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand">
                        <img src="{{ asset('assets/img/newlogogeneration.png') }}" alt="MIS — Management Integration System" width="150">
                    </div>
                    <div class="sidebar-brand sidebar-brand-sm">
                        {{-- logo tampil utuh, hanya diperkecil --}}
                        <img src="{{ asset('assets/img/newlogogeneration.png') }}" alt="MIS" class="merek-ikon-kecil">
                    </div>
                    <ul class="sidebar-menu">

                        <!--================== DASBOARD ==================-->
                        <li class="menu-header">DASHBOARD</li>
                        <li class="{{ setActive('account/dashboard') }}"><a class="nav-link" href="{{ route('account.dashboard.index') }}"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                        <!--================== END ==================-->

                        <!--================== CLINIC SCOPUS ==================-->
                        @php
                        $pengguna = Auth::user();
                        @endphp
                        <li class="menu-header">Clinik Scopus</li>
                        @if ($pengguna->adalahAdministrator())
                        <li class="{{ setActive('account/customer') . setActive('account/pengguna/search') }}">
                            <a class="nav-link" href="{{ route('account.customer.index') }}">
                                <i class="fas fa-users"></i> <span>Data Customer</span>
                            </a>
                        </li>
                        <li class="{{ setActive('account/Clinik-Scopus-Biaya-Persesi') }}">
                            <a class="nav-link" href="{{ route('account.Clinik-Scopus-Biaya-Persesi.index') }}">
                                <i class="fas fa-coins"></i> <span>Tarif Layanan</span>
                            </a>
                        </li>
                        {{-- Angkatan seluruh layanan; menggantikan dua menu kategori yang
                             terpisah, yang masih dibiarkan hidup sampai layar ini terpakai
                             sehari-hari. --}}
                        <li class="{{ setActive('account/kategori-layanan') }}">
                            <a class="nav-link" href="{{ route('account.kategori-layanan.index') }}">
                                <i class="fas fa-layer-group"></i> <span>Angkatan Layanan</span>
                            </a>
                        </li>

                        <li class="{{ setActive('account/Clinik-Scopus-Promo') }}">
                            <a class="nav-link" href="{{ route('account.Clinik-Scopus-Promo.index') }}">
                                <i class="fas fa-tags"></i> <span>Promo</span>
                            </a>
                        </li>
                        @endif

                        {{-- Dulu manager ATAU karyawan; sesudah peran dipisah, keduanya
                             berarti "orang dalam" — administrator maupun karyawan. --}}
                        @if ($pengguna->adalahOrangDalam())
                        <li class="{{ setActive('account/clinikscopus') }}">
                            <a class="nav-link" href="{{ route('account.clinikscopus.index') }}">
                                <i class="fas fa-home"></i> <span>Clinik Scopus</span>
                            </a>
                        </li>
                        @endif

                        <li class="{{ setActive('account/Clinik-Scopus-Riwayat-Pemesanan') . setActive('account/pengguna/search') }}">
                            <a class="nav-link d-flex align-items-center justify-content-between" href="{{ route('account.Clinik-Scopus-Riwayat-Pemesanan.index') }}">
                                <div>
                                    <i class="fas fa-users"></i> <span>Riwayat Pemesanan</span>
                                </div>

                                <div class="d-flex align-items-center" style="gap: 3px;">
                                    @if(($countScopusPending ?? 0) > 0)
                                    <span class="badge badge-warning" style="font-size: 10px; padding: 2px 6px; border-radius: 5px;">
                                        {{ $countScopusPending }}
                                    </span>
                                    @endif

                                    @if(($countPaid ?? 0) > 0)
                                    <span class="badge badge-success" style="font-size: 10px; padding: 2px 6px; border-radius: 5px;">
                                        {{ $countPaid }}
                                    </span>
                                    @endif
                                </div>
                            </a>
                        </li>
                        <!--================== END ==================-->

                        <!--================== REDIRECT TO BERANDA ==================-->
                        @if (Auth::user()->adalahPelanggan())
                        <li><a class="nav-link" href="{{ route('public.clinikscopus.index') }}"><i class="fas fa-comment"></i> <span>Konsultasi Sekarang</span></a></li>
                        @endif
                        <!--================== END ==================-->

                        @if (Auth::check() && Auth::user()->email_verified_at)
                        @php $isStatusnonactive = (Auth::user()->status === 'nonactive'); @endphp

                                <!--==================PERUSAHAAN==================-->
                                @if (Auth::user()->adalahAdministrator())
                                <li class="menu-header">PERUSAHAAN</li>
                                <li class="{{ setActive('account/company/' . Auth::user()->id . '/edit') }}">
                                    <a class="nav-link" href="{{ route('account.company.edit', ['id' => Auth::user()->id]) }}">
                                        <i class="fas fa-building"></i> <span>Company</span>
                                    </a>
                                </li>
                                @endif
                                <!--================== END ==================-->

                                <!--================== KARYAWAN ==================-->
                                @if (Auth::user()->adalahOrangDalam())
                                <li class="menu-header">KARYAWAN</li>
                                @endif

                                @if (Auth::user()->adalahAdministrator())
                                <li class="{{ setActive('account/pengguna') . setActive('account/pengguna/search') }}">
                                    <a class="nav-link" href="{{ route('account.pengguna.index') }}">
                                        <i class="fas fa-users"></i> <span>Data Karyawan</span>
                                    </a>
                                </li>
                                @endif

                                @if ($isStatusnonactive)
                                @else
                                @if (Auth::user()->adalahOrangDalam())
                                <li class="{{ setActive('account/gaji') }}">
                                    <a class="nav-link" href="{{ route('account.gaji.index') }}">
                                        <i class="fas fa-dollar-sign"></i> <span>Gaji Karyawan</span>
                                    </a>
                                </li>
                                <li class="{{ setActive('account/cuti') }}">
                                    <a class="nav-link" href="{{ route('account.cuti.index') }}">
                                        <i class="fas fa-calendar-alt"></i> <span>Cuti Karyawan</span>
                                    </a>
                                </li>
                                <li class="{{ setActive('account/presensi') }}">
                                    <a class="nav-link" href="{{ route('account.presensi.index') }}">
                                        <i class="fas fa-user-clock"></i> <span>Presensi</span>
                                    </a>
                                </li>
                                <li class="{{ setActive('account/Perjalanan-Dinas') }}">
                                    <a class="nav-link" href="{{ route('account.PerjalananDinas.index') }}">
                                        <i class="fas fa-suitcase-rolling"></i> <span>Perjalanan Dinas</span>
                                        <span class="badge badge-warning right" style="width: fit-content;">{{ $countAjukan }}</span>
                                    </a>
                                </li>
                                @endif

                                @if (! Auth::user()->adalahKaryawan() && Auth::user()->adalahOrangDalam())
                                <li class="{{ setActive('account/karir') }}">
                                    <a class="nav-link" href="{{ route('karir.list') }}">
                                        <i class="fas fa-user-tie"></i> <span>Karir</span>
                                        @php
                                        $totalStatusNull = App\Karir::whereNull('status')->count();
                                        @endphp

                                        @if ($totalStatusNull > 0)
                                        <span class="badge badge-warning right" style="width: fit-content;">{{ $totalStatusNull }}</span>
                                        @endif

                                    </a>
                                </li>
                                @endif

                                @if (Auth::user()->adalahOrangDalam())
                                <li class="{{ setActive('account/todolist') }}">
                                    <a class="nav-link" href="{{ route('account.todolist.index') }}">
                                        <i class="fas fa-list-alt"></i> <span>To Do List</span>

                                        @if (isset($totalAssignTask) && $totalAssignTask > 0)
                                        <span class="badge badge-warning right" style="width: fit-content;">{{ $totalAssignTask }}</span>
                                        @endif
                                    </a>
                                </li>
                                @endif
                                <!--================== END ==================-->


                                <!--================== PAPER ==================-->
                                @if (Auth::user()->adalahAdministrator() )
                                <li class="menu-header">PAPER</li>
                                <li class="dropdown {{ setActive('account/meme/data') . setActive('account/meme/create-data') . setActive('account/meme/edit-data') . setActive('account/pendaftaran-scopus-kafe/data') }}">
                                    <a href="#" class="nav-link has-dropdown">
                                        <i class="fas fa-coffee"></i><span>Scopus Kafe</span>
                                        @php
                                        // Menghitung jumlah data dengan status 'menunggu verifikasi'
                                        $totalStatusMenunggu = App\PendaftaranScopusKafe::where('status', 'menunggu verifikasi')->count();
                                        @endphp

                                        @if ($totalStatusMenunggu > 0)
                                        <span class="badge badge-warning right" style="width: fit-content;">{{ $totalStatusMenunggu }}</span>
                                        @endif
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="{{ setActive('account/meme') . setActive('account/meme/edit-data') }}"><a class="nav-link" href="{{ route('account.meme.index') }}"><i class="fas fa-dice-d6"></i>Create Data</a></li>
                                        <li class="{{ setActive('account/pendaftaran-scopus-kafe') }}"><a class="nav-link" href="{{ route('account.pendaftaran-scopus-kafe.index') }}"><i class="fas fa-users"></i>Data Pendaftaran</a></li>
                                    </ul>
                                </li>

                                <li class="{{ setActive('account/paperisasi/data') }}">
                                    <a class="nav-link" href="{{ route('account.paperisasi.index') }}">
                                        <i class="fas fa-folder-open"></i> <span>Paperisasi</span>
                                    </a>
                                </li>
                                @endif

                                {{-- Dulu "bukan manager, bukan karyawan, bukan user" — yang
                                     tersisa staff/ceo/trainer. Ketiganya sekarang berperan
                                     karyawan, jadi syarat itu tidak pernah benar lagi. Judul ini
                                     menaungi menu di bawahnya, jadi syaratnya disamakan dengan
                                     isinya. --}}
                                @if (Auth::user()->adalahAdministrator())
                                <li class="menu-header">PAPER</li>
                                @endif
                                @if (Auth::user()->adalahAdministrator() || Auth::user()->id === 99)
                                <li class="{{ setActive('account/refrensi-paper/data') }}">
                                    <a class="nav-link" href="{{ route('account.refrensi-paper.index') }}">
                                        <i class="fas fa-folder"></i> <span>Refrensi Paper</span>
                                    </a>
                                </li>
                                @endif
                                <!--================== END ==================-->

                                <!--================== BLOG ==================-->
                                @if (Auth::user()->adalahAdministrator() || Auth::user()->id === 83 || Auth::user()->id === 87)
                                <li class="menu-header">BLOG</li>
                                <li class="dropdown {{ setActive('account/article') . setActive('account/artikel-kategori') }}">
                                    <a href="#" class="nav-link has-dropdown">
                                        <i class="fas fa-newspaper"></i><span>Artikel</span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="{{ setActive('account/artikel-kategori') }}"><a class="nav-link" href="{{ route('account.Kategori-Artikel.index') }}"><i class="fas fa-dice-d6"></i>Kategori</a></li>
                                        <li class="{{ setActive('account/article') }}"><a class="nav-link" href="{{ route('account.Artikel.index') }}"><i class="fas fa-file-signature"></i>Data Artikel</a></li>
                                        <!-- <li class="{{ setActive('account/article') }}"><a class="nav-link" href="{{ route('account.Artikel.index') }}"><i class="fas fa-comments"></i>DATA KOMENTAR</a></li> -->
                                    </ul>
                                </li>
                                @endif
                                <!--================== END ==================-->

                                <!--================== ANALISIS BIBLIOMETRIK ==================-->
                                @if (Auth::user()->adalahAdministrator())
                                <li class="menu-header">ANALISIS BIBLIOMETRIK</li>

                                <li class="{{ setActive('account/kategori') }}">
                                    <a class="nav-link" href="{{ route('account.kategori.index') }}">
                                        <i class="fas fa-dice-d6"></i> <span>Kategori </span>
                                    </a>
                                </li>

                                <li class="{{ setActive('account/Analisis-Bibliometrik') }}">
                                    <a class="nav-link" href="{{ route('account.analisisbibliometrik.index') }}">
                                        <i class="fas fa-file-signature"></i> <span>Data Pendaftar</span>
                                    </a>
                                </li>
                                @endif
                                <!--================== END ==================-->

                                <!--================== SCOPUS CAMP ==================-->
                                @if (Auth::user()->adalahAdministrator())
                                <li class="menu-header">SCOPUS CAMP</li>

                                <li class="{{ setActive('account/scopus-camp') }}">
                                    <a class="nav-link" href="{{ route('account.kategoriscopuscamp.index') }}">
                                        <i class="fas fa-dice-d6"></i> <span>Kategori </span>
                                    </a>
                                </li>

                                <li class="{{ setActive('account/PendaftaranScopusCamp') }}">
                                    <a class="nav-link" href="{{ route('account.pendaftaranscopuscamp.index') }}">
                                        <i class="fas fa-file-signature"></i> <span>Data Pendaftar</span>
                                    </a>
                                </li>
                                @endif
                                <!--================== END ==================-->

                                {{-- Menu KEUANGAN (Uang Masuk & Uang Keluar beserta kategorinya)
                                     dihapus 27 September 2026 atas permintaan pemilik.
                                     Laporan Uang Masuk/Keluar di bagian LAPORAN sengaja
                                     dibiarkan karena membaca data lama yang masih tersimpan. --}}

                                {{-- @if (Auth::user()->adalahAdministrator() || Auth::user()->jenis === 'penyewaan')
                                <li class="dropdown {{ setActive('account/tambah_barang'). setActive('account/penyewaan') }}  show">
                                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-car"></i><span>RENTAL KENDARAAN</span></a>
                                    <ul class="dropdown-menu">
                                        <li class="{{ setActive('account/tambah_barang') }}"><a class="nav-link" href="{{ route('account.tambah_barang.index') }}"><i class="fas fa-plus"></i>TAMBAH
                                            </a></li>
                                        <li class="{{ setActive('account/penyewaan') }}"><a class="nav-link" href="{{ route('account.penyewaan.index') }}"><i class="fas fa-list"></i>PENYEWAAN</a></li>
                                    </ul>
                                </li>
                                @endif --}}

                                <!--================== LAPORAN ==================-->
                                @if (Auth::user()->adalahOrangDalam() && ! Auth::user()->adalahKaryawan())
                                <li class="menu-header">LAPORAN</li>
                                @if (Auth::user()->adalahAdministrator())
                                <li class="{{ setActive('account/camp') . setActive('account/camp/search') }}">
                                    <a class="nav-link" href="{{ route('account.camp.index') }}">
                                        <i class="fas fa-campground"></i> <span>Laporan Camp</span>
                                    </a>
                                </li>
                                @endif

                                <li class="dropdown mb-5 {{ setActive('account/laporan_debit') . setActive('account/laporan_credit') . setActive('account/laporan_semua') . setActive('account/neraca') }} show">
                                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-chart-pie"></i><span>Laporan</span></a>
                                    <ul class="dropdown-menu">
                                        <li class="{{ setActive('account/laporan_debit') }}"><a class="nav-link" href="{{ route('account.laporan_debit.index') }}"><i class="fas fa-chart-line"></i> Uang Masuk</a></li>
                                        <li class="{{ setActive('account/laporan_credit') }}"><a class="nav-link" href="{{ route('account.laporan_credit.index') }}"><i class="fas fa-chart-area"></i> Uang Keluar</a></li>
                                        <li class="dropdown {{ setActive('account/laporan_semua') . setActive('account/neraca') }} show">
                                            <a href="#" class="nav-link has-dropdown"><i class="fas fa-chart-pie"></i><span>Semua</span></a>
                                            <ul class="dropdown-menu">
                                                <li class="{{ setActive('account/laporan_semua') }}"><a class="nav-link" href="{{ route('account.laporan_semua.index') }}"><i class="fas fa-chart-area"></i>Catatan</a></li>
                                                <li class="{{ setActive('account/neraca') }}"><a class="nav-link" href="{{ route('account.neraca.index') }}"><i class="fas fa-balance-scale"></i>Neraca</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                @endif
                                <!--================== END ==================-->

                                <!-- <li class="dropdown show">
                                    <a href="https://mail.hostinger.com/" class="nav-link" target="_blank">
                                        <i class="fas fa-envelope-open"></i>
                                        <span>MASUK EMAIL</span>
                                    </a>
                                </li> -->
                                @endif

                                <!--================== KEAMANAN ==================-->
                                @if (Auth::user()->adalahAdministrator())
                                <li class="{{ setActive('account/aktivitas-masuk') }}">
                                    <a class="nav-link" href="{{ route('account.aktivitas-masuk.index') }}">
                                        <i class="fas fa-user-shield"></i> <span>Aktivitas Masuk</span>
                                    </a>
                                </li>
                                @endif
                                <!--================== END ==================-->

                                <!-- jika user dengan level admin maka dapat akses menu maintenance -->
                                @if (Auth::user()->adalahAdministrator())
                                <li class="{{ setActive('account/maintenance') . setActive('account/pengguna/search') }}">
                                    <a class="nav-link" href="{{ route('account.maintenance.index') }}">
                                        <i class="fas fa-users-cog"></i> <span>MAINTENANCE</span>
                                    </a>
                                </li>
                                @endif
                                <!-- end maintenance -->

                                @else
                                @endif
                    </ul>
                </aside>
            </div>

        </div>
        <!-- Main Content -->
        @yield('content')

        @extends('layouts.version')
    </div>
    @endif

    <!--================== UCAPAN SELAMAT ==================-->
    <script>
        function getGreeting() {
            const currentTime = new Date();
            const currentHour = currentTime.getHours();
            let fullName = "{{ Auth::check() ? Auth::user()->full_name : '' }}";

            // Ambil hanya kata pertama dari nama lengkap
            fullName = fullName.split(' ')[0]; // Mengambil kata pertama saja

            console.log(fullName);

            let greeting;

            if (currentHour >= 5 && currentHour < 11) {
                greeting = "Selamat Pagi " + fullName;
            } else if (currentHour >= 11 && currentHour < 15) {
                greeting = "Selamat Siang " + fullName;
            } else if (currentHour >= 15 && currentHour < 18) {
                greeting = "Selamat Sore " + fullName;
            } else if (currentHour >= 1 && currentHour < 5) {
                greeting = "Selamat Dini Hari " + fullName;
            } else {
                greeting = "Selamat Malam " + fullName;
            }

            return greeting;
        }

        const greetingElement = document.getElementById("greeting");
        greetingElement.innerText = getGreeting();
    </script>
    <!--================== END ==================-->

    <!--================== GENERAL JS ==================-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        window.$ = window.jQuery;
    </script>
    <script src="{{ asset('assets/modules/popper.js') }}"></script>
    <script src="{{ asset('assets/modules/tooltip.js') }}"></script>
    <script src="{{ asset('assets/modules/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/modules/nicescroll/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('assets/js/stisla.js') }}"></script>
    <script src="{{ asset('assets/modules/select2/dist/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    {{-- Mengingat posisi gulir sidebar antar halaman. --}}
    <script src="{{ asset('assets/js/mis-ui.js') }}?v=16"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
    <!--================== END ==================-->

    @extends('layouts.alerts')
    </body>

</html>