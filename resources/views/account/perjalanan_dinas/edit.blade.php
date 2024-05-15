@extends('layouts.account')

@section('title')
Update Perjalanan Dinas | MIS
@stop

<!--================== BUTTON ==================-->
<style>
  .button-container {
    display: flex;
    justify-content: space-between;
  }

  .button-container button {
    width: 32%;
    /* Adjust width to fit three buttons side by side */
    padding: 10px;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    align-items: center;
    justify-content: center;
  }
</style>
<!--================== END ==================-->

<!--================== CARD CUSTOM ==================-->
<style>
  .card-custom {
    border: 2px solid #007bff;
    /* Border lebih tebal dan berwarna biru */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    /* Shadow lebih besar */
    border-radius: 12px;
    /* Sudut yang lebih bulat */
    padding: 20px;
    background: linear-gradient(145deg, #ffffff, #e6e6e6);
    /* Gradient background */
    margin: 20px 0;
    transition: transform 0.3s;
    /* Transisi untuk efek hover */
  }

  .card-custom:hover {
    transform: translateY(-10px);
    /* Efek hover */
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
    /* Shadow lebih besar saat hover */
  }

  .card-custom .form-group {
    margin-bottom: 20px;
  }

  .card-custom .input-group-text {
    background-color: #f1f1f1;
  }

  .card-custom .form-control {
    border-radius: 4px;
  }

  .btn-custom {
    margin: 10px 0;
    border-radius: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }
</style>

<!--================== END ==================-->

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>UPDATE LAPORAN PERJALANAN DINAS</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <div class="card-header">
          <h4>UPDATE DETAIL PERJALANAN DINAS</h4>
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

          <form action="{{ route('account.PerjalananDinas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!--================== DETAIL PERJALANAN DINAS ==================-->
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Nama Bendahara</label>
                  <select class="form-control select2" name="user_id" id="karyawanSelect" style="width: 100%">
                    <option value="" disabled selected>-- PILIH NAMA BENDAHARA --</option>
                    @foreach ($datas as $user)
                    <option value="{{ $user->id }}" {{ $user->id == $perjalanandinas->user_id ? 'selected' : '' }}>{{ $user->full_name }}</option>
                    @endforeach
                  </select>

                  @error('user_id')
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
                  <label>Nama Camp</label>
                  <div class="input-group">
                    <input type="text" name="tempat" value="{{ $perjalanandinas->tempat }}" placeholder="Masukkan Nama Camp" class="form-control" style="text-transform:uppercase;">
                  </div>
                  @error('tempat')
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
                    <input type="number" name="camp" value="{{ $perjalanandinas->camp }}" placeholder="Masukkan Nomor Camp Ke" class="form-control">
                  </div>
                  @error('camp')
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
                  <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ $perjalanandinas->tanggal_mulai }}" placeholder="Masukkan Total Tunjangan" class="form-control">
                </div>
                @error('tanggal_mulai')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Tanggal Berakhir Camp</label>
                  <input type="date" name="tanggal_akhir" id="tanggal_akhir" value="{{ $perjalanandinas->tanggal_akhir }}" placeholder="Masukkan Total Tunjangan" class="form-control">
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
                  <label>Status</label>
                  <div class="input-group">
                    @if (Auth::user()->level == 'karyawan')
                    <select class="form-control" name="status">
                      <option value="" disabled selected>-- PILIH STATUS --</option>
                      <option value="ajukan" {{ $perjalanandinas->status == 'ajukan' ? 'selected' : '' }}>AJUKAN</option>
                    </select>
                    @else
                    <select class="form-control" name="status">
                      <option value="" disabled selected>-- PILIH STATUS --</option>
                      <option value="diterima" {{ $perjalanandinas->status == 'diterima' ? 'selected' : '' }}>DITERIMA</option>
                      <option value="ajukan" {{ $perjalanandinas->status == 'ajukan' ? 'selected' : '' }}>AJUKAN</option>
                      <option value="ditolak" {{ $perjalanandinas->status == 'ditolak' ? 'selected' : '' }}>DITOLAK</option>
                    </select>
                    @endif
                  </div>
                  @error('status')
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

      <!--================== CARD 1==================-->
      <div class="card">
        <div class="card-header d-flex justify-content-between">
          <h4>TAMBAH LAPORAN</h4>
          <div>
            <label class="mb-3"></label>
            <button type="button" class="btn btn-warning ml-auto d-flex justify-content-center align-items-center" id="addCard" style="height: 40px; white-space: nowrap;">
              <i class="fas fa-plus"></i>
              <span class="ml-2">CARD</span>
            </button>
          </div>
        </div>
        <div class="card-body">

          <div class="row">
            <div class="col-md-10">
              <div class="form-group">
                <label>Tanggal</label>
                <div class="input-group">
                  <input type="date" name="tanggal" id="tanggal" value="{{ $perjalanandinas->tanggal }}" placeholder="Masukkan Tanggal" class="form-control">
                </div>
                @error('tanggal')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-2">
              <label class="mb-3"></label>
              <button type="button" class="btn btn-info ml-auto d-flex justify-content-center align-items-center" id="addInput" style="height: 40px; white-space: nowrap;">
                <i class="fas fa-plus"></i>
                <span class="ml-2">INPUT</span>
              </button>
            </div>

          </div>

          <!--================== INPUT 1 ==================-->
          <div class="card card-custom">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Uang Masuk</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_masuk" value="{{ $perjalanandinas->uang_masuk }}" placeholder="Total Uang Masuk" class="form-control uang_masuk_currency">
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
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar" value="{{ $perjalanandinas->uang_keluar }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea name="keterangan" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 2 ==================-->
          <div class="card card-custom input-field2" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput2" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar2" value="{{ $perjalanandinas->uang_keluar2 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency2">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan2" value="{{ old('keterangan2') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan2 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar2" id="gambar2" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 3 ==================-->
          <div class="card card-custom input-field3" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput3" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar3" value="{{ $perjalanandinas->uang_keluar3 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency3">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan3" value="{{ old('keterangan3') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan3 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar3" id="gambar3" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 4 ==================-->
          <div class="card card-custom input-field4" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput4" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar4" value="{{ $perjalanandinas->uang_keluar4 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency4">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan4" value="{{ old('keterangan4') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan4 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar4" id="gambar4" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 5 ==================-->
          <div class="card card-custom input-field5" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput5" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar5" value="{{ $perjalanandinas->uang_keluar5 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency5">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan5" value="{{ old('keterangan5') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan5 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar5" id="gambar5" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 6 ==================-->
          <div class="card card-custom input-field6" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput6" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar6" value="{{ $perjalanandinas->uang_keluar6 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency6">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan6" value="{{ old('keterangan6') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan6 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar6" id="gambar6" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 7 ==================-->
          <div class="card card-custom input-field7" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput7" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar7" value="{{ $perjalanandinas->uang_keluar7 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency7">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan7" value="{{ old('keterangan7') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan7 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar7" id="gambar7" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 8 ==================-->
          <div class="card card-custom input-field8" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput8" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar8" value="{{ $perjalanandinas->uang_keluar8 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency8">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan8" value="{{ old('keterangan8') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan8 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar8" id="gambar8" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 9 ==================-->
          <div class="card card-custom input-field9" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput9" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar9" value="{{ $perjalanandinas->uang_keluar9 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency9">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan9" value="{{ old('keterangan9') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan9 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar9" id="gambar9" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 10 ==================-->
          <div class="card card-custom input-field10" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput10" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar10" value="{{ $perjalanandinas->uang_keluar10 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency10">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan10" value="{{ old('keterangan10') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan10 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar10" id="gambar10" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

        </div>
      </div>
      <!--================== END ==================-->

      <!--================== CARD 2 ==================-->
      <div class="card card-field2" style="display: none;">
        <div class="card-header d-flex justify-content-between">
          <h4>TAMBAH LAPORAN</h4>
          <div>
            <label class="mb-3"></label>
            <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedCard2" style="height: 40px; white-space: nowrap;">
              <i class="fas fa-trash"></i>
              <span class="ml-2">HAPUS</span>
            </button>
          </div>
        </div>
        <div class="card-body">

          <div class="row">
            <div class="col-md-10">
              <div class="form-group">
                <label>Tanggal</label>
                <div class="input-group">
                  <input type="date" name="tanggal11" id="tanggal11" value="{{ $perjalanandinas->tanggal11 }}" placeholder="Masukkan Tanggal" class="form-control">
                </div>
              </div>
            </div>

            <div class="col-md-2">
              <label class="mb-3"></label>
              <button type="button" class="btn btn-info ml-auto d-flex justify-content-center align-items-center" id="addInput2" style="height: 40px; white-space: nowrap;">
                <i class="fas fa-plus"></i>
                <span class="ml-2">INPUT</span>
              </button>
            </div>

          </div>

          <!--================== INPUT 11 ==================-->
          <div class="card card-custom">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Uang Masuk</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_masuk11" value="{{ $perjalanandinas->uang_masuk11 }}" placeholder="Total Uang Masuk" class="form-control uang_masuk_currency11">
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar11" value="{{ $perjalanandinas->uang_keluar11 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency11">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan11" value="{{ old('keterangan11') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan11 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar11" id="gambar11" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <!--================== END ==================-->

          <!--================== INPUT 12 ==================-->
          <div class="card card-custom input-field12" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput12" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar12" value="{{ $perjalanandinas->uang_keluar12 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency12">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan12" value="{{ old('keterangan12') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan12 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar12" id="gambar12" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 13 ==================-->
          <div class="card card-custom input-field13" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput13" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar13" value="{{ $perjalanandinas->uang_keluar13 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency13">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan13" value="{{ old('keterangan13') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan13 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar13" id="gambar13" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 14 ==================-->
          <div class="card card-custom input-field14" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput14" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar14" value="{{ $perjalanandinas->uang_keluar14 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency14">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan14" value="{{ old('keterangan14') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan14 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar14" id="gambar14" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 15 ==================-->
          <div class="card card-custom input-field15" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput15" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar15" value="{{ $perjalanandinas->uang_keluar15 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency15">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan15" value="{{ old('keterangan15') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan15 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar15" id="gambar15" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 16 ==================-->
          <div class="card card-custom input-field16" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput16" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar16" value="{{ $perjalanandinas->uang_keluar16 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency16">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan16" value="{{ old('keterangan16') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan16 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar16" id="gambar16" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 17 ==================-->
          <div class="card card-custom input-field17" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput17" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar17" value="{{ $perjalanandinas->uang_keluar17 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency17">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan17" value="{{ old('keterangan17') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan17 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar17" id="gambar17" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 18 ==================-->
          <div class="card card-custom input-field18" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput18" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar18" value="{{ $perjalanandinas->uang_keluar18 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency18">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan18" value="{{ old('keterangan18') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan18 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar18" id="gambar18" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 19 ==================-->
          <div class="card card-custom input-field19" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput19" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar19" value="{{ $perjalanandinas->uang_keluar19 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency19">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan19" value="{{ old('keterangan19') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan19 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar19" id="gambar19" class="form-control" accept="image/*">
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 20 ==================-->
          <div class="card card-custom input-field20" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput20" style="height: 40px; white-space: nowrap;">
                  <i class="fas fa-trash"></i>
                  <span class="ml-2">HAPUS</span>
                </button>
              </div>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Uang Keluar</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Rp.</span>
                      </div>
                      <input type="text" name="uang_keluar20" value="{{ $perjalanandinas->uang_keluar20 }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency20">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Keterangan</label>
                    <div class="input-group">
                      <textarea type="text" name="keterangan20" value="{{ old('keterangan20') }}" placeholder="Masukan Keterangan" class="form-control">{{ $perjalanandinas->keterangan20 }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar20" id="gambar20" class="form-control" accept="image/*">
                    </div>
                    @error('gambar20')
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

        </div>
      </div>
      <!--================== END ==================-->



















      <div class="button-container">
        <button class="btn btn-primary btn-submit" name="action" value="save" type="submit">
          <i class="fa fa-paper-plane"></i> SIMPAN
        </button>
        <button class="btn btn-addcreate" name="action" value="save_add" type="submit" style="background-color: #8FBC8F; color: white;">
          <i class="fa fa-plus"></i> SIMPAN & TAMBAH DATA
        </button>
        <button class="btn btn-warning btn-reset" type="reset">
          <i class="fa fa-redo"></i> RESET
        </button>
      </div>

      </form>

    </div>
</div>
</div>
</section>
</div>

<!--================== ADD DAN REMOVE FIELDS CARD ==================-->
<script>
  $(document).ready(function() {

    var cardCounter = 0;

    $('#addCard').on('click', function() {
      if (cardCounter === 0) {
        $('.card-field2').show();
        $('#addCard').hide();
        $('#removeAddedCard2').show();
      }

      cardCounter++;
    });

    // Remove additional card fields
    $('#removeAddedCard2').on('click', function() {
      $('.card-field2').hide();
      $('#addCard').show();
      cardCounter--;
    });
  });
</script>
<!--================== END ==================-->

<!--================== ADD DAN REMOVE FIELDS INPUT 1 ==================-->
<script>
  $(document).ready(function() {

    var inputCounter = 0;

    $('#addInput').on('click', function() {
      if (inputCounter === 0) {
        $('.input-field2').show();
        $('#removeAddedInput2').show();
        $('#removeAddedInput3').show();
        $('#removeAddedInput4').show();
        $('#removeAddedInput5').show();
        $('#removeAddedInput6').show();
        $('#removeAddedInput7').show();
        $('#removeAddedInput8').show();
        $('#removeAddedInput9').show();
        $('#removeAddedInput10').show();
      } else if (inputCounter === 1) {
        $('.input-field3').show();
        $('#addInput').show();
        $('#removeAddedInput3').show();
      } else if (inputCounter === 2) {
        $('.input-field4').show();
        $('#addInput').show();
        $('#removeAddedInput4').show();
      } else if (inputCounter === 3) {
        $('.input-field5').show();
        $('#addInput').show();
        $('#removeAddedInput5').show();
      } else if (inputCounter === 4) {
        $('.input-field6').show();
        $('#addInput').show();
        $('#removeAddedInput6').show();
      } else if (inputCounter === 5) {
        $('.input-field7').show();
        $('#addInput').show();
        $('#removeAddedInput7').show();
      } else if (inputCounter === 6) {
        $('.input-field8').show();
        $('#addInput').show();
        $('#removeAddedInput8').show();
      } else if (inputCounter === 7) {
        $('.input-field9').show();
        $('#addInput').show();
        $('#removeAddedInput9').show();
      } else if (inputCounter === 8) {
        $('.input-field10').show();
        $('#addInput').hide();
        $('#removeAddedInput10').show();
      }

      inputCounter++;
    });

    // Remove additional input fields
    $('#removeAddedInput2').on('click', function() {
      $('.input-field2').hide();
      $('#addInput').show();
      inputCounter--;
    });
    $('#removeAddedInput3').on('click', function() {
      $('.input-field3').hide();
      $('#addInput').show();
      inputCounter--;
    });
    $('#removeAddedInput4').on('click', function() {
      $('.input-field4').hide();
      $('#addInput').show();
      inputCounter--;
    });
    $('#removeAddedInput5').on('click', function() {
      $('.input-field5').hide();
      $('#addInput').show();
      inputCounter--;
    });
    $('#removeAddedInput6').on('click', function() {
      $('.input-field6').hide();
      $('#addInput').show();
      inputCounter--;
    });
    $('#removeAddedInput7').on('click', function() {
      $('.input-field7').hide();
      $('#addInput').show();
      inputCounter--;
    });
    $('#removeAddedInput8').on('click', function() {
      $('.input-field8').hide();
      $('#addInput').show();
      inputCounter--;
    });
    $('#removeAddedInput9').on('click', function() {
      $('.input-field9').hide();
      $('#addInput').show();
      inputCounter--;
    });
    $('#removeAddedInput10').on('click', function() {
      $('.input-field10').hide();
      $('#addInput').show();
      inputCounter--;
    });

  });
</script>
<!--================== END ==================-->

<!--================== ADD DAN REMOVE FIELDS INPUT 2 ==================-->
<script>
  $(document).ready(function() {

    var inputCounter = 0;

    $('#addInput2').on('click', function() {
      if (inputCounter === 0) {
        $('#removeAddedInput12').show();
        $('#removeAddedInput13').show();
        $('#removeAddedInput14').show();
        $('#removeAddedInput15').show();
        $('#removeAddedInput16').show();
        $('#removeAddedInput17').show();
        $('#removeAddedInput18').show();
        $('#removeAddedInput19').show();
        $('#removeAddedInput20').show();
      } else if (inputCounter === 1) {
        $('.input-field12').show();
        $('#addInput2').show();
        $('#removeAddedInput12').show();
      } else if (inputCounter === 2) {
        $('.input-field13').show();
        $('#addInput2').show();
        $('#removeAddedInput13').show();
      } else if (inputCounter === 3) {
        $('.input-field14').show();
        $('#addInput2').show();
        $('#removeAddedInput14').show();
      } else if (inputCounter === 4) {
        $('.input-field15').show();
        $('#addInput2').show();
        $('#removeAddedInput15').show();
      } else if (inputCounter === 5) {
        $('.input-field16').show();
        $('#addInput2').show();
        $('#removeAddedInput16').show();
      } else if (inputCounter === 6) {
        $('.input-field17').show();
        $('#addInput2').show();
        $('#removeAddedInput17').show();
      } else if (inputCounter === 7) {
        $('.input-field18').show();
        $('#addInput2').show();
        $('#removeAddedInput18').show();
      } else if (inputCounter === 8) {
        $('.input-field19').show();
        $('#addInput2').show();
        $('#removeAddedInput19').show();
      } else if (inputCounter === 9) {
        $('.input-field20').show();
        $('#addInput2').hide();
        $('#removeAddedInput20').show();
      }

      inputCounter++;
    });

    // Remove additional input fields
    $('#removeAddedInput12').on('click', function() {
      $('.input-field12').hide();
      $('#addInput2').show();
      inputCounter--;
    });
    $('#removeAddedInput13').on('click', function() {
      $('.input-field13').hide();
      $('#addInput2').show();
      inputCounter--;
    });
    $('#removeAddedInput14').on('click', function() {
      $('.input-field14').hide();
      $('#addInput2').show();
      inputCounter--;
    });
    $('#removeAddedInput15').on('click', function() {
      $('.input-field15').hide();
      $('#addInput2').show();
      inputCounter--;
    });
    $('#removeAddedInput16').on('click', function() {
      $('.input-field16').hide();
      $('#addInput2').show();
      inputCounter--;
    });
    $('#removeAddedInput17').on('click', function() {
      $('.input-field17').hide();
      $('#addInput2').show();
      inputCounter--;
    });
    $('#removeAddedInput18').on('click', function() {
      $('.input-field18').hide();
      $('#addInput2').show();
      inputCounter--;
    });
    $('#removeAddedInput19').on('click', function() {
      $('.input-field19').hide();
      $('#addInput2').show();
      inputCounter--;
    });
    $('#removeAddedInput20').on('click', function() {
      $('.input-field20').hide();
      $('#addInput2').show();
      inputCounter--;
    });

  });
</script>
<!--================== END ==================-->

<!--================== FORMAT RUPIAH ==================-->
<script>
  // <!-- INPUT 1 -->
  var cleaveC = new Cleave('.uang_masuk_currency', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.uang_keluar_currency', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 2 -->
  var cleaveC = new Cleave('.uang_keluar_currency2', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 3 -->
  var cleaveC = new Cleave('.uang_keluar_currency3', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 4 -->
  var cleaveC = new Cleave('.uang_keluar_currency4', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->


  // <!-- INPUT 5 -->
  var cleaveC = new Cleave('.uang_keluar_currency5', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 6 -->
  var cleaveC = new Cleave('.uang_keluar_currency6', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 7 -->
  var cleaveC = new Cleave('.uang_keluar_currency7', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 8 -->
  var cleaveC = new Cleave('.uang_keluar_currency8', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 9 -->
  var cleaveC = new Cleave('.uang_keluar_currency9', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 10 -->
  var cleaveC = new Cleave('.uang_keluar_currency10', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 11 -->
  var cleaveC = new Cleave('.uang_masuk_currency11', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.uang_keluar_currency11', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 12 -->
  var cleaveC = new Cleave('.uang_keluar_currency12', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 13 -->
  var cleaveC = new Cleave('.uang_keluar_currency13', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 14 -->
  var cleaveC = new Cleave('.uang_keluar_currency14', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 15 -->
  var cleaveC = new Cleave('.uang_keluar_currency15', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 16 -->
  var cleaveC = new Cleave('.uang_keluar_currency16', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 17 -->
  var cleaveC = new Cleave('.uang_keluar_currency17', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 18 -->
  var cleaveC = new Cleave('.uang_keluar_currency18', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 19 -->
  var cleaveC = new Cleave('.uang_keluar_currency19', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 20 -->
  var cleaveC = new Cleave('.uang_keluar_currency20', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->
</script>
<!--================== end ==================-->

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
  // <!-- BUTTON SUBMIT LOADER -->
  $(".btn-submit").click(function() {
    $(".btn-submit").addClass('btn-progress');
    if (timeoutHandler) clearTimeout(timeoutHandler);

    timeoutHandler = setTimeout(function() {
      $(".btn-submit").removeClass('btn-progress');

    }, 1000);
  });
  // <!-- END -->

  // <!-- BUTTON SUBMIT & TAMBAH DATA LOADER -->
  $(".btn-addcreate").click(function() {
    $(".btn-addcreate").addClass('btn-progress');
    if (timeoutHandler) clearTimeout(timeoutHandler);

    timeoutHandler = setTimeout(function() {
      $(".btn-addcreate").removeClass('btn-progress');

    }, 1000);
  });
  // <!-- END -->

  // <!-- BUTTON RESET LOADER -->
  $(".btn-reset").click(function() {
    $(".btn-reset").addClass('btn-progress');
    if (timeoutHandler) clearTimeout(timeoutHandler);

    timeoutHandler = setTimeout(function() {
      $(".btn-reset").removeClass('btn-progress');
      $("#karyawanSelect").val('');
    }, 500);
  })
</script>
// <!-- END -->

@stop