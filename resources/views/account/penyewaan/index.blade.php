@extends('layouts.account')

@section('title')
Uang Masuk - UANGKU
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>LIST PENYEWAAN</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-money-check-alt"></i> LIST PENYEWAAN</h4>
        </div>

        <div class="card-body">
          <form action="{{ route('account.penyewaan.search') }}" method="GET">
            <div class="form-group">
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <a href="{{ route('account.penyewaan.create') }}" class="btn btn-primary" style="padding-top: 10px;"><i class="fa fa-plus-circle"></i> TAMBAH</a>
                </div>
                <input type="text" class="form-control" name="q" placeholder="cari berdasarkan keterangan">
                <div class="input-group-append">
                  <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> CARI
                  </button>
                </div>
              </div>
            </div>
          </form>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th scope="col" style="text-align: center;width: 6%">NO.</th>
                  <th scope="col">KENDARAAN</th>
                  <th scope="col">NAMA PENYEWA</th>
                  <th scope="col">NO TELP</th>
                  <th scope="col">ALAMAT</th>
                  <th scope="col">IDENTITAS</th>
                  <th scope="col" style="width: 15%;text-align: center">AKSI</th>
                </tr>
              </thead>
              <tbody>
                @php
                $no = 1;
                @endphp
                @foreach ($penyewaan as $hasil)
                <tr>
                  <th scope="row" style="text-align: center">{{ $no }}</th>
                  <td>{{ $hasil->nama_barang }}</td>
                  <td>{{ $hasil->nama }}</td>
                  <td>{{ $hasil->telp }}</td>
                  <td>{{ $hasil->alamat }}</td>
                  <td>{{ $hasil->identitas }}</td>
                  <td class="text-center">
                    <a href="{{ route('account.penyewaan.edit', $hasil->id) }}" class="btn btn-sm btn-primary">
                      <i class="fa fa-pencil-alt"></i>
                    </a>
                    <button onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger">
                      <i class="fa fa-trash"></i>
                    </button>
                    <a href="{{ route('account.penyewaan.detail', $hasil->id) }}" class="btn btn-sm btn-warning">
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
              {{$penyewaan->links("vendor.pagination.bootstrap-4")}}
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<script>
  /**
   * Sweet alert
   */
  @if($message = Session::get('success'))
  swal({
    type: "success",
    icon: "success",
    title: "BERHASIL!",
    text: "{{ $message }}",
    timer: 1500,
    showConfirmButton: false,
    showCancelButton: false,
    buttons: false,
  });
  @elseif($message = Session::get('error'))
  swal({
    type: "error",
    icon: "error",
    title: "GAGAL!",
    text: "{{ $message }}",
    timer: 1500,
    showConfirmButton: false,
    showCancelButton: false,
    buttons: false,
  });
  @endif

  // delete
  // delete
  function Delete(id) {
    var token = $("meta[name='csrf-token']").attr("content");

    swal({
      title: "APAKAH KAMU YAKIN?",
      text: "INGIN MENGHAPUS DATA INI!",
      icon: "warning",
      buttons: ['TIDAK', 'YA'],
      dangerMode: true,
    }).then(function(isConfirm) {
      if (isConfirm) {
        // ajax delete
        $.ajax({
          url: "/account/penyewaan/" + id,
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
                showConfirmButton: false,
                showCancelButton: false,
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
    })
  }
</script>

@stop