@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Data Promo | MIS
@stop

<!-- ================== FILTER ================== -->
<style>
  /* 🔹 Responsive behavior */
  @media (max-width: 576px) {
    .card-header {
      flex-direction: column;
      align-items: stretch !important;
    }

    /* Search penuh */
    .search-wrapper {
      width: 100%;
    }

    /* Tombol sejajar (50% - 50%) */
    #toggleFilterBtn {
      width: 100%;
      justify-content: center;
      margin-top: 10px;
    }

    /* Rapatkan jarak antar tombol */
    .card-header .d-flex {
      gap: 0.5rem !important;
    }

    /* Hilangkan teks panjang kalau mau lebih ringkas di HP */
    #toggleFilterText {
      font-size: 12px;
    }
  }

  /* 🔹 Tambahkan jarak input → tombol di laptop/desktop */
  @media (min-width: 577px) {
    .search-wrapper {
      margin-right: 10px !important;
      /* 💡 jarak nyaman antara input dan tombol */
    }
  }
</style>

<style>
  /* 🔹 Wrapper agar posisi relatif terhadap tombol */
  .search-wrapper {
    position: relative;
    min-width: 200px;
  }

  /* 🔹 Tambahkan ruang di kanan agar teks tidak menabrak ikon */
  #liveSearch {
    padding-right: 36px;
  }

  /* 🔹 Tombol clear di dalam input */
  #clearSearch {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    font-size: 16px;
    color: #aaa;
    cursor: pointer;
    display: none;
    /* sembunyikan awal */
  }

  #clearSearch:hover {
    color: #dc3545;
    /* warna merah muda saat hover */
  }
</style>
<!-- ================== END ================== -->

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>DATA PROMO</h1>
    </div>

    <div class="section-body">

      <div class="card">


        <div class="card">

          <!--================== FILTER ==================-->
          <div class="card-header  d-flex justify-content-between align-items-center">
            <h4><i class="fas fa-list"></i> DATA PROMO</h4>

            <div class="d-flex justify-content-end align-items-center mb-3" style="gap: 10px;">

              <!-- CREATE DATA -->
              <a href="{{ route('account.Clinik-Scopus-Promo.create') }}"
                class="btn btn-primary btn-block rounded-pill">
                <i class="fa fa-plus-circle"></i> Tambah Data
              </a>
              <!-- END -->

              <div class="dropdown card-header-action">
                <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
                  <i class="fas fa-download"></i> FILTER
                </button>
                <div class="dropdown-menu dropdown-menu-right p-3" style="min-width: 300px;">

                  <!-- FILTER TANGGAL -->
                  <form action="{{ route('account.Clinik-Scopus-Promo.filter') }}" method="GET">
                    <div class="form-group">
                      <label>Tanggal Awal</label>
                      <input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}" class="form-control">
                    </div>

                    <div class="form-group">
                      <label>Tanggal Akhir</label>
                      <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="form-control">
                    </div>

                    @if (request()->has('tanggal_awal') && request()->has('tanggal_akhir'))
                    <div class="btn-group" style="width: 100%;">
                      <button class="btn btn-info mr-1" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                      <a href="{{ route('account.Clinik-Scopus-Promo.index') }}" class="btn btn-danger" style="margin-top: 30px;">
                        <i class="fa fa-trash mt-2"></i> HAPUS
                      </a>
                    </div>
                    @else
                    <button class="btn btn-info mr-1 btn-block" type="submit" style="margin-top: 30px;"><i class="fa fa-filter"></i> FILTER</button>
                    @endif
                  </form>
                  <!-- END -->

                </div>
              </div>

              <!-- SEARCH -->
              <div style="max-width:250px; width:100%; position:relative;">
                <input type="text" id="liveSearch" class="form-control pe-4" placeholder="Pencarian..." autocomplete="off">

                <button
                  type="button"
                  id="clearSearch"
                  style="position:absolute; right:8px; top:50%; transform:translateY(-50%); border:none; background:transparent; display:none; cursor:pointer; font-size:14px;">
                  ✕
                </button>
              </div>
              <!-- END -->

            </div>
          </div>
          <!--================== END FILTER ==================-->

          <!--================== DATA ==================-->
          <div class="card-body" style="font-size: 11px;">
            <div class="table-responsive" id="customerTableWrapper">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th rowspan="2" class="text-center">NO</th>
                    <th rowspan="2" class="text-center">Nama Promo</th>
                    <th rowspan="2" class="text-center">Tanggal Mulai</th>
                    <th rowspan="2" class="text-center">Tanggal Selesai</th>
                    <th rowspan="2" class="text-center">Jenis Promo</th>
                    <th rowspan="2" class="text-center">Status</th>
                    <th colspan="9" class="text-center">Sesi</th>
                    <th rowspan="2" class="text-center">Action</th>
                  </tr>
                  <tr>
                    @for ($i = 1; $i <= 9; $i++)
                      <th class="text-center">{{ $i }}</th>
                      @endfor
                  </tr>
                </thead>

                <tbody id="customerTable">
                  @forelse ($promos as $index => $promo)
                  <tr id="promo-{{ $promo->id }}" npm install>
                    <td class="text-center">
                      {{ $promos->firstItem() + $index }}
                    </td>

                    <td>
                      <strong>{{ $promo->nama_promo }}</strong><br>
                      <small class="text-muted">{{ $promo->kode_diskon ?? '-' }}</small>
                    </td>

                    <td class="text-center">
                      {{ \Carbon\Carbon::parse($promo->tanggal_mulai_promo)->format('d M Y') }}
                    </td>

                    <td class="text-center">
                      {{ \Carbon\Carbon::parse($promo->tanggal_selesai_promo)->format('d M Y') }}
                    </td>

                    <td class="text-center">
                      <span class="badge bg-info" style="padding: 6px 12px; border-radius: 6px;" disabled>
                        {{ ucfirst($promo->tipe_diskon) }}
                      </span>
                    </td>

                    <td class="text-center">
                      @if ($promo->status === 'active')
                      <span class="badge bg-success" style="padding: 6px 12px; border-radius: 6px;" disabled>
                        Active
                      </span>
                      @else
                      <span class="badge bg-danger" style="padding: 6px 12px; border-radius: 6px;" disabled>
                        Non Active
                      </span>
                      @endif
                    </td>

                    {{-- SESI 1–9 --}}
                    @php
                    $sesiAktif = [];

                    if ($promo->tipe_diskon === 'bundling') {
                    // 🔹 AMBIL DARI TABEL clinikscopus_promo_sesi
                    $sesiAktif = $promo->sesi
                    ->pluck('sesi_key')
                    ->map(fn($v) => (int) $v)
                    ->toArray();
                    }
                    @endphp

                    @for ($i = 1; $i <= 9; $i++)
                      <td class="text-center">
                      @if(in_array($i, $sesiAktif))
                      <i class="fas fa-check-circle text-success"></i>
                      @else
                      <i class="fas fa-minus text-muted"></i>
                      @endif
                      </td>
                      @endfor

                      {{-- ACTION --}}
                      <td class=" text-center align-middle">
                        <div class="d-flex justify-content-center align-items-center"
                          style="gap: 6px; flex-wrap: nowrap; min-height: 32px;">

                          <!-- Tombol Edit -->
                          <a href="{{ route('account.Clinik-Scopus-Promo.edit', $promo->id) }}"
                            class="btn btn-warning d-flex align-items-center justify-content-center shadow-sm"
                            style="width: 28px; height: 28px; padding: 0; border-radius: 6px; display: inline-flex;">
                            <i class="fa fa-pencil-alt" style="font-size: 13px; line-height: 1;"></i>
                          </a>

                          <!-- Tombol Delete -->
                          <button onclick="Delete('{{ $promo->id }}')"
                            class="btn btn-danger d-flex align-items-center justify-content-center shadow-sm"
                            style="width: 28px; height: 28px; padding: 0; border-radius: 6px; display: inline-flex;">
                            <i class="fa fa-trash" style="font-size: 13px; line-height: 1;"></i>
                          </button>

                        </div>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="17" class="text-center text-muted">
                      Tidak ada data promo
                    </td>
                  </tr>
                  @endforelse
                </tbody>

              </table>
              <div style="text-align: center;">
                <style>
                  @media (max-width: 767px) {
                    .pagination {
                      margin-left: 480px;
                      /* Adjust the margin value as needed for mobile devices */
                    }
                  }

                  @media (min-width: 768px) and (max-width: 991px) {
                    .pagination {
                      margin-left: 300px;
                      /* Adjust the margin value as needed for iPads */
                    }
                  }
                </style>

                <div id="paginationWrapper" style="text-align: center;">
                  <div id="paginationWrapper">
                    {{ $promos->links() }}
                  </div>

                </div>

              </div>
            </div>
          </div>
          <!--================== END DATA ==================-->

        </div>
      </div>
  </section>
</div>

<!--================== SHOW & HIDE FILTER ==================-->
<script>
  const toggleText = document.getElementById('toggleFilterText');
  const filterCard = document.getElementById('filterCard');

  filterCard.addEventListener('show.bs.collapse', () => {
    toggleText.textContent = 'Tutup Filter';
  });

  filterCard.addEventListener('hide.bs.collapse', () => {
    toggleText.textContent = 'Tampilkan Filter';
  });
</script>
<!--================== END ==================-->

<!-- ================== LIVE SEARCH ================== -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    let timer;
    const liveSearchInput = document.getElementById('liveSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const customerTable = document.getElementById('customerTable');
    const paginationWrapper = document.querySelector('#paginationWrapper');

    // 🧩 Cek apakah ada query ?q= di URL, isi input otomatis
    const urlParams = new URLSearchParams(window.location.search);
    const queryFromURL = urlParams.get('q');
    if (queryFromURL) {
      liveSearchInput.value = queryFromURL;
      clearSearchBtn.style.display = 'block';
    } else {
      clearSearchBtn.style.display = 'none';
    }

    // 🔹 Event keyup untuk Live Search
    liveSearchInput.addEventListener('keyup', function() {
      clearTimeout(timer);
      const query = this.value.trim();

      timer = setTimeout(() => {
        if (query === "") {
          // Jika input kosong → reload semua data
          fetch(`{{ route('account.clinikscopus.index') }}`)
            .then(response => response.text())
            .then(html => {
              const parser = new DOMParser();
              const doc = parser.parseFromString(html, 'text/html');
              const newTableBody = doc.querySelector('#customerTable');
              if (newTableBody) customerTable.innerHTML = newTableBody.innerHTML;
              if (paginationWrapper) {
                const newPagination = doc.querySelector('.pagination');
                paginationWrapper.innerHTML = newPagination ? newPagination.outerHTML : '';
              }
            });
          return;
        }

        // Jika ada teks → jalankan pencarian AJAX
        fetch(`{{ route('account.clinikscopus.search') }}?q=${encodeURIComponent(query)}`)
          .then(response => response.text())
          .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTableBody = doc.querySelector('#customerTable');
            const newPagination = doc.querySelector('.pagination');

            if (newTableBody && newTableBody.children.length > 0) {
              customerTable.innerHTML = newTableBody.innerHTML;
              if (paginationWrapper) {
                paginationWrapper.innerHTML = newPagination ? newPagination.outerHTML : '';
              }
            } else {
              customerTable.innerHTML = "";
              if (paginationWrapper) paginationWrapper.innerHTML = "";
              Swal.fire({
                icon: 'warning',
                title: 'Tidak Ditemukan',
                text: `Tidak ada hasil untuk pencarian: "${query}"`,
                confirmButtonColor: '#3085d6',
              });
            }
          })
          .catch(error => console.error('Error:', error));
      }, 300); // debounce 300ms
    });

    // 🔹 Tampilkan / sembunyikan tombol clear sesuai input
    liveSearchInput.addEventListener('input', function() {
      clearSearchBtn.style.display = this.value.trim() ? 'block' : 'none';
    });

    // 🔹 Klik tombol clear → hapus teks & reload semua data
    clearSearchBtn.addEventListener('click', function() {
      liveSearchInput.value = '';
      this.style.display = 'none';
      fetch(`{{ route('account.clinikscopus.index') }}`)
        .then(response => response.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newTableBody = doc.querySelector('#customerTable');
          const newPagination = doc.querySelector('.pagination');
          if (newTableBody) customerTable.innerHTML = newTableBody.innerHTML;
          if (paginationWrapper) {
            paginationWrapper.innerHTML = newPagination ? newPagination.outerHTML : '';
          }
        });
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
      title: "APAKAH KAMU YAKIN?",
      text: "INGIN MENGHAPUS DATA INI!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: 'YA',
      cancelButtonText: 'TIDAK',
      reverseButtons: true
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