@extends('layouts.account')

@section('title')
Laporan Transaksi Neraca | MANAGEMENT
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
                    <h4><i class="fas fa-chart-pie"></i> LAPORAN TRANSAKSI NERACA</h4>
                    <div class="card-header-action">
                        <a href="{{ route('account.laporan_neraca.download-pdf') }}" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Download PDF</a>
                        <br>
                        <i class="fas fa-info-circle info-icon"></i>
                        <span class="info-text" style="font-size: 13px;">Data yang terdownload hanya data bulan saat ini</span>
                        <!-- Add this line -->
                    </div>

                </div>

                <div class="card-body">
                    <!-- <form action="{{ route('account.neraca.search') }}" method="GET">
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="q" placeholder="pencarian">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> CARI
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form> -->
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

                                $mergedItems[$key]['nominal_gaji'] += $item->total;
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
                    @if (Auth::user()->level == 'manager' || Auth::user()->level == 'staff')
                    <table class="table table-bordered mt-5">
                        <thead style="border: 2px solid red;">
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
            </div>
        </div>
</div>
</section>
</div>


@stop