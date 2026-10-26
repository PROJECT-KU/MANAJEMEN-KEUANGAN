<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kepala keamanan untuk SETIAP jawaban.
 *
 * Terukur 26 Okt 2026: aplikasi ini tidak mengirim satu pun — tidak
 * Referrer-Policy, tidak X-Frame-Options, tidak X-Content-Type-Options.
 * Ketiganya satu baris masing-masing, dan ketiganya menutup hal yang tidak
 * terlihat sampai dipakai orang.
 */
class KepalaKeamanan
{
    public function handle(Request $request, Closure $next): Response
    {
        $jawab = $next($request);

        /*
         * REFERRER-POLICY — dan ini yang paling penting di aplikasi ini.
         *
         * Alamat halaman status pendaftaran memuat UUID yang tidak bisa
         * ditebak; UUID itulah satu-satunya yang menjaga nomor telepon,
         * afiliasi, daftar peserta, dan faktur seseorang. Alamat semacam itu
         * adalah rahasia pembawa, dan halaman itu memuat tautan keluar ke
         * WhatsApp serta mengantar orangnya ke gerbang pembayaran.
         *
         * Peramban masa kini memang sudah memakai strict-origin-when-cross-
         * origin sebagai nilai bawaan, jadi jalurnya tidak ikut terkirim.
         * Tetapi itu NILAI BAWAAN PERAMBAN, bukan keputusan kita: ia berbeda
         * antar peramban, berubah antar versi, dan bisa dilonggarkan oleh
         * kebijakan perusahaan. Rahasia tidak dititipkan pada nilai bawaan
         * pihak lain.
         */
        $jawab->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        /*
         * X-FRAME-OPTIONS — pencegah jebakan klik.
         *
         * Halaman status memuat borang POST yang mengubah keadaan: membuat
         * tagihan baru di gerbang, dan mengirim ulang surat. Token CSRF
         * menjaga kiriman dari situs lain, tetapi TIDAK menjaga dari jebakan
         * klik: di sana yang menekan tombolnya orang yang benar, di sesi yang
         * benar, dengan token yang benar — ia cuma tidak melihat apa yang ia
         * tekan karena halamannya dibingkai dan ditimpa.
         *
         * SAMEORIGIN, bukan DENY: ada layar panitia yang membingkai pratinjau
         * dokumennya sendiri.
         */
        $jawab->headers->set('X-Frame-Options', 'SAMEORIGIN');

        /*
         * X-CONTENT-TYPE-OPTIONS — melarang peramban menebak jenis berkas.
         *
         * Folder unggahan lama (2.376 bukti bayar) sudah memasangnya lewat
         * .htaccess, tetapi hanya folder itu. Dipasang di sini, seluruh
         * jawaban aplikasi ikut terlindung — termasuk berkas yang disajikan
         * lewat PHP, yang tidak pernah melewati .htaccess mana pun.
         */
        $jawab->headers->set('X-Content-Type-Options', 'nosniff');

        /*
         * Halaman status dan berkas yang dirakit untuk satu orang TIDAK
         * boleh disimpan mesin pencari maupun singgahan bersama.
         *
         * Halaman statusnya sudah memasang meta noindex sendiri, tetapi meta
         * hanya terbaca pada HTML — faktur dan kwitansi PDF-nya tidak. Dan
         * singgahan bersama (proksi kantor, CDN) tidak membaca meta sama
         * sekali.
         */
        if ($request->is('Scopus-Camp/Status/*', 'Webinar-Eksklusif/Status/*')) {
            $jawab->headers->set('X-Robots-Tag', 'noindex, nofollow');
            $jawab->headers->set('Cache-Control', 'private, no-store, max-age=0');
        }

        return $jawab;
    }
}
