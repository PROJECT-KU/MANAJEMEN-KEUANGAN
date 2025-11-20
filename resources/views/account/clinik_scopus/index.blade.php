@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Data Customer | MIS
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
      <h1>DATA CUSTOMER</h1>
    </div>

    <div class="section-body">

      <div class="card">

        <!-- ================== FILTER ================== -->
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">

          <div class="search-wrapper flex-grow-1 me-2">
            <!-- 🔍 Search Input -->
            <input
              type="text"
              id="liveSearch"
              class="form-control"
              placeholder="Pencarian..."
              autocomplete="off">

            <!-- ❌ Tombol Clear di dalam input -->
            <button
              type="button"
              id="clearSearch"
              title="Hapus pencarian">
              <i class="fas fa-times-circle"></i>
            </button>
          </div>

          <!-- 🔘 Tombol Filter -->
          <button
            class="btn btn-outline-primary rounded-pill flex-fill flex-sm-grow-0"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#filterCard"
            aria-expanded="false"
            aria-controls="filterCard"
            id="toggleFilterBtn">
            <i class="bi bi-funnel-fill"></i>
            <span class="ms-1" id="toggleFilterText">Tampilkan Filter</span>

            <!-- 🔽 Filter Collapse -->
            <div class="collapse w-100 mt-2" id="filterCard">
              @include('account.clinik_scopus.partials.filter')
            </div>
        </div>
        <!-- ================== END FILTER ================== -->

        <!-- ================== Tombol Create ================== -->
        <a href="{{ route('account.clinikscopus.create') }}" class="btn btn-primary btn-block rounded-pill">
          <i class="fa fa-plus-circle"></i> TAMBAH DATA
        </a>

        <!--================== DATA ==================-->
        <div class="card-body" style="font-size: 11px;">
          <div class="table-responsive" id="customerTableWrapper">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th scope="col" style="text-align: center;" rowspan="2">NO.</th>
                  <th scope="col" rowspan="2" style="text-align: center;">Nama Trainer</th>
                  <th scope="col" rowspan="2" style="text-align: center;">Sesi Trainer</th>
                  <th scope="col" rowspan="2" style="text-align: center;">Spesialis</th>
                  <th scope="col" rowspan="2" style="text-align: center;">Tanggal</th>
                  <th scope="col" rowspan="2" style="text-align: center;">Status</th>
                  <th scope="col" style="width: 10%;text-align: center">Action</th>
                </tr>
              </thead>
              <tbody id="customerTable">
                @php
                $no = 1;
                @endphp
                @foreach ($datatrainer as $item)
                <tr>
                  <th scope="row" style="text-align: center">{{ $no }}</th>
                  <td style="display: flex; align-items: center; gap: 10px;">{{ $item->nama }} </td>
                  <td style="text-align: center;">{{ $item->sesi }}</td>
                  <td style="text-align: center;">{{ $item->spesialis }}</td>
                  <td style="text-align: center;">{{ $item->tanggal }}</td>
                  <td style="text-align: center;">
                    @if ($item->status)
                    <span class="badge bg-success" style="padding: 6px 12px; border-radius: 6px;">
                      Aktif
                    </span>
                    @else
                    <span class="badge bg-danger" style="padding: 6px 12px; border-radius: 6px;">
                      Non aktif
                    </span>
                    @endif
                  </td>

                  <td class=" text-center align-middle">
                    <div class="d-flex justify-content-center align-items-center"
                      style="gap: 6px; flex-wrap: nowrap; min-height: 32px;">

                      <!-- Tombol Edit -->
                      <a href=""
                        class="btn btn-warning d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 28px; height: 28px; padding: 0; border-radius: 6px; display: inline-flex;">
                        <i class="fa fa-pencil-alt" style="font-size: 13px; line-height: 1;"></i>
                      </a>

                      <!-- Tombol Delete -->
                      <button onclick="Delete('{{ $item->id }}')"
                        class="btn btn-danger d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 28px; height: 28px; padding: 0; border-radius: 6px; display: inline-flex;">
                        <i class="fa fa-trash" style="font-size: 13px; line-height: 1;"></i>
                      </button>

                    </div>
                  </td>
                </tr>
                @php
                $no++;
                @endphp
                @endforeach

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
          fetch(`{{ route('account.customer.index') }}`)
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
        fetch(`{{ route('account.customer.search') }}?q=${encodeURIComponent(query)}`)
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
      fetch(`{{ route('account.customer.index') }}`)
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
          url: "{{ route('account.customer.destroy', '') }}/" + id,
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