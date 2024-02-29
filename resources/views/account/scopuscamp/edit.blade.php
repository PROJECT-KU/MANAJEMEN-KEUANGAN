@extends('layouts.account')

@section('title')
Update Scopus Camp | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>UPDATE SCOPUS CAMP</h1>
        </div>

        <div class="section-body">
            <form action="{{ route('account.scopuscamp.update', $scopuscamp->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!--================== DETAIL DATA PENDAFTARAN ==================-->
                <div class="card">
                    <div class="card-header">
                        <h4>DATA PENDAFTAR SCOPUS CAMP</h4>
                        <div class="card-header-action">
                            <h4 class="float-right"><i class="fas fa-receipt"></i> ID TRANSAKSI : {{ $scopuscamp->id_transaksi }}</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <div class="input-group">
                                        <input type="text" name="email" value="{{ $scopuscamp->email }}" placeholder="Masukkan Email Pendaftar" class="form-control" readonly>
                                    </div>
                                    @error('email')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama</label>
                                    <div class="input-group">
                                        <input type="text" name="nama" value="{{ $scopuscamp->nama }}" placeholder="Masukkan Nama Pendaftar" class="form-control" readonly>
                                    </div>
                                    @error('nama')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Judul Manuscript</label>
                                    <div class="input-group">
                                        <input type="text" name="judul" value="{{ $scopuscamp->judul }}" class="form-control" readonly>
                                    </div>
                                    @error('judul')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>No WA</label>
                                    <div class="input-group">
                                        <input type="text" name="telp" value="{{ $scopuscamp->telp }}" class="form-control" readonly>
                                    </div>
                                    @error('telp')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Afiliasi</label>
                                    <div class="input-group">
                                        <input type="text" name="afiliasi" value="{{ $scopuscamp->afiliasi }}" placeholder="Masukkan Afiliasi Pendaftar" class="form-control" readonly>
                                    </div>
                                    @error('afiliasi')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Pembayaran</label>
                                    <select class="form-control" name="pembayaran" readonly>
                                        <option value="" disabled selected>-- PILIH PEMBAYARAN --</option>
                                        <option value="BRI" {{ $scopuscamp->pembayaran == 'BRI' ? 'selected' : '' }}>BRI</option>
                                    </select>
                                    @error('pembayaran')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Scopus Camp</label>
                                    <div class="input-group">
                                        <input type="text" name="camp" value="{{ $scopuscamp->camp }}" placeholder="Masukkan Scopus Camp Pendaftar" class="form-control" readonly>
                                    </div>
                                    @error('camp')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bukti Pembayaran</label>
                                    <div class="input-group">
                                        <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" capture="camera" readonly>
                                    </div>
                                    @error('gambar')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bukti Pembayaran</label>
                                    <a href="{{ asset('scopuscamp/' . $scopuscamp->gambar) }}" data-lightbox="{{ $scopuscamp->id }}">
                                        <div class="card" style="width: 18rem;">
                                            @if ($scopuscamp->gambar == null)
                                            <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail">
                                            @else
                                            <img id="image-preview" style="width: 200px; height:200px;" class="card-img-top" src="{{ asset('scopuscamp/' . $scopuscamp->gambar) }}" alt="Preview Image">
                                            @endif
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--================== END ==================-->

                <!--================== UPDATE DATA PENDAFTARAN ==================-->
                <div class="card">
                    <div class="card-header">
                        <h4>UPDATE PENDAFTAR SCOPUS CAMP</h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Status Pendaftaran</label>
                                    <select class="form-control" name="status">
                                        <option value="" disabled selected>-- PILIH STATUS PENDAFTARAN --</option>
                                        <option value="Dalam Proses Pengecekan" {{ $scopuscamp->status == 'Dalam Proses Pengecekan' ? 'selected' : '' }}>Dalam Proses Pengecekan</option>
                                        <option value="Pendaftaran Diterima" {{ $scopuscamp->status == 'Pendaftaran Diterima' ? 'selected' : '' }}>Pendaftaran Diterima</option>
                                        <option value="Pendaftaran Ditolak" {{ $scopuscamp->status == 'Pendaftaran Ditolak' ? 'selected' : '' }}>Pendaftaran Ditolak</option>
                                    </select>
                                    @error('status')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Mulai Pelaksaan</label>
                                    <input type="datetime-local" class="form-control" name="mulai" placeholder="Pilih Tanggal" value="{{ $scopuscamp->mulai }}">
                                    @error('mulai')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mt-4 label" style="text-align: center;">
                                    <label>S/D</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Selesai Pelaksaan</label>
                                    <input type="datetime-local" class="form-control" name="selesai" placeholder="Pilih Tanggal" value="{{ $scopuscamp->selesai }}">
                                    @error('selesai')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Tempat Pelaksanaan</label>
                                    <div class="input-group">
                                        <input type="text" name="tempat" value="{{ $scopuscamp->tempat }}" placeholder="Masukkan Tempat Pelaksanaan" class="form-control">
                                    </div>
                                    @error('tempat')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Catatan</label>
                                    <div class="input-group">
                                        <textarea name="note" id="note" placeholder="Masukkan catatan" class="form-control" style="width: 100%;">{{ $scopuscamp->note }}</textarea>
                                    </div>
                                    @error('note')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> SIMPAN</button>
                        <a href="{{ route('account.scopuscamp.index') }}" class="btn btn-info">
                            <i class="fa fa-undo"></i> KEMBALI
                        </a>
                    </div>
                </div>
                <!--================== END ==================-->
            </form>
        </div>
    </section>
</div>

<!--================== CKEditor JS ==================-->
<script src="//cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('note', {
        width: '100%' // Atur tinggi editor menjadi 100% dari tinggi parent container
    });
</script>
<!--================== end ==================-->

<!--================== HIDDEN AND SHOW INPUTAN ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var statusSelect = document.querySelector('select[name="status"]');
        var mulaiInput = document.querySelector('input[name="mulai"]');
        var selesaiInput = document.querySelector('input[name="selesai"]');
        var tempatInput = document.querySelector('input[name="tempat"]');
        var noteInput = document.querySelector('textarea[name="note"]');
        var labelSd = document.querySelector('.form-group.mt-4.label');

        // Ambil nilai status yang dipilih saat halaman dimuat
        var selectedOption = statusSelect.value;

        // Panggil fungsi showElementsBasedOnStatus untuk menampilkan elemen berdasarkan selectedOption saat halaman dimuat
        showElementsBasedOnStatus();

        // Tambahkan event listener untuk perubahan pada elemen select
        statusSelect.addEventListener('change', function() {
            // Perbarui selectedOption saat nilai status berubah
            selectedOption = statusSelect.value;
            // Panggil kembali fungsi showElementsBasedOnStatus untuk menampilkan elemen sesuai dengan nilai status yang baru
            showElementsBasedOnStatus();
        });

        // Fungsi untuk menampilkan elemen berdasarkan selectedOption saat halaman dimuat
        function showElementsBasedOnStatus() {
            if (selectedOption === 'Pendaftaran Ditolak') {
                mulaiInput.parentElement.parentElement.style.display = 'none';
                selesaiInput.parentElement.parentElement.style.display = 'none';
                tempatInput.parentElement.parentElement.style.display = 'none';
                noteInput.parentElement.parentElement.style.display = 'block';
                labelSd.style.display = 'none';
            } else if (selectedOption === 'Pendaftaran Diterima') {
                mulaiInput.parentElement.parentElement.style.display = 'block';
                selesaiInput.parentElement.parentElement.style.display = 'block';
                tempatInput.parentElement.parentElement.style.display = 'block';
                noteInput.parentElement.parentElement.style.display = 'block';
                labelSd.style.display = 'block';
            } else {
                mulaiInput.parentElement.parentElement.style.display = 'none';
                selesaiInput.parentElement.parentElement.style.display = 'none';
                tempatInput.parentElement.parentElement.style.display = 'none';
                noteInput.parentElement.parentElement.style.display = 'none';
                labelSd.style.display = 'none';
            }
        }

        statusSelect.addEventListener('change', function() {
            var selectedOption = this.value;

            // Sembunyikan semua elemen
            mulaiInput.parentElement.parentElement.style.display = 'none';
            selesaiInput.parentElement.parentElement.style.display = 'none';
            tempatInput.parentElement.parentElement.style.display = 'none';
            noteInput.parentElement.parentElement.style.display = 'none';
            labelSd.style.display = 'none';

            // Tampilkan elemen sesuai dengan nilai yang dipilih
            if (selectedOption === 'Pendaftaran Diterima') {
                mulaiInput.parentElement.parentElement.style.display = 'block';
                selesaiInput.parentElement.parentElement.style.display = 'block';
                tempatInput.parentElement.parentElement.style.display = 'block';
                noteInput.parentElement.parentElement.style.display = 'block';
                labelSd.style.display = 'block';
            } else if (selectedOption === 'Pendaftaran Ditolak') {
                noteInput.parentElement.parentElement.style.display = 'block';
            }
        });
    });
</script>
<!--================== END ==================-->

<!--================== MAKSIMAL UPLOAD GAMBAR & JENIS FILE YANG DIPERBOLEHKAN ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('gambar').addEventListener('change', function() {
        const maxFileSizeInBytes = 5024 * 5024; // 5MB
        const allowedExtensions = ['jpg', 'jpeg', 'png'];
        const fileInput = this;

        if (fileInput.files.length > 0) {
            const selectedFile = fileInput.files[0];
            const fileSize = selectedFile.size; // Get the file size in bytes
            const fileName = selectedFile.name.toLowerCase();

            // Check file size
            if (fileSize > maxFileSizeInBytes) {
                // Display a SweetAlert error message
                Swal.fire({
                    icon: 'error',
                    title: 'Ukuran File Melebihi Batas',
                    text: 'Ukuran File Yang Diperbolehkan Dibawah 5MB.',
                });
                fileInput.value = ''; // Clear the file input
                return;
            }

            // Check file extension
            const fileExtension = fileName.split('.').pop();
            if (!allowedExtensions.includes(fileExtension)) {
                // Display a SweetAlert error message
                Swal.fire({
                    icon: 'error',
                    title: 'Jenis File Tidak Valid',
                    text: 'Hanya File JPG, JPEG, dan PNG Yang Diperbolehkan.',
                });
                fileInput.value = ''; // Clear the file input
            }
        }
    });
</script>
<!--================== END ==================-->

<!--================== UPLOAD IMAGE ==================-->
<script>
    const imageInput = document.getElementById('gambar');
    const imagePreview = document.getElementById('image-preview');

    imageInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block'; // Show the preview
            };
            reader.readAsDataURL(file);
        }
    });
</script>
<!--================== END ==================-->

<!--================== BUTTON LOADER, DATE PICKER & RESET BUTTON ==================-->
<script>
    // date picker
    if ($(".datetimepicker").length) {
        $('.datetimepicker').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD hh:mm'
            },
            singleDatePicker: true,
            timePicker: true,
            timePicker24Hour: true,
        });
    }

    var cleaveC = new Cleave('.currency', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });

    var timeoutHandler = null;
    // end

    /**
     * btn submit loader
     */
    $(".btn-submit").click(function() {
        $(".btn-submit").addClass('btn-progress');
        if (timeoutHandler) clearTimeout(timeoutHandler);

        timeoutHandler = setTimeout(function() {
            $(".btn-submit").removeClass('btn-progress');

        }, 1000);
    });

    /**
     * btn reset loader
     */
    $(".btn-reset").click(function() {
        $(".btn-reset").addClass('btn-progress');
        if (timeoutHandler) clearTimeout(timeoutHandler);

        timeoutHandler = setTimeout(function() {
            $(".btn-reset").removeClass('btn-progress');

        }, 500);
    })
</script>
<!--================== END ==================-->
@stop