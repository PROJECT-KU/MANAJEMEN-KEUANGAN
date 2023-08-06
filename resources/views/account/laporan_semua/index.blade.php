@extends('layouts.account')

@section('title')
Uang Masuk dan Keluar - UANGKU
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
          <h4><i class="fas fa-money-check-alt"></i> LAPORAN TRANSAKSI SEMUA</h4>
          <div class="card-header-action">
            <a href="{{ route('account.laporan_semua.download-pdf') }}" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Download PDF</a>
            <br>
            <i class="fas fa-info-circle info-icon"></i>
            <span class="info-text" style="font-size: 13px;">Data yang terdownload hanya data bulan saat ini</span>
            <!-- Add this line -->
          </div>

        </div>

        <div class="card-body">
          <form action="{{ route('account.laporan_semua.search') }}" method="GET">
            <div class="form-group">
              <div class="input-group mb-3">
                <input type="text" class="form-control" name="q" placeholder="pencarian">
                <div class="input-group-append">
                  <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> CARI
                  </button>
                </div>
              </div>
            </div>
          </form>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th scope="col" style="text-align: center;width: 6%" rowspan="2">NO.</th>
                  <th scope="col" colspan="2" style="text-align: center;">JENIS TRANSAKSI</th>
                  <th scope="col" rowspan="2" style="text-align: center;">KATEGORI</th>
                  <th scope="col" rowspan="2" style="text-align: center;">KETERANGAN</th>
                  <th scope="col" rowspan="2" style="text-align: center;">TANGGAL</th>
                </tr>
                <tr>
                  <th scope="col" style="text-align: center;">MASUK</th>
                  <th scope="col" style="text-align: center;">KELUAR</th>
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
                  <td style="text-align: center;">{{ $item->name }}</td>
                  <td style="text-align: center;">{{ $item->description }}</td>
                  <td style="text-align: center;">
                    @if ($transaction['type'] === 'debit')
                    {{ date('d-m-Y H:i', strtotime($item->debit_date)) }}
                    @else
                    {{ date('d-m-Y H:i', strtotime($item->credit_date)) }}
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
    </div>
  </section>
</div>


@stop