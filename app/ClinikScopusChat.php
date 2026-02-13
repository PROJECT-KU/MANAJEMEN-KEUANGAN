<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\User;

class ClinikScopusChat extends Model
{
    protected $table = 'clinik_scopus_chats';

    protected $fillable = [
        'pemesanan_id',
        'sender_id',
        'receiver_id',
        'message',
        'images'
    ];
    protected $casts = [
        'images' => 'array'
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
