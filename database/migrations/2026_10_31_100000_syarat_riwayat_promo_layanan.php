<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Syarat "pernah ikut layanan lain" pada promo.
 *
 * Keputusan pemiliknya 31 Okt 2026: promo boleh menuntut bahwa nomor
 * WhatsApp pendaftarnya PERNAH IKUT DAN LUNAS di layanan lain. Alumni
 * Webinar Eksklusif dapat potongan saat mendaftar Scopus Camp; yang baru
 * mendaftar tetapi belum membayar TIDAK dapat.
 *
 * Layanannya dipilih admin, bukan dipatok di kode: pasangan layanan yang
 * dianggap "alumni" berubah tiap kampanye, dan setiap perubahan yang
 * menuntut rilis baru akan berakhir sebagai kampanye yang tidak jadi
 * dijalankan.
 *
 * Kolomnya BOLEH KOSONG, dan kosong berarti tanpa syarat riwayat — itu yang
 * menjaga promo yang sudah ada tetap berjalan seperti sebelumnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_layanan', function (Blueprint $table) {
            /*
             * json, bukan tabel pivot tersendiri.
             *
             * Isinya daftar KUNCI LAYANAN ('scopus_camp', 'webinar_eksklusif'),
             * bukan kunci baris — tidak ada tabel `layanan` yang bisa
             * dijadikan kunci asing untuk semuanya, dan katalognya memang
             * hidup sebagai data di PendaftaranSemuaLayanan. Pivot ke tabel
             * yang tidak ada hanya akan jadi tabel tali tanpa ujung.
             */
            $table->json('syarat_layanan')->nullable()->after('kode');
        });
    }

    public function down(): void
    {
        Schema::table('promo_layanan', function (Blueprint $table) {
            $table->dropColumn('syarat_layanan');
        });
    }
};
