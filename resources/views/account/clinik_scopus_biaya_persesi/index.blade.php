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
     * Bahasa rupanya mengikuti docs/panduan-ui-mis.md — ubin ikon .mis-medali,
     * lencana .mis-pil, tombol .mis-tombol, kartu .mis-bagian.
     */

    .tar-kepala {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        column-gap: 12px; row-gap: 2px; align-items: center;
        margin-bottom: 12px;
    }

    .tar-kepala > .mis-medali { grid-row: 1 / span 2; align-self: center; }
    .tar-kepala-judul { grid-column: 2; grid-row: 1; margin: 0; line-height: 1.25; font-size: .92rem; font-weight: 800; color: var(--mis-tinta); }
    .tar-kepala-sub { grid-column: 2; grid-row: 2; margin: 0; line-height: 1.45; font-size: .75rem; color: var(--mis-tinta-3); }

    /* ------------------------------------------------- kisi kartu layanan */

    .tar-kisi {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
        gap: var(--mis-jarak);
        margin-bottom: var(--mis-jarak);
        /*
         * stretch (bawaan), bukan start. Borangnya sekarang terbuka di
         * dialog, jadi tidak ada lagi kartu yang tiba-tiba jadi dua kali
         * lebih tinggi dan menarik seisi barisnya. Yang tersisa cuma selisih
         * jumlah fasilitas, dan itu justru lebih rapi kalau dasarnya rata:
         * tombolnya sejajar di seluruh baris.
         */
    }

    .tar-kartu {
        display: flex;
        flex-direction: column;
        padding: 18px;
        border: 1px solid var(--mis-garis);
        border-radius: var(--mis-radius);
        background: #fff;
        box-shadow: var(--mis-bayang);
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .tar-kartu:hover { border-color: #c7d2fe; box-shadow: 0 10px 24px -18px rgba(99, 102, 241, .9); }

    /* Yang belum punya tarif ditandai, bukan disembunyikan: layanan yang
       terlewat harus kelihatan justru karena terlewat. */
    .tar-kartu.kosong { border-color: #fde68a; background: linear-gradient(180deg, #fffbeb 0%, #fff 60%); }
    .tar-kartu.kosong:hover { border-color: #fcd34d; }

    .tar-kartu-kepala {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        column-gap: 11px; row-gap: 4px; align-items: center;
    }

    .tar-kartu-kepala > .mis-medali { grid-row: 1 / span 2; align-self: center; }

    .tar-nama {
        grid-column: 2; grid-row: 1; margin: 0;
        line-height: 1.25; font-size: .88rem; font-weight: 800; color: var(--mis-tinta);
        overflow-wrap: anywhere;
    }

    .tar-lencana { grid-column: 2; grid-row: 2; display: flex; flex-wrap: wrap; gap: 5px; }

    /* ----------------------------------------------------------- harga */

    .tar-harga {
        display: flex; flex-wrap: wrap; align-items: baseline; gap: 7px;
        margin: 15px 0 0;
    }

    .tar-angka {
        line-height: 1.05;
        font-size: 1.45rem; font-weight: 800; color: var(--mis-tinta);
        overflow-wrap: anywhere;
    }

    .tar-kartu.kosong .tar-angka { font-size: 1.15rem; color: #b45309; }

    .tar-satuan { line-height: 1.4; font-size: .74rem; color: var(--mis-tinta-3); }

    /* Dibayar pelanggan hanya muncul kalau PPN-nya memang dikenakan; baris
       "PPN 0%" tidak memberi tahu apa pun. */
    .tar-total {
        display: flex; align-items: baseline; justify-content: space-between; gap: 10px;
        margin-top: 9px; padding: 7px 10px;
        border-radius: 9px; background: #ecfdf5;
        font-size: .74rem; color: #047857;
    }

    .tar-total strong { font-size: .84rem; font-weight: 800; }

    /* ------------------------------------------------------- fasilitas */

    .tar-fasilitas {
        margin: 13px 0 0; padding: 0; list-style: none;
        display: grid; gap: 5px;
    }

    .tar-fasilitas li {
        display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 7px;
        line-height: 1.4; font-size: .76rem; color: var(--mis-tinta-2);
    }

    .tar-fasilitas .fas { font-size: 10px !important; color: #10b981; margin-top: 3px; }

    /* Daftar dipotong supaya kartunya tetap sepadan; selengkapnya ada di
       dialog. Empat butir cukup untuk tahu ini layanan yang mana. */
    .tar-sisa {
        margin: 6px 0 0; line-height: 1.4;
        font-size: .73rem; font-weight: 700; color: #6d28d9;
    }

    .tar-kosong-teks { margin: 13px 0 0; line-height: 1.45; font-size: .76rem; color: var(--mis-tinta-4); }

    /* ------------------------------------------------------------ kaki */

    /* margin-top: auto mendorong tombolnya ke dasar kartu, jadi seluruh baris
       tombolnya sejajar walau jumlah fasilitasnya berbeda-beda. */
    .tar-kaki { margin-top: auto; padding-top: 15px; }

    .tar-kaki .mis-tombol { width: 100%; }

    .tar-tanda-cetakan {
        display: flex; align-items: center; gap: 6px;
        margin: 0 0 9px;
        line-height: 1.4; font-size: .72rem; color: var(--mis-tinta-4);
    }

    .tar-tanda-cetakan .fas { font-size: 11px !important; }

    /* --------------------------------------------------------- dialog */

    /*
     * Borangnya di dialog, bukan terlipat di dalam kartunya.
     *
     * Sebelumnya tujuh kartu memuat tujuh borang; membuka satu menarik seisi
     * barisnya jadi dua kali lebih tinggi — terukur 294px jadi 590px — dan
     * kartu di sebelahnya menyisakan petak putih hampir 300px. Di dialog,
     * kisinya tidak bergerak sama sekali, dan borangnya dapat ruang yang cukup
     * untuk tarif, fasilitas, kegiatan, kontak, dan cetakan sekaligus.
     */
    .tar-dialog {
        width: min(620px, calc(100vw - 32px));
        max-height: calc(100vh - 48px);
        padding: 0;
        border: none; border-radius: 20px;
        background: #fff;
        box-shadow: 0 32px 64px -24px rgba(15, 23, 42, .35);
        overflow: hidden;
    }

    .tar-dialog::backdrop { background: rgba(15, 23, 42, .45); backdrop-filter: blur(2px); }

    .tar-dialog-borang { display: flex; flex-direction: column; max-height: calc(100vh - 48px); }

    .tar-dialog-kepala {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 12px; align-items: center;
        padding: 16px 18px;
        border-bottom: 1px solid var(--mis-garis);
    }

    .tar-dialog-judul { margin: 0; line-height: 1.25; font-size: .95rem; font-weight: 800; color: var(--mis-tinta); }
    .tar-dialog-sub { margin: 2px 0 0; line-height: 1.4; font-size: .74rem; color: var(--mis-tinta-3); }

    .tar-tutup {
        display: grid; place-items: center;
        width: 34px; height: 34px;
        border: 1px solid var(--mis-garis); border-radius: 10px;
        background: #fff; color: var(--mis-tinta-3);
        cursor: pointer;
    }

    .tar-tutup:hover { border-color: #fecaca; background: #fef2f2; color: #e11d48; }
    .tar-tutup .fas { font-size: 14px !important; }

    .tar-dialog-isi {
        display: grid; gap: 13px;
        padding: 18px;
        overflow-y: auto;
    }

    .tar-dialog-kaki {
        display: flex; flex-wrap: wrap; gap: 9px;
        padding: 14px 18px;
        border-top: 1px solid var(--mis-garis);
        background: #f8fafc;
    }

    .tar-dua { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr); gap: 11px; }

    /* Awalan "Rp" dan akhiran "%" menempel di dalam kotaknya: keduanya satuan,
       bukan keterangan yang perlu labelnya sendiri. */
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
        /* width 100% WAJIB: lebar bawaan textarea datang dari atribut cols
           (20 aksara), bukan dari induknya. */
        width: 100%;
        min-height: 92px; padding: 10px 13px;
        border: 1px solid var(--mis-garis); border-radius: 11px;
        background: #fff;
        line-height: 1.5; font-size: .82rem; color: var(--mis-tinta);
        resize: vertical;
    }

    .tar-area:focus { outline: none; border-color: #a5b4fc; box-shadow: 0 0 0 3px rgba(99, 102, 241, .5); }

    .tar-pendek { min-height: 62px; }
    .tar-panjang { min-height: 210px; font-family: ui-monospace, "SF Mono", Menlo, monospace; font-size: .75rem; }

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
        padding: 9px 11px; border-radius: 10px; background: #f5f3ff;
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
        .tar-angka { font-size: 1.35rem; }

        .tar-dialog {
            width: 100vw; max-width: 100vw; max-height: 100vh;
            margin: 0; border-radius: 0;
        }

        .tar-dialog-borang { max-height: 100vh; }
        .tar-dialog-kaki > .mis-tombol { flex: 1 1 auto; }

        /* Tiap baris riwayat jadi kartu tersendiri, seperti daftar pelanggan. */
        .mis-tabel-kartu.tar-tabel tbody { display: flex; flex-direction: column; gap: 8px; }
        .mis-tabel-kartu.tar-tabel tbody tr { border: 1px solid var(--mis-garis); border-radius: 13px; background: #f8fafc; }
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
                <p class="mis-sub">Harga, fasilitas, dan cetakan deskripsi seluruh layanan jasa — disetel sekali di sini.</p>
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
                @php($fasilitas = $t ? $t->daftar_fasilitas : [])
                <div class="tar-kartu {{ $t ? '' : 'kosong' }}">
                    <div class="tar-kartu-kepala">
                        <span class="mis-medali {{ $k['warna'] }}" aria-hidden="true">
                            <i class="fas {{ $k['ikon'] }}"></i>
                        </span>
                        <h2 class="tar-nama">{{ $k['nama'] }}</h2>
                        <div class="tar-lencana">
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

                    <div class="tar-harga">
                        <span class="tar-angka">{{ $t ? $t->tarif_terbaca : 'Belum disetel' }}</span>
                        <span class="tar-satuan">{{ $k['satuan'] }}</span>
                    </div>

                    @if ($t && $t->ppn_persen > 0)
                        <div class="tar-total">
                            <span>Dibayar pelanggan</span>
                            <strong>Rp {{ number_format($t->total_dibayar, 0, ',', '.') }}</strong>
                        </div>
                    @endif

                    @if ($fasilitas)
                        <ul class="tar-fasilitas">
                            @foreach (array_slice($fasilitas, 0, 4) as $f)
                                <li>
                                    <i class="fas fa-check" aria-hidden="true"></i>
                                    <span>{{ $f }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @if (count($fasilitas) > 4)
                            <p class="tar-sisa">+{{ count($fasilitas) - 4 }} fasilitas lainnya</p>
                        @endif
                    @else
                        <p class="tar-kosong-teks">
                            {{ $t
                                ? 'Fasilitasnya belum diisi, jadi pengumuman angkatan tidak menyebut apa pun.'
                                : 'Tarifnya belum pernah disetel, jadi borang angkatan menampilkan harga kosong.' }}
                        </p>
                    @endif

                    <div class="tar-kaki">
                        @if ($t)
                            <p class="tar-tanda-cetakan">
                                @if ($t->ada_cetakan)
                                    <i class="fas fa-check-circle mis-ikon-hijau" aria-hidden="true"></i>
                                    Cetakan deskripsi siap dipakai
                                @else
                                    <i class="fas fa-exclamation-circle mis-ikon-kuning" aria-hidden="true"></i>
                                    Cetakan deskripsi belum diisi
                                @endif
                            </p>
                        @endif

                        @if ($bolehUbah)
                            <button type="button" class="mis-tombol {{ $t ? 'mis-tombol-halus' : 'mis-tombol-ungu' }}"
                                data-setel="{{ json_encode([
                                    'layanan' => $k['layanan'],
                                    'varian' => $k['varian'],
                                    'nama' => $k['nama'] . ($k['namaVarian'] ? ' — ' . $k['namaVarian'] : ''),
                                    'satuan' => $k['satuan'],
                                    'id' => $t?->getKey(),
                                    'biaya' => $t ? (int) $t->biaya_persesi : null,
                                    'ppn' => $t && $t->ppn !== null ? $t->ppn_persen : null,
                                    'fasilitas' => implode("\n", $fasilitas),
                                    'kegiatan' => $t ? implode("\n", $t->daftar_kegiatan) : '',
                                    'kontak' => $t?->kontak ?? '',
                                    'cetakan' => $t?->template_deskripsi ?? '',
                                ]) }}">
                                <i class="fas {{ $t ? 'fa-edit' : 'fa-plus' }}"></i>
                                {{ $t ? 'Ubah tarif & fasilitas' : 'Setel tarif' }}
                            </button>
                        @endif
                    </div>
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
                <p class="tar-kepala-sub">Disimpan karena angkatan yang sudah berjalan memakai harga yang berlaku saat itu.</p>
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
                                                <p class="tar-riwayat-ket">{{ $item->nama_varian ?: $item->satuan }}</p>
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

@if ($bolehUbah)
    {{-- SATU borang untuk semua layanan, diisi saat dibuka. Tujuh borang di
         tujuh kartu berarti tujuh salinan markah yang sama di setiap muat
         halaman, dan tiap kartu jadi dua kali lebih tinggi saat dibuka. --}}
    <dialog class="tar-dialog" id="tar-dialog">
        <form method="POST" action="{{ route('account.Clinik-Scopus-Biaya-Persesi.simpan') }}"
            class="tar-dialog-borang" id="tar-borang">
            @csrf
            <input type="hidden" name="layanan" id="tar-f-layanan">
            <input type="hidden" name="varian" id="tar-f-varian">
            {{-- Diisi skrip saat angkanya TIDAK berubah: membetulkan salah ketik
                 tidak boleh meninggalkan jejak seolah harganya pernah naik. --}}
            <input type="hidden" name="perbaiki" id="tar-f-perbaiki" value="">

            <div class="tar-dialog-kepala">
                <span class="mis-medali kecil mis-ungu" aria-hidden="true"><i class="fas fa-coins"></i></span>
                <div>
                    <h2 class="tar-dialog-judul" id="tar-f-judul">Setel tarif</h2>
                    <p class="tar-dialog-sub" id="tar-f-sub"></p>
                </div>
                <button type="button" class="tar-tutup" id="tar-batal" aria-label="Tutup">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="tar-dialog-isi">
                <div class="tar-dua">
                    <div class="mis-isian tar-isian">
                        <label class="mis-label" for="tar-f-biaya">Tarif</label>
                        <span class="tar-tanda kiri" aria-hidden="true">Rp</span>
                        <input type="text" class="form-control-modern" id="tar-f-biaya" name="biaya_persesi"
                            inputmode="numeric" autocomplete="off" required placeholder="0">
                    </div>

                    <div class="mis-isian tar-isian persen">
                        <label class="mis-label" for="tar-f-ppn">PPN</label>
                        <span class="tar-tanda kanan" aria-hidden="true">%</span>
                        <input type="number" class="form-control-modern" id="tar-f-ppn" name="ppn"
                            min="0" max="100" inputmode="numeric" placeholder="0">
                    </div>
                </div>

                <div class="tar-pratinjau" aria-live="polite">
                    <i class="fas fa-calculator mis-ikon-ungu" aria-hidden="true"></i>
                    <span id="tar-f-pratinjau">Pelanggan membayar <strong>—</strong></span>
                </div>

                <div class="mis-isian">
                    <label class="mis-label" for="tar-f-fasilitas">Fasilitas yang didapat</label>
                    <textarea class="tar-area" id="tar-f-fasilitas" name="fasilitas" rows="5"
                        placeholder="Tempel teks pengumuman di sini, atau ketik satu fasilitas per baris"></textarea>
                    <p class="mis-bantuan">
                        Boleh <strong>ditempel utuh</strong> dari teks pengumuman — yang diambil hanya
                        baris di bawah judul &ldquo;Fasilitas&rdquo;, nomornya dibuang sendiri.
                    </p>
                </div>

                <div class="mis-isian">
                    <label class="mis-label" for="tar-f-kegiatan">Kegiatan utama</label>
                    <textarea class="tar-area" id="tar-f-kegiatan" name="kegiatan" rows="4"
                        placeholder="Boleh ditempel utuh, atau satu kegiatan per baris"></textarea>
                    <p class="mis-bantuan">
                        Sama seperti fasilitas — yang diambil baris di bawah judul &ldquo;Kegiatan&rdquo;.
                    </p>
                </div>

                <div class="mis-isian">
                    <label class="mis-label" for="tar-f-kontak">Kontak panitia</label>
                    <textarea class="tar-area tar-pendek" id="tar-f-kontak" name="kontak" rows="2"
                        placeholder="&#128222; Kumala: 0889-8356-7819"></textarea>
                    <p class="mis-bantuan">Diubah di sini sekali, seluruh angkatan berikutnya ikut.</p>
                </div>

                <details class="tar-cetakan">
                    <summary>
                        <i class="fas fa-file-alt mis-ikon-biru" aria-hidden="true"></i>
                        Cetakan deskripsi angkatan
                        <span class="mis-pil mis-pil-abu" id="tar-f-tanda">belum diisi</span>
                    </summary>

                    <textarea class="tar-area tar-panjang" id="tar-f-cetakan" name="template_deskripsi"
                        rows="10" spellcheck="false"
                        placeholder="Teks pengumuman yang dipakai ulang tiap angkatan"></textarea>

                    <p class="mis-bantuan">
                        Ditulis sekali, dipakai semua angkatan. Bagian yang berganti tiap angkatan
                        cukup ditulis sebagai penanda di bawah ini — sistem yang mengisinya.
                    </p>

                    <ul class="tar-penanda">
                        @foreach (\App\Support\PerakitDeskripsi::PENANDA as $kode => $arti)
                            <li><code>{{ $kode }}</code> <span>{{ $arti }}</span></li>
                        @endforeach
                    </ul>
                </details>
            </div>

            <div class="tar-dialog-kaki">
                <button type="submit" class="mis-tombol mis-tombol-ungu" id="tar-f-simpan">
                    <i class="fas fa-save"></i> Berlakukan
                </button>
                <button type="button" class="mis-tombol mis-tombol-halus" data-tutup>Batal</button>
            </div>
        </form>
    </dialog>
@endif
@endsection

@push('scripts')
<script>
    /*
     * Dialog tarif: satu borang yang diisi ulang tiap kali dibuka.
     */
    (function () {
        const dialog = document.getElementById('tar-dialog');
        if (!dialog) return;

        const el = (id) => document.getElementById(id);
        const rupiah = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');
        const angka = (v) => parseInt(String(v).replace(/\D+/g, ''), 10) || 0;

        const biaya = el('tar-f-biaya');
        const ppn = el('tar-f-ppn');
        const perbaiki = el('tar-f-perbaiki');
        const simpan = el('tar-f-simpan');
        const pratinjau = el('tar-f-pratinjau');

        let acuan = null;   // tarif yang berlaku saat dialog dibuka
        let idLama = null;

        function segarkan() {
            const dasar = angka(biaya.value);
            const persen = Math.min(100, Math.max(0, parseInt(ppn.value, 10) || 0));
            const pajak = Math.round(dasar * persen / 100);

            if (dasar < 1) {
                pratinjau.innerHTML = 'Pelanggan membayar <strong>—</strong>';
            } else if (persen > 0) {
                pratinjau.innerHTML = 'Pelanggan membayar <strong>' + rupiah(dasar + pajak) + '</strong> — '
                    + rupiah(dasar) + ' + PPN ' + persen + '% (' + rupiah(pajak) + ')';
            } else {
                pratinjau.innerHTML = 'Pelanggan membayar <strong>' + rupiah(dasar) + '</strong> — tanpa PPN';
            }

            /*
             * Tulisan tombolnya mengikuti apa yang sebenarnya akan terjadi.
             * Tarif yang sama persis tetapi PPN atau fasilitasnya berbeda tetap
             * dihitung perbaikan, bukan kenaikan harga.
             */
            const sama = acuan !== null && dasar === acuan;
            perbaiki.value = sama ? (idLama || '') : '';
            simpan.innerHTML = sama
                ? '<i class="fas fa-save"></i> Perbaiki'
                : '<i class="fas fa-save"></i> Berlakukan';
        }

        biaya.addEventListener('input', function () {
            const n = angka(biaya.value);
            biaya.value = n > 0 ? n.toLocaleString('id-ID') : '';
            segarkan();
        });

        ppn.addEventListener('input', segarkan);

        document.addEventListener('click', function (e) {
            const pemicu = e.target.closest('[data-setel]');
            if (!pemicu) return;

            const d = JSON.parse(pemicu.dataset.setel);

            el('tar-f-layanan').value = d.layanan;
            el('tar-f-varian').value = d.varian || '';
            el('tar-f-judul').textContent = d.nama;
            el('tar-f-sub').textContent = d.biaya === null
                ? 'Belum punya tarif — ' + d.satuan
                : 'Berlaku sekarang ' + rupiah(d.biaya) + ' ' + d.satuan;

            biaya.value = d.biaya === null ? '' : Number(d.biaya).toLocaleString('id-ID');
            ppn.value = d.ppn === null ? '' : d.ppn;
            el('tar-f-fasilitas').value = d.fasilitas;
            el('tar-f-kegiatan').value = d.kegiatan;
            el('tar-f-kontak').value = d.kontak;
            el('tar-f-cetakan').value = d.cetakan;

            const tanda = el('tar-f-tanda');
            tanda.textContent = d.cetakan.trim() !== '' ? 'sudah ada' : 'belum diisi';
            tanda.className = 'mis-pil ' + (d.cetakan.trim() !== '' ? 'mis-pil-hijau' : 'mis-pil-abu');

            acuan = d.biaya;
            idLama = d.id;

            segarkan();
            dialog.showModal();
            biaya.focus();
        });

        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-tutup]') || e.target.closest('#tar-batal')) dialog.close();
        });

        // Menekan latar gelapnya ikut menutup, seperti yang orang harapkan.
        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) dialog.close();
        });
    })();

    /*
     * Memberlakukan tarif lama dan menghapus baris riwayat.
     *
     * Satu penangan untuk seluruh tabel: alamatnya datang dari route() di
     * markah, bukan dirangkai di JavaScript — perubahan rute tidak diam-diam
     * merusak tombolnya.
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
                    pesan: '%s akan dipakai untuk angkatan berikutnya. Tarif yang sekarang berhenti berlaku.',
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
