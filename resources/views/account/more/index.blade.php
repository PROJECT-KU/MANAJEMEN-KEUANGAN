@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Dashboard | MIS
@stop

<!--================== MENAMPILKAN MENU HANYA DI PWA ==================-->
<style>
    #carousel {
        display: flex;
        /* Menggunakan flex untuk menampilkan dalam satu baris */
        flex-wrap: wrap;
        /* Agar card-icon yang berlebihan bisa turun ke baris baru */
        gap: 10px;
        /* Jarak antara card-icon */
        justify-content: center;
        /* Menempatkan card-icon di tengah secara horizontal */
    }
</style>
<!--================== END ==================-->

<!--================== MENYEMBUNYIKAN SAAT DI REFRESH DI PWA ==================-->
<style>
    /* Menyembunyikan sidebar */
    #totalGajiCard {
        display: none;
    }

    #PemasukanHariIniCard {
        display: none;
    }

    #PemasukanBulanIniCard {
        display: none;
    }

    #PemasukanTahunIniCard {
        display: none;
    }

    #PengeluaranHariIniCard {
        display: none;
    }

    #PengeluaranBulanIniCard {
        display: none;
    }

    #PengeluaranTahunIniCard {
        display: none;
    }

    #StatistikPemasukan {
        display: none;
    }

    #StatistikPengeluaran {
        display: none;
    }

    #PenggunaBaru {
        display: none;
    }
</style>
<!--================== END ==================-->

@section('content')

<div class="main-content">
    <section class="section">
        <!--================== MANU DI PWA ==================-->
        <div class="row" id="MenuPwaCard">
            <div class="col-md-12 mt-5">
                <div class="card card-statistic-2" style="overflow: hidden; height:max-content;">
                    <div id="carousel" class="mb-5">
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #007bff, #0056b3, #002366, #000080); text-align: center;">
                            <a href="{{ route('account.gaji.index') }}"><i class="fas fa-file-invoice-dollar" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left: 2px;"></i></a>
                            <span style="font-size: 16px; display: inline-block;">Gaji</span>
                        </div>
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #FFA500, #FF8C00, #FF6347, #FF4500); text-align: center;">
                            <a href="{{ route('account.presensi.index') }}"><i class="fas fa-user-clock" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left: -2px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-left: -5px;">Presensi</span>
                        </div>
                        @if (Auth::user()->level === 'admin' || Auth::user()->level === 'manager' || Auth::user()->level === 'ceo')
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #00FF00, #008000, #7FFF00, #6B8E23); text-align: center;">
                            <a href="{{ route('account.pengguna.index') }}"><i class="fas fa-user" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left: 2px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-left: -10px;">Pengguna</span>
                        </div>
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #D2B48C, #8B4513, #A52A2A, #BC8F8F); text-align: center;">
                            <a href="{{ route('account.company.edit', ['id' => Auth::user()->id]) }}"><i class="fas fa-building" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left: -1px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-left: -10px;">Company</span>
                        </div>
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #5F9EA0, #4B0082, #ADD8E6, #8A9597); text-align: center;">
                            <a href="{{ route('account.camp.index') }}"><i class="fas fa-campground" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left: -2px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px; margin-left: -6px;">Laporan</span>
                            <span style="font-size: 16px;">Camp</span>
                        </div>
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #1B4D3E, #183028, #006400, #556B2F); text-align: center;">
                            <a href="{{ route('karir.list') }}"><i class="fas fa-user-tie" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left: 1px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px;">Karir</span>
                        </div>
                        @endif
                    </div>

                    @if (Auth::user()->level === 'admin' || Auth::user()->level === 'manager' || Auth::user()->level === 'ceo')
                    <hr>
                    <span style="margin-left: 20px; font-size:20px; font-weight: bold;">Peserta</span>
                    <div id="carousel" class="mb-5">
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #7FFF00, #8B4513, #A52A2A, #A52A2A); text-align: center;">
                            <a href="{{ route('account.kategori.index') }}"><i class="fas fa-dice-d6" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left:-1px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px; margin-left:-5px;">Kategori</span>
                        </div>
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #D2B48C, #BC987E, #AF8E78, #EED5B7); text-align: center;">
                            <a href="{{ route('account.scopuscamp.index') }}"><i class="fas fa-file-signature" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px; margin-left:-18px;">Pendaftaran</span>
                        </div>
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #D2B48C, #CD853F, #BC8F8F, #8B6969); text-align: center;">
                            <a href="{{ route('account.peserta.list') }}"><i class="fas fa-user-edit" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px; margin-left:-3px;">Evaluasi</span>
                        </div>
                    </div>
                    @endif

                    @if (Auth::user()->level === 'admin' || Auth::user()->level === 'manager' || Auth::user()->level === 'ceo')
                    <hr>
                    <span style="margin-left: 20px; font-size:20px; font-weight: bold;">Artikel</span>
                    <div id="carousel" class="mb-5">
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #483D8B, #4B0082, #800080, #6A5ACD); text-align: center;">
                            <a href="{{ route('account.Kategori-Artikel.index') }}"><i class="fas fa-dice-d6" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left:-1px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px; margin-left:-5px;">Kategori</span>
                        </div>
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #D8BFD8, #E6E6FA, #9370DB, #6A5ACD); text-align: center;">
                            <a href="{{ route('account.Artikel.index') }}"><i class="fas fa-file-signature" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px;">Data</span>
                            <span style="font-size: 16px;">Artikel</span>
                        </div>
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #FF00FF, #A020F0, #FF77FF, #8A2BE2); text-align: center;">
                            <a href="{{ route('account.Artikel.index') }}"><i class="fas fa-comments" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px; margin-left:-10px;">Komentar</span>
                        </div>
                    </div>
                    @endif

                    <hr>
                    <span style="margin-left: 20px; font-size:20px; font-weight: bold;">Uang Masuk</span>
                    <div id="carousel" class="mb-5">
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #1B4D3E, #183028, #006400, #556B2F); text-align: center;">
                            <a href="{{ route('account.categories_debit.index') }}"><i class="fas fa-dice-d6" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left:-1px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px; margin-left:-5px;">Kategori</span>
                        </div>
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #8A2BE2, #800080, #4B0082, #483D8B); text-align: center;">
                            <a href="{{ route('account.debit.index') }}"><i class="fas fa-wallet" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px;">Uang</span>
                            <span style="font-size: 16px;">Masuk</span>
                        </div>
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #7FFF00, #32CD32, #008000, #006400); text-align: center;">
                            <a href="{{ route('account.laporan_debit.index') }}"><i class="fas fa-chart-line" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px; margin-left:-5px;">Laporan</span>
                        </div>
                    </div>

                    <hr>
                    <span style="margin-left: 20px; font-size:20px; font-weight: bold;">Uang Keluar</span>
                    <div id="carousel" class="mb-5">
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #800000, #800000, #660000, #B22222); text-align: center;">
                            <a href="{{ route('account.categories_credit.index') }}"><i class="fas fa-dice-d6" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left:-1px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px; margin-left:-5px;">Kategori</span>
                        </div>
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #A52A2A, #8B0000, #800000, #C71585); text-align: center;">
                            <a href="{{ route('account.credit.index') }}"><i class="fas fa-money-check-alt" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left:-5px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px;">Uang</span>
                            <span style="font-size: 16px;">Keluar</span>
                        </div>
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #FFC0CB, #FFB6C1, #FF69B4, #FA8072); text-align: center;">
                            <a href="{{ route('account.laporan_credit.index') }}"><i class="fas fa-chart-area" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px; margin-left:-5px;">Laporan</span>
                        </div>
                    </div>

                    <hr>
                    <span style="margin-left: 20px; font-size:20px; font-weight: bold;">Laporan</span>
                    <div id="carousel" class="mb-5">
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #FFD700, #ADFF2F, #808000, #DAA520); text-align: center;">
                            <a href="{{ route('account.laporan_semua.index') }}"><i class="fas fa-chart-area" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left:-1px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px; margin-left:-5px;">Catatan</span>
                        </div>
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #FFD700, #FFBF00, #FFA500, #FF8C00); text-align: center;">
                            <a href="{{ route('account.neraca.index') }}"><i class="fas fa-balance-scale" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left:-5px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px;">Neraca</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!--================== END ==================-->

    </section>
</div>

<!--================== CEK DIVACE APAKAH PWA ATAU WEBSITE ==================-->
<script>
    // Fungsi untuk mendeteksi apakah perangkat adalah ponsel atau browser
    function isMobileDevice() {
        return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
    }

    // Fungsi untuk menyembunyikan atau menampilkan elemen berdasarkan tipe perangkat
    function toggleElementBasedOnDevice() {
        var totalGajiCard = document.getElementById('totalGajiCard');
        var MenuPwaCard = document.getElementById('MenuPwaCard');
        var SisaSaldoBulanIniCard = document.getElementById('SisaSaldoBulanIniCard');
        var SisaSaldoBulanLaluCard = document.getElementById('SisaSaldoBulanLaluCard');
        var PemasukanHariIniCard = document.getElementById('PemasukanHariIniCard');
        var PemasukanBulanIniCard = document.getElementById('PemasukanBulanIniCard');
        var PemasukanTahunIniCard = document.getElementById('PemasukanTahunIniCard');
        var PengeluaranHariIniCard = document.getElementById('PengeluaranHariIniCard');
        var PengeluaranBulanIniCard = document.getElementById('PengeluaranBulanIniCard');
        var PengeluaranTahunIniCard = document.getElementById('PengeluaranTahunIniCard');
        var StatistikPemasukan = document.getElementById('StatistikPemasukan');
        var StatistikPengeluaran = document.getElementById('StatistikPengeluaran');
        var PenggunaBaru = document.getElementById('PenggunaBaru');

        if (isMobileDevice()) {
            // Jika aplikasi berjalan di perangkat seluler (PWA)
            totalGajiCard.style.display = 'none';
            MenuPwaCard.style.display = 'block';
            SisaSaldoBulanIniCard.style.display = 'block';
            SisaSaldoBulanLaluCard.style.display = 'block';
            PemasukanHariIniCard.style.display = 'none';
            PemasukanBulanIniCard.style.display = 'none';
            PemasukanTahunIniCard.style.display = 'none';
            PengeluaranHariIniCard.style.display = 'none';
            PengeluaranBulanIniCard.style.display = 'none';
            PengeluaranTahunIniCard.style.display = 'none';
            StatistikPemasukan.style.display = 'none';
            StatistikPengeluaran.style.display = 'none';
            PenggunaBaru.style.display = 'none';
        } else {
            // Jika aplikasi berjalan di browser
            totalGajiCard.style.display = 'block';
            MenuPwaCard.style.display = 'none';
            SisaSaldoBulanIniCard.style.display = 'block';
            SisaSaldoBulanLaluCard.style.display = 'block';
            PemasukanHariIniCard.style.display = 'block';
            PemasukanBulanIniCard.style.display = 'block';
            PemasukanTahunIniCard.style.display = 'block';
            PengeluaranHariIniCard.style.display = 'block';
            PengeluaranBulanIniCard.style.display = 'block';
            PengeluaranTahunIniCard.style.display = 'block';
            StatistikPemasukan.style.display = 'block';
            StatistikPengeluaran.style.display = 'block';
            PenggunaBaru.style.display = 'block';
        }
    }

    // Panggil fungsi ketika halaman dimuat
    window.addEventListener('load', toggleElementBasedOnDevice);
</script>
<!--================== END ==================-->

<!--================== SHOW & HIDE TOTAL GAJI ==================-->
<script>
    function toggleTotalGaji() {
        const totalgajiToggle = document.getElementById('totalgaji-toggle');

        if (totalgajiToggle.classList.contains('fa-eye')) {
            document.getElementById('totalgaji').innerHTML = '<span style="margin-left: -30px; font-size: 23px;">Rp. {{ number_format($totalGaji, 0, ', ', ', ') }}</span> <i class="fas fa-eye-slash totalgaji-toggle ml-2" id="totalgaji-toggle" onclick="toggleTotalGaji()"></i>';
        } else {
            document.getElementById('totalgaji').innerHTML = '<span style="margin-left: -30px; font-size: 23px;"> ****** </span> <i class="fas fa-eye totalgaji-toggle ml-2" id="totalgaji-toggle" onclick="toggleTotalGaji()" style="margin-left: -1em;"></i>';
        }
    }
</script>
<!--================== END ==================-->

<script>
    function submitForm() {
        // Prevent default form submission
        event.preventDefault();

        // Submit the form using AJAX
        var form = document.getElementById('pulangForm');
        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.action, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Handle success, if needed
                    console.log('Form submitted successfully');
                    window.location.reload(); // Reload the page after successful submission
                } else {
                    // Handle error, if needed
                    console.error('Form submission failed');
                }
            }
        };
        xhr.send(formData);
    }
</script>

<style>
    /* CSS for the hover effect */
    .card-hover:hover {
        box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.3);
    }
</style>

<!--================== open and close chart akses cepat ==================-->
<script>
    function toggleChartAksescepat() {
        var chartContainerAksescepat = document.getElementById('chartContainerAksescepat');
        var financeChartAksescepat = document.getElementById('financeChartAksescepat');
        var toggleBtnAksescepat = document.getElementById('toggleChartBtnAksescepat');

        if (chartContainerAksescepat.style.display === 'none') {
            chartContainerAksescepat.style.display = 'block';
            financeChartAksescepat.style.display = 'none';
            toggleBtnAksescepat.innerText = 'Tutup Chart';
        } else {
            chartContainerAksescepat.style.display = 'none';
            financeChartAksescepat.style.display = 'block';
            toggleBtnAksescepat.innerText = 'Buka Chart';
        }
    }
</script>
<!--================== end ==================-->

<!--================== open and close chart pemasukan ==================-->
<script>
    function toggleChartPemasukan() {
        var chartContainerPemasukan = document.getElementById('chartContainerPemasukan');
        var financeChartPemasukan = document.getElementById('financeChartPemasukan');
        var toggleBtnPemasukan = document.getElementById('toggleChartBtnPemasukan');

        if (chartContainerPemasukan.style.display === 'none') {
            chartContainerPemasukan.style.display = 'block';
            financeChartPemasukan.style.display = 'none';
            toggleBtnPemasukan.innerText = 'Tutup Chart';
        } else {
            chartContainerPemasukan.style.display = 'none';
            financeChartPemasukan.style.display = 'block';
            toggleBtnPemasukan.innerText = 'Buka Chart';
        }
    }
</script>
<!--================== end ==================-->

<!--================== open and close chart pengeluaran ==================-->
<script>
    function toggleChart() {
        var chartContainer = document.getElementById('chartContainer');
        var financeChart = document.getElementById('financeChart');
        var toggleBtn = document.getElementById('toggleChartBtn');

        if (chartContainer.style.display === 'none') {
            chartContainer.style.display = 'block';
            financeChart.style.display = 'none';
            toggleBtn.innerText = 'Tutup Chart';
        } else {
            chartContainer.style.display = 'none';
            financeChart.style.display = 'block';
            toggleBtn.innerText = 'Buka Chart';
        }
    }
</script>
<!--================== end ==================-->

<!--================== popup akun berhasil ==================-->
@if (session('message'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Use SweetAlert to display the success message
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Selamat Akun Anda Berhasil Dibuat!',
        confirmButtonText: 'OK'
    });
</script>
@endif
<!--================== end ==================-->

<script type="text/javascript" src="chartjs/Chart.js"></script>
<script>
    var ctx = document.getElementById("myChart").getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ["Red", "Blue", "Yellow", "Green", "Purple", "Orange"],
            datasets: [{
                label: '# of Votes',
                data: [12, 19, 3, 23, 2, 3],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)'
                ],
                borderColor: [
                    'rgba(255,99,132,1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
</script>
@stop