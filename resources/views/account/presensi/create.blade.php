@extends('layouts.account')

@section('title')
Presensi Karyawan - MANAGEMENT
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
          <h4><i class="fas fa-dice-d6"></i> PRESENSI KARYAWAN</h4>
        </div>

        <div class="card-body">

          <form action="{{ route('account.presensi.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>STATUS PRESENSI</label>
                  <select class="form-control" name="status" id="status" required>
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="hadir">HADIR</option>
                    <option value="remote">REMOTE</option>
                    <option value="izin">IZIN</option>
                    <option value="pulangcepat">PULANG CEPAT</option>
                    <option value="luarkota">DINAS LUAR KOTA</option>
                    <option value="lembur">LEMBUR</option>
                    <option value="cuti">CUTI</option>
                    <option value="pulang">PULANG</option>
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
                </div>
                <div class="mt-3">
                  <div class="card" style="width: 12rem;">
                    <img id="image-preview" class="card-img-top" src="#" alt="Preview Image" style="display: none;">
                  </div>
                </div>
              </div>
            </div>

            <div class="card-body">
              <!-- ... existing form ... -->

              <!-- Display current location -->
              <div id="currentLocation" class="mt-3">
                <h6>Current Location:</h6>
                <p id="locationText">Detecting...</p>
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

<!-- LOKASI -->
@section('scripts')
<script>
  // Function to display the current location
  function showLocation(position) {
    const latitude = position.coords.latitude;
    const longitude = position.coords.longitude;

    const locationText = `Latitude: ${latitude.toFixed(6)}, Longitude: ${longitude.toFixed(6)}`;
    document.getElementById('locationText').textContent = locationText;
  }

  // Function to handle location detection error
  function locationError(error) {
    let errorMessage = 'An error occurred while detecting the location: ';
    switch (error.code) {
      case error.PERMISSION_DENIED:
        errorMessage += 'Permission was denied by the user.';
        break;
      case error.POSITION_UNAVAILABLE:
        errorMessage += 'Location information is unavailable.';
        break;
      case error.TIMEOUT:
        errorMessage += 'The request to get user location timed out.';
        break;
      default:
        errorMessage += 'An unknown error occurred.';
    }
    document.getElementById('locationText').textContent = errorMessage;
  }

  // Check if geolocation is available
  if ('geolocation' in navigator) {
    // Try to get the current location
    navigator.geolocation.getCurrentPosition(showLocation, locationError);
  } else {
    document.getElementById('locationText').textContent = 'Geolocation is not available';
  }
</script>
@endsection


<!-- END LOKASI -->

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