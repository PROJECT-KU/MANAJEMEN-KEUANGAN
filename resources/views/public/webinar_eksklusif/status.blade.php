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
@php($lunas = $pendaftaran->lunas)
@php($habis = $pendaftaran->sudah_kedaluwarsa || $pendaftaran->status === 'expired')
@php($batal = $pendaftaran->status === 'cancel')

{{-- Keadaan KEEMPAT: bukti sudah dikirim, panitia belum mencocokkan.

     Dulu halaman ini cuma punya tiga keadaan, jadi sesudah mengunggah
     peserta kembali ke layar yang sama persis — lengkap dengan nomor
     rekening, langkah "transfer dulu", dan borang unggah yang masih
     menganga. Yang awam membacanya sebagai "belum berhasil", lalu
     mengunggah lagi. Dan lagi.

     Sejak ada keadaan ini, yang sudah mengirim bukti tidak lagi
     disuguhi satu pun hal yang bisa ditekan berulang. --}}
@php($menunggu = ! $lunas && ! $batal && ! $habis && $buktiAda)

{{-- Dua lajur hanya kalau lajur KANANNYA memang berisi.

     Yang sudah lunas, dibatalkan, atau kedaluwarsa tidak punya satu
     pun hal yang harus dikerjakan — tanpa penjaga ini kartunya
     menggambar lajur kanan kosong lengkap dengan garis pemisah yang
     berdiri sendiri di tengah ruang putih. --}}
@php($duaLajur = ! $lunas && ! $batal && ! $habis && ! $menunggu)

<section class="sta-latar">
    {{-- Lebarnya ikut keadaan.

         Yang masih harus membayar punya DUA lajur isi, dan di situ layar penuh
         memang terpakai. Yang sudah lunas, sudah mengirim bukti, batal, atau
         kedaluwarsa cuma punya satu lajur — dibiarkan selebar layar juga,
         yang tergambar kartu putih 1.382px dengan kolom 620px melayang di
         tengahnya. --}}
    <div class="container sta-wadah @if (! $duaLajur) sta-wadah-ramping @endif">

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
                : (($habis || $batal)
                    ? ['merah', 'fa-times-circle']
                    : ($menunggu ? ['hijau', 'fa-check-circle'] : ['kuning', 'fa-hourglass-half'])))
            {{-- Ikon, judul, dan kalimat pembukanya dibungkus satu kepala.

                 Bertumpuk di tengah, ketiganya memakan 184px sebelum isi
                 pertamanya terlihat — di layar 846px itu seperlima layar
                 habis untuk sapaan. Di layar lebar ketiganya disejajarkan
                 jadi satu baris; di ponsel tetap bertumpuk di tengah, sebab
                 di sana lebarnya yang langka, bukan tingginya. --}}
            <div class="sta-kepala">
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
                    @elseif ($menunggu)
                        Bukti pembayaran Anda sudah masuk
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
                    @elseif ($menunggu)
                        {{-- Kalimat terpentingnya: TIDAK ADA LAGI yang harus ia
                             kerjakan. Tanpa itu orang menunggu sambil menduga-duga
                             apakah ada langkah yang terlewat. --}}
                        Panitia memeriksanya pada jam kerja.
                        <strong>Anda tidak perlu mengirim apa pun lagi.</strong>
                        Kursi Anda ditahan sampai pemeriksaannya selesai.
                    @else
                        Tinggal satu langkah: selesaikan pembayarannya.
                    @endif
                </p>
            </div>


            {{-- DUA LAJUR di layar lebar, satu lajur di ponsel.

                 Dibungkus div sungguhan, bukan diserahkan ke penempatan
                 otomatis grid pada anak-anak .sta-kartu: sebagian anaknya ada
                 di dalam @if, jadi yang tergambar berbeda-beda per keadaan —
                 dan penempatan otomatis akan menaruh hal yang sama di baris
                 yang berbeda tergantung keadaan mana yang sedang aktif. --}}
            <div class="sta-kisi @if (! $duaLajur) sta-kisi-tunggal @endif">
                <div class="sta-lajur">

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
                </div>{{-- /lajur kiri --}}

                <div class="sta-lajur">

            @if (! $lunas && ! $batal && ! $habis && ! $menunggu)
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
                            {{-- Batas waktunya ditulis sebagai ISO 8601 LENGKAP
                                 DENGAN SELISIH ZONA (+07:00), bukan "2026-10-05
                                 23:43". Tanpa selisihnya, peramban menafsirkan
                                 angka itu memakai zona waktu PEMBACANYA — dan
                                 peserta yang membuka dari luar Jakarta akan
                                 melihat sisa waktu meleset berjam-jam.

                                 Isi awalnya tetap dirender peladen: tanpa
                                 skrip, yang terbaca kalimat yang sama seperti
                                 dulu, bukan kotak kosong. --}}
                            Selesaikan dalam <strong data-mis-mundur="{{ $pendaftaran->kedaluwarsa_pada->toIso8601String() }}">{{ $pendaftaran->sisa_waktu }}</strong>,
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
                            {{-- Langkah keduanya MENUNJUK KE BAWAH, ke borang di
                                 halaman ini. Sebelumnya tertulis "kirim ke
                                 panitia lewat WhatsApp" — dan bukti yang
                                 menumpuk di satu nomor pribadi tidak pernah
                                 sampai ke baris pendaftarannya; yang memeriksa
                                 harus mencocokkan tangkapan layar dengan
                                 daftar, satu per satu. --}}
                            <li><strong>Unggah bukti transfernya di halaman ini</strong>,
                                di kotak tepat di bawah. Nomor
                                <code>{{ $pendaftaran->id_transaksi }}</code> sudah menempel
                                sendiri, jadi tidak perlu Anda ketik.</li>
                            {{-- Yang BENAR-BENAR terjadi. Sebelumnya tertulis
                                 "tautan masuk dikirim ke email Anda", padahal
                                 tidak ada mekanismenya di sistem — panitia
                                 membagikannya lewat grup. --}}
                            <li>Panitia mengonfirmasi, lalu Anda dimasukkan ke grup peserta
                                di WhatsApp — tautan masuk sesinya dibagikan di sana.</li>
                        </ol>
                    </div>

                </div>{{-- /lajur tengah --}}

                <div class="sta-lajur">

                    {{--
                        UNGGAH BUKTI TRANSFER — jalur UTAMA.

                        Dulu tombol WhatsApp hijau selebar kartu berdiri lebih
                        dulu, dan borang ini di bawahnya. Yang paling besar dan
                        paling atas yang ditekan orang, jadi buktinya tetap
                        mengalir ke nomor pribadi panitia dan tidak pernah
                        menempel ke barisnya.

                        Sekarang terbalik: borangnya yang utama, dan WhatsApp
                        turun jadi satu baris bantuan untuk yang unggahannya
                        bermasalah. Tombolnya tidak DIHAPUS — peserta yang
                        fotonya ditolak terus tetap butuh jalan keluar.
                    --}}
                    <div class="sta-unggah">
                        <p class="sta-bayar-judul">Sudah transfer? Unggah buktinya di sini</p>

                        @error('bukti')
                            <p class="sta-unggah-galat" role="alert">{{ $message }}</p>
                        @enderror

                        <form method="POST"
                            action="{{ route('public.webinareksklusif.bukti', $pendaftaran->getKey()) }}"
                            enctype="multipart/form-data" class="sta-unggah-borang">
                            @csrf

                            {{-- Isian aslinya disembunyikan 1x1 TRANSPARAN, bukan
                                 display:none, supaya tetap bisa menerima fokus
                                 papan ketik; labelnya yang jadi sasaran ketukan. --}}
                            <input type="file" id="sta-bukti" name="bukti" class="sta-berkas"
                                accept=".jpg,.jpeg,.png,.heic,.heif,image/jpeg,image/png,image/heic,image/heif"
                                required>

                            <label for="sta-bukti" class="sta-unggah-tombol">
                                <i class="fas fa-cloud-upload-alt" aria-hidden="true"></i>
                                <span class="sta-unggah-teks">
                                    <strong id="sta-bukti-nama">Pilih foto bukti transfer</strong>
                                    <small>JPG, PNG, HEIC &middot; maksimal 8 MB</small>
                                </span>
                            </label>

                            <button type="submit">
                                <i class="fas fa-paper-plane" aria-hidden="true"></i>
                                Kirim bukti
                            </button>
                        </form>

                        {{-- Yang BENAR-BENAR terjadi pada berkasnya. Orang yang
                             mengunggah foto dari ponselnya berhak tahu bahwa
                             yang disimpan versi ringkasnya, bukan fotonya
                             apa adanya. --}}
                        <p class="sta-unggah-nota">
                            Fotonya kami ringkas jadi WebP supaya hemat ruang; berkas aslinya
                            tidak kami simpan.
                        </p>

                        {{-- WhatsApp: BANTUAN, bukan jalur utama lagi.

                             Kalimatnya menyebut syaratnya di depan ("kalau
                             fotonya ditolak terus"), supaya yang unggahannya
                             lancar tidak merasa masih ada langkah tersisa. --}}
                        <p class="sta-bantuan">
                            Kalau fotonya ditolak terus atau ada kendala lain,
                            <a href="https://wa.me/{{ config('panitia.whatsapp') }}?text={{ rawurlencode('Halo, saya kesulitan mengunggah bukti transfer untuk pendaftaran ' . $pendaftaran->id_transaksi . ' atas nama ' . $pendaftaran->nama . '.') }}"
                                target="_blank" rel="noopener">
                                <i class="fab fa-whatsapp" aria-hidden="true"></i> hubungi panitia
                            </a>.
                        </p>
                    </div>
                @endif
            @endif

            {{--
                LAYAR SELESAI — buktinya sudah dikirim, panitia belum mencocokkan.

                Tidak ada satu pun yang bisa ditekan berulang di sini: nomor
                rekening, langkah "transfer dulu", hitung mundur, dan borang
                unggahnya semua TIDAK digambar. Yang awam tidak punya apa pun
                untuk diulang.

                Penggantian bukti tetap mungkin — ada yang memotret layar yang
                salah — tetapi DILIPAT di balik <details>. Yang terbuka sejak
                awal akan ditekan juga oleh yang tidak perlu menggantinya.
            --}}
            @if ($menunggu)
                <div class="sta-selesai">
                    <div class="sta-selesai-isi">
                        <i class="fas fa-shield-alt" aria-hidden="true"></i>
                        <span>
                            Kursi Anda ditahan sampai panitia selesai memeriksa &mdash;
                            batas waktu 24 jam itu <strong>tidak lagi berjalan</strong> untuk Anda.
                        </span>
                    </div>

                    @if ($buktiUrl)
                        <a class="sta-lihat-bukti" href="{{ $buktiUrl }}" target="_blank" rel="noopener">
                            <i class="fas fa-image" aria-hidden="true"></i>
                            Lihat bukti yang Anda kirim
                        </a>
                    @endif

                    @error('bukti')
                        <p class="sta-unggah-galat" role="alert">{{ $message }}</p>
                    @enderror

                    <details class="sta-ganti" @if ($errors->has('bukti')) open @endif>
                        <summary>Salah kirim? Ganti buktinya</summary>

                        <form method="POST"
                            action="{{ route('public.webinareksklusif.bukti', $pendaftaran->getKey()) }}"
                            enctype="multipart/form-data" class="sta-unggah-borang">
                            @csrf

                            <input type="file" id="sta-bukti" name="bukti" class="sta-berkas"
                                accept=".jpg,.jpeg,.png,.heic,.heif,image/jpeg,image/png,image/heic,image/heif"
                                required>

                            <label for="sta-bukti" class="sta-unggah-tombol">
                                <i class="fas fa-cloud-upload-alt" aria-hidden="true"></i>
                                <span class="sta-unggah-teks">
                                    <strong id="sta-bukti-nama">Pilih foto penggantinya</strong>
                                    <small>JPG, PNG, HEIC &middot; maksimal 8 MB</small>
                                </span>
                            </label>

                            <button type="submit">
                                <i class="fas fa-paper-plane" aria-hidden="true"></i>
                                Ganti bukti
                            </button>
                        </form>
                    </details>

                    <p class="sta-bantuan">
                        Ada kendala?
                        <a href="https://wa.me/{{ config('panitia.whatsapp') }}?text={{ rawurlencode('Halo, saya sudah mengunggah bukti transfer untuk pendaftaran ' . $pendaftaran->id_transaksi . ' atas nama ' . $pendaftaran->nama . '. Mohon dibantu pengecekannya.') }}"
                            target="_blank" rel="noopener">
                            <i class="fab fa-whatsapp" aria-hidden="true"></i> hubungi panitia
                        </a>.
                    </p>
                </div>
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

                </div>{{-- /lajur kanan --}}
            </div>{{-- /kisi --}}


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
        /* 96px, bukan 110: kepala situsnya `fixed-top` setinggi 80px, jadi
           angka ini TIDAK boleh turun di bawah itu — isinya akan tertutup.
           96 menyisakan 16px napas, dan 14px kembali ke layar.

           Bawahnya 44, bukan 90: di bawahnya sudah ada kaki situs setinggi
           412px; 90px kosong di antaranya tidak memisahkan apa pun. */
        padding: 96px 0 44px;
        min-height: 100vh;
        font-family: 'Poppins', 'Inter', system-ui, sans-serif;
        color: var(--tinta);
    }

    /* Selebar layar, bukan strip 640px di tengah.

       Bantalan sampingnya ikut lebar layar lewat clamp: 16px di ponsel sampai
       48px di layar besar. Dipatok satu angka, 16px terasa sesak di meja dan
       48px memakan separuh layar ponsel.

       Yang menjaga barisnya tetap terbaca BUKAN lebar wadahnya, melainkan kisi
       dua lajur di bawah — itu sebabnya wadahnya boleh dilepas sepenuhnya. */
    .sta-wadah {
        max-width: none;
        padding-left: clamp(16px, 3vw, 48px);
        padding-right: clamp(16px, 3vw, 48px);
    }

    /* Satu lajur dulu. Dua lajurnya dipasang di media query layar lebar,
       bukan sebaliknya — halaman ini paling sering dibuka di ponsel. */
    .sta-kisi { display: block; }

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

    /* Bawaannya bertumpuk di tengah — keadaan ponsel, tempat yang langka
       lebarnya, bukan tingginya. */
    .sta-kepala { text-align: center; }

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


    /* Angka hitung mundurnya TIDAK boleh patah antar-baris.

       Terukur di 375px: tanpa ini "23 jam 58 menit 54 detik lagi" terpotong
       di tengah, dan karena angkanya berganti tiap detik, titik patahnya ikut
       berpindah — kalimatnya terlihat bergoyang sendiri.

       tabular-nums menahan goyangan yang kedua: angka berlebar beda membuat
       seluruh kalimat bergeser tiap kali 1 berganti jadi 8. */
    .sta-waktu [data-mis-mundur] {
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }


    /* ------------------------------------------------- layar bukti terkirim */

    .sta-selesai { margin-top: 20px; }

    .sta-selesai-isi {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 13px 15px;
        border-radius: 13px;
        background: #ecfdf3;
        color: #166534;
        font-size: .88rem;
        line-height: 1.55;
        text-align: left;
    }

    .sta-selesai-isi > .fas { margin: 2px 0 0 !important; font-size: 1.05rem; }

    .sta-lihat-bukti {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        min-height: 48px;
        margin-top: 12px;
        border: 1.5px solid #d9e1ef;
        border-radius: 13px;
        background: #fff;
        color: var(--navy);
        font-size: .92rem;
        font-weight: 700;
        text-decoration: none;
    }

    .sta-lihat-bukti:hover {
        border-color: #818cf8;
        color: var(--navy);
        text-decoration: none;
    }

    .sta-lihat-bukti > .fas { margin: 0 !important; color: #6366f1; }

    /* Penggantian bukti DILIPAT. Yang terbuka sejak awal akan ditekan juga
       oleh yang tidak perlu menggantinya — dan itu persis kebiasaan yang
       hendak dihentikan layar ini. */
    .sta-ganti { margin-top: 14px; }

    .sta-ganti > summary {
        cursor: pointer;
        color: var(--tinta-2);
        font-size: .84rem;
        font-weight: 600;
        list-style: none;
    }

    /* Segitiga bawaannya dibuang di kedua mesin: Safari/Chrome lama memakai
       ::-webkit-details-marker, yang baru memakai list-style di atas. */
    .sta-ganti > summary::-webkit-details-marker { display: none; }

    .sta-ganti > summary::before {
        content: '\f067';
        margin-right: 7px;
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        font-size: .72rem;
    }

    .sta-ganti[open] > summary::before { content: '\f068'; }

    .sta-ganti > summary:hover { color: var(--navy); }

    .sta-ganti > form { margin-top: 12px; }

    /* Baris bantuan WhatsApp. Sengaja sekecil ini: ia jalan keluar untuk yang
       tersangkut, bukan langkah yang harus dilewati semua orang. */
    .sta-bantuan {
        margin: 14px 0 0;
        color: var(--tinta-2);
        font-size: .82rem;
        line-height: 1.55;
    }

    .sta-bantuan a {
        color: #128c7e;
        font-weight: 700;
        text-decoration: underline;
    }

    .sta-bantuan .fab { margin: 0 2px 0 0 !important; }

    /* ---------------------------------------------- unggah bukti transfer */

    .sta-unggah {
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px dashed #d9e1ef;
    }

    .sta-unggah-ada {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-bottom: 12px;
        padding: 11px 13px;
        border-radius: 11px;
        background: #ecfdf3;
        color: #166534;
        font-size: .86rem;
        line-height: 1.5;
    }

    .sta-unggah-ada > .fas { margin: 2px 0 0 !important; }
    .sta-unggah-ada a { color: #166534; font-weight: 700; text-decoration: underline; }

    .sta-unggah-galat {
        margin-bottom: 12px;
        padding: 11px 13px;
        border-radius: 11px;
        background: #fef2f2;
        color: #b91c1c;
        font-size: .86rem;
        line-height: 1.5;
    }

    .sta-unggah-borang { margin: 0; }

    /* Isian berkas bawaan peramban disembunyikan 1x1 TRANSPARAN, BUKAN
       display:none: yang display:none dilewati sama sekali oleh papan ketik,
       dan labelnya tidak bisa menerima fokus menggantikannya. */
    .sta-berkas {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .sta-unggah-tombol {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        margin: 0;
        padding: 14px 15px;
        border: 1.5px dashed #c7d2fe;
        border-radius: 13px;
        background: #f8faff;
        cursor: pointer;
    }

    .sta-unggah-tombol:hover { border-color: #818cf8; background: #f1f5ff; }

    /* Penanda fokus dipasang di LABELNYA, sebab isiannya sendiri tidak
       terlihat — tanpa ini yang berpindah dengan Tab tidak tahu di mana ia. */
    .sta-berkas:focus-visible + .sta-unggah-tombol {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, .18);
    }

    .sta-unggah-tombol > .fas {
        flex: 0 0 auto;
        margin: 0 !important;
        font-size: 1.35rem;
        color: #6366f1;
    }

    /* min-width: 0 supaya nama berkas yang panjang BOLEH menyusut dan
       terpotong rapi; tanpa itu unsur lentur memakai min-width:auto dan
       borangnya melebar melewati kartunya.

       text-align: left MELAWAN rata tengah yang diwarisi dari kartunya.
       Terukur di 375px: tanpa ini ikon awannya menempel di kiri sementara
       tulisannya melayang di tengah, dan keduanya terbaca tidak sejajar. */
    .sta-unggah-teks { min-width: 0; text-align: left; }

    .sta-unggah-teks strong {
        display: block;
        overflow: hidden;
        color: #1e293b;
        font-size: .9rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Keterangan formatnya TIDAK boleh patah: dipatahkan, "MB" turun
       sendirian ke baris kedua dan kotaknya jadi setinggi 100px di ponsel.

       Kalimatnya ikut diperpendek — "paling besar" jadi "maksimal", dan
       kata "atau" dibuang. Yang panjang 242px tidak muat di lajur selebar
       219px saat kisinya jadi tiga lajur di 1200px, dan karena tidak boleh
       patah ia MELUBERKAN seluruh kartunya. */
    .sta-unggah-teks small {
        display: block;
        color: #64748b;
        font-size: .78rem;
        white-space: nowrap;
    }

    .sta-unggah-borang button {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        width: 100%;
        min-height: 48px;
        margin-top: 11px;
        border: 0;
        border-radius: 13px;
        background: linear-gradient(135deg, #ff3131, #ff914d);
        color: #fff;
        font-size: .95rem;
        font-weight: 700;
    }

    .sta-unggah-borang button:hover { filter: brightness(1.05); }
    .sta-unggah-borang button:active { transform: scale(.98); }
    .sta-unggah-borang button > .fas { margin: 0 !important; }

    .sta-unggah-nota {
        margin: 10px 0 0;
        color: #94a3b8;
        font-size: .78rem;
        line-height: 1.5;
    }

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

    /* ------------------------------------------------- layar lebar: 2 lajur */

    @media (min-width: 992px) {
        .sta-kisi {
            display: grid;
            /* minmax(0, ...) WAJIB: tanpa itu lajurnya memakai min-width auto
               dan isi terlebar di dalamnya — nomor rekening yang berspasi —
               melebarkan lajurnya melewati jatahnya, lalu kartunya meluber. */
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 0 clamp(24px, 3vw, 44px);
            align-items: start;
        }

        /* Garis pemisah tipis di antaranya. Dipasang sebagai border lajur
           kedua, bukan unsur sendiri: unsur pemisah akan ikut tergambar di
           ponsel saat lajurnya menumpuk. */
        .sta-kisi:not(.sta-kisi-tunggal) > .sta-lajur + .sta-lajur {
            padding-left: clamp(24px, 3vw, 44px);
            border-left: 1px solid var(--garis);
        }

        /* 992-1299px: lajur KETIGA turun jadi pita selebar kisinya.

           Dipaksa jadi lajur ketiga di lebar segini, tiap lajurnya tinggal
           ~290px — nomor rekening yang berspasi tidak muat di situ. Turun ke
           bawah, urutan bacanya tetap benar: rincian, cara bayar, lalu
           unggah. */
        .sta-kisi:not(.sta-kisi-tunggal) > .sta-lajur:nth-child(3) {
            grid-column: 1 / -1;
            margin-top: clamp(18px, 2vw, 26px);
            padding-top: clamp(18px, 2vw, 26px);
            padding-left: 0;
            border-top: 1px solid var(--garis);
            border-left: 0;
        }

        /* Judul dan kalimat pembukanya tidak ikut melebar sampai ujung:
           baris teks sepanjang 1.400px tidak bisa diikuti mata, yang kehilangan
           tempatnya tiap kali berpindah ke baris berikutnya. */
        .sta-judul,
        .sta-sub {
            max-width: 46ch;
            margin-left: auto;
            margin-right: auto;
        }

        /* Bantalan tegaknya dipangkas 40 -> 26. Kartunya sudah punya
           jarak sendiri dari tepi layar; 40px di atas dan bawah cuma
           menambah gulir. */
        .sta-kartu { padding: 26px clamp(28px, 3vw, 48px) 30px; }

        /* Ikon, judul, dan kalimatnya jadi SATU BARIS.

           Terukur: bertumpuk 184px, sebaris 62px. Ikonnya ikut mengecil
           68 -> 52 sebab di samping teks ia tidak lagi harus menyangga
           perhatian sendirian. */
        .sta-kepala {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 22px;
            text-align: left;
        }

        .sta-kepala > .sta-ubin {
            flex: 0 0 52px;
            width: 52px;
            height: 52px;
            margin: 0;
            border-radius: 16px;
            font-size: 1.25rem;
        }

        /* min-width: 0 supaya judul panjang boleh menyusut, bukan mendorong
           ikonnya keluar: unsur lentur memakai min-width auto. */
        .sta-kepala > .sta-judul,
        .sta-kepala > .sta-sub {
            max-width: none;
            margin: 0;
            min-width: 0;
        }

        .sta-kepala > .sta-judul { font-size: 1.45rem; }

        .sta-kepala > .sta-sub { flex: 1 1 320px; font-size: .88rem; line-height: 1.55; }

        /* Jarak antar blok di dalam lajur dirapatkan; yang menumpuk tegak
           di sini enam blok, jadi tiap 6px terbayar enam kali. */
        .sta-kisi .sta-rincian { margin-bottom: 14px; padding: 14px 18px; }
        .sta-kisi .sta-tawar-akun { margin-top: 14px; padding: 14px 16px; }
        .sta-kisi .sta-bayar > ol { margin-bottom: 10px; }
        .sta-kisi .sta-unggah { margin-top: 14px; padding-top: 14px; }

        /* Barisnya dirapatkan 8px -> 5px. Enam baris, jadi tiap 3px yang
           dihemat terbayar dua belas kali — 36px tanpa satu pun baris jadi
           lebih sulit dibaca, sebab yang memisahkannya garis, bukan jarak. */
        .sta-kisi .sta-rincian > div { padding: 5px 0; }

        /* line-height 1.8 -> 1.6 pada daftar langkah bayar. Angkanya tetap
           di atas 1.5, batas yang masih nyaman dibaca untuk teks panjang. */
        .sta-kisi .sta-bayar ol { line-height: 1.6; }

        .sta-kisi .sta-rekening { margin: 10px 0; padding: 12px 18px; }

        /* Kalimat "fotonya kami ringkas" dan baris bantuan WhatsApp
           dirapatkan; keduanya keterangan, bukan langkah. */
        .sta-kisi .sta-unggah-nota { margin-top: 7px; }
        .sta-kisi .sta-bantuan { margin-top: 9px; }

        /* Satu lajur walau layarnya lebar: tidak ada yang harus dikerjakan,
           jadi tidak ada lajur kanan untuk menampungnya. Isinya dipusatkan
           supaya tidak jadi satu pita sempit yang menempel ke kiri. */
        .sta-kisi-tunggal { display: block; }

        /* Satu lajur: halamannya ikut menyempit, bukan kartunya dibiarkan
           melar dengan kolom sempit melayang di tengah. */
        .sta-wadah-ramping { max-width: 900px; }

        .sta-kisi-tunggal > .sta-lajur {
            max-width: 620px;
            margin: 0 auto;
            border-left: 0;
            padding-left: 0;
        }
    }

    /* ------------------------------------------ layar sangat lebar: 3 lajur */

    /* 1200px. Dipatok 1300 lebih dulu, dan di 1280 — ukuran laptop yang
       lazim — lajur ketiganya turun jadi pita penuh dan kisinya justru
       membengkak 670 -> 975. Ambangnya diturunkan sampai laptop 1280 ikut
       kebagian tiga lajur. */
    @media (min-width: 1200px) {
        .sta-kisi:not(.sta-kisi-tunggal) {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        /* Pita penuh di lebar menengah dibatalkan; ia kembali jadi lajur. */
        .sta-kisi:not(.sta-kisi-tunggal) > .sta-lajur:nth-child(3) {
            grid-column: auto;
            margin-top: 0;
            padding-top: 0;
            padding-left: clamp(24px, 3vw, 44px);
            border-top: 0;
            border-left: 1px solid var(--garis);
        }
    }

    @media (max-width: 575.98px) {
        .sta-kartu { padding: 26px 18px; }

        /* Label dan nilainya ditumpuk: berdampingan, "Nomor pendaftaran" dan
           nomornya saling menghimpit sampai keduanya patah di tengah kata. */
        .sta-rincian > div { flex-direction: column; align-items: flex-start; }
        .sta-rincian dd { text-align: left; }
    }
</style>

<script>
    /*
     * Hitung mundur batas waktu membayar, berjalan tiap detik.
     *
     * Sebelumnya angkanya dirender peladen SEKALI lewat diffForHumans, jadi
     * "23 jam 59 menit lagi" membeku di layar sampai halamannya dimuat ulang.
     * Orang yang membuka tautan ini besok paginya tetap membaca 23 jam —
     * padahal kursinya sudah dilepas.
     *
     * Detiknya SELALU ikut ditampilkan, bahkan saat sisanya masih 23 jam.
     * Tanpa itu angkanya hanya berubah sekali semenit dan tidak ada tanda
     * apa pun di layar bahwa batas waktunya memang sedang berjalan.
     */
    document.addEventListener('DOMContentLoaded', function () {
        var kotak = document.querySelector('[data-mis-mundur]');

        if (!kotak) { return; }

        var batas = new Date(kotak.getAttribute('data-mis-mundur')).getTime();

        // Tanggal yang tidak terbaca dibiarkan apa adanya: kalimat dari
        // peladen masih benar, dan menggantinya dengan "NaN" jauh lebih buruk.
        if (isNaN(batas)) { return; }

        var sudahMuatUlang = false;

        var sebut = function (angka, satuan) {
            return angka + ' ' + satuan;
        };

        var gambar = function () {
            var sisa = Math.floor((batas - Date.now()) / 1000);

            if (sisa <= 0) {
                kotak.textContent = 'waktunya sudah habis';

                /*
                 * Dimuat ulang SEKALI, bukan berulang: peladen yang menentukan
                 * status sebenarnya, dan halaman yang terus memuat ulang
                 * sendiri tidak bisa dibaca siapa pun.
                 */
                if (!sudahMuatUlang) {
                    sudahMuatUlang = true;
                    setTimeout(function () { location.reload(); }, 1500);
                }

                return;
            }

            var jam = Math.floor(sisa / 3600);
            var menit = Math.floor((sisa % 3600) / 60);
            var detik = sisa % 60;

            var bagian = [];

            if (jam > 0) { bagian.push(sebut(jam, 'jam')); }
            if (jam > 0 || menit > 0) { bagian.push(sebut(menit, 'menit')); }

            bagian.push(sebut(detik, 'detik'));

            kotak.textContent = bagian.join(' ') + ' lagi';
        };

        gambar();
        setInterval(gambar, 1000);
    });
</script>

<script>
    /*
     * Nama berkas yang dipilih ditulis di labelnya.
     *
     * Isian aslinya tidak terlihat, jadi tanpa ini tidak ada satu pun tanda di
     * layar bahwa berkasnya sudah terpilih — dan orang menekan "Pilih foto"
     * berkali-kali, atau mengira unggahannya gagal padahal belum dikirim.
     */
    document.addEventListener('DOMContentLoaded', function () {
        var kotak = document.getElementById('sta-bukti');
        var nama = document.getElementById('sta-bukti-nama');

        if (!kotak || !nama) { return; }

        var semula = nama.textContent;

        kotak.addEventListener('change', function () {
            nama.textContent = kotak.files && kotak.files.length
                ? kotak.files[0].name
                : semula;
        });
    });
</script>
@stop
