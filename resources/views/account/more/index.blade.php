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
        <div class="section-body">

            <!--================== AKUN BELUM DI VERIFIKASI ==================-->
            @if (!Auth::user()->email_verified_at)
            <div class="alert alert-danger" role="alert" style="text-align: center;">
                <b style="font-size: 20px;">Akun Anda Belum Diverifikasi Oleh Admin!</b><br>Silahkan Hubungin Admin Untuk Verifikasi Akun!
            </div>
            @endif
            <!--================== END ==================-->

            <!--================== AKUN DINONAKTIFKAN ==================-->
            @if (Auth::user()->status === 'off')
            <div class="alert alert-danger" role="alert" style="text-align: center;">
                <b style="font-size: 20px;">Akun Anda Di Nonaktifkan Sementara!</b><br>Silahkan Hubungin Admin Untuk Aktifkan Akun!
            </div>
            @endif
            <!--================== END ==================-->

            <!--================== MASA SEWA AKUN AKAN HABIS ==================-->
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
            <!--================== END ==================-->

            <!--================== JIKA DATA DIRI MASIH ADA YANG KOSONG ==================-->
            @if (Auth::user()->company === null || Auth::user()->telp === null || Auth::user()->nik === null || Auth::user()->norek === null || Auth::user()->bank === null || Auth::user()->gambar == null || Auth::user()->jobdesk == null)
            <div class="alert alert-warning" role="alert" style="text-align: center;">
                <b style="font-size: 20px;">DATA DIRI</b><br>
                <p style="font-size: 15px;">Data diri anda masih ada yang kosong! Silahkan Lengkapi data diri anda terlebih dahulu!</p>
            </div>
            @endif
            <!--================== END ==================-->

            <!--================== MAINTENANCE ==================-->
            @if (!$maintenances->isEmpty())
            @foreach($maintenances as $maintenance)
            @if ($maintenance->status === 'aktif' && (now() <= Carbon\Carbon::parse($maintenance->end_date)->endOfDay()))
                <div class="alert alert-danger" role="alert" style="text-align: center;">
                    <b style="font-size: 25px; text-transform:uppercase">{{ $maintenance->title }}</b><br>
                    <!-- <img style="width: 100px; height:100px;" src="{{ asset('images/' . $maintenance->gambar) }}" alt="Gambar Presensi" class="img-thumbnail"> -->
                    <p style="font-size: 20px;" class="mt-2">{{ $maintenance->note }}</p>
                    @if ($maintenance->start_date !== null)
                    <p style="font-size: 15px;">Dari Tanggal {{ \Carbon\Carbon::parse($maintenance->start_date)->isoFormat('D MMMM YYYY HH:mm') }} - {{ \Carbon\Carbon::parse($maintenance->end_date)->isoFormat('D MMMM YYYY HH:mm') }}</p>
                    @endif
                </div>
                @endif
                @endforeach
                @endif
                <!--================== END ==================-->

                <!--================== TOTAL GAJI TAHUN INI ==================-->
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" id="totalGajiCard">
                    <div class="card card-statistic-2">
                        <div class="card-icon shadow-primary" style="background-color: #5F9EA0;">
                            <i class="fas fa-dollar-sign" style="margin-top: 13px;"></i>
                        </div>
                        <div class="card-wrap flex-column">
                            <div class="card-header">
                                <h4>TOTAL GAJI TAHUN INI</h4>
                            </div>

                            <div class="card-body d-flex align-items-center" id="totalgaji">
                                <span style="margin-left: -30px; font-size: 1em;">******</span>
                                <i class="fas fa-eye totalgaji-toggle ml-2" id="totalgaji-toggle" onclick="toggleTotalGaji()"></i>
                            </div>

                            <div class="d-flex" style="width: 100%;">
                                @if ($gaji->isEmpty())
                                <div class="alert alert-info mb-0" role="alert" style="flex-grow: 1;">
                                    Belum ada data gaji untuk bulan ini. Mohon bersabar.
                                </div>
                                @else
                                @php
                                $belumTerbayarkan = false;
                                foreach ($gaji as $item) {
                                if ($item->status != 'terbayar') {
                                $belumTerbayarkan = true;
                                break;
                                }
                                }
                                @endphp
                                @if ($belumTerbayarkan)
                                <div class="alert alert-warning mb-0" role="alert" style="flex-grow: 1;">
                                    Gaji pada bulan ini belum terbayarkan. Sabar ya, semoga segera cair!
                                </div>
                                @else
                                <div class="alert alert-success mb-0" role="alert" style="flex-grow: 1;">
                                    Gaji pada bulan ini sudah terbayarkan. Terima kasih atas kerja keras Anda!
                                </div>
                                @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!--================== END ==================-->
        </div>


        <!--================== MANU DI PWA ==================-->
        <div class="row" id="MenuPwaCard">
            <div class="col-md-12">
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
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #8A2BE2, #800080, #4B0082, #483D8B); text-align: center;">
                            <a href="{{ route('account.debit.index') }}"><i class="fas fa-wallet" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left: 1px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px;">Uang</span>
                            <span style="font-size: 16px;">Masuk</span>
                        </div>
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #FF6347, #FF4500, #FF0000, #B22222); text-align: center;">
                            <a href="{{ route('account.credit.index') }}"><i class="fas fa-hand-holding-usd" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left: 1px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px;">Uang</span>
                            <span style="font-size: 16px;">Keluar</span>
                        </div>
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #7FFF00, #32CD32, #008000, #006400); text-align: center;">
                            <a href="{{ route('account.laporan_semua.index') }}"><i class="fas fa-chart-line" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left: -1px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px;">Laporan</span>
                        </div>
                        <div class="card-icon shadow-primary rounded-circle" style="background-image: linear-gradient(to bottom, #FFC0CB, #FFB6C1, #FF69B4, #FF1493); text-align: center;">
                            <a href="{{ route('account.laporan_credit.index') }}"><i class="fas fa-th-large" style="margin-top: 13px; margin-bottom: 8px; font-size: 24px; width: 24px; margin-left: 1px;"></i></a>
                            <span style="font-size: 16px; display: inline-block; margin-bottom: -30px;">More</span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!--================== END ==================-->


        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                <div class="card card-statistic-2" id="SisaSaldoBulanIniCard">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt" style="margin-top: 13px;"></i>
                    </div>
                    <div class="card-wrap" style="height: 100px;">
                        <div class="card-header">
                            <h4>SISA SALDO BULAN INI</h4>
                        </div>
                        <div class="card-body" style="font-size: 20px; height: 100%;">
                            {{ rupiah($saldo_bulan_ini) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                <div class="card card-statistic-2" id="SisaSaldoBulanLaluCard">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt" style="margin-top: 13px;"></i>
                    </div>
                    <div class="card-wrap" style="height: 100px;">
                        <div class="card-header">
                            <h4>SISA SALDO BULAN LALU</h4>
                        </div>
                        <div class="card-body" style="font-size: 20px; height: 100%;">
                            {{ rupiah($saldo_bulan_lalu) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- <div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; background-color:#6495ED">
                <h4 style="color: white;">AKSES CEPAT</h4>
            </div>

            <div class="card-body">
                <div class="row">

                    <div class="col-md-3">
                        <center>
                            <div class="form-group">
                                <a href="{{ route('account.debit.create') }}">
                                    <img alt="image" src="{{ asset('assets/img/moneyin.png') }}" style="margin-left: 30px;">
                                </a>
                                <br>
                                <label class="text-center ml-4">Tambah Uang Masuk</label>
                            </div>
                        </center>
                    </div>

                    <div class="col-md-3">
                        <center>
                            <div class="form-group">
                                <a href="{{ route('account.credit.create') }}">
                                    <img alt="image" src="{{ asset('assets/img/moneyout.png') }}" style="margin-left: 25px;">
                                </a>
                                <br>
                                <label class="text-center ml-4">Tambah Uang Keluar</label>
                            </div>
                        </center>
                    </div>

                    @if (Auth::user()->level == 'karyawan' || Auth::user()->level == 'staff' || Auth::user()->level == 'trainer')
                    @php
                    $todayPresensi = \App\Presensi::where('user_id', Auth::user()->id)
                    ->whereDate('created_at', now()->toDateString())
                    ->first();
                    @endphp
                    @if ($todayPresensi && is_null($todayPresensi->status_pulang))
                    <div class="col-md-3">
                        <center>
                            <div class="form-group">
                                <a href="{{ route('account.presensi.edit', $todayPresensi->id) }}">
                                    <img alt="image" src="{{ asset('assets/img/presensi.png') }}" style="margin-left: 10px;">
                                </a>
                                <br>
                                <label class="text-center ml-3">Presensi Kepulangan</label>
                            </div>
                        </center>
                    </div>
                    @elseif (!$todayPresensi)
                    <div class="col-md-3">
                        <center>
                            <div class="form-group">
                                <a href="{{ route('account.presensi.create') }}">
                                    <img alt="image" src="{{ asset('assets/img/hadir.png') }}" style="margin-left: 10px;">
                                </a>
                                <br>
                                <label class="text-center ml-3">Presensi Kehadiran</label>
                            </div>
                        </center>
                    </div>
                    @else
                    <div class="col-md-3">
                        <center>
                            <div class="form-group">
                                <a href="#">
                                    <img alt="image" src="{{ asset('assets/img/presensiselesai.png') }}" style="margin-left: 10px;">
                                </a>
                                <br>
                                <label class="text-center ml-3">Presensi Selesai</label>
                            </div>
                        </center>
                    </div>
                    @endif
                    @endif
                    @if (Auth::user()->level == 'manager')
                    <div class="col-md-3">
                        <center>
                            <div class="form-group">
                                <a href="{{ route('account.presensi.create') }}">
                                    <img alt="image" src="{{ asset('assets/img/presensi.png') }}" style="margin-left: 10px;">
                                </a>
                                <br>
                                <label class="text-center ml-3">Tambah Presensi</label>
                            </div>
                        </center>
                    </div>
                    @endif

                    @if (Auth::user()->level == 'karyawan' || Auth::user()->level == 'trainer')
                    <div class="col-md-3">
                        <center>
                            <div class="form-group">
                                <a href="{{ route('account.gaji.index') }}">
                                    <img alt="image" src="{{ asset('assets/img/gaji.png') }}" style="margin-left: 10px;">
                                </a>
                                <br>
                                <label class="text-center ml-3">List Gaji Karyawan</label>
                            </div>
                        </center>
                    </div>
                    @else
                    <div class="col-md-3">
                        <center>
                            <div class="form-group">
                                <a href="{{ route('account.gaji.create') }}">
                                    <img alt="image" src="{{ asset('assets/img/gaji.png') }}" style="margin-left: 10px;">
                                </a>
                                <br>
                                <label class="text-center ml-3">Tambah Gaji Karyawan</label>
                            </div>
                        </center>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div> -->


        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="card card-statistic-2" style="background-color:#AFEEEE;" id="PemasukanHariIniCard">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt" style="margin-top: 13px;"></i>
                    </div>
                    <div class="card-wrap" style="height: 100px;">
                        <div class="card-header">
                            <h4><b>PEMASUKAN HARI INI</b></h4>
                        </div>
                        <div class="card-body" style="font-size: 20px; height: 100%;">
                            {{ rupiah($Pemasukan_hari_ini) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="card card-statistic-2" style="background-color:#AFEEEE;" id="PemasukanBulanIniCard">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt" style="margin-top: 13px;"></i>
                    </div>
                    <div class="card-wrap" style="height: 100px;">
                        <div class="card-header">
                            <h4><b>PEMASUKAN BULAN INI</b></h4>
                        </div>
                        <div class="card-body" style="font-size: 20px; height: 100%;">
                            {{ rupiah($pemasukan_bulan_ini) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="card card-statistic-2" style="background-color:#AFEEEE;" id="PemasukanTahunIniCard">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt" style="margin-top: 13px;"></i>
                    </div>
                    <div class="card-wrap" style="height: 100px;">
                        <div class="card-header">
                            <h4><b>PEMASUKAN TAHUN INI</b></h4>
                        </div>
                        <div class="card-body" style="font-size: 20px; height: 100%;">
                            {{ rupiah($pemasukan_tahun_ini) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="card card-statistic-2" style="background-color:#FFB6C1;" id="PengeluaranHariIniCard">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt" style="margin-top: 13px;"></i>
                    </div>
                    <div class="card-wrap" style="height: 100px;">
                        <div class="card-header">
                            <h4><b>PENGELUARAN HARI INI</b></h4>
                        </div>
                        <div class="card-body" style="font-size: 20px;">
                            {{ rupiah($pengeluaran_hari_ini) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="card card-statistic-2" style="background-color:#FFB6C1;" id="PengeluaranBulanIniCard">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt" style="margin-top: 13px;"></i>
                    </div>
                    <div class="card-wrap" style="height: 100px;">
                        <div class="card-header">
                            <h4><b>PENGELUARAN BULAN INI</b></h4>
                        </div>
                        <div class="card-body" style="font-size: 20px;">
                            {{ rupiah($pengeluaran_bulan_ini) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="card card-statistic-2" style="background-color:#FFB6C1;" id="PengeluaranTahunIniCard">
                    <div class="card-icon shadow-primary bg-primary">
                        <i class="fas fa-money-check-alt" style="margin-top: 13px;"></i>
                    </div>
                    <div class="card-wrap" style="height: 100px;">
                        <div class="card-header">
                            <h4><b>PENGELUARAN TAHUN INI</b></h4>
                        </div>
                        <div class="card-body" style="font-size: 20px;">
                            {{ rupiah($pengeluaran_tahun_ini) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="StatistikPemasukan">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; background-color:#6495ED">
                        <h4 style="color: white;"><i class=" fas fa-chart-pie"></i> STATISTIK PEMASUKAN PERKATEGORI</h4>
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

        <div class="row" id="StatistikPengeluaran">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; background-color:#6495ED">
                        <h4 style="color: white;"><i class="fas fa-chart-pie"></i> STATISTIK PENGELUARAN PERKATEGORI</h4>
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

        @if (Auth::user()->level == 'manager' || Auth::user()->level == 'admin' )
        <div class="row" id="PenggunaBaru">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; background-color:rgba(169, 169, 169, 0.4);">
                        <h4><i class="fas fa-user"></i> PENGGUNA BARU</h4>
                    </div>
                    <div class="row" style="margin: 10px;">
                        @foreach($users as $user)
                        @if ($loop->iteration <= 6) <div class="col-md-4 mb-4">
                            <div class="card text-center card-hover">
                                @if ($user->gambar == null)
                                <a class="mt-3" href="{{ asset('assets/img/avatar/avatar-1.PNG') }}" data-lightbox="{{ $user->id }}">
                                    @else
                                    <a class="mt-3" href="{{ asset('images/' . $user->gambar) }}" data-lightbox="{{ $user->id }}">
                                        @endif
                                        <div class="thumbnail-circle">
                                            <img style="width: 100px; height: 100px;" src="{{ $user->gambar ? asset('images/' . $user->gambar) : asset('assets/img/avatar/avatar-1.PNG') }}" alt="Gambar Pengguna" class="card-img-top rounded-circle">
                                        </div>
                                    </a>
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $user->full_name }}</h5>
                                    </div>
                            </div>

                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
        @endif


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