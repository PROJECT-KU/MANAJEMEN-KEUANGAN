<?php

namespace App\Actions\WebinarEksklusif;

use App\KategoriLayanan;
use App\Services\Fonnte;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Support\Facades\Log;

/**
 * Pemberitahuan WhatsApp, sepasang dengan suratnya.
 *
 * Alasannya satu kalimat dari pemilik produk: tidak semua orang membuka
 * emailnya. Yang tidak membukanya kehilangan nomor pendaftaran, tagihan,
 * dan batas waktunya sekaligus — lalu kursinya dilepas tanpa ia pernah
 * tahu apa yang terjadi.
 *
 * TIDAK PERNAH MENGGAGALKAN PEMANGGILNYA. Dipanggil dari jalur pendaftaran
 * dan dari pemberitahuan gerbang pembayaran; kegagalan di sini tidak boleh
 * membatalkan pendaftaran maupun membuat kami menjawab bukan-200 ke DOKU —
 * yang lalu mengulang pemberitahuan pembayaran yang sudah tercatat.
 *
 * Pesannya SENGAJA PENDEK dan tidak mengulang seluruh isi suratnya.
 * WhatsApp dibaca sambil lalu di layar kecil; yang panjang digulung tanpa
 * dibaca. Yang harus ada cuma: apa yang terjadi, nomornya, angkanya, dan
 * satu tautan.
 */
class KabariWhatsApp
{
    public function __construct(private Fonnte $fonnte)
    {
    }

    public function pendaftaranMasuk(WebinarEksklusifPendaftaran $p, ?KategoriLayanan $sesi = null): bool
    {
        $batas = $p->kedaluwarsa_pada
            ? $p->kedaluwarsa_pada->locale('id')->translatedFormat('j F Y, H:i') . ' WIB'
            : null;

        return $this->kirim($p, implode("\n", array_filter([
            '*Pendaftaran tersimpan* — Rumah Scopus Foundation',
            '',
            'Halo ' . $p->nama . ', pendaftaran Anda untuk *' . ($sesi?->nama ?: 'Webinar Eksklusif') . '* sudah kami terima.',
            '',
            'No. pendaftaran: ' . $p->id_transaksi,
            'Total bayar: Rp ' . number_format((int) $p->total_pembayaran, 0, ',', '.'),
            $batas ? 'Batas bayar: ' . $batas : null,
            '',
            'Selesaikan pembayarannya di sini:',
            $this->tautanStatus($p),
            '',
            'Fakturnya kami kirim ke email Anda. Kursi ditahan sampai batas waktu di atas.',
        ], fn ($b) => $b !== null)));
    }

    public function lunas(WebinarEksklusifPendaftaran $p, ?KategoriLayanan $sesi = null): bool
    {
        return $this->kirim($p, implode("\n", array_filter([
            '*Pembayaran diterima* — Rumah Scopus Foundation',
            '',
            'Halo ' . $p->nama . ', pembayaran Anda sudah kami terima dan *kursi Anda aman*.',
            '',
            'No. pendaftaran: ' . $p->id_transaksi,
            'Jumlah: Rp ' . number_format((int) $p->total_pembayaran, 0, ',', '.'),
            $p->bayar_kanal ? 'Cara bayar: ' . $p->bayar_kanal : null,
            '',
            'Kwitansinya kami kirim ke ' . $p->email . '.',
            'Tautan masuk sesinya dibagikan panitia lewat grup peserta di WhatsApp.',
        ], fn ($b) => $b !== null)));
    }

    public function gagalBayar(WebinarEksklusifPendaftaran $p, ?KategoriLayanan $sesi = null): bool
    {
        return $this->kirim($p, implode("\n", [
            '*Pembayaran belum berhasil* — Rumah Scopus Foundation',
            '',
            'Halo ' . $p->nama . ', pembayaran untuk ' . $p->id_transaksi . ' tidak selesai, jadi kursinya kami lepas kembali.',
            '',
            'Ini biasanya urusan bank atau kartunya — bukan kesalahan Anda, dan bisa dicoba lagi sekarang juga.',
            '',
            'Kalau saldo Anda sempat terpotong, dana yang tertahan dikembalikan sendiri oleh bank dalam beberapa hari kerja.',
            '',
            'Mau daftar lagi?',
            $this->tautanDaftar($sesi),
        ]));
    }

    public function kursiDilepas(WebinarEksklusifPendaftaran $p, ?KategoriLayanan $sesi = null): bool
    {
        return $this->kirim($p, implode("\n", [
            '*Kursi Anda dilepas* — Rumah Scopus Foundation',
            '',
            'Halo ' . $p->nama . ', pembayaran untuk ' . $p->id_transaksi . ' belum masuk sampai batas waktunya, jadi kursinya kami lepas untuk peserta lain.',
            '',
            'Merasa sudah membayar? Balas pesan ini dengan menyebut nomor di atas — pembayarannya tidak hilang, hanya konfirmasinya yang tertunda.',
            '',
            'Masih ingin ikut? Kursinya bisa diambil lagi selama kuotanya belum penuh:',
            $this->tautanDaftar($sesi),
        ]));
    }

    private function tautanStatus(WebinarEksklusifPendaftaran $p): string
    {
        return route('public.webinareksklusif.status', $p->getKey());
    }

    private function tautanDaftar(?KategoriLayanan $sesi): string
    {
        return $sesi
            ? route('public.webinareksklusif.daftar', $sesi->getKey())
            : route('public.webinareksklusif.index');
    }

    private function kirim(WebinarEksklusifPendaftaran $p, string $pesan): bool
    {
        $hasil = $this->fonnte->kirim($p->telp, $pesan);

        if (! $hasil['berhasil']) {
            /*
             * Dicatat sebagai PERINGATAN, bukan galat: tidak tersetelnya
             * token dan nomor yang memang tidak bisa dihubungi keduanya
             * normal, dan galat yang normal mengajari orang mengabaikan
             * lognya.
             */
            Log::warning('Kabar WhatsApp tidak terkirim.', [
                'nomor' => $p->id_transaksi,
                'sebab' => $hasil['pesan'],
            ]);
        }

        return $hasil['berhasil'];
    }
}
