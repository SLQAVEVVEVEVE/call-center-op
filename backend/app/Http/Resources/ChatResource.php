<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'unread_count'   => $this->unread_count,
            'last_message_at'=> $this->last_message_at?->toISOString(),
            'telegram_user'  => $this->whenLoaded('telegramUser'),
            'last_message'   => $this->whenLoaded('latestMessage', fn ($m) => new MessageResource($m)),
            'created_at'     => $this->created_at?->toISOString(),
        ];
    }
}
