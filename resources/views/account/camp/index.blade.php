@extends('layouts.account')
@extends('layouts.loader')


@section('title')
Data Laporan Camp | MIS
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>DATA LAPORAN CAMP</h1>
    </div>

    <div class="section-body">
      <!--================== FILTER ==================-->
      <div class="card">
        <div class="card-header  text-right">
          <h4><i class="fas fa-filter"></i> FILTER</h4>
        </div>

        <div class="card-body">
          <form action="{{ route('account.camp.search') }}" method="GET" id="searchForm">
            <div class="form-group position-relative">
              <div class="input-group">
                <!-- Input Pencarian -->
                <input type="text" class="form-control rounded-pill" name="q" placeholder="PENCARIAN"
                  value="{{ app('request')->input('q') }}"
                  style="height: 45px; padding-right: 110px; border-right: 0;">

                <!-- Tombol di dalam Input -->
                <div class="position-absolute d-flex align-items-center"
                  style="right: 10px; height: 45px; z-index: 10; border-radius: 40px; padding-left: 5px;">

                  <button type="submit" class="btn btn-info rounded-pill"
                    style="height: 40px; display: flex; align-items: center;">
                    <i class="fa fa-search"></i>
                  </button>

                  @if(request()->has('q'))
                  <a href="{{ route('account.camp.index') }}" class="btn btn-danger rounded-pill ml-1"
                    style="height: 40px; display: flex; align-items: center;">
                    <i class="fa fa-trash"></i>
                  </a>
                  @endif
                </div>
              </div>
            </div>
          </form>

          <form action="{{ route('account.camp.filter') }}" method="GET">
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
                  <input type="date" name="tanggal_akhir" value="{{ old('tanggal_kahir') }}" class="form-control datepicker">
                </div>
              </div>
              <div class="col-md-2">
                @if (request()->has('tanggal_awal') && request()->has('tanggal_akhir'))
                <div class="btn-group" style="width: 100%;">
                  <button class="btn btn-info mr-1" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                  <a href="{{ route('account.camp.index') }}" class="btn btn-danger" style="margin-top: 30px;">
                    <i class="fa fa-trash mt-2"></i> HAPUS
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
          <div class="row">
            <div class="col-12 mt-3">
              <div class="form-group text-center">
                <div class="input-group mb-3">
                  <a href="{{ route('account.camp.create') }}" class="btn btn-primary btn-block rounded-pill" style="padding-top: 10px;"><i class="fa fa-plus-circle"></i> TAMBAH LAPORAN CAMP</a>
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
          <h4><i class="fas fa-list"></i> DATA LAPORAN CAMP</h4>
          <div class="dropdown card-header-action">
            <button href="#" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
              <i class="fas fa-download"></i> DOWNLOAD
            </button>
            <div class="dropdown-menu dropdown-menu-right">
              <a href="{{ route('account.laporan_camp.download-pdf', ['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate]) }}" class="dropdown-item has-icon">
                <i class="far fa-file-pdf"></i> PDF
              </a>
              <a href="{{ route('account.laporan_camp.download-excel', ['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate]) }}" class="dropdown-item has-icon">
                <i class="far fa-file-excel"></i> EXCEL
              </a>
            </div>
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
                    <th scope="col" class="column-width" style="text-align: center;">ID TRANSAKSI</th>
                    <th scope="col" class="column-width" style="text-align: center;">NAMA CAMP</th>
                    <th scope="col" class="column-width" style="text-align: center;">TOTAL UANG MASUK</th>
                    <th scope="col" class="column-width" style="text-align: center;">TOTAL PENGELUARAN</th>
                    <th scope="col" class="column-width" style="text-align: center;">KEUNTUNGAN</th>
                    <th scope="col" class="column-width" style="text-align: center;">PERSENTASE KEUNTUNGAN</th>
                    <th scope="col" class="column-width" style="text-align: center;">TANGGAL CAMP</th>
                    <th scope="col" class="column-width" style="text-align: center;">STATUS</th>
                    <th scope="col" style="width: 15%;text-align: center">AKSI</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $no = 1;
                  $terbayarCount = 0; // Count of terbayar records
                  @endphp
                  @foreach ($camp as $hasil)

                  <tr>
                    <th scope="row" style="text-align: center">{{ $no }}</th>
                    <td class="column-width" style="text-align: center; text-transform:uppercase;">{{ $hasil->id_transaksi }}</td>
                    <td class="column-width" style="text-align: center; text-transform:uppercase;">{{ $hasil->title }} #{{ $hasil->camp_ke }}</td>
                    <td class=" column-width" style="text-align: center;">Rp. {{ number_format($hasil->total_uang_masuk, 0, ',', '.') }}</td>
                    <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->total, 0, ',', '.') }}</td>
                    <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->keuntungan, 0, ',', '.') }}</td>
                    <td class="column-width" style="text-align: center;">
                      {{ rtrim(rtrim(number_format($hasil->persentase_keuntungan, 1, ',', '.'), '0'), ',') }}%
                    </td>
                    <td class="column-width" style="text-align: center; width:200px">
                      {{ strftime('%d %B %Y', strtotime($hasil->tanggal)) }} <br>
                      s/d<br>
                      {{ strftime('%d %B %Y', strtotime($hasil->tanggal_akhir)) }}
                    </td>
                    <td class="column-width" style="text-align: center;">
                      @if($hasil->status == 'pending')
                      <span class="badge badge-warning">Pending</span>
                      @else
                      <span class="badge badge-success">Terbayar</span>
                      @endif
                    </td>

                    @if ( Auth::user()->level == 'ceo')
                    <td class="text-center">
                      <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.camp.detail', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-info mt-2">
                        <i class="fa fa-eye"></i>
                      </a>
                      <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.laporan_Camp.Slip-Camp', $hasil->id) }}" class="btn btn-sm btn-info mt-2 mb-2">
                        <i class="fa fa-download"></i>
                      </a>
                    </td>
                    @else
                    <td class="text-center">
                      <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.camp.edit', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-warning mt-2">
                        <i class="fa fa-pencil-alt"></i>
                      </a>
                      <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.camp.detail', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-info mt-2">
                        <i class="fa fa-eye"></i>
                      </a>
                      <button style="margin-right: 5px; margin-bottom:5px;" onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger mt-2">
                        <i class="fa fa-trash"></i>
                      </button>
                      <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.laporan_Camp.Slip-Camp', $hasil->id) }}" class="btn btn-sm btn-info mt-2 mb-2">
                        <i class="fa fa-download"></i>
                      </a>
                    </td>
                    @endif
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
                {{ $camp->appends(['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate])->links("vendor.pagination.bootstrap-4") }}
              </div>

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
          url: "/account/camp/" + id,
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