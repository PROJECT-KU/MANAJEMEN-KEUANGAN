@extends('layouts.account')

@section('title')
Detail Laporan Camp | MIS
@stop

<link rel="stylesheet" href="{{ asset('assets/artikel/summernote/summernote-bs4.css') }}">

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>DETAIL LAPORAN CAMP</h1>
        </div>

        <div class="section-body">

            <div class="card">
                <div class="card-header">
                    <h4>DETAIL CAMP</h4>
                    <div class="card-header-action">
                        <h4 class="float-right"><i class="fas fa-receipt"></i> ID TRANSAKSI: {{ $camp->id_transaksi }}</h4>
                    </div>
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

                    <form action="{{ route('account.camp.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!--================== DETAIL CAMP ==================-->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Camp</label>
                                    <div class="input-group">
                                        <input type="text" name="title" value="{{ $camp->title }}" placeholder="Masukkan Nama Camp" class="form-control" style="text-transform:uppercase;" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Camp Ke</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">#</span>
                                        </div>
                                        <input type="number" name="camp_ke" value="{{ $camp->camp_ke }}" placeholder="Masukkan Nomor Camp Ke" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Mulai Camp</label>
                                    <input type="text" name="tanggal" id="tanggal" value="{{ date('d F Y', strtotime($camp->tanggal)) }}" placeholder="Masukkan Total Tunjangan" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Berakhir Camp</label>
                                    <input type="text" name="tanggal_akhir" id="tanggal_akhir" value="{{ date('d F Y', strtotime($camp->tanggal_akhir)) }}" placeholder="Masukkan Total Tunjangan" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Jumlah Peserta</label>
                                    <div class="input-group">
                                        <input type="text" name="tiket_trainer" value="{{ $camp->peserta }}" placeholder="Masukkan Uang Keluar Tiket Trainer" class="form-control currency_tiket_trainer" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                </div>
            </div>
            <!--================== END ==================-->


            <div class="card">
                <div class="card-header">
                    <h4>DETAIL UANG MASUK</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                    </div>

                    <!--================== UANG MASUK ==================-->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Uang Masuk</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="uang_masuk" value="{{ number_format($camp->uang_masuk, 0, ',', ',') }} placeholder=" Masukkan Total Uang Masuk" class="form-control currency" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Uang Masuk Lain-Lain</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="lain_lain" value="{{ number_format($camp->lain_lain, 0, ',', ',') }}" placeholder="Masukkan Total Uang Masuk Lainnya" class="form-control currency1" readonly>
                                </div>
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
                    <!--================== END ==================-->
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>DETAIL GAJI</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                    </div>

                    <!--================== gaji trainer ==================-->
                    <!-- default -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer" value="{{ number_format($camp->gaji_trainer, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama" value="{{ $camp->gaji_trainer_nama }}" placeholder="Masukkan Nama Trainer" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end -->

                    <!-- 1 -->
                    @if($camp->gaji_trainer1 != null && $camp->gaji_trainer1 != 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer1" value="{{ number_format($camp->gaji_trainer1, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer1" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama1" value="{{ $camp->gaji_trainer_nama1 }}" placeholder="Masukkan Nama Trainer" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 2 -->
                    @if($camp->gaji_trainer2 != null && $camp->gaji_trainer2 != 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer2" value="{{ number_format($camp->gaji_trainer2, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer2" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama2" value="{{ $camp->gaji_trainer_nama2 }}" placeholder="Masukkan Nama Trainer" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 3 -->
                    @if($camp->gaji_trainer3 != null && $camp->gaji_trainer3 != 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer3" value="{{ number_format($camp->gaji_trainer3, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer3" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama3" value="{{ $camp->gaji_trainer_nama3 }}" placeholder="Masukkan Nama Trainer" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 4 -->
                    @if($camp->gaji_trainer4 != null && $camp->gaji_trainer4 != 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer4" value="{{ number_format($camp->gaji_trainer4, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer4" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama4" value="{{ $camp->gaji_trainer_nama4 }}" placeholder="Masukkan Nama Trainer" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 5 -->
                    @if($camp->gaji_trainer5 != null && $camp->gaji_trainer5 != 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer5" value="{{ number_format($camp->gaji_trainer5, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer5" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama5" value="{{ $camp->gaji_trainer_nama5 }}" placeholder="Masukkan Nama Trainer" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 6 -->
                    @if($camp->gaji_trainer6 != null && $camp->gaji_trainer6 != 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_trainer6" value="{{ number_format($camp->gaji_trainer6, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer6" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_trainer_nama6" value="{{ $camp->gaji_trainer_nama6 }}" placeholder="Masukkan Nama Trainer" class="form-control" readonly>
                                </div>
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

                    <!--================== gaji team ==================-->
                    <!-- default -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team" value="{{ number_format($camp->gaji_team, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama" value="{{ $camp->gaji_team_nama }}" placeholder="Masukkan Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end -->

                    <!-- 1 -->
                    @if($camp->gaji_team1 != null && $camp->gaji_team1 != 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team1" value="{{ number_format($camp->gaji_team1, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team1" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama1" value="{{ $camp->gaji_team_nama1 }}" placeholder="Masukkan Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 2 -->
                    @if($camp->gaji_team2 != null && $camp->gaji_team2 != 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team2" value="{{ number_format($camp->gaji_team2, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team2" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama2" value="{{ $camp->gaji_team_nama2 }}" placeholder="Masukkan Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 3 -->
                    @if($camp->gaji_team3 != null && $camp->gaji_team3 != 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team3" value="{{ number_format($camp->gaji_team3, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team3" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama3" value="{{ $camp->gaji_team_nama3 }}" placeholder="Masukkan Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 4 -->
                    @if($camp->gaji_team4 != null && $camp->gaji_team4 != 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team4" value="{{ number_format($camp->gaji_team4, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team4" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama4" value="{{ $camp->gaji_team_nama4 }}" placeholder="Masukkan Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 5 -->
                    @if($camp->gaji_team5 != null && $camp->gaji_team5 != 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team5" value="{{ number_format($camp->gaji_team5, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team5" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama5" value="{{ $camp->gaji_team_nama5 }}" placeholder="Masukkan Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 6 -->
                    @if($camp->gaji_team6 != null && $camp->gaji_team6 != 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team6" value="{{ number_format($camp->gaji_team6, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team6" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama6" value="{{ $camp->gaji_team_nama6 }}" placeholder="Masukkan Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 7 -->
                    @if($camp->gaji_team7 != null && $camp->gaji_team7 != 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team7" value="{{ number_format($camp->gaji_team7, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team7" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama7" value="{{ $camp->gaji_team_nama7 }}" placeholder="Masukkan Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 8 -->
                    @if($camp->gaji_team8 != null && $camp->gaji_team8 != 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team8" value="{{ number_format($camp->gaji_team8, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team8" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama8" value="{{ $camp->gaji_team_nama8 }}" placeholder="Masukkan Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 9 -->
                    @if($camp->gaji_team9 != null && $camp->gaji_team9 != 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team9" value="{{ number_format($camp->gaji_team9, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team9" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama9" value="{{ $camp->gaji_team_nama9 }}" placeholder="Masukkan Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 10 -->
                    @if($camp->gaji_team10 != null && $camp->gaji_team10 != 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="gaji_team10" value="{{ number_format($camp->gaji_team10, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Gaji Team" class="form-control currency_gaji_team10" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="gaji_team_nama10" value="{{ $camp->gaji_team_nama10 }}" placeholder="Masukkan Nama Team" class="form-control" readonly>
                                </div>
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

                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>DETAIL UANG KELUAR</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                    </div>

                    <!--================== UANG KELUAR ==================-->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Team Cabang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="team_cabang" value="{{ number_format($camp->team_cabang, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Team Cabang" class="form-control currency_team_cabang" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Booknote</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="booknote" value="{{ number_format($camp->booknote, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Booknote" class="form-control currency_booknote" readonly>
                                </div>
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
                                    <input type="text" name="grammarly" value="{{ number_format($camp->grammarly, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Grammarly" class="form-control currency_grammarly" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Hotel</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="hotel" value="{{ number_format($camp->hotel, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Hotel" class="form-control currency_hotel" readonly>
                                </div>
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
                                    <input type="text" name="konsumsi_tambahan" value="{{ number_format($camp->konsumsi_tambahan, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Konsumsi Tambahan" class="form-control currency_konsumsi_tambahan" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Lain-Lain</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="lainnya" value="{{ number_format($camp->lainnya, 0, ',', ',') }}" placeholder="Masukkan Uang Keluar Lain-Lain" class="form-control currency_lainnya" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--================== END ==================-->
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>DETAIL TIKET</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                    </div>

                    <!--================== TIKET TRAINER ==================-->
                    <!-- default -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="tiket_trainer_nama" value="{{ $camp->tiket_trainer_nama }}" placeholder="Nama Trainer" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer Berangkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer" value="{{ number_format($camp->tiket_trainer, 0, ',', ',') }}" placeholder="Total Uang Tiket Trainer Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer_pulang" value="{{ number_format($camp->tiket_trainer_pulang, 0, ',', ',') }}" placeholder="Total Uang Tiket Trainer Pulang" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end -->

                    <!-- 1 -->
                    @if($camp->tiket_trainer1 != null && $camp->tiket_trainer1 != 0)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="tiket_trainer_nama1" value="{{ $camp->tiket_trainer_nama1 }}" placeholder="Nama Trainer" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer Berangkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer1" value="{{ number_format($camp->tiket_trainer1, 0, ',', ',') }}" placeholder="Total Uang Tiket Trainer Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer_pulang1" value="{{ number_format($camp->tiket_trainer_pulang1, 0, ',', ',') }}" placeholder="Total Uang Tiket Trainer Pulang" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 2 -->
                    @if($camp->tiket_trainer2 != null && $camp->tiket_trainer2 != 0)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="tiket_trainer_nama2" value="{{ $camp->tiket_trainer_nama2 }}" placeholder="Nama Trainer" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer Berangkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer2" value="{{ number_format($camp->tiket_trainer2, 0, ',', ',') }}" placeholder="Total Uang Tiket Trainer Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer_pulang2" value="{{ number_format($camp->tiket_trainer_pulang2, 0, ',', ',') }}" placeholder="Total Uang Tiket Trainer Pulang" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 3 -->
                    @if($camp->tiket_trainer3 != null && $camp->tiket_trainer3 != 0)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="tiket_trainer_nama3" value="{{ $camp->tiket_trainer_nama3 }}" placeholder="Nama Trainer" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer Berangkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer3" value="{{ number_format($camp->tiket_trainer3, 0, ',', ',') }}" placeholder="Total Uang Tiket Trainer Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer_pulang3" value="{{ number_format($camp->tiket_trainer_pulang3, 0, ',', ',') }}" placeholder="Total Uang Tiket Trainer Pulang" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 4 -->
                    @if($camp->tiket_trainer4 != null && $camp->tiket_trainer4 != 0)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="tiket_trainer_nama4" value="{{ $camp->tiket_trainer_nama4 }}" placeholder="Nama Trainer" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer Berangkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer4" value="{{ number_format($camp->tiket_trainer4, 0, ',', ',') }}" placeholder="Total Uang Tiket Trainer Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer_pulang4" value="{{ number_format($camp->tiket_trainer_pulang4, 0, ',', ',') }}" placeholder="Total Uang Tiket Trainer Pulang" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 5 -->
                    @if($camp->tiket_trainer5 != null && $camp->tiket_trainer5 != 0)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="tiket_trainer_nama5" value="{{ $camp->tiket_trainer_nama5 }}" placeholder="Nama Trainer" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer Berangkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer5" value="{{ number_format($camp->tiket_trainer5, 0, ',', ',') }}" placeholder="Total Uang Tiket Trainer Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer_pulang5" value="{{ number_format($camp->tiket_trainer_pulang5, 0, ',', ',') }}" placeholder="Total Uang Tiket Trainer Pulang" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 6 -->
                    @if($camp->tiket_trainer6 != null && $camp->tiket_trainer6 != 0)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="tiket_trainer_nama6" value="{{ $camp->tiket_trainer_nama6 }}" placeholder="Nama Trainer" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class=" col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer Berangkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer6" value="{{ number_format($camp->tiket_trainer6, 0, ',', ',') }}" placeholder="Total Uang Tiket Trainer Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer_pulang6" value="{{ number_format($camp->tiket_trainer_pulang6, 0, ',', ',') }}" placeholder="Total Uang Tiket Trainer Pulang" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 7 -->
                    @if($camp->tiket_trainer7 != null && $camp->tiket_trainer7 != 0)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Trainer</label>
                                <div class="input-group">
                                    <input type="text" name="tiket_trainer_nama7" value="{{ $camp->tiket_trainer_nama7 }}" placeholder="Nama Trainer" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer Berangkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer7" value="{{ number_format($camp->tiket_trainer7, 0, ',', ',') }}" placeholder="Total Uang Tiket Trainer Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Trainer Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_trainer_pulang7" value="{{ number_format($camp->tiket_trainer_pulang7, 0, ',', ',') }}" placeholder="Total Uang Tiket Trainer Pulang" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->
                    <!--================== end ==================-->

                    <?php
                    $value_berangkat = $camp->total_tiket_trainer_berangkat;
                    $value_pulang = $camp->total_tiket_trainer_pulang;
                    $value = $value_berangkat + $value_pulang;
                    ?>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Total Tiket Trainer</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="border-color: red;">Rp.</span>
                                    </div>
                                    <input type="text" style="border-color: red;" name="total_tiket_trainer_berangkat" value="{{ number_format($value, 0, ',', ',') }}" placeholder="Masukkan Uang Total Tiket Trainer Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--================== TIKET TEAM ==================-->
                    <!-- default -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="tiket_team_nama" value="{{ $camp->tiket_team_nama }}" placeholder="Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team Berangkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team" value="{{ number_format($camp->tiket_team, 0, ',', ',') }}" placeholder="Total Uang Tiket Team Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team_pulang" value="{{ number_format($camp->tiket_team_pulang, 0, ',', ',') }}" placeholder="Total Uang Tiket Team Pulang" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end -->

                    <!-- 1 -->
                    @if($camp->tiket_team1 != null && $camp->tiket_team1 != 0)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="tiket_team_nama1" value="{{ $camp->tiket_team_nama1 }}" placeholder="Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team Berangkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team1" value="{{ number_format($camp->tiket_team1, 0, ',', ',') }}" placeholder="Total Uang Tiket Team Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team_pulang1" value="{{ number_format($camp->tiket_team_pulang1, 0, ',', ',') }}" placeholder="Total Uang Tiket Team Pulang" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 2 -->
                    @if($camp->tiket_team2 != null && $camp->tiket_team2 != 0)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="tiket_team_nama2" value="{{ $camp->tiket_team_nama2 }}" placeholder="Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team Berangkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team2" value="{{ number_format($camp->tiket_team2, 0, ',', ',') }}" placeholder="Total Uang Tiket Team Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team_pulang2" value="{{ number_format($camp->tiket_team_pulang2, 0, ',', ',') }}" placeholder="Total Uang Tiket Team Pulang" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 3 -->
                    @if($camp->tiket_team3 != null && $camp->tiket_team3 != 0)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="tiket_team_nama3" value="{{ $camp->tiket_team_nama3 }}" placeholder="Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team Berangkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team3" value="{{ number_format($camp->tiket_team3, 0, ',', ',') }}" placeholder="Total Uang Tiket Team Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team_pulang3" value="{{ number_format($camp->tiket_team_pulang3, 0, ',', ',') }}" placeholder="Total Uang Tiket Team Pulang" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 4 -->
                    @if($camp->tiket_team4 != null && $camp->tiket_team4 != 0)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="tiket_team_nama4" value="{{ $camp->tiket_team_nama4 }}" placeholder="Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team Berangkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team4" value="{{ number_format($camp->tiket_team4, 0, ',', ',') }}" placeholder="Total Uang Tiket Team Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team_pulang4" value="{{ number_format($camp->tiket_team_pulang4, 0, ',', ',') }}" placeholder="Total Uang Tiket Team Pulang" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 5 -->
                    @if($camp->tiket_team5 != null && $camp->tiket_team5 != 0)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="tiket_team_nama5" value="{{ $camp->tiket_team_nama5 }}" placeholder="Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team Berangkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team5" value="{{ number_format($camp->tiket_team5, 0, ',', ',') }}" placeholder="Total Uang Tiket Team Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team_pulang5" value="{{ number_format($camp->tiket_team_pulang5, 0, ',', ',') }}" placeholder="Total Uang Tiket Team Pulang" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 6 -->
                    @if($camp->tiket_team6 != null && $camp->tiket_team6 != 0)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="tiket_team_nama6" value="{{ $camp->tiket_team_nama6 }}" placeholder="Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team Berangkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team6" value="{{ number_format($camp->tiket_team6, 0, ',', ',') }}" placeholder="Total Uang Tiket Team Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team_pulang6" value="{{ number_format($camp->tiket_team_pulang6, 0, ',', ',') }}" placeholder="Total Uang Tiket Team Pulang" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->

                    <!-- 7 -->
                    @if($camp->tiket_team7 != null && $camp->tiket_team7 != 0)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Team</label>
                                <div class="input-group">
                                    <input type="text" name="tiket_team_nama7" value="{{ $camp->tiket_team_nama7 }}" placeholder="Nama Team" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team Berangkat</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team7" value="{{ number_format($camp->tiket_team7, 0, ',', ',') }}" placeholder="Total Uang Tiket Team Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiket Team Pulang</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp.</span>
                                    </div>
                                    <input type="text" name="tiket_team_pulang7" value="{{ number_format($camp->tiket_team_pulang7, 0, ',', ',') }}" placeholder="Total Uang Tiket Team Pulang" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- end -->
                    <!--================== end ==================-->

                    <?php
                    $value_team_berangkat = $camp->total_tiket_team_berangkat;
                    $value_team_pulang = $camp->total_tiket_team_pulang;
                    $value_team = $value_team_berangkat + $value_team_pulang;
                    ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Total Tiket Team</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="border-color: red;">Rp.</span>
                                    </div>
                                    <input type="text" style="border-color: red;" name="total_tiket_team_berangkat" value="{{ number_format($value_team, 0, ',', ',') }}" placeholder="Masukkan Uang Total Tiket Team Berangkat" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>UPDATE LAINNYA</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                    </div>

                    <!--================== LAINNYA ==================-->
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
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Total Komisi Marketing (10%)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="border-color: red;">Rp.</span>
                                    </div>
                                    <input type="text" name="marketing" style="border-color: red;" value="{{ number_format($camp->marketing, 0, ',', ',') }}" placeholder="Masukkan Gaji Pokok Karyawan" class="form-control" readonly>
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
                                    <input type="text" name="persentase_keuntungan" style="border-color: red;" value="{{ rtrim(rtrim(number_format($camp->persentase_keuntungan, 1, ',', '.'), '0'), ',') }}%" placeholder="Masukkan Gaji Pokok Karyawan" class="form-control currency7" readonly>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="border-color: red;">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Status Pembayaran</label>
                                <select class="form-control" name="status" disabled>
                                    <option value="" disabled selected>Silahkan Pilih</option>
                                    <option value="pending" {{ $camp->status == 'pending' ? 'selected' : '' }}>PENDING</option>
                                    <option value="terbayar" {{ $camp->status == 'terbayar' ? 'selected' : '' }}>TERBAYAR</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label>Catatan</label>
                            <div class="card card-outline card-info">
                                <div class="card-body pad">
                                    <textarea name="note" id="note" placeholder="Masukkan catatan" class="textarea" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;" readonly>{{ $camp->note }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex m-3">
                        <a href="{{ route('account.camp.index') }}" class="btn btn-info btn-reset rounded-pill" style="width: 100%; font-size: 14px;">
                            <i class="fa fa-redo"></i> KEMBALI
                        </a>
                    </div>

                    </form>

                </div>
            </div>
        </div>
    </section>
</div>

<!--================== SUMMERNOTE ==================-->
<script src="{{ asset('assets/artikel/summernote/summernote-bs4.min.js') }}"></script>
<script>
    $(function() {
        // Initialize Summernote
        $('.textarea').summernote({
            height: 300, // Set the height of the editor to 300px
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link']], // Only keep the link button
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    })
</script>
<!--================== END ==================-->

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

    var cleaveC = new Cleave('.currency_gaji_trainer', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });

    var cleaveC = new Cleave('.currency_gaji_team', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });

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