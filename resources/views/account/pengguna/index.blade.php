@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Data Karyawan | MIS
@stop


@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>DATA KARYAWAN</h1>
    </div>

    <div class="section-body">

      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="mb-0"><i class="fas fa-list"></i> DATA KARYAWAN</h4>

          <!--================== FILTER ==================-->
          <div class="d-flex justify-content-end align-items-center mb-3" style="gap: 10px;">
            <a href="{{ route('account.pengguna.crphpeate') }}" class="btn btn-primary rounded-pill d-flex align-items-center" style="white-space: nowrap;">
              <i class="fa fa-plus-circle mr-1"></i> TAMBAH DATA KARYAWAN
            </a>

            <div style="max-width: 250px; width: 100%;">
              <input type="text" id="liveSearch" class="form-control" placeholder="Pencarian..." autocomplete="off">
            </div>
          </div>
          <!--================== END FILTER ==================-->

        </div>


        <div class="card-body" style="font-size: 11px;">
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th scope="col" style="text-align: center;" rowspan="2">NO.</th>
                  <th scope="col" rowspan="2" style="text-align: center;">Email</th>
                  <th scope="col" rowspan="2" style="text-align: center;">Username</th>
                  <th scope="col" rowspan="2" style="text-align: center;">Verifikasi Email</th>
                  <th scope="col" rowspan="2" style="text-align: center;">Jenis</th>
                  <th scope="col" rowspan="2" style="text-align: center;">Role</th>
                  <th scope="col" rowspan="2" style="text-align: center;">Status</th>
                  <th scope="col" style="width: 10%;text-align: center">Action</th>
                </tr>
              </thead>
              <tbody id="userTable">
                @php
                $no = 1;
                @endphp
                @forelse ($users as $item)
                <tr>
                  <th scope="row" style="text-align: center">{{ $no }}</th>
                  <td style="display: flex; align-items: center; gap: 10px;">
                    <img
                      src="{{ $item->gambar ? asset('assets/img/profil/' . $item->gambar) : asset('assets/img/profil/no-image.jpg') }}"
                      alt="Foto Profil"
                      style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                    <span>{{ $item->email }}</span>
                  </td>

                  <td style="text-align: center;">{{ $item->username }}</td>

                  <td style="text-align: center;">
                    @if ($item->email_verified_at)
                    <span class="badge bg-success" style="padding: 6px 12px; border-radius: 6px;" disabled>
                      Sudah Diverifikasi
                    </span>
                    @else
                    <span class="badge bg-danger" style="padding: 6px 12px; border-radius: 6px;" disabled>
                      Belum Diverifikasi
                    </span>
                    @endif
                  </td>

                  <td style="text-align: center;">{{ $item->jenis }}</td>

                  <td style="text-align: center;">{{ $item->level }}</td>

                  <td style="text-align: center;">
                    @if ($item->status == 'active')
                    <span class="badge bg-success" style="padding: 6px 12px; border-radius: 6px;" disabled>
                      Active
                    </span>
                    @else
                    <span class="badge bg-danger" style="padding: 6px 12px; border-radius: 6px;" disabled>
                      Non Active
                    </span>
                    @endif
                  </td>

                  <td class="text-center align-middle">
                    <div class="d-flex justify-content-center align-items-center"
                      style="gap: 6px; flex-wrap: nowrap; min-height: 32px;">

                      <!-- Tombol Edit -->
                      <a href="{{ route('account.pengguna.edit', $item->id) }}" class="btn btn-warning d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 28px; height: 28px; padding: 0; border-radius: 6px; display: inline-flex;">
                        <i class="fa fa-pencil-alt" style="font-size: 13px; line-height: 1;"></i>
                      </a>

                      <!-- Tombol Delete -->
                      <button onclick="Delete('{{ $item->id }}')" class="btn btn-danger d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 28px; height: 28px; padding: 0; border-radius: 6px; display: inline-flex;">
                        <i class="fa fa-trash" style="font-size: 13px; line-height: 1;"></i>
                      </button>

                    </div>
                  </td>
                </tr>
                @php
                $no++;
                @endphp
                @empty
                <tr>
                  <td colspan="8" class="text-center">Tidak ada data</td>
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
              {{$users->links("vendor.pagination.bootstrap-4")}}
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>
</div>

<!--================== LIVE SEARCH ==================-->
<script>
  let timer;

  document.getElementById('liveSearch').addEventListener('keyup', function() {
    clearTimeout(timer);
    const query = this.value;

    timer = setTimeout(() => {
      fetch(`{{ route('account.pengguna.search') }}?q=${encodeURIComponent(query)}`)
        .then(response => response.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newTableBody = doc.querySelector('#userTable');
          if (newTableBody) {
            document.getElementById('userTable').innerHTML = newTableBody.innerHTML;
          }
        });
    }, 300); // debounce 300ms
  });
</script>
<!--================== END ==================-->

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
    var token = $("meta[name='csrf-token']").attr("content");

    swal({
      title: "APAKAH KAMU YAKIN ?",
      text: "INGIN MENGHAPUS DATA INI!",
      icon: "warning",
      buttons: ['TIDAK', 'YA'],
      dangerMode: true,
    }).then(function(isConfirm) {
      if (isConfirm) {
        // Ajax delete
        $.ajax({
          url: "{{ route('account.pengguna.destroy', '') }}/" + id,
          data: {
            "_token": token,
            "_method": "DELETE"
          },
          type: 'POST',
          success: function(response) {
            swal({
              title: 'BERHASIL!',
              text: 'DATA BERHASIL DIHAPUS!',
              icon: 'success',
              timer: 1000,
              showConfirmButton: false,
              showCancelButton: false,
              buttons: false,
            }).then(function() {
              location.reload();
            });
          }
        });
      } else {
        return true;
      }
    });
  }
</script>
<!--================== END ==================-->

<!--================== SWEET ALERT JIKA FIELDS KOSONG ==================-->

<script>
  document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("searchButton").addEventListener("click", function() {
      var searchInputValue = document.querySelector("input[name='q']").value.trim();

      if (searchInputValue === "") {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan',
          text: 'Harap isi field pencarian terlebih dahulu!',
          confirmButtonColor: '#3085d6',
          confirmButtonText: 'OK'
        });
      } else {
        // If not empty, submit the form
        document.getElementById("searchForm").submit();
      }
    });
  });
</script>
<!--================== END ==================-->

@stop