@extends('layouts.account')

@section('title')
Update Data Refrensi Paper | MIS
@stop

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
            <h1>UPDATE DATA REFRENSI PAPER</h1>
        </div>

        <div class="section-body">
            <form action="{{ route('account.refrensi-paper.update', $datas->id) }}" method="POST" enctype="multipart/form-data" id="myForm">
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
                                    <label>Nama Author</label>
                                    <div class="input-group" id="keyword-container">
                                        <!-- Tempat untuk menampilkan kata kunci -->
                                    </div>
                                    <input id="kata_kunci_input" type="text" value="{{ $datas->nama_author }}" name="nama_author" placeholder="Masukkan Nama Author" class="form-control" onkeypress="return/[a-zA-Z ]/i.test(event.key)">
                                    <p class="mt-2" style="color: red;"><i class="fas fa-info-circle"></i> Tekan Enter di keyboard setelah memasukan nama author</p>
                                    @error('kata_kunci')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                    <!-- Elemen tersembunyi untuk menyimpan kata kunci sebagai tag -->
                                    <input type="hidden" value="{{ $datas->nama_author }}" id="kata_kunci_tags" name="kata_kunci_tags">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Journal</label>
                                    <div class="input-group">
                                        <input type="text" name="nama_journal" value="{{ $datas->nama_journal }}" placeholder="Masukkan Nama Journal" class="form-control" required>
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
                                        <input type="number" name="quartile_journal" value="{{ $datas->quartile_journal }}" placeholder="Masukkan Quartile Jurnal" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>APC</label>
                                    <div class="input-group">
                                        <input type="text" name="apc" value="{{ $datas->apc }}" placeholder="Masukkan APC Journal" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type Journal</label>
                                    <div class="input-group">
                                        <select name="type" class="form-control" required>
                                            <option value="" disabled selected>Pilih Type Journal</option>
                                            <option value="Open Access" {{ $datas->type == 'Open Access' ? 'selected' : '' }}>Open Access</option>
                                            <option value="Close Access" {{ $datas->type == 'Close Access' ? 'selected' : '' }}>Close Access</option>
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
                                        <input type="text" name="judul_paper" value="{{ $datas->judul_paper }}" placeholder="Masukkan Judul Paper" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>DOI</label>
                                    <div class="input-group">
                                        <input type="text" name="doi" value="{{ $datas->doi }}" placeholder="Masukkan DOI Paper" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card card-outline card-info">
                                    <div class="card-body pad">
                                        <div class="mb-3">
                                            <textarea class="textarea" name="subjek_area_journal" id="subjek_area_journal" placeholder="Subjek Area Journal" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;">{{ $datas->subjek_area_journal }}</textarea>
                                        </div>
                                    </div>
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
            title: 'Apakah Kamu Sudah Menekan Enter Di Keyboard Pada Input Nama Author ?',
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
                var inputKeywords = input.value.trim().split(',');
                inputKeywords.forEach(function(keyword) {
                    addKeyword(keyword.trim());
                });
                input.value = '';
            }
        });

        function addKeyword(keyword) {
            if (keyword !== '') {
                keywords.push(keyword); // Menyimpan kata kunci ke dalam array
                renderKeywords(); // Memperbarui tampilan kata kunci
                updateTagsInput(); // Memperbarui nilai input tersembunyi
            }
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