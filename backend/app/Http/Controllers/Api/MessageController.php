<?php

namespace App\Http\Controllers\Api;

use App\Events\ChatUpdated;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Internal\InputFile;

class MessageController extends Controller
{
    public function index(Chat $chat): AnonymousResourceCollection
    {
        $messages = $chat->messages()
            ->with('attachments')
            ->orderBy('created_at')
            ->limit(100)
            ->get();

        return MessageResource::collection($messages);
    }

    public function store(Request $request, Chat $chat): JsonResponse
    {
        $validated = $request->validate([
            'body'  => ['nullable', 'string', 'max:4000'],
            'image' => ['nullable', 'image', 'max:8192'],
        ]);

        if (empty($validated['body']) && ! $request->hasFile('image')) {
            return response()->json([
                'message' => 'Either body or image is required.',
                'errors'  => ['body' => ['Either body or image is required.']],
            ], 422);
        }

        $message = Message::create([
            'chat_id'             => $chat->id,
            'direction'           => 'out',
            'body'                => $validated['body'] ?? null,
            'author_type'         => 'operator',
            'telegram_message_id' => null,
        ]);

        $bot = app(Nutgram::class);
        $telegramId = $chat->telegramUser->telegram_id;

        $relPath = null;
        if ($request->hasFile('image')) {
            $relPath = $request->file('image')->store("attachments/{$chat->id}", 'public');

            $message->attachments()->create([
                'kind'          => 'photo',
                'disk'          => 'public',
                'path'          => $relPath,
                'mime_type'     => $request->file('image')->getMimeType(),
                'size_bytes'    => $request->file('image')->getSize(),
                'original_name' => $request->file('image')->getClientOriginalName(),
            ]);

            try {
                $result = $bot->sendPhoto(
                    chat_id: $telegramId,
                    photo: InputFile::make(Storage::disk('public')->path($relPath)),
                    caption: $message->body,
                );

                if ($result && isset($result->message_id)) {
                    $message->update(['telegram_message_id' => $result->message_id]);
                }

                if ($result && ! empty($result->photo)) {
                    $largest = end($result->photo);
                    $message->attachments()->latest('id')->first()
                        ?->update(['telegram_file_id' => $largest->file_id ?? null]);
                }
            } catch (\Throwable $e) {
                Log::warning('telegram.sendPhoto_failed', [
                    'chat_id' => $chat->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        } else {
            try {
                $result = $bot->sendMessage(
                    chat_id: $telegramId,
                    text: $message->body,
                );

                if ($result && isset($result->message_id)) {
                    $message->update(['telegram_message_id' => $result->message_id]);
                }
            } catch (\Throwable $e) {
                Log::warning('telegram.sendMessage_failed', [
                    'chat_id' => $chat->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        $chat->last_message_at = now();
        $chat->unread_count = 0;

        // Auto-assign on first operator reply — UX glue between Features 1 and 2.
        $chatChanged = false;
        if ($chat->assigned_to_user_id === null) {
            $chat->assigned_to_user_id = $request->user()->id;
            if ($chat->status === 'new') {
                $chat->status = 'in_progress';
            }
            $chatChanged = true;
        }
        $chat->save();

        broadcast(new MessageSent($message->load('attachments'), $chat));

        if ($chatChanged) {
            broadcast(new ChatUpdated($chat));
        }

        return (new MessageResource($message->fresh()->load('attachments')))
            ->response()
            ->setStatusCode(201);
    }
}
