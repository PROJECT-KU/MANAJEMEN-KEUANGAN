<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Slip Laporan Percamp | MANAGEMENT</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body>
  <form action="{{ route('account.camp.store') }}" method="GET" enctype="multipart/form-data">
    @csrf
    <div class="wrapper">
      <img src="{{ asset('images/' . $user->logo_company) }}" alt="Company Logo" style="max-width: 100px;">
      <section class="invoice">
        <div class="row">
          <div class="col-12">
            <h2 class="page-header">
              <center><i class="fas fa-globe"></i> SLIP LAPORAN PERCAMP<br>
                <p style="margin-top: -3px; font-size: 15px"><strong>Periode</strong>
                  <?php
                  $tanggalPembayaran = strtotime($camp->tanggal);
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
                  <th scope="col" style="text-align: left;  width:250px"><b>ID Transaksi : {{ $camp->id_transaksi }}</b>
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
                  <td style="margin-top: -200px;"><strong>Camp {{ $camp->title }} #{{ $camp->camp_ke }}</strong><br>
                    Mulai Camp: {{ date('d F Y', strtotime($camp->tanggal)) }}<br>
                    Selesai Camp: {{ date('d F Y', strtotime($camp->tanggal_akhir)) }}<br>
                    Jumlah Peserta : {{ $camp->peserta }}<br>
                    Keuntungan : {{ rtrim(rtrim(number_format($camp->persentase_keuntungan, 1, ',', '.'), '0'), ',') }}%<br>
                    Tanggal : {{ date('d F Y') }}
                  </td>
                  <td>

                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div><br><br>

        <div class="row">
          <div class="col-12 table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th scope="col" style="text-align: left; width:225px; margin-left:100px;">
                    <u>Pemasukan</u>
                  </th>
                  <th></th>
                  <th scope="col" style="text-align: left; width:225px"><u>Pengeluaran</u></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td style="text-align: left; width:145px">Uang Masuk</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($camp->uang_masuk, 0, ',', '.') }}</td>

                  <td style="text-align: left; width:145px">Gaji Trainer</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($camp->total_gaji_trainer, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px">Uang Masuk Lain-lain</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($camp->lain_lain, 0, ',', '.') }}</td>

                  <td style="text-align: left; width:145px">Gaji Team</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($camp->total_gaji_team, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px"></td>
                  <td style="text-align: left; width:145px"></td>

                  <td style="text-align: left; width:145px">Team Cabang</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($camp->team_cabang, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px"></td>
                  <td style="text-align: left; width:145px"></td>

                  <td style="text-align: left; width:145px">Booknote</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($camp->booknote, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px"></td>
                  <td style="text-align: left; width:145px"></td>

                  <td style="text-align: left; width:145px">Grammarly</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($camp->grammarly, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px"></td>
                  <td style="text-align: left; width:145px"></td>

                  <td style="text-align: left; width:145px">Hotel</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($camp->Hotel, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px"></td>
                  <td style="text-align: left; width:145px"></td>

                  <td style="text-align: left; width:145px">Konsumsi Tambahan</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($camp->konsumsi_tambahan, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px"></td>
                  <td style="text-align: left; width:145px"></td>

                  <td style="text-align: left; width:145px">Lain-Lain</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($camp->lainnya, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px"></td>
                  <td style="text-align: left; width:145px"></td>

                  <td style="text-align: left; width:145px">Total Tiket Trainer Berangkat</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($camp->total_tiket_trainer_berangkat, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px"></td>
                  <td style="text-align: left; width:145px"></td>

                  <td style="text-align: left; width:145px">Total Tiket Trainer Pulang</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($camp->total_tiket_trainer_pulang, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px"></td>
                  <td style="text-align: left; width:145px"></td>

                  <td style="text-align: left; width:145px">total Tiket Team Berangkat</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($camp->total_tiket_team_berangkat, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px"></td>
                  <td style="text-align: left; width:145px"></td>

                  <td style="text-align: left; width:145px">total Tiket Team Pulang</td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($camp->total_tiket_team_pulang, 0, ',', '.') }}</td>
                </tr>
                <tr>
                  <td style="text-align: left; width:145px"><b>Total Uang Masuk</b></td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($camp->total_uang_masuk, 0, ',', '.') }}</td>

                  <td style="text-align: left; width:145px"><b>Total Uang Keluar</b></td>
                  <td style="text-align: left; width:145px">Rp. {{ number_format($camp->total, 0, ',', '.') }}</td>
                </tr>
              </tbody>

            </table>
            <br>
            <hr>
            <center>
              <h3><b>Total Keuntungan </b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Rp. {{ number_format($camp->keuntungan, 0, ',', '.') }}</h3>
              <p><i>{{ $terbilang }}</i></p>
            </center>
            <hr>
          </div>
        </div>

        <div class="row">
          <div class="col-6" style="float: right;">
            <center>
              <p class="lead">Yogyakarta, {{ date('j F Y', strtotime($camp->tanggal)) }}</p>
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