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
        <!--================== FILTER ==================-->
        @if (Auth::user()->level === 'admin' || Auth::user()->level === 'manager')
        <div class="card">
          <div class="card-header  text-right">
            <h4><i class="fas fa-filter"></i> FILTER</h4>
          </div>

          <div class="card-body">
            <form action="{{ route('account.presensi.search') }}" method="GET" id="searchForm">
              <div class="form-group">
                <div class="input-group mb-3">
                  <input type="text" class="form-control rounded-pill" name="q" placeholder="PENCARIAN" value="{{ app('request')->input('q') }}" id="searchInput">
                  <div class="input-group-append">
                    <!-- <button type="button" class="btn btn-info" id="searchButton"><i class="fa fa-search"></i> CARI</button> -->
                  </div>
                  @if(request()->has('q'))
                  <a href="{{ route('account.presensi.index') }}" class="btn btn-danger rounded-pill ml-1">
                    <i class="fa fa-trash mt-2"></i>
                  </a>
                  @endif
                </div>
              </div>
            </form>

            <form action="{{ route('account.presensi.filter') }}" method="GET" id="filterForm">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>TANGGAL AWAL</label>
                    <input type="date" name="tanggal_awal" value="{{ old('tanggal_awal') }}" class="form-control datepicker rounded-pill">
                  </div>
                </div>
                <div class="col-md-2" style="text-align: center">
                  <label style="margin-top: 38px;">S/D</label>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>TANGGAL AKHIR</label>
                    <input type="date" name="tanggal_akhir" value="{{ old('tanggal_akhir') }}" class="form-control datepicker rounded-pill">
                  </div>
                </div>
                <div class="col-md-2">
                  @if (request()->has('tanggal_awal') && request()->has('tanggal_akhir'))
                  <div class="btn-group" style="width: 100%;">
                    <a href="{{ route('account.presensi.index') }}" id="clearFilterButton" class="btn btn-danger rounded-pill" style="margin-top: 30px; font-size:15px;">
                      <i class="fa fa-trash mt-2"></i>
                    </a>
                  </div>
                  @else
                  <button class="btn btn-info mr-1 btn-block rounded-pill" type="submit" style="margin-top: 30px; font-size:15px;" id="filterButton"><i class="fa fa-filter"></i> FILTER</button>
                  @endif
                </div>
              </div>
            </form>


            @if (Auth::user()->level == 'manager')
            <a href="{{ route('account.presensi.create') }}" class="btn btn-primary btn-block mt-3" style="padding-top: 10px;">
              <i class="fa fa-plus-circle"></i> TAMBAH PRESENSI
            </a>
            @endif
          </div>
        </div>
        @else
        @endif
        <!--================== end ==================-->

        <!--================== DATA PRESENSI HARI INI ==================-->
        <div class="card" id="presensiCard">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h4><i class="fas fa-clock"></i> PRESENSI HARI INI</h4>
            <p style="margin-top: 15px; font-size: 15px; margin-bottom: 0;"><strong>Periode {{ \Carbon\Carbon::now()->format('d F Y') }}</strong></p>
          </div>

          <div class="card-body">
            @if($presensihariini->isEmpty())
            <p class="text-center">Belum ada karyawan yang presensi pada hari ini.</p>
            @else
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
                <tbody>
                  @php
                  $no = 1;
                  @endphp
                  @foreach ($presensihariini as $hasil)
                  <tr>
                    <th scope="row" style="text-align: center">{{ $no }}</th>
                    <td class="column-width" style="text-align: center;">{{ $hasil->full_name }}</td>
                    <td class="column-width" style="text-align: center;">
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
                      @php
                      $created_at = strtotime($hasil->created_at);
                      $time_pulang = strtotime($hasil->time_pulang);

                      // Menghitung selisih waktu dalam detik
                      $selisih_detik = $time_pulang - $created_at;

                      // Menghitung jumlah jam dan menit
                      $jam = floor($selisih_detik / 3600);
                      $menit = floor(($selisih_detik % 3600) / 60);

                      // Menampilkan lama kerja dalam format "jam jam menit menit"
                      echo sprintf('%02d jam %02d menit', $jam, $menit);
                      @endphp
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
                    @if (Auth::user()->level == 'manager' || Auth::user()->level == 'ceo')
                    <td class="text-center">
                      <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.presensi.edit', $hasil->id) }}" class="btn btn-sm btn-primary mt-2">
                        <i class="fa fa-pencil-alt"></i>
                      </a>
                      <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.presensi.detail', $hasil->id) }}" class="btn btn-sm btn-warning mt-2">
                        <i class="fa fa-eye"></i>
                      </a>
                      <button style="margin-right: 5px; margin-bottom:5px;" onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger mt-2 mb-2">
                        <i class="fa fa-trash"></i>
                      </button>
                    </td>
                    @endif
                  </tr>
                  @php
                  $no++;
                  @endphp
                  @endforeach
                </tbody>
              </table>
            </div>
            @endif
          </div>
        </div>
        <!--================== END ==================-->


        <!--================== DATA PRESENSI ==================-->
        <div class="card">
          <div class="card-header">
            <h4><i class="fas fa-list"></i> DATA PRESENSI KARYAWAN</h4>
            <div class="card-header-action">
              <a href="{{ route('account.laporan_presensi.download-pdf', [
        'tanggal_awal' => $startDate,
        'tanggal_akhir' => $endDate,
        'q' => app('request')->input('q') // include search query in the PDF link
    ]) }}" class="btn btn-primary">
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
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th scope="col" rowspan="2" style="text-align: center;width: 6%">NO.</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">NAMA KARYAWAN</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">TANGGAL PRESENSI</th>
                    <th scope="col" colspan="2" class="column-width" style="text-align: center;">KEHADIRAN</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">LAMA KERJA</th>
                    <th scope="col" rowspan="2" class="column-width" style="text-align: center;">STATUS PRESENSI</th>
                    <!-- <th scope="col" rowspan="2" class="column-width" style="text-align: center;">BUKTI PRESENSI</th> -->
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
                <tbody>
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
                    <!-- <td class="column-width" style="text-align: center;">
                        <a href="{{ asset('images/' . $hasil->gambar) }}" data-lightbox="{{ $hasil->id }}">
                          <div class="thumbnail-circle">
                            <img style="width: 100px; height:100px;" src="{{ asset('images/' . $hasil->gambar) }}" alt="Gambar Presensi" class="img-thumbnail rounded-circle">
                          </div>
                        </a>
                      </td> -->
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
                      <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.presensi.edit', $hasil->id) }}" class="btn btn-sm btn-primary mt-2">
                        <i class="fa fa-pencil-alt"></i>
                      </a>
                      <a style="margin-right: 5px; margin-bottom:5px;" href="{{ route('account.presensi.detail', $hasil->id) }}" class="btn btn-sm btn-warning mt-2">
                        <i class="fa fa-eye"></i>
                      </a>
                      <button style="margin-right: 5px; margin-bottom:5px;" onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger mt-2 mb-2">
                        <i class="fa fa-trash"></i>
                      </button>
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

<!--================== SEARCH WITH JQUERY ==================-->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    let searchInput = document.getElementById('searchInput');
    let searchForm = document.getElementById('searchForm');
    let debounceTimeout;
    let presensiCard = document.getElementById('presensiCard'); // Pastikan ID sesuai dengan elemen card yang ingin disembunyikan

    searchInput.addEventListener('keyup', function() {
      clearTimeout(debounceTimeout);
      debounceTimeout = setTimeout(function() {
        if (searchInput.value.trim() === '') {
          window.location.href = "{{ route('account.presensi.search') }}";
          if (presensiCard) {
            presensiCard.style.display = 'block'; // Tampilkan card jika pencarian kosong
          }
        } else {
          searchForm.submit();
          if (presensiCard) {
            presensiCard.style.display = 'none'; // Sembunyikan card jika pencarian tidak kosong
          }
        }
      }, 500); // Sesuaikan waktu debounce sesuai kebutuhan
    });

    // Pemrosesan awal saat halaman dimuat
    if (searchInput.value.trim() === '') {
      if (presensiCard) {
        presensiCard.style.display = 'block'; // Tampilkan card saat halaman dimuat jika pencarian kosong
      }
    } else {
      if (presensiCard) {
        presensiCard.style.display = 'none'; // Sembunyikan card saat halaman dimuat jika pencarian tidak kosong
      }
    }
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


<!--================== mengirim pesan ke wa ==================-->
<script>
  // SENIN HADIR
  function sendSeninHadir(employeeName, checkInTime, checkOutTime, checkInDate) {
    const userPhoneNumber = "{{ Auth::user()->telp }}"; // Assuming you're using the user's phone number here
    const message = `Hi, ${employeeName}. Anda telah melakukan presensi kehadiran pada pukul ${checkInTime} pada tanggal ${checkInDate}.`; // The message content

    // Checking if the phone number exists
    if (userPhoneNumber) {
      const url = `https://wa.me/${userPhoneNumber}?text=${encodeURIComponent(message)}`;
      window.open(url, '_blank'); // Opens the WhatsApp link in a new tab
    } else {
      alert("Nomor telepon tidak tersedia"); // Notifies the user if the phone number is not available
    }
  }
  // END

  // SENIN WARNING
  function sendSeninWarning(employeeName, checkInTime, checkOutTime, checkInDate) {
    const userPhoneNumber = "{{ Auth::user()->telp }}"; // Assuming you're using the user's phone number here
    const message = `Hi, ${employeeName}. Jangan lupa untuk melakukan presensi pulang mulai pukul 15.00 - 17.00`; // The message content

    // Checking if the phone number exists
    if (userPhoneNumber) {
      const url = `https://wa.me/${userPhoneNumber}?text=${encodeURIComponent(message)}`;
      window.open(url, '_blank'); // Opens the WhatsApp link in a new tab
    } else {
      alert("Nomor telepon tidak tersedia"); // Notifies the user if the phone number is not available
    }
  }
  // END

  // SENIN PULANG
  function sendSeninPulang(employeeName, checkInTime, checkOutTime, checkInDate) {
    const userPhoneNumber = "{{ Auth::user()->telp }}";
    const message = `Hi, ${employeeName}. Terimakasih sudah menyelesaikan presensi, anda hadir pada pukul ${checkInTime} dan pulang pada pukul ${checkOutTime} pada tanggal ${checkInDate}.`;

    if (userPhoneNumber) {
      const url = `https://wa.me/${userPhoneNumber}?text=${encodeURIComponent(message)}`;
      window.open(url, '_blank');
    } else {
      alert("Nomor telepon tidak tersedia");
    }
  }
  // END

  // SELASA LIBUR
  // function sendSelasaLibur(employeeName, checkInTime, checkOutTime, checkInDate) {
  //   const userPhoneNumber = "{{ Auth::user()->telp }}"; // Assuming you're using the user's phone number here
  //   const message = `Hi, ${employeeName}. Hari ini kamu libur lo.. jangan lupa liburan dan jaga kesehatan ya!`; // The message content

  //   // Checking if the phone number exists
  //   if (userPhoneNumber) {
  //     const url = `https://wa.me/${userPhoneNumber}?text=${encodeURIComponent(message)}`;
  //     window.open(url, '_blank'); // Opens the WhatsApp link in a new tab
  //   } else {
  //     alert("Nomor telepon tidak tersedia"); // Notifies the user if the phone number is not available
  //   }
  // }
  function sendSelasaLibur(employeeName, checkInTime, checkOutTime, checkInDate) {
    // Get the user ID from the row data attribute
    const userId = $('#row-' + {
      {
        $hasil - > id
      }
    }).data('user-id');

    // Make an AJAX request to fetch the user's phone number
    $.ajax({
      url: `/account/get-user-phone/${userId}`,
      method: 'GET',
      success: function(response) {
        const userPhoneNumber = response.phone_number;

        // Now you have the user's phone number, proceed with your existing code
        const message = `Hi, ${employeeName}. Anda telah melakukan presensi kehadiran pada pukul ${checkInTime} pada tanggal ${checkInDate}.`;

        // Checking if the phone number exists
        if (userPhoneNumber) {
          const url = `https://wa.me/${userPhoneNumber}?text=${encodeURIComponent(message)}`;
          window.open(url, '_blank'); // Opens the WhatsApp link in a new tab
        } else {
          alert("Nomor telepon tidak tersedia"); // Notifies the user if the phone number is not available
        }
      },
      error: function(error) {
        console.error('Error fetching user phone number:', error);
      }
    });
  }

  // END

  // KAMIS HADIR
  function sendKamisHadir(employeeName, checkInTime, checkOutTime, checkInDate) {
    const userPhoneNumber = "{{ Auth::user()->telp }}"; // Assuming you're using the user's phone number here
    const message = `Hi, ${employeeName}. Anda telah melakukan presensi kehadiran pada pukul ${checkInTime} pada tanggal ${checkInDate}.`; // The message content

    // Checking if the phone number exists
    if (userPhoneNumber) {
      const url = `https://wa.me/${userPhoneNumber}?text=${encodeURIComponent(message)}`;
      window.open(url, '_blank'); // Opens the WhatsApp link in a new tab
    } else {
      alert("Nomor telepon tidak tersedia"); // Notifies the user if the phone number is not available
    }
  }
  // END

  // KAMIS WARNING
  function sendKamisWarning(employeeName, checkInTime, checkOutTime, checkInDate) {
    const userPhoneNumber = "{{ Auth::user()->telp }}"; // Assuming you're using the user's phone number here
    const message = `Hi, ${employeeName}. Jangan lupa untuk melakukan presensi pulang mulai pukul 18.00 - 20.00`; // The message content

    // Checking if the phone number exists
    if (userPhoneNumber) {
      const url = `https://wa.me/${userPhoneNumber}?text=${encodeURIComponent(message)}`;
      window.open(url, '_blank'); // Opens the WhatsApp link in a new tab
    } else {
      alert("Nomor telepon tidak tersedia"); // Notifies the user if the phone number is not available
    }
  }
  // END

  // KAMIS PULANG
  function sendKamisPulang(employeeName, checkInTime, checkOutTime, checkInDate) {
    const userPhoneNumber = "{{ Auth::user()->telp }}"; // Assuming you're using the user's phone number here
    const message = `Hi, ${employeeName}. Terimakasih sudah menyelesaikan presensi, anda hadir pada pukul ${checkInTime} dan pulang pada pukul ${checkOutTime} pada tanggal ${checkInDate}.`; // The message content

    // Checking if the phone number exists
    if (userPhoneNumber) {
      const url = `https://wa.me/${userPhoneNumber}?text=${encodeURIComponent(message)}`;
      window.open(url, '_blank'); // Opens the WhatsApp link in a new tab
    } else {
      alert("Nomor telepon tidak tersedia"); // Notifies the user if the phone number is not available
    }
  }
  // END

  // JUMAT, SABTU, MINGGU HADIR
  function sendJumatHadir(employeeName, checkInTime, checkOutTime, checkInDate) {
    const userPhoneNumber = "{{ Auth::user()->telp }}"; // Assuming you're using the user's phone number here
    const message = `Hi, ${employeeName}. Anda telah melakukan presensi kehadiran pada pukul ${checkInTime} pada tanggal ${checkInDate}.`; // The message content

    // Checking if the phone number exists
    if (userPhoneNumber) {
      const url = `https://wa.me/${userPhoneNumber}?text=${encodeURIComponent(message)}`;
      window.open(url, '_blank'); // Opens the WhatsApp link in a new tab
    } else {
      alert("Nomor telepon tidak tersedia"); // Notifies the user if the phone number is not available
    }
  }
  // END

  // JUMAT, SABTU, MINGGU WARNING
  function sendJumatWarning(employeeName, checkInTime, checkOutTime, checkInDate) {
    const userPhoneNumber = "{{ Auth::user()->telp }}"; // Assuming you're using the user's phone number here
    const message = `Hi, ${employeeName}. Jangan lupa untuk melakukan presensi pulang mulai pukul 18.00 - 20.00`; // The message content

    // Checking if the phone number exists
    if (userPhoneNumber) {
      const url = `https://wa.me/${userPhoneNumber}?text=${encodeURIComponent(message)}`;
      window.open(url, '_blank'); // Opens the WhatsApp link in a new tab
    } else {
      alert("Nomor telepon tidak tersedia"); // Notifies the user if the phone number is not available
    }
  }
  // END

  // JUMAT, SABTU, MINGGU PULANG
  function sendJumatPulang(employeeName, checkInTime, checkOutTime, checkInDate) {
    const userPhoneNumber = "{{ Auth::user()->telp }}"; // Assuming you're using the user's phone number here
    const message = `Hi, ${employeeName}. Terimakasih sudah menyelesaikan presensi, anda hadir pada pukul ${checkInTime} dan pulang pada pukul ${checkOutTime} pada tanggal ${checkInDate}.`; // The message content

    // Checking if the phone number exists
    if (userPhoneNumber) {
      const url = `https://wa.me/${userPhoneNumber}?text=${encodeURIComponent(message)}`;
      window.open(url, '_blank'); // Opens the WhatsApp link in a new tab
    } else {
      alert("Nomor telepon tidak tersedia"); // Notifies the user if the phone number is not available
    }
  }
  // END
</script>
<!--================== end ==================-->

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