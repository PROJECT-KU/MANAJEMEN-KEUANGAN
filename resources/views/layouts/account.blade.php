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
    <link rel="shortcut icon" href="{{ asset('assets/img/logo-pwa.png') }}">
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
    <script src="{{ asset('assets/modules/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/modules/moment.min.js') }}"></script>
    <script src="{{ asset('assets/modules/cleave-js/dist/cleave.min.js') }}"></script>
    <script src="{{ asset('assets/js/highcharts.js') }}"></script>
    <!-- zoom image -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

    <!-- end -->
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
</head>
@php
$isStatusnonactive = Auth::check() && Auth::user()->status === 'nonactive';

$tenggatDate = null;
$currentDate = strtotime(date('Y-m-d'));
$isTenggatExpired = false;

if (Auth::check() && Auth::user()->tenggat) {
$tenggatDate = strtotime(Auth::user()->tenggat);
$isTenggatExpired = $tenggatDate < $currentDate;
    }
    @endphp

    <body style="background-color: #F5F5F5;" class="{{ $agent->isMobile() ? 'is-mobile' : '' }}">
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <!--==================UNTUK DIVACE MOBILE==================-->
            @if ($agent->isMobile())
            <nav class="navbar navbar-expand-lg main-navbar shadow-sm">
                <form class="form-inline mr-auto d-flex align-items-center">
                    <p id="greeting" class="text-dark font-weight-bold mb-0 ml-4 mt-3" style="font-size:13px;"></p>
                </form>

                <!-- Dropdown Profil -->
                <ul class="navbar-nav navbar-right mr-2">
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user d-flex align-items-center text-dark">
                            @if (Auth::user()->gambar == null)
                            <img alt="image" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail rounded-circle" style="width: 50px; height:50px; margin: 5px 10px;">
                            @else
                            <img alt="image" src="{{ asset('assets/img/profil/' .  Auth::user()->gambar) }}" class="img-thumbnail rounded-circle" style="width: 50px; height:50px; margin: 5px 10px;">
                            @endif
                            <div class="d-sm-none d-lg-inline-block">Hi, {{ Auth::user()->full_name }}</div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="dropdown-title">Logged in as <strong>{{ Auth::user()->username }}</strong>
                                <hr>
                            </div>
                            <a href="{{ route('account.profil.show', ['id' => Auth::user()->id]) }}" class="dropdown-item has-icon">
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
                    <p id="greeting" class="text-white font-weight-bold mb-0 ml-3 d-flex align-items-center mt-3"></p>
                </form>

                <!-- Dropdown Profil -->
                @if (Auth::check())
                <ul class="navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user d-flex align-items-center">
                            @if (Auth::user()->gambar == null)
                            <img alt="image" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail rounded-circle" style="width: 50px; height:50px; margin: 5px 10px;">
                            @else
                            <img alt="image" src="{{ asset('assets/img/profil/' .  Auth::user()->gambar) }}" class="img-thumbnail rounded-circle" style="width: 50px; height:50px; margin: 5px 10px;">
                            @endif
                            <div class="d-sm-none d-lg-inline-block">Hi, {{ Auth::user()->full_name }}</div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="dropdown-title">Logged in as <strong>{{ Auth::user()->username }}</strong>
                                <hr>
                            </div>
                            <a href="{{ route('account.profil.show', ['id' => Auth::user()->id]) }}" class="dropdown-item has-icon">
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
                        <img src="{{ asset('assets/img/logo-header.png') }}" alt="logo" width="150">
                    </div>
                    <div class="sidebar-brand sidebar-brand-sm">
                        <img src="{{ asset('assets/img/logo-pwa.png') }}" alt="logo" width="50px">
                    </div>
                    <ul class="sidebar-menu">

                        <!--================== DASBOARD ==================-->
                        <li class="menu-header">DASHBOARD</li>
                        <li class="{{ setActive('account/dashboard') }}"><a class="nav-link" href="{{ route('account.dashboard.index') }}"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                        <!--================== END ==================-->

                        <!--================== CLINIC SCOPUS ==================-->
                        @php
                        $level = Auth::user()->level;
                        @endphp
                        <li class="menu-header">Clinik Scopus</li>
                        @if (in_array($level, ['manager', 'ceo']))
                        <li class="{{ setActive('account/customer') . setActive('account/pengguna/search') }}">
                            <a class="nav-link" href="{{ route('account.customer.index') }}">
                                <i class="fas fa-users"></i> <span>Data Customer</span>
                            </a>
                        </li>
                        <li class="{{ setActive('account/Clinik-Scopus-Biaya-Persesi') }}">
                            <a class="nav-link" href="{{ route('account.Clinik-Scopus-Biaya-Persesi.index') }}">
                                <i class="fas fa-coins"></i> <span>Biaya Persesi</span>
                            </a>
                        </li>

                        <li class="{{ setActive('account/Clinik-Scopus-Promo') }}">
                            <a class="nav-link" href="{{ route('account.Clinik-Scopus-Promo.index') }}">
                                <i class="fas fa-tags"></i> <span>Promo</span>
                            </a>
                        </li>
                        @endif

                        @if (in_array($level, ['manager', 'karyawan']))
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
                        @if (Auth::user()->level === 'user')
                        <li><a class="nav-link" href="{{ route('public.clinikscopus.index') }}"><i class="fas fa-comment"></i> <span>Konsultasi Sekarang</span></a></li>
                        @endif
                        <!--================== END ==================-->

                        @if (Auth::check() && Auth::user()->email_verified_at)
                        @php
                        $tenggatDate = Auth::user()->tenggat;
                        $isTenggatExpired = ($tenggatDate && strtotime($tenggatDate) < strtotime(date('Y-m-d'))); @endphp @php $isStatusnonactive=(Auth::user()->status === 'nonactive');
                            $tenggatDate = Auth::user()->tenggat;
                            $currentDate = strtotime(date('Y-m-d')); // Current date in Unix timestamp
                            $isTenggatExpired = ($tenggatDate && strtotime($tenggatDate) < $currentDate);
                                @endphp

                                <!--==================PERUSAHAAN==================-->
                                @if (Auth::user()->level === 'manager' || Auth::user()->level === 'ceo')
                                <li class="menu-header">PERUSAHAAN</li>
                                <li class="{{ setActive('account/company/' . Auth::user()->id . '/edit') }}">
                                    <a class="nav-link" href="{{ route('account.company.edit', ['id' => Auth::user()->id]) }}">
                                        <i class="fas fa-building"></i> <span>Company</span>
                                    </a>
                                </li>
                                @endif
                                <!--================== END ==================-->

                                <!--================== KARYAWAN ==================-->
                                @if (Auth::user()->level !== 'user')
                                <li class="menu-header">KARYAWAN</li>
                                @endif

                                @if (Auth::user()->level === 'manager' || Auth::user()->level === 'ceo')
                                <li class="{{ setActive('account/pengguna') . setActive('account/pengguna/search') }}">
                                    <a class="nav-link" href="{{ route('account.pengguna.index') }}">
                                        <i class="fas fa-users"></i> <span>Data Karyawan</span>
                                    </a>
                                </li>
                                @endif

                                @if ($isStatusnonactive)
                                @else
                                @if (Auth::user()->level !== 'user')
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

                                @if (Auth::user()->level !== 'karyawan' && Auth::user()->level !== 'user')
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

                                @if (Auth::user()->level !== 'user')
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
                                @if (Auth::user()->level == 'staff' || Auth::user()->level == 'manager' )
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

                                @if (Auth::user()->level !== 'manager' && Auth::user()->level !== 'karyawan' && Auth::user()->level !== 'user')
                                <li class="menu-header">PAPER</li>
                                @endif
                                @if (Auth::user()->level === 'manager' || Auth::user()->level === 'ceo' || Auth::user()->level === 'staff' || Auth::user()->id === 99)
                                <li class="{{ setActive('account/refrensi-paper/data') }}">
                                    <a class="nav-link" href="{{ route('account.refrensi-paper.index') }}">
                                        <i class="fas fa-folder"></i> <span>Refrensi Paper</span>
                                    </a>
                                </li>
                                @endif
                                <!--================== END ==================-->

                                <!--================== BLOG ==================-->
                                @if (Auth::user()->level === 'manager' || Auth::user()->level === 'ceo' || Auth::user()->level === 'staff' || Auth::user()->id === 83 || Auth::user()->id === 87)
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
                                @if (Auth::user()->level == 'staff' || Auth::user()->level == 'manager')
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
                                @if (Auth::user()->level == 'staff' || Auth::user()->level == 'manager')
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

                                <!--================== KEUANGAN ==================-->
                                @if (Auth::user()->level !== 'user')
                                <li class="menu-header">KEUANGAN</li>
                                <li class="dropdown {{ setActive('account/categories_debit'). setActive('account/debit') }}">
                                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-wallet"></i><span>Uang Masuk</span></a>
                                    <ul class="dropdown-menu">
                                        <li class="{{ setActive('account/categories_debit') }}"><a class="nav-link" href="{{ route('account.categories_debit.index') }}"><i class="fas fa-dice-d6"></i> Kategori</a></li>
                                        <li class="{{ setActive('account/debit') }}"><a class="nav-link" href="{{ route('account.debit.index') }}"><i class="fas fa-money-check-alt"></i> Uang Masuk</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown {{ setActive('account/categories_credit'). setActive('account/credit') }}">
                                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-wallet"></i><span>Uang Keluar</span></a>
                                    <ul class="dropdown-menu">
                                        <li class="{{ setActive('account/categories_credit') }}"><a class="nav-link" href="{{ route('account.categories_credit.index') }}"><i class="fas fa-dice-d6"></i> Kategori</a></li>
                                        <li class="{{ setActive('account/credit') }}"><a class="nav-link" href="{{ route('account.credit.index') }}"><i class="fas fa-money-check-alt"></i> Uang Keluar</a></li>
                                    </ul>
                                </li>
                                @endif
                                <!--================== END ==================-->

                                <!-- @if (Auth::user()->level === 'admin' || Auth::user()->jenis === 'penyewaan')
                                <li class="dropdown {{ setActive('account/tambah_barang'). setActive('account/penyewaan') }}  show">
                                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-car"></i><span>RENTAL KENDARAAN</span></a>
                                    <ul class="dropdown-menu">
                                        <li class="{{ setActive('account/tambah_barang') }}"><a class="nav-link" href="{{ route('account.tambah_barang.index') }}"><i class="fas fa-plus"></i>TAMBAH
                                            </a></li>
                                        <li class="{{ setActive('account/penyewaan') }}"><a class="nav-link" href="{{ route('account.penyewaan.index') }}"><i class="fas fa-list"></i>PENYEWAAN</a></li>
                                    </ul>
                                </li>
                                @endif -->

                                <!--================== LAPORAN ==================-->
                                @if (Auth::user()->level !== 'user' && Auth::user()->level !== 'karyawan')
                                <li class="menu-header">LAPORAN</li>
                                @if (Auth::user()->level === 'manager' || Auth::user()->level === 'ceo' || Auth::user()->level === 'staff')
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

                                <!-- jika user dengan level admin maka dapat akses menu maintenance -->
                                @if (Auth::user()->level === 'admin')
                                <li class="{{ setActive('account/maintenance') . setActive('account/pengguna/search') }}">
                                    <a class="nav-link" href="{{ route('account.maintenance.index') }}">
                                        <i class="fas fa-users-cog"></i> <span>MAINTENANCE</span>
                                    </a>
                                </li>
                                <li class="{{ setActive('account/sewa') . setActive('account/pengguna/search') }}">
                                    <a class="nav-link" href="{{ route('account.sewa.index') }}">
                                        <i class="fas fa-bell"></i> <span>NOTIF SEWA</span>
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
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
    <!--================== END ==================-->

    @extends('layouts.alerts')
    </body>

</html>