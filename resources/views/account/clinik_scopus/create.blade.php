@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Clinik Scopus Create Trainer | MIS
@stop

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
    }

    .image-preview img {
        max-width: 100%;
        height: auto;
        border-radius: 5px;
    }
</style>
<!--================== END ==================-->

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>TAMBAH DATA TRAINER</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-body">

                    <form id="trainerForm" action="{{ route('account.clinikscopus.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Karyawan <span style="color: red;">*</span></label>
                                    <select class="form-control select2" name="user_id" id="karyawanSelect" style="width: 100%" required>
                                        <option value="">-- PILIH NAMA KARYAWAN --</option>
                                        @foreach ($datas as $user)
                                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
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
                                    <label>Status <span style="color: red;">*</span></label>
                                    <select class="form-control" name="status" style="height: auto;" required>
                                        <option value="" disabled selected>-- PILIH STATUS TRAINER --</option>
                                        <option value="active">Active</option>
                                        <option value="non active">Non Active</option>
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sesi 1</label>
                                    <select name="sesi" id="sesi" class="form-control" style="height: auto;">
                                        <option value="">-- Pilih Sesi --</option>
                                        @php
                                        $start = \Carbon\Carbon::createFromTime(6, 0);
                                        $end = \Carbon\Carbon::createFromTime(23, 0);
                                        @endphp

                                        @while ($start->lt($end))
                                        @php
                                        $finish = $start->copy()->addMinutes(50);
                                        @endphp

                                        <option value="{{ $start->format('H.i') }} - {{ $finish->format('H.i') }}">
                                            {{ $start->format('H.i') }} - {{ $finish->format('H.i') }}
                                        </option>

                                        @php
                                        $start->addMinutes(50);
                                        @endphp
                                        @endwhile
                                    </select>

                                    @error('sesi')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sesi 2</label>
                                    <select name="sesi2" id="sesi2" class="form-control" style="height: auto;">
                                        <option value="">-- Pilih Sesi --</option>
                                        @php
                                        $start = \Carbon\Carbon::createFromTime(6, 0);
                                        $end = \Carbon\Carbon::createFromTime(23, 0);
                                        @endphp

                                        @while ($start->lt($end))
                                        @php
                                        $finish = $start->copy()->addMinutes(50);
                                        @endphp

                                        <option value="{{ $start->format('H.i') }} - {{ $finish->format('H.i') }}">
                                            {{ $start->format('H.i') }} - {{ $finish->format('H.i') }}
                                        </option>

                                        @php
                                        $start->addMinutes(50);
                                        @endphp
                                        @endwhile
                                    </select>

                                    @error('sesi2')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sesi 3</label>
                                    <select name="sesi3" id="sesi3" class="form-control" style="height: auto;">
                                        <option value="">-- Pilih Sesi --</option>
                                        @php
                                        $start = \Carbon\Carbon::createFromTime(6, 0);
                                        $end = \Carbon\Carbon::createFromTime(23, 0);
                                        @endphp

                                        @while ($start->lt($end))
                                        @php
                                        $finish = $start->copy()->addMinutes(50);
                                        @endphp

                                        <option value="{{ $start->format('H.i') }} - {{ $finish->format('H.i') }}">
                                            {{ $start->format('H.i') }} - {{ $finish->format('H.i') }}
                                        </option>

                                        @php
                                        $start->addMinutes(50);
                                        @endphp
                                        @endwhile
                                    </select>

                                    @error('sesi3')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sesi 4</label>
                                    <select name="sesi4" id="sesi4" class="form-control" style="height: auto;">
                                        <option value="">-- Pilih Sesi --</option>
                                        @php
                                        $start = \Carbon\Carbon::createFromTime(6, 0);
                                        $end = \Carbon\Carbon::createFromTime(23, 0);
                                        @endphp

                                        @while ($start->lt($end))
                                        @php
                                        $finish = $start->copy()->addMinutes(50);
                                        @endphp

                                        <option value="{{ $start->format('H.i') }} - {{ $finish->format('H.i') }}">
                                            {{ $start->format('H.i') }} - {{ $finish->format('H.i') }}
                                        </option>

                                        @php
                                        $start->addMinutes(50);
                                        @endphp
                                        @endwhile
                                    </select>

                                    @error('sesi4')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sesi 5</label>
                                    <select name="sesi5" id="sesi5" class="form-control" style="height: auto;">
                                        <option value="">-- Pilih Sesi --</option>
                                        @php
                                        $start = \Carbon\Carbon::createFromTime(6, 0);
                                        $end = \Carbon\Carbon::createFromTime(23, 0);
                                        @endphp

                                        @while ($start->lt($end))
                                        @php
                                        $finish = $start->copy()->addMinutes(50);
                                        @endphp

                                        <option value="{{ $start->format('H.i') }} - {{ $finish->format('H.i') }}">
                                            {{ $start->format('H.i') }} - {{ $finish->format('H.i') }}
                                        </option>

                                        @php
                                        $start->addMinutes(50);
                                        @endphp
                                        @endwhile
                                    </select>

                                    @error('sesi5')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sesi 6</label>
                                    <select name="sesi6" id="sesi6" class="form-control" style="height: auto;">
                                        <option value="">-- Pilih Sesi --</option>
                                        @php
                                        $start = \Carbon\Carbon::createFromTime(6, 0);
                                        $end = \Carbon\Carbon::createFromTime(23, 0);
                                        @endphp

                                        @while ($start->lt($end))
                                        @php
                                        $finish = $start->copy()->addMinutes(50);
                                        @endphp

                                        <option value="{{ $start->format('H.i') }} - {{ $finish->format('H.i') }}">
                                            {{ $start->format('H.i') }} - {{ $finish->format('H.i') }}
                                        </option>

                                        @php
                                        $start->addMinutes(50);
                                        @endphp
                                        @endwhile
                                    </select>

                                    @error('sesi6')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sesi 7</label>
                                    <select name="sesi7" id="sesi7" class="form-control" style="height: auto;">
                                        <option value="">-- Pilih Sesi --</option>
                                        @php
                                        $start = \Carbon\Carbon::createFromTime(6, 0);
                                        $end = \Carbon\Carbon::createFromTime(23, 0);
                                        @endphp

                                        @while ($start->lt($end))
                                        @php
                                        $finish = $start->copy()->addMinutes(50);
                                        @endphp

                                        <option value="{{ $start->format('H.i') }} - {{ $finish->format('H.i') }}">
                                            {{ $start->format('H.i') }} - {{ $finish->format('H.i') }}
                                        </option>

                                        @php
                                        $start->addMinutes(50);
                                        @endphp
                                        @endwhile
                                    </select>

                                    @error('sesi7')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sesi 8</label>
                                    <select name="sesi8" id="sesi8" class="form-control" style="height: auto;">
                                        <option value="">-- Pilih Sesi --</option>
                                        @php
                                        $start = \Carbon\Carbon::createFromTime(6, 0);
                                        $end = \Carbon\Carbon::createFromTime(23, 0);
                                        @endphp

                                        @while ($start->lt($end))
                                        @php
                                        $finish = $start->copy()->addMinutes(50);
                                        @endphp

                                        <option value="{{ $start->format('H.i') }} - {{ $finish->format('H.i') }}">
                                            {{ $start->format('H.i') }} - {{ $finish->format('H.i') }}
                                        </option>

                                        @php
                                        $start->addMinutes(50);
                                        @endphp
                                        @endwhile
                                    </select>

                                    @error('sesi8')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sesi 9</label>
                                    <select name="sesi9" id="sesi9" class="form-control" style="height: auto;">
                                        <option value="">-- Pilih Sesi --</option>
                                        @php
                                        $start = \Carbon\Carbon::createFromTime(6, 0);
                                        $end = \Carbon\Carbon::createFromTime(23, 0);
                                        @endphp

                                        @while ($start->lt($end))
                                        @php
                                        $finish = $start->copy()->addMinutes(50);
                                        @endphp

                                        <option value="{{ $start->format('H.i') }} - {{ $finish->format('H.i') }}">
                                            {{ $start->format('H.i') }} - {{ $finish->format('H.i') }}
                                        </option>

                                        @php
                                        $start->addMinutes(50);
                                        @endphp
                                        @endwhile
                                    </select>

                                    @error('sesi9')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal Online <span style="color: red;">*</span></label>
                                    <input type="date" id="tanggal_online" name="tanggal_online" placeholder="Masukkan tanggal_online" class="form-control" required>

                                    @error('tanggal_online')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal Offline <span style="color: red;">*</span></label>
                                    <input type="date" id="tanggal_offline" name="tanggal_offline" placeholder="Masukkan tanggal_offline" class="form-control" required>

                                    @error('tanggal_offline')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Biaya Per Sesi</label>

                                    <input type="text"
                                        id="biaya_persesi"
                                        class="form-control"
                                        value="{{ $biayaPersesiAktif ? 'Rp ' . number_format($biayaPersesiAktif->biaya_persesi, 0, ',', '.') : '-' }}"
                                        readonly>

                                    <!-- ID disimpan hidden -->
                                    <input type="hidden"
                                        name="biaya_persesi_id"
                                        value="{{ $biayaPersesiAktif->id ?? '' }}">

                                    @error('biaya_persesi_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Spesialis <span style="color:red">*</span></label>

                                    <!-- Container Tag -->
                                    <div id="tag-container"
                                        style="display:flex; flex-wrap:wrap; gap:6px; padding:6px; border:1px solid #ced4da; min-height:38px; cursor:text;">

                                        <!-- Input -->
                                        <input
                                            type="text"
                                            id="spesialisInput"
                                            class="border-0"
                                            placeholder="Ketik lalu tekan Enter atau ,"
                                            style="outline:none; flex:1; min-width:150px;">
                                    </div>

                                    <!-- Hidden input untuk dikirim ke backend -->
                                    <input type="hidden" name="spesialis" id="spesialis">

                                    @error('spesialis')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group custom-file-upload" style="margin-top: -3px;">
                                    <label>Foto Trainer <span style="color:red">*</span></label>
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
                                    <div id="imagePreview" class="image-preview">
                                        <img src="{{ asset('ClinikScopusTrainer/no-image.jpg') }}" alt="No Image">
                                    </div>
                                    <span id="file-selected"></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="d-flex flex-md-nowrap flex-wrap gap-2 mt-4">

                                <!-- Tombol Simpan -->
                                <button type="submit"
                                    class="btn btn-primary btn-submit rounded-pill w-100 w-md-auto mb-2 mb-md-0">
                                    <i class="fa fa-paper-plane"></i> SIMPAN
                                </button>

                                <!-- Tombol Kembali -->
                                <a href="{{ route('account.clinikscopus.index') }}"
                                    class="btn btn-warning btn-submit rounded-pill w-100 w-md-auto mb-2 mb-md-0">
                                    <i class="fa fa-undo"></i> KEMBALI
                                </a>

                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<!--================== TIDAK BOLEH MEMILIH JAM YANG SAMA ANTAR SESI ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sessionSelects = document.querySelectorAll(
            '#sesi, #sesi2, #sesi3, #sesi4, #sesi5, #sesi6, #sesi7, #sesi8, #sesi9'
        );

        function updateDisabledOptions() {
            // Ambil semua value yang dipilih
            const selectedValues = Array.from(sessionSelects)
                .map(select => select.value)
                .filter(value => value !== '');

            sessionSelects.forEach(select => {
                Array.from(select.options).forEach(option => {
                    if (
                        option.value !== '' &&
                        selectedValues.includes(option.value) &&
                        option.value !== select.value
                    ) {
                        option.disabled = true;
                    } else {
                        option.disabled = false;
                    }
                });
            });
        }

        sessionSelects.forEach(select => {
            select.addEventListener('change', updateDisabledOptions);
        });
    });
</script>
<!--================== END ==================-->

<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<script>
    document.getElementById('foto').addEventListener('change', function(event) {
        const fileInput = event.target;
        const file = fileInput.files[0];

        if (!file) return;

        const fileName = file.name;
        const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];

        const preview = document.getElementById('imagePreview');
        const img = preview.querySelector('img');
        const fileInfo = document.getElementById('file-selected');

        // Reset info
        fileInfo.innerHTML = '';

        // ❌ Validasi format
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Format Tidak Valid',
                text: 'Foto harus berformat JPG, JPEG, atau PNG',
                confirmButtonColor: '#d33'
            });

            fileInput.value = '';
            img.src = "{{ asset('ClinikScopusTrainer/no-image.jpg') }}";
            return;
        }

        // ❌ Validasi ukuran (max 2 MB)
        if (fileSizeMB > 2) {
            Swal.fire({
                icon: 'error',
                title: 'Ukuran Terlalu Besar',
                text: 'Ukuran foto maksimal 2 MB',
                confirmButtonColor: '#d33'
            });

            fileInput.value = '';
            img.src = "{{ asset('ClinikScopusTrainer/no-image.jpg') }}";
            return;
        }

        // ✅ Info file
        fileInfo.innerHTML = `${fileName} (${fileSizeMB} MB)`;

        // ✅ Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
</script>

<!-- wajib upload gambar -->
<script>
    document.getElementById('trainerForm').addEventListener('submit', function(e) {
        const fotoInput = document.getElementById('foto');

        // Jika belum upload foto
        if (!fotoInput.files || fotoInput.files.length === 0) {
            e.preventDefault(); // ❌ STOP submit form

            Swal.fire({
                icon: 'warning',
                title: 'Foto Wajib Diunggah',
                text: 'Silakan upload foto trainer terlebih dahulu',
                confirmButtonColor: '#3085d6'
            });

            return false;
        }
    });
</script>
<!--================== END ==================-->

<!--================== TAGS SPESIALIS ==================-->
<script>
    const input = document.getElementById('spesialisInput');
    const container = document.getElementById('tag-container');
    const hiddenInput = document.getElementById('spesialis');

    let tags = [];

    function updateHiddenInput() {
        hiddenInput.value = tags.join(', ');
    }

    function createTag(label) {
        const tag = document.createElement('span');
        tag.textContent = label;
        tag.style.cssText = `
        background:#0d6efd;
        color:#fff;
        padding:4px 8px;
        border-radius:12px;
        font-size:13px;
        display:flex;
        align-items:center;
        gap:6px;
    `;

        const closeBtn = document.createElement('span');
        closeBtn.textContent = '×';
        closeBtn.style.cursor = 'pointer';

        closeBtn.onclick = () => {
            tags = tags.filter(t => t !== label);
            tag.remove();
            updateHiddenInput();
        };

        tag.appendChild(closeBtn);
        container.insertBefore(tag, input);
    }

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();

            const value = input.value.trim().replace(',', '');
            if (value && !tags.includes(value)) {
                tags.push(value);
                createTag(value);
                updateHiddenInput();
            }
            input.value = '';
        }
    });

    // klik container → fokus input
    container.addEventListener('click', () => input.focus());
</script>
<!--================== END ==================-->

<!--================== FORMAT BIAYA SESI ==================-->
<script>
    const biayaInput = document.getElementById('biaya_persesi');

    biayaInput.addEventListener('input', function(e) {
        // Hapus semua karakter selain angka
        let value = this.value.replace(/\D/g, '');

        if (value) {
            // Format dengan ribuan
            value = parseInt(value, 10).toLocaleString('id-ID');
            this.value = 'Rp ' + value;
        } else {
            this.value = '';
        }
    });
</script>
<!--================== END ==================-->
@stop