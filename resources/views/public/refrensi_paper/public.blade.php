@extends('public.layout.header')
@extends('public.paperisasi.layout.css')

@section('title')
Refrensi Paper | Rumah Scopus
@stop

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

<!--================== SEARCH STYLE ==================-->
<style>
    .search-wrapper {
        max-width: 700px;
        margin: 0 auto 25px auto;
    }

    .search-input {
        border: none;
        padding: 14px 20px;
        font-size: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .btn-search {
        background: linear-gradient(to right, #ff3131, #ff914d);
        border: none;
        color: white;
        font-weight: 600;
        padding: 10px 20px;
    }

    .btn-search:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(255, 49, 49, 0.3);
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
                Referensi Paper Ilmiah
                <span style="background: linear-gradient(to right, #ff3131, #ff914d); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    Terpercaya
                </span>
                untuk Publikasi
                <span style="background: linear-gradient(to right, #ff3131, #ff914d); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    Scopus
                </span>
            </h1>
        </div>

        <div class="row d-flex justify-content-center flex-wrap">


            <div class="container mt-5 mb-5">
                <div class="main-card" data-aos="fade-right">
                    <div class="card-body">
                        <div class="search-wrapper">
                            <form action="{{ route('public.refrensi-paper.SearchPublic') }}" method="GET" id="searchForm">
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control rounded-pill search-input"
                                        name="q"
                                        placeholder="Cari Judul, Jurnal, atau Topik..."
                                        value="{{ app('request')->input('q') }}">

                                    <div class="input-group-append">
                                        <button type="button"
                                            class="btn btn-search rounded-pill ml-2"
                                            id="searchButton">
                                            <i class="fa fa-search"></i> CARI
                                        </button>

                                        @if(request()->has('q'))
                                        <a href="{{ route('public.refrensi-paper.PublicRefrensiPaper') }}"
                                            class="btn btn-danger rounded-pill ml-2">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="row">
                            @foreach ($datas as $data)
                            <div class="col-md-3 mb-2" data-aos="fade-up">
                                <div class="inner-card" style="background-color: white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; justify-content: space-between; height: 100%; border-radius: 10px; padding: 20px;">
                                    <div style="width: 100%;">
                                        <div class="d-flex align-items-center" style="width: 100%;">
                                            <div style="margin-left: 0; width: 100%;">
                                                <h5 style="font-weight: bold; width: 100%;">{{ $data->judul_paper }}</h5>
                                                <p style="width: 100%;">{{ $data->nama_journal }}</p>
                                                <p style="width: 100%;">Q-{{ $data->quartile_journal }}</p>
                                                <hr style="width: 100%; margin: 10px 0;">
                                                <h5 style="font-weight: bold; width: 100%;">Abstrak</h5>
                                                <span style="width: 100%; display: inline-block;">
                                                    {{ implode(' ', array_slice(explode(' ', strip_tags($data->abstrak)), 0, 5)) }}
                                                    @if(str_word_count(strip_tags($data->abstrak)) > 5)
                                                    ...
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="width: 100%; margin-top: 15px;">
                                        <a href="{{ route('public.refrensi-paper.Selengkapnya', $data->id) }}" style="text-decoration: none; width: 100%; display: inline-block;">
                                            <button class="btn btn-submit rounded-pill" style="color: white; font-size: 14px; width: 100%; background: linear-gradient(to right, #ff3131, #ff914d); border-radius: 15px; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); overflow: hidden;">
                                                <i class="fa fa-paper-plane"></i> SELENGKAPNYA
                                            </button>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-center mt-4">
                            {{ $datas->appends(['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate])->links('vendor.pagination.bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>

<!--================== SWEET ALERT JIKA FIELDS KOSONG ==================-->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("searchButton").addEventListener("click", function() {
            var searchInputValue = document.querySelector("input[name='q']").value.trim();

            if (searchInputValue === "") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Harap isi field pencarian terlebih dahulu!',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            } else {
                // If not empty, submit the form
                document.getElementById("searchForm").submit();
            }
        });
    });
</script>
<!--================== END ==================-->
@stop