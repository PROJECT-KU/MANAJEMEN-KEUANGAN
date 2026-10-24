{{--
  Bukti pendaftaran Analisis Bibliometrik.

  SELURUH susunannya di emails/pendaftaran/bukti.blade.php, dipakai bersama
  semua layanan. Berkas ini hanya menyiapkan isinya — lihat alasan
  penyeragamannya di kerangka itu.

  Yang dikirim AnalisisBibliometrikMail: $analisisbibliometrik,
  $categoriesanalisisbibliometrik, $appName.
--}}
@php
    $pendaftaran = $analisisbibliometrik;
    $angkatan = $categoriesanalisisbibliometrik;

    $lunas = in_array((string) $pendaftaran->status,
        \App\Support\PendaftaranSemuaLayanan::KEADAAN['lunas']['nilai'], true);

    $belumBayar = ! $lunas;

    $namaKelas = trim($angkatan->nama . ' ' . ($angkatan->nama_ke ? '#' . $angkatan->nama_ke : ''));

    $rincian = [
        ['label' => 'Angkatan', 'nilai' => $namaKelas],
        ['label' => 'Tanggal', 'nilai' => \App\Support\RentangTanggal::tulis(
            $angkatan->mulai ? \Carbon\Carbon::parse($angkatan->mulai) : null,
            $angkatan->selesai ? \Carbon\Carbon::parse($angkatan->selesai) : null
        ) ?: 'Menyusul'],
        ['label' => 'Atas nama', 'nilai' => $pendaftaran->nama],
        ['label' => 'Jumlah peserta', 'nilai' => (int) $pendaftaran->jumlah_pendaftar . ' orang'],
    ];

    // Baris "Rp 0" terbaca sebagai potongan yang GAGAL dipakai, bukan sebagai
    // potongan yang memang tidak diminta — jadi hanya disebut kalau ada.
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
@endphp

@include('emails.pendaftaran.bukti', [
    'judul' => 'Data Anda sudah kami terima',
    'ringkas' => $belumBayar
        ? 'Pendaftaran Anda tercatat. Tinggal satu langkah lagi: pembayaran.'
        : 'Pendaftaran Anda lengkap. Sampai jumpa di kelasnya.',
    'lencana' => $belumBayar
        ? ['teks' => 'BELUM DIBAYAR', 'latar' => '#fff7ed', 'garis' => '#fed7aa', 'tinta' => '#9a3412']
        : ['teks' => 'SUDAH DIBAYAR', 'latar' => '#ecfdf5', 'garis' => '#a7f3d0', 'tinta' => '#065f46'],
    'sapaan' => $pendaftaran->nama,
    'pembuka' => 'terima kasih sudah mendaftar <strong style="color:#0f2b5b;">'
        . e($namaKelas) . '</strong>.',
    'nomor' => $pendaftaran->id_transaksi,
    'rincian' => $rincian,
    'total' => [
        'label' => $belumBayar ? 'Total bayar' : 'Sudah dibayar',
        'nilai' => 'Rp ' . number_format((int) $pendaftaran->total_pembayaran, 0, ',', '.'),
    ],
    'langkah' => $belumBayar
        ? ['judul' => 'Langkah berikutnya', 'daftar' => [
            'Transfer <strong>tepat sampai angka terakhir</strong> sesuai total di atas.'
                . ((int) $pendaftaran->kode_unik > 0
                    ? ' Angka terakhirnya kode unik Anda — kalau dibulatkan, pembayarannya '
                        . 'tidak bisa kami cocokkan.'
                    : ''),
            'Kirim bukti transfernya ke panitia lewat WhatsApp.',
            'Panitia memeriksanya, lalu mengirim kabar penerimaan ke email ini.',
        ]]
        : null,
    'catatan' => $belumBayar
        ? ['<strong style="color:#0f2b5b;">Fakturnya terlampir</strong> di email ini sebagai '
            . 'berkas PDF, lengkap dengan jumlah dan rinciannya.']
        : [],
    'kaki' => ['Pengumuman teknis dibagikan panitia lewat grup peserta di WhatsApp.'],
])
