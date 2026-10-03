@extends('layouts.account')
@extends('layouts.loader')

@section('title')
Rincian Pendaftaran | MIS Rumah Scopus
@stop

@push('gaya')
    <style>
        /* Dua kolom: kartu identitas di kiri (tetap), borangnya di kanan
           (melar). align-items: start, BUKAN stretch — dengan stretch kartu
           identitas yang isinya pendek ikut setinggi kolom borang dan
           sisanya jadi petak putih di dalam kartunya. */
        .rin-kisi {
            display: grid;
            grid-template-columns: minmax(min(100%, 300px), 340px) minmax(0, 1fr);
            gap: var(--mis-jarak);
            align-items: start;
        }

        @media (max-width: 991.98px) {
            .rin-kisi {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        .rin-identitas-kepala {
            display: flex;
            align-items: center;
            gap: 11px;
            padding-bottom: 13px;
            margin-bottom: 13px;
            border-bottom: 1px dashed var(--mis-garis);
        }

        .rin-nama {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--mis-tinta);
            line-height: 1.3;
            overflow-wrap: anywhere;
        }

        .rin-layanan {
            margin: 2px 0 0;
            font-size: .78rem;
            color: var(--mis-tinta-3);
        }

        /* Daftar keterangan: label di atas, nilai di bawah. Bukan dua kolom
           berdampingan — di 320px label dan nilai berdesakan jadi dua kata
           per baris. */
        .rin-daftar {
            display: grid;
            gap: 11px;
            margin: 0;
        }

        .rin-label {
            margin: 0;
            font-size: .66rem;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: var(--mis-tinta-4);
        }

        .rin-nilai {
            margin: 1px 0 0;
            font-size: .85rem;
            color: var(--mis-tinta);
            overflow-wrap: anywhere;
        }

        .rin-nilai a {
            color: var(--mis-tinta);
            text-decoration: none;
        }

        .rin-nilai a:hover {
            text-decoration: underline;
        }

        .rin-nilai i {
            font-size: inherit;
        }

        .rin-kosong {
            color: var(--mis-tinta-4);
        }

        /* Kisi isian: tiga kolom di layar lebar, satu di ponsel, tanpa titik
           putus yang harus dijaga satu per satu. minmax(min(100%, 210px), ..)
           supaya lantainya bisa ditembus di 320px. */
        .rin-isian-kisi {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 210px), 1fr));
            gap: 13px;
            align-content: start;
        }

        .rin-isian-penuh {
            grid-column: 1 / -1;
        }

        .rin-bagian-judul {
            margin: 0 0 11px;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: var(--mis-tinta-4);
        }

        .rin-bagian + .rin-bagian {
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px dashed var(--mis-garis);
        }

        .rin-kaki {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
            padding-top: 15px;
            border-top: 1px solid var(--mis-garis);
        }

        /* Baris status: pil keadaan sekarang, lalu menu dan tombolnya. */
        .rin-status-baris {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 11px;
        }

        .rin-status-baris .mis-isian {
            flex: 1 1 200px;
            min-width: 0;
        }

        .rin-peringatan {
            margin: 11px 0 0;
            padding: 10px 13px;
            border: 1px solid #fde68a;
            border-radius: var(--mis-radius-kecil);
            background: #fffbeb;
            font-size: .79rem;
            color: #92400e;
        }

        .rin-bahaya {
            border-color: #fecdd3;
            background: #fff1f2;
            color: #9f1239;
        }

        .rin-bukti-gambar {
            display: block;
            width: 100%;
            max-height: 240px;
            object-fit: contain;
            margin-top: 8px;
            border: 1px solid var(--mis-garis);
            border-radius: var(--mis-radius-kecil);
            background: #f8fafc;
        }
    </style>
@endpush

@section('content')
@php
    use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
    use App\Support\PesananPelanggan;

    $keadaan = Pendaftaran::keadaanDari($pendaftaran->status);
    $rupa = Pendaftaran::KEADAAN[$keadaan] ?? ['label' => 'Belum dikenali', 'warna' => 'abu', 'ikon' => 'fa-circle-notch'];
    $bukti = $baris ? Pendaftaran::buktiBaris($baris) : ['url' => null, 'ada' => false, 'nilai' => false];
    $sesiBaris = $baris ? Pendaftaran::sesiBaris($baris) : null;
    $waktuBaris = $baris ? Pendaftaran::waktuBaris($baris) : null;

    $nomor = $pendaftaran->id_transaksi ?? $pendaftaran->id_pemesanan ?? $pendaftaran->getKey();
    $namaOrang = $pendaftaran->nama ?? $pendaftaran->nama_pemesan ?? 'Tanpa nama';
    $emailOrang = $pendaftaran->email ?? $pendaftaran->email_pemesan ?? '';
    $telpOrang = $pendaftaran->telp ?? $pendaftaran->telp_pemesan ?? '';
    $afiliasiOrang = $pendaftaran->affiliasi ?? $pendaftaran->afiliasi_pemesan ?? '';
    $wa = PesananPelanggan::nomorWa($telpOrang);

    /*
     * Label dan jenis isian tiap medan, dirakit di sini karena ia urusan
     * tampilan. Medan yang BOLEH disunting datang dari $medan — daftar putih
     * di UbahDataPendaftaran — jadi menambah medan di sana cukup menambah
     * labelnya di sini, dan medan tanpa label tidak pernah tergambar.
     */
    $label = [
        'nama' => 'Nama lengkap', 'nama_pemesan' => 'Nama pemesan',
        'email' => 'Email', 'email_pemesan' => 'Email',
        'telp' => 'Nomor WhatsApp', 'telp_pemesan' => 'Nomor WhatsApp',
        'affiliasi' => 'Afiliasi / instansi', 'afiliasi_pemesan' => 'Afiliasi / instansi',
        'kategori_id' => 'Angkatan', 'jumlah_pendaftar' => 'Jumlah orang',
        'ppn' => 'PPN', 'kode_unik' => 'Kode unik',
        'nominal_diskon' => 'Nominal potongan', 'kode_diskon' => 'Kode diskon',
        'total_pembayaran' => 'Total bayar',
        'tanggal_reschedule' => 'Tanggal jadwal ulang', 'group_wa' => 'Tautan grup WhatsApp',
        'note' => 'Catatan panitia',
        'tanggal_pemesanan' => 'Tanggal pemesanan',
        'sesi' => 'Sesi', 'jam_sesi' => 'Jam sesi',
        'waktu_mulai' => 'Mulai', 'waktu_selesai' => 'Selesai', 'lokasi' => 'Lokasi',
        'biaya' => 'Biaya', 'kode_unik_pembayaran' => 'Kode unik', 'subtotal_pembayaran' => 'Subtotal',
        'sesi_kedua' => 'Sesi ke-2', 'waktu_mulai_kedua' => 'Mulai', 'waktu_selesai_kedua' => 'Selesai',
        'lokasi_kedua' => 'Lokasi', 'biaya_kedua' => 'Biaya',
        'kode_unik_pembayaran_kedua' => 'Kode unik', 'subtotal_pembayaran_kedua' => 'Subtotal',
        'sesi_ketiga' => 'Sesi ke-3', 'waktu_mulai_ketiga' => 'Mulai', 'waktu_selesai_ketiga' => 'Selesai',
        'lokasi_ketiga' => 'Lokasi', 'biaya_ketiga' => 'Biaya',
        'kode_unik_pembayaran_ketiga' => 'Kode unik', 'subtotal_pembayaran_ketiga' => 'Subtotal',
        'total_keseluruhan_pembayaran' => 'Total keseluruhan',
        'kendala' => 'Kendala', 'desc_kendala' => 'Keterangan kendala',
    ];

    /*
     * Medan dikelompokkan supaya borangnya terbaca, bukan jadi tiga puluh
     * isian berderet. Yang tidak dipunyai layanannya dilewati sendiri.
     */
    $kelompok = [
        'Data diri' => ['nama', 'nama_pemesan', 'email', 'email_pemesan', 'telp', 'telp_pemesan', 'affiliasi', 'afiliasi_pemesan'],
        'Angkatan & jumlah' => ['kategori_id', 'jumlah_pendaftar'],
        'Pembayaran' => ['ppn', 'kode_unik', 'kode_diskon', 'nominal_diskon', 'total_pembayaran'],
        'Sesi pertama' => ['tanggal_pemesanan', 'sesi', 'jam_sesi', 'waktu_mulai', 'waktu_selesai', 'lokasi', 'biaya', 'kode_unik_pembayaran', 'subtotal_pembayaran'],
        'Sesi kedua' => ['sesi_kedua', 'waktu_mulai_kedua', 'waktu_selesai_kedua', 'lokasi_kedua', 'biaya_kedua', 'kode_unik_pembayaran_kedua', 'subtotal_pembayaran_kedua'],
        'Sesi ketiga' => ['sesi_ketiga', 'waktu_mulai_ketiga', 'waktu_selesai_ketiga', 'lokasi_ketiga', 'biaya_ketiga', 'kode_unik_pembayaran_ketiga', 'subtotal_pembayaran_ketiga'],
        'Jadwal & lain-lain' => ['tanggal_reschedule', 'group_wa', 'total_keseluruhan_pembayaran', 'kendala', 'desc_kendala', 'note'],
    ];

    $medanPenuh = ['group_wa', 'note', 'desc_kendala', 'lokasi', 'lokasi_kedua', 'lokasi_ketiga'];
    $medanUang = ['ppn', 'kode_unik', 'nominal_diskon', 'total_pembayaran', 'biaya', 'kode_unik_pembayaran', 'subtotal_pembayaran', 'biaya_kedua', 'kode_unik_pembayaran_kedua', 'subtotal_pembayaran_kedua', 'biaya_ketiga', 'kode_unik_pembayaran_ketiga', 'subtotal_pembayaran_ketiga', 'total_keseluruhan_pembayaran'];
@endphp
<div class="main-content mis-badan">
    <section class="section">

        <div class="mis-kepala">
            <span class="mis-medali {{ $info['warna'] }}" aria-hidden="true">
                <i class="fas {{ $info['ikon'] }}"></i>
            </span>
            <div class="mis-kepala-teks">
                <h1 class="mis-judul">Rincian Pendaftaran</h1>
                <p class="mis-sub">{{ $info['nama'] }} &middot; {{ $nomor }}</p>
            </div>
            <div class="mis-kepala-aksi">
                <a class="mis-tombol mis-tombol-halus" href="{{ route('account.pendaftaran-layanan.index') }}">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali ke daftar
                </a>
            </div>
        </div>

        @include('account.pendaftaran_layanan.partials.pesan')

        <div class="rin-kisi">

            {{-- ------------------------------------- kartu identitas --}}
            <div class="mis-kartu">
                <div class="rin-identitas-kepala">
                    <span class="mis-medali kecil {{ $info['warna'] }}" aria-hidden="true">
                        <i class="fas {{ $info['ikon'] }}"></i>
                    </span>
                    <div style="min-width: 0;">
                        <p class="rin-nama">{{ $namaOrang }}</p>
                        <p class="rin-layanan">{{ $info['nama'] }}</p>
                    </div>
                </div>

                <div class="rin-daftar">
                    <div>
                        <p class="rin-label">Keadaan</p>
                        <p class="rin-nilai">
                            <span class="mis-pil mis-pil-{{ $rupa['warna'] }}">
                                <i class="fas {{ $rupa['ikon'] }}" aria-hidden="true"></i> {{ $rupa['label'] }}
                            </span>
                        </p>
                    </div>

                    <div>
                        <p class="rin-label">Nomor pendaftaran</p>
                        <p class="rin-nilai">{{ $nomor }}</p>
                    </div>

                    <div>
                        <p class="rin-label">Email</p>
                        <p class="rin-nilai">
                            @if ($emailOrang)
                                <a href="mailto:{{ $emailOrang }}">
                                    <i class="fas fa-envelope mis-ikon-biru" aria-hidden="true"></i> {{ $emailOrang }}
                                </a>
                            @else
                                <span class="rin-kosong">belum diisi</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="rin-label">Nomor WhatsApp</p>
                        <p class="rin-nilai">
                            @if ($wa)
                                <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener">
                                    <i class="fab fa-whatsapp mis-ikon-hijau" aria-hidden="true"></i> {{ $telpOrang }}
                                </a>
                            @elseif ($telpOrang)
                                {{-- Ditandai, bukan dijadikan tautan yang menuntun ke
                                     halaman galat WhatsApp. --}}
                                <span title="Nomor ini tidak bisa dipakai menghubungi lewat WhatsApp.">
                                    <i class="fas fa-phone-slash mis-ikon-kuning" aria-hidden="true"></i> {{ $telpOrang }}
                                </span>
                            @else
                                <span class="rin-kosong">belum diisi</span>
                            @endif
                        </p>
                    </div>

                    @if ($afiliasiOrang)
                        <div>
                            <p class="rin-label">Afiliasi</p>
                            <p class="rin-nilai">{{ $afiliasiOrang }}</p>
                        </div>
                    @endif

                    <div>
                        <p class="rin-label">Sesi / angkatan</p>
                        <p class="rin-nilai">{{ $sesiBaris ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="rin-label">Mendaftar</p>
                        <p class="rin-nilai">
                            {{ $waktuBaris ? $waktuBaris->translatedFormat('d F Y, H:i') . ' WIB' : '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="rin-label">Bukti bayar</p>
                        @if (! $bukti['nilai'])
                            <p class="rin-nilai rin-kosong">
                                @if ($layanan === 'webinar_eksklusif')
                                    Layanan ini tidak memakai unggahan bukti; pembayarannya
                                    dicocokkan lewat kode uniknya.
                                @else
                                    Belum diunggah.
                                @endif
                            </p>
                        @elseif ($bukti['ada'])
                            <p class="rin-nilai">
                                <a href="{{ $bukti['url'] }}" target="_blank" rel="noopener">
                                    <i class="fas fa-external-link-alt mis-ikon-hijau" aria-hidden="true"></i>
                                    Buka ukuran penuh
                                </a>
                            </p>
                            <img class="rin-bukti-gambar" src="{{ $bukti['url'] }}"
                                alt="Bukti bayar {{ $namaOrang }}" loading="lazy">
                        @else
                            {{-- Dibedakan dari "belum diunggah": terukur 72 dari 183
                                 nilai bukti menunjuk berkas yang sudah tidak ada di
                                 cakram, dan keduanya menuntut tindakan berbeda. --}}
                            <p class="rin-nilai" style="color: #92400e;">
                                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                                Kolomnya terisi tetapi berkasnya sudah tidak ada di server,
                                jadi buktinya tidak bisa diperiksa lagi.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- -------------------------------------- kolom kanan --}}
            <div style="display: grid; gap: var(--mis-jarak); min-width: 0;">

                {{-- ------------------------------------- ubah status --}}
                <div class="mis-kartu">
                    <div class="mis-kartu-kepala">
                        <h2 class="mis-kartu-judul">Status pembayaran</h2>
                        <p class="mis-kartu-sub">
                            Statusnya menentukan apa yang dilihat pendaftar di halaman buktinya.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('account.pendaftaran-layanan.status', [$layanan, $pendaftaran->getKey()]) }}">
                        @csrf
                        <div class="rin-status-baris">
                            <div class="mis-isian">
                                <label class="mis-label" for="rin-status">Pindahkan status ke</label>
                                <select class="form-control-modern" id="rin-status" name="status" required>
                                    @foreach ($pilihanStatus as $nilai => $tulisan)
                                        <option value="{{ $nilai }}" @selected($pendaftaran->status === $nilai)>{{ $tulisan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="mis-tombol mis-tombol-ungu">
                                <i class="fas fa-check" aria-hidden="true"></i> Simpan status
                            </button>
                        </div>

                        @php
                            // Status mana saja yang MENGIRIM EMAIL ke pendaftarnya.
                            $statusBersurat = [];

                            foreach (array_keys($pilihanStatus) as $nilai) {
                                if (\App\Support\PendaftaranSemuaLayanan::suratUntuk($layanan, $nilai) !== null) {
                                    $statusBersurat[] = $pilihanStatus[$nilai];
                                }
                            }
                        @endphp

                        @if ($statusBersurat !== [])
                            {{-- Disebut di muka, bukan sesudah terkirim: mengubah status
                                 ke salah satu nilai ini MENGIRIM EMAIL ke pendaftarnya,
                                 dan email tidak bisa ditarik kembali. --}}
                            <p class="rin-peringatan">
                                <i class="fas fa-envelope" aria-hidden="true"></i>
                                Memilih <strong>{{ implode('</strong> atau <strong>', $statusBersurat) }}</strong>
                                akan mengirim email pemberitahuan ke pendaftarnya. Email tidak bisa ditarik kembali.
                            </p>
                        @endif

                        @if ($layanan === 'webinar_eksklusif')
                            <p class="rin-peringatan">
                                <i class="fas fa-chair" aria-hidden="true"></i>
                                Khusus layanan ini, memindahkan status ke <strong>Kedaluwarsa</strong>
                                atau <strong>Dibatalkan</strong> mengembalikan kursinya ke kuota angkatan,
                                dan mengembalikannya ke status aktif mengambil kursinya lagi.
                            </p>
                        @endif
                    </form>
                </div>

                {{-- -------------------------------------- ubah data --}}
                <div class="mis-kartu">
                    <div class="mis-kartu-kepala">
                        <h2 class="mis-kartu-judul">Data pendaftaran</h2>
                        <p class="mis-kartu-sub">
                            Untuk membetulkan salah ketik dan memindahkan angkatan. Statusnya
                            diubah di kartu di atas.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('account.pendaftaran-layanan.ubah', [$layanan, $pendaftaran->getKey()]) }}">
                        @csrf
                        @method('PUT')

                        @foreach ($kelompok as $judulKelompok => $daftarMedan)
                            @php
                                $adaDiKelompok = array_values(array_filter(
                                    $daftarMedan,
                                    fn ($k) => isset($medan[$k], $label[$k])
                                ));
                            @endphp

                            @if ($adaDiKelompok !== [])
                                <div class="rin-bagian">
                                    <p class="rin-bagian-judul">{{ $judulKelompok }}</p>
                                    <div class="rin-isian-kisi">
                                        @foreach ($adaDiKelompok as $kolom)
                                            @include('account.pendaftaran_layanan.partials.isian', [
                                                'kolom' => $kolom,
                                                'jenis' => $medan[$kolom],
                                                'tulisan' => $label[$kolom],
                                                'nilai' => old($kolom, $pendaftaran->{$kolom}),
                                                'penuh' => in_array($kolom, $medanPenuh, true),
                                                'uang' => in_array($kolom, $medanUang, true),
                                                'angkatan' => $angkatan,
                                            ])
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        <div class="rin-kaki">
                            <button type="submit" class="mis-tombol mis-tombol-ungu">
                                <i class="fas fa-save" aria-hidden="true"></i> Simpan perubahan
                            </button>
                            <a class="mis-tombol mis-tombol-halus"
                                href="{{ route('account.pendaftaran-layanan.rincian', [$layanan, $pendaftaran->getKey()]) }}">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>

                {{-- --------------------------------- peserta rombongan --}}
                @if ($layanan === 'webinar_eksklusif' && (int) $pendaftaran->jumlah_pendaftar > 1)
                    <div class="mis-kartu">
                        <div class="mis-kartu-kepala">
                            <h2 class="mis-kartu-judul">Peserta rombongan</h2>
                            <p class="mis-kartu-sub">
                                Inilah nama yang dipakai menerbitkan sertifikat dan memasukkan
                                orang ke grup.
                            </p>
                        </div>

                        {{-- Dibawa dari layar pendaftar webinar yang dibuang:
                             tanpa daftar ini, nama peserta kedua dan seterusnya
                             tidak bisa dilihat di mana pun lagi. --}}
                        <ol class="rin-daftar" style="padding-left: 20px; gap: 7px;">
                            @foreach ($pendaftaran->semuaPeserta() as $orangKe)
                                <li class="rin-nilai" style="margin: 0;">
                                    {{ $orangKe['nama'] ?: 'Tanpa nama' }}
                                    @if ($orangKe['utama'])
                                        <span class="mis-pil mis-pil-ungu">pendaftar</span>
                                    @endif
                                    @if (! empty($orangKe['email']))
                                        <span class="rin-kosong">&middot; {{ $orangKe['email'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>

                        @php
                            $terdaftar = count($pendaftaran->semuaPeserta());
                            $dibayar = (int) $pendaftaran->jumlah_pendaftar;
                        @endphp

                        @if ($terdaftar !== $dibayar)
                            {{-- Selisihnya disebut, bukan dibiarkan: pendaftaran
                                 yang dibayar untuk lima orang tetapi hanya memuat
                                 tiga nama berarti dua sertifikat tidak bisa
                                 diterbitkan, dan itu baru ketahuan di hari acara. --}}
                            <p class="rin-peringatan">
                                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                                Dibayar untuk <strong>{{ $dibayar }}</strong> orang, tetapi baru
                                <strong>{{ $terdaftar }}</strong> nama yang tercatat. Tanyakan
                                sisanya ke pendaftarnya.
                            </p>
                        @endif
                    </div>
                @endif

                {{-- ---------------------------------------- hapus --}}
                <div class="mis-kartu">
                    <div class="mis-kartu-kepala">
                        <h2 class="mis-kartu-judul">Hapus pendaftaran</h2>
                        <p class="mis-kartu-sub">
                            Tidak bisa diurungkan, dan belum ada tong sampah.
                        </p>
                    </div>

                    @if ($bolehMenghapus)
                        <p class="rin-peringatan rin-bahaya">
                            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                            Barisnya hilang permanen beserta berkas bukti bayarnya
                            @if (\App\Support\PendaftaranSemuaLayanan::berangkatan($layanan))
                                , dan kursinya dikembalikan ke kuota angkatan
                            @endif
                            @if ($layanan === 'clinik_scopus')
                                , beserta testimoni yang menempel padanya
                            @endif
                            . Kalau yang Anda maksud membatalkan, pindahkan statusnya saja —
                            datanya tetap bisa dilihat.
                        </p>

                        <form method="POST" id="rin-borang-hapus"
                            action="{{ route('account.pendaftaran-layanan.hapus', [$layanan, $pendaftaran->getKey()]) }}">
                            @csrf
                            @method('DELETE')
                            <div class="rin-kaki" style="margin-top: 0; border-top: 0; padding-top: 0;">
                                <button type="button" class="mis-tombol mis-tombol-hapus" id="rin-tombol-hapus"
                                    data-nama="{{ $namaOrang }}" data-nomor="{{ $nomor }}">
                                    <i class="fas fa-trash" aria-hidden="true"></i> Hapus pendaftaran ini
                                </button>
                            </div>
                        </form>
                    @else
                        {{-- Tombolnya tidak disuguhkan, DAN alasannya ditulis di tempat
                             tombol itu seharusnya berada. Layar tidak boleh menyuguhkan
                             sesuatu yang kirimannya akan ditolak. --}}
                        <p class="rin-peringatan">
                            <i class="fas fa-lock" aria-hidden="true"></i>
                            Hanya administrator yang boleh menghapus pendaftaran. Kalau yang
                            Anda maksud membatalkan, pindahkan statusnya di kartu di atas.
                        </p>
                    @endif
                </div>

            </div>
        </div>

    </section>
</div>
@endsection

@push('scripts')
<script>
    /*
     * Konfirmasi hapus lewat misKonfirmasi(), bukan Swal.fire langsung —
     * pembungkus bersama itu yang menjaga rupanya seragam di semua layar.
     *
     * Nama orangnya lewat `sorot`, BUKAN dirangkai ke `pesan`: ia disisipkan
     * sebagai teks, jadi tanda < di dalam nama tidak pernah tertafsir markah.
     *
     * Dialognya bukan pengaman. Peladennya tetap memeriksa peran penghapusnya,
     * jadi melewatinya lewat konsol tidak membuat apa pun bisa terhapus.
     */
    document.addEventListener('DOMContentLoaded', function () {
        var tombol = document.getElementById('rin-tombol-hapus');

        if (!tombol) {
            return;
        }

        tombol.addEventListener('click', function () {
            window.misKonfirmasi({
                judul: 'Hapus pendaftaran ini?',
                pesan: 'Barisnya hilang permanen beserta bukti bayarnya. Tidak bisa diurungkan.',
                sorot: tombol.dataset.nomor + ' — ' + tombol.dataset.nama,
                tombol: 'Ya, hapus',
                jenis: 'bahaya',
            }).then(function (setuju) {
                if (setuju) {
                    document.getElementById('rin-borang-hapus').submit();
                }
            });
        });
    });
</script>
@endpush
