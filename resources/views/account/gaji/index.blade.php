@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Data Gaji Karyawan | MIS
@stop

<style>
  :root {
    --accent: #6366f1;
    --accent-light: #818cf8;
    --bg-main: #f4f7ff;
    --card-bg: rgba(255, 255, 255, 0.85);
    --radius-xl: 20px;
    --radius-md: 12px;
    --shadow-soft: 0 10px 30px rgba(0, 0, 0, 0.03);
  }

  .main-content {
    padding-top: 110px !important;
    background-color: transparent;
    min-height: 100vh;
  }

  /* 🔹 Glossy Stats Card */
  .stat-card-glossy {
    background: var(--card-bg);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.9);
    border-radius: var(--radius-xl);
    padding: 20px;
    box-shadow: var(--shadow-soft);
    display: flex;
    align-items: center;
    gap: 18px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 25px;
  }

  .stat-card-glossy:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06);
  }

  .stat-icon-wrap {
    width: 55px;
    height: 55px;
    border-radius: 16px;
    background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 22px;
    box-shadow: 0 8px 15px rgba(59, 130, 246, 0.25);
    flex-shrink: 0;
  }

  .stat-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .stat-content h4 {
    font-size: 11px;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 4px;
  }

  .stat-content h5 {
    font-size: 20px;
    font-weight: 800;
    color: #1e293b;
    margin: 0;
    letter-spacing: -0.5px;
  }

  /* 🔹 Hero Glass Controls */
  .hero-glass {
    background: var(--card-bg);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border-radius: var(--radius-xl);
    padding: 20px 25px;
    border: 1px solid rgba(255, 255, 255, 0.9);
    box-shadow: var(--shadow-soft);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
  }

  .title-section h1 {
    font-size: 22px;
    font-weight: 800;
    background: linear-gradient(to right, #1e293b, #6366f1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: -0.5px;
    margin: 0 0 3px 0;
  }

  .header-controls {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  /* 🔹 Search Input Modern */
  .search-neo {
    background: #ffffff;
    border-radius: 50px;
    padding: 6px 18px;
    display: flex;
    align-items: center;
    border: 1px solid #e2e8f0;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
    width: 240px;
    height: 40px;
    transition: all 0.3s ease;
  }

  .search-neo:focus-within {
    width: 280px;
    border-color: #818cf8;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
  }

  .search-neo input {
    border: none;
    background: transparent;
    padding: 5px 8px;
    width: 100%;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    outline: none;
  }

  /* 🔹 Buttons Modern */
  .btn-modern {
    flex: 1;
    padding: 12px;
    border-radius: 14px;
    border: none;
    font-size: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none !important;
  }

  .btn-create-animate {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
    position: relative;
    overflow: hidden;
  }

  .btn-create-animate:hover {
    transform: scale(1.05) translateY(-3px);
    box-shadow: 0 15px 30px rgba(99, 102, 241, 0.4) !important;
    filter: brightness(1.1);
  }

  .btn-create-animate:active {
    transform: scale(0.95);
  }

  /* Efek kilauan (shimmer) saat hover */
  .btn-create-animate::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(120deg,
        transparent,
        rgba(255, 255, 255, 0.3),
        transparent);
    transition: all 0.6s;
  }

  .btn-create-animate:hover::before {
    left: 100%;
  }

  .btn-export-glossy {
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    color: white;
    box-shadow: 0 8px 15px rgba(16, 185, 129, 0.2);
  }

  /* 🔹 Table Glossy */
  .table-container-glossy {
    background: var(--card-bg);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border-radius: var(--radius-xl);
    padding: 20px;
    border: 1px solid rgba(255, 255, 255, 0.9);
    box-shadow: var(--shadow-soft);
    overflow: hidden;
  }

  .table-modern {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 8px;
    /* Jarak antar baris dirapatkan */
  }

  .table-modern thead th {
    background: transparent;
    color: #64748b;
    font-weight: 800;
    text-transform: uppercase;
    font-size: 10px;
    /* Font header lebih proporsional */
    letter-spacing: 0.5px;
    border: none;
    padding: 12px 10px;
    border-bottom: 2px dashed #e2e8f0;
    white-space: nowrap;
  }

  .table-modern tbody tr {
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    transition: all 0.2s ease;
  }

  .table-modern tbody tr:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(99, 102, 241, 0.06);
  }

  .table-modern tbody td {
    padding: 12px 10px;
    /* Padding sel dirapatkan */
    border: none;
    font-size: 12.5px;
    /* Font isi tabel disesuaikan */
    font-weight: 600;
    color: #334155;
    vertical-align: middle;
  }

  .table-modern tbody td:first-child {
    border-radius: 12px 0 0 12px;
  }

  .table-modern tbody td:last-child {
    border-radius: 0 12px 12px 0;
  }

  /* Mencegah text wrap pada kolom tertentu */
  .nowrap-col {
    white-space: nowrap;
  }

  .badge-modern {
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .badge-pending {
    background: #fffbeb;
    color: #d97706;
    border: 1px solid #fde68a;
  }

  .badge-terbayar {
    background: #ecfdf5;
    color: #10b981;
    border: 1px solid #a7f3d0;
  }

  /* Style Action Buttons in Table */
  .action-btn {
    border-radius: 8px;
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    transition: 0.2s;
  }

  .action-btn:hover {
    filter: brightness(0.9);
    transform: scale(1.05);
  }

  .action-btn-slip {
    border-radius: 8px;
    padding: 0 10px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 10px;
    text-transform: uppercase;
    transition: 0.2s;
  }

  .action-btn-slip:hover {
    filter: brightness(0.9);
    transform: scale(1.05);
    text-decoration: none;
  }

  @media (max-width: 992px) {
    .hero-glass {
      flex-direction: column;
      text-align: center;
    }

    .header-controls {
      flex-direction: column;
      width: 100%;
    }

    .search-neo,
    .btn-modern {
      width: 100% !important;
    }

    .table-modern tbody td {
      white-space: nowrap;
    }
  }

  #clearSearch:focus,
  #clearSearch:active {
    outline: none !important;
    box-shadow: none !important;
  }
</style>
@section('content')

<div style="position: fixed; top: -10%; left: -10%; width: 40vw; height: 40vw; background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, rgba(99,102,241,0) 70%); border-radius: 50%; z-index: -1; pointer-events: none;"></div>
<div style="position: fixed; bottom: -10%; right: -10%; width: 50vw; height: 50vw; background: radial-gradient(circle, rgba(16,185,129,0.08) 0%, rgba(16,185,129,0) 70%); border-radius: 50%; z-index: -1; pointer-events: none;"></div>
<div class="main-content">
  <section class="section">

    @if ($gaji->count() > 0 && (Auth::user()->level == 'staff' || Auth::user()->level == 'manager' || Auth::user()->level == 'ceo'))
    @php $totalPendingSalaries = 0; @endphp
    @foreach ($gaji as $item)
    @if ($item->status === 'pending')
    @php $totalPendingSalaries++; @endphp
    @endif
    @endforeach

    @if ($totalPendingSalaries > 0)
    <div style="background: rgba(254, 243, 199, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(245, 158, 11, 0.5); border-radius: 16px; padding: 15px 20px; text-align: center; margin-bottom: 25px; box-shadow: 0 5px 15px rgba(245, 158, 11, 0.08);">
      <p style="margin: 0; font-size: 14px; color: #b45309; font-weight: 700;">
        <i class="fas fa-exclamation-triangle mr-2" style="font-size: 16px;"></i>
        Terdapat <b style="color: #ea580c; font-size: 16px;">{{ $totalPendingSalaries }}</b> data gaji pending. Segera selesaikan pembayaran.
      </p>
    </div>
    @endif
    @endif
    <div class="row">
      <div class="col-lg-4 col-md-4 col-sm-12">
        <div class="stat-card-glossy">
          <div class="stat-icon-wrap" style="background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);">
            <i class="fas fa-wallet"></i>
          </div>
          <div class="stat-content">
            <h4>Periode {{ date('F Y') }}</h4>
            @php
            $tampilTotalIni = $totalBulanIni;

            // Khusus selain manager: hitung ulang hanya yang berstatus "terbayar"
            if (Auth::user()->level != 'manager') {
            $tampilTotalIni = DB::table('gaji')
            ->where('user_id', Auth::user()->id)
            ->where('status', 'terbayar')
            ->whereBetween('tanggal', [
            \Carbon\Carbon::now()->startOfMonth(),
            \Carbon\Carbon::now()->endOfMonth()
            ])
            ->sum('total');
            }
            @endphp
            <h5>Rp. {{ number_format($tampilTotalIni, 0, ',', '.') }}</h5>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-4 col-sm-12">
        <div class="stat-card-glossy">
          <div class="stat-icon-wrap" style="background: linear-gradient(135deg, #0ea5e9 0%, #2dd4bf 100%);">
            <i class="fas fa-history"></i>
          </div>
          <div class="stat-content">
            <h4>Periode {{ \Carbon\Carbon::now()->subMonth()->format('F Y') }}</h4>
            @php
            $tampilTotalLalu = $totalBulanLalu;

            if (Auth::user()->level != 'manager') {
            $tampilTotalLalu = DB::table('gaji')
            ->where('user_id', Auth::user()->id)
            ->where('status', 'terbayar')
            ->whereBetween('tanggal', [
            \Carbon\Carbon::now()->subMonth()->startOfMonth(),
            \Carbon\Carbon::now()->subMonth()->endOfMonth()
            ])
            ->sum('total');
            }
            @endphp
            <h5>Rp. {{ number_format($tampilTotalLalu, 0, ',', '.') }}</h5>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-4 col-sm-12">
        <div class="stat-card-glossy">
          <div class="stat-icon-wrap" style="background: linear-gradient(135deg, #f43f5e 0%, #fb923c 100%);">
            <i class="fas fa-calendar-check"></i>
          </div>
          <div class="stat-content">
            <h4>Periode {{ \Carbon\Carbon::now()->subMonths(2)->format('F Y') }}</h4>
            @php
            $tampilTotalDuaLalu = $totalDuaBulanLalu;

            if (Auth::user()->level != 'manager') {
            $tampilTotalDuaLalu = DB::table('gaji')
            ->where('user_id', Auth::user()->id)
            ->where('status', 'terbayar')
            ->whereBetween('tanggal', [
            \Carbon\Carbon::now()->subMonths(2)->startOfMonth(),
            \Carbon\Carbon::now()->subMonths(2)->endOfMonth()
            ])
            ->sum('total');
            }
            @endphp
            <h5>Rp. {{ number_format($tampilTotalDuaLalu, 0, ',', '.') }}</h5>
          </div>
        </div>
      </div>
    </div>
    <div class="hero-glass">
      <div class="title-section">
        <h1>Data Gaji Karyawan</h1>
        <p class="text-muted font-weight-bold mb-0" style="font-size: 12px;">Manajemen rekapitulasi pembayaran gaji bulanan tim.</p>
      </div>

      <div class="header-controls">

        <div class="search-neo">
          <i class="fas fa-search text-primary" style="font-size: 14px;"></i>
          <input type="text" id="liveSearch" placeholder="Cari nama karyawan..." autocomplete="off">
          <button type="button" id="clearSearch" class="border-0 bg-transparent" style="display:none; cursor:pointer;">
            <i class="fas fa-times-circle text-muted"></i>
          </button>
        </div>

        <button type="button" id="btnFilterPopup" class="btn-modern" style="background: #ffffff; color: #475569; border: 1px solid #e2e8f0;">
          <i class="fas fa-filter text-primary"></i> FILTER
        </button>

        @if (Auth::user()->level == 'manager')
        <button type="button" id="downloadExcelBtn" class="btn-modern btn-export-glossy btn-create-animate">
          <i class="far fa-file-excel"></i> EXCEL
        </button>
        @endif

        @auth
        @if (Auth::user()->level === 'manager')
        @if ($presensiExist)
        <a href="{{ route('account.gaji.create') }}" class="btn-modern shadow-sm font-weight-bold btn-create-animate"
          style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: white; height: 42px;">
          <i class="fas fa-plus-circle" style="font-size: 18px;"></i>
          <span style="letter-spacing: 0.5px;">TAMBAH</span>
        </a>
        @else
        <a href="javascript:void(0)" id="tambahGajiBtn" class="btn-modern shadow-sm font-weight-bold btn-create-animate"
          style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: white; height: 42px;">
          <i class="fas fa-plus-circle" style="font-size: 18px;"></i>
          <span style="letter-spacing: 0.5px;">TAMBAH</span>
        </a>
        @endif
        @endif
        @endauth

      </div>
    </div>
    <div class="table-container-glossy">
      <div class="table-responsive">
        <table class="table-modern">
          <thead>
            <tr>
              <th style="text-align: center; width: 5%;">No</th>
              <th style="text-align: center;">ID Transaksi</th>
              <th style="text-align: left;">Nama Karyawan</th>
              <th style="text-align: center;">No Rekening</th>
              <th style="text-align: left;">Bank</th>
              <th style="text-align: right;">Total Gaji</th>
              <th style="text-align: center;">Tanggal</th>
              <th style="text-align: center;">Status</th>
              <th style="text-align: center; width: 12%;">Aksi</th>
            </tr>
          </thead>
          <tbody id="gajiTable">
            @php
            $no = 1;
            $terbayarCount = 0;
            @endphp

            @if ($gaji->isEmpty())
            <tr>
              <td colspan="9" class="text-center text-muted font-weight-bold py-5" style="background: transparent; box-shadow: none;">
                <i class="fas fa-folder-open fa-3x mb-3" style="color: #cbd5e1; display: block;"></i>
                Tidak ada data gaji yang ditemukan.
              </td>
            </tr>
            @endif

            @foreach ($gaji as $hasil)
            @if ((Auth::user()->level == 'karyawan' || Auth::user()->level == 'trainer') && $hasil->status == 'pending')
            @continue
            @endif

            <tr>
              <td style="text-align: center">{{ $no }}</td>
              <td class="nowrap-col" style="text-align: center; font-family: monospace; color: #6366f1; letter-spacing: 1px;"><b>{{ $hasil->id_transaksi }}</b></td>
              <td class="nowrap-col" style="text-align: left;">{{ $hasil->full_name }}</td>
              <td class="nowrap-col" style="text-align: center; font-family: monospace;">{{ $hasil->norek }}</td>
              <td class="nowrap-col" style="text-align: left;">
                @php
                $bankNames = [
                '002' => 'BRI', '008' => 'BANK MANDIRI', '009' => 'BNI', '200' => 'BANK TABUNGAN NEGARA',
                '011' => 'BANK DANAMON', '013' => 'BANK PERMATA', '014' => 'BCA', '016' => 'MAYBANK',
                '019' => 'PANINBANK', '022' => 'CIMB NIAGA', '023' => 'BANK UOB INDONESIA', '028' => 'BANK OCBC NISP',
                '087' => 'BANK HSBC INDONESIA', '147' => 'BANK MUAMALAT', '153' => 'BANK SINARMAS', '426' => 'BANK MEGA',
                '441' => 'BANK BUKOPIN', '451' => 'BSI', '484' => 'BANK KEB HANA INDONESIA', '494' => 'BANK RAYA INDONESIA',
                '506' => 'BANK MEGA SYARIAH', '046' => 'BANK DBS INDONESIA', '947' => 'BANK ALADIN SYARIAH',
                '950' => 'BANK COMMONWEALTH', '213' => 'BANK BTPN', '490' => 'BANK NEO COMMERCE', '501' => 'BANK DIGITAL BCA',
                '521' => 'BANK BUKOPIN SYARIAH', '535' => 'SEABANK INDONESIA', '542' => 'BANK JAGO', '567' => 'ALLO BANK',
                '110' => 'BPD JAWA BARAT', '111' => 'BPD DKI', '112' => 'BPD DAERAH ISTIMEWA YOGYAKARTA', '113' => 'BPD JAWA TENGAH',
                '114' => 'BPD JAWA TIMUR', '115' => 'BPD JAMBI', '116' => 'BANK ACEH SYARIAH', '117' => 'BPD SUMATERA UTARA',
                '118' => 'BANK NAGARI', '119' => 'BPD RIAU KEPRI SYARIAH', '120' => 'BPD SUMATERA SELATAN DAN BANGKA BELITUNG',
                '121' => 'BPD LAMPUNG', '122' => 'BPD KALIMANTAN SELATAN', '123' => 'BPD KALIMANTAN BARAT',
                '124' => 'BPD KALIMANTAN TIMUR DAN KALIMANTAN UTARA', '125' => 'BPD KALIMANTAN TENGAH',
                '126' => 'BPD SULAWESI SELATAN DAN SULAWESI BARAT', '127' => 'BPD SULAWESI UTARA DAN GORONTALO',
                '128' => 'BANK NTB SYARIAH', '129' => 'BPD BALI', '130' => 'BPD NUSA TENGGARA TIMUR', '131' => 'BPD MALUKU DAN MALUKU UTARA',
                '132' => 'BPD PAPUA', '133' => 'BPD BENGKULU', '134' => 'BPD SULAWESI TENGAH', '135' => 'BPD SULAWESI TENGGARA',
                '137' => 'BPD BANTEN'
                ];
                @endphp
                {{ array_key_exists($hasil->bank, $bankNames) ? $bankNames[$hasil->bank] : 'Bank Lainnya' }}
              </td>
              <td class="nowrap-col" style="text-align: right; color: #10b981; font-weight: 700;">Rp. {{ number_format($hasil->total, 0, ',', '.') }}</td>
              <td class="nowrap-col" style="text-align: center; color: #64748b; font-size: 11px;">
                {{ strftime('%d %b %Y %H:%M', strtotime($hasil->tanggal)) }}
              </td>

              <td class="nowrap-col" style="text-align: center;">
                @if($hasil->status == 'pending')
                <span class="badge-modern badge-pending">Pending</span>
                @else
                <span class="badge-modern badge-terbayar"><i class="fas fa-check-circle mr-1"></i> Terbayar</span>
                @endif
              </td>

              <td class="nowrap-col text-center">
                <div class="d-flex justify-content-center align-items-center" style="gap: 5px;">

                  @if(Auth::user()->level == 'karyawan' || Auth::user()->level == 'trainer' || Auth::user()->level == 'ceo')

                  <a href="{{ route('account.gaji.detail', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="action-btn" style="background: #eef2ff; color: #6366f1;" title="Detail">
                    <i class="fa fa-eye"></i>
                  </a>
                  <a href="{{ route('account.laporan_gaji.Slip-Gaji', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="action-btn-slip" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fa fa-file-invoice mr-1"></i> Slip
                  </a>

                  @else

                  @if($hasil->status == 'pending' && now()->month == \Carbon\Carbon::parse($hasil->tanggal)->month)
                  <a href="{{ route('account.gaji.edit', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="action-btn" style="background: #fffbeb; color: #d97706;" title="Edit">
                    <i class="fa fa-pencil-alt"></i>
                  </a>
                  @endif

                  <a href="{{ route('account.gaji.detail', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="action-btn" style="background: #eef2ff; color: #6366f1;" title="Detail">
                    <i class="fa fa-eye"></i>
                  </a>

                  <button onclick="Delete('{{ $hasil->id }}')" class="action-btn" style="background: #fff1f2; color: #e11d48;" title="Hapus">
                    <i class="fa fa-trash"></i>
                  </button>

                  <a href="{{ route('account.laporan_gaji.Slip-Gaji', ['id' => $hasil->id, 'token' => $hasil->token]) }}" class="action-btn-slip" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fa fa-file-invoice mr-1"></i> Slip
                  </a>

                  @endif
                </div>
              </td>
            </tr>
            @php
            $no++;
            $terbayarCount++;
            @endphp
            @endforeach
          </tbody>
        </table>

        <div class="d-flex justify-content-center mt-3 mb-2">
          {{ $gaji->appends(['tanggal_awal' => request('tanggal_awal'), 'tanggal_akhir' => request('tanggal_akhir'), 'q' => request('q')])->links("vendor.pagination.bootstrap-4") }}
        </div>
      </div>
    </div>
  </section>
</div>

// <!--================== POP UP FILTER DATA ==================-->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const btnFilterPopup = document.getElementById('btnFilterPopup');
    if (btnFilterPopup) {
      btnFilterPopup.addEventListener('click', function() {
        const tglAwal = "{{ request('tanggal_awal') }}";
        const tglAkhir = "{{ request('tanggal_akhir') }}";

        let ekstraTombol = '';
        if (tglAwal && tglAkhir) {
          ekstraTombol = `
            <button type="button" onclick="window.location.href='{{ route('account.gaji.index') }}'" class="btn-swal-action btn-danger-glossy">
          <i class="fas fa-times-circle mr-2"></i> HAPUS FILTER PENCARIAN
        </button>
      `;
        }

        Swal.fire({
          title: '<span style="background: linear-gradient(to right, #1e293b, #6366f1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800; letter-spacing: -1px; font-size: 24px;">Filter Data Gaji</span>',
          html: `
           <style>
          .glossy-swal-popup {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.8) !important;
            border-radius: 24px !important;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1) !important;
            padding: 2.5em 2em !important;
          }
          
          .swal2-container.swal2-backdrop-show {
            background: rgba(15, 23, 42, 0.4) !important;
            backdrop-filter: blur(5px) !important;
          }

          .glass-input-swal {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 12px 20px;
            height: 55px;
            font-weight: 700;
            color: #475569;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
          }
          
          .glass-input-swal:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            background: #f8fafc;
          }

          .swal-actions-custom {
            display: flex;
            gap: 12px;
            width: 100%;
            margin-top: 25px;
            justify-content: space-between;
          }

          .btn-swal-action {
            flex: 1;
            border-radius: 16px;
            font-weight: 800;
            padding: 16px 20px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            width: 100%;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
          }

          .btn-swal-action:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 25px rgba(0,0,0,0.15);
            filter: brightness(1.1);
          }

          .btn-swal-action:active {
            transform: scale(0.96);
          }

          .btn-confirm-glossy {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: #fff;
          }

          .btn-cancel-glossy {
            background: #f43f5e;
            color: #ffffff;
          }

          .btn-danger-glossy {
            background: linear-gradient(135deg, #f43f5e 0%, #fb7185 100%);
            color: #fff;
            margin-top: 15px;
          }

          .btn-success-glossy {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: #fff;
          }
        </style>

        <form id="formFilterSwal" action="{{ route('account.gaji.filter') }}" method="GET" style="text-align: left; margin-top: 25px;">
          <div class="form-group mb-4">
            <label class="font-weight-bold text-muted mb-2" style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
              <i class="far fa-calendar-alt text-primary mr-2" style="font-size: 14px;"></i> Tanggal Awal
            </label>
            <input type="date" id="inputTglAwal" name="tanggal_awal" value="${tglAwal}" class="glass-input-swal">
          </div>
          <div class="form-group mb-2">
            <label class="font-weight-bold text-muted mb-2" style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
              <i class="far fa-calendar-check text-primary mr-2" style="font-size: 14px;"></i> Tanggal Akhir
            </label>
            <input type="date" id="inputTglAkhir" name="tanggal_akhir" value="${tglAkhir}" class="glass-input-swal">
          </div>
        </form>
            ${ekstraTombol}
          `,
          buttonsStyling: false,
          showCancelButton: true,
          confirmButtonText: '<i class="fas fa-search mr-2"></i> TERAPKAN',
          cancelButtonText: 'BATAL',
          customClass: {
            popup: 'glossy-swal-popup',
            actions: 'swal-actions-custom',
            confirmButton: 'btn-swal-action btn-confirm-glossy',
            cancelButton: 'btn-swal-action btn-cancel-glossy'
          },
          preConfirm: () => {
            const awal = document.getElementById('inputTglAwal').value;
            const akhir = document.getElementById('inputTglAkhir').value;
            if (!awal || !akhir) {
              Swal.showValidationMessage('Pastikan Tanggal Awal dan Akhir telah dipilih!');
              return false;
            }
            return true;
          }
        }).then((result) => {
          if (result.isConfirmed) document.getElementById('formFilterSwal').submit();
        });
      });
    }
  });
</script>
// <!--================== END POP UP FILTER DATA ==================-->

// <!--================== EXPORT EXCEL DATA GAJI ==================-->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const downloadBtn = document.getElementById('downloadExcelBtn');
    const searchInput = document.getElementById('liveSearch');

    if (downloadBtn) {
      downloadBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const tanggal_awal = "{{ request('tanggal_awal') }}";
        const tanggal_akhir = "{{ request('tanggal_akhir') }}";
        const q = searchInput ? searchInput.value : '';

        const url = `{{ route('account.laporan_gaji.download-excel') }}?tanggal_awal=${tanggal_awal}&tanggal_akhir=${tanggal_akhir}&q=${encodeURIComponent(q)}`;
        window.location.href = url;
      });
    }
  });
</script>
// <!--================== END EXPORT EXCEL DATA GAJI ==================-->

// <!--================== LIVE SEARCH GAJI ==================-->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    let timer;
    const searchInput = document.getElementById('liveSearch');
    const clearSearchBtn = document.getElementById('clearSearch');

    function performSearch(query) {
      // Update parameter URL di browser secara live
      const currentUrl = new URL(window.location.href);
      if (query) {
        currentUrl.searchParams.set('q', query);
      } else {
        currentUrl.searchParams.delete('q');
      }
      window.history.pushState({}, '', currentUrl);

      // Fetch data menggunakan AJAX
      fetch(`{{ route('account.gaji.search') }}${currentUrl.search}`)
        .then(response => response.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');

          // Update isi tabel
          const newTableBody = doc.querySelector('#gajiTable');
          if (newTableBody) {
            document.getElementById('gajiTable').innerHTML = newTableBody.innerHTML;
          }

          // Update Pagination
          const newPagination = doc.querySelector('.d-flex.justify-content-center.mt-3.mb-2');
          const currentPagination = document.querySelector('.d-flex.justify-content-center.mt-3.mb-2');
          if (currentPagination) {
            currentPagination.innerHTML = newPagination ? newPagination.innerHTML : '';
          }
        });
    }

    if (searchInput) {
      // Mempertahankan teks dan tombol X saat halaman di-refresh
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('q')) {
        searchInput.value = urlParams.get('q');
        if (clearSearchBtn) clearSearchBtn.style.display = 'block';
      }

      // Deteksi ketikan secara live
      searchInput.addEventListener('input', function() {
        clearTimeout(timer);
        const query = this.value;

        // Munculkan/Sembunyikan tombol X
        if (clearSearchBtn) {
          clearSearchBtn.style.display = query.length > 0 ? 'block' : 'none';
        }

        timer = setTimeout(() => {
          performSearch(query);
        }, 300);
      });

      // EKSEKUSI TOMBOL X (RESET TOTAL KE HALAMAN INDEX)
      if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function(e) {
          e.preventDefault();
          // Langsung redirect ke route index (semua filter URL otomatis hilang)
          window.location.href = "{{ route('account.gaji.index') }}";
        });
      }
    }
  });
</script>
// <!--================== END LIVE SEARCH GAJI ==================-->

// <!--================== TAMBAH GAJI ==================-->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('tambahGajiBtn');
    if (btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        Swal.fire({
          icon: 'warning',
          title: 'Perhatian',
          text: 'Belum Ada Karyawan Yang Presensi pada bulan ini!',
          confirmButtonColor: '#6366f1',
          confirmButtonText: 'OK Mengerti',
          customClass: {
            popup: 'glossy-swal-popup'
          }
        });
      });
    }
  });
</script>
// <!--================== END TAMBAH GAJI ==================-->

// <!--================== NOTIFIKASI ==================-->
<script>
  @if(Session::has('success'))
  setTimeout(function() {
    window.location.reload();
  }, 1000);
  @endif
</script>
// <!--================== END NOTIFIKASI ==================-->

// <!--================== DELETE GAJI ==================-->
<script>
  function Delete(id) {
    Swal.fire({
      title: 'Apakah Anda yakin?',
      text: "Data yang dihapus tidak dapat dikembalikan!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#6366f1',
      cancelButtonColor: '#f43f5e',
      confirmButtonText: 'YA, HAPUS!',
      cancelButtonText: 'BATAL',
      borderRadius: '15px'
    }).then((result) => {
      if (result.isConfirmed) {
        let token = $("meta[name='csrf-token']").attr("content");

        $.ajax({
          url: "/account/gaji/delete/" + id,
          type: "DELETE",
          cache: false,
          data: {
            "_token": token
          },
          success: function(response) {
            if (response.status === "success" || response.status === true) {
              Swal.fire({
                icon: 'success',
                title: 'BERHASIL!',
                text: response.message || 'Data berhasil dihapus.',
                showConfirmButton: false,
                timer: 2000
              }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'GAGAL!',
                text: response.message
              });
            }
          },
          error: function(xhr) {
            Swal.fire({
              icon: 'error',
              title: 'ERROR!',
              text: 'Terjadi kesalahan pada server atau akses ditolak.'
            });
          }
        });
      }
    })
  }
</script>
// <!--================== END DELETE GAJI ==================-->
@stop