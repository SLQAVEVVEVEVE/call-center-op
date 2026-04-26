<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use App\Models\Chat;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChatController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $chats = Chat::with(['telegramUser', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->limit(50)
            ->get();

        return ChatResource::collection($chats);
    }
}
