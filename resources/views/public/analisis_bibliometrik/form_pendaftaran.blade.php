@extends('public.layout.header')

@section('title')
Form Pendaftaran Analisis Bibliometrik | Rumah Scopus
@stop

<style>
    /* =========================================
       BACKGROUND & GLASSMORPHISM BASE (TEMA BIRU SCOPUS CAMP)
       ========================================= */
    .hero-bg {
        position: relative;
        background-color: #f8fafc;
        /* Gradien Biru-Indigo seragam dengan Scopus Camp */
        background-image: radial-gradient(circle at top right, rgba(99, 102, 241, 0.15), transparent 40%),
            radial-gradient(circle at bottom left, rgba(245, 158, 11, 0.15), transparent 40%),
            url('/assets/artikel/img/hero-bg.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        padding: 100px 0 80px 0;
        min-height: 100vh;
        z-index: 1;
    }

    /* Efek Kaca Utama untuk Card */
    .glass-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 1);
        border-radius: 24px;
        padding: 30px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .glass-card:hover {
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.08);
    }

    .glass-header {
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px dashed rgba(148, 163, 184, 0.3);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .glass-header i {
        color: #6366f1;
        /* Ikon warna Indigo */
    }

    /* =========================================
       CSS GRID LAYOUT
       ========================================= */
    .registration-grid {
        display: grid;
        grid-template-columns: 1.8fr 1.2fr;
        gap: 30px;
        align-items: start;
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    /* =========================================
       INPUT FORM GLOSSY
       ========================================= */
    .form-group label {
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: block;
    }

    .form-control-glass {
        width: 100%;
        background: rgba(255, 255, 255, 0.8);
        border: 1.5px solid rgba(203, 213, 225, 0.6);
        border-radius: 14px;
        padding: 12px 18px;
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
        transition: all 0.3s ease;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .form-control-glass:focus {
        background: #ffffff;
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }

    .form-control-glass[readonly] {
        background: rgba(241, 245, 249, 0.6);
        color: #64748b;
        cursor: not-allowed;
        border-color: transparent;
    }

    /* Custom Input Group (Gabungan Rp dan Input) */
    .input-group-glass {
        display: flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.8);
        border: 1.5px solid rgba(203, 213, 225, 0.6);
        border-radius: 14px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .input-group-glass:focus-within {
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }

    .input-group-glass.readonly {
        background: rgba(241, 245, 249, 0.6);
        border-color: transparent;
    }

    .input-group-glass .input-group-text {
        background: transparent;
        border: none;
        font-weight: 800;
        color: #475569;
        padding: 12px 15px;
    }

    .input-group-glass .form-control-glass {
        border: none;
        border-radius: 0;
        box-shadow: none;
        background: transparent;
        padding-left: 0;
    }

    /* =========================================
       FILE UPLOAD KACA
       ========================================= */
    .file-upload-glass {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(168, 85, 247, 0.1) 100%);
        border: 2px dashed #818cf8;
        border-radius: 16px;
        color: #4f46e5;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-upload-glass:hover {
        background: rgba(99, 102, 241, 0.15);
        transform: translateY(-2px);
    }

    .image-preview {
        width: 100%;
        height: 180px;
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        background: rgba(241, 245, 249, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.05);
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* =========================================
       ACCORDION KACA (Untuk Bank)
       ========================================= */
    .accordion-glass .accordion-item {
        background: rgba(255, 255, 255, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 16px !important;
        margin-bottom: 10px;
        overflow: hidden;
    }

    .accordion-glass .accordion-button {
        background: transparent;
        font-weight: 700;
        color: #1e293b;
        box-shadow: none;
        padding: 15px 20px;
    }

    .accordion-glass .accordion-button:not(.collapsed) {
        background: rgba(99, 102, 241, 0.05);
        color: #4f46e5;
    }

    .accordion-glass .accordion-body {
        background: rgba(255, 255, 255, 0.8);
        border-top: 1px solid rgba(255, 255, 255, 0.5);
    }

    /* =========================================
       BUTTON CEK DISKON PILL
       ========================================= */
    .btn-check-discount {
        background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
        color: white;
        border: none;
        padding: 8px 20px;
        font-weight: 800;
        font-size: 13px;
        letter-spacing: 1px;
        border-radius: 10px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
        cursor: pointer;
        margin-right: 3px;
    }

    .btn-check-discount:hover {
        background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 6px 18px rgba(234, 88, 12, 0.4);
    }

    .btn-check-discount:active {
        transform: translateY(1px);
        box-shadow: 0 2px 8px rgba(234, 88, 12, 0.2);
    }

    /* =========================================
       BUTTON GLOSSY UTAMA (SUBMIT)
       ========================================= */
    .btn-glossy {
        background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%);
        color: white;
        border: none;
        padding: 16px;
        border-radius: 16px;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
        width: 100%;
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        transition: all 0.3s ease;
    }

    .btn-glossy:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 25px rgba(99, 102, 241, 0.4);
        color: white;
    }

    /* =========================================
       RINGKASAN PEMBAYARAN
       ========================================= */
    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        font-size: 15px;
        color: #475569;
        font-weight: 600;
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        font-size: 22px;
        font-weight: 900;
        color: #ef4444;
        border-top: 2px dashed rgba(148, 163, 184, 0.3);
        margin-top: 10px;
    }

    @media (max-width: 991px) {
        .registration-grid {
            grid-template-columns: 1fr;
        }

        .form-grid-3 {
            grid-template-columns: 1fr;
        }

        .form-grid-2 {
            grid-template-columns: 1fr;
        }
    }
</style>

@section('konten')
<section id="blog" class="hero-bg align-items-center">
    <div class="container">

        <form id="formKonfirmasi" method="POST" action="{{ route('public.analisisbibliometrik.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="categories_analisis_bibliometrik_id" id="analisisbibliometrikIdvalue" value="{{ $item->id }}">

            <div class="registration-grid mt-4">

                <div class="grid-main">

                    <div class="glass-card">
                        <div class="glass-header">
                            <i class="fas fa-layer-group"></i> Detail Batch
                        </div>

                        <div class="form-grid-3">
                            <div class="form-group">
                                <label>Batch</label>
                                <input type="text" name="waktu_mulai_tampil" value="{{ $item->nama }} #{{ $item->nama_ke }}" class="form-control-glass" readonly>
                            </div>
                            <div class="form-group">
                                <label>Lokasi</label>
                                <input type="text" name="lokasi" id="lokasi" value="{{ $item->lokasi ?? 'Online' }}" class="form-control-glass" readonly>
                            </div>
                            <div class="form-group">
                                <label>Sisa Kuota</label>
                                <input type="text" value="{{ $item->sisa_kuota ?? 0 }}" class="form-control-glass" readonly style="color: #ef4444; font-weight: 800;">
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label>Mulai Pelaksanaan</label>
                                <input type="text" name="waktu_mulai" id="waktu_mulai" value="{{ date('d M Y', strtotime($item->mulai)) }}" class="form-control-glass" readonly>
                            </div>
                            <div class="form-group">
                                <label>Selesai Pelaksanaan</label>
                                <input type="text" name="waktu_selesai" id="waktu_selesai" value="{{ date('d M Y', strtotime($item->selesai)) }}" class="form-control-glass" readonly>
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label>Biaya Per Sesi</label>
                                <div class="input-group-glass readonly">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="biaya" id="biaya" value="{{ number_format($item->biaya, 0, ',', '.') }}" class="form-control-glass currency" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Kode Unik Pembayaran</label>
                                <div class="input-group-glass readonly">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="kode_unik" id="kodeUnik" value="{{ old('kode_unik') }}" placeholder="0" class="form-control-glass" readonly style="color: #f59e0b;">
                                </div>
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label>Jumlah Pendaftar <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah_pendaftar" id="jumlahPendaftar" class="form-control-glass" required
                                    value="1"
                                    readonly
                                    data-sisa-kuota="{{ $item->sisa_kuota ?? 0 }}"
                                    style="background: rgba(241, 245, 249, 0.6); font-weight: 900; color: #ef4444; text-align: center; border-color: transparent;">
                            </div>

                            <div class="form-group">
                                <label>Diskon (Jika Ada)</label>
                                <div class="input-group-glass" style="background: #fff; padding: 5px;">
                                    <input type="text" name="kode_diskon_input" id="kodeDiskon" class="form-control-glass" placeholder="KODE DISKON..." style="border: none; background: transparent; box-shadow: none; padding-left: 15px; text-transform: uppercase;">
                                    <button type="button" class="btn-check-discount" id="btnCekDiskon" data-id="{{ $item->id }}">
                                        <i class="fas fa-tags"></i> CEK
                                    </button>
                                </div>
                                <input type="hidden" name="kode_diskon" id="hiddenKodeDiskon">
                                <small id="diskonMessage" class="text-success mt-1 d-block font-weight-bold"></small>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card">
                        <div class="glass-header">
                            <i class="fas fa-user-circle"></i> Data Diri Peserta
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label>Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                                <input type="text" name="nama" id="nama" value="{{ old('nama') }}" placeholder="John Doe, S.Kom." class="form-control-glass" required style="background: #fff;">
                            </div>
                            <div class="form-group">
                                <label>Email Aktif <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="email@domain.com" class="form-control-glass" maxlength="100" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required style="background: #fff;">
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label>Afiliasi <span class="text-danger">*</span></label>
                                <input type="text" name="affiliasi" id="affiliasi" value="{{ old('affiliasi') }}" placeholder="Universitas / Instansi" class="form-control-glass" minlength="5" onkeypress="return/[a-zA-Z0-9@. ]/i.test(event.key)" required style="background: #fff;">
                            </div>
                            <div class="form-group">
                                <label>WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" name="telp" id="telp" value="{{ old('telp') }}" placeholder="0812-3456-7890" class="form-control-glass" maxlength="20" minlength="8" onkeypress="return event.charCode >= 48 && event.charCode <=57" oninput="formatPhoneNumber(this)" required style="background: #fff;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid-sidebar">

                    <div class="glass-card">
                        <div class="glass-header">
                            <i class="fas fa-wallet"></i> Pembayaran
                        </div>

                        <div class="accordion accordion-glass" id="accordionExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        <img src="{{ asset('assets/img/bri.jpg') }}" alt="BRI" style="width: 45px; height: auto; margin-right:15px; border-radius:4px;"> BANK BRI
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                    <div class="accordion-body text-center">
                                        <p class="mb-2 text-muted small font-weight-bold">NOMOR REKENING</p>
                                        <h4 id="nomor-rekening" class="font-weight-900 text-dark mb-3" style="letter-spacing: 2px;">216401000467563</h4>
                                        <p class="mb-3">a.n. <strong>Rumah Scopus Akademi</strong></p>
                                        <button type="button" onclick="copyToClipboard('nomor-rekening')" class="btn btn-outline-primary btn-sm w-100 rounded-pill font-weight-bold">
                                            <i class="fas fa-copy"></i> Salin Rekening
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr style="border-top: 2px dashed rgba(148, 163, 184, 0.3); margin: 25px 0;">

                        <div class="form-group mb-3">
                            <label>Upload Bukti Transfer <span class="text-danger">*</span></label>
                            <input type="file" name="gambar" id="gambar" style="display: none;" accept="image/*" required>
                            <label for="gambar" class="file-upload-glass">
                                <i class="fas fa-cloud-upload-alt fa-lg"></i> Pilih Gambar
                            </label>
                            @error('gambar')
                            <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="image-preview-container">
                            <div id="imagePreview" class="image-preview"></div>
                            <span id="file-selected" class="small text-muted mt-2 d-block text-center font-weight-bold"></span>
                        </div>
                    </div>

                    <div class="glass-card" style="position: sticky; top: 100px;">
                        <div class="glass-header">
                            <i class="fas fa-receipt"></i> Ringkasan
                        </div>

                        @php
                        $jumlahPPN = ($item->ppn > 0) ? $item->biaya * ($item->ppn / 100) : 0;
                        @endphp

                        <div class="summary-container">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <div>
                                    <span>Rp</span> <span>{{ number_format($item->biaya, 0, ',', '.') }}</span>
                                </div>
                                <input type="hidden" name="subtotal" id="subtotalInput" value="{{ $item->biaya }}">
                            </div>

                            <div class="summary-row">
                                <span>PPN</span>
                                <div>
                                    <span>Rp</span> <span>{{ number_format($jumlahPPN, 0, ',', '.') }}</span>
                                </div>
                                <input type="hidden" name="ppn" id="jumlahPPN" value="{{ $jumlahPPN }}">
                            </div>

                            <div class="summary-row" id="formDiskon">
                                <span>Diskon</span>
                                <div class="text-success">
                                    <span>- Rp</span> <span id="diskonText">0</span>
                                </div>
                                <input type="hidden" name="nominal_diskon" id="potonganDiskon" value="0">
                            </div>

                            <div class="summary-total">
                                <span>Total</span>
                                <div>
                                    <span>Rp</span> <span id="TotalPembayaran">0</span>
                                </div>
                                <input type="hidden" name="total_pembayaran" id="totalPembayaranInput">
                            </div>
                        </div>

                        <input type="hidden" name="total_keseluruhan_pembayaran" id="totalKeseluruhanPembayaranhidden" readonly>

                        <div class="mt-4">
                            <button type="submit" class="btn-glossy btn-submit" id="btnSubmit">
                                <i class="fa fa-paper-plane mr-2"></i> Konfirmasi
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </form>

    </div>
</section>

<script>
    document.getElementById('formKonfirmasi').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const btn = document.getElementById('btnSubmit');

        Swal.fire({
            title: 'Konfirmasi Data',
            text: 'Pastikan semua data sudah sesuai sebelum mengirim.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            /* Warna tombol disamakan dengan Scopus Camp */
            confirmButtonText: 'Ya, Kirim',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                btn.disabled = true;
                btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Mengirim...`;

                Swal.fire({
                    title: 'Memproses Pendaftaran...',
                    html: 'Silakan tunggu, kwitansi sedang disiapkan.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                setTimeout(() => {
                    form.submit();
                }, 300);
            }
        });
    });
</script>

<script>
    document.getElementById('btnCekDiskon').addEventListener('click', function() {
        const id = this.dataset.id;
        const kode = document.getElementById('kodeDiskon').value.trim();

        const messageEl = document.getElementById('diskonMessage');
        const hiddenInput = document.getElementById('hiddenKodeDiskon');
        const potonganInput = document.getElementById('potonganDiskon');
        const diskonText = document.getElementById('diskonText');

        if (!kode) {
            hiddenInput.value = '';
            potonganInput.value = 0;
            diskonText.textContent = '0';
            messageEl.textContent = '';
            hitungTotalPembayaran();
            Swal.fire({
                icon: 'warning',
                title: 'Kode kosong',
                text: 'Silakan masukkan kode diskon.'
            });
            return;
        }

        fetch(`/cek-kode-diskon/${encodeURIComponent(id)}?kode_diskon=${encodeURIComponent(kode)}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const nominalDiskon = Number(data.casted_nominal_diskon) || 0;
                    hiddenInput.value = kode;
                    potonganInput.value = nominalDiskon;
                    diskonText.textContent = nominalDiskon.toLocaleString('id-ID');
                    messageEl.textContent = `Diskon sebesar Rp ${nominalDiskon.toLocaleString('id-ID')}`;
                    messageEl.classList.replace('text-danger', 'text-success');
                    hitungTotalPembayaran();
                    Swal.fire({
                        icon: 'success',
                        title: 'Diskon Valid',
                        text: 'Berhasil diterapkan.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    hiddenInput.value = '';
                    potonganInput.value = 0;
                    diskonText.textContent = '0';
                    messageEl.textContent = data.message || 'Kode tidak valid';
                    messageEl.classList.replace('text-success', 'text-danger');
                    hitungTotalPembayaran();
                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak Valid',
                        text: data.message || 'Kode tidak ditemukan.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                hiddenInput.value = '';
                potonganInput.value = 0;
                diskonText.textContent = '0';
                messageEl.textContent = '';
                hitungTotalPembayaran();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal memeriksa kode diskon.'
                });
            });
    });
</script>

<script>
    const jumlahPendaftarInput = document.getElementById('jumlahPendaftar');
    const biayaInput = document.getElementById('biaya');
    const kodeUnikInput = document.getElementById('kodeUnik');
    const totalPembayaranDisplay = document.getElementById('TotalPembayaran');
    const potonganDiskonInput = document.getElementById('potonganDiskon');

    function formatRupiah(number) {
        return Number(number).toLocaleString('id-ID');
    }

    function generateUniqueCode() {
        return Math.floor(Math.random() * 100) + 1;
    }

    function hitungTotalPembayaran() {
        const jumlah = parseInt(jumlahPendaftarInput.value) || 0;
        const biayaRaw = biayaInput.value.replace(/\./g, '').replace(/,/g, '');
        const biaya = parseInt(biayaRaw) || 0;

        let potonganDiskon = 0;
        if (document.getElementById('formDiskon').style.display !== 'none') {
            const diskonRaw = potonganDiskonInput.value.replace(/[^\d]/g, '').trim();
            potonganDiskon = parseInt(diskonRaw) || 0;
        }

        const jumlahPPN = parseInt(document.getElementById('jumlahPPN')?.value || 0);

        if (jumlah > 0) {
            // Hanya buat kode unik jika kode unik masih '0' atau kosong
            let kodeUnikRaw = kodeUnikInput.value.replace(/\./g, '');
            let kodeUnik = parseInt(kodeUnikRaw);

            if (isNaN(kodeUnik) || kodeUnik === 0) {
                kodeUnik = generateUniqueCode();
            }

            const subtotal = (jumlah * biaya) + kodeUnik - potonganDiskon + jumlahPPN;

            kodeUnikInput.value = formatRupiah(kodeUnik);
            totalPembayaranDisplay.textContent = formatRupiah(subtotal);
            document.getElementById('totalPembayaranInput').value = subtotal;
        } else {
            kodeUnikInput.value = '0';
            totalPembayaranDisplay.textContent = '0';
            document.getElementById('totalPembayaranInput').value = 0;
        }
    }

    // Panggil otomatis saat halaman pertama kali diload agar nilai 1 langsung dihitung
    document.addEventListener('DOMContentLoaded', function() {
        hitungTotalPembayaran();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var output = document.getElementById('imagePreview');
        output.innerHTML = `<img src="{{ asset('assets/img/meme/no-image.jpg') }}" alt="No Image">`;
    });

    document.getElementById('gambar').addEventListener('change', function(event) {
        var fileInput = event.target;
        var file = fileInput.files[0];
        var fileName = file.name;
        var fileSize = (file.size / 1024).toFixed(2);
        var allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];

        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Format Salah',
                text: 'Hanya file PNG, JPEG, dan JPG yang diizinkan.'
            });
            resetImagePreview();
            return;
        }

        if (fileSize > 2000) {
            Swal.fire({
                icon: 'error',
                title: 'File Terlalu Besar',
                text: 'Maksimum ukuran gambar adalah 2MB.'
            });
            resetImagePreview();
            return;
        }

        document.getElementById('file-selected').innerHTML = fileName + ' (' + fileSize + ' KB)';
        var reader = new FileReader();
        reader.onload = function() {
            document.getElementById('imagePreview').innerHTML = `<img src="${reader.result}" alt="Preview">`;
        };
        reader.readAsDataURL(file);
    });

    function resetImagePreview() {
        document.getElementById('imagePreview').innerHTML = `<img src="{{ asset('assets/img/meme/no-image.jpg') }}" alt="No Image">`;
        document.getElementById('file-selected').innerHTML = '';
        document.getElementById('gambar').value = "";
    }

    document.querySelector('.btn-submit').addEventListener('click', function(event) {
        const fileInput = document.getElementById('gambar');
        if (!fileInput.files.length) {
            event.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Bukti Belum Diunggah',
                text: 'Harap upload bukti pembayaran Anda.'
            });
        }
    });
</script>

<script>
    function copyToClipboard(elementId) {
        let text = document.getElementById(elementId).innerText.replace(/[^\d]/g, '');
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Disalin!',
                text: 'Nomor rekening: ' + text,
                timer: 2000,
                showConfirmButton: false
            });
        });
    }
</script>

<script>
    function formatPhoneNumber(input) {
        var phoneNumber = input.value.replace(/\D/g, '');
        var len = phoneNumber.length;
        if (len === 11) phoneNumber = phoneNumber.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
        else if (len === 12) phoneNumber = phoneNumber.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
        else if (len === 13) phoneNumber = phoneNumber.replace(/(\d{5})(\d{4})(\d{4})/, '$1-$2-$3');
        input.value = phoneNumber;
    }
</script>
@stop