@extends('public.layout.header')

@section('title')
Refrensi Paper Selengkapnya | Rumah Scopus
@stop

<style>
    /* GAMBAR COVER */
    .entry-img {
        display: flex;
        justify-content: center;
        align-items: center;
        height: auto;
        /* Adjust the height as needed */
    }

    /* END */

    /* SHARE */
    .entry-footer {
        display: flex;
        justify-content: space-between;
        /* Menyusun konten secara bersebelahan */
        align-items: center;
        /* Memusatkan konten secara vertikal */
    }

    .author-social {
        display: flex;
        align-items: center;
        /* Memusatkan konten secara vertikal */
    }

    .social-links {
        display: flex;
    }

    .social-links a {
        margin-right: 5px;
    }

    .social-links a:last-child {
        margin-right: 0;
    }

    /* END */
</style>
<!--================== IKLAN ==================-->
<!-- google ads -->
<meta name="google-adsense-account" content="ca-pub-4416930989633394">
<!-- end -->
<!--================== END ==================-->

@section('konten')
@csrf
<main id="main">

    <!--================== BREADCRUMBS ==================-->
    <section class="breadcrumbs" style="background: linear-gradient(to right, #ff3131, #ff914d);">
        <div class="container">

            <ol>
                <li><a href="{{ url('/blog') }}">Home</a></li>
                <li><a href="">Refrensi Paper</a></li>
            </ol>

            <h2>Refrensi Paper</h2>

        </div>
    </section>
    <!--================== END ==================-->

    <!--================== BLOG CONTENT ==================-->
    <section id="blog" class="blog">
        <div class="container" data-aos="fade-up">

            <div class="row">
                <div class="col-lg-8 entries">

                    <!--================== ARTIKEL ==================-->
                    <article class="entry entry-single">
                        <h2 class="entry-title">
                            <a href="">{{ $datas->judul_paper }}</a>
                        </h2>
                        <div class="entry-meta">
                            <ul>
                                <li class="d-flex align-items-center">
                                    <i class="bi bi-person"></i>
                                    <a href="">{{ $datas->nama_author }}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="entry-content">
                            <div class="article-container">
                                {!! $datas->abstrak !!}
                            </div>
                            <style>
                                .article-container img {
                                    max-width: 100%;
                                    /* Set maximum width to 100% */
                                    height: auto;
                                    /* Maintain aspect ratio */
                                    display: block;
                                    /* Ensure proper rendering */
                                    margin: 0 auto;
                                    /* Center the images */
                                }
                            </style>
                        </div>
                    </article>
                    <!--================== END ==================-->
                </div>

                <div class="col-lg-4">
                    <div class="sidebar">
                        <h3 class="sidebar-title">Detail Paper</h3>
                        <hr>
                        <!--================== MENAMPILKAN NAMA AUTHOR ==================-->
                        <h3 class="sidebar-title">Subjek Area Journal</h3>
                        <div class="sidebar-item tags">
                            <ul>
                                @php
                                $authors = explode(',', $datas->subjek_area_journal); // Mengambil nama author
                                @endphp
                                @foreach ($authors as $author)
                                <li><a href="#">{{ $author }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                        <!--================== END ==================-->

                        <!--================== JUDUL PAPER ==================-->
                        <h3 class="sidebar-title">Nama Journal</h3>
                        <div class="sidebar-item">
                            <p>{{ $datas->nama_journal ?? 'Nama Journal tidak tersedia' }}</p> <!-- Mengakses langsung judul_paper -->
                        </div>
                        <!--================== END ==================-->

                        <!--================== DOI ==================-->
                        <h3 class="sidebar-title">DOI</h3>
                        <div class="sidebar-item recent-posts">
                            <p>{{ $datas->doi ?? 'DOI tidak tersedia' }}</p> <!-- Mengakses langsung DOI -->
                        </div>
                        <!--================== END ==================-->

                        <!--================== QUARTILE & APC ==================-->
                        <h3 class="sidebar-title">Quartile & APC</h3>
                        <div class="sidebar-item recent-posts">
                            <p>Q-{{ $datas->quartile_journal ?? 'Quartile Journal tidak tersedia' }} | {{ $datas->apc ?? 'APC Journal tidak tersedia' }}</p>
                        </div>
                        <!--================== END ==================-->

                        <!--================== QUARTILE & APC ==================-->
                        <h3 class="sidebar-title">Type Journal</h3>
                        <div class="sidebar-item recent-posts">
                            <p>{{ $datas->type ?? 'Type Journal tidak tersedia' }}</p>
                        </div>
                        <!--================== END ==================-->
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@stop