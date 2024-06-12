@extends('layouts.account')

@section('title')
Tambah laporan Camp | MIS
@stop
<!--================== BUTTON ==================-->
<style>
  .button-container {
    display: flex;
    justify-content: space-between;
  }

  .button-container button {
    width: 49%;
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

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>TAMBAH LAPORAN PERJALANAN DINAS</h1>
    </div>

    <form action="{{ route('account.PerjalananDinas.addstore', $perjalanandinas->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label hidden>Total Uang Masuk</label>
            <div class="input-group">
              <input type="text" name="totalmasukpage1" value="{{ $perjalanandinas->total_uang_masuk }}" hidden>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label hidden>Total Uang Keluar</label>
            <div class="input-group">
              <input type="text" name="totalkeluarpage1" value="{{ $perjalanandinas->total_uang_keluar }}" hidden>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label hidden>Sisa Saldo</label>
            <div class="input-group">
              <input type="text" name="sisasaldopage1" value="{{ $perjalanandinas->sisa_saldo }}" hidden>
            </div>
          </div>
        </div>
      </div>
      <!--================== CARD 3 ==================-->
      <div class="card">
        <div class="card-header d-flex justify-content-between">
          <h4>TAMBAH LAPORAN HARI KETIGA</h4>
          <div>
            <label class="mb-3"></label>
            <button type="button" class="btn btn-warning ml-auto d-flex justify-content-center align-items-center" id="addCard3" style="height: 40px; white-space: nowrap;">
              <i class="fas fa-plus"></i>
              <span class="ml-2">HARI</span>
            </button>
          </div>
        </div>
        <div class="card-body">

          <div class="row">
            <div class="col-md-10">
              <div class="form-group">
                <label>Tanggal</label>
                <div class="input-group">
                  <input type="date" name="tanggal21" id="tanggal21" value="{{ old('tanggal21') }}" placeholder="Masukkan Tanggal" class="form-control">
                </div>
                @error('tanggal21')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-2">
              <label class="mb-3"></label>
              <button type="button" class="btn btn-info ml-auto d-flex justify-content-center align-items-center" id="addInput21" style="height: 40px; white-space: nowrap;">
                <i class="fas fa-plus"></i>
                <span class="ml-2">INPUT</span>
              </button>
            </div>
          </div>

          <!--================== INPUT 21 ==================-->
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
                      <input type="text" name="uang_masuk21" value="{{ old('uang_masuk21') }}" placeholder="Total Uang Masuk" class="form-control uang_masuk_currency21">
                    </div>
                    @error('uang_masuk21')
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
                      <input type="text" name="uang_keluar21" value="{{ old('uang_keluar21') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency21">
                    </div>
                    @error('uang_keluar21')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan21">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan21')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar21" id="gambar21" class="inputfile" accept="image/*">
                      <label for="gambar21" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview21" class="image-preview"></div>
                    <span id="file-selected21"></span>
                  </div>
                  @error('gambar21')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 22 ==================-->
          <div class="card card-custom input-field22" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput22" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar22" value="{{ old('uang_keluar22') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency22">
                    </div>
                    @error('uang_keluar22')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan22">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan22')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar22" id="gambar22" class="inputfile" accept="image/*">
                      <label for="gambar22" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview22" class="image-preview"></div>
                    <span id="file-selected22"></span>
                  </div>
                  @error('gambar22')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 23 ==================-->
          <div class="card card-custom input-field23" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput23" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar23" value="{{ old('uang_keluar23') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency23">
                    </div>
                    @error('uang_keluar23')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan23">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan23')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar23" id="gambar23" class="inputfile" accept="image/*">
                      <label for="gambar23" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview23" class="image-preview"></div>
                    <span id="file-selected23"></span>
                  </div>
                  @error('gambar23')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 24 ==================-->
          <div class="card card-custom input-field24" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput24" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar24" value="{{ old('uang_keluar24') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency24">
                    </div>
                    @error('uang_keluar24')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan24">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan24')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar24" id="gambar24" class="inputfile" accept="image/*">
                      <label for="gambar24" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview24" class="image-preview"></div>
                    <span id="file-selected24"></span>
                  </div>
                  @error('gambar24')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 25 ==================-->
          <div class="card card-custom input-field25" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput25" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar25" value="{{ old('uang_keluar25') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency25">
                    </div>
                    @error('uang_keluar25')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan25">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan25')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar25" id="gambar25" class="inputfile" accept="image/*">
                      <label for="gambar25" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview25" class="image-preview"></div>
                    <span id="file-selected25"></span>
                  </div>
                  @error('gambar25')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 26 ==================-->
          <div class="card card-custom input-field26" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput26" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar26" value="{{ old('uang_keluar26') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency26">
                    </div>
                    @error('uang_keluar26')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan26">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan26')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar26" id="gambar26" class="inputfile" accept="image/*">
                      <label for="gambar26" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview26" class="image-preview"></div>
                    <span id="file-selected26"></span>
                  </div>
                  @error('gambar26')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 27 ==================-->
          <div class="card card-custom input-field27" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput27" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar27" value="{{ old('uang_keluar27') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency27">
                    </div>
                    @error('uang_keluar27')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan27">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan27')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar27" id="gambar27" class="inputfile" accept="image/*">
                      <label for="gambar27" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview27" class="image-preview"></div>
                    <span id="file-selected27"></span>
                  </div>
                  @error('gambar27')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 28 ==================-->
          <div class="card card-custom input-field28" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput28" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar28" value="{{ old('uang_keluar28') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency28">
                    </div>
                    @error('uang_keluar28')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan28">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan28')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar28" id="gambar28" class="inputfile" accept="image/*">
                      <label for="gambar28" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview28" class="image-preview"></div>
                    <span id="file-selected28"></span>
                  </div>
                  @error('gambar28')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 29 ==================-->
          <div class="card card-custom input-field29" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput29" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar29" value="{{ old('uang_keluar29') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency29">
                    </div>
                    @error('uang_keluar29')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan29">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan29')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar29" id="gambar29" class="inputfile" accept="image/*">
                      <label for="gambar29" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview29" class="image-preview"></div>
                    <span id="file-selected29"></span>
                  </div>
                  @error('gambar29')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 30 ==================-->
          <div class="card card-custom input-field30" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput30" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar30" value="{{ old('uang_keluar30') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency30">
                    </div>
                    @error('uang_keluar30')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan30">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan30')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar30" id="gambar30" class="inputfile" accept="image/*">
                      <label for="gambar30" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview30" class="image-preview"></div>
                    <span id="file-selected30"></span>
                  </div>
                  @error('gambar30')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

        </div>
      </div>
      <!--================== END ==================-->


      <!--================== CARD 4 ==================-->
      <div class="card card-field4" style="display: none;">
        <div class="card-header d-flex justify-content-between">
          <h4>TAMBAH LAPORAN HARI KEEMPAT</h4>
          <div>
            <label class="mb-3"></label>
            <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedCard4" style="height: 40px; white-space: nowrap;">
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
                  <input type="date" name="tanggal31" id="tanggal31" value="{{ old('tanggal31') }}" placeholder="Masukkan Tanggal" class="form-control">
                </div>
                @error('tanggal31')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-2">
              <label class="mb-3"></label>
              <button type="button" class="btn btn-info ml-auto d-flex justify-content-center align-items-center" id="addInput31" style="height: 40px; white-space: nowrap;">
                <i class="fas fa-plus"></i>
                <span class="ml-2">INPUT</span>
              </button>
            </div>

          </div>

          <!--================== INPUT 31 ==================-->
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
                      <input type="text" name="uang_masuk31" value="{{ old('uang_masuk31') }}" placeholder="Total Uang Masuk" class="form-control uang_masuk_currency31">
                    </div>
                    @error('uang_masuk31')
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
                      <input type="text" name="uang_keluar31" value="{{ old('uang_keluar31') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency31">
                    </div>
                    @error('uang_keluar31')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan31">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan31')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar31" id="gambar31" class="inputfile" accept="image/*">
                      <label for="gambar31" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview31" class="image-preview"></div>
                    <span id="file-selected31"></span>
                  </div>
                  @error('gambar31')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 32 ==================-->
          <div class="card card-custom input-field32" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput32" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar32" value="{{ old('uang_keluar32') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency32">
                    </div>
                    @error('uang_keluar32')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan32">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan32')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar32" id="gambar32" class="inputfile" accept="image/*">
                      <label for="gambar32" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview32" class="image-preview"></div>
                    <span id="file-selected32"></span>
                  </div>
                  @error('gambar32')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 33 ==================-->
          <div class="card card-custom input-field33" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput33" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar33" value="{{ old('uang_keluar33') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency33">
                    </div>
                    @error('uang_keluar33')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan33">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan33')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar33" id="gambar33" class="inputfile" accept="image/*">
                      <label for="gambar33" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview33" class="image-preview"></div>
                    <span id="file-selected33"></span>
                  </div>
                  @error('gambar33')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 34 ==================-->
          <div class="card card-custom input-field34" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput34" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar34" value="{{ old('uang_keluar34') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency34">
                    </div>
                    @error('uang_keluar34')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan34">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan34')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar34" id="gambar34" class="inputfile" accept="image/*">
                      <label for="gambar34" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview34" class="image-preview"></div>
                    <span id="file-selected34"></span>
                  </div>
                  @error('gambar34')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 35 ==================-->
          <div class="card card-custom input-field35" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput35" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar35" value="{{ old('uang_keluar35') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency35">
                    </div>
                    @error('uang_keluar35')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan35">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan35')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar35" id="gambar35" class="inputfile" accept="image/*">
                      <label for="gambar35" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview35" class="image-preview"></div>
                    <span id="file-selected35"></span>
                  </div>
                  @error('gambar35')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>

        </div>
      </div>
      <!--================== END ==================-->


      <!--================== CARD 5 ==================-->
      <div class="card card-field5" style="display: none;">
        <div class="card-header d-flex justify-content-between">
          <h4>TAMBAH LAPORAN HARI KELIMA</h4>
          <div>
            <label class="mb-3"></label>
            <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedCard5" style="height: 40px; white-space: nowrap;">
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
                  <input type="date" name="tanggal36" id="tanggal36" value="{{ old('tanggal36') }}" placeholder="Masukkan Tanggal" class="form-control">
                </div>
                @error('tanggal36')
                <div class="invalid-feedback" style="display: block">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>

            <div class="col-md-2">
              <label class="mb-3"></label>
              <button type="button" class="btn btn-info ml-auto d-flex justify-content-center align-items-center" id="addInput36" style="height: 40px; white-space: nowrap;">
                <i class="fas fa-plus"></i>
                <span class="ml-2">INPUT</span>
              </button>
            </div>

          </div>
          <!--================== INPUT 36 ==================-->
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
                      <input type="text" name="uang_masuk36" value="{{ old('uang_masuk36') }}" placeholder="Total Uang Masuk" class="form-control uang_masuk_currency36">
                    </div>
                    @error('uang_masuk36')
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
                      <input type="text" name="uang_keluar36" value="{{ old('uang_keluar36') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency36">
                    </div>
                    @error('uang_keluar36')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan36">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan36')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar36" id="gambar36" class="inputfile" accept="image/*">
                      <label for="gambar36" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview36" class="image-preview"></div>
                    <span id="file-selected36"></span>
                  </div>
                  @error('gambar36')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 37 ==================-->
          <div class="card card-custom input-field37" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput37" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar37" value="{{ old('uang_keluar37') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency37">
                    </div>
                    @error('uang_keluar37')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan37">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan37')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar37" id="gambar37" class="inputfile" accept="image/*">
                      <label for="gambar37" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview37" class="image-preview"></div>
                    <span id="file-selected37"></span>
                  </div>
                  @error('gambar37')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 38 ==================-->
          <div class="card card-custom input-field38" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput38" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar38" value="{{ old('uang_keluar38') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency38">
                    </div>
                    @error('uang_keluar38')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan38">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan38')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar38" id="gambar38" class="inputfile" accept="image/*">
                      <label for="gambar38" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview38" class="image-preview"></div>
                    <span id="file-selected38"></span>
                  </div>
                  @error('gambar38')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== INPUT 39 ==================-->
          <div class="card card-custom input-field39" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput39" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar39" value="{{ old('uang_keluar39') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency39">
                    </div>
                    @error('uang_keluar39')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan39">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan39')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar39" id="gambar39" class="inputfile" accept="image/*">
                      <label for="gambar39" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview39" class="image-preview"></div>
                    <span id="file-selected39"></span>
                  </div>
                  @error('gambar39')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->


          <!--================== INPUT 40 ==================-->
          <div class="card card-custom input-field40" style="display: none;">
            <div class="card-header d-flex justify-content-between">
              <h4>TAMBAH INPUTAN</h4>
              <div>
                <label class="mb-3"></label>
                <button type="button" class="btn btn-danger d-flex align-items-center" id="removeAddedInput40" style="height: 40px; white-space: nowrap;">
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
                      <input type="text" name="uang_keluar40" value="{{ old('uang_keluar40') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency40">
                    </div>
                    @error('uang_keluar40')
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
                    <label>Kategori</label>
                    <div class="input-group">
                      <select class="form-control" name="keterangan40">
                        <option value="" disabled selected>-- PILIH KATEGORI --</option>
                        <option value="transportasi">TRANSPORTASI</option>
                        <option value="makan">MAKAN</option>
                        <option value="obat-obatan">OBAT-OBATAN</option>
                        <option value="jajan atau belanja">JAJAN ATAU BELANJA</option>
                        <option value="lain-lain">LAIN-LAIN</option>
                      </select>
                    </div>
                    @error('keterangan40')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group custom-file-upload" style="margin-top: -3px;">
                    <label>Bukti Struk</label>
                    <div class="input-group">
                      <input type="file" name="gambar40" id="gambar40" class="inputfile" accept="image/*">
                      <label for="gambar40" class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Image
                      </label>
                    </div>
                  </div>
                  <div class="image-preview-container">
                    <div id="imagePreview40" class="image-preview"></div>
                    <span id="file-selected40"></span>
                  </div>
                  @error('gambar40')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>
            </div>
          </div>
          <!--================== END ==================-->

        </div>
      </div>
      <!--================== END ==================-->

      <div class="button-container">
        <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> SIMPAN</button>
        <button class="btn btn-warning btn-reset" type="reset"><i class="fa fa-redo"></i> RESET</button>
      </div>

    </form>

</div>
</section>
</div>

<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  for (var i = 21; i <= 40; i++) {
    var fileInput = document.getElementById('gambar' + i);
    if (fileInput) {
      (function(i) { // Capture the value of i in a closure
        fileInput.addEventListener('change', function(event) {
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
            return;
          }

          if (fileSize > 3000) {
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: 'File size exceeds the maximum limit of 3MB. Please choose a smaller file.'
            });
            return;
          }

          document.getElementById('file-selected' + i).innerHTML = fileName + ' (' + fileSize + ' KB)';

          var reader = new FileReader();
          reader.onload = function() {
            var output = document.getElementById('imagePreview' + i);
            output.innerHTML = `<img src="${reader.result}">`;
            output.style.display = 'block';
          };
          reader.readAsDataURL(file);
        });
      })(i); // Pass the current value of i to the closure
    }
  }
</script>
<!--================== END ==================-->

<!--================== ADD DAN REMOVE FIELDS CARD ==================-->
<script>
  $(document).ready(function() {

    var cardCounter = 0;

    $('#addCard3').on('click', function() {
      if (cardCounter === 0) {
        $('.card-field4').show();
        $('#removeAddedCard4').show();
      } else if (cardCounter === 1) {
        $('.card-field5').show();
        $('#removeAddedCard5').show();
        $('#addCard3').hide(); // Hide add button after showing the second card field
      }
      cardCounter++;
    });

    // Remove additional card fields
    $('#removeAddedCard4').on('click', function() {
      $('.card-field4').hide();
      $('#removeAddedCard4').hide();
      if (cardCounter > 1) {
        $('#addCard3').show();
      }
      cardCounter--;
    });

    $('#removeAddedCard5').on('click', function() {
      $('.card-field5').hide();
      $('#removeAddedCard5').hide();
      $('#addCard3').show(); // Show add button when the second card field is removed
      cardCounter--;
    });

  });
</script>

<!--================== END ==================-->

<!--================== ADD DAN REMOVE FIELDS INPUT 3 ==================-->
<script>
  $(document).ready(function() {

    var inputCounter = 0;

    $('#addInput21').on('click', function() {
      if (inputCounter === 0) {
        $('.input-field22').show();
        $('#removeAddedInput22').show();
        $('#removeAddedInput23').show();
        $('#removeAddedInput24').show();
        $('#removeAddedInput25').show();
        $('#removeAddedInput26').show();
        $('#removeAddedInput27').show();
        $('#removeAddedInput28').show();
        $('#removeAddedInput29').show();
        $('#removeAddedInput30').show();
      } else if (inputCounter === 1) {
        $('.input-field22').show();
        $('#addInput21').show();
        $('#removeAddedInput22').show();
      } else if (inputCounter === 2) {
        $('.input-field23').show();
        $('#addInput21').show();
        $('#removeAddedInput23').show();
      } else if (inputCounter === 3) {
        $('.input-field24').show();
        $('#addInput21').show();
        $('#removeAddedInput24').show();
      } else if (inputCounter === 4) {
        $('.input-field25').show();
        $('#addInput21').show();
        $('#removeAddedInput25').show();
      } else if (inputCounter === 5) {
        $('.input-field26').show();
        $('#addInput21').show();
        $('#removeAddedInput26').show();
      } else if (inputCounter === 6) {
        $('.input-field27').show();
        $('#addInput21').show();
        $('#removeAddedInput27').show();
      } else if (inputCounter === 7) {
        $('.input-field28').show();
        $('#addInput21').show();
        $('#removeAddedInput28').show();
      } else if (inputCounter === 8) {
        $('.input-field29').show();
        $('#addInput21').show();
        $('#removeAddedInput29').show();
      } else if (inputCounter === 9) {
        $('.input-field30').show();
        $('#addInput21').show();
        $('#removeAddedInput30').show();
      }

      inputCounter++;
    });

    // Remove additional input fields
    $('#removeAddedInput22').on('click', function() {
      $('.input-field22').hide();
      $('#addInput21').show();
      inputCounter--;
    });
    $('#removeAddedInput23').on('click', function() {
      $('.input-field23').hide();
      $('#addInput21').show();
      inputCounter--;
    });
    $('#removeAddedInput24').on('click', function() {
      $('.input-field24').hide();
      $('#addInput21').show();
      inputCounter--;
    });
    $('#removeAddedInput25').on('click', function() {
      $('.input-field25').hide();
      $('#addInput21').show();
      inputCounter--;
    });
    $('#removeAddedInput26').on('click', function() {
      $('.input-field26').hide();
      $('#addInput21').show();
      inputCounter--;
    });
    $('#removeAddedInput27').on('click', function() {
      $('.input-field27').hide();
      $('#addInput21').show();
      inputCounter--;
    });
    $('#removeAddedInput28').on('click', function() {
      $('.input-field28').hide();
      $('#addInput21').show();
      inputCounter--;
    });
    $('#removeAddedInput29').on('click', function() {
      $('.input-field29').hide();
      $('#addInput21').show();
      inputCounter--;
    });
    $('#removeAddedInput30').on('click', function() {
      $('.input-field30').hide();
      $('#addInput21').show();
      inputCounter--;
    });

  });
</script>
<!--================== END ==================-->

<!--================== ADD DAN REMOVE FIELDS INPUT 4 ==================-->
<script>
  $(document).ready(function() {

    var inputCounter4 = 0;

    $('#addInput31').on('click', function() {
      if (inputCounter4 === 0) {
        $('.input-field32').show();
        $('#removeAddedInput32').show();
      } else if (inputCounter4 === 1) {
        $('.input-field33').show();
        $('#removeAddedInput33').show();
      } else if (inputCounter4 === 2) {
        $('.input-field34').show();
        $('#removeAddedInput34').show();
      } else if (inputCounter4 === 3) {
        $('.input-field35').show();
        $('#removeAddedInput35').show();
      }

      inputCounter4++;
      if (inputCounter4 >= 4) {
        $('#addInput31').hide();
      }
    });

    // Remove additional input fields
    $('#removeAddedInput32').on('click', function() {
      $('.input-field32').hide();
      $('#removeAddedInput32').hide();
      inputCounter4--;
      $('#addInput31').show();
    });
    $('#removeAddedInput33').on('click', function() {
      $('.input-field33').hide();
      $('#removeAddedInput33').hide();
      inputCounter4--;
      $('#addInput31').show();
    });
    $('#removeAddedInput34').on('click', function() {
      $('.input-field34').hide();
      $('#removeAddedInput34').hide();
      inputCounter4--;
      $('#addInput31').show();
    });
    $('#removeAddedInput35').on('click', function() {
      $('.input-field35').hide();
      $('#removeAddedInput35').hide();
      inputCounter4--;
      $('#addInput31').show();
    });

  });
</script>
<!--================== END ==================-->

<!--================== ADD DAN REMOVE FIELDS INPUT 5 ==================-->
<script>
  $(document).ready(function() {

    var inputCounter5 = 0;

    $('#addInput36').on('click', function() {
      if (inputCounter5 === 0) {
        $('.input-field37').show();
        $('#removeAddedInput37').show();
        $('#removeAddedInput38').show();
        $('#removeAddedInput39').show();
        $('#removeAddedInput40').show();
      } else if (inputCounter5 === 1) {
        $('.input-field37').show();
        $('#addInput36').show();
        $('#removeAddedInput37').show();
      } else if (inputCounter5 === 2) {
        $('.input-field38').show();
        $('#addInput36').show();
        $('#removeAddedInput38').show();
      } else if (inputCounter5 === 3) {
        $('.input-field39').show();
        $('#addInput36').show();
        $('#removeAddedInput39').show();
      } else if (inputCounter5 === 4) {
        $('.input-field40').show();
        $('#addInput36').hide();
        $('#removeAddedInput40').show();
      }

      inputCounter5++;
    });

    // Remove additional input fields
    $('#removeAddedInput37').on('click', function() {
      $('.input-field37').hide();
      $('#addInput36').show();
      inputCounter5--;
    });
    $('#removeAddedInput38').on('click', function() {
      $('.input-field38').hide();
      $('#addInput36').show();
      inputCounter5--;
    });
    $('#removeAddedInput39').on('click', function() {
      $('.input-field39').hide();
      $('#addInput36').show();
      inputCounter5--;
    });
    $('#removeAddedInput40').on('click', function() {
      $('.input-field40').hide();
      $('#addInput36').show();
      inputCounter5--;
    });

  });
</script>
<!--================== END ==================-->

<!--================== FORMAT RUPIAH ==================-->
<script>
  // <!-- INPUT 21 -->
  var cleaveC = new Cleave('.uang_masuk_currency21', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.uang_keluar_currency21', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 22 -->
  var cleaveC = new Cleave('.uang_keluar_currency22', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 23 -->
  var cleaveC = new Cleave('.uang_keluar_currency23', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 24 -->
  var cleaveC = new Cleave('.uang_keluar_currency24', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 25 -->
  var cleaveC = new Cleave('.uang_keluar_currency25', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 26 -->
  var cleaveC = new Cleave('.uang_keluar_currency26', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 27 -->
  var cleaveC = new Cleave('.uang_keluar_currency27', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 28 -->
  var cleaveC = new Cleave('.uang_keluar_currency28', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 29 -->
  var cleaveC = new Cleave('.uang_keluar_currency29', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 30 -->
  var cleaveC = new Cleave('.uang_keluar_currency30', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 31 -->
  var cleaveC = new Cleave('.uang_masuk_currency31', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.uang_keluar_currency31', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 32 -->
  var cleaveC = new Cleave('.uang_keluar_currency32', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 33 -->
  var cleaveC = new Cleave('.uang_keluar_currency33', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 34 -->
  var cleaveC = new Cleave('.uang_keluar_currency34', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 35 -->
  var cleaveC = new Cleave('.uang_keluar_currency35', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 36 -->
  var cleaveC = new Cleave('.uang_masuk_currency36', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  var cleaveC = new Cleave('.uang_keluar_currency36', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 37 -->
  var cleaveC = new Cleave('.uang_keluar_currency37', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 38 -->
  var cleaveC = new Cleave('.uang_keluar_currency38', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 39 -->
  var cleaveC = new Cleave('.uang_keluar_currency39', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->

  // <!-- INPUT 40 -->
  var cleaveC = new Cleave('.uang_keluar_currency40', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand'
  });
  // <!-- END -->
</script>
<!--================== end ==================-->

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