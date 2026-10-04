@include('emails.pendaftaran.kerangka', [
    'judul' => 'Pendaftaran Scopus Camp dijadwalkan ulang',
    'lencana' => ['teks' => 'Dijadwalkan ulang', 'latar' => '#eff6ff', 'tinta' => '#1d4ed8'],
    'sapaan' => $pendaftaran->nama,
    'pembuka' => 'Pendaftaran Scopus Camp Anda dipindahkan ke jadwal berikut. Mohon dicatat tanggalnya.',
    'rincian' => [
        'Kode transaksi' => strtoupper($pendaftaran->id_transaksi),
        'Angkatan baru' => $categoriesScopusCamp->nama . ' #' . $categoriesScopusCamp->nama_ke,
        'Lokasi' => $categoriesScopusCamp->lokasi,
        'Mulai' => \Carbon\Carbon::parse($categoriesScopusCamp->mulai)->translatedFormat('d F Y'),
        'Selesai' => \Carbon\Carbon::parse($categoriesScopusCamp->selesai)->translatedFormat('d F Y'),
        'Total pembayaran' => 'Rp ' . number_format($pendaftaran->total_pembayaran, 0, ',', '.'),
    ],
    'sorot' => $categoriesScopusCamp->group_wa
        ? ['judul' => 'Grup WhatsApp peserta', 'isi' => $categoriesScopusCamp->group_wa]
        : null,
    'penutup' => ['Kalau jadwal baru ini berhalangan, balas email ini supaya kami carikan jalan keluarnya.'],
])
