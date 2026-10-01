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
                <div>
                    <dt>Total</dt>
                    <dd class="sta-total">Rp {{ number_format((int) $pendaftaran->total_pembayaran, 0, ',', '.') }}</dd>
                </div>
            </dl>

            @if (! $lunas && ! $batal && ! $habis)
                @if ($pendaftaran->sisa_waktu)
                    <p class="sta-waktu">
                        <i class="fas fa-stopwatch" aria-hidden="true"></i>
                        Selesaikan dalam <strong>{{ $pendaftaran->sisa_waktu }}</strong>,
                        setelah itu kursinya dilepas untuk orang lain.
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
                            <li>Transfer <strong>tepat</strong>
                                Rp {{ number_format((int) $pendaftaran->total_pembayaran, 0, ',', '.') }} ke:</li>
                        </ol>
                        <div class="sta-rekening">
                            <span>BRI</span>
                            <strong>2164 0100 0467 563</strong>
                            <small>a.n. Rumah Scopus Akademi</small>
                        </div>
                        <ol start="2">
                            <li>Kirim bukti transfer beserta nomor
                                <code>{{ $pendaftaran->id_transaksi }}</code> ke panitia lewat WhatsApp.</li>
                            <li>Panitia mengonfirmasi, lalu tautan masuk dikirim ke email Anda.</li>
                        </ol>

                        <a class="sta-wa"
                            href="https://wa.me/6288983567819?text={{ rawurlencode('Halo, saya sudah mendaftar Sharing Session dengan nomor ' . $pendaftaran->id_transaksi . ' atas nama ' . $pendaftaran->nama . '. Berikut bukti transfernya.') }}"
                            target="_blank" rel="noopener">
                            <i class="fab fa-whatsapp" aria-hidden="true"></i>
                            Kirim bukti transfer
                        </a>
                    </div>
                @endif
            @endif

            <div class="sta-aksi">
                @if ($batal || $habis)
                    <a href="{{ route('public.sharingsession.index') }}" class="sta-tombol">
                        Lihat sesi yang dibuka
                    </a>
                @else
                    <a href="{{ route('public.sharingsession.index') }}" class="sta-tautan">
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

    .sta-galat {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 22px;
        padding: 14px 18px;
        border-radius: 14px;
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #991b1b;
        font-size: .9rem;
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

    .sta-waktu > .fas { margin: 0 !important; }

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
