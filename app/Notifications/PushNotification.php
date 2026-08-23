<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Channels\CustomDatabaseChannel;
class PushNotification extends Notification
{
    use Queueable;

    public $id;
    protected $title;
    protected $body;
    protected $data;

    public function __construct($title, $body, $data = [], $id = null)
    {
        $this->id    = $id ?? (string) \Illuminate\Support\Str::uuid();
        $this->title = $title;
        $this->body  = $body;
        $this->data  = $data;
    }

    public function via($notifiable)
    {
        // Use custom database channel instead of default
        return [CustomDatabaseChannel::class];
    }

    public function toCustomDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'body'  => $this->body,
            'data'  => $this->data,
        ];
    }
}
