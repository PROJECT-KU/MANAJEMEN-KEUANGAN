@extends('public.layout.header')

@section('title')
Form Pendaftaran Analisis Bibliometrik | Rumah Scopus
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

    .image-preview-container {
        margin-top: 10px;
    }

    .image-preview {
        max-width: 150px;
        max-height: 150px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 2px dashed orange;
        /* Added dashed white border */
        border-radius: 5px;
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 5px;
    }
</style>
<!--================== END ==================-->

<!--================== BACKGOUND IMAGE ==================-->
<style>
    .hero-bg {
        position: relative;
        background-image: url('/assets/artikel/img/hero-bg.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        padding-bottom: 80px;
    }

    .hero-bg::before {
        content: "";
        position: absolute;
        inset: 0;
    }

    .hero-bg>.container {
        position: relative;
        z-index: 2;
    }
</style>
<!--================== END ==================-->

@section('konten')
<!-- ======= Hero Section ======= -->
<section id="blog" class="hero-bg align-items-center mt-5">
    <div class="container">

        <form id="formKonfirmasi" method="POST" action="{{ route('public.analisisbibliometrik.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="categories_analisis_bibliometrik_id" id="analisisbibliometrikIdvalue" value="{{ $item->id }}">
            <div class="row mt-4">

                <!-- ================== KOLOM KIRI ================== -->
                <div class="col-md-8">

                    <!-- FORM BATCH -->
                    <div class="card mb-4" style="box-shadow: 0 10px 25px rgba(0,0,0,.15);">
                        <div class="card-header fw-bold">
                            Detail Batch
                        </div>
                        <div class="card-body">
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Batch</label>
                                        <input type="text" name="waktu_mulai" id="waktu_mulai" value="{{ $item->nama }} #{{ $item->nama_ke }}" style="background-color:darkgrey;" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Sisa Kuota</label>
                                        <input type="text" name="lokasi" id="lokasi" value="{{ $item->sisa_kuota ?? 0 }}" style="background-color:darkgrey;" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Tanggal Mulai Pelaksanaan</label>
                                        <div class="input-group">
                                            <input type="text" name="waktu_mulai" id="waktu_mulai" value="{{ date('d M Y', strtotime($item->mulai)) }}" style="background-color:darkgrey;" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2 d-flex align-items-end justify-content-center">
                                    <div class="form-group text-center">
                                        <span>s/d</span>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Tanggal Selesai Pelaksanaan</label>
                                        <div class="input-group">
                                            <input type="text" name="waktu_selesai" id="waktu_selesai" value="{{ date('d M Y', strtotime($item->selesai)) }}" style="background-color:darkgrey;" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Biaya Per Sesi</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp.</span>
                                            </div>
                                            <input type="text" name="biaya" id="biaya" value="{{ number_format($item->biaya, 0, ',', '.') }}" style="background-color:darkgrey;" class="form-control currency" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kode Unik Pembayaran</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp.</span>
                                            </div>
                                            <input type="text" name="kode_unik" id="kodeUnik" value="{{ old('kode_unik') }}" style="background-color:darkgrey;" placeholder="0" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Jumlah Pendaftar <span style="color: red;">*</span></label>
                                        <input type="number" name="jumlah_pendaftar" id="jumlahPendaftar"
                                            class="form-control" required
                                            max="{{ $item->sisa_kuota ?? 0 }}"
                                            oninput="cekKuota(this)"
                                            data-sisa-kuota="{{ $item->sisa_kuota ?? 0 }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Diskon (jika ada)</label>
                                        <div class="input-group">
                                            <input type="text" name="kode_diskon_input" id="kodeDiskon" class="form-control" placeholder="Masukkan kode diskon">
                                            <button type="button" class="btn btn-primary" id="btnCekDiskon" data-id="{{ $item->id }}">Cek Diskon</button>
                                        </div>
                                        {{-- Hidden input untuk simpan kode diskon valid --}}
                                        <input type="hidden" name="kode_diskon" id="hiddenKodeDiskon">
                                        <small id="diskonMessage" class="text-success"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END -->

                    <!-- FORM DATA DIRI -->
                    <div class="card mb-4" style="box-shadow: 0 10px 25px rgba(0,0,0,.15);">
                        <div class="card-header fw-bold">
                            Data Diri Peserta
                        </div>
                        <div class="card-body">
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Lengkap Beserta Gelar <span style="color: red;">*</span></label>
                                        <div class="input-group">
                                            <input type="text" name="nama" id="nama" value="{{ old('nama') }}" placeholder="Masukkan Nama Lengkap" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email Aktif <span style="color: red;">*</span></label>
                                        <div class="input-group">
                                            <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="Masukkan Email Aktif" class="form-control" maxlength="100" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3 mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Affiliasi <span style="color: red;">*</span></label>
                                        <div class="input-group">
                                            <input type="text" name="affiliasi" id="affiliasi" value="{{ old('affiliasi') }}" placeholder="Masukkan Affiliasi" class="form-control" minlength="5" onkeypress="return/[a-zA-Z0-9@. ]/i.test(event.key)" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nomor WhatsApp <span style="color: red;">*</span></label>
                                        <div class="input-group">
                                            <input type="text" name="telp" id="telp" value="{{ old('telp') }}" placeholder="Masukkan Nomor WhatsApp" class="form-control" maxlength="20" minlength="8" onkeypress="return event.charCode >= 48 && event.charCode <=57" oninput="formatPhoneNumber(this)" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END -->
                </div>
                <!-- ================== END KOLOM KIRI ================== -->

                <!-- ================== KOLOM KANAN ================== -->
                <div class="col-md-4">

                    <!-- METODE PEMBAYARAN -->
                    <div class="card mb-4" style="box-shadow: 0 10px 25px rgba(0,0,0,.15);">
                        <div class="card-header fw-bold">
                            Metode Pembayaran
                        </div>
                        <div class="card-body">
                            <h1 class="header-title mt-3 mb-3">Metode Pembayaran</h1>
                            <div class="accordion" id="accordionExample">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                            <img src="{{ asset('assets/img/bri.jpg') }}" alt="BRI Image" style="width: 50px; height: 30px; margin-right:10px;"> BRI
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <p class="card-text">Nomor Rekening : <span id="nomor-rekening" style="font-weight: bold;  letter-spacing: 1px;">216401000467563</span>
                                                <button onclick="copyToClipboard('nomor-rekening')" class="btn btn-primary"><i class="fas fa-copy"></i> Salin</button>
                                                <br>
                                                Atas Nama : <b>Rumah Scopus Akademi</b>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr style="width: 100%;">
                            <h1 class="header-title mt-3 mb-3">Upload Bukti Pembayaran</h1>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group custom-file-upload" style="margin-top: -3px;">
                                        <label>Bukti Pembayaran</label>
                                        <div class="input-group">
                                            <input type="file" name="gambar" id="gambar" class="inputfile" accept="image/*" required>
                                            <label for="gambar" class="file-upload">
                                                <i class="fas fa-cloud-upload-alt"></i> Choose Image
                                            </label>
                                            @error('gambar')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="image-preview-container">
                                        <div id="imagePreview" class="image-preview"></div>
                                        <span id="file-selected" style="color: black;"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TOTAL PEMBAYARAN -->
                    <div class="card mb-4" style="box-shadow: 0 10px 25px rgba(0,0,0,.15);">
                        <div class="card-header fw-bold">
                            Ringkasan Pembayaran
                        </div>
                        <div class="card-body">

                            <div class="d-flex justify-content-between">
                                <strong style="width: 100%;">
                                    <span style="text-align: center; font-size:21px; font-weight: bold;" class="mt-3">
                                        Total yang Harus Dibayarkan
                                    </span>
                                    <hr style="margin: 10px;">

                                    @php
                                    $jumlahPPN = ($item->ppn > 0) ? $item->biaya * ($item->ppn / 100) : 0;
                                    @endphp

                                    <div class="card-body" style="color: black; display: flex; flex-direction: column; gap: 10px;">

                                        <!-- SUBTOTAL -->
                                        <div style="display: flex; align-items: center;">
                                            <label style="white-space: nowrap; font-weight: bold;">Subtotal :</label>
                                            <div style="margin-left:auto; display:flex; justify-content:flex-end; width:100%; text-align:right;">
                                                <span style="margin-right:5px;">Rp.</span>
                                                <span>{{ number_format($item->biaya, 0, ',', '.') }}</span>
                                                <input type="hidden" name="subtotal" id="subtotalInput" value="{{ $item->biaya }}">
                                            </div>
                                        </div>

                                        <!-- PPN -->
                                        <div style="display: flex; align-items: center;">
                                            <label style="white-space: nowrap; font-weight: bold;">PPN :</label>
                                            <div style="margin-left:auto; display:flex; justify-content:flex-end; width:100%; text-align:right;">
                                                <span style="margin-right:5px;">Rp.</span>
                                                <span>{{ number_format($jumlahPPN, 0, ',', '.') }}</span>
                                                <input type="hidden" name="ppn" id="jumlahPPN" value="{{ $jumlahPPN }}">
                                            </div>
                                        </div>

                                        <!-- DISKON -->
                                        <div style="display: flex; align-items: center;" id="formDiskon">
                                            <label style="white-space: nowrap; font-weight: bold;">Diskon :</label>
                                            <div style="margin-left:auto; display:flex; justify-content:flex-end; width:100%; text-align:right;">
                                                <span style="margin-right:5px;">Rp.</span>
                                                <span id="diskonText">0</span>
                                                <input type="hidden" name="nominal_diskon" id="potonganDiskon" value="0">
                                            </div>
                                        </div>

                                    </div>

                                    <hr style="margin: 10px;">

                                    <!-- TOTAL -->
                                    <div class="card-body mb-3"
                                        style="display:flex; align-items:center; font-size:20px; color:red;">
                                        <label style="white-space: nowrap; font-weight: bold;">Total :</label>
                                        <div style="margin-left:auto; display:flex; justify-content:flex-end; width:100%; text-align:right;">
                                            <span style="margin-right:5px;">Rp.</span>
                                            <span id="TotalPembayaran">0</span>
                                            <input type="hidden" name="total_pembayaran" id="totalPembayaranInput">
                                        </div>
                                    </div>

                                    <input type="hidden"
                                        name="total_keseluruhan_pembayaran"
                                        id="totalKeseluruhanPembayaranhidden"
                                        class="form-control"
                                        readonly>
                                </strong>
                            </div>

                            <!-- ================== TOMBOL SUBMIT ================== -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100 btn-submit" id="btnSubmit" style="height:45px; font-size:16px;">
                                        <i class="fa fa-paper-plane"></i> Konfirmasi Pendaftaran
                                    </button>
                                </div>
                            </div>
                            <!-- ================== END ================== -->

                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</section>

<!--================== LOADING ==================-->
<script>
    document.getElementById('formKonfirmasi').addEventListener('submit', function(e) {
        e.preventDefault(); // Cegah submit langsung

        const form = this;
        const btn = document.getElementById('btnSubmit');

        // Tampilkan konfirmasi dulu
        Swal.fire({
            title: 'Apakah data yang Anda masukkan sudah benar?',
            text: 'Pastikan semua data sudah sesuai sebelum mengirim.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, kirim sekarang',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Disable tombol dan ubah isi
                btn.disabled = true;
                btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim...`;

                // Tampilkan SweetAlert loading
                Swal.fire({
                    title: 'Sedang Mengirim Kwitansi...',
                    html: 'Silakan tunggu, kwitansi pesanan sedang kami kirim ke email.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                // Submit form manual setelah loading muncul
                setTimeout(() => {
                    form.submit();
                }, 300);
            }
            // Jika batal, tidak ada aksi — tetap di halaman
        });
    });
</script>
<!--================== END ==================-->

<!--================== JUMLAH PENDAFTAR TIDAK BOLEH MELEBIHI SISA KUOTA ==================-->
<script>
    function cekKuota(input) {
        const sisaKuota = parseInt(input.dataset.sisaKuota || 0);
        const jumlahInput = parseInt(input.value || 0);

        if (jumlahInput > sisaKuota) {
            Swal.fire({
                icon: 'warning',
                title: 'Kuota Melebihi Batas',
                html: `Jumlah pendaftar tidak boleh melebihi sisa kuota.<br><strong>Sisa kuota saat ini tinggal ${sisaKuota} orang.</strong>`,
                confirmButtonText: 'Oke',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                location.reload(); // Reload seluruh halaman
            });
        } else {
            hitungTotalPembayaran();
        }
    }
</script>
<!--================== END ==================-->

<!--================== CEK KODE DISKON ADA DI DB ATAU TIDAK ==================-->
<script>
    document.getElementById('btnCekDiskon').addEventListener('click', function() {
        const id = this.dataset.id;
        const kode = document.getElementById('kodeDiskon').value.trim();

        const messageEl = document.getElementById('diskonMessage');
        const hiddenInput = document.getElementById('hiddenKodeDiskon');
        const potonganInput = document.getElementById('potonganDiskon');
        const diskonText = document.getElementById('diskonText');

        // =============================
        // KODE KOSONG
        // =============================
        if (!kode) {
            hiddenInput.value = '';
            potonganInput.value = 0;
            diskonText.textContent = '0';
            messageEl.textContent = '';

            hitungTotalPembayaran();

            Swal.fire({
                icon: 'warning',
                title: 'Kode kosong',
                text: 'Kode diskon tidak boleh kosong.',
            });
            return;
        }

        // =============================
        // FETCH CEK DISKON
        // =============================
        fetch(`/cek-kode-diskon/${encodeURIComponent(id)}?kode_diskon=${encodeURIComponent(kode)}`)
            .then(response => response.json())
            .then(data => {

                // =============================
                // KODE VALID
                // =============================
                if (data.status === 'success') {
                    const nominalDiskon = Number(data.casted_nominal_diskon) || 0;

                    hiddenInput.value = kode;
                    potonganInput.value = nominalDiskon;
                    diskonText.textContent = nominalDiskon.toLocaleString('id-ID');

                    messageEl.textContent = `Diskon sebesar Rp ${nominalDiskon.toLocaleString('id-ID')}`;
                    messageEl.classList.remove('text-danger');
                    messageEl.classList.add('text-success');

                    hitungTotalPembayaran();

                    Swal.fire({
                        icon: 'success',
                        title: 'Kode Diskon Valid',
                        text: 'Diskon berhasil diterapkan.',
                        timer: 2000,
                        showConfirmButton: false
                    });

                }
                // =============================
                // KODE TIDAK VALID
                // =============================
                else {
                    hiddenInput.value = '';
                    potonganInput.value = 0;
                    diskonText.textContent = '0';

                    messageEl.textContent = data.message || 'Kode tidak valid';
                    messageEl.classList.remove('text-success');
                    messageEl.classList.add('text-danger');

                    hitungTotalPembayaran();

                    Swal.fire({
                        icon: 'error',
                        title: 'Kode Tidak Valid',
                        text: data.message || 'Kode diskon tidak ditemukan.',
                    });
                }
            })
            // =============================
            // ERROR SERVER
            // =============================
            .catch(error => {
                console.error('Error:', error);

                hiddenInput.value = '';
                potonganInput.value = 0;
                diskonText.textContent = '0';
                messageEl.textContent = '';

                hitungTotalPembayaran();

                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: 'Gagal memeriksa kode diskon.',
                });
            });
    });
</script>
<!--================== END ==================-->

<!--================== MENAMPILKAN KODE UNIK DAN MENGHITUNG SAAT FORM JUMLAH PENDAFTAR DI INPUTKAN ==================-->
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
            const kodeUnik = generateUniqueCode();
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

    jumlahPendaftarInput.addEventListener('input', hitungTotalPembayaran);
</script>
<!--================== END ==================-->

<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set default image on page load
        var output = document.getElementById('imagePreview');
        output.innerHTML = `<img src="{{ asset('assets/img/meme/no-image.jpg') }}" alt="No Image" style="max-width: 100%; height: auto;">`;
    });

    document.getElementById('gambar').addEventListener('change', function(event) {
        var fileInput = event.target;
        var file = fileInput.files[0];
        var fileName = file.name;
        var fileSize = (file.size / 1024).toFixed(2); // in KB
        var allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];

        // Validate file type
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Hanya file PNG, JPEG, dan JPG yang diizinkan. Harap pilih jenis file yang valid.'
            });
            resetImagePreview();
            return;
        }

        // Validate file size (max 2MB)
        if (fileSize > 2000) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Ukuran file melebihi batas maksimum 2MB. Harap pilih file yang lebih kecil.'
            });
            resetImagePreview();
            return;
        }

        // Display selected file name and size
        document.getElementById('file-selected').innerHTML = fileName + ' (' + fileSize + ' KB)';

        // Display image preview
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById('imagePreview');
            output.innerHTML = `<img src="${reader.result}" alt="Selected Image" style="max-width: 100%; height: auto;">`;
        };
        reader.readAsDataURL(file);
    });

    // Reset image preview if file is invalid or cleared
    function resetImagePreview() {
        var output = document.getElementById('imagePreview');
        output.innerHTML = `<img src="{{ asset('assets/img/meme/no-image.jpg') }}" alt="No Image" style="max-width: 100%; height: auto;">`;
        document.getElementById('file-selected').innerHTML = ''; // Clear file name display
    }

    // Check if the image preview is set before submitting the form
    document.querySelector('form').addEventListener('submit', function(event) {
        var imagePreview = document.getElementById('imagePreview').innerHTML;
        if (imagePreview.includes('no-image.jpg')) { // Check if default image is still shown
            Swal.fire({
                icon: 'error',
                title: 'Gambar Tidak Dipilih',
                text: 'Harap unggah gambar sebelum mengirimkan formulir.'
            });
            event.preventDefault(); // Prevent form submission
        }
    });
</script>
<!--================== END ==================-->

<!--================== BUTTOM COPY ==================-->
<script>
    function copyToClipboard(elementId) {
        let text = document.getElementById(elementId).innerText;

        // Normalisasi unicode → angka biasa
        text = text.normalize('NFKD').replace(/[^\d]/g, '');

        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Nomor rekening berhasil disalin',
                text: 'Nomor rekening: ' + text,
                confirmButtonText: 'OK',
            });
        });
    }
</script>
<!--================== END ==================-->

<!--================== SWEET ALERT JIKA BELUM UPLOAD BUKTI PEMBAYARAN ==================-->
<script>
    document.querySelector('.btn-submit').addEventListener('click', function(event) {
        // Get the file input element
        const fileInput = document.getElementById('gambar');

        // Check if the file input is empty
        if (!fileInput.files.length) {
            // Prevent form submission
            event.preventDefault();

            // Show SweetAlert warning
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: 'Wajib upload bukti pembayaran sebelum melanjutkan.',
                confirmButtonText: 'OK'
            });
        }
    });
</script>
<!--================== END ==================-->

<!--================== FORMAT NO TELP ==================-->
<script>
    function formatPhoneNumber(input) {
        // Menghapus semua karakter non-digit
        var phoneNumber = input.value.replace(/\D/g, '');

        // Menentukan panjang nomor telepon
        var phoneNumberLength = phoneNumber.length;

        // Memeriksa panjang nomor telepon dan menerapkan format yang sesuai
        if (phoneNumberLength === 11) {
            phoneNumber = phoneNumber.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
        } else if (phoneNumberLength === 12) {
            phoneNumber = phoneNumber.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
        } else if (phoneNumberLength === 13) {
            phoneNumber = phoneNumber.replace(/(\d{5})(\d{4})(\d{4})/, '$1-$2-$3');
        }

        // Mengatur nilai input dengan nomor telepon yang diformat
        input.value = phoneNumber;
    }
</script>
<!--================== END ==================-->
@stop