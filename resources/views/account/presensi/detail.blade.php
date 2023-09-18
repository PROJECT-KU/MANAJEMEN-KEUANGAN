@extends('layouts.account')

@section('title')
Detail Presensi Karyawan | MANAGEMENT
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
          <h4><i class="fas fa-user-clock"></i> DETAIL PRESENSI KARYAWAN</h4>
        </div>

        <div class="card-body">

          <form action="" method="GET" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">

            if (Auth::user()->level == 'karyawan')
            @php
            $todayPresensi = \App\Presensi::where('user_id', Auth::user()->id)
            ->whereDate('created_at', now()->toDateString())
            ->first();
            @endphp
            @if ($todayPresensi)
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>STATUS PRESENSI KEHADIRAN</label>
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
              <div class="col-md-6">
                <div class="form-group">
                  <label>STATUS PRESENSI PULANG</label>
                  <select class="form-control" name="status_pulang" id="status_pulang" disabled>
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="pulang" {{ $presensi->status_pulang == 'pulang' ? 'selected' : '' }}>PULANG</option>
                  </select>
                </div>
              </div>
            </div>
            @else
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>STATUS PRESENSI</label>
                  <select class="form-control" name="status" id="status" disabled="true">
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
                  <label>NAMA KARYAWAN</label>
                  <div class="input-group">
                    <input name="user_id" id="full_name" placeholder="Masukkan catatan" class="form-control" value="{{ $users->first()->full_name }}" readonly>
                  </div>
                </div>
              </div>
            </div>
            @endif
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>CATATAN</label>
                  <div class="input-group">
                    <textarea name="note" id="note" placeholder="Masukkan catatan" class="form-control" readonly>{{ $presensi->note }}</textarea>
                  </div>
                  @error('note')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
              <div class="col-md-6">
                <!--<div class="form-group">
                  <label>GAMBAR</label>
                  <div class="input-group">
                    <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" capture="camera">
                  </div>
                  <i class="fas fa-info mt-2" style="color: red"></i> Upload Gambar atau Gunakan Kamera
                  @error('gambar')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>-->
                <div class="mt-3">
                  <label>BUKTI PRESENSI</label>
                  <a href="{{ asset('images/' . $presensi->gambar) }}" data-lightbox="{{ $presensi->id }}">
                    <div class="card" style="width: 12rem;">
                      <img id="image-preview" style="width: 200px; height:200px;" class="card-img-top" src="{{ asset('images/' . $presensi->gambar) }}" alt="Preview Image">
                    </div>
                  </a>
                </div>
              </div>
            </div>

            <a href="{{ route('account.presensi.index') }}" class="btn btn-info mr-1">
              <i class="fa fa-list"></i> LIST PRESENSI KARYAWAN
            </a>

          </form>

        </div>
      </div>
    </div>
  </section>
</div>

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