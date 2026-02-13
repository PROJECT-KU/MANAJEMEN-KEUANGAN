<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ClinikscopusPromo;
use Carbon\Carbon;
use App\Events\PromoStatusUpdated;

class ExpirePromoStatus extends Command
{
    protected $signature = 'promo:expire';
    protected $description = 'Non-activekan promo yang sudah lewat';

    public function handle()
    {
        $promos = ClinikscopusPromo::where('status', 'active')
            ->where('tanggal_selesai_promo', '<', Carbon::now())
            ->get();

        foreach ($promos as $promo) {
            $promo->status = 'non active';
            $promo->save();

            // Broadcast event ke frontend
            broadcast(new PromoStatusUpdated($promo))->toOthers();
        }

        $this->info($promos->count() . ' promo(s) expired.');
    }
}
