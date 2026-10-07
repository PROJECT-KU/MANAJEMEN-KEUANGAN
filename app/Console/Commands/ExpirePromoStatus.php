<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ClinikScopusPromo;
use Carbon\Carbon;

class ExpirePromoStatus extends Command
{
    protected $signature = 'promo:expire';
    protected $description = 'Non-activekan promo yang sudah lewat';

    public function handle()
    {
        $promos = ClinikScopusPromo::where('status', 'active')
            ->where('tanggal_selesai_promo', '<', Carbon::now())
            ->get();

        foreach ($promos as $promo) {
            $promo->status = 'non active';
            $promo->save();
        }

        /*
         * Siaran ke sisi depan DIBUANG.
         *
         * Barisnya dulu `broadcast(new PromoStatusUpdated($promo))->toOthers()`
         * — dan kelas App\Events\PromoStatusUpdated TIDAK PERNAH ADA; folder
         * app/Events pun tidak ada. Jadi barisnya selalu melempar
         * "Class not found", DI DALAM perulangan: promo pertama tersimpan,
         * lalu perintahnya mati dan sisanya tidak pernah dinonaktifkan.
         *
         * Tidak terlihat selama ini karena perintahnya sudah gagal lebih dulu
         * di baris atasnya (kelas model yang tidak bisa dimuat di Linux), dan
         * sebelum itu penjadwalnya sendiri mati karena proc_open.
         *
         * Tidak ada satu pun yang mendengarkan siarannya — ditelusuri ke
         * seluruh app/, routes/, dan resources/. Yang dibutuhkan layar adalah
         * statusnya benar saat halaman dimuat, dan itu sudah dikerjakan
         * $promo->save() di atas.
         */

        $this->info($promos->count() . ' promo(s) expired.');
    }
}
