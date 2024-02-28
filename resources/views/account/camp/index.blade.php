@extends('layouts.account')

@section('title')
List aporan Camp | MANAGEMENT
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>LAPORAN CAMP</h1>
    </div>

    <div class="section-body">

      <!--================== MAINTENANCE ==================-->
      @if (!$maintenances->isEmpty())
      @foreach($maintenances as $maintenance)
      @if ($maintenance->status === 'aktif' && ($maintenance->end_date !== null && now() <= Carbon\Carbon::parse($maintenance->end_date)->endOfDay()))
        <div class="alert alert-danger" role="alert" style="text-align: center;">
          <b style="font-size: 25px; text-transform:uppercase">INFORMASI!</b><br>
          <!-- <img style="width: 100px; height:100px;" src="{{ asset('images/' . $maintenance->gambar) }}" alt="Gambar Presensi" class="img-thumbnail"> -->
          <p style="font-size: 20px;" class="mt-2">{{ $maintenance->note }}</p>
          <p style="font-size: 15px;">Dari Tanggal {{ \Carbon\Carbon::parse($maintenance->start_date)->isoFormat('D MMMM YYYY HH:mm') }} - {{ \Carbon\Carbon::parse($maintenance->end_date)->isoFormat('D MMMM YYYY HH:mm') }}</p>
        </div>
        @endif
        @endforeach
        @endif
        <!--================== END ==================-->

        <!--================== FILTER ==================-->
        <div class="card">
          <div class="card-header  text-right">
            <h4><i class="fas fa-filter"></i> FILTER LAPORAN CAMP</h4>
          </div>

          <div class="card-body">
            <form action="{{ route('account.camp.search') }}" method="GET" id="searchForm">
              <div class="form-group">
                <div class="input-group mb-3">
                  <!-- <div class="input-group-prepend">
                    <a href="{{ route('account.pengguna.create') }}" class="btn btn-primary" style="padding-top: 10px;">
                      <i class="fa fa-plus-circle"></i> TAMBAH
                    </a>
                  </div> -->
                  <input type="text" class="form-control" name="q" placeholder="PENCARIAN" value="{{ app('request')->input('q') }}">
                  <div class="input-group-append">
                    <button type="button" class="btn btn-info" id="searchButton"><i class="fa fa-search"></i> CARI</button>
                  </div>
                  @if(request()->has('q'))
                  <a href="{{ route('account.camp.index') }}" class="btn btn-danger ml-1">
                    <i class="fa fa-times-circle mt-2"></i> HAPUS PENCARIAN
                  </a>
                  @endif
                </div>
              </div>
            </form>

            <form action="{{ route('account.camp.filter') }}" method="GET">
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
                    <input type="text" name="tanggal_akhir" value="{{ old('tanggal_kahir') }}" class="form-control datepicker">
                  </div>
                </div>
                <div class="col-md-2">
                  @if (request()->has('tanggal_awal') && request()->has('tanggal_akhir'))
                  <div class="btn-group" style="width: 100%;">
                    <button class="btn btn-info mr-1" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                    <a href="{{ route('account.camp.index') }}" class="btn btn-danger" style="margin-top: 30px;">
                      <i class="fa fa-times-circle mt-2"></i> HAPUS
                    </a>
                  </div>
                  @else
                  <button class="btn btn-info mr-1 btn-block" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                  @endif
                </div>
              </div>
            </form>

            <div class="row">
              <div class="col-12 mt-3">
                <div class="form-group text-center">
                  <div class="input-group mb-3">
                    <a href="{{ route('account.camp.create') }}" class="btn btn-primary btn-block" style="padding-top: 10px;"><i class="fa fa-plus-circle"></i> TAMBAH LAPORAN CAMP</a>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
        <!--================== END ==================-->

        <div class="card">
          <div class="card-header">
            <h4><i class="fas fa-list"></i> LIST LAPORAN CAMP</h4>
            <div class="card-header-action">
              <a href="{{ route('account.laporan_camp.download-pdf', ['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate]) }}" class="btn btn-primary">
                <i class="fas fa-file-pdf"></i> Download PDF
              </a>
            </div>
          </div>
          <div class="card-header">
            <p style="margin-top: -3px; font-size: 15px"><strong>Periode
                @if ($startDate && $endDate)
                {{ date('d F Y', strtotime($startDate)) }} - {{ date('d F Y', strtotime($endDate)) }}
                @else
                {{ date('F Y') }}
                @endif
              </strong>
            </p>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th scope="col" style="text-align: center;width: 6%">NO.</th>
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
                      <td class="column-width" style="text-align: center; text-transform:uppercase;">{{ $hasil->title }} #{{ $hasil->camp_ke }}</td>
                      <td class=" column-width" style="text-align: center;">Rp. {{ number_format($hasil->total_uang_masuk, 0, ',', '.') }}</td>
                      <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->total, 0, ',', '.') }}</td>
                      <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->keuntungan, 0, ',', '.') }}</td>
                      <td class="column-width" style="text-align: center;">{{ number_format($hasil->persentase_keuntungan, 0, ',', '.') }}%</td>
                      <td class="column-width" style="text-align: center; width:200px">
                        {{ strftime('%d %B %Y', strtotime($hasil->tanggal)) }} <br>
                        s/d<br>
                        {{ strftime('%d %B %Y', strtotime($hasil->tanggal_akhir)) }}
                      </td>
                      <td class="column-width" style="text-align: center;">
                        @if($hasil->status == 'pending')
                        <span class="badge badge-warning"><i class="fas fa-hourglass-half"></i></span>
                        @else
                        <span class="badge badge-success"><i class="fas fa-check-circle"></i></span>
                        @endif
                      </td>
                      <td class="text-center">
                        <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.camp.edit', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-primary">
                          <i class="fa fa-pencil-alt"></i>
                        </a>
                        <button style="margin-right: 5px; margin-bottom:5px;" onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger">
                          <i class="fa fa-trash"></i>
                        </button>
                        <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.camp.detail', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-warning">
                          <i class="fa fa-eye"></i>
                        </a>
                        <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.laporan_Camp.Slip-Camp', $hasil->id) }}" class="btn btn-sm btn-info">
                          <i class="fa fa-download"></i>
                        </a>

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