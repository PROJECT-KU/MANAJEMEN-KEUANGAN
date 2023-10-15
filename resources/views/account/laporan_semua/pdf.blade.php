<div class="main-content">
  <section class="section">
    <div class="section-header">
      <center>
        <h1>LAPORAN TRANSAKSI SEMUA</h1>
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
    <hr><br><br>

    <div class="section-body">

      <div class="card">

        <div class="card-body">

          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th scope="col" style="text-align: center;width: 6%" rowspan="2">NO.</th>
                  <th scope="col" rowspan="2" style="text-align: center; width:175px">ID TRANSAKSI</th>
                  <th scope="col" colspan="3" style="text-align: center; width:175px">JENIS TRANSAKSI</th>
                  <th scope="col" rowspan="2" style="text-align: center; width:175px">NAMA KARYAWAN</th>
                  <th scope="col" rowspan="2" style="text-align: center; width:175px">KATEGORI</th>
                  <!--<th scope="col" rowspan="2" style="text-align: center; width:175px">KETERANGAN</th>-->
                  <th scope="col" rowspan="2" style="text-align: center; width:175px">TANGGAL</th>
                </tr>
                <tr>
                  <th scope="col" style="text-align: center; width:100px">MASUK</th>
                  <th scope="col" style="text-align: center; width:100px">KELUAR</th>
                  <th scope="col" style="text-align: center; width:100px">GAJI</th>
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
                    @if ($transaction['type'] === 'gaji')
                    {{ $item->id_transaksi }}
                    @else
                    -
                    @endif
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
                    @else
                    {{ date('d-m-Y H:i', strtotime($item->tanggal)) }}
                    @endif
                  </td>
                </tr>
                @php $no++; @endphp
                @endforeach
              </tbody>
            </table><br><br>

            <hr>
            <table class="table table-bordered mt-5">
              <thead>
                <tr>
                  <th scope="col" rowspan="2" style="text-align: center; font-weight: bold; width:350px">TOTAL TRANSAKSI MASUK</th>
                  <th scope="col" rowspan="2" style="text-align: center; font-weight: bold; width:350px">TOTAL TRANSAKSI KELUAR</th>
                  <th scope="col" rowspan="2" style="text-align: center; font-weight: bold; width:350px">TOTAL GAJI KARYAWAN</th>
                </tr>
              </thead>
              <br>
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
            <hr><br>

          </div>
        </div>
      </div>
    </div>
  </section>
</div>
@extends('layouts.version')