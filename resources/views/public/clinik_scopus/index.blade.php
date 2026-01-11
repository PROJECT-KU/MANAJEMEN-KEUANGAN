@extends('public.layout.header')

@section('title')
Klinik Scopus | Rumah Scopus
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
                Punya Kendala
                <span style="background: linear-gradient(to right, #ff3131, #ff914d); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    Scopus
                </span>
                ? Konsultasikan Langsung ke
                <span style="background: linear-gradient(to right, #ff3131, #ff914d); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    Ahlinya di Klinik Scopus
                </span>
            </h1>
        </div>

        <div class="row d-flex justify-content-center flex-wrap">

            <!--================== JIKA CLASS ADA ==================-->
            @forelse($categories as $item)
            <div class="col-md-3 mb-2 d-flex" data-aos="fade-up" style="margin: 10px;">
                <div class="inner-card d-flex flex-column"
                    style="border-radius: 10px; padding: 20px; background-color: white; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); transition: transform 0.3s ease, box-shadow 0.3s ease; position: relative; width: 100%;">

                    <!-- GAMBAR -->
                    <div style="border-radius: 5px">
                        <img src="{{ !empty($item->foto) ? asset('ClinikScopusTrainer/' . basename($item->foto)) : asset('ClinikScopusTrainer/no-image.jpg') }}" style="width:100%; height:auto;" alt="Current Image">
                    </div>

                    <!-- KONTEN -->
                    <div class="mt-3 text-center mb-4">
                        <h4 style="font-weight: bold;">
                            {{ $item->full_name }}
                        </h4>

                        <h4 style="color: #bbb6b6ff; font-size: 14px;">
                            {{ $item->jobdesk }}
                        </h4>
                    </div>

                    <!-- BUTTON (DIPAKSA KE BAWAH) -->
                    <a href="{{ route('public.clinikscopus.sesi', $item->id) }}" class="mt-auto text-center btn-konsultasi" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: linear-gradient(to right, #ff3131, #ff914d); color: #fff; border-radius: 10px; padding: 12px 30px; font-weight: 600; font-size: 15px; text-decoration: none; transition: transform .2s ease, box-shadow .2s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.2)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        <i class="fas fa-comments"></i>
                        Konsultasikan Sekarang
                    </a>

                </div>
            </div>

            <!--================== END ==================-->

            <!--================== JIKA CLASS TIDAK ADA ==================-->
            @empty
            <div class="col-lg-12 d-flex justify-content-center py-5" data-aos="fade-up">
                <div
                    style="background: #ffffff; border-radius: 16px; padding: 40px 32px; max-width: 720px; width: 100%; text-align: center; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12), 0 20px 40px rgba(0, 0, 0, 0.08);">

                    <!-- TITLE -->
                    <h4 style="font-weight: 700; color: #0f172a; font-size: clamp(20px, 3vw, 26px); margin-bottom: 14px;">
                        Kelas Klinik Scopus Belum Tersedia
                    </h4>

                    <!-- DESCRIPTION -->
                    <p style="color: #64748b; font-size: clamp(14px, 2.5vw, 16px); max-width: 560px; margin: 0 auto 28px; line-height: 1.6;">
                        Saat ini belum ada kelas Klinik Scopus yang dibuka.
                        Silakan cek kembali dalam waktu dekat atau hubungi kami untuk informasi terbaru.
                    </p>

                    <!-- CTA BUTTON -->
                    <a href="{{ route('blog.contact.kontak') }}"
                        style="display: inline-block; background: linear-gradient(to right, #ff3131, #ff914d); color: #fff; border-radius: 10px; padding: 12px 30px; font-weight: 600; font-size: 15px; text-decoration: none; transition: transform .2s ease, box-shadow .2s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.2)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        Hubungi Admin
                    </a>
                </div>
            </div>
            <!--================== END ==================-->
            @endempty

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