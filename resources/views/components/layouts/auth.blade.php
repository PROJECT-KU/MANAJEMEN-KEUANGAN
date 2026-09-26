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
                <div>
                    <h1 class="merek-judul">{{ $merekJudul ?? 'Satu pintu untuk seluruh operasional.' }}</h1>
                    <p class="merek-teks">{{ $merekTeks ?? 'Kelola keuangan, presensi, gaji, dan layanan Rumah Scopus Foundation dari satu sistem yang rapi dan terukur.' }}</p>
                </div>

                <ul class="daftar-nilai">
                    <li>
                        <span class="ikon-bulat">
                            <svg viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 7-7" /><path d="M14 8h6v6" /></svg>
                        </span>
                        <span>Arus kas, debit, dan kredit terpantau harian</span>
                    </li>
                    <li>
                        <span class="ikon-bulat">
                            <svg viewBox="0 0 24 24"><path d="M9 11l3 3 8-8" /><path d="M21 12v6a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h9" /></svg>
                        </span>
                        <span>Presensi, cuti, dan slip gaji dalam satu alur</span>
                    </li>
                    <li>
                        <span class="ikon-bulat">
                            <svg viewBox="0 0 24 24"><path d="M12 3l8 4v5c0 4.4-3.4 8.3-8 9-4.6-.7-8-4.6-8-9V7l8-4z" /><path d="M9.5 12.5l1.8 1.8 3.4-3.6" /></svg>
                        </span>
                        <span>Akses dibatasi sesuai peran tiap pengguna</span>
                    </li>
                </ul>
            </div>

            <div class="merek-bawah">
                <div class="papan-angka">
                    <div>
                        <b>12+</b>
                        <span>Modul</span>
                    </div>
                    <div>
                        <b>7</b>
                        <span>Peran</span>
                    </div>
                    <div>
                        <b>24/7</b>
                        <span>Akses</span>
                    </div>
                </div>
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
                        Data sesuai peran
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
