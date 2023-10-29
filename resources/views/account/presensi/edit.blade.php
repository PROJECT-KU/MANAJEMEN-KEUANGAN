@extends('layouts.account')

@section('title')
Update Presensi Karyawan | MANAGEMENT
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
          <h4><i class="fas fa-user-clock"></i> UPDATE PRESENSI KARYAWAN</h4>
        </div>

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
          <select class="form-control" name="status" id="status" disabled>
            <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
            <option value="hadir" {{ $presensi->status == 'hadir' ? 'selected' : '' }}>HADIR</option>
            <option value="remote" {{ $presensi->status == 'remote' ? 'selected' : '' }}>REMOTE</option>
            <option value="izin" {{ $presensi->status == 'izin' ? 'selected' : '' }}>IZIN</option>
            <option value="dinas luar kota" {{ $presensi->status == 'dinas luar kota' ? 'selected' : '' }}>DINAS LUAR KOTA</option>
            <option value="lembur" {{ $presensi->status == 'lembur' ? 'selected' : '' }}>LEMBUR</option>
            <option value="cuti" {{ $presensi->status == 'cuti' ? 'selected' : '' }}>CUTI</option>
            <option value="terlambat" {{ $presensi->status == 'terlambat' ? 'selected' : '' }} hidden>TERLAMBAT</option>
            <option value="pulang" {{ $presensi->status == 'pulang' ? 'selected' : '' }}>PULANG</option>
          </select>
        </div>
      </div>
      @if ($presensi->status_pulang == null)
      <div class="col-md-6">
        <div class="form-group">
          <label>Status Presensi Pulang</label>
          <select class="form-control" name="status_pulang" id="status_pulang">
            <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
            <option value="pulang" {{ $presensi->status_pulang == 'pulang' ? 'selected' : '' }}>PULANG</option>
          </select>
        </div>
      </div>
      @else
      <div class="col-md-6">
        <div class="form-group">
          <label>Status Presensi Pulang</label>
          <select class="form-control" name="status_pulang" id="status_pulang" disabled>
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
            <option value="hadir" {{ $presensi->status == 'hadir' ? 'selected' : '' }}>HADIR</option>
            <option value="remote" {{ $presensi->status == 'remote' ? 'selected' : '' }}>REMOTE</option>
            <option value="izin" {{ $presensi->status == 'izin' ? 'selected' : '' }}>IZIN</option>
            <option value="dinas luar kota" {{ $presensi->status == 'dinas luar kota' ? 'selected' : '' }}>DINAS LUAR KOTA</option>
            <option value="lembur" {{ $presensi->status == 'lembur' ? 'selected' : '' }}>LEMBUR</option>
            <option value="cuti" {{ $presensi->status == 'cuti' ? 'selected' : '' }}>CUTI</option>
            <option value="terlambat" {{ $presensi->status == 'terlambat' ? 'selected' : '' }} hidden>TERLAMBAT</option>
            <option value="pulang" {{ $presensi->status == 'pulang' ? 'selected' : '' }}>PULANG</option>
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
            <option value="hadir" {{ $presensi->status == 'hadir' ? 'selected' : '' }}>HADIR</option>
            <option value="remote" {{ $presensi->status == 'remote' ? 'selected' : '' }}>REMOTE</option>
            <option value="izin" {{ $presensi->status == 'izin' ? 'selected' : '' }}>IZIN</option>
            <option value="dinas luar kota" {{ $presensi->status == 'dinas luar kota' ? 'selected' : '' }}>DINAS LUAR KOTA</option>
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
          <select class="form-control" name="status_pulang" id="status_pulang">
            <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
            <option value="pulang" {{ $presensi->status_pulang == 'pulang' ? 'selected' : '' }}>PULANG</option>
          </select>
        </div>
      </div>
    </div>
    @endif

    <div class="row">
      <div class="col-md-6">
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
      <div class="col-md-6">
        <div class="form-group">
          <label>Bukti Presensi</label>
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
          <a href="{{ asset('images/' . $presensi->gambar) }}" data-lightbox="{{ $presensi->id }}">
            <div class="card" style="width: 12rem;">
              <img id="image-preview" style="width: 200px; height:200px;" class="card-img-top" src="{{ asset('images/' . $presensi->gambar) }}" alt="Preview Image">
            </div>
          </a>
        </div>
      </div>
    </div>
    @if ($presensi->status_pulang == null)
    <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> SIMPAN</button>
    @else
    <button class="btn btn-primary mr-1 btn-secondary" type="submit" disabled><i class="fa fa-paper-plane"></i> SIMPAN</button>
    @endif
    <a href="{{ route('account.presensi.index') }}" class="btn btn-info mr-1">
      <i class="fa fa-list"></i> LIST PRESENSI KARYAWAN
    </a>

    </form>

</div>
</div>
</div>
</section>
</div>

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