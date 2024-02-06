<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Evaluasi | RUMAH SCOPUS</title>
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
                                <h4>EVALUASI SCOPUS CAMP</h4>
                            </div>

                            @if($peserta)
                            <div class="card-body">
                                <form action="{{ route('account.peserta.update', $peserta->id) }}" method="POST" enctype="multipart/form-data" onsubmit="return validateform()">
                                    @csrf
                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="email" style="font-size: 15px;">Alamat Email</label>
                                            <input id="email" type="email" class="form-control" name="email" value="{{ $peserta->email }}" maxlength="50" minlength="5" onkeypress="return /[a-zA-Z0-9@.]/i.test(event.key)" readonly>
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
                                            <input id="nama" type="text" style="text-transform:uppercase;" class="form-control" name="nama" value="{{ $peserta->nama }}" minlength="5" onkeypress="return /[a-zA-Z0-9., ]/i.test(event.key)" readonly>
                                            @error('nama')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="afiliasi" style="font-size: 15px;">Affiliasi</label>
                                            <input id="afiliasi" type="text" style="text-transform:uppercase;" class="form-control" name="afiliasi" value="{{ $peserta->afiliasi }}" minlength="5" onkeypress="return /[a-zA-Z0-9 ]/i.test(event.key)" readonly>
                                            @error('afiliasi')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="scopus_camp" style="font-size: 15px;">Dengan adanya Scopus Camp apakah dapat membantu Bapak/Ibu dalam menyelesaikan Manuscript ?</label>
                                            <div class="form-check">
                                                <input type="radio" id="Sangat Membantu" name="scopus_camp" class="form-check-input" value="Sangat Membantu" required>
                                                <label class="form-check-label" for="Sangat Membantu" style="font-size: 15px;">Sangat Membantu</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="Lumayan Membantu" name="scopus_camp" class="form-check-input" value="Lumayan Membantu" required>
                                                <label class="form-check-label" for="Lumayan Membantu" style="font-size: 15px;">Lumayan Membantu</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="Tidak Membantu" name="scopus_camp" class="form-check-input" value="Tidak Membantu" required>
                                                <label class="form-check-label" for="Tidak Membantu" style="font-size: 15px;">Tidak Membantu</label>
                                            </div>
                                            @error('scopus_camp')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="materi" style="font-size: 15px;">Apakah materi yang disampaikan sudah jelas ?</label>
                                            <div class="form-check">
                                                <input type="radio" id="Sudah" name="materi" class="form-check-input" value="Sudah" required>
                                                <label class="form-check-label" for="Sudah" style="font-size: 15px;">Sudah</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="Belum" name="materi" class="form-check-input" value="Belum" required>
                                                <label class="form-check-label" for="Belum" style="font-size: 15px;">Belum</label>
                                            </div>
                                            @error('materi')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="pelayanan" style="font-size: 15px;">Apakah makanan yang disajikan cukup enak ?</label>
                                            <div class="form-check">
                                                <input type="radio" id="Sangat Enak" name="makanan" class="form-check-input" value="Sangat Enak" required>
                                                <label class="form-check-label" for="Sangat Enak" style="font-size: 15px;">Sangat Enak</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="Cukup Enak" name="makanan" class="form-check-input" value="Cukup Enak" required>
                                                <label class="form-check-label" for="Cukup Enak" style="font-size: 15px;">Cukup Enak</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="Kurang Enak" name="makanan" class="form-check-input" value="Kurang Enak" required>
                                                <label class="form-check-label" for="Kurang Enak" style="font-size: 15px;">Kurang Enak</label>
                                            </div>
                                            @error('makanan')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="pelayanan" style="font-size: 15px;">Apakah pelayanan kami sudah bagus ?</label>
                                            <div class="form-check">
                                                <input type="radio" id="Sangat Bagus" name="pelayanan" class="form-check-input" value="Sangat Bagus" required>
                                                <label class="form-check-label" for="Sangat Bagus" style="font-size: 15px;">Sangat Bagus</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="Cukup" name="pelayanan" class="form-check-input" value="Cukup" required>
                                                <label class="form-check-label" for="Cukup" style="font-size: 15px;">Cukup</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="Kurang" name="pelayanan" class="form-check-input" value="Kurang" required>
                                                <label class="form-check-label" for="Kurang" style="font-size: 15px;">Kurang</label>
                                            </div>
                                            @error('pelayanan')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="tempat" style="font-size: 15px;">Apakah tempat terselenggaranya camp cukup nyaman ?</label>
                                            <div class="form-check">
                                                <input type="radio" id="Sangat Nyaman" name="tempat" class="form-check-input" value="Sangat Nyaman" required>
                                                <label class="form-check-label" for="Sangat Nyaman" style="font-size: 15px;">Sangat Nyaman</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="Cukup Nyaman" name="tempat" class="form-check-input" value="Cukup Nyaman" required>
                                                <label class="form-check-label" for="Cukup Nyaman" style="font-size: 15px;">Cukup Nyaman</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="Kurang Nyaman" name="tempat" class="form-check-input" value="Kurang Nyaman" required>
                                                <label class="form-check-label" for="Kurang Nyaman" style="font-size: 15px;">Kurang Nyaman</label>
                                            </div>
                                            @error('tempat')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="terfavorit" style="font-size: 15px;">Siapa terfavorit Terfavorit Menurut Bapak/Ibu ?</label>
                                            <input id="terfavorit" type="text" class="form-control" name="terfavorit">
                                            @error('terfavorit')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="terbaik" style="font-size: 15px;">Siapa Trainer Terbaik Menurut Bapak/Ibu ?</label>
                                            <input id="terbaik" type="text" class="form-control" name="terbaik">
                                            @error('terbaik')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="terlucu" style="font-size: 15px;">Siapa Trainer Terlucu Menurut Bapak/Ibu ?</label>
                                            <input id="terlucu" type="text" class="form-control" name="terlucu">
                                            @error('terlucu')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="kritik" style="font-size: 15px;">Kritik & Saran Untuk Rumah Scopus</label>
                                            <textarea id="kritik" type="text" class="form-control" name="kritik" required></textarea>
                                            @error('kritik')
                                            <div class="invalid-feedback" style="display: block">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                                            SIMPAN
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @else
                        <!-- Display a message or handle the case where $peserta is null -->
                        <p>No peserta found.</p>
                        @endif
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

    <!--================== DATA BERHASIL DI GAGAL SIMPAN ==================-->
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