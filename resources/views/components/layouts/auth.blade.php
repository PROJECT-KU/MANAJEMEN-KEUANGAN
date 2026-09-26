<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="{{ $warnaTema ?? '#0891b2' }}">
    <title>{{ $judulHalaman ?? 'Masuk' }} | MIS Rumah Scopus</title>

    {{-- hanya favicon yang memakai potongan logo, sebab tab peramban butuh
         gambar persegi; di tempat lain logo selalu tampil utuh --}}
    <link rel="icon" href="{{ asset('assets/img/mis-favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/mis-favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}?v=13">
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
