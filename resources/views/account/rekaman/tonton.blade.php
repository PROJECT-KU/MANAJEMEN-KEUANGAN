@extends('layouts.account')

@section('title', 'Rekaman — ' . $angkatan->nama)

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-8 order-md-1 order-last">
                <h3>{{ $angkatan->nama }}</h3>
                <p class="text-subtitle text-muted">
                    <i class="bi bi-calendar-event"></i>
                    {{ $angkatan->mulai ? \Illuminate\Support\Carbon::parse($angkatan->mulai)->locale('id')->translatedFormat('j F Y') : '-' }}
                    @if ($angkatan->pemateri) &nbsp;·&nbsp; {{ $angkatan->pemateri }} @endif
                </p>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                {{-- aspect-ratio 16/9 pada pembungkusnya, iframe mengisi penuh.
                     Tinggi tetap membuat bilah hitam di atas-bawah pada layar
                     ponsel, dan iframe tanpa pembungkus beraspek runtuh jadi
                     150px bawaan peramban. --}}
                <div style="position:relative;width:100%;aspect-ratio:16/9;background:#000;border-radius:12px;overflow:hidden;">
                    <iframe src="{{ $semat }}"
                        title="Rekaman {{ $angkatan->nama }}"
                        style="position:absolute;inset:0;width:100%;height:100%;border:0;"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen></iframe>
                </div>

                <div class="d-flex flex-wrap align-items-center mt-3" style="gap:10px;">
                    <a href="{{ route('account.rekaman.index') }}" class="btn btn-light-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali ke daftar
                    </a>
                    <a href="{{ $tautanYouTube }}" target="_blank" rel="noopener"
                        class="btn btn-light-secondary btn-sm">
                        <i class="bi bi-box-arrow-up-right"></i> Buka di YouTube
                    </a>
                </div>

                <p class="text-muted mt-3 mb-0" style="font-size:.86rem;line-height:1.7;">
                    Rekaman ini untuk peserta webinar tersebut. Mohon tidak membagikan tautannya
                    ke luar — teman yang ingin menontonnya bisa mendaftar webinar berikutnya.
                </p>
            </div>
        </div>
    </section>
</div>
@endsection
