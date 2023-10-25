@extends('layouts.account')

@section('title')
Tambah Presensi Karyawan | MANAGEMENT
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>PRESENSI KARYAWAN</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-user-clock"></i> TAMBAH PRESENSI KARYAWAN</h4>
        </div>

        <div class="card-body">

          <form action="{{ route('account.presensi.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">

            <!--================== alerts di luar jam kerja ==================-->
            @php
            $currentDay = date('N'); // Mendapatkan kode hari (1 untuk Senin, 2 untuk Selasa, dst.)
            $currentTime = date('H:i:s'); // Mendapatkan waktu saat ini dalam format "HH:MM:SS"
            @endphp

            @if (date('H:i:s') >= '22:00:00' && date('H:i:s') <= '23:59:59' ) <div class="alert alert-warning" role="alert">
              Mohon Maaf.. Kamu Di Luar Jam Kerja! Silahkan Presensi Besok Ya!
        </div>

        @elseif ($currentDay == 1 && ($currentTime>= '00:00:00' && $currentTime <= '07:59:59' )) <div class="alert alert-warning" role="alert">
          Kok Rajin Banget Sih hehe.. Tunggu jam 08.00 ya!
      </div>

      @elseif (in_array($currentDay, [2, 3]) && ($currentTime>= '00:00:00' && $currentTime <= '23:59:59' )) <div class="alert alert-warning" role="alert">
        LIBUR LO! Kok Mau Presensi terus sih.. jangan rajin-rajin Kesehatan juga perlu diperhatikan seperti kamu memperhatikan dia!
    </div>

    @elseif ($currentDay == 4 && ($currentTime>= '00:00:00' && $currentTime <= '11:59:59' )) <div class="alert alert-warning" role="alert">
      Kok Rajin Banget Sih hehe.. Tunggu jam 12.00 ya!
</div>

@elseif (in_array($currentDay, [5, 6, 7]) && ($currentTime>= '00:00:00' && $currentTime <= '06:59:99' )) <div class="alert alert-warning" role="alert">
  Kok Rajin Banget Sih hehe.. Tunggu jam 07.00 ya!
  </div>
  @endif
  <!--================== end ==================-->

  <!--================== alerts info jam kerja ==================-->
  @php
  $currentDay = date('N'); // Mendapatkan kode hari (1 untuk Senin, 2 untuk Selasa, dst.)
  $currentTime = date('H:i:s'); // Mendapatkan waktu saat ini dalam format "HH:MM:SS"
  @endphp

  @if ($currentDay == 1 && ($currentTime>= '09:00:00' && $currentTime <= '16:00:00' )) <div class="alert alert-info" role="alert">
    Jam kerja mulai dari 09.00 - 16.00 WIB
    </div>
    @elseif ($currentDay == 4 && ($currentTime>= '13:00:00' && $currentTime <= '20:00:00' )) <div class="alert alert-info" role="alert">
      Jam kerja mulai dari 13.00 - 20.00 WIB
      </div>
      @elseif (in_array($currentDay, [5, 6, 7]) && ($currentTime>= '08:30:00' && $currentTime <= '20:00:00' )) <div class="alert alert-info" role="alert">
        Jam kerja mulai dari 08.30 - 20.00 WIB
        </div>
        @endif
        <!--================== end ==================-->

        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label>STATUS PRESENSI</label>
              <select class="form-control" name="status" id="status" required>
                <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                @php
                $currentDay = date('N'); // Mendapatkan kode hari (1 untuk Senin, 2 untuk Selasa, dst.)
                $currentTime = date('H:i:s'); // Mendapatkan waktu saat ini dalam format "HH:MM:SS"
                @endphp

                <!-- senin -->
                @if ($currentDay == 1 && ($currentTime >= '08:00:00' && $currentTime <= '10:00:00' )) <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                  <option value="hadir">HADIR</option>
                  <option value="remote">REMOTE</option>
                  <option value="izin">IZIN</option>
                  <option value="dinas luar kota">DINAS LUAR KOTA</option>
                  <option value="lembur">LEMBUR</option>
                  <option value="cuti">CUTI</option>
                  @elseif ($currentDay == 1 && ($currentTime >= '10:00:00' && $currentTime <= '22:00:00' )) <option value="terlambat">HADIR</option>
                    <option value="terlambat">REMOTE</option>
                    <option value="izin">IZIN</option>
                    <option value="terlambat">DINAS LUAR KOTA</option>
                    <option value="lembur">LEMBUR</option>
                    <option value="cuti">CUTI</option>
                    @elseif ($currentDay == 1 && ($currentTime >= '22:00:00' && $currentTime <= '23:59:59' )) <option value="tidak bisa presensi" selected>Belum dapat presensi. Harap presensi kembali pada keesokan hari.</option>
                      @elseif (in_array($currentDay, [2, 3]) && ($currentTime>= '00:00:00' && $currentTime <= '23:59:59' )) <option value="tidak bisa presensi" selected>Ciee yang libur kerja, Jangan lupa hiling jangan kerja terus. Kesehatan juga perlu diperhatikan seperti kamu memperhatikan dia :D</option>
                        <!-- end senin -->

                        <!-- selasa & rabu -->
                        @elseif (in_array($currentDay, [2, 3]))
                        <option value="libur" selected>LIBUR</option>
                        @elseif ($currentDay == 4 && ($currentTime>= '00:00:00' && $currentTime <= '11:59:59' )) <option value="tidak bisa presensi" selected>Belum dapat presensi. Dapat presensi mulai pukul 12.00.</option>
                          <!-- end selasa & rabu -->

                          <!-- kamis -->
                          @elseif ($currentDay == 4 && ($currentTime >= '12:00:00' && $currentTime <= '14:00:00' )) <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                            <option value="hadir">HADIR</option>
                            <option value="remote">REMOTE</option>
                            <option value="izin">IZIN</option>
                            <option value="dinas luar kota">DINAS LUAR KOTA</option>
                            <option value="lembur">LEMBUR</option>
                            <option value="cuti">CUTI</option>
                            @elseif ($currentDay == 4 && ($currentTime >= '14:00:00' && $currentTime <= '22:00:00' )) <option value="terlambat">HADIR</option>
                              <option value="terlambat">REMOTE</option>
                              <option value="izin">IZIN</option>
                              <option value="terlambat">DINAS LUAR KOTA</option>
                              <option value="lembur">LEMBUR</option>
                              <option value="cuti">CUTI</option>
                              @elseif ($currentDay == 4 && ($currentTime >= '22:00:00' && $currentTime <= '23:59:59' )) <option value="tidak bisa presensi" selected>Belum dapat presensi. Harap presensi kembali pada keesokan hari.</option>
                                @elseif (in_array($currentDay, [5, 6, 7]) && ($currentTime>= '00:00:00' && $currentTime <= '06:59:59' )) <option value="tidak bisa presensi" selected>Belum dapat presensi. Dapat presensi mulai pukul 07.00.</option>
                                  <!-- end kamis -->

                                  <!-- jumat, sabtu & minggu -->
                                  @elseif (in_array($currentDay, [5, 6, 7]) && ($currentTime >= '07:00:00' && $currentTime <= '08:30:00' )) <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                                    <option value="hadir">HADIR</option>
                                    <option value="remote">REMOTE</option>
                                    <option value="izin">IZIN</option>
                                    <option value="dinas luar kota">DINAS LUAR KOTA</option>
                                    <option value="lembur">LEMBUR</option>
                                    <option value="cuti">CUTI</option>
                                    @elseif (in_array($currentDay, [5, 6, 7]) && ($currentTime >= '08:30:00' && $currentTime <= '22:00:00' )) <option value="terlambat">HADIR</option>
                                      <option value="terlambat">REMOTE</option>
                                      <option value="izin">IZIN</option>
                                      <option value="terlambat">DINAS LUAR KOTA</option>
                                      <option value="lembur">LEMBUR</option>
                                      <option value="cuti">CUTI</option>
                                      @elseif (in_array($currentDay, [5, 6, 7]) && ($currentTime >= '22:00:00' && $currentTime <= '23:59:59' )) <option value="tidak bisa presensi" selected>Belum dapat presensi. Harap presensi kembali pada keesokan hari. </option>
                                        @elseif ($currentDay == 1 && ($currentTime>= '00:00:00' && $currentTime <= '07:59:59' )) <option value="tidak bisa presensi" selected>Belum dapat presensi. Dapat presensi mulai pukul 08.00.</option>
                                          <!-- end jumat, sabtu & minggu -->
                                          @endif
              </select>
              <!-- <select class="form-control" name="status" id="status" required>
                @if (date('H:i:s') >= '07:00:00' && date('H:i:s') <= '09:00:00' ) <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                  <option value="hadir">HADIR</option>
                  <option value="remote">REMOTE</option>
                  <option value="izin">IZIN</option>
                  <option value="dinas luar kota">DINAS LUAR KOTA</option>
                  <option value="lembur">LEMBUR</option>
                  <option value="cuti">CUTI</option>
                  <option value="pulang" disabled>PULANG</option>
                  @elseif (date('H:i') >= '09:00:00' && date('H:i:s') <= '22:00:00' ) <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="terlambat">HADIR</option>
                    <option value="terlambat">REMOTE</option>
                    <option value="izin">IZIN</option>
                    <option value="terlambat">DINAS LUAR KOTA</option>
                    <option value="lembur">LEMBUR</option>
                    <option value="cuti">CUTI</option>
                    <option value="pulang">PULANG</option>
                    @elseif (date('H:i:s') >= '22:00:00' && date('H:i:s') <= '23:59:59' ) || (date('H:i:s')>= '00:00:00' && date('H:i:s') <= '07:00:00' ) <option value="tidak bisa presensi" disabled selected>Belum dapat presensi. Harap pilih status setelah jam 07:00.</option>
                        @else
                        <option value="tidak bisa presensi" disabled selected>Belum dapat presensi. Harap pilih status setelah jam 07:00.</option>
                        @endif
              </select> -->

              @error('status')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>CATATAN</label>
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
              <label>BUKTI PRESENSI</label>
              <div class="input-group">
                <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" capture="camera">
              </div>
              @error('gambar')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
            <div class="mt-3">
              <div class="card" style="width: 12rem;">
                <img id="image-preview" class="card-img-top" src="#" alt="Preview Image" style="display: none;">
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
        <?php
        $currentDay = date('N'); // Mendapatkan kode hari (1 untuk Senin, 2 untuk Selasa, dst.)
        $currentTime = date('H:i:s'); // Mendapatkan waktu saat ini dalam format "HH:MM:SS"
        ?>
        @if (date('H:i:s') >= '22:00:00' && date('H:i:s') <= '23:59:59' ) <button class="btn btn-secondary mr-1 btn-submit" type="submit" disabled><i class="fa fa-paper-plane"></i> SIMPAN</button>
          @elseif ($currentDay == 1 && ($currentTime >= '00:00:00' && $currentTime <= '07:59:59' )) <button class="btn btn-secondary mr-1 btn-submit" type="submit" disabled><i class="fa fa-paper-plane"></i> SIMPAN</button>
            @elseif (in_array($currentDay, [2, 3]) && ($currentTime >= '00:00:00' && $currentTime <= '23:59:59' )) <button class="btn btn-secondary mr-1 btn-submit" type="submit" disabled><i class="fa fa-paper-plane"></i> SIMPAN</button>
              @elseif ($currentDay == 4 && ($currentTime >= '00:00:00' && $currentTime <= '11:59:59' )) <button class="btn btn-secondary mr-1 btn-submit" type="submit" disabled><i class="fa fa-paper-plane"></i> SIMPAN</button>
                @elseif (in_array($currentDay, [5, 6, 7]) && ($currentTime >= '00:00:00' && $currentTime <= '06:59:59' )) <button class="btn btn-secondary mr-1 btn-submit" type="submit" disabled><i class="fa fa-paper-plane"></i> SIMPAN</button>
                  @else
                  <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> SIMPAN</button>
                  @endif

                  <button class="btn btn-warning btn-reset" type="reset"><i class="fa fa-redo"></i> RESET</button>
                  </form>


                  </div>
                  </div>
                  </section>
                  </div>

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
                      const maxFileSizeInBytes = 1024 * 1024; // 1MB
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
                            text: 'Ukuran File Yang Diperbolehkan Dibawah 1MB.',
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