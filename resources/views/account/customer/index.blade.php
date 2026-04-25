@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Data Customer | MIS
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

  /* 🔹 Header Glassmorphism */
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

  /* 🔹 Cari bagian ini di dalam tag <style> */
  .title-section h1 {
    font-size: 28px;
    /* Diubah dari 2rem ke 28px */
    font-weight: 800;
    background: linear-gradient(to right, #1e293b, #6366f1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: -1.5px;
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 5px;
    text-decoration: none !important;
    margin: 0px;
    /* Memastikan tidak ada garis bawah */
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

  /* 🔹 Controls (Search & Export) */
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

  /* 🔹 Card Grid */
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
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    z-index: 1;
  }

  .customer-card::after {
    content: "";
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.08) 0%, transparent 70%);
    opacity: 0;
    transition: opacity 0.4s;
    z-index: -1;
    pointer-events: none;
  }

  .customer-card:hover {
    transform: translateY(-10px);
    background: #ffffff;
  }

  .customer-card:hover::after {
    opacity: 1;
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

  .info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px dashed #e2e8f0;
  }

  .info-item label {
    display: block;
    font-size: 10px;
    text-transform: uppercase;
    color: #94a3b8;
    font-weight: 800;
  }

  .info-item span {
    font-size: 12px;
    font-weight: 700;
    color: #1e293b;
  }

  .action-wrap {
    display: flex;
    gap: 12px;
    margin-top: 25px;
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

  @media (max-width: 992px) {
    .hero-glass {
      flex-direction: column;
      text-align: center;
    }

    .header-controls {
      width: 100%;
      justify-content: center;
    }

    .search-neo {
      width: 100%;
    }
  }
</style>

@section('content')
<div class="main-content">
  <section class="section">

    <div class="hero-glass">
      <div class="title-section">
        <h1>
          Customer Universe
          <span class="total-badge" id="totalCounter">{{ number_format($users->total()) }} Users</span>
        </h1>
        <p class="text-muted font-weight-bold">Insight dan manajemen data cerdas untuk bisnis Anda.</p>
      </div>

      <div class="header-controls">
        <div class="search-neo">
          <i class="fas fa-search text-primary"></i>
          <input type="text" id="liveSearch" placeholder="Cari data..." autocomplete="off">
          <button type="button" id="clearSearch" class="border-0 bg-transparent" style="display:none; cursor:pointer;">
            <i class="fas fa-times-circle text-muted"></i>
          </button>
        </div>
      </div>
    </div>

    <div id="customerTable">
      @if($users->count() > 0)
      @foreach ($users as $item)
      <div class="customer-card">
        <div class="card-top">
          <div class="avatar-wrap">
            <img src="{{ $item->gambar ? asset('assets/img/profil/' . $item->gambar) : asset('assets/img/avatar/avatar-1.png') }}" class="avatar-img">
            <div class="status-indicator {{ $item->status != 'active' ? 'non-active' : '' }}"></div>
          </div>
          <div>
            @if($item->email_verified_at)
            <div style="
        display: inline-flex; 
        align-items: center; 
        gap: 6px; 
        padding: 6px 12px; 
        border-radius: 8px; 
        background-color: #d1fae5; 
        color: #065f46; 
        font-size: 10px; 
        font-weight: 800; 
        text-transform: uppercase;">
              <i class="fas fa-check-circle" style="font-size: 12px;"></i>
              <span>Verified</span>
            </div>
            @else
            <div style="
        display: inline-flex; 
        align-items: center; 
        gap: 6px; 
        padding: 6px 12px; 
        border-radius: 8px; 
        background-color: #fef3c7; 
        color: #92400e; 
        font-size: 10px; 
        font-weight: 800; 
        text-transform: uppercase;">
              <i class="fas fa-clock" style="font-size: 12px;"></i>
              <span>Pending</span>
            </div>
            @endif
          </div>
        </div>

        <div class="customer-meta">
          <h5 class="mb-0 font-weight-800 text-dark">{{ $item->full_name ?? $item->username }}</h5>
          <small class="text-primary font-weight-bold">{{ $item->email }}</small>
        </div>

        <div class="info-grid">
          <div class="info-item">
            <label>Username</label>
            <span>{{ $item->username }}</span>
          </div>
          <div class="info-item">
            <label>Phone</label>
            <span>{{ $item->telp ?? '-' }}</span>
          </div>
          <div class="info-item">
            <label>Role</label>
            <span class="text-uppercase">{{ $item->level ?? 'Member' }}</span>
          </div>
          <div class="info-item">
            <label>Status</label>
            <span class="{{ $item->status == 'active' ? 'text-success' : 'text-danger' }}">{{ strtoupper($item->status) }}</span>
          </div>
        </div>

        <div class="action-wrap">
          <a href="{{ route('account.customer.edit', $item->id) }}" class="btn-modern btn-edit">
            <i class="fas fa-user-edit"></i> Edit
          </a>
          <button type="button" onclick="Delete('{{ $item->id }}')" class="btn-modern btn-delete">
            <i class="fas fa-trash-alt"></i> Hapus
          </button>
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

    <div class="d-flex justify-content-center mt-5" id="paginationWrapper">
      {{ $users->appends(['q' => request('q')])->links('vendor.pagination.bootstrap-4') }}
    </div>

  </section>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    let timer;
    const liveSearchInput = document.getElementById('liveSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const container = document.getElementById('customerTable');
    const paginationWrapper = document.getElementById('paginationWrapper');
    const totalCounter = document.getElementById('totalCounter');

    liveSearchInput.addEventListener('input', function() {
      clearSearchBtn.style.display = this.value.trim() ? 'block' : 'none';
      clearTimeout(timer);
      const query = this.value.trim();

      timer = setTimeout(() => {
        let url = query === "" ?
          `{{ route('account.customer.index') }}` :
          `{{ route('account.customer.search') }}?q=${encodeURIComponent(query)}`;

        fetch(url)
          .then(response => response.text())
          .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector('#customerTable');
            const newPagination = doc.querySelector('.pagination');
            const newTotalBadge = doc.querySelector('#totalCounter');

            container.innerHTML = newContent ? newContent.innerHTML : '<div class="w-100 text-center p-5"><h4>Data tidak ditemukan</h4></div>';

            if (paginationWrapper) {
              paginationWrapper.innerHTML = newPagination ? newPagination.outerHTML : '';
            }

            if (newTotalBadge && totalCounter) {
              totalCounter.innerText = newTotalBadge.innerText;
            }
          });
      }, 500);
    });

    clearSearchBtn.addEventListener('click', function() {
      liveSearchInput.value = '';
      this.style.display = 'none';
      liveSearchInput.dispatchEvent(new Event('input'));
    });
  });

  function Delete(id) {
    const token = "{{ csrf_token() }}";
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
        $.ajax({
          url: "{{ route('account.customer.destroy', '') }}/" + id,
          type: 'POST',
          data: {
            _token: token,
            _method: 'DELETE'
          },
          success: function() {
            Swal.fire({
                icon: 'success',
                title: 'Data Terhapus',
                showConfirmButton: false,
                timer: 1000
              })
              .then(() => location.reload());
          }
        });
      }
    });
  }
</script>
@stop