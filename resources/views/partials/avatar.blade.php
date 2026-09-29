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

{{-- $lencana: tampilkan lencana terverifikasi di sudut foto. Dimatikan
     secara bawaan karena di dalam tabel ia hanya menambah keramaian. --}}
@php ($lencana = $lencana ?? false)

@if ($lencana)
    <span class="mis-foto-bingkai">
@endif

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

@if ($lencana)
        {{-- Bentuk bergerigi digambar sebagai SATU path SVG, bukan lingkaran
             CSS: tepi bergelombang tidak bisa dibuat dengan border-radius. --}}
        @php ($sudahVerif = (bool) ($orang->email_verified_at ?? null))
        <span class="mis-foto-lencana {{ $sudahVerif ? '' : 'belum' }}"
            title="{{ $sudahVerif ? 'Email sudah terverifikasi' : 'Email belum terverifikasi' }}">
            <svg viewBox="-7 -7 114 114" aria-hidden="true">
                <path class="mis-lencana-tepi" d="M89.0 50.0 Q104.4 64.6 83.8 69.5 Q89.8 89.8 69.5 83.8 Q64.6 104.4 50.0 89.0 Q35.4 104.4 30.5 83.8 Q10.2 89.8 16.2 69.5 Q-4.4 64.6 11.0 50.0 Q-4.4 35.4 16.2 30.5 Q10.2 10.2 30.5 16.2 Q35.4 -4.4 50.0 11.0 Q64.6 -4.4 69.5 16.2 Q89.8 10.2 83.8 30.5 Q104.4 35.4 89.0 50.0Z" />
                <path class="mis-lencana-isi" d="M89.0 50.0 Q104.4 64.6 83.8 69.5 Q89.8 89.8 69.5 83.8 Q64.6 104.4 50.0 89.0 Q35.4 104.4 30.5 83.8 Q10.2 89.8 16.2 69.5 Q-4.4 64.6 11.0 50.0 Q-4.4 35.4 16.2 30.5 Q10.2 10.2 30.5 16.2 Q35.4 -4.4 50.0 11.0 Q64.6 -4.4 69.5 16.2 Q89.8 10.2 83.8 30.5 Q104.4 35.4 89.0 50.0Z" />
                @if ($sudahVerif)
                    <path class="mis-lencana-centang" d="M33 51 L45 63 L68 38" />
                @else
                    <path class="mis-lencana-centang" d="M50 30 L50 56 M50 68 L50 70" />
                @endif
            </svg>
        </span>
    </span>
@endif
