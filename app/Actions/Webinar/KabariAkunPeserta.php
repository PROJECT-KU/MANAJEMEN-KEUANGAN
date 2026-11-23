<?php

namespace App\Actions\Webinar;

use App\KategoriLayanan;
use App\Mail\AkunPesertaWebinarMail;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Membuatkan akun peserta, lalu mengirimkan keterangan masuknya.
 *
 * Dipisahkan dari BuatAkunPeserta supaya pembuatan akunnya bisa diuji dan
 * dijalankan tanpa mengirim surat apa pun — dan supaya perintah perbaikan
 * nanti bisa memakai salah satunya saja.
 *
 * ---------------------------------------------------------------------------
 * SATU SURAT PER ORANG
 * ---------------------------------------------------------------------------
 *
 * Tiap peserta menerima nama penggunanya sendiri. Mengirim satu surat berisi
 * daftar seluruh rombongan akan membocorkan nama pengguna orang lain kepada
 * semua orang di pendaftaran itu — dan nama pengguna adalah separuh dari
 * kunci masuk.
 *
 * ---------------------------------------------------------------------------
 * KEGAGALAN SURAT TIDAK MEMBATALKAN AKUNNYA
 * ---------------------------------------------------------------------------
 *
 * Akunnya sudah dibuat sebelum surat pertama dikirim. Kalau peladen surat
 * sedang bermasalah di tengah rombongan, yang pantas terjadi adalah galatnya
 * dicatat dan sisanya tetap dicoba — bukan tiga orang pertama punya akun,
 * sisanya tidak, dan pemanggilnya melempar galat ke gerbang pembayaran.
 */
class KabariAkunPeserta
{
    public function __construct(private BuatAkunPeserta $pembuat)
    {
    }

    /**
     * @return int jumlah surat yang berhasil dikirim
     */
    public function untuk(WebinarEksklusifPendaftaran $pendaftaran): int
    {
        $akun = $this->pembuat->untuk($pendaftaran);

        if ($akun === []) {
            return 0;
        }

        $sesi = $pendaftaran->kategori_id
            ? KategoriLayanan::find($pendaftaran->kategori_id)
            : null;

        $terkirim = 0;

        foreach ($akun as $orang) {
            try {
                Mail::to($orang['email'])->send(new AkunPesertaWebinarMail(
                    $orang['nama'],
                    $orang['username'],
                    $orang['baru'],
                    $sesi,
                ));

                $terkirim++;
            } catch (\Throwable $e) {
                /*
                 * Dicatat beserta nomor pendaftarannya supaya bisa dikirim
                 * ulang dengan tangan. Alamatnya TIDAK ditulis utuh di log:
                 * log dibaca lebih banyak orang daripada basis data.
                 */
                Log::error('Gagal mengirim keterangan akun peserta webinar.', [
                    'pendaftaran' => $pendaftaran->id_transaksi,
                    'email' => $this->samarkan($orang['email']),
                    'sebab' => $e->getMessage(),
                ]);
            }
        }

        return $terkirim;
    }

    private function samarkan(string $email): string
    {
        [$depan, $belakang] = array_pad(explode('@', $email, 2), 2, '');

        return mb_substr($depan, 0, 2) . '***@' . $belakang;
    }
}
