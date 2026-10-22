@extends('public.layout.header')

@section('title', 'Blog | Rumah Scopus')

@section('uraian', 'Catatan soal nulis artikel ilmiah, milih jurnal, ngolah data, sampai hal-hal yang sering bikin naskah balik lagi dari reviewer. Gratis dibaca siapa saja.')

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
                {{ $halaman->total() }} tulisan, semuanya boleh dibaca siapa saja.
            </p>

            {{-- Satu-satunya cara menyaring sebelumnya cuma lima keripik
                 kategori. Dua belas tulisan masih bisa dipindai mata; lima
                 puluh tidak. --}}
            <form class="blg-cari" method="GET" action="{{ url('/blog') }}" role="search">
                <i class="bi bi-search"></i>
                <input type="search" name="cari" value="{{ $cari }}"
                    placeholder="Cari tulisan — misalnya sitasi, reviewer, jurnal predator"
                    aria-label="Cari tulisan">
                @if ($cari !== '')
                    <a href="{{ url('/blog') }}" class="blg-cari-hapus" aria-label="Hapus pencarian">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
                <button type="submit">Cari</button>
            </form>

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
        /*
         * Sorotan cuma di halaman pertama daftar biasa.
         *
         * Pada hasil pencarian, membesarkan yang teratas berarti menjanjikan
         * bahwa ia paling cocok — padahal urutannya menurut tanggal, bukan
         * menurut kecocokan. Pada halaman kedua dan seterusnya, "Terbaru"
         * jelas salah.
         */
        $halamanPertama = $halaman->currentPage() === 1 && $cari === '';

        $sorotan = $halamanPertama ? $artikel->first() : null;
        $sisanya = $halamanPertama ? $artikel->skip(1) : $artikel;

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
                            @if ($sorotan->dilihat > 0)
                                <span><i class="bi bi-eye"></i> dibaca {{ number_format($sorotan->dilihat, 0, ',', '.') }} kali</span>
                            @endif
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
                <h2 class="blg-judul-kecil">
                    @if ($cari !== '')
                        {{ $halaman->total() }} hasil untuk "{{ $cari }}"
                    @elseif ($halamanPertama)
                        Tulisan lainnya
                    @else
                        Halaman {{ $halaman->currentPage() }} dari {{ $halaman->lastPage() }}
                    @endif
                </h2>

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
                                    @if ($artikelnya->dilihat > 0)
                                        <span><i class="bi bi-eye"></i> {{ number_format($artikelnya->dilihat, 0, ',', '.') }}</span>
                                    @endif
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

                @if ($halaman->hasPages())
                    <div class="blg-halaman">
                        {{ $halaman->links() }}
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Daftarnya KOSONG — karena dicari dan tidak ketemu, atau karena
         memang belum ada tulisan terbit sama sekali. Kisi kosong tanpa
         kalimat terbaca sebagai halaman yang gagal dimuat, bukan sebagai
         jawaban. --}}
    @if ($sorotan === null && $sisanya->isEmpty())
        <section class="blg-bagian" style="background: var(--kertas-2);">
            <div class="container">
                <div class="blg-kosong">
                    <i class="bi bi-search"></i>
                    @if ($cari !== '')
                        <h2>Nggak ketemu</h2>
                        <p>Nggak ada tulisan yang memuat "{{ $cari }}". Coba kata lain, atau lihat semuanya.</p>
                        <a href="{{ url('/blog') }}" class="blg-tombol blg-tombol-kedua">
                            <i class="bi bi-arrow-left"></i> Lihat semua tulisan
                        </a>
                    @else
                        <h2>Belum ada tulisan</h2>
                        <p>Blognya masih kosong. Sementara ini, kelasnya yang bisa dilihat dulu.</p>
                        <a href="{{ url('/#jadwal') }}" class="blg-tombol blg-tombol-kedua">
                            Lihat jadwal kelas <i class="bi bi-arrow-right"></i>
                        </a>
                    @endif
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

                {{-- Satu-satunya cara "berlangganan" dulu adalah tombol yang
                     berbunyi "Langganan Update Artikel" lalu membuka kanal
                     YouTube — bukan tempat tulisannya terbit. --}}
                <p class="blg-umpan">
                    Mau dikabari tiap ada tulisan baru?
                    <a href="{{ route('blog.rss') }}"><i class="bi bi-rss-fill"></i> Langganan lewat RSS</a>
                </p>
            </div>
        </div>
    </section>

</div>
@stop
