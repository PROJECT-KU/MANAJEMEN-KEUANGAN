@extends('layouts.account')

@section('title')
Update Kategori Uang Masuk | MIS
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>UPDATE KATEGORI UANG MASUK</h1>
        </div>

        <div class="section-body">

            <div class="card">
                <!-- <div class="card-header">
                    <h4><i class="fas fa-hand-holding-usd"></i> UPDATE KATEGORI UANG MASUK</h4>
                </div> -->

                <div class="card-body">

                    <form action="{{ route('account.categories_debit.update', $categoriesDebit->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kode Kategori</label>
                                    @php
                                    $initialValue = old('kode', $categoriesDebit->kode);
                                    @endphp
                                    <input type="text" name="kode" value="{{ $initialValue }}" placeholder="Huruf-angka" minlength="5" maxlength="15" class="form-control" style="text-transform: uppercase">
                                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
                                    <script>
                                        var kodeInput = document.querySelector('input[name="kode"]');
                                        var initialValue = "{!! $initialValue !!}";

                                        kodeInput.addEventListener('blur', function() {
                                            var pattern = /^(?=.*[0-9])(?=.*[a-zA-Z])([a-zA-Z0-9-]+)$/;
                                            if (!pattern.test(this.value)) {
                                                // Use SweetAlert for the validation message
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Oops...',
                                                    text: 'KODE KATEGORI harus mengandung huruf dan angka',
                                                    allowOutsideClick: false, // Prevent dismissing the alert by clicking outside
                                                }).then(function() {
                                                    // Restore the initial input value
                                                    kodeInput.value = initialValue;
                                                    kodeInput.focus(); // Focus back on the input field
                                                });
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
                                <div class="form-group">
                                    <label>Nama Kategori</label>
                                    <input type="text" name="name" value="{{ old('name', $categoriesDebit->name) }}" placeholder="Masukkan Nama Kategori" class="form-control" style="text-transform:uppercase">

                                    @error('name')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> UPDATE</button>
                        <a href="{{ route('account.categories_debit.index') }}" class="btn btn-info">
                            <i class="fa fa-undo"></i> KEMBALI
                        </a>

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