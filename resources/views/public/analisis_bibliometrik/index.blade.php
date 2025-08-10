@extends('public.layout.header')

@section('title')
Analisis Bibliometrik | Rumah Scopus
@stop


@section('konten')
<!-- ======= Hero Section ======= -->
<section id="hero" class="hero d-flex align-items-center">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 d-flex flex-column justify-content-center">
                <h1 data-aos="fade-up">Analisis Bibliometrik</h1>
                <h2 data-aos="fade-up" data-aos-delay="400">
                    Layanan Analisis Bibliometrik ini diselenggarakan secara online melalui Zoom sebanyak 8 kali pertemuan,
                    dengan durasi 90 menit per sesi. Pendampingan dilakukan bersama Trainer Rumah Scopus,
                    dan dirancang untuk membantu peserta memahami serta menerapkan teknik analisis bibliometrik secara efektif.
                </h2>
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

<div class="container mt-5 mb-5">
    <div class="main-card"
        style="background: linear-gradient(to right, #ff3131, #ff914d); border-radius: 15px; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); overflow: hidden; margin-bottom: 20px;"
        data-aos="fade-right">
        <div class="card-body" style="padding: 30px; border-radius: 15px;">
            <div class="row d-flex justify-content-center flex-wrap">
                @foreach($categories as $item)
                <div class="col-md-3 mb-2 d-flex" data-aos="fade-up" style="margin: 10px; text-align: center; min-height: 100%;">
                    <div class="inner-card d-flex flex-column justify-content-between"
                        style="border-radius: 10px; padding: 20px; padding-bottom: 60px; background-color: white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); position: relative; width: 100%;">

                        {{-- Gambar --}}
                        <div style="border-radius: 5px">
                            <img id="clickableImage"
                                src="{{ !empty($item->gambar) ? asset('bibliometrik/' . basename($item->gambar)) : asset('bibliometrik/no-image.jpg') }}"
                                style="max-width:100%; height:auto; cursor: zoom-in;"
                                alt="Current Image">
                        </div>

                        {{-- Konten Tengah --}}
                        <div class="mt-3 flex-grow-1 d-flex flex-column justify-content-between">
                            <div>
                                {{-- Judul --}}
                                <h6 style="font-weight: bold; font-size:18px;">
                                    {{ strtoupper($item->nama) }} #{{ strtoupper($item->nama_ke) }}
                                </h6>

                                {{-- Baris Tanggal dan Kuota --}}
                                <div class="d-flex justify-content-between align-items-center mt-2" style="font-size: 12px;">
                                    <span style="display: flex; align-items: center;">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ date('d M Y', strtotime($item->mulai)) }} - {{ date('d M Y', strtotime($item->selesai)) }}
                                    </span>
                                    <span style="display: flex; align-items: center;">
                                        <i class="fas fa-users me-1"></i>
                                        Sisa: {{ $item->sisa_kuota ?? 0 }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Daftar --}}
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


                            {{-- Label Harga --}}
                            <div class="label"
                                style="position: absolute; top: 0px; right: 0px; background-color: #ff3131; color: white; padding: 5px; border-radius: 5px; font-size: 15px;">
                                Rp. {{ number_format($item->biaya, 0, ',', '.') }}
                            </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

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