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

          <form action="{{ route('account.gaji.store') }}" method="GET" enctype="multipart/form-data">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nama Karyawan</label>
                  <select class="form-control select2" name="user_id" id="karyawanSelect" style="width: 100%" disabled="true">
                    <option value="">-- PILIH NAMA KARYAWAN --</option>
                    @foreach ($datas as $user)
                    <option value="{{ $user->id }}" data-nik="{{ $user->nik }}" data-norek="{{ $user->norek }}" data-bank="{{ $user->bank }}" data-telp="{{ $user->telp }}" data-alpha="{{ $user->alpha }}" data-hadir="{{ $user->hadir }}" data-camp_jogja="{{ $user->camp_jogja }}" data-camp_luar_kota="{{ $user->camp_luar_kota }}" data-perjalanan_jawa="{{ $user->perjalanan_jawa }}" data-perjalanan_luar_jawa="{{ $user->perjalanan_luar_jawa }}" data-remote="{{ $user->remote }}" data-izin="{{ $user->izin }}" {{ $user->id == $gaji->user_id ? 'selected' : '' }}>{{ $user->full_name }}</option>
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
                  <label>No Telp</label>
                  <input type="text" class="form-control" id="telp" disabled>
                </div>
              </div>
            </div>
        </div>
      </div>

      <div class="section-body">
        <div class="card">
          <div class="card-body">

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Gaji Pokok</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" style="border-color: red;">Rp.</span>
                    </div>
                    <input type="text" name="gaji_pokok" style="border-color: red;" value="{{ number_format($gaji->gaji_pokok, 0, ',', ',') }}" placeholder="Masukkan Gaji Pokok Karyawan" class="form-control" readonly>
                  </div>
                </div>
              </div>
            </div>

            <!-- lembur field default -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="lembur" value="{{ number_format($gaji->lembur, 0, ',', ',') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Total Jam Lembur</label>
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
                  <label>Bonus Lembur (Per Jam)</label>
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
                  <label>Total Jam Lembur</label>
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
                  <label>Bonus Lembur (Per Jam)</label>
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
                  <label>Total Jam Lembur</label>
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
                  <label>Bonus Lembur (Per Jam)</label>
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
                  <label>Total Jam Lembur</label>
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
                  <label>Bonus Lembur (Per Jam)</label>
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
                  <label>Total Jam Lembur</label>
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
                  <label>Bonus Lembur (Per Jam)</label>
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
                  <label>Total Jam Lembur</label>
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
                  <label>Bonus Lembur (Per Jam)</label>
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
                  <label>Total Jam Lembur</label>
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
                  <label>Bonus Lembur (Per Jam)</label>
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
                  <label>Total Jam Lembur</label>
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
                  <label>Bonus Lembur (Per Jam)</label>
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
                  <label>Total Jam Lembur</label>
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
                  <label>Bonus Lembur (Per Jam)</label>
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
                  <label>Total Jam Lembur</label>
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
                  <label>Bonus Lembur (Per Jam)</label>
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
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur10" value="{{ $gaji->jumlah_lembur10 }}" placeholder="Masukkan Total Jam" class="form-control" readonly>
                </div>
              </div>
            </div>
            @endif
            <!-- end lembur field 10 -->

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Total Lembur</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" style="border-color: red;">Rp.</span>
                    </div>
                    <input type="text" name="total_lembur" style="border-color: red;" value="{{ number_format($gaji->total_lembur, 0, ',', ',') }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control" readonly>
                  </div>
                </div>
              </div>
            </div>

            <!--================== BONUS DARI PRESENSI ==================-->
            <div class="row">
              <!-- ALPHA -->
              <!-- <div class="col-md-3">
                <div class="form-group">
                  <label>Alpha</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus5" value="{{ number_format($gaji->bonus5, 0, ',', ',') }}" placeholder="Tanpa Kehadiran" class="form-control currency5" readonly>
                  </div>
                </div>
              </div> -->

              <div class="col-md-6">
                <div class="form-group">
                  <label>Total Alpha</label>
                  <input type="text" id="alpha" name="jumlah_bonus5" placeholder="Total Tanpa Kehadiran" class="form-control" readonly>
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
                    <input type="text" name="bonus" value="{{ number_format($gaji->bonus, 0, ',', ',') }}" placeholder="Bonus Kehadiran" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Total Kehadiran</label>
                  <input type="text" id="hadir" name="jumlah_bonus" placeholder="Total Kehadiran" class="form-control" readonly>
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
                    <input type="text" name="bonus1" value="{{ number_format($gaji->bonus1, 0, ',', ',') }}" placeholder="Bonus Camp Jogja" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Total Camp Jogja</label>
                  <input type="text" id="camp_jogja" name="jumlah_bonus1" placeholder="Total Camp Jogja" class="form-control" readonly>
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
                    <input type="text" name="bonus4" value="{{ number_format($gaji->bonus4, 0, ',', ',') }}" placeholder="Bonus Camp Luar Kota" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Total Camp Luar Kota</label>
                  <input type="text" id="camp_luar_kota" name="jumlah_bonus4" placeholder="Total Camp Luar Kota" class="form-control" readonly>
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
                    <input type="text" name="bonus2" value="{{ number_format($gaji->bonus2, 0, ',', ',') }}" placeholder="Bonus Perjalanan Dalam Jawa" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Total Perjalanan Dalam Jawa</label>
                  <input type="text" id="perjalanan_jawa" name="jumlah_bonus2" placeholder="Total Perjalanan Dalam Jawa" class="form-control" readonly>
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
                    <input type="text" name="bonus3" value="{{ number_format($gaji->bonus3, 0, ',', ',') }}" placeholder="Bonus Perjalanan Luar Jawa" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Total Perjalanan Dalam Jawa</label>
                  <input type="text" id="perjalanan_luar_jawa" name="jumlah_bonus3" placeholder="Total Perjalanan Luar Jawa" class="form-control" readonly>
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
                    <input type="text" name="bonus6" value="{{ number_format($gaji->bonus6, 0, ',', ',') }}" placeholder="Bonus Remote" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Total Remote</label>
                  <input type="text" id="remote" name="jumlah_bonus6" placeholder="Total Remote" class="form-control" readonly>
                </div>
              </div>
              <!-- END -->

              <!-- IZIN -->
              <div class="col-md-3">
                <div class="form-group">
                  <label>Bonus Izin</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="bonus7" value="{{ number_format($gaji->bonus7, 0, ',', ',') }}" placeholder="Bonus Izin" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Total Izin</label>
                  <input type="text" id="izin" name="jumlah_bonus7" placeholder="Total Izin" class="form-control" readonly>
                </div>
              </div>
              <!-- END -->
            </div>
            <!--================== end ==================-->

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Total Bonus</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" style="border-color: red;">Rp.</span>
                    </div>
                    <input type="text" name="total_bonus" style="border-color: red;" value="{{ number_format($gaji->total_bonus, 0, ',', ',') }}" placeholder="Masukkan Gaji Pokok Karyawan" class="form-control " readonly>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Tunjangan BPJS</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="tunjangan_bpjs" id="tunjangan_bpjs" value="{{ number_format($gaji->tunjangan_bpjs, 0, ',', ',') }}" placeholder="Masukkan Total Tunjangan BPJS" class="form-control" readonly>
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
                    <input type="text" name="tunjangan_thr" id="tunjangan_thr" value="{{ number_format($gaji->tunjangan_thr, 0, ',', ',') }}" placeholder="Masukkan Total Tunjangan THR" class="form-control" readonly>
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
                    <input type="text" name="tunjangan_pulsa" id="tunjangan_pulsa" value="{{ number_format($gaji->tunjangan_pulsa, 0, ',', ',') }}" placeholder="Masukkan Total Tunjangan Pulsa" class="form-control" readonly>
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
                    <input type="text" name="tunjangan" id="tunjangan" value="{{ number_format($gaji->tunjangan, 0, ',', ',') }}" placeholder="Masukkan Total Tunjangan" class="form-control" readonly>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Potongan</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">Rp.</span>
                    </div>
                    <input type="text" name="potongan" id="potongan" value="{{ number_format($gaji->potongan, 0, ',', ',') }}" placeholder="Masukkan Total Potongan" class="form-control" readonly>
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
                    <input type="text" name="pph" id="pph" value="{{ number_format($gaji->pph, 0, ',', ',') }}" placeholder="Masukkan Total PPH 21" class="form-control" readonly>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>Tanggal Dibayarkan</label>
                  <input type="text" name="tanggal" id="tanggal" value="{{ date('d-m-Y H:i', strtotime($gaji->tanggal)) }}" placeholder="Masukkan Total Tunjangan" class="form-control" readonly>
                </div>
              </div>
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

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label style="font-weight: bold;">Total Gaji</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" style="border-color: black; font-weight: bold;">Rp.</span>
                    </div>
                    <input type="text" name="total" style="border-color: black; font-weight: bold;" id="total" value="{{ number_format($gaji->total, 0, ',', ',') }}" placeholder="Masukkan Total Potongan" class="form-control" readonly>
                  </div>
                </div>
              </div>
            </div>

            <a href="{{ route('account.gaji.index') }}" class="btn btn-info mr-1">
              <i class="fa fa-list"></i> KEMBALI
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
        $('#telp').val(telp);
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
        $('#telp').val('');
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