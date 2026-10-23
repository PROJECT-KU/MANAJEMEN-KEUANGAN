@extends('public.layout.header')

@section('title', 'Status pendaftaran ' . $pendaftaran->id_transaksi . ' | Rumah Scopus')

@push('kepala')
{{-- Jangan diindeks: isinya keterangan satu orang.

     Ditulis sebagai meta di tumpukan 'kepala', BUKAN @section('robot'):
     tata letak publik ini tidak punya @yield untuk itu, jadi bagiannya akan
     diam-diam tidak digambar sama sekali dan halaman ini terindeks. --}}
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600..900&family=Figtree:wght@400;500;700;800&display=swap">
@endpush

@section('konten')
{{--
    Keadaan satu pendaftaran Scopus Camp.

    Halaman ini yang menampung orang dari TIGA arah: sesudah membayar di
    DOKU, lewat tautan di surat buktinya, dan saat tagihannya gagal dibuat.
    Jadi ia harus masuk akal dibaca dalam ketiga keadaan itu, termasuk yang
    paling buruk — tagihan tidak terbuat dan orangnya tidak tahu harus apa.

    Yang ditanyakan berkala BUKAN seluruh halaman melainkan satu alamat yang
    cuma memulangkan keadaannya. Memuat ulang seluruh halaman tiap beberapa
    detik di hosting ini pernah berakhir 503 — kuota prosesnya habis oleh
    poller, bukan oleh pengunjung.
--}}
@php
    $lunas = in_array((string) $pendaftaran->status,
        \App\Support\PendaftaranSemuaLayanan::KEADAAN['lunas']['nilai'], true);

    $batal = in_array((string) $pendaftaran->status,
        ['Pendaftaran Dibatalkan', 'Pendaftaran Ditolak', 'Pendaftaran Refund'], true);

    /*
     * Tombol bayarnya hanya digambar kalau tagihannya MEMANG masih bisa
     * dibayar: alamatnya ada, belum lunas, belum dibatalkan, dan tenggatnya
     * belum lewat. Tanpa pemeriksaan tenggat, yang membukanya besok menekan
     * tombol yang mengantarnya ke halaman DOKU bertuliskan "order has
     * expired" — dan menyalahkan dirinya sendiri.
     */
    $masihBisaBayar = $pendaftaran->bayar_url
        && ! $lunas
        && ! $batal
        && ($pendaftaran->kedaluwarsa_pada === null || $pendaftaran->kedaluwarsa_pada->isFuture());

    $wa = \App\Support\TautanWa::kirim(
        config('panitia.whatsapp'),
        'Halo, saya mau bertanya soal pendaftaran Scopus Camp nomor '
            . $pendaftaran->id_transaksi . '.'
    );
@endphp
<style>
    .sts {
        --kertas: #fdf5e8;  --kertas-2: #f8ead4;
        --tinta: #16233f;   --tinta-2: #5c6b85;
        --jingga: #e76d20;  --jingga-muda: #fb8c3d; --jingga-tua: #ad4d12;
        --rumput: #0f766e;  --garis: #e7dcc6;

        background: var(--kertas); color: var(--tinta);
        font-family: 'Figtree', system-ui, sans-serif;
        font-size: 18px; padding: 118px 0 72px; min-height: 86vh;
        overflow-x: clip;
    }
    .sts h1, .sts h2 {
        font-family: 'Fraunces', Georgia, serif; font-weight: 800;
        color: var(--tinta); margin: 0;
    }
    .sts p { margin: 0; color: var(--tinta-2); line-height: 1.7; }
    .sts a { text-decoration: none; }

    .sts-kotak {
        max-width: 640px; margin: 0 auto;
        background: #fff; border: 1px solid var(--garis); border-radius: 24px;
        padding: 36px 32px 32px; box-shadow: 0 14px 36px rgba(22, 35, 63, .07);
    }

    .sts-lencana {
        display: inline-flex; align-items: center; gap: 9px;
        padding: 8px 17px; border-radius: 999px; margin-bottom: 18px;
        font-size: .9rem; font-weight: 800;
    }
    .sts-lencana.tunggu { background: #fff4d6; color: #8a6200; }
    .sts-lencana.lunas  { background: #dcf3ee; color: var(--rumput); }
    .sts-lencana.batal  { background: #fbe4e0; color: #a3281c; }

    .sts-judul { font-size: clamp(1.5rem, 3vw, 2rem); line-height: 1.2; }
    .sts-kata { margin-top: 10px; font-size: 1.04rem; }

    .sts-rincian {
        margin: 26px 0 0; padding: 20px; border-radius: 16px;
        background: var(--kertas); border: 1px solid var(--garis);
        display: grid; grid-template-columns: 1fr 1fr; gap: 16px 22px;
    }
    .sts-rincian dt {
        font-size: .76rem; font-weight: 800; letter-spacing: .06em;
        text-transform: uppercase; color: var(--tinta-2); margin-bottom: 2px;
    }
    .sts-rincian dd { margin: 0; font-weight: 700; font-size: 1.02rem; color: var(--tinta); }
    .sts-rincian .sts-penuh { grid-column: 1 / -1; }

    .sts-tombol {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        width: 100%; min-height: 60px; margin-top: 22px; padding: 0 22px;
        border: 0; border-radius: 16px; cursor: pointer;
        background: linear-gradient(135deg, var(--jingga-muda), var(--jingga-tua));
        color: #fff; font-family: 'Fraunces', Georgia, serif;
        font-size: 1.1rem; font-weight: 800;
        box-shadow: 0 10px 24px rgba(231, 109, 32, .32);
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .sts-tombol:hover { transform: translateY(-2px); color: #fff; }

    .sts-tombol-polos {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        width: 100%; min-height: 54px; margin-top: 12px; padding: 0 20px;
        border: 2px solid var(--garis); border-radius: 15px;
        background: #fff; color: var(--tinta); font-weight: 800; font-size: 1rem;
    }
    .sts-tombol-polos:hover { border-color: var(--jingga); color: var(--jingga-tua); }

    .sts-catatan {
        display: flex; align-items: flex-start; gap: 10px; margin-top: 20px;
        font-size: .92rem; line-height: 1.6; color: var(--tinta-2);
    }
    .sts-catatan i { color: var(--jingga); margin-top: 3px; }

    @media (max-width: 575.98px) {
        .sts { font-size: 17px; padding-top: 100px; }
        .sts-kotak { padding: 28px 20px 26px; border-radius: 18px; }
        .sts-rincian { grid-template-columns: 1fr; }
    }
</style>

<section class="sts" data-keadaan="{{ route('public.scopuscamp.keadaan', $pendaftaran->id) }}"
    data-selesai="{{ $lunas || $batal ? '1' : '0' }}">
    <div class="container">
        <div class="sts-kotak">

            @if ($lunas)
                <span class="sts-lencana lunas"><i class="fas fa-check-circle"></i> Sudah dibayar</span>
                <h1 class="sts-judul">Pendaftaran Anda sudah lunas</h1>
                <p class="sts-kata">
                    Terima kasih. Keterangan lengkap beserta tautan grup peserta
                    sudah kami kirim ke <strong>{{ $pendaftaran->email }}</strong>.
                    Jika tidak ada di kotak masuk, coba periksa folder spam.
                </p>
            @elseif ($batal)
                <span class="sts-lencana batal"><i class="fas fa-times-circle"></i> Tidak aktif</span>
                <h1 class="sts-judul">Pendaftaran ini sudah tidak berjalan</h1>
                <p class="sts-kata">
                    Statusnya sekarang <strong>{{ $pendaftaran->status }}</strong>.
                    Kalau menurut Anda ini keliru, hubungi panitia dengan menyebut
                    nomor pendaftaran di bawah.
                </p>
            @else
                <span class="sts-lencana tunggu"><i class="fas fa-clock"></i> Menunggu pembayaran</span>
                <h1 class="sts-judul">Pendaftaran Anda sudah tersimpan</h1>
                <p class="sts-kata">
                    Kursi Anda sudah ditahan. Tinggal satu langkah lagi:
                    menyelesaikan pembayaran.
                </p>
            @endif

            <dl class="sts-rincian">
                <div class="sts-penuh">
                    <dt>Nomor pendaftaran</dt>
                    <dd>{{ $pendaftaran->id_transaksi }}</dd>
                </div>
                <div class="sts-penuh">
                    <dt>Kelas</dt>
                    <dd>
                        @if ($kategori)
                            {{ $kategori->nama }}@if ($kategori->nama_ke) #{{ $kategori->nama_ke }}@endif
                        @else
                            {{-- Angkatannya sudah dihapus panitia; barisnya tetap
                                 ada, jadi halaman ini tidak boleh 500 karenanya. --}}
                            Keterangan kelasnya tidak tersedia lagi
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Atas nama</dt>
                    <dd>{{ $pendaftaran->nama }}</dd>
                </div>
                <div>
                    <dt>Total bayar</dt>
                    <dd>Rp {{ number_format((int) $pendaftaran->total_pembayaran, 0, ',', '.') }}</dd>
                </div>
                @if ($pendaftaran->bayar_kanal)
                    <div>
                        <dt>Dibayar lewat</dt>
                        <dd>{{ $pendaftaran->bayar_kanal }}</dd>
                    </div>
                @endif
            </dl>

            @if ($masihBisaBayar)
                <a href="{{ $pendaftaran->bayar_url }}" class="sts-tombol">
                    <i class="fas fa-credit-card"></i> Bayar sekarang
                </a>
                <div class="sts-catatan">
                    <i class="fas fa-info-circle"></i>
                    <span>Begitu pembayaran Anda masuk, halaman ini berubah sendiri —
                        tidak perlu mengirim bukti transfer.</span>
                </div>
            @elseif (! $lunas && ! $batal)
                <div class="sts-catatan">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Halaman pembayarannya sudah tidak berlaku. Hubungi panitia
                        lewat WhatsApp dengan menyebut nomor pendaftaran di atas,
                        dan tagihannya akan dibuatkan lagi.</span>
                </div>
            @endif

            @if ($wa !== '')
                <a href="{{ $wa }}" target="_blank" rel="noopener" class="sts-tombol-polos">
                    <i class="fab fa-whatsapp"></i> Tanya panitia lewat WhatsApp
                </a>
            @endif

            <a href="{{ route('public.scopuscamp.index') }}" class="sts-tombol-polos">
                <i class="fas fa-arrow-left"></i> Kembali ke daftar Scopus Camp
            </a>

        </div>
    </div>
</section>

<script>
    (function () {
        var wadah = document.querySelector('.sts');

        // Sudah selesai: tidak ada yang perlu ditunggu, jadi tidak ada yang
        // perlu ditanyakan.
        if (! wadah || wadah.dataset.selesai === '1') {
            return;
        }

        var alamat = wadah.dataset.keadaan;
        var sisa = 40;   // 40 x 5 detik = sekitar 3 menit, lalu berhenti sendiri

        /*
         * Berhenti sendiri, dan berhenti saat tabnya tidak terlihat.
         *
         * Poller yang jalan selamanya di tiap tab yang lupa ditutup adalah
         * sebab 503 di hosting ini: kuota prosesnya habis oleh permintaan
         * yang tidak ada yang membaca jawabannya.
         */
        var jam = setInterval(function () {
            if (document.hidden) {
                return;
            }

            if (--sisa <= 0) {
                clearInterval(jam);
                return;
            }

            fetch(alamat, { headers: { 'Accept': 'application/json' } })
                .then(function (jawab) { return jawab.ok ? jawab.json() : null; })
                .then(function (data) {
                    if (data && data.selesai) {
                        clearInterval(jam);
                        window.location.reload();
                    }
                })
                .catch(function () { /* jaringan putus sesaat; dicoba lagi nanti */ });
        }, 5000);
    })();
</script>
@stop
