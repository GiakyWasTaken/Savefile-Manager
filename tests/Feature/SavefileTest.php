<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;
use Faker\Factory;
use Illuminate\Support\Facades\Storage;
use App\Models\Savefile;
use App\Models\User;
use Laravel\Passport\Passport;

class SavefileTest extends TestCase
{

    use DatabaseTransactions;

    public function test_list_savefile(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->get('/api/savefile');

        $response->assertStatus(200);
    }

    public function test_get_savefile(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->get('/api/savefile/1');

        $response
            ->assertStatus(200)
            ->assertDownload();
    }

    public function test_store_savefile(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $faker = Factory::create();

        // Create a file
        $file = UploadedFile::fake()->create('savefile.txt', 256);
        $file_name = $faker->word . '.' . $faker->fileExtension;
        $fk_id_game = strval($faker->numberBetween(1, 10));

        // Send the request
        $response = $this->post('/api/savefile', [
            'savefile' => $file,
            'file_name' => $file_name,
            'fk_id_game' => $fk_id_game
        ]);

        // Check the response
        $response
            ->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('file_name', $file_name)
                    ->where('fk_id_game', $fk_id_game)
                    ->etc()
            );

        // Check if the file was created
        $this->assertFileEquals(
            $file->getPathname(),
            storage_path('app/saves/' . $file_name)
        );

        // Delete the file
        Storage::delete('saves/' . $file_name);
    }

    public function test_store_savefile_no_file_name(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $faker = Factory::create();

        // Create a file
        $file_name = $faker->word . '.' . $faker->fileExtension;
        $file = UploadedFile::fake()->create($file_name, 256);
        $fk_id_game = strval($faker->numberBetween(1, 10));

        // Send the request
        $response = $this->post('/api/savefile', [
            'savefile' => $file,
            'fk_id_game' => $fk_id_game
        ]);

        // Check the response
        $response
            ->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('file_name', $file_name)
                    ->where('fk_id_game', $fk_id_game)
                    ->etc()
            );

        // Check if the file was created
        $this->assertFileEquals(
            $file->getPathname(),
            storage_path('app/saves/' . $file_name)
        );

        // Delete the file
        Storage::delete('saves/' . $file_name);
    }

    public function test_update_savefile(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        // Create a file
        $file = UploadedFile::fake()->create('savefile.txt', 256);
        $file_name = Savefile::find(1)->file_name;
        $fk_id_game = strval(Factory::create()->numberBetween(1, 10));

        // Send the request
        $response = $this->put('/api/savefile/1', [
            'savefile' => $file,
            'fk_id_game' => $fk_id_game
        ]);

        // Check the response
        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('fk_id_game', $fk_id_game)
                    ->etc()
            );

        // Check if the file was updated
        $this->assertFileEquals(
            $file->getPathname(),
            storage_path('app/saves/' . $file_name)
        );

        // Delete the file
        $files = Storage::files('backups');
        // Find all files that match the file name
        $matchingFiles = array_filter($files, function ($file) use ($file_name) {
            return strpos($file, $file_name) !== false;
        });
        // Delete the last created file
        if (!empty($matchingFiles)) {
            $lastCreatedFile = end($matchingFiles);
            Storage::delete($lastCreatedFile);
        }
    }

    public function test_delete_savefile(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        // Create a backup of the file
        $file_name = Savefile::find(1)->file_name;
        Storage::copy('saves/' . $file_name, 'saves/' . $file_name . '.bak');

        // Test the deletion
        $response = $this->delete('/api/savefile/1');
        $response->assertStatus(200);
        $this->assertFileDoesNotExist('app/saves/' . $file_name);

        // Restore the file
        Storage::move('saves/' . $file_name . '.bak', 'saves/' . $file_name);
    }

}
