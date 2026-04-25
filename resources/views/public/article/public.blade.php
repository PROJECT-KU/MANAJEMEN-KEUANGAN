@extends('public.layout.header')

@section('title', 'Blog | Rumah Scopus')

@section('konten')
<style>
    /* Hero Refined - Blog Version */
    .hero {
        position: relative;
        /* Padding disesuaikan */
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    /* Background halus agar tidak kosong */
    .hero::before {
        content: "";
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at center, rgba(255, 49, 49, 0.02) 0%, transparent 70%);
        z-index: 0;
    }

    .hero .container {
        position: relative;
        z-index: 1;
    }

    /* Badge Blog */
    .hero-meta {
        display: inline-flex;
        align-items: center;
        padding: 6px 16px;
        background: #fff5f5;
        border: 1px solid rgba(255, 49, 49, 0.1);
        border-radius: 50px;
        margin-bottom: 20px;
    }

    /* Judul Utama */
    .hero h1 {
        font-size: clamp(32px, 5vw, 56px);
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
        margin-bottom: 20px;
    }

    .hero .hero-description {
        max-width: 800px;
        margin: 0 auto;
        font-size: 18px;
        color: #64748b;
        line-height: 1.6;
    }

    /* Utilitas Gradient - Pastikan ini ada agar teks tidak hilang */
    .text-gradient {
        background: linear-gradient(135deg, #ff3131 0%, #ff914d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
        /* Penting agar gradient tampil sempurna */
    }

    /* Tombol */
    .btn-hero {
        background: linear-gradient(135deg, #ff3131 0%, #ff914d 100%);
        color: white !important;
        padding: 12px 30px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 10px 20px rgba(255, 49, 49, 0.15);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .btn-hero:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 25px rgba(255, 49, 49, 0.25);
    }

    /* Card Modernization */
    .post-box {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        border-radius: 15px;
        overflow: hidden;
        background: #fff;
        height: 100%;
        display: flex;
        flex-direction: column;
        padding: 0 !important;
        /* Reset padding to handle image correctly */
    }

    .post-box:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .post-img img {
        width: 100%;
        height: 220px;
        object-fit: cover;
    }

    .post-content {
        padding: 25px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .post-title {
        font-size: 20px;
        font-weight: 700;
        margin: 15px 0;
        line-height: 1.4;
        color: var(--text-dark);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .section-header {
        margin-bottom: 40px;
        border-bottom: 2px solid #f1f2f6;
        padding-bottom: 15px;
    }

    .author-img {
        width: 45px;
        height: 45px;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }
</style>

<section id="hero" class="hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10" data-aos="fade-up">

                <div class="hero-meta">
                    <span class="text-gradient fw-bold small text-uppercase" style="letter-spacing: 1px;">
                        <i class="bi bi-journal-text me-2"></i> Arsip Pengetahuan & Riset
                    </span>
                </div>

                <h1>
                    Temukan <span class="text-gradient">Inspirasi</span> & Strategi <br class="d-none d-md-block">
                    Publikasi <span class="text-gradient">Jurnal Scopus</span>
                </h1>

                <div class="hero-description">
                    <p class="mb-0">
                        "Janganlah pernah menyerah ketika kamu masih mampu berusaha lagi. <br class="d-none d-md-block">
                        Tidak ada kata berakhir sampai kamu berhenti mencoba."
                    </p>
                </div>

                <div class="mt-4">
                    <a href="https://www.youtube.com/@rumahscopus" class="btn-hero" target="_blank">
                        Langganan Update Artikel <i class="bi bi-bell ms-2"></i>
                    </a>
                </div>

                <div class="mt-4 d-flex justify-content-center align-items-center gap-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check2-circle text-success me-2"></i>
                        <span class="small text-muted fw-bold">Tips Riset</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check2-circle text-success me-2"></i>
                        <span class="small text-muted fw-bold">Sertifikat Resmi</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check2-circle text-success me-2"></i>
                        <span class="small text-muted fw-bold">Akses Selamanya</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

@foreach ($categories_artikel as $category)
@php
$articlesInCategory = $artikel->where('categories_artikel_id', $category->id)
->where('status', 'publish')
->sortByDesc('created_at')
->take(3);
@endphp

@if ($articlesInCategory->isNotEmpty())
<section class="recent-blog-posts py-5">
    <div class="container" data-aos="fade-up">

        <header class="section-header d-flex justify-content-between align-items-end">
            <div>
                <h2 class="fw-bold mb-0" style="letter-spacing: -1px;">{{ strtoupper($category->kategori) }}</h2>
            </div>
            <a href="{{ route('blog.topic.kategori', ['categories_artikel_id' => $category->id, 'token' => $category->token]) }}" class="text-gradient text-decoration-none">
                Selengkapnya <i class="bi bi-chevron-right"></i>
            </a>
        </header>

        <div class="row g-4">
            @foreach ($articlesInCategory as $article)
            <div class="col-lg-4 col-md-6">
                <article class="post-box">
                    <div class="post-img">
                        <img src="{{ asset('images/' . $article->gambar_depan) }}" alt="{{ $article->judul }}">
                    </div>

                    <div class="post-content">
                        <div class="d-flex align-items-center mb-3">
                            <small class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i> {{ \Carbon\Carbon::parse($article->created_at)->translatedFormat('d M Y') }}
                            </small>
                        </div>

                        <h3 class="post-title">
                            <a href="{{ route('blog.topic.blog-single', ['id' => $article->id, 'token' => $article->token]) }}" class="text-decoration-none text-dark">
                                {{ $article->judul }}
                            </a>
                        </h3>

                        <div class="mt-auto d-flex justify-content-between align-items-center pt-3 border-top">
                            <div class="d-flex align-items-center">
                                <img src="{{ $article->gambar ? asset('assets/img/profil/' . $article->gambar) : asset('assets/img/avatar/avatar-1.png') }}"
                                    class="rounded-circle author-img me-2" alt="Author">
                                <small class="fw-bold text-secondary">{{ Str::limit($article->full_name, 15) }}</small>
                            </div>
                            <a href="{{ route('blog.topic.blog-single', ['id' => $article->id, 'token' => $article->token]) }}"
                                class="text-gradient text-decoration-none fw-bold small">
                                Baca <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </article>
            </div>
            @endforeach
        </div>

    </div>
</section>
@endif
@endforeach

@stop