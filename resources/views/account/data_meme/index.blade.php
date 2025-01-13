@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Data Scopus Kafe | MIS
@stop
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>DATA SCOPUS KAFE</h1>
        </div>

        <div class="section-body">
            <!--================== FILTER ==================-->
            <div class="card">
                <div class="card-header  text-right">
                    <h4><i class="fas fa-filter"></i> FILTER</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('account.gaji.searchmanager') }}" method="GET" id="searchForm">
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control rounded-pill" name="q" placeholder="Pencarian" value="{{ app('request')->input('q') }}" id="searchInput">
                                <div class="input-group-append">
                                </div>
                                @if(request()->has('q'))
                                <a href="{{ route('account.meme.index') }}" class="btn btn-danger rounded-pill ml-1">
                                    <i class="fa fa-trash mt-2"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <form action="{{ route('account.gaji.filtermanager') }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal Awal</label>
                                    <input type="date" name="tanggal_awal" value="{{ old('tanggal_awal') }}" class="form-control datepicker rounded-pill">
                                </div>
                            </div>
                            <div class="col-md-2" style="text-align: center">
                                <label style="margin-top: 38px;">S/D</label>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal Akhir</label>
                                    <input type="date" name="tanggal_akhir" value="{{ old('tanggal_akhir') }}" class="form-control datepicker rounded-pill">
                                </div>
                            </div>
                            <div class="col-md-2">
                                @if (request()->has('tanggal_awal') && request()->has('tanggal_akhir'))
                                <div class="btn-group" style="width: 100%;">
                                    <a href="{{ route('account.meme.index') }}" class="btn btn-danger rounded-pill" style="margin-top: 30px; font-size:15px;"">
                                <i class=" fa fa-trash mt-2"></i>
                                    </a>
                                </div>
                                @else
                                <button class="btn btn-info mr-1 btn-block rounded-pill" type="submit" style="margin-top: 30px; font-size:15px;" id="filterButton"><i class="fa fa-filter"></i> Filter</button>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="row">
                        <div class="col-12 mt-3">
                            <div class="form-group text-center">
                                <div class="input-group mb-3">
                                    <a href="{{ route('account.meme.create') }}" class="btn btn-primary btn-block" style="padding-top: 10px;">
                                        <i class="fa fa-plus-circle"></i> Tambah Data
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!--================== END ==================-->

            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-list"></i> DATA SCOPUS KAFE</h4>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col" rowspan="2" style="text-align: center;width: 6%">NO.</th>
                                        <th scope="col" rowspan="2" class="column-width" style="text-align: center;">SESI</th>
                                        <th scope="col" colspan="2" class="column-width" style="text-align: center;">WAKTU</th>
                                        <th scope="col" rowspan="2" class="column-width" style="text-align: center;">JUMLAH KUOTA</th>
                                        <th scope="col" rowspan="2" class="column-width" style="text-align: center;">STATUS</th>
                                        <th scope="col" rowspan="2" style="width: 15%;text-align: center">AKSI</th>
                                    </tr>
                                    <tr>
                                        <th scope="col" style="text-align: center;">MULAI</th>
                                        <th scope="col" style="text-align: center;">SELESAI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $no = 1;
                                    @endphp
                                    @foreach ($meme as $hasil)
                                    <tr>
                                        <th scope="row" style="text-align: center">{{ $no }}</th>
                                        <td class="column-width" style="text-align: center;">{{ $hasil->sesi }}</td>
                                        <td class="column-width" style="text-align: center;">
                                            {{ date('H:i', strtotime($hasil->waktu_mulai)) }}
                                        </td>
                                        <td class="column-width" style="text-align: center;">
                                            {{ date('H:i', strtotime($hasil->waktu_selesai)) }}
                                        </td>
                                        <td class="column-width" style="text-align: center;">{{ $hasil->kuota }}</td>
                                        <td class="column-width" style="text-align: center;">
                                            @if($hasil->status == 'draft')
                                            <span class="badge badge-warning">DRAFT</span>
                                            @else
                                            <span class="badge badge-success">PUBLISH</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <a style="margin-right: 5px; margin-bottom:4px; height: 30px; width: 30px;" href="{{ route('account.meme.edit', ['id' => $hasil->id]) }}" class="btn btn-sm btn-primary mt-2">
                                                <i class="fa fa-pencil-alt" style="margin-top: 6px;"></i>
                                            </a>
                                            <button style="margin-right: 5px; margin-bottom:4px; width:30px; height:30px;"
                                                onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger mt-2">
                                                <i class="fa fa-trash"></i>
                                            </button>
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
                                {{ $meme->links("vendor.pagination.bootstrap-4") }}
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

<!--================== SEARCH WITH JQUERY ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let searchInput = document.getElementById('searchInput');
        let searchForm = document.getElementById('searchForm');
        let debounceTimeout;

        searchInput.addEventListener('keyup', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(function() {
                if (searchInput.value.trim() === '') {
                    window.location.href = "{{ route('account.gaji.index') }}";
                } else {
                    searchForm.submit();
                }
            }, 500); // Adjust the debounce delay as needed
        });
    });
</script>
<!--================== END ==================-->

<!--================== SWEET ALERT DELETE ==================-->
<script>
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
                    url: "/account/meme/delete/" + id,
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
<!--================== END ==================-->

<!--================== RELOAD KETIKA DATA SUKSES ==================-->
<script>
    @if(Session::has('success'))
    // Menggunakan setTimeout untuk menunggu pesan sukses muncul sebelum melakukan refresh
    setTimeout(function() {
        window.location.reload();
    }, 1000); // Refresh halaman setelah 2 detik
    @endif
</script>
<!--================== END ==================-->
@stop