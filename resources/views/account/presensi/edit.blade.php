@extends('layouts.account')

@section('title')
Update Presensi Karyawan | MIS
@stop

<!--================== animasi image ==================-->
<!-- Kode CSS animasi image di sini -->
<!--================== end ==================-->

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>PRESENSI KEPULANGAN</h1>
    </div>

    <!--================== menampilkan card berdasarkan level ==================-->
    @if (Auth::user()->level == 'karyawan' || Auth::user()->level == 'staff' || Auth::user()->level == 'trainer')
    @else
    <div class="section-body">
      <div class="card">
        <div class="card-body">
          @endif
          <!--================== END ==================-->

          <form id="updateForm" action="{{ route('account.presensi.update', $presensi->id) }}" method="post" enctype="multipart/form-data">
            @csrf

            <!--================== jika selain manager atau ceo sweet alert tampil ==================-->
            @if (Auth::user()->level == 'karyawan' || Auth::user()->level == 'staff' || Auth::user()->level == 'trainer')
            @php
            $todayPresensi = \App\Presensi::where('user_id', Auth::user()->id)
            ->whereDate('created_at', now()->toDateString())
            ->first();
            @endphp
            @if ($todayPresensi)
            <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
            <input type="hidden" name="status_pulang" value="pulang">

            <div class="row" hidden>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status Presensi Kehadiran</label>
                  <select class="form-control" name="status" id="status" style="height:auto">
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="alpha" {{ $presensi->status == 'alpha' ? 'selected' : '' }}>ALPHA</option>
                    <option value="hadir" {{ $presensi->status == 'hadir' ? 'selected' : '' }}>HADIR</option>
                    <option value="camp jogja" {{ $presensi->status == 'camp jogja' ? 'selected' : '' }}>CAMP JOGJA</option>
                    <option value="perjalanan luar kota jawa" {{ $presensi->status == 'perjalanan luar kota jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA DALAM JAWA</option>
                    <option value="perjalanan luar kota luar jawa" {{ $presensi->status == 'perjalanan luar kota luar jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA LUAR JAWA</option>
                    <option value="camp luar kota" {{ $presensi->status == 'camp luar kota' ? 'selected' : '' }}>CAMP LUAR KOTA</option>
                    <option value="remote" {{ $presensi->status == 'remote' ? 'selected' : '' }}>REMOTE</option>
                    <option value="izin" {{ $presensi->status == 'izin' ? 'selected' : '' }}>IZIN</option>
                    <option value="lembur" {{ $presensi->status == 'lembur' ? 'selected' : '' }}>LEMBUR</option>
                    <option value="cuti" {{ $presensi->status == 'cuti' ? 'selected' : '' }}>CUTI</option>
                    <option value="terlambat" {{ $presensi->status == 'terlambat' ? 'selected' : '' }} hidden>TERLAMBAT</option>
                    <option value="pulang" {{ $presensi->status == 'pulang' ? 'selected' : '' }}>PULANG</option>
                  </select>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Status Presensi Pulang</label>
                  <select class="form-control" name="status_pulang" id="status_pulang" style="height:auto">
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="pulang" {{ $presensi->status_pulang == 'pulang' ? 'selected' : '' }}>PULANG</option>
                  </select>
                </div>
              </div>

            </div>
            <!--================== END ==================-->

            <!--================== jika manager atau ceo maka tampil standart tanpa sweet alert ==================-->

            @endif
            @else
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status Presensi Kehadiran</label>
                  <select class="form-control" name="status" id="status" style="height:auto">
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="alpha" {{ $presensi->status == 'alpha' ? 'selected' : '' }}>ALPHA</option>
                    <option value="hadir" {{ $presensi->status == 'hadir' ? 'selected' : '' }}>HADIR</option>
                    <option value="camp jogja" {{ $presensi->status == 'camp jogja' ? 'selected' : '' }}>CAMP JOGJA</option>
                    <option value="perjalanan luar kota jawa" {{ $presensi->status == 'perjalanan luar kota jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA DALAM JAWA</option>
                    <option value="perjalanan luar kota luar jawa" {{ $presensi->status == 'perjalanan luar kota luar jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA LUAR JAWA</option>
                    <option value="camp luar kota" {{ $presensi->status == 'camp luar kota' ? 'selected' : '' }}>CAMP LUAR KOTA</option>
                    <option value="remote" {{ $presensi->status == 'remote' ? 'selected' : '' }}>REMOTE</option>
                    <option value="izin" {{ $presensi->status == 'izin' ? 'selected' : '' }}>IZIN</option>
                    <option value="cuti" {{ $presensi->status == 'cuti' ? 'selected' : '' }}>CUTI</option>
                    <option value="terlambat" {{ $presensi->status == 'terlambat' ? 'selected' : '' }}>TERLAMBAT</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status Presensi Pulang</label>
                  <select class="form-control" name="status_pulang" id="status_pulang" style="height:auto">
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="pulang" {{ $presensi->status_pulang == 'pulang' ? 'selected' : '' }}>PULANG</option>
                  </select>
                </div>
              </div>
            </div>
            @endif
            <!--================== END ==================-->

            <!--================== menampilkan button berdasarkan level ==================-->
            @if (Auth::user()->level == 'karyawan' || Auth::user()->level == 'staff' || Auth::user()->level == 'trainer')
            @else
            <div class="d-flex mt-3" style="gap: 10px;">
              <button class="btn btn-primary btn-submit rounded-pill"
                type="submit"
                style="flex: 0 0 80%; height:35px; font-size: 15px;">
                <i class="fa fa-paper-plane"></i> SIMPAN
              </button>

              <a href="{{ route('account.presensi.index') }}"
                class="btn btn-warning rounded-pill d-flex align-items-center justify-content-center"
                style="flex: 0 0 20%; height:35px; font-size: 15px;">
                <i class="fa fa-undo"></i> KEMBALI
              </a>
            </div>
            @endif
            <!--================== END ==================-->

          </form>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Include SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(Auth::user()->level != 'manager' && Auth::user()->level != 'ceo')
<script>
  window.onload = function() {
    Swal.fire({
      title: 'Konfirmasi Presensi Pulang',
      text: 'Apakah Anda yakin ingin melakukan presensi pulang?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, Pulang!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      // Jika tombol "OK" ditekan
      if (result.isConfirmed) {
        // Submit form secara otomatis
        document.getElementById('updateForm').submit();
      } else {
        // Redirect to the specified route when cancel button is clicked
        window.location.href = "{{ route('account.dashboard.index') }}";
      }
    });
  };
</script>
@endif

@stop