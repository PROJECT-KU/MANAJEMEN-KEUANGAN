@extends('layouts.account')
@extends('layouts.loader')

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
      <!--================== NOTIF JIKA GAJI MASIH ADA YANG PENDING ==================-->
      @if ($gaji->count() > 0 && (Auth::user()->level == 'staff' || Auth::user()->level == 'manager' || Auth::user()->level == 'ceo'))
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

      <!--================== TOTAL GAJI ==================-->
      <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
          <div class="card card-statistic-2">
            <div class="card-icon shadow-primary" style="background-color: #5F9EA0;">
              <i class="fas fa-users" style="margin-top: 13px;"></i>
            </div>
            <div class="card-wrap flex-column">
              <div class="card-header">
                <h4>Periode {{ date('F Y') }}</h4>
              </div>
              <div class="card-body">
                <h5>Rp. {{ number_format($totalBulanIni, 0, ',', '.') }}</h5>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
          <div class="card card-statistic-2">
            <div class="card-icon shadow-primary" style="background-color: #5F9EA0;">
              <i class="fas fa-users" style="margin-top: 13px;"></i>
            </div>
            <div class="card-wrap flex-column">
              <div class="card-header">
                <h4>Periode {{ \Carbon\Carbon::now()->subMonth()->format('F Y') }}</h4>
              </div>
              <div class="card-body">
                <h5> Rp. {{ number_format($totalBulanLalu, 0, ',', '.') }}</h5>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
          <div class="card card-statistic-2">
            <div class="card-icon shadow-primary" style="background-color: #5F9EA0;">
              <i class="fas fa-users" style="margin-top: 13px;"></i>
            </div>
            <div class="card-wrap flex-column">
              <div class="card-header">
                <h4>Periode {{ \Carbon\Carbon::now()->subMonths(2)->format('F Y') }}</h4>
              </div>
              <div class="card-body">
                <h5> Rp. {{ number_format($totalDuaBulanLalu, 0, ',', '.') }}</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--================== END ==================-->

      <div class="card">

        <!--================== FILTER ==================-->
        <div class="card-header  d-flex justify-content-between align-items-center">
          <h4><i class="fas fa-list"></i> DATA GAJI KARYAWAN</h4>

          <div class="d-flex justify-content-end align-items-center mb-3" style="gap: 10px;">

            <!-- CREATE DATA -->
            @if ($presensiExist)
            <a href="{{ route('account.gaji.create') }}" class="btn btn-primary btn-block rounded-pill">
              <i class="fa fa-plus-circle"></i> TAMBAH GAJI
            </a>
            @else
            <a href="#" class="btn btn-primary btn-block rounded-pill" id="tambahGajiBtn">
              <i class="fa fa-plus-circle"></i> TAMBAH GAJI
            </a>
            @endif
            <!-- END -->

            <div class="dropdown card-header-action">
              <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
                <i class="fas fa-download"></i> FILTER
              </button>
              <div class="dropdown-menu dropdown-menu-right p-3" style="min-width: 300px;">

                <!-- FILTER TANGGAL -->
                <form action="{{ route('account.gaji.filter') }}" method="GET">
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
                    <a href="{{ route('account.gaji.index') }}" class="btn btn-danger" style="margin-top: 30px;">
                      <i class="fa fa-trash mt-2"></i> HAPUS
                    </a>
                  </div>
                  @else
                  <button class="btn btn-info mr-1 btn-block" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                  @endif
                </form>
                <!-- END -->

                <!-- DOWNLOAD DATA GAJI -->
                @if (Auth::user()->level == 'manager')
                <hr class="my-2">

                <div class="d-flex flex-wrap gap-2">
                  <a id="downloadExcelBtn" href="{{ route('account.laporan_gaji.download-excel', ['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate, 'q' => request('q')]) }}" class="btn btn-info" style="flex: 1 1 auto;">
                    <i class="far fa-file-excel"></i> Unduh EXCEL
                  </a>
                </div>

                @endif
                <!-- END -->

              </div>
            </div>

            <!-- SEARCH -->
            <div style="max-width: 250px; width: 100%;">
              <input type="text" id="liveSearch" class="form-control" placeholder="Pencarian..." autocomplete="off">
            </div>
            <!-- END -->

          </div>

        </div>
        <!--================== END FILTER ==================-->

        <div class="card-body" style="font-size: 11px;">
          <div class="table-responsive">
            <div class="table-responsive">

              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th scope="col" style="text-align: center;width: 6%">NO.</th>
                    <th scope="col" class="column-width" style="text-align: center;">ID Transaksi</th>
                    <th scope="col" class="column-width" style="text-align: center;">Nama Karyawan</th>
                    <th scope="col" class="column-width" style="text-align: center;">No Rekening</th>
                    <th scope="col" class="column-width" style="text-align: center;">Bank</th>
                    <th scope="col" class="column-width" style="text-align: center;">Total Gaji</th>
                    <th scope="col" class="column-width" style="text-align: center;">Tanggal Pembayaran</th>
                    <th scope="col" class="column-width" style="text-align: center;">Status Pembayaran</th>
                    <th scope="col" style="width: 15%;text-align: center">AKSI</th>
                  </tr>
                </thead>
                <tbody id="gajiTable">
                  @php
                  $no = 1;
                  $terbayarCount = 0;
                  @endphp

                  @if ($gaji->isEmpty())
                  <tr>
                    <td colspan="9" class="text-center">Tidak ada data</td>
                  </tr>
                  @endif

                  @foreach ($gaji as $hasil)
                  @if ((Auth::user()->level == 'karyawan' || Auth::user()->level == 'trainer') && $hasil->status == 'pending')
                  <!-- Skip displaying records where user is karyawan and status is pending -->
                  @else
                  <tr>
                    <th scope="row" style="text-align: center">{{ $no }}</th>
                    <td class="column-width" style="text-align: center;">{{ $hasil->id_transaksi }}</td>
                    <td class="column-width" style="text-align: center;">{{ $hasil->full_name }}</td>
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
                      <span class="badge bg-warning" style="padding: 6px 12px; border-radius: 6px;" disabled>
                        Pending
                      </span>
                      @else
                      <span class="badge bg-success" style="padding: 6px 12px; border-radius: 6px;" disabled>
                        Terbayar
                      </span>
                      @endif
                    </td>

                    <td class="text-center">
                      <div class="d-flex justify-content-center align-items-center" style="gap: 5px;">
                        @if(Auth::user()->level == 'karyawan' || Auth::user()->level == 'trainer' || Auth::user()->level == 'ceo')
                        @if(now()->month == \Carbon\Carbon::parse($hasil->tanggal)->month)
                        @endif
                        <a style="margin-bottom:4px; height: 30px; width: 30px;" href="{{ route('account.gaji.detail', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-secondary mt-2">
                          <i class="fa fa-eye" style="margin-top: 6px;"></i>
                        </a>
                        <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.laporan_gaji.Slip-Gaji', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-info mb-2">
                          <i class="fa fa-download"></i> Slip Gaji
                        </a>
                        @else
                        @if(now()->month == \Carbon\Carbon::parse($hasil->tanggal)->month)
                        <a style="margin-right: 5px; margin-bottom:4px; height: 30px; width: 30px;" href="{{ route('account.gaji.edit', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-warning mt-2">
                          <i class="fa fa-pencil-alt" style="margin-top: 6px;"></i>
                        </a>
                        @endif
                        <a style="margin-right: 5px; margin-bottom:4px; height: 30px; width: 30px;" href="{{ route('account.gaji.detail', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-secondary mt-2">
                          <i class="fa fa-eye" style="margin-top: 6px;"></i>
                        </a>
                        <button style="margin-right: 5px; margin-bottom:4px; width:30px; height:30px;" onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger mt-2">
                          <i class="fa fa-trash"></i>
                        </button>
                        <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.laporan_gaji.Slip-Gaji', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="btn btn-sm btn-info mt-2 mb-2">
                          <i class="fa fa-download"></i> Slip Gaji
                        </a>
                        @endif
                      </div>
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

      const url = `{{ route('account.laporan_gaji.download-excel') }}?tanggal_awal=${tanggal_awal}&tanggal_akhir=${tanggal_akhir}&q=${encodeURIComponent(q)}`;

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
      fetch(`{{ route('account.gaji.search') }}?q=${encodeURIComponent(query)}`)
        .then(response => response.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newTableBody = doc.querySelector('#gajiTable');
          if (newTableBody) {
            document.getElementById('gajiTable').innerHTML = newTableBody.innerHTML;
          }
        });
    }, 300);
  });
</script>
<!--================== END ==================-->

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