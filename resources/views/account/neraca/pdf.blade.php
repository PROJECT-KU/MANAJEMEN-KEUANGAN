<div class="main-content">
  <section class="section">
    <div class="section-header">
      <center>
        <h1>LAPORAN TRANSAKSI NERACA</h1>
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
                  <th scope="col" rowspan="2" style="text-align: center; width:255px">KODE</th>
                  <th scope="col" rowspan="2" style="text-align: center; width:255px">NAMA KATEGORI</th>
                  <th scope="col" colspan="3" style="text-align: center; width:255px">TOTAL</th>
                </tr>
                <tr>
                  <th scope="col" style="text-align: center; width:150px">MASUK</th>
                  <th scope="col" style="text-align: center; width:150px">KELUAR</th>
                  <th scope="col" style="text-align: center; width:150px">GAJI</th>
                </tr>
              </thead>
              <tbody>
                @php
                $no = 1;
                $totalPerCategory = []; // Inisialisasi array untuk total per kategori

                // Menggabungkan entri dengan kode dan nama kategori yang sama
                $mergedItems = [];

                foreach ($debit as $item) {
                $key = $item->kode . '-' . $item->name;
                if (!isset($mergedItems[$key])) {
                $mergedItems[$key] = [
                'kode' => $item->kode,
                'name' => $item->name,
                'nominal_masuk' => 0,
                'nominal_keluar' => 0,
                'nominal_gaji' => 0,
                ];
                }

                $mergedItems[$key]['nominal_masuk'] += $item->nominal;
                }

                foreach ($credit as $item) {
                $key = $item->kode . '-' . $item->name;
                if (!isset($mergedItems[$key])) {
                $mergedItems[$key] = [
                'kode' => $item->kode,
                'name' => $item->name,
                'nominal_masuk' => 0,
                'nominal_keluar' => 0,
                'nominal_gaji' => 0,
                ];
                }

                $mergedItems[$key]['nominal_keluar'] += $item->nominal;
                }

                foreach ($gaji as $item) {
                $key = 'G001-GAJI KARYAWAN';
                if (!isset($mergedItems[$key])) {
                $mergedItems[$key] = [
                'kode' => 'G001',
                'name' => 'GAJI KARYAWAN',
                'nominal_masuk' => 0,
                'nominal_keluar' => 0,
                'nominal_gaji' => 0,
                ];
                }
                if ($item->status != 'pending') {
                $mergedItems[$key]['nominal_gaji'] += $item->total;
                }
                }
                // Menampilkan data yang telah digabung
                foreach ($mergedItems as $key => $item) {
                $total = $item['nominal_masuk'] - $item['nominal_keluar'];
                $totalPerCategory[$key] = $total;
                }

                @endphp

                @foreach ($totalPerCategory as $key => $total)
                @php
                $item = $mergedItems[$key];
                @endphp
                <tr>
                  <th scope="row" style="text-align: center">{{ $no }}</th>
                  <td style="text-align: center;">
                    {{ $item['kode'] }}
                  </td>
                  <td style="text-align: center;">
                    {{ $item['name'] }}
                  </td>
                  <td style="text-align: center;">
                    {{ rupiah($item['nominal_masuk']) }}
                  </td>
                  <td style="text-align: center;">
                    {{ rupiah($item['nominal_keluar']) }}
                  </td>
                  <td style="text-align: center;">
                    {{ rupiah($item['nominal_gaji']) }}
                  </td>
                </tr>
                @php
                $no++;
                @endphp
                @endforeach

              </tbody>
            </table>

            <br><br>
            @if (Auth::user()->level == 'manager' || Auth::user()->level == 'staff')
            <hr>
            <table class="table table-bordered mt-5" style="text-align: center; font-weight: bold;">
              <thead>
                <tr>
                  <th scope="col" rowspan="2" style="text-align: center; font-weight: bold; width:1100px">KEUNTUNGAN</th>
                </tr>
              </thead>
              <br>
              <tbody>
                <tr style="text-align: center; font-weight: bold;">
                  <td>Rp. {{ number_format($totalDebit-$totalCredit-$totalGaji, 0, ',', ',')}}</td>
                </tr>
              </tbody>
            </table>
            <hr>
            <br>
            @endif

          </div>
        </div>
      </div>
    </div>
  </section>
</div>
@extends('layouts.version')