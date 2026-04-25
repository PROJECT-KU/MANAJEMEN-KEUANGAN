@extends('layouts.account')
@extends('layouts.loader')
@extends('layouts.inputfitur')

@section('title')
Update Data Trainer | MIS
@stop

<style>
    /* 🔹 Base Card Modern */
    .card-modern {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .card-modern:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
    }

    /* 🔹 Header Custom di dalam Card */
    .card-header-custom {

        display: flex;
        align-items: center;
        border-bottom: 1px solid #f1f5f9;
    }

    .card-header-custom h6 {
        font-size: 15px;
        letter-spacing: -0.5px;
        color: #1e293b;
    }

    /* 🔹 Sesi Item Styling */
    .sesi-item {
        transition: all 0.2s ease;
    }

    .sesi-item:focus-within {
        border-color: #6366f1 !important;
        /* Warna ungu indigo modern */
        background: #ffffff !important;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.08);
    }

    /* 🔹 Tag Spesialis Modern */
    .badge-spesialis {
        background: #6366f1;
        color: white;
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* 🔹 Button Styling */
    .btn-primary.shadow-lg {
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2) !important;
    }

    /* Lingkaran Icon Timeline */
    .date-icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 16px;
    }

    /* Input Modern yang tidak akan meluber */
    .form-control-modern-date-fixed {
        display: block;
        width: 100%;
        max-width: 100%;
        /* Kunci agar tidak keluar card */
        padding: 10px 15px;
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        height: 45px;
        transition: all 0.3s ease;
    }

    .form-control-modern-date-fixed:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        outline: none;
    }

    /* Badge Status */
    .status-badge-modern {
        display: inline-block;
        padding: 8px 20px;
        background: #ffffff;
        border-radius: 50px;
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
    }

    /* Penyesuaian Border di Mobile */
    .timeline-container {
        position: relative;
    }

    /* Garis Timeline yang Pintar */
    .timeline-line {
        position: absolute;
        /* 44px didapat dari: Padding container (24px) + setengah lebar icon (20px) */
        left: 38px;
        top: 60px;
        bottom: 60px;
        border-left: 2px dashed #cbd5e1;
        z-index: 1;
    }

    /* Penyesuaian di Mobile (HP) */
    @media (max-width: 767px) {
        .timeline-line {
            /* Di mobile padding p-4 biasanya tetap 24px, 
           tapi karena icon mengecil jadi 35px, maka setengahnya 17.5px.
           24px + 17.5px = 41.5px */
            left: 38px;
            top: 55px;
            bottom: 55px;
        }

        /* Pastikan garis tetap muncul di mobile */
        .d-md-block {
            display: block !important;
        }
    }
</style>

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header-modern mb-4">
            <div>
                <h1>Update Data Trainer</h1>
                <p class="text-muted font-weight-bold mb-0">Perbarui informasi spesialisasi, ketersediaan jadwal, dan tarif bimbingan trainer Scopus.</p>
            </div>
        </div>

        <div class="section-body">
            <form action="{{ route('account.clinikscopus.update', $datas->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-lg-8">

                        <div class="card-modern mb-4">
                            <div class="card-header-custom p-4 border-bottom">
                                <h6 class="m-0 font-weight-bold"><i class="fas fa-user text-primary mr-2"></i> Identitas & Status</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="form-group mb-0">
                                            <label class="text-muted">Nama Karyawan</label>
                                            <input type="text" class="form-control-modern" value="{{ $datas->user->full_name ?? '' }}" readonly style="background-color: #f1f5f9; cursor: not-allowed; color: #64748b; font-weight: 600;">
                                            <input type="hidden" name="user_id" value="{{ $datas->user_id }}">
                                        </div>
                                    </div>

                                    <div class="col-md-5 mt-4 mt-md-0">
                                        <div class="form-group mb-0">
                                            <label>Status</label>
                                            <select class="form-control-modern" name="status" value="{{$datas->status}}">
                                                <option value="" disabled selected>-- PILIH STATUS TRAINER --</option>
                                                <option value="active" {{ $datas->status == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="non active" {{ $datas->status == 'non active' ? 'selected' : '' }}>Non Active</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-modern mb-4">
                            <div class="card-header-custom p-4 border-bottom">
                                <h6 class="m-0 font-weight-bold"><i class="fas fa-calendar-alt text-primary mr-2"></i> Jadwal Sesi Bimbingan</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    @for ($i = 1; $i <= 9; $i++)
                                        <div class="col-md-4 mb-3">
                                        <div class="sesi-item p-3" style="background: #f8fafc; border-radius: 15px; border: 1px solid #e2e8f0;">
                                            <label class="small font-weight-bold text-primary mb-1 d-block">Sesi {{ $i }}</label>

                                            @php
                                            $fieldName = ($i == 1) ? 'sesi' : 'sesi'.$i;
                                            // Ambil nilai jam dari database untuk sesi ini
                                            $dbValue = $datas->$fieldName;
                                            @endphp

                                            <select name="{{ $fieldName }}" id="{{ $fieldName }}" class="form-control border-0 bg-transparent p-0" style="height: auto;">
                                                <option value="">-- Kosong --</option>

                                                @php
                                                $start = \Carbon\Carbon::createFromTime(6, 0);
                                                $end = \Carbon\Carbon::createFromTime(23, 0);
                                                @endphp

                                                @while ($start->lt($end))
                                                @php
                                                $finish = $start->copy()->addMinutes(50);
                                                $timeRange = $start->format('H.i') . ' - ' . $finish->format('H.i');
                                                @endphp

                                                <option value="{{ $timeRange }}" {{ $dbValue == $timeRange ? 'selected' : '' }}>
                                                    {{ $timeRange }}
                                                </option>

                                                @php $start->addMinutes(50); @endphp
                                                @endwhile
                                            </select>
                                        </div>
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>

                    <div class="card-modern mb-4">
                        <div class="card-header-custom p-4 border-bottom">
                            <h6 class="m-0 font-weight-bold"><i class="fas fa-brain text-primary mr-2"></i> Keahlian Spesialis</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="form-group mb-0">
                                <label>Input Spesialis</label>
                                <div id="tag-container" class="form-control-modern d-flex flex-wrap align-items-center"
                                    data-initial="{{ $datas->spesialis ?? '' }}"
                                    style="min-height: 50px; gap: 8px; cursor: text;">

                                    <input type="text" id="spesialisInput" class="border-0 flex-grow-1"
                                        style="outline:none; min-width: 200px; background: transparent;"
                                        placeholder="Ketik keahlian lalu Enter...">
                                </div>
                                <input type="hidden" name="spesialis" id="spesialis" value="{{ $datas->spesialis ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="card-modern mb-4">
                        <div class="card-header-custom p-4 border-bottom">
                            <h6 class="m-0 font-weight-bold"><i class="fas fa-file-invoice-dollar text-primary mr-2"></i> Tarif & Masa Aktif</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row align-items-stretch">

                                <div class="col-md-6 border-right">
                                    <div class="form-group mb-0 h-100 d-flex flex-column">
                                        <label>Biaya Per Sesi Aktif </label>

                                        <div class="position-relative overflow-hidden p-4 text-center d-flex flex-column justify-content-center flex-grow-1"
                                            style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border-radius: 25px; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2); min-height: 200px;">

                                            <i class="fas fa-wallet position-absolute"
                                                style="font-size: 80px; right: -15px; bottom: -15px; color: rgba(255,255,255,0.1); transform: rotate(-15deg);"></i>

                                            <div class="position-relative" style="z-index: 2;">
                                                <span class="text-white-50 small d-block font-weight-bold mb-1">Tarif Layanan</span>
                                                <h3 class="mb-0 text-white font-weight-bold" style="letter-spacing: -1px; font-size: 28px;">
                                                    {{ $biayaPersesiAktif ? 'Rp ' . number_format($biayaPersesiAktif->biaya_persesi, 0, ',', '.') : '-' }}
                                                </h3>
                                                <div class="mt-2">
                                                    <span class="badge badge-pill d-inline-flex align-items-center"
                                                        style="background: rgba(255,255,255,0.2); color: #fff; font-size: 11px; padding: 5px 12px; line-height: 1;">
                                                        <i class="fas fa-check-circle mr-1" style="font-size: 12px;"></i>
                                                        <span>Terkonfigurasi di Sistem</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <input type="hidden" name="biaya_persesi_id" value="{{ $biayaPersesiAktif->id ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-0 h-100 d-flex flex-column">
                                        <label class="mt-4 mt-md-0">Masa Aktif Online</label>

                                        <div class="p-4 timeline-container flex-grow-1 d-flex flex-column justify-content-center"
                                            style="background: #f8fafc; border-radius: 25px; border: 1px solid #e2e8f0;">

                                            <div class="timeline-line"></div>

                                            <div class="d-flex align-items-center mb-4" style="position: relative; z-index: 2;">
                                                <div class="date-icon-circle bg-primary shadow-sm">
                                                    <i class="fas fa-calendar-check text-white"></i>
                                                </div>
                                                <div class="flex-grow-1 ml-3 text-left">
                                                    <span class="d-block small text-muted font-weight-bold mb-1">Mulai Online</span>
                                                    <input type="date"
                                                        name="tanggal_online"
                                                        class="form-control-modern-date-fixed"
                                                        value="{{ $datas->tanggal_online ? \Carbon\Carbon::parse($datas->tanggal_online)->format('Y-m-d') : '' }}"
                                                        required>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center" style="position: relative; z-index: 2;">
                                                <div class="date-icon-circle bg-danger shadow-sm">
                                                    <i class="fas fa-calendar-times text-white"></i>
                                                </div>
                                                <div class="flex-grow-1 ml-3 text-left">
                                                    <span class="d-block small text-muted font-weight-bold mb-1">Selesai Offline</span>
                                                    <input type="date"
                                                        name="tanggal_offline"
                                                        class="form-control-modern-date-fixed"
                                                        value="{{ $datas->tanggal_offline ? \Carbon\Carbon::parse($datas->tanggal_offline)->format('Y-m-d') : '' }}"
                                                        required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card-modern sticky-top" style="top: 100px; z-index: 10;">
                        <div class="card-header-custom p-4 border-bottom">
                            <h6 class="m-0 font-weight-bold"><i class="fas fa-camera text-primary mr-2"></i> Foto Profil</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="image-preview-container d-flex flex-column align-items-center justify-content-center p-3"
                                style="background: #f1f5f9;">

                                <div id="imagePreview" class="image-preview mb-4 w-100 d-flex justify-content-center">
                                    @php
                                    $imageSrc = (empty($datas->foto) || !file_exists(public_path('ClinikScopusTrainer/' . $datas->foto)))
                                    ? asset('ClinikScopusTrainer/no-image.jpg')
                                    : asset('ClinikScopusTrainer/' . $datas->foto);
                                    @endphp

                                    <img id="image-preview"
                                        src="{{ $imageSrc }}"
                                        alt="Preview"
                                        style="width: 100%; min-height: 350px; max-height: 600px; object-fit: cover; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                                </div>

                                <input type="file" name="foto" id="foto" class="inputfile" accept="image/*" style="display:none;">
                                <label for="foto" class="btn btn-dark btn-block btn-lg rounded-pill py-3">
                                    <i class="fas fa-upload mr-2"></i> Pilih Foto Trainer
                                </label>
                                <span id="file-selected" class="small text-primary d-block text-center font-weight-bold"></span>
                            </div>

                            <div class="mt-4 p-3 rounded-lg bg-light text-center">
                                <small class="text-muted d-flex align-items-center justify-content-center">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <span>Gunakan foto formal dengan latar belakang polos untuk hasil terbaik di website.</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 order-3 order-lg-2">
                    <div class="d-flex flex-md-nowrap flex-wrap gap-3 mt-4">
                        <button type="submit" class="btn-modern btn-save flex-grow-1">
                            <i class="fas fa-sync-alt"></i> UPDATE DATA
                    </div>
                </div>

            </form>
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
        const hidden = document.getElementById('spesialis'); // ID disamakan dengan HTML

        let tags = [];

        // --- Fungsi Update Input Hidden ---
        function updateHidden() {
            hidden.value = tags.join(',');
        }

        // --- Fungsi Membuat Visual Tag ---
        function createTag(text) {
            const cleanText = text.trim();
            if (!cleanText || tags.includes(cleanText)) return;

            tags.push(cleanText);

            const tag = document.createElement('span');
            tag.style.cssText = `
                background: #6366f1;
                color: #fff;
                padding: 5px 12px;
                border-radius: 12px;
                font-size: 13px;
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: 600;
            `;

            tag.innerHTML = `
                ${cleanText}
                <span style="cursor:pointer; font-size: 18px; line-height: 1;">&times;</span>
            `;

            // Hapus tag
            tag.querySelector('span').onclick = (e) => {
                e.stopPropagation();
                tags = tags.filter(t => t !== cleanText);
                tag.remove();
                updateHidden();
            };

            container.insertBefore(tag, input);
            updateHidden();
        }

        // --- 1. LOAD DATA DARI DATABASE ---
        // Ambil data dari atribut data-initial
        const dbValue = container.getAttribute('data-initial');
        if (dbValue) {
            // Pecah string koma menjadi array dan buat tag-nya
            dbValue.split(',').forEach(item => {
                if (item.trim() !== "") {
                    createTag(item.trim());
                }
            });
        }

        // --- 2. INPUT EVENT (ENTER / KOMA) ---
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                createTag(input.value);
                input.value = '';
            }
        });

        // Klik area kotak fokus ke input teks
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