@extends('account.artikel.layout.header')

@section('title')
Blog | Rumah Scopus
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

@foreach ($categories_artikel as $category)
<section id="recent-blog-posts" class="recent-blog-posts">
    <div class="container" data-aos="fade-up">

        @php
        $articlesInCategory = $artikel->where('categories_artikel_id', $category->id)->sortByDesc('created_at')->take(3);
        @endphp

        @if ($articlesInCategory->isNotEmpty() && $articlesInCategory->contains('status', 'publish'))
        <header class="section-header">
            <p style="font-size: 30px;">{{ strtoupper($category->kategori) }}</p>
            <a href="{{ route('blog.topic.kategori', ['categories_artikel_id' => $category->id, 'token' => $category->token]) }}" class="more-text">
                <h2>More <i class="fas fa-chevron-right"></i></h2>
            </a>
        </header>

        <div class="row">
            @foreach ($articlesInCategory as $article)
            @if ($article->status == 'publish')
            <div class="col-lg-4">
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
                            <img id="image-preview" class="img-thumbnail rounded-circle" src="{{ asset('images/' .  $article->gambar) }}" alt="Preview Image" style="width: 55px; height:55px;">
                            @endif
                            <div style="font-size: 15px; margin-left: 10px;" class="mt-3">
                                <p>{{ $article->full_name }}</p>
                            </div>
                        </div>
                        <a href="{{ route('blog.topic.blog-single', ['id' => $article->id, 'token' => $article->token]) }}" class="readmore stretched-link" style="text-align: right;">
                            <span>Read More</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </header>

                </div>
            </div>
            @endif
            @endforeach
        </div>
        @endif

    </div>
</section>
@endforeach
@stop