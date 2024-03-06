@extends('layouts.account')

@section('title')
Tambah Kategori Uang Masuk | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>TAMBAH KATEGORI UANG MASUK</h1>
        </div>

        <div class="section-body">

            <div class="card">
                <!-- <div class="card-header">
                    <h4><i class="fas fa-hand-holding-usd"></i> TAMBAH KATEGORI UANG MASUK</h4>
                </div> -->

                <div class="card-body">

                    <form action="{{ route('account.categories_debit.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kode Kategori</label>
                                    <input type="text" name="kode" value="{{ old('kode') }}" placeholder="huruf-angka" minlength="5" maxlength="15" class="form-control" style="text-transform:uppercase" required>
                                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
                                    <script>
                                        document.querySelector('input[name="kode"]').addEventListener('blur', function() {
                                            var pattern = /^(?=.*[0-9])(?=.*[a-zA-Z])([a-zA-Z0-9-]+)$/;
                                            if (!pattern.test(this.value)) {
                                                // Use SweetAlert for the validation message
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Oops...',
                                                    text: 'KODE KATEGORI harus mengandung huruf dan angka',
                                                });
                                                this.value = ''; // Clear the input if it doesn't match the pattern
                                            }
                                        });
                                    </script>
                                    @error('kode')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label>Nama Kategori</label>
                                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Masukkan Nama Kategori" minlength="5" maxlength="15" onkeypress="return/[a-zA-Z0-9 -]/i.test(event.key)" class="form-control" style="text-transform:uppercase" required>

                                    @error('name')
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