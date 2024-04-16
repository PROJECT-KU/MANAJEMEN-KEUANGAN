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
                  <select class="form-control" name="status" id="status">
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
              @if ($presensi->status_pulang == null)
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status Presensi Pulang</label>
                  <select class="form-control" name="status_pulang" id="status_pulang">
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="pulang" {{ $presensi->status_pulang == 'pulang' ? 'selected' : '' }}>PULANG</option>
                  </select>
                </div>
              </div>
              @else
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status Presensi Pulang</label>
                  <select class="form-control" name="status_pulang" id="status_pulang">
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="pulang" {{ $presensi->status_pulang == 'pulang' ? 'selected' : '' }}>PULANG</option>
                  </select>
                </div>
              </div>
              @endif
            </div>
            <!--================== END ==================-->

            <!--================== jika manager atau ceo maka tampil standart tanpa sweet alert ==================-->
            @else
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Status Presensi Kehadiran</label>
                  <select class="form-control" name="status" id="status">
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="alpha" {{ $presensi->status == 'alpha' ? 'selected' : '' }}>ALPHA</option>
                    <option value="hadir" {{ $presensi->status == 'hadir' ? 'selected' : '' }}>HADIR</option>
                    <option value="camp jogja" {{ $presensi->status == 'camp jogja' ? 'selected' : '' }}>CAMP JOGJA</option>
                    <option value="perjalanan luar kota jawa" {{ $presensi->status == 'perjalanan luar kota jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA DALAM JAWA</option>
                    <option value="perjalanan luar kota luar jawa" {{ $presensi->status == 'perjalanan luar kota luar jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA LUAR JAWA</option>
                    <option value="camp luar kota" {{ $presensi->status == 'camp luar kota' ? 'selected' : '' }}>CAMP LUAR KOTA</option>
                    <option value="remote" {{ $presensi->status == 'remote' ? 'selected' : '' }}>REMOTE</option>
                    <option value="izin" {{ $presensi->status == 'izin' ? 'selected' : '' }}>IZIN</option>
                  </select>
                </div>
              </div>
              <!--<div class="col-md-6">
                <div class="form-group">
                  <label>NAMA KARYAWAN</label>
                  <div class="input-group">
                    <input name="user_id" id="full_name" placeholder="Masukkan catatan" class="form-control" value="{{ $users->first()->full_name }}" readonly>
                  </div>
                </div>
              </div>-->
            </div>
            @endif
            @else
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status Presensi Kehadiran</label>
                  <select class="form-control" name="status" id="status">
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="alpha" {{ $presensi->status == 'alpha' ? 'selected' : '' }}>ALPHA</option>
                    <option value="hadir" {{ $presensi->status == 'hadir' ? 'selected' : '' }}>HADIR</option>
                    <option value="camp jogja" {{ $presensi->status == 'camp jogja' ? 'selected' : '' }}>CAMP JOGJA</option>
                    <option value="perjalanan luar kota jawa" {{ $presensi->status == 'perjalanan luar kota jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA DALAM JAWA</option>
                    <option value="perjalanan luar kota luar jawa" {{ $presensi->status == 'perjalanan luar kota luar jawa' ? 'selected' : '' }}>PERJALANAN LUAR KOTA LUAR JAWA</option>
                    <option value="camp luar kota" {{ $presensi->status == 'camp luar kota' ? 'selected' : '' }}>CAMP LUAR KOTA</option>
                    <option value="remote" {{ $presensi->status == 'remote' ? 'selected' : '' }}>REMOTE</option>
                    <option value="izin" {{ $presensi->status == 'izin' ? 'selected' : '' }}>IZIN</option>
                    <!-- <option value="cuti" {{ $presensi->status == 'cuti' ? 'selected' : '' }}>CUTI</option>
            <option value="terlambat" {{ $presensi->status == 'terlambat' ? 'selected' : '' }}>TERLAMBAT</option> -->
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status Presensi Pulang</label>
                  <select class="form-control" name="status_pulang" id="status_pulang">
                    <option value="" disabled selected>-- PILIH STATUS PRESENSI --</option>
                    <option value="pulang" {{ $presensi->status_pulang == 'pulang' ? 'selected' : '' }}>PULANG</option>
                  </select>
                </div>
              </div>
            </div>
            @endif

            <div id="image-section" style="display: none;">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Presensi Masuk</label>
                    <!-- <div class="input-group">
            <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" capture="camera" disabled>
          </div>
          @error('gambar')
          <div class="invalid-feedback" style="display: block">
            {{ $message }}
          </div>
          @enderror -->
                    <div class="mb-3" style="width: 200px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);">
                      <a href="{{ asset('images/' . $presensi->gambar) }}" data-lightbox="{{ $presensi->id }}">
                        <div class="cardgambar" style="width: 200px;">
                          <img id="image-preview" style="width: 200px; height:200px;" class="card-img-top" src="{{ asset('images/' . $presensi->gambar) }}" alt="Preview Image">
                        </div>
                      </a>
                    </div>
                  </div>
                </div>
                @if ($presensi->gambar_pulang == null)
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="gambar_pulang">Bukti Presensi</label>
                    <input type="file" name="gambar_pulang" id="gambar_pulang" class="form-control custom-file-upload" accept="image/*" capture="camera">
                  </div>
                  @error('gambar_pulang')
                  <div class="invalid-feedback" style="display: block">
                    {{ $message }}
                  </div>
                  @enderror
                  <div class="mb-3">
                    <div class="cardgambar_pulang" style="width: 200px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);">
                      <img id="image-preview-pulang" class="card-img-top" src="#" alt="Preview Image" style="display: none; width: 200px; height: 200px;">
                    </div>
                  </div>
                </div>
                @else
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Bukti Presensi Pulang</label>
                    <div class="input-group">
                      <input type="file" name="gambar_pulang" id="gambar_pulang" class="form-control" accept="image/*" capture="camera" disabled>
                    </div>
                    @error('gambar_pulang')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                  <div class="mb-3" style="width: 200px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);">
                    <a href="{{ asset('images/' . $presensi->gambar_pulang) }}" data-lightbox="{{ $presensi->id }}">
                      <div class="cardgambar" style="width: 200px;">
                        <img id="image-preview" style="width: 200px; height:200px;" class="card-img-top" src="{{ asset('images/' . $presensi->gambar_pulang) }}" alt="Preview Image">
                      </div>
                    </a>
                  </div>
                </div>
                @endif
              </div>
            </div>

            <div id="catatan-section" style="display: none;">
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Catatan</label>
                    <div class="input-group">
                      <textarea name="note" id="note" placeholder="Masukkan catatan" class="form-control">{{ $presensi->note }}</textarea>
                    </div>
                    @error('note')
                    <div class="invalid-feedback" style="display: block">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>
            <!--================== END ==================-->

            <!--================== menampilkan button berdasarkan level ==================-->
            @if (Auth::user()->level == 'karyawan' || Auth::user()->level == 'staff' || Auth::user()->level == 'trainer')
            @else
            @if ($presensi->status_pulang == null)
            <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> SIMPAN</button>
            @elseif (Auth::user()->level == 'manager')
            <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> SIMPAN</button>
            @else
            <button class="btn btn-primary mr-1 btn-secondary" type="submit" disabled><i class="fa fa-paper-plane"></i> SIMPAN</button>
            @endif
            <a href="{{ route('account.presensi.index') }}" class="btn btn-info">
              <i class="fa fa-undo"></i> KEMBALI
            </a>
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