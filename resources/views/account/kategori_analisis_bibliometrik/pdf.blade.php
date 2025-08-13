<head>
  <meta charset="UTF-8">
  <title>List Kategori Analisis Bibliometrik</title>
  <style>
    @page {
      margin: 20px 40px;
      /* Atur margin kertas langsung di @page */
    }

    body {
      font-family: DejaVu Sans, sans-serif;
      margin: 0;
      /* Hilangkan margin body agar pakai @page */
      font-size: 12px;
    }

    h1,
    h4 {
      margin: 5px 0;
      text-align: center;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      table-layout: fixed;
      /* Pastikan lebar tabel proporsional */
    }

    th,
    td {
      border: 1px solid #000;
      padding: 8px;
      text-align: center;
      word-wrap: break-word;
    }

    th {
      background-color: #f2f2f2;
    }

    .column-width {
      width: 14%;
      /* Persentase agar tabel tidak kepentok kanan */
    }

    .section-header {
      margin-bottom: 10px;
    }

    hr {
      border: 1px solid #000;
    }
  </style>
</head>

<body>
  <center>
    @if($src)
    <img src="{{ $src }}" alt="Logo" height="45px">
    @endif
  </center>

  <div class="section-header">
    <h1>LIST KATEGORI ANALISIS BIBLIOMETRIK</h1>
    <hr>
    <h4>{{ $user->alamat_company }}</h4>
    <h4>Email: {{ $user->email_company }} | Telp: {{ $user->telp_company }}</h4>
  </div>

  <table>
    <thead>
      <tr>
        <th rowspan="2" style="width: 6%">NO.</th>
        <th rowspan="2" class="column-width">NAMA KATEGORI</th>
        <th colspan="2">TANGGAL</th>
        <th rowspan="2" class="column-width">TOTAL KUOTA</th>
        <th rowspan="2" class="column-width">SISA KUOTA</th>
        <th rowspan="2" class="column-width">STATUS</th>
      </tr>
      <tr>
        <th class="column-width">MULAI</th>
        <th class="column-width">SELESAI</th>
      </tr>
    </thead>
    <tbody>
      @php $no = 1; @endphp
      @foreach($categories as $item)
      <tr>
        <td>{{ $no++ }}</td>
        <td>{{ $item->nama }} #{{ $item->nama_ke }}</td>
        <td>{{ \Carbon\Carbon::parse($item->mulai)->translatedFormat('d F Y') }}</td>
        <td>{{ \Carbon\Carbon::parse($item->selesai)->translatedFormat('d F Y') }}</td>
        <td>{{ $item->total_kuota }}</td>
        <td>{{ $item->sisa_kuota }}</td>
        <td>{{ strtoupper($item->status) }}</td>
      </tr>
      @endforeach

      @if($categories->isEmpty())
      <tr>
        <td colspan="7">Tidak ada data yang tersedia.</td>
      </tr>
      @endif
    </tbody>
  </table>
</body>