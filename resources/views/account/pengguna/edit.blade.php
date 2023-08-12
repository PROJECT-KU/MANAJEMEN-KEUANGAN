@extends('layouts.account')

@section('title')
Edit Uang Masuk - UANGKU
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
                    <h4><i class="fas fa-money-check-alt"></i> EDIT PENGGUNA</h4>
                </div>

                <div class="card-body">

                    <form action="{{ route('account.pengguna.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama</label>
                                    <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $user->full_name) }}" class="form-control currency" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z ]/i.test(event.key)">

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
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)">

                                    @error('email')
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
                                    <input type="text" name="company" class="form-control" value="{{ old('company', $user->company) }}" maxlength="30" minlength="5" onkeypress="return/[A-Z]/i.test(event.key)" style="text-transform:uppercase">

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
                                    <input type="text" name="telp" class="form-control" value="{{ old('telp', $user->telp) }}" maxlength="14" minlength="8" onkeypress="return event.charCode >= 48 && event.charCode <=57">

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
                                    <select class="form-control" name="level">
                                        <option value="admin" {{ $user->level == 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="user" {{ $user->level == 'user' ? 'selected' : '' }}>User</option>
                                        <option value="manager" {{ $user->level == 'manager' ? 'selected' : '' }}>Manager</option>
                                        <option value="staff" {{ $user->level == 'staff' ? 'selected' : '' }}>Staff</option>
                                        <option value="karyawan" {{ $user->level == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
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
                                    <select class="form-control" name="jenis">
                                        <option value="bisnis" {{ $user->jenis == 'bisnis' ? 'selected' : '' }}>Bisnis</option>
                                        <option value="penyewaan" {{ $user->jenis == 'penyewaan' ? 'selected' : '' }}>Penyewaan</option>
                                        <option value="kasir" {{ $user->jenis == 'kasir' ? 'selected' : '' }}>kasir</option>
                                        <option value="perorangan" {{ $user->jenis == 'perorangan' ? 'selected' : '' }}>Perorangan</option>
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
                                    <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9 ]/i.test(event.key)">

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
                                    <input type="password" class="form-control" name="password"></input>

                                    @error('password')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @if(Auth::user()->level === 'admin')
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Akun Dibikin Pada Tanggal</label>
                                <input class="form-control" name="notif" placeholder="" value="{{ old('created_at', $user->created_at->format('d-m-Y h:i')) }}" readonly>
                                @error('created_at')
                                <div class=" invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>


                        <hr style="border-radius: 2px; border-width: 3px;">
                        <div style="text-align: center;">
                            <p><span style="color: red;">*</span>Notifikasi Untuk Masa Sewa Yang Akan Habis<span style="color: red;">*</span></p>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Judul</label>
                                    <textarea class="form-control" name="title" rows="6" placeholder="Masukkan Keterangan">{{ old('title', $user->title) }}</textarea>
                                    @error('title')
                                    <div class=" invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Berakhir Sewa</label>
                                    <input type="date" class="form-control" name="tenggat" value="{{ old('tenggat', $user->tenggat) }}"></input>

                                    @error('tenggat')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Pesan</label>
                                <textarea class="form-control" name="notif" rows="6" placeholder="Masukkan Keterangan">{{ old('notif', $user->notif) }}</textarea>
                                @error('notif')
                                <div class=" invalid-feedback" style="display: block">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                        @endif
                        <div class="row">
                            <div class="col-2">
                                <div class="form-group">
                                    <input type="checkbox" value="1" name="email_verified_at" style="margin-top: 5px;" {{ $user->email_verified_at ? 'checked' : '' }}>
                                    <label>Verifikasi</label>
                                    @error('email_verified_at')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <input type="checkbox" value="1" name="status" style="margin-top: 5px;" {{ $user->status == 'on' ? 'checked' : '' }}>
                                    <label>Status</label>
                                    @error('status')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> UPDATE</button>
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
        $('.datetimepicker').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD hh:mm'
            },
            singleDatePicker: true,
            timePicker: true,
            timePicker24Hour: true,
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