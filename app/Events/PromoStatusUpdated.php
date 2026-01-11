<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\ClinikscopusPromo;

class PromoStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $promo;

    public function __construct(ClinikscopusPromo $promo)
    {
        $this->promo = $promo;
    }

    public function broadcastOn()
    {
        return new Channel('promo-status');
    }

    public function broadcastAs()
    {
        return 'updated';
    }
}
