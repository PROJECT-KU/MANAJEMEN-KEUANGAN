<style>
  /* Define the fixed width for the columns */
  .column-width {
    width: 150px;
  }
</style>
<div class="main-content">
  <section class="section">
    <center>
      <div class="section-header">
        <h1> LAPORAN UANG MASUK</h1>
        <p style="margin-top: -3px; font-size: 15px"><strong>Periode
            @if ($tanggal_awal && $tanggal_akhir)
            {{ date('d F Y', strtotime($tanggal_awal)) }} - {{ date('d F Y', strtotime($tanggal_akhir)) }}
            @else
            {{ date('F Y') }}
            @endif
          </strong>
        </p>
        <hr>
        <h4>{{ $user->alamat_company }}</h4>
        <h4>Email : {{ $user->email_company }} Telp : {{ $user->telp_company }}</h4>
      </div>
    </center>
    <hr><br><br>

    <div class="section-body">

      @if(isset($debit) && count($debit) > 0)
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th scope="col" style="text-align: center;width: 6%">NO.</th>
                  <th scope="col" style="text-align: center; width:165px" class="column-width">KATEGORI</th>
                  <th scope="col" style="text-align: center; width:165px" class="column-width">NOMINAL</th>
                  <th scope="col" style="text-align: center; width:165px" class="column-width">KETERANGAN</th>
                  <th scope="col" style="text-align: center; width:165px" class="column-width">TANGGAL</th>
                </tr>
              </thead>
              <tbody>
                @php
                $no = 1;
                @endphp
                @foreach ($debit as $hasil)
                <tr>
                  <th scope="row" style="text-align: center">{{ $no }}</th>
                  <td class="column-width" style="text-align: center">{{ $hasil->name }}</td>
                  <td class="column-width" style="text-align: center">{{ rupiah($hasil->nominal) }}</td>
                  <td class="column-width" style="text-align: center">{{ $hasil->description }}</td>
                  <td class="column-width" style="text-align: center">{{ strftime('%d %B %Y %H:%M', strtotime($hasil->debit_date)) }}</td>
                </tr>
                @php
                $no++;
                @endphp
                @endforeach
              </tbody>
            </table>

            <br><br>
            <hr>
            <table class="table table-bordered mt-5">
              <thead>
                <tr>
                  <th scope="col" rowspan="2" style="text-align: center; font-weight: bold; width:750px">TOTAL</th>
                </tr>
              </thead>
              <br>
              <tbody>
                <tr style="text-align: center; font-weight: bold;">
                  <td>Rp. {{ number_format($totalDebit, 0, ',', ',')}}</td>
                </tr>
              </tbody>
            </table>
            <hr>

          </div>
        </div>
      </div>
      @endif
    </div>
  </section>
</div>
@extends('layouts.version')