@extends('layouts.account')

@section('title')
Tambah Presensi Karyawan | MIS
@stop

<!--================== animasi image ==================-->
<style>
  .cardgambar {
    transition: transform 0.2s ease;
  }

  .cardgambar:hover {
    transform: scale(1.05);
  }
</style>
<!--================== end ==================-->

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>TAMBAH PRESENSI KARYAWAN</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <!-- <div class="card-header">
          <h4><i class="fas fa-user-clock"></i> TAMBAH PRESENSI KARYAWAN</h4>
        </div> -->

        <div class="card-body">

          <form action="{{ route('account.presensi.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @if (Auth::user()->level == 'karyawan' || Auth::user()->level == 'staff' || Auth::user()->level == 'trainer')
            <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Status Presensi</label>
                  <select class="form-control" name="status" id="status" required>
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    @php
                    $currentDay = date('N'); // Mendapatkan kode hari (1 untuk Senin, 2 untuk Selasa, dst.)
                    $currentTime = date('H:i:s'); // Mendapatkan waktu saat ini dalam format "HH:MM:SS"
                    @endphp

                    @if (date('H:i:s') >= '08:00:00' && date('H:i:s') <= '22:00:00' ) <option value="hadir">HADIR</option>
                      <option value="camp jogja">CAMP JOGJA</option>
                      <option value="perjalanan luar kota jawa">PERJALANAN LUAR KOTA DALAM JAWA</option>
                      <option value="perjalanan luar kota luar jawa">PERJALANAN LUAR KOTA LUAR JAWA</option>
                      <option value="camp luar kota">CAMP LUAR KOTA</option>
                      <option value="remote">REMOTE</option>
                      <option value="izin">IZIN</option>
                      @elseif (date('H:i:s') >= '23:00:00' && date('H:i:s') <= '23:59:59' ) || (date('H:i:s')>= '00:00:00' && date('H:i:s') <= '08:00:00' ) <option value="tidak bisa presensi" disabled selected>Belum dapat presensi. Harap pilih status setelah jam 08:00.</option>
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
                  <label>Nama Karyawan</label>
                  <select class="form-control" name="user_id" id="user_id">
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
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
                  <label>Status Presensi Kehadiran</label>
                  <select class="form-control" name="status" id="status">
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
                  <label>Status Presensi Pulang</label>
                  <select class="form-control" name="status_pulang" id="status_pulang">
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="pulang">PULANG</option>
                  </select>
                </div>
              </div>
            </div>
            @endif

            <div id="catatan-section" style="display: none;">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Catatan</label>
                    <div class="input-group">
                      <textarea name="note" id="note" placeholder="Masukkan catatan" class="form-control"></textarea>
                    </div>
                    @error('note')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="gambar">Bukti Presensi</label>
                    <input type="file" name="gambar" id="gambar" class="form-control custom-file-upload" accept="image/*" capture="camera">
                  </div>
                  @error('gambar')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                  <div class="mb-4">
                    <div class="cardgambar" style="width: 200px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);">
                      <img id="image-preview" class="card-img-top" src="#" alt="Preview Image" style="display: none; width: 200px; height: 200px;">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <input type="hidden" name="latitude" id="latitude" value="">
                  <input type="hidden" name="longitude" id="longitude" value="">
                  <div id="map"></div>
                </div>
              </div>
            </div>
            <div class="d-flex">
              <button class="btn btn-primary btn-submit mr-1 rounded-pill" type="submit" style="width: 50%; font-size: 14px;">
                <i class="fa fa-paper-plane"></i> SIMPAN
              </button>
              <button class="btn btn-warning btn-reset rounded-pill" type="reset" style="width: 50%; font-size: 14px;">
                <i class="fa fa-redo"></i> RESET
              </button>
            </div>

          </form>

        </div>
      </div>
  </section>
</div>
<!--================== jika status remote atau izin wajib upload image ==================-->
<script>
  // Function to toggle visibility of catatan and gambar based on selected status
  function toggleSections() {
    var statusSelect = document.getElementById('status');
    var catatanSection = document.getElementById('catatan-section');
    var GambarUpload = document.getElementById('gambar');

    if (statusSelect.value === 'izin') {
      catatanSection.style.display = 'block';
      GambarUpload.setAttribute('required', 'required');
    } else {
      catatanSection.style.display = 'none';
    }
  }

  // Call the function initially
  toggleSections();

  // Add an event listener to the status dropdown
  document.getElementById('status').addEventListener('change', toggleSections);
</script>
<!--================== end ==================-->

<!-- live lokasi -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
  function initMap() {
    if ("geolocation" in navigator) {
      const options = {
        enableHighAccuracy: true, // Request high-accuracy location data
      };

      const map = L.map('map').setView([0, 0], 16); // Initial view
      let marker = null; // Initialize marker

      // Set up the map
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(map);

      navigator.geolocation.watchPosition(
        function(position) {
          const latitude = position.coords.latitude;
          const longitude = position.coords.longitude;

          // Update the input fields with the new location
          document.getElementById('latitude').value = latitude;
          document.getElementById('longitude').value = longitude;

          // Update the map with the new location
          if (marker) {
            map.removeLayer(marker);
          }
          marker = L.marker([latitude, longitude]).addTo(map);
          marker.bindPopup('Lokasi Anda').openPopup();
          map.setView([latitude, longitude]);
        },
        function(error) {
          console.log(`Error getting location: ${error.message}`);
          // Handle location errors here, e.g., display an error message to the user.
        },
        options
      );
    } else {
      alert('Geolocation tidak didukung oleh browser Anda.');
    }
  }

  // Panggil fungsi initMap() saat halaman dimuat
  window.onload = initMap;
</script>

<!-- Style untuk peta (gunakan CSS sesuai dengan preferensi Anda) -->
<style>
  #map {
    width: 100%;
    height: 400px;
  }
</style>
<!-- end live lokasi -->

<!-- maksimal upload gambar & jenis file yang di perbolehkan -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.getElementById('gambar').addEventListener('change', function() {
    const maxFileSizeInBytes = 5024 * 5024; // 5MB
    const allowedExtensions = ['jpg', 'jpeg', 'png'];
    const fileInput = this;

    if (fileInput.files.length > 0) {
      const selectedFile = fileInput.files[0];
      const fileSize = selectedFile.size; // Get the file size in bytes
      const fileName = selectedFile.name.toLowerCase();

      // Check file size
      if (fileSize > maxFileSizeInBytes) {
        // Display a SweetAlert error message
        Swal.fire({
          icon: 'error',
          title: 'Ukuran File Melebihi Batas',
          text: 'Ukuran File Yang Diperbolehkan Dibawah 5MB.',
        });
        fileInput.value = ''; // Clear the file input
        return;
      }

      // Check file extension
      const fileExtension = fileName.split('.').pop();
      if (!allowedExtensions.includes(fileExtension)) {
        // Display a SweetAlert error message
        Swal.fire({
          icon: 'error',
          title: 'Jenis File Tidak Valid',
          text: 'Hanya File JPG, JPEG, dan PNG Yang Diperbolehkan.',
        });
        fileInput.value = ''; // Clear the file input
      }
    }
  });
</script>
<!-- end -->

<!-- upload image -->
<script>
  const imageInput = document.getElementById('gambar');
  const imagePreview = document.getElementById('image-preview');

  imageInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        imagePreview.src = e.target.result;
        imagePreview.style.display = 'block'; // Show the preview
      };
      reader.readAsDataURL(file);
    }
  });
</script>
<!-- end upload image -->

<!-- Include CKEditor JS -->
<script src="//cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script>
  CKEDITOR.replace('note');
</script>
<!-- end ckeditor -->

<script>
  /**
   * btn submit loader
   */
  $(".btn-submit").click(function() {
    $(".btn-submit").addClass('btn-progress');
    if (timeoutHandler) clearTimeout(timeoutHandler);

    timeoutHandler = setTimeout(function() {
      $(".btn-submit").removeClass('btn-progress');

    }, 1000);
  });

  /**
   * btn reset loader
   */
  $(".btn-reset").click(function() {
    $(".btn-reset").addClass('btn-progress');
    if (timeoutHandler) clearTimeout(timeoutHandler);

    timeoutHandler = setTimeout(function() {
      $(".btn-reset").removeClass('btn-progress');

    }, 500);
  })
</script>

@stop