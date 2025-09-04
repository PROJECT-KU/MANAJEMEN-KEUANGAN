<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <form action="{{ route('account.gaji.store') }}" method="GET" enctype="multipart/form-data">
        @csrf
        <div class="wrapper">
            <section class="invoice">
                <div class="row">
                    <div class="col-12">
                        <center>
                            <img src="{{ public_path('assets/img/LogoRSC.png') }}" alt="logo" width="250">
                        </center>
                        <h2 class="page-header" style="text-align: center;">
                            INVOICE ANALISIS BIBLIOMETRIK
                            <p style="margin-top: -3px; font-size: 15px; text-align: center;"><strong>Pelaksanaan</strong>
                                {{ date('j F Y', strtotime($categoriesanalisisbibliometrik->mulai)) }}
                                s/d
                                {{ date('j F Y', strtotime($categoriesanalisisbibliometrik->selesai)) }}
                            </p>
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
                                    <th scope="col" style="text-align: left;  width:250px"><b>ID Transaksi : {{ $analisisbibliometrik->id_transaksi }}</b>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td> <strong style="text-transform:uppercase">RUMAH SCOPUS FOUNDATION</strong><br>
                                        Bangunsari, Jl. Bangunsari, Bangunsari, Bangun Kerto, Turi, Sleman Regency, Special Region of Yogyakarta 55551<br>
                                        Phone: 0812-2688-3280<br>
                                        Email: info@rumahscopusfoundation.com
                                    </td>
                                    <td style="margin-top: -200px;"><strong>{{ $analisisbibliometrik->nama }}</strong><br>
                                        Affiliasi :{{ $analisisbibliometrik->affiliasi }}<br>
                                        Phone: {{ $analisisbibliometrik->telp }}<br>
                                        Email: {{ $analisisbibliometrik->email }}<br>
                                        <b>Pembayaran : </b>{{ date('j F Y', strtotime($analisisbibliometrik->created_at)) }}<br>
                                        <b>Pukul &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : </b>{{ date('H:i', strtotime($analisisbibliometrik->created_at)) }}<br>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div><br><br>

                <div class="row">
                    <div class="col-12 table-responsive">
                        <table class="table table-striped" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 40%; padding-left: 80px; text-align: left;"><u>Kategori</u></th>
                                    <th style="width: 10%;"></th> <!-- Spacer -->
                                    <th style="width: 40%; padding-right: 80px; text-align: right;"><u>Jumlah</u></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding-left: 80px;">
                                        {{ $categoriesanalisisbibliometrik->nama }} #{{ $categoriesanalisisbibliometrik->nama_ke }}
                                    </td>
                                    <td></td>
                                    <td style="padding-right: 80px; text-align: right;">
                                        {{ $analisisbibliometrik->jumlah_pendaftar }} Orang
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 80px;">Biaya</td>
                                    <td></td>
                                    <td style="padding-right: 80px; text-align: right;">
                                        Rp. {{ number_format($categoriesanalisisbibliometrik->biaya, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 80px;">Kode Unik Transfer</td>
                                    <td></td>
                                    <td style="padding-right: 80px; text-align: right;">
                                        Rp. {{ number_format($analisisbibliometrik->kode_unik, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 80px;">PPN</td>
                                    <td></td>
                                    <td style="padding-right: 80px; text-align: right;">
                                        Rp. {{ number_format($analisisbibliometrik->ppn, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 80px;">Diskon</td>
                                    <td></td>
                                    <td style="padding-right: 80px; text-align: right;">
                                        Rp. {{ number_format($analisisbibliometrik->nominal_diskon, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <br>
                        <hr>
                        <center>
                            <h3><b>Total </b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Rp. {{ number_format($analisisbibliometrik->total_pembayaran, 0, ',', '.') }}</h3>
                            <p><i>{{ $terbilang }}</i></p>
                        </center>
                        <hr>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6" style="float: right;">
                        <center>
                            <p class="lead">Yogyakarta, {{ date('j F Y', strtotime($analisisbibliometrik->created_at)) }}</p>
                            <p class="lead"> Manager Operasional</p><br>
                            <p class="lead">
                                Umang Wildan Pratama., S.E
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
</body>

</html>