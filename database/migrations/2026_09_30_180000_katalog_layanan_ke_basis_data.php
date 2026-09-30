<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Memindahkan katalog layanan dari kode ke basis data.
 *
 * Sebelum ini daftar layanan ditulis sebagai konstanta di model, jadi menambah
 * satu layanan berarti mengubah kode lalu deploy. Untuk sistem yang
 * penggunanya admin, itu salah tempat: layanan baru datang dari keputusan
 * bisnis, bukan dari rilis.
 *
 * Bentuknya sengaja SAMA PERSIS dengan konstanta yang digantikannya — nama,
 * satuan, ikon, warna, varian — supaya kedua belas pembacanya tidak perlu
 * diubah selain sumber datanya.
 *
 * `kode` yang jadi kuncinya, bukan id: nilai itulah yang sudah tersimpan di
 * kolom `layanan` pada `clinikscopus_biaya_persesi` dan `kategori_layanan`.
 * Karena itu kode tidak boleh berubah setelah dipakai, dan layarnya
 * menguncinya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan', function (Blueprint $t) {
            $t->uuid('id')->primary();
            // Dipakai sebagai kunci di dua tabel lain; unik dan tidak diubah.
            $t->string('kode', 40)->unique();
            $t->string('nama');
            $t->string('satuan', 60)->default('per satuan');
            $t->string('ikon', 60)->default('fa-tag');
            $t->string('warna', 30)->default('mis-ungu');
            /*
             * Varian disimpan sebagai json kode => nama, bentuk yang sama
             * dengan konstanta lamanya. Tabel sendiri belum sepadan: variannya
             * paling banyak dua per layanan dan tidak punya data lain.
             */
            $t->json('varian')->nullable();
            $t->unsignedSmallInteger('urutan')->default(0);
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });

        $this->isiDariKatalogLama();
    }

    /**
     * Lima layanan yang selama ini ditulis di kode.
     *
     * Disalin apa adanya, bukan dibaca dari konstantanya: migrasi harus
     * menghasilkan yang sama walau konstantanya sudah dibuang dari kode nanti.
     */
    private function isiDariKatalogLama(): void
    {
        $katalog = [
            ['clinik_scopus', 'Clinik Scopus', 'per satu sesi', 'fa-user-md', 'mis-biru', null],
            ['bibliometrik', 'Analisis Bibliometrik', 'per peserta', 'fa-chart-line', 'mis-ungu',
                ['online' => 'Online', 'offline' => 'Offline']],
            ['scopus_camp', 'Scopus Camp', 'per peserta', 'fa-campground', 'mis-hijau',
                ['jawa' => 'Pulau Jawa', 'luar_jawa' => 'Luar Pulau Jawa']],
            ['scopus_kafe', 'Scopus Kafe', 'per pertemuan', 'fa-coffee', 'mis-jingga', null],
            ['online_training', 'Online Training', 'per paket', 'fa-chalkboard-teacher', 'mis-kuning', null],
        ];

        $isi = [];

        foreach ($katalog as $i => [$kode, $nama, $satuan, $ikon, $warna, $varian]) {
            $isi[] = [
                'id' => (string) Str::uuid(),
                'kode' => $kode,
                'nama' => $nama,
                'satuan' => $satuan,
                'ikon' => $ikon,
                'warna' => $warna,
                'varian' => $varian ? json_encode($varian) : null,
                'urutan' => ($i + 1) * 10,
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('layanan')->insert($isi);
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan');
    }
};
