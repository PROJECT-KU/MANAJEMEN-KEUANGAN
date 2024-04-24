<!DOCTYPE html>
<html lang="en">

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
    <link rel="shortcut icon" href="{{ asset('assets/img/logonew1.png') }}">
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
    <script src="{{ asset('assets/modules/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('assets/modules/bootstrap-timepicker/js/bootstrap-timepicker.min.js') }}"></script>
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
</head>
@php
$isStatusOff = (Auth::user()->status === 'off');
$tenggatDate = strtotime(Auth::user()->tenggat);
$currentDate = strtotime(date('Y-m-d')); // Current date in Unix timestamp
$isTenggatExpired = ($tenggatDate < $currentDate); @endphp <body style="background-color: #f3f3f3;">
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar">
                <form class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3 mb-3" id="NavbarPwa">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
                    </ul>
                    <p id="greeting" style="color: #ffffff; font-size:13px; width:150px; font-weight: bold;" class="mt-2"></p>
                </form>

                <!--================== dropdown profil ==================-->
                <ul class="navbar-nav navbar-right">
                    <li class="dropdown"><a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            @if (Auth::user()->gambar == null)
                            <img alt="image" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail rounded-circle mb-2" style="width: 50px; height:50px;">
                            @else
                            <img alt="image" src="{{ asset('images/' .  Auth::user()->gambar) }}" class="img-thumbnail rounded-circle mb-2" style="width: 50px; height:50px;">
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
                            <a href="{{ route('account.profil.password', ['id' => Auth::user()->id]) }}" class="dropdown-item has-icon">
                                <i class="fas fa-unlock-alt"></i> RESET PASSWORD
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
                <!--================== end ==================-->
            </nav>
            <div class="main-sidebar sidebar-style-2">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand">
                        <img src="{{ asset('assets/img/logoterbaru.png') }}" alt="logo" width="150">
                    </div>
                    <div class="sidebar-brand sidebar-brand-sm">
                        <img src="{{ asset('assets/img/logoterbaru1.png') }}" alt="logo" width="50px">
                    </div>
                    <ul class="sidebar-menu">
                        <li class="menu-header">MAIN MENU</li>
                        <li class="{{ setActive('account/dashboard') }}"><a class="nav-link" href="{{ route('account.dashboard.index') }}"><i class="fas fa-home"></i> <span>DASHBOARD</span></a></li>
                        @if (Auth::user()->email_verified_at)


                        @php
                        $tenggatDate = Auth::user()->tenggat;
                        $isTenggatExpired = ($tenggatDate && strtotime($tenggatDate) < strtotime(date('Y-m-d'))); @endphp @php $isStatusOff=(Auth::user()->status === 'off');
                            $tenggatDate = Auth::user()->tenggat;
                            $currentDate = strtotime(date('Y-m-d')); // Current date in Unix timestamp
                            $isTenggatExpired = ($tenggatDate && strtotime($tenggatDate) < $currentDate); $isPenyewaanUser=(Auth::user()->jenis === 'penyewaan');
                                $isAdminOrPenyewaan = (Auth::user()->level === 'admin' || $isPenyewaanUser);
                                @endphp


                                @if (Auth::user()->level === 'admin' || Auth::user()->level === 'manager' || Auth::user()->level === 'ceo')
                                <li class="{{ setActive('account/pengguna') }} . {{ setActive('account/pengguna/search') }}">
                                    <a class="nav-link @if ($isTenggatExpired) disabled @endif" href="{{ route('account.pengguna.index') }}">
                                        <i class="fas fa-user"></i> <span>PENGGUNA</span>
                                    </a>
                                </li>
                                <li class="{{ setActive('account/company/' . Auth::user()->id . '/edit') }}">
                                    <a class="nav-link @if ($isTenggatExpired) disabled @endif" href="{{ route('account.company.edit', ['id' => Auth::user()->id]) }}">
                                        <i class="fas fa-building"></i> <span>COMPANY</span>
                                    </a>
                                </li>
                                <li class="{{ setActive('account/camp') }} . {{ setActive('account/camp/search') }}">
                                    <a class="nav-link @if ($isTenggatExpired) disabled @endif" href="{{ route('account.camp.index') }}">
                                        <i class="fas fa-campground"></i> <span>LAPORAN CAMP</span>
                                    </a>
                                </li>
                                <li class="{{ setActive('account/karir') }} . {{ setActive('account/camp/search') }}">
                                    <a class="nav-link @if ($isTenggatExpired) disabled @endif" href="{{ route('karir.list') }}">
                                        <i class="fas fa-user-tie"></i> <span>KARIR</span>
                                    </a>
                                </li>
                                <li class="dropdown {{ setActive('account/Laporan-Peserta'). setActive('account/Scopus-Camp'). setActive('account/kategori') }}">
                                    <a href="#" class="nav-link has-dropdown">
                                        <i class="fas fa-user-cog"></i><span>PESERTA</span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="{{ setActive('account/kategori') }}"><a class="nav-link" href="{{ route('account.kategori.index') }}"><i class="fas fa-dice-d6"></i>KATEGORI</a></li>
                                        <li class="{{ setActive('account/Scopus-Camp') }}"><a class="nav-link" href="{{ route('account.scopuscamp.index') }}"><i class="fas fa-file-signature"></i>PENDAFTARAN</a></li>
                                        <li class="{{ setActive('account/Laporan-Peserta') }}"><a class="nav-link" href="{{ route('account.peserta.list') }}"><i class="fas fa-user-edit"></i>EVALUASI</a></li>
                                    </ul>
                                </li>
                                @endif

                                @if ($isStatusOff || $isTenggatExpired)
                                <li class="dropdown {{ setActive('account/gaji'). setActive('account/debit') }}">
                                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-users"></i><span>KARYAWAN</span></a>
                                </li>
                                <li class="dropdown {{ setActive('account/categories_debit'). setActive('account/debit') }}">
                                    <a href="#" class="nav-link has-dropdown" disabled><i class="fas fa-wallet"></i><span>UANG MASUK</span></a>
                                </li>
                                <li class="dropdown {{ setActive('account/categories_credit'). setActive('account/credit') }}">
                                    <a href="#" class="nav-link has-dropdown" disabled><i class="fas fa-wallet"></i><span>UANG KELUAR</span></a>
                                </li>
                                <li class="dropdown {{ setActive('account/laporan_debit'). setActive('account/laporan_credit') }}. {{ setActive('account/laporan_semua') }}">
                                    <a href="#" class="nav-link has-dropdown" disabled><i class="fas fa-chart-pie"></i><span>LAPORAN</span></a>
                                </li>
                                @else
                                @if (Auth::user()->level === 'admin' || Auth::user()->level === 'manager' || Auth::user()->level === 'staff' || Auth::user()->level === 'karyawan' || Auth::user()->level === 'trainer' || Auth::user()->level === 'ceo')
                                <li class="dropdown {{ setActive('account/gaji'). setActive('account/presensi') }}">
                                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-users"></i><span>KARYAWAN</span></a>
                                    <ul class="dropdown-menu">
                                        <li class="{{ setActive('account/gaji') }}"><a class="nav-link" href="{{ route('account.gaji.index') }}"><i class="fas fa-dollar-sign"></i>GAJI</a></li>
                                        <li class="{{ setActive('account/presensi') }}"><a class="nav-link" href="{{ route('account.presensi.index') }}"><i class="fas fa-user-clock"></i>PRESENSI</a></li>

                                    </ul>
                                </li>
                                @endif

                                @if (Auth::user()->level === 'admin' || Auth::user()->level === 'manager'|| Auth::user()->level === 'ceo')
                                <li class="dropdown {{ setActive('account/article') . setActive('account/artikel-kategori') }}">
                                    <a href="#" class="nav-link has-dropdown">
                                        <i class="fas fa-newspaper"></i><span>ARTIKEL</span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="{{ setActive('account/artikel-kategori') }}"><a class="nav-link" href="{{ route('account.Kategori-Artikel.index') }}"><i class="fas fa-dice-d6"></i>KATEGORI</a></li>
                                        <li class="{{ setActive('account/article') }}"><a class="nav-link" href="{{ route('account.Artikel.index') }}"><i class="fas fa-file-signature"></i>DATA ARTIKEL</a></li>
                                        <li class="{{ setActive('account/article') }}"><a class="nav-link" href="{{ route('account.Artikel.index') }}"><i class="fas fa-comments"></i>DATA KOMENTAR</a></li>
                                    </ul>
                                </li>
                                @endif

                                <li class="dropdown {{ setActive('account/categories_debit'). setActive('account/debit') }}">
                                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-wallet"></i><span>UANG MASUK</span></a>
                                    <ul class="dropdown-menu">
                                        <li class="{{ setActive('account/categories_debit') }}"><a class="nav-link" href="{{ route('account.categories_debit.index') }}"><i class="fas fa-dice-d6"></i> KATEGORI</a></li>
                                        <li class="{{ setActive('account/debit') }}"><a class="nav-link" href="{{ route('account.debit.index') }}"><i class="fas fa-money-check-alt"></i> UANG MASUK</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown {{ setActive('account/categories_credit'). setActive('account/credit') }}">
                                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-wallet"></i><span>UANG KELUAR</span></a>
                                    <ul class="dropdown-menu">
                                        <li class="{{ setActive('account/categories_credit') }}"><a class="nav-link" href="{{ route('account.categories_credit.index') }}"><i class="fas fa-dice-d6"></i> KATEGORI</a></li>
                                        <li class="{{ setActive('account/credit') }}"><a class="nav-link" href="{{ route('account.credit.index') }}"><i class="fas fa-money-check-alt"></i> UANG KELUAR</a></li>
                                    </ul>
                                </li>

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

                                <li class="dropdown {{ setActive('account/laporan_debit') }} {{ setActive('account/laporan_credit') }} {{ setActive('account/laporan_semua') }} {{ setActive('account/neraca') }} show">
                                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-chart-pie"></i><span>LAPORAN</span></a>
                                    <ul class="dropdown-menu">
                                        <li class="{{ setActive('account/laporan_debit') }}"><a class="nav-link" href="{{ route('account.laporan_debit.index') }}"><i class="fas fa-chart-line"></i> UANG MASUK</a></li>
                                        <li class="{{ setActive('account/laporan_credit') }}"><a class="nav-link" href="{{ route('account.laporan_credit.index') }}"><i class="fas fa-chart-area"></i> UANG KELUAR</a></li>
                                        <li class="dropdown {{ setActive('account/laporan_semua') }} {{ setActive('account/neraca') }} show">
                                            <a href="#" class="nav-link has-dropdown"><i class="fas fa-chart-pie"></i><span>SEMUA</span></a>
                                            <ul class="dropdown-menu">
                                                <li class="{{ setActive('account/laporan_semua') }}"><a class="nav-link" href="{{ route('account.laporan_semua.index') }}"><i class="fas fa-chart-area"></i>CATATAN</a></li>
                                                <li class="{{ setActive('account/neraca') }}"><a class="nav-link" href="{{ route('account.neraca.index') }}"><i class="fas fa-balance-scale"></i>NERACA</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>

                                <li class="dropdown show">
                                    <a href="https://mail.hostinger.com/" class="nav-link" target="_blank">
                                        <i class="fas fa-envelope-open"></i>
                                        <span>MASUK EMAIL</span>
                                    </a>
                                </li>
                                @endif

                                <!-- jika user dengan level admin maka dapat akses menu maintenance -->
                                @if (Auth::user()->level === 'admin')
                                <li class="{{ setActive('account/maintenance') }} . {{ setActive('account/pengguna/search') }}">
                                    <a class="nav-link" href="{{ route('account.maintenance.index') }}">
                                        <i class="fas fa-users-cog"></i> <span>MAINTENANCE</span>
                                    </a>
                                </li>
                                <li class="{{ setActive('account/sewa') }} . {{ setActive('account/pengguna/search') }}">
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

            <!-- Main Content -->
            @yield('content')

            @extends('layouts.version')
        </div>
    </div>

    <!--================== CEK APAKAH PWA ATAU WEBSITE ==================-->
    <script>
        // Fungsi untuk mendeteksi apakah perangkat adalah ponsel atau browser
        function isMobileDevice() {
            return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
        }

        // Fungsi untuk menyembunyikan atau menampilkan elemen berdasarkan tipe perangkat
        function toggleElementBasedOnDevice() {
            var NavbarPwa = document.getElementById('NavbarPwa');

            if (isMobileDevice()) {
                // Jika aplikasi berjalan di perangkat seluler (PWA)
                NavbarPwa.style.display = 'none';
            } else {
                // Jika aplikasi berjalan di browser
                NavbarPwa.style.display = 'block';
            }
        }

        // Panggil fungsi ketika halaman dimuat
        window.addEventListener('load', toggleElementBasedOnDevice);
    </script>
    <!--================== END ==================-->

    <!--================== UCAPAN SELAMAT ==================-->
    <script>
        function getGreeting() {
            const currentTime = new Date();
            const currentHour = currentTime.getHours();
            let fullName = "{{ Auth::user()->full_name }}"; // Ganti ini dengan cara Anda mendapatkan nama lengkap pengguna

            // Batasi nama hingga 50 karakter
            if (fullName.length > 15) {
                fullName = fullName.slice(0, 15);
            }

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
    <script src="{{ asset('assets/modules/popper.js') }}"></script>
    <script src="{{ asset('assets/modules/tooltip.js') }}"></script>
    <script src="{{ asset('assets/modules/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/modules/nicescroll/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('assets/js/stisla.js') }}"></script>
    <script src="{{ asset('assets/modules/select2/dist/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <!--================== END ==================-->

    @extends('layouts.alerts')
    </body>

</html>