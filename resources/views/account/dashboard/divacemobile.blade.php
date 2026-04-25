@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Dashboard | MIS
@stop

{{-- Vendor CSS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5253 100%);
        --success-gradient: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        --warning-gradient: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
        --glass-bg: rgba(255, 255, 255, 0.9);
        --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        --card-hover-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
    }

    /* Core Card Design */
    .modern-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: var(--card-shadow);
        padding: 24px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
    }

    .modern-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-hover-shadow);
    }

    /* Stats Icon Styling */
    .icon-box {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    /* Attendance Buttons */
    .presensi-btn-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .btn-modern {
        padding: 14px;
        border-radius: 16px;
        font-weight: 700;
        text-align: center;
        border: none;
        transition: 0.3s ease;
        text-decoration: none !important;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-in { background: var(--success-gradient); color: white; }
    .btn-out { background: var(--danger-gradient); color: white; }
    .btn-disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
    .btn-modern:not(.btn-disabled):hover { opacity: 0.9; transform: scale(1.02); }

    /* Salary Chart Components */
    .chart-container {
        height: 200px;
        display: flex;
        align-items: flex-end;
        gap: 8px;
        padding-top: 20px;
    }

    .bar-modern {
        flex: 1;
        background: var(--primary-gradient);
        border-radius: 8px 8px 4px 4px;
        position: relative;
        min-height: 5px;
        transition: height 0.6s ease;
    }

    .bar-modern:hover::after {
        content: attr(data-value);
        position: absolute;
        top: -40px;
        left: 50%;
        transform: translateX(-50%);
        background: #1e293b;
        color: white;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 11px;
        white-space: nowrap;
        z-index: 10;
    }

    /* Layout Spacing */
    .section-body { padding-top: 20px; }
    .mb-24 { margin-bottom: 24px; }

    @media (max-width: 576px) {
        .modern-card { padding: 20px; }
    }

    /* Enhanced Article Slider */
    .article-slide-card {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        height: 180px;
        border: none;
        transition: all 0.4s ease;
    }

    .article-image-wrapper {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    .article-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 20px 15px 15px;
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 60%, transparent 100%);
        z-index: 2;
        transition: all 0.3s ease;
    }

    .article-slide-card:hover {
        transform: scale(0.98);
    }

    .article-slide-card:hover .article-overlay {
        padding-bottom: 25px;
        background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.5) 70%, transparent 100%);
    }

    .article-title {
        color: white !important;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
        text-decoration: none !important;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .swiper-pagination-bullet-active {
        background: var(--primary-gradient) !important;
        width: 20px !important;
        border-radius: 5px !important;
    }
</style>

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">

            @include('account.dashboard.task-slider-mobile')

            @if (Auth::user()->level === 'manager')
            <div class="row mb-24">
                @php
                    $stats = [
                        ['label' => 'Total Karyawan', 'value' => $totalKaryawan, 'icon' => 'fa-users', 'bg' => '#4facfe'],
                        ['label' => 'Aktif', 'value' => $totalKaryawanAktif, 'icon' => 'fa-user-check', 'bg' => '#43e97b', 'color' => 'text-success'],
                        ['label' => 'Non-Aktif', 'value' => $totalKaryawanNonAktif, 'icon' => 'fa-user-times', 'bg' => '#f093fb', 'color' => 'text-danger']
                    ];
                @endphp
                @foreach($stats as $stat)
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="modern-card d-flex align-items-center">
                        <div class="icon-box mr-3" style="background: {{ $stat['bg'] }}">
                            <i class="fas {{ $stat['icon'] }}"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small font-weight-bold">{{ $stat['label'] }}</p>
                            <h3 class="font-weight-bold mb-0 {{ $stat['color'] ?? '' }}">{{ $stat['value'] }}</h3>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <div class="row mb-24">
                @if (Auth::user()->level !== 'user')
                <div class="col-lg-6 mb-4">
                    <div class="modern-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="font-weight-bold mb-0">Sistem Presensi</h5>
                            <span class="badge badge-light px-3 py-2" style="border-radius: 10px;">{{ date('d M Y') }}</span>
                            {{-- Penampung Jam Real-time --}}
                <span id="live-clock" class="font-weight-bold text-primary small" style="letter-spacing: 1px;">00:00:00</span>
                        </div>

                        @php
                            $todayPresensi = \App\Presensi::where('user_id', Auth::user()->id)
                                ->whereDate('created_at', now()->toDateString())->first();
                            $currentTime = date('H:i:s');
                            $isTimeRange = ($currentTime >= '07:00:00' && $currentTime <= '22:00:00');
                        @endphp

                        <div class="presensi-btn-container">
                            @if (!$todayPresensi && $isTimeRange)
                                <a href="{{ route('account.presensi.create') }}" class="btn-modern btn-in"><i class="fas fa-sign-in-alt"></i> Masuk</a>
                                <div class="btn-modern btn-disabled"><i class="fas fa-sign-out-alt"></i> Pulang</div>
                            @elseif ($todayPresensi && is_null($todayPresensi->status_pulang) && $isTimeRange)
                                <div class="btn-modern btn-disabled"><i class="fas fa-sign-in-alt"></i> Masuk</div>
                                <a href="{{ route('account.presensi.edit', $todayPresensi->id) }}" class="btn-modern btn-out"><i class="fas fa-sign-out-alt"></i> Pulang</a>
                            @else
                                <div class="btn-modern btn-disabled">Masuk</div>
                                <div class="btn-modern btn-disabled">Pulang</div>
                            @endif
                        </div>

                        @if($todayPresensi)
                        <div class="mt-4 p-3 border-0 rounded-xl bg-light text-center font-weight-bold {{ is_null($todayPresensi->status_pulang) ? 'text-success' : 'text-primary' }}">
                            <i class="fas {{ is_null($todayPresensi->status_pulang) ? 'fa-check-circle' : 'fa-briefcase' }} mr-2"></i>
                            {{ is_null($todayPresensi->status_pulang) ? 'Anda sudah absen masuk' : 'Tugas hari ini selesai' }}
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <div class="col-lg-6">
                    <div class="modern-card">
                        <h5 class="font-weight-bold mb-4">Akses Cepat</h5>
                        <div id="quick-access-wrapper">
                            @include('account.dashboard.menu-akses-cepat')
                        </div>
                    </div>
                </div>
            </div>

            {{-- Jarak Seragam Baris Izin & Cuti --}}
            <div class="row mb-24">
                <div class="col-md-6 mb-4 mb-md-0"> {{-- Margin bawah hanya di mobile agar tidak menumpuk --}}
                    <div class="modern-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="font-weight-bold text-muted mb-0">Izin Bulan Ini</h6>
                            <span class="text-danger font-weight-bold">{{ $totalIzin }} / 3</span>
                        </div>
                        <div class="progress mt-3 mb-2" style="height: 10px; border-radius: 10px; background: #f1f5f9;">
                            <div class="progress-bar" style="width: {{ ($totalIzin / 3) * 100 }}%; background: var(--danger-gradient);"></div>
                        </div>
                        <p class="small mb-0 {{ $totalIzin >= 3 ? 'text-danger' : 'text-muted' }}">
                            {{ $totalIzin >= 3 ? 'Batas maksimal tercapai' : 'Sisa kuota: ' . (3 - $totalIzin) }}
                        </p>
                    </div>
                </div>
            <div class="col-md-6">
    <div class="modern-card d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="font-weight-bold text-muted mb-0">Hak Cuti</h6>
            @php
                // Cek apakah sudah 1 tahun sejak email diverifikasi
                $verifiedAt = Auth::user()->email_verified_at;
                $isOneYear = $verifiedAt ? \Carbon\Carbon::parse($verifiedAt)->diffInYears(now()) >= 1 : false;
                
                // Variabel $bolehCuti digabung dengan syarat 1 tahun
                $hakCutiFinal = $bolehCuti && $isOneYear;
            @endphp
            <i class="fas fa-calendar-check {{ $hakCutiFinal ? 'text-success' : 'text-light' }}"></i>
        </div>
        <div class="mt-2">
            <h5 class="font-weight-bold mb-0">
                {{ $hakCutiFinal ? 'Siap Diajukan' : 'Belum Memenuhi Syarat' }}
            </h5>
            
            @if($hakCutiFinal)
                <a href="#" class="btn btn-primary btn-sm rounded-pill mt-2 px-3">Ajukan Sekarang</a>
            @else
                <p class="small text-muted mb-0 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    {{ !$isOneYear ? 'Minimal 1 tahun masa kerja (sejak verifikasi email)' : 'Syarat lainnya belum terpenuhi' }}
                </p>
            @endif
        </div>
    </div>
</div>
            </div>

            <div class="row mb-24">
                <div class="col-12">
                    <div class="modern-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="font-weight-bold mb-0"><i class="fas fa-newspaper mr-2 text-primary"></i> Artikel Terupdate</h5>
                            <a style="text-decoration:none" href="{{ route('account.Artikel.index') }}" class="text-primary small font-weight-bold">Lihat Semua</a>
                        </div>
                        
                        <div class="swiper mySwiper">
                            <div class="swiper-wrapper">
                                @foreach($artikel->take(6) as $item)
                                <div class="swiper-slide">
                                    <a href="{{ route('blog.topic.blog-single', ['id' => $item->id, 'token' => $item->token]) }}" class="article-slide-card d-block shadow-sm">
                                        <div class="article-image-wrapper">
                                            <img src="{{ asset('images/' . $item->gambar_depan) }}" class="w-100 h-100" style="object-fit: cover;">
                                        </div>
                                        <div class="article-overlay">
                                            <span class="badge badge-primary mb-2" style="font-size: 8px; opacity: 0.9;">Terbaru</span>
                                            <div class="article-title">
                                                {{ $item->judul }}
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                @endforeach
                            </div>
                            <div class="swiper-pagination" style="position: relative; margin-top: 25px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-24">
                <div class="col-12">
                    <div class="modern-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="font-weight-bold mb-0">Statistik Pendapatan</h5>
                                <p class="text-muted small">Total: Rp {{ number_format($totalGaji, 0, ',', '.') }}</p>
                            </div>
                            <div class="icon-box" style="background: var(--primary-gradient); width: 44px; height: 44px;">
                                <i class="fas fa-chart-line" style="font-size: 16px;"></i>
                            </div>
                        </div>
                        <div class="chart-container">
                            @php $maxS = count($salaryData) > 0 ? max($salaryData) : 1; @endphp
                            @foreach($salaryData as $month => $salary)
                                @php $h = ($salary / $maxS) * 100; @endphp
                                <div class="bar-modern" style="height: {{ max($h, 5) }}%;" data-value="Rp {{ number_format($salary, 0, ',', '.') }}"></div>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-between mt-3 px-2">
                            @foreach($salaryData as $month => $salary)
                                <span class="text-muted small font-weight-bold" style="flex: 1; text-align: center;">
                                    {{ substr(date('F', mktime(0, 0, 0, $month, 1)), 0, 3) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Swiper(".mySwiper", {
            slidesPerView: 1,
            spaceBetween: 20,
            autoplay: { delay: 4000, disableOnInteraction: false },
            pagination: { el: ".swiper-pagination", clickable: true },
            breakpoints: {
                640: { slidesPerView: 2 },
                1024: { slidesPerView: 3 }
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Live Clock Function
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const timeString = `${hours}:${minutes}:${seconds}`;
            
            const clockElement = document.getElementById('live-clock');
            if(clockElement) clockElement.textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock(); // Jalankan langsung saat load

        // Swiper Configuration (tetap seperti semula)
        new Swiper(".mySwiper", {
            slidesPerView: 1,
            spaceBetween: 20,
            autoplay: { delay: 4000, disableOnInteraction: false },
            pagination: { el: ".swiper-pagination", clickable: true },
            breakpoints: {
                640: { slidesPerView: 2 },
                1024: { slidesPerView: 3 }
            }
        });
    });
</script>

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
@stop