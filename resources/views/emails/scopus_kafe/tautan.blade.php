{{--
  Tautan pemesanan yang dikirim kembali ke pemesannya.

  Isinya saja; rupanya dirakit kerangka bersama di emails/pendaftaran, supaya
  surat ini tidak bisa berbeda rupa dari surat bukti pemesanannya.

  SATU ALAMAT BISA PUNYA BEBERAPA PEMESANAN — orang yang sama memesan akhir
  pekan berikutnya, atau memesankan rekannya memakai emailnya sendiri.
  Seluruhnya dikirim, bukan yang terbaru saja: yang dicari belum tentu yang
  terakhir.
--}}
@php
    use App\Support\PendaftaranSemuaLayanan as Pendaftaran;

    $daftar = collect($pemesanan);
    $nama = trim((string) ($daftar->first()->nama ?? ''));

    $tautan = $daftar->map(function ($p) {
        $keadaan = Pendaftaran::pilihanStatus('scopus_kafe')[(string) $p->status]
            ?? (string) $p->status;

        $tanggal = $p->tanggal_pemesanan
            ? \Carbon\Carbon::parse($p->tanggal_pemesanan)->locale('id')->translatedFormat('j M Y')
            : null;

        return [
            'label' => strtoupper((string) $p->id_pemesanan)
                . ($tanggal ? ' — ' . $tanggal : '') . ' — ' . $keadaan,
            'teks' => 'Buka halaman pemesanan',
            'alamat' => route('public.scopuskafe.status', $p->getKey()),
        ];
    })->all();
@endphp
@include('emails.pendaftaran.kerangka', [
    'judul' => 'Tautan pemesanan Anda',
    'lencana' => null,
    'sapaan' => $nama !== '' ? $nama : 'Bapak/Ibu',
    'pembuka' => $daftar->count() === 1
        ? 'Berikut tautan halaman pemesanan Scopus Kafe Anda. Di halaman itu '
            . 'Bapak/Ibu dapat melihat keadaannya, menyelesaikan pembayaran, dan '
            . 'mengunduh fakturnya.'
        : 'Berikut ' . $daftar->count() . ' pemesanan Scopus Kafe yang terdaftar dengan '
            . 'alamat email ini.',
    'rincian' => [],
    'sorot' => [
        'judul' => 'Simpan tautannya',
        'isi' => 'Tautan di atas hanya berlaku untuk pemesanan Anda sendiri dan tidak '
            . 'bisa ditebak orang lain. Mohon jangan dibagikan.',
    ],
    'penutup' => [
        'Surat ini dikirim karena ada permintaan tautan dari halaman Scopus Kafe. '
            . 'Apabila bukan Bapak/Ibu yang memintanya, surat ini cukup diabaikan — '
            . 'tidak ada yang berubah pada pemesanannya.',
    ],
    'tautan' => $tautan,
])
