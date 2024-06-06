@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Data Kategori Uang keluar | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>DATA KATEGORI UANG KELUAR</h1>
        </div>

        <div class="section-body">
            <!--================== FILTER ==================-->
            <div class="card">
                <div class="card-header  text-right">
                    <h4><i class="fas fa-filter"></i> FILTER</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('account.categories_credit.search') }}" method="GET" id="searchForm">
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="q" placeholder="PENCARIAN" value="{{ app('request')->input('q') }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-info" id="searchButton"><i class="fa fa-search"></i> CARI</button>
                                </div>
                                @if(request()->has('q'))
                                <a href="{{ route('account.categories_credit.index') }}" class="btn btn-danger ml-1">
                                    <i class="fa fa-times-circle mt-2"></i> HAPUS PENCARIAN
                                </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if ( Auth::user()->level == 'ceo')
                    @else
                    <div class="row">
                        <div class="col-12 mt-3">
                            <div class="form-group text-center">
                                <div class="input-group mb-3">
                                    <a href="{{ route('account.categories_credit.create') }}" class="btn btn-primary btn-block" style="padding-top: 10px;"><i class="fa fa-plus-circle"></i> TAMBAH KATEGORI</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
            <!--================== END ==================-->


            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-list"></i> DATA KATEGORI UANG MASUK</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col" style="text-align: center;width: 6%">NO.</th>
                                    <th scope="col">KODE KATEGORI</th>
                                    <th scope="col">NAMA KATEGORI</th>
                                    @if ( Auth::user()->level == 'ceo')
                                    @else
                                    <th scope="col" style="width: 15%;text-align: center">AKSI</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $no = 1;
                                @endphp
                                @foreach ($categories as $hasil)
                                <tr>
                                    <th scope="row" style="text-align: center">{{ $no }}</th>
                                    <td style="text-transform:uppercase">{{ $hasil->kode }}</td>
                                    <td style="text-transform:uppercase">{{ $hasil->name }}</td>
                                    @if ( Auth::user()->level == 'ceo')
                                    @else
                                    <td class="text-center">
                                        <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.categories_credit.edit', $hasil->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-pencil-alt"></i>
                                        </a>
                                        <button style="margin-right: 5px; margin-bottom:5px;" onClick="Delete(this.id)" class="btn btn-sm btn-danger" id="{{ $hasil->id }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                    @endif
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
                            {{$categories->links("vendor.pagination.bootstrap-4")}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!--================== SWEET ALERT JIKA FIELDS PENCARIAN KOSONG ==================-->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("searchButton").addEventListener("click", function() {
            var searchInputValue = document.querySelector("input[name='q']").value.trim();

            if (searchInputValue === "") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Harap isi field pencarian terlebih dahulu!',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            } else {
                // If not empty, submit the form
                document.getElementById("searchForm").submit();
            }
        });
    });
</script>
<!--================== END ==================-->

<!--================== RELOAD DATA KETIKA SUKSES ==================-->
<script>
    @if(Session::has('success'))
    // Menggunakan setTimeout untuk menunggu pesan sukses muncul sebelum melakukan refresh
    setTimeout(function() {
        window.location.reload();
    }, 1000); // Refresh halaman setelah 2 detik
    @endif
</script>
<!--================== END ==================-->

<!--================== SWEET ALERT DELETE ==================-->
<script>
    function Delete(id) {
        var id = id;
        var token = $("meta[name='csrf-token']").attr("content");

        swal({
            title: "APAKAH KAMU YAKIN ?",
            text: "INGIN MENGHAPUS DATA INI!",
            icon: "warning",
            buttons: [
                'TIDAK',
                'YA'
            ],
            dangerMode: true,
        }).then(function(isConfirm) {
            if (isConfirm) {

                //ajax delete
                jQuery.ajax({
                    url: "/account/categories_credit/" + id,
                    data: {
                        "id": id,
                        "_token": token
                    },
                    type: 'DELETE',
                    success: function(response) {
                        if (response.status == "success") {
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
                    },
                    error: function() {
                        swal({
                            title: 'GAGAL!',
                            text: 'KATEGORI MASIH TERHUBUNG DENGAN UANG KELUAR!, silahkan hapus terlebih dahulu uang keluar!',
                            icon: 'error',
                            timer: 1000,
                            showConfirmButton: false,
                            showCancelButton: false,
                            buttons: false,
                        });
                    }
                });

            } else {
                return true;
            }
        })
    }
</script>
<!--================== END ==================-->
@stop