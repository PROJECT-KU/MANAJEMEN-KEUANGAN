@extends('layouts.account')
@extends('layouts.loader')

@section('title')
{{ $sunting ? 'Ubah Angkatan' : 'Angkatan Baru' }} | MIS
@stop

@include('partials.toast-flash')

@push('gaya')
<style>
    .brg-kisi {
        display: grid;
        /* auto-fit + minmax, bukan col-md-*: tiga kolom di layar lebar, dua di
           tablet, satu di ponsel tanpa titik putus yang harus dijaga. */
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 12px;
        align-content: start;
    }

    .brg-penuh { grid-column: 1 / -1; }

    /* Penanda wajib. Merah dan kelihatan tanpa hover — hanya dipasang pada
       medan yang validatornya memang required, supaya tandanya tetap berarti. */
    .brg-wajib { color: #e11d48; font-weight: 800; }

    .brg-area {
        /* width 100% WAJIB: lebar bawaan textarea diambil dari atribut cols
           (20 aksara), bukan dari induknya — terukur 180px di dalam kartu
           selebar 570px, dan teksnya terpatah-patah jadi kolom sempit. */
        width: 100%;
        min-height: 300px; padding: 12px 14px;
        border: 1px solid var(--mis-garis); border-radius: 11px;
        background: #fff;
        line-height: 1.55; font-size: .8rem; color: var(--mis-tinta);
        resize: vertical;
    }

    .brg-area:focus { outline: none; border-color: #a5b4fc; box-shadow: 0 0 0 3px rgba(99, 102, 241, .5); }

    /* Kartu berdampingan pakai align-items: start, BUKAN stretch. Dengan
       stretch, kartu yang isinya pendek ikut setinggi kartu deskripsi dan
       sisanya jadi petak putih DI DALAM kartu. */
    .brg-dua {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: var(--mis-jarak);
        align-items: start;
    }

    /* Kartu bergembok: nilai yang tidak bisa disunting. */
    .brg-harga {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 12px; align-items: center;
        padding: 12px 14px;
        border: 1px solid var(--mis-garis); border-radius: 13px;
        background: linear-gradient(180deg, #f0fdf4 0%, #fff 70%);
    }

    .brg-harga-nilai {
        margin: 0; line-height: 1.2;
        font-size: 1.15rem; font-weight: 800; color: var(--mis-tinta);
        overflow-wrap: anywhere;
    }

    .brg-harga-ket { margin: 2px 0 0; line-height: 1.45; font-size: .74rem; color: var(--mis-tinta-3); }

    .brg-ikut {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 10px; align-items: start;
        margin: 0; padding: 11px 13px;
        border: 1px dashed #fde68a; border-radius: 12px;
        background: #fffbeb;
        line-height: 1.5; font-size: .78rem; font-weight: 600; color: var(--mis-tinta-2);
        cursor: pointer;
    }

    .brg-ikut input { margin-top: 3px; }
    .brg-ikut-ket { display: block; margin-top: 3px; font-size: .73rem; font-weight: 400; color: var(--mis-tinta-3); }

    .brg-alat {
        display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
        margin-bottom: 10px;
    }

    .brg-catatan {
        margin: 0; line-height: 1.45;
        font-size: .74rem; color: var(--mis-tinta-3);
    }

    .brg-tarif {
        display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
        padding: 10px 12px; margin-bottom: 12px;
        border: 1px dashed var(--mis-garis); border-radius: 11px;
        background: #f8fafc;
        line-height: 1.45; font-size: .76rem; color: var(--mis-tinta-3);
    }

    .brg-tarif strong { font-size: .84rem; font-weight: 800; color: var(--mis-tinta); }

    .brg-sampul {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 12px; align-items: start;
    }

    .brg-sampul-pratinjau {
        display: grid; place-items: center;
        width: 96px; height: 96px;
        overflow: hidden;
        border: 1px solid var(--mis-garis); border-radius: 13px;
        background: #f8fafc; color: var(--mis-tinta-4);
    }

    .brg-sampul-pratinjau img { width: 100%; height: 100%; object-fit: cover; }
    .brg-sampul-pratinjau .fas { font-size: 22px !important; }

    .brg-kaki {
        display: flex; flex-wrap: wrap; gap: 10px;
        margin-top: var(--mis-jarak);
    }

    .brg-isian .form-control-modern { width: 100%; }

    @media (max-width: 991.98px) {
        .brg-dua { grid-template-columns: minmax(0, 1fr); }
    }
</style>
@endpush

@section('content')
<div class="main-content mis-badan">
    <section class="section">

        <div class="mis-kepala">
            <span class="mis-medali mis-ungu" aria-hidden="true">
                <i class="fas {{ $sunting ? 'fa-edit' : 'fa-plus' }}"></i>
            </span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">{{ $sunting ? 'Ubah angkatan' : 'Angkatan baru' }}</h1>
                <p class="mis-sub">Harga, fasilitas, dan deskripsinya mengikuti tarif induk — tidak perlu diketik ulang.</p>
            </div>
            <div class="mis-kepala-aksi">
                <a href="{{ route('account.kategori-layanan.index') }}" class="mis-tombol mis-tombol-halus">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="mis-bagian" style="border-color: #fecaca; background: #fef2f2;">
                <p class="mis-kartu-judul" style="color: #b91c1c;">
                    <i class="fas fa-exclamation-circle"></i> Ada yang perlu dibetulkan
                </p>
                <ul style="margin: 8px 0 0 18px; font-size: .8rem; color: #b91c1c; line-height: 1.6;">
                    @foreach ($errors->all() as $galat)
                        <li>{{ $galat }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- enctype WAJIB: tanpa itu berkas sampulnya tidak pernah sampai ke
             peladen, dan borangnya tersimpan seolah tidak ada gambar yang
             dipilih — tanpa galat apa pun. --}}
        <form method="POST" enctype="multipart/form-data"
            action="{{ $sunting ? route('account.kategori-layanan.update', $angkatan) : route('account.kategori-layanan.store') }}"
            id="brg-angkatan">
            @csrf

            <div class="brg-dua">
                {{-- ------------------------------------------- isian pokok --}}
                <div class="mis-bagian">
                    <p class="mis-kartu-judul">
                        <i class="fas fa-info-circle mis-ikon-biru"></i> Keterangan angkatan
                    </p>

                    <div class="brg-tarif" id="brg-tarif" aria-live="polite">
                        <i class="fas fa-tag mis-ikon-hijau" aria-hidden="true"></i>
                        <span id="brg-tarif-teks">Memuat tarif induk…</span>
                    </div>

                    <div class="brg-kisi">
                        <div class="mis-isian brg-isian">
                            <label class="mis-label" for="brg-layanan">Layanan <span class="brg-wajib" title="Wajib diisi" aria-hidden="true">*</span></label>
                            <select class="form-control-modern" id="brg-layanan" name="layanan" required>
                                @foreach ($katalog as $kunci => $tentang)
                                    <option value="{{ $kunci }}"
                                        @selected(old('layanan', $angkatan->layanan) === $kunci)>{{ $tentang['nama'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Varian hanya muncul untuk layanan yang punya. Disembunyikan
                             lewat kelas, bukan dihapus dari markah, supaya nilainya tetap
                             terkirim saat layanannya diganti bolak-balik. --}}
                        <div class="mis-isian brg-isian" id="brg-bungkus-varian" hidden>
                            <label class="mis-label" for="brg-varian">Varian <span class="brg-wajib" title="Wajib diisi" aria-hidden="true">*</span></label>
                            <select class="form-control-modern" id="brg-varian" name="varian"></select>
                        </div>

                        <div class="mis-isian brg-isian brg-penuh">
                            <label class="mis-label" for="brg-nama">Nama angkatan <span class="brg-wajib" title="Wajib diisi" aria-hidden="true">*</span></label>
                            <input type="text" class="form-control-modern" id="brg-nama" name="nama" required
                                value="{{ old('nama', $angkatan->nama) }}"
                                placeholder="mis. SCOPUS CAMP YOGYAKARTA">
                        </div>

                        <div class="mis-isian brg-isian">
                            <label class="mis-label" for="brg-nomor">Angkatan ke-</label>
                            <input type="text" class="form-control-modern" id="brg-nomor" name="nama_ke"
                                value="{{ old('nama_ke', $angkatan->nama_ke) }}" placeholder="202">
                        </div>

                        <div class="mis-isian brg-isian">
                            <label class="mis-label" for="brg-lokasi">Lokasi</label>
                            <input type="text" class="form-control-modern" id="brg-lokasi" name="lokasi"
                                value="{{ old('lokasi', $angkatan->lokasi) }}" placeholder="Yogyakarta">
                        </div>

                        <div class="mis-isian brg-isian">
                            <label class="mis-label" for="brg-mulai">Mulai <span class="brg-wajib" title="Wajib diisi" aria-hidden="true">*</span></label>
                            <input type="date" class="form-control-modern" id="brg-mulai" name="mulai" required
                                value="{{ old('mulai', $angkatan->mulai ? \Carbon\Carbon::parse($angkatan->mulai)->format('Y-m-d') : '') }}">
                        </div>

                        <div class="mis-isian brg-isian">
                            <label class="mis-label" for="brg-selesai">Selesai</label>
                            <input type="date" class="form-control-modern" id="brg-selesai" name="selesai"
                                value="{{ old('selesai', $angkatan->selesai ? \Carbon\Carbon::parse($angkatan->selesai)->format('Y-m-d') : '') }}">
                        </div>

                        <div class="mis-isian brg-isian">
                            <label class="mis-label" for="brg-kuota">Total kuota</label>
                            <input type="number" class="form-control-modern" id="brg-kuota" name="total_kuota"
                                min="0" value="{{ old('total_kuota', $angkatan->total_kuota) }}" placeholder="20">
                        </div>

                        @if ($sunting)
                            <div class="mis-isian brg-isian">
                                <label class="mis-label" for="brg-sisa">Sisa kuota</label>
                                <input type="number" class="form-control-modern" id="brg-sisa" name="sisa_kuota"
                                    min="0" value="{{ old('sisa_kuota', $angkatan->sisa_kuota) }}">
                                <p class="mis-bantuan">Berkurang sendiri tiap ada yang mendaftar.</p>
                            </div>
                        @endif

                        <div class="mis-isian brg-isian">
                            <label class="mis-label" for="brg-status">Status <span class="brg-wajib" title="Wajib diisi" aria-hidden="true">*</span></label>
                            <select class="form-control-modern" id="brg-status" name="status" required>
                                @foreach (['draft' => 'Draf', 'active' => 'Aktif', 'non active' => 'Nonaktif'] as $k => $l)
                                    <option value="{{ $k }}"
                                        @selected(old('status', $angkatan->status ?: 'draft') === $k)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>

                        @php($tarifIni = $sunting ? $angkatan->tarif() : null)
                        @php($hargaTersimpan = (int) $angkatan->biaya)
                        @php($hargaTarif = $tarifIni ? (int) $tarifIni->biaya_persesi : null)

                        {{-- Harga tidak diketik. Nilai yang tidak bisa disunting
                             ditampilkan sebagai kartu bergembok, bukan isian
                             disabled — itu aturan di docs/panduan-ui-mis.md. --}}
                        <div class="brg-harga brg-penuh">
                            <span class="mis-medali kecil mis-hijau" aria-hidden="true">
                                <i class="fas fa-lock"></i>
                            </span>
                            <div class="brg-harga-teks">
                                <p class="brg-harga-nilai" id="brg-harga-nilai">
                                    {{ $sunting && $hargaTersimpan > 0
                                        ? 'Rp ' . number_format($hargaTersimpan, 0, ',', '.')
                                        : '—' }}
                                </p>
                                <p class="brg-harga-ket" id="brg-harga-ket">
                                    {{ $sunting
                                        ? 'Harga yang dipotret saat angkatan ini dibuat.'
                                        : 'Mengikuti tarif layanan — tidak perlu diketik.' }}
                                </p>
                            </div>

                            {{-- Isian tersembunyi ini hanya untuk pratinjau deskripsi.
                                 Yang disimpan tetap ditentukan peladen, jadi mengubahnya
                                 di peramban tidak mengubah harga yang tersimpan. --}}
                            <input type="hidden" name="biaya" id="brg-harga-nilai-mentah"
                                value="{{ $sunting ? $hargaTersimpan : '' }}">
                        </div>

                        @if ($sunting && $hargaTarif !== null && $hargaTarif !== $hargaTersimpan)
                            {{-- Muncul HANYA kalau tarifnya memang sudah berbeda. Tanpa
                                 jalan ini, angkatan yang harganya terlanjur salah tidak
                                 bisa dibetulkan sama sekali. --}}
                            <label class="brg-ikut brg-penuh" for="brg-ikuti">
                                <input type="checkbox" id="brg-ikuti" name="ikuti_tarif" value="1">
                                <span>
                                    Ikuti tarif sekarang
                                    (<strong>Rp {{ number_format($hargaTarif, 0, ',', '.') }}</strong>)
                                    <span class="brg-ikut-ket">
                                        Peserta yang sudah mendaftar membayar harga lama, jadi
                                        centang ini hanya kalau angkatannya belum berjalan.
                                        Promo yang ada ikut disetarakan.
                                    </span>
                                </span>
                            </label>
                        @endif

                        <div class="mis-isian brg-isian brg-penuh">
                            <label class="mis-label" for="brg-sampul">Sampul angkatan</label>

                            {{-- Halaman publik memakai gambar ini; tanpa sampul ia
                                 menampilkan "no-image". Semua 58 angkatan yang ada
                                 punya sampul, jadi angkatan tanpa gambar akan
                                 langsung terlihat ganjil di antara mereka. --}}
                            <div class="brg-sampul">
                                <span class="brg-sampul-pratinjau" id="brg-sampul-pratinjau">
                                    @if ($sunting && $angkatan->alamat_sampul)
                                        <img src="{{ $angkatan->alamat_sampul }}" alt="Sampul {{ $angkatan->nama }}">
                                    @else
                                        <i class="fas fa-image" aria-hidden="true"></i>
                                    @endif
                                </span>

                                <div class="brg-sampul-isi">
                                    <input type="file" class="form-control-modern" id="brg-sampul"
                                        name="gambar" accept="image/jpeg,image/png,image/webp">
                                    <p class="mis-bantuan">
                                        JPG, PNG, atau WebP, maksimal 4 MB.
                                        {{ $sunting && $angkatan->gambar
                                            ? 'Dibiarkan kosong, sampul yang sekarang tetap dipakai.'
                                            : 'Ukuran yang pas mengikuti flyer pengumuman.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mis-isian brg-isian brg-penuh">
                            <label class="mis-label" for="brg-wa">Grup WhatsApp</label>
                            <input type="text" class="form-control-modern" id="brg-wa" name="group_wa"
                                value="{{ old('group_wa', $angkatan->group_wa) }}" placeholder="Menyusul">
                        </div>
                    </div>
                </div>

                {{-- ------------------------------------------- deskripsi --}}
                <div class="mis-bagian">
                    <p class="mis-kartu-judul">
                        <i class="fas fa-file-alt mis-ikon-ungu"></i> Deskripsi angkatan
                    </p>

                    <div class="brg-alat">
                        <button type="button" class="mis-tombol mis-tombol-ungu" id="brg-rakit">
                            <i class="fas fa-magic"></i> Rakit dari cetakan
                        </button>
                        <p class="brg-catatan">
                            Mengisi ulang kotak di bawah memakai cetakan layanan, dengan tanggal,
                            harga, dan fasilitas angkatan ini.
                        </p>
                    </div>

                    <textarea class="brg-area" name="desc" id="brg-desc" rows="16"
                        placeholder="Tekan “Rakit dari cetakan”, lalu sunting seperlunya.">{{ old('desc', $angkatan->desc) }}</textarea>

                    <p class="mis-bantuan">
                        Hasil rakitan boleh disunting. Yang tersimpan teks di kotak ini,
                        bukan cetakannya — jadi mengubah cetakan nanti tidak mengubah
                        pengumuman angkatan yang sudah terbit.
                    </p>
                </div>
            </div>

            <div class="brg-kaki">
                <button type="submit" class="mis-tombol mis-tombol-ungu">
                    <i class="fas fa-save"></i> {{ $sunting ? 'Simpan perubahan' : 'Simpan angkatan' }}
                </button>
                <a href="{{ route('account.kategori-layanan.index') }}" class="mis-tombol mis-tombol-halus">
                    Batal
                </a>

                @if ($sunting)
                    {{-- Menghapus tersedia di tempat angkatan itu sedang dibuka,
                         bukan hanya di daftar — begitu orang masuk ke borangnya,
                         di situlah ia memutuskan. Angkatan yang sudah punya
                         pendaftar tetap ditolak peladen. --}}
                    <button type="button" class="mis-tombol mis-tombol-hapus" id="brg-hapus"
                        data-hapus="{{ route('account.kategori-layanan.destroy', $angkatan) }}"
                        data-nama="{{ $angkatan->nama }}">
                        <i class="fas fa-trash-alt"></i> Hapus angkatan
                    </button>
                @endif
            </div>
        </form>

    </section>
</div>
@endsection

@push('scripts')
<script>
    /* Menghapus dari borang sunting; aturannya sama dengan di daftar. */
    (function () {
        const tombol = document.getElementById('brg-hapus');
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
                        window.misToast(j.ok && j.d.success ? 'berhasil' : 'gagal', j.d.message);
                        if (j.ok && j.d.success) {
                            setTimeout(function () {
                                window.location.href = '{{ route('account.kategori-layanan.index') }}';
                            }, 900);
                        }
                    })
                    .catch(function () { window.misToast('gagal', 'Tidak bisa menghubungi peladen.'); });
            });
        });
    })();

    (function () {
        const KATALOG = @json(collect($katalog)->map(fn ($t) => ['nama' => $t['nama'], 'varian' => $t['varian']]));
        const TARIF = @json($tarifPer);
        const VARIAN_AWAL = @json(old('varian', $angkatan->varian));
        const SUNTING = @json($sunting);

        const layanan = document.getElementById('brg-layanan');
        const bungkusVarian = document.getElementById('brg-bungkus-varian');
        const varian = document.getElementById('brg-varian');
        const tarifTeks = document.getElementById('brg-tarif-teks');

        const rupiah = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');

        function segarkanVarian() {
            const daftar = KATALOG[layanan.value]?.varian || {};
            const kunci = Object.keys(daftar);

            varian.innerHTML = '';

            if (kunci.length === 0) {
                bungkusVarian.hidden = true;
                varian.disabled = true;
                return;
            }

            bungkusVarian.hidden = false;
            varian.disabled = false;

            kunci.forEach(function (k) {
                const o = document.createElement('option');
                o.value = k;
                o.textContent = daftar[k];
                if (k === VARIAN_AWAL) o.selected = true;
                varian.appendChild(o);
            });
        }

        const hargaNilai = document.getElementById('brg-harga-nilai');
        const hargaKet = document.getElementById('brg-harga-ket');
        const hargaMentah = document.getElementById('brg-harga-nilai-mentah');

        function segarkanTarif() {
            const kunci = layanan.value + '|' + (varian.disabled ? '' : varian.value || '');
            const t = TARIF[kunci];

            if (!t || t.biaya === null) {
                tarifTeks.innerHTML = 'Layanan ini <strong>belum punya tarif induk</strong> — '
                    + 'setel dulu di Tarif layanan sebelum membuat angkatannya.';
            } else {
                tarifTeks.innerHTML = 'Harga patokan <strong>' + rupiah(t.biaya) + '</strong>'
                    + ' &middot; ' + t.fasilitas.length + ' fasilitas'
                    + (t.adaCetakan ? ' &middot; cetakan deskripsi siap' : ' &middot; <strong>cetakan belum diisi</strong>');
            }

            /*
             * Pada angkatan yang SUDAH ADA, kartu harganya tidak ikut berubah:
             * yang tertulis di sana harga yang dipotret saat angkatan itu
             * dibuat, dan mengganti layanannya di layar tidak mengubah apa yang
             * sudah dibayar peserta.
             */
            if (SUNTING) return;

            if (!t || t.biaya === null) {
                hargaNilai.textContent = '—';
                hargaKet.textContent = 'Layanan ini belum punya tarif; setel dulu di Tarif layanan.';
                hargaMentah.value = '';
                return;
            }

            hargaNilai.textContent = rupiah(t.biaya);
            hargaKet.textContent = 'Mengikuti tarif layanan — tidak perlu diketik.';
            hargaMentah.value = t.biaya;
        }

        layanan.addEventListener('change', function () { segarkanVarian(); segarkanTarif(); });
        varian.addEventListener('change', segarkanTarif);

        segarkanVarian();
        segarkanTarif();

        /*
         * Perakitan deskripsi dikerjakan peladen, bukan di sini. Disalin ke
         * JavaScript, format tanggal dan aturan baris kosongnya pasti
         * berselisih dengan yang dipakai saat menyimpan.
         */
        /* Pratinjau sampul sebelum disimpan: mengunggah gambar yang salah lalu
           baru tahu sesudah halaman publiknya terbit itu mahal. */
        const sampul = document.getElementById('brg-sampul');

        if (sampul) {
            sampul.addEventListener('change', function () {
                const berkas = sampul.files && sampul.files[0];
                if (!berkas) return;

                const kotak = document.getElementById('brg-sampul-pratinjau');
                const alamat = URL.createObjectURL(berkas);

                kotak.innerHTML = '';
                const gbr = document.createElement('img');
                gbr.src = alamat;
                gbr.alt = 'Pratinjau sampul';
                // Alamat sementaranya dilepas sesudah dipakai; kalau tidak,
                // berkasnya tetap dipegang peramban sampai tabnya ditutup.
                gbr.addEventListener('load', function () { URL.revokeObjectURL(alamat); });
                kotak.appendChild(gbr);
            });
        }

        document.getElementById('brg-rakit').addEventListener('click', function () {
            const borang = document.getElementById('brg-angkatan');
            const isi = new FormData(borang);
            const kotak = document.getElementById('brg-desc');

            const kirim = function () {
                fetch('{{ route('account.kategori-layanan.rakit') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: isi,
                })
                    .then(function (r) { return r.json(); })
                    .then(function (j) {
                        if (j.deskripsi) kotak.value = j.deskripsi;
                        window.misToast(j.success ? 'berhasil' : 'gagal', j.message);
                    })
                    .catch(function () { window.misToast('gagal', 'Tidak bisa menghubungi peladen.'); });
            };

            // Menimpa tulisan yang sudah ada harus ditanya dulu; kalau tidak,
            // suntingan tangan hilang oleh satu tekanan tombol.
            if (kotak.value.trim() !== '') {
                window.misKonfirmasi({
                    judul: 'Tulis ulang deskripsinya?',
                    pesan: 'Isi kotak deskripsi sekarang akan diganti hasil rakitan dari cetakan %s.',
                    sorot: KATALOG[layanan.value]?.nama || '',
                    tombol: 'Ya, rakit ulang',
                    jenis: 'tanya',
                    glif: 'fa-magic',
                }).then(function (ya) { if (ya) kirim(); });

                return;
            }

            kirim();
        });
    })();
</script>
@endpush
