@extends('public.layout.header')

@section('title', 'Pesan Scopus Kafe | Rumah Scopus')

@push('kepala')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600..900&family=Figtree:wght@400;500;700;800&display=swap">
@endpush

@section('konten')
{{--
    Borang pemesanan Scopus Kafe — dirombak 26 Okt 2026 mengikuti Scopus Camp.

    Yang dibuang dari bentuk lamanya, dan sebabnya:

      - TUJUH MEDAN UANG TERSEMBUNYI. `biaya`, `kode_unik_pembayaran`,
        `subtotal_pembayaran` (tiga kali, satu per sesi), dan
        `total_keseluruhan_pembayaran` dikirim dari peramban lalu disimpan
        mentah. Siapa pun bisa memesan tiga sesi bertotal nol. Sekarang yang
        dikirim cuma KUNCI sesinya; harganya dihitung peladen dari katalog.

      - KOTAK UNGGAH BUKTI TRANSFER, beserta kartu nomor rekeningnya.
        Pembayaran layanan ini seluruhnya lewat gerbang sekarang.

    Yang ditambahkan: aturan tanggal. Layanan ini tidak berangkatan — terbuka
    terus — tetapi hanya BERJALAN Jumat, Sabtu, Minggu. Tanpa aturan itu orang
    memesan hari Selasa, panitia menolaknya dengan tangan lewat WhatsApp, dan
    pemesannya mengulang dari nol.
--}}
@include('public.scopus_kafe.partials.gaya-borang')

<section class="kfb">
    <div class="container">

        @if (session('error'))
            <div class="kfb-kabar galat" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if (session('success'))
            <div class="kfb-kabar sukses" role="status">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="kfb-kabar galat" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="kfb-kepala">
            <h1 class="kfb-judul">Pesan Scopus Kafe</h1>
            <p class="kfb-sub">
                Pendampingan privat bersama trainer Rumah Scopus, lima jam per sesi.
                Isinya sebentar saja, lalu Anda langsung diantar ke halaman pembayaran.
            </p>
        </div>

        <form method="POST" action="{{ route('public.scopuskafe.store') }}" id="kfbBorang">
            @csrf

            <div class="kfb-kisi">
                <div class="kfb-kartu">
                    <h2 class="kfb-kartu-judul"><i class="bi bi-person-vcard"></i> Data diri</h2>

                    <label class="kfb-label" for="kfbTelp">
                        Nomor WhatsApp <span class="kfb-wajib">*</span>
                    </label>
                    {{-- Nomor DULUAN, sebelum nama.

                         Dari nomor inilah data diri yang pernah memesan dibaca
                         dan diisikan sendiri — menaruhnya sesudah nama berarti
                         orangnya sudah mengetik yang tidak perlu ia ketik. --}}
                    <input type="tel" id="kfbTelp" name="telp" class="kfb-isian" required
                        inputmode="tel" autocomplete="tel" placeholder="08xxxxxxxxxx"
                        value="{{ old('telp', $akun->phone ?? '') }}">
                    <p class="kfb-bantu" id="kfbKabarIsi"></p>

                    <label class="kfb-label" for="kfbNama">Nama lengkap <span class="kfb-wajib">*</span></label>
                    <input type="text" id="kfbNama" name="nama" class="kfb-isian" required
                        autocomplete="name" value="{{ old('nama', $akun->full_name ?? '') }}">

                    <label class="kfb-label" for="kfbEmail">Email <span class="kfb-wajib">*</span></label>
                    <input type="email" id="kfbEmail" name="email" class="kfb-isian" required
                        autocomplete="email" value="{{ old('email', $akun->email ?? '') }}">
                    <p class="kfb-bantu">Bukti pemesanan dan fakturnya dikirim ke sini.</p>
                </div>

                <div class="kfb-kartu">
                    <h2 class="kfb-kartu-judul"><i class="bi bi-calendar-week"></i> Kapan &amp; sesi apa</h2>

                    <label class="kfb-label" for="kfbTanggal">Tanggal <span class="kfb-wajib">*</span></label>
                    {{-- min= menahan tanggal yang sudah lewat di peramban;
                         peladennya memeriksa ulang, sebab atribut ini hilang
                         begitu orangnya mengirim lewat alat lain. --}}
                    <input type="date" id="kfbTanggal" name="tanggal_pemesanan" class="kfb-isian"
                        required min="{{ $tanggalMin }}"
                        value="{{ old('tanggal_pemesanan', $tanggalMin) }}"
                        data-hari-buka="5,6,7">
                    <p class="kfb-bantu" id="kfbKabarTanggal">
                        Scopus Kafe berjalan setiap <strong>{{ $namaHariBuka }}</strong>.
                    </p>

                    <span class="kfb-label">Pilih sesi <span class="kfb-wajib">*</span></span>
                    <p class="kfb-bantu" style="margin-top: -4px;">
                        Boleh lebih dari satu, paling banyak {{ $maksSesi }} sesi.
                    </p>

                    <div class="kfb-sesi">
                        @foreach ($sesi as $s)
                            @php $terpilih = in_array($s->id, (array) old('sesi', []), true); @endphp
                            <label class="kfb-sesi-butir {{ $terpilih ? 'dipilih' : '' }}">
                                <input type="checkbox" name="sesi[]" value="{{ $s->id }}"
                                    data-biaya="{{ \App\Support\HargaKafe::rupiah($s->biaya) }}"
                                    {{ $terpilih ? 'checked' : '' }}>
                                <span class="kfb-sesi-isi">
                                    {{-- namaSesi: isinya di basis data berhuruf
                                         kecil semua ("sesi 1"), dan dicetak apa
                                         adanya ia terbaca seperti catatan yang
                                         belum selesai. --}}
                                    <strong>{{ \App\Support\HargaKafe::namaSesi($s->sesi) }}</strong>
                                    <span class="kfb-sesi-jam">
                                        {{ \Carbon\Carbon::parse($s->waktu_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($s->waktu_selesai)->format('H:i') }}
                                        @if ($s->lokasi) · {{ $s->lokasi }} @endif
                                    </span>
                                </span>
                                <span class="kfb-sesi-harga">
                                    Rp {{ number_format(\App\Support\HargaKafe::rupiah($s->biaya), 0, ',', '.') }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="kfb-ringkas">
                {{-- Totalnya dipajang hidup, tetapi TIDAK dikirim.

                     Angka di sini cuma supaya orangnya tahu apa yang akan ia
                     bayar; yang ditagihkan dihitung ulang peladen dari katalog
                     sesinya. Dikirim, ia kembali jadi nominal yang ditentukan
                     sisi yang tidak bisa dipercaya. --}}
                <div class="kfb-ringkas-baris">
                    <span>Total tagihan</span>
                    <strong id="kfbTotal">Rp 0</strong>
                </div>
                <p class="kfb-bantu">
                    Nominalnya ditambah kode unik tiga angka saat tagihan dibuat — itu yang
                    membedakan pembayaran Anda dari pemesan lain.
                </p>

                <label class="kfb-setuju">
                    <input type="checkbox" name="setuju" value="1" required
                        {{ old('setuju') ? 'checked' : '' }}>
                    <span>
                        Saya setuju dengan
                        <a href="{{ url('/ketentuan-layanan') }}" target="_blank" rel="noopener">ketentuan layanan</a>
                        Rumah Scopus Foundation.
                    </span>
                </label>

                <button type="submit" class="kfb-kirim" id="kfbKirim">
                    <i class="bi bi-lock-fill"></i> Lanjut ke pembayaran
                </button>

                <div class="kfb-aman">
                    <i class="bi bi-shield-check"></i>
                    <span>Pembayaran diproses gerbang resmi. Setelah membayar, status
                        pemesanan Anda berpindah sendiri — tidak perlu mengirim bukti
                        transfer.</span>
                </div>

                <div class="kfb-aman">
                    <i class="bi bi-hourglass-split"></i>
                    <span>Tagihannya berlaku <strong>{{ $menitBayar }} menit</strong>. Lewat
                        dari itu tagihannya mati, dan Anda bisa membuat tagihan baru dari
                        halaman pemesanan.</span>
                </div>
            </div>
        </form>
    </div>
</section>

@include('public.scopus_kafe.partials.skrip-borang', [
    'alamatCari' => route('public.scopuscamp.caripendaftar'),
])
@stop
