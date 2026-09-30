@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Tarif Layanan | MIS
@stop

{{-- Pemberitahuan lewat toast bersama, bukan kotak alert Bootstrap. --}}
@include('partials.toast-flash')

@push('gaya')
<style>
    /*
     * Bahasa rupanya sama dengan Profil dan Data Pelanggan — kartu
     * .mis-bagian, ubin ikon .mis-medali, lencana .mis-pil, tombol
     * .mis-tombol. Acuannya docs/panduan-ui-mis.md.
     */

    /* Kepala bagian: disalin dari .pel-kepala di Data Pelanggan.
       .mis-kartu-kepala TIDAK dipakai — ia ber-justify-content space-between,
       yang melempar judulnya ke tepi kanan begitu anaknya cuma ubin dan teks. */
    .tar-kepala {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        column-gap: 12px;
        row-gap: 2px;
        align-items: center;
        margin-bottom: 12px;
    }

    .tar-kepala > .mis-medali { grid-row: 1 / span 2; align-self: center; }

    .tar-kepala-judul {
        grid-column: 2; grid-row: 1;
        margin: 0; line-height: 1.25;
        font-size: .92rem; font-weight: 800; color: var(--mis-tinta);
    }

    .tar-kepala-sub {
        grid-column: 2; grid-row: 2;
        margin: 0;
        /* Angka relatif, bukan warisan line-height 28px mutlak dari layout. */
        line-height: 1.45; font-size: .75rem; color: var(--mis-tinta-3);
    }

    /* ------------------------------------------------- kisi kartu layanan */

    /*
     * auto-fit + minmax, bukan jumlah kolom yang dipatok: layanan bisa
     * bertambah kapan saja, dan kisinya menyesuaikan sendiri tanpa ada titik
     * putus baru yang harus dijaga.
     */
    .tar-kisi {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: var(--mis-jarak);
        margin-bottom: var(--mis-jarak);
        /*
         * start, BUKAN stretch bawaan kisi. Dengan stretch, membuka borang di
         * satu kartu menarik SEISI barisnya jadi setinggi itu — terukur 294px
         * jadi 590px — dan kartu di sebelahnya menyisakan petak putih hampir
         * 300px di dalam dirinya sendiri. Kartu yang berhenti di ujung isinya
         * jauh lebih enak dilihat daripada kartu yang dipaksa rata bawah.
         */
        align-items: start;
    }

    .tar-kartu {
        display: flex;
        flex-direction: column;
        padding: 18px;
        border: 1px solid var(--mis-garis);
        border-radius: var(--mis-radius);
        background: #fff;
        box-shadow: var(--mis-bayang);
    }

    /* Yang belum punya tarif ditandai tepi kuning, bukan disembunyikan:
       layanan yang terlewat harus kelihatan justru karena terlewat. */
    .tar-kartu.kosong {
        border-color: #fde68a;
        background: linear-gradient(180deg, #fffbeb 0%, #fff 55%);
    }

    .tar-kartu-kepala {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        column-gap: 12px;
        row-gap: 3px;
        align-items: center;
    }

    .tar-kartu-kepala > .mis-medali { grid-row: 1 / span 2; align-self: center; }

    .tar-nama {
        grid-column: 2; grid-row: 1;
        margin: 0; line-height: 1.25;
        font-size: .92rem; font-weight: 800; color: var(--mis-tinta);
        overflow-wrap: anywhere;
    }

    .tar-varian { grid-column: 2; grid-row: 2; display: flex; flex-wrap: wrap; gap: 6px; }

    .tar-harga {
        margin: 14px 0 0;
        line-height: 1.1;
        font-size: 1.55rem; font-weight: 800; color: var(--mis-tinta);
        overflow-wrap: anywhere;
    }

    .tar-satuan {
        margin: 3px 0 0;
        line-height: 1.45; font-size: .75rem; color: var(--mis-tinta-3);
    }

    /* Rincian PPN hanya muncul kalau memang dikenakan — baris "PPN 0%" tidak
       memberi tahu apa pun dan cuma menambah satu hal untuk dibaca. */
    .tar-rinci {
        display: flex; align-items: baseline; justify-content: space-between;
        gap: 10px; margin-top: 10px; padding-top: 10px;
        border-top: 1px dashed var(--mis-garis);
        font-size: .8rem; color: var(--mis-tinta-2);
    }

    .tar-rinci strong { font-size: .92rem; font-weight: 800; color: #047857; }

    /* --------------------------------------------------------- fasilitas */

    .tar-fasilitas {
        margin: 12px 0 0; padding: 0; list-style: none;
        display: grid; gap: 5px;
    }

    .tar-fasilitas li {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 8px;
        line-height: 1.45; font-size: .78rem; color: var(--mis-tinta-2);
    }

    .tar-fasilitas .fas { font-size: 11px !important; color: #10b981; margin-top: 3px; }

    .tar-kosong-teks {
        margin: 12px 0 0; line-height: 1.45;
        font-size: .78rem; color: var(--mis-tinta-4);
    }

    /* ------------------------------------------------------ borang setel */

    /* Borangnya terlipat di dalam kartunya sendiri: tujuh kartu dengan tujuh
       borang terbuka sekaligus menuntut menggulung jauh hanya untuk melihat
       harga yang berlaku — padahal itu yang paling sering dicari.

       Tanpa margin-top: auto — kartunya setinggi isinya sendiri sejak kisinya
       memakai align-items: start, jadi tidak ada ruang sisa untuk didorong. */
    .tar-setel { padding-top: 14px; }

    .tar-setel > summary {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        height: 38px; padding: 0 14px;
        border: 1px solid var(--mis-garis); border-radius: 12px;
        background: #fff;
        font-size: .8rem; font-weight: 700; color: var(--mis-tinta-2);
        cursor: pointer; list-style: none;
        transition: all .2s ease;
    }

    .tar-setel > summary::-webkit-details-marker { display: none; }

    .tar-setel > summary:hover { border-color: #c7d2fe; color: #4f46e5; }

    .tar-setel[open] > summary { margin-bottom: 12px; }

    .tar-borang { display: grid; gap: 10px; }

    .tar-dua { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr); gap: 10px; }

    /* Awalan "Rp" dan akhiran "%" menempel di dalam kotaknya: keduanya
       satuan, bukan keterangan yang perlu labelnya sendiri. */
    .tar-isian { position: relative; }
    .tar-isian .form-control-modern { padding-left: 40px; }
    .tar-isian.persen .form-control-modern { padding-left: 14px; padding-right: 36px; }

    .tar-tanda {
        position: absolute; bottom: 0;
        display: grid; place-items: center;
        width: 30px; height: 42px;
        font-size: .82rem; font-weight: 700; color: var(--mis-tinta-4);
        pointer-events: none;
    }

    .tar-tanda.kiri { left: 8px; }
    .tar-tanda.kanan { right: 6px; }

    .tar-area {
        /* width 100% WAJIB, tidak boleh mengandalkan induknya: lebar bawaan
           textarea datang dari atribut cols (20 aksara). Kotak fasilitas
           selamat karena kebetulan ada di dalam .mis-isian yang meregangkan
           anaknya; kotak cetakan di bawah TIDAK, dan tanpa aturan ini ia
           menyempit jadi satu kolom sempit. */
        width: 100%;
        min-height: 92px; padding: 10px 13px;
        border: 1px solid var(--mis-garis); border-radius: 11px;
        background: #fff;
        line-height: 1.5; font-size: .82rem; color: var(--mis-tinta);
        resize: vertical;
    }

    .tar-area:focus { outline: none; border-color: #a5b4fc; box-shadow: 0 0 0 3px rgba(99, 102, 241, .5); }

    .tar-pendek { min-height: 62px; }
    .tar-panjang { min-height: 220px; font-family: ui-monospace, "SF Mono", Menlo, monospace; font-size: .76rem; }

    /* Cetakan deskripsinya terlipat di dalam borang yang sudah terlipat:
       yang paling sering diubah tarif dan fasilitasnya, bukan teks yang
       ditulis sekali lalu dibiarkan bertahun-tahun. */
    .tar-cetakan > summary {
        display: flex; align-items: center; gap: 8px;
        padding: 9px 12px;
        border: 1px dashed var(--mis-garis); border-radius: 11px;
        background: #f8fafc;
        font-size: .78rem; font-weight: 700; color: var(--mis-tinta-2);
        cursor: pointer; list-style: none;
    }

    .tar-cetakan > summary::-webkit-details-marker { display: none; }
    .tar-cetakan[open] > summary { margin-bottom: 10px; }

    .tar-penanda {
        margin: 8px 0 0; padding: 10px 12px; list-style: none;
        display: grid; gap: 5px;
        border-radius: 11px; background: #f8fafc;
    }

    .tar-penanda li {
        display: grid;
        /* minmax(0, ...) supaya nama penanda yang panjang tetap bisa menyusut
           dan tidak mendorong keterangannya keluar kartu di layar sempit. */
        grid-template-columns: minmax(0, auto) minmax(0, 1fr);
        gap: 8px; align-items: baseline;
        line-height: 1.45; font-size: .72rem; color: var(--mis-tinta-3);
    }

    .tar-penanda code {
        padding: 1px 6px; border-radius: 6px;
        background: #ede9fe; color: #6d28d9;
        font-size: .72rem; white-space: nowrap;
    }

    .tar-pratinjau {
        display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
        font-size: .76rem; color: var(--mis-tinta-3);
    }

    /* ----------------------------------------------------------- riwayat */

    .tar-riwayat-nilai { margin: 0; line-height: 1.25; font-size: .86rem; font-weight: 700; color: var(--mis-tinta); }
    .tar-riwayat-ket { margin: 1px 0 0; line-height: 1.4; font-size: .73rem; color: var(--mis-tinta-3); }
    .tar-aksi { display: flex; align-items: center; justify-content: flex-end; gap: 6px; }

    /* --------------------------------------------------------- responsif */

    @media (max-width: 767.98px) {
        .tar-kisi { grid-template-columns: minmax(0, 1fr); }
        .tar-dua { grid-template-columns: minmax(0, 1fr); }
        .tar-harga { font-size: 1.4rem; }

        /* Tiap baris riwayat jadi kartu tersendiri, seperti daftar pelanggan:
           garis 1px terlalu sepi untuk memisahkan lima keterangan berlabel. */
        .mis-tabel-kartu.tar-tabel tbody {
            display: flex; flex-direction: column; gap: 8px;
        }

        .mis-tabel-kartu.tar-tabel tbody tr {
            border: 1px solid var(--mis-garis); border-radius: 13px; background: #f8fafc;
        }
    }
</style>
@endpush

@section('content')
<div class="main-content mis-badan">
    <section class="section">

        {{-- ------------------------------------------------ kepala --}}
        <div class="mis-kepala">
            <span class="mis-medali mis-hijau" aria-hidden="true"><i class="fas fa-money-bill-wave"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Tarif layanan</h1>
                <p class="mis-sub">Harga dan fasilitas seluruh layanan jasa, disetel sekali di sini.</p>
            </div>
            <div class="mis-kepala-aksi">
                <span class="mis-pil {{ $adaTarif === $totalKartu ? 'mis-pil-hijau' : 'mis-pil-kuning' }}">
                    <i class="fas {{ $adaTarif === $totalKartu ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                    {{ $adaTarif }} dari {{ $totalKartu }} tarif sudah disetel
                </span>
            </div>
        </div>

        {{-- --------------------------------------- kartu tiap layanan --}}
        <div class="tar-kisi">
            @foreach ($kartu as $k)
                @php($t = $k['tarif'])
                <div class="tar-kartu {{ $t ? '' : 'kosong' }}">
                    <div class="tar-kartu-kepala">
                        <span class="mis-medali {{ $k['warna'] }}" aria-hidden="true">
                            <i class="fas {{ $k['ikon'] }}"></i>
                        </span>
                        <h2 class="tar-nama">{{ $k['nama'] }}</h2>
                        <div class="tar-varian">
                            @if ($k['namaVarian'])
                                <span class="mis-pil mis-pil-ungu">
                                    <i class="fas fa-map-marker-alt"></i> {{ $k['namaVarian'] }}
                                </span>
                            @endif
                            @if ($t && $t->ppn_persen > 0)
                                <span class="mis-pil mis-pil-kuning">
                                    <i class="fas fa-percent"></i> PPN {{ $t->ppn_persen }}%
                                </span>
                            @endif
                        </div>
                    </div>

                    <p class="tar-harga">{{ $t ? $t->tarif_terbaca : 'Belum disetel' }}</p>
                    <p class="tar-satuan">
                        {{ $k['satuan'] }}
                        @if ($t)
                            &middot; disetel {{ optional($t->updated_at)->locale('id')->translatedFormat('d M Y') }}
                        @endif
                    </p>

                    @if ($t && $t->ppn_persen > 0)
                        <div class="tar-rinci">
                            <span>Dibayar pelanggan</span>
                            <strong>Rp {{ number_format($t->total_dibayar, 0, ',', '.') }}</strong>
                        </div>
                    @endif

                    @if ($t && $t->daftar_fasilitas)
                        <ul class="tar-fasilitas">
                            @foreach ($t->daftar_fasilitas as $f)
                                <li>
                                    <i class="fas fa-check" aria-hidden="true"></i>
                                    <span>{{ $f }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="tar-kosong-teks">
                            {{ $t ? 'Fasilitasnya belum diisi.' : 'Tarifnya belum pernah disetel, jadi borang pemesanan menampilkan harga kosong.' }}
                        </p>
                    @endif

                    @if ($bolehUbah)
                        <details class="tar-setel">
                            <summary>
                                <i class="fas fa-edit mis-ikon-ungu" aria-hidden="true"></i>
                                {{ $t ? 'Ubah tarif & fasilitas' : 'Setel tarif' }}
                            </summary>

                            <form method="POST" action="{{ route('account.Clinik-Scopus-Biaya-Persesi.simpan') }}"
                                class="tar-borang" data-tarif-borang>
                                @csrf
                                <input type="hidden" name="layanan" value="{{ $k['layanan'] }}">
                                <input type="hidden" name="varian" value="{{ $k['varian'] }}">
                                {{-- Diisi skrip saat angkanya TIDAK berubah: membetulkan
                                     salah ketik tidak boleh meninggalkan jejak seolah
                                     harganya pernah naik. --}}
                                <input type="hidden" name="perbaiki" value="" data-perbaiki
                                    data-acuan="{{ $t?->getKey() }}">

                                <div class="tar-dua">
                                    <div class="mis-isian tar-isian">
                                        <label class="mis-label" for="tar-biaya-{{ $loop->index }}">Tarif</label>
                                        <span class="tar-tanda kiri" aria-hidden="true">Rp</span>
                                        <input type="text" class="form-control-modern" id="tar-biaya-{{ $loop->index }}"
                                            name="biaya_persesi" inputmode="numeric" autocomplete="off" required
                                            data-biaya data-awal="{{ $t ? (int) $t->biaya_persesi : '' }}"
                                            value="{{ $t ? number_format((int) $t->biaya_persesi, 0, ',', '.') : '' }}"
                                            placeholder="0">
                                    </div>

                                    <div class="mis-isian tar-isian persen">
                                        <label class="mis-label" for="tar-ppn-{{ $loop->index }}">PPN</label>
                                        <span class="tar-tanda kanan" aria-hidden="true">%</span>
                                        <input type="number" class="form-control-modern" id="tar-ppn-{{ $loop->index }}"
                                            name="ppn" min="0" max="100" inputmode="numeric" data-ppn
                                            value="{{ $t && $t->ppn !== null ? $t->ppn_persen : '' }}" placeholder="0">
                                    </div>
                                </div>

                                <div class="mis-isian">
                                    <label class="mis-label" for="tar-fas-{{ $loop->index }}">
                                        Fasilitas yang didapat
                                    </label>
                                    <textarea class="tar-area" id="tar-fas-{{ $loop->index }}" name="fasilitas"
                                        rows="5" placeholder="Tempel teks pengumuman di sini, atau ketik satu fasilitas per baris&#10;Sertifikat&#10;Konsumsi selama acara">{{ $t ? implode("\n", $t->daftar_fasilitas) : '' }}</textarea>
                                    <p class="mis-bantuan">
                                        Boleh <strong>ditempel utuh</strong> dari teks pengumuman —
                                        yang diambil hanya baris di bawah judul &ldquo;Fasilitas&rdquo;,
                                        nomornya dibuang sendiri. Atau ketik biasa, satu baris satu fasilitas.
                                        Dipakai ulang saat membuat angkatan baru, jadi tidak perlu diketik lagi.
                                    </p>
                                </div>

                                <div class="mis-isian">
                                    <label class="mis-label" for="tar-keg-{{ $loop->index }}">
                                        Kegiatan utama
                                    </label>
                                    <textarea class="tar-area" id="tar-keg-{{ $loop->index }}" name="kegiatan"
                                        rows="4" placeholder="Boleh ditempel utuh, atau satu kegiatan per baris">{{ $t ? implode("\n", $t->daftar_kegiatan) : '' }}</textarea>
                                    <p class="mis-bantuan">
                                        Sama seperti fasilitas — tempel teks pengumuman, yang diambil
                                        baris di bawah judul &ldquo;Kegiatan&rdquo;.
                                    </p>
                                </div>

                                <div class="mis-isian">
                                    <label class="mis-label" for="tar-kontak-{{ $loop->index }}">
                                        Kontak panitia
                                    </label>
                                    <textarea class="tar-area tar-pendek" id="tar-kontak-{{ $loop->index }}"
                                        name="kontak" rows="2"
                                        placeholder="📞 Kumala: 0889-8356-7819">{{ $t?->kontak }}</textarea>
                                    <p class="mis-bantuan">
                                        Berganti tiap beberapa bulan. Diubah di sini sekali, seluruh
                                        angkatan berikutnya ikut.
                                    </p>
                                </div>

                                <details class="tar-cetakan">
                                    <summary>
                                        <i class="fas fa-file-alt mis-ikon-biru" aria-hidden="true"></i>
                                        Cetakan deskripsi angkatan
                                        @if ($t?->ada_cetakan)
                                            <span class="mis-pil mis-pil-hijau">
                                                <i class="fas fa-check"></i> sudah ada
                                            </span>
                                        @else
                                            <span class="mis-pil mis-pil-abu">belum diisi</span>
                                        @endif
                                    </summary>

                                    <textarea class="tar-area tar-panjang" name="template_deskripsi"
                                        rows="10" spellcheck="false"
                                        placeholder="Teks pengumuman yang dipakai ulang tiap angkatan">{{ $t?->template_deskripsi }}</textarea>

                                    <p class="mis-bantuan">
                                        Ditulis sekali, dipakai semua angkatan. Bagian yang berganti
                                        tiap angkatan cukup ditulis sebagai penanda di bawah ini —
                                        sistem yang mengisinya.
                                    </p>

                                    <ul class="tar-penanda">
                                        @foreach (\App\Support\PerakitDeskripsi::PENANDA as $kode => $arti)
                                            <li>
                                                <code>{{ $kode }}</code>
                                                <span>{{ $arti }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>

                                <div class="tar-pratinjau" aria-live="polite">
                                    <i class="fas fa-calculator mis-ikon-ungu" aria-hidden="true"></i>
                                    <span data-pratinjau>Pelanggan membayar <strong>—</strong></span>
                                </div>

                                <button type="submit" class="mis-tombol mis-tombol-ungu" data-simpan>
                                    <i class="fas fa-save"></i> Berlakukan
                                </button>
                            </form>
                        </details>
                    @endif
                </div>
            @endforeach
        </div>

        @unless ($bolehUbah)
            <p class="mis-bantuan" style="margin-bottom: var(--mis-jarak);">
                <i class="fas fa-lock mis-ikon-kuning"></i>
                Hanya administrator yang boleh mengubah tarif.
            </p>
        @endunless

        {{-- ----------------------------------------------- riwayat --}}
        <div class="mis-bagian">
            <div class="tar-kepala">
                <span class="mis-medali kecil mis-biru" aria-hidden="true"><i class="fas fa-history"></i></span>
                <h2 class="tar-kepala-judul">Tarif sebelumnya</h2>
                <p class="tar-kepala-sub">Disimpan karena pesanan yang sudah terjadi memakai harga yang berlaku saat itu.</p>
            </div>

            @if ($riwayat->isEmpty())
                <div class="mis-kosong">
                    <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-history"></i></span>
                    <p class="mis-kosong-judul">Belum ada tarif lama</p>
                    <p class="mis-kosong-teks">Tarif yang diganti akan tersimpan di sini, lengkap dengan tanggalnya.</p>
                </div>
            @else
                <div class="mis-tabel-bungkus">
                    <table class="mis-tabel mis-tabel-kartu tar-tabel">
                        <thead>
                            <tr>
                                <th>Layanan</th>
                                <th>Tarif</th>
                                <th>PPN</th>
                                <th>Dipakai</th>
                                <th>Disetel</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($riwayat as $item)
                                @php($tentang = \App\ClinikScopusBiayaPersesi::LAYANAN[$item->layanan] ?? null)
                                <tr>
                                    <td class="mis-td-utama">
                                        <span class="mis-sel-utama">
                                            <span class="mis-medali kecil mis-abu" aria-hidden="true">
                                                <i class="fas {{ $tentang['ikon'] ?? 'fa-tag' }}"></i>
                                            </span>
                                            <span class="mis-sel-teks">
                                                <p class="tar-riwayat-nilai">{{ $item->nama_layanan }}</p>
                                                <p class="tar-riwayat-ket">
                                                    {{ $item->nama_varian ?: $item->satuan }}
                                                </p>
                                            </span>
                                        </span>
                                    </td>

                                    <td data-judul="Tarif">
                                        <span class="tar-riwayat-nilai">{{ $item->tarif_terbaca }}</span>
                                    </td>

                                    <td data-judul="PPN">
                                        @if ($item->ppn_persen > 0)
                                            <span class="mis-pil mis-pil-kuning">
                                                <i class="fas fa-percent"></i> {{ $item->ppn_persen }}%
                                            </span>
                                        @else
                                            <span class="tar-riwayat-ket">Tanpa PPN</span>
                                        @endif
                                    </td>

                                    <td data-judul="Dipakai">
                                        @if ($item->clinik_scopus_count > 0)
                                            <span class="mis-pil mis-pil-biru" title="Jadi acuan harga sesi sebanyak ini">
                                                <i class="fas fa-link"></i> {{ $item->clinik_scopus_count }} sesi
                                            </span>
                                        @else
                                            <span class="tar-riwayat-ket">Belum dipakai</span>
                                        @endif
                                    </td>

                                    <td data-judul="Disetel">
                                        <span class="tar-riwayat-ket">
                                            {{ optional($item->updated_at)->locale('id')->translatedFormat('d M Y') ?: '—' }}
                                        </span>
                                    </td>

                                    <td data-judul="Aksi">
                                        <div class="tar-aksi">
                                            @if ($bolehUbah)
                                                <button type="button" class="mis-tombol mis-tombol-garis"
                                                    title="Berlakukan lagi tarif ini"
                                                    data-berlaku="{{ route('account.Clinik-Scopus-Biaya-Persesi.berlakukan', $item) }}"
                                                    data-nilai="{{ $item->nama_layanan }} {{ $item->tarif_terbaca }}">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                                <button type="button" class="mis-tombol mis-tombol-bahaya"
                                                    title="Hapus dari riwayat"
                                                    data-hapus="{{ route('account.Clinik-Scopus-Biaya-Persesi.destroy', $item) }}"
                                                    data-nilai="{{ $item->nama_layanan }} {{ $item->tarif_terbaca }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @else
                                                <span class="tar-riwayat-ket">—</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $riwayat->links('vendor.pagination.bootstrap-4') }}
            @endif
        </div>

    </section>
</div>
@endsection

@push('scripts')
<script>
    /*
     * Tiap borang kartu memoles isian tarifnya dan menghitung pratinjaunya
     * sendiri.
     *
     * Dipasang per borang, bukan satu penangan untuk semuanya: tujuh kartu
     * punya tujuh borang dengan angka acuannya masing-masing, dan menyimpan
     * acuan itu di satu tempat berarti kartu yang satu ikut mengubah tulisan
     * tombol kartu yang lain.
     */
    (function () {
        const rupiah = function (n) { return 'Rp ' + n.toLocaleString('id-ID'); };
        const angka = function (v) { return parseInt(String(v).replace(/\D+/g, ''), 10) || 0; };

        document.querySelectorAll('[data-tarif-borang]').forEach(function (borang) {
            const biaya = borang.querySelector('[data-biaya]');
            const ppn = borang.querySelector('[data-ppn]');
            const teks = borang.querySelector('[data-pratinjau]');
            const perbaiki = borang.querySelector('[data-perbaiki]');
            const simpan = borang.querySelector('[data-simpan]');
            if (!biaya || !teks) return;

            const acuan = angka(biaya.dataset.awal);
            const punyaAcuan = (biaya.dataset.awal || '') !== '';

            const segarkan = function () {
                const dasar = angka(biaya.value);
                const persen = Math.min(100, Math.max(0, parseInt(ppn && ppn.value, 10) || 0));
                const pajak = Math.round(dasar * persen / 100);

                if (dasar < 1) {
                    teks.innerHTML = 'Pelanggan membayar <strong>—</strong>';
                } else if (persen > 0) {
                    teks.innerHTML = 'Pelanggan membayar <strong>' + rupiah(dasar + pajak) + '</strong> — '
                        + rupiah(dasar) + ' + PPN ' + persen + '% (' + rupiah(pajak) + ')';
                } else {
                    teks.innerHTML = 'Pelanggan membayar <strong>' + rupiah(dasar) + '</strong> — tanpa PPN';
                }

                /*
                 * Menekan tombol berarti hal yang berbeda tergantung apa yang
                 * berubah, jadi tulisannya ikut berubah. Tarif yang sama persis
                 * tetapi PPN atau fasilitasnya berbeda tetap dihitung
                 * perbaikan, bukan kenaikan harga.
                 */
                if (perbaiki && simpan) {
                    const sama = punyaAcuan && dasar === acuan;
                    perbaiki.value = sama ? (perbaiki.dataset.acuan || '') : '';
                    simpan.innerHTML = sama
                        ? '<i class="fas fa-save"></i> Perbaiki'
                        : '<i class="fas fa-save"></i> Berlakukan';
                }
            };

            biaya.addEventListener('input', function () {
                const n = angka(biaya.value);
                biaya.value = n > 0 ? n.toLocaleString('id-ID') : '';
                segarkan();
            });

            if (ppn) ppn.addEventListener('input', segarkan);

            segarkan();
        });
    })();

    /*
     * Memberlakukan tarif lama dan menghapus baris riwayat.
     *
     * Satu penangan untuk seluruh tabel, bukan onclick di tiap baris: dengan
     * begitu alamatnya datang dari route() di markah, bukan dirangkai di
     * JavaScript — perubahan rute tidak diam-diam merusak tombolnya.
     */
    (function () {
        const kirim = function (alamat, metode) {
            return fetch(alamat, {
                method: metode,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                .then(function (j) {
                    window.misToast(j.ok && j.d.success ? 'berhasil' : 'gagal',
                        j.d.message || 'Tindakannya gagal.');
                    return j.ok && j.d.success;
                })
                .catch(function () {
                    window.misToast('gagal', 'Tidak bisa menghubungi peladen.');
                    return false;
                });
        };

        document.addEventListener('click', function (e) {
            const pulih = e.target.closest('[data-berlaku]');

            if (pulih) {
                window.misKonfirmasi({
                    judul: 'Berlakukan lagi tarif ini?',
                    pesan: '%s akan dipakai untuk pemesanan berikutnya. Tarif yang sekarang berhenti berlaku.',
                    sorot: pulih.dataset.nilai,
                    tombol: 'Ya, berlakukan',
                    jenis: 'tanya',
                    glif: 'fa-undo',
                }).then(function (ya) {
                    if (ya) kirim(pulih.dataset.berlaku, 'POST').then(function (baik) {
                        if (baik) setTimeout(function () { window.location.reload(); }, 900);
                    });
                });

                return;
            }

            const hapus = e.target.closest('[data-hapus]');
            if (!hapus) return;

            window.misKonfirmasi({
                judul: 'Hapus tarif ini dari riwayat?',
                pesan: 'Baris %s dihapus permanen. Tarif yang masih jadi acuan harga pesanan tidak bisa dihapus.',
                sorot: hapus.dataset.nilai,
                tombol: 'Ya, hapus',
                jenis: 'bahaya',
            }).then(function (ya) {
                if (ya) kirim(hapus.dataset.hapus, 'DELETE').then(function (baik) {
                    if (baik) hapus.closest('tr').remove();
                });
            });
        });
    })();
</script>
@endpush
