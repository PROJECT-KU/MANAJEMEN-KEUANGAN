@extends('layouts.account')

@section('title')
Uang Masuk - UANGKU
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>LIST GAJI KARYAWAN</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <div class="card-header  text-right">
          <h4><i class="fas fa-money-check-alt"></i> LIST GAJI KARYAWAN</h4>
          <div class="card-header-action">

            <br>
            <i class="fas fa-info-circle info-icon"></i>
            <span class="info-text" style="font-size: 13px;">Data yang terdownload hanya data bulan saat ini</span>
            <!-- Add this line -->
          </div>
        </div>

        <div class="card-body">

          <div class="form-group">
            <div class="input-group mb-3">
              <div class="input-group-prepend">
                <a href="{{ route('account.gaji.create') }}" class="btn btn-primary" style="padding-top: 10px;"><i class="fa fa-plus-circle"></i> TAMBAH</a>
              </div>
              <input type="text" class="form-control" name="q" placeholder="pencarian">
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
                  <th scope="col" class="column-width" style="text-align: center;">ID TRANSAKSI</th>
                  <th scope="col" class="column-width" style="text-align: center;">NAMA KARYAWAN</th>
                  <th scope="col" class="column-width" style="text-align: center;">NIK</th>
                  <th scope="col" class="column-width" style="text-align: center;">NO REKENING</th>
                  <th scope="col" class="column-width" style="text-align: center;">BANK</th>
                  <th scope="col" class="column-width" style="text-align: center;">TOTAL GAJI</th>
                  <th scope="col" style="width: 15%;text-align: center">AKSI</th>
                </tr>
              </thead>
              <tbody>
                @php
                $no = 1;
                @endphp
                @foreach ($gaji as $hasil)
                <tr>
                  <th scope="row" style="text-align: center">{{ $no }}</th>
                  <td class="column-width" style="text-align: center;">{{ $hasil->id_transaksi }}</td>
                  <td class="column-width" style="text-align: center;">{{ $hasil->full_name }}</td>
                  <td class="column-width" style="text-align: center;">{{ $hasil->nik }}</td>
                  <td class="column-width" style="text-align: center;">{{ $hasil->norek }}</td>
                  <td class="column-width" style="text-align: center;">{{ $hasil->bank }}</td>
                  <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->total, 0, ',', '.') }}</td>
                  <td class="text-center">
                    <a href="{{ route('account.gaji.edit', $hasil->id) }}" class="btn btn-sm btn-primary">
                      <i class="fa fa-pencil-alt"></i>
                    </a>
                    <button onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger">
                      <i class="fa fa-trash"></i>
                    </button>
                    <a href="{{ route('account.gaji.detail', $hasil->id) }}" class="btn btn-sm btn-warning">
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
              {{$gaji->links("vendor.pagination.bootstrap-4")}}
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
          url: "/account/gaji/" + id,
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