@extends('layouts.account')
@extends('layouts.inputfitur')

@section('title')
Tambah Gaji Karyawan | MIS
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

  .form-control-modern[readonly] {
    background-color: #f1f5f9 !important;
    color: #0b0b0b !important;
    cursor: not-allowed !important;
    box-shadow: none !important;
  }

  .input-group-modern:has(input[readonly]) {
    background-color: #f1f5f9 !important;
    cursor: not-allowed !important;
    border-color: #e2e8f0 !important;
  }

  .input-group-modern:has(input[readonly]):focus-within {
    border-color: #e2e8f0 !important;
    box-shadow: none !important;
    background-color: #f1f5f9 !important;
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
        <h1>Tambah Gaji Karyawan</h1>
        <p class="text-muted font-weight-bold mb-0">Manajemen rekapitulasi pembayaran gaji bulanan tim.</p>
      </div>
    </div>

    <div class="section-body">
      <form action="{{ route('account.gaji.store') }}" method="POST" enctype="multipart/form-data">
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
                  <label>Nama Karyawan <span class="badge-required">*</span></label>
                  <select class="form-control-modern select2" name="user_id" id="karyawanSelect" style="width: 100%" required>
                    <option value="">-- PILIH NAMA KARYAWAN --</option>
                    @foreach ($datas as $user)
                    <option value="{{ $user->id }}" data-norek="{{ $user->norek }}" data-bank="{{ $user->bank }}" data-email="{{ $user->email }}" data-alpha="{{ $user->alpha }}" data-hadir="{{ $user->hadir }}" data-camp_jogja="{{ $user->camp_jogja }}" data-camp_luar_kota="{{ $user->camp_luar_kota }}" data-perjalanan_jawa="{{ $user->perjalanan_jawa }}" data-perjalanan_luar_jawa="{{ $user->perjalanan_luar_jawa }}" data-remote="{{ $user->remote }}" data-izin="{{ $user->izin }}">{{ $user->full_name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Nomor Rekening</label>
                  <input type="text" class="form-control-modern" id="norek" readonly placeholder="0000-0000-0000">
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label>Bank</label>
                  <select class="form-control-modern bank select2" name="bank" id="bank" disabled style="height: auto;">
                    <option value="">Pilih Bank</option>
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
                  <input type="text" name="email" class="form-control-modern" id="email" readonly placeholder="email@contoh.com">
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
                  <label>Gaji Pokok <span class="badge-required">*</span></label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="gaji_pokok" value="{{ old('gaji_pokok') }}" placeholder="Masukkan Gaji Pokok Karyawan" class="form-control-modern currency" required>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Bonus Ethes Digital</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="gaji_pokok_ethes_digital" value="{{ old('gaji_pokok_ethes_digital') }}" placeholder="Masukkan Gaji Pokok Karyawan Ethes Digital" class="form-control-modern currency_ethes">
                  </div>
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
                    <input type="text" name="lembur" value="{{ old('lembur') }}" placeholder="Masukkan Nominal" class="form-control-modern currency_lembur_default">
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur" value="{{ old('jumlah_lembur') }}" placeholder="Masukkan Total Jam" class="form-control-modern input-glossy">
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-add-glossy" id="addLembur">
                  <i class="fas fa-plus-circle"></i> INPUT
                </button>
              </div>
            </div>
            <!-- END DEFAULTS -->

            <!-- LEMBUR FIELDS 1 -->
            <div class="row align-items-end mb-4 lembur-field0" style="display: none;">
              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur1" value="{{ old('lembur1') }}" placeholder="Masukkan Nominal" class="form-control-modern currency_lembur_1">
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur1" value="{{ old('jumlah_lembur1') }}" placeholder="Masukkan Total Jam" class="form-control-modern input-glossy">
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur0">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>
            </div>
            <!-- END LEMBUR FIELDS 1 -->

            <!-- LEMBUR FIELDS 2 -->
            <div class="row align-items-end mb-4 lembur-field2" style="display: none;">
              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur2" value="{{ old('lembur2') }}" placeholder="Masukkan Nominal" class="form-control-modern currency_lembur_2">
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur2" value="{{ old('jumlah_lembur2') }}" placeholder="Masukkan Total Jam" class="form-control-modern input-glossy">
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur2">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>
            </div>
            <!-- END LEMBUR FIELDS 2 -->

            <!-- LEMBUR FIELDS 3 -->
            <div class="row align-items-end mb-4 lembur-field3" style="display: none;">
              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur3" value="{{ old('lembur3') }}" placeholder="Masukkan Nominal" class="form-control-modern currency_lembur_3">
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur3" value="{{ old('jumlah_lembur3') }}" placeholder="Masukkan Total Jam" class="form-control-modern input-glossy">
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur3">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>
            </div>
            <!-- END LEMBUR FIELDS 3 -->

            <!-- LEMBUR FIELDS 4 -->
            <div class="row align-items-end mb-4 lembur-field4" style="display: none;">
              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur4" value="{{ old('lembur4') }}" placeholder="Masukkan Nominal" class="form-control-modern currency_lembur_4">
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur4" value="{{ old('jumlah_lembur4') }}" placeholder="Masukkan Total Jam" class="form-control-modern input-glossy">
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur4">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>
            </div>
            <!-- END LEMBUR FIELDS 4 -->

            <!-- LEMBUR FIELDS 5 -->
            <div class="row align-items-end mb-4 lembur-field5" style="display: none;">
              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur5" value="{{ old('lembur5') }}" placeholder="Masukkan Nominal" class="form-control-modern currency_lembur_5">
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur5" value="{{ old('jumlah_lembur5') }}" placeholder="Masukkan Total Jam" class="form-control-modern input-glossy">
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur5">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>
            </div>
            <!-- END LEMBUR FIELDS 5 -->

            <!-- LEMBUR FIELDS 6 -->
            <div class="row align-items-end mb-4 lembur-field6" style="display: none;">
              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur6" value="{{ old('lembur6') }}" placeholder="Masukkan Nominal" class="form-control-modern currency_lembur_6">
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur6" value="{{ old('jumlah_lembur6') }}" placeholder="Masukkan Total Jam" class="form-control-modern input-glossy">
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur6">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>
            </div>
            <!-- END LEMBUR FIELDS 6 -->

            <!-- LEMBUR FIELDS 7 -->
            <div class="row align-items-end mb-4 lembur-field7" style="display: none;">
              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur7" value="{{ old('lembur7') }}" placeholder="Masukkan Nominal" class="form-control-modern currency_lembur_7">
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur7" value="{{ old('jumlah_lembur7') }}" placeholder="Masukkan Total Jam" class="form-control-modern input-glossy">
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur7">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>
            </div>
            <!-- END LEMBUR FIELDS 7 -->

            <!-- LEMBUR FIELDS 8 -->
            <div class="row align-items-end mb-4 lembur-field8" style="display: none;">
              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur8" value="{{ old('lembur8') }}" placeholder="Masukkan Nominal" class="form-control-modern currency_lembur_8">
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur8" value="{{ old('jumlah_lembur8') }}" placeholder="Masukkan Total Jam" class="form-control-modern input-glossy">
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur8">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>
            </div>
            <!-- END LEMBUR FIELDS 8 -->

            <!-- LEMBUR FIELDS 9 -->
            <div class="row align-items-end mb-4 lembur-field9" style="display: none;">
              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur9" value="{{ old('lembur9') }}" placeholder="Masukkan Nominal" class="form-control-modern currency_lembur_9">
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur9" value="{{ old('jumlah_lembur9') }}" placeholder="Masukkan Total Jam" class="form-control-modern input-glossy">
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur9">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>
            </div>
            <!-- END LEMBUR FIELDS 9 -->

            <!-- LEMBUR FIELDS 10 -->
            <div class="row align-items-end mb-0 lembur-field10" style="display: none;">
              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Bonus Lembur (Per Jam)</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="lembur10" value="{{ old('lembur10') }}" placeholder="Masukkan Nominal" class="form-control-modern currency_lembur_10">
                  </div>
                </div>
              </div>

              <div class="col-md-5 mb-3 mb-md-0">
                <div class="form-group mb-0">
                  <label>Total Jam Lembur</label>
                  <input type="text" name="jumlah_lembur10" value="{{ old('jumlah_lembur10') }}" placeholder="Masukkan Total Jam" class="form-control-modern input-glossy">
                </div>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn-action-glossy btn-remove-glossy" id="removeAddedLembur10">
                  <i class="fas fa-trash-alt"></i> HAPUS
                </button>
              </div>
            </div>
            <!-- END LEMBUR FIELDS 10 -->

          </div>
        </div>
        <!--================== END BONUS LEMBUR ==================-->

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
                    <input type="text" name="bonus" value="{{ old('bonus') }}" placeholder="Bonus Kehadiran" class="form-control-modern currency_kehadiran">
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
                    <input type="text" name="bonus1" value="{{ old('bonus1') }}" placeholder="Bonus Camp Jogja" class="form-control-modern currency_camp_jogja ">
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
                    <input type="text" name="bonus4" value="{{ old('bonus4') }}" placeholder="Bonus Camp Luar Kota" class="form-control-modern currency_camp_luar_kota">
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
                    <input type="text" name="bonus2" value="{{ old('bonus2') }}" placeholder="Bonus Perjalanan Dalam Jawa" class="form-control-modern currency_perjalanan_jawa">
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Total Perjalanan Dalam Jawa</label>
                  <input type="text" id="perjalanan_jawa" name="jumlah_bonus2" placeholder="Total Perjalanan Dalam Jawa" class="form-control-modern" readonly>
                </div>
              </div>
              <!-- END PERJALANAN JAWA -->

              <!-- PERJALANAN LUAR JAWA -->
              <div class="col-md-3">
                <div class="form-group">
                  <label>Bonus Perjalanan Luar Jawa</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="bonus3" value="{{ old('bonus3') }}" placeholder="Bonus Perjalanan Luar Jawa" class="form-control-modern currency_perjalanan_luar_jawa">
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
                    <input type="text" name="bonus6" value="{{ old('bonus6') }}" placeholder="Bonus Remote" class="form-control-modern currency_remote">
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
          </div>
        </div>
        <!--================== END BONUS DARI PRESENSI ==================-->

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
                    <input type="text" name="webinar" value="{{ old('webinar') }}" placeholder="Masukkan Total Bonus Webinar" class="form-control-modern currency_webinar">
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Bonus Kinerja</label>
                  <div class="input-group">
                    <span class="modern-prefix">Rp</span>
                    <input type="text" name="kinerja" id="kinerja" value="{{ old('kinerja') }}" placeholder="Masukkan Total Bonus Kinerja" class="form-control-modern currency_kinerja">
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
                      <input type="text" name="tunjangan_bpjs" id="tunjangan_bpjs" value="{{ old('tunjangan_bpjs') }}" placeholder="Masukkan Total Tunjangan Kesehatan" class="form-control-modern currency_tunjanganBPJS">
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
                      <input type="text" name="tunjangan_thr" id="tunjangan_thr" value="{{ old('tunjangan_thr') }}" placeholder="Masukkan Total Tunjangan THR" class="form-control-modern currency_tunjanganTHR">
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
                      <input type="text" name="tunjangan_pulsa" id="tunjangan_pulsa" value="{{ old('tunjangan_pulsa') }}" placeholder="Masukkan Total Tunjangan Pulsa" class="form-control-modern currency_tunjanganPulsa">
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
                      <input type="text" name="tunjangan" id="tunjangan" value="{{ old('tunjangan') }}" placeholder="Masukkan Total Tunjangan Lainnya" class="form-control-modern currency_tunjangan_lainnya">
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
        <!--================== END BONUS LAINNYA ==================-->

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
                      <input type="text" name="potongan" id="potongan" value="{{ old('potongan') }}" placeholder="Masukkan Total Potongan" class="form-control-modern currency_potongan">
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
                      <input type="text" name="pph" id="pph" value="{{ old('pph') }}" placeholder="Masukkan Total PPH 21" class="form-control-modern currency_pph">
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
        <!--================== END POTONGAN ==================-->

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
                  <label>Status Pembayaran <span class="badge-required">*</span></label>
                  <select class="form-control-modern" name="status" required>
                    <option value="" disabled selected>-- PILIH STATUS PEMBAYARAN --</option>
                    <option value="pending">PENDING</option>
                    <option value="terbayar">TERBAYAR</option>
                  </select>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="form-group">
                  <label>Tanggal Dibayarkan <span style="color: red;">*</span></label>
                  <input type="datetime-local" name="tanggal" id="tanggal" value="{{ old('tanggal') }}" placeholder="Masukkan Total Tunjangan" class="form-control-modern" required>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12 mb-3">
                <div class="form-group mb-0">
                  <label>Catatan</label>
                  <textarea name="note" id="note" placeholder="Tuliskan catatan tambahan (opsional)..." class="form-control-modern" rows="2" style="min-height: 48px;"></textarea>
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
                <div class="preview-container-glossy" style="position: relative; height: 300px; border: 2px dashed #e2e8f0; border-radius: 12px; overflow: hidden; background: #f8fafc; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                  <div id="placeholder-elements" style="text-align: center;">
                    <i class="far fa-image text-muted" style="font-size: 40px; margin-bottom: 10px;"></i>
                    <span class="text-muted" style="font-size: 14px; font-weight: 600; display: block;">Preview Gambar Akan Tampil Disini</span>
                  </div>
                  <img id="imagePreview" src="" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 1; display: none;">
                </div>
                <div class="mt-2 text-center">
                  <span id="file-selected" style="font-size: 12px; font-weight: 700; color: #6366f1; background: #eef2ff; padding: 4px 12px; border-radius: 6px;">
                    Belum ada file dipilih
                  </span>
                </div>
              </div>
            </div>

            <div class="d-flex flex-md-nowrap flex-wrap gap-3 mt-5">
              <button type="submit" class="btn-modern btn-save flex-grow-1">
                <i class="fas fa-save"></i> SIMPAN DATA
            </div>

          </div>
        </div>
        <!--================== END LAINNYA ==================-->

      </form>
    </div>
  </section>
</div>

<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<script>
  document.getElementById('gambar').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (!isValidFile(file)) return;

    updateUI(file);
  });

  // 1. Fungsi Validasi (Pemisahan logika)
  function isValidFile(file) {
    const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
    const maxSize = 3000; // KB
    const fileSize = (file.size / 1024).toFixed(2);

    if (!allowedTypes.includes(file.type)) {
      showError('Format tidak didukung! Gunakan JPG atau PNG.');
      return false;
    }

    if (fileSize > maxSize) {
      showError('Maksimal ukuran file adalah 3MB!');
      return false;
    }

    return true;
  }

  // 2. Fungsi Update UI (Pemisahan logika tampilan)
  function updateUI(file) {
    const reader = new FileReader();
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('placeholder-elements');
    const fileLabel = document.getElementById('file-selected');

    reader.onload = (e) => {
      preview.src = e.target.result;
      preview.style.display = 'block';
      placeholder.style.display = 'none';
      fileLabel.textContent = `${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
    };

    reader.readAsDataURL(file);
  }

  function showError(message) {
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: message
    });
  }
</script>
<!--================== END UPLOAD IMAGE WITH VIEW ==================-->

<!--================== ADD & REMOVE LEMBUR ==================-->
<script>
  $(document).ready(function() {

    // 1. Tombol Tambah Lembur
    $('#addLembur').on('click', function() {
      // Cari elemen field pertama yang sedang tersembunyi
      var $hiddenField = $('[class*="lembur-field"]:hidden').first();

      if ($hiddenField.length) {
        $hiddenField.show();

        // Jika tidak ada lagi field yang tersembunyi, sembunyikan tombol tambah
        if ($('[class*="lembur-field"]:hidden').length === 0) {
          $(this).hide();
        }
      }
    });

    // 2. Tombol Hapus Lembur (Gunakan event delegation)
    $(document).on('click', '[id^="removeAddedLembur"]', function() {
      // Cari parent baris yang tombol hapusnya diklik
      var $parentRow = $(this).closest('[class*="lembur-field"]');

      // Sembunyikan baris
      $parentRow.hide();

      // Kosongkan semua input di dalam baris tersebut agar tidak tersimpan ke DB
      $parentRow.find('input').val('');

      // Tampilkan kembali tombol tambah
      $('#addLembur').show();
    });

  });
</script>
<!--================== END ADD & REMOVE LEMBUR ==================-->

<!--================== MENAMPILKAN DATA KARYAWAN ==================-->
<script>
  $(document).ready(function() {
    // 1. Inisialisasi Select2 untuk semua dropdown
    $('.select2').select2({
      width: '100%'
    });

    // 2. Fungsi utama untuk mengupdate field saat karyawan dipilih
    function updateFields() {
      var selectedKaryawanOption = $('#karyawanSelect option:selected');

      if (selectedKaryawanOption.length && selectedKaryawanOption.val() !== "") {
        // Ambil semua data dari atribut 'data-*'
        var data = {
          norek: selectedKaryawanOption.data('norek'),
          bank: selectedKaryawanOption.data('bank'),
          email: selectedKaryawanOption.data('email'),
          alpha: selectedKaryawanOption.data('alpha'),
          hadir: selectedKaryawanOption.data('hadir'),
          camp_jogja: selectedKaryawanOption.data('camp_jogja'),
          camp_luar_kota: selectedKaryawanOption.data('camp_luar_kota'),
          perjalanan_jawa: selectedKaryawanOption.data('perjalanan_jawa'),
          perjalanan_luar_jawa: selectedKaryawanOption.data('perjalanan_luar_jawa'),
          remote: selectedKaryawanOption.data('remote'),
          izin: selectedKaryawanOption.data('izin')
        };

        // Update Field Input Biasa
        $('#norek').val(data.norek);
        $('#email').val(data.email);
        $('#alpha').val(data.alpha);
        $('#hadir').val(data.hadir);
        $('#camp_jogja').val(data.camp_jogja);
        $('#camp_luar_kota').val(data.camp_luar_kota);
        $('#perjalanan_dalam_jawa').val(data.perjalanan_jawa);
        $('#perjalanan_luar_jawa').val(data.perjalanan_luar_jawa);
        $('#remote').val(data.remote);
        $('#izin').val(data.izin);

        // Update khusus Select2 (BANK)
        $('#bank').val(data.bank).trigger('change.select2');

      } else {
        // Kosongkan semua field jika tidak ada yang dipilih
        $('#norek, #email, #alpha, #hadir, #camp_jogja, #camp_luar_kota, #perjalanan_dalam_jawa, #perjalanan_luar_jawa, #remote, #izin').val('');
        $('#bank').val('').trigger('change.select2');
      }

      // Panggil fungsi hitung otomatis (jika ada)
      if (typeof calculateGrandTotalPresensi === 'function') {
        calculateGrandTotalPresensi();
      }
    }

    // 3. Jalankan fungsi saat ganti pilihan
    $('#karyawanSelect').on('change', function() {
      updateFields();
    });

    // 4. Jalankan saat pertama kali halaman dimuat (dengan sedikit delay agar Select2 siap)
    setTimeout(function() {
      updateFields();
    }, 200);
  });
</script>
<!--================== END MENAMPILKAN DATA KARYAWAN ==================-->

<!--================== FORMAT RUPIAH ==================-->
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