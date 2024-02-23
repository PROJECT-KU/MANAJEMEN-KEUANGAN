<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap-social/bootstrap-social.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('path/to/sweetalert2.css') }}">
    <script src="{{ asset('path/to/sweetalert2.js') }}"></script>

    <style>
        .container {
            background-color: #FF8C00;
            margin: 20px;
            max-width: 100%;
        }

        .card {
            background-color: #F8F8FF;
            border-radius: 10px;
            max-width: 100%;
            /* Lebar maksimum card */
            padding: 20px;
            margin-top: 100px;
            /* Jarak atas */
            margin-bottom: 100px;
            /* Jarak bawah */
        }

        .card_dalam {
            background-color: #F5F5F5;
            color: white;
            border-radius: 10px;
            max-width: 100%;
            /* Lebar maksimum card */
            padding: 20px;
            margin-top: 20px;
            /* Jarak atas */
            margin-bottom: 20px;
            /* Jarak bawah */
        }

        @media (max-width: 767px) {

            /* Target perangkat dengan lebar maksimum 767px */
            .container {
                background-color: transparent;
                /* Mengubah warna latar belakang menjadi transparan */
            }

            .mobile {
                font-size: 18px;
            }
        }

        @media (min-width: 768px) and (max-width: 991px) {
            .mobile {
                margin-left: 100px;
            }
        }
    </style>
</head>

<body>
    <div id="app">
        <section class="section">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-8 offset-lg-2 col-xl-8 offset-xl-2">
                        <center>
                            <div class="card mt-5 mb-5" style="width: 35rem;">
                                <div style="text-align: center;" class="login-brand">
                                    <a href="https://rumahscopusfoundation.com/"> <img src="{{ $message->embed(public_path('assets/img/LogoRSC.png')) }}" alt="logo" width="250"></a>
                                </div>
                                <div class="card-body">
                                    <p style="font-weight: bold; font-size: 35px;">Hallo, {{ $user->full_name }}</p>
                                    <p style="font-size: 15px;">Yeay, Kamu telah menerima pembayaran gaji pada periode <b>{{ strftime('%d %B %Y', strtotime($gaji->tanggal)) }}</b>, berikut rinciannya:</p>
                                    <div class="card_dalam" style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                                        <div class="card-body mobile geser1" style="color: black; text-align: left; flex: 1; margin-left:25px;">
                                            <p>Kode Transaksi </p>
                                            <p>Total </p>
                                        </div>
                                        <div class="card-body mobile" style="color: black; text-align: left; flex: 1; margin-right: 25px; margin-left:180px;">
                                            <div style="text-align: right;">
                                                <p>{{ strtoupper($gaji->id_transaksi) }}</p>
                                                <p>Rp. {{ number_format($gaji->total, 0, ',', ',') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <p style="font-size:15px">Kamu bisa melihat riwayat pembayaran gaji di Rincian Gaji melalui sistem MIS.</p>

                                    <p>Salam,<br>

                                        Admin Rumah Scopus Foundation<br>
                                        Rumah Scopus Foundation (RSC) <br>
                                        Bangunsari, Jl. Bangunsari, Bangunsari, Bangun Kerto, Turi,<br>
                                        Sleman Regency, Special Region of Yogyakarta 55551 <br>
                                        Telp: 0812-2688-3280</p>
                                    <a href="https://www.instagram.com/rumah_scopus/"> <img src="{{ $message->embed(public_path('assets/img/instagram.png')) }}" alt="logo" width="40" height="40" style="margin-right: 20px; margin-top:10px"></a>
                                    <a href="https://www.youtube.com/@rumahscopus"> <img src="{{ $message->embed(public_path('assets/img/youtube.png')) }}" alt="logo" width="40" style="margin-right: 20px; margin-top:10px"></a>
                                    <a href="https://www.facebook.com/RumahScopusAkademi"> <img src="{{ $message->embed(public_path('assets/img/facebook.png')) }}" alt="logo" width="40" style="margin-top:10px"></a>
                                </div>
                            </div>
                        </center>
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>

</html>