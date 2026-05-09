@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Profil | MIS
@stop

<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap');

  :root {
    --accent: #6366f1;
    --accent-light: #818cf8;
    --bg-main: #f4f7ff;
    --card-bg: rgba(255, 255, 255, 0.9);
    --radius-xl: 24px;
    --shadow-soft: 0 20px 40px rgba(0, 0, 0, 0.04);
    --text-dark: #1e293b;
    --bg-readonly: #f1f5f9;
    /* Warna khusus Read Only */
  }

  .main-content {
    padding-top: 110px !important;
    background-color: var(--bg-main);
    min-height: 100vh;
    font-family: 'Inter', sans-serif;
  }

  .section-header-modern {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-xl);
    padding: 25px 30px;
    border: 1px solid rgba(255, 255, 255, 0.6);
    box-shadow: var(--shadow-soft);
    margin-bottom: 30px;
  }

  .section-header-modern h1 {
    font-size: 28px !important;
    font-weight: 800 !important;
    letter-spacing: -1.5px;

    /* Gunakan kombinasi ini agar gradient muncul dengan kuat */
    background: linear-gradient(to right, #1e293b 0%, #6366f1 100%) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    color: transparent !important;
    /* Fallback */

    /* Tambahkan ini agar gradient tidak terputus */
    display: inline-block;
    line-height: 1.2;
    margin: 0;
    text-decoration: none !important;
  }

  .card-neo {
    background: var(--card-bg);
    backdrop-filter: blur(15px);
    border-radius: var(--radius-xl);
    border: 1px solid rgba(255, 255, 255, 0.8);
    box-shadow: var(--shadow-soft);
    margin-bottom: 25px;
    overflow: hidden;
  }

  .form-group label {
    font-weight: 700;
    color: #64748b;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: block;
  }

  .form-control-modern {
    border-radius: 14px;
    border: 1.5px solid #e2e8f0;
    padding: 12px 18px;
    height: auto;
    font-weight: 600;
    width: 100%;
    transition: all 0.3s ease;
    background: rgba(248, 250, 252, 0.8);
    color: var(--text-dark);
  }

  .form-control-modern:focus {
    border-color: var(--accent);
    background: #fff;
    outline: none;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
  }

  /* 🔹 STYLE KHUSUS READ ONLY */
  .form-control-modern[readonly] {
    background-color: var(--bg-readonly) !important;
    border-color: #cbd5e1;
    color: #94a3b8;
    cursor: not-allowed;
    box-shadow: none;
  }

  .btn-modern {
    border-radius: 16px;
    padding: 14px 28px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    cursor: pointer;
  }

  .btn-gradient {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    color: white;
    box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
  }

  .btn-gradient:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(99, 102, 241, 0.3);
    color: white;
  }

  .custom-popup {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(8px);
  }

  .custom-popup-content {
    position: relative;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-width: 450px;
    width: 90%;
    background: white;
    padding: 35px;
    border-radius: var(--radius-xl);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
  }

  .custom-popup-close {
    position: absolute;
    right: 20px;
    top: 15px;
    font-size: 28px;
    color: #94a3b8;
    cursor: pointer;
  }

  .position-relative {
    position: relative !important;
  }

  /* Padding kanan agar teks tidak tertutup ikon */
  .form-control-modern.pr-5 {
    padding-right: 45px !important;
  }

  /* Styling Ikon Mata */
  .password-toggle-inside {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #94a3b8;
    /* Warna abu-abu modern */
    transition: all 0.3s ease;
    z-index: 10;
    font-size: 14px;
    padding: 5px;
    /* Area klik lebih luas */
  }

  .password-toggle-inside:hover {
    color: var(--primary);
    /* Berubah warna saat hover */
  }

  /* Responsive adjustment untuk mobile */
  @media (max-width: 576px) {
    .password-toggle-inside {
      right: 12px;
      font-size: 13px;
    }
  }

  input[type="date"].form-control-modern {
    -webkit-appearance: none;
    appearance: none;
    display: block;
    width: 100% !important;
    min-height: calc(1.5em + 1.2rem + 2px);
  }

  @media (max-width: 576px) {
    .row {
      margin-left: 0 !important;
      margin-right: 0 !important;
    }

    .form-group {
      padding-left: 0 !important;
      padding-right: 0 !important;
    }
  }
</style>

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header-modern">
      <h1>Profile</h1>
      <p class="text-muted font-weight-bold mb-0">Kelola identitas dan keamanan akun Anda dalam satu tempat.</p>
    </div>

    <div class="section-body">
      <div class="row">
        <div class="col-lg-4 col-md-5">
          <div class="card-neo p-4 text-center">
            <img src="{{ Auth::user()->gambar ? asset('assets/img/profil/' . Auth::user()->gambar) : asset('assets/img/profil/no-image.jpg') }}"
              style="width: 130px; height: 130px; border-radius: 40px; object-fit: cover; border: 5px solid #fff; box-shadow: var(--shadow-soft);">

            <h5 class="mt-3 font-weight-800">{{ Auth::user()->full_name }}</h5>
            <span class="badge badge-pill badge-light text-primary font-weight-bold px-3 py-2 mb-4">
              {{ strtoupper(Auth::user()->jobdesk) }}
            </span>

            <form action="{{ route('account.profil.updatePhoto') }}" method="POST" enctype="multipart/form-data" class="mt-2 text-left">
              @csrf
              <div class="form-group mb-3">
                <label>Ganti Foto</label>
                <input type="file" name="gambar" class="form-control" style="font-size: 11px;" id="foto" onchange="togglePhotoBtn()">
              </div>
              <button type="submit" id="updatePhotoBtn" class="btn-modern btn-gradient w-100" style="padding: 10px;" disabled>
                Update Foto
              </button>
            </form>
          </div>

          <div class="card-neo p-4">
            <div class="mb-4">
              <label class="text-muted small font-weight-bold">ALAMAT EMAIL</label>
              <div class="d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">{{ Auth::user()->email }}</span>
                <button class="btn btn-sm btn-light text-warning" id="openPopupButtonEmail"><i class="fas fa-pencil-alt"></i></button>
              </div>
            </div>
            <hr style="border-top: 1px dashed #e2e8f0;">
            <div class="mb-4">
              <label class="text-muted small font-weight-bold">WHATSAPP / TELP</label>
              <div class="d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">{{ Auth::user()->telp ?? '-' }}</span>
                <button class="btn btn-sm btn-light text-warning" id="openPopupButtonTelp"><i class="fas fa-pencil-alt"></i></button>
              </div>
            </div>
            @if (Auth::user()->level !== 'user')
            <hr style="border-top: 1px dashed #e2e8f0;">
            <div class="mb-4">
              <label class="text-muted small font-weight-bold">POSISI / JABATAN</label>
              <div class="d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">{{ Auth::user()->jobdesk ?? '-' }}</span>
                <button class="btn btn-sm btn-light text-warning" id="openPopupButtonJobdesk"><i class="fas fa-pencil-alt"></i></button>
              </div>
            </div>
            <hr style="border-top: 1px dashed #e2e8f0;">
            <div class="mb-4">
              <label class="text-muted small font-weight-bold">LAMA KERJA</label>
              <div class="d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">{{ $workDuration }}</span>
              </div>
            </div>
            @endif
          </div>
        </div>

        <!--================== MODAL DATA PROFIL ==================-->
        <!-- Modal for updating email -->
        <div id="customPopupEmail" class="custom-popup">
          <div class="custom-popup-content">
            <span class="custom-popup-close" id="customPopupCloseEmail">&times;</span>
            <h5 class="font-weight-800 mb-4 text-primary">Update Email</h5>
            <form action="{{ route('account.pengguna.update.datadiri', Auth::user()->id) }}" method="POST">
              @csrf
              <div class="form-group mb-4">
                <label>Masukkan Email Terbaru</label>
                <input type="email" class="form-control-modern" name="email" value="{{ Auth::user()->email }}" required>
              </div>
              <button type="submit" class="btn-modern btn-gradient w-100">Simpan Email</button>
            </form>
          </div>
        </div>

        <!-- Modal for updating jobdesk -->
        <div id="customPopupJobdesk" class="custom-popup">
          <div class="custom-popup-content">
            <span class="custom-popup-close" id="customPopupCloseJobdesk">&times;</span>
            <h5 class="font-weight-800 mb-4 text-primary">Update Posisi / Jabatan</h5>
            <form action="{{ route('account.pengguna.update.datadiri', Auth::user()->id) }}" method="POST">
              @csrf
              <div class="form-group mb-4">
                <label>Masukkan Posisi / Jabatan Anda</label>
                <select class="form-control-modern" name="jobdesk" required>
                  <option value="">-- Pilih Jabatan --</option>
                  <option value="MANAGER" {{ Auth::user()->jobdesk == 'MANAGER' ? 'selected' : '' }}>MANAGER</option>
                  <option value="STAFF" {{ Auth::user()->jobdesk == 'STAFF' ? 'selected' : '' }}>STAFF</option>
                  <option value="ASISTEN TRAINER" {{ Auth::user()->jobdesk == 'ASISTEN TRAINER' ? 'selected' : '' }}>ASISTEN TRAINER</option>
                  <option value="KARYAWAN" {{ Auth::user()->jobdesk == 'KARYAWAN' ? 'selected' : '' }}>KARYAWAN</option>
                </select>
              </div>
              <button type="submit" class="btn-modern btn-gradient w-100">Simpan Posisi / Jabatan</button>
            </form>
          </div>
        </div>

        <!-- Modal for updating telp -->
        <div id="customPopupTelp" class="custom-popup">
          <div class="custom-popup-content">
            <span class="custom-popup-close" id="customPopupCloseTelp">&times;</span>
            <h5 class="font-weight-800 mb-4 text-primary">Update No. Telp</h5>
            <form action="{{ route('account.pengguna.update.datadiri', Auth::user()->id) }}" method="POST">
              @csrf
              <div class="form-group mb-4">
                <label>Masukkan Nomor WhatsApp Baru</label>
                <input type="text" class="form-control-modern" name="telp" value="{{ Auth::user()->telp }}" oninput="formatPhoneNumber(this)">
              </div>
              <button type="submit" class="btn-modern btn-gradient w-100">Simpan Nomor</button>
            </form>
          </div>
        </div>

        <!-- modal input kode verifikasi email -->
        <div id="customPopup" class="custom-popup" style="display:none;">
          <div class="custom-popup-content">
            <span class="custom-popup-close" id="customPopupClose">&times;</span>
            <h5 class="font-weight-800 mb-4 text-primary">Masukkan Kode Verifikasi</h5>
            <form id="verification-form" action="{{ route('account.profil.verify.code') }}" method="POST">
              @csrf
              <div class="form-group mb-4">
                <input type="text" name="verification_code" class="form-control-modern text-center" placeholder="6 Digit Kode" maxlength="6">
              </div>
              <button type="submit" class="btn-modern btn-gradient w-100">Verifikasi Sekarang</button>
            </form>
          </div>
        </div>
        <!--================== END ==================-->

        <div class="col-lg-8 col-md-7">
          <div class="card-neo">
            <div class="card-header bg-transparent border-0 p-4 pb-0">
              <h5 class="font-weight-800 text-dark mb-4">
                <i class="fas fa-id-card text-primary mr-2"></i> Pengaturan Akun
              </h5>
              <ul class="nav nav-pills nav-fill p-2" id="pills-tab" role="tablist"
                style="background: #f1f5f9; border-radius: 18px; border: 1px solid #e2e8f0;">
                <li class="nav-item">
                  <a class="nav-link active font-weight-bold d-flex align-items-center justify-content-center"
                    id="pills-activity-tab" data-toggle="pill" href="#activity" role="tab"
                    style="border-radius: 14px; padding: 12px; transition: 0.3s;">
                    <i class="fas fa-user-circle mr-2"></i> Profil Saya
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link font-weight-bold d-flex align-items-center justify-content-center"
                    id="pills-settings-tab" data-toggle="pill" href="#settings" role="tab"
                    style="border-radius: 14px; padding: 12px; transition: 0.3s;">
                    <i class="fas fa-shield-alt mr-2"></i> Ubah Password
                  </a>
                </li>
              </ul>
            </div>

            <div class="card-body pt-0">
              <div class="tab-content" id="profileTabContent">

                <!--================== TAB 1: DATA PROFIL ==================-->
                <div class="tab-pane fade show active" id="activity" role="tabpanel">
                  @if (Auth::user()->email_verified_at == null)
                  <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4" style="border-radius: 16px; background: #fffbeb; border: 1px solid #fde68a !important;">
                    <i class="fas fa-shield-alt text-warning mr-3 fa-lg"></i>
                    <div class="font-weight-bold text-dark small">Email Anda belum diverifikasi. Harap segera verifikasi akun Anda.</div>
                  </div>
                  @endif

                  <div class="mb-4">
                    <h6 class="text-uppercase small font-weight-800 text-muted mb-3" style="letter-spacing: 1px;">Profil Dasar</h6>
                    <div class="row">
                      <div class="col-md-6 form-group">
                        <label class="small font-weight-bold">Nama Lengkap</label>
                        <input class="form-control-modern" type="text" name="full_name" value="{{ $user->full_name }}" form="form-update-data">
                      </div>
                      <div class="col-md-6 form-group">
                        <label class="small font-weight-bold">Username</label>
                        <input class="form-control-modern" type="text" name="username" value="{{ $user->username }}" form="form-update-data">
                      </div>
                    </div>

                    <form id="verify-email-form" action="{{ route('account.profil.verify.email') }}" method="POST">
                      @csrf
                      <input type="hidden" name="code_verified_mail" value="{{ Auth::user()->code_verified_mail }}">
                      <div class="row align-items-end">
                        <div class="{{ Auth::user()->email_verified_at ? 'col-md-12' : 'col-md-8' }} form-group">
                          <label class="small font-weight-bold">Email Terdaftar</label>
                          <input class="form-control-modern bg-light" type="text" value="{{ Auth::user()->email }}" readonly style="cursor: not-allowed;">
                        </div>

                        @if(!Auth::user()->email_verified_at)
                        <div class="col-md-4 form-group" id="container-verify-btn">
                          <button type="button" id="btn-verify-email" class="btn-modern btn-info w-100" style="padding: 13px; font-size: 12px; border-radius: 14px;">
                            <i class="fas fa-check-circle mr-1"></i> Verifikasi Email
                          </button>
                        </div>
                        @endif
                      </div>
                    </form>
                  </div>

                  <hr class="my-4" style="border-top: 1px dashed #e2e8f0;">

                  <form id="form-update-data" action="{{ route('account.profil.update') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                      <h6 class="text-uppercase small font-weight-800 text-muted mb-3" style="letter-spacing: 1px;">Akses & Kepegawaian</h6>
                      <div class="row">
                        <div class="col-md-4 form-group">
                          <label class="small font-weight-bold">Status Akun</label>
                          <select class="form-control-modern" name="status" disabled style="color: #6b6b6c; background-color: #f1f5f9; border: 2px solid #cbd5e1; cursor: not-allowed; height:55px">
                            <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="nonactive" {{ $user->status === 'nonactive' ? 'selected' : '' }}>Non Active</option>
                          </select>
                        </div>

                        <div class="col-md-4 form-group">
                          <label class="small font-weight-bold">Level Sistem</label>
                          <select class="form-control-modern" name="level" disabled style="color: #6b6b6c; background-color: #f1f5f9; border: 2px solid #cbd5e1; cursor: not-allowed; height:55px">
                            <option value="">-- Pilih Level --</option>
                            <option value="manager" {{ $user->level == 'manager' ? 'selected' : '' }}>Manager Sistem</option>
                            <option value="karyawan" {{ $user->level == 'karyawan' ? 'selected' : '' }}>Karyawan Sistem</option>
                            <option value="staff" {{ $user->level == 'staff' ? 'selected' : '' }}>Staff Sistem</option>
                            <option value="user" {{ $user->level == 'user' ? 'selected' : '' }}>User Sistem</option>
                          </select>
                        </div>

                        @if($user->level === 'user')
                        {{-- Muncul jika level user --}}
                        <div class="col-md-4 form-group">
                          <label class="small font-weight-bold">Tanggal Lahir</label>
                          <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control-modern"
                            value="{{ old('tanggal_lahir', $user->tanggal_lahir) }}" required>
                        </div>
                        @else
                        {{-- Muncul jika level selain user (manager, karyawan, staff) --}}
                        <div class="col-md-4 form-group">
                          <label class="small font-weight-bold">Kode Perusahaan</label>
                          <input class="form-control-modern" type="text" value="{{ $user->company ?? 'Tidak Ada' }}"
                            readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                        </div>
                        @endif
                      </div>

                      <div class="row">
                        <div class="col-md-12 form-group">
                          <label class="small font-weight-bold">Jenis Akun</label>
                          <select class="form-control-modern" name="jenis" disabled style="color: #6b6b6c; background-color: #f1f5f9; border: 2px solid #cbd5e1; cursor: not-allowed; height:55px">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="bisnis" {{ $user->jenis == 'bisnis' ? 'selected' : '' }}>Bisnis (Entity)</option>
                            <option value="perorangan" {{ $user->jenis == 'perorangan' ? 'selected' : '' }}>Perorangan (Personal)</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    @if (Auth::user()->level !== 'user')
                    <hr class="my-4" style="border-top: 1px dashed #e2e8f0;">

                    <div class="mb-2">
                      <h6 class="text-uppercase small font-weight-800 text-muted mb-3" style="letter-spacing: 1px;">Data Finansial & Pribadi</h6>
                      <div class="row">
                        <div class="col-md-4 form-group">
                          <label class="small font-weight-bold">Tanggal Lahir</label>
                          <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control-modern" value="{{ old('tanggal_lahir', $user->tanggal_lahir) }}" max="{{ \Carbon\Carbon::now()->subYears(15)->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                          <label class="small font-weight-bold">No Rekening</label>
                          <input type="text" id="norek" name="norek" class="form-control-modern" value="{{ old('norek', $user->norek) }}" placeholder="Nomor Rekening" maxlength="40" onkeypress="return event.charCode >= 48 && event.charCode <=57" oninput="formatNoRek(this)">
                        </div>
                        <div class="col-md-4 form-group">
                          <label class="small font-weight-bold">Bank</label>
                          <select class="form-control-modern bank" id="bank" name="bank" style="height:55px">
                            <option value="" disabled selected>-- PILIH NAMA BANK --</option>
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
                    @endif

                    <div class="form-group mt-2">
                      <button type="submit" class="btn-modern btn-gradient w-100">
                        <i class="fas fa-sync-alt"></i> UPDATE DATA
                      </button>
                    </div>
                  </form>
                </div>
                <!--================== END TAB DATA DIRI ==================-->

                <!--================== TAB 2: RESET PASSWORD ==================-->
                <div class="tab-pane fade" id="settings" role="tabpanel">

                  <form id="register-form" action="{{ route('account.profil.reset.password') }}" method="POST">
                    @csrf
                    <div class="row">
                      <div class="col-md-12 form-group">
                        <label class="small font-weight-bold">Masukkan Password Lama</label>
                        <div class="position-relative">
                          <input type="password" class="form-control-modern pr-5" id="old-password" name="old_password" placeholder="••••••••" required>
                          <i class="fas fa-eye password-toggle-inside" id="old-password-toggle"></i>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6 form-group">
                        <label class="small font-weight-bold">Masukkan Password Baru</label>
                        <div class="position-relative">
                          <input type="password" class="form-control-modern pr-5" name="password" id="password" placeholder="••••••••" required>
                          <i class="fas fa-eye password-toggle-inside" id="password-toggle"></i>
                        </div>
                      </div>
                      <div class="col-md-6 form-group">
                        <label class="small font-weight-bold">Ulangi Password Baru</label>
                        <div class="position-relative">
                          <input type="password" class="form-control-modern pr-5" name="password_confirmation" id="password_confirmation" placeholder="••••••••" required>
                          <i class="fas fa-eye password-toggle-inside" id="password-confirmation-toggle"></i>
                        </div>
                      </div>
                    </div>

                    <div class="form-group mt-2">
                      <button type="submit" class="btn-modern btn-gradient w-100">
                        <i class="fas fa-key mr-2"></i> UPDATE PASSWORD
                      </button>
                    </div>
                  </form>
                </div>
                <!--================== END TAB RESET PASSWORD ==================-->

              </div>
            </div>
          </div>
  </section>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
  @csrf
</form>
<!--================== SWEET ALERT HARUS VERIFIKASI EMAIL DAHULU ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const button = document.getElementById('edit-jobdesk-button');
    if (button) {
      button.addEventListener('click', function(event) {
        // Check the data-action attribute to determine if the email is verified
        if (button.getAttribute('data-action') === 'verify-email') {
          event.preventDefault(); // Prevent default action
          Swal.fire({
            title: 'Harus verifikasi Email',
            text: 'Anda harus memverifikasi email Anda sebelum mengedit jobdesk Anda.',
            icon: 'warning',
            confirmButtonText: 'OK'
          });
        }
      });
    }
  });
  document.addEventListener('DOMContentLoaded', function() {
    const button = document.getElementById('edit-telp-button');
    if (button) {
      button.addEventListener('click', function(event) {
        // Check the data-action attribute to determine if the email is verified
        if (button.getAttribute('data-action') === 'verify-email') {
          event.preventDefault(); // Prevent default action
          Swal.fire({
            title: 'Harus verifikasi Email',
            text: 'Anda harus memverifikasi email Anda sebelum mengedit No Telp Anda.',
            icon: 'warning',
            confirmButtonText: 'OK'
          });
        }
      });
    }
  });
</script>
<!--================== END ==================-->

<!--================== FORMAT NO REKENING ==================-->
<script>
  function formatNoRek(input) {
    // Menghapus semua karakter non-digit
    var NoRek = input.value.replace(/\D/g, '');

    // Menggunakan ekspresi reguler untuk memformat nomor telepon
    NoRek = NoRek.replace(/(\d{4})(\d{2})(\d{6})(\d{2})(\d{1})/, '$1-$2-$3-$4-$5');

    // Mengatur nilai input dengan nomor telepon yang diformat
    input.value = NoRek;
  }
</script>
<!--================== END ==================-->

<!--================== MAKSIMAL UPLOAD GAMBAR & FILE YANG DI PERBOLEHKAN ==================-->
<script>
  document.getElementById('foto').addEventListener('change', function() {
    const fileInput = this;
    const btn = document.getElementById('updatePhotoBtn');
    const maxFileSize = 3 * 1024 * 1024; // 3MB
    const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (fileInput.files.length > 0) {
      const file = fileInput.files[0];
      const extension = file.name.split('.').pop().toLowerCase();

      // 1. Validasi Ekstensi
      if (!allowedExtensions.includes(extension)) {
        Swal.fire({
          icon: 'error',
          title: 'Format Salah',
          text: 'Hanya file JPG, JPEG, PNG, dan GIF yang diizinkan.'
        });
        fileInput.value = '';
        btn.disabled = true;
        return;
      }

      // 2. Validasi Ukuran
      if (file.size > maxFileSize) {
        Swal.fire({
          icon: 'error',
          title: 'File Terlalu Besar',
          text: 'Ukuran foto maksimal adalah 3MB.'
        });
        fileInput.value = '';
        btn.disabled = true;
        return;
      }

      // Jika lolos semua validasi, aktifkan tombol
      btn.disabled = false;
      btn.style.opacity = "1";
    } else {
      btn.disabled = true;
    }
  });
</script>
<!--================== END ==================-->

<!--================== FORMAT NO TELP ==================-->
<script>
  function formatPhoneNumber(input) {
    // Menghapus semua karakter non-digit
    var phoneNumber = input.value.replace(/\D/g, '');

    // Menentukan panjang nomor telepon
    var phoneNumberLength = phoneNumber.length;

    // Memeriksa panjang nomor telepon dan menerapkan format yang sesuai
    if (phoneNumberLength === 11) {
      phoneNumber = phoneNumber.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
    } else if (phoneNumberLength === 12) {
      phoneNumber = phoneNumber.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
    } else if (phoneNumberLength === 13) {
      phoneNumber = phoneNumber.replace(/(\d{5})(\d{4})(\d{4})/, '$1-$2-$3');
    }

    // Mengatur nilai input dengan nomor telepon yang diformat
    input.value = phoneNumber;
  }
</script>
<!--================== END ==================-->

<!--================== VERIFIKASI EMAIL (FIXED) ==================-->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const btnVerify = document.getElementById('btn-verify-email');
    const container = document.getElementById('container-verify-btn');
    const customPopup = document.getElementById('customPopup');
    const verificationForm = document.getElementById('verification-form');

    // 1. FUNGSI COUNTDOWN
    function startCountdown(duration) {
      if (!btnVerify || !container) return;

      const countdownSpan = document.createElement('span');
      countdownSpan.className = 'btn btn-warning w-100 disabled';
      countdownSpan.style.cssText = 'padding: 13px; font-size: 12px; border-radius: 14px;';

      container.replaceChild(countdownSpan, btnVerify);

      let timer = duration;
      const interval = setInterval(() => {
        countdownSpan.textContent = `Tunggu ${timer} detik`;
        localStorage.setItem('countdownRemaining', timer);
        localStorage.setItem('countdownStartDate', new Date().toISOString());

        if (--timer < 0) {
          clearInterval(interval);
          countdownSpan.replaceWith(btnVerify);
          localStorage.removeItem('countdownRemaining');
          localStorage.removeItem('countdownStartDate');
        }
      }, 1000);
    }

    // Lanjutkan countdown jika direfresh
    const savedRemaining = parseInt(localStorage.getItem('countdownRemaining')) || 0;
    if (savedRemaining > 0) {
      startCountdown(savedRemaining);
    }

    // 2. EVENT KLIK MINTA KODE KE EMAIL
    if (btnVerify) {
      btnVerify.addEventListener('click', function(e) {
        e.preventDefault();

        // Cegah klik berulang
        if (localStorage.getItem('countdownRemaining') > 0) return;

        // Mulai countdown 120 detik
        startCountdown(60);

        // Fetch ke server minta email
        fetch("{{ route('account.profil.verify.email') }}", {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.statusterkirim === 'success') {
              Swal.fire({
                title: 'Kode Terkirim!',
                text: 'Silakan periksa email Anda dan masukkan kode 6 digit.',
                icon: 'success',
                confirmButtonText: 'OK'
              }).then(() => {
                customPopup.style.display = 'block'; // Munculkan Modal Input Kode
              });
            } else {
              Swal.fire('Error', data.message || 'Gagal mengirim email.', 'error');
            }
          })
          .catch(error => {
            Swal.fire('Error', 'Terjadi kesalahan pada sistem.', 'error');
          });
      });
    }

    // 3. EVENT SUBMIT FORM DI DALAM MODAL
    if (verificationForm) {
      verificationForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const verificationCode = this.querySelector('input[name="verification_code"]').value;

        fetch(this.action, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              verification_code: verificationCode,
              _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.statusvalid === 'success') {
              Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
              }).then(() => {
                customPopup.style.display = 'none';
                window.location.reload(); // Refresh halaman agar centang email hijau
              });
            } else {
              Swal.fire('Gagal!', data.message, 'error');
            }
          })
          .catch(error => {
            Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
          });
      });
    }

    // 4. TUTUP MODAL JIKA KLIK X ATAU AREA LUAR
    const closeButton = document.getElementById('customPopupClose');
    if (closeButton) {
      closeButton.addEventListener('click', () => customPopup.style.display = 'none');
    }
    window.addEventListener('click', (event) => {
      if (event.target === customPopup) {
        customPopup.style.display = 'none';
      }
    });
  });
</script>
<!--================== END ==================-->

<!--================== RESET PASSWORD ==================-->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');

    // 1. FUNGSI TOGGLE PASSWORD (SHOW/HIDE)
    function setupPasswordToggle(inputId, toggleId) {
      const input = document.getElementById(inputId);
      const toggle = document.getElementById(toggleId);

      if (input && toggle) {
        toggle.addEventListener('click', function() {
          const isPassword = input.type === 'password';
          input.type = isPassword ? 'text' : 'password';

          // Ganti Icon
          this.classList.toggle('fa-eye');
          this.classList.toggle('fa-eye-slash');

          // Efek warna saat aktif
          this.style.color = isPassword ? '#4e73df' : '#94a3b8';
        });
      }
    }

    // Inisialisasi untuk ketiga field
    setupPasswordToggle('old-password', 'old-password-toggle');
    setupPasswordToggle('password', 'password-toggle');
    setupPasswordToggle('password_confirmation', 'password-confirmation-toggle');


    // 2. LOGIC SUBMIT FORM (VALIDASI, AJAX, & AUTO-LOGOUT)
    if (registerForm) {
      registerForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const password = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;

        // A. Validasi Client-side: Kecocokan Password Baru
        if (password !== passwordConfirmation) {
          Swal.fire({
            icon: 'error',
            title: 'Password Tidak Sesuai',
            text: 'Konfirmasi password baru tidak cocok. Silakan periksa kembali.',
            confirmButtonText: 'OK'
          });
          return;
        }

        // B. Kirim Data via Fetch API
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);

        fetch(this.action, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
          })
          .then(response => response.json())
          .then(res => {
            if (res.statuserrorreset === 'error') {
              // Jika password lama salah atau ada error validasi server
              Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: res.message,
                confirmButtonText: 'Coba Lagi'
              });
            } else if (res.statussuksesreset === 'success') {
              // Jika Berhasil -> Beri notifikasi lalu Auto-Logout
              Swal.fire({
                icon: 'success',
                title: 'Password Diperbarui!',
                text: 'Keamanan akun telah diperbarui. Silakan login kembali.',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                allowOutsideClick: false,
                willClose: () => {
                  // Eksekusi form logout tersembunyi
                  const logoutForm = document.getElementById('logout-form');
                  if (logoutForm) {
                    logoutForm.submit();
                  } else {
                    // Fallback jika form tidak ditemukan
                    window.location.href = "/logout";
                  }
                }
              });
            }
          })
          .catch(error => {
            console.error('Error:', error);
            Swal.fire({
              icon: 'error',
              title: 'System Error',
              text: 'Terjadi gangguan koneksi. Silakan coba lagi.'
            });
          });
      });
    }
  });
</script>
<!--================== END ==================-->

<!--================== POPUP EDIT DATA DIRI ==================-->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const openPopupButton = document.getElementById('openPopupButtonEmail');
    const customPopupemail = document.getElementById('customPopupEmail');
    const customPopupClose = document.getElementById('customPopupCloseEmail');

    // Show popup when the pencil icon is clicked
    openPopupButton.addEventListener('click', function() {
      customPopupemail.style.display = 'block';
    });

    // Hide popup when the close button is clicked
    customPopupClose.addEventListener('click', function() {
      customPopupemail.style.display = 'none';
    });

    // Hide popup when clicking outside the popup content
    window.addEventListener('click', function(event) {
      if (event.target === customPopupemail) {
        customPopupemail.style.display = 'none';
      }
    });
  });

  document.addEventListener('DOMContentLoaded', function() {
    const openPopupButton = document.getElementById('openPopupButtonJobdesk');
    const customPopupemail = document.getElementById('customPopupJobdesk');
    const customPopupClose = document.getElementById('customPopupCloseJobdesk');

    // Show popup when the pencil icon is clicked
    openPopupButton.addEventListener('click', function() {
      customPopupemail.style.display = 'block';
    });

    // Hide popup when the close button is clicked
    customPopupClose.addEventListener('click', function() {
      customPopupemail.style.display = 'none';
    });

    // Hide popup when clicking outside the popup content
    window.addEventListener('click', function(event) {
      if (event.target === customPopupemail) {
        customPopupemail.style.display = 'none';
      }
    });
  });

  document.addEventListener('DOMContentLoaded', function() {
    const openPopupButton = document.getElementById('openPopupButtonTelp');
    const customPopupemail = document.getElementById('customPopupTelp');
    const customPopupClose = document.getElementById('customPopupCloseTelp');

    // Show popup when the pencil icon is clicked
    openPopupButton.addEventListener('click', function() {
      customPopupemail.style.display = 'block';
    });

    // Hide popup when the close button is clicked
    customPopupClose.addEventListener('click', function() {
      customPopupemail.style.display = 'none';
    });

    // Hide popup when clicking outside the popup content
    window.addEventListener('click', function(event) {
      if (event.target === customPopupemail) {
        customPopupemail.style.display = 'none';
      }
    });
  });
</script>
<!--================== END ==================-->

<!--================== SWEET ALERT DATA PROFIL ==================-->
<script>
  // Function to show SweetAlert messages
  document.addEventListener('DOMContentLoaded', function() {
    @if(session('statusauthorized'))
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'You are not authorized to update the email',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    }).then(() => {
      location.reload(); // Automatically refresh the page after the alert
    });
    @endif

    @if(session('statusdataprofil'))
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'Data profil berhasil diperbarui',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    }).then(() => {
      location.reload(); // Automatically refresh the page after the alert
    });
    @endif

    @if(session('statusdatabank'))
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'Data bank berhasil diperbarui',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    }).then(() => {
      location.reload(); // Automatically refresh the page after the alert
    });
    @endif

    @if(session('erroremailterpakai'))
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: 'Email sudah terdaftar silahkan gunakan email yang lain',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    }).then(() => {
      location.reload(); // Automatically refresh the page after the alert
    });
    @endif
  });

  document.addEventListener('DOMContentLoaded', function() {
    @if(session('errortanggallahir'))
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: '{{ session("errortanggallahir") }}',
      showConfirmButton: true,
      confirmButtonColor: '#6366f1'
    });
    @endif
  });
</script>


<!--================== END ==================-->
@stop