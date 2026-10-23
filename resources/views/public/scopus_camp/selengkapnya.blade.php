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
        /* 18px, bukan 16px bawaan templat: pembacanya rata-rata berusia
           lanjut, dan ukuran bawaannya terbaca kecil di layar mereka. */
        font-size: 18px;
        overflow-x: clip;
    }
    .cmp h1, .cmp h2, .cmp h3 {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 800; color: var(--tinta); margin: 0; letter-spacing: -.01em;
    }
    .cmp p { margin: 0; color: var(--tinta-2); line-height: 1.7; }
    .cmp a { text-decoration: none; }

    /* ========================================================== hero */
    .cmp-hero {
        padding: 112px 0 52px;
        background: linear-gradient(180deg, var(--kertas-2) 0%, var(--kertas) 100%);
    }
    .cmp-balik {
        display: inline-flex; align-items: center; gap: 8px; margin-bottom: 18px;
        color: var(--tinta-2); font-weight: 700; font-size: .95rem;
        transition: gap .2s ease, color .2s ease;
    }
    .cmp-balik:hover { color: var(--jingga-tua); gap: 13px; }

    /* Dua kolom TAK SAMA: keterangan lebih lebar daripada flyernya. */
    .cmp-hero-kisi {
        display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 46px;
        align-items: start;
    }

    .cmp-tanda {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 6px 15px; margin-bottom: 14px;
        background: var(--tinta); color: var(--kertas);
        border-radius: 999px; font-size: .84rem; font-weight: 800;
    }
    .cmp-tanda i { color: var(--stabilo); }

    .cmp .cmp-judul { font-size: clamp(1.75rem, 3.6vw, 2.6rem); line-height: 1.14; }
    .cmp-nomor { color: var(--tinta-2); }

    /* Empat keterangan pokok, SEBELUM tombolnya dan di layar pertama.
       Dulu tiga di antaranya baru muncul di 991px, di balik flyer setinggi
       588px — tiga hal yang justru paling dicari orang. */
    /* TIGA kolom, bukan dua.

       Kartunya tinggal tiga sesudah "Sisa kursi" dibuang; dibiarkan dua
       kolom, satu sel menganga kosong di kanan bawah — dan sel kosong di
       antara kartu berbingkai tebal terbaca seperti ada yang gagal dimuat. */
    .cmp-fakta {
        display: grid; grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px; margin: 22px 0 26px;
    }
    .cmp-fakta-butir {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 14px 16px;
        background: var(--kertas);
        border: 2px solid var(--tinta); border-radius: 16px;
        box-shadow: 4px 4px 0 var(--c, var(--jingga));
    }
    .cmp-fakta-ikon {
        flex: 0 0 auto;
        display: inline-flex; align-items: center; justify-content: center;
        width: 40px; height: 40px; border-radius: 12px;
        background: var(--c, var(--jingga)); color: #fff; font-size: 1rem;
    }
    .cmp-fakta-label {
        display: block; color: var(--tinta-2); font-size: .78rem; font-weight: 700;
        letter-spacing: .06em; text-transform: uppercase;
    }
    .cmp-fakta-nilai { display: block; font-weight: 800; font-size: 1.02rem; line-height: 1.35; }
    .cmp-coret { color: var(--tinta-2); font-weight: 600; font-size: .85rem; margin-right: 5px; }

    .cmp-tombol {
        display: inline-flex; align-items: center; justify-content: center; gap: 11px;
        width: 100%; min-height: 58px; padding: 16px 26px;
        background: var(--jingga-kuat); color: #fff;
        border: 2px solid var(--tinta); border-radius: 999px;
        box-shadow: 5px 5px 0 var(--tinta);
        font-weight: 800; font-size: 1.1rem;
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

    .cmp .cmp-tenang {
        display: flex; align-items: flex-start; gap: 9px;
        margin-top: 14px; font-size: .94rem;
    }
    .cmp-tenang i { margin-top: 4px; color: var(--rumput); }

    /* Flyer dimiringkan dengan bayangan padat — bahasa rupa yang sama
       dengan polaroid testimoni di beranda. */
    .cmp-flyer {
        margin: 0; padding: 12px;
        background: var(--kertas);
        border: 2px solid var(--tinta); border-radius: 18px;
        box-shadow: 9px 9px 0 var(--jingga);
        transform: rotate(1.4deg);
    }
    .cmp-flyer img { width: 100%; height: auto; display: block; border-radius: 10px; }

    /* ==================================================== pita tinta */
    .cmp-pita-tinta { padding: 52px 0; background: var(--tinta); }
    .cmp .cmp-judul-pita {
        color: var(--kertas); font-size: 1.6rem; margin-bottom: 22px; text-align: center;
    }
    .cmp-fasilitas {
        list-style: none; padding: 0; margin: 0;
        display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px;
    }
    .cmp-fasilitas li {
        display: flex; gap: 11px; align-items: flex-start;
        padding: 14px 16px;
        background: rgba(251, 245, 233, .07);
        border: 1px solid rgba(251, 245, 233, .18);
        border-radius: 14px;
        color: var(--kertas); line-height: 1.45; font-size: .98rem;
    }
    .cmp-fasilitas i { flex: 0 0 auto; margin-top: 3px; color: var(--stabilo); }

    /* ========================================================== isi */
    .cmp-bagian { padding: 52px 0; }
    .cmp-kisi { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 40px; }

    .cmp-kartu {
        padding: 26px 28px;
        background: var(--kertas-2);
        border: 2px solid var(--tinta); border-radius: 22px;
        box-shadow: 7px 7px 0 var(--jingga-muda);
    }
    .cmp .cmp-sub-judul {
        display: flex; align-items: center; gap: 10px;
        font-size: 1.22rem; margin-bottom: 14px;
    }
    .cmp .cmp-sub-judul i { color: var(--jingga); }
    .cmp-uraian { white-space: pre-line; line-height: 1.75; }

    /* ===================================================== langkah */
    .cmp-bagian-langkah { padding-top: 0; background: var(--kertas-2); padding-bottom: 54px; padding-top: 44px; }
    .cmp-langkah ol {
        list-style: none; padding: 0; margin: 0;
        display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px;
        align-items: stretch;
    }
    .cmp-langkah li {
        display: flex; align-items: flex-start; gap: 14px;
        padding: 16px 18px;
        background: var(--kertas);
        border: 2px solid var(--tinta); border-radius: 16px;
    }
    .cmp-langkah-nomor {
        flex: 0 0 auto;
        display: inline-flex; align-items: center; justify-content: center;
        width: 38px; height: 38px; border-radius: 50%;
        background: var(--jingga); color: #fff;
        font-family: 'Fraunces', serif; font-weight: 900; font-size: 1.1rem;
    }
    .cmp-langkah li strong { display: block; margin-bottom: 3px; font-size: 1.02rem; }
    .cmp-langkah li strong i { margin-right: 7px; color: var(--jingga-tua); }
    .cmp-langkah li > span > span { color: var(--tinta-2); font-size: .95rem; line-height: 1.55; }

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

    /* ============================================ pita ajakan bawah

       HANYA di layar sempit, dan itu permintaan pemiliknya: di layar lebar
       pitanya mengganggu.

       Dan ia memang benar — di sana pita itu tidak menyelesaikan apa pun.
       Halaman selebar 1440 memuat tombol daftar, keempat fakta pokok, dan
       bilah jadwal lain sekaligus, jadi tombolnya tidak pernah jauh. Pita
       melayang yang menutupi isi hanya menukar satu masalah yang tidak ada
       dengan satu masalah yang nyata.

       Di ponsel ia tetap dipakai: halaman ini 3.000px lebih, dan tanpa pita
       orang yang sudah yakin di tengah harus menggulung balik ke atas. */
    .cmp-pita { display: none; }

    @media (max-width: 991.98px) {
        .cmp-pita {
            display: block;
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 99998;
            padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
            background: var(--kertas);
            border-top: 2px solid var(--tinta);
            box-shadow: 0 -4px 18px rgba(22, 35, 63, .12);

            /* Diturunkan keluar layar, bukan display:none — supaya munculnya
               meluncur, tidak melompat. */
            transform: translateY(110%);
            transition: transform .22s ease;
        }
        .cmp-pita-tampil { transform: translateY(0); }

        /* Ruang di dasar halaman, HANYA di lebar yang punya pita.

           Pitanya melayang di atas isi; terukur ia menutupi 97px terakhir
           kaki halaman. Ruangnya tetap dipesan walau pita sedang
           tersembunyi: memberinya hanya saat pita muncul membuat seluruh
           halaman melompat 92px tepat ketika orang sedang menggulung.

           Di layar lebar ruang ini TIDAK dipesan — tanpa pita, 92px kosong
           di bawah kaki halaman cuma celah yang tidak ada gunanya. */
        body { padding-bottom: 92px; }
    }

    /* Hero menumpuk di bawah 1200px, bukan 992px.

       Dengan tiga kartu fakta, rentang 992–1199 jadi masalahnya sendiri:
       kolom kiri masih menyempit karena flyernya di sebelah, jadi ketiga
       kartu itu turun ke dua kolom dan satu sel menganga. Dinaikkan ke
       1200, flyernya pindah ke bawah lebih awal dan ketiga kartu dapat
       lebar penuh. */
    @media (max-width: 1199.98px) {
        .cmp-hero-kisi { grid-template-columns: 1fr; gap: 30px; }
        .cmp-flyer { max-width: 420px; margin: 0 auto; transform: none; }
    }

    @media (max-width: 991.98px) {
        .cmp-kisi { grid-template-columns: 1fr; gap: 30px; }
        .cmp-langkah ol { grid-template-columns: 1fr; }
        .cmp-fasilitas { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .kaki-wa { display: none !important; }
        .back-to-top { bottom: 96px !important; }
    }
    @media (max-width: 767.98px) {
        .cmp-fakta { grid-template-columns: 1fr; }
    }

    @media (max-width: 600px) {
        .cmp-hero { padding: 96px 0 40px; }
        .cmp-fasilitas { grid-template-columns: 1fr; }
        .cmp-kartu { padding: 20px 18px; }
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

    {{-- ====================================================== hero =====

         Susunannya DIBALIK dari versi sebelumnya, dan itu hasil pengukuran.

         Dulu urutannya: judul (0px), tombol (291px), flyer setinggi 588px,
         lalu BARU tanggal/biaya/lokasi di 991px. Artinya tiga keterangan
         yang paling dicari orang — kapan, berapa, di mana — berada di bawah
         lipatan layar, di balik gambar yang isinya mengulang daftar
         fasilitas yang juga sudah tertulis di bawahnya.

         Sekarang keempatnya duduk di layar pertama bersama tombolnya, dan
         flyernya jadi pendamping di sebelah kanan — bukan palang setinggi
         setengah layar yang harus dilewati dulu. --}}
    <section class="cmp-hero">
        <div class="container">
            <a href="{{ url('/Scopus-Camp') }}" class="cmp-balik">
                <i class="bi bi-arrow-left"></i> Semua jadwal Scopus Camp
            </a>

            <div class="cmp-hero-kisi">
                <div>
                    <span class="cmp-tanda">
                        <i class="bi bi-geo-alt-fill"></i> Kelas tatap muka · 3 hari
                    </span>

                    <h1 class="cmp-judul">{{ $item->nama }}@if ($item->nama_ke) <span class="cmp-nomor">#{{ $item->nama_ke }}</span>@endif</h1>

                    <div class="cmp-fakta">
                        <div class="cmp-fakta-butir" style="--c: var(--jingga);">
                            <span class="cmp-fakta-ikon"><i class="bi bi-calendar3"></i></span>
                            <span>
                                <span class="cmp-fakta-label">Tanggal</span>
                                <span class="cmp-fakta-nilai">{{ \App\Support\RentangTanggal::tulis($mulai, $selesai) }}</span>
                            </span>
                        </div>

                        <div class="cmp-fakta-butir" style="--c: var(--rumput);">
                            <span class="cmp-fakta-ikon"><i class="bi bi-cash-coin"></i></span>
                            <span>
                                <span class="cmp-fakta-label">Biaya per orang</span>
                                <span class="cmp-fakta-nilai">
                                    @if ($hargaBayar > 0)
                                        @if ($adaDiskon)
                                            <s class="cmp-coret">Rp {{ number_format($hargaAsal, 0, ',', '.') }}</s>
                                        @endif
                                        Rp {{ number_format($hargaBayar, 0, ',', '.') }}
                                    @else
                                        Hubungi admin
                                    @endif
                                </span>
                            </span>
                        </div>

                        <div class="cmp-fakta-butir" style="--c: var(--ungu);">
                            <span class="cmp-fakta-ikon"><i class="bi bi-geo-alt"></i></span>
                            <span>
                                <span class="cmp-fakta-label">Lokasi</span>
                                <span class="cmp-fakta-nilai">{{ $item->lokasi ?: 'Akan dikabari panitia' }}</span>
                            </span>
                        </div>

                        {{-- Kartu "Sisa kursi" DIBUANG atas permintaan pemiliknya.

                             Keadaan penuhnya tidak ikut hilang: tombol daftarnya
                             berubah jadi "Kursinya sudah habis" dan mati, dan
                             angkatan yang kuotanya habis memang sudah tidak
                             ditawarkan di halaman daftarnya. Jadi yang hilang
                             cuma angkanya, bukan kabarnya. --}}
                    </div>

                    @if ($penuh)
                        <span class="cmp-tombol cmp-tombol-mati cmp-tombol-atas">
                            <i class="bi bi-lock-fill"></i> Kursinya sudah habis
                        </span>
                    @else
                        <a href="{{ $tautanDaftar }}" class="cmp-tombol cmp-tombol-atas">
                            <i class="bi bi-pencil-square"></i> Daftar sekarang
                        </a>
                    @endif

                    <p class="cmp-tenang">
                        <i class="bi bi-shield-check"></i>
                        Isi borangnya sebentar saja, lalu transfer. Kalau bingung,
                        tekan tombol WhatsApp di pojok dan tanya admin dulu.
                    </p>
                </div>

                {{-- Flyer jadi PENDAMPING, bukan palang. Dimiringkan sedikit
                     dengan bayangan padat — bahasa rupa yang sama dengan
                     polaroid testimoni di beranda. --}}
                <figure class="cmp-flyer">
                    <img src="{{ \App\Support\AlamatGambar::url($item->gambar ?? null, 'ScopusCamp') ?: asset('assets/img/public/scopuscamp.png') }}"
                        alt="Flyer {{ $item->nama }}" loading="lazy" decoding="async">
                </figure>
            </div>
        </div>
    </section>

    {{-- ================================================== fasilitas ====

         Pita TINTA, bukan krem lagi.

         Seluruh halaman ini satu kolom krem dari atas sampai bawah, dan
         sesudah layar kedua mata berhenti membedakan bagian satu dari yang
         lain. Satu pita gelap di tengah memutus kemonotonan itu — dan yang
         dipilih untuk dipitakan adalah bagian yang paling menjual. --}}
    @if (! empty($item->daftar_fasilitas))
        <section class="cmp-pita-tinta">
            <div class="container">
                <h2 class="cmp-judul-pita">Yang kamu dapat</h2>
                <ul class="cmp-fasilitas">
                    @foreach ($item->daftar_fasilitas as $butir)
                        <li><i class="bi bi-check-circle-fill"></i> <span>{{ $butir }}</span></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ======================================================= isi ===== --}}
    <section class="cmp-bagian">
        <div class="container">
            <div class="cmp-kisi">

                <div>
                    @if (trim((string) $item->desc_promo) !== '')
                        <article class="cmp-kartu">
                            <h2 class="cmp-sub-judul"><i class="bi bi-info-circle"></i> Keterangan kegiatan</h2>
                            {{-- desc_promo: harga di dalam naskahnya ikut
                                 menyesuaikan promo yang sedang berjalan. --}}
                            <p class="cmp-uraian">{{ $item->desc_promo }}</p>
                        </article>
                    @endif

                </div>

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
                                                {{ \App\Support\RentangTanggal::tulis($lainMulai, $lainSelesai, true) }}
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

    {{-- ================================================== langkah =====

         DI LUAR kisi dua kolom, melebar penuh.

         Sebelumnya ia duduk di kolom kiri bersama keterangan kegiatan,
         sementara bilah kanan sudah habis jauh di atasnya — sisi kanan
         halaman jadi krem kosong memanjang sepanjang bagian ini. Di lebar
         penuh, ketiga langkahnya berjajar dan ruangnya terpakai. --}}
    <section class="cmp-bagian cmp-bagian-langkah">
        <div class="container">
            {{-- Tiga langkah sesudah menekan daftar.

                 Pembacanya berusia lanjut, dan yang paling membuat ragu
                 bukan harganya melainkan ketidaktahuan apa yang terjadi
                 sesudah tombolnya ditekan. --}}
            <div class="cmp-langkah">
                <h2 class="cmp-sub-judul"><i class="bi bi-signpost-split"></i> Sesudah menekan daftar</h2>
                <ol>
                    @foreach ([
                        ['bi-pencil-square', 'Isi borangnya', 'Nama, email, asal instansi, dan nomor WhatsApp. Empat isian saja.'],
                        ['bi-bank', 'Transfer biayanya', 'Nomor rekening dan nominal persisnya tertulis di halaman borang.'],
                        ['bi-chat-dots', 'Tunggu dikabari', 'Panitia mengecek bukti transfermu, lalu menghubungi lewat WhatsApp.'],
                    ] as $i => [$ikon, $judul, $ket])
                        <li>
                            <span class="cmp-langkah-nomor">{{ $i + 1 }}</span>
                            <span>
                        <strong><i class="bi {{ $ikon }}"></i> {{ $judul }}</strong>
                        <span>{{ $ket }}</span>
                            </span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    {{-- Pita ajakan: muncul justru ketika tombol di atas sudah lewat layar,
         jadi tidak pernah ada dua jalan masuk yang terlihat bersamaan. --}}
    <div class="cmp-pita" id="cmpPita" aria-hidden="true">
        @if ($penuh)
            <span class="cmp-tombol cmp-tombol-mati"><i class="bi bi-lock-fill"></i> Kursinya sudah habis</span>
        @else
            <a href="{{ $tautanDaftar }}" class="cmp-tombol">
                <i class="bi bi-pencil-square"></i> Daftar sekarang
            </a>
        @endif
    </div>

</div>

<script>
    /*
     * Pita ajakan muncul HANYA saat tombol daftar di atas sudah lewat dari
     * layar — jadi tidak pernah ada dua jalan masuk yang terlihat bersamaan.
     *
     * IntersectionObserver, bukan penghitung posisi pada tiap gulir:
     * perhitungan manual memanggil getBoundingClientRect() puluhan kali per
     * detik dan membuat gulirannya tersendat di perangkat lama — persis
     * perangkat yang dipakai sebagian pembaca halaman ini.
     */
    (function () {
        var pita = document.getElementById('cmpPita');
        var tombolAtas = document.querySelector('.cmp-tombol-atas');

        if (!pita || !tombolAtas) return;

        function tampilkan(ya) {
            pita.classList.toggle('cmp-pita-tampil', ya);
            pita.setAttribute('aria-hidden', ya ? 'false' : 'true');
        }

        if (!('IntersectionObserver' in window)) {
            // Peramban lama: pitanya dibiarkan tampil terus. Satu tombol
            // tambahan jauh lebih baik daripada tidak ada jalan masuk sama
            // sekali di halaman sepanjang ini.
            tampilkan(true);

            return;
        }

        new IntersectionObserver(function (entri) {
            tampilkan(!entri[0].isIntersecting);
        }, { threshold: 0 }).observe(tombolAtas);
    })();
</script>
@stop
