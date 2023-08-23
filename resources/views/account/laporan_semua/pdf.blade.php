<div class="main-content">
  <section class="section">
    <div class="section-header">
      <center>
        <h1>LAPORAN TRANSAKSI SEMUA</h1>
      </center>
    </div>

    <div class="section-body">

      <div class="card">

        <div class="card-body">

          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th scope="col" style="text-align: center;width: 6%" rowspan="2">NO.</th>
                  <th scope="col" rowspan="2" style="text-align: center; width:150px">ID TRANSAKSI</th>
                  <th scope="col" colspan="3" style="text-align: center; width:150px">JENIS TRANSAKSI</th>
                  <th scope="col" rowspan="2" style="text-align: center; width:150px">NAMA KARYAWAN</th>
                  <th scope="col" rowspan="2" style="text-align: center; width:150px">KATEGORI</th>
                  <th scope="col" rowspan="2" style="text-align: center; width:150px">KETERANGAN</th>
                  <th scope="col" rowspan="2" style="text-align: center; width:150px">TANGGAL</th>
                </tr>
                <tr>
                  <th scope="col" style="text-align: center; width:80px">MASUK</th>
                  <th scope="col" style="text-align: center; width:80px">KELUAR</th>
                  <th scope="col" style="text-align: center; width:80px">GAJI</th>
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
                $combinedTransactions->push([
                'type' => 'gaji',
                'transaction_date' => $item->tanggal,
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
                    @if ($transaction['type'] === 'gaji')
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
                  <td style="text-align: center;">
                    @if ($transaction['type'] === 'gaji')
                    -
                    @else
                    {{ $item->description }}
                    @endif
                  </td>
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
            </table>

            <div class="mt-5" style="color: red;">
              <h4>TOTAL TRANSAKSI MASUK : Rp. {{ number_format($totalDebit, 0, ',', ',') }}</h4>
              <h4>TOTAL TRANSAKSI KELUAR : Rp. {{ number_format($totalCredit, 0, ',', ',') }}</h4>
              <h4>TOTAL GAJI KARYAWAN : Rp. {{ number_format($totalGaji, 0, ',', ',') }}</h4>
            </div>


          </div>
        </div>
      </div>
    </div>
  </section>
</div>
<footer class="main-footer" style="border-top: 3px solid #6777ef;background-color: #ffffff;margin-bottom: -20px">
  <div class="footer-left">
    © <strong>Berto Juni</strong> 2019. Hak Cipta Dilindungi.
  </div>
  <div class="footer-right">
    Version 1.8
  </div>
</footer>