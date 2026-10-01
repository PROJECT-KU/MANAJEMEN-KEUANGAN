@extends('livewire.layout.templateindex')

@section('title', 'Galeri Layanan')

@push('gaya')
<style>
    .gal-kisi {
        display: grid;
        /* auto-fill + minmax: empat kolom di layar lebar, dua di tablet, satu
           di ponsel — tanpa titik putus yang harus dijaga sendiri. */
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 16px;
    }

    .gal-kartu {
        display: flex;
        flex-direction: column;
        border: 1px solid var(--mis-garis);
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
    }

    .gal-kartu.nonaktif { opacity: .55; }

    .gal-foto {
        width: 100%;
        aspect-ratio: 4 / 3;
        object-fit: cover;
        background: #f1f5f9;
    }

    .gal-hilang {
        display: grid;
        place-items: center;
        width: 100%;
        aspect-ratio: 4 / 3;
        background: #fef2f2;
        color: #b91c1c;
        font-size: .78rem;
        text-align: center;
        padding: 10px;
    }

    .gal-isi { display: flex; flex-direction: column; gap: 8px; padding: 12px; }

    .gal-isi input {
        width: 100%;
        height: 36px;
        padding: 0 10px;
        border: 1px solid var(--mis-garis);
        border-radius: 9px;
        background: #f8fafc;
        font-size: .82rem;
    }

    .gal-baris { display: flex; align-items: center; gap: 8px; }
    .gal-baris input[type="number"] { width: 70px; }

    /* margin-top: auto — tombol menempel ke bawah kartu, jadi tombol semua
       kartu sebaris walau keterangannya beda panjang. */
    .gal-aksi { display: flex; align-items: center; gap: 8px; margin-top: auto; }

    .gal-pil {
        padding: 3px 9px;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 700;
    }

    .gal-pil-sesi { background: #ede9fe; color: #5b21b6; }
    .gal-pil-umum { background: #e0f2fe; color: #075985; }

    .gal-unggah {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 14px;
        align-items: end;
    }

    .gal-terang {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 18px;
        padding: 13px 16px;
        border-radius: 12px;
        background: #eef2ff;
        border: 1px solid #c7d2fe;
        color: #3730a3;
        font-size: .84rem;
        line-height: 1.6;
    }

    .gal-terang > .fas { margin: 0 !important; margin-top: 2px !important; }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">

        {{-- ------------------------------------------------------ kepala --}}
        <div class="mis-kartu mis-kepala">
            <span class="mis-medali mis-ungu" aria-hidden="true"><i class="fas fa-images"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Galeri layanan</h1>
                <p class="mis-sub">
                    Foto yang tampil di halaman landing. Diunggah sekali, dipakai semua sesi layanan itu.
                </p>
            </div>
        </div>

        {{-- ----------------------------------------------------- unggah --}}
        <div class="mis-kartu" style="margin-top: var(--mis-jarak);">
            <p class="gal-terang">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                <span>
                    Foto yang diunggah di sini <strong>otomatis diubah jadi WebP</strong> dan disimpan di
                    storage; berkas aslinya dihapus. Lebarnya dipotong paling besar
                    {{ \App\Services\Gambar::LEBAR_MAKS }}px — ukuran yang dipakai layar, bukan ukuran kamera.
                </span>
            </p>

            <form method="POST" action="{{ route('account.galeri-layanan.store') }}"
                enctype="multipart/form-data" class="gal-unggah">
                @csrf
                <input type="hidden" name="layanan" value="{{ $layanan }}">

                <div class="mis-isian">
                    <label class="mis-label" for="gal-berkas">Pilih foto <x-wajib /></label>
                    <input type="file" class="form-control-modern" id="gal-berkas" name="berkas[]"
                        accept="image/jpeg,image/png,image/webp" multiple required>
                    <p class="mis-bantuan">Boleh beberapa sekaligus, paling banyak 20.</p>
                </div>

                <div class="mis-isian">
                    <label class="mis-label" for="gal-sesi">Khusus sesi</label>
                    <select class="form-control-modern" id="gal-sesi" name="kategori_id">
                        <option value="">Foto umum — tampil di semua sesi</option>
                        @foreach ($angkatan as $a)
                            <option value="{{ $a->id }}">
                                {{ $a->nama }}{{ $a->nama_ke ? ' ke-' . $a->nama_ke : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mis-bantuan">Kosongkan kalau fotonya bukan dokumentasi satu sesi tertentu.</p>
                </div>

                <div class="mis-isian">
                    <label class="mis-label" for="gal-ket">Keterangan</label>
                    <input type="text" class="form-control-modern" id="gal-ket" name="keterangan"
                        maxlength="160" placeholder="mis. Suasana sesi Oktober 2026">
                    <p class="mis-bantuan">Dibacakan pembaca layar dan jadi teks pengganti kalau gambarnya gagal.</p>
                </div>

                <div class="mis-isian">
                    <button type="submit" class="mis-tombol mis-tombol-ungu">
                        <i class="fas fa-upload"></i> Unggah
                    </button>
                </div>
            </form>
        </div>

        {{-- ---------------------------------------------------- saringan --}}
        <div class="mis-kartu" style="margin-top: var(--mis-jarak);">
            <form method="GET" class="mis-saring">
                <div class="mis-isian mis-saring-pilih">
                    <label class="mis-label" for="gal-layanan">Layanan</label>
                    <select class="form-control-modern" id="gal-layanan" name="layanan"
                        onchange="this.form.submit()">
                        @foreach ($katalog as $kunci => $tentang)
                            <option value="{{ $kunci }}" @selected($layanan === $kunci)>{{ $tentang['nama'] }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        {{-- ------------------------------------------------------ daftar --}}
        <div class="mis-kartu" style="margin-top: var(--mis-jarak);">
            @if ($jumlahHilang > 0)
                {{-- Berkas yang tercatat tetapi sudah tidak ada di cakram
                     disebut terang-terangan: tanpa ini, yang terlihat cuma
                     kotak kosong dan tidak ada yang tahu kenapa. --}}
                <p class="gal-terang" style="background: #fef2f2; border-color: #fecaca; color: #991b1b;">
                    <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                    <span>{{ $jumlahHilang }} foto berkasnya sudah tidak ada di peladen. Hapus lalu unggah ulang.</span>
                </p>
            @endif

            @if ($foto->isEmpty())
                <div class="mis-kosong">
                    <span class="mis-medali mis-abu" aria-hidden="true"><i class="fas fa-images"></i></span>
                    <p class="mis-kosong-judul">Belum ada foto di galeri layanan ini</p>
                    <p class="mis-kosong-sub">Unggah lewat borang di atas; halaman landing langsung memakainya.</p>
                </div>
            @else
                <div class="gal-kisi">
                    @foreach ($foto as $g)
                        <div class="gal-kartu {{ $g->aktif ? '' : 'nonaktif' }}" data-id="{{ $g->id }}">
                            @if ($g->alamat)
                                <img src="{{ $g->alamat }}" alt="{{ $g->keterangan_tampil }}"
                                    class="gal-foto" loading="lazy" decoding="async">
                            @else
                                <div class="gal-hilang">
                                    <span><i class="fas fa-exclamation-triangle"></i><br>Berkasnya tidak ada</span>
                                </div>
                            @endif

                            <div class="gal-isi">
                                <span class="gal-pil {{ $g->kategori_id ? 'gal-pil-sesi' : 'gal-pil-umum' }}">
                                    {{ $g->kategori_id ? 'Sesi tertentu' : 'Semua sesi' }}
                                </span>

                                <input type="text" value="{{ $g->keterangan }}" maxlength="160"
                                    placeholder="Keterangan foto" data-ubah="keterangan"
                                    aria-label="Keterangan foto">

                                <div class="gal-baris">
                                    <input type="number" value="{{ $g->urutan }}" min="0" max="9999"
                                        data-ubah="urutan" aria-label="Urutan tampil">
                                    <label class="mis-bantuan" style="margin: 0; display: flex; align-items: center; gap: 6px;">
                                        <input type="checkbox" class="mis-centang" data-ubah="aktif"
                                            @checked($g->aktif) style="width: 18px; height: 18px;">
                                        Tampil
                                    </label>
                                </div>

                                <div class="gal-aksi">
                                    <button type="button" class="mis-tombol mis-tombol-bahaya"
                                        title="Hapus foto" data-hapus="{{ route('account.galeri-layanan.destroy', $g) }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </section>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        'use strict';

        var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        /* Perubahan dikirim saat isian DITINGGALKAN, bukan tiap ketukan: satu
           keterangan enam kata akan jadi tiga puluh permintaan ke peladen. */
        document.addEventListener('change', function (e) {
            var isian = e.target.closest('[data-ubah]');
            if (!isian) return;

            var kartu = isian.closest('.gal-kartu');
            if (!kartu) return;

            var isi = new FormData();
            isi.append('_token', csrf);

            var medan = isian.dataset.ubah;
            isi.append(medan, isian.type === 'checkbox' ? (isian.checked ? 1 : 0) : isian.value);

            fetch('{{ url('account/galeri-layanan') }}/' + kartu.dataset.id + '/ubah', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: isi
            })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (!d.success) { window.misToast('gagal', d.message); return; }
                    if (medan === 'aktif') kartu.classList.toggle('nonaktif', !isian.checked);
                })
                .catch(function () { window.misToast('gagal', 'Tidak bisa menghubungi peladen.'); });
        });

        document.addEventListener('click', function (e) {
            var tombol = e.target.closest('[data-hapus]');
            if (!tombol) return;

            window.misKonfirmasi({
                judul: 'Hapus foto ini?',
                pesan: 'Berkasnya ikut dihapus dari peladen dan tidak bisa dikembalikan.',
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
                        if (d.success) tombol.closest('.gal-kartu').remove();
                    })
                    .catch(function () { window.misToast('gagal', 'Tidak bisa menghubungi peladen.'); });
            });
        });
    })();
</script>
@endpush
