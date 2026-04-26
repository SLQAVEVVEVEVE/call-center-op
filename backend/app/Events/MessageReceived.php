<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Message $message,
        public readonly Chat $chat,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel("chat.{$this->chat->id}")];
        foreach (User::all() as $user) {
            $channels[] = new PrivateChannel("operator.{$user->id}");
        }
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'message.received';
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->message->id,
            'chat_id'    => $this->message->chat_id,
            'body'       => $this->message->body,
            'direction'  => $this->message->direction,
            'created_at' => $this->message->created_at?->toISOString(),
        ];
    }
}
