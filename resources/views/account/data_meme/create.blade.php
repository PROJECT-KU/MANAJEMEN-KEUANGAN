@extends('layouts.account')

@section('title')
Tambah Data Scopus Kafe | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>TAMBAH DATA MEME</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('account.meme.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Sesi</label>
                                    <select class="form-control" name="sesi" id="sesi" required>
                                        <option value="" disabled selected>-- PILIH SESI --</option>
                                        <option value="sesi 1">SESI 1</option>
                                        <option value="sesi 2">SESI 2</option>
                                        <!-- <option value="sesi 3">SESI 3</option> -->
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Mulai</label>
                                    <div class="input-group">
                                        <input type="text" name="waktu_mulai" id="waktu_mulai" value="" placeholder="Masukkan Waktu Mulai Per Sesi" class="form-control" readonly>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">WIB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Selesai</label>
                                    <div class="input-group">
                                        <input type="text" name="waktu_selesai" id="waktu_selesai" value="" placeholder="Masukkan Waktu Selesai Per Sesi" class="form-control" readonly>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">WIB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jumlah Kuota</label>
                                    <input type="number" name="kuota" value="{{ old('kuota') }}" placeholder="Masukkan Jumlah Kuota Per Sesi" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya Per Sesi</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="biaya" id="biaya" value="{{ old('biaya') }}" placeholder="Masukkan Jumlah Biaya Per Sesi" class="form-control currency" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Lokasi</label>
                                    <input type="text" name="lokasi" value="{{ old('lokasi') }}" placeholder="Masukkan Lokasi" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="status" required>
                                        <option value="" disabled selected>-- PILIH STATUS --</option>
                                        <option value="publish">PUBLISH</option>
                                        <option value="draft">DRAFT</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Deskripsi</label>
                                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="row">
                            <div class="col-md-6">
                                <div class="form-group custom-file-upload" style="margin-top: -3px;">
                                    <label>Gambar</label>
                                    <div class="input-group">
                                        <input type="file" name="gambar" id="gambar" class="inputfile" accept="image/*">
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
                        </div> -->

                </div>
            </div>
        </div>

        <div class="d-flex mt-3">
            <button class="btn btn-primary mr-1 btn-submit" type="submit" style="flex: 1; height:40px; font-size: 15px;"><i class="fa fa-paper-plane"></i> SIMPAN</button>
            <button class="btn btn-warning btn-reset" type="reset" style="flex: 1; height:40px; font-size: 15px;"><i class="fa fa-redo"></i> RESET</button>
        </div>

        </form>
    </section>
</div>

<!--================== MENAMPILKAN WAKTU SESI OTOMATIS ==================-->
<script>
    const sesiSelect = document.getElementById('sesi');
    const waktuMulaiInput = document.getElementById('waktu_mulai');
    const waktuSelesaiInput = document.getElementById('waktu_selesai');

    sesiSelect.addEventListener('change', (e) => {
        const selectedSesi = e.target.value;
        switch (selectedSesi) {
            case 'sesi 1':
                waktuMulaiInput.value = '08:00';
                waktuSelesaiInput.value = '13:00';
                break;
            case 'sesi 2':
                waktuMulaiInput.value = '13:00';
                waktuSelesaiInput.value = '18:00';
                break;
                // case 'sesi 3':
                //     waktuMulaiInput.value = '17:00';
                //     waktuSelesaiInput.value = '21:00';
                //     break;
            default:
                waktuMulaiInput.value = '';
                waktuSelesaiInput.value = '';
        }
    });
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

        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Only PNG, JPEG, and JPG files are allowed. Please choose a valid file type.'
            });
            resetImagePreview();
            return;
        }

        if (fileSize > 3000) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'File size exceeds the maximum limit of 3MB. Please choose a smaller file.'
            });
            resetImagePreview();
            return;
        }

        document.getElementById('file-selected').innerHTML = fileName + ' (' + fileSize + ' KB)';

        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById('imagePreview');
            output.innerHTML = `<img src="${reader.result}" alt="Selected Image" style="max-width: 100%; height: auto;">`;
        };
        reader.readAsDataURL(file);
    });

    function resetImagePreview() {
        var output = document.getElementById('imagePreview');
        output.innerHTML = `<img src="{{ asset('assets/img/meme/no-image.jpg') }}" alt="No Image" style="max-width: 100%; height: auto;">`;
        document.getElementById('file-selected').innerHTML = ''; // Clear file name display
    }
</script>
<!--================== END ==================-->

<!--================== DESKRIPSI ==================-->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
    // Initialize CKEditor 5
    ClassicEditor
        .create(document.querySelector('#deskripsi'))
        .catch(error => {
            console.error(error);
        });
</script>
<!--================== END ==================-->

<!--================== FORMAT RUPIAH ==================-->
<script>
    document.getElementById('biaya').addEventListener('keyup', function(e) {
        let value = this.value.replace(/[^,\d]/g, '').toString();
        let split = value.split(',');
        let remainder = split[0].length % 3;
        let rupiah = split[0].substr(0, remainder);
        let thousands = split[0].substr(remainder).match(/\d{3}/gi);

        if (thousands) {
            let separator = remainder ? '.' : '';
            rupiah += separator + thousands.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        this.value = rupiah;
    });
</script>
<!--================== END ==================-->
<script>
    var timeoutHandler = null;

    /**
     * btn submit loader
     */
    $(".btn-submit").click(function() {
        $(".btn-submit").addClass('btn-progress');
        if (timeoutHandler) clearTimeout(timeoutHandler);

        timeoutHandler = setTimeout(function() {
            $(".btn-submit").removeClass('btn-progress');

        }, 1000);
    });

    /**
     * btn reset loader
     */
    $(".btn-reset").click(function() {
        $(".btn-reset").addClass('btn-progress');
        if (timeoutHandler) clearTimeout(timeoutHandler);

        timeoutHandler = setTimeout(function() {
            $(".btn-reset").removeClass('btn-progress');

        }, 500);
    })
</script>
@stop