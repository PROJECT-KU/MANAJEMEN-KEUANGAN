@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Company | MIS
@stop

<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<style>
    .custom-file-upload {
        position: relative;
        overflow: hidden;
    }

    .inputfile {
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
    }

    .file-upload {
        cursor: pointer;
        display: inline-block;
        padding: 10px 20px;
        color: #fff;
        background-color: #007bff;
        border: none;
        border-radius: 5px;
        font-size: 12px;
        transition: background-color 0.3s;
    }

    .file-upload:hover {
        background-color: #0056b3;
    }

    #file-selected {
        margin-top: 5px;
        color: #888;
        font-size: 12px;
    }

    .image-preview img {
        max-width: 100%;
        height: auto;
        border-radius: 5px;
    }
</style>

<!--================== END ==================-->

@section('content')
<div class="main-content" style="font-size: 12px;">
    <section class="section">
        <div class="section-header">
            <h1>COMPANY</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-body">

                    <form action="{{ route('account.company.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nama Perusahaan</label>
                                    <input type="text" id="company" name="company" class="form-control" value="{{ old('company', $user->company) }}" class="form-control currency" maxlength="30" minlength="5" onkeypress="return/[A-Z]/i.test(event.key)" style="text-transform:uppercase">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Email Perusahaan</label>
                                    <input type="text" id="email_company" name="email_company" class="form-control" value="{{ old('email_company', $user->email_company) }}" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Manager Perusahaan</label>
                                    <input type="text" id="pj_company" name="pj_company" class="form-control" value="{{ old('pj_company', $user->pj_company) }}" maxlength="50" minlength="5" onkeypress="return/[a-zA-Z0-9., ]/i.test(event.key)">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Alamat Perusahaan</label>
                                    <textarea id="alamat_company" name="alamat_company" class="form-control" value="{{ old('alamat_company', $user->alamat_company) }}">{{ ($user->alamat_company) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Telp Perusahaan</label>
                                    <input type="tel" id="telp_company" name="telp_company" class="form-control" value="{{ old('telp_company', $user->telp_company) }}" maxlength="20" minlength="8" onkeypress="return event.charCode >= 48 && event.charCode <=57" oninput="formatPhoneNumber(this)">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group custom-file-upload">
                                    <label>Logo Perusahaan</label>
                                    <div class="input-group">
                                        <input
                                            type="file"
                                            name="logo_company"
                                            id="logo_company"
                                            class="inputfile"
                                            accept="image/*"
                                            capture="environment">
                                        <label for="logo_company" class="file-upload">
                                            <i class="fas fa-cloud-upload-alt"></i> Pilih Gambar
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div id="imagePreview" class="image-preview">
                                    @php
                                    $logoPath = Auth::user()->logo_company
                                    ? asset('images/' . Auth::user()->logo_company)
                                    : asset('assets/img/avatar/no-image.jpg');
                                    @endphp

                                    <img
                                        id="previewImage"
                                        src="{{ $logoPath }}"
                                        alt="Preview Logo"
                                        class="img-thumbnail">
                                </div>
                                <span id="file-selected" class="d-block mt-2 text-muted"></span>
                            </div>
                        </div>

                        <div class="d-flex mt-5">
                            <button class="btn btn-primary mr-1 btn-submit rounded-pill" type="submit" style="flex: 1;"><i class="fa fa-paper-plane"></i> SIMPAN</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<!--================== FORMAT TELP ==================-->
<script>
    function formatPhoneNumber(input) {
        // Menghapus semua karakter non-digit
        var phoneNumber = input.value.replace(/\D/g, '');

        // Menggunakan ekspresi reguler untuk memformat nomor telepon
        phoneNumber = phoneNumber.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');

        // Mengatur nilai input dengan nomor telepon yang diformat
        input.value = phoneNumber;
    }
</script>
<!--================== END ==================-->

<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('logo_company').addEventListener('change', function(event) {
        const fileInput = event.target;
        const file = fileInput.files[0];

        if (!file) return;

        const fileName = file.name;
        const fileSizeKB = (file.size / 1024).toFixed(2);
        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];

        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'File Tidak Valid',
                text: 'Hanya file PNG, JPG, dan JPEG yang diperbolehkan.',
            });
            fileInput.value = '';
            return;
        }

        if (file.size > 3 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'Ukuran Terlalu Besar',
                text: 'Maksimum ukuran file adalah 3MB.',
            });
            fileInput.value = '';
            return;
        }

        document.getElementById('file-selected').textContent = `${fileName} (${fileSizeKB} KB)`;

        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImage = document.getElementById('previewImage');
            previewImage.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
</script>

<!--================== END ==================-->

<!--================== BUTTON LOADER ==================-->
<script>
    $(".btn-submit").click(function() {
        $(".btn-submit").addClass('btn-progress');
        if (timeoutHandler) clearTimeout(timeoutHandler);

        timeoutHandler = setTimeout(function() {
            $(".btn-submit").removeClass('btn-progress');

        }, 1000);
    });

    $(".btn-reset").click(function() {
        $(".btn-reset").addClass('btn-progress');
        if (timeoutHandler) clearTimeout(timeoutHandler);

        timeoutHandler = setTimeout(function() {
            $(".btn-reset").removeClass('btn-progress');

        }, 500);
    })
</script>
<!--================== END ==================-->
@stop