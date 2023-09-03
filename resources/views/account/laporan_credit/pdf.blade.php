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
        <h1> LAPORAN UANG KELUAR</h1>
        <h4>{{ $user->alamat_company }}</h4>
        <h4>Email : {{ $user->email_company }} Telp : {{ $user->telp_company }}</h4>
      </div>
    </center>
    <hr><br><br>

    <div class="section-body">
      @if(isset($credit) && count($credit) > 0)
      <div class="card">
        <div class="card-body">

          <div class="table-responsive">
            <table class="table table-bordered">
              <table>
                <thead>
                  <tr>
                    <th scope="col" style="text-align: center; width: 6%">NO.</th>
                    <th scope="col" class="column-width" style="text-align: center">KATEGORI</th>
                    <th scope="col" class="column-width" style="text-align: center">NOMINAL</th>
                    <th scope="col" class="column-width" style="text-align: center">KETERANGAN</th>
                    <th scope="col" class="column-width" style="text-align: center">TANGGAL</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                  $no = 1;
                  @endphp
                  @foreach ($credit as $hasil)
                  <tr>
                    <th scope="row" style="text-align: center">{{ $no }}</th>
                    <td class="column-width" style="text-align: center">{{ $hasil->name }}</td>
                    <td class="column-width" style="text-align: center">{{ rupiah($hasil->nominal) }}</td>
                    <td class="column-width" style="text-align: center">{{ $hasil->description }}</td>
                    <td class="column-width" style="text-align: center">{{ date('d-m-Y H:i', strtotime($hasil->credit_date)) }}</td>
                  </tr>
                  @php
                  $no++;
                  @endphp
                  @endforeach
                </tbody>
              </table>
            </table>
          </div>
        </div>
      </div>
      @endif
    </div>
  </section>
</div>
@extends('layouts.version')