{{--
  Bukti pemesanan Scopus Kafe.

  SELURUH susunannya di emails/pendaftaran/bukti.blade.php, dipakai bersama
  semua layanan. Berkas ini hanya menyiapkan isinya.

  Versi sebelumnya yang PALING jauh dari sampai: ia memuat Bootstrap,
  FontAwesome, style.css, components.css, bahkan sweetalert2 lewat <link>,
  lalu menata isinya dengan kelas .card di dalam blok <style>. Gmail membuang
  <link> DAN <style> sama sekali — jadi yang benar-benar diterima orangnya
  teks telanjang tanpa warna, tanpa kartu, tanpa logo. Diseragamkan atas
  permintaan pemiliknya 24 Okt 2026.

  Yang dikirim CreatePublicPendaftaranScopusKafeMail: $datas, $appName.
--}}
@php
    $pendaftaran = $datas;

    $lunas = in_array((string) $pendaftaran->status,
        \App\Support\PendaftaranSemuaLayanan::KEADAAN['lunas']['nilai'], true);

    $belumBayar = ! $lunas;

    /*
     * Scopus Kafe menyimpan SAMPAI TIGA SESI dalam satu baris, masing-masing
     * dengan kolomnya sendiri (sesi, sesi_kedua, sesi_ketiga). Ketiganya
     * disebut sebagai barisnya sendiri; yang kosong dilewati.
     */
    $rincian = [];

    $sesiKe = [
        ['sesi', 'waktu_mulai', 'waktu_selesai', 'lokasi'],
        ['sesi_kedua', 'waktu_mulai_kedua', 'waktu_selesai_kedua', 'lokasi_kedua'],
        ['sesi_ketiga', 'waktu_mulai_ketiga', 'waktu_selesai_ketiga', 'lokasi_ketiga'],
    ];

    foreach ($sesiKe as $ke => [$kNama, $kMulai, $kSelesai, $kLokasi]) {
        $nama = trim((string) $pendaftaran->{$kNama});

        if ($nama === '') {
            continue;
        }

        $jam = trim(implode(' - ', array_filter([
            trim((string) $pendaftaran->{$kMulai}),
            trim((string) $pendaftaran->{$kSelesai}),
        ])));

        $lokasi = trim((string) $pendaftaran->{$kLokasi});

        $rincian[] = [
            'label' => 'Sesi ' . ($ke + 1),
            'nilai' => $nama . ($jam !== '' ? ' · ' . $jam : '') . ($lokasi !== '' ? ' · ' . $lokasi : ''),
        ];
    }

    $rincian[] = ['label' => 'Atas nama', 'nilai' => $pendaftaran->nama];

    if (trim((string) $pendaftaran->telp) !== '') {
        $rincian[] = [
            'label' => 'Nomor WhatsApp',
            'nilai' => \App\Support\NomorTelepon::tampil($pendaftaran->telp),
        ];
    }

    if ($pendaftaran->tanggal_pemesanan) {
        $rincian[] = [
            'label' => 'Tanggal pemesanan',
            'nilai' => \Carbon\Carbon::parse($pendaftaran->tanggal_pemesanan)
                ->locale('id')->translatedFormat('j F Y'),
        ];
    }
@endphp

@include('emails.pendaftaran.bukti', [
    'judul' => 'Pemesanan Anda sudah kami terima',
    'ringkas' => $belumBayar
        ? 'Pemesanan Anda tercatat. Tinggal satu langkah lagi: pembayaran.'
        : 'Pemesanan Anda lengkap. Sampai jumpa di sesinya.',
    'lencana' => $belumBayar
        ? ['teks' => 'BELUM DIBAYAR', 'latar' => '#fff7ed', 'garis' => '#fed7aa', 'tinta' => '#9a3412']
        : ['teks' => 'SUDAH DIBAYAR', 'latar' => '#ecfdf5', 'garis' => '#a7f3d0', 'tinta' => '#065f46'],
    'sapaan' => $pendaftaran->nama,
    'pembuka' => 'terima kasih sudah memesan <strong style="color:#0f2b5b;">Scopus Kafe</strong>.',
    'nomor' => $pendaftaran->id_pemesanan,
    'rincian' => $rincian,
    'total' => [
        'label' => $belumBayar ? 'Total bayar' : 'Sudah dibayar',
        'nilai' => 'Rp ' . number_format((int) $pendaftaran->total_keseluruhan_pembayaran, 0, ',', '.'),
    ],
    'langkah' => $belumBayar
        ? ['judul' => 'Langkah berikutnya', 'daftar' => [
            'Transfer <strong>tepat sampai angka terakhir</strong> sesuai total di atas. '
                . 'Angka terakhirnya kode unik Anda — kalau dibulatkan, pembayarannya '
                . 'tidak bisa kami cocokkan.',
            'Kirim bukti transfernya ke panitia lewat WhatsApp.',
            'Panitia memeriksanya, lalu mengirim kabar penerimaan ke email ini.',
        ]]
        : null,
    'kaki' => ['Pengumuman teknis dibagikan panitia lewat WhatsApp.'],
])
