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
                                    <p style="font-weight: bold; font-size: 35px;">Hallo, {{ $scopuscamp->nama }}</p>
                                    <p>Terima kasih sudah melakukan pendaftaran Scopus Camp! Kamu baru saja mendaftar Scopus Camp, berikut rinciannya:</p>
                                    <div class="card_dalam">
                                        <div class="card-body" style="color: black; text-align: left;">
                                            <p>Kode Transaksi <b style="margin-left: 65px;">: {{ strtoupper($scopuscamp->id_transaksi) }}</b></p>
                                            <p>Tanggal <b style="margin-left: 105px;">: {{ strftime('%d %B %Y', strtotime($scopuscamp->created_at)) }}</b></p>
                                            <!-- <p>Scopus Camp <b style="margin-left: 70px;">: {{ strtoupper($campName) }}</b></p> -->
                                            <p>Scopus Camp <b style="margin-left: 70px;">: {{ strtoupper($scopuscamp->camp) }}</b></p>
                                            <!-- <p>Mulai Pelaksanaan <b style="margin-left: 43px;">: {{ date('d F Y', strtotime($scopuscamp->mulai)) }}</b></p>
                                            <p>Selesai Pelaksanaan <b style="margin-left: 31px;">: {{ date('d F Y', strtotime($scopuscamp->selesai)) }}</b></p>
                                            <p>Tempat Pelaksanaan <b style="margin-left: 32px;">: {{ strtoupper($scopuscamp->tempat) }}</b></p> -->
                                        </div>
                                    </div>
                                    <p>Status pendaftaran Scopus Camp anda saat ini masih tahap <b>{{ $status }}</b>. Silahkan Tunggu 1x24 jam.</p>
                                    <p>Salam Q1!</p>

                                    <p>Salam,<br>

                                        Admin Rumah Scopus Foundation<br>
                                        Rumah Scopus Foundation (RSC) <br>
                                        Bangunsari, Jl. Bangunsari, Bangunsari, Bangun Kerto, Turi,<br>
                                        Sleman Regency, Special Region of Yogyakarta 55551 <br>
                                        Telp: 0812-2688-3280</p>
                                    <a href="https://www.instagram.com/rumah_scopus/"> <img src="{{ $message->embed(public_path('assets/img/instagram.png')) }}" alt="logo" width="30" height="30" style="margin-right: 20px; margin-top:10px"></a>
                                    <a href="https://www.youtube.com/@rumahscopus"> <img src="{{ $message->embed(public_path('assets/img/youtube.png')) }}" alt="logo" width="30" style="margin-right: 20px; margin-top:10px"></a>
                                    <a href="https://www.facebook.com/RumahScopusAkademi"> <img src="{{ $message->embed(public_path('assets/img/facebook.png')) }}" alt="logo" width="30" style="margin-top:10px"></a>
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