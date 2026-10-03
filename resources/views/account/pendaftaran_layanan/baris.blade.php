{{--
  Satu baris pendaftaran, dipakai daftar utama.

  Dipisah jadi partial bukan demi kerapian: tubuh barisnya memuat lima
  perhitungan yang semuanya butuh blok @php, dan blok @php di dalam @foreach
  pada berkas yang juga memakai penanda sebaris adalah persis jebakan yang
  pernah menelan markah sampai @endphp. Di berkas sendiri, bloknya aman.

  Yang harus dikirim pemanggilnya:
    $b        satu baris hasil PendaftaranSemuaLayanan::kueri()
    $katalog  katalog layanan (kunci => nama, ikon, warna)
--}}
@php
    use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
    use App\Support\PesananPelanggan;

    $layananBaris = $katalog[$b->layanan] ?? ['nama' => $b->layanan, 'ikon' => 'fa-tag', 'warna' => 'mis-abu'];
    $keadaanBaris = Pendaftaran::keadaanDari($b->status);
    $rupa = Pendaftaran::KEADAAN[$keadaanBaris] ?? ['label' => 'Belum dikenali', 'warna' => 'abu', 'ikon' => 'fa-circle-notch'];
    $buktiBaris = Pendaftaran::buktiBaris($b);
    $sesiBaris = Pendaftaran::sesiBaris($b);
    $waktuBaris = Pendaftaran::waktuBaris($b);
    $tautanBaris = Pendaftaran::tautanBaris($b);
    $wa = PesananPelanggan::nomorWa($b->telp);
    $jumlahOrang = (int) $b->jumlah;
    $diskon = (int) $b->nominal_diskon;
    $kodeUnik = (int) $b->kode_unik;
@endphp
<tr>
    <td class="mis-td-utama">
        <div class="pdl-layanan">
            <span class="mis-medali kecil {{ $layananBaris['warna'] }}" aria-hidden="true">
                <i class="fas {{ $layananBaris['ikon'] }}"></i>
            </span>
            <div style="min-width: 0;">
                <p class="pdl-layanan-nama">{{ $layananBaris['nama'] }}</p>
                {{-- Nomor pendaftarannya ditaruh di sini, bukan di kolom
                     sendiri: inilah yang disebut orang saat menghubungi
                     panitia lewat WhatsApp, tetapi ia tidak pernah dibaca
                     sebagai kolom berdiri sendiri — selalu bersama layanannya. --}}
                <span class="pdl-nomor">{{ $b->nomor ?: '—' }}</span>
            </div>
        </div>
    </td>

    <td data-judul="Pendaftar">
        <div class="pdl-orang">
            <p class="pdl-nama">{{ $b->nama_orang ?: 'Tanpa nama' }}</p>
            {{-- Tautan, bukan teks. Kolom ini gunanya menghubungi orangnya;
                 sebagai teks, nomornya harus disalin dulu ke aplikasi lain.
                 wa.me menuntut nomor berformat internasional tanpa tanda baca,
                 dan itulah yang dikerjakan nomorWa(). --}}
            <div class="pdl-kontak">
                @if ($b->email)
                    <a href="mailto:{{ $b->email }}" title="Kirim email ke {{ $b->email }}">
                        <i class="fas fa-envelope mis-ikon-biru" aria-hidden="true"></i> {{ $b->email }}
                    </a>
                @endif
                @if ($wa)
                    <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener"
                        title="Hubungi lewat WhatsApp">
                        <i class="fab fa-whatsapp mis-ikon-hijau" aria-hidden="true"></i> {{ $b->telp }}
                    </a>
                @elseif ($b->telp)
                    {{-- Ditandai, bukan dijadikan tautan yang menuntun ke
                         halaman galat WhatsApp. Nomor yang kurang dari sembilan
                         angka bukan nomor telepon melainkan sisa isian. --}}
                    <span title="Nomor ini tidak bisa dipakai menghubungi lewat WhatsApp.">
                        <i class="fas fa-phone-slash mis-ikon-kuning" aria-hidden="true"></i> {{ $b->telp }}
                    </span>
                @endif
            </div>
            @if ($b->affiliasi)
                <p class="pdl-afiliasi">{{ $b->affiliasi }}</p>
            @endif
        </div>
    </td>

    <td data-judul="Sesi">
        <span class="pdl-sesi">{{ $sesiBaris ?? '—' }}</span>
    </td>

    <td data-judul="Orang" class="text-center">
        <span class="pdl-jumlah">{{ $jumlahOrang }}</span>
    </td>

    <td data-judul="Total bayar" class="text-right">
        {{-- Nominal dan kedua keterangannya dibungkus jadi SATU anak.

             Di mode kartu, selnya `display: flex` dengan label `::before`
             sebagai anak pertama — jadi tiap <span> di sini jadi anak flex
             tersendiri, dan `display: inline` pun diblokkan jadi blok.
             Terukur di 320px: label + nominal + kode unik + potongan = empat
             anak sebaris menuntut 314px dalam sel selebar 285px. Dibungkus,
             anaknya tinggal dua dan keterangannya menumpuk di bawah
             nominalnya. --}}
        <span class="pdl-uang-blok">
            <span class="pdl-uang">Rp {{ number_format((int) $b->total, 0, ',', '.') }}</span>
            @if ($kodeUnik > 0)
                {{-- Kode uniknya ditampilkan: inilah yang dicocokkan panitia
                     dengan mutasi rekening, dan tanpanya dua transfer
                     bernominal sama tidak bisa dibedakan milik siapa. --}}
                <span class="pdl-uang-ket">kode unik {{ number_format($kodeUnik, 0, ',', '.') }}</span>
            @endif
            @if ($diskon > 0)
                <span class="pdl-uang-ket">
                    potongan Rp {{ number_format($diskon, 0, ',', '.') }}{{ $b->kode_diskon ? ' · ' . $b->kode_diskon : '' }}
                </span>
            @endif
        </span>
    </td>

    <td data-judul="Bukti">
        {{-- Sel ini TIDAK memakai .mis-pil, dan itu bukan selera.

             mis-tabel-kartu menandai sel status lewat `:has(.mis-pil)` lalu
             menaikkannya ke baris kaki kartu dengan `flex: 1 1 0`. Dengan pil
             di sel Bukti, sel Keadaan, dan sel Aksi sekaligus, ketiganya
             berbagi satu baris — terukur di 320px masing-masing tinggal 46px,
             sementara lencana "Berkas hilang" sendiri menuntut 92px. Tabelnya
             meluber 234px dan terpotong, sebab pembungkusnya overflow:hidden. --}}
        @if (! $buktiBaris['nilai'])
            {{-- Webinar Eksklusif memang tidak pernah mengunggah bukti:
                 pembayarannya dicocokkan lewat kode unik terhadap mutasi
                 rekening. Jadi kosong di sana bukan pekerjaan yang terlewat. --}}
            <span class="pdl-bukti pdl-bukti-nihil">
                <i class="fas fa-minus" aria-hidden="true"></i> Belum ada
            </span>
        @elseif ($buktiBaris['ada'])
            <a class="pdl-bukti" href="{{ $buktiBaris['url'] }}" target="_blank" rel="noopener"
                aria-label="Buka bukti bayar {{ $b->nama_orang }}" title="Buka bukti bayar di tab baru">
                <i class="fas fa-image mis-ikon-hijau" aria-hidden="true"></i> Lihat
            </a>
        @else
            {{-- Dibedakan dari "belum ada", dan itu bukan kerewelan: terukur
                 72 dari 183 nilai bukti menunjuk berkas yang sudah tidak ada
                 di cakram. Ditulis "ada", panitia akan menekan tautan yang
                 pasti gagal lalu mengira layarnya yang rusak. --}}
            <span class="pdl-bukti pdl-bukti-hilang"
                title="Kolomnya terisi tetapi berkasnya tidak ada lagi di server, jadi buktinya tidak bisa dibuka.">
                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Berkas hilang
            </span>
        @endif
    </td>

    <td data-judul="Keadaan">
        {{-- Warna status dipetakan di PHP, bukan dengan @if berantai di sini:
             lima layanan memakai lima kosakata, dan memilah-milahnya di
             tampilan berarti belasan cabang dalam satu baris. Nilai yang tidak
             dikenali jadi abu-abu, bukan hijau. --}}
        <span class="pdl-keadaan-blok">
        <span class="mis-pil mis-pil-{{ $rupa['warna'] }}">
            <i class="fas {{ $rupa['ikon'] }}" aria-hidden="true"></i> {{ $rupa['label'] }}
        </span>
        @if ($keadaanBaris === 'lain' || strtolower(trim((string) $b->status)) !== strtolower($rupa['label']))
            {{-- Status aslinya tetap bisa dilihat. Terjemahannya menyatukan
                 sembilan nilai jadi empat keadaan, jadi selisih antar layanan
                 — 'expired' di webinar vs 'Pendaftaran Dibatalkan' di camp —
                 hilang dari layar kalau nilainya tidak bisa dijangkau. --}}
            <div class="pdl-nomor mt-1">
                <span class="pdl-status-asli" title="Status asli yang tersimpan di basis data">{{ $b->status }}</span>
            </div>
        @endif
        </span>
    </td>

    <td data-judul="Daftar">
        @if ($waktuBaris)
            {{ $waktuBaris->translatedFormat('d M Y') }}
            <div class="pdl-nomor">{{ $waktuBaris->format('H:i') }}</div>
        @else
            —
        @endif
    </td>

    <td data-judul="Aksi" class="text-right">
        @if ($tautanBaris)
            {{-- Menautkan ke layar layanannya, tidak menyalin tindakannya ke
                 sini. Aturan kuota berbeda di tiap layanan — webinar
                 mengembalikan kursi saat dibatalkan, camp tidak punya kursi
                 untuk dikembalikan — dan menuliskannya ulang dari satu layar
                 berarti lima aturan yang harus dijaga di dua tempat. --}}
            <a class="mis-tombol mis-tombol-halus" href="{{ $tautanBaris }}"
                title="Buka di layar {{ $layananBaris['nama'] }}">
                <i class="fas fa-external-link-alt" aria-hidden="true"></i> Buka
            </a>
        @else
            {{-- Tanpa pil, sama alasannya dengan sel Bukti di atas. --}}
            <span class="pdl-bukti pdl-bukti-nihil">Tanpa layar</span>
        @endif
    </td>
</tr>
