@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Data Presensi Karyawan | MIS
@stop

<style>
  :root {
    --accent: #6366f1;
    --accent-light: #818cf8;
    --bg-main: #f4f7ff;
    --card-bg: rgba(255, 255, 255, 0.9);
    --radius-xl: 24px;
    --radius-md: 16px;
    --shadow-soft: 0 20px 40px rgba(0, 0, 0, 0.04);
  }

  .main-content {
    padding-top: 120px !important;
    background-color: var(--bg-main);
    min-height: 100vh;
  }

  .hero-glass {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-xl);
    padding: 30px;
    border: 1px solid rgba(255, 255, 255, 0.5);
    box-shadow: var(--shadow-soft);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 40px;
  }

  .title-section h1 {
    font-size: 28px;
    font-weight: 800;
    background: linear-gradient(to right, #1e293b, #6366f1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: -1.5px;
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 0px;
  }

  .total-badge {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    color: white;
    padding: 5px 18px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 700;
    box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
    -webkit-text-fill-color: white;
    letter-spacing: 0.5px;
  }

  .header-controls {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .search-neo {
    background: #ffffff;
    border-radius: 50px;
    padding: 8px 25px;
    display: flex;
    align-items: center;
    box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.05), 0 5px 15px rgba(99, 102, 241, 0.1);
    width: 300px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  }

  .search-neo:focus-within {
    width: 380px;
  }

  .search-neo input {
    border: none;
    background: transparent;
    padding: 10px;
    width: 100%;
    font-weight: 600;
    color: #475569;
  }

  .search-neo input:focus {
    outline: none;
  }

  #customerTable {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
    position: relative;
  }

  .customer-card {
    background: var(--card-bg);
    backdrop-filter: blur(5px);
    border-radius: var(--radius-xl);
    padding: 25px;
    border: 1px solid rgba(255, 255, 255, 0.7);
    box-shadow: var(--shadow-soft);
    transition: all 0.4s ease;
  }

  .customer-card:hover {
    transform: translateY(-10px);
    background: #ffffff;
  }

  .card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
  }

  .avatar-wrap {
    position: relative;
    width: 65px;
    height: 65px;
  }

  .avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 18px;
    object-fit: cover;
  }

  .status-indicator {
    position: absolute;
    bottom: -4px;
    right: -4px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid #fff;
    background: #22c55e;
  }

  .status-indicator.non-active {
    background: #ef4444;
  }

  .info-grid-modern {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 20px;
    padding-top: 18px;
    border-top: 1px dashed #e2e8f0;
  }

  .info-chip {
    padding: 10px;
    border-radius: 14px;
    background: #f8fafc;
    border: 1px solid transparent;
  }

  .info-chip label {
    display: block;
    font-size: 9px;
    text-transform: uppercase;
    color: #94a3b8;
    font-weight: 800;
    margin-bottom: 5px;
  }

  .info-chip .value {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    color: #1e293b;
  }

  .btn-modern {
    flex: 1;
    padding: 12px;
    border-radius: 14px;
    border: none;
    font-size: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none !important;
  }

  .btn-edit {
    background: #eef2ff;
    color: #6366f1;
  }

  .btn-edit:hover {
    background: #6366f1;
    color: #fff;
    transform: translateY(-3px);
  }

  .btn-delete {
    background: #fff1f2;
    color: #f43f5e;
  }

  .btn-delete:hover {
    background: #f43f5e;
    color: #fff;
    transform: translateY(-3px);
  }

  /* 🔹 Animasi khusus untuk Tombol Create Data */
  .btn-create-animate {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
    position: relative;
    overflow: hidden;
  }

  .btn-create-animate:hover {
    transform: scale(1.05) translateY(-3px);
    box-shadow: 0 15px 30px rgba(99, 102, 241, 0.4) !important;
    filter: brightness(1.1);
  }

  .btn-create-animate:active {
    transform: scale(0.95);
  }

  /* Efek kilauan (shimmer) saat hover */
  .btn-create-animate::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(120deg,
        transparent,
        rgba(255, 255, 255, 0.3),
        transparent);
    transition: all 0.6s;
  }

  .btn-create-animate:hover::before {
    left: 100%;
  }

  @media (max-width: 992px) {
    .hero-glass {
      flex-direction: column;
      text-align: center;
      padding: 25px 20px;
    }

    .header-controls {
      flex-direction: column;
      width: 100%;
      gap: 15px;
    }

    .search-neo {
      width: 100% !important;
      max-width: none;
    }

    .header-controls .btn-modern {
      width: 100% !important;
      height: 50px;
      /* Memberikan tinggi yang cukup agar mudah ditekan */
      justify-content: center;
      display: flex !important;
      min-width: 100%;
      /* Memaksa tombol tidak mengecil */
      margin: 0;
    }
  }

  .status-verified {
    color: #10b981 !important;
  }

  .status-unverified {
    color: #f43f5e !important;
  }

  .status-active {
    color: #10b981 !important;
  }
</style>

@section('content')
<div class="main-content">
  <section class="section">

    <div class="hero-glass">
      <div class="title-section">
        <h1>
          Data Presensi
          <span class="total-badge" id="totalCounter">{{ $presensi->total() }} Catatan</span>
        </h1>
        <p class="text-muted font-weight-bold mb-1">Pantau kehadiran, waktu kerja, dan lokasi tim operasional Anda.</p>
        <small class="text-primary font-weight-bold"><i class="far fa-clock"></i> <span id="current-time"></span> WIB</small>
      </div>

      <div class="header-controls">
        <div class="search-neo">
          <i class="fas fa-search text-primary"></i>
          <input type="text" id="liveSearch" placeholder="Cari nama karyawan..." autocomplete="off">
          <button type="button" id="clearSearch" class="border-0 bg-transparent" style="display:none; cursor:pointer;">
            <i class="fas fa-times-circle text-muted"></i>
          </button>
        </div>

        <button type="button" id="btnFilterPopup" class="btn-modern shadow-sm font-weight-bold" style="background: #ffffff; color: #475569; border: 1px solid #e2e8f0; height: 42px; width: 120px;">
          <i class="fas fa-filter text-primary"></i> FILTER
        </button>

        @if (Auth::user()->level === 'manager')
        <a href="{{ route('account.presensi.create') }}" class="btn-modern shadow-sm font-weight-bold btn-create-animate"
          style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: white; height: 42px;">
          <i class="fas fa-plus-circle" style="font-size: 18px;"></i>
          <span style="letter-spacing: 0.5px;">TAMBAH</span>
        </a>
        @endif
      </div>
    </div>

    <div id="customerTable">
      @if($presensi->count() > 0)

      @php
      if (!function_exists('getStatusBg')) {
      function getStatusBg($status) {
      $s = strtolower($status);
      if (in_array($s, ['hadir', 'camp jogja', 'camp luar kota'])) return 'background: #10b981; color: white;';
      if (in_array($s, ['perjalanan luar kota jawa', 'perjalanan luar kota luar jawa', 'remote'])) return 'background: #3b82f6; color: white;';
      if (in_array($s, ['izin', 'cuti'])) return 'background: #f59e0b; color: white;';
      if (in_array($s, ['lembur'])) return 'background: #8b5cf6; color: white;';
      return 'background: #ef4444; color: white;'; // Alpha, telat, pulang
      }
      }

      // Penerjemah Hari Bahasa Indonesia
      $daftar_hari = array(
      'Sunday' => 'Minggu',
      'Monday' => 'Senin',
      'Tuesday' => 'Selasa',
      'Wednesday' => 'Rabu',
      'Thursday' => 'Kamis',
      'Friday' => 'Jumat',
      'Saturday' => 'Sabtu'
      );
      @endphp
      @foreach ($presensi as $hasil)

      @php
      $hari_presensi = $daftar_hari[date('l', strtotime($hasil->created_at))];
      @endphp

      <div class="customer-card">
        <div class="card-top">
          <div class="avatar-wrap">
            <img src="{{ !empty($hasil->user_gambar) ? asset('assets/img/profil/' . $hasil->user_gambar) : asset('assets/img/profil/no-image.jpg') }}" class="avatar-img" alt="Profile">
            <div class="status-indicator" style="background: {{ $hasil->time_pulang ? '#22c55e' : '#f59e0b' }};"></div>
          </div>
          <div class="text-right">
            <span class="d-inline-flex align-items-center" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; padding: 6px 12px; border-radius: 10px; font-size: 10px; font-weight: 800; text-transform: uppercase;">
              <i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($hasil->created_at)->locale('id')->translatedFormat('d F Y') }}
            </span>
          </div>
        </div>

        <div class="card-body-content">
          <h5 class="mb-0 font-weight-bold">{{ $hasil->full_name }}</h5>
          <p class="text-muted mb-3" style="font-size: 12px; margin-top: 5px;">Presensi Masuk: {{ $hari_presensi }}, {{ date('H:i', strtotime($hasil->created_at)) }} WIB</p>

          <div style="position: relative; width: 100%; height: 160px; border-radius: 16px; overflow: hidden; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid rgba(226,232,240,0.8); background: #e2e8f0;">
            <iframe
              src="https://maps.google.com/maps?q={{ $hasil->latitude }},{{ $hasil->longitude }}&t=&z=15&ie=UTF8&iwloc=&output=embed"
              style="width: 100%; height: 100%; border: none; pointer-events: none;"
              loading="lazy">
            </iframe>

            <div style="position: absolute; top: 12px; right: 12px; padding: 5px 12px; border-radius: 10px; font-size: 10px; font-weight: 800; text-transform: uppercase; box-shadow: 0 4px 10px rgba(0,0,0,0.2); backdrop-filter: blur(5px); {{ getStatusBg($hasil->status) }}">
              {{ $hasil->status }}
            </div>

            <a href="https://www.google.com/maps?q={{ $hasil->latitude }},{{ $hasil->longitude }}" target="_blank" style="position: absolute; bottom: 12px; left: 50%; transform: translateX(-50%); background: rgba(255,255,255,0.95); color: #1e293b; padding: 8px 16px; border-radius: 50px; font-size: 11px; font-weight: 800; text-decoration: none; box-shadow: 0 4px 15px rgba(0,0,0,0.15); backdrop-filter: blur(5px); display: flex; align-items: center; gap: 5px; white-space: nowrap; transition: all 0.3s ease;" onmouseover="this.style.background='#1e293b'; this.style.color='#ffffff';" onmouseout="this.style.background='rgba(255,255,255,0.95)'; this.style.color='#1e293b';">
              <i class="fas fa-map-marker-alt text-danger"></i> Buka Full Maps
            </a>
          </div>

          <div class="info-grid-modern">
            <div class="info-chip">
              <label>Jam Datang</label>
              <div class="value">
                <i class="fas fa-sign-in-alt text-success"></i>
                <span>{{ strftime('%H:%M:%S', strtotime($hasil->created_at)) }}</span>
              </div>
            </div>
            <div class="info-chip">
              <label>Jam Pulang</label>
              <div class="value">
                <i class="fas fa-sign-out-alt text-danger"></i>
                <span>{{ $hasil->time_pulang ? strftime('%H:%M:%S', strtotime($hasil->time_pulang)) : '--:--:--' }}</span>
              </div>
            </div>
            <div class="info-chip" style="grid-column: 1 / -1;">
              <label>Durasi / Lama Kerja</label>
              <div class="value">
                <i class="fas fa-hourglass-half text-primary"></i>
                <span>
                  @if($hasil->time_pulang)
                  @php
                  $created_at = strtotime($hasil->created_at);
                  $time_pulang = strtotime($hasil->time_pulang);
                  $selisih = $time_pulang - $created_at;
                  echo sprintf('%02d Jam %02d Menit', floor($selisih / 3600), floor(($selisih % 3600) / 60));
                  @endphp
                  @else
                  <span style="color: #f59e0b;"><i class="fas fa-spinner fa-spin mr-1"></i> Sedang Bekerja</span>
                  @endif
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="action-wrap" style="display:flex; gap:10px; margin-top:20px;">
          @if (Auth::user()->level == 'staff' || Auth::user()->level == 'ceo')
          <a href="{{ route('account.presensi.detail', $hasil->id) }}" class="btn-modern btn-edit" style="background: #fffbeb; color: #d97706;"><i class="fas fa-eye"></i> Detail</a>
          @elseif (Auth::user()->level == 'manager' || Auth::user()->level == 'admin')
          <a href="{{ route('account.presensi.edit', $hasil->id) }}" class="btn-modern btn-edit"><i class="fas fa-edit"></i> Edit</a>
          <button onclick="Delete('{{ $hasil->id }}')" class="btn-modern btn-delete"><i class="fas fa-trash"></i> Hapus</button>
          @endif
        </div>
      </div>
      @endforeach
      @else
      <div class=" text-center" style="grid-column: 1 / -1;">
        <div class="customer-card" style="background: var(--card-bg); border: 1px solid rgba(255, 255, 255, 0.7); box-shadow: var(--shadow-soft);">
          <div style="background: var(--card-bg); padding: 60px 20px; border-radius: var(--radius-xl); border: 2px dashed #e2e8f0; margin: 20px;">
            <div class="mb-4">
              <i class="fas fa-calendar-times" style="font-size: 64px; color: #cbd5e1;"></i>
            </div>
            <h4 style="font-weight: 800; color: #475569;">Data Tidak Ditemukan</h4>
            <p style="color: #94a3b8; font-weight: 600;">Maaf, sepertinya tidak ada data presensi yang cocok dengan pencarian Anda.</p>
            <a href="{{ route('account.presensi.index') }}" class="btn btn-primary mt-3" style="border-radius: 50px; padding: 10px 25px; font-weight: 700; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);">
              <i class="fas fa-sync-alt mr-2"></i> Muat Ulang Halaman
            </a>
          </div>
        </div>
      </div>
      @endif
    </div>

    <div id="paginationWrapper" class="d-flex justify-content-center mt-5">
      {{ $presensi->appends(['tanggal_awal' => request('tanggal_awal'), 'tanggal_akhir' => request('tanggal_akhir'), 'q' => request('q')])->links("vendor.pagination.bootstrap-4") }}
    </div>

  </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@php
$isManagerJS = (Auth::user()->level == 'manager') ? 1 : 0;
@endphp

<script>
  document.getElementById('btnFilterPopup').addEventListener('click', function() {
    const tglAwal = "{{ request('tanggal_awal') }}";
    const tglAkhir = "{{ request('tanggal_akhir') }}";
    const isManager = "{{ $isManagerJS }}" === "1";

    let ekstraTombol = '';

    if (tglAwal && tglAkhir) {
      ekstraTombol += `
        <button type="button" onclick="window.location.href='{{ route('account.presensi.index') }}'" class="btn-swal-action btn-danger-glossy">
          <i class="fas fa-times-circle mr-2"></i> HAPUS FILTER PENCARIAN
        </button>
      `;
    }

    if (isManager) {
      let q = document.getElementById('liveSearch') ? document.getElementById('liveSearch').value : '';
      let urlExcel = `{{ route('account.laporan_presensi.download-excel') }}?tanggal_awal=${tglAwal}&tanggal_akhir=${tglAkhir}&q=${encodeURIComponent(q)}`;
      ekstraTombol += `
        <hr style="border-top: 2px dashed #e2e8f0; margin: 25px 0;">
        <a href="${urlExcel}" class="btn-swal-action btn-success-glossy" style="text-decoration: none;">
          <i class="far fa-file-excel mr-2"></i> UNDUH LAPORAN EXCEL
        </a>
      `;
    }

    Swal.fire({
      title: '<span style="background: linear-gradient(to right, #1e293b, #6366f1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800; letter-spacing: -1px; font-size: 28px;">Filter Presensi</span>',
      html: `
        <style>
          .glossy-swal-popup {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.8) !important;
            border-radius: 24px !important;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1) !important;
            padding: 2.5em 2em !important;
          }
          
          .swal2-container.swal2-backdrop-show {
            background: rgba(15, 23, 42, 0.4) !important;
            backdrop-filter: blur(5px) !important;
          }

          .glass-input-swal {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 12px 20px;
            height: 55px;
            font-weight: 700;
            color: #475569;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
          }
          
          .glass-input-swal:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            background: #f8fafc;
          }

          .swal-actions-custom {
            display: flex;
            gap: 12px;
            width: 100%;
            margin-top: 25px;
            justify-content: space-between;
          }

          .btn-swal-action {
            flex: 1;
            border-radius: 16px;
            font-weight: 800;
            padding: 16px 20px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            width: 100%;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
          }

          .btn-swal-action:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 25px rgba(0,0,0,0.15);
            filter: brightness(1.1);
          }

          .btn-swal-action:active {
            transform: scale(0.96);
          }

          .btn-confirm-glossy {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: #fff;
          }

          .btn-cancel-glossy {
            background: #f43f5e;
            color: #ffffff;
          }

          .btn-danger-glossy {
            background: linear-gradient(135deg, #f43f5e 0%, #fb7185 100%);
            color: #fff;
            margin-top: 15px;
          }

          .btn-success-glossy {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: #fff;
          }
        </style>

        <form id="formFilterSwal" action="{{ route('account.presensi.filter') }}" method="GET" style="text-align: left; margin-top: 25px;">
          <div class="form-group mb-4">
            <label class="font-weight-bold text-muted mb-2" style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
              <i class="far fa-calendar-alt text-primary mr-2" style="font-size: 14px;"></i> Tanggal Awal
            </label>
            <input type="date" id="inputTglAwal" name="tanggal_awal" value="${tglAwal}" class="glass-input-swal">
          </div>
          <div class="form-group mb-2">
            <label class="font-weight-bold text-muted mb-2" style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
              <i class="far fa-calendar-check text-primary mr-2" style="font-size: 14px;"></i> Tanggal Akhir
            </label>
            <input type="date" id="inputTglAkhir" name="tanggal_akhir" value="${tglAkhir}" class="glass-input-swal">
          </div>
        </form>
        ${ekstraTombol}
      `,
      buttonsStyling: false,
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-search mr-2"></i> TERAPKAN',
      cancelButtonText: 'BATAL',
      focusConfirm: false,
      customClass: {
        popup: 'glossy-swal-popup',
        actions: 'swal-actions-custom',
        confirmButton: 'btn-swal-action btn-confirm-glossy',
        cancelButton: 'btn-swal-action btn-cancel-glossy'
      },
      preConfirm: () => {
        const awal = document.getElementById('inputTglAwal').value;
        const akhir = document.getElementById('inputTglAkhir').value;
        if (!awal || !akhir) {
          Swal.showValidationMessage('Pastikan Tanggal Awal dan Akhir telah dipilih!');
          return false;
        }
        return true;
      }
    }).then((result) => {
      if (result.isConfirmed) {
        document.getElementById('formFilterSwal').submit();
      }
    });
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    let timer;
    const liveSearchInput = document.getElementById('liveSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const container = document.getElementById('customerTable');
    const paginationWrapper = document.getElementById('paginationWrapper');
    const totalCounter = document.getElementById('totalCounter');

    function fetchContent(url) {
      container.style.opacity = '0.5';
      fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(response => response.text())
        .then(html => {
          container.style.opacity = '1';
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');

          const newContent = doc.querySelector('#customerTable');
          const newPagination = doc.querySelector('#paginationWrapper');
          const newTotal = doc.querySelector('#totalCounter');

          if (newContent && newContent.innerHTML.trim() !== "") {
            container.innerHTML = newContent.innerHTML;
          } else {
            container.innerHTML = `<div class="text-center w-100" style="grid-column: 1 / -1; padding: 50px;">
                                        <i class="fas fa-search-minus fa-3x mb-3 text-muted"></i>
                                        <h5 class="text-muted">Data tidak ditemukan</h5>
                                      </div>`;
          }

          if (newPagination && paginationWrapper) paginationWrapper.innerHTML = newPagination.innerHTML;
          if (newTotal && totalCounter) totalCounter.innerText = newTotal.innerText;
        })
        .catch(err => {
          container.style.opacity = '1';
          console.error("Fetch Error:", err);
        });
    }

    // 1. Handle Input Search
    if (liveSearchInput) {
      liveSearchInput.addEventListener('input', function() {
        clearSearchBtn.style.display = this.value.trim() ? 'block' : 'none';
        clearTimeout(timer);
        const query = this.value;

        timer = setTimeout(() => {
          const t_awal = "{{ request('tanggal_awal') }}";
          const t_akhir = "{{ request('tanggal_akhir') }}";

          let url = query.trim() === "" ?
            `{{ route('account.presensi.index') }}?tanggal_awal=${t_awal}&tanggal_akhir=${t_akhir}` :
            `{{ route('account.presensi.search') }}?q=${encodeURIComponent(query)}&tanggal_awal=${t_awal}&tanggal_akhir=${t_akhir}`;

          fetchContent(url);
        }, 300);
      });
    }

    // 2. Handle Tombol Clear (Klik X)
    if (clearSearchBtn) {
      clearSearchBtn.addEventListener('click', function() {
        liveSearchInput.value = '';
        this.style.display = 'none';
        const url = `{{ route('account.presensi.index') }}?tanggal_awal={{ request('tanggal_awal') }}&tanggal_akhir={{ request('tanggal_akhir') }}`;
        fetchContent(url);
      });
    }

    // 3. Handle Klik Pagination
    document.addEventListener('click', function(e) {
      const paginationLink = e.target.closest('#paginationWrapper a');
      if (paginationLink) {
        e.preventDefault();
        const url = paginationLink.getAttribute('href');
        if (url) {
          fetchContent(url);
          window.scrollTo({
            top: 0,
            behavior: 'smooth'
          });
        }
      }
    });

    // 4. Update Waktu Realtime
    function updateCurrentTime() {
      var now = new Date();
      var hours = now.getHours().toString().padStart(2, '0');
      var minutes = now.getMinutes().toString().padStart(2, '0');
      if (document.getElementById('current-time')) {
        document.getElementById('current-time').innerText = hours + ':' + minutes;
      }
    }
    setInterval(updateCurrentTime, 1000);
    updateCurrentTime();
  });

  function Delete(id) {
    Swal.fire({
      title: 'Apakah Anda yakin?',
      text: "Data yang dihapus tidak dapat dikembalikan!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#6366f1',
      cancelButtonColor: '#f43f5e',
      confirmButtonText: 'YA, HAPUS!',
      cancelButtonText: 'BATAL',
      borderRadius: '15px'
    }).then((result) => {
      if (result.isConfirmed) {
        let token = $("meta[name='csrf-token']").attr("content");

        $.ajax({
          url: "/account/presensi/" + id,
          type: "DELETE",
          cache: false,
          data: {
            "_token": token
          },
          success: function(response) {
            if (response.status === "success" || response.status === true) {
              Swal.fire({
                icon: 'success',
                title: 'BERHASIL!',
                text: response.message || 'Data berhasil dihapus.',
                showConfirmButton: false,
                timer: 2000
              }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'GAGAL!',
                text: response.message
              });
            }
          },
          error: function(xhr) {
            Swal.fire({
              icon: 'error',
              title: 'ERROR!',
              text: 'Terjadi kesalahan pada server atau akses ditolak.'
            });
          }
        });
      }
    })
  }
</script>
@stop