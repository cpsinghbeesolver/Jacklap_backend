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
        public array $latest = [],
    ) {
    }

    /**
     * Build the event with fresh counts and the latest 10 notifications for a user.
     */
    public static function for(User $user): self
    {
        $latest = $user->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'data'       => $n->data,
                'read_at'    => $n->read_at,
                'created_at' => $n->created_at,
            ])
            ->toArray();

        return new self(
            $user->id,
            $user->notifications()->count(),
            $user->unreadNotifications()->count(),
            $latest,
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
            'notifications' => $this->latest,
        ];
    }
}