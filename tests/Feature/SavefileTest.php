<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\Testing\File;
use Illuminate\Testing\Fluent\AssertableJson;

class SavefileTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_list_savefile(): void
    {
        $response = $this->get('/api/savefile');

        $response->assertStatus(200);
    }

    public function test_get_savefile(): void
    {
        $response = $this->get('/api/savefile/1');

        $response
            ->assertStatus(200)
            ->assertDownload();
    }

    public function test_store_savefile(): void
    {
        $faker = \Faker\Factory::create();

        $file_name = $faker->word . $faker->fileExtension;
        $response = $this->post('/api/savefile', [
            'savefile' => File::fake()->create('savefile.txt'),
            'file_name' => $file_name,
            'fk_id_game' => '1'
        ]);

        $response
            ->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('file_name', $file_name)
                    ->where('fk_id_game', '1')
                    ->etc()
            );
    }

}
