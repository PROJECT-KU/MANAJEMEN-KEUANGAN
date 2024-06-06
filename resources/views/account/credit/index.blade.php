@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Data Uang Keluar | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>DATA UANG KELUAR</h1>
        </div>

        <div class="section-body">
            <!--================== FILTER ==================-->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-filter"></i> FILTER</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('account.credit.search') }}" method="GET" id="searchForm">
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="q" placeholder="PENCARIAN" value="{{ app('request')->input('q') }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-info" id="searchButton"><i class="fa fa-search"></i> CARI</button>
                                </div>
                                @if(request()->has('q'))
                                <a href="{{ route('account.credit.index') }}" class="btn btn-danger ml-1">
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
                                    <a href="{{ route('account.credit.create') }}" class="btn btn-primary btn-block" style="padding-top: 10px;"><i class="fa fa-plus-circle"></i> TAMBAH UANG KELUAR</a>
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
                    <h4><i class="fas fa-list"></i> DATA UANG KELUAR</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col" style="text-align: center;width: 6%">NO.</th>
                                    <th scope="col">KATEGORI</th>
                                    <th scope="col">NOMINAL</th>
                                    <th scope="col">KETERANGAN</th>
                                    <th scope="col">TANGGAL</th>
                                    <th scope="col">BUKTI UANG KELUAR</th>
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
                                @foreach ($credit as $hasil)
                                <tr>
                                    <th scope="row" style="text-align: center">{{ $no }}</th>
                                    <td class="column-width" style="text-align: center;">{{ $hasil->name }}</td>
                                    <td class="column-width" style="text-align: center;">{{ rupiah($hasil->nominal) }}</td>
                                    <td class="column-width" style="text-align: center;">{{ $hasil->description }}</td>
                                    <td class="column-width" style="text-align: center;">{{ strftime('%d %B %Y %H:%M', strtotime($hasil->credit_date)) }}</td>
                                    <td class="column-width" style="text-align: center;">
                                        @if ($hasil->gambar == null)
                                        <div>
                                            <img style="width: 100px; height:100px;" src="{{ asset('images/placeholder.jpg') }}" alt="No Image" class="img-thumbnail mb-2 mt-2">
                                        </div>
                                        @else
                                        <a href="#" onclick="showImage('{{ asset('images/' . $hasil->gambar) }}')">
                                            <div>
                                                <img style="width: 100px; height:100px;" src="{{ asset('images/' . $hasil->gambar) }}" alt="Gambar Credit" class="img-thumbnail mb-2 mt-2">
                                            </div>
                                        </a>
                                        @endif
                                    </td>
                                    @if ( Auth::user()->level == 'ceo')
                                    @else
                                    <td class="text-center">
                                        <a style="margin-right: 5px; margin-bottom:5px; width: 30px; height:30px; display: inline-flex; justify-content: center; align-items: center;" href="{{ route('account.credit.edit', $hasil->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-pencil-alt"></i>
                                        </a>
                                        <button style="margin-right: 5px; margin-bottom:5px; width: 30px; height:30px; display: inline-flex; justify-content: center; align-items: center;" onClick="Delete(this.id)" class="btn btn-sm btn-danger" id="{{ $hasil->id }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                    @endif
                                </tr>
                                @php
                                $no++;
                                @endphp
                                @endforeach

                                @foreach ($gaji as $item)
                                @if ($item->status == 'terbayar' && Auth::user()->level == 'manager' || Auth::user()->level == 'ceo')
                                <tr>
                                    <th scope="row" style="text-align: center">{{ $no }}</th>
                                    <td class="column-width" style="text-align: center;">GAJI KARYAWAN</td>
                                    <td class="column-width" style="text-align: center;">{{ rupiah($item->total) }}</td>
                                    <td class="column-width" style="text-align: center;">Gaji Bulan {{ strftime('%B %Y', strtotime($item->tanggal)) }}</td>
                                    <td class="column-width" style="text-align: center;">{{ strftime('%d %B %Y %H:%M', strtotime($item->tanggal)) }}</td>
                                    <td class="column-width" style="text-align: center;">
                                        @if ($item->gambar == null)
                                        <div>
                                            <img style="width: 100px; height:100px;" src="{{ asset('images/placeholder.jpg') }}" alt="No Image" class="img-thumbnail mb-2 mt-2">
                                        </div>
                                        @else
                                        <a href="#" onclick="showImageGaji('{{ asset('images/' . $item->gambar) }}')">
                                            <div>
                                                <img style="width: 100px; height:100px;" src="{{ asset('images/' . $item->gambar) }}" alt="Gambar Credit" class="img-thumbnail mb-2 mt-2">
                                            </div>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @php
                                $no++;
                                @endphp
                                @endif
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
                            {{$credit->links("vendor.pagination.bootstrap-4")}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!--================== SWEET ALERT GAMBAR CREDIT ==================-->
<script>
    function showImage(imageUrl) {
        if (imageUrl) {
            // Jika ada gambar, tampilkan SweetAlert dengan gambar
            Swal.fire({
                title: 'Bukti Uang Keluar',
                imageUrl: imageUrl,
                showCloseButton: false, // Menghilangkan tombol close
                confirmButtonText: 'Tutup',
                didOpen: () => { // Hindari pergerakan otomatis ke atas
                    Swal.enableButtons(); // Aktifkan tombol secara manual
                }
            });
        }
    }
</script>
<!--================== END ==================-->

<!--================== SWEET ALERT GAMBAR GAJI ==================-->
<script>
    function showImageGaji(imageUrl) {
        if (imageUrl) {
            // Jika ada gambar, tampilkan SweetAlert dengan gambar
            Swal.fire({
                title: 'Bukti Pembayaran Gaji',
                imageUrl: imageUrl,
                showCloseButton: false, // Menghilangkan tombol close
                confirmButtonText: 'Tutup',
                didOpen: () => { // Hindari pergerakan otomatis ke atas
                    Swal.enableButtons(); // Aktifkan tombol secara manual
                }
            });
        }
    }
</script>
<!--================== END ==================-->

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
                    url: "/account/credit/" + id,
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