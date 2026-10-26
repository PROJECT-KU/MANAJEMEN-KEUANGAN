@extends('public.layout.header')

@section('title', 'Pendaftaran ' . $item->nama . ' #' . $item->nama_ke . ' | Rumah Scopus')

@push('kepala')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600..900&family=Figtree:wght@400;500;700;800&display=swap">
@endpush

@section('konten')
{{--
    Borang pendaftaran Scopus Camp — dirombak 23 Okt 2026.

    Dua hal sekaligus, dan keduanya diminta pemiliknya: tampilannya disamakan
    dengan halaman daftar dan halaman detail, dan pembayarannya pindah ke
    gerbang DOKU.

    Yang HILANG dari versi lama, berikut sebabnya:

      - palet ungu-indigo #6366f1 dengan kaca buram. Seluruh jalur Scopus
        Camp sudah memakai kertas krem dan jingga; borang ini satu-satunya
        layar yang berganti warna di tengah alur, tepat saat orangnya
        hendak membayar;
      - nomor rekening dan unggah bukti transfer, SELAMA gerbangnya siap.
        Jalurnya: salin nomor, pindah ke aplikasi bank, kembali, potret
        bukti, unggah, lalu menunggu panitia membuka gambarnya. Terukur di
        basis data ini, enam bukti menunggu diperiksa 177, 127, 74, 28, 17,
        dan 6 hari. Dengan gerbang, orangnya menekan satu tombol dan
        statusnya berpindah sendiri;
      - `position: sticky` pada kartu ringkasan. Di dalam butir CSS Grid ia
        TIDAK PERNAH menempel — sudah diukur di halaman detail — tetapi
        tetap menggeser kartunya sejauh nilai `top`-nya.

    JALUR TRANSFER MANUAL SUDAH DIBUANG SELURUHNYA, 26 Okt 2026, atas
    keputusan pemiliknya: pembayaran layanan ini seluruhnya lewat gerbang.

    Yang dibuang bukan sekadar tampilan kartu rekening dan kotak unggahnya.
    Jalur itu menerima BERKAS dari orang luar tanpa satu pun aturan validasi —
    tanpa mimes, tanpa max — lalu menyimpannya ke public/ScopusCamp memakai
    ekstensi kiriman pengunggah, di folder yang tersaji lewat HTTP. Berkas
    .htaccess di sana memang mematikan mesin PHP, tetapi .html dan .svg tidak
    ada di daftar tolaknya: halaman buatan orang luar bisa berdiri di domain
    kita sendiri.

    Borang ini karena itu hanya digambar kalau gerbangnya siap; pengendalinya
    memantulkan orangnya kembali kalau tidak, dan store() menolak sekali lagi
    di peladen bagi yang menembak alamatnya langsung.
--}}
<style>
    .daf {
        --kertas: #fdf5e8;  --kertas-2: #f8ead4;
        --tinta: #16233f;   --tinta-2: #5c6b85;
        --jingga: #e76d20;  --jingga-muda: #fb8c3d; --jingga-tua: #ad4d12;
        --stabilo: #ffd84d; --rumput: #0f766e;
        --garis: #e7dcc6;

        background: var(--kertas); color: var(--tinta);
        font-family: 'Figtree', system-ui, sans-serif;
        /* 18px, bukan 16px bawaan templat: pembacanya rata-rata berusia
           lanjut, dan ukuran bawaannya terbaca kecil di layar mereka. */
        font-size: 18px;
        padding: 112px 0 64px;
        overflow-x: clip;
    }
    .daf h1, .daf h2, .daf h3 {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 800; color: var(--tinta); margin: 0; letter-spacing: -.01em;
    }
    .daf p { margin: 0; color: var(--tinta-2); line-height: 1.7; }
    .daf a { text-decoration: none; }

    .daf-balik {
        display: inline-flex; align-items: center; gap: 8px; margin-bottom: 16px;
        color: var(--tinta-2); font-weight: 700; font-size: .95rem;
        transition: gap .2s ease, color .2s ease;
    }
    .daf-balik:hover { color: var(--jingga-tua); gap: 13px; }

    .daf .daf-judul { font-size: clamp(1.6rem, 3.2vw, 2.3rem); line-height: 1.15; }
    .daf-anak { margin-top: 8px; font-size: 1.02rem; }

    /* ==================================================== susunan kolom */
    .daf-kisi {
        display: grid; grid-template-columns: minmax(0, 1fr) 390px; gap: 30px;
        align-items: start; margin-top: 34px;
    }

    .daf-kartu {
        background: #fff; border: 1px solid var(--garis); border-radius: 22px;
        padding: 26px 26px 28px; margin-bottom: 24px;
        box-shadow: 0 10px 28px rgba(22, 35, 63, .06);
    }
    .daf-kartu:last-child { margin-bottom: 0; }

    .daf-kepala {
        display: flex; align-items: center; gap: 11px;
        font-family: 'Fraunces', Georgia, serif; font-weight: 800;
        font-size: 1.22rem; color: var(--tinta);
        padding-bottom: 14px; margin-bottom: 20px;
        border-bottom: 2px solid var(--kertas-2);
    }
    .daf-kepala i { color: var(--jingga); }

    /* ==================================================== ringkasan kelas */
    /* Nama kelasnya berdiri sendiri di puncak kartu, bukan salah satu
       pasangan label-nilai: ia identitas barangnya. */
    /*
     * Pemilihnya `.daf .daf-kelas-nama` — bobot (0,2,0), bukan (0,1,0).
     *
     * `.daf p` di atas berbobot (0,1,1) karena ia kelas DAN unsur, jadi ia
     * menang atas satu kelas saja walau ditulis lebih dulu. Terukur: nama
     * kelasnya tergambar rgb(92,107,133) — warna redup untuk kalimat biasa —
     * padahal diminta #16233f. Tidak ada galat; yang terlihat cuma judul yang
     * pucat, dan itu mudah dikira memang begitu desainnya.
     */
    .daf .daf-kelas-nama {
        margin: -4px 0 18px;
        font-family: 'Fraunces', Georgia, serif;
        font-size: 1.32rem; font-weight: 800; line-height: 1.25;
        color: var(--tinta);
    }
    .daf-kelas-nomor { color: var(--tinta-2); font-weight: 700; }

    /* Baris label KIRI, nilai KANAN — sama persis dengan kartu Rincian biaya
       dan dengan kwitansi di halaman status, jadi ketiganya dibaca dengan
       cara yang sama. */
    .daf-baris-daftar { margin: 0; }
    .daf-baris {
        display: flex; align-items: baseline; justify-content: space-between;
        gap: 18px; padding: 12px 0;
        border-bottom: 1px dashed var(--garis);
    }
    .daf-baris:first-child { padding-top: 0; }
    .daf-baris:last-child { border-bottom: 0; padding-bottom: 0; }
    .daf-baris dt {
        flex: 0 0 auto;
        font-size: .76rem; font-weight: 800; letter-spacing: .06em;
        text-transform: uppercase; color: var(--tinta-2);
    }
    .daf-baris dd {
        flex: 1 1 auto; min-width: 0; margin: 0; text-align: right;
        font-weight: 700; font-size: 1.02rem; color: var(--tinta);
        line-height: 1.45;
    }

    /* Harganya dibesarkan dan dijinggakan: ia angka yang paling dicari di
       kartu ini, dan di antara empat baris sewarna ia tidak terlihat. */
    .daf-baris-harga dd b {
        font-family: 'Fraunces', Georgia, serif;
        font-size: 1.2rem; font-weight: 900; color: var(--jingga-tua);
        font-variant-numeric: lining-nums tabular-nums;
    }
    .daf-coret { color: var(--tinta-2); font-weight: 600; margin-right: 7px; font-size: .95rem; }

    /* ==================================================== isian */
    .daf-medan { margin-bottom: 20px; }
    .daf-medan:last-child { margin-bottom: 0; }
    .daf-medan label {
        display: block; margin-bottom: 7px;
        font-size: .88rem; font-weight: 800; color: var(--tinta);
    }
    .daf-wajib { color: #c0392b; }
    .daf-opsional { font-weight: 600; color: var(--tinta-2); text-transform: none; letter-spacing: 0; }

    .daf-isian {
        width: 100%; background: #fff;
        border: 2px solid var(--garis); border-radius: 14px;
        padding: 13px 16px; font-size: 1.02rem; font-weight: 600;
        color: var(--tinta); font-family: inherit;
        transition: border-color .18s ease, box-shadow .18s ease;
    }
    .daf-isian::placeholder { color: #a9b3c4; font-weight: 500; }
    .daf-isian:focus {
        outline: none; border-color: var(--jingga);
        box-shadow: 0 0 0 4px rgba(231, 109, 32, .15);
    }

    .daf-dua { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }

    .daf-bantu {
        display: block; margin-top: 7px;
        font-size: .88rem; line-height: 1.5; color: var(--tinta-2);
    }

    /* ================================================== ringkasan galat */
    .daf-galat {
        margin-top: 22px; border-color: #e3b7b1; background: #fdf1ef;
    }
    .daf-galat p { color: #a3281c; font-weight: 700; margin: 0; }
    .daf-galat p i { margin-right: 7px; }
    .daf-galat ul {
        margin: 10px 0 0; padding-left: 26px;
        color: #a3281c; font-size: .95rem; line-height: 1.7;
    }

    /* Angka sisa kursi yang tinggal sedikit disebut dengan warna,
       bukan dengan tanda seru: yang membacanya orang berusia lanjut. */
    .daf-tipis { color: #a3281c; font-weight: 800; }

    /* ================================================== jumlah peserta */
    /*
     * Dipisahkan garis dari deret keterangan di atasnya.
     *
     * Tanpa garis, "Jumlah peserta" menempel di bawah "Biaya per orang" dan
     * terbaca sebagai keterangan kelima tentang kelasnya — padahal ia
     * satu-satunya hal di kartu ini yang bisa DIUBAH orangnya.
     */
    .daf-jumlah {
        margin-top: 22px; padding-top: 22px;
        border-top: 2px dashed var(--garis);
    }
    .daf-jumlah-baris { display: flex; align-items: stretch; gap: 10px; }
    .daf-jumlah-baris .daf-isian {
        flex: 1 1 auto; min-width: 0; text-align: center;
        font-size: 1.15rem; font-weight: 800;
    }
    .daf-langkah {
        /* 54px, bukan sekadar cukup untuk angkanya: patokan sasaran sentuh
           44px, dan pembacanya rata-rata berusia lanjut. */
        flex: 0 0 auto; width: 54px; min-height: 54px;
        border: 2px solid var(--garis); border-radius: 14px;
        background: #fff; color: var(--tinta);
        font-family: inherit; font-size: 1.5rem; font-weight: 800;
        line-height: 1; cursor: pointer;
        transition: border-color .18s ease, color .18s ease;
    }
    .daf-langkah:hover { border-color: var(--jingga); color: var(--jingga-tua); }
    .daf-langkah:disabled { opacity: .4; cursor: not-allowed; }

    /* ================================================== peserta lain */
    .daf-peserta {
        display: grid; grid-template-columns: 38px minmax(0, 1fr); gap: 14px;
        align-items: start; padding: 16px 0;
        border-top: 1px dashed var(--garis);
    }
    .daf-peserta:first-child { border-top: 0; padding-top: 0; }
    .daf-peserta-nomor {
        display: flex; align-items: center; justify-content: center;
        width: 38px; height: 38px; margin-top: 26px;
        border-radius: 50%; background: var(--kertas-2);
        color: var(--tinta); font-weight: 800; font-size: .95rem;
    }
    @media (max-width: 575.98px) {
        .daf-peserta { grid-template-columns: 1fr; gap: 8px; }
        .daf-peserta-nomor { margin-top: 0; }
    }

    /* ================================================== persetujuan */
    .daf-setuju {
        display: flex; align-items: flex-start; gap: 11px;
        margin: 22px 0 0; padding: 14px 15px;
        background: var(--kertas); border: 1px solid var(--garis); border-radius: 14px;
        font-size: .93rem; line-height: 1.55; color: var(--tinta);
        font-weight: 600; cursor: pointer;
    }
    .daf-setuju input {
        /* 22px, bukan bawaan 13px: kotak centang bawaan terlalu kecil untuk
           jari, dan ini satu-satunya centang yang menghalangi pengiriman. */
        flex: 0 0 auto; width: 22px; height: 22px; margin-top: 1px;
        accent-color: var(--jingga);
    }
    .daf-setuju a {
        color: var(--jingga-tua); text-decoration: underline;
        /* Padding tegak pada tautan sebaris MELEBARKAN daerah sentuhnya
           tanpa menggeser barisnya — kotak barisnya tidak ikut tinggi.
           Tidak sampai 44px; tautan di tengah kalimat tidak bisa setinggi
           itu tanpa merusak kalimatnya, dan ini tautan pendamping, bukan
           tombol utama. */
        padding: 7px 0;
    }

    /* Sasaran sentuh tautan "Kembali ke rincian kelas": terukur 23px
       sebelum ini, di bawah patokan 44px. */
    .daf-balik { padding: 11px 0; }

    /* ================================================== isi otomatis */
    .daf-kabar-isi {
        display: flex; align-items: flex-start; flex-wrap: wrap; gap: 9px;
        margin: 0 0 20px; padding: 13px 15px;
        background: #e8f5f2; border: 1px solid #bfe3db; border-radius: 13px;
        font-size: .92rem; line-height: 1.55; color: #0b5a53;
    }
    /* [hidden] kalah oleh display:flex di atas — bobotnya sama (0,1,0) dan
       aturan ini berdiri belakangan — jadi kotaknya tetap tergambar walau
       atribut hidden-nya ada. Dinyatakan sendiri, bukan diandaikan. */
    .daf-kabar-isi[hidden] { display: none; }
    .daf-kabar-isi i { margin-top: 3px; color: var(--rumput); }
    .daf-kabar-isi span { flex: 1 1 180px; min-width: 0; }
    .daf-kabar-isi button {
        flex: 0 0 auto; border: 0; border-radius: 10px; padding: 7px 14px;
        background: #0b5a53; color: #fff; font-family: inherit;
        font-size: .86rem; font-weight: 800; cursor: pointer;
    }
    .daf-kabar-isi button:hover { background: #09443f; }

    /* Isian yang baru saja diisikan sendiri DITANDAI.

       Tanpa tanda, tiga kotak yang mendadak terisi tanpa disentuh terbaca
       seperti halaman yang berkelakuan aneh — dan yang paling mungkin
       merasa begitu justru pembaca yang jadi sasaran layanan ini. */
    .daf-isian.daf-baru-diisi {
        border-color: #58b5a5;
        background: #f2fbf9;
    }

    /* ==================================================== kode diskon */
    .daf-diskon-baris { display: flex; gap: 10px; }
    .daf-diskon-baris .daf-isian { flex: 1 1 auto; min-width: 0; }
    .daf-cek {
        flex: 0 0 auto; border: 0; border-radius: 14px; padding: 0 20px;
        background: var(--tinta); color: #fff; font-weight: 800; font-size: .95rem;
        font-family: inherit; cursor: pointer; transition: background .18s ease;
    }
    .daf-cek:hover { background: var(--jingga-tua); }
    .daf-pesan { display: block; margin-top: 8px; font-size: .92rem; font-weight: 700; }
    .daf-pesan.ok { color: var(--rumput); }
    .daf-pesan.gagal { color: #c0392b; }

    /* ==================================================== rincian biaya */
    .daf-baris {
        display: flex; align-items: baseline; justify-content: space-between;
        gap: 14px; padding: 11px 0; border-bottom: 1px dashed var(--garis);
        font-size: 1rem;
    }
    .daf-baris span:first-child { color: var(--tinta-2); font-weight: 600; }
    .daf-baris b { font-weight: 800; color: var(--tinta); }
    .daf-baris.potong b { color: var(--rumput); }

    .daf-total {
        display: flex; align-items: baseline; justify-content: space-between;
        gap: 14px; margin-top: 16px; padding: 16px 18px;
        background: var(--kertas-2); border-radius: 16px;
    }
    .daf-total span { font-weight: 800; font-size: 1rem; color: var(--tinta); }
    .daf-total b {
        font-family: 'Fraunces', Georgia, serif; font-size: 1.5rem;
        font-weight: 900; color: var(--jingga-tua); line-height: 1.1;
        /* Fraunces memakai angka gaya lama: tingginya tidak seragam dan
           sebagian turun di bawah garis dasar. Pada nominal uang ia terbaca
           seperti salah cetak di sebelah "Rp" yang setinggi huruf besar. */
        font-variant-numeric: lining-nums tabular-nums;
    }

    /* ==================================================== tombol kirim */
    .daf-kirim {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        width: 100%; margin-top: 18px; padding: 0 22px;
        /* 60px: di atas patokan sasaran sentuh 44px, sebab pembacanya
           rata-rata berusia lanjut. */
        min-height: 60px;
        border: 0; border-radius: 16px; cursor: pointer;
        background: linear-gradient(135deg, var(--jingga-muda), var(--jingga-tua));
        color: #fff; font-family: 'Fraunces', Georgia, serif;
        font-size: 1.1rem; font-weight: 800; line-height: 1.25;
        box-shadow: 0 10px 24px rgba(231, 109, 32, .32);
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .daf-kirim:hover { transform: translateY(-2px); box-shadow: 0 16px 32px rgba(231, 109, 32, .4); }
    .daf-kirim:disabled { opacity: .72; cursor: wait; transform: none; }

    .daf-aman {
        display: flex; align-items: flex-start; gap: 9px;
        margin-top: 14px; font-size: .9rem; line-height: 1.5; color: var(--tinta-2);
    }
    .daf-aman i { color: var(--rumput); margin-top: 3px; }

    /* ==================================================== kanal bayar */
    .daf-kanal { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
    .daf-kanal span {
        padding: 6px 13px; border-radius: 999px;
        background: var(--kertas-2); border: 1px solid var(--garis);
        font-size: .86rem; font-weight: 700; color: var(--tinta);
    }

    /* Aturan rupa transfer manual — .daf-rekening, .daf-salin, .daf-unggah,
       .daf-pratinjau, .daf-berkas — DIBUANG 26 Okt 2026 bersama jalurnya.

       Markahnya sudah hilang lebih dulu, tetapi aturannya tetap terkirim ke
       tiap pengunjung: beban yang tidak menggambar apa pun, dan — yang lebih
       merugikan — cetakan siap pakai yang membuat menghidupkan kembali kotak
       unggahnya terasa seperti menambah satu baris markah saja. */

    /* ==================================================== layar sempit */
    @media (max-width: 991.98px) {
        .daf-kisi { grid-template-columns: 1fr; gap: 24px; }
    }
    @media (max-width: 575.98px) {
        .daf { font-size: 17px; padding-top: 96px; }
        .daf-kartu { padding: 22px 18px 24px; border-radius: 18px; }
        .daf-dua { grid-template-columns: 1fr; }

        /*
         * Di bawah 576px label dan nilainya BERTUMPUK, bukan berhadapan.
         *
         * Nilai terpanjangnya "30 Oktober – 1 November 2026"; berhadapan
         * dengan labelnya di layar selebar 360px ia tersisa sekitar 150px dan
         * pecah jadi tiga baris di sebelah label satu baris. Bertumpuk, tiap
         * keterangan tetap utuh.
         */
        .daf-baris { display: block; }
        .daf-baris dt { display: block; margin-bottom: 3px; }
        .daf-baris dd { text-align: left; }
        .daf-diskon-baris { flex-wrap: wrap; }
        .daf-cek { width: 100%; min-height: 50px; }
    }
</style>

<section class="daf">
    <div class="container">

        <a href="{{ route('public.scopuscamp.Selengkapnya', $item->id) }}" class="daf-balik">
            <i class="fas fa-arrow-left"></i> Kembali ke rincian kelas
        </a>

        <h1 class="daf-judul">Formulir pendaftaran</h1>
        <p class="daf-anak">
            {{-- "Empat" tidak lagi benar sejak jumlah peserta dan centang
                 ketentuan ditambahkan; angka yang meleset di kalimat pembuka
                 membuat sisa halaman ikut terbaca tidak bisa dipercaya. --}}
            Isi data di bawah, lalu
            {{-- Dirakit sebagai ungkapan, bukan sebagai percabangan sebaris:
                 cabang sebaris meninggalkan spasi tepat sebelum titiknya,
                 jadi yang tercetak "lanjutkan ke pembayaran ." --}}
            lanjutkan ke pembayaran.
            Butuh sekitar dua menit.
        </p>

        @if (session('error'))
            <div class="daf-kartu daf-galat" role="alert">
                <p><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</p>
            </div>
        @endif

        {{-- RINGKASAN GALAT, dan sampai 23 Okt 2026 tidak ada sama sekali.

             Peladen tidak memvalidasi apa pun, jadi memang tidak pernah ada
             yang perlu ditampilkan. Begitu validasinya dipasang, kiriman yang
             ditolak memulangkan halaman yang sama — dan tanpa kotak ini
             orangnya cuma melihat borang yang sama tanpa satu tanda pun bahwa
             ada yang salah, lalu menekan tombolnya lagi. --}}
        @if ($errors->any())
            <div class="daf-kartu daf-galat" role="alert">
                <p><i class="fas fa-exclamation-circle"></i>
                    Ada {{ $errors->count() }} isian yang perlu diperbaiki dulu:</p>
                <ul>
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="formKonfirmasi" method="POST" action="{{ route('public.scopuscamp.store') }}"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="kategori_id" id="scopusCampIdvalue" value="{{ $item->id }}">

            @php
                $fMulai = \Carbon\Carbon::parse($item->mulai)->locale('id');
                $fSelesai = $item->selesai ? \Carbon\Carbon::parse($item->selesai) : null;

                /*
                 * Dihitung dari SATU kursi, seperti sebelumnya, dan seperti
                 * App\Support\HargaUmum menghitungnya di peladen. Kejanggalan
                 * itu dipertahankan apa adanya: membetulkannya di sini berarti
                 * mengubah nominal yang ditagihkan untuk alasan yang berbeda
                 * dari perkara tampilan, dan dua perubahan uang dalam satu
                 * langkah tidak bisa ditelusuri kalau salah satunya keliru.
                 */
                $jumlahPPN = ($item->ppn > 0) ? $item->harga_setelah_promo * ($item->ppn / 100) : 0;
            @endphp

            <div class="daf-kisi">

                <div class="daf-kolom-kiri">

                    {{-- RINGKASAN, bukan tujuh kotak isian.

                         Kartu ini dulu berisi tujuh <input readonly>: Batch,
                         Lokasi, Sisa Kuota, Mulai, Selesai, Biaya, dan Kode
                         Unik. Semuanya tampak seperti isian yang menunggu
                         diisi, padahal tidak satu pun bisa diubah, dan
                         peladen pun mengabaikan lima di antaranya. --}}
                    <div class="daf-kartu">
                        <div class="daf-kepala">
                            <i class="fas fa-campground"></i> Kelas yang Anda daftarkan
                        </div>

                        {{-- NAMA KELASNYA berdiri sendiri, bukan salah satu
                             pasangan label-nilai.

                             Ia identitas barangnya, bukan salah satu
                             keterangan tentangnya — di dalam deret label-nilai
                             ia tenggelam di antara tanggal dan lokasi, padahal
                             itu satu-satunya hal yang dipastikan orang sebelum
                             membayar. --}}
                        <p class="daf-kelas-nama">
                            {{ $item->nama }}@if ($item->nama_ke) <span class="daf-kelas-nomor">#{{ $item->nama_ke }}</span>@endif
                        </p>

                        {{-- Label KIRI, nilai KANAN — sama persis dengan kartu
                             Rincian biaya di sebelahnya dan dengan kwitansi di
                             halaman status.

                             Kisi dua kolom yang labelnya di ATAS nilainya
                             membuat mata melompat dua kali untuk tiap
                             keterangan, dan menyisakan sel kosong di sebelah
                             keterangan terakhir saat jumlahnya ganjil —
                             terukur, "Biaya per orang" duduk sendirian di
                             baris ketiga. --}}
                        <dl class="daf-baris-daftar">
                            <div class="daf-baris">
                                <dt>Tanggal</dt>
                                <dd>{{ \App\Support\RentangTanggal::tulis($fMulai, $fSelesai) }}</dd>
                            </div>

                            <div class="daf-baris">
                                <dt>Lokasi</dt>
                                <dd>{{ $item->lokasi ?: 'Akan dikabari panitia' }}</dd>
                            </div>

                            {{-- SISA KURSI, dan sebelum 23 Okt 2026 tidak
                                 disebut di halaman ini sama sekali.

                                 Dua akibatnya: tidak ada alasan untuk
                                 bergegas, dan orang bisa mengisi seluruh
                                 borang lalu baru ditolak saat menekan
                                 tombol. --}}
                            <div class="daf-baris">
                                <dt>Sisa kursi</dt>
                                <dd>
                                    @if ($sisaKursi === null)
                                        Tidak dibatasi
                                    @elseif ($sisaKursi <= 3)
                                        <span class="daf-tipis">{{ $sisaKursi }} kursi lagi</span>
                                    @else
                                        {{ $sisaKursi }} kursi
                                    @endif
                                </dd>
                            </div>

                            <div class="daf-baris daf-baris-harga">
                                <dt>Biaya per orang</dt>
                                <dd>
                                    @if ($item->harga_sebelum_promo > $item->harga_setelah_promo)
                                        <s class="daf-coret">Rp {{ number_format($item->harga_sebelum_promo, 0, ',', '.') }}</s>
                                    @endif
                                    <b>Rp {{ number_format($item->harga_setelah_promo, 0, ',', '.') }}</b>
                                </dd>
                            </div>
                        </dl>

                        {{-- harga_setelah_promo, BUKAN $item->biaya.

                             Halaman daftarnya MENCORET `biaya` dan menjanjikan
                             harga promonya; borang ini dulu menagih angka yang
                             dicoret itu. Terukur 18 angkatan terkena — Scopus
                             Camp Padang menjanjikan Rp 5.060.000 lalu menagih
                             Rp 5.500.000. Lihat App\Support\HargaUmum. --}}
                        <input type="hidden" name="biaya" id="biaya"
                            value="{{ number_format($item->harga_setelah_promo, 0, ',', '.') }}">
                        <input type="hidden" name="kode_unik" id="kodeUnik" value="{{ old('kode_unik') }}">
                        {{-- JUMLAH PESERTA, dan dulu ia isian tersembunyi
                             bernilai 1 yang tidak bisa diubah siapa pun.

                             Padahal peladen dan rumus harganya sudah
                             mendukung lebih dari satu sejak awal: satu kantor
                             yang mengirim tiga orang harus mengisi borang ini
                             tiga kali, dengan tiga pembayaran terpisah yang
                             harus dicocokkan panitia satu per satu.

                             Batas atasnya sisa kursi yang benar-benar ada,
                             bukan angka tetap — dan peladen memeriksanya lagi
                             di dalam kunci baris, sebab nilai di sini bisa
                             disunting siapa saja sebelum dikirim. --}}
                        <div class="daf-medan daf-jumlah">
                            <label for="jumlahPendaftar">Jumlah peserta</label>
                            <div class="daf-jumlah-baris">
                                <button type="button" class="daf-langkah" data-ubah="-1"
                                    aria-label="Kurangi satu peserta">&minus;</button>
                                <input type="number" name="jumlah_pendaftar" id="jumlahPendaftar"
                                    class="daf-isian" value="{{ old('jumlah_pendaftar', 1) }}"
                                    min="1" max="{{ $sisaKursi === null ? 20 : min(20, max(1, $sisaKursi)) }}"
                                    inputmode="numeric" required>
                                <button type="button" class="daf-langkah" data-ubah="1"
                                    aria-label="Tambah satu peserta">+</button>
                            </div>
                            <small class="daf-bantu">
                                Mendaftarkan beberapa orang sekaligus? Ubah angkanya —
                                satu pembayaran untuk semuanya.
                            </small>
                        </div>
                        <input type="hidden" name="subtotal" id="subtotalInput" value="{{ $item->harga_setelah_promo }}">
                        <input type="hidden" name="ppn" id="jumlahPPN" value="{{ $jumlahPPN }}">
                    </div>

                    {{-- NOMOR WHATSAPP DULU, baru sisanya.

                         Diminta pemiliknya 23 Okt 2026: yang pernah memesan
                         cukup mengetik nomornya, dan nama, email, serta asal
                         instansinya terisi sendiri dari pesanan terakhirnya.

                         Urutannya ikut berubah, dan itu bukan soal rupa:
                         nomor yang duduk di kotak TERAKHIR berarti orangnya
                         sudah terlanjur mengetik ketiga isian lain sebelum
                         pengisian otomatisnya sempat menolong — dan isian
                         yang sudah terisi tidak pernah ditimpa. --}}
                    <div class="daf-kartu">
                        <div class="daf-kepala">
                            <i class="fas fa-user"></i> Data diri peserta
                        </div>

                        <div class="daf-medan">
                            <label for="telp">Nomor WhatsApp <span class="daf-wajib">*</span></label>
                            <input type="text" name="telp" id="telp"
                                value="{{ old('telp', $isiAwal['telp'] ?? '') }}"
                                placeholder="0812-3456-7890" class="daf-isian"
                                inputmode="numeric" maxlength="20" minlength="8"
                                autocomplete="tel"
                                oninput="formatPhoneNumber(this)" required>
                            <small class="daf-bantu">
                                Isi nomor Anda dulu. Kalau pernah memesan di Rumah Scopus,
                                sisanya kami isikan sendiri.
                            </small>
                        </div>

                        {{-- role="status" + aria-live: pembaca layar harus ikut
                             diberi tahu bahwa tiga isian baru saja berubah
                             sendiri, bukan cuma yang melihat layarnya. --}}
                        <p class="daf-kabar-isi" id="daf-kabar-isi" role="status"
                            aria-live="polite" hidden></p>

                        <div class="daf-dua">
                            <div class="daf-medan">
                                <label for="nama">Nama lengkap &amp; gelar <span class="daf-wajib">*</span></label>
                                <input type="text" name="nama" id="nama"
                                    value="{{ old('nama', $isiAwal['nama'] ?? '') }}"
                                    placeholder="John Doe, S.Kom." class="daf-isian"
                                    autocomplete="name" required>
                            </div>
                            <div class="daf-medan">
                                <label for="email">Email aktif <span class="daf-wajib">*</span></label>
                                <input type="email" name="email" id="email"
                                    value="{{ old('email', $isiAwal['email'] ?? '') }}"
                                    placeholder="email@domain.com" class="daf-isian"
                                    maxlength="100" minlength="5"
                                    autocomplete="email" required>
                            </div>
                        </div>

                        <div class="daf-medan" style="margin-top: 20px;">
                            <label for="affiliasi">Asal instansi <span class="daf-wajib">*</span></label>
                            <input type="text" name="affiliasi" id="affiliasi"
                                value="{{ old('affiliasi', $isiAwal['affiliasi'] ?? '') }}"
                                placeholder="Universitas / Instansi" class="daf-isian"
                                autocomplete="organization" minlength="3" required>
                        </div>
                    </div>

                    {{-- PESERTA LAINNYA.

                         Tanpa bagian ini, mendaftarkan lima orang menagih
                         biaya lima orang lalu menyimpan SATU nama — panitia
                         memegang daftar hadir berisi satu, dan empat
                         sertifikat tidak bisa diterbitkan atas nama siapa
                         pun. Sisanya harus dikejar lewat WhatsApp satu per
                         satu.

                         Barisnya dirakit JavaScript mengikuti jumlah peserta,
                         TETAPI yang sudah diketik dirender ulang peladen dari
                         old(): kiriman yang ditolak validasi memulangkan
                         halaman yang sama, dan tanpa itu seluruh nama yang
                         sudah diketik hilang. --}}
                    <div class="daf-kartu" id="kartuPesertaLain" hidden>
                        <div class="daf-kepala">
                            <i class="fas fa-users"></i> Peserta lainnya
                        </div>

                        <p class="daf-bantu" style="margin: -8px 0 18px;">
                            Nama di sini dipakai untuk daftar hadir dan sertifikat.
                            Nomornya dipakai panitia memasukkan mereka ke grup —
                            boleh dikosongkan kalau belum tahu.
                        </p>

                        @error('peserta')
                            <p class="daf-pesan gagal" style="margin-bottom: 16px;">{{ $message }}</p>
                        @enderror

                        <div id="daftarPesertaLain">
                            @foreach (old('peserta', []) as $i => $orang)
                                <div class="daf-peserta" data-baris="{{ $i }}">
                                    <span class="daf-peserta-nomor">{{ $i + 2 }}</span>
                                    <div class="daf-dua">
                                        <div class="daf-medan">
                                            <label>Nama lengkap &amp; gelar <span class="daf-wajib">*</span></label>
                                            <input type="text" name="peserta[{{ $i }}][nama]"
                                                value="{{ $orang['nama'] ?? '' }}"
                                                class="daf-isian" placeholder="Nama peserta" required>
                                        </div>
                                        <div class="daf-medan">
                                            <label>Nomor WhatsApp <span class="daf-opsional">— boleh dikosongkan</span></label>
                                            <input type="text" name="peserta[{{ $i }}][telp]"
                                                value="{{ $orang['telp'] ?? '' }}"
                                                class="daf-isian" placeholder="0812-3456-7890"
                                                inputmode="numeric" maxlength="20"
                                                oninput="formatPhoneNumber(this)">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Kartu nomor rekening dan unggah bukti transfer DIBUANG
                         26 Okt 2026, atas keputusan pemiliknya: pembayaran
                         layanan ini seluruhnya lewat gerbang.

                         Yang dibuang bukan sekadar tampilannya. Jalur itu
                         menerima BERKAS dari orang luar tanpa satu pun aturan
                         validasi lalu menyimpannya ke public/ScopusCamp
                         memakai ekstensi kiriman pengunggah, di folder yang
                         tersaji lewat HTTP. Dibiarkan sebagai cabang mati di
                         sini, ia tinggal menunggu satu orang menghidupkannya
                         lagi tanpa tahu apa yang ia buka. --}}
                </div>

                <div class="daf-kolom-kanan">
                    <div class="daf-kartu">
                        <div class="daf-kepala">
                            <i class="fas fa-receipt"></i> Rincian biaya
                        </div>

                        <div class="daf-medan">
                            <label for="kodeDiskon">
                                Punya kode diskon? <span class="daf-opsional">— boleh dikosongkan</span>
                            </label>
                            <div class="daf-diskon-baris">
                                <input type="text" name="kode_diskon_input" id="kodeDiskon" class="daf-isian"
                                    placeholder="Ketik kode diskon" autocomplete="off">
                                <button type="button" class="daf-cek" id="btnCekDiskon" data-id="{{ $item->id }}">
                                    <i class="fas fa-tag"></i> Cek
                                </button>
                            </div>
                            <input type="hidden" name="kode_diskon" id="hiddenKodeDiskon">
                            <small id="diskonMessage" class="daf-pesan ok"></small>
                        </div>

                        <div style="margin-top: 22px;">
                            <div class="daf-baris">
                                {{-- Jumlah pesertanya ikut disebut di barisnya,
                                     bukan cuma di isiannya: begitu angkanya lebih
                                     dari satu, "Biaya kelas Rp 5.479.000" di sebelah
                                     total Rp 10.958.000 terbaca seperti salah hitung. --}}
                                <span>Biaya kelas <span id="rincianJumlah"></span></span>
                                <b>Rp <span id="rincianSubtotal">{{ number_format($item->harga_setelah_promo, 0, ',', '.') }}</span></b>
                            </div>

                            {{-- Kode unik dipajang DI SEBELAH totalnya, bukan di antara
                                 keterangan kelas: ia bagian dari nominal yang harus
                                 dibayar. Diperbarui JavaScript bersama totalnya; isian
                                 tersembunyi #kodeUnik tetap yang dikirim ke peladen. --}}
                            <div class="daf-baris">
                                <span>Kode unik</span>
                                <b>Rp <span id="kodeUnikTeks">0</span></b>
                            </div>

                            @if ($jumlahPPN > 0)
                                <div class="daf-baris">
                                    <span>PPN</span>
                                    <b>Rp {{ number_format($jumlahPPN, 0, ',', '.') }}</b>
                                </div>
                            @endif

                            {{-- Disembunyikan selama belum ada potongan.

                                 "Diskon — Rp 0" yang selalu terpampang di atas
                                 totalnya terbaca sebagai potongan yang GAGAL
                                 dipakai, bukan sebagai potongan yang memang
                                 tidak diminta. --}}
                            <div class="daf-baris potong" id="formDiskon" style="display: none;">
                                <span>Diskon</span>
                                <b>&minus; Rp <span id="diskonText">0</span></b>
                                <input type="hidden" name="nominal_diskon" id="potonganDiskon" value="0">
                            </div>

                            <div class="daf-total">
                                <span>Total bayar</span>
                                <b>Rp <span id="TotalPembayaran">0</span></b>
                                <input type="hidden" name="total_pembayaran" id="totalPembayaranInput">
                            </div>
                        </div>

                        <input type="hidden" name="total_keseluruhan_pembayaran" id="totalKeseluruhanPembayaranhidden">

                        {{-- PERSETUJUAN KETENTUAN, dan borang ini belum
                             pernah punya satu pun.

                             Borang Webinar Eksklusif sudah memakainya sejak
                             lama. Tanpa ini tidak ada jejak bahwa pendaftar
                             pernah diberi tahu aturan pembatalannya — dan
                             yang menanggung perdebatannya panitia, saat
                             uangnya sudah masuk. --}}
                        <label class="daf-setuju">
                            <input type="checkbox" name="setuju" id="setuju" value="1"
                                {{ old('setuju') ? 'checked' : '' }} required>
                            <span>
                                Saya sudah membaca dan menyetujui
                                <a href="{{ url('/ketentuan-layanan') }}" target="_blank" rel="noopener">ketentuan layanan</a>,
                                termasuk aturan pembatalan dan pengembalian dana.
                            </span>
                        </label>

                        <button type="submit" class="daf-kirim" id="btnSubmit">
                            <i class="fas fa-lock"></i> Lanjut ke pembayaran
                        </button>

                        <div class="daf-aman">
                                <i class="fas fa-shield-alt"></i>
                                <span>Pembayaran diproses DOKU. Setelah membayar, status
                                    pendaftaran Anda berpindah sendiri — tidak perlu
                                    mengirim bukti transfer.</span>
                            </div>

                            {{-- TENGGATNYA DISEBUT di sini, bukan baru
                                 ditemukan orangnya saat tagihannya sudah mati.

                                 Satu-satunya kata "menit" di halaman ini
                                 sebelumnya adalah "butuh sekitar dua menit" —
                                 tentang mengisi borang, bukan tentang
                                 membayar. --}}
                            <div class="daf-aman">
                                <i class="fas fa-hourglass-half" style="color: var(--jingga);"></i>
                                <span>Tagihannya berlaku <strong>{{ $menitBayar }} menit</strong>.
                                    Lewat dari itu kursinya dilepas lagi, dan Anda perlu
                                    mendaftar ulang.</span>
                            </div>

                            <p style="margin-top: 14px; font-size: .88rem; font-weight: 700; color: var(--tinta);">
                                Bisa dibayar lewat
                            </p>
                            {{-- QRIS dan Virtual Account SAJA, keputusan
                                 pemiliknya 23 Okt 2026.

                                 Daftar ini cuma janji: yang menentukan pilihan
                                 sebenarnya halaman DOKU. Jadi pembatasannya
                                 ikut dikirim bersama tagihannya — lihat
                                 Doku::KANAL_QRIS_DAN_VA — supaya yang dibaca
                                 di sini sama dengan yang ditemukan di sana. --}}
                            <div class="daf-kanal">
                                <span>QRIS</span>
                                <span>Virtual Account</span>
                            </div>
                    </div>
                </div>

            </div>
        </form>

    </div>
</section>

<script>
    (function () {
        var borang = document.getElementById('formKonfirmasi');
        var tombol = document.getElementById('btnSubmit');

        /*
         * Jalur bayarnya dibaca dari BORANGNYA, bukan ditulis ulang di sini.
         *
         * Yang menentukan satu-satunya jawaban ada di pengendali; menyalinnya
         * ke JavaScript berarti dua sumber jawaban, dan yang kedua akan
         * ketinggalan diam-diam.
         */

        borang.addEventListener('submit', function (e) {
            e.preventDefault();


            Swal.fire({
                title: 'Sudah benar semua?',
                text: 'Setelah ini Anda dibawa ke halaman pembayaran.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e76d20',
                confirmButtonText: 'Ya, lanjut bayar',
                cancelButtonText: 'Periksa lagi',
                reverseButtons: true
            }).then(function (hasil) {
                if (! hasil.isConfirmed) {
                    return;
                }

                tombol.disabled = true;
                tombol.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' +
                    'Menyiapkan pembayaran...';

                /*
                 * Rel yang meluncur, BUKAN Swal.showLoading().
                 *
                 * Cincin bawaannya menyampaikan "sedang sibuk" tetapi tidak
                 * menyampaikan bahwa sesuatu sedang BERJALAN — di tengah
                 * kotak seluas ini ia terbaca seperti halaman yang
                 * menggantung. Yang menunggunya orang yang baru menekan
                 * tombol bayar lima juta rupiah.
                 *
                 * Kalimat di bawahnya menyebut APA yang sedang dikerjakan
                 * dan ke mana ia akan dibawa, supaya perpindahan ke domain
                 * DOKU tidak terasa seperti halaman yang nyasar.
                 */
                Swal.fire({
                    title: 'Menyiapkan pembayaran',
                    html: '<div class="dlg-tunggu">'
                        + '<div class="dlg-rel"></div>'
                        + '<div class="dlg-langkah">'
                        + 'Kami sedang membuat tagihan Anda, lalu membuka halaman '
                        + 'pembayaran <b>DOKU</b>.<br>Mohon jangan tutup halaman ini.'
                        + '</div></div>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false
                });

                setTimeout(function () { borang.submit(); }, 250);
            });
        });
    })();
</script>

<script>
    document.getElementById('btnCekDiskon').addEventListener('click', function () {
        var id = this.dataset.id;
        var kode = document.getElementById('kodeDiskon').value.trim();

        var pesan = document.getElementById('diskonMessage');
        var tersembunyi = document.getElementById('hiddenKodeDiskon');
        var potongan = document.getElementById('potonganDiskon');
        var teksDiskon = document.getElementById('diskonText');

        function kosongkan() {
            tersembunyi.value = '';
            potongan.value = 0;
            teksDiskon.textContent = '0';
            document.getElementById('formDiskon').style.display = 'none';
            hitungTotalPembayaran();
        }

        if (! kode) {
            kosongkan();
            pesan.textContent = '';
            Swal.fire({
                icon: 'warning',
                title: 'Kode masih kosong',
                text: 'Ketik dulu kode diskonnya, atau lewati bagian ini.'
            });
            return;
        }

        fetch('/Scopus-Camp/cek-kode-diskon/' + encodeURIComponent(id) +
                '?kode_diskon=' + encodeURIComponent(kode))
            .then(function (jawab) { return jawab.json(); })
            .then(function (data) {
                if (data.status === 'success') {
                    var nominal = Number(data.casted_nominal_diskon) || 0;
                    tersembunyi.value = kode;
                    potongan.value = nominal;
                    teksDiskon.textContent = nominal.toLocaleString('id-ID');
                    document.getElementById('formDiskon').style.display = nominal > 0 ? '' : 'none';
                    pesan.textContent = 'Potongan Rp ' + nominal.toLocaleString('id-ID') + ' sudah dipakai.';
                    pesan.className = 'daf-pesan ok';
                    hitungTotalPembayaran();
                    Swal.fire({
                        icon: 'success',
                        title: 'Kode diskon dipakai',
                        timer: 1800,
                        showConfirmButton: false
                    });
                } else {
                    kosongkan();
                    pesan.textContent = data.message || 'Kode itu tidak berlaku.';
                    pesan.className = 'daf-pesan gagal';
                    Swal.fire({
                        icon: 'error',
                        title: 'Kode tidak berlaku',
                        text: data.message || 'Kodenya tidak ditemukan.'
                    });
                }
            })
            .catch(function () {
                kosongkan();
                pesan.textContent = '';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal memeriksa',
                    text: 'Kode diskonnya tidak bisa diperiksa sekarang.'
                });
            });
    });
</script>

<script>
    var jumlahPendaftarInput = document.getElementById('jumlahPendaftar');
    var biayaInput = document.getElementById('biaya');
    var kodeUnikInput = document.getElementById('kodeUnik');
    var totalPembayaranDisplay = document.getElementById('TotalPembayaran');
    var potonganDiskonInput = document.getElementById('potonganDiskon');

    function formatRupiah(angka) {
        return Number(angka).toLocaleString('id-ID');
    }

    /*
     * Rentangnya SAMA untuk semua layanan: 500-1500.
     *
     * Dulu halaman ini membuat 1-100, sementara layanan lain memakai rentang
     * yang berbeda-beda. Nominal yang penanda uniknya bergantung pada layanan
     * mana yang didaftar membuat panitia harus mengingat aturan berbeda untuk
     * tiap layanan saat mencocokkan mutasi rekening.
     *
     * Angkanya diambil dari PendaftaranSemuaLayanan::KODE_UNIK supaya tidak
     * ada dua tempat yang bisa berselisih.
     */
    function generateUniqueCode() {
        var r = @json(\App\Support\PendaftaranSemuaLayanan::KODE_UNIK);

        return Math.floor(Math.random() * (r[1] - r[0] + 1)) + r[0];
    }

    function hitungTotalPembayaran() {
        var jumlah = parseInt(jumlahPendaftarInput.value) || 0;
        var biaya = parseInt(biayaInput.value.replace(/\D/g, '')) || 0;
        var potonganDiskon = parseInt(String(potonganDiskonInput.value).replace(/\D/g, '')) || 0;
        var jumlahPPN = parseInt(document.getElementById('jumlahPPN').value) || 0;

        if (jumlah <= 0) {
            kodeUnikInput.value = '0';
            document.getElementById('kodeUnikTeks').textContent = '0';
            totalPembayaranDisplay.textContent = '0';
            document.getElementById('totalPembayaranInput').value = 0;
            return;
        }

        var kodeUnik = generateUniqueCode();
        var total = (jumlah * biaya) + kodeUnik - potonganDiskon + jumlahPPN;

        // Barisnya ikut menyebut berapa orang, dan subtotalnya dikalikan.
        var rincianJumlah = document.getElementById('rincianJumlah');
        if (rincianJumlah) {
            rincianJumlah.textContent = jumlah > 1 ? '\u00d7 ' + jumlah + ' orang' : '';
        }

        var rincianSubtotal = document.getElementById('rincianSubtotal');
        if (rincianSubtotal) {
            rincianSubtotal.textContent = formatRupiah(jumlah * biaya);
        }

        kodeUnikInput.value = formatRupiah(kodeUnik);
        // Isian #kodeUnik tersembunyi; angkanya tetap harus terlihat orang
        // karena ikut menentukan nominal yang dibayar.
        document.getElementById('kodeUnikTeks').textContent = formatRupiah(kodeUnik);
        totalPembayaranDisplay.textContent = formatRupiah(total);
        document.getElementById('totalPembayaranInput').value = total;
    }

    /*
     * Dihitung SEKARANG JUGA, bukan menunggu DOMContentLoaded.
     *
     * Tata letak publik ini memuat sembilan skrip yang menahan penguraian
     * halaman, salah satunya dari CDN di luar. Terukur: empat detik sesudah
     * halaman dibuka, document.readyState masih "loading" — dan selama itu
     * totalnya terbaca "Rp 0" pada halaman yang sedang menagih lima juta
     * rupiah. Tidak ada galat; yang melihatnya menyangka kelasnya gratis
     * atau halamannya rusak.
     *
     * Aman dipanggil di sini: seluruh unsur yang dibacanya digambar di atas
     * blok ini, jadi semuanya sudah ada saat baris ini jalan.
     */
    hitungTotalPembayaran();
    /*
     * Tombol +/- dan pengetikan langsung sama-sama menghitung ulang.
     *
     * Angkanya DIJEPIT ke rentang min/max isiannya di sini juga, bukan hanya
     * lewat atribut: atribut `max` cuma menghalangi tombol panah peramban —
     * angka yang diketik tangan lolos begitu saja, dan orangnya baru ditolak
     * sesudah menekan tombol kirim. Peladen tetap memeriksanya sekali lagi di
     * dalam kunci baris; yang di sini supaya tidak perlu sampai ke sana.
     */
    (function () {
        var kotak = document.getElementById('jumlahPendaftar');

        if (! kotak) {
            return;
        }

        var batasBawah = parseInt(kotak.min, 10) || 1;
        var batasAtas = parseInt(kotak.max, 10) || 20;
        var langkah = document.querySelectorAll('.daf-langkah');

        function rapikan() {
            var n = parseInt(kotak.value, 10);

            if (isNaN(n) || n < batasBawah) { n = batasBawah; }
            if (n > batasAtas) { n = batasAtas; }

            kotak.value = n;

            Array.prototype.forEach.call(langkah, function (t) {
                var arah = parseInt(t.dataset.ubah, 10);
                t.disabled = arah < 0 ? n <= batasBawah : n >= batasAtas;
            });

            hitungTotalPembayaran();
        }

        Array.prototype.forEach.call(langkah, function (t) {
            t.addEventListener('click', function () {
                kotak.value = (parseInt(kotak.value, 10) || batasBawah)
                    + parseInt(t.dataset.ubah, 10);
                rapikan();
            });
        });

        /*
         * Baris nama peserta dirakit MENGIKUTI jumlahnya, dan yang sudah
         * diketik TIDAK dibuang saat angkanya naik-turun.
         *
         * Membangun ulang seluruh daftar tiap kali angkanya berubah akan
         * menghapus nama yang sudah diketik begitu orangnya menekan "−" lalu
         * "+" sekali — dan di borang berisi sepuluh nama, itu kerugian yang
         * tidak bisa dibatalkan.
         */
        var wadah = document.getElementById('daftarPesertaLain');
        var kartu = document.getElementById('kartuPesertaLain');

        function barisPeserta(i) {
            var baris = document.createElement('div');
            baris.className = 'daf-peserta';
            baris.dataset.baris = String(i);

            var nomor = document.createElement('span');
            nomor.className = 'daf-peserta-nomor';
            // i+2: peserta pertama adalah pendaftar utamanya sendiri.
            nomor.textContent = String(i + 2);

            var dua = document.createElement('div');
            dua.className = 'daf-dua';
            dua.appendChild(medanPeserta(i, 'nama', 'Nama lengkap & gelar', true));
            dua.appendChild(medanPeserta(i, 'telp', 'Nomor WhatsApp', false));

            baris.appendChild(nomor);
            baris.appendChild(dua);

            return baris;
        }

        function medanPeserta(i, kunci, judul, wajib) {
            var bungkus = document.createElement('div');
            bungkus.className = 'daf-medan';

            var label = document.createElement('label');
            label.textContent = judul + ' ';

            var tanda = document.createElement('span');

            if (wajib) {
                tanda.className = 'daf-wajib';
                tanda.textContent = '*';
            } else {
                tanda.className = 'daf-opsional';
                tanda.textContent = '— boleh dikosongkan';
            }

            label.appendChild(tanda);

            var isian = document.createElement('input');
            isian.type = 'text';
            isian.name = 'peserta[' + i + '][' + kunci + ']';
            isian.className = 'daf-isian';

            if (kunci === 'telp') {
                isian.placeholder = '0812-3456-7890';
                isian.inputMode = 'numeric';
                isian.maxLength = 20;
                // Dirapikan sama persis dengan nomor pendaftar utamanya,
                // supaya yang terbaca di layar panitia satu bentuk saja.
                isian.addEventListener('input', function () { formatPhoneNumber(isian); });
            } else {
                isian.placeholder = 'Nama peserta';
            }

            if (wajib) {
                isian.required = true;
            }

            bungkus.appendChild(label);
            bungkus.appendChild(isian);

            return bungkus;
        }

        function susunPeserta() {
            var perlu = Math.max(0, (parseInt(kotak.value, 10) || 1) - 1);
            var ada = wadah.children.length;

            // Hanya selisihnya yang disentuh, bukan seluruh daftarnya.
            for (var i = ada; i < perlu; i++) {
                wadah.appendChild(barisPeserta(i));
            }

            for (var j = ada; j > perlu; j--) {
                wadah.removeChild(wadah.lastElementChild);
            }

            kartu.hidden = perlu === 0;
        }

        kotak.addEventListener('input', rapikan);
        kotak.addEventListener('change', rapikan);

        /*
         * susunPeserta() dipasang sebagai pendengar TERSENDIRI, bukan
         * dipanggil dari dalam rapikan().
         *
         * rapikan() sudah terlanjur terpasang sebagai pendengar di atas;
         * membungkusnya belakangan tidak mengubah fungsi yang sudah
         * terdaftar, jadi pembungkus itu tidak akan pernah terpanggil —
         * cacat yang tidak mengeluarkan galat, cuma baris peserta yang
         * tidak pernah muncul.
         */
        kotak.addEventListener('input', susunPeserta);
        kotak.addEventListener('change', susunPeserta);

        Array.prototype.forEach.call(langkah, function (t) {
            t.addEventListener('click', susunPeserta);
        });

        rapikan();
        susunPeserta();
    })();
</script>


<script>
    /*
     * ISI OTOMATIS DARI NOMOR WHATSAPP.
     *
     * Yang sudah masuk akun tidak lewat sini — borangnya sudah dirender
     * terisi oleh peladen. Ini untuk tamu yang pernah memesan.
     *
     * Aturannya dijaga supaya tidak menyebalkan:
     *   - hanya saat nomornya LENGKAP (9-15 angka), bukan tiap ketukan;
     *   - isian yang SUDAH diisi orangnya tidak pernah ditimpa;
     *   - nomor yang sama tidak dicari dua kali;
     *   - selalu bisa dibatalkan, dan pembatalan hanya mengosongkan yang
     *     memang diisikan skrip — bukan yang diketik orangnya sendiri.
     */
    (function () {
        var telp = document.getElementById('telp');
        var kabar = document.getElementById('daf-kabar-isi');

        if (! telp || ! kabar || ! window.fetch) {
            return;
        }

        var ALAMAT = @json(route('public.scopuscamp.caripendaftar'));
        var CSRF = document.querySelector('meta[name=csrf-token]');

        var medan = {
            nama: document.getElementById('nama'),
            email: document.getElementById('email'),
            affiliasi: document.getElementById('affiliasi')
        };

        var nomorTerakhir = '';
        var diisiSkrip = [];

        function angkaSaja(n) {
            return (n || '').replace(/\D+/g, '');
        }

        function bersihkanTanda() {
            Object.keys(medan).forEach(function (k) {
                if (medan[k]) {
                    medan[k].classList.remove('daf-baru-diisi');
                }
            });
        }

        function batalkan() {
            /*
             * HANYA yang diisikan skrip yang dikosongkan. Mengosongkan
             * semuanya akan membuang nama yang mungkin sudah diketik
             * orangnya sendiri sebelum nomornya diisi.
             */
            diisiSkrip.forEach(function (k) {
                if (medan[k]) {
                    medan[k].value = '';
                }
            });

            diisiSkrip = [];
            bersihkanTanda();
            kabar.hidden = true;

            if (medan.nama) {
                medan.nama.focus();
            }
        }

        function tampilkanKabar(jumlah) {
            kabar.innerHTML = '';

            var ikon = document.createElement('i');
            // fa-magic, bukan padanannya di Font Awesome 6: yang dimuat
            // aplikasi ini versi 5, dan nama ikon yang tidak ada di sana
            // tetap menyisakan kotak kosong tanpa galat apa pun.
            ikon.className = 'fas fa-magic';
            ikon.setAttribute('aria-hidden', 'true');

            var teks = document.createElement('span');
            teks.textContent = jumlah + ' isian kami isikan dari pesanan Anda sebelumnya. '
                + 'Periksa dulu, ubah kalau ada yang berbeda.';

            var batal = document.createElement('button');
            batal.type = 'button';
            batal.textContent = 'Kosongkan';
            batal.addEventListener('click', batalkan);

            kabar.appendChild(ikon);
            kabar.appendChild(teks);
            kabar.appendChild(batal);
            kabar.hidden = false;
        }

        function isikan(data) {
            diisiSkrip = [];
            bersihkanTanda();

            Object.keys(medan).forEach(function (k) {
                var e = medan[k];

                // Yang sudah ada isinya DIBIARKAN: orangnya mungkin sengaja
                // memakai nama atau email yang berbeda kali ini.
                if (! e || ! data[k] || e.value.trim() !== '') {
                    return;
                }

                e.value = data[k];
                e.classList.add('daf-baru-diisi');
                diisiSkrip.push(k);
            });

            if (diisiSkrip.length === 0) {
                kabar.hidden = true;

                return;
            }

            tampilkanKabar(diisiSkrip.length);
        }

        function cari() {
            var nomor = angkaSaja(telp.value);

            if (nomor.length < 9 || nomor.length > 15) {
                nomorTerakhir = '';

                return;
            }

            // Nomor yang sama tidak dicari ulang: orang sering keluar masuk
            // isian ini saat memeriksa kembali isiannya.
            if (nomor === nomorTerakhir) {
                return;
            }

            nomorTerakhir = nomor;

            fetch(ALAMAT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF ? CSRF.getAttribute('content') : ''
                },
                body: JSON.stringify({ telp: telp.value })
            })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (d) { if (d && d.ditemukan) { isikan(d); } })
                .catch(function () {
                    /*
                     * Didiamkan dengan sengaja. Ini kemudahan, bukan syarat —
                     * jaringan yang bermasalah tidak boleh memunculkan pesan
                     * galat di borang yang sebenarnya masih bisa diisi tangan.
                     */
                });
        }

        /*
         * Dicari saat mengetiknya BERHENTI sejenak, bukan hanya saat
         * berpindah isian.
         *
         * Nomornya kini isian pertama. Kalau menunggu change/blur, sisanya
         * baru terisi setelah orangnya sudah berpindah ke isian Nama — dan
         * isian yang terisi sendiri tepat saat kursornya ada di sana justru
         * mengagetkan. Dengan jeda, borangnya sudah lengkap sebelum ia
         * menyentuh isian berikutnya.
         *
         * 600 ms: cukup lama untuk tidak mencari di tengah pengetikan nomor,
         * cukup singkat untuk selesai sebelum orangnya berpindah.
         */
        var jadwal = null;

        telp.addEventListener('input', function () {
            clearTimeout(jadwal);
            jadwal = setTimeout(cari, 600);
        });

        // Jaring pengaman: tempel dari papan klip dan isian otomatis peramban
        // tidak selalu memicu 'input' di semua peramban.
        telp.addEventListener('change', cari);
        telp.addEventListener('blur', cari);
    })();
</script>

<script>
    function formatPhoneNumber(isian) {
        var nomor = isian.value.replace(/\D/g, '');

        if (nomor.length === 11) {
            nomor = nomor.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
        } else if (nomor.length === 12) {
            nomor = nomor.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
        } else if (nomor.length === 13) {
            nomor = nomor.replace(/(\d{5})(\d{4})(\d{4})/, '$1-$2-$3');
        }

        isian.value = nomor;
    }
</script>
@stop
