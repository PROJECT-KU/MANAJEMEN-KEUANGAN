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
      </div>
    </center>

    <div class="section-body">

      @if(isset($debit) && count($debit) > 0)
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th scope="col" style="text-align: center;width: 6%">NO.</th>
                  <th scope="col" style="text-align: center" class="column-width">KATEGORI</th>
                  <th scope="col" style="text-align: center" class="column-width">NOMINAL</th>
                  <th scope="col" style="text-align: center" class="column-width">KETERANGAN</th>
                  <th scope="col" style="text-align: center" class="column-width">TANGGAL</th>
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
                  <td class="column-width" style="text-align: center">{{ date('d-m-Y H:i', strtotime($hasil->debit_date)) }}</td>
                </tr>
                @php
                $no++;
                @endphp
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endif
    </div>
  </section>
</div>