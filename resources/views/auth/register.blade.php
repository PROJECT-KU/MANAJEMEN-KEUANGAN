<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Daftar Akun &mdash; UANGKU</title>
    <link rel="shortcut icon" href="{{ asset('assets/img/logo.png') }}">
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
</head>

<body style="background: #f3f3f3">
    <div id="app">
        <section class="section">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-8 offset-lg-2 col-xl-8 offset-xl-2">
                        <div class="login-brand">
                            <img src="{{ asset('assets/img/jewelry.svg') }}" alt="logo" width="100" class="shadow-light rounded-circle">
                        </div>

                        <div class="card card-primary">
                            <div class="card-header">
                                <h4>DAFTAR AKUN</h4>
                            </div>

                            <div class="card-body">
                                <form action="{{ route('register') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="form-group col-6">
                                            <label for="frist_name">Nama Lengkap</label>
                                            <input id="frist_name" type="text" class="form-control" name="full_name" value="{{ old('full_name') }}" autofocus maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9 ]/i.test(event.key)" required>
                                            @error('full_name')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-6">
                                            <label for="last_name">Username</label>
                                            <input id="last_name" type="text" class="form-control" name="username" value="{{ old('username') }}" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9 ]/i.test(event.key)" required>
                                            @error('username')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Jenis Akun</label>
                                                <select class="form-control" name="jenis" id="jenis" required>
                                                    <option value="">Silahkan Pilih</option>
                                                    <option value="bisnis">Bisnis</option>
                                                    <option value="perorangan">Perorangan</option>
                                                </select>

                                                @error('jenis')
                                                <div class="invalid-feedback" style="display: block">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6" id="levelDropdown" style="display: none;">
                                            <div class="form-group">
                                                <label>Level</label>
                                                <select class="form-control" name="level">
                                                    <option value="">Silahkan Pilih</option>
                                                    <option value="staff">Staff</option>
                                                </select>

                                                @error('level')
                                                <div class="invalid-feedback" style="display: block">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6" id="namaPerusahaanContainer" style="display: none;">
                                            <div class="form-group">
                                                <label>Nama Perusahaan</label>
                                                <input type="text" name="company" class="form-control" maxlength="30" minlength="5" onkeypress="return /[A-Z ]/i.test(event.key)" style="text-transform:uppercase">
                                                @error('company')
                                                <div class="invalid-feedback" style="display: block">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group col-md-6" id="telpContainer" style="display: block;">
                                            <label for="telp">No Telp</label>
                                            <input id="telp" type="text" class="form-control" name="telp" value="{{ old('telp') }}" maxlength="14" minlength="8" onkeypress="return event.charCode >= 48 && event.charCode <=57">
                                            @error('telp')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>


                                    </div>


                                    <div class="form-group col-12">
                                        <label for="email">Alamat Email</label>
                                        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>
                                        @error('email')
                                        <div class="invalid-feedback" style="display: block">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    <div class="row" style="margin-top: 30px">
                                        <div class="form-group col-6">
                                            <label for="password" class="d-block">Password</label>
                                            <input id="password" type="password" class="form-control pwstrength" data-indicator="pwindicator" name="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Harus berisi setidaknya satu angka dan satu huruf besar dan kecil, dan setidaknya 8 karakter atau lebih" required>
                                            <i class="fas fa-eye password-toggle" id="password-toggle"></i>
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
                                        <div class="form-group col-6">
                                            <label for="password2" class="d-block">Konfirmasi Password</label>
                                            <input id="password2" type="password" class="form-control" name="password_confirmation" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Harus berisi setidaknya satu angka dan satu huruf besar dan kecil, dan setidaknya 8 karakter atau lebih" required>
                                            <i class="fas fa-eye password-toggle2" id="password-toggle2"></i>
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
    </div>


    <!-- ... Your HTML and CSS ... -->

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
            if (selectedValue === 'bisnis') {
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
                levelSelect.innerHTML = '<option value="staff">Staff</option>';
            }
        }

        // Attach the event listener to the dropdown
        jenisDropdown.addEventListener('change', handleVisibility);

        // Call the function once on page load to initialize the visibility
        handleVisibility();
    </script>
    <!-- ... Your remaining HTML ... -->



    <!-- show and hide password -->
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
    <!-- end -->
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