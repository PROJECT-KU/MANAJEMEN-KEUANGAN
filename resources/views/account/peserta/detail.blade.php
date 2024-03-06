@extends('layouts.account')

@section('title')
Detail Laporan Peserta | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>DETAIL LAPORAN PESERTA</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <!-- <div class="card-header">
                    <h4><i class="fas fa-user"></i> DETAIL LAPORAN PESERTA</h4>
                </div> -->

                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Email</label>
                                    <div class="input-group">
                                        <input type="text" name="email" value="{{ $peserta->email }}" placeholder="Masukkan Email Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Peserta</label>
                                    <div class="input-group">
                                        <input type="text" name="nama" value="{{ $peserta->nama }}" placeholder="Masukkan Nama Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Afiliasi</label>
                                    <div class="input-group">
                                        <input type="text" name="afiliasi" value="{{ $peserta->afiliasi }}" placeholder="Masukkan Afiliasi Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Judul</label>
                                    <div class="input-group">
                                        <input type="text" name="judul" value="{{ $peserta->judul }}" placeholder="Masukkan judul Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Journal</label>
                                    <div class="input-group">
                                        <input type="text" name="jurnal" value="{{ $peserta->jurnal }}" placeholder="Masukkan jurnal Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Refrensi</label>
                                    <div class="input-group">
                                        <input type="text" name="refrensi" value="{{ $peserta->refrensi }}" placeholder="Masukkan refrensi Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Digital Writing</label>
                                    <div class="input-group">
                                        <input type="text" name="digital_writing" value="{{ $peserta->digital_writing }}" placeholder="Masukkan Digital Writing Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mendeley</label>
                                    <div class="input-group">
                                        <input type="text" name="mendeley" value="{{ $peserta->mendeley }}" placeholder="Masukkan mendeley Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Persentase Penyelesaian</label>
                                    <div class="input-group">
                                        <input type="text" name="persentase_penyelesaian" value="{{ $peserta->persentase_penyelesaian }}" placeholder="Masukkan Persentase Penyelesaian Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Submit</label>
                                    <div class="input-group">
                                        <input type="text" name="submit" value="{{ $peserta->submit }}" placeholder="Masukkan submit Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Target</label>
                                    <div class="input-group">
                                        <input type="datetime-local" name="target" value="{{ $peserta->target }}" placeholder="Masukkan target Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Scopus Camp</label>
                                    <div class="input-group">
                                        <input type="text" name="scopus_camp" value="{{ $peserta->scopus_camp }}" placeholder="Masukkan Scopus Camp Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Materi</label>
                                    <div class="input-group">
                                        <input type="text" name="materi" value="{{ $peserta->materi }}" placeholder="Masukkan Materi Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Makanan</label>
                                    <div class="input-group">
                                        <input type="text" name="makanan" value="{{ $peserta->makanan }}" placeholder="Masukkan Makanan Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Pelayanan</label>
                                    <div class="input-group">
                                        <input type="text" name="pelayanan" value="{{ $peserta->pelayanan }}" placeholder="Masukkan Pelayanan Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tempat</label>
                                    <div class="input-group">
                                        <input type="text" name="tempat" value="{{ $peserta->tempat }}" placeholder="Masukkan Tempat Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Terfavorit</label>
                                    <div class="input-group">
                                        <input type="text" name="terfavorit" value="{{ $peserta->terfavorit }}" placeholder="Masukkan Terfavorit Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Terbaik</label>
                                    <div class="input-group">
                                        <input type="text" name="terbaik" value="{{ $peserta->terbaik }}" placeholder="Masukkan Terbaik Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Terlucu</label>
                                    <div class="input-group">
                                        <input type="text" name="terlucu" value="{{ $peserta->terlucu }}" placeholder="Masukkan Terlucu Peserta" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Kritik & Saran</label>
                                    <div class="input-group">
                                        <textarea name="kritik" placeholder="Masukkan Kritik Peserta" class="form-control" readonly>{{ $peserta->kritik }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('account.peserta.list') }}" class="btn btn-info">
                            <i class="fa fa-undo"></i> KEMBALI
                        </a>

                    </form>

                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!--================== botton loader ==================-->
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
<!--================== end ==================-->

@stop