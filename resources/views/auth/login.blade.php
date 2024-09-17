<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Masuk Akun | MIS</title>
    <link rel="shortcut icon" href="{{ asset('assets/img/logo-pwa.png') }}">

    <!--================== LOGIN NEW ==================-->
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('assets/login/fonts/material-icon/css/material-design-iconic-font.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/login/css/style.css') }}">


    <link rel="shortcut icon" href="{{ asset('assets/img/logo-pwa.png') }}">
    <!--================== END ==================-->

    <!--================== IKLAN ==================-->
    <!-- google ads -->
    <meta name="google-adsense-account" content="ca-pub-4416930989633394">
    <!-- end -->
    <!--================== END ==================-->

    <!--================== PWA ==================-->
    <meta name="theme-color" content="#6777ef" />
    <link rel="apple-touch-icon" href="{{ asset('logo-pwa.png') }}">
    <link rel="manifest" href="{{ asset('/manifest.json') }}">
    <!--================== END ==================-->
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
    <!-- -->
    <style>
        /* // <!-- SHOW AND HIDE PASSWORD --> */
        .password-group {
            position: relative;
            display: flex;
            /* Tambahkan ini */
            align-items: center;
            /* Tambahkan ini */
        }

        .password-toggle {
            cursor: pointer;
            margin-left: auto;
            /* Tambahkan ini */
            z-index: 1;
            vertical-align: middle;
            display: flex;
            justify-content: center;
        }

        /* // <!-- END --> */

        /* // <!-- GAYA SAAT INPUT DALAM KEADAAN FOKUS --> */
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        .form-control {
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        /* // <!-- END --> */

        /* // <!-- IMAGE --> */
        @media (max-width: 768px) {
            .signin-content {
                flex-direction: column-reverse;
                /* Membalikkan urutan konten saat tampilan mobile */
                text-align: center;
                /* Pusatkan teks */
            }

            .signin-form {
                margin-top: 20px;
                /* Berikan sedikit margin atas */
            }

            .signin-image {
                margin-bottom: 20px;
                /* Berikan sedikit margin bawah */
            }
        }

        /* // <!-- END -->  */

        /* // <!-- MENAMPILKAN & MENYEMBUNYIKAN GAMBAR --> */
        /* CSS untuk menyembunyikan gambar pada perangkat mobile */
        @media (max-width: 767px) {
            .signin-image {
                display: none;
            }

            .create-account {
                display: none;
            }

            .reset-password {
                margin-left: 1em;
            }
        }

        /* CSS untuk menampilkan gambar pada desktop atau tablet */
        @media (min-width: 768px) {
            .signin-image {
                display: block;
            }

            .create-account {
                display: block;
            }

            .reset-password {
                margin-left: 5em;
            }
        }

        /* // <!-- END --> */

        /* // <!-- MEREFRES PWA DI HP --> */
        .loader {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 8px solid #f3f3f3;
            border-top: 8px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* // <!-- END --> */
    </style>
</head>


<body>
    <div id="app">
        <div class="main">
            <section class="section">

                <div class="login-brand">
                    <!--<img src="{{ asset('assets/img/newlogo.png') }}" alt="logo" width="100" class="shadow-light rounded-circle">-->
                    <!-- <img src="{{ asset('assets/img/logoterbaru.png') }}" alt="logo" width="350"> -->
                    <!-- <p>TMIS <br> Rumah Scopus <br> total management integrated system</p> -->
                </div>

                <div class="card card-primary">
                    <div class="card-body">

                        <form method="POST" action="{{ route('login') }}" id="login-form" class="needs-validation" novalidate="">
                            @csrf
                            <section class="sign-in">
                                <div class="container">
                                    <div class="signin-content">
                                        <div class="signin-image">
                                            <figure><img src="{{ asset('assets/login/images/welcome.jpg') }}" style="height: 450px; width: 700px;" alt="sing up image"></figure>
                                        </div>

                                        <div class="signin-form">
                                            <h2 class="form-title">Sign In</h2>

                                            <div class="form-group">
                                                <label for="username"><i class="zmdi zmdi-account material-icons-name"></i></label>
                                                <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan Username Anda" tabindex="1" required autofocus maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9 ]/i.test(event.key)">
                                            </div>

                                            <div class="form-group password-group">
                                                <label for="password"><i class="zmdi zmdi-lock"></i></label>
                                                <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan Password Anda" tabindex="2" required>
                                                <i class="zmdi zmdi-eye password-toggle" id="password-toggle"></i>
                                            </div>

                                            <div class="form-group">
                                                <input type="checkbox" name="remember-me" id="remember-me" class="agree-term" />
                                                <label for="remember-me" class="label-agree-term" req><span><span></span></span>Ingatkan Saya</label>
                                                <a href="{{ route('formemail.reset') }}" class="reset-password" style="color: #6495ED; text-decoration: none; text-align:rignt;">Reset Password!</a>
                                            </div>

                                            <div class="form-group form-button">
                                                <input type="submit" name="signin" style="width: 100%;" id="signin" class="form-submit" value="Log in" />
                                            </div>

                                            <!-- <div class="form-group form-button" style="margin-top: -10px;">
                                                <a href="{{ route('account.page-maintenance.blank') }}"><input type="button" id="signin" class="form-submit" value="Log in"></a>
                                            </div> -->

                                            <div style="display: flex; align-items: center;">
                                                <span style="text-align: left;" class="create-account">Belum Punya Akun?</span>
                                                <a href="{{ route('register') }}" class="signup-image-link create-account" style="color: #6495ED; text-decoration: none; text-align:left; margin-left: 3px;">Buat Sekarang!</a>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </section>

                        </form>
                    </div>
                </div>

        </div>
    </div>
    </div>
    </section>

    <!--================== MENYIMPAN DATA USERNAME & PASSWORD ==================-->
    <script>
        // Fungsi untuk memeriksa apakah terdapat informasi login yang tersimpan
        function checkSavedLogin() {
            var savedUsername = localStorage.getItem('savedUsername');
            var savedPassword = localStorage.getItem('savedPassword');

            if (savedUsername && savedPassword) {
                document.getElementById('username').value = savedUsername;
                document.getElementById('password').value = savedPassword;
                document.getElementById('remember-me').checked = true;
            }
        }

        // Fungsi untuk menyimpan informasi login saat tombol login ditekan
        document.getElementById('login-form').addEventListener('submit', function(event) {
            if (document.getElementById('remember-me').checked) {
                var username = document.getElementById('username').value;
                var password = document.getElementById('password').value;
                localStorage.setItem('savedUsername', username);
                localStorage.setItem('savedPassword', password);
            } else {
                localStorage.removeItem('savedUsername');
                localStorage.removeItem('savedPassword');
            }
        });

        // Panggil fungsi untuk memeriksa informasi login yang tersimpan saat halaman dimuat
        window.addEventListener('DOMContentLoaded', checkSavedLogin);
    </script>

    <!--================== END ==================-->

    <!--================== MEREFRESH PWA DI HP ==================-->
    <!-- <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(registration => {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                }).catch(err => {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }

        let isRefreshing = false;

        // Fungsi untuk menampilkan loader
        function showLoader() {
            // Tambahkan elemen loader ke dalam body
            var loader = document.createElement('div');
            loader.className = 'loader';
            document.body.appendChild(loader);
        }

        // Fungsi untuk menyembunyikan loader
        function hideLoader() {
            // Hapus elemen loader dari body jika ada
            var loader = document.querySelector('.loader');
            if (loader) {
                loader.parentNode.removeChild(loader);
            }
        }

        // Fungsi untuk menangani refresh saat menggeser ke bawah
        function handlePullToRefresh() {
            // Cek apakah scroll berada di paling atas dan tidak sedang dalam proses refresh
            if (window.scrollY === 0 && !isRefreshing) {
                isRefreshing = true;
                // Tampilkan loader
                showLoader();

                // Lakukan refresh halaman setelah beberapa saat
                setTimeout(() => {
                    location.reload();
                    // Setelah proses refresh selesai, sembunyikan loader
                    hideLoader();
                    // Set isRefreshing ke false untuk memungkinkan refresh kembali
                    isRefreshing = false;
                }, 1000); // Mengatur delay refresh selama 1 detik (1000 milidetik)
            }
        }

        // Tambahkan event listener untuk mendeteksi gerakan menggeser ke bawah
        window.addEventListener('scroll', handlePullToRefresh, {
            passive: true
        });
    </script> -->
    <!--================== END ==================-->

    <!--================== PWA ==================-->
    <!-- <script src="{{ asset('/sw.js') }}"></script>
    <script>
        if ("serviceWorker" in navigator) {
            // Register a service worker hosted at the root of the
            // site using the default scope.
            navigator.serviceWorker.register("/sw.js").then(
                (registration) => {
                    console.log("Service worker registration succeeded:", registration);
                },
                (error) => {
                    console.error(`Service worker registration failed: ${error}`);
                },
            );
        } else {
            console.error("Service workers are not supported.");
        }
    </script> -->
    <!--================== END ==================-->

    <!--================== IKLAN ==================-->
    <!-- google ads -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4416930989633394" crossorigin="anonymous"></script>
    <!-- end -->

    <!-- adnow ads -->
    <div id="SC_TBlock_883745"></div>
    <div id="SC_TBlock_883745" class="SC_TBlock"></div>
    <!-- end -->
    <!--================== END ==================-->

    </div>

    <!--================== IKLAN ==================-->

    <!-- google ads -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4416930989633394" crossorigin="anonymous"></script>
    <!-- end -->

    <!-- adnow ads -->
    <script type="text/javascript">
        (sc_adv_out = window.sc_adv_out || []).push({
            id: 883745,
            domain: "n.ads1-adnow.com",
        });
    </script>
    <script type="text/javascript" src="//st-n.ads1-adnow.com/js/a.js" async></script>
    <script type="text/javascript">
        (sc_adv_out = window.sc_adv_out || []).push({
            id: "883745",
            domain: "n.nnowa.com",
            no_div: false
        });
    </script>
    <script async type="text/javascript" src="//st-n.nnowa.com/js/a.js"></script>
    <!-- end -->
    <!--================== END ==================-->

    <!-- pop up success logout -->
    @if (session('success'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil Log Out',
            text: 'Anda telah berhasil keluar.',
            confirmButtonText: 'OK'
        });
    </script>
    @endif
    <!-- end -->

    @if (session('reset'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil Ganti Password',
            text: 'Anda telah berhasil Mengganti Password.',
            confirmButtonText: 'OK'
        });
    </script>
    @endif
    <!-- end -->

    <!-- popup -->
    @error('username')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Use SweetAlert to display the error message
        Swal.fire({
            icon: 'error',
            title: 'Login Gagal',
            text: 'Username atau Password Anda Salah!',
            confirmButtonText: 'OK'
        });
    </script>
    @enderror
    <!-- end -->

    <!-- show and hide password -->
    <script>
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('password-toggle');

        passwordToggle.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.classList.remove('zmdi-eye');
                passwordToggle.classList.add('zmdi-eye-off');
            } else {
                passwordInput.type = 'password';
                passwordToggle.classList.remove('zmdi-eye-off');
                passwordToggle.classList.add('zmdi-eye');
            }
        });
    </script>
    <!-- end -->

    <!--================== MENYIMPAN DATA LOGIN ==================-->
    <script>
        // Ambil elemen-elemen yang diperlukan
        const rememberMeCheckbox = document.getElementById('remember-me');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');

        // Fungsi untuk menyimpan data login ke local storage jika checkbox "Ingatkan Saya" dicentang
        function saveLoginData() {
            if (rememberMeCheckbox.checked) {
                localStorage.setItem('username', usernameInput.value);
                localStorage.setItem('password', passwordInput.value);
            } else {
                localStorage.removeItem('username');
                localStorage.removeItem('password');
            }
        }

        // Cek apakah aplikasi berjalan sebagai PWA di perangkat seluler
        window.addEventListener('beforeinstallprompt', (event) => {
            // Pastikan bahwa pengguna belum menginstal PWA
            if (!window.matchMedia('(display-mode: standalone)').matches && !window.navigator.standalone) {
                // Tambahkan event listener untuk menyimpan data login saat PWA diinstal
                event.prompt();
                event.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        // PWA diinstal, simpan data login jika checkbox "Ingatkan Saya" dicentang
                        saveLoginData();
                    }
                });
            }
        });

        // Saat PWA dijalankan, cek apakah ada data login yang tersimpan
        document.addEventListener('DOMContentLoaded', function() {
            // Jika ada data tersimpan, isi input dan centang kotak "Ingatkan Saya"
            const storedUsername = localStorage.getItem('username');
            const storedPassword = localStorage.getItem('password');
            if (storedUsername && storedPassword) {
                usernameInput.value = storedUsername;
                passwordInput.value = storedPassword;
                rememberMeCheckbox.checked = true;
            }
        });

        // Tambahkan event listener untuk menyimpan data login saat formulir disubmit
        document.getElementById('login-form').addEventListener('submit', function(event) {
            saveLoginData();
        });
    </script>
    <!--================== END ==================-->

    <script src="{{ asset('assets/login/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/login/js/main.js') }}"></script>

</body>

</html>