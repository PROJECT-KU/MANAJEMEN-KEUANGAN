@extends('public.layout.header')

@section('title', 'Blog | Rumah Scopus')

@push('kepala')
{{-- Huruf yang SAMA dengan beranda: Fraunces untuk judul, Figtree untuk
     teks. Tanpa ini halaman blog berganti rupa begitu orang menekan menu
     Blog — dan pergantian huruf di tengah jalan terbaca sebagai pindah
     situs, bukan sebagai pindah halaman. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600..900&family=Figtree:wght@400;500;700;800&display=swap">
@endpush

@section('konten')
{{--
    Blog Rumah Scopus — dirombak 19 Okt 2026 mengikuti beranda.

    Yang diganti dan kenapa:

      - PALETNYA. Halaman ini memakai gradien merah-jingga #ff3131 -> #ff914d
        di atas putih: palet lama yang sudah ditinggalkan beranda justru
        karena pemiliknya menyebutnya terlalu mirip situs lain. Satu situs
        dengan dua palet terbaca sebagai dua situs.

      - LAYAR PERTAMANYA. Terukur hero-nya 900px dengan isi 387px — 513px
        putih kosong yang harus digulung orang sebelum melihat satu judul
        pun. Tingginya datang dari #hero{height:900px} milik templat; halaman
        ini tidak memakai #hero lagi sama sekali.

      - KALIMATNYA. Layar pertamanya dulu berisi kutipan motivasi dan tiga
        janji — "Sertifikat Resmi" dan "Akses Selamanya" — yang tidak ada
        hubungannya dengan membaca blog; itu janji kelas berbayar, dipasang
        di halaman yang isinya bacaan gratis. Tombolnya pun berbunyi
        "Langganan Update Artikel" lalu membuka kanal YouTube.

      - SUSUNANNYA. Dulu dikelompokkan per kategori lalu take(3). Kategori
        Journal punya lima tulisan, jadi dua di antaranya tidak pernah
        muncul di /blog. Sekarang semuanya terpajang: satu yang terbaru
        dibesarkan, sisanya berkisi.
--}}
@include('public.article.partials.gaya')
<div class="blg">

    {{-- ==================================================== kepala ===== --}}
    <section class="blg-kepala">
        <div class="container">
            <span class="blg-stiker"><i class="bi bi-journal-text"></i> Bacaan gratis</span>

            <h1 class="blg-judul">
                Yang biasanya bikin naskah <span class="blg-stabilo">mandek</span>
            </h1>

            <p class="blg-sub">
                Catatan soal nulis artikel, milih jurnal, ngolah data, sampai
                hal-hal kecil yang sering bikin naskah balik lagi dari reviewer.
                {{ $artikel->count() }} tulisan, semuanya boleh dibaca siapa saja.
            </p>

            @if ($categories_artikel->isNotEmpty())
                {{-- Keripik kategori, bukan tab: tiap satunya halaman sendiri
                     yang memang sudah ada rutenya. Yang kosong sudah disaring
                     di pengendali, jadi tidak ada yang mengantar ke halaman
                     tanpa isi. --}}
                <div class="blg-keripik">
                    @foreach ($categories_artikel as $i => $kategori)
                        <a href="{{ route('blog.topic.kategori', ['categories_artikel_id' => $kategori->id]) }}"
                            class="blg-keripik-butir" style="--c: {{ ['var(--jingga)', 'var(--rumput)', 'var(--ungu)', 'var(--langit)', '#be185d'][$i % 5] }}">
                            {{ $kategori->kategori }}
                            <span>{{ $jumlahPerKategori[$kategori->id] ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @php
        $sorotan = $artikel->first();
        $sisanya = $artikel->skip(1);

        /*
         * Lama baca dihitung dari isinya, bukan ditulis admin.
         *
         * 200 kata per menit — patokan yang lazim untuk teks non-fiksi
         * berbahasa Indonesia. Markahnya dibuang dulu; tanpa itu, tulisan
         * yang banyak <p> dan <strong>-nya terhitung dua kali lebih panjang
         * daripada yang benar-benar dibaca orang.
         */
        $lamaBaca = function ($isi) {
            $kata = str_word_count(trim(strip_tags((string) $isi)));

            return max(1, (int) ceil($kata / 200));
        };
    @endphp

    {{-- =================================================== sorotan ===== --}}
    @if ($sorotan)
        <section class="blg-bagian">
            <div class="container">
                <a href="{{ route('blog.topic.blog-single', ['id' => $sorotan->id]) }}" class="blg-sorot">
                    <span class="blg-sorot-gambar">
                        {{-- fetchpriority high + TANPA lazy: ini gambar terbesar di
                             layar pertama sesudah digulung sedikit, dan menundanya
                             membuat kotaknya kosong justru saat orang menatapnya. --}}
                        <img src="{{ asset('images/' . $sorotan->gambar_depan) }}"
                            alt="{{ $sorotan->judul }}" fetchpriority="high" decoding="async">
                    </span>

                    <span class="blg-sorot-isi">
                        <span class="blg-tanda">Terbaru</span>

                        <h2>{{ $sorotan->judul }}</h2>

                        <span class="blg-cuplik">
                            {{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $sorotan->isi)), 190) }}
                        </span>

                        <span class="blg-meta">
                            @if ($sorotan->kategori)
                                <span><i class="bi bi-tag"></i> {{ $sorotan->kategori }}</span>
                            @endif
                            <span><i class="bi bi-calendar3"></i>
                                {{ \Carbon\Carbon::parse($sorotan->created_at)->locale('id')->translatedFormat('j M Y') }}</span>
                            <span><i class="bi bi-clock"></i> {{ $lamaBaca($sorotan->isi) }} menit baca</span>
                        </span>

                        <span class="blg-ajak">Baca sekarang <i class="bi bi-arrow-right"></i></span>
                    </span>
                </a>
            </div>
        </section>
    @endif

    {{-- ================================================== sisanya ====== --}}
    @if ($sisanya->isNotEmpty())
        <section class="blg-bagian" style="background: var(--kertas-2); padding-top: 62px;">
            <div class="container">
                <h2 class="blg-judul-kecil">Tulisan lainnya</h2>

                <div class="blg-kisi">
                    @foreach ($sisanya as $i => $artikelnya)
                        <a href="{{ route('blog.topic.blog-single', ['id' => $artikelnya->id]) }}"
                            class="blg-kartu" data-aos="fade-up" data-aos-delay="{{ ($i % 3) * 90 }}">
                            <span class="blg-kartu-gambar">
                                <img src="{{ asset('images/' . $artikelnya->gambar_depan) }}"
                                    alt="{{ $artikelnya->judul }}" loading="lazy" decoding="async">
                                @if ($artikelnya->kategori)
                                    <span class="blg-kartu-tag">{{ $artikelnya->kategori }}</span>
                                @endif
                            </span>

                            <span class="blg-kartu-isi">
                                <span class="blg-meta">
                                    <span><i class="bi bi-calendar3"></i>
                                        {{ \Carbon\Carbon::parse($artikelnya->created_at)->locale('id')->translatedFormat('j M Y') }}</span>
                                    <span><i class="bi bi-clock"></i> {{ $lamaBaca($artikelnya->isi) }} menit</span>
                                </span>

                                <h3>{{ $artikelnya->judul }}</h3>

                                <span class="blg-kartu-kaki">
                                    <span class="blg-penulis">
                                        <img src="{{ $artikelnya->gambar ? \App\Support\FotoProfil::url($artikelnya->gambar) : asset('assets/img/avatar/avatar-1.png') }}"
                                            alt="" loading="lazy">
                                        {{ \Illuminate\Support\Str::limit($artikelnya->full_name, 16) }}
                                    </span>
                                    <span class="blg-ajak-kecil">Baca <i class="bi bi-arrow-right"></i></span>
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- =================================================== ajakan ====== --}}
    <section class="blg-bagian">
        <div class="container">
            <div class="blg-ajakan">
                <h2>Udah baca-baca, sekarang gimana?</h2>
                <p>
                    Bacaan bisa bantu sampai titik tertentu. Selebihnya naskahmu
                    perlu dibedah bareng orang yang sudah sering menembus jurnalnya.
                </p>
                <div class="blg-aksi">
                    <a href="{{ url('/#jadwal') }}" class="blg-tombol blg-tombol-utama">
                        Lihat jadwal kelas <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="{{ url('/contact') }}" class="blg-tombol blg-tombol-kedua">
                        <i class="bi bi-chat-dots-fill"></i> Tanya dulu
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@stop
