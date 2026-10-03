@extends('public.layout.header')

@section('title')
Status Pendaftaran {{ $pendaftaran->id_transaksi }} | Rumah Scopus
@stop

@section('konten')
{{--
    Halaman status satu pendaftaran.

    Dipakai dua keadaan sekaligus: tempat mendarat sesudah membayar lewat DOKU,
    dan halaman cara-bayar kalau pembayaran daring sedang tidak bisa dipakai.
    Satu halaman, bukan dua, karena yang ditanyakan orang di keduanya sama —
    "pendaftaran saya sudah masuk belum, dan sekarang saya harus apa".
--}}
<section class="sta-latar">
    <div class="container sta-wadah">

        @if (session('error'))
            <div class="sta-galat" role="alert">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if (session('sukses'))
            <div class="sta-sukses" role="status">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
                <span>{{ session('sukses') }}</span>
            </div>
        @endif

        @php($lunas = $pendaftaran->lunas)
        @php($habis = $pendaftaran->sudah_kedaluwarsa || $pendaftaran->status === 'expired')
        @php($batal = $pendaftaran->status === 'cancel')

        <div class="sta-kartu">

            {{-- Nama kelas dan ikonnya DIRAKIT UTUH di PHP, bukan ditempel
                 sebagian di dalam class="fa-{{ ... }}".

                 Dirakit sebagian, nama ikon lengkapnya tidak pernah muncul
                 sebagai teks di berkas ini — jadi uji yang memindai nama ikon
                 tidak bisa melihatnya, dan nama Font Awesome 6 yang keliru
                 lolos begitu saja. Halaman publik memakai Font Awesome 5, dan
                 nama FA6 tidak merender apa pun TANPA galat sama sekali. --}}
            @php($rupa = $lunas
                ? ['hijau', 'fa-check-circle']
                : (($habis || $batal) ? ['merah', 'fa-times-circle'] : ['kuning', 'fa-hourglass-half']))
            <span class="sta-ubin sta-ubin-{{ $rupa[0] }}" aria-hidden="true">
                <i class="fas {{ $rupa[1] }}"></i>
            </span>

            <h1 class="sta-judul">
                @if ($lunas)
                    Pendaftaran Anda sudah lunas
                @elseif ($batal)
                    Pendaftaran ini dibatalkan
                @elseif ($habis)
                    Batas waktu pembayarannya sudah lewat
                @else
                    Pendaftaran Anda sudah masuk
                @endif
            </h1>

            <p class="sta-sub">
                @if ($lunas)
                    Sampai jumpa di kelas. Tautan masuk dikirim ke
                    <strong>{{ $pendaftaran->email }}</strong> paling lambat sehari sebelum acara.
                @elseif ($batal || $habis)
                    Kursinya sudah dilepas kembali. Silakan daftar ulang kalau masih ingin ikut.
                @else
                    Tinggal satu langkah: selesaikan pembayarannya.
                @endif
            </p>

            <dl class="sta-rincian">
                <div>
                    <dt>Nomor pendaftaran</dt>
                    {{-- Dipilih <code> supaya mudah disalin dan tidak tertukar
                         huruf saat dibacakan lewat telepon ke panitia. --}}
                    <dd><code>{{ $pendaftaran->id_transaksi }}</code></dd>
                </div>

                @if ($sesi)
                    <div>
                        <dt>Sesi</dt>
                        <dd>{{ $sesi->nama }}</dd>
                    </div>
                    <div>
                        <dt>Tanggal</dt>
                        <dd>
                            {{ \App\Support\RentangTanggal::tulis(
                                $sesi->mulai ? \Carbon\Carbon::parse($sesi->mulai) : null,
                                $sesi->selesai ? \Carbon\Carbon::parse($sesi->selesai) : null
                            ) ?: 'Menyusul' }}
                            @if ($sesi->jam) &middot; {{ $sesi->jam }} @endif
                        </dd>
                    </div>
                @endif

                <div>
                    <dt>Atas nama</dt>
                    <dd>{{ $pendaftaran->nama }}</dd>
                </div>
                <div>
                    <dt>Jumlah peserta</dt>
                    <dd>{{ $pendaftaran->jumlah_pendaftar }} orang</dd>
                </div>
                @if ((int) $pendaftaran->nominal_diskon > 0)
                    <div>
                        <dt>Potongan</dt>
                        <dd class="sta-potongan">
                            &minus; Rp {{ number_format((int) $pendaftaran->nominal_diskon, 0, ',', '.') }}
                            <small>{{ $pendaftaran->kode_diskon }}</small>
                        </dd>
                    </div>
                @endif
                @if ((int) $pendaftaran->kode_unik > 0)
                    <div>
                        <dt>Kode unik</dt>
                        <dd class="sta-kode-unik">
                            + Rp {{ number_format((int) $pendaftaran->kode_unik, 0, ',', '.') }}
                            <small>penanda pembayaran Anda</small>
                        </dd>
                    </div>
                @endif
                <div>
                    <dt>Total</dt>
                    <dd class="sta-total">Rp {{ number_format((int) $pendaftaran->total_pembayaran, 0, ',', '.') }}</dd>
                </div>
            </dl>

            @if (! $lunas && ! $batal && ! $habis)
                @if ($pendaftaran->sisa_waktu)
                    {{--
                        Kalimatnya DIBUNGKUS <span>, dan itu bukan hiasan.

                        .sta-waktu memakai display:flex, dan pada flex setiap
                        unsur anak jadi item tersendiri — <strong> di tengah
                        kalimat terlempar jadi kolomnya sendiri, sehingga
                        terbaca "Selesaikan dalam | 23 jam 59 menit lagi |
                        , setelah itu kursinya dilepas" dalam tiga kolom
                        terpisah. Dengan satu pembungkus, flexnya tinggal dua
                        item: ikon dan kalimat.
                    --}}
                    <p class="sta-waktu">
                        <i class="fas fa-stopwatch" aria-hidden="true"></i>
                        <span>
                            Selesaikan dalam <strong>{{ $pendaftaran->sisa_waktu }}</strong>,
                            setelah itu kursinya dilepas untuk orang lain.
                        </span>
                    </p>
                @endif

                @if ($pendaftaran->cara_bayar === 'transfer')
                    {{-- Cara bayar manual. Nomor rekeningnya SENGAJA diketik di
                         sini dan bukan diambil dari basis data: tidak ada layar
                         yang mengaturnya, dan mengambilnya dari kolom yang bisa
                         kosong berarti halaman ini bisa menampilkan rekening
                         kosong kepada orang yang hendak membayar. --}}
                    <div class="sta-bayar">
                        <p class="sta-bayar-judul">Cara membayar</p>
                        <ol>
                            <li>
                                Transfer <strong>tepat sampai angka terakhir</strong>
                                Rp {{ number_format((int) $pendaftaran->total_pembayaran, 0, ',', '.') }} ke:
                                @if ((int) $pendaftaran->kode_unik > 0)
                                    {{-- Alasannya disebut. Tanpa itu orang mengira
                                         angka ganjilnya salah hitung, lalu
                                         membulatkannya — dan transfernya tidak
                                         bisa dicocokkan lagi. --}}
                                    <span class="sta-catatan-unik">
                                        Angka terakhirnya kode unik Anda; kalau dibulatkan,
                                        pembayarannya tidak bisa kami cocokkan.
                                    </span>
                                @endif
                            </li>
                        </ol>
                        <div class="sta-rekening">
                            <span>BRI</span>
                            <strong>2164 0100 0467 563</strong>
                            <small>a.n. Rumah Scopus Akademi</small>
                        </div>
                        <ol start="2">
                            <li>Kirim bukti transfer beserta nomor
                                <code>{{ $pendaftaran->id_transaksi }}</code> ke panitia lewat WhatsApp.</li>
                            {{-- Yang BENAR-BENAR terjadi. Sebelumnya tertulis
                                 "tautan masuk dikirim ke email Anda", padahal
                                 tidak ada mekanismenya di sistem — panitia
                                 membagikannya lewat grup. --}}
                            <li>Panitia mengonfirmasi, lalu Anda dimasukkan ke grup peserta
                                di WhatsApp — tautan masuk sesinya dibagikan di sana.</li>
                        </ol>

                        <a class="sta-wa"
                            href="https://wa.me/{{ config('panitia.whatsapp') }}?text={{ rawurlencode('Halo, saya sudah mendaftar Webinar Eksklusif dengan nomor ' . $pendaftaran->id_transaksi . ' atas nama ' . $pendaftaran->nama . '. Berikut bukti transfernya.') }}"
                            target="_blank" rel="noopener">
                            <i class="fab fa-whatsapp" aria-hidden="true"></i>
                            Kirim bukti transfer
                        </a>
                    </div>
                @endif
            @endif

            {{--
                TAWARAN BUAT AKUN — opt-in, bukan dibuatkan diam-diam.

                Akun tidak dibuat otomatis saat mendaftar dengan sengaja:
                akun hasil buatan sistem tidak punya sandi yang dipilih
                orangnya, dan email yang salah ketik akan menciptakan akun
                yang tidak bisa dibuka siapa pun — sekaligus menghalangi
                pendaftaran akun sungguhannya nanti, sebab emailnya unik.

                Jadi ditawarkan di sini, sesudah pendaftarannya aman, dengan
                email dan namanya sudah dibawa ke borang pendaftaran akun.
                Tidak ditampilkan kepada yang sudah masuk.
            --}}
            @guest
                @if (! $batal && ! $habis)
                    <div class="sta-tawar-akun">
                        <p class="sta-tawar-judul">Mau lebih mudah lain kali?</p>
                        <p class="sta-tawar-isi">
                            Dengan akun, riwayat pendaftaran Anda tersimpan dan borangnya
                            terisi sendiri di sesi berikutnya.
                        </p>
                        <a href="{{ route('register', ['email' => $pendaftaran->email, 'nama' => $pendaftaran->nama]) }}">
                            <i class="fas fa-user-plus" aria-hidden="true"></i>
                            Buat akun pakai email ini
                        </a>
                    </div>
                @endif
            @endguest

            {{--
                Kirim ulang bukti pendaftaran.

                Satu-satunya jalan kembali ke halaman ini adalah tautan
                ber-UUID di email. Kalau emailnya terhapus atau masuk folder
                sampah, orangnya kehilangan nomor pendaftaran dan cara
                bayarnya sekaligus — dan yang menanggung panitia lewat
                WhatsApp.

                Tidak ditampilkan untuk yang sudah batal atau kedaluwarsa:
                di sana tidak ada lagi yang perlu disimpan.
            --}}
            @if (! $batal && ! $habis)
                <form method="POST" action="{{ route('public.webinareksklusif.kirimulang', $pendaftaran->getKey()) }}"
                    class="sta-kirim-ulang">
                    @csrf
                    <button type="submit">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i>
                        Kirim ulang bukti pendaftaran ke email saya
                    </button>
                </form>
            @endif

            {{-- Daftar peserta ditampilkan supaya pendaftar bisa memeriksa
                 ejaan namanya sebelum sertifikat diterbitkan. Salah eja yang
                 baru ketahuan saat sertifikatnya jadi adalah pekerjaan ulang
                 yang bisa dicegah di sini. --}}
            @php($semuaPeserta = $pendaftaran->semuaPeserta())

            @if (count($semuaPeserta) > 1)
                <div class="sta-peserta">
                    <p class="sta-peserta-judul">Nama peserta ({{ count($semuaPeserta) }} orang)</p>
                    <ol>
                        @foreach ($semuaPeserta as $orang)
                            <li>{{ $orang['nama'] }}@if ($orang['utama'])<span>pendaftar</span>@endif</li>
                        @endforeach
                    </ol>
                    <p class="sta-peserta-catatan">
                        Sertifikat diterbitkan atas nama ini. Kalau ada yang salah eja,
                        kabari panitia sebelum hari pelaksanaan.
                    </p>
                </div>
            @endif

            <div class="sta-aksi">
                @if ($batal || $habis)
                    <a href="{{ route('public.webinareksklusif.index') }}" class="sta-tombol">
                        Lihat sesi yang dibuka
                    </a>
                @else
                    <a href="{{ route('public.webinareksklusif.index') }}" class="sta-tautan">
                        Lihat sesi lainnya
                    </a>
                @endif
            </div>

            <p class="sta-simpan">
                Simpan halaman ini. Alamatnya bisa dibuka kapan saja untuk melihat status terbaru.
            </p>
        </div>
    </div>
</section>

<style>
    .sta-latar {
        --navy: #0f2b5b;
        --tinta: #1e293b;
        --tinta-2: #64748b;
        --garis: #e2e8f0;

        background: #f8fafc;
        background-image:
            radial-gradient(circle at 85% -5%, rgba(255, 106, 0, .12), transparent 45%),
            radial-gradient(circle at -10% 20%, rgba(15, 43, 91, .1), transparent 45%);
        padding: 110px 0 90px;
        min-height: 100vh;
        font-family: 'Poppins', 'Inter', system-ui, sans-serif;
        color: var(--tinta);
    }

    .sta-wadah { max-width: 640px; }

    .sta-galat,
    .sta-sukses {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 22px;
        padding: 14px 18px;
        border-radius: 14px;
        font-size: .9rem;
    }

    .sta-galat {
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #991b1b;
    }

    .sta-sukses {
        border: 1px solid #a7f3d0;
        background: #ecfdf5;
        color: #065f46;
    }

    .sta-galat > .fas,
    .sta-sukses > .fas { margin: 0 !important; margin-top: 2px !important; }

    /* ---------------------------------------------- tawaran buat akun */

    .sta-tawar-akun {
        margin: 0 0 16px;
        padding: 16px 18px;
        border-radius: 14px;
        border: 1px solid #c7d2fe;
        background: #eef2ff;
        text-align: center;
    }

    .sta-tawar-judul {
        margin: 0 0 5px;
        font-size: .9rem;
        font-weight: 800;
        color: #3730a3;
    }

    .sta-tawar-isi {
        margin: 0 0 12px;
        font-size: .82rem;
        line-height: 1.6;
        color: #4338ca;
    }

    .sta-tawar-akun a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 42px;
        padding: 0 18px;
        border-radius: 11px;
        background: #4f46e5;
        color: #fff;
        font-size: .85rem;
        font-weight: 700;
        text-decoration: none;
        transition: background .2s ease, transform .12s ease;
    }

    .sta-tawar-akun a:hover { background: #4338ca; }
    .sta-tawar-akun a:active { transform: scale(.97); }
    .sta-tawar-akun a > .fas { margin: 0 !important; }

    @media (prefers-reduced-motion: reduce) {
        .sta-tawar-akun a { transition: none !important; }
        .sta-tawar-akun a:active { transform: none !important; }
    }

    /* ------------------------------------------------- daftar peserta */

    .sta-peserta {
        margin: 0 0 18px;
        padding: 16px 18px;
        border-radius: 14px;
        border: 1px solid var(--garis, #e2e8f0);
        background: #f8fafc;
    }

    .sta-peserta-judul {
        margin: 0 0 10px;
        font-size: .85rem;
        font-weight: 800;
        color: var(--navy, #0f2b5b);
    }

    .sta-peserta ol { margin: 0; padding-left: 20px; }

    .sta-peserta li {
        padding: 4px 0;
        font-size: .9rem;
        color: #334155;
    }

    .sta-peserta li span {
        margin-left: 7px;
        padding: 1px 8px;
        border-radius: 999px;
        background: #e0e7ff;
        font-size: .68rem;
        font-weight: 700;
        color: #3730a3;
    }

    .sta-peserta-catatan {
        margin: 10px 0 0;
        font-size: .76rem;
        line-height: 1.55;
        color: #64748b;
    }

    .sta-kode-unik { color: var(--jingga, #ff6a00) !important; }
    .sta-kode-unik small { display: block; font-size: .7rem; color: #94a3b8; }

    .sta-catatan-unik {
        display: block;
        margin-top: 5px;
        padding: 8px 10px;
        border-radius: 9px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        font-size: .78rem;
        line-height: 1.55;
        color: #9a3412;
    }

    .sta-potongan { color: #0f9b74 !important; }
    .sta-potongan small { display: block; font-size: .7rem; color: #94a3b8; }

    /* ---------------------------------------- kirim ulang bukti pendaftaran */

    .sta-kirim-ulang { margin: 0 0 14px; text-align: center; }

    .sta-kirim-ulang button {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 11px 18px;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        background: #fff;
        font-family: inherit;
        font-size: .85rem;
        font-weight: 700;
        color: #475569;
        cursor: pointer;
        transition: border-color .2s ease, color .2s ease, background .2s ease, transform .12s ease;
    }

    .sta-kirim-ulang button:hover {
        border-color: var(--jingga, #ff6a00);
        color: var(--jingga, #ff6a00);
        background: #fff7ed;
    }

    .sta-kirim-ulang button:active { transform: scale(.97); }

    .sta-kirim-ulang button > .fas { margin: 0 !important; }

    .sta-kirim-ulang button:focus-visible {
        outline: 3px solid rgba(255, 106, 0, .45);
        outline-offset: 3px;
    }

    @media (prefers-reduced-motion: reduce) {
        .sta-kirim-ulang button { transition: none !important; }
        .sta-kirim-ulang button:active { transform: none !important; }
    }

    .sta-kartu {
        padding: 34px 28px;
        border-radius: 22px;
        border: 1px solid var(--garis);
        background: #fff;
        box-shadow: 0 24px 60px -34px rgba(15, 43, 91, .5);
        text-align: center;
    }

    .sta-ubin {
        display: grid;
        place-items: center;
        width: 68px;
        height: 68px;
        margin: 0 auto 18px;
        border-radius: 20px;
        font-size: 1.6rem;
        color: #fff;
    }

    .sta-ubin > .fas { margin: 0 !important; }

    .sta-ubin-hijau { background: linear-gradient(135deg, #10b981, #059669); }
    .sta-ubin-kuning { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .sta-ubin-merah { background: linear-gradient(135deg, #ef4444, #b91c1c); }

    .sta-judul {
        margin: 0 0 10px;
        font-size: clamp(1.35rem, 3.5vw, 1.75rem);
        font-weight: 800;
        line-height: 1.3;
        color: var(--navy);
    }

    .sta-sub { margin: 0 0 26px; font-size: .95rem; line-height: 1.7; color: var(--tinta-2); }

    .sta-rincian {
        margin: 0 0 22px;
        padding: 18px 20px;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid var(--garis);
        text-align: left;
    }

    .sta-rincian > div {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        justify-content: space-between;
        gap: 4px 16px;
        padding: 8px 0;
        border-bottom: 1px solid #eef2f7;
    }

    .sta-rincian > div:last-child { border-bottom: 0; }

    .sta-rincian dt { margin: 0; font-size: .8rem; font-weight: 600; color: var(--tinta-2); }

    /* min-width: 0 supaya nilai panjang boleh patah alih-alih meluber. */
    .sta-rincian dd { margin: 0; min-width: 0; font-size: .92rem; font-weight: 600; text-align: right; }

    .sta-rincian code {
        padding: 3px 8px;
        border-radius: 7px;
        background: #e0e7ff;
        color: #3730a3;
        font-size: .86rem;
        font-weight: 700;
    }

    .sta-total { font-size: 1.1rem !important; color: var(--navy); }

    .sta-waktu {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        margin: 0 0 20px;
        padding: 12px 16px;
        border-radius: 12px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
        font-size: .86rem;
    }

    .sta-waktu > .fas { margin: 0 !important; flex: 0 0 auto; }
    .sta-waktu > span { line-height: 1.55; }

    .sta-bayar { text-align: left; }

    .sta-bayar-judul { margin: 0 0 10px; font-size: .95rem; font-weight: 800; color: var(--navy); }

    .sta-bayar ol { margin: 0; padding-left: 20px; font-size: .9rem; line-height: 1.8; color: var(--tinta); }

    .sta-bayar code {
        padding: 2px 7px;
        border-radius: 6px;
        background: #e0e7ff;
        color: #3730a3;
        font-weight: 700;
    }

    .sta-rekening {
        margin: 12px 0;
        padding: 16px 18px;
        border-radius: 14px;
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: #fff;
        text-align: center;
    }

    .sta-rekening span { display: block; font-size: .74rem; letter-spacing: .1em; opacity: .85; }
    .sta-rekening strong { display: block; margin: 4px 0; font-size: 1.3rem; font-weight: 800; letter-spacing: .04em; }
    .sta-rekening small { font-size: .78rem; opacity: .85; }

    .sta-wa {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 50px;
        margin-top: 16px;
        border-radius: 13px;
        background: #25d366;
        color: #fff;
        font-size: .95rem;
        font-weight: 700;
        text-decoration: none;
    }

    .sta-wa:hover { background: #1ebe5a; color: #fff; text-decoration: none; }
    .sta-wa > .fab { margin: 0 !important; font-size: 1.2rem; }

    .sta-aksi { margin-top: 24px; }

    .sta-tombol {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 50px;
        padding: 0 26px;
        border-radius: 13px;
        background: linear-gradient(135deg, #ff8c00, #e65c00);
        color: #fff;
        font-weight: 800;
        text-decoration: none;
    }

    .sta-tombol:hover { color: #fff; text-decoration: none; }

    .sta-tautan { font-size: .9rem; font-weight: 700; color: var(--navy); }

    .sta-simpan { margin: 18px 0 0; font-size: .76rem; color: var(--tinta-2); }

    @media (max-width: 575.98px) {
        .sta-kartu { padding: 26px 18px; }

        /* Label dan nilainya ditumpuk: berdampingan, "Nomor pendaftaran" dan
           nomornya saling menghimpit sampai keduanya patah di tengah kata. */
        .sta-rincian > div { flex-direction: column; align-items: flex-start; }
        .sta-rincian dd { text-align: left; }
    }
</style>
@stop
