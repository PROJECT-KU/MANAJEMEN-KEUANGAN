@extends('layouts.account')
@extends('layouts.inputfitur')

@section('title')
Tambah Presensi Karyawan | MIS
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
        <h1>Tambah Presensi</h1>
        <p class="text-muted font-weight-bold">Pantau kehadiran, waktu kerja, dan lokasi tim operasional Anda.</p>
      </div>
    </div>

    <div class="section-body">
      <div class="card-neo">
        <div class="card-body p-5">
          <form action="{{ route('account.presensi.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @if (Auth::user()->level == 'karyawan' || Auth::user()->level == 'staff' || Auth::user()->level == 'trainer')
            <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label style="color: #475569; letter-spacing: 1px;"><i class="fas fa-clipboard-check text-primary mr-1"></i> Status Presensi</label>
                  <select class="form-control-modern" name="status" id="status" style="height: auto; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(5px); border: 2px solid rgba(226, 232, 240, 0.8);" required>
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    @php
                    $currentDay = date('N');
                    $currentTime = date('H:i:s');
                    @endphp

                    @if (date('H:i:s') >= '08:00:00' && date('H:i:s') <= '22:00:00' )
                      <option value="hadir">HADIR</option>
                      <option value="camp jogja">CAMP JOGJA</option>
                      <option value="perjalanan luar kota jawa">PERJALANAN LUAR KOTA DALAM JAWA</option>
                      <option value="perjalanan luar kota luar jawa">PERJALANAN LUAR KOTA LUAR JAWA</option>
                      <option value="camp luar kota">CAMP LUAR KOTA</option>
                      <option value="remote">REMOTE</option>
                      <option value="izin">IZIN</option>
                      @elseif (date('H:i:s') >= '23:00:00' && date('H:i:s') <= '23:59:59' ) || (date('H:i:s')>= '00:00:00' && date('H:i:s') <= '08:00:00' )
                          <option value="tidak bisa presensi" disabled selected>Belum dapat presensi. Harap pilih status setelah jam 08:00.</option>
                          @endif
                  </select>

                  @error('status')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
            </div>
            @else
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label style="color: #475569; letter-spacing: 1px;"><i class="fas fa-user-circle text-primary mr-1"></i> Nama Karyawan</label>
                  <select class="form-control-modern select2" name="user_id" id="user_id" style="height: auto; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(5px); border: 2px solid rgba(226, 232, 240, 0.8);">
                    <option value="" disabled selected>-- PILIH NAMA KARYAWAN --</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label style="color: #475569; letter-spacing: 1px;"><i class="fas fa-sign-in-alt text-success mr-1"></i> Status Kehadiran</label>
                  <select class="form-control-modern" name="status" id="status" style="height: auto; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(5px); border: 2px solid rgba(226, 232, 240, 0.8);">
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="alpha">ALPHA</option>
                    <option value="hadir">HADIR</option>
                    <option value="camp jogja">CAMP JOGJA</option>
                    <option value="perjalanan luar kota jawa">PERJALANAN LUAR KOTA DALAM JAWA</option>
                    <option value="perjalanan luar kota luar jawa">PERJALANAN LUAR KOTA LUAR JAWA</option>
                    <option value="camp luar kota">CAMP LUAR KOTA</option>
                    <option value="remote">REMOTE</option>
                    <option value="izin">IZIN</option>
                    <option value="lembur">LEMBUR</option>
                    <option value="cuti">CUTI</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label style="color: #475569; letter-spacing: 1px;"><i class="fas fa-sign-out-alt text-danger mr-1"></i> Status Kepulangan</label>
                  <select class="form-control-modern" name="status_pulang" id="status_pulang" style="height: auto; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(5px); border: 2px solid rgba(226, 232, 240, 0.8);">
                    <option value="" disabled selected>-- PILIH STATUS PULANG --</option>
                    <option value="pulang">PULANG</option>
                  </select>
                </div>
              </div>
            </div>
            @endif
            <div class="row mt-4">
              <div class="col-md-12">
                <div class="form-group">
                  <label style="color: #475569; letter-spacing: 1px;"><i class="fas fa-map-marker-alt text-danger mr-1"></i> Lokasi Saat Ini</label>
                  <input type="hidden" name="latitude" id="latitude" value="">
                  <input type="hidden" name="longitude" id="longitude" value="">

                  <div style="padding: 12px; background: rgba(255, 255, 255, 0.5); border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.8); box-shadow: inset 0 2px 10px rgba(0,0,0,0.02);">
                    <div id="map" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;"></div>
                  </div>

                </div>
              </div>
            </div>
            <div class="d-flex flex-md-nowrap flex-wrap gap-3 mt-5">
              <button type="submit" class="btn-modern btn-save flex-grow-1">
                <i class="fas fa-save"></i> SIMPAN DATA
            </div>
          </form>
        </div>
      </div>
  </section>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
  function initMap() {
    if ("geolocation" in navigator) {
      const options = {
        enableHighAccuracy: true,
      };

      const map = L.map('map').setView([0, 0], 16);
      let marker = null;

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(map);

      navigator.geolocation.watchPosition(
        function(position) {
          const latitude = position.coords.latitude;
          const longitude = position.coords.longitude;

          document.getElementById('latitude').value = latitude;
          document.getElementById('longitude').value = longitude;

          if (marker) {
            map.removeLayer(marker);
          }
          marker = L.marker([latitude, longitude]).addTo(map);
          marker.bindPopup('Lokasi Anda Saat Ini').openPopup();
          map.setView([latitude, longitude]);
        },
        function(error) {
          console.log(`Error getting location: ${error.message}`);
        },
        options
      );
    } else {
      alert('Geolocation tidak didukung oleh browser Anda.');
    }
  }

  window.onload = initMap;
</script>

<style>
  #map {
    width: 100%;
    height: 400px;
  }
</style>
@stop