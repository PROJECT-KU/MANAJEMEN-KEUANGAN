@section('title')
Update Nama Trainer | MIS
@stop

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
                                    <label>Nama Karyawan</label>

                                    <select class="form-control select2" disabled>
                                        @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ $user->id == $datas->user_id ? 'selected' : '' }}>
                                            {{ $user->full_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="user_id" value="{{ $datas->user_id }}">

                                    @error('user_id')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="status" value="{{$datas->status}}" style="height: auto;">
                                        <option value="" disabled selected>-- PILIH STATUS TRAINER --</option>
                                        <option value="active" {{ $datas->status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="non active" {{ $datas->status == 'non active' ? 'selected' : '' }}>Non Active</option>
                                    </select>
                                    @error('Status')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @php
                        use Carbon\Carbon;
                        @endphp

                        <!--================== ROW 1 : SESI 1–9 ==================-->
                        <div class="row">
                            @foreach(['sesi','sesi2','sesi3','sesi4','sesi5','sesi6','sesi7','sesi8','sesi9'] as $i => $field)
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sesi {{ $i + 1 }}</label>

                                    <select name="{{ $field }}" id="{{ $field }}" class="form-control" style="height: auto;">
                                        <option value="">-- Pilih Sesi --</option>

                                        @php
                                        $start = Carbon::createFromTime(6, 0);
                                        $end = Carbon::createFromTime(23, 0);
                                        @endphp

                                        @while ($start->lt($end))
                                        @php
                                        $finish = $start->copy()->addMinutes(50);
                                        $value = $start->format('H.i').' - '.$finish->format('H.i');
                                        @endphp

                                        <option value="{{ $value }}"
                                            {{ ($datas->$field ?? '') === $value ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>

                                        @php $start->addMinutes(50); @endphp
                                        @endwhile
                                    </select>

                                    @error($field)
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', \Carbon\Carbon::parse($datas->tanggal)->format('Y-m-d')) }}">

                                    @error('tanggal')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
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
                                    <label>Spesialis</label>

                                    <div id="tag-container"
                                        data-value="{{ $datas->spesialis ?? '' }}"
                                        style="display:flex; flex-wrap:wrap; gap:6px; padding:6px; border:1px solid #ced4da; min-height:38px; cursor:text;">

                                        <input type="text" id="spesialisInput" class="border-0" placeholder="Ketik lalu tekan Enter atau ," style="outline:none; flex:1; min-width:150px;">
                                    </div>

                                    <input type="hidden" name="spesialis" id="spesialis">

                                    @error('spesialis')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
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
                                <div class="form-group">
                                    <a href="{{ asset('ClinikScopusTrainer/' . $datas->foto) }}" data-lightbox="{{ $datas->id }}">
                                        <div class="card" style="width: 18rem; height: 250px; overflow: hidden; border: 2px dashed #000;">
                                            @if ($datas->foto == null)
                                            <img alt="image" id="image-preview" src="{{ asset('ClinikScopusTrainer/no-image.jpg') }}" class="img-thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                            <img id="image-preview" style="width: 100%; height: 100%; object-fit: cover; object-position: top;" class="card-img-top" src="{{ asset('ClinikScopusTrainer/' . $datas->foto) }}" alt="Preview Image">
                                            @endif
                                        </div>
                                    </a>
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
            const selectedValues = Array.from(sessionSelects)
                .map(select => select.value)
                .filter(value => value !== '');

            sessionSelects.forEach(select => {
                Array.from(select.options).forEach(option => {

                    // Jangan disable option kosong
                    if (option.value === '') {
                        option.disabled = false;
                        return;
                    }

                    // Disable jika dipakai sesi lain
                    if (
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

        // 🔥 EVENT CHANGE
        sessionSelects.forEach(select => {
            select.addEventListener('change', updateDisabledOptions);
        });

        // 🔥 PENTING: JALANKAN SAAT LOAD (UNTUK DATA DB)
        updateDisabledOptions();
    });
</script>
<!--================== END ==================-->

<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<script>
    document.getElementById('foto').addEventListener('change', function(event) {
        const fileInput = event.target;
        const file = fileInput.files[0];

        if (!file) return;

        const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];

        const img = document.getElementById('image-preview');

        // ❌ Validasi format
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Format Tidak Valid',
                text: 'Foto harus JPG, JPEG, atau PNG',
            });
            fileInput.value = '';
            img.src = "{{ asset('ClinikScopusTrainer/no-image.jpg') }}";
            return;
        }

        // ❌ Validasi ukuran
        if (fileSizeMB > 2) {
            Swal.fire({
                icon: 'error',
                title: 'Ukuran Terlalu Besar',
                text: 'Ukuran foto maksimal 2 MB',
            });
            fileInput.value = '';
            img.src = "{{ asset('ClinikScopusTrainer/no-image.jpg') }}";
            return;
        }

        // ✅ Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result; // 🔥 INI YANG PENTING
        };
        reader.readAsDataURL(file);
    });
</script>
<!--================== END ==================-->

<!--================== TAGS SPESIALIS ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('tag-container');
        const input = document.getElementById('spesialisInput');
        const hidden = document.getElementById('spesialis');

        let tags = [];

        // ================== CREATE TAG ==================
        function createTag(text) {
            if (!text || tags.includes(text)) return;

            tags.push(text);

            const tag = document.createElement('span');
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

            tag.innerHTML = `
            ${text}
            <span style="cursor:pointer;font-weight:bold;">×</span>
        `;

            // Remove tag
            tag.querySelector('span').onclick = () => {
                tags = tags.filter(t => t !== text);
                tag.remove();
                updateHidden();
            };

            container.insertBefore(tag, input);
            updateHidden();
        }

        function updateHidden() {
            hidden.value = tags.join(',');
        }

        // ================== INPUT EVENT ==================
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                createTag(input.value.trim());
                input.value = '';
            }
        });

        // ================== LOAD DATA DB ==================
        const dbValue = container.dataset.value;
        if (dbValue) {
            dbValue.split(',').forEach(item => {
                createTag(item.trim());
            });
        }

        // Klik container fokus ke input
        container.addEventListener('click', () => input.focus());
    });
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