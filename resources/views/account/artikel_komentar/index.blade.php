@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Komentar Blog | MIS
@stop

@include('partials.toast-flash')

@push('gaya')
<style>
    .kmt-tab { display: flex; flex-wrap: wrap; gap: 8px; }
    .kmt-tab a {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 8px 15px; border-radius: 999px;
        border: 1px solid var(--mis-garis); background: #fff;
        color: #475569; font-size: .9rem; font-weight: 600; text-decoration: none;
    }
    .kmt-tab a:hover { background: #f8fafc; }
    .kmt-tab a.aktif { background: #0f172a; border-color: #0f172a; color: #fff; }
    .kmt-tab span {
        min-width: 22px; padding: 1px 7px; border-radius: 999px;
        background: rgba(0, 0, 0, .07); font-size: .78rem; text-align: center;
    }
    .kmt-tab a.aktif span { background: rgba(255, 255, 255, .22); }

    .kmt-cari { display: flex; gap: 8px; flex-wrap: wrap; margin-top: var(--mis-jarak); }
    .kmt-cari input { flex: 1 1 240px; min-width: 0; }

    /* Isi komentarnya dibatasi lebarnya. Tanpa batas, satu komentar spam
       sepanjang dua ribu huruf membuat kolom lain terdorong keluar layar. */
    .kmt-isi { max-width: 46ch; }
    .kmt-isi p { margin: 0; white-space: pre-line; word-break: break-word; }
    .kmt-balasan { color: #64748b; font-size: .8rem; }

    .kmt-pengirim { font-weight: 600; }
    .kmt-pengirim small { display: block; color: #64748b; font-weight: 400; }
    .kmt-aksi { display: flex; flex-wrap: wrap; gap: 6px; }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">

        {{-- ------------------------------------------------------ kepala --}}
        <div class="mis-kartu mis-kepala">
            <span class="mis-medali mis-ungu" aria-hidden="true"><i class="fas fa-comments"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Komentar blog</h1>
                <p class="mis-sub">
                    Komentar baru ditahan dulu sampai dilihat di sini. Yang diterbitkan
                    langsung tampil di halaman tulisannya.
                </p>
            </div>
        </div>

        <div class="mis-kartu" style="margin-top: var(--mis-jarak);">
            <div class="kmt-tab">
                @foreach ([
                    'tertahan' => ['Menunggu dicek', 'fa-clock'],
                    'terbit' => ['Terbit', 'fa-check'],
                    'sampah' => ['Sampah', 'fa-ban'],
                ] as $kode => [$label, $ikon])
                    <a href="{{ route('account.komentar-artikel.index', ['status' => $kode, 'cari' => $cari]) }}"
                        class="{{ $status === $kode ? 'aktif' : '' }}">
                        <i class="fas {{ $ikon }}" aria-hidden="true"></i>
                        {{ $label }}
                        <span>{{ $jumlah[$kode] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('account.komentar-artikel.index') }}" class="kmt-cari">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="search" name="cari" value="{{ $cari }}" class="form-control-modern"
                    placeholder="Cari nama, email, atau isi komentar">
                <button type="submit" class="mis-tombol mis-tombol-biru">
                    <i class="fas fa-search" aria-hidden="true"></i> Cari
                </button>
                @if ($cari !== '')
                    <a href="{{ route('account.komentar-artikel.index', ['status' => $status]) }}"
                        class="mis-tombol mis-tombol-garis">Hapus pencarian</a>
                @endif
            </form>
        </div>

        {{-- ------------------------------------------------------ daftar --}}
        <div class="mis-kartu" style="margin-top: var(--mis-jarak);">
            @if ($komentar->isEmpty())
                {{-- Kosong berbunyi, bukan tabel tanpa baris: tabel kosong
                     terbaca sebagai layar yang gagal memuat. --}}
                <p class="mis-sub" style="text-align: center; padding: 28px 0;">
                    @if ($cari !== '')
                        Tidak ada komentar yang cocok dengan "{{ $cari }}".
                    @elseif ($status === 'tertahan')
                        Tidak ada komentar yang menunggu dicek. Bersih.
                    @else
                        Belum ada komentar berstatus ini.
                    @endif
                </p>
            @else
                <div class="mis-tabel-bungkus">
                    <table class="mis-tabel">
                        <thead>
                            <tr>
                                <th>Komentar</th>
                                <th>Pengirim</th>
                                <th>Tulisan</th>
                                <th>Masuk</th>
                                <th style="text-align: right;">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($komentar as $k)
                                <tr data-baris="{{ $k->id }}">
                                    <td class="kmt-isi">
                                        <p>{{ $k->komentar }}</p>
                                        @if ($k->reply)
                                            <span class="kmt-balasan">
                                                <i class="fas fa-reply" aria-hidden="true"></i> balasan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="kmt-pengirim">
                                        {{ $k->nama ?: '—' }}
                                        <small>{{ $k->email ?: '—' }}</small>
                                        @if ($k->nama_akun)
                                            <small>akun: {{ $k->nama_akun }}</small>
                                        @endif
                                        @if ($k->link_website)
                                            {{-- Ditulis APA ADANYA, bukan sebagai tautan.
                                                 Isian ini tidak pernah tampil di halaman
                                                 publik, dan membuatnya bisa diklik di sini
                                                 berarti mengantar orang dalam ke alamat
                                                 yang diketik orang asing. --}}
                                            <small>situs: {{ $k->link_website }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($k->judul_artikel)
                                            <a href="{{ route('blog.topic.blog-single', ['id' => $k->artikel_id]) }}"
                                                target="_blank" rel="noopener">{{ \Illuminate\Support\Str::limit($k->judul_artikel, 40) }}</a>
                                        @else
                                            <span class="mis-lencana mis-lencana-abu">tulisannya sudah dihapus</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($k->created_at)->locale('id')->translatedFormat('j M Y, H:i') }}</td>
                                    <td>
                                        <div class="kmt-aksi" style="justify-content: flex-end;">
                                            @foreach ([
                                                'terbit' => ['Terbitkan', 'fa-check', 'mis-tombol-hijau'],
                                                'tertahan' => ['Tahan', 'fa-clock', 'mis-tombol-garis'],
                                                'sampah' => ['Sampah', 'fa-ban', 'mis-tombol-garis'],
                                            ] as $tujuan => [$label, $ikon, $kelas])
                                                @if ($status !== $tujuan)
                                                    <form method="POST"
                                                        action="{{ route('account.komentar-artikel.status', $k->id) }}">
                                                        @csrf
                                                        <input type="hidden" name="status" value="{{ $tujuan }}">
                                                        <button type="submit" class="mis-tombol {{ $kelas }}">
                                                            <i class="fas {{ $ikon }}" aria-hidden="true"></i> {{ $label }}
                                                        </button>
                                                    </form>
                                                @endif
                                            @endforeach

                                            <button type="button" class="mis-tombol mis-tombol-bahaya"
                                                title="Hapus komentar"
                                                data-hapus="{{ route('account.komentar-artikel.destroy', $k->id) }}">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($komentar->hasPages())
                    <div style="margin-top: var(--mis-jarak);">{{ $komentar->links() }}</div>
                @endif
            @endif
        </div>

    </section>
</div>
@stop

@push('scripts')
<script>
    (function () {
        var csrf = document.querySelector('meta[name="csrf-token"]')?.content;

        document.addEventListener('click', function (e) {
            var tombol = e.target.closest('[data-hapus]');
            if (!tombol) return;

            window.misKonfirmasi({
                judul: 'Hapus komentar ini?',
                pesan: 'Balasannya ikut terhapus, dan keduanya tidak bisa dikembalikan.',
                tombol: 'Ya, hapus',
                jenis: 'bahaya',
            }).then(function (ya) {
                if (!ya) return;

                fetch(tombol.dataset.hapus, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        window.misToast(d.success ? 'berhasil' : 'gagal', d.message);
                        if (d.success) tombol.closest('tr').remove();
                    })
                    .catch(function () { window.misToast('gagal', 'Tidak bisa menghubungi peladen.'); });
            });
        });
    })();
</script>
@endpush
