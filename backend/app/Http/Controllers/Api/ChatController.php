<?php

namespace App\Http\Controllers\Api;

use App\Events\ChatUpdated;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChatController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $chats = Chat::with(['telegramUser', 'assignedTo', 'latestMessage.attachments'])
            ->orderByDesc('last_message_at')
            ->limit(50)
            ->get();

        return ChatResource::collection($chats);
    }

    public function update(Request $request, Chat $chat): ChatResource
    {
        $validated = $request->validate([
            'status'              => ['sometimes', 'string', 'in:new,in_progress,resolved'],
            'assigned_to_user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ]);

        $chat->update($validated);

        broadcast(new ChatUpdated($chat))->toOthers();

        return new ChatResource($chat->load(['telegramUser', 'assignedTo', 'latestMessage.attachments']));
    }
}
