<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class CustomDatabaseChannel
{
    public function send($notifiable, Notification $notification)
    {
        // Generate or use provided ID
        $id = $notification->id ?? (string) \Illuminate\Support\Str::uuid();
        $utcNow = Carbon::now('UTC');

        // Create via relationship (so it hydrates a model)
        return $notifiable->notifications()->create([
            'id'             => $id,
            'type'           => get_class($notification),
            'data'           => $notification->toCustomDatabase($notifiable),
            'read_at'        => null,
            'created_at'=> $utcNow,
            'updated_at'=> $utcNow
        ]);
    }
}
