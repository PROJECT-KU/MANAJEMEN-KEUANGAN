<?xml version="1.0" encoding="UTF-8"?>
{{-- sitemapS.org — JAMAK. Ditulis "sitemap.org" peta situsnya tetap XML
     yang sah dan tetap 200, tetapi namespace-nya bukan yang dikenali
     protokol, dan mesin pencari menolaknya tanpa memberi tahu siapa pun. --}}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($halaman as [$jalur, $bobot, $sering])
    <url>
        <loc>{{ url($jalur) }}</loc>
        <changefreq>{{ $sering }}</changefreq>
        <priority>{{ $bobot }}</priority>
    </url>
@endforeach
@foreach ($kategori as $id)
    <url>
        <loc>{{ route('blog.topic.kategori', ['categories_artikel_id' => $id]) }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
@endforeach
@foreach ($artikel as $tulisan)
    <url>
        <loc>{{ route('blog.topic.blog-single', ['id' => $tulisan->id]) }}</loc>
        {{-- W3C datetime, yang diminta protokolnya. Tanggal biasa diterima
             juga, tetapi lastmod yang salah bentuk DIABAIKAN diam-diam —
             dan peta situs tanpa lastmod kehilangan separuh gunanya. --}}
        <lastmod>{{ \Carbon\Carbon::parse($tulisan->updated_at ?? $tulisan->created_at)->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
@endforeach
</urlset>
