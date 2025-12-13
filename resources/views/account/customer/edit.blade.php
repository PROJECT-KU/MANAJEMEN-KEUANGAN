@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Update Data Customer | MIS
@stop

@section('content')
<!--================== ICON VERIFIKASI EMAIL ==================-->
<style>
    .input-container {
        position: relative;
    }

    .input-container input {
        padding-right: 2.5rem;
        /* Adjust space for the icon */
    }

    .input-container .icon-container {
        position: absolute;
        right: 0.5rem;
        top: 75%;
        transform: translateY(-50%);
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        /* Size of the badge */
        height: 20px;
        /* Size of the badge */
        background-color: lightblue;
        /* Badge color */
        border-radius: 50%;
        /* Circular badge */
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.2);
        /* Optional shadow */
    }

    .icon-container .icon {
        font-size: 1rem;
        /* Icon size */
        color: blue;
        /* Color for the checkmark icon */
    }
</style>
<!--================== END ==================-->

<!--================== RESET PASSWORD ==================-->
<style>
    /* Password and Confirmation password group */
    .password-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-toggle {
        position: absolute;
        right: 10px;
        cursor: pointer;
        z-index: 1;
        color: #000;
        font-size: 24px;
    }

    /* Danger color */
    .border-danger {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, .25) !important;
    }

    /* Responsive adjustment */
    .form-row {
        display: flex;
        gap: 10px;
    }

    .form-group {
        flex: 1;
    }

    .form-group input {
        width: 100%;
        box-sizing: border-box;
        padding-right: 40px;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
    }
</style>
<!--================== END ==================-->

<!--================== POPUP ==================-->
<style>
    /* Custom Popup Styles */
    .custom-popup {
        display: none;
        /* Hidden by default */
        position: fixed;
        z-index: 9999;
        /* Sit on top */
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        /* Enable scroll if needed */
        background-color: rgba(0, 0, 0, 0.5);
        /* Black background with opacity */
    }

    .custom-popup-content {
        background-color: #fefefe;
        margin: 15% auto;
        /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        /* Could be more or less, depending on screen size */
        max-width: 500px;
        /* Optional: limits width */
        border-radius: 8px;
        /* Optional: rounded corners */
    }

    .custom-popup-title {
        margin: 0;
        padding-bottom: 10px;
    }

    .custom-popup-close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .custom-popup-close:hover,
    .custom-popup-close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
</style>
<!--================== END ==================-->

<div class="main-content" style="font-size: 12px;">
    <section class="section">
        <div class="section-header">
            <h1>PROFIL CUSTOMER</h1>
        </div>

        <div class="section-body">

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-3">

                            <!--================== FOTO PROFIL ==================-->
                            <div class="card card-primary card-outline">
                                <div class="card-body box-profile">
                                    <div class="text-center">
                                        @if ($user->gambar == null)
                                        <img alt="User profile picture" id="image-preview" src="{{ asset('assets/img/profil/no-image.jpg') }}" class="profile-user-img img-fluid img-circle" style="width: 128px; height: 128px; border-radius: 50%;">
                                        @else
                                        <img id="image-preview" class="profile-user-img img-fluid img-circle" src="{{ asset('assets/img/profil/' . $user->gambar) }}" alt="User profile picture" style="width: 128px; height: 128px; border-radius: 50%;">
                                        @endif
                                    </div>

                                    <h3 class="profile-username text-center">{{ $user->full_name }}</h3>
                                    <p class="text-muted text-center">{{ $user->level }}</p>

                                    <form action="{{ route('account.pengguna.update.updatePhoto', $user->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-group">
                                            <label for="foto">Update Foto</label>
                                            <input type="file" name="gambar" class="form-control-file" id="foto" onchange="toggleSubmitButton()">
                                        </div>
                                        <button type="submit" id="updateButton" class="btn btn-primary btn-block" disabled><b>Update Foto</b></button>
                                    </form>
                                </div>
                            </div>
                            <!--================== END ==================-->

                            <!--================== DATA PROFIL ==================-->
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Tentang Saya</h3>
                                </div>
                                <div class="card-body">
                                    <!-- Email Section -->
                                    <strong><i class="fas fa-envelope-open mr-1"></i> Email</strong>
                                    <p class="text-muted">
                                        {{ $user->email }}
                                        @if (Auth::user()->level === 'manager' || Auth::user()->level === 'admin')
                                        <button class="btn btn-sm btn-warning float-right" id="openPopupButtonEmail" style="width: 24px; height:24px">
                                            <i class="fas fa-pencil-alt" style="font-size:15px"></i>
                                        </button>
                                        @endif
                                    </p>
                                    <hr>

                                    <!-- No Telp Section -->
                                    <strong><i class="fas fa-phone mr-1"></i> No Telp</strong>
                                    <p class="text-muted">
                                        {{ $user->telp }}
                                        @if ($user->email_verified_at == null)
                                        <button id="edit-telp-button" class="btn btn-sm btn-warning float-right" data-action="verify-email" style="width: 24px; height:24px">
                                            <i class="fas fa-pencil-alt" style="font-size:15px"></i>
                                        </button>
                                        @else
                                        <button class="btn btn-sm btn-warning float-right" id="openPopupButtonTelp" style="width: 24px; height:24px">
                                            <i class="fas fa-pencil-alt" style="font-size:15px"></i>
                                        </button>
                                        @endif
                                    </p>
                                    <hr>

                                </div>
                            </div>
                            <!--================== END ==================-->
                        </div>

                        <!--================== MODAL DATA PROFIL ==================-->
                        <!-- Modal for updating email -->
                        <div id="customPopupEmail" class="custom-popup">
                            <div class="custom-popup-content">
                                <span class="custom-popup-close" id="customPopupCloseEmail">&times;</span>
                                <h5 class="custom-popup-title">Email</h5>
                                <form action="{{ route('account.pengguna.update.datadiri', $user->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Masukkan Email Terbaru</label>
                                        <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 btn-submit"><i class="fa fa-paper-plane"></i> SIMPAN</button>
                                </form>
                            </div>
                        </div>

                        <!-- Modal for updating jobdesk -->
                        <div id="customPopupJobdesk" class="custom-popup">
                            <div class="custom-popup-content">
                                <span class="custom-popup-close" id="customPopupCloseJobdesk">&times;</span>
                                <h5 class="custom-popup-title">Job Desk</h5>
                                <form action="{{ route('account.pengguna.update.datadiri', $user->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="jobdesk" class="form-label">Masukkan Job Desk Anda</label>
                                        <input type="text" class="form-control" id="jobdesk" name="jobdesk" value="{{ $user->jobdesk }}" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 btn-submit"><i class="fa fa-paper-plane"></i> SIMPAN</button>
                                </form>
                            </div>
                        </div>

                        <!-- Modal for updating telp -->
                        <div id="customPopupTelp" class="custom-popup">
                            <div class="custom-popup-content">
                                <span class="custom-popup-close" id="customPopupCloseTelp">&times;</span>
                                <h5 class="custom-popup-title">No Telp</h5>
                                <form action="{{ route('account.pengguna.update.datadiri', $user->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="telp" class="form-label">Masukkan No Telp Anda</label>
                                        <input type="text" class="form-control" id="telp" name="telp" value="{{ $user->telp }}" maxlength="20" minlength="8" onkeypress="return event.charCode >= 48 && event.charCode <=57" oninput="formatPhoneNumber(this)" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 btn-submit"><i class="fa fa-paper-plane"></i> SIMPAN</button>
                                </form>
                            </div>
                        </div>
                        <!--================== END ==================-->

                        <div class="col-md-9">
                            <div class="card">
                                <div class="card-header p-2">
                                    <ul class="nav nav-pills">
                                        <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Data Profil</a></li>
                                        <!-- <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Reset Password</a></li> -->
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content">

                                        <!--================== TAB DATA DIRI ==================-->
                                        <div class="active tab-pane" id="activity" style="margin-top: -20px;">

                                            <!-- NOTIF EMAIL BELUM DI VERIFIKASI -->
                                            @if ($user->email_verified_at == null)
                                            <div class="alert alert-warning" role="alert">
                                                Email anda <b>Belum Terverifikasi</b>, Silahkan verifikasi sekarang untuk melengkapi data diri.
                                            </div>
                                            @endif
                                            <!-- END -->

                                            <div class="post">
                                                <!-- <div class="user-block d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        @if ($user->gambar == null)
                                                        <img class="img-circle img-bordered-sm" src="{{ asset('assets/img/profil/no-image.jpg') }}" alt="user image" style="width: 50px; height: 50px; border-radius: 50%;">
                                                        @else
                                                        <img class="img-circle img-bordered-sm" src="{{ asset('assets/img/profil/' . $user->gambar) }}" alt="user image" style="width: 50px; height: 50px; border-radius: 50%;">
                                                        @endif
                                                        <span class="username ml-2">
                                                            <span>{{ $user->full_name }}</span>
                                                        </span>
                                                    </div>
                                                </div> -->

                                                <form id="verification-form" action="{{ route('account.pengguna.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="row mt-3">
                                                        <div class="col-md-6">
                                                            <label>Nama Lengkap</label>
                                                            <input class="form-control form-control" type="text" id="full_name" name="full_name" value="{{ $user->full_name }}" placeholder="Nama">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Username</label>
                                                            <input class="form-control form-control" type="text" id="username" name="username" placeholder="Username" value="{{ $user->username }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 mt-4" id="submit-container" style="display: none;">
                                                        <button type="submit" class="btn btn-primary btn-submit rounded-pill" style="width: 100%;"><i class="fa fa-paper-plane"></i> UPDATE DATA</button>
                                                    </div>
                                                </form>

                                                <div class="row mt-3">
                                                    <!-- BUTTON VERIFIKASI EMAIL -->
                                                    <div class="col-md-12 col-lg-12">
                                                        <form id="verify-email-form" action="{{ route('account.pengguna.update.vertifikasiemail', $user->id) }}" method="POST">
                                                            @csrf
                                                            <div class="row">
                                                                @if($user->email_verified_at)
                                                                <div class="col-md-12 d-flex align-items-center mb-3">
                                                                    <div class="w-100 input-container">
                                                                        <label>Email</label>
                                                                        <input class="form-control form-control" type="text" value="{{ $user->email }}" readonly>
                                                                        <div class="icon-container">
                                                                            <i class="fas fa-check icon"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @else
                                                                <div class="col-md-8 d-flex align-items-center mb-3">
                                                                    <div class="w-100 input-container">
                                                                        <label>Email</label>
                                                                        <input class="form-control form-control" type="text" value="{{ $user->email }}">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 d-flex align-items-center mt-4">
                                                                    <button style="height: 42px; font-size: 14px;"
                                                                        type="submit"
                                                                        class="btn btn-info w-100">
                                                                        Verifikasi
                                                                    </button>
                                                                </div>
                                                                @endif
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <!-- END -->
                                                </div>
                                                <div class="col-md-12 mt-2" id="submit-container-company" style="display: none;">
                                                    <button type="submit" class="btn btn-primary btn-submit rounded-pill" style="width: 100%;"> <i class="fa fa-paper-plane"></i> UPDATE DATA</button>
                                                </div>
                                                </form>
                                            </div>

                                            <form id="verification-form-bank" action="{{ route('account.pengguna.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="post mt-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label>Role Akun</label>
                                                            <input style="height: 42px; font-size: 14px;" class="form-control form-control-sm" type="text" placeholder="Role" value="{{ ucfirst(strtolower($user->level)) }}" readonly>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Status Akun</label>
                                                            <select class="form-control" id="status" name="status" style="height:max-content">
                                                                <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                                                                <option value="nonactive" {{ $user->status === 'nonactive' || $user->status === null ? 'selected' : '' }}>Non Active</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row mt-3">
                                                        <div class="col-md-6">
                                                            <label>No Telp</label>
                                                            <input type="text" class="form-control form-control" id="telp" name="telp" value="{{ $user->telp }}" maxlength="15" minlength="8" onkeypress="return event.charCode >= 48 && event.charCode <=57" oninput="formatPhoneNumber(this)" readonly>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Jenis Akun</label>
                                                            <input class="form-control form-control" type="text" placeholder="jenis" value="{{ ucfirst(strtolower($user->jenis)) }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 mt-4" id="submit-container-bank">
                                                    <button type="submit" class="btn btn-primary btn-submit rounded-pill" style="width: 100%;"> <i class="fa fa-paper-plane"></i> UPDATE DATA</button>
                                                </div>
                                            </form>
                                        </div>
                                        <!--================== END TAB DATA DIRI ==================-->

                                        <!--================== TAB RESET PASSWORD ==================-->
                                        <div class="tab-pane" id="settings">
                                            <form class="form-horizontal" id="register-form" action="{{ route('account.profil.reset.password') }}" method="POST">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-md-12 mt-4">
                                                        <label>Masukan Password Lama</label>
                                                        <div class="password-group">
                                                            <input type="password" class="form-control" id="old-password" name="old_password" placeholder="Masukan Password Lama"
                                                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                                                title="Harus berisi setidaknya satu angka dan satu huruf besar dan kecil, dan setidaknya 8 karakter atau lebih"
                                                                required>
                                                            <i class="fas fa-eye password-toggle" id="old-password-toggle"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mt-4">
                                                        <label>Masukan Password Baru</label>
                                                        <div class="password-group">
                                                            <input type="password" class="form-control" name="password" id="password"
                                                                placeholder="Masukan Password Baru"
                                                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                                                title="Harus berisi setidaknya satu angka dan satu huruf besar dan kecil, dan setidaknya 8 karakter atau lebih"
                                                                required>
                                                            <i class="fas fa-eye password-toggle" id="password-toggle"></i>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mt-4">
                                                        <label>Ulangi Password Baru</label>
                                                        <div class="password-group">
                                                            <input type="password" class="form-control" name="password_confirmation"
                                                                id="password_confirmation" placeholder="Ulangi Password Baru"
                                                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                                                title="Harus berisi setidaknya satu angka dan satu huruf besar dan kecil, dan setidaknya 8 karakter atau lebih"
                                                                required>
                                                            <i class="fas fa-eye password-toggle" id="password-confirmation-toggle"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <div class="col-md-12 mt-4">
                                                        <button type="submit" class="btn btn-info btn-submit" style="width: 100%;">Simpan</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <!--================== END TAB RESET PASSWORD ==================-->

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </section>
</div>
<!--================== SHOW & HIDE BUTTON SUBMIT ==================-->
<script>
    // UPDAT DATA NAMA DAN USERNAME
    document.addEventListener("DOMContentLoaded", function() {
        const fullNameInput = document.getElementById('full_name');
        const usernameInput = document.getElementById('username');
        const submitContainer = document.getElementById('submit-container');

        function checkForChanges() {
            if (fullNameInput.value !== fullNameInput.defaultValue || usernameInput.value !== usernameInput.defaultValue) {
                submitContainer.style.display = 'block'; // Show the submit button
            } else {
                submitContainer.style.display = 'none'; // Hide the submit button
            }
        }

        fullNameInput.addEventListener('input', checkForChanges);
        usernameInput.addEventListener('input', checkForChanges);
    });

    // UPDAT DATA COMPANY
    document.addEventListener("DOMContentLoaded", function() {
        const companyInput = document.getElementById('company');
        const submitContainerCompany = document.getElementById('submit-container-company');

        function checkForChanges() {
            if (companyInput.value !== companyInput.defaultValue) {
                submitContainerCompany.style.display = 'block'; // Show the submit button
            } else {
                submitContainerCompany.style.display = 'none'; // Hide the submit button
            }
        }

        companyInput.addEventListener('input', checkForChanges);
    });

    // UPDAT DATA BANK
    document.addEventListener("DOMContentLoaded", function() {
        const levelInput = document.getElementById('level');
        const statusInput = document.getElementById('status');
        const nikInput = document.getElementById('nik');
        const norekInput = document.getElementById('norek');
        const bankInput = document.getElementById('bank');
        const submitContainerBank = document.getElementById('submit-container-bank');

        function checkForChanges() {
            if (levelInput.value !== levelInput.defaultValue || statusInput.value !== statusInput.defaultValue || nikInput.value !== nikInput.defaultValue || norekInput.value !== norekInput.defaultValue || bankInput.value !== bankInput.defaultValue) {
                submitContainerBank.style.display = 'block'; // Show the submit button
            } else {
                submitContainerBank.style.display = 'none'; // Hide the submit button
            }
        }

        levelInput.addEventListener('input', checkForChanges);
        statusInput.addEventListener('input', checkForChanges);
        nikInput.addEventListener('input', checkForChanges);
        norekInput.addEventListener('input', checkForChanges);
        bankInput.addEventListener('input', checkForChanges);
    });
</script>
<!--================== END ==================-->

<!--================== POPUP EDIT DATA DIRI ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const openPopupButton = document.getElementById('openPopupButtonEmail');
        const customPopupemail = document.getElementById('customPopupEmail');
        const customPopupClose = document.getElementById('customPopupCloseEmail');

        // Show popup when the pencil icon is clicked
        openPopupButton.addEventListener('click', function() {
            customPopupemail.style.display = 'block';
        });

        // Hide popup when the close button is clicked
        customPopupClose.addEventListener('click', function() {
            customPopupemail.style.display = 'none';
        });

        // Hide popup when clicking outside the popup content
        window.addEventListener('click', function(event) {
            if (event.target === customPopupemail) {
                customPopupemail.style.display = 'none';
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const openPopupButton = document.getElementById('openPopupButtonJobdesk');
        const customPopupemail = document.getElementById('customPopupJobdesk');
        const customPopupClose = document.getElementById('customPopupCloseJobdesk');

        // Show popup when the pencil icon is clicked
        openPopupButton.addEventListener('click', function() {
            customPopupemail.style.display = 'block';
        });

        // Hide popup when the close button is clicked
        customPopupClose.addEventListener('click', function() {
            customPopupemail.style.display = 'none';
        });

        // Hide popup when clicking outside the popup content
        window.addEventListener('click', function(event) {
            if (event.target === customPopupemail) {
                customPopupemail.style.display = 'none';
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const openPopupButton = document.getElementById('openPopupButtonTelp');
        const customPopupemail = document.getElementById('customPopupTelp');
        const customPopupClose = document.getElementById('customPopupCloseTelp');

        // Show popup when the pencil icon is clicked
        openPopupButton.addEventListener('click', function() {
            customPopupemail.style.display = 'block';
        });

        // Hide popup when the close button is clicked
        customPopupClose.addEventListener('click', function() {
            customPopupemail.style.display = 'none';
        });

        // Hide popup when clicking outside the popup content
        window.addEventListener('click', function(event) {
            if (event.target === customPopupemail) {
                customPopupemail.style.display = 'none';
            }
        });
    });
</script>
<!--================== END ==================-->

<!--================== SWEET ALERT HARUS VERIFIKASI EMAIL DAHULU ==================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const button = document.getElementById('edit-jobdesk-button');
        if (button) {
            button.addEventListener('click', function(event) {
                // Check the data-action attribute to determine if the email is verified
                if (button.getAttribute('data-action') === 'verify-email') {
                    event.preventDefault(); // Prevent default action
                    Swal.fire({
                        title: 'Harus verifikasi Email',
                        text: 'Anda harus memverifikasi email Anda sebelum mengedit jobdesk Anda.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
        const button = document.getElementById('edit-telp-button');
        if (button) {
            button.addEventListener('click', function(event) {
                // Check the data-action attribute to determine if the email is verified
                if (button.getAttribute('data-action') === 'verify-email') {
                    event.preventDefault(); // Prevent default action
                    Swal.fire({
                        title: 'Harus verifikasi Email',
                        text: 'Anda harus memverifikasi email Anda sebelum mengedit No Telp Anda.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
</script>
<!--================== END ==================-->

<!--================== FORMAT NO REKENING ==================-->
<script>
    function formatNoRek(input) {
        // Menghapus semua karakter non-digit
        var NoRek = input.value.replace(/\D/g, '');

        // Menggunakan ekspresi reguler untuk memformat nomor telepon
        NoRek = NoRek.replace(/(\d{4})(\d{2})(\d{6})(\d{2})(\d{1})/, '$1-$2-$3-$4-$5');

        // Mengatur nilai input dengan nomor telepon yang diformat
        input.value = NoRek;
    }
</script>
<!--================== END ==================-->

<!--================== FOTO PROFIL ==================-->
<script>
    function toggleSubmitButton() {
        var fileInput = document.getElementById('foto');
        var submitButton = document.getElementById('updateButton');
        submitButton.disabled = !fileInput.files.length; // Disable button if no file selected
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session("success") }}',
            showConfirmButton: false,
            timer: 3000,
            // timerProgressBar: true
        }).then(() => {
            location.reload(); // Automatically refresh the page after the alert
        });
        @endif
    });
</script>
<!--================== END ==================-->

<!--================== MAKSIMAL UPLOAD GAMBAR & FILE YANG DI PERBOLEHKAN ==================-->
<script>
    document.getElementById('foto').addEventListener('change', function() {
        const maxFileSizeInBytes = 3 * 1024 * 1024;
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        const fileInput = this;

        if (fileInput.files.length > 0) {
            const selectedFile = fileInput.files[0];
            const fileSize = selectedFile.size; // Get the file size in bytes
            const fileName = selectedFile.name.toLowerCase();

            // Check file size
            if (fileSize > maxFileSizeInBytes) {
                // Display a SweetAlert error message
                Swal.fire({
                    icon: 'error',
                    title: 'Ukuran File Melebihi Batas',
                    text: 'Ukuran File Yang Diperbolehkan Dibawah 3MB.',
                    showConfirmButton: false,
                    timer: 3000,
                    // timerProgressBar: true
                });
                fileInput.value = ''; // Clear the file input
                return;
            }

            // Check file extension
            const fileExtension = fileName.split('.').pop();
            if (!allowedExtensions.includes(fileExtension)) {
                // Display a SweetAlert error message
                Swal.fire({
                    icon: 'error',
                    title: 'Jenis File Tidak Valid',
                    text: 'Hanya File JPG, JPEG, PNG, dan GIF Yang Diperbolehkan.',
                    showConfirmButton: false,
                    timer: 3000,
                    // timerProgressBar: true
                });
                fileInput.value = ''; // Clear the file input
            }
        }
    });
</script>
<!--================== END ==================-->

<!--================== DATA PROFIL ==================-->
<script>
    // Function to show SweetAlert messages
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('statusauthorized'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'You are not authorized to update the email',
            showConfirmButton: false,
            timer: 3000,
            // timerProgressBar: true
        }).then(() => {
            location.reload(); // Automatically refresh the page after the alert
        });
        @endif

        @if(session('statusdataprofil'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data profil berhasil diperbarui',
            showConfirmButton: false,
            timer: 3000,
            // timerProgressBar: true
        }).then(() => {
            location.reload(); // Automatically refresh the page after the alert
        });
        @endif

        @if(session('statusdatabank'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data bank berhasil diperbarui',
            showConfirmButton: false,
            timer: 3000,
            // timerProgressBar: true
        }).then(() => {
            location.reload(); // Automatically refresh the page after the alert
        });
        @endif

        @if(session('erroremailterpakai'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Email sudah terdaftar silahkan gunakan email yang lain',
            showConfirmButton: false,
            timer: 3000,
            // timerProgressBar: true
        }).then(() => {
            location.reload(); // Automatically refresh the page after the alert
        });
        @endif

        @if(session('statusverifikasiemail'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Email berhasil di verifikasi',
            showConfirmButton: false,
            timer: 3000,
            // timerProgressBar: true
        }).then(() => {
            location.reload(); // Automatically refresh the page after the alert
        });
        @endif
    });
</script>
<!--================== END ==================-->

<!--================== FORMAT NO TELP ==================-->
<script>
    function formatPhoneNumber(input) {
        // Menghapus semua karakter non-digit
        var phoneNumber = input.value.replace(/\D/g, '');

        // Menentukan panjang nomor telepon
        var phoneNumberLength = phoneNumber.length;

        // Memeriksa panjang nomor telepon dan menerapkan format yang sesuai
        if (phoneNumberLength === 11) {
            phoneNumber = phoneNumber.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
        } else if (phoneNumberLength === 12) {
            phoneNumber = phoneNumber.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
        } else if (phoneNumberLength === 13) {
            phoneNumber = phoneNumber.replace(/(\d{5})(\d{4})(\d{4})/, '$1-$2-$3');
        }

        // Mengatur nilai input dengan nomor telepon yang diformat
        input.value = phoneNumber;
    }
</script>
<!--================== END ==================-->

<!--================== SHOW & HIDE PASSWORD ==================-->
<script>
    // Validasi konfirmasi password saat submit
    document.getElementById('register-form').addEventListener('submit', function(event) {
        const password = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;

        if (password !== passwordConfirmation) {
            event.preventDefault(); // Cegah pengiriman form
            Swal.fire({
                icon: 'error',
                title: 'Passwords Tidak Sesuai',
                text: 'Harap pastikan password dan konfirmasi password cocok.',
                confirmButtonText: 'OK'
            });
        }
    });

    // Fungsi untuk menampilkan atau menyembunyikan password dan mengubah border warna
    function togglePasswordVisibility(inputId, toggleId) {
        const passwordInput = document.getElementById(inputId);
        const passwordToggle = document.getElementById(toggleId);

        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;

            // Toggle the icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');

            // Toggle the border color
            passwordInput.classList.toggle('border-danger');
        });
    }

    // Panggil fungsi togglePasswordVisibility untuk setiap input password
    togglePasswordVisibility('old-password', 'old-password-toggle');
    togglePasswordVisibility('password', 'password-toggle');
    togglePasswordVisibility('password_confirmation', 'password-confirmation-toggle');
</script>
<!--================== END ==================-->

<!--================== RESET PASSWORD ==================-->
<script>
    document.getElementById('register-form').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Get form data
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);

        fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.statuserrorreset === 'error') {
                    // Show SweetAlert if old password does not match
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonText: 'OK'
                    });
                } else if (data.statussuksesreset === 'success') {
                    // Show SweetAlert if password is successfully changed
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message,
                        timer: 3000,
                        // timerProgressBar: true,
                        showConfirmButton: false,
                        willClose: () => {
                            window.location.href = "{{ route('account.profil.show', ['id' => $user->id]) }}";
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
</script>


<!-- JIKA KONFIRMASI PASSWORD TIDAK SAMA -->
<script>
    document.getElementById('register-form').addEventListener('submit', function(event) {
        const password = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;

        if (password !== passwordConfirmation) {
            event.preventDefault(); // Cegah pengiriman form
            Swal.fire({
                icon: 'error',
                title: 'Passwords Tidak Sesuai',
                text: 'Harap pastikan password dan konfirmasi password cocok.',
                confirmButtonText: 'OK'
            });
        }
    });
</script>
<!-- END -->
<!--================== END ==================-->

<!--================== LOADER BUTTON ==================-->
<script>
    // <!-- BUTTON SUBMIT -->
    $(".btn-submit").click(function() {
        $(".btn-submit").addClass('btn-progress');
        if (timeoutHandler) clearTimeout(timeoutHandler);

        timeoutHandler = setTimeout(function() {
            $(".btn-submit").removeClass('btn-progress');

        }, 1000);
    });
    // <!-- END -->

    // <!-- BUTTON RESET -->
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
    // <!-- END -->
</script>
<!--================== END ==================-->
@stop