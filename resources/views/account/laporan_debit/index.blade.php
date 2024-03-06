@extends('layouts.account')

@section('title')
Laporan Uang Masuk | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>LAPORAN UANG MASUK</h1>
        </div>

        <div class="section-body">

            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-filter"></i> FILTER</h4>
                </div>

                <div class="card-body">

                    <form action="{{ route('account.laporan_debit.check') }}" method="GET">
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
                                    <input type="text" name="tanggal_akhir" value="{{ old('tanggal_akhir') }}" class="form-control datepicker">
                                </div>
                            </div>
                            <div class="col-md-2">
                                @if (request()->has('tanggal_awal') && request()->has('tanggal_akhir'))
                                <div class="btn-group" style="width: 100%;">
                                    <button class="btn btn-primary mr-1" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                                    <a href="{{ route('account.laporan_debit.index') }}" class="btn btn-danger" style="margin-top: 30px;">
                                        <i class="fa fa-times-circle mt-2"></i> HAPUS
                                    </a>
                                </div>
                                @else
                                <button class="btn btn-primary mr-1 btn-block" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                                @endif
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>


        <div class="section-body">
            @if (isset($debit) && count($debit) > 0 )
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-line"></i> LAPORAN UANG MASUK</h4>
                    <div class="card-header-action">
                        <a href="{{ route('account.laporan_debit.download-pdf', ['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate]) }}" class="btn btn-primary">
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
                                    <th scope="col" style="text-align: center;width: 6%">NO.</th>
                                    <th scope="col" style="text-align: center">KATEGORI</th>
                                    <th scope="col" style="text-align: center">NOMINAL</th>
                                    <th scope="col" style="text-align: center">KETERANGAN</th>
                                    <th scope="col" style="text-align: center">TANGGAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $no = 1;
                                @endphp
                                @foreach ($debit as $hasil)
                                <tr>
                                    <th scope="row" style="text-align: center">{{ $no }}</th>
                                    <td style="text-align: center">{{ $hasil->name }}</td>
                                    <td style="text-align: center">{{ rupiah($hasil->nominal) }}</td>
                                    <td style="text-align: center">{{ $hasil->description }}</td>
                                    <td style="text-align: center">{{ strftime('%d %B %Y %H:%M', strtotime($hasil->debit_date)) }}</td>
                                </tr>
                                @php
                                $no++;
                                @endphp
                                @endforeach
                            </tbody>
                        </table>
                        <table class="table table-bordered mt-5" style="border: 2px solid red;">
                            <thead>
                                <tr>
                                    <th scope="col" rowspan="2" style="text-align: center; font-weight: bold;">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="text-align: center; font-weight: bold;">
                                    <td>Rp. {{ number_format($totalDebit, 0, ',', ',')}}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div style="text-align: center">
                            {{$debit->links("vendor.pagination.bootstrap-4")}}
                        </div>
                    </div>
                </div>
            </div>

            @else

            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-line"></i> LAPORAN UANG MASUK</h4>
                    <div class="card-header-action">
                        <a href="{{ route('account.laporan_debit.download-pdf', ['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate]) }}" class="btn btn-primary">
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
    </section>
</div>
@stop