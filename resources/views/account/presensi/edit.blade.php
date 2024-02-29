@extends('layouts.account')

@section('title')
Update Presensi Karyawan | MIS
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
      <h1>UPDATE PRESENSI KARYAWAN</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <!-- <div class="card-header">
          <h4><i class="fas fa-user-clock"></i> UPDATE PRESENSI KARYAWAN</h4>
        </div> -->

        <div class="card-body">

          <form action="{{ route('account.presensi.update', $presensi->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">

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

    @if (Auth::user()->level == 'karyawan' || Auth::user()->level == 'staff' || Auth::user()->level == 'trainer')
    @php
    $todayPresensi = \App\Presensi::where('user_id', Auth::user()->id)
    ->whereDate('created_at', now()->toDateString())
    ->first();
    @endphp
    @if ($todayPresensi)
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Status Presensi Kehadiran</label>
          <select class="form-control" name="status" id="status" readonly>
            <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
            <option value="alpha" {{ $presensi->status == 'alpha' ? 'selected' : '' }}>ALPHA</option>
            <option value="hadir" {{ $presensi->status == 'hadir' ? 'selected' : '' }}>HADIR</option>
            <option value="camp jogja" {{ $presensi->status == 'camp jogja' ? 'selected' : '' }}>CAMP JOGJA</option>
            <option value="perjalanan luar kota jawa" {{ $presensi->status == 'perjalanan luar kota jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA DALAM JAWA</option>
            <option value="perjalanan luar kota luar jawa" {{ $presensi->status == 'perjalanan luar kota luar jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA LUAR JAWA</option>
            <option value="camp luar kota" {{ $presensi->status == 'camp luar kota' ? 'selected' : '' }}>CAMP LUAR KOTA</option>
            <option value="remote" {{ $presensi->status == 'remote' ? 'selected' : '' }}>REMOTE</option>
            <option value="izin" {{ $presensi->status == 'izin' ? 'selected' : '' }}>IZIN</option>
            <!-- <option value="lembur" {{ $presensi->status == 'lembur' ? 'selected' : '' }}>LEMBUR</option>
            <option value="cuti" {{ $presensi->status == 'cuti' ? 'selected' : '' }}>CUTI</option>
            <option value="terlambat" {{ $presensi->status == 'terlambat' ? 'selected' : '' }} hidden>TERLAMBAT</option>
            <option value="pulang" {{ $presensi->status == 'pulang' ? 'selected' : '' }}>PULANG</option> -->
          </select>
        </div>
      </div>
      @if ($presensi->status_pulang == null)
      <div class="col-md-6">
        <div class="form-group">
          <label>Status Presensi Pulang</label>
          <select class="form-control" name="status_pulang" id="status_pulang" required>
            <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
            <option value="pulang" {{ $presensi->status_pulang == 'pulang' ? 'selected' : '' }}>PULANG</option>
          </select>
        </div>
      </div>
      @else
      <div class="col-md-6">
        <div class="form-group">
          <label>Status Presensi Pulang</label>
          <select class="form-control" name="status_pulang" id="status_pulang" readonly>
            <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
            <option value="pulang" {{ $presensi->status_pulang == 'pulang' ? 'selected' : '' }}>PULANG</option>
          </select>
        </div>
      </div>
      @endif
    </div>
    @else
    <div class="row">
      <div class="col-md-12">
        <div class="form-group">
          <label>Status Presensi Kehadiran</label>
          <select class="form-control" name="status" id="status">
            <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
            <option value="alpha" {{ $presensi->status == 'alpha' ? 'selected' : '' }}>ALPHA</option>
            <option value="hadir" {{ $presensi->status == 'hadir' ? 'selected' : '' }}>HADIR</option>
            <option value="camp jogja" {{ $presensi->status == 'camp jogja' ? 'selected' : '' }}>CAMP JOGJA</option>
            <option value="perjalanan luar kota jawa" {{ $presensi->status == 'perjalanan luar kota jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA DALAM JAWA</option>
            <option value="perjalanan luar kota luar jawa" {{ $presensi->status == 'perjalanan luar kota luar jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA LUAR JAWA</option>
            <option value="camp luar kota" {{ $presensi->status == 'camp luar kota' ? 'selected' : '' }}>CAMP LUAR KOTA</option>
            <option value="remote" {{ $presensi->status == 'remote' ? 'selected' : '' }}>REMOTE</option>
            <option value="izin" {{ $presensi->status == 'izin' ? 'selected' : '' }}>IZIN</option>
          </select>
        </div>
      </div>
      <!--<div class="col-md-6">
                <div class="form-group">
                  <label>NAMA KARYAWAN</label>
                  <div class="input-group">
                    <input name="user_id" id="full_name" placeholder="Masukkan catatan" class="form-control" value="{{ $users->first()->full_name }}" readonly>
                  </div>
                </div>
              </div>-->
    </div>
    @endif
    @else
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Status Presensi Kehadiran</label>
          <select class="form-control" name="status" id="status">
            <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
            <option value="alpha" {{ $presensi->status == 'alpha' ? 'selected' : '' }}>ALPHA</option>
            <option value="hadir" {{ $presensi->status == 'hadir' ? 'selected' : '' }}>HADIR</option>
            <option value="camp jogja" {{ $presensi->status == 'camp jogja' ? 'selected' : '' }}>CAMP JOGJA</option>
            <option value="perjalanan luar kota jawa" {{ $presensi->status == 'perjalanan luar kota jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA DALAM JAWA</option>
            <option value="perjalanan luar kota luar jawa" {{ $presensi->status == 'perjalanan luar kota luar jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA LUAR JAWA</option>
            <option value="camp luar kota" {{ $presensi->status == 'camp luar kota' ? 'selected' : '' }}>CAMP LUAR KOTA</option>
            <option value="remote" {{ $presensi->status == 'remote' ? 'selected' : '' }}>REMOTE</option>
            <option value="izin" {{ $presensi->status == 'izin' ? 'selected' : '' }}>IZIN</option>
            <!-- <option value="cuti" {{ $presensi->status == 'cuti' ? 'selected' : '' }}>CUTI</option>
            <option value="terlambat" {{ $presensi->status == 'terlambat' ? 'selected' : '' }}>TERLAMBAT</option> -->
          </select>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Status Presensi Pulang</label>
          <select class="form-control" name="status_pulang" id="status_pulang">
            <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
            <option value="pulang" {{ $presensi->status_pulang == 'pulang' ? 'selected' : '' }}>PULANG</option>
          </select>
        </div>
      </div>
    </div>
    @endif

    <div id="image-section" style="display: none;">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Bukti Presensi Masuk</label>
            <!-- <div class="input-group">
            <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" capture="camera" disabled>
          </div>
          @error('gambar')
          <div class="invalid-feedback" style="display: block">
            {{ $message }}
          </div>
          @enderror -->
            <div class="mb-3" style="width: 200px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);">
              <a href="{{ asset('images/' . $presensi->gambar) }}" data-lightbox="{{ $presensi->id }}">
                <div class="cardgambar" style="width: 200px;">
                  <img id="image-preview" style="width: 200px; height:200px;" class="card-img-top" src="{{ asset('images/' . $presensi->gambar) }}" alt="Preview Image">
                </div>
              </a>
            </div>
          </div>
        </div>
        @if ($presensi->gambar_pulang == null)
        <div class="col-md-6">
          <div class="form-group">
            <label for="gambar_pulang">Bukti Presensi</label>
            <input type="file" name="gambar_pulang" id="gambar_pulang" class="form-control custom-file-upload" accept="image/*" capture="camera">
          </div>
          @error('gambar_pulang')
          <div class="invalid-feedback" style="display: block">
            {{ $message }}
          </div>
          @enderror
          <div class="mb-3">
            <div class="cardgambar_pulang" style="width: 200px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);">
              <img id="image-preview-pulang" class="card-img-top" src="#" alt="Preview Image" style="display: none; width: 200px; height: 200px;">
            </div>
          </div>
        </div>
        @else
        <div class="col-md-6">
          <div class="form-group">
            <label>Bukti Presensi Pulang</label>
            <div class="input-group">
              <input type="file" name="gambar_pulang" id="gambar_pulang" class="form-control" accept="image/*" capture="camera" disabled>
            </div>
            @error('gambar_pulang')
            <div class="invalid-feedback" style="display: block">
              {{ $message }}
            </div>
            @enderror
          </div>
          <div class="mb-3" style="width: 200px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);">
            <a href="{{ asset('images/' . $presensi->gambar_pulang) }}" data-lightbox="{{ $presensi->id }}">
              <div class="cardgambar" style="width: 200px;">
                <img id="image-preview" style="width: 200px; height:200px;" class="card-img-top" src="{{ asset('images/' . $presensi->gambar_pulang) }}" alt="Preview Image">
              </div>
            </a>
          </div>
        </div>
        @endif
      </div>
    </div>

    <div id="catatan-section" style="display: none;">
      <div class="row">
        <div class="col-md-12">
          <div class="form-group">
            <label>Catatan</label>
            <div class="input-group">
              <textarea name="note" id="note" placeholder="Masukkan catatan" class="form-control">{{ $presensi->note }}</textarea>
            </div>
            @error('note')
            <div class="invalid-feedback" style="display: block">
              {{ $message }}
            </div>
            @enderror
          </div>
        </div>
      </div>
    </div>

    @if ($presensi->status_pulang == null)
    <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> SIMPAN</button>
    @elseif (Auth::user()->level == 'manager')
    <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> SIMPAN</button>
    @else
    <button class="btn btn-primary mr-1 btn-secondary" type="submit" disabled><i class="fa fa-paper-plane"></i> SIMPAN</button>
    @endif
    <a href="{{ route('account.presensi.index') }}" class="btn btn-info">
      <i class="fa fa-undo"></i> KEMBALI
    </a>

    </form>

</div>
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
    var imageSection = document.getElementById('image-section');
    var GambarUpload = document.getElementById('gambar_pulang');

    if (statusSelect.value === 'izin') {
      catatanSection.style.display = 'block';
      imageSection.style.display = 'block';
      GambarUpload.setAttribute('required', 'required');
    } else {
      catatanSection.style.display = 'none';
      imageSection.style.display = 'none';
    }
  }

  // Call the function initially
  toggleSections();

  // Add an event listener to the status dropdown
  document.getElementById('status').addEventListener('change', toggleSections);
</script>
<!--================== end ==================-->

<!--================== maksimal upload gambar & jenis file yang di perbolehkan ==================-->
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

<script>
  document.getElementById('gambar_pulang').addEventListener('change', function() {
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
<!--================== end ==================-->

<!--================== upload image ==================-->
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
<script>
  const imageInputPulang = document.getElementById('gambar_pulang');
  const imagePreviewPulang = document.getElementById('image-preview-pulang');

  imageInputPulang.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        imagePreviewPulang.src = e.target.result;
        imagePreviewPulang.style.display = 'block'; // Show the preview
      };
      reader.readAsDataURL(file);
    }
  });
</script>
<!--================== end ==================-->

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

    }, 10);
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