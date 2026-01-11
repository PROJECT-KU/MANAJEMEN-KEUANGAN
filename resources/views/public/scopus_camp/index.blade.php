@extends('public.layout.header')

@section('title')
Scopus Camp | Rumah Scopus
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
<section id="hero" class="hero-bg d-flex align-items-center">
    <div class="container mb-5">

        <div class="col-lg-12 d-flex flex-column justify-content-center align-items-center text-center"
            style="font-family:'Poppins','Inter',sans-serif;">

            <h1 data-aos="fade-down"
                style="font-size:48px; font-weight:700; line-height:1.3; color:#0f172a; max-width:900px;">
                Tingkatkan Peluang Tembus
                <span style="background: linear-gradient(to right, #ff3131, #ff914d); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Scopus</span>
                Bersama
                <span style="background: linear-gradient(to right, #ff3131, #ff914d); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Scopus Camp</span>
            </h1>
        </div>

        <div class="row d-flex justify-content-center flex-wrap">

            <!--================== JIKA CLASS ADA ==================-->
            @forelse($categories as $item)
            <div class="col-md-3 mb-2 d-flex" data-aos="fade-up" style="margin: 10px; text-align: center; min-height: 100%;">
                <div class="inner-card d-flex flex-column justify-content-between"
                    style="border-radius: 10px; padding: 20px; padding-bottom: 60px; background-color: white; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); transition: transform 0.3s ease, box-shadow 0.3s ease; position: relative; width: 100%;">

                    <!-- GAMBAR -->
                    <div style="border-radius: 5px">
                        <img id="clickableImage"
                            src="{{ !empty($item->gambar) ? asset('ScopusCamp/' . basename($item->gambar)) : asset('ScopusCamp/no-image.jpg') }}"
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

                    <!-- TOMBOL HARGA -->
                    @php
                    $kuotaHabis = $item->sisa_kuota === null || $item->sisa_kuota <= 0;
                        @endphp

                        <a href="{{ $kuotaHabis ? '#' : route('public.scopuscamp.Selengkapnya', ['id' => $item->id, 'token' => $item->token]) }}">
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
            <!--================== END ==================-->

            <!--================== JIKA CLASS TIDAK ADA ==================-->
            @empty
            <div class="col-lg-12 d-flex justify-content-center py-5" data-aos="fade-up">
                <div
                    style="background: #ffffff; border-radius: 16px; padding: 40px 32px; max-width: 720px; width: 100%; text-align: center; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12), 0 20px 40px rgba(0, 0, 0, 0.08);">

                    <!-- TITLE -->
                    <h4 style="font-weight: 700; color: #0f172a; font-size: clamp(20px, 3vw, 26px); margin-bottom: 14px;">
                        Kelas Scopus Camp Belum Tersedia
                    </h4>

                    <!-- DESCRIPTION -->
                    <p style="color: #64748b; font-size: clamp(14px, 2.5vw, 16px); max-width: 560px; margin: 0 auto 28px; line-height: 1.6;">
                        Saat ini belum ada kelas Scopus Camp yang dibuka.
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
            html: 'Data Pendaftaran Scopus Camp Berhasil Terkirim!<br><br>Pembayaran Anda akan kami verifikasi terlebih dahulu. Mohon menunggu maksimal 1x24 jam.',
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
            text: 'Data Pendaftaran Scopus Camp Gagal Terkirim',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif
<!--================== END ==================-->
@stop