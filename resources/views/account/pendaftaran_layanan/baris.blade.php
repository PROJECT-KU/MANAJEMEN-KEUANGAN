{{--
  Satu baris pendaftaran di daftar terpadu.

  Dipisah jadi partial bukan demi kerapian: tubuh barisnya memuat delapan
  perhitungan yang semuanya butuh blok @php, dan blok @php di dalam @foreach
  pada berkas yang juga memakai penanda sebaris adalah persis jebakan yang
  pernah menelan markah sampai @endphp. Di berkas sendiri, bloknya aman.

  Tujuh kolom, bukan sembilan. "Orang" dilipat ke dalam kolom Pendaftar
  sebagai pil, dan "Bukti" ke dalam kolom Keadaan sebagai keping — keduanya
  keterangan tentang hal di sebelahnya, bukan kolom yang dibaca sendiri.
  Terukur di mode kartu 320px, sembilan sel berarti sembilan baris berlabel
  dalam satu kartu; tujuh sudah cukup dan kartunya 2 baris lebih pendek.

  Yang harus dikirim pemanggilnya:
    $b        satu baris hasil PendaftaranSemuaLayanan::kueri()
    $katalog  katalog layanan (kunci => nama, ikon, warna)
--}}
@php
    use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
    use App\Support\PesananPelanggan;

    $layananBaris = $katalog[$b->layanan]
        ?? ['nama' => $b->layanan, 'ikon' => 'fa-tag', 'warna' => 'mis-abu'];

    $keadaanBaris = Pendaftaran::keadaanDari($b->status);
    $rupa = Pendaftaran::KEADAAN[$keadaanBaris]
        ?? ['label' => 'Belum dikenali', 'warna' => 'abu', 'ikon' => 'fa-circle-notch'];

    /*
     * Dibaca lewat katalog, bukan dari kolomnya langsung: baris lama dari
     * sebelum kolom `cara_bayar` ada bernilai kosong, dan `caraBayar()`
     * menyebutnya transfer — sebelum kolom itu ada, transfer satu-satunya
     * jalur yang disediakan borangnya.
     */
    $caraBayar = Pendaftaran::caraBayar($b->cara_bayar ?? null);

    $buktiBaris = Pendaftaran::buktiBaris($b);
    $sesiBaris = Pendaftaran::sesiBaris($b);
    $angkatanBaris = Pendaftaran::angkatanBaris($b);

    // Kursinya habis: dipakai menandai angkatan yang tidak bisa menerima
    // pendaftaran baru lagi. Angkatan tanpa batas kuota tidak pernah penuh.
    $angkatanPenuh = $angkatanBaris !== null
        && $angkatanBaris['total_kuota'] !== null
        && $angkatanBaris['sisa_kuota'] !== null
        && $angkatanBaris['sisa_kuota'] <= 0;
    $waktuBaris = Pendaftaran::waktuBaris($b);
    $tautanBaris = Pendaftaran::tautanBaris($b);

    $wa = PesananPelanggan::nomorWa($b->telp);
    $jumlahOrang = max(1, (int) $b->jumlah);
    $diskon = (int) $b->nominal_diskon;
    $kodeUnik = (int) $b->kode_unik;

    /*
     * Status aslinya hanya ditampilkan di daftar kalau ia BELUM DIKENALI.
     *
     * Syarat sebelumnya "berbeda dari labelnya", dan itu hampir selalu benar —
     * 'Pendaftaran Diterima' vs label 'Lunas', 'expired' vs 'Tidak jadi' —
     * sehingga kepingnya muncul di hampir setiap baris dan menambah ~20px
     * pada semuanya tanpa memberi tahu apa pun yang baru. Untuk nilai yang
     * sudah dikenali, lencana keadaannya sudah menyebutkan artinya; nilai
     * mentahnya tetap terbaca di halaman rincian.
     */
    $statusAsing = $keadaanBaris === 'lain';
@endphp
<tr class="pdl-baris {{ $layananBaris['warna'] }}">
    <td class="mis-td-utama">
        <div class="pdl-layanan">
            <span class="mis-medali kecil {{ $layananBaris['warna'] }}" aria-hidden="true">
                <i class="fas {{ $layananBaris['ikon'] }}"></i>
            </span>
            <div style="min-width: 0;">
                <p class="pdl-layanan-nama" title="{{ $layananBaris['nama'] }}">{{ $layananBaris['nama'] }}</p>
            </div>
        </div>
    </td>

    <td data-judul="Pendaftar">
        <div class="pdl-orang">
            {{-- Nomor pendaftaran ditaruh di kolom INI, bukan di kolom
                 layanan: di sana lebarnya cuma 158px dan nomornya pecah jadi
                 tiga baris di tanda hubungnya. Di sini 229px, dan ia memang
                 keterangan tentang orangnya — itu yang disebut saat
                 menghubungi panitia lewat WhatsApp. --}}
            <span class="pdl-nomor">{{ $b->nomor ?: 'Tanpa nomor' }}</span>

            <p class="pdl-nama">
                {{-- title: namanya dipotong dua baris lewat CSS, jadi yang
                     panjang tetap harus bisa dibaca utuh. --}}
                <span class="pdl-nama-teks" title="{{ $b->nama_orang ?: 'Tanpa nama' }}">{{ $b->nama_orang ?: 'Tanpa nama' }}</span>
                @if ($jumlahOrang > 1)
                    {{-- Rombongan ditandai di sebelah namanya, bukan di kolom
                         angka sendiri: yang penting bukan angkanya melainkan
                         bahwa pendaftaran ini mewakili beberapa orang.
                         Terukur ada baris berisi 5, 6, dan 13 orang. --}}
                    <span class="mis-pil mis-pil-ungu pdl-pil-orang">
                        <i class="fas fa-users" aria-hidden="true"></i> {{ $jumlahOrang }} orang
                    </span>
                @endif
            </p>

            {{-- HANYA nomor WhatsApp di sini.

                 Email dan afiliasi sengaja tidak ikut: keduanya sudah ada di
                 halaman rincian, dan di daftar keduanya menambah dua baris
                 pada SETIAP baris tabel tanpa dipakai memindai. Yang dipakai
                 memindai cuma tiga — nomor pendaftaran, nama, dan nomor yang
                 bisa dihubungi.

                 Tautan, bukan teks: kolom ini gunanya menghubungi orangnya,
                 dan sebagai teks nomornya harus disalin dulu ke aplikasi lain.
                 wa.me menuntut nomor berformat internasional tanpa tanda baca,
                 dan itulah yang dikerjakan nomorWa(). --}}
            <div class="pdl-kontak">
                @if ($wa)
                    <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener"
                        title="Hubungi {{ $b->nama_orang }} lewat WhatsApp">
                        <i class="fab fa-whatsapp mis-ikon-hijau" aria-hidden="true"></i> {{ $b->telp }}
                    </a>
                @elseif ($b->telp)
                    {{-- Ditandai, bukan dijadikan tautan yang menuntun ke
                         halaman galat WhatsApp. Nomor yang kurang dari sembilan
                         angka bukan nomor telepon melainkan sisa isian. --}}
                    <span title="Nomor ini tidak bisa dipakai menghubungi lewat WhatsApp.">
                        <i class="fas fa-phone-slash mis-ikon-kuning" aria-hidden="true"></i> {{ $b->telp }}
                    </span>
                @else
                    <span class="pdl-kontak-kosong">nomor belum diisi</span>
                @endif
            </div>
        </div>
    </td>

    <td data-judul="Sesi">
        @if ($angkatanBaris)
            {{-- Nama angkatan TANPA awalan nama layanannya: terukur 56 dari 59
                 angkatan namanya memuat nama layanannya sendiri, jadi kolom
                 Layanan di sebelah kiri dan kolom ini menulis hal yang sama
                 dua kali. Nama penuhnya tetap di title. --}}
            <span class="pdl-sesi" title="{{ $angkatanBaris['nama'] }}">{{ $angkatanBaris['ringkas'] }}</span>

            @if ($angkatanBaris['nomor'])
                {{-- Nomor angkatannya — "batch ke berapa". Tanpa ini nama
                     tempat saja tidak menunjuk satu angkatan: Scopus Camp
                     Yogyakarta sudah angkatan ke-202 sementara Jakarta baru
                     ke-9, dan merekap tanpa nomornya menggabungkan dua ratus
                     angkatan jadi satu baris.

                     DITULIS DENGAN KATA, bukan "#199". Tanda pagar memang
                     dipahami orang yang terbiasa dengan nomor urut, tetapi
                     artinya hanya muncul di title — dan title cuma terbaca
                     kalau kursornya ditahan di atasnya, yang tidak dilakukan
                     orang yang sedang mencari satu nama. --}}
                <span class="pdl-angkatan-no">Angkatan ke-{{ $angkatanBaris['nomor'] }}</span>
            @endif

            <span class="pdl-sesi-ket">
                @if ($angkatanBaris['mulai'])
                    {{-- Tanggalnya disebut: tanpa itu tidak ada cara tahu
                         angkatan ini bulan depan atau sudah lewat. --}}
                    {{ \Illuminate\Support\Carbon::parse($angkatanBaris['mulai'])->translatedFormat('d M Y') }}
                @endif
                @if ($angkatanPenuh)
                    <span class="pdl-penuh" title="Kursinya habis; angkatan ini tidak bisa menerima pendaftaran baru.">penuh</span>
                @elseif ($angkatanBaris['sisa_kuota'] !== null)
                    <span class="pdl-sisa">sisa {{ $angkatanBaris['sisa_kuota'] }}</span>
                @endif
            </span>
        @else
            {{-- Dua layanan tidak berangkatan; sesinya teks bebas di barisnya
                 sendiri. Dipotong dua baris lewat CSS, bukan di markah: teks
                 penuhnya tetap sampai ke cetakan dan pembaca layar. --}}
            <span class="pdl-sesi" @if ($sesiBaris) title="{{ $sesiBaris }}" @endif>{{ $sesiBaris ?: '—' }}</span>
        @endif
    </td>

    <td data-judul="Total bayar" class="text-right">
        {{-- Nominal dan kedua keterangannya dibungkus jadi SATU anak.

             Di mode kartu, selnya `display: flex` dengan label `::before`
             sebagai anak pertama — jadi tiap <span> di sini jadi anak flex
             tersendiri, dan `display: inline` pun diblokkan jadi blok.
             Terukur di 320px: label + nominal + kode unik + potongan = empat
             anak sebaris menuntut 314px dalam sel selebar 285px. --}}
        <span class="pdl-uang-blok">
            <span class="pdl-uang">Rp {{ number_format((int) $b->total, 0, ',', '.') }}</span>
            @if ($kodeUnik > 0)
                {{-- Kode uniknya ditampilkan: inilah yang dicocokkan panitia
                     dengan mutasi rekening, dan tanpanya dua transfer
                     bernominal sama tidak bisa dibedakan milik siapa. --}}
                <span class="pdl-uang-ket">kode {{ number_format($kodeUnik, 0, ',', '.') }}</span>
            @endif
            @if ($diskon > 0)
                <span class="pdl-uang-ket">
                    potongan Rp {{ number_format($diskon, 0, ',', '.') }}{{ $b->kode_diskon ? ' · ' . $b->kode_diskon : '' }}
                </span>
            @endif
            {{-- Cara bayarnya dilipat ke kolom ini, BUKAN jadi kolom sendiri:
                 daftarnya sudah tujuh kolom dan kolom kedelapan menggeser
                 tabelnya melewati lebar layar di 768px. Tidak memakai
                 .mis-pil — mis-tabel-kartu menandai sel status lewat
                 `:has(.mis-pil)`, dan sel berpil kedua membuat keduanya
                 berbagi satu baris sempit di mode kartu. --}}
            <span class="pdl-uang-ket">
                <i class="fas {{ $caraBayar['ikon'] }}" aria-hidden="true"></i>
                {{ $caraBayar['ringkas'] }}
            </span>
        </span>
    </td>

    <td data-judul="Keadaan">
        {{-- Warna status dipetakan di PHP, bukan dengan @if berantai di sini:
             lima layanan memakai lima kosakata, dan memilah-milahnya di
             tampilan berarti belasan cabang dalam satu baris. Nilai yang tidak
             dikenali jadi abu-abu, bukan hijau — lencana hijau pada keadaan
             yang tidak dipahami lebih menyesatkan daripada lencana netral. --}}
        <span class="pdl-keadaan-blok">
            <span class="mis-pil mis-pil-{{ $rupa['warna'] }}">
                <i class="fas {{ $rupa['ikon'] }}" aria-hidden="true"></i> {{ $rupa['label'] }}
            </span>

            {{-- Keping bukti dilipat ke kolom ini, dan TIDAK memakai .mis-pil:
                 mis-tabel-kartu menandai sel status lewat `:has(.mis-pil)` lalu
                 menaikkannya ke baris kaki kartu dengan `flex: 1 1 0`. Dua sel
                 berpil berarti keduanya berbagi satu baris sempit. --}}
            <span class="pdl-bukti-baris">
                @if (! $buktiBaris['nilai'])
                    <span class="pdl-bukti pdl-bukti-nihil">
                        <i class="fas fa-minus-circle" aria-hidden="true"></i>
                        @if ($caraBayar['kunci'] === 'tunai')
                            {{-- Bayar di tempat memang TIDAK punya bukti untuk
                                 diunggah. Ditulis "bukti belum ada", panitia
                                 akan mengejar tangkapan layar yang tidak akan
                                 pernah ada. --}}
                            bayar di tempat
                        @elseif ($b->layanan === 'webinar_eksklusif')
                            {{-- Webinar memang tidak pernah mengunggah bukti:
                                 pembayarannya dicocokkan lewat kode unik. Jadi
                                 kosong di sana bukan pekerjaan yang terlewat. --}}
                            pakai kode unik
                        @else
                            bukti belum ada
                        @endif
                    </span>
                @elseif ($buktiBaris['ada'])
                    <a class="pdl-bukti pdl-bukti-ada" href="{{ $buktiBaris['url'] }}"
                        target="_blank" rel="noopener"
                        aria-label="Buka bukti bayar {{ $b->nama_orang }}"
                        title="Buka bukti bayar di tab baru">
                        <i class="fas fa-image" aria-hidden="true"></i> lihat bukti
                    </a>
                @else
                    {{-- Dibedakan dari "belum ada", dan itu bukan kerewelan:
                         terukur 72 dari 183 nilai bukti menunjuk berkas yang
                         sudah tidak ada di cakram. Ditulis "ada", panitia akan
                         menekan tautan yang pasti gagal lalu mengira layarnya
                         yang rusak. --}}
                    <span class="pdl-bukti pdl-bukti-hilang"
                        title="Kolomnya terisi tetapi berkasnya tidak ada lagi di server, jadi buktinya tidak bisa dibuka.">
                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i> berkas hilang
                    </span>
                @endif
            </span>

            @if ($statusAsing)
                {{-- Nilai yang belum punya keadaan WAJIB terbaca apa adanya:
                     lencananya cuma berbunyi "Belum dikenali", dan tanpa nilai
                     mentahnya tidak ada yang tahu apa yang harus ditambahkan
                     ke katalog. --}}
                <span class="pdl-status-asli" title="Status asli yang tersimpan di basis data, belum punya keadaan di katalog">{{ $b->status }}</span>
            @endif
        </span>
    </td>

    <td data-judul="Daftar">
        @if ($waktuBaris)
            <span class="pdl-tanggal">{{ $waktuBaris->translatedFormat('d M Y') }}</span>
            <span class="pdl-jam">{{ $waktuBaris->format('H:i') }}</span>
        @else
            <span class="rin-samar">—</span>
        @endif
    </td>

    <td data-judul="Aksi" class="text-right">
        @if ($tautanBaris)
            {{-- Satu halaman rincian untuk kelima layanan, dengan bagian borang
                 yang berbeda per layanan. Di sanalah seluruh tindakan berada
                 sejak layar pendaftaran per layanan dibuang. --}}
            {{-- aria-label WAJIB: di lebar tablet tulisan "Rincian"
                 disembunyikan supaya tabelnya tidak terklip, dan tombol ikon
                 tanpa nama tidak menyebut apa pun ke pembaca layar. --}}
            <a class="mis-tombol mis-tombol-halus pdl-tombol-buka" href="{{ $tautanBaris }}"
                aria-label="Buka rincian pendaftaran {{ $b->nama_orang }}"
                title="Buka rincian pendaftaran {{ $b->nama_orang }}">
                <i class="fas fa-eye" aria-hidden="true"></i> <span class="pdl-tombol-teks">Rincian</span>
            </a>
        @else
            <span class="pdl-bukti pdl-bukti-nihil">tanpa layar</span>
        @endif
    </td>
</tr>
