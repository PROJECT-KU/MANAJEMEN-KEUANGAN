<div class="main-content">
  <section class="section">
    <center>
      <div class="section-header">
        <h1>LIST PENYEWAAN</h1>
      </div>
    </center>

    <div class="section-body">

      <div class="card">

        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th scope="col" style="text-align: center;width: 6%">NO.</th>
                  <th scope="col" class="column-width" style="text-align: center;">KENDARAAN</th>
                  <th scope="col" class="column-width" style="text-align: center;">NAMA PENYEWA</th>
                  <th scope="col" class="column-width" style="text-align: center;">NO TELP</th>
                  <th scope="col" class="column-width" style="text-align: center;">ALAMAT</th>
                  <th scope="col" class="column-width" style="text-align: center;">IDENTITAS</th>
                  <th scope="col" class="column-width" style="text-align: center;">TANGGAL PEMINJAMAN</th>
                </tr>
              </thead>
              <tbody>
                @php
                $no = 1;
                @endphp
                @foreach ($penyewaan as $hasil)
                <tr>
                  <th scope="row" style="text-align: center">{{ $no }}</th>
                  <td class="column-width" style="text-align: center">{{ $hasil->nama_barang }}</td>
                  <td class="column-width" style="text-align: center">{{ $hasil->nama }}</td>
                  <td class="column-width" style="text-align: center">{{ $hasil->telp }}</td>
                  <td class="column-width" style="text-align: center">{{ $hasil->alamat }}</td>
                  <td class="column-width" style="text-align: center">{{ $hasil->identitas }}</td>
                  <td class="column-width" style="text-align: center">{{ date('d-m-Y', strtotime($hasil->tanggal)) }}</td>
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
    </div>
  </section>
</div>