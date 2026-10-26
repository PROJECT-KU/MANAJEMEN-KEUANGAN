@extends('public.layout.header')

@section('title', 'Scopus Kafe | Rumah Scopus')

@push('kepala')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600..900&family=Figtree:wght@400;500;700;800&display=swap">
@endpush

@section('konten')
{{--
    Scopus Kafe — dirombak 26 Okt 2026 mengikuti Scopus Camp.

    Bentuk lamanya memakai hero Bootstrap bawaan templat beserta gradien
    merah-jingga #ff3131 → #ff914d: palet yang sudah ditinggalkan beranda dan
    blog justru karena pemiliknya menyebutnya terlalu mirip situs lain. Ia
    juga memajang kartu sesi lewat `meme` tanpa menyebutkan satu hal yang
    paling ditanyakan orang — KAPAN acaranya berjalan.

    TIDAK BERANGKATAN, dan itu yang membedakannya dari Scopus Camp: tidak ada
    daftar angkatan yang dibuka dan ditutup. Yang ada sesi tetap, dan
    tanggalnya dipilih sendiri pemesan — asal akhir pekan.
--}}
@php
    $hariBuka = \App\Support\HargaKafe::namaHariBuka();
@endphp
@include('public.scopus_kafe.partials.gaya-daftar')

<div class="kfd">

    @if (session('error') || session('success') || $errors->any())
        <div class="container" style="padding-top: 96px;">
            @if (session('error'))
                <div class="kfd-kabar galat" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i> <span>{{ session('error') }}</span>
                </div>
            @endif

            @if (session('success'))
                <div class="kfd-kabar sukses" role="status">
                    <i class="bi bi-check-circle-fill"></i> <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="kfd-kabar galat" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i> <span>{{ $errors->first() }}</span>
                </div>
            @endif
        </div>
    @endif

    <section class="kfd-kepala">
        <div class="container">
            {{-- TERPUSAT, bukan rata kiri.

                 Bentuk pertama rombakan ini rata kiri: judulnya jatuh ke
                 empat baris di lajur sempit sementara separuh kanan layar
                 kosong, dan kotak "kapan" mengambang sendirian di bawahnya.
                 Layanan ini cuma punya dua sesi — tidak ada daftar panjang
                 yang menuntut lajur kiri seperti di Scopus Camp — jadi yang
                 pantas justru susunan terpusat yang memakai lebarnya. --}}
            <span class="kfd-stiker"><i class="bi bi-cup-hot-fill"></i> Pendampingan privat · 5 jam</span>

            <h1 class="kfd-judul">
                Datang dengan naskahmu, pulang dengan <span class="kfd-stabilo">jalan keluarnya</span>
            </h1>

            <p class="kfd-sub">
                Lima jam berdua dengan trainer Rumah Scopus — bukan kelas, bukan ceramah.
                Naskahmu sendiri yang dibedah, pertanyaanmu sendiri yang dijawab.
            </p>

            {{-- KAPAN-nya satu pita terpusat, bukan kotak di pojok.

                 Ini layanan tanpa angkatan, jadi "kapan bisanya?" pertanyaan
                 pertama yang muncul — dan bentuk lamanya tidak menjawabnya
                 sama sekali sampai orangnya membuka borang. --}}
            <div class="kfd-kapan">
                <span class="kfd-kapan-butir">
                    <i class="bi bi-calendar-week"></i>
                    Setiap <strong>{{ $hariBuka }}</strong>
                </span>
                <span class="kfd-kapan-pisah"></span>
                <span class="kfd-kapan-butir">
                    <i class="bi bi-arrow-right-circle"></i>
                    Terdekat <strong>{{ $tanggalBerikutnya->locale('id')->translatedFormat('j F Y') }}</strong>
                </span>
            </div>
        </div>
    </section>

    <section class="kfd-bagian">
        <div class="container">

            @if ($sesi->isNotEmpty())
                <h2 class="kfd-bagian-judul">Pilih sesinya</h2>
                <p class="kfd-bagian-kata">
                    Boleh satu, boleh keduanya — kalau dua sesi, tagihannya dijumlahkan
                    jadi satu.
                </p>

                {{-- Kartu berdampingan, bukan baris selebar layar.

                     Barisnya dulu selebar kartu dengan jurang kosong di
                     tengahnya: nama sesi di kiri, harga di kanan, dan dua
                     pertiga ruang di antaranya tidak mengatakan apa-apa.
                     Dengan dua sesi, kartu berdampingan memakai lebarnya dan
                     tiap sesi punya bobot yang sama. --}}
                <div class="kfd-sesi">
                    @foreach ($sesi as $s)
                        <article class="kfd-kartu">
                            <div class="kfd-jam">
                                <span class="kfd-jam-angka">{{ \Carbon\Carbon::parse($s->waktu_mulai)->format('H:i') }}</span>
                                <span class="kfd-jam-label">mulai</span>
                            </div>

                            {{-- namaSesi: isinya di basis data berhuruf kecil
                                 semua ("sesi 1"), dan dicetak apa adanya ia
                                 terbaca seperti catatan yang belum selesai. --}}
                            <h3 class="kfd-nama">{{ \App\Support\HargaKafe::namaSesi($s->sesi) }}</h3>

                            <ul class="kfd-ciri">
                                <li>
                                    <i class="bi bi-clock"></i>
                                    {{ \Carbon\Carbon::parse($s->waktu_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($s->waktu_selesai)->format('H:i') }}
                                    <span class="kfd-ciri-ket">
                                        ({{ \Carbon\Carbon::parse($s->waktu_mulai)->diffInHours(\Carbon\Carbon::parse($s->waktu_selesai)) }} jam)
                                    </span>
                                </li>
                                @if ($s->lokasi)
                                    <li><i class="bi bi-geo-alt"></i> {{ $s->lokasi }}</li>
                                @endif
                                @if ($s->deskripsi)
                                    <li><i class="bi bi-info-circle"></i> {{ $s->deskripsi }}</li>
                                @endif
                            </ul>

                            <div class="kfd-harga">
                                <span class="kfd-harga-angka">
                                    Rp {{ number_format(\App\Support\HargaKafe::rupiah($s->biaya), 0, ',', '.') }}
                                </span>
                                <span class="kfd-harga-ket">per sesi</span>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="kfd-ajak">
                    @if ($gerbangSiap)
                        <a href="{{ route('public.scopuskafe.formpendaftaran') }}" class="kfd-tombol">
                            <i class="bi bi-pencil-square"></i> Pesan sekarang
                        </a>
                        <p class="kfd-ajak-kata">
                            Isinya sebentar saja — lalu Anda langsung diantar ke halaman
                            pembayaran.
                        </p>
                    @else
                        {{-- Tanpa gerbang, borangnya memang dipantulkan
                             pengendalinya. Menawarkan tombolnya berarti
                             mengantar orang ke jalan buntu. --}}
                        <span class="kfd-tombol mati">
                            <i class="bi bi-lock-fill"></i> Pemesanan ditutup sementara
                        </span>
                        <p class="kfd-ajak-kata">
                            Pembayaran daring sedang tidak tersedia. Silakan hubungi panitia
                            lewat WhatsApp.
                        </p>
                    @endif
                </div>
            @else
                <div class="kfd-kosong">
                    <i class="bi bi-cup"></i>
                    <h2>Belum ada sesi yang dibuka</h2>
                    <p>Jadwalnya biasanya diumumkan beberapa hari sebelumnya.
                        Kabari admin kalau mau diberi tahu duluan.</p>
                </div>
            @endif

            <h2 class="kfd-bagian-judul kfd-renggang">Yang kamu dapat</h2>

            {{-- Empat ubin sejajar, bukan daftar centang di lajur sempit:
                 keempatnya sederajat, dan ditumpuk ke bawah di kiri halaman
                 ia terbaca seperti syarat dan ketentuan. --}}
            <div class="kfd-ubin">
                @foreach ([
                    ['bi-hourglass-split', 'Lima jam penuh', 'Satu sesi utuh, bukan konsultasi sepuluh menit.'],
                    ['bi-file-earmark-text', 'Naskahmu yang dibedah', 'Bukan materi umum — yang dibuka naskah yang kamu bawa.'],
                    ['bi-person-check', 'Trainer berpengalaman', 'Pendamping yang sudah menembus jurnal terindeks Scopus.'],
                    ['bi-cup-hot', 'Tempat dan kopinya', 'Di Rumah Scopus Pusat, Yogyakarta.'],
                ] as [$ikon, $judul, $kata])
                    <div class="kfd-ubin-butir">
                        <span class="kfd-ubin-ikon"><i class="bi {{ $ikon }}"></i></span>
                        <h3>{{ $judul }}</h3>
                        <p>{{ $kata }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Jalan kembali bagi yang kehilangan tautannya.

                 Alamat halaman status memuat UUID yang tidak bisa ditebak,
                 jadi yang menutup tabnya dan tidak menerima suratnya
                 kehilangan pemesanannya — termasuk tombol bayarnya. Tautannya
                 dikirim ke EMAIL, tidak ke layar: ditampilkan, siapa pun yang
                 menebak alamat email orang lain bisa membuka pemesanannya. --}}
            <form method="POST" action="{{ route('public.scopuskafe.tautansaya') }}" class="kfd-lacak">
                @csrf
                <span class="kfd-lacak-ikon"><i class="bi bi-ticket-detailed"></i></span>
                <div class="kfd-lacak-kata">
                    <strong>Sudah pernah memesan?</strong>
                    <span>Masukkan email Anda, tautan pemesanannya kami kirim ke sana.</span>
                </div>
                <div class="kfd-lacak-isian">
                    <label for="kfdLacakEmail" class="visually-hidden">Alamat email</label>
                    <input type="email" id="kfdLacakEmail" name="email" required
                        autocomplete="email" placeholder="nama@email.com" value="{{ old('email') }}">
                    <button type="submit"><i class="bi bi-envelope"></i> Kirim tautannya</button>
                </div>
            </form>
        </div>
    </section>
</div>
@stop
