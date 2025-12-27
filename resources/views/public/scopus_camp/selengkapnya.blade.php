@extends('public.layout.header')

@section('title')
Analisis Bibliometrik Selengkapnya | Rumah Scopus
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

@section('konten')
@csrf
<main id="main">

    <!--================== BREADCRUMBS ==================-->
    <section class="breadcrumbs" style="background: linear-gradient(to right, #ff3131, #ff914d); margin-top: 70px">
        <div class="container">

            <ol>
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><a href="{{ url('/Scopus-Camp') }}">Scopus Camp</a></li>
            </ol>

            <h2>Scopus Camp</h2>

        </div>
    </section>
    <!--================== END ==================-->

    <!--================== CONTENT ==================-->
    <section id="blog" class="blog">
        <div class="container" data-aos="fade-up">

            <div class="row">
                <div class="col-lg-8 entries">

                    <article class="entry entry-single">
                        {{-- Carousel --}}
                        <div id="carouselExampleIndicators" class="carousel slide mb-4" data-bs-ride="carousel" style="max-width: 100%; max-height: 400px; overflow: hidden;">
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="{{ asset('assets/img/public/cover2.png') }}" class="d-block w-100" alt="Cover 1" style="object-fit: cover; height: 400px;">
                                </div>
                                <div class="carousel-item">
                                    <img src="{{ asset('assets/img/public/cover1.png') }}" class="d-block w-100" alt="Cover 2" style="object-fit: cover; height: 400px;">
                                </div>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            </button>
                        </div>

                        <!-- JUDUL -->
                        <h2 class="entry-title text-center" style="font-size: 30px;">
                            <a href="">{{ $item->nama }} #{{ $item->nama_ke }}</a>
                        </h2>

                        <!-- DATA DETAIL -->
                        <div class="entry-meta d-flex justify-content-between flex-wrap mb-4" style="gap: 10px;">
                            <div class="d-flex align-items-center" style="gap: 20px;">

                                <!-- TANGGAL -->
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-calendar-alt me-1" style="color: #333;"></i>
                                    <span>
                                        {{ date('d M Y', strtotime($item->mulai)) }} - {{ date('d M Y', strtotime($item->selesai)) }}
                                    </span>
                                </div>

                                <!-- TOTAL BIAYA -->
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-money-bill-wave me-1" style="color: #333;"></i>
                                    <span style="font-weight: bold;">
                                        Rp {{ number_format($item->total_biaya, 0, ',', '.') }}
                                    </span>
                                </div>

                                <!-- LOKASI -->
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-map-marker-alt me-1" style="color: #333;"></i>
                                    <span style="font-weight: bold;">
                                        {{ $item->lokasi }}
                                    </span>
                                </div>

                            </div>
                        </div>

                        <!-- DESKRIPSI -->
                        <div class="entry-content mb-4">
                            <h5 style="font-weight: bold;">Deskripsi</h5>
                            <div class="article-container" style="white-space: pre-line;">
                                {{ $item->desc }}
                            </div>

                            <style>
                                .article-container {
                                    font-size: 1rem;
                                    line-height: 1.7;
                                    color: #333;
                                    text-align: justify;
                                }

                                .article-container p {
                                    margin-bottom: 1rem;
                                }

                                .article-container img {
                                    max-width: 100%;
                                    height: auto;
                                    display: block;
                                    margin: 1rem auto;
                                    border-radius: 8px;
                                }
                            </style>
                        </div>

                        <!-- TOMBOL DAFTAR -->
                        @php
                        $kuotaHabis = $item->sisa_kuota === null || $item->sisa_kuota <= 0;
                            @endphp

                            <div class="mt-4">
                            <a href="{{ $kuotaHabis ? '#' :route('public.scopuscamp.formpendaftaran', ['id' => $item->id, 'token' => $item->token]) }}">
                                <button class="btn btn-info w-100 {{ $kuotaHabis ? 'btn-danger' : 'btn-info' }}" style="{{ $kuotaHabis ? '' : 'background-color: #6495ED; color:white;' }} font-size: 16px;" {{ $kuotaHabis ? 'disabled' : '' }}>
                                    <i class="fa {{ $kuotaHabis ? 'fa-lock' : 'fa-paper-plane' }}"></i>
                                    {{ $kuotaHabis ? ' Pendaftaran Ditutup' : ' Daftar Sekarang' }}
                                </button>
                            </a>
                    </article>
                </div>

                <!--================== END ==================-->
                <div class="col-lg-4">
                    <div class="sidebar">
                        <h3 class="sidebar-title">Jadwal Terbaru</h3>
                        <hr>
                        <div class="sidebar-item recent-posts">
                            <ul class="list-unstyled">
                                @foreach($terbaru as $data)
                                @if($data->status === 'publish')
                                <li class="mb-3 d-flex align-items-start">

                                    <!-- GAMBAR -->
                                    <img src="{{ !empty($data->gambar) ? asset('ScopusCamp/' . basename($data->gambar)) : asset('ScopusCamp/no-image.jpg') }}"
                                        alt="Gambar"
                                        class="rounded-circle me-2"
                                        style="width: 45px; height: 45px; object-fit: cover;">

                                    <!-- INFO JADWAL -->
                                    <div>
                                        <a href="{{ route('public.scopuscamp.Selengkapnya', ['id' => $data->id, 'token' => $data->token]) }}" class="text-decoration-none text-dark">
                                            <strong style="font-size: 18px;">{{ $data->nama }} #{{ $data->nama_ke }}</strong><br>
                                            <small>
                                                <i class="fa fa-calendar-alt"></i>
                                                {{ date('d M Y', strtotime($data->mulai)) }} s/d {{ date('d M Y', strtotime($data->selesai)) }}
                                            </small><br>
                                        </a>
                                    </div>
                                </li>
                                @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

</main>
@stop