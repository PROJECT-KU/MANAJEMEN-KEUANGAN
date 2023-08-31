@extends('layouts.account')

@section('title')
List Presensi Karyawan | MANAGEMENT
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div id="realtime-container">
      <div class="section-header">
        <h1>PRESENSI KARYAWAN</h1>
      </div>

      <div class="section-body">

        <div class="card">
          <div class="card-header  text-right">
            <h4><i class="fas fa-list"></i> LIST PRESENSI KARYAWAN</h4>
            @if (Auth::user()->level == 'karyawan')
            @else
            <!--<div class="card-header-action">
            <a href="{{ route('account.laporan_gaji.download-pdf') }}" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Download PDF</a>
          </div>-->
            @endif
          </div>

          <div class="card-body">
            <form action="{{ route('account.presensi.search') }}" method="GET">
              <div class="form-group">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <a href="{{ route('account.presensi.create') }}" class="btn btn-primary" style="padding-top: 10px;"><i class="fa fa-plus-circle"></i> TAMBAH</a>
                  </div>
                  <input type="text" class="form-control" name="q" placeholder="PENCARIAN" value="{{ app('request')->input('q') }}">
                  <div class="input-group-append">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> CARI
                    </button>
                  </div>
                  @if(request()->has('q'))
                  <a href="{{ route('account.presensi.search') }}" class="btn btn-danger">
                    <i class="fa fa-times-circle mt-2"></i> HAPUS PENCARIAN
                  </a>
                  @endif
                </div>
              </div>
            </form>

            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th scope="col" style="text-align: center;width: 6%">NO.</th>
                    <th scope="col" class="column-width" style="text-align: center;">NAMA KARYAWAN</th>
                    <th scope="col" class="column-width" style="text-align: center;">TANGGAL PRESENSI</th>
                    <th scope="col" class="column-width" style="text-align: center;">STATUS PRESENSI</th>
                    <th scope="col" class="column-width" style="text-align: center;">BUKTI PRESENSI</th>
                    <th scope="col" style="width: 15%;text-align: center">AKSI</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $no = 1;
                  @endphp
                  @foreach ($presensi as $hasil)
                  <tr>
                    <th scope="row" style="text-align: center">{{ $no }}</th>
                    <td class="column-width" style="text-align: center;">{{ $hasil->full_name }}</td>
                    <td class="column-width" style="text-align: center;">{{ date('d-m-Y H:i', strtotime($hasil->created_at)) }}</td>
                    <td class="column-width" style="text-align: center;">
                      @if ($hasil->status == 'hadir')
                      <button type="button" class="btn btn-success">HADIR</button>
                      @elseif ($hasil->status == 'remote')
                      <button type="button" class="btn btn-info">REMOTE</button>
                      @elseif ($hasil->status == 'izin')
                      <button type="button" class="btn btn-warning">IZIN</button>
                      @elseif ($hasil->status == 'dinas luar kota')
                      <button type="button" class="btn btn-info">DINAS LUAR KOTA</button>
                      @elseif ($hasil->status == 'lembur')
                      <button type="button" class="btn btn-primary">LEMBUR</button>
                      @elseif ($hasil->status == 'cuti')
                      <button type="button" class="btn btn-warning">CUII</button>
                      @elseif ($hasil->status == 'terlambat')
                      <button type="button" class="btn btn-danger">TERLAMBAT</button>
                      @elseif ($hasil->status == 'pulang')
                      <button type="button" class="btn btn-danger">PULANG</button>
                      @endif
                    </td>
                    <td class="column-width" style="text-align: center;">
                      <a href="{{ asset('images/' . $hasil->gambar) }}" data-lightbox="{{ $hasil->id }}">
                        <div class="thumbnail-circle">
                          <img style="width: 100px; height:100px;" src="{{ asset('images/' . $hasil->gambar) }}" alt="Gambar Presensi" class="img-thumbnail rounded-circle">
                        </div>
                      </a>
                    </td>
                    @if (Auth::user()->level == 'karyawan')
                    <td class="text-center">
                      <a href="{{ route('account.presensi.detail', $hasil->id) }}" class="btn btn-sm btn-warning">
                        <i class="fa fa-eye"></i>
                      </a>
                    </td>
                    @else
                    <td class="text-center">
                      <a href="{{ route('account.presensi.edit', $hasil->id) }}" class="btn btn-sm btn-primary">
                        <i class="fa fa-pencil-alt"></i>
                      </a>
                      <button onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger">
                        <i class="fa fa-trash"></i>
                      </button>
                      <a href="{{ route('account.presensi.detail', $hasil->id) }}" class="btn btn-sm btn-warning">
                        <i class="fa fa-eye"></i>
                      </a>
                    </td>
                    @endif
                  </tr>
                  @php
                  $no++;
                  @endphp
                  @endforeach
                </tbody>
              </table>
              <div style="text-align: center">
                {{$presensi->links("vendor.pagination.bootstrap-4")}}
              </div>

            </div>

          </div>
        </div>
      </div>
    </div>
  </section>
</div>


<script>
  //@if($message = Session::get('success'))
  //swal({
  //  type: "success",
  //  icon: "success",
  //  title: "BERHASIL!",
  //  text: "{{ $message }}",
  //  timer: 1500,
  //  showConfirmButton: false,
  //  showCancelButton: false,
  //  buttons: false,
  //});
  //@elseif($message = Session::get('error'))
  //swal({
  //  type: "error",
  //  icon: "error",
  //  title: "GAGAL!",
  //  text: "{{ $message }}",
  //  timer: 1500,
  //  showConfirmButton: false,
  //  showCancelButton: false,
  //  buttons: false,
  //});
  //@endif

  // delete
  // delete
  function Delete(id) {
    var token = $("meta[name='csrf-token']").attr("content");

    swal({
      title: "APAKAH KAMU YAKIN?",
      text: "INGIN MENGHAPUS DATA INI!",
      icon: "warning",
      buttons: {
        cancel: {
          text: "TIDAK",
          value: null,
          visible: true,
          className: "",
          closeModal: true,
        },
        confirm: {
          text: "YA",
          value: true,
          visible: true,
          className: "",
          closeModal: true
        }
      },
      dangerMode: true,
    }).then(function(isConfirm) {
      if (isConfirm) {
        // ajax delete
        $.ajax({
          url: "/account/presensi/" + id,
          data: {
            "_token": token,
            "_method": "DELETE"
          },
          type: 'POST',
          success: function(response) {
            if (response.status === "success") {
              swal({
                title: 'BERHASIL!',
                text: response.message,
                icon: 'success',
                timer: 1000,
                buttons: false,
              }).then(function() {
                location.reload();
              });
            } else {
              swal({
                title: 'GAGAL!',
                text: response.message,
                icon: 'error',
                timer: 1000,
                buttons: false,
              }).then(function() {
                location.reload();
              });
            }
          }
        });
      }
    });
  }
</script>

@stop