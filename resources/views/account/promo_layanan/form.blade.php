@extends('layouts.account')
@extends('layouts.loader')

@section('title')
{{ $isEdit ? 'Ubah Promo' : 'Promo Baru' }} | MIS
@stop

@include('partials.toast-flash')

@push('gaya')
<style>
    /*
     * Bahasa rupanya mengikuti docs/panduan-ui-mis.md. Yang ditulis di sini
     * hanya yang khas borang ini.
     */

    /* Penanda wajib. Merah dan kelihatan tanpa hover — hanya dipasang pada
       medan yang validatornya memang required, supaya tandanya tetap berarti. */
    .prf-wajib { color: #e11d48; font-weight: 800; }

    /*
     * Kartunya menurun satu lajur, bukan dua berdampingan.
     *
     * Borang ini empat langkah yang harus dibaca BERURUTAN — "promo apa",
     * "potongannya berapa", "kapan", "untuk siapa" — dan dua lajur membuat
     * langkah ketiga duduk di sebelah langkah pertama. Yang memakainya bukan
     * orang yang hafal borangnya.
     */
    .prf-tumpuk { display: grid; gap: var(--mis-jarak); max-width: 880px; }

    /* Nomor langkah di kepala kartu, supaya urutannya tidak bisa salah baca. */
    .prf-langkah { display: flex; align-items: center; gap: 9px; margin: 0 0 4px; }
    .prf-nomor {
        flex: 0 0 auto;
        display: grid; place-items: center;
        width: 26px; height: 26px;
        border-radius: 9px;
        background: #eef2ff;
        line-height: 1;
        font-size: .78rem; font-weight: 800; color: #4338ca;
    }
    .prf-langkah-judul { margin: 0; line-height: 1.3; font-size: .88rem; font-weight: 800; color: var(--mis-tinta); }
    .prf-langkah-sub { margin: 0 0 14px 35px; line-height: 1.5; font-size: .76rem; color: var(--mis-tinta-3); }

    .prf-kisi { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px; align-content: start; }

    /* .mis-isian memasang align-self: start, jadi di susunan menurun tiap
       isian selebar isinya dan tepinya ragged. */
    .prf-kisi > .mis-isian { align-self: stretch; }
    .prf-penuh { grid-column: 1 / -1; }

    /* ------------------------------------------------------- pilihan tipe */

    /*
     * Dua kartu pilihan, bukan menu <select>.
     *
     * Tipe promo menentukan kotak mana yang muncul di bawahnya, dan menu
     * yang tertutup menyembunyikan akibat itu sampai sesudah dipilih. Dua
     * kartu berdampingan memperlihatkan kedua kemungkinan sekaligus.
     */
    .prf-tipe { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 10px; margin-bottom: 14px; }

    .prf-tipe label {
        display: flex; align-items: flex-start; gap: 10px;
        margin: 0; padding: 12px 13px;
        border: 1px solid var(--mis-garis); border-radius: var(--mis-radius-kecil);
        background: #fff;
        cursor: pointer;
        transition: border-color .15s, background .15s, box-shadow .15s;
    }

    .prf-tipe label:hover { border-color: #c7d2fe; background: #fafaff; }

    /* :has, bukan kelas yang dipasang skrip: tanpa JavaScript pilihannya
       masih terlihat terpilih. */
    .prf-tipe label:has(input:checked) {
        border-color: #6366f1;
        background: #eef2ff;
        box-shadow: 0 0 0 1px #6366f1 inset;
    }

    .prf-tipe input { margin: 2px 0 0; flex: 0 0 auto; }
    .prf-tipe-judul { display: block; line-height: 1.3; font-size: .83rem; font-weight: 700; color: var(--mis-tinta); }
    .prf-tipe-ket { display: block; margin-top: 2px; line-height: 1.45; font-size: .73rem; color: var(--mis-tinta-3); }

    /* ---------------------------------------------------------- pratinjau */

    /*
     * Pratinjau hitungan, bukan rumus.
     *
     * "40%" tidak menjawab pertanyaan yang sebenarnya ada di kepala admin:
     * "jadi berapa?". Angkanya dihitung ulang tiap ketikan dan diberi
     * aria-live supaya ikut terbaca pembaca layar.
     */
    .prf-pratinjau {
        display: flex; flex-wrap: wrap; align-items: baseline; gap: 6px 10px;
        padding: 11px 13px; margin-top: 12px;
        border: 1px solid #bbf7d0; border-radius: 13px;
        background: #f0fdf4;
        line-height: 1.5; font-size: .8rem; color: #166534;
    }

    .prf-pratinjau.kosong { border-color: #fde68a; background: #fffbeb; color: #92400e; }
    .prf-pratinjau strong { font-size: .95rem; font-weight: 800; }
    .prf-coret { text-decoration: line-through; opacity: .7; }

    /* ---------------------------------------------------------- angkatan */

    .prf-alat { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 10px; margin-bottom: 12px; }
    .prf-alat > .mis-isian { flex: 1 1 220px; min-width: 0; align-self: stretch; }

    /* Penghitung terpilih. Berupa role=status: isinya berubah tanpa halaman
       dimuat ulang, dan tanpa penanda itu tidak ada yang mengumumkannya. */
    .prf-hitung {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        background: #eef2ff;
        font-size: .78rem; font-weight: 700; color: #4338ca;
        white-space: nowrap;
    }

    .prf-hitung.nihil { background: #fffbeb; color: #92400e; }

    .prf-kelompok { border: 1px solid var(--mis-garis); border-radius: var(--mis-radius-kecil); margin-bottom: 10px; overflow: hidden; }
    .prf-kelompok[hidden] { display: none; }

    .prf-kelompok-kepala {
        display: flex; align-items: center; gap: 9px;
        padding: 10px 12px;
        border-bottom: 1px solid var(--mis-garis);
        background: #f8fafc;
    }

    .prf-kelompok-nama { margin: 0; line-height: 1.3; font-size: .82rem; font-weight: 800; color: var(--mis-tinta); }
    .prf-kelompok-jumlah { margin: 0; line-height: 1.4; font-size: .72rem; color: var(--mis-tinta-3); }

    /* Pilih semua milik satu layanan. Di kanan, dan labelnya menyebut
       layanannya — "Pilih semua" saja tidak menyatakan sejauh mana. */
    .prf-semua { margin-left: auto; display: inline-flex; align-items: center; gap: 7px; font-size: .75rem; font-weight: 700; color: #4338ca; cursor: pointer; }

    /*
     * Daftar angkatannya menggulung SENDIRI, bukan halamannya.
     *
     * 230 angkatan Scopus Camp dalam satu kartu membuat tombol Simpan di
     * kaki borang berada belasan layar di bawah — dan admin yang mencentang
     * tiga angkatan harus menggulung melewati sisanya untuk menyimpannya.
     */
    .prf-baris-bungkus { max-height: 320px; overflow-y: auto; }

    .prf-baris {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        column-gap: 10px; align-items: center;
        padding: 9px 12px;
        border-top: 1px solid #f1f5f9;
        cursor: pointer;
        margin: 0;
    }

    .prf-baris:first-child { border-top: 0; }
    .prf-baris:hover { background: #fafaff; }
    .prf-baris[hidden] { display: none; }
    .prf-baris:has(input:checked) { background: #f5f3ff; }

    .prf-baris-teks { min-width: 0; }
    .prf-baris-nama { display: block; line-height: 1.35; font-size: .8rem; font-weight: 700; color: var(--mis-tinta); overflow-wrap: anywhere; }
    .prf-baris-ket { display: block; margin-top: 1px; line-height: 1.4; font-size: .71rem; color: var(--mis-tinta-3); }

    /* Harga sesudah promo, dihitung skrip. Nominalnya tidak boleh terpatah —
       "Rp 3.900.000" yang jadi dua baris terbaca seperti dua angka. */
    .prf-baris-harga { text-align: right; line-height: 1.3; font-size: .76rem; white-space: nowrap; }
    .prf-harga-lama { display: block; color: var(--mis-tinta-4); text-decoration: line-through; }
    .prf-harga-baru { display: block; font-size: .84rem; font-weight: 800; color: #15803d; }
    .prf-harga-nihil { display: block; font-size: .73rem; color: var(--mis-tinta-4); }

    /* Angkatan yang tanggal mulainya sudah lewat. Ditandai, bukan dibuang:
       promo lama yang sudah mencentangnya harus tetap bisa dibuka. */
    .prf-lewat { display: inline-block; margin-left: 5px; padding: 1px 7px; border-radius: 999px; background: #f1f5f9; font-size: .66rem; font-weight: 700; color: #64748b; }

    /*
     * Kotak centang di dalam kisi isian dirapatkan ke KIRI.
     *
     * `.mis-centang-bungkus` bersama memasang `justify-content: center`, dan
     * di dalam `.mis-isian` yang melar labelnya ikut selebar barisnya —
     * terukur 350px pada kolom 328px, jadi "Promo ini menyala" duduk di
     * tengah kartu sementara seluruh label lain rata kiri. Bobotnya (0,3,0)
     * supaya menang atas (0,1,0) milik kelas bersamanya.
     *
     * Lebarnya ikut dibatasi isinya: tanpa itu daerah tekannya selebar kartu,
     * dan menekan ruang kosong di ujung kanan ikut menyalakan centangnya.
     */
    .prf-kisi > .mis-isian > .mis-centang-bungkus {
        justify-content: flex-start;
        align-self: start;
        width: fit-content;
    }

    .prf-kaki { display: flex; flex-wrap: wrap; gap: 10px; margin-top: var(--mis-jarak); }

    /* Tombol hapus didorong ke ujung lain: ia tidak boleh bersebelahan
       dengan Simpan. */
    .prf-kaki .mis-tombol-hapus { margin-left: auto; }

    @media (max-width: 575.98px) {
        .prf-langkah-sub { margin-left: 0; }

        /*
         * Penghitung terpilih melebar penuh dan rata tengah.
         *
         * Di layar lebar ia didorong ke kanan oleh margin-left: auto dan duduk
         * sebaris dengan kotak carinya. Di 390px kotak carinya sudah memakan
         * satu baris penuh, jadi lencana yang tetap terdorong ke kanan
         * menyisakan lajur kosong di kirinya dan terbaca seperti tersangkut.
         */
        .prf-hitung {
            width: 100%;
            margin: 0 0 8px !important;
            justify-content: center;
        }

        /* Di ponsel tombol hapus turun ke barisnya sendiri dan rata kiri —
           didorong ke kanan, ia duduk persis di bawah Simpan. */
        .prf-kaki .mis-tombol-hapus { margin-left: 0; width: 100%; justify-content: center; }
    }
</style>
@endpush

@section('content')
<div class="main-content mis-badan">
    <section class="section">

        <div class="mis-kepala">
            <span class="mis-medali mis-ungu" aria-hidden="true">
                <i class="fas {{ $isEdit ? 'fa-edit' : 'fa-plus' }}"></i>
            </span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">{{ $isEdit ? 'Ubah promo' : 'Promo baru' }}</h1>
                <p class="mis-sub">Potongannya berlaku sendiri selama periodenya — pendaftar tidak perlu mengetik kode apa pun.</p>
            </div>
            <div class="mis-kepala-aksi">
                <a href="{{ route('account.promo-layanan.index') }}" class="mis-tombol mis-tombol-halus">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="mis-bagian" style="border-color: #fecaca; background: #fef2f2;">
                <p class="mis-kartu-judul" style="color: #b91c1c;">
                    <i class="fas fa-exclamation-circle"></i> Ada yang perlu dibetulkan
                </p>
                <ul style="margin: 8px 0 0 18px; font-size: .8rem; color: #b91c1c; line-height: 1.6;">
                    @foreach ($errors->all() as $galat)
                        <li>{{ $galat }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" id="prf-borang"
            action="{{ $isEdit ? route('account.promo-layanan.update', $promo->id) : route('account.promo-layanan.store') }}">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="prf-tumpuk">

                {{-- ------------------------------------------- langkah 1 --}}
                <div class="mis-bagian">
                    <p class="prf-langkah">
                        <span class="prf-nomor" aria-hidden="true">1</span>
                        <span class="prf-langkah-judul">Promo apa ini?</span>
                    </p>
                    <p class="prf-langkah-sub">Namanya ikut tersimpan di tiap pendaftaran yang kena promo, jadi pakai nama yang masih bisa dikenali setahun lagi.</p>

                    <div class="prf-kisi">
                        <div class="mis-isian prf-penuh">
                            <label class="mis-label" for="prf-nama">
                                Nama promo <span class="prf-wajib" title="Wajib diisi" aria-hidden="true">*</span>
                            </label>
                            <input type="text" class="form-control-modern" id="prf-nama" name="nama" required
                                maxlength="120" value="{{ old('nama', $promo->nama) }}"
                                placeholder="mis. Promo Ulang Tahun Rumah Scopus ke-8">
                        </div>

                        <div class="mis-isian prf-penuh">
                            <label class="mis-label" for="prf-catatan">Catatan</label>
                            <textarea class="form-control-modern" id="prf-catatan" name="catatan" rows="2"
                                maxlength="500"
                                placeholder="Boleh dikosongkan. Mis. alasan promonya, atau siapa yang menyetujuinya.">{{ old('catatan', $promo->catatan) }}</textarea>
                            <p class="mis-bantuan">Hanya terbaca admin, tidak pernah tampil ke pendaftar.</p>
                        </div>
                    </div>
                </div>

                {{-- ------------------------------------------- langkah 2 --}}
                <div class="mis-bagian">
                    <p class="prf-langkah">
                        <span class="prf-nomor" aria-hidden="true">2</span>
                        <span class="prf-langkah-judul">Potongannya berapa?</span>
                    </p>
                    <p class="prf-langkah-sub">Potongan dihitung <strong>per peserta</strong>. Pesanan lima kursi dapat lima kali potongan ini.</p>

                    @php
                        $tipeSekarang = old('tipe', $promo->tipe ?: \App\PromoLayanan::PERSEN);

                        $tipeKet = [
                            \App\PromoLayanan::PERSEN => 'Harga ikut turun sendiri kalau harga angkatannya berubah.',
                            \App\PromoLayanan::RUPIAH => 'Potongannya tetap, berapa pun harga angkatannya.',
                        ];
                    @endphp

                    <div class="prf-tipe">
                        @foreach ($tipe as $nilai => $nama)
                            <label>
                                <input type="radio" name="tipe" value="{{ $nilai }}"
                                    data-prf-tipe @checked($tipeSekarang === $nilai)>
                                <span>
                                    <span class="prf-tipe-judul">{{ $nama }}</span>
                                    <span class="prf-tipe-ket">{{ $tipeKet[$nilai] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="prf-kisi">
                        {{-- Kotak yang tidak dipakai disembunyikan lewat hidden,
                             bukan dihapus dari markah: nilainya tetap ada saat
                             tipenya diganti bolak-balik, dan validator tetap
                             menerima kiriman dari peramban tanpa JavaScript. --}}
                        <div class="mis-isian" id="prf-bungkus-persen">
                            <label class="mis-label" for="prf-persen">
                                Potongan persen <span class="prf-wajib" title="Wajib diisi" aria-hidden="true">*</span>
                            </label>
                            <input type="number" class="form-control-modern" id="prf-persen" name="persen"
                                min="1" max="100" step="1" data-prf-hitung
                                value="{{ old('persen', $promo->persen) }}" placeholder="40">
                            <p class="mis-bantuan">Antara 1 dan 100.</p>
                        </div>

                        <div class="mis-isian" id="prf-bungkus-batas">
                            <label class="mis-label" for="prf-batas">Maksimal potongan</label>
                            <input type="number" class="form-control-modern" id="prf-batas" name="batas_potongan"
                                min="1" step="1000" data-prf-hitung
                                value="{{ old('batas_potongan', $promo->batas_potongan) }}" placeholder="Boleh dikosongkan">
                            <p class="mis-bantuan">Pengaman untuk harga besar: 40% dari Rp 6.500.000 memotong Rp 2.600.000.</p>
                        </div>

                        <div class="mis-isian" id="prf-bungkus-nominal">
                            <label class="mis-label" for="prf-nominal">
                                Potongan rupiah <span class="prf-wajib" title="Wajib diisi" aria-hidden="true">*</span>
                            </label>
                            <input type="number" class="form-control-modern" id="prf-nominal" name="nominal"
                                min="1" step="1000" data-prf-hitung
                                value="{{ old('nominal', $promo->nominal) }}" placeholder="75000">
                            <p class="mis-bantuan">Diketik angkanya saja, tanpa titik.</p>
                        </div>
                    </div>

                    {{-- aria-live: isinya dihitung ulang tiap ketikan tanpa
                         halaman dimuat, jadi harus ikut diumumkan. --}}
                    <p class="prf-pratinjau kosong" id="prf-pratinjau" aria-live="polite">
                        Isi potongannya dulu untuk melihat contoh hitungannya.
                    </p>
                </div>

                {{-- ------------------------------------------- langkah 3 --}}
                <div class="mis-bagian">
                    <p class="prf-langkah">
                        <span class="prf-nomor" aria-hidden="true">3</span>
                        <span class="prf-langkah-judul">Kapan berlakunya?</span>
                    </p>
                    <p class="prf-langkah-sub">Promonya mulai memotong harga pada tanggal mulai, dan berhenti sesudah tanggal selesai habis. Tidak perlu dimatikan sendiri.</p>

                    <div class="prf-kisi">
                        <div class="mis-isian">
                            <label class="mis-label" for="prf-mulai">
                                Mulai tanggal <span class="prf-wajib" title="Wajib diisi" aria-hidden="true">*</span>
                            </label>
                            <input type="date" class="form-control-modern" id="prf-mulai" name="mulai" required
                                value="{{ old('mulai', $promo->mulai ? $promo->mulai->format('Y-m-d') : '') }}">
                        </div>

                        <div class="mis-isian">
                            <label class="mis-label" for="prf-selesai">
                                Sampai tanggal <span class="prf-wajib" title="Wajib diisi" aria-hidden="true">*</span>
                            </label>
                            <input type="date" class="form-control-modern" id="prf-selesai" name="selesai" required
                                value="{{ old('selesai', $promo->selesai ? $promo->selesai->format('Y-m-d') : '') }}">
                            <p class="mis-bantuan">Hari ini ikut dihitung penuh sampai tengah malam.</p>
                        </div>

                        <div class="mis-isian prf-penuh">
                            <label class="mis-centang-bungkus" style="gap: 9px; font-size: .82rem;">
                                {{-- Nilai 0 di depan: kotak centang yang tidak
                                     dicentang TIDAK terkirim sama sekali, jadi
                                     tanpa baris ini "matikan promo" pada borang
                                     sunting tidak pernah tersimpan. --}}
                                <input type="hidden" name="aktif" value="0">
                                <input type="checkbox" class="mis-centang" name="aktif" value="1"
                                    @checked((bool) old('aktif', $promo->exists ? $promo->aktif : true))>
                                <span>Promo ini menyala</span>
                            </label>
                            <p class="mis-bantuan">
                                Matikan untuk menghentikan promo lebih awal tanpa mengubah tanggalnya —
                                misalnya kalau kuotanya sudah habis.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ------------------------------------------- langkah 4 --}}
                <div class="mis-bagian">
                    <p class="prf-langkah">
                        <span class="prf-nomor" aria-hidden="true">4</span>
                        <span class="prf-langkah-judul">Angkatan mana yang dapat promo?</span>
                    </p>
                    <p class="prf-langkah-sub">Centang satu per satu. Yang tidak dicentang tetap harga normal.</p>

                    <div class="prf-alat">
                        <div class="mis-isian">
                            <label class="mis-label" for="prf-cari">Cari angkatan</label>
                            <input type="search" class="form-control-modern" id="prf-cari"
                                placeholder="Nama angkatan atau nomornya" autocomplete="off"
                                aria-controls="prf-daftar">
                        </div>

                        <label class="mis-centang-bungkus" style="gap: 8px; font-size: .78rem; padding-bottom: 10px;">
                            <input type="checkbox" class="mis-centang" id="prf-sembunyi-lewat" checked>
                            <span>Sembunyikan yang sudah lewat</span>
                        </label>

                        <p class="prf-hitung" id="prf-hitung" role="status" aria-live="polite" style="margin: 0 0 8px auto;">
                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                            <span id="prf-hitung-teks">0 angkatan dipilih</span>
                        </p>
                    </div>

                    <div id="prf-daftar">
                        @forelse ($kelompok as $kode => $grup)
                            <div class="prf-kelompok" data-prf-kelompok>
                                <div class="prf-kelompok-kepala">
                                    <span class="mis-medali kecil {{ $grup['warna'] }}" aria-hidden="true">
                                        <i class="fas {{ $grup['ikon'] }}"></i>
                                    </span>
                                    <div style="min-width: 0;">
                                        <p class="prf-kelompok-nama">{{ $grup['nama'] }}</p>
                                        <p class="prf-kelompok-jumlah" data-prf-tampak>{{ count($grup['baris']) }} angkatan</p>
                                    </div>
                                    <label class="prf-semua">
                                        <input type="checkbox" class="mis-centang" data-prf-semua
                                            aria-label="Pilih semua angkatan {{ $grup['nama'] }} yang sedang tampil">
                                        <span>Pilih semua</span>
                                    </label>
                                </div>

                                <div class="prf-baris-bungkus">
                                    @foreach ($grup['baris'] as $baris)
                                        {{-- Seluruh barisnya <label>, jadi daerah tekannya
                                             selebar barisnya — bukan kotak 16px saja. --}}
                                        <label class="prf-baris" data-prf-baris
                                            data-prf-lewat="{{ $baris['lewat'] ? '1' : '0' }}"
                                            data-prf-kata="{{ \Illuminate\Support\Str::lower($baris['nama'] . ' ' . $baris['ke'] . ' ' . ($baris['varian'] ?? '')) }}">
                                            <input type="checkbox" class="mis-centang" name="angkatan[]"
                                                value="{{ $baris['id'] }}" data-prf-pilih
                                                @checked($baris['dipilih'])>

                                            <span class="prf-baris-teks">
                                                <span class="prf-baris-nama">
                                                    {{ $baris['nama'] }}
                                                    @if ($baris['lewat'])
                                                        <span class="prf-lewat">sudah lewat</span>
                                                    @endif
                                                </span>
                                                <span class="prf-baris-ket">
                                                    @if ($baris['ke'])
                                                        angkatan ke-{{ $baris['ke'] }} &middot;
                                                    @endif
                                                    @if ($baris['varian'])
                                                        {{ $baris['varian'] }} &middot;
                                                    @endif
                                                    {{ $baris['mulai'] ?? 'tanggal belum diisi' }}
                                                </span>
                                            </span>

                                            {{-- Harga sesudah promo dihitung skrip dari
                                                 data-prf-harga. Tanpa JavaScript yang
                                                 terbaca harga normalnya — bukan kotak
                                                 kosong yang terlihat seperti gagal muat. --}}
                                            <span class="prf-baris-harga" data-prf-harga="{{ $baris['harga'] }}">
                                                @if ($baris['harga'] > 0)
                                                    <span class="prf-harga-baru">Rp {{ number_format($baris['harga'], 0, ',', '.') }}</span>
                                                @else
                                                    <span class="prf-harga-nihil">harga belum diisi</span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="mis-kosong">
                                <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
                                <p class="mis-kosong-judul">Belum ada angkatan</p>
                                <p class="mis-kosong-teks">
                                    Promo menempel pada angkatan. Buat angkatannya dulu di menu Angkatan Layanan,
                                    lalu kembali ke sini untuk mencentangnya.
                                </p>
                                <a href="{{ route('account.kategori-layanan.index') }}"
                                    class="mis-tombol mis-tombol-halus mis-kosong-aksi">
                                    <i class="fas fa-layer-group"></i> Buka Angkatan Layanan
                                </a>
                            </div>
                        @endforelse
                    </div>

                    {{-- Keadaan "pencarian tidak ketemu" punya bentuknya sendiri,
                         dan tanpa itu menyaring sampai nol baris terlihat seperti
                         daftarnya hilang. --}}
                    <div class="mis-kosong mis-kosong-cari" id="prf-tidak-ketemu" hidden>
                        <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-search"></i></span>
                        <p class="mis-kosong-judul">Tidak ada angkatan yang cocok</p>
                        <p class="mis-kosong-teks">
                            Coba kata kunci lain, atau hilangkan centang
                            &ldquo;Sembunyikan yang sudah lewat&rdquo;.
                        </p>
                    </div>
                </div>
            </div>

            <div class="prf-kaki">
                <button type="submit" class="mis-tombol mis-tombol-ungu">
                    <i class="fas fa-save"></i> {{ $isEdit ? 'Simpan perubahan' : 'Simpan promo' }}
                </button>
                <a href="{{ route('account.promo-layanan.index') }}" class="mis-tombol mis-tombol-halus">Batal</a>

                @if ($isEdit)
                    {{-- Menghapus tersedia di tempat promo itu sedang dibuka,
                         bukan hanya di daftar: begitu orang masuk ke borangnya,
                         di situlah ia memutuskan. --}}
                    <button type="button" class="mis-tombol mis-tombol-hapus" id="prf-hapus"
                        data-hapus="{{ route('account.promo-layanan.hapus', $promo->id) }}"
                        data-nama="{{ $promo->nama }}">
                        <i class="fas fa-trash-alt"></i> Hapus promo
                    </button>
                @endif
            </div>
        </form>

        {{-- Borang hapus terpisah, di luar borang promo: <form> di dalam <form>
             tidak sah dan peramban membuang yang di dalam tanpa galat. --}}
        @if ($isEdit)
            <form method="POST" id="prf-borang-hapus"
                action="{{ route('account.promo-layanan.hapus', $promo->id) }}" hidden>
                @csrf
                @method('DELETE')
            </form>
        @endif

    </section>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var borang = document.getElementById('prf-borang');

        if (!borang) {
            return;
        }

        // ------------------------------------------------------ tipe promo

        var radio = borang.querySelectorAll('[data-prf-tipe]');
        var bungkusPersen = document.getElementById('prf-bungkus-persen');
        var bungkusBatas = document.getElementById('prf-bungkus-batas');
        var bungkusNominal = document.getElementById('prf-bungkus-nominal');
        var isiPersen = document.getElementById('prf-persen');
        var isiBatas = document.getElementById('prf-batas');
        var isiNominal = document.getElementById('prf-nominal');

        function tipeTerpilih() {
            for (var i = 0; i < radio.length; i++) {
                if (radio[i].checked) {
                    return radio[i].value;
                }
            }

            return 'persen';
        }

        /*
         * Kotak yang disembunyikan ikut dilepas `required`-nya.
         *
         * Tanpa itu peramban menolak mengirim borangnya dan menunjuk kotak
         * yang TIDAK TERLIHAT — pesannya "please fill out this field" pada
         * medan yang display:none, jadi tombol Simpan tampak tidak berfungsi
         * sama sekali. Validator peladen tetap memeriksanya lewat required_if.
         */
        function segarkanTipe() {
            var persenan = tipeTerpilih() === 'persen';

            bungkusPersen.hidden = !persenan;
            bungkusBatas.hidden = !persenan;
            bungkusNominal.hidden = persenan;

            isiPersen.required = persenan;
            isiNominal.required = !persenan;

            hitungUlang();
        }

        for (var r = 0; r < radio.length; r++) {
            radio[r].addEventListener('change', segarkanTipe);
        }

        // ------------------------------------------------------- hitungan

        function rupiah(angka) {
            return 'Rp ' + Math.round(angka).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        /** Rumusnya menyalin PromoLayanan::potonganUntuk, termasuk batasnya. */
        function potongan(harga) {
            if (!(harga > 0)) {
                return 0;
            }

            var hasil;

            if (tipeTerpilih() === 'persen') {
                var persen = parseInt(isiPersen.value, 10);

                if (!(persen > 0)) {
                    return 0;
                }

                hasil = Math.round(harga * Math.min(100, persen) / 100);

                var batas = parseInt(isiBatas.value, 10);

                if (batas > 0) {
                    hasil = Math.min(hasil, batas);
                }
            } else {
                hasil = parseInt(isiNominal.value, 10);

                if (!(hasil > 0)) {
                    return 0;
                }
            }

            return Math.min(harga, hasil);
        }

        // ------------------------------------------------------ pratinjau

        var pratinjau = document.getElementById('prf-pratinjau');

        /*
         * Contohnya memakai harga angkatan yang BENAR-BENAR tercentang, bukan
         * angka bulat yang dikarang.
         *
         * Harga karangan seperti Rp 1.000.000 membuat pratinjaunya terbaca
         * rapi dan tidak pernah memperlihatkan hal yang sebetulnya ingin
         * diketahui: 40% dari Rp 5.500.000 itu Rp 2.200.000. Kalau belum ada
         * yang dicentang, baru dipakai contoh bulat.
         */
        function hargaContoh() {
            var terpilih = borang.querySelectorAll('[data-prf-pilih]:checked');

            for (var i = 0; i < terpilih.length; i++) {
                var sel = terpilih[i].closest('[data-prf-baris]').querySelector('[data-prf-harga]');
                var harga = parseInt(sel.dataset.prfHarga, 10);

                if (harga > 0) {
                    return { harga: harga, nyata: true };
                }
            }

            return { harga: 1000000, nyata: false };
        }

        function hitungUlang() {
            var contoh = hargaContoh();
            var potong = potongan(contoh.harga);

            if (potong <= 0) {
                pratinjau.classList.add('kosong');
                pratinjau.textContent = 'Isi potongannya dulu untuk melihat contoh hitungannya.';
            } else {
                pratinjau.classList.remove('kosong');
                pratinjau.innerHTML = '';

                var kalimat = document.createElement('span');
                kalimat.textContent = (contoh.nyata ? 'Angkatan terpilih' : 'Contoh')
                    + ': harga ';
                pratinjau.appendChild(kalimat);

                var lama = document.createElement('span');
                lama.className = 'prf-coret';
                lama.textContent = rupiah(contoh.harga);
                pratinjau.appendChild(lama);

                /*
                 * Spasinya ada DI DALAM teksnya, bukan hanya `gap` pada
                 * kisinya. Jaraknya terlihat benar di layar lewat gap, tetapi
                 * aria-live membacakan textContent gabungan — dan tanpa spasi
                 * nyata ia terbaca "Rp 1.000.000jadiRp 600.000per peserta".
                 */
                var panah = document.createElement('span');
                panah.textContent = ' jadi ';
                pratinjau.appendChild(panah);

                var baru = document.createElement('strong');
                baru.textContent = rupiah(contoh.harga - potong);
                pratinjau.appendChild(baru);

                var hemat = document.createElement('span');
                hemat.textContent = ' per peserta, hemat ' + rupiah(potong) + '.';
                pratinjau.appendChild(hemat);
            }

            segarkanHarga();
        }

        /** Harga sesudah promo di tiap baris angkatan. */
        function segarkanHarga() {
            var sel = borang.querySelectorAll('[data-prf-harga]');

            for (var i = 0; i < sel.length; i++) {
                var harga = parseInt(sel[i].dataset.prfHarga, 10);

                if (!(harga > 0)) {
                    continue;
                }

                var potong = potongan(harga);

                sel[i].innerHTML = '';

                if (potong > 0) {
                    var lama = document.createElement('span');
                    lama.className = 'prf-harga-lama';
                    lama.textContent = rupiah(harga);
                    sel[i].appendChild(lama);
                }

                var baru = document.createElement('span');
                baru.className = 'prf-harga-baru';
                baru.textContent = rupiah(harga - potong);
                sel[i].appendChild(baru);
            }
        }

        var kotakHitung = borang.querySelectorAll('[data-prf-hitung]');

        for (var h = 0; h < kotakHitung.length; h++) {
            kotakHitung[h].addEventListener('input', hitungUlang);
        }

        // -------------------------------------------- saringan & penghitung

        var kotakCari = document.getElementById('prf-cari');
        var sembunyiLewat = document.getElementById('prf-sembunyi-lewat');
        var tidakKetemu = document.getElementById('prf-tidak-ketemu');
        var hitungTeks = document.getElementById('prf-hitung-teks');
        var lencanaHitung = document.getElementById('prf-hitung');
        var kelompok = borang.querySelectorAll('[data-prf-kelompok]');

        /*
         * Yang SUDAH tercentang tidak pernah disembunyikan saringan.
         *
         * Kotak centang yang tersembunyi tetap terkirim — itu memang yang
         * diinginkan — tetapi admin yang mencari "yogyakarta" lalu melihat
         * penghitung menyebut 6 angkatan sementara layarnya menampilkan 2
         * akan menyangka ada yang tercentang tanpa sepengetahuannya.
         */
        function saring() {
            var kata = (kotakCari ? kotakCari.value : '').trim().toLowerCase();
            var sembunyi = sembunyiLewat ? sembunyiLewat.checked : false;
            var adaYangTampak = false;

            for (var k = 0; k < kelompok.length; k++) {
                var baris = kelompok[k].querySelectorAll('[data-prf-baris]');
                var tampak = 0;

                for (var b = 0; b < baris.length; b++) {
                    var pilih = baris[b].querySelector('[data-prf-pilih]');
                    var tercentang = pilih && pilih.checked;

                    var cocokKata = kata === '' || baris[b].dataset.prfKata.indexOf(kata) !== -1;
                    var bolehLewat = !sembunyi || baris[b].dataset.prfLewat !== '1';

                    var terlihat = tercentang || (cocokKata && bolehLewat);

                    baris[b].hidden = !terlihat;

                    if (terlihat) {
                        tampak++;
                    }
                }

                kelompok[k].hidden = tampak === 0;

                var ket = kelompok[k].querySelector('[data-prf-tampak]');

                if (ket) {
                    ket.textContent = tampak + ' angkatan'
                        + (tampak !== baris.length ? ' dari ' + baris.length : '');
                }

                if (tampak > 0) {
                    adaYangTampak = true;
                }

                segarkanSemua(kelompok[k]);
            }

            if (tidakKetemu) {
                tidakKetemu.hidden = adaYangTampak || kelompok.length === 0;
            }
        }

        function hitungTerpilih() {
            var jumlah = borang.querySelectorAll('[data-prf-pilih]:checked').length;

            hitungTeks.textContent = jumlah === 0
                ? 'Belum ada angkatan dipilih'
                : jumlah + ' angkatan dipilih';

            lencanaHitung.classList.toggle('nihil', jumlah === 0);
        }

        /*
         * "Pilih semua" mengikuti yang SEDANG TAMPIL di kelompoknya.
         *
         * Mengikuti seluruh anggota kelompok, menekannya saat sedang mencari
         * akan mencentang 230 angkatan Scopus Camp sekaligus — padahal yang
         * terlihat di layar cuma tiga.
         */
        function segarkanSemua(grup) {
            var tombol = grup.querySelector('[data-prf-semua]');

            if (!tombol) {
                return;
            }

            var tampak = [];
            var baris = grup.querySelectorAll('[data-prf-baris]');

            for (var i = 0; i < baris.length; i++) {
                if (!baris[i].hidden) {
                    var p = baris[i].querySelector('[data-prf-pilih]');

                    if (p) {
                        tampak.push(p);
                    }
                }
            }

            var tercentang = tampak.filter(function (p) { return p.checked; }).length;

            tombol.checked = tampak.length > 0 && tercentang === tampak.length;
            tombol.indeterminate = tercentang > 0 && tercentang < tampak.length;
            tombol.disabled = tampak.length === 0;
        }

        for (var g = 0; g < kelompok.length; g++) {
            (function (grup) {
                var tombol = grup.querySelector('[data-prf-semua]');

                if (!tombol) {
                    return;
                }

                tombol.addEventListener('change', function () {
                    var baris = grup.querySelectorAll('[data-prf-baris]');

                    for (var i = 0; i < baris.length; i++) {
                        if (baris[i].hidden) {
                            continue;
                        }

                        var p = baris[i].querySelector('[data-prf-pilih]');

                        if (p) {
                            p.checked = tombol.checked;
                        }
                    }

                    hitungTerpilih();
                    hitungUlang();
                    saring();
                });
            })(kelompok[g]);
        }

        /*
         * Dipasang pada borangnya, bukan pada tiap kotak centang: 241 kotak
         * berarti 241 pendengar, dan pendengar sebanyak itu untuk peristiwa
         * yang sama adalah biaya yang tidak perlu di halaman yang juga
         * menghitung harga ulang.
         */
        borang.addEventListener('change', function (ev) {
            if (!ev.target.matches('[data-prf-pilih]')) {
                return;
            }

            hitungTerpilih();
            hitungUlang();

            var grup = ev.target.closest('[data-prf-kelompok]');

            if (grup) {
                segarkanSemua(grup);
            }
        });

        if (kotakCari) {
            kotakCari.addEventListener('input', saring);
        }

        if (sembunyiLewat) {
            sembunyiLewat.addEventListener('change', saring);
        }

        // ---------------------------------------------------------- hapus

        var tombolHapus = document.getElementById('prf-hapus');

        if (tombolHapus) {
            tombolHapus.addEventListener('click', function () {
                window.misKonfirmasi({
                    judul: 'Hapus promo ini?',
                    pesan: 'Promo %s akan dihapus. Harga pendaftaran yang sudah terjadi tidak berubah — nominalnya sudah tersimpan di masing-masing pendaftaran.',
                    sorot: tombolHapus.dataset.nama,
                    tombol: 'Ya, hapus',
                    jenis: 'bahaya',
                }).then(function (ya) {
                    if (ya) {
                        document.getElementById('prf-borang-hapus').submit();
                    }
                });
            });
        }

        segarkanTipe();
        hitungTerpilih();
        saring();
    })();
</script>
@endpush
