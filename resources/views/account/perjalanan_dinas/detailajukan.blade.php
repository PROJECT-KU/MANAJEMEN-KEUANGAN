@extends('layouts.account')

@section('title')
Update Perjalanan Dinas Ajukan | MIS
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

<!--================== COLOR IN STATUS ==================-->
<style>
  .option-diterima {
    color: green;
  }

  .option-ajukan {
    color: yellow;
  }

  .option-ditolak {
    color: red;
  }
</style>
<!--================== END ==================-->

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>PERJALANAN DINAS AJUKAN</h1>
    </div>

    <div class="section-body">

      <form action="{{ route('account.PerjalananDinas.PengajuanManager', $DatasAjukan->id) }}" method="POST" enctype="multipart/form-data">
        @csrf


        <!--================== DETAIL PERJALANAN DINAS ==================-->
        <div class="card" style="background-color: rgba(255, 255, 255, 0.5); border: 1px solid rgba(0, 0, 0, 0.1); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 5px; padding: 20px; margin: 20px;">
          <div class="card">
            <div class="card-header">
              <h4>DETAIL PERJALANAN DINAS</h4>
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
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Nama Bendahara</label>
                    <input type="text" name="user_name" value="{{ $datas->where('id', $DatasAjukan->user_id)->first()->full_name }}" class="form-control" style="text-transform:uppercase;" readonly>
                    <input type="hidden" name="user_id" value="{{ $DatasAjukan->user_id }}">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Nama Camp</label>
                    <div class="input-group">
                      <input type="text" name="tempat" value="{{ $DatasAjukan->tempat }}" placeholder="Masukkan Nama Camp" class="form-control" style="text-transform:uppercase;" readonly>
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
                      <input type="number" name="camp" value="{{ $DatasAjukan->camp }}" placeholder="Masukkan Nomor Camp Ke" class="form-control" readonly>
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
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ \Carbon\Carbon::parse($DatasAjukan->tanggal_mulai)->format('Y-m-d') }}" placeholder="Masukkan Total Tunjangan" class="form-control" readonly>
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
                    <input type="date" name="tanggal_akhir" id="tanggal_akhir" value="{{ \Carbon\Carbon::parse($DatasAjukan->tanggal_akhir)->format('Y-m-d') }}" placeholder="Masukkan Total Tunjangan" class="form-control" readonly>
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
                      <select class="form-control" name="status" disabled>
                        <option value="" disabled selected>-- PILIH STATUS --</option>
                        <option value="ajukan" {{ $DatasAjukan->status == 'ajukan' ? 'selected' : '' }}>AJUKAN</option>
                        <option value="draft" {{ $DatasAjukan->status == 'draft' ? 'selected' : '' }}>DRAFT</option>
                      </select>
                      @else
                      <select class="form-control" name="status" required>
                        <option value="" disabled selected>-- PILIH STATUS --</option>
                        <option value="diterima" {{ $DatasAjukan->status == 'diterima' ? 'selected' : '' }}>DITERIMA</option>
                        <option value="ajukan" {{ $DatasAjukan->status == 'ajukan' ? 'selected' : '' }}>AJUKAN</option>
                        <option value="ditolak" {{ $DatasAjukan->status == 'ditolak' ? 'selected' : '' }}>DITOLAK</option>
                      </select>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!--================== END ==================-->

          <!--================== DETAIL LAPORAN ==================-->
          <div class="card">
            <div class="card-header">
              <h4>DETAIL LAPORAN</h4>
            </div>
            <div class="card-body">

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label style=" font-weight: bold;">Total Uang Masuk</label>
                    <div class="input-group" style=" border: 2px solid #90EE90; border-radius: 8px;">
                      <div class="input-group-prepend">
                        <span class="input-group-text" style="font-weight: bold;">Rp.</span>
                      </div>
                      <input type="text" style="font-weight: bold;" name="total_uang_masuk" value="{{ number_format($DatasAjukan->total_uang_masuk, 0, ',', ',') }}" placeholder="Masukan Keterangan" class="form-control" readonly>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label style=" font-weight: bold;">Total Uang Keluar</label>
                    <div class="input-group" style=" border: 2px solid #CD5C5C; border-radius: 8px;">
                      <div class="input-group-prepend">
                        <span class="input-group-text" style="font-weight: bold;">Rp.</span>
                      </div>
                      <input type="text" style=" font-weight: bold;" name="total_uang_keluar" value="{{ number_format($DatasAjukan->total_uang_keluar, 0, ',', ',') }}" placeholder="Masukan Keterangan" class="form-control" readonly>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label style=" font-weight: bold;">Sisa Saldo</label>
                    <div class="input-group" style=" border: 2px solid #87CEFA; border-radius: 8px;">
                      <div class="input-group-prepend">
                        <span class="input-group-text" style="font-weight: bold;">Rp.</span>
                      </div>
                      <input type="text" style=" font-weight: bold;" name="sisa_saldo" value="{{ number_format($DatasAjukan->sisa_saldo, 0, ',', ',') }}" placeholder="Masukan Keterangan" class="form-control" readonly>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label style=" font-weight: bold;">Catatan</label>
                    <div class="input-group" style=" border: 2px solid 	#FFA500; border-radius: 8px;">
                      @if (Auth::user()->level == 'karyawan')
                      <textarea type="text" name="deskripsi" placeholder="Masukan Catatan" class="form-control" readonly>{{ $DatasAjukan->deskripsi }}</textarea>
                      @else
                      <textarea type="text" name="deskripsi" placeholder="Masukan Catatan" class="form-control" required>{{ $DatasAjukan->deskripsi }}</textarea>
                      @endif
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <!--================== END ==================-->

          <!--================== CARD 1==================-->
          <div class="accordion" id="additionalInputsAccordion2">
            <div class="card">
              <div class="card-header d-flex justify-content-between">
                <h4> LAPORAN HARI PERTAMA </h4>
                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse2" aria-expanded="true" aria-controls="collapse2" id="toggleCard1" onclick="Card1()">
                  <i class="fas fa-chevron-down"></i>
                </button>
              </div>

              <div class="card-body">

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label>Tanggal</label>
                      <div class="input-group">
                        <input type="date" name="tanggal" id="tanggal" value="{{ $DatasAjukan->tanggal }}" placeholder="Masukkan Tanggal" class="form-control" readonly>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="collapse2" class="collapse" aria-labelledby="heading2" data-parent="#additionalInputsAccordion2">
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
                              <input type="text" name="uang_masuk" value="{{ number_format($DatasAjukan->uang_masuk, 0, ',', ',') }}" placeholder="Total Uang Masuk" class="form-control uang_masuk_currency" readonly>
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
                              <input type="text" name="uang_keluar" value="{{ number_format($DatasAjukan->uang_keluar, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 2 ==================-->
                  @if($DatasAjukan->uang_keluar2 !== null)
                  <div class="card card-custom input-field2">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-2</h4>
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
                              <input type="text" name="uang_keluar2" value="{{ number_format($DatasAjukan->uang_keluar2, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency2" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan2" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan2 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan2 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan2 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan2 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan2 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar2) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar2 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar2) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 3 ==================-->
                  @if($DatasAjukan->uang_keluar3 !== null)
                  <div class="card card-custom input-field3">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-3</h4>
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
                              <input type="text" name="uang_keluar3" value="{{ number_format($DatasAjukan->uang_keluar3, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency3" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan3" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan3 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan3 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan3 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan3 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan3 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar3) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar3 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar3) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 4 ==================-->
                  @if($DatasAjukan->uang_keluar4 !== null)
                  <div class="card card-custom input-field4">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-4</h4>
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
                              <input type="text" name="uang_keluar4" value="{{ number_format($DatasAjukan->uang_keluar4, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency4" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan4" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan4 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan4 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan4 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan4 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan4 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar4) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar4 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar4) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 5 ==================-->
                  @if($DatasAjukan->uang_keluar5 !== null)
                  <div class="card card-custom input-field5">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-5</h4>
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
                              <input type="text" name="uang_keluar5" value="{{ number_format($DatasAjukan->uang_keluar5, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency5" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan5" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan5 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan5 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan5 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan5 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan5 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar5) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar5 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar5) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 6 ==================-->
                  @if($DatasAjukan->uang_keluar6 !== null)
                  <div class="card card-custom input-field6">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-6</h4>
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
                              <input type="text" name="uang_keluar6" value="{{ number_format($DatasAjukan->uang_keluar6, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency6" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan6" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan6 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan6 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan6 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan6 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan6 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar6) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar6 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar6) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT==================-->

                  <!--================== INPUT 7 ==================-->
                  @if($DatasAjukan->uang_keluar7 !== null)
                  <div class="card card-custom input-field7">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-7</h4>
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
                              <input type="text" name="uang_keluar7" value="{{ number_format($DatasAjukan->uang_keluar7, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency7" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan7" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan7 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan7 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan7 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan7 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan7 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar7) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar7 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar7) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 8 ==================-->
                  @if($DatasAjukan->uang_keluar8 !== null)
                  <div class="card card-custom input-field8">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-8</h4>
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
                              <input type="text" name="uang_keluar8" value="{{ number_format($DatasAjukan->uang_keluar8, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency8" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan8" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan8 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan8 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan8 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan8 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan8 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar8) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar8 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar8) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 9 ==================-->
                  @if($DatasAjukan->uang_keluar9 !== null)
                  <div class="card card-custom input-field9">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-9</h4>
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
                              <input type="text" name="uang_keluar9" value="{{ number_format($DatasAjukan->uang_keluar9, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency9" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan9" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan9 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan9 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan9 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan9 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan9 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar9) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar9 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar9) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 10 ==================-->
                  @if($DatasAjukan->uang_keluar10 !== null)
                  <div class="card card-custom input-field10">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-10</h4>
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
                              <input type="text" name="uang_keluar10" value="{{ number_format($DatasAjukan->uang_keluar10, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency10" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan10" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan10 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan10 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan10 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan10 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan10 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar10) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar10 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar10) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                </div>
              </div>
            </div>
          </div>
          <!--================== END CARD ==================-->

          <!--================== CARD 2 ==================-->
          <div class="accordion" id="additionalInputsAccordion3">
            @if($DatasAjukan->tanggal11 !== null)
            <div class="card">
              <div class="card-header d-flex justify-content-between">
                <h4>LAPORAN HARI KEDUA</h4>
                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse3" aria-expanded="true" aria-controls="collapse3" id="toggleCard2" onclick="Card2()">
                  <i class="fas fa-chevron-down"></i>
                </button>
              </div>
              <div class="card-body">

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label>Tanggal</label>
                      <div class="input-group">
                        <input type="date" name="tanggal11" id="tanggal11" value="{{ $DatasAjukan->tanggal11 }}" placeholder="Masukkan Tanggal" class="form-control" readonly>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="collapse3" class="collapse" aria-labelledby="heading3" data-parent="#additionalInputsAccordion3">
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
                              <input type="text" name="uang_masuk11" value="{{ number_format($DatasAjukan->uang_masuk11, 0, ',', ',') }}" placeholder="Total Uang Masuk" class="form-control uang_masuk_currency11" readonly>
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
                              <input type="text" name="uang_keluar11" value="{{ number_format($DatasAjukan->uang_keluar11, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency11" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan11" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan11 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan11 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan11 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan11 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan11 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar11) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar11 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar11) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 12 ==================-->
                  @if($DatasAjukan->uang_keluar12 !== null)
                  <div class="card card-custom input-field12">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-2</h4>
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
                              <input type="text" name="uang_keluar12" value="{{ number_format($DatasAjukan->uang_keluar12, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency12" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan12" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan12 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan12 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan12 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan12 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan12 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar12) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar12 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar12) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 13 ==================-->
                  @if($DatasAjukan->uang_keluar13 !== null)
                  <div class="card card-custom input-field13">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-3</h4>
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
                              <input type="text" name="uang_keluar13" value="{{ number_format($DatasAjukan->uang_keluar13, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency13" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan13" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan13 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan13 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan13 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan13 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan13 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar13) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar13 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar13) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 14 ==================-->
                  @if($DatasAjukan->uang_keluar14 !== null)
                  <div class="card card-custom input-field14">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-4</h4>
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
                              <input type="text" name="uang_keluar14" value="{{ number_format($DatasAjukan->uang_keluar14, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency14" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan14" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan14 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan14 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan14 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan14 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan14 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar14) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar14 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar14) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 15 ==================-->
                  @if($DatasAjukan->uang_keluar15 !== null)
                  <div class="card card-custom input-field15">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-5</h4>
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
                              <input type="text" name="uang_keluar15" value="{{ number_format($DatasAjukan->uang_keluar15, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency15" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan15" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan15 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan15 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan15 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan15 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan15 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar15) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar15 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar15) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 16 ==================-->
                  @if($DatasAjukan->uang_keluar16 !== null)
                  <div class="card card-custom input-field16">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-6</h4>
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
                              <input type="text" name="uang_keluar16" value="{{ number_format($DatasAjukan->uang_keluar16, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency16" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan16" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan16 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan16 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan16 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan16 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan16 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar16) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar16 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar16) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 17 ==================-->
                  @if($DatasAjukan->uang_keluar17 !== null)
                  <div class="card card-custom input-field17">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-7</h4>
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
                              <input type="text" name="uang_keluar17" value="{{ number_format($DatasAjukan->uang_keluar17, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency17" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan17" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan17 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan17 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan17 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan17 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan17 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar17) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar17 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar17) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 18 ==================-->
                  @if($DatasAjukan->uang_keluar18 !== null)
                  <div class="card card-custom input-field18">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-8</h4>
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
                              <input type="text" name="uang_keluar18" value="{{ number_format($DatasAjukan->uang_keluar18, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency18" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan18" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan18 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan18 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan18 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan18 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan18 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar18) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar18 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar18) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 19 ==================-->
                  @if($DatasAjukan->uang_keluar19 !== null)
                  <div class="card card-custom input-field19">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-9</h4>
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
                              <input type="text" name="uang_keluar19" value="{{ number_format($DatasAjukan->uang_keluar19, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency19" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan19" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan19 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan19 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan19 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan19 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan19 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar19) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar19 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar19) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 20 ==================-->
                  @if($DatasAjukan->uang_keluar20 !== null)
                  <div class="card card-custom input-field20">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-10</h4>
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
                              <input type="text" name="uang_keluar20" value="{{ number_format($DatasAjukan->uang_keluar20, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency20" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan20" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan20 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan20 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan20 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan20 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan20 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar20) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar20 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar20) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT==================-->

                </div>
              </div>
            </div>
          </div>
          <!--================== END CARD ==================-->

          <!--================== CARD 3 ==================-->
          <div class="accordion" id="additionalInputsAccordion4">
            @if($DatasAjukan->tanggal21 !== null)
            <div class="card">
              <div class="card-header d-flex justify-content-between">
                <h4>LAPORAN HARI KETIGA</h4>
                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse4" aria-expanded="true" aria-controls="collapse4" id="toggleCard3" onclick="Card3()">
                  <i class="fas fa-chevron-down"></i>
                </button>
              </div>
              <div class="card-body">

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label>Tanggal</label>
                      <div class="input-group">
                        <input type="date" name="tanggal21" id="tanggal21" value="{{ $DatasAjukan->tanggal21 }}" placeholder="Masukkan Tanggal" class="form-control" readonly>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="collapse4" class="collapse" aria-labelledby="heading4" data-parent="#additionalInputsAccordion4">
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
                              <input type="text" name="uang_masuk21" value="{{ number_format($DatasAjukan->uang_masuk21, 0, ',', ',') }}" placeholder="Total Uang Masuk" class="form-control uang_masuk_currency21" readonly>
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
                              <input type="text" name="uang_keluar21" value="{{ number_format($DatasAjukan->uang_keluar21, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency21" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan21" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan21 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan21 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan21 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan21 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan21 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar21) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar21 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar21) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 22 ==================-->
                  @if($DatasAjukan->uang_keluar22 !== null)
                  <div class="card card-custom input-field22">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-2</h4>
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
                              <input type="text" name="uang_keluar22" value="{{ number_format($DatasAjukan->uang_keluar22, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency22" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan22" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan22 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan22 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan22 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan22 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan22 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar22) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar22 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar22) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 23 ==================-->
                  @if($DatasAjukan->uang_keluar23 !== null)
                  <div class="card card-custom input-field23">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-3</h4>
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
                              <input type="text" name="uang_keluar23" value="{{ number_format($DatasAjukan->uang_keluar23, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency23" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan23" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan23 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan23 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan23 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan23 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan23 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar23) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar23 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar23) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 24 ==================-->
                  @if($DatasAjukan->uang_keluar24 !== null)
                  <div class="card card-custom input-field24">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-4</h4>
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
                              <input type="text" name="uang_keluar24" value="{{ number_format($DatasAjukan->uang_keluar24, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency24" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan24" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan24 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan24 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan24 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan24 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan24 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar24) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar24 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar24) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 25 ==================-->
                  @if($DatasAjukan->uang_keluar25 !== null)
                  <div class="card card-custom input-field25">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-5</h4>
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
                              <input type="text" name="uang_keluar25" value="{{ number_format($DatasAjukan->uang_keluar25, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency25" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan25" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan25 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan25 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan25 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan25 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan25 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar25) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar25 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar25) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 26 ==================-->
                  @if($DatasAjukan->uang_keluar26 !== null)
                  <div class="card card-custom input-field26">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-6</h4>
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
                              <input type="text" name="uang_keluar26" value="{{ number_format($DatasAjukan->uang_keluar26, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency26" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan26" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan26 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan26 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan26 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan26 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan26 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar26) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar26 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar26) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 27 ==================-->
                  @if($DatasAjukan->uang_keluar27 !== null)
                  <div class="card card-custom input-field27">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-7</h4>
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
                              <input type="text" name="uang_keluar27" value="{{ number_format($DatasAjukan->uang_keluar27, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency27" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan27" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan27 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan27 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan27 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan27 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan27 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar27) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar27 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar27) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 28 ==================-->
                  @if($DatasAjukan->uang_keluar28 !== null)
                  <div class="card card-custom input-field28">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-8</h4>
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
                              <input type="text" name="uang_keluar28" value="{{ number_format($DatasAjukan->uang_keluar28, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency28" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan28" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan28 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan28 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan28 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan28 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan28 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar28) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar28 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar28) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 29 ==================-->
                  @if($DatasAjukan->uang_keluar29 !== null)
                  <div class="card card-custom input-field29">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-9</h4>
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
                              <input type="text" name="uang_keluar29" value="{{ number_format($DatasAjukan->uang_keluar29, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency29" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan29" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan29 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan29 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan29 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan29 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan29 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar29) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar29 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar29) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 30 ==================-->
                  @if($DatasAjukan->uang_keluar30 !== null)
                  <div class="card card-custom input-field30">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-10</h4>
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
                              <input type="text" name="uang_keluar30" value="{{ number_format($DatasAjukan->uang_keluar30, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency30" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan30" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan30 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan30 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan30 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan30 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan30 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar30) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar30 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar30) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                </div>
              </div>
            </div>
          </div>
          <!--================== END CARD ==================-->

          <!--================== CARD 4 ==================-->
          <div class="accordion" id="additionalInputsAccordion5">
            @if($DatasAjukan->tanggal31 !== null)
            <div class="card card-field4">
              <div class="card-header d-flex justify-content-between">
                <h4>LAPORAN HARI KEEMPAT</h4>
                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse5" aria-expanded="true" aria-controls="collapse5" id="toggleCard4" onclick="Card4()">
                  <i class="fas fa-chevron-down"></i>
                </button>
              </div>
              <div class="card-body">

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label>Tanggal</label>
                      <div class="input-group">
                        <input type="date" name="tanggal31" id="tanggal31" value="{{ $DatasAjukan->tanggal31 }}" placeholder="Masukkan Tanggal" class="form-control" readonly>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="collapse5" class="collapse" aria-labelledby="heading5" data-parent="#additionalInputsAccordion5">
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
                              <input type="text" name="uang_masuk31" value="{{ number_format($DatasAjukan->uang_masuk31, 0, ',', ',') }}" placeholder="Total Uang Masuk" class="form-control uang_masuk_currency31" readonly>
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
                              <input type="text" name="uang_keluar31" value="{{ number_format($DatasAjukan->uang_keluar31, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency31" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan31" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan31 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan31 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan31 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan31 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan31 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar31) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar31 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar31) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 32 ==================-->
                  @if($DatasAjukan->uang_keluar32 !== null)
                  <div class="card card-custom input-field32">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-2</h4>
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
                              <input type="text" name="uang_keluar32" value="{{ number_format($DatasAjukan->uang_keluar32, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency32" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan32" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan32 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan32 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan32 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan32 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan32 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar32) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar32 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar32) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 33 ==================-->
                  @if($DatasAjukan->uang_keluar33 !== null)
                  <div class="card card-custom input-field33">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-3</h4>
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
                              <input type="text" name="uang_keluar33" value="{{ number_format($DatasAjukan->uang_keluar33, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency33" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan33" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan33 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan33 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan33 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan33 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan33 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar33) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar33 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar33) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 34 ==================-->
                  @if($DatasAjukan->uang_keluar34 !== null)
                  <div class="card card-custom input-field34">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-4</h4>
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
                              <input type="text" name="uang_keluar34" value="{{ number_format($DatasAjukan->uang_keluar34, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency34" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan34" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan34 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan34 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan34 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan34 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan34 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar34) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar34 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar34) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 35 ==================-->
                  @if($DatasAjukan->uang_keluar35 !== null)
                  <div class="card card-custom input-field35">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-5</h4>
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
                              <input type="text" name="uang_keluar35" value="{{ number_format($DatasAjukan->uang_keluar35, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency35" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan35" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan35 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan35 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan35 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan35 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan35 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar35) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar35 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar35) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->
                </div>
              </div>
            </div>
          </div>
          <!--================== END CARD ==================-->

          @if($DatasAjukan->tanggal36 == null)
          <div class="button-container">
            @if (Auth::user()->level == 'karyawan')
            <a href="{{ route('account.PerjalananDinas.index') }}" class="btn btn-info" role="button" style="width:100%; height:45px; font-size:14px; padding:10px;">
              <i class="fa fa-undo mr-1"></i> KEMBALI
            </a>
            @else
            <button class="btn btn-primary btn-submit" name="action" value="save" type="submit" style="width:50%; margin-right:5px;">
              <i class="fa fa-paper-plane"></i> SIMPAN
            </button>
            <a href="{{ route('account.PerjalananDinas.index') }}" class="btn btn-info" role="button" style="width:50%; height:45px; font-size:14px; padding:10px;">
              <i class="fa fa-undo mr-1"></i> KEMBALI
            </a>
            @endif
          </div>
          @endif

          <!--================== CARD 5 ==================-->
          <div class="accordion" id="additionalInputsAccordion6">
            @if($DatasAjukan->tanggal36 !== null)
            <div class="card card-field5">
              <div class="card-header d-flex justify-content-between">
                <h4>LAPORAN HARI KELIMA</h4>
                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse6" aria-expanded="true" aria-controls="collapse6" id="toggleCard5" onclick="Card5()">
                  <i class="fas fa-chevron-down"></i>
                </button>
              </div>
              <div class="card-body">

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label>Tanggal</label>
                      <div class="input-group">
                        <input type="date" name="tanggal36" id="tanggal36" value="{{ $DatasAjukan->tanggal36 }}" placeholder="Masukkan Tanggal" class="form-control" readonly>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="collapse6" class="collapse" aria-labelledby="heading6" data-parent="#additionalInputsAccordion6">
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
                              <input type="text" name="uang_masuk36" value="{{ number_format($DatasAjukan->uang_masuk36, 0, ',', ',') }}" placeholder="Total Uang Masuk" class="form-control uang_masuk_currency36" readonly>
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
                              <input type="text" name="uang_keluar36" value="{{ number_format($DatasAjukan->uang_keluar36, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency36" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan36" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan36 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan36 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan36 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan36 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan36 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar36) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar36 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar36) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 37 ==================-->
                  @if($DatasAjukan->uang_keluar37 !== null)
                  <div class="card card-custom input-field37">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-2</h4>
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
                              <input type="text" name="uang_keluar37" value="{{ number_format($DatasAjukan->uang_keluar37, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency37" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan37" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan37 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan37 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan37 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan37 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan37 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar37) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar37 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar37) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 38 ==================-->
                  @if($DatasAjukan->uang_keluar38 !== null)
                  <div class="card card-custom input-field38">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-3</h4>
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
                              <input type="text" name="uang_keluar38" value="{{ number_format($DatasAjukan->uang_keluar38, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency38" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan38" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan38 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan38 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan38 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan38 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan38 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar38) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar38 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar38) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 39 ==================-->
                  @if($DatasAjukan->uang_keluar39 !== null)
                  <div class="card card-custom input-field39">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-4</h4>
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
                              <input type="text" name="uang_keluar39" value="{{ number_format($DatasAjukan->uang_keluar39, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency39" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan39" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan39 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan39 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan39 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan39 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan39 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar39) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar39 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar39) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                  <!--================== INPUT 40 ==================-->
                  @if($DatasAjukan->uang_keluar40 !== null)
                  <div class="card card-custom input-field40">
                    <div class="card-header d-flex justify-content-between">
                      <h4>INPUTAN KE-5</h4>
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
                              <input type="text" name="uang_keluar40" value="{{ number_format($DatasAjukan->uang_keluar40, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency40" readonly>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Kategori</label>
                            <div class="input-group">
                              <select class="form-control" name="keterangan40" disabled>
                                <option value="" disabled selected>-- PILIH KATEGORI --</option>
                                <option value="transportasi" {{ $DatasAjukan->keterangan40 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                                <option value="makan" {{ $DatasAjukan->keterangan40 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                                <option value="obat-obatan" {{ $DatasAjukan->keterangan40 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                                <option value="jajan atau belanja" {{ $DatasAjukan->keterangan40 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                                <option value="lain-lain" {{ $DatasAjukan->keterangan40 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label>Bukti Struk</label>
                            <a href="{{ asset('images/' . $DatasAjukan->gambar40) }}" data-lightbox="{{ $DatasAjukan->id }}">
                              <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                                @if ($DatasAjukan->gambar40 == null)
                                <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/no-image.jpg') }}" class="img-thumbnail">
                                @else
                                <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasAjukan->gambar40) }}" alt="Preview Image">
                                @endif
                              </div>
                            </a>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  @endif
                  <!--================== END INPUT ==================-->

                </div>
              </div>
            </div>
          </div>
          <!--================== END CARD ==================-->

          @if($DatasAjukan->tanggal36 !== null)
          <div class="button-container">
            @if (Auth::user()->level == 'karyawan')
            <a href="{{ route('account.PerjalananDinas.index') }}" class="btn btn-info" role="button" style="width:100%; height:45px; font-size:14px; padding:10px;">
              <i class="fa fa-undo mr-1"></i> KEMBALI
            </a>
            @else
            <button class="btn btn-primary btn-submit" name="action" value="save" type="submit" style="width:50%; margin-right:5px;">
              <i class="fa fa-paper-plane"></i> SIMPAN
            </button>
            <a href="{{ route('account.PerjalananDinas.index') }}" class="btn btn-info" role="button" style="width:50%; height:45px; font-size:14px; padding:10px;">
              <i class="fa fa-undo mr-1"></i> KEMBALI
            </a>
            @endif
          </div>
          @endif

      </form>
    </div>
  </section>
</div>
</div>

<!--================== PREVIEW IMAGE ==================-->
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

<!--================== OPEN & CLOSE ACCORDION ==================-->
<script>
  // <!-- CARD 1 -->
  function Card1() {
    var icon = document.getElementById("toggleCard1").querySelector("i");
    if (icon.classList.contains("fa-chevron-down")) {
      icon.classList.remove("fa-chevron-down");
      icon.classList.add("fa-chevron-up");
    } else {
      icon.classList.remove("fa-chevron-up");
      icon.classList.add("fa-chevron-down");
    }
  }
  // <!-- END -->

  // <!-- CARD 2 -->
  function Card2() {
    var icon = document.getElementById("toggleCard2").querySelector("i");
    if (icon.classList.contains("fa-chevron-down")) {
      icon.classList.remove("fa-chevron-down");
      icon.classList.add("fa-chevron-up");
    } else {
      icon.classList.remove("fa-chevron-up");
      icon.classList.add("fa-chevron-down");
    }
  }
  // <!-- END -->

  // <!-- CARD 3 -->
  function Card3() {
    var icon = document.getElementById("toggleCard3").querySelector("i");
    if (icon.classList.contains("fa-chevron-down")) {
      icon.classList.remove("fa-chevron-down");
      icon.classList.add("fa-chevron-up");
    } else {
      icon.classList.remove("fa-chevron-up");
      icon.classList.add("fa-chevron-down");
    }
  }
  // <!-- END -->

  // <!-- CARD 4 -->
  function Card4() {
    var icon = document.getElementById("toggleCard4").querySelector("i");
    if (icon.classList.contains("fa-chevron-down")) {
      icon.classList.remove("fa-chevron-down");
      icon.classList.add("fa-chevron-up");
    } else {
      icon.classList.remove("fa-chevron-up");
      icon.classList.add("fa-chevron-down");
    }
  }
  // <!-- END -->

  // <!-- CARD 5 -->
  function Card5() {
    var icon = document.getElementById("toggleCard5").querySelector("i");
    if (icon.classList.contains("fa-chevron-down")) {
      icon.classList.remove("fa-chevron-down");
      icon.classList.add("fa-chevron-up");
    } else {
      icon.classList.remove("fa-chevron-up");
      icon.classList.add("fa-chevron-down");
    }
  }
  // <!-- END -->
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
<!-- END -->

@stop