@extends('public.layout.header')

@section('title', 'Pendaftaran ' . $item->nama . ' #' . $item->nama_ke . ' | Rumah Scopus')

@push('kepala')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600..900&family=Figtree:wght@400;500;700;800&display=swap">
@endpush

@section('konten')
{{--
    Borang pendaftaran Scopus Camp — dirombak 23 Okt 2026.

    Dua hal sekaligus, dan keduanya diminta pemiliknya: tampilannya disamakan
    dengan halaman daftar dan halaman detail, dan pembayarannya pindah ke
    gerbang DOKU.

    Yang HILANG dari versi lama, berikut sebabnya:

      - palet ungu-indigo #6366f1 dengan kaca buram. Seluruh jalur Scopus
        Camp sudah memakai kertas krem dan jingga; borang ini satu-satunya
        layar yang berganti warna di tengah alur, tepat saat orangnya
        hendak membayar;
      - nomor rekening dan unggah bukti transfer, SELAMA gerbangnya siap.
        Jalurnya: salin nomor, pindah ke aplikasi bank, kembali, potret
        bukti, unggah, lalu menunggu panitia membuka gambarnya. Terukur di
        basis data ini, enam bukti menunggu diperiksa 177, 127, 74, 28, 17,
        dan 6 hari. Dengan gerbang, orangnya menekan satu tombol dan
        statusnya berpindah sendiri;
      - `position: sticky` pada kartu ringkasan. Di dalam butir CSS Grid ia
        TIDAK PERNAH menempel — sudah diukur di halaman detail — tetapi
        tetap menggeser kartunya sejauh nilai `top`-nya.

    Jalur transfer manual TIDAK dibuang, cuma disembunyikan selama gerbangnya
    siap: peladen yang kredensial DOKU-nya belum dipasang harus tetap punya
    cara menerima uang. Yang menentukan $bayarDaring, dihitung sekali di
    pengendali supaya borang dan penyimpanannya tidak bisa berselisih.
--}}
<style>
    .daf {
        --kertas: #fdf5e8;  --kertas-2: #f8ead4;
        --tinta: #16233f;   --tinta-2: #5c6b85;
        --jingga: #e76d20;  --jingga-muda: #fb8c3d; --jingga-tua: #ad4d12;
        --stabilo: #ffd84d; --rumput: #0f766e;
        --garis: #e7dcc6;

        background: var(--kertas); color: var(--tinta);
        font-family: 'Figtree', system-ui, sans-serif;
        /* 18px, bukan 16px bawaan templat: pembacanya rata-rata berusia
           lanjut, dan ukuran bawaannya terbaca kecil di layar mereka. */
        font-size: 18px;
        padding: 112px 0 64px;
        overflow-x: clip;
    }
    .daf h1, .daf h2, .daf h3 {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 800; color: var(--tinta); margin: 0; letter-spacing: -.01em;
    }
    .daf p { margin: 0; color: var(--tinta-2); line-height: 1.7; }
    .daf a { text-decoration: none; }

    .daf-balik {
        display: inline-flex; align-items: center; gap: 8px; margin-bottom: 16px;
        color: var(--tinta-2); font-weight: 700; font-size: .95rem;
        transition: gap .2s ease, color .2s ease;
    }
    .daf-balik:hover { color: var(--jingga-tua); gap: 13px; }

    .daf .daf-judul { font-size: clamp(1.6rem, 3.2vw, 2.3rem); line-height: 1.15; }
    .daf-anak { margin-top: 8px; font-size: 1.02rem; }

    /* ==================================================== susunan kolom */
    .daf-kisi {
        display: grid; grid-template-columns: minmax(0, 1fr) 390px; gap: 30px;
        align-items: start; margin-top: 34px;
    }

    .daf-kartu {
        background: #fff; border: 1px solid var(--garis); border-radius: 22px;
        padding: 26px 26px 28px; margin-bottom: 24px;
        box-shadow: 0 10px 28px rgba(22, 35, 63, .06);
    }
    .daf-kartu:last-child { margin-bottom: 0; }

    .daf-kepala {
        display: flex; align-items: center; gap: 11px;
        font-family: 'Fraunces', Georgia, serif; font-weight: 800;
        font-size: 1.22rem; color: var(--tinta);
        padding-bottom: 14px; margin-bottom: 20px;
        border-bottom: 2px solid var(--kertas-2);
    }
    .daf-kepala i { color: var(--jingga); }

    /* ==================================================== ringkasan kelas */
    .daf-ringkas { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 24px; margin: 0; }
    .daf-ringkas dt {
        font-size: .78rem; font-weight: 800; letter-spacing: .06em;
        text-transform: uppercase; color: var(--tinta-2); margin-bottom: 3px;
    }
    .daf-ringkas dd { margin: 0; font-weight: 700; font-size: 1.04rem; color: var(--tinta); }
    .daf-coret { color: var(--tinta-2); font-weight: 600; margin-right: 6px; }

    /* ==================================================== isian */
    .daf-medan { margin-bottom: 20px; }
    .daf-medan:last-child { margin-bottom: 0; }
    .daf-medan label {
        display: block; margin-bottom: 7px;
        font-size: .88rem; font-weight: 800; color: var(--tinta);
    }
    .daf-wajib { color: #c0392b; }
    .daf-opsional { font-weight: 600; color: var(--tinta-2); text-transform: none; letter-spacing: 0; }

    .daf-isian {
        width: 100%; background: #fff;
        border: 2px solid var(--garis); border-radius: 14px;
        padding: 13px 16px; font-size: 1.02rem; font-weight: 600;
        color: var(--tinta); font-family: inherit;
        transition: border-color .18s ease, box-shadow .18s ease;
    }
    .daf-isian::placeholder { color: #a9b3c4; font-weight: 500; }
    .daf-isian:focus {
        outline: none; border-color: var(--jingga);
        box-shadow: 0 0 0 4px rgba(231, 109, 32, .15);
    }

    .daf-dua { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }

    /* ==================================================== kode diskon */
    .daf-diskon-baris { display: flex; gap: 10px; }
    .daf-diskon-baris .daf-isian { flex: 1 1 auto; min-width: 0; }
    .daf-cek {
        flex: 0 0 auto; border: 0; border-radius: 14px; padding: 0 20px;
        background: var(--tinta); color: #fff; font-weight: 800; font-size: .95rem;
        font-family: inherit; cursor: pointer; transition: background .18s ease;
    }
    .daf-cek:hover { background: var(--jingga-tua); }
    .daf-pesan { display: block; margin-top: 8px; font-size: .92rem; font-weight: 700; }
    .daf-pesan.ok { color: var(--rumput); }
    .daf-pesan.gagal { color: #c0392b; }

    /* ==================================================== rincian biaya */
    .daf-baris {
        display: flex; align-items: baseline; justify-content: space-between;
        gap: 14px; padding: 11px 0; border-bottom: 1px dashed var(--garis);
        font-size: 1rem;
    }
    .daf-baris span:first-child { color: var(--tinta-2); font-weight: 600; }
    .daf-baris b { font-weight: 800; color: var(--tinta); }
    .daf-baris.potong b { color: var(--rumput); }

    .daf-total {
        display: flex; align-items: baseline; justify-content: space-between;
        gap: 14px; margin-top: 16px; padding: 16px 18px;
        background: var(--kertas-2); border-radius: 16px;
    }
    .daf-total span { font-weight: 800; font-size: 1rem; color: var(--tinta); }
    .daf-total b {
        font-family: 'Fraunces', Georgia, serif; font-size: 1.5rem;
        font-weight: 900; color: var(--jingga-tua); line-height: 1.1;
        /* Fraunces memakai angka gaya lama: tingginya tidak seragam dan
           sebagian turun di bawah garis dasar. Pada nominal uang ia terbaca
           seperti salah cetak di sebelah "Rp" yang setinggi huruf besar. */
        font-variant-numeric: lining-nums tabular-nums;
    }

    /* ==================================================== tombol kirim */
    .daf-kirim {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        width: 100%; margin-top: 18px; padding: 0 22px;
        /* 60px: di atas patokan sasaran sentuh 44px, sebab pembacanya
           rata-rata berusia lanjut. */
        min-height: 60px;
        border: 0; border-radius: 16px; cursor: pointer;
        background: linear-gradient(135deg, var(--jingga-muda), var(--jingga-tua));
        color: #fff; font-family: 'Fraunces', Georgia, serif;
        font-size: 1.1rem; font-weight: 800; line-height: 1.25;
        box-shadow: 0 10px 24px rgba(231, 109, 32, .32);
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .daf-kirim:hover { transform: translateY(-2px); box-shadow: 0 16px 32px rgba(231, 109, 32, .4); }
    .daf-kirim:disabled { opacity: .72; cursor: wait; transform: none; }

    .daf-aman {
        display: flex; align-items: flex-start; gap: 9px;
        margin-top: 14px; font-size: .9rem; line-height: 1.5; color: var(--tinta-2);
    }
    .daf-aman i { color: var(--rumput); margin-top: 3px; }

    /* ==================================================== kanal bayar */
    .daf-kanal { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
    .daf-kanal span {
        padding: 6px 13px; border-radius: 999px;
        background: var(--kertas-2); border: 1px solid var(--garis);
        font-size: .86rem; font-weight: 700; color: var(--tinta);
    }

    /* ==================================================== transfer manual */
    .daf-rekening {
        background: var(--kertas-2); border: 1px solid var(--garis);
        border-radius: 18px; padding: 20px; text-align: center;
    }
    .daf-rekening-bank {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        margin-bottom: 14px; font-size: 1.05rem;
    }
    .daf-rekening-label {
        font-size: .76rem; font-weight: 800; letter-spacing: .07em;
        text-transform: uppercase; color: var(--tinta-2); margin-bottom: 2px;
    }
    .daf-rekening-nomor {
        font-family: 'Fraunces', Georgia, serif; font-size: 1.5rem; font-weight: 900;
        color: var(--tinta); letter-spacing: .02em; margin-bottom: 2px;
    }
    .daf-rekening-nama { font-size: .95rem; margin-bottom: 14px; }
    .daf-salin {
        width: 100%; min-height: 50px; border: 0; border-radius: 13px;
        background: var(--tinta); color: #fff; font-family: inherit;
        font-size: .98rem; font-weight: 800; cursor: pointer;
    }
    .daf-salin:hover { background: var(--jingga-tua); }

    .daf-unggah {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        width: 100%; min-height: 56px; margin: 0;
        border: 2px dashed var(--jingga); border-radius: 14px;
        background: #fff; color: var(--jingga-tua);
        font-weight: 800; cursor: pointer;
    }
    .daf-unggah:hover { background: var(--kertas-2); }
    .daf-pratinjau { margin-top: 14px; text-align: center; }
    .daf-pratinjau img { max-width: 100%; border-radius: 12px; border: 1px solid var(--garis); }
    .daf-berkas { display: block; margin-top: 8px; font-size: .88rem; font-weight: 700; color: var(--tinta-2); }

    /* ==================================================== layar sempit */
    @media (max-width: 991.98px) {
        .daf-kisi { grid-template-columns: 1fr; gap: 24px; }
    }
    @media (max-width: 575.98px) {
        .daf { font-size: 17px; padding-top: 96px; }
        .daf-kartu { padding: 22px 18px 24px; border-radius: 18px; }
        .daf-ringkas, .daf-dua { grid-template-columns: 1fr; }
        .daf-diskon-baris { flex-wrap: wrap; }
        .daf-cek { width: 100%; min-height: 50px; }
    }
</style>

<section class="daf">
    <div class="container">

        <a href="{{ route('public.scopuscamp.Selengkapnya', $item->id) }}" class="daf-balik">
            <i class="fas fa-arrow-left"></i> Kembali ke rincian kelas
        </a>

        <h1 class="daf-judul">Formulir pendaftaran</h1>
        <p class="daf-anak">
            Isi empat keterangan di bawah, lalu
            {{-- Dirakit sebagai ungkapan, bukan sebagai percabangan sebaris:
                 cabang sebaris meninggalkan spasi tepat sebelum titiknya,
                 jadi yang tercetak "lanjutkan ke pembayaran ." --}}
            {{ $bayarDaring ? 'lanjutkan ke pembayaran' : 'kirim bukti transfernya' }}.
            Butuh sekitar dua menit.
        </p>

        @if (session('error'))
            <div class="daf-kartu" style="margin-top: 22px; border-color: #e3b7b1; background: #fdf1ef;">
                <p style="color: #a3281c; font-weight: 700;">{{ session('error') }}</p>
            </div>
        @endif

        <form id="formKonfirmasi" method="POST" action="{{ route('public.scopuscamp.store') }}"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="kategori_id" id="scopusCampIdvalue" value="{{ $item->id }}">

            @php
                $fMulai = \Carbon\Carbon::parse($item->mulai)->locale('id');
                $fSelesai = $item->selesai ? \Carbon\Carbon::parse($item->selesai) : null;

                /*
                 * Dihitung dari SATU kursi, seperti sebelumnya, dan seperti
                 * App\Support\HargaUmum menghitungnya di peladen. Kejanggalan
                 * itu dipertahankan apa adanya: membetulkannya di sini berarti
                 * mengubah nominal yang ditagihkan untuk alasan yang berbeda
                 * dari perkara tampilan, dan dua perubahan uang dalam satu
                 * langkah tidak bisa ditelusuri kalau salah satunya keliru.
                 */
                $jumlahPPN = ($item->ppn > 0) ? $item->harga_setelah_promo * ($item->ppn / 100) : 0;
            @endphp

            <div class="daf-kisi">

                <div class="daf-kolom-kiri">

                    {{-- RINGKASAN, bukan tujuh kotak isian.

                         Kartu ini dulu berisi tujuh <input readonly>: Batch,
                         Lokasi, Sisa Kuota, Mulai, Selesai, Biaya, dan Kode
                         Unik. Semuanya tampak seperti isian yang menunggu
                         diisi, padahal tidak satu pun bisa diubah, dan
                         peladen pun mengabaikan lima di antaranya. --}}
                    <div class="daf-kartu">
                        <div class="daf-kepala">
                            <i class="fas fa-campground"></i> Kelas yang Anda daftarkan
                        </div>

                        <dl class="daf-ringkas">
                            <div>
                                <dt>Kelas</dt>
                                <dd>{{ $item->nama }}@if ($item->nama_ke) #{{ $item->nama_ke }}@endif</dd>
                            </div>
                            <div>
                                <dt>Tanggal</dt>
                                <dd>{{ \App\Support\RentangTanggal::tulis($fMulai, $fSelesai) }}</dd>
                            </div>
                            <div>
                                <dt>Lokasi</dt>
                                <dd>{{ $item->lokasi ?: 'Akan dikabari panitia' }}</dd>
                            </div>
                            <div>
                                <dt>Biaya per orang</dt>
                                <dd>
                                    @if ($item->harga_sebelum_promo > $item->harga_setelah_promo)
                                        <s class="daf-coret">Rp {{ number_format($item->harga_sebelum_promo, 0, ',', '.') }}</s>
                                    @endif
                                    Rp {{ number_format($item->harga_setelah_promo, 0, ',', '.') }}
                                </dd>
                            </div>
                        </dl>

                        {{-- harga_setelah_promo, BUKAN $item->biaya.

                             Halaman daftarnya MENCORET `biaya` dan menjanjikan
                             harga promonya; borang ini dulu menagih angka yang
                             dicoret itu. Terukur 18 angkatan terkena — Scopus
                             Camp Padang menjanjikan Rp 5.060.000 lalu menagih
                             Rp 5.500.000. Lihat App\Support\HargaUmum. --}}
                        <input type="hidden" name="biaya" id="biaya"
                            value="{{ number_format($item->harga_setelah_promo, 0, ',', '.') }}">
                        <input type="hidden" name="kode_unik" id="kodeUnik" value="{{ old('kode_unik') }}">
                        <input type="hidden" name="jumlah_pendaftar" id="jumlahPendaftar" value="1"
                            data-sisa-kuota="{{ $item->sisa_kuota ?? 0 }}">
                        <input type="hidden" name="subtotal" id="subtotalInput" value="{{ $item->harga_setelah_promo }}">
                        <input type="hidden" name="ppn" id="jumlahPPN" value="{{ $jumlahPPN }}">
                    </div>

                    <div class="daf-kartu">
                        <div class="daf-kepala">
                            <i class="fas fa-user"></i> Data diri peserta
                        </div>

                        <div class="daf-dua">
                            <div class="daf-medan">
                                <label for="nama">Nama lengkap &amp; gelar <span class="daf-wajib">*</span></label>
                                <input type="text" name="nama" id="nama" value="{{ old('nama') }}"
                                    placeholder="John Doe, S.Kom." class="daf-isian" required>
                            </div>
                            <div class="daf-medan">
                                <label for="email">Email aktif <span class="daf-wajib">*</span></label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                    placeholder="email@domain.com" class="daf-isian"
                                    maxlength="100" minlength="5" required>
                            </div>
                        </div>

                        <div class="daf-dua" style="margin-top: 20px;">
                            <div class="daf-medan">
                                <label for="affiliasi">Asal instansi <span class="daf-wajib">*</span></label>
                                <input type="text" name="affiliasi" id="affiliasi" value="{{ old('affiliasi') }}"
                                    placeholder="Universitas / Instansi" class="daf-isian" minlength="3" required>
                            </div>
                            <div class="daf-medan">
                                <label for="telp">Nomor WhatsApp <span class="daf-wajib">*</span></label>
                                <input type="text" name="telp" id="telp" value="{{ old('telp') }}"
                                    placeholder="0812-3456-7890" class="daf-isian"
                                    inputmode="numeric" maxlength="20" minlength="8"
                                    oninput="formatPhoneNumber(this)" required>
                            </div>
                        </div>
                    </div>

                    @unless ($bayarDaring)
                        {{-- Kartu rekening BIASA, bukan akordeon.

                             Nomor rekeningnya dulu duduk di dalam akordeon
                             yang memang terbuka secara bawaan — tetapi
                             tombolnya tetap bisa ditekan, dan sekali tertekan
                             nomornya menghilang. Di halaman yang satu-satunya
                             tujuannya mentransfer uang, tidak ada alasan
                             menyembunyikan nomor rekening di balik tombol
                             yang bisa salah tekan. --}}
                        <div class="daf-kartu">
                            <div class="daf-kepala">
                                <i class="fas fa-university"></i> Transfer ke rekening ini
                            </div>

                            <div class="daf-rekening">
                                <div class="daf-rekening-bank">
                                    <img src="{{ asset('assets/img/bri.jpg') }}" alt="Logo Bank BRI"
                                        style="width: 48px; height: auto; border-radius: 5px;">
                                    <strong>Bank BRI</strong>
                                </div>

                                <p class="daf-rekening-label">Nomor rekening</p>
                                <p id="nomor-rekening" class="daf-rekening-nomor">216401000467563</p>
                                <p class="daf-rekening-nama">a.n. <strong>Rumah Scopus Akademi</strong></p>

                                <button type="button" onclick="copyToClipboard('nomor-rekening')" class="daf-salin">
                                    <i class="fas fa-copy"></i> Salin nomor rekening
                                </button>
                            </div>

                            <div class="daf-medan" style="margin-top: 22px;">
                                <label for="gambar">Unggah bukti transfer <span class="daf-wajib">*</span></label>
                                <input type="file" name="gambar" id="gambar" style="display: none;"
                                    accept="image/*" required>
                                <label for="gambar" class="daf-unggah">
                                    <i class="fas fa-cloud-upload-alt"></i> Pilih gambar
                                </label>
                                @error('gambar')
                                    <span class="daf-pesan gagal">{{ $message }}</span>
                                @enderror

                                {{-- Disembunyikan selama belum ada yang dipilih: kotak
                                     abu-abu kosong tepat di bawah tombol unggah terbaca
                                     sebagai gambar yang gagal dimuat. --}}
                                <div class="daf-pratinjau" id="wadahPratinjau" style="display: none;">
                                    <div id="imagePreview"></div>
                                    <span id="file-selected" class="daf-berkas"></span>
                                </div>
                            </div>
                        </div>
                    @endunless
                </div>

                <div class="daf-kolom-kanan">
                    <div class="daf-kartu">
                        <div class="daf-kepala">
                            <i class="fas fa-receipt"></i> Rincian biaya
                        </div>

                        <div class="daf-medan">
                            <label for="kodeDiskon">
                                Punya kode diskon? <span class="daf-opsional">— boleh dikosongkan</span>
                            </label>
                            <div class="daf-diskon-baris">
                                <input type="text" name="kode_diskon_input" id="kodeDiskon" class="daf-isian"
                                    placeholder="Ketik kode diskon" autocomplete="off">
                                <button type="button" class="daf-cek" id="btnCekDiskon" data-id="{{ $item->id }}">
                                    <i class="fas fa-tag"></i> Cek
                                </button>
                            </div>
                            <input type="hidden" name="kode_diskon" id="hiddenKodeDiskon">
                            <small id="diskonMessage" class="daf-pesan ok"></small>
                        </div>

                        <div style="margin-top: 22px;">
                            <div class="daf-baris">
                                <span>Biaya kelas</span>
                                <b>Rp {{ number_format($item->harga_setelah_promo, 0, ',', '.') }}</b>
                            </div>

                            {{-- Kode unik dipajang DI SEBELAH totalnya, bukan di antara
                                 keterangan kelas: ia bagian dari nominal yang harus
                                 dibayar. Diperbarui JavaScript bersama totalnya; isian
                                 tersembunyi #kodeUnik tetap yang dikirim ke peladen. --}}
                            <div class="daf-baris">
                                <span>Kode unik</span>
                                <b>Rp <span id="kodeUnikTeks">0</span></b>
                            </div>

                            @if ($jumlahPPN > 0)
                                <div class="daf-baris">
                                    <span>PPN</span>
                                    <b>Rp {{ number_format($jumlahPPN, 0, ',', '.') }}</b>
                                </div>
                            @endif

                            {{-- Disembunyikan selama belum ada potongan.

                                 "Diskon — Rp 0" yang selalu terpampang di atas
                                 totalnya terbaca sebagai potongan yang GAGAL
                                 dipakai, bukan sebagai potongan yang memang
                                 tidak diminta. --}}
                            <div class="daf-baris potong" id="formDiskon" style="display: none;">
                                <span>Diskon</span>
                                <b>&minus; Rp <span id="diskonText">0</span></b>
                                <input type="hidden" name="nominal_diskon" id="potonganDiskon" value="0">
                            </div>

                            <div class="daf-total">
                                <span>Total bayar</span>
                                <b>Rp <span id="TotalPembayaran">0</span></b>
                                <input type="hidden" name="total_pembayaran" id="totalPembayaranInput">
                            </div>
                        </div>

                        <input type="hidden" name="total_keseluruhan_pembayaran" id="totalKeseluruhanPembayaranhidden">

                        <button type="submit" class="daf-kirim" id="btnSubmit"
                            data-bayar-daring="{{ $bayarDaring ? '1' : '0' }}">
                            @if ($bayarDaring)
                                <i class="fas fa-lock"></i> Lanjut ke pembayaran
                            @else
                                <i class="fas fa-paper-plane"></i> Kirim pendaftaran
                            @endif
                        </button>

                        @if ($bayarDaring)
                            <div class="daf-aman">
                                <i class="fas fa-shield-alt"></i>
                                <span>Pembayaran diproses DOKU. Setelah membayar, status
                                    pendaftaran Anda berpindah sendiri — tidak perlu
                                    mengirim bukti transfer.</span>
                            </div>

                            <p style="margin-top: 14px; font-size: .88rem; font-weight: 700; color: var(--tinta);">
                                Bisa dibayar lewat
                            </p>
                            {{-- QRIS dan Virtual Account SAJA, keputusan
                                 pemiliknya 23 Okt 2026.

                                 Daftar ini cuma janji: yang menentukan pilihan
                                 sebenarnya halaman DOKU. Jadi pembatasannya
                                 ikut dikirim bersama tagihannya — lihat
                                 Doku::KANAL_QRIS_DAN_VA — supaya yang dibaca
                                 di sini sama dengan yang ditemukan di sana. --}}
                            <div class="daf-kanal">
                                <span>QRIS</span>
                                <span>Virtual Account</span>
                            </div>
                        @else
                            <div class="daf-aman">
                                <i class="fas fa-clock"></i>
                                <span>Panitia memeriksa bukti transfer Anda, lalu
                                    mengirim kabarnya lewat email.</span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </form>

    </div>
</section>

<script>
    (function () {
        var borang = document.getElementById('formKonfirmasi');
        var tombol = document.getElementById('btnSubmit');

        /*
         * Jalur bayarnya dibaca dari BORANGNYA, bukan ditulis ulang di sini.
         *
         * Yang menentukan satu-satunya jawaban ada di pengendali; menyalinnya
         * ke JavaScript berarti dua sumber jawaban, dan yang kedua akan
         * ketinggalan diam-diam — borang menuntut bukti transfer sementara
         * peladen mengantar orangnya ke DOKU.
         */
        var bayarDaring = tombol.dataset.bayarDaring === '1';

        var gambar = document.getElementById('gambar');

        borang.addEventListener('submit', function (e) {
            e.preventDefault();

            /*
             * Bukti transfer diperiksa di SINI, di dalam pengirimnya.
             *
             * Dulu pemeriksaannya menempel sebagai klik tersendiri pada
             * tombolnya, dan tombolnya selalu ada — jadi begitu jalur
             * unggahnya hilang (lewat DOKU), pemeriksaan itu akan menolak
             * SETIAP pendaftaran dengan "bukti belum diunggah" untuk berkas
             * yang memang tidak diminta.
             */
            if (! bayarDaring && gambar && gambar.files.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Bukti belum diunggah',
                    text: 'Silakan unggah bukti pembayaran Anda dulu.'
                });
                return;
            }

            Swal.fire({
                title: 'Sudah benar semua?',
                text: bayarDaring
                    ? 'Setelah ini Anda dibawa ke halaman pembayaran.'
                    : 'Pastikan data dan bukti transfernya sudah sesuai.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e76d20',
                confirmButtonText: bayarDaring ? 'Ya, lanjut bayar' : 'Ya, kirim',
                cancelButtonText: 'Periksa lagi',
                reverseButtons: true
            }).then(function (hasil) {
                if (! hasil.isConfirmed) {
                    return;
                }

                tombol.disabled = true;
                tombol.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' +
                    (bayarDaring ? 'Menyiapkan pembayaran...' : 'Mengirim...');

                Swal.fire({
                    title: bayarDaring ? 'Menyiapkan pembayaran' : 'Memproses pendaftaran',
                    html: 'Mohon tunggu sebentar, jangan tutup halaman ini.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: function () { Swal.showLoading(); }
                });

                setTimeout(function () { borang.submit(); }, 250);
            });
        });
    })();
</script>

<script>
    document.getElementById('btnCekDiskon').addEventListener('click', function () {
        var id = this.dataset.id;
        var kode = document.getElementById('kodeDiskon').value.trim();

        var pesan = document.getElementById('diskonMessage');
        var tersembunyi = document.getElementById('hiddenKodeDiskon');
        var potongan = document.getElementById('potonganDiskon');
        var teksDiskon = document.getElementById('diskonText');

        function kosongkan() {
            tersembunyi.value = '';
            potongan.value = 0;
            teksDiskon.textContent = '0';
            document.getElementById('formDiskon').style.display = 'none';
            hitungTotalPembayaran();
        }

        if (! kode) {
            kosongkan();
            pesan.textContent = '';
            Swal.fire({
                icon: 'warning',
                title: 'Kode masih kosong',
                text: 'Ketik dulu kode diskonnya, atau lewati bagian ini.'
            });
            return;
        }

        fetch('/Scopus-Camp/cek-kode-diskon/' + encodeURIComponent(id) +
                '?kode_diskon=' + encodeURIComponent(kode))
            .then(function (jawab) { return jawab.json(); })
            .then(function (data) {
                if (data.status === 'success') {
                    var nominal = Number(data.casted_nominal_diskon) || 0;
                    tersembunyi.value = kode;
                    potongan.value = nominal;
                    teksDiskon.textContent = nominal.toLocaleString('id-ID');
                    document.getElementById('formDiskon').style.display = nominal > 0 ? '' : 'none';
                    pesan.textContent = 'Potongan Rp ' + nominal.toLocaleString('id-ID') + ' sudah dipakai.';
                    pesan.className = 'daf-pesan ok';
                    hitungTotalPembayaran();
                    Swal.fire({
                        icon: 'success',
                        title: 'Kode diskon dipakai',
                        timer: 1800,
                        showConfirmButton: false
                    });
                } else {
                    kosongkan();
                    pesan.textContent = data.message || 'Kode itu tidak berlaku.';
                    pesan.className = 'daf-pesan gagal';
                    Swal.fire({
                        icon: 'error',
                        title: 'Kode tidak berlaku',
                        text: data.message || 'Kodenya tidak ditemukan.'
                    });
                }
            })
            .catch(function () {
                kosongkan();
                pesan.textContent = '';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal memeriksa',
                    text: 'Kode diskonnya tidak bisa diperiksa sekarang.'
                });
            });
    });
</script>

<script>
    var jumlahPendaftarInput = document.getElementById('jumlahPendaftar');
    var biayaInput = document.getElementById('biaya');
    var kodeUnikInput = document.getElementById('kodeUnik');
    var totalPembayaranDisplay = document.getElementById('TotalPembayaran');
    var potonganDiskonInput = document.getElementById('potonganDiskon');

    function formatRupiah(angka) {
        return Number(angka).toLocaleString('id-ID');
    }

    /*
     * Rentangnya SAMA untuk semua layanan: 500-1500.
     *
     * Dulu halaman ini membuat 1-100, sementara layanan lain memakai rentang
     * yang berbeda-beda. Nominal yang penanda uniknya bergantung pada layanan
     * mana yang didaftar membuat panitia harus mengingat aturan berbeda untuk
     * tiap layanan saat mencocokkan mutasi rekening.
     *
     * Angkanya diambil dari PendaftaranSemuaLayanan::KODE_UNIK supaya tidak
     * ada dua tempat yang bisa berselisih.
     */
    function generateUniqueCode() {
        var r = @json(\App\Support\PendaftaranSemuaLayanan::KODE_UNIK);

        return Math.floor(Math.random() * (r[1] - r[0] + 1)) + r[0];
    }

    function hitungTotalPembayaran() {
        var jumlah = parseInt(jumlahPendaftarInput.value) || 0;
        var biaya = parseInt(biayaInput.value.replace(/\D/g, '')) || 0;
        var potonganDiskon = parseInt(String(potonganDiskonInput.value).replace(/\D/g, '')) || 0;
        var jumlahPPN = parseInt(document.getElementById('jumlahPPN').value) || 0;

        if (jumlah <= 0) {
            kodeUnikInput.value = '0';
            document.getElementById('kodeUnikTeks').textContent = '0';
            totalPembayaranDisplay.textContent = '0';
            document.getElementById('totalPembayaranInput').value = 0;
            return;
        }

        var kodeUnik = generateUniqueCode();
        var total = (jumlah * biaya) + kodeUnik - potonganDiskon + jumlahPPN;

        kodeUnikInput.value = formatRupiah(kodeUnik);
        // Isian #kodeUnik tersembunyi; angkanya tetap harus terlihat orang
        // karena ikut menentukan nominal yang dibayar.
        document.getElementById('kodeUnikTeks').textContent = formatRupiah(kodeUnik);
        totalPembayaranDisplay.textContent = formatRupiah(total);
        document.getElementById('totalPembayaranInput').value = total;
    }

    /*
     * Dihitung SEKARANG JUGA, bukan menunggu DOMContentLoaded.
     *
     * Tata letak publik ini memuat sembilan skrip yang menahan penguraian
     * halaman, salah satunya dari CDN di luar. Terukur: empat detik sesudah
     * halaman dibuka, document.readyState masih "loading" — dan selama itu
     * totalnya terbaca "Rp 0" pada halaman yang sedang menagih lima juta
     * rupiah. Tidak ada galat; yang melihatnya menyangka kelasnya gratis
     * atau halamannya rusak.
     *
     * Aman dipanggil di sini: seluruh unsur yang dibacanya digambar di atas
     * blok ini, jadi semuanya sudah ada saat baris ini jalan.
     */
    hitungTotalPembayaran();
</script>

@unless ($bayarDaring)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        /*
         * Kotak pratinjaunya DIKOSONGKAN, bukan diisi gambar cadangan.
         *
         * Baris ini dulu menyuntikkan <img src=".../meme/no-image.jpg">, dan
         * berkas itu TIDAK ADA di proyek ini — jadi yang tampil sebelum orang
         * memilih apa pun adalah ikon gambar rusak bertuliskan "No Image".
         * Di halaman yang meminta bukti transfer, gambar rusak terbaca
         * sebagai unggahannya yang gagal.
         */
        document.getElementById('imagePreview').innerHTML = '';
        document.getElementById('wadahPratinjau').style.display = 'none';
    });

    document.getElementById('gambar').addEventListener('change', function (peristiwa) {
        var berkas = peristiwa.target.files[0];

        if (! berkas) {
            return;
        }

        var ukuran = berkas.size / 1024;
        var diizinkan = ['image/png', 'image/jpeg', 'image/jpg'];

        if (diizinkan.indexOf(berkas.type) === -1) {
            Swal.fire({
                icon: 'error',
                title: 'Format tidak didukung',
                text: 'Yang bisa diunggah hanya PNG, JPEG, dan JPG.'
            });
            kosongkanPratinjau();
            return;
        }

        if (ukuran > 2000) {
            Swal.fire({
                icon: 'error',
                title: 'Berkasnya terlalu besar',
                text: 'Ukuran gambar paling besar 2 MB.'
            });
            kosongkanPratinjau();
            return;
        }

        document.getElementById('file-selected').textContent =
            berkas.name + ' (' + ukuran.toFixed(0) + ' KB)';

        var pembaca = new FileReader();
        pembaca.onload = function () {
            document.getElementById('imagePreview').innerHTML =
                '<img src="' + pembaca.result + '" alt="Pratinjau bukti transfer yang dipilih">';
            document.getElementById('wadahPratinjau').style.display = '';
        };
        pembaca.readAsDataURL(berkas);
    });

    function kosongkanPratinjau() {
        document.getElementById('imagePreview').innerHTML = '';
        document.getElementById('wadahPratinjau').style.display = 'none';
        document.getElementById('file-selected').textContent = '';
        document.getElementById('gambar').value = '';
    }

    function copyToClipboard(id) {
        var teks = document.getElementById(id).innerText.replace(/\D/g, '');

        navigator.clipboard.writeText(teks).then(function () {
            Swal.fire({
                icon: 'success',
                title: 'Nomor rekening disalin',
                text: teks,
                timer: 2000,
                showConfirmButton: false
            });
        });
    }
</script>
@endunless

<script>
    function formatPhoneNumber(isian) {
        var nomor = isian.value.replace(/\D/g, '');

        if (nomor.length === 11) {
            nomor = nomor.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
        } else if (nomor.length === 12) {
            nomor = nomor.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
        } else if (nomor.length === 13) {
            nomor = nomor.replace(/(\d{5})(\d{4})(\d{4})/, '$1-$2-$3');
        }

        isian.value = nomor;
    }
</script>
@stop
