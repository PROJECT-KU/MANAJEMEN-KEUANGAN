@extends('layouts.account')
@extends('layouts.inputfitur')

@section('title')
Update Presensi Karyawan | MIS
@stop

<style>
  :root {
    --accent-color: #6366f1;
    --bg-main: #f8faff;
    --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
    --radius-lg: 24px;
    --radius-md: 16px;
  }

  .card-step {
    background: #ffffff;
    border: 1px solid #f1f5f9;
    border-radius: var(--radius-lg);
    box-shadow: var(--card-shadow);
    padding: 30px;
    margin-bottom: 25px;
    transition: transform 0.3s ease;
  }

  .card-title-modern {
    font-size: 16px;
    font-weight: 800;
    color: var(--accent-color);
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 25px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .form-group label {
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    margin-bottom: 8px;
  }

  .form-control-modern {
    display: block;
    width: 100%;
    padding: 14px 18px;
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    background-color: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    transition: all 0.3s ease;
  }

  .form-control-modern:focus {
    background-color: #fff;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    outline: none;
  }

  /* 🔹 Refined Switch Styling */
  .switch-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .switch-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
    padding: 16px 20px;
    border-radius: var(--radius-md);
    border: 2px solid #f1f5f9;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 0;
  }

  .switch-container:hover {
    border-color: var(--accent-color);
    background-color: #f5f3ff;
  }

  .switch-label-wrapper {
    display: flex;
    flex-direction: column;
  }

  .switch-label-text {
    font-size: 14px;
    font-weight: 800;
    color: #1e293b;
  }

  .switch-subtext {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    margin-top: 2px;
  }

  .switch-modern {
    position: relative;
    display: inline-block;
    width: 46px;
    height: 24px;
  }

  .switch-modern input {
    opacity: 0;
    width: 0;
    height: 0;
  }

  .slider-modern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #e2e8f0;
    transition: .4s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 34px;
  }

  .slider-modern:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 50%;
  }

  input:checked+.slider-modern {
    background-color: #22c55e;
  }

  input:checked+.slider-modern:before {
    transform: translateX(22px);
  }

  .btn-save-modern {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    color: white;
    border: none;
    padding: 14px 28px;
    border-radius: 16px;
    font-weight: 700;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 10px 25px rgba(30, 41, 59, 0.2);
    transition: all 0.3s ease;
    cursor: pointer;
  }

  .btn-save-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(99, 102, 241, 0.3);
    color: white;
  }

  @media (max-width: 767.98px) {
    .btn-desktop-only {
      display: none;
    }

    .btn-mobile-only {
      display: block;
      margin-top: 10px;
      margin-bottom: 50px;
    }
  }

  @media (min-width: 768px) {
    .btn-mobile-only {
      display: none;
    }
  }

  .section-header-modern {
    height: 113.594px;
    margin-bottom: 25px;
  }

  .section-header-modern h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
    color: #1e293b;
    letter-spacing: -1px;
    line-height: 1.2;
  }

  .section-header-modern p {
    margin: 0px 0 0 0;
    font-size: 14px;
  }

  @media (max-width: 991.98px) {
    .section-header-modern {
      height: auto;
      padding: 20px 0;
    }

    .section-header-modern h1 {
      font-size: 24px;
    }
  }

  .section-header-modern h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
    color: #1e293b;
    letter-spacing: -1px;
    line-height: 1.2;
    text-align: left;
  }

  .section-header-modern p {
    margin: 0px 0 0 0;
    font-size: 14px;
    text-align: left;
  }
</style>
@section('content')

<div class="main-content">
  <section class="section">
    <div class="section-header-modern">
      <div>
        <h1>Update Presensi</h1>
        <p class="text-muted font-weight-bold">Pantau kehadiran, waktu kerja, dan lokasi tim operasional Anda.</p>
      </div>
    </div>

    <div class="section-body">
      <div class="card-neo">
        <div class="card-body p-5">

          <form id="updateForm" action="{{ route('account.presensi.update', $presensi->id) }}" method="post" enctype="multipart/form-data">
            @csrf

            @if (Auth::user()->level == 'karyawan' || Auth::user()->level == 'staff' || Auth::user()->level == 'trainer')
            @php
            $todayPresensi = \App\Presensi::where('user_id', Auth::user()->id)
            ->whereDate('created_at', now()->toDateString())
            ->first();
            @endphp

            @if ($todayPresensi)
            <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
            <input type="hidden" name="status_pulang" value="pulang">

            <div class="row" hidden>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status Presensi Kehadiran</label>
                  <select class="form-control" name="status" id="status" style="height:auto">
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="alpha" {{ $presensi->status == 'alpha' ? 'selected' : '' }}>ALPHA</option>
                    <option value="hadir" {{ $presensi->status == 'hadir' ? 'selected' : '' }}>HADIR</option>
                    <option value="camp jogja" {{ $presensi->status == 'camp jogja' ? 'selected' : '' }}>CAMP JOGJA</option>
                    <option value="perjalanan luar kota jawa" {{ $presensi->status == 'perjalanan luar kota jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA DALAM JAWA</option>
                    <option value="perjalanan luar kota luar jawa" {{ $presensi->status == 'perjalanan luar kota luar jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA LUAR JAWA</option>
                    <option value="camp luar kota" {{ $presensi->status == 'camp luar kota' ? 'selected' : '' }}>CAMP LUAR KOTA</option>
                    <option value="remote" {{ $presensi->status == 'remote' ? 'selected' : '' }}>REMOTE</option>
                    <option value="izin" {{ $presensi->status == 'izin' ? 'selected' : '' }}>IZIN</option>
                    <option value="lembur" {{ $presensi->status == 'lembur' ? 'selected' : '' }}>LEMBUR</option>
                    <option value="cuti" {{ $presensi->status == 'cuti' ? 'selected' : '' }}>CUTI</option>
                    <option value="terlambat" {{ $presensi->status == 'terlambat' ? 'selected' : '' }} hidden>TERLAMBAT</option>
                    <option value="pulang" {{ $presensi->status == 'pulang' ? 'selected' : '' }}>PULANG</option>
                  </select>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Status Presensi Pulang</label>
                  <select class="form-control" name="status_pulang" id="status_pulang" style="height:auto">
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="pulang" {{ $presensi->status_pulang == 'pulang' ? 'selected' : '' }}>PULANG</option>
                  </select>
                </div>
              </div>
            </div>
            @endif

            @else
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label style="color: #475569; letter-spacing: 1px;"><i class="fas fa-sign-in-alt text-success mr-1"></i> Status Kehadiran</label>
                  <select class="form-control-modern" name="status" id="status" style="height:auto; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(5px); border: 2px solid rgba(226, 232, 240, 0.8);">
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="alpha" {{ $presensi->status == 'alpha' ? 'selected' : '' }}>ALPHA</option>
                    <option value="hadir" {{ $presensi->status == 'hadir' ? 'selected' : '' }}>HADIR</option>
                    <option value="camp jogja" {{ $presensi->status == 'camp jogja' ? 'selected' : '' }}>CAMP JOGJA</option>
                    <option value="perjalanan luar kota jawa" {{ $presensi->status == 'perjalanan luar kota jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA DALAM JAWA</option>
                    <option value="perjalanan luar kota luar jawa" {{ $presensi->status == 'perjalanan luar kota luar jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA LUAR JAWA</option>
                    <option value="camp luar kota" {{ $presensi->status == 'camp luar kota' ? 'selected' : '' }}>CAMP LUAR KOTA</option>
                    <option value="remote" {{ $presensi->status == 'remote' ? 'selected' : '' }}>REMOTE</option>
                    <option value="izin" {{ $presensi->status == 'izin' ? 'selected' : '' }}>IZIN</option>
                    <option value="cuti" {{ $presensi->status == 'cuti' ? 'selected' : '' }}>CUTI</option>
                    <option value="terlambat" {{ $presensi->status == 'terlambat' ? 'selected' : '' }}>TERLAMBAT</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label style="color: #475569; letter-spacing: 1px;"><i class="fas fa-sign-out-alt text-danger mr-1"></i> Status Kepulangan</label>
                  <select class="form-control-modern" name="status_pulang" id="status_pulang" style="height:auto; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(5px); border: 2px solid rgba(226, 232, 240, 0.8);">
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="pulang" {{ $presensi->status_pulang == 'pulang' ? 'selected' : '' }}>PULANG</option>
                  </select>
                </div>
              </div>
            </div>
            @endif
            @if (Auth::user()->level == 'karyawan' || Auth::user()->level == 'staff' || Auth::user()->level == 'trainer')
            @else
            <div class="d-flex flex-md-nowrap flex-wrap gap-3 mt-5">
              <button type="submit" class="btn-modern btn-update flex-grow-1">
                <i class="fas fa-sync-alt"></i> UPDATE DATA
              </button>
            </div>
            @endif
          </form>

        </div>
      </div>
    </div>
  </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(Auth::user()->level != 'manager' && Auth::user()->level != 'ceo')
<script>
  window.onload = function() {
    Swal.fire({
      // Mengubah title dan html untuk menyuntikkan style glossy
      title: '<span style="background: linear-gradient(135deg, #1e293b 0%, #6366f1 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800;">Konfirmasi Presensi Pulang</span>',
      html: `
        <p style="color: #64748b; font-weight: 600; font-size: 15px; margin-bottom: 10px;">Apakah Anda yakin ingin melakukan presensi pulang saat ini?</p>
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
          .swal-actions-custom {
            display: flex;
            gap: 12px;
            width: 100%;
            margin-top: 25px;
            justify-content: center;
          }
          .btn-swal-action {
            flex: 1;
            border-radius: 16px;
            font-weight: 800;
            padding: 14px 20px;
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
            background: linear-gradient(135deg, #f43f5e 0%, #fb7185 100%);
            color: #ffffff;
          }
        </style>
      `,
      icon: 'question', // Diganti question agar terlihat lebih ramah/modern
      buttonsStyling: false,
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-sign-out-alt mr-2"></i> YA, PULANG',
      cancelButtonText: '<i class="fas fa-times mr-2"></i> BATAL',
      focusConfirm: false,
      customClass: {
        popup: 'glossy-swal-popup',
        actions: 'swal-actions-custom',
        confirmButton: 'btn-swal-action btn-confirm-glossy',
        cancelButton: 'btn-swal-action btn-cancel-glossy'
      }
    }).then((result) => {
      // Logika tetap dipertahankan
      if (result.isConfirmed) {
        document.getElementById('updateForm').submit();
      } else {
        window.location.href = "{{ route('account.dashboard.index') }}";
      }
    });
  };
</script>
@endif
@stop