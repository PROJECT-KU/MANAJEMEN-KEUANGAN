@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Data Pendaftaran Scopus Kafe | MIS
@stop
@section('content')

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>DATA PENDAFTARAN SCOPUS KAFE</h1>
        </div>

        <div class="section-body">
            <!--================== FILTER ==================-->
            <div class="card">
                <div class="card-header  text-right">
                    <h4><i class="fas fa-filter"></i> FILTER</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('account.pendaftaran-scopus-kafe.search') }}" method="GET" id="searchForm">
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control rounded-pill" name="q" placeholder="PENCARIAN" value="{{ app('request')->input('q') }}" id="searchInput">
                                <div class="input-group-append">
                                </div>
                                @if(request()->has('q'))
                                <a href="{{ route('account.pendaftaran-scopus-kafe.index') }}" class="btn btn-danger rounded-pill ml-1">
                                    <i class="fa fa-trash mt-2"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <form action="{{ route('account.pendaftaran-scopus-kafe.filter') }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>TANGGAL AWAL</label>
                                    <input type="date" name="tanggal_awal" value="{{ old('tanggal_awal') }}" class="form-control datepicker rounded-pill">
                                </div>
                            </div>
                            <div class="col-md-2" style="text-align: center">
                                <label style="margin-top: 38px;">S/D</label>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>TANGGAL AKHIR</label>
                                    <input type="date" name="tanggal_akhir" value="{{ old('tanggal_akhir') }}" class="form-control datepicker rounded-pill">
                                </div>
                            </div>
                            <div class="col-md-2">
                                @if (request()->has('tanggal_awal') && request()->has('tanggal_akhir'))
                                <div class="btn-group" style="width: 100%;">
                                    <a href="{{ route('account.pendaftaran-scopus-kafe.index') }}" class="btn btn-danger rounded-pill" style="margin-top: 30px; font-size:15px;">
                                        <i class=" fa fa-trash mt-2"></i>
                                    </a>
                                </div>
                                @else
                                <button class="btn btn-info mr-1 btn-block rounded-pill" type="submit" style="margin-top: 30px; font-size:15px;" id="filterButton"><i class="fa fa-filter"></i> FILTER</button>
                                @endif
                            </div>
                        </div>
                    </form>

                </div>
            </div>
            <!--================== END ==================-->

            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-list"></i> DATA PENDAFTARAN </h4>
                    <div class="dropdown card-header-action">
                        <button href="#" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
                            <i class="fas fa-download"></i> DOWNLOAD
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="{{ route('account.laporan_gaji.download-pdf', ['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate]) }}" class="dropdown-item has-icon">
                                <i class="far fa-file-pdf"></i> PDF
                            </a>
                            <a href="{{ route('account.laporan_gaji.download-excel', ['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate]) }}" class="dropdown-item has-icon">
                                <i class="far fa-file-excel"></i> EXCEL
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col" style="text-align: center;width: 6%">NO.</th>
                                        <th scope="col" class="column-width" style="text-align: center;">ID PEMESANAN</th>
                                        <th scope="col" class="column-width" style="text-align: center;">TANGGAL RESERVASI</th>
                                        <th scope="col" class="column-width" style="text-align: center;">NAMA</th>
                                        <th scope="col" class="column-width" style="text-align: center;">TOTAL PEMBAYARAN</th>
                                        <th scope="col" class="column-width" style="text-align: center;">STATUS PEMESANAN</th>
                                        <th scope="col" style="width: 15%;text-align: center">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $no = 1;
                                    $terbayarCount = 0; // Count of terbayar records
                                    @endphp
                                    @foreach ($datas as $data)
                                    <tr>
                                        <th scope="row" style="text-align: center">{{ $no }}</th>
                                        <td class="column-width" style="text-align: center;">{{ $data->id_pemesanan }}</td>
                                        <td class="column-width" style="text-align: center;">{{ strftime('%d %B %Y', strtotime($data->tanggal_pemesanan)) }}</td>
                                        <td class="column-width" style="text-align: center;">{{ $data->nama }}</td>
                                        <td class="column-width" style="text-align: center;">Rp. {{ number_format($data->total_keseluruhan_pembayaran, 0, ',', '.') }}</td>
                                        <td class="column-width" style="text-align: center;">
                                            @if($data->status == 'menunggu verifikasi')
                                            <span class="badge badge-warning">MENUNGGU VERIFIKASI </span>
                                            @elseif($data->status == 'pembayaran diterima')
                                            <span class="badge badge-success">PEMBAYARAN DITERIMA</span>
                                            @else
                                            <span class="badge badge-danger">PEMBAYARAN DITOLAK</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <a style="margin-right: 5px; margin-bottom:4px; height: 30px; width: 30px;" href="{{ route('account.pendaftaran-scopus-kafe.edit', $data->id) }}" class="btn btn-sm btn-info mt-2">
                                                <i class="fa fa-pen" style="margin-top: 6px;"></i>
                                            </a>
                                            <button style="margin-right: 5px; margin-bottom:4px; width:30px; height:30px;" onclick="Delete('{{ $data->id }}')" class="btn btn-sm btn-danger mt-2">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>

                                    </tr>
                                    @php
                                    $no++;
                                    $terbayarCount++;
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
                                {{ $datas->appends(['tanggal_awal'=> $startDate, 'tanggal_akhir'=> $endDate])->links("vendor.pagination.bootstrap-4")}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<!--==================SEARCH WITH JQUERY==================-->
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
                }

                , 500); // Adjust the debounce delay as needed
        });
    });
</script>
<!--==================END==================-->
<!--==================SWEET ALERT JIKA FIELDS KOSONG==================-->
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
<!--==================END==================-->
<!--==================RELOAD KETIKA DATA SUKSES==================-->
<script>
    @if(Session::has('success')) // Menggunakan setTimeout untuk menunggu pesan sukses muncul sebelum melakukan refresh

    setTimeout(function() {
            window.location.reload();
        }

        , 1000); // Refresh halaman setelah 2 detik

    @endif
</script>
<!--==================END==================-->
<!--==================SWEET ALERT DELETE==================-->
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
                }

                ,
                confirm: {
                    text: "YA",
                    value: true,
                    visible: true,
                    className: "",
                    closeModal: true
                }
            }

            ,
            dangerMode: true,
        }).then(function(isConfirm) {
            if (isConfirm) {

                // ajax delete
                $.ajax({

                    url: "/account/pendaftaran-scopus-kafe/data/delete/" + id,
                    data: {
                        "_token": token,
                        "_method": "DELETE"
                    }

                    ,
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
<!--==================END==================-->
@stop