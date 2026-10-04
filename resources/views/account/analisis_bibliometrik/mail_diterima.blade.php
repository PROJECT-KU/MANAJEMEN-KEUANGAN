{{-- Isinya saja; rupanya dirakit kerangka bersama di emails/pendaftaran. --}}
@include('emails.pendaftaran.kerangka', [
    'judul' => 'Pendaftaran Analisis Bibliometrik diterima',
    'lencana' => ['teks' => 'Diterima', 'latar' => '#ecfdf5', 'tinta' => '#047857'],
    'sapaan' => $analisisbibliometrik->nama,
    'pembuka' => 'Terima kasih sudah mendaftar Analisis Bibliometrik. Pendaftaran Anda sudah kami terima, berikut rinciannya.',
    'rincian' => [
        'Kode transaksi' => strtoupper($analisisbibliometrik->id_transaksi),
        'Angkatan' => $categoriesanalisisbibliometrik->nama . ' #' . $categoriesanalisisbibliometrik->nama_ke,
        'Mulai' => \Carbon\Carbon::parse($categoriesanalisisbibliometrik->mulai)->translatedFormat('d F Y'),
        'Selesai' => \Carbon\Carbon::parse($categoriesanalisisbibliometrik->selesai)->translatedFormat('d F Y'),
        'Total pembayaran' => 'Rp ' . number_format($analisisbibliometrik->total_pembayaran, 0, ',', '.'),
    ],
    'sorot' => $analisisbibliometrik->group_wa
        ? ['judul' => 'Grup WhatsApp peserta', 'isi' => $analisisbibliometrik->group_wa]
        : null,
    'penutup' => ['Silakan bergabung ke grup WhatsApp di atas supaya tidak ketinggalan kabar kegiatan.'],
])
