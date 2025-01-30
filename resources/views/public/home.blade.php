@extends('public.layout.header')

@section('title')
Beranda | Rumah Scopus
@stop

<style>
    .video-card {
        padding: 20px;
        text-align: center;
        position: relative;
        /* Add this to make the video responsive */
    }

    .video-card video {
        width: 40%;
        height: 40%;
        object-fit: cover;
        /* Make the video cover the entire card */
    }

    .video-controls {
        margin-top: 20px;
    }
</style>

@section('konten')
<!-- ======= Hero Section ======= -->
<section id="hero" class="hero d-flex align-items-center">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 d-flex flex-column justify-content-center">
                <h1 data-aos="fade-up">Rumah Scopus: Pelatihan Jurnal Bereputasi untuk Mewujudkan Impian Publikasi Internasional</h1>
                <h2 data-aos="fade-up" data-aos-delay="400">Jelajahi Hutan Ilmu Pengetahuan Bersama Kami, Temukan Jalan Mudah Menuju Scopus dengan Pelatihan Terbaik dan Terpercaya!</h2>
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
                <div class="card-with-gradient">
                    <img src="{{ asset('assets/img/header1.png') }}" style="width: 100%;" class="img-fluid" alt="">
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container mt-5 mb-5">
    <div class="main-card" style="background: linear-gradient(to right, #ff3131, #ff914d); border-radius: 15px; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); overflow: hidden;" data-aos="fade-right">
        <h1 class="text-center mt-3" style="font-weight: bold; color: white;">Kenapa Memilih Kami ?</h1>
        <div class="card-body" style="padding: 30px; border-radius: 15px;">
            <div class="row">
                <div class="col-md-3 mb-2" data-aos="fade-up">
                    <div class="inner-card" style="border-radius: 10px; padding: 20px; background-color: white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <div class="d-flex align-items-center">
                            <div style="border-radius: 5px; background: linear-gradient(to right, #ff3131, #ff914d); padding: 10px; display: inline-block;">
                                <i class="fas fa-user-graduate" style="font-size: 24px; color: white;"></i>
                            </div>
                            <div style="margin-left: 10px;">
                                <h5 style="font-weight: bold;">Trainer Profesional</h5>
                                <p>Di dampingi oleh Trainer yang profesional</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2" data-aos="fade-down">
                    <div class="inner-card" style="border-radius: 10px; padding: 20px; background-color:white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <div class="d-flex align-items-center">
                            <div style="border-radius: 5px; background: linear-gradient(to right, #ff3131, #ff914d); padding: 10px; display: inline-block;">
                                <i class="fas fa-user" style="font-size: 24px; color: white;"></i>
                            </div>
                            <div style="margin-left: 10px;">
                                <h5 style="font-weight: bold;">Pendampingan</h5>
                                <p>Di dampingi hingga artikel Terindeks Scopus</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2" data-aos="fade-up">
                    <div class="inner-card" style="border-radius: 10px; padding: 20px; background-color:white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <div class="d-flex align-items-center">
                            <div style="border-radius: 5px; background: linear-gradient(to right, #ff3131, #ff914d); padding: 10px; display: inline-block;">
                                <i class="fas fa-chalkboard-teacher" style="font-size: 24px; color: white;"></i>
                            </div>
                            <div style="margin-left: 10px;">
                                <h5 style="font-weight: bold;">Learning By Doing</h5>
                                <p>Pembelajaran secara langsung dengan praktik</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2" data-aos="fade-down">
                    <div class="inner-card" style="border-radius: 10px; padding: 20px; background-color:white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <div class="d-flex align-items-center">
                            <div style="border-radius: 5px; background: linear-gradient(to right, #ff3131, #ff914d); padding: 10px; display: inline-block;">
                                <i class="fas fa-newspaper" style="font-size: 24px; color: white;"></i>
                            </div>
                            <div style="margin-left: 10px;">
                                <h5 style="font-weight: bold;">Terbukti</h5>
                                <p>Sudah terbukti ratusan kali terindeks scopus</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-5 mb-5 d-flex justify-content-center">
    <div class="main-card">
        <h1 class="text-center mt-3" style="font-weight: bold; position: relative;">
            Layanan Kami
            <span style="
                display: block; 
                width: 15%; 
                height: 8px; 
                background: linear-gradient(to right, #ff3131, #ff914d); 
                position: absolute; 
                bottom: -15px; 
                left: 50%; 
                transform: translateX(-50%); 
                border-radius: 50px; 
                opacity: 0.8; 
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
            </span>
        </h1>
        <div class="card-body" style="padding: 30px; border-radius: 15px;">
            <div class="row justify-content-center">
                <div class="col-md-3 mb-2" data-aos="fade-right">
                    <div class="inner-card d-flex flex-column" style="border-radius: 10px; padding: 20px; background-color: white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); height: 100%;">
                        <div>
                            <img src="{{ asset('assets/img/public/camp.jpg') }}" style="width: 100%; height: 200px; object-fit: cover; border-radius: 10px;" class="img-fluid" alt="Scopus Camp">
                        </div>
                        <div style="text-align: center; margin-top: 10px;">
                            <h5 style="font-weight: bold;">Scopus Camp</h5>
                            <p style="text-align: justify;">
                                Pendampingan secara langsung atau offline <b>selama 3 hari</b>, dengan di dampingi oleh <b>Trainer dari Rumah Scopus</b>. Akan mendapatkan pendampingan kembali <b>selama 1 Tahun.</b>
                            </p>
                        </div>
                        <div class="mt-auto" style="text-align: center;">
                            <a href="https://rumahscopus.com/event/">
                                <button class="btn" style="width: 100%; color: white; background: linear-gradient(to right, #ff3131, #ff914d); border: none; padding: 10px; border-radius: 5px;">Pesan Sekarang</button>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2" data-aos="fade-down">
                    <div class="inner-card d-flex flex-column" style="border-radius: 10px; padding: 20px; background-color: white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); height: 100%;">
                        <div>
                            <img src="{{ asset('assets/img/public/onlinetraining.jpeg') }}" style="width: 100%; height: 200px; object-fit: cover; border-radius: 10px;" class="img-fluid" alt="Online Training">
                        </div>
                        <div style="text-align: center; margin-top: 10px;">
                            <h5 style="font-weight: bold;">Online Training</h5>
                            <p style="text-align: justify;">
                                Pendampingan secara online <b>selama 8 kali pertemuan</b>, dengan di dampingi oleh <b>Trainer dari Rumah Scopus</b>. Akan mendapatkan pendampingan kembali <b>selama 3 bulan.</b>
                            </p>
                        </div>
                        <div class="mt-auto" style="text-align: center;">
                            <a href="https://rumahscopus.com/courses/online-class/">
                                <button class="btn" style="width: 100%; color: white; background: linear-gradient(to right, #ff3131, #ff914d); border: none; padding: 10px; border-radius: 5px;">Pesan Sekarang</button>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2" data-aos="fade-left">
                    <div class="inner-card d-flex flex-column" style="border-radius: 10px; padding: 20px; background-color: white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); height: 100%;">
                        <div>
                            <img src="{{ asset('assets/img/public/scopuskafe.jpg') }}" style="width: 100%; height: 200px; object-fit: cover; border-radius: 10px;" class="img-fluid" alt="Scopus Kafe">
                        </div>
                        <div style="text-align: center; margin-top: 10px;">
                            <h5 style="font-weight: bold;">Scopus Kafe</h5>
                            <p style="text-align: justify;">
                                Pendampingan secara langsung atau offline <b>dengan private selama 5 jam</b>, dengan di dampingi oleh <b>Trainer dari Rumah Scopus</b>.
                            </p>
                        </div>
                        <div class="mt-auto" style="text-align: center;">
                            <a href="{{ url('/Scopus-Kafe') }}">
                                <button class="btn" style="width: 100%; color: white; background: linear-gradient(to right, #ff3131, #ff914d); border: none; padding: 10px; border-radius: 5px;">Pesan Sekarang</button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-5 mb-5">
    <div style="background: linear-gradient(to right, #ff3131, #ff914d); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); overflow: hidden; padding: 30px;">
        <h1 class="text-center mt-3" style="font-weight: bold; color: white;">Sudah Dipercaya Ribuan Orang</h1>
        <div class="card-body d-flex justify-content-center" style="padding: 30px; border-radius: 15px;">
            <div class="row justify-content-center">
                <!-- First Card -->
                <div class="col-md-3 mb-2" data-aos="fade-up">
                    <div class="inner-card" style="border-radius: 10px; padding: 20px; background-color: white; border: 2px dashed #ff914d; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <div class="d-flex align-items-center">
                            <div style="border-radius: 5px; background: linear-gradient(to right, #ff3131, #ff914d); padding: 10px; display: inline-block;">
                                <i class="fas fa-user-graduate" style="font-size: 24px; color: white;"></i>
                            </div>
                            <div style="margin-left: 10px;">
                                <h5 id="counter1" style="font-weight: bold; font-size:50px">0</h5>
                            </div>
                        </div>
                        <p>sudah 5000 lebih alumni yang telah mengikuti di Rumah Scopus</p>
                    </div>
                </div>
                <!-- Second Card -->
                <div class="col-md-3 mb-2" data-aos="fade-up">
                    <div class="inner-card" style="border-radius: 10px; padding: 20px; background-color: white; border: 2px dashed #ff914d; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <div class="d-flex align-items-center">
                            <div style="border-radius: 5px; background: linear-gradient(to right, #ff3131, #ff914d); padding: 10px; display: inline-block;">
                                <i class="bi bi-inboxes-fill" style="font-size: 24px; color: white;"></i>
                            </div>
                            <div style="margin-left: 10px;">
                                <h5 id="counter2" style="font-weight: bold; font-size:50px">0</h5>
                            </div>
                        </div>
                        <p>Sudah 2000 lebih orang yang sudah submit ke jurnal internasional</p>
                    </div>
                </div>
                <!-- Third Card -->
                <div class="col-md-3 mb-2" data-aos="fade-up">
                    <div class="inner-card" style="border-radius: 10px; padding: 20px; background-color: white; border: 2px dashed #ff914d; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <div class="d-flex align-items-center">
                            <div style="border-radius: 5px; background: linear-gradient(to right, #ff3131, #ff914d); padding: 10px; display: inline-block;">
                                <i class="bi bi-file-earmark-check-fill" style="font-size: 24px; color: white;"></i>
                            </div>
                            <div style="margin-left: 10px;">
                                <h5 id="counter3" style="font-weight: bold; font-size:50px">0</h5>
                            </div>
                        </div>
                        <p>sudah 1000 lebih orang yang publish terindeks Scopus</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<section id="contact" class="contact mb-5 mt-5">
    <div class="container">
        <h1 class="text-center mt-3" style="font-weight: bold; position: relative;">
            Apa Yang Akan Anda Pelajari
            <span style="
                display: block; 
                width: 15%; 
                height: 8px; 
                background: linear-gradient(to right, #ff3131, #ff914d); 
                position: absolute; 
                bottom: -15px; 
                left: 50%; 
                transform: translateX(-50%); 
                border-radius: 50px; 
                opacity: 0.8; 
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
            </span>
        </h1>

        <div class="row gy-4 mt-5">
            <div class="col-lg-6">
                <div class="row gy-4">
                    <div class="col-md-6" data-aos="fade-right">
                        <div class="info-box text-center">
                            <i class="bi bi-file-earmark-richtext" style="font-size: 50px; color: #347ccb;"></i>
                            <h3 style="font-weight: bold; margin-top: 10px; font-size:30px;">Penulisan Digital</h3>
                        </div>
                    </div>

                    <div class="col-md-6" data-aos="fade-left">
                        <div class="info-box text-center">
                            <i class="fas fa-brain mt-4" style="font-size: 50px; color: #6495ED;"></i>
                            <h3 style="font-weight: bold; margin-top: 35px; font-size:30px;">Berlatih AI</h3>
                        </div>
                    </div>

                    <div class="col-md-6" data-aos="fade-up">
                        <div class="info-box text-center">
                            <i class="fas fa-file-alt mt-4" style="font-size: 50px; color: #2F4F4F;"></i>
                            <h3 style="font-weight: bold; margin-top: 35px; font-size:30px;">Paper Anatomy</h3>
                        </div>
                    </div>
                    <div class="col-md-6" data-aos="fade-down">
                        <div class="info-box text-center">
                            <i class="fas fa-link mt-4" style="font-size: 50px; color: #5F9EA0;"></i>
                            <h3 style="font-weight: bold; margin-top: 35px; font-size:30px;">Refrensi Tebaik</h3>
                        </div>
                    </div>

                    <div class="col-md-6" data-aos="fade-right">
                        <div class="info-box text-center">
                            <i class="fas fa-chalkboard-teacher mt-4" style="font-size: 50px; color: #DAA520;"></i>
                            <h3 style="font-weight: bold; margin-top: 35px; font-size:30px;">Pendampingan</h3>
                        </div>
                    </div>
                    <div class="col-md-6" data-aos="fade-left">
                        <div class="info-box text-center">
                            <i class="fas fa-file-signature mt-4" style="font-size: 50px; color: #20B2AA;"></i>
                            <h3 style="font-weight: bold; margin-top: 35px; font-size:30px;">Terindeks Scopus</h3>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-6" data-aos="fade-left">
                <img src="{{ asset('assets/artikel/img/Tiny.jpg') }}" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</section>

<section id="contact" class="contact mb-5 mt-5">
    <div class="container">
        <h1 class="text-center mt-3" style="font-weight: bold; position: relative;">
            Testimoni
            <span style="display: block; 
                         width: 15%; 
                         height: 8px; 
                         background: linear-gradient(to right, #ff3131, #ff914d); 
                         position: absolute; 
                         bottom: -15px; 
                         left: 50%; 
                         transform: translateX(-50%); 
                         border-radius: 50px; 
                         opacity: 0.8; 
                         box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
            </span>
        </h1>
        <div class="row gy-4 mt-5">
            <div class="col-lg-6 d-flex justify-content-center" id="video" data-aos="fade-left">
                <div class="card" id="video-card" style="background: linear-gradient(to right, #ff3131, #ff914d); border-radius: 15px; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); overflow: hidden; width: 100%; height: 680px;">
                    <video id="videoElement" controls style="border-radius: 15px 15px 0 0; object-fit: cover; width: 100%; height: 100%; min-height: 100%; max-height: 100%; max-width: 100%;">
                        <source src="{{ asset('assets/img/public/testimoni.MOV') }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="row gy-4">
                    <div class="col-md-12" data-aos="fade-right">
                        <div class="info-box text-center">
                            <h3 style="margin-top: 10px; font-size:20px; text-align:justify">
                                Testimoni dari peserta yang berhasil submit ke Journal Internasional Bereputasi di Scopus. Para peserta
                                merasa bahagia setalah mengikuti Scopus Camp di Pusat Rumah Scopus yang terletak di Yogyakarta selama 3 hari.
                                Dari yang awalnya para peserta merasa pesimis dan takut untuk tembus ke Scopus, Sekarang para peserta sudah membuktikan
                                dengan sendiri bahwa tembus Scopus itu Mudah.
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!--================== ANIMASI MENGHITUNG ANGKA ==================-->
<script>
    // Function to animate the counter
    function animateValue(id, start, end, duration) {
        let obj = document.getElementById(id);
        let startTime = null;

        const step = (currentTime) => {
            if (!startTime) startTime = currentTime;
            const progress = Math.min((currentTime - startTime) / duration, 1);
            obj.innerHTML = Math.floor(progress * (end - start) + start);
            if (progress < 1) {
                requestAnimationFrame(step);
            }
        };
        requestAnimationFrame(step);
    }

    // Start animations when DOM is loaded
    document.addEventListener("DOMContentLoaded", function() {
        animateValue("counter1", 0, 5000, 2000); // Animate to 5000
        animateValue("counter2", 0, 2315, 2000); // Animate to 2000
        animateValue("counter3", 0, 1851, 2000); // Animate to 1000
    });
</script>
<!--================== END ==================-->

<!--================== ANIMASI MENGHITUNG ANGKA ==================-->
<!-- CSS -->
<style>
    /* Default height for non-zoomed or smaller screens */
    #video-card {
        height: 680px;
    }

    /* Full-screen height for larger screens or zoomed-in view */
    /* No changes needed for this as we handle full screen dynamically */
</style>

<!-- JavaScript -->
<script>
    // Get the video and card elements
    const videoElement = document.getElementById('videoElement');
    const videoCard = document.getElementById('video-card');

    // Function to handle fullscreen change
    function handleFullscreenChange() {
        if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {
            // If in fullscreen mode, make the card fill the screen
            videoCard.style.height = "100vh"; // Full screen height
            videoElement.style.objectFit = "contain"; // Ensure no cropping
        } else {
            // If not in fullscreen, revert to original height
            videoCard.style.height = "680px"; // Default height
            videoElement.style.objectFit = "cover"; // Maintain cover behavior
        }
    }

    // Add event listener for fullscreen change
    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
    document.addEventListener('mozfullscreenchange', handleFullscreenChange);
    document.addEventListener('MSFullscreenChange', handleFullscreenChange);
</script>
<!--================== END ==================-->

@stop