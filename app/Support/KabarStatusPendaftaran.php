<?php

namespace App\Support;

use App\Support\PendaftaranSemuaLayanan as Pendaftaran;

/**
 * Kalimat surat untuk tiap perubahan status pendaftaran.
 *
 * Sebelum ini hanya 5 dari 23 status yang mengirim surat: "diterima" dan
 * "dijadwalkan ulang" untuk Scopus Camp dan Bibliometrik, plus "pembayaran
 * diterima" untuk Scopus Kafe. Delapan belas sisanya diam — termasuk
 * SELURUH status Webinar Eksklusif dan Clinik Scopus. Pendaftar yang
 * ditolak, dibatalkan, atau kursinya dilepas karena batas waktu tidak pernah
 * diberi tahu sama sekali; ia menunggu kabar yang tidak akan pernah datang,
 * lalu bertanya lewat WhatsApp — dan itu menjadi pekerjaan panitia.
 *
 * Kalimatnya ditulis per STATUS MENTAH, bukan per keadaan. Lima keadaan di
 * PendaftaranSemuaLayanan::KEADAAN menyatukan "Ditolak", "Dibatalkan", dan
 * "expired" jadi satu `tidak_jadi` — cukup untuk memilih warna, tetapi
 * ketiganya berarti hal yang berbeda bagi yang menerimanya: ditolak panitia,
 * dibatalkan, atau kursinya dilepas karena batas waktunya lewat. Orang yang
 * membacanya berhak tahu yang mana.
 *
 * WARNANYA tetap diambil dari KEADAAN, tidak ditulis ulang di sini: dua
 * daftar warna untuk satu hal yang sama pasti berbeda suatu hari.
 *
 * Bahasanya sengaja biasa. Penggunanya bukan orang teknis, dan kata seperti
 * "status pendaftaran Anda telah diperbarui menjadi expired" tidak memberi
 * tahu apa pun tentang apa yang harus ia lakukan sekarang.
 */
class KabarStatusPendaftaran
{
    /**
     * judul   — judul besar di kepala surat
     * lencana — tulisan di lencana; warnanya dari KEADAAN
     * pembuka — satu kalimat yang menyebut APA yang terjadi
     * penutup — larik alinea: apa yang perlu dikerjakan sesudahnya
     */
    private const KABAR = [
        // ---------------------------------------------------- masih menunggu
        'diproses' => [
            'judul' => 'Pendaftaran Anda sedang diproses',
            'lencana' => 'Menunggu pembayaran',
            'pembuka' => 'Pendaftaran Anda sudah masuk dan sekarang menunggu pembayaran.',
            'penutup' => ['Kalau sudah transfer, kirimkan bukti transfernya ke panitia supaya bisa segera kami cek.'],
        ],
        'pending' => [
            'judul' => 'Pendaftaran Anda sedang diproses',
            'lencana' => 'Menunggu pembayaran',
            'pembuka' => 'Pendaftaran Anda sudah masuk dan sekarang menunggu pembayaran.',
            'penutup' => ['Kalau sudah transfer, kirimkan bukti transfernya ke panitia supaya bisa segera kami cek.'],
        ],
        'menunggu verifikasi' => [
            'judul' => 'Bukti pembayaran Anda sedang kami periksa',
            'lencana' => 'Sedang diperiksa',
            'pembuka' => 'Bukti pembayaran Anda sudah kami terima dan sedang diperiksa panitia.',
            'penutup' => ['Anda tidak perlu mengirim apa pun lagi. Kami kabari lagi lewat email ini begitu pemeriksaannya selesai.'],
        ],

        // ------------------------------------------------------------- lunas
        'Pendaftaran Diterima' => [
            'judul' => 'Pendaftaran Anda diterima',
            'lencana' => 'Diterima',
            'pembuka' => 'Pembayaran Anda sudah kami terima dan pendaftaran Anda resmi diterima.',
            'penutup' => ['Simpan email ini sebagai bukti. Kabar berikutnya soal acara akan kami kirim lewat email yang sama.'],
        ],
        'paid' => [
            'judul' => 'Pembayaran Anda sudah lunas',
            'lencana' => 'Lunas',
            'pembuka' => 'Pembayaran Anda sudah kami terima. Kursi Anda sudah aman.',
            'penutup' => ['Simpan email ini sebagai bukti. Kabar berikutnya soal acara akan kami kirim lewat email yang sama.'],
        ],
        'pembayaran diterima' => [
            'judul' => 'Pembayaran Anda sudah kami terima',
            'lencana' => 'Lunas',
            'pembuka' => 'Pembayaran Anda sudah kami terima dan pemesanan Anda sudah terkunci.',
            'penutup' => ['Simpan email ini sebagai bukti. Sampai bertemu di sesi Anda.'],
        ],
        'completed' => [
            'judul' => 'Sesi Anda sudah selesai',
            'lencana' => 'Selesai',
            'pembuka' => 'Sesi Anda sudah selesai dan tercatat lengkap di kami.',
            'penutup' => ['Terima kasih sudah mengikutinya sampai tuntas. Kalau ada yang masih ingin ditanyakan, balas saja email ini.'],
        ],

        // ---------------------------------------------------- dijadwalkan ulang
        'Pendaftaran Reschedule' => [
            'judul' => 'Jadwal Anda dipindahkan',
            'lencana' => 'Dijadwalkan ulang',
            'pembuka' => 'Pendaftaran Anda dipindahkan ke jadwal yang baru. Rinciannya ada di bawah ini.',
            'penutup' => ['Kalau jadwal barunya ternyata tidak bisa Anda ikuti, segera balas email ini supaya bisa kami carikan jalan keluarnya.'],
        ],

        // ------------------------------------------------------------ refund
        'Pendaftaran Refund' => [
            'judul' => 'Dana Anda dikembalikan',
            'lencana' => 'Dana dikembalikan',
            'pembuka' => 'Pendaftaran Anda dibatalkan dan dana yang sudah Anda bayarkan kami kembalikan.',
            'penutup' => [
                'Pengembaliannya diproses ke rekening yang Anda pakai membayar. Biasanya butuh beberapa hari kerja sampai dananya masuk.',
                'Kalau sampai seminggu belum juga masuk, balas email ini dan akan kami telusuri.',
            ],
        ],

        // --------------------------------------------------------- tidak jadi
        'Pendaftaran Ditolak' => [
            'judul' => 'Pendaftaran Anda belum bisa kami terima',
            'lencana' => 'Belum diterima',
            'pembuka' => 'Mohon maaf, pendaftaran Anda belum bisa kami terima kali ini.',
            'penutup' => ['Kalau Anda merasa ini keliru, balas email ini — panitia akan memeriksanya lagi.'],
        ],
        'pembayaran ditolak' => [
            'judul' => 'Bukti pembayaran Anda belum cocok',
            'lencana' => 'Perlu diperbaiki',
            'pembuka' => 'Bukti pembayaran yang Anda kirim belum cocok dengan tagihannya, jadi belum bisa kami sahkan.',
            'penutup' => [
                'Yang paling sering jadi sebabnya: nominalnya kurang atau lebih, atau bukti yang terkirim bukan bukti transfer yang dimaksud.',
                'Balas email ini dengan bukti yang benar, atau hubungi panitia kalau Anda ragu nominalnya berapa.',
            ],
        ],
        'Pendaftaran Dibatalkan' => [
            'judul' => 'Pendaftaran Anda dibatalkan',
            'lencana' => 'Dibatalkan',
            'pembuka' => 'Pendaftaran Anda sudah dibatalkan dan kursinya kami lepas.',
            'penutup' => ['Kalau ini bukan permintaan Anda, segera balas email ini supaya bisa kami periksa.'],
        ],
        'cancel' => [
            'judul' => 'Pendaftaran Anda dibatalkan',
            'lencana' => 'Dibatalkan',
            'pembuka' => 'Pendaftaran Anda sudah dibatalkan dan kursinya kami lepas.',
            'penutup' => ['Kalau ini bukan permintaan Anda, segera balas email ini supaya bisa kami periksa.'],
        ],
        'canceled' => [
            'judul' => 'Pemesanan Anda dibatalkan',
            'lencana' => 'Dibatalkan',
            'pembuka' => 'Pemesanan Anda sudah dibatalkan.',
            'penutup' => ['Kalau ini bukan permintaan Anda, segera balas email ini supaya bisa kami periksa.'],
        ],
        'expired' => [
            'judul' => 'Batas waktu pembayaran Anda sudah lewat',
            'lencana' => 'Kursi dilepas',
            'pembuka' => 'Batas waktu pembayaran sudah lewat, jadi kursi Anda kami lepas untuk pendaftar lain.',
            'penutup' => [
                'Kalau Anda sebenarnya sudah transfer tetapi belum sempat mengirim buktinya, balas email ini — masih bisa kami bantu.',
                'Kalau masih ingin ikut, Anda bisa mendaftar lagi selama kuotanya masih ada.',
            ],
        ],
    ];

    /** Apakah status ini punya kalimatnya. */
    public static function ada(?string $status): bool
    {
        return $status !== null && array_key_exists($status, self::KABAR);
    }

    /** Semua status yang sudah punya kalimat. */
    public static function semuaStatus(): array
    {
        return array_keys(self::KABAR);
    }

    /**
     * Kabar lengkap satu status, beserta warna lencananya.
     *
     * @return array{judul:string, lencana:array{teks:string, latar:string, tinta:string}, pembuka:string, penutup:array<int, string>}|null
     */
    public static function untuk(?string $status): ?array
    {
        if (! self::ada($status)) {
            return null;
        }

        $kabar = self::KABAR[$status];
        $keadaan = Pendaftaran::keadaanDari($status);

        // Tanda kurungnya WAJIB: `+` mengikat lebih erat daripada `??`, jadi
        // tanpa itu keadaan yang belum terdaftar di RUPA menggabungkan larik
        // dengan null — TypeError saat suratnya dirakit, bukan warna cadangan.
        $rupa = self::RUPA[$keadaan] ?? self::RUPA['lain'];

        return [
            'judul' => $kabar['judul'],
            'lencana' => ['teks' => $kabar['lencana']] + $rupa,
            'pembuka' => $kabar['pembuka'],
            'penutup' => $kabar['penutup'],
        ];
    }

    /**
     * Warna lencana per keadaan, dalam hex.
     *
     * Hex, bukan nama kelas: surat tidak memuat CSS aplikasi, dan satu-satunya
     * gaya yang selamat sampai ke kotak masuk adalah atribut style sebaris.
     * Pasangannya dipilih dari palet yang sama dengan lencana di layar.
     */
    private const RUPA = [
        'menunggu' => ['latar' => '#fffbeb', 'tinta' => '#b45309'],
        'lunas' => ['latar' => '#ecfdf5', 'tinta' => '#047857'],
        'jadwal' => ['latar' => '#eff6ff', 'tinta' => '#1d4ed8'],
        'refund' => ['latar' => '#f5f3ff', 'tinta' => '#6d28d9'],
        'tidak_jadi' => ['latar' => '#fef2f2', 'tinta' => '#b91c1c'],
        'lain' => ['latar' => '#f1f5f9', 'tinta' => '#475569'],
    ];
}
