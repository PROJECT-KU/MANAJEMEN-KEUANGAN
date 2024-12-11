@extends('account.artikel.layout.header')

@section('title')
Meme | Rumah Scopus
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
    }
</style>

@section('konten')
<!-- ======= Hero Section ======= -->
<section id="hero" class="hero d-flex align-items-center">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 d-flex flex-column justify-content-center">
                <h1 data-aos="fade-up">Find Your Way To The Right Scopus Journal</h1>
                <h2 data-aos="fade-up" data-aos-delay="400">Like Exploring The Forest Of Knowledge, Finding Your Way To The Scopus Journal Is An Unforgettable Adventure</h2>
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

@stop