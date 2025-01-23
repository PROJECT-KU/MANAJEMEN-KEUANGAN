@extends('layouts.account')

@section('title')
Tambah Artikel | MIS
@stop

<!--================== KATA KUNCI ==================-->
<style>
    .keyword-container {
        display: inline-block;
        margin: 5px;
        padding: 5px 10px;
        border-radius: 20px;
        background-color: #f0f0f0;
    }

    .close-icon {
        margin-left: 5px;
        cursor: pointer;
    }

    .keyword {
        margin-right: 5px;
    }

    #error-message {
        color: red;
        display: none;
    }
</style>
<!--================== END ==================-->
<link rel="stylesheet" href="{{ asset('assets/artikel/summernote/summernote-bs4.css') }}">

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>TAMBAH ARTIKEL</h1>
        </div>

        <div class="section-body">
            <form action="{{ route('account.Artikel.store') }}" method="POST" enctype="multipart/form-data" id="myForm">
                @csrf

                <div class="card">
                    <div class="card-header">
                        <h4>KATEGORI</h4>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Kategori</label>
                                    <select class="form-control select2" name="categories_artikel_id" id="kategoriSelect" style="width: 100%" required>
                                        <option value="" selected disabled>-- PILIH KATEGORI ARTIKEL --</option>
                                        @foreach ($categories_artikel as $kategori)
                                        <option value="{{ $kategori->id }}">{{ strtoupper($kategori->kategori) }}</option>
                                        @endforeach
                                    </select>
                                    @error('kategori')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Penulis</label>
                                    <select class="form-control select2" name="user_id" id="karyawanSelect" style="width: 100%" required>
                                        <option value="">-- PILIH NAMA PENULIS --</option>
                                        @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ strtoupper($user->full_name) }}</option>
                                        @endforeach
                                    </select>

                                    @error('user_id')
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
                                    <label>Status</label>
                                    <select class="form-control" name="status" required>
                                        <option value="" disabled selected>-- PILIH STATUS ARTIKEL --</option>
                                        <option value="draft">DRAFT</option>
                                        <option value="publish">PUBLISH</option>
                                    </select>
                                    @error('status')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>ARTIKEL</h4>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Judul Artikel</label>
                                    <div class="input-group">
                                        <input type="text" name="judul" value="{{ old('judul') }}" placeholder="Masukkan Judul Artikel" class="form-control" required>
                                    </div>
                                    @error('judul')
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
                                    <label>Kata Kunci</label>
                                    <div class="input-group" id="keyword-container">
                                        <!-- Tempat untuk menampilkan kata kunci -->
                                    </div>
                                    <input id="kata_kunci_input" type="text" name="kata_kunci" placeholder="Masukkan Kata Kunci Artikel" class="form-control" onkeypress="return/[a-zA-Z ]/i.test(event.key)">
                                    <p class="mt-2" style="color: red;"><i class="fas fa-info-circle"></i> Tekan Enter di keyboard setelah memasukan kata kunci</p>
                                    @error('kata_kunci')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                    <!-- Elemen tersembunyi untuk menyimpan kata kunci sebagai tag -->
                                    <input type="hidden" id="kata_kunci_tags" name="kata_kunci_tags">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Thumbnail</label>
                                    <div class="input-group">
                                        <input type="file" name="gambar_depan" id="gambar_depan" class="form-control" accept="image/*" required>
                                    </div>
                                    <!-- <i class="fas fa-info mt-2" style="color: red"></i> Upload gambar_depan atau Gunakan Kamera -->
                                    @error('gambar_depan')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="card" style="width: 18rem;">
                                        <img id="image-preview_gambar_depan" class="card-img-top" src="#" alt="Preview Image">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Gambar Cover</label>
                                    <div class="input-group">
                                        <input type="file" name="gambar_cover" id="gambar_cover" class="form-control" accept="image/*" required>
                                    </div>
                                    <!-- <i class="fas fa-info mt-2" style="color: red"></i> Upload gambar_cover atau Gunakan Kamera -->
                                    @error('gambar_cover')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="card" style="width: 18rem;">
                                        <img id="image-preview_gambar_cover" class="card-img-top" src="#" alt="Preview Image">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card card-outline card-info">
                                    <div class="card-body pad">
                                        <div class="mb-3">
                                            <textarea class="textarea" name="isi" id="isi" placeholder="Place some text here" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> SIMPAN</button>
                        <button class="btn btn-warning btn-reset" type="reset"><i class="fa fa-redo"></i> RESET</button>
            </form>

        </div>
</div>
</div>
</section>
</div>

<!--================== SUMMERNOTE ==================-->
<script src="{{ asset('assets/artikel/summernote/summernote-bs4.min.js') }}"></script>
<script>
    $(function() {
        // Initialize Summernote
        $('.textarea').summernote({
            height: 300, // Set the height of the editor to 300px
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link']], // Only keep the link button
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    })
</script>
<!--================== END ==================-->

<!--================== SWEET ALERT KATA KUNCI ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Event listener untuk tombol submit
    document.querySelector('.btn-submit').addEventListener('click', function(e) {
        e.preventDefault(); // Mencegah form untuk langsung di-submit

        // Menampilkan Sweet Alert dengan pilihan
        Swal.fire({
            title: 'Apakah Kamu Sudah Menekan Enter Di Keyboard Pada Input Kata Kunci ?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sudah',
            cancelButtonText: 'Belum'
        }).then((result) => {
            // Jika opsi "Sudah" dipilih
            if (result.isConfirmed) {
                // Lakukan aksi penyimpanan di sini (misalnya, dengan menyubmit form)
                document.getElementById('myForm').submit();
            }
            // Jika opsi "Belum" dipilih
            else if (result.dismiss === Swal.DismissReason.cancel) {
                // Tidak lakukan apa-apa
            }
        });
    });
</script>
<!--================== END ==================-->

<!--================== KATA KUNCI ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.getElementById('kata_kunci_input');
        var keywordContainer = document.getElementById('keyword-container');
        var tagsInput = document.getElementById('kata_kunci_tags');
        var keywords = [];

        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addKeyword(input.value);
                input.value = '';
                focusNextKeywordInput();
            }
        });

        function addKeyword(keyword) {
            keywords.push(keyword); // Menyimpan kata kunci ke dalam array
            renderKeywords(); // Memperbarui tampilan kata kunci
            updateTagsInput(); // Memperbarui nilai input tersembunyi
        }

        function renderKeywords() {
            keywordContainer.innerHTML = ''; // Mengosongkan tampilan kata kunci sebelumnya
            keywords.forEach(function(keyword) {
                var keywordSpan = document.createElement('span');
                keywordSpan.textContent = keyword;
                keywordSpan.classList.add('keyword');

                var closeIcon = document.createElement('i');
                closeIcon.classList.add('fas', 'fa-times', 'close-icon');
                closeIcon.addEventListener('click', function() {
                    removeKeyword(keyword);
                });

                var keywordDiv = document.createElement('div');
                keywordDiv.classList.add('keyword-container');
                keywordDiv.appendChild(keywordSpan);
                keywordDiv.appendChild(closeIcon);

                keywordContainer.appendChild(keywordDiv);
            });
        }

        function removeKeyword(keyword) {
            keywords = keywords.filter(function(value) {
                return value !== keyword;
            });
            renderKeywords(); // Memperbarui tampilan kata kunci setelah menghapus
            updateTagsInput(); // Memperbarui nilai input tersembunyi setelah menghapus
        }

        function updateTagsInput() {
            tagsInput.value = keywords.join(','); // Menyimpan kata kunci sebagai tag dalam input tersembunyi
            // Menyimpan kata kunci sebagai placeholder input
            input.placeholder = keywords.length > 0 ? keywords.join(', ') : "Masukkan Kata Kunci Artikel";
        }

        function focusNextKeywordInput() {
            input.focus(); // Fokus kembali ke input setelah menambah kata kunci
        }

        // Pengiriman kata kunci ke server saat formulir disubmit
        document.getElementById('form-id').addEventListener('submit', function(e) {
            e.preventDefault(); // Mencegah pengiriman formulir
            // Mengirim kata kunci ke server (gunakan AJAX atau bentuk pengiriman data yang sesuai)
            // Misalnya, dapat menggunakan fetch API untuk mengirim data
            fetch('url/to/server', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        kata_kunci: keywords.join(',')
                    }), // Mengirim kata kunci yang sudah di-update ke server
                })
                .then(response => {
                    if (response.ok) {
                        // Lakukan sesuatu jika pengiriman berhasil
                    } else {
                        // Handle kesalahan jika pengiriman gagal
                    }
                })
                .catch(error => {
                    // Handle kesalahan jika terjadi kesalahan jaringan
                });
        });
    });
</script>
<!--================== END ==================-->

<!--================== MAKSIMAL UPLOAD GAMBAR & JENIS FILE YANG DI PERBOLEHKAN ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- GAMBAR DEPAN -->
<script>
    document.getElementById('gambar_depan').addEventListener('change', function() {
        const maxFileSizeInBytes = 5 * 1024 * 1024; // 5MB
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
<!-- END -->

<!-- GAMBAR COVER -->
<script>
    document.getElementById('gambar_cover').addEventListener('change', function() {
        const maxFileSizeInBytes = 5 * 1024 * 1024; // 5MB
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
<!-- END -->
<!--================== END ==================-->

<!--================== PREVIEW IMAGE ==================-->
<!-- GAMBAR DEPAN -->
<script>
    const imageInput = document.getElementById('gambar_depan');
    const imagePreview = document.getElementById('image-preview_gambar_depan');

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
<!-- END -->

<!-- GAMBAR COVER -->
<script>
    const imageInputCover = document.getElementById('gambar_cover');
    const imagePreviewCover = document.getElementById('image-preview_gambar_cover');

    imageInputCover.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreviewCover.src = e.target.result;
                imagePreviewCover.style.display = 'block'; // Show the preview
            };
            reader.readAsDataURL(file);
        }
    });
</script>
<!-- END -->
<!--================== END ==================-->

<!--================== CKEDITOR ==================-->
<!-- <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        ClassicEditor
            .create(document.querySelector('#isi'))
            .then(editor => {
                console.log('CKEditor initialized successfully');
            })
            .catch(error => {
                console.error(error);
            });
    });
</script> -->

<!--================== END ==================-->

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