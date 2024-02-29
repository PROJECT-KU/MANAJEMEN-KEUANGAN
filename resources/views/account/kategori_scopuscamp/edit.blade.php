@extends('layouts.account')

@section('title')
Update Kategori | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>UPDATE KATEGORI</h1>
        </div>

        <div class="section-body">

            <div class="card">
                <!-- <div class="card-header">
                    <h4><i class="fas fa-hand-holding-usd"></i> UPDATE KATEGORI</h4>
                </div> -->

                <div class="card-body">

                    <form action="{{ route('account.kategori.update', $categories_scopuscamp->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Scopus Camp</label>
                                    <div class="input-group">
                                        <input type="text" name="camp" value="{{ $categories_scopuscamp->camp }}" placeholder="Masukkan Nama Scopus Camp" class="form-control" required>
                                    </div>
                                    @error('camp')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tempat Pelaksanaan</label>
                                    <div class="input-group">
                                        <input type="text" name="tempat" value="{{ $categories_scopuscamp->tempat }}" placeholder="Masukkan Tempat Pelaksanaan" class="form-control" required>
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Mulai Pelaksaan</label>
                                    <input type="datetime-local" class="form-control" name="mulai" placeholder="Pilih Tanggal" value="{{ $categories_scopuscamp->mulai }}" required>
                                    @error('mulai')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mt-4" style="text-align: center;">
                                    <label>S/D</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Selesai Pelaksaan</label>
                                    <input type="datetime-local" class="form-control" name="selesai" placeholder="Pilih Tanggal" value="{{ $categories_scopuscamp->selesai }}" required>
                                    @error('selesai')
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
                                    <label>Kuota Scopus Camp</label>
                                    <div class="input-group">
                                        <input type="text" name="kuota" value="{{ $categories_scopuscamp->kuota }}" placeholder="Masukkan Kuota Scopus Camp" class="form-control" required>
                                    </div>
                                    @error('kuota')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" name="status">
                                        <option value="" disabled selected>-- PILIH STATUS --</option>
                                        <option value="AKTIF" {{ $categories_scopuscamp->status == 'AKTIF' ? 'selected' : '' }}>AKTIF</option>
                                        <option value="TIDAK AKTIF" {{ $categories_scopuscamp->status == 'TIDAK AKTIF' ? 'selected' : '' }}>TIDAK AKTIF</option>
                                    </select>
                                    @error('status')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> SIMPAN</button>
                        <a href="{{ route('account.kategori.index') }}" class="btn btn-info">
                            <i class="fa fa-undo"></i> KEMBALI
                        </a>

                    </form>

                </div>
            </div>
        </div>
    </section>
</div>

<!-- maksimal upload gambar & jenis file yang di perbolehkan -->
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
<!-- end -->

<!-- upload image -->
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
<!-- end upload image -->

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
@stop