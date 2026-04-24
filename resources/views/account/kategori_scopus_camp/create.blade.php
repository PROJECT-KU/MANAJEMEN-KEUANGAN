@extends('layouts.account')

@section('title')
Tambah Kategori | MIS
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
        display: none;
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
            <h1>TAMBAH KATEGORI</h1>
        </div>

        <div class="section-body">

            <form id="kategoriForm" action="{{ route('account.kategoriscopuscamp.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!--================== DETAIL KATEGORI ==================-->
                <div class="card">
                    <div class="d-flex justify-content-center align-items-center mt-3">
                        <h4 class="m-0" style="font-size: 20px;">DETAIL KATEGORI</h4>
                    </div>
                    <hr>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Batch <span style="color: red;">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="nama" value="{{ old('nama') }}" placeholder="Masukkan Nama Kategori" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Batch Ke- <span style="color: red;">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">#</span>
                                        </div>
                                        <input type="number" name="nama_ke" value="{{ old('nama_ke') }}" placeholder="Kategori Ke" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Tanggal Mulai Pelaksanaan <span style="color: red;">*</span></label>
                                    <input type="date" class="form-control" name="mulai" placeholder="Pilih Tanggal" required>
                                </div>
                            </div>

                            <div class="col-md-2 text-center">
                                <label class="d-block" style="margin-top: 15px;">S/D</label>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Tanggal Selesai Pelaksanaan <span style="color: red;">*</span></label>
                                    <input type="date" class="form-control" name="selesai" placeholder="Pilih Tanggal" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Total Kuota <span style="color: red;">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="total_kuota" value="{{ old('total_kuota') }}" placeholder="Masukkan Kuota" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Best Price <span style="color: red;">*</span></label>
                                    <div class="input-group">
                                        <select class="form-control" style="height: auto;" name="best_price" required>
                                            <option value="" disabled selected>-- PILIH STATUS --</option>
                                            <option value="yes">Yes</option>
                                            <option value="no">No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status <span style="color: red;">*</span></label>
                                    <div class="input-group">
                                        <select class="form-control" style="height: auto;" name="status" required>
                                            <option value="" disabled selected>-- PILIH STATUS --</option>
                                            <option value="active">Active</option>
                                            <option value="non active">Non Active</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Link Group WhatsApp <span style="color: red;">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="group_wa" value="{{ old('group_wa') }}" placeholder="Masukkan Link Group WhatsApp" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Lokasi <span style="color: red;">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="lokasi" value="{{ old('lokasi') }}" placeholder="Masukkan Lokasi" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!--================== END ==================-->

                <!--================== BIAYA KATEGORI ==================-->
                <div class="card">
                    <div class="d-flex justify-content-center align-items-center mt-3">
                        <h4 class="m-0" style="font-size: 20px;">BIAYA</h4>
                    </div>
                    <hr>
                    <div class="card-body">

                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya Per Batch <span style="color: red;">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="biaya" id="biaya" value="{{ old('biaya') }}" placeholder="Masukkan Total Biaya" class="form-control" oninput="updateNominalDiskon()" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>PPN</label>
                                    <div class="input-group">
                                        <input type="number" name="ppn" id="ppn" value="{{ old('ppn') }}" placeholder="Masukkan Total PPN" class="form-control">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipe Diskon</label>
                                    <div class="input-group">
                                        <select class="form-control" name="tipe_diskon" id="tipe_diskon" style="height: auto;" onchange="handleDiskonTypeChange()">
                                            <option value="" disabled selected>-- PILIH TIPE DISKON --</option>
                                            <option value="persentase">Persentase</option>
                                            <option value="nominal">Nominal</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Diskon Persentase</label>
                                    <div class="input-group">
                                        <input type="number" name="diskon_persentase" id="diskon_persentase" value="{{ old('diskon_persentase') }}" placeholder="Masukkan Total Persentase" class="form-control" disabled oninput="updateNominalDiskon()">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nominal Diskon</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="nominal_diskon" id="nominal_diskon" value="{{ old('nominal_diskon') }}" placeholder="Masukkan Total Nominal Diskon" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kode Diskon</label>
                                    <div class="input-group">
                                        <input type="text" name="kode_diskon" id="kode_diskon" value="{{ old('kode_diskon') }}" placeholder="Masukkan Kode Diskon" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Total Biaya</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="total_biaya" id="total_biaya" value="{{ old('total_biaya') }}" placeholder="Masukkan Total Biaya" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!--================== END ==================-->

                <!--================== DETAIL LAINNYA ==================-->
                <div class="card">
                    <div class="d-flex justify-content-center align-items-center mt-3">
                        <h4 class="m-0" style="font-size: 20px;">DETAIL LAINNYA</h4>
                    </div>
                    <hr>
                    <div class="card-body">

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <div class="form-group custom-file-upload">
                                    <label>Tumbnail <span style="color: red;">*</span></label>
                                    <div class="input-group">
                                        <input type="file" name="gambar" id="gambar" class="inputfile" accept="image/*" required>
                                        <label for="gambar" class="file-upload">
                                            <i class="fas fa-cloud-upload-alt"></i> Choose Image
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="image-preview-container">
                                    <div id="imagePreview" class="image-preview"></div>
                                    <span id="file-selected"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <label>Deskripsi</label>
                                <div class="mb-3">
                                    <textarea class="textarea" name="desc" id="isi" placeholder="Place some text here" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>
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
                                <a href="{{ route('account.kategoriscopuscamp.index') }}"
                                    class="btn btn-warning btn-submit rounded-pill w-100 w-md-auto mb-2 mb-md-0">
                                    <i class="fa fa-undo"></i> KEMBALI
                                </a>

                            </div>
                        </div>

                    </div>
                </div>
            </form>

        </div>
    </section>
</div>

<!--================== MENGHITUNG DISKON DAN DISABLED DISKON ==================-->
<script>
    function handleDiskonTypeChange() {
        const tipe = document.getElementById('tipe_diskon').value;
        const persenField = document.getElementById('diskon_persentase');
        const nominalField = document.getElementById('nominal_diskon');

        if (tipe === 'persentase') {
            persenField.disabled = false;
            nominalField.readOnly = true;
            nominalField.value = ''; // reset manual entry
            updateNominalDiskon();
        } else if (tipe === 'nominal') {
            persenField.disabled = true;
            nominalField.readOnly = false;
            persenField.value = ''; // reset persentase
        } else {
            // Belum memilih tipe
            persenField.disabled = true;
            nominalField.readOnly = true;
            persenField.value = '';
            nominalField.value = '';
        }
    }

    function updateNominalDiskon() {
        const tipe = document.getElementById('tipe_diskon').value;
        const persen = parseFloat(document.getElementById('diskon_persentase').value) || 0;
        const biayaRaw = document.getElementById('biaya').value.replace(/\D/g, '');
        const biaya = parseFloat(biayaRaw) || 0;
        let hasilDiskon = 0;

        if (tipe === 'persentase' && biaya > 0 && persen > 0) {
            hasilDiskon = Math.round((persen / 100) * biaya);
            document.getElementById('nominal_diskon').value = formatRupiah(hasilDiskon);
        } else if (tipe === 'nominal') {
            const diskonRaw = document.getElementById('nominal_diskon').value.replace(/\D/g, '');
            hasilDiskon = parseFloat(diskonRaw) || 0;
        }

        const total = biaya - hasilDiskon;
        document.getElementById('total_biaya').value = formatRupiah(total > 0 ? total : 0);
    }

    function formatRupiah(angka) {
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    window.addEventListener('DOMContentLoaded', function() {
        handleDiskonTypeChange();

        document.getElementById('tipe_diskon').addEventListener('change', handleDiskonTypeChange);
        document.getElementById('diskon_persentase').addEventListener('input', updateNominalDiskon);
        document.getElementById('nominal_diskon').addEventListener('input', function(e) {
            const angka = e.target.value.replace(/\D/g, '');
            e.target.value = formatRupiah(angka);
            updateNominalDiskon();
        });
        document.getElementById('biaya').addEventListener('input', updateNominalDiskon);
    });
</script>
<!--================== END ==================-->

<!--================== FORMAT RUPIAH ==================-->
<script>
    const biayaInput = document.getElementById('biaya');

    biayaInput.addEventListener('input', function(e) {
        let value = this.value.replace(/[^\d]/g, '');
        value = new Intl.NumberFormat('id-ID').format(value);
        this.value = value;
    });
</script>
<!--================== END ==================-->

<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('gambar').addEventListener('change', function(event) {
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

    for (var i = 2; i <= 20; i++) {
        var fileInput = document.getElementById('gambar' + i);
        if (fileInput) {
            (function(i) { // Capture the value of i in a closure
                fileInput.addEventListener('change', function(event) {
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

                    document.getElementById('file-selected' + i).innerHTML = fileName + ' (' + fileSize + ' KB)';

                    var reader = new FileReader();
                    reader.onload = function() {
                        var output = document.getElementById('imagePreview' + i);
                        output.innerHTML = `<img src="${reader.result}">`;
                        output.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                });
            })(i); // Pass the current value of i to the closure
        }
    }
</script>
<script>
    document.querySelector(".btn-submit").addEventListener("click", function(e) {
        var gambarInput = document.getElementById('gambar');
        if (gambarInput.files.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Silakan pilih gambar terlebih dahulu sebelum submit!'
            }).then(() => gambarInput.focus());
        }
    });
</script>
<!--================== END ==================-->
@stop