@extends('layouts.account')
@extends('layouts.inputfitur')

@section('title')
Update Gaji Karyawan | MIS
@stop

<!--================== LEMBUR RESPONSIVE ==================-->
<style>
  /* 🔹 Input Group Glossy (Untuk prefix Rp) */
  .input-group-glossy {
    display: flex;
    align-items: stretch;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.01);
    transition: all 0.3s ease;
  }

  .input-group-glossy:focus-within {
    border-color: #818cf8;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
  }

  .prefix-glossy {
    padding: 12px 15px;
    font-size: 13px;
    font-weight: 800;
    color: #64748b;
    background: #f8fafc;
    border-right: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .input-group-glossy input {
    border: none !important;
    box-shadow: none !important;
    border-radius: 0;
    width: 100%;
    padding: 12px 15px;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
  }

  /* 🔹 Tombol Clean & Glossy */
  .btn-action-glossy {
    height: 46px;
    /* Sejajar dengan tinggi input */
    border-radius: 12px;
    font-weight: 800;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: none;
    transition: all 0.3s ease;
    cursor: pointer;
    width: 100%;
    /* Otomatis full width di HP, mengikuti kolom di Laptop */
  }

  .btn-action-glossy:hover {
    transform: translateY(-2px);
    filter: brightness(1.1);
  }

  .btn-add-glossy {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
  }

  .btn-remove-glossy {
    background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(225, 29, 72, 0.2);
  }
</style>
<!--================== END ==================-->

<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<style>
  /* 🔹 Modern File Upload */
  .upload-btn-glossy {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    padding: 12px 24px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.25);
    transition: all 0.3s ease;
    margin-bottom: 0;
  }

  .upload-btn-glossy:hover {
    transform: translateY(-2px);
    color: white;
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.35);
  }

  .inputfile {
    width: 0.1px;
    height: 0.1px;
    opacity: 0;
    overflow: hidden;
    position: absolute;
    z-index: -1;
  }

  /* 🔹 Image Preview Container */
  .preview-container-glossy {
    border: 2px dashed #cbd5e1;
    background: #f8fafc;
    border-radius: 12px;
    padding: 10px;
    min-height: 130px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    transition: all 0.3s ease;
  }

  .preview-container-glossy:hover {
    border-color: #94a3b8;
  }

  .image-preview img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    margin-top: 10px;
  }

  #file-selected {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    margin-top: 8px;
    display: block;
  }

  /* 🔹 Main Action Buttons (Simpan & Kembali) */
  .btn-main-action {
    padding: 12px 30px;
    border-radius: 50px;
    /* pill shape */
    font-weight: 800;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    border: none;
    transition: all 0.3s ease;
  }

  .btn-main-action:hover {
    transform: translateY(-3px);
  }

  .btn-save-glossy {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: white;
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
  }

  .btn-back-glossy {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
  }
</style>
<!--================== END ==================-->

<!--================== GLOBAL STYLE ==================-->
<style>
  .card-neo {
    background: white;
    border-radius: var(--radius-xl);
    border: none;
    box-shadow: var(--shadow-soft);
    margin-bottom: 25px;
    overflow: hidden;
  }

  .card-header-neo {
    padding: 20px 25px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fcfcfd;
  }

  .card-header-neo i {
    font-size: 18px;
    color: var(--accent);
  }

  .card-header-neo span {
    font-weight: 800;
    color: var(--text-dark);
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 0.5px;
  }

  /* 🔹 Table Styling */
  .table-modern thead th {
    background: #f8fafc;
    color: #64748b;
    font-size: 10px;
    text-transform: uppercase;
    font-weight: 700;
    padding: 15px;
    border: none;
  }

  .table-modern tbody td {
    padding: 15px;
    vertical-align: middle;
    border-bottom: 1px solid #f8fafc;
    font-size: 13px;
  }

  /* 🔹 Buttons */
  .btn-modern {
    padding: 12px 28px;
    border-radius: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
    border: none;
  }

  .btn-primary-gradient {
    background: var(--accent-gradient);
    color: white;
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.2);
  }

  .btn-primary-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(99, 102, 241, 0.3);
    color: white;
  }

  .alert-modern {
    background: linear-gradient(to right, #1e293b 0%, #6366f1 100%);
    border-left: 4px solid var(--accent);
    border-radius: 12px;
    color: #1e40af;
    font-weight: 600;
  }
</style>
<!--================== END ==================-->

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header-modern">
      <div>
        <h1>Update Gaji Karyawan</h1>
        <p class="text-muted font-weight-bold mb-0">Manajemen rekapitulasi pembayaran gaji bulanan tim.</p>
      </div>
    </div>

    <div class="section-body">

      <form action="{{ route('account.gaji.update', $gaji->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!--================== DETAIL KARYAWAN ==================-->
        <div class="card-neo">
          <div class="card-header-neo">
            <i class="fas fa-user-tie"></i>
            <span>Detail Karyawan</span>
          </div>
          <div class="card-body p-4">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Nama Karyawan</label>
                  <select class="form-control-modern select2" name="user_id" id="karyawanSelect" style="height: auto;" disabled>
                    <option value="">-- PILIH NAMA KARYAWAN --</option>
                    @foreach ($datas as $user)
                    <option value="{{ $user->id }}" data-norek="{{ $user->norek }}" data-bank="{{ $user->bank }}" data-email="{{ $user->email }}" data-alpha="{{ $user->alpha }}" data-hadir="{{ $user->hadir }}" data-camp_jogja="{{ $user->camp_jogja }}" data-camp_luar_kota="{{ $user->camp_luar_kota }}" data-perjalanan_jawa="{{ $user->perjalanan_jawa }}" data-perjalanan_luar_jawa="{{ $user->perjalanan_luar_jawa }}" data-remote="{{ $user->remote }}" data-izin="{{ $user->izin }}" {{ $user->id == $gaji->user_id ? 'selected' : '' }}>{{ $user->full_name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Nomor Rekening</label>
                  <input type="text" class="form-control-modern" id="norek" readonly>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Bank</label>
                  <select class="form-control-modern bank select2" name="bank" id="bank" style="height: auto;" disabled>
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
                  <input type="text" name="email" class="form-control-modern" id="email" readonly>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--================== END DETAIL KARYAWAN ==================-->

        <!--================== GAJI POKOK ==================-->
        <div class="card-neo">
          <div class="card-header-neo">
            <i class="fas fa-calculator"></i>
            <span>Gaji Pokok</span>
          </div>
          <div class="card-body p-4">

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Gaji Pokok</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="gaji_pokok" value="{{ $gaji->gaji_pokok }}" placeholder="Masukkan Gaji Pokok Karyawan" class="form-control-modern currency">
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Bonus Ethes Digital</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="gaji_pokok_ethes_digital" value="{{ $gaji->gaji_pokok_ethes_digital }}" placeholder="Masukkan Gaji Pokok Karyawan Ethes Digital" class="form-control-modern currency_ethes">
                  </div>
                </div>
              </div>
            </div>

            <div class="row mt-4 pt-4 border-top">
              <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center p-3" style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); border-radius: 12px; border: 1px solid #a5b4fc; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.15);">
                  <h5 class="mb-0" style="color: #3730a3; font-weight: 800;"><i class="fas fa-money-bill-wave mr-2"></i> Total Gaji Pokok</h5>
                  <h4 class="mb-0" style="color: #312e81; font-weight: 900;">Rp {{ number_format($gaji->total_gaji_pokok, 0, ',', '.') }}</h4>
                </div>
              </div>
            </div>

          </div>
        </div>
        <!--================== END GAJI POKOK ==================-->

        <!--================== BONUS LEMBUR ==================-->
        <div class="card-neo">
          <div class="card-header-neo">
            <i class="fas fa-calculator"></i>
            <span>Bonus Lembur</span>
          </div>
          <div class="card-body p-4">

            <!-- DEFAULTS -->
            <div class="row align-items-end mb-4">
              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur" value="{{ $gaji->lembur }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control-modern currency_lembur_default">
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur" value="{{ $gaji->jumlah_lembur }}" placeholder="Masukkan Total Jam" class="form-control-modern">
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-add-glossy" id="addLembur">
                  <i class="fas fa-plus-circle"></i> INPUT
                </button>
              </div>
            </div>
            <!-- END DEFAULTS -->

            <!-- LEMBUR FIELD 1 -->
            <div class="row align-items-end mb-4 lembur-field0" style="{{ ($gaji->lembur1 == null || $gaji->jumlah_lembur1 == null || $gaji->lembur1 == '0' || $gaji->jumlah_lembur1 == '0') ? 'display: none;' : '' }}">

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur1" value="{{ $gaji->lembur1 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control-modern currency_lembur_1">
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur1" value="{{ $gaji->jumlah_lembur1 }}" placeholder="Masukkan Total Jam" class="form-control-modern">
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur0">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>

            </div>
            <!-- END LEMBUR FIELD 1 -->

            <!-- LEMBUR FIELD 2 -->
            <div class="row align-items-end mb-4 lembur-field2" style="{{ ($gaji->lembur2 == null || $gaji->jumlah_lembur2 == null || $gaji->lembur2 == '0' || $gaji->jumlah_lembur2 == '0') ? 'display: none;' : '' }}">

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur2" value="{{ $gaji->lembur2 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control-modern currency_lembur_2" autofocus>
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur2" value="{{ $gaji->jumlah_lembur2 }}" placeholder="Masukkan Total Jam" class="form-control-modern" autofocus>
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur2">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>

            </div>
            <!-- END LEMBUR FIELD 2 -->

            <!-- LEMBUR FIELD 3 -->
            <div class="row align-items-end mb-4 lembur-field3" style="{{ ($gaji->lembur3 == null || $gaji->jumlah_lembur3 == null || $gaji->lembur3 == '0' || $gaji->jumlah_lembur3 == '0') ? 'display: none;' : '' }}">

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur3" value="{{ $gaji->lembur3 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control-modern currency_lembur_3" autofocus>
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur3" value="{{ $gaji->jumlah_lembur3 }}" placeholder="Masukkan Total Jam" class="form-control-modern" autofocus>
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur3">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>

            </div>
            <!-- END LEMBUR FIELD 3 -->

            <!-- LEMBUR FIELD 4 -->
            <div class="row align-items-end mb-4 lembur-field4" style="{{ ($gaji->lembur4 == null || $gaji->jumlah_lembur4 == null || $gaji->lembur4 == '0' || $gaji->jumlah_lembur4 == '0') ? 'display: none;' : '' }}">

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur4" value="{{ $gaji->lembur4 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control-modern currency_lembur_4" autofocus>
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur4" value="{{ $gaji->jumlah_lembur4 }}" placeholder="Masukkan Total Jam" class="form-control-modern" autofocus>
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur4">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>

            </div>
            <!-- END LEMBUR FIELD 4 -->

            <!-- LEMBUR FIELD 5 -->
            <div class="row align-items-end mb-4 lembur-field5" style="{{ ($gaji->lembur5 == null || $gaji->jumlah_lembur5 == null || $gaji->lembur5 == '0' || $gaji->jumlah_lembur5 == '0') ? 'display: none;' : '' }}">

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur5" value="{{ $gaji->lembur5 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control-modern currency_lembur_5" autofocus>
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur5" value="{{ $gaji->jumlah_lembur5 }}" placeholder="Masukkan Total Jam" class="form-control-modern" autofocus>
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur5">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>

            </div>
            <!-- END LEMBUR FIELD 5 -->

            <!-- LEMBUR FIELD 6 -->
            <div class="row align-items-end mb-4 lembur-field6" style="{{ ($gaji->lembur6 == null || $gaji->jumlah_lembur6 == null || $gaji->lembur6 == '0' || $gaji->jumlah_lembur6 == '0') ? 'display: none;' : '' }}">

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur6" value="{{ $gaji->lembur6 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control-modern currency_lembur_6" autofocus>
                  </div>
                  @error('lembur6')
                  <div class="invalid-feedback" style="display: block; font-weight: 600;">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur6" value="{{ $gaji->jumlah_lembur6 }}" placeholder="Masukkan Total Jam" class="form-control-modern" autofocus>
                  @error('jumlah_lembur6')
                  <div class="invalid-feedback" style="display: block; font-weight: 600;">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur6">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>

            </div>
            <!-- END LEMBUR FIELD 6 -->

            <!-- LEMBUR FIELD 7 -->
            <div class="row align-items-end mb-4 lembur-field7" style="{{ ($gaji->lembur7 == null || $gaji->jumlah_lembur7 == null || $gaji->lembur7 == '0' || $gaji->jumlah_lembur7 == '0') ? 'display: none;' : '' }}">

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur7" value="{{ $gaji->lembur7 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control-modern currency_lembur_7" autofocus>
                  </div>
                  @error('lembur7')
                  <div class="invalid-feedback" style="display: block; font-weight: 600;">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur7" value="{{ $gaji->jumlah_lembur7 }}" placeholder="Masukkan Total Jam" class="form-control-modern" autofocus>
                  @error('jumlah_lembur7')
                  <div class="invalid-feedback" style="display: block; font-weight: 600;">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur7">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>

            </div>
            <!-- END LEMBUR FIELD 7 -->

            <!-- LEMBUR FIELD 8 -->
            <div class="row align-items-end mb-4 lembur-field8" style="{{ ($gaji->lembur8 == null || $gaji->jumlah_lembur8 == null || $gaji->lembur8 == '0' || $gaji->jumlah_lembur8 == '0') ? 'display: none;' : '' }}">

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur8" value="{{ $gaji->lembur8 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control-modern currency_lembur_8" autofocus>
                  </div>
                  @error('lembur8')
                  <div class="invalid-feedback" style="display: block; font-weight: 600;">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur8" value="{{ $gaji->jumlah_lembur8 }}" placeholder="Masukkan Total Jam" class="form-control-modern" autofocus>
                  @error('jumlah_lembur8')
                  <div class="invalid-feedback" style="display: block; font-weight: 600;">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur8">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>

            </div>
            <!-- END LEMBUR FIELD 8 -->

            <!-- LEMBUR FIELD 9 -->
            <div class="row align-items-end mb-4 lembur-field9" style="{{ ($gaji->lembur9 == null || $gaji->jumlah_lembur9 == null || $gaji->lembur9 == '0' || $gaji->jumlah_lembur9 == '0') ? 'display: none;' : '' }}">

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur9" value="{{ $gaji->lembur9 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control-modern currency_lembur_9" autofocus>
                  </div>
                  @error('lembur9')
                  <div class="invalid-feedback" style="display: block; font-weight: 600;">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur9" value="{{ $gaji->jumlah_lembur9 }}" placeholder="Masukkan Total Jam" class="form-control-modern" autofocus>
                  @error('jumlah_lembur9')
                  <div class="invalid-feedback" style="display: block; font-weight: 600;">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur9">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>

            </div>
            <!-- END LEMBUR FIELD 9 -->

            <!-- LEMBUR FIELD 10 -->
            <div class="row align-items-end mb-0 lembur-field10" style="{{ ($gaji->lembur10 == null || $gaji->jumlah_lembur10 == null || $gaji->lembur10 == '0' || $gaji->jumlah_lembur10 == '0') ? 'display: none;' : '' }}">

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur10" value="{{ $gaji->lembur10 }}" placeholder="Masukkan Bayaran Lembur Per Jam" class="form-control-modern currency_lembur_10" autofocus>
                  </div>
                  @error('lembur10')
                  <div class="invalid-feedback" style="display: block; font-weight: 600;">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur10" value="{{ $gaji->jumlah_lembur10 }}" placeholder="Masukkan Total Jam" class="form-control-modern" autofocus>
                  @error('jumlah_lembur10')
                  <div class="invalid-feedback" style="display: block; font-weight: 600;">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur10">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>

            </div>
            <!-- END LEMBUR FIELD 10 -->

            <!-- TOTAL LEMBUR -->
            <div class="row mt-4 pt-4 border-top">
              <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center p-3" style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); border-radius: 12px; border: 1px solid #a5b4fc; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.15);">
                  <h5 class="mb-0" style="color: #3730a3; font-weight: 800;"><i class="fas fa-money-bill-wave mr-2"></i> Total Bonus Lembur</h5>
                  <h4 class="mb-0" style="color: #312e81; font-weight: 900;" id="grandTotalLembur">Rp 0</h4>
                </div>
              </div>
            </div>
            <!-- END TOTAL LEMBUR -->

          </div>
        </div>
        <!--================== END ==================-->

        <!--================== BONUS DARI PRESENSI ==================-->
        <div class="card-neo">
          <div class="card-header-neo">
            <i class="fas fa-calculator"></i>
            <span>Bonus Presensi</span>
          </div>
          <div class="card-body p-4">

            <div class="row">
              <!-- TOTAL ALPHA -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Total Alpha</label>
                  <input type="text" id="alpha" name="jumlah_bonus5" placeholder="Total Tanpa Kehadiran" class="form-control-modern" readonly>
                </div>
              </div>
              <!-- END TOTAL ALPHA -->

              <!-- KEHADIRAN -->
              <div class="col-md-3">
                <div class="form-group">
                  <label>Bonus Kehadiran</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="bonus" value="{{ $gaji->bonus }}" placeholder="Bonus Kehadiran" class="form-control-modern currency_kehadiran">
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Total Kehadiran</label>
                  <input type="text" id="hadir" name="jumlah_bonus" placeholder="Total Kehadiran" class="form-control-modern" readonly>
                </div>
              </div>
              <!-- END KEHADIRAN -->
            </div>

            <div class="row">
              <!-- CAMP JOGJA -->
              <div class="col-md-3">
                <div class="form-group">
                  <label>Bonus Camp Jogja</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="bonus1" value="{{ $gaji->bonus1 }}" placeholder="Bonus Camp Jogja" class="form-control-modern currency_camp_jogja">
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Total Camp Jogja</label>
                  <input type="text" id="camp_jogja" name="jumlah_bonus1" placeholder="Total Camp Jogja" class="form-control-modern" readonly>
                </div>
              </div>
              <!-- END CAMP JOGJA -->

              <!-- CAMP LUAR KOTA-->
              <div class="col-md-3">
                <div class="form-group">
                  <label>Bonus Camp Luar Kota</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="bonus4" value="{{ $gaji->bonus4 }}" placeholder="Bonus Camp Luar Kota" class="form-control-modern currency_camp_luar_kota">
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Total Camp Luar Kota</label>
                  <input type="text" id="camp_luar_kota" name="jumlah_bonus4" placeholder="Total Camp Luar Kota" class="form-control-modern" readonly>
                </div>
              </div>
              <!-- END CAMP LUAR KOTA -->
            </div>

            <div class="row">
              <!-- PERJALANAN JAWA -->
              <div class="col-md-3">
                <div class="form-group">
                  <label>Bonus Perjalanan Dalam Jawa</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="bonus2" value="{{ $gaji->bonus2 }}" placeholder="Bonus Perjalanan Dalam Jawa" class="form-control-modern currency_perjalanan_jawa">
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Total Perjalanan Dalam Jawa</label>
                  <input type="text" id="perjalanan_dalam_jawa" name="jumlah_bonus2" placeholder="Total Perjalanan Dalam Jawa" class="form-control-modern" readonly>
                </div>
              </div>
              <!-- END PERJALANAN JAWA -->

              <!-- PERJALANAN LUAR JAWA -->
              <div class="col-md-3">
                <div class="form-group">
                  <label>Bonus Perjalanan Luar Jawa</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="bonus3" value="{{ $gaji->bonus3 }}" placeholder="Bonus Perjalanan Luar Jawa" class="form-control-modern currency_perjalanan_luar_jawa">
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Total Perjalanan Luar Jawa</label>
                  <input type="text" id="perjalanan_luar_jawa" name="jumlah_bonus3" placeholder="Total Perjalanan Luar Jawa" class="form-control-modern" readonly>
                </div>
              </div>
              <!-- END PERJALANAN LUAR JAWA -->
            </div>

            <div class="row">
              <!-- REMOTE -->
              <div class="col-md-3">
                <div class="form-group">
                  <label>Bonus Remote</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="bonus6" value="{{ $gaji->bonus6 }}" placeholder="Bonus Remote" class="form-control-modern currency_remote">
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Total Remote</label>
                  <input type="text" id="remote" name="jumlah_bonus6" placeholder="Total Remote" class="form-control-modern" readonly>
                </div>
              </div>
              <!-- END REMOTE -->

              <!-- TOTAL IZIN -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Total Izin</label>
                  <input type="text" id="izin" name="jumlah_bonus7" placeholder="Total Izin" class="form-control-modern" readonly>
                </div>
              </div>
              <!-- END TOTAL IZIN -->
            </div>

            <!-- TOTAL BONUS PRESENSI -->
            <div class="row mt-4 pt-4 border-top">
              <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center p-3" style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); border-radius: 12px; border: 1px solid #a5b4fc; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.15);">
                  <h5 class="mb-0" style="color: #3730a3; font-weight: 800;"><i class="fas fa-money-bill-wave mr-2"></i> Total Bonus Presensi</h5>
                  <h4 class="mb-0" style="color: #312e81; font-weight: 900;" id="GrandTotalPresensi">Rp 0</h4>
                </div>
              </div>
            </div>
            <!-- END TOTAL BONUS PRESENSI -->
          </div>
        </div>
        <!--================== END ==================-->

        <!--================== BONUS LAINNYA ==================-->
        <div class="card-neo">
          <div class="card-header-neo">
            <i class="fas fa-calculator"></i>
            <span>Bonus Lainnya</span>
          </div>
          <div class="card-body p-4">

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Bonus Webinar</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="webinar" id="webinar" value="{{ $gaji->webinar }}" placeholder="Masukkan Total Bonus Webinar" class="form-control-modern currency_webinar">
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Bonus Kinerja</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="kinerja" id="kinerja" value="{{ $gaji->kinerja }}" placeholder="Masukkan Total Bonus Kinerja" class="form-control-modern currency_kinerja">
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Tunjangan Kesehatan</label>
                  <div class="input-group">
                    <div class="input-group">
                      <span class="modern-prefix">Rp</span>
                      <input type="text" name="tunjangan_bpjs" id="tunjangan_bpjs" value="{{ $gaji->tunjangan_bpjs }}" placeholder="Masukkan Total Tunjangan Kesehatan" class="form-control-modern currency_tunjanganBPJS">
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Tunjangan THR</label>
                  <div class="input-group">
                    <div class="input-group">
                      <span class="modern-prefix">Rp</span>
                      <input type="text" name="tunjangan_thr" id="tunjangan_thr" value="{{ $gaji->tunjangan_thr }}" placeholder="Masukkan Total Tunjangan THR" class="form-control-modern currency_tunjanganTHR">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Tunjangan Pulsa</label>
                  <div class="input-group">
                    <div class="input-group">
                      <span class="modern-prefix">Rp</span>
                      <input type="text" name="tunjangan_pulsa" id="tunjangan_pulsa" value="{{ $gaji->tunjangan_pulsa }}" placeholder="Masukkan Total Tunjangan Pulsa" class="form-control-modern currency_tunjanganPulsa">
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Tunjangan Lainnya</label>
                  <div class="input-group">
                    <div class="input-group">
                      <span class="modern-prefix">Rp</span>
                      <input type="text" name="tunjangan" id="tunjangan" value="{{ $gaji->tunjangan }}" placeholder="Masukkan Total Tunjangan Lainnya" class="form-control-modern currency_tunjangan_lainnya">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- TOTAL BONUS LAINNYA -->
            <div class="row mt-4 pt-4 border-top">
              <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center p-3" style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); border-radius: 12px; border: 1px solid #a5b4fc; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.15);">
                  <h5 class="mb-0" style="color: #3730a3; font-weight: 800;"><i class="fas fa-money-bill-wave mr-2"></i> Total Bonus Lainnya</h5>
                  <h4 class="mb-0" style="color: #312e81; font-weight: 900;" id="GrandTotalLainnya">Rp 0</h4>
                </div>
              </div>
            </div>
            <!-- END TOTAL BONUS LAINNYA -->

          </div>
        </div>
        <!--================== END ==================-->

        <!--================== POTONGAN ==================-->
        <div class="card-neo">
          <div class="card-header-neo">
            <i class="fas fa-calculator"></i>
            <span>Potongan</span>
          </div>
          <div class="card-body p-4">

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Potongan</label>
                  <div class="input-group">
                    <div class="input-group">
                      <span class="modern-prefix">Rp</span>
                      <input type="text" name="potongan" id="potongan" value="{{ $gaji->potongan }}" placeholder="Masukkan Total Potongan" class="form-control-modern currency_potongan">
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>PPH 21</label>
                  <div class="input-group">
                    <div class="input-group">
                      <span class="modern-prefix">Rp</span>
                      <input type="text" name="pph" id="pph" value="{{ $gaji->pph }}" placeholder="Masukkan Total PPH 21" class="form-control-modern currency_pph">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- TOTAL POTONGAN -->
            <div class="row mt-4 pt-4 border-top">
              <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center p-3" style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); border-radius: 12px; border: 1px solid #a5b4fc; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.15);">
                  <h5 class="mb-0" style="color: #3730a3; font-weight: 800;"><i class="fas fa-money-bill-wave mr-2"></i> Total Potongan</h5>
                  <h4 class="mb-0" style="color: #312e81; font-weight: 900;" id="GrandTotalPotongan">Rp 0</h4>
                </div>
              </div>
            </div>
            <!-- END TOTAL POTONGAN -->

          </div>
        </div>
        <!--================== END ==================-->

        <!--================== LAINNYA ==================-->
        <div class="card-neo">
          <div class="card-header-neo">
            <i class="fas fa-clipboard-list"></i>
            <span>Lainnya & Konfirmasi</span>
          </div>

          <div class="card-body p-4 p-md-5">

            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="form-group mb-0">
                  <label>Status Pembayaran</label>
                  <select class="form-control-modern" name="status" style="height: auto;">
                    <option value="" disabled selected>-- PILIH STATUS PEMBAYARAN --</option>
                    <option value="pending" {{ $gaji->status == 'pending' ? 'selected' : '' }}>PENDING</option>
                    <option value="terbayar" {{ $gaji->status == 'terbayar' ? 'selected' : '' }}>TERBAYAR</option>
                  </select>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="form-group">
                  <label>Tanggal Dibayarkan</label>
                  <input type="datetime-local" name="tanggal" id="tanggal" value="{{ $gaji->tanggal }}" placeholder="Masukkan Total Tunjangan" class="form-control-modern">
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12 mb-3">
                <div class="form-group mb-0">
                  <label>Catatan</label>
                  <textarea name="note" id="note" placeholder="Tuliskan catatan tambahan (opsional)..." class="form-control-modern" rows="2" style="min-height: 48px;">{{ $gaji->note }}</textarea>
                </div>
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-md-6 mb-3">
                <div class="form-group mb-0">
                  <label>Bukti Pembayaran</label>
                  <div class="mt-2">
                    <input type="file" name="gambar" id="gambar" class="inputfile" accept="image/*">
                    <label for="gambar" class="upload-btn-glossy">
                      <i class="fas fa-cloud-upload-alt"></i> Pilih Gambar Bukti
                    </label>
                  </div>
                  <small class="text-muted font-weight-bold mt-2 d-block">Format: JPG, JPEG, PNG (Maks 3MB)</small>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="preview-container-glossy" style="position: relative; height: 300px; border: 2px dashed #e2e8f0; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f8fafc; border-radius: 12px; overflow: hidden;">
                  <div id="placeholder-elements" style="text-align: center; z-index: 1;">
                    <i class="far fa-image text-muted" style="font-size: 24px; margin-bottom: 5px;"></i>
                    <span class="text-muted" style="font-size: 12px; font-weight: 600; display: block;">Preview Gambar Akan Tampil Disini</span>
                  </div>
                  @if ($gaji->gambar == null)
                  <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}"
                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 2;">
                  @else
                  <img id="image-preview" src="{{ asset('images/' . $gaji->gambar) }}" alt="Preview Image"
                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; object-position: top; z-index: 2;">
                  @endif
                  <div id="imagePreview" class="image-preview w-100" style="display: none;"></div>
                  <span id="file-selected"></span>
                </div>
              </div>
            </div>

            <div class="row mt-4 pt-4 border-top">
              <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center p-3" style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); border-radius: 12px; border: 1px solid #a5b4fc; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.15);">
                  <h5 class="mb-0" style="color: #3730a3; font-weight: 800;"><i class="fas fa-money-bill-wave mr-2"></i> Total Bayar </h5>
                  <h4 class="mb-0" style="color: #312e81; font-weight: 900;">Rp {{ number_format($gaji->total, 0, ',', '.') }}</h4>
                </div>
              </div>
            </div>

            <div class="d-flex flex-md-nowrap flex-wrap gap-3 mt-5">
              <button type="submit" class="btn-modern btn-update flex-grow-1">
                <i class="fas fa-sync-alt"></i> UPDATE DATA
              </button>
            </div>

          </div>
        </div>
      </form>
    </div>
  </section>
</div>

<!--================== VALIDASI IMAGE ==================-->
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
<!--================== END VALIDASI IMAGE ==================-->

<!--================== CHANGE PREVIEW IMAGE ==================-->
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
<!--================== END CHANGE PREVIEW IMAGE ==================-->

<!--================== FORMAT RUPIAH ==================-->
<script>
  var cleaveC = new Cleave('.currency', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.currency_ethes', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var timeoutHandler = null;
</script>
<!--================== END FORMAT RUPIAH ==================-->

<!--================== MENAMPILKAN DATA KARYAWAN ==================-->
<script>
  $(document).ready(function() {

    // Function to update the input fields based on selected karyawan
    function updateFields() {
      var selectedKaryawanOption = $('#karyawanSelect option:selected');

      if (selectedKaryawanOption.length) {
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
<!--================== END MENAMPILKAN DATA KARYAWAN ==================-->

<!--================== ADD & REMOVE LEMBUR ==================-->
<script>
  $(document).ready(function() {

    // 1. SIMPAN MEMORI DATA DATABASE SAAT HALAMAN DIMUAT
    // Menyimpan nilai asli setiap input ke dalam atribut sementara (data-original)
    $('[class*="lembur-field"] input, [class*="bonus-field"] input').each(function() {
      $(this).data('original', $(this).val());
    });

    // ==========================================
    // LOGIKA BONUS LEMBUR
    // ==========================================
    function checkAddLemburBtn() {
      let hiddenFields = $('[class*="lembur-field"]:hidden').length;
      if (hiddenFields === 0) {
        $('#addLembur').hide();
      } else {
        $('#addLembur').show();
      }
    }

    $('#addLembur').on('click', function() {
      let firstHidden = $('[class*="lembur-field"]:hidden').first();

      // Kembalikan nilai input ke memori asli (database) sebelum ditampilkan
      firstHidden.find('input').each(function() {
        $(this).val($(this).data('original'));
      });

      firstHidden.show();
      checkAddLemburBtn();
    });

    $('[id^="removeAddedLembur"]').on('click', function() {
      let parentRow = $(this).closest('[class*="lembur-field"]');

      parentRow.hide();
      parentRow.find('input').val(''); // Kosongkan agar di DB terhapus jika disimpan

      checkAddLemburBtn();
    });

    checkAddLemburBtn();


    // ==========================================
    // LOGIKA BONUS LAINNYA
    // ==========================================
    function checkAddBonusBtn() {
      let hiddenFields = $('[class*="bonus-field"]:hidden').length;
      if (hiddenFields === 0) {
        $('#addBonus').hide();
      } else {
        $('#addBonus').show();
      }
    }

    $('#addBonus').on('click', function() {
      let firstHidden = $('[class*="bonus-field"]:hidden').first();

      // Kembalikan nilai input ke memori asli (database) sebelum ditampilkan
      firstHidden.find('input').each(function() {
        $(this).val($(this).data('original'));
      });

      firstHidden.show();
      checkAddBonusBtn();
    });

    $('[id^="removeAddedBonus"]').on('click', function() {
      let parentRow = $(this).closest('[class*="bonus-field"]');

      parentRow.hide();
      parentRow.find('input').val('');

      checkAddBonusBtn();
    });

    checkAddBonusBtn();

  });
</script>
<!--================== END ADD & REMOVE LEMBUR ==================-->

<!--================== CALCULATE GRAND TOTAL LEMBUR ==================-->
<script>
  $(document).ready(function() {

    // Fungsi untuk memformat angka menjadi format Rupiah (contoh: 1000000 -> 1.000.000)
    function formatRupiahHitung(angka) {
      var number_string = angka.toString().replace(/[^,\d]/g, ''),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

      if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
      }
      return rupiah;
    }

    // Fungsi utama kalkulasi
    function calculateGrandTotalLembur() {
      let totalLembur = 0;

      // Array daftar field lembur (kosong untuk default, lalu 1 sampai 10)
      let fieldSuffixes = ['', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];

      fieldSuffixes.forEach(function(suffix) {
        // Ambil elemen input
        let lemburInput = $('input[name="lembur' + suffix + '"]');
        let jamInput = $('input[name="jumlah_lembur' + suffix + '"]');

        // Bersihkan format currency (hilangkan titik dan huruf) agar bisa dihitung secara matematis
        let lemburValStr = lemburInput.val() ? lemburInput.val().replace(/[^0-9]/g, '') : '0';
        let lemburRate = parseInt(lemburValStr) || 0;

        // Ambil nilai jam (ubah koma jadi titik agar terbaca sebagai desimal)
        let jamValStr = jamInput.val() ? jamInput.val().replace(',', '.') : '0';
        let jamHours = parseFloat(jamValStr) || 0;

        // Kalikan (Tarif per Jam * Jam) dan tambahkan ke total
        totalLembur += (lemburRate * jamHours);
      });

      // Tampilkan hasil di kotak total
      $('#grandTotalLembur').text('Rp ' + formatRupiahHitung(totalLembur));
    }

    // Panggil fungsi saat user mengetik sesuatu (keyup) atau nilai input berubah (change)
    $(document).on('input change keyup', 'input[name^="lembur"], input[name^="jumlah_lembur"]', function() {
      calculateGrandTotalLembur();
    });

    // Panggil fungsi saat tombol hapus diklik (delay 100ms agar input sempat di-clear oleh fungsi sebelumnya)
    $('[id^="removeAddedLembur"]').on('click', function() {
      setTimeout(calculateGrandTotalLembur, 100);
    });

    // Panggil fungsi sekali saat halaman pertama kali dimuat (untuk load data Edit DB)
    calculateGrandTotalLembur();

  });
</script>
<!--================== END CALCULATE GRAND TOTAL LEMBUR ==================-->

<!--================== CALCULATE GRAND TOTAL PRESENSI ==================-->
<script>
  function calculateGrandTotalPresensi() {
    let totalPresensi = 0;

    // Daftar item: [selector_input_bonus, selector_total_item]
    let items = [
      ['input[name="bonus"]', '#hadir'],
      ['input[name="bonus1"]', '#camp_jogja'],
      ['input[name="bonus4"]', '#camp_luar_kota'],
      ['input[name="bonus2"]', '#perjalanan_dalam_jawa'],
      ['input[name="bonus3"]', '#perjalanan_luar_jawa'],
      ['input[name="bonus6"]', '#remote']
    ];

    items.forEach(function(item) {
      // Ambil nilai, jika tidak ada value di HTML, coba ambil .val()
      let bonusEl = $(item[0]);
      let totalEl = $(item[1]);

      // Ambil nilai bersih
      let bonusVal = bonusEl.val() ? bonusEl.val().replace(/[^0-9]/g, '') : '0';
      let totalVal = totalEl.val() ? totalEl.val().replace(/[^0-9]/g, '') : '0';

      let cleanBonus = parseInt(bonusVal) || 0;
      let cleanTotal = parseInt(totalVal) || 0;

      totalPresensi += (cleanBonus * cleanTotal);
    });

    // Update tampilan - Pastikan ID di sini sama dengan ID di HTML (GrandTotalPresensi)
    $('#GrandTotalPresensi').text('Rp ' + totalPresensi.toLocaleString('id-ID'));
  }

  // Jalankan saat ada perubahan
  $(document).on('input change', 'input', function() {
    calculateGrandTotalPresensi();
  });

  // Jalankan saat dokumen siap dan saat window selesai load
  $(document).ready(function() {
    calculateGrandTotalPresensi();
  });

  $(window).on('load', function() {
    calculateGrandTotalPresensi();
  });
</script>
<!--================== END CALCULATE GRAND TOTAL PRESENSI ==================-->

<!--================== CALCULATE GRAND TOTAL LAINNYA ==================-->
<script>
  function calculateGrandTotalLainnya() {
    let totalLainnya = 0;

    // Daftar ID dari semua input bonus lainnya
    let ids = [
      '#webinar',
      '#kinerja',
      '#tunjangan_bpjs',
      '#tunjangan_thr',
      '#tunjangan_pulsa',
      '#tunjangan'
    ];

    ids.forEach(function(id) {
      // Ambil nilai dari input, hapus semua karakter non-angka (titik/Rp)
      let valStr = $(id).val() ? $(id).val().replace(/[^0-9]/g, '') : '0';
      totalLainnya += parseInt(valStr) || 0;
    });

    // Update tampilan hasil
    $('#GrandTotalLainnya').text('Rp ' + totalLainnya.toLocaleString('id-ID'));
  }

  // Jalankan kalkulasi saat ada perubahan input
  $(document).on('input change keyup', '#webinar, #kinerja, #tunjangan_bpjs, #tunjangan_thr, #tunjangan_pulsa, #tunjangan', function() {
    calculateGrandTotalLainnya();
  });

  // Jalankan pertama kali saat halaman dimuat
  $(document).ready(function() {
    calculateGrandTotalLainnya();
  });
</script>
<!--================== END CALCULATE GRAND TOTAL LAINNYA ==================-->

<!--================== CALCULATE GRAND TOTAL POTONGAN ==================-->
<script>
  function calculateGrandTotalPotongan() {
    let totalPotongan = 0;

    // Daftar ID input yang akan dijumlahkan
    let ids = ['#potongan', '#pph'];

    ids.forEach(function(id) {
      // Ambil nilai, hapus karakter non-angka, lalu konversi ke integer
      let valStr = $(id).val() ? $(id).val().replace(/[^0-9]/g, '') : '0';
      totalPotongan += parseInt(valStr) || 0;
    });

    // Update tampilan hasil dengan format Rupiah
    $('#GrandTotalPotongan').text('Rp ' + totalPotongan.toLocaleString('id-ID'));
  }

  // Event listener untuk update otomatis saat user mengetik
  $(document).on('input change keyup', '#potongan, #pph', function() {
    calculateGrandTotalPotongan();
  });

  // Jalankan kalkulasi saat halaman selesai dimuat untuk menampilkan nilai dari database
  $(document).ready(function() {
    calculateGrandTotalPotongan();
  });
</script>
<!--================== CALCULATE GRAND TOTAL POTONGAN ==================-->

<<!--================== FORMAT RUPIAH ==================-->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/cleave.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {

      const currencyClasses = [
        '.currency',
        '.currency_ethes',

        '.currency_lembur_default',
        '.currency_lembur_1',
        '.currency_lembur_2',
        '.currency_lembur_3',
        '.currency_lembur_4',
        '.currency_lembur_5',
        '.currency_lembur_6',
        '.currency_lembur_7',
        '.currency_lembur_8',
        '.currency_lembur_9',
        '.currency_lembur_10',

        '.currency_kehadiran',
        '.currency_camp_jogja',
        '.currency_camp_luar_kota',
        '.currency_perjalanan_jawa',
        '.currency_perjalanan_luar_jawa',
        '.currency_remote',
        '.currency_izin',

        '.currency_webinar',
        '.currency_kinerja',
        '.currency_tunjanganBPJS',
        '.currency_tunjanganTHR',
        '.currency_tunjanganPulsa',
        '.currency_tunjangan_lainnya',
        '.currency_potongan',
        '.currency_pph',
      ];

      currencyClasses.forEach(selector => {
        document.querySelectorAll(selector).forEach(el => {
          new Cleave(el, {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand'
          });
        });
      });

    });
  </script>
  <!--================== END FORMAT RUPIAH ==================-->

  @stop