@extends('layouts.account')
@extends('layouts.loader')
@extends('layouts.inputfitur')

@section('title')
Tambah Karyawan | MIS
@stop

<style>
    :root {
        --accent-color: #6366f1;
        --bg-main: #f8faff;
        --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        --radius-lg: 24px;
        --radius-md: 16px;
    }


    .card-step {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: var(--radius-lg);
        box-shadow: var(--card-shadow);
        padding: 30px;
        margin-bottom: 25px;
        transition: transform 0.3s ease;
    }

    .card-title-modern {
        font-size: 16px;
        font-weight: 800;
        color: var(--accent-color);
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 25px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .form-group label {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .form-control-modern {
        display: block;
        width: 100%;
        padding: 14px 18px;
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        background-color: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        transition: all 0.3s ease;
    }

    .form-control-modern:focus {
        background-color: #fff;
        border-color: var(--accent-color);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        outline: none;
    }

    /* 🔹 Refined Switch Styling */
    .switch-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .switch-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #ffffff;
        padding: 16px 20px;
        border-radius: var(--radius-md);
        border: 2px solid #f1f5f9;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 0;
    }

    .switch-container:hover {
        border-color: var(--accent-color);
        background-color: #f5f3ff;
    }

    .switch-label-wrapper {
        display: flex;
        flex-direction: column;
    }

    .switch-label-text {
        font-size: 14px;
        font-weight: 800;
        color: #1e293b;
    }

    .switch-subtext {
        font-size: 11px;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        margin-top: 2px;
    }

    .switch-modern {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
    }

    .switch-modern input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider-modern {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e2e8f0;
        transition: .4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 34px;
    }

    .slider-modern:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 50%;
    }

    input:checked+.slider-modern {
        background-color: #22c55e;
    }

    input:checked+.slider-modern:before {
        transform: translateX(22px);
    }

    .btn-save-modern {
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: white;
        border: none;
        padding: 14px 28px;
        border-radius: 16px;
        font-weight: 700;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 10px 25px rgba(30, 41, 59, 0.2);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-save-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(99, 102, 241, 0.3);
        color: white;
    }

    @media (max-width: 767.98px) {
        .btn-desktop-only {
            display: none;
        }

        .btn-mobile-only {
            display: block;
            margin-top: 10px;
            margin-bottom: 50px;
        }
    }

    @media (min-width: 768px) {
        .btn-mobile-only {
            display: none;
        }
    }

    .section-header-modern {
        height: 113.594px;
        margin-bottom: 25px;
    }

    .section-header-modern h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -1px;
        line-height: 1.2;
    }

    .section-header-modern p {
        margin: 0px 0 0 0;
        font-size: 14px;
    }

    @media (max-width: 991.98px) {
        .section-header-modern {
            height: auto;
            padding: 20px 0;
        }

        .section-header-modern h1 {
            font-size: 24px;
        }
    }

    .section-header-modern h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -1px;
        line-height: 1.2;
        text-align: left;
    }

    .section-header-modern p {
        margin: 0px 0 0 0;
        font-size: 14px;
        text-align: left;
    }
</style>

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header-modern">
            <div>
                <h1>Tambah Data Karyawan</h1>
                <p class="text-muted font-weight-bold">Manajemen tim dan konfigurasi akun operasional.</p>
            </div>
        </div>

        <div class="section-body">
            <form action="{{ route('account.pengguna.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-7">
                        <div class="card-step">
                            <div class="card-title-modern">
                                <i class="fas fa-id-card"></i> Profil Dasar
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Lengkap <span class="badge-required">*</span></label>
                                        <input type="text" name="full_name" placeholder="NAMA LENGKAP" class="form-control-modern" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email <span class="badge-required">*</span></label>
                                        <input type="email" name="email" placeholder="email@perusahaan.com" class="form-control-modern" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kode Perusahaan <span class="badge-required">*</span></label>
                                        <input type="text" name="company" placeholder="Contoh: SCIPUS INDO" class="form-control-modern" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No. WhatsApp <span class="badge-required">*</span></label>
                                        <input type="tel" name="telp" placeholder="08xx-xxxx-xxxx" class="form-control-modern" oninput="formatPhoneNumber(this)" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-step">
                            <div class="card-title-modern">
                                <i class="fas fa-shield-alt"></i> Akses & Kepegawaian
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Level Sistem <span class="badge-required">*</span></label>
                                        <select class="form-control-modern" name="level" required>
                                            <option value="">-- Pilih Level --</option>
                                            <option value="manager">Manager Sistem</option>
                                            <option value="karyawan">Karyawan Sistem</option>
                                            <option value="staff">Staff Sistem</option>
                                            <option value="user">User Sistem</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Posisi / Jabatan <span class="badge-required">*</span></label>
                                        <select class="form-control-modern" name="jobdesk" required>
                                            <option value="">-- Pilih Jabatan --</option>
                                            <option value="MANAGER">MANAGER</option>
                                            <option value="STAFF">STAFF</option>
                                            <option value="ASISTEN TRAINER">ASISTEN TRAINER</option>
                                            <option value="KARYAWAN">KARYAWAN</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Jenis Akun <span class="badge-required">*</span></label>
                                        <select class="form-control-modern" name="jenis" required>
                                            <option value="">-- Pilih Jenis --</option>
                                            <option value="bisnis">Bisnis (Entity)</option>
                                            <option value="perorangan">Perorangan (Personal)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Username <span class="badge-required">*</span></label>
                                        <input type="text" name="username" class="form-control-modern" placeholder="Username unik" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Password Akun <span class="badge-required">*</span></label>
                                        <div style="position: relative">
                                            <input type="password" id="password" class="form-control-modern" name="password" placeholder="Min. 8 Karakter" required>
                                            <i class="fas fa-eye" id="password-toggle" style="position: absolute; right: 15px; top: 15px; cursor: pointer; color: #94a3b8;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="btn-desktop-only mt-2">
                            <button type="submit" class="btn-save-modern">
                                <i class="fas fa-save"></i> SIMPAN DATA
                            </button>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="card-step">
                            <div class="card-title-modern">
                                <i class="fas fa-university"></i> Data Finansial
                            </div>
                            <div class="form-group">
                                <label>Tanggal Lahir <span class="badge-required">*</span></label>
                                <input type="date" name="tanggal_lahir" class="form-control-modern" required>
                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Bank <span class="badge-required">*</span></label>
                                        <select class="form-control-modern" name="bank" required>
                                            <option value="" disabled selected>-- PILIH BANK --</option>
                                            <option value="002">BRI</option>
                                            <option value="008">BANK MANDIRI</option>
                                            <option value="009">BNI</option>
                                            <option value="200">BANK TABUNGAN NEGARA</option>
                                            <option value="011">BANK DANAMON</option>
                                            <option value="013">BANK PERMATA</option>
                                            <option value="014">BCA</option>
                                            <option value="016">MAYBANK</option>
                                            <option value="019">PANINBANK</option>
                                            <option value="022">CIMB NIAGA</option>
                                            <option value="023">BANK UOB INDONESIA</option>
                                            <option value="028">BANK OCBC NISP</option>
                                            <option value="087">BANK HSBC INDONESIA</option>
                                            <option value="147">BANK MUAMALAT</option>
                                            <option value="153">BANK SINARMAS</option>
                                            <option value="426">BANK MEGA</option>
                                            <option value="441">BANK BUKOPIN</option>
                                            <option value="451">BSI</option>
                                            <option value="484">BANK KEB HANA INDONESIA</option>
                                            <option value="494">BANK RAYA INDONESIA</option>
                                            <option value="506">BANK MEGA SYARIAH</option>
                                            <option value="046">BANK DBS INDONESIA</option>
                                            <option value="947">BANK ALADIN SYARIAH</option>
                                            <option value="950">BANK COMMONWEALTH</option>
                                            <option value="213">BANK BTPN</option>
                                            <option value="490">BANK NEO COMMERCE</option>
                                            <option value="501">BANK DIGITAL BCA</option>
                                            <option value="521">BANK BUKOPIN SYARIAH </option>
                                            <option value="535">SEABANK INDONESIA</option>
                                            <option value="542">BANK JAGO</option>
                                            <option value="567">ALLO BANK</option>
                                            <option value="110">BPD JAWA BARAT</option>
                                            <option value="111">BPD DKI</option>
                                            <option value="112">BPD DAERAH ISTIMEWA YOGYAKARTA</option>
                                            <option value="113">BPD JAWA TENGAH</option>
                                            <option value="114">BPD JAWA TIMUR</option>
                                            <option value="115">BPD JAMBI</option>
                                            <option value="116">BANK ACEH SYARIAH</option>
                                            <option value="117">BPD SUMATERA UTARA</option>
                                            <option value="118">BANK NAGARI</option>
                                            <option value="119">BPD RIAU KEPRI SYARIAH</option>
                                            <option value="120">BPD SUMATERA SELATAN DAN BANGKA BELITUNG</option>
                                            <option value="121">BPD LAMPUNG</option>
                                            <option value="122">BPD KALIMANTAN SELATAN</option>
                                            <option value="123">BPD KALIMANTAN BARAT</option>
                                            <option value="124">BPD KALIMANTAN TIMUR DAN KALIMANTAN UTARA</option>
                                            <option value="125">BPD KALIMANTAN TENGAH</option>
                                            <option value="126">BPD SULAWESI SELATAN DAN SULAWESI BARAT</option>
                                            <option value="127">BPD SULAWESI UTARA DAN GORONTALO</option>
                                            <option value="128">BANK NTB SYARIAH</option>
                                            <option value="129">BPD BALI</option>
                                            <option value="130">BPD NUSA TENGGARA TIMUR</option>
                                            <option value="131">BPD MALUKU DAN MALUKU UTARA</option>
                                            <option value="132">BPD PAPUA</option>
                                            <option value="133">BPD BENGKULU</option>
                                            <option value="134">BPD SULAWESI TENGAH</option>
                                            <option value="135">BPD SULAWESI TENGGARA</option>
                                            <option value="137">BPD BANTEN</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label>No. Rekening <span class="badge-required">*</span></label>
                                        <input type="text" name="norek" class="form-control-modern" placeholder="Masukkan Nomor" oninput="formatNoRek(this)" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-step">
                            <div class="card-title-modern">
                                <i class="fas fa-toggle-on"></i> Konfigurasi Akun
                            </div>
                            <div class="switch-group">
                                <label class="switch-container">
                                    <div class="switch-label-wrapper">
                                        <span class="switch-label-text">Status Aktif</span>
                                        <span class="switch-subtext">Akun dapat digunakan</span>
                                    </div>
                                    <span class="switch-modern">
                                        <input type="checkbox" name="status" checked>
                                        <span class="slider-modern"></span>
                                    </span>
                                </label>
                                <label class="switch-container">
                                    <div class="switch-label-wrapper">
                                        <span class="switch-label-text">Verifikasi</span>
                                        <span class="switch-subtext">Tandai email valid</span>
                                    </div>
                                    <span class="switch-modern">
                                        <input type="checkbox" name="email_verified_at">
                                        <span class="slider-modern"></span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="btn-mobile-only">
                            <button type="submit" class="btn-save-modern">
                                <i class="fas fa-save"></i> SIMPAN DATA
                            </button>
                        </div>

                        <div class="card-step d-none d-md-block" style="background: #eff6ff; border: 1px dashed #6366f1;">
                            <div class="card-title-modern" style="color: #1e40af; margin-bottom: 12px;">
                                <i class="fas fa-info-circle"></i> Catatan Admin
                            </div>
                            <p style="font-size: 13px; color: #475569; line-height: 1.6; margin: 0;">
                                Pastikan data diri sesuai dokumen resmi. Password harus kombinasi aman untuk sistem MIS.
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
    document.getElementById('password-toggle').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });

    function formatPhoneNumber(input) {
        let num = input.value.replace(/\D/g, '');
        if (num.length > 3 && num.length <= 7) input.value = num.replace(/(\d{4})(\d{0,4})/, '$1-$2');
        else if (num.length > 7) input.value = num.replace(/(\d{4})(\d{4})(\d{0,5})/, '$1-$2-$3');
    }

    function formatNoRek(input) {
        let num = input.value.replace(/\D/g, '');
        input.value = num.replace(/(\d{4})(?=\d)/g, '$1-');
    }
</script>
@stop