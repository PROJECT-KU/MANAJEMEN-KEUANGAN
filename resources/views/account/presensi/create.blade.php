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
            @if ((date('H:i:s') >= '22:00:00' && date('H:i:s') <= '23:59:59' ) || (date('H:i:s')>= '00:00:00' && date('H:i:s') <= '07:00:00' )) <div class="alert alert-warning" role="alert">
                Mohon Maaf.. Kamu Di Luar Jam Kerja! Silahkan Presensi Setelah Jam 07.00 Ya!
        </div>
        @endif

        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label>STATUS PRESENSI</label>
              <select class="form-control" name="status" id="status" required>
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
                    @elseif (date('H:i:s') >= '22:00:00' && date('H:i:s') <= '23:59:59' ) || (date('H:i:s')>= '00:00:00' && date('H:i:s') <= '07:00:00' ) <!-- <option value="terlambat">HADIR</option>
                        <option value="terlambat">REMOTE</option>
                        <option value="izin">IZIN</option>
                        <option value="terlambat">DINAS LUAR KOTA</option>
                        <option value="lembur">LEMBUR</option>
                        <option value="cuti">CUTI</option>
                        <option value="pulang">PULANG</option> -->
                        @else
                        <option value="tidak bisa presensi" disabled selected>Belum dapat presensi. Harap pilih status setelah jam 07:00.</option>
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

        <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> SIMPAN</button>
        <button class="btn btn-warning btn-reset" type="reset"><i class="fa fa-redo"></i> RESET</button>

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