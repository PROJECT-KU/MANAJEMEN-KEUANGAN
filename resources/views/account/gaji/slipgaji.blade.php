<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Slip Gaji Karyawan | MANAGEMENT</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body>
  <form action="{{ route('account.gaji.store') }}" method="GET" enctype="multipart/form-data">
    @csrf
    <div class="wrapper">
      <section class="invoice">
        <div class="row">
          <div class="col-12">
            <center> <img src="data:image/png;base64,{{ base64_encode(file_get_contents($userLogoPath)) }}" alt="Logo" height="45px"></center>
            <h2 class="page-header">
              <center><i class="fas fa-globe"></i> SLIP GAJI KARYAWAN<br>
                <p style="margin-top: -3px; font-size: 15px"><strong>Periode</strong>
                  <?php
                  $tanggalPembayaran = strtotime($gaji->tanggal);
                  $awalBulan = date('j F, Y', strtotime('first day of this month', $tanggalPembayaran));
                  $akhirBulan = date('j F, Y', strtotime('last day of this month', $tanggalPembayaran));
                  echo $awalBulan . ' - ' . $akhirBulan;
                  ?>
                </p>
              </center>
            </h2>
          </div>
        </div>
        <hr>
        <br>

        <div class="row">
          <div class="col-12 table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th scope="col" style="text-align: left; width:300px">Dari</th>
                  <th scope="col" style="text-align: left; width:250px">Untuk</th>
                  <th scope="col" style="text-align: left;  width:250px"><b>ID Transaksi : {{ $gaji->id_transaksi }}</b>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td> <strong style="text-transform:uppercase">{{ $user->company }}</strong><br>
                    {{ $user->alamat_company }}<br>
                    Phone: {{ $user->telp_company }}<br>
                    Email: {{ $user->email_company }}
                  </td>
                  <td style="margin-top: -200px;"><strong>{{ $employee->full_name }}</strong><br>
                    Jabatan: {{ $employee->level }}<br>
                    Phone: {{ $employee->telp }}<br>
                    Email: {{ $employee->email }}<br>
                    <b>Pembayaran : </b>{{ date('j F Y', strtotime($gaji->tanggal)) }}<br>
                    <b>Pukul &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : </b>{{ date('H:i', strtotime($gaji->tanggal)) }}<br>
                  </td>
                  <!-- <td>
                    <b>No Rekening :</b> {{ $userWithNorekBank->norek }}<br>
                    <b>Bank&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</b>
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
                    @if (array_key_exists($userWithNorekBank->bank, $bankNames))
                    {{ $bankNames[$userWithNorekBank->bank] }}
                    @else
                    Bank Name Not Found
                    @endif
                    <br>
                    <b>Pembayaran : </b>{{ date('j F Y', strtotime($gaji->tanggal)) }}<br>
                    <b>Pukul &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : </b>{{ date('H:i', strtotime($gaji->tanggal)) }}<br>
                    <b>Tanggal &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </b>{{ date('d F Y') }}
                  </td> -->
                </tr>
              </tbody>
            </table>
          </div>
        </div><br><br>

        <div class="row">
          <div class="col-12 table-responsive">
            <table class="table table-striped">
              <!-- <thead>
                  <tr>
                    <th scope="col" style="text-align: center; width:145px">Gaji Pokok</th>
                    <th scope="col" style="text-align: center; width:145px">Lemburan</th>
                    <th scope="col" style="text-align: center; width:145px">Bonus</th>
                    <th scope="col" style="text-align: center; width:145px">Tunjangan BPJS</th>
                    <th scope="col" style="text-align: center; width:145px">Tunjangan THR</th>
                    <th scope="col" style="text-align: center; width:145px">Tunjangan Lainnya</th>
                    <th scope="col" style="text-align: center; width:145px">Potongan</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td scope="col" style="text-align: center; width:145px">Rp. {{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</td>
                    <td scope="col" style="text-align: center; width:145px">Rp. {{ number_format($gaji->total_lembur, 0, ',', '.') }}</td>
                    <td scope="col" style="text-align: center; width:145px">Rp. {{ number_format($gaji->total_bonus, 0, ',', '.') }}</td>
                    <td scope="col" style="text-align: center; width:145px">Rp. {{ number_format($gaji->tunjangan_bpjs, 0, ',', '.') }}</td>
                    <td scope="col" style="text-align: center; width:145px">Rp. {{ number_format($gaji->tunjangan_thr, 0, ',', '.') }}</td>
                    <td scope="col" style="text-align: center; width:145px">Rp. {{ number_format($gaji->tunjangan, 0, ',', '.') }}</td>
                    <td scope="col" style="text-align: center; width:145px">Rp. {{ number_format($gaji->potongan, 0, ',', '.') }}</td>
                  </tr>
                </tbody> -->
              <thead>
                <tr>
                  <th scope="col" style="text-align: left; width:225px; margin-left:100px;">
                    <u>Penghasilan</u>
                  </th>
                  <th></th>
                  <th scope="col" style="text-align: left; width:225px"><u>Potongan</u></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td style="text-align: left; width:145px">Gaji Pokok</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</td>

                  <td style="text-align: left; width:145px">Potongan</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($gaji->potongan, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px">Lemburan</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($gaji->total_lembur, 0, ',', '.') }}</td>

                  <td style="text-align: left; width:145px">PPH 21</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($gaji->pph, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px">Bonus</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($gaji->total_bonus, 0, ',', '.') }}</td>

                  <td style="text-align: left; width:145px">Alpha</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($gaji->alpha, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px">Tunjangan Kesehatan</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($gaji->tunjangan_bpjs, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px">Tunjangan THR</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($gaji->tunjangan_thr, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px">Tunjangan Pulsa</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($gaji->tunjangan_pulsa, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px">Tunjangan Lainnya</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($gaji->tunjangan, 0, ',', '.') }}</td>
                </tr>
              </tbody>

            </table>
            <br>
            <hr>
            <center>
              <h3><b>Total </b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Rp. {{ number_format($gaji->total, 0, ',', '.') }}</h3>
              <p><i>{{ $terbilang }}</i></p>
            </center>
            <hr>
          </div>
        </div>

        <div class="row">
          <div class="col-6" style="float: right;">
            <center>
              <p class="lead">Yogyakarta, {{ date('j F Y', strtotime($gaji->tanggal)) }}</p>
              <p class="lead"> Manager Operasional</p><br>
              <!-- <img src="{{ asset('adminlte/dist/img/credit/mastercard.png') }}" alt="Mastercard"><br> -->
              <p class="lead">
                {{ $user->pj_company }}
              </p>
            </center>
          </div>
        </div>
      </section>
    </div>
  </form>

  <script type="text/javascript">
    window.addEventListener("load", window.print());
  </script>

  <script>
    const imageInput = document.getElementById('gambar');
    const imagePreview = document.getElementById('image-preview');

    imageInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          imagePreview.src = e.target.result;
          imagePreview.style.display = 'block'; // Show the preview
        };
        reader.readAsDataURL(file);
      }
    });
  </script>
</body>

</html>