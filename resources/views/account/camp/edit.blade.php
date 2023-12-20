@extends('layouts.account')

@section('title')
Update Laporan Camp | MANAGEMENT
@stop

<!--================== button trainer responsive ==================-->
<style>
    /* Default styling for the button */
    #addTrainer,
    #removeAddedTrainer1,
    #removeAddedTrainer2,
    #removeAddedTrainer3,
    #removeAddedTrainer4,
    #removeAddedTrainer5,
    #removeAddedTrainer6 {
        height: 40px;
        white-space: nowrap;
    }

    /* Media query for handphones (width 767px or less) */
    @media (max-width: 767px) {

        #addTrainer,
        #removeAddedTrainer1,
        #removeAddedTrainer2,
        #removeAddedTrainer3,
        #removeAddedTrainer4,
        #removeAddedTrainer5,
        #removeAddedTrainer6 {
            width: 100%;
        }
    }

    /* Media query for tablets (width between 768px and 991px) */
    @media (min-width: 768px) and (max-width: 991px) {

        #addTrainer,
        #removeAddedTrainer1,
        #removeAddedTrainer2,
        #removeAddedTrainer3,
        #removeAddedTrainer4,
        #removeAddedTrainer5,
        #removeAddedTrainer6 {
            width: auto;
            /* atau atur sesuai kebutuhan pada tablet */
        }
    }

    /* Styling for larger screens (laptops, monitors) */
    @media (min-width: 992px) {

        #addTrainer,
        #removeAddedTrainer1,
        #removeAddedTrainer2,
        #removeAddedTrainer3,
        #removeAddedTrainer4,
        #removeAddedTrainer5,
        #removeAddedTrainer6 {
            width: auto;
            /* Atur sesuai kebutuhan pada laptop atau monitor */
        }
    }
</style>
<!--================== end ==================-->

<!--================== button team responsive ==================-->
<style>
    /* Default styling for the button */
    #addTeam,
    #removeAddedTeam1,
    #removeAddedTeam2,
    #removeAddedTeam3,
    #removeAddedTeam4,
    #removeAddedTeam5,
    #removeAddedTeam6,
    #removeAddedTeam7,
    #removeAddedTeam8,
    #removeAddedTeam9,
    #removeAddedTeam10 {
        height: 40px;
        white-space: nowrap;
    }

    /* Media query for handphones (width 767px or less) */
    @media (max-width: 767px) {

        #addTeam,
        #removeAddedTeam1,
        #removeAddedTeam2,
        #removeAddedTeam3,
        #removeAddedTeam4,
        #removeAddedTeam5,
        #removeAddedTeam6,
        #removeAddedTeam7,
        #removeAddedTeam8,
        #removeAddedTeam9,
        #removeAddedTeam10 {
            width: 100%;
        }
    }

    /* Media query for tablets (width between 768px and 991px) */
    @media (min-width: 768px) and (max-width: 991px) {

        #addTeam,
        #removeAddedTeam1,
        #removeAddedTeam2,
        #removeAddedTeam3,
        #removeAddedTeam4,
        #removeAddedTeam5,
        #removeAddedTeam6,
        #removeAddedTeam7,
        #removeAddedTeam8,
        #removeAddedTeam9,
        #removeAddedTeam10 {
            width: auto;
            /* atau atur sesuai kebutuhan pada tablet */
        }
    }

    /* Styling for larger screens (laptops, monitors) */
    @media (min-width: 992px) {

        #addTeam,
        #removeAddedTeam1,
        #removeAddedTeam2,
        #removeAddedTeam3,
        #removeAddedTeam4,
        #removeAddedTeam5,
        #removeAddedTeam6,
        #removeAddedTeam7,
        #removeAddedTeam8,
        #removeAddedTeam9,
        #removeAddedTeam10 {
            width: auto;
            /* Atur sesuai kebutuhan pada laptop atau monitor */
        }
    }
</style>
<!--================== end ==================-->

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>LAPORAN CAMP</h1>
        </div>

        <div class="section-body">

            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-hand-holding-usd"></i> UPDATE LAPORAN CAMP</h4>
                </div>

                @if(session('status') === 'error')
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <b>{{ session('message') }}</b>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif

                <div class="card-body">

                    <form action="{{ route('account.camp.update', $camp->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Camp</label>
                                    <div class="input-group">
                                        <input type="text" name="title" value="{{ $camp->title }}" placeholder="Masukkan Nama Camp" class="form-control" style="text-transform:uppercase;">
                                    </div>
                                    @error('title')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Camp Ke</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">#</span>
                                        </div>
                                        <input type="number" name="camp_ke" value="{{ $camp->camp_ke }}" placeholder="Masukkan Nomor Camp Ke" class="form-control">
                                    </div>
                                    @error('camp_ke')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Mulai Camp</label>
                                    <input type="datetime-local" name="tanggal" id="tanggal" value="{{ $camp->tanggal }}" placeholder="Masukkan Total Tunjangan" class="form-control">
                                </div>
                                @error('tanggal')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Berakhir Camp</label>
                                    <input type="datetime-local" name="tanggal_akhir" id="tanggal_akhir" value="{{ $camp->tanggal_akhir }}" placeholder="Masukkan Total Tunjangan" class="form-control">
                                </div>
                                @error('tanggal_akhir')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                </div>
            </div>


            <div class="card">
                <div class="card-body">
                    <div class="row">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Uang Masuk</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="uang_masuk" value="{{ number_format($camp->uang_masuk, 0, ',', ',') }} placeholder=" Masukkan Total Uang Masuk" class="form-control currency">
                                </div>
                                @error('uang_masuk')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Uang Masuk Lain-Lain</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="lain_lain" value="{{ number_format($camp->lain_lain, 0, ',', ',') }}" placeholder="Masukkan Total Uang Masuk Lainnya" class="form-control currency1">
                                </div>
                                @error('lain_lain')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Total Uang Masuk</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="border-color: red;">Rp.</span>
                                    </div>
                                    <input type="text" name="total_bonus" style="border-color: red;" value="{{ number_format($camp->total_uang_masuk, 0, ',', ',') }}" placeholder="Masukkan Gaji Pokok Karyawan" class="form-control currency7" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--================== GAJI TRAINER ==================-->
                    <!-- default -->
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer" value="{{ number_format($camp->gaji_trainer, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer">
                                </div>
                                @error('gaji_trainer')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama" value="{{ $camp->gaji_trainer_nama }}" placeholder="Masukkan Nama Trainer" class="form-control">
                                </div>
                                @error('gaji_trainer_nama')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-info mt-2" id="addTrainer" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-plus"></i> INPUT
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- end -->

                    <!-- 1 -->
                    @if($camp->gaji_trainer1 == null || $camp->gaji_trainer1 == 0)
                    <div class="row trainer-field1" style="display: none;">
                        <div class=" col-md-5">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer1" value="{{ number_format($camp->gaji_trainer1, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer1">
                                </div>
                                @error('gaji_trainer1')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama1" value="{{ $camp->gaji_trainer_nama1 }}" placeholder="Masukkan Nama Trainer" class="form-control">
                                </div>
                                @error('gaji_trainer_nama1')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTrainer1" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class=" col-md-5">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer1" value="{{ number_format($camp->gaji_trainer1, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer1">
                                </div>
                                @error('gaji_trainer1')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama1" value="{{ $camp->gaji_trainer_nama1 }}" placeholder="Masukkan Nama Trainer" class="form-control">
                                </div>
                                @error('gaji_trainer_nama1')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTrainer1" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 2 -->
                    @if($camp->gaji_trainer2 == null || $camp->gaji_trainer2 == 0)
                    <div class="row trainer-field2" style="display: none;">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer2" value="{{ number_format($camp->gaji_trainer2, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer2">
                                </div>
                                @error('gaji_trainer2')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama2" value="{{ $camp->gaji_trainer_nama2 }}" placeholder="Masukkan Nama Trainer" class="form-control">
                                </div>
                                @error('gaji_trainer_nama2')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTrainer2" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer2" value="{{ number_format($camp->gaji_trainer2, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer2">
                                </div>
                                @error('gaji_trainer2')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama2" value="{{ $camp->gaji_trainer_nama2 }}" placeholder="Masukkan Nama Trainer" class="form-control">
                                </div>
                                @error('gaji_trainer_nama2')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTrainer2" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 3 -->
                    @if($camp->gaji_trainer3 == null || $camp->gaji_trainer3 == 0)
                    <div class="row trainer-field3" style="display: none;">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer3" value="{{ number_format($camp->gaji_trainer3, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer3">
                                </div>
                                @error('gaji_trainer3')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama3" value="{{ $camp->gaji_trainer_nama3 }}" placeholder="Masukkan Nama Trainer" class="form-control">
                                </div>
                                @error('gaji_trainer_nama3')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTrainer3" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer3" value="{{ number_format($camp->gaji_trainer3, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer3">
                                </div>
                                @error('gaji_trainer3')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama3" value="{{ $camp->gaji_trainer_nama3 }}" placeholder="Masukkan Nama Trainer" class="form-control">
                                </div>
                                @error('gaji_trainer_nama3')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTrainer3" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 4 -->
                    @if($camp->gaji_trainer4 == null || $camp->gaji_trainer4 == 0)
                    <div class="row trainer-field4" style="display: none;">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer4" value="{{ number_format($camp->gaji_trainer4, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer4">
                                </div>
                                @error('gaji_trainer4')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama4" value="{{ $camp->gaji_trainer_nama4 }}" placeholder="Masukkan Nama Trainer" class="form-control">
                                </div>
                                @error('gaji_trainer_nama4')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTrainer4" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer4" value="{{ number_format($camp->gaji_trainer4, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer4">
                                </div>
                                @error('gaji_trainer4')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama4" value="{{ $camp->gaji_trainer_nama4 }}" placeholder="Masukkan Nama Trainer" class="form-control">
                                </div>
                                @error('gaji_trainer_nama4')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTrainer4" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 5 -->
                    @if($camp->gaji_trainer5 == null || $camp->gaji_trainer5 == 0)
                    <div class="row trainer-field5" style="display: none;">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer5" value="{{ number_format($camp->gaji_trainer5, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer5">
                                </div>
                                @error('gaji_trainer5')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama5" value="{{ $camp->gaji_trainer_nama5 }}" placeholder="Masukkan Nama Trainer" class="form-control">
                                </div>
                                @error('gaji_trainer_nama5')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTrainer5" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer5" value="{{ number_format($camp->gaji_trainer5, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer5">
                                </div>
                                @error('gaji_trainer5')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama5" value="{{ $camp->gaji_trainer_nama5 }}" placeholder="Masukkan Nama Trainer" class="form-control">
                                </div>
                                @error('gaji_trainer_nama5')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTrainer5" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 6 -->
                    @if($camp->gaji_trainer6 == null || $camp->gaji_trainer6 == 0)
                    <div class="row trainer-field6" style="display: none;">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer6" value="{{ number_format($camp->gaji_trainer6, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer6">
                                </div>
                                @error('gaji_trainer6')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama6" value="{{ $camp->gaji_trainer_nama6 }}" placeholder="Masukkan Nama Trainer" class="form-control">
                                </div>
                                @error('gaji_trainer_nama6')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTrainer6" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer6" value="{{ number_format($camp->gaji_trainer6, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer6">
                                </div>
                                @error('gaji_trainer6')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama6" value="{{ $camp->gaji_trainer_nama6 }}" placeholder="Masukkan Nama Trainer" class="form-control">
                                </div>
                                @error('gaji_trainer_nama6')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTrainer6" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->
                    <!--================== end ==================-->

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Total Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="border-color: red;">Rp.</span>
                                    </div>
                                    <input type="text" name="total_gaji_trainer" style="border-color: red;" value="{{ number_format($camp->total_gaji_trainer, 0, ',', ',') }}" placeholder="Masukkan Gaji Pokok Karyawan" class="form-control currency7" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--================== GAJI TEAM ==================-->
                    <!-- default -->
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team" value="{{ number_format($camp->gaji_team, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team">
                                </div>
                                @error('gaji_team')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama" value="{{ $camp->gaji_team_nama }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-info mt-2" id="addTeam" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-plus"></i> INPUT
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- end -->

                    <!-- 1 -->
                    @if($camp->gaji_team1 == null || $camp->gaji_team1 == 0)
                    <div class="row team-field1" style="display: none;">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team1" value="{{ number_format($camp->gaji_team1, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team1">
                                </div>
                                @error('gaji_team1')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama1" value="{{ $camp->gaji_team_nama1 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama1')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam1" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team1" value="{{ number_format($camp->gaji_team1, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team1">
                                </div>
                                @error('gaji_team1')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama1" value="{{ $camp->gaji_team_nama1 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama1')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam1" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 2 -->
                    @if($camp->gaji_team2 == null || $camp->gaji_team2 == 0)
                    <div class="row team-field2" style="display: none;">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team2" value="{{ number_format($camp->gaji_team2, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team2">
                                </div>
                                @error('gaji_team2')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama2" value="{{ $camp->gaji_team_nama2 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama2')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam2" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team2" value="{{ number_format($camp->gaji_team2, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team2">
                                </div>
                                @error('gaji_team2')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama2" value="{{ $camp->gaji_team_nama2 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama2')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam2" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 3 -->
                    @if($camp->gaji_team3 == null || $camp->gaji_team3 == 0)
                    <div class="row team-field3" style="display: none;">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team3" value="{{ number_format($camp->gaji_team3, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team3">
                                </div>
                                @error('gaji_team3')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama3" value="{{ $camp->gaji_team_nama3 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama3')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam3" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team3" value="{{ number_format($camp->gaji_team3, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team3">
                                </div>
                                @error('gaji_team3')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama3" value="{{ $camp->gaji_team_nama3 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama3')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam3" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 4 -->
                    @if($camp->gaji_team4 == null || $camp->gaji_team4 == 0)
                    <div class="row team-field4" style="display: none;">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team4" value="{{ number_format($camp->gaji_team4, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team4">
                                </div>
                                @error('gaji_team4')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama4" value="{{ $camp->gaji_team_nama4 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama4')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam4" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team4" value="{{ number_format($camp->gaji_team4, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team4">
                                </div>
                                @error('gaji_team4')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama4" value="{{ $camp->gaji_team_nama4 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama4')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam4" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 5 -->
                    @if($camp->gaji_team5 == null || $camp->gaji_team5 == 0)
                    <div class="row team-field5" style="display: none;">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team5" value="{{ number_format($camp->gaji_team5, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team5">
                                </div>
                                @error('gaji_team5')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama5" value="{{ $camp->gaji_team_nama5 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama5')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam5" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team5" value="{{ number_format($camp->gaji_team5, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team5">
                                </div>
                                @error('gaji_team5')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama5" value="{{ $camp->gaji_team_nama5 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama5')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam5" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 6 -->
                    @if($camp->gaji_team6 == null || $camp->gaji_team6 == 0)
                    <div class="row team-field6" style="display: none;">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team6" value="{{ number_format($camp->gaji_team6, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team6">
                                </div>
                                @error('gaji_team6')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama6" value="{{ $camp->gaji_team_nama6 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama6')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam6" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team6" value="{{ number_format($camp->gaji_team6, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team6">
                                </div>
                                @error('gaji_team6')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama6" value="{{ $camp->gaji_team_nama6 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama6')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam6" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 7 -->
                    @if($camp->gaji_team7 == null || $camp->gaji_team7 == 0)
                    <div class="row team-field7" style="display: none;">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team7" value="{{ number_format($camp->gaji_team7, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team7">
                                </div>
                                @error('gaji_team7')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama7" value="{{ $camp->gaji_team_nama7 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama7')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam7" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team7" value="{{ number_format($camp->gaji_team7, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team7">
                                </div>
                                @error('gaji_team7')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama7" value="{{ $camp->gaji_team_nama7 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama7')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam7" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 8 -->
                    @if($camp->gaji_team8 == null || $camp->gaji_team8 == 0)
                    <div class="row team-field8" style="display: none;">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team8" value="{{ number_format($camp->gaji_team8, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team8">
                                </div>
                                @error('gaji_team8')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama8" value="{{ $camp->gaji_team_nama8 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama8')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam8" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team8" value="{{ number_format($camp->gaji_team8, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team8">
                                </div>
                                @error('gaji_team8')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama8" value="{{ $camp->gaji_team_nama8 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama8')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam8" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 9 -->
                    @if($camp->gaji_team9 == null || $camp->gaji_team9 == 0)
                    <div class="row team-field9" style="display: none;">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team9" value="{{ number_format($camp->gaji_team9, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team9">
                                </div>
                                @error('gaji_team9')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama9" value="{{ $camp->gaji_team_nama9 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama9')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam9" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team9" value="{{ number_format($camp->gaji_team9, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team9">
                                </div>
                                @error('gaji_team9')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama9" value="{{ $camp->gaji_team_nama9 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama9')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam9" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 10 -->
                    @if($camp->gaji_team10 == null || $camp->gaji_team10 == 0)
                    <div class="row team-field10" style="display: none;">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team10" value="{{ number_format($camp->gaji_team10, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team10">
                                </div>
                                @error('gaji_team10')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama10" value="{{ $camp->gaji_team_nama10 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama10')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam10" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team10" value="{{ number_format($camp->gaji_team10, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team10">
                                </div>
                                @error('gaji_team10')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama10" value="{{ $camp->gaji_team_nama10 }}" placeholder="Masukkan Nama Team" class="form-control">
                                </div>
                                @error('gaji_team_nama10')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-1 col-12">
                            <div class="form-group">
                                <label class="mb-3"></label>
                                <button type="button" class="btn btn-danger mt-2" id="removeAddedTeam10" style="height: 40px; white-space: nowrap;">
                                    <i class="fas fa-times"></i> HAPUS</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->
                    <!--================== end ==================-->

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Total Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="border-color: red;">Rp.</span>
                                    </div>
                                    <input type="text" name="total_gaji_team" style="border-color: red;" value="{{ number_format($camp->total_gaji_team, 0, ',', ',') }}" placeholder="Masukkan Gaji Pokok Karyawan" class="form-control currency7" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Team Cabang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="team_cabang" value="{{ number_format($camp->team_cabang, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Team Cabang" class="form-control currency_team_cabang">
                                </div>
                                @error('team_cabang')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Booknote</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="booknote" value="{{ number_format($camp->booknote, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Booknote" class="form-control currency_booknote">
                                </div>
                                @error('booknote')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Grammarly</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="grammarly" value="{{ number_format($camp->grammarly, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Grammarly" class="form-control currency_grammarly">
                                </div>
                                @error('grammarly')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer" value="{{ number_format($camp->tiket_trainer, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Tiket Trainer" class="form-control currency_tiket_trainer">
                                </div>
                                @error('tiket_trainer')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team" value="{{ number_format($camp->tiket_team, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Tiket Team" class="form-control currency_tiket_team">
                                </div>
                                @error('tiket_team')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Hotel</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="hotel" value="{{ number_format($camp->hotel, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Hotel" class="form-control currency_hotel">
                                </div>
                                @error('hotel')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Konsumsi Tambahan</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="konsumsi_tambahan" value="{{ number_format($camp->konsumsi_tambahan, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Konsumsi Tambahan" class="form-control currency_konsumsi_tambahan">
                                </div>
                                @error('konsumsi_tambahan')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Lain-Lain</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="lainnya" value="{{ number_format($camp->lainnya, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Lain-Lain" class="form-control currency_lainnya">
                                </div>
                                @error('lainnya')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Total Uang Keluar</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="border-color: red;">Rp.</span>
                                    </div>
                                    <input type="text" name="total" style="border-color: red;" value="{{ number_format($camp->total, 0, ',', ',') }}" placeholder="Masukkan Gaji Pokok Karyawan" class="form-control currency7" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Keuntungan</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="border-color: red;">Rp.</span>
                                    </div>
                                    <input type="text" name="keuntungan" style="border-color: red;" value="{{ number_format($camp->keuntungan, 0, ',', ',') }}" placeholder="Masukkan Gaji Pokok Karyawan" class="form-control currency7" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Persentase Keuntungan</label>
                                <div class="input-group">
                                    <input type="text" name="persentase_keuntungan" style="border-color: red;" value="{{ number_format($camp->persentase_keuntungan, 0, ',', ',') }}" placeholder="Masukkan Gaji Pokok Karyawan" class="form-control currency7" readonly>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="border-color: red;">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status Pembayaran</label>
                                <select class="form-control" name="status">
                                    <option value="" disabled selected>Silahkan Pilih</option>
                                    <option value="pending" {{ $camp->status == 'pending' ? 'selected' : '' }}>PENDING</option>
                                    <option value="terbayar" {{ $camp->status == 'terbayar' ? 'selected' : '' }}>TERBAYAR</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Catatan</label>
                                <div class="input-group">
                                    <textarea name="note" id="note" placeholder="Masukkan catatan" class="form-control" style="width: 100%;">{{ $camp->note }}</textarea>
                                </div>
                                @error('note')
                                <div class="invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>



                    <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> SIMPAN</button>
                    <button class="btn btn-warning btn-reset" type="reset"><i class="fa fa-redo"></i> RESET</button>

                    </form>

                </div>
            </div>
        </div>
    </section>
</div>

<!--================== add dan remove field trainer ==================-->
<script>
    $(document).ready(function() {

        var trainerCounter = 0;

        $('#addTrainer').on('click', function() {
            if (trainerCounter === 0) {
                $('.trainer-field1').show();
                $('#removeAddedTrainer1').show();
                $('#removeAddedTrainer2').show();
                $('#removeAddedTrainer3').show();
                $('#removeAddedTrainer4').show();
                $('#removeAddedTrainer5').show();
                $('#removeAddedTrainer6').show();
            } else if (trainerCounter === 1) {
                $('.trainer-field2').show();
                $('#addTrainer').show();
                $('#removeAddedTrainer2').show();
            } else if (trainerCounter === 2) {
                $('.trainer-field3').show();
                $('#addTrainer').show();
                $('#removeAddedTrainer3').show();
            } else if (trainerCounter === 3) {
                $('.trainer-field4').show();
                $('#addTrainer').show();
                $('#removeAddedTrainer4').show();
            } else if (trainerCounter === 4) {
                $('.trainer-field5').show();
                $('#addTrainer').show();
                $('#removeAddedTrainer5').show();
            } else if (trainerCounter === 5) {
                $('.trainer-field6').show();
                $('#addTrainer').hide();
                $('#removeAddedTrainer6').show();
            }
            trainerCounter++;
        });

        // Remove additional trainer2 fields
        $('#removeAddedTrainer1').on('click', function() {
            $('.trainer-field1').hide();
            $('#addTrainer').show();
            trainerCounter--;
            $('.currency_gaji_trainer1').val('');
            $('[name="gaji_trainer_nama1"]').val('');
        });

        $('#removeAddedTrainer2').on('click', function() {
            $('.trainer-field2').hide();
            $('#addTrainer').show();
            trainerCounter--;
            $('.currency_gaji_trainer2').val('');
            $('[name="gaji_trainer_nama2"]').val('');
        });
        $('#removeAddedTrainer3').on('click', function() {
            $('.trainer-field3').hide();
            $('#addTrainer').show();
            trainerCounter--;
            $('.currency_gaji_trainer3').val('');
            $('[name="gaji_trainer_nama3"]').val('');
        });
        $('#removeAddedTrainer4').on('click', function() {
            $('.trainer-field4').hide();
            $('#addTrainer').show();
            trainerCounter--;
            $('.currency_gaji_trainer4').val('');
            $('[name="gaji_trainer_nama4"]').val('');
        });
        $('#removeAddedTrainer5').on('click', function() {
            $('.trainer-field5').hide();
            $('#addTrainer').show();
            trainerCounter--;
            $('.currency_gaji_trainer5').val('');
            $('[name="gaji_trainer_nama5"]').val('');
        });
        $('#removeAddedTrainer6').on('click', function() {
            $('.trainer-field6').hide();
            $('#addTrainer').show();
            trainerCounter--;
            $('.currency_gaji_trainer6').val('');
            $('[name="gaji_trainer_nama6"]').val('');
        });
    });
</script>
<!--================== end ==================-->

<!--================== add dan remove field team ==================-->
<script>
    $(document).ready(function() {

        var teamCounter = 0;

        $('#addTeam').on('click', function() {
            if (teamCounter === 0) {
                $('.team-field1').show();
                $('#removeAddedTeam1').show();
                $('#removeAddedTeam2').show();
                $('#removeAddedTeam3').show();
                $('#removeAddedTeam4').show();
                $('#removeAddedTeam5').show();
                $('#removeAddedTeam6').show();
                $('#removeAddedTeam7').show();
                $('#removeAddedTeam8').show();
                $('#removeAddedTeam9').show();
                $('#removeAddedTeam10').show();
            } else if (teamCounter === 1) {
                $('.team-field2').show();
                $('#addTeam').show();
                $('#removeAddedTeam2').show();
            } else if (teamCounter === 2) {
                $('.team-field3').show();
                $('#addTeam').show();
                $('#removeAddedTeam3').show();
            } else if (teamCounter === 3) {
                $('.team-field4').show();
                $('#addTeam').show();
                $('#removeAddedTeam4').show();
            } else if (teamCounter === 4) {
                $('.team-field5').show();
                $('#addTeam').show();
                $('#removeAddedTeam5').show();
            } else if (teamCounter === 5) {
                $('.team-field6').show();
                $('#addTeam').show();
                $('#removeAddedTeam6').show();
            } else if (teamCounter === 6) {
                $('.team-field7').show();
                $('#addTeam').show();
                $('#removeAddedTeam7').show();
            } else if (teamCounter === 7) {
                $('.team-field8').show();
                $('#addTeam').show();
                $('#removeAddedTeam8').show();
            } else if (teamCounter === 8) {
                $('.team-field9').show();
                $('#addTeam').show();
                $('#removeAddedTeam9').show();
            } else if (teamCounter === 9) {
                $('.team-field10').show();
                $('#addTeam').hide();
                $('#removeAddedTeam10').show();
            }
            teamCounter++;
        });

        // Remove additional team fields
        $('#removeAddedTeam1').on('click', function() {
            $('.team-field1').hide();
            $('#addTeam').show();
            teamCounter--;
            $('.currency_gaji_team1').val('');
            $('[name="gaji_team_nama1"]').val('');
        });

        $('#removeAddedTeam2').on('click', function() {
            $('.team-field2').hide();
            $('#addTeam').show();
            teamCounter--;
            $('.currency_gaji_team2').val('');
            $('[name="gaji_team_nama2"]').val('');
        });
        $('#removeAddedTeam3').on('click', function() {
            $('.team-field3').hide();
            $('#addTeam').show();
            teamCounter--;
            $('.currency_gaji_team3').val('');
            $('[name="gaji_team_nama3"]').val('');
        });
        $('#removeAddedTeam4').on('click', function() {
            $('.team-field4').hide();
            $('#addTeam').show();
            teamCounter--;
            $('.currency_gaji_team4').val('');
            $('[name="gaji_team_nama4"]').val('');
        });
        $('#removeAddedTeam5').on('click', function() {
            $('.team-field5').hide();
            $('#addTeam').show();
            teamCounter--;
            $('.currency_gaji_team5').val('');
            $('[name="gaji_team_nama5"]').val('');
        });
        $('#removeAddedTeam6').on('click', function() {
            $('.team-field6').hide();
            $('#addTeam').show();
            teamCounter--;
            $('.currency_gaji_team6').val('');
            $('[name="gaji_team_nama6"]').val('');
        });
        $('#removeAddedTeam7').on('click', function() {
            $('.team-field7').hide();
            $('#addTeam').show();
            teamCounter--;
            $('.currency_gaji_team7').val('');
            $('[name="gaji_team_nama7"]').val('');
        });
        $('#removeAddedTeam8').on('click', function() {
            $('.team-field8').hide();
            $('#addTeam').show();
            teamCounter--;
            $('.currency_gaji_team8').val('');
            $('[name="gaji_team_nama8"]').val('');
        });
        $('#removeAddedTeam9').on('click', function() {
            $('.team-field9').hide();
            $('#addTeam').show();
            teamCounter--;
            $('.currency_gaji_team9').val('');
            $('[name="gaji_team_nama9"]').val('');
        });
        $('#removeAddedTeam10').on('click', function() {
            $('.team-field10').hide();
            $('#addTeam').show();
            teamCounter--;
            $('.currency_gaji_team10').val('');
            $('[name="gaji_team_nama10"]').val('');
        });
    });
</script>
<!--================== end ==================-->

<!-- Include CKEditor JS -->
<script src="//cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('note');
</script>
<!-- end ckeditor -->

<script>
    // datepicker
    if ($(".datetimepicker").length) {
        $('.datetimepicker').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD hh:mm'
            },
            singleDatePicker: true,
            timePicker: true,
            timePicker24Hour: true,
        });
    }

    var cleaveC = new Cleave('.currency', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });

    var timeoutHandler = null;
    // end
</script>



<script>
    var cleaveC = new Cleave('.currency', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });

    var cleaveC = new Cleave('.currency1', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });

    // gaji trainer
    var cleaveC = new Cleave('.currency_gaji_trainer', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });

    var cleaveC = new Cleave('.currency_gaji_trainer1', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });

    var cleaveC = new Cleave('.currency_gaji_trainer2', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });

    var cleaveC = new Cleave('.currency_gaji_trainer3', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });

    var cleaveC = new Cleave('.currency_gaji_trainer4', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });

    var cleaveC = new Cleave('.currency_gaji_trainer5', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });

    var cleaveC = new Cleave('.currency_gaji_trainer6', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    // end

    // gaji team
    var cleaveC = new Cleave('.currency_gaji_team', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    var cleaveC = new Cleave('.currency_gaji_team1', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    var cleaveC = new Cleave('.currency_gaji_team2', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    var cleaveC = new Cleave('.currency_gaji_team3', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    var cleaveC = new Cleave('.currency_gaji_team4', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    var cleaveC = new Cleave('.currency_gaji_team5', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    var cleaveC = new Cleave('.currency_gaji_team6', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    var cleaveC = new Cleave('.currency_gaji_team7', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    var cleaveC = new Cleave('.currency_gaji_team8', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    var cleaveC = new Cleave('.currency_gaji_team9', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    var cleaveC = new Cleave('.currency_gaji_team10', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    // end

    var cleaveC = new Cleave('.currency_team_cabang', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });

    var cleaveC = new Cleave('.currency_booknote', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    var cleaveC = new Cleave('.currency_grammarly', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    var cleaveC = new Cleave('.currency_tiket_trainer', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    var cleaveC = new Cleave('.currency_tiket_team', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    var cleaveC = new Cleave('.currency_hotel', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    var cleaveC = new Cleave('.currency_konsumsi_tambahan', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    var cleaveC = new Cleave('.currency_lainnya', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });

    var timeoutHandler = null;
</script>

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