<?php

namespace Tests\Feature;

use App\Events\ChatUpdated;
use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\Message;
use App\Models\TelegramUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use SergiX44\Nutgram\Nutgram;
use Tests\TestCase;

class ApiFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_api_flow(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $tgUser = TelegramUser::create([
            'telegram_id' => 123456789,
            'username'    => 'tester',
            'first_name'  => 'Test',
            'last_name'   => 'User',
        ]);

        $chat = Chat::create([
            'telegram_user_id' => $tgUser->id,
            'last_message_at'  => now(),
            'unread_count'     => 1,
        ]);

        Message::create([
            'chat_id'     => $chat->id,
            'direction'   => 'in',
            'body'        => 'Hello from user',
            'author_type' => 'user',
        ]);

        $listResponse = $this->getJson('/api/chats');
        $listResponse->assertStatus(200);
        $listResponse->assertJsonFragment(['id' => $chat->id]);

        $historyResponse = $this->getJson("/api/chats/{$chat->id}/messages");
        $historyResponse->assertStatus(200);

        $bot = $this->mock(Nutgram::class);
        $bot->shouldReceive('sendMessage')->once()->andReturn((object) ['message_id' => 999]);

        Event::fake([MessageSent::class, ChatUpdated::class]);

        $sendResponse = $this->postJson("/api/chats/{$chat->id}/messages", [
            'body' => 'Reply from operator',
        ]);

        $sendResponse->assertStatus(201);

        $this->assertDatabaseHas('messages', [
            'chat_id'   => $chat->id,
            'direction' => 'out',
            'body'      => 'Reply from operator',
        ]);

        // Auto-assign on first reply (Phase 06): chat should now belong to the operator
        // and bump from 'new' to 'in_progress'.
        $this->assertDatabaseHas('chats', [
            'id'                  => $chat->id,
            'assigned_to_user_id' => $user->id,
            'status'              => 'in_progress',
        ]);

        Event::assertDispatched(MessageSent::class);
        Event::assertDispatched(ChatUpdated::class);
    }
}
