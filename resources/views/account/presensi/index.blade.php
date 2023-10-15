@extends('layouts.account')

@section('title')
List Presensi Karyawan | MANAGEMENT
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div id="realtime-container">
      <div class="section-header">
        <h1>PRESENSI KARYAWAN</h1>
      </div>

      <div class="section-body">

        <!-- jika maintenace aktif -->
        @if (!$maintenances->isEmpty())
        @foreach($maintenances as $maintenance)
        @if ($maintenance->status === 'aktif' || ($maintenance->end_date !== null && now() <= Carbon\Carbon::parse($maintenance->end_date)->endOfDay()))
          <div class="alert alert-danger" role="alert" style="text-align: center; background-image: url('{{ asset('/images/background-maintenance.png') }}'">


            <b style="font-size: 25px; text-transform:uppercase">{{ $maintenance->title }}</b><br>
            <img style="width: 100px; height:100px;" src="{{ asset('images/' . $maintenance->gambar) }}" alt="Gambar Presensi" class="img-thumbnail">
            <p style="font-size: 20px;" class="mt-2">{{ $maintenance->note }}</p>
            <p style="font-size: 15px;">Dari Tanggal {{ \Carbon\Carbon::parse($maintenance->start_date)->isoFormat('D MMMM YYYY HH:mm') }} - {{ \Carbon\Carbon::parse($maintenance->end_date)->isoFormat('D MMMM YYYY HH:mm') }}</p>


          </div </ </div>
          @endif
          @endforeach
          @endif
          <!-- end -->

          <div class="card">
            <div class="card-header  text-right">
              <h4><i class="fas fa-list"></i> LIST PRESENSI KARYAWAN</h4>
              @if (Auth::user()->level == 'karyawan')
              @else
              <!--<div class="card-header-action">
            <a href="{{ route('account.laporan_gaji.download-pdf') }}" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Download PDF</a>
          </div>-->
              @endif
            </div>

            <div class="card-body">
              <form action="{{ route('account.presensi.search') }}" method="GET">
                <div class="form-group">
                  <div class="input-group mb-3">
                    @if (Auth::user()->level == 'karyawan' || Auth::user()->level == 'staff')
                    @php
                    $todayPresensi = \App\Presensi::where('user_id', Auth::user()->id)
                    ->whereDate('created_at', now()->toDateString())
                    ->first();
                    @endphp
                    <td class="text-center">
                      @if ($todayPresensi)
                      <a href="{{ route('account.presensi.edit', $todayPresensi->id) }}" class="btn btn-sm btn-warning" style="padding-top: 10px;">
                        <i class="fa fa-pencil-alt"></i> UPDATE
                      </a>
                      @else
                      <a href="{{ route('account.presensi.create') }}" class="btn btn-primary" style="padding-top: 10px;">
                        <i class="fa fa-plus-circle"></i> TAMBAH
                      </a>
                      @endif
                    </td>
                    @endif

                    @if (Auth::user()->level == 'manager')
                    <div class="input-group-prepend">
                      <a href="{{ route('account.presensi.create') }}" class="btn btn-primary" style="padding-top: 10px;"><i class="fa fa-plus-circle"></i> TAMBAH</a>
                    </div>
                    @endif
                    <input type="text" class="form-control" name="q" placeholder="PENCARIAN" value="{{ app('request')->input('q') }}">
                    <div class="input-group-append">
                      <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> CARI
                      </button>
                    </div>
                    @if(request()->has('q'))
                    <a href="{{ route('account.presensi.index') }}" class="btn btn-danger ml-1">
                      <i class="fa fa-times-circle mt-2"></i> HAPUS PENCARIAN
                    </a>
                    @endif
                  </div>
                </div>
              </form>

              <form action="{{ route('account.presensi.index') }}" method="GET">
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
                      <button class="btn btn-primary mr-1" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                      <a href="{{ route('account.presensi.index') }}" class="btn btn-danger" style="margin-top: 30px;">
                        <i class="fa fa-times-circle mt-2"></i> HAPUS
                      </a>
                    </div>
                    @else
                    <button class="btn btn-primary mr-1 btn-block" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                    @endif
                  </div>

                </div>
            </div>
            </form>
          </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-list"></i> LIST PRESENSI KARYAWAN</h4>
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
                <th scope="col" rowspan="2" class="column-width" style="text-align: center;">BUKTI PRESENSI</th>
                <th scope="col" rowspan="2" style="text-align: center">AKSI</th>
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
                <td class="column-width" style="text-align: center;">
                  <!-- {{ date('d-m-Y H:i', strtotime($hasil->created_at)) }} <br> -->
                  {{ strftime('%A, %d %B %Y %H:%M', strtotime($hasil->created_at)) }}
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
                  <span class="badge badge-success">HADIR</span>
                  @elseif ($hasil->status == 'remote')
                  <span class="badge badge-info">REMOTE</span>
                  @elseif ($hasil->status == 'izin')
                  <span class="badge badge-warning">IZIN</span>
                  @elseif ($hasil->status == 'dinas luar kota')
                  <span class="badge badge-info">DINAS LUAR KOTA</span>
                  @elseif ($hasil->status == 'lembur')
                  <span class="badge badge-primary">LEMBUR</span>
                  @elseif ($hasil->status == 'cuti')
                  <span class="badge badge-warning">CUTI</span>
                  @elseif ($hasil->status == 'terlambat')
                  <span class="badge badge-danger">TERLAMBAT</span>
                  @elseif ($hasil->status == 'pulang')
                  <span class="badge badge-danger">PULANG</span>
                  @endif
                  <br>
                  @if ($hasil->status_pulang == 'hadir')
                  <span class="badge badge-success mt-2">HADIR</span>
                  @elseif ($hasil->status_pulang == 'remote')
                  <span class="badge badge-info mt-2">REMOTE</span>
                  @elseif ($hasil->status_pulang == 'izin')
                  <span class="badge badge-warning mt-2">IZIN</span>
                  @elseif ($hasil->status_pulang == 'dinas luar kota')
                  <span class="badge badge-info mt-2">DINAS LUAR KOTA</span>
                  @elseif ($hasil->status_pulang == 'lembur')
                  <span class="badge badge-primary mt-2">LEMBUR</span>
                  @elseif ($hasil->status_pulang == 'cuti')
                  <span class="badge badge-warning mt-2">CUTI</span>
                  @elseif ($hasil->status_pulang == 'terlambat')
                  <span class="badge badge-danger mt-2">TERLAMBAT</span>
                  @elseif ($hasil->status_pulang == 'pulang')
                  <span class="badge badge-danger mt-2">PULANG</span>
                  @endif
                </td>
                <td class="column-width" style="text-align: center;">
                  <a href="{{ asset('images/' . $hasil->gambar) }}" data-lightbox="{{ $hasil->id }}">
                    <div class="thumbnail-circle">
                      <img style="width: 100px; height:100px;" src="{{ asset('images/' . $hasil->gambar) }}" alt="Gambar Presensi" class="img-thumbnail rounded-circle">
                    </div>
                  </a>
                </td>
                @if (Auth::user()->level == 'karyawan' || Auth::user()->level == 'staff')
                <td class="text-center">
                  <a href="{{ route('account.presensi.detail', $hasil->id) }}" class="btn btn-sm btn-warning">
                    <i class="fa fa-eye"></i>
                  </a>
                </td>
                @else
                <td class="text-center">
                  <a href="{{ route('account.presensi.edit', $hasil->id) }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-pencil-alt"></i>
                  </a>
                  <button onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger">
                    <i class="fa fa-trash"></i>
                  </button>
                  <a href="{{ route('account.presensi.detail', $hasil->id) }}" class="btn btn-sm btn-warning">
                    <i class="fa fa-eye"></i>
                  </a>
                </td>
                @endif
              </tr>
              @php
              $no++;
              @endphp
              @endforeach
            </tbody>
          </table>
          <div style="text-align: center">
            {{$presensi->links("vendor.pagination.bootstrap-4")}}
          </div>

        </div>

      </div>
    </div>
</div>
</div>
</section>
</div>

<!-- reload data ketika success -->
<script>
  @if(Session::has('success'))
  // Menggunakan setTimeout untuk menunggu pesan sukses muncul sebelum melakukan refresh
  setTimeout(function() {
    window.location.reload();
  }, 1000); // Refresh halaman setelah 2 detik
  @endif
</script>
<!-- end -->

<script>
  //@if($message = Session::get('success'))
  //swal({
  //  type: "success",
  //  icon: "success",
  //  title: "BERHASIL!",
  //  text: "{{ $message }}",
  //  timer: 1500,
  //  showConfirmButton: false,
  //  showCancelButton: false,
  //  buttons: false,
  //});
  //@elseif($message = Session::get('error'))
  //swal({
  //  type: "error",
  //  icon: "error",
  //  title: "GAGAL!",
  //  text: "{{ $message }}",
  //  timer: 1500,
  //  showConfirmButton: false,
  //  showCancelButton: false,
  //  buttons: false,
  //});
  //@endif

  // delete
  // delete
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

@stop