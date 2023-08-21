@extends('layouts.account')

@section('title')
Uang Masuk dan Keluar - UANGKU
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>DATA PENGGUNA</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-money-check-alt"></i> DATA PENGGUNA</h4>
        </div>

        <div class="card-body">
          <form action="{{ route('account.pengguna.search') }}" method="GET">
            <div class="form-group">
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <a href="{{ route('account.pengguna.create') }}" class="btn btn-primary" style="padding-top: 10px;">
                    <i class="fa fa-plus-circle"></i> TAMBAH
                  </a>
                </div>
                <input type="text" class="form-control" name="q" placeholder="pencarian" value="{{ app('request')->input('q') }}">
                <!-- Menggunakan app('request')->input('q') untuk mempertahankan nilai pencarian -->
                <div class="input-group-append">
                  <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search"></i> CARI
                  </button>
                </div>
                @if(request()->has('q'))
                <a href="{{ route('account.pengguna.search') }}" class="btn btn-danger">
                  <i class="fa fa-times-circle mt-2"></i> HAPUS PENCARIAN
                </a>
                @endif
                <!-- Menampilkan tombol "HAPUS PENCARIAN" hanya jika ada query parameter 'q' -->
              </div>
            </div>
          </form>


          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th scope="col" style="text-align: center;width: 6%" rowspan="2">NO.</th>
                  <th scope="col" rowspan="2" style="text-align: center;">NAMA</th>
                  <th scope="col" rowspan="2" style="text-align: center;">EMAIL</th>
                  <th scope="col" rowspan="2" style="text-align: center;">USERNAME</th>
                  <th scope="col" rowspan="2" style="text-align: center;">VERIFIKASI EMAIL</th>
                  <!--<th scope="col" rowspan="2" style="text-align: center;">TANGGAL DI BUAT</th>-->
                  <th scope="col" rowspan="2" style="text-align: center;">JENIS</th>
                  <th scope="col" rowspan="2" style="text-align: center;">LEVEL</th>
                  <th scope="col" rowspan="2" style="text-align: center;">STATUS</th>
                  <th scope="col" style="width: 10%;text-align: center">AKSI</th>
                </tr>
              </thead>
              <tbody>
                @php
                $no = 1;
                @endphp
                @foreach ($users as $item)
                <tr>
                  <th scope="row" style="text-align: center">{{ $no }}</th>
                  <td style="text-align: center;">{{ $item->full_name }}</td>
                  <td style="text-align: center;">{{ $item->email }}</td>
                  <td style="text-align: center;">{{ $item->username }}</td>
                  <td style="text-align: center;">
                    @if ($item->email_verified_at)
                    <button class="btn btn-success" disabled>Sudah Diverifikasi</button>
                    @else
                    <button class="btn btn-danger" disabled>Belum Diverifikasi</button>
                    @endif
                  </td>
                  <!--<td style="text-align: center;">
                    @if ($item->avatar)
                    <img src="{{ asset('assets/img/avatar/' . $item->avatar) }}" alt="Avatar" width="50">
                    @else
                    No Avatar
                    @endif
                  </td>-->
                  <!--<td style="text-align: center;">{{ date('d-m-Y H:i', strtotime($item->created_at)) }}</td>-->
                  <td style="text-align: center;">{{ $item->jenis }}</td>
                  <td style="text-align: center;">{{ $item->level }}</td>
                  <td style="text-align: center;">
                    @if ($item->status == 'on')
                    <button class="btn btn-success" disabled>ON</button>
                    @else
                    <button class="btn btn-danger" disabled>OFF</button>
                    @endif
                  </td>
                  <td class="text-center">
                    <a href="{{ route('account.pengguna.edit', $item->id) }}" class="btn btn-sm btn-primary">
                      <i class="fa fa-pencil-alt"></i>
                    </a>
                    <button class="btn btn-sm btn-danger" onclick="handleDelete({{ $item->id }})">
                      <i class="fa fa-trash"></i>
                    </button>
                    <a href="{{ route('account.pengguna.detail', $item->id) }}" class="btn btn-sm btn-warning">
                      <i class="fa fa-eye"></i>
                    </a>
                  </td>
                </tr>
                @php
                $no++;
                @endphp
                @endforeach
              </tbody>
            </table>
            <div style="text-align: center">
              {{$users->links("vendor.pagination.bootstrap-4")}}
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
<script>
  // delete
  function handleDelete(id) {
    var token = $("meta[name='csrf-token']").attr("content");

    swal({
      title: "APAKAH KAMU YAKIN ?",
      text: "INGIN MENGHAPUS DATA INI!",
      icon: "warning",
      buttons: ['TIDAK', 'YA'],
      dangerMode: true,
    }).then(function(isConfirm) {
      if (isConfirm) {
        // Ajax delete
        $.ajax({
          url: "{{ route('account.pengguna.destroy', '') }}/" + id,
          data: {
            "_token": token,
            "_method": "DELETE"
          },
          type: 'POST',
          success: function(response) {
            swal({
              title: 'BERHASIL!',
              text: 'DATA BERHASIL DIHAPUS!',
              icon: 'success',
              timer: 1000,
              showConfirmButton: false,
              showCancelButton: false,
              buttons: false,
            }).then(function() {
              location.reload();
            });
          }
        });
      } else {
        return true;
      }
    });
  }
</script>

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
  function Delete(id) {
    var token = $("meta[name='csrf-token']").attr("content");

    swal({
      title: "APAKAH KAMU YAKIN ?",
      text: "INGIN MENGHAPUS DATA INI!",
      icon: "warning",
      buttons: ['TIDAK', 'YA'],
      dangerMode: true,
    }).then(function(isConfirm) {
      if (isConfirm) {
        // Ajax delete
        $.ajax({
          url: "{{ route('account.pengguna.destroy', '') }}/" + id,
          data: {
            "_token": token,
            "_method": "DELETE"
          },
          type: 'POST',
          success: function(response) {
            if (response.status === "success") {
              swal({
                title: 'BERHASIL!',
                text: 'DATA BERHASIL DIHAPUS!',
                icon: 'success',
                timer: 1000,
                showConfirmButton: false,
                showCancelButton: false,
                buttons: false,
              }).then(function() {
                location.reload();
              });
            } else {
              swal({
                title: 'GAGAL!',
                text: 'DATA GAGAL DIHAPUS!',
                icon: 'error',
                timer: 1000,
                showConfirmButton: false,
                showCancelButton: false,
                buttons: false,
              }).then(function() {
                location.reload();
              });
            }
          }
        });
      } else {
        return true;
      }
    });
  }
</script>
@stop