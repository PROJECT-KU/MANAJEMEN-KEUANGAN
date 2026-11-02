{{-- Isinya saja; rupanya dirakit kerangka bersama di emails/pendaftaran.
     Lihat catatan di sana soal kenapa templat lama tidak pernah bergaya
     sampai ke penerima. --}}
@include('emails.pendaftaran.kerangka', [
    'judul' => 'Pendaftaran Class Online diterima',
    'lencana' => ['teks' => 'Diterima', 'latar' => '#ecfdf5', 'tinta' => '#047857'],
    'sapaan' => $pendaftaran->nama,
    'pembuka' => 'Terima kasih sudah mendaftar Class Online. Pendaftaran Anda sudah kami terima, berikut rinciannya.',
    'rincian' => [
        'Kode transaksi' => strtoupper($pendaftaran->id_transaksi),
        'Angkatan' => $categoriesOnlineTraining->nama . ' #' . $categoriesOnlineTraining->nama_ke,
        'Lokasi' => $categoriesOnlineTraining->lokasi,
        'Mulai' => \Carbon\Carbon::parse($categoriesOnlineTraining->mulai)->translatedFormat('d F Y'),
        'Selesai' => \Carbon\Carbon::parse($categoriesOnlineTraining->selesai)->translatedFormat('d F Y'),
        'Total pembayaran' => 'Rp ' . number_format($pendaftaran->total_pembayaran, 0, ',', '.'),
    ],
    'sorot' => $categoriesOnlineTraining->group_wa
        ? ['judul' => 'Grup WhatsApp peserta', 'isi' => $categoriesOnlineTraining->group_wa]
        : null,
    'penutup' => ['Silakan bergabung ke grup WhatsApp di atas supaya tidak ketinggalan kabar acara.'],
])
