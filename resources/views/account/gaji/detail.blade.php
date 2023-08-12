@extends('layouts.account')

@section('title')
Tambah Kategori Uang Masuk - UANGKU
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>DETAIL GAJI KARYAWAN</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-dice-d6"></i> DETAIL GAJI KARYAWAN</h4>
        </div>

        @if(session('status') === 'error')
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <b>{{ session('message') }}</b>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif

        <div class="card-body">

          <form action="{{ route('account.gaji.store') }}" method="POST">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>NAMA KARYAWAN</label>
                  <select class="form-control select2" name="user_id" id="karyawanSelect" style="width: 100%" disabled="true">
                    <option value="">-- PILIH NAMA KARYAWAN --</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}" data-nik="{{ $user->nik }}" data-norek="{{ $user->norek }}" data-bank="{{ $user->bank }}" data-telp="{{ $user->telp }}" {{ $user->id == $gaji->user_id ? 'selected' : '' }}>{{ $user->full_name }}</option>
                    @endforeach
                  </select>

                  @error('user_id')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>NIK</label>
                  <input type="text" class="form-control" id="nik" disabled>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>NOMOR REKENING</label>
                  <input type="text" class="form-control" id="norek" disabled>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>BANK</label>
                  <input type="text" class="form-control" id="bank" disabled>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>NO TELP</label>
                  <input type="text" class="form-control" id="telp" disabled>
                </div>
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-group">
                <label>GAJI POKOK</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_pokok" value="{{ $gaji->gaji_pokok }}" placeholder="Masukkan Gaji Pokok Karyawan" class="form-control currency" readonly>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur" value="{{ $gaji->lembur }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency1" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur" value="{{ $gaji->jumlah_lembur }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
                </div>
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-group">
                <label>TOTAL LEMBUR</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text" style="border-color: red;">Rp.</span>
                  </div>
                  <input type="text" name="total_lembur" style="border-color: red;" value="{{ $gaji->total_lembur }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency8" readonly>
                </div>
              </div>
            </div>

            @if (Auth::user()->company === 'rumahscopus')
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus" value="{{ $gaji->bonus }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control currency2" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus" value="{{ $gaji->jumlah_bonus }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>


              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar" value="{{ $gaji->bonus_luar }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control currency6" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar" value="{{ $gaji->jumlah_bonus_luar }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>
            </div>
            @else
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>BAYARAN BONUS (Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus" value="{{ $gaji->bonus }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control currency2" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>TOTAL BONUS (Hari)</label>
                  <input type="text" name="jumlah_bonus" value="{{ $gaji->jumlah_bonus }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif

            <div class="col-md-12">
              <div class="form-group">
                <label>TOTAL BONUS</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text" style="border-color: red;">Rp.</span>
                  </div>
                  <input type="text" name="total_bonus" style="border-color: red;" value="{{ $gaji->total_bonus }}" placeholder="Masukkan Gaji Pokok Karyawan" class="form-control currency7" readonly>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>TUNJANGAN</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="tunjangan" id="tunjangan" value="{{ $gaji->tunjangan }}" placeholder="Masukkan Total Tunjangan" class="form-control currency3" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>POTONGAN</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="potongan" id="potongan" value="{{ $gaji->potongan }}" placeholder="Masukkan Total Potongan" class="form-control currency4" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>TANGGAL DIBAYARKAN</label>
                  <input type="text" name="tanggal" id="tanggal" value="{{ date('d-m-Y H:i', strtotime($gaji->tanggal)) }}" placeholder="Masukkan Total Tunjangan" class="form-control" readonly>
                </div>
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-group">
                <label style="font-weight: bold;">TOTAL GAJI</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text" style="border-color: black; font-weight: bold;">Rp.</span>
                  </div>
                  <input type="text" name="total" style="border-color: black; font-weight: bold;" id="total" value="{{ $gaji->total }}" placeholder="Masukkan Total Potongan" class="form-control currency5" readonly>
                </div>
              </div>
            </div>

            <a href="{{ route('account.gaji.index') }}" class="btn btn-primary mr-1">
              <i class="fa fa-list"></i> LIST GAJI KARYAWAN
            </a>

          </form>

        </div>
      </div>
    </div>
  </section>
</div>



<script>
  if ($(".datetimepicker").length) {
    $('.datetimepicker').daterangepicker({
      locale: {
        format: 'DD-MM-YYYY hh:mm'
      },
      singleDatePicker: true,
      timePicker: true,
      timePicker24Hour: true,
    });
  }


  $(document).ready(function() {
    // ... (kode lainnya)

    // Function to update the input fields based on selected karyawan
    function updateFields() {
      var selectedKaryawanOption = $('#karyawanSelect option:selected');

      if (selectedKaryawanOption.length) {
        var nik = selectedKaryawanOption.data('nik');
        var norek = selectedKaryawanOption.data('norek');
        var bank = selectedKaryawanOption.data('bank');
        var telp = selectedKaryawanOption.data('telp');

        $('#nik').val(nik);
        $('#norek').val(norek);
        $('#bank').val(bank);
        $('#telp').val(telp);
      } else {
        $('#nik').val('');
        $('#norek').val('');
        $('#bank').val('');
        $('#telp').val('');
      }
    }

    // Call the function when the page loads to initialize the values
    updateFields();

    // Call the function whenever the user selects a karyawan
    $('#karyawanSelect').on('change', function() {
      updateFields();
    });
  });
</script>



<script>
  var cleaveC = new Cleave('.currency', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.currency1', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.currency2', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.currency3', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.currency4', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.currency5', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.currency6', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.currency7', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.currency8', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var timeoutHandler = null;
</script>

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