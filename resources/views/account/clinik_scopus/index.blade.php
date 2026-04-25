@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Clinik Scopus Data Trainer | MIS
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
      padding: 25px 20px;
    }

    .header-controls {
      flex-direction: column;
      /* Membuat elemen berjejer ke bawah */
      width: 100%;
      gap: 15px;
    }

    .search-neo {
      width: 100% !important;
      /* Search bar memenuhi lebar layar */
      max-width: none;
    }

    /* 🔹 Perbaikan tombol agar besar di HP */
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

    .title-section h1 {
      justify-content: center;
      font-size: 24px;
    }
  }

  /* 🔹 Badge Spesialis Modern & Responsive */
  .specialist-container {
    display: flex;
    align-items: flex-start;
    /* Ikon tetap di atas saat teks turun */
    gap: 10px;
    background: linear-gradient(to right, rgba(99, 102, 241, 0.05), transparent);
    padding: 10px 14px;
    border-radius: 16px;
    border-left: 3px solid var(--accent);
    transition: all 0.3s ease;
  }

  .specialist-icon {
    color: var(--accent);
    font-size: 14px;
    margin-top: 2px;
    /* Alignment dengan baris pertama teks */
  }

  .specialist-content {
    display: flex;
    flex-direction: column;
  }

  .specialist-label {
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #94a3b8;
    font-weight: 800;
    margin-bottom: 2px;
  }

  .specialist-text {
    font-size: 12px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.4;
    white-space: normal;
    /* Biarkan teks membungkus ke bawah */
    word-break: break-word;
  }

  /* Hover effect pada card agar spesialis lebih menonjol */
  .customer-card:hover .specialist-container {
    background: rgba(99, 102, 241, 0.1);
    transform: translateX(5px);
  }
</style>

@section('content')
<div class="main-content">
  <section class="section">

    <div class="hero-glass">
      <div class="title-section">
        <h1>
          Master Trainer
          <span class="total-badge" id="totalCounter">{{ count($data) }} Trainers</span>
        </h1>
        <p class="text-muted font-weight-bold">Manajemen jadwal dan keahlian spesialis trainer Scopus Anda.</p>
      </div>

      <div class="header-controls">
        <div class="search-neo">
          <i class="fas fa-search text-primary"></i>
          <input type="text" id="liveSearch" placeholder="Cari trainer..." autocomplete="off">
          <button type="button" id="clearSearch" class="border-0 bg-transparent" style="display:none; cursor:pointer;">
            <i class="fas fa-times-circle text-muted"></i>
          </button>
        </div>

        @if (Auth::user()->level === 'manager')
        <a href="{{ route('account.clinikscopus.create') }}" class="btn-modern shadow-sm font-weight-bold btn-create-animate"
          style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: white;">
          <i class="fas fa-plus-circle" style="font-size: 18px;"></i>
          <span style="letter-spacing: 0.5px;">TAMBAH DATA</span>
        </a>
        @endif
      </div>
    </div>

    <div id="customerTable">
      @forelse ($data as $item)
      <div class="customer-card">
        <div class="card-top">
          <div class="avatar-wrap">
            @php
            $userFoto = $item->foto; // Sesuaikan dengan nama kolom di DB Anda
            $path = 'ClinikScopusTrainer/' . $userFoto;

            $displayFoto = (!empty($userFoto) && file_exists(public_path($path)))
            ? asset($path)
            : asset('assets/img/avatar/avatar-1.png');
            @endphp

            <img src="{{ $displayFoto }}" class="avatar-img" alt="Avatar">
            <div class="status-indicator {{ $item->status != 'active' ? 'non-active' : '' }}"></div>
          </div>
          <div style="display: inline-flex; padding: 6px 14px; border-radius: 10px; font-size: 10px; font-weight: 800; text-transform: uppercase; background: {{ $item->status == 'active' ? '#d1fae5' : '#fee2e2' }}; color: {{ $item->status == 'active' ? '#065f46' : '#991b1b' }};">
            {{ $item->status }}
          </div>
        </div>

        <div class="customer-meta mb-3">
          <h5 class="mb-2 font-weight-800 text-dark" style="letter-spacing: -0.5px; line-height: 1.2;">
            {{ $item->full_name }}
          </h5>

          <div class="specialist-container">
            <div class="specialist-icon">
              <i class="fas fa-microscope"></i>
            </div>
            <div class="specialist-content">
              <span class="specialist-label">Keahlian Spesialis</span>
              <span class="specialist-text">{{ $item->spesialis }}</span>
            </div>
          </div>
        </div>

        <div style="background: #f8fafc; border-radius: 15px; padding: 15px; margin-bottom: 15px; border: 1px solid #f1f5f9;">
          <label class="text-muted font-weight-bold small d-block mb-2 text-uppercase" style="font-size: 9px; letter-spacing: 1px;">Jadwal Sesi Aktif</label>
          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
            @foreach(['sesi','sesi2','sesi3','sesi4','sesi5','sesi6','sesi7','sesi8','sesi9'] as $index => $field)
            @if(!empty($item->$field))
            <div class="text-center p-1" style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 9px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
              <strong class="d-block text-primary">S{{ $index+1 }}</strong>
              <span class="text-dark">{{ explode(' - ', $item->$field)[0] }}</span>
            </div>
            @endif
            @endforeach
          </div>
        </div>

        <div class="info-grid">
          <div class="info-item">
            <label>Tanggal Online</label>
            <span>{{ \Carbon\Carbon::parse($item->tanggal_online)->translatedFormat('d M Y') }}</span>
          </div>
          <div class="info-item">
            <label>Tanggal Offline</label>
            <span>{{ \Carbon\Carbon::parse($item->tanggal_offline)->translatedFormat('d M Y') }}</span>
          </div>
        </div>

        <div class="action-wrap">
          @if (Auth::user()->level === 'manager' || Auth::user()->id === $item->user_id)
          <a href="{{ route('account.clinikscopus.edit', $item->id) }}" class="btn-modern btn-edit">
            <i class="fas fa-user-edit"></i> Edit
          </a>

          @if (Auth::user()->level === 'manager')
          <button type="button" onclick="Delete('{{ $item->id }}')" class="btn-modern btn-delete">
            <i class="fas fa-trash-alt"></i> Hapus
          </button>
          @endif
          @else
          <span class="text-muted small w-100 text-center font-weight-bold">
            <i class="fas fa-lock mr-1"></i> Read Only Mode
          </span>
          @endif
        </div>
      </div>
      @empty
      <div class=" text-center" style="grid-column: 1 / -1;">
        <div class="customer-card" style="background: var(--card-bg); border: 1px solid rgba(255, 255, 255, 0.7); box-shadow: var(--shadow-soft);">
          <div style="background: var(--card-bg); padding: 60px 20px; border-radius: var(--radius-xl); border: 2px dashed #e2e8f0; margin: 20px;">
            <div class="mb-4">
              <i class="fas fa-user" style="font-size: 64px; color: #cbd5e1;"></i>
            </div>
            <h4 style="font-weight: 800; color: #475569;">Data Tidak Ditemukan</h4>
            <p style="color: #94a3b8; font-weight: 600;">Maaf, sepertinya tidak ada data trainer yang cocok dengan pencarian Anda.</p>
            <a href="{{ route('account.clinikscopus.index') }}" class="btn btn-primary mt-3" style="border-radius: 50px; padding: 10px 25px; font-weight: 700; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);">
              <i class="fas fa-sync-alt mr-2"></i> Muat Ulang Halaman
            </a>
          </div>
        </div>
      </div>
      @endforelse
    </div>

    <div class="d-flex justify-content-center mt-5" id="paginationWrapper">
      {{ $data->links() }}
    </div>

  </section>
</div>

<!-- ================== LIVE SEARCH ================== -->
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
          `{{ route('account.clinikscopus.index') }}` :
          `{{ route('account.clinikscopus.search') }}?q=${encodeURIComponent(query)}`;

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
</script>

<!-- ================== END ================== -->

<!--================== RELOAD DATA KETIKA SUKSES ==================-->
<script>
  @if(Session::has('success'))
  // Menggunakan setTimeout untuk menunggu pesan sukses muncul sebelum melakukan refresh
  setTimeout(function() {
    window.location.reload();
  }, 1000); // Refresh halaman setelah 2 detik
  @endif
</script>
<!--================== END ==================-->

<!--==================  SWEET ALERT DELETET  ==================-->
<script>
  function Delete(id) {
    const token = document.querySelector("meta[name='csrf-token']").getAttribute("content");

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
          url: "{{ route('account.clinikscopus.index') }}/" + id,
          type: 'POST',
          data: {
            _token: token,
            _method: 'DELETE'
          },
          success: function(response) {
            Swal.fire({
              title: 'BERHASIL!',
              text: response.message || 'Data berhasil dihapus!',
              icon: 'success',
              timer: 1000,
              showConfirmButton: false,
              willClose: () => location.reload()
            });
          },
          error: function(xhr) {
            let msg = 'Terjadi kesalahan saat menghapus data.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
              msg = xhr.responseJSON.message;
            }
            Swal.fire('Gagal!', msg, 'error');
          }
        });
      }
    });
  }
</script>
<!--================== END ==================-->

@stop