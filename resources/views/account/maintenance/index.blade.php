@extends('layouts.account')

@section('title')
List Pengguna | MANAGEMENT
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>MAINTENANCE</h1>
        </div>

        <div class="section-body">

            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-list"></i> LIST MAINTENANCE</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('account.pengguna.search') }}" method="GET">
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <a href="{{ route('account.maintenance.create') }}" class="btn btn-primary" style="padding-top: 10px;">
                                        <i class="fa fa-plus-circle"></i> TAMBAH
                                    </a>
                                </div>
                                <input type="text" class="form-control" name="q" placeholder="PENCARIAN" value="{{ app('request')->input('q') }}">
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
                                    <th scope="col" rowspan="2" style="text-align: center;">TITLE</th>
                                    <th scope="col" rowspan="2" style="text-align: center;">START MAINTENANCE</th>
                                    <th scope="col" rowspan="2" style="text-align: center;">END MAINTENANCE</th>
                                    <th scope="col" rowspan="2" style="text-align: center;">NOTE</th>
                                    <th scope="col" rowspan="2" style="text-align: center;">STATUS</th>
                                    <th scope="col" style="width: 10%;text-align: center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $no = 1;
                                @endphp
                                @foreach ($maintenances as $item)
                                <tr>
                                    <th scope="row" style="text-align: center">{{ $no }}</th>
                                    <td style="text-align: center;">{{ $item->title }}</td>
                                    <td style="text-align: center;">{{ date('d F Y H:i', strtotime($item->start_date)) }}</td>
                                    <td style="text-align: center;">{{ date('d F Y H:i', strtotime($item->end_date)) }}</td>
                                    <td style="text-align: center;">{{ $item->note }}</td>
                                    <td style="text-align: center;">
                                        @if ($item->status == 'aktif')
                                        <span class="badge badge-success mt-2">ACTIVE</span>
                                        @else
                                        <span class="badge badge-danger mt-2">NON-ACTIVE</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.maintenance.edit', $item->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-pencil-alt"></i>
                                        </a>
                                        <button style="margin-right: 5px; margin-bottom:5px;" class="btn btn-sm btn-danger" onclick="handleDelete({{ $item->id }})">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.pengguna.detail', $item->id) }}" class="btn btn-sm btn-warning">
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
                        <div style="text-align: center;">
                            <style>
                                @media (max-width: 767px) {
                                    .pagination {
                                        margin-left: 480px;
                                        /* Adjust the margin value as needed for mobile devices */
                                    }
                                }

                                @media (min-width: 768px) and (max-width: 991px) {
                                    .pagination {
                                        margin-left: 300px;
                                        /* Adjust the margin value as needed for iPads */
                                    }
                                }
                            </style>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- reload data ketika success -->
<script>
    @if(Session::has('success'))
    // Menggunakan setTimeout untuk menunggu pesan sukses muncul sebelum melakukan refresh
    setTimeout(function() {
        window.location.reload();
    }, 1000); // Refresh halaman setelah 2 detik
    @endif
</script>
<!-- end -->

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
                    url: "{{ route('account.maintenance.destroy', '') }}/" + id,
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