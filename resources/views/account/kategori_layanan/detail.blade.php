@extends('layouts.account')
@extends('layouts.loader')

@section('title')
{{ $angkatan->nama }} | MIS
@stop

@include('partials.toast-flash')

@push('gaya')
<style>
    /* Bahasa rupanya mengikuti docs/panduan-ui-mis.md. */

    .det-kisi {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 340px);
        gap: var(--mis-jarak);
        /* start, bukan stretch: kartu yang isinya pendek tidak ikut setinggi
           kartu deskripsi, dan sisanya tidak jadi petak putih di dalamnya. */
        align-items: start;
    }

    .det-sampul {
        width: 100%;
        /* Batas lebarnya sama dengan kolom kisinya (340px). Tanpa batas ini,
           di bawah 992px kisinya jadi satu kolom dan flyer 1080x1350 terukur
           membesar jadi 726x907 di layar 820px — halamannya 2640px, lebih
           tinggi daripada di ponsel 390px. */
        max-width: 340px;
        height: auto;
        border-radius: var(--mis-radius);
        border: 1px solid var(--mis-garis);
        display: block;
    }

    .det-fakta { display: grid; gap: 11px; }

    .det-baris {
        display: grid;
        grid-template-columns: minmax(0, 120px) minmax(0, 1fr);
        gap: 10px; align-items: baseline;
        padding-bottom: 9px;
        border-bottom: 1px dashed var(--mis-garis);
    }

    .det-baris:last-child { border-bottom: 0; padding-bottom: 0; }
    .det-label { line-height: 1.45; font-size: .74rem; color: var(--mis-tinta-4); }
    .det-nilai { line-height: 1.45; font-size: .84rem; font-weight: 700; color: var(--mis-tinta); overflow-wrap: anywhere; }
    .det-nilai.samar { font-weight: 400; color: var(--mis-tinta-3); }

    /*
     * Deskripsi ditampilkan apa adanya dengan baris barunya dipertahankan —
     * sama seperti halaman publik, yang memakai white-space: pre-line. Kalau
     * di sini dirapatkan, admin menyetujui tampilan yang bukan yang dilihat
     * pengunjung.
     */
    .det-deskripsi {
        margin: 0;
        padding: 14px;
        border: 1px solid var(--mis-garis);
        border-radius: 13px;
        background: #f8fafc;
        white-space: pre-line;
        line-height: 1.6;
        font-size: .82rem;
        color: var(--mis-tinta-2);
        overflow-wrap: anywhere;
    }

    .det-kosong { line-height: 1.5; font-size: .8rem; color: var(--mis-tinta-4); }
    .det-fasilitas { margin: 0; padding: 0; list-style: none; display: grid; gap: 6px; }

    .det-fasilitas li {
        display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 8px;
        line-height: 1.45; font-size: .8rem; color: var(--mis-tinta-2);
    }

    .det-fasilitas .fas { font-size: 11px !important; color: #10b981; margin-top: 3px; }

    @media (max-width: 991.98px) {
        .det-kisi { grid-template-columns: minmax(0, 1fr); }
    }
</style>
@endpush

@section('content')
<div class="main-content mis-badan">
    <section class="section">

        <div class="mis-kepala">
            @php($tentang = \App\ClinikScopusBiayaPersesi::layanan()[$angkatan->layanan] ?? null)
            <span class="mis-medali {{ $tentang['warna'] ?? 'mis-abu' }}" aria-hidden="true">
                <i class="fas {{ $tentang['ikon'] ?? 'fa-layer-group' }}"></i>
            </span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">{{ $angkatan->nama }}</h1>
                <p class="mis-sub">
                    {{ $angkatan->nama_layanan }}@if ($angkatan->nama_varian) &middot; {{ $angkatan->nama_varian }}@endif
                    @if ($angkatan->nama_ke) &middot; angkatan ke-{{ $angkatan->nama_ke }} @endif
                </p>
            </div>
            <div class="mis-kepala-aksi">
                <a href="{{ route('account.kategori-layanan.index') }}" class="mis-tombol mis-tombol-halus">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                @if ($bolehUbah)
                    <a href="{{ route('account.kategori-layanan.edit', $angkatan) }}"
                        class="mis-tombol mis-tombol-ungu">
                        <i class="fas fa-edit"></i> Ubah
                    </a>
                @endif
            </div>
        </div>

        <div class="det-kisi">
            {{-- ------------------------------------------- keterangan --}}
            <div class="mis-bagian">
                <p class="mis-kartu-judul">
                    <i class="fas fa-info-circle mis-ikon-biru"></i> Keterangan
                </p>

                <div class="det-fakta">
                    <div class="det-baris">
                        <span class="det-label">Status</span>
                        <span class="det-nilai">
                            @php($rupa = ['active' => ['mis-pil-hijau', 'Aktif'], 'non active' => ['mis-pil-abu', 'Nonaktif'], 'draft' => ['mis-pil-kuning', 'Draf']])
                            @php($st = $rupa[$angkatan->status] ?? ['mis-pil-abu', $angkatan->status])
                            <span class="mis-pil {{ $st[0] }}">{{ $st[1] }}</span>
                            @if ($angkatan->sudah_lewat)
                                <span class="mis-pil mis-pil-kuning">
                                    <i class="fas fa-exclamation-triangle"></i> Tanggalnya lewat
                                </span>
                            @endif
                        </span>
                    </div>

                    <div class="det-baris">
                        <span class="det-label">Tanggal</span>
                        <span class="det-nilai">
                            {{ \App\Support\RentangTanggal::tulis(
                                $angkatan->mulai ? \Carbon\Carbon::parse($angkatan->mulai) : null,
                                $angkatan->selesai ? \Carbon\Carbon::parse($angkatan->selesai) : null
                            ) ?: '—' }}
                        </span>
                    </div>

                    <div class="det-baris">
                        <span class="det-label">Lokasi</span>
                        <span class="det-nilai {{ $angkatan->lokasi ? '' : 'samar' }}">
                            {{ $angkatan->lokasi ?: 'Tidak disebutkan' }}
                        </span>
                    </div>

                    <div class="det-baris">
                        <span class="det-label">Kuota</span>
                        <span class="det-nilai">
                            {{ $angkatan->sisa_kuota ?? '—' }} sisa dari {{ $angkatan->total_kuota ?? '—' }}
                            @if ($angkatan->kuota_habis)
                                <span class="mis-pil mis-pil-merah">
                                    <i class="fas fa-user-friends"></i> Penuh
                                </span>
                            @endif
                        </span>
                    </div>

                    <div class="det-baris">
                        <span class="det-label">Pendaftar</span>
                        <span class="det-nilai">{{ $angkatan->jumlah_pendaftar }} orang</span>
                    </div>

                    <div class="det-baris">
                        <span class="det-label">Biaya</span>
                        <span class="det-nilai">
                            @if ((int) $angkatan->biaya > 0)
                                Rp {{ number_format((int) $angkatan->biaya, 0, ',', '.') }}
                                @if ((int) $angkatan->total_biaya > 0 && (int) $angkatan->total_biaya !== (int) $angkatan->biaya)
                                    <span class="det-nilai samar">
                                        &middot; promo Rp {{ number_format((int) $angkatan->total_biaya, 0, ',', '.') }}
                                        @if ($angkatan->kode_diskon) ({{ $angkatan->kode_diskon }}) @endif
                                    </span>
                                @endif
                            @else
                                <span class="det-nilai samar">Belum disetel</span>
                            @endif
                        </span>
                    </div>

                    <div class="det-baris">
                        <span class="det-label">Grup WhatsApp</span>
                        <span class="det-nilai {{ $angkatan->group_wa ? '' : 'samar' }}">
                            {{ $angkatan->group_wa ?: 'Belum ada' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- ------------------------------------------------ sampul --}}
            <div class="mis-bagian">
                <p class="mis-kartu-judul">
                    <i class="fas fa-image mis-ikon-ungu"></i> Sampul
                </p>

                @if ($angkatan->alamat_sampul)
                    <img src="{{ $angkatan->alamat_sampul }}" alt="Sampul {{ $angkatan->nama }}" class="det-sampul">
                @else
                    <p class="det-kosong">
                        Belum ada sampul, jadi halaman publiknya menampilkan gambar cadangan.
                    </p>
                @endif

                @if ($tarif && $tarif->daftar_fasilitas)
                    <p class="mis-kartu-judul" style="margin-top: 16px;">
                        <i class="fas fa-check-circle mis-ikon-hijau"></i> Fasilitas dari tarif induk
                    </p>
                    <ul class="det-fasilitas">
                        @foreach ($tarif->daftar_fasilitas as $f)
                            <li><i class="fas fa-check" aria-hidden="true"></i> <span>{{ $f }}</span></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- ------------------------------------------------- deskripsi --}}
        <div class="mis-bagian" style="margin-top: var(--mis-jarak);">
            <p class="mis-kartu-judul">
                <i class="fas fa-file-alt mis-ikon-ungu"></i> Deskripsi yang dilihat pengunjung
            </p>

            @if (trim((string) $angkatan->desc) !== '')
                <p class="det-deskripsi">{{ $angkatan->desc }}</p>
            @else
                <p class="det-kosong">Deskripsinya belum diisi.</p>
            @endif
        </div>

    </section>
</div>
@endsection
