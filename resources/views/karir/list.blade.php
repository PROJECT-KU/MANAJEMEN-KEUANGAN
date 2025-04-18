@extends('layouts.account')
@extends('layouts.loader')


@section('title')
Data Karir | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>DATA KARIR</h1>
        </div>

        <div class="section-body">

            <!--================== FILTER ==================-->
            <div class="card">
                <div class="card-header  text-right">
                    <h4><i class="fas fa-filter"></i> FILTER</h4>
                </div>

                <div class="card-body">

                    <!--====== SEARCH ======-->
                    <form action="{{ route('karir.search') }}" method="GET" id="searchForm">
                        <div class="form-group position-relative">
                            <div class="input-group">

                                <input type="text" class="form-control rounded-pill" name="q" placeholder="PENCARIAN"
                                    value="{{ app('request')->input('q') }}"
                                    style="height: 45px; padding-right: 110px; border-right: 0;">

                                <div class="position-absolute d-flex align-items-center"
                                    style="right: 10px; height: 45px; z-index: 10; border-radius: 40px; padding-left: 5px;">

                                    <button type="submit" class="btn btn-info rounded-pill"
                                        style="height: 40px; display: flex; align-items: center;">
                                        <i class="fa fa-search"></i>
                                    </button>

                                    @if(request()->has('q'))
                                    <a href="{{ route('karir.list') }}" class="btn btn-danger rounded-pill ml-1"
                                        style="height: 40px; display: flex; align-items: center;">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                    <!--====== END ======-->

                    <!--====== FILTER ======-->
                    <form action="{{ route('karir.filter') }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>TANGGAL AWAL</label>
                                    <input type="date" name="tanggal_awal" value="{{ old('tanggal_awal') }}" class="form-control datepicker">
                                </div>
                            </div>
                            <div class="col-md-2" style="text-align: center">
                                <label style="margin-top: 38px;">S/D</label>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>TANGGAL AKHIR</label>
                                    <input type="date" name="tanggal_akhir" value="{{ old('tanggal_akhir') }}" class="form-control datepicker">
                                </div>
                            </div>
                            <div class="col-md-2">
                                @if (request()->has('tanggal_awal') && request()->has('tanggal_akhir'))
                                <div class="btn-group" style="width: 100%;">
                                    <button class="btn btn-info mr-1" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                                    <a href="{{ route('karir.list') }}" class="btn btn-danger" style="margin-top: 30px;">
                                        <i class="fa fa-trash mt-2"></i> HAPUS
                                    </a>
                                </div>
                                @else
                                <button class="btn btn-info mr-1 btn-block" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                                @endif
                            </div>
                        </div>
                    </form>
                    <!--====== END ======-->

                </div>
            </div>
            <!--================== END ==================-->

            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-list"></i> DATA KARIR</h4>
                    <div class="card-header-action">
                        <a href="{{ route('account.laporan_gaji.download-pdf', ['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate]) }}" class="btn btn-primary">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </a>
                    </div>
                </div>
                <!-- <div class="card-header">
                        <p style="margin-top: -3px; font-size: 15px"><strong>Periode
                                @if ($startDate && $endDate)
                                {{ date('d F Y', strtotime($startDate)) }} - {{ date('d F Y', strtotime($endDate)) }}
                                @else
                                {{ date('F Y') }}
                                @endif
                            </strong>
                        </p>
                    </div> -->
                <div class="card-body">
                    <div class="table-responsive">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col" style="text-align: center;width: 6%">NO.</th>
                                        <th scope="col" class="column-width" style="text-align: center;">NAMA</th>
                                        <th scope="col" class="column-width" style="text-align: center;">NO TELP</th>
                                        <th scope="col" class="column-width" style="text-align: center;">EMAIL</th>
                                        <th scope="col" class="column-width" style="text-align: center;">POSISI</th>
                                        <th scope="col" class="column-width" style="text-align: center;">PENDIDIKAN</th>
                                        <th scope="col" class="column-width" style="text-align: center;">STATUS</th>
                                        <th scope="col" style="width: 15%;text-align: center">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $no = 1;
                                    $terbayarCount = 0; // Count of terbayar records
                                    @endphp
                                    @foreach ($karir as $hasil)
                                    <tr>
                                        <th scope="row" style="text-align: center">{{ $no }}</th>
                                        <td class="column-width" style="text-align: center;">{{ $hasil->nama }}</td>
                                        <td class="column-width" style="text-align: center;">{{ $hasil->telp }}</td>
                                        <td class="column-width" style="text-align: center;">{{ $hasil->email }}</td>
                                        <td class="column-width" style="text-align: center;">{{ $hasil->posisi }}</td>
                                        <td class="column-width" style="text-align: center;">{{ $hasil->pendidikan }}</td>
                                        <td class="column-width" style="text-align: center;">
                                            @if ($hasil->status == 'Interview')
                                            <span class="badge badge-warning mt-2">Interview</span>
                                            @elseif ($hasil->status == 'Diterima')
                                            <span class="badge badge-success">DiTerima</span>
                                            @elseif ($hasil->status == 'Ditolak')
                                            <span class="badge badge-danger">DiTolak</span>
                                            @else
                                            <span class="badge badge-info">DiProses</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('karir.edit', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-primary">
                                                <i class="fa fa-pencil-alt"></i>
                                            </a>
                                            <button style="margin-right: 5px; margin-bottom:5px;" onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger">
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
                                {{ $karir->appends(['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate])->links("vendor.pagination.bootstrap-4") }}
                            </div>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>
</div>

<!--================== SWEET ALERT JIKA BELUM ADA KARYAWAN YANG PRESENSI PADA BULAN INI ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listener to the button
        document.getElementById('tambahGajiBtn').addEventListener('click', function(e) {
            e.preventDefault();

            // Display SweetAlert based on the condition

            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Belum Ada Karyawan Yang Presensi!',
            });

        });
    });
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
                    url: "/account/karir/" + id,
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