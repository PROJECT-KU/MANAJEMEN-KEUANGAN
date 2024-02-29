@extends('layouts.account')

@section('title')
Detail Presensi Karyawan | MIS
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
      <h1>DETAIL PRESENSI KARYAWAN</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <!-- <div class="card-header">
          <h4><i class="fas fa-user-clock"></i> DETAIL PRESENSI KARYAWAN</h4>
        </div> -->

        <div class="card-body">

          <form action="" method="GET" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">


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
              @if ($presensi->status_pulang == null)
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status Presensi Pulang</label>
                  <select class="form-control" name="status_pulang" id="status_pulang" disabled>
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
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status Presensi Kehadiran</label>
                  <select class="form-control" name="status" id="status" disabled>
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
                  <select class="form-control" name="status_pulang" id="status_pulang" disabled>
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="pulang" {{ $presensi->status_pulang == 'pulang' ? 'selected' : '' }}>PULANG</option>
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
                  <select class="form-control" name="status" id="status" disabled>
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
                  <select class="form-control" name="status_pulang" id="status_pulang" disabled>
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
                    <div class="mb-3" style="width: 200px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);">
                      <a href="{{ asset('images/' . $presensi->gambar) }}" data-lightbox="{{ $presensi->id }}">
                        <div class="cardgambar" style="width: 200px;">
                          <img id="image-preview" style="width: 200px; height: 200px;" class="card-img-top" src="{{ asset('images/' . $presensi->gambar) }}" alt="Preview Image">
                        </div>
                      </a>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Presensi Pulang</label>
                    <div class="mb-3" style="width: 200px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);">
                      <a href="{{ asset('images/' . $presensi->gambar_pulang) }}" data-lightbox="{{ $presensi->id }}">
                        <div class="cardgambar" style="width: 200px;">
                          <img id="image-preview-pulang" style="width: 200px; height: 200px;" class="card-img-top" src="{{ asset('images/' . $presensi->gambar_pulang) }}" alt="Belum Presensi Pulang">
                        </div>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div id="catatan-section" style="display: none;">
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Catatan</label>
                    <div class="input-group">
                      <textarea name="note" id="note" placeholder="Masukkan catatan" class="form-control" disabled>{{ $presensi->note }}</textarea>
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

<!--================== view upload image ==================-->
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
  const imageInputPulang = document.getElementById('gambargambar_pulang');
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

<!--================== Include CKEditor JS ==================-->
<script src="//cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script>
  CKEDITOR.replace('note');
</script>
<!--================== end ==================-->

<!--================== submit & reset button loader ==================-->
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
<!--================== end ==================-->

@stop