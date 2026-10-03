@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Daftarkan Pendaftar | MIS Rumah Scopus
@stop

@push('gaya')
    <style>
        /*
         * Lebar isi dibatasi.
         *
         * Pemilihnya menyebut .mis-badan juga, bukan .bar-wadah saja:
         * mis-ui.css memasang `.mis-badan > .section { max-width: 1600px }`
         * yang berbobot (0,1,1), dan pemilih (0,1,0) kalah walau ditulis
         * belakangan. Versi pertama aturan ini tidak mengubah apa pun justru
         * karena itu — borangnya tetap melar 1.540px, dan isian yang
         * merentang sejauh itu membuat mata menyapu untuk tiap barisnya.
         */
        .mis-badan > .section.bar-wadah {
            max-width: 1000px;
        }

        .bar-langkah {
            margin-bottom: var(--mis-jarak);
        }

        .bar-langkah-kepala {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 14px;
        }

        /* Nomor langkah sebagai ubin bergradien — sama bahasanya dengan
           medali ikon, jadi tidak ada bentuk baru yang harus dipelajari. */
        .bar-nomor {
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            width: 30px;
            height: 30px;
            border-radius: 9px;
            background: var(--mis-ungu);
            color: #fff;
            font-size: .82rem;
            font-weight: 800;
        }

        .bar-langkah-judul {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: var(--mis-tinta);
        }

        .bar-langkah-sub {
            margin: 1px 0 0;
            font-size: .8rem;
            color: var(--mis-tinta-3);
        }

        /*
         * Pilihan layanan sebagai KARTU, bukan menu jatuh.
         *
         * Empat pilihan tetap, masing-masing punya warna dan ikonnya sendiri
         * yang sudah dipakai di seluruh layar pendaftaran — jadi kartunya
         * sekaligus mengajarkan warna mana milik layanan mana. Menu jatuh
         * menyembunyikan ketiganya sampai ditekan dan tidak menampilkan apa
         * pun selain nama.
         */
        .bar-pilihan {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 200px), 1fr));
            gap: 11px;
        }

        .bar-pilihan input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
        }

        /* Radio-nya unsur borang SUNGGUHAN yang disembunyikan, bukan dihapus:
           papan ketik, pembaca layar, dan pengiriman borang tetap bekerja. */
        .bar-kartu {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 14px;
            border: 1.5px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #fff;
            cursor: pointer;
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }

        .bar-kartu:hover {
            border-color: #c7d2fe;
            transform: translateY(-1px);
        }

        .bar-pilihan input:focus-visible + .bar-kartu {
            outline: 3px solid rgba(99, 102, 241, .5);
            outline-offset: 2px;
        }

        .bar-pilihan input:checked + .bar-kartu {
            border-color: #6366f1;
            box-shadow: 0 8px 20px -14px rgba(79, 70, 229, .9);
        }

        /* display: block WAJIB — keduanya <span> di dalam <label>, dan sebagai
           unsur sebaris nama dan keterangannya menyatu dalam satu baris.
           Terlihat pada kartu Scopus Kafe, yang namanya cukup pendek sehingga
           keterangannya ikut naik: "Scopus Kafe harga diketik sendiri". */
        .bar-kartu-nama {
            display: block;
            margin: 0;
            font-size: .88rem;
            font-weight: 700;
            color: var(--mis-tinta);
            line-height: 1.3;
        }

        .bar-kartu-ket {
            display: block;
            margin: 2px 0 0;
            font-size: .74rem;
            color: var(--mis-tinta-4);
        }

        /* Tanda centang muncul hanya pada yang terpilih. */
        .bar-kartu-centang {
            margin-left: auto;
            flex: 0 0 auto;
            color: #6366f1;
            opacity: 0;
            transition: opacity .18s ease;
        }

        .bar-pilihan input:checked + .bar-kartu .bar-kartu-centang {
            opacity: 1;
        }

        .bar-kartu-centang i {
            font-size: 17px;
        }

        /* Pilihan alumni: kotak centang berkartu, bukan centang telanjang di
           antara isian teks. Seluruh kartunya bisa ditekan, jadi sasaran
           sentuhnya jauh lebih besar daripada kotak 16px-nya sendiri. */
        .bar-centang {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            margin: 0;
            padding: 13px 15px;
            border: 1.5px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #fff;
            cursor: pointer;
            transition: border-color .18s ease, background .18s ease;
        }

        .bar-centang:hover {
            border-color: #c7d2fe;
        }

        .bar-centang:has(input:checked) {
            border-color: #10b981;
            background: #ecfdf5;
        }

        .bar-centang input {
            flex: 0 0 auto;
            margin-top: 2px;
        }

        .bar-centang-judul {
            display: block;
            font-size: .85rem;
            font-weight: 700;
            color: var(--mis-tinta);
        }

        .bar-centang-ket {
            display: block;
            margin-top: 2px;
            font-size: .76rem;
            color: var(--mis-tinta-3);
        }

        /* Isian yang dimatikan karena potongan alumni dipakai: terlihat
           nonaktif, bukan sekadar tidak bisa diketik tanpa penjelasan. */
        .bar-isian-mati {
            opacity: .45;
        }

        .bar-isian-kisi {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 215px), 1fr));
            gap: 14px;
            align-content: start;
        }

        .bar-penuh {
            grid-column: 1 / -1;
        }

        /*
         * Isian yang nilainya pendek TIDAK melar selebar kartu.
         *
         * Menu angkatan sempat merentang 1.500px untuk satu baris teks, dan
         * isian selebar itu membuat mata menyapu jauh tanpa alasan. Dibatasi
         * 520px — cukup untuk nama angkatan terpanjang beserta harga dan sisa
         * kursinya — dan tetap menyusut sendiri di layar sempit.
         */
        .bar-penuh > select,
        .bar-penuh > input {
            max-width: 520px;
        }

        /* Batang biaya: angka besar di kiri, rincian di tengah, tombol di
           kanan. Satu baris di layar lebar, menumpuk di ponsel. */
        .bar-biaya {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px 22px;
            padding: 16px 20px;
            border: 1px solid var(--mis-garis);
            border-radius: var(--mis-radius);
            background: linear-gradient(135deg, #faf5ff 0%, #eef2ff 100%);
        }

        .bar-biaya-angka-blok {
            flex: 0 0 auto;
        }

        /*
         * Rincian biaya sebagai daftar istilah, bukan kalimat.
         *
         * <dl> dipakai memang untuk pasangan label-nilai, dan pembaca layar
         * mengumumkannya sebagai pasangan — kalimat "harga 5.500.000 dikali 2
         * dikurangi 500.000" menuntut didengar sampai habis untuk tahu
         * angkanya.
         */
        .bar-rinci {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 20px;
            margin: 0;
            padding-left: 20px;
            border-left: 1px solid #ddd6fe;
        }

        .bar-rinci > div {
            display: flex;
            align-items: baseline;
            gap: 7px;
        }

        .bar-rinci dt {
            margin: 0;
            font-size: .7rem;
            font-weight: 600;
            color: var(--mis-tinta-4);
        }

        .bar-rinci dd {
            margin: 0;
            font-size: .82rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: var(--mis-tinta-2);
        }

        /* Potongan bertinta hijau dan berawalan minus: ia MENGURANGI, dan
           angka yang mengurangi di antara angka yang menambah harus terbaca
           berbeda tanpa membaca labelnya. */
        .bar-rinci-kurang {
            color: #047857 !important;
        }

        .bar-catatan-bawah {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin: 11px 2px 0;
            font-size: .76rem;
            line-height: 1.5;
            color: var(--mis-tinta-4);
        }

        .bar-catatan-bawah i {
            flex: 0 0 auto;
            margin-top: 3px;
            font-size: inherit;
        }

        .bar-biaya-angka {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            color: var(--mis-tinta);
            line-height: 1.15;
        }

        .bar-biaya-label {
            margin: 1px 0 0;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--mis-tinta-4);
        }

        .bar-biaya-aksi {
            margin-left: auto;
            align-self: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        @media (max-width: 575.98px) {
            .bar-biaya-aksi {
                margin-left: 0;
                width: 100%;
            }

            .bar-biaya-aksi .mis-tombol {
                flex: 1 1 100%;
                justify-content: center;
            }
        }

        .bar-nota {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 13px 0 0;
            padding: 11px 14px;
            border: 1px solid #bfdbfe;
            border-radius: var(--mis-radius-kecil);
            background: #eff6ff;
            font-size: .79rem;
            line-height: 1.5;
            color: #1d4ed8;
        }

        .bar-nota i {
            flex: 0 0 auto;
            margin-top: 2px;
            font-size: inherit;
        }

        /* Bagian yang baru berlaku sesudah layanannya dipilih. Disembunyikan
           lewat atribut hidden, bukan kelas: tanpa JavaScript seluruhnya
           tetap terlihat dan borangnya masih bisa dipakai. */
        .bar-langkah[hidden] {
            display: none;
        }
    </style>
@endpush

@section('content')
@php
    use App\Support\PendaftaranSemuaLayanan as Pendaftaran;

    /*
     * Angkatan dirakit jadi data yang bisa dibaca skrip: pilihan angkatan
     * ditukar di peramban saat layanannya berganti, tanpa memuat ulang
     * halaman. Hanya kolom yang memang dipakai yang ikut — harga, kursi, dan
     * tanggalnya — bukan seluruh baris.
     */
    $angkatanJson = [];

    foreach ($angkatan as $kunciLayanan => $daftar) {
        $angkatanJson[$kunciLayanan] = $daftar->map(fn ($a) => [
            'id' => $a->id,
            'varian' => $a->varian ?? '',
            'nama' => $a->nama,
            'mulai' => $a->mulai,
            'harga' => (int) ($a->total_biaya ?: $a->biaya),
            'total_kuota' => $a->total_kuota === null ? null : (int) $a->total_kuota,
            'sisa_kuota' => $a->sisa_kuota === null ? null : (int) $a->sisa_kuota,
        ])->values();
    }
@endphp
<div class="main-content mis-badan">
    <section class="section bar-wadah">

        <div class="mis-kepala">
            <span class="mis-medali mis-hijau" aria-hidden="true"><i class="fas fa-user-plus"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Daftarkan Pendaftar</h1>
                <p class="mis-sub">
                    Untuk yang mendaftar lewat WhatsApp, datang langsung, atau membayar di tempat.
                </p>
            </div>
            <div class="mis-kepala-aksi">
                <a class="mis-tombol mis-tombol-halus" href="{{ route('account.pendaftaran-layanan.index') }}">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali ke daftar
                </a>
            </div>
        </div>

        @include('account.pendaftaran_layanan.partials.pesan')

        <form method="POST" action="{{ route('account.pendaftaran-layanan.simpan') }}" id="bar-borang">
            @csrf

            {{-- ------------------------------------ langkah 1: layanan --}}
            <div class="mis-kartu bar-langkah">
                <div class="bar-langkah-kepala">
                    <span class="bar-nomor" aria-hidden="true">1</span>
                    <div>
                        <p class="bar-langkah-judul">Layanan apa?</p>
                        <p class="bar-langkah-sub">Pilih satu; isian berikutnya menyesuaikan sendiri.</p>
                    </div>
                </div>

                <div class="bar-pilihan">
                    @foreach ($katalog as $kunci => $l)
                        <label>
                            <input type="radio" name="layanan" value="{{ $kunci }}"
                                data-berangkatan="{{ $l['berangkatan'] ? '1' : '0' }}"
                                data-potongan="{{ $l['bisa_potongan'] ? '1' : '0' }}"
                                @checked(old('layanan', $terpilih) === $kunci) required>
                            <span class="bar-kartu">
                                <span class="mis-medali {{ $l['warna'] }}" aria-hidden="true">
                                    <i class="fas {{ $l['ikon'] }}"></i>
                                </span>
                                <span style="min-width: 0;">
                                    <span class="bar-kartu-nama">{{ $l['nama'] }}</span>
                                    <span class="bar-kartu-ket">
                                        {{ $l['berangkatan'] ? 'berangkatan, harga ikut angkatan' : 'harga diketik sendiri' }}
                                    </span>
                                </span>
                                <span class="bar-kartu-centang" aria-hidden="true"><i class="fas fa-check-circle"></i></span>
                            </span>
                        </label>
                    @endforeach
                </div>

                {{-- Disebut apa adanya, bukan dibiarkan jadi pertanyaan: layanan
                     yang tidak ada di sini memang tidak bisa didaftarkan dari
                     layar ini, dan alasannya bukan kelalaian. --}}
                <p class="bar-nota">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    <span>
                        <strong>Clinik Scopus tidak ada di sini.</strong> Pemesanannya mengikat
                        sesi tertentu, trainer yang mendampingi, dan akun pelanggan — ketiganya
                        dipilih lewat alur pemesanan Clinik Scopus sendiri.
                    </span>
                </p>
            </div>

            {{-- ------------------------------ langkah 2: angkatan & biaya --}}
            {{-- SELALU ada, tidak pernah disembunyikan.

                 Versi sebelumnya menyembunyikan langkah ini untuk layanan
                 tanpa angkatan, sehingga nomor langkahnya melompat 1 ke 3 dan
                 terbaca seperti ada yang rusak. Sekarang isinya yang berganti:
                 angkatan untuk yang berangkatan, nominal ketik untuk yang
                 tidak — keduanya sama-sama "berapa yang dibayar". --}}
            <div class="mis-kartu bar-langkah">
                <div class="bar-langkah-kepala">
                    <span class="bar-nomor" aria-hidden="true">2</span>
                    <div>
                        <p class="bar-langkah-judul">Berapa yang dibayar?</p>
                        <p class="bar-langkah-sub" id="bar-sub-biaya">Pilih layanannya dulu.</p>
                    </div>
                </div>

                <div class="bar-isian-kisi">
                    <div class="mis-isian bar-penuh" id="bar-bungkus-angkatan">
                        <label class="mis-label" for="bar-angkatan">Angkatan</label>
                        <select class="form-control-modern" id="bar-angkatan" name="kategori_id">
                            <option value="">Pilih layanan dulu</option>
                        </select>
                        <p class="mis-bantuan" id="bar-angkatan-ket">
                            Hanya angkatan yang belum lewat yang ditawarkan.
                        </p>
                    </div>

                    <div class="mis-isian" id="bar-bungkus-jumlah">
                        <label class="mis-label" for="bar-jumlah">Jumlah orang</label>
                        <input type="number" class="form-control-modern" id="bar-jumlah" name="jumlah"
                            value="{{ old('jumlah', 1) }}" min="1" max="99">
                        <p class="mis-bantuan">Untuk pendaftaran rombongan.</p>
                    </div>

                    <div class="mis-isian" id="bar-bungkus-total" hidden>
                        <label class="mis-label" for="bar-total">Total bayar</label>
                        <input type="text" class="form-control-modern" id="bar-total" name="total"
                            value="{{ old('total') }}" inputmode="numeric" placeholder="contoh: 250000">
                        <p class="mis-bantuan">Dalam rupiah, tanpa titik.</p>
                    </div>

                    {{-- Potongan ALUMNI, disetel sekali di Tarif Layanan.

                         Dicentang, ia MENGGANTIKAN potongan khusus — bukan
                         menambahnya. Dua potongan yang ditumpuk membuat harga
                         akhirnya tidak bisa dijelaskan dari salah satunya, dan
                         panitia yang memberi potongan khusus kepada seorang
                         alumni hampir selalu bermaksud menggantikannya. --}}
                    <div class="mis-isian bar-penuh" id="bar-bungkus-alumni" hidden>
                        <label class="bar-centang" for="bar-alumni">
                            <input type="checkbox" class="mis-centang" id="bar-alumni"
                                name="alumni" value="1" @checked(old('alumni'))>
                            <span>
                                <span class="bar-centang-judul">Pendaftar ini alumni</span>
                                <span class="bar-centang-ket" id="bar-alumni-ket">
                                    Potongannya ikut aturan di Tarif Layanan.
                                </span>
                            </span>
                        </label>
                    </div>

                    {{-- Potongan KHUSUS, di atas potongan bawaan angkatannya.

                         Angkatan sudah punya diskonnya sendiri dan itu sudah
                         terhitung di harganya; yang ini untuk hal yang tidak
                         bisa diketahui angkatan — peserta yang disponsori,
                         harga mitra, atau kesepakatan di tempat. --}}
                    <div class="mis-isian" id="bar-bungkus-potongan">
                        <label class="mis-label" for="bar-potongan">Potongan khusus</label>
                        <input type="text" class="form-control-modern" id="bar-potongan" name="potongan"
                            value="{{ old('potongan') }}" inputmode="numeric" placeholder="boleh dikosongkan">
                        <p class="mis-bantuan">Rupiah, di luar diskon angkatannya.</p>
                    </div>

                    <div class="mis-isian" id="bar-bungkus-kode">
                        <label class="mis-label" for="bar-kode-potongan">Alasan potongan</label>
                        <input type="text" class="form-control-modern" id="bar-kode-potongan"
                            name="kode_potongan" value="{{ old('kode_potongan') }}" maxlength="40"
                            placeholder="mis. SPONSOR, MITRA">
                        {{-- Tanpa keterangan, potongan Rp 500.000 pada satu
                             pendaftaran tidak bisa dijelaskan siapa pun enam
                             bulan kemudian. --}}
                        <p class="mis-bantuan">Supaya potongannya bisa dijelaskan nanti.</p>
                    </div>
                </div>
            </div>

            {{-- -------------------------------- langkah 3: cara bayarnya --}}
            {{-- Ada demi pendaftar yang DIBANTU panitia.

                 Sebelum ini satu-satunya cara bayar yang bisa dicatat adalah
                 transfer, jadi yang menyerahkan uang di tempat tercatat
                 "menunggu bayar" tanpa bukti — tidak bisa dibedakan dari yang
                 memang belum membayar, dan ikut tertandai menggantung setelah
                 7 hari. --}}
            <div class="mis-kartu bar-langkah">
                <div class="bar-langkah-kepala">
                    <span class="bar-nomor" aria-hidden="true">3</span>
                    <div>
                        <p class="bar-langkah-judul">Bagaimana bayarnya?</p>
                        <p class="bar-langkah-sub">Pilih yang sesuai; sisanya menyesuaikan sendiri.</p>
                    </div>
                </div>

                <div class="bar-pilihan">
                    @foreach ($caraBayar as $kunci => $c)
                        <label>
                            <input type="radio" name="cara_bayar" value="{{ $kunci }}"
                                data-perlu-bukti="{{ $c['perlu_bukti'] ? '1' : '0' }}"
                                @checked(old('cara_bayar', 'transfer') === $kunci) required>
                            <span class="bar-kartu">
                                <span class="mis-medali {{ $c['warna'] }}" aria-hidden="true">
                                    <i class="fas {{ $c['ikon'] }}"></i>
                                </span>
                                <span style="min-width: 0;">
                                    <span class="bar-kartu-nama">{{ $c['label'] }}</span>
                                    <span class="bar-kartu-ket">{{ $c['ket'] }}</span>
                                </span>
                                <span class="bar-kartu-centang" aria-hidden="true"><i class="fas fa-check-circle"></i></span>
                            </span>
                        </label>
                    @endforeach
                </div>

                {{-- Hanya muncul untuk tunai: melunaskan transfer dari borang
                     ini berarti melunaskan sebelum ada yang mencocokkannya
                     dengan mutasi rekening. --}}
                <div class="mis-isian bar-penuh" id="bar-bungkus-terima" hidden>
                    <label class="bar-centang" for="bar-terima">
                        <input type="checkbox" class="mis-centang" id="bar-terima"
                            name="uang_diterima" value="1" @checked(old('uang_diterima'))>
                        <span>
                            <span class="bar-centang-judul">Uangnya sudah saya terima</span>
                            <span class="bar-centang-ket">
                                Pendaftarannya langsung dicatat lunas. Biarkan kosong kalau
                                orangnya baru akan membayar saat datang.
                            </span>
                        </span>
                    </label>
                </div>

                <p class="bar-nota" id="bar-nota-bukti">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    <span>
                        <strong>Bukti transfernya diunggah nanti</strong> dari halaman rincian
                        pendaftaran ini, setelah uangnya masuk — borang ini tidak memintanya
                        supaya pendaftarnya bisa dicatat lebih dulu.
                    </span>
                </p>

                <p class="bar-nota" id="bar-nota-doku" hidden>
                    <i class="fas fa-credit-card" aria-hidden="true"></i>
                    <span>
                        <strong>Pembayaran daring (DOKU)</strong> tidak dipilih dari sini.
                        Pendaftar yang membayar sendiri lewat halaman DOKU mendapat tandanya
                        otomatis saat tagihannya dibuat.
                    </span>
                </p>
            </div>

            {{-- ------------------------------------- langkah 4: orangnya --}}
            <div class="mis-kartu bar-langkah">
                <div class="bar-langkah-kepala">
                    <span class="bar-nomor" aria-hidden="true">4</span>
                    <div>
                        <p class="bar-langkah-judul">Siapa yang mendaftar?</p>
                        <p class="bar-langkah-sub">Empat isian; nomor, status, dan kode uniknya diisi sistem.</p>
                    </div>
                </div>

                <div class="bar-isian-kisi">
                    <div class="mis-isian">
                        <label class="mis-label" for="bar-nama">Nama lengkap</label>
                        <input type="text" class="form-control-modern" id="bar-nama" name="nama"
                            value="{{ old('nama') }}" required maxlength="255" autocomplete="off">
                    </div>

                    <div class="mis-isian">
                        <label class="mis-label" for="bar-email">Email</label>
                        <input type="email" class="form-control-modern" id="bar-email" name="email"
                            value="{{ old('email') }}" required maxlength="255" autocomplete="off">
                    </div>

                    <div class="mis-isian">
                        <label class="mis-label" for="bar-telp">Nomor WhatsApp</label>
                        <input type="text" class="form-control-modern" id="bar-telp" name="telp"
                            value="{{ old('telp') }}" required maxlength="30" autocomplete="off"
                            inputmode="tel" placeholder="08xx atau 62xx">
                    </div>

                    <div class="mis-isian">
                        <label class="mis-label" for="bar-affiliasi">Afiliasi / instansi</label>
                        <input type="text" class="form-control-modern" id="bar-affiliasi" name="affiliasi"
                            value="{{ old('affiliasi') }}" maxlength="255" autocomplete="off"
                            placeholder="boleh dikosongkan">
                    </div>

                    <div class="mis-isian bar-penuh">
                        <label class="mis-label" for="bar-note">Catatan panitia</label>
                        <textarea class="form-control-modern" id="bar-note" name="note" rows="2"
                            maxlength="1000" placeholder="boleh dikosongkan">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- -------------------------------------------- biaya --}}
            <div class="bar-biaya">
                <div class="bar-biaya-angka-blok">
                    <p class="bar-biaya-angka" id="bar-angka">Rp 0</p>
                    <p class="bar-biaya-label">Total bayar</p>
                </div>

                {{-- Rinciannya disebut, bukan cuma hasil akhirnya: panitia yang
                     memberi potongan perlu melihat potongannya memang masuk,
                     dan yang tidak memberi potongan tidak perlu melihat baris
                     yang selalu nol. Karena itu tiap barisnya hanya muncul
                     saat memang ada isinya. --}}
                <dl class="bar-rinci" id="bar-rinci" hidden>
                    <div id="bar-rinci-satuan" hidden>
                        <dt>Harga satuan</dt>
                        <dd id="bar-nilai-satuan">—</dd>
                    </div>
                    <div id="bar-rinci-jumlah" hidden>
                        <dt>Jumlah orang</dt>
                        <dd id="bar-nilai-jumlah">—</dd>
                    </div>
                    <div id="bar-rinci-potongan" hidden>
                        <dt>Potongan khusus</dt>
                        <dd id="bar-nilai-potongan" class="bar-rinci-kurang">—</dd>
                    </div>
                </dl>

                <div class="bar-biaya-aksi">
                    <a class="mis-tombol mis-tombol-halus" href="{{ route('account.pendaftaran-layanan.index') }}">
                        Batal
                    </a>
                    <button type="submit" class="mis-tombol mis-tombol-ungu">
                        <i class="fas fa-save" aria-hidden="true"></i> Simpan pendaftaran
                    </button>
                </div>
            </div>

            {{-- Keterangan apa yang dibuat sistem ditaruh DI BAWAH batang
                 biaya, bukan di dalamnya: ia dibaca sekali lalu tidak pernah
                 dilihat lagi, sementara angka di atasnya dilihat tiap kali
                 isiannya berubah. --}}
            <p class="bar-catatan-bawah">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                Nomor pendaftaran, status, dan kode unik dibuat sistem sesudah disimpan.
                Kode unik itu yang membuat nominalnya bisa dicocokkan dengan mutasi rekening.
            </p>

        </form>

    </section>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        'use strict';

        /*
         * Pilihan angkatan ditukar di peramban saat layanannya berganti —
         * tanpa memuat ulang halaman, dan tanpa satu kueri pun tambahan:
         * seluruh angkatan keempat layanan sudah ikut terkirim bersama
         * halamannya. Daftarnya 59 baris di seluruh basis data, jadi
         * mengirimnya sekaligus jauh lebih murah daripada satu permintaan
         * tiap kali pilihannya berubah.
         */
        var ANGKATAN = @json($angkatanJson);
        var LAMA_ANGKATAN = @json(old('kategori_id'));

        /*
         * Potongan alumni tiap tarif aktif, berkunci "layanan|varian".
         *
         * Dikunci varian pula, sebab satu layanan bisa punya beberapa tarif
         * dengan harga berbeda — Scopus Camp punya jawa dan luar_jawa — dan
         * potongan alumninya disetel per tarif.
         */
        var ALUMNI = @json($alumni);

        var borang = document.getElementById('bar-borang');

        if (!borang) {
            return;
        }

        var el = function (id) { return document.getElementById(id); };

        var menuAngkatan = el('bar-angkatan');
        var bungkusAngkatan = el('bar-bungkus-angkatan');
        var bungkusJumlah = el('bar-bungkus-jumlah');
        var bungkusTotal = el('bar-bungkus-total');
        var bungkusPotongan = el('bar-bungkus-potongan');
        var bungkusKode = el('bar-bungkus-kode');
        var isianTotal = el('bar-total');
        var isianJumlah = el('bar-jumlah');
        var isianPotongan = el('bar-potongan');
        var isianKode = el('bar-kode-potongan');
        var centangAlumni = el('bar-alumni');
        var bungkusAlumni = el('bar-bungkus-alumni');
        var ketAlumni = el('bar-alumni-ket');
        var bungkusTerima = el('bar-bungkus-terima');
        var centangTerima = el('bar-terima');
        var notaBukti = el('bar-nota-bukti');
        var angka = el('bar-angka');
        var ket = el('bar-angkatan-ket');
        var subBiaya = el('bar-sub-biaya');

        var rinci = el('bar-rinci');
        var rinciSatuan = el('bar-rinci-satuan');
        var rinciJumlah = el('bar-rinci-jumlah');
        var rinciPotongan = el('bar-rinci-potongan');
        var nilaiSatuan = el('bar-nilai-satuan');
        var nilaiJumlah = el('bar-nilai-jumlah');
        var nilaiPotongan = el('bar-nilai-potongan');

        var rupiah = function (n) {
            return 'Rp ' + (n || 0).toLocaleString('id-ID');
        };

        var angkaDari = function (teks) {
            var n = parseInt(String(teks || '').replace(/\D+/g, ''), 10);
            return isNaN(n) ? 0 : n;
        };

        /* Berapa persen potongan alumni untuk pilihan yang sedang dibuat. */
        var persenAlumni = function (pilih) {
            if (!pilih) {
                return 0;
            }

            var varian = '';

            if (pilih.berangkatan) {
                var o = menuAngkatan.options[menuAngkatan.selectedIndex];
                varian = o ? (o.dataset.varian || '') : '';
            }

            return ALUMNI[pilih.nilai + '|' + varian] || 0;
        };

        var layananTerpilih = function () {
            var r = borang.querySelector('input[name="layanan"]:checked');

            return r ? {
                nilai: r.value,
                berangkatan: r.dataset.berangkatan === '1',
                bisaPotongan: r.dataset.potongan === '1',
            } : null;
        };

        var isiAngkatan = function (layanan) {
            var daftar = ANGKATAN[layanan] || [];

            menuAngkatan.innerHTML = '';

            if (!daftar.length) {
                var kosong = document.createElement('option');
                kosong.value = '';
                kosong.textContent = 'Belum ada angkatan yang akan datang';
                menuAngkatan.appendChild(kosong);
                ket.textContent = 'Buat angkatannya dulu di layar Angkatan Layanan.';
                return;
            }

            daftar.forEach(function (a) {
                var o = document.createElement('option');
                o.value = a.id;
                o.dataset.harga = a.harga;
                o.dataset.varian = a.varian || '';

                /*
                 * Harga dan sisa kursi ikut tertulis di pilihannya.
                 *
                 * Tanpa itu panitia harus membuka layar Angkatan Layanan untuk
                 * tahu angkatan mana yang masih longgar — dan angkatan yang
                 * sudah penuh baru ketahuan sesudah kirimannya ditolak.
                 */
                var sisa = a.sisa_kuota === null
                    ? 'tanpa batas kuota'
                    : ('sisa ' + a.sisa_kuota + ' kursi');

                o.textContent = a.nama + ' — ' + rupiah(a.harga) + ' · ' + sisa;
                menuAngkatan.appendChild(o);
            });

            ket.textContent = 'Hanya angkatan yang belum lewat yang ditawarkan.';

            if (LAMA_ANGKATAN) {
                menuAngkatan.value = LAMA_ANGKATAN;
                LAMA_ANGKATAN = null;
            }
        };

        var tampil = function (unsur, tampak) {
            if (unsur) {
                unsur.hidden = !tampak;
            }
        };

        /**
         * Menyesuaikan langkah cara bayar dengan pilihannya.
         *
         * "Uangnya sudah saya terima" HANYA untuk tunai: melunaskan transfer
         * dari borang ini berarti melunaskan sebelum ada yang mencocokkannya
         * dengan mutasi rekening. Centangnya ikut dilepas saat disembunyikan —
         * yang tersembunyi tidak terkirim, dan nilai lama yang menempel
         * membuat layar dan yang tersimpan berbeda.
         */
        var segarkanBayar = function () {
            var pilih = borang.querySelector('input[name="cara_bayar"]:checked');
            var tunai = !!pilih && pilih.value === 'tunai';

            tampil(bungkusTerima, tunai);
            tampil(notaBukti, !tunai);

            if (!tunai && centangTerima) {
                centangTerima.checked = false;
            }
        };

        var hitung = function () {
            var pilih = layananTerpilih();

            if (!pilih) {
                angka.textContent = 'Rp 0';
                tampil(rinci, false);
                return;
            }

            var satuan = 0;
            var jml = 1;

            if (pilih.berangkatan) {
                var o = menuAngkatan.options[menuAngkatan.selectedIndex];
                satuan = o ? parseInt(o.dataset.harga || '0', 10) : 0;
                jml = Math.max(1, angkaDari(isianJumlah.value) || 1);
            } else {
                // Nominal diketik: yang bukan angka dibuang, sama dengan cara
                // peladen membacanya — jadi angka di layar dan yang tersimpan
                // tidak pernah berbeda.
                satuan = angkaDari(isianTotal.value);
            }

            var subtotal = satuan * jml;
            var persen = persenAlumni(pilih);
            var pakaiAlumni = centangAlumni.checked && persen > 0;

            /*
             * Alumni MENGGANTIKAN potongan khusus, tidak menambahnya — sama
             * dengan aturan di peladen. Isian potongan khususnya dimatikan
             * supaya tidak ada yang mengetik angka yang kemudian diabaikan
             * diam-diam.
             */
            var potongan;

            if (pakaiAlumni) {
                potongan = Math.round(subtotal * persen / 100);
            } else {
                potongan = pilih.bisaPotongan ? angkaDari(isianPotongan.value) : 0;
            }

            potongan = Math.min(subtotal, potongan);

            [isianPotongan, isianKode].forEach(function (n) {
                n.disabled = pakaiAlumni;
                n.closest('.mis-isian').classList.toggle('bar-isian-mati', pakaiAlumni);
            });

            angka.textContent = rupiah(subtotal - potongan);

            nilaiSatuan.textContent = rupiah(satuan);
            nilaiJumlah.textContent = jml + ' orang';
            nilaiPotongan.textContent = '− ' + rupiah(potongan)
                + (pakaiAlumni ? ' (alumni ' + persen + '%)' : '');

            // Tiap baris rincian hanya muncul kalau memang ada isinya; baris
            // yang selalu nol cuma menambah yang harus dibaca.
            tampil(rinciSatuan, pilih.berangkatan && satuan > 0);
            tampil(rinciJumlah, pilih.berangkatan && jml > 1);
            tampil(rinciPotongan, potongan > 0);

            tampil(rinci, (pilih.berangkatan && satuan > 0 && (jml > 1 || potongan > 0)) || potongan > 0);
        };

        var segarkan = function () {
            var pilih = layananTerpilih();

            if (!pilih) {
                subBiaya.textContent = 'Pilih layanannya dulu.';
                tampil(bungkusAngkatan, false);
                tampil(bungkusJumlah, false);
                tampil(bungkusTotal, false);
                tampil(bungkusPotongan, false);
                tampil(bungkusKode, false);
                hitung();
                return;
            }

            tampil(bungkusAngkatan, pilih.berangkatan);
            tampil(bungkusJumlah, pilih.berangkatan);
            tampil(bungkusTotal, !pilih.berangkatan);
            tampil(bungkusPotongan, pilih.bisaPotongan);
            tampil(bungkusKode, pilih.bisaPotongan);


            menuAngkatan.required = pilih.berangkatan;
            isianTotal.required = !pilih.berangkatan;

            subBiaya.textContent = pilih.berangkatan
                ? 'Harga dan sisa kursinya ikut dari angkatan yang dipilih.'
                : 'Layanan ini tidak berangkatan, jadi nominalnya diketik sendiri.';

            if (pilih.berangkatan) {
                isiAngkatan(pilih.nilai);
            }

            /*
             * Tawaran alumni dihitung SESUDAH pilihan angkatannya diisi.
             *
             * Potongannya disetel per VARIAN, dan variannya dibaca dari
             * pilihan angkatan yang sedang terpilih — dihitung sebelum
             * pilihannya ada, variannya masih kosong dan tawarannya tidak
             * pernah muncul. Terukur: pilihan alumni tidak tampil sama sekali
             * padahal tarifnya menyetel 15%.
             *
             * Hanya ditawarkan kalau tarifnya MEMANG menyetelnya; kotak
             * centang yang tidak mengubah apa pun saat ditekan lebih buruk
             * daripada tidak ada kotaknya.
             */
            var persen = persenAlumni(pilih);

            tampil(bungkusAlumni, pilih.bisaPotongan && persen > 0);

            if (persen > 0) {
                ketAlumni.textContent = 'Potongan ' + persen
                    + '% dari Tarif Layanan. Tidak bisa digabung dengan potongan khusus.';
            } else {
                // Tersembunyi berarti juga tidak ikut terkirim; centangnya
                // dilepas supaya nilai lama tidak menempel saat berganti
                // layanan.
                centangAlumni.checked = false;
            }

            hitung();
        };

        borang.addEventListener('change', function (e) {
            if (e.target.name === 'layanan') {
                segarkan();
            } else if (e.target.name === 'cara_bayar') {
                segarkanBayar();
            } else if (e.target === menuAngkatan) {
                // Berganti angkatan bisa berganti VARIAN, dan potongan
                // alumninya disetel per varian — jadi tawarannya ikut dihitung
                // ulang, bukan cuma harganya.
                segarkan();
            } else {
                hitung();
            }
        });

        [isianTotal, isianJumlah, isianPotongan].forEach(function (n) {
            n.addEventListener('input', hitung);
        });

        segarkan();
        segarkanBayar();
    })();
</script>
@endpush
