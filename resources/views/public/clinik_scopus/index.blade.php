@extends('public.layout.header')

@section('title')
Clinik Scopus | Rumah Scopus
@stop


@section('konten')
<!-- ======= Hero Section ======= -->
<section id="hero" class="hero d-flex align-items-center">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 d-flex flex-column justify-content-center">
                <h1 data-aos="fade-up">Clinik Scopus</h1>
                <p data-aos="fade-up" data-aos-delay="400" style="font-size: 15px;">
                    Clinic Scopus adalah layanan pendampingan online berbasis chat antara peserta dan Trainer Rumah Scopus.
                    Setiap sesi berdurasi 50 menit, difokuskan khusus untuk membantu proses penulisan artikel ilmiah,
                    mulai dari perbaikan struktur, tata tulis, hingga penguatan argumentasi agar sesuai standar jurnal bereputasi.
                    Semua komunikasi dilakukan melalui chat sehingga peserta dapat bertanya, berdiskusi, serta mendapatkan arahan
                    penulisan secara jelas dan terfokus. </p>
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