@extends('layouts.account')
@extends('layouts.loader')
@extends('layouts.headerfitur')

@section('title')
Clinik Scopus Data Biaya Per Sesi | MIS
@stop

@section('content')
<div class="main-content" style="padding-top: 110px; background-color: #f4f7ff; min-height: 100vh;">
  <section class="section">

    {{-- ========================================================== kepala --}}
    <header class="mis-kepala">
      <span class="mis-medali mis-hijau" aria-hidden="true"><i class="fas fa-money-bill-wave"></i></span>

      <div class="mis-kepala-teks">
        <h1 class="mis-judul">Biaya per sesi</h1>
        <p class="mis-sub">Tarif yang dipakai saat pelanggan memesan Clinik Scopus.</p>
      </div>

      <div class="mis-kepala-aksi">
        <span class="mis-cari">
          <i class="fas fa-search"></i>
          <input type="text" id="liveSearch" class="form-control-modern" placeholder="Cari tarif…" autocomplete="off">
          <button type="button" id="clearSearch" class="mis-cari-hapus" style="display:none;" title="Kosongkan pencarian">
            <i class="fas fa-times"></i>
          </button>
        </span>
        <a href="{{ route('account.Clinik-Scopus-Biaya-Persesi.create') }}" class="mis-tombol mis-tombol-ungu">
          <i class="fas fa-plus"></i> Tambah tarif
        </a>
      </div>
    </header>

    <div class="section-body">
      <div class="mis-tabel-bungkus" id="search-results">
        @if($biayaPersesi->count() > 0)
          <div class="table-responsive">
            {{-- .mis-tabel-kartu: di ponsel tiap baris berubah jadi kartu,
                 dengan data-judul sebagai label tiap nilainya. --}}
            <table class="mis-tabel mis-tabel-kartu">
              <thead>
                <tr>
                  <th style="width: 70px;">No.</th>
                  <th>Tarif</th>
                  <th>PPN</th>
                  <th>Status</th>
                  <th style="width: 120px;">Aksi</th>
                </tr>
              </thead>
              <tbody id="customerTable">
                @foreach ($biayaPersesi as $nomor => $item)
                  <tr>
                    <td class="mis-td-samar" style="color: #94a3b8;">
                      {{ $biayaPersesi->firstItem() + $nomor }}
                    </td>

                    <td class="mis-td-utama">
                      <span class="mis-sel-utama">
                        <span class="mis-medali kecil mis-hijau" aria-hidden="true">
                          <i class="fas fa-money-bill-wave"></i>
                        </span>
                        <span class="mis-sel-teks">
                          <span class="mis-sel-judul">Rp {{ number_format($item->biaya_persesi, 0, ',', '.') }}</span>
                          <span class="mis-sel-catatan">per satu sesi</span>
                        </span>
                      </span>
                    </td>

                    <td data-judul="PPN">
                      <span class="mis-pil mis-pil-ungu">{{ $item->ppn ?? '0' }}%</span>
                    </td>

                    <td data-judul="Status">
                      @if ($item->status == 'active')
                        <span class="mis-pil mis-pil-hijau"><i class="fas fa-check-circle"></i> Dipakai</span>
                      @else
                        <span class="mis-pil mis-pil-abu"><i class="fas fa-pause-circle"></i> Tidak dipakai</span>
                      @endif
                    </td>

                    <td data-judul="Aksi">
                      <span class="mis-aksi-baris">
                        <a href="{{ route('account.Clinik-Scopus-Biaya-Persesi.edit', $item->id) }}"
                          class="mis-tombol-garis" title="Ubah tarif ini">
                          <i class="fas fa-pen"></i>
                        </a>
                        <button type="button" onclick="Delete('{{ $item->id }}')"
                          class="mis-tombol-bahaya" title="Hapus tarif ini">
                          <i class="fas fa-trash"></i>
                        </button>
                      </span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          {{-- id customerTable dipertahankan: AJAX pencarian memakainya --}}
          <div id="customerTable">
            <div class="mis-kosong">
              <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-money-bill-wave"></i></span>
              <h2 class="mis-kosong-judul">Belum ada tarif yang cocok</h2>
              <p class="mis-kosong-teks">
                Coba hapus kata pencarian, atau tambahkan tarif baru supaya pelanggan
                bisa memesan sesi.
              </p>
              <a href="{{ route('account.Clinik-Scopus-Biaya-Persesi.create') }}" class="mis-tombol mis-tombol-ungu">
                <i class="fas fa-plus"></i> Tambah tarif
              </a>
            </div>
          </div>
        @endif
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