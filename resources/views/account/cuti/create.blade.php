@extends('layouts.account')

@section('title')
Pengajuan Cuti | MIS
@stop

<!--================== BUTTON ==================-->
<style>
  .button-container {
    display: flex;
    justify-content: space-between;
  }

  .button-container button {
    width: 32%;
    /* Adjust width to fit three buttons side by side */
    padding: 10px;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    align-items: center;
    justify-content: center;
  }
</style>
<!--================== END ==================-->

<!--================== CARD CUSTOM ==================-->
<style>
  .card-custom {
    border: 2px solid #007bff;
    /* Border lebih tebal dan berwarna biru */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    /* Shadow lebih besar */
    border-radius: 12px;
    /* Sudut yang lebih bulat */
    padding: 20px;
    background: linear-gradient(145deg, #ffffff, #e6e6e6);
    /* Gradient background */
    margin: 20px 0;
    transition: transform 0.3s;
    /* Transisi untuk efek hover */
  }

  .card-custom:hover {
    transform: translateY(-10px);
    /* Efek hover */
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
    /* Shadow lebih besar saat hover */
  }

  .card-custom .form-group {
    margin-bottom: 20px;
  }

  .card-custom .input-group-text {
    background-color: #f1f1f1;
  }

  .card-custom .form-control {
    border-radius: 4px;
  }

  .btn-custom {
    margin: 10px 0;
    border-radius: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }
</style>
<!--================== END ==================-->

<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<style>
  .custom-file-upload {
    position: relative;
    overflow: hidden;
    margin-top: 10px;
  }

  .inputfile {
    width: 0.1px;
    height: 0.1px;
    opacity: 0;
    overflow: hidden;
    position: absolute;
    z-index: -1;
  }

  .file-upload {
    cursor: pointer;
    display: inline-block;
    padding: 10px 20px;
    color: #fff;
    background-color: #007bff;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    transition: background-color 0.3s;
  }

  .file-upload:hover {
    background-color: #0056b3;
  }

  #file-selected {
    display: block;
    margin-top: 5px;
    color: #888;
  }

  .image-preview {
    margin-top: 10px;
    display: none;
  }

  .image-preview img {
    max-width: 100%;
    height: auto;
    border-radius: 5px;
  }
</style>
<!--================== END ==================-->

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>PENGAJUAN CUTI</h1>
    </div>

    <div class="section-body">
      @if(session('status') === 'error')
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <b>{{ session('message') }}</b>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      @endif

      <form action="{{ route('account.PerjalananDinas.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!--================== DETAIL DATA KARYAWAN ==================-->
        <div class="card">
          <div class="card-header">
            <h4>Data Karyawan</h4>
          </div>

          <div class="card-body">

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Nama Karyawan</label>
                  <input type="text" name="user_name" value="{{ Auth::user()->full_name }}" class="form-control" readonly>
                  <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
                  @error('user_id')
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
                  <label>Email Karyawan</label>
                  <div class="input-group">
                    <input type="text" name="email" value="{{ Auth::user()->email }}" class="form-control" readonly>
                  </div>
                  @error('tempat')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Telp Karyawan</label>
                  <div class="input-group">
                    <input type="text" name="camp" value="{{ Auth::user()->telp }}" class="form-control" readonly>
                  </div>
                  @error('camp')
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
                  <label>Role Karyawan</label>
                  <input type="text" name="tanggal_mulai" id="tanggal_mulai" value="{{ Auth::user()->level }}" class="form-control" readonly>
                </div>
                @error('tanggal_mulai')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>

              @php
              use Carbon\Carbon;
              $lamaBekerja = Carbon::parse(auth()->user()->created_at)->diff(Carbon::now());
              @endphp

              <div class="col-md-6">
                <div class="form-group">
                  <label>Lama Bekerja</label>
                  <input type="text"
                    class="form-control"
                    value="{{ $lamaBekerja->y }} Tahun {{ $lamaBekerja->m }} Bulan {{ $lamaBekerja->d }} Hari"
                    readonly>
                </div>
              </div>
            </div>

          </div>
        </div>
        <!--================== END ==================-->

        <!--================== DATA PENGAJUAN CUTI ==================-->
        <div class="card">
          <div class="card-header">
            <h4>Data Pengajuan Cuti</h4>
          </div>

          <div class="card-body">

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Jenis Cuti</label>
                  <select class="form-control" name="jenis_cuti" style="height: auto;" required>
                    <option value="" disabled selected>-- PILIH JENIS CUTI --</option>
                    <option value="pending">PENDING</option>
                    <option value="terbayar">TERBAYAR</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Tanggal Mulai Cuti</label>
                  <div class="input-group">
                    <input type="date" name="tanggal_mulai_cuti" id="tanggal_mulai_cuti" class="form-control" required>
                  </div>
                  @error('tanggal_mulai_cuti')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>Tanggal Selesai Cuti</label>
                  <div class="input-group">
                    <input type="date" name="tanggal_selesai_cuti" id="tanggal_selesai_cuti" class="form-control" required>
                  </div>
                  @error('tanggal_selesai_cuti')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>Total Cuti</label>
                  <input type="text" name="total_hari_cuti" id="total_hari_cuti" class="form-control" readonly>
                </div>
                @error('total_hari_cuti')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Keterangan</label>
                  <textarea name="keterangan" id="keterangan" class="form-control" style="min-height: 120px;"></textarea>
                </div>
                @error('keterangan')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

          </div>
        </div>
        <!--================== END ==================-->

        <div class="mt-3">
          <div class="d-flex flex-md-nowrap flex-wrap gap-2 mt-4">

            <!-- Tombol Simpan -->
            <button type="submit"
              class="btn btn-primary btn-submit rounded-pill w-100 w-md-auto mb-2 mb-md-0">
              <i class="fa fa-paper-plane"></i> SIMPAN
            </button>

            <!-- Tombol Kembali -->
            <a href="{{ route('account.gaji.index') }}"
              class="btn btn-warning btn-submit rounded-pill w-100 w-md-auto mb-2 mb-md-0">
              <i class="fa fa-undo"></i> KEMBALI
            </a>

          </div>
        </div>

      </form>

    </div>
  </section>
</div>

<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.getElementById('gambar').addEventListener('change', function(event) {
    var fileInput = event.target;
    var file = fileInput.files[0];
    var fileName = file.name;
    var fileSize = (file.size / 1024).toFixed(2); // in KB
    var allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];

    if (!allowedTypes.includes(file.type)) {
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Only PNG, JPEG, and JPG files are allowed. Please choose a valid file type.'
      });
      return;
    }

    if (fileSize > 3000) {
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'File size exceeds the maximum limit of 3MB. Please choose a smaller file.'
      });
      return;
    }

    document.getElementById('file-selected').innerHTML = fileName + ' (' + fileSize + ' KB)';

    var reader = new FileReader();
    reader.onload = function() {
      var output = document.getElementById('imagePreview');
      output.innerHTML = `<img src="${reader.result}">`;
      output.style.display = 'block';
    };
    reader.readAsDataURL(file);
  });

  for (var i = 2; i <= 20; i++) {
    var fileInput = document.getElementById('gambar' + i);
    if (fileInput) {
      (function(i) { // Capture the value of i in a closure
        fileInput.addEventListener('change', function(event) {
          var fileInput = event.target;
          var file = fileInput.files[0];
          var fileName = file.name;
          var fileSize = (file.size / 1024).toFixed(2); // in KB
          var allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];

          if (!allowedTypes.includes(file.type)) {
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: 'Only PNG, JPEG, and JPG files are allowed. Please choose a valid file type.'
            });
            return;
          }

          if (fileSize > 3000) {
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: 'File size exceeds the maximum limit of 3MB. Please choose a smaller file.'
            });
            return;
          }

          document.getElementById('file-selected' + i).innerHTML = fileName + ' (' + fileSize + ' KB)';

          var reader = new FileReader();
          reader.onload = function() {
            var output = document.getElementById('imagePreview' + i);
            output.innerHTML = `<img src="${reader.result}">`;
            output.style.display = 'block';
          };
          reader.readAsDataURL(file);
        });
      })(i); // Pass the current value of i to the closure
    }
  }
</script>
<!--================== END ==================-->

<!--================== MENGHITUNG TOTAL LAMA CUTI ==================-->
<script>
  const tanggalMulai = document.getElementById('tanggal_mulai_cuti');
  const tanggalSelesai = document.getElementById('tanggal_selesai_cuti');
  const totalHari = document.getElementById('total_hari_cuti');

  function hitungHariCuti() {
    if (tanggalMulai.value && tanggalSelesai.value) {
      const start = new Date(tanggalMulai.value);
      const end = new Date(tanggalSelesai.value);

      if (end < start) {
        totalHari.value = '';
        alert('Tanggal selesai tidak boleh lebih awal dari tanggal mulai');
        tanggalSelesai.value = '';
        return;
      }

      const diffTime = end - start;
      const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;

      totalHari.value = diffDays + ' Hari';
    }
  }

  tanggalMulai.addEventListener('change', hitungHariCuti);
  tanggalSelesai.addEventListener('change', hitungHariCuti);
</script>
<!--================== END ==================-->

@stop