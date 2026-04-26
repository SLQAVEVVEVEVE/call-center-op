<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

class MessageController extends Controller
{
    public function index(Chat $chat): AnonymousResourceCollection
    {
        $messages = $chat->messages()
            ->orderBy('created_at')
            ->limit(100)
            ->get();

        return MessageResource::collection($messages);
    }

    public function store(Request $request, Chat $chat): JsonResponse
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:4000']]);

        $message = Message::create([
            'chat_id'             => $chat->id,
            'direction'           => 'out',
            'body'                => $validated['body'],
            'author_type'         => 'operator',
            'telegram_message_id' => null,
        ]);

        try {
            $result = app(Nutgram::class)->sendMessage(
                chat_id: $chat->telegramUser->telegram_id,
                text: $message->body,
            );

            if ($result && isset($result->message_id)) {
                $message->update(['telegram_message_id' => $result->message_id]);
            }
        } catch (\Throwable $e) {
            Log::warning('telegram.send_failed', [
                'chat_id' => $chat->id,
                'error'   => $e->getMessage(),
            ]);
        }

        $chat->update(['last_message_at' => now(), 'unread_count' => 0]);

        broadcast(new MessageSent($message, $chat));

        return (new MessageResource($message->fresh()))->response()->setStatusCode(201);
    }
}
