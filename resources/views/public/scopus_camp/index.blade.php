@extends('public.layout.header')

@section('title', 'Scopus Camp — kelas tatap muka | Rumah Scopus')

@section('uraian', 'Tiga hari menulis artikel ilmiah sampai terkirim ke jurnal terindeks Scopus, ditemani pendamping yang sudah terbiasa menembusnya. Jadwal, biaya, dan sisa kursi tiap angkatan.')

@push('kepala')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600..900&family=Figtree:wght@400;500;700;800&display=swap">
@endpush

@section('konten')
{{--
    Scopus Camp — dirombak 22 Okt 2026 mengikuti beranda.

    Yang diganti dan kenapa:

      - PALETNYA. Gradien merah-jingga #ff3131 -> #ff914d di atas putih:
        palet lama yang sudah ditinggalkan beranda dan blog justru karena
        pemiliknya menyebutnya terlalu mirip situs lain.

      - BLOK GAYANYA menggantung di luar @section, sama seperti berkas
        kategori blog yang sudah dibetulkan: tetap tercetak, tetapi mendarat
        sebelum <!doctype html>.

      - SUSUNANNYA. Seluruh angkatan dicetak sebagai kartu poster di satu
        halaman — terukur 294 kartu setinggi 22.711px. Dan posternya SAMA
        PERSIS di tiap kartu, jadi halamannya jadi dinding berisi satu gambar
        yang diulang ratusan kali. Sekarang posternya dipajang sekali di
        kolom kiri bersama daftar fasilitas, dan angkatannya jadi baris
        berisi tanggal, lokasi, harga, dan sisa kursi.

      - TANGGALNYA memakai date('d M'), yang memulangkan "Oct" di halaman
        berbahasa Indonesia.
--}}
<style>
    /* Palet dan huruf DISALIN dari beranda.

       Ditulis lagi di sini, bukan menumpang .bnd: aturan beranda hidup di
       dalam home.blade.php dan tidak ikut terkirim ke halaman ini.
       Menumpangnya berarti halaman ini diam-diam kehilangan seluruh
       warnanya pada hari beranda dirapikan. */
    .kmp {
        --kertas: #fdf5e8;  --kertas-2: #f8ead4;
        --tinta: #16233f;   --tinta-2: #5c6b85;
        --jingga: #e76d20;  --jingga-muda: #fb8c3d; --jingga-tua: #ad4d12;
        --jingga-kuat: #b85213;
        --stabilo: #ffd84d;
        --rumput: #0f766e;  --ungu: #5b3fd6;

        background: var(--kertas);
        color: var(--tinta);
        font-family: 'Figtree', system-ui, sans-serif;
        overflow-x: clip;
    }
    .kmp h1, .kmp h2, .kmp h3 {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 800; color: var(--tinta); margin: 0; letter-spacing: -.01em;
    }
    .kmp p { margin: 0; color: var(--tinta-2); line-height: 1.68; }
    .kmp a { text-decoration: none; }

    /* ========================================================= kepala */
    /* 124px di atas, sama dengan hero beranda: kepala situs berposisi fixed
       setinggi 82px dan tidak memesan ruang untuk dirinya sendiri. */
    .kmp-kepala {
        padding: 124px 0 50px;
        background: linear-gradient(180deg, var(--kertas-2) 0%, var(--kertas) 100%);
        text-align: center;
    }
    .kmp-stiker {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 7px 17px; margin-bottom: 18px;
        background: var(--tinta); color: var(--kertas);
        border-radius: 999px; font-weight: 800; font-size: .84rem;
    }
    .kmp-stiker i { color: var(--stabilo); }

    .kmp .kmp-judul { font-size: clamp(1.9rem, 4.2vw, 3rem); line-height: 1.12; max-width: 20ch; margin: 0 auto; }

    .kmp-stabilo { position: relative; display: inline-block; z-index: 0; }
    .kmp-stabilo::after {
        content: ''; position: absolute; z-index: -1;
        left: -5px; right: -5px; bottom: .06em; height: .42em;
        background: var(--stabilo); border-radius: 3px;
        transform: rotate(-1.1deg) skewX(-9deg);
    }

    .kmp p.kmp-sub { max-width: 56ch; margin: 17px auto 0; font-size: 1.04rem; }

    /* ========================================================== isi */
    .kmp-bagian { padding: 54px 0; }

    /* Dua kolom TAK SAMA: keterangan tetap di kiri, daftar angkatan di
       kanan — bentuk yang sama dengan bagian jadwal di beranda. */
    .kmp-kisi {
        display: grid; grid-template-columns: 330px minmax(0, 1fr); gap: 48px;
    }

    /* TIDAK ada position: sticky di panel ini, dan itu hasil pengukuran,
       bukan selera.

       Panelnya sempat dibuat menempel dengan top: 104px. Terukur di
       peramban: di gulir 0 ia turun TEPAT 104px dari baris pertama — celah
       yang terlihat jelas — lalu saat digulung ia TIDAK menempel sama
       sekali, tepi atasnya terus naik sampai -933px. Jadi aturan itu nol
       guna dan berbiaya: satu-satunya akibatnya celah di layar pertama.

       Dicoba empat bentuk (sticky di item kisi, di kartu dalam, dengan
       bungkus blok biasa, dan dengan bungkus setinggi penuh); yang
       menghilangkan celahnya juga menghilangkan kemenempelannya. Sticky di
       blok biasa pada halaman yang SAMA bergeser 0px, jadi pemicunya
       konteks kisi, bukan gaya halaman ini. */

    .kmp-panel-kartu {
        padding: 22px;
        background: var(--kertas-2);
        border: 2px solid var(--tinta); border-radius: 22px;
        box-shadow: 7px 7px 0 var(--jingga);
    }

    .kmp .kmp-panel-kartu h2 { font-size: 1.12rem; margin-bottom: 12px; }
    .kmp-paket + .kmp-paket {
        margin-top: 18px; padding-top: 18px;
        border-top: 2px dashed rgba(22, 35, 63, .18);
    }
    .kmp-paket-tanda {
        display: inline-block; margin-left: 6px; padding: 2px 10px;
        border-radius: 999px; background: var(--jingga); color: #fff;
        font-family: 'Figtree', sans-serif; font-size: .72rem; font-weight: 800;
        vertical-align: middle;
    }
    .kmp-beda {
        margin-top: 20px; padding-top: 18px;
        border-top: 2px dashed rgba(22, 35, 63, .2);
    }
    .kmp-beda-butir + .kmp-beda-butir { margin-top: 14px; }
    .kmp-beda-label {
        display: inline-block; margin-bottom: 8px; padding: 3px 11px;
        border-radius: 999px; background: var(--jingga); color: #fff;
        font-family: 'Figtree', sans-serif; font-size: .78rem; font-weight: 800;
    }
    /* Pemilihnya `.kmp-beda .kmp-fasilitas-tambah i` — (0,3,0), bukan
       (0,2,0).

       Ditulis `.kmp-fasilitas-tambah i` saja, bobotnya SAMA dengan
       `.kmp-fasilitas i` yang kebetulan berdiri belakangan di berkas ini,
       jadi yang menang aturan hijau itu. Terukur: kedua ikon memulangkan
       rgb(15,118,110), dan tanda "+" tidak bisa dibedakan dari tanda
       centang — padahal justru selisih itu yang jadi inti bagian ini. */
    .kmp-beda .kmp-fasilitas-tambah i { color: var(--jingga); }
    .kmp-beda-nihil { color: var(--tinta-2); font-size: .9rem; margin: 0; }

    .kmp-varian {
        padding: 1px 9px; border-radius: 999px;
        background: color-mix(in srgb, var(--ungu) 14%, transparent);
        color: var(--ungu); font-size: .76rem; font-weight: 800;
    }
    .kmp-fasilitas { list-style: none; padding: 0; margin: 0; }
    .kmp-fasilitas li {
        display: flex; gap: 9px; padding: 6px 0;
        color: var(--tinta); font-size: .92rem; line-height: 1.5;
    }
    .kmp-fasilitas i { flex: 0 0 auto; margin-top: 4px; color: var(--rumput); }

    /* ====================================================== angkatan */
    .kmp-deret { display: flex; flex-direction: column; gap: 15px; }

    .kmp-baris {
        display: grid; grid-template-columns: 74px minmax(0, 1fr) auto;
        gap: 20px; align-items: center;
        padding: 17px 21px;
        background: var(--kertas);
        border: 2px solid var(--tinta); border-radius: 18px;
        box-shadow: 5px 5px 0 var(--jingga);
        color: var(--tinta);
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .kmp-baris:hover {
        color: var(--tinta);
        transform: translate(-3px, -3px); box-shadow: 9px 9px 0 var(--jingga);
    }
    /* Yang kuotanya habis tidak boleh terlihat sama menariknya dengan yang
       masih bisa didaftari. */
    .kmp-baris.kmp-penuh {
        background: var(--kertas-2); box-shadow: 5px 5px 0 rgba(22, 35, 63, .2);
        opacity: .72;
    }
    .kmp-baris.kmp-penuh:hover { transform: none; box-shadow: 5px 5px 0 rgba(22, 35, 63, .2); }

    .kmp-tgl {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: 9px 4px; border-radius: 13px;
        background: var(--jingga); color: #fff; line-height: 1;
    }
    .kmp-penuh .kmp-tgl { background: var(--tinta-2); }
    .kmp-tgl strong { font-family: 'Fraunces', serif; font-weight: 900; font-size: 1.68rem; }
    .kmp-tgl span {
        margin-top: 4px; font-size: .72rem; font-weight: 800;
        letter-spacing: .1em; text-transform: uppercase;
    }

    .kmp-isi { display: flex; flex-direction: column; gap: 5px; min-width: 0; }
    .kmp-nama { font-family: 'Fraunces', serif; font-weight: 800; font-size: 1.1rem; line-height: 1.3; }
    .kmp-rinci {
        display: flex; flex-wrap: wrap; gap: 4px 16px;
        color: var(--tinta-2); font-size: .85rem; font-weight: 600;
    }
    .kmp-rinci i { margin-right: 4px; color: var(--jingga); }

    .kmp-kanan {
        display: flex; flex-direction: column; align-items: flex-end; gap: 4px;
        text-align: right; white-space: nowrap;
    }
    .kmp-coret { color: var(--tinta-2); font-size: .82rem; font-weight: 600; }
    .kmp-harga { font-family: 'Fraunces', serif; font-weight: 800; font-size: 1.12rem; }
    .kmp-hemat {
        padding: 1px 9px; border-radius: 999px;
        background: var(--stabilo); color: var(--tinta);
        font-size: .72rem; font-weight: 800;
    }
    /* ================================================ kabar kilat & tautan */
    .kmp-kabar {
        display: flex; gap: 12px; align-items: flex-start;
        padding: 14px 18px; margin: 0 0 20px; border-radius: 14px;
        font-size: 15px; line-height: 1.6; border: 1px solid;
    }
    .kmp-kabar i { flex: 0 0 auto; margin-top: 3px; font-size: 18px; }
    .kmp-kabar.sukses { background: #eafaf6; border-color: #a7e3d6; color: #0b5a52; }
    .kmp-kabar.galat  { background: #fdecea; border-color: #f0bdb6; color: #8e2318; }

    /* ======================== borang "kehilangan tautan"

       Di lajur keterangan, tepat di bawah kartu "Yang kamu dapat".

       Riwayat letaknya: mula-mula di kaki halaman — terukur 2,4 layar ke
       bawah di laptop dan 3,9 layar di ponsel — lalu sempat jadi pita
       selebar layar di bawah hero (0,5 layar), dan kini di sini (1,1 layar).
       Setengah layar lebih jauh daripada pita, dan itu harga yang dibayar
       dengan sadar: lajur ini memang lajur keterangan, jadi borangnya duduk
       bersama hal-hal yang dibaca orang SEBELUM memilih angkatan, dan daftar
       angkatannya tidak lagi terdorong ke bawah.

       MENUMPUK KE BAWAH, bukan sebaris: lajurnya 330 px. Dipaksa sebaris,
       kotak email tinggal ~120 px — terlalu sempit untuk melihat alamat yang
       sedang diketik, dan alamat yang salah ketik berarti suratnya terbang ke
       tempat lain tanpa ada yang tahu. */
    .kmp-lacak {
        margin-top: 14px; padding: 18px 20px; border-radius: 18px;
        background: #fff; border: 1px solid #efe3cd;
        box-shadow: 0 6px 20px rgba(22, 35, 63, .05);
    }

    .kmp-lacak-kata {
        display: flex; align-items: flex-start; gap: 12px;
        font-size: 14px; line-height: 1.55; color: #5c6b85;
    }
    .kmp-lacak-kata strong {
        display: block; color: #16233f; font-size: 15.5px; margin-bottom: 2px;
    }

    .kmp-lacak-ikon {
        flex: 0 0 auto;
        display: grid; place-items: center; width: 40px; height: 40px;
        border-radius: 12px; font-size: 18px;
        background: linear-gradient(135deg, #fde8d3, #fbd9b4); color: #ad4d12;
    }

    .kmp-lacak-isian { display: grid; gap: 9px; margin-top: 14px; }

    .kmp-lacak-isian input {
        width: 100%; min-width: 0; padding: 12px 14px; font-size: 15px;
        border: 1px solid #ddd0b8; border-radius: 12px; background: #fdfcf9;
        color: #16233f;
    }
    .kmp-lacak-isian input::placeholder { color: #9aa6ba; }
    .kmp-lacak-isian input:focus {
        outline: 2px solid #e76d20; outline-offset: 1px; background: #fff;
    }

    .kmp-lacak-isian button {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; padding: 12px 16px; font-size: 15px; font-weight: 700;
        border: 0; border-radius: 12px; cursor: pointer;
        background: #16233f; color: #fff;
        transition: background .18s ease;
    }
    .kmp-lacak-isian button:hover { background: #23365c; }

    /* Di layar sempit lajurnya melebar penuh, jadi kotak dan tombolnya muat
       sebaris — dan sebaris di situ lebih baik: ia memendekkan satu blok lagi
       di halaman yang sudah panjang. */
    @media (min-width: 576px) and (max-width: 992px) {
        .kmp-lacak-isian { grid-template-columns: minmax(0, 1fr) auto; }
        .kmp-lacak-isian button { width: auto; white-space: nowrap; }
    }

    .kmp-sisa {
        padding: 1px 9px; border-radius: 999px;
        background: var(--jingga-kuat); color: #fff;
        font-size: .72rem; font-weight: 800;
    }
    .kmp-tutup {
        padding: 2px 11px; border-radius: 999px;
        background: var(--tinta-2); color: #fff;
        font-size: .74rem; font-weight: 800;
    }
    .kmp-ajak {
        display: inline-flex; align-items: center; gap: 7px; margin-top: 3px;
        color: var(--jingga-tua); font-weight: 800; font-size: .9rem;
        transition: gap .2s ease;
    }
    .kmp-baris:hover .kmp-ajak { gap: 13px; }

    .kmp-halaman { display: flex; justify-content: center; margin-top: 34px; }
    .kmp-halaman .pagination { gap: 8px; margin: 0; flex-wrap: wrap; }
    .kmp-halaman .page-item .page-link {
        min-width: 42px; padding: 9px 14px;
        background: var(--kertas); color: var(--tinta);
        border: 2px solid var(--tinta); border-radius: 13px;
        box-shadow: 3px 3px 0 rgba(22, 35, 63, .16);
        font-weight: 800; text-align: center;
    }
    .kmp-halaman .page-item.active .page-link {
        background: var(--jingga-kuat); color: #fff; box-shadow: 3px 3px 0 var(--tinta);
    }
    .kmp-halaman .page-item.disabled .page-link {
        background: var(--kertas-2); color: var(--tinta-2);
        border-color: rgba(22, 35, 63, .25); box-shadow: none;
    }

    .kmp-kosong {
        max-width: 48ch; margin: 0 auto; padding: 48px 30px; text-align: center;
        background: var(--kertas-2);
        border: 2px dashed rgba(22, 35, 63, .28); border-radius: 22px;
    }
    .kmp-kosong i { font-size: 2.2rem; color: var(--jingga); }
    .kmp .kmp-kosong h2 { margin: 14px 0 9px; font-size: 1.3rem; }

    .kmp-tombol {
        display: inline-flex; align-items: center; gap: 9px;
        padding: 12px 22px; margin-top: 20px;
        background: var(--jingga-kuat); color: #fff;
        border: 2px solid var(--tinta); border-radius: 999px;
        box-shadow: 4px 4px 0 var(--tinta); font-weight: 800;
    }
    .kmp-tombol:hover { color: #fff; background: var(--tinta); }

    @media (max-width: 992px) {
        .kmp-kisi { grid-template-columns: 1fr; gap: 30px; }
    }
    @media (max-width: 600px) {
        .kmp-kepala { padding: 104px 0 40px; }
        .kmp-baris { grid-template-columns: 58px minmax(0, 1fr); gap: 14px; padding: 15px 16px; }
        .kmp-kanan {
            grid-column: 1 / -1;
            flex-direction: row; align-items: center; justify-content: flex-start;
            flex-wrap: wrap; gap: 6px 12px;
            padding-top: 11px; border-top: 2px dashed rgba(22, 35, 63, .14);
        }
        .kmp-ajak { margin-top: 0; margin-left: auto; }
    }

    @media (prefers-reduced-motion: reduce) {
        .kmp *, .kmp *::before, .kmp *::after { transition: none !important; }
    }
</style>

<div class="kmp">

    {{-- ====================================== kabar kilat & jalan kembali

         Pesan galat sebelumnya TIDAK DIGAMBAR DI MANA PUN pada jalur layanan
         ini. Terukur 25 Okt 2026: membuka borang angkatan yang sudah ditutup
         memantulkan orangnya ke halaman ini dengan ->with('error'), dan
         halaman ini cuma punya penangan 'success' — yang pun mengabaikan isi
         pesannya dan selalu berbunyi "Data Berhasil Terkirim! Mohon tunggu
         verifikasi 1x24 jam", kalimat yang sudah tidak benar sejak
         pembayarannya lewat gerbang. --}}
    {{-- DIGAMBAR HANYA KALAU ADA ISINYA.

         Pembungkusnya berpadding-atas 96 px untuk melewati kepala halaman
         yang melayang. Digambar selalu, padding itu jadi 96 px ruang kosong
         di atas hero pada SETIAP kunjungan — padahal hero-nya sendiri sudah
         punya ruang atasnya. --}}
    @if (session('error') || session('success') || $errors->any())
    <div class="container" style="padding-top: 96px;">
        @if (session('error'))
            <div class="kmp-kabar galat" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if (session('success'))
            <div class="kmp-kabar sukses" role="status">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="kmp-kabar galat" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif
    </div>
    @endif


    {{-- ==================================================== kepala ===== --}}
    <section class="kmp-kepala">
        <div class="container">
            <span class="kmp-stiker"><i class="bi bi-geo-alt-fill"></i> Kelas tatap muka · 3 hari</span>

            <h1 class="kmp-judul">
                Datang bawa data, pulang naskahmu sudah <span class="kmp-stabilo">terkirim</span>
            </h1>

            <p class="kmp-sub">
                Tiga hari menulis bareng pendamping yang sudah terbiasa menembus
                jurnal terindeks Scopus. Bukan duduk mendengarkan ceramah —
                naskahmu sendiri yang dikerjakan.
                @if ($categories->total() > 0)
                    Ada {{ $categories->total() }} angkatan yang sedang dibuka.
                @endif
            </p>
        </div>
    </section>

    {{-- ================================================== angkatan ===== --}}
    <section class="kmp-bagian">
        <div class="container">
            @if ($categories->isEmpty())
                <div class="kmp-kosong">
                    <i class="bi bi-calendar-x"></i>
                    <h2>Belum ada angkatan yang dibuka</h2>
                    <p>
                        Jadwal berikutnya biasanya diumumkan beberapa minggu sebelum
                        acaranya. Kabari admin kalau mau diberi tahu duluan.
                    </p>
                    <a href="{{ route('blog.contact.kontak') }}" class="kmp-tombol">
                        <i class="bi bi-chat-dots-fill"></i> Hubungi admin
                    </a>
                </div>
            @else
                <div class="kmp-kisi">

                    {{-- Poster dan fasilitasnya SEKALI di sini, bukan di tiap
                         kartu: flyer-nya sama persis untuk semua angkatan. --}}
                    <div class="kmp-panel">
                        <div class="kmp-panel-kartu">
                            {{-- POSTER TIDAK DIPAJANG di sini, dan itu keputusan.

                                 Versi pertama rombakan ini memasangnya sekali di
                                 panel, diambil dari angkatan pertama. Begitu
                                 halaman ini memuat Jawa bersama luar Jawa, satu
                                 flyer berdiri di atas dua paket yang isinya
                                 berbeda — dan flyer itu mewakili salah satunya
                                 saja.

                                 Sempat dicoba menampilkannya hanya kalau seluruh
                                 angkatan memakai gambar yang sama; terukur dengan
                                 data sebenarnya ia nyaris tidak pernah lolos,
                                 sebab sebagian angkatan memang tidak berflyer.
                                 Aturan yang hampir selalu salah lebih baik
                                 dibuang daripada dipelihara.

                                 Yang dibutuhkan orang di halaman daftar adalah
                                 APA YANG DIDAPAT, dan itu ada di bawah ini.
                                 Flyer tiap angkatan tetap tampil di halaman
                                 detailnya masing-masing. --}}

                            {{-- Yang SAMA ditulis sekali, selisihnya disebut
                                 terpisah.

                                 Bentuk sebelumnya memajang dua daftar utuh
                                 berdampingan — dan terukur, keduanya mengulang
                                 5 dari 7 butir yang sama. Untuk tahu bedanya,
                                 pembaca harus membandingkan dua daftar baris
                                 per baris. Sasaran halaman ini orang berusia
                                 lanjut, dan yang mereka cari cuma satu hal:
                                 apa bedanya.

                                 Sebelum itu pun pernah lebih buruk: SATU daftar
                                 untuk semua varian, diambil dari angkatan
                                 pertama — jadi peserta luar Jawa dijanjikan
                                 penginapan dan kolam renang yang tidak akan ia
                                 dapatkan, padahal membayar satu juta lebih
                                 mahal. --}}
                            @if ($pisahkan)
                                <h2>Yang kamu dapat</h2>
                                <ul class="kmp-fasilitas">
                                    @foreach ($fasilitasUmum as $butir)
                                        <li><i class="bi bi-check-circle-fill"></i> <span>{{ $butir }}</span></li>
                                    @endforeach
                                </ul>

                                <div class="kmp-beda">
                                    <h2>Bedanya</h2>

                                    @foreach ($paket as $p)
                                        <div class="kmp-beda-butir">
                                            <span class="kmp-beda-label">{{ $p['label'] ?: 'Paket lain' }}</span>

                                            @if (! empty($p['khusus']))
                                                <ul class="kmp-fasilitas kmp-fasilitas-tambah">
                                                    @foreach ($p['khusus'] as $butir)
                                                        <li><i class="bi bi-plus-circle-fill"></i> <span>{{ $butir }}</span></li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                {{-- Disebut, bukan didiamkan: paket yang tidak
                                                     muncul di daftar "bedanya" membuat
                                                     pembacanya menebak apakah ia terlewat. --}}
                                                <p class="kmp-beda-nihil">Yang di atas saja, tanpa tambahan.</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                @foreach ($paket as $p)
                                    <div class="kmp-paket">
                                        <h2>
                                            Yang kamu dapat
                                            @if ($paket->count() > 1 && $p['label'])
                                                <span class="kmp-paket-tanda">{{ $p['label'] }}</span>
                                            @endif
                                        </h2>
                                        <ul class="kmp-fasilitas">
                                            @foreach ($p['fasilitas'] as $butir)
                                                <li><i class="bi bi-check-circle-fill"></i> <span>{{ $butir }}</span></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        {{-- ================== jalan kembali bagi yang kehilangan tautannya

                             DI BAWAH kartu "Yang kamu dapat", di lajur
                             keterangan — bukan di kaki halaman, dan bukan pula
                             sebagai pita selebar layar di bawah hero.

                             Riwayatnya: mula-mula di KAKI halaman, dan terukur
                             2,4 layar ke bawah di laptop serta 3,9 layar di
                             ponsel — di balik dua belas kartu angkatan dan
                             penomoran 29 nomor. Dipindahkan ke bawah hero ia
                             jadi 0,5 layar, tetapi memotong jalan antara
                             judul halaman dan daftar angkatannya.

                             Di sini ia 1,1 layar — setengah layar lebih jauh
                             daripada di bawah hero, dan itu harga yang dibayar
                             dengan sadar: lajur ini memang lajur keterangan,
                             jadi borangnya duduk bersama hal-hal lain yang
                             dibaca orang SEBELUM memilih angkatan, dan daftar
                             angkatannya tidak lagi terdorong ke bawah.

                             Alamat halaman status memuat UUID yang tidak bisa
                             ditebak, jadi yang menutup tabnya dan tidak
                             menerima suratnya kehilangan pendaftarannya untuk
                             selamanya, termasuk tombol bayarnya. Tautannya
                             dikirim ke EMAIL, tidak ke layar: ditampilkan,
                             siapa pun yang menebak alamat email orang lain
                             bisa membuka pendaftarannya — di halaman itu ada
                             nama, nomor telepon, afiliasi, dan daftar
                             pesertanya. --}}
                        <form method="POST" action="{{ route('public.scopuscamp.tautansaya') }}"
                            class="kmp-lacak">
                            @csrf

                            <div class="kmp-lacak-kata">
                                <span class="kmp-lacak-ikon"><i class="bi bi-ticket-detailed"></i></span>
                                <span>
                                    <strong>Sudah pernah mendaftar?</strong>
                                    Masukkan email Anda, tautan pendaftarannya kami kirim ke sana.
                                </span>
                            </div>

                            <div class="kmp-lacak-isian">
                                <label for="kmpLacakEmail" class="visually-hidden">Alamat email</label>
                                <input type="email" id="kmpLacakEmail" name="email" required
                                    autocomplete="email" placeholder="nama@email.com"
                                    value="{{ old('email') }}">
                                <button type="submit">
                                    <i class="bi bi-envelope"></i> Kirim tautannya
                                </button>
                            </div>
                        </form>
                    </div>

                    @php
                        /*
                         * Penanda varian hanya muncul kalau halaman ini memang
                         * memuat lebih dari satu. Dengan satu varian saja,
                         * lencana "Pulau Jawa" di tiap baris cuma pengulangan
                         * yang tidak membedakan apa pun.
                         */
                        $banyakVarian = $categories->getCollection()
                            ->pluck('varian')->filter()->unique()->count() > 1;
                    @endphp

                    <div class="kmp-deret">
                        @foreach ($categories as $i => $item)
                            @php
                                /*
                                 * Kuota NULL berarti TIDAK DIBATASI, bukan nol.
                                 *
                                 * Syarat lamanya `sisa_kuota === null || <= 0`,
                                 * jadi angkatan tanpa batas kuota dipajang
                                 * "Pendaftaran Ditutup". Belum pernah terasa
                                 * karena kebetulan semua angkatan yang ada
                                 * mengisi kuotanya.
                                 */
                                $penuh = $item->total_kuota !== null && (int) $item->sisa_kuota < 1;

                                $hargaAsal = (int) $item->harga_sebelum_promo;
                                $hargaBayar = (int) $item->harga_setelah_promo;
                                $adaDiskon = $hargaAsal > $hargaBayar && $hargaBayar > 0;
                                $hematPersen = $adaDiskon ? (int) round((($hargaAsal - $hargaBayar) / $hargaAsal) * 100) : 0;

                                $mulai = \Carbon\Carbon::parse($item->mulai)->locale('id');
                                $selesai = $item->selesai ? \Carbon\Carbon::parse($item->selesai) : null;
                            @endphp

                            @if ($penuh)
                                <div class="kmp-baris kmp-penuh">
                            @else
                                <a href="{{ route('public.scopuscamp.Selengkapnya', ['id' => $item->id]) }}"
                                    class="kmp-baris" data-aos="fade-up" data-aos-delay="{{ ($i % 4) * 80 }}">
                            @endif

                                <span class="kmp-tgl">
                                    <strong>{{ $mulai->translatedFormat('j') }}</strong>
                                    <span>{{ $mulai->translatedFormat('M') }}</span>
                                </span>

                                <span class="kmp-isi">
                                    <span class="kmp-nama">
                                        {{ $item->nama }}@if ($item->nama_ke) <span style="color: var(--tinta-2);">#{{ $item->nama_ke }}</span>@endif
                                    </span>
                                    <span class="kmp-rinci">
                                        <span><i class="bi bi-calendar3"></i>
                                            {{ \App\Support\RentangTanggal::tulis($mulai, $selesai, true) }}</span>
                                        @if ($item->lokasi)
                                            <span><i class="bi bi-geo-alt"></i> {{ $item->lokasi }}</span>
                                        @endif
                                        @if ($banyakVarian && $item->varian)
                                            <span class="kmp-varian">
                                                {{ $namaVarian[$item->varian] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $item->varian)) }}
                                            </span>
                                        @endif
                                        @if ($item->total_kuota !== null && ! $penuh)
                                            <span><i class="bi bi-people"></i> kuota {{ $item->total_kuota }}</span>
                                        @endif
                                    </span>
                                </span>

                                <span class="kmp-kanan">
                                    @if ($penuh)
                                        <span class="kmp-tutup"><i class="bi bi-lock-fill"></i> Kursinya habis</span>
                                    @else
                                        {{-- Harga nol DIAM, tidak jadi "Rp 0".

                                             Terukur ada angkatan yang biayanya
                                             memang 0 di basis data; "Rp 0" di
                                             samping angkatan lain yang 5,5 juta
                                             terbaca sebagai sistem yang rusak,
                                             bukan sebagai kelas gratis. --}}
                                        @if ($hargaBayar > 0)
                                            @if ($adaDiskon)
                                                <s class="kmp-coret">Rp {{ number_format($hargaAsal, 0, ',', '.') }}</s>
                                            @endif
                                            <span class="kmp-harga">Rp {{ number_format($hargaBayar, 0, ',', '.') }}</span>

                                            @if ($hematPersen > 0)
                                                <span class="kmp-hemat">hemat {{ $hematPersen }}%</span>
                                            @endif
                                        @endif

                                        {{-- Sisa kursi disebut hanya kalau tinggal
                                             sedikit. "20 kursi" tidak mendesak siapa
                                             pun; menyebutnya saat masih longgar
                                             membuat angka itu kehilangan arti waktu
                                             benar-benar sempit. --}}
                                        @if ($item->total_kuota !== null && (int) $item->sisa_kuota <= 5)
                                            <span class="kmp-sisa">sisa {{ (int) $item->sisa_kuota }} kursi</span>
                                        @endif

                                        <span class="kmp-ajak">Lihat & daftar <i class="bi bi-arrow-right"></i></span>
                                    @endif
                                </span>

                            @if ($penuh)
                                </div>
                            @else
                                </a>
                            @endif
                        @endforeach

                        @if ($categories->hasPages())
                            <div class="kmp-halaman">{{ $categories->links() }}</div>
                        @endif
                    </div>

                </div>
            @endif
        </div>
    </section>

</div>

{{-- Pemberitahuan 'success' DULU digambar SweetAlert dengan teks tetap
     "Data Berhasil Terkirim! Mohon tunggu verifikasi 1x24 jam" — isi pesannya
     diabaikan seluruhnya.

     Kalimat itu sudah tidak benar sejak pembayarannya lewat gerbang: tidak ada
     yang diverifikasi 1x24 jam, dan yang baru saja meminta tautan
     pendaftarannya diberi tahu bahwa datanya terkirim. Diganti pita di kepala
     halaman yang mencetak pesannya sendiri. --}}
@stop
