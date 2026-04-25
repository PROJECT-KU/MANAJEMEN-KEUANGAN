@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Dashboard | MIS
@stop

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --card-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    /* Layout Dasar & Spacing */
    .section-body {
        padding-bottom: 60px;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    /* Modern Card Styles */
    .modern-card {
        background: var(--glass-bg);
        border-radius: 20px;
        padding: 25px;
        border: none;
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .modern-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
    }

    .card-total-karyawan::before {
        background: #5F9EA0;
    }

    .card-aktif::before {
        background: #28a745;
    }

    .card-nonaktif::before {
        background: #dc3545;
    }

    .card-icon-modern {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center; justify-content: center;
        font-size: 24px;
        margin-right: 20px;
        color: white;
    }

    .info-label {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 600;
        margin: 0;
    }

    .info-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #2d3436;
        margin: 0;
    }

    /* Presensi Styles */
    .presensi-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 15px;
    }

    .btn-presensi {
        border-radius: 12px;
        padding: 15px;
        font-weight: 700;
        border: none;
        transition: var(--transition);
        text-transform: uppercase;
        letter-spacing: 1px;
        text-decoration: none !important; /* Menghilangkan underline */
    }

    .btn-masuk {
        background: #667eea;
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-pulang {
        background: #ff7f50;
        color: white;
        box-shadow: 0 4px 15px rgba(255, 127, 80, 0.4);
    }

    .btn-disabled {
        background: #e9ecef;
        color: #adb5bd;
        cursor: not-allowed;
    }

    /* Main Grid: Chart & Info */
    .dashboard-main-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-top: 20px;
        align-items: stretch;
    }

    .salary-analytics-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 25px;
        box-shadow: var(--card-shadow);
        display: flex;
        flex-direction: column;
        min-height: 350px;
    }

    .bar-chart-wrapper {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        height: 200px;
        padding: 20px 10px;
        background: #f8fafc;
        border-radius: 15px;
        margin-top: 20px;
    }

    .bar-pill {
        flex: 1;
        margin: 0 5px;
        background: #e2e8f0;
        border-radius: 10px;
        position: relative;
        transition: all 0.3s ease;
    }

    .bar-pill:hover {
        background: var(--primary-gradient);
        transform: scaleX(1.1);
    }

    .bar-tooltip {
        position: absolute;
        top: -40px;
        left: 50%;
        transform: translateX(-50%);
        background: #1e293b;
        color: white;
        padding: 5px 10px;
        border-radius: 8px;
        font-size: 10px;
        opacity: 0;
        transition: 0.3s;
        white-space: nowrap;
        pointer-events: none;
        z-index: 10;
    }

    .bar-pill:hover .bar-tooltip {
        opacity: 1;
        top: -50px;
    }

    /* Right Column Grid */
    .info-right-column {
        display: grid;
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .mini-card-modern {
        background: white;
        border-radius: 18px;
        padding: 20px;
        display: flex;
        align-items: center;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(0, 0, 0, 0.02);
        transition: var(--transition);
    }

    .mini-card-limit {
        background: #fff5f5;
        border: 1px dashed #feb2b2;
    }

    .icon-circle {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 18px;
    }

    .info-label-small {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 600;
        margin: 0;
    }

    .info-value-small {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2d3436;
        margin: 0;
    }

    @media (max-width: 992px) {
        .dashboard-main-grid {
            grid-template-columns: 1fr;
        }

        .presensi-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">

            @if (Auth::user()->level !== 'user')
            @include('account.dashboard.task-slider-default')
            @endif

            {{-- 1. Statistik Manager (Hanya untuk Level Manager) --}}
            @if (Auth::user()->level === 'manager')
            <div class="dashboard-grid">
                <div class="modern-card card-total-karyawan">
                    <div class="card-icon-modern" style="background: #5F9EA0;"><i class="fas fa-users"></i></div>
                    <div>
                        <p class="info-label">Total Karyawan</p>
                        <h5 class="info-value">{{ $totalKaryawan }}</h5>
                    </div>
                </div>
                <div class="modern-card card-aktif">
                    <div class="card-icon-modern" style="background: #28a745;"><i class="fas fa-user-check"></i></div>
                    <div>
                        <p class="info-label">Karyawan Aktif</p>
                        <h5 class="info-value">{{ $totalKaryawanAktif }}</h5>
                    </div>
                </div>
                <div class="modern-card card-nonaktif">
                    <div class="card-icon-modern" style="background: #dc3545;"><i class="fas fa-user-times"></i></div>
                    <div>
                        <p class="info-label">Non-Aktif</p>
                        <h5 class="info-value">{{ $totalKaryawanNonAktif }}</h5>
                    </div>
                </div>
            </div>
            @endif

            {{-- 2. Baris Presensi & Artikel --}}
            @if (Auth::user()->status !== 'nonactive' && !is_null(Auth::user()->email_verified_at))
            <div class="row">
                {{-- Bagian Presensi disembunyikan jika level user --}}
                @if (Auth::user()->level !== 'user')
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="modern-card flex-column align-items-stretch">
                        <div class="d-flex align-items-center mb-3">
                            <div class="card-icon-modern m-0 me-3" style="background: var(--primary-gradient); width: 45px; height: 45px;">
                                <i class="fas fa-fingerprint" style="font-size: 18px;"></i>
                            </div>
                            <h5 class="m-0 fw-bold ml-2">Presensi Kehadiran</h5>
                        </div>

                        @php
                        $todayPresensi = \App\Presensi::where('user_id', Auth::user()->id)
                        ->whereDate('created_at', now()->toDateString())->first();
                        $currentTime = date('H:i:s');
                        @endphp

                        <div class="presensi-container">
                            @if (!$todayPresensi && $currentTime >= '07:00:00' && $currentTime <= '22:00:00' )
                                <a href="{{ route('account.presensi.create') }}" class="btn-presensi btn-masuk text-center">Masuk</a>
                                @else
                                <button class="btn-presensi btn-disabled" disabled>Masuk</button>
                                @endif

                                @if ($todayPresensi && is_null($todayPresensi->status_pulang) && $currentTime >= '07:00:00' && $currentTime <= '22:00:00' )
                                    <a href="{{ route('account.presensi.edit', $todayPresensi->id) }}" class="btn-presensi btn-pulang text-center">Pulang</a>
                                    @else
                                    <button class="btn-presensi btn-disabled" disabled>Pulang</button>
                                    @endif
                        </div>

                        <div class="mt-4">
                            @if ($todayPresensi && is_null($todayPresensi->status_pulang))
                            <div class="alert alert-success border-0"><i class="fas fa-check-circle me-2"></i> Selamat Bekerja!</div>
                            @elseif (!$todayPresensi)
                            <div class="alert alert-danger border-0"><i class="fas fa-exclamation-triangle me-2"></i> Belum Presensi Hari Ini</div>
                            @else
                            <div class="alert alert-info border-0"><i class="fas fa-info-circle me-2"></i> Tugas Selesai!</div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- Artikel Slider (Selalu Tampil) --}}
                {{-- Penambahan mt-5 pt-4 khusus level user agar lebih turun --}}
                <div class="{{ Auth::user()->level === 'user' ? 'col-12 mt-5 pt-4' : 'col-lg-6 col-md-12' }} mb-4">
                    <div class="modern-card flex-column align-items-stretch" style="background: var(--glass-bg); border-radius: 20px; padding: 25px; box-shadow: var(--card-shadow); overflow: hidden;">
                        <h5 class="fw-bold mb-4"><i class="fas fa-newspaper me-2 text-primary"></i> Artikel Terbaru</h5>

                        <div class="swiper mySwiper" style="width: 100%; cursor: grab; padding-bottom: 35px;">
                            <div class="swiper-wrapper" style="display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important;">
                                @foreach($artikel->take(10) as $item)
                                <div class="swiper-slide ml-2" style="width: 160px; flex-shrink: 0; height: auto;">
                                    <div class="card border-0 shadow-sm" style="width: 100%; border-radius: 15px; overflow: hidden; background: #fff; border: 1px solid #f1f1f1; margin-bottom: 5px;">
                                        <a href="{{ route('blog.topic.blog-single', ['id' => $item->id, 'token' => $item->token]) }}" class="text-decoration-none">
                                            <div style="width: 100%; height: 85px; overflow: hidden;">
                                                <img src="{{ asset('images/' . $item->gambar_depan) }}"
                                                    alt="{{ $item->judul }}"
                                                    style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>

                                            <div class="p-2" style="height: 55px; display: flex; align-items: center; justify-content: center; background: #fff;">
                                                <h6 style="font-size: 10px; font-weight: 600; line-height: 1.3; color: #2d3436; margin: 0; text-align: center;">
                                                    {{ Str::limit($item->judul, 35) }}
                                                </h6>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="swiper-pagination" style="bottom: 0 !important;"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 3. Analitik Gaji & Informasi (Hanya untuk Level Non-User) --}}
            @if (Auth::user()->level !== 'user')
            <div class="dashboard-main-grid">
                <div class="salary-analytics-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Gaji {{ $currentYear }}</h6>
                            <p class="text-muted small mb-0">Statistik bulanan Anda</p>
                        </div>
                        <div class="text-end">
                            <p class="text-muted small mb-0">Total Pendapatan</p>
                            <span class="fw-bold text-primary">Rp {{ number_format($totalGaji ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="bar-chart-wrapper">
                        @php $maxSalary = !empty($salaryData) && max($salaryData) > 0 ? max($salaryData) : 1; @endphp
                        @foreach($salaryData as $month => $salary)
                        <div class="bar-pill" style="height: {{ max(($salary / $maxSalary) * 100, 5) }}%;">
                            <span class="bar-tooltip">Rp {{ number_format($salary, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between mt-3 px-1">
                        @foreach($salaryData as $month => $salary)
                        <span class="text-muted" style="font-size: 9px; flex: 1; text-align: center; font-weight: 600;">
                            {{ substr(date('F', mktime(0, 0, 0, $month, 1)), 0, 3) }}
                        </span>
                        @endforeach
                    </div>
                </div>

                <div class="info-right-column">
                    <div class="mini-card-modern {{ ($totalIzin ?? 0) >= 3 ? 'mini-card-limit' : '' }}"
                        style="background: white; border-radius: 18px; padding: 20px; display: flex; align-items: center; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05); border: 1px solid rgba(0, 0, 0, 0.02); position: relative; overflow: hidden;">

                        @php
                        $currentIzin = $totalIzin ?? 0;
                        $isLimit = $currentIzin >= 3;
                        $isWarning = $currentIzin == 2;
                        @endphp

                        <div style="display: flex; align-items: center; width: 100%; justify-content: space-between;">
                            <div style="display: flex; align-items: center;">
                                <div class="icon-circle" style="
                width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-size: 18px;
                background: {{ $isLimit ? '#feb2b2' : ($isWarning ? '#fef3c7' : 'rgba(102, 126, 234, 0.1)') }}; 
                color: {{ $isLimit ? '#c53030' : ($isWarning ? '#d97706' : '#667eea') }};">
                                    <i class="fas {{ $isLimit ? 'fa-ban' : 'fa-calendar-minus' }}"></i>
                                </div>
                                <div>
                                    <p style="font-size: 0.75rem; color: #6c757d; font-weight: 600; margin: 0;">Izin Bulan Ini</p>
                                    <h6 style="font-size: 1.1rem; font-weight: 700; margin: 0; color: {{ $isLimit ? '#c53030' : ($isWarning ? '#d97706' : '#2d3436') }};">
                                        {{ $currentIzin }} / 3 Kali
                                    </h6>
                                </div>
                            </div>

                            <div style="flex-shrink: 0;">
                                @if($isLimit)
                                <div style="background: #fff5f5; color: #c53030; border: 1px solid #feb2b2; padding: 6px 10px; border-radius: 10px; font-size: 10px; line-height: 1.2;">
                                    <i class="fas fa-exclamation-circle me-1"></i> <strong>Kuota Habis</strong><br>
                                    Batas maksimal 3x tercapai.
                                </div>
                                @elseif($isWarning)
                                <div style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a; padding: 6px 10px; border-radius: 10px; font-size: 10px; line-height: 1.2;">
                                    <i class="fas fa-exclamation-triangle me-1"></i> <strong>Tersisa 1x</strong><br>
                                    Gunakan dengan bijak.
                                </div>
                                @else
                                <div style="color: #667eea; font-size: 10px; font-style: italic; opacity: 0.7;">
                                    Kuota aman <i class="fas fa-check-circle"></i>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mini-card-modern">
    @php
        // 1. Hitung durasi sejak email diverifikasi
        $verifiedAt = Auth::user()->email_verified_at;
        $isOneYear = $verifiedAt ? \Carbon\Carbon::parse($verifiedAt)->diffInYears(now()) >= 1 : false;

        // 2. Logika Sisa Cuti (Hanya muncul jika sudah 1 tahun)
        $displaySisaCuti = $isOneYear ? ($sisaCuti ?? 0) : 0;
    @endphp

    <div class="icon-circle" style="background: {{ $isOneYear ? 'rgba(40, 167, 69, 0.1)' : 'rgba(148, 163, 184, 0.1)' }}; 
                                   color: {{ $isOneYear ? '#28a745' : '#94a3b8' }};">
        <i class="fas {{ $isOneYear ? 'fa-user-clock' : 'fa-user-lock' }}"></i>
    </div>
    
    <div>
        <p class="info-label-small">Sisa Hak Cuti</p>
        <h6 class="info-value-small {{ $isOneYear ? 'text-dark' : 'text-muted' }}">
            {{ $displaySisaCuti }} Hari
        </h6>
        
        @if(!$isOneYear)
            <div style="font-size: 9px; color: #ef4444; font-weight: 600; line-height: 1.1; margin-top: 2px;">
                <i class="fas fa-info-circle"></i> Minimal 1 tahun masa kerja (sejak verifikasi email)
            </div>
        @endif
    </div>
</div>

                    <div class="mini-card-modern" style="background: var(--primary-gradient); color: white; border: none;">
                        <div class="icon-circle" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div>
                            <p class="small mb-0" style="opacity: 0.8;">Rata-rata Gaji</p>
                            <h6 class="fw-bold mb-0">
                                Rp {{ number_format(($totalGaji > 0 ? $totalGaji / 12 : 0), 0, ',', '.') }}
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: "auto",
            spaceBetween: 20,
            freeMode: true,
            loop: true,
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
                dynamicBullets: true,
            },
            breakpoints: {
                320: { spaceBetween: 15 },
                768: { spaceBetween: 20 }
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