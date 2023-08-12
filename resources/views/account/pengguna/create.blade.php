@extends('layouts.account')

@section('title')
Tambah Uang Masuk - UANGKU
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>PENGGUNA</h1>
        </div>

        <div class="section-body">

            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-money-check-alt"></i> TAMBAH PENGGUNA</h4>
                </div>

                <div class="card-body">

                    <form action="{{ route('account.pengguna.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama</label>
                                    <input type="text" name="full_name" placeholder="Masukkan Nama" class="form-control" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z ]/i.test(event.key)" required>

                                    @error('full_name')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" name="email" placeholder="Masukan Email" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>

                                    @error('Email')
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
                                    <label>Nama Perusahaan</label>
                                    <input type="text" name="company" placeholder="Masukkan Nama" class="form-control" maxlength="30" minlength="5" onkeypress="return/[A-Z]/i.test(event.key)" style="text-transform:uppercase" required>

                                    @error('company')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>No Telp</label>
                                    <input type="text" name="telp" placeholder="Masukkan No Telp" class="form-control" maxlength="14" minlength="8" onkeypress="return event.charCode >= 48 && event.charCode <=57" required>

                                    @error('telp')
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
                                    <label>Level</label>
                                    <select class="form-control" name="level" required>
                                        <option value="">Silahkan Pilih</option>
                                        <option value="admin">Admin</option>
                                        <option value="user">User</option>
                                        <option value="manager">Manager</option>
                                        <option value="staff">Staff</option>
                                        <option value="karyawan">Karyawan</option>
                                    </select>

                                    @error('level')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jenis</label>
                                    <select class="form-control" name="jenis" required>
                                        <option value="">Silahkan Pilih</option>
                                        <option value="bisnis">Bisnis</option>
                                        <option value="penyewaan">Penyewaan</option>
                                        <option value="kasir">Kasir</option>
                                        <option value="perorangan">Perorangan</option>
                                    </select>

                                    @error('jenis')
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
                                    <label>Username</label>
                                    <input type="text" name="username" class="form-control" value="" placeholder="Masukan Username" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9 ]/i.test(event.key)" required>

                                    @error('username')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" class="form-control" name="password" rows="6" placeholder="Masukkan Password" required>

                                    @error('password')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <!--<div class="col-md-6">
                                <div class="form-group">
                                    <label>Foto Profil</label>
                                    <div class="input-group">
                                        <div class="input-group-append">
                                            <img id="imagePreview" src="#" alt="Preview Foto" style="max-width: 200px; max-height: 200px;">
                                        </div>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="avatar" name="avatar" onchange="previewImage(event)">
                                            <label class="custom-file-label" for="avatar">Pilih Gambar</label>
                                        </div>
                                    </div>
                                    <div class="mt-2">

                                    </div>
                                    @error('avatar')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>-->
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="checkbox" name="email_verified_at" style="margin-top: 5px;">
                                    <label>Verifikasi</label>
                                    @error('email_verified_at')
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
<script>
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function() {
            var output = document.getElementById('imagePreview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
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

        }, 500);
    })
</script>
@stop