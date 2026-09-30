@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Angkatan Layanan | MIS
@stop

@include('partials.toast-flash')

@push('gaya')
<style>
    /* Bahasa rupanya mengikuti docs/panduan-ui-mis.md — sama dengan Tarif
       layanan dan Data Pelanggan. */

    .ang-kepala {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        column-gap: 12px; row-gap: 2px; align-items: center;
        margin-bottom: 12px;
    }

    .ang-kepala > .mis-medali { grid-row: 1 / span 2; align-self: center; }
    .ang-kepala-judul { grid-column: 2; grid-row: 1; margin: 0; line-height: 1.25; font-size: .92rem; font-weight: 800; color: var(--mis-tinta); }
    .ang-kepala-sub { grid-column: 2; grid-row: 2; margin: 0; line-height: 1.45; font-size: .75rem; color: var(--mis-tinta-3); }

    /* ------------------------------------------------------- saringan layanan */

    /* Strip yang bisa digeser di ponsel, bukan lencana yang membungkus jadi
       empat baris — polanya sama dengan strip tab di Profil. */
    .ang-strip {
        display: flex; gap: 8px;
        overflow-x: auto; scroll-snap-type: x proximity;
        /*
         * Padding 5px memberi ruang untuk cincin fokus, yang kalau tidak
         * akan terpotong oleh overflow-x: auto. TANPA margin negatif
         * penyeimbang: terukur, margin itu membuat strip 1170px di dalam
         * bagian selebar 1160px — meluber 5px ke kanan. Lebih baik lencananya
         * masuk 5px daripada halamannya punya lebar yang bukan lebarnya.
         */
        padding: 5px; margin: 0 0 12px;
        scrollbar-width: none;
    }

    .ang-strip::-webkit-scrollbar { display: none; }

    .ang-pilih {
        display: inline-flex; align-items: center; gap: 7px;
        flex: 0 0 auto; scroll-snap-align: start;
        height: 38px; padding: 0 14px;
        border: 1px solid var(--mis-garis); border-radius: 12px;
        background: #fff;
        font-size: .78rem; font-weight: 700; color: var(--mis-tinta-2);
        text-decoration: none; white-space: nowrap;
        transition: all .2s ease;
    }

    .ang-pilih:hover { border-color: #c7d2fe; color: #4f46e5; text-decoration: none; }

    .ang-pilih.aktif {
        border-color: transparent; color: #fff;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        box-shadow: 0 6px 16px -12px rgba(99, 102, 241, .9);
    }

    .ang-pilih .fas { font-size: 13px !important; }

    .ang-hitung {
        display: inline-grid; place-items: center;
        min-width: 21px; height: 19px; padding: 0 6px;
        border-radius: 999px; background: #eef2ff;
        font-size: .68rem; font-weight: 800; color: #4f46e5;
    }

    .ang-pilih.aktif .ang-hitung { background: rgba(255, 255, 255, .22); color: #fff; }

    /* --------------------------------------------------------------- saringan */

    .ang-saring {
        display: grid;
        /* Empat kendali sejak pengurut ditambahkan; kisinya masih untuk tiga,
           jadi tombol Saring jatuh ke baris sendiri selebar penuh. */
        grid-template-columns: minmax(0, 1fr) 170px 190px auto;
        gap: 10px; align-items: end;
        margin-bottom: var(--mis-jarak);
    }

    /* ------------------------------------------------------------------ tabel */

    .ang-cari-kotak { position: relative; display: block; }
    .ang-cari-kotak .form-control-modern { width: 100%; padding-right: 40px; }
    .ang-cari-kotak input[type="search"]::-webkit-search-cancel-button { display: none; }

    .ang-cari-bersih {
        position: absolute; right: 5px; top: 50%;
        transform: translateY(-50%);
        display: grid; place-items: center;
        width: 30px; height: 30px;
        border-radius: 8px;
        color: var(--mis-tinta-4);
    }

    .ang-cari-bersih:hover { background: #fef2f2; color: #e11d48; text-decoration: none; }
    .ang-cari-bersih .fas { font-size: 12px !important; }

    .ang-nama { margin: 0; line-height: 1.3; font-size: .86rem; font-weight: 700; color: var(--mis-tinta); overflow-wrap: anywhere; }
    .ang-ket { margin: 1px 0 0; line-height: 1.4; font-size: .73rem; color: var(--mis-tinta-3); }
    .ang-aksi { display: flex; align-items: center; justify-content: flex-end; gap: 6px; }

    /* Borang gandakan tidak boleh memakai ruang barisnya sendiri. */
    .ang-gandakan { display: inline-flex; margin: 0; }

    .ang-tautan {
        display: inline-block;
        margin-top: 3px;
        line-height: 1.4;
        font-size: .72rem;
        font-weight: 700;
        color: #4f46e5;
    }

    .ang-tautan:hover { color: #4338ca; }

    .ang-kuota { display: inline-flex; align-items: baseline; gap: 4px; font-size: .8rem; color: var(--mis-tinta-2); }

    /* Satu nilai bertingkat: angkanya di atas, keterangannya di bawah. */
    .ang-nilai { display: block; }
    .ang-nilai .ang-nama { display: block; white-space: nowrap; }
    .ang-nilai .ang-ket { display: block; }
    .ang-kuota strong { font-size: .92rem; font-weight: 800; color: var(--mis-tinta); }

    @media (max-width: 1199.98px) {
        .ang-saring { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
        .ang-saring > .mis-tombol { grid-column: 1 / -1; }
    }

    @media (max-width: 767.98px) {
        .ang-saring { grid-template-columns: minmax(0, 1fr); }

        /* Tiap baris jadi kartu, seperti daftar pelanggan: garis 1px terlalu
           sepi untuk memisahkan enam keterangan berlabel. */
        .mis-tabel-kartu.ang-tabel tbody { display: flex; flex-direction: column; gap: 8px; }
        .mis-tabel-kartu.ang-tabel tbody tr { border: 1px solid var(--mis-garis); border-radius: 13px; background: #f8fafc; }

        /* Tujuh keterangan berlabel per kartu terukur 253px di 390px dan 304px
           di 320px — terlalu tinggi untuk daftar yang dibaca sambil menggulung.
           Dua yang paling jarang ditindaklanjuti disembunyikan; keduanya tetap
           ada di layar lebar dan di berkas unduhan. */
        .mis-tabel-kartu.ang-tabel td[data-judul="Layanan"],
        .mis-tabel-kartu.ang-tabel td[data-judul="Biaya"] { display: none; }
    }
</style>
@endpush

@section('content')
@php
    /*
     * Tautan ke daftar pendaftar tiap angkatan. Hanya dua layanan yang
     * pendaftarannya tercatat di sistem ini; sisanya belum punya layar
     * pendaftar, jadi tautannya tidak dibuat daripada menunjuk halaman yang
     * tidak menjawab apa-apa.
     */
    $tautanPendaftar = function ($a) {
        $rute = [
            'scopus_camp' => 'account.pendaftaranscopuscamp.index',
            'bibliometrik' => 'account.analisisbibliometrik.index',
        ];

        if (! isset($rute[$a->layanan]) || ! \Illuminate\Support\Facades\Route::has($rute[$a->layanan])) {
            return null;
        }

        return route($rute[$a->layanan], ['kategori' => $a->id]);
    };
@endphp
<div class="main-content mis-badan">
    <section class="section">

        <div class="mis-kepala">
            <span class="mis-medali mis-ungu" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Angkatan layanan</h1>
                <p class="mis-sub">Semua angkatan jasa dalam satu daftar — harga dan deskripsinya ikut tarif induk.</p>
            </div>
            <div class="mis-kepala-aksi">
                {{-- Layar kategori yang digantikan layar ini SUDAH punya unduhan
                     PDF dan Excel; menyatukannya tanpa keduanya berarti
                     diam-diam mencabut kemampuan yang sudah dipakai orang.
                     Saringan yang sedang aktif ikut terbawa. --}}
                <a href="{{ route('account.kategori-layanan.excel', request()->query()) }}"
                    class="mis-tombol mis-tombol-halus">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('account.kategori-layanan.cetak', request()->query()) }}"
                    class="mis-tombol mis-tombol-halus">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                @if ($bolehUbah)
                    <a href="{{ route('account.kategori-layanan.create', ['layanan' => $layanan ?: 'scopus_camp']) }}"
                        class="mis-tombol mis-tombol-ungu">
                        <i class="fas fa-plus"></i> Angkatan baru
                    </a>
                @endif
            </div>
        </div>

        {{-- Strip layanan. Tautan biasa, bukan JavaScript: bisa dibuka di tab
             baru dan alamatnya bisa disimpan. --}}
        <div class="ang-strip" role="tablist" aria-label="Saring menurut layanan">
            <a href="{{ route('account.kategori-layanan.index') }}"
                class="ang-pilih {{ $layanan ? '' : 'aktif' }}">
                <i class="fas fa-th-large"></i> Semua
                <span class="ang-hitung">{{ $jumlah->sum() }}</span>
            </a>

            @foreach ($katalog as $kunci => $tentang)
                <a href="{{ route('account.kategori-layanan.index', ['layanan' => $kunci]) }}"
                    class="ang-pilih {{ $layanan === $kunci ? 'aktif' : '' }}">
                    <i class="fas {{ $tentang['ikon'] }}"></i> {{ $tentang['nama'] }}
                    <span class="ang-hitung">{{ $jumlah[$kunci] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        <div class="mis-bagian">
            <form method="GET" action="{{ route('account.kategori-layanan.index') }}" class="ang-saring">
                <input type="hidden" name="layanan" value="{{ $layanan }}">
                {{-- Menu pengurut menulis ke dua isian ini, bukan mengirim
                     namanya sendiri: peladen hanya mengenal 'urut' dan 'arah'. --}}
                <input type="hidden" name="urut" id="ang-urut-kolom" value="{{ $urut }}">
                <input type="hidden" name="arah" id="ang-urut-arah" value="{{ $arah }}">

                <div class="mis-isian">
                    <label class="mis-label" for="ang-cari">Cari angkatan</label>
                    <div class="ang-cari-kotak">
                        <input type="search" class="form-control-modern" id="ang-cari" name="cari"
                            value="{{ $cari }}" placeholder="Nama, nomor angkatan, atau lokasi">
                        @if ($cari !== '')
                            {{-- Tautan, bukan tombol berskrip: pencariannya dijalankan
                                 peladen, jadi mengosongkannya berarti membuka daftar
                                 tanpa kata kunci — dan tautan tetap bekerja tanpa
                                 JavaScript. --}}
                            <a href="{{ route('account.kategori-layanan.index', array_filter([
                                'layanan' => $layanan, 'status' => $status,
                            ])) }}" class="ang-cari-bersih" aria-label="Kosongkan pencarian"
                                title="Kosongkan pencarian">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="mis-isian">
                    <label class="mis-label" for="ang-status">Status</label>
                    <select class="form-control-modern" id="ang-status" name="status">
                        <option value="">Semua status</option>
                        @foreach (['active' => 'Aktif', 'non active' => 'Nonaktif', 'draft' => 'Draf'] as $k => $l)
                            <option value="{{ $k }}" @selected($status === $k)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mis-isian">
                    <label class="mis-label" for="ang-urut">Urutkan</label>
                    <select class="form-control-modern" id="ang-urut" name="urutgabung">
                        @php
                            // Larik bersarang, bukan kunci "kolom|arah" yang dibelah
                            // di dalam @foreach: @php(...) sebaris tidak menangani
                            // pembongkaran larik, dan halamannya galat 500 tanpa
                            // menyebut sebabnya.
                            $pilihanUrut = [
                                ['mulai', 'turun', 'Terbaru dulu'],
                                ['mulai', 'naik', 'Terlama dulu'],
                                ['nama', 'naik', 'Nama A–Z'],
                                ['nama', 'turun', 'Nama Z–A'],
                                ['sisa_kuota', 'naik', 'Sisa kuota paling sedikit'],
                                ['sisa_kuota', 'turun', 'Sisa kuota paling banyak'],
                                ['status', 'naik', 'Status'],
                            ];
                        @endphp
                        @foreach ($pilihanUrut as $pilihan)
                            <option value="{{ $pilihan[0] }}|{{ $pilihan[1] }}"
                                @selected($urut === $pilihan[0] && $arah === $pilihan[1])>{{ $pilihan[2] }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="mis-tombol mis-tombol-halus">
                    <i class="fas fa-search"></i> Saring
                </button>
            </form>

            @if ($angkatan->isEmpty())
                {{-- Judulnya ikut berubah, bukan cuma subjudulnya: "Belum ada
                     angkatan" pada daftar yang sedang disaring adalah kalimat
                     yang tidak benar — angkatannya ada, cuma tidak cocok. --}}
                @if ($adaSaringan)
                    <div class="mis-kosong mis-kosong-cari">
                        <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-search"></i></span>
                        <p class="mis-kosong-judul">Tidak ada yang cocok</p>
                        <p class="mis-kosong-teks">
                            @if ($cari !== '')
                                Tidak ada angkatan bernama &ldquo;<strong>{{ $cari }}</strong>&rdquo;.
                            @endif
                            Coba kata kunci lain, atau hapus saringannya.
                        </p>
                        <a href="{{ route('account.kategori-layanan.index') }}"
                            class="mis-tombol mis-tombol-halus mis-kosong-aksi">
                            <i class="fas fa-times"></i> Hapus saringan
                        </a>
                    </div>
                @else
                    <div class="mis-kosong">
                        <span class="mis-kosong-ikon" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
                        <p class="mis-kosong-judul">Belum ada angkatan</p>
                        <p class="mis-kosong-teks">Buat angkatan pertama lewat tombol di atas.</p>
                    </div>
                @endif
            @else
                <div class="mis-tabel-bungkus">
                    <table class="mis-tabel mis-tabel-kartu ang-tabel">
                        <thead>
                            <tr>
                                <th>Angkatan</th>
                                <th>Layanan</th>
                                <th>Tanggal</th>
                                <th>Kuota</th>
                                <th>Biaya</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($angkatan as $a)
                                @php($tentang = $katalog[$a->layanan] ?? null)
                                <tr>
                                    <td class="mis-td-utama">
                                        <span class="mis-sel-utama">
                                            <span class="mis-medali kecil {{ $tentang['warna'] ?? 'mis-abu' }}" aria-hidden="true">
                                                <i class="fas {{ $tentang['ikon'] ?? 'fa-layer-group' }}"></i>
                                            </span>
                                            <span class="mis-sel-teks">
                                                <p class="ang-nama">{{ $a->nama }}</p>
                                                <p class="ang-ket">
                                                    {{ $a->nama_ke ? 'Angkatan ke-' . $a->nama_ke : 'Tanpa nomor' }}
                                                    @if ($a->lokasi) &middot; {{ $a->lokasi }} @endif
                                                </p>
                                            </span>
                                        </span>
                                    </td>

                                    <td data-judul="Layanan">
                                        <span class="ang-ket">{{ $a->nama_layanan }}</span>
                                        @if ($a->nama_varian)
                                            <span class="mis-pil mis-pil-ungu">{{ $a->nama_varian }}</span>
                                        @endif
                                    </td>

                                    <td data-judul="Tanggal">
                                        <span class="ang-ket">
                                            {{ \App\Support\RentangTanggal::tulis(
                                                $a->mulai ? \Carbon\Carbon::parse($a->mulai) : null,
                                                $a->selesai ? \Carbon\Carbon::parse($a->selesai) : null
                                            ) ?: '—' }}
                                        </span>
                                    </td>

                                    <td data-judul="Kuota">
                                        <span class="ang-kuota">
                                            <strong>{{ $a->sisa_kuota ?? '—' }}</strong>
                                            <span>dari {{ $a->total_kuota ?? '—' }}</span>
                                        </span>

                                        @if ($a->kuota_habis)
                                            {{-- Kuota habis harus kelihatan tanpa membandingkan
                                                 dua angka sendiri. --}}
                                            <span class="mis-pil mis-pil-merah">
                                                <i class="fas fa-user-friends"></i> Penuh
                                            </span>
                                        @endif

                                        @php($pendaftar = $a->jumlah_pendaftar)
                                        @if ($pendaftar > 0 && $tautanPendaftar($a))
                                            {{-- "14 dari 25" lalu berhenti di situ menyisakan
                                                 pertanyaan "siapa"; jawabannya satu klik. --}}
                                            <a href="{{ $tautanPendaftar($a) }}" class="ang-tautan">
                                                lihat {{ $pendaftar }} pendaftar
                                            </a>
                                        @elseif ($pendaftar > 0)
                                            <span class="ang-ket">{{ $pendaftar }} pendaftar</span>
                                        @endif
                                    </td>

                                    {{-- Harga dan promonya dibungkus jadi SATU nilai. Di modus
                                         kartu, selnya jadi flex (label | nilai), jadi dua unsur
                                         terpisah berubah jadi dua item berdampingan dan angka
                                         yang panjang terpaksa patah jadi "Rp" lalu "4.500.000". --}}
                                    <td data-judul="Biaya">
                                        <span class="ang-nilai">
                                            <span class="ang-nama">
                                                {{ (int) $a->biaya > 0 ? 'Rp ' . number_format((int) $a->biaya, 0, ',', '.') : '—' }}
                                            </span>
                                            @if ((int) $a->total_biaya > 0 && (int) $a->total_biaya !== (int) $a->biaya)
                                                <span class="ang-ket">
                                                    promo Rp {{ number_format((int) $a->total_biaya, 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </span>
                                    </td>

                                    <td data-judul="Status">
                                        @php($rupa = ['active' => ['mis-pil-hijau', 'Aktif'], 'non active' => ['mis-pil-abu', 'Nonaktif'], 'draft' => ['mis-pil-kuning', 'Draf']])
                                        @php($s = $rupa[$a->status] ?? ['mis-pil-abu', $a->status])
                                        <span class="mis-pil {{ $s[0] }}">{{ $s[1] }}</span>

                                        @if ($a->sudah_lewat)
                                            {{-- Tidak ada apa pun yang menutup angkatan otomatis,
                                                 jadi ia bisa terpajang "Aktif" berbulan-bulan
                                                 sesudah acaranya selesai. --}}
                                            <span class="mis-pil mis-pil-kuning"
                                                title="Masih aktif padahal tanggalnya sudah lewat">
                                                <i class="fas fa-exclamation-triangle"></i> Lewat
                                            </span>
                                        @endif
                                    </td>

                                    <td data-judul="Aksi">
                                        <div class="ang-aksi">
                                            @if ($bolehUbah)
                                                <a href="{{ route('account.kategori-layanan.edit', $a) }}"
                                                    class="mis-tombol mis-tombol-garis" title="Lihat &amp; ubah">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                {{-- Menggandakan: 41 angkatan Yogyakarta isinya
                                                     nyaris sama persis, dan semuanya diketik ulang. --}}
                                                <form method="POST" class="ang-gandakan"
                                                    action="{{ route('account.kategori-layanan.gandakan', $a) }}">
                                                    @csrf
                                                    <button type="submit" class="mis-tombol mis-tombol-garis"
                                                        title="Gandakan jadi rancangan baru">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </form>

                                                <button type="button" class="mis-tombol mis-tombol-bahaya"
                                                    title="Hapus angkatan"
                                                    data-hapus="{{ route('account.kategori-layanan.destroy', $a) }}"
                                                    data-nama="{{ $a->nama }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @else
                                                <span class="ang-ket">—</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $angkatan->links('vendor.pagination.bootstrap-4') }}
            @endif
        </div>

    </section>
</div>
@endsection

@push('scripts')
<script>
    /*
     * Menu pengurut menulis ke isian tersembunyi urut/arah lalu mengirim
     * borangnya. Namanya sengaja 'urutgabung' supaya nilainya sendiri tidak
     * ikut terkirim ke peladen, yang cuma mengenal dua nama itu.
     */
    (function () {
        const pilih = document.getElementById('ang-urut');
        if (!pilih) return;

        pilih.addEventListener('change', function () {
            const bagian = pilih.value.split('|');
            document.getElementById('ang-urut-kolom').value = bagian[0];
            document.getElementById('ang-urut-arah').value = bagian[1];
            pilih.form.submit();
        });
    })();

    document.addEventListener('click', function (e) {
        const tombol = e.target.closest('[data-hapus]');
        if (!tombol) return;

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
                    window.misToast(j.ok && j.d.success ? 'berhasil' : 'gagal', j.d.message);
                    if (j.ok && j.d.success) tombol.closest('tr').remove();
                })
                .catch(function () { window.misToast('gagal', 'Tidak bisa menghubungi peladen.'); });
        });
    });
</script>
@endpush
