{{--
  Pengingat pembayaran, untuk kelima layanan.

  Isinya saja; rupanya dirakit kerangka bersama di emails/pendaftaran — sama
  persis dengan surat perubahan status. Nama kolomnya diambil dari KATALOG,
  bukan diketik di sini: kelima layanan menyimpan nomor, nama, dan totalnya
  di kolom bernama berbeda, dan menuliskan salah satunya berarti sebagian
  layanan mengirim surat berisi baris kosong.
--}}
@php
    use App\Support\PendaftaranSemuaLayanan as Pendaftaran;

    $ambil = fn (string $peran) => Pendaftaran::nilaiKolom($pendaftaran, $layanan, $peran);

    // Peran 'nomor' Clinik Scopus berupa ungkapan SQL, jadi nilaiKolom()
    // memulangkan null untuknya; tanpa cadangan ini suratnya terbit tanpa
    // nomor pendaftaran sama sekali.
    $nomor = $ambil('nomor')
        ?: ($pendaftaran->{Pendaftaran::kolomNomor($layanan) ?: 'id'} ?? null);
    $nama = $ambil('nama_orang');
    $total = (int) ($ambil('total') ?: 0);
    $kodeUnik = (int) ($ambil('kode_unik') ?: 0);

    $rincian = array_filter([
        'Layanan' => Pendaftaran::katalog()[$layanan]['nama'] ?? null,
        'Nomor pendaftaran' => $nomor ? strtoupper((string) $nomor) : null,
        'Nama' => $nama ?: null,
        'Total pembayaran' => $total > 0 ? 'Rp ' . number_format($total, 0, ',', '.') : null,
    ], fn ($v) => $v !== null && $v !== '');

    $pembuka = 'Pendaftaran Anda sudah kami terima, tetapi pembayarannya belum masuk'
        . ($hariBerlalu > 0 ? ' sampai ' . $hariBerlalu . ' hari sejak Anda mendaftar' : '')
        . '. Kursi Anda masih kami simpan — surat ini hanya pengingat supaya tidak terlewat.';

    /*
     * Kode uniknya disorot, bukan diselipkan di tabel rincian.
     *
     * Angka satuan itulah yang membedakan transfer satu pendaftar dari
     * pendaftar lain yang nominalnya sama; dibulatkan, pembayarannya tidak
     * bisa dicocokkan dan pendaftarnya tetap tercatat belum bayar meski
     * uangnya sudah masuk.
     */
    $sorot = ($total > 0 && $kodeUnik > 0) ? [
        'judul' => 'Nominalnya harus persis',
        'isi' => 'Transfer tepat Rp ' . number_format($total, 0, ',', '.')
            . ' — jangan dibulatkan. Tiga angka terakhirnya kode unik Anda, dan itulah'
            . ' yang kami pakai untuk mencocokkan pembayaran Anda.',
    ] : null;
@endphp
@include('emails.pendaftaran.kerangka', [
    'judul' => 'Pembayaran Anda belum kami terima',
    'lencana' => ['teks' => 'Menunggu pembayaran', 'latar' => '#fef3c7', 'tinta' => '#92400e'],
    'sapaan' => $nama ?: 'Bapak/Ibu',
    'pembuka' => $pembuka,
    'rincian' => $rincian,
    'sorot' => $sorot,
    'penutup' => [
        'Sudah terlanjur membayar? Abaikan surat ini. Panitia memperbarui status'
            . ' pendaftaran Anda setelah pembayarannya dicocokkan, dan Anda akan'
            . ' menerima surat pemberitahuannya.',
        'Kalau ada kendala atau Anda ingin membatalkan, balas surat ini — panitia'
            . ' akan membantu.',
    ],
])
