@extends('layouts.account')
@extends('layouts.loader')

@section('title')
{{ $angkatan->nama }} | MIS
@stop

@include('partials.toast-flash')

@push('gaya')
<style>
    /* Bahasa rupanya mengikuti docs/panduan-ui-mis.md. */

    /*
     * Dua kolom, dan tingginya DIBIARKAN berbeda — tetapi kartunya ditarik
     * sama tinggi.
     *
     * Dulu align-items: start, dengan alasan yang benar untuk keadaan waktu
     * itu. Yang tidak terduga: arah timpangnya berganti-ganti tergantung isi.
     * Terukur di 1470px — ada sampul: kiri 325px, kanan 670px, jadi 345px
     * menganga di bawah kartu kiri; tanpa sampul: kiri 325px, kanan 114px,
     * 211px menganga di bawah kartu kanan.
     *
     * Ditarik sama tinggi, yang tersisa cuma ruang DI DALAM satu kartu, dan
     * itu jauh lebih tenang dilihat daripada dua kartu yang ujungnya tidak
     * pernah bertemu.
     */
    .det-kisi {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 340px);
        gap: var(--mis-jarak);
        align-items: stretch;
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

    /* Sama seperti daftar fasilitas di layar Tarif: tanpa align-items: start,
       centang pada butir dua baris melayang di tengah blok. */
    .det-fasilitas li {
        display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 8px;
        align-items: start;
        line-height: 1.45; font-size: .8rem; color: var(--mis-tinta-2);
    }

    .det-fasilitas .fas { font-size: 11px !important; color: #10b981; margin-top: 3px; }

    /* Borang gandakan tidak boleh memakai ruang barisnya sendiri. */
    .det-gandakan { display: inline-flex; margin: 0; }

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
                    {{-- Menggandakan dan menghapus tersedia di sini juga, bukan
                         cuma di daftar. Begitu orang membuka rincian sebuah
                         angkatan, DI SITULAH ia memutuskan — memaksanya kembali
                         ke daftar berarti mencari barisnya lagi di antara enam
                         puluh baris. --}}
                    <form method="POST" action="{{ route('account.kategori-layanan.gandakan', $angkatan) }}"
                        class="det-gandakan">
                        @csrf
                        <button type="submit" class="mis-tombol mis-tombol-halus"
                            title="Salin jadi rancangan baru">
                            <i class="fas fa-copy mis-ikon-biru"></i> Gandakan
                        </button>
                    </form>

                    <a href="{{ route('account.kategori-layanan.edit', $angkatan) }}"
                        class="mis-tombol mis-tombol-ungu">
                        <i class="fas fa-edit"></i> Ubah
                    </a>

                    <button type="button" class="mis-tombol mis-tombol-hapus" id="det-hapus"
                        data-hapus="{{ route('account.kategori-layanan.destroy', $angkatan) }}"
                        data-nama="{{ $angkatan->nama }}">
                        <i class="fas fa-trash-alt"></i> Hapus
                    </button>
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

                <p class="mis-kartu-judul" style="margin-top: 16px;">
                    <i class="fas fa-check-circle mis-ikon-hijau"></i> Fasilitas dari tarif induk
                </p>

                {{-- Dulu seluruh blok ini hilang begitu saja kalau tarifnya tidak
                     ketemu — padahal sampul yang kosong tepat di atasnya justru
                     dijelaskan. Dua kekurangan di kartu yang sama, satu diterangkan
                     dan satu disembunyikan, membuat yang disembunyikan terbaca
                     seolah layanan ini memang tidak punya fasilitas. --}}
                @if (! $tarif)
                    <p class="det-kosong">
                        Angkatan ini tidak menemukan tarif induknya
                        (<strong>{{ $angkatan->nama_layanan }}{{ $angkatan->varian ? ' · ' . $angkatan->varian : '' }}</strong>),
                        jadi harga, fasilitas, dan perakit deskripsinya kosong.
                        Setel variannya di borang ubah, atau tambahkan tarifnya di
                        <a href="{{ route('account.Clinik-Scopus-Biaya-Persesi.index') }}">Tarif Layanan</a>.
                    </p>
                @elseif (! $tarif->daftar_fasilitas)
                    <p class="det-kosong">
                        Tarif induknya belum mencantumkan fasilitas apa pun.
                    </p>
                @else
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

@push('scripts')
<script>
    /* Menghapus dari halaman rincian; aturannya sama dengan di daftar, dan
       sesudahnya kembali ke daftar sebab barisnya sudah tidak ada. */
    (function () {
        const tombol = document.getElementById('det-hapus');
        if (!tombol) return;

        tombol.addEventListener('click', function () {
            window.misKonfirmasi({
                judul: 'Hapus angkatan ini?',
                pesan: '%s dihapus permanen. Angkatan yang sudah punya pendaftar tidak bisa dihapus.',
                sorot: tombol.dataset.nama,
                tombol: 'Ya, hapus',
                jenis: 'bahaya',
            }).then(function (ya) {
                if (!ya) return;

                fetch(tombol.dataset.hapus, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                })
                    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                    .then(function (j) {
                        if (!j.ok || !j.d.success) {
                            window.misToast('gagal', j.d.message || 'Gagal menghapus angkatan.');
                            return;
                        }
                        window.misToast('berhasil', j.d.message);
                        setTimeout(function () {
                            window.location.href = @json(route('account.kategori-layanan.index'));
                        }, 900);
                    })
                    .catch(function () { window.misToast('gagal', 'Tidak bisa menghubungi peladen.'); });
            });
        });
    })();
</script>
@endpush
