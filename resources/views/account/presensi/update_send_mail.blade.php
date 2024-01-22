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
</head>

<body>
    <div id="app">
        <section class="section">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-8 offset-lg-2 col-xl-8 offset-xl-2">
                        <div style="text-align: center;" class="login-brand">
                            <a href="https://rumahscopusfoundation.com/"> <img src="{{ $message->embed(public_path('assets/img/LogoRSC.png')) }}" alt="logo" width="250"></a>
                        </div>
                        <p style="font-weight: bold; font-size: 25px;">Halo, {{ $user->full_name }}</p>
                        <p>Yeay, kamu telah menyelesaikan presensi hari ini pada tanggal <b>{{ strftime('%d %B %Y', strtotime($presensi->updated_at)) }}</b> pukul <b>{{ strftime('%H:%M', strtotime($presensi->updated_at)) }}</b>.</p>
                        <p>
                            Dengan lama bekerja
                            <b>
                                <?php
                                $created_at = strtotime($presensi->created_at);
                                $time_pulang = strtotime($presensi->time_pulang);

                                // Menghitung selisih waktu dalam detik
                                $selisih_detik = $time_pulang - $created_at;

                                // Menghitung jumlah jam dan menit
                                $jam = floor($selisih_detik / 3600);
                                $menit = floor(($selisih_detik % 3600) / 60);

                                // Menampilkan lama kerja dalam format "jam jam menit menit"
                                echo sprintf('%02d jam %02d menit', $jam, $menit);

                                // Menambahkan pesan kondisional
                                if ($jam < 8) {
                                    echo ' (Lama kerja kamu kurang dari 8 jam kerja, yuk tingkatkan kinerja kamu!)';
                                } else {
                                    echo ' (Yeay, lama kerja kamu sangat cukup, tetap semangat dan terus tingkatkan kinerja kamu!)';
                                }
                                ?>
                            </b>
                        </p>

                        <p>Selamat Beristirahat!</p>

                        <p>Salam,<br>

                            Admin Rumah Scopus Foundation<br>
                            Rumah Scopus Foundation (RSC) <br>
                            Bangunsari, Jl. Bangunsari, Bangunsari, Bangun Kerto, Turi,<br>
                            Sleman Regency, Special Region of Yogyakarta 55551 <br>
                            Telp: 0812-2688-3280</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>

</html>