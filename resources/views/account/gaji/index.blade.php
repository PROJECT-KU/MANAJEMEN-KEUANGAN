@extends('layouts.account')

@section('title')
List Gaji Karyawan | MANAGEMENT
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>GAJI KARYAWAN</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <div class="card-header  text-right">
          <h4><i class="fas fa-list"></i> LIST GAJI KARYAWAN</h4>
          @if (Auth::user()->level == 'karyawan')
          @else
          <div class="card-header-action">
            <a href="{{ route('account.laporan_gaji.download-pdf') }}" id="generate-pdf-btn" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Download PDF</a>
          </div>
          @endif
        </div>

        <div class="card-body">
          <form action="{{ route('account.gaji.search') }}" method="GET">
            <div class="form-group">
              <div class="input-group mb-3">
                @if (Auth::user()->level == 'karyawan')
                @else
                <div class="input-group-prepend">
                  <a href="{{ route('account.gaji.create') }}" class="btn btn-primary" style="padding-top: 10px;"><i class="fa fa-plus-circle"></i> TAMBAH</a>
                </div>
                @endif
                <input type="text" class="form-control" name="q" placeholder="PENCARIAN" value="{{ app('request')->input('q') }}">
                <div class="input-group-append">
                  <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> CARI
                  </button>
                </div>
                @if(request()->has('q'))
                <a href="{{ route('account.gaji.index') }}" class="btn btn-danger">
                  <i class="fa fa-times-circle mt-2"></i> HAPUS PENCARIAN
                </a>
                @endif
              </div>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th scope="col" style="text-align: center;width: 6%">NO.</th>
                  <th scope="col" class="column-width" style="text-align: center;">ID TRANSAKSI</th>
                  <th scope="col" class="column-width" style="text-align: center;">NAMA KARYAWAN</th>
                  <!--<th scope="col" class="column-width" style="text-align: center;">NIK</th>-->
                  <th scope="col" class="column-width" style="text-align: center;">NO REKENING</th>
                  <!--<th scope="col" class="column-width" style="text-align: center;">BANK</th>-->
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
                @if (Auth::user()->level == 'karyawan' && $hasil->status == 'pending')
                <!-- Skip displaying records where user is karyawan and status is pending -->
                @else
                <tr>
                  <th scope="row" style="text-align: center">{{ $no }}</th>
                  <td class="column-width" style="text-align: center;">{{ $hasil->id_transaksi }}</td>
                  <td class="column-width" style="text-align: center;">{{ $hasil->full_name }}</td>
                  <!--<td class="column-width" style="text-align: center;">{{ $hasil->nik }}</td>-->
                  <td class="column-width" style="text-align: center;">{{ $hasil->norek }}</td>
                  <!--<td class="column-width" style="text-align: center;">
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
                  </td>-->
                  <td class="column-width" style="text-align: center; width:150px">Rp. {{ number_format($hasil->total, 0, ',', '.') }}</td>
                  <td class="column-width" style="text-align: center; width:200px">
                    {{ strftime('%d %B %Y %H:%M', strtotime($hasil->tanggal)) }}
                  </td>

                  <td class="column-width" style="text-align: center;">
                    @if($hasil->status == 'pending')
                    <span class="badge badge-warning">PENDING</span>
                    @else
                    <span class="badge badge-success">TERBAYAR</span>
                    @endif
                  </td>
                  @if (Auth::user()->level == 'karyawan')
                  <td class="text-center">
                    <a href="{{ route('account.gaji.detail', $hasil->id) }}" class="btn btn-sm btn-warning">
                      <i class="fa fa-eye"></i>
                    </a>
                    <a href="{{ route('account.laporan_gaji.Slip-Gaji', $hasil->id) }}" class="btn btn-sm btn-info">
                      <i class="fa fa-download"></i> Slip Gaji
                    </a>
                  </td>
                  @else
                  <td class="text-center">
                    <a href="{{ route('account.gaji.edit', $hasil->id) }}" class="btn btn-sm btn-primary">
                      <i class="fa fa-pencil-alt"></i>
                    </a>
                    <button onclick="Delete('{{ $hasil->id }}')" class="btn btn-sm btn-danger">
                      <i class="fa fa-trash"></i>
                    </button>
                    <a href="{{ route('account.gaji.detail', $hasil->id) }}" class="btn btn-sm btn-warning">
                      <i class="fa fa-eye"></i>
                    </a>
                    <a href="{{ route('account.laporan_gaji.Slip-Gaji', $hasil->id) }}" class="btn btn-sm btn-info mt-2 mb-2">
                      <i class="fa fa-download"></i> Slip Gaji
                    </a>
                  </td>
                  @endif
                </tr>
                @php
                $no++;
                $terbayarCount++;
                @endphp
                @endif
                @endforeach
              </tbody>
            </table>
            <div style="text-align: center">
              {{$gaji->links("vendor.pagination.bootstrap-4")}}
            </div>

          </div>

        </div>
      </div>
    </div>
  </section>
</div>

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

<!-- tabel in pdf -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>

<script>
  document.getElementById("generate-pdf-btn").addEventListener("click", function() {
    const doc = new jsPDF();

    // Add a title to the PDF
    doc.text("List Gaji Karyawan", 105, 15, {
      align: "center"
    });

    // Define the table's headers and data
    const headers = ["NO.", "ID TRANSAKSI", "NAMA KARYAWAN", "NO REKENING", "TOTAL GAJI", "TANGGAL PEMBAYARAN", "STATUS PEMBAYARAN"];
    const data = [];

    // Loop through the table rows and add data to the data array
    const tableRows = document.querySelectorAll(".table tbody tr");
    tableRows.forEach((row) => {
      const rowData = [];
      row.querySelectorAll("td").forEach((cell) => {
        rowData.push(cell.textContent.trim());
      });
      data.push(rowData);
    });

    // Add the table to the PDF
    doc.autoTable({
      head: [headers],
      body: data,
      startY: 25,
    });

    // Save the PDF
    doc.save("gaji_karyawan.pdf");
  });
</script>
<!-- end -->
@stop