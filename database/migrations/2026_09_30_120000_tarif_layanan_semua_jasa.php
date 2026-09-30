<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Satu tempat untuk tarif SELURUH layanan jasa, bukan cuma Clinik Scopus.
 *
 * Tabelnya sengaja TIDAK diganti nama meski namanya kini keliru
 * (clinikscopus_biaya_persesi). clinikscopus.biaya_persesi_id berkunci asing
 * ke sini dan sepuluh sesi sudah menunjuk barisnya; mengganti nama tabel
 * berarti membongkar dan memasang ulang kunci asing itu di basis data
 * produksi yang tidak bisa saya uji dari sini. Nama yang keliru jauh lebih
 * murah daripada kunci asing yang putus.
 *
 * Yang ditambahkan:
 *
 *   layanan    layanan mana tarif ini berlaku
 *   varian     pembeda di dalam satu layanan — Scopus Camp punya Jawa dan
 *              luar Jawa yang harganya berbeda, Bibliometrik punya online
 *              dan offline
 *   fasilitas  daftar yang didapat peserta, supaya admin tidak mengetik
 *              ulang isinya tiap kali membuat angkatan baru
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinikscopus_biaya_persesi', function (Blueprint $table) {
            $table->string('layanan', 40)->default('clinik_scopus')->after('id')->index();
            $table->string('varian', 40)->nullable()->after('layanan');
            $table->json('fasilitas')->nullable()->after('ppn');
        });

        // Baris yang sudah ada semuanya milik Clinik Scopus.
        DB::table('clinikscopus_biaya_persesi')->update(['layanan' => 'clinik_scopus']);

        /*
         * Tarif awal untuk empat layanan lain diisi dari harga yang MEMANG
         * sudah dipakai, bukan angka karangan — supaya layarnya berguna sejak
         * dibuka, bukan kosong dan menunggu diisi.
         *
         *   Scopus Camp   Yogyakarta & Jakarta 4.000.000-5.500.000 (Jawa),
         *                 Padang & Medan 5.500.000 (luar Jawa)
         *   Bibliometrik  Reguler 549.000, Bundling 999.000
         *
         * Scopus Kafe selama ini diketik per pendaftaran tanpa acuan sama
         * sekali; angkanya diambil dari biaya yang paling sering muncul.
         */
        $kafe = DB::table('pendaftaran_scopus_kafe')
            ->whereNotNull('biaya')
            ->where('biaya', '!=', '')
            ->selectRaw('biaya, COUNT(*) n')
            ->groupBy('biaya')
            ->orderByDesc('n')
            ->value('biaya');

        $bawaan = [
            ['scopus_camp', 'jawa', 4500000, 'Materi & modul pelatihan|Sertifikat|Konsumsi selama acara|Pendampingan pascaacara'],
            ['scopus_camp', 'luar_jawa', 5500000, 'Materi & modul pelatihan|Sertifikat|Konsumsi selama acara|Pendampingan pascaacara'],
            ['bibliometrik', 'online', 549000, 'Materi & rekaman|Sertifikat|Pendampingan analisis'],
            ['bibliometrik', 'offline', 999000, 'Materi cetak|Sertifikat|Konsumsi|Pendampingan analisis'],
            ['scopus_kafe', null, (int) preg_replace('/\D+/', '', (string) $kafe) ?: 150000, 'Ruang diskusi|Konsumsi|Pendampingan trainer'],
            ['online_training', null, 0, 'Delapan kali pertemuan daring|Pendampingan trainer|Pendampingan lanjutan tiga bulan'],
        ];

        foreach ($bawaan as [$layanan, $varian, $biaya, $fasilitas]) {
            $sudahAda = DB::table('clinikscopus_biaya_persesi')
                ->where('layanan', $layanan)
                ->where(fn ($q) => $varian === null ? $q->whereNull('varian') : $q->where('varian', $varian))
                ->exists();

            if ($sudahAda) {
                continue;
            }

            DB::table('clinikscopus_biaya_persesi')->insert([
                'id' => (string) Str::uuid(),
                'layanan' => $layanan,
                'varian' => $varian,
                'biaya_persesi' => (string) $biaya,
                'ppn' => null,
                'fasilitas' => json_encode(explode('|', $fasilitas)),
                // Online Training belum punya harga di sistem ini; barisnya
                // dibuat supaya fasilitasnya tercatat, tetapi TIDAK
                // diberlakukan sampai tarifnya benar-benar disetel.
                'status' => $biaya > 0 ? 'active' : 'non active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('clinikscopus_biaya_persesi')->where('layanan', '!=', 'clinik_scopus')->delete();

        Schema::table('clinikscopus_biaya_persesi', function (Blueprint $table) {
            $table->dropIndex(['layanan']);
            $table->dropColumn(['layanan', 'varian', 'fasilitas']);
        });
    }
};
