<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Lembar Kerja | RUMAH SCOPUS</title>
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

    <style>
        @media (max-width: 767px) {
            .form-group {
                margin-bottom: 15px;
            }
        }

        .label-font {
            font-size: 15px;
        }
    </style>
</head>

<!-- <body style="background: #f3f3f3"> -->

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
                            <div class="text-center mt-5">
                                <h4>LEMBAR KERJA SCOPUS CAMP</h4>
                            </div>

                            <div class="card-body">
                                <form action="{{ route('account.peserta.store') }}" method="POST" enctype="multipart/form-data" onsubmit="return validateform()">
                                    @csrf

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="email" style="font-size: 15px;">Alamat Email</label>
                                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" maxlength="50" minlength="5" onkeypress="return /[a-zA-Z0-9@.]/i.test(event.key)" required>
                                            @error('email')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="nama" style="font-size: 15px;">Nama Lengkap</label>
                                            <input id="nama" type="text" style="text-transform:uppercase;" class="form-control" name="nama" value="{{ old('nama') }}" minlength="5" onkeypress="return /[a-zA-Z0-9., ]/i.test(event.key)" required>
                                            @error('nama')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="afiliasi" style="font-size: 15px;">Affiliasi</label>
                                            <input id="afiliasi" type="text" style="text-transform:uppercase;" class="form-control" name="afiliasi" value="{{ old('afiliasi') }}" minlength="5" onkeypress="return /[a-zA-Z0-9 ]/i.test(event.key)" required>
                                            @error('afiliasi')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="judul" style="font-size: 15px;">Judul Manuscript</label>
                                            <input id="judul" type="judul" class="form-control" name="judul" value="{{ old('judul') }}" required>
                                            @error('judul')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="jurnal" style="font-size: 15px;">Jurnal Tujuan</label>
                                            <input id="jurnal" type="jurnal" class="form-control" name="jurnal" value="{{ old('jurnal') }}" required>
                                            @error('jurnal')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="camp" style="font-size: 15px;">Nomor Batch Scopus Camp yang di ikuti</label>
                                            <input id="camp" type="camp" class="form-control" name="camp" value="{{ old('camp') }}" required>
                                            @error('camp')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="refrensi" style="font-size: 15px;">Apakah Bapak/Ibu sudah mendapatkan 50 referensi yang relevan untuk manuskripnya?</label>
                                            <div class="form-check">
                                                <input type="radio" id="sudah" name="refrensi" class="form-check-input" value="Sudah" required>
                                                <label class="form-check-label" for="sudah" style="font-size: 15px;">Sudah</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="belum" name="refrensi" class="form-check-input" value="Belum" required>
                                                <label class="form-check-label" for="belum" style="font-size: 15px;">Belum</label>
                                            </div>
                                            @error('refrensi')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="digital_writing" style="font-size: 15px;">Apakah Bapak/Ibu sudah menguasai Digital Writing (Paraphrase, Translate, dan Citation) ?</label>
                                            <div class="form-check">
                                                <input type="radio" id="sudah" name="digital_writing" class="form-check-input" value="Sudah" required>
                                                <label class="form-check-label" for="sudah" style="font-size: 15px;">Sudah</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="belum" name="digital_writing" class="form-check-input" value="Belum" required>
                                                <label class="form-check-label" for="belum" style="font-size: 15px;">Belum</label>
                                            </div>
                                            @error('digital_writing')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="mendeley" style="font-size: 15px;">Apakah Bapak/Ibu sudah menguasai penggunaan Mendeley Dekstop ?</label>
                                            <div class="form-check">
                                                <input type="radio" id="sudah" name="mendeley" class="form-check-input" value="Sudah" required>
                                                <label class="form-check-label" for="sudah" style="font-size: 15px;">Sudah</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="belum" name="mendeley" class="form-check-input" value="Belum" required>
                                                <label class="form-check-label" for="belum" style="font-size: 15px;">Belum</label>
                                            </div>
                                            @error('mendeley')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="persentase_penyelesaian" style="font-size: 15px;">Berapakah persetase penyelesaian manuscript Bapak/Ibu ?</label>
                                            <div class="form-check">
                                                <input type="radio" id="0-24%" name="persentase_penyelesaian" class="form-check-input" value="0-24%" required>
                                                <label class="form-check-label" for="0-24%" style="font-size: 15px;">0-24%</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="25-49%" name="persentase_penyelesaian" class="form-check-input" value="25-49%" required>
                                                <label class="form-check-label" for="25-49%" style="font-size: 15px;">25-49%</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="50-74%" name="persentase_penyelesaian" class="form-check-input" value="50-74%" required>
                                                <label class="form-check-label" for="50-74%" style="font-size: 15px;">50-74%</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="75-100%" name="persentase_penyelesaian" class="form-check-input" value="75-100%" required>
                                                <label class="form-check-label" for="75-100%" style="font-size: 15px;">75-100%</label>
                                            </div>
                                            @error('persentase_penyelesaian')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="submit" style="font-size: 15px;">Apakah Bapak/Ibu sudah Submit ?</label>
                                            <div class="form-check">
                                                <input type="radio" id="sudah" name="submit" class="form-check-input" value="Sudah" required onclick="toggleTargetInput(this)">
                                                <label class="form-check-label" for="sudah" style="font-size: 15px;">Sudah</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="belum" name="submit" class="form-check-input" value="Belum" required onclick="toggleTargetInput(this)">
                                                <label class="form-check-label" for="belum" style="font-size: 15px;">Belum</label>
                                            </div>
                                            @error('submit')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row" id="targetRow" style="display: none;">
                                        <div class="form-group col-md-12">
                                            <label for="target" style="font-size: 15px;">Target Submit</label>
                                            <input id="target" type="date" class="form-control" name="target" value="{{ old('target') }}" width="100%" required>
                                            @error('target')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                                            SIMPAN & LANJUTKAN
                                        </button>
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

        <script>
            function toggleTargetInput(radio) {
                const targetRow = document.getElementById('targetRow');
                const targetInput = document.getElementById('target');
                if (radio.value === 'Sudah') {
                    targetRow.style.display = 'none';
                    targetInput.value = ''; // Reset the value if 'Sudah' is selected
                } else {
                    targetRow.style.display = 'block';
                    targetInput.focus(); // Optional: Focus on the input when 'Belum' is selected
                }
                targetInput.required = radio.value === 'Belum';
            }
        </script>

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

    <!--================== DATA BERHASIL DI SIMPAN ==================-->
    @if (session('success'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Lembar Kerja & Evaluasi Terkirim',
            text: 'Lembar Kerja & Evaluasi Bapak/Ibu Sudah Kami Terima, Terimakasih!',
            confirmButtonText: 'OK'
        });
    </script>
    @endif
    <!--================== END ==================-->

    <!--================== DATA GAGAL DI SIMPAN ==================-->
    @if (session('error'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Lembar Kerja & Evaluasi Gagal Terkirim',
            text: 'Lembar Kerja & Evaluasi Bapak/Ibu Sudah Kami Belum Kami Terima!',
            confirmButtonText: 'OK'
        });
    </script>
    @endif
    <!--================== END ==================-->

    <!--================== TOKEN DUPLICATE ==================-->
    @if (session('token'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Token Sudah Terdaftar',
            text: 'Token yang Anda gunakan sudah terdaftar. Silakan mengisi ulang.',
            confirmButtonText: 'OK'
        });
    </script>
    @endif
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