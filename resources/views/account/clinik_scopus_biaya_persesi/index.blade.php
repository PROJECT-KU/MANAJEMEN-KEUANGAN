@extends('layouts.account')
@extends('layouts.loader')
@extends('layouts.headerfitur')

@section('title')
Clinik Scopus Data Biaya Per Sesi | MIS
@stop

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header-modern">
      <div>
        <h1>Data Biaya Per Sesi</h1>
        <p class="text-muted font-weight-bold mb-0">Kelola rincian tarif dan administrasi layanan tanpa hambatan.</p>
      </div>

      <div class="search-action-wrapper">
        <div class="search-container">
          <i class="fas fa-search"></i>
          <input type="text" id="liveSearch" class="form-control-modern w-100" placeholder="Cari data..." autocomplete="off">
          <span id="clearSearch" style="display:none;">✕</span>
        </div>

        <a href="{{ route('account.Clinik-Scopus-Biaya-Persesi.create') }}" class="btn-modern btn-gradient text-white btn-create-animate">
          <i class="fas fa-plus-circle"></i> Tambah Data
        </a>
      </div>
    </div>

    <div class="section-body">
      <div class="card-neo" id="search-results">
        @if($biayaPersesi->count() > 0)
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-modern mb-0">
              <thead>
                <tr>
                  <th class="text-center" width="80">No.</th>
                  <th>Biaya Persesi</th>
                  <th class="text-center">PPN</th>
                  <th class="text-center">Status</th>
                  <th class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody id="customerTable">
                @php $no = 1; @endphp
                @foreach ($biayaPersesi as $item)
                <tr>
                  <td class="text-center text-muted">{{ $no++ }}</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="icon-shape" style="width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: #eef2ff; color: var(--accent);">
                        <i class="fas fa-money-bill-wave"></i>
                      </div>
                      <div class="ml-3">
                        <span style="font-size: 15px; font-weight: 700;">Rp {{ number_format($item->biaya_persesi, 0, ',', '.') }}</span>
                      </div>
                    </div>
                  </td>
                  <td class="text-center">
                    <span class="badge badge-light px-3 py-2" style="border-radius: 8px; color: #6366f1; background: #f5f3ff; font-weight: 700;">
                      {{ $item->ppn ?? '0' }}%
                    </span>
                  </td>
                  <td class="text-center">
                    @if ($item->status == "active")
                    <span class="badge-modern bg-success text-white">ACTIVE</span>
                    @else
                    <span class="badge-modern bg-danger text-white">NON ACTIVE</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <div class="d-flex justify-content-center" style="gap: 8px;">
                      <a href="{{ route('account.Clinik-Scopus-Biaya-Persesi.edit', $item->id) }}"
                        class="btn-modern btn-outline-modern" style="padding: 8px 12px; font-size: 12px;">
                        <i class="fas fa-edit text-warning"></i>
                      </a>
                      <button onclick="Delete('{{ $item->id }}')"
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
          <div id="customerTable"> {{-- Tetap beri ID ini agar AJAX bisa mengenali area update --}}
            <div class="text-center py-5">
              <div style="background: var(--card-bg); padding: 60px 20px; border-radius: var(--radius-xl); border: 2px dashed #e2e8f0; margin: 20px;">
                <div class="mb-4">
                  <i class="fas fa-search-minus" style="font-size: 64px; color: #cbd5e1;"></i>
                </div>
                <h4 style="font-weight: 800; color: #475569;">Data Tidak Ditemukan</h4>
                <p style="color: #94a3b8; font-weight: 600;">Maaf, sepertinya tidak ada data biaya persesi yang cocok dengan pencarian Anda.</p>
                <a href="{{ route('account.Clinik-Scopus-Biaya-Persesi.index') }}" class="btn btn-primary mt-3" style="border-radius: 50px; padding: 10px 25px; font-weight: 700; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);">
                  <i class="fas fa-sync-alt mr-2"></i> Muat Ulang Halaman
                </a>
              </div>
            </div>
          </div>
          @endif
        </div>
      </div>

      <div class="mt-4 d-flex justify-content-center" id="paginationWrapper">
        {{ $biayaPersesi->links() }}
      </div>
    </div>
  </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  $(document).ready(function() {
    $('#liveSearch').on('keyup', function() {
      let query = $(this).val();

      if (query.length > 0) {
        $('#clearSearch').show();
      } else {
        $('#clearSearch').hide();
      }

      $.ajax({
        url: "{{ route('account.Clinik-Scopus-Biaya-Persesi.search') }}",
        type: "GET",
        data: {
          'q': query
        },
        success: function(data) {
          // 🔹 AMBIL SELURUH ISI card-neo DARI HASIL RESPONSE
          let html = $(data).find('#search-results').html();
          let pagination = $(data).find('#paginationWrapper').html();

          // 🔹 UPDATE SELURUH KONTAINER (Tabel Akan Hilang/Muncul Secara Utuh)
          $('#search-results').html(html);
          $('#paginationWrapper').html(pagination);
        }
      });
    });

    $('#clearSearch').on('click', function() {
      $('#liveSearch').val('').trigger('keyup');
    });
  });

  // 🗑️ FUNGSI DELETE DENGAN SWEETALERT2
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
          url: "/account/Clinik-Scopus-Biaya-Persesi/data/" + id,
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