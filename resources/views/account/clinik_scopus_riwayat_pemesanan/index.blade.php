@extends('layouts.account')
@extends('layouts.loader')
@extends('layouts.headerfitur')

@section('title')
Clinik Scopus Riwayat Pemesanan | MIS
@stop

<style>
  :root {
    --glass-bg: rgba(255, 255, 255, 0.8);
    --glass-border: rgba(255, 255, 255, 0.5);
    --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);

    .booking-grid {
      display: grid;
      /* Membuat 3 kolom sama rata di desktop */
      grid-template-columns: repeat(3, 1fr);
      gap: 25px;
      padding: 20px 0;
      align-items: stretch;
      /* Memastikan tinggi card sama dalam satu baris */
    }

    @media (max-width: 1100px) {
      .booking-grid {
        grid-template-columns: repeat(2, 1fr);
        /* Menjadi 2 kolom */
        gap: 20px;
      }
    }

    @media (max-width: 768px) {
      .booking-grid {
        grid-template-columns: 1fr;
        /* Menjadi 1 kolom (full width) */
        gap: 15px;
      }
    }

    .card-booking {
      background: var(--glass-bg);
      backdrop-filter: blur(10px);
      border-radius: 35px;
      border: 1px solid var(--glass-border);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
      transition: all 0.5s ease;
      display: flex;
      flex-direction: column;
      height: 100%;
      position: relative;
    }

    .card-booking:hover {
      transform: translateY(-10px);
      border-color: #6366f1;
      box-shadow: 0 25px 50px rgba(99, 102, 241, 0.15);
    }

    /* 🔹 Kendala / Issue Box */
    .issue-container {
      margin-top: 15px;
      padding: 12px 18px;
      background: rgba(239, 68, 68, 0.08);
      /* Soft Red */
      border-radius: 20px;
      border-left: 4px solid #ef4444;
    }

    .issue-label {
      font-size: 9px;
      font-weight: 900;
      color: #ef4444;
      text-transform: uppercase;
      display: flex;
      align-items: center;
      gap: 5px;
      margin-bottom: 4px;
    }

    .issue-text {
      font-size: 12px;
      color: #7f1d1d;
      font-weight: 600;
      line-height: 1.4;
    }

    /* 🔹 Sesi Info Styling */
    .sesi-badge {
      display: inline-block;
      padding: 4px 12px;
      background: #f1f5f9;
      border-radius: 10px;
      font-size: 11px;
      font-weight: 700;
      color: #475569;
      margin-bottom: 8px;
    }

    .sesi-title {
      font-size: 20px;
      font-weight: 800;
      color: #1e293b;
      margin-bottom: 15px;
      display: block;
      line-height: 1.2;
    }

    .bento-info {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .bento-item {
      background: white;
      padding: 12px;
      border-radius: 18px;
      border: 1px solid #f1f5f9;
    }

    .bento-item .val {
      font-size: 12px;
      font-weight: 700;
      color: #1e293b;
    }

    /* 🔹 Base Status Badge */
    .status-badge {
      position: absolute;
      top: 25px;
      right: 25px;
      padding: 6px 14px;
      border-radius: 12px;
      font-size: 10px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      display: flex;
      align-items: center;
      gap: 6px;
      z-index: 5;
    }

    /* Dot Indikator di depan teks */
    .status-badge::before {
      content: '';
      width: 6px;
      height: 6px;
      border-radius: 50%;
    }

    /* 🔹 Variant: PAID (Sudah Bayar) */
    .status-paid {
      background: #dcfce7;
      /* Soft Green */
      color: #166534;
      border: 1px solid rgba(22, 101, 52, 0.1);
    }

    .status-paid::before {
      background: #22c55e;
      box-shadow: 0 0 8px #22c55e;
    }

    /* 🔹 Variant: PENDING (Menunggu) */
    .status-pending {
      background: #fef3c7;
      /* Soft Amber */
      color: #92400e;
      border: 1px solid rgba(146, 64, 14, 0.1);
    }

    .status-pending::before {
      background: #f59e0b;
    }

    /* 🔹 Variant: COMPLETED (Selesai) */
    .status-completed {
      background: #e0e7ff;
      /* Soft Indigo */
      color: #3730a3;
      border: 1px solid rgba(55, 48, 163, 0.1);
    }

    .status-completed::before {
      background: #6366f1;
    }

    /* 🔹 Variant: CANCELED (Dibatalkan) */
    .status-canceled {
      background: #fee2e2;
      /* Soft Red */
      color: #991b1b;
      border: 1px solid rgba(153, 27, 27, 0.1);
    }

    .status-canceled::before {
      background: #ef4444;
    }

    .action-wrap {
      display: flex;
      gap: 12px;
      position: relative;
      z-index: 10;
    }

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

    .btn-edit {
      background: #eef2ff;
      color: #6366f1;
    }

    .btn-edit:hover {
      background: #6366f1;
      color: #fff;
      transform: translateY(-3px);
    }

    .btn-delete {
      background: #fff1f2;
      color: #f43f5e;
    }

    .btn-delete:hover {
      background: #f43f5e;
      color: #fff;
      transform: translateY(-3px);
    }

    .btn-chat {
      background: #ecfdf5;
      color: #059669;
    }

    .btn-chat:hover {
      background: #10b981;
      color: #ffffff;
      transform: translateY(-3px);
    }

    .btn-locked {
      background: #fef3c7;
      color: #78350f;
      border: 1px solid #fcd34d;
      cursor: not-allowed;
    }

    .btn-locked i {
      color: #92400e;
    }

    .btn-locked:hover {
      transform: none;
      background: #fef3c7;
      color: #78350f;
    }

    /* 🔹 Wrapper Utama Tabs */
    .status-tabs-wrapper {
      background: #f8fafc;
      padding: 8px;
      border-radius: 25px;
      display: inline-flex;
      border: 1px solid #e2e8f0;
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .nav-pills#statusTabs {
      border: none;
      gap: 5px;
    }

    .nav-pills .nav-item .nav-link {
      border-radius: 20px;
      padding: 10px 24px;
      font-size: 13px;
      font-weight: 800;
      color: #64748b;
      border: none;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      align-items: center;
      gap: 8px;
      background: transparent;
    }

    /* 🔹 State Aktif yang Berwarna-warni */
    .nav-pills .nav-link.active[data-status="all"] {
      background: #1e293b;
      color: white;
      box-shadow: 0 10px 15px -3px rgba(30, 41, 59, 0.3);
    }

    .nav-pills .nav-link.active[data-status="pending"] {
      background: #f59e0b;
      color: white;
      box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.3);
    }

    .nav-pills .nav-link.active[data-status="paid"] {
      background: #10b981;
      color: white;
      box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
    }

    .nav-pills .nav-link.active[data-status="completed"] {
      background: #6366f1;
      color: white;
      box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
    }

    .nav-pills .nav-link.active[data-status="canceled"] {
      background: #ef4444;
      color: white;
      box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.3);
    }

    .nav-pills .nav-link:hover:not(.active) {
      background: #ffffff;
      color: #334155;
      transform: translateY(-1px);
    }

    /* 🔹 Badge Angka yang Cantik */
    .tab-count {
      font-size: 10px;
      padding: 2px 8px;
      border-radius: 10px;
      background: rgba(0, 0, 0, 0.05);
      color: inherit;
      transition: all 0.3s;
    }

    .nav-link.active .tab-count {
      background: rgba(255, 255, 255, 0.2);
      color: white;
    }

    /* 🔹 Perbaikan Mobile Responsive untuk Tabs */
    /* 🔹 TABLET & IPAD MINI (768px ke atas) */
    /* Kita ubah min-width menjadi 768px agar iPad Mini ikut ke sini */
    @media (min-width: 768px) {
      .d-flex.justify-content-center {
        justify-content: center !important;
        padding: 0 20px;
      }

      .status-tabs-wrapper {
        display: flex;
        width: auto;
        min-width: 600px;
        /* Menjaga agar tidak terlalu ciut di tablet */
        max-width: 100%;
        background: #f8fafc;
        padding: 7px;
        border-radius: 25px;
        border: 1px solid #e2e8f0;
      }

      .nav-pills#statusTabs {
        display: flex;
        width: 100%;
        /* Paksa UL ambil ruang penuh */
        justify-content: space-between;
        /* Bagi ruang rata kiri-kanan */
        flex-wrap: nowrap;
      }

      .nav-pills .nav-item {
        flex: 1;
        /* Membuat setiap tab memiliki lebar yang sama/adil */
        text-align: center;
      }

      .nav-pills .nav-item .nav-link {
        justify-content: center;
        /* Teks & Ikon di tengah */
        padding: 12px 10px;
        font-size: 13px;
        white-space: nowrap;
      }

      .action-bar-glass {
        gap: 8px;
      }

      .btn-modern {
        padding: 10px 12px;
        font-size: 11px;
        gap: 5px;
      }

      .action-wrap {
        gap: 6px;
      }
    }

    /* 🔹 HANDPHONE SAJA (Dibawah 768px) */
    @media (max-width: 767px) {
      .d-flex.justify-content-center {
        justify-content: flex-start !important;
        padding: 0 15px;
      }

      .status-tabs-wrapper {
        display: flex;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        border-radius: 18px;
        padding: 5px;
        background: #f8fafc;
      }

      .status-tabs-wrapper::-webkit-scrollbar {
        display: none;
      }

      .nav-pills#statusTabs {
        flex-wrap: nowrap;
        display: flex;
      }

      .nav-pills .nav-item .nav-link {
        padding: 8px 14px;
        font-size: 11px;
        white-space: nowrap;
      }

      .tab-count {
        padding: 1px 5px;
        font-size: 9px;
      }

      /* 1. Baris utama dibagi menjadi dua baris vertikal */
      .action-bar-glass {
        flex-direction: column;
        gap: 12px;
        /* Jarak antar baris tombol */
        padding-top: 15px !important;
      }

      /* 2. Setiap grup tombol (Chat & Edit/Delete) mengambil lebar penuh */
      .action-wrap {
        width: 100%;
        display: flex;
        gap: 8px;
        /* Jarak antar tombol yang bersisian */
      }

      /* 4. Ukuran tombol disesuaikan untuk layar sentuh */
      .btn-modern {
        flex: 1;
        /* Tombol membagi lebar secara adil (50:50) */
        padding: 12px 8px;
        font-size: 11px;
        border-radius: 12px;
        height: 45px;
        /* Tinggi standar jempol agar mudah diklik */
      }

      .btn-modern i {
        font-size: 14px;
      }
    }
</style>

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header-modern">
      <div>
        <h1>Riwayat Pemesanan
        </h1>
        <p class="text-muted font-weight-bold mb-0">Daftar administrasi sesi bimbingan Scopus dalam tampilan kartu.</p>
      </div>

      <div class="search-container">
        <i class="fas fa-search"></i>
        <input type="text" id="liveSearch" class="form-control-modern w-100" placeholder="Cari data..." autocomplete="off">
        <span id="clearSearch" style="display:none; cursor:pointer;">✕</span>
      </div>
    </div>

    <div class="d-flex justify-content-center">
      <div class="status-tabs-wrapper">
        <ul class="nav nav-pills" id="statusTabs">
          <li class="nav-item">
            <a class="nav-link active" href="#" data-status="all">
              <i class="fas fa-grid-2"></i> Semua <span class="tab-count" data-count="all">0</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" data-status="pending">
              <i class="fas fa-clock"></i> Pending <span class="tab-count" data-count="pending">0</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" data-status="paid">
              <i class="fas fa-check-circle"></i> Paid <span class="tab-count" data-count="paid">0</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" data-status="completed">
              <i class="fas fa-flag-checkered"></i> Done <span class="tab-count" data-count="completed">0</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" data-status="canceled">
              <i class="fas fa-times-circle"></i> Cancel <span class="tab-count" data-count="canceled">0</span>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <div id="search-results">
      @if($datas->count() > 0)
      <div class="booking-grid" id="customerTable">
        @foreach ($datas as $item)
        <div class="card-booking p-4" data-status="{{ $item->status }}">
          <div class="status-badge status-{{ $item->status }}">
            {{ $item->status }}
          </div>

          <div class="card-booking-content">
            <span class="booking-id-text text-muted small">#{{ $item->kode_booking }}</span>
            <h2 class="sesi-title">{{ $item->sesi ?? '-' }}</h2>

            <div class="bento-info">
              <div class="bento-item">
                <span class="label text-muted d-block" style="font-size: 9px;">TANGGAL BOOKING</span>
                <span class="val">
                  {{ $item->tanggal_booking ? \Carbon\Carbon::parse($item->tanggal_booking)->translatedFormat('d F Y') : '-' }}
                </span>
              </div>
              <div class="bento-item">
                <span class="label text-muted d-block" style="font-size: 9px;">JAM SESI</span>
                <span class="val">{{ $item->jam_sesi ?? '--:--' }}</span>
              </div>
            </div>

            @if($item->kendala)
            <div class="issue-container">
              <div class="issue-label">
                <i class="fas fa-exclamation-triangle"></i> Kendala Sesi
              </div>
              <div class="issue-text italic">
                "{{ $item->kendala }}"
              </div>
            </div>
            @endif

            <div class="profile-stack mt-4 d-flex align-items-center bg-white p-3 shadow-sm" style="border-radius: 20px;">
              <div class="avatar-stack shadow-sm" style="width: 45px; height: 45px; border-radius: 14px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #f1f5f9;">
                @if($item->customer && $item->customer->gambar && file_exists(public_path('assets/img/profil/' . $item->customer->gambar)))
                <img src="{{ asset('assets/img/profil/' . $item->customer->gambar) }}"
                  alt="Profile {{ $item->customer->full_name }}"
                  style="width: 100%; height: 100%; object-fit: cover;">
                @else
                <div class="bg-primary text-white d-flex align-items-center justify-content-center"
                  style="width: 100%; height: 100%; font-weight: 800; font-size: 16px; background: var(--primary-gradient) !important;">
                  {{ strtoupper(substr($item->customer->full_name ?? 'C', 0, 1)) }}
                </div>
                @endif
              </div>
              <div class="ml-3">
                <span class="d-block font-weight-bold" style="font-size: 13px;">{{ $item->customer->full_name ?? '-' }}</span>
                <span class="text-muted" style="font-size: 11px;">Trainer: <b class="text-primary">{{ $item->trainer->full_name ?? 'Pending' }}</b></span>
              </div>
            </div>

            <div class="action-bar-glass mt-4 pt-3 d-flex justify-content-between " style="border-top: 1px dashed #ddd;">
              <div class="action-wrap">
                @if ($item->status === 'paid')
                <a href="{{ route('chat.index', $item->id) }}" class="btn-modern btn-chat" style="display: inline-flex;">
                  <i class="fas fa-comments mr-2"></i>Live Chat
                </a>
                @else
                <span class="btn-modern btn-locked"><i class="fas fa-lock mr-1"></i>Chat Locked</span>
                @endif
              </div>

              <div class="action-wrap">
                <a href="{{ route('account.Clinik-Scopus-Riwayat-Pemesanan.detail', $item->id) }}" class="btn-modern btn-edit" style="display: inline-flex;">
                  <i class="fa fa-clipboard-list"></i>Detail
                </a>
                @if (Auth::user()->level === 'manager')
                <button onclick="Delete('{{ $item->id  }}')" type="button" class="btn-modern btn-delete" style="display: inline-flex;">
                  <i class="fas fa-trash"></i>
                </button>
                @endif
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="text-center py-5">
        <div style="background: var(--card-bg); padding: 60px 20px; border-radius: var(--radius-xl); border: 2px dashed #e2e8f0; margin: 20px;">
          <div class="mb-4">
            <i class="fas fa-search-minus" style="font-size: 64px; color: #cbd5e1; transform: rotate(-15deg);"></i>
          </div>
          <h4 style="font-weight: 800; color: #475569;">Data Tidak Ditemukan</h4>
          <p style="color: #94a3b8; font-weight: 600;">Maaf, sepertinya tidak ada data riwayat pemesanan yang cocok dengan pencarian Anda.</p>
          <a href="{{ route('account.Clinik-Scopus-Riwayat-Pemesanan.index') }}" class="btn btn-primary mt-3" style="border-radius: 50px; padding: 10px 25px; font-weight: 700;">
            <i class="fas fa-sync-alt mr-2"></i> Segarkan Halaman
          </a>
        </div>
      </div>
      @endif
    </div>

    <div id="paginationWrapper" class="mt-4 d-flex justify-content-center">
      @if ($datas instanceof \Illuminate\Pagination\LengthAwarePaginator)
      {{ $datas->links('pagination::bootstrap-4') }}
      @endif
    </div>

  </section>
</div>

<!--================== PROTEKSI AKSES SESI CHAT ==================-->
@if(session('alert'))
<script>
  Swal.fire({
    icon: "{{ session('alert.type') }}",
    title: "{{ session('alert.title') }}",
    text: "{{ session('alert.message') }}",
    confirmButtonColor: '#3085d6'
  });
</script>
@endif
<!--================== END PROTEKSI AKSES SESI CHAT ==================-->

<!--================== TABS STATUS DATA ==================-->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('#statusTabs .nav-link');
    const cards = document.querySelectorAll('.card-booking');

    // --- 1. Fungsi Update Counter ---
    function updateCounts() {
      const counts = {
        all: cards.length,
        pending: 0,
        paid: 0,
        completed: 0,
        canceled: 0
      };

      cards.forEach(card => {
        const status = card.dataset.status.toLowerCase();
        if (counts.hasOwnProperty(status)) counts[status]++;
      });

      Object.keys(counts).forEach(key => {
        const el = document.querySelector(`[data-count="${key}"]`);
        if (el) el.textContent = counts[key];
      });
    }

    // --- 2. Fungsi Filter Data ---
    tabs.forEach(tab => {
      tab.addEventListener('click', function(e) {
        e.preventDefault();

        // Toggle Class Active
        tabs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        const selectedStatus = this.dataset.status.toLowerCase();

        cards.forEach(card => {
          const cardStatus = card.dataset.status.toLowerCase();

          if (selectedStatus === 'all' || cardStatus === selectedStatus) {
            card.style.display = 'flex'; // Tampilkan
            card.style.animation = 'fadeInUp 0.4s ease forwards';
          } else {
            card.style.display = 'none'; // Sembunyikan
          }
        });
      });
    });

    // Jalankan counter saat page load
    updateCounts();
  });
</script>

<style>
  /* Animasi halus saat filter berubah */
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>
<!--================== END ==================-->

<!-- ================== LIVE SEARCH ================== -->
<script>
  $(document).ready(function() {
    let timer;

    $('#liveSearch').on('keyup', function() {
      let query = $(this).val();

      // Kontrol tombol clear (X)
      if (query.length > 0) {
        $('#clearSearch').show();
      } else {
        $('#clearSearch').hide();
      }

      // Gunakan debounce agar tidak spam request ke server
      clearTimeout(timer);
      timer = setTimeout(function() {
        $.ajax({
          url: "{{ route('account.Clinik-Scopus-Riwayat-Pemesanan.search') }}",
          type: "GET",
          data: {
            'q': query
          },
          beforeSend: function() {
            // Beri efek transparan saat loading
            $('#customerTable').css('opacity', '0.5');
          },
          success: function(data) {
            // Ambil konten dari response AJAX
            // Kita ambil isi dari #search-results dan #paginationWrapper
            let htmlContent = $(data).find('#search-results').html();
            let paginationContent = $(data).find('#paginationWrapper').html();

            // Masukkan ke halaman aktif
            $('#search-results').html(htmlContent);
            $('#paginationWrapper').html(paginationContent);

            // Kembalikan opacity
            $('#customerTable').css('opacity', '1');

            // Jalankan ulang fungsi hitung angka di tab status jika ada
            if (typeof updateCounts === "function") {
              updateCounts();
            }
          },
          error: function() {
            $('#customerTable').css('opacity', '1');
          }
        });
      }, 500); // Tunggu 0.5 detik setelah berhenti mengetik
    });

    // Fungsi klik tombol X
    $('#clearSearch').on('click', function() {
      $('#liveSearch').val('').trigger('keyup');
    });
  });
</script>

<!-- ================== END ================== -->

<!--================== DELETE DATA  ==================-->
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
          url: "/account/Clinik-Scopus-Riwayat-Pemesanan/delete/" + id,
          type: "DELETE",
          data: {
            "_token": token
          },
          success: function(response) {
            if (response.status) {
              Swal.fire({
                icon: 'success',
                title: 'BERHASIL!',
                text: response.message,
                showConfirmButton: false,
                timer: 2000
              }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'GAGAL!',
                text: response.message,
              });
            }
          }
        });
      }
    })
  }
</script>
<!--================== END ==================-->

@stop