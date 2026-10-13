<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tiga hal yang membuat promo bisa dipertanggungjawabkan.
 *
 * 1. `promo_jejak` — siapa mengubah apa, kapan. Bentuknya sama persis dengan
 *    `angkatan_jejak` yang sudah ada; promo mengubah HARGA, jadi tidak boleh
 *    kalah terlacak dibanding angkatan.
 *
 * 2. `promo_layanan.kuota` — "promo untuk 50 pendaftar pertama". Sebelumnya
 *    satu-satunya cara menghentikan promo yang kebablasan adalah menunggu ada
 *    yang menyadarinya lalu mematikannya tangan.
 *
 * 3. `promo_id` pada ketiga tabel pendaftaran — tali ke promonya.
 *
 * Yang ketiga perlu dijelaskan. Nama promonya memang sudah tersimpan di
 * `kode_diskon` ("PROMO: Ulang Tahun ke-8"), dan untuk DIBACA MANUSIA itu
 * cukup. Tetapi menghitung dampak promo dari untaian nama akan berbohong
 * begitu promonya diganti nama — "Promo Lebaran" jadi "Promo Lebaran 2026"
 * membuat seluruh pendaftaran lama berhenti terhitung, tanpa galat dan tanpa
 * ada yang curiga. Angkanya tinggal turun.
 *
 * Jadi keduanya disimpan: id untuk menghitung, nama untuk dibaca. Namanya
 * TETAP disalin karena promo boleh dihapus, dan pendaftaran yang promonya
 * sudah hilang harus tetap bisa dijelaskan.
 */
return new class extends Migration
{
    /** Tabel pendaftaran yang punya kolom diskon, jadi bisa kena promo. */
    private const PENDAFTARAN = [
        'scopus_camp_pendaftaran',
        'analisis_bibliometrik',
        'webinar_eksklusif_pendaftaran',
    ];

    public function up(): void
    {
        Schema::create('promo_jejak', function (Blueprint $t) {
            /*
             * Kunci utamanya BERURUT, bukan UUID seperti angkatan_jejak.
             *
             * Jejak selalu dibaca "terbaru dulu", dan satu-satunya pengurut
             * yang tersedia pada UUID adalah waktunya. Dua peristiwa pada
             * promo yang sama bisa jatuh di milidetik yang sama — dibuat lalu
             * langsung dimatikan oleh kode yang sama — dan saat itu urutannya
             * ditentukan apa pun yang kebetulan dikembalikan MySQL. Terukur:
             * ujinya hijau kalau dijalankan sendiri, merah di rangkaian penuh.
             *
             * Barisnya tidak pernah ditunjuk dari alamat halaman, jadi tidak
             * ada alasan menyembunyikan nomor urutnya.
             */
            $t->id();
            $t->char('promo_id', 36);

            /*
             * Nama promonya DISALIN, bukan ditunjuk lewat relasi. Promo boleh
             * dihapus, dan jejak yang kehilangan nama subjeknya kehilangan
             * separuh gunanya — persis alasan yang sama dipakai angkatan_jejak
             * untuk menyalin nama penggunanya.
             */
            $t->string('nama')->nullable();
            $t->string('aksi', 20);
            $t->text('ringkasan')->nullable();
            $t->json('data')->nullable();
            $t->unsignedBigInteger('oleh_id')->nullable();
            $t->string('oleh_nama')->nullable();

            /*
             * Presisi MILIDETIK, bukan detik bawaannya.
             *
             * Jejak diurutkan "terbaru dulu" dari kolom ini. Dengan presisi
             * detik, dua peristiwa pada promo yang sama dalam satu detik —
             * dibuat lalu langsung dimatikan, atau diubah dua kali
             * berturut-turut — seri, dan urutannya ditentukan apa pun yang
             * kebetulan dikembalikan MySQL. Terukur: `jejak()->first()`
             * memulangkan baris "dibuat" sesudah ada baris "dimatikan" yang
             * lebih baru.
             *
             * Idnya UUID acak, jadi tidak bisa jadi pengurut kedua.
             */
            $t->timestamp('created_at', 3)->nullable();

            $t->index('promo_id');
            $t->index('created_at');
        });

        Schema::table('promo_layanan', function (Blueprint $t) {
            /*
             * Batas berapa PENDAFTARAN yang boleh kena promo ini. NULL berarti
             * tanpa batas — berbeda artinya dari 0, yang berarti promonya
             * sudah habis sejak detik pertama.
             */
            $t->unsignedInteger('kuota')->nullable()->after('batas_potongan');
        });

        foreach (self::PENDAFTARAN as $tabel) {
            if (! Schema::hasTable($tabel) || Schema::hasColumn($tabel, 'promo_id')) {
                continue;
            }

            Schema::table($tabel, function (Blueprint $t) use ($tabel) {
                $t->char('promo_id', 36)->nullable()->index($tabel . '_promo');
            });
        }
    }

    public function down(): void
    {
        foreach (self::PENDAFTARAN as $tabel) {
            if (Schema::hasTable($tabel) && Schema::hasColumn($tabel, 'promo_id')) {
                Schema::table($tabel, function (Blueprint $t) use ($tabel) {
                    $t->dropIndex($tabel . '_promo');
                    $t->dropColumn('promo_id');
                });
            }
        }

        Schema::table('promo_layanan', fn (Blueprint $t) => $t->dropColumn('kuota'));

        Schema::dropIfExists('promo_jejak');
    }
};
