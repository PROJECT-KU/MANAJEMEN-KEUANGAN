@extends('layouts.account')

@section('title')
Detail Gaji Karyawan | MIS
@stop

<!--================== button lembur responsive ==================-->
<style>
  /* Default styling for the button */
  #addLembur,
  #removeAddedLembur0,
  #removeAddedLembur2,
  #removeAddedLembur3,
  #removeAddedLembur4,
  #removeAddedLembur5,
  #removeAddedLembur6,
  #removeAddedLembur7,
  #removeAddedLembur8,
  #removeAddedLembur9,
  #removeAddedLembur10 {
    height: 40px;
    white-space: nowrap;
  }

  /* Media query for handphones (width 767px or less) */
  @media (max-width: 767px) {

    #addLembur,
    #removeAddedLembur0,
    #removeAddedLembur2,
    #removeAddedLembur3,
    #removeAddedLembur4,
    #removeAddedLembur5,
    #removeAddedLembur6,
    #removeAddedLembur7,
    #removeAddedLembur8,
    #removeAddedLembur9,
    #removeAddedLembur10 {
      width: 100%;
    }
  }

  /* Media query for tablets (width between 768px and 991px) */
  @media (min-width: 768px) and (max-width: 991px) {

    #addLembur,
    #removeAddedLembur0,
    #removeAddedLembur2,
    #removeAddedLembur3,
    #removeAddedLembur4,
    #removeAddedLembur5,
    #removeAddedLembur6,
    #removeAddedLembur7,
    #removeAddedLembur8,
    #removeAddedLembur9,
    #removeAddedLembur10 {
      width: auto;
      /* atau atur sesuai kebutuhan pada tablet */
    }
  }

  /* Styling for larger screens (laptops, monitors) */
  @media (min-width: 992px) {

    #addLembur,
    #removeAddedLembur0,
    #removeAddedLembur2,
    #removeAddedLembur3,
    #removeAddedLembur4,
    #removeAddedLembur5,
    #removeAddedLembur6,
    #removeAddedLembur7,
    #removeAddedLembur8,
    #removeAddedLembur9,
    #removeAddedLembur10 {
      width: auto;
      /* Atur sesuai kebutuhan pada laptop atau monitor */
    }
  }
</style>
<!--================== end ==================-->

<!--================== button bonus responsive ==================-->
<style>
  /* Default styling for the button */
  #addBonus,
  #removeAddedBonus1,
  #removeAddedBonus2,
  #removeAddedBonus3,
  #removeAddedBonus4,
  #removeAddedBonus5,
  #removeAddedBonus6,
  #removeAddedBonus7,
  #removeAddedBonus8,
  #removeAddedBonus9,
  #removeAddedBonus10 {
    height: 40px;
    white-space: nowrap;
  }

  #label {
    margin-bottom: 20px;
  }

  /* Media query for handphones (width 767px or less) */
  @media (max-width: 767px) {

    #addBonus,
    #removeAddedBonus1,
    #removeAddedBonus2,
    #removeAddedBonus3,
    #removeAddedBonus4,
    #removeAddedBonus5,
    #removeAddedBonus6,
    #removeAddedBonus7,
    #removeAddedBonus8,
    #removeAddedBonus9,
    #removeAddedBonus10 {
      width: 100%;
    }
  }

  /* Media query for tablets (width between 768px and 991px) */
  @media (min-width: 768px) and (max-width: 991px) {

    #addBonus,
    #removeAddedBonus1,
    #removeAddedBonus2,
    #removeAddedBonus3,
    #removeAddedBonus4,
    #removeAddedBonus5,
    #removeAddedBonus6,
    #removeAddedBonus7,
    #removeAddedBonus8,
    #removeAddedBonus9,
    #removeAddedBonus10 {
      width: auto;
      /* atau atur sesuai kebutuhan pada tablet */
    }

    #label {
      margin-bottom: 35px;
    }
  }

  /* Styling for larger screens (laptops, monitors) */
  @media (min-width: 992px) {

    #addBonus,
    #removeAddedBonus1,
    #removeAddedBonus2,
    #removeAddedBonus3,
    #removeAddedBonus4,
    #removeAddedBonus5,
    #removeAddedBonus6,
    #removeAddedBonus7,
    #removeAddedBonus8,
    #removeAddedBonus9,
    #removeAddedBonus10 {
      width: auto;
      /* Atur sesuai kebutuhan pada laptop atau monitor */
    }
  }
</style>
<!--================== end ==================-->

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>DETAIL GAJI KARYAWAN</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <div class="card-header">
          <h4>DETAIL KARYAWAN</h4>
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

          <form action="" method="POST" enctype="multipart/form-data">
            @csrf

            <!--================== DETAIL KARYAWAN ==================-->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nama Karyawan</label>
                  <select class="form-control select2" name="user_id" id="karyawanSelect" style="width: 100%" disabled="true">
                    <option value="">-- PILIH NAMA KARYAWAN --</option>
                    @foreach ($datas as $user)
                    <option value="{{ $user->id }}" data-nik="{{ $user->nik }}" data-norek="{{ $user->norek }}" data-bank="{{ $user->bank }}" data-email="{{ $user->email }}" {{ $user->id == $gaji->user_id ? 'selected' : '' }}>{{ $user->full_name }}</option>
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
                  <label>Nomor Rekening</label>
                  <input type="text" class="form-control" id="norek" disabled>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Bank</label>
                  <select class="form-control bank" name="bank" id="bank" disabled="true">
                    <option value="" disabled selected></option>
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
              <div class="col-md-4">
                <div class="form-group">
                  <label>Email</label>
                  <input type="text" name="email" class="form-control" id="email" readonly>
                </div>
              </div>
            </div>
        </div>
      </div>
      <!--================== END ==================-->

      <!--================== GAJI POKOK ==================-->
      <div class="card">
        <div class="card-header">
          <h4>GAJI POKOK</h4>
        </div>
        <div class="card-body">
          <div class="row">
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Gaji Pokok</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_pokok" value="{{ $gaji->gaji_pokok }}" placeholder="Masukkan Gaji Pokok Karyawan" class="form-control currency" readonly>
                </div>
                @error('gaji_pokok')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
          </div>

        </div>
      </div>
      <!--================== END ==================-->

      <!--================== BONUS LEMBUR ==================-->
      <div class="card">
        <div class="card-header">
          <h4>BONUS LEMBUR</h4>
        </div>
        <div class="card-body">
          <div class="row">
          </div>

          <!-- lembur default -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Bonus Lembur (Per Jam)</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="lembur" value="{{ $gaji->lembur }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_default" readonly>
                </div>
                @error('lembur')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Total Jam Lembur</label>
                <input type="text" name="jumlah_lembur" value="{{ $gaji->jumlah_lembur }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
                @error('jumlah_lembur')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-info mt-2" id="addLembur" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-plus"></i> INPUT</button>
              </div> -->
          </div>
        </div>
        <!-- end lembur default -->

        <!-- lembur field 1 -->
        @if($gaji->lembur1 == null || $gaji->jumlah_lembur1 == null || $gaji->lembur1 == '0' || $gaji->jumlah_lembur1 == '0')
        <div class="row lembur-field0" style="display: none;">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur1" value="{{ $gaji->lembur1 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_1" readonly>
              </div>
              @error('lembur1')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur1" value="{{ $gaji->jumlah_lembur1 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur1')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur0" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @else
        <div class="row lembur-field0">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur1" value="{{ $gaji->lembur1 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_1" readonly>
              </div>
              @error('lembur1')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur1" value="{{ $gaji->jumlah_lembur1 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur1')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur0" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @endif
        <!-- end lembur field 1 -->

        <!-- lembur field 2 -->
        @if($gaji->lembur2 == null || $gaji->jumlah_lembur2 == null || $gaji->lembur2 == '0' || $gaji->jumlah_lembur2 == '0')
        <div class="row lembur-field2" style="display: none;">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur2" value="{{ $gaji->lembur2 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_2" readonly>
              </div>
              @error('lembur2')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur2" value="{{ $gaji->jumlah_lembur2 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur2')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur2" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @else
        <div class="row lembur-field2">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur2" value="{{ $gaji->lembur2 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_2" readonly>
              </div>
              @error('lembur2')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur2" value="{{ $gaji->jumlah_lembur2 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur2')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur2" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @endif
        <!-- end lembur field 2 -->

        <!-- lembur field 3 -->
        @if($gaji->lembur3 == null || $gaji->jumlah_lembur3 == null || $gaji->lembur3 == '0' || $gaji->jumlah_lembur3 == '0')
        <div class="row lembur-field3" style="display: none;">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur3" value="{{ $gaji->lembur3 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_3" readonly>
              </div>
              @error('lembur3')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur3" value="{{ $gaji->jumlah_lembur3 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur3')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur3" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @else
        <div class="row lembur-field3">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur3" value="{{ $gaji->lembur3 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_3" readonly>
              </div>
              @error('lembur3')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur3" value="{{ $gaji->jumlah_lembur3 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur3')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur3" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @endif
        <!-- end lembur field 3 -->

        <!-- lembur field 4 -->
        @if($gaji->lembur4 == null || $gaji->jumlah_lembur4 == null || $gaji->lembur4 == '0' || $gaji->jumlah_lembur4 == '0')
        <div class="row lembur-field4" style="display: none;">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur4" value="{{ $gaji->lembur4 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_4" readonly>
              </div>
              @error('lembur4')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur4" value="{{ $gaji->jumlah_lembur4 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur4')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur4" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @else
        <div class="row lembur-field4">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur4" value="{{ $gaji->lembur4 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_4" readonly>
              </div>
              @error('lembur4')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur4" value="{{ $gaji->jumlah_lembur4 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur4')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur4" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @endif
        <!-- end lembur field 4 -->

        <!-- lembur field 5 -->
        @if($gaji->lembur5 == null || $gaji->jumlah_lembur5 == null || $gaji->lembur5 == '0' || $gaji->jumlah_lembur5 == '0')
        <div class="row lembur-field5" style="display: none;">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur5" value="{{ $gaji->lembur5 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_5" readonly>
              </div>
              @error('lembur5')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur5" value="{{ $gaji->jumlah_lembur5 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur5')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur5" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @else
        <div class="row lembur-field5">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur5" value="{{ $gaji->lembur5 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_5" readonly>
              </div>
              @error('lembur5')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur5" value="{{ $gaji->jumlah_lembur5 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur5')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur5" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @endif
        <!-- end lembur field 5 -->

        <!-- lembur field 6 -->
        @if($gaji->lembur6 == null || $gaji->jumlah_lembur6 == null || $gaji->lembur6 == '0' || $gaji->jumlah_lembur6 == '0')
        <div class="row lembur-field6" style="display: none;">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur6" value="{{ $gaji->lembur6 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_6" readonly>
              </div>
              @error('lembur6')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur6" value="{{ $gaji->jumlah_lembur6 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur6')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur6" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @else
        <div class="row lembur-field6">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur6" value="{{ $gaji->lembur6 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_6" readonly>
              </div>
              @error('lembur6')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur6" value="{{ $gaji->jumlah_lembur6 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur6')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur6" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @endif
        <!-- end lembur field 6 -->

        <!-- lembur field 7 -->
        @if($gaji->lembur7 == null || $gaji->jumlah_lembur7 == null || $gaji->lembur7 == '0' || $gaji->jumlah_lembur7 == '0')
        <div class="row lembur-field7" style="display: none;">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur7" value="{{ $gaji->lembur7 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_7" readonly>
              </div>
              @error('lembur7')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur7" value="{{ $gaji->jumlah_lembur7 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur7')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur7" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @else
        <div class="row lembur-field7">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur7" value="{{ $gaji->lembur7 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_7" readonly>
              </div>
              @error('lembur7')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur7" value="{{ $gaji->jumlah_lembur7 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur7')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur7" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @endif
        <!-- end lembur field 7 -->

        <!-- lembur field 8 -->
        @if($gaji->lembur8 == null || $gaji->jumlah_lembur8 == null || $gaji->lembur8 == '0' || $gaji->jumlah_lembur8 == '0')
        <div class="row lembur-field8" style="display: none;">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur8" value="{{ $gaji->lembur8 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_8" readonly>
              </div>
              @error('lembur8')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur8" value="{{ $gaji->jumlah_lembur8 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur8')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur8" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @else
        <div class="row lembur-field8">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur8" value="{{ $gaji->lembur8 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_8" readonly>
              </div>
              @error('lembur8')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur8" value="{{ $gaji->jumlah_lembur8 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur8')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur8" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @endif
        <!-- end lembur field 8 -->

        <!-- lembur field 9 -->
        @if($gaji->lembur9 == null || $gaji->jumlah_lembur9 == null || $gaji->lembur9 == '0' || $gaji->jumlah_lembur9 == '0')
        <div class="row lembur-field9" style="display: none;">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur9" value="{{ $gaji->lembur9 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_9" readonly>
              </div>
              @error('lembur9')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur9" value="{{ $gaji->jumlah_lembur9 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur9')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur9" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @else
        <div class="row lembur-field9">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur9" value="{{ $gaji->lembur9 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_9" readonly>
              </div>
              @error('lembur9')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur9" value="{{ $gaji->jumlah_lembur9 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur9')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur9" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @endif
        <!-- end lembur field 9 -->

        <!-- lembur field 10 -->
        @if($gaji->lembur10 == null || $gaji->jumlah_lembur10 == null || $gaji->lembur10 == '0' || $gaji->jumlah_lembur10 == '0')
        <div class="row lembur-field10" style="display: none;">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur10" value="{{ $gaji->lembur10 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_10" readonly>
              </div>
              @error('lembur10')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur10" value="{{ $gaji->jumlah_lembur10 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur10')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur10" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @else
        <div class="row lembur-field10">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Lembur (Per Jam)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="lembur10" value="{{ $gaji->lembur10 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur_10" readonly>
              </div>
              @error('lembur10')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Total Jam Lembur</label>
              <input type="text" name="jumlah_lembur10" value="{{ $gaji->jumlah_lembur10 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
              @error('jumlah_lembur10')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <!-- <div class="col-md-1">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur10" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div> -->
        </div>
        @endif
        <!-- end lembur field 10 -->

      </div>
    </div>
    <!--================== END ==================-->

    <!--================== BONUS PRESENSI ==================-->
    <div class="card">
      <div class="card-header">
        <h4>BONUS PRESENSI</h4>
      </div>
      <div class="card-body">
        <div class="row">
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Total Alpha</label>
              <input type="text" name="jumlah_bonus5" value="{{ $gaji->jumlah_bonus5 ?? 0}}" placeholder="Total Tanpa Kehadiran" class="form-control" readonly>
            </div>
          </div>
          <!-- END -->

          <!-- BONUS KEHADIRAN -->
          <div class="col-md-3">
            <div class="form-group">
              <label>Bonus Kehadiran</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="bonus" value="{{ $gaji->bonus ?? 0}}" placeholder="Bonus Kehadiran" class="form-control currency_kehadiran" readonly>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
              <label>Total Kehadiran</label>
              <input type="text" name="jumlah_bonus" value="{{ $gaji->jumlah_bonus ?? 0}}" placeholder="Total Kehadiran" class="form-control" readonly>
            </div>
          </div>
          <!-- END -->
        </div>

        <div class="row">
          <!-- BONUS CAMP JOGJA -->
          <div class="col-md-3">
            <div class="form-group">
              <label>Bonus Camp Jogja</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="bonus1" value="{{ $gaji->bonus1 ?? 0}}" placeholder="Bonus Camp Jogja" class="form-control currency_camp_jogja" readonly>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
              <label>Total Camp Jogja</label>
              <input type="text" name="jumlah_bonus1" value="{{ $gaji->jumlah_bonus1 ?? 0}}" placeholder="Total Camp Jogja" class="form-control" readonly>
            </div>
          </div>
          <!-- END -->

          <!-- BONUS CAMP LUAR KOTA-->
          <div class="col-md-3">
            <div class="form-group">
              <label>Bonus Camp Luar Kota</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="bonus4" value="{{ $gaji->bonus4 ?? 0}}" placeholder="Bonus Camp Luar Kota" class="form-control currency_camp_luar_kota" readonly>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
              <label>Total Camp Luar Kota</label>
              <input type="text" value="{{ $gaji->jumlah_bonus4 ?? 0}}" placeholder="Total Camp Luar Kota" class="form-control" readonly>
            </div>
          </div>
          <!-- END -->
        </div>

        <div class="row">
          <!-- PERJALANAN JAWA -->
          <div class="col-md-3">
            <div class="form-group">
              <label>Bonus Perjalanan Dalam Jawa</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="bonus2" value="{{ $gaji->bonus2 ?? 0}}" placeholder="Bonus Perjalanan Dalam Jawa" class="form-control currency_perjalanan_jawa" readonly>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
              <label>Total Perjalanan Dalam Jawa</label>
              <input type="text" name="jumlah_bonus2" value="{{ $gaji->jumlah_bonus2 ?? 0}}" placeholder="Total Perjalanan Dalam Jawa" class="form-control" readonly>
            </div>
          </div>
          <!-- END -->

          <!-- PERJALANAN LUAR JAWA -->
          <div class="col-md-3">
            <div class="form-group">
              <label>Bonus Perjalanan Luar Jawa</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="bonus3" value="{{ $gaji->bonus3 ?? 0}}" placeholder="Bonus Perjalanan Luar Jawa" class="form-control currency_perjalanan_luar_jawa" readonly>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
              <label>Total Perjalanan Luar Jawa</label>
              <input type="text" name="jumlah_bonus3" value="{{ $gaji->jumlah_bonus3 ?? 0}}" placeholder="Total Perjalanan Luar Jawa" class="form-control" readonly>
            </div>
          </div>
          <!-- END -->
        </div>

        <div class="row">
          <!-- REMOTE -->
          <div class="col-md-3">
            <div class="form-group">
              <label>Bonus Remote</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="bonus6" value="{{ $gaji->bonus6 ?? 0 }}" placeholder="Bonus Remote" class="form-control currency_remote" readonly>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
              <label>Total Remote</label>
              <input type="text" name="jumlah_bonus6" value="{{ $gaji->jumlah_bonus6 ?? 0}}" placeholder="Total Remote" class="form-control" readonly>
            </div>
          </div>
          <!-- END -->

          <!-- IZIN -->
          <div class="col-md-6">
            <div class="form-group">
              <label>Jumlah Izin</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="bonus7" value="{{ $gaji->jumlah_bonus7 ?? 0}}" placeholder="Bonus Izin" class="form-control currency_izin" readonly>
              </div>
            </div>
          </div>
          <!-- END -->
        </div>

      </div>
    </div>
    <!--================== END ==================-->

    <!--================== BONUS LAINNYA ==================-->
    <div class="card">
      <div class="card-header">
        <h4>BONUS LAINNYA</h4>
      </div>
      <div class="card-body">
        <div class="row">
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Webinar</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="webinar" id="webinar" value="{{ $gaji->webinar }}" placeholder="Masukkan Total Bonus Webinar" class="form-control currency_webinar" readonly>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Bonus Kinerja</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="kinerja" id="kinerja" value="{{ $gaji->kinerja }}" placeholder="Masukkan Total Bonus Kinerja" class="form-control currency_kinerja" readonly>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Tunjangan Kesehatan</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="tunjangan_bpjs" id="tunjangan_bpjs" value="{{ $gaji->tunjangan_bpjs }}" placeholder="Masukkan Total Tunjangan Kesehatan" class="form-control currency_tunjanganBPJS" readonly>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Tunjangan THR</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="tunjangan_thr" id="tunjangan_thr" value="{{ $gaji->tunjangan_thr }}" placeholder="Masukkan Total Tunjangan THR" class="form-control currency_tunjanganTHR" readonly>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Tunjangan Pulsa</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="tunjangan_pulsa" id="tunjangan_pulsa" value="{{ $gaji->tunjangan_pulsa }}" placeholder="Masukkan Total Tunjangan Pulsa" class="form-control currency_tunjanganPulsa" readonly>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Tunjangan Lainnya</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="tunjangan" id="tunjangan" value="{{ $gaji->tunjangan }}" placeholder="Masukkan Total Tunjangan Lainnya" class="form-control currency_tunjangan_lainnya" readonly>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
    <!--================== END ==================-->

    <!--================== POTONGAN ==================-->
    <div class="card">
      <div class="card-header">
        <h4>POTONGAN</h4>
      </div>
      <div class="card-body">
        <div class="row">
        </div>

        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Potongan</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="potongan" id="potongan" value="{{ $gaji->potongan }}" placeholder="Masukkan Total Potongan" class="form-control currency_potongan" readonly>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label>PPH 21</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Rp.</span>
                </div>
                <input type="text" name="pph" id="pph" value="{{ $gaji->pph }}" placeholder="Masukkan Total PPH 21" class="form-control currency_pph" readonly>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label>Tanggal Dibayarkan</label>
              <input type="datetime-local" name="tanggal" id="tanggal" value="{{ $gaji->tanggal }}" placeholder="Masukkan Total Tunjangan" class="form-control" readonly>
            </div>
          </div>
        </div>

      </div>
    </div>
    <!--================== END ==================-->

    <!--================== LAINNYA ==================-->
    <div class="card">
      <div class="card-header">
        <h4>LAINNYA</h4>
      </div>
      <div class="card-body">
        <div class="row">
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Status Pembayaran</label>
              <select class="form-control" name="status" disabled>
                <option value="" disabled selected>Silahkan Pilih</option>
                <option value="pending" {{ $gaji->status == 'pending' ? 'selected' : '' }}>PENDING</option>
                <option value="terbayar" {{ $gaji->status == 'terbayar' ? 'selected' : '' }}>TERBAYAR</option>
              </select>
              @error('status')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label>Catatan</label>
              <div class="input-group">
                <textarea name="note" id="note" placeholder="Masukkan catatan" class="form-control" style="width: 100%;" disabled>{{ $gaji->note }}</textarea>
              </div>
              @error('note')
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
              <label>Bukti Pembayaran</label>
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
              <a href="{{ asset('images/' . $gaji->gambar) }}" data-lightbox="{{ $gaji->id }}">
                <div class="card" style="width: 18rem;">
                  @if ($gaji->gambar == null)
                  <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                  @else
                  <img id="image-preview" style="width: 200px; height:200px;" class="card-img-top" src="{{ asset('images/' . $gaji->gambar) }}" alt="Preview Image">
                  @endif
                </div>
              </a>
            </div>
          </div>
        </div>

        <div class="col-md-12">
          <div class="form-group">
            <label>Total Gaji</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text" style="border-color: red;  font-weight: bold;">Rp.</span>
              </div>
              <input type="text" name="total" id="total" value="{{ number_format($gaji->total, 0, ',', ',') }}" placeholder="Masukkan Total Potongan" class="form-control currency6" style="border-color: red;  font-weight: bold;" readonly>
            </div>
          </div>
        </div>

      </div>
    </div>

    <div class="d-flex mt-3">
      <a href="{{ route('account.gaji.index') }}" class="btn btn-info" style="flex: 1; height:40px; font-size: 15px;">
        <i class="fa fa-undo"></i> KEMBALI
      </a>
    </div>

    </form>

</div>
</section>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!--================== maksimal upload gambar & jenis file yang di perbolehkan ==================-->
<script>
  document.getElementById('gambar').addEventListener('change', function() {
    const maxFileSizeInBytes = 5024 * 5024; // 5MB
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
          text: 'Ukuran File Yang Diperbolehkan Dibawah 5MB.',
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
<!--================== end ==================-->

<!--================== upload image ==================-->
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
<!--================== end ==================-->

<!--================== date picker ==================-->
<script>
  // if ($(".datetimepicker").length) {
  //   $('.datetimepicker').daterangepicker({
  //     locale: {
  //       format: 'DD-MM-YYYY hh:mm'
  //     },
  //     singleDatePicker: true,
  //     timePicker: true,
  //     timePicker24Hour: true,
  //   });
  // }

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
</script>
<!--================== end ==================-->

<!--================== format rupiah gaji pokok ==================-->
<script>
  var cleaveC = new Cleave('.currency', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var timeoutHandler = null;
</script>
<!--================== end ==================-->

<!--================== select option untuk nama karyawan ==================-->
<script>
  $(document).ready(function() {

    // Function to update the input fields based on selected karyawan
    function updateFields() {
      var selectedKaryawanOption = $('#karyawanSelect option:selected');

      if (selectedKaryawanOption.length) {
        var nik = selectedKaryawanOption.data('nik');
        var norek = selectedKaryawanOption.data('norek');
        var bank = selectedKaryawanOption.data('bank');
        var email = selectedKaryawanOption.data('email');
        var alpha = selectedKaryawanOption.data('alpha');
        var hadir = selectedKaryawanOption.data('hadir');
        var camp_jogja = selectedKaryawanOption.data('camp_jogja');
        var camp_luar_kota = selectedKaryawanOption.data('camp_luar_kota');
        var perjalanan_jawa = selectedKaryawanOption.data('perjalanan_jawa');
        var perjalanan_luar_jawa = selectedKaryawanOption.data('perjalanan_luar_jawa');
        var remote = selectedKaryawanOption.data('remote');
        var izin = selectedKaryawanOption.data('izin');

        $('#nik').val(nik);
        $('#norek').val(norek);
        $('#bank').val(bank);
        $('#email').val(email);
        $('#alpha').val(alpha);
        $('#hadir').val(hadir);
        $('#camp_jogja').val(camp_jogja);
        $('#camp_luar_kota').val(camp_luar_kota);
        $('#perjalanan_jawa').val(perjalanan_jawa);
        $('#perjalanan_luar_jawa').val(perjalanan_luar_jawa);
        $('#remote').val(remote);
        $('#izin').val(izin);
      } else {
        $('#nik').val('');
        $('#norek').val('');
        $('#bank').val('');
        $('#email').val('');
        $('#alpha').val('');
        $('#hadir').val('');
        $('#camp_jogja').val('');
        $('#camp_luar_kota').val('');
        $('#perjalanan_jawa').val('');
        $('#perjalanan_luar_jawa').val('');
        $('#remote').val('');
        $('#izin').val('');
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
<!--================== end ==================-->

<!--================== add & remove field lembur ==================-->
<script>
  $(document).ready(function() {
    var lemburCounter = 0;

    $('#addLembur').on('click', function() {
      if (lemburCounter === 0) {
        $('.lembur-field0').show();
        $('#removeAddedLembur0').show();
        $('#removeAddedLembur1').show();
        $('#removeAddedLembur2').show();
        $('#removeAddedLembur3').show();
        $('#removeAddedLembur4').show();
        $('#removeAddedLembur5').show();
        $('#removeAddedLembur6').show();
        $('#removeAddedLembur7').show();
        $('#removeAddedLembur8').show();
        $('#removeAddedLembur9').show();
        $('#removeAddedLembur10').show();
      } else if (lemburCounter === 1) {
        $('.lembur-field2').show();
        $('#addLembur').show();
        $('#removeAddedLembur2').show();
      } else if (lemburCounter === 2) {
        $('.lembur-field3').show();
        $('#addLembur').show();
        $('#removeAddedLembur3').show();
      } else if (lemburCounter === 3) {
        $('.lembur-field4').show();
        $('#addLembur').show();
        $('#removeAddedLembur4').show();
      } else if (lemburCounter === 4) {
        $('.lembur-field5').show();
        $('#addLembur').show();
        $('#removeAddedLembur5').show();
      } else if (lemburCounter === 5) {
        $('.lembur-field6').show();
        $('#addLembur').show();
        $('#removeAddedLembur6').show();
      } else if (lemburCounter === 6) {
        $('.lembur-field7').show();
        $('#addLembur').show();
        $('#removeAddedLembur7').show();
      } else if (lemburCounter === 7) {
        $('.lembur-field8').show();
        $('#addLembur').show();
        $('#removeAddedLembur8').show();
      } else if (lemburCounter === 8) {
        $('.lembur-field9').show();
        $('#addLembur').show();
        $('#removeAddedLembur9').show();
      } else if (lemburCounter === 9) {
        $('.lembur-field10').show();
        $('#addLembur').hide();
        $('#removeAddedLembur10').show();
      }
      lemburCounter++;
    });

    $('#removeAddedLembur0').on('click', function() {
      $('.lembur-field0').hide();
      $('#addLembur').show();
      lemburCounter--;
      $('.currency_lembur1').val('');
      $('[name="jumlah_lembur1"]').val('');
    });
    $('#removeAddedLembur2').on('click', function() {
      $('.lembur-field2').hide();
      $('#addLembur').show();
      lemburCounter--;
      $('.currency_lembur2').val('');
      $('[name="jumlah_lembur2"]').val('');
    });
    $('#removeAddedLembur3').on('click', function() {
      $('.lembur-field3').hide();
      $('#addLembur').show();
      lemburCounter--;
      $('.currency_lembur3').val('');
      $('[name="jumlah_lembur3"]').val('');
    });
    $('#removeAddedLembur4').on('click', function() {
      $('.lembur-field4').hide();
      $('#addLembur').show();
      lemburCounter--;
      $('.currency_lembur4').val('');
      $('[name="jumlah_lembur4"]').val('');
    });
    $('#removeAddedLembur5').on('click', function() {
      $('.lembur-field5').hide();
      $('#addLembur').show();
      lemburCounter--;
      $('.currency_lembur5').val('');
      $('[name="jumlah_lembur5"]').val('');
    });
    $('#removeAddedLembur6').on('click', function() {
      $('.lembur-field6').hide();
      $('#addLembur').show();
      lemburCounter--;
      $('.currency_lembur6').val('');
      $('[name="jumlah_lembur6"]').val('');
    });
    $('#removeAddedLembur7').on('click', function() {
      $('.lembur-field7').hide();
      $('#addLembur').show();
      lemburCounter--;
      $('.currency_lembur7').val('');
      $('[name="jumlah_lembur7"]').val('');
    });
    $('#removeAddedLembur8').on('click', function() {
      $('.lembur-field8').hide();
      $('#addLembur').show();
      lemburCounter--;
      $('.currency_lembur8').val('');
      $('[name="jumlah_lembur8"]').val('');
    });
    $('#removeAddedLembur9').on('click', function() {
      $('.lembur-field9').hide();
      $('#addLembur').show();
      lemburCounter--;
      $('.currency_lembur9').val('');
      $('[name="jumlah_lembur9"]').val('');
    });
    $('#removeAddedLembur10').on('click', function() {
      $('.lembur-field10').hide();
      $('#addLembur').show();
      lemburCounter--;
      $('.currency_lembur10').val('');
      $('[name="jumlah_lembur10"]').val('');
    });
  });
</script>
<!--================== end ==================-->

<!--================== add & remove field bonus ==================-->
<script>
  $(document).ready(function() {

    var bonusCounter = 0;

    $('#addBonus').on('click', function() {
      if (bonusCounter === 0) {
        $('.bonus-field1').show();
        $('#removeAddedBonus1').show();
        $('#removeAddedBonus2').show();
        $('#removeAddedBonus3').show();
        $('#removeAddedBonus4').show();
        $('#removeAddedBonus5').show();
        $('#removeAddedBonus6').show();
        $('#removeAddedBonus7').show();
        $('#removeAddedBonus8').show();
        $('#removeAddedBonus9').show();
        $('#removeAddedBonus10').show();
      } else if (bonusCounter === 1) {
        $('.bonus-field2').show();
        $('#addBonus').show();
        $('#removeAddedBonus2').show();
      } else if (bonusCounter === 2) {
        $('.bonus-field3').show();
        $('#addBonus').show();
        $('#removeAddedBonus3').show();
      } else if (bonusCounter === 3) {
        $('.bonus-field4').show();
        $('#addBonus').show();
        $('#removeAddedBonus4').show();
      } else if (bonusCounter === 4) {
        $('.bonus-field5').show();
        $('#addBonus').show();
        $('#removeAddedBonus5').show();
      } else if (bonusCounter === 5) {
        $('.bonus-field6').show();
        $('#addBonus').show();
        $('#removeAddedBonus6').show();
      } else if (bonusCounter === 6) {
        $('.bonus-field7').show();
        $('#addBonus').show();
        $('#removeAddedBonus7').show();
      } else if (bonusCounter === 7) {
        $('.bonus-field8').show();
        $('#addBonus').show();
        $('#removeAddedBonus8').show();
      } else if (bonusCounter === 8) {
        $('.bonus-field9').show();
        $('#addBonus').show();
        $('#removeAddedBonus9').show();
      } else if (bonusCounter === 9) {
        $('.bonus-field10').show();
        $('#addBonus').hide();
        $('#removeAddedBonus10').show();
      }
      bonusCounter++;
    });

    // Remove additional bonus fields
    $('#removeAddedBonus1').on('click', function() {
      $('.bonus-field1').hide();
      $('#addBonus').show();
      bonusCounter--;
      $('.currency_bonus1').val('');
      $('.currency_bonus_luar1').val('');
      $('[name="jumlah_bonus1"]').val('');
      $('[name="jumlah_bonus_luar1"]').val('');
    });
    $('#removeAddedBonus2').on('click', function() {
      $('.bonus-field2').hide();
      $('#addBonus').show();
      bonusCounter--;
      $('.currency_bonus2').val('');
      $('.currency_bonus_luar2').val('');
      $('[name="jumlah_bonus2"]').val('');
      $('[name="jumlah_bonus_luar2"]').val('');
    });
    $('#removeAddedBonus3').on('click', function() {
      $('.bonus-field3').hide();
      $('#addBonus').show();
      bonusCounter--;
      $('.currency_bonus3').val('');
      $('.currency_bonus_luar3').val('');
      $('[name="jumlah_bonus3"]').val('');
      $('[name="jumlah_bonus_luar3"]').val('');
    });
    $('#removeAddedBonus4').on('click', function() {
      $('.bonus-field4').hide();
      $('#addBonus').show();
      bonusCounter--;
      $('.currency_bonus4').val('');
      $('.currency_bonus_luar4').val('');
      $('[name="jumlah_bonus4"]').val('');
      $('[name="jumlah_bonus_luar4"]').val('');
    });
    $('#removeAddedBonus5').on('click', function() {
      $('.bonus-field5').hide();
      $('#addBonus').show();
      bonusCounter--;
      $('.currency_bonus5').val('');
      $('.currency_bonus_luar5').val('');
      $('[name="jumlah_bonus5"]').val('');
      $('[name="jumlah_bonus_luar5"]').val('');
    });
    $('#removeAddedBonus6').on('click', function() {
      $('.bonus-field6').hide();
      $('#addBonus').show();
      bonusCounter--;
      $('.currency_bonus6').val('');
      $('.currency_bonus_luar6').val('');
      $('[name="jumlah_bonus6"]').val('');
      $('[name="jumlah_bonus_luar6"]').val('');
    });
    $('#removeAddedBonus7').on('click', function() {
      $('.bonus-field7').hide();
      $('#addBonus').show();
      bonusCounter--;
      $('.currency_bonus7').val('');
      $('.currency_bonus_luar7').val('');
      $('[name="jumlah_bonus7"]').val('');
      $('[name="jumlah_bonus_luar7"]').val('');
    });
    $('#removeAddedBonus8').on('click', function() {
      $('.bonus-field8').hide();
      $('#addBonus').show();
      bonusCounter--;
      $('.currency_bonus8').val('');
      $('.currency_bonus_luar8').val('');
      $('[name="jumlah_bonus8"]').val('');
      $('[name="jumlah_bonus_luar8"]').val('');
    });
    $('#removeAddedBonus9').on('click', function() {
      $('.bonus-field9').hide();
      $('#addBonus').show();
      bonusCounter--;
      $('.currency_bonus9').val('');
      $('.currency_bonus_luar9').val('');
      $('[name="jumlah_bonus9"]').val('');
      $('[name="jumlah_bonus_luar9"]').val('');
    });
    $('#removeAddedBonus10').on('click', function() {
      $('.bonus-field10').hide();
      $('#addBonus').show();
      bonusCounter--;
      $('.currency_bonus10').val('');
      $('.currency_bonus_luar10').val('');
      $('[name="jumlah_bonus10"]').val('');
      $('[name="jumlah_bonus_luar10"]').val('');
    });
  });
</script>
<!--================== end ==================-->

<!--================== FORMAT RUPIAH ==================-->
<script>
  var cleaveC = new Cleave('.currency', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  // <!-- FORMAT RUPIAH LEMBUR -->
  var cleaveC = new Cleave('.currency_lembur_default', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur_1', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur_2', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur_3', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur_4', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur_5', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur_6', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur_7', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur_8', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur_9', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur_10', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- FORMAT RUPIAH BONUS DARI PRESENSI -->
  var cleaveC = new Cleave('.currency_kehadiran', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_camp_jogja', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_camp_luar_kota', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_perjalanan_jawa', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_perjalanan_luar_jawa', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_remote', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_izin', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  var cleaveC = new Cleave('.currency_webinar', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_kinerja', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_tunjanganBPJS', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_tunjanganTHR', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_tunjanganPulsa', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_tunjangan_lainnya', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_potongan', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_pph', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var timeoutHandler = null;
</script>
<!--================== END ==================-->

<!--================== botton loader ==================-->
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