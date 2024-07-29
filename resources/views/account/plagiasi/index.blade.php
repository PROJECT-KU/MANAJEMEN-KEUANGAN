@extends('account.plagiasi.layout.header')

@section('title')
Cek Plagiasi | Rumah Scopus
@stop

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/min/dropzone.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11">
<style>
    /* CSS untuk mempercantik tampilan */
    .card {
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        background-color: #fff;
    }

    .dz-message {
        text-align: center;
        font-size: 18px;
        padding: 20px;
        border: 2px dashed #ccc;
        color: orange;
        background-color: #fff;
        border-radius: 5px;
        margin: 20px 0;
    }

    .dropzone {
        border: 2px dashed orange;
        padding: 25px;
        background-color: #fff;
        clear: both;
    }

    /* CSS untuk menyembunyikan ukuran file */
    .dz-size {
        display: none !important;
    }

    /* Hide default preview elements */
    .dz-preview.dz-file-preview {
        display: none;
    }

    .dz-file-preview {
        display: inline-block;
        vertical-align: top;
        margin-right: 10px;
    }

    /* CSS untuk gaya ikon */
    .dz-preview.dz-image-preview .dz-image {
        border-radius: 10px;
    }

    .dz-preview.dz-file-preview .dz-details {
        text-align: center;
    }

    .dz-file-preview .dz-image img {
        max-width: 50px;
        max-height: 50px;
        display: block;
        margin: 0 auto 5px;
        border-radius: 10px;
        margin-top: 10px;
    }

    .dz-file-preview .dz-details {
        text-align: center;
        margin-top: 20px;
        font-size: 12px;
    }

    .file-preview-card {
        width: 120px;
        height: 120px;
        padding: 10px;
        border-radius: 10px;
        background-color: #fff;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        margin: 10px;
        display: inline-block;
        overflow: hidden;
        position: relative;
    }

    .file-preview-card .delete-icon {
        position: absolute;
        top: 5px;
        right: 5px;
        color: red;
        cursor: pointer;
    }

    .outer-card {
        max-width: 900px;
        margin: 50px auto;
        padding: 25px;
        border: 1px solid #ccc;
        border-radius: 10px;
        background-color: #fff;
        animation: rise 0.5s ease-out;
    }

    .button-container {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }

    .hero {
        justify-content: flex-start;
    }

    /* Ensure all elements inside the card fit within the fixed size */
    .dz-image img {
        max-width: 100%;
        max-height: 60px;
        /* Adjust as needed */
        display: block;
        margin: 0 auto;
    }

    .dz-details {
        text-align: center;
    }

    .dz-filename span {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
    }
</style>

@section('konten')
<!-- ======= Hero Section ======= -->
<section id="hero" class="hero d-flex align-items-center">
    <div class="container justify-content-start">
        <div class="row">
            <div class="col-lg-6 d-flex flex-column justify-content-center">
                <h1 data-aos="fade-up">Temukan Plagiasi Manuscript Anda di Rumah Scopus Foundation</h1>
                <h2 data-aos="fade-up" data-aos-delay="400">Jangan takut untuk gagal, karena kegagalan adalah bagian dari perjalanan menuju sukses</h2>
                <div data-aos="fade-up" data-aos-delay="600">
                    <div class="text-center text-lg-start">
                        <a href="https://www.youtube.com/@rumahscopus" class="btn-get-started scrollto d-inline-flex align-items-center justify-content-center align-self-center" target="_blank">
                            <span>Get Started</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 hero-img" data-aos="zoom-out" data-aos-delay="200">
                <img src="{{ asset('assets/artikel/img/Tiny.jpg') }}" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</section>

<div class="outer-card">
    <div class="card-body">
        <h3 style="font-weight: bold;">Cek Plagiasi No-Repository</h3>
        <div class="card" id="upload-card">
            <div class="button-container">
                <button type="button" class="btn" id="upload-button" style="background-color: #5F9EA0; color:white;">
                    <i class="fas fa-upload"></i> Unggah File
                </button>
                <button type="button" class="btn btn-danger" id="remove-button">
                    <i class="fas fa-trash-alt"></i> Hapus File
                </button>
            </div>
            <form action="{{ route('cek.plagiasi.proses') }}" method="POST" enctype="multipart/form-data" class="dropzone" id="file-upload">
                @csrf
                <div class="dz-message" data-dz-message>
                    <span>Drop files here or click to upload.</span>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sertakan library Dropzone.js dan SweetAlert -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/min/dropzone.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Inisialisasi Dropzone dengan opsi yang diperlukan
    Dropzone.options.fileUpload = {
        paramName: "file",
        maxFilesize: 10, // Ukuran maksimal file dalam MB
        acceptedFiles: ".doc,.docx,.pdf", // Jenis file yang diterima
        addRemoveLinks: true, // Tampilkan tombol hapus di pratinjau
        dictDefaultMessage: "Drop files here or click to upload", // Pesan default
        dictRemoveFile: "", // Hapus teks tombol
        maxFiles: 3, // Jumlah maksimal file yang dapat diunggah
        uploadMultiple: true, // Mengizinkan upload multiple file
        success: function(file, response) {
            console.log(response);
            document.getElementById('upload-card').style.backgroundColor = 'white';
        },
        error: function(file, errorMessage) {
            console.error(errorMessage);
        },
        init: function() {
            var myDropzone = this; // Simpan referensi ke instance Dropzone

            // Fungsi untuk membersihkan semua pratinjau
            var clearPreviews = function() {
                // Hapus semua elemen pratinjau
                while (myDropzone.previewsContainer.firstChild) {
                    myDropzone.previewsContainer.removeChild(myDropzone.previewsContainer.firstChild);
                }
                // Tampilkan kembali pesan default
                var messageElement = document.createElement('div');
                messageElement.className = 'dz-message';
                messageElement.dataset.dzMessage = true;
                messageElement.innerHTML = '<span>Drop files here or click to upload.</span>';
                myDropzone.previewsContainer.appendChild(messageElement);

                // Reset warna latar belakang kartu unggah
                document.getElementById('upload-card').style.backgroundColor = '#fff';
            };

            // Menambahkan file baru
            this.on("addedfile", function(file) {
                // Validasi jenis file
                if (!file.type.match(/application\/(msword|vnd.openxmlformats-officedocument.wordprocessingml.document)|application\/pdf/)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'File type not allowed. Only PDF, DOC, and DOCX are accepted.'
                    }).then(function() {
                        myDropzone.removeFile(file);
                    });
                }

                // Ambil nama file dan potong jika lebih dari 13 karakter
                var fileName = file.name;
                if (fileName.length > 13) {
                    fileName = fileName.substring(0, 13) + '...';
                }

                let iconSrc = "";
                if (file.type === "application/pdf") {
                    iconSrc = "{{ asset('assets/img/avatar/pdf.png') }}";
                } else if (file.type === "application/msword" || file.type === "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
                    iconSrc = "{{ asset('assets/img/avatar/docx.png') }}";
                } else {
                    iconSrc = "https://via.placeholder.com/50";
                }

                // Buat template pratinjau file
                var previewTemplate = document.createElement('div');
                previewTemplate.className = 'dz-file-preview file-preview-card';
                previewTemplate.innerHTML = `
                    <div class="dz-image">
                        <img src="${iconSrc}" alt="File Icon" />
                    </div>
                    <div class="dz-details">
                        <div class="dz-filename"><span title="${file.name}">${fileName}</span></div>
                    </div>
                    <div class="delete-icon">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                `;

                file.previewElement = previewTemplate;
                myDropzone.previewsContainer.appendChild(previewTemplate);

                // Tambahkan event listener untuk tombol hapus
                previewTemplate.querySelector('.delete-icon').addEventListener('click', function() {
                    myDropzone.removeFile(file);
                    previewTemplate.remove(); // Hapus elemen pratinjau dari DOM
                });
            });

            // Fungsi untuk menghandle klik tombol hapus semua
            document.getElementById('remove-button').addEventListener('click', function() {
                myDropzone.removeAllFiles(true); // Hapus semua file dari Dropzone
                clearPreviews(); // Hapus semua pratinjau dan tampilkan pesan default
            });
        }
    };

    // Inisialisasi Dropzone
    var myDropzone = new Dropzone("#file-upload");

    // Fungsi untuk memulai proses upload saat tombol "Unggah File" diklik
    document.getElementById('upload-button').addEventListener('click', function() {
        // Jika tidak ada file yang dipilih, buka dialog pemilihan file
        if (myDropzone.getQueuedFiles().length === 0) {
            myDropzone.hiddenFileInput.click();
        } else {
            myDropzone.processQueue(); // Mulai proses upload file
        }
    });

    // Fungsi untuk menghapus file saat tombol "Hapus File" diklik
    document.getElementById('remove-button').addEventListener('click', function() {
        myDropzone.removeAllFiles(true); // Hapus semua file dari Dropzone
        document.getElementById('upload-card').style.backgroundColor = '#fff'; // Reset warna latar belakang kartu menjadi putih setelah file dihapus
    });
</script>


@stop