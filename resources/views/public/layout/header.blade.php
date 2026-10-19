<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>@yield('title')</title>
    <meta content="" name="description">
    <meta content="" name="keywords">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Favicons -->
    <link href="{{ asset('assets/img/Icon-RS.png') }}" rel="icon">
    <link href="{{ asset('assets/img/Icon-RS.png') }}" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    {{-- Figtree untuk menu kepala situs.

         Dimuat di sini, bukan lewat @push('kepala') seperti huruf judul
         beranda: kepala situs muncul di SETIAP halaman publik, jadi kalau
         hurufnya cuma dimuat beranda, menu yang sama akan berganti rupa
         begitu orang membuka Blog. Satu keluarga huruf, empat ketebalan. --}}
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Figtree:wght@500;600;700;800&display=swap">

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
        /* ================= KEPALA SITUS & MENU =================
           Ditulis DI SINI, bukan di assets/artikel/css/style.css.

           Berkas itu milik templat yang dipakai seluruh halaman publik —
           menyuntingnya berarti menyentuh layar yang sedang tidak
           dikerjakan, dan tidak ada yang memberi tahu kalau ada yang
           rusak di salah satunya. Blok ini dimuat SESUDAH style.css, jadi
           pada bobot yang sama ia yang menang; tidak satu pun butuh
           !important.

           Warnanya mengikuti beranda, dan jingganya diambil dari berkas
           logo yang terpasang persis di sebelah menu ini (#e76d20). */
        #header {
            --kop-jingga: #e76d20;
            --kop-jingga-muda: #fb8c3d;
            /* Khusus tombol berisi penuh — lihat alasannya di .kop-tombol. */
            --kop-jingga-kuat: #b85213;
            --kop-jingga-kuat-2: #a8470e;
            --kop-tinta: #16233f;
            --kop-kertas: #fdf5e8;
            --kop-kertas-2: #f8ead4;
            --kop-garis: #efe0c8;

            background: #fff;
            border-bottom: 1px solid var(--kop-garis);
            padding: 14px 0;
        }
        #header > .container-fluid { gap: 18px; }

        /* Tingginya yang dipatok, lebarnya auto — supaya nisbahnya utuh.
           max-height: none wajib: aturan templat memasangnya 40px, dan
           dengan height 46px yang menang justru batas atasnya. */
        #header .logo img {
            height: 46px; width: auto;
            max-height: none; margin-right: 0;
        }
        .header.header-scrolled {
            box-shadow: 0 6px 24px rgba(22, 35, 63, .08);
        }

        /* --------------------------------------------- menu layar lebar */
        /* Tautannya dikumpulkan dalam satu pil krem.

           Sebelumnya enam kata mengambang di bidang putih tanpa satu pun
           bentuk yang mengikatnya, jadi jarak antara logo, menu, dan tombol
           terbaca kebetulan. Dengan pil ini menunya jadi SATU benda yang
           punya tepi, dan ketiganya berdiri sebagai tiga bagian yang jelas.

           Tombol masuknya tetap di dalam <ul> yang sama — memindahkannya
           keluar akan membuatnya hilang dari panel menu ponsel, yang
           dirakit templat dari <ul> itu juga. Ia didorong keluar pil lewat
           margin negatif. */
        @media (min-width: 1201px) {
            #navbar > ul {
                gap: 2px; padding: 5px 7px;
                background: var(--kop-kertas);
                border: 1px solid var(--kop-garis);
                border-radius: 999px;
            }
            #navbar > ul > li:last-child { margin: -6px -8px -6px 10px; }
        }

        #navbar a,
        #navbar a:focus {
            padding: 9px 14px;
            margin: 0 2px;
            border-radius: 11px;
            font-family: 'Figtree', 'Nunito', sans-serif;
            font-size: 15px; font-weight: 700;
            color: var(--kop-tinta);
            transition: color .18s ease, background .18s ease;
        }
        #navbar a:hover,
        #navbar li:hover > a {
            color: var(--kop-jingga);
            background: var(--kop-kertas-2);
        }

        /* Butir yang sedang dibuka: tulisannya jingga, dan sebatang
           penanda membulat di bawahnya. Menggantikan garis gradien
           merah-jingga lama yang warnanya tidak ada di logo mana pun. */
        #navbar a.active,
        #navbar a.active:focus { color: var(--kop-jingga); background: transparent; }
        #navbar a.active::after {
            content: ''; position: absolute;
            left: 14px; right: 14px; bottom: 2px; height: 3px;
            border-radius: 99px;
            background: var(--kop-jingga);
        }
        #navbar li { position: relative; }

        /* ------------------------------------------- daftar turun Class */
        #navbar .dropdown ul {
            left: 6px; padding: 8px;
            background: var(--kop-kertas);
            border: 2px solid var(--kop-tinta);
            border-radius: 16px;
            /* Bayangan PEJAL, sama seperti kartu di beranda. Bayangan
               kabur adalah tanda paling khas antarmuka cetakan. */
            box-shadow: 5px 5px 0 rgba(22, 35, 63, .17);
        }
        #navbar .dropdown ul a {
            padding: 9px 13px; margin: 0; border-radius: 9px;
            font-size: 14.5px; font-weight: 600;
        }
        #navbar .dropdown ul li:hover > a,
        #navbar .dropdown ul a:hover {
            color: var(--kop-jingga); background: var(--kop-kertas-2);
        }

        /* ------------------------------------------------ tombol masuk */
        /* Putih di atas jingga yang LEBIH DALAM — sama seperti tombol di
           beranda, dan dengan alasan yang sama.

           Terukur: putih di atas jingga logo #e76d20 berasio 3,18 (gagal);
           tinta di atas jingga logo 4,91 (lulus, tetapi dua warna gelap
           sedang berdampingan terbaca kurang menonjol); putih di atas
           #b85213 berasio 4,94 — lulus DAN menonjol. */
        #navbar .kop-tombol,
        #navbar .kop-tombol:focus {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 9px 18px; margin: 0;
            background: var(--kop-jingga-kuat);
            color: #fff;
            border: 2px solid var(--kop-tinta);
            border-radius: 999px;
            font-family: 'Figtree', sans-serif; font-size: 14.5px; font-weight: 800;
            box-shadow: 3px 3px 0 var(--kop-tinta);
            transition: transform .16s ease, box-shadow .16s ease;
        }
        /* Saat disentuh latarnya DIPEKATKAN, bukan dimudakan: jingga muda
           menjatuhkan rasio putihnya kembali ke 2,35. */
        #navbar .kop-tombol:hover {
            color: #fff; background: var(--kop-jingga-kuat-2);
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0 var(--kop-tinta);
        }
        #navbar .kop-tombol img {
            width: 24px; height: 24px; border-radius: 50%; object-fit: cover;
            border: 2px solid #fff;
        }

        /* ================= LAYAR SEMPIT ================= */
        /* Tombol hamburgernya jadi TOMBOL, bukan ikon telanjang.

           Sebagai ikon 28px tanpa bingkai, sasaran sentuhnya jauh di bawah
           44px yang disarankan untuk jempol — dan di layar terang ia
           nyaris tidak terbaca sebagai sesuatu yang bisa ditekan. */
        .mobile-nav-toggle {
            display: none;
            width: 44px; height: 44px;
            align-items: center; justify-content: center;
            border: 2px solid var(--kop-tinta); border-radius: 13px;
            background: var(--kop-kertas);
            color: var(--kop-jingga);
            font-size: 22px; line-height: 1;
            box-shadow: 3px 3px 0 var(--kop-tinta);
            transition: background .18s ease, color .18s ease;
        }
        @media (max-width: 1200px) {
            .mobile-nav-toggle { display: inline-flex; }
        }
        .mobile-nav-toggle.bi-x {
            position: fixed; top: 18px; right: 18px; z-index: 9999;
            background: var(--kop-tinta); color: var(--kop-kertas);
            border-color: var(--kop-kertas);
            box-shadow: none;
        }

        /* Tirai gelapnya memakai tinta, bukan biru templat. */
        .navbar-mobile { background: rgba(22, 35, 63, .66); }

        /* SETIAP aturan di bawah ditulis `#navbar.navbar-mobile`, bukan
           `.navbar-mobile` saja — dan itu bukan kerapian.

           Aturan layar lebar di atas memakai #navbar, yang berbobot ID.
           Selektor berkelas saja KALAH melawannya, jadi versi pertama blok
           ini mati diam-diam: terukur di ponsel, butir menunya memakai
           padding 14px/9px dan ukuran 15px milik layar lebar, latar butir
           aktifnya tetap tembus pandang, dan penandanya jadi campuran —
           lebar 5px dari sini, tinggi 3px dari sana.

           Gejalanya paling menipu: menunya tampak HAMPIR benar, jadi mudah
           disimpulkan selesai. */
        #navbar.navbar-mobile ul {
            top: 76px; right: 14px; left: 14px; bottom: auto;
            max-height: calc(100vh - 100px);
            padding: 10px;
            background: var(--kop-kertas);
            border: 2px solid var(--kop-tinta);
            border-radius: 20px;
            box-shadow: 7px 7px 0 rgba(22, 35, 63, .3);
        }
        #navbar.navbar-mobile a,
        #navbar.navbar-mobile a:focus {
            padding: 14px; margin: 0; border-radius: 12px;
            font-size: 16px; font-weight: 700;
            color: var(--kop-tinta);
        }
        #navbar.navbar-mobile li + li > a {
            border-top: 1px dashed var(--kop-garis); border-radius: 0;
        }
        #navbar.navbar-mobile a:hover,
        #navbar.navbar-mobile li:hover > a {
            color: var(--kop-jingga); background: var(--kop-kertas-2);
        }

        /* Butir yang sedang dibuka: latar krem, batang jingga di KIRI.

           Di daftar yang tersusun tegak, garis BAWAH terbaca sebagai
           pemisah antar-butir, bukan sebagai penanda. */
        #navbar.navbar-mobile a.active {
            color: var(--kop-jingga);
            background: var(--kop-kertas-2);
            padding-left: 24px;
        }
        #navbar.navbar-mobile a.active::after {
            left: 9px; right: auto; top: 50%; bottom: auto;
            width: 5px; height: 58%; translate: 0 -50%;
            border-radius: 99px;
        }

        #navbar.navbar-mobile .dropdown ul {
            position: static; margin: 4px 8px 8px; padding: 6px;
            background: var(--kop-kertas-2);
            border: 1px dashed var(--kop-garis);
            border-radius: 12px; box-shadow: none;
            max-height: none;
        }
        #navbar.navbar-mobile .dropdown ul a {
            padding: 12px; font-size: 15px; font-weight: 600;
        }
        #navbar.navbar-mobile .dropdown ul li + li > a {
            border-top: 0; border-radius: 9px;
        }

        /* Tombol masuknya selebar panel: di layar sempit ia tujuan utama
           menu ini, dan tombol yang menggantung separuh lebar terbaca
           seperti sisa tata letak layar lebar. */
        #navbar.navbar-mobile .kop-tombol,
        #navbar.navbar-mobile .kop-tombol:focus {
            width: 100%; justify-content: center;
            margin: 10px 0 4px; padding: 14px 18px; font-size: 15.5px;
        }
        #navbar.navbar-mobile .kop-bungkus-tombol {
            display: block; width: 100%; padding: 0 6px 4px;
        }

        @media (prefers-reduced-motion: reduce) {
            #navbar a,
            #navbar .kop-tombol,
            .mobile-nav-toggle { transition: none !important; }
        }
    </style>

    <!-- Facebook Pixel Code -->
    <script>
        ! function(f, b, e, v, n, t, s) {
            if (f.fbq) return;
            n = f.fbq = function() {
                n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n;
            n.push = n;
            n.loaded = !0;
            n.version = '2.0';
            n.queue = [];
            t = b.createElement(e);
            t.async = !0;
            t.src = v;
            s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
        }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '1627568205358582');
        fbq('track', 'PageView');
    </script>
    <noscript>
        <img height="1" width="1" src="https://www.facebook.com/tr?id=1627568205358582&ev=PageView&noscript=1" />
    </noscript>
    <!-- End Facebook Pixel Code -->


    {{-- Tempat halaman menyisipkan isian kepalanya sendiri — font, meta,
         pratarik. Dipakai beranda untuk memuat huruf judulnya.

         Di dalam <head>, bukan di badan halaman: <link rel="stylesheet">
         yang ditulis sesudah isi halaman membuat peramban menggambar
         paragraf dengan huruf cadangan lebih dulu, lalu menggesernya saat
         huruf aslinya datang. --}}
    @stack('kepala')
</head>

<body>

    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top">
        <div class="container-fluid container-xl d-flex align-items-center justify-content-between">

            <!-- Logo -->
            {{-- Lebarnya TIDAK lagi dipatok di atribut style=.

                 Gaya sebaris width: 180px bertabrakan dengan max-height: 40px
                 milik templat: keduanya berlaku, jadi gambarnya digambar
                 180x40 padahal nisbah aslinya 1906x470 — terukur 4,5 lawan
                 4,055, meregang sekitar sebelas persen. Logo yang teregang
                 terbaca "dipaksa" walau tidak ada yang bisa menunjuk apa
                 yang salah.

                 Sekarang tingginya yang dipatok dan lebarnya mengikuti. --}}
            <a href="{{ url('/') }}" class="logo d-flex align-items-center">
                <img src="{{ asset('assets/img/LogoRSC.png') }}" alt="Rumah Scopus Foundation">
            </a>

            <!-- Navbar -->
            <nav id="navbar" class="navbar">
                <ul>
                    <li><a class="nav-link scrollto {{ Request::is('/') ? 'active' : '' }}" href="{{ url('/') }}">Beranda</a></li>
                    <li><a class="nav-link scrollto {{ Request::is('blog') || Request::is('blog/topic/*') ? 'active' : '' }}" href="{{ url('/blog') }}">Blog</a></li>

                    <li class="dropdown">
                        <a href="#"><span>Class</span> <i class="bi bi-chevron-down"></i></a>
                        <ul>
                            <li><a href="https://rumahscopus.com/courses/online-class/" target="_blank">Class Online</a></li>
                            <li><a href="{{ url('/Scopus-Camp') }}">Class Offline</a></li>
                            <li><a href="{{ url('/Scopus-Kafe') }}">Scopus Kafe</a></li>
                            <li><a href="{{ url('/Analisis-Bibliometrik') }}">Analisis Bibliometrik</a></li>
                            <li><a href="{{ url('/Clinik-Scopus') }}">Klinik Scopus</a></li>
                        </ul>
                    </li>

                    <li><a class="nav-link scrollto {{ Request::is('paperisasi/public/data') ? 'active' : '' }}" href="{{ url('/paperisasi/public/data') }}">Cek ID</a></li>
                    <!-- <li><a class="nav-link scrollto {{ Request::is('Cek-Plagiasi') ? 'active' : '' }}" href="{{ url('/Cek-Plagiasi') }}">Cek Plagiasi</a></li> -->
                    <li><a class="nav-link scrollto {{ Request::is('Refrensi-Paper') ? 'active' : '' }}" href="{{ url('/Refrensi-Paper') }}">Referensi Paper</a></li>
                    <li><a class="nav-link scrollto {{ Request::is('contact') ? 'active' : '' }}" href="{{ url('/contact') }}">Kontak</a></li>

                    {{-- Tombol masuk / akun.

                         Gaya sebarisnya DIBUANG dan dipindahkan ke kelas
                         .kop-tombol: style= menang atas aturan CSS mana pun,
                         jadi selama warnanya ditulis di sini, menatanya dari
                         lembar gaya tidak akan pernah berpengaruh — dan itu
                         yang membuat tombol ini tertinggal memakai gradien
                         merah-jingga lama sementara seluruh halaman sudah
                         berpindah ke jingga logo. --}}
                    <li>
                        <div class="kop-bungkus-tombol ms-lg-3 mt-2 mt-lg-0">
                            @auth
                            @php
                            // Ambil dua kata pertama dari full_name
                            $nameParts = explode(' ', trim(Auth::user()->full_name));
                            $shortName = implode(' ', array_slice($nameParts, 0, 2));

                            // Tentukan gambar profil (default jika null)
                            $userImage = Auth::user()->gambar
                            ? \App\Support\FotoProfil::url(Auth::user()->gambar)
                            : asset('assets/img/avatar/avatar-1.png');
                            @endphp

                            <a href="{{ url('/account/dashboard') }}" class="kop-tombol">
                                <img src="{{ $userImage }}" alt="Foto profil">
                                Hi, {{ $shortName }}
                            </a>
                            @else
                            <a href="{{ url('/login') }}" class="kop-tombol">
                                <i class="bi bi-box-arrow-in-right"></i> Login / Register
                            </a>
                            @endauth
                        </div>
                    </li>

                </ul>
                <i class="bi bi-list mobile-nav-toggle"></i>
            </nav>

        </div>
    </header>
    <!-- ======= End Header ======= -->

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
                            <strong>Phone:</strong> <b>Dinar</b> +62 812-2688-3280<br>
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