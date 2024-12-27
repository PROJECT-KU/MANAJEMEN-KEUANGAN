@extends('public.layout.header')
@extends('public.paperisasi.layout.css')

@section('title')
Cek ID | Rumah Scopus
@stop

<style>
    /* Remove border when hovering over the image */
    #image-preview {
        border: none !important;
        /* Hapus border pada gambar */
    }

    #image-preview:hover {
        border: none;
        /* Hapus border pada gambar saat dihover */
    }

    .container {
        width: 100%;
        max-width: 1200px;
        /* Sesuaikan lebar container sesuai kebutuhan */
        margin: 0 auto;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .more-text {
        font-size: 15px;
        margin: 0;
        text-align: right;
        background: linear-gradient(to right, #ff3131, #ff914d);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>

<!--================== ANIMASI CARD PAPER ==================-->
<style>
    /* Animasi saat hover pada card */
    .hover-card {
        transition: transform 0.4s ease-out, box-shadow 0.4s ease-out, filter 0.3s ease-in-out;
        cursor: pointer;
        /* Mengubah kursor menjadi pointer saat dihover */
        transform: translateZ(0);
        /* Memastikan rendering yang lebih halus */
    }

    .hover-card:hover {
        transform: translateY(-12px) scale(1.07) rotate(2deg);
        /* Meningkatkan efek perpindahan dan sedikit rotasi */
        box-shadow: 0px 20px 35px rgba(0, 0, 0, 0.2);
        /* Membuat bayangan lebih besar */
        filter: brightness(1.1);
        /* Mencerahkan sedikit gambar saat dihover */
    }

    /* Efek Bayangan Lebih Besar dengan Efek Blur */
    .card {
        transition: box-shadow 0.3s ease-in-out;
    }

    .card:hover {
        box-shadow: 0px 20px 40px rgba(0, 0, 0, 0.3);
        /* Membuat bayangan lebih kuat saat hover */
    }
</style>
<!--================== END ==================-->

@section('konten')
<!-- ======= Hero Section ======= -->
<section id="hero" class="hero d-flex align-items-center">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 d-flex flex-column justify-content-center">
                <h1 data-aos="fade-up">Cek ID</h1>
                <h2 data-aos="fade-up" data-aos-delay="400">Janganlah pernah menyerah ketika kamu masih mampu berusaha lagi. Tidak ada kata berakhir sampai kamu berhenti mencoba</h2>
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
                <img src="{{ asset('assets/artikel/img/TinyPNG.png') }}" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</section>

<div class="container mt-5 mb-5">
    <div class="main-card" style="background: linear-gradient(to right, #ff3131, #ff914d); border-radius: 15px; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); overflow: hidden;" data-aos="fade-right">
        <h1 class="text-center mt-3" style="font-weight: bold; color: white;">Cari ID Paper</h1>
        <div class="card-body" style="padding: 30px; border-radius: 15px;">
            <form action="{{ route('public.paperisasi.search') }}" method="GET" id="searchForm">
                <div class="form-group">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control rounded-pill" name="q" placeholder="Masukkan ID Paper" value="{{ request()->input('q') }}" id="searchInput">
                        <div class="input-group-append" style="margin-left: 10px;">
                            <button type="submit" class="btn btn-info rounded-pill" id="searchButton" style="color: white;">
                                <i class="fa fa-search"></i> Cari
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="mt-5">
    @if(!request()->has('q'))
    <div class="d-flex justify-content-center mb-4">
        <div class="alert alert-warning text-center" style="width: max-content;">
            Belum Ada Data yang Bisa Ditampilkan, Silahkan Masukkan ID Anda
        </div>
    </div>
    @elseif($datas->isEmpty())
    <div class="d-flex justify-content-center mb-4">
        <div class="alert alert-warning text-center" style="width: max-content;">
            Data tidak ditemukan untuk ID Paper: <strong>{{ request()->input('q') }}</strong>
        </div>
    </div>
    @else <!-- Jika data ditemukan -->
    <div class="d-flex justify-content-center mb-4">
        <div class="alert alert-success text-center" style="width: max-content;">
            Hasil Pencarian untuk ID Paper: <strong>{{ request()->input('q') }}</strong>
        </div>
    </div>
    @foreach($datas as $data)
    <!--================== DETAIL PAPER ==================-->
    <div class="container mt-5">
        <div class="row">
            <!-- Card 1 -->
            <div class="col-md-3">
                <div class="card shadow hover-card">
                    <span class="btn btn-info" style="font-size: 20px;">Judul Paper</span>
                    <div class="card-body">
                        <p class="card-text">{{ $data->judul_paper }}</p>
                    </div>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="col-md-3">
                <div class="card shadow hover-card">
                    <span class="btn btn-info" style="font-size: 20px;">First Author</span>
                    <div class="card-body">
                        <h5 class="card-title">Nama : <strong>{{ $data->first_author }}</strong></h5>
                        <p class="card-text">Affiliasi : <strong>{{ $data->affiliasi_first_author }}</strong></p>
                    </div>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="col-md-3">
                <div class="card shadow hover-card">
                    <span class="btn btn-info" style="font-size: 20px;">Co-Author</span>
                    <div class="card-body">
                        <h5 class="card-title">Nama : <strong>{{ $data->co_author }}</strong></h5>
                        <p class="card-text">Affiliasi : <strong>{{ $data->affiliasi_co_author }}</strong></p>
                    </div>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="col-md-3">
                <div class="card shadow hover-card">
                    <span class="btn btn-info" style="font-size: 20px;">Journal Target</span>
                    <div class="card-body">
                        <h5 class="card-title">Nama Journal : <strong>{{ $data->jurnal_target }}</strong></h5>
                        <p class="card-text">Quartile Journal : <strong>{{ $data->q_jurnal }}</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--================== END ==================-->

    <!--================== TIMELINE ==================-->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="timeline">
                        <div class="time-label">
                            <span class="bg-red">{{ \Carbon\Carbon::parse($data->tanggal_masuk_paper)->format('d F Y H:i') }}</span>
                        </div>
                        <!-- STATUS PAPER -->
                        <div>
                            <i class="fas fa-newspaper bg-blue"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Status Paper</h3>
                                <div class="timeline-footer" style="font-size: 20px;">
                                    @if($data->status_paper == 'antrian paper')
                                    <a class="badge badge-danger">ANTRIAN PAPER</a>
                                    @elseif($data->status_paper == 'paper masuk')
                                    <a class="badge badge-info">PAPER MASUK</a>
                                    @elseif($data->status_paper == 'pengerjaan paper')
                                    <a class="badge badge-warning">PENGERJAAN PAPER</a>
                                    @elseif($data->status_paper == 'submit paper')
                                    <a class="badge badge-success">SUBMIT PAPER</a>
                                    @elseif($data->status_paper == 'revisi paper')
                                    <a class="badge badge-warning">REVISI PAPER</a>
                                    @elseif($data->status_paper == 'resubmit paper')
                                    <a class="badge badge-info">RESUBMIT PAPER</a>
                                    @else
                                    <a class="badge badge-success">PAPER SELESAI</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- END STATUS PAPER -->

                        <!-- PROGRESS ANATOMY PAPER -->
                        @if ($data->progres_anatomi_tanggal === null)
                        @else
                        <div class="time-label">
                            <span class="bg-info">{{ \Carbon\Carbon::parse($data->progres_anatomi_tanggal)->format('d F Y H:i') }}</span>
                        </div>
                        <div>
                            <i class="fa fa-newspaper bg-purple"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Status Anatomy Paper</h3>
                                <div class="timeline-body" style="font-size: 20px;">
                                    @if($data->progres_anatomi_status == 'in progress anatomy')
                                    <a class="badge badge-info" style="color: white;">PENGERJAAN ANATOMY</a>
                                    @elseif($data->progres_anatomi_status == 'revisi anatomy')
                                    <a class="badge badge-warning">REVISI ANATOMY</a>
                                    @else
                                    <a class="badge badge-success">ANATOMY SELESAI</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-clock bg-orange" style="color: white;"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Progress Anatomy Paper</h3>
                                <div class="timeline-footer">
                                    <a href="#" class="btn btn-sm bg-warning">Estimasi Pengerjaan : {{ $data->progres_anatomi_estimasi }}</a>
                                </div>
                                <div class="timeline-body">
                                    <p>{{ $data->progres_anatomi_keterangan }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        <!-- END PROGRESS ANATOMY PAPER -->

                        <!-- PROGRESS ANATOMY PAPER SECOND -->
                        @if ($data->progres_anatomi_tanggal_second === null)
                        @else
                        <div class="time-label">
                            <span class="bg-info">{{ \Carbon\Carbon::parse($data->progres_anatomi_tanggal_second)->format('d F Y H:i') }}</span>
                        </div>
                        <div>
                            <i class="fa fa-newspaper bg-purple"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Status Anatomy Paper</h3>
                                <div class="timeline-body" style="font-size: 20px;">
                                    @if($data->progres_anatomi_status_second == 'in progress anatomy')
                                    <a class="badge badge-info" style="color: white;">PENGERJAAN ANATOMY</a>
                                    @elseif($data->progres_anatomi_status_second == 'revisi anatomy')
                                    <a class="badge badge-warning">REVISI ANATOMY</a>
                                    @else
                                    <a class="badge badge-success">ANATOMY SELESAI</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-clock bg-orange" style="color: white;"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Progress Anatomy Paper</h3>
                                <div class="timeline-footer">
                                    <a href="#" class="btn btn-sm bg-warning">Estimasi Pengerjaan : {{ $data->progres_anatomi_estimasi_second }}</a>
                                </div>
                                <div class="timeline-body">
                                    <p>{{ $data->progres_anatomi_keterangan_second }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        <!-- END PROGRESS ANATOMY PAPER SECOND -->

                        <!-- PROGRESS ANATOMY PAPER THIRD -->
                        @if ($data->progres_anatomi_tanggal_third === null)
                        @else
                        <div class="time-label">
                            <span class="bg-info">{{ \Carbon\Carbon::parse($data->progres_anatomi_tanggal_third)->format('d F Y H:i') }}</span>
                        </div>
                        <div>
                            <i class="fa fa-newspaper bg-purple"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Status Anatomy Paper</h3>
                                <div class="timeline-body" style="font-size: 20px;">
                                    @if($data->progres_anatomi_status_third == 'in progress anatomy')
                                    <a class="badge badge-info" style="color: white;">PENGERJAAN ANATOMY</a>
                                    @elseif($data->progres_anatomi_status_third == 'revisi anatomy')
                                    <a class="badge badge-warning">REVISI ANATOMY</a>
                                    @else
                                    <a class="badge badge-success">ANATOMY SELESAI</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-clock bg-orange"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Progress Anatomy Paper</h3>
                                <div class="timeline-footer">
                                    <a href="#" class="btn btn-sm bg-warning">Estimasi Pengerjaan : {{ $data->progres_anatomi_estimasi_third }}</a>
                                </div>
                                <div class="timeline-body">
                                    <p>{{ $data->progres_anatomi_keterangan_third }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        <!-- END PROGRESS ANATOMY PAPER THIRD -->

                        <!-- PROGRESS PAPER -->
                        @if ($data->progres_paper_tanggal === null)
                        @else
                        <div class="time-label">
                            <span class="bg-success">{{ \Carbon\Carbon::parse($data->progres_paper_tanggal)->format('d F Y H:i') }}</span>
                        </div>
                        <div>
                            <i class="fa fa-newspaper bg-info"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Status Paper</h3>
                                <div class="timeline-body" style="font-size: 20px;">
                                    @if($data->progres_paper_status == 'in progress paper')
                                    <a class="badge badge-info" style="color: white;">PENGERJAAN PAPER</a>
                                    @elseif($data->progres_paper_status == 'revisi paper')
                                    <a class="badge badge-warning">REVISI PAPER</a>
                                    @else
                                    <a class="badge badge-success">PAPER SELESAI</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-clock bg-blue"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Progress Paper</h3>
                                <div class="timeline-footer">
                                    <a href="#" class="btn btn-sm bg-warning">Estimasi Pengerjaan : {{ $data->progres_paper_estimasi }}</a>
                                </div>
                                <div class="timeline-body">
                                    <p>{{ $data->progres_paper_keterangan }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        <!-- END PROGRESS PAPER -->

                        <!-- PROGRESS PAPER SECOND -->
                        @if ($data->progres_paper_tanggal_second === null)
                        @else
                        <div class="time-label">
                            <span class="bg-success">{{ \Carbon\Carbon::parse($data->progres_paper_tanggal_second)->format('d F Y H:i') }}</span>
                        </div>
                        <div>
                            <i class="fa fa-newspaper bg-info"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Status Paper</h3>
                                <div class="timeline-body" style="font-size: 20px;">
                                    @if($data->progres_paper_status_second == 'in progress paper')
                                    <a class="badge badge-info" style="color: white;">PENGERJAAN PAPER</a>
                                    @elseif($data->progres_paper_status_second == 'revisi paper')
                                    <a class="badge badge-warning">REVISI PAPER</a>
                                    @else
                                    <a class="badge badge-success">PAPER SELESAI</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-clock bg-blue"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Progress Paper</h3>
                                <div class="timeline-footer">
                                    <a href="#" class="btn btn-sm bg-warning">Estimasi Pengerjaan : {{ $data->progres_paper_estimasi_second }}</a>
                                </div>
                                <div class="timeline-body">
                                    <p>{{ $data->progres_paper_keterangan_second }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        <!-- END PROGRESS PAPER SECOND -->

                        <!-- PROGRESS PAPER THIRD -->
                        @if ($data->progres_paper_tanggal_third === null)
                        @else
                        <div class="time-label">
                            <span class="bg-success">{{ \Carbon\Carbon::parse($data->progres_paper_tanggal_third)->format('d F Y H:i') }}</span>
                        </div>
                        <div>
                            <i class="fa fa-newspaper bg-info"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Status Paper</h3>
                                <div class="timeline-body" style="font-size: 20px;">
                                    @if($data->progres_paper_status_third == 'in progress paper')
                                    <a class="badge badge-info" style="color: white;">PENGERJAAN PAPER</a>
                                    @elseif($data->progres_paper_status_third == 'revisi paper')
                                    <a class="badge badge-warning">REVISI PAPER</a>
                                    @else
                                    <a class="badge badge-success">PAPER SELESAI</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-clock bg-blue"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Progress Paper</h3>
                                <div class="timeline-footer">
                                    <a href="#" class="btn btn-sm bg-warning">Estimasi Pengerjaan : {{ $data->progres_paper_estimasi_third }}</a>
                                </div>
                                <div class="timeline-body">
                                    <p>{{ $data->progres_paper_keterangan_third }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        <!-- END PROGRESS PAPER THIRD -->

                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--================== END ==================-->
    @endforeach
    @endif
</div>
@stop