@extends('public.layout.header')

@section('title')
Webinar Eksklusif | Rumah Scopus
@stop

@section('konten')
{{--
    Daftar sesi yang sedang dibuka.

    Halaman pemasarannya ada di subdomain tersendiri dan memajang SATU sesi;
    yang di sini jaring pengamannya — tempat mendarat orang yang membuka
    tautan sesi yang sudah ditutup, dan satu-satunya tempat yang memperlihatkan
    semua tanggal sekaligus.
--}}
<section class="sel-latar">
    <div class="container sel-wadah">

        @if (session('error'))
            <div class="sel-galat" role="alert">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="sel-kepala">
            <span class="sel-lencana"><i class="fas fa-microphone" aria-hidden="true"></i> Webinar Eksklusif</span>
            <h1>Belajar langsung dari yang sudah menjalani</h1>
            <p>Sesi daring dua jam, materinya terapan, dan Anda bisa bertanya langsung.</p>
        </div>

        @if ($sesi->isEmpty())
            <div class="sel-kosong">
                <i class="fas fa-calendar-times" aria-hidden="true"></i>
                <p class="sel-kosong-judul">Belum ada sesi yang dijadwalkan</p>
                <p class="sel-kosong-sub">
                    Jadwal berikutnya diumumkan lewat Instagram dan grup WhatsApp Rumah Scopus.
                </p>
            </div>
        @else
            {{-- Satu sesi digambar MENDATAR, bukan sebagai kartu kecil
                 sendirian di tengah wadah selebar 1.056px.

                 Jumlahnya dibaca di sini, bukan ditebak CSS: @container
                 bisa melakukannya tanpa kelas tambahan, tetapi peramban
                 yang belum mengenalnya akan memulangkan kartu tegak
                 selebar penuh — yaitu persis keadaan yang sedang
                 diperbaiki. --}}
            <div class="sel-kisi @if ($sesi->count() === 1) sel-kisi-tunggal @endif">
                @foreach ($sesi as $s)
                    @php($sisa = $s->total_kuota === null ? null : (int) $s->sisa_kuota)
                    <article class="sel-kartu">
                        @if ($s->alamat_sampul)
                            <img src="{{ $s->alamat_sampul }}" alt="" class="sel-flyer" loading="lazy">
                        @endif

                        <div class="sel-isi">
                            <div class="sel-pil-baris">
                                <span class="sel-pil">{{ $s->platform ?: 'Daring' }}</span>
                                @if ($sisa !== null && $sisa > 0 && $sisa <= 10)
                                    <span class="sel-pil sel-pil-merah">Tinggal {{ $sisa }} kursi</span>
                                @elseif ($sisa !== null && $sisa < 1)
                                    <span class="sel-pil sel-pil-abu">Penuh</span>
                                @endif
                            </div>

                            <h2>{{ $s->nama }}</h2>

                            <p class="sel-waktu">
                                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                                {{ \App\Support\RentangTanggal::tulis(
                                    $s->mulai ? \Carbon\Carbon::parse($s->mulai) : null,
                                    $s->selesai ? \Carbon\Carbon::parse($s->selesai) : null
                                ) ?: 'Menyusul' }}
                                @if ($s->jam) &middot; {{ $s->jam }} @endif
                            </p>

                            {{-- PEMATERINYA, berikut wajah dan jabatannya.

                                 Keduanya sudah ada di data sejak lama dan tidak
                                 pernah tergambar di mana pun. "Siapa yang
                                 mengajar" termasuk yang dicari orang sebelum
                                 memutuskan ikut, dan wajah menjawabnya lebih
                                 cepat daripada nama.

                                 Fotonya lewat accessor alamat_pemateri, bukan
                                 kolomnya apa adanya: kolom yang terisi tetapi
                                 berkasnya sudah hilang memulangkan null, jadi
                                 yang tergambar ikon — bukan gambar rusak. --}}
                            @if ($s->pemateri)
                                <div class="sel-pemateri">
                                    @if ($s->alamat_pemateri)
                                        <img src="{{ $s->alamat_pemateri }}" alt=""
                                            class="sel-pemateri-foto" loading="lazy">
                                    @else
                                        <span class="sel-pemateri-foto sel-pemateri-ikon" aria-hidden="true">
                                            <i class="fas fa-user"></i>
                                        </span>
                                    @endif

                                    <span class="sel-pemateri-teks">
                                        <strong>{{ $s->pemateri }}</strong>
                                        @if ($s->pemateri_jabatan)
                                            <span class="sel-jabatan">{{ $s->pemateri_jabatan }}</span>
                                        @endif
                                    </span>
                                </div>
                            @endif

                            <div class="sel-kaki">
                                {{-- Harga yang BENAR-BENAR ditagih, dan harga
                                     penuhnya dicoret kalau ada promo.

                                     Sebelum ini kartu ini memajang `biaya`
                                     mentah, jadi sesi yang sedang promo
                                     terbaca harga penuh di sini lalu turun
                                     sendiri di borangnya — selisih yang tidak
                                     dijelaskan apa pun. --}}
                                @php($hargaAsal = (int) $s->biaya)
                                @php($hargaBayar = (int) $s->harga_setelah_promo)

                                <span class="sel-harga">
                                    @if ($hargaBayar > 0)
                                        @if ($hargaAsal > $hargaBayar)
                                            <s class="sel-harga-coret">Rp {{ number_format($hargaAsal, 0, ',', '.') }}</s>
                                        @endif
                                        Rp {{ number_format($hargaBayar, 0, ',', '.') }}
                                    @else
                                        Gratis
                                    @endif
                                    <small>per peserta</small>
                                </span>

                                @if ($sisa !== null && $sisa < 1)
                                    <span class="sel-tombol sel-tombol-mati">Kuota penuh</span>
                                @else
                                    <a href="{{ route('public.webinareksklusif.daftar', $s->id) }}"
                                        class="sel-tombol">
                                        Daftar <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

<style>
    .sel-latar {
        --navy: #0f2b5b;
        --tinta: #1e293b;
        --tinta-2: #64748b;
        --garis: #e2e8f0;

        background: #f8fafc;
        background-image:
            radial-gradient(circle at 85% -5%, rgba(255, 106, 0, .12), transparent 45%),
            radial-gradient(circle at -10% 20%, rgba(15, 43, 91, .1), transparent 45%);
        padding: 110px 0 90px;
        min-height: 100vh;
        font-family: 'Poppins', 'Inter', system-ui, sans-serif;
        color: var(--tinta);
    }

    .sel-wadah { max-width: 1080px; }

    .sel-galat {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 24px;
        padding: 14px 18px;
        border-radius: 14px;
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #991b1b;
        font-size: .9rem;
    }

    .sel-kepala { max-width: 620px; margin: 0 auto 36px; text-align: center; }

    .sel-lencana {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 14px;
        padding: 7px 16px;
        border-radius: 999px;
        background: linear-gradient(135deg, #ff8c00, #e65c00);
        color: #fff;
        font-size: .74rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    /* margin: 0 !important — style.css Stisla memaksa margin-left pada .fas
       di dalam tautan dan rentang tertentu dengan bobot (0,4,1). */
    .sel-lencana > .fas { margin: 0 !important; }

    .sel-kepala h1 {
        margin: 0 0 10px;
        font-size: clamp(1.6rem, 4vw, 2.3rem);
        font-weight: 800;
        line-height: 1.25;
        color: var(--navy);
    }

    .sel-kepala p { margin: 0; font-size: 1rem; color: var(--tinta-2); }

    /* auto-fit + minmax: tiga kartu di layar lebar, dua di tablet, satu di
       ponsel — tanpa titik putus yang harus dijaga sendiri. */
    /*
     * Lajurnya dibatasi 360px, bukan 1fr.
     *
     * auto-fit MENGUNCUPKAN lajur yang kosong, jadi satu sesi berarti satu
     * kartu selebar seluruh wadah — terukur 1.056px, dengan sampul setinggi
     * 593px. Satu kartu memakan dua layar penuh, dan rincian sesinya —
     * tanggal, pemateri, harga, tombol daftar — terdorong ke bawah lipatan.
     *
     * Dibatasi, kartunya tetap sebesar kartu berapa pun jumlah sesinya.
     * justify-content memusatkannya supaya satu-dua kartu tidak menempel
     * ke tepi kiri dengan ruang menganga di kanannya.
     */
    .sel-kisi {
        display: grid;
        /*
         * Batas atas lajurnya 330px, dan angka itu dihitung, bukan dikira.
         *
         * Dengan 1fr, auto-fit membuat lajur sebanyak yang muat lalu
         * MEMBAGI RATA seluruh lebar kepada yang tersisa. Dua sesi berarti
         * tiga lajur dibuat, yang ketiga menguncup, dan dua sisanya jadi
         * 517px masing-masing — kartu 360px lalu mengambang di tengah
         * lajurnya sendiri. Terukur: jarak antar kartu 179px, bukan 22px
         * yang disetel di sini.
         *
         * Dipatok lebar tetap, lajurnya berhenti tumbuh dan jaraknya
         * benar-benar jadi gap-nya. 330, bukan 360: tiga lajur 330px
         * ditambah dua jarak 22px = 1.034px, masih muat di wadah 1.056px —
         * sementara 360px berarti 1.124px dan tiga sesi tidak pernah
         * sebaris.
         *
         * justify-content memusatkan kelompoknya, jadi satu-dua kartu tidak
         * menempel ke tepi kiri dengan ruang menganga di kanannya.
         */
        grid-template-columns: repeat(auto-fit, minmax(280px, 330px));
        justify-content: center;
        gap: 22px;
    }

    /* Di layar sempit lajurnya dibiarkan memenuhi wadahnya: dipatok 330px,
       kartunya menyisakan tepi kosong di kanan-kiri pada layar yang memang
       sudah sempit. */
    @media (max-width: 520px) {
        .sel-kisi { grid-template-columns: minmax(0, 1fr); }
    }

    .sel-kartu {
        display: flex;
        flex-direction: column;
        border-radius: 20px;
        border: 1px solid var(--garis);
        background: #fff;
        overflow: hidden;
        box-shadow: 0 20px 50px -34px rgba(15, 43, 91, .5);
        transition: transform .25s ease, box-shadow .25s ease;
    }

    .sel-kartu:hover { transform: translateY(-4px); box-shadow: 0 26px 56px -30px rgba(15, 43, 91, .55); }

    /*
     * 4 / 5, bukan 16 / 9.
     *
     * Posternya TEGAK. Diukur pada 50 sampul yang benar-benar ada di
     * aplikasi ini: semuanya tegak, dan 48 di antaranya tepat 1080x1350 —
     * yaitu 4:5. Tidak satu pun melebar.
     *
     * Dipaksa ke bingkai 16:9 dengan object-fit: cover, yang tersisa cuma
     * pita tengahnya: judul besar di puncak poster terpenggal, dan tawaran
     * di kakinya ("Paket Bundling Spesial") terpotong di tengah kata. Yang
     * dibuang sekitar 55% tinggi gambarnya — pada gambar yang seluruhnya
     * dirancang sebagai satu kesatuan.
     *
     * Dengan 4:5 poster baku tergambar UTUH, tanpa satu piksel pun dipotong.
     */
    .sel-flyer {
        width: 100%;
        aspect-ratio: 4 / 5;
        object-fit: cover;
        /* Sampul yang rasionya menyimpang (ada satu 1400x1981) dipotong dari
           tepi bawah, bukan dari keduanya: judul poster ada di puncaknya. */
        object-position: top center;
        background: #f1f5f9;
    }

    /* flex: 1 supaya kaki kartu rata bawah walau judulnya beda panjang. */
    .sel-isi { flex: 1 1 auto; display: flex; flex-direction: column; padding: 20px; }

    /*
     * SATU SESI: kartunya mendatar, poster di kiri dan rinciannya di kanan.
     *
     * Tegak, satu sesi berarti kartu selebar 360px berdiri sendiri di tengah
     * ruang 1.056px — rapi, tetapi terbaca seperti halaman yang isinya belum
     * datang. Mendatar, lebar itu terpakai dan rinciannya naik sejajar
     * posternya, jadi harga dan tombol daftarnya terlihat tanpa menggulung.
     *
     * Hanya di layar lebar: di ponsel keduanya tetap bertumpuk.
     */
    @media (min-width: 820px) {
        .sel-kisi-tunggal { grid-template-columns: minmax(0, 1fr); }

        .sel-kisi-tunggal .sel-kartu { flex-direction: row; }

        /*
         * Lebar posternya dipatok persen, dan tingginya DIBIARKAN mengikuti
         * rasionya. Dipatok height:100% supaya setinggi kartunya, posternya
         * akan dipotong lagi — persis yang baru saja diperbaiki.
         */
        /*
         * Lebarnya dipangkas 380 -> 310, dan itu soal TINGGI, bukan lebar.
         *
         * Posternya 4:5, jadi tiap piksel lebar membawa 1,25 piksel tinggi:
         * 380px lebar berarti 475px tinggi, sementara seluruh rincian di
         * sebelahnya cuma 228px. Terukur 123px kosong di atasnya dan 124px
         * di bawahnya — kartunya tampak separuh terisi.
         *
         * Dipangkas, bukan dipotong: rasionya tetap 4:5 dan posternya tetap
         * utuh. Yang berkurang ukuran tampilnya saja.
         */
        .sel-kisi-tunggal .sel-flyer {
            width: 34%;
            max-width: 310px;
            flex: 0 0 auto;
            align-self: flex-start;
        }

        /* Isinya dipusatkan tegak: posternya lebih tinggi daripada
           rinciannya, dan rincian yang menempel ke puncak menyisakan ruang
           kosong panjang di bawahnya. */
        .sel-kisi-tunggal .sel-isi {
            justify-content: center;
            padding: 28px 30px;
        }

        /*
         * margin-top: auto DIBATALKAN di sini, dan itu yang menutup lubangnya.
         *
         * Pada kartu tegak berjajar, auto itu benar: ia menempelkan kaki ke
         * dasar kartu supaya tombol semua kartu sebaris walau judulnya
         * berbeda panjang. Di kartu mendatar tidak ada kartu lain untuk
         * disejajarkan — yang ada cuma satu kolom setinggi posternya, dan
         * auto mendorong harga beserta tombolnya ke dasar sejauh 330px dari
         * baris di atasnya. Terukur: nama pemateri berakhir di y=662,
         * harganya baru mulai di y=990.
         *
         * Tanpa auto, seluruh rinciannya jadi satu kelompok rapat yang
         * dipusatkan tegak: ruang kosongnya terbagi di atas dan di bawah,
         * dan terbaca sebagai bantalan, bukan sebagai sesuatu yang hilang.
         */
        .sel-kisi-tunggal .sel-kaki { margin-top: 0; padding-top: 22px; }

        /* Judulnya boleh lebih besar: di sini ia punya ruang, dan ia yang
           pertama dibaca orang sesudah posternya. */
        .sel-kisi-tunggal .sel-kartu h2 { font-size: 1.35rem; }
    }

    .sel-pil-baris { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }

    .sel-pil {
        padding: 5px 11px;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        font-size: .72rem;
        font-weight: 700;
    }

    .sel-pil-merah { background: #fee2e2; color: #b91c1c; }
    .sel-pil-abu { background: #f1f5f9; color: #64748b; }

    .sel-isi h2 {
        margin: 0 0 10px;
        font-size: 1.12rem;
        font-weight: 800;
        line-height: 1.4;
        color: var(--navy);
    }

    .sel-waktu {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px 8px;
        margin: 0 0 6px;
        font-size: .86rem;
        color: var(--tinta-2);
    }

    .sel-pemateri {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 10px 0 0;
        font-size: .86rem;
        color: var(--tinta-2);
    }

    /* Ukurannya dipatok dua-duanya dan object-fit: cover — foto pemateri
       diunggah dengan rasio apa saja, dan yang tidak dipatok akan menggepeng
       atau memanjang mengikuti aslinya. */
    .sel-pemateri-foto {
        width: 42px;
        height: 42px;
        flex: 0 0 auto;
        border-radius: 50%;
        object-fit: cover;
        background: #eef2f7;
        border: 2px solid #fff;
        box-shadow: 0 2px 8px -2px rgba(15, 43, 91, .35);
    }

    /* Penggantinya saat fotonya tidak ada: lingkaran seukuran sama, jadi
       barisnya tidak bergeser antara sesi yang berfoto dan yang tidak. */
    .sel-pemateri-ikon {
        display: grid;
        place-items: center;
        color: #94a3b8;
    }

    .sel-pemateri-teks { display: flex; flex-direction: column; min-width: 0; }
    .sel-pemateri-teks strong { color: var(--tinta); font-weight: 700; }

    /* Jabatannya keterangan, bukan nama — dibedakan supaya namanya tetap
       yang pertama terbaca. */
    .sel-jabatan { font-size: .78rem; color: #94a3b8; }

    .sel-waktu > .fas,
    .sel-pemateri > .fas { margin: 0 !important; color: #ff6a00; font-size: .85rem; }

    .sel-kaki {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        /* margin-top: auto — kaki menempel ke bawah kartu, jadi tombol semua
           kartu sebaris walau judulnya beda panjang. */
        margin-top: auto;
        padding-top: 16px;
    }

    .sel-harga { font-size: 1.15rem; font-weight: 800; color: var(--navy); }
    .sel-harga small { display: block; font-size: .72rem; font-weight: 500; color: var(--tinta-2); }

    /* Harga penuh yang dicoret: sengaja lebih kecil dan pudar supaya angka
       yang dibaca duluan adalah yang benar-benar ditagih, bukan yang dicoret. */
    .sel-harga-coret { font-size: .82rem; font-weight: 600; color: var(--tinta-2); margin-right: .25rem; }

    .sel-tombol {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 44px;
        padding: 0 18px;
        border-radius: 12px;
        background: linear-gradient(135deg, #ff8c00, #e65c00);
        color: #fff;
        font-size: .9rem;
        font-weight: 700;
        text-decoration: none;
    }

    .sel-tombol:hover { color: #fff; text-decoration: none; }
    .sel-tombol > .fas { margin: 0 !important; }

    .sel-tombol-mati { background: #e2e8f0; color: #94a3b8; cursor: not-allowed; }

    /* ------------------------------------------- gerak tombol daftar
     *
     * Ketiganya dipindahkan dari halaman landing Webinar Eksklusif, yang
     * repo dan subdomainnya terpisah dari aplikasi ini. Disalin, bukan
     * dibagi: tidak ada satu pun berkas gaya yang dipakai berdua, dan
     * menautkan keduanya berarti satu repo menyetir tampilan repo lain.
     *
     * TIDAK satu pun dipasang pada .sel-tombol-mati. Tombol "Kuota penuh"
     * yang berdenyut dan panahnya menyenggol maju adalah ajakan menekan
     * sesuatu yang tidak bisa ditekan — dan yang menekannya berulang kali
     * akan mengira halamannya rusak.
     */

    /*
     * Denyutnya digambar ::after, jadi yang membesar HANYA bayangannya.
     * Tombolnya sendiri yang diskalakan membuat sasaran tekan ikut bergerak,
     * dan sasaran yang bergerak lebih sulit dikenai — terutama di ponsel.
     */
    .sel-tombol:not(.sel-tombol-mati) { position: relative; }

    .sel-tombol:not(.sel-tombol-mati)::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        box-shadow: 0 0 0 0 rgba(255, 140, 0, .55);
        animation: sel-denyut-tombol 2.4s ease-out infinite;
        pointer-events: none;
    }

    @keyframes sel-denyut-tombol {
        0%   { box-shadow: 0 0 0 0 rgba(255, 140, 0, .5); }
        70%  { box-shadow: 0 0 0 18px rgba(255, 140, 0, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 140, 0, 0); }
    }

    /* Panah kanannya menyenggol ke depan: menandakan ada langkah lanjutan,
       bukan sekadar tombol yang berdiri. */
    .sel-tombol:not(.sel-tombol-mati) .fa-arrow-right {
        animation: sel-panah-maju 1.6s ease-in-out infinite;
    }

    @keyframes sel-panah-maju {
        0%, 100% { transform: translateX(0); }
        50%      { transform: translateX(5px); }
    }

    /* Sapuan kilau HANYA di peranti yang benar-benar punya tetikus.
       Di layar sentuh tidak ada hover, dan sebagian peramban menahannya
       sampai sentuhan pertama — kilau yang baru muncul sesudah ditekan
       tidak menarik siapa pun, ia cuma terlambat. */
    @media (hover: hover) {
        /*
         * overflow: hidden HANYA saat hover, dan itu bukan kerapian.
         *
         * Kilau sapuannya unsur sempit yang melintas dari luar tombol, jadi
         * ia harus dipotong tepinya. Tetapi overflow: hidden memotong
         * SELURUH yang tergambar di luar kotak tombol — termasuk bayangan
         * cincin denyut yang justru melebar KE LUAR. Dipasang tetap, cincin
         * denyutnya tidak pernah terlihat sama sekali; terukur di potret
         * diam: tombolnya polos tanpa satu pun cincin.
         *
         * Dibatasi ke :hover, keduanya tidak pernah saling memotong:
         * cincinnya terlihat selama tetikusnya tidak di atas tombol, dan
         * begitu ia di atas tombol, kilaunya yang mengambil alih.
         */
        .sel-tombol:not(.sel-tombol-mati):hover { overflow: hidden; }

        .sel-tombol:not(.sel-tombol-mati)::before {
            content: '';
            position: absolute;
            top: 0;
            left: -60%;
            width: 45%;
            height: 100%;
            background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .45), transparent);
            transform: skewX(-18deg);
            pointer-events: none;
        }

        .sel-tombol:not(.sel-tombol-mati):hover::before {
            animation: sel-sapuan-kilau .7s ease;
        }
    }

    @keyframes sel-sapuan-kilau {
        from { left: -60%; }
        to   { left: 115%; }
    }

    /* Gerak minimal: ketiganya dimatikan, sama seperti di landing-nya. */
    @media (prefers-reduced-motion: reduce) {
        .sel-tombol:not(.sel-tombol-mati)::after,
        .sel-tombol:not(.sel-tombol-mati) .fa-arrow-right,
        .sel-tombol:not(.sel-tombol-mati):hover::before {
            animation: none !important;
        }
    }

    .sel-kosong {
        max-width: 460px;
        margin: 0 auto;
        padding: 46px 28px;
        border-radius: 20px;
        border: 1px solid var(--garis);
        background: #fff;
        text-align: center;
    }

    .sel-kosong > .fas {
        margin: 0 0 14px !important;
        display: block;
        font-size: 2.4rem;
        /* Abu-abu khusus untuk ketiadaan data; di tempat lain warna aksen. */
        color: #cbd5e1;
    }

    .sel-kosong-judul { margin: 0 0 6px; font-size: 1.05rem; font-weight: 800; color: var(--navy); }
    .sel-kosong-sub { margin: 0; font-size: .9rem; color: var(--tinta-2); }
</style>
@stop
