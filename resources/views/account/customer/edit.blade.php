@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Update Data Customer | MIS
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
            <h1>Profil Customer</h1>
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
                            {{ strtoupper($user->level) }}
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
                        <div>
                            <label class="text-muted small font-weight-bold">WHATSAPP / TELP</label>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold">{{ $user->telp ?? '-' }}</span>
                                <button class="btn btn-sm btn-light text-warning" id="openPopupButtonTelp"><i class="fas fa-pencil-alt"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 col-md-7">
                    <div class="card-neo">
                        <div class="card-header bg-transparent border-0 p-4">
                            <h5 class="font-weight-800 text-dark mb-0"><i class="fas fa-id-card text-primary mr-2"></i> Pengaturan Data Profil</h5>
                        </div>
                        <div class="card-body p-4 pt-0">

                            @if ($user->email_verified_at == null)
                            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4" style="border-radius: 16px;">
                                <i class="fas fa-shield-alt mr-3 fa-lg"></i>
                                <div class="font-weight-bold small">Email Anda belum diverifikasi. Harap segera verifikasi akun Anda.</div>
                            </div>
                            @endif

                            <form action="{{ route('account.pengguna.update', $user->id) }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Nama Lengkap</label>
                                        <input class="form-control-modern" type="text" name="full_name" value="{{ $user->full_name }}">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Username</label>
                                        <input class="form-control-modern" type="text" name="username" value="{{ $user->username }}">
                                    </div>
                                </div>

                                <div class="row align-items-end">
                                    <div class="{{ $user->email_verified_at ? 'col-md-12' : 'col-md-8' }} form-group">
                                        <label>Email Terdaftar </label>
                                        <input class="form-control-modern" type="text" value="{{ $user->email }}" readonly>
                                    </div>
                                    @if(!$user->email_verified_at)
                                    <div class="col-md-4 form-group">
                                        <button type="submit" form="verify-email-form" class="btn-modern btn-info w-100" style="padding: 12px; font-size: 12px;">
                                            Verifikasi Email
                                        </button>
                                    </div>
                                    @endif
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Status Akun</label>
                                        <select class="form-control-modern" name="status">
                                            <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="nonactive" {{ $user->status === 'nonactive' ? 'selected' : '' }}>Non Active</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Level Akses </label>
                                        <input class="form-control-modern" type="text" value="{{ ucfirst($user->level) }}" readonly>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Jenis Akun </label>
                                        <input class="form-control-modern" type="text" value="{{ ucfirst($user->jenis) }}" readonly>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Nomor Telepon </label>
                                        <input class="form-control-modern" type="text" value="{{ $user->telp }}" readonly>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn-modern btn-gradient w-100">
                                        <i class="fas fa-save mr-2"></i> SIMPAN SEMUA PERUBAHAN DATA
                                    </button>
                                </div>
                            </form>

                            <form id="verify-email-form" action="{{ route('account.pengguna.update.vertifikasiemail', $user->id) }}" method="POST" style="display:none;">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function togglePhotoBtn() {
        const fileInput = document.getElementById('foto');
        document.getElementById('updatePhotoBtn').disabled = !fileInput.files.length;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const setups = [{
                btn: 'openPopupButtonEmail',
                popup: 'customPopupEmail',
                close: 'customPopupCloseEmail'
            },
            {
                btn: 'openPopupButtonTelp',
                popup: 'customPopupTelp',
                close: 'customPopupCloseTelp'
            }
        ];

        setups.forEach(s => {
            const openBtn = document.getElementById(s.btn);
            const popup = document.getElementById(s.popup);
            const closeBtn = document.getElementById(s.close);

            if (openBtn) openBtn.onclick = () => popup.style.display = 'block';
            if (closeBtn) closeBtn.onclick = () => popup.style.display = 'none';
            window.addEventListener('click', (e) => {
                if (e.target === popup) popup.style.display = 'none';
            });
        });

        @if(session('statusdataprofil') || session('statusverifikasiemail'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Profil Anda telah diperbarui.',
            showConfirmButton: false,
            timer: 2000
        });
        @endif
    });

    function formatPhoneNumber(input) {
        let num = input.value.replace(/\D/g, '');
        if (num.length >= 11) num = num.replace(/(\d{4})(\d{4})(\d{2,5})/, '$1-$2-$3');
        input.value = num;
    }
</script>
@stop