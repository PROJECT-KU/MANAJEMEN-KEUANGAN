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
        <div class="card-header">
          <h4><i class="fas fa-money-check-alt"></i> LAPORAN TRANSAKSI SEMUA</h4>
        </div>

        <div class="card-body">
          <form action="{{ route('account.laporan_semua.search') }}" method="GET">
            <div class="form-group">
              <div class="input-group mb-3">
                <input type="text" class="form-control" name="q" placeholder="cari berdasarkan keterangan">
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
                $no = 1;
                @endphp
                @foreach ($debit as $item)
                <tr>
                  <th scope="row" style="text-align: center">{{ $no }}</th>
                  <td style="text-align: center;">{{ rupiah($item->nominal) }}</td>
                  <td style="text-align: center;">-</td>
                  <td style="text-align: center;">{{ $item->name }}</td>
                  <td style="text-align: center;">{{ $item->description }}</td>
                  <td style="text-align: center;">{{ $item->debit_date }}</td>
                </tr>
                @php
                $no++;
                @endphp
                @endforeach
                @foreach ($credit as $item)
                <tr>
                  <th scope="row" style="text-align: center">{{ $no }}</th>
                  <td style="text-align: center;">-</td>
                  <td style="text-align: center;">{{ rupiah($item->nominal) }}</td>
                  <td style="text-align: center;">{{ $item->name }}</td>
                  <td style="text-align: center;">{{ $item->description }}</td>
                  <td style="text-align: center;">{{ $item->credit_date }}</td>
                </tr>
                @php
                $no++;
                @endphp
                @endforeach
              </tbody>
            </table>
            <div style="text-align: center">
              {{$debit->links("vendor.pagination.bootstrap-4")}}
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>


@stop