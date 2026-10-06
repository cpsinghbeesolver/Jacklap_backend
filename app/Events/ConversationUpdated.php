<?php

namespace App\Events;

use App\Models\ChatModule\Conversation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public array $conversations = [],
    ) {
    }

    /** Same query as the conversations() API, limited to the latest $limit. */
    public static function for(int $userId, int $limit = 10): self
    {
        $conversations = Conversation::with([
                'provider:id,name,image',
                'seeker:id,name,image',
                'latestMessage',
                'booking:id,status',
            ])
            ->where(function ($q) use ($userId) {
                $q->where('provider_id', $userId)->orWhere('seeker_id', $userId);
            })
            ->orderByDesc('last_message_at')
            ->limit($limit)
            ->get()
            ->toArray();

        return new self($userId, $conversations);
    }

    /** Refresh the list for both participants of a conversation. */
    public static function dispatchToParticipants(Conversation $conversation, int $limit = 10): void
    {
        foreach (array_unique([$conversation->provider_id, $conversation->seeker_id]) as $userId) {
            event(self::for((int) $userId, $limit));
        }
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('App.Models.User.' . $this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'conversations.list';
    }

    public function broadcastWith(): array
    {
        return ['conversations' => $this->conversations];
    }
}