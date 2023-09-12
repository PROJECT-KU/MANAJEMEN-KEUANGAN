@extends('layouts.account')

@section('title')
Update Uang keluar | MANAGEMENT
@stop

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1> UANG KELUAR</h1>
        </div>

        <div class="section-body">

            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-hand-holding-usd"></i> UPDATE UANG KELUAR</h4>
                </div>

                <div class="card-body">

                    <form action="{{ route('account.credit.update', $credit->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>NOMINAL (Rp.)</label>
                                    <input type="text" name="nominal" value="{{ old('nominal', $credit->nominal) }}" placeholder="Masukkan Nominal" class="form-control currency">

                                    @error('nominal')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>TANGGAL</label>
                                    <input type="text" class="form-control datetimepicker" name="credit_date" value="{{ old('credit_date', $credit->credit_date) }}" placeholder="Pilih Tanggal">

                                    @error('date_debit')
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
                                    <label>KATEGORI</label>
                                    <select class="form-control select2" name="category_id" style="width: 100%">
                                        <option value="">-- PILIH KATEGORI --</option>
                                        @foreach ($categories as $hasil)
                                        @if($credit->category_id == $hasil->id)
                                        <option value="{{ $hasil->id }}" selected> {{ $hasil->name }}</option>
                                        @else
                                        <option value="{{ $hasil->id }}"> {{ $hasil->name }}</option>
                                        @endif
                                        @endforeach
                                    </select>

                                    @error('category_id')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>BUKTI UANG KELUAR</label>
                                    <div class="input-group">
                                        <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" capture="camera">
                                    </div>
                                    @error('gambar')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="mt-3">
                                    <a href="{{ asset('images/' . $credit->gambar) }}" data-lightbox="{{ $credit->id }}">
                                        <div class="card" style="width: 12rem;">
                                            <img id="image-preview" style="width: 200px; height:200px;" class="card-img-top" src="{{ asset('images/' . $credit->gambar) }}" alt="Preview Image">
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>KETERANGAN</label>
                                    <textarea class="form-control" name="description" rows="6" placeholder="Masukkan Keterangan">{{ old('description', $credit->description) }}</textarea>

                                    @error('description')
                                    <div class="invalid-feedback" style="display: block">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i> UPDATE</button>
                        <a href="{{ route('account.credit.index') }}" class="btn btn-info mr-1">
                            <i class="fa fa-list"></i> LIST UANG KELUAR
                        </a>

                    </form>

                </div>
            </div>
        </div>
    </section>
</div>

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