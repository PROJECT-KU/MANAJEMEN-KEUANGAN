<div class="main-content">
  <section class="section">
    <center>
      <img src="{{ $src }}" alt="Logo" height="45px">
    </center>

    <center>
      <div class="section-header">
        <h1>LIST GAJI KARYAWAN</h1>
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
                <th scope="col" class="column-width" style="text-align: center; width:150px">ID TRANSAKSI</th>
                <th scope="col" class="column-width" style="text-align: center; width:200px">NAMA KARYAWAN</th>
                <!--<th scope="col" class="column-width" style="text-align: center;">NIK</th>-->
                <th scope="col" class="column-width" style="text-align: center; width:250px">NO REKENING</th>
                <th scope="col" class="column-width" style="text-align: center;">BANK</th>
                <th scope="col" class="column-width" style="text-align: center; width:200px">TOTAL GAJI</th>
                <th scope="col" class="column-width" style="text-align: center; width:150px">TANGGAL PEMBAYARAN</th>
                <!-- <th scope="col" class="column-width" style="text-align: center; width:150px">STATUS PEMBAYARAN</th> -->
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
              @continue
              @else
              @if ($hasil->status == 'terbayar')
              <tr>
                <th scope="row" style="text-align: center">{{ $no }}</th>
                <td class="column-width" style="text-align: center;">{{ $hasil->id_transaksi }}</td>
                <td class="column-width" style="text-align: center;">{{ $hasil->full_name }}</td>
                <!--<td class="column-width" style="text-align: center;">{{ $hasil->nik }}</td>-->
                <td class="column-width" style="text-align: center;">{{ $hasil->norek }}</td>
                <td class="column-width" style="text-align: center;">
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
                <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->total, 0, ',', '.') }}</td>
                <td class="column-width" style="text-align: center;">{{ date('d-m-Y H:i', strtotime($hasil->tanggal)) }}</td>
                <!-- <td class="column-width" style="text-align: center;">
                  @if($hasil->status == 'pending')
                  <button type="button" class="btn btn-warning">PENDING</button>
                  @else
                  <button type="button" class="btn btn-success">TERBAYAR</button>
                  @endif
                </td> -->
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
          </table><br><br>
          <hr>
          <center>
            <div class="mt-5">
              <h3><b>TOTAL GAJI KARYAWAN</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Rp. {{ number_format($totalGajiTerbayar, 0, ',', ',') }}</h3>
              <p><i>{{ $terbilangterbayar }}</i></p>
              <!-- <h3><b>TOTAL GAJI KARYAWAN</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Rp. {{ number_format($totalGaji, 0, ',', ',') }}</h3>
              <p><i>{{ $terbilang }}</i></p> -->
            </div>
          </center>
          <hr>
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