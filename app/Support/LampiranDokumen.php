<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Riskihajar\Terbilang\Facades\Terbilang;

/**
 * Perakit lampiran faktur dan kwitansi untuk surat semua layanan.
 *
 * Diminta pemiliknya 24 Okt 2026: "untuk attach file di email dibuat sama
 * seperti webinar eksklusif, ada faktur, kwitansi dll untuk semua layanan".
 *
 * Sebelumnya hanya Webinar Eksklusif yang punya keduanya. Yang lain timpang:
 *
 *   Scopus Camp    faktur saja — pembayarannya diterima tanpa satu pun
 *                  kwitansi, padahal surat penerimaannya menyebut nominal
 *   Bibliometrik   faktur saja, sama
 *   Scopus Kafe    TIDAK ADA LAMPIRAN SAMA SEKALI
 *   Clinik Scopus  tidak mengirim surat apa pun
 *
 * Akibatnya yang harus menyerahkan bukti ke bendahara kantornya tidak punya
 * apa pun untuk diserahkan — dan itu justru pemesan rombongan, yang
 * nominalnya paling besar.
 *
 * ---------------------------------------------------------------------------
 * SATU tempat, bukan satu salinan per Mailable
 * ---------------------------------------------------------------------------
 *
 * Tiap lampiran butuh tiga hal yang harus cocok: cetakan yang benar, nomor
 * berkas yang benar, dan terbilang dari kolom total yang benar. Disalin ke
 * tujuh Mailable, ketiganya akan berselisih di sebagian — dan yang berselisih
 * pada dokumen keuangan adalah angka.
 */
class LampiranDokumen
{
    /**
     * Katalog per layanan: cetakannya, kolom nomornya, dan kolom totalnya.
     *
     * Kolomnya disebut DI SINI karena memang berbeda-beda: Scopus Kafe
     * memakai id_pemesanan dan total_keseluruhan_pembayaran, sisanya
     * id_transaksi dan total_pembayaran. Ditebak dari nama layanannya, satu
     * layanan akan merakit kwitansi bernilai nol tanpa satu pun galat.
     *
     * @var array<string, array{cetakan:string, nomor:string, total:string, awalan:string}>
     */
    private const SUMBER = [
        'webinar_eksklusif' => [
            'cetakan' => 'public.webinar_eksklusif.dokumen_pdf',
            'nomor' => 'id_transaksi', 'total' => 'total_pembayaran', 'awalan' => 'WE-',
        ],
        'scopus_camp' => [
            'cetakan' => 'public.scopus_camp.dokumen_pdf',
            'nomor' => 'id_transaksi', 'total' => 'total_pembayaran', 'awalan' => 'CAMP-',
        ],
        'bibliometrik' => [
            'cetakan' => 'public.analisis_bibliometrik.dokumen_pdf',
            'nomor' => 'id_transaksi', 'total' => 'total_pembayaran', 'awalan' => 'BIB-',
        ],
        'scopus_kafe' => [
            'cetakan' => 'public.scopus_kafe.dokumen_pdf',
            'nomor' => 'id_pemesanan', 'total' => 'total_keseluruhan_pembayaran', 'awalan' => 'KAFE-',
        ],
    ];

    /**
     * Isi berkas dan namanya, siap diserahkan ke attachData().
     *
     * @param  'faktur'|'kwitansi'  $jenis
     * @return array{isi:string, nama:string}
     */
    public static function rakit(string $layanan, string $jenis, $pendaftaran, $angkatan = null): array
    {
        $sumber = self::SUMBER[$layanan] ?? null;

        if ($sumber === null) {
            throw new \InvalidArgumentException(
                'Layanan ' . $layanan . ' tidak punya cetakan faktur/kwitansi.'
            );
        }

        $nomor = (string) $pendaftaran->{$sumber['nomor']};
        $total = (int) $pendaftaran->{$sumber['total']};

        $pdf = Pdf::loadView($sumber['cetakan'], [
            'jenis' => $jenis,
            'pendaftaran' => $pendaftaran,
            'angkatan' => $angkatan,

            /*
             * Terbilang dirakit DI SINI, bukan diminta dari pemanggilnya.
             *
             * Tiap pemanggil yang merakitnya sendiri bisa memakai kolom total
             * yang berbeda — dan kwitansi yang angkanya Rp 5.480.393 tetapi
             * terbilangnya "lima juta empat ratus tujuh puluh sembilan ribu"
             * adalah dokumen yang menyebut dua jumlah sekaligus.
             */
            'terbilang' => Terbilang::make($total, ' rupiah'),
        ]);

        /*
         * Nama berkasnya memuat NOMORNYA, bukan "kwitansi.pdf" saja: yang
         * mengikuti beberapa angkatan akan punya beberapa berkas di satu
         * folder unduhan, dan nama yang sama membuat yang kedua menimpa yang
         * pertama di sebagian peramban.
         */
        $awalanBaru = $jenis === 'kwitansi' ? 'KW-' : 'INV-';

        /*
         * Awalannya DITAMBAHKAN kalau nomornya tidak memilikinya, bukan
         * sekadar ditukar.
         *
         * Nomor lama Scopus Kafe tidak berawalan apa pun — lima aksara acak
         * dari pembuat nomor yang sudah dibuang, misalnya "HUQEZ". Ditukar
         * saja, str_replace tidak menemukan apa-apa dan FAKTUR SERTA
         * KWITANSINYA bernama berkas sama persis: HUQEZ.pdf keduanya. Yang
         * mengunduh keduanya menimpa yang pertama, lalu memegang satu berkas
         * yang ia kira dua.
         *
         * Terukur 24 Okt 2026 saat perakit ini pertama dicoba.
         */
        $namaDasar = str_contains($nomor, $sumber['awalan'])
            ? str_replace($sumber['awalan'], $awalanBaru, $nomor)
            : $awalanBaru . $nomor;

        return [
            'isi' => $pdf->output(),
            'nama' => $namaDasar . '.pdf',
        ];
    }

    /** Layanan yang memang punya cetakan faktur/kwitansi. */
    public static function punyaCetakan(string $layanan): bool
    {
        return isset(self::SUMBER[$layanan]);
    }
}
