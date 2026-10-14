@extends('layouts.account')
@extends('layouts.loader')

@section('title')
{{ $promo->nama }} | Promo Layanan | MIS
@stop

@include('partials.toast-flash')

@push('gaya')
<style>
    /* Bahasa rupanya mengikuti docs/panduan-ui-mis.md — sama dengan rincian
       Angkatan Layanan. Yang ditulis di sini hanya yang khas layar ini. */

    .prd-kisi { display: grid; gap: var(--mis-jarak); }

    @media (min-width: 992px) {
        /*
         * Kiri lebih sempit: isinya tujuh baris fakta pendek, sementara kanan
         * memuat tabel per angkatan DAN jejak. Dibagi rata, kolom kiri
         * menyisakan ruang kosong hampir separuh tingginya.
         */
        .prd-kisi { grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr); align-items: start; }
        .prd-penuh { grid-column: 1 / -1; }
    }

    .prd-fakta { display: grid; gap: 11px; }

    .prd-baris {
        display: grid;
        grid-template-columns: minmax(0, 130px) minmax(0, 1fr);
        gap: 10px; align-items: baseline;
        padding-bottom: 9px;
        border-bottom: 1px dashed var(--mis-garis);
    }

    .prd-baris:last-child { border-bottom: 0; padding-bottom: 0; }
    .prd-label { line-height: 1.45; font-size: .74rem; color: var(--mis-tinta-4); }
    .prd-nilai { line-height: 1.45; font-size: .84rem; font-weight: 700; color: var(--mis-tinta); overflow-wrap: anywhere; }
    .prd-nilai.samar { font-weight: 400; color: var(--mis-tinta-3); }

    /* ----------------------------------------------------------- dampak */

    /*
     * Empat angka hasil promo, memakai .mis-ubin bersama.
     *
     * Bukan ubin buatan sendiri: RupaBersamaTest melarang layar membuat
     * salinan ubin ringkasan dengan awalannya sendiri, dan larangan itu lahir
     * dari dua layar yang sempat punya tiga ukuran ubin berbeda untuk tugas
     * yang sama. Yang ditulis di sini hanya WARNANYA.
     *
     * Susunannya menurun (bukan baris berikon) karena ubin ini tidak punya
     * medali: tanpa ikon, flex mendatar bawaan .mis-ubin menyisakan ruang
     * kosong di kirinya.
     */
    .prd-angka { display: grid; grid-template-columns: repeat(auto-fit, minmax(128px, 1fr)); gap: 10px; }

    .prd-angka .mis-ubin { display: block; padding: 12px 13px; }
    .prd-angka .mis-ubin-angka { font-size: 1.2rem; }

    /* Yang berupa uang dibedakan warnanya: ia angka yang dikorbankan, bukan
       angka yang dikumpulkan, dan dua-duanya hitam membuatnya terbaca sama.

       Namanya sengaja tidak memuat kata "ubin": RupaBersamaTest mencari nama
       kelas berpola <awalan>-ubin untuk menangkap layar yang membuat salinan
       ubin ringkasannya sendiri, dan "prd-uang-ubin" tertangkap polanya
       padahal ia cuma modifier warna di atas .mis-ubin bersama. */
    .prd-warna-uang { border-color: #fecaca !important; background: #fef2f2; }
    .prd-warna-uang .mis-ubin-angka { color: #b91c1c; }

    .prd-warna-kuota { border-color: #fde68a !important; background: #fffbeb; }
    .prd-warna-kuota .mis-ubin-angka { color: #b45309; }

    /* Batang kuota: tanpa gambarnya, "12 dari 50" harus dihitung sendiri. */
    .prd-batang { height: 7px; margin-top: 9px; border-radius: 999px; background: #fde68a; overflow: hidden; }
    .prd-batang > span { display: block; height: 100%; border-radius: 999px; background: #d97706; }

    /* ------------------------------------------------------ per angkatan */

    .prd-angkatan-nama { margin: 0; line-height: 1.3; font-size: .82rem; font-weight: 700; color: var(--mis-tinta); overflow-wrap: anywhere; }
    .prd-angkatan-ket { margin: 1px 0 0; line-height: 1.4; font-size: .71rem; color: var(--mis-tinta-3); }

    /* Nominal tidak boleh terpatah; "Rp 2.600.000" yang jadi dua baris
       terbaca seperti dua angka. */
    .prd-uang { white-space: nowrap; font-weight: 800; }

    /* --------------------------------------------------------- angkatan */

    /*
     * Pita peringatan angkatan terlewat. Kuning, bukan merah: tidak ada yang
     * rusak — promonya bekerja persis seperti yang disetel. Yang perlu
     * diputuskan orang apakah angkatan baru itu memang sengaja tidak ikut.
     */
    .prd-ingatkan { border-color: #fde68a !important; background: #fffbeb; }
    .prd-ingatkan-judul { display: flex; align-items: center; gap: 8px; margin: 0 0 4px; line-height: 1.35; font-size: .88rem; font-weight: 800; color: #92400e; }
    .prd-ingatkan-judul .fas { font-size: inherit; }
    .prd-ingatkan-teks { margin: 0 0 11px; line-height: 1.55; font-size: .78rem; color: #a16207; }
    .prd-keping-sisa { display: inline-flex; align-items: center; padding: 5px 11px; border-radius: 999px; background: #fef3c7; font-size: .75rem; font-weight: 700; color: #92400e; }

    /* Ubin yang sekaligus tautan. .mis-ubin bersama sudah menyediakan
       keadaan hover untuk a.mis-ubin, jadi yang ditulis di sini cuma
       membuang garis bawah bawaannya.

       Namanya tidak memuat kata "ubin" — RupaBersamaTest mencari pola
       <awalan>-ubin untuk menangkap layar yang menyalin ubin ringkasannya
       sendiri, dan nama modifier yang kebetulan mengandungnya ikut
       tertangkap walau ia justru MEMAKAI ubin bersamanya. */
    .prd-angka-tautan { text-decoration: none; }
    .prd-angka-tautan:hover { text-decoration: none; }

    .prd-lihat { display: inline-flex; align-items: center; gap: 5px; font-size: .76rem; font-weight: 700; color: #4f46e5; white-space: nowrap; }
    .prd-lihat .fas { font-size: inherit; }
    .prd-lihat:hover { color: #4338ca; text-decoration: none; }

    .prd-keping { display: flex; flex-wrap: wrap; gap: 7px; }

    .prd-keping a {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 11px;
        border: 1px solid var(--mis-garis); border-radius: 999px;
        background: #fff;
        line-height: 1.4; font-size: .75rem; font-weight: 600; color: var(--mis-tinta-2);
    }

    .prd-keping a:hover { border-color: #c7d2fe; background: #eef2ff; color: #4338ca; text-decoration: none; }
    .prd-keping .fas { font-size: .68rem; color: var(--mis-tinta-4); }

    /* ------------------------------------------------------------ jejak */

    .prd-jejak { display: grid; gap: 0; }

    .prd-peristiwa {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        column-gap: 11px;
        padding: 11px 0;
        border-top: 1px dashed var(--mis-garis);
    }

    .prd-peristiwa:first-child { border-top: 0; padding-top: 0; }

    .prd-peristiwa-ikon {
        grid-row: 1 / span 2;
        display: grid; place-items: center;
        width: 28px; height: 28px;
        border-radius: 9px;
        font-size: .72rem;
    }

    /* font-size: inherit WAJIB — layout memasang .fas { font-size: 20px }
       untuk seluruh halaman, jadi mengecilkan pembungkusnya tidak mengubah
       glifnya. */
    .prd-peristiwa-ikon .fas { font-size: inherit; }

    .prd-peristiwa-judul { margin: 0; line-height: 1.35; font-size: .8rem; font-weight: 700; color: var(--mis-tinta); }
    .prd-peristiwa-ket { margin: 1px 0 0; line-height: 1.45; font-size: .73rem; color: var(--mis-tinta-3); overflow-wrap: anywhere; }

    @media (max-width: 767.98px) {
        .prd-baris { grid-template-columns: minmax(0, 1fr); gap: 2px; }
    }
</style>
@endpush

@section('content')
<div class="main-content mis-badan">
    <section class="section">

        <div class="mis-kepala">
            <span class="mis-medali mis-ungu" aria-hidden="true"><i class="fas fa-tags"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">{{ $promo->nama }}</h1>
                <p class="mis-sub">
                    Potongan {{ $promo->potongan_ringkas }} per peserta &middot; {{ $promo->periode_terbaca }}
                </p>
            </div>
            <div class="mis-kepala-aksi">
                @if ($bolehUbah)
                    <a href="{{ route('account.promo-layanan.edit', $promo->id) }}" class="mis-tombol mis-tombol-ungu">
                        <i class="fas fa-pen"></i> Ubah
                    </a>
                    {{-- Menggandakan lewat POST: ia MEMBUAT baris baru, dan
                         alamat GET yang membuat baris ikut terpicu pemuat
                         pratinjau tautan serta tombol Kembali peramban. --}}
                    <form method="POST" action="{{ route('account.promo-layanan.gandakan', $promo->id) }}" style="display: inline-flex; margin: 0;">
                        @csrf
                        <button type="submit" class="mis-tombol mis-tombol-halus">
                            <i class="fas fa-copy mis-ikon-ungu"></i> Gandakan
                        </button>
                    </form>
                @endif
                <a href="{{ route('account.promo-layanan.index') }}" class="mis-tombol mis-tombol-halus">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="prd-kisi">

            {{-- ----------------------------------------------- keterangan --}}
            <div class="mis-bagian">
                <p class="mis-kartu-judul">
                    <i class="fas fa-info-circle mis-ikon-biru"></i> Keterangan
                </p>

                <div class="prd-fakta">
                    <div class="prd-baris">
                        <span class="prd-label">Keadaan</span>
                        <span class="prd-nilai">
                            <span class="mis-pil mis-pil-{{ $promo->keadaan_warna }}">{{ $promo->keadaan_terbaca }}</span>
                        </span>
                    </div>

                    <div class="prd-baris">
                        <span class="prd-label">Potongan</span>
                        <span class="prd-nilai">
                            {{ $promo->potongan_ringkas }} per peserta
                            @if ($promo->tipe === \App\PromoLayanan::PERSEN && $promo->batas_potongan)
                                <span class="prd-nilai samar">
                                    &middot; maksimal Rp {{ number_format($promo->batas_potongan, 0, ',', '.') }}
                                </span>
                            @endif
                        </span>
                    </div>

                    <div class="prd-baris">
                        <span class="prd-label">Periode</span>
                        <span class="prd-nilai">
                            {{ $promo->periode_terbaca }}
                            @if ($promo->sisa_hari !== null)
                                <span class="prd-nilai samar">
                                    &middot; {{ $promo->sisa_hari === 0 ? 'habis hari ini' : 'sisa ' . $promo->sisa_hari . ' hari' }}
                                </span>
                            @endif
                        </span>
                    </div>

                    <div class="prd-baris">
                        <span class="prd-label">Kuota</span>
                        <span class="prd-nilai {{ $promo->kuota === null ? 'samar' : '' }}">
                            {{-- Tanpa batas ditulis apa adanya, bukan "0":
                                 nol berarti habis sejak detik pertama, dan itu
                                 hal yang sama sekali berbeda. --}}
                            {{ $promo->kuota === null
                                ? 'Tanpa batas'
                                : number_format($promo->kuota) . ' pendaftaran' }}
                        </span>
                    </div>

                    <div class="prd-baris">
                        <span class="prd-label">Dibuat oleh</span>
                        <span class="prd-nilai {{ $promo->penginput ? '' : 'samar' }}">
                            {{ $promo->penginput?->full_name ?: 'Tidak tercatat' }}
                            <span class="prd-nilai samar">
                                &middot; {{ $promo->created_at?->locale('id')->translatedFormat('j M Y') }}
                            </span>
                        </span>
                    </div>

                    <div class="prd-baris">
                        <span class="prd-label">Catatan</span>
                        <span class="prd-nilai {{ $promo->catatan ? '' : 'samar' }}">
                            {{ $promo->catatan ?: 'Tidak ada catatan' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- --------------------------------------------------- hasil --}}
            <div class="mis-bagian">
                <p class="mis-kartu-judul">
                    <i class="fas fa-chart-line mis-ikon-hijau"></i> Hasil promo ini
                </p>
                <p class="mis-kartu-sub">
                    Dihitung dari pendaftaran yang benar-benar kena promo ini — bukan dari namanya,
                    jadi angkanya tetap benar walau promonya diganti nama.
                </p>

                <div class="prd-angka">
                    {{-- Angkanya TAUTAN ke orangnya.

                         "Dipakai 23 kali" menjawab berapa, tidak menjawab
                         siapa — dan pertanyaan kedua selalu menyusul. Berupa
                         <a>, bukan tombol berskrip, supaya alamatnya bisa
                         disalin dan dikirim ke orang lain. --}}
                    @if ($dampak['pendaftaran'] > 0)
                        <a class="mis-ubin prd-angka-tautan"
                            href="{{ route('account.pendaftaran-layanan.index', ['promo' => $promo->id]) }}"
                            title="Lihat pendaftarnya">
                            <p class="mis-ubin-angka">{{ number_format($dampak['pendaftaran']) }}</p>
                            <p class="mis-ubin-label">Pendaftaran &middot; lihat orangnya</p>
                        </a>
                    @else
                        <div class="mis-ubin">
                            <p class="mis-ubin-angka">0</p>
                            <p class="mis-ubin-label">Pendaftaran</p>
                        </div>
                    @endif
                    <div class="mis-ubin">
                        <p class="mis-ubin-angka">{{ number_format($dampak['peserta']) }}</p>
                        <p class="mis-ubin-label">Peserta</p>
                    </div>
                    <div class="mis-ubin prd-warna-uang">
                        <p class="mis-ubin-angka">Rp {{ number_format($dampak['potongan'], 0, ',', '.') }}</p>
                        <p class="mis-ubin-label">Total potongan</p>
                    </div>
                    @if ($promo->kuota !== null)
                        <div class="mis-ubin prd-warna-kuota">
                            <p class="mis-ubin-angka">{{ number_format($promo->sisa_kuota) }}</p>
                            <p class="mis-ubin-label">Sisa kuota dari {{ number_format($promo->kuota) }}</p>
                            @php
                                $pakai = $promo->kuota > 0
                                    ? min(100, (int) round($dampak['pendaftaran'] / $promo->kuota * 100))
                                    : 100;
                            @endphp
                            <div class="prd-batang" role="img"
                                aria-label="Terpakai {{ $pakai }} persen dari kuotanya">
                                <span style="width: {{ $pakai }}%"></span>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($dampak['pendaftaran'] === 0)
                    <p class="mis-bantuan" style="margin-top: 12px;">
                        Belum ada pendaftaran yang kena promo ini.
                        @if ($promo->angkatan->isEmpty())
                            Promonya belum menunjuk angkatan mana pun — selama itu ia tidak memotong apa pun.
                        @elseif ($promo->keadaan !== 'berjalan')
                            Wajar: promonya {{ mb_strtolower($promo->keadaan_terbaca) }}.
                        @endif
                    </p>
                @endif
            </div>

            {{-- Angkatan yang dibuat SESUDAH promo ini dan belum dicentang.

                 Akibat wajar dari "centang satu per satu", tetapi tanpa
                 peringatan ini akibatnya tidak pernah terlihat: angkatan baru
                 diam-diam harga normal, dan yang menyadarinya biasanya
                 pendaftar yang membandingkan harga. --}}
            @if ($terlewat->isNotEmpty())
                <div class="mis-bagian prd-penuh prd-ingatkan" role="alert">
                    <p class="prd-ingatkan-judul">
                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                        {{ $terlewat->count() }} angkatan dibuat sesudah promo ini dan belum ikut
                    </p>
                    <p class="prd-ingatkan-teks">
                        Promo menempel pada angkatan yang dicentang, jadi angkatan baru tidak ikut sendiri.
                        Selama belum dicentang, angkatan di bawah ini tetap harga normal.
                    </p>
                    <div class="prd-keping">
                        @foreach ($terlewat->take(8) as $a)
                            <a href="{{ route('account.kategori-layanan.detail', $a->id) }}">
                                <i class="fas {{ $katalog[$a->layanan]['ikon'] ?? 'fa-tag' }}" aria-hidden="true"></i>
                                {{ $a->nama }}{{ $a->nama_ke ? ' ke-' . $a->nama_ke : '' }}
                            </a>
                        @endforeach
                        @if ($terlewat->count() > 8)
                            <span class="prd-keping-sisa">+{{ $terlewat->count() - 8 }} lainnya</span>
                        @endif
                    </div>
                    @if ($bolehUbah)
                        <a href="{{ route('account.promo-layanan.edit', $promo->id) }}"
                            class="mis-tombol mis-tombol-ungu" style="margin-top: 12px;">
                            <i class="fas fa-check-square"></i> Centang angkatannya
                        </a>
                    @endif
                </div>
            @endif

            {{-- ------------------------------------------- per angkatan --}}
            <div class="mis-bagian prd-penuh">
                <p class="mis-kartu-judul">
                    <i class="fas fa-layer-group mis-ikon-ungu"></i> Angkatan yang kena promo
                    <span class="mis-pil mis-pil-ungu">{{ $promo->angkatan->count() }}</span>
                </p>

                @if ($promo->angkatan->isEmpty())
                    <div class="mis-kosong">
                        <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-exclamation-triangle"></i></span>
                        <p class="mis-kosong-judul">Belum menunjuk angkatan mana pun</p>
                        <p class="mis-kosong-teks">
                            Promo menempel pada angkatan. Selama belum ada yang dicentang,
                            promo ini tidak memotong harga apa pun.
                        </p>
                        @if ($bolehUbah)
                            <a href="{{ route('account.promo-layanan.edit', $promo->id) }}"
                                class="mis-tombol mis-tombol-ungu mis-kosong-aksi">
                                <i class="fas fa-check-square"></i> Pilih angkatannya
                            </a>
                        @endif
                    </div>
                @else
                    {{-- Keping tautan, bukan tabel: tanpa pemakaian, baris
                         tabelnya hanya berisi nama — dan dua belas baris
                         sekadar nama memakan tinggi yang tidak sepadan. --}}
                    <div class="prd-keping">
                        @foreach ($promo->angkatan as $a)
                            <a href="{{ route('account.kategori-layanan.detail', $a->id) }}"
                                title="Buka angkatan {{ $a->nama }}">
                                <i class="fas {{ $katalog[$a->layanan]['ikon'] ?? 'fa-tag' }}" aria-hidden="true"></i>
                                {{ $a->nama }}{{ $a->nama_ke ? ' ke-' . $a->nama_ke : '' }}
                            </a>
                        @endforeach
                    </div>

                    @if ($perAngkatan !== [])
                        <div class="mis-tabel-bungkus" style="margin-top: var(--mis-jarak);">
                            <table class="mis-tabel mis-tabel-kartu">
                                <thead>
                                    <tr>
                                        <th>Angkatan</th>
                                        <th>Layanan</th>
                                        <th>Pendaftaran</th>
                                        <th>Peserta</th>
                                        <th class="text-right">Potongan</th>
                                        <th class="text-right">Pendaftar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($perAngkatan as $baris)
                                        <tr>
                                            <td class="mis-td-utama">
                                                <div>
                                                    <p class="prd-angkatan-nama">
                                                        {{ $baris['nama'] }}{{ $baris['ke'] ? ' ke-' . $baris['ke'] : '' }}
                                                    </p>
                                                </div>
                                            </td>
                                            <td data-judul="Layanan">{{ $baris['nama_layanan'] }}</td>
                                            <td data-judul="Pendaftaran">{{ number_format($baris['pendaftaran']) }}</td>
                                            <td data-judul="Peserta">{{ number_format($baris['peserta']) }}</td>
                                            <td data-judul="Potongan" class="text-right">
                                                <span class="prd-uang">Rp {{ number_format($baris['potongan'], 0, ',', '.') }}</span>
                                            </td>
                                            <td data-judul="Pendaftar" class="text-right">
                                                <a class="prd-lihat"
                                                    href="{{ route('account.pendaftaran-layanan.index', ['promo' => $promo->id, 'angkatan' => $baris['kategori_id']]) }}"
                                                    title="Lihat pendaftar angkatan ini yang kena promo">
                                                    <i class="fas fa-users" aria-hidden="true"></i> lihat
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="mis-bantuan" style="margin-top: 12px;">
                            Belum ada pendaftaran dari angkatan-angkatan itu yang kena promo ini.
                        </p>
                    @endif
                @endif
            </div>

            {{-- --------------------------------------------------- jejak --}}
            <div class="mis-bagian prd-penuh">
                <p class="mis-kartu-judul">
                    <i class="fas fa-history mis-ikon-biru"></i> Jejak perubahan
                </p>
                <p class="mis-kartu-sub">
                    Promo mengubah harga yang ditagihkan ke orang, jadi tiap perubahannya dicatat.
                </p>

                @if ($jejak->isEmpty())
                    <div class="mis-kosong">
                        <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-history"></i></span>
                        <p class="mis-kosong-judul">Belum ada jejak</p>
                        <p class="mis-kosong-teks">
                            Promo ini dibuat sebelum pencatatan jejak dipasang.
                            Perubahan berikutnya akan tercatat di sini.
                        </p>
                    </div>
                @else
                    <div class="prd-jejak">
                        @foreach ($jejak as $p)
                            <div class="prd-peristiwa">
                                <span class="prd-peristiwa-ikon mis-pil mis-pil-{{ $p->warna }}" aria-hidden="true">
                                    <i class="fas {{ $p->ikon }}"></i>
                                </span>
                                <p class="prd-peristiwa-judul">
                                    {{ ucfirst($p->aksi) }}
                                    @if ($p->oleh_nama)
                                        oleh {{ $p->oleh_nama }}
                                    @endif
                                </p>
                                <p class="prd-peristiwa-ket">
                                    {{ $p->waktu_terbaca }}{{ $p->ringkasan ? ' — ' . $p->ringkasan : '' }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Penomoran, bukan "lihat 50 terakhir": jejak lama justru
                         yang dicari saat ada yang bertanya sejak kapan harganya
                         berubah. --}}
                    @if ($jejak->hasPages())
                        <div class="mis-halaman" style="margin-top: var(--mis-jarak);">
                            {{ $jejak->links('vendor.pagination.bootstrap-4') }}
                            <p class="mis-halaman-ket">
                                Menampilkan {{ $jejak->firstItem() }}–{{ $jejak->lastItem() }}
                                dari {{ number_format($jejak->total()) }} peristiwa.
                            </p>
                        </div>
                    @endif
                @endif
            </div>
        </div>

    </section>
</div>
@endsection
