<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0891b2">
    <title>{{ $judulHalaman ?? 'Masuk' }} | MIS Rumah Scopus</title>

    <link rel="icon" href="{{ asset('assets/img/logoterbaru1.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}?v=2">
    @livewireStyles
</head>

<body class="{{ $kelasHalaman ?? 'halaman-masuk' }}">
    <div class="kerangka">

        {{-- ============ panel identitas (kiri di desktop, hero di atas pada layar kecil) --}}
        <aside class="panel-merek">
            <span class="gelembung gelembung-1"></span>
            <span class="gelembung gelembung-2"></span>
            <span class="gelembung gelembung-3"></span>

            <div class="merek-atas">
                <img src="{{ asset('assets/img/newlogo.png') }}" alt="ManagePro" class="merek-logo">
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
            <div class="kepala-panel">
                <span class="tanda-merek">
                    <img src="{{ asset('assets/img/logoterbaru1.png') }}" alt="">
                    MIS Rumah Scopus
                </span>
                <span class="tanda-status"><i></i> Sistem berjalan normal</span>
            </div>

            <div class="isi-form">
                {{ $slot }}

                <ul class="jaminan">
                    <li>
                        <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="10" rx="2" /><path d="M7 11V8a5 5 0 0110 0v3" /></svg>
                        Sambungan terenkripsi
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24"><path d="M12 3l8 4v5c0 4.4-3.4 8.3-8 9-4.6-.7-8-4.6-8-9V7l8-4z" /></svg>
                        Data Anda terlindungi
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
                        Bantuan hari kerja
                    </li>
                </ul>
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
