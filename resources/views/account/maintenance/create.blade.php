@extends('layouts.account')

@section('title')
Tambah Pengguna | MANAGEMENT
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>MAINTENANCE</h1>
        </div>

        <div class="section-body">

            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-user-plus"></i> TAMBAH MAINTENANCE</h4>
                </div>

                <div class="card-body">

                    <form action="{{ route('account.maintenance.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>TITLE</label>
                                    <input type="text" id="title" name="title" placeholder="Masukkan Judul Maintenance" class="form-control" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z !., ]/i.test(event.key)" required>

                                    @error('title')
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
                                    <label>START MAINTENANCE</label>
                                    <input type="text" id="start_date" name="start_date" placeholder="Masukkan Mulai Maintenance" class="form-control datetimepicker" required>

                                    @error('start_date')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>END MAINTENANCE</label>
                                    <input type="text" id="end_date" name="end_date" placeholder="Masukkan Berakhir Maintenance" class="form-control datetimepicker" required>

                                    @error('end_date')
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
                                    <label>GAMBAR</label>
                                    <div class="input-group">
                                        <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" capture="camera">
                                    </div>
                                    @error('gambar')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="card" style="width: 18rem;">
                                        <img id="image-preview" class="card-img-top" src="#" alt="Preview Image">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>NOTE</label>
                                    <input type="text" id="note" name="note" class="form-control" style="height: 100px;" placeholder="Masukan Pesan Maintenance" required>

                                    @error('note')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
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

<!-- datepicker -->
<script>
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
    if ($(".datetimepicker").length) {
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm',
            defaultDate: new Date(),
            useCurrent: true,
            autoclose: true,
            todayButton: true,
            todayHighlight: true,
            showMeridian: false
        });
    }

    var cleaveC = new Cleave('.currency', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });

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
            $("#full_name").val('');
            $("#email").val('');
            $("#company").val('');
            $("#telp").val('');
            $("#level").val('');
            $("#jenis").val('');
            $("#password").val('');
            $("#nik").val('');
            $("#norek").val('');
            $("#bank").val('');
        }, 500);
    })
</script>
@stop