@extends('layouts.account')

@section('title')
Tambah laporan Camp | MIS
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

<!--================== button tiket trainer berangkat responsive ==================-->
<style>
  /* Default styling for the button */
  #addTiketTrainerBerangkat,
  #removeTiketTrainerBerangkat1,
  #removeTiketTrainerBerangkat2,
  #removeTiketTrainerBerangkat3,
  #removeTiketTrainerBerangkat4,
  #removeTiketTrainerBerangkat5,
  #removeTiketTrainerBerangkat6,
  #removeTiketTrainerBerangkat7 {
    height: 40px;
    white-space: nowrap;
  }

  /* Media query for handphones (width 767px or less) */
  @media (max-width: 767px) {

    #addTiketTrainerBerangkat,
    #removeTiketTrainerBerangkat1,
    #removeTiketTrainerBerangkat2,
    #removeTiketTrainerBerangkat3,
    #removeTiketTrainerBerangkat4,
    #removeTiketTrainerBerangkat5,
    #removeTiketTrainerBerangkat6,
    #removeTiketTrainerBerangkat7 {
      width: 100%;
    }
  }

  /* Media query for tablets (width between 768px and 991px) */
  @media (min-width: 768px) and (max-width: 991px) {

    #addTiketTrainerBerangkat,
    #removeTiketTrainerBerangkat1,
    #removeTiketTrainerBerangkat2,
    #removeTiketTrainerBerangkat3,
    #removeTiketTrainerBerangkat4,
    #removeTiketTrainerBerangkat5,
    #removeTiketTrainerBerangkat6,
    #removeTiketTrainerBerangkat7 {
      width: auto;
      /* atau atur sesuai kebutuhan pada tablet */
    }
  }

  /* Styling for larger screens (laptops, monitors) */
  @media (min-width: 992px) {

    #addTiketTrainerBerangkat,
    #removeTiketTrainerBerangkat1,
    #removeTiketTrainerBerangkat2,
    #removeTiketTrainerBerangkat3,
    #removeTiketTrainerBerangkat4,
    #removeTiketTrainerBerangkat5,
    #removeTiketTrainerBerangkat6,
    #removeTiketTrainerBerangkat7 {
      width: auto;
      /* Atur sesuai kebutuhan pada laptop atau monitor */
    }
  }
</style>
<!--================== end ==================-->

<!--================== button tiket trainer pulang responsive ==================-->
<style>
  /* Default styling for the button */
  #addTiketTrainerPulang,
  #removeTiketTrainerPulang1,
  #removeTiketTrainerPulang2,
  #removeTiketTrainerPulang3,
  #removeTiketTrainerPulang4,
  #removeTiketTrainerPulang5,
  #removeTiketTrainerPulang6,
  #removeTiketTrainerPulang7 {
    height: 40px;
    white-space: nowrap;
  }

  /* Media query for handphones (width 767px or less) */
  @media (max-width: 767px) {

    #addTiketTrainerPulang,
    #removeTiketTrainerPulang1,
    #removeTiketTrainerPulang2,
    #removeTiketTrainerPulang3,
    #removeTiketTrainerPulang4,
    #removeTiketTrainerPulang5,
    #removeTiketTrainerPulang6,
    #removeTiketTrainerPulang7 {
      width: 100%;
    }
  }

  /* Media query for tablets (width between 768px and 991px) */
  @media (min-width: 768px) and (max-width: 991px) {

    #addTiketTrainerPulang,
    #removeTiketTrainerPulang1,
    #removeTiketTrainerPulang2,
    #removeTiketTrainerPulang3,
    #removeTiketTrainerPulang4,
    #removeTiketTrainerPulang5,
    #removeTiketTrainerPulang6,
    #removeTiketTrainerPulang7 {
      width: auto;
      /* atau atur sesuai kebutuhan pada tablet */
    }
  }

  /* Styling for larger screens (laptops, monitors) */
  @media (min-width: 992px) {

    #addTiketTrainerPulang,
    #removeTiketTrainerPulang1,
    #removeTiketTrainerPulang2,
    #removeTiketTrainerPulang3,
    #removeTiketTrainerPulang4,
    #removeTiketTrainerPulang5,
    #removeTiketTrainerPulang6,
    #removeTiketTrainerPulang7 {
      width: auto;
      /* Atur sesuai kebutuhan pada laptop atau monitor */
    }
  }
</style>
<!--================== end ==================-->

<!--================== button tiket team berangkat responsive ==================-->
<style>
  /* Default styling for the button */
  #addTiketTeamBerangkat,
  #removeTiketTeamBerangkat1,
  #removeTiketTeamBerangkat2,
  #removeTiketTeamBerangkat3,
  #removeTiketTeamBerangkat4,
  #removeTiketTeamBerangkat5,
  #removeTiketTeamBerangkat6,
  #removeTiketTeamBerangkat7 {
    height: 40px;
    white-space: nowrap;
  }

  /* Media query for handphones (width 767px or less) */
  @media (max-width: 767px) {

    #addTiketTeamBerangkat,
    #removeTiketTeamBerangkat1,
    #removeTiketTeamBerangkat2,
    #removeTiketTeamBerangkat3,
    #removeTiketTeamBerangkat4,
    #removeTiketTeamBerangkat5,
    #removeTiketTeamBerangkat6,
    #removeTiketTeamBerangkat7 {
      width: 100%;
    }
  }

  /* Media query for tablets (width between 768px and 991px) */
  @media (min-width: 768px) and (max-width: 991px) {

    #addTiketTeamBerangkat,
    #removeTiketTeamBerangkat1,
    #removeTiketTeamBerangkat2,
    #removeTiketTeamBerangkat3,
    #removeTiketTeamBerangkat4,
    #removeTiketTeamBerangkat5,
    #removeTiketTeamBerangkat6,
    #removeTiketTeamBerangkat7 {
      width: auto;
      /* atau atur sesuai kebutuhan pada tablet */
    }
  }

  /* Styling for larger screens (laptops, monitors) */
  @media (min-width: 992px) {

    #addTiketTeamBerangkat,
    #removeTiketTeamBerangkat1,
    #removeTiketTeamBerangkat2,
    #removeTiketTeamBerangkat3,
    #removeTiketTeamBerangkat4,
    #removeTiketTeamBerangkat5,
    #removeTiketTeamBerangkat6,
    #removeTiketTeamBerangkat7 {
      width: auto;
      /* Atur sesuai kebutuhan pada laptop atau monitor */
    }
  }
</style>
<!--================== end ==================-->

<!--================== button tiket team pulang responsive ==================-->
<style>
  /* Default styling for the button */
  #addTiketTeamPulang,
  #removeTiketTeamPulang1,
  #removeTiketTeamPulang2,
  #removeTiketTeamPulang3,
  #removeTiketTeamPulang4,
  #removeTiketTeamPulang5,
  #removeTiketTeamPulang6,
  #removeTiketTeamPulang7 {
    height: 40px;
    white-space: nowrap;
  }

  /* Media query for handphones (width 767px or less) */
  @media (max-width: 767px) {

    #addTiketTeamPulang,
    #removeTiketTeamPulang1,
    #removeTiketTeamPulang2,
    #removeTiketTeamPulang3,
    #removeTiketTeamPulang4,
    #removeTiketTeamPulang5,
    #removeTiketTeamPulang6,
    #removeTiketTeamPulang7 {
      width: 100%;
    }
  }

  /* Media query for tablets (width between 768px and 991px) */
  @media (min-width: 768px) and (max-width: 991px) {

    #addTiketTeamPulang,
    #removeTiketTeamPulang1,
    #removeTiketTeamPulang2,
    #removeTiketTeamPulang3,
    #removeTiketTeamPulang4,
    #removeTiketTeamPulang5,
    #removeTiketTeamPulang6,
    #removeTiketTeamPulang7 {
      width: auto;
      /* atau atur sesuai kebutuhan pada tablet */
    }
  }

  /* Styling for larger screens (laptops, monitors) */
  @media (min-width: 992px) {

    #addTiketTeamPulang,
    #removeTiketTeamPulang1,
    #removeTiketTeamPulang2,
    #removeTiketTeamPulang3,
    #removeTiketTeamPulang4,
    #removeTiketTeamPulang5,
    #removeTiketTeamPulang6,
    #removeTiketTeamPulang7 {
      width: auto;
      /* Atur sesuai kebutuhan pada laptop atau monitor */
    }
  }
</style>
<!--================== end ==================-->
<link rel="stylesheet" href="{{ asset('assets/artikel/summernote/summernote-bs4.css') }}">

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>TAMBAH LAPORAN CAMP</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <div class="card-header">
          <h4>TAMBAH DETAIL CAMP</h4>
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
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="Masukkan Nama Camp" class="form-control" style="text-transform:uppercase;" required>
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
                    <input type="number" name="camp_ke" value="{{ old('camp_ke') }}" placeholder="Masukkan Nomor Camp Ke" class="form-control" required>
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
                  <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal') }}" placeholder="Masukkan Total Tunjangan" class="form-control" required>
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
                  <input type="date" name="tanggal_akhir" id="tanggal_akhir" value="{{ old('tanggal_akhir') }}" placeholder="Masukkan Total Tunjangan" class="form-control" required>
                </div>
                @error('tanggal_akhir')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Jumlah Peserta</label>
                  <div class="input-group">
                    <input type="number" name="peserta" value="{{ old('peserta') }}" placeholder="Masukkan Jumlah peserta" class="form-control currency_peserta">
                  </div>
                  @error('peserta')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
            </div>

        </div>
      </div>
      <!--================== END ==================-->


      <div class="card">
        <div class="card-header">
          <h4>TAMBAH UANG MASUK</h4>
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
                  <input type="text" name="uang_masuk" value="{{ old('uang_masuk') }}" placeholder="Total Uang Masuk" class="form-control currency" required>
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
                  <input type="text" name="lain_lain" value="{{ old('lain_lain') }}" placeholder="Total Uang Masuk Lainnya" class="form-control currency1">
                </div>
                @error('lain_lain')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
          </div>
          <!--================== END ==================-->
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h4>TAMBAH GAJI</h4>
        </div>
        <div class="card-body">

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
                  <input type="text" name="gaji_trainer" value="{{ old('gaji_trainer') }}" placeholder="Total Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer">
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
                  <input type="text" name="gaji_trainer_nama" value="{{ old('gaji_trainer_nama') }}" placeholder="Masukkan Nama Trainer" class="form-control">
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
          <div class="row trainer-field1" style="display: none;">
            <div class=" col-md-5">
              <div class="form-group">
                <label>Gaji Trainer</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_trainer1" value="{{ old('gaji_trainer1') }}" placeholder="Total Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer1">
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
                  <input type="text" name="gaji_trainer_nama1" value="{{ old('gaji_trainer_nama1') }}" placeholder="Masukkan Nama Trainer" class="form-control">
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
          <!-- end -->

          <!-- 2 -->
          <div class="row trainer-field2" style="display: none;">
            <div class="col-md-5">
              <div class="form-group">
                <label>Gaji Trainer</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_trainer2" value="{{ old('gaji_trainer2') }}" placeholder="Total Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer2">
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
                  <input type="text" name="gaji_trainer_nama2" value="{{ old('gaji_trainer_nama2') }}" placeholder="Masukkan Nama Trainer" class="form-control">
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
          <!-- end -->

          <!-- 3 -->
          <div class="row trainer-field3" style="display: none;">
            <div class="col-md-5">
              <div class="form-group">
                <label>Gaji Trainer</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_trainer3" value="{{ old('gaji_trainer3') }}" placeholder="Total Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer3">
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
                  <input type="text" name="gaji_trainer_nama3" value="{{ old('gaji_trainer_nama3') }}" placeholder="Masukkan Nama Trainer" class="form-control">
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
          <!-- end -->

          <!-- 4 -->
          <div class="row trainer-field4" style="display: none;">
            <div class="col-md-5">
              <div class="form-group">
                <label>Gaji Trainer</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_trainer4" value="{{ old('gaji_trainer4') }}" placeholder="Total Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer4">
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
                  <input type="text" name="gaji_trainer_nama4" value="{{ old('gaji_trainer_nama4') }}" placeholder="Masukkan Nama Trainer" class="form-control">
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
          <!-- end -->

          <!-- 5 -->
          <div class="row trainer-field5" style="display: none;">
            <div class="col-md-5">
              <div class="form-group">
                <label>Gaji Trainer</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_trainer5" value="{{ old('gaji_trainer5') }}" placeholder="Total Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer5">
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
                  <input type="text" name="gaji_trainer_nama5" value="{{ old('gaji_trainer_nama5') }}" placeholder="Masukkan Nama Trainer" class="form-control">
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
          <!-- end -->

          <!-- 6 -->
          <div class="row trainer-field6" style="display: none;">
            <div class="col-md-5">
              <div class="form-group">
                <label>Gaji Trainer</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_trainer6" value="{{ old('gaji_trainer6') }}" placeholder="Total Uang Keluar Gaji Trainer" class="form-control currency_gaji_trainer6">
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
                  <input type="text" name="gaji_trainer_nama6" value="{{ old('gaji_trainer_nama6') }}" placeholder="Masukkan Nama Trainer" class="form-control">
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
          <!-- end -->
          <!--================== end ==================-->

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
                  <input type="text" name="gaji_team" value="{{ old('gaji_team') }}" placeholder="Total Uang Keluar Gaji Team" class="form-control currency_gaji_team">
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
                  <input type="text" name="gaji_team_nama" value="{{ old('gaji_team_nama') }}" placeholder="Masukkan Nama Team" class="form-control">
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
          <div class="row team-field1" style="display: none;">
            <div class="col-md-5">
              <div class="form-group">
                <label>Gaji Team</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_team1" value="{{ old('gaji_team1') }}" placeholder="Total Uang Keluar Gaji Team" class="form-control currency_gaji_team1">
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
                  <input type="text" name="gaji_team_nama1" value="{{ old('gaji_team_nama1') }}" placeholder="Masukkan Nama Team" class="form-control">
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
          <!-- end -->

          <!-- 2 -->
          <div class="row team-field2" style="display: none;">
            <div class="col-md-5">
              <div class="form-group">
                <label>Gaji Team</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_team2" value="{{ old('gaji_team2') }}" placeholder="Total Uang Keluar Gaji Team" class="form-control currency_gaji_team2">
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
                  <input type="text" name="gaji_team_nama2" value="{{ old('gaji_team_nama2') }}" placeholder="Masukkan Nama Team" class="form-control">
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
          <!-- end -->

          <!-- 3 -->
          <div class="row team-field3" style="display: none;">
            <div class="col-md-5">
              <div class="form-group">
                <label>Gaji Team</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_team3" value="{{ old('gaji_team3') }}" placeholder="Total Uang Keluar Gaji Team" class="form-control currency_gaji_team3">
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
                  <input type="text" name="gaji_team_nama3" value="{{ old('gaji_team_nama3') }}" placeholder="Masukkan Nama Team" class="form-control">
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
          <!-- end -->

          <!-- 4 -->
          <div class="row team-field4" style="display: none;">
            <div class="col-md-5">
              <div class="form-group">
                <label>Gaji Team</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_team4" value="{{ old('gaji_team4') }}" placeholder="Total Uang Keluar Gaji Team" class="form-control currency_gaji_team4">
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
                  <input type="text" name="gaji_team_nama4" value="{{ old('gaji_team_nama4') }}" placeholder="Masukkan Nama Team" class="form-control">
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
          <!-- end -->

          <!-- 5 -->
          <div class="row team-field5" style="display: none;">
            <div class="col-md-5">
              <div class="form-group">
                <label>Gaji Team</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_team5" value="{{ old('gaji_team5') }}" placeholder="Total Uang Keluar Gaji Team" class="form-control currency_gaji_team5">
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
                  <input type="text" name="gaji_team_nama5" value="{{ old('gaji_team_nama5') }}" placeholder="Masukkan Nama Team" class="form-control">
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
          <!-- end -->

          <!-- 6 -->
          <div class="row team-field6" style="display: none;">
            <div class="col-md-5">
              <div class="form-group">
                <label>Gaji Team</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_team6" value="{{ old('gaji_team6') }}" placeholder="Total Uang Keluar Gaji Team" class="form-control currency_gaji_team6">
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
                  <input type="text" name="gaji_team_nama6" value="{{ old('gaji_team_nama6') }}" placeholder="Masukkan Nama Team" class="form-control">
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
          <!-- end -->

          <!-- 7 -->
          <div class="row team-field7" style="display: none;">
            <div class="col-md-5">
              <div class="form-group">
                <label>Gaji Team</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_team7" value="{{ old('gaji_team7') }}" placeholder="Total Uang Keluar Gaji Team" class="form-control currency_gaji_team7">
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
                  <input type="text" name="gaji_team_nama7" value="{{ old('gaji_team_nama7') }}" placeholder="Masukkan Nama Team" class="form-control">
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
          <!-- end -->

          <!-- 8 -->
          <div class="row team-field8" style="display: none;">
            <div class="col-md-5">
              <div class="form-group">
                <label>Gaji Team</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_team8" value="{{ old('gaji_team8') }}" placeholder="Total Uang Keluar Gaji Team" class="form-control currency_gaji_team8">
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
                  <input type="text" name="gaji_team_nama8" value="{{ old('gaji_team_nama8') }}" placeholder="Masukkan Nama Team" class="form-control">
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
          <!-- end -->

          <!-- 9 -->
          <div class="row team-field9" style="display: none;">
            <div class="col-md-5">
              <div class="form-group">
                <label>Gaji Team</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_team9" value="{{ old('gaji_team9') }}" placeholder="Total Uang Keluar Gaji Team" class="form-control currency_gaji_team9">
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
                  <input type="text" name="gaji_team_nama9" value="{{ old('gaji_team_nama9') }}" placeholder="Masukkan Nama Team" class="form-control">
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
          <!-- end -->

          <!-- 10 -->
          <div class="row team-field10" style="display: none;">
            <div class="col-md-5">
              <div class="form-group">
                <label>Gaji Team</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="gaji_team10" value="{{ old('gaji_team10') }}" placeholder="Total Uang Keluar Gaji Team" class="form-control currency_gaji_team10">
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
                  <input type="text" name="gaji_team_nama10" value="{{ old('gaji_team_nama10') }}" placeholder="Masukkan Nama Team" class="form-control">
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
          <!-- end -->
          <!--================== end ==================-->
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h4>TAMBAH UANG KELUAR</h4>
        </div>
        <div class="card-body">

          <!--================== UANG KELUAR ==================-->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Team Cabang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="team_cabang" value="{{ old('team_cabang') }}" placeholder="Total Uang Keluar Team Cabang" class="form-control currency_team_cabang">
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
                  <input type="text" name="booknote" value="{{ old('booknote') }}" placeholder="Total Uang Keluar Booknote" class="form-control currency_booknote">
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
                  <input type="text" name="grammarly" value="{{ old('grammarly') }}" placeholder="Total Uang Keluar Grammarly" class="form-control currency_grammarly">
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
                <label>Hotel</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="hotel" value="{{ old('hotel') }}" placeholder="Masukkan Uang Keluar Hotel" class="form-control currency-hotel">
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
                  <input type="text" name="konsumsi_tambahan" value="{{ old('konsumsi_tambahan') }}" placeholder="Masukkan Uang Keluar Konsumsi Tambahan" class="form-control currency_konsumsi_tambahan">
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
                  <input type="text" name="lainnya" value="{{ old('lainnya') }}" placeholder="Masukkan Uang Keluar Lain-Lain" class="form-control currency_lainnya">
                </div>
                @error('lainnya')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
          </div>
          <!--================== END ==================-->
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h4>TAMBAH TIKET</h4>
        </div>
        <div class="card-body">
          <!--================== TIKET TRAINER ==================-->
          <!-- default -->
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Nama Trainer</label>
                <div class="input-group">
                  <input type="text" name="tiket_trainer_nama" value="{{ old('tiket_trainer_nama') }}" placeholder="Masukkan Nama Trainer" class="form-control">
                </div>
                @error('tiket_trainer_nama')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Trainer Berangkat</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_trainer" value="{{ old('tiket_trainer') }}" placeholder="Total Uang Tiket Trainer Berangkat" class="form-control tiket_trainer">
                </div>
                @error('tiket_trainer')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Trainer Pulang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_trainer_pulang" value="{{ old('tiket_trainer_pulang') }}" placeholder="Total Uang Tiket Trainer Pulang" class="form-control tiket_trainer_pulang">
                </div>
                @error('tiket_trainer_pulang')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="col-md-1 col-12">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-info mt-2" id="addTiketTrainerBerangkat" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-plus"></i> INPUT
                </button>
              </div>
            </div>
          </div>
          <!-- end -->

          <!-- 1 -->
          <div class="row TiketTrainerBerangkat-field1" style="display: none;">

            <div class="col-md-12">
              <div class="form-group">
                <label>Nama Trainer</label>
                <div class="input-group">
                  <input type="text" name="tiket_trainer_nama1" value="{{ old('tiket_trainer_nama1') }}" placeholder="Masukkan Nama Trainer" class="form-control">
                </div>
                @error('tiket_trainer_nama1')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Trainer Berangkat</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_trainer1" value="{{ old('tiket_trainer1') }}" placeholder="Total Uang Tiket Trainer Berangkat" class="form-control tiket_trainer1">
                </div>
                @error('tiket_trainer1')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Trainer Pulang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_trainer_pulang1" value="{{ old('tiket_trainer_pulang1') }}" placeholder="Total Uang Tiket Trainer Pulang" class="form-control tiket_trainer_pulang1">
                </div>
                @error('tiket_trainer_pulang1')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-1 col-12">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeTiketTrainerBerangkat1" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div>
          </div>
          <!-- end -->

          <!-- 2 -->
          <div class="row TiketTrainerBerangkat-field2" style="display: none;">

            <div class="col-md-12">
              <div class="form-group">
                <label>Nama Trainer</label>
                <div class="input-group">
                  <input type="text" name="tiket_trainer_nama2" value="{{ old('tiket_trainer_nama2') }}" placeholder="Masukkan Nama Trainer" class="form-control">
                </div>
                @error('tiket_trainer_nama2')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Trainer Berangkat</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_trainer2" value="{{ old('tiket_trainer2') }}" placeholder="Total Uang Tiket Trainer Berangkat" class="form-control tiket_trainer2">
                </div>
                @error('tiket_trainer2')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Trainer Pulang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_trainer_pulang2" value="{{ old('tiket_trainer_pulang2') }}" placeholder="Total Uang Tiket Trainer Pulang" class="form-control tiket_trainer_pulang2">
                </div>
                @error('tiket_trainer_pulang2')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-1 col-12">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeTiketTrainerBerangkat2" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div>
          </div>
          <!-- end -->

          <!-- 3 -->
          <div class="row TiketTrainerBerangkat-field3" style="display: none;">

            <div class="col-md-12">
              <div class="form-group">
                <label>Nama Trainer</label>
                <div class="input-group">
                  <input type="text" name="tiket_trainer_nama3" value="{{ old('tiket_trainer_nama3') }}" placeholder="Masukkan Nama Trainer" class="form-control">
                </div>
                @error('tiket_trainer_nama3')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Trainer Berangkat</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_trainer3" value="{{ old('tiket_trainer3') }}" placeholder="Total Uang Tiket Trainer Berangkat" class="form-control tiket_trainer3">
                </div>
                @error('tiket_trainer3')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Trainer Pulang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_trainer_pulang3" value="{{ old('tiket_trainer_pulang3') }}" placeholder="Total Uang Tiket Trainer Pulang" class="form-control tiket_trainer_pulang3">
                </div>
                @error('tiket_trainer_pulang3')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-1 col-12">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeTiketTrainerBerangkat3" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div>
          </div>
          <!-- end -->

          <!-- 4 -->
          <div class="row TiketTrainerBerangkat-field4" style="display: none;">

            <div class="col-md-12">
              <div class="form-group">
                <label>Nama Trainer</label>
                <div class="input-group">
                  <input type="text" name="tiket_trainer_nama4" value="{{ old('tiket_trainer_nama4') }}" placeholder="Masukkan Nama Trainer" class="form-control">
                </div>
                @error('tiket_trainer_nama4')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Trainer Berangkat</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_trainer4" value="{{ old('tiket_trainer4') }}" placeholder="Total Uang Tiket Trainer Berangkat" class="form-control tiket_trainer4">
                </div>
                @error('tiket_trainer4')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Trainer Pulang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_trainer_pulang4" value="{{ old('tiket_trainer_pulang4') }}" placeholder="Total Uang Tiket Trainer Pulang" class="form-control tiket_trainer_pulang4">
                </div>
                @error('tiket_trainer_pulang4')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-1 col-12">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeTiketTrainerBerangkat4" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div>
          </div>
          <!-- end -->

          <!-- 5 -->
          <div class="row TiketTrainerBerangkat-field5" style="display: none;">

            <div class="col-md-12">
              <div class="form-group">
                <label>Nama Trainer</label>
                <div class="input-group">
                  <input type="text" name="tiket_trainer_nama5" value="{{ old('tiket_trainer_nama5') }}" placeholder="Masukkan Nama Trainer" class="form-control">
                </div>
                @error('tiket_trainer_nama5')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Trainer Berangkat</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_trainer5" value="{{ old('tiket_trainer5') }}" placeholder="Total Uang Tiket Trainer Berangkat" class="form-control tiket_trainer5">
                </div>
                @error('tiket_trainer5')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Trainer Pulang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_trainer_pulang5" value="{{ old('tiket_trainer_pulang5') }}" placeholder="Total Uang Tiket Trainer Pulang" class="form-control tiket_trainer_pulang5">
                </div>
                @error('tiket_trainer_pulang5')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-1 col-12">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeTiketTrainerBerangkat5" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div>
          </div>
          <!-- end -->

          <!-- 6 -->
          <div class="row TiketTrainerBerangkat-field6" style="display: none;">

            <div class="col-md-12">
              <div class="form-group">
                <label>Nama Trainer</label>
                <div class="input-group">
                  <input type="text" name="tiket_trainer_nama6" value="{{ old('tiket_trainer_nama6') }}" placeholder="Masukkan Nama Trainer" class="form-control">
                </div>
                @error('tiket_trainer_nama6')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class=" col-md-5">
              <div class="form-group">
                <label>Tiket Trainer Berangkat</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_trainer6" value="{{ old('tiket_trainer6') }}" placeholder="Total Uang Tiket Trainer Berangkat" class="form-control tiket_trainer6">
                </div>
                @error('tiket_trainer6')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Trainer Pulang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_trainer_pulang6" value="{{ old('tiket_trainer_pulang6') }}" placeholder="Total Uang Tiket Trainer Pulang" class="form-control tiket_trainer_pulang6">
                </div>
                @error('tiket_trainer_pulang6')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-1 col-12">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeTiketTrainerBerangkat6" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div>
          </div>
          <!-- end -->

          <!-- 7 -->
          <div class="row TiketTrainerBerangkat-field7" style="display: none;">

            <div class="col-md-12">
              <div class="form-group">
                <label>Nama Trainer</label>
                <div class="input-group">
                  <input type="text" name="tiket_trainer_nama7" value="{{ old('tiket_trainer_nama7') }}" placeholder="Masukkan Nama Trainer" class="form-control">
                </div>
                @error('tiket_trainer_nama7')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Trainer Berangkat</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_trainer7" value="{{ old('tiket_trainer7') }}" placeholder="Total Uang Tiket Trainer Berangkat" class="form-control tiket_trainer7">
                </div>
                @error('tiket_trainer7')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Trainer Pulang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_trainer_pulang7" value="{{ old('tiket_trainer_pulang7') }}" placeholder="Total Uang Tiket Trainer Pulang" class="form-control tiket_trainer_pulang7">
                </div>
                @error('tiket_trainer_pulang7')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-1 col-12">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeTiketTrainerBerangkat7" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div>
          </div>
          <!-- end -->
          <!--================== end ==================-->

          <!--================== TIKET TEAM ==================-->
          <!-- default -->
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Nama Team</label>
                <div class="input-group">
                  <input type="text" name="tiket_team_nama" value="{{ old('tiket_team_nama') }}" placeholder="Masukkan Nama Team" class="form-control">
                </div>
                @error('tiket_team_nama')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Team Berangkat</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_team" value="{{ old('tiket_team') }}" placeholder="Total Uang Tiket Team Berangkat" class="form-control tiket_team">
                </div>
                @error('tiket_team')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Team Pulang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_team_pulang" value="{{ old('tiket_team_pulang') }}" placeholder="Total Uang Tiket Team Pulang" class="form-control tiket_team_pulang">
                </div>
                @error('tiket_team_pulang')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-1 col-12">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-info mt-2" id="addTiketTeamBerangkat" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-plus"></i> INPUT
                </button>
              </div>
            </div>
          </div>
          <!-- end -->

          <!-- 1 -->
          <div class="row TiketTeamBerangkat-field1" style="display: none;">

            <div class="col-md-12">
              <div class="form-group">
                <label>Nama Team</label>
                <div class="input-group">
                  <input type="text" name="tiket_team_nama1" value="{{ old('tiket_team_nama1') }}" placeholder="Masukkan Nama Team" class="form-control">
                </div>
                @error('tiket_team_nama1')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Team Berangkat</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_team1" value="{{ old('tiket_team1') }}" placeholder="Total Uang Tiket Team Berangkat" class="form-control tiket_team1">
                </div>
                @error('tiket_team1')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Team Pulang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_team_pulang1" value="{{ old('tiket_team_pulang1') }}" placeholder="Total Uang Tiket Team Pulang" class="form-control tiket_team_pulang1">
                </div>
                @error('tiket_team_pulang1')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-1 col-12">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeTiketTeamBerangkat1" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div>
          </div>
          <!-- end -->

          <!-- 2 -->
          <div class="row TiketTeamBerangkat-field2" style="display: none;">

            <div class="col-md-12">
              <div class="form-group">
                <label>Nama Team</label>
                <div class="input-group">
                  <input type="text" name="tiket_team_nama2" value="{{ old('tiket_team_nama2') }}" placeholder="Masukkan Nama Team" class="form-control">
                </div>
                @error('tiket_team_nama2')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Team Berangkat</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_team2" value="{{ old('tiket_team2') }}" placeholder="Total Uang Tiket Team Berangkat" class="form-control tiket_team2">
                </div>
                @error('tiket_team2')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Team Pulang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_team_pulang2" value="{{ old('tiket_team_pulang2') }}" placeholder="Total Uang Tiket Team Pulang" class="form-control tiket_team_pulang2">
                </div>
                @error('tiket_team_pulang2')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-1 col-12">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeTiketTeamBerangkat2" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div>
          </div>
          <!-- end -->

          <!-- 3 -->
          <div class="row TiketTeamBerangkat-field3" style="display: none;">

            <div class="col-md-12">
              <div class="form-group">
                <label>Nama Team</label>
                <div class="input-group">
                  <input type="text" name="tiket_team_nama3" value="{{ old('tiket_team_nama3') }}" placeholder="Masukkan Nama Team" class="form-control">
                </div>
                @error('tiket_team_nama3')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Team Berangkat</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_team3" value="{{ old('tiket_team3') }}" placeholder="Total Uang Tiket Team Berangkat" class="form-control tiket_team3">
                </div>
                @error('tiket_team3')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Team Pulang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_team_pulang3" value="{{ old('tiket_team_pulang3') }}" placeholder="Total Uang Tiket Team Pulang" class="form-control tiket_team_pulang3">
                </div>
                @error('tiket_team_pulang3')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-1 col-12">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeTiketTeamBerangkat3" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div>
          </div>
          <!-- end -->

          <!-- 4 -->
          <div class="row TiketTeamBerangkat-field4" style="display: none;">

            <div class="col-md-12">
              <div class="form-group">
                <label>Nama Team</label>
                <div class="input-group">
                  <input type="text" name="tiket_team_nama4" value="{{ old('tiket_team_nama4') }}" placeholder="Masukkan Nama Team" class="form-control">
                </div>
                @error('tiket_team_nama4')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Team Berangkat</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_team4" value="{{ old('tiket_team4') }}" placeholder="Total Uang Tiket Team Berangkat" class="form-control tiket_team4">
                </div>
                @error('tiket_team4')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Team Pulang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_team_pulang4" value="{{ old('tiket_team_pulang4') }}" placeholder="Total Uang Tiket Team Pulang" class="form-control tiket_team_pulang4">
                </div>
                @error('tiket_team_pulang4')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-1 col-12">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeTiketTeamBerangkat4" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div>
          </div>
          <!-- end -->

          <!-- 5 -->
          <div class="row TiketTeamBerangkat-field5" style="display: none;">

            <div class="col-md-12">
              <div class="form-group">
                <label>Nama Team</label>
                <div class="input-group">
                  <input type="text" name="tiket_team_nama5" value="{{ old('tiket_team_nama5') }}" placeholder="Masukkan Nama Team" class="form-control">
                </div>
                @error('tiket_team_nama5')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Team Berangkat</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_team5" value="{{ old('tiket_team5') }}" placeholder="Total Uang Tiket Team Berangkat" class="form-control tiket_team5">
                </div>
                @error('tiket_team5')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Team Pulang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_team_pulang5" value="{{ old('tiket_team_pulang5') }}" placeholder="Total Uang Tiket Team Pulang" class="form-control tiket_team_pulang5">
                </div>
                @error('tiket_team_pulang5')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-1 col-12">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeTiketTeamBerangkat5" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div>
          </div>
          <!-- end -->

          <!-- 6 -->
          <div class="row TiketTeamBerangkat-field6" style="display: none;">

            <div class="col-md-12">
              <div class="form-group">
                <label>Nama Team</label>
                <div class="input-group">
                  <input type="text" name="tiket_team_nama6" value="{{ old('tiket_team_nama6') }}" placeholder="Masukkan Nama Team" class="form-control">
                </div>
                @error('tiket_team_nama6')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Team Berangkat</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_team6" value="{{ old('tiket_team6') }}" placeholder="Total Uang Tiket Team Berangkat" class="form-control tiket_team6">
                </div>
                @error('tiket_team6')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Team Pulang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_team_pulang6" value="{{ old('tiket_team_pulang6') }}" placeholder="Total Uang Tiket Team Pulang" class="form-control tiket_team_pulang6">
                </div>
                @error('tiket_team_pulang6')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-1 col-12">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeTiketTeamBerangkat6" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div>
          </div>
          <!-- end -->

          <!-- 7 -->
          <div class="row TiketTeamBerangkat-field7" style="display: none;">

            <div class="col-md-12">
              <div class="form-group">
                <label>Nama Team</label>
                <div class="input-group">
                  <input type="text" name="tiket_team_nama7" value="{{ old('tiket_team_nama7') }}" placeholder="Masukkan Nama Team" class="form-control">
                </div>
                @error('tiket_team_nama7')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Team Berangkat</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_team7" value="{{ old('tiket_team7') }}" placeholder="Total Uang Tiket Team Berangkat" class="form-control tiket_team7">
                </div>
                @error('tiket_team7')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group">
                <label>Tiket Team Pulang</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Rp.</span>
                  </div>
                  <input type="text" name="tiket_team_pulang7" value="{{ old('tiket_team_pulang7') }}" placeholder="Total Uang Tiket Team Pulang" class="form-control tiket_team_pulang7">
                </div>
                @error('tiket_team_pulang7')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-1 col-12">
              <div class="form-group">
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger mt-2" id="removeTiketTeamBerangkat7" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-times"></i> HAPUS</button>
              </div>
            </div>
          </div>
          <!-- end -->
          <!--================== end ==================-->
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h4>TAMBAH LAINNYA</h4>
        </div>
        <div class="card-body">
          <!--================== LAINNYA ==================-->
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Status Pembayaran</label>
                <select class="form-control" name="status" required>
                  <option value="" disabled selected>Silahkan Pilih</option>
                  <option value="pending">PENDING</option>
                  <option value="terbayar">TERBAYAR</option>
                </select>
                @error('status')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <label>Catatan</label>
              <div class="card card-outline card-info">
                <div class="card-body pad">
                  <textarea class="textarea" name="note" id="note" placeholder="Masukan Catatan" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>
                </div>
              </div>
              @error('note')
              <div class="invalid-feedback" style="display: block">
                {{ $message }}
              </div>
              @enderror
            </div>
          </div>
          <!--================== END ==================-->

          <div class="d-flex">
            <button class="btn btn-primary btn-submit mr-1 rounded-pill" type="submit" style="width: 50%; font-size: 14px;">
              <i class="fa fa-paper-plane"></i> SIMPAN
            </button>
            <button class="btn btn-warning btn-reset rounded-pill" type="reset" style="width: 50%; font-size: 14px;">
              <i class="fa fa-redo"></i> RESET
            </button>
          </div>

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

<!--================== add dan remove field tiket trainer berangkat ==================-->
<script>
  $(document).ready(function() {

    var TiketTrainerBerangkatCounter = 0;

    $('#addTiketTrainerBerangkat').on('click', function() {
      if (TiketTrainerBerangkatCounter === 0) {
        $('.TiketTrainerBerangkat-field1').show();
        $('#removeTiketTrainerBerangkat1').show();
        $('#removeTiketTrainerBerangkat2').show();
        $('#removeTiketTrainerBerangkat3').show();
        $('#removeTiketTrainerBerangkat4').show();
        $('#removeTiketTrainerBerangkat5').show();
        $('#removeTiketTrainerBerangkat6').show();
        $('#removeTiketTrainerBerangkat7').show();
        $('#removeTiketTrainerBerangkat8').show();
        $('#removeTiketTrainerBerangkat9').show();
        $('#removeTiketTrainerBerangkat10').show();
      } else if (TiketTrainerBerangkatCounter === 1) {
        $('.TiketTrainerBerangkat-field2').show();
        $('#addTiketTrainerBerangkat').show();
        $('#removeTiketTrainerBerangkat2').show();
      } else if (TiketTrainerBerangkatCounter === 2) {
        $('.TiketTrainerBerangkat-field3').show();
        $('#addTiketTrainerBerangkat').show();
        $('#removeTiketTrainerBerangkat3').show();
      } else if (TiketTrainerBerangkatCounter === 3) {
        $('.TiketTrainerBerangkat-field4').show();
        $('#addTiketTrainerBerangkat').show();
        $('#removeTiketTrainerBerangkat4').show();
      } else if (TiketTrainerBerangkatCounter === 4) {
        $('.TiketTrainerBerangkat-field5').show();
        $('#addTiketTrainerBerangkat').show();
        $('#removeTiketTrainerBerangkat5').show();
      } else if (TiketTrainerBerangkatCounter === 5) {
        $('.TiketTrainerBerangkat-field6').show();
        $('#addTiketTrainerBerangkat').show();
        $('#removeTiketTrainerBerangkat6').show();
      } else if (TiketTrainerBerangkatCounter === 6) {
        $('.TiketTrainerBerangkat-field7').show();
        $('#addTiketTrainerBerangkat').hide();
        $('#removeTiketTrainerBerangkat7').show();
      } else if (TiketTrainerBerangkatCounter === 7) {
        $('.TiketTrainerBerangkat-field8').show();
        $('#addTiketTrainerBerangkat').show();
        $('#removeTiketTrainerBerangkat8').show();
      }
      TiketTrainerBerangkatCounter++;
    });

    // Remove additional team fields
    $('#removeTiketTrainerBerangkat1').on('click', function() {
      $('.TiketTrainerBerangkat-field1').hide();
      $('#addTiketTrainerBerangkat').show();
      TiketTrainerBerangkatCounter--;
      $('.currency_tiket_trainer').val('');
      $('[name="tiket_trainer"]').val('');
    });

    $('#removeTiketTrainerBerangkat2').on('click', function() {
      $('.TiketTrainerBerangkat-field2').hide();
      $('#addTiketTrainerBerangkat').show();
      TiketTrainerBerangkatCounter--;
      $('.currency_tiket_trainer1').val('');
      $('[name="tiket_trainer1"]').val('');
    });

    $('#removeTiketTrainerBerangkat3').on('click', function() {
      $('.TiketTrainerBerangkat-field3').hide();
      $('#addTiketTrainerBerangkat').show();
      TiketTrainerBerangkatCounter--;
      $('.currency_tiket_trainer2').val('');
      $('[name="tiket_trainer2"]').val('');
    });

    $('#removeTiketTrainerBerangkat4').on('click', function() {
      $('.TiketTrainerBerangkat-field4').hide();
      $('#addTiketTrainerBerangkat').show();
      TiketTrainerBerangkatCounter--;
      $('.currency_tiket_trainer3').val('');
      $('[name="tiket_trainer3"]').val('');
    });

    $('#removeTiketTrainerBerangkat5').on('click', function() {
      $('.TiketTrainerBerangkat-field5').hide();
      $('#addTiketTrainerBerangkat').show();
      TiketTrainerBerangkatCounter--;
      $('.currency_tiket_trainer4').val('');
      $('[name="tiket_trainer4"]').val('');
    });

    $('#removeTiketTrainerBerangkat6').on('click', function() {
      $('.TiketTrainerBerangkat-field6').hide();
      $('#addTiketTrainerBerangkat').show();
      TiketTrainerBerangkatCounter--;
      $('.currency_tiket_trainer5').val('');
      $('[name="tiket_trainer5"]').val('');
    });

    $('#removeTiketTrainerBerangkat7').on('click', function() {
      $('.TiketTrainerBerangkat-field7').hide();
      $('#addTiketTrainerBerangkat').show();
      TiketTrainerBerangkatCounter--;
      $('.currency_tiket_trainer6').val('');
      $('[name="tiket_trainer6"]').val('');
    });

    $('#removeTiketTrainerBerangkat8').on('click', function() {
      $('.TiketTrainerBerangkat-field8').hide();
      $('#addTiketTrainerBerangkat').show();
      TiketTrainerBerangkatCounter--;
      $('.currency_tiket_trainer7').val('');
      $('[name="tiket_trainer7"]').val('');
    });
  });
</script>
<!--================== end ==================-->

<!--================== format rupiah tiket trainer berangkat ==================-->
<script>
  var cleaveC = new Cleave('.tiket_trainer', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_trainer1', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_trainer2', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_trainer3', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_trainer4', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_trainer5', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_trainer6', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_trainer7', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
</script>
<!--================== end ==================-->

<!--================== add dan remove field tiket trainer pulang ==================-->
<script>
  $(document).ready(function() {

    var TiketTrainerPulangCounter = 0;

    $('#addTiketTrainerPulang').on('click', function() {
      if (TiketTrainerPulangCounter === 0) {
        $('.TiketTrainerPulang-field1').show();
        $('#removeTiketTrainerPulang1').show();
        $('#removeTiketTrainerPulang2').show();
        $('#removeTiketTrainerPulang3').show();
        $('#removeTiketTrainerPulang4').show();
        $('#removeTiketTrainerPulang5').show();
        $('#removeTiketTrainerPulang6').show();
        $('#removeTiketTrainerPulang7').show();
        $('#removeTiketTrainerPulang8').show();
        $('#removeTiketTrainerPulang9').show();
        $('#removeTiketTrainerPulang10').show();
      } else if (TiketTrainerPulangCounter === 1) {
        $('.TiketTrainerPulang-field2').show();
        $('#addTiketTrainerPulang').show();
        $('#removeTiketTrainerPulang2').show();
      } else if (TiketTrainerPulangCounter === 2) {
        $('.TiketTrainerPulang-field3').show();
        $('#addTiketTrainerPulang').show();
        $('#removeTiketTrainerPulang3').show();
      } else if (TiketTrainerPulangCounter === 3) {
        $('.TiketTrainerPulang-field4').show();
        $('#addTiketTrainerPulang').show();
        $('#removeTiketTrainerPulang4').show();
      } else if (TiketTrainerPulangCounter === 4) {
        $('.TiketTrainerPulang-field5').show();
        $('#addTiketTrainerPulang').show();
        $('#removeTiketTrainerPulang5').show();
      } else if (TiketTrainerPulangCounter === 5) {
        $('.TiketTrainerPulang-field6').show();
        $('#addTiketTrainerPulang').show();
        $('#removeTiketTrainerPulang6').show();
      } else if (TiketTrainerPulangCounter === 6) {
        $('.TiketTrainerPulang-field7').show();
        $('#addTiketTrainerPulang').hide();
        $('#removeTiketTrainerPulang7').show();
      } else if (TiketTrainerPulangCounter === 7) {
        $('.TiketTrainerPulang-field8').show();
        $('#addTiketTrainerPulang').show();
        $('#removeTiketTrainerPulang8').show();
      }
      TiketTrainerPulangCounter++;
    });

    // Remove additional team fields
    $('#removeTiketTrainerPulang1').on('click', function() {
      $('.TiketTrainerPulang-field1').hide();
      $('#addTiketTrainerPulang').show();
      TiketTrainerPulangCounter--;
      $('.currency_tiket_trainer').val('');
      $('[name="tiket_trainer"]').val('');
    });

    $('#removeTiketTrainerPulang2').on('click', function() {
      $('.TiketTrainerPulang-field2').hide();
      $('#addTiketTrainerPulang').show();
      TiketTrainerPulangCounter--;
      $('.currency_tiket_trainer1').val('');
      $('[name="tiket_trainer1"]').val('');
    });

    $('#removeTiketTrainerPulang3').on('click', function() {
      $('.TiketTrainerPulang-field3').hide();
      $('#addTiketTrainerPulang').show();
      TiketTrainerPulangCounter--;
      $('.currency_tiket_trainer2').val('');
      $('[name="tiket_trainer2"]').val('');
    });

    $('#removeTiketTrainerPulang4').on('click', function() {
      $('.TiketTrainerPulang-field4').hide();
      $('#addTiketTrainerPulang').show();
      TiketTrainerPulangCounter--;
      $('.currency_tiket_trainer3').val('');
      $('[name="tiket_trainer3"]').val('');
    });

    $('#removeTiketTrainerPulang5').on('click', function() {
      $('.TiketTrainerPulang-field5').hide();
      $('#addTiketTrainerPulang').show();
      TiketTrainerPulangCounter--;
      $('.currency_tiket_trainer4').val('');
      $('[name="tiket_trainer4"]').val('');
    });

    $('#removeTiketTrainerPulang6').on('click', function() {
      $('.TiketTrainerPulang-field6').hide();
      $('#addTiketTrainerPulang').show();
      TiketTrainerPulangCounter--;
      $('.currency_tiket_trainer5').val('');
      $('[name="tiket_trainer5"]').val('');
    });

    $('#removeTiketTrainerPulang7').on('click', function() {
      $('.TiketTrainerPulang-field7').hide();
      $('#addTiketTrainerPulang').show();
      TiketTrainerPulangCounter--;
      $('.currency_tiket_trainer6').val('');
      $('[name="tiket_trainer6"]').val('');
    });

    $('#removeTiketTrainerPulang8').on('click', function() {
      $('.TiketTrainerPulang-field8').hide();
      $('#addTiketTrainerPulang').show();
      TiketTrainerPulangCounter--;
      $('.currency_tiket_trainer7').val('');
      $('[name="tiket_trainer7"]').val('');
    });
  });
</script>
<!--================== end ==================-->

<!--================== format rupiah tiket trainer pulang ==================-->
<script>
  var cleaveC = new Cleave('.tiket_trainer_pulang', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_trainer_pulang1', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_trainer_pulang2', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_trainer_pulang3', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_trainer_pulang4', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_trainer_pulang5', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_trainer_pulang6', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_trainer_pulang7', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
</script>
<!--================== end ==================-->

<!--================== add dan remove field tiket team berangkat ==================-->
<script>
  $(document).ready(function() {

    var TiketTeamBerangkatCounter = 0;

    $('#addTiketTeamBerangkat').on('click', function() {
      if (TiketTeamBerangkatCounter === 0) {
        $('.TiketTeamBerangkat-field1').show();
        $('#removeTiketTeamBerangkat1').show();
        $('#removeTiketTeamBerangkat2').show();
        $('#removeTiketTeamBerangkat3').show();
        $('#removeTiketTeamBerangkat4').show();
        $('#removeTiketTeamBerangkat5').show();
        $('#removeTiketTeamBerangkat6').show();
        $('#removeTiketTeamBerangkat7').show();
        $('#removeTiketTeamBerangkat8').show();
        $('#removeTiketTeamBerangkat9').show();
        $('#removeTiketTeamBerangkat10').show();
      } else if (TiketTeamBerangkatCounter === 1) {
        $('.TiketTeamBerangkat-field2').show();
        $('#addTiketTeamBerangkat').show();
        $('#removeTiketTeamBerangkat2').show();
      } else if (TiketTeamBerangkatCounter === 2) {
        $('.TiketTeamBerangkat-field3').show();
        $('#addTiketTeamBerangkat').show();
        $('#removeTiketTeamBerangkat3').show();
      } else if (TiketTeamBerangkatCounter === 3) {
        $('.TiketTeamBerangkat-field4').show();
        $('#addTiketTeamBerangkat').show();
        $('#removeTiketTeamBerangkat4').show();
      } else if (TiketTeamBerangkatCounter === 4) {
        $('.TiketTeamBerangkat-field5').show();
        $('#addTiketTeamBerangkat').show();
        $('#removeTiketTeamBerangkat5').show();
      } else if (TiketTeamBerangkatCounter === 5) {
        $('.TiketTeamBerangkat-field6').show();
        $('#addTiketTeamBerangkat').show();
        $('#removeTiketTeamBerangkat6').show();
      } else if (TiketTeamBerangkatCounter === 6) {
        $('.TiketTeamBerangkat-field7').show();
        $('#addTiketTeamBerangkat').hide();
        $('#removeTiketTeamBerangkat7').show();
      } else if (TiketTeamBerangkatCounter === 7) {
        $('.TiketTeamBerangkat-field8').show();
        $('#addTiketTeamBerangkat').show();
        $('#removeTiketTeamBerangkat8').show();
      }
      TiketTeamBerangkatCounter++;
    });

    // Remove additional team fields
    $('#removeTiketTeamBerangkat1').on('click', function() {
      $('.TiketTeamBerangkat-field1').hide();
      $('#addTiketTeamBerangkat').show();
      TiketTeamBerangkatCounter--;
      $('.currency_tiket_trainer').val('');
      $('[name="tiket_trainer"]').val('');
    });

    $('#removeTiketTeamBerangkat2').on('click', function() {
      $('.TiketTeamBerangkat-field2').hide();
      $('#addTiketTeamBerangkat').show();
      TiketTeamBerangkatCounter--;
      $('.currency_tiket_trainer1').val('');
      $('[name="tiket_trainer1"]').val('');
    });

    $('#removeTiketTeamBerangkat3').on('click', function() {
      $('.TiketTeamBerangkat-field3').hide();
      $('#addTiketTeamBerangkat').show();
      TiketTeamBerangkatCounter--;
      $('.currency_tiket_trainer2').val('');
      $('[name="tiket_trainer2"]').val('');
    });

    $('#removeTiketTeamBerangkat4').on('click', function() {
      $('.TiketTeamBerangkat-field4').hide();
      $('#addTiketTeamBerangkat').show();
      TiketTeamBerangkatCounter--;
      $('.currency_tiket_trainer3').val('');
      $('[name="tiket_trainer3"]').val('');
    });

    $('#removeTiketTeamBerangkat5').on('click', function() {
      $('.TiketTeamBerangkat-field5').hide();
      $('#addTiketTeamBerangkat').show();
      TiketTeamBerangkatCounter--;
      $('.currency_tiket_trainer4').val('');
      $('[name="tiket_trainer4"]').val('');
    });

    $('#removeTiketTeamBerangkat6').on('click', function() {
      $('.TiketTeamBerangkat-field6').hide();
      $('#addTiketTeamBerangkat').show();
      TiketTeamBerangkatCounter--;
      $('.currency_tiket_trainer5').val('');
      $('[name="tiket_trainer5"]').val('');
    });

    $('#removeTiketTeamBerangkat7').on('click', function() {
      $('.TiketTeamBerangkat-field7').hide();
      $('#addTiketTeamBerangkat').show();
      TiketTeamBerangkatCounter--;
      $('.currency_tiket_trainer6').val('');
      $('[name="tiket_trainer6"]').val('');
    });

    $('#removeTiketTeamBerangkat8').on('click', function() {
      $('.TiketTeamBerangkat-field8').hide();
      $('#addTiketTeamBerangkat').show();
      TiketTeamBerangkatCounter--;
      $('.currency_tiket_trainer7').val('');
      $('[name="tiket_trainer7"]').val('');
    });
  });
</script>
<!--================== end ==================-->

<!--================== format rupiah tiket team berangkat ==================-->
<script>
  var cleaveC = new Cleave('.tiket_team', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_team1', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_team2', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_team3', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_team4', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_team5', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_team6', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_team7', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
</script>
<!--================== end ==================-->

<!--================== add dan remove field tiket team pulang ==================-->
<script>
  $(document).ready(function() {

    var TiketTeamPulangCounter = 0;

    $('#addTiketTeamPulang').on('click', function() {
      if (TiketTeamPulangCounter === 0) {
        $('.TiketTeamPulang-field1').show();
        $('#removeTiketTeamPulang1').show();
        $('#removeTiketTeamPulang2').show();
        $('#removeTiketTeamPulang3').show();
        $('#removeTiketTeamPulang4').show();
        $('#removeTiketTeamPulang5').show();
        $('#removeTiketTeamPulang6').show();
        $('#removeTiketTeamPulang7').show();
        $('#removeTiketTeamPulang8').show();
        $('#removeTiketTeamPulang9').show();
        $('#removeTiketTeamPulang10').show();
      } else if (TiketTeamPulangCounter === 1) {
        $('.TiketTeamPulang-field2').show();
        $('#addTiketTeamPulang').show();
        $('#removeTiketTeamPulang2').show();
      } else if (TiketTeamPulangCounter === 2) {
        $('.TiketTeamPulang-field3').show();
        $('#addTiketTeamPulang').show();
        $('#removeTiketTeamPulang3').show();
      } else if (TiketTeamPulangCounter === 3) {
        $('.TiketTeamPulang-field4').show();
        $('#addTiketTeamPulang').show();
        $('#removeTiketTeamPulang4').show();
      } else if (TiketTeamPulangCounter === 4) {
        $('.TiketTeamPulang-field5').show();
        $('#addTiketTeamPulang').show();
        $('#removeTiketTeamPulang5').show();
      } else if (TiketTeamPulangCounter === 5) {
        $('.TiketTeamPulang-field6').show();
        $('#addTiketTeamPulang').show();
        $('#removeTiketTeamPulang6').show();
      } else if (TiketTeamPulangCounter === 6) {
        $('.TiketTeamPulang-field7').show();
        $('#addTiketTeamPulang').hide();
        $('#removeTiketTeamPulang7').show();
      } else if (TiketTeamPulangCounter === 7) {
        $('.TiketTeamPulang-field8').show();
        $('#addTiketTeamPulang').show();
        $('#removeTiketTeamPulang8').show();
      }
      TiketTeamPulangCounter++;
    });

    // Remove additional team fields
    $('#removeTiketTeamPulang1').on('click', function() {
      $('.TiketTeamPulang-field1').hide();
      $('#addTiketTeamPulang').show();
      TiketTeamPulangCounter--;
      $('.currency_tiket_trainer').val('');
      $('[name="tiket_trainer"]').val('');
    });

    $('#removeTiketTeamPulang2').on('click', function() {
      $('.TiketTeamPulang-field2').hide();
      $('#addTiketTeamPulang').show();
      TiketTeamPulangCounter--;
      $('.currency_tiket_trainer1').val('');
      $('[name="tiket_trainer1"]').val('');
    });

    $('#removeTiketTeamPulang3').on('click', function() {
      $('.TiketTeamPulang-field3').hide();
      $('#addTiketTeamPulang').show();
      TiketTeamPulangCounter--;
      $('.currency_tiket_trainer2').val('');
      $('[name="tiket_trainer2"]').val('');
    });

    $('#removeTiketTeamPulang4').on('click', function() {
      $('.TiketTeamPulang-field4').hide();
      $('#addTiketTeamPulang').show();
      TiketTeamPulangCounter--;
      $('.currency_tiket_trainer3').val('');
      $('[name="tiket_trainer3"]').val('');
    });

    $('#removeTiketTeamPulang5').on('click', function() {
      $('.TiketTeamPulang-field5').hide();
      $('#addTiketTeamPulang').show();
      TiketTeamPulangCounter--;
      $('.currency_tiket_trainer4').val('');
      $('[name="tiket_trainer4"]').val('');
    });

    $('#removeTiketTeamPulang6').on('click', function() {
      $('.TiketTeamPulang-field6').hide();
      $('#addTiketTeamPulang').show();
      TiketTeamPulangCounter--;
      $('.currency_tiket_trainer5').val('');
      $('[name="tiket_trainer5"]').val('');
    });

    $('#removeTiketTeamPulang7').on('click', function() {
      $('.TiketTeamPulang-field7').hide();
      $('#addTiketTeamPulang').show();
      TiketTeamPulangCounter--;
      $('.currency_tiket_trainer6').val('');
      $('[name="tiket_trainer6"]').val('');
    });

    $('#removeTiketTeamPulang8').on('click', function() {
      $('.TiketTeamPulang-field8').hide();
      $('#addTiketTeamPulang').show();
      TiketTeamPulangCounter--;
      $('.currency_tiket_trainer7').val('');
      $('[name="tiket_trainer7"]').val('');
    });
  });
</script>
<!--================== end ==================-->

<!--================== format rupiah tiket team pulang ==================-->
<script>
  var cleaveC = new Cleave('.tiket_team_pulang', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_team_pulang1', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_team_pulang2', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_team_pulang3', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_team_pulang4', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_team_pulang5', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_team_pulang6', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });

  var cleaveC = new Cleave('.tiket_team_pulang7', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
</script>
<!--================== end ==================-->

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
  // end
</script>

<script>
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
  var timeoutHandler = null;
</script>

<script>
  // <!-- UANG MASUK -->
  var cleaveC = new Cleave('.currency', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- UANG MASUK LAINNYA -->
  var cleaveC = new Cleave('.currency1', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- HOTEL -->
  var cleaveC = new Cleave('.currency-hotel', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- KONSUMSI TAMBAHAN -->
  var cleaveC = new Cleave('.currency_konsumsi_tambahan', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- PENGELUARAN LAINNYA -->
  var cleaveC = new Cleave('.currency_lainnya', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

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