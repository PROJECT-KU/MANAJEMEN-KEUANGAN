@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Dashboard | MIS
@stop

<style>
    .bar-chart-container {
        width: 100%;
        max-width: 100%;
        padding: 20px;
        box-sizing: border-box;
    }

    .bar-chart {
        display: flex;
        align-items: flex-end;
        height: 200px;
        border-left: 2px solid #333;
        border-bottom: 2px solid #333;
        padding-left: 10px;
        padding-bottom: 10px;
        width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }

    .bar {
        flex-grow: 1;
        margin: 0 5px;
        position: relative;
        transition: background-color 0.3s;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }

    .increase {
        background-color: rgba(76, 175, 80, 0.7);
    }

    .increase:hover {
        background-color: rgba(76, 175, 80, 1);
    }

    .decrease {
        background-color: rgba(244, 67, 54, 0.7);
        transform: translateY(100%);
    }

    .decrease:hover {
        background-color: rgba(244, 67, 54, 1);
    }

    .bar-label {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        text-align: center;
        font-size: 12px;
        white-space: nowrap;
    }

    .total-salary {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-top: -5px;
        font-weight: bold;
        font-size: 14px;
        display: none;
        /* Initially hidden */
    }

    .month-labels {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
        margin-left: 10px;
    }

    .month {
        flex: 1;
        text-align: center;
        font-size: 12px;
    }

    /* Media Queries for Responsive Design */
    @media (max-width: 768px) {
        .bar-label {
            display: none;
            /* Hide total salary above the bar on mobile */
        }

        .bar:hover .total-salary {
            display: block;
            /* Show only when hovered */
        }

        .bar-chart {
            height: 140px;
        }

        .month {
            font-size: 5px;
        }

        .bar {
            margin: 0 2px;
        }
    }

    @media (max-width: 480px) {
        .bar-chart {
            height: 120px;
        }

        .month {
            font-size: 5px;
        }

        .bar {
            margin: 0 1px;
        }
    }
</style>



@section('content')

<div class="main-content">
    <section class="section">
        <div class="section-body">

            <!--================== ASSIGN TASK ==================-->
            @php
            $user = Auth::user();
            $userId = $user->id;

            if ($user->level === 'manager') {
            $tasks = DB::table('todolist')->where('status', 'Assign Task')->get();
            $taskCount = $tasks->count();
            } else {
            $tasks = DB::table('todolist')
            ->where('status', 'Assign Task')
            ->where(function ($query) use ($userId) {
            $query->where('user_id', $userId)->orWhere('user_id_kedua', $userId);
            })
            ->get();
            $taskCount = $tasks->count();
            }
            @endphp

            @if ($taskCount > 0)
            <div class="alert alert-info mt-3" role="alert" style="text-align: center;">
                <h5 class="text-center">Notifikasi Tugas</h5>
                <p style="font-size: 20px;">Anda memiliki Jumlah tugas yang harus dikerjakan sebanyak: {{ $taskCount }}<br>Anda bisa melihat tugas di menu To Do List</p>
            </div>
            @endif
            <!--================== END ==================-->

            <!--================== AKUN BELUM DI VERIFIKASI ==================-->
            @if (Auth::user()->status == "nonactive")
            <div class="alert alert-danger mt-5" role="alert" style="text-align: center;">
                <b style="font-size: 20px;">Akun Anda Telah Di Non Active kan</b><br>Silahkan Hubungin Admin Untuk meng Active kan Akun!
            </div>
            @endif
            <!--================== END ==================-->

            <!--================== AKUN DINONAKTIFKAN ==================-->
            @if (Auth::user()->status === 'off')
            <div class="alert alert-danger" role="alert" style="text-align: center;">
                <b style="font-size: 20px;">Akun Anda Telah Di Nonaktifkan!</b><br>Silahkan Hubungin Admin Untuk Aktifkan Akun!
            </div>
            @endif
            <!--================== END ==================-->

            <!--================== PERJALANAN DINAS AJUKAN ==================-->
            <!-- @if ($hasAjukan)
            <div class="alert alert-danger" role="alert" style="text-align: center; padding: 15px 0 0 0; width: 100%; box-sizing: border-box; border-radius: 10px;">
                <b style="font-size: 20px;">Terdapat Perjalanan Dinas dengan status "PENGAJUAN"!</b>
                <br>Silahkan periksa dan ambil tindakan yang diperlukan.<br>
                <a href="{{ route('account.PerjalananDinas.index') }}" style="text-align: center; text-decoration: none; display: block; width: 100%; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
                    <button type="button" class="btn btn-info" style="width: 100%; border-radius: 0 0 10px 10px; text-decoration: none; color: white; margin-top:10px; font-size:15px;">
                        LIHAT PENGAJUAN
                    </button>
                </a>
            </div>
            @endif -->
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

                <!--================== TOTAL KARYAWAN ==================-->
                @if (Auth::user()->level === 'manager')
                <div class="row"> <!-- Tambahkan row untuk mengatur tata letak grid -->
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12" id="totalKaryawanCard">
                        <div class="card card-statistic-2">
                            <div class="card-icon shadow-primary" style="background-color: #5F9EA0;">
                                <i class="fas fa-users" style="margin-top: 13px;"></i>
                            </div>
                            <div class="card-wrap flex-column">
                                <div class="card-header">
                                    <h4>Total Karyawan</h4>
                                </div>
                                <div class="card-body">
                                    <h5>{{ $totalKaryawan }} Karyawan</h5> <!-- Menampilkan jumlah total karyawan -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12" id="totalKaryawanAktifCard">
                        <div class="card card-statistic-2">
                            <div class="card-icon shadow-primary" style="background-color: #008000;">
                                <i class="fas fa-user-check" style="margin-top: 13px;"></i>
                            </div>
                            <div class="card-wrap flex-column">
                                <div class="card-header">
                                    <h4>Total Karyawan Aktif</h4>
                                </div>
                                <div class="card-body">
                                    <h5>{{ $totalKaryawanAktif }} Karyawan Aktif</h5> <!-- Menampilkan jumlah total karyawan aktif -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12" id="totalKaryawanNonAktifCard">
                        <div class="card card-statistic-2">
                            <div class="card-icon shadow-primary" style="background-color: #800000;">
                                <i class="fas fa-user-times" style="margin-top: 13px;"></i>
                            </div>
                            <div class="card-wrap flex-column">
                                <div class="card-header">
                                    <h4>Total Karyawan Non Aktif</h4>
                                </div>
                                <div class="card-body">
                                    <h5>{{ $totalKaryawanNonAktif }} Karyawan Non Aktif</h5> <!-- Menampilkan jumlah total karyawan nonaktif -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <!--================== END ==================-->

                @if (Auth::user()->status === 'nonactive' || is_null(Auth::user()->status) || is_null(Auth::user()->email_verified_at))
                @else
                <!--================== PRESENSI KARYAWAN ==================-->
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <div class="card card-statistic-2">
                            <div class="card-icon shadow-warning" style="background-color: #FF7F50;">
                                <img alt="image" src="{{ asset('assets/img/hadir.png') }}" style="width: 40px; margin-top: 6px;">
                            </div>

                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>PRESENSI KEHADIRAN</h4>
                                </div>

                                @php
                                $todayPresensi = \App\Presensi::where('user_id', Auth::user()->id)
                                ->whereDate('created_at', now()->toDateString())
                                ->first();
                                @endphp
                                @if ($todayPresensi && is_null($todayPresensi->status_pulang) && date('H:i:s') >= '07:00:00' && date('H:i:s') <= '22:00:00' ) <div class="d-flex mx-1 mt-2 mb-2">
                                    <button href="{{ route('account.presensi.create') }}" class="btn btn-secondary mr-2" style="flex-grow: 1; margin-left: -5px; padding-top: 10px; padding-bottom:10px; font-size: 15px; font-family:'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;" disabled>
                                        MASUK
                                    </button>
                                    <a href="{{ route('account.presensi.edit', $todayPresensi->id) }}" class="btn btn-sm btn-warning" style="flex-grow: 1; padding-top: 10px; padding-bottom:10px; font-size: 15px; font-family:'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif">
                                        PULANG
                                    </a>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="alert alert-success mb-0" role="alert" style="flex-grow: 1;">
                                    Selamat Bekerja!
                                </span>
                            </div>
                            @elseif (!$todayPresensi && date('H:i:s') >= '07:00:00' && date('H:i:s') <= '22:00:00' ) <div class="d-flex mx-1 mt-2 mb-2">
                                <a href="{{ route('account.presensi.create') }}" class="btn btn-primary mr-2" style="flex-grow: 1; margin-left: -5px; padding-top: 10px; padding-bottom:10px; font-size: 15px; font-family:'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif; width: 100%">
                                    MASUK
                                </a>
                                <button href="{{ route('account.presensi.create') }}" class="btn btn-secondary" style="flex-grow: 1; padding-top: 10px; padding-bottom:10px; font-size: 15px; font-family:'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif; width: 100%" disabled>
                                    PULANG
                                </button>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="alert alert-danger mb-0" role="alert" style="flex-grow: 1;">
                                Anda Belum Melakukan Presensi Pada Hari Ini!
                            </span>
                        </div>
                        @else
                        <div class="d-flex mx-1 mt-2 mb-2">
                            <button href="{{ route('account.presensi.create') }}" class="btn btn-secondary mr-2" style="flex-grow: 1; margin-left: -5px; padding-top: 10px; padding-bottom:10px; font-size: 15px; font-family:'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif; width: 100%" disabled>
                                MASUK
                            </button>
                            <button href="{{ route('account.presensi.create') }}" class="btn btn-secondary" style="flex-grow: 1; padding-top: 10px; padding-bottom:10px; font-size: 15px; font-family:'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif; width: 100%" disabled>
                                PULANG
                            </button>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="alert alert-info mb-0" role="alert" style="flex-grow: 1;">
                                Selesai Bekerja!
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
        </div>
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


        <!--================== TAMPILAN ARTIKEL ==================-->
        <!-- <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; background-color:#6495ED">
                        <h4 style="color: white;"><i class="fas fa-chart-pie"></i> ARTIKEL TERBARU</h4>
                    </div>
                    <div class="card-body">
                        <div class="artikel-list mt-4">
                            @foreach ($artikel as $item)
                            <div class="artikel-item mb-3">
                                <h5>{{ $item->judul }}</h5>
                                <p>Jumlah Dilihat: {{ $item->dilihat }}</p>
                            </div>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-center mt-4">
                            {{ $artikel->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
        <!--================== END ==================-->
        @endif

        <!--================== CHART GAJI PERBULAN SELAMA SETAHUN ==================-->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-0"
                    style="display: flex; justify-content: space-between; align-items: center; background-color: rgba(169, 169, 169, 0.4);">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title"><i class="fas fa-dollar-sign"></i> Total Gaji Per Bulan</h4>
                    </div>
                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseCardGaji"
                        aria-expanded="true" aria-controls="collapseCardGaji">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>

                <!-- Collapsible Content -->
                <div id="collapseCardGaji" class="collapse show">
                    <div class="card-body chartgaji">
                        <div class="d-flex">
                            <p class="d-flex flex-column">
                                <span class="total-salary-above-label">Total Gaji Tahun {{ $currentYear }}</span>
                                <span class="text-bold text-lg total-salary-above">
                                    Rp {{ number_format($totalGaji, 0, ',', '.') }}
                                </span>
                            </p>
                            <p class="ml-auto d-flex flex-column text-right">
                                <span class="text-success">
                                    <i class="fas fa-arrow-up"></i>
                                    {{ $totalGaji > 0 ? number_format(($totalGaji / 12), 0, ',', '.') : '0' }}
                                </span>
                                <span class="text-muted">Rata-rata per Bulan</span>
                            </p>
                        </div>

                        <!-- Bar Chart -->
                        <div class="bar-chart">
                            @foreach($salaryData as $month => $salary)
                            <div class="bar {{ $salary >= 0 ? 'increase' : 'decrease' }}"
                                style="height: {{ $totalGaji > 0 ? abs(($salary / $totalGaji) * 500) : 0 }}%;">
                                <span class="bar-label">Rp {{ number_format($salary, 0, ',', '.') }}</span>
                                <span class="total-salary">Rp {{ number_format($salary, 0, ',', '.') }}</span>
                            </div>
                            @endforeach
                        </div>

                        <div class="month-labels">
                            @foreach($salaryData as $month => $salary)
                            <span class="month">{{ date('F', mktime(0, 0, 0, $month, 1)) }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--================== END ==================-->

        <!--================== PENGGUNA BARU ==================-->
        @if (Auth::user()->level == 'manager' || Auth::user()->level == 'admin')
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-0"
                    style="justify-content: space-between; align-items: center; background-color: rgba(169, 169, 169, 0.4);">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title"><i class="fas fa-user"></i> Pengguna Baru</h4>
                    </div>
                    <!-- Tombol untuk membuka/menutup card -->
                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapsePenggunaBaru"
                        aria-expanded="true" aria-controls="collapsePenggunaBaru">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>

                <!-- Card body yang dapat di-collapse -->
                <div id="collapsePenggunaBaru" class="collapse show">
                    <div class="card-body">
                        <div class="row" style="margin: 3px;">
                            @foreach($users as $user)
                            @if ($loop->iteration <= 6)
                                <div class="col-lg-4 col-md-12 mt-4">
                                <div class="card text-center card-hover" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                                    @if ($user->gambar == null)
                                    <a class="mt-3" href="{{ asset('assets/img/profil/no-image.jpg') }}" data-lightbox="{{ $user->id }}">
                                        @else
                                        <a class="mt-3" href="{{ asset('assets/img/profil/' . $user->gambar) }}" data-lightbox="{{ $user->id }}">
                                            @endif
                                            <div class="thumbnail-circle">
                                                <img style="width: 100px; height: 100px;" src="{{ $user->gambar ? asset('assets/img/profil/' . $user->gambar) : asset('assets/img/profil/no-image.jpg') }}" alt="Gambar Pengguna" class="card-img-top rounded-circle">
                                            </div>
                                        </a>
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $user->full_name }}</h5>
                                        </div>
                                        <a href="{{ route('account.pengguna.edit', $user->id) }}" class="btn btn-info">Lihat Detail</a>
                                </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
</div>
@endif

<!--================== END ==================-->

</section>
</div>

<!--================== CEK DIVACE APAKAH PWA ATAU WEBSITE ==================-->
<!-- <script>
    function isMobileDevice() {
        return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
    }

    function toggleElementBasedOnDevice() {
        var totalGajiCard = document.getElementById('totalGajiCard');
        var MenuPwaCard = document.getElementById('MenuPwaCard');
        var SisaSaldoBulanIniCard = document.getElementById('SisaSaldoBulanIniCard');
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
            totalGajiCard.style.display = 'none';
            MenuPwaCard.style.display = 'block';
            SisaSaldoBulanIniCard.style.display = 'block';
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
            totalGajiCard.style.display = 'block';
            MenuPwaCard.style.display = 'none';
            SisaSaldoBulanIniCard.style.display = 'block';
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

    window.addEventListener('load', toggleElementBasedOnDevice);
</script> -->
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
@if (is_null(auth()->user()->email_verified_at))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Use SweetAlert to display the message if the email is not verified
    Swal.fire({
        icon: 'warning',
        title: 'Belum Verifikasi Email',
        text: 'Silahkan verifikasi email untuk dapat menggunakan aplikasi ini',
        confirmButtonText: 'OK'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to the profile page when "OK" is clicked
            window.location.href = "{{ route('account.profil.show', ['id' => Auth::user()->id]) }}";
        }
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