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

            {{-- KAPAN-nya DILIPAT ke kalimat ini, bukan pita tersendiri.

                 Ini layanan tanpa angkatan, jadi "kapan bisanya?" pertanyaan
                 pertama yang muncul dan harus terjawab di layar pertama —
                 itu tidak berubah. Yang berubah bentuknya.

                 Sampai 9 Nov 2026 ia berdiri sebagai pita sendiri di bawah
                 kalimat pembuka: terukur 56px kotaknya ditambah 34px jarak
                 atas-bawahnya, 90px untuk dua potong keterangan yang muat
                 dalam satu kalimat. Scopus Camp — yang halaman ini memang
                 diminta mengikutinya — melipat keterangan serupa ("Ada 1
                 angkatan yang sedang dibuka") ke ujung kalimat pembukanya,
                 bukan memberinya kotak sendiri. --}}
            <p class="kfd-sub">
                Lima jam berdua dengan trainer Rumah Scopus — bukan kelas, bukan ceramah.
                Naskahmu sendiri yang dibedah, pertanyaanmu sendiri yang dijawab.
                Dibuka setiap <strong>{{ $hariBuka }}</strong>; yang terdekat
                <strong>{{ $tanggalBerikutnya->locale('id')->translatedFormat('j F Y') }}</strong>.
            </p>
        </div>
    </section>

    <section class="kfd-bagian">
        <div class="container">

            {{-- TANPA judul bagian "Pilih bentuk dan sesinya" beserta
                 kalimat bawahnya.

                 Scopus Camp melompat dari kalimat pembuka langsung ke
                 kartunya; judul kedua di antaranya cuma mengulang apa yang
                 sudah terbaca dari kartu-kartu di bawahnya sendiri. Terukur
                 110px untuk judul dan dua baris yang tidak menambah satu
                 keterangan pun.

                 Yang BENAR-BENAR baru di kalimat itu — boleh ambil lebih
                 dari satu sesi, tagihannya dijumlahkan — dipindahkan ke
                 kalimat di bawah tombol pesan, tempat ia benar-benar
                 dibutuhkan. --}}
            @if ($varian !== [])
                {{-- Dikelompokkan per varian, bukan satu deret kartu.

                     Sampai 31 Okt 2026 halaman ini memajang SATU daftar sesi
                     untuk semua orang, dibaca dari tabel `meme`. Yang tatap
                     muka dan yang daring terbaca sebagai pilihan yang sama
                     padahal jam dan harganya berbeda — dan harga yang
                     tertulis cuma salah satunya. --}}
                @foreach ($varian as $v)
                    <section class="kfd-varian">
                        {{-- SATU BARIS, bukan judul bertingkat.

                             Nama varian, tempatnya, dan harganya sebelumnya
                             mengambil dua blok (h3 + paragraf) — 240px per
                             varian, dua varian jadi 480px untuk enam potong
                             keterangan yang semuanya pendek. Dijadikan satu
                             baris, ketiganya tetap terbaca dan halamannya
                             memendek. --}}
                        <header class="kfd-varian-kepala">
                            <h3 class="kfd-varian-nama">
                                <i class="bi {{ $v['kunci'] === 'online' ? 'bi-laptop' : 'bi-geo-alt' }}"></i>
                                {{ $v['nama'] }}
                                <span class="kfd-varian-ket">
                                    {{ $v['lokasi'] }} ·
                                    <strong>Rp {{ number_format($v['biaya'], 0, ',', '.') }}</strong> per sesi
                                </span>
                            </h3>
                        </header>

                        <div class="kfd-sesi">
                            @foreach ($v['sesi'] as $s)
                                <article class="kfd-kartu">
                                    <div class="kfd-jam">
                                        <span class="kfd-jam-angka">{{ $s['mulai'] }}</span>
                                        <span class="kfd-jam-label">mulai</span>
                                    </div>

                                    {{-- namaSesi: isinya di Tarif Layanan boleh
                                         berhuruf kecil semua ("sesi 1"), dan
                                         dicetak apa adanya ia terbaca seperti
                                         catatan yang belum selesai. --}}
                                    <h4 class="kfd-nama">{{ \App\Support\HargaKafe::namaSesi($s['nama']) }}</h4>

                                    <ul class="kfd-ciri">
                                        <li>
                                            <i class="bi bi-clock"></i>
                                            {{ $s['mulai'] }}–{{ $s['selesai'] }}
                                        </li>
                                        <li><i class="bi bi-geo-alt"></i> {{ $v['lokasi'] }}</li>
                                    </ul>

                                    <div class="kfd-harga">
                                        <span class="kfd-harga-angka">
                                            Rp {{ number_format($v['biaya'], 0, ',', '.') }}
                                        </span>
                                        <span class="kfd-harga-ket">per sesi</span>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <div class="kfd-ajak">
                    @if ($gerbangSiap)
                        <a href="{{ route('public.scopuskafe.formpendaftaran') }}" class="kfd-tombol">
                            <i class="bi bi-pencil-square"></i> Pesan sekarang
                        </a>
                        <p class="kfd-ajak-kata">
                            Boleh ambil lebih dari satu sesi — tagihannya dijumlahkan jadi
                            satu. Isinya sebentar saja, lalu Anda langsung diantar ke
                            halaman pembayaran.
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

            {{-- Dua blok penutup SEJAJAR di layar lebar — susunan yang sama
                 dengan Scopus Camp. Ditumpuk, keduanya memakan dua blok penuh
                 padahal kartu lacak cuma setinggi satu isian dan separuh
                 lebarnya kosong. --}}
            <div class="kfd-bawah">

            {{-- DAFTAR CENTANG DI DALAM SATU KARTU, bukan empat ubin berikon.

                 Bentuk ubinnya — ikon besar, judul, kalimat, masing-masing
                 berkotak sendiri — terukur 422px, dan dengan judul bagian di
                 atasnya 502px. Scopus Camp menyampaikan tujuh butir yang
                 setara dalam satu kartu setinggi 211px, sebab butirnya cuma
                 satu baris bertanda centang dan kartunya berlajur dua.

                 Halaman ini diminta mengikuti bentuk itu, dan isinya memang
                 cocok: keempat butir di bawah kalimat pendek semua. Ikonnya
                 tidak hilang — ia jadi tanda centang yang sama untuk semua,
                 sebab yang membedakan keempatnya teksnya, bukan gambarnya. --}}
            <div class="kfd-panel">
                <div class="kfd-panel-kartu">
                    <h2>Yang kamu dapat</h2>
                    <ul class="kfd-fasilitas">
                        @foreach ([
                            'Lima jam penuh — satu sesi utuh, bukan konsultasi sepuluh menit',
                            'Naskahmu sendiri yang dibedah, bukan materi umum',
                            'Trainer yang sudah menembus jurnal terindeks Scopus',
                            'Tempat dan kopinya di Rumah Scopus Pusat, Yogyakarta',
                        ] as $butir)
                            <li><i class="bi bi-check-circle-fill"></i> <span>{{ $butir }}</span></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Jalan kembali bagi yang kehilangan tautannya.

                 Alamat halaman status memuat UUID yang tidak bisa ditebak,
                 jadi yang menutup tabnya dan tidak menerima suratnya
                 kehilangan pemesanannya — termasuk tombol bayarnya. Tautannya
                 dikirim ke EMAIL, tidak ke layar: ditampilkan, siapa pun yang
                 menebak alamat email orang lain bisa membuka pemesanannya. --}}
            <form method="POST" action="{{ route('public.scopuskafe.tautansaya') }}" class="kfd-lacak">
                @csrf
                {{-- Ikon dan kata dibungkus satu baris, seperti kartu lacak
                     Scopus Camp: ikon di kiri, judul dan kalimatnya di
                     kanan. Terpusat dan bertingkat, kartunya lebih tinggi
                     tanpa memuat apa pun yang tambahan. --}}
                <div class="kfd-lacak-sapa">
                    <span class="kfd-lacak-ikon"><i class="bi bi-ticket-detailed"></i></span>
                    <div class="kfd-lacak-kata">
                        <strong>Sudah pernah memesan?</strong>
                        <span>Masukkan email Anda, tautan pemesanannya kami kirim ke sana.</span>
                    </div>
                </div>
                <div class="kfd-lacak-isian">
                    <label for="kfdLacakEmail" class="visually-hidden">Alamat email</label>
                    <input type="email" id="kfdLacakEmail" name="email" required
                        autocomplete="email" placeholder="nama@email.com" value="{{ old('email') }}">
                    <button type="submit"><i class="bi bi-envelope"></i> Kirim tautannya</button>
                </div>
            </form>

            </div>
        </div>
    </section>
</div>
@stop
