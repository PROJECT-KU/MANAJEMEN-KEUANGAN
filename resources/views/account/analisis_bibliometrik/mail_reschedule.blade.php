@include('emails.pendaftaran.kerangka', [
    'judul' => 'Pendaftaran Analisis Bibliometrik dijadwalkan ulang',
    'lencana' => ['teks' => 'Dijadwalkan ulang', 'latar' => '#eff6ff', 'tinta' => '#1d4ed8'],
    'sapaan' => $analisisbibliometrik->nama,
    'pembuka' => 'Pendaftaran Analisis Bibliometrik Anda dipindahkan ke jadwal berikut. Mohon dicatat tanggalnya.',
    'rincian' => [
        'Kode transaksi' => strtoupper($analisisbibliometrik->id_transaksi),
        'Angkatan baru' => $categoriesanalisisbibliometrik->nama . ' #' . $categoriesanalisisbibliometrik->nama_ke,
        'Mulai' => \Carbon\Carbon::parse($categoriesanalisisbibliometrik->mulai)->translatedFormat('d F Y'),
        'Selesai' => \Carbon\Carbon::parse($categoriesanalisisbibliometrik->selesai)->translatedFormat('d F Y'),
        'Total pembayaran' => 'Rp ' . number_format($analisisbibliometrik->total_pembayaran, 0, ',', '.'),
    ],
    'sorot' => $analisisbibliometrik->group_wa
        ? ['judul' => 'Grup WhatsApp peserta', 'isi' => $analisisbibliometrik->group_wa]
        : null,
    'penutup' => ['Kalau jadwal baru ini berhalangan, balas email ini supaya kami carikan jalan keluarnya.'],
])
