@extends('layouts.account')
<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<style>
    .custom-file-upload {
        position: relative;
        overflow: hidden;
        margin-top: 10px;
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
        font-size: 16px;
        transition: background-color 0.3s;
    }

    .file-upload:hover {
        background-color: #0056b3;
    }

    #file-selected {
        display: block;
        margin-top: 5px;
        color: #888;
    }

    .image-preview {
        margin-top: 10px;
        display: none;
    }

    .image-preview img {
        max-width: 100%;
        height: auto;
        border-radius: 5px;
    }
</style>
<!--================== END ==================-->
@section('title')
Tambah Nama Trainer |
@stop

<style>
    .password-input {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
    }
</style>
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>TAMBAH DATA TRAINER</h1>
        </div>

        @if(session('status') === 'error')
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <b>{{ session('message') }}</b>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <div class="section-body">
            <div class="card">
                <div class="card-body">

                    <form action="{{ route('account.clinikscopus.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Karyawan</label>
                                    <select class="form-control select2" name="user_id" id="karyawanSelect" style="width: 100%" required>
                                        <option value="">-- PILIH NAMA KARYAWAN --</option>
                                        @foreach ($datas as $user)
                                        <option value="{{ $user->id }}" data-nik="{{ $user->nik }}" data-norek="{{ $user->norek }}" data-bank="{{ $user->bank }}" data-email="{{ $user->email }}">{{ $user->full_name }}</option>
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
                                    <label>Sesi</label>
                                    <input type="sesi" id="sesi" class="form-control" name="sesi" placeholder="Masukan Sesi" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>

                                    @error('Sesi')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sesi 2</label>
                                    <input type="sesi2" id="sesi2" class="form-control" name="sesi2" placeholder="Masukan Sesi" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>

                                    @error('Sesi2')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sesi 3</label>
                                    <input type="sesi3" id="sesi3" class="form-control" name="sesi3" placeholder="Masukan Sesi" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>

                                    @error('Sesi3')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sesi 4</label>
                                    <input type="sesi4" id="sesi4" class="form-control" name="sesi4" placeholder="Masukan Sesi" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>

                                    @error('Sesi4')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sesi 5</label>
                                    <input type="sesi5" id="sesi5" class="form-control" name="sesi5" placeholder="Masukan Sesi" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>

                                    @error('Sesi5')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sesi 6</label>
                                    <input type="sesi6" id="sesi6" class="form-control" name="sesi6" placeholder="Masukan Sesi" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>

                                    @error('Sesi6')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sesi 7</label>
                                    <input type="sesi7" id="sesi7" class="form-control" name="sesi7" placeholder="Masukan Sesi" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>

                                    @error('Sesi7')
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
                                    <label>Spesialis</label>
                                    <input type="text" id="spesialis" name="spesialis" placeholder="Masukkan Spesialis" class="form-control" maxlength="30" minlength="5" onkeypress="return/[A-Z]/i.test(event.key)" style="text-transform:uppercase" required>

                                    @error('spesialis')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>tanggal</label>
                                    <input type="date" id="tanggal" name="tanggal" placeholder="Masukkan tanggal" class="form-control" maxlength="30" minlength="5" onkeypress="return/[A-Z]/i.test(event.key)" style="text-transform:uppercase" required>

                                    @error('tanggal')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="status" style="height: auto;" required>
                                        <option value="" disabled selected>-- PILIH STATUS TRAINER --</option>
                                        <option value="aktif">Aktif</option>
                                        <option value="non aktif">Non Aktif</option>
                                    </select>
                                    @error('Status')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group custom-file-upload" style="margin-top: -3px;">
                                    <label>Foto Trainer</label>
                                    <div class="input-group">
                                        <input type="file" name="foto" id="foto" class="inputfile" accept="image/*">
                                        <label for="foto" class="file-upload">
                                            <i class="fas fa-cloud-upload-alt"></i> Choose Image
                                        </label>
                                    </div>
                                </div>
                                @error('foto')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <div class="image-preview-container">
                                    <div id="imagePreview" class="image-preview"></div>
                                    <span id="file-selected"></span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex mt-3" style="gap: 10px;">
                            <button class="btn btn-primary btn-submit rounded-pill"
                                type="submit"
                                style="flex: 0 0 80%; height:35px; font-size: 15px;">
                                <i class="fa fa-paper-plane"></i> SIMPAN
                            </button>

                            <a href="{{ route('account.pengguna.index') }}"
                                class="btn btn-warning rounded-pill d-flex align-items-center justify-content-center"
                                style="flex: 0 0 20%; height:35px; font-size: 15px;">
                                <i class="fa fa-undo"></i> KEMBALI
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </section>
</div>


<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('foto').addEventListener('change', function(event) {
        var fileInput = event.target;
        var file = fileInput.files[0];
        var fileName = file.name;
        var fileSize = (file.size / 1024).toFixed(2); // in KB
        var allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];

        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Only PNG, JPEG, and JPG files are allowed. Please choose a valid file type.'
            });
            return;
        }

        if (fileSize > 3000) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'File size exceeds the maximum limit of 3MB. Please choose a smaller file.'
            });
            return;
        }

        document.getElementById('file-selected').innerHTML = fileName + ' (' + fileSize + ' KB)';

        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById('imagePreview');
            output.innerHTML = `<img src="${reader.result}">`;
            output.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });
</script>
<!--================== END ==================-->
@stop