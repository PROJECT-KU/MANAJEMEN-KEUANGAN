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

    <style>
        /* Pusatkan seluruh isi card */
        .signin-content,
        .signin-form,
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 100%;
            overflow-x: hidden;
        }

        /* Pastikan form input tetap rapi dan tidak terlalu lebar */
        .form-group {
            width: 100%;
            max-width: 300px;
            margin: 15px;
        }

        .form-control {
            width: 100%;
        }

        /* Untuk baris "Belum Punya Akun?" tetap di tengah */
        .create-account {
            text-align: center;
        }
    </style>

</head>


<body>
    <div id="app">
        <div class="main">
            <section class="section">

                <div class="card card-primary" style="width: fit-content; margin: 0 auto;">
                    <div class="card-body">

                        <form method="POST" action="{{ route('login') }}" id="login-form" class="needs-validation" novalidate="">
                            @csrf
                            <section class="sign-in">
                                <div class="container">
                                    <div class="signin-content">

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
                                                <label for="remember-me" class="label-agree-term" style="margin-right: 10px;" req><span><span></span></span>Ingatkan Saya</label>
                                                <span style="color: darkgrey;">|</span>
                                                <a href="{{ route('formemail.reset') }}" class="reset-password" style="color: #6495ED; text-decoration: none; text-align:rignt; margin-left:10px;">Reset Password!</a>
                                            </div>

                                            <div class="form-group form-button">
                                                <input type="submit" name="signin" style="width: 100%; font-size:12px;" id="signin" class="form-submit" value="Log in" />
                                            </div>


                                            <div style="display: flex; align-items: center;">
                                                <span style="text-align: left; margin-right:10px;" class="create-account">Belum Punya Akun?</span>
                                                <a href="{{ route('register') }}" class="signup-image-link create-account" style="color: #6495ED; text-decoration: none; text-align:left; margin-left: 10px;">Buat Sekarang!</a>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </section>

                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>
</body>

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

</html>