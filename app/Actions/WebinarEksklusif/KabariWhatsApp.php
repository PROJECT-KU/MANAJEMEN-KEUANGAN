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
            ...$this->kaki(),
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
            ...$this->kaki(),
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
            ...$this->kaki(),
        ]));
    }

    public function kursiDilepas(WebinarEksklusifPendaftaran $p, ?KategoriLayanan $sesi = null): bool
    {
        return $this->kirim($p, implode("\n", [
            '*Kursi Anda dilepas* — Rumah Scopus Foundation',
            '',
            'Halo ' . $p->nama . ', pembayaran untuk ' . $p->id_transaksi . ' belum masuk sampai batas waktunya, jadi kursinya kami lepas untuk peserta lain.',
            '',
            'Merasa sudah membayar? Hubungi admin resmi kami dengan menyebut nomor di atas — pembayarannya tidak hilang, hanya konfirmasinya yang tertunda.',
            '',
            'Masih ingin ikut? Kursinya bisa diambil lagi selama kuotanya belum penuh:',
            $this->tautanDaftar($sesi),
            ...$this->kaki(),
        ]));
    }

    /**
     * Kaki yang sama untuk SEMUA pemberitahuan.
     *
     * Tiga hal, dan ketiganya menjawab persoalan yang berbeda:
     *
     *   1. "jangan dibalas" — nomor pengirimnya nomor SISTEM. Balasan ke
     *      sini tidak dibaca siapa pun, dan yang membalas lalu menunggu
     *      jawaban yang tidak akan pernah datang;
     *   2. ke mana harus menghubungi — tanpa itu, butir pertama cuma
     *      menutup pintu tanpa menunjukkan pintu lain;
     *   3. PERINGATAN PENIPUAN, dan ini yang paling penting.
     *
     * Butir ketiga ada karena orang yang baru saja mendaftar adalah sasaran
     * empuk: ia memang sedang menunggu dihubungi soal pembayaran, jadi
     * siapa pun yang menyapa mengaku Rumah Scopus akan dipercaya. Menyebut
     * SATU nomor resmi mengubah "siapa pun yang mengaku" jadi sesuatu yang
     * bisa dicocokkan sendiri oleh orangnya, di pesan yang sudah ada di
     * tangannya.
     *
     * "admin resmi", bukan "panitia": panitia bisa berarti siapa saja yang
     * terlibat acara, dan penipu pun menyebut dirinya panitia.
     *
     * Kalimat "tidak pernah meminta transfer ke rekening pribadi" BOLEH
     * ditulis setegas ini karena memang begitu adanya: sejak transfer
     * manual dicabut, Webinar Eksklusif hanya menerima pembayaran lewat
     * gerbang resminya. Kalau suatu saat jalur manual dibuka lagi, kalimat
     * ini harus ikut berubah — kalau tidak, ia jadi janji yang dilanggar
     * sistemnya sendiri.
     *
     * Nomornya dibaca dari config('panitia.whatsapp'), BUKAN diketik di
     * sini: ia berubah saat orangnya berganti, dan nomor yang diketik ulang
     * di tiap tempat akan tertinggal di sebagian tempat.
     *
     * Dirakit di SATU tempat, bukan disalin ke empat pesan: disalin,
     * keempatnya akan berbeda perlahan — dan yang berbeda adalah nomor yang
     * dipakai orang untuk memastikan ia tidak sedang ditipu.
     */
    private function kaki(): array
    {
        $wa = preg_replace('/^62/', '0', (string) config('panitia.whatsapp'));
        $tampil = trim(chunk_split((string) $wa, 4, '-'), '-');

        return [
            '',
            '— — —',
            'Pesan ini dikirim otomatis, *mohon jangan dibalas*.',
            '',
            'Ada kendala atau pertanyaan? Hubungi *admin resmi Rumah Scopus Foundation*, '
                . 'dan hanya di nomor ini:',
            '*' . $tampil . '*',
            '',
            '*Hati-hati penipuan.* Ada pihak yang mengatasnamakan Rumah Scopus '
                . 'Foundation. Kami tidak pernah meminta transfer ke rekening pribadi, '
                . 'dan pembayaran hanya lewat tautan resmi di pesan maupun email ini.',
        ];
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
