<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Daftar Akun | MIS</title>
    <link rel="shortcut icon" href="{{ asset('assets/img/logo-pwa.png') }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('assets/modules/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/login/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('path/to/sweetalert2.css') }}">
    <!-- show and hide password -->
    <style>
        /* // <!-- SHOW AND HIDE PASSWORD --> */
        .password-group {
            position: relative;
            display: flex;
            align-items: center;
            /* Untuk membuat ikon mata berada di tengah-tengah vertikal */
        }

        .password-toggle {
            cursor: pointer;
            margin-left: auto;
            z-index: 1;
            vertical-align: middle;
            display: flex;
            justify-content: center;
            position: absolute;
            /* Menyesuaikan posisi ikon */
            right: 10px;
            /* Menyesuaikan posisi ikon */
            top: 50%;
            /* Menyesuaikan posisi ikon */
            transform: translateY(-50%);
            /* Menyesuaikan posisi ikon */
        }

        .password-group2 {
            position: relative;
            display: flex;
            align-items: center;
            /* Untuk membuat ikon mata berada di tengah-tengah vertikal */
        }

        .password-toggle2 {
            cursor: pointer;
            z-index: 1;
            vertical-align: middle;
            display: flex;
            justify-content: center;
            position: absolute;
            /* Menyesuaikan posisi ikon */
            right: 20px;
            /* Menyesuaikan posisi ikon */
            top: 35%;
            /* Menyesuaikan posisi ikon */
            transform: translateY(-50%);
            /* Menyesuaikan posisi ikon */
        }

        /* // <!-- END --> */
    </style>

    <style>
        /* Pusatkan seluruh isi card */
        .signup-content,
        .signup-form,
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            overflow-x: hidden;
            box-sizing: border-box;
        }

        /* 🔹 Gaya input Bootstrap-style dengan garis bawah */
        .form-group {
            position: relative;
            width: 100%;
            margin-bottom: 25px;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            display: block;
            margin-bottom: 6px;
        }

        .form-control {
            border: none;
            border-bottom: 2px solid #ccc;
            border-radius: 0;
            width: 100%;
            padding: 10px 6px;
            font-size: 15px;
            background-color: transparent;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            box-shadow: none;
            border-bottom-color: #007bff;
        }

        /* 🔹 Placeholder sedikit transparan */
        .form-control::placeholder {
            color: #aaa;
            font-style: italic;
        }

        /* 🔹 Card otomatis menyesuaikan layar */
        @media (max-width: 992px) {

            .signup-content,
            .signup-form,
            .container {
                width: 90%;
                padding: 30px 15px;
            }
        }

        /* 🔹 Tampilan di layar kecil */
        @media (max-width: 768px) {

            .signup-content,
            .signup-form,
            .container {
                width: 100%;
                padding: 20px 10px;
                flex-direction: column;
            }

            .row {
                flex-direction: column !important;
                gap: 20px;
                /* ✅ Jarak antar input dalam satu row */
                width: 100%;
            }

            .form-group {
                max-width: 100%;
                width: 100%;
                display: block;
                margin-bottom: 0;
            }

            .form-control {
                font-size: 14px;
                width: 100%;
            }

            /* ✅ Tambahkan jarak antar kelompok input */
            .row+.row {
                margin-top: 25px;
                /* 🔹 Jarak antar baris (antara username→telp, email→password) */
            }
        }

        /* 🔹 Tambahan untuk layar sangat kecil (HP mini) */
        @media (max-width: 480px) {

            .signup-content,
            .signup-form,
            .container {
                padding: 15px 10px;
                flex-direction: column;
            }

            .row {
                flex-direction: column !important;
                gap: 18px;
                width: 100%;
            }

            .form-group {
                width: 100%;
                display: block;
                flex: 0 0 100%;
                margin-bottom: 0;
            }

            .form-control {
                font-size: 13px;
                padding: 8px 4px;
                width: 100%;
            }

            .form-group label {
                font-size: 13px;
            }

            .form-row {
                display: flex;
                flex-direction: column !important;
                width: 100%;
                gap: 18px;
            }

            .form-row .form-group {
                width: 100% !important;
            }

            /* ✅ Tambahan agar jarak antar baris tetap seragam di HP mini */
            .row+.row {
                margin-top: 20px;
                /* 🔹 Sedikit lebih kecil untuk layar kecil */
            }
        }
    </style>
</head>

<body>
    <div id="app">
        <div class="main">
            <section class="section">


                <div class="card-body">
                    <form action="{{ route('register') }}" method="POST">
                        @csrf
                        <section class="signup">
                            <div class="container">
                                <div class="signup-content">
                                    <div class="signup-form">
                                        <h2 class="form-title"><strong>Sign up</strong></h2>

                                        <div class="row">
                                            <div class="form-group col-6 ">
                                                <label for="username"><i class="zmdi zmdi-account material-icons-name"></i></label>
                                                <input type="text" name="full_name" id="first_name" class="form-control" placeholder="Nama Lengkap" value="{{ old('full_name') }}" autofocus maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9 ]/i.test(event.key)" required>
                                                @error('full_name')
                                                <div class="invalid-feedback" style="display: block">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            <div class="form-group col-6">
                                                <input type="text" name="username" id="last_name" class="form-control" placeholder="Username" value="{{ old('username') }}" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9 ]/i.test(event.key)" required>
                                                @error('username')
                                                <div class="invalid-feedback" style="display: block">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="form-group col-6" id="telpContainer">
                                                <input type="text" name="telp" id="telp" class="form-control" placeholder="No Telp" value="{{ old('telp') }}" maxlength="20 minlength=" 8" onkeypress="return event.charCode >= 48 && event.charCode <= 57" oninput="formatPhoneNumber(this)" required>
                                                @error('telp')
                                                <div class="invalid-feedback" style="display: block">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                            <div class="form-group col-6">
                                                <input type="email" name="email" id="email" class="form-control" placeholder="Email" value="{{ old('email') }}" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>
                                                @error('email')
                                                <div class="invalid-feedback" style="display: block">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="form-group col-6">
                                                <input type="password" name="password" id="password" placeholder="Password" class="form-control" pwstrength" data-indicator="pwindicator" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Harus berisi setidaknya satu angka dan satu huruf besar dan kecil, dan setidaknya 8 karakter atau lebih" required>
                                                <i class="fas fa-eye password-toggle" id="password-toggle" style="margin-right: 10px;"></i>
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
                                                <label for="password2"><i class="zmdi zmdi-lock-outline"></i></label>
                                                <input type="password" name="password_confirmation" id="password2" class="form-control" placeholder="Konfirmasi Password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Harus berisi setidaknya satu angka dan satu huruf besar dan kecil, dan setidaknya 8 karakter atau lebih" required>
                                                <i class="fas fa-eye password-toggle2" id="password-toggle2"></i>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox mt-5">
                                                <input type="checkbox" name="agree" class="custom-control-input" id="agree" @if(old('agree')) checked @endif required>
                                                <label class="custom-control-label" for="agree">Saya setuju dengan syarat dan ketentuan</label>
                                                @error('agree')
                                                <div class="invalid-feedback" style="display: block">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="form-group form-button col-md-6">
                                                <input type="submit" name="signup" id="signup" class="form-submit" value="Register">
                                            </div>
                                            <div class="form-group col-md-6 mt-4">
                                                <p style="text-align: center; margin-bottom: -2px;">Sudah Punya Akun?</p>
                                                <a href="{{ route('login') }}" style="color: #6495ED; text-decoration: none;" class="signup-image-link">Login Sekarang!</a>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </section>


                    </form>
                </div>
            </section>
        </div>
    </div>
</body>

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
            levelSelect.innerHTML = '<option value="">Silahkan Pilih</option> <option value="staff">Staff</option> <option value="karyawan">Karyawan</option> <option value="trainer">Trainer</option>';
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


</html>