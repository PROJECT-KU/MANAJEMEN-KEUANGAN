@extends('layouts.account')

@section('title')
Profil | MANAGEMENT
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>PROFIL</h1>
    </div>



    <div class="section-body">

      <!-- jika maintenace aktif -->
      @if (!$maintenances->isEmpty())
      @foreach($maintenances as $maintenance)
      @if ($maintenance->status === 'aktif' || ($maintenance->end_date !== null && now() <= Carbon\Carbon::parse($maintenance->end_date)->endOfDay()))
        <div class="alert alert-danger" role="alert" style="text-align: center; background-image: url('{{ asset('/images/background-maintenance.png') }}'">


          <b style="font-size: 25px; text-transform:uppercase">{{ $maintenance->title }}</b><br>
          <img style="width: 100px; height:100px;" src="{{ asset('images/' . $maintenance->gambar) }}" alt="Gambar Presensi" class="img-thumbnail">
          <p style="font-size: 20px;" class="mt-2">{{ $maintenance->note }}</p>
          <p style="font-size: 15px;">Dari Tanggal {{ \Carbon\Carbon::parse($maintenance->start_date)->isoFormat('D MMMM YYYY HH:mm') }} - {{ \Carbon\Carbon::parse($maintenance->end_date)->isoFormat('D MMMM YYYY HH:mm') }}</p>


        </div </ </div>
        @endif
        @endforeach
        @endif
        <!-- end -->

        <div class="card">
          <div class="card-header">
            <h4><i class="fas fa-user-edit"></i> PROFIL</h4>
          </div>

          <div class="card-body">

            <form action="{{ route('account.profil.update', $user->id) }}" method="POST" enctype="multipart/form-data">
              @csrf
              @method('PUT')
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $user->full_name) }}" class="form-control currency" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z ]/i.test(event.key)" required>

                    @error('full_name')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" readonly>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Nama Perusahaan</label>
                    <input type="text" name="company" class="form-control" value="{{ old('company', $user->company) }}" maxlength="30" minlength="5" onkeypress="return/[A-Z]/i.test(event.key)" style="text-transform:uppercase" required>

                    @error('company')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>No Telp</label>
                    <input type="text" name="telp" class="form-control" value="{{ old('telp', $user->telp) }}" maxlength="14" minlength="8" onkeypress="return event.charCode >= 48 && event.charCode <=57" required>

                    @error('telp')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Level</label>
                    <select class="form-control" name="level" disabled="true">
                      <option value="" disabled selected>Silahkan Pilih</option>
                      <option value="admin" {{ $user->level == 'admin' ? 'selected' : '' }}>Admin</option>
                      <option value="user" {{ $user->level == 'user' ? 'selected' : '' }}>User</option>
                      <option value="manager" {{ $user->level == 'manager' ? 'selected' : '' }}>Manager</option>
                      <option value="staff" {{ $user->level == 'staff' ? 'selected' : '' }}>Staff</option>
                      <option value="karyawan" {{ $user->level == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                    </select>

                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label>Jenis</label>
                    <select class="form-control" name="jenis" disabled="true">
                      <option value="" disabled selected>Silahkan Pilih</option>
                      <option value="bisnis" {{ $user->jenis == 'bisnis' ? 'selected' : '' }}>Bisnis</option>
                      <option value="penyewaan" {{ $user->jenis == 'penyewaan' ? 'selected' : '' }}>Penyewaan</option>
                      <option value="kasir" {{ $user->jenis == 'kasir' ? 'selected' : '' }}>kasir</option>
                      <option value="perorangan" {{ $user->jenis == 'perorangan' ? 'selected' : '' }}>Perorangan</option>
                    </select>

                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9 ]/i.test(event.key)" required>

                    @error('username')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>
              </div>



              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>NIK</label>
                    <input type="text" name="nik" class="form-control" value="{{ old('nik', $user->nik) }}" placeholder="Masukan NIK" maxlength="30" minlength="5" onkeypress="return event.charCode >= 48 && event.charCode <=57" required>
                    @error('nik')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>NOMOR REKENING</label>
                    <input type="text" name="norek" class="form-control" value="{{ old('norek', $user->norek) }}" placeholder="Masukan Nomor Rekening" maxlength="30" minlength="5" onkeypress="return event.charCode >= 48 && event.charCode <=57" required>
                    @error('norek')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>BANK</label>
                    <select class="form-control bank" name="bank" required>
                      <option value="" disabled selected>Silahkan Pilih</option>
                      <option value="002" {{ $user->bank == '002' ? 'selected' : '' }}>BRI</option>
                      <option value="008" {{ $user->bank == '008' ? 'selected' : '' }}>BANK MANDIRI</option>
                      <option value="009" {{ $user->bank == '009' ? 'selected' : '' }}>BNI</option>
                      <option value="200" {{ $user->bank == '200' ? 'selected' : '' }}>BANK TABUNGAN NEGARA</option>
                      <option value="011" {{ $user->bank == '011' ? 'selected' : '' }}>BANK DANAMON</option>
                      <option value="013" {{ $user->bank == '013' ? 'selected' : '' }}>BANK PERMATA</option>
                      <option value="014" {{ $user->bank == '014' ? 'selected' : '' }}>BCA</option>
                      <option value="016" {{ $user->bank == '016' ? 'selected' : '' }}>MAYBANK</option>
                      <option value="019" {{ $user->bank == '019' ? 'selected' : '' }}>PANINBANK</option>
                      <option value="022" {{ $user->bank == '022' ? 'selected' : '' }}>CIMB NIAGA</option>
                      <option value="023" {{ $user->bank == '023' ? 'selected' : '' }}>BANK UOB INDONESIA</option>
                      <option value="028" {{ $user->bank == '028' ? 'selected' : '' }}>BANK OCBC NISP</option>
                      <option value="087" {{ $user->bank == '087' ? 'selected' : '' }}>BANK HSBC INDONESIA</option>
                      <option value="147" {{ $user->bank == '147' ? 'selected' : '' }}>BANK MUAMALAT</option>
                      <option value="153" {{ $user->bank == '153' ? 'selected' : '' }}>BANK SINARMAS</option>
                      <option value="426" {{ $user->bank == '426' ? 'selected' : '' }}>BANK MEGA</option>
                      <option value="441" {{ $user->bank == '441' ? 'selected' : '' }}>BANK BUKOPIN</option>
                      <option value="451" {{ $user->bank == '451' ? 'selected' : '' }}>BSI</option>
                      <option value="484" {{ $user->bank == '484' ? 'selected' : '' }}>BANK KEB HANA INDONESIA</option>
                      <option value="494" {{ $user->bank == '494' ? 'selected' : '' }}>BANK RAYA INDONESIA</option>
                      <option value="506" {{ $user->bank == '506' ? 'selected' : '' }}>BANK MEGA SYARIAH</option>
                      <option value="046" {{ $user->bank == '046' ? 'selected' : '' }}>BANK DBS INDONESIA</option>
                      <option value="947" {{ $user->bank == '947' ? 'selected' : '' }}>BANK ALADIN SYARIAH</option>
                      <option value="950" {{ $user->bank == '950' ? 'selected' : '' }}>BANK COMMONWEALTH</option>
                      <option value="213" {{ $user->bank == '213' ? 'selected' : '' }}>BANK BTPN</option>
                      <option value="490" {{ $user->bank == '490' ? 'selected' : '' }}>BANK NEO COMMERCE</option>
                      <option value="501" {{ $user->bank == '501' ? 'selected' : '' }}>BANK DIGITAL BCA</option>
                      <option value="521" {{ $user->bank == '521' ? 'selected' : '' }}>BANK BUKOPIN SYARIAH </option>
                      <option value="535" {{ $user->bank == '535' ? 'selected' : '' }}>SEABANK INDONESIA</option>
                      <option value="542" {{ $user->bank == '542' ? 'selected' : '' }}>BANK JAGO</option>
                      <option value="567" {{ $user->bank == '567' ? 'selected' : '' }}>ALLO BANK</option>
                      <option value="110" {{ $user->bank == '110' ? 'selected' : '' }}>BPD JAWA BARAT</option>
                      <option value="111" {{ $user->bank == '111' ? 'selected' : '' }}>BPD DKI</option>
                      <option value="112" {{ $user->bank == '112' ? 'selected' : '' }}>BPD DAERAH ISTIMEWA YOGYAKARTA</option>
                      <option value="113" {{ $user->bank == '113' ? 'selected' : '' }}>BPD JAWA TENGAH</option>
                      <option value="114" {{ $user->bank == '114' ? 'selected' : '' }}>BPD JAWA TIMUR</option>
                      <option value="115" {{ $user->bank == '115' ? 'selected' : '' }}>BPD JAMBI</option>
                      <option value="116" {{ $user->bank == '116' ? 'selected' : '' }}>BANK ACEH SYARIAH</option>
                      <option value="117" {{ $user->bank == '117' ? 'selected' : '' }}>BPD SUMATERA UTARA</option>
                      <option value="118" {{ $user->bank == '118' ? 'selected' : '' }}>BANK NAGARI</option>
                      <option value="119" {{ $user->bank == '119' ? 'selected' : '' }}>BPD RIAU KEPRI SYARIAH</option>
                      <option value="120" {{ $user->bank == '120' ? 'selected' : '' }}>BPD SUMATERA SELATAN DAN BANGKA BELITUNG</option>
                      <option value="121" {{ $user->bank == '121' ? 'selected' : '' }}>BPD LAMPUNG</option>
                      <option value="122" {{ $user->bank == '122' ? 'selected' : '' }}>BPD KALIMANTAN SELATAN</option>
                      <option value="123" {{ $user->bank == '123' ? 'selected' : '' }}>BPD KALIMANTAN BARAT</option>
                      <option value="124" {{ $user->bank == '124' ? 'selected' : '' }}>BPD KALIMANTAN TIMUR DAN KALIMANTAN UTARA</option>
                      <option value="125" {{ $user->bank == '125' ? 'selected' : '' }}>BPD KALIMANTAN TENGAH</option>
                      <option value="126" {{ $user->bank == '126' ? 'selected' : '' }}>BPD SULAWESI SELATAN DAN SULAWESI BARAT</option>
                      <option value="127" {{ $user->bank == '127' ? 'selected' : '' }}>BPD SULAWESI UTARA DAN GORONTALO</option>
                      <option value="128" {{ $user->bank == '128' ? 'selected' : '' }}>BANK NTB SYARIAH</option>
                      <option value="129" {{ $user->bank == '129' ? 'selected' : '' }}>BPD BALI</option>
                      <option value="130" {{ $user->bank == '130' ? 'selected' : '' }}>BPD NUSA TENGGARA TIMUR</option>
                      <option value="131" {{ $user->bank == '131' ? 'selected' : '' }}>BPD MALUKU DAN MALUKU UTARA</option>
                      <option value="132" {{ $user->bank == '132' ? 'selected' : '' }}>BPD PAPUA</option>
                      <option value="133" {{ $user->bank == '133' ? 'selected' : '' }}>BPD BENGKULU</option>
                      <option value="134" {{ $user->bank == '134' ? 'selected' : '' }}>BPD SULAWESI TENGAH</option>
                      <option value="135" {{ $user->bank == '135' ? 'selected' : '' }}>BPD SULAWESI TENGGARA</option>
                      <option value="137" {{ $user->bank == '137' ? 'selected' : '' }}>BPD BANTEN</option>
                    </select>
                    @error('bank')
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
                    <label>FOTO PROFIL</label>
                    <div class="input-group">
                      <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" capture="camera">
                    </div>
                    <!-- <i class="fas fa-info mt-2" style="color: red"></i> Upload Gambar atau Gunakan Kamera -->
                    @error('gambar')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <div class="thumbnail-circle" style="width: 12rem;">
                      @if (Auth::user()->gambar == null)
                      <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail rounded-circle" style="width: 100px; height:100px;">
                      @else
                      <img id="image-preview" class="img-thumbnail rounded-circle" src="{{ asset('images/' .  Auth::user()->gambar) }}" alt="Preview Image" style="width: 100px; height:100px;">
                      @endif
                    </div>
                  </div>
                </div>
              </div>

              <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> UPDATE</button>
              <button class="btn btn-warning btn-reset" type="reset"><i class="fa fa-redo"></i> RESET</button>

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

<!-- waktu untuk menampilkan alerts -->
<!--<script>
  document.addEventListener('DOMContentLoaded', function() {
    var successAlert = document.getElementById('successAlert');
    if (successAlert) {
      setTimeout(function() {
        successAlert.style.display = 'none';
      }, 3000);
    }
  });
</script>-->
<!-- end -->

<script>
  if ($(".datetimepicker").length) {
    $('.datetimepicker').daterangepicker({
      locale: {
        format: 'YYYY-MM-DD hh:mm'
      },
      singleDatePicker: true,
      timePicker: true,
      timePicker24Hour: true,
    });
  }

  var cleaveC = new Cleave('.currency', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var timeoutHandler = null;

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