@extends('layouts.account')

@section('title')
Dashboard - UANGKU
@stop

@section('content')

<script>

</script>

<div class="main-content">
    <section class="section">
        <div class=" col-lg-12 col-md-4 col-sm-4 col-xs-4">
            @if (!Auth::user()->email_verified_at)
            <div class="alert alert-danger" role="alert" style="text-align: center;">
                <b>Akun Anda Belum Diverifikasi Oleh Admin!</b><br>Silahkan Hubungin Admin Untuk Verifikasi Akun!
            </div>
            @endif
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
                    <div class="card-header">
                        <h4><i class="fas fa-chart-pie"></i> STATISTIK KEUANGAN DALAM 1 TAHUN</h4>
                    </div>

                    <div class="card-body">
                        <div id="container"></div>
                    </div>
                </div>
            </div>
        </div>


    </section>
</div>
@stop