@extends('layouts.account')

@section('title')
Detail Gaji Karyawan | MANAGEMENT
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>GAJI KARYAWAN</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-file-invoice-dollar"></i> DETAIL GAJI KARYAWAN</h4>
        </div>

        <!--@if(session('status') === 'error')
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <b>{{ session('message') }}</b>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif-->

        <div class="card-body">

          <form action="{{ route('account.gaji.store') }}" method="POST" enctype="multipart/form-data">
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
                  <!--<input type="text" class="form-control" id="bank" disabled>-->
                  <select class="form-control bank" name="bank" id="bank" disabled="true">
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

            <!-- lembur field default -->
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
            <!-- end lembur field default -->

            <!-- lembur field 1 -->
            @if($gaji->lembur1 == null || $gaji->jumlah_lembur1 == null || $gaji->lembur1 == '0' || $gaji->jumlah_lembur1 == '0')
            @else
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur1" value="{{ number_format($gaji->lembur1, 0, ',', ',') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur1" value="{{ $gaji->jumlah_lembur1 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end lembur field 1 -->

            <!-- lembur field 2 -->
            @if($gaji->lembur2 == null || $gaji->jumlah_lembur2 == null || $gaji->lembur2 == '0' || $gaji->jumlah_lembur2 == '0')
            @else
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur2" value="{{ number_format($gaji->lembur2, 0, ',', ',') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur2" value="{{ $gaji->jumlah_lembur2 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end lembur field 2 -->

            <!-- lembur field 3 -->
            @if($gaji->lembur3 == null || $gaji->jumlah_lembur3 == null || $gaji->lembur3 == '0' || $gaji->jumlah_lembur3 == '0')
            @else
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur3" value="{{ number_format($gaji->lembur3, 0, ',', ',') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur3" value="{{ $gaji->jumlah_lembur3 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end lembur field 3 -->

            <!-- lembur field 4 -->
            @if($gaji->lembur4 == null || $gaji->jumlah_lembur4 == null || $gaji->lembur4 == '0' || $gaji->jumlah_lembur4 == '0')
            @else
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur4" value="{{ number_format($gaji->lembur4, 0, ',', ',') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur4" value="{{ $gaji->jumlah_lembur4 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end lembur field 4 -->

            <!-- lembur field 5 -->
            @if($gaji->lembur5 == null || $gaji->jumlah_lembur5 == null || $gaji->lembur5 == '0' || $gaji->jumlah_lembur5 == '0')
            @else
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur5" value="{{ number_format($gaji->lembur5, 0, ',', ',') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur5" value="{{ $gaji->jumlah_lembur5 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end lembur field 5 -->

            <!-- lembur field 6 -->
            @if($gaji->lembur6 == null || $gaji->jumlah_lembur6 == null || $gaji->lembur6 == '0' || $gaji->jumlah_lembur6 == '0')
            @else
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur6" value="{{ number_format($gaji->lembur6, 0, ',', ',') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur6" value="{{ $gaji->jumlah_lembur6 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end lembur field 6 -->

            <!-- lembur field 7 -->
            @if($gaji->lembur7 == null || $gaji->jumlah_lembur7 == null || $gaji->lembur7 == '0' || $gaji->jumlah_lembur7 == '0')
            @else
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur7" value="{{ number_format($gaji->lembur7, 0, ',', ',') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur7" value="{{ $gaji->jumlah_lembur7 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end lembur field 7 -->

            <!-- lembur field 8 -->
            @if($gaji->lembur8 == null || $gaji->jumlah_lembur8 == null || $gaji->lembur8 == '0' || $gaji->jumlah_lembur8 == '0')
            @else
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur8" value="{{ number_format($gaji->lembur8, 0, ',', ',') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur8" value="{{ $gaji->jumlah_lembur8 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end lembur field 8 -->

            <!-- lembur field 9 -->
            @if($gaji->lembur9 == null || $gaji->jumlah_lembur9 == null || $gaji->lembur9 == '0' || $gaji->jumlah_lembur9 == '0')
            @else
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur9" value="{{ number_format($gaji->lembur9, 0, ',', ',') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur9" value="{{ $gaji->jumlah_lembur9 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end lembur field 9 -->

            <!-- lembur field 10 -->
            @if($gaji->lembur10 == null || $gaji->jumlah_lembur10 == null || $gaji->lembur10 == '0' || $gaji->jumlah_lembur10 == '0')
            @else
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>BAYARAN LEMBUR (Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur10" value="{{ number_format($gaji->lembur10, 0, ',', ',') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>TOTAL JAM LEMBUR (Jam)</label>
                  <input type="text" name="jumlah_lembur10" value="{{ $gaji->jumlah_lembur10 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end lembur field 10 -->

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

            <!-- bonus field default -->
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
            <!-- end filed bonus default -->

            <!-- filed bonus 1 -->
            @if($gaji->bonus1 == null || $gaji->jumlah_bonus1 == null || $gaji->bonus_luar1 == null || $gaji->jumlah_bonus_luar1 == null || $gaji->bonus1 == '0' || $gaji->jumlah_bonus1 == '0' || $gaji->bonus_luar1 == '0' || $gaji->jumlah_bonus_luar1 == '0')
            @else
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus1" value="{{ number_format($gaji->bonus1, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus1" value="{{ $gaji->jumlah_bonus1 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>


              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar1" value="{{ number_format($gaji->bonus_luar1, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar1" value="{{ $gaji->jumlah_bonus_luar1 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end field bonus 1 -->

            <!-- filed bonus 2 -->
            @if($gaji->bonus2 == null || $gaji->jumlah_bonus2 == null || $gaji->bonus_luar2 == null || $gaji->jumlah_bonus_luar2 == null || $gaji->bonus2 == '0' || $gaji->jumlah_bonus2 == '0' || $gaji->bonus_luar2 == '0' || $gaji->jumlah_bonus_luar2 == '0')
            @else
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus2" value="{{ number_format($gaji->bonus2, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus2" value="{{ $gaji->jumlah_bonus2 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>


              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar2" value="{{ number_format($gaji->bonus_luar2, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar2" value="{{ $gaji->jumlah_bonus_luar2 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end field bonus 2 -->

            <!-- filed bonus 3 -->
            @if($gaji->bonus3 == null || $gaji->jumlah_bonus3 == null || $gaji->bonus_luar3 == null || $gaji->jumlah_bonus_luar3 == null || $gaji->bonus3 == '0' || $gaji->jumlah_bonus3 == '0' || $gaji->bonus_luar3 == '0' || $gaji->jumlah_bonus_luar3 == '0')
            @else
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus3" value="{{ number_format($gaji->bonus3, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus3" value="{{ $gaji->jumlah_bonus3 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>


              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar3" value="{{ number_format($gaji->bonus_luar3, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar3" value="{{ $gaji->jumlah_bonus_luar3 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end field bonus 3 -->

            <!-- filed bonus 4 -->
            @if($gaji->bonus4 == null || $gaji->jumlah_bonus4 == null || $gaji->bonus_luar4 == null || $gaji->jumlah_bonus_luar4 == null || $gaji->bonus4 == '0' || $gaji->jumlah_bonus4 == '0' || $gaji->bonus_luar4 == '0' || $gaji->jumlah_bonus_luar4 == '0')
            @else
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus4" value="{{ number_format($gaji->bonus4, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus4" value="{{ $gaji->jumlah_bonus4 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>


              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar4" value="{{ number_format($gaji->bonus_luar4, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar4" value="{{ $gaji->jumlah_bonus_luar4 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end field bonus 4 -->

            <!-- filed bonus 5 -->
            @if($gaji->bonus5 == null || $gaji->jumlah_bonus5 == null || $gaji->bonus_luar5 == null || $gaji->jumlah_bonus_luar5 == null || $gaji->bonus5 == '0' || $gaji->jumlah_bonus5 == '0' || $gaji->bonus_luar5 == '0' || $gaji->jumlah_bonus_luar5 == '0')
            @else
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus5" value="{{ number_format($gaji->bonus5, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus5" value="{{ $gaji->jumlah_bonus5 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>


              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar5" value="{{ number_format($gaji->bonus_luar5, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar5" value="{{ $gaji->jumlah_bonus_luar5 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end field bonus 5 -->

            <!-- filed bonus 6 -->
            @if($gaji->bonus6 == null || $gaji->jumlah_bonus6 == null || $gaji->bonus_luar6 == null || $gaji->jumlah_bonus_luar6 == null || $gaji->bonus6 == '0' || $gaji->jumlah_bonus6 == '0' || $gaji->bonus_luar6 == '0' || $gaji->jumlah_bonus_luar6 == '0')
            @else
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus6" value="{{ number_format($gaji->bonus6, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus6" value="{{ $gaji->jumlah_bonus6 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>


              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar6" value="{{ number_format($gaji->bonus_luar6, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar6" value="{{ $gaji->jumlah_bonus_luar6 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end field bonus 6 -->

            <!-- filed bonus 7 -->
            @if($gaji->bonus7 == null || $gaji->jumlah_bonus7 == null || $gaji->bonus_luar7 == null || $gaji->jumlah_bonus_luar7 == null || $gaji->bonus7 == '0' || $gaji->jumlah_bonus7 == '0' || $gaji->bonus_luar7 == '0' || $gaji->jumlah_bonus_luar7 == '0')
            @else
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus7" value="{{ number_format($gaji->bonus7, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus7" value="{{ $gaji->jumlah_bonus7 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>


              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar7" value="{{ number_format($gaji->bonus_luar7, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar7" value="{{ $gaji->jumlah_bonus_luar7 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end field bonus 7 -->

            <!-- filed bonus 8 -->
            @if($gaji->bonus8 == null || $gaji->jumlah_bonus8 == null || $gaji->bonus_luar8 == null || $gaji->jumlah_bonus_luar8 == null || $gaji->bonus8 == '0' || $gaji->jumlah_bonus8 == '0' || $gaji->bonus_luar8 == '0' || $gaji->jumlah_bonus_luar8 == '0')
            @else
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus8" value="{{ number_format($gaji->bonus8, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus8" value="{{ $gaji->jumlah_bonus8 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>


              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar8" value="{{ number_format($gaji->bonus_luar8, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar8" value="{{ $gaji->jumlah_bonus_luar8 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end field bonus 8 -->

            <!-- filed bonus 9 -->
            @if($gaji->bonus9 == null || $gaji->jumlah_bonus9 == null || $gaji->bonus_luar9 == null || $gaji->jumlah_bonus_luar9 == null || $gaji->bonus9 == '0' || $gaji->jumlah_bonus9 == '0' || $gaji->bonus_luar9 == '0' || $gaji->jumlah_bonus_luar9 == '0')
            @else
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus9" value="{{ number_format($gaji->bonus9, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus9" value="{{ $gaji->jumlah_bonus9 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>


              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar9" value="{{ number_format($gaji->bonus_luar9, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar9" value="{{ $gaji->jumlah_bonus_luar9 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end field bonus 9 -->

            <!-- filed bonus 10 -->
            @if($gaji->bonus10 == null || $gaji->jumlah_bonus10 == null || $gaji->bonus_luar10 == null || $gaji->jumlah_bonus_luar10 == null || $gaji->bonus10 == '0' || $gaji->jumlah_bonus10 == '0' || $gaji->bonus_luar10 == '0' || $gaji->jumlah_bonus_luar10 == '0')
            @else
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS DALAM KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus10" value="{{ number_format($gaji->bonus10, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Dalam Kota)</label>
                  <input type="text" name="jumlah_bonus10" value="{{ $gaji->jumlah_bonus10 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>


              <div class="col-md-3">
                <div class="form-group">
                  <label>BAYARAN BONUS LUAR KOTA</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus_luar10" value="{{ number_format($gaji->bonus_luar10, 0, ',', ',') }}" placeholder="Masukkan Bayaran Bonus Per Hari" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>JUMLAH HARI (Luar Kota)</label>
                  <input type="text" name="jumlah_bonus_luar10" value="{{ $gaji->jumlah_bonus_luar10 }}" placeholder="Masukkan Total Hari" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end field bonus 10 -->

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

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>STATUS PEMBAYARAN</label>
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
                  <label>CATATAN</label>
                  <div class="input-group">
                    <textarea name="note" id="note" placeholder="Masukkan catatan" class="form-control" style="width: 100%;" readonly> {{ $gaji->note }}</textarea>
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
                    <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" capture="camera" disabled>
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
                  <a href="{{ asset('storage/assets/img/presensi/' . $gaji->gambar) }}" data-lightbox="{{ $gaji->id }}">
                    <div class="card" style="width: 18rem;">
                      @if ($gaji->gambar == null)
                      <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                      @else
                      <img id="image-preview" class="card-img-top" src="{{ asset('storage/assets/img/presensi/' . $gaji->gambar) }}" alt="Preview Image">
                      @endif
                    </div>
                  </a>
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

            <a href="{{ route('account.gaji.index') }}" class="btn btn-info mr-1">
              <i class="fa fa-list"></i> LIST GAJI KARYAWAN
            </a>

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