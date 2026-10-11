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
            'pembuka' => 'Pendaftaran Anda telah kami terima dan saat ini menunggu pembayaran.',
            'penutup' => ['Apabila pembayaran telah dilakukan, mohon kirimkan bukti transfernya kepada panitia agar dapat segera kami periksa.'],
        ],
        'pending' => [
            'judul' => 'Pendaftaran Anda sedang diproses',
            'lencana' => 'Menunggu pembayaran',
            'pembuka' => 'Pendaftaran Anda telah kami terima dan saat ini menunggu pembayaran.',
            'penutup' => ['Apabila pembayaran telah dilakukan, mohon kirimkan bukti transfernya kepada panitia agar dapat segera kami periksa.'],
        ],
        'menunggu verifikasi' => [
            'judul' => 'Bukti pembayaran Anda sedang kami periksa',
            'lencana' => 'Sedang diperiksa',
            'pembuka' => 'Bukti pembayaran Anda telah kami terima dan saat ini sedang diperiksa panitia.',
            'penutup' => ['Anda tidak perlu mengirimkan apa pun lagi. Kami akan mengabari melalui email ini setelah pemeriksaannya selesai.'],
        ],

        // ------------------------------------------------------------- lunas
        'Pendaftaran Diterima' => [
            'judul' => 'Pendaftaran Anda diterima',
            'lencana' => 'Diterima',
            'pembuka' => 'Pembayaran Anda telah kami terima dan pendaftaran Anda resmi diterima.',
            'penutup' => ['Mohon simpan email ini sebagai bukti. Pemberitahuan berikutnya mengenai acara akan kami kirimkan melalui email yang sama.'],
        ],
        'paid' => [
            'judul' => 'Pembayaran Anda sudah lunas',
            'lencana' => 'Lunas',
            'pembuka' => 'Pembayaran Anda telah kami terima dan kursi Anda telah kami amankan.',
            'penutup' => ['Mohon simpan email ini sebagai bukti. Pemberitahuan berikutnya mengenai acara akan kami kirimkan melalui email yang sama.'],
        ],
        'pembayaran diterima' => [
            'judul' => 'Pembayaran Anda sudah kami terima',
            'lencana' => 'Lunas',
            'pembuka' => 'Pembayaran Anda telah kami terima dan pemesanan Anda telah kami pastikan.',
            'penutup' => ['Mohon simpan email ini sebagai bukti. Sampai berjumpa di sesi Anda.'],
        ],
        'completed' => [
            'judul' => 'Sesi Anda sudah selesai',
            'lencana' => 'Selesai',
            'pembuka' => 'Sesi Anda telah selesai dan tercatat lengkap dalam catatan kami.',
            'penutup' => ['Terima kasih telah mengikutinya sampai tuntas. Apabila ada yang ingin ditanyakan, mohon hubungi admin kami lewat WhatsApp.'],
        ],

        // ---------------------------------------------------- dijadwalkan ulang
        'Pendaftaran Reschedule' => [
            'judul' => 'Jadwal Anda dipindahkan',
            'lencana' => 'Dijadwalkan ulang',
            'pembuka' => 'Pendaftaran Anda telah dipindahkan ke jadwal baru. Rinciannya tercantum di bawah ini.',
            'penutup' => ['Apabila jadwal baru tersebut tidak dapat Anda ikuti, mohon segera hubungi admin kami lewat WhatsApp agar kami carikan solusinya.'],
        ],

        // ------------------------------------------------------------ refund
        'Pendaftaran Refund' => [
            'judul' => 'Dana Anda dikembalikan',
            'lencana' => 'Dana dikembalikan',
            'pembuka' => 'Pendaftaran Anda dibatalkan dan dana yang telah Anda bayarkan kami kembalikan.',
            'penutup' => [
                'Pengembaliannya diproses ke rekening yang Anda gunakan untuk membayar. Umumnya diperlukan beberapa hari kerja sampai dananya masuk.',
                'Apabila dalam satu minggu dananya belum juga masuk, mohon hubungi admin kami lewat WhatsApp agar kami telusuri.',
            ],
        ],

        // --------------------------------------------------------- tidak jadi
        'Pendaftaran Ditolak' => [
            'judul' => 'Pendaftaran Anda belum bisa kami terima',
            'lencana' => 'Belum diterima',
            'pembuka' => 'Dengan menyesal kami sampaikan bahwa pendaftaran Anda belum dapat kami terima kali ini.',
            'penutup' => ['Apabila menurut Anda terdapat kekeliruan, mohon hubungi admin kami lewat WhatsApp agar panitia memeriksanya kembali.'],
        ],
        'pembayaran ditolak' => [
            'judul' => 'Bukti pembayaran Anda belum cocok',
            'lencana' => 'Perlu diperbaiki',
            'pembuka' => 'Bukti pembayaran yang Anda kirimkan belum sesuai dengan tagihannya sehingga belum dapat kami sahkan.',
            'penutup' => [
                'Penyebab yang paling sering terjadi: nominalnya kurang atau lebih, atau berkas yang terkirim bukan bukti transfer yang dimaksud.',
                'Mohon kirimkan bukti yang benar kepada admin kami lewat WhatsApp, atau tanyakan di sana apabila Anda ragu mengenai nominalnya.',
            ],
        ],
        'Pendaftaran Dibatalkan' => [
            'judul' => 'Pendaftaran Anda dibatalkan',
            'lencana' => 'Dibatalkan',
            'pembuka' => 'Pendaftaran Anda telah dibatalkan dan kursinya kami lepaskan.',
            'penutup' => ['Apabila pembatalan ini bukan permintaan Anda, mohon segera hubungi admin kami lewat WhatsApp agar dapat kami periksa.'],
        ],
        'cancel' => [
            'judul' => 'Pendaftaran Anda dibatalkan',
            'lencana' => 'Dibatalkan',
            'pembuka' => 'Pendaftaran Anda telah dibatalkan dan kursinya kami lepaskan.',
            'penutup' => ['Apabila pembatalan ini bukan permintaan Anda, mohon segera hubungi admin kami lewat WhatsApp agar dapat kami periksa.'],
        ],
        'canceled' => [
            'judul' => 'Pemesanan Anda dibatalkan',
            'lencana' => 'Dibatalkan',
            'pembuka' => 'Pemesanan Anda telah dibatalkan.',
            'penutup' => ['Apabila pembatalan ini bukan permintaan Anda, mohon segera hubungi admin kami lewat WhatsApp agar dapat kami periksa.'],
        ],
        'expired' => [
            'judul' => 'Batas waktu pembayaran Anda sudah lewat',
            'lencana' => 'Kursi dilepas',
            'pembuka' => 'Batas waktu pembayaran telah terlewati sehingga kursi Anda kami lepaskan untuk pendaftar lain.',
            'penutup' => [
                'Apabila Anda telah melakukan pembayaran tetapi belum sempat mengirimkan buktinya, mohon hubungi admin kami lewat WhatsApp — hal tersebut masih dapat kami bantu.',
                'Apabila Anda masih berminat mengikutinya, Anda dapat mendaftar kembali selama kuotanya tersedia.',
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
