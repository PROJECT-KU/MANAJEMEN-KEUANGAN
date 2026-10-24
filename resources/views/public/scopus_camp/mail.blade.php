{{--
  Bukti pendaftaran Scopus Camp.

  SELURUH susunannya di emails/pendaftaran/bukti.blade.php, dipakai bersama
  semua layanan. Berkas ini hanya menyiapkan isinya.

  Sebelum 24 Okt 2026 ia punya desain sendiri: logo MIS biru, kepala biru
  muda, dan susunan yang sama sekali berbeda dari surat Webinar Eksklusif.
  Satu pelanggan yang memesan dua layanan menerima dua surat yang tidak
  terlihat berasal dari tempat yang sama. Diseragamkan atas permintaan
  pemiliknya.

  Yang dikirim ScopusCampMail: $pendaftaran, $categoriesScopusCamp, $appName.
--}}
@php
    $angkatan = $categoriesScopusCamp;

    /*
     * "Sudah dibayar" ditentukan STATUSNYA, bukan ada-tidaknya bukti.
     *
     * Surat ini dikirim tepat saat pendaftaran dibuat — jadi lewat gerbang
     * pembayaran ia selalu belum lunas, dan lewat transfer manual ia menunggu
     * panitia memeriksa buktinya. Keduanya sama-sama "belum".
     */
    $lunas = in_array((string) $pendaftaran->status,
        \App\Support\PendaftaranSemuaLayanan::KEADAAN['lunas']['nilai'], true);

    $belumBayar = ! $lunas;

    $rincian = [
        ['label' => 'Kelas', 'nilai' => trim($angkatan->nama . ' ' . ($angkatan->nama_ke ? '#' . $angkatan->nama_ke : ''))],
        ['label' => 'Tanggal', 'nilai' => \App\Support\RentangTanggal::tulis(
            $angkatan->mulai ? \Carbon\Carbon::parse($angkatan->mulai) : null,
            $angkatan->selesai ? \Carbon\Carbon::parse($angkatan->selesai) : null
        ) ?: 'Menyusul'],
        ['label' => 'Lokasi', 'nilai' => $angkatan->lokasi ?: 'Akan dikabari panitia'],
        ['label' => 'Atas nama', 'nilai' => $pendaftaran->nama],
        ['label' => 'Jumlah peserta', 'nilai' => (int) $pendaftaran->jumlah_pendaftar . ' orang'],
    ];

    /*
     * Potongan dan kode unik hanya disebut kalau memang ada. Baris "Rp 0"
     * terbaca sebagai potongan yang GAGAL dipakai, bukan sebagai potongan
     * yang memang tidak diminta.
     */
    if ((int) $pendaftaran->nominal_diskon > 0) {
        $rincian[] = [
            'label' => 'Potongan' . ($pendaftaran->kode_diskon ? ' (' . $pendaftaran->kode_diskon . ')' : ''),
            'nilai' => 'Rp ' . number_format((int) $pendaftaran->nominal_diskon, 0, ',', '.'),
            'warna' => '#0f9b74',
            'awalan' => '&minus; ',
        ];
    }

    if ((int) $pendaftaran->ppn > 0) {
        $rincian[] = [
            'label' => 'PPN',
            'nilai' => 'Rp ' . number_format((int) $pendaftaran->ppn, 0, ',', '.'),
        ];
    }

    if ((int) $pendaftaran->kode_unik > 0) {
        $rincian[] = [
            'label' => 'Kode unik',
            'nilai' => 'Rp ' . number_format((int) $pendaftaran->kode_unik, 0, ',', '.'),
            'warna' => '#ff6a00',
            'awalan' => '+ ',
        ];
    }

    /*
     * Langkahnya BERCABANG menurut cara bayarnya. Menyuruh yang lewat gerbang
     * "transfer lalu kirim bukti" berarti mengarahkan uang ke rekening yang
     * tidak lagi dicocokkan siapa pun — dan sebaliknya, menyuruh yang
     * transfer manual "selesaikan di halaman status" menunjuk tombol yang
     * memang tidak ada baginya.
     */
    $langkah = null;

    if ($belumBayar) {
        $langkah = ['judul' => 'Langkah berikutnya', 'daftar' => $pendaftaran->cara_bayar === 'doku'
            ? [
                'Buka halaman status lewat tombol di bawah.',
                'Selesaikan pembayarannya di sana — lewat QRIS atau Virtual Account.',
                'Begitu pembayarannya masuk, status Anda berubah sendiri. '
                    . 'Tidak perlu mengirim bukti apa pun.',
            ]
            : [
                'Transfer <strong>tepat sampai angka terakhir</strong> sesuai total di atas.'
                    . ((int) $pendaftaran->kode_unik > 0
                        ? ' Angka terakhirnya kode unik Anda — kalau dibulatkan, pembayarannya '
                            . 'tidak bisa kami cocokkan.'
                        : ''),
                'Kirim bukti transfernya ke panitia lewat WhatsApp.',
                'Panitia memeriksanya, lalu mengirim kabar penerimaan ke email ini.',
            ]];
    }
@endphp

@include('emails.pendaftaran.bukti', [
    'judul' => 'Data Anda sudah kami terima',
    'ringkas' => $belumBayar
        ? 'Kursi Anda ditahan sementara. Tinggal satu langkah lagi: pembayaran.'
        : 'Pendaftaran Anda lengkap. Sampai jumpa di kelasnya.',
    'lencana' => $belumBayar
        ? ['teks' => 'BELUM DIBAYAR', 'latar' => '#fff7ed', 'garis' => '#fed7aa', 'tinta' => '#9a3412']
        : ['teks' => 'SUDAH DIBAYAR', 'latar' => '#ecfdf5', 'garis' => '#a7f3d0', 'tinta' => '#065f46'],
    'sapaan' => $pendaftaran->nama,
    'pembuka' => 'terima kasih sudah mendaftar <strong style="color:#0f2b5b;">'
        . e(trim($angkatan->nama . ' ' . ($angkatan->nama_ke ? '#' . $angkatan->nama_ke : '')))
        . '</strong>.',
    'nomor' => $pendaftaran->id_transaksi,
    'rincian' => $rincian,
    'total' => [
        'label' => $belumBayar ? 'Total bayar' : 'Sudah dibayar',
        'nilai' => 'Rp ' . number_format((int) $pendaftaran->total_pembayaran, 0, ',', '.'),
    ],
    'peserta' => $pendaftaran->semuaPeserta(),
    'langkah' => $langkah,
    'catatan' => $belumBayar
        ? ['<strong style="color:#0f2b5b;">Fakturnya terlampir</strong> di email ini sebagai '
            . 'berkas PDF, lengkap dengan jumlah dan rinciannya.']
        : [],
    'tombol' => [
        'teks' => $belumBayar ? 'Lihat cara pembayaran' : 'Lihat rincian pendaftaran',
        'url' => route('public.scopuscamp.status', $pendaftaran->getKey()),
    ],
    'tenggat' => $belumBayar && $pendaftaran->kedaluwarsa_pada
        ? 'Kursi Anda ditahan sampai <strong>'
            . $pendaftaran->kedaluwarsa_pada->locale('id')->translatedFormat('d F Y, H:i')
            . ' WIB</strong>.'
        : null,
    'kaki' => ['Pengumuman teknis dibagikan panitia lewat grup peserta di WhatsApp.'],
])
