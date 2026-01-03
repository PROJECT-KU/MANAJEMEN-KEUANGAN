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
Update data Trainer|
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
            <h1>Edit Data Trainer</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-body">

                    <form action="{{ route('account.clinikscopus.update', $datas->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama</label>
                                    <input type="text" id="nama" name="nama" value="{{$datas->nama}}" style="text-transform:uppercase;" placeholder="Masukkan Nama" class="form-control" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z ]/i.test(event.key)" required>

                                    @error('nama')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sesi</label>
                                    <input type="sesi" id="sesi" class="form-control" name="sesi" value="{{$datas->sesi}}" placeholder="Masukan Sesi" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>

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
                                    <input type="sesi2" id="sesi2" class="form-control" name="sesi2" value="{{$datas->sesi2}}" placeholder="Masukan Sesi" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>

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
                                    <input type="sesi3" id="sesi3" class="form-control" name="sesi3" value="{{$datas->sesi3}}" placeholder="Masukan Sesi" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>

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
                                    <input type="sesi4" id="sesi4" class="form-control" name="sesi4" value="{{$datas->sesi4}}" placeholder="Masukan Sesi" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>

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
                                    <input type="sesi5" id="sesi5" class="form-control" name="sesi5" value="{{$datas->sesi5}}" placeholder="Masukan Sesi" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>

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
                                    <input type="sesi6" id="sesi6" class="form-control" name="sesi6" value="{{$datas->sesi6}}" placeholder="Masukan Sesi" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>

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
                                    <input type="sesi7" id="sesi7" class="form-control" name="sesi7" value="{{$datas->sesi7}}" placeholder="Masukan Sesi" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>

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
                                    <input type="text" id="spesialis" name="spesialis" value="{{$datas->spesialis}}" placeholder="Masukkan Spesialis" class="form-control" maxlength="30" minlength="5" onkeypress="return/[A-Z]/i.test(event.key)" style="text-transform:uppercase" required>

                                    @error('spesialis')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <input
                                        type="date"
                                        id="tanggal"
                                        name="tanggal"
                                        value="{{ old('tanggal', \Carbon\Carbon::parse($datas->tanggal)->format('Y-m-d')) }}"
                                        class="form-control"
                                        required>

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
                                    <select class="form-control" name="status" value="{{$datas->status}}" style="height: auto;" required>
                                        <option value="" disabled selected>-- PILIH STATUS TRAINER --</option>
                                        <option value="aktif" {{ $datas->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="non aktif" {{ $datas->status == 'non aktif' ? 'selected' : '' }}>Non Aktif</option>
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
                                <div class="form-group">
                                    <label>Foto Trainer</label>
                                    <div class="input-group">
                                        <input type="file" name="foto" id="foto" class="form-control" accept="image/*" capture="camera">
                                    </div>
                                    @error('foto')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <a href="{{ asset('images/' . $datas->foto) }}" data-lightbox="{{ $datas->id }}">
                                        <div class="card" style="width: 18rem; height: 250px; overflow: hidden; border: 2px dashed #000;">
                                            @if ($datas->foto == null)
                                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                            <img id="image-preview" style="width: 100%; height: 100%; object-fit: cover; object-position: top;" class="card-img-top" src="{{ asset('images/' . $datas->foto) }}" alt="Preview Image">
                                            @endif
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex mt-3" style="gap: 10px;">
                            <button class="btn btn-primary btn-submit rounded-pill"
                                type="submit"
                                style="flex: 0 0 80%; height:35px; font-size: 15px;">
                                <i class="fa fa-paper-plane"></i> SIMPAN
                            </button>

                            <a href="{{ route('account.clinikscopus.index') }}"
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


<!--================== maksimal upload foto & jenis file yang di perbolehkan ==================-->
<script>
    document.getElementById('foto').addEventListener('change', function() {
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
<!--================== end ==================-->

<!--================== upload image ==================-->
<script>
    const imageInput = document.getElementById('foto');
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
<!--================== end ==================-->
@stop