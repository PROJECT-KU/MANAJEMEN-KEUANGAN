@extends('layouts.account')

@section('title', 'Ganti Kata Sandi')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Ganti kata sandi sementara</h3>
                <p class="text-subtitle text-muted">Sekali ini saja, lalu Anda bisa memakai MIS seperti biasa.</p>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-7">

                {{-- Kenapa ia ada di sini dijelaskan, bukan cuma dituntut.
                     Layar yang menahan orang tanpa menyebut sebabnya terbaca
                     seperti akunnya bermasalah. --}}
                <div class="alert alert-warning">
                    <h6 class="alert-heading mb-1"><i class="bi bi-shield-exclamation"></i> Kenapa diminta?</h6>
                    <p class="mb-0" style="font-size:.93rem;line-height:1.7;">
                        Kata sandi yang kami kirim lewat email <strong>sama untuk semua peserta</strong>
                        webinar. Selama belum diganti, orang lain yang tahu nama Anda bisa ikut
                        masuk ke akun ini. Pilih kata sandi Anda sendiri dulu — setelah itu halaman
                        ini tidak muncul lagi.
                    </p>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('account.sandi.wajib.simpan') }}" autocomplete="off">
                            @csrf

                            <div class="form-group">
                                <label for="password">Kata sandi baru</label>
                                <input type="password" id="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    required autocomplete="new-password" autofocus>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-1">
                                    Minimal 8 huruf, memuat huruf dan angka, dan bukan kata sandi
                                    yang pernah bocor di internet.
                                </small>
                            </div>

                            <div class="form-group mt-3">
                                <label for="password_confirmation">Ulangi kata sandi baru</label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="form-control" required autocomplete="new-password">
                            </div>

                            <button type="submit" class="btn btn-primary mt-3">
                                <i class="bi bi-check2-circle"></i> Simpan dan lanjutkan
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>
@endsection
