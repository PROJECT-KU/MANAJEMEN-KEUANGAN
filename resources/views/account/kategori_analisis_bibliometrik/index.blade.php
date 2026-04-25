@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Kategori Analisis Bibliometrik | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>KATEGORI ANALISIS BIBLIOMETRIK</h1>
        </div>

        <div class="section-body">

            <!--================== DATA KATEGORI ==================-->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-list"></i> DATA KATEGORI ANALISIS BIBLIOMETRIK</h4>

                    <!--================== FILTER ==================-->
                    <div class="d-flex justify-content-end align-items-center mb-3" style="gap: 10px;">

                        <!-- CREATE DATA -->
                        @if (Auth::user()->level == 'manager')
                        <a href="{{ route('account.kategori.create') }}" class="btn btn-primary btn-block rounded-pill">
                            <i class="fa fa-plus-circle"></i> TAMBAH KATEGORI
                        </a>
                        @endif
                        <!-- END -->

                        <div class="dropdown card-header-action">
                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
                                <i class="fas fa-download"></i> FILTER
                            </button>
                            <div class="dropdown-menu dropdown-menu-right p-3" style="min-width: 300px;">

                                <!-- FILTER TANGGAL -->
                                <form action="{{ route('account.ketegori.filter') }}" method="GET">
                                    <div class="form-group">
                                        <label>Tanggal Awal</label>
                                        <input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}" class="form-control">
                                    </div>

                                    <div class="form-group">
                                        <label>Tanggal Akhir</label>
                                        <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="form-control">
                                    </div>

                                    @if (request()->has('tanggal_awal') && request()->has('tanggal_akhir'))
                                    <div class="btn-group" style="width: 100%;">
                                        <button class="btn btn-info mr-1" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                                        <a href="{{ route('account.kategori.index') }}" class="btn btn-danger" style="margin-top: 30px;">
                                            <i class="fa fa-trash mt-2"></i> HAPUS
                                        </a>
                                    </div>
                                    @else
                                    <button class="btn btn-info mr-1 btn-block" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                                    @endif
                                </form>
                                <!-- END -->

                                <!-- DOWNLOAD DATA -->
                                <hr class="my-2">

                                <div class="d-flex flex-wrap gap-2">
                                    <a id="downloadExcelBtn" href="{{ route('account.ketegori.download-excel', ['tanggal_awal' => request('tanggal_awal'),'tanggal_akhir' => request('tanggal_akhir'),'q' => request('q')]) }}" class="btn btn-info" style="flex: 1 1 auto;">
                                        <i class="far fa-file-excel"></i> Unduh EXCEL
                                    </a>
                                </div>
                                <!-- END -->

                            </div>
                        </div>

                        <!-- SEARCH -->
                        <div style="max-width: 250px; width: 100%;">
                            <input type="text" id="liveSearch" class="form-control" placeholder="Pencarian..." autocomplete="off">
                        </div>
                        <!-- END -->

                    </div>
                    <!--================== END FILTER ==================-->
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col" rowspan="2" style="text-align: center;width: 6%">NO.</th>
                                        <th scope="col" rowspan="2" class="column-width" style="text-align: center;">NAMA KATEGORI</th>
                                        <th scope="col" colspan="2" class="column-width" style="text-align: center;">TANGGAL</th>
                                        <th scope="col" rowspan="2" class="column-width" style="text-align: center;">TOTAL KUOTA</th>
                                        <th scope="col" rowspan="2" class="column-width" style="text-align: center;">SISA KUOTA</th>
                                        <th scope="col" rowspan="2" class="column-width" style="text-align: center;">STATUS</th>
                                        <th scope="col" rowspan="2" style="width: 15%;text-align: center">AKSI</th>
                                    </tr>
                                    <tr>
                                        <th scope="col" class="column-width" style="text-align: center;">MULAI</th>
                                        <th scope="col" class="column-width" style="text-align: center;">SELESAI</th>
                                    </tr>
                                </thead>

                                <tbody id="bibliometrikTable">
                                    @php
                                    $no = 1;
                                    $terbayarCount = 0; // Count of terbayar records
                                    @endphp
                                    @foreach ($categories as $hasil)

                                    <tr>
                                        <th scope="row" style="text-align: center">{{ $no }}</th>
                                        <td class="column-width" style="text-align: center;">{{ strtoupper($hasil->nama) }} #{{ $hasil->nama_ke }}</td>
                                        <td class="column-width" style="text-align: center;">{{ strftime('%d %B %Y', strtotime($hasil->mulai)) }}</td>
                                        <td class="column-width" style="text-align: center;">{{ strftime('%d %B %Y', strtotime($hasil->selesai)) }}</td>
                                        <td class="column-width" style="text-align: center;">{{ $hasil->total_kuota }}</td>
                                        <td class="column-width" style="text-align: center;">{{ $hasil->sisa_kuota }}</td>
                                        <td class="column-width" style="text-align: center;">
                                            @if($hasil->status == 'active')
                                            <span class="badge badge-success">Active</span>
                                            @else
                                            <span class="badge badge-danger">Non Active</span>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.kategori.edit', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-warning">
                                                <i class="fa fa-pencil-alt"></i>
                                            </a>
                                            <button style="margin-right: 5px; margin-bottom:5px;" onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger mb-2">
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
                                {{ $categories->appends(['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate])->links("vendor.pagination.bootstrap-4") }}
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!--================== END ==================-->

        </div>
    </section>
</div>

<!--================== GET DATA SERACH UNTUK DI EXPORT KE EXCEL ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const downloadBtn = document.getElementById('downloadExcelBtn');
        const searchInput = document.getElementById('liveSearch');

        downloadBtn.addEventListener('click', function(e) {
            e.preventDefault();

            const tanggal_awal = "{{ request('tanggal_awal') }}";
            const tanggal_akhir = "{{ request('tanggal_akhir') }}";
            const q = searchInput.value;

            const url = `{{ route('account.ketegori.download-excel') }}?tanggal_awal=${tanggal_awal}&tanggal_akhir=${tanggal_akhir}&q=${encodeURIComponent(q)}`;

            window.location.href = url;
        });
    });
</script>
<!--================== END ==================-->

<!--================== LIVE SEARCH ==================-->
<script>
    let timer;

    document.getElementById('liveSearch').addEventListener('keyup', function() {
        clearTimeout(timer);
        const query = this.value;

        timer = setTimeout(() => {
            const newUrl = new URL(window.location.href);
            if (query) {
                newUrl.searchParams.set('q', query);
            } else {
                newUrl.searchParams.delete('q');
            }
            window.history.pushState({}, '', newUrl);

            // Update tombol PDF supaya ikut query terbaru
            const pdfButton = document.getElementById("pdfDownloadButton");
            if (pdfButton) {
                const pdfUrl = `{{ route('account.ketegori.download-pdf') }}?tanggal_awal=${encodeURIComponent(newUrl.searchParams.get('tanggal_awal') || '')}&tanggal_akhir=${encodeURIComponent(newUrl.searchParams.get('tanggal_akhir') || '')}&q=${encodeURIComponent(query)}`;
                pdfButton.setAttribute('href', pdfUrl);
            }

            // Fetch data tabel
            fetch(`{{ route('account.ketegori.search') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTableBody = doc.querySelector('#bibliometrikTable');
                    if (newTableBody) {
                        document.getElementById('bibliometrikTable').innerHTML = newTableBody.innerHTML;
                    }
                });
        }, 300);
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
                    url: "/account/kategori/analisis-bibliometrik/delete/" + id,
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