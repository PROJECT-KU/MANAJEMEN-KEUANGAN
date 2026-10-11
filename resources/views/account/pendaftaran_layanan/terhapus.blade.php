@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Catatan Penghapusan | MIS Rumah Scopus
@stop

{{--
  Catatan pendaftaran yang sudah dihapus.

  Gaya khusus lewat @push('gaya'), WAJIB — <style> di badan berkas terbit
  sebelum CSS Bootstrap, jadi aturan berbobot sama selalu kalah.
--}}
@push('gaya')
    <style>
        /*
         * Nota khas layar ini. Tidak memakai .mis-nota — kelas itu TIDAK ADA
         * di mis-ui.css, dan markah yang memanggil kelas hantu tampil sebagai
         * paragraf polos tanpa galat apa pun. Layar rincian pun memakai
         * versinya sendiri (.rin-nota).
         */
        .thp-nota {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-bottom: var(--mis-jarak);
            padding: 12px 15px;
            border: 1px solid #bfdbfe;
            border-radius: 13px;
            background: #eff6ff;
            font-size: .83rem;
            line-height: 1.55;
            color: #1e3a8a;
        }

        .thp-nota > i {
            margin-top: 2px;
            color: #2563eb;
        }

        .thp-borang {
            display: flex;
            flex-wrap: wrap;
            gap: 11px;
            align-items: flex-end;
            margin-bottom: 15px;
        }

        .thp-borang .mis-isian {
            flex: 1 1 240px;
            min-width: 0;
        }

        .thp-baris {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr) auto;
            gap: 11px;
            align-items: start;
            padding: 13px 0;
            border-bottom: 1px solid var(--mis-garis);
        }

        .thp-baris:last-child {
            border-bottom: 0;
        }

        .thp-nomor {
            display: block;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--mis-tinta-4);
        }

        .thp-nama {
            margin: 1px 0 3px;
            font-size: .9rem;
            font-weight: 700;
            color: var(--mis-tinta);
        }

        .thp-ket {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 12px;
            font-size: .78rem;
            color: var(--mis-tinta-3);
        }

        .thp-uang {
            font-size: .9rem;
            font-weight: 800;
            color: #be123c;
            white-space: nowrap;
        }

        .thp-uang-nol {
            color: var(--mis-tinta-4);
            font-weight: 600;
        }

        /* Potretnya dilipat: isinya JSON penuh, dan dua belas baris terbuka
           sekaligus membuat halamannya tidak bisa dipindai sama sekali. */
        /*
         * Borangnya ditempatkan TEGAS di kisinya.
         *
         * .thp-baris berkisi tiga lajur (38px / isi / nominal) dan anak
         * keempat jatuh sendiri ke lajur pertama yang cuma 38px — tombolnya
         * terjepit jadi sepotong ikon tanpa tulisan. Ditaruh 2/-1 dan
         * dirapatkan ke kanan, ia duduk di bawah nominalnya, sejajar
         * tepi kanan barisnya.
         */
        .thp-pulih-borang {
            grid-column: 2 / -1;
            justify-self: end;
            margin-top: 4px;
        }

        .thp-tombol-pulih {
            height: 34px;
            padding: 0 13px;
            font-size: .76rem;
        }

        /* Hijau muda, bukan sekadar abu: barisnya sudah TIDAK hilang lagi,
           dan itu kabar baik yang harus terbaca sekilas di antara baris
           merah di sekitarnya. */
        .thp-pulih {
            padding: 1px 8px;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            font-weight: 700;
        }

        .thp-potret {
            grid-column: 1 / -1;
            margin-top: 6px;
        }

        .thp-potret > summary {
            cursor: pointer;
            font-size: .78rem;
            font-weight: 600;
            color: var(--mis-tinta-2);
            /* 40px: sasaran ketuk di ponsel. Ringkasan setinggi barisnya
               sendiri (±18px) meleset lebih sering daripada kena. */
            min-height: 40px;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .thp-potret pre {
            max-height: 320px;
            overflow: auto;
            margin: 0;
            padding: 11px 13px;
            border-radius: 11px;
            background: #0f172a;
            color: #e2e8f0;
            font-size: .72rem;
            line-height: 1.5;
        }

        @media (max-width: 575.98px) {
            .thp-baris {
                grid-template-columns: 38px minmax(0, 1fr);
            }

            /* Nominalnya turun ke bawah namanya, bukan terjepit di lajur
               ketiga selebar 60px yang memecah "Rp 1.250.000" jadi tiga
               baris. */
            .thp-uang {
                grid-column: 2;
            }

            .thp-pulih-borang {
                grid-column: 2;
                justify-self: start;
            }
        }
    </style>
@endpush

@push('skrip')
<script>
    /*
     * Penegasan sebelum mengembalikan.
     *
     * Bukan karena berbahaya — ia justru kebalikan dari menghapus — melainkan
     * karena ia MENGUBAH PEMBUKUAN: nominal yang sudah diterima masuk lagi ke
     * ringkasan, dan kuota angkatannya diambil lagi. Dialognya menyebut nama
     * dan nomornya; penegasan yang cuma bertanya "yakin?" dijawab ya tanpa
     * dibaca.
     */
    document.querySelectorAll('.thp-tombol-pulih').forEach(function (tombol) {
        tombol.addEventListener('click', function (e) {
            if (tombol.dataset.lanjut === '1') {
                return;
            }

            e.preventDefault();

            window.misKonfirmasi({
                judul: 'Kembalikan pendaftaran ini?',
                pesan: 'Barisnya muncul lagi di daftar pendaftar, kuota angkatannya '
                    + 'diambil lagi, dan pembayarannya kembali terhitung. %s',
                sorot: (tombol.dataset.nomor || '') + ' · ' + (tombol.dataset.nama || ''),
                tombol: 'Ya, kembalikan',
                jenis: 'tanya',
            }).then(function (ya) {
                if (! ya) {
                    return;
                }

                // Penanda supaya klik kedua yang kita picu sendiri tidak
                // membuka dialognya lagi — tanpa ini borangnya tidak pernah
                // terkirim dan tombolnya terlihat mati.
                tombol.dataset.lanjut = '1';
                tombol.closest('form').submit();
            });
        });
    });
</script>
@endpush

@section('content')
<div class="main-content mis-badan">
    <section class="section">

        <div class="mis-kepala">
            <span class="mis-medali mis-merah" aria-hidden="true"><i class="fas fa-trash-alt"></i></span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Catatan penghapusan</h1>
                <p class="mis-sub">
                    Pendaftaran yang sudah dihapus, beserta uang yang ikut terhapus bersamanya.
                </p>
            </div>
            <div class="mis-kepala-aksi mis-kepala-aksi-pasangan">
                {{-- Unduhannya membawa pencarian yang sedang dipakai, sama
                     seperti kedua unduhan di layar daftar: yang diunduh orang
                     hampir selalu yang sedang dilihatnya. --}}
                <a class="mis-tombol mis-tombol-halus"
                    href="{{ route('account.pendaftaran-layanan.terhapus.excel', array_filter(['cari' => $cari])) }}">
                    <i class="fas fa-file-excel mis-ikon-hijau" aria-hidden="true"></i> Unduh Excel
                </a>
                <a class="mis-tombol mis-tombol-halus"
                    href="{{ route('account.pendaftaran-layanan.index') }}">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali ke daftar
                </a>
            </div>
        </div>

        {{-- Disebut di muka, bukan di catatan kaki.

             Kalimatnya DULU berbunyi "bukan tong sampah — pendaftarannya
             sendiri tidak bisa dikembalikan", dan itu benar sampai tombol
             Kembalikan ada. Dibiarkan, layarnya menyangkal tombol yang
             terpampang di tiap barisnya sendiri. --}}
        <p class="thp-nota">
            <i class="fas fa-info-circle" aria-hidden="true"></i>
            <span>
                Isinya potret keadaan terakhir tiap pendaftaran yang dihapus, supaya
                angkanya tetap bisa ditelusuri — dan dari sini pendaftarannya
                <strong>bisa dikembalikan</strong> berikut peserta, pembayaran, dan
                bukti bayarnya. Catatannya sendiri tidak ikut hilang saat dikembalikan;
                ia cuma ditandai.
            </span>
        </p>

        {{-- Ubinnya memakai .mis-ringkas/.mis-ubin apa adanya, BUKAN salinan
             berawalan layar ini. Dijaga RupaBersamaTest, dan penjaganya benar:
             tiap layar yang membuat ubinnya sendiri menambah satu dialek gaya
             lagi, dan sesudah belasan layar tidak ada satu tuas pun untuk
             mengubah semuanya sekaligus. --}}
        {{-- Pembungkus geser + petunjuknya, sama dengan layar daftar.
             Tanpa itu, di ponsel ubin keduanya meluber ke kanan tanpa satu
             tanda pun bahwa barisnya masih berlanjut. --}}
        <div class="mis-ringkas-geser" data-mis-geser>
        <div class="mis-ringkas mis-ringkas-2" aria-label="Ringkasan penghapusan">
            <div class="mis-ubin">
                <span class="mis-medali kecil mis-merah" aria-hidden="true">
                    <i class="fas fa-trash-alt"></i>
                </span>
                <div>
                    <p class="mis-ubin-angka">{{ number_format($jumlahSemua, 0, ',', '.') }}</p>
                    <p class="mis-ubin-label">Pendaftaran yang sudah dihapus</p>
                </div>
            </div>
            <div class="mis-ubin">
                <span class="mis-medali kecil mis-merah" aria-hidden="true">
                    <i class="fas fa-money-bill-wave"></i>
                </span>
                <div>
                    <p class="mis-ubin-angka">Rp {{ number_format($uangSemua, 0, ',', '.') }}</p>
                    <p class="mis-ubin-label">Uang tercatat yang ikut terhapus</p>
                </div>
            </div>
        </div>
            <p class="mis-ringkas-petunjuk" aria-hidden="true">
                <i class="fas fa-arrows-alt-h"></i> Geser untuk lihat semua
            </p>
        </div>

        <div class="mis-kartu">
            {{-- form-control-modern, bukan kelas karangan sendiri: itu yang
                 dipakai kotak cari di layar daftarnya, dan isian yang
                 memanggil kelas yang tidak ada tampil sebagai kotak telanjang
                 tanpa galat apa pun. --}}
            <form method="GET" class="thp-borang">
                <div class="mis-isian">
                    <label class="mis-label" for="thp-cari">Cari</label>
                    <input type="search" class="form-control-modern" id="thp-cari" name="cari"
                        value="{{ $cari }}" placeholder="Nomor, nama, atau email" autocomplete="off">
                </div>
                <button class="mis-tombol mis-tombol-ungu" type="submit">
                    <i class="fas fa-search" aria-hidden="true"></i> Cari
                </button>
                @if ($cari !== '')
                    <a class="mis-tombol mis-tombol-halus"
                        href="{{ route('account.pendaftaran-layanan.terhapus') }}">
                        <i class="fas fa-times" aria-hidden="true"></i> Reset
                    </a>
                @endif
            </form>

            @if ($daftar->isEmpty())
                {{-- Nama kelasnya mengikuti mis-ui.css apa adanya:
                     .mis-kosong-ikon dan .mis-kosong-teks. Sempat ditulis
                     .mis-kosong-sub dari ingatan, dan kelas yang tidak ada
                     tidak menimbulkan galat — ia cuma tampil tanpa gaya. --}}
                <div class="mis-kosong {{ $cari === '' ? '' : 'mis-kosong-cari' }}">
                    <span class="mis-kosong-ikon" aria-hidden="true">
                        <i class="fas {{ $cari === '' ? 'fa-check' : 'fa-search' }}"></i>
                    </span>
                    <p class="mis-kosong-judul">
                        {{ $cari === '' ? 'Belum ada pendaftaran yang dihapus' : 'Tidak ada yang cocok' }}
                    </p>
                    <p class="mis-kosong-teks">
                        {{ $cari === ''
                            ? 'Setiap penghapusan akan tercatat di sini beserta potretnya.'
                            : 'Coba kata kunci lain, atau reset pencariannya.' }}
                    </p>
                </div>
            @else
                @foreach ($daftar as $satu)
                    <div class="thp-baris">
                        <span class="mis-medali kecil mis-merah" aria-hidden="true">
                            <i class="fas fa-trash-alt"></i>
                        </span>

                        <div style="min-width: 0;">
                            <span class="thp-nomor">{{ strtoupper($satu->nomor ?: '—') }}</span>
                            <p class="thp-nama">{{ $satu->nama ?: 'Tanpa nama' }}</p>
                            <div class="thp-ket">
                                <span><i class="fas fa-tag" aria-hidden="true"></i> {{ $satu->layanan_nama }}</span>
                                @if ($satu->status)
                                    <span><i class="fas fa-circle-notch" aria-hidden="true"></i> {{ $satu->status }}</span>
                                @endif
                                <span>
                                    <i class="fas fa-user-shield" aria-hidden="true"></i>
                                    {{ $satu->oleh_nama ?: 'tidak tercatat' }}
                                </span>
                                <span>
                                    <i class="fas fa-clock" aria-hidden="true"></i>
                                    {{ optional($satu->created_at)->translatedFormat('d M Y, H:i') }}
                                </span>
                                @if ($satu->jumlah_jejak > 0)
                                    <span>
                                        <i class="fas fa-history" aria-hidden="true"></i>
                                        {{ $satu->jumlah_jejak }} jejak
                                    </span>
                                @endif
                                {{-- Catatannya TIDAK dihapus saat dikembalikan,
                                     melainkan ditandai. Catatan penghapusan
                                     gunanya menjawab "ke mana perginya
                                     pendaftaran itu", dan baris yang lenyap saat
                                     dipulihkan membuat pertanyaan itu tidak
                                     terjawab untuk kedua kalinya. --}}
                                @if ($satu->dipulihkan_at)
                                    <span class="thp-pulih">
                                        <i class="fas fa-undo" aria-hidden="true"></i>
                                        dikembalikan
                                        {{ $satu->dipulihkan_at->translatedFormat('d M Y, H:i') }}
                                        @if ($satu->dipulihkan_oleh)
                                            oleh {{ $satu->dipulihkan_oleh }}
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>

                        <span class="thp-uang {{ $satu->uang_terhapus > 0 ? '' : 'thp-uang-nol' }}"
                            title="{{ $satu->uang_terhapus > 0
                                ? $satu->jumlah_pembayaran . ' catatan pembayaran ikut terhapus'
                                : 'Tidak ada pembayaran tercatat; tagihannya ' . $satu->total_tulis }}">
                            @if ($satu->uang_terhapus > 0)
                                &minus; {{ $satu->uang_terhapus_tulis }}
                            @else
                                tanpa pembayaran
                            @endif
                        </span>

                        @if (! $satu->dipulihkan_at)
                            <form method="POST" class="thp-pulih-borang"
                                action="{{ route('account.pendaftaran-layanan.pulihkan', $satu->getKey()) }}">
                                @csrf
                                {{-- Nama & nomor dititipkan ke tombolnya supaya
                                     dialog penegasannya menyebut SIAPA yang akan
                                     kembali. Penegasan yang cuma bertanya "yakin?"
                                     dijawab ya tanpa dibaca. --}}
                                <button type="submit" class="mis-tombol mis-tombol-halus thp-tombol-pulih"
                                    data-nama="{{ $satu->nama ?: 'Tanpa nama' }}"
                                    data-nomor="{{ strtoupper($satu->nomor ?: '') }}">
                                    <i class="fas fa-undo mis-ikon-hijau" aria-hidden="true"></i>
                                    Kembalikan
                                </button>
                            </form>
                        @endif

                        <details class="thp-potret">
                            <summary>
                                <i class="fas fa-code" aria-hidden="true"></i>
                                Lihat potret lengkapnya
                            </summary>
                            {{-- JSON_PRETTY_PRINT + UNESCAPED_UNICODE: tanpa yang
                                 kedua, nama berhuruf non-ASCII tersimpan sebagai
                                 \uXXXX dan potretnya tidak terbaca orang. --}}
                            <pre>{{ json_encode($satu->potret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                        </details>
                    </div>
                @endforeach

                {{ $daftar->links('vendor.pagination.bootstrap-4') }}
            @endif
        </div>

    </section>
</div>
@endsection
