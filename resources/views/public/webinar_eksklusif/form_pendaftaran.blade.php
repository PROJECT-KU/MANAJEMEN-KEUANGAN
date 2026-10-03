@extends('public.layout.header')

@section('title')
Daftar {{ $sesi->nama }} | Rumah Scopus
@stop

@section('konten')
{{--
    Borang pendaftaran Webinar Eksklusif.

    Semua yang tampil di sini datang dari angkatannya — judul, tanggal, jam,
    platform, pemateri, flyer, harga, dan sisa kuota. Tidak ada satu pun yang
    diketik di berkas ini, supaya mengganti sesi cukup dilakukan sekali di
    layar Angkatan Layanan.

    Susunannya dua kolom di layar lebar: kiri yang membuat orang MAU (flyer,
    pemateri, yang didapat), kanan yang membuat orang BISA (isian). Di ponsel
    keduanya bertumpuk, dan ringkasan harganya menempel di bawah layar supaya
    tombol daftarnya tidak pernah hilang dari pandangan.
--}}
<section class="ses-latar">
    <div class="container ses-wadah">

        @if (session('error'))
            <div class="ses-galat" role="alert">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @php($sisa = $sesi->total_kuota === null ? null : (int) $sesi->sisa_kuota)
        @php($harga = (int) $sesi->biaya)

        <div class="ses-kisi">

            {{-- ------------------------------------------------ kiri: isi --}}
            <div class="ses-kiri">

                <div class="ses-lencana-baris">
                    <span class="ses-lencana ses-lencana-jingga">
                        <i class="fas fa-bolt" aria-hidden="true"></i> {{ $sesi->platform ?: 'Online' }}
                    </span>
                    @if ($sisa !== null && $sisa > 0 && $sisa <= 10)
                        {{-- Hanya muncul kalau memang tinggal sedikit. Lencana
                             "terbatas" yang selalu ada berhenti dipercaya. --}}
                        <span class="ses-lencana ses-lencana-merah">
                            <i class="fas fa-fire" aria-hidden="true"></i> Tinggal {{ $sisa }} kursi
                        </span>
                    @endif
                </div>

                <h1 class="ses-judul">{{ $sesi->nama }}</h1>

                {{-- Deskripsi otomatis SENGAJA tidak ditampilkan di sini.

                     Cetakannya dirakit untuk WhatsApp dan halaman daftar: ia
                     mengulang judul, tanggal, jam, platform, pemateri, dan
                     daftar fasilitas — semuanya sudah tampil terstruktur di
                     halaman ini, jadi yang terbaca adalah hal yang sama dua
                     kali dalam dua bentuk. Yang dipakai di sini kegiatan dan
                     fasilitasnya langsung dari tarif induk. --}}
                {{--
                    Yang DIBAHAS dan yang DIBAWA PULANG disandingkan, dan
                    keduanya naik ke atas tepat di bawah judul.

                    Dulu daftar manfaatnya berada paling bawah di kolom ini —
                    sesudah fakta, pemateri, dan flyer setinggi 758 px. Jadi
                    alasan terkuat untuk mendaftar justru yang paling jauh
                    dari pandangan, dan paling besar kemungkinannya tidak
                    terbaca sama sekali.

                    Disandingkan, bukan ditumpuk: keduanya daftar pendek, dan
                    bertumpuk mereka mendorong sisa halaman ke bawah tanpa
                    memakai lebar yang sudah tersedia.
                --}}
                @if (($tarif && $tarif->daftar_kegiatan) || ($tarif && $tarif->daftar_fasilitas))
                    <div class="ses-duo">
                        @if ($tarif->daftar_kegiatan)
                            <div class="ses-bahas">
                                <h2 class="ses-bahas-judul">
                                    <span class="ses-keping ses-keping-jingga" aria-hidden="true"><i class="fas fa-tasks"></i></span>
                                    Yang dibahas di sesi ini
                                </h2>
                                <ol>
                                    @foreach ($tarif->daftar_kegiatan as $k)
                                        <li>{{ $k }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        @endif

                        @if ($tarif->daftar_fasilitas)
                            <div class="ses-dapat">
                                <h2 class="ses-dapat-judul">
                                    <span class="ses-keping ses-keping-hijau" aria-hidden="true"><i class="fas fa-gift"></i></span>
                                    Yang Anda bawa pulang
                                </h2>
                                <ul>
                                    @foreach ($tarif->daftar_fasilitas as $f)
                                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> <span>{{ $f }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="ses-fakta">
                    <div class="ses-fakta-item">
                        <span class="ses-fakta-label"><i class="fas fa-calendar-alt" aria-hidden="true"></i> Tanggal</span>
                        <strong>{{ \App\Support\RentangTanggal::tulis(
                            $sesi->mulai ? \Carbon\Carbon::parse($sesi->mulai) : null,
                            $sesi->selesai ? \Carbon\Carbon::parse($sesi->selesai) : null
                        ) ?: 'Menyusul' }}</strong>
                    </div>

                    @if ($sesi->jam)
                        <div class="ses-fakta-item">
                            <span class="ses-fakta-label"><i class="fas fa-clock" aria-hidden="true"></i> Jam</span>
                            <strong>{{ $sesi->jam }}</strong>
                        </div>
                    @endif

                    <div class="ses-fakta-item">
                        <span class="ses-fakta-label"><i class="fas fa-video" aria-hidden="true"></i> Lewat</span>
                        <strong>{{ $sesi->platform ?: 'Daring' }}</strong>
                    </div>
                </div>

                @if ($sesi->pemateri)
                    <div class="ses-pemateri">
                        @if ($sesi->alamat_pemateri)
                            @php($ukuranPemateri = $sesi->ukuran_pemateri)
                            <img src="{{ $sesi->alamat_pemateri }}" alt="" class="ses-pemateri-foto"
                                @if ($ukuranPemateri) width="{{ $ukuranPemateri['lebar'] }}" height="{{ $ukuranPemateri['tinggi'] }}" @endif
                                loading="lazy" decoding="async">
                        @else
                            {{-- Huruf awal namanya, bukan gambar cadangan: ikon
                                 orang abu-abu membuat pematerinya terasa belum
                                 ditentukan, padahal namanya justru ada. --}}
                            <span class="ses-pemateri-huruf" aria-hidden="true">
                                {{ mb_strtoupper(mb_substr($sesi->pemateri, 0, 1)) }}
                            </span>
                        @endif
                        <span class="ses-pemateri-teks">
                            <small>Dibawakan oleh</small>
                            <strong>{{ $sesi->pemateri }}</strong>
                            @if ($sesi->pemateri_jabatan)
                                <small>{{ $sesi->pemateri_jabatan }}</small>
                            @endif
                        </span>
                    </div>
                @endif

                @if ($sesi->alamat_sampul)
                    @php($ukuranFlyer = $sesi->ukuran_sampul)
                    {{--
                        width/height DIPASANG supaya ruangnya sudah dipesan
                        sebelum gambarnya tiba. Tanpa itu flyernya setinggi nol
                        dulu, lalu seluruh isi di bawahnya terdorong sekaligus
                        saat berkasnya termuat — terukur: halaman yang baru
                        digulir ke pesan galat terdorong lagi sampai pesannya
                        keluar layar.

                        Harus disertai height:auto di CSS, kalau tidak tinggi
                        asli dari atribut ini menang atas lebar 100%.
                    --}}
                    <img src="{{ $sesi->alamat_sampul }}" alt="Flyer {{ $sesi->nama }}"
                        @if ($ukuranFlyer) width="{{ $ukuranFlyer['lebar'] }}" height="{{ $ukuranFlyer['tinggi'] }}" @endif
                        class="ses-flyer" loading="lazy" decoding="async">
                @endif

            </div>

            {{-- --------------------------------------------- kanan: isian --}}
            <div class="ses-kanan">
                <form method="POST" action="{{ route('public.webinareksklusif.store') }}" class="ses-kartu"
                    id="ses-borang">
                    @csrf
                    <input type="hidden" name="kategori_id" value="{{ $sesi->id }}">

                    <h2 class="ses-kartu-judul">Amankan kursi Anda</h2>
                    <p class="ses-kartu-sub">Isinya lima, tidak sampai satu menit.</p>

                    {{--
                        Ringkasan galat di KEPALA kartu, bukan hanya pesan per
                        isian. Dengan pesan per isian saja, yang salah bisa
                        berada di bawah lipatan layar dan orangnya cuma melihat
                        borang yang sama tanpa tahu apa yang kurang.

                        role="alert" supaya pembaca layar membacakannya begitu
                        halamannya dimuat ulang.
                    --}}
                    @if ($errors->any())
                        <div class="ses-ringkas-galat" id="ses-ringkas-galat" role="alert" tabindex="-1">
                            <p class="ses-ringkas-judul">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                Ada {{ $errors->count() }} isian yang perlu diperbaiki
                            </p>
                            <ul>
                                @foreach ($errors->all() as $pesan)
                                    <li>{{ $pesan }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="ses-isian">
                        <label for="ses-nama">Nama lengkap <span aria-hidden="true">*</span></label>
                        <input type="text" id="ses-nama" name="nama" required maxlength="120"
                            value="{{ old('nama', $isiAwal['nama'] ?? '') }}" placeholder="Nama beserta gelar, untuk sertifikat"
                            autocomplete="name"
                            @error('nama') aria-invalid="true" aria-describedby="ses-nama-salah" @enderror>
                        @error('nama') <p class="ses-salah" id="ses-nama-salah">{{ $message }}</p> @enderror
                    </div>

                    <div class="ses-isian">
                        <label for="ses-email">Email aktif <span aria-hidden="true">*</span></label>
                        <input type="email" id="ses-email" name="email" required maxlength="120"
                            value="{{ old('email', $isiAwal['email'] ?? '') }}" placeholder="nama@email.com" autocomplete="email"
                            aria-describedby="ses-email-bantu @error('email') ses-email-salah @enderror"
                            @error('email') aria-invalid="true" @enderror>
                        {{--
                            Alasannya disebut, bukan cuma "wajib". Orang lebih
                            rela memberikan emailnya kalau tahu untuk apa.

                            Kalimatnya menyebut yang BENAR-BENAR dikirim.
                            Sebelumnya tertulis "Tautan masuk dan sertifikat
                            dikirim ke sini", padahal tidak ada kolom tautan
                            Zoom di angkatan dan tidak ada penerbitan
                            sertifikat — janji yang tidak bisa ditepati sistem,
                            dan yang menanggung akibatnya panitia saat orang
                            menunggu sesuatu yang tidak akan datang.
                        --}}
                        <p class="ses-bantu" id="ses-email-bantu">Bukti pendaftaran dikirim ke sini.</p>
                        @error('email') <p class="ses-salah" id="ses-email-salah">{{ $message }}</p> @enderror
                    </div>

                    <div class="ses-isian">
                        <label for="ses-telp">Nomor WhatsApp <span aria-hidden="true">*</span></label>
                        <input type="tel" id="ses-telp" name="telp" required maxlength="30"
                            value="{{ old('telp', $isiAwal['telp'] ?? '') }}" placeholder="0812 3456 7890" autocomplete="tel"
                            inputmode="numeric"
                            aria-describedby="ses-telp-bantu @error('telp') ses-telp-salah @enderror"
                            @error('telp') aria-invalid="true" @enderror>
                        <p class="ses-bantu" id="ses-telp-bantu">Dipakai menambahkan Anda ke grup peserta.</p>

                        {{-- Kabar hasil pencarian. aria-live supaya pembaca
                             layar ikut mendengar borangnya terisi sendiri —
                             tanpa itu, isian yang berubah diam-diam justru
                             membingungkan. --}}
                        <p class="ses-isi-otomatis" id="ses-kabar-isi" role="status" aria-live="polite" hidden></p>
                        @error('telp') <p class="ses-salah" id="ses-telp-salah">{{ $message }}</p> @enderror
                    </div>

                    <div class="ses-isian">
                        <label for="ses-affiliasi">Asal instansi</label>
                        <input type="text" id="ses-affiliasi" name="affiliasi" maxlength="160"
                            value="{{ old('affiliasi', $isiAwal['affiliasi'] ?? '') }}" placeholder="Universitas / lembaga — boleh dikosongkan"
                            autocomplete="organization"
                            @error('affiliasi') aria-invalid="true" aria-describedby="ses-affiliasi-salah" @enderror>
                        @error('affiliasi') <p class="ses-salah" id="ses-affiliasi-salah">{{ $message }}</p> @enderror
                    </div>

                    <div class="ses-isian">
                        <label for="ses-jumlah">Jumlah peserta</label>
                        {{-- Tombol tambah-kurang, bukan hanya kotak angka: di
                             ponsel papan ketik angka menutupi separuh layar
                             hanya untuk mengubah 1 jadi 2. --}}
                        <div class="ses-hitung">
                            <button type="button" class="ses-hitung-tombol" data-ubah="-1"
                                aria-label="Kurangi jumlah peserta">&minus;</button>
                            <input type="number" id="ses-jumlah" name="jumlah_pendaftar" min="1"
                                max="{{ $sisa !== null && $sisa > 0 ? min(50, $sisa) : 50 }}"
                                value="{{ old('jumlah_pendaftar', 1) }}" inputmode="numeric"
                                data-harga="{{ $harga }}">
                            <button type="button" class="ses-hitung-tombol" data-ubah="1"
                                aria-label="Tambah jumlah peserta">+</button>
                        </div>
                        @if ($sisa !== null && $sisa > 0)
                            <p class="ses-bantu">Tersisa {{ $sisa }} kursi.</p>
                        @endif
                        @error('jumlah_pendaftar') <p class="ses-salah">{{ $message }}</p> @enderror
                    </div>

                    {{-- aria-live: angkanya berubah saat tombol +/- ditekan,
                         dan tanpa ini pembaca layar tidak mengumumkan apa pun —
                         orangnya menambah peserta tanpa tahu harganya ikut
                         naik. "polite" supaya tidak memotong bacaan berjalan. --}}
                    <div class="ses-total" aria-live="polite" aria-atomic="true">
                        <span>Total bayar</span>
                        {{-- Nilai awalnya dirender peladen, bukan dihitung
                             JavaScript saat halaman dibuka: tanpa itu, angkanya
                             sempat kosong sepersekian detik pertama. --}}
                        <strong id="ses-total-nilai"
                            data-harga="{{ $harga }}">Rp {{ number_format($harga, 0, ',', '.') }}</strong>
                    </div>
                    <p class="ses-total-rincian" id="ses-total-rincian">
                        Rp {{ number_format($harga, 0, ',', '.') }} × 1 peserta
                    </p>

                    {{--
                        Persetujuan diminta DI SINI, tepat sebelum tombolnya —
                        bukan di bawah tombol, tempat orang tidak lagi membaca.
                        Kalimatnya menyebut untuk apa datanya dipakai, sebab
                        "saya setuju dengan ketentuan" tanpa isi tidak
                        memberitahu siapa pun apa pun.
                    --}}
                    <div class="ses-setuju">
                        <input type="checkbox" id="ses-setuju" name="setuju" value="1"
                            {{ old('setuju') ? 'checked' : '' }}
                            @error('setuju') aria-invalid="true" aria-describedby="ses-setuju-salah" @enderror>
                        <label for="ses-setuju">
                            Saya setuju nama, email, dan nomor WhatsApp saya dipakai untuk
                            mengirim tautan masuk, sertifikat, dan menambahkan saya ke grup peserta.
                        </label>
                    </div>
                    @error('setuju') <p class="ses-salah" id="ses-setuju-salah">{{ $message }}</p> @enderror

                    @if ($sisa !== null && $sisa < 1)
                        <p class="ses-penuh"><i class="fas fa-times-circle" aria-hidden="true"></i>
                            Kuotanya sudah penuh. Hubungi panitia untuk sesi berikutnya.</p>
                    @else
                        <button type="submit" class="ses-tombol" id="ses-kirim">
                            {{-- "dan", bukan "&": di dalam {{ }} entitas HTML
                                 ikut dilolosi, jadi &amp; terbaca apa adanya
                                 di layar sebagai "&amp;". --}}
                            <span>{{ $bayarDaring ? 'Daftar dan bayar sekarang' : 'Daftar sekarang' }}</span>
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </button>
                    @endif

                    <p class="ses-tenang">
                        <i class="fas fa-lock" aria-hidden="true"></i>
                        @if ($bayarDaring)
                            Pembayaran diproses DOKU. Data Anda tidak dibagikan ke pihak lain.
                        @else
                            Setelah mendaftar, Anda akan diberi cara pembayarannya.
                        @endif
                    </p>
                </form>
            </div>
        </div>
    </div>

    {{-- Ringkasan menempel di bawah layar, KHUSUS ponsel. Borangnya lebih
         panjang dari satu layar, dan tanpa ini harga dan tombolnya hilang dari
         pandangan tepat saat orang sedang menimbang. --}}
    @if ($sisa === null || $sisa > 0)
        <div class="ses-tempel" id="ses-tempel" hidden>
            <div>
                <small>Total</small>
                <strong id="ses-tempel-nilai">Rp {{ number_format($harga, 0, ',', '.') }}</strong>
            </div>
            <button type="submit" form="ses-borang" class="ses-tombol ses-tombol-kecil">
                Daftar <i class="fas fa-arrow-right" aria-hidden="true"></i>
            </button>
        </div>
    @endif
</section>

<style>
    /* Palet diambil dari halaman landing Webinar Eksklusif supaya orang yang
       datang dari sana merasa masih di tempat yang sama. */
    .ses-latar {
        --navy: #0f2b5b;
        --jingga: #ff6a00;
        --tinta: #1e293b;
        --tinta-2: #64748b;
        --garis: #e2e8f0;

        background: #f8fafc;
        background-image:
            radial-gradient(circle at 85% -5%, rgba(255, 106, 0, .14), transparent 45%),
            radial-gradient(circle at -10% 20%, rgba(15, 43, 91, .12), transparent 45%);
        padding: 110px 0 90px;
        min-height: 100vh;
        font-family: 'Poppins', 'Inter', system-ui, sans-serif;
        color: var(--tinta);

        /*
         * clip, BUKAN hidden — dan ini yang menghidupkan kartu isian lengket.
         *
         * Stylesheet bersama memasang `section { overflow: hidden }`. hidden
         * membuat unsurnya jadi wadah gulir, dan wadah gulir terdekat itulah
         * yang mengurung position:sticky. Akibatnya kartu isian di kolom
         * kanan ikut tergulung keluar layar padahal ruang jelajahnya ada
         * 646 px — terukur puncaknya di -890 px saat halaman digulir 1100 px.
         *
         * clip memangkas persis seperti hidden tetapi TIDAK membuat wadah
         * gulir, jadi pemangkasannya tetap ada dan lengketnya hidup. Peramban
         * lama yang belum mengenal clip mengabaikan baris ini dan kembali ke
         * hidden — tanpa gerak lengket, tetapi tidak ada yang rusak.
         */
        overflow: clip;
    }

    .ses-wadah { max-width: 1120px; }

    .ses-galat {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 24px;
        padding: 14px 18px;
        border-radius: 14px;
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #991b1b;
        font-size: .9rem;
    }

    /* Dua kolom; kanan lebih sempit karena isinya isian, bukan bacaan. */
    .ses-kisi {
        display: grid;
        grid-template-columns: 1fr;
        gap: 28px;
        align-items: start;
    }

    @media (min-width: 992px) {
        .ses-kisi { grid-template-columns: 1.15fr .85fr; gap: 40px; }

        /* Kartu isian ikut turun saat halaman digulung, jadi harga dan
           tombolnya tetap terlihat sepanjang orang membaca kolom kiri. */
        .ses-kanan { position: sticky; top: 100px; }
    }

    .ses-lencana-baris { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }

    .ses-lencana {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 999px;
        font-size: .74rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #fff;
    }

    /* margin-left: 0 !important — style.css Stisla memaksa margin pada .fas
       di dalam <a>/<span> tertentu dengan bobot (0,4,1). */
    .ses-lencana > .fas { margin: 0 !important; font-size: .78rem; }

    .ses-lencana-jingga { background: linear-gradient(135deg, #ff8c00, #e65c00); }
    .ses-lencana-merah { background: linear-gradient(135deg, #ef4444, #b91c1c); }

    .ses-judul {
        margin: 0 0 14px;
        font-size: clamp(1.75rem, 4.5vw, 2.6rem);
        font-weight: 800;
        line-height: 1.18;
        color: var(--navy);
    }

    /*
     * Sepasang kartu sejajar. Petaknya 1fr 1fr supaya lebarnya sama rata,
     * dan keduanya meregang setinggi yang tertinggi (bawaan grid), jadi
     * dasarnya segaris walau jumlah butirnya berbeda — di sesi ini 3 lawan 4.
     *
     * Satu kolom sampai 576 px: di bawah itu dua kolom membuat tiap barisnya
     * tinggal sekitar 20 huruf, dan daftarnya jadi lebih tinggi daripada
     * kalau ditumpuk.
     */
    .ses-duo {
        display: grid;
        grid-template-columns: 1fr;
        gap: 14px;
        margin-bottom: 22px;
    }

    @media (min-width: 576px) {
        .ses-duo { grid-template-columns: 1fr 1fr; }
    }

    .ses-bahas {
        margin: 0;
        padding: 18px 22px;
        border-radius: 16px;
        border: 1px solid var(--garis);
        border-left: 4px solid var(--jingga);
        background: #fff;
    }

    .ses-bahas-judul { margin: 0 0 8px; font-size: .95rem; font-weight: 800; color: var(--navy); }

    .ses-bahas ol { margin: 0; padding-left: 20px; }
    .ses-bahas li { padding: 4px 0; font-size: .93rem; line-height: 1.65; color: var(--tinta); }

    /*
     * Flex, bukan grid auto-fit.
     *
     * Dengan grid, tiga kotak di layar yang cuma muat dua kolom menyisakan
     * kotak ketiga selebar separuh di pojok kiri baris kedua - terukur 177 px
     * di layar 390 px, dengan 189 px kosong di sebelahnya. Petak grid lebarnya
     * sudah ditetapkan, jadi kotak yatim itu tidak bisa melebar.
     *
     * Dengan flex-grow, kotak yang tersisa sendirian MEMANJANG mengisi
     * barisnya. Menyesuaikan sendiri di tiap lebar dan tiap jumlah kotak -
     * jam boleh tidak diisi, dan susunannya tetap rapi tanpa media query.
     */
    .ses-fakta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 22px;
    }

    .ses-fakta-item {
        flex: 1 1 150px;
        padding: 14px 16px;
        border-radius: 14px;
        border: 1px solid var(--garis);
        background: rgba(255, 255, 255, .8);
    }

    .ses-fakta-label {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 4px;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--tinta-2);
    }

    .ses-fakta-label > .fas { margin: 0 !important; color: var(--jingga); font-size: .8rem; }
    .ses-fakta-item strong { font-size: .98rem; color: var(--navy); }

    .ses-pemateri {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 22px;
        padding: 14px 16px;
        border-radius: 16px;
        border: 1px solid var(--garis);
        background: #fff;
    }

    .ses-pemateri-foto,
    .ses-pemateri-huruf {
        flex: 0 0 auto;
        width: 62px;
        height: 62px;
        border-radius: 16px;
        object-fit: cover;
    }

    .ses-pemateri-huruf {
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: #fff;
        font-size: 1.5rem;
        font-weight: 800;
    }

    /* min-width: 0 supaya nama panjang boleh patah; tanpa itu item flex
       menolak menyusut dan kartunya meluber di layar sempit. */
    .ses-pemateri-teks { flex: 1 1 0; min-width: 0; display: flex; flex-direction: column; }
    .ses-pemateri-teks small { font-size: .76rem; color: var(--tinta-2); }
    .ses-pemateri-teks strong { font-size: 1rem; color: var(--navy); }

    /*
     * Batas 420 px hanya berlaku sampai tablet. Di layar lebar kolom kirinya
     * 607 px, jadi batas itu meninggalkan 187 px kosong di sebelah kanan
     * flyer - padahal kartu di atas dan di bawahnya selebar kolom penuh, jadi
     * yang kosong itu terbaca seperti ada yang gagal dimuat.
     */
    .ses-flyer {
        display: block;
        width: 100%;
        /* WAJIB sejak atribut height dipasang di markup: tanpa ini tinggi
           asli gambarnya menang atas lebar 100% dan flyernya melar. */
        height: auto;
        max-width: 420px;
        margin-bottom: 22px;
        border-radius: 18px;
        border: 1px solid var(--garis);
        box-shadow: 0 18px 40px -24px rgba(15, 43, 91, .5);
    }

    /*
     * DITULIS SESUDAH aturan di atas, bukan di media query .ses-kisi.
     *
     * Bobot pemilihnya sama persis (satu kelas), jadi yang menang yang
     * ditulis belakangan - bukan yang di dalam media query. Ditaruh di atas,
     * aturan ini kalah tanpa gejala apa pun: flyernya tetap 420 px dan
     * tampak seperti media query-nya tidak pernah cocok.
     */
    @media (min-width: 992px) {
        .ses-flyer { max-width: 100%; }
    }

    .ses-dapat {
        padding: 20px 22px;
        border-radius: 18px;
        border: 1px solid var(--garis);
        background: #fff;
    }

    .ses-dapat-judul {
        margin: 0 0 12px;
        font-size: .95rem;
        font-weight: 800;
        color: var(--navy);
    }

    .ses-dapat ul { list-style: none; margin: 0; padding: 0; }

    .ses-dapat li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 7px 0;
        font-size: .92rem;
        color: var(--tinta);
    }

    .ses-dapat li > .fas { margin: 0 !important; margin-top: 3px !important; color: #10b981; }

    /* ---------------------------------------------------------- isian */

    .ses-kartu {
        padding: 26px 24px;
        border-radius: 22px;
        border: 1px solid var(--garis);
        background: #fff;
        box-shadow: 0 24px 60px -34px rgba(15, 43, 91, .55);
    }

    .ses-kartu-judul { margin: 0 0 4px; font-size: 1.22rem; font-weight: 800; color: var(--navy); }
    .ses-kartu-sub { margin: 0 0 20px; font-size: .86rem; color: var(--tinta-2); }

    .ses-isian { margin-bottom: 16px; }

    .ses-isian label {
        display: block;
        margin-bottom: 6px;
        font-size: .82rem;
        font-weight: 700;
        color: var(--tinta);
    }

    /* Bintang merah hanya pada yang validatornya memang required, supaya
       tandanya tetap berarti. */
    .ses-isian label span { color: #dc2626; }

    .ses-isian input {
        width: 100%;
        height: 48px;
        padding: 0 14px;
        border: 1px solid var(--garis);
        border-radius: 12px;
        background: #f8fafc;
        font-family: inherit;
        font-size: .95rem;
        color: var(--tinta);
        transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .ses-isian input:focus {
        outline: none;
        background: #fff;
        border-color: var(--jingga);
        box-shadow: 0 0 0 4px rgba(255, 106, 0, .14);
    }

    .ses-bantu { margin: 6px 0 0; font-size: .78rem; color: var(--tinta-2); }
    .ses-salah { margin: 6px 0 0; font-size: .78rem; color: #dc2626; }

    .ses-hitung { display: flex; align-items: center; gap: 8px; }

    .ses-hitung-tombol {
        flex: 0 0 auto;
        /* 48px penuh: ini kendali yang paling sering ditekan di ponsel. */
        width: 48px;
        height: 48px;
        border: 1px solid var(--garis);
        border-radius: 12px;
        background: #f1f5f9;
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1;
        color: var(--navy);
        cursor: pointer;
    }

    .ses-hitung-tombol:hover { background: #e2e8f0; }
    .ses-hitung input { text-align: center; }

    .ses-total {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px dashed var(--garis);
        font-size: .9rem;
        color: var(--tinta-2);
    }

    .ses-total strong { font-size: 1.6rem; font-weight: 800; color: var(--navy); }
    .ses-total-rincian { margin: 4px 0 0; font-size: .78rem; color: var(--tinta-2); }

    .ses-tombol {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        min-height: 54px;
        margin-top: 18px;
        padding: 0 20px;
        border: 0;
        border-radius: 14px;
        background: linear-gradient(135deg, #ff8c00, #e65c00);
        color: #fff;
        font-family: inherit;
        font-size: 1rem;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 16px 30px -14px rgba(255, 106, 0, .7);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .ses-tombol:hover { transform: translateY(-2px); box-shadow: 0 20px 36px -14px rgba(255, 106, 0, .8); }
    .ses-tombol[disabled] { opacity: .65; cursor: progress; transform: none; }
    .ses-tombol > .fas { margin: 0 !important; }

    .ses-penuh {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 18px 0 0;
        padding: 14px 16px;
        border-radius: 12px;
        background: #fef2f2;
        color: #991b1b;
        font-size: .88rem;
        font-weight: 600;
    }

    .ses-penuh > .fas { margin: 0 !important; }

    .ses-tenang {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin: 14px 0 0;
        font-size: .76rem;
        color: var(--tinta-2);
    }

    .ses-tenang > .fas { margin: 0 !important; margin-top: 2px !important; }

    /* ------------------------------------------------- ringkasan tempel */

    .ses-tempel {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1040;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
        background: rgba(255, 255, 255, .97);
        border-top: 1px solid var(--garis);
        box-shadow: 0 -10px 30px -18px rgba(15, 43, 91, .5);
    }

    .ses-tempel small { display: block; font-size: .72rem; color: var(--tinta-2); }
    .ses-tempel strong { font-size: 1.2rem; font-weight: 800; color: var(--navy); }

    .ses-tombol-kecil { width: auto; min-height: 46px; margin: 0; font-size: .92rem; }

    @media (min-width: 992px) {
        /* Di layar lebar kartunya sudah ikut menggulung, jadi pita ini cuma
           menutupi isi halaman tanpa menambah apa pun. */
        .ses-tempel { display: none !important; }
    }

    @media (max-width: 991.98px) {
        /*
         * PANAH "KE ATAS" DISINGKIRKAN SELAMA PITANYA TAMPIL.
         *
         * Panah itu milik layout publik bersama dan duduk di pojok kanan
         * bawah - tepat di tempat tombol Daftar berada. Terukur di layar
         * 390 px: panahnya 335-375 px, tombol Daftar 263-374 px, jadi 39 px
         * tombolnya tertutup.
         *
         * Dan bukan cuma tertutup. z-index panahnya 99999 melawan 1040 milik
         * pita, jadi panahnya yang menerima ketukan: menekan sisi kanan
         * "Daftar" justru melompat ke puncak halaman, bukan mendaftar.
         *
         * Disembunyikan, bukan digeser: saat pitanya tampil, tindakan yang
         * dituju sudah ada di layar, dan dua tombol melayang bertumpuk hanya
         * membuat ragu harus menekan yang mana.
         *
         * !important perlu sebab panahnya memakai .d-flex bawaan Bootstrap
         * yang sudah ber-!important.
         */
        body.ses-pita-tampil .back-to-top { display: none !important; }

        /*
         * Ruang supaya pita bawah tidak menutupi isian terakhir.
         *
         * Terukur tinggi pitanya 71 px dari 320 px sampai 991 px - isinya
         * cuma satu baris harga dan satu tombol, jadi tidak ikut tumbuh.
         * Angka 150 px sebelumnya menyisakan 79 px ruang menganga di atas
         * footer yang tidak dipakai apa pun.
         */
        .ses-latar { padding-bottom: 110px; }
    }

    /* ------------------------------------------------- isi otomatis */

    .ses-isi-otomatis {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin: 8px 0 0;
        padding: 9px 11px;
        border-radius: 10px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        font-size: .78rem;
        line-height: 1.55;
        color: #065f46;
    }

    .ses-isi-otomatis > .fas { margin: 0 !important; margin-top: 2px !important; }

    .ses-isi-otomatis button {
        flex: 0 0 auto;
        margin-left: auto;
        padding: 0;
        border: 0;
        background: none;
        font-family: inherit;
        font-size: .78rem;
        font-weight: 700;
        color: #047857;
        text-decoration: underline;
        cursor: pointer;
    }

    /* Isian yang baru saja diisikan ditandai sebentar, supaya terlihat
       MANA saja yang berubah — bukan cuma diberi tahu bahwa ada yang
       berubah. */
    .ses-isian input.ses-baru-diisi {
        border-color: #10b981;
        background: #f0fdf4;
    }

    @media (prefers-reduced-motion: no-preference) {
        .ses-isian input { transition: border-color .2s ease, box-shadow .2s ease, background .2s ease; }
    }

    /* ---------------------------------------------- ringkasan galat */

    .ses-ringkas-galat {
        margin: 0 0 18px;
        padding: 14px 16px;
        border-radius: 12px;
        border: 1px solid #fecaca;
        background: #fef2f2;
    }

    .ses-ringkas-judul {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 8px;
        font-size: .88rem;
        font-weight: 800;
        color: #991b1b;
    }

    .ses-ringkas-judul > .fas { margin: 0 !important; }

    .ses-ringkas-galat ul { margin: 0; padding-left: 20px; }

    .ses-ringkas-galat li {
        font-size: .83rem;
        line-height: 1.6;
        color: #b91c1c;
    }

    /* ------------------------------------------------ kotak persetujuan */

    .ses-setuju {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px dashed var(--garis);
    }

    .ses-setuju input {
        flex: 0 0 auto;
        /* 20px, dan padding sentuhnya diperlebar lewat label di sebelahnya:
           kotak centang bawaan terlalu kecil untuk jempol di ponsel. */
        width: 20px;
        height: 20px;
        margin-top: 1px;
        accent-color: var(--jingga);
        cursor: pointer;
    }

    .ses-setuju label {
        font-size: .8rem;
        line-height: 1.6;
        color: var(--tinta-2);
        cursor: pointer;
    }

    /* ======================================================= RUPA KARTU */
    /*
     * Ditulis SESUDAH aturan kartu di atas, bukan menyunting di tempatnya:
     * beberapa di antaranya menimpa aturan lama yang bobotnya sama, dan di
     * CSS yang menang adalah yang ditulis belakangan.
     */

    /* Keping ikon di kepala kartu — titik tumpu supaya judulnya tidak
       mengambang sebagai teks tebal biasa. */
    .ses-keping {
        flex: 0 0 auto;
        display: grid;
        place-items: center;
        width: 30px;
        height: 30px;
        border-radius: 10px;
        font-size: .82rem;
    }

    .ses-keping > .fas { margin: 0 !important; }

    .ses-keping-jingga {
        background: linear-gradient(135deg, rgba(255, 140, 0, .16), rgba(230, 92, 0, .13));
        color: var(--jingga);
    }

    .ses-keping-hijau {
        background: rgba(16, 185, 129, .13);
        color: #0f9b74;
    }

    .ses-bahas-judul,
    .ses-dapat-judul {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 14px;
        padding-bottom: 12px;
        border-bottom: 1px solid #eef2f7;
        font-size: 1rem;
    }

    /*
     * Tepi kiri jingga diganti pita bergradien di sisi atas kartu.
     * Garis 4 px di kiri membuat kedua kartu tampak tidak sejenis padahal
     * isinya sepasang; pita atas memberi warna tanpa memiringkan salah satu.
     */
    .ses-bahas,
    .ses-dapat {
        position: relative;
        overflow: hidden;
        border-left: 1px solid var(--garis);
        box-shadow: 0 14px 34px -28px rgba(15, 43, 91, .5);

        /*
         * Isinya DISAMAKAN. Aturan lamanya memberi 18px pada kartu kiri dan
         * 20px pada kartu kanan, dan selisih 2px itu membuat kedua judul
         * tidak sebaris walau kartunya sendiri sudah sejajar sempurna —
         * terukur judul kanan 2px lebih rendah. Pada sepasang kartu
         * bersebelahan, meleset 2px terbaca sebagai sesuatu yang salah
         * tanpa orang bisa menunjuk apa.
         */
        padding: 20px 22px;
    }

    /*
     * DAFTARNYA yang mengisi sisa tinggi kartu, bukan kartunya yang
     * menyisakan ruang kosong.
     *
     * Kedua kartu meregang setinggi yang tertinggi (3 butir lawan 4), dan
     * terukur itu meninggalkan 49 px putih di dasar kartu kiri terhadap 21 px
     * di kanan. Ruang kosong di DALAM kartu lebih mencolok daripada jarak
     * biasa, sebab ia punya tepi dan bayangan yang mengurungnya.
     *
     * Dua jalan lain sudah diukur dan sama-sama memindahkan 28 px itu, bukan
     * menghilangkannya: membiarkan kartunya memeluk isinya membuat tingginya
     * berbeda 28 px, dan itu sama kelihatannya pada sepasang kartu
     * bersebelahan. Dengan cara ini keduanya berdasar rata DAN berisi rata;
     * yang berbeda tinggal jarak antarbutir, dan itu yang paling tidak
     * tertangkap mata.
     */
    .ses-bahas,
    .ses-dapat {
        display: flex;
        flex-direction: column;
    }

    .ses-bahas ol,
    .ses-dapat ul {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .ses-bahas::before,
    .ses-dapat::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
    }

    .ses-bahas::before { background: linear-gradient(90deg, #ff8c00, #e65c00); }
    .ses-dapat::before { background: linear-gradient(90deg, #10b981, #0f9b74); }

    /* Nomor urut jadi keping bulat, bukan angka bawaan <ol> yang menempel
       di tepi dan tidak sejajar dengan centang di kartu sebelahnya. */
    .ses-bahas ol {
        list-style: none;
        margin: 0;
        padding: 0;
        counter-reset: ses-urut;
    }

    .ses-bahas li {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 7px 0;
        counter-increment: ses-urut;
    }

    .ses-bahas li::before {
        content: counter(ses-urut);
        flex: 0 0 auto;
        display: grid;
        place-items: center;
        width: 23px;
        height: 23px;
        margin-top: 1px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ff8c00, #e65c00);
        color: #fff;
        font-size: .72rem;
        font-weight: 800;
        box-shadow: 0 5px 12px -6px rgba(230, 92, 0, .9);
    }

    /* Centang diberi alas bulat supaya setara dengan nomor di sebelahnya. */
    .ses-dapat li { gap: 11px; padding: 7px 0; }

    .ses-dapat li > .fas {
        flex: 0 0 auto;
        display: grid;
        place-items: center;
        width: 23px;
        height: 23px;
        margin: 1px 0 0 !important;
        border-radius: 50%;
        background: rgba(16, 185, 129, .13);
        color: #0f9b74;
        font-size: .74rem;
    }

    /* Kotak fakta: ikonnya diberi keping, angkanya dibesarkan. */
    .ses-fakta-item {
        padding: 15px 16px;
        background: #fff;
        box-shadow: 0 12px 30px -28px rgba(15, 43, 91, .5);
    }

    .ses-fakta-label {
        gap: 9px;
        margin-bottom: 7px;
        font-size: .68rem;
    }

    .ses-fakta-label > .fas {
        display: grid;
        place-items: center;
        width: 26px;
        height: 26px;
        border-radius: 9px;
        background: linear-gradient(135deg, rgba(255, 140, 0, .16), rgba(230, 92, 0, .13));
        font-size: .76rem;
    }

    .ses-fakta-item strong { font-size: 1.04rem; font-weight: 800; }

    /* Pemateri: fotonya diberi cincin, dan kartunya diberi bayangan yang
       sama dengan sepasang kartu di atasnya supaya satu keluarga. */
    .ses-pemateri {
        box-shadow: 0 14px 34px -28px rgba(15, 43, 91, .5);
    }

    .ses-pemateri-foto,
    .ses-pemateri-huruf {
        width: 66px;
        height: 66px;
        box-shadow: 0 0 0 3px #fff, 0 0 0 4px rgba(255, 140, 0, .35);
    }

    .ses-pemateri-teks small:first-child {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--jingga);
    }

    .ses-pemateri-teks strong { font-size: 1.05rem; font-weight: 800; }

    /* ===================================================== GERAK HALAMAN */
    /*
     * Tiga aturan yang dipegang di seluruh blok ini:
     *
     * 1. Yang menyembunyikan isi HANYA berlaku setelah skrip memasang kelas
     *    .ses-gerak di <body>. Kalau skripnya gagal dimuat, tidak ada satu
     *    pun isi yang hilang — halaman ini halaman pendaftaran, dan isi yang
     *    tak pernah muncul berarti kursi yang tak pernah terjual.
     *
     * 2. prefers-reduced-motion dihormati penuh di bagian paling bawah.
     *
     * 3. Tidak ada gerak yang menunda orang mengisi borang. Kartu isian
     *    muncul paling dulu dan paling cepat; sisanya menyusul.
     */

    .ses-gerak .ses-muncul {
        opacity: 0;
        transform: translateY(16px);
    }

    .ses-gerak .ses-muncul.tampak {
        opacity: 1;
        transform: none;
        transition: opacity .55s ease, transform .55s cubic-bezier(.22, .61, .36, 1);
        /* Jeda berjenjang, dipasang skrip lewat --urut. */
        transition-delay: calc(var(--urut, 0) * 70ms);
    }

    /* Kotak fakta menyusul satu per satu di dalam barisnya sendiri. */
    .ses-gerak .ses-fakta.tampak .ses-fakta-item {
        animation: ses-naik .5s cubic-bezier(.22, .61, .36, 1) backwards;
        animation-delay: calc(var(--urut, 0) * 80ms);
    }

    @keyframes ses-naik {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: none; }
    }

    /*
     * Pita bawah menyelusup naik saat muncul. Dipakai animation, bukan
     * transition: pitanya disembunyikan lewat atribut hidden (display:none),
     * dan transition tidak pernah jalan dari display:none. Animation jalan
     * tepat saat unsurnya mulai ditampilkan.
     */
    .ses-gerak .ses-tempel:not([hidden]) {
        animation: ses-pita-naik .3s cubic-bezier(.22, .61, .36, 1);
    }

    @keyframes ses-pita-naik {
        from { transform: translateY(100%); }
        to   { transform: none; }
    }

    /*
     * Harga berdenyut sesaat setiap nilainya BERUBAH — bukan terus-menerus.
     * Gerak yang berulang tanpa sebab berhenti diperhatikan; gerak yang
     * menjawab tindakan orang justru memastikan tombol tambah-kurangnya
     * memang bekerja.
     */
    .ses-total strong.ses-berubah,
    .ses-tempel strong.ses-berubah {
        animation: ses-denyut .42s ease;
    }

    @keyframes ses-denyut {
        0%   { transform: none; }
        35%  { transform: scale(1.09); color: var(--jingga); }
        100% { transform: none; }
    }

    .ses-total strong,
    .ses-tempel strong { display: inline-block; }

    /* Panah tombol menyenggol ke depan, menandakan ada langkah lanjutan. */
    .ses-tombol > .fas { transition: transform .25s ease; }
    .ses-tombol:hover > .fas { transform: translateX(4px); }

    /*
     * Lencana sisa kursi berdenyut pelan — HANYA muncul kalau kursinya
     * memang tinggal sedikit, jadi denyutnya menyampaikan sesuatu yang benar.
     */
    .ses-gerak .ses-lencana-merah {
        animation: ses-kedip 2.4s ease-in-out infinite;
    }

    @keyframes ses-kedip {
        0%, 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, .34); }
        50%      { box-shadow: 0 0 0 7px rgba(220, 38, 38, 0); }
    }

    /* Kartu di kolom kiri sedikit terangkat saat disentuh tetikus. */
    @media (hover: hover) {
        .ses-bahas, .ses-dapat, .ses-pemateri, .ses-fakta-item, .ses-flyer {
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .ses-bahas:hover, .ses-dapat:hover, .ses-pemateri:hover, .ses-flyer:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 38px -26px rgba(15, 43, 91, .6);
        }
    }

    /* ================================================ GERAK SAAT DISENTUH */
    /*
     * Gerak di atas hanya terjadi sekali, saat halamannya dibuka. Yang di
     * bawah ini hidup terus dan menjawab tindakan orang — itu yang membuat
     * kendalinya terasa menanggapi, bukan gambar diam.
     */

    /*
     * Tombol utama bernapas pelan. Lingkaran cahayanya mengembang lalu
     * memudar tiap 3,2 detik — cukup untuk menarik mata ke tempat yang
     * memang dituju halaman ini, cukup pelan untuk tidak mengganggu orang
     * yang sedang mengisi.
     *
     * Berhenti saat tombolnya ditekan atau sedang dilayani: denyut pada
     * tombol yang sudah dimatikan memberi janji yang tidak ditepati.
     */
    .ses-gerak .ses-tombol:not([disabled]) {
        animation: ses-napas 3.2s ease-in-out infinite;
    }

    @keyframes ses-napas {
        0%, 100% { box-shadow: 0 16px 30px -14px rgba(255, 106, 0, .7), 0 0 0 0 rgba(255, 106, 0, .34); }
        55%      { box-shadow: 0 16px 30px -14px rgba(255, 106, 0, .7), 0 0 0 12px rgba(255, 106, 0, 0); }
    }

    /* Kilau yang menyapu tombol sesekali. overflow:hidden menahannya di
       dalam lengkung tombolnya. */
    .ses-tombol { position: relative; overflow: hidden; }

    .ses-gerak .ses-tombol:not([disabled])::after {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 42%;
        background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .34), transparent);
        animation: ses-kilau 4.6s ease-in-out infinite;
        pointer-events: none;
    }

    @keyframes ses-kilau {
        0%, 62%  { left: -45%; }
        92%, 100% { left: 110%; }
    }

    /*
     * Umpan balik TEKAN. Ini yang paling sering dirasakan dan paling sering
     * tidak ada: tanpa ini, menekan tombol di ponsel tidak memberi tanda apa
     * pun sampai halamannya berganti, dan orang menekannya dua kali.
     */
    .ses-tombol:active:not([disabled]) { transform: scale(.975); }

    .ses-hitung-tombol {
        transition: background .18s ease, border-color .18s ease,
                    transform .12s ease, color .18s ease;
    }

    .ses-hitung-tombol:hover {
        border-color: var(--jingga);
        color: var(--jingga);
    }

    .ses-hitung-tombol:active {
        transform: scale(.9);
        background: rgba(255, 106, 0, .14);
        border-color: var(--jingga);
        color: var(--jingga);
    }

    /* Jejak papan ketik dibuat terlihat — :focus-visible, bukan :focus,
       supaya cincinnya tidak muncul saat ditekan tetikus. */
    .ses-tombol:focus-visible,
    .ses-hitung-tombol:focus-visible {
        outline: 3px solid rgba(255, 106, 0, .45);
        outline-offset: 3px;
    }

    /*
     * Label ikut menyala saat isiannya sedang diisi. Di borang sepanjang
     * ini, penanda baris mana yang sedang aktif menghemat satu lirikan tiap
     * kali orang berpindah isian.
     */
    .ses-isian label { transition: color .18s ease; }
    .ses-isian:focus-within label { color: var(--jingga); }

    /* Keping ikon ikut membesar sedikit saat kartunya disentuh. */
    @media (hover: hover) {
        .ses-keping { transition: transform .25s ease; }
        .ses-bahas:hover .ses-keping,
        .ses-dapat:hover .ses-keping { transform: scale(1.1) rotate(-4deg); }
    }

    /*
     * PENGHORMATAN PENUH pada pilihan sistem orangnya.
     *
     * Ditulis paling bawah supaya menang urutan sumber terhadap semua aturan
     * di atas, dan menyebut .ses-gerak juga supaya bobotnya tidak kalah.
     */
    @media (prefers-reduced-motion: reduce) {
        .ses-gerak .ses-muncul,
        .ses-gerak .ses-muncul.tampak {
            opacity: 1 !important;
            transform: none !important;
            transition: none !important;
        }

        .ses-gerak .ses-fakta.tampak .ses-fakta-item,
        .ses-gerak .ses-tempel:not([hidden]),
        .ses-gerak .ses-lencana-merah,
        .ses-gerak .ses-tombol:not([disabled]),
        .ses-total strong.ses-berubah,
        .ses-tempel strong.ses-berubah {
            animation: none !important;
        }

        /* Kilau penyapu dibuang sama sekali, bukan cuma dihentikan — kalau
           hanya animasinya yang dimatikan, pita putihnya diam menempel di
           satu sisi tombol dan terlihat seperti cacat gambar. */
        .ses-gerak .ses-tombol::after { display: none !important; }

        .ses-bahas, .ses-dapat, .ses-pemateri, .ses-flyer,
        .ses-tombol, .ses-tombol > .fas, .ses-isian input,
        .ses-hitung-tombol, .ses-keping, .ses-isian label {
            transition: none !important;
        }

        /* Panahnya ikut disebut. Mematikan transisinya saja tidak cukup —
           transform:translateX(4px) tetap berlaku, cuma berpindahnya jadi
           seketika. Terukur masih matrix(1,0,0,1,4,0) sebelum baris ini. */
        .ses-tombol:hover > .fas,
        .ses-tombol:hover, .ses-tombol:active,
        .ses-hitung-tombol:active,
        .ses-bahas:hover .ses-keping, .ses-dapat:hover .ses-keping,
        .ses-bahas:hover, .ses-dapat:hover, .ses-pemateri:hover, .ses-flyer:hover {
            transform: none !important;
        }
    }
</style>

<script>
    (function () {
        'use strict';

        var jumlah = document.getElementById('ses-jumlah');
        var nilai = document.getElementById('ses-total-nilai');
        var rincian = document.getElementById('ses-total-rincian');
        var tempel = document.getElementById('ses-tempel');
        var tempelNilai = document.getElementById('ses-tempel-nilai');
        var borang = document.getElementById('ses-borang');

        if (!jumlah || !nilai) return;

        var harga = parseInt(nilai.dataset.harga, 10) || 0;

        function rupiah(n) {
            // toLocaleString('id-ID'), bukan perakit sendiri: pemisah ribuan
            // Indonesia memakai titik, dan menulisnya sendiri berarti satu
            // aturan lagi yang harus dijaga.
            return 'Rp ' + n.toLocaleString('id-ID');
        }

        function batas() {
            return parseInt(jumlah.getAttribute('max'), 10) || 50;
        }

        function hitung() {
            var n = parseInt(jumlah.value, 10);

            if (isNaN(n) || n < 1) n = 1;
            if (n > batas()) n = batas();

            jumlah.value = n;

            var total = harga * n;
            var baru = rupiah(total);

            /*
             * Denyutnya dipasang HANYA kalau angkanya berubah. hitung()
             * dipanggil juga saat halaman dibuka dan tiap kali kotaknya
             * disentuh, jadi tanpa penjagaan ini angkanya berdenyut tanpa
             * ada yang berubah — dan gerak tanpa sebab berhenti diperhatikan.
             */
            var berubah = nilai.textContent !== baru;

            nilai.textContent = baru;
            if (tempelNilai) tempelNilai.textContent = baru;
            if (rincian) rincian.textContent = rupiah(harga) + ' × ' + n + ' peserta';

            if (berubah) {
                [nilai, tempelNilai].forEach(function (e) {
                    if (!e) return;
                    e.classList.remove('ses-berubah');
                    // Dibaca paksa supaya animasinya bisa dijalankan ulang
                    // beberapa kali berturut-turut; tanpa ini menekan "+"
                    // dua kali cepat hanya berdenyut sekali.
                    void e.offsetWidth;
                    e.classList.add('ses-berubah');
                });
            }
        }

        jumlah.addEventListener('input', hitung);
        jumlah.addEventListener('change', hitung);

        Array.prototype.forEach.call(document.querySelectorAll('[data-ubah]'), function (t) {
            t.addEventListener('click', function () {
                jumlah.value = (parseInt(jumlah.value, 10) || 1) + parseInt(t.dataset.ubah, 10);
                hitung();
            });
        });

        hitung();

        /*
         * ISI OTOMATIS DARI NOMOR WHATSAPP.
         *
         * Yang sudah masuk akun tidak lewat sini — borangnya sudah dirender
         * terisi oleh peladen. Ini untuk tamu yang pernah mendaftar.
         *
         * Aturannya dijaga supaya tidak menyebalkan:
         * - hanya saat nomornya LENGKAP (9-15 angka), bukan tiap ketukan
         * - isian yang SUDAH diisi orangnya tidak pernah ditimpa
         * - nomor yang sama tidak dicari dua kali
         * - selalu bisa dibatalkan, dan pembatalan hanya mengosongkan yang
         *   memang diisikan skrip, bukan yang diketik orangnya
         */
        (function () {
            var telp = document.getElementById('ses-telp');
            var kabar = document.getElementById('ses-kabar-isi');

            if (!telp || !kabar || !window.fetch) return;

            var ALAMAT = @json(route('public.webinareksklusif.caripendaftar'));
            var CSRF = document.querySelector('meta[name=csrf-token]');

            var medan = {
                nama: document.getElementById('ses-nama'),
                email: document.getElementById('ses-email'),
                affiliasi: document.getElementById('ses-affiliasi')
            };

            var nomorTerakhir = '';
            var diisiSkrip = [];

            function angkaSaja(n) { return (n || '').replace(/\D+/g, ''); }

            function bersihkanTanda() {
                Object.keys(medan).forEach(function (k) {
                    if (medan[k]) medan[k].classList.remove('ses-baru-diisi');
                });
            }

            function batalkan() {
                /*
                 * HANYA yang diisikan skrip yang dikosongkan. Mengosongkan
                 * semuanya akan membuang nama yang mungkin sudah diketik
                 * orangnya sendiri sebelum nomornya diisi.
                 */
                diisiSkrip.forEach(function (k) {
                    if (medan[k]) medan[k].value = '';
                });

                diisiSkrip = [];
                bersihkanTanda();
                kabar.hidden = true;
                if (medan.nama) medan.nama.focus();
            }

            function tampilkanKabar(jumlah) {
                kabar.innerHTML = '';

                var ikon = document.createElement('i');
                // fa-magic: padanannya di Font Awesome 6 TIDAK ada di FA 5
                // yang dimuat aplikasi ini, dan ikon yang tidak ada tetap
                // menyisakan kotak kosong. Dijaga IkonAdaGlifnyaTest.
                ikon.className = 'fas fa-magic';
                ikon.setAttribute('aria-hidden', 'true');

                var teks = document.createElement('span');
                teks.textContent = jumlah + ' isian kami isikan dari pendaftaran Anda sebelumnya. '
                    + 'Periksa dulu, ubah kalau ada yang berbeda.';

                var batal = document.createElement('button');
                batal.type = 'button';
                batal.textContent = 'Kosongkan';
                batal.addEventListener('click', batalkan);

                kabar.appendChild(ikon);
                kabar.appendChild(teks);
                kabar.appendChild(batal);
                kabar.hidden = false;
            }

            function isikan(data) {
                diisiSkrip = [];
                bersihkanTanda();

                Object.keys(medan).forEach(function (k) {
                    var e = medan[k];

                    // Yang sudah ada isinya DIBIARKAN: orangnya mungkin
                    // sengaja memakai nama atau email yang berbeda kali ini.
                    if (!e || !data[k] || e.value.trim() !== '') return;

                    e.value = data[k];
                    e.classList.add('ses-baru-diisi');
                    diisiSkrip.push(k);
                });

                if (diisiSkrip.length === 0) {
                    kabar.hidden = true;

                    return;
                }

                tampilkanKabar(diisiSkrip.length);
            }

            function cari() {
                var nomor = angkaSaja(telp.value);

                if (nomor.length < 9 || nomor.length > 15) {
                    nomorTerakhir = '';

                    return;
                }

                // Nomor yang sama tidak dicari ulang: orang sering keluar
                // masuk isian ini saat memeriksa kembali isiannya.
                if (nomor === nomorTerakhir) return;

                nomorTerakhir = nomor;

                fetch(ALAMAT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF ? CSRF.getAttribute('content') : ''
                    },
                    body: JSON.stringify({ telp: telp.value })
                })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (d) { if (d && d.ditemukan) isikan(d); })
                    .catch(function () {
                        /*
                         * Didiamkan dengan sengaja. Ini kemudahan, bukan
                         * syarat — jaringan yang bermasalah tidak boleh
                         * memunculkan pesan galat di borang yang sebenarnya
                         * masih bisa diisi tangan.
                         */
                    });
            }

            // Saat selesai mengisi, bukan tiap ketukan.
            telp.addEventListener('change', cari);
            telp.addEventListener('blur', cari);
        })();

        /*
         * SESUDAH VALIDASI GAGAL, ORANGNYA DIANTAR KE GALATNYA.
         *
         * Peladen mengirim balik halaman yang sama dan peramban mendarat di
         * puncak. Di ponsel kartu isian berada JAUH di bawah kolom kiri —
         * terukur pesan galatnya 1935 px dari puncak pada layar 900 px, jadi
         * yang terlihat cuma halaman yang sama tanpa tanda apa pun bahwa ada
         * yang salah. Orangnya mengira tombolnya tidak berfungsi.
         *
         * Digulir ke ringkasannya, lalu fokus dipindah ke isian pertama yang
         * bermasalah supaya bisa langsung diperbaiki tanpa mencari.
         */
        (function () {
            var ringkas = document.getElementById('ses-ringkas-galat');
            if (!ringkas) return;

            var halus = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            function antarkan(mulus) {
                ringkas.scrollIntoView({
                    behavior: mulus ? 'smooth' : 'auto',
                    block: 'center',
                });
            }

            antarkan(halus);

            /*
             * DIULANG sesudah semua gambar selesai dimuat.
             *
             * Atribut width/height sudah memesan ruang untuk gambar yang
             * ukurannya terbaca, tetapi sampul yang hilang atau yang
             * alamatnya lengkap (http) tidak punya ukuran — dan begitu
             * tingginya berubah, halaman yang sudah digulir ke pesan galat
             * ikut bergeser dan pesannya keluar layar lagi.
             *
             * Diulang hanya kalau ringkasannya memang sudah tidak utuh
             * terlihat, supaya tidak menarik halaman yang sudah digulir
             * sendiri oleh orangnya.
             */
            window.addEventListener('load', function () {
                var kotak = ringkas.getBoundingClientRect();

                if (kotak.top < 0 || kotak.bottom > window.innerHeight) {
                    antarkan(halus);
                }
            });

            /*
             * Fokusnya menyusul SESUDAH gulirnya selesai. Memfokus isian lebih
             * dulu membuat peramban menggulir sendiri ke isian itu dan
             * ringkasannya terlewat begitu saja.
             */
            setTimeout(function () {
                var pertama = borang.querySelector('[aria-invalid="true"]');
                if (pertama) {
                    pertama.focus({ preventScroll: true });
                } else {
                    ringkas.focus({ preventScroll: true });
                }
            }, halus ? 650 : 0);
        })();

        /*
         * PENYINGKAPAN BERTAHAP.
         *
         * Kelas .ses-gerak dan .ses-muncul dipasang DI SINI, bukan ditulis di
         * markup. Yang menyembunyikan isi ada di CSS di belakang .ses-gerak,
         * jadi kalau berkas skrip ini gagal dimuat, tidak ada satu pun bagian
         * halaman yang hilang — ia cuma tampil tanpa gerak.
         *
         * Urutannya disengaja: kartu isian lebih dulu daripada isi kolom
         * kiri. Yang datang ke halaman ini sudah berniat mendaftar, jadi
         * borangnya tidak boleh menunggu giliran.
         */
        (function () {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            var sasaran = [].slice.call(document.querySelectorAll(
                '.ses-kartu, .ses-lencana-baris, .ses-judul, .ses-duo > *, .ses-fakta, .ses-pemateri, .ses-flyer'
            ));

            if (!sasaran.length) return;

            document.body.classList.add('ses-gerak');

            sasaran.forEach(function (e, i) {
                e.classList.add('ses-muncul');
                e.style.setProperty('--urut', i);
            });

            // Kotak fakta menyusul satu per satu di dalam barisnya sendiri.
            [].forEach.call(document.querySelectorAll('.ses-fakta-item'), function (e, i) {
                e.style.setProperty('--urut', i);
            });

            function tampakkan(e) {
                e.classList.add('tampak');
                var i = tersisa.indexOf(e);
                if (i !== -1) tersisa.splice(i, 1);
            }

            var tersisa = sasaran.slice();

            if (!('IntersectionObserver' in window)) {
                // Tanpa pengamat, semuanya langsung ditampilkan. Lebih baik
                // tanpa gerak daripada ada yang tidak pernah terlihat.
                sasaran.forEach(tampakkan);
                return;
            }

            var pengamat = new IntersectionObserver(function (masuk) {
                masuk.forEach(function (m) {
                    if (!m.isIntersecting) return;
                    pengamat.unobserve(m.target);
                    tampakkan(m.target);
                });
            }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });

            /*
             * JARING PENGAMAN, dan ini bukan kehati-hatian berlebihan.
             *
             * IntersectionObserver hanya melapor saat ambangnya DILINTASI.
             * Kalau halaman melompat — pemulihan posisi gulir sesudah borang
             * ditolak validasi, atau Cmd+End — bagian di tengah berpindah
             * dari "di bawah layar" langsung ke "di atas layar" tanpa pernah
             * bersinggungan, jadi tidak ada laporan dan bagiannya tertinggal
             * opasitas 0 SELAMANYA.
             */
            function sapu() {
                tersisa.slice().forEach(function (e) {
                    if (e.getBoundingClientRect().top < window.innerHeight) {
                        pengamat.unobserve(e);
                        tampakkan(e);
                    }
                });

                if (!tersisa.length) {
                    window.removeEventListener('scroll', jadwalkan2);
                    window.removeEventListener('resize', jadwalkan2);
                }
            }

            var terjadwal2 = false;

            function jadwalkan2() {
                if (terjadwal2) return;
                terjadwal2 = true;
                requestAnimationFrame(function () { terjadwal2 = false; sapu(); });
            }

            window.addEventListener('scroll', jadwalkan2, { passive: true });
            window.addEventListener('resize', jadwalkan2);

            sasaran.forEach(function (e) {
                if (e.getBoundingClientRect().top < window.innerHeight * 0.95) {
                    // Sudah terlihat sejak awal: ditampilkan di bingkai
                    // berikutnya supaya transisinya tetap jalan, bukan
                    // melompat begitu saja.
                    requestAnimationFrame(function () {
                        requestAnimationFrame(function () { tampakkan(e); });
                    });

                    return;
                }

                pengamat.observe(e);
            });
        })();

        /*
         * Pita bawah baru muncul setelah tombol daftar yang asli tergulung
         * keluar layar. Ditampilkan sejak awal, ia menutupi isian pertama
         * padahal tombol aslinya masih kelihatan tepat di atasnya.
         */
        if (tempel && borang && 'IntersectionObserver' in window) {
            var tombolAsli = document.getElementById('ses-kirim');

            if (tombolAsli) {
                new IntersectionObserver(function (masuk) {
                    tempel.hidden = masuk[0].isIntersecting;

                    // Penanda di <body> supaya CSS bisa menyingkirkan panah
                    // "ke atas" milik layout bersama selama pitanya tampil.
                    document.body.classList.toggle('ses-pita-tampil', !tempel.hidden);
                }, { rootMargin: '-20px 0px 0px 0px' }).observe(tombolAsli);
            }
        }

        /*
         * Tombolnya dimatikan sesudah ditekan. Menekan dua kali membuat dua
         * pendaftaran dan dua kursi terpotong untuk satu orang — dan yang
         * kedua baru ketahuan saat panitia merekap.
         */
        if (borang) {
            borang.addEventListener('submit', function () {
                Array.prototype.forEach.call(
                    borang.querySelectorAll('button[type=submit]'),
                    function (t) { t.disabled = true; }
                );

                var utama = document.getElementById('ses-kirim');
                if (utama) utama.querySelector('span').textContent = 'Memproses…';
            });
        }
    })();
</script>
@stop
