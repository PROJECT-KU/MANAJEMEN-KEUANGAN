@extends('layouts.account')

@section('title')
Dashboard | MANAGEMENT
@stop

@section('content')

<script>

</script>

<div class="main-content">
    <section class="section">
        <div class=" col-lg-12 col-md-4 col-sm-4 col-xs-4">

            <!-- akun belum di verifikasi -->
            @if (!Auth::user()->email_verified_at)
            <div class="alert alert-danger" role="alert" style="text-align: center;">
                <b style="font-size: 20px;">Akun Anda Belum Diverifikasi Oleh Admin!</b><br>Silahkan Hubungin Admin Untuk Verifikasi Akun!
            </div>
            @endif
            <!-- end -->

            <!-- akun dinonaktifkan -->
            @if (Auth::user()->status === 'off')
            <div class="alert alert-danger" role="alert" style="text-align: center;">
                <b style="font-size: 20px;">Akun Anda Di Nonaktifkan Sementara!</b><br>Silahkan Hubungin Admin Untuk Aktifkan Akun!
            </div>
            @endif
            <!-- end -->

            <!-- masa sewa akun akan habis -->
            @if (Auth::user()->tenggat === null)
            @elseif (now() > Auth::user()->tenggat)
            <div class="alert alert-danger" role="alert" style="text-align: center;">
                <b style="font-size: 20px;">MASA SEWA TELAH HABIS</b><br>
                <p style="font-size: 15px;">Masa sewa anda telah berakhir.</p>
                TELAH HABIS SEJAK TANGGAL {{ date('d-m-Y', strtotime(Auth::user()->tenggat)) }}
            </div>
            @elseif (now()->addDays(3) >= Auth::user()->tenggat)
            <div class="alert alert-warning" role="alert" style="text-align: center;">
                <b style="font-size: 20px;">MASA SEWA SEGERA HABIS</b><br>
                <p style="font-size: 15px;">Masa sewa anda akan segera habis.</p>
                HABIS PADA TANGGAL {{ date('d-m-Y', strtotime(Auth::user()->tenggat)) }}
            </div>
            @endif
            <!-- end -->


            <!-- jika data diri masih ada yang kosong -->
            @if (Auth::user()->company === null || Auth::user()->telp === null || Auth::user()->nik === null || Auth::user()->norek === null || Auth::user()->bank === null || Auth::user()->gambar == null)
            <div class="alert alert-warning" role="alert" style="text-align: center;">
                <b style="font-size: 20px;">DATA DIRI</b><br>
                <p style="font-size: 15px;">Data diri anda masih ada yang kosong! Silahkan Lengkapi data diri anda terlebih dahulu!</p>
            </div>
            @endif
            <!-- end -->
        </div>
        <div class="row">
            <!--<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="card card-statistic-2">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>SEMUA SALDO </h4>
                        </div>
                        <div class="card-body" style="font-size: 20px">
                            {{ rupiah($saldo_selama_ini) }}
                        </div>
                    </div>
                </div>
            </div>-->
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                <div class="card card-statistic-2">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>SISA SALDO BULAN INI</h4>
                        </div>
                        <div class="card-body" style="font-size: 20px">
                            {{ rupiah($saldo_bulan_ini) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                <div class="card card-statistic-2">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>SISA SALDO BULAN LALU</h4>
                        </div>
                        <div class="card-body" style="font-size: 20px">
                            {{ rupiah($saldo_bulan_lalu) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="card card-statistic-2" style="background-color:#AFEEEE;">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4><b>PEMASUKAN HARI INI</b></h4>
                        </div>
                        <div class="card-body" style="font-size: 20px">
                            {{ rupiah($Pemasukan_hari_ini) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="card card-statistic-2" style="background-color:#AFEEEE;">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4><b>PEMASUKAN BULAN INI</b></h4>
                        </div>
                        <div class="card-body" style="font-size: 20px">
                            {{ rupiah($pemasukan_bulan_ini) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="card card-statistic-2" style="background-color:#AFEEEE;">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4><b>PEMASUKAN TAHUN INI</b></h4>
                        </div>
                        <div class="card-body" style="font-size: 20px">
                            {{ rupiah($pemasukan_tahun_ini) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="card card-statistic-2" style="background-color:#FFB6C1;">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4><b>PENGELUARAN HARI INI</b></h4>
                        </div>
                        <div class="card-body" style="font-size: 20px">
                            {{ rupiah($pengeluaran_hari_ini) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="card card-statistic-2" style="background-color:#FFB6C1;">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4><b>PENGELUARAN BULAN INI</b></h4>
                        </div>
                        <div class="card-body" style="font-size: 20px">
                            {{ rupiah($pengeluaran_bulan_ini) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="card card-statistic-2" style="background-color:#FFB6C1;">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4><b>PENGELUARAN TAHUN INI</b></h4>
                        </div>
                        <div class="card-body" style="font-size: 20px">
                            {{ rupiah($pengeluaran_tahun_ini) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h4><i class=" fas fa-chart-pie"></i> STATISTIK PEMASUKAN PERKATEGORI</h4>
                        <button type="button" class="btn btn-info" id="toggleChartBtnPemasukan" onclick="toggleChartPemasukan()">Buka Chart</button>
                    </div>
                    <div class="card-body">
                        <div id="chartContainerPemasukan" style="display: none;">
                            @foreach ($debit as $hasil)
                            @php
                            $target = 10000000; // Target nominal 10 juta
                            $persentase = ($hasil->total_nominal / $target) * 100;
                            @endphp
                            <div style="display: flex; flex-direction: column; align-items: center;">
                                <h6 style="margin-bottom: 5px;">{{ $hasil->name }}</h6>
                                <div style="display: flex; align-items: center; width: 100%;">
                                    <span style="margin-right: 5px; margin-left:5px">{{ rupiah($hasil->total_nominal) }}</span>
                                    <div class="progress" role="progressbar" aria-label="Info example" aria-valuenow="{{ $persentase }}" aria-valuemin="0" aria-valuemax="100" style="flex: 1; margin-right: 5px;">
                                        <div class="progress-bar bg-info text-dark" style="width: {{ $persentase }}%; padding: 5px; text-align: center;">
                                        </div>
                                    </div>
                                    <span style="margin-left: 5px; margin-right:5px">{{ rupiah($target) }}</span>
                                </div>
                            </div>
                            <div class="mb-3"></div>
                            @endforeach
                        </div>
                        <canvas id="financeChartPemasukan" width="100%" height="40"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h4><i class="fas fa-chart-pie"></i> STATISTIK PENGELUARAN PERKATEGORI</h4>
                        <button type="button" class="btn btn-info" id="toggleChartBtn" onclick="toggleChart()">Buka Chart</button>
                    </div>
                    <div class="card-body">
                        <div id="chartContainer" style="display: none;">
                            @foreach ($credit as $hasil)
                            @php
                            $target = 10000000; // Target nominal 10 juta
                            $persentase = ($hasil->total_nominal / $target) * 100;
                            @endphp
                            <div style="display: flex; flex-direction: column; align-items: center;">
                                <h6 style="margin-bottom: 5px;">{{ $hasil->name }}</h6>
                                <div style="display: flex; align-items: center; width: 100%;">
                                    <span style="margin-right: 5px; margin-left:5px;">{{ rupiah($hasil->total_nominal) }}</span>
                                    <div class="progress" role="progressbar" aria-label="Info example" aria-valuenow="{{ $persentase }}" aria-valuemin="0" aria-valuemax="100" style="flex: 1; margin-right: 5px; width:500px">
                                        <div class="progress-bar bg-danger text-dark" style="width: {{ $persentase }}%; padding: 5px; text-align: center;">
                                        </div>
                                    </div>
                                    <span style="margin-left: 5px; margin-right:5px">{{ rupiah($target) }}</span>
                                </div>
                            </div>
                            <div class="mb-3"></div>
                            @endforeach
                        </div>
                        <canvas id="financeChart" width="100%" height="40"></canvas>
                    </div>
                </div>
            </div>
        </div>



    </section>
</div>

<!-- open and close chart pemasukan-->
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
<!-- end -->

<!-- open and close chart pengeluaran -->
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
<!-- end -->

<!-- popup akun berhasil -->
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
<!-- end -->

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