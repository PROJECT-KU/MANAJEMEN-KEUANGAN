@extends('public.layout.header')

@section('title')
Blog | Rumah Scopus
@stop

@section('konten')
<!-- ======= Contact Section ======= -->
<section id="contact" class="contact mb-5 mt-5">

    <div class="container" data-aos="fade-up">

        <header class="section-header">
            <h2>Kontak</h2>
            <p>Hubungi Kami Sekarang</p>
        </header>

        <div class="row gy-4">
            <div class="col-lg-6">
                <div class="row gy-4">
                    <div class="col-md-6">
                        <div class="info-box">
                            <i class="bi bi-geo-alt"></i>
                            <h3>Alamat</h3>
                            <p>Bangunsari, Jl. Bangunsari, Bangunsari, Bangun Kerto, Turi, Sleman Regency, Special Region of Yogyakarta 55551</p>
                            <a href="https://maps.app.goo.gl/M3VYdnDJvPhoRtbQ6" class="more-text" target="_blank">
                                <p>Lihat Lokasi <i style="font-size: 15px;" class="fas fa-chevron-right"></i></p>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <i class="bi bi-clock"></i>
                            <h3>Jam Buka</h3>
                            <p>Kamis : 13.00 - 19.00 WIB</p>
                            <p>Jumat : 08.30 - 19.00 WIB</p>
                            <p>Sabtu : 08.30 - 19.00 WIB</p>
                            <p>Minggu : 08.30 - 19.00 WIB</p>
                            <p>Senin : 09.00 - 16.00 WIB</p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <i class="bi bi-envelope"></i>
                            <h3>Email</h3>
                            <p>info@rumahscopusfoundation.com</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <i class="bi bi-telephone"></i>
                            <h3>Telp</h3>
                            <p class="mb-2"><b>[Dinar]</b> +62 812-2688-3280 <a href="https://wa.me/+6281226883280" target="_blank"><i style="font-size: 25px;" class="fab fa-whatsapp"></i></a></p>
                            <p><b>[Elsa]</b> +62 857-4781-0881 <a href="https://wa.me/+6285747810881" target="_blank"><i style="font-size: 25px;" class="fab fa-whatsapp"></i></a></p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-6">
                <img src="{{ asset('assets/artikel/img/features-3.png') }}" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</section>

@stop