@extends('layouts.account')

@section('title')
Update Data TO DO LIST| MIS
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
            <h1>UPDATE DATA TO DO LIST</h1>
        </div>

        <div class="section-body">
            <form action="{{ route('account.todolist.update', $todolist->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="card">
                    <div class="card-header">
                        <h4>ASSIGN TASK</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                        </div>
                        @if($user->level == 'manager')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Assign Task Pertama</label>
                                    <select class="form-control select2" name="user_id" id="karyawanSelect" style="width: 100%">
                                        <option value="">-- PILIH NAMA KARYAWAN --</option>
                                        @foreach ($datas as $user)
                                        <option value="{{ $user->id }}"
                                            {{ $todolist->user_id == $user->id ? 'selected' : '' }}>
                                            {{ $user->full_name }}
                                        </option>
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
                                        <option value="{{ $user->id }}"
                                            {{ $todolist->user_id_kedua == $user->id ? 'selected' : '' }}>
                                            {{ $user->full_name }}
                                        </option>
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
                                        <input type="date" name="tanggal_assign" id="tanggal_assign"
                                            value="{{ old('tanggal_assign', $todolist->tanggal_assign ? $todolist->tanggal_assign->format('Y-m-d') : '') }}" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Deadline</label>
                                    <div class="input-group">
                                        <input type="date" name="tanggal_deadline" id="tanggal_deadline"
                                            value="{{ old('tanggal_deadline', $todolist->tanggal_deadline ? $todolist->tanggal_deadline->format('Y-m-d') : '') }}" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status Task</label>
                                    <select class="form-control" name="status" id="status">
                                        <option value="" disabled selected>-- PILIH STATUS TASK --</option>
                                        <option value="Assign Task" {{ $todolist->status == 'Assign Task' ? 'selected' : '' }}>ASSIGN TASK</option>
                                        <option value="In Progress" {{ $todolist->status == 'In Progress' ? 'selected' : '' }}>IN PROGRESS</option>
                                        <option value="Testing" {{ $todolist->status == 'Testing' ? 'selected' : '' }}>TESTING</option>
                                        <option value="Revisi" {{ $todolist->status == 'Revisi' ? 'selected' : '' }}>REVISI</option>
                                        <option value="Completed" {{ $todolist->status == 'Completed' ? 'selected' : '' }}>COMPLETED</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Prioritas Task</label>
                                    <select class="form-control" name="prioritas_task" id="prioritas_task">
                                        <option value="" disabled selected>-- PILIH PRIORITAS TASK --</option>
                                        <option value="Low" {{ $todolist->prioritas_task == 'Low' ? 'selected' : '' }}>LOW</option>
                                        <option value="Medium" {{ $todolist->prioritas_task == 'Medium' ? 'selected' : '' }}>MEDIUM</option>
                                        <option value="High" {{ $todolist->prioritas_task == 'High' ? 'selected' : '' }}>HIGH</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        @else

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Assign Task Pertama</label>
                                    <select class="form-control select2" name="user_id" id="karyawanSelect" style="width: 100%" disabled>
                                        <option value="">-- PILIH NAMA KARYAWAN --</option>
                                        @foreach ($datas as $user)
                                        <option value="{{ $user->id }}"
                                            {{ $todolist->user_id == $user->id ? 'selected' : '' }}>
                                            {{ $user->full_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Assign Task Kedua</label>
                                    <select class="form-control select2" name="user_id_kedua" id="karyawanSelect" style="width: 100%" disabled>
                                        <option value="">-- PILIH NAMA KARYAWAN --</option>
                                        @foreach ($datas as $user)
                                        <option value="{{ $user->id }}"
                                            {{ $todolist->user_id_kedua == $user->id ? 'selected' : '' }}>
                                            {{ $user->full_name }}
                                        </option>
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
                                        <input type="date" name="tanggal_assign" id="tanggal_assign"
                                            value="{{ old('tanggal_assign', $todolist->tanggal_assign ? $todolist->tanggal_assign->format('Y-m-d') : '') }}" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Deadline</label>
                                    <div class="input-group">
                                        <input type="date" name="tanggal_deadline" id="tanggal_deadline"
                                            value="{{ old('tanggal_deadline', $todolist->tanggal_deadline ? $todolist->tanggal_deadline->format('Y-m-d') : '') }}" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status Task</label>
                                    <select class="form-control" name="status" id="status">
                                        <option value="" disabled selected>-- PILIH STATUS TASK --</option>
                                        <option value="Assign Task" {{ $todolist->status == 'Assign Task' ? 'selected' : '' }}>ASSIGN TASK</option>
                                        <option value="In Progress" {{ $todolist->status == 'In Progress' ? 'selected' : '' }}>IN PROGRESS</option>
                                        <option value="Testing" {{ $todolist->status == 'Testing' ? 'selected' : '' }}>TESTING</option>
                                        <option value="Revisi" {{ $todolist->status == 'Revisi' ? 'selected' : '' }}>REVISI</option>
                                        <option value="Completed" {{ $todolist->status == 'Completed' ? 'selected' : '' }}>COMPLETED</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Prioritas Task</label>
                                    <select class="form-control" name="prioritas_task" id="prioritas_task" disabled>
                                        <option value="" disabled selected>-- PILIH PRIORITAS TASK --</option>
                                        <option value="Low" {{ $todolist->prioritas_task == 'Low' ? 'selected' : '' }}>LOW</option>
                                        <option value="Medium" {{ $todolist->prioritas_task == 'Medium' ? 'selected' : '' }}>MEDIUM</option>
                                        <option value="High" {{ $todolist->prioritas_task == 'High' ? 'selected' : '' }}>HIGH</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>DETAIL TASK</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Judul Task</label>
                                    <input type="text" name="judul_task" value="{{ $todolist->judul_task }}" placeholder="Masukkan Judul Task" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Deskripsi</label>
                                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5">{{ $todolist->deskripsi }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>CHECKLIST TASK</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Check List Task</label>
                                    <div id="keyword-container" class="mb-2">
                                        <!-- Tempat untuk menampilkan kata kunci -->
                                    </div>
                                    <input id="kata_kunci_input" type="text" placeholder="Masukkan Check List Task"
                                        class="form-control" onkeypress="return /[a-zA-Z ]/i.test(event.key)">
                                    <p class="mt-2 text-danger">
                                        <i class="fas fa-info-circle"></i> Tekan Enter setelah memasukkan Check List Task
                                    </p>
                                    <input type="hidden" id="tasklist" name="tasklist" value="{{ $todolist->tasklist }}">
                                </div>
                            </div>
                        </div>

                        <!-- Tempat untuk menampilkan Checklist -->
                        @if($todolist->tasklist != null)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Checklist</label>

                                    @php
                                    $checkedItems = json_decode($todolist->checked, true) ?? []; // Decode checked items
                                    $tasklistArray = array_filter(array_map('trim', explode(',', $todolist->tasklist))); // Bersihkan array
                                    $totalTasks = count($tasklistArray);
                                    $completedTasks = count(array_intersect($tasklistArray, $checkedItems));
                                    $progressPercent = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                                    @endphp

                                    @foreach($tasklistArray as $task)
                                    @php
                                    $isChecked = in_array($task, $checkedItems);
                                    @endphp
                                    <div class="form-check">
                                        <input class="form-check-input task-checkbox" type="checkbox"
                                            id="task_{{ $loop->index }}" name="tasks[]"
                                            value="{{ $task }}" data-id="{{ $todolist->id }}"
                                            {{ $isChecked ? 'checked' : '' }}>

                                        <label class="form-check-label {{ $isChecked ? 'text-decoration-line-through text-muted' : '' }}"
                                            for="task_{{ $loop->index }}">
                                            {{ $task }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="progress mb-3">
                                    <div id="progress-bar" class="progress-bar bg-success" role="progressbar"
                                        style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>FILE TASK</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Link Akses</label>
                                    <input type="text" name="link_akses" value="{{ $todolist->link_akses }}" placeholder="Masukkan Link Akses" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5">
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
                                <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 50%; background-color:#888">
                                    @if($todolist->file_task)
                                    <div class="text-center">
                                        <p style="color: #fff;">Uploaded File: {{ basename($todolist->file_task) }}</p>
                                        <a href="{{ asset($todolist->file_task) }}" class="btn btn-sm btn-primary" download>
                                            <i class="fas fa-download"></i> Download File
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="image-preview-container-paper d-flex justify-content-center align-items-center" style="height: 100%;">
                                    <div id="imagePreview-file-task" class="image-preview"></div>
                                    <span id="file-selected-file-task" class="file-info-task">
                                        <p class="text-center">No file uploaded .</p>
                                    </span>
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

        // Ambil tasklist dari input hidden & pecah menjadi array
        var existingTasklist = tagsInput.value.trim();
        if (existingTasklist) {
            keywords = existingTasklist.split(',').map(item => item.trim());
            renderKeywords();
        }

        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                let newKeyword = input.value.trim();
                if (newKeyword !== "" && !keywords.includes(newKeyword)) {
                    addKeyword(newKeyword);
                    saveKeywordToDB(newKeyword);
                }
                input.value = ''; // Bersihkan input
                focusNextKeywordInput();
            }
        });

        function addKeyword(keyword) {
            keywords.push(keyword);
            renderKeywords();
            updateTagsInput();
        }

        function renderKeywords() {
            keywordContainer.innerHTML = ''; // Kosongkan sebelum render ulang
            keywords.forEach(function(keyword) {
                var keywordSpan = document.createElement('span');
                keywordSpan.textContent = keyword;
                keywordSpan.classList.add('badge', 'badge-primary', 'p-2', 'm-1');

                var closeIcon = document.createElement('i');
                closeIcon.classList.add('fas', 'fa-times', 'ml-2', 'text-danger', 'cursor-pointer');
                closeIcon.style.cursor = "pointer";
                closeIcon.addEventListener('click', function() {
                    confirmRemoveKeyword(keyword);
                });

                keywordSpan.appendChild(closeIcon);
                keywordContainer.appendChild(keywordSpan);
            });

            updateTagsInput();
        }

        function updateTagsInput() {
            tagsInput.value = keywords.join(', ');
            input.placeholder = keywords.length > 0 ? keywords.join(', ') : "Masukkan Check List Task";
        }

        function focusNextKeywordInput() {
            input.focus();
        }

        function saveKeywordToDB(keyword) {
            $.ajax({
                url: "{{ route('account.todolist.addTask') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: "{{ $todolist->id }}",
                    task: keyword
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sukses!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });

                    setTimeout(function() {
                        location.reload(); // Reload halaman otomatis
                    }, 1600);
                },
                error: function(error) {
                    console.error("Error adding task:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Terjadi kesalahan! Silakan coba lagi.',
                    });
                }
            });
        }

        function confirmRemoveKeyword(keyword) {
            Swal.fire({
                title: "Apakah Anda yakin?",
                text: `Task "${keyword}" akan dihapus secara permanen.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, hapus!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    removeKeywordFromDB(keyword);
                }
            });
        }

        function removeKeywordFromDB(keyword) {
            $.ajax({
                url: "{{ route('account.todolist.removeTask') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: "{{ $todolist->id }}",
                    task: keyword
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Dihapus!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });

                    setTimeout(function() {
                        location.reload(); // 🔄 Reload otomatis setelah sukses
                    }, 1600);
                },
                error: function(error) {
                    console.error("Error removing task:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Gagal menghapus! Silakan coba lagi.',
                    });

                    setTimeout(function() {
                        location.reload(); // 🔄 Reload otomatis meskipun gagal
                    }, 1600);
                }
            });
        }
    });
</script>

<!-- coret data yang di checklist -->
<script>
    $(document).ready(function() {
        function updateProgressBar() {
            let totalTasks = $(".task-checkbox").length;
            let checkedTasks = $(".task-checkbox:checked").length;
            let progressPercent = totalTasks > 0 ? Math.round((checkedTasks / totalTasks) * 100) : 0;

            $("#progress-bar").css("width", progressPercent + "%")
                .attr("aria-valuenow", progressPercent)
                .text(progressPercent + "%");
        }

        // Inisialisasi progress bar saat halaman dimuat
        updateProgressBar();

        $(".task-checkbox").change(function() {
            let checkbox = $(this);
            let isChecked = checkbox.is(":checked");
            let taskValue = checkbox.val();
            let todoId = checkbox.data("id");
            let label = checkbox.next("label");

            // Efek coret jika dicentang
            label.toggleClass("text-decoration-line-through text-muted", isChecked);

            // Kirim data via AJAX
            $.ajax({
                url: "{{ route('account.todolist.updateChecklist') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: todoId,
                    task: taskValue,
                    checked: isChecked ? 1 : 0
                },
                success: function(response) {
                    console.log("Checklist updated successfully");

                    // Perbarui progress bar setelah sukses update
                    updateProgressBar();
                },
                error: function(error) {
                    console.error("Error updating checklist", error);
                }
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