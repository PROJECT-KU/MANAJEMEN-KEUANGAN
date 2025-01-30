@extends('layouts.account')

@section('title')
Edit Data Scopus Kafe | MIS
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
            <h1>EDIT DATA SCOPUS KAFE</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('account.meme.update', $datameme->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Kategory</label>
                                    <input type="text" name="name" value="{{ $datameme->name }}" placeholder="Nama Meme" minlength="5" class="form-control">
                                </div>
                            </div>
                        </div> -->

                        <div class="row">
                            <!-- <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Mulai</label>
                                    <input type="date" name="tanggal" value="{{ date('Y-m-d', strtotime($datameme->tanggal)) }}" class="form-control">
                                </div>
                            </div> -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Sesi</label>
                                    <select class="form-control" name="sesi" id="sesi" required>
                                        <option value="" disabled selected>-- PILIH SESI --</option>
                                        <option value="sesi 1" {{ $datameme->sesi == 'sesi 1' ? 'selected' : '' }}>SESI 1</option>
                                        <option value="sesi 2" {{ $datameme->sesi == 'sesi 2' ? 'selected' : '' }}>SESI 2</option>
                                        <!-- <option value="sesi 3" {{ $datameme->sesi == 'sesi 3' ? 'selected' : '' }}>SESI 3</option> -->
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Mulai</label>
                                    <div class="input-group">
                                        <input type="text" name="waktu_mulai" id="waktu_mulai" value="{{ date('H:i', strtotime($datameme->waktu_mulai)) }}" placeholder="Masukkan Waktu Mulai Per Sesi" class="form-control" readonly>
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
                                        <input type="text" name="waktu_selesai" id="waktu_selesai" value="{{ date('H:i', strtotime($datameme->waktu_selesai)) }}" placeholder="Masukkan Waktu Selesai Per Sesi" class="form-control" readonly>
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
                                    <input type="number" name="kuota" value="{{ $datameme->kuota }}" placeholder="Masukkan Jumlah Kuota Per Sesi" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya Per Sesi</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="biaya" id="biaya" value="{{ $datameme->biaya }}" placeholder="Masukkan Jumlah Biaya Per Sesi" class="form-control currency" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Lokasi</label>
                                    <input type="text" name="lokasi" value="{{ $datameme->lokasi }}" placeholder="Masukkan Lokasi" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="status" required>
                                        <option value="" disabled selected>-- PILIH STATUS --</option>
                                        <option value="publish" {{ $datameme->status == 'publish' ? 'selected' : '' }}>PUBLISH</option>
                                        <option value="draft" {{ $datameme->status == 'draft' ? 'selected' : '' }}>DRAFT</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Deskripsi</label>
                                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5">{{ $datameme->deskripsi }}</textarea>
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
                                    <div class="card" style="width: 18rem; height: 250px; overflow: hidden; border: 2px dashed #000;">
                                        @if ($datameme->gambar == null)
                                        <img alt="image" id="image-preview" src="{{ asset('assets/img/meme/no-image.jpg') }}" class="img-thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                        <img id="image-preview" style="width: 100%; height: 100%; object-fit: cover; object-position: top;" class="card-img-top" src="{{ asset('assets/img/meme/' . $datameme->gambar) }}" alt="Preview Image">
                                        @endif
                                    </div>
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
    const imageInput = document.getElementById('gambar');
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