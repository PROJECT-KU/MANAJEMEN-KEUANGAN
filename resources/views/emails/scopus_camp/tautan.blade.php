{{--
  Tautan pendaftaran yang dikirim kembali ke pendaftarnya.

  Isinya saja; rupanya dirakit kerangka bersama di emails/pendaftaran, supaya
  surat ini tidak bisa berbeda rupa dari surat bukti yang sudah berjalan.

  SATU ALAMAT BISA PUNYA BEBERAPA PENDAFTARAN — orang yang sama mendaftar
  angkatan berikutnya, atau mendaftarkan rekannya memakai emailnya sendiri.
  Seluruhnya dikirim, bukan yang terbaru saja: yang dicari belum tentu yang
  terakhir, dan menebak salah satunya berarti ia tetap kehilangan yang lain.
--}}
@php
    use App\Support\PendaftaranSemuaLayanan as Pendaftaran;

    $daftar = collect($pendaftaran);

    $nama = trim((string) ($daftar->first()->nama ?? ''));

    /*
     * Tombolnya satu per pendaftaran, berlabel nomor DAN keadaannya.
     *
     * Tanpa keadaannya, orang yang punya tiga pendaftaran harus membuka
     * ketiganya untuk menemukan mana yang belum dibayar — dan yang paling
     * sering ia cari justru itu.
     */
    $tautan = $daftar->map(function ($p) {
        $keadaan = Pendaftaran::pilihanStatus('scopus_camp')[(string) $p->status]
            ?? (string) $p->status;

        return [
            'label' => strtoupper((string) $p->id_transaksi) . ' — ' . $keadaan,
            'teks' => 'Buka halaman pendaftaran',
            'alamat' => route('public.scopuscamp.status', $p->getKey()),
        ];
    })->all();
@endphp
@include('emails.pendaftaran.kerangka', [
    'judul' => 'Tautan pendaftaran Anda',
    'lencana' => null,
    'sapaan' => $nama !== '' ? $nama : 'Bapak/Ibu',
    'pembuka' => $daftar->count() === 1
        ? 'Berikut tautan halaman pendaftaran Scopus Camp Anda. Di halaman itu '
            . 'Bapak/Ibu dapat melihat keadaannya, menyelesaikan pembayaran, dan '
            . 'mengunduh fakturnya.'
        : 'Berikut ' . $daftar->count() . ' pendaftaran Scopus Camp yang terdaftar '
            . 'dengan alamat email ini. Di tiap halaman Bapak/Ibu dapat melihat '
            . 'keadaannya, menyelesaikan pembayaran, dan mengunduh fakturnya.',
    'rincian' => [],
    'sorot' => [
        'judul' => 'Simpan tautannya',
        'isi' => 'Tautan di atas hanya berlaku untuk pendaftaran Anda sendiri dan tidak '
            . 'bisa ditebak orang lain. Mohon jangan dibagikan.',
    ],
    'penutup' => [
        'Surat ini dikirim karena ada permintaan tautan dari halaman Scopus Camp. '
            . 'Apabila bukan Bapak/Ibu yang memintanya, surat ini cukup diabaikan — '
            . 'tidak ada yang berubah pada pendaftarannya.',
    ],
    'tautan' => $tautan,
])
