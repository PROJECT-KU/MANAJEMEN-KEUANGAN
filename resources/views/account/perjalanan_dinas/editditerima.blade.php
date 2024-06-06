@extends('layouts.account')

@section('title')
Update Perjalanan Dinas Diterima | MIS
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
      <h1>PERJALANAN DINAS DITERIMA</h1>
    </div>

    <div class="section-body">

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

          <form action="{{ route('account.PerjalananDinas.updateDiterima', $DatasDiterima->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!--================== DETAIL PERJALANAN DINAS ==================-->
            @if (Auth::user()->level == 'karyawan')
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Nama Bendahara</label>
                  <select class="form-control select2" name="user_id" id="karyawanSelect" style="width: 100%" disabled>
                    <option value="" disabled selected>-- PILIH NAMA BENDAHARA --</option>
                    @foreach ($datas as $user)
                    <option value="{{ $user->id }}" {{ $user->id == $DatasDiterima->user_id ? 'selected' : '' }}>{{ $user->full_name }}</option>
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
                    <input type="text" name="tempat" value="{{ $DatasDiterima->tempat }}" placeholder="Masukkan Nama Camp" class="form-control" style="text-transform:uppercase;" readonly>
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
                    <input type="number" name="camp" value="{{ $DatasDiterima->camp }}" placeholder="Masukkan Nomor Camp Ke" class="form-control" readonly>
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
                  <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ \Carbon\Carbon::parse($DatasDiterima->tanggal_mulai)->format('Y-m-d') }}" placeholder="Masukkan Total Tunjangan" class="form-control" readonly>
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
                  <input type="date" name="tanggal_akhir" id="tanggal_akhir" value="{{ \Carbon\Carbon::parse($DatasDiterima->tanggal_akhir)->format('Y-m-d') }}" placeholder="Masukkan Total Tunjangan" class="form-control" readonly>
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
                      <option value="diterima" {{ $DatasDiterima->status == 'diterima' ? 'selected' : '' }}>DITERIMA</option>
                      <option value="ajukan" {{ $DatasDiterima->status == 'ajukan' ? 'selected' : '' }}>AJUKAN</option>
                      <option value="ditolak" {{ $DatasDiterima->status == 'ditolak' ? 'selected' : '' }}>DITOLAK</option>
                    </select>
                    @else
                    <select class="form-control" name="status" disabled>
                      <option value="" disabled selected>-- PILIH STATUS --</option>
                      <option value="diterima" {{ $DatasDiterima->status == 'diterima' ? 'selected' : '' }}>DITERIMA</option>
                      <option value="ajukan" {{ $DatasDiterima->status == 'ajukan' ? 'selected' : '' }}>AJUKAN</option>
                      <option value="ditolak" {{ $DatasDiterima->status == 'ditolak' ? 'selected' : '' }}>DITOLAK</option>
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
            @else
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Nama Bendahara</label>
                  <select class="form-control select2" name="user_id" id="karyawanSelect" style="width: 100%">
                    <option value="" disabled selected>-- PILIH NAMA BENDAHARA --</option>
                    @foreach ($datas as $user)
                    <option value="{{ $user->id }}" {{ $user->id == $DatasDiterima->user_id ? 'selected' : '' }}>{{ $user->full_name }}</option>
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
                    <input type="text" name="tempat" value="{{ $DatasDiterima->tempat }}" placeholder="Masukkan Nama Camp" class="form-control" style="text-transform:uppercase;">
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
                    <input type="number" name="camp" value="{{ $DatasDiterima->camp }}" placeholder="Masukkan Nomor Camp Ke" class="form-control">
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
                  <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ \Carbon\Carbon::parse($DatasDiterima->tanggal_mulai)->format('Y-m-d') }}" placeholder="Masukkan Total Tunjangan" class="form-control">
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
                  <input type="date" name="tanggal_akhir" id="tanggal_akhir" value="{{ \Carbon\Carbon::parse($DatasDiterima->tanggal_akhir)->format('Y-m-d') }}" placeholder="Masukkan Total Tunjangan" class="form-control">
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
                      <option value="ajukan" {{ $DatasDiterima->status == 'ajukan' ? 'selected' : '' }}>AJUKAN</option>
                    </select>
                    @else
                    <select class="form-control" name="status">
                      <option value="" disabled selected>-- PILIH STATUS --</option>
                      <option value="diterima" {{ $DatasDiterima->status == 'diterima' ? 'selected' : '' }}>DITERIMA</option>
                      <option value="ajukan" {{ $DatasDiterima->status == 'ajukan' ? 'selected' : '' }}>AJUKAN</option>
                      <option value="ditolak" {{ $DatasDiterima->status == 'ditolak' ? 'selected' : '' }}>DITOLAK</option>
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
            @endif

        </div>
      </div>
      <!--================== END ==================-->

      <!--================== CARD 1==================-->
      <div class="accordion" id="additionalInputsAccordion">
        <div class="card">
          <div class="card-header d-flex justify-content-between">
            <h4> TAMBAH LAPORAN</h4>
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
                    <input type="text" name="tanggal" id="tanggal" value="{{strftime('%d %B %Y', strtotime($DatasDiterima->tanggal)) }}" placeholder="Masukkan Tanggal" class="form-control" readonly>
                  </div>
                  @error('tanggal')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
            </div>

            <div id="collapse2" class="collapse" aria-labelledby="heading2" data-parent="#additionalInputsAccordion">
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
                          <input type="text" name="uang_masuk" value="{{ number_format($DatasDiterima->uang_masuk, 0, ',', ',') }}" placeholder="Total Uang Masuk" class="form-control uang_masuk_currency" readonly>
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
                          <input type="text" name="uang_keluar" value="{{ number_format($DatasDiterima->uang_keluar, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!--================== END ==================-->


              <!--================== INPUT 2 ==================-->
              @if($DatasDiterima->uang_keluar2 !== null)
              <div class="card card-custom input-field2">
                <div class="card-header d-flex justify-content-between">
                  <h4>
                    TAMBAH INPUTAN
                  </h4>
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
                          <input type="text" name="uang_keluar2" value="{{ number_format($DatasDiterima->uang_keluar2, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency2" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan2 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan2 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan2 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan2 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan2 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar2) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar2 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar2) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 3 ==================-->
              @if($DatasDiterima->uang_keluar3 !== null)
              <div class="card card-custom input-field3">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar3" value="{{ number_format($DatasDiterima->uang_keluar3, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency3" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan3 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan3 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan3 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan3 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan3 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar3) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar3 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar3) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 4 ==================-->
              @if($DatasDiterima->uang_keluar4 !== null)
              <div class="card card-custom input-field4">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar4" value="{{ number_format($DatasDiterima->uang_keluar4, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency4" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan4 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan4 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan4 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan4 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan4 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar4) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar4 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar4) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 5 ==================-->
              @if($DatasDiterima->uang_keluar5 !== null)
              <div class="card card-custom input-field5">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar5" value="{{ number_format($DatasDiterima->uang_keluar5, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency5" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan5 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan5 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan5 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan5 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan5 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar5) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar5 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar5) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 6 ==================-->
              @if($DatasDiterima->uang_keluar6 !== null)
              <div class="card card-custom input-field6">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar6" value="{{ number_format($DatasDiterima->uang_keluar6, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency6" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan6 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan6 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan6 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan6 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan6 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar6) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar6 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar6) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 7 ==================-->
              @if($DatasDiterima->uang_keluar7 !== null)
              <div class="card card-custom input-field7">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar7" value="{{ number_format($DatasDiterima->uang_keluar7, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency7" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan7 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan7 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan7 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan7 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan7 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar7) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar7 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar7) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 8 ==================-->
              @if($DatasDiterima->uang_keluar8 !== null)
              <div class="card card-custom input-field8">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar8" value="{{ number_format($DatasDiterima->uang_keluar8, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency8" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan8 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan8 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan8 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan8 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan8 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar8) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar8 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar8) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 9 ==================-->
              @if($DatasDiterima->uang_keluar9 !== null)
              <div class="card card-custom input-field9">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar9" value="{{ number_format($DatasDiterima->uang_keluar9, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency9" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan9 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan9 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan9 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan9 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan9 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar9) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar9 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar9) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 10 ==================-->
              @if($DatasDiterima->uang_keluar10 !== null)
              <div class="card card-custom input-field10">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar10" value="{{ number_format($DatasDiterima->uang_keluar10, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency10" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan10 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan10 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan10 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan10 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan10 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar10) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar10 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar10) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

            </div>
          </div>
        </div>
      </div>
      <!--================== END ==================-->

      <!--================== CARD 2 ==================-->
      <div class="accordion" id="additionalInputsAccordion">
        @if($DatasDiterima->tanggal11 !== null)
        <div class="card">
          <div class="card-header d-flex justify-content-between">
            <h4>TAMBAH LAPORAN</h4>
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
                    <input type="text" name="tanggal11" id="tanggal11" value="{{ strftime('%d %B %Y', strtotime($DatasDiterima->tanggal11)) }}" placeholder="Masukkan Tanggal" class="form-control" readonly>
                  </div>
                </div>
              </div>
            </div>

            <div id="collapse3" class="collapse" aria-labelledby="heading3" data-parent="#additionalInputsAccordion">
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
                          <input type="text" name="uang_masuk11" value="{{ number_format($DatasDiterima->uang_masuk11, 0, ',', ',') }}" placeholder="Total Uang Masuk" class="form-control uang_masuk_currency11" readonly>
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
                          <input type="text" name="uang_keluar11" value="{{ number_format($DatasDiterima->uang_keluar11, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency11" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan11 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan11 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan11 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan11 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan11 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar11) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar11 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar11) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 12 ==================-->
              @if($DatasDiterima->uang_keluar12 !== null)
              <div class="card card-custom input-field12">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar12" value="{{ number_format($DatasDiterima->uang_keluar12, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency12" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan12 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan12 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan12 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan12 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan12 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar12) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar12 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar12) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 13 ==================-->
              @if($DatasDiterima->uang_keluar13 !== null)
              <div class="card card-custom input-field13">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar13" value="{{ number_format($DatasDiterima->uang_keluar13, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency13" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan13 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan13 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan13 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan13 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan13 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar13) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar13 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar13) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 14 ==================-->
              @if($DatasDiterima->uang_keluar14 !== null)
              <div class="card card-custom input-field14">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar14" value="{{ number_format($DatasDiterima->uang_keluar14, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency14" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan14 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan14 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan14 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan14 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan14 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar14) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar14 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar14) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 15 ==================-->
              @if($DatasDiterima->uang_keluar15 !== null)
              <div class="card card-custom input-field15">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar15" value="{{ number_format($DatasDiterima->uang_keluar15, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency15" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan15 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan15 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan15 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan15 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan15 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar15) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar15 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar15) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 16 ==================-->
              @if($DatasDiterima->uang_keluar16 !== null)
              <div class="card card-custom input-field16">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar16" value="{{ number_format($DatasDiterima->uang_keluar16, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency16" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan16 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan16 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan16 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan16 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan16 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar16) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar16 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar16) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 17 ==================-->
              @if($DatasDiterima->uang_keluar17 !== null)
              <div class="card card-custom input-field17">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar17" value="{{ number_format($DatasDiterima->uang_keluar17, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency17" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan17 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan17 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan17 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan17 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan17 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar17) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar17 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar17) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 18 ==================-->
              @if($DatasDiterima->uang_keluar18 !== null)
              <div class="card card-custom input-field18">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar18" value="{{ number_format($DatasDiterima->uang_keluar18, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency18" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan18 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan18 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan18 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan18 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan18 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar18) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar18 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar18) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 19 ==================-->
              @if($DatasDiterima->uang_keluar19 !== null)
              <div class="card card-custom input-field19">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar19" value="{{ number_format($DatasDiterima->uang_keluar19, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency19" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan19 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan19 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan19 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan19 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan19 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar19) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar19 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar19) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 20 ==================-->
              @if($DatasDiterima->uang_keluar20 !== null)
              <div class="card card-custom input-field20">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar20" value="{{ number_format($DatasDiterima->uang_keluar20, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency20" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan20 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan20 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan20 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan20 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan20 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar20) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar20 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar20) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
                @endif

              </div>
            </div>
          </div>
        </div>
      </div>
      <!--================== END ==================-->

      <!--================== CARD 3 ==================-->
      <div class="accordion" id="additionalInputsAccordion">
        @if($DatasDiterima->tanggal21 !== null)
        <div class="card">
          <div class="card-header d-flex justify-content-between">
            <h4>TAMBAH LAPORAN</h4>
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
                    <input type="text" name="tanggal21" id="tanggal21" value="{{ strftime('%d %B %Y', strtotime($DatasDiterima->tanggal21)) }}" placeholder="Masukkan Tanggal" class="form-control" readonly>
                  </div>
                </div>
              </div>
            </div>

            <div id="collapse4" class="collapse" aria-labelledby="heading4" data-parent="#additionalInputsAccordion">
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
                          <input type="text" name="uang_masuk21" value="{{ number_format($DatasDiterima->uang_masuk21, 0, ',', ',') }}" placeholder="Total Uang Masuk" class="form-control uang_masuk_currency21" readonly>
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
                          <input type="text" name="uang_keluar21" value="{{ number_format($DatasDiterima->uang_keluar21, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency21" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan21 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan21 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan21 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan21 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan21 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar21) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar21 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar21) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 22 ==================-->
              @if($DatasDiterima->uang_keluar22 !== null)
              <div class="card card-custom input-field22">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar22" value="{{ number_format($DatasDiterima->uang_keluar22, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency22" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan22 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan22 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan22 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan22 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan22 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar22) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar22 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar22) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 23 ==================-->
              @if($DatasDiterima->uang_keluar23 !== null)
              <div class="card card-custom input-field23">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar23" value="{{ number_format($DatasDiterima->uang_keluar23, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency23" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan23 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan23 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan23 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan23 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan23 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar23) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar23 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar23) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 24 ==================-->
              @if($DatasDiterima->uang_keluar24 !== null)
              <div class="card card-custom input-field24">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar24" value="{{ number_format($DatasDiterima->uang_keluar24, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency24" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan24 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan24 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan24 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan24 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan24 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar24) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar24 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar24) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 25 ==================-->
              @if($DatasDiterima->uang_keluar25 !== null)
              <div class="card card-custom input-field25">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar25" value="{{ number_format($DatasDiterima->uang_keluar25, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency25" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan25 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan25 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan25 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan25 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan25 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar25) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar25 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar25) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 26 ==================-->
              @if($DatasDiterima->uang_keluar26 !== null)
              <div class="card card-custom input-field26">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar26" value="{{ number_format($DatasDiterima->uang_keluar26, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency26" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan26 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan26 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan26 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan26 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan26 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar26) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar26 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar26) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 27 ==================-->
              @if($DatasDiterima->uang_keluar27 !== null)
              <div class="card card-custom input-field27">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar27" value="{{ number_format($DatasDiterima->uang_keluar27, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency27" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan27 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan27 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan27 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan27 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan27 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar27) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar27 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar27) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 28 ==================-->
              @if($DatasDiterima->uang_keluar28 !== null)
              <div class="card card-custom input-field28">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar28" value="{{ number_format($DatasDiterima->uang_keluar28, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency28" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan28 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan28 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan28 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan28 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan28 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar28) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar28 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar28) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 29 ==================-->
              @if($DatasDiterima->uang_keluar29 !== null)
              <div class="card card-custom input-field29">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar29" value="{{ number_format($DatasDiterima->uang_keluar29, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency29" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan29 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan29 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan29 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan29 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan29 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar29) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar29 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar29) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 30 ==================-->
              @if($DatasDiterima->uang_keluar30 !== null)
              <div class="card card-custom input-field30">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar30" value="{{ number_format($DatasDiterima->uang_keluar30, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency30" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan30 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan30 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan30 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan30 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan30 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar30) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar30 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar30) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

            </div>
          </div>
        </div>
      </div>
      <!--================== END ==================-->

      <!--================== CARD 4 ==================-->
      <div class="accordion" id="additionalInputsAccordion">
        @if($DatasDiterima->tanggal31 !== null)
        <div class="card card-field4">
          <div class="card-header d-flex justify-content-between">
            <h4>TAMBAH LAPORAN</h4>
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
                    <input type="text" name="tanggal31" id="tanggal31" value="{{ strftime('%d %B %Y', strtotime($DatasDiterima->tanggal31)) }}" placeholder="Masukkan Tanggal" class="form-control" readonly>
                  </div>
                </div>
              </div>
            </div>

            <div id="collapse5" class="collapse" aria-labelledby="heading5" data-parent="#additionalInputsAccordion">
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
                          <input type="text" name="uang_masuk31" value="{{ number_format($DatasDiterima->uang_masuk31, 0, ',', ',') }}" placeholder="Total Uang Masuk" class="form-control uang_masuk_currency31" readonly>
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
                          <input type="text" name="uang_keluar31" value="{{ number_format($DatasDiterima->uang_keluar31, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency31" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan31 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan31 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan31 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan31 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan31 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar31) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar31 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar31) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 32 ==================-->
              @if($DatasDiterima->uang_keluar32 !== null)
              <div class="card card-custom input-field32">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar32" value="{{ number_format($DatasDiterima->uang_keluar32, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency32" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan32 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan32 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan32 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan32 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan32 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar32) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar32 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar32) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 33 ==================-->
              @if($DatasDiterima->uang_keluar33 !== null)
              <div class="card card-custom input-field33">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar33" value="{{ number_format($DatasDiterima->uang_keluar33, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency33" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan33 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan33 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan33 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan33 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan33 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar33) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar33 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar33) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 34 ==================-->
              @if($DatasDiterima->uang_keluar34 !== null)
              <div class="card card-custom input-field34">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar34" value="{{ number_format($DatasDiterima->uang_keluar34, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency34" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan34 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan34 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan34 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan34 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan34 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar34) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar34 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar34) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 35 ==================-->
              @if($DatasDiterima->uang_keluar35 !== null)
              <div class="card card-custom input-field35">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar35" value="{{ number_format($DatasDiterima->uang_keluar35, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency35" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan35 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan35 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan35 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan35 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan35 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar35) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar35 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar35) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 36 ==================-->
              @if($DatasDiterima->uang_keluar36 !== null)
              <div class="card card-custom input-field36">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar36" value="{{ number_format($DatasDiterima->uang_keluar36, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency36" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan36 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan36 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan36 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan36 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan36 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar36) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar36 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar36) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 37 ==================-->
              @if($DatasDiterima->uang_keluar37 !== null)
              <div class="card card-custom input-field37">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar37" value="{{ number_format($DatasDiterima->uang_keluar37, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency37" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan37 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan37 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan37 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan37 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan37 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar37) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar37 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar37) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 38 ==================-->
              @if($DatasDiterima->uang_keluar38 !== null)
              <div class="card card-custom input-field38">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar38" value="{{ number_format($DatasDiterima->uang_keluar38, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency38" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan38 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan38 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan38 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan38 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan38 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar38) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar38 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar38) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 39 ==================-->
              @if($DatasDiterima->uang_keluar39 !== null)
              <div class="card card-custom input-field39">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar39" value="{{ number_format($DatasDiterima->uang_keluar39, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency39" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan39 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan39 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan39 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan39 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan39 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar39) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar39 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar39) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

              <!--================== INPUT 40 ==================-->
              @if($DatasDiterima->uang_keluar40 !== null)
              <div class="card card-custom input-field40">
                <div class="card-header d-flex justify-content-between">
                  <h4>TAMBAH INPUTAN</h4>
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
                          <input type="text" name="uang_keluar40" value="{{ number_format($DatasDiterima->uang_keluar40, 0, ',', ',') }}" placeholder="Total Uang Keluar" class="form-control uang_keluar_currency40" readonly>
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
                            <option value="transportasi" {{ $DatasDiterima->keterangan40 == 'transportasi' ? 'selected' : '' }}>TRANSPORTASI</option>
                            <option value="makan" {{ $DatasDiterima->keterangan40 == 'makan' ? 'selected' : '' }}>MAKAN</option>
                            <option value="obat-obatan" {{ $DatasDiterima->keterangan40 == 'obat-obatan' ? 'selected' : '' }}>OBAT-OBATAN</option>
                            <option value="jajan atau belanja" {{ $DatasDiterima->keterangan40 == 'jajan atau belanja' ? 'selected' : '' }}>JAJAN ATAU BELANJA</option>
                            <option value="lain-lain" {{ $DatasDiterima->keterangan40 == 'lain-lain' ? 'selected' : '' }}>LAIN-LAIN</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Bukti Struk</label>
                        <a href="{{ asset('images/' . $DatasDiterima->gambar40) }}" data-lightbox="{{ $DatasDiterima->id }}">
                          <div class="card" style="width: 8rem; align-items:center; background-color:#F5F5F5;">
                            @if ($DatasDiterima->gambar40 == null)
                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                            @else
                            <img id="image-preview" style="width: 125px; height:125px;" class="card-img-top" src="{{ asset('images/' . $DatasDiterima->gambar40) }}" alt="Preview Image">
                            @endif
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
              @endif
              <!--================== END ==================-->

            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label style=" font-weight: bold;">Total Uang Masuk</label>
                  <div class="input-group" style=" border: 2px solid #90EE90; border-radius: 8px;">
                    <input type="text" style=" font-weight: bold;" name="total_uang_masuk" value="{{ number_format($DatasDiterima->total_uang_masuk, 0, ',', ',') }}" placeholder="Masukan Keterangan" class="form-control" readonly>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label style=" font-weight: bold;">Total Uang Keluar</label>
                  <div class="input-group" style=" border: 2px solid #CD5C5C; border-radius: 8px;">
                    <input type="text" style=" font-weight: bold;" name="total_uang_keluar" value="{{ number_format($DatasDiterima->total_uang_keluar, 0, ',', ',') }}" placeholder="Masukan Keterangan" class="form-control" readonly>
                  </div>
                </div>
              </div>
            </div>


            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label style=" font-weight: bold;">Sisa Saldo</label>
                  <div class="input-group" style=" border: 2px solid #87CEFA; border-radius: 8px;">
                    <input type="text" style=" font-weight: bold;" name="sisa_saldo" value="{{ number_format($DatasDiterima->sisa_saldo, 0, ',', ',') }}" placeholder="Masukan Keterangan" class="form-control" readonly>
                  </div>
                </div>
              </div>
            </div>

            @if (Auth::user()->level == 'karyawan')
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label style=" font-weight: bold;">Catatan</label>
                  <div class="input-group" style=" border: 2px solid 	#FFA500; border-radius: 8px;">
                    <textarea type="text" name="deskripsi" placeholder="Masukan Catatan" class="form-control" readonly>{{ $DatasDiterima->deskripsi }}</textarea>
                  </div>
                </div>
              </div>
            </div>
            @else
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label style=" font-weight: bold;">Catatan</label>
                  <div class="input-group" style=" border: 2px solid 	#FFA500; border-radius: 8px;">
                    <textarea type="text" name="deskripsi" placeholder="Masukan Catatan" class="form-control">{{ $DatasDiterima->deskripsi }}</textarea>
                  </div>
                </div>
              </div>
            </div>
            @endif

          </div>
        </div>

      </div>
      <!--================== END ==================-->

      <div class="button-container">
        <button class="btn btn-primary mr-1 btn-submit" type="submit" style="width: 50%;"><i class="fa fa-paper-plane"></i> SIMPAN</button>
        <a href="{{ route('account.PerjalananDinas.index') }}" class="btn btn-info" style="width: 50%; font-size:14px; text-align:center; padding-top:10px;"><i class="fa fa-undo"></i> KEMBALI</a>
      </div>

      </form>
    </div>
  </section>
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
</script>
<!--================== END ==================-->

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
// <!-- END -->

@stop