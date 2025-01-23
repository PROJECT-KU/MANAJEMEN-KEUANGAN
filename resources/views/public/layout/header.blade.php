<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>@yield('title')</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="{{ asset('assets/img/Icon-RS.png') }}" rel="icon">
    <link href="{{ asset('assets/img/Icon-RS.png') }}" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/artikel/vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/artikel/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/artikel/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/artikel/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/artikel/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/artikel/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/modules/fontawesome/css/all.min.css') }}">
    <script src="{{ asset('assets/js/sweetalert.min.js') }}"></script>

    <!-- Template Main CSS File -->
    <link href="{{ asset('assets/artikel/css/style.css') }}" rel="stylesheet">

    <style>
        /* <!-- ANIMASI GARIS PADA MENU --> */
        .navbar ul li {
            position: relative;
            /* Ensure positioning context for the underline (::after) */
        }

        .navbar ul li a {
            padding: 0px;
            margin: 0 10px;
            /* Use horizontal margin only to space out the menu items */
            display: inline-block;
            position: relative;
            /* Necessary for the underline to be positioned correctly */
        }

        .navbar ul li a.active {
            color: #ff3131;
            /* Active text color */
            text-decoration: none;
            /* Remove any default underline */
        }

        .navbar ul li a.active::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 4px;
            bottom: -2px;
            left: 0;
            background: linear-gradient(to right, #ff3131, #ff914d);
            border-radius: 2px;
        }

        /* <!-- END --> */
    </style>
</head>

<body>

    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top">
        <div class="container-fluid container-xl d-flex align-items-center justify-content-between">

            <a href="{{ url('/') }}" class="logo d-flex align-items-center">
                <img style="width: 180px;" src="{{ asset('assets/img/LogoRSC.png') }}" alt="">
            </a>

            <nav id="navbar" class="navbar">
                <ul>
                    <li>
                        <a class="nav-link scrollto {{ Request::is('/') ? 'active' : '' }}" href="{{ url('/') }}">Beranda</a>
                    </li>
                    <li>
                        <a class="nav-link scrollto {{ Request::is('blog') || Request::is('blog/topic/*') ? 'active' : '' }} || Request::is('blog/topic/blog-single/*') ? 'active' : '' }}" href="{{ url('/blog') }}">Blog</a>
                    </li>
                    <li>
                        <a class="nav-link scrollto {{ Request::is('Scopus-Kafe') || Request::is('Scopus-Kafe/Form-Pendaftaran') ? 'active' : '' }}"
                            href="{{ url('/Scopus-Kafe') }}">Scopus Kafe</a>
                    </li>
                    <li class="dropdown">
                        <a href="#"><span>Class<i class="bi bi-chevron-down"></i></a>
                        <ul>
                            <li><a href="https://rumahscopus.com/courses/online-class/" target="_blank">Class Online</a></li>
                            <!-- <li><a href="https://rumahscopusfoundation.com/account/Scopus-Camp" target="_blank">Class Offline</a></li> -->
                            <li><a href="https://rumahscopus.com/event/" target="_blank">Class Offline</a></li>
                        </ul>
                    </li>
                    <li>
                        <a class="nav-link scrollto {{ Request::is('paperisasi/public/data') ? 'active' : '' }} || {{ Request::is('paperisasi/public/data/search') ? 'active' : '' }}" href="{{ url('/paperisasi/public/data') }}">Cek ID</a>
                    </li>
                    <li>
                        <a class="nav-link scrollto {{ Request::is('Cek-Plagiasi') ? 'active' : '' }}" href="{{ url('/Cek-Plagiasi') }}">Cek Plagiasi</a>
                    </li>
                    <li>
                        <a class="nav-link scrollto {{ Request::is('Refrensi-Paper/data') ? 'active' : '' }} || {{ Request::is('Refrensi-Paper/selengkapnya/*') ? 'active' : '' }" href="{{ url('/Refrensi-Paper') }}">Refrensi Paper</a>
                    </li>
                    <li>
                        <a class="nav-link scrollto {{ Request::is('contact') ? 'active' : '' }}" href="{{ url('/contact') }}">Kontak</a>
                    </li>
                </ul>
                <i class="bi bi-list mobile-nav-toggle"></i>
            </nav><!-- .navbar -->

        </div>
    </header><!-- End Header -->

    @yield('konten')

    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer">
        <div class="footer-top">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-5 col-md-12 footer-info">
                        <a href="{{url('/blog')}}" class="logo d-flex align-items-center">
                            <img src="{{ asset('assets/img/LogoRSC.png') }}" alt="">
                        </a>
                        <p>Rumah Scopus adalah penyedia layanan jasa di bidang pendampingan penyusunan artikel ilmiah dan publikasi artikel ke jurnal terindeks Scopus.</p>
                        <div class="social-links mt-3">
                            <a href="https://www.tiktok.com/@rumah_scopus?_t=8l3900jRtiM&_r=1" class="tiktok" target="_blank"><i class="bi bi-tiktok"></i></a>
                            <a href="https://www.facebook.com/RumahScopusAkademi" class="facebook" target="_blank"><i class="bi bi-facebook"></i></a>
                            <a href="https://www.instagram.com/rumah_scopus/" class="instagram" target="_blank"><i class="bi bi-instagram"></i></a>
                            <a href="https://www.youtube.com/@rumahscopus" class="youtube" target="_blank"><i class="bi bi-youtube"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-2 col-6 footer-links">
                        <h4>Link Bermanfaat</h4>
                        <ul>
                            <li><i class="bi bi-chevron-right"></i> <a href="{{ url('/blog') }}">Blog</a></li>
                            <li><i class="bi bi-chevron-right"></i> <a href="https://rumahscopus.com/courses/online-class/" target="_blank">Class Online</a></li>
                            <li><i class="bi bi-chevron-right"></i> <a href="https://rumahscopusfoundation.com/account/Scopus-Camp" target="_blank">Class Offline</a></li>
                            <li><i class="bi bi-chevron-right"></i> <a href="{{ url('/blog/contact') }}">Contact</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-2 col-6 footer-links">
                        <h4>Layanan Kami</h4>
                        <ul>
                            <li><i class="bi bi-chevron-right"></i> <a href="#">Class Online</a></li>
                            <li><i class="bi bi-chevron-right"></i> <a href="#">Class Offline</a></li>
                            <li><i class="bi bi-chevron-right"></i> <a href="#">Scopus Kafe</a></li>
                            <li><i class="bi bi-chevron-right"></i> <a href="#">Online Training</a></li>
                            <li><i class="bi bi-chevron-right"></i> <a href="#">Meeting Room</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-12 footer-contact text-center text-md-start">
                        <h4>Hubungi Kami</h4>
                        <p>
                            Bangunsari, Jl. Bangunsari, Bangunsari, Bangun Kerto, Turi, Sleman Regency, Special Region of Yogyakarta 55551 <br><br>
                            <strong>Phone:</strong> <b>[Dinar]</b> +62 812-2688-3280<br>
                            <strong>Email:</strong> info@rumahscopusfoundation.com<br>
                        </p>

                    </div>

                </div>
            </div>
        </div>

        <div class="container">
            <div class="copyright">
                &copy; Copyright <strong><span>Rumah Scopus Foundation</span></strong>. All Rights Reserved
            </div>
            <div class="credits">
            </div>
        </div>
    </footer><!-- End Footer -->


    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="{{ asset('assets/artikel/vendor/purecounter/purecounter_vanilla.js') }}"></script>
    <script src="{{ asset('assets/artikel/vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('assets/artikel/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/artikel/vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('assets/artikel/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('assets/artikel/vendor/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/artikel/vendor/php-email-form/validate.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Template Main JS File -->
    <script src="{{ asset('assets/artikel/js/main.js') }}"></script>

</body>

</html>