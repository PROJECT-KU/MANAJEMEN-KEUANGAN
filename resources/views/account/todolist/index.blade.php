@extends('layouts.account')
@extends('layouts.loader')

@section('title')
TO DO List | MIS
@stop

<meta name="csrf-token" content="{{ csrf_token() }}">

<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>DATA TO DO LIST</h1>
        </div>

        <div class="row">
            <div class="col-12 mt-3">
                <div class="form-group text-center">
                    @if($user->level == 'manager')
                    <div class="input-group mb-3">
                        <a href="{{ route('account.todolist.create') }}" class="btn btn-primary btn-block">
                            <i class="fa fa-plus-circle"></i> TAMBAH DATA TO DO LIST
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="section-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex flex-nowrap overflow-auto">
                            @php
                            $columns = [
                            'DatasAssignTask' => 'Assign Task',
                            'DatasInProgress' => 'In Progress',
                            'DatasTesting' => 'Testing',
                            'DatasRevisi' => 'Revisi',
                            'DatasCompleted' => 'Completed',
                            'DatasMelebihiDeadline' => 'Melebihi Deadline' // ✅ Hanya status valid yang ditampilkan
                            ];
                            @endphp

                            @foreach ($columns as $var => $title)
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-4 flex-shrink-0" style="min-width: 300px;">
                                <div class="card h-100 shadow">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted">{{ strtoupper($title) }}</h6>
                                        <hr>
                                        <div id="{{ strtolower(str_replace('Datas', '', $var)) }}" class="sortable-list">
                                            @foreach ($$var as $task)
                                            <div class="card mb-3 shadow" data-id="{{ $task->id }}" data-deadline="{{ $task->tanggal_deadline }}">
                                                <div class="card-body p-2">
                                                    <div class="d-flex justify-content-between">
                                                        <span>{{ $task->judul_task }}</span>
                                                        <span class="badge badge-{{ $task->prioritas_task == 'Low' ? 'success' : ($task->prioritas_task == 'Medium' ? 'warning' : 'danger') }}">
                                                            {{ ucfirst($task->prioritas_task) }}
                                                        </span>
                                                    </div>
                                                    <div class="d-flex justify-content-between text-muted small mt-2">
                                                        <span>{{ Str::words(strip_tags($task->deskripsi), 5, '...') }}</span>
                                                        <span class="badge badge-secondary">{{ $task->status }}</span>
                                                    </div>
                                                    @if($task->tasklist != null)
                                                    <div class="progress mt-2">
                                                        <div class="progress-bar bg-success" role="progressbar"
                                                            style="width: {{ $task->progressPercent }}%;" aria-valuenow="{{ $task->progressPercent }}"
                                                            aria-valuemin="0" aria-valuemax="100">
                                                            {{ $task->progressPercent }}%
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <div class="d-flex text-muted small mt-2">
                                                        <div class="mr-3"><i class="far fa-id-badge"></i> {{ $task->id_task }}</div>
                                                        <div>
                                                            <i class="fas fa-user"></i>
                                                            {{ $task->full_name ?? 'Tidak Diketahui' }}
                                                            @if($task->full_name_kedua)
                                                            , {{ $task->full_name_kedua }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($user->level == 'manager')
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('account.todolist.edit', $task->id) }}" class="btn btn-info flex-grow-1">
                                                        <i class="fa fa-pen"></i> Edit Task
                                                    </a>
                                                    <button onclick="Delete('{{ $task->id }}')" class="btn btn-danger">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                                @else
                                                <a href="{{ route('account.todolist.edit', $task->id) }}" class="btn btn-info w-100">
                                                    <i class="fa fa-pen"></i> Edit Task
                                                </a>
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!--================== PROGRESS BAR CHECKLIST TASK ==================-->
<script>
    $(document).ready(function() {
        function updateProgressBar(todoId) {
            let totalTasks = $(`.task-checkbox[data-id='${todoId}']`).length;
            let checkedTasks = $(`.task-checkbox[data-id='${todoId}']:checked`).length;
            let progressPercent = totalTasks > 0 ? Math.round((checkedTasks / totalTasks) * 100) : 0;

            console.log("Total Tasks:", totalTasks);
            console.log("Checked Tasks:", checkedTasks);
            console.log("Progress:", progressPercent);

            $(`#progress-bar-${todoId}`).css("width", progressPercent + "%")
                .attr("aria-valuenow", progressPercent)
                .text(progressPercent + "%");
        }

        // Inisialisasi progress bar saat halaman dimuat
        $(".task-checkbox").each(function() {
            updateProgressBar($(this).data("id"));
        });

        $(".task-checkbox").change(function() {
            let checkbox = $(this);
            let isChecked = checkbox.is(":checked");
            let taskValue = checkbox.val();
            let todoId = checkbox.data("id");
            let label = checkbox.next("label");

            label.toggleClass("text-decoration-line-through text-muted", isChecked);

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
                    updateProgressBar(todoId);
                },
                error: function(error) {
                    console.error("Error updating checklist", error);
                }
            });
        });
    });
</script>
<!--================== END ==================-->

<!--================== MEMPERBARUI STATUS CARD DENGAN DRAG  ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var lists = document.querySelectorAll('.sortable-list');
        var today = new Date().toISOString().split('T')[0];

        lists.forEach(list => {
            new Sortable(list, {
                group: 'shared',
                animation: 150,
                onEnd: function(event) {
                    var taskId = event.item.getAttribute('data-id');
                    var newStatus = event.to.getAttribute('id');

                    var statusMapping = {
                        'assigntask': 'Assign Task',
                        'inprogress': 'In Progress',
                        'testing': 'Testing',
                        'revisi': 'Revisi',
                        'completed': 'Completed',
                        'melebihideadline': 'Melebihi Deadline'
                    };

                    // Mencegah perubahan status dari "Completed" ke "Melebihi Deadline"
                    if (statusMapping[newStatus] === 'Melebihi Deadline' && event.item.querySelector('.badge-secondary').textContent.trim() === 'Completed') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak Dapat Memindahkan!',
                            text: 'Tugas yang sudah selesai tidak dapat dipindahkan ke "Melebihi Deadline".',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        event.from.appendChild(event.item); // Kembalikan item ke tempat awal
                        return;
                    }

                    fetch('{{ route("account.updatestatusoto.updatestatus") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                id: taskId,
                                status: statusMapping[newStatus]
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Status berhasil diubah',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: 'Status gagal diubah',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                        })
                        .catch(() => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Terjadi kesalahan, halaman akan dimuat ulang!',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        });
                }
            });
        });
    });
</script>
<!--================== END ==================-->
<!--================== SWEET ALERT DELETE ==================-->
<script>
    function Delete(id) {
        var token = $("meta[name='csrf-token']").attr("content");

        swal({
            title: "APAKAH KAMU YAKIN?",
            text: "INGIN MENGHAPUS DATA INI!",
            icon: "warning",
            buttons: {
                cancel: {
                    text: "TIDAK",
                    value: null,
                    visible: true,
                    className: "",
                    closeModal: true,
                },
                confirm: {
                    text: "YA",
                    value: true,
                    visible: true,
                    className: "",
                    closeModal: true
                }
            },
            dangerMode: true,
        }).then(function(isConfirm) {
            if (isConfirm) {
                // ajax delete
                $.ajax({
                    url: "/account/todolist/data/delete/" + id,
                    data: {
                        "_token": token,
                        "_method": "DELETE"
                    },
                    type: 'POST',
                    success: function(response) {
                        if (response.status === "success") {
                            swal({
                                title: 'BERHASIL!',
                                text: response.message,
                                icon: 'success',
                                timer: 1000,
                                buttons: false,
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            swal({
                                title: 'GAGAL!',
                                text: response.message,
                                icon: 'error',
                                timer: 1000,
                                buttons: false,
                            }).then(function() {
                                location.reload();
                            });
                        }
                    }
                });
            }
        });
    }
</script>
<!--================== END ==================-->
@stop