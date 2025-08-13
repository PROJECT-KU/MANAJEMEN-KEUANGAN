@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Data Presensi Karyawan | MIS
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div id="realtime-container">
      <div class="section-header">
        <h1>DATA PRESENSI KARYAWAN</h1>
      </div>

      <div class="section-body">

        <!--================== DATA PRESENSI ==================-->
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h4><i class=" fas fa-list"></i> DATA PRESENSI KARYAWAN</h4>

            <!--================== FILTER ==================-->
            <div class="d-flex justify-content-end align-items-center mb-3" style="gap: 10px;">

              <!-- CREATE DATA -->
              @if (Auth::user()->level == 'manager')
              <a href="{{ route('account.presensi.create') }}" class="btn btn-primary btn-block rounded-pill">
                <i class="fa fa-plus-circle"></i> TAMBAH PRESENSI
              </a>
              @endif
              <!-- END -->

              <div class="dropdown card-header-action">
                <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
                  <i class="fas fa-download"></i> FILTER
                </button>
                <div class="dropdown-menu dropdown-menu-right p-3" style="min-width: 300px;">

                  <!-- FILTER TANGGAL -->
                  <form action="{{ route('account.presensi.filter') }}" method="GET">
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
                      <a href="{{ route('account.presensi.index') }}" class="btn btn-danger" style="margin-top: 30px;">
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
                    <a id="downloadExcelBtn" href="{{ route('account.laporan_presensi.download-excel', ['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate, 'q' => request('q')]) }}" class="btn btn-info" style="flex: 1 1 auto;">
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
            <!--================== END FILTER ==================-->
          </div>

          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th scope="col" rowspan="2" style="text-align: center;width: 6%">NO.</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">NAMA KARYAWAN</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">TANGGAL PRESENSI</th>
                    <th scope="col" colspan="2" class="column-width" style="text-align: center;">KEHADIRAN</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">LAMA KERJA</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">STATUS PRESENSI</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">LOKASI PRESENSI</th>
                    @if(Auth::user()->level == 'manager' || Auth::user()->level == 'ceo')
                    <th scope="col" rowspan="2" style="text-align: center">AKSI</th>
                    @endif
                  </tr>
                  <tr>
                    <th scope="col" style="text-align: center;">HADIR</th>
                    <th scope="col" style="text-align: center;">PULANG</th>
                  </tr>
                </thead>
                <tbody id="presensiTable">
                  @php
                  $no = 1;
                  @endphp
                  @foreach ($presensi as $hasil)
                  <tr>
                    <th scope="row" style="text-align: center">{{ $no }}</th>
                    <td class="column-width" style="text-align: center;">{{ $hasil->full_name }}</td>
                    <td class="column-width" style="text-align: center;" hidden>{{ $hasil->telp }}</td>
                    <td class="column-width" style="text-align: center;">
                      <!-- {{ date('d-m-Y H:i', strtotime($hasil->created_at)) }} <br> -->
                      {{ strftime('%A, %d %B %Y', strtotime($hasil->created_at)) }}
                    </td>
                    <td class="column-width" style="text-align: center;">{{ strftime('%H:%M:%S', strtotime($hasil->created_at)) }}</td>
                    @if($hasil->time_pulang == null)
                    <td class="column-width" style="text-align: center;"></td>
                    @else
                    <td class="column-width" style="text-align: center;">{{ strftime('%H:%M:%S', strtotime($hasil->time_pulang)) }}</td>
                    @endif
                    @if($hasil->time_pulang == null)
                    <td class="column-width" style="text-align: center;"></td>
                    @else
                    <td class="column-width" style="text-align: center;">
                      <?php
                      $created_at = strtotime($hasil->created_at);
                      $time_pulang = strtotime($hasil->time_pulang);

                      // Menghitung selisih waktu dalam detik
                      $selisih_detik = $time_pulang - $created_at;

                      // Menghitung jumlah jam dan menit
                      $jam = floor($selisih_detik / 3600);
                      $menit = floor(($selisih_detik % 3600) / 60);

                      // Menampilkan lama kerja dalam format "jam jam menit menit"
                      echo sprintf('%02d jam %02d menit', $jam, $menit);
                      ?>
                    </td>
                    @endif
                    <td class="column-width" style="text-align: center;">
                      @if ($hasil->status == 'hadir')
                      <span class="badge badge-success mt-2">HADIR</span>
                      @elseif ($hasil->status == 'camp jogja')
                      <span class="badge badge-success mt-2">CAMP JOGJA</span>
                      @elseif ($hasil->status == 'perjalanan luar kota jawa')
                      <span class="badge badge-info mt-2">PERJALANAN LUAR KOTA DALAM JAWA</span>
                      @elseif ($hasil->status == 'perjalanan luar kota luar jawa')
                      <span class="badge badge-info mt-2">PERJALANAN LUAR KOTA LUAR JAWA</span>
                      @elseif ($hasil->status == 'camp luar kota')
                      <span class="badge badge-success mt-2">CAMP LUAR KOTA</span>
                      @elseif ($hasil->status == 'remote')
                      <span class="badge badge-info mt-2">REMOTE</span>
                      @elseif ($hasil->status == 'izin')
                      <span class="badge badge-warning mt-2">IZIN</span>
                      @elseif ($hasil->status == 'lembur')
                      <span class="badge badge-primary mt-2">LEMBUR</span>
                      @elseif ($hasil->status == 'cuti')
                      <span class="badge badge-warning mt-2">CUTI</span>
                      @elseif ($hasil->status == 'terlambat')
                      <span class="badge badge-danger mt-2">TERLAMBAT</span>
                      @elseif ($hasil->status == 'alpha')
                      <span class="badge badge-danger mt-2">ALPHA</span>
                      @elseif ($hasil->status == 'pulang')
                      <span class="badge badge-danger mt-2">PULANG</span>
                      @endif
                      <br>
                      @if ($hasil->status_pulang == 'hadir')
                      <span class="badge badge-success mt-2">HADIR</span>
                      @elseif ($hasil->status_pulang == 'camp jogja')
                      <span class="badge badge-success">CAMP JOGJA</span>
                      @elseif ($hasil->status_pulang == 'perjalanan luar kota jawa')
                      <span class="badge badge-info">PERJALANAN LUAR KOTA DALAM JAWA</span>
                      @elseif ($hasil->status_pulang == 'perjalanan luar kota luar jawa')
                      <span class="badge badge-info">PERJALANAN LUAR KOTA LUAR JAWA</span>
                      @elseif ($hasil->status_pulang == 'camp luar kota')
                      <span class="badge badge-success">CAMP LUAR KOTA</span>
                      @elseif ($hasil->status_pulang == 'remote')
                      <span class="badge badge-info mt-2">REMOTE</span>
                      @elseif ($hasil->status_pulang == 'izin')
                      <span class="badge badge-warning mt-2">IZIN</span>
                      @elseif ($hasil->status_pulang == 'lembur')
                      <span class="badge badge-primary mt-2">LEMBUR</span>
                      @elseif ($hasil->status_pulang == 'cuti')
                      <span class="badge badge-warning mt-2">CUTI</span>
                      @elseif ($hasil->status_pulang == 'terlambat')
                      <span class="badge badge-danger mt-2">TERLAMBAT</span>
                      @elseif ($hasil->status_pulang == 'alpha')
                      <span class="badge badge-danger">ALPHA</span>
                      @elseif ($hasil->status_pulang == 'pulang')
                      <span class="badge badge-danger mt-2">PULANG</span>
                      @endif
                    </td>
                    <td class="column-width" style="text-align: center;">
                      <a href="https://www.google.com/maps?q={{ $hasil->latitude }},{{ $hasil->longitude }}" target="_blank">
                        Lihat di Google Maps
                      </a>
                    </td>
                    @if (Auth::user()->level == 'staff' || Auth::user()->level == 'ceo')
                    <td class="text-center">
                      <a href="{{ route('account.presensi.detail', $hasil->id) }}" class="btn btn-sm btn-warning">
                        <i class="fa fa-eye"></i>
                      </a>
                    </td>
                    @elseif (Auth::user()->level == 'karyawan' || Auth::user()->level == 'trainer')
                    @else
                    <td class="text-center">
                      <div class="d-flex justify-content-center align-items-center" style="gap: 5px;">
                        <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.presensi.edit', $hasil->id) }}" class="btn btn-sm btn-warning mt-2">
                          <i class="fa fa-pencil-alt"></i>
                        </a>
                        <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.presensi.detail', $hasil->id) }}" class="btn btn-sm btn-secondary mt-2">
                          <i class="fa fa-eye"></i>
                        </a>
                        <button style="margin-right: 5px; margin-bottom:5px;" onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger mt-2 mb-2">
                          <i class="fa fa-trash"></i>
                        </button>
                      </div>
                    </td>
                    @endif
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
                {{ $presensi->appends(['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate])->links("vendor.pagination.bootstrap-4") }}
              </div>
            </div>

          </div>
        </div>
        <!--================== END ==================-->

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

      const url = `{{ route('account.laporan_presensi.download-excel') }}?tanggal_awal=${tanggal_awal}&tanggal_akhir=${tanggal_akhir}&q=${encodeURIComponent(q)}`;

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
        const pdfUrl = `{{ route('account.laporan_presensi.download-excel') }}?tanggal_awal=${encodeURIComponent(newUrl.searchParams.get('tanggal_awal') || '')}&tanggal_akhir=${encodeURIComponent(newUrl.searchParams.get('tanggal_akhir') || '')}&q=${encodeURIComponent(query)}`;
        pdfButton.setAttribute('href', pdfUrl);
      }

      // Fetch data tabel
      fetch(`{{ route('account.presensi.search') }}?q=${encodeURIComponent(query)}`)
        .then(response => response.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newTableBody = doc.querySelector('#presensiTable');
          if (newTableBody) {
            document.getElementById('presensiTable').innerHTML = newTableBody.innerHTML;
          }
        });
    }, 300);
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
          url: "/account/presensi/" + id,
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