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

        /*
         * Tiap catatan satu KARTU, bukan baris bergaris bawah.
         *
         * Bergaris bawah, satu catatan yang isinya tiga baris (judul, deret
         * keterangan, kaki) terbaca menyatu dengan catatan berikutnya — dan
         * di layar yang isinya cuma satu catatan, yang tampak sekadar teks
         * yang tercecer di ruang kosong.
         */
        .thp-baris {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr) auto;
            gap: 4px 11px;
            align-items: start;
            margin-bottom: 11px;
            padding: 14px 16px;
            border: 1px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #fff;
        }

        .thp-baris:last-child {
            margin-bottom: 0;
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
            gap: 5px;
            font-size: .76rem;
            color: var(--mis-tinta-3);
        }

        /*
         * Kepingnya memakai .mis-lencana BERSAMA, bukan salinan berawalan
         * layar ini.
         *
         * Versi pertama membuat kepingnya sendiri — latar abu seragam untuk
         * kelimanya — dan warnanya karena itu tidak mengatakan apa-apa.
         * Kelas bersamanya sudah punya warna per keadaan yang sama persis
         * dengan lencana di layar daftar, jadi satu status berwarna sama di
         * kedua layar.
         */
        .thp-ket > span > i {
            font-size: 11px;
            opacity: .85;
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
         * Kaki kartunya: pembuka potret di kiri, tombol di kanan.
         *
         * Ditaruh di lajur 2/-1 supaya sejajar dengan isinya, bukan dengan
         * medali di lajur pertama. Membungkus di layar sempit — tombolnya
         * turun ke bawah pembuka potret, bukan menyempit jadi ikon.
         */
        .thp-kaki {
            grid-column: 2 / -1;
            display: flex;
            flex-wrap: wrap;
            /*
             * flex-start, BUKAN center. Panel rinciannya tumbuh ke bawah saat
             * dibuka, dan dengan center tombolnya ikut melorot ke tengah
             * panel — jauh dari baris pembuka yang baru saja ditekan.
             */
            align-items: flex-start;
            justify-content: space-between;
            gap: 9px;
            margin-top: 9px;
            padding-top: 9px;
            border-top: 1px dashed var(--mis-garis);
        }

        /* min-width 0 supaya <pre> potret yang lebar tidak memaksa kaki ini
           melebar dan mendorong tombolnya keluar kartu. */
        .thp-kaki > .thp-potret {
            flex: 1 1 220px;
            min-width: 0;
        }

        /* Dua tombol berdampingan di ujung kanan kaki kartunya. */
        .thp-aksi {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            flex: 0 0 auto;
        }

        .thp-pulih-borang,
        .thp-buang-borang {
            flex: 0 0 auto;
        }

        .thp-tombol-buang {
            height: 34px;
            padding: 0 13px;
            font-size: .76rem;
        }

        /* Merahnya terlihat TANPA hover: tombol yang tidak bisa diurungkan
           tidak boleh baru mengaku berbahaya saat disentuh. */
        .thp-tombol-buang:hover {
            border-color: #fecdd3;
            background: #fff1f2;
            color: #9f1239;
        }

        .thp-tombol-pulih {
            height: 34px;
            padding: 0 13px;
            font-size: .76rem;
        }

        .thp-potret {
            margin: 0;
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

        .thp-potret-isi {
            margin-top: 9px;
            padding: 13px 15px;
            border: 1px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #f8fafc;
        }

        /*
         * Pasangan label-nilai berkisi, bukan daftar menurun.
         *
         * Sepuluh keterangan yang ditumpuk satu per baris membuat panelnya
         * sepanjang dua layar; berkisi, semuanya terbaca sekali pandang.
         * auto-fill, BUKAN auto-fit: dengan auto-fit satu pasangan terakhir
         * melar memenuhi seluruh baris dan terbaca seperti judul.
         */
        .thp-pokok {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
            gap: 10px 16px;
            margin: 0;
        }

        .thp-pokok dt {
            margin: 0;
            font-size: .67rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--mis-tinta-4);
        }

        .thp-pokok dd {
            margin: 1px 0 0;
            font-size: .82rem;
            color: var(--mis-tinta);
            /* Email dan afiliasi panjang memecah kisinya kalau tidak
               diizinkan membungkus di tengah kata. */
            overflow-wrap: anywhere;
        }

        .thp-potret-bagian {
            margin-top: 13px;
            padding-top: 11px;
            border-top: 1px dashed var(--mis-garis);
        }

        .thp-potret-judul {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0 0 7px;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: var(--mis-tinta-3);
        }

        .thp-potret-daftar {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 6px;
        }

        .thp-potret-daftar li {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 4px 10px;
            font-size: .8rem;
            color: var(--mis-tinta-2);
        }

        .thp-potret-daftar strong { color: var(--mis-tinta); }
        .thp-potret-daftar span { font-size: .74rem; color: var(--mis-tinta-4); }

        /* Data mentahnya satu lapis lebih dalam: yang membutuhkannya
           pengembang, bukan panitia. */
        .thp-mentah { margin-top: 13px; }

        .thp-mentah > summary {
            cursor: pointer;
            font-size: .72rem;
            color: var(--mis-tinta-4);
            display: flex;
            align-items: center;
            gap: 6px;
            min-height: 32px;
        }

        .thp-mentah pre {
            max-height: 320px;
            overflow: auto;
            margin: 6px 0 0;
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

            .thp-kaki {
                grid-column: 2;
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
    /*
     * Penegasan sebelum membuang catatannya.
     *
     * Jenis 'bahaya', berbeda dari tombol Kembalikan di sebelahnya: INI yang
     * benar-benar tidak bisa diurungkan. Kalimatnya menyebut bukti bayarnya
     * hanya kalau memang masih ada yang menunggu di parkiran — menjanjikan
     * sesuatu yang sudah tidak ada membuat penegasannya berhenti dipercaya.
     */
    document.querySelectorAll('.thp-tombol-buang').forEach(function (tombol) {
        tombol.addEventListener('click', function (e) {
            if (tombol.dataset.lanjut === '1') {
                return;
            }

            e.preventDefault();

            window.misKonfirmasi({
                judul: 'Buang catatan ini permanen?',
                pesan: 'Potretnya hilang dan pendaftarannya tidak bisa dikembalikan lagi'
                    + (tombol.dataset.bukti === '1' ? ', termasuk bukti bayarnya' : '')
                    + '. %s',
                sorot: (tombol.dataset.nomor || '') + ' \u00b7 ' + (tombol.dataset.nama || ''),
                tombol: 'Ya, buang',
                jenis: 'bahaya',
            }).then(function (ya) {
                if (! ya) {
                    return;
                }

                tombol.dataset.lanjut = '1';
                tombol.closest('form').submit();
            });
        });
    });

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
                    href="{{ route('account.pendaftaran-layanan.terhapus.excel', array_filter(['cari' => $cari, 'keadaan' => $keadaan !== 'semua' ? $keadaan : ''])) }}">
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
        {{-- SATU kalimat, bukan empat.

             Versi sebelumnya menerangkan potret, penelusuran angka, apa saja
             yang ikut kembali, dan nasib catatannya sendiri — empat hal
             dalam satu paragraf selebar layar, di atas daftar yang sering
             cuma berisi satu baris. Yang perlu diketahui sebelum menekan
             apa pun cuma satu: dari sini barisnya bisa kembali. Sisanya
             sudah terbaca dari tombol dan lencananya sendiri. --}}
        <p class="thp-nota">
            <i class="fas fa-info-circle" aria-hidden="true"></i>
            <span>
                Dari sini pendaftaran yang dihapus <strong>bisa dikembalikan</strong>
                berikut peserta, pembayaran, dan bukti bayarnya.
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
        {{-- Lajurnya mengikuti JUMLAH ubinnya.
             Dipatok dua sementara ubin ketiga muncul saat ada yang
             dikembalikan, ubin itu jatuh sendirian ke baris kedua dan
             menyisakan lubang selebar separuh layar. --}}
        <div class="mis-ringkas-geser" data-mis-geser>
        <div id="thp-ringkas" class="mis-ringkas mis-ringkas-{{ $jumlahPulih > 0 ? '3' : '2' }}"
            aria-label="Ringkasan penghapusan">
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
                    <p class="mis-ubin-label">Uang yang masih hilang</p>
                </div>
            </div>
            {{-- Ubin ketiga hanya muncul kalau memang ada yang dikembalikan.
                 Selalu tampil, "0 dikembalikan" jadi angka ketiga yang tidak
                 pernah berarti apa-apa di layar yang cuma punya dua angka
                 sungguhan. --}}
            @if ($jumlahPulih > 0)
                <div class="mis-ubin">
                    <span class="mis-medali kecil mis-hijau" aria-hidden="true">
                        <i class="fas fa-undo"></i>
                    </span>
                    <div>
                        <p class="mis-ubin-angka">{{ number_format($jumlahPulih, 0, ',', '.') }}</p>
                        <p class="mis-ubin-label">Sudah dikembalikan</p>
                    </div>
                </div>
            @endif
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
            {{-- Saringan HIDUP, sama dengan layar Pendaftar Layanan.

                 Dulu borang biasa: memilih "Sudah dikembalikan" mengubah isi
                 menunya tetapi daftarnya diam sampai tombol Cari ditekan —
                 dan di layar sebelah, memilih saringan langsung menyaring.
                 Dua layar bersebelahan yang berperilaku berbeda membuat yang
                 memakainya menyangka saringannya rusak.

                 Ringkasannya ikut diperbarui lewat data-mis-saring-juga:
                 angkanya berubah mengikuti saringan, dan ubin yang tertinggal
                 pada angka lama lebih menyesatkan daripada tidak ada. --}}
            <form method="GET" class="thp-borang" id="thp-borang"
                data-mis-saring="thp-hasil" data-mis-saring-juga="thp-ringkas">
                <div class="mis-isian">
                    <label class="mis-label" for="thp-cari">Cari</label>
                    <input type="search" class="form-control-modern" id="thp-cari" name="cari"
                        data-mis-cari
                        value="{{ $cari }}" placeholder="Nomor, nama, atau email" autocomplete="off">
                </div>
                {{-- Saringan keadaan. Tanpa ini, yang masih terhapus dan yang
                     sudah dikembalikan tercampur dalam satu daftar — dan
                     satu-satunya pembedanya keping kecil di deret
                     keterangannya. --}}
                <div class="mis-isian">
                    <label class="mis-label" for="thp-keadaan">Keadaan</label>
                    <select class="form-control-modern" id="thp-keadaan" name="keadaan">
                        <option value="semua" @selected($keadaan === 'semua')>Semua catatan</option>
                        <option value="terhapus" @selected($keadaan === 'terhapus')>Masih terhapus</option>
                        <option value="pulih" @selected($keadaan === 'pulih')>Sudah dikembalikan</option>
                    </select>
                </div>
                {{-- Tetap ada sebagai cadangan tanpa JavaScript; penyaring
                     hidup menyembunyikannya sendiri saat skripnya jalan. --}}
                <button class="mis-tombol mis-tombol-ungu" type="submit" data-mis-terapkan>
                    <i class="fas fa-search" aria-hidden="true"></i> Cari
                </button>
                @if ($cari !== '' || $keadaan !== 'semua')
                    <a class="mis-tombol mis-tombol-halus"
                        href="{{ route('account.pendaftaran-layanan.terhapus') }}">
                        <i class="fas fa-times" aria-hidden="true"></i> Reset
                    </a>
                @endif
            </form>

            {{-- Wadah yang ditukar penyaring hidup. Isinya HANYA daftar dan
                 penomorannya: kepala, ubin, dan borangnya tinggal di luar
                 supaya kotak pencariannya tidak kehilangan fokus tiap huruf
                 yang diketik. --}}
            <div id="thp-hasil">

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
                            @php
                                /*
                                 * Warna tiap keping diambil dari KOSAKATA YANG
                                 * SUDAH ADA, bukan dikarang per keping.
                                 *
                                 * Layanan memakai warnanya sendiri di katalog
                                 * — Scopus Camp hijau, Bibliometrik ungu —
                                 * sehingga layanan yang sama berwarna sama di
                                 * layar mana pun. Statusnya memakai warna
                                 * keadaan yang dipakai lencana di layar daftar.
                                 * Dikarang sendiri di sini, satu layanan akan
                                 * punya dua warna di dua layar dan warnanya
                                 * berhenti berarti apa-apa.
                                 */
                                $warnaLayanan = str_replace(
                                    'mis-', '',
                                    \App\Support\PendaftaranSemuaLayanan::katalog()[$satu->layanan]['warna'] ?? 'abu'
                                );

                                $keadaanBaris = \App\Support\PendaftaranSemuaLayanan::keadaanDari($satu->status);
                                $warnaKeadaan = \App\Support\PendaftaranSemuaLayanan::KEADAAN[$keadaanBaris]['warna'] ?? 'abu';
                            @endphp

                            <div class="thp-ket">
                                <span class="mis-lencana mis-lencana-{{ $warnaLayanan }}">
                                    <i class="fas fa-tag" aria-hidden="true"></i> {{ $satu->layanan_nama }}
                                </span>
                                @if ($satu->status)
                                    <span class="mis-lencana mis-lencana-{{ $warnaKeadaan }}">
                                        <i class="fas fa-circle-notch" aria-hidden="true"></i> {{ $satu->status }}
                                    </span>
                                @endif
                                {{-- Siapa dan kapan tetap ABU. Keduanya keterangan,
                                     bukan keadaan — diberi warna sendiri, kepingnya
                                     ikut berebut mata dengan dua keping di
                                     sebelahnya yang warnanya memang berarti. --}}
                                <span class="mis-lencana mis-lencana-abu">
                                    <i class="fas fa-user-shield" aria-hidden="true"></i>
                                    {{ $satu->oleh_nama ?: 'tidak tercatat' }}
                                </span>
                                <span class="mis-lencana mis-lencana-abu">
                                    <i class="fas fa-clock" aria-hidden="true"></i>
                                    {{ optional($satu->created_at)->translatedFormat('d M Y, H:i') }}
                                </span>
                                @if ($satu->jumlah_jejak > 0)
                                    <span class="mis-lencana mis-lencana-ungu">
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
                                    <span class="mis-lencana mis-lencana-hijau">
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

                        {{-- Kaki kartunya: pembuka potret di kiri, tombol di
                             kanan, satu baris.

                             Keduanya DULU jadi anak kisi tersendiri, jadi satu
                             catatan memakan tiga baris longgar — nominalnya
                             melayang di kanan atas, tombolnya sendirian di baris
                             kedua, dan pembuka potretnya di baris ketiga paling
                             kiri. Yang terbaca bukan satu catatan melainkan tiga
                             potongan yang kebetulan berdekatan. --}}
                        <div class="thp-kaki">
                            {{-- Potretnya DIBACAKAN, bukan dicetak mentah.

                                 Dulu isinya JSON apa adanya di kotak hitam: 22
                                 kolom berisi token, UUID, dan created_at — format
                                 untuk pengembang, sementara yang membukanya panitia
                                 yang sedang menelusuri ke mana perginya satu
                                 pendaftaran. Datanya sendiri tidak diubah sedikit
                                 pun; PulihkanPendaftaran tetap membaca potret yang
                                 sama. --}}
                            <details class="thp-potret">
                                <summary>
                                    <i class="fas fa-file-alt" aria-hidden="true"></i>
                                    Lihat rincian yang tersimpan
                                </summary>

                                @php
                                    $pokok = \App\Support\PotretPendaftaran::pokok($satu);
                                    $pesertaPotret = \App\Support\PotretPendaftaran::peserta($satu);
                                    $terminPotret = \App\Support\PotretPendaftaran::pembayaran($satu);
                                    $jejakPotret = \App\Support\PotretPendaftaran::jejak($satu);
                                @endphp

                                <div class="thp-potret-isi">
                                    <dl class="thp-pokok">
                                        @foreach ($pokok as $label => $nilai)
                                            <div>
                                                <dt>{{ $label }}</dt>
                                                <dd>{{ $nilai }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>

                                    @if ($pesertaPotret !== [])
                                        <div class="thp-potret-bagian">
                                            <p class="thp-potret-judul">
                                                <i class="fas fa-users" aria-hidden="true"></i>
                                                Peserta rombongan
                                            </p>
                                            <ul class="thp-potret-daftar">
                                                @foreach ($pesertaPotret as $orangPotret)
                                                    <li>
                                                        <strong>{{ $orangPotret['nama'] }}</strong>
                                                        @if ($orangPotret['kontak'] !== '')
                                                            <span>{{ $orangPotret['kontak'] }}</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if ($terminPotret !== [])
                                        <div class="thp-potret-bagian">
                                            <p class="thp-potret-judul">
                                                <i class="fas fa-receipt" aria-hidden="true"></i>
                                                Pembayaran yang ikut terhapus
                                            </p>
                                            <ul class="thp-potret-daftar">
                                                @foreach ($terminPotret as $terminSatu)
                                                    <li>
                                                        <strong>{{ $terminSatu['nominal'] }}</strong>
                                                        <span>{{ $terminSatu['label'] }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if ($jejakPotret !== [])
                                        <div class="thp-potret-bagian">
                                            <p class="thp-potret-judul">
                                                <i class="fas fa-history" aria-hidden="true"></i>
                                                Riwayat perubahannya
                                            </p>
                                            <ul class="thp-potret-daftar">
                                                @foreach ($jejakPotret as $jejakSatu)
                                                    <li>
                                                        <strong>{{ $jejakSatu['kalimat'] }}</strong>
                                                        <span>{{ $jejakSatu['waktu'] }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    {{-- Data mentahnya TETAP ADA, satu lapis lebih
                                         dalam. Yang membutuhkannya pengembang saat
                                         pemulihan bermasalah, dan membuangnya berarti
                                         menutup satu-satunya jendela ke isi potret
                                         yang sebenarnya. --}}
                                    <details class="thp-mentah">
                                        <summary>
                                            <i class="fas fa-code" aria-hidden="true"></i>
                                            Data mentahnya
                                        </summary>
                                        {{-- JSON_PRETTY_PRINT + UNESCAPED_UNICODE: tanpa yang
                                             kedua, nama berhuruf non-ASCII tersimpan sebagai
                                             \uXXXX dan potretnya tidak terbaca orang. --}}
                                        <pre>{{ json_encode($satu->potret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                </div>
                            </details>

                            <div class="thp-aksi">
                                {{-- Buang permanen. Merah, dan di sebelah kiri
                                     tombol hijau: INI yang benar-benar tidak bisa
                                     diurungkan, bukan tombol Hapus di layar
                                     pendaftaran. --}}
                                <form method="POST" class="thp-buang-borang"
                                    action="{{ route('account.pendaftaran-layanan.buang-catatan', $satu->getKey()) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mis-tombol mis-tombol-halus thp-tombol-buang"
                                        data-nama="{{ $satu->nama ?: 'Tanpa nama' }}"
                                        data-nomor="{{ strtoupper($satu->nomor ?: '') }}"
                                        data-bukti="{{ $satu->bukti_parkir ? '1' : '0' }}">
                                        <i class="fas fa-trash-alt mis-ikon-merah" aria-hidden="true"></i>
                                        Buang catatan
                                    </button>
                                </form>

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
                            </div>
                        </div>
                    </div>
                @endforeach

                {{ $daftar->links('vendor.pagination.bootstrap-4') }}
            @endif

            </div>
        </div>

    </section>
</div>
@endsection
