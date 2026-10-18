<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda bahwa kabar "kursi Anda dilepas" sudah dikirim.
 *
 * Satu pendaftaran bisa dinyatakan kedaluwarsa dari TIGA arah: perintah
 * terjadwal yang menyapu, halaman sesi yang melepas kursi secara oportunis
 * saat dibuka orang lain, dan pemberitahuan "expired" dari DOKU. Ketiganya
 * sah, dan ketiganya bisa terjadi untuk baris yang sama dalam hitungan
 * detik.
 *
 * Tanpa penanda ini, "kirim sekali" bersandar pada tebakan bahwa cabang
 * yang memindahkan statusnya hanya dimasuki sekali — dan tebakan itu putus
 * diam-diam begitu ada jalur keempat. Yang menanggung orangnya: tiga surat
 * berbunyi sama di kotak masuk, pada menit yang sama.
 *
 * Waktunya disimpan, bukan sekadar benar/salah: saat ada yang mengadu
 * "saya tidak pernah diberi tahu", yang dicari jam pastinya.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('webinar_eksklusif_pendaftaran', 'kedaluwarsa_dikabari_pada')) {
            return;
        }

        Schema::table('webinar_eksklusif_pendaftaran', function (Blueprint $t) {
            $t->timestamp('kedaluwarsa_dikabari_pada')->nullable()->after('kedaluwarsa_pada');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('webinar_eksklusif_pendaftaran', 'kedaluwarsa_dikabari_pada')) {
            return;
        }

        Schema::table('webinar_eksklusif_pendaftaran', function (Blueprint $t) {
            $t->dropColumn('kedaluwarsa_dikabari_pada');
        });
    }
};
