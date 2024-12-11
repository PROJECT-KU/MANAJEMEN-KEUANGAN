<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>@yield('title')</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="{{ asset('assets/img/scopus.jpg') }}" rel="icon">
    <link href="{{ asset('assets/img/scopus.jpg') }}" rel="apple-touch-icon">

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
</head>

<body>

    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top">
        <div class="container-fluid container-xl d-flex align-items-center justify-content-between">

            <a href="{{ url('/blog') }}" class="logo d-flex align-items-center">
                <img style="width: 180px;" src="{{ asset('assets/img/LogoRSC.png') }}" alt="">
            </a>

            <nav id="navbar" class="navbar">
                <ul>
                    <li><a class="nav-link scrollto active" href="{{ url('/Cek-Plagiasi') }}">Cek Plagiasi</a></li>
                    <li><a class="nav-link scrollto" href="{{ url('/blog') }}">Cek Pesanan</a></li>
                    <li><a class="nav-link scrollto" href="{{ url('/blog/contact') }}">Contact</a></li>
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
                            <img style="width: 200px;" src="{{ asset('assets/img/LogoRSC.png') }}" alt="">
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
                        <h4>Useful Links</h4>
                        <ul>
                            <li><i class="bi bi-chevron-right"></i> <a href="{{ url('/blog') }}">Blog</a></li>
                            <li><i class="bi bi-chevron-right"></i> <a href="https://rumahscopus.com/courses/online-class/" target="_blank">Class Online</a></li>
                            <li><i class="bi bi-chevron-right"></i> <a href="https://rumahscopusfoundation.com/account/Scopus-Camp" target="_blank">Class Offline</a></li>
                            <li><i class="bi bi-chevron-right"></i> <a href="{{ url('/blog/contact') }}">Contact</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-2 col-6 footer-links">
                        <h4>Our Services</h4>
                        <ul>
                            <li><i class="bi bi-chevron-right"></i> <a href="#">Class Online</a></li>
                            <li><i class="bi bi-chevron-right"></i> <a href="#">Class Offline</a></li>
                            <li><i class="bi bi-chevron-right"></i> <a href="#">Cek Plagiasi</a></li>
                            <li><i class="bi bi-chevron-right"></i> <a href="#">Webinar</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-12 footer-contact text-center text-md-start">
                        <h4>Contact Us</h4>
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

    <!-- Template Main JS File -->
    <script src="{{ asset('assets/artikel/js/main.js') }}"></script>

</body>

</html>