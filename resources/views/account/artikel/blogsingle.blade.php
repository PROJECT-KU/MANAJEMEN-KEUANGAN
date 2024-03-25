@extends('account.artikel.layout.header')

@section('title')
Artikel | MIS
@stop

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
<main id="main">

    <!--================== BREADCRUMBS ==================-->
    <section class="breadcrumbs">
        <div class="container">

            <ol>
                <li><a href="{{ url('/blog') }}">Home</a></li>
                <li><a href="">Blog</a></li>
                @foreach($categories_artikel as $category)
                @if($category->id == $artikel->categories_artikel_id)
                <li><a href="{{ route('blog.topic.kategori', ['categories_artikel_id' => $category->id, 'token' => $category->token]) }}">{{ $category->kategori }}</a></li>
                @endif
                @endforeach
            </ol>

            <h2>Blog</h2>

        </div>
    </section>
    <!--================== END ==================-->

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
                            <a href="blog-single.html">{{ $artikel->judul }}</a>
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
                                    <i class="bi bi-person"></i>
                                    <a href="blog-single.html">{{ $user->full_name }}</a>
                                </li>
                                <li class="d-flex align-items-center"><i class="bi bi-clock"></i> <a href="blog-single.html"><time datetime="2020-01-01"> {{ \Carbon\Carbon::parse($artikel->created_at)->format('l, j F Y') }}</time></a></li>
                                <li class="d-flex align-items-center"><i class="bi bi-chat-dots"></i> <a href="blog-single.html">{{ $jumlah_komentar }} Comments</a></li>
                            </ul>
                        </div>
                        <div class="entry-content">
                            <p>
                                {!! strip_tags($artikel->isi) !!}
                            </p>

                            <blockquote>
                                <p>
                                    Et vero doloremque tempore voluptatem ratione vel aut. Deleniti sunt animi aut. Aut eos aliquam doloribus minus autem quos.
                                </p>
                            </blockquote>
                        </div>

                        <div class="entry-footer">
                            <div class="author-social">
                                <p style="margin-right: 10px;">Share</p>
                                <div class="social-links" style="margin-top: -10px;">
                                    <a href="https://twitter.com/share?url={{ urlencode(route('blog.topic.blog-single', ['id' => $artikel->id, 'token' => $artikel->token])) }}" target="_blank"><i class="bi bi-twitter" style="color: gray;"></i></a>
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('blog.topic.blog-single', ['id' => $artikel->id, 'token' => $artikel->token])) }}" target="_blank"><i class="bi bi-facebook" style="color: gray;"></i></a>
                                    <a href="https://www.instagram.com/" target="_blank"><i class="biu bi-instagram" style="color: gray;"></i></a>
                                </div>
                            </div>
                        </div>

                    </article>
                    <!--================== END ==================-->

                    <div class="blog-author d-flex align-items-center">
                        <img src="assets/img/blog/blog-author.jpg" class="rounded-circle float-left" alt="">
                        <div>
                            <h4>Jane Smith</h4>
                            <div class="social-links">
                                <a href="https://twitters.com/#"><i class="bi bi-twitter"></i></a>
                                <a href="https://facebook.com/#"><i class="bi bi-facebook"></i></a>
                                <a href="https://instagram.com/#"><i class="biu bi-instagram"></i></a>
                            </div>
                            <p>
                                Itaque quidem optio quia voluptatibus dolorem dolor. Modi eum sed possimus accusantium. Quas repellat voluptatem officia numquam sint aspernatur voluptas. Esse et accusantium ut unde voluptas.
                            </p>
                        </div>
                    </div>

                    <!--================== KOMENTAR ==================-->
                    <div class="blog-comments">
                        <h4 class="comments-count">{{ $jumlah_komentar }} Comments</h4>

                        @foreach($artikel_komentar as $komentar)
                        @if($komentar->artikel_id == $artikel->id && $komentar->reply == null)

                        <!--================== TAMPILAN KOMENTAR AWAL ==================-->
                        <div id="comment-{{ $komentar->id }}" class="comment">
                            <div class="d-flex">
                                <div class="comment-img"><img src="assets/img/blog/comments-1.jpg" alt=""></div>
                                <div>
                                    <h5><a href="">{{ $komentar->nama }}</a> <a href="#" class="reply" onclick="showReplyForm(event, {{ $komentar->id }})"><i class="bi bi-reply-fill"></i> Reply</a></h5>
                                    <time datetime="2020-01-01">{{ \Carbon\Carbon::parse($komentar->created_at)->format('l, j F Y') }}</time>
                                    <p>
                                        {{ $komentar->komentar }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <!--================== END ==================-->

                        <!--================== FORM KOMENTAR AWAL UNTUK REPLY ==================-->
                        <div id="reply-form-{{ $komentar->id }}" class="reply-form" style="display: none;">
                            <h4>Leave a Reply</h4>
                            <p>Your email address will not be published. Required fields are marked * </p>
                            <form action="{{ route('blog.store.komentar') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <input name="nama" type="text" class="form-control" placeholder="Masukkan Nama Anda" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <input name="email" type="text" class="form-control" placeholder="Masukkan Email Anda" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group">
                                        <input name="link_website" type="text" class="form-control" placeholder="Masukkan Website Anda">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group">
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
                                        <input name="reply" value="{{ $komentar->id }}" type="text" class="form-control">
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
                                <div class="comment-img"><img src="assets/img/blog/comments-3.jpg" alt=""></div>
                                <div>
                                    <h5><a href="">{{ $reply->nama }}</a> <a href="#" class="reply" onclick="showReplyForm(event, {{ $reply->id }})"><i class="bi bi-reply-fill"></i> Reply</a></h5>
                                    <time datetime="{{ $reply->created_at }}">{{ \Carbon\Carbon::parse($reply->created_at)->format('l, j F Y') }}</time>
                                    <p>
                                        {{ $reply->komentar }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <!--================== END ==================-->

                        <!--================== FORM KOMENTAR YANG DI-REPLY ==================-->
                        <div id="reply-form-{{ $reply->id }}" class="reply-form" style="display: none;">
                            <h4>Leave a Reply</h4>
                            <p>Your email address will not be published. Required fields are marked * </p>
                            <form action="{{ route('blog.store.komentar') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <input name="nama" type="text" class="form-control" placeholder="Masukkan Nama Anda" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <input name="email" type="text" class="form-control" placeholder="Masukkan Email Anda" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group">
                                        <input name="link_website" type="text" class="form-control" placeholder="Masukkan Website Anda">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group">
                                        <textarea name="komentar" class="form-control" placeholder="Masukkan Komentar" required></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <input name="artikel_id" value="{{ $artikel->id }}" type="text" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <input name="categories_artikel_id" value="{{ $artikel->categories_artikel_id }}" type="text" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <input name="user_id" value="{{ $artikel->user_id }}" type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <input name="reply" value="{{ $reply->id }}" type="text" class="form-control">
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
                                <div class="comment-img"><img src="assets/img/blog/comments-3.jpg" alt=""></div>
                                <div>
                                    <h5><a href="">{{ $nestedReply->nama }}</a> <a href="#" class="reply" onclick="showReplyForm(event, {{ $nestedReply->id }})"><i class="bi bi-reply-fill"></i> Reply</a></h5>
                                    <time datetime="{{ $nestedReply->created_at }}">{{ \Carbon\Carbon::parse($nestedReply->created_at)->format('l, j F Y') }}</time>
                                    <p>
                                        {{ $nestedReply->komentar }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <!--================== END ==================-->

                        <!--================== FORM KOMENTAR YANG DI-REPLY KEMBALI ==================-->
                        <div id="reply-form-{{ $nestedReply->id }}" class="reply-form" style="display: none;">
                            <h4>Leave a Reply</h4>
                            <p>Your email address will not be published. Required fields are marked * </p>
                            <form action="{{ route('blog.store.komentar') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <input name="nama" type="text" class="form-control" placeholder="Masukkan Nama Anda" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <input name="email" type="text" class="form-control" placeholder="Masukkan Email Anda" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group">
                                        <input name="link_website" type="text" class="form-control" placeholder="Masukkan Website Anda">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group">
                                        <textarea name="komentar" class="form-control" placeholder="Masukkan Komentar" required></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <input name="artikel_id" value="{{ $artikel->id }}" type="text" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <input name="categories_artikel_id" value="{{ $artikel->categories_artikel_id }}" type="text" class="form-control">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <input name="user_id" value="{{ $artikel->user_id }}" type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <input name="reply" value="{{ $nestedReply->id }}" type="text" class="form-control">
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
                            <h4>Leave a Reply</h4>
                            <p>Your email address will not be published. Required fields are marked * </p>
                            <form action="{{ route('blog.store.komentar') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <input name="nama" type="text" class="form-control" placeholder="Masukkan Nama Anda" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <input name="email" type="text" class="form-control" placeholder="Masukkan Email Anda" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group">
                                        <input name="link_website" type="text" class="form-control" placeholder="Masukkan Website Anda">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col form-group">
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

                        <h3 class="sidebar-title">Search</h3>
                        <div class="sidebar-item search-form">
                            <form action="">
                                <input type="text">
                                <button type="submit"><i class="bi bi-search"></i></button>
                            </form>
                        </div><!-- End sidebar search formn-->

                        <!--================== KATEGORI TERBARU ==================-->
                        <h3 class="sidebar-title">Categories</h3>
                        <div class="sidebar-item categories">
                            <ul>
                                @foreach($categories_artikel->take(6) as $category)
                                <li><a href="{{ route('blog.topic.kategori', ['categories_artikel_id' => $category->id, 'token' => $category->token]) }}">{{ $category->kategori }} <span>({{ strtoupper($category->jumlah_artikel) }})</span></a></li>
                                @endforeach
                            </ul>
                        </div>
                        <!--================== END ==================-->

                        <!--================== POST TERBARU ==================-->
                        <h3 class="sidebar-title">Recent Posts</h3>
                        <div class="sidebar-item recent-posts">
                            @foreach ($datas->take(5) as $article)
                            <div class="post-item clearfix">
                                <img src="{{ asset('images/' . $article->gambar_depan) }}" alt="">
                                <a href="{{ route('blog.topic.blog-single', ['id' => $article->id, 'token' => $article->token]) }}">
                                    <h4>{{ $article->judul }}</h4>
                                </a>
                                <time datetime="{{ $article->created_at }}"> {{ strftime('%A, %d %B %Y', strtotime($article->created_at)) }}</time>
                            </div>
                            @endforeach
                        </div>
                        <!--================== END ==================-->

                        <!--================== MENAMPILKAN KATA KUNCI ==================-->
                        <h3 class="sidebar-title">Tags</h3>
                        <div class="sidebar-item tags">
                            <ul>
                                @php
                                $keywords = explode(',', $artikel->kata_kunci); // Memisahkan kata kunci yang dipisahkan oleh koma
                                @endphp
                                @foreach ($keywords as $keyword)
                                <li><a href="#">{{ $keyword }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                        <!--================== END ==================-->
                    </div>
                </div>
            </div>
        </div>
    </section>

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
@stop