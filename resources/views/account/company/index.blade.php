@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Company | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>COMPANY</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <!-- <div class="card-header">
                        <h4><i class="fas fa-building"></i> UPDATE COMPANY</h4>
                    </div> -->

                <div class="card-body">

                    <form action="{{ route('account.company.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nama Perusahaan</label>
                                    <input type="text" id="company" name="company" class="form-control" value="{{ old('company', $user->company) }}" class="form-control currency" maxlength="30" minlength="5" onkeypress="return/[A-Z]/i.test(event.key)" style="text-transform:uppercase">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Email Perusahaan</label>
                                    <input type="text" id="email_company" name="email_company" class="form-control" value="{{ old('email_company', $user->email_company) }}" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Manager Perusahaan</label>
                                    <input type="text" id="pj_company" name="pj_company" class="form-control" value="{{ old('pj_company', $user->pj_company) }}" maxlength="50" minlength="5" onkeypress="return/[a-zA-Z0-9., ]/i.test(event.key)">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Alamat Perusahaan</label>
                                    <textarea id="alamat_company" name="alamat_company" class="form-control" value="{{ old('alamat_company', $user->alamat_company) }}">{{ ($user->alamat_company) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Telp Perusahaan</label>
                                    <input type="tel" id="telp_company" name="telp_company" class="form-control" value="{{ old('telp_company', $user->telp_company) }}" maxlength="20" minlength="8" onkeypress="return event.charCode >= 48 && event.charCode <=57" oninput="formatPhoneNumber(this)">
                                </div>
                            </div>
                        </div>



                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Logo Perusahaan</label>
                                    <div class="input-group">
                                        <input type="file" name="logo_company" id="logo_company" class="form-control" accept="image/*" capture="camera">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="thumbnail-circle" style="width: 12rem;">
                                        @if (Auth::user()->logo_company == null)
                                        <img alt="image" id="image-preview" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-thumbnail rounded-circle" style="width: 100px; height:100px;">
                                        @else
                                        <img id="image-preview" class="img-thumbnail rounded-circle" src="{{ asset('images/' .  Auth::user()->logo_company) }}" alt="Preview Image" style="width: 100px; height:100px;">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if ( Auth::user()->level == 'ceo')
                        @else
                        <div class="d-flex">
                            <button class="btn btn-primary btn-submit mr-1 rounded-pill" type="submit" style="width: 100%; font-size: 14px;">
                                <i class="fa fa-paper-plane"></i> UPDATE
                            </button>
                        </div>
                        @endif

                    </form>

                </div>
            </div>
        </div>
    </section>
</div>

<!--================== format telp ==================-->
<script>
    function formatPhoneNumber(input) {
        // Menghapus semua karakter non-digit
        var phoneNumber = input.value.replace(/\D/g, '');

        // Menggunakan ekspresi reguler untuk memformat nomor telepon
        phoneNumber = phoneNumber.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');

        // Mengatur nilai input dengan nomor telepon yang diformat
        input.value = phoneNumber;
    }
</script>
<!--================== end ==================-->

<!-- maksimal upload gambar & jenis file yang di perbolehkan -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('logo_company').addEventListener('change', function() {
        const maxFileSizeInBytes = 5024 * 5024; // 5MB
        const allowedExtensions = ['jpg', 'jpeg', 'png'];
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
                    text: 'Ukuran File Yang Diperbolehkan Dibawah 5MB.',
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
                    text: 'Hanya File JPG, JPEG, dan PNG Yang Diperbolehkan.',
                });
                fileInput.value = ''; // Clear the file input
            }
        }
    });
</script>
<!-- end -->

<!-- upload image -->
<script>
    const imageInput = document.getElementById('logo_company');
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

<!-- waktu untuk menampilkan alerts -->
<!--<script>
  document.addEventListener('DOMContentLoaded', function() {
    var successAlert = document.getElementById('successAlert');
    if (successAlert) {
      setTimeout(function() {
        successAlert.style.display = 'none';
      }, 3000);
    }
  });
</script>-->
<!-- end -->

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
            $("#company").val('');
            $("#email_company").val('');
            $("#pj_company").val('');
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