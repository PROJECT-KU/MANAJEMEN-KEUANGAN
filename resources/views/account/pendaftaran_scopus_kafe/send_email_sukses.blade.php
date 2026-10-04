@include('emails.pendaftaran.kerangka', [
    'judul' => 'Pemesanan Scopus Kafe diperbarui',
    'lencana' => ['teks' => $data->status, 'latar' => '#ecfdf5', 'tinta' => '#047857'],
    'sapaan' => $data->nama,
    'pembuka' => 'Status pemesanan Scopus Kafe Anda diperbarui pada '
        . \Carbon\Carbon::parse($data->updated_at)->translatedFormat('d F Y')
        . '. Berikut rinciannya.',
    'rincian' => [
        'ID pemesanan' => strtoupper($data->id_pemesanan),
        'Atas nama' => $data->nama,
        'Nomor telepon' => $data->telp,
        'Tanggal pemesanan' => \Carbon\Carbon::parse($data->tanggal_pemesanan)->translatedFormat('d F Y'),
        'Total pembayaran' => 'Rp ' . number_format($data->total_keseluruhan_pembayaran, 0, ',', '.'),
    ],
    'sorot' => null,
    'penutup' => ['Kalau ada kendala atau pertanyaan, silakan hubungi kami lewat nomor di bawah.'],
])
