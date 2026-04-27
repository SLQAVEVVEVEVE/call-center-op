<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\Chat;
use App\Models\Message;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SergiX44\Nutgram\Nutgram;

$resolveChat = function (Nutgram $bot): Chat {
    $from = $bot->update()->message->from;

    $telegramUser = TelegramUser::updateOrCreate(
        ['telegram_id' => $from->id],
        [
            'username'   => $from->username ?? null,
            'first_name' => $from->first_name,
            'last_name'  => $from->last_name ?? null,
        ]
    );

    return Chat::firstOrCreate(['telegram_user_id' => $telegramUser->id]);
};

$bot->onCommand('start', function (Nutgram $bot) {
    $bot->sendMessage('Здравствуйте! Ваше сообщение принято.');
})->description('Приветствие');

// IMPORTANT: onPhoto must be registered BEFORE onText('.*') —
// Nutgram dispatches handlers in registration order; the regex
// fallback would otherwise swallow photo updates.
$bot->onPhoto(function (Nutgram $bot) use ($resolveChat) {
    $chat = $resolveChat($bot);

    $update = $bot->update()->message;
    $photos = $update->photo ?? [];
    if (empty($photos)) {
        return;
    }

    // Telegram sends an array of PhotoSize objects in ascending size — pick the largest.
    $largest = end($photos);

    try {
        $file = $bot->getFile($largest->file_id);
    } catch (\Throwable $e) {
        Log::warning('telegram.getFile_failed', ['error' => $e->getMessage()]);
        return;
    }

    // Defensive size check (Telegram caps photos at 10 MB; reject anything wildly larger).
    if (($file->file_size ?? 0) > 20 * 1024 * 1024) {
        Log::warning('telegram.photo_too_large', ['size' => $file->file_size]);
        return;
    }

    try {
        $contents = $bot->downloadFile($file);
    } catch (\Throwable $e) {
        Log::warning('telegram.downloadFile_failed', ['error' => $e->getMessage()]);
        return;
    }

    $ext = pathinfo($file->file_path ?? '', PATHINFO_EXTENSION) ?: 'jpg';
    $relPath = "attachments/{$chat->id}/" . Str::uuid() . ".{$ext}";
    Storage::disk('public')->put($relPath, $contents);

    $message = Message::updateOrCreate(
        ['telegram_message_id' => $update->message_id],
        [
            'chat_id'     => $chat->id,
            'direction'   => 'in',
            'body'        => $update->caption,
            'author_type' => 'user',
        ]
    );

    $message->attachments()->create([
        'kind'             => 'photo',
        'disk'             => 'public',
        'path'             => $relPath,
        'mime_type'        => 'image/jpeg',
        'size_bytes'       => $largest->file_size ?? null,
        'width'            => $largest->width ?? null,
        'height'           => $largest->height ?? null,
        'telegram_file_id' => $largest->file_id,
    ]);

    $chat->last_message_at = now();
    $chat->increment('unread_count');
    $chat->save();

    broadcast(new \App\Events\MessageReceived($message->load('attachments'), $chat));

    Log::info('telegram.photo_in', [
        'chat_id'    => $chat->id,
        'message_id' => $message->id,
        'path'       => $relPath,
    ]);
});

$bot->onText('.*', function (Nutgram $bot) use ($resolveChat) {
    $chat = $resolveChat($bot);

    $message = Message::updateOrCreate(
        ['telegram_message_id' => $bot->update()->message->message_id],
        [
            'chat_id'     => $chat->id,
            'direction'   => 'in',
            'body'        => $bot->update()->message->text,
            'author_type' => 'user',
        ]
    );

    $chat->last_message_at = now();
    $chat->increment('unread_count');
    $chat->save();

    broadcast(new \App\Events\MessageReceived($message, $chat));

    Log::info('telegram.message_in', [
        'chat_id'    => $chat->id,
        'message_id' => $message->id,
    ]);
});
