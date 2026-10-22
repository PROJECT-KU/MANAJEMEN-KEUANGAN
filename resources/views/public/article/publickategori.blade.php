@extends('public.layout.header')

@section('title', ($kategoriIni?->kategori ?? 'Kategori') . ' | Blog Rumah Scopus')

@section('uraian', $totalArticles . ' tulisan Rumah Scopus tentang ' . ($kategoriIni?->kategori ?? 'topik ini') . ' — seputar penulisan artikel ilmiah dan publikasi ke jurnal terindeks Scopus.')

@push('kepala')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600..900&family=Figtree:wght@400;500;700;800&display=swap">
@endpush

@section('konten')
{{--
    Halaman kategori blog — dirombak 19 Okt 2026, mengikuti /blog.

    Dirombak BARENGAN, bukan menyusul: keripik kategori di /blog mengantar
    ke sini, jadi halaman ini yang pertama dibuka orang sesudah melihat rupa
    barunya. Dibiarkan memakai gradien merah lama, perpindahannya terbaca
    seperti keluar dari situsnya.

    Gaya halamannya di-include dari partial yang sama dengan /blog. Berkas
    lamanya menaruh <style> MENGGANTUNG di luar @section — tetap tercetak,
    tetapi mendarat sebelum <!doctype html>.
--}}
@include('public.article.partials.gaya')

<div class="blg">

    {{-- ==================================================== kepala ===== --}}
    <section class="blg-kepala">
        <div class="container">
            {{-- Remah jejak: halaman ini satu-satunya layar blog yang bisa
                 dibuka tanpa lewat /blog — dari mesin pencari, misalnya —
                 dan tanpa jalan pulang orangnya tertahan di satu kategori. --}}
            <div class="blg-balik-baris">
                <a href="{{ url('/blog') }}" class="blg-balik">
                    <i class="bi bi-arrow-left"></i> Semua tulisan
                </a>
            </div>

            <span class="blg-stiker"><i class="bi bi-tag-fill"></i> Kategori</span>

            <h1 class="blg-judul">{{ $kategoriIni?->kategori ?? 'Kategori' }}</h1>

            <p class="blg-sub">
                @if ($totalArticles > 0)
                    {{ $totalArticles }} tulisan di kategori ini.
                @else
                    Belum ada tulisan di kategori ini.
                @endif
            </p>

            @if ($categories_artikel->isNotEmpty())
                <div class="blg-keripik">
                    @foreach ($categories_artikel as $i => $kategori)
                        <a href="{{ route('blog.topic.kategori', ['categories_artikel_id' => $kategori->id]) }}"
                            class="blg-keripik-butir {{ $kategori->id == $kategoriIni?->id ? 'blg-keripik-aktif' : '' }}"
                            style="--c: {{ ['var(--jingga)', 'var(--rumput)', 'var(--ungu)', 'var(--langit)', '#be185d'][$i % 5] }}">
                            {{ $kategori->kategori }}
                            <span>{{ $kategori->jumlah_artikel }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- =================================================== tulisan ===== --}}
    <section class="blg-bagian" style="background: var(--kertas-2);">
        <div class="container">
            @if ($articles->isEmpty())
                {{-- Kosong berbunyi, bukan diam. Kisi kosong tanpa kalimat
                     terbaca sebagai halaman yang gagal dimuat. --}}
                <div class="blg-kosong">
                    <i class="bi bi-journal-x"></i>
                    <h2>Belum ada tulisan di sini</h2>
                    <p>Kategorinya sudah dibuat, tulisannya belum. Coba lihat kategori lain dulu.</p>
                    <a href="{{ url('/blog') }}" class="blg-tombol blg-tombol-kedua">
                        <i class="bi bi-arrow-left"></i> Balik ke semua tulisan
                    </a>
                </div>
            @else
                @php
                    $lamaBaca = function ($isi) {
                        $kata = str_word_count(trim(strip_tags((string) $isi)));

                        return max(1, (int) ceil($kata / 200));
                    };
                @endphp

                <div class="blg-kisi">
                    @foreach ($articles as $i => $artikelnya)
                        <a href="{{ route('blog.topic.blog-single', ['id' => $artikelnya->id]) }}"
                            class="blg-kartu" data-aos="fade-up" data-aos-delay="{{ ($i % 3) * 90 }}">
                            <span class="blg-kartu-gambar">
                                {{-- Tiga kartu teratas TIDAK ditunda: ketiganya di layar
                                     pertama, dan menundanya membuat kotaknya kosong
                                     justru saat orang menatapnya. --}}
                                <img src="{{ asset('images/' . $artikelnya->gambar_depan) }}"
                                    alt="{{ $artikelnya->judul }}"
                                    loading="{{ $i < 3 ? 'eager' : 'lazy' }}" decoding="async">
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

                @if ($articles->hasPages())
                    <div class="blg-halaman">
                        {{ $articles->links() }}
                    </div>
                @endif
            @endif
        </div>
    </section>

</div>
@stop
