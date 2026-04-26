<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\Chat;
use App\Models\Message;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

$bot->onCommand('start', function (Nutgram $bot) {
    $bot->sendMessage('Здравствуйте! Ваше сообщение принято.');
})->description('Приветствие');

$bot->onText('.*', function (Nutgram $bot) {
    $from = $bot->update()->message->from;

    $telegramUser = TelegramUser::updateOrCreate(
        ['telegram_id' => $from->id],
        [
            'username'   => $from->username ?? null,
            'first_name' => $from->first_name,
            'last_name'  => $from->last_name ?? null,
        ]
    );

    $chat = Chat::firstOrCreate(['telegram_user_id' => $telegramUser->id]);

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
