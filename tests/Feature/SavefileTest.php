<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;
use Faker\Factory;
use Illuminate\Support\Facades\Storage;
use App\Models\Savefile;
use App\Models\Game;
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

        // Get the directory from the game
        $game = Game::find($fk_id_game);
        $savefile_dir = 'saves/' . $game->name . '/';

        // Find the just created backup
        $files = Storage::files($savefile_dir . 'backups');
        // Find all files that match the file name
        $matchingFiles = array_filter($files, function ($file) use ($file_name) {
            return strpos($file, $file_name) !== false;
        });
        // Save the last created file between the backups
        if (!empty($matchingFiles)) {
            $backup_file_path = end($matchingFiles);
        }

        // Check if the file and the backup were created
        $this->assertFileEquals(
            $file->getPathname(),
            storage_path('app/' . $savefile_dir . $file_name),
            storage_path('app/' . $backup_file_path)
        );

        // Delete the file and the backup
        Storage::delete($savefile_dir . $file_name);
        Storage::delete($backup_file_path);
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

        // Get the directory from the game
        $game = Game::find($fk_id_game);
        $savefile_dir = 'saves/' . $game->name . '/';

        // Find the just created backup
        $files = Storage::files($savefile_dir . 'backups');
        // Find all files that match the file name
        $matchingFiles = array_filter($files, function ($file) use ($file_name) {
            return strpos($file, $file_name) !== false;
        });
        // Save the last created file between the backups
        if (!empty($matchingFiles)) {
            $backup_file_path = end($matchingFiles);
        }

        // Check if the file and the backup were created
        $this->assertFileEquals(
            $file->getPathname(),
            storage_path('app/' . $savefile_dir . $file_name),
            storage_path('app/' . $backup_file_path)
        );

        // Delete the file and the backup
        Storage::delete($savefile_dir . $file_name);
        Storage::delete($backup_file_path);
    }

    public function test_update_savefile(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        // Create a file
        $file = UploadedFile::fake()->create('savefile.txt', 256);
        $savefile = Savefile::find(1);
        $file_name = $savefile->file_name;
        $fk_id_game = $savefile->fk_id_game;

        // Get the directory from the game
        $game = Game::find($fk_id_game);
        $savefile_dir = 'saves/' . $game->name . '/';

        // Save the previous file
        Storage::copy($savefile_dir . $file_name, $savefile_dir . $file_name . '.bak');

        // Send the request
        $response = $this->put('/api/savefile/1', [
            'savefile' => $file
        ]);

        // Check the response
        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('fk_id_game', $fk_id_game)
                    ->etc()
            );

        // Find the just created backup
        $files = Storage::files($savefile_dir . 'backups');
        // Find all files that match the file name
        $matchingFiles = array_filter($files, function ($file) use ($file_name) {
            return strpos($file, $file_name) !== false;
        });
        // Save the last created file between the backups
        if (!empty($matchingFiles)) {
            $backup_file_path = end($matchingFiles);
        }

        // Check if the file was updated and the backup was created
        $this->assertFileEquals(
            $file->getPathname(),
            storage_path('app/' . $savefile_dir . $file_name),
            storage_path('app/' . $backup_file_path)
        );

        // Restore the file
        Storage::move($savefile_dir . $file_name . '.bak', $savefile_dir . $file_name);

        // Delete the backup
        Storage::delete($backup_file_path);
    }

    public function test_delete_savefile(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        // Save the previous file
        $savefile = Savefile::find(1);
        $file_name = $savefile->file_name;
        $fk_id_game = $savefile->fk_id_game;
        $game = Game::find($fk_id_game);
        $savefile_dir = 'saves/' . $game->name . '/';
        Storage::copy($savefile_dir . $file_name, $savefile_dir . $file_name . '.bak');

        // Test the deletion
        $response = $this->delete('/api/savefile/1');
        $response->assertStatus(200);
        $this->assertFileDoesNotExist('app/' . $savefile_dir . $file_name);

        // Restore the file
        Storage::move($savefile_dir . $file_name . '.bak', $savefile_dir . $file_name);
    }

}
