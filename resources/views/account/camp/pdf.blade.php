<div class="main-content">
  <section class="section">
    <center>
      <div class="section-header">
        <h1>LIST LAPORAN CAMP</h1>
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
              </tr>
            </thead>
            <tbody>
              @php
              $no = 1;
              $terbayarCount = 0; // Count of terbayar records
              @endphp
              @foreach ($camp as $hasil)
              @if ((Auth::user()->level == 'karyawan' || Auth::user()->level == 'trainer') && $hasil->status == 'pending')
              <!-- Skip displaying records where user is karyawan and status is pending -->
              @continue
              @else
              @if ($hasil->status == 'terbayar')
              <tr>
                <th scope="row" style="text-align: center">{{ $no }}</th>
                <td class="column-width" style="text-align: center; text-transform:uppercase;"">{{ $hasil->title }} #{{ $hasil->camp_ke }}</td>
                <td class=" column-width" style="text-align: center;">Rp. {{ number_format($hasil->total_uang_masuk, 0, ',', '.') }}</td>
                <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->total, 0, ',', '.') }}</td>
                <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->keuntungan, 0, ',', '.') }}</td>
                <td class="column-width" style="text-align: center;">{{ number_format($hasil->persentase_keuntungan, 0, ',', '.') }}%</td>
                <td class="column-width" style="text-align: center; width:200px">
                  {{ strftime('%d %B %Y', strtotime($hasil->tanggal)) }} <br>
                  s/d<br>
                  {{ strftime('%d %B %Y', strtotime($hasil->tanggal_akhir)) }}
                </td>
              </tr>
              @endif
              @php
              if ($hasil->status == 'terbayar') {
              $no++; // Increment the number only for terbayar records
              $terbayarCount++;
              }
              @endphp
              @endif
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