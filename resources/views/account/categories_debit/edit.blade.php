@extends('layouts.account')

@section('title')
Update Kategori Uang Masuk | MANAGEMENT
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>KATEGORI UANG MASUK</h1>
        </div>

        <div class="section-body">

            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-hand-holding-usd"></i> UPDATE KATEGORI UANG MASUK</h4>
                </div>

                <div class="card-body">

                    <form action="{{ route('account.categories_debit.update', $categoriesDebit->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label>NAMA KATEGORI</label>
                            <input type="text" name="name" value="{{ old('name', $categoriesDebit->name) }}" placeholder="Masukkan Nama Kategori" class="form-control" style="text-transform:uppercase">

                            @error('name')
                            <div class="invalid-feedback" style="display: block">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> UPDATE</button>
                        <a href="{{ route('account.categories_debit.index') }}" class="btn btn-info mr-1">
                            <i class="fa fa-list"></i> LIST KATEGORI UANG MASUK
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