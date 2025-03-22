<style>
  .version-text {
    position: fixed;
    bottom: 10px;
    right: 10px;
    font-size: 12px;
    color: #666;
    background: rgba(255, 255, 255, 0.8);
    padding: 5px 10px;
    border-radius: 5px;
  }
</style>

<div class="main-content">
  <section class="section">
    <center>
      <div class="section-header">
        <h1>DATA LAPORAN CAMP</h1>
        <div class="section-header">
          <center>
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
      </div>
    </center>
    <hr><br><br>

    <div class="section-body">
      <div class="card">
        <div class="card-body">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th scope="col" style="text-align: center;width: 6%">NO.</th>
                <th scope="col" class="column-width" style="text-align: center;">ID TRANSAKSI</th>
                <th scope="col" class="column-width" style="text-align: center;">NAMA CAMP</th>
                <th scope="col" class="column-width" style="text-align: center;">TOTAL UANG MASUK</th>
                <th scope="col" class="column-width" style="text-align: center;">TOTAL PENGELUARAN</th>
                <th scope="col" class="column-width" style="text-align: center;">KEUNTUNGAN</th>
                <th scope="col" class="column-width" style="text-align: center;">PERSENTASE KEUNTUNGAN</th>
                <th scope="col" class="column-width" style="text-align: center;">TANGGAL CAMP</th>
              </tr>
            </thead>
            <tbody>
              @php $no = 1; @endphp
              @foreach ($camp as $hasil)
              <tr>
                <th scope="row" style="text-align: center">{{ $loop->iteration }}</th>
                <td class="column-width" style="text-align: center; text-transform:uppercase;">{{ $hasil->id_transaksi }}</td>
                <td class="column-width" style="text-align: center; text-transform:uppercase;">{{ $hasil->title }} #{{ $hasil->camp_ke }}</td>
                <td class=" column-width" style="text-align: center;">Rp. {{ number_format($hasil->total_uang_masuk, 0, ',', '.') }}</td>
                <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->total, 0, ',', '.') }}</td>
                <td class="column-width" style="text-align: center;">Rp. {{ number_format($hasil->keuntungan, 0, ',', '.') }}</td>
                <td class="column-width" style="text-align: center;">{{ rtrim(rtrim(number_format($hasil->persentase_keuntungan, 1, ',', '.'), '0'), ',') }}%</td>
                <td class="column-width" style="text-align: center; width:200px">
                  {{ strftime('%d %B %Y', strtotime($hasil->tanggal)) }} <br>
                  s/d<br>
                  {{ strftime('%d %B %Y', strtotime($hasil->tanggal_akhir)) }}
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<!--================== FOOTER ==================-->
<style>
  .main-footer {
    border-top: 3px solid #ff914d;
    background-color: rgba(255, 255, 255, 0.95);
    /* Transparan sedikit untuk kesan modern */
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 50px;
    /* Sesuaikan tinggi footer */
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 500;
    text-align: center;
  }
</style>
<footer class="main-footer" id="PwaFooter">
  <div class="footer-left">
    © <strong>Rumah Scopus Foundation</strong> <?php echo date("Y"); ?>
  </div>
</footer>
<!--================== END ==================-->