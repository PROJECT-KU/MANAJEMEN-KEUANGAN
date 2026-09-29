{{--
  Foto pengguna, atau inisial namanya kalau belum ada fotonya.

  Dipakai: @include('partials.avatar', ['orang' => $user, 'ukuran' => 38])

  Sebelumnya semua yang belum berfoto menampilkan berkas no-image.jpg yang
  sama persis. Di daftar pelanggan itu berarti 8 dari 12 baris bergambar
  kembar, sehingga tiap baris kehilangan jati dirinya dan mata tidak punya
  pegangan saat menyusuri daftar. Inisial berwarna memberi tiap orang tanda
  yang berbeda tanpa menuntut siapa pun mengunggah apa pun.
--}}
@php
    $ukuran = $ukuran ?? 38;
    $namaLengkap = trim((string) ($orang->full_name ?: $orang->username ?: '?'));

    /*
     * Dua huruf pertama dari dua kata pertama; kalau cuma satu kata, dua huruf
     * pertamanya. mb_* dipakai supaya nama ber-aksen tidak terpotong di tengah
     * bita.
     */
    $kata = preg_split('/\s+/', $namaLengkap, -1, PREG_SPLIT_NO_EMPTY) ?: ['?'];
    $inisial = count($kata) > 1
        ? mb_substr($kata[0], 0, 1) . mb_substr($kata[1], 0, 1)
        : mb_substr($kata[0], 0, 2);
    $inisial = mb_strtoupper($inisial);

    /*
     * Warna dipilih dari nama, bukan diacak: orang yang sama harus selalu
     * berwarna sama, termasuk sesudah halaman dimuat ulang atau diurutkan
     * ulang. Enam warna diambil dari palet yang sudah dipakai .mis-medali.
     */
    $palet = [
        ['#eef2ff', '#4338ca'],
        ['#ecfdf5', '#047857'],
        ['#eff6ff', '#1d4ed8'],
        ['#fff7ed', '#c2410c'],
        ['#fdf4ff', '#a21caf'],
        ['#fef2f2', '#be123c'],
    ];
    [$latar, $tinta] = $palet[crc32(mb_strtolower($namaLengkap)) % count($palet)];

    $punyaFoto = \App\Support\FotoProfil::punyaFoto($orang->gambar ?? null);
@endphp

@if ($punyaFoto)
    <img class="mis-avatar" alt="Foto {{ $namaLengkap }}"
        src="{{ \App\Support\FotoProfil::url($orang->gambar) }}"
        style="width: {{ $ukuran }}px; height: {{ $ukuran }}px;">
@else
    {{-- aria-hidden: inisialnya cuma pengganti gambar, dan nama lengkapnya
         sudah tertulis tepat di sebelahnya. Dibacakan, ia hanya mengeja dua
         huruf tanpa arti. --}}
    <span class="mis-avatar mis-avatar-inisial" aria-hidden="true"
        style="width: {{ $ukuran }}px; height: {{ $ukuran }}px;
               background: {{ $latar }}; color: {{ $tinta }};
               font-size: {{ max(11, (int) round($ukuran * 0.36)) }}px;">{{ $inisial }}</span>
@endif
