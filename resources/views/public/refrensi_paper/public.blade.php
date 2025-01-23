@extends('public.layout.header')
@extends('public.paperisasi.layout.css')

@section('title')
Refrensi Paper | Rumah Scopus
@stop

<style>
    /* Remove border when hovering over the image */
    #image-preview {
        border: none !important;
        /* Hapus border pada gambar */
    }

    #image-preview:hover {
        border: none;
        /* Hapus border pada gambar saat dihover */
    }

    .container {
        width: 100%;
        max-width: 1200px;
        /* Sesuaikan lebar container sesuai kebutuhan */
        margin: 0 auto;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .more-text {
        font-size: 15px;
        margin: 0;
        text-align: right;
        background: linear-gradient(to right, #ff3131, #ff914d);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>

<!--================== ANIMASI CARD PAPER ==================-->
<style>
    /* Animasi saat hover pada card */
    .hover-card {
        transition: transform 0.4s ease-out, box-shadow 0.4s ease-out, filter 0.3s ease-in-out;
        cursor: pointer;
        /* Mengubah kursor menjadi pointer saat dihover */
        transform: translateZ(0);
        /* Memastikan rendering yang lebih halus */
    }

    .hover-card:hover {
        transform: translateY(-12px) scale(1.07) rotate(2deg);
        /* Meningkatkan efek perpindahan dan sedikit rotasi */
        box-shadow: 0px 20px 35px rgba(0, 0, 0, 0.2);
        /* Membuat bayangan lebih besar */
        filter: brightness(1.1);
        /* Mencerahkan sedikit gambar saat dihover */
    }

    /* Efek Bayangan Lebih Besar dengan Efek Blur */
    .card {
        transition: box-shadow 0.3s ease-in-out;
    }

    .card:hover {
        box-shadow: 0px 20px 40px rgba(0, 0, 0, 0.3);
        /* Membuat bayangan lebih kuat saat hover */
    }
</style>
<!--================== END ==================-->

@section('konten')
<!-- ======= Hero Section ======= -->
<section id="hero" class="hero d-flex align-items-center">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 d-flex flex-column justify-content-center">
                <h1 data-aos="fade-up">Wujudkan Penelitian Anda dengan Referensi Paper Terbaik!</h1>
                <h2 data-aos="fade-up" data-aos-delay="400">Akses jurnal berkualitas tinggi untuk meningkatkan kredibilitas penelitian Anda. Kami menyediakan informasi lengkap, mulai dari subjek area jurnal hingga rincian DOI</h2>
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
                <img src="{{ asset('assets/artikel/img/TinyPNG.png') }}" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</section>

<div class="container mt-5 mb-5">
    <div class="main-card" data-aos="fade-right">
        <div class="card-body">
            <div class="row">
                @foreach ($datas as $data)
                <div class="col-md-3 mb-2" data-aos="fade-up">
                    <div class="inner-card" style="background-color: white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; justify-content: space-between; height: 100%; border-radius: 10px; padding: 20px;">
                        <div style="width: 100%;">
                            <div class="d-flex align-items-center" style="width: 100%;">
                                <div style="margin-left: 0; width: 100%;">
                                    <h5 style="font-weight: bold; width: 100%;">{{ $data->judul_paper }}</h5>
                                    <p style="width: 100%;">Q-{{ $data->nama_journal }}</p>
                                    <p style="width: 100%;">Q-{{ $data->quartile_journal }}</p>
                                    <hr style="width: 100%; margin: 10px 0;">
                                    <h5 style="font-weight: bold; width: 100%;">Abstrak</h5>
                                    <span style="width: 100%; display: inline-block;">
                                        {{ implode(' ', array_slice(explode(' ', strip_tags($data->abstrak)), 0, 5)) }}
                                        @if(str_word_count(strip_tags($data->abstrak)) > 5)
                                        ...
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div style="width: 100%; margin-top: 15px;">
                            <a href="{{ route('public.refrensi-paper.Selengkapnya', $data->id) }}" style="text-decoration: none; width: 100%; display: inline-block;">
                                <button class="btn btn-submit rounded-pill" style="color: white; font-size: 14px; width: 100%; background: linear-gradient(to right, #ff3131, #ff914d); border-radius: 15px; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); overflow: hidden;">
                                    <i class="fa fa-paper-plane"></i> SELENGKAPNYA
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

</div>
@stop