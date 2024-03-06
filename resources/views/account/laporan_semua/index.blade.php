  @extends('layouts.account')

  @section('title')
  Laporan Transaksi Semua | MIS
  @stop

  @section('content')
  <div class="main-content">
    <section class="section">
      <div class="section-header">
        <h1>LAPORAN TRANSAKSI SEMUA</h1>
      </div>

      <div class="section-body">

        <div class="card">
          <div class="card-header text-right">
            <h4><i class="fas fa-filter"></i> FILTER</h4>
            <!-- <div class="card-header-action">
              <a href="{{ route('account.laporan_semua.download-pdf') }}" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Download PDF</a>
              <br>
              <i class="fas fa-info-circle info-icon"></i>
              <span class="info-text" style="font-size: 13px;">Data yang terdownload hanya data bulan saat ini</span>
            </div> -->
          </div>
          <div class="card-body">
            <form action="{{ route('account.laporan_semua.index') }}" method="GET">
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
                    <a href="{{ route('account.laporan_semua.index') }}" class="btn btn-danger" style="margin-top: 30px;">
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
          <h4><i class="fas fa-chart-pie"></i> LAPORAN TRANSAKSI SEMUA</h4>
          <div class="card-header-action">
            <a href="{{ route('account.laporan_semua.download-pdf', ['tanggal_awal' => $startDate, 'tanggal_akhir' => $endDate]) }}" class="btn btn-primary">
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
                  <th scope="col" rowspan="2" style="text-align: center;">ID TRANSAKSI</th>
                  <th scope="col" colspan="3" style="text-align: center;">JENIS TRANSAKSI</th>
                  <th scope="col" rowspan="2" style="text-align: center;">NAMA KARYAWAN</th>
                  <th scope="col" rowspan="2" style="text-align: center;">KATEGORI</th>
                  <!--<th scope="col" rowspan="2" style="text-align: center;">KETERANGAN</th>-->
                  <th scope="col" rowspan="2" style="text-align: center;">TANGGAL</th>
                </tr>
                <tr>
                  <th scope="col" style="text-align: center;">MASUK</th>
                  <th scope="col" style="text-align: center;">KELUAR</th>
                  <th scope="col" style="text-align: center;">GAJI</th>
                </tr>
              </thead>
              <tbody>
                @php
                $combinedTransactions = collect([]); // Create an empty collection to store the merged transactions

                // Add debit transactions to the combined collection with type 'debit' and debit_date as transaction_date
                foreach ($debit as $item) {
                $combinedTransactions->push([
                'type' => 'debit',
                'transaction_date' => $item->debit_date,
                'data' => $item,
                ]);
                }

                // Add credit transactions to the combined collection with type 'credit' and credit_date as transaction_date
                foreach ($credit as $item) {
                $combinedTransactions->push([
                'type' => 'credit',
                'transaction_date' => $item->credit_date,
                'data' => $item,
                ]);
                }

                // Add gaji transactions to the combined collection with type 'gaji' and tanggal as transaction_date
                foreach ($gaji as $item) {
                if ($item->status != 'pending') {
                $combinedTransactions->push([
                'type' => 'gaji',
                'transaction_date' => $item->tanggal,
                'data' => $item,
                ]);
                }
                }

                // Sort the combined transactions by transaction date in descending order
                $sortedTransactions = $combinedTransactions->sortByDesc(function ($item) {
                return strtotime($item['transaction_date']);
                });

                $no = 1;
                @endphp

                @foreach ($sortedTransactions as $transaction)
                @php $item = $transaction['data']; @endphp
                <tr>
                  <th scope="row" style="text-align: center">{{ $no }}</th>
                  <td style="text-align: center;">
                    {{ $item->id_transaksi }}
                  </td>
                  <td style="text-align: center;">
                    @if ($transaction['type'] === 'debit')
                    {{ rupiah($item->nominal) }}
                    @else
                    -
                    @endif
                  </td>
                  <td style="text-align: center;">
                    @if ($transaction['type'] === 'credit')
                    {{ rupiah($item->nominal) }}
                    @else
                    -
                    @endif
                  </td>

                  <td style="text-align: center;">
                    @if ($transaction['type'] === 'gaji' && $item->status != 'pending')
                    {{ rupiah($item->total) }}
                    @else
                    -
                    @endif
                  </td>

                  <td style="text-align: center;">
                    @if ($transaction['type'] === 'gaji')
                    {{ $item->full_name }}
                    @else
                    -
                    @endif
                  </td>
                  <td style="text-align: center;">
                    @if ($transaction['type'] === 'gaji')
                    GAJI KARYAWAN
                    @else
                    {{ $item->name }}
                    @endif
                  </td>
                  <!--<td style="text-align: center;">
                      @if ($transaction['type'] === 'gaji')
                      -
                      @else
                      {{ $item->description }}
                      @endif
                    </td>-->
                  <td style="text-align: center;">
                    @if ($transaction['type'] === 'debit')
                    {{ date('d-m-Y H:i', strtotime($item->debit_date)) }}
                    @elseif ($transaction['type'] === 'credit')
                    {{ date('d-m-Y H:i', strtotime($item->credit_date)) }}
                    @elseif ($transaction['type'] === 'gaji')
                    {{ date('d-m-Y H:i', strtotime($item->tanggal)) }}
                    @endif
                  </td>
                </tr>
                @php $no++; @endphp
                @endforeach
              </tbody>
            </table>


          </div>
        </div>
      </div>
      @else
      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-chart-pie"></i> LAPORAN TRANSAKSI SEMUA</h4>
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

      <table class="table table-bordered mt-5" style="border: 2px solid red;">
        <thead>
          <tr>
            <th scope="col" rowspan="2" style="text-align: center; font-weight: bold;">TOTAL TRANSAKSI MASUK</th>
            <th scope="col" rowspan="2" style="text-align: center; font-weight: bold;">TOTAL TRANSAKSI KELUAR</th>
            <th scope="col" rowspan="2" style="text-align: center; font-weight: bold;">TOTAL GAJI KARYAWAN</th>
          </tr>
        </thead>
        <tbody>
          <tr style="text-align: center; font-weight: bold;">
            <td>Rp. {{ number_format($totalDebit, 0, ',', ',') }}</td>
            <td>Rp. {{ number_format($totalCredit, 0, ',', ',') }}</td>
            @php
            $totalGaji = 0;
            foreach ($gaji as $item) {
            if ($item->status != 'pending') {
            $totalGaji += $item->total;
            }
            }
            @endphp
            <td>Rp. {{ number_format($totalGaji, 0, ',', ',') }}</td>
          </tr>
        </tbody>
      </table>
  </div>

  </div>
  </section>
  </div>


  @stop