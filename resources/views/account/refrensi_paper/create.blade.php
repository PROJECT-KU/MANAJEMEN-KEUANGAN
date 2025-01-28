@extends('layouts.account')

@section('title')
Tambah Data Refrensi Paper | MIS
@stop

<!--================== UPLOAD IMAGE WITH VIEW ==================-->
<style>
    .custom-file-upload {
        position: relative;
        overflow: hidden;
        margin-top: 10px;
    }

    .inputfile {
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
    }

    .file-upload {
        cursor: pointer;
        display: inline-block;
        padding: 10px 20px;
        color: #fff;
        background-color: #007bff;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        transition: background-color 0.3s;
    }

    .file-upload:hover {
        background-color: #0056b3;
    }

    #file-selected {
        display: block;
        margin-top: 5px;
        color: #888;
    }

    .image-preview {
        margin-top: 10px;
        display: none;
    }

    .image-preview img {
        max-width: 100%;
        height: auto;
        border-radius: 5px;
    }
</style>
<!--================== END ==================-->

<!--================== CARD VIEW UPLOAD FILE ==================-->
<style>
    /* Container styling - Anatomy */
    .custom-file-upload-anatomy {
        position: relative;
    }

    .file-upload-anatomy {
        display: inline-block;
        background: #28a745;
        color: #fff;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: 500;
        text-align: center;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .file-upload-anatomy i {
        margin-right: 8px;
        font-size: 18px;
    }

    .file-upload-anatomy:hover {
        background: #218838;
    }

    .image-preview-container-anatomy {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 2px dashed #28a745;
        padding: 15px;
        border-radius: 5px;
        min-height: 150px;
    }

    .file-info-anatomy {
        margin-top: 10px;
        font-size: 14px;
        color: #555;
        text-align: center;
    }

    /* Container styling - Paper */
    .custom-file-upload-paper {
        position: relative;
    }

    .file-upload-paper {
        display: inline-block;
        background: #007bff;
        color: #fff;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: 500;
        text-align: center;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .file-upload-paper i {
        margin-right: 8px;
        font-size: 18px;
    }

    .file-upload-paper:hover {
        background: #0056b3;
    }

    .image-preview-container-paper {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 2px dashed #007bff;
        padding: 15px;
        border-radius: 5px;
        min-height: 150px;
    }

    .file-info-paper {
        margin-top: 10px;
        font-size: 14px;
        color: #555;
        text-align: center;
    }

    .inputfile {
        display: none;
    }

    /* Responsive styling */
    @media (max-width: 768px) {

        .image-preview-container-anatomy,
        .image-preview-container-paper {
            min-height: 100px;
        }

        .file-upload-anatomy,
        .file-upload-paper {
            font-size: 14px;
            padding: 8px 15px;
        }
    }
</style>
<!--================== END ==================-->

<!--================== AUTHOR ==================-->
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
            <h1>TAMBAH DATA REFRENSI PAPER</h1>
        </div>

        <div class="section-body">
            <form action="{{ route('account.refrensi-paper.store') }}" method="POST" enctype="multipart/form-data" id="myForm">
                @csrf

                <!--================== DETAIL PAPER ==================-->
                <div class="card">
                    <div class="card-header">
                        <h4>DETAIL REFRENSI PAPER</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Subjek Area Journal</label>
                                    <div class="input-group" id="keyword-container">
                                        <!-- Tempat untuk menampilkan kata kunci -->
                                    </div>
                                    <input id="kata_kunci_input" type="text" name="subjek_area_journal" placeholder="Masukkan Subjek Area Journal" class="form-control" onkeypress="return/[a-zA-Z ]/i.test(event.key)">
                                    <p class="mt-2" style="color: red;"><i class="fas fa-info-circle"></i> Tekan Enter di keyboard setelah memasukan subjek area journal</p>
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
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Nama Author</label>
                                    <div class="input-group">
                                        <input type="text" name="nama_author" value="{{ old('nama_author') }}" placeholder="Masukkan Nama Author" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Journal</label>
                                    <div class="input-group">
                                        <input type="text" name="nama_journal" value="{{ old('nama_journal') }}" placeholder="Masukkan Nama Journal" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Quratile Jurnal</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Q-</span>
                                        </div>
                                        <input type="number" name="quartile_journal" value="{{ old('quartile_journal') }}" placeholder="Masukkan Quartile Jurnal" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>APC</label>
                                    <div class="input-group">
                                        <input type="text" name="apc" value="{{ old('apc') }}" placeholder="Masukkan APC Journal" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type Journal</label>
                                    <div class="input-group">
                                        <select name="type" class="form-control" required>
                                            <option value="" disabled selected>Pilih Type Journal</option>
                                            <option value="Open Access">Open Access</option>
                                            <option value="Close Access">Close Access</option>
                                            <option value="Hybrid">Hybrid</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Judul Paper</label>
                                    <div class="input-group">
                                        <input type="text" name="judul_paper" value="{{ old('judul_paper') }}" placeholder="Masukkan Judul Paper" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>DOI</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">https://doi.org/</span>
                                        </div>
                                        <input type="text" name="doi" value="{{ old('doi') }}" placeholder="Masukkan DOI Paper" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <label>Abstrak</label>
                                <div class="card card-outline card-info">
                                    <div class="card-body pad">
                                        <div class="mb-3">
                                            <textarea class="textarea" name="abstrak" id="abstrak" placeholder="Abstrak Journal" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="form-group custom-file-upload-anatomy">
                                    <label for="file" class="form-label">File Referensi</label>
                                    <div class="input-group">
                                        <input type="file" name="file" id="file" class="inputfile" hidden>
                                        <label for="file" class="file-upload-anatomy">
                                            <i class="fas fa-cloud-upload-alt"></i> Choose File Referensi
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="image-preview-container-anatomy">
                                    <div id="imagePreview-anatomy" class="image-preview"></div>
                                    <span id="file-selected-anatomy" class="file-info-anatomy"></span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex">
                            <button class="btn btn-primary btn-submit mr-1 rounded-pill" type="submit" style="width: 50%; font-size: 14px;">
                                <i class="fa fa-paper-plane"></i> SIMPAN
                            </button>
                            <button class="btn btn-warning btn-reset rounded-pill" type="reset" style="width: 50%; font-size: 14px;">
                                <i class="fa fa-redo"></i> RESET
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<!--================== UPLOAD FILE WITH VIEW ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function handleFileUpload(inputId, previewId, fileInfoId, allowedTypes, maxFileSizeMB, alertFileSizeMB) {
        document.getElementById(inputId).addEventListener('change', function(event) {
            var fileInput = event.target;
            var file = fileInput.files[0];
            var fileName = file.name;
            var fileSizeKB = (file.size / 1024).toFixed(5); // Ukuran file dalam KB
            var fileSizeMB = (file.size / (1024 * 1024)).toFixed(5); // Ukuran file dalam MB

            // Validasi tipe file
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Hanya file PDF, DOC, dan DOCX yang diizinkan. Harap pilih jenis file yang valid.'
                });
                fileInput.value = ""; // Reset input file
                document.getElementById(previewId).innerHTML = ""; // Reset preview
                document.getElementById(fileInfoId).innerHTML = ""; // Reset file info
                return;
            }

            // SweetAlert jika file melebihi alertFileSizeMB (2 MB)
            if (file.size > alertFileSizeMB * 1024 * 1024) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Ukuran file besar',
                    text: `Ukuran file ini adalah ${fileSizeMB} MB, yang melebihi ${alertFileSizeMB} MB. Harap pertimbangkan untuk mengunggah file yang lebih kecil.`
                });
            }

            // Validasi ukuran maksimum file
            if (file.size > maxFileSizeMB * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: `Ukuran file melebihi batas maksimum ${maxFileSizeMB} MB. Harap pilih file yang lebih kecil.`
                });
                fileInput.value = ""; // Reset input file
                document.getElementById(previewId).innerHTML = ""; // Reset preview
                document.getElementById(fileInfoId).innerHTML = ""; // Reset file info
                return;
            }

            // Tampilkan nama dan ukuran file
            var displaySize = fileSizeMB >= 1 ? `${fileSizeMB} MB` : `${fileSizeKB} KB`;
            document.getElementById(fileInfoId).innerHTML = `${fileName} (${displaySize})`;

            // Preview untuk non-gambar
            document.getElementById(previewId).innerHTML = '<span style="color: #555;">Preview tidak tersedia untuk tipe file ini.</span>';
        });
    }

    // Inisialisasi untuk file anatomy
    handleFileUpload(
        'file',
        'imagePreview-anatomy',
        'file-selected-anatomy',
        ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        5, // Maksimal 5MB
        5 // Alert jika lebih dari 2MB
    );
</script>

<!--================== END ==================-->

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

<!--================== SWEET ALERT AUTHOR ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Event listener untuk tombol submit
    document.querySelector('.btn-submit').addEventListener('click', function(e) {
        e.preventDefault(); // Mencegah form untuk langsung di-submit

        // Menampilkan Sweet Alert dengan pilihan
        Swal.fire({
            title: 'Apakah Kamu Sudah Menekan Enter Di Keyboard Pada Input Subjek Area Journal ?',
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

<!--================== AUTHOR ==================-->
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

<script>
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
            $("#karyawanSelect").val('');
        }, 500);
    })
</script>
@stop