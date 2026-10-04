{{--
  Satu isian borang rincian pendaftaran.

  Yang harus dikirim pemanggilnya:
    $kolom     nama kolomnya
    $jenis     'teks' | 'uang' | 'angka' | 'tanggal' | 'waktu'
    $tulisan   label yang dibaca orang
    $nilai     nilai sekarang
    $penuh     true kalau isiannya memakai lebar penuh kisinya
    $rentang   berapa lajur yang ditempatinya (boleh tidak dikirim; bawaannya 1)
    $sembunyiLabel  true kalau labelnya mengulang judul bagiannya (boleh tidak dikirim)
    $uang      true kalau nominal — diberi awalan Rp di dalam kotaknya
    $angkatan  pilihan angkatan, hanya dipakai kolom kategori_id

  Satuan seperti Rp menempel DI DALAM kotaknya sebagai awalan, bukan jadi
  label sendiri — aturan yang sudah berlaku di layar Tarif layanan.
--}}
@php
    $id = 'rin-' . $kolom;

    /*
     * Nominal ditampilkan BERPEMISAH RIBUAN.
     *
     * Dulu polos — "82500022" — dan angka sepanjang itu praktis tidak bisa
     * dibaca sekilas; panitia yang mencocokkannya dengan mutasi rekening
     * harus menghitung digitnya satu per satu.
     *
     * Aman karena peladen MEMBUANG seluruh karakter bukan angka sebelum
     * menyimpan (lihat baca() di UbahDataPendaftaran), jadi titik maupun
     * awalan Rp tidak pernah ikut tersimpan.
     */
    $tampil = $uang ? number_format((int) $nilai, 0, ',', '.') : $nilai;

    /*
     * Peran isian ini di dalam penjumlahan Total bayar.
     *
     * Rumusnya mengikuti BuatPendaftaran: total = subtotal - potongan + kode
     * unik, dan PPN menambah. Yang ditulis di sini cuma TANDA-nya; subtotalnya
     * sendiri tidak pernah ditebak di muka — skrip di layar rincian
     * menurunkannya dari selisih nilai yang tersimpan, supaya membuka halaman
     * tidak mengubah angka apa pun.
     */
    $peranHitung = [
        'ppn' => 'tambah',
        'kode_unik' => 'tambah',
        'nominal_diskon' => 'kurang',
        'total_pembayaran' => 'hasil',
    ][$kolom] ?? null;

    $jenisKotak = match ($jenis) {
        'tanggal' => 'date',
        'waktu' => 'time',
        'angka' => 'number',
        default => 'text',
    };
@endphp
{{-- Rentangnya lewat gaya sebaris, bukan kelas: jumlah lajurnya dihitung
     per bagian dari jumlah isiannya, jadi nilainya tidak terbatas pada
     beberapa kelas yang ditulis di muka. --}}
<div class="mis-isian {{ $penuh ? 'rin-isian-penuh' : '' }}"
    @if (! $penuh && ($rentang ?? 1) > 1) style="grid-column: span {{ $rentang }};" @endif>
    @php
        /*
         * Ikon berwarna per isian.
         *
         * Kartu di tab Ringkasan penuh ikon berwarna, sementara borang di tab
         * lain polos sama sekali — kartunya sendiri sudah identik (bantalan,
         * radius, bayangan, tipografi judul semuanya sama terukur), yang
         * membuatnya terasa beda hanya isinya.
         *
         * Warnanya SENGAJA menyamai baris di kartu identitas sebelah kiri:
         * email biru, WhatsApp hijau, afiliasi ungu. Satu hal yang sama tidak
         * boleh berganti warna hanya karena dilihat di tab yang berbeda.
         *
         * Yang tidak terdaftar jatuh ke ikon netral — menambah medan baru
         * tidak pernah membuat barisnya kosong.
         */
        $petaIkon = [
            'nama' => ['fas fa-user', 'ungu'], 'nama_pemesan' => ['fas fa-user', 'ungu'],
            'email' => ['fas fa-envelope', 'biru'], 'email_pemesan' => ['fas fa-envelope', 'biru'],
            'telp' => ['fab fa-whatsapp', 'hijau'], 'telp_pemesan' => ['fab fa-whatsapp', 'hijau'],
            'affiliasi' => ['fas fa-building', 'ungu'], 'afiliasi_pemesan' => ['fas fa-building', 'ungu'],
            'note' => ['fas fa-sticky-note', 'kuning'],
            'kendala' => ['fas fa-exclamation-triangle', 'merah'],
            'desc_kendala' => ['fas fa-exclamation-triangle', 'merah'],
            'kategori_id' => ['fas fa-layer-group', 'jingga'],
            'jumlah_pendaftar' => ['fas fa-users', 'ungu'],
            'ppn' => ['fas fa-percent', 'biru'],
            'kode_unik' => ['fas fa-hashtag', 'biru'],
            'kode_unik_pembayaran' => ['fas fa-hashtag', 'biru'],
            'kode_unik_pembayaran_kedua' => ['fas fa-hashtag', 'biru'],
            'kode_unik_pembayaran_ketiga' => ['fas fa-hashtag', 'biru'],
            'kode_diskon' => ['fas fa-tag', 'kuning'],
            'nominal_diskon' => ['fas fa-tag', 'kuning'],
            'group_wa' => ['fab fa-whatsapp', 'hijau'],
            'tanggal_pemesanan' => ['fas fa-calendar-alt', 'jingga'],
            'tanggal_reschedule' => ['fas fa-calendar-alt', 'jingga'],
        ];

        $rupaIkon = $petaIkon[$kolom] ?? null;

        if ($rupaIkon === null) {
            // Dikelompokkan dari AWALAN namanya: sesi kedua dan ketiga memakai
            // kolom bernama sama berakhiran _kedua/_ketiga, dan mendaftarkan
            // ketiganya satu per satu berarti tiga tempat yang harus sepakat.
            $rupaIkon = match (true) {
                str_starts_with($kolom, 'sesi') => ['fas fa-clipboard-list', 'jingga'],
                str_starts_with($kolom, 'jam_') => ['fas fa-clock', 'kuning'],
                str_starts_with($kolom, 'waktu_') => ['fas fa-clock', 'kuning'],
                str_starts_with($kolom, 'lokasi') => ['fas fa-map-marker-alt', 'merah'],
                str_starts_with($kolom, 'biaya') => ['fas fa-money-bill-wave', 'hijau'],
                str_starts_with($kolom, 'subtotal') => ['fas fa-money-bill-wave', 'hijau'],
                str_starts_with($kolom, 'total') => ['fas fa-money-bill-wave', 'hijau'],
                default => ['fas fa-pen', 'abu'],
            };
        }
    @endphp

    {{-- Labelnya tetap ADA untuk pembaca layar walau disembunyikan dari mata:
         isian tanpa label sama sekali tidak bisa dikenali pemakainya. --}}
    <label class="mis-label rin-label {{ ($sembunyiLabel ?? false) ? 'sr-only' : '' }}" for="{{ $id }}">
        @if (! ($sembunyiLabel ?? false))
            <span class="mis-medali mini mis-{{ $rupaIkon[1] }}" aria-hidden="true">
                <i class="{{ $rupaIkon[0] }}"></i>
            </span>
        @endif
        <span>{{ $tulisan }}</span>
    </label>

    @if ($kolom === 'kategori_id')
        <select class="form-control-modern" id="{{ $id }}" name="{{ $kolom }}" required>
            @foreach ($angkatan as $a)
                <option value="{{ $a->id }}" @selected((string) $nilai === (string) $a->id)>
                    {{ \Illuminate\Support\Str::limit($a->nama, 60) }}@if ($a->total_kuota !== null) &mdash; sisa {{ (int) $a->sisa_kuota }}/{{ (int) $a->total_kuota }}@endif
                </option>
            @endforeach
        </select>
        {{-- Pilihannya DIBATASI angkatan layanan ini; memindahkan pendaftaran
             ke angkatan layanan lain membuat kuota keduanya salah tanpa ada
             yang menolak. --}}
        <p class="mis-bantuan">Hanya angkatan layanan ini yang bisa dipilih.</p>
    @elseif ($kolom === 'note' || $kolom === 'desc_kendala')
        {{-- data-mis-tumbuh: tingginya mengikuti isi, lihat skrip di layar
             rincian. rows="3" tetap ditulis sebagai lantai dan sebagai
             keadaan yang masuk akal kalau skripnya tidak jalan. --}}
        <textarea class="form-control-modern" id="{{ $id }}" name="{{ $kolom }}" rows="3"
            data-mis-tumbuh>{{ $tampil }}</textarea>
    @else
        @if ($uang)
            {{-- "Rp" MENEMPEL di dalam kotaknya, bukan jadi label terpisah.
                 Kepala berkas ini sudah menjanjikannya sejak lama, tetapi
                 yang ada hanya kalimat bantuan "Dalam rupiah" di bawah kotak
                 — dan kalimat di bawah kotak tidak terbaca saat mata sedang
                 di dalam kotaknya. --}}
            <span class="rin-uang-kotak">
                <span class="rin-uang-awalan" aria-hidden="true">Rp</span>
                <input type="{{ $jenisKotak }}" class="form-control-modern rin-uang-isian"
                    id="{{ $id }}" name="{{ $kolom }}" value="{{ $tampil }}"
                    inputmode="numeric" data-mis-rupiah
                    @if ($peranHitung) data-mis-hitung="{{ $peranHitung }}" @endif>
            </span>
            @if ($peranHitung === 'hasil')
                {{-- SATU-SATUNYA nominal yang diberi kalimat bantuan.

                     Angka yang berubah sendiri tanpa keterangan membuat orang
                     ragu apakah ia sempat salah ketik; kalimat ini yang
                     memberitahunya bahwa itu memang hitungan, dan tautannya
                     jalan pulang kalau totalnya sempat diketik tangan. --}}
                <p class="mis-bantuan rin-hitung-nota" data-mis-hitung-nota>
                    <span data-mis-hitung-nota-otomatis>Dihitung sendiri dari PPN, kode unik, dan potongan di atas.</span>
                    <span data-mis-hitung-nota-tangan hidden>Diisi tangan, tidak ikut berubah lagi.
                        <button type="button" class="rin-hitung-ulang" data-mis-hitung-ulang>Hitung sendiri lagi</button>
                    </span>
                </p>
            @endif

            {{-- Tidak ada kalimat bantuan di sini.

                 Dulu tertulis "Dalam rupiah, tanpa titik" — dan itu memang
                 perlu saat kotaknya polos. Sejak "Rp" menempel di dalamnya
                 dan titiknya terlihat sambil diketik, kalimat itu tidak
                 menjelaskan apa pun lagi; ia hanya terulang di bawah SETIAP
                 nominal dan membungkus jadi dua baris. --}}
        @else
            <input type="{{ $jenisKotak }}" class="form-control-modern" id="{{ $id }}" name="{{ $kolom }}"
                value="{{ $tampil }}"
                @if ($jenis === 'angka') min="1" max="99" @endif
                @if (in_array($kolom, ['nama', 'nama_pemesan', 'email', 'email_pemesan', 'telp', 'telp_pemesan'], true)) required @endif>
        @endif
    @endif
</div>
