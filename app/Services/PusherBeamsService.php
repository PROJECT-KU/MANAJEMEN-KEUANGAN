<?php

namespace App\Services;

use Pusher\PushNotifications\PushNotifications;

class PusherBeamsService
{
    protected $beamsClient;

    public function __construct()
    {
        $this->beamsClient = new PushNotifications([
            "instanceId" => env('PUSHER_BEAMS_INSTANCE_ID'),
            "secretKey"  => env('PUSHER_BEAMS_SECRET_KEY'),
        ]);
    }

    public function sendNotification($userId, $title, $message)
    {
        try {
            return $this->beamsClient->publishToUsers(
                ["user-{$userId}"], // Format user-ID agar sesuai dengan client
                [
                    "fcm" => [
                        "notification" => [
                            "title" => $title,
                            "body"  => $message,
                        ]
                    ],
                    "apns" => [
                        "aps" => [
                            "alert" => [
                                "title" => $title,
                                "body"  => $message,
                            ]
                        ]
                    ]
                ]
            );
        } catch (\Exception $e) {
            \Log::error("Pusher Beams Error: " . $e->getMessage());
        }
    }
}
