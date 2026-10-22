@extends('public.layout.header')

{{-- Judulnya dulu "Artikel | Rumah Scopus" untuk SETIAP tulisan, tanpa
     kecuali — judul tulisannya sendiri tidak pernah masuk ke <title>.

     Akibatnya dua: tautan yang dibagikan ke WhatsApp muncul sebagai
     "Artikel | Rumah Scopus" tanpa gambar dan tanpa kalimat, dan di mesin
     pencari kedua belas tulisan saling bertumpuk sebagai hasil yang
     kelihatan identik. Untuk halaman mana pun ini sayang; untuk blog —
     yang satu-satunya cara hidupnya dibagikan dan ditemukan — melumpuhkan. --}}
{{-- Semuanya memakai bentuk DUA ARGUMEN, bukan pasangan buka-tutup.

     Bentuk buka-tutupnya sempat dipakai di sini dan separuhnya mati diam-
     diam: penutupnya menempel huruf — "Scopus@stop", "article@stop" — dan
     Blade menuntut bukan-huruf sebelum tanda @. Yang menempel tidak
     dikompilasi, tercetak mentah sebagai teks biasa, dan bagiannya tidak
     pernah ditutup.

     Gejalanya paling menipu: halamannya tetap 200, uraian dan gambarnya
     benar — hanya judul dan tipe og-nya yang diam-diam kembali ke nilai
     bawaan. Yang berhasil kebetulan berakhir dengan "}}", yang bukan
     huruf. --}}
@section('title', $artikel->judul . ' | Blog Rumah Scopus')

{{-- Uraiannya dirakit dari isi tulisannya, markahnya dibuang dulu. 155
     aksara: lebih dari itu dipotong mesin pencari di tengah kalimat. --}}
@section('uraian', \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $artikel->isi))), 155))

@section('gambar', $artikel->gambar_depan ? asset('images/' . $artikel->gambar_depan) : asset('assets/img/LogoRSC.png'))

@section('ogtipe', 'article')

@if (trim((string) $artikel->kata_kunci) !== '')
    @section('katakunci', $artikel->kata_kunci)
@endif

<style>
    /* GAMBAR COVER */
    .entry-img {
        display: flex;
        justify-content: center;
        align-items: center;
        height: auto;
        /* Adjust the height as needed */
    }

    /* END */

    /* SHARE */
    .entry-footer {
        display: flex;
        justify-content: space-between;
        /* Menyusun konten secara bersebelahan */
        align-items: center;
        /* Memusatkan konten secara vertikal */
    }

    .author-social {
        display: flex;
        align-items: center;
        /* Memusatkan konten secara vertikal */
    }

    .social-links {
        display: flex;
    }

    .social-links a {
        margin-right: 5px;
    }

    .social-links a:last-child {
        margin-right: 0;
    }

    /* END */
</style>

@section('konten')
@csrf
@include('public.article.partials.gaya')

<style>
    /* Gaya KHUSUS halaman artikel. Tokennya datang dari partial di atas.

       Tiap pemilih diawali #main — bobot (1,x,x) — supaya menang atas aturan
       bawaan templat yang sedalam `.blog .entry .entry-title a` (0,3,1).
       Berkelas saja, timpaannya kalah diam-diam dan halamannya kembali ke
       rupa lamanya tanpa satu pun galat. */
    #main.blg-artikel { background: var(--kertas); padding-bottom: 10px; }

    #main.blg-artikel .blg-remah {
        padding: 104px 0 22px;
        background: linear-gradient(180deg, var(--kertas-2) 0%, var(--kertas) 100%);
    }
    #main.blg-artikel .blg-remah nav {
        display: flex; flex-wrap: wrap; align-items: center; gap: 9px;
        color: var(--tinta-2); font-size: .9rem; font-weight: 700;
    }
    #main.blg-artikel .blg-remah a {
        display: inline-flex; align-items: center; gap: 7px;
        color: var(--tinta-2); text-decoration: none;
        transition: color .18s ease;
    }
    #main.blg-artikel .blg-remah a:hover { color: var(--jingga-tua); }

    /* ====================================================== badan tulisan */
    #main.blg-artikel .entry {
        padding: 0 0 30px;
        background: transparent; border: none; box-shadow: none; margin-bottom: 0;
    }
    #main.blg-artikel .entry-img {
        margin: 0 0 26px; padding: 0;
        border: 2px solid var(--tinta); border-radius: 22px;
        box-shadow: 8px 8px 0 var(--jingga); overflow: hidden;
    }
    #main.blg-artikel .entry-img img { width: 100%; height: auto; display: block; margin: 0 !important; }

    #main.blg-artikel .entry-title {
        margin: 0 0 16px; padding: 0;
        font-family: 'Fraunces', Georgia, serif;
        font-size: clamp(1.6rem, 3.4vw, 2.4rem); font-weight: 800;
        line-height: 1.17; letter-spacing: -.01em;
    }
    /* Judulnya dibungkus <a href=""> di markah lama. Tautan tanpa tujuan tidak
       boleh terlihat seperti tautan. */
    #main.blg-artikel .entry-title a { color: var(--tinta); text-decoration: none; cursor: default; }

    #main.blg-artikel .entry-meta { padding: 0; margin-bottom: 24px; }
    #main.blg-artikel .entry-meta ul {
        display: flex; flex-wrap: wrap; gap: 8px 20px;
        padding: 0 0 20px; margin: 0; list-style: none;
        border-bottom: 2px dashed rgba(22, 35, 63, .16);
    }
    #main.blg-artikel .entry-meta li { padding: 0; color: var(--tinta-2); font-size: .89rem; font-weight: 600; }
    #main.blg-artikel .entry-meta i { color: var(--jingga); margin-right: 6px; }
    #main.blg-artikel .entry-meta a { color: var(--tinta-2); text-decoration: none; }
    #main.blg-artikel .entry-meta img { border: 2px solid var(--tinta); }

    /* Isinya datang dari penyunting admin: markahnya tidak bisa diatur dari
       sini, jadi yang diatur bentuk bakunya. */
    #main.blg-artikel .entry-content {
        color: var(--tinta); font-size: 1.04rem; line-height: 1.78;
    }
    #main.blg-artikel .entry-content p { margin-bottom: 1.15rem; color: var(--tinta); }
    #main.blg-artikel .entry-content h1,
    #main.blg-artikel .entry-content h2,
    #main.blg-artikel .entry-content h3,
    #main.blg-artikel .entry-content h4 {
        font-family: 'Fraunces', Georgia, serif; font-weight: 800;
        color: var(--tinta); margin: 2rem 0 .8rem; line-height: 1.26;
    }
    #main.blg-artikel .entry-content h2 { font-size: 1.5rem; }
    #main.blg-artikel .entry-content h3 { font-size: 1.25rem; }
    #main.blg-artikel .entry-content a { color: var(--jingga-tua); font-weight: 700; }
    #main.blg-artikel .entry-content ul,
    #main.blg-artikel .entry-content ol { padding-left: 1.3rem; margin-bottom: 1.15rem; }
    #main.blg-artikel .entry-content li { margin-bottom: .45rem; }
    #main.blg-artikel .entry-content img {
        max-width: 100%; height: auto; display: block; margin: 1.4rem auto;
        border: 2px solid var(--tinta); border-radius: 16px;
    }
    #main.blg-artikel .entry-content blockquote {
        margin: 1.6rem 0; padding: 18px 22px;
        background: var(--kertas-2);
        border: none; border-left: 5px solid var(--jingga); border-radius: 0 14px 14px 0;
        font-size: 1.02rem;
    }
    #main.blg-artikel .entry-content blockquote p { margin-bottom: 0; }

    #main.blg-artikel .entry-footer {
        padding-top: 20px; margin-top: 26px;
        border-top: 2px dashed rgba(22, 35, 63, .16);
    }

    /* ======================================================= bilah samping */
    #main.blg-artikel .sidebar {
        padding: 26px 24px;
        background: var(--kertas-2);
        border: 2px solid var(--tinta); border-radius: 22px;
        box-shadow: 7px 7px 0 rgba(22, 35, 63, .14);
    }
    #main.blg-artikel .sidebar-title {
        position: relative;
        margin: 26px 0 15px; padding: 0 0 11px;
        font-family: 'Fraunces', Georgia, serif;
        font-size: 1.1rem; font-weight: 800; color: var(--tinta);
        border: none;
    }
    #main.blg-artikel .sidebar-title:first-child { margin-top: 0; }
    #main.blg-artikel .sidebar-title::after {
        content: ''; position: absolute; left: 0; bottom: 0;
        width: 34px; height: 3px; border-radius: 3px; background: var(--jingga);
    }
    #main.blg-artikel .sidebar-item { margin-bottom: 4px; }

    #main.blg-artikel .search-form form {
        display: flex; align-items: center; gap: 8px;
        padding: 5px 5px 5px 14px;
        background: var(--kertas);
        border: 2px solid var(--tinta); border-radius: 999px;
    }
    #main.blg-artikel .search-form input {
        flex: 1 1 auto; min-width: 0;
        padding: 8px 0; border: none; background: transparent;
        color: var(--tinta); font-size: .93rem; font-weight: 600;
    }
    #main.blg-artikel .search-form input:focus { outline: none; }
    #main.blg-artikel .search-form button {
        position: static; flex: 0 0 auto;
        width: 36px; height: 36px; padding: 0;
        display: inline-flex; align-items: center; justify-content: center;
        background: var(--jingga-kuat); color: #fff;
        border: 2px solid var(--tinta); border-radius: 50%;
        font-size: .9rem;
    }
    #main.blg-artikel .search-form button:hover { background: var(--tinta); }

    #main.blg-artikel .categories ul { padding: 0; margin: 0; list-style: none; }
    #main.blg-artikel .categories li { padding: 7px 0; border: none; }
    #main.blg-artikel .categories a { color: var(--tinta); font-weight: 700; font-size: .95rem; }
    #main.blg-artikel .categories a:hover { color: var(--jingga-tua); }
    #main.blg-artikel .categories span { color: var(--tinta-2); font-weight: 600; }

    /* KISI, bukan flex baris.

       Markahnya tiga saudara sejajar: gambar, tautan judul, lalu <time>.
       Sebagai flex baris ketiganya berjajar dan judulnya terjepit sampai
       satu kata per baris. Di kisi, gambarnya membentang dua baris
       sementara judul dan tanggal menumpuk di kolom sebelahnya. */
    #main.blg-artikel .recent-posts .post-item {
        display: grid; grid-template-columns: 62px minmax(0, 1fr);
        gap: 3px 12px; align-items: start;
        padding: 11px 0; border-bottom: 1px dashed rgba(22, 35, 63, .18);
    }
    #main.blg-artikel .recent-posts .post-item img { grid-row: span 2; }
    #main.blg-artikel .recent-posts .post-item > a { grid-column: 2; }
    #main.blg-artikel .recent-posts .post-item > time { grid-column: 2; }
    #main.blg-artikel .recent-posts .post-item:last-child { border-bottom: none; }
    #main.blg-artikel .recent-posts img {
        flex: 0 0 auto; width: 62px; height: 46px; object-fit: cover;
        border: 2px solid var(--tinta); border-radius: 10px; float: none; margin: 0;
    }
    #main.blg-artikel .recent-posts h4 {
        margin: 0 0 4px;
        font-family: 'Fraunces', Georgia, serif;
        font-size: .93rem; font-weight: 800; line-height: 1.32;
    }
    #main.blg-artikel .recent-posts a { color: var(--tinta); }
    #main.blg-artikel .recent-posts a:hover { color: var(--jingga-tua); }
    #main.blg-artikel .recent-posts time { color: var(--tinta-2); font-size: .79rem; font-weight: 600; }

    #main.blg-artikel .tags ul { display: flex; flex-wrap: wrap; gap: 8px; padding: 0; margin: 0; list-style: none; }
    #main.blg-artikel .tags li { padding: 0; }
    #main.blg-artikel .tags a {
        display: inline-block; padding: 5px 13px;
        background: var(--kertas); color: var(--tinta);
        border: 2px solid var(--tinta); border-radius: 999px;
        font-size: .82rem; font-weight: 700;
    }
    #main.blg-artikel .tags a:hover { background: var(--jingga); color: #fff; }

    /* ============================================================ komentar

       Kelasnya .blog-comments dan .reply-form — BUKAN .comments dan
       .comment-form. Versi pertama gaya ini menebak nama yang lazim dipakai
       templat sejenis, dan keduanya tidak ada di markah ini: borang
       komentarnya terlewat sama sekali, tanpa galat, dan tidak kelihatan
       karena artikel yang dipakai memeriksa kebetulan belum punya komentar. */
    #main.blg-artikel .blog-comments { margin-top: 10px; }
    #main.blg-artikel .comments-count,
    #main.blg-artikel .blog-comments h4 {
        font-family: 'Fraunces', Georgia, serif; font-weight: 800; color: var(--tinta);
    }
    #main.blg-artikel .comment {
        padding: 18px 20px; margin-bottom: 14px;
        background: var(--kertas-2);
        border: 2px solid var(--tinta); border-radius: 18px;
    }
    #main.blg-artikel .comment-reply { margin-left: 32px; background: var(--kertas); }
    #main.blg-artikel .comment h5 { font-weight: 800; font-size: 1rem; }
    #main.blg-artikel .comment .comment-img img { border: 2px solid var(--tinta); }

    #main.blg-artikel .reply-form {
        padding: 26px 24px; margin-top: 10px;
        background: var(--kertas-2);
        border: 2px solid var(--tinta); border-radius: 22px;
        box-shadow: 7px 7px 0 rgba(22, 35, 63, .14);
    }
    #main.blg-artikel .reply-form input,
    #main.blg-artikel .reply-form textarea {
        background: var(--kertas); color: var(--tinta);
        border: 2px solid var(--tinta); border-radius: 13px;
        box-shadow: none; padding: 11px 14px;
    }
    #main.blg-artikel .reply-form input:focus,
    #main.blg-artikel .reply-form textarea:focus {
        outline: none; border-color: var(--jingga); box-shadow: 0 0 0 3px rgba(231, 109, 32, .18);
    }
    #main.blg-artikel .reply-form .btn-primary,
    #main.blg-artikel .reply-form button[type="submit"] {
        padding: 12px 24px;
        background: var(--jingga-kuat); color: #fff;
        border: 2px solid var(--tinta); border-radius: 999px;
        box-shadow: 4px 4px 0 var(--tinta);
        font-weight: 800;
    }
    #main.blg-artikel .reply-form .btn-primary:hover,
    #main.blg-artikel .reply-form button[type="submit"]:hover { background: var(--tinta); }

    @media (max-width: 991.98px) {
        #main.blg-artikel .sidebar { margin-top: 34px; }
    }
    @media (max-width: 767.98px) {
        #main.blg-artikel .blg-remah { padding: 92px 0 18px; }
        #main.blg-artikel .comment-reply { margin-left: 14px; }
    }
</style>

<main id="main" class="blg blg-artikel">

    {{-- Pita remahnya dulu bergradien merah #ff3131 -> #ff914d dengan judul
         "Blog" — satu kata yang sama untuk kedua belas tulisan, dan warna
         yang sudah ditinggalkan seluruh situs. Sekarang remahnya menyebut
         kategori tulisannya dan mengantar balik ke sana. --}}
    <section class="blg-remah">
        <div class="container">
            <nav aria-label="Remah jejak">
                <a href="{{ url('/blog') }}"><i class="bi bi-arrow-left"></i> Semua tulisan</a>
                @foreach($categories_artikel as $category)
                    @if($category->id == $artikel->categories_artikel_id)
                        <span aria-hidden="true">/</span>
                        <a href="{{ route('blog.topic.kategori', ['categories_artikel_id' => $category->id]) }}">{{ $category->kategori }}</a>
                    @endif
                @endforeach
            </nav>
        </div>
    </section>

    <!--================== BLOG CONTENT ==================-->
    <section id="blog" class="blog">
        <div class="container" data-aos="fade-up">

            <div class="row">
                <div class="col-lg-8 entries">

                    <!--================== ARTIKEL ==================-->
                    <article class="entry entry-single">
                        <div class="entry-img text-center"> <!-- Add 'text-center' class to center the image -->
                            <img src="{{ asset('images/' . $artikel->gambar_cover) }}" alt="" class="img-fluid mt-3">
                        </div>

                        <h2 class="entry-title">
                            <a href="">{{ $artikel->judul }}</a>
                        </h2>
                        <div class="entry-meta">
                            <ul>

                                @php
                                $user = DB::table('users')->where('id', $artikel->user_id)->first();
                                @endphp

                                <?php $jumlah_komentar = 0; ?>
                                @foreach($artikel_komentar as $komentar)
                                @if($komentar->artikel_id == $artikel->id)
                                <?php $jumlah_komentar++; ?>
                                @endif
                                @endforeach

                                <li class="d-flex align-items-center">
                                    @if ($user->gambar == null)
                                    <img src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="rounded-circle float-left" style="width: 25px; height: 25px;" alt="">
                                    @else
                                    <img src="{{ \App\Support\FotoProfil::url($user->gambar) }}" class="rounded-circle float-left" style="width: 25px; height: 25px;" alt="">
                                    @endif
                                    <a href="" style="margin-left: 10px;">{{ $user->full_name }}</a>
                                </li>
                                {{-- translatedFormat + locale('id'), bukan format().

                                     APP_LOCALE=en di .env, jadi format('l, j F Y')
                                     memulangkan "Thursday, 6 March 2025" di halaman
                                     yang seluruh kalimatnya Bahasa Indonesia. --}}
                                <li class="d-flex align-items-center"><i class="bi bi-clock"></i>
                                    <time datetime="{{ \Carbon\Carbon::parse($artikel->created_at)->toDateString() }}">
                                        {{ \Carbon\Carbon::parse($artikel->created_at)->locale('id')->translatedFormat('l, j F Y') }}
                                    </time>
                                </li>
                                <li class="d-flex align-items-center"><i class="bi bi-chat-dots"></i>
                                    {{ $jumlah_komentar }} komentar
                                </li>
                                @if ($artikel->dilihat > 0)
                                    <li class="d-flex align-items-center"><i class="bi bi-eye"></i>
                                        dibaca {{ number_format($artikel->dilihat, 0, ',', '.') }} kali
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class="entry-content">
                            <div class="article-container">
                                {!! $artikel->isi !!}
                            </div>
                            <style>
                                .article-container img {
                                    max-width: 100%;
                                    /* Set maximum width to 100% */
                                    height: auto;
                                    /* Maintain aspect ratio */
                                    display: block;
                                    /* Ensure proper rendering */
                                    margin: 0 auto;
                                    /* Center the images */
                                }
                            </style>
                        </div>

                        <div class="entry-footer">
                            <div class="author-social">
                                <p style="margin-right: 10px;">Share</p>
                                <div class="social-links" style="margin-top: -10px;">
                                    <a href="https://twitter.com/share?url={{ urlencode(route('blog.topic.blog-single', ['id' => $artikel->id])) }}" target="_blank"><i class="bi bi-twitter" style="color: gray;"></i></a>
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('blog.topic.blog-single', ['id' => $artikel->id])) }}" target="_blank"><i class="bi bi-facebook" style="color: gray;"></i></a>
                                    <a href="https://www.instagram.com/" target="_blank"><i class="biu bi-instagram" style="color: gray;"></i></a>
                                </div>
                            </div>
                        </div>

                    </article>
                    <!--================== END ==================-->

                    <!--================== KOMENTAR ==================-->
                    <div class="blog-comments">
                        <h4 class="comments-count">{{ $jumlah_komentar }} komentar</h4>

                        @foreach($artikel_komentar as $komentar)
                        @if($komentar->artikel_id == $artikel->id && $komentar->reply == null)

                        <!--================== TAMPILAN KOMENTAR AWAL ==================-->
                        <div id="comment-{{ $komentar->id }}" class="comment">
                            <div class="d-flex">
                                <div class="comment-img thumbnail-circle" style="width: 50px; height: 50px;">
                                    @php
                                    // Mengambil user berdasarkan email
                                    $user = App\User::where('email', $komentar->email)->first();
                                    @endphp

                                    @if($user && $user->gambar)
                                    <img src="{{ asset('images/' . $user->gambar) }}" class="rounded-circle" alt="">
                                    @else
                                    <img src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="rounded-circle" alt="">
                                    @endif
                                </div>

                                <div>
                                    <h5><a href="">{{ $komentar->nama }}</a> <a href="#" class="reply" onclick="showReplyForm(event, {{ $komentar->id }})"><i class="bi bi-reply-fill"></i> Reply</a></h5>
                                    <time datetime="{{ \Carbon\Carbon::parse($komentar->created_at)->toIso8601String() }}">
                                        {{ \Carbon\Carbon::parse($komentar->created_at)->format('l, j F Y H:i') }}
                                    </time>
                                    <p>
                                        {{ $komentar->komentar }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <!--================== END ==================-->

                        <!--================== FORM KOMENTAR AWAL UNTUK REPLY ==================-->
                        <div id="reply-form-{{ $komentar->id }}" class="reply-form" style="display: none;">
                            <h4>Masukkan Balasan</h4>
                            <p>Alamat email Anda tidak akan dipublikasikan. Bidang yang wajib diisi ditandai <span style="color: red;">*</span></p>
                            <form action="{{ route('blog.store.komentar') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label style="font-size: 15px;">Masukkan Nama Anda <span style="color: red;">*</span></label>
                                        <input name="nama" type="text" class="form-control" placeholder="Masukkan Nama Anda" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z ]/i.test(event.key)" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label style="font-size: 15px;">Masukkan Email Anda <span style="color: red;">*</span></label>
                                        <input name="email" type="text" class="form-control" placeholder="Masukkan Email Anda" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group">
                                        <label style="font-size: 15px;">Masukkan Website Anda</label>
                                        <input name="link_website" type="text" class="form-control" placeholder="Masukkan Website Anda">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group">
                                        <label style="font-size: 15px;">Masukkan Komentar Anda <span style="color: red;">*</span></label>
                                        <textarea name="komentar" class="form-control" placeholder="Masukkan Komentar" required></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <input name="artikel_id" value="{{ $artikel->id }}" type="text" class="form-control" hidden>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <input name="categories_artikel_id" value="{{ $artikel->categories_artikel_id }}" type="text" class="form-control" hidden>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <input name="user_id" value="{{ $artikel->user_id }}" type="text" class="form-control" hidden>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <input name="reply" value="{{ $komentar->id }}" type="text" class="form-control" hidden>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Kirim</button>
                            </form>
                        </div>
                        <!--================== END ==================-->

                        @foreach($artikel_komentar as $reply)
                        @if($reply->reply == $komentar->id)
                        <!--================== TAMPILAN REPLY KOMENTAR AWAL ==================-->
                        <div id="comment-reply-{{ $reply->id }}" class="comment comment-reply">
                            <div class="d-flex">
                                <div class="comment-img thumbnail-circle" style="width: 50px; height: 50px;">
                                    @php
                                    // Mengambil user berdasarkan email
                                    $user = App\User::where('email', $reply->email)->first();
                                    @endphp

                                    @if($user && $user->gambar)
                                    <img src="{{ asset('images/' . $user->gambar) }}" class="rounded-circle" alt="">
                                    @else
                                    <img src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="rounded-circle" alt="">
                                    @endif
                                </div>

                                <div>
                                    <h5><a href="">{{ $reply->nama }}</a>
                                        @php
                                        $parentReply = $artikel_komentar->where('id', $reply->reply)->first(); // Mengambil entitas dengan ID yang sesuai
                                        @endphp
                                        @if($parentReply)
                                        <i class="fas fa-play" style="font-size: 0.75em;"></i>
                                        {{ $parentReply->nama }}
                                        @endif
                                        <a href="#" class="reply" onclick="showReplyForm(event, {{ $reply->id }})"><i class="bi bi-reply-fill"></i> Reply</a>
                                    </h5>
                                    <time datetime="{{ $reply->created_at }}">{{ \Carbon\Carbon::parse($reply->created_at)->format('l, j F Y H:i') }}</time>
                                    <p>
                                        {{ $reply->komentar }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <!--================== END ==================-->

                        <!--================== FORM KOMENTAR YANG DI-REPLY ==================-->
                        <div id="reply-form-{{ $reply->id }}" class="reply-form" style="display: none;">
                            <h4>Masukkan Balasan</h4>
                            <p>Alamat email Anda tidak akan dipublikasikan. Bidang yang wajib diisi ditandai <span style="color: red;">*</span></p>
                            <form action="{{ route('blog.store.komentar') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label style="font-size: 15px;">Masukkan Nama Anda <span style="color: red;">*</span></label>
                                        <input name="nama" type="text" class="form-control" placeholder="Masukkan Nama Anda" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z ]/i.test(event.key)" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label style="font-size: 15px;">Masukkan Email Anda <span style="color: red;">*</span></label>
                                        <input name="email" type="text" class="form-control" placeholder="Masukkan Email Anda" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group">
                                        <label style="font-size: 15px;">Masukkan Website Anda</label>
                                        <input name="link_website" type="text" class="form-control" placeholder="Masukkan Website Anda">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group">
                                        <label style="font-size: 15px;">Masukkan Komentar Anda <span style="color: red;">*</span></label>
                                        <textarea name="komentar" class="form-control" placeholder="Masukkan Komentar" required></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <input name="artikel_id" value="{{ $artikel->id }}" type="text" class="form-control" hidden>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <input name="categories_artikel_id" value="{{ $artikel->categories_artikel_id }}" type="text" class="form-control" hidden>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <input name="user_id" value="{{ $artikel->user_id }}" type="text" class="form-control" hidden>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <input name="reply" value="{{ $reply->id }}" type="text" class="form-control" hidden>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Kirim</button>
                            </form>
                        </div>
                        <!--================== END ==================-->

                        @foreach($artikel_komentar as $nestedReply)
                        @if($nestedReply->reply == $reply->id)
                        <!--================== TAMPILAN REPLY KOMENTAR KEMBALI ==================-->
                        <div id="comment-reply-{{ $nestedReply->id }}" class="comment comment-reply">
                            <div class="d-flex">
                                <div class="comment-img thumbnail-circle" style="width: 50px; height: 50px;">
                                    @php
                                    // Mengambil user berdasarkan email
                                    $user = App\User::where('email', $nestedReply->email)->first();
                                    @endphp

                                    @if($user && $user->gambar)
                                    <img src="{{ asset('images/' . $user->gambar) }}" class="rounded-circle" alt="">
                                    @else
                                    <img src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="rounded-circle" alt="">
                                    @endif
                                </div>

                                <div>
                                    <h5><a href="">{{ $nestedReply->nama }}</a>
                                        @php
                                        $parentReply = $artikel_komentar->where('id', $nestedReply->reply)->first(); // Mengambil entitas dengan ID yang sesuai
                                        @endphp
                                        @if($parentReply)
                                        <i class="fas fa-play" style="font-size: 0.75em;"></i>
                                        {{ $parentReply->nama }}
                                        @endif
                                    </h5>
                                    <time datetime="{{ $nestedReply->created_at }}">{{ \Carbon\Carbon::parse($nestedReply->created_at)->format('l, j F Y H:i') }}</time>
                                    <p>
                                        {{ $nestedReply->komentar }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <!--================== END ==================-->

                        <!--================== FORM KOMENTAR YANG DI-REPLY KEMBALI ==================-->
                        <div id="reply-form-{{ $nestedReply->id }}" class="reply-form" style="display: none;">
                            <h4>Masukkan Balasan</h4>
                            <p>Alamat email Anda tidak akan dipublikasikan. Bidang yang wajib diisi ditandai <span style="color: red;">*</span></p>
                            <form action="{{ route('blog.store.komentar') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label style="font-size: 15px;">Masukkan Nama Anda <span style="color: red;">*</span></label>
                                        <input name="nama" type="text" class="form-control" placeholder="Masukkan Nama Anda" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z ]/i.test(event.key)" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label style="font-size: 15px;">Masukkan Email Anda <span style="color: red;">*</span></label>
                                        <input name="email" type="text" class="form-control" placeholder="Masukkan Email Anda" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group">
                                        <label style="font-size: 15px;">Masukkan Website Anda</label>
                                        <input name="link_website" type="text" class="form-control" placeholder="Masukkan Website Anda">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group">
                                        <label style="font-size: 15px;">Masukkan Komentar Anda <span style="color: red;">*</span></label>
                                        <textarea name="komentar" class="form-control" placeholder="Masukkan Komentar" required></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <input name="artikel_id" value="{{ $artikel->id }}" type="text" class="form-control" hidden>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <input name="categories_artikel_id" value="{{ $artikel->categories_artikel_id }}" type="text" class="form-control" hidden>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <input name="user_id" value="{{ $artikel->user_id }}" type="text" class="form-control" hidden>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <input name="reply" value="{{ $nestedReply->id }}" type="text" class="form-control" hidden>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Kirim</button>
                            </form>
                        </div>
                        <!--================== END ==================-->
                        @endif
                        @endforeach

                        @endif
                        @endforeach
                        @endif
                        @endforeach

                        <!--================== FORM KOMENTAR ==================-->
                        <div class="reply-form">
                            <h4>Masukkan Komentar</h4>
                            <p>Alamat email Anda tidak akan dipublikasikan. Bidang yang wajib diisi ditandai <span style="color: red;">*</span></p>
                            <form action="{{ route('blog.store.komentar') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label style="font-size: 15px;">Masukkan Nama Anda <span style="color: red;">*</span></label>
                                        <input name="nama" type="text" class="form-control" placeholder="Masukkan Nama Anda" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z ]/i.test(event.key)" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label style="font-size: 15px;">Masukkan Email Anda <span style="color: red;">*</span></label>
                                        <input name="email" type="text" class="form-control" placeholder="Masukkan Email Anda" maxlength="30" minlength="5" onkeypress="return/[a-zA-Z0-9@.]/i.test(event.key)" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group">
                                        <label style="font-size: 15px;">Masukkan Website Anda</label>
                                        <input name="link_website" type="text" class="form-control" placeholder="Masukkan Website Anda">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group">
                                        <label style="font-size: 15px;">Masukkan Komentar Anda <span style="color: red;">*</span></label>
                                        <textarea name="komentar" class="form-control" placeholder="Masukkan Komentar" required></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <input name="artikel_id" value="{{ $artikel->id }}" type="text" class="form-control" hidden>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <input name="categories_artikel_id" value="{{ $artikel->categories_artikel_id }}" type="text" class="form-control" hidden>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <input name="user_id" value="{{ $artikel->user_id }}" type="text" class="form-control" hidden>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Kirim</button>
                            </form>
                        </div>
                        <!--================== END ==================-->

                    </div>
                </div><!-- End blog entries list -->

                <div class="col-lg-4">
                    <div class="sidebar">

                        {{-- Borang ini dulu action="" dengan <input type="text">
                             TANPA atribut name. Ditekan, ia memuat ulang halaman
                             yang sama tanpa membawa apa pun — tidak pernah bisa
                             mencari sejak dipasang, dan tidak ada galat yang
                             memberi tahu. Yang memakainya menyimpulkan tulisan
                             yang dicarinya memang tidak ada. --}}
                        <h3 class="sidebar-title">Cari tulisan</h3>
                        <div class="sidebar-item search-form">
                            <form action="{{ url('/blog') }}" method="GET" role="search">
                                <input type="search" name="cari" aria-label="Cari tulisan"
                                    placeholder="Kata kunci…">
                                <button type="submit" aria-label="Cari"><i class="bi bi-search"></i></button>
                            </form>
                        </div>

                        <!--================== KATEGORI TERBARU ==================-->
                        <h3 class="sidebar-title">Kategori</h3>
                        <div class="sidebar-item categories">
                            <ul>
                                @foreach($categories_artikel->take(6) as $category)
                                <li><a href="{{ route('blog.topic.kategori', ['categories_artikel_id' => $category->id]) }}">{{ $category->kategori }} <span>({{ strtoupper($category->jumlah_artikel) }})</span></a></li>
                                @endforeach
                            </ul>
                        </div>
                        <!--================== END ==================-->

                        <!--================== POST TERBARU ==================-->
                        <h3 class="sidebar-title">Tulisan terbaru</h3>
                        <div class="sidebar-item recent-posts">
                            @foreach ($datas->take(5) as $article)
                            @if($article->status == 'publish')
                            <div class="post-item clearfix">
                                <img src="{{ asset('images/' . $article->gambar_depan) }}" alt="">
                                <a href="{{ route('blog.topic.blog-single', ['id' => $article->id]) }}">
                                    <h4>{{ $article->judul }}</h4>
                                </a>
                                {{-- Carbon, bukan strftime().

                                     strftime() sudah usang sejak PHP 8.1 dan DIBUANG
                                     di PHP 9 — peladen ini sudah 8.4. Ia juga
                                     mengabaikan locale Carbon, jadi keluarannya
                                     "Friday, 11 July 2025" di halaman yang seluruh
                                     kalimatnya Bahasa Indonesia. --}}
                                <time datetime="{{ \Carbon\Carbon::parse($article->created_at)->toDateString() }}">
                                    {{ \Carbon\Carbon::parse($article->created_at)->locale('id')->translatedFormat('j M Y') }}
                                </time>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        <!--================== END ==================-->

                        <!--================== MENAMPILKAN KATA KUNCI ==================-->
                        <div class="sidebar-tags-container" style="margin-top: 30px;">
                            <style>
                                .sidebar-tags-wrapper {
                                    background: #ffffff;
                                }

                                .tags-title {
                                    font-size: 16px;
                                    font-weight: 700;
                                    color: #1e293b;
                                    margin-bottom: 15px;
                                    display: flex;
                                    align-items: center;
                                    gap: 8px;
                                }

                                .tags-list {
                                    list-style: none;
                                    padding: 0;
                                    margin: 0;
                                    display: flex;
                                    flex-wrap: wrap;
                                    gap: 6px;
                                }

                                .tag-badge {
                                    display: inline-block;
                                    padding: 5px 12px;
                                    background: #f8fafc;
                                    /* Ultra soft gray */
                                    color: #94a3b8;
                                    /* Disabled look */
                                    border-radius: 6px;
                                    font-size: 12px;
                                    font-weight: 600;
                                    border: 1px solid #f1f5f9;
                                    cursor: not-allowed;
                                    /* Menunjukkan status disabled */
                                    user-select: none;
                                    transition: all 0.2s ease;
                                }

                                .tag-badge i {
                                    font-size: 10px;
                                    opacity: 0.6;
                                }
                            </style>

                            <div class="sidebar-tags-wrapper">
                                <h3 class="tags-title">
                                    <span style="width: 3px; height: 15px; background: var(--primary-gradient, #ff3131); display: inline-block; border-radius: 10px;"></span>
                                    Tags
                                </h3>

                                <div class="sidebar-item tags">
                                    <ul class="tags-list">
                                        @php
                                        $keywords = $artikel->kata_kunci ? explode(',', $artikel->kata_kunci) : [];
                                        @endphp

                                        @forelse ($keywords as $keyword)
                                        @if(trim($keyword) !== '')
                                        <li>
                                            <span class="tag-badge">
                                                <i class="bi bi-tag-fill me-1"></i>{{ trim($keyword) }}
                                            </span>
                                        </li>
                                        @endif
                                        @empty
                                        <li style="color: #cbd5e1; font-size: 12px; font-style: italic;">No tags available</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!--================== END ==================-->
                    </div>
                </div>
            </div>
        </div>
</main>

<script>
    // komentar
    function showReplyForm(event, commentId) {
        event.preventDefault();
        var replyForm = document.getElementById('reply-form-' + commentId);
        if (replyForm.style.display === "none") {
            replyForm.style.display = "block";
            window.location.hash = '#reply-form-' + commentId;
        } else {
            replyForm.style.display = "none";
        }
    }
    // end
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var toastMixin = Swal.mixin({
        toast: true,
        icon: 'success',
        title: 'General Title',
        animation: false,
        position: 'top-right',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
    @if(session('success'))
    toastMixin.fire({
        animation: true,
        title: "{{ session('success') }}",
    });
    @endif

    @if(session('error'))
    toastMixin.fire({
        title: "{{ session('error') }}",
        icon: 'error'
    });
    @endif
</script>

@stop