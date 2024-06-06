@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Kategori Scopus Camp | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>KATEGORI SCOPUS CAMP</h1>
        </div>

        <div class="section-body">
            <!--================== FILTER ==================-->
            <div class="card">
                <div class="card-header  text-right">
                    <h4><i class="fas fa-filter"></i> FILTER</h4>
                    <!-- @if (Auth::user()->level == 'karyawan')
            @else
            <div class="card-header-action">
              <a href="{{ route('account.laporan_gaji.download-pdf') }}" id="generate-pdf-btn" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Download PDF</a>
            </div>
            @endif -->
                </div>

                <div class="card-body">
                    <form action="{{ route('account.ketegori.search') }}" method="GET" id="searchForm">
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="q" placeholder="PENCARIAN" value="{{ app('request')->input('q') }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-info" id="searchButton"><i class="fa fa-search"></i> CARI</button>
                                </div>
                                @if(request()->has('q'))
                                <a href="{{ route('account.kategori.index') }}" class="btn btn-danger ml-1">
                                    <i class="fa fa-times-circle mt-2"></i> HAPUS PENCARIAN
                                </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <form action="{{ route('account.ketegori.filter') }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>TANGGAL AWAL</label>
                                    <input type="text" name="tanggal_awal" value="{{ old('tanggal_awal') }}" class="form-control datepicker">
                                </div>
                            </div>
                            <div class="col-md-2" style="text-align: center">
                                <label style="margin-top: 38px;">S/D</label>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>TANGGAL AKHIR</label>
                                    <input type="text" name="tanggal_akhir" value="{{ old('tanggal_akhir') }}" class="form-control datepicker">
                                </div>
                            </div>
                            <div class="col-md-2">
                                @if (request()->has('tanggal_awal') && request()->has('tanggal_akhir'))
                                <div class="btn-group" style="width: 100%;">
                                    <button class="btn btn-info mr-1" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                                    <a href="{{ route('account.kategori.index') }}" class="btn btn-danger" style="margin-top: 30px;">
                                        <i class="fa fa-times-circle mt-2"></i> HAPUS
                                    </a>
                                </div>
                                @else
                                <button class="btn btn-info mr-1 btn-block" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if ( Auth::user()->level == 'ceo')
                    @else
                    <a href="{{ route('account.kategori.create') }}" class="btn btn-primary btn-block mt-3" style="padding-top: 10px;">
                        <i class="fa fa-plus-circle"></i> TAMBAH KATEGORI
                    </a>
                    @endif
                </div>
            </div>
            <!--================== END ==================-->

            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-list"></i> DATA KATEGORI SCOPUS CAMP</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col" rowspan="2" style="text-align: center;width: 6%">NO.</th>
                                        <th scope="col" rowspan="2" class="column-width" style="text-align: center;">SCOPUS CAMP</th>
                                        <th scope="col" rowspan="2" class="column-width" style="text-align: center;">TEMPAT</th>
                                        <th scope="col" colspan="2" class="column-width" style="text-align: center;">TANGGAL</th>
                                        <th scope="col" rowspan="2" class="column-width" style="text-align: center;">KUOTA</th>
                                        <th scope="col" rowspan="2" class="column-width" style="text-align: center;">STATUS</th>
                                        <th scope="col" rowspan="2" style="width: 15%;text-align: center">AKSI</th>
                                    </tr>
                                    <tr>
                                        <th scope="col" class="column-width" style="text-align: center;">MULAI</th>
                                        <th scope="col" class="column-width" style="text-align: center;">SELESAI</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php
                                    $no = 1;
                                    $terbayarCount = 0; // Count of terbayar records
                                    @endphp
                                    @foreach ($categories_scopuscamp as $hasil)

                                    <tr>
                                        <th scope="row" style="text-align: center">{{ $no }}</th>
                                        <td class="column-width" style="text-align: center;">{{ strtoupper($hasil->camp) }}</td>
                                        <td class="column-width" style="text-align: center;">{{ $hasil->tempat }}</td>
                                        <td class="column-width" style="text-align: center;">{{ strftime('%d %B %Y', strtotime($hasil->mulai)) }}</td>
                                        <td class="column-width" style="text-align: center;">{{ strftime('%d %B %Y', strtotime($hasil->selesai)) }}</td>
                                        <td class="column-width" style="text-align: center;">{{ $hasil->kuota }}</td>
                                        <td class="column-width" style="text-align: center;">
                                            @if($hasil->status == 'AKTIF')
                                            <span class="badge badge-success">AKTIF</i></span>
                                            @else
                                            <span class="badge badge-danger">TIDAK AKTIF</span>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.kategori.edit', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-primary">
                                                <i class="fa fa-pencil-alt"></i>
                                            </a>
                                            <button style="margin-right: 5px; margin-bottom:5px;" onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger">
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
                                {{ $categories_scopuscamp->appends(['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate])->links("vendor.pagination.bootstrap-4") }}
                            </div>

                        </div>
                    </div>
                </div>

            </div>


        </div>
    </section>
</div>

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

<!--================== SWEET ALERT JIKA FIELDS KOSONG ==================-->
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
                    url: "/account/kategori/ScopusCamp/delete/" + id,
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
@stop