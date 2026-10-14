@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Promo Layanan | MIS
@stop

@include('partials.toast-flash')

@push('gaya')
<style>
    /*
     * Bahasa rupanya mengikuti docs/panduan-ui-mis.md — sama dengan Angkatan
     * Layanan dan Data Pelanggan. Yang ditulis di sini hanya yang khas layar
     * ini.
     */

    .pro-nama { margin: 0; line-height: 1.3; font-size: .86rem; font-weight: 700; color: var(--mis-tinta); overflow-wrap: anywhere; }
    .pro-nama a { color: inherit; }
    .pro-nama a:hover { color: #4f46e5; text-decoration: none; }
    .pro-ket { margin: 1px 0 0; line-height: 1.4; font-size: .73rem; color: var(--mis-tinta-3); overflow-wrap: anywhere; }

    /*
     * Potongannya DIBESARKAN dan tidak boleh terpatah.
     *
     * Ia angka yang paling sering dicari di layar ini — "promo ini berapa?" —
     * dan "Rp 2.600.000" yang patah jadi dua baris terbaca seperti dua angka.
     * Keterangannya di bawahnya justru harus boleh membungkus.
     */
    .pro-potongan { display: block; line-height: 1.25; font-size: 1rem; font-weight: 800; color: #4338ca; white-space: nowrap; }
    .pro-potongan-ket { display: block; margin-top: 1px; line-height: 1.35; font-size: .72rem; color: var(--mis-tinta-3); }

    .pro-periode { display: block; line-height: 1.3; font-size: .8rem; color: var(--mis-tinta-2); }
    .pro-sisa { display: block; margin-top: 2px; line-height: 1.35; font-size: .72rem; font-weight: 700; color: #b45309; }

    .pro-jumlah { display: inline-flex; align-items: baseline; gap: 4px; font-size: .8rem; color: var(--mis-tinta-2); }
    .pro-jumlah strong { font-size: .92rem; font-weight: 800; color: var(--mis-tinta); }

    /* Angkatan yang belum dicentang satu pun: promonya tidak memotong apa pun,
       dan itu harus terbaca sebagai peringatan, bukan sebagai angka nol biasa. */
    .pro-nihil { display: inline-flex; align-items: center; gap: 5px; font-size: .76rem; font-weight: 700; color: #b45309; }
    /* Bobot (0,2,1) supaya menang atas `a:hover` milik Bootstrap yang (0,1,1)
       — tanpa itu warnanya berubah jadi biru bawaan saat disentuh. */
    .pro-aksi a.pro-nihil:hover, a.pro-nihil:hover { color: #92400e; text-decoration: underline; }

    /* Kolom centang: ukuran dan daerah tekannya datang dari .mis-centang
       bersama, supaya tidak jadi ukuran ketiga untuk tugas yang sama. */
    .pro-centang-sel { width: 34px; padding-right: 0 !important; }

    .pro-massal {
        display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
        padding: 11px 13px; margin-bottom: 12px;
        border: 1px solid #c7d2fe; border-radius: 13px;
        background: #eef2ff;
    }

    .pro-massal-jumlah { font-size: .8rem; color: #4338ca; }
    .pro-massal-jumlah strong { font-size: .92rem; font-weight: 800; }
    .pro-massal-tombol { display: flex; flex-wrap: wrap; gap: 8px; margin-left: auto; }

    @media (max-width: 575.98px) {
        /* Tombolnya turun ke barisnya sendiri dan melebar penuh: didorong ke
           kanan di 390px, keduanya berdesakan jadi 90px dan tulisannya
           terpotong. */
        .pro-massal-tombol { width: 100%; margin-left: 0; }
        .pro-massal-tombol .mis-tombol { flex: 1 1 0; min-width: 0; justify-content: center; }
    }

    /* Batang kuota. Tipis dan tanpa tulisan: angkanya sudah ada di atasnya,
       yang ditambahkan batang ini cuma rasa "tinggal sedikit lagi" yang tidak
       bisa diberikan dua angka bersebelahan. */
    .pro-batang {
        display: block; width: 100%; max-width: 110px; height: 5px;
        margin-top: 5px; border-radius: 999px; background: #e2e8f0; overflow: hidden;
    }

    .pro-batang > span { display: block; height: 100%; border-radius: 999px; background: #6366f1; }

    .pro-aksi { display: flex; align-items: center; justify-content: flex-end; gap: 6px; }

    /* Borang saklar dan hapus tidak boleh memakai ruang barisnya sendiri. */
    .pro-aksi form { display: inline-flex; margin: 0; }

    /*
     * Lebar kolom dipatok persen.
     *
     * Dibiarkan otomatis, peramban membagi rata menurut isi terpanjang dan
     * kolom Promo — yang paling perlu ruang — justru menyempit karena namanya
     * pendek dibanding periode dan tombolnya.
     */
    .pro-k-nama { width: 24%; }
    .pro-k-potongan { width: 12%; }
    .pro-k-periode { width: 11%; }
    .pro-k-selesai { width: 11%; }
    .pro-k-angkatan { width: 10%; }
    .pro-k-pakai { width: 12%; }
    .pro-k-keadaan { width: 10%; }
    .pro-k-aksi { width: 10%; }

    @media (max-width: 767.98px) {
        /*
         * Di mode kartu tombolnya rata kiri bersama label selnya, bukan
         * terdorong ke kanan: `flex-end` di dalam kartu meninggalkan lajur
         * kosong di sisi kiri sel yang isinya cuma tiga tombol 34px.
         */
        .pro-aksi { justify-content: flex-start; }
    }
</style>
@endpush

@section('content')
<div class="main-content mis-badan">
    <section class="section">

        {{-- ------------------------------------------------ kepala --}}
        <div class="mis-kepala">
            <span class="mis-medali mis-ungu" aria-hidden="true"><i class="fas fa-tags"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Promo Layanan</h1>
                <p class="mis-sub">Potongan harga yang berlaku otomatis untuk angkatan yang dipilih.</p>
            </div>
            <div class="mis-kepala-aksi" id="pro-aksi">
                {{-- Unduhan membawa saringan yang sedang dipakai, bukan seluruh
                     tabel: yang diunduh orang hampir selalu yang dilihatnya.

                     $saringan datang dari pengendalinya (MEDAN_SARINGAN), TIDAK
                     dirakit di sini — daftar yang ditulis tangan di markah
                     cepat atau lambat ketinggalan satu medan, dan berkasnya
                     memulangkan seluruh baris tanpa satu pun tanda bahwa
                     saringannya tertinggal. --}}
                <a class="mis-tombol mis-tombol-halus"
                    href="{{ route('account.promo-layanan.excel', $saringan) }}">
                    <i class="fas fa-file-excel mis-ikon-hijau"></i> Unduh Excel
                </a>
                <a class="mis-tombol mis-tombol-halus"
                    href="{{ route('account.promo-layanan.cetak', $saringan) }}">
                    <i class="fas fa-file-pdf mis-ikon-merah"></i> Unduh PDF
                </a>
                @if ($bolehUbah)
                    <a class="mis-tombol mis-tombol-ungu" href="{{ route('account.promo-layanan.create') }}">
                        <i class="fas fa-plus"></i> Buat promo
                    </a>
                @endif
            </div>
        </div>

        {{-- ---------------------------------------------- ringkasan --}}
        {{-- Tiap ubin sekaligus pintasan saringan: angka yang menarik
             perhatian selalu memancing "yang mana saja?". Berupa TAUTAN, bukan
             tombol berskrip, supaya alamatnya bisa disalin. --}}
        <div id="pro-ringkas">
        <div class="mis-ringkas-geser" data-mis-geser>
        <div class="mis-ringkas mis-ringkas-auto" aria-label="Ringkasan promo">
            <a class="mis-ubin {{ $keadaan === null ? 'terpilih' : '' }}"
                href="{{ route('account.promo-layanan.index', request()->only('cari', 'layanan')) }}"
                title="Tampilkan semua promo">
                <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-tags"></i></span>
                <div>
                    {{-- 'semua' datang dari pengendalinya, BUKAN array_sum:
                         "hampir habis" adalah bagian dari "sedang berjalan",
                         jadi menjumlahkan seluruh ubin menghitung promo yang
                         sama dua kali. --}}
                    <p class="mis-ubin-angka">{{ number_format($ringkas['semua']) }}</p>
                    <p class="mis-ubin-label">Seluruh promo</p>
                </div>
            </a>

            @php
                // Dirakit dari larik, bukan lima blok yang hampir sama: ikon
                // dan warnanya harus cocok dengan lencana keadaan di tabel di
                // bawah, dan dua daftar terpisah pasti berselisih cepat atau
                // lambat.
                $ubin = [
                    ['berjalan', 'mis-hijau', 'fa-bolt', 'Sedang berjalan'],
                    ['hampir-habis', 'mis-jingga', 'fa-hourglass-half', 'Hampir habis'],
                    ['belum-mulai', 'mis-biru', 'fa-clock', 'Belum mulai'],
                    ['kuota-habis', 'mis-kuning', 'fa-ban', 'Kuota habis'],
                    ['selesai', 'mis-abu', 'fa-flag-checkered', 'Sudah selesai'],
                    ['nonaktif', 'mis-merah', 'fa-power-off', 'Dimatikan'],
                ];
            @endphp

            @foreach ($ubin as $u)
                <a class="mis-ubin {{ $keadaan === $u[0] ? 'terpilih' : '' }}"
                    href="{{ route('account.promo-layanan.index', array_merge(request()->only('cari', 'layanan'), ['keadaan' => $u[0]])) }}"
                    title="Saring: {{ $u[3] }}">
                    <span class="mis-medali kecil {{ $u[1] }}" aria-hidden="true"><i class="fas {{ $u[2] }}"></i></span>
                    <div>
                        <p class="mis-ubin-angka">{{ number_format($ringkas[$u[0]]) }}</p>
                        <p class="mis-ubin-label">{{ $u[3] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
            <p class="mis-ringkas-petunjuk" aria-hidden="true">
                <i class="fas fa-arrows-alt-h"></i> Geser untuk lihat semua
            </p>
        </div>
        </div>{{-- pro-ringkas --}}

        {{-- ---------------------------------------------- penyaring --}}
        {{-- Dibungkus <details>: di ponsel tiga kendali yang selalu terbuka
             memakan satu layar penuh sebelum promo pertama kelihatan. Di layar
             lebar skrip bersama memaksanya terbuka. --}}
        <details class="mis-lipat" id="pro-penyaring" data-mis-lipat>
            <summary>
                <i class="fas fa-sliders-h mis-ikon-ungu" aria-hidden="true"></i>
                Cari &amp; saring
                @if ($adaSaringan)
                    <span class="mis-pil mis-pil-ungu">aktif</span>
                @endif
            </summary>

        <div class="mis-saring-kartu">
        <form method="GET" action="{{ route('account.promo-layanan.index') }}" class="mis-saring" id="pro-borang"
            data-mis-saring="pro-hasil" data-mis-saring-juga="pro-ringkas">
            {{-- Urutan ikut terbawa saat menyaring; tanpa ini, menekan Terapkan
                 diam-diam mengembalikan urutannya ke bawaan. --}}
            <input type="hidden" name="urut" value="{{ $urut }}">
            <input type="hidden" name="arah" value="{{ $arah === 'asc' ? 'naik' : 'turun' }}">

            <div class="mis-isian mis-saring-cari">
                <label class="mis-label" for="pro-cari">Cari</label>
                <input type="search" class="form-control-modern" id="pro-cari" name="cari" data-mis-cari
                    value="{{ $cari }}" placeholder="Nama promo atau catatannya"
                    autocomplete="off" aria-controls="pro-hasil">
                <button type="button" class="mis-saring-hapus" id="pro-hapus" data-mis-kosongkan
                    aria-label="Hapus kata kunci pencarian" title="Hapus kata kunci"
                    @if ($cari === '') hidden @endif>
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
                <span class="mis-saring-sibuk" id="pro-sibuk" aria-hidden="true"></span>
            </div>

            <div class="mis-isian mis-saring-pilih">
                <label class="mis-label" for="pro-keadaan">Keadaan</label>
                <select class="form-control-modern" id="pro-keadaan" name="keadaan">
                    <option value="">Semua keadaan</option>
                    @foreach ($daftarKeadaan as $nilai => $nama)
                        <option value="{{ $nilai }}" @selected($keadaan === $nilai)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mis-isian mis-saring-pilih">
                <label class="mis-label" for="pro-layanan">Layanan</label>
                <select class="form-control-modern" id="pro-layanan" name="layanan">
                    <option value="">Semua layanan</option>
                    @foreach ($katalog as $kode => $tentang)
                        <option value="{{ $kode }}" @selected($layanan === $kode)>{{ $tentang['nama'] }}</option>
                    @endforeach
                </select>
            </div>

            {{--
              Pengurut KHUSUS ponsel.

              Kepala kolom yang bisa diurutkan ada di dalam <thead>, dan di mode
              kartu <thead> disembunyikan untuk pembaca layar saja — terukur ia
              berukuran 1x1 dan terklip. Tanpa menu ini, pengurutnya tidak bisa
              dijangkau sama sekali dari ponsel.
            --}}
            <div class="mis-isian mis-saring-pilih mis-urut-ponsel">
                <label class="mis-label" for="pro-urut-pilih">Urutkan</label>
                <select class="form-control-modern" id="pro-urut-pilih" name="urutgabung" data-mis-urut-ponsel>
                    @php
                        // Larik bersarang, bukan kunci "kolom|arah" yang dibelah
                        // di dalam @foreach: @php(...) sebaris tidak menangani
                        // pembongkaran larik dan halamannya 500 tanpa sebab.
                        $pilihanUrut = [
                            ['mulai', 'turun', 'Mulai terbaru'],
                            ['mulai', 'naik', 'Mulai terlama'],
                            ['selesai', 'naik', 'Paling dekat selesai'],
                            ['nama', 'naik', 'Nama A–Z'],
                            ['nama', 'turun', 'Nama Z–A'],
                            ['dibuat', 'turun', 'Terbaru dibuat'],
                        ];
                        $arahSekarang = $arah === 'asc' ? 'naik' : 'turun';
                    @endphp
                    @foreach ($pilihanUrut as $pilihan)
                        <option value="{{ $pilihan[0] }}|{{ $pilihan[1] }}"
                            @selected($urut === $pilihan[0] && $arahSekarang === $pilihan[1])>{{ $pilihan[2] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tombolnya tetap ada di markah dan baru disembunyikan skrip:
                 tanpa JavaScript penyaringnya masih bisa dipakai. --}}
            <button type="submit" class="mis-tombol mis-tombol-ungu" id="pro-terapkan" data-mis-terapkan>
                <i class="fas fa-search"></i> Terapkan
            </button>

            @if ($adaSaringan)
                <a href="{{ route('account.promo-layanan.index') }}"
                    class="mis-tombol mis-tombol-bahaya-halus" title="Hapus semua saringan">
                    <i class="fas fa-times"></i> Reset
                </a>
            @endif
        </form>
        </div>
        </details>

        {{-- ------------------------------------------------- daftar --}}
        {{-- role=status + aria-live: isi bagian ini ditukar diam-diam tiap
             ketikan, dan tanpa penanda itu pembaca layar tidak mengumumkan
             apa pun. --}}
        <div class="mis-hasil" id="pro-hasil" role="status" aria-live="polite" aria-atomic="false">
        @if ($daftar->isEmpty())
            <div class="mis-bagian">
                {{-- Dua keadaan yang terlihat sama padahal jalan keluarnya
                     berbeda: ganti kata kunci, atau buat promo pertama. --}}
                <div class="mis-kosong {{ $adaSaringan ? 'mis-kosong-cari' : '' }}">
                    <span class="mis-kosong-ikon" aria-hidden="true">
                        <i class="fas {{ $adaSaringan ? 'fa-search' : 'fa-tags' }}"></i>
                    </span>
                    <p class="mis-kosong-judul">
                        {{ $adaSaringan ? 'Tidak ada yang cocok' : 'Belum ada promo' }}
                    </p>
                    <p class="mis-kosong-teks">
                        @if ($adaSaringan)
                            @if ($cari !== '')
                                Tidak ada promo bernama &ldquo;<strong>{{ $cari }}</strong>&rdquo;.
                            @endif
                            Coba kata kunci lain, atau hapus saringannya.
                        @else
                            Promo memotong harga angkatan secara otomatis selama periodenya.
                            Buat satu, lalu centang angkatan mana saja yang kena.
                        @endif
                    </p>
                    @if ($adaSaringan)
                        <a href="{{ route('account.promo-layanan.index') }}"
                            class="mis-tombol mis-tombol-halus mis-kosong-aksi">
                            <i class="fas fa-times"></i> Hapus saringan
                        </a>
                    @elseif ($bolehUbah)
                        <a href="{{ route('account.promo-layanan.create') }}"
                            class="mis-tombol mis-tombol-ungu mis-kosong-aksi">
                            <i class="fas fa-plus"></i> Buat promo pertama
                        </a>
                    @endif
                </div>
            </div>
        @else
            @if ($bolehUbah)
                {{-- Bilah aksi massal. Disembunyikan sampai ada yang dicentang:
                     bilah kosong yang selalu nongkrong di atas daftar memakan
                     satu baris penuh untuk sesuatu yang belum bisa dilakukan. --}}
                <form method="POST" action="{{ route('account.promo-layanan.massal') }}"
                    class="pro-massal" id="pro-massal" hidden>
                    @csrf
                    <span class="pro-massal-jumlah">
                        <strong id="pro-massal-angka">0</strong> promo dipilih
                    </span>
                    <div class="pro-massal-tombol">
                        <button type="submit" name="tindakan" value="nyalakan" class="mis-tombol mis-tombol-hijau">
                            <i class="fas fa-toggle-on"></i> Nyalakan
                        </button>
                        <button type="submit" name="tindakan" value="matikan" class="mis-tombol mis-tombol-halus">
                            <i class="fas fa-toggle-off mis-ikon-merah"></i> Matikan
                        </button>
                    </div>
                </form>
            @endif

            {{-- mis-tabel-kartu: di bawah 768px tabelnya jadi tumpukan kartu,
                 jadi tidak perlu digeser ke samping di ponsel. --}}
            <div class="mis-tabel-bungkus">
                <table class="mis-tabel mis-tabel-kartu">
                    <thead>
                        <tr>
                            @php
                                $ariaUrut = fn ($k) => $urut === $k
                                    ? ($arah === 'asc' ? 'ascending' : 'descending')
                                    : 'none';
                            @endphp
                            @if ($bolehUbah)
                                <th class="pro-centang-sel">
                                    <label class="mis-centang-bungkus">
                                        <input type="checkbox" id="pro-centang-semua" class="mis-centang"
                                            aria-label="Pilih semua promo di halaman ini">
                                    </label>
                                </th>
                            @endif
                            <th class="pro-k-nama" aria-sort="{{ $ariaUrut('nama') }}">@include('partials.urut-kolom', ['rute' => 'account.promo-layanan.index', 'bawa' => $saringan, 'kolom' => 'nama', 'label' => 'Promo'])</th>
                            <th class="pro-k-potongan">Potongan</th>
                            <th class="pro-k-periode" aria-sort="{{ $ariaUrut('mulai') }}">@include('partials.urut-kolom', ['rute' => 'account.promo-layanan.index', 'bawa' => $saringan, 'kolom' => 'mulai', 'label' => 'Mulai'])</th>
                            {{-- Keempat pengurut yang dikenal pengendali punya
                                 kepala kolomnya sendiri. Dua di antaranya dulu
                                 hanya ada di menu ponsel, jadi di layar lebar
                                 "paling dekat selesai" tidak bisa dicapai sama
                                 sekali. --}}
                            <th class="pro-k-selesai" aria-sort="{{ $ariaUrut('selesai') }}">@include('partials.urut-kolom', ['rute' => 'account.promo-layanan.index', 'bawa' => $saringan, 'kolom' => 'selesai', 'label' => 'Selesai'])</th>
                            <th class="pro-k-angkatan">Angkatan</th>
                            <th class="pro-k-pakai">Dipakai</th>
                            {{-- Dua kolom di bawah ini HANYA ada di mode kartu.
                                 Di layar lebar isinya sudah terwakili kolom
                                 Mulai/Selesai dan Angkatan/Dipakai; di ponsel
                                 keduanya digabung supaya kartunya kembali enam
                                 baris, bukan delapan. --}}
                            <th class="mis-kolom-kartu">Periode</th>
                            <th class="mis-kolom-kartu">Cakupan</th>
                            <th class="pro-k-keadaan">Keadaan</th>
                            <th class="pro-k-aksi text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($daftar as $promo)
                            @php
                                // Dihitung sekali di awal baris, bukan di dalam
                                // salah satu sel: sel yang sama dipakai dua kali
                                // (layar lebar dan mode kartu), dan menghitungnya
                                // di dalam salah satunya berarti yang lain tidak
                                // punya nilainya sama sekali.
                                $pakai = \App\Support\DampakPromo::untuk((string) $promo->getKey());
                                $sisaKuota = $promo->kuota === null
                                    ? null
                                    : max(0, (int) $promo->kuota - $pakai['pendaftaran']);
                            @endphp
                            <tr>
                                @if ($bolehUbah)
                                    {{-- BUKAN mis-td-samar: kelas itu berarti
                                         display:none di mode kartu, dan memakainya
                                         di sini membuat seluruh aksi massal mustahil
                                         dari ponsel — kotaknya ada di markah, nol
                                         yang terlihat. --}}
                                    <td class="pro-centang-sel">
                                        <label class="mis-centang-bungkus">
                                            <input type="checkbox" class="pro-centang mis-centang"
                                                value="{{ $promo->id }}"
                                                aria-label="Pilih promo {{ $promo->nama }}">
                                        </label>
                                    </td>
                                @endif
                                <td class="mis-td-utama">
                                    {{-- Sel bertingkat dibungkus SATU anak: di mode
                                         kartu, dua anak sejajar membuat labelnya dan
                                         isinya berebut satu baris. --}}
                                    <div>
                                        {{-- Menunjuk RINCIAN, bukan borang ubah.
                                             Sebelumnya menekan nama promo langsung
                                             membuka borangnya — jadi MELIHAT promo
                                             berarti membuka layar yang setiap
                                             isiannya bisa tersenggol. --}}
                                        <p class="pro-nama">
                                            <a href="{{ route('account.promo-layanan.detail', $promo->id) }}"
                                                title="Lihat rincian promo ini">{{ $promo->nama }}</a>
                                        </p>
                                        @if ($promo->catatan)
                                            <p class="pro-ket">{{ \Illuminate\Support\Str::limit($promo->catatan, 70) }}</p>
                                        @endif
                                        <p class="pro-ket">
                                            oleh {{ $promo->penginput?->full_name ?: 'tidak tercatat' }}
                                        </p>
                                    </div>
                                </td>

                                <td data-judul="Potongan">
                                    <div>
                                        <span class="pro-potongan">{{ $promo->potongan_ringkas }}</span>
                                        <span class="pro-potongan-ket">
                                            @if ($promo->tipe === \App\PromoLayanan::PERSEN && $promo->batas_potongan)
                                                maksimal Rp {{ number_format($promo->batas_potongan, 0, ',', '.') }} per peserta
                                            @else
                                                per peserta
                                            @endif
                                        </span>
                                    </div>
                                </td>

                                <td data-judul="Mulai" class="mis-td-samar">
                                    <span class="pro-periode">{{ $promo->mulai?->locale('id')->translatedFormat('j M Y') }}</span>
                                </td>

                                <td data-judul="Selesai" class="mis-td-samar">
                                    <div>
                                        <span class="pro-periode">{{ $promo->selesai?->locale('id')->translatedFormat('j M Y') }}</span>
                                        @if ($promo->sisa_hari !== null && $promo->sisa_hari <= 7)
                                            <span class="pro-sisa">
                                                {{ $promo->sisa_hari === 0 ? 'habis hari ini' : 'sisa ' . $promo->sisa_hari . ' hari' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td data-judul="Angkatan" class="mis-td-samar">
                                    @if ($promo->angkatan_count > 0)
                                        <span class="pro-jumlah">
                                            <strong>{{ $promo->angkatan_count }}</strong> angkatan
                                        </span>
                                    @else
                                        {{-- Promo tanpa angkatan tidak memotong apa pun.
                                             Ditulis "0" saja, ia terbaca seperti angka
                                             biasa dan tidak ada yang memperbaikinya.

                                             Berupa TAUTAN ke borangnya: peringatan yang
                                             menunjuk masalah tanpa menawarkan jalan
                                             keluarnya memaksa orang mencari sendiri ke
                                             mana harus pergi. --}}
                                        <a class="pro-nihil" href="{{ route('account.promo-layanan.edit', $promo->id) }}"
                                            title="Promo ini belum memotong harga apa pun — pilih angkatannya.">
                                            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                                            belum dipilih
                                        </a>
                                    @endif
                                </td>

                                {{-- Hasilnya, dibaca dari pendaftaran yang tertali ke
                                     promo ini. Tanpa kolom ini daftar promo tidak
                                     pernah menyatakan mana yang berhasil. --}}
                                <td data-judul="Dipakai" class="mis-td-samar">
                                    @if ($pakai['pendaftaran'] > 0)
                                        <div>
                                            <span class="pro-jumlah">
                                                <strong>{{ number_format($pakai['pendaftaran']) }}</strong>
                                                @if ($promo->kuota !== null)/{{ number_format($promo->kuota) }}@endif
                                                daftar
                                            </span>
                                            <span class="pro-potongan-ket">
                                                potong Rp {{ number_format($pakai['potongan'], 0, ',', '.') }}
                                            </span>
                                            {{-- Batang kuotanya: tanpa gambar, "12/50"
                                                 harus dihitung sendiri tiap kali dibaca. --}}
                                            @if ($promo->kuota !== null)
                                                <span class="pro-batang" role="img"
                                                    aria-label="Terpakai {{ $pakai['pendaftaran'] }} dari {{ $promo->kuota }} jatah">
                                                    <span style="width: {{ $promo->kuota > 0 ? min(100, (int) round($pakai['pendaftaran'] / $promo->kuota * 100)) : 100 }}%"></span>
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="pro-ket">belum dipakai</span>
                                    @endif
                                </td>

                                {{-- ---- dua sel berikut HANYA tampil di mode kartu ---- --}}
                                <td data-judul="Periode" class="mis-kolom-kartu">
                                    <div>
                                        <span class="pro-periode">{{ $promo->periode_terbaca }}</span>
                                        @if ($promo->sisa_hari !== null && $promo->sisa_hari <= 7)
                                            <span class="pro-sisa">
                                                {{ $promo->sisa_hari === 0 ? 'habis hari ini' : 'sisa ' . $promo->sisa_hari . ' hari' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td data-judul="Cakupan" class="mis-kolom-kartu">
                                    <div>
                                        @if ($promo->angkatan_count > 0)
                                            <span class="pro-jumlah">
                                                <strong>{{ $promo->angkatan_count }}</strong> angkatan
                                            </span>
                                        @else
                                            <a class="pro-nihil" href="{{ route('account.promo-layanan.edit', $promo->id) }}"
                                                title="Promo ini belum memotong harga apa pun — pilih angkatannya.">
                                                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                                                belum dipilih
                                            </a>
                                        @endif
                                        <span class="pro-potongan-ket">
                                            @if ($pakai['pendaftaran'] > 0)
                                                dipakai {{ number_format($pakai['pendaftaran']) }}@if ($promo->kuota !== null)/{{ number_format($promo->kuota) }}@endif,
                                                potong Rp {{ number_format($pakai['potongan'], 0, ',', '.') }}
                                            @else
                                                belum dipakai
                                            @endif
                                        </span>
                                    </div>
                                </td>

                                {{-- HANYA sel ini yang berisi .mis-pil. Dua lencana
                                     dalam satu baris membuat kartunya di ponsel
                                     terbaca seperti dua status yang bertentangan. --}}
                                <td data-judul="Keadaan">
                                    <span class="mis-pil mis-pil-{{ $promo->keadaan_warna }}">{{ $promo->keadaan_terbaca }}</span>
                                </td>

                                <td data-judul="Aksi" class="text-right">
                                    <div class="pro-aksi">
                                        <a href="{{ route('account.promo-layanan.detail', $promo->id) }}"
                                            class="mis-tombol-garis" title="Lihat rincian"
                                            aria-label="Lihat rincian promo {{ $promo->nama }}">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                        </a>

                                        @if ($bolehUbah)
                                            <a href="{{ route('account.promo-layanan.edit', $promo->id) }}"
                                                class="mis-tombol-garis" title="Ubah promo"
                                                aria-label="Ubah promo {{ $promo->nama }}">
                                                <i class="fas fa-pen" aria-hidden="true"></i>
                                            </a>

                                            {{-- Menggandakan lewat POST: ia MEMBUAT baris
                                                 baru, dan alamat GET yang membuat baris
                                                 ikut terpicu pemuat pratinjau tautan. --}}
                                            <form method="POST" action="{{ route('account.promo-layanan.gandakan', $promo->id) }}">
                                                @csrf
                                                <button type="submit" class="mis-tombol-garis"
                                                    title="Gandakan promo"
                                                    aria-label="Gandakan promo {{ $promo->nama }}">
                                                    <i class="fas fa-copy" aria-hidden="true"></i>
                                                </button>
                                            </form>

                                            {{-- Saklar lewat POST: alamat GET yang mengubah
                                                 keadaan ikut terpicu pemuat pratinjau tautan. --}}
                                            <form method="POST" action="{{ route('account.promo-layanan.saklar', $promo->id) }}">
                                                @csrf
                                                <button type="submit" class="mis-tombol-garis"
                                                    title="{{ $promo->aktif ? 'Matikan promo ini' : 'Nyalakan promo ini' }}"
                                                    aria-label="{{ $promo->aktif ? 'Matikan' : 'Nyalakan' }} promo {{ $promo->nama }}">
                                                    <i class="fas {{ $promo->aktif ? 'fa-toggle-on mis-ikon-hijau' : 'fa-toggle-off' }}" aria-hidden="true"></i>
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('account.promo-layanan.hapus', $promo->id) }}"
                                                data-pro-hapus="{{ $promo->nama }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="mis-tombol-bahaya"
                                                    title="Hapus promo"
                                                    aria-label="Hapus promo {{ $promo->nama }}">
                                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="pro-ket">Tidak ada akses</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mis-halaman">
                {{ $daftar->links('vendor.pagination.bootstrap-4') }}
                <p class="mis-halaman-ket">
                    Menampilkan {{ $daftar->firstItem() }}–{{ $daftar->lastItem() }} dari {{ number_format($daftar->total()) }} promo.
                </p>
            </div>
        @endif
        </div>{{-- pro-hasil --}}

    </section>
</div>
@endsection

@push('scripts')
<script>
    /*
     * Konfirmasi hapus lewat pembungkus bersama misKonfirmasi, bukan Swal.fire
     * langsung: dialognya harus sama bentuknya di seluruh MIS.
     *
     * Dipasang pada document, bukan pada tiap borang: isi #pro-hasil DITUKAR
     * tiap ketikan oleh penyaring hidup, jadi pendengar yang dipasang ke
     * elemennya langsung ikut hilang bersama baris lamanya — tanpa galat,
     * dan tombol hapus diam-diam kembali menghapus tanpa bertanya.
     */
    /*
     * Pemilihan massal.
     *
     * Pilihan disimpan di Set, BUKAN dibaca dari kotak centang yang sedang
     * tampil: isi daftarnya ditukar tiap ketikan oleh penyaring hidup, jadi
     * membacanya dari DOM berarti pilihan ikut hilang tiap kali orang
     * mengetik satu huruf.
     */
    (function () {
        var dipilih = new Set();
        var bilah = document.getElementById('pro-massal');

        if (!bilah) {
            return;
        }

        var angka = document.getElementById('pro-massal-angka');

        /*
         * Tri-state "pilih semua" dan centang tiap baris.
         *
         * SENGAJA hanya menyentuh properti `checked`/`indeterminate`, tidak
         * satu pun struktur DOM — supaya aman dipanggil dari MutationObserver
         * di bawah. Properti bukan atribut, jadi ia tidak melahirkan catatan
         * mutasi baru.
         */
        function pasangUlang() {
            var tampak = document.querySelectorAll('.pro-centang');
            var kena = 0;

            tampak.forEach(function (k) {
                k.checked = dipilih.has(k.value);

                if (k.checked) {
                    kena++;
                }
            });

            var semua = document.getElementById('pro-centang-semua');

            if (semua) {
                semua.checked = tampak.length > 0 && kena === tampak.length;
                semua.indeterminate = kena > 0 && kena < tampak.length;
            }
        }

        /*
         * Penyegaran penuh — MENGUBAH DOM, jadi tidak boleh dipanggil dari
         * MutationObserver.
         *
         * Bilah massalnya berada DI DALAM #pro-hasil, wadah yang diamati
         * observer itu. Dipanggil dari sana, merakit ulang medan tersembunyi
         * di bawah ini melahirkan mutasi baru yang memanggilnya lagi — dan
         * seterusnya tanpa henti. Terukur: tab peramban menggantung dan skrip
         * pengukur tidak pernah memulangkan apa pun.
         */
        function segarkan() {
            angka.textContent = dipilih.size;
            bilah.hidden = dipilih.size === 0;

            // Medan tersembunyi dirakit ulang tiap kali: yang terkirim harus
            // SELURUH pilihan, termasuk yang barisnya sedang tersaring keluar.
            bilah.querySelectorAll('input[name="id[]"]').forEach(function (e) { e.remove(); });

            dipilih.forEach(function (id) {
                var m = document.createElement('input');
                m.type = 'hidden';
                m.name = 'id[]';
                m.value = id;
                bilah.appendChild(m);
            });

            pasangUlang();
        }

        // Dipasang pada document: isi daftarnya DITUKAR tiap ketikan, jadi
        // pendengar pada kotaknya langsung ikut hilang bersama baris lamanya.
        document.addEventListener('change', function (ev) {
            if (ev.target.matches('.pro-centang')) {
                ev.target.checked ? dipilih.add(ev.target.value) : dipilih.delete(ev.target.value);
                segarkan();
                return;
            }

            if (ev.target.id === 'pro-centang-semua') {
                document.querySelectorAll('.pro-centang').forEach(function (k) {
                    k.checked = ev.target.checked;
                    ev.target.checked ? dipilih.add(k.value) : dipilih.delete(k.value);
                });
                segarkan();
            }
        });

        // Sesudah daftarnya ditukar penyaring hidup, centangnya dipasang ulang
        // dari Set — kalau tidak, baris yang kembali muncul terlihat belum
        // dipilih padahal masih ikut terkirim.
        var wadah = document.getElementById('pro-hasil');

        if (wadah && window.MutationObserver) {
            new MutationObserver(pasangUlang)
                .observe(wadah, { childList: true, subtree: true });
        }

        segarkan();
    })();

    document.addEventListener('submit', function (ev) {
        var borang = ev.target.closest('form[data-pro-hapus]');

        if (!borang || borang.dataset.proSudah === '1') {
            return;
        }

        ev.preventDefault();

        misKonfirmasi({
            judul: 'Hapus promo ini?',
            // %s menandai di mana nama promonya disisipkan. Tanpa penanda
            // itu, pembungkusnya menempelkan namanya di DEPAN kalimat.
            pesan: 'Promo %s akan dihapus. Harga pendaftaran yang sudah terjadi tidak berubah — nominalnya sudah tersimpan di masing-masing pendaftaran.',
            sorot: borang.dataset.proHapus,
            tombol: 'Ya, hapus',
            jenis: 'bahaya',
        }).then(function (setuju) {
            if (setuju) {
                borang.dataset.proSudah = '1';
                borang.submit();
            }
        });
    });
</script>
@endpush
