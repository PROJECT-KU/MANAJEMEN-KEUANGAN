@extends('layouts.account')

@section('title')
Update Data Pendafataran Scopus Kafe | MIS
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

<!--================== POSISI HEADER TEXT IN CARD ==================-->
<style>
    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-title {
        font-weight: bold;
        color: #696969;
        font-size: 20px;
        margin: 0;
    }

    .current-date {
        font-size: 16px;
        color: #696969;
    }
</style>
<!--================== END ==================-->

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>UPDATE DATA PENDAFTARAN</h1>
        </div>

        <div class="section-body">
            <form action="{{ route('account.pendaftaran-scopus-kafe.update', $datas->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!--================== DETAIL PAPER ==================-->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>DETAIL DATA DIRI</h4>
                        <span>ID Pesanan : {{ $datas->id_pemesanan }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Lengkap</label>
                                    <div class="input-group">
                                        <input type="text" name="nama" id="nama" value="{{ $datas->nama }}" placeholder="Masukkan Nama Lengkap" class="form-control">
                                    </div>
                                    @error('nama')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Pemesanan</label>
                                    <div class="input-group">
                                        <!-- Gunakan input type="date" untuk format tanggal -->
                                        <input type="date" name="tanggal_pemesanan" id="tanggal_pemesanan"
                                            value="{{ $datas->tanggal_pemesanan ? \Carbon\Carbon::parse($datas->tanggal_pemesanan)->format('Y-m-d') : '' }}"
                                            placeholder="Masukkan Tanggal Pemesanan" class="form-control">
                                    </div>
                                    @error('tanggal_pemesanan')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <div class="input-group">
                                        <input type="email" name="email" id="email" value="{{ $datas->email }}" placeholder="Masukkan Email Aktif" class="form-control" maxlength="100" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)">
                                    </div>
                                    @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Telp</label>
                                    <div class="input-group">
                                        <input type="text" name="telp" id="telp" value="{{ $datas->telp }}" placeholder="Masukkan Telp Aktif" class="form-control" maxlength="20" minlength="8" onkeypress="return event.charCode >= 48 && event.charCode <=57" oninput="formatPhoneNumber(this)">
                                    </div>
                                    @error('telp')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="status">
                                        <option value="menunggu verifikasi" {{ $datas->status == 'menunggu verifikasi' ? 'selected' : '' }}>MENUNGGU VERIFIKASI</option>
                                        <option value="pembayaran diterima" {{ $datas->status == 'pembayaran diterima' ? 'selected' : '' }}>PEMBAYARAN DITERIMA</option>
                                        <option value="pembayaran ditolak" {{ $datas->status == 'pembayaran ditolak' ? 'selected' : '' }}>PEMBAYARAN DITOLAK</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END -->

                <!-- PILIH SESI 1 -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>DETAIL FORM SESI</h4>
                        <button type="button" class="btn btn-info" id="AddformPertama" style="height: 40px; white-space: nowrap;">
                            <i class="fas fa-plus"></i> Tambah Sesi
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Sesi</label>
                                    <select class="form-control" name="sesi" id="sesi">
                                        <option value="" disabled selected>-- PILIH SESI --</option>
                                        <option value="sesi 1" {{ $datas->sesi == 'sesi 1' ? 'selected' : '' }}>SESI 1</option>
                                        <option value="sesi 2" {{ $datas->sesi == 'sesi 2' ? 'selected' : '' }}>SESI 2</option>
                                        <option value="sesi 3" {{ $datas->sesi == 'sesi 3' ? 'selected' : '' }}>SESI 3</option>
                                    </select>
                                    @error('sesi')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Mulai</label>
                                    <div class="input-group">
                                        <input type="text" name="waktu_mulai" id="waktu_mulai" value="{{ $datas->waktu_mulai }}" placeholder="Waktu Mulai Per Sesi" class="form-control" readonly>
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
                                        <input type="text" name="waktu_selesai" id="waktu_selesai" value="{{ $datas->waktu_selesai }}" placeholder="Waktu Selesai Per Sesi" class="form-control" readonly>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">WIB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Lokasi</label>
                                    <input type="text" name="lokasi" id="lokasi" value="{{ $datas->lokasi }}" placeholder="Lokasi Scopus Kafe" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya Per Sesi</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="biaya" id="biaya" value="{{ $datas->biaya }}" placeholder="Jumlah Biaya Per Sesi" class="form-control currency" readonly>
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
                                        <input type="text" name="kode_unik_pembayaran" id="kodeUnik" value="{{ $datas->kode_unik_pembayaran }}" placeholder="Kode Unik Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Subtotal Pembayaran</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="subtotal_pembayaran" id="TotalPembayaran" value="{{ $datas->subtotal_pembayaran }}" placeholder="Subtotal Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END SESI 1 -->

                <!-- PILIH SESI 2 -->
                @if ($datas->sesi_kedua !== null)
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>DETAIL FORM SESI KEDUA</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Sesi</label>
                                    <select class="form-control" name="sesi_kedua" id="sesi_kedua">
                                        <option value="" disabled selected>-- PILIH SESI --</option>
                                        <option value="sesi 1 kedua" {{ $datas->sesi_kedua == 'sesi 1 kedua' ? 'selected' : '' }}>SESI 1</option>
                                        <option value="sesi 2 kedua" {{ $datas->sesi_kedua == 'sesi 2 kedua' ? 'selected' : '' }}>SESI 2</option>
                                        <option value="sesi 3 kedua" {{ $datas->sesi_kedua == 'sesi 3 kedua' ? 'selected' : '' }}>SESI 3</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Mulai</label>
                                    <div class="input-group">
                                        <input type="text" name="waktu_mulai_kedua" id="waktu_mulai_kedua" value="{{ $datas->waktu_mulai_kedua }}" placeholder="Waktu Mulai Per Sesi" class="form-control" readonly>
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
                                        <input type="text" name="waktu_selesai_kedua" id="waktu_selesai_kedua" value="{{ $datas->waktu_selesai_kedua }}" placeholder="Waktu Selesai Per Sesi" class="form-control" readonly>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">WIB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Lokasi</label>
                                    <input type="text" name="lokasi_kedua" id="lokasi_kedua" value="{{ $datas->lokasi_kedua }}" placeholder="Lokasi Scopus Kafe" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya Per Sesi</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="biaya_kedua" id="biaya_kedua" value="{{ $datas->biaya_kedua }}" placeholder="Jumlah Biaya Per Sesi" class="form-control currency" readonly>
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
                                        <input type="text" name="kode_unik_pembayaran_kedua" id="kodeUnik_kedua" value="{{ $datas->kode_unik_pembayaran_kedua }}" placeholder="Kode Unik Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Subtotal Pembayaran</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="subtotal_pembayaran_kedua" id="TotalPembayaran_kedua" value="{{ $datas->subtotal_pembayaran_kedua }}" placeholder="Subtotal Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="card ScopusKafe-Card-Kedua" style="display: none;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>DETAIL FORM SESI KEDUA</h4>
                        <button type="button" class="btn btn-danger" id="RemoveformKedua" style="height: 40px; white-space: nowrap;">
                            <i class="fas fa-trash"></i> Hapus Sesi
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Sesi</label>
                                    <select class="form-control" name="sesi_kedua" id="sesi_kedua">
                                        <option value="" disabled selected>-- PILIH SESI --</option>
                                        <option value="sesi 1 kedua">SESI 1</option>
                                        <option value="sesi 2 kedua">SESI 2</option>
                                        <option value="sesi 3 kedua">SESI 3</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Mulai</label>
                                    <div class="input-group">
                                        <input type="text" name="waktu_mulai_kedua" id="waktu_mulai_kedua" value="" placeholder="Waktu Mulai Per Sesi" class="form-control" readonly>
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
                                        <input type="text" name="waktu_selesai_kedua" id="waktu_selesai_kedua" value="" placeholder="Waktu Selesai Per Sesi" class="form-control" readonly>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">WIB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Lokasi</label>
                                    <input type="text" name="lokasi_kedua" id="lokasi_kedua" value="" placeholder="Lokasi Scopus Kafe" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya Per Sesi</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="biaya_kedua" id="biaya_kedua" value="" placeholder="Jumlah Biaya Per Sesi" class="form-control currency" readonly>
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
                                        <input type="text" name="kode_unik_pembayaran_kedua" id="kodeUnik_kedua" value="" placeholder="Kode Unik Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Subtotal Pembayaran</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="subtotal_pembayaran_kedua" id="TotalPembayaran_kedua" value="" placeholder="Subtotal Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <!-- END SESI 2 -->

                <!-- PILIH SESI 3 -->
                @if ($datas->sesi_ketiga !== null)
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>DETAIL FORM SESI KETIGA</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Sesi</label>
                                    <select class="form-control" name="sesi_ketiga" id="sesi_ketiga">
                                        <option value="" disabled selected>-- PILIH SESI --</option>
                                        <option value="sesi 1 ketiga" {{ $datas->sesi_ketiga == 'sesi 1 ketiga' ? 'selected' : '' }}>SESI 1</option>
                                        <option value="sesi 2 ketiga" {{ $datas->sesi_ketiga == 'sesi 2 ketiga' ? 'selected' : '' }}>SESI 2</option>
                                        <option value="sesi 3 ketiga" {{ $datas->sesi_ketiga == 'sesi 3 ketiga' ? 'selected' : '' }}>SESI 3</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Mulai</label>
                                    <div class="input-group">
                                        <input type="text" name="waktu_mulai_ketiga" id="waktu_mulai_ketiga" value="{{ $datas->waktu_mulai_ketiga }}" placeholder="Waktu Mulai Per Sesi" class="form-control" readonly>
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
                                        <input type="text" name="waktu_selesai_ketiga" id="waktu_selesai_ketiga" value="{{ $datas->waktu_selesai_ketiga }}" placeholder="Waktu Selesai Per Sesi" class="form-control" readonly>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">WIB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Lokasi</label>
                                    <input type="text" name="lokasi_ketiga" id="lokasi_ketiga" value="{{ $datas->lokasi_ketiga }}" placeholder="Lokasi Scopus Kafe" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya Per Sesi</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="biaya_ketiga" id="biaya_ketiga" value="{{ $datas->biaya_ketiga }}" placeholder="Jumlah Biaya Per Sesi" class="form-control currency" readonly>
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
                                        <input type="text" name="kode_unik_pembayaran_ketiga" id="kodeUnik_ketiga" value="{{ $datas->kode_unik_pembayaran_ketiga }}" placeholder="Kode Unik Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Subtotal Pembayaran</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="subtotal_pembayaran_ketiga" id="TotalPembayaran_ketiga" value="{{ $datas->subtotal_pembayaran_ketiga }}" placeholder="Subtotal Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="card ScopusKafe-Card-Ketiga" style="display: none;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>DETAIL FORM SESI KETIGA</h4>
                        <button type="button" class="btn btn-danger" id="RemoveformKetiga" style="height: 40px; white-space: nowrap;">
                            <i class="fas fa-trash"></i> Hapus Sesi
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Sesi</label>
                                    <select class="form-control" name="sesi_ketiga" id="sesi_ketiga">
                                        <option value="" disabled selected>-- PILIH SESI --</option>
                                        <option value="sesi 1 ketiga">SESI 1</option>
                                        <option value="sesi 2 ketiga">SESI 2</option>
                                        <option value="sesi 3 ketiga">SESI 3</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Waktu Mulai</label>
                                    <div class="input-group">
                                        <input type="text" name="waktu_mulai_ketiga" id="waktu_mulai_ketiga" value="" placeholder="Waktu Mulai Per Sesi" class="form-control" readonly>
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
                                        <input type="text" name="waktu_selesai_ketiga" id="waktu_selesai_ketiga" value="" placeholder="Waktu Selesai Per Sesi" class="form-control" readonly>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">WIB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Lokasi</label>
                                    <input type="text" name="lokasi_ketiga" id="lokasi_ketiga" value="" placeholder="Lokasi Scopus Kafe" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya Per Sesi</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="biaya_ketiga" id="biaya_ketiga" value="" placeholder="Jumlah Biaya Per Sesi" class="form-control currency" readonly>
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
                                        <input type="text" name="kode_unik_pembayaran_ketiga" id="kodeUnik_ketiga" value="" placeholder="Kode Unik Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Subtotal Pembayaran</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="text" name="subtotal_pembayaran_ketiga" id="TotalPembayaran_ketiga" value="" placeholder="Subtotal Pembayaran" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <!-- END SESI 3 -->

                <div class="card" style="box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); overflow: hidden; margin-bottom: 20px;">
                    <div class="card-body" style="color: black;">

                        <!-- TOTAL KESELURUHAN -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body" style="color: black;">
                                        <h1 class="header-title mb-3">Upload Bukti Pembayaran</h1>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group custom-file-upload" style="margin-top: -3px;">
                                                    <label>Bukti Pembayaran</label>
                                                    <div class="input-group">
                                                        <input type="file" name="gambar" id="gambar" class="inputfile" accept="image/*">
                                                        <label for="gambar" class="file-upload">
                                                            <i class="fas fa-cloud-upload-alt"></i> Choose Image
                                                        </label>
                                                        @if ($datas->gambar == null)
                                                        <span>No Image Available</span>
                                                        @elseif ($datas->gambar !== null)
                                                        <span id="file-selected" style="color: black;">
                                                            {{ basename($datas->gambar) }}
                                                        </span>
                                                        @else
                                                        <span id="file-selected" style="color: black;"></span>
                                                        @endif
                                                        @error('gambar')
                                                        <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <a href="{{ asset($datas->gambar) }}" data-lightbox="{{ $datas->id }}">
                                                        <div class="card" style="width: 15rem; height: 250px; overflow: hidden; border: 2px dashed #000;">
                                                            @if ($datas->gambar == null)
                                                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
                                                            @else
                                                            <img id="image-preview" style="width: 100%; height: 100%; object-fit: cover; object-position: top;" class="card-img-top" src="{{ asset($datas->gambar) }}" alt="Preview Image">
                                                            @endif
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body" style="color: black; display: flex; justify-content: space-between; align-items: center;">
                                        <label style="white-space: nowrap; font-weight: bold;">Subtotal Keseluruhan :</label>
                                        <div style="display: flex; justify-content: flex-end; width: 100%;">
                                            <span style="margin-right: 5px;">Rp.</span>
                                            <span id="subtotalKeseluruhanPembayaran">{{ number_format($datas->total_keseluruhan_pembayaran, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <hr style="margin: 10px;">
                                    <div class="card-body" style="color: black; display: flex; justify-content: space-between; align-items: center;">
                                        <label style="white-space: nowrap; font-weight: bold;">Tax :</label>
                                        <div style="display: flex; justify-content: flex-end; width: 100%;">
                                            <span style="margin-right: 5px;">Rp.</span>
                                            <span>0</span>
                                        </div>
                                    </div>
                                    <hr style="margin: 10px;">
                                    <div class="card-body mb-3" style="color: black; display: flex; justify-content: space-between; align-items: center;">
                                        <label style="white-space: nowrap; font-weight: bold;">Total Keseluruhan :</label>
                                        <div style="display: flex; justify-content: flex-end; width: 100%;">
                                            <span style="margin-right: 5px;">Rp.</span>
                                            <span id="totalKeseluruhanPembayaran">{{ number_format($datas->total_keseluruhan_pembayaran, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="total_keseluruhan_pembayaran" id="totalKeseluruhanPembayaranhidden" value="{{ $datas->total_keseluruhan_pembayaran }}" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <!-- END TOTAL KESELURUHAN -->

                        <div class="d-flex">
                            <button class="btn btn-primary mr-1 btn-submit" type="submit" style="flex: 1; height:40px; font-size: 15px;"><i class="fa fa-paper-plane"></i> SIMPAN</button>
                            <button class="btn btn-warning btn-reset" type="reset" style="flex: 1; height:40px; font-size: 15px;"><i class="fa fa-redo"></i> RESET</button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </section>
</div>

<!--================== upload image ==================-->
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
<!--================== end ==================-->

<!--================== MENAMPILKAN WAKTU SESI OTOMATIS ==================-->
<script>
    // Format number to Rupiah
    function formatRupiah(number) {
        return Number(number).toLocaleString('id-ID');
    }

    // Generate a random unique code
    function generateUniqueCode() {
        return Math.floor(Math.random() * 1000) + 1; // Random number between 1 and 1000
    }

    // Fungsi untuk menangani perubahan pada setiap form
    function handleFormChange(sesiSelect, waktuMulaiInput, waktuSelesaiInput, lokasiInput, biayaInput, kodeUnikInput, totalPembayaranInput, sesiMap, updateTotalKeseluruhan) {
        sesiSelect.addEventListener('change', (e) => {
            const selectedSesi = e.target.value;
            const biaya = 500000; // Default biaya
            const kodeUnik = generateUniqueCode(); // Generate kode unik
            const totalPembayaran = biaya + kodeUnik;

            // Atur waktu dan lokasi berdasarkan sesi yang dipilih
            if (sesiMap[selectedSesi]) {
                waktuMulaiInput.value = sesiMap[selectedSesi].waktuMulai;
                waktuSelesaiInput.value = sesiMap[selectedSesi].waktuSelesai;
                lokasiInput.value = sesiMap[selectedSesi].lokasi;
            } else {
                waktuMulaiInput.value = '';
                waktuSelesaiInput.value = '';
                lokasiInput.value = '';
            }

            // Update biaya, kode unik, dan total pembayaran
            biayaInput.value = formatRupiah(biaya);
            kodeUnikInput.value = formatRupiah(kodeUnik);
            totalPembayaranInput.value = formatRupiah(totalPembayaran);

            // Update total keseluruhan pembayaran
            updateSubTotalKeseluruhan();
            updateTotalKeseluruhan();
        });
    }

    // Fungsi untuk menghitung subtotal keseluruhan pembayaran dari semua form
    function updateSubTotalKeseluruhan() {
        const totalPembayaranPertama = parseInt(document.getElementById('TotalPembayaran').value.replace(/\D/g, '')) || 0;
        const totalPembayaranKedua = parseInt(document.getElementById('TotalPembayaran_kedua').value.replace(/\D/g, '')) || 0;
        const totalPembayaranKetiga = parseInt(document.getElementById('TotalPembayaran_ketiga').value.replace(/\D/g, '')) || 0;

        const subtotalKeseluruhan = totalPembayaranPertama + totalPembayaranKedua + totalPembayaranKetiga;

        // Tampilkan total keseluruhan di input
        document.getElementById('subtotalKeseluruhanPembayaran').textContent = `${formatRupiah(subtotalKeseluruhan)}`;
    }

    // Fungsi untuk menghitung total keseluruhan pembayaran dari semua form
    function updateTotalKeseluruhan() {
        const totalPembayaranPertama = parseInt(document.getElementById('TotalPembayaran').value.replace(/\D/g, '')) || 0;
        const totalPembayaranKedua = parseInt(document.getElementById('TotalPembayaran_kedua').value.replace(/\D/g, '')) || 0;
        const totalPembayaranKetiga = parseInt(document.getElementById('TotalPembayaran_ketiga').value.replace(/\D/g, '')) || 0;

        const totalKeseluruhan = totalPembayaranPertama + totalPembayaranKedua + totalPembayaranKetiga;

        // Tampilkan total keseluruhan di input
        document.getElementById('totalKeseluruhanPembayaran').textContent = `${formatRupiah(totalKeseluruhan)}`;
        document.getElementById('totalKeseluruhanPembayaranhidden').value = totalKeseluruhan;
    }

    // Data sesi untuk setiap form
    const sesiMapPertama = {
        'sesi 1': {
            waktuMulai: '08:00',
            waktuSelesai: '12:00',
            lokasi: 'Rumah Scopus Pusat'
        },
        'sesi 2': {
            waktuMulai: '12:30',
            waktuSelesai: '16:30',
            lokasi: 'Rumah Scopus Pusat'
        },
        'sesi 3': {
            waktuMulai: '17:00',
            waktuSelesai: '21:00',
            lokasi: 'Rumah Scopus Pusat'
        }
    };

    const sesiMapKedua = {
        'sesi 1 kedua': {
            waktuMulai: '08:00',
            waktuSelesai: '12:00',
            lokasi: 'Rumah Scopus Pusat'
        },
        'sesi 2 kedua': {
            waktuMulai: '12:30',
            waktuSelesai: '16:30',
            lokasi: 'Rumah Scopus Pusat'
        },
        'sesi 3 kedua': {
            waktuMulai: '17:00',
            waktuSelesai: '21:00',
            lokasi: 'Rumah Scopus Pusat'
        }
    };

    const sesiMapKetiga = {
        'sesi 1 ketiga': {
            waktuMulai: '08:00',
            waktuSelesai: '12:00',
            lokasi: 'Rumah Scopus Pusat'
        },
        'sesi 2 ketiga': {
            waktuMulai: '12:30',
            waktuSelesai: '16:30',
            lokasi: 'Rumah Scopus Pusat'
        },
        'sesi 3 ketiga': {
            waktuMulai: '17:00',
            waktuSelesai: '21:00',
            lokasi: 'Rumah Scopus Pusat'
        }
    };

    // Inisialisasi form pertama
    handleFormChange(
        document.getElementById('sesi'),
        document.getElementById('waktu_mulai'),
        document.getElementById('waktu_selesai'),
        document.getElementById('lokasi'),
        document.getElementById('biaya'),
        document.getElementById('kodeUnik'),
        document.getElementById('TotalPembayaran'),
        sesiMapPertama,
        updateTotalKeseluruhan
    );

    // Inisialisasi form kedua
    handleFormChange(
        document.getElementById('sesi_kedua'),
        document.getElementById('waktu_mulai_kedua'),
        document.getElementById('waktu_selesai_kedua'),
        document.getElementById('lokasi_kedua'),
        document.getElementById('biaya_kedua'),
        document.getElementById('kodeUnik_kedua'),
        document.getElementById('TotalPembayaran_kedua'),
        sesiMapKedua,
        updateTotalKeseluruhan
    );

    // Inisialisasi form ketiga
    handleFormChange(
        document.getElementById('sesi_ketiga'),
        document.getElementById('waktu_mulai_ketiga'),
        document.getElementById('waktu_selesai_ketiga'),
        document.getElementById('lokasi_ketiga'),
        document.getElementById('biaya_ketiga'),
        document.getElementById('kodeUnik_ketiga'),
        document.getElementById('TotalPembayaran_ketiga'),
        sesiMapKetiga,
        updateTotalKeseluruhan
    );
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

<!--================== ADD AND REMOVE CARD FORM SCOPUS KAFE ==================-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {

        var ScopusKafeCounter = 0;

        $('#AddformPertama').on('click', function() {
            if (ScopusKafeCounter === 0) {
                $('.ScopusKafe-Card-Kedua').show();
                $('#RemoveformKedua').show();
            } else if (ScopusKafeCounter === 1) {
                $('.ScopusKafe-Card-Ketiga').show();
                $('#RemoveformKetiga').show();
                // Sembunyikan tombol AddformPertama setelah Anatomy-Card-Third ditampilkan
                $(this).hide();
            }
            ScopusKafeCounter++;
        });

        // Remove Anatomy-Card-Second
        $('#RemoveformKedua').on('click', function() {
            $('.ScopusKafe-Card-Kedua').hide();
            $('#RemoveformKedua').hide();
            ScopusKafeCounter--;

            // Tampilkan tombol AddformPertama jika kurang dari 2 cards yang tampil
            if (ScopusKafeCounter < 2) {
                $('#AddformPertama').show();
            }
        });

        // Remove Anatomy-Card-Third
        $('#RemoveformKetiga').on('click', function() {
            $('.ScopusKafe-Card-Ketiga').hide();
            $('#RemoveformKetiga').hide();
            ScopusKafeCounter--;

            // Tampilkan tombol AddformPertama jika kurang dari 2 cards yang tampil
            if (ScopusKafeCounter < 2) {
                $('#AddformPertama').show();
            }
        });

    });
</script>
<!--================== END ==================-->

<!--================== BUTTOM COPY ==================-->
<script>
    function copyToClipboard(elementId) {
        var tempInput = document.createElement("textarea");
        tempInput.value = document.getElementById(elementId).innerText;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);

        // Menggunakan SweetAlert2 untuk menampilkan pesan
        Swal.fire({
            icon: 'success',
            title: 'Nomor rekening berhasil disalin',
            text: 'Nomor rekening: ' + tempInput.value,
            showConfirmButton: true, // Menampilkan tombol konfirmasi
            confirmButtonText: 'OK', // Mengubah teks tombol konfirmasi
            confirmButtonColor: '#3085d6', // Warna tombol konfirmasi
        });
    }
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