<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'chat_id'             => $this->chat_id,
            'direction'           => $this->direction,
            'body'                => $this->body,
            'author_type'         => $this->author_type,
            'telegram_message_id' => $this->telegram_message_id,
            'created_at'          => $this->created_at?->toISOString(),
        ];
    }
}
