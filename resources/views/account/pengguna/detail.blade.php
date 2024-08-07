@extends('layouts.account')

@section('title')
Detail Pengguna | MIS
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>DETAIL PENGGUNA</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <!-- <div class="card-header">
          <h4><i class="fas fa-users-cog"></i> DETAIL PENGGUNA</h4>
        </div> -->

        <div class="card-body">

          <form action="{{ route('account.pengguna.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nama</label>
                  <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $user->full_name) }}" readonly>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Email</label>
                  <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" readonly>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nama Perusahaan</label>
                  <input type="text" name="company" class="form-control" value="{{ old('company', $user->company) }}" readonly>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>No Telp</label>
                  <input type="text" name="telp" class="form-control" value="{{ old('telp', $user->telp) }}" readonly>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Level</label>
                  <select class="form-control" name="level" disabled="true">
                    <option value="admin" {{ $user->level == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ $user->level == 'user' ? 'selected' : '' }}>User</option>
                    <option value="ceo" {{ $user->level == 'ceo' ? 'selected' : '' }}>CEO</option>
                    <option value="manager" {{ $user->level == 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="staff" {{ $user->level == 'staff' ? 'selected' : '' }}>Staff</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Jenis</label>
                  <select class="form-control" name="jenis" disabled="true">
                    <option value="bisnis" {{ $user->jenis == 'bisnis' ? 'selected' : '' }}>Bisnis</option>
                    <option value="perorangan" {{ $user->jenis == 'perorangan' ? 'selected' : '' }}>Perorangan</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Username</label>
                  <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" readonly>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Tanggal Dibikin</label>
                  <?php
                  // Import Carbon at the top of the file if not already imported
                  use Carbon\Carbon;

                  // Convert the created_at value to a Carbon instance
                  $createdAt = Carbon::parse($user->created_at);
                  // Format the Carbon instance in "tanggal-bulan-tahun jam-menit-detik" format
                  $formattedDate = $createdAt->format('d-m-Y H:i:s');
                  ?>
                  <input type="text" name="username" class="form-control" value="{{ old('created_at', $formattedDate) }}" readonly>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>NIK</label>
                  <input type="text" name="nik" class="form-control" value="{{ old('nik', $user->nik) }}" placeholder="Masukan NIK" maxlength="30" minlength="5" onkeypress="return event.charCode >= 48 && event.charCode <=57" readonly>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Nomor Rekening</label>
                  <input type="text" name="norek" class="form-control" value="{{ old('norek', $user->norek) }}" placeholder="Masukan Nomor Rekening" maxlength="30" minlength="5" onkeypress="return event.charCode >= 48 && event.charCode <=57" readonly>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Bank</label>
                  <select class="form-control bank" name="bank" disabled="true">
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
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Foto Profil</label>
                  <div class="input-group">
                    <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" capture="camera" disabled>
                  </div>
                  @error('gambar')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <a href="{{ asset('images/' . $user->gambar) }}" data-lightbox="{{ $user->id }}">
                    <div class="thumbnail-circle" style="width: 12rem;">
                      @if ($user->gambar == null)
                      <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail rounded-circle" style="width: 100px; height:100px;">
                      @else
                      <img id="image-preview" class="img-thumbnail rounded-circle" src="{{ asset('images/' .  $user->gambar) }}" alt="Preview Image" style="width: 100px; height:100px;">
                      @endif
                    </div>
                  </a>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Quotes</label>
                  <div class="input-group">
                    <textarea name="jobdesk" id="jobdesk" value="" placeholder="Masukkan Quotes" class="form-control" style="width: 100%;" readonly>{{ old('jobdesk', $user->jobdesk) }}</textarea>
                  </div>
                  @error('jobdesk')
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
                  <label>Akun Dibikin Pada Tanggal</label>
                  <input class="form-control" name="notif" placeholder="" value="{{ strftime('%d %B %Y %H:%M', strtotime($user->created_at)) }}" readonly>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Lama Bekerja</label>
                  <?php
                  $now = now();
                  $diff = $user->created_at->diff($now);

                  $years = $diff->y;
                  $months = $diff->m;

                  $result = '';

                  if ($years > 0) {
                    $result .= $years . ($years > 1 ? ' tahun ' : ' tahun ');
                  }

                  if ($months > 0) {
                    $result .= $months . ($months > 1 ? ' bulan' : ' bulan');
                  } else {
                    $result .= '1 bulan';
                  }
                  ?>
                  <input class="form-control" name="lama_bekerja" placeholder="" value="{{ $result }}" readonly>
                </div>
              </div>
            </div>


            <div class="row">
              <div class="col-md-2 col-4">
                <div class="form-group">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="emailVerifiedSwitch" name="email_verified_at" {{ $user->email_verified_at ? 'checked' : '' }} disabled="true">
                    <label class="custom-control-label" for="emailVerifiedSwitch">Verifikasi</label>
                  </div>
                </div>
              </div>
              <div class="col-md-2 col-4">
                <div class="form-group">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="statusSwitch" name="status" {{ $user->status == 'on' ? 'checked' : '' }} disabled="true">
                    <label class="custom-control-label" for="statusSwitch">Status</label>
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex">
              <a href="{{ route('account.pengguna.index') }}" class="btn btn-info rounded-pill" style="width: 100%; font-size: 14px;">
                <i class="fa fa-undo"></i> KEMBALI
              </a>
            </div>

          </form>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Include CKEditor JS -->
<style>
  .ckeditor-container {
    width: 100%;
  }
</style>

<!-- <script src="//cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script>
  // Replace 'jobdesk' textarea with CKEditor
  CKEDITOR.replace('jobdesk', {
    width: '100%', // Set CKEditor width to 100%
    height: '300px' // You can adjust the height as needed
  });
</script> -->
<!-- end ckeditor -->

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

<script>
  //date with datepicker
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
  //end

  //update angka to rupiah
  var cleaveC = new Cleave('.currency', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var timeoutHandler = null;
  //end

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