<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

class ChatsController extends Controller
{
    /**
     * GET /api/chats
     * List all chats ordered by latest activity, with telegram user info.
     */
    public function index(): JsonResponse
    {
        $chats = Chat::with('telegramUser')
            ->orderByDesc('last_message_at')
            ->get();

        return response()->json($chats);
    }

    /**
     * GET /api/chats/{chat}/messages
     * Return message history for a chat (oldest first).
     */
    public function messages(Chat $chat): JsonResponse
    {
        $messages = $chat->messages()
            ->orderBy('created_at')
            ->get();

        return response()->json($messages);
    }

    /**
     * POST /api/chats/{chat}/messages
     * Operator sends a reply — saves to DB, broadcasts via Reverb, sends to Telegram.
     */
    public function sendMessage(Request $request, Chat $chat, Nutgram $bot): JsonResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:4096'],
        ]);

        $message = Message::create([
            'chat_id'     => $chat->id,
            'direction'   => 'out',
            'body'        => $data['body'],
            'author_type' => 'operator',
        ]);

        // Reset unread counter
        $chat->update(['unread_count' => 0]);

        broadcast(new MessageSent($message, $chat))->toOthers();

        // Send reply to Telegram user (best-effort — don't 500 on network issues)
        try {
            $telegramId = $chat->telegramUser->telegram_id;
            $bot->sendMessage($data['body'], chat_id: $telegramId);
        } catch (\Throwable $e) {
            Log::warning('telegram.send_failed', [
                'chat_id' => $chat->id,
                'error'   => $e->getMessage(),
            ]);
        }

        return response()->json($message, 201);
    }
}
