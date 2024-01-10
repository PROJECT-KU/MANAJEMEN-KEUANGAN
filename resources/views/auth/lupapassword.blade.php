<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Reset Password | MANAGEMENT</title>
    <link rel="shortcut icon" href="{{ asset('assets/img/logonew1.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap-social/bootstrap-social.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('path/to/sweetalert2.css') }}">
    <script src="{{ asset('path/to/sweetalert2.js') }}"></script>

    <!-- show and hide password -->
    <style>
        .password-group {
            position: relative;
        }

        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1;
            vertical-align: middle;
            display: flex;
            justify-content: center;
        }

        .password-toggle2 {
            cursor: pointer;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1;
            vertical-align: middle;
            display: flex;
            justify-content: center;
        }

        .invalid-feedback {
            cursor: pointer;
            position: absolute;
            left: 20px;
            top: 80%;
            transform: translateY(-50%);
            z-index: 1;
            vertical-align: middle;
            display: flex;
            justify-content: center;
        }
    </style>
    <!-- end -->

    <!-- background -->
    <style>
        * {
            padding: 0;
            margin: 0;
        }

        svg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            display: block;
            background-color: #0e4166;
            background-image: linear-gradient(to bottom, rgba(14, 65, 102, 0.86), #0e4166);
        }
    </style>
    <!-- end -->
</head>


<body>
    <div id="app">
        <section class="section">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-8 offset-lg-2 col-xl-8 offset-xl-2">
                        <div class="login-brand">
                            <img src="{{ asset('assets/img/logoterbaru.png') }}" alt="logo" width="350">
                        </div>

                        <div class="card card-primary">
                            <div class="card-header">
                                <h4>LUPA PASSWORD</h4>
                            </div>
                            <div class="card-body">

                                <form method="POST" action="{{ route('cekemail.reset') }}" class="needs-validation" novalidate="">
                                    @csrf
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input id="email" type="text" class="form-control" name="email" placeholder="Masukkan Alamat Email" tabindex="1" required autofocus maxlength="50" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)">
                                        @error('email')
                                        <div class="invalid-feedback" style="display: block">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group rounded">
                                                <label for="password">Password Baru</label>
                                                <div class="input-group-prepend rounded">
                                                    <input id="password" type="password" name="password" placeholder="Masukan Password Baru" class="form-control pwstrength rounded" data-indicator="pwindicator" name="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Harus berisi setidaknya satu angka dan satu huruf besar dan kecil, dan setidaknya 8 karakter atau lebih" required>
                                                    <i class="fas fa-eye password-toggle" id="password-toggle"></i>
                                                </div>
                                                <div id="pwindicator" class="pwindicator">
                                                    <div class="bar"></div>
                                                    <div class="label"></div>
                                                </div>
                                                @error('password')
                                                <div class="invalid-feedback" style="display: block">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group rounded">
                                                <label for="password2">Konfirmasi Password</label>
                                                <div class="input-group-prepend rounded">
                                                    <input id="password2" type="password" name="password_confirmation" placeholder="Masukan Konfirmasi Password Baru" class="form-control rounded" data-indicator="pwindicator" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Harus berisi setidaknya satu angka dan satu huruf besar dan kecil, dan setidaknya 8 karakter atau lebih" required>
                                                    <i class="fas fa-eye password-toggle2" id="password-toggle2"></i>
                                                </div>
                                            </div>
                                            <div id="pwindicator" class="pwindicator">
                                                <div class="bar"></div>
                                                <div class="label"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="agree" class="custom-control-input" id="agree" @if(old('agree')) checked @endif required>
                                            <label class="custom-control-label" for="agree">Saya setuju dengan syarat dan ketentuan</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">
                                            RESET SEKARANG
                                        </button>
                                    </div>
                                    Sudah punya akun? <br> <a href="{{ route('login') }}">Login Sekarang!</a>
                                </form>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </section>

    <!-- bacground -->
    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="100%" height="100%">
        <defs>
            <linearGradient id="bg">
                <stop offset="0%" style="stop-color:rgba(130, 158, 249, 0.06)"></stop>
                <stop offset="50%" style="stop-color:rgba(76, 190, 255, 0.6)"></stop>
                <stop offset="100%" style="stop-color:rgba(115, 209, 72, 0.2)"></stop>
            </linearGradient>
            <path id="wave" fill="url(#bg)" d="M-363.852,502.589c0,0,236.988-41.997,505.475,0
	s371.981,38.998,575.971,0s293.985-39.278,505.474,5.859s493.475,48.368,716.963-4.995v560.106H-363.852V502.589z" />
        </defs>
        <g>
            <use xlink:href='#wave' opacity=".3">
                <animateTransform attributeName="transform" attributeType="XML" type="translate" dur="10s" calcMode="spline" values="270 230; -334 180; 270 230" keyTimes="0; .5; 1" keySplines="0.42, 0, 0.58, 1.0;0.42, 0, 0.58, 1.0" repeatCount="indefinite" />
            </use>
            <use xlink:href='#wave' opacity=".6">
                <animateTransform attributeName="transform" attributeType="XML" type="translate" dur="8s" calcMode="spline" values="-270 230;243 220;-270 230" keyTimes="0; .6; 1" keySplines="0.42, 0, 0.58, 1.0;0.42, 0, 0.58, 1.0" repeatCount="indefinite" />
            </use>
            <use xlink:href='#wave' opacty=".9">
                <animateTransform attributeName="transform" attributeType="XML" type="translate" dur="6s" calcMode="spline" values="0 230;-140 200;0 230" keyTimes="0; .4; 1" keySplines="0.42, 0, 0.58, 1.0;0.42, 0, 0.58, 1.0" repeatCount="indefinite" />
            </use>
        </g>
    </svg>
    <!-- end -->
    </div>



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

    <!-- popup -->
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
    <!-- end -->

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

    <!-- General JS Scripts -->
    <script src="{{ asset('assets/modules/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/modules/popper.js') }}"></script>
    <script src="{{ asset('assets/modules/tooltip.js') }}"></script>
    <script src="{{ asset('assets/modules/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/modules/nicescroll/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('assets/modules/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/stisla.js') }}"></script>

    <!-- JS Libraies -->

    <!-- Page Specific JS File -->

    <!-- Template JS File -->
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
</body>

</html>