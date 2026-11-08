<?php

namespace App\Http\Controllers\Publict;

use App\Http\Controllers\Controller;
use App\Support\PendaftaranSemuaLayanan as Katalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * SATU alamat pemberitahuan DOKU untuk SEMUA layanan.
 *
 * ---------------------------------------------------------------------------
 * Kenapa ini harus ada
 * ---------------------------------------------------------------------------
 *
 * Tiap layanan bergerbang punya alamat pemberitahuannya sendiri —
 * /Scopus-Camp/pemberitahuan/doku, /Class-Online/pemberitahuan/doku, dan
 * seterusnya. Itu rapi di kode, dan SALAH di dasbor DOKU.
 *
 * Dasbor DOKU menyediakan SATU kolom "Notification URL" per KANAL PEMBAYARAN
 * — Virtual Account satu, QRIS satu — bukan satu per produk atau per
 * layanan. Keempat layanan kita memakai akun dan kanal yang sama, jadi
 * keempat alamat itu tidak mungkin didaftarkan bersamaan: yang terpasang
 * hanya satu, dan tiga layanan sisanya TIDAK PERNAH diberi tahu.
 *
 * Akibatnya tidak terlihat sebagai galat. Pembayarannya tetap tersusul saat
 * halaman status dibuka (susulkanDariGerbang), jadi yang membuka halamannya
 * akhirnya lunas juga — tetapi yang menutup tabnya sesudah membayar
 * menunggu tanpa batas, dan panitia melihat pendaftaran yang terlihat belum
 * bayar padahal uangnya sudah masuk.
 *
 * ---------------------------------------------------------------------------
 * Cara memilah
 * ---------------------------------------------------------------------------
 *
 * Nomor tagihannya sendiri yang menyebutkan pemiliknya: tiap layanan punya
 * AWALAN yang berbeda di katalog — CAMP, CO, WE, KAFE. Dibaca dari katalog,
 * bukan ditulis ulang di sini, supaya layanan bergerbang berikutnya ikut
 * tersortir tanpa menyentuh berkas ini.
 *
 * Nomor percobaan kedua berakhiran -R2, -R3; awalannya tidak berubah, jadi
 * pemilahan tetap benar.
 *
 * ---------------------------------------------------------------------------
 * Kenapa MENERUSKAN, bukan menyalin logikanya
 * ---------------------------------------------------------------------------
 *
 * Tiap pengendali layanan sudah memeriksa tanda tangan, menolak
 * pemberitahuan ganda, menandai lunas, mengirim surat, dan mengabari
 * WhatsApp — dengan kosakata status dan aturan yang berbeda-beda. Menyalin
 * semuanya ke sini berarti dua rakitan untuk satu pekerjaan, dan yang kedua
 * akan berbeda perlahan pada hal yang paling sulit diuji: uang.
 *
 * Permintaannya diteruskan APA ADANYA, jadi pemeriksaan tanda tangan di
 * dalamnya memakai jalur permintaan yang sebenarnya — yaitu alamat INI,
 * yang memang yang ditandatangani DOKU.
 *
 * Keempat alamat lama TETAP HIDUP. Yang sudah terlanjur terdaftar di dasbor
 * tidak boleh mati hanya karena ada jalan baru.
 */
class PemberitahuanDokuController extends Controller
{
    /**
     * Pengendali yang memiliki tiap layanan bergerbang.
     *
     * Ditulis di sini, bukan di katalog: katalog dibaca juga oleh layar
     * panitia dan perintah terjadwal, dan tidak satu pun dari mereka perlu
     * tahu nama pengendali publik.
     *
     * @var array<string, class-string>
     */
    private const PENGENDALI = [
        'scopus_camp' => PublicScopusCampController::class,
        'online_training' => PublicOnlineTrainingController::class,
        'scopus_kafe' => PublicScopusKafeController::class,
        'webinar_eksklusif' => PublicWebinarEksklusifController::class,
    ];

    public function __invoke(Request $request)
    {
        $nomor = trim((string) $request->input('order.invoice_number'));
        $layanan = self::layananDariNomor($nomor);

        if ($layanan === null) {
            /*
             * 200, bukan galat. Nomor yang tidak dikenali di sini sama
             * artinya dengan nomor yang tidak dikenali di pengendali
             * layanan: menjawab bukan-200 membuat DOKU mengulang
             * pemberitahuan yang sama berhari-hari.
             *
             * Nomornya dicatat supaya awalan baru yang terlewat ketahuan
             * dari log, bukan dari pendaftar yang menunggu.
             */
            Log::warning('Pemberitahuan DOKU tidak bisa dipilah: awalan nomornya tidak dikenali', [
                'nomor' => $nomor,
                'ip' => $request->ip(),
            ]);

            return response()->json(['ok' => true]);
        }

        return app(self::PENGENDALI[$layanan])->pemberitahuan($request);
    }

    /**
     * Layanan pemilik satu nomor tagihan, atau null.
     *
     * Awalan TERPANJANG didahulukan. Tanpa itu, awalan yang jadi awalan
     * milik layanan lain — misalnya "CO" di depan "CONTOH" kelak — memilah
     * ke layanan yang salah, dan pembayarannya tercatat di pendaftaran
     * orang lain.
     */
    public static function layananDariNomor(string $nomor): ?string
    {
        $nomor = mb_strtoupper(trim($nomor));

        if ($nomor === '') {
            return null;
        }

        $awalan = [];

        foreach (Katalog::semuaSumber() as $kunci => $sumber) {
            if (empty($sumber['pakai_gerbang']) || ! isset(self::PENGENDALI[$kunci])) {
                continue;
            }

            $a = mb_strtoupper(trim((string) ($sumber['nomor_awalan'] ?? '')));

            if ($a !== '') {
                $awalan[$kunci] = $a;
            }
        }

        arsort($awalan, SORT_NATURAL);

        foreach ($awalan as $kunci => $a) {
            if (str_starts_with($nomor, $a . '-')) {
                return $kunci;
            }
        }

        return null;
    }
}
