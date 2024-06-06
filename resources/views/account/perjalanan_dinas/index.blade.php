@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Data Perjalanan Dinas | MIS
@stop

<style>
  /* Sembunyikan tabel Diterima dan Ditolak secara default */
  #tableDiterima,
  #tableDitolak {
    display: none;
  }

  #btnDiajukan {
    display: block;
  }
</style>

<style>
  /* Gaya untuk tombol saat diklik */
  .btn.clicked {
    background-color: #6495ED !important;
    /* Warna latar belakang saat diklik */
    color: #fff !important;
    /* Warna teks saat diklik */
    border-color: #17a2b8 !important;
    /* Warna border saat diklik */
  }
</style>

@section('content')
<div class="main-content">
  <section class="section">
    <div id="realtime-container">
      <div class="section-header">
        <h1>DATA PERJALANAN DINAS</h1>
      </div>

      <div class="section-body">

        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              <button id="btnDiajukan" type="button" class="btn btn-block btnDiajukan clicked status-button mt-2" data-status="ajukan" style="height: 50px; font-size:15px; background-color:#DCDCDC;">DIAJUKAN</button>
            </div>
            <div class="col-md-4">
              <button id="btnDiterima" type="button" class="btn btn-block btnDiterima status-button mt-2" data-status="diterima" style="height: 50px; font-size:15px; background-color:#DCDCDC;">DITERIMA</button>
            </div>
            <div class="col-md-4">
              <button id="btnDitolak" type="button" class="btn btn-block btnDitolak status-button mt-2" data-status="ditolak" style="height: 50px; font-size:15px; background-color:#DCDCDC;">DITOLAK</button>
            </div>
          </div>
        </div>

        <!--================== TABEL DIAJUKAN ==================-->
        <div id="tableDiajukan" class="card">
          <div id="tblDiajukan" class="card-header" style="background-color: #FF7F50; color:white;">
            <h4 class="d-none d-sm-block"><i class="fas fa-list"></i> DATA PERJALANAN DINAS DIAJUKAN</h4>
            <h4 class="d-block d-sm-none btn-block"><i class="fas fa-list"></i> DATA DIAJUKAN</h4>
            <a href="{{ route('account.PerjalananDinas.create') }}" class="btn btn-primary d-none d-sm-block ml-auto"> <i class="fa fa-plus-circle"></i> TAMBAH DATA
            </a>
            <a href="{{ route('account.PerjalananDinas.create') }}" class="btn btn-primary d-block d-sm-none btn-block"> <i class="fa fa-plus-circle"></i> TAMBAH DATA
            </a>
          </div>

          <div class="card-header d-flex justify-content-between align-items-center">
            <p style="margin-top: -3px; font-size: 15px;"><strong>Periode
                @if ($startDate && $endDate)
                {{ date('d F Y', strtotime($startDate)) }} - {{ date('d F Y', strtotime($endDate)) }}
                @else
                {{ date('F Y') }}
                @endif
              </strong>
            </p>
            <div class="input-group" style="width: 300px;">
              <input type="text" class="form-control" id="searchDiajukan" placeholder="Pencarian">
              <!-- <div class="input-group-append">
                  <a href="{{ route('account.PerjalananDinas.index') }}"><button class="btn btn-outline-danger ml-1" type="button" id="clearSearch" style="display: none;">
                      <i class="fas fa-trash"></i>
                    </button></a>
                </div> -->
            </div>
          </div>

          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th scope="col" rowspan="2" style="text-align: center;width: 6%">NO.</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">ID TRANSAKSI</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">BENDAHARA</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">SCOPUS CAMP</th>
                    <th scope="col" colspan="3" class="column-width" style="text-align: center;">TOTAL SALDO</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">STATUS</th>
                    <th scope="col" rowspan="2" style="text-align: center">AKSI</th>
                  </tr>
                  <tr>
                    <th scope="col" style="text-align: center;">TOTAL MASUK</th>
                    <th scope="col" style="text-align: center;">TOTAL KELUAR</th>
                    <th scope="col" style="text-align: center;">SISA SALDO</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $no = 1;
                  @endphp
                  @foreach ($DatasAjukan as $hasil)
                  @if ($hasil->status == 'ajukan')
                  <tr>
                    <th scope="row" style="text-align: center">{{ $no }}</th>
                    <td class="column-width" style="text-align: center;">{{ $hasil->id_transaksi }}</td>
                    <td class="column-width" style="text-align: center;">{{ $hasil->full_name }}</td>
                    <td class="column-width" style="text-align: center; text-transform:uppercase;">{{ $hasil->tempat }} #{{ $hasil->camp }}</td>
                    <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->total_uang_masuk, 0, ',', '.') }}</td>
                    <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->total_uang_keluar, 0, ',', '.') }}</td>
                    <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->sisa_saldo, 0, ',', '.') }}</td>
                    <td class="column-width" style="text-align: center; width:150px">
                      @if($hasil->status == 'ajukan')
                      <span class="badge badge-warning">DIAJUKAN</span>
                      @endif
                    </td>
                    @if (Auth::user()->level == 'karyawan')
                    <td class="text-center">
                      <a style="margin-right: 5px; margin-bottom:5px; height: 30px; width: 30px;" href="{{ route('account.PerjalananDinas.editAjukan', $hasil->id) }}" class="btn btn-sm btn-warning mt-2">
                        <i class="fa fa-eye" style="margin-top:6px"></i>
                      </a>
                    </td>
                    @else
                    <td class="text-center">
                      <a style="margin-right: 5px; margin-bottom:5px; height: 30px; width: 30px;" href="{{ route('account.PerjalananDinas.editAjukan', $hasil->id) }}" class="btn btn-sm btn-primary mt-2">
                        <i class="fa fa-pencil-alt" style="margin-top:6px"></i>
                      </a>
                      <button style="margin-right: 5px; margin-bottom:5px; height: 30px; width: 30px;" onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger mt-2 mb-2">
                        <i class="fa fa-trash"></i>
                      </button>
                    </td>
                    @endif
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
                {{ $DatasAjukan->appends(['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate, 'status' => 'ajukan'])->links("vendor.pagination.bootstrap-4") }}
              </div>
            </div>

          </div>
        </div>
        <!--================== END ==================-->

        <!--================== TABEL DITERIMA ==================-->
        <div id="tableDiterima" class="card">
          <div id="tblDiajukan" class="card-header" style="background-color: #63ED7A; color:white;">
            <h4 class="d-none d-sm-block"><i class="fas fa-list"></i> DATA PERJALANAN DINAS DITERIMA</h4>
            <h4 class="d-block d-sm-none btn-block"><i class="fas fa-list"></i> DATA DITERIMA</h4>
          </div>

          <div class="card-header d-flex justify-content-between align-items-center">
            <p style="margin-top: -3px; font-size: 15px"><strong>Periode
                @if ($startDate && $endDate)
                {{ date('d F Y', strtotime($startDate)) }} - {{ date('d F Y', strtotime($endDate)) }}
                @else
                {{ date('F Y') }}
                @endif
              </strong>
            </p>
            <div class="input-group" style="width: 300px;">
              <input type="text" class="form-control" id="searchDiterima" placeholder="Pencarian">
              <!-- <div class="input-group-append">
                  <button class="btn btn-outline-danger ml-1" type="button" id="clearSearchDiterima">
                    <i class="fas fa-trash"></i>
                  </button>
                </div> -->
            </div>
          </div>

          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th scope="col" rowspan="2" style="text-align: center;width: 6%">NO.</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">ID TRANSAKSI</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">BENDAHARA</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">SCOPUS CAMP</th>
                    <th scope="col" colspan="3" class="column-width" style="text-align: center;">TOTAL SALDO</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">STATUS</th>
                    <th scope="col" rowspan="2" style="text-align: center">AKSI</th>
                  </tr>
                  <tr>
                    <th scope="col" style="text-align: center;">TOTAL MASUK</th>
                    <th scope="col" style="text-align: center;">TOTAL KELUAR</th>
                    <th scope="col" style="text-align: center;">SISA SALDO</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $no = 1;
                  @endphp
                  @foreach ($DatasDiterima as $hasil)
                  @if ($hasil->status == 'diterima')
                  <tr>
                    <th scope="row" style="text-align: center">{{ $no }}</th>
                    <td class="column-width" style="text-align: center;">{{ $hasil->id_transaksi }}</td>
                    <td class="column-width" style="text-align: center;">{{ $hasil->full_name }}</td>
                    <td class="column-width" style="text-align: center; text-transform:uppercase;">{{ $hasil->tempat }} #{{ $hasil->camp }}</td>
                    <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->total_uang_masuk, 0, ',', '.') }}</td>
                    <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->total_uang_keluar, 0, ',', '.') }}</td>
                    <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->sisa_saldo, 0, ',', '.') }}</td>
                    <td class="column-width" style="text-align: center; width:150px">
                      @if($hasil->status == 'diterima')
                      <span class="badge badge-success">DITERIMA</span>
                      @endif
                    </td>
                    @if (Auth::user()->level == 'karyawan')
                    <td class="text-center">
                      <a style="margin-right: 5px; margin-bottom:5px; height: 30px; width: 30px;" href="{{ route('account.PerjalananDinas.editDiterima', $hasil->id) }}" class="btn btn-sm btn-warning mt-2">
                        <i class="fa fa-eye" style="margin-top:6px"></i>
                      </a>
                    </td>
                    @else
                    <td class="text-center">
                      <a style="margin-right: 5px; margin-bottom:5px; height: 30px; width: 30px;" href="{{ route('account.PerjalananDinas.editDiterima', $hasil->id) }}" class="btn btn-sm btn-primary mt-2">
                        <i class="fa fa-pencil-alt" style="margin-top:6px"></i>
                      </a>
                      <button style="margin-right: 5px; margin-bottom:5px; height: 30px; width: 30px;" onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger mt-2 mb-2">
                        <i class="fa fa-trash"></i>
                      </button>
                    </td>
                    @endif
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
                {{ $DatasDiterima->appends(['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate, 'status' => 'diterima'])->links("vendor.pagination.bootstrap-4") }}
              </div>
            </div>

          </div>
        </div>
        <!--================== END ==================-->

        <!--================== TABEL DITOLAK ==================-->
        <div id="tableDitolak" class="card">
          <div id="tblDiajukan" class="card-header" style="background-color: #FC554B; color:white;">
            <h4 class="d-none d-sm-block"><i class="fas fa-list"></i> DATA PERJALANAN DINAS DITOLAK</h4>
            <h4 class="d-block d-sm-none btn-block"><i class="fas fa-list"></i> DATA DITOLAK</h4>
          </div>

          <div class="card-header  d-flex justify-content-between align-items-center">
            <p style="margin-top: -3px; font-size: 15px"><strong>Periode
                @if ($startDate && $endDate)
                {{ date('d F Y', strtotime($startDate)) }} - {{ date('d F Y', strtotime($endDate)) }}
                @else
                {{ date('F Y') }}
                @endif
              </strong>
            </p>
            <div class="input-group" style="width: 300px;">
              <input type="text" class="form-control" id="searchDitolak" placeholder="Pencarian">
              <!-- <div class="input-group-append">
                  <a href="{{ route('account.PerjalananDinas.index') }}"><button class="btn btn-outline-danger ml-1" type="button" id="clearSearchDitolak" style="display: none;">
                      <i class="fas fa-trash"></i>
                    </button></a>
                </div> -->
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th scope="col" rowspan="2" style="text-align: center;width: 6%">NO.</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">ID TRANSAKSI</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">BENDAHARA</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">SCOPUS CAMP</th>
                    <th scope="col" colspan="3" class="column-width" style="text-align: center;">TOTAL SALDO</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">STATUS</th>
                    <th scope="col" rowspan="2" style="text-align: center">AKSI</th>
                  </tr>
                  <tr>
                    <th scope="col" style="text-align: center;">TOTAL MASUK</th>
                    <th scope="col" style="text-align: center;">TOTAL KELUAR</th>
                    <th scope="col" style="text-align: center;">SISA SALDO</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $no = 1;
                  @endphp
                  @foreach ($DatasDitolak as $hasil)
                  @if ($hasil->status == 'ditolak')
                  <tr>
                    <th scope="row" style="text-align: center">{{ $no }}</th>
                    <td class="column-width" style="text-align: center;">{{ $hasil->id_transaksi }}</td>
                    <td class="column-width" style="text-align: center;">{{ $hasil->full_name }}</td>
                    <td class="column-width" style="text-align: center; text-transform:uppercase;">{{ $hasil->tempat }} #{{ $hasil->camp }}</td>
                    <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->total_uang_masuk, 0, ',', '.') }}</td>
                    <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->total_uang_keluar, 0, ',', '.') }}</td>
                    <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->sisa_saldo, 0, ',', '.') }}</td>
                    <td class="column-width" style="text-align: center; width:150px">
                      @if($hasil->status == 'ditolak')
                      <span class="badge badge-danger">DITOLAK</span>
                      @endif
                    </td>
                    @if (Auth::user()->level == 'karyawan')
                    <td class="text-center">
                      <a style="margin-right: 5px; margin-bottom:5px; height: 30px; width: 30px;" href="{{ route('account.PerjalananDinas.editDitolak', $hasil->id) }}" class="btn btn-sm btn-warning mt-2">
                        <i class="fa fa-eye" style="margin-top:6px"></i>
                      </a>
                    </td>
                    @else
                    <td class="text-center">
                      <a style="margin-right: 5px; margin-bottom:5px; height: 30px; width: 30px;" href="{{ route('account.PerjalananDinas.editDitolak', $hasil->id) }}" class="btn btn-sm btn-primary mt-2">
                        <i class="fa fa-pencil-alt" style="margin-top:6px"></i>
                      </a>
                      <button style="margin-right: 5px; margin-bottom:5px; height: 30px; width: 30px;" onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger mt-2 mb-2">
                        <i class="fa fa-trash"></i>
                      </button>
                    </td>
                    @endif
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
                {{ $DatasDitolak->appends(['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate, 'status' => 'ditolak'])->links("vendor.pagination.bootstrap-4") }}
              </div>
            </div>

          </div>
        </div>
        <!--================== END ==================-->

      </div>
    </div>
  </section>
</div>


<!--================== RESET SEARCH ==================-->
<script>
  // <!-- DIAJUKAN -->
  document.getElementById('clearSearch').addEventListener('click', function() {
    document.getElementById('searchDiajukan').value = '';
    this.style.display = 'none';
  });
  // <!-- END -->

  // <!-- DITERIMA -->
  document.getElementById('clearSearchDiterima').addEventListener('click', function() {
    document.getElementById('searchDiterima').value = '';
    this.style.display = 'none';
  });
  // <!-- END -->

  // <!-- DITOLAK -->
  document.getElementById('clearSearchDitolak').addEventListener('click', function() {
    document.getElementById('searchDitolak').value = '';
    this.style.display = 'none';
  });
  // <!-- END -->
</script>

<!--================== END ==================-->

<!--================== SEARCH WITH JQUERY ==================-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  // jQuery function to handle search input for each table
  $(document).ready(function() {
    // Function to filter table rows based on search input value
    function filterTable(inputId, tableId) {
      $(inputId).on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(tableId + " tbody tr").filter(function() {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
      });
    }

    // Call filterTable function for each table
    filterTable("#searchDiajukan", "#tableDiajukan");
    filterTable("#searchDiterima", "#tableDiterima");
    filterTable("#searchDitolak", "#tableDitolak");
  });
</script>
<!--================== END ==================-->

<!--================== MOVE TO BUTTON CLICKED ==================-->
<script>
  $(document).ready(function() {
    // Tambahkan kelas .clicked ke tombol yang diklik
    $('.btn').click(function() {
      $('.btn').removeClass('clicked');
      $(this).addClass('clicked');
    });
  });
</script>
<!--================== END ==================-->

<!--================== UNTUK PINDAH TABEL DIAJUKAN ATAU DITERIMA ATAU DITOLAK ==================-->
<script>
  $(document).ready(function() {
    $('#tableDiajukan').show();
    $('#btnDiajukan').show();

    // Menampilkan tabel Diajukan saat tombol "Diajukan" diklik
    $('.btnDiajukan').click(function() {
      $('#tableDiajukan').show(); // Menampilkan tabel Diajukan
      $('#tableDiterima').hide(); // Menyembunyikan tabel Diterima
      $('#tableDitolak').hide(); // Menyembunyikan tabel Ditolak
    });

    // Menampilkan tabel Diterima saat tombol "Diterima" diklik
    $('.btnDiterima').click(function() {
      $('#tableDiajukan').hide(); // Menyembunyikan tabel Diajukan
      $('#tableDiterima').show(); // Menampilkan tabel Diterima
      $('#tableDitolak').hide(); // Menyembunyikan tabel Ditolak
    });

    // Menampilkan tabel Ditolak saat tombol "Ditolak" diklik
    $('.btnDitolak').click(function() {
      $('#tableDiajukan').hide(); // Menyembunyikan tabel Diajukan
      $('#tableDiterima').hide(); // Menyembunyikan tabel Diterima
      $('#tableDitolak').show(); // Menampilkan tabel Ditolak
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


<!--================== TIME SAAT INI ==================-->
<script>
  // Function to update the current time
  function updateCurrentTime() {
    // Get the current date and time
    var now = new Date();

    // Format the time as HH:mm:ss
    var hours = now.getHours().toString().padStart(2, '0');
    var minutes = now.getMinutes().toString().padStart(2, '0');

    // Display the formatted time in the element with the ID "current-time"
    $('#current-time').text(hours + ':' + minutes);
  }

  // Update the current time every second
  setInterval(updateCurrentTime, 1000);

  // Call the function once to initialize the time
  updateCurrentTime();
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
          url: "/account/Perjalanan-Dinas/delete/" + id,
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