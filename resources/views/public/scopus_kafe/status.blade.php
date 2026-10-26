@extends('public.layout.header')

@section('title', 'Status pemesanan ' . $pemesanan->id_pemesanan . ' | Rumah Scopus')

@push('kepala')
{{-- Jangan diindeks: isinya keterangan satu orang. Ditulis sebagai meta di
     tumpukan 'kepala', BUKAN @section('robot') — tata letak publik ini tidak
     punya @yield untuk itu, jadi bagiannya akan diam-diam tidak digambar. --}}
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600..900&family=Figtree:wght@400;500;700;800&display=swap">
@endpush

@section('konten')
{{--
    Keadaan satu pemesanan Scopus Kafe.

    Bentuknya MENYALIN halaman status Scopus Camp, dan itu disengaja: satu
    orang bisa memakai dua layanan, dan halaman hasil yang berbeda bentuk
    membuatnya mengira salah satunya bukan milik Rumah Scopus.

    Halaman ini menampung orang dari TIGA arah: sesudah membayar di gerbang,
    lewat tautan di suratnya, dan saat tagihannya gagal dibuat. Jadi ia harus
    masuk akal dibaca dalam ketiga keadaan itu.
--}}
@php
    use App\Support\PendaftaranSemuaLayanan as Katalog;

    $lunas = in_array((string) $pemesanan->status, Katalog::KEADAAN['lunas']['nilai'], true);
    $batal = (string) $pemesanan->status === 'pembayaran ditolak';

    $gagalBayar = ! $lunas && in_array((string) $pemesanan->bayar_status,
        ['failed', 'cancel', 'canceled', 'gagal', 'gagal-dibuat'], true);

    $habisWaktu = ! $lunas && (string) $pemesanan->bayar_status === 'expired';

    /*
     * Tombol bayarnya hanya digambar kalau tagihannya MEMANG masih bisa
     * dibayar. Tanpa pemeriksaan tenggat, yang membukanya besok menekan
     * tombol yang mengantarnya ke halaman gerbang bertuliskan "order has
     * expired" — dan menyalahkan dirinya sendiri.
     */
    $masihBisaBayar = $pemesanan->bayar_url
        && ! $lunas && ! $batal && ! $gagalBayar && ! $habisWaktu
        && ($pemesanan->kedaluwarsa_pada === null || $pemesanan->kedaluwarsa_pada->isFuture());

    /*
     * Tanggal sesinya harus MASIH DI DEPAN untuk boleh dibuatkan tagihan
     * baru — syarat yang sama diperiksa ulang peladennya di bayarUlang().
     */
    $tanggal = $pemesanan->tanggal_pemesanan;
    $masihDiDepan = $tanggal === null || $tanggal->copy()->startOfDay()->gte(now()->startOfDay());

    $bolehBayarUlang = ! $lunas && ! $batal && $masihDiDepan && $gerbangSiap;
    $bisaLanjut = $masihBisaBayar || $bolehBayarUlang;

    $wa = \App\Support\TautanWa::kirim(
        config('panitia.whatsapp'),
        'Halo, saya mau bertanya soal pemesanan Scopus Kafe nomor '
            . $pemesanan->id_pemesanan . '.'
    );
@endphp

@include('public.scopus_kafe.partials.gaya-status')

<section class="sts" data-keadaan="{{ route('public.scopuskafe.keadaan', $pemesanan->id) }}"
    data-selesai="{{ $lunas || $batal || $gagalBayar || $habisWaktu ? '1' : '0' }}">
    <div class="container">
        <div class="sts-bungkus">

            @foreach (['success' => 'sukses', 'error' => 'galat'] as $kunci => $rupa)
                @if (session($kunci))
                    <div class="sts-kabar {{ $rupa }}" role="{{ $kunci === 'error' ? 'alert' : 'status' }}">
                        <i class="fas fa-{{ $kunci === 'error' ? 'exclamation' : 'check' }}-circle"></i>
                        <span>{{ session($kunci) }}</span>
                    </div>
                @endif
            @endforeach

            @if ($errors->any())
                <div class="sts-kabar galat" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <div class="sts-kotak {{ $lunas ? 'lunas' : (($batal || $habisWaktu) ? 'batal' : ($gagalBayar ? 'gagal' : 'tunggu')) }}">

                <div class="sts-kepala">
                    @if ($lunas)
                        {{-- Centangnya SVG, bukan glif: yang menyenangkan
                             dilihat di layar "sudah lunas" adalah centang yang
                             DIGAMBAR, dan itu menuntut stroke-dasharray pada
                             path sungguhan. --}}
                        <span class="sts-meterai lunas">
                            <svg class="sts-centang" viewBox="0 0 52 52" aria-hidden="true" focusable="false">
                                <circle class="sts-centang-lingkar" cx="26" cy="26" r="23" />
                                <path class="sts-centang-garis" d="M14.5 26.5 L22.5 34.5 L37.5 18.5" />
                            </svg>
                        </span>
                        <h1 class="sts-judul">Pembayaran Anda sudah lunas</h1>
                        <p class="sts-kata">
                            Terima kasih — tempat Anda sudah pasti. Keterangan lengkapnya
                            juga kami kirim ke <strong>{{ $pemesanan->email }}</strong>.
                        </p>
                    @elseif ($habisWaktu)
                        <span class="sts-meterai batal"><i class="fas fa-hourglass-end"></i></span>
                        <h1 class="sts-judul">Batas waktu pembayarannya sudah lewat</h1>
                        <p class="sts-kata">
                            Tagihan sebelumnya sudah kedaluwarsa.
                            @if ($bisaLanjut)
                                <strong>Tempat Anda masih kami tahan</strong> — tagihannya
                                bisa dibuat ulang sekarang, dan nomor pemesanan Anda tidak
                                berubah.
                            @else
                                Hubungi panitia dengan menyebut nomor pemesanan di bawah.
                            @endif
                        </p>
                    @elseif ($batal)
                        <span class="sts-meterai batal"><i class="fas fa-times"></i></span>
                        <h1 class="sts-judul">Pemesanan ini sudah tidak berjalan</h1>
                        <p class="sts-kata">
                            Statusnya sekarang <strong>{{ $pemesanan->status }}</strong>.
                            Kalau menurut Anda ini keliru, hubungi panitia dengan menyebut
                            nomor pemesanan di bawah.
                        </p>
                    @elseif ($gagalBayar)
                        <span class="sts-meterai gagal"><i class="fas fa-exclamation"></i></span>
                        <h1 class="sts-judul">Pembayarannya belum berhasil</h1>
                        <p class="sts-kata">
                            Pembayaran sebelumnya tidak selesai — ini biasanya urusan bank
                            atau kartunya, bukan kesalahan Anda.
                            @if ($bisaLanjut)
                                <strong>Tempat Anda masih kami tahan</strong>, dan tagihannya
                                bisa dibuat ulang sekarang.
                            @else
                                Hubungi panitia dengan menyebut nomor pemesanan di bawah.
                            @endif
                        </p>
                    @elseif (! $bisaLanjut)
                        {{-- Belum dibayar, dan tidak ada lagi jalan membayarnya
                             — paling sering karena tanggal sesinya sudah lewat.
                             Judulnya tidak boleh berbunyi "tinggal satu langkah
                             lagi": tidak ada langkah yang bisa ia kerjakan. --}}
                        <span class="sts-meterai batal"><i class="fas fa-ban"></i></span>
                        <h1 class="sts-judul">Pembayarannya tidak pernah selesai</h1>
                        <p class="sts-kata">
                            Tanggal sesinya sudah lewat, jadi pembayarannya tidak bisa
                            diselesaikan sendiri lagi. Merasa sudah membayar? Hubungi
                            panitia dengan menyebut nomor pemesanan di bawah.
                        </p>
                    @else
                        <span class="sts-meterai tunggu"><i class="fas fa-hourglass-half"></i></span>
                        <h1 class="sts-judul">Tinggal satu langkah lagi</h1>
                        <p class="sts-kata">
                            Pemesanan Anda sudah tersimpan dan tempatnya ditahan.
                            Yang tersisa hanya menyelesaikan pembayaran.
                        </p>
                    @endif
                </div>

                <div class="sts-karcis">
                    {{-- Nomor pemesanan berdiri sendiri di puncak kwitansi: ia
                         satu-satunya yang harus DISEBUT ULANG orangnya saat
                         menghubungi panitia. --}}
                    <div class="sts-nomor">
                        <span class="sts-nomor-label">Nomor pemesanan</span>
                        <strong class="sts-nomor-nilai">{{ strtoupper($pemesanan->id_pemesanan) }}</strong>
                    </div>

                    <dl class="sts-baris">
                        <dt>Nama</dt>
                        <dd>{{ $pemesanan->nama }}</dd>

                        @if ($tanggal)
                            <dt>Tanggal</dt>
                            <dd>{{ $tanggal->locale('id')->translatedFormat('l, j F Y') }}</dd>
                        @endif

                        @foreach ($sesi as $ke => $s)
                            <dt>{{ $ke === 0 ? 'Sesi' : 'Sesi ke-' . ($ke + 1) }}</dt>
                            <dd>
                                {{ $s['nama'] }}
                                @if ($s['mulai'])
                                    <span class="sts-jam">
                                        {{ \Carbon\Carbon::parse($s['mulai'])->format('H:i') }}–{{ \Carbon\Carbon::parse($s['selesai'])->format('H:i') }}
                                    </span>
                                @endif
                            </dd>
                        @endforeach

                        @if ($sesi !== [] && $sesi[0]['lokasi'])
                            <dt>Lokasi</dt>
                            <dd>{{ $sesi[0]['lokasi'] }}</dd>
                        @endif

                        @if ($pemesanan->bayar_kanal)
                            <dt>Dibayar lewat</dt>
                            <dd>{{ $pemesanan->bayar_kanal }}</dd>
                        @endif
                    </dl>

                    <div class="sts-total">
                        <span>{{ $lunas ? 'Sudah dibayar' : 'Total tagihan' }}</span>
                        <strong>Rp {{ number_format((int) $pemesanan->total_keseluruhan_pembayaran, 0, ',', '.') }}</strong>
                    </div>
                </div>

                @unless ($batal && ! $lunas)
                    <div class="sts-bagian">
                        <h2 class="sts-bagian-judul"><i class="fas fa-clipboard-list"></i> Setelah ini</h2>
                        <ol class="sts-langkah">
                            @if ($lunas)
                                <li><span><strong>Simpan nomor pemesanan</strong> di atas — itu
                                    yang disebut saat menghubungi panitia.</span></li>
                                <li><span>Datang sesuai tanggal dan jam di atas. Panitia
                                    mengabari lagi menjelang harinya.</span></li>
                            @elseif (! $bisaLanjut)
                                <li><span><strong>Hubungi panitia</strong> lewat tombol di bawah,
                                    sebutkan nomor pemesanan di atas.</span></li>
                            @else
                                <li><span><strong>Selesaikan pembayaran</strong> lewat tombol di
                                    bawah — QRIS atau Virtual Account.</span></li>
                                <li><span>Halaman ini <strong>berubah sendiri</strong> begitu
                                    pembayaran Anda masuk. Tidak perlu mengirim bukti
                                    transfer.</span></li>
                            @endif
                        </ol>
                    </div>
                @endunless

                <div class="sts-aksi">
                    @if ($masihBisaBayar)
                        <a href="{{ $pemesanan->bayar_url }}" class="sts-tombol">
                            <i class="fas fa-credit-card"></i> Bayar sekarang
                        </a>
                        <div class="sts-catatan">
                            <i class="fas fa-info-circle"></i>
                            <span>Begitu pembayaran Anda masuk, halaman ini berubah sendiri —
                                tidak perlu mengirim bukti transfer.</span>
                        </div>
                    @elseif ($bolehBayarUlang)
                        <form method="POST" action="{{ route('public.scopuskafe.bayarulang', $pemesanan->id) }}">
                            @csrf
                            <button type="submit" class="sts-tombol" style="width: 100%; border: 0;">
                                <i class="fas fa-redo"></i>
                                {{ $gagalBayar || $habisWaktu ? 'Buat tagihan baru' : 'Lanjutkan pembayaran' }}
                            </button>
                        </form>
                        <div class="sts-catatan">
                            <i class="fas fa-info-circle"></i>
                            <span>Tagihan baru berlaku {{ $menitBayar }} menit sejak dibuat.
                                Nomor pemesanan Anda tidak berubah.</span>
                        </div>
                    @endif

                    @if ($batal && $masihDiDepan)
                        <a href="{{ route('public.scopuskafe.formpendaftaran') }}" class="sts-tombol">
                            <i class="fas fa-pen"></i> Pesan lagi
                        </a>
                    @endif

                    <div class="sts-aksi-kecil">
                        @unless ($batal && ! $lunas)
                            <a href="{{ route('public.scopuskafe.dokumen', [$pemesanan->id, $lunas ? 'kwitansi' : 'faktur']) }}"
                                class="sts-tombol-kecil">
                                <i class="fas fa-file-pdf"></i>
                                <span>{{ $lunas ? 'Kwitansi (PDF)' : 'Faktur (PDF)' }}</span>
                            </a>
                        @endunless

                        <form method="POST" action="{{ route('public.scopuskafe.kirimulang', $pemesanan->id) }}">
                            @csrf
                            <button type="submit" class="sts-tombol-kecil">
                                <i class="fas fa-envelope"></i>
                                <span>Kirim ulang ke email</span>
                            </button>
                        </form>

                        @if ($wa !== '')
                            <a href="{{ $wa }}" target="_blank" rel="noopener noreferrer"
                                class="sts-tombol-kecil">
                                <i class="fab fa-whatsapp"></i>
                                <span>Tanya panitia</span>
                            </a>
                        @endif
                    </div>
                </div>

            </div>

            <a href="{{ route('public.scopuskafe.index') }}" class="sts-balik">
                <i class="fas fa-arrow-left"></i> Kembali ke Scopus Kafe
            </a>
        </div>
    </div>
</section>

@include('public.scopus_kafe.partials.poller')
@stop
