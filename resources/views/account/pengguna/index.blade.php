@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Data Karyawan | MIS
@stop

<style>
  :root {
    --accent: #6366f1;
    --accent-light: #818cf8;
    --bg-main: #f4f7ff;
    --card-bg: rgba(255, 255, 255, 0.9);
    --radius-xl: 24px;
    --radius-md: 16px;
    --shadow-soft: 0 20px 40px rgba(0, 0, 0, 0.04);
  }

  .main-content {
    padding-top: 120px !important;
    background-color: var(--bg-main);
    min-height: 100vh;
  }

  .hero-glass {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-xl);
    padding: 30px;
    border: 1px solid rgba(255, 255, 255, 0.5);
    box-shadow: var(--shadow-soft);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 40px;
  }

  .title-section h1 {
    font-size: 28px;
    font-weight: 800;
    background: linear-gradient(to right, #1e293b, #6366f1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: -1.5px;
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 0px;
  }

  .total-badge {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    color: white;
    padding: 5px 18px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 700;
    box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
    -webkit-text-fill-color: white;
    letter-spacing: 0.5px;
  }

  .header-controls {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .search-neo {
    background: #ffffff;
    border-radius: 50px;
    padding: 8px 25px;
    display: flex;
    align-items: center;
    box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.05), 0 5px 15px rgba(99, 102, 241, 0.1);
    width: 300px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  }

  .search-neo:focus-within {
    width: 380px;
  }

  .search-neo input {
    border: none;
    background: transparent;
    padding: 10px;
    width: 100%;
    font-weight: 600;
    color: #475569;
  }

  .search-neo input:focus {
    outline: none;
  }

  #customerTable {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
    position: relative;
  }

  .customer-card {
    background: var(--card-bg);
    backdrop-filter: blur(5px);
    border-radius: var(--radius-xl);
    padding: 25px;
    border: 1px solid rgba(255, 255, 255, 0.7);
    box-shadow: var(--shadow-soft);
    transition: all 0.4s ease;
  }

  .customer-card:hover {
    transform: translateY(-10px);
    background: #ffffff;
  }

  .card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
  }

  .avatar-wrap {
    position: relative;
    width: 65px;
    height: 65px;
  }

  .avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 18px;
    object-fit: cover;
  }

  .status-indicator {
    position: absolute;
    bottom: -4px;
    right: -4px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid #fff;
    background: #22c55e;
  }

  .status-indicator.non-active {
    background: #ef4444;
  }

  .info-grid-modern {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 20px;
    padding-top: 18px;
    border-top: 1px dashed #e2e8f0;
  }

  .info-chip {
    padding: 10px;
    border-radius: 14px;
    background: #f8fafc;
    border: 1px solid transparent;
  }

  .info-chip label {
    display: block;
    font-size: 9px;
    text-transform: uppercase;
    color: #94a3b8;
    font-weight: 800;
    margin-bottom: 5px;
  }

  .info-chip .value {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    color: #1e293b;
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

  /* 🔹 Animasi khusus untuk Tombol Create Data */
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

  @media (max-width: 992px) {
    .hero-glass {
      flex-direction: column;
      text-align: center;
      padding: 25px 20px;
    }

    .header-controls {
      flex-direction: column;
      width: 100%;
      gap: 15px;
    }

    .search-neo {
      width: 100% !important;
      max-width: none;
    }

    .header-controls .btn-modern {
      width: 100% !important;
      height: 50px;
      /* Memberikan tinggi yang cukup agar mudah ditekan */
      justify-content: center;
      display: flex !important;
      min-width: 100%;
      /* Memaksa tombol tidak mengecil */
      margin: 0;
    }
  }

  .status-verified {
    color: #10b981 !important;
  }

  .status-unverified {
    color: #f43f5e !important;
  }

  .status-active {
    color: #10b981 !important;
  }
</style>

@section('content')
<div class="main-content">
  <section class="section">
    <div class="hero-glass">
      <div class="title-section">
        <h1>
          Data Karyawan
          <span class="total-badge" id="totalCounter">{{ $users->total() }} Karyawan</span>
        </h1>
        <p class="text-muted font-weight-bold">Manajemen identitas dan status akses tim operasional Anda.</p>
      </div>

      <div class="header-controls">
        <div class="search-neo">
          <i class="fas fa-search text-primary"></i>
          <input type="text" id="liveSearch" placeholder="Cari karyawan..." autocomplete="off">
          <button type="button" id="clearSearch" class="border-0 bg-transparent" style="display:none; cursor:pointer;">
            <i class="fas fa-times-circle text-muted"></i>
          </button>
        </div>

        @if (Auth::user()->level === 'manager')
        <a href="{{ route('account.pengguna.create') }}" class="btn-modern shadow-sm font-weight-bold btn-create-animate"
          style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: white;">
          <i class="fas fa-plus-circle" style="font-size: 18px;"></i>
          <span style="letter-spacing: 0.5px;">TAMBAH DATA</span>
        </a>
        @endif
      </div>
    </div>

    <div id="customerTable">
      @if($users->count() > 0)
      @foreach ($users as $item)
      <div class="customer-card">
        <div class="card-top">
          <div class="avatar-wrap">
            <img src="{{ $item->gambar ? asset('assets/img/profil/' . $item->gambar) : asset('assets/img/profil/no-image.jpg') }}" class="avatar-img" alt="Profile">
            <div class="status-indicator {{ $item->status == 'active' ? '' : 'non-active' }}"></div>
          </div>
          <div class="text-right">
            @php
            // Logika penentuan warna berdasarkan Jobdesk
            $bg = '#f1f5f9'; // Default Gray
            $color = '#475569';

            switch(strtoupper($item->jobdesk)) {
            case 'MANAGER':
            $bg = 'rgba(99, 102, 241, 0.1)'; // Indigo/Purple
            $color = '#6366f1';
            break;
            case 'STAFF':
            $bg = 'rgba(16, 185, 129, 0.1)'; // Green
            $color = '#10b981';
            break;
            case 'ASISTEN TRAINER':
            $bg = 'rgba(245, 158, 11, 0.1)'; // Orange/Amber
            $color = '#f59e0b';
            break;
            case 'KARYAWAN':
            $bg = 'rgba(59, 130, 246, 0.1)'; // Blue
            $color = '#3b82f6';
            break;
            }
            @endphp

            <span class="d-inline-flex align-items-center"
              style="background: {{ $bg }}; color: {{ $color }}; padding: 6px 12px; border-radius: 10px; font-size: 10px; font-weight: 800; border: 1px solid rgba(0,0,0,0.05); text-transform: uppercase;">
              {{ $item->jobdesk }}
            </span>
          </div>
        </div>

        <div class="card-body-content">
          <h5 class="mb-0 font-weight-bold">{{ $item->username }}</h5>
          <p class="text-muted mb-3" style="font-size: 13px;">{{ $item->email }}</p>

          <div class="info-grid-modern">
            <div class="info-chip">
              <label>Verifikasi</label>
              <div class="value {{ $item->email_verified_at ? 'status-verified' : 'status-unverified' }}">
                <i class="fas {{ $item->email_verified_at ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
                <span>{{ $item->email_verified_at ? 'Verified' : 'Unverified' }}</span>
              </div>
            </div>
            <div class="info-chip">
              <label>Status Akun</label>
              <div class="value">
                <i class="fas fa-circle {{ $item->status == 'active' ? 'status-active' : 'text-muted' }}" style="font-size: 8px;"></i>
                <span class="text-capitalize">{{ $item->status }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="action-wrap" style="display:flex; gap:10px; margin-top:20px;">
          <a href="{{ route('account.pengguna.edit', $item->id) }}" class="btn-modern btn-edit"><i class="fas fa-edit"></i> Edit</a>
          <button onclick="Delete('{{ $item->id }}')" class="btn-modern btn-delete"><i class="fas fa-trash"></i> Hapus</button>
        </div>
      </div>
      @endforeach
      @else
      <div class=" text-center" style="grid-column: 1 / -1;">
        <div class="customer-card" style="background: var(--card-bg); border: 1px solid rgba(255, 255, 255, 0.7); box-shadow: var(--shadow-soft);">
          <div style="background: var(--card-bg); padding: 60px 20px; border-radius: var(--radius-xl); border: 2px dashed #e2e8f0; margin: 20px;">
            <div class="mb-4">
              <i class="fas fa-user" style="font-size: 64px; color: #cbd5e1;"></i>
            </div>
            <h4 style="font-weight: 800; color: #475569;">Data Tidak Ditemukan</h4>
            <p style="color: #94a3b8; font-weight: 600;">Maaf, sepertinya tidak ada data customer yang cocok dengan pencarian Anda.</p>
            <a href="{{ route('account.customer.index') }}" class="btn btn-primary mt-3" style="border-radius: 50px; padding: 10px 25px; font-weight: 700; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);">
              <i class="fas fa-sync-alt mr-2"></i> Muat Ulang Halaman
            </a>
          </div>
        </div>
      </div>
      @endif
    </div>

    <div id="paginationWrapper" class="d-flex justify-content-center mt-5">
      {{ $users->links("vendor.pagination.bootstrap-4") }}
    </div>

  </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    let timer;
    const liveSearchInput = document.getElementById('liveSearch');
    const clearSearchBtn = document.getElementById('clearSearch'); // Tambahkan variabel ini
    const container = document.getElementById('customerTable');
    const paginationWrapper = document.getElementById('paginationWrapper');
    const totalCounter = document.getElementById('totalCounter');

    function fetchContent(url) {
      container.style.opacity = '0.5';
      fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(response => response.text())
        .then(html => {
          container.style.opacity = '1';
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newContent = doc.querySelector('#customerTable');
          const newPagination = doc.querySelector('#paginationWrapper');
          const newTotal = doc.querySelector('#totalCounter');

          if (newContent && newContent.innerHTML.trim() !== "") {
            container.innerHTML = newContent.innerHTML;
          } else {
            container.innerHTML = `<div class="text-center w-100" style="grid-column: 1 / -1; padding: 50px;">
                                        <i class="fas fa-search-minus fa-3x mb-3 text-muted"></i>
                                        <h5 class="text-muted">Data tidak ditemukan</h5>
                                      </div>`;
          }

          if (newPagination && paginationWrapper) {
            paginationWrapper.innerHTML = newPagination.innerHTML;
          }

          if (newTotal && totalCounter) {
            totalCounter.innerText = newTotal.innerText;
          }
        })
        .catch(err => {
          container.style.opacity = '1';
          console.error("Fetch Error:", err);
        });
    }

    // 1. Handle Input Search
    liveSearchInput.addEventListener('input', function() {
      // Tampilkan/Sembunyikan tombol X
      clearSearchBtn.style.display = this.value.trim() ? 'block' : 'none';

      clearTimeout(timer);
      const query = this.value;

      timer = setTimeout(() => {
        let url = query.trim() === "" ?
          `{{ route('account.pengguna.index') }}` :
          `{{ route('account.pengguna.search') }}?q=${encodeURIComponent(query)}`;

        fetchContent(url);
      }, 300);
    });

    // 2. Handle Tombol Clear (Klik X)
    clearSearchBtn.addEventListener('click', function() {
      liveSearchInput.value = ''; // Kosongkan input
      this.style.display = 'none'; // Sembunyikan tombol X sendiri

      // Kembalikan ke data awal (index)
      const url = `{{ route('account.pengguna.index') }}`;
      fetchContent(url);
    });

    // 3. Handle Klik Pagination
    document.addEventListener('click', function(e) {
      const paginationLink = e.target.closest('#paginationWrapper a');
      if (paginationLink) {
        e.preventDefault();
        const url = paginationLink.getAttribute('href');
        if (url) {
          fetchContent(url);
          window.scrollTo({
            top: 0,
            behavior: 'smooth'
          });
        }
      }
    });
  });

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
      // Perbaikan: Properti yang benar adalah 'borderRadius', bukan 'borderRadius' di dalam string
    }).then((result) => {
      if (result.isConfirmed) {
        let token = $("meta[name='csrf-token']").attr("content");

        $.ajax({
          url: "/account/pengguna/delete/" + id, // Pastikan route ini sesuai dengan php artisan route:list
          type: "DELETE",
          cache: false,
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
          },
          error: function(xhr) {
            // Menangani jika terjadi error server (misal 500 atau 404)
            Swal.fire({
              icon: 'error',
              title: 'ERROR!',
              text: 'Terjadi kesalahan pada server atau akses ditolak.',
            });
          }
        });
      }
    })
  }
</script>
@stop