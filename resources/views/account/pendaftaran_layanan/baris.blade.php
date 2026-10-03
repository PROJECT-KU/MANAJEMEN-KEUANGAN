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

    $buktiBaris = Pendaftaran::buktiBaris($b);
    $sesiBaris = Pendaftaran::sesiBaris($b);
    $waktuBaris = Pendaftaran::waktuBaris($b);
    $tautanBaris = Pendaftaran::tautanBaris($b);

    $wa = PesananPelanggan::nomorWa($b->telp);
    $jumlahOrang = max(1, (int) $b->jumlah);
    $diskon = (int) $b->nominal_diskon;
    $kodeUnik = (int) $b->kode_unik;

    // Status aslinya hanya ditampilkan kalau ia memang berbeda dari labelnya;
    // mengulang kata yang sama dua kali cuma menambah ramai.
    $statusBeda = strtolower(trim((string) $b->status)) !== strtolower($rupa['label']);
@endphp
<tr class="pdl-baris {{ $layananBaris['warna'] }}">
    <td class="mis-td-utama">
        <div class="pdl-layanan">
            <span class="mis-medali kecil {{ $layananBaris['warna'] }}" aria-hidden="true">
                <i class="fas {{ $layananBaris['ikon'] }}"></i>
            </span>
            <div style="min-width: 0;">
                <p class="pdl-layanan-nama">{{ $layananBaris['nama'] }}</p>
                {{-- Nomor pendaftarannya di sini, bukan di kolom sendiri:
                     inilah yang disebut orang saat menghubungi panitia lewat
                     WhatsApp, tetapi ia tidak pernah dibaca sebagai kolom
                     berdiri sendiri — selalu bersama layanannya. --}}
                <span class="pdl-nomor">{{ $b->nomor ?: '—' }}</span>
            </div>
        </div>
    </td>

    <td data-judul="Pendaftar">
        <div class="pdl-orang">
            <p class="pdl-nama">
                {{ $b->nama_orang ?: 'Tanpa nama' }}
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
        <span class="pdl-sesi">{{ $sesiBaris ?: '—' }}</span>
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
                        @if ($b->layanan === 'webinar_eksklusif')
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

            @if ($keadaanBaris === 'lain' || $statusBeda)
                {{-- Status aslinya tetap bisa dilihat. Terjemahannya menyatukan
                     sembilan nilai jadi lima keadaan, jadi selisih antar layanan
                     — 'expired' di webinar vs 'Pendaftaran Dibatalkan' di camp —
                     hilang dari layar kalau nilainya tidak bisa dijangkau. --}}
                <span class="pdl-status-asli" title="Status asli yang tersimpan di basis data">{{ $b->status }}</span>
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
