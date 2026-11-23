@extends('layouts.account')

@section('title', 'Rekaman Webinar')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Rekaman Webinar</h3>
                <p class="text-subtitle text-muted">Webinar yang Anda ikuti, bisa ditonton ulang kapan saja.</p>
            </div>
        </div>
    </div>

    <section class="section">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($siap->isEmpty() && $belum->isEmpty())
            {{-- Kosong SEPENUHNYA berarti akun ini memang belum pernah
                 membayar webinar. Dikatakan apa adanya, beserta apa yang bisa
                 ia lakukan — halaman kosong tanpa kalimat terbaca seperti
                 sistemnya rusak. --}}
            <div class="card">
                <div class="card-body text-center" style="padding:48px 24px;">
                    <i class="bi bi-camera-video-off" style="font-size:44px;color:#cbd5e1;"></i>
                    <h5 class="mt-3 mb-1">Belum ada rekaman untuk akun ini</h5>
                    <p class="text-muted mb-0" style="max-width:460px;margin:0 auto;line-height:1.7;">
                        Rekaman muncul di sini setelah pembayaran webinar Anda lunas dan panitia
                        memasang rekamannya. Kalau Anda merasa sudah membayar, pastikan Anda masuk
                        dengan email yang sama seperti saat mendaftar.
                    </p>
                </div>
            </div>
        @endif

        @if ($siap->isNotEmpty())
            <div class="row">
                @foreach ($siap as $a)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100">
                            <a href="{{ route('account.rekaman.tonton', $a->token) }}"
                                style="display:block;position:relative;background:#0f172a;border-radius:.5rem .5rem 0 0;overflow:hidden;">
                                {{-- aspect-ratio, bukan tinggi tetap: gambar mini
                                     YouTube selalu 16:9, dan tinggi tetap
                                     memotongnya berbeda-beda di tiap lebar kartu. --}}
                                <img src="{{ \App\Support\TautanYouTube::gambarMini($a->rekaman_url) }}"
                                    alt="Rekaman {{ $a->nama }}" loading="lazy"
                                    style="display:block;width:100%;aspect-ratio:16/9;object-fit:cover;">
                                <span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                                    <span style="width:54px;height:54px;border-radius:999px;background:rgba(15,23,42,.72);display:flex;align-items:center;justify-content:center;">
                                        <i class="bi bi-play-fill" style="color:#fff;font-size:26px;"></i>
                                    </span>
                                </span>
                            </a>
                            <div class="card-body">
                                <h5 class="card-title mb-1" style="font-size:1rem;line-height:1.5;">{{ $a->nama }}</h5>
                                <p class="text-muted mb-3" style="font-size:.86rem;">
                                    <i class="bi bi-calendar-event"></i>
                                    {{ $a->mulai ? \Illuminate\Support\Carbon::parse($a->mulai)->locale('id')->translatedFormat('j F Y') : '-' }}
                                </p>
                                <a href="{{ route('account.rekaman.tonton', $a->token) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-play-circle"></i> Tonton rekaman
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($belum->isNotEmpty())
            {{-- Yang belum ada rekamannya TETAP ditampilkan. Peserta yang
                 webinarnya baru selesai kemarin akan melihat halaman kosong
                 kalau tidak — dan halaman kosong tidak bisa ia bedakan dari
                 "akun saya salah". --}}
            <div class="card mt-2">
                <div class="card-header"><h6 class="mb-0">Belum tersedia</h6></div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:.9rem;">
                        Webinar ini sudah Anda bayar, tetapi rekamannya belum dipasang panitia.
                        Halaman ini akan memperbaruinya sendiri begitu rekamannya ada.
                    </p>
                    <ul class="mb-0" style="padding-left:18px;line-height:2;">
                        @foreach ($belum as $a)
                            <li>
                                {{ $a->nama }}
                                <span class="text-muted" style="font-size:.86rem;">
                                    — {{ $a->mulai ? \Illuminate\Support\Carbon::parse($a->mulai)->locale('id')->translatedFormat('j F Y') : '-' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </section>
</div>
@endsection
