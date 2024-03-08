<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;
use Faker\Factory;
use Illuminate\Support\Facades\Storage;
use App\Models\Game;

class GameTest extends TestCase
{

    use DatabaseTransactions;

    public function test_list_game(): void
    {
        $response = $this->get('/api/game');

        $response->assertStatus(200);
    }

    public function test_get_game(): void
    {
        $response = $this->get('/api/game/1');

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('id', 1)
                    ->etc()
            );
    }

    public function test_create_game(): void
    {
        $game = Game::factory()->create();

        $response = $this->post('/api/game', [
            'name' => $game->name,
        ]);

        $response
            ->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('name', $game->name)
                    ->etc()
        );
    }

    public function test_update_game(): void
    {
        $game = Game::factory()->create();

        $response = $this->put('/api/game/1', [
            'name' => $game->name,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('name', $game->name)
                    ->etc()
            );
    }

    public function test_delete_game(): void
    {
        $game = Game::factory()->create();

        $response = $this->delete('/api/game/' . $game->id);

        $response->assertStatus(200);
    }

}
