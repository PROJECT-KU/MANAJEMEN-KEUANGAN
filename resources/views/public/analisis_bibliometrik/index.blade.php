@extends('public.layout.header')

@section('title')
Analisis Bibliometrik | Rumah Scopus
@stop

<!--================== BACKGOUND IMAGE ==================-->
<style>
    .hero-bg {
        position: relative;
        background-image: url('/assets/artikel/img/hero-bg.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        padding-top: 120px;
        padding-bottom: 80px;
    }

    .hero-bg::before {
        content: "";
        position: absolute;
        inset: 0;
    }

    .hero-bg>.container {
        position: relative;
        z-index: 2;
    }
</style>
<!--================== END ==================-->

@section('konten')
<!-- ======= Hero Section ======= -->
<section id="hero" class="hero-bg d-flex align-items-center">
    <div class="container mb-5">

        <div class="col-lg-12 d-flex flex-column justify-content-center align-items-center text-center"
            style="font-family:'Poppins','Inter',sans-serif;">
            <h1 data-aos="fade-down" style="font-size:48px; font-weight:700; line-height:1.3; color:#0f172a; max-width:900px;">
                Upgrade
                <span style="background: linear-gradient(to right, #ff3131, #ff914d); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Riset</span>
                Anda dengan
                <span style="background: linear-gradient(to right, #ff3131, #ff914d); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Kelas Analisis Bibliometrik</span>
            </h1>
        </div>

        <div class="row d-flex justify-content-center flex-wrap">
            @foreach($categories as $item)
            <div class="col-md-3 mb-2 d-flex" data-aos="fade-up" style="margin: 10px; text-align: center; min-height: 100%;">
                <div class="inner-card d-flex flex-column justify-content-between"
                    style="border-radius: 10px; padding: 20px; padding-bottom: 60px; background-color: white; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); transition: transform 0.3s ease, box-shadow 0.3s ease; position: relative; width: 100%;">

                    <!-- GAMBAR -->
                    <div style="border-radius: 5px">
                        <img id="clickableImage"
                            src="{{ !empty($item->gambar) ? asset('bibliometrik/' . basename($item->gambar)) : asset('bibliometrik/no-image.jpg') }}"
                            style="max-width:100%; height:auto;"
                            alt="Current Image">
                    </div>

                    <!-- KONTEN TENGAH -->
                    <div class="mt-3 flex-grow-1 d-flex flex-column justify-content-between">
                        <div>
                            <!-- Judul -->
                            <h4 style="font-weight: bold;">
                                {{ strtoupper($item->nama) }} #{{ strtoupper($item->nama_ke) }}
                            </h4>

                            <h4 style="color:#ff3131; text-decoration: line-through;">
                                Rp {{ number_format($item->biaya, 0, ',', '.') }}
                            </h4>

                            <span
                                style="font-size: clamp(14px, 2.5vw, 22px); font-weight: 700; padding: 6px 10px; border-radius: 6px; color: #fff; background: linear-gradient(to right, #ff3131, #ff914d); display: inline-block; white-space: nowrap;">
                                Rp {{ number_format($item->total_biaya, 0, ',', '.') }}
                            </span>

                            <!-- Baris Tanggal -->
                            <div class="d-flex justify-content-between align-items-center mt-2" style="font-size: 12px;">
                                <span style="display: flex; align-items: center;">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ date('d M Y', strtotime($item->mulai)) }} - {{ date('d M Y', strtotime($item->selesai)) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Daftar -->
                    @php
                    $kuotaHabis = $item->sisa_kuota === null || $item->sisa_kuota <= 0;
                        @endphp

                        <a href="{{ $kuotaHabis ? '#' : route('public.analisisbibliometrik.Selengkapnya', ['id' => $item->id, 'token' => $item->token]) }}">
                        <button class="btn {{ $kuotaHabis ? 'btn-danger' : 'btn-info' }}"
                            style="{{ $kuotaHabis ? '' : 'background-color: #6495ED; color:white;' }} font-size: 16px; width: 100%; position: absolute; bottom: 0; left: 0;"
                            {{ $kuotaHabis ? 'disabled' : '' }}>
                            <i class="fa {{ $kuotaHabis ? 'fa-lock' : 'fa-paper-plane' }}"></i>
                            {{ $kuotaHabis ? ' Pendaftaran Ditutup' : ' Daftar Sekarang' }}
                        </button>
                        </a>


                        <!-- LABEL DISKON -->
                        @php
                        $hematPersen = $item->biaya > 0
                        ? round((($item->biaya - $item->total_biaya) / $item->biaya) * 100)
                        : 0;
                        @endphp

                        <div class="label"
                            style="position: absolute; top: 0px; right: 0px; background: linear-gradient(135deg, #ff3131, #ff6b6b); color: white; padding: 6px 10px; border-radius: 0 10px 0 10px; font-size: 14px; font-weight: bold; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                            Hemat {{ $hematPersen }}%
                        </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
<!--================== RELOAD KETIKA DATA SUKSES ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if (session()->has('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            html: 'Data Pendaftaran Analisis Bibliometrik Berhasil Terkirim!<br><br>Pembayaran Anda akan kami verifikasi terlebih dahulu. Mohon menunggu maksimal 1x24 jam.',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif

@if (session()->has('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Data Pendaftaran Analisis Bibliometrik Gagal Terkirim',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif
<!--================== END ==================-->
@stop