@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Update Data Karyawan | MIS
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
</style>

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header-modern">
            <h1>Profil Karyawan</h1>
            <p class="text-muted font-weight-bold mb-0">Kelola identitas dan keamanan akun Anda dalam satu tempat.</p>
        </div>


        <div class="section-body">
            <div class="row">
                <div class="col-lg-4 col-md-5">
                    <div class="card-neo p-4 text-center">
                        <img src="{{ $user->gambar ? asset('assets/img/profil/' . $user->gambar) : asset('assets/img/profil/no-image.jpg') }}"
                            style="width: 130px; height: 130px; border-radius: 40px; object-fit: cover; border: 5px solid #fff; box-shadow: var(--shadow-soft);">

                        <h5 class="mt-3 font-weight-800">{{ $user->full_name }}</h5>
                        <span class="badge badge-pill badge-light text-primary font-weight-bold px-3 py-2 mb-4">
                            {{ strtoupper($user->jobdesk) }}
                        </span>

                        <form action="{{ route('account.pengguna.update.updatePhoto', $user->id) }}" method="POST" enctype="multipart/form-data" class="mt-2 text-left">
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
                                <span class="font-weight-bold">{{ $user->email }}</span>
                                <button class="btn btn-sm btn-light text-warning" id="openPopupButtonEmail"><i class="fas fa-pencil-alt"></i></button>
                            </div>
                        </div>
                        <hr style="border-top: 1px dashed #e2e8f0;">
                        <div class="mb-4">
                            <label class="text-muted small font-weight-bold">WHATSAPP / TELP</label>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold">{{ $user->telp ?? '-' }}</span>
                                <button class="btn btn-sm btn-light text-warning" id="openPopupButtonTelp"><i class="fas fa-pencil-alt"></i></button>
                            </div>
                        </div>
                        <hr style="border-top: 1px dashed #e2e8f0;">
                        <div class="mb-4">
                            <label class="text-muted small font-weight-bold">POSISI / JABATAN</label>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold">{{ $user->jobdesk ?? '-' }}</span>
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
                    </div>
                </div>

                <!--================== MODAL DATA PROFIL ==================-->
                <!-- Modal for updating email -->
                <div id="customPopupEmail" class="custom-popup">
                    <div class="custom-popup-content">
                        <span class="custom-popup-close" id="customPopupCloseEmail">&times;</span>
                        <h5 class="font-weight-800 mb-4 text-primary">Update Email</h5>
                        <form action="{{ route('account.pengguna.update.datadiri', $user->id) }}" method="POST">
                            @csrf
                            <div class="form-group mb-4">
                                <label>Masukkan Email Terbaru</label>
                                <input type="email" class="form-control-modern" name="email" value="{{ $user->email }}" required>
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
                        <form action="{{ route('account.pengguna.update.datadiri', $user->id) }}" method="POST">
                            @csrf
                            <div class="form-group mb-4">
                                <label>Masukkan Posisi / Jabatan Anda</label>
                                <select class="form-control-modern" name="jobdesk" required>
                                    <option value="">-- Pilih Jabatan --</option>
                                    <option value="MANAGER" {{ $user->jobdesk == 'MANAGER' ? 'selected' : '' }}>MANAGER</option>
                                    <option value="STAFF" {{ $user->jobdesk == 'STAFF' ? 'selected' : '' }}>STAFF</option>
                                    <option value="ASISTEN TRAINER" {{ $user->jobdesk == 'ASISTEN TRAINER' ? 'selected' : '' }}>ASISTEN TRAINER</option>
                                    <option value="KARYAWAN" {{ $user->jobdesk == 'KARYAWAN' ? 'selected' : '' }}>KARYAWAN</option>
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
                        <form action="{{ route('account.pengguna.update.datadiri', $user->id) }}" method="POST">
                            @csrf
                            <div class="form-group mb-4">
                                <label>Masukkan Nomor WhatsApp Baru</label>
                                <input type="text" class="form-control-modern" name="telp" value="{{ $user->telp }}" oninput="formatPhoneNumber(this)" required>
                            </div>
                            <button type="submit" class="btn-modern btn-gradient w-100">Simpan Nomor</button>
                        </form>
                    </div>
                </div>
                <!--================== END ==================-->

                <div class="col-lg-8 col-md-7">
                    <div class="card-neo">
                        <div class="card-header bg-transparent border-0 p-4">
                            <h5 class="font-weight-800 text-dark mb-0">
                                <i class="fas fa-id-card text-primary mr-2"></i> Pengaturan Data Profil
                            </h5>
                        </div>

                        <div class="card-body p-4 pt-0">
                            @if ($user->email_verified_at == null)
                            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4" style="border-radius: 16px; background: #fffbeb; border: 1px solid #fde68a !important;">
                                <i class="fas fa-shield-alt text-warning mr-3 fa-lg"></i>
                                <div class="font-weight-bold text-dark small">Email Anda belum diverifikasi. Harap segera verifikasi akun Anda.</div>
                            </div>
                            @endif

                            <form id="verification-form" action="{{ route('account.pengguna.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="mb-4">
                                    <h6 class="text-uppercase small font-weight-800 text-muted mb-3" style="letter-spacing: 1px;">Profil Dasar</h6>
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label class="small font-weight-bold">Nama Lengkap</label>
                                            <input class="form-control-modern" type="text" name="full_name" value="{{ $user->full_name }}">
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="small font-weight-bold">Username</label>
                                            <input class="form-control-modern" type="text" name="username" value="{{ $user->username }}">
                                        </div>
                                    </div>

                                    <div class="row align-items-end">
                                        <div class="{{ $user->email_verified_at ? 'col-md-12' : 'col-md-8' }} form-group">
                                            <label class="small font-weight-bold">Email Terdaftar</label>
                                            <input class="form-control-modern bg-light" type="text" value="{{ $user->email }}" readonly style="cursor: not-allowed;">
                                        </div>
                                        @if(!$user->email_verified_at)
                                        <div class="col-md-4 form-group">
                                            <button type="submit" form="verify-email-form" class="btn-modern btn-info w-100" style="padding: 13px; font-size: 12px; border-radius: 14px;">
                                                <i class="fas fa-check-circle mr-1"></i> Verifikasi Email
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <hr class="my-4" style="border-top: 1px dashed #e2e8f0;">

                                <div class="mb-4">
                                    <h6 class="text-uppercase small font-weight-800 text-muted mb-3" style="letter-spacing: 1px;">Akses & Kepegawaian</h6>
                                    <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label class="small font-weight-bold">Status Akun</label>
                                            <select class="form-control-modern" name="status">
                                                <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="nonactive" {{ $user->status === 'nonactive' ? 'selected' : '' }}>Non Active</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label class="small font-weight-bold">Level Sistem</label>
                                            <select class="form-control-modern" name="level">
                                                <option value="">-- Pilih Level --</option>
                                                <option value="manager" {{ $user->level == 'manager' ? 'selected' : '' }}>Manager Sistem</option>
                                                <option value="karyawan" {{ $user->level == 'karyawan' ? 'selected' : '' }}>Karyawan Sistem</option>
                                                <option value="staff" {{ $user->level == 'staff' ? 'selected' : '' }}>Staff Sistem</option>
                                                <option value="user" {{ $user->level == 'user' ? 'selected' : '' }}>User Sistem</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label class="small font-weight-bold">Kode Perusahaan</label>
                                            <input class="form-control-modern" type="text" value="{{ $user->company }}" readonly>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 form-group">
                                            <label class="small font-weight-bold">Jenis Akun</label>
                                            <select class="form-control-modern" name="jenis">
                                                <option value="">-- Pilih Jenis --</option>
                                                <option value="bisnis" {{ $user->jenis == 'bisnis' ? 'selected' : '' }}>Bisnis (Entity)</option>
                                                <option value="perorangan" {{ $user->jenis == 'perorangan' ? 'selected' : '' }}>Perorangan (Personal)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4" style="border-top: 1px dashed #e2e8f0;">

                                <div class="mb-2">
                                    <h6 class="text-uppercase small font-weight-800 text-muted mb-3" style="letter-spacing: 1px;">Data Finansial & Pribadi</h6>
                                    <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label class="small font-weight-bold">Tanggal Lahir</label>
                                            <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control-modern" value="{{ old('tanggal_lahir', $user->tanggal_lahir) }}">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label class="small font-weight-bold">No Rekening</label>
                                            <input type="text" id="norek" name="norek" class="form-control-modern" value="{{ old('norek', $user->norek) }}" placeholder="Nomor Rekening" maxlength="40" onkeypress="return event.charCode >= 48 && event.charCode <=57" oninput="formatNoRek(this)">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label class="small font-weight-bold">Bank</label>
                                            <select class="form-control-modern bank" id="bank" name="bank" style="height:max-content">
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

                                <div class="mt-4">
                                    <button type="submit" class="btn-modern btn-gradient w-100">
                                        <i class="fas fa-sync-alt"></i> UPDATE DATA
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <form id="verify-email-form" action="{{ route('account.pengguna.update.vertifikasiemail', $user->id) }}" method="POST" style="display:none;">
                    @csrf
                </form>

            </div>
        </div>
    </section>
</div>


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

<!--================== FOTO PROFIL ==================-->
<script>
    function togglePhotoBtn() {
        var fileInput = document.getElementById('foto');
        var submitButton = document.getElementById('updatePhotoBtn'); // Sesuaikan dengan ID di HTML

        // Jika ada file yang dipilih, tombol aktif (disabled = false)
        if (fileInput.files && fileInput.files.length > 0) {
            submitButton.disabled = false;
        } else {
            submitButton.disabled = true;
        }
    }
</script>
<!--================== END ==================-->

<!--================== MAKSIMAL UPLOAD GAMBAR & FILE YANG DI PERBOLEHKAN ==================-->
<script>
    document.getElementById('foto').addEventListener('change', function() {
        const maxFileSizeInBytes = 3 * 1024 * 1024;
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
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
                    text: 'Ukuran File Yang Diperbolehkan Dibawah 3MB.',
                    showConfirmButton: false,
                    timer: 2000,
                    // timerProgressBar: true
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
                    text: 'Hanya File JPG, JPEG, PNG, dan GIF Yang Diperbolehkan.',
                    showConfirmButton: false,
                    timer: 2000,
                    // timerProgressBar: true
                });
                fileInput.value = ''; // Clear the file input
            }
        }
    });
</script>
<!--================== END ==================-->

<!--================== DATA PROFIL ==================-->
<script>
    // Function to show SweetAlert messages
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('statusauthorized'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'You are not authorized to update the email',
            showConfirmButton: false,
            timer: 2000,
            // timerProgressBar: true
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
            timer: 2000,
            // timerProgressBar: true
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
            timer: 2000,
            // timerProgressBar: true
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
            timer: 2000,
            // timerProgressBar: true
        }).then(() => {
            location.reload(); // Automatically refresh the page after the alert
        });
        @endif

        @if(session('statusverifikasiemail'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Email berhasil di verifikasi',
            showConfirmButton: false,
            timer: 2000,
            // timerProgressBar: true
        }).then(() => {
            location.reload(); // Automatically refresh the page after the alert
        });
        @endif
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
@stop