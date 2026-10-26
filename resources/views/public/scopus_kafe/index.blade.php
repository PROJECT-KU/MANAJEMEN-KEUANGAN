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
            <span class="kfd-stiker"><i class="bi bi-cup-hot-fill"></i> Pendampingan privat · 5 jam</span>

            <h1 class="kfd-judul">
                Datang dengan naskahmu, pulang dengan <span class="kfd-stabilo">jalan keluarnya</span>
            </h1>

            <p class="kfd-sub">
                Lima jam berdua dengan trainer Rumah Scopus — bukan kelas, bukan ceramah.
                Naskahmu sendiri yang dibedah, pertanyaanmu sendiri yang dijawab.
            </p>

            {{-- KAPAN-nya disebut di layar pertama, bukan ditemukan orangnya
                 di borang. Ini layanan tanpa angkatan, jadi pertanyaan pertama
                 yang muncul justru "kapan bisanya?" — dan sebelum ini halaman
                 ini tidak menjawabnya sama sekali. --}}
            <div class="kfd-kapan">
                <i class="bi bi-calendar-week"></i>
                <span>
                    Terbuka sepanjang tahun, dijalankan setiap <strong>{{ $hariBuka }}</strong>.
                    Tanggal terdekat yang bisa dipesan
                    <strong>{{ $tanggalBerikutnya->locale('id')->translatedFormat('l, j F Y') }}</strong>.
                </span>
            </div>
        </div>
    </section>

    <section class="kfd-bagian">
        <div class="container">
            <div class="kfd-kisi">

                <div class="kfd-panel">
                    <div class="kfd-panel-kartu">
                        <h2 class="kfd-panel-judul">Yang kamu dapat</h2>
                        <ul class="kfd-fasilitas">
                            <li><i class="bi bi-check-circle-fill"></i> <span>Lima jam pendampingan privat</span></li>
                            <li><i class="bi bi-check-circle-fill"></i> <span>Bedah naskah langsung, bukan materi umum</span></li>
                            <li><i class="bi bi-check-circle-fill"></i> <span>Trainer yang sudah menembus jurnal terindeks</span></li>
                            <li><i class="bi bi-check-circle-fill"></i> <span>Tempat dan kopi di Rumah Scopus Pusat</span></li>
                        </ul>

                        <p class="kfd-panel-kata">
                            Mau dua sesi sekaligus? Pilih keduanya di borang — tagihannya
                            dijumlahkan jadi satu.
                        </p>
                    </div>

                    {{-- Jalan kembali bagi yang kehilangan tautannya, di lajur
                         keterangan seperti di Scopus Camp.

                         Alamat halaman status memuat UUID yang tidak bisa
                         ditebak, jadi yang menutup tabnya dan tidak menerima
                         suratnya kehilangan pemesanannya — termasuk tombol
                         bayarnya. Tautannya dikirim ke EMAIL, tidak ke layar:
                         ditampilkan, siapa pun yang menebak alamat email orang
                         lain bisa membuka pemesanannya. --}}
                    <form method="POST" action="{{ route('public.scopuskafe.tautansaya') }}" class="kfd-lacak">
                        @csrf
                        <div class="kfd-lacak-kata">
                            <span class="kfd-lacak-ikon"><i class="bi bi-ticket-detailed"></i></span>
                            <span>
                                <strong>Sudah pernah memesan?</strong>
                                Masukkan email Anda, tautan pemesanannya kami kirim ke sana.
                            </span>
                        </div>
                        <div class="kfd-lacak-isian">
                            <label for="kfdLacakEmail" class="visually-hidden">Alamat email</label>
                            <input type="email" id="kfdLacakEmail" name="email" required
                                autocomplete="email" placeholder="nama@email.com" value="{{ old('email') }}">
                            <button type="submit"><i class="bi bi-envelope"></i> Kirim tautannya</button>
                        </div>
                    </form>
                </div>

                <div class="kfd-sesi">
                    @forelse ($sesi as $s)
                        <div class="kfd-baris">
                            <div class="kfd-jam">
                                <span class="kfd-jam-angka">{{ \Carbon\Carbon::parse($s->waktu_mulai)->format('H:i') }}</span>
                                <span class="kfd-jam-label">mulai</span>
                            </div>

                            <div class="kfd-isi">
                                <h3 class="kfd-nama">{{ $s->sesi }}</h3>
                                <p class="kfd-ket">
                                    <i class="bi bi-clock"></i>
                                    {{ \Carbon\Carbon::parse($s->waktu_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($s->waktu_selesai)->format('H:i') }}
                                    @if ($s->lokasi)
                                        <span class="kfd-pisah">·</span>
                                        <i class="bi bi-geo-alt"></i> {{ $s->lokasi }}
                                    @endif
                                </p>
                                @if ($s->deskripsi)
                                    <p class="kfd-ket">{{ $s->deskripsi }}</p>
                                @endif
                            </div>

                            <div class="kfd-kanan">
                                <span class="kfd-harga">
                                    Rp {{ number_format(\App\Support\HargaKafe::rupiah($s->biaya), 0, ',', '.') }}
                                </span>
                                <span class="kfd-harga-ket">per sesi</span>
                            </div>
                        </div>
                    @empty
                        <div class="kfd-kosong">
                            <i class="bi bi-cup"></i>
                            <h2>Belum ada sesi yang dibuka</h2>
                            <p>Jadwalnya biasanya diumumkan beberapa hari sebelumnya.
                                Kabari admin kalau mau diberi tahu duluan.</p>
                        </div>
                    @endforelse

                    @if ($sesi->isNotEmpty())
                        @if ($gerbangSiap)
                            <a href="{{ route('public.scopuskafe.formpendaftaran') }}" class="kfd-tombol">
                                <i class="bi bi-pencil-square"></i> Pesan sekarang
                            </a>
                        @else
                            {{-- Tanpa gerbang, borangnya memang dipantulkan
                                 pengendalinya. Menawarkan tombolnya berarti
                                 mengantar orang ke jalan buntu. --}}
                            <span class="kfd-tombol mati">
                                <i class="bi bi-lock-fill"></i> Pemesanan ditutup sementara
                            </span>
                        @endif
                    @endif
                </div>

            </div>
        </div>
    </section>
</div>
@stop
