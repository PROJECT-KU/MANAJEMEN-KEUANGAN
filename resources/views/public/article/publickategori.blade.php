@extends('public.layout.header')

@section('title')
Kategori Blog | Rumah Scopus
@stop
<style>
    /* Hero Category - Modern Center Focus */
    .hero {
        position: relative;
        padding: 150px 0 100px;
        background: #ffffff;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Background Ornament agar tidak kosong */
    .hero::before {
        content: "";
        position: absolute;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255, 49, 49, 0.05) 0%, transparent 70%);
        top: -150px;
        right: -50px;
        z-index: 0;
    }

    .hero .container {
        position: relative;
        z-index: 1;
    }

    .hero-meta-badge {
        display: inline-flex;
        align-items: center;
        padding: 8px 20px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 50px;
        margin-bottom: 30px;
    }

    /* Tipografi Utama Halaman Kategori */
    .hero h1 {
        font-size: clamp(36px, 6vw, 64px);
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        letter-spacing: -3px;
        margin-bottom: 25px;
    }

    .hero-description {
        max-width: 700px;
        margin: 0 auto;
        font-size: 18px;
        color: #64748b;
        line-height: 1.8;
    }

    /* Tombol Premium */
    .btn-hero {
        background: linear-gradient(135deg, #ff3131 0%, #ff914d 100%);
        color: white !important;
        padding: 16px 35px;
        border-radius: 12px;
        font-weight: 700;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 10px 20px rgba(255, 49, 49, 0.2);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .btn-hero:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(255, 49, 49, 0.3);
    }

    .btn-get-started {
        background: linear-gradient(135deg, #ff3131 0%, #ff914d 100%);
        color: white !important;
        padding: 14px 30px;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 10px 20px rgba(255, 49, 49, 0.2);
        text-decoration: none;
    }

    .btn-get-started:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(255, 49, 49, 0.3);
    }

    /* Animasi Halus untuk Gambar */
    .hero-img img {
        animation: floating 4s ease-in-out infinite;
        filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.05));
    }

    @keyframes floating {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-15px);
        }
    }
</style>

@section('konten')
<!-- ======= Hero Section ======= -->
<section id="hero" class="hero text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11">

                <div class="hero-meta-badge shadow-sm" data-aos="fade-up">
                    <span class="text-gradient fw-bold small text-uppercase" style="letter-spacing: 2px;">
                        <i class="bi bi-tag-fill me-2"></i> Kategori Artikel
                    </span>
                </div>

                <h1 data-aos="fade-up" data-aos-delay="100">
                    Eksplorasi <span class="text-gradient">Wawasan</span> Melalui <br class="d-none d-md-block">
                    Koleksi <span class="text-gradient">Publikasi Riset</span>
                </h1>

                <div class="hero-description mt-4" data-aos="fade-up" data-aos-delay="200">
                    <p class="fst-italic text-muted">
                        "Janganlah pernah menyerah ketika kamu masih mampu berusaha lagi. <br class="d-none d-md-block">
                        Tidak ada kata berakhir sampai kamu berhenti mencoba."
                    </p>
                </div>

                <div class="mt-5" data-aos="fade-up" data-aos-delay="300">
                    <a href="https://www.youtube.com/@rumahscopus" class="btn-hero" target="_blank">
                        Mulai Belajar Sekarang <i class="bi bi-arrow-right ms-2"></i>
                    </a>

                    <div class="mt-4 d-flex justify-content-center align-items-center gap-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-journal-text text-danger me-2"></i>
                            <span class="small text-muted fw-bold">Update Berkala</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-shield-check text-danger me-2"></i>
                            <span class="small text-muted fw-bold">Konten Terverifikasi</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<main id="main">
    @foreach ($categories_artikel as $category)
    <!-- ======= Team Section ======= -->
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
    <section id="recent-blog-posts" class="recent-blog-posts">
        <div class="container" data-aos="fade-up">
            @php
            // Mendapatkan ID kategori dari URL
            $categoryID = request()->segment(3);

            // Menentukan apakah kategori saat ini sesuai dengan kategori yang dipilih
            $isCurrentCategory = $categoryID == $category->id;
            @endphp

            @if ($isCurrentCategory)
            <header class="section-header">
                <p style="font-size: 30px;">{{ strtoupper($category->kategori) }}</p>
            </header>

            <div class="row">
                @foreach ($articles as $article)
                @if ($article->status == 'publish')
                <div class="col-lg-4 mb-5">
                    <div class="post-box">
                        <div class="post-img"><img src="{{ asset('images/' . $article->gambar_depan) }}" class="img-fluid" alt=""></div>
                        <span class="post-date" style="font-size: 12px;">
                            {{ \Carbon\Carbon::parse($article->created_at)->format('l, j F Y') }}
                            <!-- Format: hari bulan tanggal tahun -->
                            - {{ \Carbon\Carbon::parse($article->created_at)->diffForHumans() }}
                            <!-- Berapa menit atau berapa jam yang lalu -->
                        </span>

                        <h3 class="post-title">{{ $article->judul }}</h3>

                        <header class="readmore mt-auto" style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center;">
                                @if ($article->gambar == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail rounded-circle" style="width: 55px; height:55px;">
                                @else
                                <img id="image-preview" class="img-thumbnail rounded-circle" src="{{ asset('assets/img/profil/' .  $article->gambar) }}" alt="Preview Image" style="width: 55px; height:55px;">
                                @endif
                                <div style="font-size: 15px; margin-left: 10px;" class="mt-3">
                                    <p>{{ $article->full_name }}</p>
                                </div>
                            </div>
                            <a href="{{ route('blog.topic.blog-single', ['id' => $article->id, 'token' => $article->token]) }}" class="readmore stretched-link" style="text-align: right;  background: linear-gradient(to right, #ff3131, #ff914d);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;">
                                <span>Baca</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </header>

                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endif
            @endforeach
            {{ $articles->links("vendor.pagination.bootstrap-4") }}
        </div>
    </section>
</main>
@stop