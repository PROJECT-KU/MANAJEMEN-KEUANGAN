<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Recruitment | RUMAH SCOPUS</title>
    <link rel="shortcut icon" href="{{ asset('assets/img/logonew1.png') }}">
    <!-- General CSS Files -->
    <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/fontawesome/css/all.min.css') }}">

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap-social/bootstrap-social.css') }}">

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    <!-- show and hide password -->
    <style>
        .password-group {
            position: relative;
        }

        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 20px;
            top: 65%;
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
            top: 65%;
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

<!-- <body style="background: #f3f3f3"> -->

<body>
    <div id="app">
        <section class="section">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-12 col-sm-10 offset-sm-1 col-md-12 offset-md-4 col-lg-10 offset-lg-1 col-xl-8 offset-xl-2">
                        <div class="login-brand">
                            <img src="{{ asset('assets/img/logoterbaru.png') }}" alt="logo" width="350">
                        </div>

                        <div class="card card-primary">
                            <div class="card-header">
                                <h4>RECRUITMENT</h4>
                            </div>

                            <div class="card-body">
                                <form action="{{ route('register') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="form-group col-6">
                                            <label for="frist_name">Nama Lengkap</label>
                                            <input id="frist_name" type="text" style="text-transform:uppercase;" class="form-control" name="full_name" value="{{ old('full_name') }}" autofocus maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9 ]/i.test(event.key)" required>
                                            @error('full_name')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-6">
                                            <label for="telp">No Telp</label>
                                            <input id="telp" type="text" class="form-control" name="telp" value="{{ old('telp') }}" maxlength="15 minlength=" 8" onkeypress="return event.charCode >= 48 && event.charCode <= 57" oninput="formatPhoneNumber(this)" required>
                                            @error('telp')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-12">
                                            <label for="email">Alamat Email</label>
                                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>
                                            @error('email')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-12">
                                            <label for="pendidikan">Pendidikan Terakhir</label>
                                            <select class="form-control pendidikan" name="pendidikan" id="pendidikan">
                                                <option value="002">BRI</option>
                                            </select>
                                            <input id="pendidikan" type="pendidikan" class="form-control" name="pendidikan" value="{{ old('pendidikan') }}" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>
                                            @error('pendidikan')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-12">
                                            <label for="cv">Berkas CV</label>
                                            <input type="file" name="cv" id="cv" class="form-control" accept="application/pdf" required>
                                            @error('cv')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-12">
                                            <label for="lamaran">Berkas Surat Lamaran</label>
                                            <input type="file" name="lamaran" id="lamaran" class="form-control" accept="application/pdf" required>
                                            @error('lamaran')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-12">
                                            <label for="lainnya">Berkas Lainnya</label>
                                            <input type="file" name="lainnya" id="lainnya" class="form-control" accept="application/pdf" required>
                                            @error('lainnya')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="agree" class="custom-control-input" id="agree" @if(old('agree')) checked @endif required>
                                            <label class="custom-control-label" for="agree">Saya setuju dengan syarat dan ketentuan</label>
                                            @error('agree')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                                            DAFTAR
                                        </button>
                                    </div>
                                    <div class="form-group">
                                        Sudah Punya Akun? <a href="{{ route('login') }}">Login Sekarang!</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="simple-footer">
                            © <strong>Berto Juni</strong> 2019. Hak Cipta Dilindungi.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!--================== BACKGROUND ==================-->
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="100%" height="100%" style="position: fixed; top: 0; left: 0; z-index: -1;">
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
        <!--================== END ==================-->
    </div>

    <!--================== FORMAT NO TELP ==================-->
    <script>
        function formatPhoneNumber(input) {
            // Menghapus semua karakter non-digit
            var phoneNumber = input.value.replace(/\D/g, '');

            // Menentukan panjang nomor telepon
            var phoneNumberLength = phoneNumber.length;

            // Memeriksa panjang nomor telepon dan menerapkan format yang sesuai
            if (phoneNumberLength === 11) {
                phoneNumber = phoneNumber.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
            } else if (phoneNumberLength === 12) {
                phoneNumber = phoneNumber.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
            } else if (phoneNumberLength === 13) {
                phoneNumber = phoneNumber.replace(/(\d{5})(\d{4})(\d{4})/, '$1-$2-$3');
            }

            // Mengatur nilai input dengan nomor telepon yang diformat
            input.value = phoneNumber;
        }
    </script>
    <!--================== END ==================-->


    <!--================== CHANGE JENIS AKUN ==================-->
    <script>
        // Get the elements
        const jenisDropdown = document.getElementById('jenis');
        const namaPerusahaanContainer = document.getElementById('namaPerusahaanContainer');
        const levelDropdown = document.getElementById('levelDropdown');
        const telpContainer = document.getElementById('telpContainer');
        const levelSelect = document.querySelector('[name="level"]');

        // Function to handle the visibility of "Nama Perusahaan", "Level", and "No Telp" fields
        function handleVisibility() {
            const selectedValue = jenisDropdown.value;
            if (selectedValue === 'bisnis' || selectedValue === 'penyewaan' || selectedValue === 'kasir') {
                namaPerusahaanContainer.style.display = 'block';
                telpContainer.style.display = 'block';
                levelDropdown.style.display = 'block';
                // If "bisnis" is selected, set the width of "Level" to col-md-6
                levelDropdown.classList.remove('col-md-4');
                levelDropdown.classList.add('col-md-6');
            } else if (selectedValue === 'perorangan') {
                namaPerusahaanContainer.style.display = 'none';
                telpContainer.style.display = 'block';
                levelDropdown.style.display = 'block';
                // If "perorangan" is selected, set the width of "Level" to col-md-4
                telpContainer.classList.remove('col-6');
                telpContainer.classList.add('col-6');
            } else {
                // In case "Silahkan Pilih" or an unexpected value is selected, hide all extra fields
                namaPerusahaanContainer.style.display = 'none';
                telpContainer.style.display = 'block';
                levelDropdown.style.display = 'none';
            }

            // When "Perorangan" is selected, show only "Users" in the level dropdown
            if (selectedValue === 'perorangan') {
                levelSelect.innerHTML = '<option value="users">Users</option>';
            } else if (selectedValue === 'bisnis') {
                levelSelect.innerHTML = '<option value="staff">Staff</option> <option value="karyawan">Karyawan</option> <option value="trainer">Trainer</option>';
            }
        }

        // Attach the event listener to the dropdown
        jenisDropdown.addEventListener('change', handleVisibility);

        // Call the function once on page load to initialize the visibility
        handleVisibility();
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

    <!--================== GENERAL JS ==================-->
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