@extends('public.layout.header')

@section('title', 'Analisis Bibliometrik | Rumah Scopus')

<style>
    .hero-bg {
        position: relative;
        background: url('/assets/artikel/img/hero-bg.png') no-repeat center center / cover;
        padding: 120px 0 80px;
    }

    .scopus-card {
        border-radius: 16px;
        padding: 20px;
        background-color: white;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border: 1px solid #f1f5f9;
    }

    .scopus-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
    }

    .card-img-wrapper {
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 15px;
        background-color: #f8fafc;
    }

    .img-fluid-custom {
        width: 100%;
        height: auto;
        display: block;
        object-fit: contain;
        /* Gambar tidak terpotong */
    }

    /* --- STYLE TOMBOL TIMBUL & BERBAYANG --- */
    .btn-register {
        font-size: 16px;
        width: 100%;
        border: none;
        padding: 14px;
        border-radius: 0 0 16px 16px;
        font-weight: 700;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-active {
        background: linear-gradient(135deg, #ff3131, #ff914d);
        color: white;
        box-shadow: 0 4px 15px rgba(255, 49, 49, 0.4);
        border-top: 1px solid rgba(255, 255, 255, 0.2);
    }

    .btn-active:hover {
        background: linear-gradient(135deg, #ff4141, #ffa15d);
        box-shadow: 0 6px 20px rgba(255, 49, 49, 0.6);
        transform: scale(1.02);
    }

    .btn-active:active {
        transform: scale(0.98);
        box-shadow: 0 2px 10px rgba(255, 49, 49, 0.3);
    }

    .btn-disabled {
        background-color: #94a3b8;
        color: white;
        cursor: not-allowed;
    }

    .text-gradient {
        background: linear-gradient(to right, #ff3131, #ff914d);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>

@section('konten')
<section id="hero" class="hero-bg d-flex align-items-center">
    <div class="container mb-5">
        <div class="col-lg-12 text-center mb-5" data-aos="fade-down">
            <h1 style="font-size: clamp(32px, 5vw, 48px); font-weight: 800; color: #0f172a; line-height: 1.2;">
                Upgrade <span class="text-gradient">Riset</span> Anda dengan <br>
                Kelas <span class="text-gradient">Analisis Bibliometrik</span>
            </h1>
        </div>

        <div class="row justify-content-center">
            @forelse($categories as $item)
            @php
            $kuotaHabis = $item->sisa_kuota === null || $item->sisa_kuota <= 0;
                $adaDiskon=($item->biaya > $item->total_biaya);
                $hematPersen = $item->biaya > 0 ? round((($item->biaya - $item->total_biaya) / $item->biaya) * 100) : 0;
                @endphp

                <div class="col-md-4 col-lg-3 mb-4 d-flex" data-aos="fade-up">
                    <div class="scopus-card d-flex flex-column w-100">

                        @if($adaDiskon && $hematPersen > 0)
                        <div style="position: absolute; top: 0; right: 0; background: linear-gradient(135deg, #ff3131, #ff6b6b); color: white; padding: 6px 15px; border-radius: 0 16px 0 16px; font-size: 13px; font-weight: 700; z-index: 10;">
                            Hemat {{ $hematPersen }}%
                        </div>
                        @endif

                        <div class="card-img-wrapper">
                            <img src="{{ !empty($item->gambar) ? asset('bibliometrik/' . basename($item->gambar)) : asset('bibliometrik/no-image.jpg') }}"
                                class="img-fluid-custom" alt="Analisis Bibliometrik">
                        </div>

                        <div class="flex-grow-1 text-center">
                            <h5 style="font-weight: 700; color: #1e293b; display: flex; align-items: center; justify-content: center;">
                                {{ strtoupper($item->nama) }} #{{ $item->nama_ke }}
                            </h5>

                            <div class="mb-3">
                                @if($adaDiskon)
                                <small class="text-muted text-decoration-line-through" style="font-size: 15px;">Rp {{ number_format($item->biaya, 0, ',', '.') }}</small>
                                @endif
                                <div class="mt-1">
                                    <span style="font-size: 20px; font-weight: 800; color: #ff3131;">
                                        Rp {{ number_format($item->total_biaya, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-center align-items-center mb-4 text-muted" style="font-size: 13px;">
                                <i class="fas fa-calendar-alt me-2"></i>
                                {{ date('d M', strtotime($item->mulai)) }} - {{ date('d M Y', strtotime($item->selesai)) }}
                            </div>
                        </div>

                        <div style="margin: 0 -20px -20px -20px;">
                            @if($kuotaHabis)
                            <button class="btn-register btn-disabled" disabled>
                                <i class="fa fa-lock"></i> Pendaftaran Ditutup
                            </button>
                            @else
                            <a href="{{ route('public.analisisbibliometrik.Selengkapnya', ['id' => $item->id, 'token' => $item->token]) }}" class="text-decoration-none">
                                <button class="btn-register btn-active">
                                    <i class="fa fa-paper-plane"></i> Daftar Sekarang
                                </button>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-lg-12 d-flex justify-content-center py-5" data-aos="fade-up">
                    <div
                        style="background: #ffffff; border-radius: 16px; padding: 40px 32px; max-width: 720px; width: 100%; text-align: center; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12), 0 20px 40px rgba(0, 0, 0, 0.08);">
                        <h4 style="font-weight: 700; color: #0f172a; font-size: clamp(20px, 3vw, 26px); margin-bottom: 14px;">Kelas Belum Tersedia</h4>
                        <p style="color: #64748b; font-size: clamp(14px, 2.5vw, 16px); max-width: 560px; margin: 0 auto 28px; line-height: 1.6;">Saat ini belum ada kelas Bibliometrik yang dibuka. Silakan cek kembali dalam waktu dekat atau hubungi kami untuk informasi terbaru.</p>
                        <a href="{{ route('blog.contact.kontak') }}"
                            style="display: inline-block; background: linear-gradient(to right, #ff3131, #ff914d); color: #fff; border-radius: 10px; padding: 12px 30px; font-weight: 600; font-size: 15px; text-decoration: none; transition: transform .2s ease, box-shadow .2s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.2)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Hubungi Admin</a>
                    </div>
                </div>
                @endforelse
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if (session()->has('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Data Berhasil Terkirim! Mohon tunggu verifikasi 1x24 jam.'
    });
</script>
@endif
@stop