@extends('layouts.account')
@extends('layouts.loader')
@extends('layouts.headerfitur')

@section('title')
Clinik Scopus Data Promo | MIS
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header-modern">
      <div>
        <h1>Data Promo</h1>
        <p class="text-muted font-weight-bold mb-0">Kelola kampanye diskon dan jadwal sesi klinik.</p>
      </div>

      <div class="search-action-wrapper">
        <div class="search-container">
          <i class="fas fa-search"></i>
          <input type="text" id="liveSearch" class="form-control-modern w-100" placeholder="Cari data..." autocomplete="off">
          <span id="clearSearch" style="display:none;">✕</span>
        </div>

        @if (Auth::user()->level === 'manager')
        <a href="{{ route('account.Clinik-Scopus-Promo.create') }}" class="btn-modern btn-gradient text-white btn-create-animate">
          <i class="fas fa-plus-circle"></i> Tambah Data
        </a>
        @endif
      </div>
    </div>

    <div class="card-neo" id="search-results">
      @if($promos->count() > 0)
      <div class="table-responsive">
        <table class="table-modern">
          <thead>
            <tr>
              <th rowspan="2" class="text-center col-no">NO</th>
              <th rowspan="2" class="col-detail">DETAIL PROMO</th>
              <th rowspan="2" class="text-center col-periode">PERIODE</th>
              <th rowspan="2" class="text-center col-jenis">JENIS</th>
              <th rowspan="2" class="text-center col-status">STATUS</th>
              <th colspan="9" class="text-center" style="background: #f1f5f9; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">SESI AKTIF</th>
              <th rowspan="2" class="text-center col-aksi">AKSI</th>
            </tr>
            <tr>
              @for ($i = 1; $i <= 9; $i++)
                <th class="text-center col-sesi" style="background: #f8fafc; border-bottom: 2px solid #edf2f7;">{{ $i }}</th>
                @endfor
            </tr>
          </thead>
          <tbody id="customerTable">
            @foreach ($promos as $index => $promo)
            <tr>
              <td class="text-center text-muted font-weight-bold">{{ $promos->firstItem() + $index }}</td>
              <td>
                <div style="display: grid; gap: 1px;">
                  <span style="font-weight: 800; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $promo->nama_promo }}</span>
                  <span class="text-primary font-weight-bold" style="font-size: 10px;">{{ $promo->kode_diskon ?? '-' }}</span>
                </div>
              </td>
              <td class="text-center">
                <div style="font-size: 11px; line-height: 1.2;">
                  <span style="font-weight: 700;">{{ \Carbon\Carbon::parse($promo->tanggal_mulai_promo)->format('d/m/y') }}</span>
                  <br><span class="text-muted" style="font-size: 9px;">S/D</span><br>
                  <span style="font-weight: 700;">{{ \Carbon\Carbon::parse($promo->tanggal_selesai_promo)->format('d/m/y') }}</span>
                </div>
              </td>
              <td class="text-center">
                <span class="badge-modern bg-status" style="font-size: 9px; border-radius: 6px;">{{ strtoupper($promo->tipe_diskon) }}</span>
              </td>
              <td class="text-center">
                @if ($promo->status === 'active')
                <span class="badge-modern bg-status-active">ACTIVE</span>
                @else
                <span class="badge-modern bg-status-nonactive">NON ACTIVE</span>
                @endif
              </td>

              @php $sesiAktif = $promo->sesi->pluck('sesi_key')->map(fn($v) => (int) $v)->toArray(); @endphp
              @for ($i = 1; $i <= 9; $i++)
                <td class="text-center" style="border-left: 1px solid #f8fafc;">
                @if(in_array($i, $sesiAktif))
                <i class="fas fa-check-circle text-success" style="font-size: 15px;"></i>
                @else
                <i class="fas fa-minus text-muted" style="opacity: 0.2;"></i>
                @endif
                </td>
                @endfor

                <td class="text-center">
                  <div class="d-flex justify-content-center" style="gap: 8px;">
                    <a href="{{ route('account.Clinik-Scopus-Promo.edit', $promo->id) }}"
                      class="btn-modern btn-outline-modern" style="padding: 8px 12px; font-size: 12px;">
                      <i class="fas fa-edit text-warning"></i>
                    </a>
                    <button onclick="Delete('{{ $promo->id  }}')"
                      class="btn-modern btn-outline-modern" style="padding: 8px 12px; font-size: 12px;">
                      <i class="fas fa-trash text-danger"></i>
                    </button>
                  </div>
                </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @else
      <div class="text-center py-5">
        <div style="background: var(--card-bg); padding: 60px 20px; border-radius: var(--radius-xl); border: 2px dashed #e2e8f0; margin: 20px;">
          <div class="mb-4">
            <i class="fas fa-ticket-alt" style="font-size: 64px; color: #cbd5e1; transform: rotate(-15deg);"></i>
          </div>
          <h4 style="font-weight: 800; color: #475569;">Data Tidak Ditemukan</h4>
          <p style="color: #94a3b8; font-weight: 600;">Maaf, sepertinya tidak ada data promo yang cocok dengan pencarian Anda.</p>
          <a href="{{ route('account.Clinik-Scopus-Promo.index') }}" class="btn btn-primary mt-3" style="border-radius: 50px; padding: 10px 25px; font-weight: 700;">
            <i class="fas fa-sync-alt mr-2"></i> Segarkan Halaman
          </a>
        </div>
      </div>
      @endif
    </div>

    <div class="mt-4 d-flex justify-content-center">
      {{ $promos->links() }}
    </div>
  </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const liveSearchInput = document.getElementById('liveSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const searchResults = document.getElementById('search-results');
    const paginationWrapper = document.getElementById('paginationWrapper');
    let searchTimeout = null;

    if (liveSearchInput) {
      liveSearchInput.addEventListener('keyup', function() {
        const query = this.value.trim();

        if (clearSearchBtn) clearSearchBtn.style.display = query ? 'block' : 'none';

        clearTimeout(searchTimeout);

        searchTimeout = setTimeout(() => {
          // searchResults.style.opacity = '0.5';

          const url = query ?
            `{{ route('account.Clinik-Scopus-Promo.search') }}?q=${encodeURIComponent(query)}` :
            `{{ route('account.Clinik-Scopus-Promo.index') }}`;

          fetch(url, {
              headers: {
                'X-Requested-With': 'XMLHttpRequest'
              }
            })
            .then(response => response.text())
            .then(html => {
              // searchResults.style.opacity = '1';
              const parser = new DOMParser();
              const doc = parser.parseFromString(html, 'text/html');

              // AMBIL SELURUH ISI card-neo DARI RESPONSE
              const newContent = doc.querySelector('#search-results');
              if (newContent && searchResults) {
                // UPDATE SELURUH KONTAINER (Tabel Hilang/Muncul Sesuai Data)
                searchResults.innerHTML = newContent.innerHTML;
              }

              // Update Pagination
              const newPagination = doc.querySelector('.pagination') || doc.querySelector('#paginationWrapper ul');
              if (paginationWrapper) {
                paginationWrapper.innerHTML = newPagination ? newPagination.outerHTML : '';
              }
            })
            .catch(error => {
              console.error('Error:', error);
              // searchResults.style.opacity = '1';
            });
        }, 500);
      });
    }

    if (clearSearchBtn) {
      clearSearchBtn.addEventListener('click', () => {
        liveSearchInput.value = '';
        clearSearchBtn.style.display = 'none';
        liveSearchInput.dispatchEvent(new Event('keyup'));
      });
    }
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
      borderRadius: '15px'
    }).then((result) => {
      if (result.isConfirmed) {
        let token = $("meta[name='csrf-token']").attr("content");

        $.ajax({
          url: "/account/Clinik-Scopus-Promo/data/" + id,
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
@stop