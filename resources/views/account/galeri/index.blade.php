@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Galeri Foto | MIS
@stop

@include('partials.toast-flash')

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
    .gal-pil-semua { background: #dcfce7; color: #166534; }

    /* Pemilih layanan selebar penuh: daftar centangnya tidak muat di satu
       kolom borang yang lebarnya 210px. */
    .gal-penuh { grid-column: 1 / -1; }

    .gal-layanan {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 8px 16px;
        padding: 12px 14px;
        border: 1px solid var(--mis-garis);
        border-radius: 12px;
        background: #f8fafc;
    }

    .gal-centang {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 0;
        font-size: .86rem;
        font-weight: 500;
        cursor: pointer;
    }

    /* "Semua layanan" dipisah garis: ia MENIMPA pilihan di bawahnya, dan
       berjajar rapat ia terbaca seperti pilihan yang setara. */
    .gal-centang-semua {
        grid-column: 1 / -1;
        padding-bottom: 10px;
        border-bottom: 1px dashed var(--mis-garis);
    }

    .gal-centang input { flex: 0 0 auto; width: 18px; height: 18px; }

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

    /* Penanda wajib. Merah dan kelihatan tanpa hover — hanya dipasang pada
       medan yang validatornya memang required, supaya tandanya tetap berarti.

       Span biasa, BUKAN komponen Blade bernama "wajib": proyek ini tidak punya
       satu pun komponen (tidak ada resources/views/components), dan memakainya
       membuat seluruh halaman galat 500 dengan pesan "Unable to locate a class
       or view for component" — galat yang menunjuk middleware, bukan
       tampilannya.

       Nama komponennya sengaja ditulis TANPA kurung sudut di komentar ini.
       Pemindai komponen Blade tidak peduli teksnya ada di dalam komentar CSS:
       menuliskannya utuh di sini membuat komentar penjelas ini sendiri yang
       menggagalkan halamannya. */
    .gal-wajib { color: #e11d48; font-weight: 800; }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">

        {{-- ------------------------------------------------------ kepala --}}
        <div class="mis-kartu mis-kepala">
            <span class="mis-medali mis-ungu" aria-hidden="true"><i class="fas fa-images"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Galeri foto</h1>
                <p class="mis-sub">
                    Foto yang tampil di halaman landing. Satu foto bisa dipakai beberapa layanan sekaligus.
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

            <form method="POST" action="{{ route('account.galeri.store') }}"
                enctype="multipart/form-data" class="gal-unggah">
                @csrf
                <input type="hidden" name="layanan" value="{{ $layanan }}">

                <div class="mis-isian">
                    <label class="mis-label" for="gal-berkas">
                        Pilih foto <span class="gal-wajib" title="Wajib diisi" aria-hidden="true">*</span>
                    </label>
                    {{-- .heic dan .heif ikut disebut di accept: di iPhone, dialog
                         berkas MENYEMBUNYIKAN foto yang jenisnya tidak disebut,
                         jadi tanpa ini sebagian besar foto pengguna iPhone tidak
                         akan kelihatan sama sekali saat memilih. --}}
                    <input type="file" class="form-control-modern" id="gal-berkas" name="berkas[]"
                        accept="image/jpeg,image/png,image/webp,image/heic,image/heif,.heic,.heif"
                        multiple required>
                    <p class="mis-bantuan">
                        Boleh beberapa sekaligus, paling banyak 20.
                        @if ($bisaHeic)
                            JPG, PNG, WebP, dan HEIC dari iPhone — semuanya diubah jadi WebP.
                            Fotonya disimpan apa adanya, tidak diputar sendiri; pakai tombol putar
                            di kartunya kalau ada yang miring.
                        @else
                            JPG, PNG, dan WebP. <strong>HEIC belum bisa dibaca peladen ini</strong>,
                            ubah dulu ke JPG.
                        @endif
                    </p>
                </div>

                <div class="mis-isian gal-penuh">
                    <label class="mis-label">
                        Dipakai layanan <span class="gal-wajib" title="Wajib dipilih" aria-hidden="true">*</span>
                    </label>

                    {{-- Centang banyak, bukan satu pilihan: satu foto boleh
                         dipakai beberapa layanan sekaligus. Dokumentasi satu
                         acara yang dihadiri peserta dua layanan sama-sama
                         relevan di kedua halaman. --}}
                    <div class="gal-layanan">
                        <label class="gal-centang gal-centang-semua">
                            <input type="checkbox" class="mis-centang" name="semua_layanan" value="1"
                                id="gal-semua">
                            <span><strong>Semua layanan</strong> — termasuk layanan yang ditambahkan nanti</span>
                        </label>

                        @foreach ($katalog as $kunci => $tentang)
                            <label class="gal-centang">
                                <input type="checkbox" class="mis-centang gal-satu" name="layanan[]"
                                    value="{{ $kunci }}">
                                <span>{{ $tentang['nama'] }}</span>
                            </label>
                        @endforeach
                    </div>
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
                        <option value="">Semua layanan</option>
                        @foreach ($katalog as $kunci => $tentang)
                            <option value="{{ $kunci }}" @selected($layanan === $kunci)>{{ $tentang['nama'] }}</option>
                        @endforeach
                    </select>
                    <p class="mis-bantuan">Foto bertanda "Semua layanan" selalu ikut tampil.</p>
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
                                <span class="gal-pil {{ $g->semua_layanan ? 'gal-pil-semua' : 'gal-pil-umum' }}"
                                    data-sebut>{{ $g->sebut_layanan }}</span>

                                @if ($g->kategori_id)
                                    <span class="gal-pil gal-pil-sesi">Khusus satu sesi</span>
                                @endif

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
                                    {{-- Putar manual. Sistem sengaja TIDAK memutar sendiri:
                                         foto yang sudah melewati WhatsApp kehilangan penanda
                                         EXIF-nya sementara pikselnya tetap miring, dan menebak
                                         berarti sebagian foto justru dimiringkan sistem. --}}
                                    <button type="button" class="mis-tombol mis-tombol-garis"
                                        title="Putar ke kiri" data-putar="-90">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <button type="button" class="mis-tombol mis-tombol-garis"
                                        title="Putar ke kanan" data-putar="90">
                                        <i class="fas fa-redo"></i>
                                    </button>

                                    <button type="button" class="mis-tombol mis-tombol-bahaya"
                                        title="Hapus foto" data-hapus="{{ route('account.galeri.destroy', $g) }}">
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

        /* "Semua layanan" mematikan pilihan per layanan: keduanya tercentang
           bersamaan tidak punya arti, dan yang dikirim ke peladen pun hanya
           salah satunya. Dimatikan di layar supaya itu kelihatan. */
        var semua = document.getElementById('gal-semua');

        if (semua) {
            var satuan = document.querySelectorAll('.gal-satu');

            var selaraskan = function () {
                Array.prototype.forEach.call(satuan, function (c) {
                    c.disabled = semua.checked;
                    c.closest('.gal-centang').style.opacity = semua.checked ? '.45' : '';
                });
            };

            semua.addEventListener('change', selaraskan);
            selaraskan();
        }

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

            fetch('{{ url('account/galeri') }}/' + kartu.dataset.id + '/ubah', {
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
            var putar = e.target.closest('[data-putar]');
            if (!putar) return;

            var kartu = putar.closest('.gal-kartu');
            var gambar = kartu.querySelector('.gal-foto');

            if (!gambar) {
                window.misToast('gagal', 'Fotonya tidak ada untuk diputar.');
                return;
            }

            var isi = new FormData();
            isi.append('_token', csrf);
            isi.append('derajat', putar.dataset.putar);

            putar.disabled = true;

            fetch('{{ url('account/galeri') }}/' + kartu.dataset.id + '/putar', {
                method: 'POST', headers: { 'Accept': 'application/json' }, body: isi
            })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    putar.disabled = false;

                    if (!d.success) { window.misToast('gagal', d.message); return; }

                    /* Jalur berkasnya SENGAJA tidak berubah supaya tautan yang
                       sudah beredar tetap hidup — jadi peramban harus dipaksa
                       memuat ulang lewat penanda waktu, kalau tidak yang tampil
                       salinan temboloknya yang masih miring. */
                    var dasar = (gambar.getAttribute('src') || '').split('?')[0];
                    gambar.setAttribute('src', dasar + '?v=' + d.penanda);
                })
                .catch(function () {
                    putar.disabled = false;
                    window.misToast('gagal', 'Tidak bisa menghubungi peladen.');
                });
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
