@extends('layouts.account')

@section('title')
Detail Uang Masuk - UANGKU
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>PENGGUNA</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-money-check-alt"></i> DETAIL PENGGUNA</h4>
        </div>

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
              <div class="col-2">
                <div class="form-group">
                  <input type="checkbox" value="1" name="email_verified_at" style="margin-top: 5px;" {{ $user->email_verified_at ? 'checked' : '' }} disabled="true">
                  <label>Verifikasi</label>
                </div>
              </div>
              <div class="col-2">
                <div class="form-group">
                  <input type="checkbox" value="1" name="status" style="margin-top: 5px;" {{ $user->status == 'on' ? 'checked' : '' }} disabled="true">
                  <label>Status</label>
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
<script>
  function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function() {
      var output = document.getElementById('imagePreview');
      output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
  }
</script>
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