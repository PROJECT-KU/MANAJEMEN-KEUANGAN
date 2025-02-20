@extends('public.layout.header')

@section('title')
Scopus Kafe | Rumah Scopus
@stop


@section('konten')
<!-- ======= Hero Section ======= -->
<section id="hero" class="hero d-flex align-items-center">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 d-flex flex-column justify-content-center">
                <h1 data-aos="fade-up">Scopus Kafe</h1>
                <h2 data-aos="fade-up" data-aos-delay="400">
                    Scopus Kafe adalah pendampingan secara private, yang akan di dampingi oleh Trainer Rumah Scopus. Pendampingan ini berdurasi selama 5 jam per sesinya. Pendampingan ini akan di
                    selenggarakan di Rumah Scopus Pusat yang terletak di Yogyakarta.
                </h2>
                <div data-aos="fade-up" data-aos-delay="600">
                    <div class="text-center text-lg-start">
                        <a href="https://www.youtube.com/@rumahscopus" class="btn-get-started scrollto d-inline-flex align-items-center justify-content-center align-self-center" target="_blank">
                            <span>Get Started</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 hero-img" data-aos="zoom-out" data-aos-delay="200">
                <img src="{{ asset('assets/artikel/img/hero-img.png') }}" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</section>

<div class="container mt-5 mb-5">
    <div class="main-card" style="background: linear-gradient(to right, #ff3131, #ff914d); border-radius: 15px; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); overflow: hidden; margin-bottom: 20px;" data-aos="fade-right">
        <div class="card-body" style="padding: 30px; border-radius: 15px;">
            <div class="row" style="display: flex; justify-content: center; flex-wrap: wrap;">
                @foreach($datas as $item)
                <div class="col-md-3 mb-2" data-aos="fade-up" style="margin: 10px;">
                    <div class="inner-card" style="border-radius: 10px; padding: 20px; background-color: white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); position: relative;">
                        <div class="d-flex align-items-center">
                            <div style="border-radius: 5px; background: linear-gradient(to right, #ff3131, #ff914d); padding: 10px; display: inline-block;">
                                <i class="fas fa-user-graduate" style="font-size: 24px; color: white;"></i>
                            </div>
                            <div style="margin-left: 10px;">
                                <h5 style="font-weight: bold;">{{ strtoupper ($item->sesi) }}</h5>
                                <p style="display: flex; align-items: center; margin-bottom: 5px;">
                                    <i class="fas fa-clock" style="margin-right: 10px;"></i>
                                    <span>{{ date('H:i', strtotime($item->waktu_mulai)) }} - {{ date('H:i', strtotime($item->waktu_selesai)) }} WIB</span>
                                </p>
                                <p style="display: flex; align-items: center; margin-bottom: 5px;">
                                    <i class="fas fa-users" style="margin-right: 10px;"></i>
                                    <span>Sisa Kuota : {{ $item->kuota }}</span>
                                </p>
                                <p style="display: flex; align-items: center; margin-bottom: 30px;">
                                    <i class="fas fa-map-marker-alt" style="margin-right: 18px;"></i>
                                    <span>{{ $item->lokasi }}</span>
                                </p>
                            </div>
                        </div>
                        <a href="{{ url('/Scopus-Kafe/Form-Pendaftaran') }}"><button class="btn btn-info" src="" style="background-color: #6495ED; color:white; font-size: 16px; width: 100%; position: absolute; bottom: 0; left: 0;">Daftar Sekarang</button></a>
                        <div class="label" style="position: absolute; top: 0px; right: 0px; background-color: #ff3131; color: white; padding: 5px; border-radius: 5px; font-size: 15px;">Rp. {{$item->biaya}}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!--================== RELOAD KETIKA DATA SUKSES ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if (session()->has('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            html: 'Data Pendaftaran Scopus Kafe Berhasil Terkirim!<br><br>Pembayaran Anda akan kami verifikasi terlebih dahulu. Mohon menunggu maksimal 1x24 jam.',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif

@if (session()->has('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Data Pendaftaran Scopus Kafe Gagal Terkirim',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif
<!--================== END ==================-->
@stop