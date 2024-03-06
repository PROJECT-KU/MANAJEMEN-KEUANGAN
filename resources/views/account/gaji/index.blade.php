@extends('layouts.account')

@section('title')
Data Gaji Karyawan | MIS
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>DATA GAJI KARYAWAN</h1>
    </div>

    <div class="section-body">

      <!--================== MAINTENANCE ==================-->
      @if (!$maintenances->isEmpty())
      @foreach($maintenances as $maintenance)
      @if ($maintenance->status === 'aktif' && (now() <= Carbon\Carbon::parse($maintenance->end_date)->endOfDay()))
        <div class="alert alert-danger" role="alert" style="text-align: center;">
          <b style="font-size: 25px; text-transform:uppercase">{{ $maintenance->title }}</b><br>
          <!-- <img style="width: 100px; height:100px;" src="{{ asset('images/' . $maintenance->gambar) }}" alt="Gambar Presensi" class="img-thumbnail"> -->
          <p style="font-size: 20px;" class="mt-2">{{ $maintenance->note }}</p>
          @if ($maintenance->start_date !== null)
          <p style="font-size: 15px;">Dari Tanggal {{ \Carbon\Carbon::parse($maintenance->start_date)->isoFormat('D MMMM YYYY HH:mm') }} - {{ \Carbon\Carbon::parse($maintenance->end_date)->isoFormat('D MMMM YYYY HH:mm') }}</p>
          @endif
        </div>
        @endif
        @endforeach
        @endif
        <!--================== END ==================-->

        <!--================== NOTIF JIKA GAJI MASIH ADA YANG PENDING ==================-->
        @if ($gaji->count() > 0 && (Auth::user()->level == 'staff' || Auth::user()->level == 'manager'))
        @php
        $totalPendingSalaries = 0;
        @endphp

        @foreach ($gaji as $item)
        @if ($item->status === 'pending')
        @php
        $totalPendingSalaries++;
        @endphp
        @endif
        @endforeach

        @if ($totalPendingSalaries > 0)
        <div class="alert alert-warning" role="alert" style="text-align: center;">
          <p style="font-size: 16px;">
            <i class="fas fa-exclamation-circle mr-1"></i>
            Ada <b>{{ $totalPendingSalaries }}</b> gaji karyawan dengan status pending yang belum terbayarkan, segara bayarkan dan ubah status menjadi terbayar
          </p>
        </div>
        @endif
        @endif
        <!--================== END ==================-->

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
            @if (Auth::user()->level == 'manager')
            <form action="{{ route('account.gaji.searchmanager') }}" method="GET" id="searchForm">
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
                  <a href="{{ route('account.gaji.index') }}" class="btn btn-danger ml-1">
                    <i class="fa fa-times-circle mt-2"></i> HAPUS PENCARIAN
                  </a>
                  @endif
                </div>
              </div>
            </form>
            @else
            <form action="{{ route('account.gaji.searchkaryawan') }}" method="GET" id="searchForm">
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
                  <a href="{{ route('account.gaji.index') }}" class="btn btn-danger ml-1">
                    <i class="fa fa-times-circle mt-2"></i> HAPUS PENCARIAN
                  </a>
                  @endif
                </div>
              </div>
            </form>
            @endif

            @if (Auth::user()->level == 'manager')
            <form action="{{ route('account.gaji.filtermanager') }}" method="GET">
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
                    <a href="{{ route('account.gaji.index') }}" class="btn btn-danger" style="margin-top: 30px;">
                      <i class="fa fa-times-circle mt-2"></i> HAPUS
                    </a>
                  </div>
                  @else
                  <button class="btn btn-info mr-1 btn-block" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                  @endif
                </div>
              </div>
            </form>
            @else
            <form action="{{ route('account.gaji.filterkaryawan') }}" method="GET">
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
                    <a href="{{ route('account.gaji.index') }}" class="btn btn-danger" style="margin-top: 30px;">
                      <i class="fa fa-times-circle mt-2"></i> HAPUS
                    </a>
                  </div>
                  @else
                  <button class="btn btn-info mr-1 btn-block" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                  @endif
                </div>
              </div>
            </form>
            @endif

            @if (Auth::user()->level == 'karyawan' || Auth::user()->level == 'trainer')
            @else
            <div class="row">
              <div class="col-12 mt-3">
                <div class="form-group text-center">
                  <div class="input-group mb-3">
                    @if ($presensiExist)
                    <a href="{{ route('account.gaji.create') }}" class="btn btn-primary btn-block" style="padding-top: 10px;">
                      <i class="fa fa-plus-circle"></i> TAMBAH GAJI
                    </a>
                    @else
                    <a href="#" class="btn btn-primary btn-block" style="padding-top: 10px;" id="tambahGajiBtn">
                      <i class="fa fa-plus-circle"></i> TAMBAH GAJI
                    </a>
                    @endif
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
            <h4><i class="fas fa-list"></i> DATA GAJI KARYAWAN</h4>
            <div class="card-header-action">
              <a href="{{ route('account.laporan_gaji.download-pdf', ['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate]) }}" class="btn btn-primary">
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
                      <th scope="col" class="column-width" style="text-align: center;">ID TRANSAKSI</th>
                      <th scope="col" class="column-width" style="text-align: center;">NAMA KARYAWAN</th>
                      <!--<th scope="col" class="column-width" style="text-align: center;">NIK</th>-->
                      <th scope="col" class="column-width" style="text-align: center;">NO REKENING</th>
                      <th scope="col" class="column-width" style="text-align: center;">BANK</th>
                      <th scope="col" class="column-width" style="text-align: center;">TOTAL GAJI</th>
                      <th scope="col" class="column-width" style="text-align: center;">TANGGAL PEMBAYARAN</th>
                      <th scope="col" class="column-width" style="text-align: center;">STATUS PEMBAYARAN</th>
                      <th scope="col" style="width: 15%;text-align: center">AKSI</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php
                    $no = 1;
                    $terbayarCount = 0; // Count of terbayar records
                    @endphp
                    @foreach ($gaji as $hasil)
                    @if ((Auth::user()->level == 'karyawan' || Auth::user()->level == 'trainer') && $hasil->status == 'pending')
                    <!-- Skip displaying records where user is karyawan and status is pending -->
                    @else
                    <tr>
                      <th scope="row" style="text-align: center">{{ $no }}</th>
                      <td class="column-width" style="text-align: center;">{{ $hasil->id_transaksi }}</td>
                      <td class="column-width" style="text-align: center;">{{ $hasil->full_name }}</td>
                      <!--<td class="column-width" style="text-align: center;">{{ $hasil->nik }}</td>-->
                      <td class="column-width" style="text-align: center;">{{ $hasil->norek }}</td>
                      <td class="column-width" style="text-align: center; width:100px">
                        @php
                        $bankNames = [
                        '002' => 'BRI',
                        '008' => 'BANK MANDIRI',
                        '009' => 'BNI',
                        '200' => 'BANK TABUNGAN NEGARA',
                        '011' => 'BANK DANAMON',
                        '013' => 'BANK PERMATA',
                        '014' => 'BCA',
                        '016' => 'MAYBANK',
                        '019' => 'PANINBANK',
                        '022' => 'CIMB NIAGA',
                        '023' => 'BANK UOB INDONESIA',
                        '028' => 'BANK OCBC NISP',
                        '087' => 'BANK HSBC INDONESIA',
                        '147' => 'BANK MUAMALAT',
                        '153' => 'BANK SINARMAS',
                        '426' => 'BANK MEGA',
                        '441' => 'BANK BUKOPIN',
                        '451' => 'BSI',
                        '484' => 'BANK KEB HANA INDONESIA',
                        '494' => 'BANK RAYA INDONESIA',
                        '506' => 'BANK MEGA SYARIAH',
                        '046' => 'BANK DBS INDONESIA',
                        '947' => 'BANK ALADIN SYARIAH',
                        '950' => 'BANK COMMONWEALTH',
                        '213' => 'BANK BTPN',
                        '490' => 'BANK NEO COMMERCE',
                        '501' => 'BANK DIGITAL BCA',
                        '521' => 'BANK BUKOPIN SYARIAH',
                        '535' => 'SEABANK INDONESIA',
                        '542' => 'BANK JAGO',
                        '567' => 'ALLO BANK',
                        '110' => 'BPD JAWA BARAT',
                        '111' => 'BPD DKI',
                        '112' => 'BPD DAERAH ISTIMEWA YOGYAKARTA',
                        '113' => 'BPD JAWA TENGAH',
                        '114' => 'BPD JAWA TIMUR',
                        '115' => 'BPD JAMBI',
                        '116' => 'BANK ACEH SYARIAH',
                        '117' => 'BPD SUMATERA UTARA',
                        '118' => 'BANK NAGARI',
                        '119' => 'BPD RIAU KEPRI SYARIAH',
                        '120' => 'BPD SUMATERA SELATAN DAN BANGKA BELITUNG',
                        '121' => 'BPD LAMPUNG',
                        '122' => 'BPD KALIMANTAN SELATAN',
                        '123' => 'BPD KALIMANTAN BARAT',
                        '124' => 'BPD KALIMANTAN TIMUR DAN KALIMANTAN UTARA',
                        '125' => 'BPD KALIMANTAN TENGAH',
                        '126' => 'BPD SULAWESI SELATAN DAN SULAWESI BARAT',
                        '127' => 'BPD SULAWESI UTARA DAN GORONTALO',
                        '128' => 'BANK NTB SYARIAH',
                        '129' => 'BPD BALI',
                        '130' => 'BPD NUSA TENGGARA TIMUR',
                        '131' => 'BPD MALUKU DAN MALUKU UTARA',
                        '132' => 'BPD PAPUA',
                        '133' => 'BPD BENGKULU',
                        '134' => 'BPD SULAWESI TENGAH',
                        '135' => 'BPD SULAWESI TENGGARA',
                        '137' => 'BPD BANTEN'
                        // Add more bank names here...
                        ];
                        @endphp
                        @if (array_key_exists($hasil->bank, $bankNames))
                        {{ $bankNames[$hasil->bank] }}
                        @else
                        Bank Name Not Found
                        @endif
                      </td>
                      <td class="column-width" style="text-align: center; width:150px">Rp. {{ number_format($hasil->total, 0, ',', '.') }}</td>
                      <td class="column-width" style="text-align: center; width:200px">
                        {{ strftime('%d %B %Y %H:%M', strtotime($hasil->tanggal)) }}
                      </td>

                      <td class="column-width" style="text-align: center;">
                        @if($hasil->status == 'pending')
                        <span class="badge badge-warning"><i class="fas fa-hourglass-half"></i></span>
                        @else
                        <span class="badge badge-success"><i class="fas fa-check-circle"></i></span>
                        @endif
                      </td>
                      <td class="text-center">
                        @if(Auth::user()->level == 'karyawan' || Auth::user()->level == 'trainer')
                        @if(now()->month == \Carbon\Carbon::parse($hasil->tanggal)->month)
                        <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.gaji.detail', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-warning">
                          <i class="fa fa-eye"></i>
                        </a>
                        @endif
                        <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.laporan_gaji.Slip-Gaji', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-info">
                          <i class="fa fa-download"></i> Slip Gaji
                        </a>
                        @else
                        @if(now()->month == \Carbon\Carbon::parse($hasil->tanggal)->month)
                        <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.gaji.edit', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-primary">
                          <i class="fa fa-pencil-alt"></i>
                        </a>
                        <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.gaji.detail', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-warning">
                          <i class="fa fa-eye"></i>
                        </a>
                        @endif
                        <button style="margin-right: 5px; margin-bottom:5px;" onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger">
                          <i class="fa fa-trash"></i>
                        </button>
                        <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.laporan_gaji.Slip-Gaji', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-info mt-2 mb-2">
                          <i class="fa fa-download"></i> Slip Gaji
                        </a>
                        @endif
                      </td>

                    </tr>
                    @php
                    $no++;
                    $terbayarCount++;
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
                  {{ $gaji->appends(['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate])->links("vendor.pagination.bootstrap-4") }}
                </div>

              </div>
            </div>
          </div>

        </div>

        @if (Auth::user()->level == 'manager' || Auth::user()->level == 'staff')
        <table class="table table-bordered mt-5" style="border: 2px solid red;">
          <thead>
            <tr>
              <th scope="col" rowspan="2" style="text-align: center; font-weight: bold;"><b>TOTAL GAJI</b></th>
            </tr>
          </thead>
          <tbody>
            <tr style="text-align: center; font-weight: bold;">
              <td>Rp. {{ number_format($totalGaji, 0, ',', ',') }}</td>
            </tr>
          </tbody>
        </table>
        @endif
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
          url: "/account/gaji/" + id,
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