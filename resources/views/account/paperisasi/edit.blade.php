@extends('layouts.account')

@section('title')
Tambah Data Paper | MIS
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
        display: none;
    }

    .image-preview img {
        max-width: 100%;
        height: auto;
        border-radius: 5px;
    }
</style>
<!--================== END ==================-->

<!--================== CARD VIEW UPLOAD FILE ==================-->
<style>
    /* Container styling - Anatomy */
    .custom-file-upload-anatomy {
        position: relative;
    }

    .file-upload-anatomy {
        display: inline-block;
        background: #28a745;
        color: #fff;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: 500;
        text-align: center;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .file-upload-anatomy i {
        margin-right: 8px;
        font-size: 18px;
    }

    .file-upload-anatomy:hover {
        background: #218838;
    }

    .image-preview-container-anatomy {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 2px dashed #28a745;
        padding: 15px;
        border-radius: 5px;
        min-height: 150px;
    }

    .file-info-anatomy {
        margin-top: 10px;
        font-size: 14px;
        color: #555;
        text-align: center;
    }

    /* Container styling - Paper */
    .custom-file-upload-paper {
        position: relative;
    }

    .file-upload-paper {
        display: inline-block;
        background: #007bff;
        color: #fff;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: 500;
        text-align: center;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .file-upload-paper i {
        margin-right: 8px;
        font-size: 18px;
    }

    .file-upload-paper:hover {
        background: #0056b3;
    }

    .image-preview-container-paper {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 2px dashed #007bff;
        padding: 15px;
        border-radius: 5px;
        min-height: 150px;
    }

    .file-info-paper {
        margin-top: 10px;
        font-size: 14px;
        color: #555;
        text-align: center;
    }

    .inputfile {
        display: none;
    }

    /* Responsive styling */
    @media (max-width: 768px) {

        .image-preview-container-anatomy,
        .image-preview-container-paper {
            min-height: 100px;
        }

        .file-upload-anatomy,
        .file-upload-paper {
            font-size: 14px;
            padding: 8px 15px;
        }
    }
</style>
<!--================== END ==================-->

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>TAMBAH DATA PAPER</h1>
        </div>

        <div class="section-body">
            <form action="{{ route('account.paperisasi.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!--================== DETAIL PAPER ==================-->
                <div class="card">
                    <div class="card-header">
                        <h4>DETAIL PAPER</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Masuk Paper</label>
                                    <div class="input-group">
                                        <input type="datetime-local" name="tanggal_masuk_paper" value="{{ $datas->tanggal_masuk_paper }}" placeholder="Masukkan Tanggal Masuk Paper" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Judul Paper</label>
                                    <div class="input-group">
                                        <input type="text" name="judul_paper" value="{{ $datas->judul_paper}}" placeholder="Masukkan Judul Paper" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama CO-Author</label>
                                    <div class="input-group">
                                        <input type="text" name="co_author" value="{{ $datas->co_author }}" placeholder="Masukkan Nama CO-Author" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Affiliasi CO-Author</label>
                                    <div class="input-group">
                                        <input type="text" name="affiliasi_co_author" value="{{ $datas->affiliasi_co_author }}" placeholder="Masukkan Affiliasi CO-Author" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Jurnal Target</label>
                                    <div class="input-group">
                                        <input type="text" name="jurnal_target" value="{{ $datas->jurnal_target }}" placeholder="Masukkan Nama Jurnal Target" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Quratile Jurnal</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Q-</span>
                                        </div>
                                        <input type="number" name="q_jurnal" value="{{ $datas->q_jurnal }}" placeholder="Masukkan Quartile Jurnal" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Estimasi Terbit</label>
                                    <div class="input-group">
                                        <input type="text" name="estimasi_terbit" value="{{ $datas->estimasi_terbit }}" placeholder="Masukkan Estimasi Terbit Paper" class="form-control" required>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Bulan</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>APC Jurnal</label>
                                    <div class="input-group">
                                        <input type="text" name="apc_jurnal" value="{{ $datas->apc_jurnal }}" placeholder="Masukkan APC Jurnal" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Status Paper</label>
                                    <select class="form-control" name="status_paper" required>
                                        <option value="" disabled selected>-- PILIH STATUS PAPER --</option>
                                        <option value="antrian" {{ $datas->status_paper == 'antrian' ? 'selected' : '' }}>ANTRIAN PAPER</option>
                                        <option value="diterima" {{ $datas->status_paper == 'diterima' ? 'selected' : '' }}>PAPER DITERIMA</option>
                                        <option value="in progress" {{ $datas->status_paper == 'in progress' ? 'selected' : '' }}>PENGERJAAN PAPER</option>
                                        <option value="submit" {{ $datas->status_paper == 'submit' ? 'selected' : '' }}>SUBMIT PAPER</option>
                                        <option value="revisi" {{ $datas->status_paper == 'revisi' ? 'selected' : '' }}>REVISI PAPER</option>
                                        <option value="resubmit" {{ $datas->status_paper == 'resubmit' ? 'selected' : '' }}>RESUBMIT PAPER</option>
                                        <option value="done" {{ $datas->status_paper == 'done' ? 'selected' : '' }}>PAPER SELESAI</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
        </div>
        <!--================== END ==================-->

        <!--================== PROGRES KERANGKA ANATOMY ==================-->
        <div class="card">
            <div class="card-header">
                <h4>PROGRES KERANGKA ANATOMY</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal Pengerjaan</label>
                            <div class="input-group">
                                <input type="datetime-local" name="progres_anatomi_tanggal" value="{{ $datas->progres_anatomi_tanggal }}" placeholder="Masukkan Tanggal Pengerjaan Anatomy" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Estimasi Pengerjaan </label>
                            <div class="input-group">
                                <input type="text" name="progres_anatomi_estimasi" value="{{ $datas->progres_anatomi_estimasi }}" placeholder="Masukkan Estimasi Pengerjaan" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Keterangan</label>
                            <div class="input-group">
                                <input type="text" name="progres_anatomi_keterangan" value="{{ $datas->progres_anatomi_keterangan }}" placeholder="Masukkan Keterangan" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status Aantomy</label>
                            <select class="form-control" name="progres_anatomi_status">
                                <option value="" disabled selected>-- PILIH STATUS ANATOMY --</option>
                                <option value="in progress anatomy" {{ $datas->progres_anatomi_status == 'in progress anatomy' ? 'selected' : '' }}>PENGERJAAN ANATOMY</option>
                                <option value="revisi anatomy" {{ $datas->progres_anatomi_status == 'revisi anatomy' ? 'selected' : '' }}>REVISI ANATOMY</option>
                                <option value="done anatomy" {{ $datas->progres_anatomi_status == 'done anatomy' ? 'selected' : '' }}>ANATOMY SELESAI</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group custom-file-upload-anatomy">
                            <label for="file_anatomi" class="form-label">File Anatomy</label>
                            <div class="input-group">
                                <input type="file" name="file_anatomi" id="file_anatomi" class="inputfile" hidden>
                                <label for="file_anatomi" class="file-upload-anatomy">
                                    <i class="fas fa-cloud-upload-alt"></i> Choose File Anatomy
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="image-preview-container-anatomy">
                            <div id="imagePreview-anatomy" class="image-preview"></div>
                            <span id="file-selected-anatomy" class="file-info-anatomy"></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!--================== END ==================-->

        <!--================== PROGRES PAPER ==================-->
        <div class="card">
            <div class="card-header">
                <h4>PROGRES PAPER</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal Pengerjaan</label>
                            <div class="input-group">
                                <input type="datetime-local" name="progres_paper_tanggal" value="{{ $datas->progres_paper_tanggal }}" placeholder="Masukkan Tanggal Pengerjaan Paper" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Estimasi Pengerjaan </label>
                            <div class="input-group">
                                <input type="text" name="progres_paper_estimasi" value="{{ $datas->progres_paper_estimasi }}" placeholder="Masukkan Estimasi Pengerjaan" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Keterangan</label>
                            <div class="input-group">
                                <input type="text" name="progres_paper_keterangan" value="{{ $datas->progres_paper_keterangan }}" placeholder="Masukkan Keterangan" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status Aantomy</label>
                            <select class="form-control" name="progres_paper_status">
                                <option value="" disabled selected>-- PILIH STATUS PAPER --</option>
                                <option value="in progress paper" {{ $datas->progres_paper_status == 'in progress paper' ? 'selected' : '' }}>PENGERJAAN PAPER</option>
                                <option value="revisi paper" {{ $datas->progres_paper_status == 'revisi paper' ? 'selected' : '' }}>REVISI PAPER</option>
                                <option value="done paper" {{ $datas->progres_paper_status == 'done paper' ? 'selected' : '' }}>PAPER SELESAI</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group custom-file-upload-paper">
                            <label for="file_paper" class="form-label">File Paper</label>
                            <div class="input-group">
                                <input type="file" name="file_paper" id="file_paper" class="inputfile" hidden>
                                <label for="file_paper" class="file-upload-paper">
                                    <i class="fas fa-cloud-upload-alt"></i> Choose File Paper
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 100%;">
                            @if($datas->file_paper)
                            <!-- Display the file name and a download link -->
                            <div class="text-center">
                                <p>Uploaded File: {{ basename($datas->file_paper) }}</p>
                                <a href="{{ asset($datas->file_paper) }}" class="btn btn-sm btn-primary" download>
                                    <i class="fas fa-download"></i> Download File
                                </a>
                            </div>
                            @else
                            <p class="text-center">No file uploaded.</p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!--================== END ==================-->

        <div class="d-flex mt-3">
            <button class="btn btn-primary mr-1 btn-submit" type="submit" style="flex: 1; height:40px; font-size: 15px;"><i class="fa fa-paper-plane"></i> SIMPAN</button>
            <button class="btn btn-warning btn-reset" type="reset" style="flex: 1; height:40px; font-size: 15px;"><i class="fa fa-redo"></i> RESET</button>
        </div>

        </form>

</div>
</section>
</div>

<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function handleFileUpload(inputId, previewId, fileInfoId, allowedTypes) {
        document.getElementById(inputId).addEventListener('change', function(event) {
            var fileInput = event.target;
            var file = fileInput.files[0];
            var fileName = file.name;
            var fileSize = (file.size / 1024).toFixed(2); // Ukuran file dalam KB

            // Validasi tipe file
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Hanya file PDF, DOC, dan DOCX yang diizinkan. Harap pilih jenis file yang valid.'
                });
                fileInput.value = ""; // Reset input file
                document.getElementById(previewId).innerHTML = ""; // Reset preview
                document.getElementById(fileInfoId).innerHTML = ""; // Reset file info
                return;
            }

            // Validasi ukuran file
            if (file.size > 1024 * 10240) { // 10MB dalam byte
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Ukuran file melebihi batas maksimum 10 MB. Harap pilih file yang lebih kecil.'
                });
                fileInput.value = ""; // Reset input file
                document.getElementById(previewId).innerHTML = ""; // Reset preview
                document.getElementById(fileInfoId).innerHTML = ""; // Reset file info
                return;
            }

            // Pratinjau file
            if (file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(previewId).innerHTML = '<img src="' + e.target.result + '" alt="' + fileName + '" style="max-width: 100%; height: auto; border-radius: 5px;" />';
                };
                reader.readAsDataURL(file);
            } else {
                document.getElementById(previewId).innerHTML = '<span style="color: #555;">Preview tidak tersedia untuk file non-gambar.</span>';
            }

            // Menampilkan nama dan ukuran file
            document.getElementById(fileInfoId).innerHTML = fileName + ' (' + fileSize + ' KB)';
        });
    }

    // Inisialisasi untuk file anatomy
    handleFileUpload(
        'file_anatomi',
        'imagePreview-anatomy',
        'file-selected-anatomy',
        ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png']
    );

    // Inisialisasi untuk file paper
    handleFileUpload(
        'file_paper',
        'imagePreview-paper',
        'file-selected-paper',
        ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png']
    );
</script>
<!--================== END ==================-->

@stop