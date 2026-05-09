@extends('public.layout.header')

@section('title')
Analisis Bibliometrik Selengkapnya | Rumah Scopus
@stop

<style>
    /* =========================================
       BACKGROUND & GLASSMORPHISM BASE
       ========================================= */
    .blog-section {
        position: relative;
        background-color: #f8fafc;
        padding: 60px 0;
        overflow: hidden;
        z-index: 1;
    }

    /* Latar belakang abstrak agar efek kaca terlihat nyata (Seragam dgn Scopus Camp) */
    .blog-section::before {
        content: '';
        position: absolute;
        top: -10%;
        left: -10%;
        width: 50%;
        height: 50%;
        background: radial-gradient(circle, rgba(255, 145, 77, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
        z-index: -1;
    }

    .blog-section::after {
        content: '';
        position: absolute;
        bottom: -10%;
        right: -10%;
        width: 50%;
        height: 50%;
        background: radial-gradient(circle, rgba(100, 149, 237, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
        z-index: -1;
    }

    /* Efek Kaca (Glassmorphism) Utama */
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 24px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
        padding: 30px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .glass-panel:hover {
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.08);
    }

    /* =========================================
       CSS GRID LAYOUT
       ========================================= */
    /* Grid Utama: Pembagian Konten Kiri (Artikel) dan Kanan (Sidebar) */
    .modern-grid-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        align-items: start;
    }

    /* Grid Info Meta (Tanggal, Harga, Lokasi) */
    .meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
        margin: 25px 0;
    }

    /* =========================================
       KOMPONEN DETAIL
       ========================================= */
    /* Cover Image Wrapper (Untuk menampung Carousel) */
    .glass-image-wrapper {
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 25px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
    }

    .glass-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .glass-image-wrapper:hover img {
        transform: scale(1.03);
    }

    /* Teks & Typography */
    .entry-title {
        font-size: 32px;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.3;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #1e293b 0%, #ff3131 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Meta Cards (Kotak Info) */
    .meta-card {
        background: rgba(255, 255, 255, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 14px;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        transition: all 0.3s ease;
    }

    .meta-card:hover {
        background: rgba(255, 255, 255, 0.9);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
    }

    .meta-icon {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #ff914d 0%, #ff3131 100%);
        color: white;
        font-size: 16px;
    }

    .meta-icon.blue {
        background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
    }

    .meta-icon.green {
        background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
    }

    .meta-text {
        display: flex;
        flex-direction: column;
    }

    .meta-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        font-weight: 700;
    }

    .meta-value {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
    }

    /* Deskripsi Artikel */
    .article-container {
        font-size: 16px;
        line-height: 1.8;
        color: #475569;
        text-align: justify;
    }

    /* Tombol Daftar Modern */
    .btn-glossy {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 16px;
        border-radius: 16px;
        font-size: 16px;
        font-weight: 700;
        letter-spacing: 0.5px;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-transform: uppercase;
    }

    .btn-glossy.active {
        background: linear-gradient(135deg, #6495ED 0%, #3b82f6 100%);
        color: white;
        box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
    }

    .btn-glossy.active:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(59, 130, 246, 0.4);
    }

    .btn-glossy.disabled {
        background: #f87171;
        color: white;
        cursor: not-allowed;
        box-shadow: none;
    }

    /* Sidebar Items */
    .sidebar-title {
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 20px;
        position: relative;
        padding-bottom: 10px;
    }

    .sidebar-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: #ff3131;
        border-radius: 2px;
    }

    .sidebar-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .sidebar-item-glass {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.8);
        transition: all 0.3s ease;
        text-decoration: none !important;
    }

    .sidebar-item-glass:hover {
        background: rgba(255, 255, 255, 0.9);
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .sidebar-img {
        width: 60px;
        height: 60px;
        border-radius: 14px;
        object-fit: cover;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar-info strong {
        font-size: 15px;
        color: #0f172a;
        line-height: 1.2;
        display: block;
        margin-bottom: 5px;
    }

    .sidebar-info small {
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
    }

    /* Responsive Grid */
    @media (max-width: 991px) {
        .modern-grid-layout {
            grid-template-columns: 1fr;
        }
    }
</style>

@section('konten')
@csrf
<main id="main">

    <section class="breadcrumbs" style="background: linear-gradient(135deg, #ff3131 0%, #ff914d 100%); margin-top: 70px; border-bottom-left-radius: 24px; border-bottom-right-radius: 24px; box-shadow: 0 10px 30px rgba(255, 49, 49, 0.2);">
        <div class="container">
            <h2 style="color: white; font-weight: 800; letter-spacing: -0.5px;"> {{ $item->nama }} #{{ $item->nama_ke }}</h2>
        </div>
    </section>
    <section id="blog" class="blog-section">
        <div class="container" data-aos="fade-up">

            <div class="modern-grid-layout">

                <div class="main-article">
                    <article class="glass-panel">

                        <div class="glass-image-wrapper" style="aspect-ratio: 16/9;">
                            <div id="carouselExampleIndicators" class="carousel slide h-100" data-bs-ride="carousel">
                                <div class="carousel-inner h-100">
                                    <div class="carousel-item active h-100">
                                        <img src="{{ asset('assets/img/public/cover2.png') }}" class="d-block w-100 h-100" alt="Cover 1" style="object-fit: cover;">
                                    </div>
                                    <div class="carousel-item h-100">
                                        <img src="{{ asset('assets/img/public/cover1.png') }}" class="d-block w-100 h-100" alt="Cover 2" style="object-fit: cover;">
                                    </div>
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>

                        <h2 class="entry-title text-center">
                            {{ $item->nama }} #{{ $item->nama_ke }}
                        </h2>

                        <div class="meta-grid">
                            <div class="meta-card">
                                <div class="meta-icon blue"><i class="fa fa-calendar-alt"></i></div>
                                <div class="meta-text">
                                    <span class="meta-label">Jadwal Pelaksanaan</span>
                                    <span class="meta-value">{{ date('d M Y', strtotime($item->mulai)) }} - {{ date('d M Y', strtotime($item->selesai)) }}</span>
                                </div>
                            </div>

                            <div class="meta-card">
                                <div class="meta-icon green"><i class="fa fa-money-bill-wave"></i></div>
                                <div class="meta-text">
                                    <span class="meta-label">Total Investasi</span>
                                    <span class="meta-value">Rp {{ number_format($item->total_biaya ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="meta-card">
                                <div class="meta-icon"><i class="fa fa-map-marker-alt"></i></div>
                                <div class="meta-text">
                                    <span class="meta-label">Lokasi</span>
                                    <span class="meta-value">{{ $item->lokasi ?? 'Online' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 mt-5">
                            <h5 style="font-weight: 800; color: #1e293b; margin-bottom: 15px; display:flex; align-items:center; gap:8px;">
                                <i class="fas fa-info-circle text-danger"></i> Deskripsi Kegiatan
                            </h5>
                            <div class="article-container" style="white-space: pre-line;">
                                {{ $item->desc }}
                            </div>
                        </div>

                        @php
                        $kuotaHabis = $item->sisa_kuota === null || $item->sisa_kuota <= 0;
                            @endphp

                            <div class="mt-5">
                            <a href="{{ $kuotaHabis ? '#' : route('public.analisisbibliometrik.formpendaftaran', ['id' => $item->id, 'token' => $item->token]) }}" style="text-decoration: none;">
                                <button class="btn-glossy {{ $kuotaHabis ? 'disabled' : 'active' }}" {{ $kuotaHabis ? 'disabled' : '' }}>
                                    <i class="fa {{ $kuotaHabis ? 'fa-lock' : 'fa-paper-plane' }}"></i>
                                    {{ $kuotaHabis ? 'Pendaftaran Ditutup' : 'Daftar Sekarang' }}
                                </button>
                            </a>
                </div>
                </article>
            </div>

            <div class="sidebar-wrapper">
                <div class="glass-panel" style="position: sticky; top: 100px;">
                    <h3 class="sidebar-title">Jadwal Terbaru</h3>

                    <div class="sidebar-list mt-4">
                        @foreach($terbaru as $data)
                        @if($data->status === 'active') <a href="{{ route('public.analisisbibliometrik.Selengkapnya', ['id' => $data->id, 'token' => $data->token]) }}" class="sidebar-item-glass">
                            <img src="{{ !empty($data->gambar) ? asset('bibliometrik/' . basename($data->gambar)) : asset('bibliometrik/no-image.jpg') }}" alt="Gambar" class="sidebar-img">

                            <div class="sidebar-info">
                                <strong>{{ $data->nama }} #{{ $data->nama_ke }}</strong>
                                <small><i class="fa fa-calendar-alt me-1"></i> {{ date('d M', strtotime($data->mulai)) }} - {{ date('d M Y', strtotime($data->selesai)) }}</small>
                            </div>
                        </a>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
        </div>
    </section>

</main>
@stop