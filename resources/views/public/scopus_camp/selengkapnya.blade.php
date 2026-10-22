@extends('public.layout.header')

@section('title', $item->nama . ' #' . $item->nama_ke . ' | Rumah Scopus')

@section('uraian', \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $item->desc_promo))), 155))

@push('kepala')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600..900&family=Figtree:wght@400;500;700;800&display=swap">
@endpush

@section('konten')
{{--
    Detail satu angkatan Scopus Camp — dirombak 22 Okt 2026.

    Sasarannya orang berusia lanjut, jadi yang dikejar SEDIKIT KLIK dan
    HURUF BESAR, bukan kepadatan informasi:

      - tombol daftarnya ada DUA KALI (atas dan bawah) dan menempel di
        bawah layar ponsel, supaya tidak perlu dicari dengan menggulung;
      - hurufnya 18px, tombolnya 56px — di atas patokan sasaran sentuh 44px;
      - fasilitasnya dipajang di sini, bukan cuma di halaman daftar, supaya
        tidak perlu bolak-balik;
      - semua tanggal ditulis lengkap dalam Bahasa Indonesia.

    Yang diperbaiki dari versi lama:

      - pita merah #ff3131 dan huruf Open Sans — palet yang sudah
        ditinggalkan seluruh situs;
      - date('d M Y', strtotime($item->selesai)) memulangkan "01 Jan 1970"
        begitu `selesai` kosong, dan itu BENAR-BENAR tampil di bilah kanan;
      - gambar cadangan ScopusCamp/no-image.jpg tidak ada berkasnya —
        terukur 6 gambar rusak di satu halaman.
--}}
<style>
    .cmp {
        --kertas: #fdf5e8;  --kertas-2: #f8ead4;
        --tinta: #16233f;   --tinta-2: #5c6b85;
        --jingga: #e76d20;  --jingga-muda: #fb8c3d; --jingga-tua: #ad4d12;
        --jingga-kuat: #b85213;
        --stabilo: #ffd84d; --rumput: #0f766e; --ungu: #5b3fd6;

        background: var(--kertas); color: var(--tinta);
        font-family: 'Figtree', system-ui, sans-serif;
        /* 18px, bukan 16px. Pembacanya rata-rata berusia lanjut; ukuran
           bawaan templat terbaca kecil di layar laptop mereka. */
        font-size: 18px;
        overflow-x: clip;
    }
    .cmp h1, .cmp h2, .cmp h3 {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 800; color: var(--tinta); margin: 0; letter-spacing: -.01em;
    }
    .cmp p { margin: 0; color: var(--tinta-2); line-height: 1.7; }
    .cmp a { text-decoration: none; }

    .cmp-kepala {
        padding: 118px 0 34px;
        background: linear-gradient(180deg, var(--kertas-2) 0%, var(--kertas) 100%);
    }
    .cmp-balik {
        display: inline-flex; align-items: center; gap: 8px; margin-bottom: 14px;
        color: var(--tinta-2); font-weight: 700; font-size: .95rem;
        transition: gap .2s ease, color .2s ease;
    }
    .cmp-balik:hover { color: var(--jingga-tua); gap: 13px; }
    .cmp .cmp-judul { font-size: clamp(1.7rem, 3.6vw, 2.5rem); line-height: 1.16; }
    .cmp-tanda {
        display: inline-flex; align-items: center; gap: 7px;
        margin-top: 12px; padding: 5px 14px; border-radius: 999px;
        background: var(--tinta); color: var(--kertas);
        font-size: .86rem; font-weight: 800;
    }

    .cmp-bagian { padding: 10px 0 56px; }
    .cmp-kisi { display: grid; grid-template-columns: minmax(0, 1fr) 330px; gap: 40px; }

    .cmp-kartu {
        background: var(--kertas-2);
        border: 2px solid var(--tinta); border-radius: 24px;
        box-shadow: 8px 8px 0 var(--jingga);
        overflow: hidden;
    }
    /* Nisbah sampulnya DIBIARKAN apa adanya.

       Sempat dipatok 16/9 dengan object-fit: cover, dan flyer Scopus Camp
       berbentuk potret — jadi yang tampil cuma pita tengahnya: judulnya
       terpotong di atas, nomor kontaknya terpotong di bawah. Yang
       dipotong justru bagian yang paling dicari orang. */
    .cmp-sampul {
        display: flex; justify-content: center;
        padding: 14px; background: var(--kertas);
    }
    .cmp-sampul img {
        max-width: 100%; max-height: 560px; width: auto; height: auto;
        display: block; border-radius: 12px;
    }

    .cmp-isi { padding: 26px 28px 30px; }

    /* Tiga keterangan pokok, masing-masing sebesar kartu sendiri. */
    .cmp-pokok {
        display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px;
        margin-bottom: 26px;
    }
    .cmp-pokok-butir {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 16px 16px;
        background: var(--kertas);
        border: 2px solid var(--tinta); border-radius: 16px;
    }
    .cmp-pokok-ikon {
        flex: 0 0 auto;
        display: inline-flex; align-items: center; justify-content: center;
        width: 42px; height: 42px; border-radius: 12px;
        background: var(--c, var(--jingga)); color: #fff; font-size: 1.05rem;
    }
    .cmp-pokok-teks { display: flex; flex-direction: column; min-width: 0; }
    .cmp-pokok-label {
        color: var(--tinta-2); font-size: .8rem; font-weight: 700;
        letter-spacing: .06em; text-transform: uppercase;
    }
    .cmp-pokok-nilai { font-weight: 800; font-size: 1.02rem; line-height: 1.35; }
    .cmp-coret { color: var(--tinta-2); font-weight: 600; font-size: .86rem; }

    .cmp .cmp-sub-judul {
        display: flex; align-items: center; gap: 10px;
        font-size: 1.22rem; margin: 30px 0 14px;
    }
    .cmp .cmp-sub-judul i { color: var(--jingga); }

    .cmp-fasilitas { list-style: none; padding: 0; margin: 0; display: grid; gap: 10px; }
    @media (min-width: 700px) { .cmp-fasilitas { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .cmp-fasilitas li {
        display: flex; gap: 10px; align-items: flex-start;
        padding: 12px 14px;
        background: var(--kertas); border: 2px solid rgba(22, 35, 63, .14);
        border-radius: 13px; line-height: 1.45;
    }
    .cmp-fasilitas i { flex: 0 0 auto; margin-top: 4px; color: var(--rumput); }

    .cmp-uraian { white-space: pre-line; line-height: 1.75; }

    /* ======================================================== tombol */
    .cmp-tombol {
        display: inline-flex; align-items: center; justify-content: center; gap: 11px;
        width: 100%; min-height: 56px; padding: 15px 26px;
        background: var(--jingga-kuat); color: #fff;
        border: 2px solid var(--tinta); border-radius: 999px;
        box-shadow: 5px 5px 0 var(--tinta);
        font-weight: 800; font-size: 1.08rem;
        transition: transform .17s ease, box-shadow .17s ease;
    }
    .cmp-tombol:hover {
        color: #fff; background: var(--tinta);
        transform: translate(-2px, -2px); box-shadow: 8px 8px 0 var(--tinta);
    }
    .cmp-tombol-mati {
        background: var(--tinta-2); border-color: var(--tinta-2);
        box-shadow: none; cursor: not-allowed;
    }
    .cmp-tombol-mati:hover { background: var(--tinta-2); transform: none; box-shadow: none; }
    .cmp-tombol-atas { margin-bottom: 24px; }

    /* ================================================== bilah kanan */
    .cmp-panel {
        padding: 22px;
        background: var(--kertas-2);
        border: 2px solid var(--tinta); border-radius: 22px;
        box-shadow: 6px 6px 0 rgba(22, 35, 63, .16);
    }
    .cmp .cmp-panel h2 { font-size: 1.1rem; margin-bottom: 14px; }

    .cmp-lain { display: flex; flex-direction: column; gap: 10px; }
    .cmp-lain-butir {
        display: grid; grid-template-columns: 58px minmax(0, 1fr); gap: 12px;
        align-items: center; padding: 11px 13px;
        background: var(--kertas);
        border: 2px solid var(--tinta); border-radius: 14px;
        color: var(--tinta);
        transition: transform .16s ease, box-shadow .16s ease;
    }
    .cmp-lain-butir:hover { color: var(--tinta); transform: translate(-2px, -2px); box-shadow: 4px 4px 0 var(--jingga); }
    .cmp-lain-tgl {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: 7px 3px; border-radius: 11px;
        background: var(--jingga); color: #fff; line-height: 1;
    }
    .cmp-lain-tgl strong { font-family: 'Fraunces', serif; font-weight: 900; font-size: 1.3rem; }
    .cmp-lain-tgl span { margin-top: 3px; font-size: .66rem; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; }
    .cmp-lain-nama { font-weight: 800; font-size: .95rem; line-height: 1.3; }
    .cmp-lain-rinci { color: var(--tinta-2); font-size: .83rem; font-weight: 600; }
    .cmp-lain-kosong { color: var(--tinta-2); font-size: .93rem; }

    /* ============================================= pita ajakan ponsel */
    .cmp-pita { display: none; }
    @media (max-width: 991.98px) {
        .cmp-kisi { grid-template-columns: 1fr; gap: 28px; }
        .cmp-pokok { grid-template-columns: 1fr; }

        /* Tombol daftarnya SELALU terlihat di ponsel. Tanpa ini, orang yang
           sudah yakin di tengah halaman harus menggulung mencari tombolnya
           — dan halaman ini panjang karena deskripsinya panjang. */
        .cmp-pita {
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 99998;
            display: block; padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
            background: var(--kertas);
            border-top: 2px solid var(--tinta);
            box-shadow: 0 -4px 18px rgba(22, 35, 63, .12);
        }
        body { padding-bottom: 86px; }
        .kaki-wa { display: none !important; }
        .back-to-top { bottom: 96px !important; }
    }
    @media (max-width: 600px) {
        .cmp-kepala { padding: 98px 0 28px; }
        .cmp-isi { padding: 20px 18px 24px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .cmp *, .cmp *::before, .cmp *::after { transition: none !important; }
    }
</style>

@php
    use App\Support\RentangTanggal;

    $mulai = \Carbon\Carbon::parse($item->mulai)->locale('id');
    $selesai = $item->selesai ? \Carbon\Carbon::parse($item->selesai) : null;

    /*
     * Kuota NULL berarti TIDAK DIBATASI, bukan nol — syarat lamanya
     * `sisa_kuota === null || <= 0` menutup angkatan tanpa batas kuota.
     */
    $penuh = $item->total_kuota !== null && (int) $item->sisa_kuota < 1;

    $hargaAsal = (int) $item->harga_sebelum_promo;
    $hargaBayar = (int) $item->harga_setelah_promo;
    $adaDiskon = $hargaAsal > $hargaBayar && $hargaBayar > 0;

    $tautanDaftar = route('public.scopuscamp.formpendaftaran', ['id' => $item->id]);
@endphp

<div class="cmp">

    {{-- ==================================================== kepala ===== --}}
    <section class="cmp-kepala">
        <div class="container">
            <a href="{{ url('/Scopus-Camp') }}" class="cmp-balik">
                <i class="bi bi-arrow-left"></i> Semua jadwal Scopus Camp
            </a>

            <h1 class="cmp-judul">{{ $item->nama }} @if ($item->nama_ke)<span style="color: var(--tinta-2);">#{{ $item->nama_ke }}</span>@endif</h1>

            <span class="cmp-tanda">
                <i class="bi bi-geo-alt-fill" style="color: var(--stabilo);"></i>
                Kelas tatap muka · {{ RentangTanggal::tulis($mulai, $selesai) }}
            </span>
        </div>
    </section>

    {{-- ======================================================= isi ===== --}}
    <section class="cmp-bagian">
        <div class="container">
            <div class="cmp-kisi">

                <div>
                    {{-- Tombol daftar DI ATAS, sebelum orang menggulung apa pun. --}}
                    @if ($penuh)
                        <span class="cmp-tombol cmp-tombol-mati cmp-tombol-atas">
                            <i class="bi bi-lock-fill"></i> Kursinya sudah habis
                        </span>
                    @else
                        <a href="{{ $tautanDaftar }}" class="cmp-tombol cmp-tombol-atas">
                            <i class="bi bi-pencil-square"></i> Daftar sekarang
                        </a>
                    @endif

                    <article class="cmp-kartu">
                        <span class="cmp-sampul">
                            <img src="{{ \App\Support\AlamatGambar::url($item->gambar ?? null, 'ScopusCamp') ?: asset('assets/img/public/scopuscamp.png') }}"
                                alt="Sampul {{ $item->nama }}">
                        </span>

                        <div class="cmp-isi">
                            <div class="cmp-pokok">
                                <div class="cmp-pokok-butir" style="--c: var(--jingga);">
                                    <span class="cmp-pokok-ikon"><i class="bi bi-calendar3"></i></span>
                                    <span class="cmp-pokok-teks">
                                        <span class="cmp-pokok-label">Tanggal</span>
                                        <span class="cmp-pokok-nilai">{{ RentangTanggal::tulis($mulai, $selesai) }}</span>
                                    </span>
                                </div>

                                <div class="cmp-pokok-butir" style="--c: var(--rumput);">
                                    <span class="cmp-pokok-ikon"><i class="bi bi-cash-coin"></i></span>
                                    <span class="cmp-pokok-teks">
                                        <span class="cmp-pokok-label">Biaya</span>
                                        <span class="cmp-pokok-nilai">
                                            @if ($hargaBayar > 0)
                                                @if ($adaDiskon)
                                                    <s class="cmp-coret">Rp {{ number_format($hargaAsal, 0, ',', '.') }}</s><br>
                                                @endif
                                                Rp {{ number_format($hargaBayar, 0, ',', '.') }}
                                            @else
                                                Hubungi admin
                                            @endif
                                        </span>
                                    </span>
                                </div>

                                <div class="cmp-pokok-butir" style="--c: var(--ungu);">
                                    <span class="cmp-pokok-ikon"><i class="bi bi-geo-alt"></i></span>
                                    <span class="cmp-pokok-teks">
                                        <span class="cmp-pokok-label">Lokasi</span>
                                        <span class="cmp-pokok-nilai">{{ $item->lokasi ?: 'Akan dikabari panitia' }}</span>
                                    </span>
                                </div>
                            </div>

                            {{-- Fasilitas dipajang DI SINI, bukan cuma di halaman
                                 daftar — dan diambil dari angkatan ini sendiri,
                                 jadi angkatan luar Jawa menampilkan paketnya
                                 sendiri yang memang tidak memuat penginapan. --}}
                            @if (! empty($item->daftar_fasilitas))
                                <h2 class="cmp-sub-judul"><i class="bi bi-check2-circle"></i> Yang kamu dapat</h2>
                                <ul class="cmp-fasilitas">
                                    @foreach ($item->daftar_fasilitas as $butir)
                                        <li><i class="bi bi-check-circle-fill"></i> <span>{{ $butir }}</span></li>
                                    @endforeach
                                </ul>
                            @endif

                            @if (trim((string) $item->desc_promo) !== '')
                                <h2 class="cmp-sub-judul"><i class="bi bi-info-circle"></i> Keterangan kegiatan</h2>
                                {{-- desc_promo: harga di dalam naskahnya ikut
                                     menyesuaikan promo yang sedang berjalan. --}}
                                <p class="cmp-uraian">{{ $item->desc_promo }}</p>
                            @endif

                            <div style="margin-top: 30px;">
                                @if ($penuh)
                                    <span class="cmp-tombol cmp-tombol-mati">
                                        <i class="bi bi-lock-fill"></i> Kursinya sudah habis
                                    </span>
                                @else
                                    <a href="{{ $tautanDaftar }}" class="cmp-tombol">
                                        <i class="bi bi-pencil-square"></i> Daftar sekarang
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                </div>

                {{-- =============================================== bilah --}}
                <aside>
                    <div class="cmp-panel">
                        <h2>Jadwal lain yang masih dibuka</h2>

                        @if ($terbaru->isEmpty())
                            <p class="cmp-lain-kosong">
                                Untuk saat ini cuma angkatan ini yang dibuka.
                            </p>
                        @else
                            <div class="cmp-lain">
                                @foreach ($terbaru as $lain)
                                    @php
                                        $lainMulai = \Carbon\Carbon::parse($lain->mulai)->locale('id');
                                        /* selesai boleh KOSONG. date('d M Y',
                                           strtotime(null)) memulangkan "01 Jan
                                           1970", dan itu benar-benar tampil di
                                           versi lama bilah ini. */
                                        $lainSelesai = $lain->selesai ? \Carbon\Carbon::parse($lain->selesai) : null;
                                    @endphp
                                    <a href="{{ route('public.scopuscamp.Selengkapnya', ['id' => $lain->id]) }}"
                                        class="cmp-lain-butir">
                                        <span class="cmp-lain-tgl">
                                            <strong>{{ $lainMulai->translatedFormat('j') }}</strong>
                                            <span>{{ $lainMulai->translatedFormat('M') }}</span>
                                        </span>
                                        <span>
                                            <span class="cmp-lain-nama">{{ $lain->nama }}</span><br>
                                            <span class="cmp-lain-rinci">
                                                {{ RentangTanggal::tulis($lainMulai, $lainSelesai, true) }}
                                                @if ($lain->lokasi) · {{ $lain->lokasi }} @endif
                                            </span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </aside>

            </div>
        </div>
    </section>

    {{-- Pita ajakan yang menempel di bawah layar ponsel. --}}
    <div class="cmp-pita">
        @if ($penuh)
            <span class="cmp-tombol cmp-tombol-mati"><i class="bi bi-lock-fill"></i> Kursinya sudah habis</span>
        @else
            <a href="{{ $tautanDaftar }}" class="cmp-tombol">
                <i class="bi bi-pencil-square"></i> Daftar sekarang
            </a>
        @endif
    </div>

</div>
@stop
