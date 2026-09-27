@php
    /**
     * Kumpulan ikon garis untuk poin di panel merek. Disimpan di satu tempat
     * supaya tiap halaman cukup menyebut namanya lewat atribut Layout.
     */
    $ikonPoin = [
        'kunci' => '<rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V8a5 5 0 0110 0v3"/>',
        'pin' => '<rect x="3" y="4" width="18" height="16" rx="3"/><path d="M8 9h.01M12 9h.01M16 9h.01M8 13h.01M12 13h.01M16 13h.01M9 17h6"/>',
        'surel' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
        'perisai' => '<path d="M12 3l8 3v6c0 4.4-3.2 8.1-8 9-4.8-.9-8-4.6-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/>',
        'jejak' => '<path d="M3 12h4l3 8 4-16 3 8h4"/>',
        'orang' => '<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'jam' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'centang' => '<circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/>',
    ];
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="{{ $warnaTema ?? '#4f46e5' }}">
    <title>{{ $judulHalaman ?? 'Masuk' }} | MIS Rumah Scopus</title>

    {{-- hanya favicon yang memakai potongan logo, sebab tab peramban butuh
         gambar persegi; di tempat lain logo selalu tampil utuh --}}
    <link rel="icon" href="{{ asset('assets/img/mis-favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/mis-favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Inter: huruf yang sama dengan dasbor --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}?v=19">
    <script src="{{ asset('assets/js/auth.js') }}?v=2"></script>
    @livewireStyles
</head>

<body class="{{ $kelasHalaman ?? 'halaman-masuk' }}">
    <div class="kerangka">

        {{-- ============ panel identitas (kiri di desktop, hero di atas pada layar kecil) --}}
        <aside class="panel-merek">
            <span class="cahaya cahaya-1"></span>
            <span class="cahaya cahaya-2"></span>
            {{-- potongan ikon sebagai latar, menyembul di pojok kanan bawah --}}
            <img src="{{ asset('assets/img/mis-ikon.png') }}" alt="" class="merek-latar">

            <div class="merek-atas">
                <img src="{{ asset('assets/img/newlogogeneration.png') }}"
                    alt="MIS — Management Integration System by Rumah Scopus" class="merek-logo">
            </div>

            <div class="merek-isi">
                <h1 class="merek-judul">{{ $merekJudul ?? 'Satu akun untuk semua layanan.' }}</h1>
                <p class="merek-teks">{{ $merekTeks ?? 'Masuk atau daftar untuk mengakses layanan Rumah Scopus Foundation.' }}</p>

                {{-- poin singkat dengan ikon berwarna: mengisi ruang tengah
                     sekaligus menjelaskan alur halaman ini --}}
                @if (! empty($poinMerek))
                    <ul class="merek-poin">
                        @foreach ($poinMerek as $poin)
                            <li>
                                <span class="poin-ikon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">{!! $ikonPoin[$poin['ikon']] ?? $ikonPoin['centang'] !!}</svg>
                                </span>
                                <span>
                                    <strong>{{ $poin['judul'] }}</strong>
                                    {{ $poin['teks'] }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="merek-bawah">
                <p class="merek-kaki">Rumah Scopus Foundation</p>
            </div>
        </aside>

        {{-- ================================================== sisi formulir --}}
        <main class="panel-form">
            <div class="isi-form">
                {{ $slot }}
            </div>

            <footer class="kaki-halaman">
                <span>&copy; {{ date('Y') }} Rumah Scopus Foundation</span>
                <a href="{{ url('/') }}">Kembali ke beranda</a>
            </footer>
        </main>
    </div>

    @livewireScripts
</body>

</html>
