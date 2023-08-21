@extends('layouts.account')

@section('title')
Tambah Uang Masuk - UANGKU
@stop

<style>
    .password-input {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
    }
</style>
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
                                        <option value="" disabled selected>Silahkan Pilih</option>
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
                                        <option value="" disabled selected>Silahkan Pilih</option>
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
                                    <div class="password-input">
                                        <input type="password" id="password" class="form-control" name="password" placeholder="Masukkan Password" required>
                                        <i class="fas fa-eye password-toggle" id="password-toggle"></i>
                                    </div>
                                    @error('password')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>NIK</label>
                                    <input type="text" name="nik" class="form-control" value="" placeholder="Masukan NIK" maxlength="30" minlength="5" onkeypress="return event.charCode >= 48 && event.charCode <=57" required>
                                    @error('nik')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>NOMOR REKENING</label>
                                    <input type="text" name="norek" class="form-control" value="" placeholder="Masukan Nomor Rekening" maxlength="30" minlength="5" onkeypress="return event.charCode >= 48 && event.charCode <=57" required>
                                    @error('norek')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>BANK</label>
                                    <select class="form-control bank" name="bank" required>
                                        <option value="" disabled selected>Silahkan Pilih</option>
                                        <option value="002">BRI</option>
                                        <option value="008">BANK MANDIRI</option>
                                        <option value="009">BNI</option>
                                        <option value="200">BANK TABUNGAN NEGARA</option>
                                        <option value="011">BANK DANAMON</option>
                                        <option value="013">BANK PERMATA</option>
                                        <option value="014">BCA</option>
                                        <option value="016">MAYBANK</option>
                                        <option value="019">PANINBANK</option>
                                        <option value="022">CIMB NIAGA</option>
                                        <option value="023">BANK UOB INDONESIA</option>
                                        <option value="028">BANK OCBC NISP</option>
                                        <option value="087">BANK HSBC INDONESIA</option>
                                        <option value="147">BANK MUAMALAT</option>
                                        <option value="153">BANK SINARMAS</option>
                                        <option value="426">BANK MEGA</option>
                                        <option value="441">BANK BUKOPIN</option>
                                        <option value="451">BSI</option>
                                        <option value="484">BANK KEB HANA INDONESIA</option>
                                        <option value="494">BANK RAYA INDONESIA</option>
                                        <option value="506">BANK MEGA SYARIAH</option>
                                        <option value="046">BANK DBS INDONESIA</option>
                                        <option value="947">BANK ALADIN SYARIAH</option>
                                        <option value="950">BANK COMMONWEALTH</option>
                                        <option value="213">BANK BTPN</option>
                                        <option value="490">BANK NEO COMMERCE</option>
                                        <option value="501">BANK DIGITAL BCA</option>
                                        <option value="521">BANK BUKOPIN SYARIAH </option>
                                        <option value="535">SEABANK INDONESIA</option>
                                        <option value="542">BANK JAGO</option>
                                        <option value="567">ALLO BANK</option>
                                        <option value="110">BPD JAWA BARAT</option>
                                        <option value="111">BPD DKI</option>
                                        <option value="112">BPD DAERAH ISTIMEWA YOGYAKARTA</option>
                                        <option value="113">BPD JAWA TENGAH</option>
                                        <option value="114">BPD JAWA TIMUR</option>
                                        <option value="115">BPD JAMBI</option>
                                        <option value="116">BANK ACEH SYARIAH</option>
                                        <option value="117">BPD SUMATERA UTARA</option>
                                        <option value="118">BANK NAGARI</option>
                                        <option value="119">BPD RIAU KEPRI SYARIAH</option>
                                        <option value="120">BPD SUMATERA SELATAN DAN BANGKA BELITUNG</option>
                                        <option value="121">BPD LAMPUNG</option>
                                        <option value="122">BPD KALIMANTAN SELATAN</option>
                                        <option value="123">BPD KALIMANTAN BARAT</option>
                                        <option value="124">BPD KALIMANTAN TIMUR DAN KALIMANTAN UTARA</option>
                                        <option value="125">BPD KALIMANTAN TENGAH</option>
                                        <option value="126">BPD SULAWESI SELATAN DAN SULAWESI BARAT</option>
                                        <option value="127">BPD SULAWESI UTARA DAN GORONTALO</option>
                                        <option value="128">BANK NTB SYARIAH</option>
                                        <option value="129">BPD BALI</option>
                                        <option value="130">BPD NUSA TENGGARA TIMUR</option>
                                        <option value="131">BPD MALUKU DAN MALUKU UTARA</option>
                                        <option value="132">BPD PAPUA</option>
                                        <option value="133">BPD BENGKULU</option>
                                        <option value="134">BPD SULAWESI TENGAH</option>
                                        <option value="135">BPD SULAWESI TENGGARA</option>
                                        <option value="137">BPD BANTEN</option>
                                    </select>
                                    @error('bank')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
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

<!-- show and hide password -->
<script>
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('password-toggle');

    passwordToggle.addEventListener('click', function() {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordToggle.classList.remove('fa-eye');
            passwordToggle.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            passwordToggle.classList.remove('fa-eye-slash');
            passwordToggle.classList.add('fa-eye');
        }
    });
    const passwordInput2 = document.getElementById('password2');
    const passwordToggle2 = document.getElementById('password-toggle2');

    passwordToggle2.addEventListener('click', function() {
        if (passwordInput2.type === 'password') {
            passwordInput2.type = 'text';
            passwordToggle2.classList.remove('fa-eye');
            passwordToggle2.classList.add('fa-eye-slash');
        } else {
            passwordInput2.type = 'password';
            passwordToggle2.classList.remove('fa-eye-slash');
            passwordToggle2.classList.add('fa-eye');
        }
    });
</script>

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