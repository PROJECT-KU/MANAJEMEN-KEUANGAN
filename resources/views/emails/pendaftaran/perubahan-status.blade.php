{{--
  Surat perubahan status, untuk kelima layanan.

  Isinya saja; rupanya dirakit kerangka bersama di emails/pendaftaran. Dengan
  begitu surat baru ini TIDAK bisa berbeda rupa dari surat yang sudah
  berjalan — logo yayasan di tengah, judul di tengah, rinciannya tabel dua
  lajur, kakinya di tengah. Yang membuatnya seragam strukturnya, bukan
  kedisiplinan menyalin.

  Rinciannya dirakit dari KATALOG, bukan dari nama kolom yang diketik di sini:
  kelima layanan menyimpan nomor, nama, dan totalnya di kolom bernama berbeda
  (`id_transaksi` vs `id_pemesanan`, `nama` vs `nama_pemesan`), dan menuliskan
  salah satunya berarti sebagian layanan mengirim surat berisi baris kosong.
--}}
@php
    use App\Support\PendaftaranSemuaLayanan as Pendaftaran;

    $ambil = fn (string $peran) => Pendaftaran::nilaiKolom($pendaftaran, $layanan, $peran);

    /*
     * Nomornya punya cadangan.
     *
     * Peran 'nomor' Clinik Scopus berupa UNGKAPAN SQL
     * (COALESCE(id_transaksi, kode_booking)) — berarti hanya di dalam kueri,
     * dan nilaiKolom() memulangkan null untuknya. Tanpa cadangan ini, surat
     * Clinik Scopus terbit tanpa nomor pendaftaran sama sekali, dan justru
     * nomor itu yang disebut orang saat bertanya ke panitia.
     */
    $nomor = $ambil('nomor')
        ?: ($pendaftaran->{Pendaftaran::kolomNomor($layanan) ?: 'id'} ?? null);
    $nama = $ambil('nama_orang');
    $total = (int) ($ambil('total') ?: 0);

    $rincian = array_filter([
        'Layanan' => Pendaftaran::katalog()[$layanan]['nama'] ?? null,
        'Nomor pendaftaran' => $nomor ? strtoupper((string) $nomor) : null,
        'Nama' => $nama ?: null,
        // Nol tidak ditampilkan: untuk layanan yang nominalnya belum dihitung,
        // "Rp 0" terbaca seperti tagihan yang memang nol.
        'Total pembayaran' => $total > 0 ? 'Rp ' . number_format($total, 0, ',', '.') : null,
    ], fn ($v) => $v !== null && $v !== '');
@endphp
@include('emails.pendaftaran.kerangka', [
    'judul' => $kabar['judul'],
    'lencana' => $kabar['lencana'],
    'sapaan' => $nama ?: 'Bapak/Ibu',
    'pembuka' => $kabar['pembuka'],
    'rincian' => $rincian,
    'sorot' => null,
    'penutup' => $kabar['penutup'],
])
