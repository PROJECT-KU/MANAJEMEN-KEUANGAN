@extends('layouts.account')

@section('title')
Tambah Kategori Uang Masuk - UANGKU
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>TAMBAH GAJI KARYAWAN</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-dice-d6"></i> TAMBAH GAJI KARYAWAN</h4>
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

          <form action="{{ route('account.gaji.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>NAMA KARYAWAN</label>
                  <select class="form-control select2" name="user_id" id="karyawanSelect" style="width: 100%" required>
                    <option value="">-- PILIH NAMA KARYAWAN --</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}" data-nik="{{ $user->nik }}" data-norek="{{ $user->norek }}" data-bank="{{ $user->bank }}" data-telp="{{ $user->telp }}">{{ $user->full_name }}</option>
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
                  <!--<input type="text" class="form-control" id="bank" disabled>-->
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
                  <label>NO TELP</label>
                  <input type="text" class="form-control" id="telp" disabled>
                </div>
              </div>
            </div>

            <div class="row">
            </div>

            <div class="col-md-12">
              <div class="form-group">
                <label>GAJI POKOK</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_pokok" value="{{ old('gaji_pokok') }}" placeholder="Masukkan Gaji Pokok Karyawan" class="form-control currency" required>
                </div>
                @error('gaji_pokok')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <!-- lembur default -->
            <div class="row">
              <div class="col-md-5">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur" value="{{ old('lembur') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency1">
                  </div>
                  @error('lembur')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur" value="{{ old('jumlah_lembur') }}" placeholder="Masukkan Total Jam" class="form-control">
                  @error('jumlah_lembur')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-info mt-2" id="addLembur"><i class="fas fa-plus"></i> INPUT</button>
                </div>
              </div>
            </div>
            <!-- end lembur default -->

            <!-- lembur field 1 -->
            <div class="row lembur-field0" style="display: none;">
              <div class="col-md-5">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur1" value="{{ old('lembur1') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur1">
                  </div>
                  @error('lembur1')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur1" value="{{ old('jumlah_lembur1') }}" placeholder="Masukkan Total Jam" class="form-control">
                  @error('jumlah_lembur1')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur0"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end lembur field 1 -->

            <!-- lembur field 2 -->
            <div class="row lembur-field2" style="display: none;">
              <div class="col-md-5">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur2" value="{{ old('lembur2') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur2" autofocus>
                  </div>
                  @error('lembur2')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur2" value="{{ old('jumlah_lembur2') }}" placeholder="Masukkan Total Jam" class="form-control" autofocus>
                  @error('jumlah_lembur2')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur2"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end lembur field 2 -->

            <!-- lembur field 3 -->
            <div class="row lembur-field3" style="display: none;">
              <div class="col-md-5">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur3" value="{{ old('lembur3') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur3" autofocus>
                  </div>
                  @error('lembur3')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur3" value="{{ old('jumlah_lembur3') }}" placeholder="Masukkan Total Jam" class="form-control" autofocus>
                  @error('jumlah_lembur3')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur3"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end lembur field 3 -->

            <!-- lembur field 4 -->
            <div class="row lembur-field4" style="display: none;">
              <div class="col-md-5">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur4" value="{{ old('lembur4') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur4" autofocus>
                  </div>
                  @error('lembur4')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur4" value="{{ old('jumlah_lembur4') }}" placeholder="Masukkan Total Jam" class="form-control" autofocus>
                  @error('jumlah_lembur4')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur4"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end lembur field 4 -->

            <!-- lembur field 5 -->
            <div class="row lembur-field5" style="display: none;">
              <div class="col-md-5">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur5" value="{{ old('lembur5') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur5" autofocus>
                  </div>
                  @error('lembur5')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur5" value="{{ old('jumlah_lembur5') }}" placeholder="Masukkan Total Jam" class="form-control" autofocus>
                  @error('jumlah_lembur5')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur5"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end lembur field 5 -->

            <!-- lembur field 6 -->
            <div class="row lembur-field6" style="display: none;">
              <div class="col-md-5">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur6" value="{{ old('lembur6') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur6" autofocus>
                  </div>
                  @error('lembur6')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur6" value="{{ old('jumlah_lembur6') }}" placeholder="Masukkan Total Jam" class="form-control" autofocus>
                  @error('jumlah_lembur6')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur6"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end lembur field 6 -->

            <!-- lembur field 7 -->
            <div class="row lembur-field7" style="display: none;">
              <div class="col-md-5">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur7" value="{{ old('lembur7') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur7" autofocus>
                  </div>
                  @error('lembur7')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur7" value="{{ old('jumlah_lembur7') }}" placeholder="Masukkan Total Jam" class="form-control" autofocus>
                  @error('jumlah_lembur7')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur7"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end lembur field 7 -->

            <!-- lembur field 8 -->
            <div class="row lembur-field8" style="display: none;">
              <div class="col-md-5">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur8" value="{{ old('lembur8') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur8" autofocus>
                  </div>
                  @error('lembur8')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur8" value="{{ old('jumlah_lembur8') }}" placeholder="Masukkan Total Jam" class="form-control" autofocus>
                  @error('jumlah_lembur8')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur8"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end lembur field 8 -->

            <!-- lembur field 9 -->
            <div class="row lembur-field9" style="display: none;">
              <div class="col-md-5">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur9" value="{{ old('lembur9') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur9" autofocus>
                  </div>
                  @error('lembur9')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur9" value="{{ old('jumlah_lembur9') }}" placeholder="Masukkan Total Jam" class="form-control" autofocus>
                  @error('jumlah_lembur9')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur9"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end lembur field 9 -->

            <!-- lembur field 10 -->
            <div class="row lembur-field10" style="display: none;">
              <div class="col-md-5">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur10" value="{{ old('lembur10') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control currency_lembur10" autofocus>
                  </div>
                  @error('lembur10')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur10" value="{{ old('jumlah_lembur10') }}" placeholder="Masukkan Total Jam" class="form-control" autofocus>
                  @error('jumlah_lembur10')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedLembur10"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end lembur field 10 -->

            @if (Auth::user()->company === 'rumahscopus')

            <!-- bonus default -->
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus" value="{{ old('bonus') }}" placeholder="Bonus Per Hari" class="form-control currency2">
                  </div>
                  @error('bonus')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus" value="{{ old('jumlah_bonus') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar" value="{{ old('bonus_luar') }}" placeholder="Bonus Per Hari" class="form-control currency5">
                  </div>
                  @error('bonus_luar')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar" value="{{ old('jumlah_bonus_luar') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus_luar')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-info mt-2" id="addBonus"><i class="fas fa-plus"></i> INPUT</button>
                </div>
              </div>
            </div>
            <!-- end bonus default -->

            <!-- bonus field 1 -->
            <div class="row bonus-field1" style="display: none;">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus1" value="{{ old('bonus1') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus1">
                  </div>
                  @error('bonus1')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus1" value="{{ old('jumlah_bonus1') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus1')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar1" value="{{ old('bonus_luar1') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus_luar1">
                  </div>
                  @error('bonus_luar1')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar1" value="{{ old('jumlah_bonus_luar1') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus_luar1')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedBonus1"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end bonus field 1 -->

            <!-- bonus field 2 -->
            <div class="row bonus-field2" style="display: none;">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus2" value="{{ old('bonus2') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus2">
                  </div>
                  @error('bonus2')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus2" value="{{ old('jumlah_bonus2') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus2')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar2" value="{{ old('bonus_luar2') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus_luar2">
                  </div>
                  @error('bonus_luar2')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar2" value="{{ old('jumlah_bonus_luar2') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus_luar2')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedBonus2"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end bonus field 2 -->

            <!-- bonus field 3 -->
            <div class="row bonus-field3" style="display: none;">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus3" value="{{ old('bonus3') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus3">
                  </div>
                  @error('bonus3')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus3" value="{{ old('jumlah_bonus3') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus3')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar3" value="{{ old('bonus_luar3') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus_luar3">
                  </div>
                  @error('bonus_luar3')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar3" value="{{ old('jumlah_bonus_luar3') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus_luar3')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedBonus3"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end bonus field 3 -->

            <!-- bonus field 4 -->
            <div class="row bonus-field4" style="display: none;">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus4" value="{{ old('bonus4') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus4">
                  </div>
                  @error('bonus4')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus4" value="{{ old('jumlah_bonus4') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus4')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar4" value="{{ old('bonus_luar4') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus_luar4">
                  </div>
                  @error('bonus_luar4')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar4" value="{{ old('jumlah_bonus_luar4') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus_luar4')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedBonus4"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end bonus field 4 -->

            <!-- bonus field 5 -->
            <div class="row bonus-field5" style="display: none;">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus5" value="{{ old('bonus5') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus5">
                  </div>
                  @error('bonus5')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus5" value="{{ old('jumlah_bonus5') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus5')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar5" value="{{ old('bonus_luar5') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus_luar5">
                  </div>
                  @error('bonus_luar5')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar5" value="{{ old('jumlah_bonus_luar5') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus_luar5')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedBonus5"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end bonus field 5 -->

            <!-- bonus field 6 -->
            <div class="row bonus-field6" style="display: none;">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus6" value="{{ old('bonus6') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus6">
                  </div>
                  @error('bonus6')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus6" value="{{ old('jumlah_bonus6') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus6')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar6" value="{{ old('bonus_luar6') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus_luar6">
                  </div>
                  @error('bonus_luar6')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar6" value="{{ old('jumlah_bonus_luar6') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus_luar6')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedBonus6"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end bonus field 6 -->

            <!-- bonus field 7 -->
            <div class="row bonus-field7" style="display: none;">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus7" value="{{ old('bonus7') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus7">
                  </div>
                  @error('bonus7')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus7" value="{{ old('jumlah_bonus7') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus7')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar7" value="{{ old('bonus_luar7') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus_luar7">
                  </div>
                  @error('bonus_luar7')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar7" value="{{ old('jumlah_bonus_luar7') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus_luar7')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedBonus7"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end bonus field 7 -->

            <!-- bonus field 8 -->
            <div class="row bonus-field8" style="display: none;">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus8" value="{{ old('bonus8') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus8">
                  </div>
                  @error('bonus8')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus8" value="{{ old('jumlah_bonus8') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus8')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar8" value="{{ old('bonus_luar8') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus_luar8">
                  </div>
                  @error('bonus_luar8')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar8" value="{{ old('jumlah_bonus_luar8') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus_luar8')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedBonus8"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end bonus field 8 -->

            <!-- bonus field 9 -->
            <div class="row bonus-field9" style="display: none;">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus9" value="{{ old('bonus9') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus9">
                  </div>
                  @error('bonus9')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus9" value="{{ old('jumlah_bonus9') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus9')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar9" value="{{ old('bonus_luar9') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus_luar9">
                  </div>
                  @error('bonus_luar9')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar9" value="{{ old('jumlah_bonus_luar9') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus_luar9')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedBonus9"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end bonus field 9 -->

            <!-- bonus field 10 -->
            <div class="row bonus-field10" style="display: none;">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus10" value="{{ old('bonus10') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus10">
                  </div>
                  @error('bonus10')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus10" value="{{ old('jumlah_bonus10') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus10')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA (Per Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar10" value="{{ old('bonus_luar10') }}" placeholder="Bonus Per Hari" class="form-control currency_bonus_luar10">
                  </div>
                  @error('bonus_luar10')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar10" value="{{ old('jumlah_bonus_luar10') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah_bonus_luar10')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-group">
                  <label></label>
                  <button type="button" class="btn btn-danger mt-2" id="removeAddedBonus10"><i class="fas fa-times"></i> HAPUS</button>
                </div>
              </div>
            </div>
            <!-- end bonus field 10 -->


            @else

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>BAYARAN BONUS (Hari)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus" value="{{ old('bonus') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control currency2">
                  </div>
                  @error('bonus')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>TOTAL BONUS (Hari)</label>
                  <input type="text" name="jumlah_bonus" value="{{ old('jumlah_bonus') }}" placeholder="Masukkan Total Hari" class="form-control">
                  @error('jumlah')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
            </div>
            @endif

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>TUNJANGAN</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="tunjangan" id="tunjangan" value="{{ old('tunjangan') }}" placeholder="Masukkan Total Tunjangan" class="form-control currency3">
                  </div>
                  @error('tunjangan')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>POTONGAN</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="potongan" id="potongan" value="{{ old('potongan') }}" placeholder="Masukkan Total Potongan" class="form-control currency4">
                  </div>
                  @error('potongan')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>TANGGAL DIBAYARKAN</label>
                  <input type="text" name="tanggal" id="tanggal" value="{{ old('tanggal') }}" placeholder="Masukkan Total Tunjangan" class="form-control datetimepicker" required>
                </div>
                @error('tanggal')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>STATUS PEMBAYARAN</label>
                  <select class="form-control" name="status" required>
                    <option value="" disabled selected>Silahkan Pilih</option>
                    <option value="pending">PENDING</option>
                    <option value="terbauar">TERBAYAR</option>
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
                  <label>CATATAN</label>
                  <div class="input-group">
                    <textarea name="note" id="note" placeholder="Masukkan catatan" class="form-control" style="width: 100%;"></textarea>
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
                  <label>BUKTI PEMBAYARAN</label>
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
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <div class="card" style="width: 18rem;">
                    <img id="image-preview" class="card-img-top" src="#" alt="Preview Image">
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

<!-- add dan remove field lembur -->
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

    // Remove additional lembur2 fields
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
<!-- end add dan remove field lembur -->

<!-- add dan remove field bonus -->
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
<!-- end add dan remove field bonus -->

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

  //format rupiah lembur
  var cleaveC = new Cleave('.currency_lembur1', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur2', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur3', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur4', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur5', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur6', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur7', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur8', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur9', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_lembur10', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  //end formar rupiah lembur

  //format rupiah bonus
  var cleaveC = new Cleave('.currency_bonus1', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus_luar1', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus2', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus_luar2', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus3', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus_luar3', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus4', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus_luar4', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus5', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus_luar5', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus6', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus_luar6', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus7', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus_luar7', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus8', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus_luar8', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus9', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus_luar9', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus10', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_bonus_luar10', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  //end format rupiah bonus
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