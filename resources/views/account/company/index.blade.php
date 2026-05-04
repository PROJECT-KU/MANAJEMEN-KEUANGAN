@extends('layouts.account')
@extends('layouts.loader')
@extends('layouts.inputfitur')

@section('title')
Company Profile | MIS
@stop

<style>
    /* Desain Modern untuk Form Group */
    .form-group label {
        font-weight: 700 !important;
        color: #334155 !important;
        font-size: 13px !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    /* Styling Custom File Upload */
    .upload-container {
        border: 2px dashed #e2e8f0;
        border-radius: 20px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s ease;
        background: #f8fafc;
    }

    .upload-container:hover {
        border-color: #6366f1;
        background: #f1f5f9;
    }

    .file-upload-btn {
        cursor: pointer;
        background: #6366f1;
        color: white;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 700;
        display: inline-block;
        transition: 0.3s;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
    }

    .file-upload-btn:hover {
        background: #4f46e5;
        transform: translateY(-2px);
    }

    .preview-box {
        width: 100%;
        max-width: 200px;
        height: 200px;
        margin: 0 auto;
        border-radius: 20px;
        overflow: hidden;
        border: 5px solid #fff;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
    }

    .preview-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .section-title-inner {
        font-size: 16px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .section-title-inner i {
        margin-right: 10px;
        color: #6366f1;
    }

    .inputfile {
        display: none;
    }
</style>

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header-modern">
            <div>
                <h1>Profil Perusahaan</h1>
                <p class="text-muted font-weight-bold mb-0">
                    Kelola identitas resmi, kontak manajerial, dan operasional perusahaan dalam satu panel.
                </p>
            </div>
        </div>

        <div class="section-body">
            <form action="{{ route('account.company.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-lg-4 col-md-5 mb-4">
                        <div class="card-neo h-100">
                            <div class="card-body p-4 text-center">
                                <div class="section-title-inner justify-content-center">
                                    <i class="fas fa-image"></i> Logo Instansi
                                </div>

                                <div class="preview-box mb-4">
                                    @php
                                    $logoPath = Auth::user()->logo_company
                                    ? asset('images/' . Auth::user()->logo_company)
                                    : asset('assets/img/avatar/no-image.jpg');
                                    @endphp
                                    <img id="previewImage" src="{{ $logoPath }}" alt="Logo">
                                </div>

                                <div class="upload-container">
                                    <input type="file" name="logo_company" id="logo_company" class="inputfile" accept="image/*">
                                    <label for="logo_company" class="file-upload-btn">
                                        <i class="fas fa-cloud-upload-alt mr-2"></i> Ganti Logo
                                    </label>
                                    <small id="file-selected" class="d-block mt-3 text-muted font-italic">Format: PNG, JPG (Max 3MB)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8 col-md-7 mb-4">
                        <div class="card-neo h-100">
                            <div class="card-body p-4">
                                <div class="section-title-inner">
                                    <i class="fas fa-info-circle"></i> Informasi Operasional
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label>Nama Perusahaan</label>
                                            <input type="text" name="company" class="form-control-modern" value="{{ old('company', $user->company) }}" placeholder="Contoh: PT. Digital Warehouse">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label>Email Perusahaan</label>
                                            <input type="email" name="email_company" class="form-control-modern" value="{{ old('email_company', $user->email_company) }}" placeholder="perusahaan@email.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label>Manager Operasional</label>
                                            <input type="text" name="pj_company" class="form-control-modern" value="{{ old('pj_company', $user->pj_company) }}" placeholder="Nama Penanggung Jawab">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label>Telp / WhatsApp</label>
                                            <input type="tel" id="telp_company" name="telp_company" class="form-control-modern" value="{{ old('telp_company', $user->telp_company) }}" oninput="formatPhoneNumber(this)" placeholder="08xx-xxxx-xxxx">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Alamat Lengkap</label>
                                            <textarea name="alamat_company" class="form-control-modern" rows="4" style="height: auto !important;" placeholder="Alamat kantor pusat...">{{ old('alamat_company', $user->alamat_company) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn-modern btn-update w-100 py-3" style="border-radius: 12px; font-size: 15px;">
                                        <i class="fas fa-sync-alt"></i> UPDATE DATA
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Format Phone
    function formatPhoneNumber(input) {
        var phoneNumber = input.value.replace(/\D/g, '');
        phoneNumber = phoneNumber.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
        input.value = phoneNumber;
    }

    // Preview Image Logic
    document.getElementById('logo_company').addEventListener('change', function(event) {
        const fileInput = event.target;
        const file = fileInput.files[0];
        if (!file) return;

        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Format Salah',
                text: 'Gunakan PNG atau JPG'
            });
            fileInput.value = '';
            return;
        }

        if (file.size > 3 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'Terlalu Besar',
                text: 'Maksimal ukuran file adalah 3MB'
            });
            fileInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('file-selected').textContent = file.name;
        };
        reader.readAsDataURL(file);
    });
</script>
@stop