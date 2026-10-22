<?xml version="1.0" encoding="UTF-8"?>
{{-- Baris <?xml ?> di atas ditulis sebagai teks biasa, bukan lewat PHP.

     Blade tidak menyentuhnya, tetapi PHP dengan short_open_tag menyala akan
     membaca "<?xml" sebagai pembuka tag PHP dan melempar galat sintaks pada
     berkas yang kelihatannya benar. Di sini aman karena berkasnya dikompilasi
     Blade lebih dulu; kalau suatu saat dipindah ke berkas PHP biasa, bagian
     inilah yang pertama patah. --}}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>Blog Rumah Scopus Foundation</title>
        <link>{{ url('/blog') }}</link>
        <atom:link href="{{ route('blog.rss') }}" rel="self" type="application/rss+xml" />
        <description>Catatan soal nulis artikel ilmiah, milih jurnal, ngolah data, sampai hal-hal yang sering bikin naskah balik lagi dari reviewer.</description>
        <language>id-ID</language>
        @if ($artikel->isNotEmpty())
            <lastBuildDate>{{ \Carbon\Carbon::parse($artikel->first()->created_at)->toRfc2822String() }}</lastBuildDate>
        @endif

        @foreach ($artikel as $tulisan)
            <item>
                <title>{{ $tulisan->judul }}</title>
                <link>{{ route('blog.topic.blog-single', ['id' => $tulisan->id]) }}</link>
                {{-- isPermaLink="true": guid-nya memang alamat yang bisa dibuka,
                     dan pembaca umpan memakainya untuk tahu mana yang sudah
                     pernah ditampilkan. --}}
                <guid isPermaLink="true">{{ route('blog.topic.blog-single', ['id' => $tulisan->id]) }}</guid>
                <pubDate>{{ \Carbon\Carbon::parse($tulisan->created_at)->toRfc2822String() }}</pubDate>
                @if ($tulisan->kategori)
                    <category>{{ $tulisan->kategori }}</category>
                @endif
                <description>{{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $tulisan->isi))), 280) }}</description>
                {{-- Isi penuhnya di CDATA: markahnya tidak boleh di-escape dua
                     kali, dan tanpa CDATA satu tanda & di dalam tulisan sudah
                     cukup membuat seluruh umpan ditolak pembacanya. --}}
                <content:encoded><![CDATA[{!! $tulisan->isi !!}]]></content:encoded>
            </item>
        @endforeach
    </channel>
</rss>
