<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Models\Console;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ConsoleTest extends TestCase
{

    use DatabaseTransactions;

    public function test_list_console(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->get('/api/console');

        $response->assertStatus(200);
    }

    public function test_get_console(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->get('/api/console/1');

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('id', 1)
                    ->etc()
            );
    }

    public function test_create_console(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $console = Console::factory()->create();

        $response = $this->post('/api/console', [
            'console_name' => $console->console_name,
        ]);

        $response
            ->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('console_name', $console->console_name)
                    ->etc()
        );
    }

    public function test_update_console(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $console = Console::factory()->create();

        $response = $this->put('/api/console/1', [
            'console_name' => $console->console_name,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('console_name', $console->console_name)
                    ->etc()
            );
    }

    public function test_delete_console(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $console = Console::factory()->create();

        $response = $this->delete('/api/console/' . $console->id);

        $response->assertStatus(200);
    }
}
