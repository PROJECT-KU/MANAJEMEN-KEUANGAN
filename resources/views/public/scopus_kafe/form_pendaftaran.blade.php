@extends('public.layout.header')

@section('title')
Form MemePendaftaran | Rumah Scopus
@stop


@section('konten')
<!-- ======= Hero Section ======= -->
<section id="hero" class="hero d-flex align-items-center">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 d-flex flex-column justify-content-center">
                <h1 data-aos="fade-up">Scopus Kafe</h1>
                <h2 data-aos="fade-up" data-aos-delay="400">
                    Scopus Kafe adalah pendingan secara private, yang akan di dampingi oleh Trainer Rumah Scopus. Pendampingan ini berdurasi selama 4 jam per sesinya. Pendampingan ini akan di
                    selenggarakan di Joglo Rumah Scopus yang terletak di Yogyakarta.
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
    <div class="main-card">
        <h1 class="text-center mt-3" style="font-weight: bold; color: white;">
            Form Pendaftaran Scopus Kafe
        </h1>
        <div class="card-body" style="padding: 30px; border-radius: 15px;">
            <div class="row">



            </div>
        </div>
    </div>
</div>
@stop