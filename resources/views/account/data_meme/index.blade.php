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
                                {{ $meme->appends(['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate])->links("vendor.pagination.bootstrap-4") }}
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

<!--================== SWEET ALERT ==================-->
<script>
    // Function to show SweetAlert messages
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success-create'))
        Swal.fire({
            icon: 'success',
            showConfirmButton: false,
            timer: 3000,
            html: `
                <h2>Berhasil!</h2>
                <p>Data Meme Berhasil Disimpan!</p>
                <div style="position: relative; width: 100%; background: #eee; height: 10px; margin-top: 10px;">
                    <div id="progress-bar" style="position: absolute; background: green; height: 10px; width: 0%; transition: width 3s;"></div>
                </div>
            `,
            didOpen: () => {
                var progressBar = document.getElementById('progress-bar');
                progressBar.style.width = '100%'; // Animate the progress bar to full width
            },
        }).then(() => {
            location.reload(); // Automatically refresh the page after the alert
        });
        @endif

        @if(session('error-create'))
        Swal.fire({
            icon: 'error',
            showConfirmButton: false,
            timer: 3000,
            html: `
                <h2>Gagal!</h2>
                <p>Data Meme Gagal Disimpan!</p>
                <div style="position: relative; width: 100%; background: #eee; height: 10px; margin-top: 10px;">
                    <div id="progress-bar" style="position: absolute; background: red; height: 10px; width: 0%; transition: width 3s;"></div>
                </div>
            `,
            didOpen: () => {
                var progressBar = document.getElementById('progress-bar');
                progressBar.style.width = '100%'; // Animate the progress bar to full width
            },
        }).then(() => {
            location.reload(); // Automatically refresh the page after the alert
        });
        @endif
    });
</script>
<!--================== END ==================-->

<!--================== SWEET ALERT DELETE ==================-->
<script>
    function Delete(id) {
        var token = $("meta[name='csrf-token']").attr("content");

        swal({
            title: "APAKAH KAMU YAKIN?",
            text: "INGIN MENGHAPUS DATA INI SECARA PERMANEN!",
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
                        if (response.statusdatadeleted === "success") {
                            // Custom SweetAlert with a progress bar
                            swal({
                                title: 'BERHASIL!',
                                text: 'Data berhasil di hapus',
                                content: {
                                    element: "div",
                                    attributes: {
                                        innerHTML: `
                                        <div style="position: relative; width: 100%; background: #eee; height: 10px;">
                                            <div id="progress-bar" style="position: absolute; background: green; height: 10px; width: 0%;"></div>
                                        </div>
                                    `
                                    }
                                },
                                icon: 'success',
                                buttons: false,
                                closeOnClickOutside: false,
                            });

                            // Animate the progress bar over 3 seconds
                            let progress = 0;
                            let interval = setInterval(function() {
                                progress += 1;
                                document.getElementById("progress-bar").style.width = progress + "%";
                                if (progress >= 100) {
                                    clearInterval(interval);
                                    location.reload(); // Reload page after the progress reaches 100%
                                }
                            }, 30); // 30ms * 100 iterations = 3 seconds
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
                    },
                    error: function(xhr, status, error) {
                        // Handle server errors or unexpected responses
                        swal({
                            title: 'GAGAL!',
                            text: 'Terjadi kesalahan saat menghapus data.',
                            icon: 'error',
                            buttons: false,
                            timer: 2000,
                        });
                    }
                });
            }
        });
    }
</script>
<!--================== END ==================-->
@stop