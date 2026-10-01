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
                @if ($tarif && $tarif->daftar_kegiatan)
                    <div class="ses-bahas">
                        <p class="ses-bahas-judul">Yang dibahas di sesi ini</p>
                        <ol>
                            @foreach ($tarif->daftar_kegiatan as $k)
                                <li>{{ $k }}</li>
                            @endforeach
                        </ol>
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
                            <img src="{{ $sesi->alamat_pemateri }}" alt="" class="ses-pemateri-foto" loading="lazy">
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
                    <img src="{{ $sesi->alamat_sampul }}" alt="Flyer {{ $sesi->nama }}"
                        class="ses-flyer" loading="lazy">
                @endif

                @if ($tarif && $tarif->daftar_fasilitas)
                    <div class="ses-dapat">
                        <p class="ses-dapat-judul">Yang Anda bawa pulang</p>
                        <ul>
                            @foreach ($tarif->daftar_fasilitas as $f)
                                <li><i class="fas fa-check-circle" aria-hidden="true"></i> <span>{{ $f }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- --------------------------------------------- kanan: isian --}}
            <div class="ses-kanan">
                <form method="POST" action="{{ route('public.webinareksklusif.store') }}" class="ses-kartu"
                    id="ses-borang">
                    @csrf
                    <input type="hidden" name="kategori_id" value="{{ $sesi->id }}">

                    <p class="ses-kartu-judul">Amankan kursi Anda</p>
                    <p class="ses-kartu-sub">Isinya lima, tidak sampai satu menit.</p>

                    <div class="ses-isian">
                        <label for="ses-nama">Nama lengkap <span aria-hidden="true">*</span></label>
                        <input type="text" id="ses-nama" name="nama" required maxlength="120"
                            value="{{ old('nama') }}" placeholder="Nama beserta gelar, untuk sertifikat"
                            autocomplete="name">
                        @error('nama') <p class="ses-salah">{{ $message }}</p> @enderror
                    </div>

                    <div class="ses-isian">
                        <label for="ses-email">Email aktif <span aria-hidden="true">*</span></label>
                        <input type="email" id="ses-email" name="email" required maxlength="120"
                            value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email">
                        {{-- Alasannya disebut, bukan cuma "wajib". Orang lebih
                             rela memberikan emailnya kalau tahu untuk apa. --}}
                        <p class="ses-bantu">Tautan masuk dan sertifikat dikirim ke sini.</p>
                        @error('email') <p class="ses-salah">{{ $message }}</p> @enderror
                    </div>

                    <div class="ses-isian">
                        <label for="ses-telp">Nomor WhatsApp <span aria-hidden="true">*</span></label>
                        <input type="tel" id="ses-telp" name="telp" required maxlength="30"
                            value="{{ old('telp') }}" placeholder="0812 3456 7890" autocomplete="tel"
                            inputmode="numeric">
                        <p class="ses-bantu">Dipakai menambahkan Anda ke grup peserta.</p>
                        @error('telp') <p class="ses-salah">{{ $message }}</p> @enderror
                    </div>

                    <div class="ses-isian">
                        <label for="ses-affiliasi">Asal instansi</label>
                        <input type="text" id="ses-affiliasi" name="affiliasi" maxlength="160"
                            value="{{ old('affiliasi') }}" placeholder="Universitas / lembaga — boleh dikosongkan"
                            autocomplete="organization">
                        @error('affiliasi') <p class="ses-salah">{{ $message }}</p> @enderror
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

                    <div class="ses-total">
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

    .ses-bahas {
        margin: 0 0 22px;
        padding: 18px 22px;
        border-radius: 16px;
        border: 1px solid var(--garis);
        border-left: 4px solid var(--jingga);
        background: #fff;
    }

    .ses-bahas-judul { margin: 0 0 8px; font-size: .95rem; font-weight: 800; color: var(--navy); }

    .ses-bahas ol { margin: 0; padding-left: 20px; }
    .ses-bahas li { padding: 4px 0; font-size: .93rem; line-height: 1.65; color: var(--tinta); }

    .ses-fakta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        margin-bottom: 22px;
    }

    .ses-fakta-item {
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

    .ses-flyer {
        display: block;
        width: 100%;
        max-width: 420px;
        margin-bottom: 22px;
        border-radius: 18px;
        border: 1px solid var(--garis);
        box-shadow: 0 18px 40px -24px rgba(15, 43, 91, .5);
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
        /* Ruang supaya pita bawah tidak menutupi isian terakhir. */
        .ses-latar { padding-bottom: 150px; }
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
            nilai.textContent = rupiah(total);
            if (tempelNilai) tempelNilai.textContent = rupiah(total);
            if (rincian) rincian.textContent = rupiah(harga) + ' × ' + n + ' peserta';
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
         * Pita bawah baru muncul setelah tombol daftar yang asli tergulung
         * keluar layar. Ditampilkan sejak awal, ia menutupi isian pertama
         * padahal tombol aslinya masih kelihatan tepat di atasnya.
         */
        if (tempel && borang && 'IntersectionObserver' in window) {
            var tombolAsli = document.getElementById('ses-kirim');

            if (tombolAsli) {
                new IntersectionObserver(function (masuk) {
                    tempel.hidden = masuk[0].isIntersecting;
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
