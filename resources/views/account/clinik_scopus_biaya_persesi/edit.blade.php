@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Clinik Scopus Edit Biaya Per Sesi | MIS
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
            <h1>UPDATE DATA BIAYA PERSESI</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-body">

                    <form action="{{ route('account.Clinik-Scopus-Biaya-Persesi.update', $biayaPersesi->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Biaya Per Sesi</label>
                                    <input type="text" id="biaya_persesi" name="biaya_persesi" value="{{ $biayaPersesi->biaya_persesi }}" placeholder="Masukkan biaya" class="form-control">

                                    @error('biaya_persesi')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>PPN</label>
                                    <div class="input-group">
                                        <input type="number" id="ppn" name="ppn" value="{{ $biayaPersesi->ppn }}" placeholder="Masukkan PPN" class="form-control">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>

                                    @error('ppn')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="status" value="{{$biayaPersesi->status}}" style="height: auto;">
                                        <option value="" disabled selected>-- PILIH STATUS TRAINER --</option>
                                        <option value="active" {{ $biayaPersesi->status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="non active" {{ $biayaPersesi->status == 'non active' ? 'selected' : '' }}>Non Active</option>
                                    </select>
                                    @error('Status')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
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

<!--================== FORMAT BIAYA SESI ==================-->
<script>
    const biayaInput = document.getElementById('biaya_persesi');

    function formatRupiah(angka) {
        return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    biayaInput.addEventListener('input', function() {
        let angka = this.value.replace(/\D/g, '');
        if (angka) {
            this.value = formatRupiah(angka);
        } else {
            this.value = '';
        }
    });

    // 🔥 FORMAT SAAT PAGE LOAD
    document.addEventListener('DOMContentLoaded', function() {
        let value = biayaInput.value.replace(/\D/g, '');
        if (value) {
            biayaInput.value = formatRupiah(value);
        }
    });

    // 🔥 HAPUS FORMAT SAAT SUBMIT
    document.querySelector('form').addEventListener('submit', function() {
        biayaInput.value = biayaInput.value.replace(/\D/g, '');
    });
</script>
<!--================== END ==================-->
@stop