<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UnreadCountUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public int $total,
        public int $unread,
    ) {
    }

    /**
     * Build the event with fresh counts for a user.
     */
    public static function for(User $user): self
    {
        return new self(
            $user->id,
            $user->notifications()->count(),
            $user->unreadNotifications()->count(),
        );
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('App.Models.User.' . $this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'notifications.count';
    }

    public function broadcastWith(): array
    {
        return [
            'total'  => $this->total,
            'unread' => $this->unread,
            'read'   => $this->total - $this->unread,
        ];
    }
}