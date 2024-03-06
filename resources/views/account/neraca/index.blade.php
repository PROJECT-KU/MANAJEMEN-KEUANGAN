@extends('layouts.account')

@section('title')
Laporan Transaksi Neraca | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>LAPORAN TRANSAKSI NERACA</h1>
        </div>

        <div class="section-body">

            <div class="card">
                <div class="card-header text-right">
                    <h4><i class="fas fa-filter"></i> FILTER</h4>
                    <!-- <div class="card-header-action">
                        <a href="{{ route('account.laporan_neraca.download-pdf') }}" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Download PDF</a>
                        <br>
                        <i class="fas fa-info-circle info-icon"></i>
                        <span class="info-text" style="font-size: 13px;">Data yang terdownload hanya data bulan saat ini</span>
                    </div> -->
                </div>

                <div class="card-body">
                    <form action="{{ route('account.neraca.index') }}" method="GET">
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
                                    <a href="{{ route('account.neraca.index') }}" class="btn btn-danger" style="margin-top: 30px;">
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

        @if (isset($debit) && count($debit) > 0 || isset($credit) && count($credit) > 0 || isset($gaji) && count($gaji) > 0)
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-chart-pie"></i> LAPORAN TRANSAKSI NERACA</h4>
                <div class="card-header-action">
                    <a href="{{ route('account.laporan_neraca.download-pdf', ['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate]) }}" class="btn btn-primary">
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
                                <th scope="col" style="text-align: center;width: 6%" rowspan="2">NO.</th>
                                <th scope="col" rowspan="2" style="text-align: center;">KODE</th>
                                <th scope="col" rowspan="2" style="text-align: center;">NAMA KATEGORI</th>
                                <th scope="col" colspan="3" style="text-align: center;">TOTAL</th>
                            </tr>
                            <tr>
                                <th scope="col" style="text-align: center;">MASUK</th>
                                <th scope="col" style="text-align: center;">KELUAR</th>
                                <th scope="col" style="text-align: center;">GAJI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $no = 1;
                            $totalPerCategory = []; // Inisialisasi array untuk total per kategori

                            // Menggabungkan entri dengan kode dan nama kategori yang sama
                            $mergedItems = [];

                            foreach ($debit as $item) {
                            $key = $item->kode . '-' . $item->name;
                            if (!isset($mergedItems[$key])) {
                            $mergedItems[$key] = [
                            'kode' => $item->kode,
                            'name' => $item->name,
                            'nominal_masuk' => 0,
                            'nominal_keluar' => 0,
                            'nominal_gaji' => 0,
                            ];
                            }

                            $mergedItems[$key]['nominal_masuk'] += $item->nominal;
                            }

                            foreach ($credit as $item) {
                            $key = $item->kode . '-' . $item->name;
                            if (!isset($mergedItems[$key])) {
                            $mergedItems[$key] = [
                            'kode' => $item->kode,
                            'name' => $item->name,
                            'nominal_masuk' => 0,
                            'nominal_keluar' => 0,
                            'nominal_gaji' => 0,
                            ];
                            }

                            $mergedItems[$key]['nominal_keluar'] += $item->nominal;
                            }

                            foreach ($gaji as $item) {
                            $key = 'G001-GAJI KARYAWAN';
                            if (!isset($mergedItems[$key])) {
                            $mergedItems[$key] = [
                            'kode' => 'G001',
                            'name' => 'GAJI KARYAWAN',
                            'nominal_masuk' => 0,
                            'nominal_keluar' => 0,
                            'nominal_gaji' => 0,
                            ];
                            }
                            if ($item->status != 'pending') {
                            $mergedItems[$key]['nominal_gaji'] += $item->total;
                            }
                            }
                            // Menampilkan data yang telah digabung
                            foreach ($mergedItems as $key => $item) {
                            $total = $item['nominal_masuk'] - $item['nominal_keluar'];
                            $totalPerCategory[$key] = $total;
                            }

                            @endphp

                            @foreach ($totalPerCategory as $key => $total)
                            @php
                            $item = $mergedItems[$key];
                            @endphp
                            <tr>
                                <th scope="row" style="text-align: center">{{ $no }}</th>
                                <td style="text-align: center;">
                                    {{ $item['kode'] }}
                                </td>
                                <td style="text-align: center;">
                                    {{ $item['name'] }}
                                </td>
                                <td style="text-align: center;">
                                    {{ rupiah($item['nominal_masuk']) }}
                                </td>
                                <td style="text-align: center;">
                                    {{ rupiah($item['nominal_keluar']) }}
                                </td>
                                <td style="text-align: center;">
                                    {{ rupiah($item['nominal_gaji']) }}
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

            @else
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-pie"></i> LAPORAN TRANSAKSI NERACA</h4>
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
                    <h6 style="margin-top: -3px; font-size: 15px; text-align: center;"><strong>Tidak Ada Data Laporan Transaksi Pada Periode
                            @if ($startDate && $endDate)
                            {{ date('d F Y', strtotime($startDate)) }} - {{ date('d F Y', strtotime($endDate)) }}
                            @else
                            {{ date('F Y') }}
                            @endif
                        </strong>
                    </h6>
                </div>
            </div>
            @endif
        </div>
        @if (Auth::user()->level == 'manager' || Auth::user()->level == 'staff')
        <table class="table table-bordered mt-5" style="border: 2px solid red;">
            <thead>
                <tr>
                    <th scope="col" rowspan="2" style="text-align: center; font-weight: bold;">KEUNTUNGAN</th>
                </tr>
            </thead>
            <tbody>
                <tr style="text-align: center; font-weight: bold;">
                    <td>Rp. {{ number_format($totalDebit-$totalCredit-$totalGaji, 0, ',', ',')}}</td>
                </tr>
            </tbody>
        </table>
        @endif
</div>

</section>
</div>


@stop