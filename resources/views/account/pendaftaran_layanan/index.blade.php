@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Pendaftar Layanan | MIS Rumah Scopus
@stop

{{--
  Gaya khusus layar ini lewat @push('gaya'), WAJIB — <style> di badan berkas
  terbit sebelum CSS Bootstrap, sehingga aturan berbobot sama selalu kalah.
  Token, kartu, tombol, pil, dan tabelnya datang dari mis-ui.css; yang di sini
  hanya yang memang khas layar ini.
--}}
@push('gaya')
    <style>
        /* Ubin layanan di kolom pertama: medali kecil + namanya, tidak
           terpatah walau namanya dua kata. */
        .pdl-layanan {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }

        .pdl-layanan-nama {
            margin: 0;
            font-size: .82rem;
            font-weight: 600;
            color: var(--mis-tinta);
            line-height: 1.3;
        }

        .pdl-orang {
            min-width: 0;
        }

        .pdl-nama {
            margin: 0;
            font-size: .86rem;
            font-weight: 600;
            color: var(--mis-tinta);
            line-height: 1.35;
        }

        .pdl-kontak {
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin-top: 3px;
            font-size: .76rem;
            color: var(--mis-tinta-3);
        }

        .pdl-kontak a {
            color: var(--mis-tinta-2);
            text-decoration: none;
        }

        .pdl-kontak a:hover {
            color: var(--mis-tinta);
            text-decoration: underline;
        }

        .pdl-afiliasi {
            margin: 3px 0 0;
            font-size: .74rem;
            color: var(--mis-tinta-4);
            overflow-wrap: anywhere;
        }

        /*
         * Alamat email WAJIB boleh dipatah di mana saja.
         *
         * Alamat adalah satu kata tanpa spasi, jadi lebar min-content-nya
         * sama dengan panjang penuhnya — dan anak flex tidak pernah menyusut
         * di bawah min-content-nya. Terukur di 320px:
         * "trianggategarutama@gmail.com" menuntut 187px, dan bersama label
         * kartunya membuat sel Pendaftar jadi 294px sementara sel lain 232px.
         * Kartunya meluber 48px dan terpotong, sebab pembungkusnya
         * overflow:hidden.
         */
        .pdl-kontak a,
        .pdl-kontak span,
        .pdl-nama {
            overflow-wrap: anywhere;
        }

        .pdl-nomor {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .74rem;
            color: var(--mis-tinta-3);
        }

        /* Uang dirapatkan ke kanan dan tidak dibiarkan terpatah: angka
           berpindah baris di tengahnya terbaca sebagai dua angka. */
        .pdl-uang {
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
            font-weight: 600;
            color: var(--mis-tinta);
        }

        /* Nominalnya TIDAK boleh terpatah — angka yang berpindah baris di
           tengahnya terbaca sebagai dua angka. Keterangan di bawahnya justru
           harus boleh terpatah: ia kalimat, dan "potongan Rp 225.000 ·
           SalamQ1" menuntut 175px sementara selnya di 320px tinggal 46px.
           Itulah yang meluberkan tabelnya 234px. */
        .pdl-uang-ket {
            display: block;
            margin-top: 2px;
            font-size: .72rem;
            font-weight: 400;
            color: var(--mis-tinta-4);
        }

        /* Pembungkus satu-anak untuk sel yang isinya bertingkat. Di mode
           kartu selnya flex, jadi tanpa pembungkus tiap bagian jadi anak
           flex sendiri dan berbaris mendatar alih-alih menumpuk. */
        .pdl-uang-blok,
        .pdl-keadaan-blok {
            display: inline-block;
            min-width: 0;
        }

        .pdl-sesi {
            font-size: .8rem;
            color: var(--mis-tinta-2);
            line-height: 1.35;
        }

        /* Jumlah orang: angka tunggal, dipusatkan, dan rombongan ditandai
           supaya terbaca sekali lihat. Terukur ada baris berisi 5, 6, dan 13
           orang di antara 187 baris. */
        .pdl-jumlah {
            font-variant-numeric: tabular-nums;
            font-weight: 600;
        }

        /* Garis bawah bertitik, bukan warna: keterangan status aslinya ada di
           title, dan tanpa penanda apa pun tidak ada yang tahu bisa ditunjuk. */
        .pdl-status-asli {
            border-bottom: 1px dotted var(--mis-tinta-4);
            cursor: help;
        }

        .pdl-bukti {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .76rem;
            text-decoration: none;
        }

        .pdl-bukti i {
            font-size: inherit;
        }

        /* Dua keadaan bukti yang bukan tautan. Warnanya membedakan "memang
           belum diunggah" dari "sudah diunggah tetapi berkasnya lenyap" —
           terukur 72 dari 183 nilai bukti menunjuk berkas yang sudah tidak
           ada, dan keduanya menuntut tindakan yang berbeda. */
        .pdl-bukti-nihil {
            color: var(--mis-tinta-4);
        }

        .pdl-bukti-hilang {
            color: #92400e;
            cursor: help;
        }

        /* Jumlah uang di kepala daftar. Bukan ubin saringan: ia tidak bisa
           jadi tautan ke mana pun, dan panduan menuntut tiap ubin ringkasan
           berupa saringan yang bisa ditekan. */
        .pdl-uang-total {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 4px 10px;
            margin: 0 0 var(--mis-jarak);
            padding: 11px 15px;
            border: 1px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: var(--mis-kartu);
            font-size: .82rem;
            color: var(--mis-tinta-2);
        }

        .pdl-uang-total strong {
            font-variant-numeric: tabular-nums;
            color: var(--mis-tinta);
        }

        /* Ikon glifnya WAJIB mewarisi ukuran: layout memasang
           .fas { font-size: 20px } untuk seluruh halaman, dan aturan itu
           menang atas pewarisan — jadi mengecilkan pembungkusnya saja tidak
           pernah mengubah ikonnya. */
        .pdl-layanan .mis-medali i,
        .pdl-bukti i,
        .pdl-kontak i {
            font-size: inherit;
            width: 100%;
            text-align: center;
        }

        .pdl-kontak i,
        .pdl-bukti i {
            width: auto;
        }

        /* Di mode kartu, kolom layanan jadi judul barisnya. */
        @media (max-width: 767.98px) {
            /* Sebaris supaya kartunya tidak memanjang, tetapi TETAP boleh
               terpatah: dipaksa sebaris tanpa itu, kalimat potongan diskon
               mendorong selnya melewati tepi kartu. */
            .pdl-uang-ket {
                display: inline;
                margin-left: 6px;
            }

            /* Keterangan status aslinya ikut boleh terpatah; di 320px
               "Pendaftaran Dibatalkan" menuntut 78px dalam sel yang
               tersisa 46px. */
            .pdl-status-asli {
                overflow-wrap: anywhere;
            }
        }

        /*
         * Layar tersempit: sel keadaan mendapat barisnya SENDIRI.
         *
         * mis-tabel-kartu menaikkan sel berpil ke baris kaki kartu bersama
         * sel aksinya, dan keduanya berbagi lebar lewat `flex: 1 1 0`.
         * Terukur di 320px sel keadaan tinggal 101px sementara lencana
         * "Menunggu bayar" sendiri menuntut 125px dan tidak boleh terpatah —
         * jadi kartunya meluber 48px dan terpotong.
         *
         * Diberi lebar penuh, ia turun ke barisnya sendiri dan mendapat 232px.
         * Ambang 359.98px memang disediakan panduan untuk penyesuaian terakhir
         * semacam ini, jadi bukan titik putus baru.
         */
        @media (max-width: 359.98px) {
            /* Pemilihnya menyebut .mis-tabel.mis-tabel-kartu juga, bukan
               .pdl-tabel saja: aturan di mis-ui.css berbobot (0,3,2) dan
               pemilih (0,2,2) kalah walau ditulis belakangan. Versi pertama
               aturan ini tidak mengubah apa pun justru karena itu. */
            .mis-tabel.mis-tabel-kartu.pdl-tabel tbody td:has(.mis-pil) {
                flex: 1 1 100%;
            }
        }
    </style>
@endpush

@section('content')
@php
    /*
     * Dirakit di sini sekali, bukan diulang di tiap tautan.
     *
     * $bawa ikut dibawa oleh kepala kolom yang bisa diurutkan dan oleh
     * penomoran halaman; tanpa itu, mengurutkan diam-diam menghapus
     * saringannya. Dan $lingkup dipakai ubin ringkasan supaya menekan satu
     * keadaan tidak melepaskan pilihan layanannya.
     *
     * Semuanya blok @php, tidak ada @php(...) sebaris di berkas ini: Blade
     * memproses blok lebih dulu, dan penanda sebaris di atas sebuah blok ikut
     * dianggap pembukanya sehingga seluruh markah di antaranya tertelan.
     */
    $bawa = request()->only('cari', 'layanan', 'keadaan', 'bukti', 'angkatan');
    $lingkup = array_filter(request()->only('cari', 'layanan', 'angkatan'));
    $namaAngkatan = $angkatan !== ''
        ? (\App\Support\PendaftaranSemuaLayanan::namaAngkatan()[$angkatan] ?? null)
        : null;
    $rute = 'account.pendaftaran-layanan.index';

    $ariaUrut = function ($kolom) use ($urut, $arah) {
        if ($urut !== $kolom) {
            return 'none';
        }

        return $arah === 'asc' ? 'ascending' : 'descending';
    };

    $pilihanUrut = [
        ['waktu', 'turun', 'Terbaru mendaftar'],
        ['waktu', 'naik', 'Terlama mendaftar'],
        ['nama', 'naik', 'Nama A–Z'],
        ['total', 'turun', 'Bayar terbesar'],
        ['layanan', 'naik', 'Dikelompokkan per layanan'],
        ['status', 'naik', 'Dikelompokkan per status'],
    ];
    $arahSekarang = $arah === 'asc' ? 'naik' : 'turun';
@endphp
<div class="main-content mis-badan">
    <section class="section">

        {{-- ------------------------------------------------ kepala --}}
        <div class="mis-kepala">
            <span class="mis-medali mis-ungu" aria-hidden="true"><i class="fas fa-clipboard-list"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Pendaftar Layanan</h1>
                <p class="mis-sub">
                    Semua yang mendaftar layanan jasa Rumah Scopus, dari lima layanan sekaligus.
                </p>
            </div>
            <div class="mis-kepala-aksi mis-kepala-aksi-pasangan">
                {{-- Unduhan membawa saringan yang sedang dipakai, bukan seluruh
                     tabel: yang diunduh orang hampir selalu yang dilihatnya.
                     Dua bentuk, bukan satu — PDF untuk dibaca dan dilampirkan,
                     lembar kerja untuk diolah jadi daftar hadir dan sertifikat. --}}
                <a class="mis-tombol mis-tombol-halus"
                    href="{{ route('account.pendaftaran-layanan.excel', $bawa + request()->only('urut', 'arah')) }}">
                    <i class="fas fa-file-excel mis-ikon-hijau"></i> Unduh Excel
                </a>
                <a class="mis-tombol mis-tombol-halus"
                    href="{{ route('account.pendaftaran-layanan.pdf', $bawa + request()->only('urut', 'arah')) }}">
                    <i class="fas fa-file-pdf mis-ikon-merah"></i> Unduh PDF
                </a>
            </div>
        </div>

        {{-- ---------------------------------------------- ringkasan --}}
        {{-- Lima angka yang paling sering ditanyakan, dihitung dari SELURUH
             baris pada lingkup layanan yang dipilih — bukan dari halaman yang
             sedang tampil.

             Tiap ubin sekaligus pintasan saringan, berupa tautan dan bukan
             tombol berskrip: alamatnya bisa disalin dan tetap bekerja tanpa
             JavaScript. Angka yang menarik perhatian selalu memancing "yang
             mana saja?", dan tanpa itu pertanyaannya tidak terjawab. --}}
        <div class="mis-ringkas-geser" data-mis-geser>
        <div class="mis-ringkas mis-ringkas-5" aria-label="Ringkasan pendaftar">
            <a class="mis-ubin {{ $keadaanDipilih === '' && $bukti === '' ? 'terpilih' : '' }}"
                href="{{ route($rute, $lingkup) }}"
                title="Tampilkan semua pendaftaran">
                <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-clipboard-list"></i></span>
                <div>
                    <p class="mis-ubin-angka">{{ number_format($ringkasan['semua'], 0, ',', '.') }}</p>
                    <p class="mis-ubin-label">Seluruh pendaftaran</p>
                </div>
            </a>

            {{-- Yang paling menuntut tindakan ditaruh kedua, bukan di ujung:
                 bukti transfernya sudah diunggah tetapi statusnya masih
                 menunggu, jadi ada orang yang sedang menunggu dicek. --}}
            <a class="mis-ubin {{ $bukti === 'ada' && $keadaanDipilih === 'menunggu' ? 'terpilih' : '' }}"
                href="{{ route($rute, $lingkup + ['keadaan' => 'menunggu', 'bukti' => 'ada']) }}"
                title="Saring: sudah unggah bukti tapi belum ditandai lunas">
                <span class="mis-medali kecil mis-merah" aria-hidden="true"><i class="fas fa-receipt"></i></span>
                <div>
                    <p class="mis-ubin-angka">{{ number_format($ringkasan['perlu_diperiksa'], 0, ',', '.') }}</p>
                    <p class="mis-ubin-label">Bukti perlu diperiksa</p>
                </div>
            </a>

            <a class="mis-ubin {{ $keadaanDipilih === 'menunggu' && $bukti === '' ? 'terpilih' : '' }}"
                href="{{ route($rute, $lingkup + ['keadaan' => 'menunggu']) }}"
                title="Saring: hanya yang belum bayar">
                <span class="mis-medali kecil mis-kuning" aria-hidden="true"><i class="fas fa-clock"></i></span>
                <div>
                    <p class="mis-ubin-angka">{{ number_format($ringkasan['menunggu'], 0, ',', '.') }}</p>
                    <p class="mis-ubin-label">Menunggu bayar</p>
                </div>
            </a>

            <a class="mis-ubin {{ $keadaanDipilih === 'lunas' ? 'terpilih' : '' }}"
                href="{{ route($rute, $lingkup + ['keadaan' => 'lunas']) }}"
                title="Saring: hanya yang sudah lunas">
                <span class="mis-medali kecil mis-hijau" aria-hidden="true"><i class="fas fa-check-circle"></i></span>
                <div>
                    <p class="mis-ubin-angka">{{ number_format($ringkasan['lunas'], 0, ',', '.') }}</p>
                    <p class="mis-ubin-label">Lunas</p>
                </div>
            </a>

            <a class="mis-ubin {{ $keadaanDipilih === 'tidak_jadi' ? 'terpilih' : '' }}"
                href="{{ route($rute, $lingkup + ['keadaan' => 'tidak_jadi']) }}"
                title="Saring: dibatalkan, ditolak, atau kedaluwarsa">
                <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-times-circle"></i></span>
                <div>
                    <p class="mis-ubin-angka">{{ number_format($ringkasan['tidak_jadi'], 0, ',', '.') }}</p>
                    <p class="mis-ubin-label">Tidak jadi</p>
                </div>
            </a>
        </div>
            <p class="mis-ringkas-petunjuk" aria-hidden="true">
                <i class="fas fa-arrows-alt-h"></i> Geser untuk lihat semua
            </p>
        </div>

        {{-- Jumlah orang dan uangnya di luar ubin: keduanya tidak bisa jadi
             saringan, dan panduan menuntut tiap ubin ringkasan berupa tautan
             yang menyaring. Ditaruh sebaris supaya tetap terbaca. --}}
        <p class="pdl-uang-total">
            <span>
                <i class="fas fa-users mis-ikon-ungu" aria-hidden="true"></i>
                Jumlah orang <strong>{{ number_format($ringkasan['orang'], 0, ',', '.') }}</strong>
            </span>
            <span>
                <i class="fas fa-wallet mis-ikon-hijau" aria-hidden="true"></i>
                Uang masuk dari yang lunas <strong>Rp {{ number_format($ringkasan['uang_lunas'], 0, ',', '.') }}</strong>
            </span>
            @if ($ringkasan['lain'] > 0)
                {{-- Hanya muncul kalau memang ada. Status di kelima tabel
                     berupa varchar bebas, jadi nilai baru bisa muncul kapan
                     saja tanpa migrasi — dan baris bernilai baru harus tetap
                     bisa ditemukan, bukan hilang dari semua saringan. --}}
                <a href="{{ route($rute, $lingkup + ['keadaan' => 'lain']) }}">
                    <i class="fas fa-exclamation-triangle mis-ikon-kuning" aria-hidden="true"></i>
                    {{ $ringkasan['lain'] }} status belum dikenali
                </a>
            @endif
        </p>

        @if ($angkatan !== '')
            <p class="pdl-uang-total">
                <span>
                    <i class="fas fa-layer-group mis-ikon-ungu" aria-hidden="true"></i>
                    Disaring ke angkatan <strong>{{ $namaAngkatan ?? $angkatan }}</strong>
                </span>
                <a href="{{ route($rute, array_filter(request()->only('cari', 'layanan', 'keadaan', 'bukti'))) }}">
                    <i class="fas fa-times" aria-hidden="true"></i> Tampilkan semua angkatan
                </a>
            </p>
        @endif

        {{-- ---------------------------------------------- penyaring --}}
        {{-- <details> membungkusnya: di ponsel empat kendali yang selalu
             terbuka memakan satu layar penuh sebelum baris pertama kelihatan.
             Di layar lebar ia dipaksa terbuka oleh mis-ui.js dan ringkasannya
             disembunyikan, jadi tampak seperti baris penyaring biasa. --}}
        <details class="mis-lipat" id="pdl-penyaring" data-mis-lipat>
            <summary>
                <i class="fas fa-sliders-h mis-ikon-ungu" aria-hidden="true"></i>
                Cari &amp; saring
                @if ($adaSaringan)
                    <span class="mis-pil mis-pil-ungu">aktif</span>
                @endif
            </summary>

        <div class="mis-saring-kartu">
        <form method="GET" action="{{ route($rute) }}" class="mis-saring" id="pdl-borang" data-mis-saring="pdl-hasil">
            {{-- Urutan ikut terbawa saat menyaring; tanpa ini, menekan tombol
                 terapkan diam-diam mengembalikan urutannya ke bawaan. --}}
            <input type="hidden" name="urut" value="{{ $urut }}">
            <input type="hidden" name="arah" value="{{ $arahSekarang }}">

            <div class="mis-isian mis-saring-cari">
                <label class="mis-label" for="pdl-cari">Cari</label>
                <input type="search" class="form-control-modern" id="pdl-cari" name="cari" data-mis-cari
                    value="{{ $cari }}" placeholder="Nama, email, nomor WA, nomor pendaftaran, atau afiliasi"
                    autocomplete="off" aria-controls="pdl-hasil">
                {{-- Tombol hapus ketikan, type=button supaya tidak ikut
                     mengirim formulir, dan disembunyikan saat kotaknya kosong. --}}
                <button type="button" class="mis-saring-hapus" id="pdl-hapus" data-mis-kosongkan
                    aria-label="Hapus kata kunci pencarian" title="Hapus kata kunci"
                    @if ($cari === '') hidden @endif>
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
                <span class="mis-saring-sibuk" id="pdl-sibuk" aria-hidden="true"></span>
            </div>

            <div class="mis-isian mis-saring-pilih">
                <label class="mis-label" for="pdl-layanan">Layanan</label>
                <select class="form-control-modern" id="pdl-layanan" name="layanan">
                    <option value="">Semua layanan</option>
                    @foreach ($katalog as $kunci => $l)
                        <option value="{{ $kunci }}" @selected($layanan === $kunci)>{{ $l['nama'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mis-isian mis-saring-pilih">
                <label class="mis-label" for="pdl-keadaan">Keadaan</label>
                <select class="form-control-modern" id="pdl-keadaan" name="keadaan">
                    <option value="">Semua keadaan</option>
                    @foreach ($keadaan as $kunci => $k)
                        <option value="{{ $kunci }}" @selected($keadaanDipilih === $kunci)>{{ $k['label'] }}</option>
                    @endforeach
                    @if ($ringkasan['lain'] > 0)
                        <option value="lain" @selected($keadaanDipilih === 'lain')>Status belum dikenali</option>
                    @endif
                </select>
            </div>

            <div class="mis-isian mis-saring-pilih">
                <label class="mis-label" for="pdl-bukti">Bukti bayar</label>
                <select class="form-control-modern" id="pdl-bukti" name="bukti">
                    <option value="">Semua</option>
                    <option value="ada" @selected($bukti === 'ada')>Sudah diunggah</option>
                    <option value="belum" @selected($bukti === 'belum')>Belum diunggah</option>
                </select>
            </div>

            {{-- Saringan angkatan tidak punya menunya sendiri: ia datang dari
                 tautan di layar Angkatan Layanan, bukan dari borang ini.
                 Tetapi ia WAJIB terlihat dan bisa dilepas — saringan yang
                 bekerja tanpa terlihat membuat orang menyimpulkan datanya
                 yang kurang. Dibawa juga sebagai isian tersembunyi supaya
                 tidak hilang saat penyaring lain diterapkan. --}}
            @if ($angkatan !== '')
                <input type="hidden" name="angkatan" value="{{ $angkatan }}">
            @endif

            {{-- Pengurut KHUSUS ponsel. Kepala kolom yang bisa diurutkan ada di
                 dalam <thead>, dan di mode kartu <thead> disembunyikan untuk
                 pembaca layar saja — terukur berukuran 1x1 dan terklip. Tanpa
                 menu ini kelima kolom yang bisa diurutkan tidak bisa dijangkau
                 sama sekali dari ponsel. --}}
            <div class="mis-isian mis-saring-pilih mis-urut-ponsel">
                <label class="mis-label" for="pdl-urut-pilih">Urutkan</label>
                <select class="form-control-modern" id="pdl-urut-pilih" name="urutgabung" data-mis-urut-ponsel>
                    @foreach ($pilihanUrut as $pilihan)
                        <option value="{{ $pilihan[0] }}|{{ $pilihan[1] }}"
                            @selected($urut === $pilihan[0] && $arahSekarang === $pilihan[1])>{{ $pilihan[2] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tombolnya tetap ada di markah dan baru disembunyikan oleh
                 mis-ui.js. Tanpa JavaScript penyaringnya masih bisa dipakai
                 seperti formulir biasa. --}}
            <button type="submit" class="mis-tombol mis-tombol-ungu" id="pdl-terapkan" data-mis-terapkan>
                <i class="fas fa-search"></i> Terapkan
            </button>

            @if ($adaSaringan)
                <a href="{{ route($rute) }}" class="mis-tombol mis-tombol-halus" title="Hapus semua saringan">
                    <i class="fas fa-times"></i> Reset
                </a>
            @endif
        </form>
        </div>
        </details>

        {{-- --------------------------------------------------- daftar --}}
        {{-- Dibungkus dan diberi id: hanya bagian inilah yang ditukar saat
             mengetik, jadi kepala, ringkasan, dan kotak pencariannya tidak
             ikut digambar ulang — dan fokus ketikan tidak hilang.

             role=status + aria-live: isinya ditukar diam-diam tiap ketikan, dan
             tanpa penanda ini pembaca layar tidak mengumumkan apa pun. --}}
        <div class="mis-hasil" id="pdl-hasil" role="status" aria-live="polite" aria-atomic="false">
        @if ($baris->isEmpty())
            <div class="mis-bagian">
                {{-- Dua keadaan yang terasa sama di layar padahal jalan
                     keluarnya berbeda: yang satu ganti kata kunci, yang lain
                     tunggu ada yang mendaftar. Hanya yang pertama dapat ikon
                     bergerak. --}}
                <div class="mis-kosong {{ $adaSaringan ? 'mis-kosong-cari' : '' }}">
                    <span class="mis-kosong-ikon" aria-hidden="true">
                        <i class="fas {{ $adaSaringan ? 'fa-search' : 'fa-clipboard-list' }}"></i>
                    </span>
                    <p class="mis-kosong-judul">
                        {{ $adaSaringan ? 'Tidak ada yang cocok' : 'Belum ada pendaftar' }}
                    </p>
                    <p class="mis-kosong-teks">
                        @if ($adaSaringan)
                            @if ($cari !== '')
                                Tidak ada pendaftar yang cocok dengan &ldquo;<strong>{{ $cari }}</strong>&rdquo;.
                            @endif
                            Coba kata kunci lain, atau hapus saringannya.
                        @else
                            Pendaftar muncul di sini begitu ada yang mendaftar lewat halaman layanan.
                        @endif
                    </p>
                    @if ($adaSaringan)
                        <a href="{{ route($rute) }}" class="mis-tombol mis-tombol-halus mis-kosong-aksi">
                            <i class="fas fa-times"></i> Hapus saringan
                        </a>
                    @endif
                </div>
            </div>
        @else
            {{-- mis-tabel-kartu: di bawah 768px tabelnya berubah jadi tumpukan
                 kartu, jadi tidak perlu digeser ke samping di ponsel. Stisla
                 memaksa min-width 800px pada tabel di dalam .table-responsive,
                 dan kelas inilah yang menimpanya. --}}
            <div class="mis-tabel-bungkus">
                <table class="mis-tabel mis-tabel-kartu pdl-tabel">
                    <thead>
                        <tr>
                            <th aria-sort="{{ $ariaUrut('layanan') }}">
                                @include('partials.urut-kolom', ['rute' => $rute, 'bawa' => $bawa, 'kolom' => 'layanan', 'label' => 'Layanan'])
                            </th>
                            <th aria-sort="{{ $ariaUrut('nama') }}">
                                @include('partials.urut-kolom', ['rute' => $rute, 'bawa' => $bawa, 'kolom' => 'nama', 'label' => 'Pendaftar'])
                            </th>
                            <th>Sesi</th>
                            <th class="text-center">Orang</th>
                            <th class="text-right" aria-sort="{{ $ariaUrut('total') }}">
                                @include('partials.urut-kolom', ['rute' => $rute, 'bawa' => $bawa, 'kolom' => 'total', 'label' => 'Total bayar'])
                            </th>
                            <th>Bukti</th>
                            <th aria-sort="{{ $ariaUrut('status') }}">
                                @include('partials.urut-kolom', ['rute' => $rute, 'bawa' => $bawa, 'kolom' => 'status', 'label' => 'Keadaan'])
                            </th>
                            <th aria-sort="{{ $ariaUrut('waktu') }}">
                                @include('partials.urut-kolom', ['rute' => $rute, 'bawa' => $bawa, 'kolom' => 'waktu', 'label' => 'Daftar'])
                            </th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($baris as $b)
                            @include('account.pendaftaran_layanan.baris', ['b' => $b, 'katalog' => $katalog])
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $baris->links('vendor.pagination.bootstrap-4') }}
        @endif
        </div>

    </section>
</div>
@endsection
