@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Daftarkan Pendaftar | MIS Rumah Scopus
@stop

@push('gaya')
    <style>
        /* Lebar isi dibatasi: borang selebar 1600px membuat mata menyapu
           jauh untuk tiap barisnya, dan isian yang melar 700px tidak membantu
           siapa pun. */
        .bar-wadah {
            max-width: 980px;
        }

        .bar-langkah {
            margin-bottom: var(--mis-jarak);
        }

        .bar-langkah-kepala {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 14px;
        }

        /* Nomor langkah sebagai ubin bergradien — sama bahasanya dengan
           medali ikon, jadi tidak ada bentuk baru yang harus dipelajari. */
        .bar-nomor {
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            width: 30px;
            height: 30px;
            border-radius: 9px;
            background: var(--mis-ungu);
            color: #fff;
            font-size: .82rem;
            font-weight: 800;
        }

        .bar-langkah-judul {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: var(--mis-tinta);
        }

        .bar-langkah-sub {
            margin: 1px 0 0;
            font-size: .8rem;
            color: var(--mis-tinta-3);
        }

        /*
         * Pilihan layanan sebagai KARTU, bukan menu jatuh.
         *
         * Empat pilihan tetap, masing-masing punya warna dan ikonnya sendiri
         * yang sudah dipakai di seluruh layar pendaftaran — jadi kartunya
         * sekaligus mengajarkan warna mana milik layanan mana. Menu jatuh
         * menyembunyikan ketiganya sampai ditekan dan tidak menampilkan apa
         * pun selain nama.
         */
        .bar-pilihan {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 200px), 1fr));
            gap: 11px;
        }

        .bar-pilihan input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
        }

        /* Radio-nya unsur borang SUNGGUHAN yang disembunyikan, bukan dihapus:
           papan ketik, pembaca layar, dan pengiriman borang tetap bekerja. */
        .bar-kartu {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 14px;
            border: 1.5px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #fff;
            cursor: pointer;
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }

        .bar-kartu:hover {
            border-color: #c7d2fe;
            transform: translateY(-1px);
        }

        .bar-pilihan input:focus-visible + .bar-kartu {
            outline: 3px solid rgba(99, 102, 241, .5);
            outline-offset: 2px;
        }

        .bar-pilihan input:checked + .bar-kartu {
            border-color: #6366f1;
            box-shadow: 0 8px 20px -14px rgba(79, 70, 229, .9);
        }

        /* display: block WAJIB — keduanya <span> di dalam <label>, dan sebagai
           unsur sebaris nama dan keterangannya menyatu dalam satu baris.
           Terlihat pada kartu Scopus Kafe, yang namanya cukup pendek sehingga
           keterangannya ikut naik: "Scopus Kafe harga diketik sendiri". */
        .bar-kartu-nama {
            display: block;
            margin: 0;
            font-size: .88rem;
            font-weight: 700;
            color: var(--mis-tinta);
            line-height: 1.3;
        }

        .bar-kartu-ket {
            display: block;
            margin: 2px 0 0;
            font-size: .74rem;
            color: var(--mis-tinta-4);
        }

        /* Tanda centang muncul hanya pada yang terpilih. */
        .bar-kartu-centang {
            margin-left: auto;
            flex: 0 0 auto;
            color: #6366f1;
            opacity: 0;
            transition: opacity .18s ease;
        }

        .bar-pilihan input:checked + .bar-kartu .bar-kartu-centang {
            opacity: 1;
        }

        .bar-kartu-centang i {
            font-size: 17px;
        }

        .bar-isian-kisi {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 215px), 1fr));
            gap: 14px;
            align-content: start;
        }

        .bar-penuh {
            grid-column: 1 / -1;
        }

        /* Ringkasan biaya: menempel di bawah layar supaya angkanya terlihat
           tanpa menggulung kembali ke atas saat mengisi nama. */
        .bar-biaya {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px 18px;
            padding: 15px 18px;
            border: 1px solid var(--mis-garis);
            border-radius: var(--mis-radius);
            background: linear-gradient(135deg, #faf5ff 0%, #eef2ff 100%);
        }

        .bar-biaya-angka {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            color: var(--mis-tinta);
            line-height: 1.15;
        }

        .bar-biaya-label {
            margin: 1px 0 0;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--mis-tinta-4);
        }

        .bar-biaya-aksi {
            margin-left: auto;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        @media (max-width: 575.98px) {
            .bar-biaya-aksi {
                margin-left: 0;
                width: 100%;
            }

            .bar-biaya-aksi .mis-tombol {
                flex: 1 1 100%;
                justify-content: center;
            }
        }

        .bar-nota {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 13px 0 0;
            padding: 11px 14px;
            border: 1px solid #bfdbfe;
            border-radius: var(--mis-radius-kecil);
            background: #eff6ff;
            font-size: .79rem;
            line-height: 1.5;
            color: #1d4ed8;
        }

        .bar-nota i {
            flex: 0 0 auto;
            margin-top: 2px;
            font-size: inherit;
        }

        /* Bagian yang baru berlaku sesudah layanannya dipilih. Disembunyikan
           lewat atribut hidden, bukan kelas: tanpa JavaScript seluruhnya
           tetap terlihat dan borangnya masih bisa dipakai. */
        .bar-langkah[hidden] {
            display: none;
        }
    </style>
@endpush

@section('content')
@php
    use App\Support\PendaftaranSemuaLayanan as Pendaftaran;

    /*
     * Angkatan dirakit jadi data yang bisa dibaca skrip: pilihan angkatan
     * ditukar di peramban saat layanannya berganti, tanpa memuat ulang
     * halaman. Hanya kolom yang memang dipakai yang ikut — harga, kursi, dan
     * tanggalnya — bukan seluruh baris.
     */
    $angkatanJson = [];

    foreach ($angkatan as $kunciLayanan => $daftar) {
        $angkatanJson[$kunciLayanan] = $daftar->map(fn ($a) => [
            'id' => $a->id,
            'nama' => $a->nama,
            'mulai' => $a->mulai,
            'harga' => (int) ($a->total_biaya ?: $a->biaya),
            'total_kuota' => $a->total_kuota === null ? null : (int) $a->total_kuota,
            'sisa_kuota' => $a->sisa_kuota === null ? null : (int) $a->sisa_kuota,
        ])->values();
    }
@endphp
<div class="main-content mis-badan">
    <section class="section bar-wadah">

        <div class="mis-kepala">
            <span class="mis-medali mis-hijau" aria-hidden="true"><i class="fas fa-user-plus"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Daftarkan Pendaftar</h1>
                <p class="mis-sub">
                    Untuk yang mendaftar lewat WhatsApp, datang langsung, atau membayar di tempat.
                </p>
            </div>
            <div class="mis-kepala-aksi">
                <a class="mis-tombol mis-tombol-halus" href="{{ route('account.pendaftaran-layanan.index') }}">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali ke daftar
                </a>
            </div>
        </div>

        @include('account.pendaftaran_layanan.partials.pesan')

        <form method="POST" action="{{ route('account.pendaftaran-layanan.simpan') }}" id="bar-borang">
            @csrf

            {{-- ------------------------------------ langkah 1: layanan --}}
            <div class="mis-kartu bar-langkah">
                <div class="bar-langkah-kepala">
                    <span class="bar-nomor" aria-hidden="true">1</span>
                    <div>
                        <p class="bar-langkah-judul">Layanan apa?</p>
                        <p class="bar-langkah-sub">Pilih satu; isian berikutnya menyesuaikan sendiri.</p>
                    </div>
                </div>

                <div class="bar-pilihan">
                    @foreach ($katalog as $kunci => $l)
                        <label>
                            <input type="radio" name="layanan" value="{{ $kunci }}"
                                data-berangkatan="{{ $l['berangkatan'] ? '1' : '0' }}"
                                @checked(old('layanan', $terpilih) === $kunci) required>
                            <span class="bar-kartu">
                                <span class="mis-medali {{ $l['warna'] }}" aria-hidden="true">
                                    <i class="fas {{ $l['ikon'] }}"></i>
                                </span>
                                <span style="min-width: 0;">
                                    <span class="bar-kartu-nama">{{ $l['nama'] }}</span>
                                    <span class="bar-kartu-ket">
                                        {{ $l['berangkatan'] ? 'berangkatan, harga ikut angkatan' : 'harga diketik sendiri' }}
                                    </span>
                                </span>
                                <span class="bar-kartu-centang" aria-hidden="true"><i class="fas fa-check-circle"></i></span>
                            </span>
                        </label>
                    @endforeach
                </div>

                {{-- Disebut apa adanya, bukan dibiarkan jadi pertanyaan: layanan
                     yang tidak ada di sini memang tidak bisa didaftarkan dari
                     layar ini, dan alasannya bukan kelalaian. --}}
                <p class="bar-nota">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    <span>
                        <strong>Clinik Scopus tidak ada di sini.</strong> Pemesanannya mengikat
                        sesi tertentu, trainer yang mendampingi, dan akun pelanggan — ketiganya
                        dipilih lewat alur pemesanan Clinik Scopus sendiri.
                    </span>
                </p>
            </div>

            {{-- ----------------------------------- langkah 2: angkatan --}}
            <div class="mis-kartu bar-langkah" id="bar-langkah-angkatan">
                <div class="bar-langkah-kepala">
                    <span class="bar-nomor" aria-hidden="true">2</span>
                    <div>
                        <p class="bar-langkah-judul">Angkatan mana?</p>
                        <p class="bar-langkah-sub">Harga dan sisa kursinya ikut dari angkatan yang dipilih.</p>
                    </div>
                </div>

                <div class="bar-isian-kisi">
                    <div class="mis-isian bar-penuh">
                        <label class="mis-label" for="bar-angkatan">Angkatan</label>
                        <select class="form-control-modern" id="bar-angkatan" name="kategori_id">
                            <option value="">Pilih layanan dulu</option>
                        </select>
                        <p class="mis-bantuan" id="bar-angkatan-ket">
                            Hanya angkatan yang belum lewat yang ditawarkan.
                        </p>
                    </div>
                </div>
            </div>

            {{-- ------------------------------------- langkah 3: orangnya --}}
            <div class="mis-kartu bar-langkah">
                <div class="bar-langkah-kepala">
                    <span class="bar-nomor" aria-hidden="true">3</span>
                    <div>
                        <p class="bar-langkah-judul">Siapa yang mendaftar?</p>
                        <p class="bar-langkah-sub">Empat isian; sisanya diisi sistem sendiri.</p>
                    </div>
                </div>

                <div class="bar-isian-kisi">
                    <div class="mis-isian">
                        <label class="mis-label" for="bar-nama">Nama lengkap</label>
                        <input type="text" class="form-control-modern" id="bar-nama" name="nama"
                            value="{{ old('nama') }}" required maxlength="255" autocomplete="off">
                    </div>

                    <div class="mis-isian">
                        <label class="mis-label" for="bar-email">Email</label>
                        <input type="email" class="form-control-modern" id="bar-email" name="email"
                            value="{{ old('email') }}" required maxlength="255" autocomplete="off">
                    </div>

                    <div class="mis-isian">
                        <label class="mis-label" for="bar-telp">Nomor WhatsApp</label>
                        <input type="text" class="form-control-modern" id="bar-telp" name="telp"
                            value="{{ old('telp') }}" required maxlength="30" autocomplete="off"
                            inputmode="tel" placeholder="08xx atau 62xx">
                    </div>

                    <div class="mis-isian">
                        <label class="mis-label" for="bar-affiliasi">Afiliasi / instansi</label>
                        <input type="text" class="form-control-modern" id="bar-affiliasi" name="affiliasi"
                            value="{{ old('affiliasi') }}" maxlength="255" autocomplete="off"
                            placeholder="boleh dikosongkan">
                    </div>

                    <div class="mis-isian" id="bar-bungkus-jumlah">
                        <label class="mis-label" for="bar-jumlah">Jumlah orang</label>
                        <input type="number" class="form-control-modern" id="bar-jumlah" name="jumlah"
                            value="{{ old('jumlah', 1) }}" min="1" max="99">
                        <p class="mis-bantuan">Untuk pendaftaran rombongan.</p>
                    </div>

                    <div class="mis-isian" id="bar-bungkus-total" hidden>
                        <label class="mis-label" for="bar-total">Total bayar</label>
                        <input type="text" class="form-control-modern" id="bar-total" name="total"
                            value="{{ old('total') }}" inputmode="numeric" placeholder="contoh: 250000">
                        <p class="mis-bantuan">Dalam rupiah, tanpa titik.</p>
                    </div>

                    <div class="mis-isian bar-penuh">
                        <label class="mis-label" for="bar-note">Catatan panitia</label>
                        <textarea class="form-control-modern" id="bar-note" name="note" rows="2"
                            maxlength="1000" placeholder="boleh dikosongkan">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- -------------------------------------------- ringkasan --}}
            <div class="bar-biaya">
                <div>
                    <p class="bar-biaya-angka" id="bar-angka">Rp 0</p>
                    <p class="bar-biaya-label">Perkiraan total bayar</p>
                </div>

                <p class="mis-bantuan" style="margin: 0; max-width: 340px;">
                    {{-- Disebut di muka supaya nomor dan kode unik yang muncul
                         di halaman berikutnya tidak terasa datang entah dari
                         mana. --}}
                    Nomor pendaftaran, status, dan kode unik dibuat sistem sesudah disimpan.
                    Kode unik yang membuat nominalnya bisa dicocokkan dengan mutasi rekening.
                </p>

                <div class="bar-biaya-aksi">
                    <a class="mis-tombol mis-tombol-halus" href="{{ route('account.pendaftaran-layanan.index') }}">
                        Batal
                    </a>
                    <button type="submit" class="mis-tombol mis-tombol-ungu">
                        <i class="fas fa-save" aria-hidden="true"></i> Simpan pendaftaran
                    </button>
                </div>
            </div>
        </form>

    </section>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        'use strict';

        /*
         * Pilihan angkatan ditukar di peramban saat layanannya berganti —
         * tanpa memuat ulang halaman, dan tanpa satu kueri pun tambahan:
         * seluruh angkatan keempat layanan sudah ikut terkirim bersama
         * halamannya. Daftarnya 59 baris di seluruh basis data, jadi
         * mengirimnya sekaligus jauh lebih murah daripada satu permintaan
         * tiap kali pilihannya berubah.
         */
        var ANGKATAN = @json($angkatanJson);
        var LAMA_ANGKATAN = @json(old('kategori_id'));

        var borang = document.getElementById('bar-borang');

        if (!borang) {
            return;
        }

        var menuAngkatan = document.getElementById('bar-angkatan');
        var langkahAngkatan = document.getElementById('bar-langkah-angkatan');
        var bungkusTotal = document.getElementById('bar-bungkus-total');
        var bungkusJumlah = document.getElementById('bar-bungkus-jumlah');
        var isianTotal = document.getElementById('bar-total');
        var isianJumlah = document.getElementById('bar-jumlah');
        var angka = document.getElementById('bar-angka');
        var ket = document.getElementById('bar-angkatan-ket');

        var rupiah = function (n) {
            return 'Rp ' + (n || 0).toLocaleString('id-ID');
        };

        var layananTerpilih = function () {
            var r = borang.querySelector('input[name="layanan"]:checked');
            return r ? { nilai: r.value, berangkatan: r.dataset.berangkatan === '1' } : null;
        };

        var isiAngkatan = function (layanan) {
            var daftar = ANGKATAN[layanan] || [];

            menuAngkatan.innerHTML = '';

            if (!daftar.length) {
                var kosong = document.createElement('option');
                kosong.value = '';
                kosong.textContent = 'Belum ada angkatan yang akan datang';
                menuAngkatan.appendChild(kosong);
                ket.textContent = 'Buat angkatannya dulu di layar Angkatan Layanan.';
                return;
            }

            daftar.forEach(function (a) {
                var o = document.createElement('option');
                o.value = a.id;
                o.dataset.harga = a.harga;
                o.dataset.sisa = a.sisa_kuota === null ? '' : a.sisa_kuota;

                /*
                 * Harga dan sisa kursi ikut tertulis di pilihannya.
                 *
                 * Tanpa itu panitia harus membuka layar Angkatan Layanan untuk
                 * tahu angkatan mana yang masih longgar — dan angkatan yang
                 * sudah penuh baru ketahuan sesudah kirimannya ditolak.
                 */
                var sisa = a.sisa_kuota === null
                    ? 'tanpa batas kuota'
                    : ('sisa ' + a.sisa_kuota + ' kursi');

                o.textContent = a.nama + ' — ' + rupiah(a.harga) + ' · ' + sisa;
                menuAngkatan.appendChild(o);
            });

            ket.textContent = 'Hanya angkatan yang belum lewat yang ditawarkan.';

            if (LAMA_ANGKATAN) {
                menuAngkatan.value = LAMA_ANGKATAN;
                LAMA_ANGKATAN = null;
            }
        };

        var hitung = function () {
            var pilih = layananTerpilih();

            if (!pilih) {
                angka.textContent = 'Rp 0';
                return;
            }

            if (pilih.berangkatan) {
                var o = menuAngkatan.options[menuAngkatan.selectedIndex];
                var harga = o ? parseInt(o.dataset.harga || '0', 10) : 0;
                var jml = parseInt(isianJumlah.value || '1', 10);

                angka.textContent = rupiah(harga * (isNaN(jml) || jml < 1 ? 1 : jml));
                return;
            }

            // Nominal diketik: yang bukan angka dibuang, sama dengan cara
            // peladen membacanya — jadi angka di layar dan yang tersimpan
            // tidak pernah berbeda.
            var ketik = parseInt((isianTotal.value || '').replace(/\D+/g, ''), 10);
            angka.textContent = rupiah(isNaN(ketik) ? 0 : ketik);
        };

        var segarkan = function () {
            var pilih = layananTerpilih();

            if (!pilih) {
                langkahAngkatan.hidden = true;
                bungkusTotal.hidden = true;
                hitung();
                return;
            }

            langkahAngkatan.hidden = !pilih.berangkatan;
            bungkusTotal.hidden = pilih.berangkatan;
            // Rombongan hanya berlaku untuk layanan berangkatan; dua layanan
            // lainnya satu baris memang satu orang.
            bungkusJumlah.hidden = !pilih.berangkatan;

            menuAngkatan.required = pilih.berangkatan;
            isianTotal.required = !pilih.berangkatan;

            if (pilih.berangkatan) {
                isiAngkatan(pilih.nilai);
            }

            hitung();
        };

        borang.addEventListener('change', function (e) {
            if (e.target.name === 'layanan') {
                segarkan();
            } else if (e.target === menuAngkatan || e.target === isianJumlah) {
                hitung();
            }
        });

        isianTotal.addEventListener('input', hitung);
        isianJumlah.addEventListener('input', hitung);

        segarkan();
    })();
</script>
@endpush
