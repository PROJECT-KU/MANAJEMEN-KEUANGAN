@extends('layouts.account')

@section('title')
Tambah Data TO DO LIST| MIS
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
        .image-preview-container-paper {
            min-height: 100px;
        }

        .file-upload-paper {
            font-size: 14px;
            padding: 8px 15px;
        }
    }
</style>
<!--================== END ==================-->

<!--================== CHECH LIST TASK  ==================-->
<style>
    .keyword-container {
        display: inline-block;
        margin: 5px;
        padding: 5px 10px;
        border-radius: 20px;
        background-color: #6495ED;
        color: #fff;
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

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>TAMBAH DATA TO DO LIST</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('account.todolist.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Assign Task Pertama</label>
                                    <select class="form-control select2" name="user_id" id="karyawanSelect" style="width: 100%" required>
                                        <option value="">-- PILIH NAMA KARYAWAN --</option>
                                        @foreach ($datas as $user)
                                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Assign Task Kedua</label>
                                    <select class="form-control select2" name="user_id_kedua" id="karyawanSelect" style="width: 100%">
                                        <option value="">-- PILIH NAMA KARYAWAN --</option>
                                        @foreach ($datas as $user)
                                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Assign</label>
                                    <div class="input-group">
                                        <input type="date" name="tanggal_assign" id="tanggal_assign" value="" placeholder="Masukkan Tanggal Assign" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Deadline</label>
                                    <div class="input-group">
                                        <input type="date" name="tanggal_deadline" id="tanggal_deadline" value="" placeholder="Masukkan Tanggal Deadline" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status Task</label>
                                    <select class="form-control" name="status" id="status" required>
                                        <option value="" disabled selected>-- PILIH STATUS TASK --</option>
                                        <option value="Assign Task">ASSIGN TASK</option>
                                        <option value="In Progress">IN PROGRESS</option>
                                        <option value="Testing">TESTING</option>
                                        <option value="Revisi">REVISI</option>
                                        <option value="Completed">COMPLETED</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Prioritas Task</label>
                                    <select class="form-control" name="prioritas_task" id="prioritas_task" required>
                                        <option value="" disabled selected>-- PILIH PRIORITAS TASK --</option>
                                        <option value="Low">LOW</option>
                                        <option value="Medium">MEDIUM</option>
                                        <option value="High">HIGH</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Judul Task</label>
                                    <input type="text" name="judul_task" value="{{ old('judul_task') }}" placeholder="Masukkan Judul Task" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Deskripsi</label>
                                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Check List Task</label>
                                    <div class="input-group" id="keyword-container">
                                        <!-- Tempat untuk menampilkan kata kunci -->
                                    </div>
                                    <input id="kata_kunci_input" type="text" name="tasklist" placeholder="Masukkan Check List Task" class="form-control" onkeypress="return/[a-zA-Z ]/i.test(event.key)">
                                    <p class="mt-2" style="color: red;"><i class="fas fa-info-circle"></i> Tekan Enter di keyboard setelah memasukan Check List Task</p>
                                    <!-- Elemen tersembunyi untuk menyimpan kata kunci sebagai tag -->
                                    <input type="hidden" id="tasklist" name="tasklist">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Link Akses</label>
                                    <input type="text" name="link_akses" value="{{ old('link_akses') }}" placeholder="Masukkan Link Akses" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group custom-file-upload">
                                    <label for="file_task" class="form-label">File Task</label>
                                    <div class="input-group">
                                        <input type="file" name="file_task" id="file_task" class="inputfile" hidden>
                                        <label for="file_task" class="file-upload-paper">
                                            <i class="fas fa-cloud-upload-alt"></i> Choose File Task
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="image-preview-container-paper">
                                    <div id="imagePreview-file-task" class="image-preview"></div>
                                    <span id="file-selected-file-task" class="file-info-task"></span>
                                </div>
                            </div>
                        </div>

                </div>
            </div>
        </div>

        <div class="d-flex mt-3">
            <button class="btn btn-primary mr-1 btn-submit" type="submit" style="flex: 1; height:40px; font-size: 15px;"><i class="fa fa-paper-plane"></i> SIMPAN</button>
            <button class="btn btn-warning btn-reset" type="reset" style="flex: 1; height:40px; font-size: 15px;"><i class="fa fa-redo"></i> RESET</button>
        </div>

        </form>
    </section>
</div>

<!--================== CHECK LIST TASK ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.getElementById('kata_kunci_input');
        var keywordContainer = document.getElementById('keyword-container');
        var tagsInput = document.getElementById('tasklist');
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
            input.placeholder = keywords.length > 0 ? keywords.join(', ') : "Masukkan Check List Task";
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

<!--================== SELECT USER LEBIH DARI 1 ==================-->
<!-- <script>
    $(document).ready(function() {
        $('#karyawanSelect').select2({
            placeholder: "-- PILIH NAMA KARYAWAN --",
            allowClear: true
        });
    });
</script> -->
<!--================== END ==================-->

<!--================== UPLOAD FILE WITH VIEW ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function handleFileUpload(inputId, previewId, fileInfoId, allowedTypes, maxFileSizeMB, alertFileSizeMB) {
        document.getElementById(inputId).addEventListener('change', function(event) {
            var fileInput = event.target;
            var file = fileInput.files[0];
            var fileName = file.name;
            var fileSizeKB = (file.size / 1024).toFixed(10); // Ukuran file dalam KB
            var fileSizeMB = (file.size / (1024 * 1024)).toFixed(10); // Ukuran file dalam MB

            // Validasi tipe file
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Hanya file yang berformat JPG, JPEG, PNG, PDF, DOC, DOCX, MP4, MOV, AVI yang di izinkan!'
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

            // Preview untuk gambar
            if (file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(previewId).innerHTML = `<img src="${e.target.result}" alt="${fileName}">`;
                };
                reader.readAsDataURL(file);
            } else {
                // Preview untuk non-gambar
                document.getElementById(previewId).innerHTML = '<span style="color: #555;">Preview tidak tersedia untuk tipe file ini.</span>';
            }
        });
    }

    // Inisialisasi untuk file task
    handleFileUpload(
        'file_task',
        'imagePreview-file-task',
        'file-selected-file-task',
        [
            'image/jpeg', 'image/png', 'image/jpg',
            'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'video/mp4', 'video/avi', 'video/mpeg', 'video/quicktime', 'video/mov'
        ],
        500, // Maksimal 500MB
        10 // Alert jika lebih dari 2MB
    );
</script>
<!--================== END ==================-->

<!--================== DESKRIPSI ==================-->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
    // Initialize CKEditor 5
    ClassicEditor
        .create(document.querySelector('#deskripsi'))
        .catch(error => {
            console.error(error);
        });
</script>
<!--================== END ==================-->

<script>
    var timeoutHandler = null;

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