{{--
  Bukti pendaftaran Webinar Eksklusif.

  SELURUH susunannya kini di emails/pendaftaran/bukti.blade.php. Surat INILAH
  acuan desain yang diminta pemiliknya untuk semua layanan 24 Okt 2026 —
  susunannya diangkat apa adanya ke kerangka itu, lalu surat ini ikut
  memakainya.

  Diangkat, bukan disalin: dua salinan desain yang sama akan menyimpang
  perlahan, dan yang pertama menyimpang selalu yang jarang dibuka. Dengan
  satu kerangka, memperbaiki satu surat memperbaiki kelimanya.

  Yang dikirim WebinarEksklusifPendaftaranMail: $pendaftaran, $sesi,
  $tautanStatus.
--}}
@php
    $belumBayar = ! $pendaftaran->lunas;

    $rincian = [
        ['label' => 'Tanggal', 'nilai' => \App\Support\RentangTanggal::tulis(
            $sesi->mulai ? \Carbon\Carbon::parse($sesi->mulai) : null,
            $sesi->selesai ? \Carbon\Carbon::parse($sesi->selesai) : null
        ) ?: 'Menyusul'],
        ['label' => 'Jam', 'nilai' => $sesi->jam ?: 'Menyusul'],
        ['label' => 'Lewat', 'nilai' => $sesi->platform ?: 'Daring'],
        ['label' => 'Atas nama', 'nilai' => $pendaftaran->nama],
        ['label' => 'Jumlah peserta', 'nilai' => $pendaftaran->jumlah_pendaftar . ' orang'],
    ];

    if ((int) $pendaftaran->nominal_diskon > 0) {
        $rincian[] = [
            'label' => 'Potongan (' . $pendaftaran->kode_diskon . ')',
            'nilai' => 'Rp ' . number_format((int) $pendaftaran->nominal_diskon, 0, ',', '.'),
            'warna' => '#0f9b74',
            'awalan' => '&minus; ',
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
     * Langkahnya BERCABANG menurut cara bayarnya.
     *
     * Sejak transfer manual dicabut, pendaftaran baru selalu lewat gerbang
     * pembayaran — dan menyuruhnya "transfer lalu kirim bukti ke WhatsApp"
     * berarti mengarahkan uang ke rekening yang tidak lagi dicocokkan siapa
     * pun. Cabang transfernya DIPERTAHANKAN: pendaftaran lama masih ada dan
     * suratnya masih bisa dikirim ulang.
     */
    $langkah = null;

    if ($belumBayar) {
        $langkah = ['judul' => 'Langkah berikutnya', 'daftar' => $pendaftaran->cara_bayar === 'transfer'
            ? [
                'Buka halaman status lewat tombol di bawah.',
                'Transfer <strong>tepat sampai angka terakhir</strong> sesuai nominal di sana.'
                    . ((int) $pendaftaran->kode_unik > 0
                        ? ' Angka terakhirnya kode unik Anda — kalau dibulatkan, pembayarannya '
                            . 'tidak bisa kami cocokkan.'
                        : ''),
                'Kirim bukti transfernya ke panitia lewat WhatsApp.',
            ]
            : [
                'Buka halaman status lewat tombol di bawah.',
                'Selesaikan pembayarannya di halaman itu — bisa lewat transfer bank '
                    . '(virtual account), QRIS, e-wallet, atau kartu.',
                'Begitu pembayarannya masuk, status Anda berubah sendiri dan kami kirimkan '
                    . 'surat penanda lunas. Tidak perlu mengirim bukti apa pun.',
            ]];
    }
@endphp

@include('emails.pendaftaran.bukti', [
    'judul' => 'Data Anda sudah kami terima',
    'ringkas' => $belumBayar
        ? 'Kursi Anda ditahan sementara. Tinggal satu langkah lagi: pembayaran.'
        : 'Pendaftaran Anda lengkap. Sampai jumpa di sesinya.',
    'lencana' => $belumBayar
        ? ['teks' => 'BELUM DIBAYAR', 'latar' => '#fff7ed', 'garis' => '#fed7aa', 'tinta' => '#9a3412']
        : ['teks' => 'SUDAH DIBAYAR', 'latar' => '#ecfdf5', 'garis' => '#a7f3d0', 'tinta' => '#065f46'],
    'sapaan' => $pendaftaran->nama,
    'pembuka' => 'terima kasih sudah mendaftar <strong style="color:#0f2b5b;">'
        . e($sesi->nama) . '</strong>.',
    'nomor' => $pendaftaran->id_transaksi,
    'rincian' => $rincian,
    'total' => ['label' => 'Total bayar',
        'nilai' => 'Rp ' . number_format((int) $pendaftaran->total_pembayaran, 0, ',', '.')],
    'peserta' => $pendaftaran->semuaPeserta(),
    'langkah' => $langkah,

    /*
     * LAMPIRANNYA DISEBUT. Lampiran yang tidak disebut tidak dibuka, dan
     * faktur yang tidak pernah dibuka sama saja dengan tidak dikirim —
     * terutama bagi yang harus menyerahkannya ke bendahara kantornya lebih
     * dulu supaya biayanya disetujui.
     *
     * Hanya selama belum lunas, sama seperti lampirannya sendiri.
     */
    'catatan' => $belumBayar
        ? ['<strong style="color:#0f2b5b;">Fakturnya terlampir</strong> di email ini sebagai '
            . 'berkas PDF, lengkap dengan jumlah dan batas waktunya.']
        : [],
    'tombol' => [
        'teks' => $belumBayar ? 'Lihat cara pembayaran' : 'Lihat rincian pendaftaran',
        'url' => $tautanStatus,
    ],
    'tenggat' => $pendaftaran->kedaluwarsa_pada && $belumBayar
        ? 'Kursi Anda ditahan sampai <strong>'
            . $pendaftaran->kedaluwarsa_pada->locale('id')->translatedFormat('d F Y, H:i')
            . ' WIB</strong>.'
        : null,
    'kaki' => ['Tautan masuk sesinya dibagikan panitia lewat grup peserta di WhatsApp.'],
])
