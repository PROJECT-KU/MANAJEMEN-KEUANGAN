@extends('layouts.account')

@section('title')
Update Karir | MIS
@stop

<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>

<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">

<!-- pdf.js library -->
<script src="https://mozilla.github.io/pdf.js/build/pdf.js"></script>
@section('title', 'PDF Viewer')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>KARIR</h1>
        </div>

        <div class="section-body">

            <div class="card">
                <div class="card-header">
                    <h4>UPDATE KARIR</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('karir.update', $karir->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control status" name="status" id="status" required>
                                        <option value="" disabled selected>PILIH STATUS</option>
                                        <option value="Interview" {{ $karir->status == 'Interview' ? 'selected' : '' }}>INTERVIEW</option>
                                        <option value="Diterima" {{ $karir->status == 'Diterima' ? 'selected' : '' }}>DITERIMA</option>
                                        <option value="Ditolak" {{ $karir->status == 'Ditolak' ? 'selected' : '' }}>DITOLAK</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Interview</label>
                                    <input type="datetime-local" name="tanggal_interview" value="{{ $karir->tanggal_interview }}" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for="lokasi_interview">Lokasi Interview</label>
                                <textarea name="lokasi_interview" id="lokasi_interview" placeholder="Lokasi Interview" class="form-control" required>{{ $karir->lokasi_interview }}</textarea>
                            </div>
                        </div>
                </div>
            </div>


            <div class="card">
                <div class="card-header">
                    <h4>DETAIL PENDAFTAR</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <div class="input-group">
                                    <input type="text" name="nama" value="{{ $karir->nama }}" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>No Telp</label>
                                <div class="input-group">
                                    <input type="text" name="telp" value="{{ $karir->telp }}" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Email</label>
                                <div class="input-group">
                                    <input type="text" name="email" value="{{ $karir->email }}" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pendidikan Terakhir</label>
                                <div class="input-group">
                                    <input type="text" name="pendidikan" value="{{ $karir->pendidikan }}" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Posisi yang Dilamar</label>
                                <div class="input-group">
                                    <input type="text" name="posisi" value="{{ $karir->posisi }}" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="desc">Deskripsi Diri</label>
                            <textarea name="desc" id="desc" placeholder="Jelaskan diri kamu" class="form-control" readonly>{{ $karir->desc }}</textarea>
                        </div>
                    </div>

                    <div class="row mb-5 justify-content-center">
                        <!-- CV File -->
                        @if($karir->cv)
                        <div class="col-md-4 col-sm-6">
                            <div class="form-group">
                                <div class="card d-flex flex-column shadow-lg" style="height: 100%;">
                                    <h5 class="card-title text-center">Berkas CV</h5>
                                    <div class="card-img-top text-center">
                                        <i class="far fa-file-pdf" style="font-size: 5em;"></i>
                                    </div>
                                    <input type="file" name="cv" id="cv" value="{{ asset('karir/' . $karir->cv) }}" class="form-control" hidden>
                                    <a href="{{ asset('karir/' . $karir->cv) }}" class="btn btn-primary w-100 mt-auto">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Surat Lamaran File -->
                        @if($karir->lamaran)
                        <div class="col-md-4 col-sm-6">
                            <div class="form-group">
                                <div class="card d-flex flex-column shadow-lg" style="height: 100%;">
                                    <h5 class="card-title text-center">Berkas Surat Lamaran</h5>
                                    <div class="card-img-top text-center">
                                        <i class="far fa-file-pdf" style="font-size: 5em;"></i>
                                    </div>
                                    <input type="file" name="lamaran" id="lamaran" value="{{ asset('karir/' . $karir->lamaran) }}" class="form-control" hidden>
                                    <a href="{{ asset('karir/' . $karir->lamaran) }}" class="btn btn-primary w-100 mt-auto">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Berkas Lainnya -->
                        @if($karir->lainnya)
                        <div class="col-md-4 col-sm-6">
                            <div class="form-group">
                                <div class="card d-flex flex-column shadow-lg" style="height: 100%;">
                                    <h5 class="card-title text-center">Berkas Lainnya</h5>
                                    <div class="card-img-top text-center">
                                        <i class="far fa-file-pdf" style="font-size: 5em;"></i>
                                    </div>
                                    <input type="file" name="lainnya" id="lainnya" value="{{ asset('karir/' . $karir->lainnya) }}" class="form-control" hidden>
                                    <a href="{{ asset('karir/' . $karir->lainnya) }}" class="btn btn-primary w-100 mt-auto">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="d-flex">
                        <button class="btn btn-primary btn-submit mr-1 rounded-pill" type="submit" style="width: 50%; font-size: 14px;">
                            <i class="fa fa-paper-plane"></i> SIMPAN
                        </button>
                        <a href="{{ route('karir.list') }}" class="btn btn-info btn-reset rounded-pill" type="reset" style="width: 50%; font-size: 14px;">
                            <i class="fa fa-redo"></i> KEMBALI
                        </a>
                    </div>

                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<!--================== view upload image ==================-->
<!-- ... (other script imports) ... -->

<script>
    // Function to open PDF in a new tab
    function openPDFViewer(pdfPath) {
        // Open a new tab/window with the PDF path
        window.open(pdfPath, '_blank');
    }

    // Event handler for 'View PDF' button
    $('.btn-view-pdf').click(function(e) {
        e.preventDefault(); // Prevent the default behavior of the link
        const pdfPath = $(this).attr('data-pdf-path'); // Get the PDF path from data attribute
        openPDFViewer(pdfPath);
    });
</script>

<!-- ... (other script imports) ... -->


<!--================== end ==================-->



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

        }, 500);
    })
</script>

@stop