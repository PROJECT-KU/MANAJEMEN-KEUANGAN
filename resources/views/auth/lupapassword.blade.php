<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Reset Password | MIS</title>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('assets/login/fonts/material-icon/css/material-design-iconic-font.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/login/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/fontawesome/css/all.min.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/img/logo-pwa.png') }}">
    <!-- show and hide password -->
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

        .password-toggle2 {
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

        /* // <!-- END --> */

        /* // <!-- MENAMPILKAN & MENYEMBUNYIKAN GAMBAR --> */
        /* CSS untuk menyembunyikan gambar pada perangkat mobile */
        @media (max-width: 767px) {
            .signin-image {
                display: none;
            }
        }

        /* CSS untuk menampilkan gambar pada desktop atau tablet */
        @media (min-width: 768px) {
            .signin-image {
                display: block;
            }
        }

        /* // <!-- END --> */
    </style>
</head>

<!-- <body style="background: #f3f3f3"> -->

<body>
    <div id="app">
        <div class="main">
            <section class="section">

                <div class="card card-primary">

                    <div class="card-body">
                        <form id="resetPasswordForm" method="POST" action="{{ route('cekemail.reset') }}" class="needs-validation" novalidate="">
                            @csrf

                            <section class="sign-in">
                                <div class="container">
                                    <div class="signin-content">
                                        <div class="signin-image">
                                            <figure><img src="{{ asset('assets/login/images/reset-password.jpg') }}" alt="sing up image" style="width: 1000px;"></figure>
                                        </div>

                                        <div class="signin-form">
                                            <h2 class="form-title">Reset Password</h2>

                                            <div class="form-group">
                                                <label for="email"><i class="fas fa-envelope-open"></i></label>
                                                <input type="email" name="email" id="email" class="form-control" placeholder="Masukkan Email Anda" value="{{ old('email') }}" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>
                                            </div>

                                            <div class="form-group password-group">
                                                <label for="password"><i class="fas fa-unlock-alt"></i></label>
                                                <input type="password" name="password" id="password" placeholder="Masukkan Password Baru" class="form-control pwstrength" data-indicator="pwindicator" name="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Harus berisi setidaknya satu angka dan satu huruf besar dan kecil, dan setidaknya 8 karakter atau lebih" required>
                                                <i class="fas fa-eye password-toggle" id="password-toggle"></i>
                                                <div id="pwindicator" class="pwindicator">
                                                    <div class="bar"></div>
                                                    <div class="label"></div>
                                                </div>
                                            </div>

                                            <div class="form-group password-group">
                                                <label for="password2"><i class="fas fa-unlock-alt"></i></label>
                                                <input type="password" name="password_confirmation" id="password2" placeholder="Masukkan Konfirmasi Password" class="form-control" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Harus berisi setidaknya satu angka dan satu huruf besar dan kecil, dan setidaknya 8 karakter atau lebih" required>
                                                <i class="fas fa-eye password-toggle2" id="password-toggle2"></i>
                                            </div>

                                            <div class="form-group">
                                                <input type="checkbox" name="agree" id="agree" class="agree-term" @if(old('agree')) checked @endif required>
                                                <label for="agree" class="label-agree-term">
                                                    <span class="checkmark"></span>
                                                    Saya setuju dengan syarat dan ketentuan
                                                </label>
                                                @error('agree')
                                                <div class="invalid-feedback" style="display: block">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>


                                            <div class="form-group form-button">
                                                <input type="submit" onclick="validateAndSubmit()" name="signin" id="signin" class="form-submit" value="Reset Sekarang" />
                                            </div>

                                            <div style="display: flex; align-items: center;">
                                                <span>Kembali Login?</span>
                                                <a href="{{ route('login') }}" class="signup-image-link" style="color: #6495ED; text-decoration: none; text-align:left; margin-left: 3px;">Login!</a>
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
    </div>

    <!--================== SWEET ALERT EMAIL TIDAK TERDAFTAR ==================-->
    @if (session('error'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Use SweetAlert to display the error message
        Swal.fire({
            icon: 'error',
            title: 'Tidak Terdaftar',
            text: 'Alamat Email Anda Tidak Terdaftar!',
            confirmButtonText: 'OK'
        });
    </script>
    @endif
    <!--================== END ==================-->

    <!--================== SWEET ALERT PASSWORD & EMAIL ==================-->
    <!-- Include your SweetAlert library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function validateAndSubmit() {
            // Validate email, password, and password confirmation fields
            var emailField = document.getElementById('email');
            var passwordField = document.getElementById('password');
            var passwordConfirmationField = document.getElementById('password2');
            var emailEmptyError = document.getElementById('emailEmptyError');
            var passwordEmptyError = document.getElementById('passwordEmptyError');
            var passwordConfirmationError = document.getElementById('passwordConfirmationError');
            var agreeCheckbox = document.getElementById('agree');

            // Check if email is empty
            if (emailField.value.trim() === '') {
                // Email is empty, display SweetAlert error
                Swal.fire({
                    icon: 'error',
                    title: 'Email Kosong',
                    text: 'Email Wajib Diisi!',
                    confirmButtonText: 'OK'
                });
                // Display the error message in the form
                emailEmptyError.style.display = 'block';
                passwordEmptyError.style.display = 'none'; // Reset password error message
                passwordConfirmationError.style.display = 'none'; // Reset confirmation error message
                return;
            }

            // Check if password is empty
            if (passwordField.value.trim() === '') {
                // Password is empty, display SweetAlert error
                Swal.fire({
                    icon: 'error',
                    title: 'Password Kosong',
                    text: 'Password Wajib Diisi!',
                    confirmButtonText: 'OK'
                });
                // Display the error message in the form
                emailEmptyError.style.display = 'none'; // Reset email error message
                passwordEmptyError.style.display = 'block';
                passwordConfirmationError.style.display = 'none'; // Reset confirmation error message
                return;
            }

            if (passwordConfirmationField.value.trim() === '') {
                // Password confirmation is empty, display SweetAlert error
                Swal.fire({
                    icon: 'error',
                    title: 'Konfirmasi Password Kosong',
                    text: 'Konfirmasi Password Wajib Diisi!',
                    confirmButtonText: 'OK'
                });
                // Display the error message in the form
                passwordEmptyError.style.display = 'none'; // Reset password error message
                passwordConfirmationError.style.display = 'block';
                return;
            }

            // Check if password meets the required pattern
            var passwordPattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/;
            if (!passwordPattern.test(passwordField.value)) {
                // Password does not match the pattern, display SweetAlert error
                Swal.fire({
                    icon: 'error',
                    title: 'Pattern Password Salah',
                    text: 'Password harus berisi setidaknya satu angka dan satu huruf besar dan kecil, dan setidaknya 8 karakter atau lebih.',
                    confirmButtonText: 'OK'
                });
                // Display the error message in the form
                emailEmptyError.style.display = 'none'; // Reset email error message
                passwordEmptyError.style.display = 'block';
                passwordConfirmationError.style.display = 'none'; // Reset confirmation error message
                return;
            }

            // Check if password and confirmation match
            if (passwordField.value !== passwordConfirmationField.value) {
                // Password and confirmation do not match, display SweetAlert error
                Swal.fire({
                    icon: 'error',
                    title: 'Konfirmasi Password Salah',
                    text: 'Konfirmasi Password Anda Tidak Sama!',
                    confirmButtonText: 'OK'
                });
                // Display the error message in the form
                emailEmptyError.style.display = 'none'; // Reset email error message
                passwordEmptyError.style.display = 'none'; // Reset password error message
                passwordConfirmationError.style.display = 'none';
                return;
            }

            if (!agreeCheckbox.checked) {
                // Checkbox is not checked, display SweetAlert error
                Swal.fire({
                    icon: 'error',
                    title: 'Syarat dan Ketentuan Belum Disetujui',
                    text: 'Anda harus menyetujui syarat dan ketentuan!',
                    confirmButtonText: 'OK'
                });
                // Display the error message in the form
                emailEmptyError.style.display = 'none'; // Reset email error message
                passwordEmptyError.style.display = 'none'; // Reset password error message
                passwordConfirmationError.style.display = 'none'; // Reset confirmation error message
                return;
            }

            // Email, password, and confirmation are not empty, password meets the pattern, and password and confirmation match
            // Continue with form submission
            emailEmptyError.style.display = 'none';
            passwordEmptyError.style.display = 'none';
            passwordConfirmationError.style.display = 'none';
            document.getElementById('resetPasswordForm').submit();
        }
    </script>
    <!--================== END ==================-->


    <!--================== SHOW & HIDE PASSWORD ==================-->
    <script>
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('password-toggle');

        passwordToggle.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.classList.remove('fa-eye');
                passwordToggle.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordToggle.classList.remove('fa-eye-slash');
                passwordToggle.classList.add('fa-eye');
            }
        });
        const passwordInput2 = document.getElementById('password2');
        const passwordToggle2 = document.getElementById('password-toggle2');

        passwordToggle2.addEventListener('click', function() {
            if (passwordInput2.type === 'password') {
                passwordInput2.type = 'text';
                passwordToggle2.classList.remove('fa-eye');
                passwordToggle2.classList.add('fa-eye-slash');
            } else {
                passwordInput2.type = 'password';
                passwordToggle2.classList.remove('fa-eye-slash');
                passwordToggle2.classList.add('fa-eye');
            }
        });
    </script>
    <!--================== END ==================-->

    <script>
        // Ambil elemen-elemen yang diperlukan
        const rememberMeCheckbox = document.getElementById('remember-me');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');

        // Cek apakah data tersimpan di localStorage saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const storedUsername = localStorage.getItem('username');
            const storedPassword = localStorage.getItem('password');

            // Jika ada data tersimpan, isi input dan centang kotak "Remember Me"
            if (storedUsername && storedPassword) {
                usernameInput.value = storedUsername;
                passwordInput.value = storedPassword;
                rememberMeCheckbox.checked = true;
            }
        });

        // Simpan data username dan password saat form disubmit jika checkbox "Remember Me" dicentang
        document.getElementById('login-form').addEventListener('submit', function(event) {
            if (rememberMeCheckbox.checked) {
                localStorage.setItem('username', usernameInput.value);
                localStorage.setItem('password', passwordInput.value);
            } else {
                localStorage.removeItem('username');
                localStorage.removeItem('password');
            }
        });
    </script>

    <!--================== GENERAL JS ==================-->
    <script src="{{ asset('assets/login/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/login/js/main.js') }}"></script>


    <script src="{{ asset('assets/modules/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/modules/popper.js') }}"></script>
    <script src="{{ asset('assets/modules/tooltip.js') }}"></script>
    <script src="{{ asset('assets/modules/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/modules/nicescroll/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('assets/modules/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/stisla.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <!--================== END ==================-->
</body>

</html>