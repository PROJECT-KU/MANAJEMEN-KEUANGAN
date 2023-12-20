<div class="main-content">
  <section class="section">
    <center>
      <div class="section-header">
        <h1>LIST LAPORAN PRESENSI</h1>
        <div class="section-header">
          <center>
            <p style="margin-top: -3px; font-size: 15px"><strong>Periode
                @if ($startDate && $endDate)
                {{ date('d F Y', strtotime($startDate)) }} - {{ date('d F Y', strtotime($endDate)) }}
                @else
                {{ date('F Y') }}
                @endif
              </strong>
            </p>
            <hr>
            <h4>{{ $user->alamat_company }}</h4>
            <h4>Email : {{ $user->email_company }} Telp : {{ $user->telp_company }}</h4>
          </center>
        </div>
      </div>
    </center>
    <hr><br><br>

    <div class="section-body">
      <div class="card">
        <div class="card-body">
          <table class="table table-striped">
            <thead>
              <tr>
                <th scope="col" rowspan="2" style="text-align: center;width: 6%">NO.</th>
                <th scope="col" rowspan="2" class="column-width" style="text-align: center; width:230px">NAMA KARYAWAN</th>
                <th scope="col" rowspan="2" class="column-width" style="text-align: center; width:230px">TANGGAL PRESENSI</th>
                <th scope="col" colspan="2" class="column-width" style="text-align: center; width:200px">KEHADIRAN</th>
                <th scope="col" rowspan="2" class="column-width" style="text-align: center; width:160px">LAMA KERJA</th>
                <th scope="col" rowspan="2" class="column-width" style="text-align: center; width:160px">STATUS PRESENSI</th>
              </tr>
              <tr>
                <th scope="col" style="text-align: center; width:100px">HADIR</th>
                <th scope="col" style="text-align: center; width:100px">PULANG</th>
              </tr>
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
                  <span class="badge badge-info mt-2">PERJALANAN LUAR KOTA (Di dalam Jawa)</span>
                  @elseif ($hasil->status == 'perjalanan luar kota luar jawa')
                  <span class="badge badge-info mt-2">PERJALANAN LUAR KOTA (Di luar Jawa)</span>
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
                  <span class="badge badge-info">PERJALANAN LUAR KOTA (Di dalam Jawa)</span>
                  @elseif ($hasil->status_pulang == 'perjalanan luar kota luar jawa')
                  <span class="badge badge-info">PERJALANAN LUAR KOTA (Di luar Jawa)</span>
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
              </tr>
              @php
              $no++;
              @endphp
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>
@extends('layouts.version')
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