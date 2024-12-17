@extends('layouts.account')

@section('title')
Update Data Paper | MIS
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
            <form action="{{ route('account.paperisasi.update', $datas->id) }}" method="POST" enctype="multipart/form-data">
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
                                    <label>Nama First Author</label>
                                    <div class="input-group">
                                        <input type="text" name="first_author" value="{{ $datas->first_author }}" placeholder="Masukkan Nama First Author" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Affiliasi First Author</label>
                                    <div class="input-group">
                                        <input type="text" name="affiliasi_first_author" value="{{ $datas->affiliasi_first_author }}" placeholder="Masukkan Affiliasi First Author" class="form-control" required>
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
                                        <option value="antrian paper" {{ $datas->status_paper == 'antrian paper' ? 'selected' : '' }}>ANTRIAN PAPER</option>
                                        <option value="paper masuk" {{ $datas->status_paper == 'paper masuk' ? 'selected' : '' }}>PAPER MASUK</option>
                                        <option value="pengerjaan paper" {{ $datas->status_paper == 'pengerjaan paper' ? 'selected' : '' }}>PENGERJAAN PAPER</option>
                                        <option value="submit paper" {{ $datas->status_paper == 'submit paper' ? 'selected' : '' }}>SUBMIT PAPER</option>
                                        <option value="revisi paper" {{ $datas->status_paper == 'revisi paper' ? 'selected' : '' }}>REVISI PAPER</option>
                                        <option value="resubmit paper" {{ $datas->status_paper == 'resubmit paper' ? 'selected' : '' }}>RESUBMIT PAPER</option>
                                        <option value="paper selesai" {{ $datas->status_paper == 'paper selesai' ? 'selected' : '' }}>PAPER SELESAI</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
        </div>
        <!--================== END ==================-->

        <!--================== PROGRES KERANGKA ANATOMY ==================-->
        <!--================== FIRST CARD ==================-->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>PROGRES KERANGKA ANATOMY</h4>
                @if( $datas->progres_anatomi_tanggal_second === null || $datas->progres_anatomi_tanggal_third === null)
                <div>
                    <button type="button" class="btn btn-info" id="AddAnatomy" style="height: 40px; white-space: nowrap;">
                        <i class="fas fa-plus"></i> INPUT
                    </button>
                </div>
                @endif
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
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 50%; background-color:#888">
                            @if($datas->file_anatomi)
                            <!-- Display the file name and a download link -->
                            <div class="text-center">
                                <p style="color: #fff;">Uploaded File: {{ basename($datas->file_anatomi) }}</p>
                                <a href="{{ asset($datas->file_anatomi) }}" class="btn btn-sm btn-primary" download>
                                    <i class="fas fa-download"></i> Download File
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 100%;">
                            <div id="imagePreview-anatomy" class="image-preview"></div>
                            <span id="file-selected-anatomy" class="file-info-anatomy">
                                <p class="text-center">No file uploaded .</p>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--================== END FIRST CARD ==================-->

        <!--================== SECOND CARD ==================-->
        @if($datas->progres_anatomi_tanggal_second)
        <div class="card Anatomy-Card-Second">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>PROGRES KERANGKA ANATOMY SECOND</h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal Pengerjaan</label>
                            <div class="input-group">
                                <input type="datetime-local" name="progres_anatomi_tanggal_second" value="{{ $datas->progres_anatomi_tanggal_second }}" placeholder="Masukkan Tanggal Pengerjaan Anatomy" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Estimasi Pengerjaan </label>
                            <div class="input-group">
                                <input type="text" name="progres_anatomi_estimasi_second" value="{{ $datas->progres_anatomi_estimasi_second }}" placeholder="Masukkan Estimasi Pengerjaan" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Keterangan</label>
                            <div class="input-group">
                                <input type="text" name="progres_anatomi_keterangan_second" value="{{ $datas->progres_anatomi_keterangan_second }}" placeholder="Masukkan Keterangan" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status Anatomy</label>
                            <select class="form-control" name="progres_anatomi_status_second">
                                <option value="" disabled selected>-- PILIH STATUS ANATOMY --</option>
                                <option value="in progress anatomy" {{ $datas->progres_anatomi_status_second == 'in progress anatomy' ? 'selected' : '' }}>PENGERJAAN ANATOMY</option>
                                <option value="revisi anatomy" {{ $datas->progres_anatomi_status_second == 'revisi anatomy' ? 'selected' : '' }}>REVISI ANATOMY</option>
                                <option value="done anatomy" {{ $datas->progres_anatomi_status_second == 'done anatomy' ? 'selected' : '' }}>ANATOMY SELESAI</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group custom-file-upload-anatomy">
                            <label for="file_anatomi_second" class="form-label">File Anatomy Second</label>
                            <div class="input-group">
                                <input type="file" name="file_anatomi_second" id="file_anatomi_second" class="inputfile" hidden>
                                <label for="file_anatomi_second" class="file-upload-anatomy">
                                    <i class="fas fa-cloud-upload-alt"></i> Choose File Anatomy Second
                                </label>
                            </div>
                        </div>
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 50%; background-color:#888;">
                            @if($datas->file_anatomi_second)
                            <div class="text-center">
                                <p style="color: #fff;">Uploaded File: {{ basename($datas->file_anatomi_second) }}</p>
                                <a href="{{ asset($datas->file_anatomi_second) }}" class="btn btn-sm btn-primary" download>
                                    <i class="fas fa-download"></i> Download File
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 100%;">
                            <div id="imagePreview-anatomy-second" class="image-preview"></div>
                            <span id="file-selected-anatomy-second" class="file-info-anatomy">
                                <p class="text-center">No file uploaded .</p>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="card Anatomy-Card-Second" style="display: none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>PROGRES KERANGKA ANATOMY SECOND</h4>
                <button type="button" class="btn btn-danger" id="RemoveAnatomyCardSecond" style="height: 40px; white-space: nowrap;">
                    <i class="fas fa-trash"></i> HAPUS
                </button>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal Pengerjaan</label>
                            <div class="input-group">
                                <input type="datetime-local" name="progres_anatomi_tanggal_second" value="{{ $datas->progres_anatomi_tanggal_second }}" placeholder="Masukkan Tanggal Pengerjaan Anatomy" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Estimasi Pengerjaan </label>
                            <div class="input-group">
                                <input type="text" name="progres_anatomi_estimasi_second" value="{{ $datas->progres_anatomi_estimasi_second }}" placeholder="Masukkan Estimasi Pengerjaan" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Keterangan</label>
                            <div class="input-group">
                                <input type="text" name="progres_anatomi_keterangan_second" value="{{ $datas->progres_anatomi_keterangan_second }}" placeholder="Masukkan Keterangan" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status Anatomy</label>
                            <select class="form-control" name="progres_anatomi_status_second">
                                <option value="" disabled selected>-- PILIH STATUS ANATOMY --</option>
                                <option value="in progress anatomy" {{ $datas->progres_anatomi_status_second == 'in progress anatomy' ? 'selected' : '' }}>PENGERJAAN ANATOMY</option>
                                <option value="revisi anatomy" {{ $datas->progres_anatomi_status_second == 'revisi anatomy' ? 'selected' : '' }}>REVISI ANATOMY</option>
                                <option value="done anatomy" {{ $datas->progres_anatomi_status_second == 'done anatomy' ? 'selected' : '' }}>ANATOMY SELESAI</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group custom-file-upload-anatomy">
                            <label for="file_anatomi_second" class="form-label">File Anatomy Second</label>
                            <div class="input-group">
                                <input type="file" name="file_anatomi_second" id="file_anatomi_second" class="inputfile" hidden>
                                <label for="file_anatomi_second" class="file-upload-anatomy">
                                    <i class="fas fa-cloud-upload-alt"></i> Choose File Anatomy Second
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 100%;">
                            <div id="imagePreview-anatomy-second" class="image-preview"></div>
                            <span id="file-selected-anatomy-second" class="file-info-anatomy">
                                <p class="text-center">No file uploaded .</p>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!--================== END SECOND CARD ==================-->

        <!--================== THIRD CARD ==================-->
        @if($datas->progres_anatomi_tanggal_third)
        <div class="card Anatomy-Card-Third">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>PROGRES KERANGKA ANATOMY THIRD</h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal Pengerjaan</label>
                            <div class="input-group">
                                <input type="datetime-local" name="progres_anatomi_tanggal_third" value="{{ $datas->progres_anatomi_tanggal_third }}" placeholder="Masukkan Tanggal Pengerjaan Anatomy" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Estimasi Pengerjaan </label>
                            <div class="input-group">
                                <input type="text" name="progres_anatomi_estimasi_third" value="{{ $datas->progres_anatomi_estimasi_third }}" placeholder="Masukkan Estimasi Pengerjaan" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Keterangan</label>
                            <div class="input-group">
                                <input type="text" name="progres_anatomi_keterangan_third" value="{{ $datas->progres_anatomi_keterangan_third }}" placeholder="Masukkan Keterangan" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status Aantomy</label>
                            <select class="form-control" name="progres_anatomi_status_third">
                                <option value="" disabled selected>-- PILIH STATUS ANATOMY --</option>
                                <option value="in progress anatomy" {{ $datas->progres_anatomi_status_third == 'in progress anatomy' ? 'selected' : '' }}>PENGERJAAN ANATOMY</option>
                                <option value="revisi anatomy" {{ $datas->progres_anatomi_status_third == 'revisi anatomy' ? 'selected' : '' }}>REVISI ANATOMY</option>
                                <option value="done anatomy" {{ $datas->progres_anatomi_status_third == 'done anatomy' ? 'selected' : '' }}>ANATOMY SELESAI</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group custom-file-upload-anatomy">
                            <label for="file_anatomi_third" class="form-label">File Anatomy Third</label>
                            <div class="input-group">
                                <input type="file" name="file_anatomi_third" id="file_anatomi_third" class="inputfile" hidden>
                                <label for="file_anatomi_third" class="file-upload-anatomy">
                                    <i class="fas fa-cloud-upload-alt"></i> Choose File Anatomy Third
                                </label>
                            </div>
                        </div>
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 50%; background-color:#888;">
                            @if($datas->file_anatomi_third)
                            <!-- Display the file name and a download link -->
                            <div class="text-center">
                                <p style="color: #fff;">Uploaded File: {{ basename($datas->file_anatomi_third) }}</p>
                                <a href="{{ asset($datas->file_anatomi_third) }}" class="btn btn-sm btn-primary" download>
                                    <i class="fas fa-download"></i> Download File
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 100%;">
                            <div id="imagePreview-anatomy-third" class="image-preview"></div>
                            <span id="file-selected-anatomy-third" class="file-info-anatomy">
                                <p class="text-center">No file uploaded.</p>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="card Anatomy-Card-Third" style="display: none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>PROGRES KERANGKA ANATOMY THIRD</h4>
                <button type="button" class="btn btn-danger" id="RemoveAnatomyCardThird" style="height: 40px; white-space: nowrap;">
                    <i class="fas fa-trash"></i> HAPUS
                </button>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal Pengerjaan</label>
                            <div class="input-group">
                                <input type="datetime-local" name="progres_anatomi_tanggal_third" value="{{ $datas->progres_anatomi_tanggal_third }}" placeholder="Masukkan Tanggal Pengerjaan Anatomy" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Estimasi Pengerjaan </label>
                            <div class="input-group">
                                <input type="text" name="progres_anatomi_estimasi_third" value="{{ $datas->progres_anatomi_estimasi_third }}" placeholder="Masukkan Estimasi Pengerjaan" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Keterangan</label>
                            <div class="input-group">
                                <input type="text" name="progres_anatomi_keterangan_third" value="{{ $datas->progres_anatomi_keterangan_third }}" placeholder="Masukkan Keterangan" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status Aantomy</label>
                            <select class="form-control" name="progres_anatomi_status_third">
                                <option value="" disabled selected>-- PILIH STATUS ANATOMY --</option>
                                <option value="in progress anatomy" {{ $datas->progres_anatomi_status_third == 'in progress anatomy' ? 'selected' : '' }}>PENGERJAAN ANATOMY</option>
                                <option value="revisi anatomy" {{ $datas->progres_anatomi_status_third == 'revisi anatomy' ? 'selected' : '' }}>REVISI ANATOMY</option>
                                <option value="done anatomy" {{ $datas->progres_anatomi_status_third == 'done anatomy' ? 'selected' : '' }}>ANATOMY SELESAI</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group custom-file-upload-anatomy">
                            <label for="file_anatomi_third" class="form-label">File Anatomy Third</label>
                            <div class="input-group">
                                <input type="file" name="file_anatomi_third" id="file_anatomi_third" class="inputfile" hidden>
                                <label for="file_anatomi_third" class="file-upload-anatomy">
                                    <i class="fas fa-cloud-upload-alt"></i> Choose File Anatomy Third
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 100%;">
                            <div id="imagePreview-anatomy-third" class="image-preview"></div>
                            <span id="file-selected-anatomy-third" class="file-info-anatomy">
                                <p class="text-center">No file uploaded.</p>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!--================== END THIRD CARD ==================-->
        <!--================== END PROGRES KERANGKA ANATOMY ==================-->

        <!--================== PROGRES PAPER ==================-->
        <!--================== FIRST CARD ==================-->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>PROGRES PAPER</h4>
                @if( $datas->progres_paper_tanggal_second === null || $datas->progres_paper_tanggal_third === null)
                <div>
                    <button type="button" class="btn btn-info" id="AddPaper" style="height: 40px; white-space: nowrap;">
                        <i class="fas fa-plus"></i> INPUT
                    </button>
                </div>
                @endif
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
                            <label>Status Paper</label>
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
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 50%; background-color:#888;">
                            @if($datas->file_paper)
                            <!-- Display the file name and a download link -->
                            <div class="text-center">
                                <p style="color: #fff;">Uploaded File: {{ basename($datas->file_paper) }}</p>
                                <a href="{{ asset($datas->file_paper) }}" class="btn btn-sm btn-primary" download>
                                    <i class="fas fa-download"></i> Download File
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 100%;">
                            <div id="imagePreview-paper" class="image-preview"></div>
                            <span id="file-selected-paper" class="file-info-paper">
                                <p class="text-center">No file uploaded.</p>
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!--================== END FIRST CARD ==================-->

        <!--================== SECOND CARD ==================-->
        @if($datas->progres_paper_tanggal_second)
        <div class="card Paper-Card-Second">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>PROGRES PAPER SECOND</h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal Pengerjaan</label>
                            <div class="input-group">
                                <input type="datetime-local" name="progres_paper_tanggal_second" value="{{ $datas->progres_paper_tanggal_second }}" placeholder="Masukkan Tanggal Pengerjaan Paper" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Estimasi Pengerjaan </label>
                            <div class="input-group">
                                <input type="text" name="progres_paper_estimasi_second" value="{{ $datas->progres_paper_estimasi_second }}" placeholder="Masukkan Estimasi Pengerjaan" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Keterangan</label>
                            <div class="input-group">
                                <input type="text" name="progres_paper_keterangan_second" value="{{ $datas->progres_paper_keterangan_second }}" placeholder="Masukkan Keterangan" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status Paper</label>
                            <select class="form-control" name="progres_paper_status_second">
                                <option value="" disabled selected>-- PILIH STATUS PAPER --</option>
                                <option value="in progress paper" {{ $datas->progres_paper_status_second == 'in progress paper' ? 'selected' : '' }}>PENGERJAAN PAPER</option>
                                <option value="revisi paper" {{ $datas->progres_paper_status_second == 'revisi paper' ? 'selected' : '' }}>REVISI PAPER</option>
                                <option value="done paper" {{ $datas->progres_paper_status_second == 'done paper' ? 'selected' : '' }}>PAPER SELESAI</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group custom-file-upload-paper">
                            <label for="file_paper_second" class="form-label">File Paper Second</label>
                            <div class="input-group">
                                <input type="file" name="file_paper_second" id="file_paper_second" class="inputfile" hidden>
                                <label for="file_paper_second" class="file-upload-paper">
                                    <i class="fas fa-cloud-upload-alt"></i> Choose File Paper Second
                                </label>
                            </div>
                        </div>
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 50%; background-color:#888;">
                            @if($datas->file_paper_second)
                            <!-- Display the file name and a download link -->
                            <div class="text-center">
                                <p style="color: #fff;">Uploaded File: {{ basename($datas->file_paper_second) }}</p>
                                <a href="{{ asset($datas->file_paper_second) }}" class="btn btn-sm btn-primary" download>
                                    <i class="fas fa-download"></i> Download File
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 100%;">
                            <div id="imagePreview-paper-second" class="image-preview"></div>
                            <span id="file-selected-paper-second" class="file-info-paper">
                                <p class="text-center">No file uploaded.</p>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="card Paper-Card-Second" style="display: none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>PROGRES PAPER SECOND</h4>
                <button type="button" class="btn btn-danger" id="RemovePaperCardSecond" style="height: 40px; white-space: nowrap;">
                    <i class="fas fa-trash"></i> HAPUS
                </button>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal Pengerjaan</label>
                            <div class="input-group">
                                <input type="datetime-local" name="progres_paper_tanggal_second" value="{{ $datas->progres_paper_tanggal_second }}" placeholder="Masukkan Tanggal Pengerjaan Paper" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Estimasi Pengerjaan </label>
                            <div class="input-group">
                                <input type="text" name="progres_paper_estimasi_second" value="{{ $datas->progres_paper_estimasi_second }}" placeholder="Masukkan Estimasi Pengerjaan" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Keterangan</label>
                            <div class="input-group">
                                <input type="text" name="progres_paper_keterangan_second" value="{{ $datas->progres_paper_keterangan_second }}" placeholder="Masukkan Keterangan" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status Paper</label>
                            <select class="form-control" name="progres_paper_status_second">
                                <option value="" disabled selected>-- PILIH STATUS PAPER --</option>
                                <option value="in progress paper" {{ $datas->progres_paper_status_second == 'in progress paper' ? 'selected' : '' }}>PENGERJAAN PAPER</option>
                                <option value="revisi paper" {{ $datas->progres_paper_status_second == 'revisi paper' ? 'selected' : '' }}>REVISI PAPER</option>
                                <option value="done paper" {{ $datas->progres_paper_status_second == 'done paper' ? 'selected' : '' }}>PAPER SELESAI</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group custom-file-upload-paper">
                            <label for="file_paper_second" class="form-label">File Paper Second</label>
                            <div class="input-group">
                                <input type="file" name="file_paper_second" id="file_paper_second" class="inputfile" hidden>
                                <label for="file_paper_second" class="file-upload-paper">
                                    <i class="fas fa-cloud-upload-alt"></i> Choose File Paper Second
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 100%;">
                            <div id="imagePreview-paper-second" class="image-preview"></div>
                            <span id="file-selected-paper-second" class="file-info-paper">
                                <p class="text-center">No file uploaded.</p>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!--================== END SECOND CARD ==================-->

        <!--================== THIRD CARD ==================-->
        @if($datas->progres_paper_tanggal_third)
        <div class="card Paper-Card-Third">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>PROGRES PAPER THIRD</h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal Pengerjaan</label>
                            <div class="input-group">
                                <input type="datetime-local" name="progres_paper_tanggal_third" value="{{ $datas->progres_paper_tanggal_third }}" placeholder="Masukkan Tanggal Pengerjaan Paper" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Estimasi Pengerjaan </label>
                            <div class="input-group">
                                <input type="text" name="progres_paper_estimasi_third" value="{{ $datas->progres_paper_estimasi_third }}" placeholder="Masukkan Estimasi Pengerjaan" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Keterangan</label>
                            <div class="input-group">
                                <input type="text" name="progres_paper_keterangan_third" value="{{ $datas->progres_paper_keterangan_third }}" placeholder="Masukkan Keterangan" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status Paper</label>
                            <select class="form-control" name="progres_paper_status_third">
                                <option value="" disabled selected>-- PILIH STATUS PAPER --</option>
                                <option value="in progress paper" {{ $datas->progres_paper_status_third == 'in progress paper' ? 'selected' : '' }}>PENGERJAAN PAPER</option>
                                <option value="revisi paper" {{ $datas->progres_paper_status_third == 'revisi paper' ? 'selected' : '' }}>REVISI PAPER</option>
                                <option value="done paper" {{ $datas->progres_paper_status_third == 'done paper' ? 'selected' : '' }}>PAPER SELESAI</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group custom-file-upload-paper">
                            <label for="file_paper_third" class="form-label">File Paper Third</label>
                            <div class="input-group">
                                <input type="file" name="file_paper_third" id="file_paper_third" class="inputfile" hidden>
                                <label for="file_paper_third" class="file-upload-paper">
                                    <i class="fas fa-cloud-upload-alt"></i> Choose File Paper Third
                                </label>
                            </div>
                        </div>
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 50%; background-color:#888;">
                            @if($datas->file_paper_third)
                            <!-- Display the file name and a download link -->
                            <div class="text-center">
                                <p style="color: #fff;">Uploaded File: {{ basename($datas->file_paper_third) }}</p>
                                <a href="{{ asset($datas->file_paper_third) }}" class="btn btn-sm btn-primary" download>
                                    <i class="fas fa-download"></i> Download File
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 100%;">
                            <div id="imagePreview-paper-third" class="image-preview"></div>
                            <span id="file-selected-paper-third" class="file-info-paper">
                                <p class="text-center">No file uploaded.</p>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="card Paper-Card-Third" style="display: none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>PROGRES PAPER THIRD</h4>
                <button type="button" class="btn btn-danger" id="RemovePaperCardThird" style="height: 40px; white-space: nowrap;">
                    <i class="fas fa-trash"></i> HAPUS
                </button>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal Pengerjaan</label>
                            <div class="input-group">
                                <input type="datetime-local" name="progres_paper_tanggal_third" value="{{ $datas->progres_paper_tanggal_third }}" placeholder="Masukkan Tanggal Pengerjaan Paper" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Estimasi Pengerjaan </label>
                            <div class="input-group">
                                <input type="text" name="progres_paper_estimasi_third" value="{{ $datas->progres_paper_estimasi_third }}" placeholder="Masukkan Estimasi Pengerjaan" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Keterangan</label>
                            <div class="input-group">
                                <input type="text" name="progres_paper_keterangan_third" value="{{ $datas->progres_paper_keterangan_third }}" placeholder="Masukkan Keterangan" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status Paper</label>
                            <select class="form-control" name="progres_paper_status_third">
                                <option value="" disabled selected>-- PILIH STATUS PAPER --</option>
                                <option value="in progress paper" {{ $datas->progres_paper_status_third == 'in progress paper' ? 'selected' : '' }}>PENGERJAAN PAPER</option>
                                <option value="revisi paper" {{ $datas->progres_paper_status_third == 'revisi paper' ? 'selected' : '' }}>REVISI PAPER</option>
                                <option value="done paper" {{ $datas->progres_paper_status_third == 'done paper' ? 'selected' : '' }}>PAPER SELESAI</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group custom-file-upload-paper">
                            <label for="file_paper_third" class="form-label">File Paper Third</label>
                            <div class="input-group">
                                <input type="file" name="file_paper_third" id="file_paper_third" class="inputfile" hidden>
                                <label for="file_paper_third" class="file-upload-paper">
                                    <i class="fas fa-cloud-upload-alt"></i> Choose File Paper Third
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 100%;">
                            <div id="imagePreview-paper-third" class="image-preview"></div>
                            <span id="file-selected-paper-third" class="file-info-paper">
                                <p class="text-center">No file uploaded.</p>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!--================== END THIRD CARD ==================-->
        <!--================== END PROGRES PAPER ==================-->

        <div class="d-flex mt-3">
            <button class="btn btn-primary mr-1 btn-submit" type="submit" style="flex: 1; height:40px; font-size: 15px;"><i class="fa fa-paper-plane"></i> SIMPAN</button>
            <button class="btn btn-warning btn-reset" type="reset" style="flex: 1; height:40px; font-size: 15px;"><i class="fa fa-redo"></i> RESET</button>
        </div>

        </form>

</div>
</section>
</div>

<!--================== UPLOAD FILE WITH VIEW ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function handleFileUpload(inputId, previewId, fileInfoId, allowedTypes, maxFileSizeMB, alertFileSizeMB) {
        document.getElementById(inputId).addEventListener('change', function(event) {
            var fileInput = event.target;
            var file = fileInput.files[0];
            var fileName = file.name;
            var fileSizeKB = (file.size / 1024).toFixed(2); // Ukuran file dalam KB
            var fileSizeMB = (file.size / (1024 * 1024)).toFixed(2); // Ukuran file dalam MB

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

            // SweetAlert jika file melebihi alertFileSizeMB (2 MB)
            if (file.size > alertFileSizeMB * 1024 * 1024) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Ukuran file besar',
                    text: `Ukuran file ini adalah ${fileSizeMB} MB, yang melebihi ${alertFileSizeMB} MB. Harap pertimbangkan untuk mengunggah file yang lebih kecil.`
                });
            }

            // Validasi ukuran maksimum file
            if (file.size > maxFileSizeMB * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: `Ukuran file melebihi batas maksimum ${maxFileSizeMB} MB. Harap pilih file yang lebih kecil.`
                });
                fileInput.value = ""; // Reset input file
                document.getElementById(previewId).innerHTML = ""; // Reset preview
                document.getElementById(fileInfoId).innerHTML = ""; // Reset file info
                return;
            }

            // Tampilkan nama dan ukuran file
            var displaySize = fileSizeMB >= 1 ? `${fileSizeMB} MB` : `${fileSizeKB} KB`;
            document.getElementById(fileInfoId).innerHTML = `${fileName} (${displaySize})`;

            // Preview untuk non-gambar
            document.getElementById(previewId).innerHTML = '<span style="color: #555;">Preview tidak tersedia untuk tipe file ini.</span>';
        });
    }

    // Inisialisasi untuk file anatomy
    handleFileUpload(
        'file_anatomi',
        'imagePreview-anatomy',
        'file-selected-anatomy',
        ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        5, // Maksimal 5MB
        2 // Alert jika lebih dari 2MB
    );

    // Inisialisasi untuk file anatomy second
    handleFileUpload(
        'file_anatomi_second',
        'imagePreview-anatomy-second',
        'file-selected-anatomy-second',
        ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        5, // Maksimal 5MB
        2 // Alert jika lebih dari 2MB
    );

    // Inisialisasi untuk file anatomy third
    handleFileUpload(
        'file_anatomi_third',
        'imagePreview-anatomy-third',
        'file-selected-anatomy-third',
        ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        5, // Maksimal 5MB
        2 // Alert jika lebih dari 2MB
    );

    // Inisialisasi untuk file paper
    handleFileUpload(
        'file_paper',
        'imagePreview-paper',
        'file-selected-paper',
        ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        5, // Maksimal 5MB
        2 // Alert jika lebih dari 2MB
    );

    // Inisialisasi untuk file paper second
    handleFileUpload(
        'file_paper_second',
        'imagePreview-paper-second',
        'file-selected-paper-second',
        ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        5, // Maksimal 5MB
        2 // Alert jika lebih dari 2MB
    );

    // Inisialisasi untuk file paper third
    handleFileUpload(
        'file_paper_third',
        'imagePreview-paper-third',
        'file-selected-paper-third',
        ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        5, // Maksimal 5MB
        2 // Alert jika lebih dari 2MB
    );
</script>

<!--================== END ==================-->

<!--================== ADD AND REMOVE CARD ANATOMY ==================-->
<script>
    $(document).ready(function() {

        var AnatomyCounter = 0;

        $('#AddAnatomy').on('click', function() {
            if (AnatomyCounter === 0) {
                $('.Anatomy-Card-Second').show();
                $('#RemoveAnatomyCardSecond').show();
            } else if (AnatomyCounter === 1) {
                $('.Anatomy-Card-Third').show();
                $('#RemoveAnatomyCardThird').show();
                // Sembunyikan tombol AddAnatomy setelah Anatomy-Card-Third ditampilkan
                $(this).hide();
            }
            AnatomyCounter++;
        });

        // Remove Anatomy-Card-Second
        $('#RemoveAnatomyCardSecond').on('click', function() {
            $('.Anatomy-Card-Second').hide();
            $('#RemoveAnatomyCardSecond').hide();
            AnatomyCounter--;

            // Tampilkan tombol AddAnatomy jika kurang dari 2 cards yang tampil
            if (AnatomyCounter < 2) {
                $('#AddAnatomy').show();
            }
        });

        // Remove Anatomy-Card-Third
        $('#RemoveAnatomyCardThird').on('click', function() {
            $('.Anatomy-Card-Third').hide();
            $('#RemoveAnatomyCardThird').hide();
            AnatomyCounter--;

            // Tampilkan tombol AddAnatomy jika kurang dari 2 cards yang tampil
            if (AnatomyCounter < 2) {
                $('#AddAnatomy').show();
            }
        });

    });
</script>
<!--================== END ==================-->

<!--================== ADD AND REMOVE CARD PAPER ==================-->
<script>
    $(document).ready(function() {

        var PaperCounter = 0;

        $('#AddPaper').on('click', function() {
            if (PaperCounter === 0) {
                $('.Paper-Card-Second').show();
                $('#RemovePaperCardSecond').show();
            } else if (PaperCounter === 1) {
                $('.Paper-Card-Third').show();
                $('#RemovePaperCardThird').show();
                // Sembunyikan tombol AddAnatomy setelah Anatomy-Card-Third ditampilkan
                $(this).hide();
            }
            PaperCounter++;
        });

        // Remove Anatomy-Card-Second
        $('#RemovePaperCardSecond').on('click', function() {
            $('.Paper-Card-Second').hide();
            $('#RemovePaperCardSecond').hide();
            PaperCounter--;

            // Tampilkan tombol AddAnatomy jika kurang dari 2 cards yang tampil
            if (PaperCounter < 2) {
                $('#AddPaper').show();
            }
        });

        // Remove Anatomy-Card-Third
        $('#RemovePaperCardThird').on('click', function() {
            $('.Paper-Card-Third').hide();
            $('#RemovePaperCardThird').hide();
            PaperCounter--;

            // Tampilkan tombol AddAnatomy jika kurang dari 2 cards yang tampil
            if (PaperCounter < 2) {
                $('#AddPaper').show();
            }
        });

    });
</script>
<!--================== END ==================-->

<script>
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
            $("#karyawanSelect").val('');
        }, 500);
    })
</script>

@stop